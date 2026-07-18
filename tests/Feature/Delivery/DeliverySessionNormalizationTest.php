<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Delivery\DeliveryAvailabilityGate;
use App\Delivery\Exceptions\DeliveryUnavailableException;
use Igniter\Flame\Geolite\Model\Location as UserLocation;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationSettings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Mockery;
use Tests\TestCase;

final class DeliverySessionNormalizationTest extends TestCase
{
    private LocationService $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->location = resolve('location');
        $this->location->setSessionKey('delivery_gate_test');
    }

    public function test_stale_delivery_session_falls_back_without_changing_cart_contents(): void
    {
        config()->set('delivery.enabled', false);
        $this->setLocation(true, true);
        $cart = [
            'items' => [['id' => 42, 'qty' => 3, 'price' => '12.50']],
            'coupon' => 'UNCHANGED',
        ];
        session()->put('cart.location-test', $cart);
        session()->put('birthday_booking.test', ['slot' => '12-16']);
        session()->put('reservation.test', ['date' => '2099-01-01']);
        $this->location->putSession('orderType', Location::DELIVERY);
        $this->location->putSession('delivery-timeslot', ['dateTime' => '2099-01-01 12:00:00']);
        $this->location->putSession('area', 123);
        $this->location->putSession('position', UserLocation::createFromArray([
            'address' => 'Test only',
        ]));

        $changed = app(DeliveryAvailabilityGate::class)->normalizeStaleSession($this->location);

        $this->assertTrue($changed);
        $this->assertSame(Location::COLLECTION, $this->location->getSession('orderType'));
        $this->assertNull($this->location->getSession('delivery-timeslot'));
        $this->assertNull($this->location->getSession('area'));
        $this->assertNull($this->location->getSession('position'));
        $this->assertSame($cart, session('cart.location-test'));
        $this->assertSame(['slot' => '12-16'], session('birthday_booking.test'));
        $this->assertSame(['date' => '2099-01-01'], session('reservation.test'));
    }

    public function test_location_level_disable_also_falls_back_to_collection(): void
    {
        config()->set('delivery.enabled', true);
        $this->setLocation(false, true);
        $this->location->putSession('orderType', Location::DELIVERY);

        app(DeliveryAvailabilityGate::class)->normalizeStaleSession($this->location);

        $this->assertSame(Location::COLLECTION, $this->location->getSession('orderType'));
    }

    public function test_missing_order_type_defaults_to_collection_while_delivery_is_off(): void
    {
        config()->set('delivery.enabled', false);
        $this->setLocation(true, true);

        app(DeliveryAvailabilityGate::class)->normalizeStaleSession($this->location);

        $this->assertSame(Location::COLLECTION, $this->location->getSession('orderType'));
    }

    public function test_missing_current_location_is_left_for_upstream_setup_handling(): void
    {
        $location = Mockery::mock(LocationService::class);
        $location->shouldReceive('current')->once()->andReturnNull();
        $location->shouldNotReceive('getSession');

        $changed = app(DeliveryAvailabilityGate::class)->normalizeStaleSession($location);

        $this->assertFalse($changed);
    }

    public function test_collection_session_is_not_rewritten(): void
    {
        config()->set('delivery.enabled', false);
        $this->setLocation(true, true);
        $this->location->putSession('orderType', Location::COLLECTION);

        $changed = app(DeliveryAvailabilityGate::class)->normalizeStaleSession($this->location);

        $this->assertFalse($changed);
        $this->assertSame(Location::COLLECTION, $this->location->getSession('orderType'));
    }

    public function test_disabled_collection_session_is_cleared_without_throwing(): void
    {
        config()->set('delivery.enabled', false);
        $this->setLocation(true, false);
        $this->location->putSession('orderType', Location::COLLECTION);

        $changed = app(DeliveryAvailabilityGate::class)->normalizeStaleSession($this->location);

        $this->assertTrue($changed);
        $this->assertNull($this->location->getSession('orderType'));
    }

    public function test_no_collection_clears_invalid_selection_without_throwing(): void
    {
        config()->set('delivery.enabled', false);
        $this->setLocation(true, false);
        $this->location->putSession('orderType', Location::DELIVERY);
        $this->location->putSession('delivery-timeslot', ['dateTime' => '2099-01-01 12:00:00']);
        $this->location->putSession('area', 123);
        $this->location->putSession('position', UserLocation::createFromArray([
            'address' => 'Test only',
        ]));

        $changed = app(DeliveryAvailabilityGate::class)->normalizeStaleSession($this->location);

        $this->assertTrue($changed);
        $this->assertNull($this->location->getSession('orderType'));
        $this->assertNull($this->location->getSession('delivery-timeslot'));
        $this->assertNull($this->location->getSession('area'));
        $this->assertNull($this->location->getSession('position'));
    }

    public function test_spoofed_delivery_update_is_normalized_and_rejected(): void
    {
        config()->set('delivery.enabled', false);
        $this->setLocation(true, true);

        try {
            $this->location->updateOrderType(Location::DELIVERY);
            $this->fail('Expected Delivery to be rejected.');
        } catch (DeliveryUnavailableException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
            $this->assertStringNotContainsString('SQLSTATE', $exception->getMessage());
            $this->assertStringNotContainsString('delivery.enabled', $exception->getMessage());
            $this->assertSame(Location::COLLECTION, $this->location->getSession('orderType'));
        }
    }

    private function setLocation(bool $deliveryEnabled, bool $collectionEnabled): void
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
        $this->location->setModel($model);
    }
}
