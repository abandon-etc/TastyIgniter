<?php

declare(strict_types=1);

namespace App\Payments;

use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;

/**
 * Pins the one payment rule that a deployment could otherwise downgrade
 * silently.
 *
 * CLAUDE_HANDOFF.md §7 states that login/registration and e-mail
 * verification are required before payment in the final public flow.
 * payments.require_verified_email exists so test revisions can skip the
 * verification round-trip, which means a production revision could ship
 * with it off and nothing would say so.
 *
 * Fails closed, the same shape as App\Mail\MailTestRedirect: a production
 * environment with verification disabled stops the application at boot,
 * so the revision fails its health check instead of quietly accepting
 * unverified customers into a real payment flow.
 */
final class PaymentGateConfiguration
{
    public const string CONFIG_KEY = 'payments.require_verified_email';

    public static function assert(Repository $config): void
    {
        if ($config->get('app.env') !== 'production') {
            return;
        }

        if (!$config->get(self::CONFIG_KEY, true)) {
            throw new InvalidArgumentException(
                'PAYMENTS_REQUIRE_VERIFIED_EMAIL is off in production; refusing to start rather than admit unverified customers to a payment flow.',
            );
        }
    }
}
