<?php

namespace Nachopitt\Database\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Nachopitt\Database\Console\Commands\DbExportCommand;
use Symfony\Component\Process\Process;

class DbExportCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function test_it_exports_database_successfully()
    {
        $processMock = \Mockery::mock(Process::class);
        $processMock->shouldReceive('run')->once()->andReturn(0);
        $processMock->shouldReceive('isSuccessful')->once()->andReturn(true);
        $processMock->shouldReceive('getOutput')->once()->andReturn('CREATE TABLE users (id INT);');

        $commandMock = \Mockery::mock(DbExportCommand::class . '[makeProcess]');
        $commandMock->shouldAllowMockingProtectedMethods();
        $commandMock->shouldReceive('makeProcess')
            ->once()
            ->with([
                'mysqldump', '-h', '127.0.0.1', '-u', 'root', '--password=secret', '-d', 'test_db'
            ])
            ->andReturn($processMock);

        $this->app->instance(DbExportCommand::class, $commandMock);

        DB::shouldReceive('purge')->once()->with('mysql');

        $this->artisan('db:export')
            ->expectsOutput('CREATE TABLE users (id INT);')
            ->assertExitCode(0);

        $this->assertEquals('test_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_exports_database_with_custom_connection()
    {
        Config::set('database.connections.custom_conn', [
            'driver' => 'mysql',
            'host' => '127.0.0.Custom',
            'username' => 'custom_user',
            'password' => 'custom_secret',
            'database' => 'custom_db',
        ]);

        $processMock = \Mockery::mock(Process::class);
        $processMock->shouldReceive('run')->once()->andReturn(0);
        $processMock->shouldReceive('isSuccessful')->once()->andReturn(true);
        $processMock->shouldReceive('getOutput')->once()->andReturn('CREATE TABLE users (id INT);');

        $commandMock = \Mockery::mock(DbExportCommand::class . '[makeProcess]');
        $commandMock->shouldAllowMockingProtectedMethods();
        $commandMock->shouldReceive('makeProcess')
            ->once()
            ->with([
                'mysqldump', '-h', '127.0.0.Custom', '-u', 'custom_user', '--password=custom_secret', '-d', 'custom_db'
            ])
            ->andReturn($processMock);

        $this->app->instance(DbExportCommand::class, $commandMock);

        DB::shouldReceive('purge')->once()->with('custom_conn');

        $this->artisan('db:export', ['--connection' => 'custom_conn'])
            ->expectsOutput('CREATE TABLE users (id INT);')
            ->assertExitCode(0);

        $this->assertEquals('custom_db', Config::get('database.connections.custom_conn.database'));
    }

    public function test_it_fails_gracefully_when_export_process_fails()
    {
        $processMock = \Mockery::mock(Process::class);
        $processMock->shouldReceive('run')->once()->andReturn(1);
        $processMock->shouldReceive('isSuccessful')->once()->andReturn(false);
        $processMock->shouldReceive('getErrorOutput')->once()->andReturn('mysqldump: Connection refused');

        $commandMock = \Mockery::mock(DbExportCommand::class . '[makeProcess]');
        $commandMock->shouldAllowMockingProtectedMethods();
        $commandMock->shouldReceive('makeProcess')
            ->once()
            ->with([
                'mysqldump', '-h', '127.0.0.1', '-u', 'root', '--password=secret', '-d', 'failed_db'
            ])
            ->andReturn($processMock);

        $this->app->instance(DbExportCommand::class, $commandMock);

        DB::shouldReceive('purge')->once()->with('mysql');

        $this->artisan('db:export', ['schema' => 'failed_db'])
            ->expectsOutput("Failed to export database 'failed_db'.")
            ->expectsOutput('mysqldump: Connection refused')
            ->assertExitCode(1);

        $this->assertEquals('failed_db', Config::get('database.connections.mysql.database'));
    }
}
