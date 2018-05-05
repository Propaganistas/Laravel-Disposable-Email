<?php

use Orchestra\Testbench\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Cache;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;

class DisposableEmailTest extends TestCase {

    /**
     * DisposableEmailCacheTest SetUp
     */
    public function setUp() {
        parent::setUp();
        Cache::update();
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
