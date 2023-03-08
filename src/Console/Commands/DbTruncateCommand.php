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
    protected $signature = 'db:truncate {tables?} {--s|schema=} {--i|ignore-foreign-key-checks}';

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
        $defaultDatabase = config("database.connections.mysql.database");
        $schemaName = $this->option('schema') ?: $defaultDatabase;
        $ignoreForeignKeyChecks = $this->option('ignore-foreign-key-checks');

        if ($this->argument('tables')) {
            $tableNames = explode(',', $this->argument('tables'));
        }
        else {
            $tableNames = array_column(DB::select('SHOW TABLES'), "Tables_in_$schemaName");
        }

        config(["database.connections.mysql.database" => $schemaName]);

        if ($ignoreForeignKeyChecks) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        DB::statement("USE `$schemaName`");

        foreach ($tableNames as $tableName) {
            DB::statement("TRUNCATE TABLE `$tableName`");

            $this->info(sprintf('Table %s truncated successfully', $tableName));
        }

        if ($ignoreForeignKeyChecks) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return Command::SUCCESS;
    }
}
