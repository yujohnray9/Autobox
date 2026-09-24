<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\User;
use App\Models\Schedule;
use App\Console\Commands\CheckUnreturnedKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        // Auto-scan for unreturned keys that passed the 10-minute return window / schedule grace
        try {
            CheckUnreturnedKeys::scanExpiredBorrows();
        } catch (\Throwable $e) {
            Log::warning("[DASHBOARD] Auto-scan unreturned keys error: " . $e->getMessage());
        }

        // ── Key status counts ──────────────────────────────
        $totalKeys     = Key::count();
        $availableKeys = Key::where('status', 'available')->count();
        $borrowedKeys  = Key::where('status', 'borrowed')->count();
        $missingKeys   = Key::where('status', 'missing')->count();

        $keys = Key::orderBy('slot_number')->get();

        // ── User & Schedule counts ──────────────────────────
        $totalUsers     = User::where('role', '!=', 'admin')->where('is_active', true)->count();
        $totalSchedules = Schedule::where('is_active', true)->count();
        $totalAdmins    = User::where('role', 'admin')->where('is_active', true)->count();

        // ── Schedule breakdown per day (for bar chart) ──────
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dayLabels   = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $schedulePerDay = [];
        foreach ($days as $day) {
            $schedulePerDay[] = Schedule::where('day_of_week', $day)->where('is_active', true)->count();
        }

        // ── Keys per status (for donut / bar data) ──────────
        $keyStatusData = [
            'available' => $availableKeys,
            'borrowed'  => $borrowedKeys,
            'missing'   => $missingKeys,
        ];

        return view('dashboard', compact(
            'totalKeys', 'availableKeys', 'borrowedKeys', 'missingKeys',
            'keys',
            'totalUsers', 'totalSchedules', 'totalAdmins',
            'dayLabels', 'schedulePerDay',
            'keyStatusData'
        ));
    }
}
