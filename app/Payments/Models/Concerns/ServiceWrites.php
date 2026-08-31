<?php

declare(strict_types=1);

namespace App\Payments\Models\Concerns;

use Closure;
use LogicException;

/**
 * Ledger rows are meant to change only through the payment services, and
 * this trait enforces that at the Eloquent *instance* surface: updates
 * outside a serviceWrite() closure and all deletes throw (design §18: a
 * payment ledger is corrected by new rows and transitions, never by
 * editing or deleting history).
 *
 * The guard is deliberately advisory beyond that surface — query-builder
 * update()/delete(), insert()/upsert(), and *Quietly() writes fire no
 * model events and bypass it, exactly as the Birthday hold ledger's
 * guards can be bypassed. Those write paths are forbidden for these
 * tables by project convention (AGENT_WORKFLOW.md reviews), not by
 * mechanism. serviceWrite() is @internal to App\Payments: calling it
 * from outside the payment services defeats the point, and while a
 * service write holds the per-class flag, model events for that class
 * are unguarded — services therefore keep their closures minimal and
 * touch only the row they locked.
 */
trait ServiceWrites
{
    private static bool $serviceWrite = false;

    /** @internal Only the App\Payments services may open a service write. */
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
