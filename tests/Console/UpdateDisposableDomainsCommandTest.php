<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Console;

use InvalidArgumentException;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;

class UpdateDisposableDomainsCommandTest extends TestCase
{
    /** @test */
    public function it_creates_the_file()
    {
        $this->assertFileDoesNotExist($this->storagePath);

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);
        $this->assertMatchesRegularExpression('/yopmail.com/', file_get_contents($this->storagePath));
    }

    /** @test */
    public function it_overwrites_the_file()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);

        $contents = file_get_contents($this->storagePath);
        $this->assertMatchesRegularExpression('/yopmail.com/', $contents);
        $this->assertNotEquals('foo', $contents);
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
        $this->assertEquals('foo', file_get_contents($this->storagePath));
    }

    /** @test */
    public function it_can_use_a_custom_fetcher()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->app['config']['disposable-email.source'] = 'bar';
        $this->app['config']['disposable-email.fetcher'] = CustomFetcher::class;

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);

        $contents = file_get_contents($this->storagePath);
        $this->assertMatchesRegularExpression('/bar/', $contents);
        $this->assertNotEquals('foo', $contents);
    }
}

class CustomFetcher
{
    public function handle($url)
    {
        return json_encode([$url]);
    }
}
