<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelDisposableEmail\Facades\DisposableDomains;

class Indisposable implements ValidationRule
{
    /**
     * Whether MX record inspection should be enabled.
     */
    protected bool $inspectMxRecords = false;

    /**
     * Determine whether the given value is valid.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isEmail = Validator::make([$attribute => $value], [$attribute => 'email']);

        if ($isEmail->fails()) {
            $fail($isEmail->errors()->first($attribute));
            return;
        }

        if (DisposableDomains::isDisposable((string) $value, checkMx: $this->inspectMxRecords)) {
            $fail('validation.indisposable')->translate();
        }
    }

    public function inspectMxRecords(): static
    {
        $this->inspectMxRecords = true;

        return $this;
    }

    public function setParameters($parameters): static
    {
        $parameters = is_array($parameters) ? $parameters : func_get_args();

        foreach ($parameters as $parameter) {
            if (strcasecmp('mx', $parameter) === 0) {
                $this->inspectMxRecords();
            }
        }

        return $this;
    }
}
