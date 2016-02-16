<?php

namespace EveOnline\Market\History;

use Illuminate\Support\ServiceProvider;

class MarketHistoryProvider extends ServiceProvider
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
        $this->app->singleton('eveonline.market.history', function() {
            $client = new Client();
            $eveLog = new EveLogHandler();

            return new MarketHistory($client, $eveLog);
        });

        $this->app->alias('eveonline.market.history', MarketHistory::class);
    }
}
