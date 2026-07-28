<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\AccessLog;
use App\Models\Key;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // ── Summary Totals ──────────────────────────────────────
        $totalBorrows  = Transaction::where('action', 'borrow')->count();
        $totalReturns  = Transaction::where('action', 'return')->count();

        // QR Access logs (granted / denied) — column is 'result' not 'status'
        try {
            $totalGranted = AccessLog::where('result', 'granted')->count();
            $totalDenied  = AccessLog::where('result', 'denied')->count();
        } catch (\Exception $e) {
            $totalGranted = 0;
            $totalDenied  = 0;
        }

        // ── Daily Borrows: Last 30 Days ─────────────────────────
        $rawDaily = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('action', 'borrow')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Fill missing dates with 0
        $dailyLabels = [];
        $dailyData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $dailyLabels[] = Carbon::parse($date)->format('M d');
            $dailyData[]   = $rawDaily[$date] ?? 0;
        }

        // ── Most Borrowed Key Slots ─────────────────────────────
        $popularKeys = Transaction::select('key_id', DB::raw('count(*) as total'))
            ->where('action', 'borrow')
            ->groupBy('key_id')
            ->orderByDesc('total')
            ->with('key')
            ->take(5)
            ->get();

        // ── Top 10 Borrowers ────────────────────────────────────
        $topBorrowers = Transaction::select('user_id', DB::raw('count(*) as total'))
            ->where('action', 'borrow')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user')
            ->take(10)
            ->get();

        // ── Key Status Breakdown ────────────────────────────────
        $statusCounts   = Key::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $availableCount = $statusCounts['available'] ?? 0;
        $borrowedCount  = $statusCounts['borrowed']  ?? 0;
        $missingCount   = $statusCounts['missing']   ?? 0;

        return view('reports.index', compact(
            'totalBorrows', 'totalReturns', 'totalGranted', 'totalDenied',
            'dailyLabels', 'dailyData',
            'popularKeys', 'topBorrowers',
            'statusCounts', 'availableCount', 'borrowedCount', 'missingCount'
        ));
    }
}
