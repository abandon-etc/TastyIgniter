<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use Igniter\Admin\Models\Status;
use Igniter\Cart\Models\Order;
use Igniter\Local\Models\Location;
use Igniter\User\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class DeliveryOrdersApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Sanctum::actingAs(User::factory()->create([
            'password' => Hash::make('delivery-api-test'),
        ]), ['orders:*']);
    }

    public function test_delivery_create_is_rejected_before_an_order_is_written(): void
    {
        $before = Order::query()->count();

        $this->postJson(route('igniter.api.orders.store'), $this->deliveryPayload())
            ->assertStatus(422);

        $this->assertSame($before, Order::query()->count());
    }

    public function test_forged_delivery_totals_and_fee_are_rejected(): void
    {
        $payload = $this->deliveryPayload();
        $payload['order_totals'] = [
            ['code' => 'delivery', 'title' => 'Delivery', 'value' => '0.00'],
            ['code' => 'total', 'title' => 'Total', 'value' => '0.01'],
        ];

        $this->postJson(route('igniter.api.orders.store'), $payload)
            ->assertStatus(422);
    }

    public function test_pickup_create_and_update_remain_available(): void
    {
        $response = $this->postJson(route('igniter.api.orders.store'), [
            'first_name' => 'API Pickup',
            'last_name' => 'Test',
            'email' => 'pickup-api@example.invalid',
            'telephone' => '0000000000',
            'order_type' => Location::COLLECTION,
            'status_id' => Status::isForOrder()->firstOrFail()->getKey(),
        ])->assertCreated();

        $orderId = (int) $response->json('data.id');
        $this->putJson(route('igniter.api.orders.update', [$orderId]), [
            'first_name' => 'Updated Pickup',
        ])->assertOk();

        $this->assertSame(Location::COLLECTION, Order::findOrFail($orderId)->order_type);
    }

    public function test_pickup_draft_cannot_be_changed_to_delivery(): void
    {
        $order = Order::factory()->create(['order_type' => Location::COLLECTION]);

        $this->putJson(route('igniter.api.orders.update', [$order->getKey()]), [
            'order_type' => Location::DELIVERY,
            'address' => [
                'address_1' => '100 Test Street',
                'city' => 'Test City',
                'postcode' => 'H0H 0H0',
            ],
        ])->assertStatus(422);

        $this->assertSame(Location::COLLECTION, $order->fresh()->order_type);
    }

    public function test_existing_delivery_cannot_be_rewritten_but_remains_readable(): void
    {
        $order = Order::factory()->create(['order_type' => Location::DELIVERY]);

        $this->putJson(route('igniter.api.orders.update', [$order->getKey()]), [
            'order_type' => Location::COLLECTION,
        ])->assertStatus(422);

        $this->getJson(route('igniter.api.orders.show', [$order->getKey()]))
            ->assertOk()
            ->assertJsonPath('data.attributes.order_type', Location::DELIVERY);
    }

    private function deliveryPayload(): array
    {
        return [
            'first_name' => 'API Delivery',
            'last_name' => 'Test',
            'email' => 'delivery-api@example.invalid',
            'telephone' => '0000000000',
            'order_type' => Location::DELIVERY,
            'status_id' => Status::isForOrder()->firstOrFail()->getKey(),
            'address' => [
                'address_1' => '100 Test Street',
                'city' => 'Test City',
                'postcode' => 'H0H 0H0',
            ],
        ];
    }
}
