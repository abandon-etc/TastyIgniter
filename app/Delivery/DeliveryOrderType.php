<?php

declare(strict_types=1);

namespace App\Delivery;

use Igniter\Cart\Facades\Cart;
use Igniter\Cart\OrderTypes\Delivery;
use Igniter\Local\Classes\CoveredArea;
use Igniter\Local\Classes\CoveredAreaCondition;
use Igniter\Local\Facades\Location as LocationFacade;
use Override;

/**
 * The Delivery order type with the minimum order total read the way the
 * owner's settings mean it.
 *
 * The vendor's Delivery minimum is the larger of the stored Location minimum
 * and a minimum derived from the delivery area's fee rules. That derivation
 * returns the matched rule's threshold whatever the rule says, so a fee rule
 * of "CA$5.00 below CA$80.00" makes CA$80.00 the minimum for every basket
 * under it, while the stored minimum of CA$20.00 never reaches the customer.
 * The same value prints the basket's "Min. Order Amount" label and disables
 * the checkout button, so both were wrong together.
 *
 * This class keeps the vendor's composition, the larger of stored and area
 * minimum, and narrows what counts as an area minimum: only a matched rule
 * that makes delivery unavailable (a negative charge, shown in the admin as
 * "delivery is not available") carries one, because that is the vendor's own
 * way of saying "no delivery below this total" for an area. A fee rule's
 * threshold is a fee boundary and nothing more.
 *
 * Known cost, recorded in CLAUDE_HANDOFF.md: a positive-charge "below" rule no
 * longer implies a minimum. A future per-area minimum (for example a farther
 * zone that starts at CA$40.00) must be expressed as "delivery is not
 * available below CA$40.00", which this class honours.
 *
 * Registered as the container binding for the vendor class in
 * AppServiceProvider; OrderTypes::makeOrderTypes() resolves each order type
 * through the container, so the storefront receives this class wherever it
 * would have received the vendor's.
 */
final class DeliveryOrderType extends Delivery
{
    #[Override]
    public function getMinimumOrderTotal(): float
    {
        $storedMinimum = (float) $this->location->getMinimumOrderTotal($this->code);

        $areaMinimum = $this->areaMinimum(LocationFacade::coveredArea(), (float) Cart::subtotal());

        return max($areaMinimum, $storedMinimum);
    }

    /**
     * The area's own minimum: the threshold of the matched rule only when that
     * rule makes delivery unavailable. Rules are consulted in the vendor's
     * order, first valid by ascending priority, so the rule found here is the
     * same one the vendor charges by.
     */
    private function areaMinimum(CoveredArea $area, float $subtotal): float
    {
        $matched = $area->listConditions()
            ->first(static fn (CoveredAreaCondition $condition): bool => $condition->isValid($subtotal));

        if (! $matched instanceof CoveredAreaCondition || $matched->getCharge() !== null) {
            return 0.0;
        }

        return (float) $matched->getMinTotal();
    }
}
