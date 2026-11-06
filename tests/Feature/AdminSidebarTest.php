<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminSidebarTest extends TestCase
{
    use DatabaseTransactions;

    public function test_kasir_transaction_menu_uses_calculator_icon(): void
    {
        $sidebar = file_get_contents(
            resource_path('views/admin/layouts/sidebar.blade.php')
        );

        $this->assertSame(
            2,
            substr_count($sidebar, 'x-heroicon-o-calculator')
        );

        $this->assertMatchesRegularExpression(
            '/<x-heroicon-o-calculator class="h-5 w-5 shrink-0"\/>\s*<span>Data Transaksi Kasir<\/span>/',
            $sidebar
        );

        $this->assertDoesNotMatchRegularExpression(
            '/<x-heroicon-o-shopping-cart[^>]*\/>\s*<span>Data Transaksi Kasir<\/span>/',
            $sidebar
        );

        $this->assertMatchesRegularExpression(
            '/<x-heroicon-o-receipt-percent[^>]*\/>\s*<span>Data Transaksi Web<\/span>/',
            $sidebar
        );

        $this->assertMatchesRegularExpression(
            '/<x-heroicon-o-clipboard-document-list[^>]*\/>\s*<span>Data Transaksi WA<\/span>/',
            $sidebar
        );
    }

    public function test_kasir_transaction_menu_stays_active_on_its_page(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $response = $this->get(route('admin.kasir-transactions.index'));

        $response->assertOk();

        $html = $response->getContent();
        $urlPosition = strpos(
            $html,
            'href="' . route('admin.kasir-transactions.index') . '"'
        );

        $this->assertNotFalse($urlPosition);

        $anchorStart = strrpos(
            substr($html, 0, $urlPosition),
            '<a'
        );
        $anchorEnd = strpos($html, '>', $urlPosition);

        $anchor = substr(
            $html,
            $anchorStart,
            $anchorEnd - $anchorStart + 1
        );

        $this->assertStringContainsString('bg-[#C99322]', $anchor);
        $this->assertStringContainsString('text-white', $anchor);
    }
}
