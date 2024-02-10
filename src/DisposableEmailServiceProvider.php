<?php

namespace Propaganistas\LaravelDisposableEmail;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use Propaganistas\LaravelDisposableEmail\Console\UpdateDisposableDomainsCommand;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;

class DisposableEmailServiceProvider extends ServiceProvider
{
    /**
     * The config source path.
     *
     * @var string
     */
    protected $config = __DIR__.'/../config/disposable-email.php';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(UpdateDisposableDomainsCommand::class);
        }

        $this->publishes([
            $this->config => config_path('disposable-email.php'),
        ], 'laravel-disposable-email');

        $this->callAfterResolving('validator', function (Factory $validator) {
            $validator->extend('indisposable', Indisposable::class.'@validate', Indisposable::$errorMessage);
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->config, 'disposable-email');

        $this->app->singleton('disposable_email.domains', function ($app) {
            // Only build and pass the requested cache store if caching is enabled.
            if ($app['config']['disposable-email.cache.enabled']) {
                $store = $app['config']['disposable-email.cache.store'];
                $cache = $app['cache']->store($store == 'default' ? $app['config']['cache.default'] : $store);
            }

            $instance = new DisposableDomains($cache ?? null);

            $instance->setStoragePath($app['config']['disposable-email.storage']);
            $instance->setCacheKey($app['config']['disposable-email.cache.key']);
            $instance->setWhitelist($app['config']['disposable-email.whitelist']);

            return $instance->bootstrap();
        });

        $this->app->alias('disposable_email.domains', DisposableDomains::class);
    }
}
