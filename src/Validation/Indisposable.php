<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Propaganistas\LaravelDisposableEmail\Facades\DisposableDomains;

class Indisposable
{
    /**
     * Default error message.
     *
     * @var string
     */
    public static $errorMessage = 'Disposable email addresses are not allowed.';

    /**
     * Validates whether an email address does not originate from a disposable email service.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        return DisposableDomains::isNotDisposable($value);
    }
}
