<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\User;
use App\Models\Schedule;
use App\Models\AccessLog;
use App\Models\OfflineRecord;
use App\Console\Commands\CheckUnreturnedKeys;
use App\Console\Commands\CheckUnattendedSchedules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Auto-scan for unreturned keys that passed the 10-minute return window / schedule grace
        try {
            CheckUnreturnedKeys::scanExpiredBorrows();
        } catch (\Throwable $e) {
            Log::warning("[DASHBOARD] Auto-scan unreturned keys error: " . $e->getMessage());
        }

        // Auto-scan for unattended class schedules (absent users who didn't scan or get key)
        try {
            CheckUnattendedSchedules::scanUnattendedSchedules();
        } catch (\Throwable $e) {
            Log::warning("[DASHBOARD] Auto-scan unattended schedules error: " . $e->getMessage());
        }

        // ── Period Filter (Activity & Security metrics) ────
        $period    = $request->query('period', 'today');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $now       = now();

        switch ($period) {
            case 'month':
                $start       = $now->copy()->startOfMonth();
                $end         = $now->copy()->endOfMonth();
                $periodLabel = 'This Month (' . $now->format('F Y') . ')';
                break;
            case 'year':
                $start       = $now->copy()->startOfYear();
                $end         = $now->copy()->endOfYear();
                $periodLabel = 'This Year (' . $now->format('Y') . ')';
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    try {
                        $start       = Carbon::parse($startDate)->startOfDay();
                        $end         = Carbon::parse($endDate)->endOfDay();
                        $periodLabel = $start->format('M d, Y') . ' – ' . $end->format('M d, Y');
                    } catch (\Throwable $e) {
                        $period      = 'today';
                        $start       = $now->copy()->startOfDay();
                        $end         = $now->copy()->endOfDay();
                        $periodLabel = 'Today (' . $now->format('M d, Y') . ')';
                    }
                } else {
                    $period      = 'today';
                    $start       = $now->copy()->startOfDay();
                    $end         = $now->copy()->endOfDay();
                    $periodLabel = 'Today (' . $now->format('M d, Y') . ')';
                }
                break;
            case 'today':
            default:
                $period      = 'today';
                $start       = $now->copy()->startOfDay();
                $end         = $now->copy()->endOfDay();
                $periodLabel = 'Today (' . $now->format('M d, Y') . ')';
                break;
        }

        // ── LIVE PHYSICAL HARDWARE STATUS (STAYS LIVE REAL-TIME, NEVER FILTERED) ──
        $totalKeys     = Key::count();
        $availableKeys = Key::where('status', 'available')->count();
        $borrowedKeys  = Key::where('status', 'borrowed')->count();
        $missingKeys   = Key::where('status', 'missing')->count();
        $keys          = Key::orderBy('slot_number')->get();

        // ── System Overview Counts (Live) ──────────────────
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

        // ── Strictly Unauthorized Users (Unregistered / Unknown QR scans) ──
        $unauthorizedQuery = AccessLog::where('result', 'denied')
            ->whereNull('user_id')
            ->where('action', '!=', 'missing');

        $unauthorizedToday    = (clone $unauthorizedQuery)->whereDate('created_at', today())->count();
        $unauthorizedInPeriod = (clone $unauthorizedQuery)->whereBetween('created_at', [$start, $end])->count();
        $unauthorizedWeek     = (clone $unauthorizedQuery)->where('created_at', '>=', now()->subDays(7))->count();
        $unknownQrToday       = $unauthorizedToday;
        $unknownQrInPeriod    = $unauthorizedInPeriod;

        $recentUnauthorized   = (clone $unauthorizedQuery)
            ->whereBetween('created_at', [$start, $end])
            ->latest('id')
            ->take(10)
            ->get()
            ->map(fn (AccessLog $log) => AlertController::present($log));

        $pendingOfflineCount  = OfflineRecord::pending()->count();

        return view('dashboard', compact(
            'totalKeys', 'availableKeys', 'borrowedKeys', 'missingKeys',
            'keys',
            'totalUsers', 'totalSchedules', 'totalAdmins',
            'dayLabels', 'schedulePerDay',
            'keyStatusData',
            'unauthorizedToday', 'unauthorizedInPeriod', 'unauthorizedWeek', 'unknownQrToday', 'unknownQrInPeriod',
            'recentUnauthorized',
            'pendingOfflineCount',
            'period', 'periodLabel', 'startDate', 'endDate'
        ));
    }
}
