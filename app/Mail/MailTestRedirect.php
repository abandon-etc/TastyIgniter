<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;

/**
 * Redirects every outgoing message of a deployment to one test address.
 *
 * A 0%-traffic revision that uses a real transport still reads its recipients
 * from the shared database: the owner's site and location addresses receive
 * order, reservation, and registration alerts, and customer addresses receive
 * confirmations. With MAIL_TEST_REDIRECT_TO set on that revision, all of it
 * lands in a single test inbox instead. The revision serving traffic leaves
 * the variable unset and keeps MAIL_MAILER=log until launch.
 *
 * How it works: Laravel applies a global "to" to every mailer it resolves, as
 * Mailer::alwaysTo(). That replaces To, drops Cc and Bcc, and covers every
 * sending path in this application: the template mailables queued through
 * MailHelper, notifications, and direct Mail calls. Setting it here, during
 * provider registration, reaches every mailer rather than only the default
 * one, and resolves nothing at boot.
 *
 * The value travels through config/mail.php, never env() at call time,
 * because the runtime caches configuration.
 *
 * Fails closed: a value that is not an address stops the application at boot
 * with an exception rather than silently sending to real recipients, so a
 * misconfigured test revision fails its health check instead of leaking.
 */
final class MailTestRedirect
{
    public const CONFIG_KEY = 'mail.test_redirect_to';

    public const RECIPIENT_NAME = 'Mail test inbox';

    /**
     * Apply the redirect if the deployment asks for one.
     *
     * @return string|null the address every message is redirected to, or null
     *                     when this deployment has no redirect
     */
    public static function apply(Repository $config): ?string
    {
        $address = trim((string) $config->get(self::CONFIG_KEY, ''));

        if ($address === '') {
            return null;
        }

        if (filter_var($address, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(
                'MAIL_TEST_REDIRECT_TO is set but is not a valid email address; refusing to start rather than send to real recipients.',
            );
        }

        $config->set('mail.to', ['address' => $address, 'name' => self::RECIPIENT_NAME]);

        return $address;
    }
}
