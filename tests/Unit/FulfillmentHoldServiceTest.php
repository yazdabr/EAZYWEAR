<?php

namespace Tests\Unit;

use App\Models\FulfillmentHold;
use App\Models\FulfillmentSlot;
use App\Models\Transaction;
use App\Services\FulfillmentHoldService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FulfillmentHoldServiceTest extends TestCase
{
    use DatabaseTransactions;

    private FulfillmentHoldService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FulfillmentHoldService::class);
    }

    private function createPendingTransaction(
        string $invoice = 'TEST-HOLD-001'
    ): Transaction {
        return Transaction::create([
            'invoice_number' => $invoice,
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'status' => 'PENDING',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'test@example.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Test Address',
            'shipping_district' => 'Test District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'fulfillment_date' => '2026-10-30',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 0,
            'total' => 100000,
        ]);
    }

    public function test_pending_transaction_can_create_hold_without_increasing_used_count(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $transaction = $this->createPendingTransaction();

        $hold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $this->assertSame(
            FulfillmentHold::HELD,
            $hold->status
        );

        $this->assertSame(
            $transaction->id,
            $hold->transaction_id
        );

        $this->assertSame(
            $slot->id,
            $hold->fulfillment_slot_id
        );

        $this->assertDatabaseHas('fulfillment_holds', [
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $this->assertDatabaseHas('fulfillment_slots', [
            'id' => $slot->id,
            'used_count' => 0,
        ]);
    }

    public function test_hold_uses_available_capacity_after_paid_orders(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'used_count' => 99,
                'capacity' => 100,
            ]);

        $transaction = $this->createPendingTransaction();

        $hold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $this->assertSame(
            FulfillmentHold::HELD,
            $hold->status
        );

        $this->assertDatabaseHas('fulfillment_slots', [
            'date' => '2026-10-30',
            'used_count' => 99,
            'capacity' => 100,
        ]);
    }

    public function test_hold_is_rejected_when_paid_orders_and_existing_holds_fill_capacity(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $slot->update([
            'used_count' => 99,
            'capacity' => 100,
        ]);

        $existingTransaction = $this->createPendingTransaction(
            'TEST-HOLD-EXISTING'
        );

        FulfillmentHold::create([
            'transaction_id' => $existingTransaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $transaction = $this->createPendingTransaction(
            'TEST-HOLD-FULL'
        );

        try {
            $this->service->createHold(
                $transaction,
                '2026-10-30'
            );

            $this->fail(
                'Hold seharusnya ditolak ketika used_count + active holds mencapai capacity.'
            );
        } catch (ValidationException $e) {
            $this->assertSame(
                'Kuota fulfillment pada tanggal tersebut sudah penuh.',
                $e->validator->errors()->first('fulfillment')
            );
        }

        $this->assertDatabaseCount('fulfillment_holds', 1);

        $this->assertDatabaseHas('fulfillment_slots', [
            'id' => $slot->id,
            'used_count' => 99,
        ]);
    }

    public function test_hold_can_use_last_available_capacity(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $slot->update([
            'used_count' => 98,
            'capacity' => 100,
        ]);

        $existingTransaction = $this->createPendingTransaction(
            'TEST-HOLD-LAST-EXISTING'
        );

        FulfillmentHold::create([
            'transaction_id' => $existingTransaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $transaction = $this->createPendingTransaction(
            'TEST-HOLD-LAST'
        );

        $hold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $this->assertSame(
            FulfillmentHold::HELD,
            $hold->status
        );

        $this->assertSame(
            2,
            FulfillmentHold::query()
                ->where('fulfillment_slot_id', $slot->id)
                ->where('status', FulfillmentHold::HELD)
                ->count()
        );

        $this->assertDatabaseHas('fulfillment_slots', [
            'id' => $slot->id,
            'used_count' => 98,
            'capacity' => 100,
        ]);
    }

    public function test_same_transaction_does_not_create_duplicate_hold(): void
    {
        $transaction = $this->createPendingTransaction();

        $firstHold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $secondHold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $this->assertSame(
            $firstHold->id,
            $secondHold->id
        );

        $this->assertDatabaseCount('fulfillment_holds', 1);
    }

    public function test_hold_requires_existing_fulfillment_slot(): void
    {
        $transaction = $this->createPendingTransaction();

        try {
            $this->service->createHold(
                $transaction,
                '2026-11-04'
            );

            $this->fail(
                'Hold seharusnya ditolak jika fulfillment slot tidak tersedia.'
            );
        } catch (ValidationException $e) {
            $this->assertSame(
                'Tanggal fulfillment tidak tersedia.',
                $e->validator->errors()->first('fulfillment')
            );
        }

        $this->assertDatabaseCount('fulfillment_holds', 0);
    }

    public function test_hold_requires_pending_transaction(): void
    {
        $transaction = $this->createPendingTransaction();

        $transaction->update([
            'status' => 'PAID',
        ]);

        try {
            $this->service->createHold(
                $transaction,
                '2026-10-30'
            );

            $this->fail(
                'Hold seharusnya hanya dapat dibuat untuk transaksi PENDING.'
            );
        } catch (ValidationException $e) {
            $this->assertSame(
                'Fulfillment hold hanya dapat dibuat untuk transaksi PENDING.',
                $e->validator->errors()->first('fulfillment')
            );
        }

        $this->assertDatabaseCount('fulfillment_holds', 0);
    }
    public function test_convert_hold_to_allocation_changes_status_and_increases_used_count(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $slot->update([
            'used_count' => 10,
            'capacity' => 100,
        ]);

        $transaction = $this->createPendingTransaction(
            'TEST-HOLD-CONVERT-001'
        );

        $hold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $convertedHold = $this->service->convertHoldToAllocation(
            $transaction
        );

        $this->assertSame(
            $hold->id,
            $convertedHold->id
        );

        $this->assertSame(
            FulfillmentHold::CONVERTED,
            $convertedHold->status
        );

        $this->assertDatabaseHas('fulfillment_holds', [
            'id' => $hold->id,
            'status' => FulfillmentHold::CONVERTED,
            'released_at' => null,
        ]);

        $this->assertDatabaseHas('fulfillment_slots', [
            'id' => $slot->id,
            'used_count' => 11,
        ]);
    }

    public function test_released_hold_cannot_be_converted_to_allocation(): void
    {
        $transaction = $this->createPendingTransaction(
            'TEST-HOLD-CONVERT-RELEASED'
        );

        $hold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $this->service->releaseHold($transaction);

        $this->assertDatabaseHas('fulfillment_holds', [
            'id' => $hold->id,
            'status' => FulfillmentHold::RELEASED,
        ]);

        try {
            $this->service->convertHoldToAllocation($transaction);

            $this->fail(
                'Hold RELEASED tidak boleh dikonversi menjadi allocation.'
            );
        } catch (ValidationException $e) {
            $this->assertSame(
                'Fulfillment hold tidak dapat dikonversi dari status saat ini.',
                $e->validator->errors()->first('fulfillment')
            );
        }

        $this->assertDatabaseHas('fulfillment_slots', [
            'date' => '2026-10-30',
            'used_count' => 0,
        ]);
    }

    public function test_release_hold_changes_held_to_released_without_changing_used_count(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $slot->update([
            'used_count' => 10,
        ]);

        $transaction = $this->createPendingTransaction(
            'TEST-HOLD-RELEASE-001'
        );

        $hold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $releasedHold = $this->service->releaseHold($transaction);

        $this->assertSame(
            $hold->id,
            $releasedHold->id
        );

        $this->assertSame(
            FulfillmentHold::RELEASED,
            $releasedHold->status
        );

        $this->assertNotNull($releasedHold->released_at);

        $this->assertDatabaseHas('fulfillment_holds', [
            'id' => $hold->id,
            'status' => FulfillmentHold::RELEASED,
        ]);

        $this->assertDatabaseHas('fulfillment_slots', [
            'id' => $slot->id,
            'used_count' => 10,
        ]);
    }

    public function test_release_hold_is_idempotent(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $slot->update([
            'used_count' => 10,
        ]);

        $transaction = $this->createPendingTransaction(
            'TEST-HOLD-RELEASE-IDEMPOTENT'
        );

        $hold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $firstRelease = $this->service->releaseHold($transaction);

        $releasedAt = $firstRelease->released_at;

        $secondRelease = $this->service->releaseHold($transaction);

        $this->assertSame(
            $hold->id,
            $secondRelease->id
        );

        $this->assertSame(
            FulfillmentHold::RELEASED,
            $secondRelease->status
        );

        $this->assertEquals(
            $releasedAt,
            $secondRelease->released_at
        );

        $this->assertDatabaseHas('fulfillment_slots', [
            'id' => $slot->id,
            'used_count' => 10,
        ]);
    }

    public function test_release_paid_allocation_changes_converted_to_released_and_decreases_used_count(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $slot->update([
            'used_count' => 10,
        ]);

        $transaction = $this->createPendingTransaction(
            'TEST-HOLD-PAID-RELEASE-001'
        );

        $hold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $this->service->convertHoldToAllocation($transaction);

        $releasedHold = $this->service->releasePaidAllocation($transaction);

        $this->assertSame(
            $hold->id,
            $releasedHold->id
        );

        $this->assertSame(
            FulfillmentHold::RELEASED,
            $releasedHold->status
        );

        $this->assertNotNull($releasedHold->released_at);

        $this->assertDatabaseHas('fulfillment_slots', [
            'id' => $slot->id,
            'used_count' => 10,
        ]);
    }

    public function test_release_paid_allocation_never_decreases_used_count_below_zero(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $slot->update([
            'used_count' => 0,
        ]);

        $transaction = $this->createPendingTransaction(
            'TEST-HOLD-PAID-RELEASE-ZERO'
        );

        $hold = $this->service->createHold(
            $transaction,
            '2026-10-30'
        );

        $this->service->convertHoldToAllocation($transaction);

        /*
        * Simulasikan kondisi data yang tidak konsisten:
        * hold sudah CONVERTED tetapi used_count ternyata 0.
        *
        * Service tidak boleh menghasilkan -1.
        */
        $releasedHold = $this->service->releasePaidAllocation($transaction);

        $this->assertSame(
            FulfillmentHold::RELEASED,
            $releasedHold->status
        );

        $this->assertDatabaseHas('fulfillment_slots', [
            'id' => $slot->id,
            'used_count' => 0,
        ]);
    }
    public function test_allocate_earliest_available_hold_skips_full_dates(): void
    {
        $oct30 = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $oct31 = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-31')
            ->firstOrFail();

        $oct30->update([
            'used_count' => 100,
        ]);

        $transaction = $this->createPendingTransaction(
            'TEST-HOLD-EARLIEST-001'
        );

        $hold = $this->service->allocateEarliestAvailableHold(
            $transaction
        );

        $this->assertSame(
            $oct31->id,
            $hold->fulfillment_slot_id
        );

        $this->assertSame(
            FulfillmentHold::HELD,
            $hold->status
        );

        $this->assertDatabaseHas('fulfillment_holds', [
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $oct31->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $this->assertSame(
            0,
            FulfillmentSlot::query()
                ->findOrFail($oct31->id)
                ->used_count
        );
    }
}