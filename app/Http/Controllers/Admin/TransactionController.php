<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\StockMovement;
use App\Services\DokuService;
use App\Services\InventoryStockService;
use App\Services\TransactionPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with([
            'customer',
            'items.productVariant.product.images',
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

        $transactions = $query->latest('transaction_date')->paginate(10)->withQueryString();

        $transactions->through(function ($transaction) {
            return [
                'id' => $transaction->id,
                'invoice' => $transaction->invoice_number,
                'date' => $transaction->transaction_date
                    ? $transaction->transaction_date->copy()->setTimezone('Asia/Makassar')->format('d M Y H:i')
                    : '-',
                'customer' => $transaction->shipping_name ?? $transaction->customer?->name ?? '-',
                'customer_phone' => $transaction->shipping_phone ?? $transaction->customer?->phone ?? '-',
                'customer_email' => $transaction->shipping_email ?? $transaction->customer?->email ?? '-',
                'phone' => $transaction->shipping_phone ?? $transaction->customer?->phone ?? '-',
                'email' => $transaction->shipping_email ?? $transaction->customer?->email ?? '-',
                'shipping_address' => $transaction->shipping_address ?? '-',
                'shipping_district' => $transaction->shipping_district ?? '-',
                'shipping_city' => $transaction->shipping_city ?? '-',
                'shipping_province' => $transaction->shipping_province ?? '-',
                'shipping_postal_code' => $transaction->shipping_postal_code ?? '-',
                'shipping_method' => $transaction->shipping_method ?? '-',
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
                    $image = $product?->images?->sortBy([
                        ['is_thumbnail', 'desc'],
                        ['sort_order', 'asc'],
                    ])->first();

                    return [
                        'id' => $item->id,
                        'name' => $product?->name ?? '-',
                        'image' => $image?->image ? asset('storage/' . $image->image) : null,
                        'size' => $variant?->size?->name ?? '-',
                        'custom_name' => $item->custom_name ?? '',
                        'custom_number' => $item->custom_number ?? '',
                        'qty' => (int) $item->qty,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                        'total' => (float) $item->subtotal,
                    ];
                })->values()->toArray(),
            ];
        });

        $totalTransactions = Transaction::count();

        $totalRevenue = Transaction::where('status', 'PAID')
            ->sum('total');

        $completedOrders = Transaction::where('status', 'PAID')->count();

        $pendingTransactions = Transaction::where('status', 'PENDING')->count();
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentTransactions = Transaction::whereBetween('transaction_date', [
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth(),
        ])
        ->where('status', 'PAID')
        ->count();

        $previousTransactions = Transaction::whereBetween('transaction_date', [
            $previousMonth->copy()->startOfMonth(),
            $previousMonth->copy()->endOfMonth(),
        ])
        ->where('status', 'PAID')
        ->count();

        $currentRevenue = Transaction::whereBetween('transaction_date', [
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth(),
        ])
        ->where('status', 'PAID')
        ->sum('total');


        $previousRevenue = Transaction::whereBetween('transaction_date', [
            $previousMonth->copy()->startOfMonth(),
            $previousMonth->copy()->endOfMonth(),
        ])
        ->where('status', 'PAID')
        ->sum('total');

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

        $transactionGrowth = $calculateGrowth($currentTransactions, $previousTransactions);
        $revenueGrowth = $calculateGrowth($currentRevenue, $previousRevenue);
        $completedGrowth = $calculateGrowth($currentCompleted, $previousCompleted);

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'totalTransactions' => $totalTransactions,
            'totalRevenue' => $totalRevenue,
            'completedOrders' => $completedOrders,
            'pendingTransactions' => $pendingTransactions,
            'transactionGrowth' => $transactionGrowth,
            'revenueGrowth' => $revenueGrowth,
            'completedGrowth' => $completedGrowth,
        ]);
    }

    public function customerSearch(Request $request)
    {
        $search = trim($request->input('search', ''));

        if (strlen($search) < 2) {
            return response()->json(['data' => []]);
        }

        $customers = Customer::query()
            ->where('name', 'like', "%{$search}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json(['data' => $customers]);
    }

    public function create()
    {
        $variants = ProductVariant::with(['product.images', 'size', 'color', 'inventory'])
            ->whereHas('product', fn ($query) => $query->where('status', true))
            ->whereHas('inventory', fn ($query) => $query->where('stock', '>', 0))
            ->orderBy('id')
            ->get();

        return view('admin.transactions.create', ['variants' => $variants]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:30'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'shipping_data.address' => ['required', 'string'],
            'shipping_data.district' => ['required', 'string', 'max:255'],
            'shipping_data.city' => ['required', 'string', 'max:255'],
            'shipping_data.province' => ['required', 'string', 'max:255'],
            'shipping_data.postal_code' => ['required', 'string', 'max:10'],
            'shipping_data.method' => ['required', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.custom_name' => ['required', 'string', 'max:20', 'regex:/^[\pL\s]+$/u'],
            'items.*.custom_number' => ['required', 'string', 'max:2', 'regex:/^[0-9]{1,2}$/'],
        ], [
            'customer.name.required' => 'Nama pelanggan wajib diisi.',
            'customer.email.email' => 'Format email tidak valid.',
            'shipping_data.address.required' => 'Alamat pengiriman wajib diisi.',
            'shipping_data.district.required' => 'Kecamatan wajib diisi.',
            'shipping_data.city.required' => 'Kota atau kabupaten wajib diisi.',
            'shipping_data.province.required' => 'Provinsi wajib diisi.',
            'shipping_data.postal_code.required' => 'Kode pos wajib diisi.',
            'shipping_data.method.required' => 'Metode pengiriman wajib diisi.',
            'items.required' => 'Minimal satu produk harus dipilih.',
            'items.min' => 'Minimal satu produk harus dipilih.',
            'items.*.custom_name.required' => 'Nama jersey wajib diisi.',
            'items.*.custom_name.max' => 'Nama jersey maksimal 20 karakter.',
            'items.*.custom_name.regex' => 'Nama jersey hanya boleh berisi huruf dan spasi.',
            'items.*.custom_number.required' => 'Nomor punggung wajib diisi.',
            'items.*.custom_number.max' => 'Nomor punggung maksimal 2 digit.',
            'items.*.custom_number.regex' => 'Nomor punggung hanya boleh berisi angka.',
        ]);

        try {
            $transaction = DB::transaction(function () use ($validated) {
                $customerData = $validated['customer'];
                $shippingData = $validated['shipping_data'];

                $customer = null;

                if (! empty($customerData['phone'])) {
                    $customer = Customer::where('phone', $customerData['phone'])->first();
                }

                if (! $customer && ! empty($customerData['email'])) {
                    $customer = Customer::where('email', $customerData['email'])->first();
                }

                if (! $customer) {
                    $customer = Customer::create([
                        'name' => $customerData['name'],
                        'phone' => $customerData['phone'] ?? null,
                        'email' => $customerData['email'] ?? null,
                    ]);
                }

                $items = [];
                $subtotal = 0;

                foreach ($validated['items'] as $item) {
                    $variant = ProductVariant::with(['product', 'size'])
                        ->lockForUpdate()
                        ->findOrFail($item['product_variant_id']);

                    if (! $variant->product || ! $variant->product->status) {
                        throw ValidationException::withMessages(['items' => 'Produk yang dipilih tidak aktif.']);
                    }

                    $inventory = Inventory::where('product_variant_id', $variant->id)->lockForUpdate()->first();

                    if (! $inventory) {
                        throw ValidationException::withMessages(['items' => "Stok untuk {$variant->sku} tidak ditemukan."]);
                    }

                    $qty = (int) $item['qty'];
                    $stock = (int) $inventory->stock;

                    if ($stock < $qty) {
                        throw ValidationException::withMessages(['items' => "Stok {$variant->sku} tidak mencukupi. Stok tersedia: {$stock}."]);
                    }

                    $customName = trim($item['custom_name']);
                    $customNumber = trim($item['custom_number']);

                    if ($customName === '') {
                        throw ValidationException::withMessages(['items' => 'Nama jersey wajib diisi.']);
                    }

                    if (! preg_match('/^[\pL\s]+$/u', $customName)) {
                        throw ValidationException::withMessages(['items' => 'Nama jersey hanya boleh berisi huruf dan spasi.']);
                    }

                    if ($customNumber === '') {
                        throw ValidationException::withMessages(['items' => 'Nomor punggung wajib diisi.']);
                    }

                    if (! preg_match('/^[0-9]{1,2}$/', $customNumber)) {
                        throw ValidationException::withMessages(['items' => 'Nomor punggung hanya boleh berisi 1-2 angka.']);
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
                        'custom_name' => $customName,
                        'custom_number' => $customNumber,
                    ];
                }

                $discount = (float) ($validated['discount'] ?? 0);
                $shipping = (float) ($validated['shipping'] ?? 0);

                if ($discount > $subtotal) {
                    throw ValidationException::withMessages(['discount' => 'Diskon tidak boleh lebih besar dari subtotal.']);
                }

                $total = $subtotal - $discount + $shipping;
                $invoiceNumber = $this->generateInvoiceNumber();

                $transactionDate = Carbon::createFromFormat(
                    'Y-m-d\TH:i',
                    $validated['transaction_date'],
                    'Asia/Makassar'
                )->utc();

                $transaction = Transaction::create([
                    'customer_id' => $customer->id,
                    'invoice_number' => $invoiceNumber,
                    'transaction_date' => $transactionDate,
                    'payment_method' => $validated['payment_method'],
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'shipping' => $shipping,
                    'total' => $total,
                    'status' => 'PENDING',
                    'source' => 'Website',
                    'shipping_name' => $customerData['name'],
                    'shipping_email' => $customerData['email'] ?? null,
                    'shipping_phone' => $customerData['phone'] ?? null,
                    'shipping_address' => $shippingData['address'],
                    'shipping_district' => $shippingData['district'],
                    'shipping_city' => $shippingData['city'],
                    'shipping_province' => $shippingData['province'],
                    'shipping_postal_code' => $shippingData['postal_code'],
                    'shipping_method' => $shippingData['method'],
                ]);

                foreach ($items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_variant_id' => $item['variant']->id,
                        'custom_name' => $item['custom_name'],
                        'custom_number' => $item['custom_number'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
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
        } while (Transaction::where('invoice_number', $invoice)->exists());

        return $invoice;
    }

    public function show(Transaction $transaction)
    {
        $transaction->load([
            'customer',
            'items.productVariant.product.images',
            'items.productVariant.size',
            'items.productVariant.color',
            'orderStatusHistories',
        ]);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function print($invoice)
    {
        $transaction = Transaction::with([
            'customer',
            'items.productVariant.product.images',
            'items.productVariant.size',
            'items.productVariant.color',
        ])->where('invoice_number', $invoice)->firstOrFail();

        return view('admin.transactions.print', compact('transaction'));
    }

    public function cancel(
        Transaction $transaction,
        InventoryStockService $inventoryStockService
    ) {
        try {
            $cancelledTransaction = DB::transaction(function () use (
                $transaction,
                $inventoryStockService
            ) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTransaction->status === 'CANCELLED') {
                    throw ValidationException::withMessages([
                        'transaction' => 'Transaksi sudah dibatalkan sebelumnya.',
                    ]);
                }

                if ($lockedTransaction->status === 'COMPLETED') {
                    throw ValidationException::withMessages([
                        'transaction' => 'Transaksi yang sudah selesai tidak dapat dibatalkan.',
                    ]);
                }

                if ($lockedTransaction->status === 'PENDING') {
                    $lockedTransaction->update([
                        'status' => 'CANCELLED',
                    ]);

                    $lockedTransaction->addStatusHistory(
                        'ORDER_CANCELLED',
                        'Pesanan dibatalkan oleh admin.'
                    );

                    return $lockedTransaction->fresh();
                }

                if ($lockedTransaction->status === 'PAID') {
                    $inventoryStockService->restoreForTransaction(
                        $lockedTransaction,
                        "Stock restored due to cancellation - {$lockedTransaction->invoice_number}"
                    );

                    $lockedTransaction->addStatusHistory(
                        'ORDER_CANCELLED',
                        'Pesanan dibatalkan oleh admin dan stok dikembalikan.'
                    );

                    return $lockedTransaction->fresh();
                }

                throw ValidationException::withMessages([
                    'transaction' => 'Status transaksi tidak dapat dibatalkan.',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaction has been cancelled successfully.',
                'data' => [
                    'id' => $cancelledTransaction->id,
                    'status' => $cancelledTransaction->status,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi.',
            ], 500);
        }
    }

    public function destroy(Transaction $transaction)
    {
        if (in_array($transaction->status, ['PAID', 'COMPLETED'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi yang sudah dibayar atau selesai tidak dapat dihapus.',
            ], 422);
        }

        $hasStockMovements = StockMovement::query()
            ->where('transaction_id', $transaction->id)
            ->exists();

        if ($hasStockMovements) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi yang memiliki riwayat stok tidak dapat dihapus.',
            ], 422);
        }

        if ($transaction->status !== 'CANCELLED') {
            return response()->json([
                'success' => false,
                'message' => 'Status transaksi tidak dapat dihapus.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($transaction) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $hasStockMovements = StockMovement::query()
                    ->where('transaction_id', $lockedTransaction->id)
                    ->exists();

                if ($hasStockMovements) {
                    throw ValidationException::withMessages([
                        'transaction' => 'Transaksi memiliki riwayat stok dan tidak dapat dihapus.',
                    ]);
                }

                if ($lockedTransaction->status !== 'CANCELLED') {
                    throw ValidationException::withMessages([
                        'transaction' => 'Status transaksi tidak dapat dihapus.',
                    ]);
                }

                $lockedTransaction->items()->delete();
                $lockedTransaction->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaction has been deleted successfully.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi.',
            ], 500);
        }
    }

    public function checkPayment(
        Transaction $transaction,
        DokuService $dokuService,
        TransactionPaymentService $paymentService
    )
    {
        if ($transaction->status !== 'PENDING') {
            return response()->json([
                'message' => 'Transaksi sudah diproses.'
            ], 422);
        }


        $result = $dokuService->checkVirtualAccountStatus([
            'partnerServiceId' => trim(
                data_get(
                    $transaction->doku_response,
                    'virtualAccountData.partnerServiceId'
                )
            ),

            'customerNo' => data_get(
                $transaction->doku_response,
                'virtualAccountData.customerNo'
            ),

            'virtualAccountNo' => data_get(
                $transaction->doku_response,
                'virtualAccountData.virtualAccountNo'
            ),

            'trxId' => $transaction->invoice_number,
        ]);


        $response = $result['response'] ?? [];


        if (
            ($response['responseCode'] ?? null) === '2002600'
        ) {

            \Log::info('Manual DOKU payment confirmed', [
                'transaction_id' => $transaction->id,
                'invoice' => $transaction->invoice_number,
                'amount' => data_get($response, 'virtualAccountData.paidAmount.value'),
            ]);

            $paymentService->processSuccessfulPayment(
                $transaction,
                $response,
                'Manual DOKU payment check'
            );


            return response()->json([
                'success'=>true,
                'message'=>'Pembayaran berhasil dikonfirmasi.'
            ]);
        }


        return response()->json([
            'success'=>false,
            'message'=>'Pembayaran belum diterima DOKU.'
        ]);
    }
}