<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Delivery\DeliveryOrderType;
use Igniter\Cart\Facades\Cart;
use Igniter\Local\Classes\CoveredArea;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationArea;
use Igniter\Local\Models\LocationSettings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Mockery;
use Tests\TestCase;

/**
 * The project's Delivery order type reads the minimum order total the way the
 * owner's settings mean it. See App\Delivery\DeliveryOrderType.
 *
 * The fee rules used here are the ones stored for D3B, unchanged: "free at or
 * above CA$80.00" then "CA$5.00 below CA$80.00". The stored minimum is
 * CA$20.00. Under the vendor's own Delivery class this shape enforces CA$80.00
 * (pinned in DeliveryAreaRuleMinimumTest); under the override it enforces the
 * stored CA$20.00.
 *
 * Two consumption points are asserted separately, because both were wrong
 * together: Location::minimumOrderTotal(), which the basket's "Min. Order
 * Amount" label prints, and Location::checkMinimumOrderTotal(), which
 * CartManager::cartTotalIsBelowMinimumOrder() calls to disable the checkout
 * button. CartManager itself is not constructed here: its constructor reads
 * the cart settings table, and the test environment has no database.
 */
final class DeliveryOrderTypeMinimumTest extends TestCase
{
    /** The rules as stored for D3B. Not changed by the override. */
    private const STORED_RULES = [
        ['type' => 'above', 'amount' => 0, 'total' => 80, 'priority' => 1],
        ['type' => 'below', 'amount' => 5, 'total' => 80, 'priority' => 2],
    ];

    /**
     * A farther zone that starts at CA$40.00, expressed the vendor's way: no
     * delivery below CA$40.00, then CA$5.00 on all orders.
     */
    private const UNAVAILABLE_BELOW_FORTY = [
        ['type' => 'below', 'amount' => -1, 'total' => 40, 'priority' => 1],
        ['type' => 'all', 'amount' => 5, 'total' => 0, 'priority' => 2],
    ];

    public function test_the_storefront_receives_the_project_order_type(): void
    {
        $location = $this->locationWithStoredDeliveryMinimum(20.0);

        $this->assertInstanceOf(DeliveryOrderType::class, $location->getOrderType(Location::DELIVERY),
            'The container binding must hand the storefront the project class, or nothing below applies.');
    }

    /**
     * The label path. The basket panel prints Location::minimumOrderTotal().
     */
    public function test_the_label_prints_the_stored_minimum_under_the_stored_rules(): void
    {
        $location = $this->locationWithStoredDeliveryMinimum(20.0);
        $location->setCoveredArea($this->coveredArea(self::STORED_RULES));

        foreach ([19.0, 20.0, 50.0, 79.0, 80.0, 81.0] as $subtotal) {
            $this->fakeCartSubtotal($subtotal);

            $this->assertSame(20.0, (float) $location->minimumOrderTotal(Location::DELIVERY),
                sprintf('Label minimum at a %.2f subtotal.', $subtotal));
        }
    }

    /**
     * The gate path. CartManager::cartTotalIsBelowMinimumOrder() asks
     * Location::checkMinimumOrderTotal($subtotal) and the checkout button is
     * disabled while it answers false.
     */
    public function test_the_checkout_gate_enforces_the_stored_minimum_under_the_stored_rules(): void
    {
        $location = $this->locationWithStoredDeliveryMinimum(20.0);
        $location->setCoveredArea($this->coveredArea(self::STORED_RULES));

        foreach ([19.0 => false, 20.0 => true, 50.0 => true, 79.0 => true, 80.0 => true, 81.0 => true] as $subtotal => $passes) {
            $this->fakeCartSubtotal((float) $subtotal);

            $this->assertSame($passes, $location->checkMinimumOrderTotal((float) $subtotal, Location::DELIVERY),
                sprintf('Checkout gate at a %.2f subtotal.', $subtotal));
        }
    }

    /**
     * The fee split is the vendor's and is untouched: CA$5.00 under CA$80.00,
     * free from CA$80.00. The override changes the minimum, not the charge.
     */
    public function test_the_delivery_fee_is_unchanged(): void
    {
        $location = $this->locationWithStoredDeliveryMinimum(20.0);
        $location->setCoveredArea($this->coveredArea(self::STORED_RULES));

        foreach ([19.0 => 5.0, 79.0 => 5.0, 80.0 => 0.0, 81.0 => 0.0] as $subtotal => $fee) {
            $this->assertSame($fee, (float) $location->deliveryAmount((float) $subtotal),
                sprintf('Delivery fee at a %.2f subtotal.', $subtotal));
        }
    }

