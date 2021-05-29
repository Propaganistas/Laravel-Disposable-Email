<?php

namespace Propaganistas\LaravelDisposableEmail;

use ErrorException;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use UnexpectedValueException;

class DisposableDomains
{
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
        $domains = $this->getFromCache();

        if (! $domains) {
            $domains = $this->getFromStorage();
            $this->saveToCache($domains);
        }

        $this->domains = $domains;

        return $this;
    }

    /**
     * Get the domains from cache.
     *
     * @return array|null
     */
    protected function getFromCache()
    {
        if ($this->cache) {
            $domains = $this->cache->get($this->getCacheKey());
            
            return is_string($domains) ? json_decode($domains) : $domains;
        }

        return null;
    }

    /**
     * Save the domains in cache.
     *
     * @param  mixed  $data
     */
    public function saveToCache($data)
    {
        if ($this->cache) {
            $this->cache->forever($this->getCacheKey(), $data);
        }
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
     * Get the domains from storage, or if non-existent, from the package.
     *
     * @return array
     */
    protected function getFromStorage()
    {
        if (is_file($this->getStoragePath())) {
            $domains = $this->parseJson(
                file_get_contents($this->getStoragePath())
            );

            if ($domains) {
                return Arr::wrap($domains);
            }
        }

        // Fall back to the list provided by the package.
        return $this->parseJson(file_get_contents(__DIR__.'/../domains.json'));
    }

    /**
     * Save the domains in storage.
     *
     * @param  mixed  $data
     */
    public function saveToStorage($data)
    {
        if (is_string($data) && ! $this->parseJson($data)) {
            throw new UnexpectedValueException('Provided data could not be parsed as a JSON list');
        }

        $saved = file_put_contents($this->getStoragePath(),
            is_string($data) ? $data : json_encode($data)
        );

        if ($saved) {
            $this->flushCache();
        }

        return $saved;
    }

    /**
     * Flushes the source's list if applicable.
     */
    public function flushStorage()
    {
        if (is_file($this->getStoragePath())) {
            @unlink($this->getStoragePath());
        }
    }

    /**
     * Checks whether the given email address' domain matches a disposable email service.
     *
     * @param string $email
     * @return bool
     */
    public function isDisposable($email)
    {
        if ($domain = Str::lower(Arr::get(explode('@', $email, 2), 1))) {
            return in_array($domain, $this->domains);
        }

        // Just ignore this validator if the value doesn't even resemble an email or domain.
        return false;
    }

    /**
     * Checks whether the given email address' domain doesn't match a disposable email service.
     *
     * @param string $email
     * @return bool
     */
    public function isNotDisposable($email)
    {
        return ! $this->isDisposable($email);
    }

    /**
     * Alias of "isNotDisposable".
     *
     * @param string $email
     * @return bool
     */
    public function isIndisposable($email)
    {
        return $this->isNotDisposable($email);
    }

    /**
     * Get the list of disposable domains.
     *
     * @return array
     */
    public function getDomains()
    {
        return $this->domains;
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

    /**
     * Parses the given JSON into a native array. Returns false on errors.
     *
     * @param string $data
     * @return array|bool
     */
    protected function parseJson($data)
    {
        $data = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            return false;
        }

        return $data;
    }
}
