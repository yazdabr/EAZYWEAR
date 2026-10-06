<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DokuQrisNotificationControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const CLIENT_ID = 'TEST-QRIS-CLIENT-ID';

    private const SECRET = 'test-qris-notification-secret';

    private const REQUEST_ID =
        '11111111-2222-3333-4444-555555555555';

    private const TIMESTAMP =
        '2026-10-05T13:00:00Z';

    private const ENDPOINT =
        '/api/doku/qris/notification';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set(
            'doku-qris.client_id',
            self::CLIENT_ID
        );

        config()->set(
            'doku-qris.notification_secret',
            self::SECRET
        );
    }

    private function payload(
        string $invoice = 'INV-TEST-QRIS-001',
        float $amount = 150000,
        string $status = 'SUCCESS'
    ): array {
        return [
            'service' => [
                'id' => 'QRIS',
                'name' => 'QRIS',
            ],

            'acquirer' => [
                'id' => 'DOKU',
                'name' => 'DOKU',
            ],

            'channel' => [
                'id' => 'QRIS_DOKU',
                'name' => 'QRIS-DOKU',
            ],

            'order' => [
                'invoice_number' => $invoice,
                'amount' => $amount,
            ],

            'transaction' => [
                'status' => $status,
                'date' => '2026-10-05T13:00:00Z',
                'original_request_id' => 'QRIS-REF-001',
            ],

            'emoney_payment' => [
                'account_id' => 'TEST-ACCOUNT',
                'approval_code' => 'TEST-APPROVAL',
            ],
        ];
    }

    private function sign(
        string $body,
        ?string $clientId = null,
        ?string $requestId = null,
        ?string $timestamp = null,
        ?string $target = null
    ): string {
        $clientId ??= self::CLIENT_ID;
        $requestId ??= self::REQUEST_ID;
        $timestamp ??= self::TIMESTAMP;
        $target ??= self::ENDPOINT;

        $digest = base64_encode(
            hash(
                'sha256',
                $body,
                true
            )
        );

        $component =
            'Client-Id:' . $clientId . "\n"
            . 'Request-Id:' . $requestId . "\n"
            . 'Request-Timestamp:' . $timestamp . "\n"
            . 'Request-Target:' . $target . "\n"
            . 'Digest:' . $digest;

        return 'HMACSHA256=' . base64_encode(
            hash_hmac(
                'sha256',
                $component,
                self::SECRET,
                true
            )
        );
    }

    private function postNotification(
        array $payload,
        array $headers = []
    ) {
        $body = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );

        return $this->sendRequest(
            $body,
            $headers
        );
    }

    private function sendRequest(
        string $body,
        array $headers = []
    ) {
        $defaultHeaders = [
            'Client-Id' => self::CLIENT_ID,
            'Request-Id' => self::REQUEST_ID,
            'Request-Timestamp' => self::TIMESTAMP,
            'Signature' => $this->sign($body),
            'Content-Type' => 'application/json',
        ];

        return $this->callRaw(
            $body,
            array_merge(
                $defaultHeaders,
                $headers
            )
        );
    }

    private function callRaw(
        string $body,
        array $headers = []
    ) {
        $server = [
            'CONTENT_TYPE' => 'application/json',
        ];

        foreach ($headers as $name => $value) {
            $server[
                'HTTP_' . strtoupper(str_replace('-', '_', $name))
            ] = $value;
        }

        return $this->call(
            'POST',
            self::ENDPOINT,
            [],
            [],
            [],
            $server,
            $body
        );
    }

    private function createPayableTransaction(
        string $invoice = 'INV-TEST-QRIS-001',
        int $stock = 10,
        int $qty = 1,
    ): Transaction {
        $product = Product::create([
            'product_code' => 'TEST-QRIS-' . uniqid(),
            'category_id' => 6,
            'name' => 'Test QRIS Product',
            'slug' => 'test-qris-product-' . uniqid(),
            'description' => null,
            'material' => null,
            'status' => 1,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => 1,
            'color_id' => null,
            'sku' => 'TEST-QRIS-SKU-' . uniqid(),
            'price' => 150000,
            'weight' => 500,
        ]);

        Inventory::create([
            'product_variant_id' => $variant->id,
            'stock' => $stock,
        ]);

        $transaction = Transaction::create([
            'invoice_number' => $invoice,
            'transaction_date' => now(),
            'payment_method' => 'QRIS',
            'subtotal' => 150000,
            'discount' => 0,
            'shipping' => 0,
            'total' => 150000,
            'status' => 'PENDING',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Test Address',
            'shipping_district' => 'Test District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Ambil di Tempat',
            'qris_reference_no' => 'DOKU-REFERENCE-001',
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_variant_id' => $variant->id,
            'custom_name' => null,
            'custom_number' => null,
            'qty' => $qty,
            'price' => 150000,
            'subtotal' => 150000,
            'weight' => 500,
        ]);

        return $transaction;
    }

    public function test_valid_signature_returns_200(): void
    {
        $transaction = Transaction::create([
            'invoice_number' => 'INV-TEST-QRIS-001',
            'transaction_date' => now(),
            'payment_method' => 'QRIS',
            'subtotal' => 150000,
            'discount' => 0,
            'shipping' => 0,
            'total' => 150000,
            'status' => 'PAID',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Test Address',
            'shipping_district' => 'Test District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Ambil di Tempat',
        ]);

        $response = $this->postNotification(
            $this->payload(
                $transaction->invoice_number,
                150000
            )
        );

        $response->assertStatus(200);
    }

    public function test_invalid_signature_returns_401(): void
    {
        $body = json_encode(
            $this->payload(),
            JSON_UNESCAPED_SLASHES
        );

        $response = $this->callRaw(
            $body,
            [
                'Client-Id' => self::CLIENT_ID,
                'Request-Id' => self::REQUEST_ID,
                'Request-Timestamp' => self::TIMESTAMP,
                'Signature' => 'HMACSHA256=INVALID',
                'Content-Type' => 'application/json',
            ]
        );

        $response->assertStatus(401);
    }

    public function test_missing_headers_returns_400(): void
    {
        $body = json_encode(
            $this->payload(),
            JSON_UNESCAPED_SLASHES
        );

        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
        ])->call(
            'POST',
            self::ENDPOINT,
            content: $body
        );

        $response->assertStatus(400);
    }

    public function test_wrong_client_id_returns_401(): void
    {
        $body = json_encode(
            $this->payload(),
            JSON_UNESCAPED_SLASHES
        );

        $response = $this->callRaw(
            $body,
            [
                'Client-Id' => 'WRONG-CLIENT-ID',
                'Request-Id' => self::REQUEST_ID,
                'Request-Timestamp' => self::TIMESTAMP,
                'Signature' => $this->sign(
                    $body,
                    'WRONG-CLIENT-ID'
                ),
                'Content-Type' => 'application/json',
            ]
        );

        $response->assertStatus(401);
    }

    public function test_invalid_json_returns_400(): void
    {
        $body = '{"invalid-json"';

        $response = $this->callRaw(
            $body,
            [
                'Client-Id' => self::CLIENT_ID,
                'Request-Id' => self::REQUEST_ID,
                'Request-Timestamp' => self::TIMESTAMP,
                'Signature' => $this->sign($body),
                'Content-Type' => 'application/json',
            ]
        );

        $response->assertStatus(400);
    }

    public function test_wrong_service_returns_400(): void
    {
        $payload = $this->payload();

        $payload['service']['id'] = 'VA';

        $response = $this->postNotification($payload);

        $response->assertStatus(400);
    }

    public function test_wrong_channel_returns_400(): void
    {
        $payload = $this->payload();

        $payload['channel']['id'] = 'QRIS_OTHER';

        $response = $this->postNotification($payload);

        $response->assertStatus(400);
    }

    public function test_success_with_matching_amount_processes_payment(): void
    {
        $transaction = $this->createPayableTransaction();

        $response = $this->postNotification(
            $this->payload(
                $transaction->invoice_number,
                150000
            )
        );

        $response->assertStatus(200);

        $transaction->refresh();

        $this->assertSame(
            'PAID',
            $transaction->status
        );

        $this->assertNotNull(
            $transaction->paid_at
        );

        $this->assertSame(
            'DOKU-REFERENCE-001',
            $transaction->qris_reference_no
        );

        $this->assertNotNull(
            $transaction->qris_response
        );

        $variantId = $transaction
            ->items()
            ->first()
            ->product_variant_id;

        $this->assertSame(
            9,
            Inventory::where(
                'product_variant_id',
                $variantId
            )->value('stock')
        );
    }

    public function test_success_with_wrong_amount_is_rejected(): void
    {
        $transaction = Transaction::create([
            'invoice_number' => 'INV-TEST-QRIS-001',
            'transaction_date' => now(),
            'payment_method' => 'QRIS',
            'subtotal' => 150000,
            'discount' => 0,
            'shipping' => 0,
            'total' => 150000,
            'status' => 'PENDING',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Test Address',
            'shipping_district' => 'Test District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Ambil di Tempat',
        ]);

        $response = $this->postNotification(
            $this->payload(
                $transaction->invoice_number,
                149999
            )
        );

        $response->assertStatus(400);

        $transaction->refresh();

        $this->assertSame(
            'PENDING',
            $transaction->status
        );
    }

    public function test_duplicate_notification_is_idempotent(): void
    {
        $transaction = $this->createPayableTransaction();

        $payload = $this->payload(
            $transaction->invoice_number,
            150000
        );

        $first = $this->postNotification($payload);

        $first->assertStatus(200);

        $transaction->refresh();

        $firstPaidAt = $transaction->paid_at;

        $variantId = $transaction
            ->items()
            ->first()
            ->product_variant_id;

        $this->assertSame(
            9,
            Inventory::where(
                'product_variant_id',
                $variantId
            )->value('stock')
        );

        $second = $this->postNotification($payload);

        $second->assertStatus(200);

        $transaction->refresh();

        $this->assertSame(
            'PAID',
            $transaction->status
        );

        $this->assertEquals(
            $firstPaidAt,
            $transaction->paid_at
        );

        $this->assertSame(
            'DOKU-REFERENCE-001',
            $transaction->qris_reference_no
        );

        // Notification kedua tidak boleh mengurangi stok lagi.
        $this->assertSame(
            9,
            Inventory::where(
                'product_variant_id',
                $variantId
            )->value('stock')
        );
    }

    public function test_expired_qris_transaction_rejects_success_notification(): void
    {
        $transaction = $this->createPayableTransaction();

        $transaction->update([
            'qris_expired_at' => now()->subMinute(),
        ]);

        $transaction->refresh();

        $this->assertTrue(
            app(\App\Services\TransactionExpiryService::class)
                ->expire($transaction)
        );

        $transaction->refresh();

        $this->assertSame(
            'EXPIRED',
            $transaction->status
        );

        $variantId = $transaction
            ->items()
            ->first()
            ->product_variant_id;

        $stockBefore = Inventory::where(
            'product_variant_id',
            $variantId
        )->value('stock');

        $response = $this->postNotification(
            $this->payload(
                $transaction->invoice_number,
                150000
            )
        );

        $response->assertStatus(409)
            ->assertJson([
                'responseCode' => '4092500',
                'responseMessage' => 'Payment processing rejected.',
            ]);

        $transaction->refresh();

        $this->assertSame(
            'EXPIRED',
            $transaction->status
        );

        $this->assertNull(
            $transaction->paid_at
        );

        $this->assertSame(
            'DOKU-REFERENCE-001',
            $transaction->qris_reference_no
        );

        $stockAfter = Inventory::where(
            'product_variant_id',
            $variantId
        )->value('stock');

        $this->assertSame(
            $stockBefore,
            $stockAfter
        );
    }
}