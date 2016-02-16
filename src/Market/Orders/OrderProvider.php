<?php

namespace App\Etis\EveOnline\Market\Orders;

use Illuminate\Support\ServiceProvider;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;

class OrderProvider extends ServiceProvider
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
        $this->app->singleton('eveonline.region.orders', function() {
            $client        = new Client();
            $eveLogHandler = new EveLogHandler();
            
            return new Order($client, $eveLogHandler);
        });

        $this->app->alias('eveonline.region.orders', Order::class);
    }
}
