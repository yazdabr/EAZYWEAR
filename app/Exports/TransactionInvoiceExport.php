<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionInvoiceExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    private int $headerRow = 5;
    private int $itemStartRow = 6;
    private int $itemEndRow = 6;
    private int $footerStartRow = 12;

    public function __construct(
        private readonly Transaction $transaction
    ) {
    }

    public function title(): string
    {
        return substr('Surat Jalan ' . $this->transaction->invoice_number, 0, 31);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 47,
            'B' => 10,
            'C' => 12,
            'D' => 35,
        ];
    }

    public function array(): array
    {
        $t = $this->transaction;

        $date = $t->transaction_date
            ? Carbon::parse($t->transaction_date)
                ->setTimezone('Asia/Makassar')
                ->format('d/m/Y')
            : now('Asia/Makassar')->format('d/m/Y');

        $customerName = $t->shipping_name
            ?? $t->customer?->name
            ?? '-';

        /*
         * Gabungkan item berdasarkan nama produk.
         * Qty dijumlahkan dan rincian ukuran dirangkum
         * ke dalam kolom Keterangan.
         */
        $groups = [];

        foreach ($t->items as $item) {
            $variant = $item->productVariant;
            $product = $variant?->product;

            $productName = $product?->name ?? 'Produk';
            $key = mb_strtolower(trim($productName));

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'name' => $productName,
                    'qty' => 0,
                    'sizes' => [],
                    'addons' => [],
                ];
            }

            $qty = (int) $item->qty;
            $groups[$key]['qty'] += $qty;

            $size = trim((string) ($variant?->size?->name ?? ''));

            if ($size !== '') {
                if (!isset($groups[$key]['sizes'][$size])) {
                    $groups[$key]['sizes'][$size] = 0;
                }

                $groups[$key]['sizes'][$size] += $qty;
            }

            $addons = [];

            if ($item->custom_name) {
                $addons[] = 'Nama: ' . mb_strtoupper($item->custom_name, 'UTF-8');
            }

            if ($item->custom_number) {
                $addons[] = 'No: ' . $item->custom_number;
            }

            if ($item->is_longsleeve) {
                $addons[] = 'Longsleeve';
            }

            if ($item->is_patch) {
                $addons[] = 'Patch';
            }

            foreach ($addons as $addon) {
                if (!in_array($addon, $groups[$key]['addons'], true)) {
                    $groups[$key]['addons'][] = $addon;
                }
            }
        }

        $rows = [
            [
                'CV. EAZYWEAR INDONESIA',
                '',
                'INVOICE',
                'No. : ' . $t->invoice_number,
            ],
            [
                'Jl. Asang Permai Km. 11.200, Kertak Hanyar 70654',
                '',
                '',
                'Tanggal : ' . $date,
            ],
            [
                'eazywearproduction@gmail.com',
                '',
                '',
                'Customer : ' . $customerName,
            ],
            ['', '', '', ''],
            ['Deskripsi', 'Qty', 'Satuan', 'Keterangan'],
        ];

        foreach ($groups as $group) {
            $sizeDetails = [];

            foreach ($group['sizes'] as $size => $qty) {
                $sizeDetails[] = $size . ':' . $qty;
            }

            $description = $group['name'];

            if (!empty($group['addons'])) {
                $description .= "\n" . implode(', ', $group['addons']);
            }

            $rows[] = [
                $description,
                $group['qty'],
                'PCS',
                implode(', ', $sizeDetails),
            ];
        }

        $this->itemEndRow = max(
            $this->itemStartRow,
            count($rows)
        );

        // Ruang kosong sebelum footer
        for ($i = 0; $i < 7; $i++) {
            $rows[] = ['', '', '', ''];
        }
        $rows[] = [
            'Apabila ada ketidakjelasan dari surat jalan ini dapat menghubungi',
            '',
            'Hormat Kami',
            'Penerima',
        ];
        $rows[] = [
            '(0857-5443-1105)',
            '',
            '',
            '',
        ];
        $rows[] = ['', '', '(Eazywear)', '(........................)'];

        $this->footerStartRow = $this->itemEndRow + 8;

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Header perusahaan
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A3:B3');
        $sheet->mergeCells('C1:C3');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
                'color' => ['rgb' => '536AA0'],
            ],
        ]);

        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => [
                'size' => 9,
                'color' => ['rgb' => '536AA0'],
            ],
        ]);

        $sheet->getStyle('C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '536AA0'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('D1:D3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 9,
                'color' => ['rgb' => '536AA0'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(8);

        // Header tabel
        $sheet->getStyle('A5:D5')->applyFromArray([
            'font' => [
                'bold' => false,
                'size' => 10,
                'color' => ['rgb' => '536AA0'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '536AA0'],
                ],
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '536AA0'],
                ],
            ],
        ]);

        $sheet->getRowDimension(5)->setRowHeight(22);

        // Isi produk
        if ($this->itemEndRow >= $this->itemStartRow) {
            $sheet->getStyle(
                "A{$this->itemStartRow}:D{$this->itemEndRow}"
            )->applyFromArray([
                'font' => [
                    'size' => 9,
                    'color' => ['rgb' => '536AA0'],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle(
                "B{$this->itemStartRow}:C{$this->itemEndRow}"
            )->getAlignment()->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

            $sheet->getStyle("D{$this->itemStartRow}:D{$this->itemEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Garis hanya di bawah header tabel, bukan di setiap baris produk.
            $sheet->getStyle('A5:D5')->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('536AA0');
        }

        // Beri ruang kosong agar tampilan menyerupai surat jalan cetak.
        for ($row = $this->itemStartRow; $row <= $this->itemEndRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        $footer = $this->footerStartRow;
        $contactRow = $footer + 1;
        $signatureRow = $footer + 2;

        $sheet->mergeCells("A{$footer}:B{$footer}");
        $sheet->mergeCells("A{$contactRow}:B{$contactRow}");

        $sheet->getStyle("A{$footer}:D{$signatureRow}")->applyFromArray([
            'font' => [
                'size' => 9,
                'color' => ['rgb' => '536AA0'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Garis pemisah di atas bagian tanda tangan
        $sheet->getStyle("A{$footer}:D{$footer}")
            ->getBorders()->getTop()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('536AA0');

        $sheet->getStyle("A{$footer}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle("A{$contactRow}:B{$contactRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("C{$footer}:D{$footer}")->getFont()
            ->setBold(false);

        $sheet->getStyle("C{$signatureRow}:D{$signatureRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_BOTTOM);

        $sheet->getRowDimension($footer)->setRowHeight(30);
        $sheet->getRowDimension($contactRow)->setRowHeight(22);
        $sheet->getRowDimension($signatureRow)->setRowHeight(48);

        // Pengaturan cetak
        $sheet->setShowGridLines(false);

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $sheet->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_STATEMENT);

        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getPageSetup()->setFitToPage(true);

        $sheet->getPageMargins()->setTop(0.25);
        $sheet->getPageMargins()->setBottom(0.25);
        $sheet->getPageMargins()->setLeft(0.25);
        $sheet->getPageMargins()->setRight(0.25);

        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setVerticalCentered(false);

        $sheet->getPageSetup()->setPrintArea("A1:D{$lastRow}");

        return [];
    }
}
