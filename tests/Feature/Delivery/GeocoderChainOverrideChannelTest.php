<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use ReflectionProperty;
use Tests\TestCase;

/**
 * Covers what the unit test cannot: that the override is registered against
 * the right container abstract, and that the package configuration still has
 * the shape the unit fixture mirrors.
 *
 * Resolving the geocoder for real is deliberately not attempted here. The
 * package's own resolving callback reads the settings table and the default
 * country row, so it needs a database that the test environment does not
 * provide. The resolve-and-apply path is verified on a deployed revision by
 * reading back which provider answered, not asserted here on faith.
 */
final class GeocoderChainOverrideChannelTest extends TestCase
{
    public function test_the_package_still_ships_the_providers_the_unit_fixture_mirrors(): void
    {
        $providers = config('igniter-geocoder.providers');

        $this->assertIsArray($providers);
        $this->assertArrayHasKey('google', $providers);
        $this->assertArrayHasKey('nominatim', $providers);
    }

    public function test_the_override_is_registered_against_the_geocoder_abstract(): void
    {
        $property = new ReflectionProperty($this->app, 'afterResolvingCallbacks');
        $callbacks = $property->getValue($this->app);

        $this->assertArrayHasKey(
            'geocoder',
            $callbacks,
            'Nothing is registered to run after the geocoder resolves.',
        );
        $this->assertNotEmpty($callbacks['geocoder']);
    }

    /**
     * afterResolving rather than resolving is the point: TastyIgniter's own
     * resolving callback rewrites the geocoder configuration from the settings
     * table, so an override registered as resolving would depend on provider
     * registration order to win.
     */
    public function test_the_override_is_not_registered_as_a_resolving_callback(): void
    {
        $property = new ReflectionProperty($this->app, 'resolvingCallbacks');
        $callbacks = $property->getValue($this->app);

        $ours = array_filter(
            $callbacks['geocoder'] ?? [],
            static function (callable $callback): bool {
                $reflection = new \ReflectionFunction($callback);

                return str_contains((string)$reflection->getFileName(), 'AppServiceProvider');
            },
        );

        $this->assertSame([], $ours);
    }

    public function test_the_delivery_config_exposes_both_override_keys(): void
    {
        $this->assertTrue(config()->has('delivery.geocoder.driver'));
        $this->assertTrue(config()->has('delivery.geocoder.providers'));
    }

    public function test_both_override_keys_default_to_null(): void
    {
        $this->assertNull(env('DELIVERY_GEOCODER_DRIVER'));
        $this->assertNull(env('DELIVERY_GEOCODER_PROVIDERS'));
        $this->assertNull(config('delivery.geocoder.driver'));
        $this->assertNull(config('delivery.geocoder.providers'));
    }
}
