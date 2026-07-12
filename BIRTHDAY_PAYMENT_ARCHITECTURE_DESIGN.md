# Birthday and Order Payment Architecture Design

Date: 2026-07-12
Environment: staging/docs only
Status: Design proposal; no runtime change

## 1. Executive Summary

This document audits the installed TastyIgniter payment and checkout paths and
proposes a shared payment foundation for two different business payables:

- the existing restaurant `Order` checkout; and
- a future `Birthday Booking` checkout that keeps the existing Reservation
  administration capability.

The recommended design keeps payment infrastructure generic and business
checkout logic separate. Birthday bookings must not be fake menu items, fake
Orders, or frontend-only objects. Order checkout keeps its existing path;
Birthday Booking owns date/slot, package, add-ons, price snapshot, hold, and
confirmation rules.

This is an audit and design document only. It creates no PaymentIntent,
payment account, webhook endpoint, migration, test order, test reservation, or
production configuration.

## 2. Scope and Safety Boundary

Included: repository audit, shared payment boundary, proposed data model,
Birthday checkout and slot-hold lifecycle, state separation, webhook/security
design, tests, rollback, and open business decisions.

Excluded: real or test payment-platform calls, PaymentIntent or checkout-session
creation, webhook registration or secret entry, database changes, vendor/core
changes, real data, real mail/SMS, refunds, and production operations.

Render staging and the Canada staging fallback architecture remain unchanged.

## 3. Repository and Installed Package Audit

PR #51 is merged on `4.x` at merge commit
`45730125929ee8afc5f5cde5cf8a8f7ac867d9c4`. The design branch starts from that
commit. Application customizations are under `app/`; installed TastyIgniter
extensions were audited from `vendor/`. No vendor file is modified.

| Area | Actual implementation | Finding |
| --- | --- | --- |
| Order | `Igniter\Cart\Models\Order` in `ti-ext-cart` | Aggregate with totals, status, customer and payment-log relations |
| Order checkout | `Igniter\Orange\Livewire\Checkout` in `ti-theme-orange` | Saves Order, then calls `OrderManager::processPayment` |
| Order manager | `Igniter\Cart\Classes\OrderManager` | Current Order session, payment selection, save, totals and payment dispatch |
| Gateway registry | `Igniter\PayRegister\Classes\PaymentGateways` | Discovers extension gateway classes and entry points |
| Gateway base | `Igniter\PayRegister\Classes\BasePaymentGateway` | Gateway form, entry-point and payment-processing contract |
| Payment extension | `vendor/tastyigniter/ti-ext-payregister` | COD, PayPal Express, Authorize.Net AIM, Stripe, Mollie and Square |
| Payment config | `Igniter\PayRegister\Models\Payment` | Gateway configuration; not a transaction ledger |
| Payment log | `Igniter\PayRegister\Models\PaymentLog` | Order-specific `order_id`, request/response arrays and refund flags |
| Reservation | `Igniter\Reservation\Models\Reservation` | Existing reservation, status history, customer and admin flow |
| Birthday flow | `app/Livewire/BirthdayReservation.php` | Saves existing Reservation and redirects to reservation success |
| Birthday rules | `app/BirthdayBooking/*` | Fixed slots, date window, occupancy and database conflict guard |

## 4. Current Order Payment Flow

```text
Checkout::onConfirm
  -> validate fields and payment code
  -> OrderManager::saveOrder
  -> igniter.checkout.afterSaveOrder
  -> OrderManager::processPayment
  -> selected gateway::processPaymentForm
  -> provider return or client completion
  -> Order::markAsPaymentProcessed
  -> OrderPaymentProcessedEvent
```

Actual findings:

1. `OrderManager::loadOrder` creates an Order from the current session, saves
   cart menus/totals, and stores its ID in the session.
2. Checkout saves the Order before payment completes. This Order-specific
   lifecycle must not become the Birthday domain model.
3. Order payment is selected by the gateway code in `orders.payment`; final
   completion uses `orders.processed` plus Order status.
4. Stripe supports PaymentIntent and off-site Checkout Session paths. Provider
   metadata currently uses `order_id`.
5. The browser return path can finish the current Order flow, but future
   Birthday confirmation must be webhook-authoritative.

Reuse risks:

