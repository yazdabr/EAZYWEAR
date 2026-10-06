<?php

namespace Tests\Feature;

use App\Mail\OrderCreatedMail;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderCreatedMailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_order_created_email_renders_va_payment_details(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
            'va_number' => '1234567890',
            'va_expired_at' => now('UTC')->addMinutes(30),
            'qris_reference_no' => null,
            'qris_expired_at' => null,
        ]);

        $mail = new OrderCreatedMail($transaction);

        $rendered = $mail->render();

        $this->assertStringContainsString(
            'Nomor Virtual Account',
            $rendered
        );

        $this->assertStringContainsString(
            '1234567890',
            $rendered
        );

        $this->assertStringContainsString(
            'MANDIRI',
            $rendered
        );

        $this->assertStringNotContainsString(
            'Referensi QRIS',
            $rendered
        );
    }

    public function test_order_created_email_renders_qris_payment_details(): void
    {
        $transaction = Transaction::factory()->create([
            'payment_method' => 'QRIS',
            'va_bank' => null,
            'va_number' => null,
            'va_expired_at' => null,
            'qris_reference_no' => 'DOKU-QRIS-REF-001',
            'qris_expired_at' => now('UTC')->addMinutes(10),
        ]);

        $mail = new OrderCreatedMail($transaction);

        $rendered = $mail->render();

        $this->assertStringContainsString(
            'Cara Pembayaran QRIS',
            $rendered
        );

        $this->assertStringContainsString(
            'Referensi QRIS',
            $rendered
        );

        $this->assertStringContainsString(
            'DOKU-QRIS-REF-001',
            $rendered
        );

        $this->assertStringContainsString(
            'Gunakan QRIS pada halaman pembayaran',
            $rendered
        );

        $this->assertStringNotContainsString(
            'Nomor Virtual Account',
            $rendered
        );
    }
}