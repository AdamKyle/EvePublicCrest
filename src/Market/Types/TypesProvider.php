<?php

namespace App\Etis\EveOnline\Market\Types;

use Illuminate\Support\ServiceProvider;

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
            return new Types();
        });

        $this->app->alias('eveonline.market.types', Types::class);
    }
}
