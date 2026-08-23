# D3C Progress

Snapshot of where Delivery D3C acceptance stands. Overwritten as work moves;
it is not a history. `CHANGELOG_AI.md` keeps the history.

Last updated: 2026-08-22, Saturday night.

Delivery is being switched on for testing only, on copies of the staging site
that receive no visitors. The live site is untouched throughout, and has been
verified unchanged before and after every deployment.

## The weekday defect: fixed, awaiting one runtime confirmation

Delivery reported itself closed on Friday 2026-08-21, inside its own opening
hours, and refused an as-soon-as-possible order. That observation is now a
**historical reading**: it was taken while the ASAP/later setting was "None",
and the user changed that setting to ASAP-only on 2026-08-22 (see the setting
change below). It is kept as the record of what was seen on Friday and is no
longer cited as evidence of current state. The cause below does not rest on
it: it was found in source and is proven by unit tests.

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

**The path was walked end to end on Saturday and works.** Selecting delivery
needs a delivery address, and the address suggestion feature is broken (see the
findings below), but the home-page widget accepts a typed address without
suggestions: enter the restaurant's own street address into "Entrez votre
adresse de livraison" and press the check button. The menu then opens with
delivery selected. Add any item and complete its options; the add is either
refused with an hours message or the item enters the basket, and that is the
reading. On Saturday, closed on both versions, both copies refused with
"Your selected order time is outside our Delivery hours" and kept the basket
empty, agreeing exactly as the table above predicts. Sunday they must
disagree: the unfixed copy accepts the item, the fixed copy refuses.

There is no way to take this reading sooner. The hours display is built from the
saved settings rather than from the corrected schedule, so it does not move. The
only storefront path that consults the corrected schedule is the order-time
check, `Location::checkOrderTime()`, and it consults it twice: first an early
return when future orders are off and the delivery schedule says closed *now*,
then `isOpenAt()` for the order time. On Saturday the delivery schedule is
closed on both versions, so the early return fires identically on both and
nothing can be learnt. On Sunday that same early return is where the two part:
the unfixed schedule says open, the fixed schedule says closed.

**The ASAP-only setting does not touch this reading.** Verified independently
in source on 2026-08-22, after the user changed the setting, rather than taken
on trust. `time_restriction` is read in exactly three places: whether the
"later" choice is offered (`hasLaterSchedule()`), whether a later choice is
accepted by the dialog (`checkScheduleRestriction()`), and whether ASAP is
available at all (`hasAsapSchedule()`, which only a later-only value disables).
None of them is on the path that decides open or closed. Under ASAP-only the
order time is "now" whenever the schedule is open now, and the first available
slot otherwise, and both gates of the order-time check read the working
schedule that the fix corrects. Sunday, unfixed: schedule open, order time now,
add accepted. Sunday, fixed: schedule closed, early return, add refused. The
procedure above is unchanged.

## The delivery minimum: the highest-priority open defect

**Priority: highest among open defects.** The storefront enforces a CA$80.00
minimum on delivery where the decision is CA$20.00. CA$20.00 to CA$80.00 is
the main band of delivery orders, and in that band the checkout button is
disabled, so delivery is effectively unusable rather than merely unattractive.

