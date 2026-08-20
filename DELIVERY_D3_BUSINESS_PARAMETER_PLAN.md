# Delivery D3 Business Parameter Plan

Date: 2026-07-19
Updated: 2026-08-20
Environment: Canada staging audit, planning, and closed-gate configuration
Status: Approved D3B configuration saved; D3C enablement remains blocked

## Scope and safety state

This document records the D3A read-only audit and the approved D3B
configuration written on 2026-08-19 while Delivery remained closed.

- Canada staging remains Pickup-only because `DELIVERY_ENABLED=false`.
- At the D3A baseline, the stored default Location had Delivery and Collection
  enabled but zero Delivery Areas. D3B now retains one approved polygon while
  the global Delivery gate remains false.
- No setting or database row was changed during D3A. The later D3B write was
  limited to the approved Location coordinates, Delivery Area, minimum, and
  schedule; it did not change schema, runtime revision, secret, Order,
  Customer, Reservation, Birthday record, payment, email, or SMS data.
- Render staging and the DigitalOcean database remain unchanged fallbacks.
- Production is outside this phase.

## Audited software baseline

- Application branch baseline: `4.x` at
  `ea5c6b5f263ba93f4bd28b9551435d85c66b7ff7`.
- TastyIgniter Local extension: `v4.1.4`.
- TastyIgniter Cart extension: `v4.2.3`.
- Native Delivery Area model: `Igniter\Local\Models\LocationArea`.
- Native coverage services:
  `Igniter\Local\Models\Concerns\HasDeliveryAreas`,
  `Igniter\Local\Classes\CoveredArea`, and
  `Igniter\Local\Classes\CoveredAreaCondition`.
- Native checkout authority:
  `Igniter\Cart\Classes\OrderManager`,
  `Igniter\Cart\Classes\CartManager`, and the Orange checkout Livewire
  component.
- Data table: `ti_location_areas` in the current prefixed staging schema.
- Admin path: Manage -> Locations -> Location Settings -> Delivery Areas.

Vendor and TastyIgniter core were inspected as installed dependencies only;
they were not modified.

## D3A Canada staging before-state

| Item | Read-only result |
| --- | --- |
| Active revision | `le-chateau-canada-staging-d2fix-31821289` |
| Traffic | Ready, 100% |
| Global Delivery gate | `DELIVERY_ENABLED=false`, also false after config cache |
| Stored Location Delivery | Enabled |
| Stored Location Collection | Enabled |
| Effective storefront fulfillment | Pickup only |
| Delivery Areas | 0 |
| Delivery minimum | CAD 0.00 |
| Delivery fee | No area rule exists, so no launch fee is configured |
| Delivery interval | 15 minutes |
| Delivery lead time | 25 minutes |
| Add lead time to schedule start | Off |
| ASAP/later restriction | None; both are allowed by the stored setting |
| Future Delivery orders | Off; retained min/max fields are 0/5 days |
| Delivery hours | Daily 12:00-21:00 |
| Pickup hours | Daily 12:00-22:00 |
| Pickup minimum | CAD 0.00 |
| Tax mode | Disabled; rate 0 |
| Tax Delivery charge | Off |
| Distance unit | Kilometres |
| Geocoder | Chain: Google followed by Nominatim |
| Google geocoder credential | Configured; value not recorded |

The table above is the D3A before-state retained for rollback. The approved
D3B after-state is recorded below.

## 2026-08-19 D3B approved staging configuration

The user confirmed that Canada staging contains no real customer, order, or
payment data and authorized the scoped Location and Delivery Area writes. No
schema, migration, Order, Customer, Reservation, Birthday, Payment, mail,
production, Render, or DigitalOcean change was made.

