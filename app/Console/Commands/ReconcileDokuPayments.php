<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\DokuService;
use App\Services\TransactionPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReconcileDokuPayments extends Command
{
    protected $signature = 'transactions:reconcile-doku {--limit=20}';

    protected $description = 'Mengecek pembayaran DOKU untuk transaksi PENDING sebagai backup callback';

    public function handle(
        DokuService $dokuService,
        TransactionPaymentService $transactionPaymentService
    ): int {
        $limit = max(1, min((int) $this->option('limit'), 50));

        $transactions = Transaction::query()
            ->where('status', 'PENDING')
            ->whereNotNull('va_number')
            ->whereNotNull('va_expired_at')
            ->where('va_expired_at', '>', now('UTC'))
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('Tidak ada transaksi PENDING yang perlu direconcile.');

            return self::SUCCESS;
        }

        $checkedCount = 0;
        $paidCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($transactions as $transaction) {
            $checkedCount++;

            try {
                $virtualAccountNo = (string) data_get(
                    $transaction->doku_response,
                    'virtualAccountData.virtualAccountNo'
                );

                $partnerServiceIdRaw = (string) data_get(
                    $transaction->doku_response,
                    'virtualAccountData.partnerServiceId'
                );

                $partnerServiceIdDigits = preg_replace(
                    '/\D/',
                    '',
                    $partnerServiceIdRaw
                );

                if (
                    $virtualAccountNo === ''
                    || $partnerServiceIdDigits === ''
                    || ! str_starts_with(
                        $virtualAccountNo,
                        $partnerServiceIdDigits
                    )
                ) {
                    $skippedCount++;

                    Log::warning('DOKU reconciliation skipped invalid VA.', [
                        'transaction_id' => $transaction->id,
                        'invoice' => $transaction->invoice_number,
                    ]);

                    continue;
                }

                $customerNo = (string) data_get(
                    $transaction->doku_response,
                    'virtualAccountData.customerNo'
                );

                if ($customerNo === '') {
                    $skippedCount++;

                    Log::warning(
                        'DOKU reconciliation skipped missing customer number.',
                        [
                            'transaction_id' => $transaction->id,
                            'invoice' => $transaction->invoice_number,
                        ]
                    );

                    continue;
                }

                $payload = [
                    'partnerServiceId' => $partnerServiceIdRaw,
                    'customerNo' => $customerNo,
                    'virtualAccountNo' => (string) $transaction->va_number,
                    'trxId' => (string) $transaction->invoice_number,
                ];

                if ($transaction->doku_payment_id) {
                    $payload['paymentRequestId'] = $transaction->doku_payment_id;
                }

                $result = $dokuService->checkVirtualAccountStatus($payload);

                $httpStatus = $result['http_status'] ?? null;
                $response = $result['response'] ?? [];

                Log::info('DOKU reconciliation response.', [
                    'transaction_id' => $transaction->id,
                    'invoice' => $transaction->invoice_number,
                    'http_status' => $httpStatus,
                    'response_code' => $response['responseCode'] ?? null,
                ]);

                if (
                    $httpStatus === null
                    || $httpStatus < 200
                    || $httpStatus >= 300
                ) {
                    $failedCount++;

                    $this->warn(
                        "Transaction #{$transaction->id}: DOKU HTTP "
                        . ($httpStatus ?? 'unknown')
                    );

                    continue;
                }

                $responseCode = $response['responseCode'] ?? null;
                $paymentFlagReason = data_get(
                    $response,
                    'virtualAccountData.paymentFlagReason.english'
                );
                $paidAmount = data_get(
                    $response,
                    'virtualAccountData.paidAmount.value'
                );

                $isPaymentSuccessful =
                    $responseCode === '2002600'
                    && strtolower((string) $paymentFlagReason) === 'success'
                    && $paidAmount !== null
                    && $paidAmount !== '';

                if (! $isPaymentSuccessful) {
                    $skippedCount++;

                    continue;
                }

                /*
                 * Final payment transition tetap wajib melalui
                 * TransactionPaymentService.
                 *
                 * Service tersebut juga melindungi transaksi EXPIRED,
                 * CANCELLED, dan race dengan payment/cancellation.
                 */
                $transactionPaymentService->processSuccessfulPayment(
                    $transaction,
                    $response,
                    'Automatic DOKU reconciliation'
                );

                $paidCount++;

                $this->info(
                    "Transaction #{$transaction->id} "
                    . "({$transaction->invoice_number}) → PAID"
                );
            } catch (ValidationException $e) {
                $failedCount++;

                Log::warning(
                    'DOKU reconciliation payment validation failed.',
                    [
                        'transaction_id' => $transaction->id,
                        'invoice' => $transaction->invoice_number,
                        'errors' => $e->errors(),
                    ]
                );

                $this->warn(
                    "Transaction #{$transaction->id}: payment validation failed."
                );
            } catch (\Throwable $e) {
                $failedCount++;

                Log::error('DOKU reconciliation failed.', [
                    'transaction_id' => $transaction->id,
                    'invoice' => $transaction->invoice_number,
                    'error' => $e->getMessage(),
                ]);

                $this->error(
                    "Transaction #{$transaction->id}: {$e->getMessage()}"
                );
            }
        }

        $this->info(
            "Reconciliation selesai: "
            . "{$checkedCount} checked, "
            . "{$paidCount} paid, "
            . "{$skippedCount} skipped, "
            . "{$failedCount} failed."
        );

        return self::SUCCESS;
    }
}