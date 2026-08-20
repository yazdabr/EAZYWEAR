<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : null;

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : null;

        if ($request->filled('month')) {
            $year = $request->filled('year')
                ? (int) $request->year
                : now()->year;

            $startDate = Carbon::create($year, (int) $request->month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($request->filled('year')) {
            $startDate = Carbon::create((int) $request->year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        }

        $query = Transaction::with([
            'customer',
            'items.productVariant.product.category',
            'items.productVariant.size',
            'items.productVariant.color',
        ])->whereIn('status', ['PAID', 'COMPLETED']);

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        $transactions = $query
            ->latest('transaction_date')
            ->get();

        $totalRevenue = (float) $transactions->sum('total');
        $totalTransactions = $transactions->count();

        $totalProductsSold = (int) $transactions
            ->flatMap(fn ($transaction) => $transaction->items)
            ->sum('qty');

        $averageOrderValue = $totalTransactions > 0
            ? $totalRevenue / $totalTransactions
            : 0;

        $revenueGrowth = 0;
        $transactionGrowth = 0;
        $averageGrowth = 0;
        $productGrowth = 0;

        if ($startDate && $endDate) {
            $days = max(1, $startDate->diffInDays($endDate) + 1);

            $previousStart = $startDate->copy()->subDays($days)->startOfDay();
            $previousEnd = $startDate->copy()->subDay()->endOfDay();

            $previousTransactions = Transaction::whereIn('status', ['PAID', 'COMPLETED'])
                ->whereBetween('transaction_date', [$previousStart, $previousEnd])
                ->with('items')
                ->get();

            $previousRevenue = (float) $previousTransactions->sum('total');
            $previousTransactionCount = $previousTransactions->count();

            $previousProductsSold = (int) $previousTransactions
                ->flatMap(fn ($transaction) => $transaction->items)
                ->sum('qty');

            $previousAverage = $previousTransactionCount > 0
                ? $previousRevenue / $previousTransactionCount
                : 0;

            $revenueGrowth = $this->growth($previousRevenue, $totalRevenue);
            $transactionGrowth = $this->growth($previousTransactionCount, $totalTransactions);
            $averageGrowth = $this->growth($previousAverage, $averageOrderValue);
            $productGrowth = $this->growth($previousProductsSold, $totalProductsSold);
        }

        $topProducts = $this->getTopProducts($transactions);
        $salesCategories = $this->getSalesCategories($transactions);
        $paymentMethods = $this->getPaymentMethods($transactions);
        $monthlyRevenue = $this->getMonthlyRevenue($transactions);

        $years = Transaction::query()
            ->selectRaw('YEAR(transaction_date) as year')
            ->whereIn('status', ['PAID', 'COMPLETED'])
            ->whereNotNull('transaction_date')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('admin.sales-reports.index', compact(
            'transactions',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalTransactions',
            'totalProductsSold',
            'averageOrderValue',
            'revenueGrowth',
            'transactionGrowth',
            'averageGrowth',
            'productGrowth',
            'topProducts',
            'salesCategories',
            'paymentMethods',
            'monthlyRevenue',
            'years'
        ));
    }

    private function growth($previous, $current)
    {
        if ((float) $previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getTopProducts($transactions)
    {
        $products = [];
        $totalRevenue = 0;

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $variant = $item->productVariant;
                $product = $variant?->product;

                if (!$product) {
                    continue;
                }

                $id = $product->id;
                $revenue = (float) $item->subtotal;
                $qty = (int) $item->qty;

                $totalRevenue += $revenue;

                if (!isset($products[$id])) {
                    $products[$id] = [
                        'name' => $product->name,
                        'units' => 0,
                        'revenue' => 0,
                    ];
                }

                $products[$id]['units'] += $qty;
                $products[$id]['revenue'] += $revenue;
            }
        }

        return collect($products)
            ->sortByDesc('units')
            ->take(5)
            ->values()
            ->map(function ($product) use ($totalRevenue) {
                $product['percentage'] = $totalRevenue > 0
                    ? round(($product['revenue'] / $totalRevenue) * 100)
                    : 0;

                return $product;
            });
    }

    private function getSalesCategories($transactions)
    {
        $categories = [];
        $totalProducts = 0;
        $totalRevenue = 0;

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $product = $item->productVariant?->product;
                $category = $product?->category;

                if (!$product || !$category) {
                    continue;
                }

                $id = $category->id;
                $qty = (int) $item->qty;
                $revenue = (float) $item->subtotal;

                $totalProducts += $qty;
                $totalRevenue += $revenue;

                if (!isset($categories[$id])) {
                    $categories[$id] = [
                        'name' => $category->name,
                        'products' => 0,
                        'revenue' => 0,
                    ];
                }

                $categories[$id]['products'] += $qty;
                $categories[$id]['revenue'] += $revenue;
            }
        }

        return collect($categories)
            ->sortByDesc('products')
            ->values()
            ->map(function ($category) use ($totalProducts) {
                $category['percentage'] = $totalProducts > 0
                    ? round(($category['products'] / $totalProducts) * 100)
                    : 0;

                return $category;
            });
    }

    private function getPaymentMethods($transactions)
    {
        $methods = [];
        $totalTransactions = $transactions->count();

        foreach ($transactions as $transaction) {
            $method = strtoupper($transaction->payment_method ?? 'UNKNOWN');

            if (!isset($methods[$method])) {
                $methods[$method] = [
                    'name' => $this->paymentLabel($method),
                    'transactions' => 0,
                    'revenue' => 0,
                    'percentage' => 0,
                    'icon' => $this->paymentIcon($method),
                ];
            }

            $methods[$method]['transactions']++;
            $methods[$method]['revenue'] += (float) $transaction->total;
        }

        return collect($methods)
            ->sortByDesc('revenue')
            ->values()
            ->map(function ($method) use ($totalTransactions) {
                $method['percentage'] = $totalTransactions > 0
                    ? round(($method['transactions'] / $totalTransactions) * 100)
                    : 0;

                return $method;
            });
    }

    private function paymentLabel($method)
    {
        return match ($method) {
            'CASH' => 'Tunai',
            'QRIS' => 'QRIS',
            'TRANSFER', 'TRANSFER_BANK', 'BANK_TRANSFER' => 'Transfer Bank',
            'EDC' => 'EDC',
            default => ucwords(strtolower(str_replace('_', ' ', $method))),
        };
    }

    private function paymentIcon($method)
    {
        return match ($method) {
            'CASH' => 'banknotes',
            'QRIS' => 'qr-code',
            'TRANSFER', 'TRANSFER_BANK', 'BANK_TRANSFER' => 'building-library',
            'EDC' => 'credit-card',
            default => 'credit-card',
        };
    }

    private function getMonthlyRevenue($transactions)
    {
        $months = [];

        foreach ($transactions as $transaction) {
            if (!$transaction->transaction_date) {
                continue;
            }

            $date = Carbon::parse($transaction->transaction_date);
            $key = $date->format('Y-m');

            if (!isset($months[$key])) {
                $months[$key] = [
                    'label' => $date->translatedFormat('M Y'),
                    'revenue' => 0,
                ];
            }

            $months[$key]['revenue'] += (float) $transaction->total;
        }

        return collect($months)
            ->sortKeys()
            ->values();
    }

    public function print(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
            : null;

        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
            : null;

        if ($request->filled('month')) {
            $year = $request->filled('year')
                ? (int) $request->year
                : now()->year;

            $startDate = \Carbon\Carbon::create($year, (int) $request->month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($request->filled('year')) {
            $startDate = \Carbon\Carbon::create((int) $request->year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        }

        $query = Transaction::with([
            'customer',
            'items.productVariant.product',
        ])->whereIn('status', ['PAID', 'COMPLETED']);

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        $transactions = $query
            ->latest('transaction_date')
            ->get();

        $totalRevenue = (float) $transactions->sum('total');
        $totalTransactions = $transactions->count();

        $totalProductsSold = (int) $transactions
            ->flatMap(fn ($transaction) => $transaction->items)
            ->sum('qty');

        $averageOrderValue = $totalTransactions > 0
            ? $totalRevenue / $totalTransactions
            : 0;

        $reportTransactions = $transactions->map(function ($transaction) {
            return [
                'invoice' => $transaction->invoice_number,
                'date' => $transaction->transaction_date
                    ? $transaction->transaction_date->format('d M Y, H:i')
                    : '-',
                'customer' => $transaction->customer?->name ?? '-',
                'payment' => $transaction->payment_method ?? '-',
                'status' => $transaction->status ?? '-',
                'total' => 'Rp ' . number_format(
                    $transaction->total,
                    0,
                    ',',
                    '.'
                ),
            ];
        });

        return view('admin.sales-reports.print', compact(
            'transactions',
            'reportTransactions',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalTransactions',
            'totalProductsSold',
            'averageOrderValue'
        ));
    }
}