| Item | Saved and independently read back |
| --- | --- |
| Global Delivery gate | `DELIVERY_ENABLED=false`; storefront remains Pickup-only |
| Delivery centre | Current default Location; coordinates saved from the supplied map point |
| Delivery Area | One default polygon named `D3 Montreal Delivery Area` |
| Boundary storage | 36 unique vertices plus the native encoded polygon |
| Base Delivery fee | CAD 5.00 below the free threshold |
| Free Delivery | CAD 0.00 at or above CAD 80.00 |
| Distance surcharge | None |
| Delivery minimum | CAD 20.00 |
| Delivery hours | Monday-Friday 12:00-21:00; Saturday-Sunday closed |
| Pickup settings | Unchanged |
| Tax settings | Unchanged |

### Supplied KMZ/KML boundary validation

- The uploaded KMZ contains one KML NetworkLink. The linked Google My Maps
  export was fetched read-only for the actual geometry.
- The geometry export contains one Document, no Folder, two Placemarks, one
  store Point, and one Polygon. There is exactly one polygon ring.
- The ring contains 37 coordinate entries including the closing repeat, or 36
  unique vertices. It is closed, has no consecutive duplicate vertices, and
  has no self-intersections.
- Bounding box is longitude `-73.6145426` to `-73.5364782` and latitude
  `45.6164684` to `45.6711452`. Approximate planar/geodesic area is
  14.74 square kilometres.
- This is suitable as the single D3 Delivery Area polygon. The native saved
  representation correctly stores the 36 unique vertices and its encoded
  polygon rather than duplicating the closing vertex.
- Runtime self-checks passed for the confirmed store point (inside), a
  synthetic outside point (outside), and an exact first-vertex boundary point
  (inside, as required by native inclusive-boundary behavior).

## Delivery Area capabilities

### Supported area types

| Type | Native support | Precision and maintenance | External dependency | Per-area fees | First-launch assessment |
| --- | --- | --- | --- | --- | --- |
| Postal/FSA | Address-component area using `postal_code`; exact case-insensitive text or an administrator-supplied regular expression | Exact values do not remove spaces and do not perform FSA expansion. Regex can cover an FSA but is easy to overmatch. Address provider output determines the postal value. | Reverse geocoding | Yes | Not recommended as the sole first-launch boundary without a tested, anchored pattern set |
| Radius | Native `circle` boundary; the edge is included | Lowest maintenance, but a circle can cross rivers, borough limits, or otherwise excluded areas | Geocoded coordinates; distance unit is km in staging | Yes, plus optional distance surcharge | Acceptable for a deliberately simple service radius |
| Polygon | Native `polygon` vertices; vertices and boundary lines are included | Highest geographic control; map edits require careful review | Geocoded coordinates and admin map | Yes | Recommended for Montreal launch when the service area is irregular |
| Multiple areas | Multiple address, circle, or polygon rows per Location | Supports fee zones, but overlap order must be intentional | Depends on each area type | Yes | Recommended only with non-overlapping zones and an explicit priority table |

### Selection behavior

- Areas are sorted by ascending `priority`; the first matching area wins.
- The implementation does not select the cheapest, smallest, nearest, or most
  specific overlapping area.
- Circle boundaries use `distance <= radius`. Polygon vertices and boundary
  lines count as inside.
- Address components are grouped by type, but the current matcher accepts the
  area when any configured component group matches. Combining a broad city
  component with a postal component can therefore enlarge coverage rather
  than require both values.
- Postal exact comparison is case-insensitive but space-sensitive. Partial FSA
  matching needs a carefully anchored regex and synthetic tests for spaced and
  unspaced provider output.
- A Location can have one default area. Runtime totals can temporarily fall
  back to it when no area is in session, but checkout re-geocodes the submitted
  address and requires a real `searchDeliveryArea` match before completion.
- For multiple Locations, candidates are ordered by geographic distance and
  the first Location with a matching area is selected. Canada staging
  currently has one active Location, so cross-Location arbitration is not a
  launch concern unless that changes.

### Fee and condition support

Each area can contain ordered conditions with:

- `all`: apply on all cart subtotals;
- `below`: apply below a configured subtotal;
- `above`: apply at or above a configured subtotal;
- amount `0`: free base delivery;
- amount below `0`: Delivery unavailable;
- optional ordered distance surcharges using greater/less/equality variants.

