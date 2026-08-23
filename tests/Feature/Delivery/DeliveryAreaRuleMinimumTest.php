<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use Igniter\Cart\Facades\Cart;
use Igniter\Cart\OrderTypes\Delivery as VendorDelivery;
use Igniter\Local\Classes\CoveredArea;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationArea;
use Igniter\Local\Models\LocationSettings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Mockery;
use Tests\TestCase;

/**
 * Characterises how the vendor derives the Delivery minimum from the area's
 * fee rules. This is not a test of project code; it pins vendor semantics that
 * the D3C acceptance record depends on, so that "the code predicts" becomes
 * "the vendor semantics are established" without a database or a basket.
 *
 * The storefront on Canada staging shows "Min. Order Amount: $80.00" for
 * Delivery while the stored minimum is CA$20.00. The rules recorded for D3B
 * are "free at or above CA$80.00" then "CA$5.00 below CA$80.00".
 *
 * Whether the stored rules actually carry that shape is a separate question,
 * answered by reading them back, not by this test.
 *
 * The composition tests construct the vendor's own Delivery order type
 * directly, so that the project's container binding to
 * App\Delivery\DeliveryOrderType does not intercept them: this file pins the
 * vendor, and the project's override is tested in DeliveryOrderTypeMinimumTest.
 */
final class DeliveryAreaRuleMinimumTest extends TestCase
{
    /** The rule shape recorded for D3B: free at or above 80, then 5.00 below 80. */
    private const RECORDED_RULES = [
        ['type' => 'above', 'amount' => 0, 'total' => 80, 'priority' => 1],
        ['type' => 'below', 'amount' => 5, 'total' => 80, 'priority' => 2],
    ];

    /** Candidate remedy A: free at or above 80, then 5.00 on all orders. */
    private const CANDIDATE_A_RULES = [
        ['type' => 'above', 'amount' => 0, 'total' => 80, 'priority' => 1],
        ['type' => 'all', 'amount' => 5, 'total' => 0, 'priority' => 2],
    ];

    /** Candidate A with the priorities the other way round. */
    private const CANDIDATE_A_REVERSED = [
        ['type' => 'all', 'amount' => 5, 'total' => 0, 'priority' => 1],
        ['type' => 'above', 'amount' => 0, 'total' => 80, 'priority' => 2],
    ];

    private const SUBTOTALS = [19.0, 20.0, 50.0, 79.0, 80.0, 81.0, 100.0];

    /**
     * Under the recorded shape every basket below CA$80.00 matches the "below"
     * rule, and the vendor returns a matched below-rule's threshold as the
     * area's minimum order total. At or above CA$80.00 the "above" rule
     * matches and its threshold is returned too. So the area minimum is
     * CA$80.00 at every subtotal, and the CA$5.00 / free split is correct.
     */
    public function test_recorded_rules_make_the_area_minimum_eighty_at_every_subtotal(): void
    {
        $area = $this->coveredArea(self::RECORDED_RULES);

        foreach (self::SUBTOTALS as $subtotal) {
            $this->assertSame(80.0, (float) $area->minimumOrderTotal($subtotal),
                sprintf('Area minimum at a %.2f subtotal under the recorded rules.', $subtotal));

            $this->assertSame($subtotal < 80 ? 5.0 : 0.0, (float) $area->deliveryAmount($subtotal),
                sprintf('Delivery fee at a %.2f subtotal under the recorded rules.', $subtotal));
        }
    }

    /**
     * Candidate A replaces the "below" rule with an "all orders" rule whose
     * threshold is zero. Below CA$80.00 the area minimum is then zero, so the
     * stored minimum governs; at or above CA$80.00 the "above" rule matches and
     * its threshold of 80 is returned, which any such basket already meets.
     */
    public function test_candidate_a_rules_leave_the_stored_minimum_in_force_below_eighty(): void
    {
        $area = $this->coveredArea(self::CANDIDATE_A_RULES);

        foreach (self::SUBTOTALS as $subtotal) {
            $this->assertSame($subtotal < 80 ? 0.0 : 80.0, (float) $area->minimumOrderTotal($subtotal),
                sprintf('Area minimum at a %.2f subtotal under candidate A.', $subtotal));

            $this->assertSame($subtotal < 80 ? 5.0 : 0.0, (float) $area->deliveryAmount($subtotal),
                sprintf('Delivery fee at a %.2f subtotal under candidate A.', $subtotal));
        }
    }

    /**
     * The vendor resolves several matching rules by taking the first valid one
     * in ascending priority. If "all orders" carries the lower priority number
     * it matches every basket first, and free delivery at CA$80.00 is lost.
     * Candidate A is only safe with "free at or above 80" ahead of "all orders".
     */
    public function test_candidate_a_with_reversed_priorities_loses_free_delivery(): void
    {
        $area = $this->coveredArea(self::CANDIDATE_A_REVERSED);

        $this->assertSame(5.0, (float) $area->deliveryAmount(100.0),
            'A CA$100.00 basket should be charged CA$5.00 once "all orders" outranks "above 80".');
        $this->assertSame(0.0, (float) $area->minimumOrderTotal(100.0));
    }

    /**
     * The Delivery order type takes the larger of the area minimum and the
     * stored minimum, and the same value is what the minimum-order check
     * compares the subtotal against. Under the recorded rules a CA$50.00
     * basket therefore fails the check although the stored minimum is 20.
     */
    public function test_recorded_rules_raise_the_enforced_delivery_minimum_to_eighty(): void
    {
        $location = $this->locationWithStoredDeliveryMinimum(20.0);
        $location->setCoveredArea($this->coveredArea(self::RECORDED_RULES));
        $this->fakeCartSubtotal(50.0);
        $vendor = $this->vendorDeliveryType($location);

        $this->assertSame(80.0, $vendor->getMinimumOrderTotal());
        $this->assertLessThan($vendor->getMinimumOrderTotal(), 50.0,
            'A CA$50.00 basket is below the vendor minimum, so the vendor gate (subtotal >= minimum) refuses it.');
    }

    public function test_candidate_a_rules_enforce_the_stored_delivery_minimum(): void
    {
        $location = $this->locationWithStoredDeliveryMinimum(20.0);
        $location->setCoveredArea($this->coveredArea(self::CANDIDATE_A_RULES));
        $this->fakeCartSubtotal(50.0);
        $vendor = $this->vendorDeliveryType($location);

        $this->assertSame(20.0, $vendor->getMinimumOrderTotal());
        $this->assertGreaterThanOrEqual($vendor->getMinimumOrderTotal(), 50.0);
        $this->assertLessThan($vendor->getMinimumOrderTotal(), 19.0);
    }

    /**
     * The vendor's own Delivery order type, constructed directly so that the
     * project's container binding does not intercept it.
     */
    private function vendorDeliveryType(LocationService $location): VendorDelivery
    {
        return new VendorDelivery($location->getModel(), [
            'code' => Location::DELIVERY,
            'name' => 'lang:igniter.local::default.text_delivery',
        ]);
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
        // global flag is off; this test is about the vendor's minimum, so the
        // gate is opened for it.
        config()->set('delivery.enabled', true);

        /** @var LocationService $location */
        $location = resolve('location');
        $location->setSessionKey('delivery_area_rule_minimum_test');
        $location->setModel($model);

        return $location;
    }
}
