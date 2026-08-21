<?php

declare(strict_types=1);

namespace App\Delivery;

use Illuminate\Contracts\Config\Repository;

/**
 * Lets a deployment pin which geocoder providers answer, and in what order.
 *
 * TastyIgniter resolves the geocoder driver and the Google API key from the
 * settings table, which every revision of a service shares. That makes the
 * provider chain a property of the database rather than of the deployment, so
 * two revisions cannot be given different chains, and a chain cannot be pinned
 * for one revision without changing it for the revision serving main traffic.
 *
 * This narrows that. When the environment says nothing, nothing changes: the
 * stored configuration stands exactly as before. When it names a driver or a
 * provider list, that deployment uses it.
 *
 * Why it matters for acceptance: Google and Nominatim can place the same
 * address tens of metres apart, and Delivery Area boundaries are decided at
 * that scale. If a transient Google failure silently falls through to
 * Nominatim, a boundary result cannot be attributed to a known coordinate
 * source. Pinning the chain makes the result attributable.
 *
 * This is provider selection only. It cannot inject a failure, and there is no
 * flag that makes a provider fail. A deployment that names no providers simply
 * has none configured, which is a configuration, not a trapdoor.
 */
final class GeocoderChainOverride
{
    public static function apply(Repository $config): void
    {
        self::applyDriver($config);
        self::applyProviders($config);
    }

    private static function applyDriver(Repository $config): void
    {
        $driver = $config->get('delivery.geocoder.driver');

        if (!is_string($driver) || trim($driver) === '') {
            return;
        }

        $config->set('igniter-geocoder.default', trim($driver));
    }

    private static function applyProviders(Repository $config): void
    {
        $names = $config->get('delivery.geocoder.providers');

        // Unset means "leave the stored chain alone". An empty string is a
        // deliberate empty list and is honoured as one.
        if (!is_string($names)) {
            return;
        }

        $config->set('igniter-geocoder.providers', self::select(
            (array)$config->get('igniter-geocoder.providers', []),
            $names,
        ));
    }

    /**
     * Keeps the named providers, in the order named, carrying each one's
     * existing configuration across untouched. A name that is not already
     * configured is skipped rather than invented, because this selects among
     * providers and does not define them.
     *
     * @param  array<string, mixed>  $configured
     * @return array<string, mixed>
     */
    private static function select(array $configured, string $names): array
    {
        $selected = [];

        foreach (explode(',', $names) as $name) {
            $name = trim($name);

            if ($name !== '' && array_key_exists($name, $configured)) {
                $selected[$name] = $configured[$name];
            }
        }

        return $selected;
    }
}