- `PaymentLog` is tied to Order and is not a generic payable transaction ledger.
- Its request/response fields may contain provider data and must not become
  unredacted generic payment logging.
- The current Stripe flow has an Order processed guard but no generic unique
  provider-event ledger.
- The current session/frontend idempotency value is not a durable cross-instance
  business idempotency record.
- The existing PayRegister webhook path is CSRF-exempt at the extension path;
  a future shared endpoint must keep the exemption narrow and verify the raw
  body signature.

## 5. Current Birthday Reservation Flow

```text
select Toronto-local date and fixed slot
  -> fill contact fields
  -> BirthdayReservation::onComplete
  -> validate date, slot, guest and telephone
  -> BookingManager::loadReservation
  -> save Reservation in a database transaction
  -> reservation success page
```

The current flow stores Birthday markers and slot fields on Reservation, uses
the existing Reservation status for occupancy, applies a unique slot key, sets
the full-slot duration, removes table assignment, and allows guest contact
details. It has no package, add-on, tax, total, currency snapshot, payment
transaction, payment webhook, refund, or Order association.

Reservation remains the correct operational admin record, but not the future
payment aggregate.

## 6. Boundary Decision

The shared payment layer owns gateway adapters, provider references, normalized
payment state, idempotency, webhook verification/deduplication, safe ledger
records, and gateway configuration. It must not decide Birthday availability,
packages, add-ons, restaurant cart totals, kitchen readiness, or cancellation
policy.

Use separate application services:

- `BirthdayBookingCheckoutService`;
- existing `OrderManager`, optionally behind an app-level payment adapter.

Both should satisfy a small payable contract:

```text
getPayableType()
getPayableId()
getAmountMinor()
getCurrency()
getPaymentDescription()
```

The payment layer references payable type and ID. It must not create a fake
Order or rely on a description, session-only ID, or URL parameter.

## 7. Proposed Shared Components

Names are proposals for implementation PRs:

```text
App\Payments\Contracts\PaymentGateway
App\Payments\Contracts\Payable
App\Payments\PaymentGatewayManager
App\Payments\PaymentTransactionService
App\Payments\PaymentWebhookService
App\Payments\PaymentIdempotencyService
App\Payments\RefundService
App\Payments\Models\PaymentTransaction
App\Payments\Models\PaymentEvent
App\Payments\Models\PaymentRefund
```

An adapter may delegate to an installed PayRegister gateway, but the adapter
must be app-owned, normalize provider responses, and avoid vendor/core changes.

## 8. Proposed Data Model

This is a schema proposal only. No migration is included.

### `payment_transactions`

The existing `payments` table stores gateway configuration and must not be
repurposed. Proposed transaction fields:

| Field | Purpose |
| --- | --- |
| `payment_transaction_id` | Internal primary key |
| `payable_type`, `payable_id` | `orders` or `birthday_bookings` |
| `gateway_code` | Internal gateway configuration |
| `external_payment_id` | Provider intent/session/transaction reference |
| `idempotency_key` | Durable unique business operation key |
| `amount_minor` | Integer minor units; never float |
| `currency` | ISO 4217 code; expected first value CAD |
| `status` | Internal payment state |
| state timestamps | Authorized, captured, failed, cancelled, refunded |
| `refunded_amount_minor` | Cumulative refund amount |
| `safe_metadata` | Redacted normalized metadata only |

Recommended constraints: unique idempotency key, provider-scoped external
payment reference, and an index on `(payable_type, payable_id)`.

### `payment_events`

Store provider, unique provider event ID, transaction ID, event type, signature
verification result, normalized safe summary, processing state, attempt count,
processed time, and safe error category. Raw bodies may be verified transiently
but must not be stored or logged. Duplicate event IDs are no-ops.

### `payment_refunds`

Store transaction ID, provider refund ID with provider-scoped uniqueness,
integer minor-unit amount, currency, `pending/succeeded/failed` state, safe
reason, and timestamps. Refund policy remains a separate research phase.

### Birthday domain tables

Proposed tables are `birthday_packages`, `birthday_addons`,
`birthday_bookings`, `birthday_booking_items`, and `birthday_slot_locks`.
`birthday_bookings` references Reservation for admin operations but owns package,
add-on, price snapshot, booking state, payment association, and contact
snapshot.

## 9. Slot Hold and Confirmation Lifecycle

