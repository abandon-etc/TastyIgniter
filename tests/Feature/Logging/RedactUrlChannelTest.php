<?php

declare(strict_types=1);

namespace Tests\Feature\Logging;

use App\Logging\RedactUrlProcessor;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\TestHandler;
use Monolog\Logger as Monolog;
use RuntimeException;
use Tests\TestCase;

/**
 * Covers the wiring, which the unit test cannot reach.
 *
 * Tests\Unit\Logging\RedactUrlProcessorTest proves the processor redacts.
 * This proves the configured channel actually installs it, and records what
 * the real formatter does with a record once it has.
 */
final class RedactUrlChannelTest extends TestCase
{
    private const URL = 'https://console.cloud.google.com/apis/library';

    public function test_the_stderr_channel_installs_the_processor(): void
    {
        $monolog = Log::channel('stderr')->getLogger();

        $this->assertInstanceOf(Monolog::class, $monolog);
        $this->assertContainsOnlyInstancesOf(
            RedactUrlProcessor::class,
            array_filter(
                $monolog->getProcessors(),
                static fn($p): bool => $p instanceof RedactUrlProcessor,
            ),
        );
        $this->assertNotEmpty(
            array_filter(
                $monolog->getProcessors(),
                static fn($p): bool => $p instanceof RedactUrlProcessor,
            ),
            'The stderr channel is configured without RedactUrlProcessor.',
        );
    }

    public function test_the_single_channel_installs_the_processor(): void
    {
        $monolog = Log::channel('single')->getLogger();

        $this->assertNotEmpty(
            array_filter(
                $monolog->getProcessors(),
                static fn($p): bool => $p instanceof RedactUrlProcessor,
            ),
            'The single channel is configured without RedactUrlProcessor.',
        );
    }

    public function test_a_url_in_the_message_is_redacted_before_formatting(): void
    {
        $formatted = $this->captureFormatted('boom '.self::URL.'/message', []);

        $this->assertStringNotContainsString('/message', $formatted);
        $this->assertStringContainsString('[redacted-url]', $formatted);
    }

    public function test_a_url_in_a_context_string_is_redacted_before_formatting(): void
    {
        $formatted = $this->captureFormatted('failed', [
            'note' => 'see '.self::URL.'/context',
        ]);

        $this->assertStringNotContainsString('/context', $formatted);
    }

    /**
     * The observed leak on Canada staging arrived this way. The Orange theme's
     * SearchesNearby concern calls Log::error(implode(PHP_EOL,
     * Geocoder::getLogs())), so the provider's raw text is the record message,
     * which this processor does reach.
     */
    public function test_a_provider_log_forwarded_as_a_message_is_redacted(): void
    {
        $providerLog = sprintf(
            'Provider "%s" could not geocode address, "API access denied. '
            .'You may need to enable this API in the Google Cloud Console: %s".',
            'Google Maps',
            self::URL.'?filter=category:maps',
        );

        $formatted = $this->captureFormatted($providerLog, []);

        $this->assertStringNotContainsString('https://', $formatted);
        $this->assertStringContainsString('could not geocode address', $formatted);
    }

    /**
     * Documented gap, asserted so it stays visible. Monolog normalises a
     * Throwable in context at format time, after processors run, so a URL
     * inside an exception object survives. Project-owned code does not pass
     * exceptions into log context; Laravel's exception handler does.
     */
    public function test_a_url_inside_a_throwable_in_context_still_survives(): void
    {
        $formatted = $this->captureFormatted('failed', [
            'exception' => new RuntimeException('inner '.self::URL.'/inside'),
        ]);

        $this->assertStringContainsString('/inside', $formatted);
    }

    /**
     * Formats a record through the real stderr handlers, processors, and
     * formatter, without writing to the real stream.
     */
    private function captureFormatted(string $message, array $context): string
    {
        $monolog = Log::channel('stderr')->getLogger();
        $formatter = $monolog->getHandlers()[0]->getFormatter();

        $handler = new TestHandler;
        $handler->setFormatter($formatter);

        $probe = new Monolog('staging');
        $probe->pushHandler($handler);
        foreach ($monolog->getProcessors() as $processor) {
            $probe->pushProcessor($processor);
        }

        $probe->error($message, $context);

        return $formatter->format($handler->getRecords()[0]);
    }
}
