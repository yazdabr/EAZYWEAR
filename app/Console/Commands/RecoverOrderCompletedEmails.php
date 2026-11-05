<?php

namespace App\Console\Commands;

use App\Jobs\SendOrderCompletedEmail;
use App\Jobs\SendPickupCompletedEmail;
use App\Models\TransactionNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

#[\Illuminate\Console\Attributes\Signature('emails:recover-completed')]
#[\Illuminate\Console\Attributes\Description('Recover order-completed emails that were not successfully queued or sent')]
class RecoverOrderCompletedEmails extends Command
{
    private const MAX_RECOVERY_ATTEMPTS = 3;

    private const STALE_QUEUE_MINUTES = 10;

    public function handle(): int
    {
        $now = now();
        $staleQueueTime = now()->subMinutes(
            self::STALE_QUEUE_MINUTES
        );

        $candidateIds = TransactionNotification::query()
            ->whereIn('type', ['ORDER_COMPLETED_EMAIL', 'PICKUP_COMPLETED_EMAIL'])
            ->whereNull('sent_at')
            ->where(function ($query) use ($now, $staleQueueTime) {
                $query
                    ->where(function ($query) use ($now) {
                        $query->where('status', 'pending')
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
                        $query->where('status', 'failed')
                            ->where(
                                'recovery_attempts',
                                '<',
                                self::MAX_RECOVERY_ATTEMPTS
                            );
                    })
                    ->orWhere(function ($query) use ($staleQueueTime) {
                        $query->where('status', 'queued')
                            ->where(
                                'recovery_attempts',
                                '<',
                                self::MAX_RECOVERY_ATTEMPTS
                            )
                            ->where('updated_at', '<=', $staleQueueTime);
                    })
                    ->orWhere(function ($query) use ($now) {
                        $query->where('status', 'processing')
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

        foreach ($candidateIds as $notificationId) {
            try {
                $claimedId = DB::transaction(function () use ($notificationId) {
                    $notification = TransactionNotification::query()
                        ->lockForUpdate()
                        ->find($notificationId);

                    if (! $notification || $notification->sent_at !== null) {
                        return null;
                    }

                    $now = now();

                    $eligible =
                        (
                            $notification->status === 'pending'
                            && $notification->recovery_attempts < self::MAX_RECOVERY_ATTEMPTS
                            && (
                                ! $notification->available_at
                                || $notification->available_at->lte($now)
                            )
                        )
                        || (
                            $notification->status === 'failed'
                            && $notification->recovery_attempts < self::MAX_RECOVERY_ATTEMPTS
                        )
                        || (
                            $notification->status === 'queued'
                            && $notification->recovery_attempts < self::MAX_RECOVERY_ATTEMPTS
                            && $notification->updated_at->lte(
                                now()->subMinutes(self::STALE_QUEUE_MINUTES)
                            )
                        )
                        || (
                            $notification->status === 'processing'
                            && $notification->recovery_attempts < self::MAX_RECOVERY_ATTEMPTS
                            && (
                                ! $notification->locked_until
                                || $notification->locked_until->lte($now)
                            )
                        );

                    if (! $eligible) {
                        return null;
                    }

                    $notification->update([
                        'status' => 'queued',
                        'recovery_attempts' => $notification->recovery_attempts + 1,
                        'available_at' => null,
                        'locked_until' => null,
                    ]);

                    return ['id' => $notification->id, 'type' => $notification->type];
                });

                if (! $claimedId) {
                    continue;
                }

                $notificationId = (int) $claimedId['id'];

                try {
                    if ($claimedId['type'] === 'PICKUP_COMPLETED_EMAIL') {
                        SendPickupCompletedEmail::dispatch($notificationId);
                    } else {
                        SendOrderCompletedEmail::dispatch($notificationId);
                    }

                    $queued++;
                } catch (Throwable $exception) {
                    TransactionNotification::query()
                        ->whereKey($notificationId)
                        ->whereNull('sent_at')
                        ->where('status', 'queued')
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

                    Log::error('Failed to recover order-completed email dispatch.', [
                        'notification_id' => $notificationId,
                        'error' => $exception->getMessage(),
                    ]);
                }
            } catch (Throwable $exception) {
                Log::error('Order-completed email recovery failed.', [
                    'notification_id' => $notificationId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info(
            "Order-completed email recovery completed. Queued: {$queued}"
        );

        return self::SUCCESS;
    }
}
