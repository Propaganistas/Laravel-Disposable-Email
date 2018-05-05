<?php

namespace Propaganistas\LaravelDisposableEmail\Validation\Tests;

use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Cache;

class CacheTest extends TestCase {

    /**
     * DisposableEmailCacheTest SetUp
     */
    public function setUp() {
        parent::setUp();
        Cache::update();
    }

    /** @test */
    public function we_should_be_able_to_fetch_the_remote_source() {
        $this->assertNotEmpty(Cache::fetchRemoteSource());
    }

    /** @test */
    public function we_should_be_able_to_fetch_domains_from_the_local_fallback_source() {
        $this->assertNotEmpty(Cache::fetchLocalSource());
    }

    /** @test */
    public function the_cache_store_method_should_store_data_as_expected() {
        Cache::store(['example.com']);

        $this->assertArrayHasKey(0, Cache::fetch());
    }

    /** @test */
    public function the_cache_update_method_should_override_old_data() {
        $testDomain = 'google.com';
        Cache::store([$testDomain]);
        Cache::update();

        $this->assertNotContains($testDomain, Cache::fetch());
    }

    /** @test */
    public function the_cache_store_method_should_handle_json_strings() {
        $testList = '["google.com", "outlook.com"]';
        Cache::store($testList);

        $this->assertArraySubset(['google.com', 'outlook.com'], Cache::fetch());
    }

    /** @test */
    public function the_fetch_or_update_method_falls_back_to_cache_renewal_on_empty_cache() {
        Cache::store(['google.com']);

        $fetch = Cache::fetchOrUpdate();

        $this->assertCount(1, $fetch);

        Cache::flush();

        $fetch = Cache::fetchOrUpdate();

        $this->assertNotCount(1, $fetch);
    }

}
