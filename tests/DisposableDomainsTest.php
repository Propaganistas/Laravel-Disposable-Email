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

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertEquals(['foo'], $domains);
    }
    
    /** @test */
    public function it_handles_a_cached_json_string()
    {
        $this->app['cache.store'][$this->disposable()->getCacheKey()] = json_encode(['foo']);

        $this->disposable()->bootstrap();

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertEquals(['foo'], $domains);
    }

    /** @test */
    public function it_skips_cache_when_configured()
    {
        $this->app['config']['disposable-email.cache.enabled'] = false;

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertNull($this->app['cache.store'][$this->disposable()->getCacheKey()]);
        $this->assertContains('yopmail.com',  $domains);
    }

    /** @test */
    public function it_takes_storage_domains_when_cache_is_not_available()
    {
        $this->app['config']['disposable-email.cache.enabled'] = false;

        file_put_contents($this->storagePath, json_encode(['foo']));

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertEquals(['foo'], $domains);
    }

    /** @test */
    public function it_casts_storage_domains_to_array()
    {
        $this->app['config']['disposable-email.cache.enabled'] = false;

        file_put_contents($this->storagePath, json_encode('foo,bar'));

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertEquals(['foo,bar'], $domains);
    }

    /** @test */
    public function it_takes_package_domains_when_storage_is_not_available()
    {
        $this->app['config']['disposable-email.cache.enabled'] = false;

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertContains('yopmail.com', $domains);
    }

    /** @test */
    public function it_can_flush_storage()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->disposable()->flushStorage();

        $this->assertFileNotExists($this->storagePath);
    }

    /** @test */
    public function it_doesnt_throw_exceptions_for_flush_storage_when_file_doesnt_exist()
    {
        $this->disposable()->flushStorage();

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
        $this->assertFalse($this->disposable()->isNotDisposable('example@yopmail.com'));
        $this->assertFalse($this->disposable()->isIndisposable('example@yopmail.com'));

        $this->assertFalse($this->disposable()->isDisposable('example@gmail.com'));
        $this->assertTrue($this->disposable()->isNotDisposable('example@gmail.com'));
        $this->assertTrue($this->disposable()->isIndisposable('example@gmail.com'));
    }

    /** @test */
    public function it_checks_the_full_email_domain()
    {
        $this->assertTrue($this->disposable()->isDisposable('example@mailinator.com'));
        $this->assertTrue($this->disposable()->isDisposable('example@mail.mailinator.com'));
        $this->assertTrue($this->disposable()->isNotDisposable('example@isnotdisposable.mailinator.com'));
    }
}
