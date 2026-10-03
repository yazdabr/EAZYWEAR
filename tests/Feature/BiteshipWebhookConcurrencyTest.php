<?php

namespace Tests\Feature;

use App\Mail\OrderCompletedMail;
use App\Models\Transaction;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class BiteshipWebhookConcurrencyTest extends TestCase
{

    private string $barrierDir;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'biteship.webhook.signature_key' => 'X-Eazywear-Biteship-Signature',
            'biteship.webhook.signature_secret' => 'test-secret-value',
        ]);

        $this->barrierDir = storage_path(
            'framework/testing/biteship-concurrency-'.Str::uuid()
        );

        File::makeDirectory($this->barrierDir, 0777, true);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->barrierDir);

        parent::tearDown();
    }

    public function test_concurrent_delivered_webhooks_complete_once_without_duplicate_history_or_email(): void
    {
        $transaction = Transaction::create([
            'invoice_number' => 'CONCURRENCY-'.Str::upper(Str::random(12)),
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 15000,
            'total' => 115000,
            'status' => Transaction::ORDER_SHIPPED,
            'source' => 'Website',

            'shipping_name' => 'Concurrency Test',
            'shipping_email' => 'concurrency@example.test',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Concurrency Test Address',
            'shipping_district' => 'Test District',
            'shipping_city' => 'Test City',
            'shipping_province' => 'Test Province',
            'shipping_postal_code' => '12345',
            'shipping_method' => 'Kurir',

            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'biteship_order_id' => 'BITESHIP-CONCURRENCY-'.Str::uuid(),
            'biteship_status' => 'picking_up',
        ]);

        $workers = [
            'A' => new Process([
                PHP_BINARY,
                base_path('tests/Support/biteship_concurrency_worker.php'),
                (string) $transaction->id,
                $this->barrierDir,
                $this->barrierDir.'/result-A.json',
                'A',
            ], base_path()),

            'B' => new Process([
                PHP_BINARY,
                base_path('tests/Support/biteship_concurrency_worker.php'),
                (string) $transaction->id,
                $this->barrierDir,
                $this->barrierDir.'/result-B.json',
                'B',
            ], base_path()),
        ];

        try {
            foreach ($workers as $worker) {
                $worker->start();
            }

            $this->waitForFile(
                $this->barrierDir.'/ready-A',
                30
            );

            $this->waitForFile(
                $this->barrierDir.'/ready-B',
                30
            );

            file_put_contents(
                $this->barrierDir.'/go',
                (string) microtime(true)
            );

            foreach ($workers as $worker) {
                $worker->wait();
            }

            foreach ($workers as $workerId => $worker) {
                $this->assertSame(
                    0,
                    $worker->getExitCode(),
                    sprintf(
                        "Worker %s failed.\nSTDOUT:\n%s\nSTDERR:\n%s",
                        $workerId,
                        $worker->getOutput(),
                        $worker->getErrorOutput()
                    )
                );
            }

            $resultA = $this->readWorkerResult('A');
            $resultB = $this->readWorkerResult('B');

            $this->assertTrue(
                $resultA['success'] ?? false,
                'Worker A failed: '.json_encode($resultA)
            );

            $this->assertTrue(
                $resultB['success'] ?? false,
                'Worker B failed: '.json_encode($resultB)
            );

            $this->assertSame(200, $resultA['response_status']);
            $this->assertSame(200, $resultB['response_status']);

            $this->assertSame(
                Transaction::ORDER_COMPLETED,
                $transaction->fresh()->status
            );

            $this->assertSame(
                1,
                $transaction
                    ->fresh()
                    ->orderStatusHistories()
                    ->where('status', Transaction::ORDER_COMPLETED)
                    ->count()
            );

            $notification = $transaction
                ->fresh()
                ->notifications()
                ->where('type', 'ORDER_COMPLETED_EMAIL')
                ->first();

            $this->assertNotNull($notification);
            $this->assertNotNull($notification->sent_at);

            $totalSentCount =
                ($resultA['sent_count'] ?? 0)
                + ($resultB['sent_count'] ?? 0);

            $this->assertSame(
                1,
                $totalSentCount,
                sprintf(
                    'Expected exactly one email send across both workers. A=%s B=%s',
                    $resultA['sent_count'] ?? 'missing',
                    $resultB['sent_count'] ?? 'missing'
                )
            );

            $this->assertSame(
                1,
                $transaction
                    ->fresh()
                    ->notifications()
                    ->where('type', 'ORDER_COMPLETED_EMAIL')
                    ->count()
            );
        } finally {
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop(3);
                }
            }

            $transaction->fresh()->delete();
        }
    }

    private function waitForFile(string $path, int $timeoutSeconds): void
    {
        $deadline = microtime(true) + $timeoutSeconds;

        while (! file_exists($path)) {
            if (microtime(true) >= $deadline) {
                $this->fail(
                    "Timed out waiting for concurrency barrier file: {$path}"
                );
            }

            usleep(10_000);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readWorkerResult(string $workerId): array
    {
        $path = $this->barrierDir."/result-{$workerId}.json";

        $this->waitForFile($path, 30);

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents);

        $result = json_decode($contents, true);

        $this->assertIsArray($result);

        return $result;
    }
}