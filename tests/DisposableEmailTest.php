<?php

use PHPUnit\Framework\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;

class DisposableEmailTest extends TestCase {

    /** @test */
    public function an_email_address_from_a_disposable_email_provider_should_be_detected() {
        $this->assertFalse(Indisposable::isDisposable('test@gmail.com'));
    }

}
