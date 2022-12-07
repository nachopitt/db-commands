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
    protected $signature = 'db:drop {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop an existing MySQL database based on the database config file or the provided name';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $schemaName = $this->argument('name') ?: config("database.connections.mysql.database");

        $this->warn("You are about to DESTROY completely database $schemaName!");
        if ($this->confirm('Do you wish to continue?', false)) {
            config(["database.connections.mysql.database" => null]);

            $query = "DROP DATABASE IF EXISTS $schemaName;";
            DB::statement($query);

            $this->info("Drop database $schemaName finished successfully!");
        }
        else {
            $this->info("Drop database $schemaName canceled!");
        }

        return Command::SUCCESS;
    }
}
