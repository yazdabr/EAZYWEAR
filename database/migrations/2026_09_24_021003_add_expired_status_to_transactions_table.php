<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE transactions
            MODIFY status ENUM(
                'PENDING',
                'PAID',
                'COMPLETED',
                'CANCELLED'
            ) NOT NULL DEFAULT 'PENDING'
        ");
    }
};