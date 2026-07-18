<?php

declare(strict_types=1);

namespace App\Delivery;

final class DeliveryFlag
{
    public static function parse(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
