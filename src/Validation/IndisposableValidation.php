<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Propaganistas\LaravelDisposableEmail\Facades\Indisposable;

class IndisposableValidation
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
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        return ! Indisposable::isDisposable($value);
    }
}
