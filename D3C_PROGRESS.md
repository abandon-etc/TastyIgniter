# D3C Progress

Snapshot of where Delivery D3C acceptance stands. Overwritten as work moves;
it is not a history. `CHANGELOG_AI.md` keeps the history.

Last updated: 2026-08-24, Monday afternoon.

Delivery is being switched on for testing only, on copies of the staging site
that receive no visitors. The live site is untouched throughout, and has been
verified unchanged before and after every deployment.

## The weekday defect: fixed and confirmed in both directions

**Both discriminating days are now read.** The Friday reverse reading was
taken on 2026-08-28 at 16:58-17:00 EDT by the user's Cowork session
(WebFetch against the two pinned hostnames; result relayed by the user):
`d3c-fix-be6835a9` showed "Delivery dans 25 minutes" — open on Friday,
which is the correct stored schedule — while `d3c-g2-1f8f0c75` showed
"Delivery is CLOSED" — closed on Friday, the shifted schedule's wrong
direction, exactly as the discrimination table below predicted. This
mirrors the Sunday 2026-08-23 reading (unfixed open on a stored-closed
day, fixed closed): the two copies now disagree in both directions, each
in the predicted one. The weekday correction stands on unit tests and on
runtime confirmation in both directions; nothing about it remains open.

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

**Confirmed in the deployed environment on Sunday 2026-08-23.** The A/B
reading was taken at 12:26-12:28 Montreal, inside the window, on both pinned
copies with hostnames verified and the ASAP-only setting read back on each:
the unfixed copy `d3c-g2-1f8f0c75` showed "We are open, Delivery" and
**accepted** an item into a delivery basket (3 × Puff-Puff, CA$14.97, fee
CA$5.00, total CA$19.97); the fixed copy `d3c-fix-be6835a9` showed CLOSED and
**refused** the same add with "Your selected order time is outside our
Delivery hours", basket empty. On a Sunday the stored hours say closed; only
the shifted schedule opens. The two copies agreed on Saturday and disagree on
Sunday, exactly as the table below predicts. The fix stands on the unit tests
and on this runtime confirmation. Nothing below this line about the window
being open is still pending; it is kept as the record of how the reading was
designed.

Pickup never showed the problem because pickup is open every day, so a one-day
shift still lands on an open day. The same is true of the general opening hours,
which reservations use. Birthday bookings are unaffected: they do not use weekly
schedules at all. Both were confirmed against the stored rows, which show no
disabled day outside delivery.

## The Sunday window is one-shot, and it is the last chance

**Taken and passed on 2026-08-23; the Friday reverse reading followed on
2026-08-28; see the section above. With both directions in hand the
discriminating condition has served its purpose, and the user retired it
on 2026-08-28 by approving the hours change to every day 12:00-21:00 —
after that write the two schedules can never be told apart again, and
nothing needs them to be.** What follows is the design of the reading,
kept for the record.

**Priority at the time: high. Sunday only.**

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

## The delivery minimum: resolved and confirmed deployed

