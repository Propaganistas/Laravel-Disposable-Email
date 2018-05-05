<?php

use Orchestra\Testbench\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Cache;

class DisposableEmailTest extends TestCase {

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
        Cache::store(['test@example.com']);

        $this->assertArrayHasKey(0, Cache::fetch());
    }

}
