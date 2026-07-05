<?php

namespace Propaganistas\LaravelDisposableEmail;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
     * The whitelist of domains to allow.
     *
     * @var array
     */
    protected $whitelist = [];

    /**
     * The cache repository.
     *
     * @var Repository|null
     */
    protected $cache;

    /**
     * The cache key.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Whether to include subdomains.
     *
     * @var bool
     */
    protected $includeSubdomains = false;

    /**
     * Whether to inspect the domain's MX records.
     *
     * @var bool
     */
    protected $checkMx = false;

    /**
     * Resolver used to fetch a domain's MX records. Overridable for testing.
     *
     * @var \Closure
     */
    protected $mxResolver;

    /**
     * In-memory memoization of resolved MX targets, keyed by domain.
     *
     * @var array
     */
    protected $mxCache = [];

    /**
     * Disposable constructor.
     */
    public function __construct(?Cache $cache = null)
    {
        $this->cache = $cache;

        $this->mxResolver = static function ($domain) {
            $records = @dns_get_record($domain, DNS_MX);

            return $records === false ? [] : $records;
        };
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
            $this->saveToCache(
                $domains = $this->getFromStorage()
            );
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

            // @TODO: Legacy code for bugfix. Remove me.
            if (is_string($domains) || empty($domains)) {
                $this->flushCache();

                return null;
            }

            return $domains;
        }

        return null;
    }

    /**
     * Save the domains in cache.
     */
    public function saveToCache(?array $domains = null)
    {
        if ($this->cache && ! empty($domains)) {
            $this->cache->forever($this->getCacheKey(), $domains);
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
        $domains = is_file($this->getStoragePath())
            ? file_get_contents($this->getStoragePath())
            : file_get_contents(__DIR__.'/../domains.json');

        return array_diff(
            json_decode($domains, true),
            $this->getWhitelist()
        );
    }

    /**
     * Save the domains in storage.
     */
    public function saveToStorage(array $domains)
    {
        $saved = file_put_contents($this->getStoragePath(), json_encode($domains));

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
     * @param  string  $email
     * @return bool
     */
    public function isDisposable($email)
    {
        $domain = Str::lower(Arr::get(explode('@', $email, 2), 1));

        if (! $domain) {
            // Just ignore this validator if the value doesn't even resemble an email or domain.
            return false;
        }

        if (in_array($domain, $this->domains)) {
            // Domain is a matching root domain.
            return true;
        }

        if ($this->getIncludeSubdomains()) {
            // Check for subdomains.
            foreach ($this->domains as $root) {
                if (str_ends_with($domain, '.'.$root)) {
                    return true;
                }
            }
        }

        if ($this->getCheckMx() && $this->hasDisposableMx($domain)) {
            // The domain's mail servers point at a known disposable service.
            // This catches freshly rotated "front" domains that aren't listed
            // yet but share their backend mail infrastructure with a listed one.
            return true;
        }

        return false;
    }

    /**
     * Checks whether any of the domain's MX targets belong to a disposable service.
     *
     * MX targets are always matched at a DNS label boundary against the domain
     * list (e.g. "mail.mailinator.com" matches the listed "mailinator.com"),
     * regardless of the "include subdomains" setting.
     *
     * @param  string  $domain
     */
    protected function hasDisposableMx($domain): bool
    {
        foreach ($this->mxTargets($domain) as $host) {
            if (in_array($host, $this->domains)) {
                return true;
            }

            foreach ($this->domains as $root) {
                if (str_ends_with($host, '.'.$root)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Resolve the lower-cased MX target hosts for the given domain.
     *
     * @param  string  $domain
     */
    protected function mxTargets($domain): array
    {
        if (array_key_exists($domain, $this->mxCache)) {
            return $this->mxCache[$domain];
        }

        $targets = [];

        foreach (call_user_func($this->mxResolver, $domain) as $record) {
            if (($record['type'] ?? null) !== 'MX' || empty($record['target'])) {
                continue;
            }

            $targets[] = Str::lower(rtrim((string) $record['target'], '.'));
        }

        return $this->mxCache[$domain] = array_values(array_unique($targets));
    }

    /**
     * Checks whether the given email address' domain doesn't match a disposable email service.
     *
     * @param  string  $email
     * @return bool
     */
    public function isNotDisposable($email)
    {
        return ! $this->isDisposable($email);
    }

    /**
     * Alias of "isNotDisposable".
     *
     * @param  string  $email
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
     * Get the whitelist.
     *
     * @return array
     */
    public function getWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * Set the whitelist.
     *
     * @return $this
     */
    public function setWhitelist(array $whitelist)
    {
        $this->whitelist = $whitelist;

        return $this;
    }

    /**
     * Get whether to include subdomains.
     *
     * @return bool
     */
    public function getIncludeSubdomains()
    {
        return $this->includeSubdomains;
    }

    /**
     * Set whether to include subdomains.
     *
     * @return $this
     */
    public function setIncludeSubdomains(bool $includeSubdomains)
    {
        $this->includeSubdomains = $includeSubdomains;

        return $this;
    }

    /**
     * Get whether to inspect the domain's MX records.
     */
    public function getCheckMx(): bool
    {
        return $this->checkMx;
    }

    /**
     * Set whether to inspect the domain's MX records.
     *
     * @return $this
     */
    public function setCheckMx(bool $checkMx)
    {
        $this->checkMx = $checkMx;

        return $this;
    }

    /**
     * Override the MX resolver (primarily for testing).
     *
     * The resolver receives a domain and returns an array of DNS records in
     * the shape produced by dns_get_record(), i.e. entries with 'type' and
     * 'target' keys.
     *
     * @return $this
     */
    public function setMxResolver(\Closure $resolver)
    {
        $this->mxResolver = $resolver;
        $this->mxCache = [];

        return $this;
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
     * @param  string  $path
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
     * @param  string  $key
     * @return $this
     */
    public function setCacheKey($key)
    {
        $this->cacheKey = $key;

        return $this;
    }
}
