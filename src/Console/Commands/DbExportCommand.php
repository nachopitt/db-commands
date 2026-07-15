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
    protected $signature = 'db:export {schema?} {--c|connection=} {--skip-ssl}';

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
        $command = [
            'mysqldump',
            '-h', $defaultHost,
            '-u', $defaultUsername,
            '--password='.$defaultPassword,
        ];

        if ($this->option('skip-ssl')) {
            $command[] = '--skip-ssl';
        }

        $schemaName = $this->argument('schema') ?: $defaultDatabase;
        $command[] = '-d';
        $command[] = $schemaName;

        config(["database.connections.{$connection}.database" => $schemaName]);
        DB::purge($connection);

        $process = $this->makeProcess($command);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error("Failed to export database '$schemaName'.");
            $this->error($process->getErrorOutput());
            $exitCode = Command::FAILURE;
        } else {
            $this->line($process->getOutput());
            $exitCode = Command::SUCCESS;
        }

        return $exitCode;
    }

    /**
     * Create a Process instance.
     */
    protected function makeProcess(array $command): Process
    {
        return new Process($command);
    }
}
