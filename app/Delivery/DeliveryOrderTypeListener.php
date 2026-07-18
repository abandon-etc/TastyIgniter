<?php

declare(strict_types=1);

namespace App\Delivery;

use App\Delivery\Exceptions\DeliveryUnavailableException;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;

final class DeliveryOrderTypeListener
{
    public function __construct(private readonly DeliveryAvailabilityGate $gate) {}

    public function handle(LocationService $location, ?string $code = null): void
    {
        if ($code === Location::COLLECTION && ! $this->gate->isCollectionEnabled($location->current())) {
            $this->gate->normalizeStaleSession($location);
            $this->gate->assertFulfillmentAvailable($location->current());
            $this->gate->assertCollectionEnabled($location->current());
        }

        if ($code !== Location::DELIVERY || $this->gate->isEnabledForLocation($location->current())) {
            return;
        }

        $this->gate->normalizeStaleSession($location);
        $this->gate->assertFulfillmentAvailable($location->current());

        throw new DeliveryUnavailableException;
    }
}
