<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionFulfillment;
use App\Models\TransactionShipment;
use App\Models\StockMovement;
use App\Models\TransactionNotification;
use App\Services\DokuService;
use App\Services\DokuQrisService;
use App\Services\InventoryStockService;
use App\Services\TransactionPaymentService;
use App\Services\TransactionCompletionService;
use App\Mail\OrderShippedMail;
use App\Mail\OrderProcessingMail;
use App\Services\FulfillmentHoldService;
use App\Models\FulfillmentHold;
use App\Services\BiteshipService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use App\Services\EmailArchiveService;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionCompletionService $transactionCompletionService,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $hiddenStatuses = $user?->hidden_transaction_statuses ?? [];

        $query = Transaction::with(['customer', 'items.productVariant.product.images', 'items.productVariant.size', 'items.productVariant.color']);

        if ($hiddenStatuses !== []) {
            $query->whereNotIn('status', $hiddenStatuses);
        }

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
                    $q->where('invoice_number', 'like', "%{$search}%")->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                    });
                }
            });
        }

        if ($request->filled('month')) $query->whereMonth('transaction_date', $request->integer('month'));
        if ($request->filled('year')) $query->whereYear('transaction_date', $request->integer('year'));
        if ($request->filled('customer_id')) $query->where('customer_id', $request->integer('customer_id'));
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('shipping_type')) {
            $shippingType = $request->input('shipping_type');

            if ($shippingType === 'pickup') {
                $query->where('shipping_method', 'Ambil di Tempat');
            } elseif ($shippingType === 'courier') {
                $query->where('shipping_method', 'Kurir');
            }
        }

        // Filter berdasarkan ketersediaan produk dalam transaksi.
        $availability = $request->input('availability');

        if ($availability === 'ready') {
            $query->whereHas(
                'items.productVariant.product',
                fn ($product) => $product->where('availability', 'ready')
            );
        } elseif ($availability === 'pre_order') {
            $query->whereHas(
                'items.productVariant.product',
                fn ($product) => $product->where('availability', 'pre_order')
            );
        } elseif ($availability === 'mixed') {
            $query
                ->whereHas(
                    'items.productVariant.product',
                    fn ($product) => $product->where('availability', 'ready')
                )
                ->whereHas(
                    'items.productVariant.product',
                    fn ($product) => $product->where('availability', 'pre_order')
                );
        }
        $summaryQuery = clone $query;
        $transactions = $query->latest('transaction_date')->paginate(10)->withQueryString();

        $transactions->through(function ($transaction) {
            $hasReady = $transaction->items->contains(
                fn ($item) => $item->productVariant?->product?->availability === 'ready'
            );

            $hasPreOrder = $transaction->items->contains(
                fn ($item) => $item->productVariant?->product?->availability === 'pre_order'
            );

            $availabilityLabel = match (true) {
                $hasReady && $hasPreOrder => 'READY + PO',
                $hasReady => 'READY',
                $hasPreOrder => 'PO',
                default => '-',
            };

            return [
                'availability_label' => $availabilityLabel,
                'id' => $transaction->id,
                'invoice' => $transaction->invoice_number,
                'date' => $transaction->transaction_date
                ? $transaction->transaction_date
                    ->copy()
                    ->setTimezone('Asia/Makassar')
                    ->locale('id')
                    ->translatedFormat('d M Y H:i')
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
                    $image = $product?->images?->sortBy([['is_thumbnail', 'desc'], ['sort_order', 'asc']])->first();

                    return [
                        'id' => $item->id,
                        'name' => $product?->name ?? '-',
                        'image' => $image?->image ? asset('storage/' . $image->image) : null,
                        'size' => $variant?->size?->name ?? '-',
                        'custom_name' => $item->custom_name ?? '',
                        'custom_number' => $item->custom_number ?? '',
                        'is_longsleeve' => (bool) $item->is_longsleeve,
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
        $totalRevenue = (clone $summaryQuery)->where('status', 'PAID')->sum('total');
        $completedOrders = (clone $summaryQuery)->where('status', Transaction::ORDER_COMPLETED)->count();
        $pendingTransactions = (clone $summaryQuery)->where('status', 'PENDING')->count();
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentTransactions = Transaction::whereBetween('transaction_date', [$currentMonth->copy()->startOfMonth(), $currentMonth->copy()->endOfMonth()])->where('status', 'PAID')->count();
        $previousTransactions = Transaction::whereBetween('transaction_date', [$previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth()])->where('status', 'PAID')->count();
        $currentRevenue = Transaction::whereBetween('transaction_date', [$currentMonth->copy()->startOfMonth(), $currentMonth->copy()->endOfMonth()])->where('status', 'PAID')->sum('total');
        $previousRevenue = Transaction::whereBetween('transaction_date', [$previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth()])->where('status', 'PAID')->sum('total');
        $currentCompleted = Transaction::whereBetween('transaction_date', [$currentMonth->copy()->startOfMonth(), $currentMonth->copy()->endOfMonth()])->where('status', Transaction::ORDER_COMPLETED)->count();
        $previousCompleted = Transaction::whereBetween('transaction_date', [$previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth()])->where('status', Transaction::ORDER_COMPLETED)->count();

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

    public function updateHiddenStatuses(Request $request)
    {
        $validated = $request->validate([
            'hidden_statuses' => ['sometimes', 'array'],
            'hidden_statuses.*' => [
                'string',
                Rule::in([
                    'PENDING',
                    'PAID',
                    'EXPIRED',
                    'ORDER_PROCESSING',
                    'ORDER_SHIPPED',
                    'ORDER_COMPLETED',
                    'CANCELLED',
                    'ORDER_CANCELLED',
                ]),
            ],
        ]);

        $request->user()->forceFill([
            'hidden_transaction_statuses' => array_values(array_unique($validated['hidden_statuses'] ?? [])),
        ])->save();

        return redirect()
            ->route('admin.transactions')
            ->with('success', 'Pengaturan status transaksi berhasil disimpan.');
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
            ->limit(5)
            ->get(['id', 'name', 'email'])
            ->map(fn ($customer) => [
                'type' => 'customer',
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ]);

        $invoices = Transaction::query()
            ->with('customer:id,name,email')
            ->where('invoice_number', 'like', "%{$search}%")
            ->latest('transaction_date')
            ->limit(5)
            ->get(['id', 'invoice_number', 'customer_id', 'shipping_name', 'shipping_email'])
            ->map(fn ($transaction) => [
                'type' => 'invoice',
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'name' => $transaction->invoice_number,
                'customer_name' => $transaction->shipping_name ?? $transaction->customer?->name ?? 'Pelanggan',
                'email' => $transaction->shipping_email ?? $transaction->customer?->email,
            ]);

        return response()->json([
            'data' => $customers->concat($invoices)->values(),
        ]);
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

                if (!empty($customerData['phone'])) $customer = Customer::where('phone', $customerData['phone'])->first();
                if (!$customer && !empty($customerData['email'])) $customer = Customer::where('email', $customerData['email'])->first();

                if (!$customer) {
                    $customer = Customer::create([
                        'name' => $customerData['name'],
                        'phone' => $customerData['phone'] ?? null,
                        'email' => $customerData['email'] ?? null,
                    ]);
                }

                $items = [];
                $subtotal = 0;

                foreach ($validated['items'] as $item) {
                    $variant = ProductVariant::with(['product', 'size'])->lockForUpdate()->findOrFail($item['product_variant_id']);

                    if (!$variant->product || !$variant->product->status) {
                        throw ValidationException::withMessages(['items' => 'Produk yang dipilih tidak aktif.']);
                    }

                    $inventory = Inventory::where('product_variant_id', $variant->id)->lockForUpdate()->first();

                    if (!$inventory) {
                        throw ValidationException::withMessages(['items' => "Stok untuk {$variant->sku} tidak ditemukan."]);
                    }

                    $qty = (int) $item['qty'];
                    $stock = (int) $inventory->stock;

                    if ($stock < $qty) {
                        throw ValidationException::withMessages(['items' => "Stok {$variant->sku} tidak mencukupi. Stok tersedia: {$stock}."]);
                    }

                    $customName = trim($item['custom_name']);
                    $customNumber = trim($item['custom_number']);

                    if ($customName === '') throw ValidationException::withMessages(['items' => 'Nama jersey wajib diisi.']);
                    if (!preg_match('/^[\pL\s]+$/u', $customName)) throw ValidationException::withMessages(['items' => 'Nama jersey hanya boleh berisi huruf dan spasi.']);
                    if ($customNumber === '') throw ValidationException::withMessages(['items' => 'Nomor punggung wajib diisi.']);
                    if (!preg_match('/^[0-9]{1,2}$/', $customNumber)) throw ValidationException::withMessages(['items' => 'Nomor punggung hanya boleh berisi 1-2 angka.']);

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

                $transactionDate = Carbon::createFromFormat('Y-m-d\TH:i', $validated['transaction_date'], 'Asia/Makassar')->utc();

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
                        'weight' => $item['variant']->weight,
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
            'fulfillments.items.productVariant.product.images',
            'fulfillments.items.productVariant.size',
            'fulfillments.items.productVariant.color',
            'shipments.fulfillments.items.productVariant.product.images',
            'shipments.fulfillments.items.productVariant.size',
            'shipments.fulfillments.items.productVariant.color',
        ]);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function print($invoice)
    {
        $transaction = Transaction::with(['customer', 'items.productVariant.product.images', 'items.productVariant.size', 'items.productVariant.color'])->where('invoice_number', $invoice)->firstOrFail();

        return view('admin.transactions.print', compact('transaction'));
    }

    public function cancel(
        Transaction $transaction,
        InventoryStockService $inventoryStockService,
        FulfillmentHoldService $fulfillmentHoldService
    )
    {
        try {
            $cancelledTransaction = DB::transaction(function () use (
                $transaction,
                $inventoryStockService,
                $fulfillmentHoldService
            ) {
                $lockedTransaction = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

                if ($lockedTransaction->status === 'CANCELLED') {
                    throw ValidationException::withMessages(['transaction' => 'Transaksi sudah dibatalkan sebelumnya.']);
                }

                if ($lockedTransaction->status === 'COMPLETED') {
                    throw ValidationException::withMessages(['transaction' => 'Transaksi yang sudah selesai tidak dapat dibatalkan.']);
                }

                if ($lockedTransaction->status === 'PENDING') {
                    $lockedTransaction->update(['status' => 'CANCELLED']);

                    if (
                        $lockedTransaction->fulfillmentHold()
                            ->where('status', FulfillmentHold::HELD)
                            ->exists()
                    ) {
                        $fulfillmentHoldService->releaseHold($lockedTransaction);
                    }

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

                    if (
                        $lockedTransaction->fulfillmentHold()
                            ->where('status', FulfillmentHold::CONVERTED)
                            ->exists()
                    ) {
                        $fulfillmentHoldService->releasePaidAllocation($lockedTransaction);
                    }

                    $lockedTransaction->addStatusHistory(
                        'ORDER_CANCELLED',
                        'Pesanan dibatalkan oleh admin dan stok dikembalikan.'
                    );

                    return $lockedTransaction->fresh();
                }

                throw ValidationException::withMessages(['transaction' => 'Status transaksi tidak dapat dibatalkan.']);
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

    public function process(Transaction $transaction)
    {
        try {
            $processedTransaction = DB::transaction(function () use ($transaction) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTransaction->status !== 'PAID') {
                    throw ValidationException::withMessages([
                        'transaction' => 'Hanya pesanan dengan status PAID yang dapat diproses.',
                    ]);
                }

                $lockedTransaction->updateStatus(
                    'ORDER_PROCESSING',
                    'Pesanan mulai diproses oleh admin.'
                );

                return $lockedTransaction->fresh();
            });

            // Kirim email setelah perubahan status berhasil di-commit.
            $notification = TransactionNotification::firstOrCreate(
                [
                    'transaction_id' => $processedTransaction->id,
                    'type' => 'ORDER_PROCESSING_EMAIL',
                ],
                [
                    'sent_at' => null,
                ]
            );

            if (
                is_null($notification->sent_at)
                && filled($processedTransaction->shipping_email)
            ) {
                try {
                    $transactionForEmail = Transaction::query()
                        ->with('items.productVariant.product')
                        ->findOrFail($processedTransaction->id);

                    app(EmailArchiveService::class)->send(
                        $transactionForEmail->shipping_email,
                        new OrderProcessingMail($transactionForEmail)
                    );

                    $notification->update([
                        'sent_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // Kegagalan email tidak membatalkan pemrosesan pesanan.
                    report($e);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diproses.',
                'data' => [
                    'id' => $processedTransaction->id,
                    'status' => $processedTransaction->status,
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
                'message' => 'Gagal memproses pesanan.',
            ], 500);
        }
    }

    public function ship(Request $request, Transaction $transaction, BiteshipService $biteshipService)
    {
        $validated = $request->validate([
            'courier' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        $lock = Cache::lock('biteship:ship:' . $transaction->id, 120);

        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sedang diproses. Silakan tunggu sebentar.',
            ], 409);
        }

        try {
            $snapshot = DB::transaction(function () use ($transaction, $validated) {
                $lockedTransaction = Transaction::query()
                    ->with(['items.productVariant.product'])
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTransaction->status !== 'ORDER_PROCESSING') {
                    throw ValidationException::withMessages([
                        'transaction' => 'Hanya pesanan dengan status ORDER_PROCESSING yang dapat dikirim.'
                    ]);
                }

                if ($lockedTransaction->fulfillments()->exists()) {
                    throw ValidationException::withMessages([
                        'transaction' => 'Transaksi dengan fulfillment harus diproses melalui pengiriman fulfillment atau shipment.'
                    ]);
                }

                if ($lockedTransaction->shipping_method !== 'Kurir') {
                    throw ValidationException::withMessages([
                        'transaction' => 'Pesanan ini bukan menggunakan metode pengiriman kurir.'
                    ]);
                }

                if ($lockedTransaction->biteship_order_id) {
                    return [
                        'mode' => 'existing',
                        'transaction_id' => $lockedTransaction->id,
                        'biteship_order_id' => $lockedTransaction->biteship_order_id,
                    ];
                }

                if (! $lockedTransaction->courier_code || ! $lockedTransaction->courier_service_code) {
                    $manualTracking = trim($validated['tracking_number'] ?? '');

                    if ($manualTracking === '') {
                        throw ValidationException::withMessages([
                            'tracking_number' => 'Data kurir Biteship tidak tersedia. Nomor resi manual wajib diisi.'
                        ]);
                    }

                    return [
                        'mode' => 'manual',
                        'transaction_id' => $lockedTransaction->id,
                        'courier' => trim($validated['courier'] ?? ''),
                        'tracking_number' => $manualTracking,
                    ];
                }

                $items = $lockedTransaction->items->map(function ($item) {
                    $productName = $item->productVariant?->product?->name ?? 'Produk EazyWear';

                    return [
                        'name' => $productName,
                        'description' => $productName,
                        'value' => (int) round((float) $item->price),
                        'quantity' => (int) $item->qty,
                        'weight' => (int) $item->weight,
                    ];
                })->values()->all();

                return [
                    'mode' => 'biteship',
                    'transaction_id' => $lockedTransaction->id,
                    'reference_id' => $lockedTransaction->invoice_number,
                    'destination_contact_name' => $lockedTransaction->shipping_name,
                    'destination_contact_phone' => $lockedTransaction->shipping_phone,
                    'destination_address' => $lockedTransaction->shipping_address,
                    'destination_postal_code' => $lockedTransaction->shipping_postal_code,
                    'destination_latitude' => $lockedTransaction->shipping_latitude,
                    'destination_longitude' => $lockedTransaction->shipping_longitude,
                    'courier_company' => $lockedTransaction->courier_code,
                    'courier_type' => $lockedTransaction->courier_service_code,
                    'items' => $items,
                ];
            });

            if ($snapshot['mode'] === 'existing') {
                $biteshipOrder = $biteshipService->getOrder(
                    $snapshot['biteship_order_id']
                );

                if (! $biteshipOrder['success'] || ! $biteshipOrder['order_id']) {
                    throw new RuntimeException('Gagal mengambil data shipment Biteship.');
                }

                $shippedTransaction = DB::transaction(function () use ($snapshot, $biteshipOrder) {
                    $lockedTransaction = Transaction::query()
                        ->whereKey($snapshot['transaction_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedTransaction->status !== 'ORDER_PROCESSING') {
                        throw ValidationException::withMessages([
                            'transaction' => 'Status pesanan sudah berubah.'
                        ]);
                    }

                    $lockedTransaction->update([
                        'biteship_tracking_id' => $biteshipOrder['tracking_id'],
                        'biteship_waybill_id' => $biteshipOrder['waybill_id'],
                        'biteship_status' => $biteshipOrder['status'],
                        'tracking_number' => $biteshipOrder['waybill_id'] ?? $biteshipOrder['tracking_id'],
                        'courier' => $biteshipOrder['courier_code'] ?? $lockedTransaction->courier,
                    ]);

                    $providerStatus = strtolower(trim((string) ($biteshipOrder['status'] ?? '')));

                    if (in_array($providerStatus, ['picked', 'in_transit', 'dropping_off', 'delivered'], true)) {
                        $lockedTransaction->updateStatus(
                            'ORDER_SHIPPED',
                            $providerStatus === 'delivered'
                                ? 'Pengiriman Biteship telah selesai.'
                                : 'Pesanan telah dikirim melalui Biteship.'
                        );
                    }

                    return $lockedTransaction->fresh();
                });
            } elseif ($snapshot['mode'] === 'manual') {
                $shippedTransaction = DB::transaction(function () use ($snapshot) {
                    $lockedTransaction = Transaction::query()
                        ->whereKey($snapshot['transaction_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedTransaction->status !== 'ORDER_PROCESSING') {
                        throw ValidationException::withMessages([
                            'transaction' => 'Status pesanan sudah berubah.'
                        ]);
                    }

                    $lockedTransaction->update([
                        'courier' => $snapshot['courier'],
                        'tracking_number' => $snapshot['tracking_number'],
                    ]);

                    $lockedTransaction->updateStatus(
                        'ORDER_SHIPPED',
                        'Pesanan telah dikirim oleh admin secara manual.'
                    );

                    return $lockedTransaction->fresh();
                });
            } else {
                $biteshipOrder = $biteshipService->createOrder([
                    'reference_id' => $snapshot['reference_id'],
                    'destination_contact_name' => $snapshot['destination_contact_name'],
                    'destination_contact_phone' => $snapshot['destination_contact_phone'],
                    'destination_address' => $snapshot['destination_address'],
                    'destination_postal_code' => $snapshot['destination_postal_code'],
                    'destination_latitude' => $snapshot['destination_latitude'],
                    'destination_longitude' => $snapshot['destination_longitude'],
                    'courier_company' => $snapshot['courier_company'],
                    'courier_type' => $snapshot['courier_type'],
                    'items' => $snapshot['items'],
                ]);

                if (! $biteshipOrder['success'] || ! $biteshipOrder['order_id']) {
                    throw new RuntimeException('Gagal membuat shipment di Biteship.');
                }

                $shippedTransaction = DB::transaction(function () use ($snapshot, $biteshipOrder) {
                    $lockedTransaction = Transaction::query()
                        ->whereKey($snapshot['transaction_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedTransaction->status !== 'ORDER_PROCESSING') {
                        throw ValidationException::withMessages([
                            'transaction' => 'Status pesanan sudah berubah setelah shipment dibuat.'
                        ]);
                    }

                    if ($lockedTransaction->biteship_order_id) {
                        throw new RuntimeException(
                            'Shipment Biteship untuk pesanan ini sudah dibuat sebelumnya.'
                        );
                    }

                    $lockedTransaction->update([
                        'biteship_order_id' => $biteshipOrder['order_id'],
                        'biteship_tracking_id' => $biteshipOrder['tracking_id'],
                        'biteship_waybill_id' => $biteshipOrder['waybill_id'],
                        'biteship_status' => $biteshipOrder['status'],
                        'courier' => $biteshipOrder['courier_code'] ?? $lockedTransaction->courier_code,
                        'tracking_number' => $biteshipOrder['waybill_id'] ?? $biteshipOrder['tracking_id'],
                    ]);

                    $providerStatus = strtolower(trim((string) ($biteshipOrder['status'] ?? '')));

                    if (in_array($providerStatus, ['picked', 'in_transit', 'dropping_off', 'delivered'], true)) {
                        $lockedTransaction->updateStatus(
                            'ORDER_SHIPPED',
                            $providerStatus === 'delivered'
                                ? 'Pengiriman Biteship telah selesai.'
                                : 'Pesanan telah dikirim melalui Biteship.'
                        );
                    }

                    return $lockedTransaction->fresh();
                });
            }

            if ($shippedTransaction->biteship_status === 'delivered') {
                $shippedTransaction = $this->transactionCompletionService
                    ->completeFromBiteship($shippedTransaction);

                $shippedTransaction = $shippedTransaction->fresh();
            }

            if ($shippedTransaction->status === 'ORDER_SHIPPED') {
                $notification = TransactionNotification::firstOrCreate(
                    [
                        'transaction_id' => $shippedTransaction->id,
                        'type' => 'ORDER_SHIPPED_EMAIL',
                    ],
                    [
                        'sent_at' => null,
                    ]
                );

                if (is_null($notification->sent_at)) {
                    try {
                        app(EmailArchiveService::class)->send(
                            $shippedTransaction->shipping_email,
                            new OrderShippedMail($shippedTransaction)
                        );

                        $notification->update([
                            'sent_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diproses.',
                'data' => [
                    'id' => $shippedTransaction->id,
                    'status' => $shippedTransaction->status,
                    'courier' => $shippedTransaction->courier,
                    'tracking_number' => $shippedTransaction->tracking_number,
                    'biteship_order_id' => $shippedTransaction->biteship_order_id,
                    'biteship_tracking_id' => $shippedTransaction->biteship_tracking_id,
                    'biteship_waybill_id' => $shippedTransaction->biteship_waybill_id,
                    'biteship_status' => $shippedTransaction->biteship_status,
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
                'message' => 'Gagal memproses pengiriman.',
            ], 500);
        } finally {
            optional($lock)->release();
        }
    }
    public function shipFulfillment(
        Request $request,
        Transaction $transaction,
        TransactionFulfillment $fulfillment,
        BiteshipService $biteshipService
    ) {
        $validated = $request->validate([
            'courier' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        if ((int) $fulfillment->transaction_id !== (int) $transaction->id) {
            abort(404);
        }

        $lock = Cache::lock('biteship:fulfillment:' . $fulfillment->id, 120);

        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman sedang diproses. Silakan coba kembali.',
            ], 409);
        }

        try {
            $snapshot = DB::transaction(function () use ($transaction, $fulfillment, $validated) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $group = TransactionFulfillment::query()
                    ->with(['items.productVariant.product'])
                    ->whereKey($fulfillment->id)
                    ->where('transaction_id', $lockedTransaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($lockedTransaction->status, [
                    'PAID',
                    'ORDER_PROCESSING',
                    'ORDER_SHIPPED',
                ], true)) {
                    throw ValidationException::withMessages([
                        'transaction' => 'Status transaksi belum mengizinkan pengiriman.',
                    ]);
                }

                if ($group->shipping_method !== 'Kurir') {
                    throw ValidationException::withMessages([
                        'fulfillment' => 'Kelompok pesanan ini tidak menggunakan kurir.',
                    ]);
                }

                if ($group->status === TransactionFulfillment::STATUS_COMPLETED) {
                    throw ValidationException::withMessages([
                        'fulfillment' => 'Kelompok pesanan ini sudah selesai.',
                    ]);
                }

                if ($group->biteship_order_id) {
                    return [
                        'mode' => 'existing',
                        'fulfillment_id' => $group->id,
                        'order_id' => $group->biteship_order_id,
                    ];
                }

                if (! $group->courier_code || ! $group->courier_service_code) {
                    $trackingNumber = trim($validated['tracking_number'] ?? '');

                    if ($trackingNumber === '') {
                        throw ValidationException::withMessages([
                            'tracking_number' => 'Pilih layanan Biteship atau masukkan nomor resi manual.',
                        ]);
                    }

                    return [
                        'mode' => 'manual',
                        'fulfillment_id' => $group->id,
                        'courier' => trim($validated['courier'] ?? ''),
                        'tracking_number' => $trackingNumber,
                    ];
                }

                $items = $group->items->map(function ($item) {
                    $productName = $item->productVariant?->product?->name
                        ?? 'Produk EazyWear';

                    return [
                        'name' => $productName,
                        'description' => $productName,
                        'value' => (int) round((float) $item->price),
                        'quantity' => (int) $item->qty,
                        'weight' => (int) $item->weight,
                    ];
                })->values()->all();

                if (count($items) === 0) {
                    throw ValidationException::withMessages([
                        'fulfillment' => 'Kelompok pengiriman tidak memiliki item.',
                    ]);
                }

                return [
                    'mode' => 'biteship',
                    'fulfillment_id' => $group->id,
                    'reference_id' => $lockedTransaction->invoice_number
                        . '-' . strtoupper($group->group_type),
                    'destination_contact_name' => $group->shipping_name,
                    'destination_contact_phone' => $group->shipping_phone,
                    'destination_address' => $group->shipping_address,
                    'destination_postal_code' => $group->shipping_postal_code,
                    'destination_latitude' => $group->shipping_latitude,
                    'destination_longitude' => $group->shipping_longitude,
                    'courier_company' => $group->courier_code,
                    'courier_type' => $group->courier_service_code,
                    'items' => $items,
                ];
            });

            if ($snapshot['mode'] === 'existing') {
                $order = $biteshipService->getOrder($snapshot['order_id']);
            } elseif ($snapshot['mode'] === 'biteship') {
                $order = $biteshipService->createOrder([
                    'reference_id' => $snapshot['reference_id'],
                    'destination_contact_name' => $snapshot['destination_contact_name'],
                    'destination_contact_phone' => $snapshot['destination_contact_phone'],
                    'destination_address' => $snapshot['destination_address'],
                    'destination_postal_code' => $snapshot['destination_postal_code'],
                    'destination_latitude' => $snapshot['destination_latitude'],
                    'destination_longitude' => $snapshot['destination_longitude'],
                    'courier_company' => $snapshot['courier_company'],
                    'courier_type' => $snapshot['courier_type'],
                    'items' => $snapshot['items'],
                ]);
            }

            if (in_array($snapshot['mode'], ['existing', 'biteship'], true)) {
                if (empty($order['success']) || empty($order['order_id'])) {
                    throw new RuntimeException('Gagal memperoleh data pengiriman Biteship.');
                }
            }

            $providerStatus = null;

            DB::transaction(function () use ($snapshot, $order, &$providerStatus) {
                $group = TransactionFulfillment::query()
                    ->whereKey($snapshot['fulfillment_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($snapshot['mode'] === 'manual') {
                    if ($group->biteship_order_id || $group->tracking_number) {
                        throw ValidationException::withMessages([
                            'fulfillment' => 'Data pengiriman sudah berubah. Muat ulang halaman.',
                        ]);
                    }

                    $group->update([
                        'courier' => $snapshot['courier'] ?: null,
                        'tracking_number' => $snapshot['tracking_number'],
                        'status' => TransactionFulfillment::STATUS_SHIPPED,
                    ]);

                    return;
                }

                if ($group->biteship_order_id
                    && $group->biteship_order_id !== $order['order_id']) {
                    throw ValidationException::withMessages([
                        'fulfillment' => 'ID pesanan Biteship tidak sesuai dengan kelompok ini.',
                    ]);
                }

                $providerStatus = strtolower(trim((string) (
                    $order['status'] ?? $group->biteship_status ?? ''
                )));

                $updates = [
                    'biteship_order_id' => $order['order_id'],
                    'biteship_tracking_id' => $order['tracking_id'] ?? $group->biteship_tracking_id,
                    'biteship_waybill_id' => $order['waybill_id'] ?? $group->biteship_waybill_id,
                    'biteship_status' => $providerStatus ?: $group->biteship_status,
                    'tracking_number' => $order['waybill_id']
                        ?? $order['tracking_id']
                        ?? $group->tracking_number,
                    'courier' => $order['courier_code'] ?? $group->courier,
                ];

                $mappedStatus = match ($providerStatus) {
                    'picked', 'in_transit', 'dropping_off' => TransactionFulfillment::STATUS_SHIPPED,
                    'delivered' => null,
                    'cancelled', 'returned', 'rejected', 'courier_not_found',
                    'disposed', 'return_in_transit' => null,
                    default => TransactionFulfillment::STATUS_PROCESSING,
                };

                if (
                    $mappedStatus !== null
                    && $group->status !== TransactionFulfillment::STATUS_COMPLETED
                    && ! (
                        $mappedStatus === TransactionFulfillment::STATUS_PROCESSING
                        && $group->status === TransactionFulfillment::STATUS_SHIPPED
                    )
                ) {
                    $updates['status'] = $mappedStatus;
                }

                $group->update($updates);
            });

            if ($providerStatus === 'delivered') {
                $deliveredGroup = TransactionFulfillment::query()
                    ->whereKey($snapshot['fulfillment_id'])
                    ->firstOrFail();

                $this->transactionCompletionService
                    ->completeFulfillmentFromBiteship($deliveredGroup);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman kelompok pesanan berhasil diperbarui.',
            ]);
        } finally {
            $lock->release();
        }
    }
    public function shipShipment(
        Request $request,
        Transaction $transaction,
        TransactionShipment $shipment,
        BiteshipService $biteshipService
    ) {
        $validated = $request->validate([
            'courier' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        if ((int) $shipment->transaction_id !== (int) $transaction->id) {
            abort(404);
        }

        $lock = Cache::lock('biteship:shipment:' . $shipment->id, 120);

        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman sedang diproses. Silakan coba kembali.',
            ], 409);
        }

        try {
            $snapshot = DB::transaction(function () use (
                $transaction,
                $shipment,
                $validated
            ) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedShipment = TransactionShipment::query()
                    ->with([
                        'fulfillments.items.productVariant.product',
                    ])
                    ->whereKey($shipment->id)
                    ->where('transaction_id', $lockedTransaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($lockedTransaction->status, [
                    'PAID',
                    Transaction::ORDER_PROCESSING,
                    Transaction::ORDER_SHIPPED,
                ], true)) {
                    throw ValidationException::withMessages([
                        'shipment' => 'Status transaksi belum mengizinkan pengiriman.',
                    ]);
                }

                if ($lockedShipment->shipping_method !== 'Kurir') {
                    throw ValidationException::withMessages([
                        'shipment' => 'Shipment ini tidak menggunakan kurir.',
                    ]);
                }

                if (
                    $lockedShipment->status === TransactionShipment::STATUS_COMPLETED
                    || $lockedShipment->status === TransactionShipment::STATUS_CANCELLED
                ) {
                    throw ValidationException::withMessages([
                        'shipment' => 'Shipment ini sudah selesai atau dibatalkan.',
                    ]);
                }

                if ($lockedShipment->biteship_order_id) {
                    return [
                        'mode' => 'existing',
                        'shipment_id' => $lockedShipment->id,
                        'order_id' => $lockedShipment->biteship_order_id,
                    ];
                }

                if (
                    $lockedShipment->biteship_tracking_id
                    || $lockedShipment->biteship_waybill_id
                    || $lockedShipment->tracking_number
                ) {
                    throw ValidationException::withMessages([
                        'shipment' => 'Data resi sudah tersedia. Muat ulang halaman sebelum melanjutkan.',
                    ]);
                }

                if (
                    ! $lockedShipment->courier_code
                    || ! $lockedShipment->courier_service_code
                ) {
                    $trackingNumber = trim($validated['tracking_number'] ?? '');

                    if ($trackingNumber === '') {
                        throw ValidationException::withMessages([
                            'tracking_number' => 'Pilih layanan Biteship atau masukkan nomor resi manual.',
                        ]);
                    }

                    return [
                        'mode' => 'manual',
                        'shipment_id' => $lockedShipment->id,
                        'courier' => trim($validated['courier'] ?? ''),
                        'tracking_number' => $trackingNumber,
                    ];
                }

                $items = $lockedShipment->fulfillments
                    ->flatMap(fn ($group) => $group->items)
                    ->map(function ($item) {
                        $productName = $item->productVariant?->product?->name
                            ?? 'Produk EazyWear';

                        return [
                            'name' => $productName,
                            'description' => $productName,
                            'value' => (int) round((float) $item->price),
                            'quantity' => (int) $item->qty,
                            'weight' => (int) $item->weight,
                        ];
                    })
                    ->values()
                    ->all();

                if (count($items) === 0) {
                    throw ValidationException::withMessages([
                        'shipment' => 'Shipment tidak memiliki item untuk dikirim.',
                    ]);
                }

                return [
                    'mode' => 'biteship',
                    'shipment_id' => $lockedShipment->id,
                    'reference_id' => $lockedTransaction->invoice_number
                        . '-SHIPMENT-' . $lockedShipment->id,
                    'destination_contact_name' => $lockedShipment->shipping_name,
                    'destination_contact_phone' => $lockedShipment->shipping_phone,
                    'destination_address' => $lockedShipment->shipping_address,
                    'destination_postal_code' => $lockedShipment->shipping_postal_code,
                    'destination_latitude' => $lockedShipment->shipping_latitude,
                    'destination_longitude' => $lockedShipment->shipping_longitude,
                    'courier_company' => $lockedShipment->courier_code,
                    'courier_type' => $lockedShipment->courier_service_code,
                    'items' => $items,
                ];
            });

            $order = null;

            if ($snapshot['mode'] === 'existing') {
                $order = $biteshipService->getOrder($snapshot['order_id']);
            } elseif ($snapshot['mode'] === 'biteship') {
                $order = $biteshipService->createOrder([
                    'reference_id' => $snapshot['reference_id'],
                    'destination_contact_name' => $snapshot['destination_contact_name'],
                    'destination_contact_phone' => $snapshot['destination_contact_phone'],
                    'destination_address' => $snapshot['destination_address'],
                    'destination_postal_code' => $snapshot['destination_postal_code'],
                    'destination_latitude' => $snapshot['destination_latitude'],
                    'destination_longitude' => $snapshot['destination_longitude'],
                    'courier_company' => $snapshot['courier_company'],
                    'courier_type' => $snapshot['courier_type'],
                    'items' => $snapshot['items'],
                ]);
            }

            if (
                in_array($snapshot['mode'], ['existing', 'biteship'], true)
                && (empty($order['success']) || empty($order['order_id']))
            ) {
                throw new RuntimeException(
                    'Gagal memperoleh data pengiriman Biteship.'
                );
            }

            $providerStatus = null;

            DB::transaction(function () use ($snapshot, $order, &$providerStatus) {
                $shipment = TransactionShipment::query()
                    ->whereKey($snapshot['shipment_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $groups = TransactionFulfillment::query()
                    ->whereHas('shipments', function ($query) use ($shipment) {
                        $query->where('transaction_shipments.id', $shipment->id);
                    })
                    ->lockForUpdate()
                    ->get();

                if ($groups->isEmpty()) {
                    throw ValidationException::withMessages([
                        'shipment' => 'Shipment belum terhubung ke fulfillment mana pun.',
                    ]);
                }

                if ($snapshot['mode'] === 'manual') {
                    if (
                        $shipment->biteship_order_id
                        || $shipment->tracking_number
                        || $shipment->biteship_tracking_id
                        || $shipment->biteship_waybill_id
                    ) {
                        throw ValidationException::withMessages([
                            'shipment' => 'Data shipment sudah berubah. Muat ulang halaman.',
                        ]);
                    }

                    $shipment->update([
                        'courier' => $snapshot['courier'] ?: null,
                        'tracking_number' => $snapshot['tracking_number'],
                        'status' => TransactionShipment::STATUS_SHIPPED,
                    ]);

                    foreach ($groups as $group) {
                        $group->update([
                            'courier' => $snapshot['courier'] ?: $group->courier,
                            'tracking_number' => $snapshot['tracking_number'],
                            'status' => TransactionFulfillment::STATUS_SHIPPED,
                        ]);
                    }

                    return;
                }

                if (
                    $shipment->biteship_order_id
                    && $shipment->biteship_order_id !== $order['order_id']
                ) {
                    throw ValidationException::withMessages([
                        'shipment' => 'ID pesanan Biteship tidak sesuai dengan shipment ini.',
                    ]);
                }

                $waybill = $order['waybill_id'] ?? $shipment->biteship_waybill_id;
                $trackingId = $order['tracking_id'] ?? $shipment->biteship_tracking_id;
                $courier = $order['courier_code'] ?? $shipment->courier;
                $providerStatus = strtolower(trim((string) (
                    $order['status'] ?? $shipment->biteship_status ?? ''
                )));

                $shipmentUpdates = [
                    'biteship_order_id' => $order['order_id'],
                    'biteship_tracking_id' => $trackingId,
                    'biteship_waybill_id' => $waybill,
                    'biteship_status' => $providerStatus ?: $shipment->biteship_status,
                    'tracking_number' => $waybill ?? $trackingId ?? $shipment->tracking_number,
                    'courier' => $courier,
                ];

                $shipmentStatus = match ($providerStatus) {
                    'picked', 'in_transit', 'dropping_off' => TransactionShipment::STATUS_SHIPPED,
                    'delivered' => TransactionShipment::STATUS_COMPLETED,
                    'cancelled', 'returned', 'rejected', 'courier_not_found',
                    'disposed', 'return_in_transit' => null,
                    default => TransactionShipment::STATUS_PROCESSING,
                };

                if (
                    $shipmentStatus !== null
                    && $shipment->status !== TransactionShipment::STATUS_COMPLETED
                    && ! (
                        $shipmentStatus === TransactionShipment::STATUS_PROCESSING
                        && $shipment->status === TransactionShipment::STATUS_SHIPPED
                    )
                ) {
                    $shipmentUpdates['status'] = $shipmentStatus;
                }

                $shipment->update($shipmentUpdates);

                foreach ($groups as $group) {
                    $groupUpdates = [
                        'biteship_tracking_id' => $trackingId ?? $group->biteship_tracking_id,
                        'biteship_waybill_id' => $waybill ?? $group->biteship_waybill_id,
                        'biteship_status' => $providerStatus ?: $group->biteship_status,
                        'tracking_number' => $waybill ?? $trackingId ?? $group->tracking_number,
                        'courier' => $courier ?? $group->courier,
                    ];

                    $groupStatus = match ($providerStatus) {
                        'picked', 'in_transit', 'dropping_off' => TransactionFulfillment::STATUS_SHIPPED,
                        'delivered' => null,
                        'cancelled', 'returned', 'rejected', 'courier_not_found',
                        'disposed', 'return_in_transit' => null,
                        default => TransactionFulfillment::STATUS_PROCESSING,
                    };

                    if (
                        $groupStatus !== null
                        && $group->status !== TransactionFulfillment::STATUS_COMPLETED
                        && ! (
                            $groupStatus === TransactionFulfillment::STATUS_PROCESSING
                            && $group->status === TransactionFulfillment::STATUS_SHIPPED
                        )
                    ) {
                        $groupUpdates['status'] = $groupStatus;
                    }

                    $group->update($groupUpdates);
                }
            });

            if ($providerStatus === 'delivered') {
                $deliveredGroups = TransactionFulfillment::query()
                    ->whereHas('shipments', function ($query) use ($shipment) {
                        $query->where('transaction_shipments.id', $shipment->id);
                    })
                    ->get();

                foreach ($deliveredGroups as $group) {
                    $this->transactionCompletionService
                        ->completeFulfillmentFromBiteship($group);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Shipment berhasil diperbarui.',
            ]);
        } finally {
            $lock->release();
        }
    }

    public function trackingLegacyTransaction(
        Transaction $transaction,
        BiteshipService $biteshipService
    ) {
        if (
            ! $transaction->biteship_order_id
            || ! $transaction->biteship_tracking_id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Data tracking Biteship belum tersedia untuk transaksi ini.',
            ], 422);
        }

        try {
            $tracking = $biteshipService->getTracking(
                $transaction->biteship_tracking_id
            );

            if (empty($tracking['success'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biteship belum menyediakan data tracking untuk transaksi ini.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $transaction->tracking_number
                        ?: ($transaction->biteship_waybill_id ?: ($tracking['waybill_id'] ?? null)),
                    'courier' => $tracking['courier_code'] ?? null,
                    'status' => ($tracking['status'] ?? null)
                        ?: ($transaction->biteship_status ?? null),
                    'history' => $tracking['history'] ?? [],
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Tracking belum dapat dimuat. Silakan coba kembali.',
            ], 502);
        }
    }
    public function trackingShipment(
        Transaction $transaction,
        TransactionShipment $shipment,
        BiteshipService $biteshipService
    ) {
        if ((int) $shipment->transaction_id !== (int) $transaction->id) {
            abort(404);
        }

        if (
            ! $shipment->biteship_order_id
            || ! $shipment->biteship_tracking_id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Data tracking Biteship belum tersedia untuk pengiriman ini.',
            ], 422);
        }

        try {
            $tracking = $biteshipService->getTracking(
                $shipment->biteship_tracking_id
            );

            if (empty($tracking['success'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biteship belum menyediakan data tracking untuk pengiriman ini.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $shipment->tracking_number
                        ?: ($shipment->biteship_waybill_id ?: ($tracking['waybill_id'] ?? null)),
                    'courier' => $shipment->courier
                        ?: ($shipment->courier_code ?: ($tracking['courier_code'] ?? null)),
                    'status' => $tracking['status']
                        ?: $shipment->biteship_status,
                    'history' => $tracking['history'],
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Tracking belum dapat dimuat. Silakan coba kembali.',
            ], 502);
        }
    }
    public function trackingFulfillment(
        Transaction $transaction,
        TransactionFulfillment $fulfillment,
        BiteshipService $biteshipService
    ) {
        if ((int) $fulfillment->transaction_id !== (int) $transaction->id) {
            abort(404);
        }

        if (
            ! $fulfillment->biteship_order_id
            || ! $fulfillment->biteship_tracking_id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Data tracking Biteship belum tersedia untuk pemenuhan ini.',
            ], 422);
        }

        try {
            $tracking = $biteshipService->getTracking(
                $fulfillment->biteship_tracking_id
            );

            if (empty($tracking['success'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biteship belum menyediakan data tracking untuk pemenuhan ini.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $fulfillment->tracking_number
                        ?: $fulfillment->biteship_waybill_id
                        ?: $tracking['waybill_id'],
                    'courier' => $fulfillment->courier
                        ?: $fulfillment->courier_code
                        ?: $tracking['courier_code'],
                    'status' => $tracking['status']
                        ?: $fulfillment->biteship_status,
                    'history' => $tracking['history'],
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Tracking belum dapat dimuat. Silakan coba kembali.',
            ], 502);
        }
    }
    public function printLegacyTransactionLabel(Transaction $transaction)
    {
        if ($transaction->shipping_method !== 'Kurir') {
            abort(404, 'Label hanya tersedia untuk transaksi dengan pengiriman kurir.');
        }

        if ($transaction->shipments()->exists()) {
            abort(404, 'Transaksi ini sudah memiliki shipment. Gunakan label shipment.');
        }

        $transaction->loadMissing([
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ]);

        $shipment = new TransactionShipment([
            'transaction_id' => $transaction->id,
            'shipment_type' => 'legacy',
            'shipping_method' => $transaction->shipping_method,
            'shipping_cost' => $transaction->shipping,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_district' => $transaction->shipping_district,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'courier' => $transaction->courier,
            'courier_code' => $transaction->courier_code,
            'courier_service_code' => $transaction->courier_service_code,
            'tracking_number' => $transaction->tracking_number,
            'biteship_order_id' => $transaction->biteship_order_id,
            'biteship_tracking_id' => $transaction->biteship_tracking_id,
            'biteship_waybill_id' => $transaction->biteship_waybill_id,
            'biteship_status' => $transaction->biteship_status,
        ]);

        $shipment->setRelation('fulfillments', collect());

        return view('admin.transactions.shipment-label', [
            'transaction' => $transaction,
            'shipment' => $shipment,
            'origin' => config('biteship.origin', []),
            'legacyLabel' => true,
        ]);
    }
    public function printFulfillmentLabel(
        Transaction $transaction,
        TransactionFulfillment $fulfillment
    ) {
        if ((int) $fulfillment->transaction_id !== (int) $transaction->id) {
            abort(404);
        }

        if ($fulfillment->shipping_method !== 'Kurir') {
            abort(404, 'Label pengiriman hanya tersedia untuk fulfillment kurir.');
        }

        $transaction->loadMissing('items');
        $fulfillment->load([
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ]);

        $shipment = new TransactionShipment([
            'id' => $fulfillment->id,
            'transaction_id' => $transaction->id,
            'shipment_type' => 'fulfillment',
            'shipping_method' => $fulfillment->shipping_method,
            'shipping_cost' => $fulfillment->shipping_cost,
            'shipping_name' => $fulfillment->shipping_name ?: $transaction->shipping_name,
            'shipping_email' => $fulfillment->shipping_email ?: $transaction->shipping_email,
            'shipping_phone' => $fulfillment->shipping_phone ?: $transaction->shipping_phone,
            'shipping_address' => $fulfillment->shipping_address ?: $transaction->shipping_address,
            'shipping_district' => $fulfillment->shipping_district ?: $transaction->shipping_district,
            'shipping_city' => $fulfillment->shipping_city ?: $transaction->shipping_city,
            'shipping_province' => $fulfillment->shipping_province ?: $transaction->shipping_province,
            'shipping_postal_code' => $fulfillment->shipping_postal_code ?: $transaction->shipping_postal_code,
            'courier' => $fulfillment->courier,
            'courier_code' => $fulfillment->courier_code,
            'courier_service_code' => $fulfillment->courier_service_code,
            'tracking_number' => $fulfillment->tracking_number,
            'biteship_order_id' => $fulfillment->biteship_order_id,
            'biteship_tracking_id' => $fulfillment->biteship_tracking_id,
            'biteship_waybill_id' => $fulfillment->biteship_waybill_id,
            'biteship_status' => $fulfillment->biteship_status,
        ]);

        $shipment->setRelation('fulfillments', collect([$fulfillment]));

        return view('admin.transactions.shipment-label', [
            'transaction' => $transaction,
            'shipment' => $shipment,
            'origin' => config('biteship.origin', []),
            'fulfillmentLabel' => true,
            'labelFulfillment' => $fulfillment,
        ]);
    }
    public function printShipmentLabel(
        Transaction $transaction,
        TransactionShipment $shipment
    ) {
        if ((int) $shipment->transaction_id !== (int) $transaction->id) {
            abort(404);
        }

        if ($shipment->shipping_method !== 'Kurir') {
            abort(404, 'Label pengiriman hanya tersedia untuk shipment kurir.');
        }

        $origin = config('biteship.origin', []);

        return view('admin.transactions.shipment-label', [
            'transaction' => $transaction,
            'shipment' => $shipment,
            'origin' => $origin,
        ]);
    }
    public function updateShipmentShippingAddress(
        Request $request,
        Transaction $transaction,
        TransactionShipment $shipment
    ) {
        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'max:2000'],
        ], [
            'shipping_address.required' => 'Alamat pengiriman wajib diisi.',
            'shipping_address.max' => 'Alamat pengiriman maksimal 2000 karakter.',
        ]);

        if ((int) $shipment->transaction_id !== (int) $transaction->id) {
            abort(404);
        }

        DB::transaction(function () use ($transaction, $shipment, $validated) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedShipment = TransactionShipment::query()
                ->whereKey($shipment->id)
                ->where('transaction_id', $lockedTransaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedTransaction->status, [
                'PAID',
                Transaction::ORDER_PROCESSING,
                Transaction::ORDER_SHIPPED,
            ], true)) {
                throw ValidationException::withMessages([
                    'shipping_address' => 'Status transaksi tidak mengizinkan perubahan alamat.',
                ]);
            }

            if ($lockedShipment->shipping_method !== 'Kurir') {
                throw ValidationException::withMessages([
                    'shipping_address' => 'Alamat hanya dapat diedit untuk shipment kurir.',
                ]);
            }

            if (
                $lockedShipment->biteship_order_id
                || $lockedShipment->biteship_tracking_id
                || $lockedShipment->biteship_waybill_id
                || $lockedShipment->tracking_number
            ) {
                throw ValidationException::withMessages([
                    'shipping_address' => 'Alamat tidak dapat diubah setelah proses pengiriman atau pengisian resi dimulai.',
                ]);
            }

            $groups = TransactionFulfillment::query()
                ->whereHas('shipments', function ($query) use ($lockedShipment) {
                    $query->where('transaction_shipments.id', $lockedShipment->id);
                })
                ->lockForUpdate()
                ->get();

            foreach ($groups as $group) {
                if (
                    $group->biteship_order_id
                    || $group->biteship_tracking_id
                    || $group->biteship_waybill_id
                    || $group->tracking_number
                ) {
                    throw ValidationException::withMessages([
                        'shipping_address' => 'Alamat tidak dapat diubah karena data pengiriman fulfillment sudah tersedia.',
                    ]);
                }
            }

            $address = trim($validated['shipping_address']);

            $lockedShipment->update([
                'shipping_address' => $address,
            ]);

            foreach ($groups as $group) {
                $group->update([
                    'shipping_address' => $address,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Alamat shipment berhasil diperbarui.',
        ]);
    }

    public function updateFulfillmentShippingAddress(
        Request $request,
        Transaction $transaction,
        TransactionFulfillment $fulfillment
    ) {
        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'max:2000'],
        ], [
            'shipping_address.required' => 'Alamat pengiriman wajib diisi.',
            'shipping_address.max' => 'Alamat pengiriman maksimal 2000 karakter.',
        ]);

        if ((int) $fulfillment->transaction_id !== (int) $transaction->id) {
            abort(404);
        }

        DB::transaction(function () use ($transaction, $fulfillment, $validated) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $group = TransactionFulfillment::query()
                ->whereKey($fulfillment->id)
                ->where('transaction_id', $lockedTransaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedTransaction->status, [
                'PAID',
                Transaction::ORDER_PROCESSING,
                Transaction::ORDER_SHIPPED,
            ], true)) {
                throw ValidationException::withMessages([
                    'shipping_address' => 'Status transaksi tidak mengizinkan perubahan alamat.',
                ]);
            }

            if ($group->shipping_method !== 'Kurir') {
                throw ValidationException::withMessages([
                    'shipping_address' => 'Alamat hanya dapat diedit untuk kelompok pengiriman kurir.',
                ]);
            }

            if (
                $group->biteship_order_id
                || $group->biteship_tracking_id
                || $group->biteship_waybill_id
                || $group->tracking_number
            ) {
                throw ValidationException::withMessages([
                    'shipping_address' => 'Alamat tidak dapat diubah setelah proses pengiriman atau pengisian resi dimulai.',
                ]);
            }

            $group->update([
                'shipping_address' => trim($validated['shipping_address']),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Alamat kelompok pengiriman berhasil diperbarui.',
        ]);
    }

    public function completeFulfillment(
        Transaction $transaction,
        TransactionFulfillment $fulfillment
    ) {
        if ((int) $fulfillment->transaction_id !== (int) $transaction->id) {
            abort(404);
        }

        $completedTransaction = $this->transactionCompletionService
            ->completeFulfillmentManually($fulfillment);

        return response()->json([
            'success' => true,
            'message' => $completedTransaction->status === Transaction::ORDER_COMPLETED
                ? 'Semua kelompok pesanan telah selesai.'
                : 'Kelompok pesanan berhasil diselesaikan.',
            'transaction_status' => $completedTransaction->status,
            'fulfillment_id' => $fulfillment->id,
        ]);
    }



    public function destroy(Transaction $transaction)
    {
        if (in_array($transaction->status, ['PAID', 'COMPLETED'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi yang sudah dibayar atau selesai tidak dapat dihapus.',
            ], 422);
        }

        $hasStockMovements = StockMovement::query()->where('transaction_id', $transaction->id)->exists();

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
                $lockedTransaction = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();
                $hasStockMovements = StockMovement::query()->where('transaction_id', $lockedTransaction->id)->exists();

                if ($hasStockMovements) {
                    throw ValidationException::withMessages(['transaction' => 'Transaksi memiliki riwayat stok dan tidak dapat dihapus.']);
                }

                if ($lockedTransaction->status !== 'CANCELLED') {
                    throw ValidationException::withMessages(['transaction' => 'Status transaksi tidak dapat dihapus.']);
                }

                $fulfillmentHold = $lockedTransaction->fulfillmentHold()
                    ->lockForUpdate()
                    ->first();

                if ($fulfillmentHold) {
                    if ($fulfillmentHold->status !== FulfillmentHold::RELEASED) {
                        throw ValidationException::withMessages([
                            'transaction' => 'Fulfillment hold transaksi belum dilepas dan transaksi tidak dapat dihapus.',
                        ]);
                    }

                    $fulfillmentHold->delete();
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
        TransactionPaymentService $paymentService,
        DokuQrisService $dokuQrisService
    ) {
        $paymentMethod = strtoupper((string) $transaction->payment_method);
        $isQris = str_contains($paymentMethod, 'QRIS');
        $isDokuVa = preg_match(
            '/(?:^|[^A-Z])VA(?:$|[^A-Z])|VIRTUAL[\s_-]*ACCOUNT/',
            $paymentMethod
        ) === 1;

        if ($transaction->status === 'PAID') {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi sudah tercatat lunas.',
                'status' => 'PAID',
            ]);
        }

        if ($isQris) {
            if (!in_array($transaction->status, ['PENDING', 'EXPIRED'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status transaksi tidak dapat diperiksa untuk pemulihan QRIS.',
                ], 422);
            }

            try {
                $originalReferenceNo = data_get(
                    $transaction->qris_response,
                    'referenceNo'
                );

                $originalPartnerReferenceNo = data_get(
                    $transaction->qris_response,
                    'partnerReferenceNo'
                );

                if (
                    ! is_string($originalReferenceNo)
                    || trim($originalReferenceNo) === ''
                    || ! is_string($originalPartnerReferenceNo)
                    || trim($originalPartnerReferenceNo) === ''
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Referensi QRIS tersimpan tidak lengkap. Pemeriksaan pembayaran dibatalkan.',
                    ], 422);
                }

                $response = $dokuQrisService->queryQr(
                    $originalReferenceNo,
                    $originalPartnerReferenceNo
                );
            } catch (\Throwable $e) {
                report($e);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memeriksa status QRIS ke DOKU.',
                ], 502);
            }

            $paidAmount = data_get($response, 'amount.value');

            if (
                ($response['responseCode'] ?? null) !== '2005100'
                || (string) ($response['latestTransactionStatus'] ?? '') !== '00'
                || strcasecmp((string) ($response['transactionStatusDesc'] ?? ''), 'Success') !== 0
                || data_get($response, 'amount.currency') !== 'IDR'
                || $paidAmount === null
                || number_format((float) $paidAmount, 2, '.', '') !==
                    number_format((float) $transaction->total, 2, '.', '')
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'DOKU belum mengonfirmasi pembayaran QRIS sukses dengan nominal yang sesuai.',
                ], 422);
            }

            $normalizedResponse = [
                'responseCode' => '2002600',
                'responseMessage' => 'Success',
                'paymentFlagReason' => ['english' => 'Success'],
                'paidAmount' => ['value' => $paidAmount, 'currency' => 'IDR'],
                'paymentRequestId' => $response['originalReferenceNo'] ?? $transaction->invoice_number,
                'qrisQueryResponse' => $response,
            ];

            $paymentService->processSuccessfulPayment(
                $transaction,
                $normalizedResponse,
                'Manual DOKU QRIS query',
                true
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran QRIS berhasil diverifikasi.',
                'status' => 'PAID',
            ]);
        }

        if (! in_array($transaction->status, ['PENDING', 'EXPIRED'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya transaksi PENDING yang dapat diperiksa melalui Virtual Account.',
            ], 422);
        }

        if (! $isDokuVa) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran tidak didukung untuk pemeriksaan DOKU VA.',
            ], 422);
        }

        try {
            $result = $dokuService->checkVirtualAccountStatus([
                'partnerServiceId' => trim((string) data_get(
                    $transaction->doku_response,
                    'virtualAccountData.partnerServiceId'
                )),
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
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa status Virtual Account ke DOKU.',
            ], 502);
        }

        $paidAmount = data_get(
            $response,
            'virtualAccountData.paidAmount.value'
        ) ?? data_get($response, 'paidAmount.value');

        $paidCurrency = data_get(
            $response,
            'virtualAccountData.paidAmount.currency'
        ) ?? data_get($response, 'paidAmount.currency');

        if (
            ($response['responseCode'] ?? null) === '2002600'
            && strtolower((string) (
                data_get($response, 'virtualAccountData.paymentFlagReason.english')
                ?? data_get($response, 'paymentFlagReason.english')
            )) === 'success'
            && $paidAmount !== null
            && $paidAmount !== ''
            && $paidCurrency === 'IDR'
            && number_format((float) $paidAmount, 2, '.', '') ===
                number_format((float) $transaction->total, 2, '.', '')
        ) {
            $paymentService->processSuccessfulPayment(
                $transaction,
                $response,
                'Manual DOKU payment check',
                $transaction->status === 'EXPIRED'
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi.',
                'status' => 'PAID',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pembayaran belum diterima DOKU.',
        ]);
    }

    public function updateCustomerContact(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:30'],
            'shipping_email' => ['nullable', 'email', 'max:255'],
        ], [
            'shipping_name.required' => 'Nama pelanggan wajib diisi.',
            'shipping_phone.required' => 'Nomor telepon pelanggan wajib diisi.',
            'shipping_email.email' => 'Format email tidak valid.',
        ]);

        $lock = Cache::lock('biteship:ship:' . $transaction->id, 120);

        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sedang diproses untuk pengiriman. Coba lagi sebentar.',
            ], 409);
        }

        try {
            $updatedTransaction = DB::transaction(function () use ($transaction, $validated) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($lockedTransaction->status, ['PAID', 'ORDER_PROCESSING'], true)) {
                    throw ValidationException::withMessages([
                        'contact' => 'Kontak hanya dapat diedit sebelum pesanan dikirim.',
                    ]);
                }

                if (
                    $lockedTransaction->biteship_order_id ||
                    $lockedTransaction->biteship_tracking_id ||
                    $lockedTransaction->biteship_waybill_id ||
                    $lockedTransaction->tracking_number
                ) {
                    throw ValidationException::withMessages([
                        'contact' => 'Kontak tidak dapat diedit karena proses atau resi pengiriman sudah dimulai.',
                    ]);
                }

                // Hanya kolom kontak pada transaksi yang diubah.
                $lockedTransaction->update([
                    'shipping_name' => trim($validated['shipping_name']),
                    'shipping_phone' => trim($validated['shipping_phone']),
                    'shipping_email' => isset($validated['shipping_email'])
                        ? trim($validated['shipping_email'])
                        : null,
                ]);

                return $lockedTransaction->fresh();
            });

            return response()->json([
                'success' => true,
                'message' => 'Kontak pelanggan berhasil diperbarui.',
                'data' => [
                    'id' => $updatedTransaction->id,
                    'shipping_name' => $updatedTransaction->shipping_name,
                    'shipping_phone' => $updatedTransaction->shipping_phone,
                    'shipping_email' => $updatedTransaction->shipping_email,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } finally {
            $lock->release();
        }
    }

    public function updateShippingAddress(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'max:2000'],
        ], [
            'shipping_address.required' => 'Alamat pengiriman wajib diisi.',
            'shipping_address.max' => 'Alamat pengiriman maksimal 2000 karakter.',
        ]);

        // Gunakan lock yang sama dengan proses pembuatan resi agar alamat
        // tidak dapat berubah ketika order Biteship sedang dibuat.
        $lock = Cache::lock('biteship:ship:' . $transaction->id, 120);

        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sedang diproses untuk pengiriman. Coba lagi sebentar.',
            ], 409);
        }

        try {
            $updatedTransaction = DB::transaction(function () use ($transaction, $validated) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    ! in_array($lockedTransaction->status, ['PAID', 'ORDER_PROCESSING'], true) ||
                    $lockedTransaction->shipping_method !== 'Kurir'
                ) {
                    throw ValidationException::withMessages([
                        'shipping_address' => 'Alamat hanya dapat diedit untuk pesanan kurir yang belum dikirim.',
                    ]);
                }

                if (
                    $lockedTransaction->biteship_order_id ||
                    $lockedTransaction->biteship_tracking_id ||
                    $lockedTransaction->biteship_waybill_id ||
                    $lockedTransaction->tracking_number
                ) {
                    throw ValidationException::withMessages([
                        'shipping_address' => 'Alamat tidak dapat diedit karena proses/resi pengiriman sudah dimulai.',
                    ]);
                }

                // Hanya kolom shipping_address yang diubah.
                $lockedTransaction->update([
                    'shipping_address' => trim($validated['shipping_address']),
                ]);

                return $lockedTransaction->fresh();
            });

            return response()->json([
                'success' => true,
                'message' => 'Alamat pengiriman berhasil diperbarui.',
                'data' => [
                    'id' => $updatedTransaction->id,
                    'shipping_address' => $updatedTransaction->shipping_address,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Alamat pengiriman tidak dapat diperbarui.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui alamat pengiriman.',
            ], 500);
        } finally {
            $lock->release();
        }
    }

    public function updateShipping(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'courier' => ['required', 'string', 'max:100'],
            'tracking_number' => ['required', 'string', 'max:100'],
        ], [
            'courier.required' => 'Kurir wajib diisi.',
            'tracking_number.required' => 'Nomor resi wajib diisi.',
        ]);

        try {
            $updatedTransaction = DB::transaction(function () use ($transaction, $validated) {
                $lockedTransaction = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

                if (!in_array($lockedTransaction->status, ['ORDER_SHIPPED', 'ORDER_COMPLETED'], true)) {
                    throw ValidationException::withMessages(['transaction' => 'Detail pengiriman hanya dapat diedit setelah pesanan dikirim.']);
                }

                $lockedTransaction->update([
                    'courier' => trim($validated['courier']),
                    'tracking_number' => trim($validated['tracking_number']),
                ]);

                return $lockedTransaction->fresh();
            });

            return response()->json([
                'success' => true,
                'message' => 'Detail pengiriman berhasil diperbarui.',
                'data' => [
                    'id' => $updatedTransaction->id,
                    'status' => $updatedTransaction->status,
                    'courier' => $updatedTransaction->courier,
                    'tracking_number' => $updatedTransaction->tracking_number,
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
                'message' => 'Gagal memperbarui detail pengiriman.',
            ], 500);
        }
    }

    public function complete(Transaction $transaction)
    {
        try {
            $completedTransaction = $this->transactionCompletionService
                ->completeManually($transaction);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diselesaikan.',
                'data' => [
                    'id' => $completedTransaction->id,
                    'status' => $completedTransaction->status,
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
                'message' => 'Gagal menyelesaikan pesanan.',
            ], 500);
        }
    }
}
