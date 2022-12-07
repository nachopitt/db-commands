<?php

namespace Nachopitt\Database;

class ConsoleSupportServiceProvider extends \Illuminate\Foundation\Providers\ConsoleSupportServiceProvider
{
    public function __construct($app)
    {
        $this->providers = [ArtisanServiceProvider::class];
        parent::__construct($app);
    }
}
