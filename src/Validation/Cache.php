<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Illuminate\Support\Facades\Cache as FrameworkCache;

class Cache {

    /**
     * The remote JSON source URI.
     *
     * @var string
     */
    public static $sourceUrl = 'https://rawgit.com/andreis/disposable-email-domains/master/domains.json';

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
    public function store($data) {
        FrameworkCache::put('laravel-disposable-email.cache', $data, 60 * 24 * 7);
    }

}