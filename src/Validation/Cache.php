<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

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

}