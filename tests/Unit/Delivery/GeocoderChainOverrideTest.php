<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery;

use App\Delivery\GeocoderChainOverride;
use Illuminate\Config\Repository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GeocoderChainOverrideTest extends TestCase
{
    public function test_it_changes_nothing_when_the_environment_says_nothing(): void
    {
        $config = $this->config(driver: null, providers: null);
        $before = $config->get('igniter-geocoder');

        GeocoderChainOverride::apply($config);

        $this->assertSame($before, $config->get('igniter-geocoder'));
    }

    #[DataProvider('blankDrivers')]
    public function test_it_ignores_a_blank_driver(?string $driver): void
    {
        $config = $this->config(driver: $driver, providers: null);

        GeocoderChainOverride::apply($config);

        $this->assertSame('chain', $config->get('igniter-geocoder.default'));
    }

    public function test_it_pins_the_driver_when_one_is_named(): void
    {
        $config = $this->config(driver: 'google', providers: null);

        GeocoderChainOverride::apply($config);

        $this->assertSame('google', $config->get('igniter-geocoder.default'));
    }

    public function test_pinning_the_driver_leaves_the_provider_list_alone(): void
    {
        $config = $this->config(driver: 'google', providers: null);
        $before = $config->get('igniter-geocoder.providers');

        GeocoderChainOverride::apply($config);

        $this->assertSame($before, $config->get('igniter-geocoder.providers'));
    }

    public function test_it_selects_only_the_named_provider(): void
    {
        $config = $this->config(driver: null, providers: 'google');
        $googleBefore = $config->get('igniter-geocoder.providers.google');

        GeocoderChainOverride::apply($config);

        $this->assertSame(['google'], array_keys($config->get('igniter-geocoder.providers')));
        $this->assertSame($googleBefore, $config->get('igniter-geocoder.providers.google'));
    }

    public function test_it_preserves_the_order_it_is_given(): void
    {
        $config = $this->config(driver: null, providers: 'nominatim,google');

        GeocoderChainOverride::apply($config);

        $this->assertSame(
            ['nominatim', 'google'],
            array_keys($config->get('igniter-geocoder.providers')),
        );
    }

    public function test_it_trims_whitespace_around_names(): void
    {
        $config = $this->config(driver: null, providers: ' google , nominatim ');

        GeocoderChainOverride::apply($config);

        $this->assertSame(
            ['google', 'nominatim'],
            array_keys($config->get('igniter-geocoder.providers')),
        );
    }

    public function test_it_skips_a_name_that_is_not_configured(): void
    {
        $config = $this->config(driver: null, providers: 'google,made-up');

        GeocoderChainOverride::apply($config);

        $this->assertSame(['google'], array_keys($config->get('igniter-geocoder.providers')));
    }

    /**
     * An empty string is a deliberate empty list, distinct from unset. It is
     * how a deployment says "no provider is available here", which is a
     * configuration rather than an injected failure.
     */
    public function test_an_empty_string_selects_no_providers(): void
    {
        $config = $this->config(driver: null, providers: '');

        GeocoderChainOverride::apply($config);

        $this->assertSame([], $config->get('igniter-geocoder.providers'));
    }

    public static function blankDrivers(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'whitespace' => ['   '],
        ];
    }

    /**
     * Mirrors the shape of the package configuration the override acts on.
     * Tests\Feature\Delivery\GeocoderChainOverrideChannelTest checks that the
     * real configuration still has this shape.
     */
    private function config(?string $driver, ?string $providers): Repository
    {
        return new Repository([
            'delivery' => [
                'geocoder' => [
                    'driver' => $driver,
                    'providers' => $providers,
                ],
            ],
            'igniter-geocoder' => [
                'default' => 'chain',
                'providers' => [
                    'google' => [
                        'endpoints' => ['geocode' => 'https://example.test/g?address=%s'],
                        'locale' => 'en-GB',
                        'region' => 'CA',
                        'apiKey' => 'stored-key',
                    ],
                    'nominatim' => [
                        'endpoints' => ['geocode' => 'https://example.test/n?q=%s'],
                        'locale' => 'en-GB',
                        'region' => 'CA',
                    ],
                ],
                'precision' => 8,
            ],
        ]);
    }
}
