# D3C Progress

Snapshot of where Delivery D3C acceptance stands. Overwritten as work moves;
it is not a history. `CHANGELOG_AI.md` keeps the history.

Last updated: 2026-08-22, Saturday afternoon.

Delivery is being switched on for testing only, on copies of the staging site
that receive no visitors. The live site is untouched throughout, and has been
verified unchanged before and after every deployment.

## The weekday defect: fixed, awaiting one runtime confirmation

Delivery reported itself closed on Friday, inside its own opening hours.

**Cause:** the site runs in Canadian French, and under that setting the date
library treats the week as starting on Sunday, while the opening-hours screen
numbers its rows starting on Monday. Hours entered as Monday to Friday were
applied as Sunday to Thursday, so Friday and Saturday closed.

**Fixed** in `App\Delivery\WeekdayScheduleCorrection`, deployed to
`d3c-fix-be6835a9` at 0% of visitors. Proven by unit tests that first assert the
faulty condition is present, then assert the correction repairs it.

**Not yet confirmed in the deployed environment.** See the window below.

Pickup never showed the problem because pickup is open every day, so a one-day
shift still lands on an open day. The same is true of the general opening hours,
which reservations use. Birthday bookings are unaffected: they do not use weekly
schedules at all. Both were confirmed against the stored rows, which show no
disabled day outside delivery.

## The Sunday window is one-shot, and it is the last chance

**Priority: high. Sunday only.**

Of the seven days, only two tell the fixed and unfixed versions apart:

| Day | Unfixed | Fixed |
| --- | --- | --- |
| Sunday | open | closed |
| Friday | closed | open |
| Monday to Thursday | open | open |
| Saturday | closed | closed |

Friday's window closed at 21:00 on 2026-08-21; the next is a week away. That
leaves Sunday.

**Changing the delivery hours to every day destroys the difference
permanently**, because then every day is open on both versions. So the hours
must not be changed until the Sunday reading is taken.

If the Sunday window is missed and the hours are changed regardless, the fix
stands on unit tests alone, and the record must say plainly that it was never
confirmed in the deployed environment.

**How to take it:** on Sunday between 12:00 and 22:00 Montreal, open both
`d3c-fix-be6835a9` and `d3c-g2-1f8f0c75`, select delivery on each, and compare.
The unfixed copy should offer delivery; the fixed copy should refuse it.

There is no way to take this reading sooner. The hours display is built from the
saved settings rather than from the corrected schedule, so it does not move. The
only place the corrected schedule is consulted is the order-time check, and
while the shop is closed that check returns early, before the schedule is
reached, identically on both copies.

## Acceptance checks

This table is the acceptance record. **Evidence type matters**: *rendered* means
a screen element was observed, *executed* means the action was carried through
and the outcome confirmed. A check whose wording promises something is offered,
preserved, or reachable is not passed on *rendered* alone.

| What is being checked | Status | Evidence |
| --- | --- | --- |
| Address provider unavailable: order refused safely | Passed | Executed |
| Address provider unavailable: nothing technical shown | Passed | Rendered, which is what this check is about |
| Address provider unavailable: logs stay clean | Passed | Executed |
| Address provider unavailable: pickup still offered **and reachable** | Passed | Executed: followed through from the basket to a working pickup checkout |
| Address provider unavailable: basket kept | Passed | Executed: the item survived a failed address lookup, with no delivery fee added |
| Delivery closed before 12:00 and after 21:00, every day | Not passed | Rendered only; no order was attempted and refused |
| Delivery open during its hours | **Failed** | Executed. See above |
| Address lookup works with one provider pinned | Passed | Executed |
| An in-area address is accepted | Passed | Executed, one address |
| Delivery area: outside, and exactly on the boundary | Blocked | Needs delivery to be orderable |
| Delivery fee of CA$5.00 below the free threshold | Partly seen | Rendered as a running total; not confirmed against a real basket |
| Free delivery at CA$80.00, minimum CA$20.00, at the edges | Blocked | Needs delivery to be orderable |
| Money reads and compares correctly in Canadian French | Not started | Decimal separator differs; the three amounts above are compared numerically |
| Unrecognised, incomplete, out-of-area addresses; changing an address | Not started | |
| Switching between pickup and delivery, both ways | Not started | |
| Old sessions cleared, totals correct, resistant to tampering, API rules enforced | Not started | |
| Desktop, mobile at 390px, keyboard use, clean browser console | Not started | |
| Nothing else broken: pickup, birthday, reservations, admin, media, health | Not started | |
| No real orders, customers, reservations, payments, or emails created | Holding | Nothing real created so far |
| Test data cleaned up afterwards | Not started | |

### A basket cannot be filled while the shop is closed

Answered on 2026-08-21 at 22:31, after pickup had closed at 22:00, and confirmed
against the opposite case on 2026-08-22 at 14:0x during opening hours.

Adding to the basket while closed is **refused at the moment of adding**, not
deferred to checkout. The basket stays empty and the customer is told why:

