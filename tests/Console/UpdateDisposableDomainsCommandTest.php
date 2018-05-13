<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Console;

use Propaganistas\LaravelDisposableEmail\Tests\TestCase;

class UpdateDisposableDomainsCommandTest extends TestCase
{
    /** @test */
    public function it_creates_the_file()
    {
        $this->assertFileNotExists($this->storagePath);

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);
        $this->assertContains('yopmail.com', file_get_contents($this->storagePath));
    }

    /** @test */
    public function it_overwrites_the_file()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);

        $contents = file_get_contents($this->storagePath);
        $this->assertContains('yopmail.com', $contents);
        $this->assertNotEquals('foo', $contents);
    }

    /** @test */
    public function it_doesnt_overwrite_on_fetch_failure()
    {
        file_put_contents($this->storagePath, 'foo');

        $this->app['config']['disposable-email.source'] = null;

        $this->artisan('disposable:update');

        $this->assertFileExists($this->storagePath);
        $this->assertEquals('foo', file_get_contents($this->storagePath));
    }
}