The first matching fee condition wins. Distance surcharge is then added to the
matched condition amount. Consequently, a free base-delivery threshold does
not remove distance surcharge automatically.

## Address and geocoding audit

### Runtime flow

1. The storefront accepts an address or selected map point.
2. The server calls the configured geocoder and receives normalized address
   components plus latitude/longitude.
3. The server stores the resulting user position in the session, clears the
   old area, and searches current Location areas by priority.
4. Checkout geocodes the submitted address again, requires a street number and
   street name, and searches the area again.
5. If checkout resolves a different area, it updates the server-side area and
   stops that submission with an area-changed message so totals can refresh.

No checkout field is trusted to provide a Delivery Area or Delivery fee. The
Orders API request discards client-provided menu/totals collections, and the
project D1 gate continues to reject Delivery writes while the global flag is
off.

### Provider state and operating limits

- Canada staging uses the native Chain provider: Google first and public
  OpenStreetMap Nominatim as fallback.
- The existing Google credential is configured. D3A needs no new secret. If
  provider or quota configuration changes later, the value must be entered in
  the platform UI and never in chat, Git, logs, screenshots, or this document.
- Google Geocoding is billed per request and its current documented standard
  limit is 3,000 queries per minute; project quotas should be lowered and
  monitored for staging rather than relying on the maximum:
  <https://developers.google.com/maps/documentation/geocoding/usage-and-billing>.
- The public Nominatim policy sets an absolute maximum of one request per
  second, requires an identifying User-Agent/Referer, and requires attribution:
  <https://operations.osmfoundation.org/policies/nominatim/>.
- The installed Nominatim provider inherits the incoming browser User-Agent.
  That is not a dedicated application identity and must be reviewed before
  public Delivery enablement.
- Native geocoder responses are cached, but the current Cloud Run file cache
  is instance-local. It is not a shared quota-control layer across instances.

### Normalization and privacy gate

- Coordinates are normalized to eight decimal places by the geometry layer.
- Postal matching uses the provider's postal component; the area matcher does
  not normalize spaces or expand partial Canadian postal codes.
- On a fully empty geocoder result, Orange writes provider diagnostic messages
  to the application log. Provider exceptions can originate from request URLs;
  Google request URLs contain the query and credential. D3B must deliberately
  test a synthetic failure and prove that complete addresses and credentials
  are absent from logs before D3C.
- Public errors must remain generic and must not return polygon vertices,
  internal Location/area IDs, SQLSTATE, class names, tokens, or provider
  credentials.

Q-011 tracks this geocoder operations/privacy gate. If the synthetic failure
test reproduces sensitive logging, fix it in a project-owned listener or
wrapper PR before enabling Delivery; do not patch vendor.

The 2026-08-20 controlled Canada staging failure reproduced the encoded
synthetic address and provider request URL in application/Cloud Run logs. The
tested path did not expose a credential, authorization header, geometry,
SQLSTATE, or internal Location/area ID. Q-011 therefore remains Open. The
project-owned redaction wrapper in PR #69 now covers direct forward/reverse
facade exceptions and narrowed autocomplete/place-lookup provider boundaries;
it is pending renewed review and must be deployed and retested before this gate
can be closed.

The native public Nominatim fallback is also not approved for production
Delivery traffic. It inherits request identity rather than setting a stable
application identity, has no shared cross-instance one-request-per-second
limiter, and can receive concurrent fallback load during a Google failure.
Public Nominatim policy also prohibits autocomplete. Production must use an
accepted Google-only, self-hosted, commercial, or other project-owned reliable
fallback design; that operating decision is separate from the Q-011 redaction
fix.

### 2026-08-20 final clean Q-011 acceptance

