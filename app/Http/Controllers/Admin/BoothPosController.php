<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\InventoryStockService;
use App\Services\DokuQrisService;
use App\Services\QrisQrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BoothPosController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('status', true)
            ->where('availability', 'ready')
            ->whereHas('variants.inventory', function ($query) {
                $query->where('stock', '>', 0);
            })
            ->with([
                'variants.size',
                'variants.color',
                'variants.inventory',
                'images',
            ])
            ->orderBy('name')
            ->get();

        $stockProducts = Product::query()
            ->where('status', true)
            ->where('availability', 'ready')
            ->with([
                'variants.size',
                'variants.color',
                'variants.inventory',
            ])
            ->orderBy('name')
            ->get();

        return view('admin.offline-transactions.pos', compact('products', 'stockProducts'));
    }

    public function store(
        Request $request,
        InventoryStockService $stockService,
        DokuQrisService $dokuQrisService,
        QrisQrCodeService $qrisQrCodeService
    ): JsonResponse {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:CASH,QRIS'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'required',
                'integer',
                'distinct',
                'exists:product_variants,id',
            ],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        $transaction = DB::transaction(function () use (
            $validated,
            $stockService,
            $dokuQrisService,
            $qrisQrCodeService
        ) {
            $requestedItems = collect($validated['items'])
                ->keyBy(fn ($item) => (int) $item['id']);

            $variantIds = $requestedItems->keys()
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $variants = ProductVariant::query()
                ->with(['product', 'size', 'color'])
                ->whereIn('id', $variantIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($variants->count() !== $requestedItems->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Ada produk yang tidak ditemukan.',
                ]);
            }

            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($requestedItems as $variantId => $requestedItem) {
                $variant = $variants->get((int) $variantId);

                if (
                    !$variant
                    || !$variant->product
                    || !$variant->product->status
                    || $variant->product->availability !== 'ready'
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Ada produk yang sudah tidak tersedia.',
                    ]);
                }

                $inventory = Inventory::query()
                    ->where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                $qty = (int) $requestedItem['qty'];

                if (!$inventory || (int) $inventory->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => sprintf(
                            'Stok %s ukuran %s tidak mencukupi. Stok tersedia: %d.',
                            $variant->product->name,
                            $variant->size?->name ?? '-',
                            (int) ($inventory?->stock ?? 0)
                        ),
                    ]);
                }

                $price = (float) $variant->price;

                if ($price < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Harga produk tidak valid.',
                    ]);
                }

                $lineSubtotal = round($price * $qty, 2);
                $subtotal += $lineSubtotal;

                $itemsToCreate[] = [
                    'product_variant_id' => $variant->id,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $lineSubtotal,
                    'weight' => (int) ($variant->weight ?? 0),
                    'custom_name' => null,
                    'custom_number' => null,
                    'is_longsleeve' => false,
                    'longsleeve_price' => 0,
                    'is_patch' => false,
                    'patch_price' => 0,
                ];
            }

            $discount = min(
                (float) ($validated['discount'] ?? 0),
                $subtotal
            );

            $invoiceNumber = $this->generateInvoiceNumber();

            $transaction = Transaction::create([
                'customer_id' => null,
                'invoice_number' => $invoiceNumber,
                'transaction_date' => now(),
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => 0,
                'total' => round($subtotal - $discount, 2),
                'status' => 'PENDING',
                'source' => 'Booth',
            ]);

            foreach ($itemsToCreate as $item) {
                $transaction->items()->create($item);
            }

            if ($validated['payment_method'] === 'CASH') {
                // Tunai tetap langsung lunas dan stok berkurang.
                $stockService->decreaseForTransaction(
                    $transaction,
                    "Penjualan tunai Kasir Booth {$invoiceNumber}"
                );
            } else {
                // QRIS belum dibayar: jangan kurangi stok sekarang.
                $validUntil = now()->addMinutes(10);

                $qrResponse = $dokuQrisService->generateQr(
                    $invoiceNumber,
                    $transaction->total,
                    $validUntil->format('Y-m-d\TH:i:sP')
                );

                if (
                    ($qrResponse['responseCode'] ?? null) !== '2004700'
                    || blank($qrResponse['referenceNo'] ?? null)
                    || blank($qrResponse['qrContent'] ?? null)
                    || ($qrResponse['partnerReferenceNo'] ?? null) !== $invoiceNumber
                ) {
                    throw new \RuntimeException(
                        'DOKU tidak mengembalikan data QRIS yang valid.'
                    );
                }

                $transaction->update([
                    'qris_reference_no' => $qrResponse['referenceNo'],
                    'qris_content' => $qrResponse['qrContent'],
                    'qris_expired_at' => $validUntil,
                    'qris_response' => $qrResponse,
                ]);
            }

            return $transaction->fresh();
        });

        $isQris = $transaction->payment_method === 'QRIS';

        return response()->json([
            'message' => $isQris
                ? 'QRIS berhasil dibuat. Menunggu pembayaran.'
                : 'Pembayaran tunai berhasil disimpan.',
            'invoice_number' => $transaction->invoice_number,
            'total' => (float) $transaction->total,
            'payment_method' => $transaction->payment_method,
            'status' => $transaction->status,
            'qris_reference_no' => $transaction->qris_reference_no,
            'qris_qr_code' => $isQris
                ? $qrisQrCodeService->generateSvg($transaction->qris_content)
                : null,
            'qris_expired_at' => $transaction->qris_expired_at?->toIso8601String(),
            'redirect' => route('admin.offline-transactions.index'),
        ]);
    }

    public function updateStock(
        Request $request,
        InventoryStockService $stockService
    ): JsonResponse {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $variant = ProductVariant::query()
            ->with('product')
            ->findOrFail($validated['variant_id']);

        if (!$variant->product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $inventory = Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->first();

        if (!$inventory) {
            return response()->json([
                'message' => 'Data inventory untuk varian ini belum tersedia.',
            ], 422);
        }

        $updatedInventory = $stockService->adjust(
            $inventory->id,
            (int) $validated['stock'],
            'Penyesuaian stok melalui Kasir Booth'
        );

        return response()->json([
            'message' => 'Stok berhasil diperbarui.',
            'variant_id' => (int) $variant->id,
            'stock' => (int) $updatedInventory->stock,
        ]);
    }
    public function paymentStatus(string $invoice): JsonResponse
    {
        $transaction = Transaction::query()
            ->where('invoice_number', $invoice)
            ->where('source', 'Booth')
            ->where('payment_method', 'QRIS')
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaksi QRIS Booth tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'invoice_number' => $transaction->invoice_number,
            'status' => $transaction->status,
            'paid_at' => $transaction->paid_at?->toIso8601String(),
        ]);
    }
    private function generateInvoiceNumber(): string
    {
        do {
            $invoice = 'BOOTH-' . now()->format('Ymd') . '-' .
                Str::upper(Str::random(6));
        } while (
            Transaction::where('invoice_number', $invoice)->exists()
        );

        return $invoice;
    }
}

