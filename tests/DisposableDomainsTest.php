<?php

namespace Propaganistas\LaravelDisposableEmail\Tests;

use Propaganistas\LaravelDisposableEmail\DisposableDomains;

class DisposableDomainsTest extends TestCase
{
    /** @test */
    public function it_can_be_resolved_using_alias()
    {
        $this->assertEquals(DisposableDomains::class, get_class($this->app->make('disposable_email.domains')));
    }

    /** @test */
    public function it_can_be_resolved_using_class()
    {
        $this->assertEquals(DisposableDomains::class, get_class($this->app->make(DisposableDomains::class)));
    }

    /** @test */
    public function it_can_get_storage_path()
    {
        $this->assertEquals(
            $this->app['config']['disposable-email.storage'],
            $this->disposable()->getStoragePath()
        );
    }

    /** @test */
    public function it_can_set_storage_path()
    {
        $this->disposable()->setStoragePath('foo');

        $this->assertEquals('foo', $this->disposable()->getStoragePath());
    }

    /** @test */
    public function it_can_get_cache_key()
    {
        $this->assertEquals(
            $this->app['config']['disposable-email.cache.key'],
            $this->disposable()->getCacheKey()
        );
    }

    /** @test */
    public function it_can_set_cache_key()
    {
        $this->disposable()->setCacheKey('foo');

        $this->assertEquals('foo', $this->disposable()->getCacheKey());
    }

    /** @test */
    public function it_takes_cached_domains_if_available()
    {
        $this->app['cache.store'][$this->disposable()->getCacheKey()] = ['foo'];

        $this->disposable()->bootstrap();

        $this->assertAttributeEquals(['foo'], 'domains', $this->disposable());
    }

    /** @test */
    public function it_skips_cache_when_configured()
    {
        $this->app['config']['disposable-email.cache.enabled'] = false;

        $this->assertNull($this->app['cache.store'][$this->disposable()->getCacheKey()]);
        $this->assertAttributeContains('yopmail.com', 'domains', $this->disposable());
    }

    /** @test */
    public function it_takes_source_domains_when_cache_is_not_available()
    {
        $this->app['config']['disposable-email.cache.enabled'] = false;

        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->assertAttributeEquals(['foo'], 'domains', $this->disposable());
    }

    /** @test */
    public function it_takes_package_domains_when_source_is_not_available()
    {
        $this->app['config']['disposable-email.cache.enabled'] = false;

        $this->assertAttributeContains('yopmail.com', 'domains', $this->disposable());
    }

    /** @test */
    public function it_can_flush_source()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->disposable()->flushSource();

        $this->assertFileNotExists($this->storagePath);
    }

    /** @test */
    public function it_doesnt_throw_exceptions_for_flush_source_when_file_doesnt_exist()
    {
        $this->disposable()->flushSource();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_flush_cache()
    {
        $this->app['cache.store'][$this->disposable()->getCacheKey()] = 'foo';

        $this->assertEquals('foo', $this->app['cache']->get($this->disposable()->getCacheKey()));

        $this->disposable()->flushCache();

        $this->assertNull($this->app['cache']->get($this->disposable()->getCacheKey()));
    }

    /** @test */
    public function it_can_verify_disposability()
    {
        $this->assertTrue($this->disposable()->isDisposable('example@yopmail.com'));
        $this->assertFalse($this->disposable()->isDisposable('example@gmail.com'));
    }
}