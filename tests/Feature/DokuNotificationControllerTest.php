<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\InventoryStockService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DokuNotificationControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function createPendingTransaction(): Transaction
    {
        $variant = ProductVariant::firstOrFail();

        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'invoice_number' => 'INV-DOKU-TEST',
            'total' => $variant->price,
            'va_number' => '190089123456789012',
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_variant_id' => $variant->id,
            'qty' => 1,
            'price' => $variant->price,
            'subtotal' => $variant->price,
        ]);

        return $transaction;
    }

    private function headers(string $body): array
    {
        $timestamp = now('Asia/Makassar')->format('Y-m-d\TH:i:sP');
        $token = 'TESTTOKEN';

        $hash = strtolower(hash('sha256', $body));

        $signature = base64_encode(
            hash_hmac(
                'sha512',
                'POST:/api/doku/bca/notification:' . $token . ':' . $hash . ':' . $timestamp,
                config('doku.secret_key'),
                true
            )
        );

        return [
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'X-PARTNER-ID' => config('doku.client_id'),
            'X-EXTERNAL-ID' => 'EXT-TEST',
            'CHANNEL-ID' => 'H2H',
            'Authorization' => 'Bearer '.$token,
        ];
    }

    public function test_doku_notification_marks_transaction_paid(): void
    {
        $transaction = $this->createPendingTransaction();

        $payload = [
            'trxId' => $transaction->invoice_number,
            'paymentRequestId' => 'PAY-001',
            'virtualAccountNo' => $transaction->va_number,
            'paidAmount' => [
                'value' => number_format($transaction->total, 2, '.', ''),
                'currency' => 'IDR',
            ],
            'responseCode' => '2002600',
            'virtualAccountData' => [
                'paymentFlagReason' => [
                    'english' => 'success',
                ],
                'paidAmount' => [
                    'value' => number_format($transaction->total, 2, '.', ''),
                ],
            ],
        ];

        $body = json_encode($payload);

        $response = $this
            ->withHeaders($this->headers($body))
            ->postJson(
                url('/api/doku/bca/notification'),
                $payload
            );

        $response->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PAID',
        ]);
    }

    public function test_doku_notification_rejects_invalid_signature(): void
    {
        $payload = [
            'trxId' => 'INV-TEST',
        ];

        $response = $this
            ->withHeaders([
                'X-TIMESTAMP' => now()->format('Y-m-d\TH:i:sP'),
                'X-SIGNATURE' => 'INVALID',
                'X-PARTNER-ID' => config('doku.client_id'),
                'X-EXTERNAL-ID' => 'TEST',
                'CHANNEL-ID' => 'H2H',
                'Authorization' => 'Bearer TEST',
            ])
            ->postJson(
                url('/api/doku/bca/notification'),
                $payload
            );

        $response->assertStatus(401);
    }

    public function test_doku_notification_rejects_wrong_amount(): void
    {
        $transaction = $this->createPendingTransaction();

        $payload = [
            'trxId' => $transaction->invoice_number,
            'paymentRequestId' => 'PAY-001',
            'virtualAccountNo' => $transaction->va_number,
            'paidAmount' => [
                'value' => '1.00',
                'currency' => 'IDR',
            ],
        ];

        $body = json_encode($payload);

        $response = $this
            ->withHeaders($this->headers($body))
            ->postJson(
                url('/api/doku/bca/notification'),
                $payload
            );

        $response->assertStatus(400);
    }
}