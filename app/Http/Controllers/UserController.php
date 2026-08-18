<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Key;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['schedules.key'])->latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $keys = Key::orderBy('slot_number')->get();
        return view('users.create', compact('keys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'role'            => 'required|in:admin,faculty,staff',
            'department'      => 'nullable|string|max:255',
            'employee_id'     => 'nullable|string|unique:users,employee_id|regex:/^EMP-\d{4}-\d{3,}$/',
            
            // Schedule fields (optional / conditional)
            'assign_schedule' => 'nullable',
            'key_id'          => 'required_if:assign_schedule,1|nullable|exists:keys,id',
            'days'            => 'required_if:assign_schedule,1|nullable|array',
            'days.*'          => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'day_of_week'     => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time'      => 'required_if:assign_schedule,1|nullable|date_format:H:i',
            'end_time'        => 'required_if:assign_schedule,1|nullable|date_format:H:i|after:start_time',
        ]);

        if (empty($validated['employee_id'])) {
            $year = now()->format('Y');
            $lastUser = User::where('employee_id', 'like', "EMP-{$year}-%")
                ->orderByRaw('CAST(SUBSTRING_INDEX(employee_id, "-", -1) AS UNSIGNED) DESC')
                ->first();
            $nextNum = $lastUser
                ? (int) explode('-', $lastUser->employee_id)[2] + 1
                : 1;
            $validated['employee_id'] = "EMP-{$year}-" . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        // Determine days array
        $days = [];
        if (!empty($validated['days'])) {
            $days = $validated['days'];
        } elseif (!empty($validated['day_of_week'])) {
            $days = [$validated['day_of_week']];
        }

        // Check for schedule conflict if schedule is assigned
        $hasSchedule = ($request->has('assign_schedule') || !empty($validated['key_id'])) && !empty($validated['key_id']) && !empty($days) && !empty($validated['start_time']) && !empty($validated['end_time']);

        if ($hasSchedule) {
            foreach ($days as $day) {
                $keyConflict = Schedule::where('key_id', $validated['key_id'])
                    ->where('day_of_week', $day)
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
                        Carbon::parse($keyConflict->start_time)->format('h:i A') . " to " .
                        Carbon::parse($keyConflict->end_time)->format('h:i A') . ". Please choose a different key or time."
                    );
                }
            }
        }

        $user = null;
        DB::transaction(function () use ($validated, $hasSchedule, $days, &$user) {
            $user = User::create([
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'role'        => $validated['role'],
                'department'  => $validated['department'] ?? null,
                'employee_id' => $validated['employee_id'],
                'password'    => Hash::make(Str::random(16)),
                'qr_token'    => Str::uuid()->toString(),
                'is_active'   => true,
            ]);

            if ($hasSchedule) {
                foreach ($days as $day) {
                    Schedule::create([
                        'user_id'     => $user->id,
                        'key_id'      => $validated['key_id'],
                        'day_of_week' => $day,
                        'start_time'  => $validated['start_time'],
                        'end_time'    => $validated['end_time'],
                        'is_active'   => true,
                    ]);
                }
            }
        });

        $message = $hasSchedule 
            ? 'User created successfully with generated QR code and access schedule.'
            : 'User created successfully with generated QR code.';

        return redirect()->route('users.qr', $user)->with('success', $message);
    }

    public function edit(User $user)
    {
        $user->load('schedules.key');
        $keys = Key::orderBy('slot_number')->get();
        return view('users.edit', compact('user', 'keys'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role'        => 'required|in:admin,faculty,staff',
            'department'  => 'nullable|string|max:255',
            'employee_id' => 'required|string|unique:users,employee_id,' . $user->id,
            'is_active'   => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function qr(User $user)
    {
        if (!$user->qr_token) {
            $user->generateQrToken();
        }
        $user->load('schedules.key');
        return view('users.qr', compact('user'));
    }

    public function regenerateQr(User $user)
    {
        $user->generateQrToken();
        return redirect()->back()->with('success', 'QR Code regenerated successfully.');
    }
}

