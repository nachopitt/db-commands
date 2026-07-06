<?php

namespace Nachopitt\Database\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Nachopitt\Database\ConsoleSupportServiceProvider;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ConsoleSupportServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database configuration for testing.
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'test_db',
            'username' => 'root',
            'password' => 'secret',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);
    }

    /**
     * Mock the DB connection and purge helper.
     *
     * @param string $connection
     * @return \Mockery\MockInterface
     */
    protected function mockConnection($connection = 'mysql')
    {
        $connectionMock = \Mockery::mock();

        DB::shouldReceive('connection')
            ->with($connection)
            ->andReturn($connectionMock);

        DB::shouldReceive('purge')
            ->zeroOrMoreTimes();

        return $connectionMock;
    }
}
