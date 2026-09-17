<?php

namespace App\Exports;

use App\Models\ProductionRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithEvents
{
    protected Request $request;
    protected $productions;
    protected $startDate;
    protected $endDate;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->startDate = null;
        $this->endDate = null;
        $this->resolveDateFilter();
    }

    /**
     * Menentukan filter tanggal, bulan, dan tahun.
     */
    protected function resolveDateFilter(): void
    {
        if ($this->request->filled('start_date')) {
            $this->startDate = Carbon::parse(
                $this->request->start_date
            )->startOfDay();
        }

        if ($this->request->filled('end_date')) {
            $this->endDate = Carbon::parse(
                $this->request->end_date
            )->endOfDay();
        }

        if ($this->request->filled('month')) {
            $year = $this->request->filled('year')
                ? (int) $this->request->year
                : now()->year;

            $this->startDate = Carbon::create(
                $year,
                (int) $this->request->month,
                1
            )->startOfMonth();

            $this->endDate = $this->startDate
                ->copy()
                ->endOfMonth();
        } elseif ($this->request->filled('year')) {
            $this->startDate = Carbon::create(
                (int) $this->request->year,
                1,
                1
            )->startOfYear();

            $this->endDate = $this->startDate
                ->copy()
                ->endOfYear();
        }
    }

    /**
     * Mengambil data produksi.
     */
    public function collection(): Enumerable
    {
        $query = ProductionRecord::with([
            'category',
            'items.size',
        ])->latest('period_start');

        if ($this->startDate) {
            $query->whereDate(
                'period_start',
                '>=',
                $this->startDate
            );
        }

        if ($this->endDate) {
            $query->whereDate(
                'period_end',
                '<=',
                $this->endDate
            );
        }

        $this->productions = $query->get();

        return $this->productions;
    }

    /**
     * Header tabel Excel.
     */
    public function headings(): array
    {
        return [
            'No.',
            'Kode Produksi',
            'Periode',
            'Produk',
            'Kategori',
            'Ukuran',
            'Jumlah Produksi',
            'Total Kuantitas',
            'Status',
            'Catatan',
        ];
    }

    /**
     * Mapping data ke tabel Excel.
     */
    public function map($production): array
    {
        $sizes = $production->items
            ->map(function ($item) {
                return ($item->size?->name ?? '-') . ': ' .
                    number_format(
                        (int) $item->quantity,
                        0,
                        ',',
                        '.'
                    ) . ' pcs';
            })
            ->implode(', ');

        $period = '-';

        if (
            $production->period_start &&
            $production->period_end
        ) {
            $period = Carbon::parse(
                $production->period_start
            )->format('d M Y') . ' s/d ' .
                Carbon::parse(
                    $production->period_end
                )->format('d M Y');
        }

        $statusLabel = match (
            strtolower((string) $production->status)
        ) {
            'planned' => 'Direncanakan',
            'in_progress' => 'Dalam Proses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(
                str_replace(
                    '_',
                    ' ',
                    (string) $production->status
                )
            ),
        };

        return [
            $production->id,
            $production->production_code ?? '-',
            $period,
            $production->product_name ?? '-',
            $production->category?->name ?? '-',
            $sizes ?: '-',
            (int) $production->total_quantity,
            (int) $production->total_quantity,
            $statusLabel,
            $production->notes ?? '-',
        ];
    }

    /**
     * Styling dasar tabel.
     */
    public function styles(Worksheet $sheet): ?array
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
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'C4902C',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    /**
     * Lebar kolom.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 24,
            'C' => 30,
            'D' => 35,
            'E' => 20,
            'F' => 40,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 35,
        ];
    }

    /**
     * Styling tambahan setelah sheet selesai dibuat.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                /*
                |--------------------------------------------------------------------------
                | Judul laporan
                |--------------------------------------------------------------------------
                */

                $sheet->insertNewRowBefore(1, 4);

                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');
                $sheet->mergeCells('A3:J3');

                $sheet->setCellValue('A1', 'EAZYWEAR');
                $sheet->setCellValue('A2', 'LAPORAN PRODUKSI');

                $periodLabel = 'Semua Periode';

                if ($this->startDate && $this->endDate) {
                    $periodLabel = $this->startDate->format('d M Y') .
                        ' s/d ' .
                        $this->endDate->format('d M Y');
                } elseif ($this->startDate) {
                    $periodLabel = 'Mulai ' .
                        $this->startDate->format('d M Y');
                } elseif ($this->endDate) {
                    $periodLabel = 'Sampai ' .
                        $this->endDate->format('d M Y');
                }

                $sheet->setCellValue(
                    'A3',
                    'Periode: ' . $periodLabel
                );

                /*
                |--------------------------------------------------------------------------
                | Styling judul
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:J1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => [
                            'rgb' => 'FFFFFF',
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '121212',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A2:J2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => [
                            'rgb' => 'C4902C',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A3:J3')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => [
                            'rgb' => '64748B',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Header tabel
                |--------------------------------------------------------------------------
                */

                $headerRow = 5;

                $sheet->getStyle(
                    "A{$headerRow}:{$highestColumn}{$headerRow}"
                )->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => [
                            'rgb' => 'FFFFFF',
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'C4902C',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'D1D5DB',
                            ],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Isi tabel
                |--------------------------------------------------------------------------
                */

                $dataStartRow = 6;
                $dataEndRow = $highestRow + 4;

                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle(
                        "A{$dataStartRow}:J{$dataEndRow}"
                    )->applyFromArray([
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => 'E2E8F0',
                                ],
                            ],
                        ],
                    ]);

                    $sheet->getStyle(
                        "A{$dataStartRow}:A{$dataEndRow}"
                    )->getAlignment()->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                    $sheet->getStyle(
                        "G{$dataStartRow}:I{$dataEndRow}"
                    )->getAlignment()->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Tinggi baris
                |--------------------------------------------------------------------------
                */

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(35);

                /*
                |--------------------------------------------------------------------------
                | Freeze header
                |--------------------------------------------------------------------------
                */

                $sheet->freezePane('A6');

                /*
                |--------------------------------------------------------------------------
                | Print setting
                |--------------------------------------------------------------------------
                */

                $sheet->getPageSetup()->setOrientation(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                );

                $sheet->getPageSetup()->setPaperSize(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                );

                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setRight(0.3);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.3);

                $sheet->getPageSetup()->setHorizontalCentered(true);

                /*
                |--------------------------------------------------------------------------
                | Footer
                |--------------------------------------------------------------------------
                */

                $sheet->getHeaderFooter()->setOddFooter(
                    '&L&EAZYWEAR&RHalaman &P dari &N'
                );
            },
        ];
    }
}