<?php

namespace App\Http\Controllers;

use App\Mail\OrderCreatedMail;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\BiteshipService;
use App\Services\DokuService;
use App\Services\DokuQrisService;
use App\Services\FulfillmentDateService;
use App\Services\FulfillmentHoldService;
use App\Services\FulfillmentSlotService;
use App\Services\QrisQrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\EmailArchiveService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function shippingRates(Request $request, BiteshipService $biteshipService): JsonResponse
    {
        $validated = $request->validate([
            'shipping_postal_code' => ['required', 'string', 'max:10'],
            'shipping_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'shipping_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $cart = collect($request->session()->get('cart', []));

        if ($cart->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Keranjang masih kosong.'], 422);
        }

        try {
            $items = [];

                foreach ($cart as $cartItem) {
                    $variantId = (int) ($cartItem['variant_id'] ?? 0);
                    $qty = (int) ($cartItem['qty'] ?? 0);

                    if ($variantId <= 0 || $qty <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Data keranjang tidak valid.',
                        ], 422);
                    }

                    $customName = trim((string) ($cartItem['custom_name'] ?? ''));
                    $customNumber = trim((string) ($cartItem['custom_number'] ?? ''));

                    if ($customName !== '') {
                        if (mb_strlen($customName) > 20) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Nama jersey maksimal 20 karakter.',
                            ], 422);
                        }

                        if (! preg_match('/^[\pL\s]+$/u', $customName)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Nama jersey hanya boleh berisi huruf dan spasi.',
                            ], 422);
                        }
                    }

                    if ($customNumber !== '') {
                        if (! preg_match('/^[0-9]{1,2}$/', $customNumber)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Nomor punggung hanya boleh berisi 1-2 angka.',
                            ], 422);
                        }
                    }

                    $variant = ProductVariant::query()
                        ->with('product')
                        ->find($variantId);

                    if (! $variant || ! $variant->product || ! $variant->product->status) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Salah satu produk di keranjang sudah tidak tersedia.',
                        ], 422);
                    }

                    if (
                        $variant->weight === null
                        || (float) $variant->weight <= 0
                    ) {
                        return response()->json([
                            'success' => false,
                            'message' => "Berat produk {$variant->product->name} belum tersedia. Ongkir belum dapat dihitung.",
                        ], 422);
                    }

                    $hasCustomization = $customName !== '' || $customNumber !== '';
                    $customizationEnabled = (bool) $variant->product->customization_enabled;

                    if (! $customizationEnabled && $hasCustomization) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Produk ini tidak menyediakan custom nama atau nomor.',
                        ], 422);
                    }

                    $customizationFee = (
                        $customizationEnabled && $hasCustomization
                    )
                        ? (int) $variant->product->customization_price
                        : 0;

                    $isLongsleeve = (bool) ($cartItem['is_longsleeve'] ?? false);
                    $longsleeveEnabled = (bool) $variant->product->longsleeve_enabled;

                    if ($isLongsleeve && ! $longsleeveEnabled) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Pilihan Longsleeve untuk produk ini sudah tidak tersedia.',
                        ], 422);
                    }

                    $longsleeveFee = $isLongsleeve
                        ? (int) $variant->product->longsleeve_price
                        : 0;

                    $isPatch = (bool) ($cartItem['is_patch'] ?? false);
                    $patchEnabled = (bool) $variant->product->patch_enabled;

                    if ($isPatch && ! $patchEnabled) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Pilihan Patch untuk produk ini sudah tidak tersedia.',
                        ], 422);
                    }

                    $patchFee = $isPatch
                        ? (int) $variant->product->patch_price
                        : 0;

                    $itemValue = (float) $variant->price
                        + $customizationFee
                        + $longsleeveFee
                        + $patchFee;

                    $items[] = [
                        'name' => $variant->product->name,
                        'value' => (int) round($itemValue),
                        'quantity' => $qty,
                        'weight' => (int) round((float) $variant->weight),
                    ];
                }

            $result = $biteshipService->getCourierRates([
                'destination_postal_code' => $validated['shipping_postal_code'],
                'destination_latitude' => $validated['shipping_latitude'] ?? null,
                'destination_longitude' => $validated['shipping_longitude'] ?? null,
                'items' => $items,
            ]);

            return response()->json(['success' => true, 'data' => $result['rates'] ?? []]);
        } catch (\RuntimeException $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 600) {
                report($e);

                return response()->json([
                    'success' => false,
                    'message' => 'Layanan pengiriman sedang tidak dapat digunakan. Silakan coba lagi.'
                ], 502);
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil pilihan pengiriman. Silakan coba lagi.'
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil pilihan pengiriman. Silakan coba lagi.'
            ], 422);
        }
    }

    public function fulfillmentAvailability(
        Request $request,
        FulfillmentSlotService $fulfillmentSlotService
    ): JsonResponse {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = $validated['date'];

        if (! $fulfillmentSlotService->isSpecialBatchDate($date)) {
            return response()->json([
                'success' => true,
                'available' => false,
                'date' => $date,
                'message' => 'Tanggal pickup tersebut tidak tersedia untuk periode ini.',
            ]);
        }

        try {
            $fulfillmentSlotService->ensureDateAvailable($date);

            return response()->json([
                'success' => true,
                'available' => true,
                'date' => $date,
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => true,
                'available' => false,
                'date' => $date,
                'message' => $exception->errors()['pickup_date'][0]
                    ?? 'Tanggal pickup tersebut sudah penuh. Silakan pilih tanggal lain.',
            ]);
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        $cart = collect($request->session()->get('cart', []));

        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $subtotal = $cart->sum(fn ($item) => (float) $item['price'] * (int) $item['qty']);
        $totalItems = $cart->sum('qty');

        $shippingMethods = [
            ['value' => 'Kurir', 'name' => 'Kurir', 'description' => 'Pengiriman ke alamat yang Anda masukkan.'],
            ['value' => 'Ambil di Tempat', 'name' => 'Ambil di Tempat', 'description' => 'Ambil pesanan langsung di Kantor Eazywear.'],
        ];

        $paymentMethods = [[
            'value' => 'VA',
            'name' => 'Virtual Account',
            'description' => 'Bayar menggunakan Virtual Account dari bank yang tersedia.',
        ]];

        return view('checkout.index', compact('cart', 'subtotal', 'totalItems', 'shippingMethods', 'paymentMethods'));
    }

    public function store(
        Request $request,
        DokuService $dokuService,
        DokuQrisService $dokuQrisService,
        BiteshipService $biteshipService,
        FulfillmentDateService $fulfillmentDateService,
        FulfillmentSlotService $fulfillmentSlotService,
        FulfillmentHoldService $fulfillmentHoldService
    ): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'shipping_district' => ['required', 'string', 'max:100'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_province' => ['required', 'string', 'max:100'],
            'shipping_postal_code' => ['required', 'string', 'max:10'],
            'shipping_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'shipping_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'shipping_method' => ['required', 'string', Rule::in(['Kurir', 'Ambil di Tempat'])],
            'courier_code' => [
                Rule::requiredIf(fn () => $request->input('shipping_method') === 'Kurir'),
                'nullable',
                'string',
                'max:50',
            ],
            'courier_service_code' => [
                Rule::requiredIf(fn () => $request->input('shipping_method') === 'Kurir'),
                'nullable',
                'string',
                'max:100',
            ],
            'pickup_date' => [Rule::requiredIf(fn () => $request->input('shipping_method') === 'Ambil di Tempat'), 'nullable', 'date'],
            'pickup_time_start' => [Rule::requiredIf(fn () => $request->input('shipping_method') === 'Ambil di Tempat'), 'nullable', 'date_format:H:i'],
            'pickup_time_end' => [Rule::requiredIf(fn () => $request->input('shipping_method') === 'Ambil di Tempat'), 'nullable', 'date_format:H:i'],
            'payment_method' => [
                'required',
                'string',
                Rule::in(['VA', 'QRIS']),
            ],

            'va_bank' => [
                Rule::requiredIf(fn () => $request->input('payment_method') === 'VA'),
                'nullable',
                'string',
                Rule::in(['MANDIRI', 'BNI', 'BRI', 'BSI']),
            ],
        ]);

        $cart = collect($request->session()->get('cart', []));

        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        try {
            $transaction = DB::transaction(function () use (
                $validated,
                $cart,
                $dokuService,
                $dokuQrisService,
                $biteshipService,
                $fulfillmentDateService,
                $fulfillmentSlotService,
                $fulfillmentHoldService
            ) {
                if (
                    $validated['payment_method'] === 'VA'
                    && ! $dokuService->isConfigured()
                ) {
                    throw new RuntimeException('Konfigurasi DOKU belum lengkap.');
                }

                if (
                    $validated['payment_method'] === 'QRIS'
                    && ! $dokuQrisService->isConfigured()
                ) {
                    throw new RuntimeException('Konfigurasi DOKU QRIS belum lengkap.');
                }

                $customer = Customer::query()
                    ->where(fn ($query) => $query->where('phone', $validated['phone'])->orWhere('email', $validated['email']))
                    ->first();

                if (! $customer) {
                    $customer = Customer::create([
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'email' => $validated['email'],
                    ]);
                }

                $items = [];
                $subtotal = 0;

                foreach ($cart as $cartItem) {
                    $variantId = (int) ($cartItem['variant_id'] ?? 0);
                    $qty = (int) ($cartItem['qty'] ?? 0);
                    $customName = trim((string) ($cartItem['custom_name'] ?? ''));
                    $customNumber = trim((string) ($cartItem['custom_number'] ?? ''));

                    if ($variantId <= 0 || $qty <= 0) {
                        throw ValidationException::withMessages([
                            'cart' => 'Data keranjang tidak valid.',
                        ]);
                    }

                    /*
                    * Custom nama dan nomor bersifat opsional.
                    * Tetapi jika diisi, formatnya tetap harus valid.
                    */
                    if ($customName !== '') {
                        if (mb_strlen($customName) > 20) {
                            throw ValidationException::withMessages([
                                'cart' => 'Nama jersey maksimal 20 karakter.',
                            ]);
                        }

                        if (! preg_match('/^[\pL\s]+$/u', $customName)) {
                            throw ValidationException::withMessages([
                                'cart' => 'Nama jersey hanya boleh berisi huruf dan spasi.',
                            ]);
                        }
                    }

                    if ($customNumber !== '') {
                        if (! preg_match('/^[0-9]{1,2}$/', $customNumber)) {
                            throw ValidationException::withMessages([
                                'cart' => 'Nomor punggung hanya boleh berisi 1-2 angka.',
                            ]);
                        }
                    }

                    $variant = ProductVariant::with([
                        'product',
                        'size',
                        'color',
                    ])->lockForUpdate()->find($variantId);

                    if (! $variant) {
                        throw ValidationException::withMessages([
                            'cart' => 'Salah satu produk sudah tidak tersedia.',
                        ]);
                    }

                    if (! $variant->product || ! $variant->product->status) {
                        throw ValidationException::withMessages([
                            'cart' => "Produk {$variant->product?->name} sudah tidak aktif.",
                        ]);
                    }

                    $inventory = Inventory::query()
                        ->where('product_variant_id', $variant->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $inventory) {
                        throw ValidationException::withMessages([
                            'cart' => "Stok untuk {$variant->sku} tidak ditemukan.",
                        ]);
                    }

                    $stock = (int) $inventory->stock;

                    if ($stock < $qty) {
                        throw ValidationException::withMessages([
                            'cart' => "Stok {$variant->product->name} tidak mencukupi. Stok tersedia: {$stock}.",
                        ]);
                    }

                    $hasCustomization = $customName !== '' || $customNumber !== '';
                    $customizationEnabled = (bool) $variant->product->customization_enabled;

                    if (! $customizationEnabled && $hasCustomization) {
                        throw ValidationException::withMessages([
                            'cart' => 'Produk ini tidak menyediakan custom nama atau nomor.',
                        ]);
                    }

                    $customizationFee = (
                        $customizationEnabled && $hasCustomization
                    )
                        ? (int) $variant->product->customization_price
                        : 0;

                    $isLongsleeve = (bool) ($cartItem['is_longsleeve'] ?? false);
                    $longsleeveEnabled = (bool) $variant->product->longsleeve_enabled;

                    if ($isLongsleeve && ! $longsleeveEnabled) {
                        throw ValidationException::withMessages([
                            'cart' => 'Pilihan Longsleeve untuk produk ini sudah tidak tersedia.',
                        ]);
                    }

                    $longsleeveFee = $isLongsleeve
                        ? (int) $variant->product->longsleeve_price
                        : 0;

                    $isPatch = (bool) ($cartItem['is_patch'] ?? false);
                    $patchEnabled = (bool) $variant->product->patch_enabled;

                    if ($isPatch && ! $patchEnabled) {
                        throw ValidationException::withMessages([
                            'cart' => 'Pilihan Patch untuk produk ini sudah tidak tersedia.',
                        ]);
                    }

                    $patchFee = $isPatch
                        ? (int) $variant->product->patch_price
                        : 0;

                    $price = (float) $variant->price
                        + $customizationFee
                        + $longsleeveFee
                        + $patchFee;

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
                        'is_longsleeve' => $isLongsleeve,
                        'longsleeve_price' => $longsleeveFee,
                        'is_patch' => $isPatch,
                        'patch_price' => $patchFee,
                    ];
                }

                $shipping = 0;

                if ($validated['shipping_method'] === 'Kurir') {
                    $shippingItems = [];

                    foreach ($items as $item) {
                        $weight = $item['variant']->weight;

                        if ($weight === null || (float) $weight <= 0) {
                            throw ValidationException::withMessages(['cart' => "Berat produk {$item['variant']->product->name} belum tersedia. Ongkir belum dapat dihitung."]);
                        }

                        $shippingItems[] = [
                            'name' => $item['variant']->product->name,
                            'value' => (int) round($item['price']),
                            'quantity' => $item['qty'],
                            'weight' => (int) round((float) $weight),
                        ];
                    }

                    $shippingRates = $biteshipService->getCourierRates([
                        'destination_postal_code' => $validated['shipping_postal_code'],
                        'destination_latitude' => $validated['shipping_latitude'] ?? null,
                        'destination_longitude' => $validated['shipping_longitude'] ?? null,
                        'items' => $shippingItems,
                    ]);

                    $selectedRate = collect($shippingRates['rates'] ?? [])
                        ->first(fn (array $rate) => $rate['courier_code'] === $validated['courier_code'] && $rate['service_code'] === $validated['courier_service_code']);

                    if (! $selectedRate) {
                        throw ValidationException::withMessages(['courier_code' => 'Pilihan kurir atau layanan pengiriman sudah tidak tersedia. Silakan pilih kembali.']);
                    }

                    $shipping = (int) ($selectedRate['price'] ?? 0);
                }
                $checkoutAt = now();

                $fulfillmentDate = $fulfillmentDateService->determine(
                    $validated['shipping_method'],
                    $checkoutAt,
                    $validated['pickup_date'] ?? null,
                );

                if ($validated['shipping_method'] === 'Ambil di Tempat') {
                    $fulfillmentDateService->validatePickupTime(
                        $validated['pickup_time_start'],
                        $validated['pickup_date'],
                        $checkoutAt,
                    );

                    $fulfillmentDateService->validatePickupTime(
                        $validated['pickup_time_end'],
                        $validated['pickup_date'],
                        $checkoutAt,
                    );

                    if ($validated['pickup_time_start'] >= $validated['pickup_time_end']) {
                        throw ValidationException::withMessages([
                            'pickup_time_end' => 'Waktu selesai pickup harus setelah waktu mulai pickup.',
                        ]);
                    }
                }

                $discount = 0;
                $total = $subtotal - $discount + $shipping;
                $invoiceNumber = $this->generateInvoiceNumber();

                $transaction = Transaction::create([
                    'customer_id' => $customer->id,
                    'invoice_number' => $invoiceNumber,
                    'transaction_date' => now(),
                    'payment_method' => $validated['payment_method'],
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'shipping' => $shipping,
                    'total' => $total,
                    'status' => 'PENDING',
                    'source' => 'Website',
                    'shipping_name' => $validated['name'],
                    'shipping_email' => $validated['email'],
                    'shipping_phone' => $validated['phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'shipping_district' => $validated['shipping_district'],
                    'shipping_city' => $validated['shipping_city'],
                    'shipping_province' => $validated['shipping_province'],
                    'shipping_postal_code' => $validated['shipping_postal_code'],
                    'shipping_latitude' => $validated['shipping_latitude'] ?? null,
                    'shipping_longitude' => $validated['shipping_longitude'] ?? null,
                    'shipping_method' => $validated['shipping_method'],
                    'fulfillment_date' => $fulfillmentDate->toDateString(),
                    'courier_code' => $validated['shipping_method'] === 'Kurir' ? $validated['courier_code'] : null,
                    'courier_service_code' => $validated['shipping_method'] === 'Kurir' ? $validated['courier_service_code'] : null,
                    'pickup_date' => $validated['shipping_method'] === 'Ambil di Tempat' ? $validated['pickup_date'] : null,
                    'pickup_time_start' => $validated['shipping_method'] === 'Ambil di Tempat' ? $validated['pickup_time_start'] : null,
                    'pickup_time_end' => $validated['shipping_method'] === 'Ambil di Tempat' ? $validated['pickup_time_end'] : null,
                ]);

                if ($fulfillmentSlotService->isSpecialBatchDate($fulfillmentDate)) {
                    if ($validated['shipping_method'] === 'Kurir') {
                        $hold = $fulfillmentHoldService->allocateEarliestAvailableHold(
                            $transaction
                        );

                        $fulfillmentDate = $hold->fulfillmentSlot->date;

                        $transaction->update([
                            'fulfillment_date' => $fulfillmentDate->toDateString(),
                        ]);
                    } else {
                        $fulfillmentHoldService->createHold(
                            $transaction,
                            $fulfillmentDate->toDateString()
                        );
                    }
                }

                foreach ($items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_variant_id' => $item['variant']->id,
                        'custom_name' => $item['custom_name'],
                        'custom_number' => $item['custom_number'],
                        'is_longsleeve' => $item['is_longsleeve'],
                        'longsleeve_price' => $item['longsleeve_price'],
                        'is_patch' => $item['is_patch'],
                        'patch_price' => $item['patch_price'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                        'weight' => $item['variant']->weight,
                    ]);
                }

                $transaction->addStatusHistory(Transaction::ORDER_CREATED, 'Pesanan berhasil dibuat melalui website.');

                $amount = number_format((float) $total, 2, '.', '');

                if ($validated['payment_method'] === 'VA') {
                    $vaExpiredAt = now()->addMinutes(10);

                    $vaBank = $validated['va_bank'];
                    $vaConfig = config("doku.va.banks.{$vaBank}");

                    $dokuResponse = $dokuService->createVirtualAccount([
                        'partnerServiceId' => $vaConfig['partner_service_id'],
                        'customerNo' => $vaConfig['customer_no'],
                        'virtualAccountName' => $validated['name'],
                        'virtualAccountEmail' => $validated['email'],
                        'virtualAccountPhone' => $validated['phone'],
                        'trxId' => $transaction->invoice_number,
                        'amount' => $amount,
                        'channel' => $vaConfig['channel'],
                        'expiredDate' => $vaExpiredAt
                            ->copy()
                            ->setTimezone('Asia/Makassar')
                            ->format('Y-m-d\TH:i:sP'),
                    ]);

                    $responseCode = (string) ($dokuResponse['responseCode'] ?? '');

                    if ($responseCode === '' || ! str_starts_with($responseCode, '200')) {
                        throw new RuntimeException(
                            'Create VA DOKU gagal: '
                            . ($dokuResponse['responseMessage'] ?? 'Respons tidak valid.')
                        );
                    }

                    $vaNumber = $this->extractDokuValue(
                        $dokuResponse,
                        ['virtualAccountNo', 'virtualAccountNumber']
                    );

                    $vaNumber = preg_replace('/\s+/', '', (string) $vaNumber);

                    if (! $vaNumber || ! preg_match('/^\d+$/', $vaNumber)) {
                        throw new RuntimeException(
                            'Create VA berhasil dipanggil, tetapi nomor VA tidak valid pada respons DOKU.'
                        );
                    }

                    $paymentRequestId = $this->extractDokuValue(
                        $dokuResponse,
                        ['paymentRequestId']
                    );

                    $transaction->update([
                        'doku_request_id' => $dokuResponse['_external_id'] ?? null,
                        'doku_payment_id' => $paymentRequestId,
                        'va_number' => $vaNumber,
                        'va_bank' => $vaBank,
                        'va_expired_at' => $vaExpiredAt,
                        'doku_response' => $dokuResponse,
                    ]);
                } else {
                    $qrisExpiredAt = now()->addMinutes(10);

                    $qrisResponse = $dokuQrisService->generateQr(
                        $transaction->invoice_number,
                        $amount,
                        $qrisExpiredAt
                            ->copy()
                            ->setTimezone('Asia/Makassar')
                            ->format('Y-m-d\TH:i:sP'),
                    );

                    $responseCode = (string) ($qrisResponse['responseCode'] ?? '');

                    if ($responseCode !== '2004700') {
                        throw new RuntimeException(
                            'Generate QRIS DOKU gagal: '
                            . ($qrisResponse['responseMessage'] ?? 'Respons tidak valid.')
                        );
                    }

                    $qrisReferenceNo = trim(
                        (string) ($qrisResponse['referenceNo'] ?? '')
                    );

                    $qrisContent = trim(
                        (string) ($qrisResponse['qrContent'] ?? '')
                    );

                    $partnerReferenceNo = trim(
                        (string) ($qrisResponse['partnerReferenceNo'] ?? '')
                    );

                    if ($qrisReferenceNo === '') {
                        throw new RuntimeException(
                            'Generate QRIS berhasil dipanggil, tetapi referenceNo tidak tersedia.'
                        );
                    }

                    if ($qrisContent === '') {
                        throw new RuntimeException(
                            'Generate QRIS berhasil dipanggil, tetapi qrContent tidak tersedia.'
                        );
                    }

                    if ($partnerReferenceNo !== $transaction->invoice_number) {
                        throw new RuntimeException(
                            'Respons QRIS tidak sesuai dengan invoice transaksi.'
                        );
                    }

                    $transaction->update([
                        'qris_reference_no' => $qrisReferenceNo,
                        'qris_content' => $qrisContent,
                        'qris_expired_at' => $qrisExpiredAt,
                        'qris_response' => $qrisResponse,
                    ]);
                }

                $transaction->load(['items.productVariant.product']);

                return $transaction;
            });

            app(EmailArchiveService::class)->send($transaction->shipping_email, new OrderCreatedMail($transaction));
            $request->session()->put('checkout_success_invoice', $transaction->invoice_number);
            $request->session()->forget('cart');

            return redirect()->route('checkout.success')->with('success', 'Pesanan berhasil dibuat.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Pesanan gagal dibuat. Silakan coba lagi.');
        }
    }

    public function success(
        Request $request,
        QrisQrCodeService $qrisQrCodeService
    ): View|RedirectResponse
    {
        $invoice = $request->session()->get('checkout_success_invoice');

        if (! $invoice) {
            return redirect()->route('home');
        }

        $transaction = Transaction::with([
            'customer',
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ])->where('invoice_number', $invoice)->first();

        if (! $transaction) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan.');
        }

        $qrisQrCode = null;

        if (
            $transaction->payment_method === 'QRIS' &&
            filled($transaction->qris_content)
        ) {
            $qrisQrCode = $qrisQrCodeService->generateSvg(
                $transaction->qris_content
            );
        }

        return view('checkout.success', [
            'transaction' => $transaction,
            'qrisQrCode' => $qrisQrCode,
        ]);
    }

    public function paymentStatus(Request $request): JsonResponse
    {
        $invoice = $request->session()->get('checkout_success_invoice');

        if (! $invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi pesanan tidak ditemukan.',
            ], 404);
        }

        $transaction = Transaction::query()
            ->where('invoice_number', $invoice)
            ->first();

        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'invoice_number' => $transaction->invoice_number,
            'email' => $transaction->shipping_email,
            'status' => $transaction->status,
            'paid' => filled($transaction->paid_at),
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $invoice = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Transaction::where('invoice_number', $invoice)->exists());

        return $invoice;
    }

    private function extractDokuValue(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return $data[$key];
            }
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $result = $this->extractDokuValue($value, $keys);

                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }
}
