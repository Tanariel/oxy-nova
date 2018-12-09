<?php

namespace Oxygencms\OxyNova\Providers;

use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the package's Nova resources.
     *
     * @return void
     */
    protected function resources()
    {
        $resources = config('oxygen.nova_resources');

        Nova::resources([
            $resources['phrase'],
        ]);
    }
}
