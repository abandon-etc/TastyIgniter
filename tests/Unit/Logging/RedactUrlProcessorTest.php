<?php

declare(strict_types=1);

namespace Tests\Unit\Logging;

use App\Logging\RedactUrlProcessor;
use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RedactUrlProcessorTest extends TestCase
{
    /**
     * The message an upstream geocoder provider logged on Canada staging when
     * the Google Geocoding API was not activated on the project. Project-owned
     * code never logs a raw provider exception, so this arrives from vendor
     * code that must not be patched.
     */
    private const OBSERVED_LEAK = 'Provider "Google Maps" could not geocode address, '
        .'"API access denied. Message: This API is not activated on your API project. '
        .'You may need to enable this API in the Google Cloud Console: '
        .'https://console.cloud.google.com/apis/library?filter=category:maps. Learn more"';

    public function test_it_removes_a_provider_url_from_the_message(): void
    {
        $record = $this->process($this->record(self::OBSERVED_LEAK));

        $this->assertStringNotContainsString('https://', $record->message);
        $this->assertStringContainsString('[redacted-url]', $record->message);
    }

    public function test_it_keeps_the_surrounding_diagnostic_text(): void
    {
        $record = $this->process($this->record(self::OBSERVED_LEAK));

        $this->assertStringContainsString('API access denied', $record->message);
        $this->assertStringContainsString('Google Maps', $record->message);
    }

    #[DataProvider('urlSamples')]
    public function test_it_removes_urls_of_either_scheme(string $message): void
    {
        $record = $this->process($this->record($message));

        $this->assertStringNotContainsString('://', $record->message);
    }

    public function test_it_redacts_nested_context_strings(): void
    {
        $record = $this->process($this->record('failed', [
            'nested' => ['url' => 'see https://example.com/a?b=c now'],
        ]));

        $this->assertSame('see [redacted-url] now', $record->context['nested']['url']);
    }

    public function test_it_redacts_extra_strings(): void
    {
        $record = $this->process($this->record('failed', [], [
            'note' => 'trace at http://internal.example/x',
        ]));

        $this->assertStringNotContainsString('://', $record->extra['note']);
    }

    public function test_it_leaves_non_string_context_values_alone(): void
    {
        $record = $this->process($this->record('failed', [
            'count' => 42,
            'enabled' => true,
            'missing' => null,
        ]));

        $this->assertSame(42, $record->context['count']);
        $this->assertTrue($record->context['enabled']);
        $this->assertNull($record->context['missing']);
    }

    public function test_it_leaves_a_record_with_no_url_unchanged(): void
    {
        $original = $this->record('no url here', ['event' => 'delivery_geocoder_empty_result']);
        $record = $this->process($original);

        $this->assertSame('no url here', $record->message);
        $this->assertSame(['event' => 'delivery_geocoder_empty_result'], $record->context);
    }

    public function test_it_preserves_channel_level_and_datetime(): void
    {
        $original = $this->record(self::OBSERVED_LEAK);
        $record = $this->process($original);

        $this->assertSame('staging', $record->channel);
        $this->assertSame(Level::Error, $record->level);
        $this->assertSame($original->datetime, $record->datetime);
    }

    /**
     * Documented limitation. Monolog normalizes a Throwable in context at
     * format time, which is after processors run, so this processor cannot
     * reach inside an exception object. Asserted so the gap stays visible
     * rather than being assumed closed.
     */
    public function test_it_does_not_reach_inside_a_throwable_in_context(): void
    {
        $record = $this->process($this->record('failed', [
            'exception' => new RuntimeException('boom https://console.cloud.google.com/x'),
        ]));

        $this->assertStringContainsString(
            'https://',
            $record->context['exception']->getMessage(),
        );
    }

    public static function urlSamples(): array
    {
        return [
            'https' => ['see https://example.com/path?q=1 end'],
            'http' => ['see http://example.com end'],
            'uppercase scheme' => ['see HTTPS://EXAMPLE.COM end'],
            'two urls' => ['https://a.example and https://b.example'],
        ];
    }

    private function process(LogRecord $record): LogRecord
    {
        return (new RedactUrlProcessor())($record);
    }

    private function record(string $message, array $context = [], array $extra = []): LogRecord
    {
        return new LogRecord(
            new DateTimeImmutable(),
            'staging',
            Level::Error,
            $message,
            $context,
            $extra,
        );
    }
}
