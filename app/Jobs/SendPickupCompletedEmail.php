<?php

namespace App\Jobs;

use App\Mail\PickupCompletedMail;
use App\Models\Transaction;
use App\Models\TransactionNotification;
use App\Services\EmailArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPickupCompletedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 60;

    public array $backoff = [60, 300, 900, 1800];

    public function __construct(public int $notificationId)
    {
        $this->onQueue('emails');
    }

    public function handle(EmailArchiveService $emailArchiveService): void
    {
        $notification = DB::transaction(function () {
            $record = TransactionNotification::query()
                ->lockForUpdate()
                ->findOrFail($this->notificationId);

            if (
                $record->sent_at !== null
                || $record->type !== 'PICKUP_COMPLETED_EMAIL'
            ) {
                return null;
            }

            if (
                $record->status === 'processing'
                && $record->locked_until?->isFuture()
            ) {
                return null;
            }

            $record->update([
                'status' => 'processing',
                'attempts' => $record->attempts + 1,
                'locked_until' => now()->addSeconds($this->timeout + 30),
                'last_error' => null,
            ]);

            return $record;
        });

        if (! $notification) {
            return;
        }

        try {
            $transaction = Transaction::query()
                ->with(['items.productVariant.product'])
                ->findOrFail($notification->transaction_id);

            $emailArchiveService->send(
                $transaction->shipping_email,
                new PickupCompletedMail($transaction)
            );

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
                'available_at' => null,
                'locked_until' => null,
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            $attempt = max(1, $this->attempts());

            $backoff = $this->backoff;
            $delay = $backoff[$attempt - 1] ?? end($backoff);

            $notification->update([
                'status' => 'pending',
                'available_at' => now()->addSeconds($delay),
                'locked_until' => null,
                'last_error' => mb_substr(
                    $exception->getMessage(),
                    0,
                    65000
                ),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        TransactionNotification::query()
            ->whereKey($this->notificationId)
            ->whereNull('sent_at')
            ->update([
                'status' => 'failed',
                'locked_until' => null,
                'last_error' => mb_substr(
                    $exception->getMessage(),
                    0,
                    65000
                ),
                'updated_at' => now(),
            ]);

        Log::error('Pickup completed email permanently failed.', [
            'notification_id' => $this->notificationId,
            'error' => $exception->getMessage(),
        ]);
    }
}
