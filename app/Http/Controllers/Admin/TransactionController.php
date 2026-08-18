<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with([
            'customer',
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ]);

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
                                ->orWhere('email', 'like', "%{$search}%");
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

        $transactions = $query
            ->latest('transaction_date')
            ->paginate(10)
            ->withQueryString();

        $transactions->through(function ($transaction) {
            return [
                'id' => $transaction->id,
                'invoice' => $transaction->invoice_number,
                'date' => $transaction->transaction_date
                    ? $transaction->transaction_date->format('d M Y H:i')
                    : '-',
                'customer' => $transaction->customer?->name ?? '-',
                'customer_phone' => $transaction->customer?->phone ?? '-',
                'customer_email' => $transaction->customer?->email ?? '-',
                'payment' => $transaction->payment_method ?? '-',
                'status' => $transaction->status ?? 'PENDING',
                'subtotal' => (float) $transaction->subtotal,
                'discount' => (float) $transaction->discount,
                'shipping' => (float) $transaction->shipping,
                'total' => (float) $transaction->total,
                'source' => $transaction->source,
                'items' => $transaction->items->map(function ($item) {
                    $variant = $item->productVariant;

                    return [
                        'id' => $item->id,
                        'name' => $variant?->product?->name ?? '-',
                        'size' => $variant?->size?->name ?? '-',
                        'color' => $variant?->color?->name ?? '-',
                        'qty' => (int) $item->qty,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                        'total' => (float) $item->subtotal,
                    ];
                })->values()->toArray(),
            ];
        });

        $totalTransactions = Transaction::count();

        $totalRevenue = Transaction::sum('total');

        $completedOrders = Transaction::where('status', 'PAID')->count();

        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentTransactions = Transaction::whereBetween('transaction_date', [
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth(),
        ])->count();

        $previousTransactions = Transaction::whereBetween('transaction_date', [
            $previousMonth->copy()->startOfMonth(),
            $previousMonth->copy()->endOfMonth(),
        ])->count();

        $currentRevenue = Transaction::whereBetween('transaction_date', [
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth(),
        ])->sum('total');

        $previousRevenue = Transaction::whereBetween('transaction_date', [
            $previousMonth->copy()->startOfMonth(),
            $previousMonth->copy()->endOfMonth(),
        ])->sum('total');

        $currentCompleted = Transaction::whereBetween('transaction_date', [
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth(),
        ])->where('status', 'PAID')->count();

        $previousCompleted = Transaction::whereBetween('transaction_date', [
            $previousMonth->copy()->startOfMonth(),
            $previousMonth->copy()->endOfMonth(),
        ])->where('status', 'PAID')->count();

        $calculateGrowth = function ($current, $previous) {
            if ((float) $previous === 0.0) {
                if ((float) $current === 0.0) {
                    return [
                        'value' => '0%',
                        'positive' => true,
                        'neutral' => true,
                    ];
                }

                return [
                    'value' => '+100%',
                    'positive' => true,
                    'neutral' => false,
                ];
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

        $completedGrowth = $calculateGrowth(
            $currentCompleted,
            $previousCompleted
        );

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'totalTransactions' => $totalTransactions,
            'totalRevenue' => $totalRevenue,
            'completedOrders' => $completedOrders,
            'transactionGrowth' => $transactionGrowth,
            'revenueGrowth' => $revenueGrowth,
            'completedGrowth' => $completedGrowth,
        ]);
    }

    public function customerSearch(Request $request)
    {
        $search = trim($request->input('search', ''));

        if (strlen($search) < 2) {
            return response()->json([
                'data' => [],
            ]);
        }

        $customers = Customer::query()
            ->where('name', 'like', "%{$search}%")
            ->orderBy('name')
            ->limit(10)
            ->get([
                'id',
                'name',
                'email',
            ]);

        return response()->json([
            'data' => $customers,
        ]);
    }

    public function create()
    {
        $variants = ProductVariant::with([
            'product.images',
            'size',
            'color',
            'inventory',
        ])
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->whereHas('inventory', function ($query) {
                $query->where('stock', '>', 0);
            })
            ->orderBy('id')
            ->get();

        return view('admin.transactions.create', [
            'variants' => $variants,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer.name' => [
                'required',
                'string',
                'max:255',
            ],
            'customer.phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'customer.email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'transaction_date' => [
                'required',
                'date',
            ],
            'payment_method' => [
                'required',
                'string',
                'max:50',
            ],
            'source' => [
                'required',
                Rule::in([
                    'Android POS',
                    'Smart EDC',
                    'API',
                ]),
            ],
            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'shipping' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.product_variant_id' => [
                'required',
                'exists:product_variants,id',
            ],
            'items.*.qty' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        try {
            $transaction = DB::transaction(function () use ($validated) {
                $customerData = $validated['customer'];

                $customer = null;

                if (!empty($customerData['phone'])) {
                    $customer = Customer::where(
                        'phone',
                        $customerData['phone']
                    )->first();
                }

                if (!$customer && !empty($customerData['email'])) {
                    $customer = Customer::where(
                        'email',
                        $customerData['email']
                    )->first();
                }

                if (!$customer) {
                    $customer = Customer::create([
                        'name' => $customerData['name'],
                        'phone' => $customerData['phone'] ?? null,
                        'email' => $customerData['email'] ?? null,
                    ]);
                } else {
                    $customer->update([
                        'name' => $customerData['name'],
                        'phone' => $customerData['phone'] ?? $customer->phone,
                        'email' => $customerData['email'] ?? $customer->email,
                    ]);
                }

                $items = [];
                $subtotal = 0;

                foreach ($validated['items'] as $item) {
                    $variant = ProductVariant::with([
                        'product',
                        'size',
                        'color',
                    ])
                        ->lockForUpdate()
                        ->findOrFail($item['product_variant_id']);

                    if (!$variant->product || !$variant->product->status) {
                        throw ValidationException::withMessages([
                            'items' => 'Produk yang dipilih tidak aktif.',
                        ]);
                    }

                    $inventory = Inventory::where(
                        'product_variant_id',
                        $variant->id
                    )
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        throw ValidationException::withMessages([
                            'items' => "Stok untuk {$variant->sku} tidak ditemukan.",
                        ]);
                    }

                    $qty = (int) $item['qty'];
                    $stock = (int) $inventory->stock;

                    if ($stock < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok {$variant->sku} tidak mencukupi. Stok tersedia: {$stock}.",
                        ]);
                    }

                    $price = (float) $variant->price;
                    $itemSubtotal = $price * $qty;

                    $subtotal += $itemSubtotal;

                    $items[] = [
                        'variant' => $variant,
                        'inventory' => $inventory,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                $discount = (float) ($validated['discount'] ?? 0);
                $shipping = (float) ($validated['shipping'] ?? 0);

                if ($discount > $subtotal) {
                    throw ValidationException::withMessages([
                        'discount' => 'Diskon tidak boleh lebih besar dari subtotal.',
                    ]);
                }

                $total = $subtotal - $discount + $shipping;

                $invoiceNumber = $this->generateInvoiceNumber();

                $transaction = Transaction::create([
                    'customer_id' => $customer->id,
                    'invoice_number' => $invoiceNumber,
                    'transaction_date' => $validated['transaction_date'],
                    'payment_method' => $validated['payment_method'],
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'shipping' => $shipping,
                    'total' => $total,
                    'status' => 'PENDING',
                    'source' => $validated['source'],
                ]);

                foreach ($items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_variant_id' => $item['variant']->id,
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    $item['inventory']->decrement(
                        'stock',
                        $item['qty']
                    );
                }

                return $transaction;
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat.',
                'data' => [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                ],
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi.',
            ], 500);
        }
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $invoice = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (
            Transaction::where('invoice_number', $invoice)->exists()
        );

        return $invoice;
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    'PENDING',
                    'PAID',
                    'COMPLETED',
                    'CANCELLED',
                ]),
            ],
        ]);

        $transaction->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction status updated successfully.',
            'data' => [
                'id' => $transaction->id,
                'status' => $transaction->status,
            ],
        ]);
    }

    public function cancel(Transaction $transaction)
    {
        if ($transaction->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending transactions can be cancelled.',
            ], 422);
        }

        $transaction->update([
            'status' => 'CANCELLED',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction has been cancelled successfully.',
            'data' => [
                'id' => $transaction->id,
                'status' => $transaction->status,
            ],
        ]);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->items()->delete();

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction has been deleted successfully.',
        ]);
    }
}