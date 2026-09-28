<?php

namespace Tests\Feature;

use App\Exports\SalesReportExport;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class SalesReportExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sales_report_export_collection_includes_all_paid_order_lifecycle_statuses(): void
    {
        $includedStatuses = [
            'PAID',
            'ORDER_PROCESSING',
            'ORDER_SHIPPED',
            'ORDER_COMPLETED',
        ];

        foreach ($includedStatuses as $status) {
            Transaction::factory()->create([
                'status' => $status,
                'total' => 100000,
            ]);
        }

        foreach ([
            'PENDING',
            'EXPIRED',
            'CANCELLED',
        ] as $status) {
            Transaction::factory()->create([
                'status' => $status,
                'total' => 100000,
            ]);
        }

        $export = new SalesReportExport(
            Request::create('/admin/sales-reports/export', 'GET')
        );

        $statuses = $export
            ->collection()
            ->pluck('status')
            ->unique()
            ->values()
            ->all();

        sort($statuses);

        $expected = $includedStatuses;
        sort($expected);

        $this->assertSame($expected, $statuses);
    }
}
