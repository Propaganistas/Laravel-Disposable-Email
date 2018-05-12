<?php

namespace Propaganistas\LaravelDisposableEmail\Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase {

    /**
     * Laravel Disposable Email Test Package Service Providers
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app) {
        return ['Propaganistas\LaravelDisposableEmail\DisposableEmailServiceProvider'];
    }

    /**
     * Laravel Disposable Email Test Package Aliases
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app) {
        return [
            'Indisposable' => 'Propaganistas\LaravelDisposableEmail\Facades\Indisposable',
        ];
    }

}