<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\TransactionController;
use App\Mail\OrderShippedMail;
use App\Models\Transaction;
use App\Services\BiteshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class BiteshipCreateOrderDuplicateRecoveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'biteship.base_url' => 'https://api.biteship.com',
            'biteship.api_key' => 'test-api-key',
        ]);
    }

    public function test_duplicate_reference_recovers_existing_biteship_order_during_shipment_creation(): void
    {
        Mail::fake();

        $transaction = Transaction::create([
            'invoice_number' => 'E3-DUPLICATE-'.Str::upper(
                Str::random(12)
            ),
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 15000,
            'total' => 115000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',

            'shipping_name' => 'E3 Duplicate Recovery Test',
            'shipping_email' => 'e3-duplicate@example.test',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'E3 Duplicate Recovery Address',
            'shipping_district' => 'Test District',
            'shipping_city' => 'Test City',
            'shipping_province' => 'Test Province',
            'shipping_postal_code' => '12345',
            'shipping_method' => 'Kurir',

            'shipping_latitude' => null,
            'shipping_longitude' => null,

            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'biteship_order_id' => null,
            'biteship_tracking_id' => null,
            'biteship_waybill_id' => null,
            'biteship_status' => null,

            'courier' => null,
            'tracking_number' => null,
        ]);

        $createCount = 0;
        $getCount = 0;

        Http::fake(function ($request) use (
            &$createCount,
            &$getCount,
            $transaction
        ) {
            if (
                $request->method() === 'POST'
                && str_contains($request->url(), '/v1/orders')
            ) {
                $createCount++;

                return Http::response([
                    'success' => false,
                    'code' => 40002060,
                    'message' => 'Reference ID already exists.',
                    'order_id' => 'BITE-E3-001',
                ], 400);
            }

            if (
                $request->method() === 'GET'
                && str_contains(
                    $request->url(),
                    '/v1/orders/BITE-E3-001'
                )
            ) {
                $getCount++;

                return Http::response([
                    'success' => true,
                    'id' => 'BITE-E3-001',
                    'reference_id' => $transaction->invoice_number,
                    'status' => 'confirmed',
                    'courier' => [
                        'company' => 'jnt',
                        'type' => 'ez',
                        'tracking_id' => 'TRACK-E3-001',
                        'waybill_id' => 'WAYBILL-E3-001',
                    ],
                ], 200);
            }

            return Http::response([
                'success' => false,
                'message' => 'Unexpected Biteship request.',
            ], 500);
        });

        $response = app(TransactionController::class)->ship(
            Request::create(
                route('admin.transactions.ship', $transaction),
                'PATCH',
                [
                    'courier' => null,
                    'tracking_number' => null,
                ]
            ),
            $transaction,
            app(BiteshipService::class)
        );

        $this->assertSame(
            200,
            $response->getStatusCode(),
            $response->getContent()
        );

        $transaction->refresh();

        /*
         * The provider reported an existing reference.
         */
        $this->assertSame(
            1,
            $createCount
        );

        /*
         * The service recovered the existing order.
         */
        $this->assertSame(
            1,
            $getCount
        );

        /*
         * Existing Biteship identity must be persisted locally.
         */
        $this->assertSame(
            'BITE-E3-001',
            $transaction->biteship_order_id
        );

        $this->assertSame(
            'TRACK-E3-001',
            $transaction->biteship_tracking_id
        );

        $this->assertSame(
            'WAYBILL-E3-001',
            $transaction->biteship_waybill_id
        );

        $this->assertSame(
            'confirmed',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );

        /*
         * Exactly one ORDER_SHIPPED history.
         */
        $this->assertSame(
            1,
            $transaction
                ->orderStatusHistories()
                ->where('status', Transaction::ORDER_SHIPPED)
                ->count()
        );

        /*
         * Exactly one shipping notification.
         */
        $this->assertSame(
            1,
            $transaction
                ->notifications()
                ->where('type', 'ORDER_SHIPPED_EMAIL')
                ->count()
        );

        $notification = $transaction
            ->notifications()
            ->where('type', 'ORDER_SHIPPED_EMAIL')
            ->first();

        $this->assertNotNull($notification);
        $this->assertNotNull($notification->sent_at);

        Mail::assertSent(
            OrderShippedMail::class,
            1
        );

        /*
         * Final local uniqueness invariant.
         */
        $this->assertSame(
            1,
            Transaction::query()
                ->where('biteship_order_id', 'BITE-E3-001')
                ->count()
        );

        $transaction->delete();
    }
}