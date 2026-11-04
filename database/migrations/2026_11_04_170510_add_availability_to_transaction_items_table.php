<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->string('availability', 20)->nullable()->after('product_variant_id');
        });

        DB::statement("
            UPDATE transaction_items AS ti
            INNER JOIN transaction_fulfillments AS tf
                ON tf.id = ti.transaction_fulfillment_id
            SET ti.availability = tf.group_type
            WHERE ti.availability IS NULL
              AND tf.group_type IN ('ready', 'pre_order')
        ");
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('availability');
        });
    }
};
