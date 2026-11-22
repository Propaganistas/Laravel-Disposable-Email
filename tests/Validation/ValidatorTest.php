<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Validation;

use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;

class ValidatorTest extends TestCase
{
    protected function validate(array $data, array $rules): Validator
    {
        return $this->app['validator']->make($data, $rules);
    }

    #[Test]
    public function it_validates_without_parameters()
    {
        $this->assertTrue($this->validate(
            ['email' => 'example@gmail.com'],
            ['email' => 'indisposable']
        )->passes());

        $this->assertFalse($this->validate(
            ['email' => 'example@yopmail.com'],
            ['email' => 'indisposable']
        )->passes());
    }

    #[Test]
    public function it_validates_rule_objects()
    {
        $this->assertTrue($this->validate(
            ['email' => 'example@gmail.com'],
            ['email' => [new Indisposable]]
        )->passes());

        $this->assertFalse($this->validate(
            ['email' => 'example@yopmail.com'],
            ['email' => [new Indisposable]]
        )->passes());
    }

    #[Test]
    public function it_validates_non_email_values_using_email_validator()
    {
        $validator = $this->validate(['email' => ['array-value']], ['email' => [new Indisposable]]);

        $this->assertFalse($validator->passes());
        $this->assertSame(
            Lang::get('validation.email', ['attribute' => 'email']),
            $validator->errors()->first('email')
        );
    }

    #[Test]
    public function it_returns_the_default_validation_error_message()
    {
        $validator = $this->validate(
            ['email' => 'example@yopmail.com'],
            ['email' => [new Indisposable]]
        );

        $this->assertSame('validation.indisposable', $validator->errors()->first('email'));
    }

    #[Test]
    public function it_returns_the_translated_validation_error_message()
    {
        Lang::addLines(['validation.indisposable' => 'Please use a permanent email address.'], 'en');

        $validator = $this->validate(
            ['email' => 'example@yopmail.com'],
            ['email' => [new Indisposable]]
        );

        $this->assertSame('Please use a permanent email address.', $validator->errors()->first('email'));
    }

    #[Test]
    public function it_validates_with_inline_mx_parameter()
    {
        $this->disposable()->setMxResolver(function ($domain) {
            return $domain === 'front-domain.example'
                ? [['type' => 'MX', 'target' => 'mail.mailinator.com', 'pri' => 10]]
                : [['type' => 'MX', 'target' => 'gmail-smtp-in.l.google.com', 'pri' => 5]];
        });

        $this->assertTrue($this->validate(
            ['email' => 'user@front-domain.example'],
            ['email' => 'indisposable']
        )->passes());

        $this->assertFalse($this->validate(
            ['email' => 'user@front-domain.example'],
            ['email' => 'indisposable:mx']
        )->passes());

        $this->assertTrue($this->validate(
            ['email' => 'user@legitimate-company.example'],
            ['email' => 'indisposable:mx']
        )->passes());
    }
}
