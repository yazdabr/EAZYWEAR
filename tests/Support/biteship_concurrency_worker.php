<?php

declare(strict_types=1);

use App\Http\Controllers\BiteshipWebhookController;
use App\Mail\OrderCompletedMail;
use App\Models\Transaction;
use Illuminate\Http\Request;
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
    'biteship.webhook.signature_key' => 'X-Eazywear-Biteship-Signature',
    'biteship.webhook.signature_secret' => 'test-secret-value',
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

file_put_contents($readyFile, (string) getmypid());

$deadline = microtime(true) + 30;

while (! file_exists($goFile)) {
    if (microtime(true) >= $deadline) {
        file_put_contents($resultFile, json_encode([
            'worker' => $workerId,
            'success' => false,
            'error' => 'Timed out waiting for concurrency barrier.',
        ], JSON_PRETTY_PRINT));

        exit(3);
    }

    usleep(10_000);
}

try {
    Mail::fake();

    $transaction = Transaction::query()->findOrFail($transactionId);

    $payload = [
        'event' => 'order.status',
        'order_id' => $transaction->biteship_order_id,
        'status' => 'delivered',
    ];

    $request = Request::create(
        '/webhooks/biteship',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-Eazywear-Biteship-Signature' => 'test-secret-value',
        ],
        json_encode($payload)
    );

    $response = app(BiteshipWebhookController::class)->handle($request);

    $sentCount = Mail::sent(OrderCompletedMail::class)->count();

    file_put_contents($resultFile, json_encode([
        'worker' => $workerId,
        'pid' => getmypid(),
        'success' => true,
        'response_status' => $response->getStatusCode(),
        'response_body' => $response->getContent(),
        'sent_count' => $sentCount,
    ], JSON_PRETTY_PRINT));

    exit(0);
} catch (Throwable $e) {
    file_put_contents($resultFile, json_encode([
        'worker' => $workerId,
        'pid' => getmypid(),
        'success' => false,
        'error' => $e->getMessage(),
        'exception' => get_class($e),
        'trace' => $e->getTraceAsString(),
    ], JSON_PRETTY_PRINT));

    fwrite(STDERR, $e."\n");

    exit(1);
}