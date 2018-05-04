<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Illuminate\Support\Facades\Cache as FrameworkCache;

class Cache {

    /**
     * The remote JSON source URI.
     *
     * @var string
     */
    protected static $sourceUrl = 'https://rawgit.com/andreis/disposable-email-domains/master/domains.json';

    /**
     * Framework cache key.
     *
     * @var string
     */
    protected static $cacheKey = 'laravel-disposable-email.cache';

    /**
     * Fetch new data from the source URI.
     *
     * @return string
     */
    public static function fetchSource() {
        return file_get_contents(static::$sourceUrl);
    }

    /**
     * Stores the given data in the framework Cache.
     *
     * @param $data
     */
    public static function store($data) {
        FrameworkCache::put(static::$cacheKey, $data, 60 * 24 * 7);
    }

    /**
     * Fetches the current disposable email cache.
     *
     * @return mixed
     */
    public static function fetch() {
        return FrameworkCache::get(static::$cacheKey, []);
    }

}