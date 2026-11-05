<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendPickupCompletedEmail;
use App\Mail\PickupCompletedMail;
use App\Models\Transaction;
use App\Models\TransactionNotification;
use App\Services\EmailArchiveService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class SendPickupCompletedEmailTest extends TestCase
{
    use DatabaseTransactions;

    private function createNotification(string $email): TransactionNotification
    {
        $transaction = Transaction::factory()->create([
            'shipping_email' => $email,
        ]);

        return TransactionNotification::create([
            'transaction_id' => $transaction->id,
            'type' => 'PICKUP_COMPLETED_EMAIL',
            'status' => 'pending',
            'attempts' => 0,
            'recovery_attempts' => 0,
            'available_at' => now(),
        ]);
    }

    public function test_job_sends_pickup_completed_email_successfully(): void
    {
        $notification = $this->createNotification('pickup-completed@example.com');

        $emailArchiveService = Mockery::mock(EmailArchiveService::class);

        $emailArchiveService
            ->shouldReceive('send')
            ->once()
            ->with(
                'pickup-completed@example.com',
                Mockery::type(PickupCompletedMail::class)
            );

        $job = new SendPickupCompletedEmail((int) $notification->id);
        $job->handle($emailArchiveService);

        $notification->refresh();

        $this->assertSame('sent', $notification->status);
        $this->assertSame(1, $notification->attempts);
        $this->assertNotNull($notification->sent_at);
        $this->assertNull($notification->locked_until);
        $this->assertNull($notification->last_error);
    }

    public function test_smtp_failure_returns_notification_to_pending(): void
    {
        $notification = $this->createNotification('pickup-smtp-failure@example.com');
        $before = now();

        $emailArchiveService = Mockery::mock(EmailArchiveService::class);

        $emailArchiveService
            ->shouldReceive('send')
            ->once()
            ->andThrow(new RuntimeException('SMTP rate limit exceeded.'));

        $job = new SendPickupCompletedEmail((int) $notification->id);

        try {
            $job->handle($emailArchiveService);
            $this->fail('SMTP exception should be propagated.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'SMTP rate limit exceeded.',
                $exception->getMessage()
            );
        }

        $notification->refresh();

        $this->assertSame('pending', $notification->status);
        $this->assertSame(1, $notification->attempts);
        $this->assertNull($notification->sent_at);
        $this->assertNull($notification->locked_until);
        $this->assertSame(
            'SMTP rate limit exceeded.',
            $notification->last_error
        );
        $this->assertTrue(
            $notification->available_at->between(
                $before->copy()->addSeconds(59),
                $before->copy()->addSeconds(61)
            ),
            'Retry availability should be approximately 60 seconds after failure.'
        );
    }

    public function test_job_can_retry_after_smtp_failure(): void
    {
        $notification = $this->createNotification('pickup-smtp-retry@example.com');

        $emailArchiveService = Mockery::mock(EmailArchiveService::class);

        $emailArchiveService
            ->shouldReceive('send')
            ->twice()
            ->with(
                'pickup-smtp-retry@example.com',
                Mockery::type(PickupCompletedMail::class)
            )
            ->andReturnUsing(function () {
                static $attempt = 0;
                $attempt++;

                if ($attempt === 1) {
                    throw new RuntimeException('Temporary SMTP failure.');
                }
            });

        $job = new SendPickupCompletedEmail((int) $notification->id);

        try {
            $job->handle($emailArchiveService);
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Temporary SMTP failure.',
                $exception->getMessage()
            );
        }

        // Simulasi percobaan ulang oleh Laravel Queue.
        $job->handle($emailArchiveService);

        $notification->refresh();

        $this->assertSame('sent', $notification->status);
        $this->assertSame(2, $notification->attempts);
        $this->assertNotNull($notification->sent_at);
        $this->assertNull($notification->last_error);
    }

    public function test_job_does_not_send_email_again_when_already_sent(): void
    {
        $notification = $this->createNotification('pickup-already-sent@example.com');

        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $emailArchiveService = Mockery::mock(EmailArchiveService::class);
        $emailArchiveService->shouldNotReceive('send');

        $job = new SendPickupCompletedEmail((int) $notification->id);
        $job->handle($emailArchiveService);

        $this->assertSame('sent', $notification->fresh()->status);
    }

    public function test_job_does_not_send_while_another_worker_holds_lock(): void
    {
        $notification = $this->createNotification('pickup-locked@example.com');

        $notification->update([
            'status' => 'processing',
            'locked_until' => now()->addMinutes(2),
        ]);

        $emailArchiveService = Mockery::mock(EmailArchiveService::class);
        $emailArchiveService->shouldNotReceive('send');

        $job = new SendPickupCompletedEmail((int) $notification->id);
        $job->handle($emailArchiveService);

        $notification->refresh();

        $this->assertSame('processing', $notification->status);
        $this->assertSame(0, $notification->attempts);
    }

    public function test_permanently_failed_job_updates_notification_status(): void
    {
        $notification = $this->createNotification('pickup-permanent-failure@example.com');

        $job = new SendPickupCompletedEmail((int) $notification->id);
        $job->failed(new RuntimeException('Maximum attempts exceeded.'));

        $notification->refresh();

        $this->assertSame('failed', $notification->status);
        $this->assertNull($notification->sent_at);
        $this->assertNull($notification->locked_until);
        $this->assertSame(
            'Maximum attempts exceeded.',
            $notification->last_error
        );
    }

    public function test_job_has_expected_queue_retry_and_timeout_settings(): void
    {
        $job = new SendPickupCompletedEmail(123);

        $this->assertSame('emails', $job->queue);
        $this->assertSame(5, $job->tries);
        $this->assertSame(60, $job->timeout);
        $this->assertSame([60, 300, 900, 1800], $job->backoff);
    }
}
