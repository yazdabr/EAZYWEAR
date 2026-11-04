<?php

namespace App\Console\Commands;

use App\Jobs\SendOrderCreatedEmail;
use App\Models\OrderEmailOutbox;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecoverOrderEmails extends Command
{
    protected $signature = 'emails:recover-orders';

    protected $description = 'Recover order-created emails that were not successfully queued or sent';

    private const MAX_RECOVERY_ATTEMPTS = 3;

    private const STALE_QUEUE_MINUTES = 10;

    public function handle(): int
    {
        $now = now();
        $staleQueueTime = now()->subMinutes(
            self::STALE_QUEUE_MINUTES
        );

        $candidateIds = OrderEmailOutbox::query()
            ->where(function ($query) use ($now, $staleQueueTime) {
                $query
                    ->where(function ($query) use ($now) {
                        $query->where(
                            'status',
                            OrderEmailOutbox::STATUS_PENDING
                        )
                        ->where(
                            'recovery_attempts',
                            '<',
                            self::MAX_RECOVERY_ATTEMPTS
                        )
                        ->where(function ($query) use ($now) {
                            $query->whereNull('available_at')
                                ->orWhere('available_at', '<=', $now);
                        });
                    })
                    ->orWhere(function ($query) {
                        $query->where(
                            'status',
                            OrderEmailOutbox::STATUS_FAILED
                        )->where(
                            'recovery_attempts',
                            '<',
                            self::MAX_RECOVERY_ATTEMPTS
                        );
                    })
                    ->orWhere(function ($query) use ($staleQueueTime) {
                        $query->where(
                            'status',
                            OrderEmailOutbox::STATUS_QUEUED
                        )
                        ->where(
                            'recovery_attempts',
                            '<',
                            self::MAX_RECOVERY_ATTEMPTS
                        )
                        ->where('updated_at', '<=', $staleQueueTime);
                    })
                    ->orWhere(function ($query) use ($now) {
                        $query->where(
                            'status',
                            OrderEmailOutbox::STATUS_PROCESSING
                        )
                        ->where(
                            'recovery_attempts',
                            '<',
                            self::MAX_RECOVERY_ATTEMPTS
                        )
                        ->where(function ($query) use ($now) {
                            $query->whereNull('locked_until')
                                ->orWhere('locked_until', '<=', $now);
                        });
                    });
            })
            ->orderBy('id')
            ->limit(100)
            ->pluck('id');

        $queued = 0;

        foreach ($candidateIds as $outboxId) {
            try {
                $claimedId = DB::transaction(function () use ($outboxId) {
                    $outbox = OrderEmailOutbox::query()
                        ->lockForUpdate()
                        ->find($outboxId);

                    if (! $outbox) {
                        return null;
                    }

                    $now = now();

                    $eligible =
                        (
                            $outbox->status === OrderEmailOutbox::STATUS_PENDING
                            && $outbox->recovery_attempts < self::MAX_RECOVERY_ATTEMPTS
                            && (
                                ! $outbox->available_at
                                || $outbox->available_at->lte($now)
                            )
                        )
                        || (
                            $outbox->status === OrderEmailOutbox::STATUS_FAILED
                            && $outbox->recovery_attempts < self::MAX_RECOVERY_ATTEMPTS
                        )
                        || (
                            $outbox->status === OrderEmailOutbox::STATUS_QUEUED
                            && $outbox->recovery_attempts < self::MAX_RECOVERY_ATTEMPTS
                            && $outbox->updated_at->lte(
                                now()->subMinutes(self::STALE_QUEUE_MINUTES)
                            )
                        )
                        || (
                            $outbox->status === OrderEmailOutbox::STATUS_PROCESSING
                            && $outbox->recovery_attempts < self::MAX_RECOVERY_ATTEMPTS
                            && (
                                ! $outbox->locked_until
                                || $outbox->locked_until->lte($now)
                            )
                        );

                    if (! $eligible) {
                        return null;
                    }

                    $outbox->update([
                        'status' => OrderEmailOutbox::STATUS_QUEUED,
                        'recovery_attempts' => $outbox->recovery_attempts + 1,
                        'available_at' => null,
                        'locked_until' => null,
                    ]);

                    return $outbox->id;
                });

                if (! $claimedId) {
                    continue;
                }

                try {
                    SendOrderCreatedEmail::dispatch((int) $claimedId);
                    $queued++;
                } catch (Throwable $exception) {
                    OrderEmailOutbox::query()
                        ->whereKey($claimedId)
                        ->where('status', OrderEmailOutbox::STATUS_QUEUED)
                        ->update([
                            'status' => OrderEmailOutbox::STATUS_PENDING,
                            'available_at' => now(),
                            'last_error' => mb_substr(
                                $exception->getMessage(),
                                0,
                                65000
                            ),
                            'updated_at' => now(),
                        ]);

                    Log::error('Failed to recover order email dispatch.', [
                        'outbox_id' => $claimedId,
                        'error' => $exception->getMessage(),
                    ]);
                }
            } catch (Throwable $exception) {
                Log::error('Order email recovery failed.', [
                    'outbox_id' => $outboxId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info("Order email recovery completed. Queued: {$queued}");

        return self::SUCCESS;
    }
}
