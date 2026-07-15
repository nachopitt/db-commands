<?php

namespace Nachopitt\Database\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class DbImportCommandTest extends TestCase
{
    public function test_it_imports_sql_file_with_defaults()
    {
        $connectionMock = $this->mockConnection('mysql');

        File::shouldReceive('get')
            ->once()
            ->with('database_model/test_db.sql')
            ->andReturn('CREATE TABLE test_table (id INT);');

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('USE `test_db`')
            ->andReturn(true);

        $connectionMock->shouldReceive('unprepared')
            ->once()
            ->with('CREATE TABLE test_table (id INT);')
            ->andReturn(true);

        $this->artisan('db:import')
            ->expectsOutput('Import SQL file database_model/test_db.sql into test_db database finished successfully!')
            ->assertExitCode(0);

        $this->assertEquals('test_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_imports_sql_file_with_custom_schema_and_file()
    {
        $connectionMock = $this->mockConnection('mysql');

        File::shouldReceive('get')
            ->once()
            ->with('custom_folder/dump.sql')
            ->andReturn('CREATE TABLE custom_table (id INT);');

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('USE `custom_db`')
            ->andReturn(true);

        $connectionMock->shouldReceive('unprepared')
            ->once()
            ->with('CREATE TABLE custom_table (id INT);')
            ->andReturn(true);

        $this->artisan('db:import', [
            'file' => 'custom_folder/dump.sql',
            '--schema' => 'custom_db',
        ])
            ->expectsOutput('Import SQL file custom_folder/dump.sql into custom_db database finished successfully!')
            ->assertExitCode(0);

        $this->assertEquals('custom_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_imports_sql_file_with_custom_connection()
    {
        Config::set('database.connections.custom_conn', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'custom_db',
        ]);

        $connectionMock = $this->mockConnection('custom_conn');

        File::shouldReceive('get')
            ->once()
            ->with('database_model/custom_db.sql')
            ->andReturn('CREATE TABLE test_table (id INT);');

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('USE `custom_db`')
            ->andReturn(true);

        $connectionMock->shouldReceive('unprepared')
            ->once()
            ->with('CREATE TABLE test_table (id INT);')
            ->andReturn(true);

        $this->artisan('db:import', ['--connection' => 'custom_conn'])
            ->expectsOutput('Import SQL file database_model/custom_db.sql into custom_db database finished successfully!')
            ->assertExitCode(0);

        $this->assertEquals('custom_db', Config::get('database.connections.custom_conn.database'));
    }

    public function test_it_imports_sql_file_ignoring_foreign_key_checks()
    {
        $connectionMock = $this->mockConnection('mysql');

        File::shouldReceive('get')
            ->once()
            ->with('database_model/test_db.sql')
            ->andReturn('CREATE TABLE test_table (id INT);');

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('SET FOREIGN_KEY_CHECKS=0')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('USE `test_db`')
            ->andReturn(true);

        $connectionMock->shouldReceive('unprepared')
            ->once()
            ->with('CREATE TABLE test_table (id INT);')
            ->andReturn(true);

        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('SET FOREIGN_KEY_CHECKS=1')
            ->andReturn(true);

        $this->artisan('db:import', [
            '--ignore-foreign-key-checks' => true,
        ])
            ->expectsOutput('Import SQL file database_model/test_db.sql into test_db database finished successfully!')
            ->assertExitCode(0);

        $this->assertEquals('test_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_fails_gracefully_when_sql_file_is_missing()
    {
        File::shouldReceive('get')
            ->once()
            ->with('database_model/test_db.sql')
            ->andThrow(new FileNotFoundException);

        $this->artisan('db:import')
            ->expectsOutput('SQL import file not found at: database_model/test_db.sql')
            ->assertExitCode(1);
    }

    public function test_it_fails_gracefully_when_db_statement_fails()
    {
        $connectionMock = $this->mockConnection('mysql');

        File::shouldReceive('get')
            ->once()
            ->with('database_model/test_db.sql')
            ->andReturn('CREATE TABLE test_table (id INT);');

        $connectionMock->shouldReceive('statement')
            ->once()
            ->andThrow(new \Exception('SQL Import Error'));

        $this->artisan('db:import')
            ->expectsOutput('Failed to import SQL file: SQL Import Error')
            ->assertExitCode(1);
    }
}
