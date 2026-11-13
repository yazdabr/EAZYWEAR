<?php

namespace Tests\Feature;

use App\Exports\CashierSalesReportExport;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class CashierSalesReportControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function loginAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));
    }

    private function createTransaction(
        string $invoice,
        string $source,
        string $status = Transaction::ORDER_COMPLETED,
        float $total = 100000,
        ?Carbon $date = null,
        int $qty = 0
    ): Transaction {
        $transaction = Transaction::create([
            'invoice_number' => $invoice,
            'transaction_date' => $date ?? now(),
            'payment_method' => 'CASH',
            'subtotal' => $total,
            'discount' => 0,
            'shipping' => 0,
            'total' => $total,
            'status' => $status,
            'source' => $source,
        ]);

        if ($qty > 0) {
            $variant = ProductVariant::firstOrFail();

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_variant_id' => $variant->id,
                'qty' => $qty,
                'price' => $total / $qty,
                'subtotal' => $total,
            ]);
        }

        return $transaction;
    }

    public function test_index_only_displays_pos_transactions(): void
    {
        $this->loginAsSuperAdmin();

        $date = Carbon::parse('2031-03-15 10:00:00');

        $this->createTransaction(
            'POS-REPORT-001',
            Transaction::SOURCE_POS,
            Transaction::ORDER_COMPLETED,
            100000,
            $date,
            2
        );
        $this->createTransaction(
            'POS-REPORT-PENDING',
            Transaction::SOURCE_POS,
            'PENDING',
            50000,
            $date,
            1
        );
        $this->createTransaction(
            'STADIUM-REPORT-001',
            Transaction::SOURCE_STADIUM,
            Transaction::ORDER_COMPLETED,
            200000,
            $date,
            3
        );
        $this->createTransaction(
            'BOOTH-REPORT-001',
            Transaction::SOURCE_BOOTH,
            'PAID',
            150000,
            $date,
            2
        );
        $this->createTransaction(
            'WEB-REPORT-001',
            Transaction::SOURCE_WEBSITE,
            Transaction::ORDER_COMPLETED,
            300000,
            $date,
            4
        );
        $this->createTransaction(
            'POS-REPORT-CANCELLED',
            Transaction::SOURCE_POS,
            'CANCELLED',
            75000,
            $date,
            1
        );

        $response = $this->get(route('admin.cashier-sales-reports', [
            'start_date' => '2031-03-15',
            'end_date' => '2031-03-15',
        ]));

        $response->assertOk()
            ->assertSee('Laporan Penjualan Kasir')
            ->assertSee('POS-REPORT-001')
            ->assertDontSee('POS-REPORT-PENDING')
            ->assertDontSee('STADIUM-REPORT-001')
            ->assertDontSee('BOOTH-REPORT-001')
            ->assertDontSee('WEB-REPORT-001')
            ->assertDontSee('POS-REPORT-CANCELLED');

        $response->assertViewHas('totalTransactions', 1);
        $response->assertViewHas('totalProductsSold', 2);
        $response->assertViewHas('transactionGrowth', 100);
        $response->assertViewHas('productGrowth', 100);
    }

    public function test_month_and_year_filter_only_counts_pos_transactions(): void
    {
        $this->loginAsSuperAdmin();

        $this->createTransaction(
            'POS-JUNE-2031',
            Transaction::SOURCE_POS,
            Transaction::ORDER_COMPLETED,
            100000,
            Carbon::parse('2031-06-10 10:00:00'),
            1
        );
        $this->createTransaction(
            'POS-JULY-2031',
            Transaction::SOURCE_POS,
            Transaction::ORDER_COMPLETED,
            100000,
            Carbon::parse('2031-07-10 10:00:00'),
            1
        );

        $response = $this->get(route('admin.cashier-sales-reports', [
            'month' => 6,
            'year' => 2031,
        ]));

        $response->assertOk()
            ->assertSee('POS-JUNE-2031')
            ->assertDontSee('POS-JULY-2031');

        $response->assertViewHas('totalTransactions', 1);
        $response->assertViewHas('totalProductsSold', 1);
    }

    public function test_print_uses_only_pos_transactions_and_cashier_identity(): void
    {
        $this->loginAsSuperAdmin();

        $date = Carbon::parse('2031-04-20 10:00:00');

        $this->createTransaction(
            'POS-PRINT-REPORT-001',
            Transaction::SOURCE_POS,
            Transaction::ORDER_COMPLETED,
            100000,
            $date,
            1
        );
        $this->createTransaction(
            'STADIUM-PRINT-REPORT-001',
            Transaction::SOURCE_STADIUM,
            Transaction::ORDER_COMPLETED,
            200000,
            $date,
            1
        );

        $this->get(route('admin.cashier-sales-reports.print', [
            'start_date' => '2031-04-20',
            'end_date' => '2031-04-20',
        ]))
            ->assertOk()
            ->assertSee('Laporan Penjualan Kasir')
            ->assertSee('POS-PRINT-REPORT-001')
            ->assertDontSee('STADIUM-PRINT-REPORT-001');
    }

    public function test_export_uses_only_pos_transactions_and_follows_filters(): void
    {
        $date = Carbon::parse('2031-05-12 10:00:00');

        $this->createTransaction(
            'POS-EXPORT-REPORT-001',
            Transaction::SOURCE_POS,
            Transaction::ORDER_COMPLETED,
            100000,
            $date,
            1
        );
        $this->createTransaction(
            'POS-EXPORT-REPORT-OUTSIDE',
            Transaction::SOURCE_POS,
            Transaction::ORDER_COMPLETED,
            100000,
            Carbon::parse('2031-05-20 10:00:00'),
            1
        );
        $this->createTransaction(
            'STADIUM-EXPORT-REPORT-001',
            Transaction::SOURCE_STADIUM,
            Transaction::ORDER_COMPLETED,
            200000,
            $date,
            1
        );
        $this->createTransaction(
            'BOOTH-EXPORT-REPORT-001',
            Transaction::SOURCE_BOOTH,
            'PAID',
            200000,
            $date,
            1
        );

        $export = new CashierSalesReportExport(
            Request::create('/admin/cashier-sales-reports/export', 'GET', [
                'start_date' => '2031-05-12',
                'end_date' => '2031-05-12',
            ])
        );

        $invoices = $export->collection()->pluck('invoice')->unique()->values()->all();

        $this->assertSame(['POS-EXPORT-REPORT-001'], $invoices);
    }

    public function test_export_endpoint_downloads_cashier_report(): void
    {
        $this->loginAsSuperAdmin();

        $this->createTransaction(
            'POS-EXPORT-HTTP-001',
            Transaction::SOURCE_POS,
            Transaction::ORDER_COMPLETED,
            100000,
            Carbon::parse('2031-08-12 10:00:00'),
            1
        );

        $response = $this->get(route('admin.cashier-sales-reports.export', [
            'start_date' => '2031-08-12',
            'end_date' => '2031-08-12',
        ]));

        $response->assertOk();

        $this->assertStringContainsString(
            'Laporan_Penjualan_Kasir_',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_cashier_report_menu_stays_active_on_its_page(): void
    {
        $this->loginAsSuperAdmin();

        $response = $this->get(route('admin.cashier-sales-reports'));

        $response->assertOk();

        $html = $response->getContent();
        $urlPosition = strpos(
            $html,
            'href="' . route('admin.cashier-sales-reports') . '"'
        );

        $this->assertNotFalse($urlPosition);

        $anchorStart = strrpos(substr($html, 0, $urlPosition), '<a');

        $anchor = substr(
            $html,
            $anchorStart,
            500
        );

        $this->assertStringContainsString('bg-[#C99322]', $anchor);
        $this->assertStringContainsString('text-white', $anchor);
    }

    public function test_reports_role_can_access_cashier_sales_report(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'reports',
        ]));

        $this->get(route('admin.cashier-sales-reports'))->assertOk();
        $this->get(route('admin.cashier-sales-reports.print'))->assertOk();
    }

    public function test_stadium_sales_report_still_only_displays_stadium_transactions(): void
    {
        $this->loginAsSuperAdmin();

        $date = Carbon::parse('2031-09-09 10:00:00');

        $this->createTransaction(
            'POS-STADIUM-SEPARATION-001',
            Transaction::SOURCE_POS,
            Transaction::ORDER_COMPLETED,
            100000,
            $date,
            1
        );
        $this->createTransaction(
            'STADIUM-STADIUM-SEPARATION-001',
            Transaction::SOURCE_STADIUM,
            Transaction::ORDER_COMPLETED,
            200000,
            $date,
            1
        );

        $this->get(route('admin.stadium-sales-reports', [
            'start_date' => '2031-09-09',
            'end_date' => '2031-09-09',
        ]))
            ->assertOk()
            ->assertSee('Laporan Penjualan Stadion')
            ->assertSee('STADIUM-STADIUM-SEPARATION-001')
            ->assertDontSee('POS-STADIUM-SEPARATION-001');
    }

    public function test_sidebar_places_kasir_above_giveaway_and_cashier_report_next_to_stadium(): void
    {
        $sidebar = file_get_contents(
            resource_path('views/admin/layouts/sidebar.blade.php')
        );

        $kasirPosition = strpos($sidebar, "route('admin.kasir')");
        $giveawayPosition = strpos($sidebar, "route('admin.giveaway-picker')");

        $this->assertNotFalse($kasirPosition);
        $this->assertNotFalse($giveawayPosition);
        $this->assertLessThan($giveawayPosition, $kasirPosition);

        $stadiumReportPosition = strpos(
            $sidebar,
            "route('admin.stadium-sales-reports')"
        );
        $cashierReportPosition = strpos(
            $sidebar,
            "route('admin.cashier-sales-reports')"
        );

        $this->assertNotFalse($stadiumReportPosition);
        $this->assertNotFalse($cashierReportPosition);
        $this->assertGreaterThan($stadiumReportPosition, $cashierReportPosition);
    }
}