The current Reservation unique key protects active Birthday reservations, but
does not coordinate a payment hold before a Reservation exists. Use a
deterministic `birthday_slot_locks` row keyed by location, date, and slot.

1. Upsert the slot-lock row and lock it with `SELECT ... FOR UPDATE`.
2. Reject an active Reservation or non-expired hold.
3. Store owner and `expires_at` with a server-generated hold token.
4. Commit the local hold before calling the gateway; never hold a DB lock over
   a network call.
5. Create a local pending payment transaction with a durable idempotency key.
6. On verified payment success, reacquire the lock, verify hold, amount,
   currency, and payable identity, then confirm Booking and Reservation in one
   local transaction.
7. On failure, cancellation, timeout, or invalid hold, release the lock in an
   idempotent transaction.
8. Run explicit expired-hold cleanup; never use destructive reset commands.

The existing `birthday_slot_key` unique index remains a final compatibility
guard, but must not be the only hold coordination mechanism. All public and
admin claim paths use the same lock service. A provisional 10-15 minute hold
range may be evaluated, but is not a runtime decision in this PR.

## 10. Separate State Models

### Birthday Booking state

```text
draft -> held -> pending_payment -> paid -> confirmed
pending_payment -> payment_failed
pending_payment -> expired
held -> cancelled
paid -> refund_pending -> refunded
confirmed -> cancelled (only after approved policy)
```

### Payment state

```text
pending, requires_action, authorized, succeeded, failed, cancelled,
refund_pending, partially_refunded, refunded
```

Gateway-specific names map to this internal vocabulary.

### Reservation status

Reservation status remains operational, not payment state. Current Birthday
rules treat Pending and Confirmed as occupying; Canceled, Expired and Rejected
release the slot. Other statuses require an explicit mapping.

Recommended mapping:

| Event | Reservation action |
| --- | --- |
| Hold created | Create/associate Pending Reservation only if admin visibility requires it |
| Verified payment success | Confirm Birthday Booking and configured Reservation status |
| Payment failure/expiry | Release hold and cancel/remove provisional record per final schema |
| Approved cancellation | Use existing canceled Reservation status |
| Refund state | Keep separate; do not overload Reservation `status_id` |

Do not invent Reservation statuses just to express payment states. A separate
Birthday Booking state is safer.

## 11. Price and Currency

Server-side calculation must snapshot package ID/name, base price, included
items, add-on ID/name/unit price/quantity, subtotal, discount if enabled, tax
basis/amount, total, currency, calculation version, and timestamp. The client
may display a preview but is never authoritative.

Use integer minor units or fixed-precision decimal consistently; never use
binary floats for payment authority. CAD is the expected first currency, but
tax-inclusive display, Quebec tax calculation, discounts, and currency support
remain business decisions. Later catalog price changes must not change a paid
Booking snapshot.

## 12. Payment and Webhook Sequence

```text
select date/slot/package/add-ons
  -> server validates and computes snapshot
  -> claim slot hold
  -> create local pending transaction
  -> gateway creates hosted checkout/payment session
  -> browser completes provider flow
  -> signed webhook is verified
  -> unique event is recorded once
  -> provider object, amount, currency, and payable are verified
  -> transaction, Booking, and slot are locked
  -> payment and Booking/Reservation states commit
  -> post-commit notification event
```

Browser return is UX only. A success redirect without a verified webhook stays
`pending_payment`. Provider calls use stored idempotency keys and never run
inside a transaction holding the slot lock.

## 13. Webhook, Idempotency, and PCI Security

The shared webhook service must verify the raw body and gateway-specific secret,
reject stale/invalid signatures, uniquely record provider event IDs, whitelist
event types, verify payable/amount/currency/gateway, lock local state, and retry
only safe failures. No card data, CVC, full metadata, signature, raw body, or
secret may be logged or stored.

The installed Stripe adapter already verifies signatures and supports payment
intent and checkout-session events, but its current queue job resolves
`order_id` and has no generic event ledger. Wrap or replace it behind the shared
boundary; do not copy its Order assumptions into Birthday code.

Use hosted checkout or a tokenized Payment Element. The application may retain
provider references and non-sensitive brand/last four only. PaymentProfile
reuse for anonymous Birthday checkout requires a separate review.

