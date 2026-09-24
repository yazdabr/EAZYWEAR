<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExpireTransactionsCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_command_expires_only_pending_transactions_past_va_deadline(): void
    {
        $expiredTransaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $activeTransaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'va_expired_at' => now('UTC')->addMinute(),
        ]);

        $paidTransaction = Transaction::factory()->create([
            'status' => 'PAID',
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $exitCode = Artisan::call('transactions:expire');

        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('transactions', [
            'id' => $expiredTransaction->id,
            'status' => 'EXPIRED',
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $activeTransaction->id,
            'status' => 'PENDING',
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $paidTransaction->id,
            'status' => 'PAID',
        ]);
    }

    public function test_command_does_not_expire_pending_transaction_without_va_deadline(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'va_expired_at' => null,
        ]);

        $exitCode = Artisan::call('transactions:expire');

        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PENDING',
        ]);
    }
}