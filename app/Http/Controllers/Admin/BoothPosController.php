<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Services\InventoryStockService;
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
        InventoryStockService $stockService
    ): JsonResponse {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:CASH,TRANSFER,QRIS'],
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
            $stockService
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
                'source' => Transaction::SOURCE_POS,
            ]);

            foreach ($itemsToCreate as $item) {
                $transaction->items()->create($item);
            }

            $paymentLabel = match ($validated['payment_method']) {
                'TRANSFER' => 'transfer',
                'QRIS' => 'QRIS offline',
                default => 'tunai',
            };

            // Semua metode pembayaran POS langsung lunas.
            // QRIS menggunakan QR milik toko di luar integrasi DOKU.
            $stockService->decreaseForTransaction(
                $transaction,
                "Penjualan {$paymentLabel} Kasir POS {$invoiceNumber}"
            );

            $transaction->addStatusHistory(
                Transaction::PAYMENT_CONFIRMED,
                "Pembayaran {$paymentLabel} diterima di Kasir POS."
            );

            $transaction->updateStatus(
                Transaction::ORDER_COMPLETED,
                'Transaksi POS Kasir selesai.'
            );

            return $transaction->fresh();
        });

        $message = match ($transaction->payment_method) {
            'QRIS' => 'Pembayaran QRIS offline berhasil disimpan.',
            'TRANSFER' => 'Pembayaran transfer berhasil disimpan.',
            default => 'Pembayaran tunai berhasil disimpan.',
        };

        return response()->json([
            'message' => $message,
            'invoice_number' => $transaction->invoice_number,
            'total' => (float) $transaction->total,
            'payment_method' => $transaction->payment_method,
            'status' => $transaction->status,

            // Dipertahankan sebagai null agar kompatibel dengan response POS lama.
            'qris_reference_no' => null,
            'qris_qr_code' => null,
            'qris_expired_at' => null,

            'redirect' => route('admin.kasir-transactions.index'),
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

    private function generateInvoiceNumber(): string
    {
        do {
            $invoice = 'KASIR-' . now()->format('Ymd') . '-' .
                Str::upper(Str::random(6));
        } while (
            Transaction::where('invoice_number', $invoice)->exists()
        );

        return $invoice;
    }
}

