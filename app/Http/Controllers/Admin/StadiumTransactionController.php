<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StadiumTransactionController extends Controller
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
            ->where('source', 'Stadium');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhere('shipping_name', 'like', "%{$search}%")
                    ->orWhere('shipping_phone', 'like', "%{$search}%");
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
            'completed_orders' => (clone $summaryQuery)
                ->where('status', 'PAID')
                ->count(),
        ];

        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

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
                'value' => ($growth >= 0 ? '+' : '')
                    . number_format($growth, 1, ',', '.') . '%',
                'positive' => $growth >= 0,
                'neutral' => false,
            ];
        };

        $currentTransactions = Transaction::where('source', 'Stadium')
            ->whereBetween('transaction_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->count();

        $previousTransactions = Transaction::where('source', 'Stadium')
            ->whereBetween('transaction_date', [
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ])
            ->count();

        $currentRevenue = Transaction::where('source', 'Stadium')
            ->whereBetween('transaction_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->sum('total');

        $previousRevenue = Transaction::where('source', 'Stadium')
            ->whereBetween('transaction_date', [
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ])
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->sum('total');

        $transactionGrowth = $calculateGrowth(
            $currentTransactions,
            $previousTransactions
        );

        $revenueGrowth = $calculateGrowth(
            $currentRevenue,
            $previousRevenue
        );

        $currentCompleted = Transaction::where('source', 'Stadium')
            ->whereBetween('transaction_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->where('status', 'PAID')
            ->count();

        $previousCompleted = Transaction::where('source', 'Stadium')
            ->whereBetween('transaction_date', [
                $previousMonth->copy()->startOfMonth(),
                $previousMonth->copy()->endOfMonth(),
            ])
            ->where('status', 'PAID')
            ->count();

        $completedGrowth = $calculateGrowth(
            $currentCompleted,
            $previousCompleted
        );

        return view('admin.stadium-transactions.index', compact(
            'transactions',
            'summary',
            'transactionGrowth',
            'revenueGrowth',
            'completedGrowth'
        ));
    }

    public function create()
    {
        $catalog = $this->buildStadiumCatalog();

        return view('admin.stadium-transactions.create', compact('catalog'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateStadiumTransactionPayload($request);

        [$itemsToCreate, $subtotal] = $this->buildStadiumTransactionItems(
            $validated['items']
        );

        $transactionDate = Carbon::parse(
            $validated['transaction_date'],
            'Asia/Makassar'
        );

        $customerName = trim((string) ($validated['customer_name'] ?? ''));
        $transaction = null;
        $maxAttempts = 3;

        try {
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                try {
                    $transaction = DB::transaction(function () use (
                        $customerName,
                        $transactionDate,
                        $subtotal,
                        $itemsToCreate
                    ) {
                        $invoiceNumber = $this->generateStadiumInvoiceNumber();

                        if (
                            Transaction::query()
                                ->where('invoice_number', $invoiceNumber)
                                ->lockForUpdate()
                                ->exists()
                        ) {
                            throw new \RuntimeException('Invoice sudah digunakan.');
                        }

                        $transaction = Transaction::create([
                            'customer_id' => null,
                            'invoice_number' => $invoiceNumber,
                            'transaction_date' => $transactionDate,
                            'payment_method' => 'CASH',
                            'subtotal' => $subtotal,
                            'discount' => 0,
                            'shipping' => 0,
                            'total' => $subtotal,
                            'status' => 'PAID',
                            'paid_at' => $transactionDate,
                            'source' => 'Stadium',
                            'shipping_name' => $customerName !== ''
                                ? $customerName
                                : 'Transaksi Stadion',
                        ]);

                        foreach ($itemsToCreate as $item) {
                            $transaction->items()->create($item);
                        }

                        return $transaction;
                    });

                    break;
                } catch (QueryException | \RuntimeException $exception) {
                    $isDuplicateInvoice = str_contains(
                        strtolower($exception->getMessage()),
                        'invoice'
                    );

                    if ($attempt === $maxAttempts || !$isDuplicateInvoice) {
                        throw $exception;
                    }
                }
            }
        } catch (\Throwable $exception) {
            report($exception);

            $message = str_contains(strtolower($exception->getMessage()), 'invoice')
                ? 'Gagal membuat nomor invoice unik. Silakan coba lagi.'
                : 'Transaksi stadion gagal disimpan. Silakan coba lagi.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', $message);
        }

        if (!$transaction) {
            $message = 'Transaksi stadion gagal disimpan. Silakan coba lagi.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', $message);
        }

        $message = 'Transaksi stadion berhasil ditambahkan.';

        if ($request->expectsJson()) {
            $request->session()->flash('success', $message);

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('admin.stadium-transactions.index'),
            ], 201);
        }

        return redirect()
            ->route('admin.stadium-transactions.index')
            ->with('success', $message);
    }

    public function edit(Transaction $transaction)
    {
        abort_unless($transaction->source === 'Stadium', 404);

        $transaction->load([
            'customer',
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ]);

        $items = $transaction->items
            ->map(function ($item) {
                $identity = $this->parseStadiumProductName(
                    $item->productVariant?->product?->name
                );

                return [
                    'version' => $identity['version'] ?? 'Stadium',
                    'kit' => $identity['kit'] ?? 'Home',
                    'size_id' => $item->productVariant?->size_id,
                    'qty' => (int) $item->qty,
                    'nns' => filled($item->custom_name),
                    'patch' => (bool) $item->is_patch,
                    'longsleeve' => (bool) $item->is_longsleeve,
                    'custom_name' => $item->custom_name ?? '',
                    'custom_number' => $item->custom_number ?? '',
                ];
            })
            ->values()
            ->all();

        $transactionDate = $transaction->transaction_date
            ?->copy()
            ->setTimezone('Asia/Makassar')
            ->format('Y-m-d\TH:i');

        $customerName = $transaction->shipping_name === 'Transaksi Stadion'
            ? ''
            : (string) ($transaction->shipping_name ?? '');

        return view('admin.stadium-transactions.edit', [
            'transaction' => $transaction,
            'catalog' => $this->buildStadiumCatalog(),
            'items' => $items,
            'transactionDate' => $transactionDate
                ?? now('Asia/Makassar')->format('Y-m-d\TH:i'),
            'customerName' => $customerName,
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_unless($transaction->source === 'Stadium', 404);

        $validated = $this->validateStadiumTransactionPayload($request);

        [$itemsToCreate, $subtotal] = $this->buildStadiumTransactionItems(
            $validated['items']
        );

        $transactionDate = Carbon::parse(
            $validated['transaction_date'],
            'Asia/Makassar'
        );

        $customerName = trim((string) ($validated['customer_name'] ?? ''));

        try {
            DB::transaction(function () use (
                $transaction,
                $customerName,
                $transactionDate,
                $subtotal,
                $itemsToCreate
            ) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->where('source', 'Stadium')
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedTransaction->update([
                    'customer_id' => null,
                    'transaction_date' => $transactionDate,
                    'payment_method' => 'CASH',
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'shipping' => 0,
                    'total' => $subtotal,
                    'status' => 'PAID',
                    'paid_at' => $transactionDate,
                    'source' => 'Stadium',
                    'shipping_name' => $customerName !== ''
                        ? $customerName
                        : 'Transaksi Stadion',
                ]);

                $lockedTransaction->items()->delete();

                foreach ($itemsToCreate as $item) {
                    $lockedTransaction->items()->create($item);
                }
            });
        } catch (\Throwable $exception) {
            report($exception);

            $message = 'Transaksi stadion gagal diperbarui. Silakan coba lagi.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', $message);
        }

        $message = 'Transaksi stadion berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('admin.stadium-transactions.index'),
            ]);
        }

        return redirect()
            ->route('admin.stadium-transactions.index')
            ->with('success', $message);
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        abort_unless($transaction->source === 'Stadium', 404);

        try {
            DB::transaction(function () use ($transaction) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->where('source', 'Stadium')
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedTransaction->items()->delete();
                $lockedTransaction->delete();
            });
        } catch (\Throwable $exception) {
            report($exception);

            $message = 'Transaksi stadion gagal dihapus. Silakan coba lagi.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()
                ->route('admin.stadium-transactions.index')
                ->with('error', $message);
        }

        $message = 'Transaksi stadion berhasil dihapus.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('admin.stadium-transactions.index'),
            ]);
        }

        return redirect()
            ->route('admin.stadium-transactions.index')
            ->with('success', $message);
    }

    private function validateStadiumTransactionPayload(Request $request): array
    {
        return $request->validate([
            'transaction_date' => ['required', 'date'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.version' => ['required', 'in:Stadium,Supporter'],
            'items.*.kit' => ['required', 'in:Home,Away,Third'],
            'items.*.size_id' => ['required', 'integer', 'exists:sizes,id'],
            'items.*.product_variant_id' => [
                'required',
                'integer',
                'exists:product_variants,id',
            ],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.nns' => ['sometimes', 'boolean'],
            'items.*.patch' => ['sometimes', 'boolean'],
            'items.*.longsleeve' => ['sometimes', 'boolean'],
            'items.*.custom_name' => [
                'nullable',
                'string',
                'max:12',
                'regex:/^[\pL\s]+$/u',
            ],
            'items.*.custom_number' => [
                'nullable',
                'string',
                'max:2',
                'regex:/^[0-9]{1,2}$/',
            ],
        ], [
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date' => 'Format tanggal transaksi tidak valid.',
            'items.required' => 'Minimal satu item transaksi harus ditambahkan.',
            'items.min' => 'Minimal satu item transaksi harus ditambahkan.',
            'items.*.version.in' => 'Versi produk tidak valid.',
            'items.*.kit.in' => 'Kit produk tidak valid.',
            'items.*.size_id.required' => 'Ukuran produk wajib dipilih.',
            'items.*.size_id.exists' => 'Ukuran produk tidak ditemukan.',
            'items.*.product_variant_id.required' => 'Varian produk wajib dipilih.',
            'items.*.product_variant_id.exists' => 'Varian produk tidak ditemukan.',
            'items.*.qty.required' => 'Jumlah item wajib diisi.',
            'items.*.qty.min' => 'Jumlah item minimal 1.',
            'items.*.custom_name.max' => 'Nama jersey maksimal 12 karakter.',
            'items.*.custom_name.regex' => 'Nama jersey hanya boleh berisi huruf dan spasi.',
            'items.*.custom_number.max' => 'Nomor punggung maksimal 2 digit.',
            'items.*.custom_number.regex' => 'Nomor punggung hanya boleh berisi angka.',
        ]);
    }

    private function buildStadiumTransactionItems(array $items): array
    {
        $requestedVariantIds = collect($items)
            ->pluck('product_variant_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $variants = ProductVariant::query()
            ->with(['product', 'size'])
            ->whereIn('id', $requestedVariantIds)
            ->get()
            ->keyBy('id');

        if ($variants->count() !== $requestedVariantIds->count()) {
            throw ValidationException::withMessages([
                'items' => 'Ada varian produk yang tidak ditemukan.',
            ]);
        }

        $itemsToCreate = [];
        $subtotal = 0;

        foreach ($items as $index => $item) {
            $variant = $variants->get((int) $item['product_variant_id']);
            $product = $variant?->product;

            if (!$variant || !$product || !$variant->size) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_variant_id" => 'Varian produk tidak valid.',
                ]);
            }

            $productIdentity = $this->parseStadiumProductName($product->name);

            if (
                !$productIdentity
                || strcasecmp($productIdentity['version'], $item['version']) !== 0
                || strcasecmp($productIdentity['kit'], $item['kit']) !== 0
            ) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_variant_id" => 'Varian produk tidak sesuai dengan versi dan kit yang dipilih.',
                ]);
            }

            if ((int) $variant->size_id !== (int) $item['size_id']) {
                throw ValidationException::withMessages([
                    "items.{$index}.size_id" => 'Ukuran produk tidak sesuai dengan varian yang dipilih.',
                ]);
            }

            $qty = (int) $item['qty'];
            $isNns = (bool) ($item['nns'] ?? false);
            $isPatch = (bool) ($item['patch'] ?? false);
            $isLongsleeve = (bool) ($item['longsleeve'] ?? false);

            if ($isNns && !$product->customization_enabled) {
                throw ValidationException::withMessages([
                    "items.{$index}.nns" => 'NNS tidak tersedia untuk produk ini.',
                ]);
            }

            if ($isPatch && !$product->patch_enabled) {
                throw ValidationException::withMessages([
                    "items.{$index}.patch" => 'Patch tidak tersedia untuk produk ini.',
                ]);
            }

            if ($isLongsleeve && !$product->longsleeve_enabled) {
                throw ValidationException::withMessages([
                    "items.{$index}.longsleeve" => 'Longsleeve tidak tersedia untuk produk ini.',
                ]);
            }

            $basePrice = (float) $variant->price;

            if ($basePrice < 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_variant_id" => 'Harga produk tidak valid.',
                ]);
            }

            $customizationFee = $isNns
                ? (int) $product->customization_price
                : 0;
            $patchFee = $isPatch
                ? (int) $product->patch_price
                : 0;
            $longsleeveFee = $isLongsleeve
                ? (int) $product->longsleeve_price
                : 0;

            $finalPrice = $basePrice
                + $customizationFee
                + $patchFee
                + $longsleeveFee;
            $lineSubtotal = round($finalPrice * $qty, 2);
            $subtotal += $lineSubtotal;

            $customName = trim((string) ($item['custom_name'] ?? ''));
            $customNumber = trim((string) ($item['custom_number'] ?? ''));

            if ($isNns) {
                $customName = $customName !== ''
                    ? mb_strtoupper($customName, 'UTF-8')
                    : 'NNS';
                $customNumber = $customNumber !== ''
                    ? $customNumber
                    : null;
            } else {
                $customName = null;
                $customNumber = null;
            }

            $itemsToCreate[] = [
                'product_variant_id' => $variant->id,
                'custom_name' => $customName,
                'custom_number' => $customNumber,
                'qty' => $qty,
                'price' => $finalPrice,
                'subtotal' => $lineSubtotal,
                'weight' => (int) ($variant->weight ?? 0),
                'stock_deducted_at' => null,
                'stock_restored_at' => null,
                'is_longsleeve' => $isLongsleeve,
                'longsleeve_price' => $longsleeveFee,
                'is_patch' => $isPatch,
                'patch_price' => $patchFee,
            ];
        }

        return [$itemsToCreate, round($subtotal, 2)];
    }

    private function buildStadiumCatalog(): array
    {
        return ProductVariant::query()
            ->with(['product', 'size'])
            ->whereHas('product', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%Stadium Version 2026/27%')
                        ->orWhere('name', 'like', '%Supporter Version 2026/27%');
                });
            })
            ->get()
            ->map(function (ProductVariant $variant) {
                $product = $variant->product;
                $identity = $this->parseStadiumProductName($product?->name);

                if (!$product || !$identity || !$variant->size) {
                    return null;
                }

                return [
                    'variant_id' => $variant->id,
                    'version' => $identity['version'],
                    'kit' => $identity['kit'],
                    'size_id' => $variant->size->id,
                    'size_name' => $variant->size->name,
                    'price' => (float) $variant->price,
                    'customization_enabled' => (bool) $product->customization_enabled,
                    'customization_price' => (int) $product->customization_price,
                    'longsleeve_enabled' => (bool) $product->longsleeve_enabled,
                    'longsleeve_price' => (int) $product->longsleeve_price,
                    'patch_enabled' => (bool) $product->patch_enabled,
                    'patch_price' => (int) $product->patch_price,
                ];
            })
            ->filter()
            ->sortBy(function ($item) {
                return $item['version']
                    . '|' . $item['kit']
                    . '|' . str_pad((string) $item['size_id'], 6, '0', STR_PAD_LEFT);
            })
            ->values()
            ->all();
    }

    private function parseStadiumProductName(?string $name): ?array
    {
        if (!$name) {
            return null;
        }

        if (!preg_match('/\b(Stadium|Supporter)\b/iu', $name, $versionMatch)) {
            return null;
        }

        if (!preg_match('/\b(Home|Away|Third)\b/iu', $name, $kitMatch)) {
            return null;
        }

        return [
            'version' => ucfirst(strtolower($versionMatch[1])),
            'kit' => ucfirst(strtolower($kitMatch[1])),
        ];
    }

    private function generateStadiumInvoiceNumber(): string
    {
        do {
            $invoice = 'STADIUM-'
                . now('Asia/Makassar')->format('Ymd')
                . '-'
                . strtoupper(Str::random(8));
        } while (
            Transaction::query()
                ->where('invoice_number', $invoice)
                ->exists()
        );

        return $invoice;
    }

    public function show(Transaction $transaction)
    {
        abort_unless($transaction->source === 'Stadium', 404);

        $transaction->load([
            'customer',
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ]);

        return view('admin.stadium-transactions.show', compact('transaction'));
    }
}
