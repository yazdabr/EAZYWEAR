<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\TransactionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class TransactionCompletionConcurrencyTest extends TestCase
{
    public function test_concurrent_biteship_completion_sends_only_one_email(): void
    {
        $transaction = Transaction::factory()->create([
            'invoice_number' => 'INV-CONC-' . Str::upper(Str::random(8)),
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => 'BITE-COMP-CONC-001',
            'biteship_status' => 'delivered',
        ]);

        $baseDir = storage_path('framework/completion-concurrency');

        $readyDir = $baseDir . '/ready-' . $transaction->id;
        $resultDir = $baseDir . '/result-' . $transaction->id;
        $goFile = $baseDir . '/go-' . $transaction->id;

        $this->deleteDirectory($readyDir);
        $this->deleteDirectory($resultDir);

        if (file_exists($goFile)) {
            unlink($goFile);
        }

        mkdir($readyDir, 0777, true);
        mkdir($resultDir, 0777, true);

        $worker = base_path(
            'tests/Support/transaction_completion_concurrency_worker.php'
        );

        $processA = new Process([
            PHP_BINARY,
            $worker,
            'A',
            (string) $transaction->id,
            $readyDir,
            $goFile,
            $resultDir . '/A.json',
        ], base_path());

        $processB = new Process([
            PHP_BINARY,
            $worker,
            'B',
            (string) $transaction->id,
            $readyDir,
            $goFile,
            $resultDir . '/B.json',
        ], base_path());

        try {
            $processA->start();
            $processB->start();

            $deadline = microtime(true) + 10;

            while (
                ! file_exists($readyDir . '/A') ||
                ! file_exists($readyDir . '/B')
            ) {
                if (microtime(true) > $deadline) {
                    $processA->stop();
                    $processB->stop();

                    $this->fail(
                        'Workers did not reach the concurrency barrier.'
                    );
                }

                usleep(10_000);
            }

            /*
             * Both workers are now ready.
             * Release them at approximately the same time.
             */
            file_put_contents($goFile, 'go');

            $processA->wait();
            $processB->wait();

            $this->assertTrue(
                $processA->isSuccessful(),
                'Worker A failed.'
                . PHP_EOL
                . 'Exit code: ' . $processA->getExitCode()
                . PHP_EOL
                . 'STDERR: ' . $processA->getErrorOutput()
                . PHP_EOL
                . 'STDOUT: ' . $processA->getOutput()
                . PHP_EOL
                . 'Result: ' . (
                    file_exists($resultDir . '/A.json')
                        ? file_get_contents($resultDir . '/A.json')
                        : '[result file missing]'
                )
            );

            $this->assertTrue(
                $processB->isSuccessful(),
                'Worker B failed.'
                . PHP_EOL
                . 'Exit code: ' . $processB->getExitCode()
                . PHP_EOL
                . 'STDERR: ' . $processB->getErrorOutput()
                . PHP_EOL
                . 'STDOUT: ' . $processB->getOutput()
                . PHP_EOL
                . 'Result: ' . (
                    file_exists($resultDir . '/B.json')
                        ? file_get_contents($resultDir . '/B.json')
                        : '[result file missing]'
                )
            );

            $transaction->refresh();

            /*
             * Invariant 1:
             * Transaction must end in ORDER_COMPLETED.
             */
            $this->assertSame(
                Transaction::ORDER_COMPLETED,
                $transaction->status
            );

            /*
             * Invariant 2:
             * Exactly one ORDER_COMPLETED history.
             */
            $this->assertSame(
                1,
                DB::table('order_status_histories')
                    ->where('transaction_id', $transaction->id)
                    ->where('status', Transaction::ORDER_COMPLETED)
                    ->count()
            );

            /*
             * Invariant 3:
             * Exactly one ORDER_COMPLETED_EMAIL notification.
             */
            $this->assertSame(
                1,
                TransactionNotification::query()
                    ->where('transaction_id', $transaction->id)
                    ->where('type', 'ORDER_COMPLETED_EMAIL')
                    ->count()
            );

            $notification = TransactionNotification::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'ORDER_COMPLETED_EMAIL')
                ->firstOrFail();

            /*
             * Invariant 4:
             * The completion email notification must be marked sent.
             */
            $this->assertNotNull($notification->sent_at);

            $resultAPath = $resultDir . '/A.json';
            $resultBPath = $resultDir . '/B.json';

            $this->assertFileExists($resultAPath);
            $this->assertFileExists($resultBPath);

            $resultA = json_decode(
                file_get_contents($resultAPath),
                true
            );

            $resultB = json_decode(
                file_get_contents($resultBPath),
                true
            );

            $this->assertIsArray($resultA);
            $this->assertIsArray($resultB);

            $this->assertSame('ok', $resultA['status']);
            $this->assertSame('ok', $resultB['status']);

            /*
             * Invariant 5:
             * Across both independent PHP processes,
             * exactly one completion email was actually sent.
             */
            $totalEmails =
                ($resultA['sent_count'] ?? 0)
                + ($resultB['sent_count'] ?? 0);

            $this->assertSame(1, $totalEmails);
        } finally {
            $this->deleteDirectory($readyDir);
            $this->deleteDirectory($resultDir);

            if (file_exists($goFile)) {
                unlink($goFile);
            }

            /*
             * This test intentionally does not use DatabaseTransactions
             * because child PHP processes need to see the committed fixture.
             * Therefore clean up the fixture explicitly.
             */
            DB::table('transaction_notifications')
                ->where('transaction_id', $transaction->id)
                ->delete();

            DB::table('order_status_histories')
                ->where('transaction_id', $transaction->id)
                ->delete();

            Transaction::query()
                ->whereKey($transaction->id)
                ->delete();
        }
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}