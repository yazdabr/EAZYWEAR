<?php

use App\Mail\OrderCompletedMail;
use App\Models\Transaction;
use App\Services\TransactionCompletionService;
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$workerId = $argv[1];
$transactionId = (int) $argv[2];
$readyDir = $argv[3];
$goFile = $argv[4];
$resultFile = $argv[5];

config([
    'mail.default' => 'array',
    'queue.default' => 'sync',
]);

Mail::fake();

file_put_contents(
    $readyDir . DIRECTORY_SEPARATOR . $workerId,
    'ready'
);

$deadline = microtime(true) + 10;

while (! file_exists($goFile)) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException(
            'Timeout waiting for concurrency barrier.'
        );
    }

    usleep(10_000);
}

try {
    $transaction = Transaction::query()
        ->findOrFail($transactionId);

    app(TransactionCompletionService::class)
        ->completeFromBiteship($transaction);

    /*
     * MailFake in this Laravel version does not expose
     * getSentMailable(). Use the facade assertion API to
     * determine whether this worker sent the completion mail.
     */
    $sentCount = 0;

    try {
        Mail::assertSent(OrderCompletedMail::class);
        $sentCount = 1;
    } catch (Throwable) {
        $sentCount = 0;
    }

    file_put_contents(
        $resultFile,
        json_encode([
            'worker' => $workerId,
            'status' => 'ok',
            'sent_count' => $sentCount,
        ], JSON_PRETTY_PRINT)
    );
} catch (Throwable $e) {
    $result = [
        'worker' => $workerId,
        'status' => 'error',
        'class' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ];

    file_put_contents(
        $resultFile,
        json_encode($result, JSON_PRETTY_PRINT)
    );

    fwrite(
        STDERR,
        json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL
    );

    exit(1);
}