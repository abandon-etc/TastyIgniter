<?php

declare(strict_types=1);

namespace App\Payments\Models\Concerns;

use Closure;
use LogicException;

/**
 * Ledger rows change only through the payment services. Models using this
 * trait refuse updates outside a serviceWrite() closure and refuse deletes
 * always: a payment ledger is corrected by new rows and transitions, never
 * by editing or deleting history (design §18: a payment exception is
 * reconciled, not rolled back by deleting records).
 */
trait ServiceWrites
{
    private static bool $serviceWrite = false;

    public static function serviceWrite(Closure $callback): mixed
    {
        $previous = static::$serviceWrite;
        static::$serviceWrite = true;

        try {
            return $callback();
        } finally {
            static::$serviceWrite = $previous;
        }
    }

    protected static function bootServiceWrites(): void
    {
        static::updating(function ($model): void {
            if (!static::$serviceWrite) {
                throw new LogicException(sprintf(
                    '%s rows are updated only through the payment services.',
                    class_basename($model),
                ));
            }
        });

        static::deleting(function ($model): void {
            throw new LogicException(sprintf(
                '%s rows are never deleted; the ledger is corrected by new rows.',
                class_basename($model),
            ));
        });
    }
}
