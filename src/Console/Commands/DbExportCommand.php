<?php

namespace Nachopitt\Database\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DbExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export {schema?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export an existing MySQL database into SQL statements';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $defaultHost = config("database.connections.mysql.host");
        $defaultUsername = config("database.connections.mysql.username");
        $defaultDatabase = config("database.connections.mysql.database");

        $schemaName = $this->argument('schema') ?: $defaultDatabase;

        config(["database.connections.mysql.database" => $schemaName]);

        $process = new Process([
            'mysqldump', '-h', $defaultHost, '-u', $defaultUsername, '-p', '-d', $schemaName
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
}
