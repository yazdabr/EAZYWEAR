<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('o2o_payment_code', 64)->nullable()->after('qris_response');
            $table->string('o2o_request_id', 128)->nullable()->after('o2o_payment_code');
            $table->dateTime('o2o_expired_at')->nullable()->after('o2o_request_id');
            $table->string('o2o_how_to_pay_url', 500)->nullable()->after('o2o_expired_at');
            $table->json('o2o_response')->nullable()->after('o2o_how_to_pay_url');
            $table->index('o2o_payment_code');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['o2o_payment_code']);
            $table->dropColumn([
                'o2o_payment_code',
                'o2o_request_id',
                'o2o_expired_at',
                'o2o_how_to_pay_url',
                'o2o_response',
            ]);
        });
    }
};
