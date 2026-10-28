<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StadiumSalesReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : null;

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : null;

        if ($request->filled('month')) {
            $year = (int) ($request->year ?: now()->year);
            $month = (int) $request->month;

            if ($month >= 1 && $month <= 12) {
                $startDate = Carbon::create($year, $month, 1)->startOfDay();
                $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            }
        } elseif ($request->filled('year')) {
            $year = (int) $request->year;

            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        }

        $query = Transaction::query()
            ->where('source', 'Stadium')
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->with([
                'customer',
                'items.productVariant.product.category',
                'items.productVariant.size',
                'items.productVariant.color',
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        $transactions = (clone $query)
            ->latest('transaction_date')
            ->get();

        $totalTransactions = $transactions->count();

        $totalProductsSold = $transactions->sum(
            fn ($transaction) => $transaction->items->sum('qty')
        );

        $transactionGrowth = 0;
        $productGrowth = 0;

        if ($startDate && $endDate) {
            $duration = $startDate->diffInSeconds($endDate) + 1;

            $previousStart = $startDate->copy()->subSeconds($duration);
            $previousEnd = $startDate->copy()->subSecond();

            $previousTransactions = Transaction::query()
                ->where('source', 'Stadium')
                ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
                ->whereBetween('transaction_date', [
                    $previousStart,
                    $previousEnd,
                ])
                ->with('items')
                ->get();

            $previousTotalTransactions = $previousTransactions->count();

            $previousTotalProductsSold = $previousTransactions->sum(
                fn ($transaction) => $transaction->items->sum('qty')
            );

            $transactionGrowth = $this->growth(
                $previousTotalTransactions,
                $totalTransactions
            );

            $productGrowth = $this->growth(
                $previousTotalProductsSold,
                $totalProductsSold
            );
        }

        $topProducts = $this->getTopProducts($transactions);
        $salesCategories = $this->getSalesCategories($transactions);

        $paginatedTransactions = (clone $query)
            ->latest('transaction_date')
            ->paginate(10)
            ->withQueryString();

        $years = Transaction::query()
            ->where('source', 'Stadium')
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->whereNotNull('transaction_date')
            ->selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years = collect([(int) now()->year]);
        }

        return view('admin.stadium-sales-reports.index', compact(
            'years',
            'startDate',
            'endDate',
            'totalTransactions',
            'transactionGrowth',
            'totalProductsSold',
            'productGrowth',
            'topProducts',
            'salesCategories',
            'transactions',
            'paginatedTransactions'
        ));
    }

    public function print(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : null;

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : null;

        if ($request->filled('month')) {
            $year = (int) ($request->year ?: now()->year);
            $month = (int) $request->month;

            if ($month >= 1 && $month <= 12) {
                $startDate = Carbon::create($year, $month, 1)->startOfDay();
                $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            }
        } elseif ($request->filled('year')) {
            $year = (int) $request->year;

            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        }

        $query = Transaction::query()
            ->where('source', 'Stadium')
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->with('customer');

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

        $totalTransactions = $transactions->count();

        $totalProductsSold = $transactions->sum(
            fn ($transaction) => $transaction->items()->sum('qty')
        );

        $reportTransactions = $transactions->map(function ($transaction) {
            return [
                'invoice' => $transaction->invoice_number
                    ?? $transaction->invoice
                    ?? '-',
                'date' => $transaction->transaction_date
                    ? Carbon::parse($transaction->transaction_date)->format('d M Y')
                    : '-',
                'customer' => $transaction->customer?->name ?? 'Pelanggan Umum',
                'status' => $transaction->status ?? '-',
                'total' => 'Rp ' . number_format(
                    (float) $transaction->total,
                    0,
                    ',',
                    '.'
                ),
            ];
        });

        return view('admin.stadium-sales-reports.print', compact(
            'startDate',
            'endDate',
            'totalTransactions',
            'totalProductsSold',
            'reportTransactions'
        ));
    }

    private function growth(float|int $previous, float|int $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getTopProducts(Collection $transactions): Collection
    {
        $products = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $variant = $item->productVariant;
                $product = $variant?->product;

                if (!$product) {
                    continue;
                }

                $productId = $product->id;

                if (!isset($products[$productId])) {
                    $products[$productId] = [
                        'name' => $product->name,
                        'units' => 0,
                        'revenue' => 0,
                        'sizes' => [],
                        'longsleeve_sizes' => [],
                    ];
                }

                $qty = (int) $item->qty;
                $sizeName = $variant?->size?->name ?? 'Tidak diketahui';

                $products[$productId]['units'] += $qty;
                $products[$productId]['revenue'] += (float) $item->subtotal;

                $products[$productId]['sizes'][$sizeName] =
                    ($products[$productId]['sizes'][$sizeName] ?? 0) + $qty;

                if ($item->is_longsleeve ?? false) {
                    $products[$productId]['longsleeve_sizes'][$sizeName] =
                        ($products[$productId]['longsleeve_sizes'][$sizeName] ?? 0)
                        + $qty;
                }
            }
        }

        return collect($products)
            ->sortByDesc('units')
            ->take(5)
            ->values();
    }

    private function getSalesCategories(Collection $transactions): Collection
    {
        $categories = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $product = $item->productVariant?->product;
                $category = $product?->category;

                if (!$category) {
                    continue;
                }

                $categoryId = $category->id;

                if (!isset($categories[$categoryId])) {
                    $categories[$categoryId] = [
                        'name' => $category->name,
                        'products' => 0,
                        'revenue' => 0,
                    ];
                }

                $categories[$categoryId]['products'] += (int) $item->qty;
                $categories[$categoryId]['revenue'] += (float) $item->subtotal;
            }
        }

        return collect($categories)
            ->sortByDesc('products')
            ->values();
    }
}