    /**
     * The reverse assertion. A rule that makes delivery unavailable below a
     * total is the vendor's way of giving an area its own minimum, and it
     * still blocks: the override narrows what counts as an area minimum, it
     * does not discard it. A CA$30.00 basket in a zone that starts at
     * CA$40.00 fails the gate, shows CA$40.00, and is also refused by the
     * vendor's unavailable-charge check. A CA$50.00 basket passes at the
     * stored CA$20.00.
     */
    public function test_an_area_that_is_unavailable_below_a_total_still_blocks(): void
    {
        $location = $this->locationWithStoredDeliveryMinimum(20.0);
        $area = $this->coveredArea(self::UNAVAILABLE_BELOW_FORTY);
        $location->setCoveredArea($area);

        $this->fakeCartSubtotal(30.0);
        $this->assertSame(40.0, (float) $location->minimumOrderTotal(Location::DELIVERY));
        $this->assertFalse($location->checkMinimumOrderTotal(30.0, Location::DELIVERY),
            'A CA$30.00 basket must still be blocked where delivery is unavailable below CA$40.00.');
        $this->assertLessThan(0, $location->deliveryAmount(30.0),
            'The vendor still reports delivery as unavailable for that basket.');

        $this->fakeCartSubtotal(50.0);
        $this->assertSame(20.0, (float) $location->minimumOrderTotal(Location::DELIVERY));
        $this->assertTrue($location->checkMinimumOrderTotal(50.0, Location::DELIVERY));
        $this->assertSame(5.0, (float) $location->deliveryAmount(50.0));
    }

    /**
     * The stored minimum still wins when it is the larger value, as in the
     * vendor's composition.
     */
    public function test_a_larger_stored_minimum_still_governs(): void
    {
        $location = $this->locationWithStoredDeliveryMinimum(45.0);
        $location->setCoveredArea($this->coveredArea(self::UNAVAILABLE_BELOW_FORTY));

        $this->fakeCartSubtotal(42.0);
        $this->assertSame(45.0, (float) $location->minimumOrderTotal(Location::DELIVERY));
        $this->assertFalse($location->checkMinimumOrderTotal(42.0, Location::DELIVERY));
    }

    /**
     * The Delivery order type reads the subtotal through the Cart facade.
     * Resolving the real cart boots the cart extension, which reads a settings
     * table, and the test environment has no database. Swapping the facade for
     * a mock sidesteps that without changing what is under test.
     */
    private function fakeCartSubtotal(float $subtotal): void
    {
        Cart::swap(Mockery::mock()->shouldReceive('subtotal')->andReturn($subtotal)->getMock());
    }

    private function coveredArea(array $conditions): CoveredArea
    {
        return new CoveredArea(new LocationArea([
            'name' => 'QA-D3-AREA-TEST',
            'type' => 'polygon',
            'conditions' => $conditions,
        ]));
    }

    private function locationWithStoredDeliveryMinimum(float $minimum): LocationService
    {
        $model = new Location;
        $model->setRelation('settings', new EloquentCollection([
            new LocationSettings([
                'item' => 'delivery',
                'data' => ['is_enabled' => true, 'min_order_amount' => $minimum],
            ]),
            new LocationSettings([
                'item' => 'collection',
                'data' => ['is_enabled' => true, 'min_order_amount' => 0],
            ]),
        ]));

        // The project's Delivery gate hides the Delivery order type while the
        // global flag is off; this test is about the minimum, so the gate is
        // opened for it.
        config()->set('delivery.enabled', true);

        /** @var LocationService $location */
        $location = resolve('location');
        $location->setSessionKey('delivery_order_type_minimum_test');

        // The location service is a singleton and caches the order types it
        // built for the previous model; drop them so this model's settings are
        // the ones read. clearInternalCache() also drops the model, so it runs
        // before setModel(). Each test sets its own covered area after this.
        $location->clearInternalCache();
        $location->setModel($model);

        return $location;
    }
}
