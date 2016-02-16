<?php

namespace EveOnline\Items;

use Illuminate\Support\ServiceProvider;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;

class DetailsProvider extends ServiceProvider
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
        $this->app->singleton('eveonline.item.details', function() {
            $client = new Client();
            $eveLog = new EveLogHandler();

            return new Details($client, $eveLog);
        });

        $this->app->alias('eveonline.item.details', Details::class);
    }
}
