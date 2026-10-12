<?php

namespace App\Services;

use App\Mail\OrderCompletedMail;
use App\Models\Transaction;
use App\Models\TransactionFulfillment;
use App\Models\TransactionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionCompletionService
{
    public function completeManually(Transaction $transaction): Transaction
    {
        // New mixed orders are completed group-by-group. Legacy orders retain old behavior.
        if ($transaction->fulfillments()->exists()) {
            throw ValidationException::withMessages([
                'transaction' => 'Transaksi memiliki kelompok pengiriman. Selesaikan masing-masing kelompok melalui panel fulfillment.',
            ]);
        }
        return $this->completeLegacy($transaction, false);
    }

    public function completeFromBiteship(Transaction $transaction): Transaction
    {
        if ($transaction->fulfillments()->exists()) {
            throw ValidationException::withMessages([
                'transaction' => 'Transaksi memiliki kelompok fulfillment; webhook harus menyelesaikan kelompok berdasarkan Biteship order ID.',
            ]);
        }
        return $this->completeLegacy($transaction, true);
    }

    public function completeFulfillmentFromBiteship(TransactionFulfillment $fulfillment): Transaction
    {
        return DB::transaction(function () use ($fulfillment) {
            $transaction = Transaction::query()
                ->whereKey($fulfillment->transaction_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedFulfillment = TransactionFulfillment::query()
                ->whereKey($fulfillment->id)
                ->where('transaction_id', $transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedFulfillment->biteship_status !== 'delivered') {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Kelompok fulfillment belum berstatus delivered dari Biteship.',
                ]);
            }

            if ($lockedFulfillment->status !== TransactionFulfillment::STATUS_COMPLETED) {
                $lockedFulfillment->update(['status' => TransactionFulfillment::STATUS_COMPLETED]);
            }

            $remaining = $transaction->fulfillments()
                ->where('status', '!=', TransactionFulfillment::STATUS_COMPLETED)
                ->exists();

            if (! $remaining && $transaction->status !== Transaction::ORDER_COMPLETED) {
                $transaction->updateStatus(
                    Transaction::ORDER_COMPLETED,
                    'Seluruh kelompok pengiriman telah selesai berdasarkan status delivered Biteship.'
                );
            }

            if (! $remaining && $transaction->status === Transaction::ORDER_COMPLETED) {
                $this->sendCompletionEmailIfNeeded($transaction);
            }

            return $transaction->fresh();
        });
    }
    public function completeFulfillmentManually(
        TransactionFulfillment $fulfillment
    ): Transaction {
        $transaction = DB::transaction(function () use ($fulfillment) {
            $lockedTransaction = Transaction::query()
                ->whereKey($fulfillment->transaction_id)
                ->lockForUpdate()
                ->firstOrFail();

            $group = TransactionFulfillment::query()
                ->whereKey($fulfillment->id)
                ->where('transaction_id', $lockedTransaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($group->status === TransactionFulfillment::STATUS_COMPLETED) {
                return $lockedTransaction->fresh();
            }

            if (! in_array($lockedTransaction->status, [
                Transaction::ORDER_PROCESSING,
                Transaction::ORDER_SHIPPED,
            ], true)) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi belum berada pada tahap pemenuhan pesanan.',
                ]);
            }

            if ($group->shipping_method === 'Ambil di Tempat') {
                // Pickup boleh diselesaikan setelah transaksi diproses.
            } elseif ($group->shipping_method === 'Kurir') {
                if ($group->status !== TransactionFulfillment::STATUS_SHIPPED) {
                    throw ValidationException::withMessages([
                        'fulfillment' => 'Pesanan kurir harus berstatus terkirim sebelum diselesaikan.',
                    ]);
                }
            } else {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Metode pemenuhan kelompok pesanan tidak valid.',
                ]);
            }

            $group->update([
                'status' => TransactionFulfillment::STATUS_COMPLETED,
            ]);

            $remaining = TransactionFulfillment::query()
                ->where('transaction_id', $lockedTransaction->id)
                ->where('status', '!=', TransactionFulfillment::STATUS_COMPLETED)
                ->exists();

            if (
                ! $remaining
                && $lockedTransaction->status !== Transaction::ORDER_COMPLETED
            ) {
                $lockedTransaction->updateStatus(
                    Transaction::ORDER_COMPLETED,
                    'Seluruh kelompok pemenuhan pesanan telah diselesaikan oleh admin.'
                );
            }

            return $lockedTransaction->fresh();
        });

        if ($transaction->status === Transaction::ORDER_COMPLETED) {
            $this->sendCompletionEmailIfNeeded($transaction);
        }

        return $transaction;
    }


    private function completeLegacy(Transaction $transaction, bool $fromBiteship): Transaction
    {
        return DB::transaction(function () use ($transaction, $fromBiteship) {
            $locked = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($fromBiteship && $locked->status === Transaction::ORDER_COMPLETED) {
                $this->sendCompletionEmailIfNeeded($locked);
                return $locked->fresh();
            }

            $allowed = $fromBiteship
                ? ($locked->shipping_method === 'Kurir' && $locked->status === Transaction::ORDER_SHIPPED)
                : (
                    ($locked->shipping_method === 'Ambil di Tempat' && $locked->status === Transaction::ORDER_PROCESSING)
                    || ($locked->shipping_method === 'Kurir' && $locked->status === Transaction::ORDER_SHIPPED)
                );

            if (! $allowed) {
                throw ValidationException::withMessages([
                    'transaction' => 'Status transaksi belum dapat diselesaikan.',
                ]);
            }

            $locked->updateStatus(
                Transaction::ORDER_COMPLETED,
                $fromBiteship
                    ? 'Pesanan telah selesai berdasarkan status delivered dari Biteship.'
                    : 'Pesanan telah diselesaikan oleh admin.'
            );
            $this->sendCompletionEmailIfNeeded($locked);

            return $locked->fresh();
        });
    }

    private function sendCompletionEmailIfNeeded(Transaction $transaction): void
    {
        $notification = TransactionNotification::firstOrCreate([
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
        ]);

        if ($notification->sent_at !== null) return;

        try {
            app(EmailArchiveService::class)->send(
                $transaction->shipping_email,
                new OrderCompletedMail($transaction->fresh())
            );
            $notification->update(['sent_at' => now()]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
