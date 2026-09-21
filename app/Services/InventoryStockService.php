<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class InventoryStockService
{
    /**
     * Mengurangi stok inventory dan mencatat stock movement OUT.
     *
     * Catatan:
     * Method ini harus dipanggil setelah pembayaran berhasil
     * dan setelah transaksi telah dikunci untuk mencegah
     * pemrosesan pembayaran ganda.
     */
    public function decrease(
        int $inventoryId,
        int $quantity,
        ?string $description = null
    ): Inventory {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah pengurangan stok harus lebih dari 0.',
            ]);
        }

        return DB::transaction(function () use (
            $inventoryId,
            $quantity,
            $description
        ) {
            $inventory = Inventory::query()
                ->whereKey($inventoryId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw ValidationException::withMessages([
                    'inventory' => 'Inventory tidak ditemukan.',
                ]);
            }

            $stockBefore = (int) $inventory->stock;

            if ($stockBefore < $quantity) {
                throw ValidationException::withMessages([
                    'stock' => sprintf(
                        'Stok tidak mencukupi. Tersedia: %d, diminta: %d.',
                        $stockBefore,
                        $quantity
                    ),
                ]);
            }

            $stockAfter = $stockBefore - $quantity;

            $inventory->update([
                'stock' => $stockAfter,
            ]);

            StockMovement::create([
                'inventory_id' => $inventory->id,
                'type' => 'OUT',
                'qty' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'description' => $description,
            ]);

            return $inventory->fresh();
        });
    }

    /**
     * Menambah stok inventory dan mencatat stock movement IN.
     */
    public function increase(
        int $inventoryId,
        int $quantity,
        ?string $description = null
    ): Inventory {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah penambahan stok harus lebih dari 0.',
            ]);
        }

        return DB::transaction(function () use (
            $inventoryId,
            $quantity,
            $description
        ) {
            $inventory = Inventory::query()
                ->whereKey($inventoryId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw ValidationException::withMessages([
                    'inventory' => 'Inventory tidak ditemukan.',
                ]);
            }

            $stockBefore = (int) $inventory->stock;
            $stockAfter = $stockBefore + $quantity;

            $inventory->update([
                'stock' => $stockAfter,
            ]);

            StockMovement::create([
                'inventory_id' => $inventory->id,
                'type' => 'IN',
                'qty' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'description' => $description,
            ]);

            return $inventory->fresh();
        });
    }

    /**
     * Menyesuaikan stok inventory secara langsung.
     *
     * $newStock adalah jumlah stok akhir yang diinginkan.
     */
    public function adjust(
        int $inventoryId,
        int $newStock,
        ?string $description = null
    ): Inventory {
        if ($newStock < 0) {
            throw ValidationException::withMessages([
                'stock' => 'Stok akhir tidak boleh kurang dari 0.',
            ]);
        }

        return DB::transaction(function () use (
            $inventoryId,
            $newStock,
            $description
        ) {
            $inventory = Inventory::query()
                ->whereKey($inventoryId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw ValidationException::withMessages([
                    'inventory' => 'Inventory tidak ditemukan.',
                ]);
            }

            $stockBefore = (int) $inventory->stock;
            $difference = $newStock - $stockBefore;

            if ($difference === 0) {
                return $inventory->fresh();
            }

            $inventory->update([
                'stock' => $newStock,
            ]);

            StockMovement::create([
                'inventory_id' => $inventory->id,
                'type' => 'ADJUSTMENT',
                'qty' => abs($difference),
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'description' => $description,
            ]);

            return $inventory->fresh();
        });
    }

    public function decreaseForTransaction(
        Transaction $transaction,
        ?string $description = null
    ): Collection {
        return DB::transaction(function () use ($transaction, $description) {
            $lockedTransaction = Transaction::query()
                ->with([
                    'items' => function ($query) {
                        $query->orderBy('product_variant_id');
                    },
                ])
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $items = $lockedTransaction->items;

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi tidak memiliki item.',
                ]);
            }

            $allItemsDeducted = $items->every(
                fn ($item) => $item->stock_deducted_at !== null
            );

            if ($allItemsDeducted) {
                return collect();
            }
            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi tidak memiliki item.',
                ]);
            }

            if (!in_array($lockedTransaction->status, ['PENDING', 'PAID'], true)) {
                throw ValidationException::withMessages([
                    'transaction' => sprintf(
                        'Transaksi dengan status %s tidak dapat diproses.',
                        $lockedTransaction->status
                    ),
                ]);
            }

            foreach ($items as $item) {
                if ((int) $item->qty <= 0) {
                    throw ValidationException::withMessages([
                        'stock' => "Jumlah item {$item->id} tidak valid.",
                    ]);
                }
                
                if ($item->stock_deducted_at !== null) {
                    continue;
                }
            }

            $inventoryUpdates = collect();

            foreach ($items as $item) {
                if ($item->stock_deducted_at !== null) {
                    continue;
                }

                $inventory = Inventory::query()
                    ->where('product_variant_id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    throw ValidationException::withMessages([
                        'stock' => sprintf(
                            'Inventory untuk varian %d tidak ditemukan.',
                            $item->product_variant_id
                        ),
                    ]);
                }

                $quantity = (int) $item->qty;
                $stockBefore = (int) $inventory->stock;

                if ($stockBefore < $quantity) {
                    throw ValidationException::withMessages([
                        'stock' => sprintf(
                            'Stok tidak mencukupi untuk varian %d. Tersedia: %d, diminta: %d.',
                            $item->product_variant_id,
                            $stockBefore,
                            $quantity
                        ),
                    ]);
                }

                $stockAfter = $stockBefore - $quantity;

                $inventory->update([
                    'stock' => $stockAfter,
                ]);

                StockMovement::create([
                    'inventory_id' => $inventory->id,
                    'transaction_id' => $lockedTransaction->id,
                    'transaction_item_id' => $item->id,
                    'type' => 'OUT',
                    'qty' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'description' => $description
                        ?? "Stock deducted for transaction #{$lockedTransaction->id}",
                ]);

                $item->update([
                    'stock_deducted_at' => now(),
                ]);

                $inventoryUpdates->push($inventory->fresh());
            }

            /*
            * Status pembayaran sebaiknya diubah setelah
            * seluruh item berhasil diproses.
            */
            $lockedTransaction->update([
                'status' => 'PAID',
                'paid_at' => $lockedTransaction->paid_at ?? now(),
            ]);

            return $inventoryUpdates;
        });
    }

    public function restoreForTransaction(
        Transaction $transaction,
        ?string $description = null
    ): Collection {
        return DB::transaction(function () use ($transaction, $description) {
            $lockedTransaction = Transaction::query()
                ->with('items')
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $items = $lockedTransaction->items;

            $allItemsRestored = $items->isNotEmpty()
                && $items->every(
                    fn ($item) => $item->stock_restored_at !== null
                );

            if (
                $lockedTransaction->status === 'CANCELLED'
                && $allItemsRestored
            ) {
                return collect();
            }

            if ($lockedTransaction->status !== 'PAID') {
                throw ValidationException::withMessages([
                    'transaction' => 'Hanya transaksi PAID yang dapat direstock.',
                ]);
            }

            $items = $lockedTransaction->items;

            $inventoryUpdates = collect();

            foreach ($items as $item) {
                if ($item->stock_deducted_at === null || $item->stock_restored_at !== null) {
                    continue;
                }

                $inventory = Inventory::query()
                    ->where('product_variant_id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    throw ValidationException::withMessages([
                        'stock' => sprintf(
                            'Inventory untuk varian %d tidak ditemukan.',
                            $item->product_variant_id
                        ),
                    ]);
                }

                $quantity = (int) $item->qty;

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'stock' => "Jumlah item {$item->id} tidak valid.",
                    ]);
                }

                $stockBefore = (int) $inventory->stock;
                $stockAfter = $stockBefore + $quantity;

                $inventory->update([
                    'stock' => $stockAfter,
                ]);

                StockMovement::create([
                    'inventory_id' => $inventory->id,
                    'transaction_id' => $lockedTransaction->id,
                    'transaction_item_id' => $item->id,
                    'type' => 'IN',
                    'qty' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'description' => $description
                        ?? "Stock restored for transaction #{$lockedTransaction->id}",
                ]);

                $item->update([
                    'stock_restored_at' => now(),
                ]);

                $inventoryUpdates->push($inventory->fresh());
            }

            $lockedTransaction->update([
                'status' => 'CANCELLED',
            ]);

            return $inventoryUpdates;
        });
    }
}