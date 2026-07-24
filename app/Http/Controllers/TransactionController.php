<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'key']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('employee_id', 'like', "%{$search}%");
            })->orWhereHas('key', function ($q) use ($search) {
                $q->where('key_name', 'like', "%{$search}%")->orWhere('room_name', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    public function export()
    {
        $transactions = Transaction::with(['user', 'key'])->latest()->get();

        $filename = "autobox_transactions_" . date('Y-m-d_H-i-s') . ".csv";

        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['ID', 'User Name', 'Employee ID', 'Key Name', 'Room', 'Slot', 'Action', 'Status', 'Borrowed At', 'Returned At', 'Notes']);

        foreach ($transactions as $t) {
            fputcsv($handle, [
                $t->id,
                $t->user->name ?? 'N/A',
                $t->user->employee_id ?? 'N/A',
                $t->key->key_name ?? 'N/A',
                $t->key->room_name ?? 'N/A',
                $t->key->slot_number ?? 'N/A',
                strtoupper($t->action),
                strtoupper($t->status),
                $t->borrowed_at ? $t->borrowed_at->format('Y-m-d H:i:s') : 'N/A',
                $t->returned_at ? $t->returned_at->format('Y-m-d H:i:s') : 'N/A',
                $t->notes,
            ]);
        }

        fclose($handle);
        exit;
    }
}
