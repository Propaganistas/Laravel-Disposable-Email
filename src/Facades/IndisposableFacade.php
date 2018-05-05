<?php

namespace Propaganistas\LaravelDisposableEmail\Facades;

use Illuminate\Support\Facades\Facade;

class IndisposableFacade extends Facade {

    protected static function getFacadeAccessor() { return 'indisposable'; }

}