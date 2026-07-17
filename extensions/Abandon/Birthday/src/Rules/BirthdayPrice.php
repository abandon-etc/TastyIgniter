<?php

declare(strict_types=1);

namespace Abandon\Birthday\Rules;

use Abandon\Birthday\Models\PriceValue;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

final class BirthdayPrice implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            PriceValue::toMinorUnits($value);
        } catch (InvalidArgumentException) {
            $fail(trans('abandon.birthday::default.validation.price', [
                'max' => PriceValue::MAX_AMOUNT,
            ]));
        }
    }
}
