<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Tests\TestCase;

class CheckoutSuccessTest extends TestCase
{
    public function test_qris_success_page_renders_qr_code_and_keeps_transaction_pending(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'QRIS',
            'status' => 'PENDING',
            'qris_reference_no' => 'DOKU-QRIS-REF-001',
            'qris_content' => '00020101021226670016COM.DOKU.WWW01189360091500000000000215QRIS2026100614300303UMI5204599953033605802ID5908Eazywear6007Banjarmasin6105701116304ABCD',
            'qris_expired_at' => now()->addMinutes(10),
        ]);

        $response = $this
            ->withSession([
                'checkout_success_invoice' => $transaction->invoice_number,
            ])
            ->get(route('checkout.success'));

        $response->assertSee('Pembayaran QRIS');
        $response->assertSee('Scan QRIS berikut menggunakan aplikasi pembayaran yang mendukung QRIS.');
        $response->assertSee('DOKU-QRIS-REF-001');
        $response->assertSee('Waktu pembayaran tersisa');
        $response->assertDontSee('Pembayaran Virtual Account');
        $response->assertDontSee('Nomor Virtual Account');

        $response->assertSuccessful();

        $response->assertViewIs('checkout.success');

        $response->assertViewHas('transaction', function ($viewTransaction) use ($transaction) {
            return $viewTransaction->is($transaction);
        });

        $response->assertViewHas('qrisQrCode', function ($qrisQrCode) {
            return is_string($qrisQrCode)
                && str_contains($qrisQrCode, '<svg')
                && str_contains($qrisQrCode, '</svg>');
        });

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PENDING',
            'payment_method' => 'QRIS',
        ]);
    }

    public function test_va_success_page_renders_without_qris_code(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'VA',
            'status' => 'PENDING',
            'va_bank' => 'MANDIRI',
            'va_number' => '1234567890123456',
            'va_expired_at' => now()->addMinutes(10),
            'qris_reference_no' => null,
            'qris_content' => null,
            'qris_expired_at' => null,
            'qris_response' => null,
        ]);

        $response = $this
            ->withSession([
                'checkout_success_invoice' => $transaction->invoice_number,
            ])
            ->get(route('checkout.success'));

        $response->assertSuccessful();

        $response->assertSee('Pembayaran Virtual Account');
        $response->assertSee('Nomor Virtual Account');
        $response->assertSee('MANDIRI');
        $response->assertDontSee('Pembayaran QRIS');

        $response->assertViewIs('checkout.success');

        $response->assertViewHas('transaction', function ($viewTransaction) use ($transaction) {
            return $viewTransaction->is($transaction);
        });

        $response->assertViewHas('qrisQrCode', null);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PENDING',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);
    }

    public function test_qris_success_page_does_not_fail_when_qris_content_is_missing(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'QRIS',
            'status' => 'PENDING',
            'qris_reference_no' => null,
            'qris_content' => null,
            'qris_expired_at' => null,
            'qris_response' => null,
        ]);

        $response = $this
            ->withSession([
                'checkout_success_invoice' => $transaction->invoice_number,
            ])
            ->get(route('checkout.success'));

        $response->assertSuccessful();

        $response->assertViewIs('checkout.success');

        $response->assertViewHas('transaction', function ($viewTransaction) use ($transaction) {
            return $viewTransaction->is($transaction);
        });

        $response->assertViewHas('qrisQrCode', null);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PENDING',
            'payment_method' => 'QRIS',
        ]);
    }
    public function test_success_page_can_be_refreshed_with_same_session_invoice(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'VA',
            'status' => 'PENDING',
            'va_bank' => 'MANDIRI',
            'va_number' => '1234567890123456',
            'va_expired_at' => now()->addMinutes(10),
        ]);

        $session = [
            'checkout_success_invoice' => $transaction->invoice_number,
        ];

        $firstResponse = $this
            ->withSession($session)
            ->get(route('checkout.success'));

        $firstResponse->assertSuccessful();
        $firstResponse->assertViewIs('checkout.success');

        $secondResponse = $this
            ->withSession($session)
            ->get(route('checkout.success'));

        $secondResponse->assertSuccessful();
        $secondResponse->assertViewIs('checkout.success');
        $secondResponse->assertViewHas('transaction', function ($viewTransaction) use ($transaction) {
            return $viewTransaction->is($transaction);
        });
    }

    public function test_payment_status_returns_404_without_checkout_session(): void
    {
        $response = $this->getJson(route('checkout.payment-status'));

        $response->assertNotFound();

        $response->assertJson([
            'success' => false,
            'message' => 'Sesi pesanan tidak ditemukan.',
        ]);
    }

    public function test_payment_status_returns_paid_false_for_pending_transaction(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'VA',
            'status' => 'PENDING',
            'paid_at' => null,
        ]);

        $response = $this
            ->withSession([
                'checkout_success_invoice' => $transaction->invoice_number,
            ])
            ->getJson(route('checkout.payment-status'));

        $response->assertSuccessful();

        $response->assertJson([
            'success' => true,
            'invoice_number' => $transaction->invoice_number,
            'email' => $transaction->shipping_email,
            'status' => 'PENDING',
            'paid' => false,
        ]);
    }

    public function test_payment_status_returns_paid_true_for_paid_transaction(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'QRIS',
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        $response = $this
            ->withSession([
                'checkout_success_invoice' => $transaction->invoice_number,
            ])
            ->getJson(route('checkout.payment-status'));

        $response->assertSuccessful();

        $response->assertJson([
            'success' => true,
            'invoice_number' => $transaction->invoice_number,
            'email' => $transaction->shipping_email,
            'status' => 'PAID',
            'paid' => true,
        ]);
    }

    public function test_payment_status_returns_transaction_invoice_and_email(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'QRIS',
            'status' => 'PAID',
            'paid_at' => now(),
            'shipping_email' => 'customer@example.com',
        ]);

        $response = $this
            ->withSession([
                'checkout_success_invoice' => $transaction->invoice_number,
            ])
            ->getJson(route('checkout.payment-status'));

        $response->assertSuccessful();

        $response->assertJsonPath(
            'invoice_number',
            $transaction->invoice_number
        );

        $response->assertJsonPath(
            'email',
            'customer@example.com'
        );
    }
}
