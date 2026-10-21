<?php

namespace App\\Exports;

use App\\Models\\Transaction;
use Carbon\\Carbon;
use Maatwebsite\\Excel\\Concerns\\FromArray;
use Maatwebsite\\Excel\\Concerns\\WithColumnWidths;
use Maatwebsite\\Excel\\Concerns\\WithHeadings;
use Maatwebsite\\Excel\\Concerns\\WithStyles;
use PhpOffice\\PhpSpreadsheet\\Style\\Alignment;
use PhpOffice\\PhpSpreadsheet\\Style\\Fill;
use PhpOffice\\PhpSpreadsheet\\Worksheet\\Worksheet;

class ProductionOrderExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function headings(): array
    {
        return [
            'Tanggal Pesanan',
            'Invoice',
            'Nama Pelanggan',
            'No. Telepon',
            'Metode Pengiriman',
            'Nama Produk',
            'Warna',
            'Ukuran',
            'Jumlah',
            'Nama Punggung',
            'Nomor Punggung',
            'Longsleeve',
            'Patch',
        ];
    }

    public function array(): array
    {
        $query = Transaction::query()
            ->with([
                'customer',
                'items.productVariant.product',
                'items.productVariant.size',
                'items.productVariant.color',
            ])
            ->whereIn('status', ['PAID', 'ORDER_PROCESSING'])
            ->whereHas('items');

        if (!empty($this->filters['product_id'])) {
            $query->whereHas('items.productVariant', function ($variant) {
                $variant->whereHas('product', fn ($product) => $product->whereKey($this->filters['product_id']));
            });
        }

        if (!empty($this->filters['month'])) {
            $query->whereMonth('transaction_date', (int) $this->filters['month']);
        }

        if (!empty($this->filters['year'])) {
            $query->whereYear('transaction_date', (int) $this->filters['year']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('transaction_date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('transaction_date', '<=', $this->filters['end_date']);
        }

        $transactions = $query->orderBy('transaction_date')->get();
        $rows = [];

        foreach ($transactions as $transaction) {
            $customerName = $transaction->shipping_name ?? $transaction->customer?->name ?? '-';
            $phone = $transaction->shipping_phone ?? $transaction->customer?->phone ?? '-';
            $date = $transaction->transaction_date
                ? Carbon::parse($transaction->transaction_date)->setTimezone('Asia/Makassar')->format('d/m/Y')
                : '-';

            foreach ($transaction->items as $item) {
                $variant = $item->productVariant;
                $product = $variant?->product;

                // Bila produk tertentu dipilih, jangan ekspor item produk lain dalam invoice yang sama.
                if (!empty($this->filters['product_id']) && (string) $product?->id !== (string) $this->filters['product_id']) {
                    continue;
                }

                $rows[] = [
                    $date,
                    $transaction->invoice_number ?? '-',
                    $customerName,
                    $phone,
                    $transaction->shipping_method ?? '-',
                    $product?->name ?? '-',
                    $variant?->color?->name ?? '-',
                    $variant?->size?->name ?? '-',
                    (int) $item->qty,
                    $item->custom_name ?: '-',
                    $item->custom_number ?: '-',
                    $item->is_longsleeve ? 'Ya' : 'Tidak',
                    $item->is_patch ? 'Ya' : 'Tidak',
                ];
            }
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16, 'B' => 22, 'C' => 28, 'D' => 18, 'E' => 22,
            'F' => 48, 'G' => 18, 'H' => 12, 'I' => 10, 'J' => 20,
            'K' => 16, 'L' => 14, 'M' => 12,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'AE7C18']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getStyle('A:M')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:M1');

        return [];
    }
}
