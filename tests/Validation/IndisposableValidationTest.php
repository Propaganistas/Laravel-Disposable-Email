<?php

namespace Propaganistas\LaravelDisposableEmail\Validation\Tests;

use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Cache;
use Propaganistas\LaravelDisposableEmail\Validation\IndisposableValidation;

class IndisposableTest extends TestCase {

    /**
     * DisposableEmailCacheTest SetUp
     */
    public function setUp() {
        parent::setUp();
        Cache::update();
    }

    /** @test */
    public function the_validator_should_pass_for_non_disposable_emails() {
        $validator = new IndisposableValidation();
        $testEmail = 'example@gmail.com';

        $this->assertTrue($validator->validate(null, $testEmail, null, null));
    }

    /** @test */
    public function the_validator_should_not_pass_for_non_disposable_emails() {
        $validator = new IndisposableValidation();
        $testEmail = 'example@yopmail.com';

        $this->assertFalse($validator->validate(null, $testEmail, null, null));
    }

}
