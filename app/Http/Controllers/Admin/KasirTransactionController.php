<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KasirTransactionController extends Controller
{
    /**
     * Daftar transaksi yang berasal dari POS Kasir.
     */
    public function index(Request $request)
    {
        $query = Transaction::query()
            ->with([
                'customer',
                'items.productVariant.product.images',
                'items.productVariant.size',
                'items.productVariant.color',
            ])
            ->fromKasirPos();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $searchBy = $request->input('search_by', 'all');

            $query->where(function ($q) use ($search, $searchBy) {
                if ($searchBy === 'invoice') {
                    $q->where('invoice_number', 'like', "%{$search}%");
                } elseif ($searchBy === 'customer') {
                    $q->whereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%");
                    });
                } elseif ($searchBy === 'email') {
                    $q->whereHas('customer', function ($customer) use ($search) {
                        $customer->where('email', 'like', "%{$search}%");
                    });
                } else {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                }
            });
        }

        if ($request->filled('month')) {
            $query->whereMonth('transaction_date', $request->integer('month'));
        }

        if ($request->filled('year')) {
            $query->whereYear('transaction_date', $request->integer('year'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        $jerseyVersion = $request->input('jersey_version');

        if (in_array($jerseyVersion, ['Home', 'Away', 'Third'], true)) {
            $query->whereHas('items.productVariant.product', function ($product) use ($jerseyVersion) {
                $product->where('name', 'like', '%' . $jerseyVersion . '%');
            });
        }

        $summaryQuery = clone $query;

        $transactions = $query->latest('transaction_date')
            ->paginate(10)
            ->withQueryString();

        $transactions->through(function ($transaction) {
            return [
                'availability_label' => $transaction->items->isNotEmpty() ? 'READY' : '-',
                'id' => $transaction->id,
                'invoice' => $transaction->invoice_number,
                'date' => $transaction->transaction_date
                    ? $transaction->transaction_date
                        ->copy()
                        ->setTimezone('Asia/Makassar')
                        ->locale('id')
                        ->translatedFormat('d M Y H:i')
                    : '-',
                'customer' => $transaction->shipping_name
                    ?? $transaction->customer?->name
                    ?? 'Pelanggan Kasir',
                'customer_phone' => $transaction->shipping_phone
                    ?? $transaction->customer?->phone
                    ?? '-',
                'customer_email' => $transaction->shipping_email
                    ?? $transaction->customer?->email
                    ?? '-',
                'phone' => $transaction->shipping_phone
                    ?? $transaction->customer?->phone
                    ?? '-',
                'email' => $transaction->shipping_email
                    ?? $transaction->customer?->email
                    ?? '-',
                'payment' => $transaction->payment_method ?? '-',
                'status' => $transaction->status ?? 'PENDING',
                'subtotal' => (float) $transaction->subtotal,
                'discount' => (float) $transaction->discount,
                'shipping' => (float) $transaction->shipping,
                'total' => (float) $transaction->total,
                'source' => $transaction->source,
                'items' => $transaction->items->map(function ($item) {
                    $variant = $item->productVariant;
                    $product = $variant?->product;
                    $image = $product?->images
                        ?->sortBy([['is_thumbnail', 'desc'], ['sort_order', 'asc']])
                        ->first();

                    return [
                        'id' => $item->id,
                        'name' => $product?->name ?? '-',
                        'image' => $image?->image ? asset('storage/' . $image->image) : null,
                        'size' => $variant?->size?->name ?? '-',
                        'custom_name' => mb_strtoupper($item->custom_name ?? '', 'UTF-8'),
                        'custom_number' => $item->custom_number ?? '',
                        'is_longsleeve' => (bool) $item->is_longsleeve,
                        'is_patch' => (bool) ($item->is_patch ?? false),
                        'longsleeve_price' => (int) $item->longsleeve_price,
                        'qty' => (int) $item->qty,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                        'total' => (float) $item->subtotal,
                    ];
                })->values()->toArray(),
            ];
        });

        $totalTransactions = (clone $summaryQuery)->count();

        $jerseyQuantity = 0;

        if (in_array($jerseyVersion, ['Home', 'Away', 'Third'], true)) {
            $jerseyQuantity = TransactionItem::query()
                ->whereIn(
                    'transaction_id',
                    (clone $summaryQuery)->select('transactions.id')
                )
                ->whereHas('productVariant.product', function ($product) use ($jerseyVersion) {
                    $product->where('name', 'like', '%' . $jerseyVersion . '%');
                })
                ->sum('qty');
        }

        $totalRevenue = (clone $summaryQuery)
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->sum('total');

        // Transaksi POS lama dapat tersimpan sebagai PAID (sebelum alur
        // ORDER_COMPLETED diterapkan). Keduanya dianggap selesai untuk kasir.
        $completedOrders = (clone $summaryQuery)
            ->whereIn('status', [Transaction::ORDER_COMPLETED, 'PAID'])
            ->count();

        $pendingTransactions = (clone $summaryQuery)
            ->where('status', 'PENDING')
            ->count();

        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $posQuery = fn () => Transaction::query()->fromKasirPos();

        $currentTransactions = $posQuery()
            ->whereBetween('transaction_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->count();

        $previousTransactions = $posQuery()
            ->whereBetween('transaction_date', [
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ])
            ->count();

        $currentRevenue = $posQuery()
            ->whereBetween('transaction_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->sum('total');

        $previousRevenue = $posQuery()
            ->whereBetween('transaction_date', [
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ])
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->sum('total');

        $currentCompleted = $posQuery()
            ->whereBetween('transaction_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->whereIn('status', [Transaction::ORDER_COMPLETED, 'PAID'])
            ->count();

        $previousCompleted = $posQuery()
            ->whereBetween('transaction_date', [
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ])
            ->whereIn('status', [Transaction::ORDER_COMPLETED, 'PAID'])
            ->count();

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

        return view('admin.kasir-transactions.index', [
            'transactions' => $transactions,
            'totalTransactions' => $totalTransactions,
            'totalRevenue' => $totalRevenue,
            'completedOrders' => $completedOrders,
            'pendingTransactions' => $pendingTransactions,
            'transactionGrowth' => $calculateGrowth($currentTransactions, $previousTransactions),
            'revenueGrowth' => $calculateGrowth($currentRevenue, $previousRevenue),
            'completedGrowth' => $calculateGrowth($currentCompleted, $previousCompleted),
            'jerseyVersion' => $jerseyVersion,
            'jerseyQuantity' => $jerseyQuantity,
        ]);
    }

    /**
     * Detail transaksi POS Kasir.
     */
    public function show(Transaction $transaction)
    {
        abort_unless(
            $transaction->source === Transaction::SOURCE_POS,
            404
        );

        $transaction->load([
            'customer',
            'items.productVariant.product.images',
            'items.productVariant.size',
            'items.productVariant.color',
            'orderStatusHistories',
        ]);

        return view('admin.kasir-transactions.show', compact('transaction'));
    }

    /**
     * Cetak invoice transaksi POS Kasir.
     */
    public function print(string $invoice)
    {
        $transaction = Transaction::query()
            ->with([
                'customer',
                'items.productVariant.product.images',
                'items.productVariant.size',
                'items.productVariant.color',
            ])
            ->fromKasirPos()
            ->where('invoice_number', $invoice)
            ->firstOrFail();

        return view('admin.transactions.print', compact('transaction'));
    }
}
