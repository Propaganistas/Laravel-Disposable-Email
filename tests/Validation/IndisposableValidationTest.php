<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Validation;

use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelDisposableEmail\Facades\Indisposable;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\IndisposableValidation;

class IndisposableValidationTestTest extends TestCase {

    /**
     * DisposableEmailCacheTest SetUp
     */
    public function setUp() {
        parent::setUp();
        Indisposable::flushCache();
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

    /** @test */
    public function our_validation_method_is_usable_through_the_laravel_validator_facade() {
        $passingValidation = Validator::make(
            ['email' => 'example@gmail.com'],
            ['email' => 'indisposable']
        );

        $failingValidation = Validator::make(
            ['email' => 'example@yopmail.com'],
            ['email' => 'indisposable']
        );

        $this->assertFalse($passingValidation->fails());
        $this->assertTrue($failingValidation->fails());
    }

}