Every link but one is now verified: the stored minimum is CA$20.00 (user
read-back); the stored fee rules are "Free above $80.00" then "$5.00 below
$80.00", in that order (read back from the storefront's own information
panel); and the vendor's semantics under exactly that shape are established
by `tests/Feature/Delivery/DeliveryAreaRuleMinimumTest.php` (PR #98):
the enforced minimum is 80 and a CA$50.00 basket fails the minimum-order
check. The one link left is that the deployed copy behaves as the installed
vendor code does, which Monday's CA$20.00 to CA$80.00 basket confirms.
Details and the remedy options are in the Saturday findings below.

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
| Delivery closed before 12:00 and after 21:00, every day | Partly passed | Executed for Saturday, closed all day: a delivery order was attempted on both copies on 2026-08-22 in the afternoon and refused at the moment of adding, basket kept empty. The before-12:00 and after-21:00 legs on open days still need their windows |
| Delivery open during its hours | **Failed**, historical reading | Executed on Friday 2026-08-21 under the ASAP/later setting "None", since changed to ASAP-only. Kept as history, not cited as current state. The cause is fixed in code and awaits the Sunday confirmation |
| Address lookup works with one provider pinned | Passed | Executed: the home-page widget geocodes the typed address server-side and it works on both copies |
| Address autocomplete on the Google-pinned copies | **Failed** | Executed: the suggestion call is refused by the provider, and the raw technical error is shown to the customer. See below |
| An in-area address is accepted | Passed | Executed, one address |
| Delivery area: outside, and exactly on the boundary | Blocked | Needs delivery to be orderable |
| Delivery fee of CA$5.00 below the free threshold | Partly seen | Rendered as a running total; not confirmed against a real basket |
| Free delivery at CA$80.00, minimum CA$20.00, at the edges | Blocked | Needs delivery to be orderable. The enforced minimum is CA$80.00, not CA$20.00, established by test; see the next row |
| Delivery minimum shown and enforced as CA$20.00 | **Failed**, blocking; fix written, not deployed | Confirmed defect, highest priority. Stored minimum CA$20.00 (user read-back); stored rules "Free above $80.00" then "$5.00 below $80.00" (storefront read-back); vendor semantics established by test. Fix: `App\Delivery\DeliveryOrderType`, tested, PR at Ready to merge. Monday: basket on the deployed copy first, then deploy the fix and repeat. See below |
| Money reads and compares correctly in Canadian French | Not started | Decimal separator differs; the three amounts above are compared numerically |
| Unrecognised, incomplete, out-of-area addresses; changing an address | Not started | |
| Switching between pickup and delivery, both ways | Not started | |
| Old sessions cleared, totals correct, resistant to tampering, API rules enforced | Not started | |
| Mobile at 390px: no sideways scrolling, no broken images | Passed | Executed |
| Browser console clean | Passed | Executed: no messages |
| Keyboard: focus is visible, no tab-order traps | Passed | Executed |
| Keyboard: an item can be added to the basket | Passed | Executed by hand: the user, on a real keyboard. Tooling cannot deliver this keypress; see below |
| Every control announces what it is | **Failed** | Executed: the add buttons have no name at all |
| Nothing else broken: pickup, birthday, reservations, media, health | Passed | Executed: ten pages, all served |
| Nothing else broken: admin | Not started | No admin access |
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
Cueillette. That is not a new defect: the English comes from the missing
translations recorded as Q-001, and the French word is the order type name,
which the owner entered as configuration. Quebec's French-language requirements
make this a production gate rather than a cosmetic issue; see
`CLAUDE_HANDOFF.md` section 11.

### Accessibility findings on the menu

The confirmed defects here are in the theme rather than in anything Delivery
added, so they are present on the live site too. None was caused by this phase.

**The add buttons have no name.** Each menu item has a round "+" button
containing only a decorative icon, with no text, no label, and no title. A
screen reader announces "button" and nothing else, so a blind customer cannot
tell which dish a button belongs to, or that it adds to the basket at all.
Verified by inspection of every such button on the page. The keyboard result
below does not touch this finding: they are different accessibility
dimensions, and a customer who depends on a screen reader still hears only
"button", whichever input device they use.

**The keyboard can add an item: passed.** Settled on 2026-08-22 by the user,
by hand, on a real keyboard: Enter on the focused button adds the dish to the
basket. Ordering by keyboard is possible.

The earlier automated reading is withdrawn, because that test never ran. An
event probe installed on the button captured zero events while Enter was sent
through the browser extension — not even a keydown — while a control run that
dispatched keydown and click from page JavaScript was captured normally, so
the probe itself worked. The keypress never reached the page, and "the page
did not react" described the tooling, not the site. Both automation toolsets
share this limitation, so keyboard-activation checks are settled by a person
or recorded as not run.

Six text links are shorter than the 24 pixels that the accessibility guidance
asks for: the two language links, "Back", "More info", "change", and the cookie
notice link. Widths are fine; only the heights are small. Also in the theme.

### Saturday delivery findings on the Google-pinned copies

Taken on 2026-08-22 in the afternoon, on `d3c-fix-be6835a9` and
`d3c-g2-1f8f0c75`, both pinned URLs confirmed by hostname.

**Delivery refuses orders while closed, on both copies.** With delivery
selected and an in-area address set, adding an item on a Saturday is refused
at the moment of adding with "Your selected order time is outside our Delivery
hours", and the basket stays empty. The order-type dialog and the menu header
both show CLOSED, and the checkout button is replaced by CLOSED. These readings
were taken under the current ASAP-only setting: the dialog already offered no
"later" choice at 15:1x. The earlier remark that no order dates or times were
offered is withdrawn as evidence; under ASAP-only with future orders off the
date list is empty on a closed day whatever the schedule, so it carries no
weight.

The refusal message is English on a French site, like the pickup one: it
merges into Q-001, with "Delivery" in place of "Cueillette".

**The address suggestion feature is broken, and it fails loudly.** Typing into
the delivery address field calls the provider's suggestion service, which
refuses the request because that service was never switched on for the
project. The customer then sees the raw technical error in the ordering
dialog: the provider's web address, the full text of what they typed, and the
project's internal number. Nothing sensitive to any customer is revealed, but
it is unprofessional, confusing, and exposes internals that a page should not
show. Two parts need separating:

- *The service being off* is deployment configuration, not code, and may be
  deliberate: the same suggestion service costs money per call. Whether to
  enable it is an owner and deployment decision.
- *The raw error reaching the customer* is a real defect regardless. A failed
  suggestion lookup should degrade to "no suggestions", which is exactly what
  the same page already renders next to the error.

Ordering is still possible without suggestions: the home-page widget accepts a
fully typed address and checks it server-side, which works on both copies.
That server-side path is the one the earlier "address lookup" pass exercised.

**The basket shows "Min. Order Amount: $80.00" for delivery: confirmed
defect.** Settled on 2026-08-22 by the user's read-back of the stored value,
`min_order_amount = 20.00`, which agrees with the decision. The storefront is
not misconfigured; it computes the CA$80.00. Traced in source:

- The label prints `Location::minimumOrderTotal()`, which for delivery is the
  larger of the stored minimum and the delivery area's own minimum.
- The area's minimum is derived from its fee rules. The rules were saved as
  "free at or above CA$80.00" then "CA$5.00 below CA$80.00". For any basket
  under CA$80.00 the matched rule is the *below* rule, and the vendor treats a
  matched below-rule's threshold as the minimum order total. So the area
  minimum is CA$80.00, and the larger of 80 and 20 is 80.
- The same value feeds the checkout gate. `CartBox::hasMinimumOrder()` asks
  whether the subtotal is below `minimumOrderTotal()`, and the checkout button
  is disabled while it is. Nothing of the stored CA$20.00 reaches the customer. The label is also English on a French site, like the other Q-001 strings.

So this is neither a wrong stored value nor purely a label: it is the vendor's
reading of a "below" fee rule, triggered by the shape the rules were saved in.

**Severity: blocking.** The user first read it as deterrent, on the premise
that the system would not actually refuse at CA$80.00, and withdrew that
reading on 2026-08-22 once the consumption path was traced: the stored value
had been read, its effect inferred. The vendor's behaviour is now established
rather than predicted, by `tests/Feature/Delivery/DeliveryAreaRuleMinimumTest.php`
(PR #98), run in a container on PHP 8.3.32 / PHPUnit 11.5.56, 5
tests and 35 assertions: under the recorded rule shape the area minimum is
CA$80.00 at subtotals 19, 20, 50, 79, 80, 81 and 100, the enforced delivery
minimum is 80, and a CA$50.00 basket fails the minimum-order check. Business
impact: CA$20.00 to CA$80.00 is the main band of delivery orders; a disabled
checkout there means delivery is effectively unusable. This is the
highest-priority open defect.

**The stored rule shape is confirmed, read-only.** The user could not read the
stored rules on 2026-08-22: Cloud SQL Studio was blocked by a console dialog,
and the admin keeps the rules inside an editor that had to be opened. The
storefront prints them itself: the "More info" panel on the menu page lists
each delivery area's rules as the vendor sorts them, by priority. On
`d3c-fix-be6835a9` it reads "D3 Montreal Delivery Area: Free above $80.00 /
$5.00 below $80.00", in that order, matching the D3B record. That panel is
the read-back route for fee rules when neither admin nor database is at hand.

**What Monday still settles:** only that the deployed copy behaves as the
installed vendor code does. Add items to a delivery basket between CA$20.00
and CA$80.00 and confirm the checkout button is disabled with the minimum
message; that is the executed evidence.

**Remedies: evaluated, none executed, decision with the user.** The vendor
takes the first valid rule in ascending priority, and in the admin the rules
are a sortable list whose row order is that priority. Two facts frame the
choice. First, the rules as stored today, "free at or above CA$80.00" and
"CA$5.00 below CA$80.00", cover disjoint ranges, so their order does not
matter; the pair is order-robust. Second, the owner edits settings directly
and has reordered or changed them before.

- **A: reshape the rules only.** "Free at or above CA$80.00" followed by
  "CA$5.00 on all orders". No code; a shared-setting write that stops for
  approval. It fixes the minimum, and it introduces an order dependency the
  current shape does not have: the all-orders rule matches every basket, so
  if it is ever dragged above the free rule, a CA$100.00 basket pays CA$5.00
  with no error anywhere. The test established this. The failure is silent,
  customer-facing, and caused by exactly the kind of owner action that has
  already happened once.
- **A plus a guard.** The guard would have to watch the *stored* rule order,
  so it cannot be a test, which sees fixtures; it has to be a runtime probe,
  for example reading the storefront's "More info" panel and asserting "Free
  above $80.00" is listed first. For that probe to mean anything it needs a
  trigger and a reader: a schedule that runs it, and an alert someone
  receives. The project has neither today: CI has never run, FP-1 is run by
  hand, and the only standing check is the agent's read-back at the start of
  a session, which finds a reorder only when a session happens. Until that
  infrastructure exists, "A plus a guard" is A with a promise.
- **B: a project-side override of the Delivery minimum.** The order type is
  built through the container (`OrderTypes::makeOrderTypes()` resolves each
  class), so a binding of the vendor's Delivery class to a project subclass
  that overrides `getMinimumOrderTotal()` is enough: the minimum becomes the
  stored value, with an area minimum counted only when the matched rule
  says delivery is unavailable, which is the vendor's own way of expressing a
  per-area minimum. The label and the checkout gate both read that one
  method, so both are corrected together. The rules keep their current,
  order-robust shape and the owner's understanding of them. It is code:
  Level 1, then a 0% deployment and acceptance, then maintenance across
  vendor upgrades, where a changed contract fails loudly, as a fatal or a
  failing test, rather than silently. This is the pattern the project already
  uses for vendor behaviour it cannot accept: `LocationDeliveryAction`,
  `GeocoderChainOverride`, `WeekdayScheduleCorrection`.

**Decision: B, taken by the user on 2026-08-22.** Implemented as
`App\Delivery\DeliveryOrderType`, bound in `AppServiceProvider`, with
`tests/Feature/Delivery/DeliveryOrderTypeMinimumTest.php`: 6 tests and 25
assertions on PHP 8.3.32 / PHPUnit 11.5.56. The whole Delivery suite with the
change: 105 tests, 223 assertions, 0 failures, and the same 25 environment
errors (no database, no APP_KEY in the container) as the unchanged base at 99
tests and 197 assertions.
The stored fee rules are untouched and keep their order-robust shape. The
label path and the gate path are asserted separately; the reverse assertion
shows an area that is unavailable below a total still blocks, so the override
narrows what counts as an area minimum without discarding it. Known cost,
recorded in `CLAUDE_HANDOFF.md` section 10 and the owner's guide: a future
per-area minimum must be expressed as "delivery is not available below X",
not as a fee rule below X. The PR stops at Ready to merge for the user's
confirmation.

**Order of operations on Monday, fixed by the user:** first the CA$20.00 to
CA$80.00 basket on the deployed copy as it stands, to confirm the deployed
state and record that the checkout was in fact blocked before the fix; only
then merge and deploy the fix to a 0% copy and repeat the basket as the
acceptance of the fix. Reversing the order loses the before-and-after
contrast.

**Visibility to live customers, verified.** On 2026-08-22 the main hostname
was read directly: the order-type dialog offers only Cueillette, the "More
info" panel has no Delivery Areas section, and there is no delivery address
widget. In code, both the Delivery order type and the panel's rule section
hang on `DeliveryAvailabilityGate::isEnabledForLocation()`, which requires
`DELIVERY_ENABLED=true`; the main-traffic revision carries `false`. A rule
change is therefore invisible to live customers and visible only on the 0%
copies. This is a statement about the storefront; the rules themselves live
in the shared database either way.

**Operating note for the owner** (also in `ADMIN_CONFIGURATION_GUIDE.md`,
in the owner's language): the order of the delivery fee rules in the admin
is the order in which the system applies them, and it uses only the first
rule that matches a basket. "Free delivery from CA$80.00" must stay as the
first row. Dragging the rows changes what customers are charged, silently,
with no warning. After any change, open the storefront menu page's "More
info" panel and check that "Free above $80.00" is listed first.

One menu observation in passing: Puff-Puff carries a stored minimum quantity
of 3, so its dialog opens at three pieces. That is stored menu data, possibly
intentional; noted for the owner rather than as a defect.

### Setting change on 2026-08-22: as-soon-as-possible only

The user changed the stored ASAP/later restriction to ASAP-only for both
delivery and pickup on 2026-08-22, intentionally, and said so. This is a
change to the decision recorded on 2026-08-20, not a new fact about the same
decision, and the decision table in `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md`
now carries the original value, the new value, and the date.

Stored values as read back by the user: delivery `time_restriction = 1`,
pickup `time_restriction = 1`, pickup minimum CA$0.00, lead time 25 minutes,
interval 15 minutes, future orders off for both. Storefront read-back by the
agent at 20:1x the same day, both order types: the dialog offers only "Dès
que possible" and no later choice, which is exactly what ASAP-only renders.
One cosmetic quirk: with delivery closed, the dialog still shows an empty
date/time picker, because the "as soon as possible" state is computed from
the schedule being open. Theme-level; noted, not pursued.

Consequences, each assessed rather than assumed:

1. **The Friday reading is historical.** See the top of this document.
2. **No acceptance row depends on a delivery time picker.** Checked row by
   row. The checks that would have exercised a chosen later slot, the last
   accepted slot before 21:00 and the "add lead time to start" first-slot
   behaviour, are now exercised through an as-soon-as-possible attempt at
   the boundary instead, and read the same.
3. **Q-010 is masked, not fixed.** The scheduled pickup time select is no
   longer rendered, so its binding defect cannot be reached. It is unchanged
   in the theme and returns the moment a later choice or future orders are
   re-enabled. It must not be closed on the strength of being unreachable
   from the storefront. Recorded against Q-010 in `CLAUDE_HANDOFF.md`
   section 10, which this statement matches.
4. **The Sunday reading is unaffected.** Verified in source, above.

The user edits shared settings directly and will say so; the agent still
reads them back before and after every key reading rather than assuming they
match the last record. Written into `AGENT_WORKFLOW.md` section 8b.

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
| Delivery refused on a closed day | Done 2026-08-22 afternoon, executed on both copies: an order was attempted and refused |
| Delivery minimum: a CA$20.00 to CA$80.00 basket finds the checkout button disabled | Monday, once delivery is orderable on the fixed copy, **before** the fix is deployed; confirms the deployed state and records the blocked state for contrast |
| Delivery minimum fix: the same basket checks out at the stored CA$20.00 minimum and the label reads CA$20.00 | Monday, after the basket above, on a 0% copy carrying the fix |
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
| Report what the admin shows for delivery hours, and where the delivery on/off switch is | User | Done 2026-08-22. Evidence: a raw read of the stored `ti_working_hours` rows, not the admin screen: the delivery rows carry weekday 0-4 with status 1 and weekday 5-6 with status 0, i.e. Monday to Friday as stored |
| Create the budget alert | User | Today |
| Upgrade to a paid account, and in the same sitting set the Geocoding daily cap to 500, create the budget alert, and split the browser key from the server key | User | Mid-September 2026 |
| Decide whether to enable the Places API | User | Before launch |
| Propose a fix for the week-start problem, with its blast radius assessed first | Agent | Done. The targeted correction, `App\Delivery\WeekdayScheduleCorrection`, is merged (PR #90) and deployed to `d3c-fix-be6835a9`; its blast radius is the delivery, pickup, and opening schedules only. Sunday confirms it in place. This Done does not release the hours change two rows below, which stays blocked until that confirmation is in hand |
| Assess a global change of the week start, so that `WorkingHour::getDay()` stops being locale-shifted for any future caller. A different thing from the merged targeted correction above, which rebuilds schedules and leaves `getDay()` as it is; a global change moves every week boundary in the framework and needs its blast radius assessed first | Agent | Deferred; not needed for D3C |
| Apply the new delivery hours, every day 12:00-21:00 | Agent | **Not before the Sunday 2026-08-23 A/B reading has been taken and judged passed.** The precondition is that runtime confirmation, not the merged code fix in the row above. If the reading does not pass, the hours are not changed and the cause is re-investigated. Changing the hours permanently destroys the discriminating condition, because both copies become open every day and can never again be told apart; so until the reading's result is in hand this item is not executed under any circumstances |
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
