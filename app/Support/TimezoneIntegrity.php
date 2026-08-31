<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Says out loud when the application is not running on the stored
 * timezone.
 *
 * TastyIgniter sets the running timezone at boot from the admin setting,
 * and silently falls back to config('app.timezone') whenever that read is
 * unavailable — `Settings::getFieldValues()` returns nothing as soon as
 * `Igniter::hasDatabase()` swallows a database error, which a hiccup at
 * container start is enough to cause. Both results are memoised, so the
 * instance keeps the fallback for its whole life.
 *
 * The fallback itself is now harmless: config/app.php follows
 * APP_TIMEZONE, which every deployment sets to the restaurant's timezone.
 * What is not harmless is the silence. The 2026-08-28 incident lay
 * undetected for four days because an instance served UTC hours while
 * every stored value was correct and nothing anywhere said so. This
 * reports it once per boot, at warning level, with no secret and no
 * connection detail — only what the instance is running on and why.
 */
final class TimezoneIntegrity
{
    /**
     * @param string|null $storedTimezone the timezone the settings store
     *                                    returned, or null when the store
     *                                    could not be read
     * @param string      $effective      the timezone the process ended up on
     * @param string      $fallback       what config/app.php would supply
     *
     * @return bool whether the effective timezone came from the store
     */
    public static function report(?string $storedTimezone, string $effective, string $fallback): bool
    {
        if (is_string($storedTimezone) && trim($storedTimezone) !== '') {
            return true;
        }

        Log::warning(sprintf(
            'Timezone setting unavailable at boot; running on the configured fallback [%s] instead of a stored value. Effective timezone: [%s]. This instance keeps it until it is replaced.',
            $fallback,
            $effective,
        ));

        return false;
    }
}
