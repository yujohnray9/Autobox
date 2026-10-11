<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\Transaction;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

        if ($request->query('format') === 'csv') {
            return $this->exportCsv($transactions);
        }

        return $this->exportXlsx($transactions);
    }

    protected function exportXlsx($transactions)
    {
        $filename = "autobox_transactions_" . date('Y-m-d_H-i-s') . ".xlsx";

        return response()->streamDownload(function () use ($transactions) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Key Transactions');

            // Header row
            $headers = [
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
            ];

            $sheet->fromArray($headers, null, 'A1');

            // Style header row: AutoBox purple background with white bold text
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '581C87'], // AutoBox deep purple
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(26);

            // Freeze top header row so it stays visible while scrolling
            $sheet->freezePane('A2');

            // Populate rows
            $rowIndex = 2;
            foreach ($transactions as $t) {
                $sheet->fromArray([
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
                ], null, "A{$rowIndex}");

                $sheet->getRowDimension($rowIndex)->setRowHeight(22);
                $rowIndex++;
            }

            $lastRow = max(2, $rowIndex - 1);

            // Center align ID, Slot, Action, Status, and date columns
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J2:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Set vertical center alignment and thin border for all cells
            $sheet->getStyle("A1:L{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Auto-size columns to prevent any '########' in Excel
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            if (ob_get_length()) {
                ob_end_clean();
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    protected function exportCsv($transactions)
    {
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
