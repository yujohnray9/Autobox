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
        $users = User::where('is_active', true)->orderBy('name')->get();
        $keys  = Key::orderBy('slot_number')->get();

        return view('schedules.index', compact('schedules', 'users', 'keys'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->get();
        $keys  = Key::orderBy('slot_number')->get();
        return view('schedules.create', compact('users', 'keys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'key_id'      => 'required|exists:keys,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        // Check: same user already has this key scheduled on this day
        $userConflict = Schedule::where('user_id', $validated['user_id'])
            ->where('key_id', $validated['key_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_time', '<=', $validated['start_time'])
                         ->where('end_time', '>=', $validated['end_time']);
                  });
            })->first();

        if ($userConflict) {
            $user = \App\Models\User::find($validated['user_id']);
            return back()->withInput()->with('conflict_error',
                "⚠️ Conflict: {$user->name} already has a schedule for this key on " . ucfirst($validated['day_of_week']) . " from " .
                \Carbon\Carbon::parse($userConflict->start_time)->format('h:i A') . " to " .
                \Carbon\Carbon::parse($userConflict->end_time)->format('h:i A') . ". Please choose a different time or day."
            );
        }

        // Check: this key is already assigned to another user on the same day with overlapping time
        $keyConflict = Schedule::where('key_id', $validated['key_id'])
            ->where('day_of_week', $validated['day_of_week'])
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
            $key = \App\Models\Key::find($validated['key_id']);
            return back()->withInput()->with('conflict_error',
                "⚠️ Conflict: Key Slot #{$key->slot_number} ({$key->key_name}) is already assigned to {$keyConflict->user->name} on " . ucfirst($validated['day_of_week']) . " from " .
                \Carbon\Carbon::parse($keyConflict->start_time)->format('h:i A') . " to " .
                \Carbon\Carbon::parse($keyConflict->end_time)->format('h:i A') . ". Please choose a different key or time."
            );
        }

        $validated['is_active'] = $request->has('is_active');

        Schedule::create($validated);

        return redirect()->route('schedules.index')->with('success', 'Access schedule created successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $name = $schedule->user->name ?? 'User';
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', "Schedule for {$name} removed.");
    }
}
