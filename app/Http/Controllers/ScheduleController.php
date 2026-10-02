<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Key;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedulesByDay = Schedule::with(['user', 'key'])
            ->orderBy('start_time')
            ->orderBy('end_time')
            ->get()
            ->groupBy(fn ($s) => strtolower($s->day_of_week));

        $users = User::where('is_active', true)->where('role', '!=', 'admin')->orderBy('name')->get();
        $keys  = Key::orderBy('slot_number')->get();

        return view('schedules.index', compact('schedulesByDay', 'users', 'keys'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->where('role', '!=', 'admin')->orderBy('name')->get();
        $keys  = Key::orderBy('slot_number')->get();
        return view('schedules.create', compact('users', 'keys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'key_id'      => 'required|exists:keys,id',
            'days'        => 'nullable|array',
            'days.*'      => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time'  => 'required|date_format:H:i|after_or_equal:07:00|before:17:00',
            'end_time'    => 'required|date_format:H:i|after:start_time|before_or_equal:17:00',
        ], [
            'start_time.after_or_equal' => 'Schedule start time must be at or after 7:00 AM (07:00).',
            'start_time.before'         => 'Schedule start time must be before 5:00 PM (17:00).',
            'end_time.after'            => 'Schedule end time must be after the start time.',
            'end_time.before_or_equal'  => 'Schedule end time must be at or before 5:00 PM (17:00).',
        ]);

        // Determine days
        $days = [];
        if (!empty($validated['days'])) {
            $days = $validated['days'];
        } elseif (!empty($validated['day_of_week'])) {
            $days = [$validated['day_of_week']];
        }

        if (empty($days)) {
            return back()->withInput()->with('conflict_error', '⚠️ Please select at least one day of the week for the access schedule.');
        }

        // Server-side guard: admins do not need schedules (they have unrestricted access)
        $selectedUser = User::find($validated['user_id']);
        if ($selectedUser && $selectedUser->role === 'admin') {
            return back()->withInput()->with('conflict_error',
                "⚠️ Admins cannot be assigned a schedule. Admins already have unrestricted access to all key slots."
            );
        }

        // CHANGED: strict overlap check. Two windows overlap only if
        // existing.start < new.end AND existing.end > new.start.
        // The old whereBetween() was inclusive, so back-to-back slots like
        // 8:00–9:00 and 9:00–10:00 were wrongly flagged as conflicts.
        $overlaps = function ($q) use ($validated) {
            $q->where('start_time', '<', $validated['end_time'])
              ->where('end_time', '>', $validated['start_time']);
        };

        // Validate conflicts across each selected day
        foreach ($days as $day) {
            // Check: same user already has this key scheduled on this day
            $userConflict = Schedule::where('user_id', $validated['user_id'])
                ->where('key_id', $validated['key_id'])
                ->where('day_of_week', $day)
                ->where($overlaps)
                ->first();

            if ($userConflict) {
                $user = User::find($validated['user_id']);
                return back()->withInput()->with('conflict_error',
                    "⚠️ Conflict: {$user->name} already has a schedule for this key on " . ucfirst($day) . " from " .
                    \Carbon\Carbon::parse($userConflict->start_time)->format('g:i A') . " to " .
                    \Carbon\Carbon::parse($userConflict->end_time)->format('g:i A') . ". Please choose a different time or day."
                );
            }

            // Check: this key is already assigned to another user on the same day with overlapping time
            $keyConflict = Schedule::where('key_id', $validated['key_id'])
                ->where('day_of_week', $day)
                ->where('user_id', '!=', $validated['user_id'])
                ->where($overlaps)
                ->with('user')
                ->first();

            if ($keyConflict) {
                $key = Key::find($validated['key_id']);
                return back()->withInput()->with('conflict_error',
                    "⚠️ Conflict: Key Slot #{$key->slot_number} ({$key->key_name}) is already assigned to {$keyConflict->user->name} on " . ucfirst($day) . " from " .
                    \Carbon\Carbon::parse($keyConflict->start_time)->format('g:i A') . " to " .
                    \Carbon\Carbon::parse($keyConflict->end_time)->format('g:i A') . ". Please choose a different key or time."
                );
            }
        }

        $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

        foreach ($days as $day) {
            Schedule::create([
                'user_id'     => $validated['user_id'],
                'key_id'      => $validated['key_id'],
                'day_of_week' => $day,
                'start_time'  => $validated['start_time'],
                'end_time'    => $validated['end_time'],
                'is_active'   => $isActive,
            ]);
        }

        $count = count($days);
        $plural = $count === 1 ? 'rule' : 'rules';
        return redirect()->route('schedules.index')->with('success', "Access schedule ({$count} {$plural}) created successfully.");
    }

    public function destroy(Schedule $schedule)
    {
        $name = $schedule->user->name ?? 'User';
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', "Schedule for {$name} removed.");
    }
}