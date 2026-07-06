<?php

namespace Nachopitt\Database\Tests;

use Illuminate\Support\Facades\Config;

class DbTruncateCommandTest extends TestCase
{
    public function test_it_truncates_specified_tables()
    {
        $connectionMock = $this->mockConnection('mysql');

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('USE `test_db`')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('TRUNCATE TABLE `users`')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('TRUNCATE TABLE `posts`')
            ->andReturn(true);

        $this->artisan('db:truncate', ['tables' => 'users,posts'])
            ->expectsOutput('Table users truncated successfully')
            ->expectsOutput('Table posts truncated successfully')
            ->assertExitCode(0);

        $this->assertEquals('test_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_truncates_all_tables_by_default()
    {
        $connectionMock = $this->mockConnection('mysql');

        $table1 = new \stdClass();
        $table1->Tables_in_test_db = 'users';
        $table2 = new \stdClass();
        $table2->Tables_in_test_db = 'posts';

        $connectionMock->shouldReceive('select')
            ->once()
            ->with('SHOW TABLES')
            ->andReturn([$table1, $table2]);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('USE `test_db`')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('TRUNCATE TABLE `users`')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('TRUNCATE TABLE `posts`')
            ->andReturn(true);

        $this->artisan('db:truncate')
            ->expectsOutput('Table users truncated successfully')
            ->expectsOutput('Table posts truncated successfully')
            ->assertExitCode(0);

        $this->assertEquals('test_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_truncates_tables_with_custom_connection()
    {
        Config::set('database.connections.custom_conn', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'custom_db',
        ]);

        $connectionMock = $this->mockConnection('custom_conn');

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('USE `custom_db`')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('TRUNCATE TABLE `users`')
            ->andReturn(true);

        $this->artisan('db:truncate', [
            'tables' => 'users',
            '--connection' => 'custom_conn',
        ])
            ->expectsOutput('Table users truncated successfully')
            ->assertExitCode(0);

        $this->assertEquals('custom_db', Config::get('database.connections.custom_conn.database'));
    }

    public function test_it_truncates_ignoring_foreign_key_checks()
    {
        $connectionMock = $this->mockConnection('mysql');

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('SET FOREIGN_KEY_CHECKS=0')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('USE `test_db`')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('TRUNCATE TABLE `users`')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('SET FOREIGN_KEY_CHECKS=1')
            ->andReturn(true);

        $this->artisan('db:truncate', [
            'tables' => 'users',
            '--ignore-foreign-key-checks' => true,
        ])
            ->expectsOutput('Table users truncated successfully')
            ->assertExitCode(0);

        $this->assertEquals('test_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_truncates_with_custom_schema()
    {
        $connectionMock = $this->mockConnection('mysql');

        $table = new \stdClass();
        $table->Tables_in_custom_db = 'users';

        $connectionMock->shouldReceive('select')
            ->once()
            ->with('SHOW TABLES')
            ->andReturn([$table]);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('USE `custom_db`')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('TRUNCATE TABLE `users`')
            ->andReturn(true);

        $this->artisan('db:truncate', [
            '--schema' => 'custom_db',
        ])
            ->expectsOutput('Table users truncated successfully')
            ->assertExitCode(0);

        $this->assertEquals('custom_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_fails_gracefully_when_truncation_fails()
    {
        $connectionMock = $this->mockConnection('mysql');

        $connectionMock->shouldReceive('statement')
            ->once()
            ->andThrow(new \Exception('SQL Truncate Error'));

        $this->artisan('db:truncate', ['tables' => 'users'])
            ->expectsOutput('Failed to truncate tables: SQL Truncate Error')
            ->assertExitCode(1);
    }
}
