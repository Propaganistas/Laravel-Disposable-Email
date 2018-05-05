<?php

namespace Propaganistas\LaravelDisposableEmail\Facades;

use Illuminate\Support\Facades\Facade;

class Indisposable extends Facade {

    protected static function getFacadeAccessor() { return 'indisposable'; }

}