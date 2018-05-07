<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Validation;

use Illuminate\Support\Facades\Cache as FrameworkCache;
use Propaganistas\LaravelDisposableEmail\Console\CacheDisposableDomainsCommand;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use Propaganistas\LaravelDisposableEmail\Facades\Indisposable;

class IndisposableTest extends TestCase {

    /**
     * Indisposable remote domains cache key
     *
     * @var string
     */
    private $cacheKey = 'laravel-disposable-email.cache';

    /**
     * DisposableEmailCacheTest SetUp
     */
    public function setUp() {
        parent::setUp();
        Indisposable::flushCache();
    }

    /** @test */
    public function remote_disposable_domains_can_be_loaded() {
        $this->assertNotNull(Indisposable::remoteDomains());
    }

    /** @test */
    public function local_disposable_domains_can_be_loaded() {
        $this->assertNotNull(Indisposable::localDomains());
    }

    /** @test */
    public function the_indisposable_remote_domains_cache_can_be_flushed() {
        // Loads remote domains and caches them indefinitely.
        Indisposable::remoteDomains();

        $this->assertNotNull(FrameworkCache::get($this->cacheKey));

        Indisposable::flushCache();

        $this->assertNull(FrameworkCache::get($this->cacheKey));
    }

    /** @test */
    public function the_indisposable_remote_domains_method_builds_a_cache() {
        $this->assertNull(FrameworkCache::get($this->cacheKey));

        Indisposable::remoteDomains();

        $this->assertNotNull(FrameworkCache::get($this->cacheKey));
    }

    /** @test */
    public function the_disposable_domain_cache_command_updates_the_domain_cache() {
        $this->assertNull(FrameworkCache::get($this->cacheKey));

        $this->artisan('disposable:cache');

        $this->assertNotNull(FrameworkCache::get($this->cacheKey));
    }

    /** @test */
    public function non_disposable_email_domains_should_not_be_detected_as_disposable() {
        $this->assertFalse(Indisposable::isDisposable('test@gmail.com'));
    }

    /** @test */
    public function a_commonly_known_disposable_email_provider_should_be_detected_as_disposable() {
        $this->assertTrue(Indisposable::isDisposable('test@yopmail.com'));
    }

}
