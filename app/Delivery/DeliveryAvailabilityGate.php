<?php

declare(strict_types=1);

namespace App\Delivery;

use App\Delivery\Exceptions\DeliveryUnavailableException;
use App\Delivery\Exceptions\NoFulfillmentMethodAvailableException;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;
use Illuminate\Support\Collection;

final class DeliveryAvailabilityGate
{
    public function isGloballyEnabled(): bool
    {
        return config('delivery.enabled') === true;
    }

    public function isEnabledForLocation(?Location $location): bool
    {
        return $location instanceof Location
            && $this->isGloballyEnabled()
            && (bool) $location->getSettings('delivery.is_enabled', 1);
    }

    public function isCollectionEnabled(?Location $location): bool
    {
        return $location instanceof Location
            && (bool) $location->getSettings('collection.is_enabled', 1);
    }

    public function availableOrderTypes(Location $location, Collection $orderTypes): Collection
    {
        if ($this->isEnabledForLocation($location)) {
            return $orderTypes;
        }

        return $orderTypes->forget(Location::DELIVERY);
    }

    public function assertDeliveryEnabled(?Location $location): void
    {
        if (! $this->isEnabledForLocation($location)) {
            throw new DeliveryUnavailableException;
        }
    }

    /**
     * Replace a stale Delivery selection without touching cart contents.
     */
    public function normalizeOrderType(LocationService $location): bool
    {
        $model = $location->current();

        if (! $model instanceof Location) {
            return false;
        }

        $storedOrderType = $location->getSession('orderType');

        if ($storedOrderType !== Location::DELIVERY
            && ! ($storedOrderType === null && ! $this->isEnabledForLocation($model))) {
            return false;
        }

        if ($this->isEnabledForLocation($model)) {
            return false;
        }

        $this->clearDeliveryState($location);

        if (! $this->isCollectionEnabled($model)) {
            $location->updateOrderType();

            throw new NoFulfillmentMethodAvailableException;
        }

        $location->updateOrderType(Location::COLLECTION);

        return true;
    }

    public function clearDeliveryState(LocationService $location): void
    {
        $location->forgetSession('delivery-timeslot');
        $location->forgetSession('position');
        $location->clearCoveredArea();
    }
}
