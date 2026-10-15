<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role
            ENUM('super_admin', 'management', 'production', 'reports')
            NULL DEFAULT 'management'
        ");
    }

    public function down(): void
    {
        $reportsCount = DB::table('users')
            ->where('role', 'reports')
            ->count();

        if ($reportsCount > 0) {
            throw new RuntimeException(
                "Rollback dibatalkan: masih ada {$reportsCount} akun dengan role reports."
            );
        }

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role
            ENUM('super_admin', 'management', 'production')
            NULL DEFAULT 'management'
        ");
    }
};
