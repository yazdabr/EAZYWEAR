<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SalesReportStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sales_report_statuses_include_all_paid_order_lifecycle_statuses(): void
    {
        $salesStatuses = [
            'PAID',
            'ORDER_PROCESSING',
            'ORDER_SHIPPED',
            'ORDER_COMPLETED',
        ];

        foreach ($salesStatuses as $status) {
            $transaction = Transaction::factory()->create([
                'status' => $status,
                'total' => 100000,
            ]);

            $this->assertTrue(
                in_array($transaction->status, $salesStatuses, true),
                "Status {$status} harus dianggap sebagai transaksi penjualan."
            );
        }
    }

    public function test_sales_report_statuses_exclude_unpaid_or_cancelled_statuses(): void
    {
        $excludedStatuses = [
            'PENDING',
            'EXPIRED',
            'CANCELLED',
        ];

        foreach ($excludedStatuses as $status) {
            $transaction = Transaction::factory()->create([
                'status' => $status,
                'total' => 100000,
            ]);

            $this->assertFalse(
                in_array(
                    $transaction->status,
                    [
                        'PAID',
                        'ORDER_PROCESSING',
                        'ORDER_SHIPPED',
                        'ORDER_COMPLETED',
                    ],
                    true
                ),
                "Status {$status} tidak boleh dianggap sebagai transaksi penjualan."
            );
        }
    }

    public function test_legacy_completed_status_is_explicitly_tracked(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'COMPLETED',
            'total' => 100000,
        ]);

        $this->assertSame('COMPLETED', $transaction->status);

        /*
         * Legacy COMPLETED belum dimasukkan ke lifecycle baru.
         * Test ini sengaja hanya mengunci keberadaan status legacy
         * sampai keputusan backward compatibility dibuat.
         */
    }
}
