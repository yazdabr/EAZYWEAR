<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $currentStart = $now->copy()->startOfMonth();
        $previousStart = $now->copy()->subMonth()->startOfMonth();
        $previousEnd = $now->copy()->subMonth()->endOfMonth();

        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalCustomers = Customer::count();

        $totalOrders = Transaction::whereIn('status', [
            'PAID',
            'COMPLETED',
        ])->count();

        $totalRevenue = Transaction::whereIn('status', [
            'PAID',
            'COMPLETED',
        ])->sum('total');

        $currentProducts = Product::whereBetween('created_at', [
            $currentStart,
            $now,
        ])->count();

        $previousProducts = Product::whereBetween('created_at', [
            $previousStart,
            $previousEnd,
        ])->count();

        $currentCategories = Category::whereBetween('created_at', [
            $currentStart,
            $now,
        ])->count();

        $previousCategories = Category::whereBetween('created_at', [
            $previousStart,
            $previousEnd,
        ])->count();

        $currentOrders = Transaction::whereIn('status', [
            'PAID',
            'COMPLETED',
        ])->whereBetween('transaction_date', [
            $currentStart,
            $now,
        ])->count();

        $previousOrders = Transaction::whereIn('status', [
            'PAID',
            'COMPLETED',
        ])->whereBetween('transaction_date', [
            $previousStart,
            $previousEnd,
        ])->count();

        $currentRevenue = Transaction::whereIn('status', [
            'PAID',
            'COMPLETED',
        ])->whereBetween('transaction_date', [
            $currentStart,
            $now,
        ])->sum('total');

        $previousRevenue = Transaction::whereIn('status', [
            'PAID',
            'COMPLETED',
        ])->whereBetween('transaction_date', [
            $previousStart,
            $previousEnd,
        ])->sum('total');

        $currentCustomers = Customer::whereBetween('created_at', [
            $currentStart,
            $now,
        ])->count();

        $previousCustomers = Customer::whereBetween('created_at', [
            $previousStart,
            $previousEnd,
        ])->count();

        $growthProducts = $this->calculateGrowth(
            $currentProducts,
            $previousProducts
        );

        $growthCategories = $this->calculateGrowth(
            $currentCategories,
            $previousCategories
        );

        $growthOrders = $this->calculateGrowth(
            $currentOrders,
            $previousOrders
        );

        $growthRevenue = $this->calculateGrowth(
            $currentRevenue,
            $previousRevenue
        );

        $growthCustomers = $this->calculateGrowth(
            $currentCustomers,
            $previousCustomers
        );

        $salesChart = $this->getSalesChart();

        $topProducts = $this->getTopProducts();

        $topProductsMax = $topProducts->max('total_qty') ?? 0;

        return view('admin.dashboard.index', compact(
            'totalProducts',
            'totalCategories',
            'totalOrders',
            'totalRevenue',
            'totalCustomers',
            'growthProducts',
            'growthCategories',
            'growthOrders',
            'growthRevenue',
            'growthCustomers',
            'salesChart',
            'topProducts',
            'topProductsMax'
        ));
    }

    private function calculateGrowth($current, $previous): array
    {
        if ($previous == 0 && $current == 0) {
            return [
                'value' => '0%',
                'positive' => true,
                'neutral' => true,
            ];
        }

        if ($previous == 0) {
            return [
                'value' => '+100%',
                'positive' => true,
                'neutral' => false,
            ];
        }

        $percentage = (($current - $previous) / $previous) * 100;

        return [
            'value' => ($percentage >= 0 ? '+' : '') . number_format($percentage, 1) . '%',
            'positive' => $percentage >= 0,
            'neutral' => false,
        ];
    }

    private function getSalesChart(): array
    {
        $start = Carbon::now()->subMonths(11)->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $sales = Transaction::select(
                DB::raw('YEAR(transaction_date) as year'),
                DB::raw('MONTH(transaction_date) as month'),
                DB::raw('SUM(total) as total')
            )
            ->whereIn('status', ['PAID', 'COMPLETED'])
            ->whereBetween('transaction_date', [$start, $end])
            ->groupBy(
                DB::raw('YEAR(transaction_date)'),
                DB::raw('MONTH(transaction_date)')
            )
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');

            $months[] = [
                'label' => $date->translatedFormat('M'),
                'month' => $date->month,
                'year' => $date->year,
                'total' => (float) ($sales[$key]->total ?? 0),
            ];
        }

        return $months;
    }

    private function getTopProducts()
    {
        return DB::table('transaction_items')
            ->join('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->leftJoin('product_images', function ($join) {
                $join->on('product_images.product_id', '=', 'products.id')
                    ->where('product_images.is_thumbnail', true);
            })
            ->whereIn('transactions.status', ['PAID', 'COMPLETED'])
            ->select(
                'products.id',
                'products.name',
                'product_images.image',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales')
            )
            ->groupBy(
                'products.id',
                'products.name',
                'product_images.image'
            )
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
    }
}