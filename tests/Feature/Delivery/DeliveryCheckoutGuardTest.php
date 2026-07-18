<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Delivery\DeliveryCheckoutGuard;
use App\Delivery\Exceptions\DeliveryUnavailableException;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationSettings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

final class DeliveryCheckoutGuardTest extends TestCase
{
    public function test_checkout_revalidates_delivery_before_order_save(): void
    {
        $location = $this->setCurrentLocation(true, true);
        config()->set('delivery.enabled', true);
        $location->updateOrderType(Location::DELIVERY);

        config()->set('delivery.enabled', false);

        $this->expectException(DeliveryUnavailableException::class);
        app(DeliveryCheckoutGuard::class)->handle();
    }

    public function test_pickup_checkout_is_not_rejected_by_delivery_gate(): void
    {
        $location = $this->setCurrentLocation(true, true);
        config()->set('delivery.enabled', false);
        $location->updateOrderType(Location::COLLECTION);

        app(DeliveryCheckoutGuard::class)->handle();

        $this->assertSame(Location::COLLECTION, $location->orderType());
    }

    private function setCurrentLocation(bool $deliveryEnabled, bool $collectionEnabled): LocationService
    {
        $model = new Location;
        $model->setRelation('settings', new EloquentCollection([
            new LocationSettings([
                'item' => 'delivery',
                'data' => ['is_enabled' => $deliveryEnabled],
            ]),
            new LocationSettings([
                'item' => 'collection',
                'data' => ['is_enabled' => $collectionEnabled],
            ]),
        ]));

        /** @var LocationService $location */
        $location = resolve('location');
        $location->setSessionKey('delivery_checkout_test');
        $location->setModel($model);

        return $location;
    }
}
