<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Console;

use InvalidArgumentException;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use UnexpectedValueException;

class UpdateDisposableDomainsCommandTest extends TestCase
{
    /** @test */
    public function it_creates_the_file()
    {
        $this->assertFileNotExists($this->storagePath);

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertContains('yopmail.com', $domains);
    }

    /** @test */
    public function it_overwrites_the_file()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertContains('yopmail.com', $domains);
        $this->assertNotContains('foo', $domains);
    }

    /** @test */
    public function it_doesnt_overwrite_on_fetch_failure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Source URL is null');

        file_put_contents($this->storagePath, 'foo');

        $this->app['config']['disposable-email.source'] = null;

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertEquals(['foo'], $domains);
    }

    /** @test */
    public function it_cannot_use_a_custom_fetcher_with_string_result()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Provided data could not be parsed as a JSON list');

        file_put_contents($this->storagePath, 'foo');

        $this->app['config']['disposable-email.source'] = 'bar';
        $this->app['config']['disposable-email.fetcher'] = CustomFetcherWithStringResult::class;

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();
        $this->assertEquals(['foo'], $domains);
    }

    /** @test */
    public function it_can_use_a_custom_fetcher_with_json_result()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->app['config']['disposable-email.source'] = 'bar';
        $this->app['config']['disposable-email.fetcher'] = CustomFetcherWithJSONResult::class;

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertEquals(['bar'], $domains);
        $this->assertNotEquals(['foo'], $domains);
    }

    /** @test */
    public function it_can_use_a_custom_fetcher_with_array_result()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->app['config']['disposable-email.source'] = 'bar';
        $this->app['config']['disposable-email.fetcher'] = CustomFetcherWithArrayResult::class;

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertEquals(['bar'], $domains);
        $this->assertNotEquals(['foo'], $domains);
    }
}

class CustomFetcherWithStringResult
{
    public function handle($url)
    {
        return $url;
    }
}

class CustomFetcherWithJSONResult
{
    public function handle($url)
    {
        return json_encode($url);
    }
}

class CustomFetcherWithArrayResult
{
    public function handle($url)
    {
        return [$url];
    }
}
