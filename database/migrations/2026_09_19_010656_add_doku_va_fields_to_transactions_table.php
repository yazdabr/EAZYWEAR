<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('doku_request_id', 100)
                ->nullable()
                ->after('payment_method');

            $table->string('doku_payment_id', 100)
                ->nullable()
                ->after('doku_request_id');

            $table->string('va_number', 100)
                ->nullable()
                ->after('doku_payment_id');

            $table->string('va_bank', 50)
                ->nullable()
                ->after('va_number');

            $table->timestamp('va_expired_at')
                ->nullable()
                ->after('va_bank');

            $table->timestamp('paid_at')
                ->nullable()
                ->after('status');

            $table->json('doku_response')
                ->nullable()
                ->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'doku_request_id',
                'doku_payment_id',
                'va_number',
                'va_bank',
                'va_expired_at',
                'paid_at',
                'doku_response',
            ]);
        });
    }
};