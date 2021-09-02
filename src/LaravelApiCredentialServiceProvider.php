<?php

namespace Adsy2010\LaravelApiCredentials;

use Illuminate\Support\ServiceProvider;

class LaravelApiCredentialServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
         $this->publishes([__DIR__.'/database/migrations' => database_path('migrations')], 'migrations');
    }
}


