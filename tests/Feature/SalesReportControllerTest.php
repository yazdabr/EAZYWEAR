<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SalesReportControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function createSalesReportUser(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
        ]);
    }

    private function createLifecycleTransactions(): void
    {
        foreach ([
            'PAID',
            'ORDER_PROCESSING',
            'ORDER_SHIPPED',
            'ORDER_COMPLETED',
        ] as $status) {
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
    }

    public function test_sales_report_index_includes_all_paid_order_lifecycle_statuses(): void
    {
        $user = $this->createSalesReportUser();

        $this->createLifecycleTransactions();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.sales-reports'));

        $response->assertOk();

        $response->assertViewHas('transactions', function ($transactions) {
            $statuses = $transactions
                ->pluck('status')
                ->unique()
                ->values()
                ->all();

            sort($statuses);

            $expected = [
                'PAID',
                'ORDER_COMPLETED',
                'ORDER_PROCESSING',
                'ORDER_SHIPPED',
            ];

            sort($expected);

            return $statuses === $expected;
        });
    }

    public function test_sales_report_print_includes_all_paid_order_lifecycle_statuses(): void
    {
        $user = $this->createSalesReportUser();

        $this->createLifecycleTransactions();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.sales-reports.print'));

        $response->assertOk();

        $response->assertViewHas('transactions', function ($transactions) {
            $statuses = $transactions
                ->pluck('status')
                ->unique()
                ->values()
                ->all();

            sort($statuses);

            $expected = [
                'PAID',
                'ORDER_COMPLETED',
                'ORDER_PROCESSING',
                'ORDER_SHIPPED',
            ];

            sort($expected);

            return $statuses === $expected;
        });
    }

    public function test_sales_report_export_includes_all_paid_order_lifecycle_statuses(): void
    {
        $user = $this->createSalesReportUser();

        $this->createLifecycleTransactions();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.sales-reports.export'));

        $response->assertOk();

        $this->assertNotNull(
            $response->headers->get('Content-Disposition')
        );
    }
}
