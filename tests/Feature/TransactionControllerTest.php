<?php

namespace Tests\Feature;

use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
        ]);
    }

    public function test_pending_transaction_can_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_paid_transaction_cannot_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'PAID',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PAID',
        ]);
    }

    public function test_completed_transaction_cannot_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'COMPLETED',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'COMPLETED',
        ]);
    }

    public function test_cancelled_transaction_without_stock_movement_can_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'CANCELLED',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_transaction_with_stock_movement_cannot_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'CANCELLED',
        ]);

        /*
         * Sesuaikan field berikut dengan struktur tabel stock_movements
         * dan fillable pada model StockMovement.
         */
        StockMovement::factory()->create([
            'transaction_id' => $transaction->id,
            'type' => 'IN',
            'qty' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'CANCELLED',
        ]);
    }
}