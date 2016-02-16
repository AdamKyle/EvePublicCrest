<?php

namespace App\Etis\EveOnline\Market\Orders;

use Illuminate\Support\ServiceProvider;

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
            return new Order();
        });

        $this->app->alias('eveonline.region.orders', Order::class);
    }
}
