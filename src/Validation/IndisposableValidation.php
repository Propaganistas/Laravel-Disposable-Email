<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

class IndisposableValidation
{
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
