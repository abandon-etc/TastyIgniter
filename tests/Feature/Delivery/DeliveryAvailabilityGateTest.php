<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Delivery\DeliveryAvailabilityGate;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationSettings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

final class DeliveryAvailabilityGateTest extends TestCase
{
    public function test_delivery_defaults_to_disabled(): void
    {
        $this->assertFalse(config('delivery.enabled'));
    }

    public function test_global_false_removes_delivery_but_keeps_collection(): void
    {
        config()->set('delivery.enabled', false);

        $types = $this->location(true, true)->availableOrderTypes();

        $this->assertFalse($types->has(Location::DELIVERY));
        $this->assertTrue($types->has(Location::COLLECTION));
    }

    public function test_location_false_removes_delivery_when_global_flag_is_true(): void
    {
        config()->set('delivery.enabled', true);

        $types = $this->location(false, true)->availableOrderTypes();

        $this->assertFalse($types->has(Location::DELIVERY));
        $this->assertTrue($types->has(Location::COLLECTION));
    }

    public function test_both_flags_true_leave_delivery_for_existing_server_validation(): void
    {
        config()->set('delivery.enabled', true);

        $types = $this->location(true, true)->availableOrderTypes();

        $this->assertTrue($types->has(Location::DELIVERY));
        $this->assertTrue($types->has(Location::COLLECTION));
    }

    public function test_both_fulfillment_methods_disabled_return_no_active_types(): void
    {
        config()->set('delivery.enabled', false);
        $location = $this->location(true, false);

        $activeTypes = $location->availableOrderTypes()
            ->filter(static fn ($orderType): bool => ! $orderType->isDisabled());

        $this->assertTrue($activeTypes->isEmpty());
    }

    public function test_gate_reads_cached_config_value_not_environment_at_runtime(): void
    {
        config()->set('delivery.enabled', true);
        $gate = app(DeliveryAvailabilityGate::class);
        $location = $this->location(true, true);

        putenv('DELIVERY_ENABLED=false');

        try {
            $this->assertTrue($gate->isEnabledForLocation($location));
        } finally {
            putenv('DELIVERY_ENABLED');
        }
    }

    private function location(bool $deliveryEnabled, bool $collectionEnabled): Location
    {
        $location = new Location;
        $location->setRelation('settings', new EloquentCollection([
            new LocationSettings([
                'item' => 'delivery',
                'data' => ['is_enabled' => $deliveryEnabled],
            ]),
            new LocationSettings([
                'item' => 'collection',
                'data' => ['is_enabled' => $collectionEnabled],
            ]),
        ]));

        return $location;
    }
}
