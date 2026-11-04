<?php

namespace App\Jobs;

use App\Mail\OrderCreatedMail;
use App\Models\OrderEmailOutbox;
use App\Models\Transaction;
use App\Services\EmailArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 60;

    public array $backoff = [60, 300, 900, 1800];

    public function __construct(public int $outboxId)
    {
        $this->onQueue('emails');
    }

    public function handle(EmailArchiveService $emailArchiveService): void
    {
        // Ambil hak pemrosesan secara atomik agar Job ganda
        // tidak mengirim email secara bersamaan.
        $outbox = DB::transaction(function () {
            $record = OrderEmailOutbox::query()
                ->lockForUpdate()
                ->findOrFail($this->outboxId);

            if ($record->status === OrderEmailOutbox::STATUS_SENT) {
                return null;
            }

            if (
                $record->status === OrderEmailOutbox::STATUS_PROCESSING
                && $record->locked_until?->isFuture()
            ) {
                return null;
            }

            $record->update([
                'status' => OrderEmailOutbox::STATUS_PROCESSING,
                'attempts' => $record->attempts + 1,
                'locked_until' => now()->addSeconds($this->timeout + 30),
                'last_error' => null,
            ]);

            return $record;
        });

        if (! $outbox) {
            return;
        }

        try {
            $transaction = Transaction::with([
                'items.productVariant.product',
            ])->findOrFail($outbox->transaction_id);

            $emailArchiveService->send(
                $transaction->shipping_email,
                new OrderCreatedMail($transaction)
            );

            $outbox->update([
                'status' => OrderEmailOutbox::STATUS_SENT,
                'sent_at' => now(),
                'locked_until' => null,
                'available_at' => null,
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            $outbox->update([
                'status' => OrderEmailOutbox::STATUS_PENDING,
                'locked_until' => null,
                'available_at' => now(),
                'last_error' => mb_substr($exception->getMessage(), 0, 65000),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        OrderEmailOutbox::query()
            ->whereKey($this->outboxId)
            ->where('status', '!=', OrderEmailOutbox::STATUS_SENT)
            ->update([
                'status' => OrderEmailOutbox::STATUS_FAILED,
                'locked_until' => null,
                'last_error' => mb_substr($exception->getMessage(), 0, 65000),
                'updated_at' => now(),
            ]);

        Log::error('Order created email permanently failed.', [
            'outbox_id' => $this->outboxId,
            'error' => $exception->getMessage(),
        ]);
    }
}