<?php

namespace App\Etis\EveOnline\Market\Types;

use Illuminate\Support\ServiceProvider;

use GuzzleHttp\Client;
use EveOnline\Logging\EveLogHandler;

class TypesProvider extends ServiceProvider
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
        $this->app->singleton('eveonline.market.types', function() {
            $client        = new Client();
            $eveLogHandler = new EveLogHandler();

            return new Types($client, $eveLogHandler);
        });

        $this->app->alias('eveonline.market.types', Types::class);
    }
}
