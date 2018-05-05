<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Illuminate\Support\Str;

class Indisposable {

    /**
     * Array of disposable email domains.
     *
     * @var array
     */
    protected static $domains = [];

    /**
     * Indisposable constructor.
     */
    public function __construct() {
        static::$domains = Cache::fetchOrUpdate();
    }

    /**
     * Checks whether or not the given email address' domain matches one from a disposable email service.
     *
     * @param $email
     * @return bool
     */
    public static function isDisposable($email) {
        // Parse the email to its top level domain.
        preg_match("/[^\.\/]+\.[^\.\/]+$/", Str::after($email, '@'), $domain);

        // Just ignore this validator if the value doesn't even resemble an email or domain.
        if (count($domain) === 0) {
            return false;
        }

        return in_array($domain[0], static::$domains);
    }

}