**Closed on 2026-08-24.** The contrast test was taken Monday 2026-08-24
(brought forward from Tuesday by the user, present during the run): on
`d3c-fix-be6835a9` under the stored 20/80, a CA$23.99 delivery basket was
blocked — label "Min. Order Amount: $80.00", checkout button disabled, a
direct `/checkout` bounced with the served minimum message. Then `4.x` at
`9a4c1bc8` was deployed as the 0% copy `d3c-min-9a4c1bc8` (first revision
carrying PR #101), and the same basket there read "Min. Order Amount:
$20.00" with the checkout page reachable and no warning. The override is
confirmed in the deployed environment; the fee split was unchanged by it.

One leg stands on unit tests rather than a deployed reading, and now always
will: refusal *below* a stored minimum was never exercised on a deployed
copy while the stored minimum was 20.00, because the approved batch write
moved it to no-minimum immediately after the contrast reading.
`DeliveryOrderTypeMinimumTest` pins that gate (fails at 19, passes at 20);
recorded here so nobody later reads the deployed evidence as covering it.

## The 2026-08-23 decision batch and its fixed execution order

On Sunday evening 2026-08-23 the user relayed an owner decision and two
professional conclusions, recorded durably in
`DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md` (parameter and tax rows) and
`CLAUDE_HANDOFF.md` section 11 (language). The four items:

1. The Delivery minimum is removed: stored CA$20.00 becomes CA$0.00.
2. The free-Delivery threshold moves from CA$80.00 to CA$50.00, keeping the
   disjoint, order-robust rule shape: free at or above CA$50.00, then
   CA$5.00 below CA$50.00.
3. Accountant: the Delivery fee is its own taxable line in the pre-tax
   subtotal, taxed like ordinary items, GST plus QST; rates are read from
   the official canada.ca and Revenu Québec pages at implementation time,
   never from a relay.
4. Language: default pure French, complete French, and a retained English
   switch; Q-002 becomes a required build item.

**The execution order is fixed by the user and must not be reordered.**
On 2026-08-24 the user, present in chat, brought the run forward from the
Tuesday schedule and it began at 14:05 Monday, inside the window. **A and B
are done (2026-08-24); C and D remain, in order.**

- **A. Contrast test first, under the stored 20/80 values — DONE
  2026-08-24.** On the weekday-fixed copy `d3c-fix-be6835a9`:
  a CA$20.00-to-CA$80.00 delivery basket, expecting the checkout blocked at
  the computed CA$80.00 minimum — the record of the deployed defect (this
  add during open hours is also the remaining positive leg of "Delivery
  open during its hours"). Then build `4.x` head and deploy it as a new 0%
  tagged copy (first revision carrying PR #101), URLs pinned, and repeat
  the same basket there: label CA$20.00, checkout enabled — the fix's
  acceptance. FP-1 on the main revision before the first action and after
  the last. This test documents the defect itself; it is why the new
  parameter values wait.
- **B. Only after A: the parameter batch — DONE 2026-08-24, with
  before/after read-back in `ADMIN_CONFIGURATION_TRACKER.md` and the
  storefront verification passed (below 50 pays 5.00 and checks out; 50.00
  exactly and above are free; panel and label agree).** As approved: set the
  stored Delivery `min_order_amount` from 20.00 to 0 and the area rule pair
  from (above/0/80, below/5/80) to (above/0/50, below/5/50), priorities and
  shape unchanged. Read back before and after, record both in
  `ADMIN_CONFIGURATION_TRACKER.md`. Both values affect Delivery only and
  the main-traffic revision keeps `DELIVERY_ENABLED=false`, so live
  customers see nothing (verified 2026-08-22: the storefront hides every
  Delivery surface while the gate is false). Then, on the copy carrying the
  fix: a below-CA$50.00 basket carries the CA$5.00 fee and **can check
  out** — no minimum interception; a basket at and above CA$50.00 is free;
  the information panel lists "Free above $50.00" then "$5.00 below
  $50.00"; the basket label agrees with the stored no-minimum state.
  Update the owner's operating note (here and in
  `ADMIN_CONFIGURATION_GUIDE.md`) to name CA$50.00 as the first row.
- **C. Only after B: tax, read-only — DONE 2026-08-24, delivered as
  `QUEBEC_TAX_PLAN.md`; no setting was touched.** As specified, the
  investigation covered the Taxation
  configuration structure, whether the Delivery fee can be taxed as its own
  line, the official current GST and QST values (read from canada.ca and
  Revenu Québec directly), and the implementation and verification path.
  **No setting is changed.** The tax settings are shared: enabling them
  changes what real Pickup customers pay on the live storefront — the first
  change in this project that touches real payment amounts — so
  implementation needs its own approval and a time agreed with the owner.
  Deliverable: a written plan.
- **D. Only after C: language planning — DONE 2026-08-24, delivered as
  `LOCALIZATION_WORKSTREAM_PLAN.md`.** The consultation answers are
  recorded in `CLAUDE_HANDOFF.md` section 11; the plan covers the Q-001
  import route, the Q-002 switcher (found present in the current theme but
  not yet effective for `en_CA` — verify-and-fix, joined with Q-005), the
  mail templates, and the currency-format question. Plan only;
  implementation is approved separately.

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
| Delivery closed before 12:00 and after 21:00, every day | **PASS - both legs executed** | **Before-12:00 leg done 2026-08-29 11:37 EDT on `d3c-min-9a4c1bc8`, executed**: delivery selected with an in-area address on a now-open day (Saturday), the add was refused by the server with "outside our Delivery hours", basket empty, checkout CLOSED. The 2026-08-22 all-day-closed Saturday reading is kept. **After-21:00 leg done 2026-08-29 21:11-21:19 EDT, executed on both copies** (`d3c-fix-be6835a9` and `d3c-min-9a4c1bc8`), clock verified on Montreal time on each first: the restaurant's own address was accepted in-area on both (Delivery selected, CA$5.00 fee line, cart CA$5.00), and the add of SCOTCH EGG (Size Small) was **refused by the server on both** with the verbatim "Your selected order time is outside our Delivery hours". The cart stayed at CA$5.00 - the fee line alone, no item - and the dialog stayed open. The cut-off is enforced server-side, not merely displayed. See the evening-cut-off entry below |
| Delivery open during its hours | Passed | Executed. Friday 2026-08-21 reading kept as history (taken under the setting "None"). Sunday 2026-08-23, both copies: unfixed accepts on a closed day, fixed refuses — the weekday correction confirmed deployed. Monday 2026-08-24 14:1x, the positive leg: delivery adds accepted on the weekday-fixed copies during the 12:00-21:00 window |
| Address lookup works with one provider pinned | Passed | Executed: the home-page widget geocodes the typed address server-side and it works on both copies |
| Address autocomplete on the Google-pinned copies | **Failed** | Executed: the suggestion call is refused by the provider, and the raw technical error is shown to the customer. See below |
| An in-area address is accepted | Passed | Executed, one address |
| Delivery area: outside, and exactly on the boundary | Outside passed; the exact boundary cannot be reached this way; coordinate-level re-proof **Deferred** | **Outside**, executed 2026-08-29: a downtown address well outside the polygon was refused — the storefront kept the customer on the home page with "We do not have any local restaurant near you.", offered no delivery, and leaked nothing technical. **Exactly on the boundary is not reachable through the storefront**, and saying otherwise would be inventing evidence: the widget takes an address string and the server geocodes it, and no street address can be made to land exactly on a polygon edge. The inclusive-boundary behaviour was verified at D3B by a runtime self-check on an exact first vertex, which is the record that stands. Re-proving it needs a coordinate-level probe — driving the area search with the vertex coordinates from a disposable job — which is **Deferred (2026-08-29, on the user's decision)**: the property is already covered by the D3B runtime self-check against an exact vertex, that record stands, and the storefront path cannot re-prove it. Not worth opening a write operation of its own; revisit before launch if there is capacity |
| Delivery fee of CA$5.00 below the free threshold | Passed | Executed 2026-08-24 against real baskets on `d3c-min-9a4c1bc8`: CA$23.99 and CA$48.00 both carried the CA$5.00 fee in the served totals |
| Free delivery at the decision threshold (CA$50.00), at the edges | Passed | Executed 2026-08-24 at the new values: below (48.00) pays 5.00; exactly 50.00 free; above (62.00) free; total equals subtotal on the free legs. The at-or-above boundary is inclusive, as recorded for D3B |
| Delivery minimum shown and enforced as the stored value | Passed, with one leg on tests | Executed 2026-08-24, the contrast test: under stored 20.00 the unfixed copy showed and enforced a computed 80.00 (label, disabled button, served `/checkout` refusal); the copy carrying PR #101 showed 20.00 and let the same CA$23.99 basket through to checkout. After the batch write the stored minimum is 0 and no label renders, which is correct. Refusal below a stored positive minimum was never exercised deployed (the minimum moved to 0 right after the reading); it stands on `DeliveryOrderTypeMinimumTest` (fails at 19, passes at 20) and is recorded in the section above |
| Money reads and compares correctly in Canadian French | Comparison passed; display recorded as wrong for the locale, not fixed here | Executed 2026-08-29 on `d3c-min-9a4c1bc8`. **Comparison is correct**: the fee applies below CA$50.00 and not at or above it, verified against real baskets at 23.99, 48.00, 50.00 and 62.00 (2026-08-24). **Display is not Canadian French**: every amount renders as `$12.00` — symbol first, period decimal — on a page whose `<html lang>` is `fr_CA`, where the local convention is `12,00 $`. Cause already traced in `CLAUDE_HANDOFF.md` section 10: the currency formatter is null, so `number_format()` uses the separators on the currency record and the locale-aware formatter never runs. Recorded only; the fix belongs to the localization workstream (`LOCALIZATION_WORKSTREAM_PLAN.md` W4), which considers it together with the tax lines at checkout |
| Unrecognised, incomplete, out-of-area addresses; changing an address | Out-of-area and unrecognised passed; incomplete open; address change not started | Executed 2026-08-29 on `d3c-min-9a4c1bc8`, each with the submitted value verified in the component before submitting (see the debounce note below). **Out-of-area** (a downtown address, well outside the polygon): delivery refused, the page stayed put with "We do not have any local restaurant near you.", no URL, provider text, geometry or internal id in the message, and the pickup entry point remained on the page. **Unrecognised** ("zzqx nonexistent street 99999 Nowhereville"): the generic "We couldn't locate the entered address or postcode, please enter a valid address or postcode.", no technical detail. Both messages are English on a French page — Q-001, not a new defect. **Incomplete** (a street name with no number): the widget **accepted** it and opened a delivery basket, and the area even matched — the basket showed the CA$5.00 fee. The requirement bites where the decision said it would: at 12:05 EDT the checkout page rendered **"No delivery address provided"**, so no delivery order can be placed on that address. Recorded as the block, with one honest limit: the final refusal on pressing Confirm was **not** exercised, because that attempts to place an order, and no real order may be created without approval. One inconsistency worth carrying: the basket priced a delivery the checkout considers addressless. **Changing an address**, executed 2026-08-29 12:11 EDT: from a working in-area delivery basket (item, CA$5.00 fee, CA$7.00 total), a change to the out-of-area address was refused at the widget with the same generic message — and the existing session **survived intact**: still Delivery, item still there, fee and total unchanged. A rejected change does not strand the customer or silently reprice the basket. An in-area to in-area change was not run separately: with one zone and a flat fee it would produce the same totals and prove nothing the refusal has not already shown about re-geocoding and re-matching |
| Switching between pickup and delivery, both ways | Passed | Executed 2026-08-29 12:02-12:04 EDT on `d3c-min-9a4c1bc8`, one basket carried through both directions. Pickup with one item: subtotal CA$2.00, **no delivery line**, total CA$2.00. Switched to Delivery through the order-type dialog: the item survived, the CA$5.00 fee appeared, total CA$7.00 — the arithmetic of the applied parameters, with no minimum interception. Switched back to Cueillette: the item survived, the **fee line disappeared**, total back to CA$2.00. The dialog offers only "Dès que possible": there is exactly one `isAsap` radio and the date and time selects are present in the DOM but not visible, which is the ASAP-only setting rendering as recorded, and leaves Q-010 masked rather than fixed |
| Old sessions cleared, totals correct, resistant to tampering, API rules enforced | **PASS** - totals, tampering, API and stale sessions all executed | Executed 2026-08-29 on `d3c-min-9a4c1bc8`. **Totals**: verified against real baskets in both fulfilment directions and at the threshold edges — CA$2.00 pickup with no fee line, CA$2.00 + CA$5.00 = CA$7.00 on delivery, and the 23.99 / 48.00 / 50.00 / 62.00 series of 2026-08-24. **Tampering**: the client holds no money at all — the cart component's state carries `tipAmount`, `isCustomTip`, `couponCode` and display flags, and no subtotal, fee or total, all of which the server computes per render. A component state altered by hand and posted through the page's own Livewire endpoint was **refused with HTTP 419**, and the control proves the mechanism: the identical request with the state untouched returned 200 and a valid response, so the refusal was the integrity check and not a CSRF failure. The address does not dictate the area either — the server geocodes and matches it, which is what refused the out-of-area address above. **API**: every request to `/api/orders` and `/api/menus` without a token answered 401, including a POST carrying a client-supplied `order_total`, so no unauthenticated write path exists and nothing was created. The deeper policy — client `order_totals` and `order_menus` stripped, Delivery writes gated — lives in `App\Delivery\DeliveryApiWriteGuard` and its tests, which now run in CI; an authenticated end-to-end API check is **not** done, because it needs a token this session does not hold and would create real data. **Stale sessions**: **PASS, executed 2026-08-29 14:17-14:37 EDT**, closed as a by-product of the owner's-switch checks rather than needing its own write. With a CA$7.00 delivery basket held open, Delivery was switched off through the admin: the session normalised to pickup cleanly - items kept, delivery fee line gone, total CA$2.00, checkout still available, no error, order-type badge Delivery to Cueillette. This was the leg that had been blocked on the shared-settings write |
| Mobile at 390px: no sideways scrolling, no broken images | Passed | Executed |
| Browser console clean | Passed | Executed: no messages |
| Keyboard: focus is visible, no tab-order traps | Passed | Executed |
| Keyboard: an item can be added to the basket | Passed | Executed by hand: the user, on a real keyboard. Tooling cannot deliver this keypress; see below |
| Every control announces what it is | **Failed** | Executed: the add buttons have no name at all |
| Nothing else broken: pickup, birthday, reservations, media, health | Passed | Executed: ten pages, all served |
| Nothing else broken: admin | **PASS - executed 2026-08-29, 10 of 10.** Full results and the five step-10 observations are in "Admin regression, executed 2026-08-29" below | The agent has no admin credentials and does not ask for any. The owner signs in and works the list below, pasting back what each step actually showed; the agent judges and records. Steps, each on the copy `d3c-min-9a4c1bc8` unless the owner prefers another: **(1) Sign-in** — the admin loads, sign-in succeeds, no error banner. **(2) Dashboard** — it renders with no red error box; note any "something went wrong" panel. **(3) Orders list** — opens. Write down the **row count** and the **highest order number**. Confirm every row is a draft: status 0 (never placed), with name, e-mail and telephone empty. Open the newest one and confirm it too is a draft with no customer information, and that the detail page renders with its totals. Copy the row count and the highest order number back verbatim. (No fixed figure is given on purpose: loading /checkout creates a draft order row, so any number written here would already be stale by the time the owner reaches this step — including from their own walk through this list. What they report becomes the current baseline for the pre-launch draft-row cleanup.) **(4) Reservations list** — opens (expected empty). **(5) Locations → the location → Delivery settings** — the minimum order amount reads **0.00** and the fee rules read **free above 50.00** then **5.00 below 50.00**, in that row order. **(6) Same screen, opening hours** — Delivery shows every day 12:00-21:00 and Collection every day 12:00-22:00. **(7) Menus list** — opens and shows the 12 items. **(8) Birthday packages and add-ons** — both lists open. **(9) System → Settings → the general/localisation screen** — the timezone field reads **America/Toronto**. **(10) Anywhere** — report any PHP error, stack trace, blank panel, or missing translation string that looks like a defect rather than untranslated text. For each step: what was shown, verbatim where it is a number or a message |
| No real orders, customers, reservations, payments, or emails created | Holding | Nothing real created so far |
| Test data cleaned up afterwards | Partly done | The two synthetic mail-test orders, #6 and #7, were deleted on 2026-08-23 with their child rows, read back before and after (total orders 6 to 4; #2 to #5 untouched). The cleanup job resource was deleted on 2026-08-23 on confirmation and read back absent. Still to remove, each on explicit confirmation: the test copies of the site - **except `d3c-a2ee559c`, which must not be deleted before the deployment rehearsal; see "The main-traffic twin" below** - and the **nine** unplaced draft order rows, highest **#12** (#2-#5, #8-#12; 6 and 7 were deleted 2026-08-23). Baseline re-read from the admin on 2026-08-29 and it supersedes the eight-row figure: every row status Incomplete with Customer Name empty. The count rises whenever /checkout is loaded, so it is re-read at cleanup time rather than trusted from here |

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

### The "transient" first address lookup was the driver, not the provider

Recorded 2026-08-29, and it corrects an earlier reading. The home-page
address field is bound with `wire:model.live.debounce.500ms`, so a script
that sets the field and submits in the same breath sends the **previous**
query — usually empty — and the server answers "We couldn't locate the
entered address or postcode", exactly the message a real failure gives.
Retrying then "works", because by the second attempt the value has
synchronised.

That is what the 2026-08-24 note called a transient provider failure
that succeeded on retry. There is no evidence a provider call failed at
all. Every address reading from now on verifies the component's bound
value before submitting, and the readings recorded for 2026-08-29 were
taken that way.

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

**What the contrast reading still settles (settled 2026-08-24, see the resolved section above):** only that the deployed copy behaves as the
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

**Order of operations for the contrast test, fixed by the user:** first the CA$20.00 to
CA$80.00 basket on the deployed copy as it stands, to confirm the deployed
state and record that the checkout was in fact blocked before the fix; only
then deploy the fix (PR #101, already merged) to a 0% copy and repeat the
basket as the acceptance of the fix. Reversing the order loses the
before-and-after contrast. On 2026-08-23 the user extended the same ordering
past the fix: the test runs under the stored 20/80 values, and only after it
does the approved parameter batch (no minimum, threshold CA$50.00) get
written — see the decision-batch section above.

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
rule that matches a basket. "Free delivery from CA$50.00" must stay as the
first row (CA$50.00 since the 2026-08-24 batch write; CA$80.00 before it).
Dragging the rows changes what customers are charged, silently, with no
warning. After any change, open the storefront menu page's "More info"
panel and check that "Free above $50.00" is listed first.

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

**All four PASS, executed 2026-08-29 14:17-14:37 EDT** on `d3c-min-9a4c1bc8`,
with the switch thrown through the admin screen - the owner's real path - and
not by writing the row directly. Baseline: Offer Delivery Enabled, Offer Pick-up
Enabled, a CA$7.00 in-area delivery basket (SCOTCH EGG Small CA$2.00 + delivery
CA$5.00), checkout available, "We are open - Delivery dans 25 min".

| Check | Status |
| --- | --- |
| How long after switching off does a customer actually see it? | **PASS. No cache delay: the setting is read per request.** Switching back on was sampled every 2 seconds - last "off" 14:30:20, first "on" 14:30:22 - so the change lands inside one 2-second window. The off direction was seen in effect within 100 seconds but was not sampled tightly. Reported per instance, as required: this is the instance that was serving `d3c-min` at the time, not a service-wide figure |
| A customer already part-way through a delivery order when it is switched off | **PASS.** The CA$7.00 delivery basket degraded to pickup by itself: items kept, delivery fee line gone, total CA$2.00, checkout button still available, no error, order-type badge Delivery to Cueillette |
| Does delivery disappear entirely, or is the customer only stopped at checkout? | **It disappears entirely.** The home page Livraison block is gone and the heading changes from "Commandez pour cueillette, verifiez la livraison ou reservez..." to "Commandez pour cueillette ou reservez..." |
| After switching back on, does everything return, or is a cache clear or redeploy needed? | **PASS, nothing else needed.** Saving the switch brings the home-page block and the three-part heading back on the next request |

**Proof that Pickup was never touched**, three independent ways: (a) Offer
Pick-up read Enabled throughout and still Enabled on the closing read-back; (b)
the Delivery section's form carries only its own ten delivery fields
(`is_enabled`, `add_lead_time`, `time_interval`, `lead_time`, `time_restriction`,
`cancellation_timeout`, `min_order_amount`, `future_orders.*`) and no
`collection` field; (c) each collapsible section loads only its own form, so two
never co-exist in the DOM. This is the measured counterpart of the source
reading of `SettingsEditor::onSaveRecord()`, which resolves
`LocationSettings::instance($location, $definition->code)` and saves that one
`item` row.

**Closing read-back** through job `qa-toggle-20260829` (execution `-9s4bv`):
location 2 `delivery.is_enabled = 1` and `collection.is_enabled = 1`, agreeing
with the admin read-back. `ti_working_hours` re-read in the same execution and
unchanged - 21 rows, all enabled, one window per type.

**FP-1 on the main-traffic revision**, before the first action and after the
last: `2127efd6d63de53e6d9fbc5388f9db3fee72d0575eec25a09b9f99e9ad8565d3` both
times, identical. The live path was not touched. The live storefront home page
was also read before and after the write window and was word-for-word the same
pickup-only page, and the order table still holds 9 rows with highest #12 - no
new row, since `/checkout` was never loaded.

**Method note, recorded rather than glossed:** adding the item and some of the
switch clicks were driven by in-page script (`.click()`), because the add
buttons carry no accessible name and the switch is a style-covered checkbox, so
coordinate clicking is unreliable. Every verdict above rests on
server-rendered output - basket totals, the home-page block, the heading text -
which is independent of how the click was emitted.

## Timed checks and their windows

Opening hours are stored in the shared database, so they are never edited for a
test. Each check waits for its real window.

| Check | Window |
| --- | --- |
| Delivery appears open on Sunday, confirming the cause | Done 2026-08-23 12:26-12:28, executed on both copies: unfixed accepted, fixed refused |
| Friday reverse reading: fixed copy open, unfixed copy closed | Done 2026-08-28 16:58-17:00 EDT by the user's Cowork session (WebFetch, pinned hostnames, relayed): fix "Delivery dans 25 minutes", unfixed "Delivery is CLOSED" — mirror of Sunday; both directions closed |
| Saturday delivery open under the new every-day hours | Saturday 2026-08-29, 12:00-21:00, on `d3c-min-9a4c1bc8`: delivery offered and an add accepted (before the write, Saturday was closed on the fixed copies) |
| Delivery closed after 21:00 under the new hours | **Done 2026-08-29 21:11-21:19 EDT, executed on both copies**: display read "Delivery is CLOSED" with "Cueillette dans 25 minutes" on both, and the server refused the delivery add with "Your selected order time is outside our Delivery hours". Pickup stayed open, which also closes the "Pickup still open after delivery has stopped" row below |
| Delivery refused on a closed day | Done 2026-08-22 afternoon, executed on both copies: an order was attempted and refused |
| Delivery minimum: a CA$20.00 to CA$80.00 basket finds the checkout button disabled | Done 2026-08-24 14:1x, executed on `d3c-fix-be6835a9` before the fix deployed: CA$23.99 basket, label $80.00, button disabled, `/checkout` refused |
| Delivery minimum fix: the same basket checks out at the stored CA$20.00 minimum and the label reads CA$20.00 | Done 2026-08-24, executed on `d3c-min-9a4c1bc8`: label $20.00, checkout page reached, nothing submitted |
| The 2026-08-23 parameter batch: below-CA$50.00 basket pays CA$5.00 and checks out, at/above CA$50.00 free, panel and label agree | Done 2026-08-24, executed after the write: 23.99 and 48.00 pay 5.00 and check out; 50.00 exactly and 62.00 free; panel lists Free above $50.00 first; no minimum label renders |
| Delivery stops taking orders at 21:00 | **Done 2026-08-29 21:11-21:19 EDT, executed on both copies**: refused server-side, verbatim "Your selected order time is outside our Delivery hours" |
| Pickup still open after delivery has stopped | **Done 2026-08-29 21:11-21:19 EDT, executed on both copies**: with delivery refusing, the info panel read "Cueillette dans 25 minutes" and `d3c-fix-be6835a9` in pickup mode read "We are open" - the deliberate 21:00-22:00 gap behaving as designed |
| Pickup stops at 22:00 | Done 2026-08-21 22:31, executed: an order was attempted and refused |
| Can a basket be filled while closed | Done 2026-08-21 22:31. See below |

Delivery's own timing checks cannot be taken until it can be ordered at all;
until then, closed cannot be told apart from the defect.

The one-hour gap between delivery stopping at 21:00 and the shop closing at
22:00 is deliberate. It comes from the two schedules differing and should not be
recorded as a missing cut-off.

## Evening delivery cut-off, executed 2026-08-29 21:11-21:19 EDT

This section originally opened by tying the reading to "a reading of
2026-08-28 at 21:10 EDT in which `d3c-fix-be6835a9` ... showed Delivery open".
**That premise was backwards** - a paraphrase drift corrected in "The
2026-08-28 21:10 symptom, corrected" below. The original record reads, verbatim:
"showing the whole site closed and 'Opening sam. 12:00 pm' at 21:10 EDT Friday,
when pickup should run to 22:00" - closed when it should have been open, on
`d3c-min-9a4c1bc8`, and already explained and closed by the timezone fail-open
(that instance was on UTC, where it was Saturday 01:10). No open display anomaly
existed for this reading to reproduce or answer.

What the reading does settle, on its own merits as the after-21:00 acceptance
leg: whether the server refuses a delivery order after closing, or accepts it.

**Answer: the cut-off is enforced by the server, on both copies.**

Taken in a real browser on Saturday 2026-08-29 between 21:11 and 21:19 EDT,
inside the one-hour window in which the shop is still open for pickup but
delivery has stopped. Hostnames were verified on each page.

**Clock check first, both copies passed.** `d3c-fix-be6835a9` in pickup mode
read "We are open" with "Cueillette · dans 25 min"; its info panel read
"Delivery is CLOSED", "Cueillette dans 25 minutes", Last Order Time
"sam. 29 10:00 pm". `d3c-min-9a4c1bc8` read "Delivery is CLOSED",
"Cueillette dans 25 minutes", Last Order Time "dim. 30 09:00 pm" - the next
delivery window, correctly rolled to Sunday. Neither had drifted to UTC: an
instance on UTC would have been at 01:1x on the 30th and would have shown
everything closed with the next opening at noon. Both readings therefore count
as evidence rather than VOID. The stored hours rendered identically on both:
Delivery 12:00 pm-09:00 pm on all seven days, Opening and Cueillette
12:00 pm-10:00 pm on all seven days.

**Executed, not merely read.** On each copy the delivery address was set through
the home-page widget to the restaurant's own address, 8407 Boul. Gouin E,
Montreal, H1E 2P6, QC, Canada. The field is `wire:model.live.debounce.500ms`, so
the value was set, an `input` event dispatched, and `searchQuery` confirmed in
the component snapshot before `#location-search` was submitted with
`requestSubmit()`. Both copies accepted the address as in-area: the order type
became Delivery, the CA$5.00 fee line appeared, and the cart read CA$5.00 with
no item. The header then read CLOSED on both, which is already the correct
display for delivery at this hour.

**The server's verbatim answer, identical on both copies:**

> Your selected order time is outside our Delivery hours

The attempt was the add of SCOTCH EGG (Size Small, CA$2.00), submitted through
the dialog's own form. On both copies the add was **refused**, the cart stayed
at CA$5.00 - the delivery fee line alone, no item - and the dialog stayed open
carrying the message. `/checkout` was not visited: it is only called for when
the basket is accepted, and loading it creates a draft order row. No order,
customer or payment was created.

**What this settles.** The server leg is now positively established on both
copies: after 21:00 a delivery add is refused. The after-21:00 acceptance leg is
closed on that evidence.

This section originally went on to say the 2026-08-28 anomaly was "not
reproduced, not explained, and not closed", and to offer a WebFetch-cache
hypothesis with a Friday browser re-take to test it. **Both are withdrawn with
the reversed premise they stood on** (see "The 2026-08-28 21:10 symptom,
corrected" below): the real 2026-08-28 21:10 symptom was a page showing
*closed* when it should have been open, explained and closed that same week by
the timezone fail-open. There was never an open "showed delivery open" display
anomaly for a cached response to explain, so nothing here waits on a Friday
re-take and no such reading is scheduled.

## Test copies of the site currently deployed

All receive 0% of visitors.

| Name | Purpose | Usable for multi-step flows |
| --- | --- | --- |
| `d3c-min-9a4c1bc8` | `4.x` at `9a4c1bc8`: weekday fix plus the minimum override (PR #101), pinned to Google, URLs pinned. The current copy for delivery flows | Yes |
| `d3c-fix-be6835a9` | The weekday fix, pinned to Google. Used for the Sunday reading and the 2026-08-24 contrast before-leg; superseded by `d3c-min` for new work | Yes |
| `d3c-g2-1f8f0c75` | Unfixed comparison, pinned to Google. Use for the Sunday reading | Yes |
| `d3c-pu2-1f8f0c75` | Provider-unavailable testing, no address provider | Yes |
| `d3c-g-1f8f0c75` | Superseded by `g2` | No |
| `d3c-pu-1f8f0c75` | Superseded by `pu2` | No |
| `d3c-25f9813b` | Earlier check of log redaction | No |
| `d3c-a2ee559c` | First D3C copy. **Also the main-traffic twin: bit-identical image, one environment variable apart. Hold for the deployment rehearsal - do not delete.** See "The main-traffic twin" | No |
| `d3c-e9a4f7ca` | Failed to start. Delete first when cleaning up | No |
| `mail-3a603e53` | Mail test: pickup-only, real SMTP transport, every message redirected to the test inbox. Not a D3C copy; listed so that cleanup sees it | Yes |

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
| Apply the new delivery hours, every day 12:00-21:00 | Agent, on the user's approval | **Done 2026-08-28**, on the user's explicit approval after the Friday reverse reading. Both stores written with before/after read-back in the tracker: the `working_hours` delivery rows (weekday 5 and 6 status 0 to 1; times already 12:00-21:00) and the `hours` LocationSettings JSON (`delivery.days` to all seven), which is what the info panel renders. FP-1 identical around the write. Way back: restore status 0 on weekday 5 and 6 and days `[1..5]` in the JSON. Saturday-open and post-21:00-closed readings wait for their windows |
| Static check that stops an exception being written into a log context | Agent | Deferred |
| Delete disposable Job `qa-params-20260824` (its two write executions and one read-back are recorded in the tracker) | Agent, on the user's explicit confirmation naming it | Done 2026-08-24: confirmed by name, described before, deleted, read back absent; the region holds no jobs |

### Address autocomplete does not work today

The Places API is not enabled on the project, so address suggestions never
appear. Every search shows "No suggestions found", and a customer must type a
complete, correctly formatted address for delivery to be offered at all, which
is hardest on a phone. Enabling Places is a business decision: its pricing works
differently from address lookup and needs costing first.

## Admin regression, executed 2026-08-29

PASS, 10 of 10, on copy `d3c-min-9a4c1bc8`. The user signed in themselves in the
browser panel - no password passed through any agent - and the ten steps were
then driven read-only: no form submitted, nothing saved. The agent holds no
admin credentials and asked for none.

| Step | Result |
| --- | --- |
| 1 Sign-in | PASS, no error banner |
| 2 Dashboard | PASS, renders with no red error box. Total Cash Payments $0.00, Total Lost Sales $71.97, Total Sales $0.00 |
| 3 Orders | **9 rows, highest #12** (12/11/10/9/8/5/4/3/2; 6 and 7 absent - the mail-test orders deleted 2026-08-23). Every row Customer Name empty, status Incomplete. #12 opened: status "--", Customer empty, totals render correctly (SCOTCH EGG $2.00 + Delivery $5.00 = $7.00), Date Added 29 aout 12:05, IP 169.254.169.126, User Agent carrying Claude/1.40609.0 |
| 4 Reservations | PASS, 0 rows |
| 5 Delivery settings | PASS. Minimum $0.00; rule order (1) $0.00 above $50.00, (2) $5.00 below $50.00 - free-above first, correct. Also read: Offer Delivery = Enabled, time slot 15, preparation 25, ASAP only, cancellation timeout 0 |
| 6 Opening hours | PASS. All three tables in Daily mode: Opening seven days 12:00-22:00, Delivery seven days 12:00-21:00, Cueillette seven days 12:00-22:00 |
| 7 Menus | PASS, 12 items, all Enabled |
| 8 Birthday packages and add-ons | PASS, both lists open, 0 rows each |
| 9 Timezone field | PASS, America/Toronto (UTC -04:00) |
| 10 Anything else | Five observations, below |

### Step 10 observations

- **(a) The shared geocoder setting is Chain (Recommended), not Google.** Its own
  description says Chain runs Google and OpenStreetMap together and takes the
  first valid result, while `CLAUDE_HANDOFF.md` section 10 records that Nominatim
  must not be used in production. Followed up under "Geocoder on main traffic".
- **(b)** All 12 menu items have an empty Category column.
- **(c)** Birthday packages and add-ons are both empty, so the birthday ordering
  flow has no data to run against today.
- **(d)** The admin mixes French and English on one screen: an order detail shows
  Type de commande / Sous-total / Total de la commande beside Status / Assignee /
  Payment Method / Date Added, and list headers are English under the French
  title Reservations. Belongs to Q-001, and is the subject of question 4 of the
  French-language consultation.
- **(e)** The only payment channel offered is Cash On Delivery.

Non-defect note, recorded so it is not rediscovered as one: guessing a wrong
settings sub-path (`general-pickup` where the correct one is
`general-collection`) returns a "Server Error" page rather than a 404. It is
caused by the bad URL, and is not counted as a defect.

## Row storage cross-check of the working hours, 2026-08-29

Read directly from `ti_working_hours` through disposable Job
`qa-toggle-20260829` (read-only execution `-9gmzw`), because the admin renders
the JSON display store and the question was whether the row store agrees.

| type | rows | rows with status=0 | distinct time windows | window |
| --- | --- | --- | --- | --- |
| collection | 7 | **0** | **1** | 12:00:00-22:00:00 |
| delivery | 7 | **0** | **1** | 12:00:00-21:00:00 |
| opening | 7 | **0** | **1** | 12:00:00-22:00:00 |

All 21 rows carry status 1, and within each type every one of the seven days
carries identical opening and closing times. The row store agrees exactly with
what the admin showed. No row is disabled and no two days differ.

**Consequence for the weekday correction.** Shifting the weekday mapping by one
day permutes a set of seven identical elements, so no customer-visible behaviour
can change under this configuration. The earlier rating of
`WeekdayScheduleCorrection` as the largest customer-visible risk of the
deployment does not survive this reading and is withdrawn; see
`DEPLOYMENT_IMPACT_TIMEZONE_FIX.md`. The rating would return the moment any day
is disabled or given different hours - which is a live possibility, since the
holiday plan of decision 6 is precisely to disable a day.

## Toggle baseline, 2026-08-29

Cross-verified for the owner's-switch checks. Admin showed Offer Delivery =
Enabled and Offer Pick-up = Enabled; the same job read `ti_location_settings`
directly and returned, for location 2, `delivery.is_enabled = 1` and
`collection.is_enabled = 1`. The two sources agree, so the baseline is trusted.

## Which geocoder answered on the copies - resolved 2026-08-29

Raised by a storefront reading on `d3c-min-9a4c1bc8` in which the local-search
component reported `geocoder: "chain"` against a record saying that copy is
pinned to Google. **Resolved by source reading. The observation is fully
explained and carries no information about the pin.**

`Igniter\Orange\Livewire\Concerns\SearchesNearby` declares `public string
$geocoder` and fills it in `mountSearchesNearby()` (line 76) with

    $this->geocoder = setting('default_geocoder', 'nominatim');

That is the **stored setting, read directly**. It never consults
`config('igniter-geocoder.default')`, which is the key
`GeocoderChainOverride` writes, and it never touches a resolved geocoder. The
property therefore reads `chain` on every copy whether the pin works or not: it
is structurally incapable of reflecting the override. The value was read from the
component's server-side snapshot, where `placesSuggestions` was `[]` in the same
reading, confirming this was not the `updateDeliveryLocationMap` event, whose
`geocoder` field is the provider that actually answered.

The contradiction is dissolved rather than adjudicated: the two things were never
measuring the same quantity.

Worth knowing as a side effect: the same stored value chooses the front-end map
library (line 79 loads Leaflet when the stored setting is `nominatim`, Google
Maps JS otherwise). So the map assets follow the stored setting rather than the
effective geocoder. Harmless while the stored value is `chain`, but it is a
coupling to remember.

### What each copy actually ran

**Corrected 2026-08-29 after a coverage check.** The first version of this table
listed seven entries, omitted three real revisions, and contained one row,
`d3c-1f8f0c75`, **for a revision that does not exist** - that string is an
Artifact Registry image tag, shared by the four `1f8f0c75` copies, and it was
mistaken for a revision name. Its "no variables set" reading came from a
`describe` whose error output had been suppressed, so a failure printed as a
finding. Re-run against the service's actual revision list, with errors shown.
Two of the omitted copies turn out **not** to be Chain copies at all.

The verdict rule is simpler than the first version implied: **a revision with no
geocoder variables runs the stored `chain` setting, whether or not the override
code is in its image**, because `GeocoderChainOverride` returns early when the
environment says nothing. Code presence only explains, it does not decide.

All nine D3C copies plus the mail copy:

| Revision | geocoder variables | What it ran | Serving state |
| --- | --- | --- | --- |
| `d3c-a2ee559c` | none | **Chain** | Ready. Image digest is the same as main traffic's, i.e. commit `31821289` |
| `d3c-25f9813b` | none | **Chain** | Ready |
| `d3c-e9a4f7ca` | none | Chain, but moot | **Ready=False, `HealthCheckContainerError`** - it never served a request |
| `d3c-g-1f8f0c75` | `google` / `google` | **Pinned to Google** | Ready. Same image and same pin as `g2`; being "superseded" did not make it a Chain copy |
| `d3c-g2-1f8f0c75` | `google` / `google` | **Pinned to Google** | Ready |
| `d3c-pu-1f8f0c75` | `chain` / **empty** | **Chain over an empty provider list** | Ready. Same construction as `pu2` |
| `d3c-pu2-1f8f0c75` | `chain` / **empty** | **Chain over an empty provider list** | Ready |
| `d3c-fix-be6835a9` | `google` / `google` | **Pinned to Google** | Ready |
| `d3c-min-9a4c1bc8` | `google` / `google` | **Pinned to Google** | Ready |
| `mail-3a603e53` | none | **Chain** | Ready. Mail-transport testing; no address work |

### Consequences for recorded evidence

- **Readings on `d3c-e9a4f7ca`, `d3c-25f9813b` and `d3c-1f8f0c75` went through
  Chain**, and Nominatim could have supplied any coordinate. This needs no probe
  and is not affected by the resolution above: it follows from the image and the
  environment alone. Any area-sensitive conclusion resting on those copies -
  outside-area, boundary, unrecognised or incomplete addresses - should be read
  as Chain-sourced.
- **Readings on the three Google-pinned copies** are now unopposed: the only
  evidence that had been against the pin turned out to measure something else.
  Confirmation by runtime probe remains **optional**, and if run should cover
  each pinned copy separately rather than letting one stand for the others.
- **`d3c-pu2-1f8f0c75` has its own status** and should not be folded into either
  group.
- **Coordinate-independent findings are untouched**: the owner's-switch checks,
  stale-session normalisation, totals, tamper resistance, API policy and every
  schedule reading. None of them depends on which provider resolved an address.
- **Main traffic is not implicated** at all: Delivery is closed there, so no
  customer reaches the lookup. Its separate exposure is in
  `DEPLOYMENT_IMPACT_TIMEZONE_FIX.md` section 7.

### The probe was not run, and why that is a judgement about evidence

Decided 2026-08-29. **No recorded conclusion depends on metre-level coordinate
accuracy**, so even under H2 nothing in the register would be overturned:

- the out-of-area reading used a downtown address, far enough outside the polygon
  that tens of metres cannot reverse it;
- the unrecognised-address reading fails under either provider;
- the incomplete-address reading is really testing the checkout page's block, not
  a coordinate;
- the exact boundary was already declared unreachable through the storefront and
  is recorded as Deferred, covered by the D3B runtime self-check.

A second reason reinforces it, and it now rests on the corrected table above
rather than on a partial one: **no area-sensitive reading was ever taken on a
Chain-running copy.** The four Chain copies are `d3c-a2ee559c` (first D3C copy),
`d3c-25f9813b` (an earlier log-redaction check), `d3c-e9a4f7ca` (never started -
`HealthCheckContainerError`) and `mail-3a603e53` (mail transport). The copies
table marks the first three "usable for multi-step flows: No", and the fourth
did no address work. Every address reading in the register came from a
Google-pinned copy or from the deliberately provider-less `pu`/`pu2` pair. So the
Chain tier contains no evidence to re-qualify - which is now a statement about
all ten copies, not about the seven the first table happened to list.

**The two places where this stops being true, and both are live:**

1. **Any future test that genuinely hugs the delivery-area boundary.** That is
   exactly the case where Google and Nominatim disagreeing by tens of metres
   decides the answer, so such a test must first establish which provider
   resolved the coordinate. The deferred coordinate-level probe would inherit
   this requirement.
2. **Main traffic once Delivery is enabled.** That image contains no override
   code at all, so it runs the stored `chain` setting and reaches Nominatim -
   and it cannot be pinned by environment variable, because nothing there reads
   one. This is a launch-gate item, not a D3C item: see the Nominatim production
   gate in `CLAUDE_HANDOFF.md` section 10 and
   `DEPLOYMENT_IMPACT_TIMEZONE_FIX.md` section 7.

## The main-traffic twin: `d3c-a2ee559c`

Found 2026-08-29 while checking the geocoder configuration, and recorded here
because it is an asset rather than a leftover.

`d3c-a2ee559c` runs image digest
`sha256:72371b610a2dff66d29dcee09a2095c72c2f6bb0d932d33744db3444c3689102` - **the
same digest the main-traffic revision runs**, i.e. commit `31821289`. It is
Ready and serves 0% of traffic.

Its environment was compared key by key with the main-traffic revision. **Both
carry the same 30 variables with the same values, with exactly one exception:**

| Variable | main traffic `d2fix-31821289` | twin `d3c-a2ee559c` |
| --- | --- | --- |
| `DELIVERY_ENABLED` | `false` | **`true`** |

Everything else - including `APP_TIMEZONE`, `RUN_CONFIG_CACHE`, the absence of
any geocoder variable, and every secret reference - is identical.

### What it is good for

1. **The comparison baseline for the Option C deployment.** On deployment day the
   new image goes to a tagged revision, and reading it side by side against this
   twin separates "what this deployment changed" from "what the site already
   did". Without it the only available comparator is the live site itself.
2. **A reproduction site for the timezone fail-open.** The live image contains no
   fix, and this one is the same image, so the cold-start path can be studied
   without touching customer traffic.
3. **A preview of main traffic with Delivery switched on**, which is stronger
   than the first two and follows from the single difference above. This twin is
   what the live revision *becomes* when the launch flips `DELIVERY_ENABLED`. In
   particular it is the one place where the **Nominatim production gate** can be
   observed rather than argued: it sets no geocoder variable and its image
   predates `GeocoderChainOverride`, so it runs the stored Chain with Delivery
   open, exactly as main traffic would.

### The constraint this creates

**It shares the one database with the live site**, so it is fit for read-only
behavioural comparison and nothing else. No write taken on it is isolated.

**And it must not be deleted before the deployment rehearsal.** The test-copy
cleanup list and the deployment plan are coupled at this single point: cleaning
up on its own schedule destroys the deployment's only baseline, and planning a
deployment that assumes the baseline exists will fail if cleanup ran first.
Neither side may proceed past this copy without the other. Recorded in the
cleanup row above and in `DEPLOYMENT_IMPACT_TIMEZONE_FIX.md`.

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
provider key, and giving one copy different opening hours.

**The first of those has since been worked around, and the limit is narrower
than this section claimed.** The provider chain does not have to be varied in
the shared settings at all: `App\Delivery\GeocoderChainOverride` (PR #86) lets a
*revision* name its own driver and provider list, and an empty
`DELIVERY_GEOCODER_PROVIDERS` is honoured as a deliberately empty list rather
than as "unset". `d3c-pu2-1f8f0c75` is built exactly that way -
`DELIVERY_GEOCODER_DRIVER=chain` with an empty provider list - so it runs a chain
over no providers while every other copy and the live site keep the stored
configuration untouched. Confirmed by reading that revision's environment on
2026-08-29.

**This is the premise of the provider-unavailable acceptance rows, and it
holds.** Those five readings - order refused safely, nothing technical shown,
logs clean, pickup still offered and reachable, basket kept - were taken on a
copy that genuinely had no usable address provider, by construction rather than
by simulation, and without touching a shared setting.

The limit still stands for the second case, opening hours, which live in the
shared row store with no per-revision override. Anything resting on those can
only be verified by waiting for real conditions. A separate
database for isolated testing would remove the limit; that is a future
improvement, not part of this phase.

## Next action

The acceptance sweep is running on `d3c-min-9a4c1bc8` under the applied
no-minimum / CA$50.00 values, one row at a time, each to the executed
standard.

**Before each session of readings, verify the copy's clock.** The
timezone fails open to UTC per container instance
(`CLAUDE_HANDOFF.md` section 10), so a reading taken on a drifted
instance would be wrong about every window while looking normal. The
cheap check: outside 12:00-22:00 local the storefront must say closed
and name the next opening, and inside those hours it must offer the
order type. Both copies were verified on Montreal time at 11:25 EDT on
2026-08-29.

Delivery's after-21:00 cut-off was taken on 2026-08-29 21:11-21:19 EDT and
passed on both copies, on its own merits as an acceptance row - it was not
answering any open anomaly: the 2026-08-28 21:10 reading it was once tied to is
corrected and closed by the timezone defect (see "The 2026-08-28 21:10 symptom,
corrected" below). Still to take: out-of-area and boundary addresses; unrecognised,
incomplete and changed addresses; pickup/delivery transitions in both
directions; stale sessions, totals, spoof resistance and API policy; and
the admin regression. The owner's delivery on/off switch stays out of the
sweep: it writes a shared setting and needs approval first. The timezone
fix, the 板块二 work and the tax implementation each wait on their own
approvals.


## The 2026-08-28 21:10 symptom, corrected

This register described that reading as "the copy that reported delivery open at
21:10". **That is backwards, and the error was this project's paraphrase rather
than the original record.** Quoting `CHANGELOG_AI.md` verbatim:

> `d3c-min-9a4c1bc8` showing the whole site closed and "Opening sam. 12:00 pm"
> at 21:10 EDT Friday, when pickup should run to 22:00

The symptom was **closed when it should have been open** - not open when it
should have been closed. The same entry already resolves it: that copy's clock
was on UTC, where the moment was Saturday 01:10, and the reading "is consistent
with that copy's clock being UTC (Saturday 01:10) and is now explained".

Two consequences:

1. **The anomaly is closed and was closed on 2026-08-29 morning**, by the
   timezone fail-open explanation. It does not need an evening reading, and any
   note saying an evening reading "resolves the 2026-08-28 anomaly" should read
   instead: *unrelated to that anomaly, which the timezone defect already
   closed*. The after-21:00 cut-off leg is still worth taking on its own merits,
   as an acceptance row - it simply is not answering an open defect.
2. **A hypothesis built on the reversed premise is withdrawn and deliberately
   not recorded**: that the Friday evening reading might have been a cached
   pre-21:00 response rendered as "Delivery dans 25 minutes". The real symptom
   was a *closed* page, so there is nothing for that explanation to explain. No
   browser re-fetch is scheduled to test it.

Unaffected by this correction: the Friday **16:58-17:00** readings, which
genuinely did show "Delivery dans 25 minutes" inside opening hours and are the
discriminating pair for the weekday fix. Those stand.

## Undetermined: a delivery cart emptied across the 21:00 boundary

Observed once, on 2026-08-29, in the user's session: a delivery cart holding
CA$7.00 at 20:49 was **empty** when the page was reloaded at 21:05:31, with the
order type fallen back to Cueillette.

**Recorded as undetermined, not as a finding.** One observation, no controlled
repeat, and no causal claim.

What makes it worth chasing is that it does **not** match the behaviour measured
this afternoon when the owner's delivery switch was turned off: there the items
were kept and only the delivery fee line disappeared. Emptying the basket
entirely is a different outcome, so either the closing-time path differs from the
switch path or something else was in play.

Customer impact if it is real: someone who fills a basket at 20:55 and returns to
check out at 21:01 loses everything they chose.

**Next window to characterise it:** any day, load a delivery cart at 20:5x and
return to it at 21:0x. It needs an attended browser session; it cannot be a
scheduled unattended task.

A second reading taken the same evening from a clean session does not cover this
leg - that session had no basket - so it neither confirms nor contradicts it.
