<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class SalesReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected Request $request;

    protected Collection $rows;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->rows = $this->getTransactions();
    }

    public function collection(): Collection
    {
        return $this->rows;
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
            'Nama Produk',
            'Ukuran',
            'Pcs',
            'Nama Custom',
            'Nomor Custom',
            'Patch',
            'Longsleeve',
            'Total',
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['invoice'],
            $row['date'],
            $row['customer'],
            $row['payment'],
            $row['status'],
            $row['product'],
            $row['size'],
            $row['qty'],
            $row['custom_name'],
            $row['custom_number'],
            $row['patch'],
            $row['longsleeve'],
            $row['total'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:N' . $sheet->getHighestRow())
            ->getAlignment()
            ->setHorizontal('center')
            ->setVertical('center');

        $sheet->getStyle('A1:N1')->applyFromArray([
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
        ]);

        $sheet->getStyle('A:N')->getAlignment()
            ->setWrapText(true);

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:N1');

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    private function getTransactions(): Collection
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
            'items.productVariant.product',
            'items.productVariant.size',
        ])
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->where(function ($q) {
                $q->whereNull('source')
                    ->orWhere('source', 'Website');
            })
            ->whereNotIn('invoice_number', [
                'INV-20261008-OHJ6PG',
                'INV-20261013-MBXNU0',
            ]);

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        $transactions = $query
            ->latest('transaction_date')
            ->get();

        $rows = collect();
        $number = 1;

        foreach ($transactions as $transaction) {
            $transactionNumber = $number++;
            $items = $transaction->items;

            // Tetap tampilkan transaksi meskipun tidak memiliki detail item.
            if ($items->isEmpty()) {
                $items = collect([null]);
            }

            $firstItem = true;

            foreach ($items as $item) {
                $variant = $item?->productVariant;
                $product = $variant?->product;

                $rows->push([
                    'no' => $transactionNumber,
                    'invoice' => $transaction->invoice_number ?? '-',
                    'date' => $transaction->transaction_date
                        ? Carbon::parse($transaction->transaction_date)
                            ->format('d/m/Y H:i')
                        : '-',
                    'customer' => $transaction->customer?->name ?? '-',
                    'payment' => $this->paymentLabel(
                        $transaction->payment_method
                    ),
                    'status' => $transaction->status ?? '-',

                    'product' => $product?->name ?? '-',
                    'size' => $variant?->size?->name ?? '-',
                    'qty' => (int) ($item?->qty ?? 0),

                    'custom_name' => filled($item?->custom_name)
                        ? $item->custom_name
                        : '-',

                    'custom_number' => filled($item?->custom_number)
                        ? (string) $item->custom_number
                        : '-',

                    'patch' => $item
                        ? ((bool) $item->is_patch ? 'Ya' : 'Tidak')
                        : '-',

                    'longsleeve' => $item
                        ? ((bool) $item->is_longsleeve ? 'Ya' : 'Tidak')
                        : '-',

                    // Total hanya muncul sekali per invoice.
                    'total' => $firstItem
                        ? (float) $transaction->total
                        : null,
                ]);

                $firstItem = false;
            }
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $startRow = 2;

                while ($startRow <= $lastRow) {
                    $invoice = $sheet->getCell("B{$startRow}")->getValue();

                    $endRow = $startRow;

                    while (
                        $endRow < $lastRow &&
                        $sheet->getCell('B' . ($endRow + 1))->getValue() === $invoice
                    ) {
                        $endRow++;
                    }

                    if ($endRow > $startRow) {
                        // Gabungkan informasi transaksi.
                        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'N'] as $column) {
                            $sheet->mergeCells(
                                "{$column}{$startRow}:{$column}{$endRow}"
                            );
                        }

                        // Ratakan isi sel hasil penggabungan.
                        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'N'] as $column) {
                            $sheet->getStyle(
                                "{$column}{$startRow}:{$column}{$endRow}"
                            )->getAlignment()
                                ->setHorizontal('center')
                                ->setVertical('center')
                                ->setWrapText(true);
                        }
                    }

                    $startRow = $endRow + 1;
                }
            },
        ];
    }

    private function paymentLabel($method): string
    {
        $method = strtoupper($method ?? '');

        return match ($method) {
            'CASH' => 'Tunai',
            'QRIS' => 'QRIS',
            'VA' => 'VA',
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
