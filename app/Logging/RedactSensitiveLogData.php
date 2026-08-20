<?php

declare(strict_types=1);

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Logger as Monolog;

/**
 * Laravel log channel tap that installs RedactUrlProcessor.
 *
 * Referenced from the `tap` key of a channel in config/logging.php.
 */
final class RedactSensitiveLogData
{
    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();

        if ($monolog instanceof Monolog) {
            $monolog->pushProcessor(new RedactUrlProcessor());
        }
    }
}
