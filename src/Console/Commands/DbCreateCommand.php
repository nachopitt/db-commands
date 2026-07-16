<?php

namespace Nachopitt\Database\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create
        {name? : The name of the database to create}
        {--c|connection= : The database connection to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new database based on the database config file or the provided name';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = $this->option('connection') ?: config('database.default');
        $schemaName = $this->argument('name') ?: config("database.connections.{$connection}.database");
        $charset = config("database.connections.{$connection}.charset", 'utf8mb4');
        $collation = config("database.connections.{$connection}.collation", 'utf8mb4_unicode_ci');

        config(["database.connections.{$connection}.database" => null]);
        DB::purge($connection);

        $query = "CREATE DATABASE IF NOT EXISTS `$schemaName` CHARACTER SET $charset COLLATE $collation;";

        try {
            DB::connection($connection)->statement($query);
        } catch (\Exception $e) {
            $this->error("Failed to create database: {$e->getMessage()}");

            return Command::FAILURE;
        }

        config(["database.connections.{$connection}.database" => $schemaName]);
        DB::purge($connection);

        $this->info("Create database $schemaName finished successfully!");

        return Command::SUCCESS;
    }
}
