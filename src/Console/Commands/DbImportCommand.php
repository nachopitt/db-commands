<?php

namespace Nachopitt\Database\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DbImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:import {file?} {--s|schema=} {--i|ignore-foreign-key-checks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a SQL file into an existing MySQL database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $defaultDatabase = config("database.connections.mysql.database");
        $schemaName = $this->option('schema') ?: $defaultDatabase;
        $sqlImportFile = $this->argument('file') ?: "database_model/$defaultDatabase.sql";
        $ignoreForeignKeyChecks = $this->option('ignore-foreign-key-checks');

        $sqlImportFileContents = File::get($sqlImportFile);

        config(["database.connections.mysql.database" => $schemaName]);

        if ($ignoreForeignKeyChecks) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        DB::statement("USE `$schemaName`");

        DB::unprepared($sqlImportFileContents);

        if ($ignoreForeignKeyChecks) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info("Import SQL file $sqlImportFile into $schemaName database finished successfully!");

        return Command::SUCCESS;
    }
}
