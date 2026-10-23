<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class OfflineTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query()
            ->with([
                'customer',
                'items.productVariant.product',
                'items.productVariant.size',
                'items.productVariant.color',
            ])
            ->where('source', 'Booth');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('month')) {
            $query->whereMonth('transaction_date', $request->integer('month'));
        }

        if ($request->filled('year')) {
            $query->whereYear('transaction_date', $request->integer('year'));
        }

        $summaryQuery = clone $query;

        $transactions = $query
            ->latest('transaction_date')
            ->paginate(10)
            ->withQueryString();


        $summary = [
            'total_transactions' => (clone $summaryQuery)->count(),
            'paid_transactions' => (clone $summaryQuery)
                ->where('status', 'PAID')
                ->count(),
            'pending_transactions' => (clone $summaryQuery)
                ->where('status', 'PENDING')
                ->count(),
            'total_revenue' => (clone $summaryQuery)
                ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
                ->sum('total'),
            'completed_orders' => (clone $summaryQuery)->where('status', 'PAID')->count(),
        ];

        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        $currentTransactions = Transaction::where('source', 'Booth')
            ->whereBetween('transaction_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])->count();

        $previousTransactions = Transaction::where('source', 'Booth')
            ->whereBetween('transaction_date', [
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ])->count();

        $currentRevenue = Transaction::where('source', 'Booth')
            ->whereBetween('transaction_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->sum('total');

        $previousRevenue = Transaction::where('source', 'Booth')
            ->whereBetween('transaction_date', [
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ])
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->sum('total');

        $calculateGrowth = function ($current, $previous) {
            if ((float) $previous === 0.0) {
                if ((float) $current === 0.0) {
                    return ['value' => '0%', 'positive' => true, 'neutral' => true];
                }

                return ['value' => '+100%', 'positive' => true, 'neutral' => false];
            }

            $growth = (($current - $previous) / $previous) * 100;

            return [
                'value' => ($growth >= 0 ? '+' : '') . number_format($growth, 1, ',', '.') . '%',
                'positive' => $growth >= 0,
                'neutral' => false,
            ];
        };

        $transactionGrowth = $calculateGrowth(
            $currentTransactions,
            $previousTransactions
        );

        $revenueGrowth = $calculateGrowth(
            $currentRevenue,
            $previousRevenue
        );

        $currentCompleted = Transaction::where('source', 'Booth')
            ->whereBetween('transaction_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->where('status', 'PAID')
            ->count();

        $previousCompleted = Transaction::where('source', 'Booth')
            ->whereBetween('transaction_date', [
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ])
            ->where('status', 'PAID')
            ->count();

        $completedGrowth = $calculateGrowth($currentCompleted, $previousCompleted);
        return view('admin.offline-transactions.index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'transactionGrowth' => $transactionGrowth,
            'revenueGrowth' => $revenueGrowth,
            'completedGrowth' => $completedGrowth,
        ]);

    }

    public function show(Transaction $transaction)
    {
        abort_unless($transaction->source === 'Booth', 404);

        $transaction->load([
            'customer',
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ]);

        return view('admin.offline-transactions.show', compact('transaction'));
    }
}
