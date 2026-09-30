<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseRowLockProbeTest extends TestCase
{
    public function test_mysql_for_update_lock_blocks_second_connection(): void
    {
        Config::set('database.connections.mysql_concurrency', [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => config('database.connections.mysql.database'),
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'unix_socket' => config('database.connections.mysql.unix_socket'),
            'charset' => config('database.connections.mysql.charset'),
            'collation' => config('database.connections.mysql.collation'),
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        $connectionA = DB::connection('mysql');
        $connectionB = DB::connection('mysql_concurrency');

        $inventoryId = DB::table('inventories')
            ->orderBy('id')
            ->value('id');

        $this->assertNotNull($inventoryId);

        $connectionA->beginTransaction();

        try {
            $lockedRow = $connectionA
                ->table('inventories')
                ->where('id', $inventoryId)
                ->lockForUpdate()
                ->first();

            $this->assertNotNull($lockedRow);

            $connectionB->statement('SET SESSION innodb_lock_wait_timeout = 1');
            $connectionB->beginTransaction();

            try {
                $blocked = false;

                try {
                    $connectionB
                        ->table('inventories')
                        ->where('id', $inventoryId)
                        ->lockForUpdate()
                        ->first();
                } catch (\Throwable $e) {
                    $blocked = true;
                }

                $this->assertTrue(
                    $blocked,
                    'Connection B seharusnya tertahan/gagal memperoleh lock karena Connection A masih memegang FOR UPDATE lock.'
                );
            } finally {
                if ($connectionB->transactionLevel() > 0) {
                    $connectionB->rollBack();
                }
            }
        } finally {
            if ($connectionA->transactionLevel() > 0) {
                $connectionA->rollBack();
            }

            $connectionA->disconnect();
            $connectionB->disconnect();
        }
    }
}