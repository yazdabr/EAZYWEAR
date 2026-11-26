<?php

namespace Tests\Feature;

use App\Services\DokuMinimarketService;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class DokuMinimarketServiceTest extends TestCase
{
    private string $clientId = 'TEST-MINIMARKET-CLIENT-ID';

    private string $secretKey = 'TEST-MINIMARKET-SECRET-KEY';

    private string $endpoint = '/alfa-online-to-offline/v2/payment-code';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('doku-minimarket.environment', 'sandbox');
        config()->set('doku-minimarket.base_url', 'https://api-sandbox.doku.com');
        config()->set('doku-minimarket.client_id', $this->clientId);
        config()->set('doku-minimarket.secret_key', $this->secretKey);
        config()->set('doku-minimarket.alfamart.endpoint', $this->endpoint);
        config()->set('doku-minimarket.alfamart.expired_time', 1440);
        config()->set('doku-minimarket.alfamart.reusable_status', false);
        config()->set(
            'doku-minimarket.alfamart.notification_url',
            'https://eazywear.id/api/doku/alfamart/notification'
        );
    }

    private function fakeDokuResponse(bool $validSignature = true): void
    {
        Http::fake([
            '*' => function (ClientRequest $request) use ($validSignature) {
                $responseBody = json_encode([
                    'order' => [
                        'invoice_number' => 'INV-TEST-ALFA-001',
                    ],
                    'online_to_offline_info' => [
                        'payment_code' => '1900600000000046',
                        'how_to_pay_page' => 'https://sandbox.doku.com/how-to-pay/example',
                        'created_date_utc' => '2026-11-25T05:00:00Z',
                        'expired_date_utc' => '2026-11-26T05:00:00Z',
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                $requestId = $request->header('Request-Id')[0] ?? '';
                $responseTimestamp = '2026-11-25T05:00:00Z';

                $digest = base64_encode(
                    hash('sha256', $responseBody, true)
                );

                $component = implode("\n", [
                    'Client-Id:' . $this->clientId,
                    'Request-Id:' . $requestId,
                    'Response-Timestamp:' . $responseTimestamp,
                    'Request-Target:' . $this->endpoint,
                    'Digest:' . $digest,
                ]);

                $signature = 'HMACSHA256=' . base64_encode(
                    hash_hmac('sha256', $component, $this->secretKey, true)
                );

                if (! $validSignature) {
                    $signature = 'HMACSHA256=' . base64_encode(
                        random_bytes(32)
                    );
                }

                return Http::response($responseBody, 200, [
                    'Client-Id' => $this->clientId,
                    'Request-Id' => $requestId,
                    'Response-Timestamp' => $responseTimestamp,
                    'Signature' => $signature,
                    'Content-Type' => 'application/json',
                ]);
            },
        ]);
    }

    private function fakeDokuResponseWithPaymentCode(mixed $paymentCode): void
    {
        Http::fake([
            '*' => function (ClientRequest $request) use ($paymentCode) {
                $responseBody = json_encode([
                    'order' => [
                        'invoice_number' => 'INV-TEST-ALFA-001',
                    ],
                    'online_to_offline_info' => [
                        'payment_code' => $paymentCode,
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                $requestId = $request->header('Request-Id')[0] ?? '';
                $responseTimestamp = '2026-11-25T05:00:00Z';

                $digest = base64_encode(
                    hash('sha256', $responseBody, true)
                );

                $component = implode("\n", [
                    'Client-Id:' . $this->clientId,
                    'Request-Id:' . $requestId,
                    'Response-Timestamp:' . $responseTimestamp,
                    'Request-Target:' . $this->endpoint,
                    'Digest:' . $digest,
                ]);

                $signature = 'HMACSHA256=' . base64_encode(
                    hash_hmac('sha256', $component, $this->secretKey, true)
                );

                return Http::response($responseBody, 200, [
                    'Client-Id' => $this->clientId,
                    'Request-Id' => $requestId,
                    'Response-Timestamp' => $responseTimestamp,
                    'Signature' => $signature,
                    'Content-Type' => 'application/json',
                ]);
            },
        ]);
    }

    public function test_alfamart_payment_code_uses_correct_api_contract(): void
    {
        $this->fakeDokuResponse();

        $service = new DokuMinimarketService();

        $response = $service->createAlfamartPaymentCode(
            'INV-TEST-ALFA-001',
            175000,
            'Test Customer',
            'REQ-TEST-ALFA-001',
            'customer@example.com',
            'Eazywear Order'
        );

        $this->assertSame(
            'INV-TEST-ALFA-001',
            data_get($response, 'order.invoice_number')
        );

        $this->assertSame(
            '1900600000000046',
            data_get($response, 'online_to_offline_info.payment_code')
        );

        $this->assertSame(
            'REQ-TEST-ALFA-001',
            $response['_request_id']
        );

        Http::assertSent(function (ClientRequest $request) {
            $this->assertSame('POST', $request->method());

            $this->assertSame(
                'https://api-sandbox.doku.com' . $this->endpoint,
                $request->url()
            );

            $this->assertSame(
                $this->clientId,
                $request->header('Client-Id')[0] ?? null
            );

            $this->assertSame(
                'REQ-TEST-ALFA-001',
                $request->header('Request-Id')[0] ?? null
            );

            $this->assertNotEmpty(
                $request->header('Request-Timestamp')[0] ?? null
            );

            $this->assertNotEmpty(
                $request->header('Signature')[0] ?? null
            );

            $payload = json_decode($request->body(), true);

            $this->assertSame(
                'INV-TEST-ALFA-001',
                data_get($payload, 'order.invoice_number')
            );

            $this->assertSame(
                175000,
                data_get($payload, 'order.amount')
            );

            $this->assertSame(
                1440,
                data_get($payload, 'online_to_offline_info.expired_time')
            );

            $this->assertFalse(
                data_get($payload, 'online_to_offline_info.reusable_status')
            );

            $this->assertSame(
                'Test Customer',
                data_get($payload, 'customer.name')
            );

            $this->assertSame(
                'customer@example.com',
                data_get($payload, 'customer.email')
            );

            $this->assertSame(
                'Eazywear Order',
                data_get($payload, 'online_to_offline_info.info')
            );

            $this->assertSame(
                'https://eazywear.id/api/doku/alfamart/notification',
                data_get(
                    $payload,
                    'additional_info.override_notification_url'
                )
            );

            // Verifikasi signature request secara independen.
            $digest = base64_encode(
                hash('sha256', $request->body(), true)
            );

            $component = implode("\n", [
                'Client-Id:' . $this->clientId,
                'Request-Id:' . $request->header('Request-Id')[0],
                'Request-Timestamp:' . $request->header('Request-Timestamp')[0],
                'Request-Target:' . $this->endpoint,
                'Digest:' . $digest,
            ]);

            $expectedSignature = 'HMACSHA256=' . base64_encode(
                hash_hmac('sha256', $component, $this->secretKey, true)
            );

            $this->assertSame(
                $expectedSignature,
                $request->header('Signature')[0]
            );

            return true;
        });
    }

    public function test_alfamart_rejects_invalid_response_signature(): void
    {
        $this->fakeDokuResponse(false);

        $service = new DokuMinimarketService();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Signature response DOKU tidak valid.'
        );

        $service->createAlfamartPaymentCode(
            'INV-TEST-ALFA-001',
            175000,
            'Test Customer',
            'REQ-TEST-ALFA-001'
        );
    }

    public function test_alfamart_rejects_fractional_amount(): void
    {
        Http::fake();

        $service = new DokuMinimarketService();

        try {
            $service->createAlfamartPaymentCode(
                'INV-TEST-ALFA-001',
                175000.50,
                'Test Customer',
                'REQ-TEST-ALFA-001'
            );

            $this->fail('Nominal pecahan seharusnya ditolak.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'bilangan bulat IDR',
                $exception->getMessage()
            );
        }

        Http::assertNothingSent();
    }

    public function test_alfamart_rejects_invoice_longer_than_64_characters(): void
    {
        Http::fake();

        $service = new DokuMinimarketService();

        try {
            $service->createAlfamartPaymentCode(
                str_repeat('A', 65),
                175000,
            'Test Customer',
            'REQ-TEST-ALFA-001'
            );

            $this->fail('Invoice lebih dari 64 karakter seharusnya ditolak.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'maksimal 64 karakter',
                $exception->getMessage()
            );
        }

        Http::assertNothingSent();
    }

    public function test_alfamart_rejects_response_without_payment_code(): void
    {
        Http::fake([
            '*' => function (ClientRequest $request) {
                $responseBody = json_encode([
                    'order' => [
                        'invoice_number' => 'INV-TEST-ALFA-001',
                    ],
                    'online_to_offline_info' => [],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                $requestId = $request->header('Request-Id')[0] ?? '';
                $responseTimestamp = '2026-11-25T05:00:00Z';

                $digest = base64_encode(
                    hash('sha256', $responseBody, true)
                );

                $component = implode("\n", [
                    'Client-Id:' . $this->clientId,
                    'Request-Id:' . $requestId,
                    'Response-Timestamp:' . $responseTimestamp,
                    'Request-Target:' . $this->endpoint,
                    'Digest:' . $digest,
                ]);

                $signature = 'HMACSHA256=' . base64_encode(
                    hash_hmac('sha256', $component, $this->secretKey, true)
                );

                return Http::response($responseBody, 200, [
                    'Client-Id' => $this->clientId,
                    'Request-Id' => $requestId,
                    'Response-Timestamp' => $responseTimestamp,
                    'Signature' => $signature,
                    'Content-Type' => 'application/json',
                ]);
            },
        ]);

        $service = new DokuMinimarketService();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Payment code Alfamart tidak ditemukan pada response DOKU.'
        );

        $service->createAlfamartPaymentCode(
            'INV-TEST-ALFA-001',
            175000,
            'Test Customer',
            'REQ-TEST-ALFA-001'
        );
    }
    public function test_alfamart_rejects_numeric_payment_code_as_integer(): void
    {
        $this->fakeDokuResponseWithPaymentCode(1900600000000046);

        $service = new DokuMinimarketService();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Payment code Alfamart dari response DOKU tidak valid.'
        );

        $service->createAlfamartPaymentCode(
            'INV-TEST-ALFA-001',
            175000,
            'Test Customer',
            'REQ-TEST-ALFA-001'
        );
    }

    public function test_alfamart_rejects_alphanumeric_payment_code(): void
    {
        $this->fakeDokuResponseWithPaymentCode('19006ABC0000046');

        $service = new DokuMinimarketService();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Payment code Alfamart dari response DOKU tidak valid.'
        );

        $service->createAlfamartPaymentCode(
            'INV-TEST-ALFA-001',
            175000,
            'Test Customer',
            'REQ-TEST-ALFA-001'
        );
    }
}
