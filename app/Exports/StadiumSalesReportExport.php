<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StadiumSalesReportExport implements
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
            ->setVertical('center')
            ->setWrapText(true);

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
                        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'N'] as $column) {
                            $sheet->mergeCells(
                                "{$column}{$startRow}:{$column}{$endRow}"
                            );

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

    private function getTransactions(): Collection
    {
        $startDate = $this->request->filled('start_date')
            ? Carbon::parse($this->request->start_date)->startOfDay()
            : null;

        $endDate = $this->request->filled('end_date')
            ? Carbon::parse($this->request->end_date)->endOfDay()
            : null;

        if ($this->request->filled('month')) {
            $year = (int) ($this->request->year ?: now()->year);
            $month = (int) $this->request->month;

            if ($month >= 1 && $month <= 12) {
                $startDate = Carbon::create($year, $month, 1)->startOfDay();
                $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            }
        } elseif ($this->request->filled('year')) {
            $year = (int) $this->request->year;

            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        }

        $query = Transaction::query()
            ->where('source', 'Stadium')
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->with([
                'customer',
                'items.productVariant.product',
                'items.productVariant.size',
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        $transactions = $query
            ->latest('transaction_date')
            ->get();

        $rows = collect();
        $number = 1;

        foreach ($transactions as $transaction) {
            $items = $transaction->items;

            if ($items->isEmpty()) {
                $items = collect([null]);
            }

            // Satu nomor untuk satu invoice, meskipun produknya banyak.
            $transactionNumber = $number++;
            $firstItem = true;

            foreach ($items as $item) {
                $variant = $item?->productVariant;
                $product = $variant?->product;

                $rows->push([
                    'no' => $transactionNumber,
                    'invoice' => $transaction->invoice_number
                        ?? $transaction->invoice
                        ?? '-',
                    'date' => $transaction->transaction_date
                        ? Carbon::parse($transaction->transaction_date)
                            ->setTimezone('Asia/Makassar')
                            ->format('d/m/Y H:i')
                        : '-',
                    'customer' => $transaction->customer?->name
                        ?? $transaction->shipping_name
                        ?? '-',
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
                    'total' => $firstItem
                        ? (float) $transaction->total
                        : null,
                ]);

                $firstItem = false;
            }
        }

        return $rows;
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