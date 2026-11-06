<?php

namespace Tests\Feature;

use Tests\TestCase;

class PosPaymentModalTest extends TestCase
{
    private function posView(): string
    {
        return file_get_contents(
            resource_path('views/admin/offline-transactions/pos.blade.php')
        );
    }

    public function test_success_modal_can_be_closed_by_x_backdrop_and_escape(): void
    {
        $view = $this->posView();

        $this->assertStringContainsString(
            '@click="closeCheckoutModal()"',
            $view
        );
        $this->assertStringContainsString(
            '@click.self="closeCheckoutModal()"',
            $view
        );
        $this->assertStringContainsString(
            '@keydown.escape.window="closeCheckoutModal()"',
            $view
        );
        $this->assertStringContainsString(
            'aria-label="Tutup popup pembayaran"',
            $view
        );
        $this->assertStringContainsString(
            '@click="window.location.href = historyUrl"',
            $view
        );
    }

    public function test_success_and_confirmation_cards_stop_inside_clicks(): void
    {
        $view = $this->posView();

        $this->assertGreaterThanOrEqual(2, substr_count($view, '@click.stop'));
    }

    public function test_confirm_modal_uses_existing_payment_flow(): void
    {
        $view = $this->posView();

        $this->assertStringContainsString(
            '@click="openPaymentConfirmation()"',
            $view
        );
        $this->assertStringContainsString(
            '@click="confirmPayment()"',
            $view
        );
        $this->assertStringContainsString(
            '@click="cancelPaymentConfirmation()"',
            $view
        );
        $this->assertStringContainsString(
            '@click.self="cancelPaymentConfirmation()"',
            $view
        );
        $this->assertStringContainsString(
            '@keydown.escape.window="cancelPaymentConfirmation()"',
            $view
        );
        $this->assertStringContainsString('Konfirmasi Pembayaran', $view);
        $this->assertStringContainsString('Ya, Proses Pembayaran', $view);
        $this->assertStringContainsString('Tidak, Batalkan', $view);
        $this->assertStringContainsString('x-text="money(total)"', $view);
        $this->assertStringContainsString('x-text="paymentMethodLabel"', $view);
    }

    public function test_confirmation_has_loading_state_and_duplicate_submit_guard(): void
    {
        $view = $this->posView();

        $this->assertMatchesRegularExpression(
            '/confirmPayment\(\)\s*\{\s*if \(this\.isSubmitting \|\| !this\.paymentConfirmOpen\) return;/',
            $view
        );

        $this->assertMatchesRegularExpression(
            '/async checkout\(\)\s*\{\s*if \(this\.isSubmitting\) return;/',
            $view
        );

        $this->assertStringContainsString(
            'x-text="isSubmitting ? \'Memproses Pembayaran...\' : \'Ya, Proses Pembayaran\'"',
            $view
        );

        $this->assertStringContainsString(':disabled="isSubmitting"', $view);
    }
}