The merged project-owned redaction fix was validated from commit
`3fc841d12e65155c445c9c747ee7f97ec3ea0f49` in isolated revision
`le-chateau-canada-staging-q011-3fc841d` at 0% traffic while the stable revision
retained 100% traffic and `DELIVERY_ENABLED=false`. The authoritative same-image
clean execution passed empty-result, forward/reverse failure, autocomplete,
place-lookup, business-validation, successful-reverse, and closed Delivery API
paths. Exact-window logs and public/Livewire results exposed no full address,
provider URL, credential, raw provider exception, geometry, SQL/internal ID,
database diagnostic, or PHP path. The failure path remained fail-closed and no
business data changed.

Q-011 is therefore `Resolved` for this current Canada staging tested failure
path. This does not approve public Nominatim for production Delivery traffic,
does not change the global Delivery gate, and does not authorize D3C cutover.

## Server-side totals audit

The effective native order is:

1. Menu item price and options form the raw item subtotal.
2. Item-level conditions are reflected in `Cart::subtotal()`.
3. Minimum order is checked against `Cart::subtotal()`.
4. Delivery condition priority 100 calculates the area fee from that same
   subtotal and adds any distance surcharge.
5. Cart-level coupon condition priority 200 applies after Delivery.
6. Tax condition priority 300 applies after Delivery and coupon.
7. Payment fee, when applicable, has priority 600.
8. The final server-side total and condition rows are persisted to the Order
   and `order_totals` history.

Implications:

- Delivery fee and tax are not included in the minimum-order basis.
- A cart-level coupon does not lower the minimum-order or free-delivery basis.
- Item-level conditions can change `Cart::subtotal()` and therefore can change
  those bases. D3B needs one explicit menu-condition regression if such a rule
  is enabled in the real menu later.
- Area free-delivery thresholds use `Cart::subtotal()`, before cart-level
  coupons and tax.
- The Location minimum and matched-area minimum are combined with `max(...)`;
  the stricter value wins.
- Tax is currently disabled. If enabled later, the existing `Tax Delivery
  Charge` setting decides whether the Delivery fee is taxable. D3A does not
  decide Quebec tax treatment.
- Switching to Pickup makes the Delivery condition inapplicable, so no
  Delivery fee is added. The D1/D2 session normalizer also clears Delivery-only
  area, position, and timeslot state when Delivery is closed.
- Address changes are geocoded and matched again at checkout. A changed area
  stops the current submission and causes totals to be recomputed on the next
  render/submission.
- Client-provided totals and fee values are not authoritative. Checkout uses
  server cart conditions and the API request strips client `order_totals` and
  `order_menus` input.
- Native monetary configuration and `order_totals.value` use decimal/float
  values formatted by the configured CAD currency. D3B should restrict admin
  inputs to non-negative two-decimal CAD amounts except the native `-1`
  unavailable sentinel.

## Delivery time audit

- Delivery has an independent weekly working schedule; the current order-type
  checks use the Delivery schedule, not the general Opening or Pickup schedule.
- Lead time, interval, ASAP/later restriction, and future-order min/max days
  are native Location settings.
- There is no separate native D3 field for a daily cutoff expressed as
  "minutes before closing". The safe configuration is an earlier Delivery
  closing time, with lead-time/timeslot behavior tested at the last slot.
- There is no native peak-period lead-time schedule in the inspected version.
  A different peak lead time would require an approved project-level feature.
- No distinct dated holiday-override workflow was found in the current
  Location schedule editor. Until a separate feature is approved, operations
  must use a controlled temporary Delivery disable or an explicitly reviewed
  schedule change, with original values recorded for rollback.
- Q-010 concerns the scheduled Pickup select binding and remains separate.
  D3 must not enable future orders merely to exercise that issue.

## Executable area options

### Option A: Postal code/FSA areas

- Configuration: address-component Delivery Areas with postal values or
  anchored regular expressions.
- Benefits: business staff can describe service by postal zones; each area can
  have a different fee.
- Risks: exact values are space-sensitive, FSA requires regex, provider output
  can vary, and multiple component types are OR-matched. An incorrect regex can
  expose a much larger area than intended.
