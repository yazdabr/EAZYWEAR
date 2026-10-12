<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TransactionShippingAddressTest extends TestCase
{
    use DatabaseTransactions;

    private function createCourierTransaction(array $overrides = []): Transaction
    {
        $customer = Customer::query()->firstOrFail();

        return Transaction::create(array_merge([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-ADDRESS-' . uniqid(),
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 8000,
            'total' => 108000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',
            'shipping_name' => 'Customer Address Test',
            'shipping_email' => 'address-test@example.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Lama',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
        ], $overrides));
    }

    public function test_admin_can_update_only_shipping_address_before_shipment_is_created(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $transaction = $this->createCourierTransaction();

        $response = $this->patchJson(
            route('admin.transactions.shipping-address.update', $transaction),
            ['shipping_address' => 'Alamat Baru Nomor 123']
        );

        $response->assertOk()->assertJson([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'shipping_address' => 'Alamat Baru Nomor 123',
            ],
        ]);

        $transaction->refresh();
        $this->assertSame('Alamat Baru Nomor 123', $transaction->shipping_address);
        $this->assertSame(108000, (int) $transaction->total);
        $this->assertSame('Banjarmasin Selatan', $transaction->shipping_district);
        $this->assertSame('70111', (string) $transaction->shipping_postal_code);
        $this->assertNull($transaction->tracking_number);
        $this->assertNull($transaction->biteship_order_id);
    }

    public function test_admin_can_update_shipping_address_before_processing_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $transaction = $this->createCourierTransaction([
            'status' => 'PAID',
        ]);

        $response = $this->patchJson(
            route('admin.transactions.shipping-address.update', $transaction),
            ['shipping_address' => 'Alamat Baru Sebelum Diproses']
        );

        $response->assertOk()->assertJson([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'shipping_address' => 'Alamat Baru Sebelum Diproses',
            ],
        ]);

        $this->assertSame(
            'Alamat Baru Sebelum Diproses',
            $transaction->fresh()->shipping_address
        );
    }

    public function test_admin_cannot_update_shipping_address_after_tracking_number_exists(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $transaction = $this->createCourierTransaction([
            'tracking_number' => 'RESI-EXISTING-001',
        ]);

        $response = $this->patchJson(
            route('admin.transactions.shipping-address.update', $transaction),
            ['shipping_address' => 'Alamat yang tidak boleh disimpan']
        );

        $response->assertUnprocessable();
        $this->assertSame('Alamat Lama', $transaction->fresh()->shipping_address);
    }

    public function test_admin_cannot_update_shipping_address_for_pickup_orders(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $transaction = $this->createCourierTransaction([
            'shipping_method' => 'Ambil di Tempat',
        ]);

        $response = $this->patchJson(
            route('admin.transactions.shipping-address.update', $transaction),
            ['shipping_address' => 'Alamat yang tidak boleh disimpan']
        );

        $response->assertUnprocessable();
        $this->assertSame('Alamat Lama', $transaction->fresh()->shipping_address);
    }

    public function test_admin_cannot_update_shipping_address_while_shipment_lock_is_held(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $transaction = $this->createCourierTransaction();

        $lock = \Illuminate\Support\Facades\Cache::lock(
            'biteship:ship:' . $transaction->id,
            120
        );

        $this->assertTrue($lock->get());

        try {
            $response = $this->patchJson(
                route('admin.transactions.shipping-address.update', $transaction),
                ['shipping_address' => 'Alamat yang tidak boleh tersimpan']
            );

            $response->assertStatus(409);

            $this->assertSame(
                'Alamat Lama',
                $transaction->fresh()->shipping_address
            );
        } finally {
            $lock->release();
        }
    }
}
