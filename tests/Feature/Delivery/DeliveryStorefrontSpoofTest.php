<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Http\Middleware\VerifyCsrfToken;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationSettings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class DeliveryStorefrontSpoofTest extends TestCase
{
    public function test_plain_http_delivery_parameter_is_rejected_server_side(): void
    {
        config()->set('delivery.enabled', false);
        $this->withoutMiddleware(VerifyCsrfToken::class);

        Route::middleware('web')->post('/_delivery-gate-test/order-type', function (Request $request) {
            /** @var LocationService $location */
            $location = resolve('location');
            $location->updateOrderType($request->string('order_type')->toString());

            return response()->json(['ok' => true]);
        });

        $response = $this->postJson('/_delivery-gate-test/order-type', [
            'order_type' => 'delivery',
            'delivery_fee' => 0,
        ]);

        $response->assertStatus(422);
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        $this->assertStringNotContainsString('delivery.enabled', $response->getContent());
    }

    public function test_order_type_write_reports_no_fulfillment_when_delivery_and_collection_are_disabled(): void
    {
        config()->set('delivery.enabled', false);
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->setCurrentLocation(true, false);

        Route::middleware('web')->post('/_delivery-gate-test/no-fulfillment', function (Request $request) {
            /** @var LocationService $location */
            $location = resolve('location');
            $location->updateOrderType($request->string('order_type')->toString());

            return response()->json(['ok' => true]);
        });

        $response = $this->postJson('/_delivery-gate-test/no-fulfillment', [
            'order_type' => Location::DELIVERY,
        ]);

        $response->assertStatus(422)
            ->assertSeeText('No fulfillment method is currently available.');
        $this->assertNull(resolve('location')->getSession('orderType'));
    }

    public function test_disabled_collection_write_is_cleared_and_rejected(): void
    {
        config()->set('delivery.enabled', true);
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->setCurrentLocation(true, false);

        Route::middleware('web')->post('/_delivery-gate-test/no-collection', function (Request $request) {
            /** @var LocationService $location */
            $location = resolve('location');
            $location->updateOrderType($request->string('order_type')->toString());

            return response()->json(['ok' => true]);
        });

        $response = $this->postJson('/_delivery-gate-test/no-collection', [
            'order_type' => Location::COLLECTION,
        ]);

        $response->assertStatus(422)
            ->assertSeeText('Pickup is currently unavailable. Please choose delivery.');
        $this->assertNull(resolve('location')->getSession('orderType'));
    }

    private function setCurrentLocation(bool $deliveryEnabled, bool $collectionEnabled): void
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
        $location->setSessionKey('delivery_storefront_spoof_test');
        $location->setModel($model);
        $location->putSession('orderType', Location::DELIVERY);
    }
}
