<?php

use PHPUnit\Framework\TestCase;

class DisposableEmailTest extends TestCase {

    /** @test */
    public function an_email_address_from_a_disposable_email_provider_should_be_detected() {
        $this->assertFalse(Indisposable::hasDisposableDomain('test@gmail.com'));
    }

}