- Appropriate when: the business already owns a reviewed postal/FSA list and
  accepts provider-based postal matching.
- Recommendation: not the sole first-launch control without a project-owned
  normalization/test layer or a small, fully enumerated and tested rule set.

### Option B: Fixed radius

- Configuration: one circle centered on the confirmed Delivery Location,
  optionally with distance surcharge rules.
- Benefits: simplest administration and clear maximum distance.
- Risks: circles ignore roads, borough boundaries, rivers, and explicitly
  excluded neighbourhoods. Distance calculation and address geocoding must be
  available.
- Appropriate when: the service promise is genuinely radial and broad boundary
  precision is acceptable.
- Recommendation: viable low-maintenance fallback, especially for an initial
  small radius with conservative exclusions handled operationally.

### Option C: Polygon or multiple non-overlapping polygons

- Configuration: one reviewed service polygon, or several non-overlapping fee
  polygons with explicit ascending priorities.
- Benefits: best fit for irregular Montreal coverage; supports separate fees
  without postal formatting dependence.
- Risks: highest map maintenance cost; accidental overlap is resolved only by
  priority and needs boundary QA.
- Appropriate when: the business can confirm the actual street-level service
  boundary and fee zones.
- Recommendation: preferred first-launch solution. Start with one conservative
  polygon and one fee rule. Add extra fee polygons only when the business
  supplies distinct, non-overlapping boundaries.

## Recommended first-launch shape

The technical recommendation, pending user approval, is:

1. One conservative custom polygon for the confirmed Delivery service area.
2. One base fee condition and one optional free-base-delivery threshold.
3. No distance surcharge for the first staging acceptance.
4. No future orders until the normal same-day path is accepted.
5. Preserve Pickup as the default fulfillment method.
6. Keep tax settings unchanged until the Delivery fee tax treatment is
   separately confirmed.

This minimizes ambiguity in overlap, distance routing, postal formatting, and
fee composition while using only native server-side behavior.

## User decision table

The `User final value` column records only values explicitly confirmed by the
user. Unconfirmed launch decisions remain marked Pending confirmation.

