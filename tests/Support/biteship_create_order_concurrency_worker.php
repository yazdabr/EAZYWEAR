<?php

declare(strict_types=1);

use App\Mail\OrderShippedMail;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

require __DIR__.'/../../vendor/autoload.php';

putenv('APP_ENV=testing');
putenv('DB_CONNECTION=mysql');
putenv('DB_DATABASE=eazywear_testing');
putenv('MAIL_MAILER=array');
putenv('QUEUE_CONNECTION=sync');

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config([
    'biteship.base_url' => 'https://api.biteship.com',
    'biteship.api_key' => 'test-api-key',
]);

$transactionId = (int) ($argv[1] ?? 0);
$barrierDir = $argv[2] ?? '';
$resultFile = $argv[3] ?? '';
$workerId = $argv[4] ?? 'unknown';

if ($transactionId <= 0 || $barrierDir === '' || $resultFile === '') {
    fwrite(STDERR, "Invalid worker arguments.\n");
    exit(2);
}

@mkdir($barrierDir, 0777, true);

$readyFile = $barrierDir."/ready-{$workerId}";
$goFile = $barrierDir.'/go';
$counterFile = $barrierDir.'/create-count.txt';

file_put_contents($readyFile, (string) getmypid());

$deadline = microtime(true) + 30;

while (! file_exists($goFile)) {
    if (microtime(true) >= $deadline) {
        file_put_contents(
            $resultFile,
            json_encode([
                'worker' => $workerId,
                'pid' => getmypid(),
                'success' => false,
                'error' => 'Timed out waiting for concurrency barrier.',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );

        exit(3);
    }

    usleep(10_000);
}

try {
    Mail::fake();

    /*
     * Each independent worker gets its own Http::fake().
     * The shared counter proves how many external create calls
     * actually happened across both PHP processes.
     */
    Http::fake(function ($request) use ($counterFile) {
        $url = $request->url();

        if (! str_contains($url, '/orders')) {
            return Http::response([
                'success' => false,
                'message' => 'Unexpected Biteship endpoint.',
            ], 500);
        }

        $handle = fopen($counterFile, 'c+');

        if ($handle === false) {
            throw new RuntimeException(
                'Unable to open Biteship create counter file.'
            );
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new RuntimeException(
                    'Unable to lock Biteship create counter file.'
                );
            }

            rewind($handle);

            $contents = stream_get_contents($handle);
            $count = (int) trim($contents ?: '0');
            $count++;

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string) $count);
            fflush($handle);

            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        return Http::response([
            'success' => true,
            'id' => 'BITESHIP-CONCURRENCY-ORDER',
            'status' => 'confirmed',
            'reference_id' => $request->data()['reference_id'] ?? null,
            'courier' => [
                'company' => 'jnt',
                'type' => 'ez',
                'tracking_id' => 'TRACK-CONCURRENCY-001',
                'waybill_id' => 'WAYBILL-CONCURRENCY-001',
            ],
        ], 200);
    });

    $transaction = Transaction::query()
        ->findOrFail($transactionId);

    $response = app(
        \App\Http\Controllers\Admin\TransactionController::class
    )->ship(
        \Illuminate\Http\Request::create(
            route('admin.transactions.ship', $transaction),
            'PATCH',
            [
                'courier' => null,
                'tracking_number' => null,
            ]
        ),
        $transaction,
        app(\App\Services\BiteshipService::class)
    );

    $sentCount = Mail::sent(OrderShippedMail::class)->count();

    $result = [
        'worker' => $workerId,
        'pid' => getmypid(),
        'success' => true,
        'response_class' => get_class($response),
        'response_status' => $response->getStatusCode(),
        'response_body' => $response->getContent(),
        'response_headers' => $response->headers->all(),
        'sent_count' => $sentCount,
    ];

    file_put_contents(
        $resultFile,
        json_encode(
            $result,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ),
        LOCK_EX
    );

    exit(0);

} catch (Throwable $e) {

    $result = [
        'worker' => $workerId,
        'pid' => getmypid(),
        'success' => false,
        'error' => $e->getMessage(),
        'exception' => get_class($e),
        'trace' => $e->getTraceAsString(),
    ];

    file_put_contents(
        $resultFile,
        json_encode(
            $result,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ),
        LOCK_EX
    );

    fwrite(STDERR, $e."\n");

    exit(1);
}