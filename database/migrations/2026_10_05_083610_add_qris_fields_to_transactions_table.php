<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('qris_reference_no')
                ->nullable()
                ->after('doku_payment_id');

            $table->text('qris_content')
                ->nullable()
                ->after('qris_reference_no');

            $table->dateTime('qris_expired_at')
                ->nullable()
                ->after('qris_content');

            $table->json('qris_response')
                ->nullable()
                ->after('qris_expired_at');

            $table->index('qris_reference_no');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex([
                'qris_reference_no',
            ]);

            $table->dropColumn([
                'qris_reference_no',
                'qris_content',
                'qris_expired_at',
                'qris_response',
            ]);
        });
    }
};