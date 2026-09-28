<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('courier', 100)->nullable()->after('shipping_method');
            $table->string('tracking_number', 100)->nullable()->after('courier');
        });

        DB::statement("
            ALTER TABLE transactions
            MODIFY status ENUM(
                'PENDING',
                'PAID',
                'COMPLETED',
                'CANCELLED',
                'EXPIRED',
                'ORDER_PROCESSING',
                'ORDER_SHIPPED',
                'ORDER_COMPLETED'
            ) NOT NULL DEFAULT 'PENDING'
        ");
    }

    public function down(): void
    {
        $hasNewStatuses = DB::table('transactions')
            ->whereIn('status', [
                'ORDER_PROCESSING',
                'ORDER_SHIPPED',
                'ORDER_COMPLETED',
            ])
            ->exists();

        if ($hasNewStatuses) {
            throw new RuntimeException(
                'Cannot rollback shipping status migration while ORDER_PROCESSING, ORDER_SHIPPED, or ORDER_COMPLETED transactions exist.'
            );
        }

        DB::statement("
            ALTER TABLE transactions
            MODIFY status ENUM(
                'PENDING',
                'PAID',
                'COMPLETED',
                'CANCELLED',
                'EXPIRED'
            ) NOT NULL DEFAULT 'PENDING'
        ");

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_number',
                'courier',
            ]);
        });
    }
};