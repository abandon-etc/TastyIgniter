# D3C Progress

Snapshot of where Delivery D3C acceptance stands. Overwritten as work moves;
it is not a history. `CHANGELOG_AI.md` keeps the history.

Last updated: 2026-08-21, 12:10 Montreal.

Delivery is being switched on for testing only, on copies of the staging site
that receive no visitors. The live site is untouched throughout, and has been
verified unchanged before and after every deployment.

## Blocking problem found today

**Delivery reports itself closed during its own opening hours.** On Friday
2026-08-21 at 12:04 Montreal, with delivery hours configured as Monday to
Friday 12:00-21:00, the storefront showed `CLOSED` for delivery and the server
offered **no delivery time slots at all**. Pickup at the same moment was
available. Both are configured to start at 12:00.

Delivery's 25-minute preparation time cannot explain it: the earliest slot
would be about 12:29, still inside the window.

This blocks most of the remaining delivery checks, because fees, thresholds,
and checkout all require a delivery order to be possible. Root cause is not yet
identified.

## Acceptance checks

This table is the acceptance record. **Evidence type matters**: *rendered*
means a screen element was observed, *executed* means the action was carried
through and the outcome confirmed. A check whose wording promises something is
offered, preserved, or reachable is not passed on *rendered* alone.

| What is being checked | Status | Evidence |
| --- | --- | --- |
| Provider unavailable: order refused safely | Passed | Executed: real submission, server refused |
| Provider unavailable: nothing technical shown | Passed | Rendered, and rendering is what this check is about |
| Provider unavailable: logs stay clean | Passed | Executed: real request, its own logs read |
| Provider unavailable: pickup still offered | **Not passed** | Rendered only. The offer appeared but was never followed, so pickup being reachable is unverified |
| Provider unavailable: basket kept | Not started | Must run on a URL-pinned copy |
| Delivery closed outside opening hours | **Not passed** | Rendered only. A `CLOSED` label was seen; no order was attempted and refused |
| Delivery open during opening hours | **Failed** | Executed: no time slots offered at 12:04 on a Friday. See above |
| Address lookup works with one provider pinned | Passed | Executed: address accepted, order type changed to delivery |
| An in-area address is accepted | Passed | Executed, for one address |
| Delivery area: outside, and exactly on the boundary | Not started | |
| Delivery fee of CA$5.00 below the free threshold | Partly seen | Rendered: CA$5.00 appeared as the running total. Not confirmed against a real basket |
| Free delivery at CA$80.00, minimum CA$20.00, at the edges | Not started | Blocked by the closed-during-hours problem |
| Unrecognised, incomplete, out-of-area addresses; changing an address | Not started | |
| Switching between pickup and delivery, both ways | Not started | |
| Old sessions cleared, totals correct, resistant to tampering, API rules enforced | Not started | |
| Desktop, mobile at 390px, keyboard use, clean browser console | Not started | |
| Nothing else broken: pickup, birthday, reservations, admin, media, health | Not started | |
| No real orders, customers, reservations, payments, or emails created | Holding | Nothing real created so far |
| Test data cleaned up afterwards | Not started | |

## Timed checks and their windows

Opening hours are stored in the shared database, so they are never edited for a
test. Each check waits for its real window.

| Check | Window | Note |
| --- | --- | --- |
| Delivery stops taking orders at 21:00 | Tonight, around 20:55 and 21:05 | |
| Pickup still open after delivery closes | Tonight, around 21:05 | The most informative point: it shows the two schedules are independent |
| Pickup stops at 22:00 | Tonight, around 22:05 | |
| Closed all weekend | Saturday or Sunday only | Missing it costs a week |
| Can a basket be filled while closed | Before 12:00 on a weekday, or after 22:00 | Today's window was missed |

The one-hour gap between delivery stopping at 21:00 and the shop closing at
22:00 is deliberate, not an oversight. It is achieved by the two schedules
differing, and should not be recorded as a missing cut-off.

## Test copies of the site currently deployed

All receive 0% of visitors.

| Name | Purpose | Usable for multi-step flows |
| --- | --- | --- |
| `d3c-g2-1f8f0c75` | Main testing, address lookups pinned to Google only | Yes |
| `d3c-pu2-1f8f0c75` | Provider-unavailable testing, no address provider configured | Yes |
| `d3c-g-1f8f0c75` | Superseded by `g2` | No |
| `d3c-pu-1f8f0c75` | Superseded by `pu2` | No |
| `d3c-25f9813b` | Earlier check of log redaction | No |
| `d3c-a2ee559c` | First D3C copy | No |
| `d3c-e9a4f7ca` | Failed to start. Delete first when cleaning up | No |

Cleaning any of these up needs explicit confirmation and is not done yet.

## Outstanding

| Item | Owner | When |
| --- | --- | --- |
| Create the budget alert | User | Today. The only spend alarm available while the account is on trial |
| Upgrade to a paid account, and in the same sitting set the Geocoding daily cap to 500, create the budget alert, and split the browser key from the server key | User | Mid-September 2026. `CLAUDE_HANDOFF.md` section 11 |
| Decide whether to enable the Places API | User | Before launch. See below |
| Find the cause of delivery being closed during its own hours | Agent | Next |
| Static check that stops an exception being written into a log context | Agent | Deferred |

### Address autocomplete does not work today

The Places API is not enabled on the project, so the address suggestions that
should appear as a customer types never do. Every address search shows "No
suggestions found". A customer must type a complete and correctly formatted
address for delivery to be offered at all, which is hardest on a phone.

Enabling Places is a business decision, not a technical one: its pricing works
differently from address lookup and needs costing before it is turned on. The
facts are recorded here so the decision can be made on evidence.

## Known problem carried over

The Google Maps key is visible in the public page source and has no restriction
on where it may be used from. The same key also authorises paid address
lookups. This is **not new and not caused by this testing**: the live site has
always exposed it. Accepted for now because the staging site gets almost no
traffic; fixed by splitting the key during the September upgrade.

## Structural limit on what can be tested

Every copy of the site shares one database. Settings that live there cannot be
varied for one copy alone, and changing them would change the live site too.
This has already blocked two things: making one copy use an invalid address-
provider key, and giving one copy different opening hours. Anything resting on
those settings can only be verified by waiting for real conditions. A separate
database for isolated testing would remove the limit; that is a future
improvement, not part of this phase.

## Next action

Find why delivery reports itself closed inside its configured hours. Then
finish the pickup-reachable and basket-kept checks on `d3c-pu2-1f8f0c75`, and
return to the main table on `d3c-g2-1f8f0c75`. Tonight, take the 20:55, 21:05,
and 22:05 readings.
