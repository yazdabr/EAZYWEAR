<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class BiteshipCreateOrderPostCreateRaceTest extends TestCase
{
    private string $barrierDir;

    private string $triggerName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->barrierDir = storage_path(
            'framework/testing/biteship-post-create-race-'.Str::uuid()
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

        $this->triggerName = 'trg_e4_fail_'.Str::lower(
            Str::random(12)
        );
    }

    protected function tearDown(): void
    {
        try {
            \DB::statement(
                'DROP TRIGGER IF EXISTS `'.$this->triggerName.'`'
            );
        } catch (\Throwable) {
        }

        File::deleteDirectory($this->barrierDir);

        parent::tearDown();
    }

    public function test_retry_after_post_create_persist_race_recovers_existing_order(): void
    {
        /*
         * This trigger only fails the session that explicitly enables
         * @e4_fail_persist.
         *
         * Worker A:
         *   external create succeeds
         *   DB persist fails
         *
         * Worker B:
         *   external duplicate
         *   GET existing
         *   DB persist succeeds
         */
        \DB::statement("
            CREATE TRIGGER `{$this->triggerName}`
            BEFORE UPDATE ON transactions
            FOR EACH ROW
            BEGIN
                IF
                    @e4_fail_persist = 1
                    AND NEW.biteship_order_id = 'BITE-E4-001'
                THEN
                    SET @e4_fail_persist = 0;

                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'E4 forced post-create persist failure';
                END IF;
            END
        ");

        $transaction = Transaction::create([
            'invoice_number' => 'E4-RACE-'.Str::upper(
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

            'shipping_name' => 'E4 Race Test',
            'shipping_email' => 'e4-race@example.test',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'E4 Race Address',
            'shipping_district' => 'Test District',
            'shipping_city' => 'Test City',
            'shipping_province' => 'Test Province',
            'shipping_postal_code' => '12345',
            'shipping_method' => 'Kurir',

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
                    'tests/Support/biteship_create_order_post_create_failure_worker.php'
                ),
                (string) $transaction->id,
                $this->barrierDir,
                $this->barrierDir.'/result-A.json',
                'A',
            ], base_path()),

            'B' => new Process([
                PHP_BINARY,
                base_path(
                    'tests/Support/biteship_create_order_post_create_failure_worker.php'
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

            /*
             * Start A first.
             */
            file_put_contents(
                $this->barrierDir.'/go-A',
                (string) microtime(true)
            );

            /*
             * Critical boundary:
             * wait until A's external Biteship CREATE succeeded.
             *
             * A is still inside ship(), and its local DB persist will
             * subsequently fail.
             */
            $this->waitForFile(
                $this->barrierDir.'/created-A',
                30
            );

            /*
             * Only now allow B to attempt the same shipment.
             *
             * A must release Cache::lock after its DB failure.
             */
            file_put_contents(
                $this->barrierDir.'/go-B',
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

            fwrite(STDERR, "\n=== WORKER A ===\n");
            fwrite(STDERR, json_encode($resultA, JSON_PRETTY_PRINT)."\n");

            fwrite(STDERR, "\n=== WORKER B ===\n");
            fwrite(STDERR, json_encode($resultB, JSON_PRETTY_PRINT)."\n");

            /*
             * A must fail at the controller level.
             */
            $this->assertSame(
                500,
                $resultA['response_status'],
                json_encode($resultA, JSON_PRETTY_PRINT)
            );

            /*
             * B must recover and succeed.
             */
            $this->assertSame(
                200,
                $resultB['response_status'],
                json_encode($resultB, JSON_PRETTY_PRINT)
            );

            /*
             * Exactly two CREATE HTTP attempts:
             *
             * A = actual creation
             * B = duplicate reference
             */
            $createCount = (int) trim(
                file_get_contents(
                    $this->barrierDir.'/create-count.txt'
                )
            );

            $this->assertSame(
                2,
                $createCount
            );

            $transaction->refresh();

            /*
             * Exactly one actual Biteship order identity.
             */
            $this->assertSame(
                'BITE-E4-001',
                $transaction->biteship_order_id
            );

            $this->assertSame(
                'TRACK-E4-001',
                $transaction->biteship_tracking_id
            );

            $this->assertSame(
                'WAYBILL-E4-001',
                $transaction->biteship_waybill_id
            );

            $this->assertSame(
                'confirmed',
                $transaction->biteship_status
            );

            $this->assertSame(
                Transaction::ORDER_SHIPPED,
                $transaction->status
            );

            /*
             * Exactly one ORDER_SHIPPED history.
             */
            $this->assertSame(
                1,
                $transaction
                    ->orderStatusHistories()
                    ->where(
                        'status',
                        Transaction::ORDER_SHIPPED
                    )
                    ->count()
            );

            /*
             * Exactly one shipping notification.
             */
            $this->assertSame(
                1,
                $transaction
                    ->notifications()
                    ->where(
                        'type',
                        'ORDER_SHIPPED_EMAIL'
                    )
                    ->count()
            );

            $notification = $transaction
                ->notifications()
                ->where(
                    'type',
                    'ORDER_SHIPPED_EMAIL'
                )
                ->first();

            $this->assertNotNull($notification);
            $this->assertNotNull($notification->sent_at);

            /*
             * Only B should have sent the successful shipping email.
             */
            $this->assertSame(
                0,
                $resultA['sent_count']
            );

            $this->assertSame(
                1,
                $resultB['sent_count']
            );

            /*
             * Local uniqueness invariant.
             */
            $this->assertSame(
                1,
                Transaction::query()
                    ->where(
                        'biteship_order_id',
                        'BITE-E4-001'
                    )
                    ->count()
            );

            $transaction->delete();

        } finally {
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop(3);
                }
            }
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
                    "Timed out waiting for barrier file: {$path}"
                );
            }

            usleep(10_000);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readWorkerResult(
        string $workerId
    ): array {
        $path = $this->barrierDir."/result-{$workerId}.json";

        $this->waitForFile($path, 30);

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents);

        $result = json_decode($contents, true);

        $this->assertIsArray($result);

        return $result;
    }
}