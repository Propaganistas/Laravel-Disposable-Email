<?php

namespace Propaganistas\LaravelDisposableEmail\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    protected $storagePath = __DIR__.'/domains.json';

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('disposable-email.storage', $this->storagePath);
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->disposable()->flushSource();
        $this->disposable()->flushCache();

        parent::tearDown();
    }

    /**
     * Package Service Providers
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Propaganistas\LaravelDisposableEmail\DisposableEmailServiceProvider'];
    }

    /**
     * Package Aliases
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return ['Indisposable' => 'Propaganistas\LaravelDisposableEmail\Facades\DisposableDomains'];
    }

    /**
     * @return \Propaganistas\LaravelDisposableEmail\DisposableDomains
     */
    protected function disposable()
    {
        return $this->app['disposable_email.domains'];
    }
}