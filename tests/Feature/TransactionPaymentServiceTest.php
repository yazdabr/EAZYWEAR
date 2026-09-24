<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TransactionPaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransactionPaymentServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function createPaymentTransaction(
        int $quantity = 1,
        int $price = 100000
    ): Transaction {
        $inventory = Inventory::query()
            ->where('stock', '>=', $quantity)
            ->firstOrFail();

        $variant = ProductVariant::query()
            ->findOrFail($inventory->product_variant_id);

        $transaction = Transaction::factory()->create([
            'subtotal' => $price * $quantity,
            'total' => $price * $quantity,
            'status' => 'PENDING',
        ]);

        TransactionItem::query()->create([
            'transaction_id' => $transaction->id,
            'product_variant_id' => $variant->id,
            'custom_name' => null,
            'custom_number' => null,
            'qty' => $quantity,
            'price' => $price,
            'subtotal' => $price * $quantity,
        ]);

        return $transaction->load('items');
    }

    private function successfulDokuResponse(
        string $paymentRequestId = 'PAYMENT-TEST-001',
        string $amount = '100000.00'
    ): array {
        return [
            'responseCode' => '2002600',
            'responseMessage' => 'Successful',
            'virtualAccountData' => [
                'paymentFlagReason' => [
                    'english' => 'Success',
                ],
                'paymentRequestId' => $paymentRequestId,
                'paidAmount' => [
                    'value' => $amount,
                    'currency' => 'IDR',
                ],
            ],
        ];
    }

    public function test_successful_doku_payment_marks_transaction_paid_and_deducts_stock(): void
    {
        $transaction = $this->createPaymentTransaction();

        $inventory = Inventory::query()
            ->where(
                'product_variant_id',
                $transaction->items->first()->product_variant_id
            )
            ->firstOrFail();

        $stockBefore = (int) $inventory->stock;

        app(TransactionPaymentService::class)->processSuccessfulPayment(
            $transaction,
            $this->successfulDokuResponse(),
            'Automated payment test'
        );

        $inventory->refresh();
        $transaction->refresh();
        $transaction->load('items');

        $this->assertSame(
            $stockBefore - 1,
            (int) $inventory->stock
        );

        $this->assertSame('PAID', $transaction->status);
        $this->assertNotNull($transaction->paid_at);
        $this->assertSame(
            'PAYMENT-TEST-001',
            $transaction->doku_payment_id
        );
        $this->assertIsArray($transaction->doku_response);

        $this->assertNotNull(
            $transaction->items->first()->stock_deducted_at
        );

        $this->assertTrue(
            StockMovement::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'OUT')
                ->exists()
        );
    }

    public function test_same_successful_payment_is_idempotent(): void
    {
        $transaction = $this->createPaymentTransaction();

        $service = app(TransactionPaymentService::class);

        $response = $this->successfulDokuResponse();

        $service->processSuccessfulPayment(
            $transaction,
            $response,
            'Idempotency test - first'
        );

        $inventory = Inventory::query()
            ->where(
                'product_variant_id',
                $transaction->items->first()->product_variant_id
            )
            ->firstOrFail();

        $inventory->refresh();

        $stockAfterFirstPayment = (int) $inventory->stock;

        $movementCountAfterFirstPayment = StockMovement::query()
            ->where('transaction_id', $transaction->id)
            ->where('type', 'OUT')
            ->count();

        $transaction->refresh();

        $service->processSuccessfulPayment(
            $transaction,
            $response,
            'Idempotency test - second'
        );

        $inventory->refresh();

        $this->assertSame(
            $stockAfterFirstPayment,
            (int) $inventory->stock
        );

        $this->assertSame(
            $movementCountAfterFirstPayment,
            StockMovement::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'OUT')
                ->count()
        );

        $this->assertSame('PAID', $transaction->refresh()->status);
    }

    public function test_payment_with_wrong_amount_is_rejected(): void
    {
        $transaction = $this->createPaymentTransaction();

        $inventory = Inventory::query()
            ->where(
                'product_variant_id',
                $transaction->items->first()->product_variant_id
            )
            ->firstOrFail();

        $stockBefore = (int) $inventory->stock;

        $this->expectException(ValidationException::class);

        try {
            app(TransactionPaymentService::class)->processSuccessfulPayment(
                $transaction,
                $this->successfulDokuResponse(
                    amount: '90000.00'
                ),
                'Amount mismatch test'
            );
        } finally {
            $inventory->refresh();
            $transaction->refresh();

            $this->assertSame(
                $stockBefore,
                (int) $inventory->stock
            );

            $this->assertSame('PENDING', $transaction->status);
        }
    }

    public function test_unsuccessful_doku_payment_is_rejected(): void
    {
        $transaction = $this->createPaymentTransaction();

        $inventory = Inventory::query()
            ->where(
                'product_variant_id',
                $transaction->items->first()->product_variant_id
            )
            ->firstOrFail();

        $stockBefore = (int) $inventory->stock;

        $this->expectException(ValidationException::class);

        try {
            app(TransactionPaymentService::class)->processSuccessfulPayment(
                $transaction,
                [
                    'responseCode' => '4002600',
                    'responseMessage' => 'Failed',
                    'virtualAccountData' => [
                        'paymentFlagReason' => [
                            'english' => 'Failed',
                        ],
                        'paymentRequestId' => 'PAYMENT-FAILED-001',
                        'paidAmount' => [
                            'value' => '100000.00',
                            'currency' => 'IDR',
                        ],
                    ],
                ],
                'Failed payment test'
            );
        } finally {
            $inventory->refresh();
            $transaction->refresh();

            $this->assertSame(
                $stockBefore,
                (int) $inventory->stock
            );

            $this->assertSame('PENDING', $transaction->status);
        }
    }

    public function test_expired_transaction_cannot_be_marked_paid(): void
    {
        $transaction = $this->createPaymentTransaction();

        $transaction->update([
            'status' => 'EXPIRED',
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $inventory = Inventory::query()
            ->where(
                'product_variant_id',
                $transaction->items->first()->product_variant_id
            )
            ->firstOrFail();

        $stockBefore = (int) $inventory->stock;

        $this->expectException(ValidationException::class);

        try {
            app(TransactionPaymentService::class)->processSuccessfulPayment(
                $transaction,
                $this->successfulDokuResponse(),
                'Expired payment test'
            );
        } finally {
            $inventory->refresh();
            $transaction->refresh();

            $this->assertSame(
                $stockBefore,
                (int) $inventory->stock
            );

            $this->assertSame('EXPIRED', $transaction->status);
        }
    }

    public function test_payment_request_id_is_saved_from_doku_response(): void
    {
        $transaction = $this->createPaymentTransaction();

        app(TransactionPaymentService::class)->processSuccessfulPayment(
            $transaction,
            $this->successfulDokuResponse(
                paymentRequestId: 'DOKU-PAYMENT-123'
            ),
            'Payment ID test'
        );

        $transaction->refresh();

        $this->assertSame(
            'DOKU-PAYMENT-123',
            $transaction->doku_payment_id
        );

        $this->assertIsArray($transaction->doku_response);

        $this->assertSame(
            'DOKU-PAYMENT-123',
            data_get(
                $transaction->doku_response,
                'virtualAccountData.paymentRequestId'
            )
        );
    }
}
