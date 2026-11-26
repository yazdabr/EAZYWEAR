<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('minimarket_payment_code', 100)
                ->nullable()
                ->after('qris_response');

            $table->string('minimarket_store', 30)
                ->nullable()
                ->after('minimarket_payment_code');

            $table->dateTime('minimarket_expired_at')
                ->nullable()
                ->after('minimarket_store');

            $table->json('minimarket_response')
                ->nullable()
                ->after('minimarket_expired_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'minimarket_payment_code',
                'minimarket_store',
                'minimarket_expired_at',
                'minimarket_response',
            ]);
        });
    }
};
