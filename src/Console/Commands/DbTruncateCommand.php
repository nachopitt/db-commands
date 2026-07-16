<?php

namespace Nachopitt\Database\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbTruncateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate
        {tables? : A comma-separated list of tables to truncate}
        {--s|schema= : The database schema to truncate tables from}
        {--i|ignore-foreign-key-checks : Ignore foreign key checks during truncate}
        {--c|connection= : The database connection to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate tables from database';

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
        $ignoreForeignKeyChecks = $this->option('ignore-foreign-key-checks');

        config(["database.connections.{$connection}.database" => $schemaName]);
        DB::purge($connection);

        try {
            $db = DB::connection($connection);

            if ($this->argument('tables')) {
                $tableNames = explode(',', $this->argument('tables'));
            } else {
                $tableNames = array_column($db->select('SHOW TABLES'), "Tables_in_$schemaName");
            }

            if ($ignoreForeignKeyChecks) {
                $db->statement('SET FOREIGN_KEY_CHECKS=0');
            }

            $db->statement("USE `$schemaName`");

            foreach ($tableNames as $tableName) {
                $db->statement("TRUNCATE TABLE `$tableName`");

                $this->info(sprintf('Table %s truncated successfully', $tableName));
            }

            if ($ignoreForeignKeyChecks) {
                $db->statement('SET FOREIGN_KEY_CHECKS=1');
            }
        } catch (\Exception $e) {
            $this->error("Failed to truncate tables: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
