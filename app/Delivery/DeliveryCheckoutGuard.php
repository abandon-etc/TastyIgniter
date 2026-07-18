<?php

declare(strict_types=1);

namespace App\Delivery;

use Igniter\Local\Classes\Location as LocationService;

final class DeliveryCheckoutGuard
{
    public function __construct(private readonly DeliveryAvailabilityGate $gate) {}

    public function handle(): void
    {
        /** @var LocationService $location */
        $location = resolve('location');

        $this->gate->assertFulfillmentAvailable($location->current());

        if ($location->orderTypeIsDelivery()) {
            $this->gate->assertDeliveryEnabled($location->current());
        }

        if ($location->orderTypeIsCollection()) {
            $this->gate->assertCollectionEnabled($location->current());
        }
    }
}
