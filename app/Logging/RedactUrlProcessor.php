<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Removes absolute URLs from log records before they are written.
 *
 * Project-owned code never logs a raw provider exception. App\Livewire\
 * DeliveryLocalSearch catches provider failures without binding the exception
 * and logs only an event name and operation. Upstream geocoder providers log
 * their own failures, and a provider's message can carry a URL; vendor code is
 * inspection-only and must not be patched. The redaction therefore has to
 * happen at the log record, which is the last project-owned point before a
 * record reaches a handler.
 *
 * Known limitation: Monolog normalizes a Throwable placed in log context at
 * format time, which is after processors run, so a URL inside an exception
 * object passed as context is not covered here. Only the record message,
 * context strings, and extra strings are redacted.
 */
final class RedactUrlProcessor implements ProcessorInterface
{
    private const URL_PATTERN = '~\bhttps?://[^\s\'"<>]+~i';

    private const REPLACEMENT = '[redacted-url]';

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(
            message: self::redact($record->message),
            context: self::redactAll($record->context),
            extra: self::redactAll($record->extra),
        );
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<array-key, mixed>
     */
    private static function redactAll(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = self::redactAll($value);
            } elseif (is_string($value)) {
                $values[$key] = self::redact($value);
            }
        }

        return $values;
    }

    private static function redact(string $value): string
    {
        return (string) preg_replace(self::URL_PATTERN, self::REPLACEMENT, $value);
    }
}
