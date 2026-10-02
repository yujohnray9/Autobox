<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildFilteredQuery($request);

        $transactions = $query->latest()->paginate(20)->withQueryString();
        $keys = Key::orderBy('slot_number')->get();

        return view('transactions.index', compact('transactions', 'keys'));
    }

    public function export(Request $request)
    {
        $transactions = $this->buildFilteredQuery($request)->latest()->get();

        $filename = "autobox_transactions_" . date('Y-m-d_H-i-s') . ".csv";

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // CSV Header with Transaction Date column included
            fputcsv($handle, [
                'ID',
                'Transaction Date',
                'User Name',
                'Employee ID',
                'Key Name',
                'Room',
                'Slot',
                'Action',
                'Status',
                'Borrowed At',
                'Returned At',
                'Notes',
            ]);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->id,
                    $t->created_at ? $t->created_at->format('Y-m-d h:i A') : 'N/A',
                    $t->user->name ?? 'N/A',
                    $t->user->employee_id ?? 'N/A',
                    $t->key->key_name ?? 'N/A',
                    $t->key->room_name ?? 'N/A',
                    $t->key->slot_number ?? 'N/A',
                    strtoupper($t->action),
                    strtoupper($t->status),
                    $t->borrowed_at ? $t->borrowed_at->format('Y-m-d h:i A') : 'N/A',
                    $t->returned_at ? $t->returned_at->format('Y-m-d h:i A') : 'Not Returned Yet',
                    $t->notes ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function buildFilteredQuery(Request $request)
    {
        $query = Transaction::with(['user', 'key']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('key_id')) {
            $query->where('key_id', $request->key_id);
        }

        if ($request->filled('date')) {
            $date = $request->date;
            $query->where(function ($q) use ($date) {
                $q->whereDate('created_at', $date)
                  ->orWhereDate('borrowed_at', $date)
                  ->orWhereDate('returned_at', $date);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('employee_id', 'like', "%{$search}%");
                })
                ->orWhereHas('key', function ($kq) use ($search) {
                    $kq->where('key_name', 'like', "%{$search}%")
                       ->orWhere('room_name', 'like', "%{$search}%")
                       ->orWhere('slot_number', 'like', "%{$search}%");
                })
                ->orWhere('action', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('created_at', 'like', "%{$search}%")
                ->orWhere('borrowed_at', 'like', "%{$search}%")
                ->orWhere('returned_at', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
