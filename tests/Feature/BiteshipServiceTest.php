<?php

namespace Tests\Feature;

use App\Services\BiteshipService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BiteshipServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'biteship.base_url' => 'https://api.biteship.test',
            'biteship.api_key' => 'test-api-key',
            'biteship.timeout' => 10,

            'biteship.origin.contact_name' => 'Eazywear',
            'biteship.origin.contact_phone' => '081234567890',
            'biteship.origin.address' => 'Jl. Test No. 1',
            'biteship.origin.postal_code' => 12345,
        ]);
    }

    private function orderPayload(): array
    {
        return [
            'reference_id' => 'INV-TEST-001',
            'destination_contact_name' => 'Test Customer',
            'destination_contact_phone' => '081234567891',
            'destination_address' => 'Jl. Customer No. 2',
            'destination_postal_code' => 54321,
            'courier_company' => 'jne',
            'courier_type' => 'reg',
            'items' => [
                [
                    'name' => 'Custom Jersey',
                    'description' => 'Test Jersey',
                    'value' => 150000,
                    'quantity' => 1,
                    'weight' => 500,
                ],
            ],
        ];
    }

    public function test_create_order_successfully_maps_biteship_response(): void
    {
        Http::fake([
            'https://api.biteship.test/v1/orders' => Http::response([
                'success' => true,
                'id' => 'order-test-001',
                'reference_id' => 'INV-TEST-001',
                'status' => 'confirmed',
                'courier' => [
                    'tracking_id' => 'track-test-001',
                    'waybill_id' => 'waybill-test-001',
                ],
            ], 200),
        ]);

        $result = app(BiteshipService::class)->createOrder(
            $this->orderPayload()
        );

        $this->assertTrue($result['success']);
        $this->assertSame('order-test-001', $result['order_id']);
        $this->assertSame('track-test-001', $result['tracking_id']);
        $this->assertSame('waybill-test-001', $result['waybill_id']);
        $this->assertSame('confirmed', $result['status']);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request->url() === 'https://api.biteship.test/v1/orders'
                && $request->header('Authorization')[0] === 'test-api-key'
                && $request['reference_id'] === 'INV-TEST-001'
                && $request['courier_company'] === 'jne'
                && $request['courier_type'] === 'reg';
        });
    }

    public function test_create_order_throws_runtime_exception_on_4xx(): void
    {
        Http::fake([
            'https://api.biteship.test/v1/orders' => Http::response([
                'success' => false,
                'code' => 40001000,
                'message' => 'Invalid request.',
            ], 400),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Biteship gagal: Invalid request.');

        app(BiteshipService::class)->createOrder(
            $this->orderPayload()
        );
    }

    public function test_create_order_throws_runtime_exception_on_5xx(): void
    {
        Http::fake([
            'https://api.biteship.test/v1/orders' => Http::response([
                'success' => false,
                'message' => 'Internal server error.',
            ], 500),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Biteship gagal: Internal server error.'
        );

        app(BiteshipService::class)->createOrder(
            $this->orderPayload()
        );
    }

    public function test_create_order_propagates_timeout_connection_exception(): void
    {
        Http::fake(function () {
            throw new ConnectionException(
                'cURL error 28: Operation timed out'
            );
        });

        $this->expectException(ConnectionException::class);

        app(BiteshipService::class)->createOrder(
            $this->orderPayload()
        );
    }

    public function test_create_order_propagates_connection_failure(): void
    {
        Http::fake(function () {
            throw new ConnectionException(
                'cURL error 7: Failed to connect'
            );
        });

        $this->expectException(ConnectionException::class);

        app(BiteshipService::class)->createOrder(
            $this->orderPayload()
        );
    }

    public function test_get_order_maps_existing_biteship_order(): void
    {
        Http::fake([
            'https://api.biteship.test/v1/orders/order-test-001' => Http::response([
                'success' => true,
                'id' => 'order-test-001',
                'reference_id' => 'INV-TEST-001',
                'status' => 'picking_up',
                'price' => 18000,
                'courier' => [
                    'company' => 'jne',
                    'type' => 'reg',
                    'tracking_id' => 'track-test-001',
                    'waybill_id' => 'waybill-test-001',
                ],
            ], 200),
        ]);

        $result = app(BiteshipService::class)->getOrder(
            'order-test-001'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('order-test-001', $result['order_id']);
        $this->assertSame('INV-TEST-001', $result['reference_id']);
        $this->assertSame('track-test-001', $result['tracking_id']);
        $this->assertSame('waybill-test-001', $result['waybill_id']);
        $this->assertSame('jne', $result['courier_code']);
        $this->assertSame('reg', $result['courier_type']);
        $this->assertSame('picking_up', $result['status']);
        $this->assertSame(18000, $result['price']);

        Http::assertSent(function ($request) {
            return $request->method() === 'GET'
                && $request->url()
                    === 'https://api.biteship.test/v1/orders/order-test-001';
        });
    }

    public function test_create_order_recovers_existing_order_when_biteship_reports_duplicate_reference(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'POST' && $request->url() === 'https://api.biteship.test/v1/orders') {
                return Http::response([
                    'success' => false,
                    'code' => 40002060,
                    'message' => 'Reference ID already exists.',
                    'order_id' => 'order-existing-001',
                    'reference_id' => 'INV-TEST-001',
                    'waybill_id' => 'waybill-existing-001',
                ], 400);
            }

            if (
                $request->method() === 'GET'
                && $request->url() === 'https://api.biteship.test/v1/orders/order-existing-001'
            ) {
                return Http::response([
                    'success' => true,
                    'id' => 'order-existing-001',
                    'reference_id' => 'INV-TEST-001',
                    'status' => 'confirmed',
                    'courier' => [
                        'tracking_id' => 'track-existing-001',
                        'waybill_id' => 'waybill-existing-001',
                        'company' => 'jne',
                        'type' => 'reg',
                    ],
                    'price' => 18000,
                ], 200);
            }

            return Http::response([], 404);
        });

        $result = app(BiteshipService::class)->createOrder(
            $this->orderPayload()
        );

        $this->assertTrue($result['success']);
        $this->assertSame('order-existing-001', $result['order_id']);
        $this->assertSame('track-existing-001', $result['tracking_id']);
        $this->assertSame('waybill-existing-001', $result['waybill_id']);
        $this->assertSame('confirmed', $result['status']);

        Http::assertSentCount(2);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request->url() === 'https://api.biteship.test/v1/orders';
        });

        Http::assertSent(function ($request) {
            return $request->method() === 'GET'
                && $request->url() === 'https://api.biteship.test/v1/orders/order-existing-001';
        });
    }

    public function test_create_order_rejects_duplicate_reference_when_existing_order_has_different_reference(): void
    {
        Http::fake(function ($request) {
            if (
                $request->method() === 'POST'
                && $request->url() === 'https://api.biteship.test/v1/orders'
            ) {
                return Http::response([
                    'success' => false,
                    'code' => 40002060,
                    'message' => 'Reference ID already exists.',
                    'order_id' => 'order-existing-002',
                ], 400);
            }

            if (
                $request->method() === 'GET'
                && $request->url() === 'https://api.biteship.test/v1/orders/order-existing-002'
            ) {
                return Http::response([
                    'success' => true,
                    'id' => 'order-existing-002',
                    'reference_id' => 'INV-DIFFERENT-999',
                    'status' => 'confirmed',
                    'courier' => [
                        'tracking_id' => 'track-existing-002',
                        'waybill_id' => 'waybill-existing-002',
                        'company' => 'jne',
                        'type' => 'reg',
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Biteship existing order memiliki reference ID yang berbeda.'
        );

        app(BiteshipService::class)->createOrder(
            $this->orderPayload()
        );
    }

    public function test_create_order_rejects_duplicate_reference_without_existing_order_id(): void
    {
        Http::fake(function ($request) {
            if (
                $request->method() === 'POST'
                && $request->url() === 'https://api.biteship.test/v1/orders'
            ) {
                return Http::response([
                    'success' => false,
                    'code' => 40002060,
                    'message' => 'Reference ID already exists.',
                ], 400);
            }

            return Http::response([], 404);
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Biteship duplicate reference tidak menyertakan order ID.'
        );

        app(BiteshipService::class)->createOrder(
            $this->orderPayload()
        );
    }
}