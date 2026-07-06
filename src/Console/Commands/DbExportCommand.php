<?php

namespace Nachopitt\Database\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class DbExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export {schema?} {--c|connection=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export an existing database into SQL statements';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = $this->option('connection') ?: config('database.default');
        $defaultHost = config("database.connections.{$connection}.host");
        $defaultUsername = config("database.connections.{$connection}.username");
        $defaultDatabase = config("database.connections.{$connection}.database");
        $defaultPassword = config("database.connections.{$connection}.password");

        $schemaName = $this->argument('schema') ?: $defaultDatabase;

        config(["database.connections.{$connection}.database" => $schemaName]);
        DB::purge($connection);

        $process = $this->makeProcess([
            'mysqldump', '-h', $defaultHost, '-u', $defaultUsername, "--password=$defaultPassword", '-d', $schemaName
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->info("Export $schemaName database into SQL statements finished unsuccessfully!");
            $exitCode = Command::FAILURE;
        }
        else {
            $exitCode = Command::SUCCESS;
        }

        echo $process->getOutput();

        return $exitCode;
    }

    /**
     * Create a Process instance.
     *
     * @param array $command
     * @return \Symfony\Component\Process\Process
     */
    protected function makeProcess(array $command): Process
    {
        return new Process($command);
    }
}
