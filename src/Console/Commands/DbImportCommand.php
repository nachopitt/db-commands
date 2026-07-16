<?php

namespace Nachopitt\Database\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DbImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:import
        {file? : The path to the SQL file to import}
        {--s|schema= : The database schema to import into}
        {--i|ignore-foreign-key-checks : Ignore foreign key checks during import}
        {--c|connection= : The database connection to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a SQL file into an existing database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = $this->option('connection') ?: config('database.default');
        $defaultDatabase = config("database.connections.{$connection}.database");
        $schemaName = $this->option('schema') ?: $defaultDatabase;
        $sqlImportFile = $this->argument('file') ?: "database_model/$defaultDatabase.sql";
        $ignoreForeignKeyChecks = $this->option('ignore-foreign-key-checks');

        try {
            $sqlImportFileContents = File::get($sqlImportFile);
        } catch (FileNotFoundException $e) {
            $this->error("SQL import file not found at: {$sqlImportFile}");

            return Command::FAILURE;
        }

        config(["database.connections.{$connection}.database" => $schemaName]);
        DB::purge($connection);

        try {
            $db = DB::connection($connection);

            if ($ignoreForeignKeyChecks) {
                $db->statement('SET FOREIGN_KEY_CHECKS=0');
            }

            $db->statement("USE `$schemaName`");

            $db->unprepared($sqlImportFileContents);

            if ($ignoreForeignKeyChecks) {
                $db->statement('SET FOREIGN_KEY_CHECKS=1');
            }
        } catch (\Exception $e) {
            $this->error("Failed to import SQL file: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $this->info("Import SQL file $sqlImportFile into $schemaName database finished successfully!");

        return Command::SUCCESS;
    }
}
