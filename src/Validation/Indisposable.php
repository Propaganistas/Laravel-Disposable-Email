<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Illuminate\Cache\CacheManager;
use Exception;

class Indisposable {

    /**
     * The remote JSON source URI.
     *
     * @var string
     */
    protected $remoteUrl = 'https://rawgit.com/andreis/disposable-email-domains/master/domains.json';

    /**
     * Framework cache key.
     *
     * @var string
     */
    protected $cacheKey = 'disposable-email.domains';

    /**
     * Array of disposable email domains.
     *
     * @var array
     */
    protected $domains = [];

    /**
     * Caching service.
     *
     * @var CacheManager
     */
    protected $cache;

    /**
     * Indisposable constructor.
     *
     * @param CacheManager $cache
     */
    public function __construct(CacheManager $cache) {
        $this->cache = $cache;

        try {
            $this->domains = $this->remoteDomains();
        } catch (Exception $exception) {
            $this->domains = $this->localDomains();
        }
    }

    /**
     * Local domain array parsed and cached for optimal performance.
     *
     * @return array
     */
    public function localDomains() {
        return $this->cache->rememberForever($this->cacheKey . 'local', function() {
            return json_decode(file_get_contents(__DIR__.'/../../domains.json'), true);
        });
    }

    /**
     * Remote domain array parsed and cached for optimal performance.
     *
     * @throws Exception
     * @return array
     */
    public function remoteDomains() {
        return $this->cache->rememberForever($this->cacheKey, function() {
            $remote = file_get_contents($this->remoteUrl);

            if (! $this->isUsefulJson($remote)) {
                throw new Exception('Couldn\'t reach the remote disposable domain source.');
            }

            return json_decode($remote, true);
        });
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
        preg_match(
            "/[^\.\/]+\.[^\.\/]+$/",
            explode('@', $email, 2)[1] ?? '',
            $domain
        );

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
        $this->cache->forget($this->cacheKey);
    }

    /**
     * Grabs the current cache key.
     *
     * @return string
     */
    public function getCacheKey() {
        return $this->cacheKey;
    }

    /**
     * Updates the current source URL.
     *
     * @param $url
     * @return string;
     */
    public function setRemoteUrl($url) {
        return $this->remoteUrl = $url;
    }

    /**
     * Forcefully updates the current remote domain cache with the given array of domains.
     *
     * @param array $domains
     */
    public function setRemoteDomainsCache($domains) {
        $this->cache->forever($this->cacheKey, $domains);
    }
}