<?php

use PHPUnit\Framework\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Cache;

class DisposableEmailTest extends TestCase {

    /** @test */
    public function we_should_be_able_to_fetch_the_remote_source() {
        $this->assertNotEmpty(Cache::fetchSource());
    }

}
