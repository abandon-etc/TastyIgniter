<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\TimezoneIntegrity;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Pins the fix for the 2026-08-28 clock incident: the application's
 * timezone fallback must follow APP_TIMEZONE rather than a hardcoded
 * value, and an instance that could not read the stored setting must say
 * so instead of running four hours off in silence.
 */
final class TimezoneConfigurationTest extends TestCase
{
    public function test_the_timezone_config_is_not_hardcoded(): void
    {
        $source = file_get_contents(base_path('config/app.php'));

        $this->assertStringContainsString("env('APP_TIMEZONE'", $source,
            'config/app.php must read APP_TIMEZONE; a hardcoded timezone is what let an instance serve UTC hours.');
        $this->assertDoesNotMatchRegularExpression("/'timezone'\s*=>\s*'[A-Za-z\/_]+'/", $source,
            'The timezone key must not be assigned a literal string.');
    }

    public function test_the_config_file_returns_the_environment_value(): void
    {
        $previousEnv = $_ENV['APP_TIMEZONE'] ?? null;
        $previousServer = $_SERVER['APP_TIMEZONE'] ?? null;

        $_ENV['APP_TIMEZONE'] = 'America/Toronto';
        $_SERVER['APP_TIMEZONE'] = 'America/Toronto';

        try {
            $config = require base_path('config/app.php');

            $this->assertSame('America/Toronto', $config['timezone']);
        } finally {
            $this->restore('APP_TIMEZONE', $previousEnv, $previousServer);
        }
    }

    public function test_the_php_ini_carries_a_second_line_of_defence(): void
    {
        $ini = file_get_contents(base_path('docker/render/php-production.ini'));

        $this->assertMatchesRegularExpression('/^date\.timezone=\S+/m', $ini,
            'The deployed php.ini must set date.timezone so a bare PHP process is not on UTC.');
    }

    public function test_a_stored_timezone_is_reported_as_authoritative_and_logs_nothing(): void
    {
        Log::spy();

        $this->assertTrue(TimezoneIntegrity::report('America/Toronto', 'America/Toronto', 'America/Toronto'));

        Log::shouldNotHaveReceived('warning');
    }

    public function test_an_unavailable_setting_is_reported_and_logged(): void
    {
        Log::spy();

        $this->assertFalse(TimezoneIntegrity::report(null, 'America/Toronto', 'America/Toronto'));

        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn (string $message): bool => str_contains($message, 'Timezone setting unavailable at boot')
                && str_contains($message, 'America/Toronto'),
        );
    }

    public function test_a_blank_stored_timezone_counts_as_unavailable(): void
    {
        Log::spy();

        $this->assertFalse(TimezoneIntegrity::report('   ', 'UTC', 'America/Toronto'));

        Log::shouldHaveReceived('warning')->once();
    }

    private function restore(string $key, ?string $env, ?string $server): void
    {
        if ($env === null) {
            unset($_ENV[$key]);
        } else {
            $_ENV[$key] = $env;
        }

        if ($server === null) {
            unset($_SERVER[$key]);
        } else {
            $_SERVER[$key] = $server;
        }
    }
}
