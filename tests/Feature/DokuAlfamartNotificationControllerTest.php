<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DokuAlfamartNotificationControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const CLIENT_ID = 'TEST-MINIMARKET-CLIENT-ID';

    private const SECRET = 'TEST-MINIMARKET-SECRET-KEY';

    private const REQUEST_ID =
        '11111111-2222-3333-4444-555555555555';

    private const TIMESTAMP =
        '2026-11-25T05:00:00Z';

    private const ENDPOINT =
        '/api/doku/alfamart/notification';

    private const PAYMENT_CODE = '1900600000000046';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set(
            'doku-minimarket.client_id',
            self::CLIENT_ID
        );

        config()->set(
            'doku-minimarket.secret_key',
            self::SECRET
        );
    }

    private function payload(
        string $invoice = 'INV-TEST-ALFA-001',
        int $amount = 150000,
        string $status = 'SUCCESS',
        string $paymentCode = self::PAYMENT_CODE
    ): array {
        return [
            'service' => [
                'id' => 'ONLINE_TO_OFFLINE',
                'name' => 'ONLINE TO OFFLINE',
            ],

            'acquirer' => [
                'id' => 'ALFA',
                'name' => 'ALFAMART',
            ],

            'channel' => [
                'id' => 'ONLINE_TO_OFFLINE_ALFA',
                'name' => 'ALFAMART',
            ],

            'order' => [
                'invoice_number' => $invoice,
                'amount' => $amount,
            ],

            'transaction' => [
                'status' => $status,
                'date' => '2026-11-25T05:00:00Z',
                'original_request_id' => 'ALFA-REF-001',
            ],

            'online_to_offline_info' => [
                'payment_code' => $paymentCode,
            ],

            'online_to_offline_payment' => [
                'identifier' => [
                    'TEST-IDENTIFIER',
                ],
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
            hash('sha256', $body, true)
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

        return $this->sendRequest($body, $headers);
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
            array_merge($defaultHeaders, $headers)
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
        string $invoice = 'INV-TEST-ALFA-001',
        int $stock = 10,
        int $qty = 1
    ): Transaction {
        $unique = uniqid();

        $product = Product::create([
            'product_code' => 'TEST-ALFA-' . $unique,
            'category_id' => 6,
            'name' => 'Test Alfamart Product',
            'slug' => 'test-alfamart-product-' . $unique,
            'description' => null,
            'material' => null,
            'status' => 1,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => 1,
            'color_id' => null,
            'sku' => 'TEST-ALFA-SKU-' . $unique,
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
            'payment_method' => 'ALFAMART',
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
            'minimarket_payment_code' => self::PAYMENT_CODE,
            'minimarket_store' => 'ALFAMART',
            'minimarket_request_id' => 'ALFA-REF-001',
            'minimarket_expired_at' => now()->addDay(),
            'minimarket_response' => [
                'online_to_offline_info' => [
                    'payment_code' => self::PAYMENT_CODE,
                ],
            ],
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

    public function test_valid_signature_with_matching_payment_processes_alfamart_payment(): void
    {
        $transaction = $this->createPayableTransaction();

        $response = $this->postNotification(
            $this->payload($transaction->invoice_number)
        );

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('PAID', $transaction->status);
        $this->assertNotNull($transaction->paid_at);
        $this->assertNotNull($transaction->doku_payment_id);

        $this->assertSame(
            self::PAYMENT_CODE,
            $transaction->minimarket_payment_code
        );

        $this->assertArrayHasKey(
            'notification',
            $transaction->minimarket_response
        );

        $variantId = $transaction->items()->first()->product_variant_id;

        $this->assertSame(
            9,
            Inventory::where(
                'product_variant_id',
                $variantId
            )->value('stock')
        );
    }

    public function test_invalid_signature_returns_401(): void
    {
        $body = json_encode(
            $this->payload(),
            JSON_UNESCAPED_SLASHES
        );

        $response = $this->callRaw($body, [
            'Client-Id' => self::CLIENT_ID,
            'Request-Id' => self::REQUEST_ID,
            'Request-Timestamp' => self::TIMESTAMP,
            'Signature' => 'HMACSHA256=INVALID',
            'Content-Type' => 'application/json',
        ]);

        $response->assertUnauthorized();
    }

    public function test_wrong_client_id_returns_401(): void
    {
        $body = json_encode(
            $this->payload(),
            JSON_UNESCAPED_SLASHES
        );

        $response = $this->callRaw($body, [
            'Client-Id' => 'WRONG-CLIENT-ID',
            'Request-Id' => self::REQUEST_ID,
            'Request-Timestamp' => self::TIMESTAMP,
            'Signature' => $this->sign(
                $body,
                'WRONG-CLIENT-ID'
            ),
            'Content-Type' => 'application/json',
        ]);

        $response->assertUnauthorized();
    }

    public function test_wrong_acquirer_returns_400(): void
    {
        $payload = $this->payload();

        $payload['acquirer']['id'] = 'DOKU';

        $this->postNotification($payload)->assertBadRequest();
    }

    public function test_wrong_channel_returns_400(): void
    {
        $payload = $this->payload();

        $payload['channel']['id'] = 'OTHER_CHANNEL';

        $this->postNotification($payload)->assertBadRequest();
    }

    public function test_wrong_payment_code_is_rejected(): void
    {
        $transaction = $this->createPayableTransaction();

        $response = $this->postNotification(
            $this->payload(
                $transaction->invoice_number,
                150000,
                'SUCCESS',
                '9999999999999999'
            )
        );

        $response->assertBadRequest();

        $transaction->refresh();

        $this->assertSame('PENDING', $transaction->status);
    }

    public function test_wrong_original_request_id_is_rejected(): void
    {
        $transaction = $this->createPayableTransaction();

        $payload = $this->payload($transaction->invoice_number);

        $payload['transaction']['original_request_id'] = 'WRONG-REQUEST-ID';

        $response = $this->postNotification($payload);

        $response->assertBadRequest();

        $transaction->refresh();

        $this->assertSame('PENDING', $transaction->status);
    }

    public function test_wrong_amount_is_rejected(): void
    {
        $transaction = $this->createPayableTransaction();

        $response = $this->postNotification(
            $this->payload(
                $transaction->invoice_number,
                149999
            )
        );

        $response->assertBadRequest();

        $transaction->refresh();

        $this->assertSame('PENDING', $transaction->status);
    }

    public function test_duplicate_notification_does_not_reduce_stock_twice(): void
    {
        $transaction = $this->createPayableTransaction();

        $payload = $this->payload($transaction->invoice_number);

        $this->postNotification($payload)->assertOk();

        $transaction->refresh();

        $firstPaidAt = $transaction->paid_at;

        $variantId = $transaction->items()->first()->product_variant_id;

        $this->assertSame(
            9,
            Inventory::where(
                'product_variant_id',
                $variantId
            )->value('stock')
        );

        $this->postNotification($payload)->assertOk();

        $transaction->refresh();

        $this->assertSame('PAID', $transaction->status);

        $this->assertEquals(
            $firstPaidAt,
            $transaction->paid_at
        );

        $this->assertSame(
            9,
            Inventory::where(
                'product_variant_id',
                $variantId
            )->value('stock')
        );
    }

    public function test_late_notification_expires_pending_transaction_without_processing_payment(): void
    {
        $transaction = $this->createPayableTransaction();

        $transaction->update([
            'minimarket_expired_at' => now()->subMinute(),
        ]);

        $variantId = $transaction->items()->first()->product_variant_id;

        $stockBefore = (int) Inventory::where(
            'product_variant_id',
            $variantId
        )->value('stock');

        $response = $this->postNotification(
            $this->payload($transaction->invoice_number)
        );

        $response->assertStatus(409);

        $transaction->refresh();

        $this->assertSame('EXPIRED', $transaction->status);
        $this->assertNull($transaction->paid_at);

        $stockAfter = (int) Inventory::where(
            'product_variant_id',
            $variantId
        )->value('stock');

        $this->assertSame($stockBefore, $stockAfter);
    }

    public function test_expired_alfamart_transaction_cannot_be_recovered(): void
    {
        $transaction = $this->createPayableTransaction();

        $transaction->update([
            'minimarket_expired_at' => now()->subMinute(),
        ]);

        $this->assertTrue(
            app(\App\Services\TransactionExpiryService::class)
                ->expire($transaction->refresh())
        );

        $transaction->refresh();

        $this->assertSame('EXPIRED', $transaction->status);

        $variantId = $transaction->items()->first()->product_variant_id;

        $stockBefore = (int) Inventory::where(
            'product_variant_id',
            $variantId
        )->value('stock');

        $response = $this->postNotification(
            $this->payload($transaction->invoice_number)
        );

        $response->assertStatus(409);

        $transaction->refresh();

        $this->assertSame('EXPIRED', $transaction->status);
        $this->assertNull($transaction->paid_at);

        $stockAfter = (int) Inventory::where(
            'product_variant_id',
            $variantId
        )->value('stock');

        $this->assertSame($stockBefore, $stockAfter);
    }
}
