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
        ];

        return view('admin.offline-transactions.index', [
            'transactions' => $transactions,
            'summary' => $summary,
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
