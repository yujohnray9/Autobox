<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Key;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // Daily borrow count past 7 days
        $dailyBorrows = Transaction::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('action', 'borrow')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Most borrowed keys
        $popularKeys = Transaction::select('key_id', DB::raw('count(*) as total'))
            ->where('action', 'borrow')
            ->groupBy('key_id')
            ->orderByDesc('total')
            ->with('key')
            ->take(5)
            ->get();

        // Key status summary
        $statusCounts = Key::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('reports.index', compact('dailyBorrows', 'popularKeys', 'statusCounts'));
    }
}
