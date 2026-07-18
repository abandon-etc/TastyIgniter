<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use Igniter\Flame\Geolite\Model\Location as UserLocation;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationSettings;
use Igniter\Main\Classes\ThemeManager;
use Igniter\Main\Models\Theme;
use Igniter\Main\Template\Page;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class DeliveryMiddlewareScopeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Theme::syncAll();
        $themeManager = resolve(ThemeManager::class);
        $themeManager->loadThemes();
        $theme = $themeManager->findTheme('birthday-orange');
        $this->assertNotNull($theme);
        $theme->active = true;
        $themeManager->bootTheme($theme);
        Page::getSourceResolver()->setDefaultSourceName('birthday-orange');
        Event::listen(
            'main.page.beforeRoute',
            static fn (string $fileName): ?Page => Page::load('birthday-orange', $fileName),
        );
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('birthday_booking.enabled', true);
        $app['config']->set('igniter-system.locationMode', Location::LOCATION_CONTEXT_SINGLE);
    }

    public function test_unrelated_real_web_routes_remain_available_when_all_food_fulfillment_is_disabled(): void
    {
        config()->set('delivery.enabled', false);
        $location = $this->setCurrentLocation(true, false);

        $routes = [
            'homepage' => '/',
            'Birthday Booking' => '/default/reservation',
            'restaurant reservation account' => '/account/reservations',
            'login' => '/login',
            'content page' => '/about-us',
        ];

        foreach ($routes as $name => $uri) {
            $this->putStaleDeliveryState($location);

            $response = $this->get($uri);

            $this->assertNotSame(422, $response->getStatusCode(), $name.' was blocked by Delivery middleware.');
            $this->assertLessThan(400, $response->getStatusCode(), $name.' was not available through its real route.');
            $this->assertNull($location->getSession('orderType'));
            $this->assertNull($location->getSession('delivery-timeslot'));
            $this->assertNull($location->getSession('area'));
            $this->assertNull($location->getSession('position'));
        }
    }

    public function test_real_homepage_request_falls_back_to_collection_without_touching_other_workflows(): void
    {
        config()->set('delivery.enabled', false);
        $location = $this->setCurrentLocation(true, true);
        $cart = [
            'items' => [['id' => 42, 'qty' => 3, 'price' => '12.50']],
            'coupon' => 'UNCHANGED',
        ];
        session()->put('cart.location-test', $cart);
        session()->put('birthday_booking.test', ['slot' => '12-16']);
        session()->put('reservation.test', ['date' => '2099-01-01']);
        $this->putStaleDeliveryState($location);

        $this->followingRedirects()->get('/')->assertOk();

        $this->assertSame(Location::COLLECTION, $location->getSession('orderType'));
        $this->assertSame($cart, session('cart.location-test'));
        $this->assertSame(['slot' => '12-16'], session('birthday_booking.test'));
        $this->assertSame(['date' => '2099-01-01'], session('reservation.test'));
    }

    private function setCurrentLocation(bool $deliveryEnabled, bool $collectionEnabled): LocationService
    {
        $model = Location::getDefault() ?? Location::query()->firstOrFail();
        $model->loadMissing('settings');
        $settings = $model->settings
            ->reject(fn (LocationSettings $setting): bool => in_array($setting->item, ['delivery', 'collection'], true))
            ->values();
        $settings->push(
            new LocationSettings([
                'item' => 'delivery',
                'data' => ['is_enabled' => $deliveryEnabled],
            ]),
            new LocationSettings([
                'item' => 'collection',
                'data' => ['is_enabled' => $collectionEnabled],
            ]),
        );
        $model->setRelation('settings', new EloquentCollection($settings->all()));

        /** @var LocationService $location */
        $location = resolve('location');
        $location->clearInternalCache();
        $location->setSessionKey('delivery_middleware_scope_test');
        $location->setModel($model);

        return $location;
    }

    private function putStaleDeliveryState(LocationService $location): void
    {
        $location->putSession('orderType', Location::DELIVERY);
        $location->putSession('delivery-timeslot', ['dateTime' => '2099-01-01 12:00:00']);
        $location->putSession('area', 123);
        $location->putSession('position', UserLocation::createFromArray([
            'address' => 'Test only',
        ]));
    }
}
