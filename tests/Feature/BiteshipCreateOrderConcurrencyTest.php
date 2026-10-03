<?php

namespace Tests\Feature;

use App\Mail\OrderShippedMail;
use App\Models\Transaction;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class BiteshipCreateOrderConcurrencyTest extends TestCase
{
    private string $barrierDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->barrierDir = storage_path(
            'framework/testing/biteship-create-concurrency-'.Str::uuid()
        );

        File::makeDirectory(
            $this->barrierDir,
            0777,
            true
        );

        file_put_contents(
            $this->barrierDir.'/create-count.txt',
            '0'
        );
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->barrierDir);

        parent::tearDown();
    }

    public function test_concurrent_biteship_create_order_allows_only_one_external_create(): void
    {
        Mail::fake();

        $transaction = Transaction::create([
            'invoice_number' => 'CONCURRENCY-CREATE-'.Str::upper(
                Str::random(12)
            ),
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 15000,
            'total' => 115000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',

            'shipping_name' => 'Concurrency Create Test',
            'shipping_email' => 'concurrency-create@example.test',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Concurrency Create Address',
            'shipping_district' => 'Test District',
            'shipping_city' => 'Test City',
            'shipping_province' => 'Test Province',
            'shipping_postal_code' => '12345',
            'shipping_method' => 'Kurir',

            'shipping_latitude' => null,
            'shipping_longitude' => null,

            /*
             * These force the controller into the Biteship create branch.
             */
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'biteship_order_id' => null,
            'biteship_tracking_id' => null,
            'biteship_waybill_id' => null,
            'biteship_status' => null,

            'courier' => null,
            'tracking_number' => null,
        ]);

        $workers = [
            'A' => new Process([
                PHP_BINARY,
                base_path(
                    'tests/Support/biteship_create_order_concurrency_worker.php'
                ),
                (string) $transaction->id,
                $this->barrierDir,
                $this->barrierDir.'/result-A.json',
                'A',
            ], base_path()),

            'B' => new Process([
                PHP_BINARY,
                base_path(
                    'tests/Support/biteship_create_order_concurrency_worker.php'
                ),
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

            $statuses = [
                $resultA['response_status'],
                $resultB['response_status'],
            ];

            sort($statuses);

            /*
             * Cache::lock() is non-blocking here.
             *
             * Therefore the expected concurrent result is:
             * one request succeeds and one request receives 409.
             */
            $this->assertSame(
                [200, 409],
                $statuses,
                sprintf(
                    "Unexpected concurrent response statuses: A=%s B=%s\nA result: %s\nB result: %s",
                    $resultA['response_status'] ?? 'missing',
                    $resultB['response_status'] ?? 'missing',
                    json_encode($resultA, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    json_encode($resultB, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                )
            );

            $transaction->refresh();

            $this->assertSame(
                Transaction::ORDER_SHIPPED,
                $transaction->status
            );

            $this->assertSame(
                'BITESHIP-CONCURRENCY-ORDER',
                $transaction->biteship_order_id
            );

            $this->assertSame(
                'TRACK-CONCURRENCY-001',
                $transaction->biteship_tracking_id
            );

            $this->assertSame(
                'WAYBILL-CONCURRENCY-001',
                $transaction->biteship_waybill_id
            );

            $this->assertSame(
                'confirmed',
                $transaction->biteship_status
            );

            /*
             * The most important external API assertion:
             * exactly one Biteship create call occurred across
             * both independent PHP processes.
             */
            $createCount = (int) trim(
                file_get_contents(
                    $this->barrierDir.'/create-count.txt'
                )
            );

            $this->assertSame(
                1,
                $createCount,
                'Expected exactly one external Biteship create call.'
            );

            /*
             * Exactly one ORDER_SHIPPED history.
             */
            $this->assertSame(
                1,
                $transaction
                    ->orderStatusHistories()
                    ->where('status', Transaction::ORDER_SHIPPED)
                    ->count()
            );

            /*
             * Exactly one shipping notification for this transaction.
             */
            $this->assertSame(
                1,
                $transaction
                    ->notifications()
                    ->where('type', 'ORDER_SHIPPED_EMAIL')
                    ->count()
            );

            $notification = $transaction
                ->notifications()
                ->where('type', 'ORDER_SHIPPED_EMAIL')
                ->first();

            $this->assertNotNull($notification);
            $this->assertNotNull($notification->sent_at);

            /*
             * Because Mail::fake() is process-local, aggregate the
             * send count from both workers.
             */
            $totalSentCount =
                ($resultA['sent_count'] ?? 0)
                + ($resultB['sent_count'] ?? 0);

            $this->assertSame(
                1,
                $totalSentCount,
                sprintf(
                    'Expected exactly one shipping email across both workers. A=%s B=%s',
                    $resultA['sent_count'] ?? 'missing',
                    $resultB['sent_count'] ?? 'missing'
                )
            );
        } finally {
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop(3);
                }
            }

            $transaction->refresh()->delete();
        }
    }

    private function waitForFile(
        string $path,
        int $timeoutSeconds
    ): void {
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