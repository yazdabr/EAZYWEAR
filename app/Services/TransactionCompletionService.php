<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionFulfillment;
use App\Models\TransactionNotification;
use App\Jobs\SendOrderCompletedEmail;
use App\Jobs\SendPickupCompletedEmail;
use Illuminate\Support\Facades\Log;
use Throwable;
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
                    'Seluruh pesanan pengiriman telah selesai berdasarkan status delivered Biteship.'
                );
            }

            if (! $remaining && $transaction->status === Transaction::ORDER_COMPLETED) {
                $this->sendCompletionEmailIfNeeded($transaction);
            }

            return $transaction->fresh();
        });
    }
    public function markFulfillmentReadyForPickup(
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

            if ($group->shipping_method !== 'Ambil di Tempat') {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Aksi ini hanya berlaku untuk pesanan ambil di tempat.',
                ]);
            }

            if (! in_array($lockedTransaction->status, [
                Transaction::ORDER_PROCESSING,
                Transaction::ORDER_SHIPPED,
            ], true)) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi belum berada pada tahap pemenuhan pesanan.',
                ]);
            }

            if (in_array($group->status, [
                TransactionFulfillment::STATUS_COMPLETED,
                TransactionFulfillment::STATUS_CANCELLED,
            ], true)) {
                throw ValidationException::withMessages([
                    'fulfillment' => 'Kelompok pesanan ini sudah tidak dapat disiapkan.',
                ]);
            }

            if (
                $group->status !== TransactionFulfillment::STATUS_READY_FOR_PICKUP
            ) {
                $group->update([
                    'status' => TransactionFulfillment::STATUS_READY_FOR_PICKUP,
                ]);
            }

            $pickupGroups = TransactionFulfillment::query()
                ->where('transaction_id', $lockedTransaction->id)
                ->where('shipping_method', 'Ambil di Tempat');

            $allPickupGroupsReady = ! $pickupGroups
                ->whereNotIn('status', [
                    TransactionFulfillment::STATUS_READY_FOR_PICKUP,
                    TransactionFulfillment::STATUS_COMPLETED,
                ])
                ->exists();

            return [
                'transaction' => $lockedTransaction->fresh(),
                'all_pickup_groups_ready' => $allPickupGroupsReady,
            ];
        });

        if ($transaction['all_pickup_groups_ready']) {
            $this->sendCompletionEmailIfNeeded($transaction['transaction']);
        }

        return $transaction['transaction'];
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
                if (
                    $group->status !== TransactionFulfillment::STATUS_READY_FOR_PICKUP
                ) {
                    throw ValidationException::withMessages([
                        'fulfillment' => 'Pesanan harus dikonfirmasi siap diambil sebelum dikonfirmasi telah diambil pelanggan.',
                    ]);
                }
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
            if ($fulfillment->shipping_method === 'Ambil di Tempat') {
                $this->sendPickupCompletedEmailIfNeeded($transaction);
            } else {
                $this->sendCompletionEmailIfNeeded($transaction);
            }
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
        $notification = TransactionNotification::query()
            ->where('transaction_id', $transaction->id)
            ->where('type', 'ORDER_COMPLETED_EMAIL')
            ->lockForUpdate()
            ->first();

        if (! $notification) {
            $notification = TransactionNotification::create([
                'transaction_id' => $transaction->id,
                'type' => 'ORDER_COMPLETED_EMAIL',
                'status' => 'pending',
                'available_at' => now(),
            ]);
        }

        if ($notification->sent_at !== null) {
            return;
        }

        // Jangan membuat antrean baru jika email sudah diantrekan
        // atau sedang diproses oleh worker.
        if (in_array($notification->status, [
            'queued',
            'processing',
            'failed',
        ], true)) {
            return;
        }

        // Hormati jadwal retry setelah kegagalan SMTP.
        if (
            $notification->status === 'pending'
            && $notification->available_at?->isFuture()
        ) {
            return;
        }

        $notification->update([
            'status' => 'queued',
            'available_at' => null,
            'locked_until' => null,
        ]);

        $notificationId = (int) $notification->id;

        DB::afterCommit(function () use ($notificationId) {
            try {
                SendOrderCompletedEmail::dispatch($notificationId);
            } catch (Throwable $exception) {
                TransactionNotification::query()
                    ->whereKey($notificationId)
                    ->whereNull('sent_at')
                    ->update([
                        'status' => 'pending',
                        'available_at' => now(),
                        'last_error' => mb_substr(
                            $exception->getMessage(),
                            0,
                            65000
                        ),
                        'updated_at' => now(),
                    ]);

                Log::error('Failed to dispatch order completed email.', [
                    'notification_id' => $notificationId,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }
    private function sendPickupCompletedEmailIfNeeded(Transaction $transaction): void
    {
        $notification = TransactionNotification::query()
            ->where('transaction_id', $transaction->id)
            ->where('type', 'PICKUP_COMPLETED_EMAIL')
            ->lockForUpdate()
            ->first();

        if (! $notification) {
            $notification = TransactionNotification::create([
                'transaction_id' => $transaction->id,
                'type' => 'PICKUP_COMPLETED_EMAIL',
                'status' => 'pending',
                'available_at' => now(),
            ]);
        }

        if ($notification->sent_at !== null) {
            return;
        }

        if (in_array($notification->status, [
            'queued',
            'processing',
            'failed',
        ], true)) {
            return;
        }

        if (
            $notification->status === 'pending'
            && $notification->available_at?->isFuture()
        ) {
            return;
        }

        $notification->update([
            'status' => 'queued',
            'available_at' => null,
            'locked_until' => null,
        ]);

        $notificationId = (int) $notification->id;

        DB::afterCommit(function () use ($notificationId) {
            try {
                SendPickupCompletedEmail::dispatch($notificationId);
            } catch (Throwable $exception) {
                TransactionNotification::query()
                    ->whereKey($notificationId)
                    ->whereNull('sent_at')
                    ->update([
                        'status' => 'pending',
                        'available_at' => now(),
                        'last_error' => mb_substr(
                            $exception->getMessage(),
                            0,
                            65000
                        ),
                        'updated_at' => now(),
                    ]);

                Log::error('Failed to dispatch pickup completed email.', [
                    'notification_id' => $notificationId,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }
}
