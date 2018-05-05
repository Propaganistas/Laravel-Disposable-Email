<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

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
     * Return the remainder of a string after a given value.
     * (Copy of Illuminate\Support's Str::after() method.)
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function stringAfter($subject, $search) {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Checks whether or not the given email address' domain matches one from a disposable email service.
     *
     * @param $email
     * @return bool
     */
    public function isDisposable($email) {
        // Parse the email to its top level domain.
        preg_match("/[^\.\/]+\.[^\.\/]+$/", static::stringAfter($email, '@'), $domain);

        // Just ignore this validator if the value doesn't even resemble an email or domain.
        if (count($domain) === 0) {
            return false;
        }

        return in_array($domain[0], static::$domains);
    }

}