<?php

use PHPUnit\Framework\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;

class DisposableEmailTest extends TestCase {

    /** @test */
    public function non_disposable_email_domains_should_not_be_detected_as_disposable() {
        $this->assertFalse(Indisposable::isDisposable('test@gmail.com'));
    }

}
