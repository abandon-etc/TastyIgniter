<?php

declare(strict_types=1);

namespace App\Delivery;

use App\Delivery\Exceptions\DeliveryApiWriteUnavailableException;
use Igniter\Api\ApiResources\Repositories\OrderRepository;
use Igniter\Cart\Models\Order;

final class DeliveryApiWriteGuard
{
    public function handle(mixed $repository, mixed $model, array $attributes = []): void
    {
        if (! $repository instanceof OrderRepository || ! $model instanceof Order) {
            return;
        }

        $requestedOrderType = $attributes['order_type'] ?? $model->order_type;
        $isExistingDelivery = $model->exists && $model->order_type === Order::DELIVERY;

        if ($requestedOrderType === Order::DELIVERY || $isExistingDelivery) {
            throw new DeliveryApiWriteUnavailableException;
        }
    }
}
