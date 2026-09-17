<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Exports\ProductionReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductionReportController extends Controller
{
    /**
     * Menampilkan laporan produksi
     */
    public function index(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $query = $this->productionQuery();

        $this->applyDateFilter(
            $query,
            $startDate,
            $endDate
        );

        $productions = $query
            ->latest('period_start')
            ->get();

        $summary = $this->buildSummary($productions);

        $years = $this->getAvailableYears();

        return view('admin.production-reports.index', [
            'productions' => $productions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'years' => $years,
            ...$summary,
        ]);
    }


    /**
     * Menampilkan laporan produksi untuk dicetak
     */
    public function print(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $query = $this->productionQuery();

        $this->applyDateFilter(
            $query,
            $startDate,
            $endDate
        );

        $productions = $query
            ->latest('period_start')
            ->get();

        $summary = $this->buildSummary($productions);

        $reportProductions = $productions->map(
            function ($production) {
                $sizeDetails = $production->items
                    ->map(function ($item) {
                        $sizeName = $item->size?->name ?? '-';

                        return $sizeName
                            . ': '
                            . number_format(
                                (int) $item->quantity,
                                0,
                                ',',
                                '.'
                            )
                            . ' pcs';
                    })
                    ->implode(', ');

                return [
                    'code' => $production->production_code ?? '-',

                    'period_type' => $this->periodLabel(
                        $production->period_type
                    ),

                    'period_start' => $production->period_start
                        ? Carbon::parse(
                            $production->period_start
                        )->format('d M Y')
                        : '-',

                    'period_end' => $production->period_end
                        ? Carbon::parse(
                            $production->period_end
                        )->format('d M Y')
                        : '-',

                    'product_name' => $production->product_name ?? '-',

                    'category' => $production->category?->name ?? '-',

                    'sizes' => $sizeDetails ?: '-',

                    'total_quantity' => (int) (
                        $production->total_quantity ?? 0
                    ),

                    'status' => $this->statusLabel(
                        $production->status
                    ),
                ];
            }
        );

        return view('admin.production-reports.print', [
            'reportProductions' => $reportProductions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            ...$summary,
        ]);
    }


    /**
     * Query utama laporan produksi
     */
    private function productionQuery()
    {
        return ProductionRecord::query()
            ->with([
                'category',
                'items.size',
            ]);
    }


    /**
     * Menentukan rentang tanggal berdasarkan filter
     */
    private function resolveDateRange(Request $request): array
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse(
                $request->input('start_date')
            )->startOfDay()
            : null;

        $endDate = $request->filled('end_date')
            ? Carbon::parse(
                $request->input('end_date')
            )->endOfDay()
            : null;

        /*
        |--------------------------------------------------------------------------
        | Filter Bulan
        |--------------------------------------------------------------------------
        */

        if ($request->filled('month')) {
            $year = $request->filled('year')
                ? (int) $request->input('year')
                : now()->year;

            $startDate = Carbon::create(
                $year,
                (int) $request->input('month'),
                1
            )->startOfMonth();

            $endDate = $startDate
                ->copy()
                ->endOfMonth();
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Tahun
        |--------------------------------------------------------------------------
        */

        elseif ($request->filled('year')) {
            $year = (int) $request->input('year');

            $startDate = Carbon::create(
                $year,
                1,
                1
            )->startOfYear();

            $endDate = $startDate
                ->copy()
                ->endOfYear();
        }

        return [$startDate, $endDate];
    }


    /**
     * Menerapkan filter tanggal pada period_start
     */
    private function applyDateFilter(
        $query,
        ?Carbon $startDate,
        ?Carbon $endDate
    ): void {
        if ($startDate) {
            $query->where(
                'period_start',
                '>=',
                $startDate->toDateString()
            );
        }

        if ($endDate) {
            $query->where(
                'period_start',
                '<=',
                $endDate->toDateString()
            );
        }
    }


    /**
     * Menghitung ringkasan laporan
     */
    private function buildSummary($productions): array
    {
        $totalProductions = $productions->count();

        $totalQuantity = (int) $productions->sum(
            'total_quantity'
        );

        $completedProductions = $productions
            ->filter(function ($production) {
                return strtolower(
                    (string) $production->status
                ) === 'completed';
            })
            ->count();

        $inProgressProductions = $productions
            ->filter(function ($production) {
                return strtolower(
                    (string) $production->status
                ) === 'in_progress';
            })
            ->count();

        $totalProducts = $productions
            ->pluck('product_name')
            ->filter()
            ->unique()
            ->count();

        return [
            'totalProductions' => $totalProductions,
            'totalQuantity' => $totalQuantity,
            'completedProductions' => $completedProductions,
            'inProgressProductions' => $inProgressProductions,
            'totalProducts' => $totalProducts,
        ];
    }


    /**
     * Mengambil daftar tahun yang tersedia
     */
    private function getAvailableYears()
    {
        return ProductionRecord::query()
            ->whereNotNull('period_start')
            ->selectRaw(
                'YEAR(period_start) as year'
            )
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }


    /**
     * Label periode
     */
    private function periodLabel(?string $periodType): string
    {
        return match (strtolower((string) $periodType)) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
            default => '-',
        };
    }


    /**
     * Label status
     */
    private function statusLabel(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'planned' => 'Direncanakan',
            'in_progress' => 'Dalam Proses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(
                str_replace(
                    '_',
                    ' ',
                    strtolower((string) $status)
                )
            ) ?: '-',
        };
    }
    public function export(Request $request)
    {
        $filename =
            'laporan-produksi-' .
            now()->format('Y-m-d-His') .
            '.xlsx';

        return Excel::download(
            new ProductionReportExport($request),
            $filename
        );
    }
}