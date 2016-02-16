<?php

namespace EveOnline\Market\Prices;

use Illuminate\Support\ServiceProvider;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;

class PricesProvider extends ServiceProvider
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
        $this->app->singleton('eveonline.market.prices', function() {
            $client        = new Client();
            $eveLogHandler = new EveLogHandler();

            return new Prices($client, $eveLogHandler);
        });

        $this->app->alias('eveonline.market.prices', Prices::class);
    }
}
