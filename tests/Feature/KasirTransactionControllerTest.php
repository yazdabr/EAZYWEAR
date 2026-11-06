<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KasirTransactionControllerTest extends TestCase
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
        ?string $source,
        string $status = Transaction::ORDER_COMPLETED,
        float $total = 100000
    ): Transaction {
        return Transaction::create([
            'invoice_number' => $invoice,
            'transaction_date' => now(),
            'payment_method' => 'CASH',
            'subtotal' => $total,
            'discount' => 0,
            'shipping' => 0,
            'total' => $total,
            'status' => $status,
            'source' => $source,
        ]);
    }

    public function test_index_only_displays_pos_transactions(): void
    {
        $this->loginAsSuperAdmin();

        $baselineTotal = Transaction::query()->fromKasirPos()->count();
        $baselineCompleted = Transaction::query()->fromKasirPos()
            ->whereIn('status', [Transaction::ORDER_COMPLETED, 'PAID'])
            ->count();
        $baselineRevenue = (float) Transaction::query()->fromKasirPos()
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->sum('total');

        $this->createTransaction('POS-TEST-001', Transaction::SOURCE_POS);
        $this->createTransaction('BOOTH-TEST-001', Transaction::SOURCE_BOOTH, 'PAID', 200000);
        $this->createTransaction('WEB-TEST-001', Transaction::SOURCE_WEBSITE);
        $this->createTransaction('LEGACY-WEB-TEST-001', null);

        $response = $this->get(route('admin.kasir-transactions.index'));

        $response->assertOk()
            ->assertSee('POS-TEST-001')
            ->assertDontSee('BOOTH-TEST-001')
            ->assertDontSee('WEB-TEST-001')
            ->assertDontSee('LEGACY-WEB-TEST-001');

        $response->assertViewHas('totalTransactions', $baselineTotal + 1);
        $response->assertViewHas('completedOrders', $baselineCompleted + 1);

        $totalRevenue = $response->viewData('totalRevenue');

        $this->assertEquals($baselineRevenue + 100000, (float) $totalRevenue);
    }

    public function test_index_summary_only_counts_pos_transactions(): void
    {
        $this->loginAsSuperAdmin();

        $baselineTotal = Transaction::query()->fromKasirPos()->count();
        $baselinePending = Transaction::query()->fromKasirPos()
            ->where('status', 'PENDING')
            ->count();
        $baselineCompleted = Transaction::query()->fromKasirPos()
            ->whereIn('status', [Transaction::ORDER_COMPLETED, 'PAID'])
            ->count();
        $baselineRevenue = (float) Transaction::query()->fromKasirPos()
            ->whereIn('status', Transaction::SALES_REPORT_STATUSES)
            ->sum('total');

        $this->createTransaction('POS-TEST-002', Transaction::SOURCE_POS, Transaction::ORDER_COMPLETED, 150000);
        $this->createTransaction('POS-TEST-PENDING', Transaction::SOURCE_POS, 'PENDING', 50000);
        $this->createTransaction('BOOTH-SUMMARY-001', Transaction::SOURCE_BOOTH, Transaction::ORDER_COMPLETED, 250000);
        $this->createTransaction('WEB-TEST-002', Transaction::SOURCE_WEBSITE, Transaction::ORDER_COMPLETED, 999000);

        $response = $this->get(route('admin.kasir-transactions.index'));

        $response->assertOk()
            ->assertDontSee('BOOTH-SUMMARY-001')
            ->assertDontSee('WEB-TEST-002');

        $response->assertViewHas('totalTransactions', $baselineTotal + 2);
        $response->assertViewHas('pendingTransactions', $baselinePending + 1);
        $response->assertViewHas('completedOrders', $baselineCompleted + 1);

        $this->assertEquals(
            $baselineRevenue + 150000,
            (float) $response->viewData('totalRevenue')
        );
    }

    public function test_search_only_matches_pos_transactions(): void
    {
        $this->loginAsSuperAdmin();

        $this->createTransaction('POS-SEARCH-001', Transaction::SOURCE_POS);
        $this->createTransaction('BOOTH-SEARCH-001', Transaction::SOURCE_BOOTH, 'PAID');

        $this->get(route('admin.kasir-transactions.index', ['search' => 'SEARCH-001']))
            ->assertOk()
            ->assertSee('POS-SEARCH-001')
            ->assertDontSee('BOOTH-SEARCH-001');
    }

    public function test_show_displays_pos_transaction(): void
    {
        $this->loginAsSuperAdmin();

        $transaction = $this->createTransaction('POS-SHOW-001', Transaction::SOURCE_POS);

        $this->get(route('admin.kasir-transactions.show', $transaction))
            ->assertOk()
            ->assertSee('POS-SHOW-001')
            ->assertSee('POS Kasir')
            ->assertDontSee('Alamat Pengiriman')
            ->assertDontSee('Ongkir');
    }

    public function test_show_rejects_legacy_booth_transaction(): void
    {
        $this->loginAsSuperAdmin();

        $transaction = $this->createTransaction(
            'BOOTH-SHOW-001',
            Transaction::SOURCE_BOOTH,
            'PAID'
        );

        $this->get(route('admin.kasir-transactions.show', $transaction))
            ->assertNotFound();
    }

    public function test_show_does_not_expose_website_transaction(): void
    {
        $this->loginAsSuperAdmin();

        $websiteTransaction = $this->createTransaction(
            'WEB-SHOW-001',
            Transaction::SOURCE_WEBSITE
        );

        $legacyWebsiteTransaction = $this->createTransaction(
            'LEGACY-WEB-SHOW-001',
            null
        );

        $this->get(route('admin.kasir-transactions.show', $websiteTransaction))
            ->assertNotFound();

        $this->get(route('admin.kasir-transactions.show', $legacyWebsiteTransaction))
            ->assertNotFound();
    }

    public function test_print_renders_pos_transaction(): void
    {
        $this->loginAsSuperAdmin();

        $posTransaction = $this->createTransaction(
            'POS-PRINT-001',
            Transaction::SOURCE_POS
        );

        $this->get(route('admin.kasir-transactions.print', $posTransaction->invoice_number))
            ->assertOk()
            ->assertSee('POS-PRINT-001');
    }

    public function test_print_rejects_legacy_booth_and_website_transactions(): void
    {
        $this->loginAsSuperAdmin();

        $boothTransaction = $this->createTransaction(
            'BOOTH-PRINT-001',
            Transaction::SOURCE_BOOTH,
            'PAID'
        );

        $websiteTransaction = $this->createTransaction(
            'WEB-PRINT-001',
            Transaction::SOURCE_WEBSITE
        );

        $this->get(route('admin.kasir-transactions.print', $boothTransaction->invoice_number))
            ->assertNotFound();

        $this->get(route('admin.kasir-transactions.print', $websiteTransaction->invoice_number))
            ->assertNotFound();
    }

    public function test_offline_transaction_page_still_displays_legacy_booth_transaction(): void
    {
        $this->loginAsSuperAdmin();

        $boothTransaction = $this->createTransaction(
            'BOOTH-OFFLINE-001',
            Transaction::SOURCE_BOOTH,
            'PAID',
            200000
        );

        $this->createTransaction('POS-OFFLINE-001', Transaction::SOURCE_POS);

        $this->get(route('admin.offline-transactions.index'))
            ->assertOk()
            ->assertSee('BOOTH-OFFLINE-001')
            ->assertDontSee('POS-OFFLINE-001');

        $this->get(route('admin.offline-transactions.show', $boothTransaction))
            ->assertOk()
            ->assertSee('BOOTH-OFFLINE-001')
            ->assertSee('Cetak');
    }

    public function test_web_transaction_page_does_not_display_pos_transaction(): void
    {
        $this->loginAsSuperAdmin();

        $this->createTransaction('POS-WEB-SEPARATION-001', Transaction::SOURCE_POS);
        $this->createTransaction('WEB-ONLY-001', Transaction::SOURCE_WEBSITE);

        $response = $this->get(route('admin.transactions'));

        $response->assertOk()
            ->assertDontSee('POS-WEB-SEPARATION-001')
            ->assertSee('WEB-ONLY-001');
    }
}
