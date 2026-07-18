<?php

declare(strict_types=1);

namespace App\Delivery;

use Igniter\Cart\Classes\OrderTypes;
use Igniter\System\Actions\ModelAction;
use Illuminate\Support\Collection;

final class LocationDeliveryAction extends ModelAction
{
    public function availableOrderTypes(): Collection
    {
        $orderTypes = resolve(OrderTypes::class)->makeOrderTypes($this->model);

        return resolve(DeliveryAvailabilityGate::class)
            ->availableOrderTypes($this->model, $orderTypes);
    }

    public function hasDelivery(): bool
    {
        return resolve(DeliveryAvailabilityGate::class)->isEnabledForLocation($this->model);
    }
}
