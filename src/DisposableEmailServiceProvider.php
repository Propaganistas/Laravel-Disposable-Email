<?php

namespace Propaganistas\LaravelDisposableEmail;

use Illuminate\Support\ServiceProvider;
use Propaganistas\LaravelDisposableEmail\Console\CacheDisposableDomainsCommand;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;

class DisposableEmailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheDisposableDomainsCommand::class,
            ]);
        }

        $this->app['validator']->extend('indisposable', Indisposable::class . '@validate');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}