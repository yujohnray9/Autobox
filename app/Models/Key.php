<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    use HasFactory;

    protected $fillable = [
        'key_name', 'room_name', 'description',
        'slot_number', 'status', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'slot_number' => 'integer',
        ];
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function currentBorrower()
    {
        return $this->transactions()
            ->where('action', 'borrow')
            ->where('status', 'success')
            ->whereNull('returned_at')
            ->with('user')
            ->latest()
            ->first();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available' => 'green',
            'borrowed'  => 'amber',
            'missing'   => 'red',
            default     => 'gray',
        };
    }

    /**
     * Get schedule and 10-minute grace countdown information for a borrowed key.
     */
    public function getScheduleStatusInfo(): ?array
    {
        if ($this->status !== 'borrowed') {
            return null;
        }

        $borrowerTx = $this->currentBorrower();
        if (!$borrowerTx || !$borrowerTx->user) {
            return null;
        }

        $user = $borrowerTx->user;
        $today = strtolower(now()->format('l'));

        $schedule = Schedule::where('user_id', $user->id)
            ->where('key_id', $this->id)
            ->where('day_of_week', $today)
            ->where('is_active', true)
            ->latest()
            ->first();

        if (!$schedule) {
            $schedule = Schedule::where('user_id', $user->id)
                ->where('key_id', $this->id)
                ->where('is_active', true)
                ->latest()
                ->first();
        }

        if (!$schedule) {
            return null;
        }

        $now = now();
        $isToday = ($schedule->day_of_week === $today);

        if ($isToday) {
            $startTime = \Carbon\Carbon::today()->setTimeFromTimeString($schedule->start_time);
            $endTime = \Carbon\Carbon::today()->setTimeFromTimeString($schedule->end_time);
            $graceEndTime = $endTime->copy()->addMinutes(10);

            if ($now->lessThan($endTime)) {
                return [
                    'state'          => 'active',
                    'in_grace'       => false,
                    'schedule_start' => $startTime->format('h:i A'),
                    'schedule_end'   => $endTime->format('h:i A'),
                    'borrower_name'  => $user->name,
                ];
            } elseif ($now->lessThan($graceEndTime)) {
                $secondsLeft = max(0, (int) $now->diffInSeconds($graceEndTime, false));
                return [
                    'state'          => 'grace_period',
                    'in_grace'       => true,
                    'schedule_end'   => $endTime->format('h:i A'),
                    'grace_end'      => $graceEndTime->format('h:i A'),
                    'grace_end_iso'  => $graceEndTime->toIso8601String(),
                    'seconds_left'   => $secondsLeft,
                    'borrower_name'  => $user->name,
                ];
            } else {
                return [
                    'state'          => 'overdue',
                    'in_grace'       => false,
                    'schedule_end'   => $endTime->format('h:i A'),
                    'borrower_name'  => $user->name,
                ];
            }
        } else {
            return [
                'state'         => 'overdue',
                'in_grace'      => false,
                'schedule_day'  => ucfirst($schedule->day_of_week),
                'borrower_name' => $user->name,
            ];
        }
    }
}
