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
        $schedules = Schedule::with(['user', 'key'])->latest()->paginate(20);
        // Only non-admin users can have schedules
        $users = User::where('is_active', true)->where('role', '!=', 'admin')->orderBy('name')->get();
        $keys  = Key::orderBy('slot_number')->get();

        return view('schedules.index', compact('schedules', 'users', 'keys'));
    }

    public function create()
    {
        // Only non-admin users can be assigned schedules
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
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
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

        // Validate conflicts across each selected day
        foreach ($days as $day) {
            // Check: same user already has this key scheduled on this day
            $userConflict = Schedule::where('user_id', $validated['user_id'])
                ->where('key_id', $validated['key_id'])
                ->where('day_of_week', $day)
                ->where(function ($q) use ($validated) {
                    $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhere(function ($q2) use ($validated) {
                          $q2->where('start_time', '<=', $validated['start_time'])
                             ->where('end_time', '>=', $validated['end_time']);
                      });
                })->first();

            if ($userConflict) {
                $user = User::find($validated['user_id']);
                return back()->withInput()->with('conflict_error',
                    "⚠️ Conflict: {$user->name} already has a schedule for this key on " . ucfirst($day) . " from " .
                    \Carbon\Carbon::parse($userConflict->start_time)->format('h:i A') . " to " .
                    \Carbon\Carbon::parse($userConflict->end_time)->format('h:i A') . ". Please choose a different time or day."
                );
            }

            // Check: this key is already assigned to another user on the same day with overlapping time
            $keyConflict = Schedule::where('key_id', $validated['key_id'])
                ->where('day_of_week', $day)
                ->where('user_id', '!=', $validated['user_id'])
                ->where(function ($q) use ($validated) {
                    $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhere(function ($q2) use ($validated) {
                          $q2->where('start_time', '<=', $validated['start_time'])
                             ->where('end_time', '>=', $validated['end_time']);
                      });
                })->with('user')->first();

            if ($keyConflict) {
                $key = Key::find($validated['key_id']);
                return back()->withInput()->with('conflict_error',
                    "⚠️ Conflict: Key Slot #{$key->slot_number} ({$key->key_name}) is already assigned to {$keyConflict->user->name} on " . ucfirst($day) . " from " .
                    \Carbon\Carbon::parse($keyConflict->start_time)->format('h:i A') . " to " .
                    \Carbon\Carbon::parse($keyConflict->end_time)->format('h:i A') . ". Please choose a different key or time."
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
