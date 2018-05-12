<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Validation;

use Illuminate\Support\Facades\Cache;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use Propaganistas\LaravelDisposableEmail\Facades\Indisposable;

class IndisposableTest extends TestCase {

    /**
     * Indisposable remote domains cache key
     *
     * @var string
     */
    private $cacheKey;

    /**
     * DisposableEmailCacheTest SetUp
     */
    public function setUp() {
        parent::setUp();
        Indisposable::flushCache();
        $this->cacheKey = Indisposable::getCacheKey();
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
    public function the_remote_domains_cache_override_method_should_override_the_cache() {
        $this->assertNull(Cache::get($this->cacheKey));

        Indisposable::setRemoteDomainsCache(['example.com']);

        $this->assertCount(1, Indisposable::remoteDomains());
    }

    /** @test */
    public function the_indisposable_remote_domains_cache_can_be_flushed() {
        // Loads remote domains and caches them indefinitely.
        Indisposable::remoteDomains();

        $this->assertNotNull(Cache::get($this->cacheKey));

        Indisposable::flushCache();

        $this->assertNull(Cache::get($this->cacheKey));
    }

    /** @test */
    public function the_indisposable_remote_domains_method_builds_a_cache() {
        $this->assertNull(Cache::get($this->cacheKey));

        Indisposable::remoteDomains();

        $this->assertNotNull(Cache::get($this->cacheKey));
    }

    /** @test */
    public function the_disposable_domain_cache_command_updates_the_domain_cache() {
        $this->assertNull(Cache::get($this->cacheKey));

        $this->artisan('disposable:cache');

        $this->assertNotNull(Cache::get($this->cacheKey));
    }

    /** @test */
    public function the_disposable_domain_cache_command_re_instantiates_the_remote_domains_cache_on_error() {
        $this->assertNull(Cache::get($this->cacheKey));

        Indisposable::setRemoteDomainsCache(['testdomain.com']);

        Indisposable::setRemoteUrl('invalid URI to simulate a cache exception');

        $this->artisan('disposable:cache');

        $this->assertCount(1, Indisposable::remoteDomains());

        $this->assertEquals(Indisposable::remoteDomains(), Cache::get($this->cacheKey));

        Indisposable::setRemoteUrl('https://rawgit.com/andreis/disposable-email-domains/master/domains.json');

        $this->artisan('disposable:cache');

        $this->assertNotCount(1, Indisposable::remoteDomains());
    }

    /** @test */
    public function non_disposable_email_domains_should_not_be_detected_as_disposable() {
        $this->assertFalse(Indisposable::isDisposable('test@gmail.com'));
    }

    /** @test */
    public function a_commonly_known_disposable_email_provider_should_be_detected_as_disposable() {
        $this->assertTrue(Indisposable::isDisposable('test@yopmail.com'));
    }

    /** @test */
    public function invalid_email_addresses_ignored_discarded_by_the_isDisposable_method() {
        $this->assertFalse(Indisposable::isDisposable(''));
        $this->assertFalse(Indisposable::isDisposable('@@'));
        $this->assertFalse(Indisposable::isDisposable('test@example@me.com'));
        $this->assertFalse(Indisposable::isDisposable('lorem ipsum'));
        $this->assertFalse(Indisposable::isDisposable('/.*/g'));
        $this->assertFalse(Indisposable::isDisposable('test@yopmail.com@me'));
        $this->assertFalse(Indisposable::isDisposable('yopmail.com@yopmail.com@yopmail.com'));
        $this->assertFalse(Indisposable::isDisposable('yopmail.com yopmail.com yopmail.com'));
    }

}
