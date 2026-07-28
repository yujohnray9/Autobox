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
