<?php

namespace Propaganistas\LaravelDisposableEmail;

use Illuminate\Support\ServiceProvider;
use Propaganistas\LaravelDisposableEmail\Console\CacheDisposableDomainsCommand;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;
use Propaganistas\LaravelDisposableEmail\Validation\IndisposableValidation;

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

        $this->app['validator']->extend(
            'indisposable',
            IndisposableValidation::class . '@validate',
            IndisposableValidation::$errorMessage
        );
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('indisposable', function() {
            return new Indisposable($this->app['cache']);
        });
    }
}