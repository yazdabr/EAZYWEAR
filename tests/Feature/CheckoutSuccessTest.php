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
}