<?php

declare(strict_types=1);

use App\Mail\OrderShippedMail;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
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
$goFile = $barrierDir.'/go-'.$workerId;
$createdFile = $barrierDir.'/created-'.$workerId;
$counterFile = $barrierDir.'/create-count.txt';

file_put_contents(
    $readyFile,
    (string) getmypid(),
    LOCK_EX
);

$deadline = microtime(true) + 30;

while (! file_exists($goFile)) {
    if (microtime(true) >= $deadline) {
        file_put_contents(
            $resultFile,
            json_encode([
                'worker' => $workerId,
                'pid' => getmypid(),
                'success' => false,
                'error' => 'Timed out waiting for worker go signal.',
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
     * Load the transaction before registering the HTTP fake.
     *
     * The invoice number is the Biteship reference_id and must be
     * returned by the fake GET when duplicate-reference recovery
     * calls GET /v1/orders/{order_id}.
     */
    $transaction = Transaction::query()
        ->findOrFail($transactionId);

    $referenceId = $transaction->invoice_number;

    Http::fake(function ($request) use (
        $counterFile,
        $createdFile,
        $referenceId
    ) {
        $url = $request->url();

        /*
         * POST /v1/orders
         */
        if (
            $request->method() === 'POST'
            && str_contains($url, '/v1/orders')
        ) {
            $handle = fopen($counterFile, 'c+');

            if ($handle === false) {
                throw new RuntimeException(
                    'Unable to open Biteship create counter.'
                );
            }

            try {
                if (! flock($handle, LOCK_EX)) {
                    throw new RuntimeException(
                        'Unable to lock Biteship create counter.'
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

            /*
             * First external CREATE actually creates the order.
             *
             * This is the critical E.4 boundary:
             * external state exists before local persistence succeeds.
             */
            if ($count === 1) {
                file_put_contents(
                    $createdFile,
                    (string) microtime(true),
                    LOCK_EX
                );

                return Http::response([
                    'success' => true,
                    'id' => 'BITE-E4-001',
                    'status' => 'confirmed',
                    'reference_id' => $referenceId,
                    'courier' => [
                        'company' => 'jnt',
                        'type' => 'ez',
                        'tracking_id' => 'TRACK-E4-001',
                        'waybill_id' => 'WAYBILL-E4-001',
                    ],
                ], 200);
            }

            /*
             * Any subsequent CREATE using the same reference_id
             * simulates Biteship duplicate-reference behavior.
             */
            return Http::response([
                'success' => false,
                'code' => 40002060,
                'message' => 'Reference ID already exists.',
                'details' => [
                    'order_id' => 'BITE-E4-001',
                    'reference_id' => $referenceId,
                ],
            ], 400);
        }

        /*
         * GET existing order after duplicate reference.
         *
         * IMPORTANT:
         * GET has no JSON request payload containing reference_id.
         * Therefore reference_id must come from the known transaction.
         */
        if (
            $request->method() === 'GET'
            && str_contains($url, '/v1/orders/BITE-E4-001')
        ) {
            return Http::response([
                'success' => true,
                'id' => 'BITE-E4-001',
                'reference_id' => $referenceId,
                'status' => 'confirmed',
                'courier' => [
                    'company' => 'jnt',
                    'type' => 'ez',
                    'tracking_id' => 'TRACK-E4-001',
                    'waybill_id' => 'WAYBILL-E4-001',
                ],
            ], 200);
        }

        return Http::response([
            'success' => false,
            'message' => 'Unexpected Biteship endpoint.',
        ], 500);
    });

    /*
     * Worker A intentionally fails the local persistence after the
     * external CREATE has succeeded.
     *
     * Worker B does not enable this session variable.
     *
     * MySQL session variables are connection-specific, so A's forced
     * failure does not affect B's database connection.
     */
    if ($workerId === 'A') {
        DB::statement('SET @e4_fail_persist = 1');
    } else {
        DB::statement('SET @e4_fail_persist = 0');
    }

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

    $result = [
        'worker' => $workerId,
        'pid' => getmypid(),
        'success' => true,
        'response_class' => get_class($response),
        'response_status' => $response->getStatusCode(),
        'response_body' => $response->getContent(),
        'response_headers' => $response->headers->all(),
        'sent_count' => Mail::sent(OrderShippedMail::class)->count(),
        'created_marker' => file_exists($createdFile),
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