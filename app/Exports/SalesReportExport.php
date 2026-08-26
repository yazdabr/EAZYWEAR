<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnFormatting
{
    protected Request $request;
    protected Enumerable $transactions;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->transactions = $this->getTransactions();
    }

    public function collection(): Enumerable
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'No',
            'Invoice',
            'Tanggal',
            'Pelanggan',
            'Pembayaran',
            'Status',
            'Total',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->invoice_number,
            $transaction->transaction_date
                ? Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i')
                : '-',
            $transaction->customer?->name ?? '-',
            $this->paymentLabel($transaction->payment_method),
            $transaction->status ?? '-',
            (float) $transaction->total,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'rgb' => 'AE7C18',
                    ],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    private function getTransactions(): Enumerable
    {
        $startDate = $this->request->filled('start_date')
            ? Carbon::parse($this->request->start_date)->startOfDay()
            : null;

        $endDate = $this->request->filled('end_date')
            ? Carbon::parse($this->request->end_date)->endOfDay()
            : null;

        if ($this->request->filled('month')) {
            $year = $this->request->filled('year')
                ? (int) $this->request->year
                : now()->year;

            $startDate = Carbon::create(
                $year,
                (int) $this->request->month,
                1
            )->startOfMonth();

            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($this->request->filled('year')) {
            $startDate = Carbon::create(
                (int) $this->request->year,
                1,
                1
            )->startOfYear();

            $endDate = $startDate->copy()->endOfYear();
        }

        $query = Transaction::with([
            'customer',
        ])->whereIn('status', [
            'PAID',
            'COMPLETED',
        ]);

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        return $query
            ->latest('transaction_date')
            ->get();
    }

    private function paymentLabel($method): string
    {
        $method = strtoupper($method ?? '');

        return match ($method) {
            'CASH' => 'Tunai',
            'QRIS' => 'QRIS',
            'TRANSFER',
            'TRANSFER_BANK',
            'BANK_TRANSFER' => 'Transfer Bank',
            'EDC' => 'EDC',
            default => ucwords(
                strtolower(
                    str_replace('_', ' ', $method)
                )
            ),
        };
    }
}