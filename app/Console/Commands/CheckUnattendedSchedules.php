<?php

namespace App\Console\Commands;

use App\Models\Key;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Transaction;
use App\Models\AccessLog;
use App\Events\AccessLogged;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckUnattendedSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autobox:check-unattended {--force : Force check ignoring 15-minute start grace period} {--day= : Check schedules for a specific day of week (e.g. monday)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan class schedules and log audit entries for users who did not scan or retrieve their assigned key (Absent / Class Unattended).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning class schedules for unattended / absent key access...");
        $force = (bool) $this->option('force');
        $day = $this->option('day');

        $count = self::scanUnattendedSchedules($force, $day, $this);
        $this->info("Scan completed. Logged {$count} absent / unattended schedule(s).");

        return Command::SUCCESS;
    }

    /**
     * Scan schedules for today (or specified day) and record an audit log if the assigned
     * user did not scan their QR code or borrow/retrieve their scheduled key.
     */
    public static function scanUnattendedSchedules(bool $force = false, ?string $dayOverride = null, ?Command $cli = null): int
    {
        $targetDay = strtolower($dayOverride ?: now()->format('l'));
        $now = now();
        $dateFormatted = $now->format('M d, Y');
        $absentCount = 0;

        $schedules = Schedule::where('day_of_week', $targetDay)
            ->where('is_active', true)
            ->with(['user', 'key'])
            ->get();

        if ($schedules->isEmpty()) {
            if ($cli) {
                $cli->line("No active schedules found for " . ucfirst($targetDay) . ".");
            }
            return 0;
        }

        foreach ($schedules as $schedule) {
            $user = $schedule->user;
            $key  = $schedule->key;

            if (!$user || !$key) {
                continue;
            }

            // Exclude administrators (they have unrestricted access and no required schedules)
            if ($user->role === 'admin') {
                continue;
            }

            $startTime = Carbon::today()->setTimeFromTimeString($schedule->start_time);
            $endTime   = Carbon::today()->setTimeFromTimeString($schedule->end_time);

            // 15-minute grace threshold past schedule start_time
            $graceThreshold = $startTime->copy()->addMinutes(15);

            // Respect newly created or updated schedules on the same day:
            // 1) If the schedule was created/updated today AFTER the schedule's end_time already passed, skip today.
            // 2) If the schedule was created/updated today while in progress (after start_time), give 15 minutes from creation/update time.
            $lastModified = $schedule->updated_at ?? $schedule->created_at;
            if (!$force && empty($dayOverride) && $lastModified && $lastModified->isToday()) {
                if ($lastModified->greaterThanOrEqualTo($endTime)) {
                    if ($cli) {
                        $cli->line("  [CREATED AFTER END] Slot #{$key->slot_number} ({$key->room_name}) schedule was created/updated at {$lastModified->format('h:i A')}, after end time ({$endTime->format('h:i A')}). Skipping for today.");
                    }
                    continue;
                }

                if ($lastModified->greaterThan($startTime)) {
                    $modifiedGrace = $lastModified->copy()->addMinutes(15);
                    if ($modifiedGrace->greaterThan($graceThreshold)) {
                        $graceThreshold = $modifiedGrace;
                    }
                }
            }

            // If not forced and not a day-override, skip if grace threshold hasn't arrived yet
            if (!$force && empty($dayOverride)) {
                if ($now->lessThan($graceThreshold)) {
                    if ($cli) {
                        $cli->line("  [GRACE/UPCOMING] Slot #{$key->slot_number} ({$key->room_name}) scheduled for {$user->name} ({$startTime->format('h:i A')}). Grace active until {$graceThreshold->format('h:i A')}.");
                    }
                    continue;
                }
            }

            // Check if the user successfully scanned or borrowed this key today
            // 1) A successful borrow transaction occurred today around this schedule window
            $hasBorrowTx = Transaction::where('user_id', $user->id)
                ->where('key_id', $key->id)
                ->where('action', 'borrow')
                ->where('status', 'success')
                ->whereDate('borrowed_at', today())
                ->whereTime('borrowed_at', '>=', $startTime->copy()->subMinutes(30)->format('H:i:s'))
                ->whereTime('borrowed_at', '<=', $endTime->copy()->addMinutes(15)->format('H:i:s'))
                ->exists();

            // 2) Is user currently holding this key as the active borrower?
            $isCurrentlyHolding = ($key->status === 'borrowed' && $key->currentBorrower()?->user_id === $user->id);

            // 3) Granted scan occurred today in this window
            $hasGrantedBorrow = AccessLog::where('user_id', $user->id)
                ->where('action', 'borrow')
                ->where('result', 'granted')
                ->whereDate('created_at', today())
                ->whereTime('created_at', '>=', $startTime->copy()->subMinutes(30)->format('H:i:s'))
                ->whereTime('created_at', '<=', $endTime->copy()->addMinutes(15)->format('H:i:s'))
                ->exists();

            // Did IR sensor confirm the key was NOT removed (i.e. scan happened but key was not retrieved)?
            $cancelledBorrow = AccessLog::where('user_id', $user->id)
                ->where('action', 'absent')
                ->where('result', 'denied')
                ->where('reason', 'like', '%Key Not Retrieved%')
                ->whereDate('created_at', today())
                ->whereTime('created_at', '>=', $startTime->copy()->subMinutes(30)->format('H:i:s'))
                ->whereTime('created_at', '<=', $endTime->copy()->addMinutes(15)->format('H:i:s'))
                ->exists();

            $userRetrievedKey = ($hasBorrowTx || $isCurrentlyHolding || ($hasGrantedBorrow && !$cancelledBorrow));

            if ($userRetrievedKey) {
                if ($cli) {
                    $cli->line("  [ATTENDED] Slot #{$key->slot_number} ({$key->room_name}) - {$user->name} scanned and retrieved key.");
                }
                continue;
            }

            // User did NOT scan, use, or get the key!
            // Prevent duplicate absent logs for the same schedule on the same day
            $startTimeStr = $startTime->format('h:i A');
            $alreadyLogged = AccessLog::where('user_id', $user->id)
                ->where('action', 'absent')
                ->whereDate('created_at', today())
                ->where('reason', 'like', "%Slot #{$key->slot_number}%")
                ->where('reason', 'like', "%{$startTimeStr}%")
                ->exists();

            if ($alreadyLogged) {
                if ($cli) {
                    $cli->line("  [ALREADY RECORDED] {$user->name} already marked absent for Slot #{$key->slot_number} ({$startTimeStr}).");
                }
                continue;
            }

            $startFormatted = $startTime->format('h:i A');
            $endFormatted   = $endTime->format('h:i A');
            $roomName       = $key->room_name ?: "Room {$key->slot_number}";
            $dayTitle       = ucfirst($schedule->day_of_week);

            $reason = "Absent / Did Not Attend Class: {$user->name} did not scan QR code or retrieve key for {$roomName} ({$key->key_name}, Slot #{$key->slot_number}) on {$dateFormatted} (Schedule: {$dayTitle} {$startFormatted} - {$endFormatted}).";

            try {
                AccessLog::create([
                    'user_id'    => $user->id,
                    'qr_token'   => $user->qr_token ?? 'SYSTEM_ABSENT',
                    'action'     => 'absent',
                    'result'     => 'denied',
                    'reason'     => $reason,
                    'ip_address' => '127.0.0.1',
                ]);

                $absentCount++;

                if ($cli) {
                    $cli->warn("  [ABSENT RECORDED] {$user->name} - {$reason}");
                }

                try {
                    AccessLogged::dispatch(
                        $user->name,
                        'absent',
                        'denied',
                        $reason,
                        $key->key_name,
                        $key->room_name
                    );
                } catch (\Throwable $e) {
                    Log::warning("[UNATTENDED] Broadcast skipped: " . $e->getMessage());
                }
            } catch (\Throwable $e) {
                Log::error("[UNATTENDED SCHEDULE] Failed to create access log: " . $e->getMessage());
                if ($cli) {
                    $cli->error("  --> Error creating absent log: " . $e->getMessage());
                }
            }
        }

        return $absentCount;
    }
}
