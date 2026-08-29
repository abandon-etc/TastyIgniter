<?php

declare(strict_types=1);

namespace App\Payments\Casts;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Persist and hydrate payment instants independently from the application
 * timezone — the same convention the Birthday domain established. Kept as
 * the payments layer's own cast so the shared layer does not depend on
 * any one business extension.
 */
final class UtcDateTime implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?CarbonImmutable
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return CarbonImmutable::instance($value)->setTimezone('UTC');
        }

        // Carbon 3 throws InvalidFormatException in strict mode (the
        // default) and returns null with strict mode off — it never
        // returns false. Both failure shapes become the same loud,
        // column-naming exception so corrupt ledger instants cannot pass
        // silently.
        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', (string) $value, 'UTC');
        } catch (InvalidFormatException $e) {
            throw new InvalidArgumentException("Invalid UTC datetime stored in [{$key}].", 0, $e);
        }

        if ($date === null) {
            throw new InvalidArgumentException("Invalid UTC datetime stored in [{$key}].");
        }

        return $date;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $date = $value instanceof CarbonInterface
            ? CarbonImmutable::instance($value)
            : CarbonImmutable::parse((string) $value, 'UTC');

        return $date->setTimezone('UTC')->format('Y-m-d H:i:s');
    }
}
