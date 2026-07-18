<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Http\Middleware\VerifyCsrfToken;
use Igniter\Local\Classes\Location as LocationService;
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
}
