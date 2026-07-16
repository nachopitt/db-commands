<?php

namespace Nachopitt\Database\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbDropCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:drop
        {name? : The name of the database to drop}
        {--c|connection= : The database connection to use}
        {--force : Force the operation to run without prompting for confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop an existing database based on the database config file or the provided name';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = $this->option('connection') ?: config('database.default');
        $schemaName = $this->argument('name') ?: config("database.connections.{$connection}.database");

        $this->warn("You are about to DESTROY completely database $schemaName!");
        if ($this->option('force') || $this->confirm('Do you wish to continue?', false)) {
            config(["database.connections.{$connection}.database" => null]);
            DB::purge($connection);

            $query = "DROP DATABASE IF EXISTS $schemaName;";

            try {
                DB::connection($connection)->statement($query);
            } catch (\Exception $e) {
                $this->error("Failed to drop database: {$e->getMessage()}");

                return Command::FAILURE;
            }

            $this->info("Drop database $schemaName finished successfully!");
        } else {
            $this->info("Drop database $schemaName canceled!");
        }

        return Command::SUCCESS;
    }
}
