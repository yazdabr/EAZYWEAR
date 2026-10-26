<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionOrderExport extends DefaultValueBinder implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithCustomValueBinder
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function headings(): array
    {
        return [
            'Tanggal Pesanan',
            'Invoice',
            'Status Transaksi',
            'Nama Pelanggan',
            'No. Telepon',
            'Metode Pengiriman',
            'Nama Produk',
            'Ukuran',
            'Jumlah',
            'Nama Punggung',
            'Nomor Punggung',
            'Longsleeve',
            'Patch',
        ];
    }

    /**
     * Simpan nomor telepon sebagai teks agar Excel tidak mengubahnya
     * menjadi notasi ilmiah (contoh: 6.28233E+12) atau menghilangkan nol awal.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getColumn() === 'E' && $cell->getRow() > 1 && $value !== null) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
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

            $shippingMethod = $transaction->shipping_method ?? '-';

            if ($shippingMethod === 'Ambil di Tempat' && $transaction->pickup_date) {
                $pickupDate = Carbon::parse($transaction->pickup_date)->format('d/m/Y');
                $pickupStart = $transaction->pickup_time_start
                    ? Carbon::parse($transaction->pickup_time_start)->format('H:i')
                    : null;
                $pickupEnd = $transaction->pickup_time_end
                    ? Carbon::parse($transaction->pickup_time_end)->format('H:i')
                    : null;

                $pickupSchedule = implode('–', array_filter([$pickupStart, $pickupEnd]));

                $shippingMethod .= ' — ' . $pickupDate;

                if ($pickupSchedule !== '') {
                    $shippingMethod .= ' (' . $pickupSchedule . ')';
                }
            }

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
                    $transaction->status ?? '-',
                    $customerName,
                    (string) $phone,
                    $shippingMethod,
                    $product?->name ?? '-',
                    $variant?->size?->name ?? '-',
                    (int) $item->qty,
                    (->custom_name ? mb_strtoupper($item->custom_name, 'UTF-8') : '-'),
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
            'A' => 18,
            'B' => 24,
            'C' => 22,
            'D' => 32,
            'E' => 20,
            'F' => 26,
            'G' => 52,
            'H' => 20,
            'I' => 14,
            'J' => 12,
            'K' => 24,
            'L' => 18,
            'M' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = max(1, $sheet->getHighestRow());

        // Ratakan semua isi sel ke tengah, baik horizontal maupun vertikal.
        $sheet->getStyle("A1:M{$lastRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Format nomor telepon sebagai teks untuk menjaga digit dan nol di depan.
        $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()->setFormatCode('@');

        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'AE7C18']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Tambahkan tinggi baris agar data tidak terlihat terlalu rapat.
        $sheet->getRowDimension(1)->setRowHeight(36);
        for ($row = 2; $row <= $lastRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(30);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:M1');

        return [];
    }
}
