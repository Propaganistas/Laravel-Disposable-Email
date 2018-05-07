<?php

namespace Propaganistas\LaravelDisposableEmail\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Indisposable
 *
 * @method static boolean isDisposable(string $email)
 * @method static array remoteDomains()
 * @method static array localDomains()
 * @method static void flushCache()
 * @method static string getCacheKey()
 * @method static string setRemoteUrl(string $url)
 * @method static void setRemoteDomainsCache(array $domains)
 *
 * @package Propaganistas\LaravelDisposableEmail\Facades
 */
class Indisposable extends Facade {

    protected static function getFacadeAccessor() { return 'indisposable'; }

}