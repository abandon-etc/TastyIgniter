# Birthday Reservation Rules Design

Date: 2026-07-10
Environment: Canada staging design only
Status: Design pending business and review approval

## 1. Scope

This document designs the first Birthday Booking rules stage. It does not
implement code, create migrations, create reservations, configure payments,
send mail or SMS, or change production. Render staging and the DigitalOcean
fallback remain available.

The confirmed opening rules are:

- Two fixed local-time slots: `12:00-16:00` and `16:00-20:00`.
- A booking date is allowed from two through sixty days ahead, inclusive.
- Same-day booking is not allowed.
- The booking represents the whole birthday venue slot, not a restaurant table.
- Guest count does not control availability or capacity.
- Each date and slot has capacity `1` by default.
- An active booking makes that date and slot unavailable.

## 2. Current TastyIgniter call chain

The application requires `tastyigniter/core`. The core package currently
declares `tastyigniter/ti-ext-reservation` and `tastyigniter/ti-theme-orange`
as dependencies.

The current public flow is:

1. The Orange theme reservation page mounts the Livewire `Booking` component.
2. `Booking::prepareDates()` derives the date window from location
   `min_advance_time`, `max_advance_time`, and the opening schedule.
3. `BookingManager::makeTimeSlots()` derives times from the opening schedule
   and the location `time_interval`.
4. `Booking::onSave()` validates date, guest count, and time, then moves to a
   timeslot step.
5. `Booking::onComplete()` validates contact fields and calls
   `EnsureUniqueProcess` with a MySQL `GET_LOCK` key based on date and time.
6. `BookingManager::saveReservation()` writes the generic `reservations` row,
   applies the default reservation status, and dispatches the reservation
   confirmed event.
7. The reservation observer can automatically allocate a dining table after
   the row is saved.

The current availability implementation is table-based. It counts available
dining tables, filters them by guest capacity, checks reservations joined to
`reservation_tables`, and treats status `0` and the configured canceled status
as not occupying a table. The current save path does not perform a Birthday
slot inventory check before writing the generic reservation row.

## 3. Configuration that can be reused

The following existing configuration is useful as reference only:

- The venue location and its timezone.
- The location opening schedule as a source for the venue's local date.
- Existing status/history concepts for the later admin workflow.
- Existing authenticated customer account data in a later workflow stage.
- Existing translation and extension registration mechanisms.

The following settings must not be reused as Birthday rules because their
semantics are table-oriented:

- `booking.time_interval`
- `booking.stay_time`
- `booking.min_guest_count`
- `booking.max_guest_count`
- `booking.limit_guests`
- `booking.auto_allocate_table`
- `dining_tables`, `reservation_tables`, and table capacity

The current staging location is configured for 15-minute intervals, 45-minute
stays, guest range 2-20, two-day minimum advance, thirty-day maximum advance,
automatic table assignment, and a 24/7 schedule. Those values are not the
Birthday Booking rules. The public generic reservation form also currently
renders many fine-grained times, which is one reason it must not be reused for
the fixed-slot flow.

## 4. Recommended architecture

Create a separate `Birthday Booking` extension or package owned by this
project. It should expose its own service, models, routes, admin screens and
translation keys. The first implementation should not modify vendor code,
TastyIgniter core, or the generic Orange reservation component.

Recommended boundaries:

- `BirthdaySlotDefinition`: the two enabled slot definitions, local start and
  end time, label key, default capacity, sort order, and enabled state.
- `BirthdaySlotOverride`: a date-specific open/closed override and optional
  non-sensitive reason. This supports future date or slot closures without
  changing the fixed slot definitions.
- `BirthdaySlotInventory`: a materialized date plus slot row with capacity and
  reserved count. Rows can be created lazily inside a transaction.
- `BirthdaySlotAvailability`: the only service allowed to calculate and claim
  a slot. It validates the venue-local date, slot definition, override, and
  inventory state.
- A later `BirthdayBooking` aggregate should own customer, payment, add-on,
  notification, hold, and cancellation state. It must not be forced into the
  generic table reservation model.

The implementation may be a dedicated extension repository installed through
Composer, or a clearly isolated project extension if the deployment strategy
supports tracked local extensions. The design stage does not choose a local
extension path that is currently ignored by `.gitignore` without a separate
packaging decision.

## 5. Slot and date model

Use stable slot codes, not display strings:

| Code | Local start | Local end | Default capacity |
| --- | --- | --- | ---: |
| `birthday_afternoon` | 12:00 | 16:00 | 1 |
| `birthday_evening` | 16:00 | 20:00 | 1 |

Store start and end as local venue `TIME` values. These slots do not cross
midnight, so no end-date adjustment is required. Display labels must use
translation keys and must not be hard-coded in a theme template.

Calculate the date window using the venue timezone, not the browser timezone:

- `earliest = venue_now.startOfDay().addDays(2)`
- `latest = venue_now.startOfDay().addDays(60)`
- both bounds are inclusive

The server must validate the date again on every availability request and on
the final booking command. The client calendar is only a convenience and is
not a security or availability boundary.

## 6. Capacity and concurrency

The database is the source of truth. Do not rely only on disabled buttons,
Livewire state, a cache, or a process-local lock.

For the default capacity of one:

1. Start a MySQL transaction.
2. Create the deterministic inventory row for `(location_id, booking_date,
   slot_definition_id)` if it does not exist. A unique key makes concurrent
   creation converge on one row.
3. Select the inventory row `FOR UPDATE`.
4. Re-check enabled state, date window, override state, and
   `reserved_count < capacity`.
5. Increment the reserved count only when the row is available.
6. Create or associate the booking in the same transaction.
7. Commit. On any failure, roll back the count and booking together.

