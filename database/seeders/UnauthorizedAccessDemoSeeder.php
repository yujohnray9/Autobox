<?php

namespace Database\Seeders;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Sample data for the dashboard "Unauthorized Access Attempts" panel and the alert bell.
 *
 *   php artisan db:seed --class=UnauthorizedAccessDemoSeeder
 *
 * Every demo row uses the IP 10.99.99.99, so re-running replaces the old demo rows
 * instead of duplicating them. Remove them with:
 *   php artisan tinker --execute="App\Models\AccessLog::where('ip_address','10.99.99.99')->delete();"
 */
class UnauthorizedAccessDemoSeeder extends Seeder
{
    public const DEMO_IP = '10.99.99.99';

    public function run(): void
    {
        $faculty = User::where('role', '!=', 'admin')->orderBy('id')->take(3)->get();
        $u1 = $faculty[0] ?? null;
        $u2 = $faculty[1] ?? $u1;
        $u3 = $faculty[2] ?? $u1;

        // Today's times are counted back from "now" so they always stay today and in the past
        $today = function (int $minutesAgo) {
            $t = now()->subMinutes($minutesAgo);
            return $t->isToday() ? $t : today()->addMinutes(1);
        };
        $daysAgo = fn (int $days, string $time) => today()->subDays($days)->setTimeFromTimeString($time);

        $day = fn ($date) => strtolower($date->format('l'));

        $rows = [
            // ── TODAY ─────────────────────────────────────────────
            [null, 'UNKNOWN-QR-7F3K9', 'scan', 'Invalid or inactive QR code', $today(5)],
            [null, 'https://bit.ly/free-wifi', 'scan', 'Invalid or inactive QR code', $today(18)],
            [$u2, null, 'borrow', 'Access Denied: Outside schedule (' . $day(now()) . ')', $today(32)],
            [$u1, null, 'security_alert', 'SECURITY ALERT: QR Code scanned 5 times within 15 minutes', $today(47)],
            [null, 'HARDWARE_IR_SENSOR', 'missing', 'Key physically removed from Slot #2 WITHOUT a QR scan (last holder: ' . ($u3?->name ?? 'unknown') . ')', $today(66)],
            [null, 'STUDENT-ID-2023-00417', 'scan', 'Invalid or inactive QR code', $today(84)],
            [$u3, null, 'return', 'Return NOT completed: IR sensor did not detect the key in Slot #1', $today(102)],

            // ── EARLIER THIS WEEK ─────────────────────────────────
            [null, 'UNKNOWN-QR-1B8XQ', 'scan', 'Invalid or inactive QR code', $daysAgo(1, '16:42:10')],
            [$u1, null, 'borrow', 'Access Denied: Outside schedule (' . $day(today()->subDays(1)) . ')', $daysAgo(1, '07:15:33')],
            [$u2, null, 'borrow', 'You still have a key that is not returned yet', $daysAgo(2, '13:05:51')],
            [null, 'COPY-OF-QR-PROF', 'scan', 'Invalid or inactive QR code', $daysAgo(3, '18:20:04')],
            [$u3, null, 'security_alert', 'SECURITY ALERT: QR Code scanned 4 times within 15 minutes', $daysAgo(4, '10:11:27')],
            [null, 'HARDWARE_IR_SENSOR', 'missing', 'Key physically removed from Slot #1 WITHOUT a QR scan (last holder: unknown)', $daysAgo(5, '21:47:59')],
            [$u1, null, 'borrow', 'Key Slot #2 was not removed - borrow cancelled (IR sensor)', $daysAgo(6, '09:02:14')],
        ];

        AccessLog::where('ip_address', self::DEMO_IP)->delete();

        // Don't fire 14 live Pusher pop-ups while seeding
        AccessLog::$muteAlerts = true;
        try {
            foreach ($rows as [$user, $qr, $action, $reason, $at]) {
                $log = AccessLog::create([
                    'user_id'    => $user?->id,
                    'qr_token'   => $qr ?? $user?->qr_token ?? 'DEMO',
                    'action'     => $action,
                    'result'     => 'denied',
                    'reason'     => $reason,
                    'ip_address' => self::DEMO_IP,
                ]);
                // created_at is not fillable - set the backdated time directly
                $log->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();
            }
        } finally {
            AccessLog::$muteAlerts = false;
        }

        $todayCount = AccessLog::where('result', 'denied')->whereDate('created_at', today())->count();
        $weekCount  = AccessLog::where('result', 'denied')->where('created_at', '>=', now()->subDays(7))->count();

        $this->command?->info(count($rows) . ' demo unauthorized attempts created.');
        $this->command?->info("Dashboard will show -> Today: {$todayCount}, Last 7 days: {$weekCount}");
    }
}
