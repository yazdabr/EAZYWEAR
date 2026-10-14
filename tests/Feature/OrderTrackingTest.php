<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionFulfillment;
use App\Models\TransactionShipment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use DatabaseTransactions;

    private function createTransaction(array $overrides = []): Transaction
    {
        $customer = Customer::query()->firstOrFail();

        return Transaction::create(array_merge([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TRACK-' . strtoupper(uniqid()),
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 200000,
            'discount' => 0,
            'shipping' => 15000,
            'total' => 215000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',
            'shipping_name' => 'Customer Tracking Test',
            'shipping_email' => 'tracking-test@example.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Tracking Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
        ], $overrides));
    }

    private function createFulfillment(
        Transaction $transaction,
        string $groupType
    ): TransactionFulfillment {
        return TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => $groupType,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 0,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_district' => $transaction->shipping_district,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'status' => TransactionFulfillment::STATUS_PENDING,
        ]);
    }

    private function createShipment(
        Transaction $transaction,
        string $type,
        string $trackingNumber,
        int $shippingCost = 7500
    ): TransactionShipment {
        return TransactionShipment::create([
            'transaction_id' => $transaction->id,
            'shipment_type' => $type,
            'shipping_method' => 'Kurir',
            'shipping_cost' => $shippingCost,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_district' => $transaction->shipping_district,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'tracking_number' => $trackingNumber,
            'courier' => 'J&T Express',
            'status' => TransactionShipment::STATUS_SHIPPED,
        ]);
    }

    private function searchOrder(Transaction $transaction, ?string $email = null)
    {
        return $this->post(route('orders.tracking.search'), [
            'invoice_number' => $transaction->invoice_number,
            'email' => $email ?? $transaction->shipping_email,
        ]);
    }

    public function test_combined_shipment_is_displayed_as_one_shipment(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-COMBINED-' . strtoupper(uniqid()),
        ]);

        $ready = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_READY
        );

        $preOrder = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_PRE_ORDER
        );

        $shipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_COMBINED,
            'RESI-COMBINED-001',
            15000
        );

        $shipment->fulfillments()->attach([$ready->id, $preOrder->id]);

        $this->searchOrder($transaction)
            ->assertOk()
            ->assertViewIs('orders.tracking-detail')
            ->assertSee('Pengiriman Gabungan')
            ->assertSee('RESI-COMBINED-001')
            ->assertSee('Rp 215.000');

        $this->assertSame(
            1,
            $transaction->shipments()->count(),
            'Pengiriman gabungan harus memiliki satu shipment.'
        );
    }

    public function test_separate_shipments_show_ready_stock_and_pre_order_individually(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-SEPARATE-' . strtoupper(uniqid()),
        ]);

        $ready = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_READY
        );

        $preOrder = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_PRE_ORDER
        );

        $readyShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-READY-001'
        );

        $preOrderShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-PO-001'
        );

        $readyShipment->fulfillments()->attach([$ready->id]);
        $preOrderShipment->fulfillments()->attach([$preOrder->id]);

        $this->searchOrder($transaction)
            ->assertOk()
            ->assertViewIs('orders.tracking-detail')
            ->assertSee('Ready Stock')
            ->assertSee('Pre-Order')
            ->assertSee('RESI-READY-001')
            ->assertSee('RESI-PO-001')
            ->assertSee('Rp 215.000');

        $this->assertSame(
            2,
            $transaction->shipments()->count(),
            'Pengiriman terpisah harus memiliki dua shipment.'
        );
    }

    public function test_legacy_transaction_without_shipments_still_displays_shipping_details(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-LEGACY-' . strtoupper(uniqid()),
            'status' => Transaction::ORDER_SHIPPED,
            'tracking_number' => 'RESI-LEGACY-001',
            'courier' => 'J&T Express',
            'shipping' => 10000,
            'total' => 210000,
        ]);

        $this->searchOrder($transaction)
            ->assertOk()
            ->assertViewIs('orders.tracking-detail')
            ->assertSee('RESI-LEGACY-001')
            ->assertSee('Alamat Tracking Test')
            ->assertSee('Rp 210.000');

        $this->assertSame(0, $transaction->shipments()->count());
    }

    public function test_wrong_email_cannot_view_order_details(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-WRONG-EMAIL-' . strtoupper(uniqid()),
        ]);

        $this->searchOrder($transaction, 'wrong-email@example.com')
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'Pesanan tidak ditemukan. Pastikan nomor invoice dan email sudah benar.'
            );
    }

    public function test_payment_summary_uses_invoice_total_not_shipment_cost_sum(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-TOTAL-' . strtoupper(uniqid()),
            'shipping' => 15000,
            'total' => 215000,
        ]);

        $ready = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_READY
        );

        $preOrder = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_PRE_ORDER
        );

        $readyShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-TOTAL-READY',
            7500
        );

        $preOrderShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-TOTAL-PO',
            7500
        );

        $readyShipment->fulfillments()->attach([$ready->id]);
        $preOrderShipment->fulfillments()->attach([$preOrder->id]);

        $this->searchOrder($transaction)
            ->assertOk()
            ->assertSee('Rp 215.000');

        $transaction->refresh();

        $this->assertSame(15000, (int) $transaction->shipping);
        $this->assertSame(215000, (int) $transaction->total);
    }
}
