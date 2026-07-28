<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\User;
use App\Models\Transaction;
use App\Models\AccessLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalKeys = Key::count();
        $availableKeys = Key::where('status', 'available')->count();
        $borrowedKeys = Key::where('status', 'borrowed')->count();
        $missingKeys = Key::where('status', 'missing')->count();

        $keys = Key::orderBy('slot_number')->get();

        $recentTransactions = Transaction::with(['user', 'key'])
            ->latest()
            ->take(10)
            ->get();

        $recentLogs = AccessLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        $todayBorrows = Transaction::whereDate('created_at', today())
            ->where('action', 'borrow')
            ->count();

        $todayReturns = Transaction::whereDate('created_at', today())
            ->where('action', 'return')
            ->count();

        // 12-month data aggregation for dashboard Chart.js dual bar chart
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyBorrows = [];
        $monthlyReturns = [];

        foreach (range(1, 12) as $m) {
            $bCount = Transaction::whereMonth('created_at', $m)
                ->whereYear('created_at', now()->year)
                ->where('action', 'borrow')
                ->count();

            $rCount = Transaction::whereMonth('created_at', $m)
                ->whereYear('created_at', now()->year)
                ->where('action', 'return')
                ->count();

            $monthlyBorrows[] = $bCount;
            $monthlyReturns[] = $rCount;
        }

        return view('dashboard', compact(
            'totalKeys', 'availableKeys', 'borrowedKeys', 'missingKeys',
            'keys', 'recentTransactions', 'recentLogs', 'todayBorrows', 'todayReturns',
            'months', 'monthlyBorrows', 'monthlyReturns'
        ));
    }
}