| Parameter | Options | Technical constraint | Recommended value | User final value |
| --- | --- | --- | --- | --- |
| Delivery centre | Current default Location / another approved Location | Area belongs to one Location; current staging has one active Location | Current default Location after business confirmation | Current default Location; confirmed 2026-08-19 |
| Delivery Area type | Postal/FSA / radius / polygon / multiple areas | First matching ascending priority wins | One conservative polygon | One polygon; confirmed 2026-08-19 |
| Service boundary | Neighbourhoods / streets / postal zones / drawn boundary | Must be supplied by the business; no guessing | Explicit street-level polygon | Supplied Google My Maps boundary; confirmed 2026-08-19 |
| Maximum distance | None / kilometre limit | Radius and distance surcharge depend on geocoding | No separate distance rule for first launch | None; confirmed 2026-08-19 |
| Excluded areas | Explicit list/boundary exclusions | Native polygon has no hole editor documented; shape around exclusions or split areas | Explicitly list exclusions before drawing | Pending confirmation |
| Cross administrative borders | Allowed / disallowed | Geometry does not understand borough policy | Disallow unless explicitly approved | Pending confirmation |
| Partial postal codes | Allowed / disallowed | FSA needs anchored regex and spaced/unspaced tests | Disallow in first polygon launch | Not used as a control; confirmed 2026-08-19 |
| Multiple fee zones | One / multiple | Areas should not overlap; priority is authoritative | One zone initially | One zone; confirmed 2026-08-19 |
| Base fee model | Fixed / per-area / base plus distance / free | Native area condition amount | One fixed per-area fee | Fixed per-area fee; confirmed 2026-08-19 |
| Base Delivery fee | CAD amount | Non-negative, two decimals; amount `-1` means unavailable | Business decision required | CAD 5.00; confirmed 2026-08-19 |
| Distance surcharge | Off / threshold rules | Added even when base fee condition is free | Off initially | Off; confirmed 2026-08-19 |
| Free Delivery | Off / on | Implemented as an ordered area condition | Optional after base flow passes | On; confirmed 2026-08-19 |
| Free threshold | CAD subtotal | Uses server `Cart::subtotal()`, before cart coupon and tax | Business decision required | CAD 80.00; confirmed 2026-08-19 |
| Delivery minimum | CAD subtotal | Max of Location and matched-area minimum; excludes Delivery fee/tax | Business decision required | CAD 20.00; confirmed 2026-08-19 |
| Pickup minimum | CAD amount | Independent Collection setting | Keep CAD 0.00 | Pending confirmation |
| Discount basis | Native subtotal behavior | Item conditions affect basis; cart-level coupon does not | Keep native behavior and document it | Pending confirmation |
| Delivery fee tax | Off / on | Current tax mode and rate are disabled/0 | Keep off until tax advice/approval | Pending confirmation |
| Fee displayed as tax-inclusive | Inclusive / exclusive | Controlled by tax mode/menu-price settings, not area rule | Do not change in D3 | Pending confirmation |
| Admin temporary fee change | Allowed / disallowed/change control | Native admin has no D3-specific approval or audit workflow | Disallow ad hoc edits; use recorded change control | Pending confirmation |
| Delivery hours | Weekly schedule | Independent from Pickup/general opening | Confirm whether retained daily 12:00-21:00 is real | Monday-Friday 12:00-21:00; weekends closed; confirmed 2026-08-19 |
| Lead time | Minutes | Current retained value 25 | Confirm operational value | Pending confirmation |
| Add lead time to start | Off / on | Changes generated first timeslot behavior | Test both only after business choice | Pending confirmation |
| Time interval | Minutes | Current retained value 15 | Confirm 15 minutes | Pending confirmation |
| ASAP | On / off | Restriction can be None, ASAP-only, or later-only | On for initial same-day flow | Pending confirmation |
| Future orders | On / off | Q-010 is separate and future orders are currently off | Off | Pending confirmation |
| Future min/max days | Integer days | Used only when future orders are on | Leave inactive at 0/5 | Pending confirmation |
| Same-day cutoff | Closing time / explicit rule | No separate native cutoff-minutes field | Encode with earlier Delivery closing time | Pending confirmation |
| Stop before close | Minutes | Must be represented by schedule/last-slot acceptance | Confirm operational buffer | Pending confirmation |
| Peak lead time | Fixed / variable | Variable peak schedule needs new approved feature | Fixed initially | Pending confirmation |
| Holiday handling | Temporary disable / schedule edit / future feature | No dated override workflow found | Recorded temporary disable until separately designed | Pending confirmation |
| Default fulfillment | Pickup / Delivery | D2 currently prefers Pickup while preserving valid Delivery session | Keep Pickup | Pending confirmation |
| Out-of-area response | Block with generic EN/FR message | Must not expose geometry or IDs | Block, retain cart, offer Pickup | Block Delivery and retain Pickup option; confirmed 2026-08-19 |
| Unrecognized address | Retry/edit/Pickup | Geocoder is authoritative | Generic retry/edit plus Pickup option | Pending confirmation |
| Incomplete postal code | Reject / ask for full address | Street number and street name are required at checkout | Ask for complete address | Pending confirmation |
| Multiple Location match | Nearest / manual choice | Native flow selects nearest matching Location | Keep native nearest while only one Location exists | Pending confirmation |
| No Delivery Area | Block Delivery | Native launch requires at least one area | Fail closed | Pending confirmation |
| Provider unavailable | Retry / Pickup / contact | Chain can fail; no sensitive diagnostics in response | Fail closed, preserve cart, offer Pickup | Pending confirmation |
| Geocoder provider | Chain / Google / Nominatim | Quota, billing, attribution, and log-redaction gates apply | Retain Chain only after Q-011 acceptance | Pending confirmation |

## D3B implementation plan: configure while closed

