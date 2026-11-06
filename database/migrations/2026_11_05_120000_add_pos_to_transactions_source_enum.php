<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE transactions
            MODIFY source ENUM(
                'Android POS',
                'Smart EDC',
                'API',
                'Website',
                'Booth',
                'Stadium',
                'POS'
            ) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE transactions
            SET source = 'Booth'
            WHERE source = 'POS'
        ");

        DB::statement("
            ALTER TABLE transactions
            MODIFY source ENUM(
                'Android POS',
                'Smart EDC',
                'API',
                'Website',
                'Booth',
                'Stadium'
            ) NULL
        ");
    }
};
