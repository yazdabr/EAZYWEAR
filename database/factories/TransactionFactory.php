<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $invoiceNumber = 'TEST-' . strtoupper(Str::random(10));

        return [
            'customer_id' => null,
            'invoice_number' => $invoiceNumber,
            'transaction_date' => now(),
            'payment_method' => 'DOKU',
            'doku_request_id' => null,
            'doku_payment_id' => null,
            'va_number' => null,
            'va_bank' => null,
            'va_expired_at' => null,
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 0,
            'total' => 100000,
            'status' => 'PENDING',
            'paid_at' => null,
            'doku_response' => null,
            'source' => 'Website',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Test Address',
            'shipping_district' => 'Test District',
            'shipping_city' => 'Tangerang',
            'shipping_province' => 'Banten',
            'shipping_postal_code' => '15111',
            'shipping_method' => 'Regular',
        ];
    }
}