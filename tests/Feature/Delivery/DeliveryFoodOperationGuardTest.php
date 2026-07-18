<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Delivery\DeliveryAvailabilityGate;
use Igniter\Cart\Classes\CartManager;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationSettings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

final class DeliveryFoodOperationGuardTest extends TestCase
{
    public function test_cart_validation_fails_when_no_fulfillment_method_is_available(): void
    {
        config()->set('delivery.enabled', false);
        $location = $this->setCurrentLocation(true, false);
        $location->putSession('orderType', Location::DELIVERY);
        app(DeliveryAvailabilityGate::class)->normalizeStaleSession($location);

        try {
            (new CartManager)->validateOrderTime();
            $this->fail('Expected cart validation to reject the missing fulfillment method.');
        } catch (ApplicationException $exception) {
            $this->assertSame(lang('igniter.local::default.alert_order_type_required'), $exception->getMessage());
            $this->assertNull($location->getSession('orderType'));
        }
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
        $location->clearInternalCache();
        $location->setSessionKey('delivery_food_operation_test');
        $location->setModel($model);

        return $location;
    }
}
