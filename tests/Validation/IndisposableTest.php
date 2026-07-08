<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Validation;

use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;

class IndisposableTest extends TestCase
{
    #[Test]
    public function it_should_pass_for_indisposable_emails()
    {
        $validator = new Indisposable;
        $email = 'example@gmail.com';

        $this->assertTrue($validator->validate(null, $email, null, null));
    }

    #[Test]
    public function it_should_fail_for_disposable_emails()
    {
        $validator = new Indisposable;
        $email = 'example@yopmail.com';

        $this->assertFalse($validator->validate(null, $email, null, null));
    }

    #[Test]
    public function it_is_usable_through_the_validator()
    {
        $passingValidation = $this->app['validator']->make(['email' => 'example@gmail.com'], ['email' => 'indisposable']);
        $failingValidation = $this->app['validator']->make(['email' => 'example@yopmail.com'], ['email' => 'indisposable']);

        $this->assertTrue($passingValidation->passes());
        $this->assertTrue($failingValidation->fails());
    }

    #[Test]
    public function it_accepts_the_inline_mx_parameter()
    {
        $this->disposable()->setMxResolver(function ($domain) {
            return $domain === 'front-domain.example'
                ? [['type' => 'MX', 'target' => 'mail.mailinator.com', 'pri' => 10]]
                : [['type' => 'MX', 'target' => 'gmail-smtp-in.l.google.com', 'pri' => 5]];
        });

        $failingValidation = $this->app['validator']->make(
            ['email' => 'user@front-domain.example'],
            ['email' => 'indisposable:mx']
        );

        $passingValidation = $this->app['validator']->make(
            ['email' => 'user@legitimate-company.example'],
            ['email' => 'indisposable:mx']
        );

        $this->assertTrue($failingValidation->fails());
        $this->assertTrue($passingValidation->passes());
    }

    #[Test]
    public function it_does_not_apply_mx_validation_without_the_inline_parameter()
    {
        $this->disposable()->setMxResolver(function ($domain) {
            return [['type' => 'MX', 'target' => 'mail.mailinator.com', 'pri' => 10]];
        });

        $validator = new Indisposable;
        $email = 'user@front-domain.example';

        $this->assertTrue($validator->validate(null, $email, [], null));
        $this->assertFalse($validator->validate(null, $email, ['mx'], null));
    }
}
