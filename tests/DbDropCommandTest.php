<?php

namespace Nachopitt\Database\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class DbDropCommandTest extends TestCase
{
    public function test_it_cancels_dropping_database()
    {
        // Replace DB facade with a mock that doesn't expect connection or statement calls.
        DB::shouldReceive('connection')->never();
        DB::shouldReceive('purge')->never();

        $this->artisan('db:drop')
            ->expectsOutput('You are about to DESTROY completely database test_db!')
            ->expectsConfirmation('Do you wish to continue?', 'no')
            ->expectsOutput('Drop database test_db canceled!')
            ->assertExitCode(0);
    }

    public function test_it_drops_default_database_when_confirmed()
    {
        $connectionMock = $this->mockConnection('mysql');
        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('DROP DATABASE IF EXISTS test_db;')
            ->andReturn(true);

        $this->artisan('db:drop')
            ->expectsOutput('You are about to DESTROY completely database test_db!')
            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->expectsOutput('Drop database test_db finished successfully!')
            ->assertExitCode(0);
    }

    public function test_it_drops_custom_database_when_confirmed()
    {
        $connectionMock = $this->mockConnection('mysql');
        $connectionMock->shouldReceive('statement')
            ->once()
            ->with('DROP DATABASE IF EXISTS custom_db;')
            ->andReturn(true);

        $this->artisan('db:drop', ['name' => 'custom_db'])
            ->expectsOutput('You are about to DESTROY completely database custom_db!')
            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->expectsOutput('Drop database custom_db finished successfully!')
            ->assertExitCode(0);
    }

    public function test_it_fails_gracefully_when_database_dropping_fails()
    {
        $connectionMock = $this->mockConnection('mysql');
        $connectionMock->shouldReceive('statement')
            ->once()
            ->andThrow(new \Exception('SQL Connection Error'));

        $this->artisan('db:drop')
            ->expectsOutput('You are about to DESTROY completely database test_db!')
            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->expectsOutput('Failed to drop database: SQL Connection Error')
            ->assertExitCode(1);
    }
}
