<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedInteger('label_print_count')->default(0);
        });

        Schema::table('transaction_fulfillments', function (Blueprint $table) {
            $table->unsignedInteger('label_print_count')->default(0);
        });

        Schema::table('transaction_shipments', function (Blueprint $table) {
            $table->unsignedInteger('label_print_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('label_print_count');
        });

        Schema::table('transaction_fulfillments', function (Blueprint $table) {
            $table->dropColumn('label_print_count');
        });

        Schema::table('transaction_shipments', function (Blueprint $table) {
            $table->dropColumn('label_print_count');
        });
    }
};