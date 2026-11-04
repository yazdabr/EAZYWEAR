<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendOrderCreatedEmail;
use App\Mail\OrderCreatedMail;
use App\Models\OrderEmailOutbox;
use App\Models\Transaction;
use App\Services\EmailArchiveService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class SendOrderCreatedEmailTest extends TestCase
{
    use DatabaseTransactions;

    private function createOutbox(string $email): OrderEmailOutbox
    {
        $transaction = Transaction::factory()->create([
            'shipping_email' => $email,
        ]);

        return OrderEmailOutbox::create([
            'transaction_id' => $transaction->id,
            'status' => OrderEmailOutbox::STATUS_PENDING,
            'available_at' => now(),
        ]);
    }

    public function test_job_sends_order_email_through_email_archive_service(): void
    {
        $outbox = $this->createOutbox('test@example.com');

        $emailArchiveService = Mockery::mock(EmailArchiveService::class);

        $emailArchiveService
            ->shouldReceive('send')
            ->once()
            ->with(
                'test@example.com',
                Mockery::type(OrderCreatedMail::class)
            );

        $job = new SendOrderCreatedEmail((int) $outbox->id);

        $job->handle($emailArchiveService);

        $this->assertDatabaseHas('order_email_outboxes', [
            'id' => $outbox->id,
            'status' => OrderEmailOutbox::STATUS_SENT,
            'attempts' => 1,
        ]);
    }

    public function test_smtp_failure_is_propagated_and_outbox_returns_to_pending(): void
    {
        $outbox = $this->createOutbox('smtp-failure@example.com');

        $emailArchiveService = Mockery::mock(EmailArchiveService::class);

        $emailArchiveService
            ->shouldReceive('send')
            ->once()
            ->with(
                'smtp-failure@example.com',
                Mockery::type(OrderCreatedMail::class)
            )
            ->andThrow(new \RuntimeException('SMTP rate limit exceeded.'));

        $job = new SendOrderCreatedEmail((int) $outbox->id);

        try {
            $job->handle($emailArchiveService);
            $this->fail('SMTP exception should be propagated to the queue.');
        } catch (\RuntimeException $exception) {
            $this->assertSame(
                'SMTP rate limit exceeded.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseHas('order_email_outboxes', [
            'id' => $outbox->id,
            'status' => OrderEmailOutbox::STATUS_PENDING,
            'attempts' => 1,
        ]);
    }

    public function test_job_can_be_attempted_again_after_smtp_failure(): void
    {
        $outbox = $this->createOutbox('smtp-retry@example.com');

        $emailArchiveService = Mockery::mock(EmailArchiveService::class);

        $emailArchiveService
            ->shouldReceive('send')
            ->twice()
            ->with(
                'smtp-retry@example.com',
                Mockery::type(OrderCreatedMail::class)
            )
            ->andReturnUsing(function () {
                static $attempt = 0;

                $attempt++;

                if ($attempt === 1) {
                    throw new \RuntimeException('Temporary SMTP failure.');
                }
            });

        $job = new SendOrderCreatedEmail((int) $outbox->id);

        try {
            $job->handle($emailArchiveService);
        } catch (\RuntimeException $exception) {
            $this->assertSame(
                'Temporary SMTP failure.',
                $exception->getMessage()
            );
        }

        // Simulasi percobaan ulang oleh Laravel Queue.
        $job->handle($emailArchiveService);

        $this->assertDatabaseHas('order_email_outboxes', [
            'id' => $outbox->id,
            'status' => OrderEmailOutbox::STATUS_SENT,
            'attempts' => 2,
        ]);
    }

    public function test_job_does_not_send_email_again_when_outbox_is_already_sent(): void
    {
        $outbox = $this->createOutbox('already-sent@example.com');

        $outbox->update([
            'status' => OrderEmailOutbox::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $emailArchiveService = Mockery::mock(EmailArchiveService::class);

        $emailArchiveService->shouldNotReceive('send');

        $job = new SendOrderCreatedEmail((int) $outbox->id);

        $job->handle($emailArchiveService);

        $this->assertDatabaseHas('order_email_outboxes', [
            'id' => $outbox->id,
            'status' => OrderEmailOutbox::STATUS_SENT,
        ]);
    }

    public function test_job_has_expected_queue_retry_and_timeout_settings(): void
    {
        $job = new SendOrderCreatedEmail(123);

        $this->assertSame('emails', $job->queue);
        $this->assertSame(5, $job->tries);
        $this->assertSame(60, $job->timeout);
        $this->assertSame([60, 300, 900, 1800], $job->backoff);
    }
}
