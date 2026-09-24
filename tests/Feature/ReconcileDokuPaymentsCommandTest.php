<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\DokuService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReconcileDokuPaymentsCommandTest extends TestCase
{
    use DatabaseTransactions;

    private function createPendingTransaction(): Transaction
    {
        $inventory = Inventory::query()
            ->where('stock', '>', 0)
            ->firstOrFail();

        $variant = ProductVariant::query()
            ->findOrFail($inventory->product_variant_id);

        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'subtotal' => 100000,
            'total' => 100000,
            'va_number' => '190089000000123456',
            'va_expired_at' => now('UTC')->addMinutes(5),
            'doku_payment_id' => null,
        ]);

        TransactionItem::query()->create([
            'transaction_id' => $transaction->id,
            'product_variant_id' => $variant->id,
            'custom_name' => null,
            'custom_number' => null,
            'qty' => 1,
            'price' => 100000,
            'subtotal' => 100000,
        ]);

        return $transaction->load('items');
    }

    private function successfulResponse(): array
    {
        return [
            'responseCode' => '2002600',
            'responseMessage' => 'Successful',
            'virtualAccountData' => [
                'paymentFlagReason' => [
                    'english' => 'Success',
                ],
                'paymentRequestId' => 'RECON-PAYMENT-001',
                'paidAmount' => [
                    'value' => '100000.00',
                    'currency' => 'IDR',
                ],
            ],
        ];
    }

    public function test_reconciliation_marks_successful_payment_as_paid(): void
    {
        $transaction = $this->createPendingTransaction();

        $inventory = Inventory::query()
            ->where(
                'product_variant_id',
                $transaction->items->first()->product_variant_id
            )
            ->firstOrFail();

        $stockBefore = (int) $inventory->stock;

        $dokuService = $this->mock(DokuService::class);

        $dokuService
            ->shouldReceive('checkVirtualAccountStatus')
            ->once()
            ->andReturn([
                'http_status' => 200,
                'response' => $this->successfulResponse(),
                '_external_id' => 'TEST-RECON-001',
            ]);

        $this->artisan('transactions:reconcile-doku')
            ->assertExitCode(0);

        $transaction->refresh();
        $inventory->refresh();

        $this->assertSame('PAID', $transaction->status);
        $this->assertSame(
            'RECON-PAYMENT-001',
            $transaction->doku_payment_id
        );
        $this->assertNotNull($transaction->paid_at);

        $this->assertSame(
            $stockBefore - 1,
            (int) $inventory->stock
        );

        $this->assertTrue(
            StockMovement::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'OUT')
                ->exists()
        );
    }

    public function test_reconciliation_does_not_process_expired_transaction(): void
    {
        $transaction = $this->createPendingTransaction();

        $transaction->update([
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $dokuService = $this->mock(DokuService::class);

        $dokuService
            ->shouldReceive('checkVirtualAccountStatus')
            ->never();

        $this->artisan('transactions:reconcile-doku')
            ->assertExitCode(0);

        $transaction->refresh();

        $this->assertSame('PENDING', $transaction->status);
    }

    public function test_reconciliation_skips_unsuccessful_payment(): void
    {
        $transaction = $this->createPendingTransaction();

        $dokuService = $this->mock(DokuService::class);

        $dokuService
            ->shouldReceive('checkVirtualAccountStatus')
            ->once()
            ->andReturn([
                'http_status' => 200,
                'response' => [
                    'responseCode' => '2002600',
                    'responseMessage' => 'Successful',
                    'virtualAccountData' => [
                        'paymentFlagReason' => [
                            'english' => 'Pending',
                        ],
                        'paidAmount' => [
                            'value' => '100000.00',
                            'currency' => 'IDR',
                        ],
                    ],
                ],
                '_external_id' => 'TEST-RECON-002',
            ]);

        $this->artisan('transactions:reconcile-doku')
            ->assertExitCode(0);

        $transaction->refresh();

        $this->assertSame('PENDING', $transaction->status);
    }

    public function test_reconciliation_rejects_wrong_amount(): void
    {
        $transaction = $this->createPendingTransaction();

        $dokuService = $this->mock(DokuService::class);

        $dokuService
            ->shouldReceive('checkVirtualAccountStatus')
            ->once()
            ->andReturn([
                'http_status' => 200,
                'response' => [
                    'responseCode' => '2002600',
                    'responseMessage' => 'Successful',
                    'virtualAccountData' => [
                        'paymentFlagReason' => [
                            'english' => 'Success',
                        ],
                        'paymentRequestId' => 'RECON-WRONG-AMOUNT',
                        'paidAmount' => [
                            'value' => '90000.00',
                            'currency' => 'IDR',
                        ],
                    ],
                ],
                '_external_id' => 'TEST-RECON-003',
            ]);

        $this->artisan('transactions:reconcile-doku')
            ->assertExitCode(0);

        $transaction->refresh();

        $this->assertSame('PENDING', $transaction->status);
        $this->assertNull($transaction->doku_payment_id);
    }
}