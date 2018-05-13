<?php

namespace Propaganistas\LaravelDisposableEmail;

use ErrorException;
use Illuminate\Contracts\Cache\Repository as Cache;
use Propaganistas\LaravelDisposableEmail\Traits\ParsesJson;

class DisposableDomains
{
    use ParsesJson;

    /**
     * The storage path to retrieve from and save to.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * Array of retrieved disposable domains.
     *
     * @var array
     */
    protected $domains = [];

    /**
     * The cache repository.
     *
     * @var \Illuminate\Contracts\Cache\Repository|null
     */
    protected $cache;

    /**
     * The cache key.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Disposable constructor.
     *
     * @param \Illuminate\Contracts\Cache\Repository|null $cache
     */
    public function __construct(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Loads the domains from cache/storage into the class.
     *
     * @return $this
     */
    public function bootstrap()
    {
        if ($this->cache) {
            $data = $inCache = $this->getFromCache();
        }

        if (! isset($data) || ! $data) {
            $data = $this->getFromStorage();
        }

        if ($this->cache && (! isset($inCache) || ! $inCache)) {
            $this->cache->forever($this->getCacheKey(), $data);
        }

        $this->domains = $data;

        return $this;
    }

    /**
     * Get the domains from cache.
     *
     * @return array|null
     */
    protected function getFromCache()
    {
        return $this->cache->get($this->getCacheKey());
    }

    /**
     * Get the domains from storage, or if non-existent, from the package.
     *
     * @return array
     */
    protected function getFromStorage()
    {
        try {
            if ($data = $this->parseJson(file_get_contents($this->getStoragePath()))) {
                return $data;
            }
        } catch (ErrorException $e) {
            // File does not exist or could not be opened.
        }

        // Fall back to the list provided by the package.
        return $this->parseJson(file_get_contents(__DIR__.'/../domains.json'));
    }

    /**
     * Checks whether or not the given email address' domain matches one from a disposable email service.
     *
     * @param string $email
     * @return bool
     */
    public function isDisposable($email)
    {
        // Parse the email to its top level domain.
        preg_match("/[^\.\/]+\.[^\.\/]+$/", explode('@', $email, 2)[1] ?? '', $domain);

        // Just ignore this validator if the value doesn't even resemble an email or domain.
        if (count($domain) === 0) {
            return false;
        }

        return in_array($domain[0], $this->domains);
    }

    /**
     * Flushes the cache if applicable.
     */
    public function flushCache()
    {
        if ($this->cache) {
            $this->cache->forget($this->getCacheKey());
        }
    }

    /**
     * Flushes the source's list if applicable.
     */
    public function flushSource()
    {
        if (is_file($this->getStoragePath())) {
            @unlink($this->getStoragePath());
        }
    }

    /**
     * Get the storage path.
     *
     * @return string
     */
    public function getStoragePath()
    {
        return $this->storagePath;
    }

    /**
     * Set the storage path.
     *
     * @param string $path
     * @return $this
     */
    public function setStoragePath($path)
    {
        $this->storagePath = $path;

        return $this;
    }

    /**
     * Get the cache key.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Set the cache key.
     *
     * @param string $key
     * @return $this
     */
    public function setCacheKey($key)
    {
        $this->cacheKey = $key;

        return $this;
    }
}