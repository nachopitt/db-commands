<?php

namespace Nachopitt\Database\Tests;

use Illuminate\Support\Facades\Config;

class DbCreateCommandTest extends TestCase
{
    public function test_it_creates_database_with_default_config_name()
    {
        $connectionMock = $this->mockConnection('mysql');
        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('CREATE DATABASE IF NOT EXISTS `test_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;')
            ->andReturn(true);

        $this->artisan('db:create')
            ->expectsOutput('Create database test_db finished successfully!')
            ->assertExitCode(0);

        $this->assertEquals('test_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_creates_database_with_custom_name()
    {
        $connectionMock = $this->mockConnection('mysql');
        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('CREATE DATABASE IF NOT EXISTS `custom_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;')
            ->andReturn(true);

        $this->artisan('db:create', ['name' => 'custom_db'])
            ->expectsOutput('Create database custom_db finished successfully!')
            ->assertExitCode(0);

        $this->assertEquals('custom_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_creates_database_with_custom_connection()
    {
        Config::set('database.connections.custom_conn', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'custom_db',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $connectionMock = $this->mockConnection('custom_conn');
        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('CREATE DATABASE IF NOT EXISTS `custom_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;')
            ->andReturn(true);

        $this->artisan('db:create', ['--connection' => 'custom_conn'])
            ->expectsOutput('Create database custom_db finished successfully!')
            ->assertExitCode(0);

        $this->assertEquals('custom_db', Config::get('database.connections.custom_conn.database'));
    }

    public function test_it_fails_gracefully_when_database_creation_fails()
    {
        $connectionMock = $this->mockConnection('mysql');
        $connectionMock->shouldReceive('statement')
            ->once()
            ->andThrow(new \Exception('SQL Connection Error'));

        $this->artisan('db:create')
            ->expectsOutput('Failed to create database: SQL Connection Error')
            ->assertExitCode(1);
    }
}