> Your selected order time is outside our Cueillette hours

During opening hours the same action succeeds immediately.

For the owner: a customer arriving before opening cannot choose their food in
advance and come back to it. They are told why, but they have to start again
during opening hours. The facts are recorded here; whether that matters enough
to change is a business question.

The refusal message is in English on a French site, and mixes in the French word
Cueillette. That is a third locale-related defect, after the language fallback
and the weekday shift.

### The owner's delivery on/off switch

The holiday plan agreed in decision 6 is "turn delivery off in the admin for the
day, turn it back on tomorrow". That switch has never been tested. Four checks,
all to the *executed* standard:

| Check | Status |
| --- | --- |
| How long after switching off does a customer actually see it? Measure it; do not assume it is instant | Not started |
| A customer already part-way through a delivery order when it is switched off: do they fall back to pickup with the basket intact, or hit an error? | Not started |
| Does delivery disappear entirely, or is the customer only stopped at checkout after choosing their food? | Not started |
| After switching back on, does everything return, or is a cache clear or redeploy needed? | Not started |

This needs writing to a setting shared with the live site, so it stops for
approval first, with the exact prior value and the way back recorded.

## Timed checks and their windows

Opening hours are stored in the shared database, so they are never edited for a
test. Each check waits for its real window.

| Check | Window |
| --- | --- |
| Delivery appears open on Sunday, confirming the cause | Sunday only |
| Delivery stops taking orders at 21:00 | Any evening, once delivery works |
| Pickup still open after delivery has stopped | Any evening, once delivery works |
| Pickup stops at 22:00 | Done 2026-08-21 22:31, executed: an order was attempted and refused |
| Can a basket be filled while closed | Done 2026-08-21 22:31. See below |

Delivery's own timing checks cannot be taken until it can be ordered at all;
until then, closed cannot be told apart from the defect.

The one-hour gap between delivery stopping at 21:00 and the shop closing at
22:00 is deliberate. It comes from the two schedules differing and should not be
recorded as a missing cut-off.

## Test copies of the site currently deployed

All receive 0% of visitors.

| Name | Purpose | Usable for multi-step flows |
| --- | --- | --- |
| `d3c-fix-be6835a9` | The weekday fix, pinned to Google. Use for the Sunday reading | Yes |
| `d3c-g2-1f8f0c75` | Unfixed comparison, pinned to Google. Use for the Sunday reading | Yes |
| `d3c-pu2-1f8f0c75` | Provider-unavailable testing, no address provider | Yes |
| `d3c-g-1f8f0c75` | Superseded by `g2` | No |
| `d3c-pu-1f8f0c75` | Superseded by `pu2` | No |
| `d3c-25f9813b` | Earlier check of log redaction | No |
| `d3c-a2ee559c` | First D3C copy | No |
| `d3c-e9a4f7ca` | Failed to start. Delete first when cleaning up | No |

Cleaning any of these up needs explicit confirmation and is not done yet.

## Outstanding

| Item | Owner | When |
| --- | --- | --- |
| Report what the admin shows for delivery hours, and where the delivery on/off switch is | User | Next |
| Create the budget alert | User | Today |
| Upgrade to a paid account, and in the same sitting set the Geocoding daily cap to 500, create the budget alert, and split the browser key from the server key | User | Mid-September 2026 |
| Decide whether to enable the Places API | User | Before launch |
| Propose a fix for the week-start problem, with its blast radius assessed first | Agent | After the admin report |
| Apply the new delivery hours, every day 12:00-21:00 | Agent | Only after the week-start problem is settled |
| Static check that stops an exception being written into a log context | Agent | Deferred |

### Address autocomplete does not work today

The Places API is not enabled on the project, so address suggestions never
appear. Every search shows "No suggestions found", and a customer must type a
complete, correctly formatted address for delivery to be offered at all, which
is hardest on a phone. Enabling Places is a business decision: its pricing works
differently from address lookup and needs costing first.

## Known problem carried over

The Google Maps key is visible in the public page source and has no restriction
on where it may be used from. The same key also authorises paid address lookups.
This is **not new and not caused by this testing**: the live site has always
exposed it. Accepted for now because the staging site gets almost no traffic;
fixed by splitting the key during the September upgrade.

## Structural limit on what can be tested

Every copy of the site shares one database. Settings stored there cannot be
varied for one copy alone, and changing them would change the live site too.
This has already blocked two things: making one copy use an invalid address-
provider key, and giving one copy different opening hours. Anything resting on
those settings can only be verified by waiting for real conditions. A separate
database for isolated testing would remove the limit; that is a future
improvement, not part of this phase.

## Next action

Take the Sunday reading between 12:00 and 22:00 Montreal, comparing
`d3c-fix-be6835a9` against `d3c-g2-1f8f0c75`. Until then, do not change the
delivery hours. Meanwhile the checks that do not need delivery to be orderable
can continue on `d3c-pu2-1f8f0c75` during opening hours.