## 14. Account, Notification, and Refund Boundaries

Current Birthday checkout does not force registration. Recommended future flow:
browse anonymously, require login/registration before payment, bind the
server-side Booking/hold after authentication, restore server-side state, and
store a contact snapshot. Email verification remains an open decision. This
must be a separate Registration Gate PR.

Notifications must be emitted only after committed events such as verified
payment, confirmed booking, failure, expiry, cancellation, or refund. Canada
staging uses `MAIL_MAILER=log`; no external mail is sent in test mode.

Refund/cancellation interfaces may be designed now, but implementation waits
for Quebec consumer-law research and business confirmation of full refund,
partial refund, cancellation window, tax treatment, and manual review.

## 15. Admin Requirements

Keep the existing Reservations list. Future Birthday admin views should show
booking reference, contact snapshot, date/slot, package/add-ons, immutable
price breakdown, booking/payment/refund/notification states, safe transaction
reference, and timestamps. Payment views should show source (`birthday` or
`order`), payable reference, gateway, amount/currency, safe external reference,
and timestamps. Never show complete card data, CVC, secrets, signatures, or
raw provider payloads.

## 16. Recommended PR Sequence

| PR | Scope | Gateway | Migration |
| --- | --- | --- | --- |
| A | Birthday packages/add-ons, translations and admin CRUD | No | Additive/reversible |
| B | Birthday Booking domain, server pricing and snapshot | No | Additive/reversible |
| C | Slot hold/lock, expiry cleanup and concurrency tests | No | Additive/reversible |
| D | Shared transactions, gateway adapter, event ledger, idempotency and refund interfaces | No live gateway | Additive/reversible |
| E | Staging test-mode checkout with fake/local gateway | Test mode only | Per D |
| F | Registration gate and checkout-state restore | No new gateway | Maybe |
| G | Post-commit notification preferences and log/no-op delivery | No external delivery | Maybe |
| H | Quebec cancellation/refund research | No runtime code | No |
| I | Compliant refund implementation after H | After approval | Additive/reversible |
| J | Reuse shared payment layer in Order checkout | Staging first | Maybe |

Recommended order is A -> B -> C -> D -> E -> F -> G, with H before I. J
follows a stable Birthday test-mode path and must not regress Order checkout.

## 17. Test Matrix

Unit: price rounding, immutable snapshots, hold expiry, payment transitions,
idempotency, duplicate/out-of-order events, amount/currency mismatch, refunds.

Integration: same-slot claims, hold versus Reservation race, test success,
failure/expiry release, duplicate webhook no-op, browser-before-webhook pending,
webhook-before-browser exactly-once confirmation, provider retry, and existing
Order checkout regression.

Browser: package/add-on selection, registration restore, test success/failure,
refresh/back behavior, hold expiry, mobile layout, and existing menu/cart flow.

Security: client amount/payable/slot tampering, invalid signature, replay,
cross-environment event, log/database raw-data inspection, and cross-customer
access control.

## 18. Rollback and Open Decisions

This PR rolls back with a Git revert. Future PRs require feature flags,
additive reversible migrations, no destructive reset, provider-independent
local state, disable switches preserving existing Reservations/Order checkout,
and Render staging fallback.

Business decisions required before implementation:

1. One package or multiple packages at launch?
2. Price per slot or package-specific pricing?
3. Add-on quantity, inventory, and cutoff rules?
4. Hold duration and renewal policy?
5. CAD-only or multi-currency?
6. Tax-inclusive display and tax calculation owner?
7. Discount support?
8. First staging test-mode gateway?
9. Login/registration and email verification requirements?
10. Payment-failure retry behavior?
11. Admin manual confirmation of unpaid Booking?
12. Cancellation/refund window and legal wording?
13. Required post-payment notifications?
14. Reservation creation at hold or only after verified payment?

No secret is needed to answer these business questions.

## 19. Current Conclusion

The installed PayRegister extension is reusable as a gateway adapter, but its
Order-specific PaymentLog, Order metadata, and current webhook job must not
become the Birthday payment model. The safe direction is an app-owned
transaction/event/refund layer with a polymorphic payable boundary, separate
Birthday Booking aggregate, and shared slot-lock service.

The next decision is to confirm the business choices, not to connect Stripe.
Implementation should begin with small staging-only data/domain PRs.
