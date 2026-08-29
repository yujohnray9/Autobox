<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // ── Key Counts ──────────────────────────────────────
        $totalKeys     = Key::count();
        $availableKeys = Key::where('status', 'available')->count();
        $borrowedKeys  = Key::where('status', 'borrowed')->count();
        $missingKeys   = Key::where('status', 'missing')->count();

        // ── User Counts ─────────────────────────────────────
        $totalUsers   = User::where('role', '!=', 'admin')->where('is_active', true)->count();
        $totalAdmins  = User::where('role', 'admin')->where('is_active', true)->count();
        $inactiveUsers = User::where('role', '!=', 'admin')->where('is_active', false)->count();

        // ── Schedule Counts ─────────────────────────────────
        $totalSchedules  = Schedule::where('is_active', true)->count();
        $inactiveSchedules = Schedule::where('is_active', false)->count();

        // ── Schedules per day of week (bar chart) ───────────
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dayLabels      = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $schedulePerDay = [];
        foreach ($days as $day) {
            $schedulePerDay[] = Schedule::where('day_of_week', $day)->where('is_active', true)->count();
        }

        // ── Users with most schedules ────────────────────────
        $topScheduledUsers = Schedule::select('user_id', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user')
            ->take(10)
            ->get();

        // ── Keys with most schedules ─────────────────────────
        $popularKeys = Schedule::select('key_id', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->groupBy('key_id')
            ->orderByDesc('total')
            ->with('key')
            ->take(5)
            ->get();

        // ── Key status breakdown ─────────────────────────────
        $statusCounts   = Key::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $availableCount = $statusCounts['available'] ?? 0;
        $borrowedCount  = $statusCounts['borrowed']  ?? 0;
        $missingCount   = $statusCounts['missing']   ?? 0;

        return view('reports.index', compact(
            'totalKeys', 'availableKeys', 'borrowedKeys', 'missingKeys',
            'totalUsers', 'totalAdmins', 'inactiveUsers',
            'totalSchedules', 'inactiveSchedules',
            'dayLabels', 'schedulePerDay',
            'topScheduledUsers', 'popularKeys',
            'statusCounts', 'availableCount', 'borrowedCount', 'missingCount'
        ));
    }
}
