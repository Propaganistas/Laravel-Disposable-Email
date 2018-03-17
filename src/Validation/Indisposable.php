<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Illuminate\Support\Str;

class Indisposable
{
    /**
     * The array of disposable domains.
     *
     * @var array
     */
    protected static $domains = [];

    /**
     * Disposable constructor.
     */
    public function __construct()
    {
        $path = base_path('storage/framework/disposable_domains.json');

        // If the list hasn't been fetched yet, fall back to defaults.
        // This ensures developers are up and running right away.
        static::$domains = json_decode(file_get_contents(
            file_exists($path) ? $path : (__DIR__ . '/../../domains.json')
        ), true);
    }

    /**
     * Validates whether an email address does not originate from a disposable email service.
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        // Parse the email to its top level domain.
        preg_match("/[^\.\/]+\.[^\.\/]+$/", Str::after($value, '@'), $domain);

        // Just ignore this validator if the value doesn't even resemble an email or domain.
        if (count($domain) === 0) {
            return true;
        }

        return ! in_array($domain[0], static::$domains);
    }
}