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
    public static function fetchRemoteSource() {
        return file_get_contents(static::$sourceUrl);
    }

    /**
     * Fetches the domains source from the local package source.
     *
     * @return bool|string
     */
    public static function fetchLocalSource() {
        return file_get_contents(__DIR__.'/../../domains.json');
    }

    /**
     * Decodes the given JSON source string.
     *
     * @param $source
     * @return mixed
     */
    protected static function decodeSource($source) {
        return json_decode($source, true);
    }

    /**
     * Stores the given data in the framework Cache.
     *
     * @param array|string $data
     */
    public static function store($data) {
        if (is_string($data)) {
            $data = static::decodeSource($data);
        }

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

    /**
     * Flushes the current disposable email cache.
     *
     * @return bool
     */
    public static function flush() {
        return FrameworkCache::forget(static::$cacheKey);
    }

    /**
     * Updates the domain cache.
     * Uses the locally stored domain file as a fallback.
     */
    public static function update() {
        $source = static::fetchRemoteSource();

        if (!$source) {
            $source = static::fetchLocalSource();
        }

        static::store($source);
    }

    /**
     * Fetches the current Disposable Email cache.
     * If no cache is available, we'll update the cache and try again.
     *
     * @return array
     */
    public static function fetchOrUpdate() {
        if ($output = static::fetch()) {
            return $output;
        }

        static::update();

        return static::fetch();
    }

}