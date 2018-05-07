<?php

namespace Propaganistas\LaravelDisposableEmail\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Indisposable
 *
 * @method static boolean isDisposable(string $email)
 * @method static array remoteDomains()
 * @method static void flushCache()
 *
 * @package Propaganistas\LaravelDisposableEmail\Facades
 */
class Indisposable extends Facade {

    protected static function getFacadeAccessor() { return 'indisposable'; }

}