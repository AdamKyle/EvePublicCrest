<?php

namespace EveOnline\Regions;

use Illuminate\Support\ServiceProvider;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;

class RegionsProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('eveonline.regions', function() {
            $client        = new Client();
            $eveLogHandler = new EveLogHandler();

            return new Regions($client, $eveLogHandler);
        });

        $this->app->alias('eveonline.regions', Regions::class);
    }
}
