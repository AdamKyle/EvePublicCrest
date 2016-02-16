<?php

namespace App\Etis\EveOnline\Market\Prices;

use Illuminate\Support\ServiceProvider;

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
        $this->app->singleton('eveonline.marketprices', function() {
            return new Prices();
        });

        $this->app->alias('eveonline.marketprices', Prices::class);
    }
}
