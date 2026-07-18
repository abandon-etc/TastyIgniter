<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Delivery\Exceptions\DeliveryApiWriteUnavailableException;
use Igniter\Api\ApiResources\Repositories\OrderRepository;
use Igniter\Cart\Models\Order;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class DeliveryApiWriteGuardTest extends TestCase
{
    public function test_delivery_create_event_fails_closed_with_422(): void
    {
        $this->expectException(DeliveryApiWriteUnavailableException::class);

        Event::dispatch('api.repository.beforeCreate', [
            app(OrderRepository::class),
            new Order,
            ['order_type' => Order::DELIVERY],
        ], true);
    }

    public function test_draft_update_cannot_be_changed_to_delivery(): void
    {
        $order = new Order;
        $order->setRawAttributes(['order_type' => Order::COLLECTION], true);
        $order->exists = true;

        $this->expectException(DeliveryApiWriteUnavailableException::class);

        Event::dispatch('api.repository.beforeUpdate', [
            app(OrderRepository::class),
            $order,
            ['order_type' => Order::DELIVERY],
        ], true);
    }

    public function test_existing_delivery_cannot_be_rewritten_as_collection(): void
    {
        $order = new Order;
        $order->setRawAttributes(['order_type' => Order::DELIVERY], true);
        $order->exists = true;

        $this->expectException(DeliveryApiWriteUnavailableException::class);

        Event::dispatch('api.repository.beforeUpdate', [
            app(OrderRepository::class),
            $order,
            ['order_type' => Order::COLLECTION],
        ], true);
    }

    public function test_pickup_create_and_update_events_are_unchanged(): void
    {
        $repository = app(OrderRepository::class);
        $newOrder = new Order;
        $existingOrder = new Order;
        $existingOrder->setRawAttributes(['order_type' => Order::COLLECTION], true);
        $existingOrder->exists = true;

        $createResult = Event::dispatch('api.repository.beforeCreate', [
            $repository,
            $newOrder,
            ['order_type' => Order::COLLECTION],
        ], true);
        $updateResult = Event::dispatch('api.repository.beforeUpdate', [
            $repository,
            $existingOrder,
            ['first_name' => 'Staging Test'],
        ], true);

        $this->assertNull($createResult);
        $this->assertNull($updateResult);
    }

    public function test_api_error_is_stable_and_does_not_leak_request_details(): void
    {
        try {
            Event::dispatch('api.repository.beforeCreate', [
                app(OrderRepository::class),
                new Order,
                [
                    'order_type' => Order::DELIVERY,
                    'address' => ['address_1' => 'Do not echo this test address'],
                    'order_totals' => [['value' => 0]],
                ],
            ], true);
            $this->fail('Expected Delivery API write to be rejected.');
        } catch (DeliveryApiWriteUnavailableException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
            $this->assertSame('Delivery orders cannot be written through this API.', $exception->getMessage());
            $this->assertStringNotContainsString('SQLSTATE', $exception->getMessage());
            $this->assertStringNotContainsString('address', $exception->getMessage());
            $this->assertStringNotContainsString('token', $exception->getMessage());
        }
    }
}
