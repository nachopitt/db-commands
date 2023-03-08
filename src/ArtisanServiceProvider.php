<?php

namespace Nachopitt\Database;

use Nachopitt\Database\Console\Commands\DbCreateCommand;
use Nachopitt\Database\Console\Commands\DbDropCommand;
use Nachopitt\Database\Console\Commands\DbExportCommand;
use Nachopitt\Database\Console\Commands\DbImportCommand;
use Nachopitt\Database\Console\Commands\DbTruncateCommand;

class ArtisanServiceProvider extends \Illuminate\Foundation\Providers\ArtisanServiceProvider
{
    public function __construct($app)
    {
        $this->commands = [
            'DbCreate' => DbCreateCommand::class,
            'DbDrop' => DbDropCommand::class,
            'DbImport' => DbImportCommand::class,
            'DbExport' => DbExportCommand::class,
            'DbTruncate' => DbTruncateCommand::class,
        ];
        $this->devCommands = [];
        parent::__construct($app);
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerDbCreateCommand()
    {
        $this->app->singleton(DbCreateCommand::class, function ($app) {
            return new DbCreateCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerDbDropCommand()
    {
        $this->app->singleton(DbDropCommand::class, function ($app) {
            return new DbDropCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerDbImportCommand()
    {
        $this->app->singleton(DbImportCommand::class, function ($app) {
            return new DbImportCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerDbExportCommand()
    {
        $this->app->singleton(DbExportCommand::class, function ($app) {
            return new DbExportCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerDbTruncateCommand()
    {
        $this->app->singleton(DbTruncateCommand::class, function ($app) {
            return new DbTruncateCommand;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array_values($this->commands);
    }
}
