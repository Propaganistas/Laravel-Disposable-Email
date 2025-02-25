<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Console;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelDisposableEmail\Contracts\Fetcher;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;

class UpdateDisposableDomainsCommandTest extends TestCase
{
    #[Test]
    public function it_loads_source_into_storage()
    {
        $this->assertFileDoesNotExist($this->storagePath);

        $this->artisan('disposable:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertContains('yopmail.com', $domains);
    }

    #[Test]
    public function it_overwrites_source_in_storage()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->artisan('disposable:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertContains('yopmail.com', $domains);
        $this->assertNotContains('foo', $domains);
    }

    #[Test]
    public function it_loads_multiple_sources()
    {
        $this->assertFileDoesNotExist($this->storagePath);

        $this->app['config']['disposable-email.sources'] = [
            'https://cdn.jsdelivr.net/gh/disposable/disposable-email-domains@master/domains.json',
            __DIR__.'/../local_source.json',
        ];

        $this->artisan('disposable:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();

        $this->assertIsArray($domains);
        $this->assertContains('yopmail.com', $domains);
        $this->assertContains('local_test_source.org', $domains);
    }

    #[Test]
    public function it_doesnt_overwrite_on_fetch_failure()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Source URL is null');

        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-email.sources'] = [null];

        $this->artisan('disposable:update')
            ->assertExitCode(1);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();
        $this->assertEquals(['foo'], $domains);
    }

    #[Test]
    public function it_can_use_a_custom_fetcher()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-email.sources'] = ['bar'];
        $this->app['config']['disposable-email.fetcher'] = CustomFetcher::class;

        $this->artisan('disposable:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();
        $this->assertEquals(['bar'], $domains);
    }

    #[Test]
    public function custom_fetchers_need_fetcher_contract()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-email.sources'] = ['bar'];
        $this->app['config']['disposable-email.fetcher'] = InvalidFetcher::class;

        $this->artisan('disposable:update')
            ->assertExitCode(1);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();
        $this->assertNotEquals(['foo'], $domains);
    }

    #[Test]
    public function it_processes_legacy_source_config()
    {
        file_put_contents($this->storagePath, json_encode(['foo']));

        $this->app['config']['disposable-email.sources'] = null;
        $this->app['config']['disposable-email.source'] = 'bar';
        $this->app['config']['disposable-email.fetcher'] = CustomFetcher::class;

        $this->artisan('disposable:update')
            ->assertExitCode(0);

        $this->assertFileExists($this->storagePath);

        $domains = $this->disposable()->getDomains();
        $this->assertEquals(['bar'], $domains);
    }
}

class CustomFetcher implements Fetcher
{
    public function handle($url): array
    {
        return [$url];
    }
}

class InvalidFetcher
{
    public function handle($url)
    {
        return $url;
    }
}
