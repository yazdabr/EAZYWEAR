<?php

namespace App\Services;

use App\Models\FulfillmentHold;
use App\Models\FulfillmentSlot;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FulfillmentHoldService
{

    public function allocateEarliestAvailableHold(Transaction $transaction): FulfillmentHold
    {
        return DB::transaction(function () use ($transaction) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->status !== 'PENDING') {
                throw ValidationException::withMessages([
                    'fulfillment_date' => 'Transaksi tidak dapat mengambil slot fulfillment.',
                ]);
            }

            $existingHold = FulfillmentHold::query()
                ->where('transaction_id', $lockedTransaction->id)
                ->lockForUpdate()
                ->first();

            if ($existingHold) {
                if ($existingHold->status === FulfillmentHold::HELD) {
                    return $existingHold;
                }

                throw ValidationException::withMessages([
                    'fulfillment_date' => 'Transaksi sudah memiliki hold fulfillment yang tidak dapat digunakan kembali.',
                ]);
            }

            $startDate = Carbon::parse(
                config('fulfillment.special_batch.start_date')
            )->startOfDay();

            $endDate = Carbon::parse(
                config('fulfillment.special_batch.end_date')
            )->startOfDay();

            $slots = FulfillmentSlot::query()
                ->whereBetween('date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ])
                ->orderBy('date')
                ->lockForUpdate()
                ->get();

            foreach ($slots as $slot) {
                $activeHoldCount = FulfillmentHold::query()
                    ->where('fulfillment_slot_id', $slot->id)
                    ->where('status', FulfillmentHold::HELD)
                    ->count();

                $reservedCount = $slot->used_count + $activeHoldCount;

                if ($reservedCount >= $slot->capacity) {
                    continue;
                }

                return FulfillmentHold::create([
                    'transaction_id' => $lockedTransaction->id,
                    'fulfillment_slot_id' => $slot->id,
                    'status' => FulfillmentHold::HELD,
                ]);
            }

            throw ValidationException::withMessages([
                'fulfillment_date' => 'Seluruh jadwal fulfillment pada batch ini sudah penuh.',
            ]);
        });
    }
    public function createHold(
        Transaction $transaction,
        string $fulfillmentDate
    ): FulfillmentHold {
        return DB::transaction(function () use (
            $transaction,
            $fulfillmentDate
        ) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->status !== 'PENDING') {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Fulfillment hold hanya dapat dibuat untuk transaksi PENDING.',
                ]);
            }

            $existingHold = FulfillmentHold::query()
                ->where('transaction_id', $lockedTransaction->id)
                ->lockForUpdate()
                ->first();

            if ($existingHold) {
                if ($existingHold->status === FulfillmentHold::HELD) {
                    return $existingHold;
                }

                throw ValidationException::withMessages([
                    'fulfillment' => 'Transaksi sudah memiliki riwayat fulfillment hold.',
                ]);
            }

            $slot = FulfillmentSlot::query()
                ->whereDate('date', $fulfillmentDate)
                ->lockForUpdate()
                ->first();

            if (! $slot) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Tanggal fulfillment tidak tersedia.',
                ]);
            }

            $activeHoldCount = FulfillmentHold::query()
                ->where('fulfillment_slot_id', $slot->id)
                ->where('status', FulfillmentHold::HELD)
                ->count();

            $reservedCount = $slot->used_count + $activeHoldCount;

            if ($reservedCount >= $slot->capacity) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Kuota fulfillment pada tanggal tersebut sudah penuh.',
                ]);
            }

            return FulfillmentHold::create([
                'transaction_id' => $lockedTransaction->id,
                'fulfillment_slot_id' => $slot->id,
                'status' => FulfillmentHold::HELD,
            ]);
        });
    }

    public function convertHoldToAllocation(
        Transaction $transaction
    ): FulfillmentHold {
        return DB::transaction(function () use ($transaction) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hold = FulfillmentHold::query()
                ->where('transaction_id', $lockedTransaction->id)
                ->lockForUpdate()
                ->first();

            if (! $hold) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Fulfillment hold tidak ditemukan.',
                ]);
            }

            if ($hold->status === FulfillmentHold::CONVERTED) {
                return $hold;
            }

            if ($hold->status !== FulfillmentHold::HELD) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Fulfillment hold tidak dapat dikonversi dari status saat ini.',
                ]);
            }

            $slot = FulfillmentSlot::query()
                ->whereKey($hold->fulfillment_slot_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($slot->used_count >= $slot->capacity) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Kuota fulfillment pada tanggal tersebut sudah penuh.',
                ]);
            }

            $slot->increment('used_count');

            $hold->update([
                'status' => FulfillmentHold::CONVERTED,
                'released_at' => null,
            ]);

            $hold->refresh();

            return $hold;
        });
    }

    public function releaseHold(
        Transaction $transaction
    ): FulfillmentHold {
        return DB::transaction(function () use ($transaction) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hold = FulfillmentHold::query()
                ->where('transaction_id', $lockedTransaction->id)
                ->lockForUpdate()
                ->first();

            if (! $hold) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Fulfillment hold tidak ditemukan.',
                ]);
            }

            if ($hold->status === FulfillmentHold::RELEASED) {
                return $hold;
            }

            if ($hold->status !== FulfillmentHold::HELD) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Allocation yang sudah dikonversi tidak dapat dilepas sebagai hold.',
                ]);
            }

            $hold->update([
                'status' => FulfillmentHold::RELEASED,
                'released_at' => now(),
            ]);

            $hold->refresh();

            return $hold;
        });
    }

    public function releasePaidAllocation(
        Transaction $transaction
    ): FulfillmentHold {
        return DB::transaction(function () use ($transaction) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hold = FulfillmentHold::query()
                ->where('transaction_id', $lockedTransaction->id)
                ->lockForUpdate()
                ->first();

            if (! $hold) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Fulfillment hold tidak ditemukan.',
                ]);
            }

            if ($hold->status === FulfillmentHold::RELEASED) {
                return $hold;
            }

            if ($hold->status !== FulfillmentHold::CONVERTED) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Fulfillment hold belum menjadi allocation PAID.',
                ]);
            }

            $slot = FulfillmentSlot::query()
                ->whereKey($hold->fulfillment_slot_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($slot->used_count > 0) {
                $slot->decrement('used_count');
            }

            $hold->update([
                'status' => FulfillmentHold::RELEASED,
                'released_at' => now(),
            ]);

            $hold->refresh();

            return $hold;
        });
    }
}