An atomic conditional update may be used as an additional guard, but it must
not replace the unique key and transaction. A MySQL `GET_LOCK` can reduce
duplicate work but is not sufficient as the business invariant. The first
implementation must include a MySQL integration test with two concurrent
attempts and assert exactly one successful claim.

For future capacity greater than one, the same inventory row can use
`reserved_count < capacity`. The default and current acceptance behavior
remains exactly one successful booking per date and slot.

## 7. Occupancy states

The Birthday domain should distinguish availability from the generic
TastyIgniter reservation status list:

| State | Occupies slot? | Notes |
| --- | --- | --- |
| `draft` | No | Not a customer-visible booking. |
| `hold` | Yes until expiry | Must have explicit `expires_at`; not permanent. |
| `pending_payment` | No, unless backed by an active hold | Payment workflow belongs to the next stage. |
| `paid` | Yes | Payment evidence is required in the workflow stage. |
| `confirmed` | Yes | Final successful booking state. |
| `payment_failed` | No | Must release any hold/count. |
| `cancelled` | No | Releases inventory in a transaction. |
| `expired` | No | Releases an expired hold. |
| `refunded` | No | Releases inventory according to the later policy. |

PR 1 must not implement payment or final confirmation. It should define the
availability contract and test that only an explicit active hold or a future
confirmed booking occupies inventory. The final mapping of payment states is
PR 2 work.

## 8. Frontend and backend behavior

The Birthday flow should have its own page/component and show only the two
fixed slots for the selected date. It must not render the generic guest
selector, five-minute or fifteen-minute time list, dining table selector, or
automatic table assignment controls.

The service should return only enabled and available slots. The server must
repeat the same check when the user selects a slot and when the booking command
is executed. A stale browser must receive a clear unavailable-slot response.

The admin surface for PR 1 should allow staff to:

- enable or disable the two slot definitions;
- view default capacity, with `1` as the initial value;
- view date-specific inventory and availability;
- close or reopen a specific date and slot through an override;
- see the reason and audit timestamp for a date-specific closure.

It should not expose payment, cancellation/refund, add-on, SMS, or production
controls in PR 1. No test reservation should be created during this design
stage.

## 9. Migration and rollback

PR 1 should use additive migrations owned by the Birthday extension. The
minimum schema is:

- `birthday_slot_definitions`: stable code, local start/end time, capacity,
  enabled state, sort order, timestamps;
- `birthday_slot_overrides`: definition, local date, open/closed state, reason,
  timestamps, unique `(definition_id, booking_date)`;
- `birthday_slot_inventory`: definition, location, local date, capacity,
  reserved count, timestamps, unique `(location_id, definition_id,
  booking_date)`.

The later booking aggregate can add its own table and reference inventory. PR 1
must not alter the core `reservations` table or add a vendor model override.

The migration `down` path must drop only the new Birthday tables in reverse
dependency order. The feature should be disabled by default until the service
and tests are deployed to Canada staging. Rollback is to disable the feature,
stop routing to the Birthday page, and revert the extension/image; dropping
new test tables is a separate confirmed database operation. No production
migration is permitted.

## 10. Testing plan

PR 1 must include tests for:

- exactly the two fixed slot definitions;
- inclusive date window from plus two through plus sixty venue-local days;
- rejection of same-day, too-early, too-late, invalid, and disabled dates;
- rejection of unknown or disabled slot codes;
- no guest-count or table-capacity gating;
- date-specific close/reopen behavior;
- capacity one availability before and after a claim;
- cancellation/expiration release at the service boundary;
- timezone and daylight-saving boundary cases;
- two concurrent claims resulting in exactly one success;
- no writes to the generic TastyIgniter reservation table in PR 1;
- mobile and English UI smoke checks for the two-slot selector.

The staging acceptance must be limited to empty/test data and must not send
real email/SMS or process real payment.

## 11. Exact scope for implementation PR 1

Recommended title: `Birthday reservation rules`

Expected scope in the Birthday extension/package:

- slot definition, override, and inventory migrations;
- Birthday slot models and the server-side availability/claim service;
- fixed two-slot frontend selector and unavailable-state handling;
- minimal admin view for slot definitions, date overrides, and availability;
- English translation keys and extension registration;
- unit, feature, and MySQL concurrency tests;
- a feature flag or disabled-by-default activation path for Canada staging.

Explicitly excluded:

- payment gateway or checkout integration;
- login/registration changes;
- add-on or package pricing;
- email/SMS delivery;
- cancellation/refund rules;
- broad bilingual content migration;
- takeout behavior;
- visual redesign;
- generic order/reservation, authentication, security, or core/vendor changes;
- production data or production deployment.

## 12. Risks and decisions still required

Risks:

- Existing generic Reservation and Orange Livewire behavior is table-oriented
  and must not be partially repurposed.
- A slot claim without a database invariant can be duplicated under concurrent
  Cloud Run instances.
- Venue-local date handling must remain stable across timezone and DST changes.
- Any future payment hold must have an explicit expiration and release path.
- A new extension must be packaged and loaded in a way compatible with the
  existing Docker and Cloud Run build process.

No secret is required. Before implementation, confirm only these business and
architecture decisions:

1. The venue timezone used for the two slot dates.
2. Whether both fixed slots are enabled every opening day by default.
3. Whether staff may close individual date/slot combinations.
4. Whether the default capacity-one value is editable in the first release.
5. The exact meaning of an active hold in the later payment workflow.

## 13. Decision

Do not create the implementation PR yet. This design is ready for user and
review-agent approval. After approval, create the focused `Birthday reservation
rules` PR from the latest `4.x`, deploy only to Canada staging, and keep Render
staging as fallback.
