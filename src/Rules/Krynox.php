<?php

namespace Krynox\Captcha\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Krynox\Captcha\KrynoxCaptcha;

/**
 * Validation rule: `'krynox-captcha' => ['required', new Krynox]`.
 */
class Krynox implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = app(KrynoxCaptcha::class)->verify(
            is_string($value) ? $value : null,
            request()->ip()
        );

        if (! $result['success']) {
            $fail('The :attribute could not be verified. Please try again.');
        }
    }
}
