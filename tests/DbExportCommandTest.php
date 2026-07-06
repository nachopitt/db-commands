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

        ob_start();
        $this->artisan('db:export')
            ->assertExitCode(0);
        $output = ob_get_clean();

        $this->assertStringContainsString('CREATE TABLE users (id INT);', $output);
        $this->assertEquals('test_db', Config::get('database.connections.mysql.database'));
    }

    public function test_it_exports_database_unsuccessfully()
    {
        $processMock = \Mockery::mock(Process::class);
        $processMock->shouldReceive('run')->once()->andReturn(1);
        $processMock->shouldReceive('isSuccessful')->once()->andReturn(false);
        $processMock->shouldReceive('getOutput')->once()->andReturn('');

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
            ->expectsOutput('Export failed_db database into SQL statements finished unsuccessfully!')
            ->assertExitCode(1);

        $this->assertEquals('failed_db', Config::get('database.connections.mysql.database'));
    }
}
