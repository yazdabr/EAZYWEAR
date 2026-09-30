<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConcurrencyProbeTest extends TestCase
{
    public function test_two_mysql_connections_use_independent_pdo_connections(): void
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

        $this->assertNotSame(
            $connectionA->getPdo(),
            $connectionB->getPdo()
        );

        $this->assertSame(
            config('database.connections.mysql.database'),
            $connectionB->getDatabaseName()
        );
    }
}