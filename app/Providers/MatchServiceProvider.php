<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MatchServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Services\Interfaces\MatchServiceInterface',
            'App\Services\MatchService');
    }
}
