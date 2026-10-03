<?php

namespace Tests\Feature;

use App\Mail\OrderShippedMail;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Http\Controllers\Admin\TransactionController;
use App\Services\BiteshipService;
use Illuminate\Http\Request;

class BiteshipCreateOrderRecoveryTest extends TestCase
{
    private string $triggerName;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'biteship.base_url' => 'https://api.biteship.com',
            'biteship.api_key' => 'test-api-key',
        ]);

        $this->triggerName = 'trg_biteship_e2_' . Str::lower(
            Str::random(12)
        );
    }

    protected function tearDown(): void
    {
        try {
            DB::statement(
                'DROP TRIGGER IF EXISTS `'.$this->triggerName.'`'
            );
        } catch (\Throwable) {
            // Best-effort cleanup.
        }

        parent::tearDown();
    }

    public function test_successful_external_create_recovers_after_database_persist_failure(): void
    {
        Mail::fake();

        $transaction = Transaction::create([
            'invoice_number' => 'E2-RECOVERY-'.Str::upper(
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

            'shipping_name' => 'E2 Recovery Test',
            'shipping_email' => 'e2-recovery@example.test',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'E2 Recovery Address',
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

        $externalCreateCount = 0;
        $externalGetCount = 0;

        Http::fake(function ($request) use (
            &$externalCreateCount,
            &$externalGetCount,
            $transaction
        ) {
            if (
                $request->method() === 'POST'
                && str_contains($request->url(), '/v1/orders')
            ) {
                $externalCreateCount++;

                if ($externalCreateCount === 1) {
                    return Http::response([
                        'success' => true,
                        'id' => 'BITE-E2-001',
                        'status' => 'confirmed',
                        'reference_id' => $transaction->invoice_number,
                        'courier' => [
                            'company' => 'jnt',
                            'type' => 'ez',
                            'tracking_id' => 'TRACK-E2-001',
                            'waybill_id' => 'WAYBILL-E2-001',
                        ],
                    ], 200);
                }

                return Http::response([
                    'success' => false,
                    'code' => 40002060,
                    'message' => 'Reference ID already exists.',
                    'order_id' => 'BITE-E2-001',
                ], 400);
            }

            if (
                $request->method() === 'GET'
                && str_contains($request->url(), '/v1/orders/BITE-E2-001')
            ) {
                $externalGetCount++;

                return Http::response([
                    'success' => true,
                    'id' => 'BITE-E2-001',
                    'reference_id' => $transaction->invoice_number,
                    'status' => 'confirmed',
                    'courier' => [
                        'company' => 'jnt',
                        'type' => 'ez',
                        'tracking_id' => 'TRACK-E2-001',
                        'waybill_id' => 'WAYBILL-E2-001',
                    ],
                ], 200);
            }

            return Http::response([
                'success' => false,
            ], 404);
        });

        /*
         * Force the database persistence step to fail when the first
         * Biteship order tries to be persisted.
         *
         * This is test-only infrastructure. No production code changes.
         */
        DB::statement("
            CREATE TRIGGER `{$this->triggerName}`
            BEFORE UPDATE ON transactions
            FOR EACH ROW
            BEGIN
                IF NEW.biteship_order_id = 'BITE-E2-001' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'E2 forced database persist failure';
                END IF;
            END
        ");

        $firstResponse = app(TransactionController::class)->ship(
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
            500,
            $firstResponse->getStatusCode(),
            $firstResponse->getContent()
        );

        /*
         * External create succeeded even though database persistence failed.
         */
        $this->assertSame(
            1,
            $externalCreateCount
        );

        $this->assertSame(
            0,
            $externalGetCount
        );

        $transaction->refresh();

        /*
         * Because the DB transaction rolled back, no Biteship data
         * should have been persisted locally.
         */
        $this->assertNull(
            $transaction->biteship_order_id
        );

        $this->assertNull(
            $transaction->biteship_tracking_id
        );

        $this->assertNull(
            $transaction->biteship_waybill_id
        );

        $this->assertNull(
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_PROCESSING,
            $transaction->status
        );

        $this->assertSame(
            0,
            $transaction
                ->orderStatusHistories()
                ->where('status', Transaction::ORDER_SHIPPED)
                ->count()
        );

        /*
         * Remove the artificial DB failure before retrying.
         */
        DB::statement(
            'DROP TRIGGER IF EXISTS `'.$this->triggerName.'`'
        );

        /*
         * Retry the exact same shipment operation.
         *
         * The external provider must report duplicate reference,
         * then the service must recover the existing order.
         */
        $secondResponse = app(TransactionController::class)->ship(
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
            $secondResponse->getStatusCode(),
            $secondResponse->getContent()
        );

        $transaction->refresh();

        /*
         * Critical idempotency assertion:
         * only ONE external create occurred.
         *
         * The second attempt received 40002060 and recovered the
         * existing Biteship order through GET.
         */
        $this->assertSame(
            2,
            $externalCreateCount
        );

        $this->assertSame(
            1,
            $externalGetCount
        );

        /*
         * Local transaction must now be fully recovered.
         */
        $this->assertSame(
            'BITE-E2-001',
            $transaction->biteship_order_id
        );

        $this->assertSame(
            'TRACK-E2-001',
            $transaction->biteship_tracking_id
        );

        $this->assertSame(
            'WAYBILL-E2-001',
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
         * Exactly one shipping history.
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
         * Final invariant:
         * no duplicate local Biteship identity.
         */
        $this->assertSame(
            1,
            Transaction::query()
                ->where('biteship_order_id', 'BITE-E2-001')
                ->count()
        );

        $transaction->delete();
    }
}