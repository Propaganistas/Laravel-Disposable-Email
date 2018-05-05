<?php

class TestCase extends Orchestra\Testbench\TestCase {

    /**
     * Laravel Disposable Email Test Package Aliases
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app) {
        return [
            'Indispensable' => 'Propaganistas\LaravelDisposableEmail\Facades\IndisposableFacade',
        ];
    }

}