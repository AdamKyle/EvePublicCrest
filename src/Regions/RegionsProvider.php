<?php

namespace App\Etis\EveOnline\Regions;

use Illuminate\Support\ServiceProvider;

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
            return new Regions();
        });

        $this->app->alias('eveonline.regions', Regions::class);
    }
}
