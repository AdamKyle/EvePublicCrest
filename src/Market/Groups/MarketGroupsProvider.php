<?php

namespace EveOnline\Market\Groups;

use Illuminate\Support\ServiceProvider;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;

class MarketGroupsProvider extends ServiceProvider
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
        $this->app->singleton('eveonline.market.groups', function() {
            $client        = new Client();
            $eveLoghandler = new EveLogHandler();
            
            return new Groups($client, $eveLoghandler);
        });

        $this->app->alias('eveonline.market.groups', Groups::class);
    }
}
