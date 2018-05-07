<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Illuminate\Support\Facades\Cache as FrameworkCache;
use Illuminate\Support\Facades\Log;
use Exception;

class Indisposable {

    /**
     * The remote JSON source URI.
     *
     * @var string
     */
    protected $sourceUrl = 'https://rawgit.com/andreis/disposable-email-domains/master/domains.json';

    /**
     * Framework cache key.
     *
     * @var string
     */
    protected $cacheKey = 'laravel-disposable-email.cache';

    /**
     * Array of disposable email domains.
     *
     * @var array
     */
    protected $domains = [];

    /**
     * Indisposable constructor.
     */
    public function __construct() {
        try {
            $this->domains = $this->remoteDomains();
        } catch (Exception $exception) {
            Log::warning($exception->getMessage());
            $this->domains = $this->localDomains();
        }
    }

    /**
     * Local domain array parsed and cached for optimal performance.
     *
     * @return array
     */
    protected function localDomains() {
        return FrameworkCache::rememberForever($this->cacheKey . 'local', function() {
            return json_decode(file_get_contents(__DIR__.'/../../domains.json'), true);
        });
    }

    /**
     * Remote domain array parsed and cached for optimal performance.
     *
     * @throws Exception
     * @return array
     */
    protected function remoteDomains() {
        return FrameworkCache::rememberForever($this->cacheKey, function() {
            $remote = file_get_contents($this->sourceUrl);

            if (! $this->isUsefulJson($remote)) {
                throw new Exception('Couldn\'t reach the remote disposable domain source.');
            }

            return json_decode($remote, true);
        });
    }

    /**
     * Return the remainder of a string after a given value.
     * (Copy of Illuminate\Support's Str::after() method.)
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    protected function stringAfter($subject, $search) {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Check whether the given JSON data is useful.
     *
     * @param string $data
     * @return bool
     */
    protected function isUsefulJson($data) {
        $data = json_decode($data, true);

        return json_last_error() === JSON_ERROR_NONE && ! empty($data);
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

        return in_array($domain[0], $this->domains);
    }

    /**
     * Flushes the remote domains cache;
     */
    public function flushCache() {
        FrameworkCache::forget($this->cacheKey);
    }
}