1. Keep `DELIVERY_ENABLED=false` and verify cached false.
2. Export a redacted, exact backup of current Location Delivery settings,
   hours, area count, tax switches, and current revision; do not export secrets
   or unrelated rows.
3. Create only approved `QA-D3-AREA-*` area rows through the admin UI where
   possible. Record area type, priority, non-sensitive boundary summary, and
   conditions.
4. Configure the approved fee, minimum, free threshold, hours, lead time,
   interval, and future-order values.
5. Read the saved values back without changing the global flag.
6. Use a disposable same-image Job or a 0%-traffic tagged revision with
   synthetic addresses to test inside, outside, exact boundary, unrecognized,
   and address-change behavior.
7. Test fee, equal/below/above minimum, equal/below/above free threshold,
   Pickup fee removal, client fee/area spoof rejection, hours, ASAP, and the
   approved future-order state.
8. Trigger a controlled synthetic geocoder failure and close Q-011 only if
   logs and public errors contain no full address, query URL, credential,
   SQLSTATE, geometry, or internal class/ID details.
9. Verify Orders API Delivery writes still fail closed and no real Order is
   submitted.
10. Delete exact synthetic rows and Jobs unless an approved QA area is retained
    for D3C; record every retained row by synthetic name.

If D3B requires direct database configuration, it must use a transaction,
exact Location and area identifiers, before/after verification, and a precise
rollback. Broad updates or deletes are forbidden.

## D3C implementation plan: isolated enablement and acceptance

1. Build the current approved full SHA without changing business code.
2. Create a new Canada staging revision whose only intended Delivery gate
   change is `DELIVERY_ENABLED=true`.
3. Route 0% traffic and expose a tagged URL.
4. Verify cached true, health, assets, logs, database connectivity, and retained
   configuration on the tag.
5. Run the synthetic browser matrix for area, fee, minimum, free threshold,
   hours, session/UI, Pickup regression, and security.
6. Confirm Birthday, Reservation, admin, Livewire, media, and health regressions
   remain clean.
7. Route 100% Canada staging traffic only after acceptance. Do not change
   production, domain, payment, or outbound mail.
8. Create the documentation-only `Record Delivery D3 staging validation` PR.

## Rollback

- Immediate gate rollback: deploy a revision with
  `DELIVERY_ENABLED=false` and verify Pickup-only behavior.
- Runtime rollback: return traffic to
  `le-chateau-canada-staging-d2fix-31821289`.
- Configuration rollback: restore the exact D3B before-values for Delivery
  settings/hours and remove only the explicitly named `QA-D3-AREA-*` rows.
- Do not delete historical Orders, Location settings, Render staging,
  DigitalOcean resources, Cloud SQL, Cloud Storage, or secrets.

## QA cleanup checklist

- Remove exact synthetic cart/session/address/customer/order/API data, if any.
- Remove disposable Jobs and 0% test revisions only when separately approved;
  leaving a tagged revision at 0% is acceptable for audit/rollback.
- Confirm Orders, Customers, Birthday, Reservation, payment, and media baselines
  are unchanged.
- Confirm no real address remains in logs or database.
- Confirm `MAIL_MAILER=log`, no payment integration, and no production change.
- Confirm Render staging remains usable as fallback.

## Open gates

- The approved polygon, fee, free threshold, minimum, and weekly hours are
  configured. Remaining pending decisions in the table must be resolved before
  D3C enablement if they affect the launch acceptance matrix.
- Q-011 is `Resolved` for the current Canada staging tested failure path. The
  final clean same-image matrix found no sensitive or internal disclosure and
  confirmed fail-closed behavior. `DELIVERY_ENABLED=false` remains unchanged;
  any D3C enablement requires a separate isolated revision and approval.
- Public Nominatim fallback is not approved for production Delivery traffic;
  stable identification, attribution, shared rate limiting, and an accepted
  replacement/operating model remain a separate production gate.
- Q-005, Q-010, and fresh-install migration ordering remain independent and
  are not changed by D3A.
