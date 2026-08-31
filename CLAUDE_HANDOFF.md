# Claude Project Handoff

Date: 2026-08-20
Environment: repository, Canada staging read-only audit, and documentation
Status: handoff freeze; Delivery remains closed

This is the primary handoff source for the next coding agent. It is designed to
stand alone; prior ChatGPT or Codex conversations are not required. Read
`AGENT_WORKFLOW.md` before starting any task.

## 1. Executive summary

The project is a project-owned customization of TastyIgniter on branch `4.x`.
Canada staging is healthy and Pickup-only. Delivery D1, D2, D3B, and the Q-011
geocoder privacy remediation are complete, but Delivery is not enabled:
`DELIVERY_ENABLED=false` remains authoritative and no Delivery-enabled revision
serves main traffic.

Birthday venue-booking rules, catalog, immutable booking snapshots, UTC
hydration, slot holds, and read-only admin visibility are implemented. Public
Birthday checkout, account/email-verification integration, payment,
notifications, and production launch are deferred. Birthday is a separate
business domain and must never be represented as a food menu item or ordinary
restaurant Order.

The handoff PR is merged and the frozen baseline is recorded in section 2, so
the next phase is unblocked. It is a 0%-traffic tagged Canada staging revision
with `DELIVERY_ENABLED=true` for D3C isolated acceptance. Do not route main
traffic or touch production without a separate explicit approval.

## 2. Frozen baseline

- Repository: `abandon-etc/TastyIgniter`.
- Base branch: `4.x`.
- PR #69, `Fix Delivery geocoder error redaction`, is merged.
- PR #70, `Record Delivery D3 Q-011 staging validation`, is merged at
  `f731f775e5d3f069a959641323b544007ca21552`.
- The freeze audit started from clean, remote-synchronized `4.x` at exactly
  `f731f775e5d3f069a959641323b544007ca21552`.
- No open Delivery implementation PR existed at freeze time.
- The merge commit of the PR that added this document is the
  **CLAUDE HANDOFF BASELINE**:
  `6c9331c1526778e474be2a980831f1e5505955b4`, the merge commit of PR #71,
  `Freeze project baseline for Claude handoff`, merged 2026-08-20. Before new
  work, fetch `origin/4.x`, verify that commit is contained in `HEAD`, and
  start from a clean worktree.

Never start from a PR #69, PR #70, handoff feature branch, or stale local clone.
Do not read, modify, or stage `.codex-tmp/`.

## 3. Repository state and source boundaries

Project-owned implementation is under `app/`, `config/`, `extensions/`,
`resources/`, `tests/`, and project runtime files. Installed dependencies under
`vendor/` and TastyIgniter core are inspection-only and must not be patched.

Primary supporting records:

- `ADMIN_CONFIGURATION_TRACKER.md`
- `CHANGELOG_AI.md`
- `CLOUD_RUN_CANADA_STAGING_RUNTIME.md`
- `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md`
- `BIRTHDAY_PAYMENT_ARCHITECTURE_DESIGN.md`
- `RENDER_RUNTIME_READINESS.md`

For every new task, sync the latest `4.x`, create a focused branch, preserve
unrelated user changes, and update the tracker and changelog when a material
state change occurs.

## 4. Infrastructure

### GCP Canada staging

- Project: `le-chateau-canada-staging`.
- Region: `northamerica-northeast1`.
- Components: Cloud Run, Cloud SQL for MySQL, Cloud Storage, Secret Manager,
  Artifact Registry, and Cloud Build.
- Runtime service account:
  `cloud-run-staging-runtime@le-chateau-canada-staging.iam.gserviceaccount.com`.
- User-managed service-account keys at freeze readback: 0. Workload identity
  uses the attached runtime identity; do not create or download a key.
- Cloud SQL attachment: present; referenced instance read back `RUNNABLE`.
- Cloud Storage mount reference: one; reference was readable.
- Secret Manager references: five unique references; all five resolved. No
  secret value was read or recorded.
- Required service APIs were enabled. The Q-011 regional Cloud Build record was
  `SUCCESS`, and the Artifact Registry image digest was readable.

Do not record database credentials, connection strings, bucket names, Secret
Manager values, API keys, or provider credentials in Git, chat, logs, shell
history, screenshots, or documentation.

## 5. Canada staging freeze readback

This state was read directly from GCP on 2026-08-20; it is not copied from an
older deployment note.

| Item | Frozen readback |
| --- | --- |
| Cloud Run service | `le-chateau-canada-staging` |
| Region | `northamerica-northeast1` |
| Main-traffic revision | `le-chateau-canada-staging-d2fix-31821289` |
| Main traffic | 100% |
| Active image digest | `sha256:72371b610a2dff66d29dcee09a2095c72c2f6bb0d932d33744db3444c3689102` |
| Retained Q-011 revision | `le-chateau-canada-staging-q011-3fc841d` |
| Q-011 tag | `q011-3fc841d` |
| Q-011 traffic | 0% |
| Q-011 image digest | `sha256:388a60cd43539746cc1e5725066a4fd7fd9bee0c538401ab8646da68608df2d1` |
| Retained failed D2 revision | `le-chateau-canada-staging-d2-fab804d2`, 0% |
| Global Delivery gate | `DELIVERY_ENABLED=false` |
| Health | HTTP 200, body `ok` |
| Redacted runtime fingerprint | `b82e3aa41eaba24a1bc54784f9f889209a83b178cda1baeec85f069337e41b1b` — **void, never verified**, see below |

The latest Ready revision is the retained 0%-traffic Q-011 revision. It is not
the active/main-traffic revision. Traffic allocation, not latest-Ready order,
defines the live staging runtime.

### Custom domain: two things that are easy to get wrong

Recorded 2026-08-29 as the domain work begins, because both are the kind of
assumption that only surfaces after something is already pointed at the wrong
place.

1. **A Cloud Run domain mapping points at a *service*, not at a revision or a
   revision tag.** Mapping `staging.lechateaudesenfants.ca` therefore sends that
   name to whatever revision holds 100% of traffic - which is the live site
   itself, not a test copy. It is a second name for production, not a route to a
   tagged revision. Sending a hostname to a specific tag needs an external HTTP(S)
   load balancer with a serverless NEG, which is a different mechanism and a
   separate piece of work.
2. **A subdomain is not a separate environment.** Every copy and every mapping
   share the one database, so a new hostname renames the same site. It is
   genuinely useful for verifying the things that are *about* the name - TLS
   certificates, absolute links, redirects, third-party callbacks and webhooks -
   and it isolates no data whatever. Anything written through the staging name is
   written to the live data.

### Domain onboarding: 1a now, 1b held on a stated trigger

Split 2026-08-29 so the two halves stop sharing a schedule.

**1a - Google domain ownership verification. Doing now.** It adds one TXT record
and nothing else: no hostname is created, no certificate is issued, nothing
enters the Certificate Transparency logs, and there is no new exposure. It is
also the only step needing the owner's own Google account, so it is the one with
human waiting time in it. Verify the **root** `lechateaudesenfants.ca`, which
covers every subdomain and the bare domain at launch, using the same account
gcloud authenticates as. The agent cannot mint the token: the Site Verification
API rejects the gcloud credential with `ACCESS_TOKEN_SCOPE_INSUFFICIENT`. Note
for whoever writes the record - a TXT record has no Cloudflare proxy toggle at
all, only A, AAAA and CNAME do, so the grey-cloud requirement applies to the
mapping CNAME later and not to this TXT.

**1b - the `staging.` mapping and its certificate. Held.** Not because it would
open an indexing window; that window is already open, see
`DEPLOYMENT_IMPACT_TIMEZONE_FIX.md`. Held because **the hostname has no consumer
yet**. Its real user is payment callbacks, and that work waits on the Snappy
conversation. Creating it now buys nothing and adds a name to maintain.

**Trigger to release 1b, whichever comes first**, recorded here so it does not
quietly die:

- the fail-safe indexing control ships with the Option C deployment; **or**
- step E is about to start.

Phase two of the domain work - setting `APP_URL` and `ASSET_URL` explicitly, so
generated links stop falling back to `CLOUD_RUN_SERVICE_URL` and silently
pointing at `run.app` - travels with 1b and still needs its own approval.

Carried forward from the phase-one read: whether Cloud Run domain mappings are
supported in `northamerica-northeast1` is **not yet proven**. Listing works
(HTTP 200, no items), but creation is the test. If the region does not support
them the fallback is an external HTTP(S) load balancer with a serverless NEG,
which is the same mechanism needed to point a hostname at a tagged revision, so
that work would not be wasted.

### Carte key on Cloud Run: the start.sh change, archived from closed PR #142

PR #142 carried a working fix for the Carte-key attach failure and was closed
unmerged on 2026-08-30: the marketplace turned out to have no language-pack
category, which removed the reason to bind a key from the running site, and the
branch had gone stale. The change itself is correct and small; if binding is
ever needed again, re-apply this block in `docker/cloudrun/start.sh`, before
"Rendering Nginx configuration" (it was verified with `sh -n`; CI never ran to
completion on that branch for unrelated reasons). Archived verbatim:

```sh
# TastyIgniter's Carte-key flow persists the key through
# SystemHelper::replaceInEnv(), which reads and rewrites "${APP_ROOT}/.env" and
# throws FileNotFoundException when that file does not exist. .env is excluded
# from the image by .dockerignore, correctly, because configuration arrives here
# as environment variables. Creating it empty makes that rewrite a harmless
# no-op - the regex matches no line and the empty content is written back - so
# the flow continues to its real persistence step, which stores the key in the
# shared settings table.
#
# Only this one file is handed to www-data. Overwriting an existing file needs
# write permission on the file, not on its directory, so the application root
# stays root-owned and composer.json, auth.json and lang/ remain unwritable at
# runtime. That is deliberate: language packs must be committed and baked into
# the image, never written at runtime, because the container filesystem is
# per-instance and does not survive the instance.
#
# The file is left empty on purpose. An IGNITER_CARTE_KEY= line here would put
# the key in a per-instance file that disappears with the instance, for no gain.
if [ ! -f "${APP_ROOT}/.env" ]; then
    log "Creating empty ${APP_ROOT}/.env so the Carte-key rewrite does not fail"
    : > "${APP_ROOT}/.env"
fi
chown www-data:www-data "${APP_ROOT}/.env" || true
chmod 600 "${APP_ROOT}/.env" || true
```

Two facts to carry with it: `clearCarte()` walks the same `replaceInEnv()`
path, so a site without this block can bind a key it then cannot unbind; and
what the fix unblocks is *seeing* the hub catalogue (`hasValidCarte()`), never
installing packs at runtime, which an immutable image rules out regardless.

That fingerprint is **void**. The freeze described it as a SHA-256 of
normalized, redacted service metadata but never recorded the normalized field
list or the algorithm, and neither can be reconstructed from anything still
held in this repository or in GCP. The value can therefore be neither confirmed
nor disproved: it has never been verified, and it never can be. Do not cite it
as evidence of any runtime state, and do not compare a new measurement against
it. It is kept in the table above only as a record of what the freeze claimed.

It is superseded by fingerprint FP-1, specified with its field list, algorithm,
and stated limits in `CLOUD_RUN_CANADA_STAGING_RUNTIME.md` and implemented in
`tools/fp1.py`. FP-1 is a new baseline whose history
starts on 2026-08-20. It is not a recomputation of the void value, and the two
are not comparable. The FP-1 baseline measured against the main-traffic
revision in this table is
`2127efd6d63de53e6d9fbc5388f9db3fee72d0575eec25a09b9f99e9ad8565d3`, with the
per-field digest table recorded alongside the definition.

## 6. Delivery D1, D2, and D3

### D1 — completed

- Project global Delivery flag.
- Server-side Delivery availability and checkout gates.
- Stale Delivery session normalization and Delivery-only state cleanup.
- Pickup fallback while Delivery is closed.
- Storefront and API fail-closed behavior.
- Delivery Orders API writes blocked while the flag is false.

Key project-owned code is in `app/Delivery/`,
`app/Http/Middleware/NormalizeDeliveryFulfillment.php`, and related tests under
`tests/Feature/Delivery/` and `tests/Unit/Delivery/`.

### D2 — completed

- Conditional Delivery storefront UI.
- Delivery-off state is Pickup-only.
- Conditional homepage local search.
- Menu fulfillment tabs.
- Pickup checkout no longer renders the Delivery-driver note.
- Desktop/mobile/accessibility and session regressions passed.

The accepted D2 runtime is the current 100%-traffic revision listed above.

### D3B — completed and stored while closed

| Parameter | Stored state |
| --- | --- |
| Delivery Area | `D3 Montreal Delivery Area` |
| Type/count | Polygon; one default area |
| Vertices | 36 unique vertices |
| Base fee | CAD 5.00 |
| Free threshold | CAD 80.00 subtotal |
| Minimum | CAD 20.00 |
| Distance surcharge | Off |
| Hours | Monday-Friday 12:00-21:00 (changed to every day 12:00-21:00 on 2026-08-28; see the decision table) |
| Weekend | Saturday-Sunday closed |
| Pickup | Unchanged |
| Tax | Unchanged |

Do not copy the polygon vertices into issues, logs, PR descriptions, or public
errors. The complete geometry remains in the approved staging configuration.

### Q-011 — resolved for the tested Canada staging failure path

PR #69 adds project-owned redaction for raw geocoder logging and Livewire
provider exceptions. Forward geocode, reverse geocode, autocomplete, place
lookup, empty results, business validation, successful reverse behavior, and
the closed Delivery API path were covered. Programming errors are not disguised
as address validation errors.

The final same-image, exact-window Canada staging rerun passed. It exposed no
full synthetic address, provider URL, credential, raw provider exception,
geometry, SQL/internal ID, database diagnostic, or PHP path. Provider failures
remain fail-closed and no real business data was created.

Public Nominatim is **not approved for production Delivery traffic**. It lacks
a stable application identity and shared cross-instance one-request-per-second
control, attribution needs an operational guarantee, Google failure can fan
out fallback load, and public autocomplete is prohibited. Resolve this as a
separate production-readiness decision; do not silently treat Q-011 privacy
acceptance as provider approval.

### D3C — not started

No `DELIVERY_ENABLED=true` revision has served main traffic. Delivery is not
launched. Do not infer launch status from the stored polygon, enabled Location
Delivery setting, or the retained Q-011 revision.

## 7. Birthday architecture and business rules

Birthday is a venue booking domain, not an ordinary restaurant table booking,
food menu item, or Order.

Current business invariants:

- Fixed Toronto-local slots: 12:00-16:00 and 16:00-20:00.
- Valid date window: today +2 through today +60; same-day is rejected.
- Capacity: one booking per Location/date/slot.
- Guest count is informational; it does not multiply slot capacity.
- Slot hold duration: 15 minutes.
- Currency: CAD.
- Login/registration and email verification are required before payment in the
  final public flow.
- A Reservation may be created only after payment is verified in the final
  checkout architecture.

Completed:

- Birthday rules and child-theme routing.
- Admin context fixes.
- Packages and add-ons.
- Birthday Booking domain with immutable package/add-on/contact/price snapshots.
- Integer minor-unit pricing and CAD snapshotting.
- UTC hydration and deterministic add-on ordering.
- Concurrency-safe 15-minute slot holds and expiry command.
- Read-only admin Booking/Hold visibility.

Deferred:

- Final public Birthday checkout.
- Account, login-state restore, and email-verification integration.
- Payment and webhooks.
- Final notification delivery.
- Production launch.

Do not reuse the legacy food checkout or create a normal Order to represent a
Birthday Booking.

## 8. Payment architecture

Birthday and food keep separate checkout, status, and business workflows. They
may share gateway adapters, transaction primitives, verified webhook handling,
idempotency, and refund infrastructure.

Planned project-owned ledgers are `payment_transactions`, `payment_events`, and
`payment_refunds`. They are design decisions only; payment implementation is
deferred and those tables must not be assumed to exist.

Mandatory rules:

- Webhook state is authoritative; a browser redirect is UX only.
- Verify payable identity, amount, currency, provider object, and gateway on the
  server.
- Use integer minor units; never trust client totals or binary floating point.
- Deduplicate provider events and keep raw payloads, signatures, card data, and
  credentials out of logs and storage.
- If payment succeeds after a hold expired, disappeared, or was reclaimed, do
  not steal the slot. Keep payment succeeded and move the Birthday Booking to
  `payment_exception`/`manual_review` for an approved recovery decision.

Read `BIRTHDAY_PAYMENT_ARCHITECTURE_DESIGN.md` before any payment work. Payment,
authentication, authorization, PII boundaries, and money/availability races
require independent review and explicit user approval.

The payment workstream (the user's 板块四) was planned on 2026-08-28:
`PAYMENT_WORKSTREAM_PLAN.md` carries the step D and step F implementation
plans and the infrastructure options (restore rehearsal; separate payment
test database), `CI_ENABLEMENT_PLAN.md` the CI decision, and
`QUEBEC_REFUND_CANCELLATION_RESEARCH.md` the step H research. All are plans
and research only; each execution is separately approved, and CI enablement
is sequenced before the step D code merges.

## 9. Security constraints

- Use only synthetic test data unless a separately approved task explicitly
  authorizes otherwise.
- Never expose `.env`, `.local`, Secret Manager values, API keys, passwords,
  tokens, connection strings, real addresses, customer/order/reservation/
  payment records, raw provider payloads, or complete polygon coordinates.
- Never modify `vendor/` or TastyIgniter core; use project-owned wrappers,
  listeners, middleware, views, extensions, and tests.
- Do not suppress all logging or globally hide application errors to mask a
  leak. Sanitize at the project-owned boundary and retain safe diagnostics.
- Production, public traffic, authentication, payment, destructive data work,
  permissions, and secret changes always require explicit approval.

## 10. Known issues and deferred gates

### Q-005 — language fallback

`en_CA` still falls back to `fr_CA`. This is non-blocking for the current
Pickup-only/Delivery staging phase and belongs to the bilingual architecture
work.

### Q-010 — scheduled Pickup time binding

The upstream Orange scheduled Pickup time select uses `wire:ignore` and does
not reliably synchronize a changed same-day value to Livewire. Future orders
remain disabled. Resolve this before depending on scheduled Pickup.

**Status 2026-08-22: masked, not fixed.** The user changed the stored
ASAP/later restriction to ASAP-only for Pickup and Delivery on 2026-08-22, so
the scheduled time select is no longer rendered and the binding defect cannot
be reached from the storefront. Nothing in the theme changed. It returns the
moment a later-slot choice or future orders are re-enabled, and must not be
closed on the strength of its absence.

### Fresh-install migration ordering

The root Birthday reservation migration can run before the Reservation
extension creates `ti_reservations`. The already-initialized Canada staging
database is not affected. Fix this in a separate focused PR; do not hide it
inside Delivery or payment work.

**Reproduced and named on 2026-08-28**, on a fresh database in the first
real CI runs (draft PR #122):
`2026_07_10_000000_add_birthday_booking_fields_to_reservations_table`
fails with 42S02 because `php artisan igniter:up` runs it in the root
migration pass before the Reservation extension has created
`ti_reservations` — identically on PHP 8.3, 8.4, and 8.5.

**Fixed on 2026-08-29 by PR #124**, and the root cause proved wider than
this note: the installed core has no extension dependency ordering at
all, and local extensions register — and migrate — before every vendor
extension, so the Birthday group's own tables (foreign keys to customers
and locations) were equally exposed on a fresh database. The fix moved
the reservations alteration into the Birthday extension's own migrations
(idempotent where already applied, loudly failing — never silently
skipping — if the order regresses) and added
`App\BirthdayBooking\BirthdayMigrationOrder`, which moves the
`abandon.birthday` group to the end of the migration map at boot with the
group key, and so the migrations ledger identity, unchanged; a contract
test pins the mechanism against vendor change. Verified in CI: fresh
`igniter:up` passes on all three PHP rows. Already-initialized databases
(staging, the payment test copy) were never affected and re-run the moved
migration as a recorded no-op.

### Pinning the geocoder chain for a deployment

TastyIgniter resolves the geocoder driver and the Google API key from the
settings table, which every revision of a service shares. The provider chain is
therefore a property of the database, not of the deployment: two revisions
cannot be given different chains, and a chain cannot be pinned for one revision
without changing it for the revision serving main traffic.

`App\Delivery\GeocoderChainOverride` narrows that, driven by two environment
variables read through `config/delivery.php`:

| Variable | Unset | Set |
| --- | --- | --- |
| `DELIVERY_GEOCODER_DRIVER` | stored driver stands | pins `igniter-geocoder.default` |
| `DELIVERY_GEOCODER_PROVIDERS` | stored chain stands | selects the named providers, in the order named |

When neither is set nothing changes, which is the state in every environment
today. A provider named but not configured is skipped rather than invented,
and each selected provider carries its existing configuration across untouched.

It is registered with `afterResolving`, not `resolving`, because the package's
own resolving callback rewrites the same configuration from the settings table.
`afterResolving` runs once every resolving callback has, so the override does
not depend on provider registration order.

**Why acceptance needs it.** Google and Nominatim can place the same address
tens of metres apart, and Delivery Area boundaries are decided at that scale.
If a transient Google failure falls through to Nominatim without saying so, an
inside-or-outside result cannot be attributed to a known coordinate source.
Decision 24 also fixes production on Google alone with the public Nominatim
fallback retired, so a boundary matrix run against the stored chain would be
validating a configuration production will not have.

**It cannot inject a failure.** There is no flag that makes a provider fail.
Naming no providers leaves a deployment with none configured, which is a
configuration and not a trapdoor, and is how the `Provider unavailable`
acceptance item is reached.

The resolve-and-apply path is not covered by an automated test. Resolving the
geocoder reads the settings table and the default country row, which the test
environment has no database for.
`tests/Unit/Delivery/GeocoderChainOverrideTest` covers the override logic
including the untouched case, and
`tests/Feature/Delivery/GeocoderChainOverrideChannelTest` covers the
registration and the configuration shape. The remaining step is verified on a
deployed revision by reading back which provider answered.

### Weekday schedules are shifted by one under a Canadian locale

Delivery reports itself closed on Friday inside its configured Monday to Friday
window. Root cause is confirmed from code plus a reproducible probe, without
database access.

1. `Igniter\Flame\Translation\Localization` calls `Carbon::setLocale()` on
   every request. This site runs `fr_CA`.
2. Carbon 3.13.1 resolves the first day of the week from the locale. Under
   `fr_CA` and `en_CA`, `startOfWeek()` returns **Sunday**; under the default,
   `en`, and `fr` it returns Monday. Verified by running Carbon directly.
3. `WorkingHour::$weekDays` is `['Mon','Tue','Wed','Thu','Fri','Sat','Sun']`, so
   the admin labels stored index 0 as Monday.
4. `WorkingHour::getDay()` computes `startOfWeek()->addDays($weekday)` and
   formats the day name, so index 0 resolves to Sunday.

A schedule entered as Monday to Friday is therefore interpreted as Sunday to
Thursday, and Friday and Saturday become closed.

**A false lead worth recording.** `HasWorkingHours::getWorkingHourByDateAndType`
uses `format('N')`, which is Monday=1, against Monday=0 storage. It is a genuine
off-by-one and it fits the symptom exactly, but the method has no callers
anywhere in vendor, app, or extensions. It is dead code and not on the live
path. Checking callers before believing it prevented a fix to something that
does not run, followed by a false claim of repair.

#### What else is affected

| Schedule | Days configured | Effect |
| --- | --- | --- |
| Delivery | Monday to Friday | Exposed. Currently failing |
| Collection, pickup | All seven | Latent. Any shift still lands on an open day |
| Opening | All seven | Latent, as above |
| Reservation | Uses the opening schedule | Affected only if opening hours are ever restricted by day |
| Birthday | Not applicable | Structurally immune |

Birthday builds availability from `config('birthday_booking.slots')` through
`App\BirthdayBooking\BirthdaySlot`, whose fields are code, start, end, label,
and capacity. It holds no weekday and never constructs a `WorkingSchedule`.

The latent cases are the dangerous ones: they are invisible only because those
schedules are open every day. Restricting any of them by day, for a holiday, a
seasonal change, or a single closure, brings the same failure back.

#### Do not fix it by compensating in the admin

Entering Sunday to Thursday to obtain Monday to Friday behaviour works
immediately and is forbidden. It permanently decouples what the screen says
from what the system means: the interface would read Sunday while the effect is
Monday. Anyone who later corrects it to what it should say, including the owner,
breaks it again, and nothing on the screen explains why.

The correct direction is to fix the first day of the week on the project side so
that the admin is what-you-see-is-what-you-get. Assess the blast radius before
proposing it: anything else that rests on week boundaries, such as weekly
reporting or period statistics, moves with it. Do not change it globally without
that assessment.

### The application timezone fails open to UTC, per container instance

Found on 2026-08-29 while characterising a copy that reported the shop
closed at 21:10 Friday. It is not a deployment mistake and not specific
to one revision: it is a latent fail-open on the live path of every
revision, including the one serving main traffic.

The chain, each link read from source and confirmed by a read-only job:

1. `config/app.php:72` is `'timezone' => 'UTC'` — **hardcoded, with no
   `env()` call**. `APP_TIMEZONE=America/Toronto` is set on every
   revision and is read by nothing in `app/`, `config/`, `bootstrap/`, or
   `vendor/tastyigniter`: it is inert.
2. The only thing that makes the application run on Montreal time is
   `Igniter\System\ServiceProvider::updateTimezone()`, called on
   `app.booted`:
   `date_default_timezone_set(Settings::get('timezone', Config::get('app.timezone', 'UTC')))`.
   The stored setting is correct — the `timezone` row reads
   `America/Toronto`, one row, verified 2026-08-29.
3. `Settings::getFieldValues()` returns `[]` when `Igniter::hasDatabase()`
   is false, and `hasDatabase()` wraps its schema probe in
   `catch (Exception) { $this->hasDatabase = false; }`. **Any database
   hiccup at boot** — a cold start racing Cloud SQL, a connection limit,
   a transient socket failure — therefore yields no settings at all.
4. With no settings, the default applies: `config('app.timezone')` =
   `UTC`. Both `hasDatabase` and `fieldValues` are memoised on singletons,
   so the instance **stays on UTC for its entire life**, serving every
   request four or five hours ahead.

What that looks like: the storefront shows the shop closed while it is
open (or open while closed), the opening panel names tomorrow's date, and
the order-time check refuses adds — all with correct data and a correct
stored setting. It is invisible in the afternoon, when UTC and Montreal
fall on the same side of every boundary, and appears after 20:00 local,
when UTC has crossed midnight.

Evidence for the two states, same copies:

| When | `d3c-min-9a4c1bc8` | `d3c-fix-be6835a9` |
| --- | --- | --- |
| 2026-08-28 21:10 EDT (01:10 UTC) | Closed, "Opening sam. 12:00 pm" — a UTC clock | "We are open" — a Montreal clock |
| 2026-08-29 11:25 EDT (15:25 UTC) | Closed, "starts sam. 12:00 pm", server refused an add: "outside our Cueillette hours" — a Montreal clock | identical |

The 11:25 pair is executed evidence, not rendering: a UTC instance would
have been at 15:25, inside 12:00-22:00, and would have accepted the add.

**Proposed fix, not yet applied** (Level 1, awaiting approval):

1. `config/app.php` reads `env('APP_TIMEZONE', 'UTC')`, which makes the
   variable already deployed on every revision the fallback, so a
   settings-read failure lands on Montreal instead of UTC.
2. `docker/render/php-production.ini` sets `date.timezone` as a second
   line of defence for code that runs before the framework boots.
3. A test pinning that `config('app.timezone')` follows `APP_TIMEZONE`,
   so the hardcoded value cannot come back unnoticed.

That removes the blast radius but not the mechanism: the settings read
can still fail silently, and the app would then run on the env fallback
rather than the stored setting. Making that visible — a logged warning
when the settings-backed timezone is unavailable — is a separate,
smaller change to consider with it.

#### Hours live in two stores, and they use different day indexing

Found on 2026-08-28 while applying the every-day Delivery hours. The vendor
keeps a schedule twice: the `working_hours` rows (weekday 0 = Monday, the
store the behavioural `WorkingSchedule` is built from, and what the weekday
correction rebuilds) and a `hours` LocationSettings JSON
(`HasWorkingHours::createScheduleItem()` reads it; the storefront info
panel and the admin form render from it). The admin's `updateSchedule()`
writes both; any direct write must too, or display and behaviour diverge.
The JSON's `daily.days` list indexes Sunday as 0 (the stored Monday-Friday
schedule was `["1".."5"]` there, against rows 0-4), and the info panel's
day labels shift with the locale week start, so the panel looked right
under fr_CA only because the two offsets cancelled. With every day open the
indexing differences map open onto open and are harmless; they matter again
the moment any schedule is day-restricted. Recorded with the write in
`ADMIN_CONFIGURATION_TRACKER.md`.

### The Delivery minimum is read from the stored setting, not from the fee rules

The vendor's Delivery minimum is the larger of the stored Location minimum
and a minimum derived from the delivery area's fee rules, and that derivation
returns the matched rule's threshold whatever the rule says. With the rules
stored for D3B, "free at or above CA$80.00" then "CA$5.00 below CA$80.00",
every basket under CA$80.00 matched the second rule and the minimum became
CA$80.00, while the stored CA$20.00 never reached the customer. The basket's
"Min. Order Amount" label and the checkout button read the same value, so
both were wrong together. Pinned by
`tests/Feature/Delivery/DeliveryAreaRuleMinimumTest.php`.

`App\Delivery\DeliveryOrderType` replaces the vendor's Delivery order type
through a container binding in `AppServiceProvider` (the vendor resolves each
order type through the container, so the binding reaches every storefront
path). It keeps the vendor's composition and narrows what counts as an area
minimum: only a matched rule that makes delivery unavailable, a negative
charge shown in the admin as "delivery is not available", carries one. That
is the vendor's own way of giving an area a minimum, and it still blocks.
Covered by `tests/Feature/Delivery/DeliveryOrderTypeMinimumTest.php`, which
asserts the label path and the gate path separately and keeps a reverse
assertion that an unavailable-below-total area still refuses.

**Known cost.** A positive-charge "below" rule no longer implies a minimum.
If the delivery range is ever split into zones with their own thresholds, a
farther zone that starts at CA$40.00 must be expressed as "delivery is not
available below CA$40.00", not as a fee rule below CA$40.00. In the vendor's
data structure the two are distinguishable only by the sign of the charge,
which is what the override reads. Recorded in `ADMIN_CONFIGURATION_GUIDE.md`
for the owner.

**Why not reshape the rules instead.** The stored rules cover disjoint
ranges and are order-robust; the rules-only remedy would have added an
all-orders rule and made free delivery depend on the row order in the admin,
where a drag silently charges CA$5.00 on a CA$100.00 basket. The evaluation
is in `D3C_PROGRESS.md` and `CHANGELOG_AI.md` for 2026-08-22.

**Consumers enumerated before merging, 2026-08-22.** Who constructs the order
type was settled by the binding; who reads the minimum was enumerated
separately across `vendor/tastyigniter` (core and every installed
extension, the Orange theme, the API), the project extension and theme, and
`app/`. Every consumer reaches the overridden method: the basket label
(`cart-box.blade.php` → `Location::minimumOrderTotal()`), the checkout
button (`CartBox::hasMinimumOrder()` → `CartManager::
cartTotalIsBelowMinimumOrder()` → `Location::checkMinimumOrderTotal()`), the
server-side checkout security check in the theme's `Checkout` component
(the same `cartTotalIsBelowMinimumOrder()` and the message's
`minimumOrderTotal()`), and the deprecated `minimumOrder()` /
`checkMinimumOrder()` wrappers. `CoveredArea::minimumOrderTotal()` had a
single caller, the vendor Delivery class now replaced; nothing else derives
a minimum from the rules. The API reads no minimum, covered area, or
delivery amount; mail templates, the admin order detail, the Order model,
and the theme's JavaScript read none. The information panel prints the raw
rule labels, which is not a minimum and is unchanged. Two look-alikes are
different concepts and untouched: a payment method's own `order_total`
threshold (`alert_min_order_total` in payregister and `OrderManager`) and a
coupon's `min_total`. No path bypasses the override; no known divergence.

### Locale-adjacent findings have three separate causes

Three symptoms look like one problem and are not. Treating them as one would
mean fixing two of them wrongly.

**The locale is working.** `Igniter\Flame\Translation\Localization` sets the
application locale and `Carbon::setLocale()` from the same value, and the
weekday shift is proof that Carbon received a Canadian locale. Nothing here is
caused by the locale failing to apply.

| Symptom | Cause | Fixed by |
| --- | --- | --- |
| Delivery closed on Friday | Locale working, and `startOfWeek()` moving with it | `App\Delivery\WeekdayScheduleCorrection` |
| English text on a French page | Missing French strings, which is Q-001 | Importing or writing the translations |
| Money shown as `$13.99` rather than `13,99 $` | Currency configuration, not locale | A currency or formatter decision |

On the second: the message key `alert_outside_hours` exists only in the
package's `lang/en/default.php`. The project already carries local overrides
under `lang/vendor/`, including `igniter-cart/fr_CA/default.php`, but this key
is not among them, so it falls back to English. The French word in the middle of
the English sentence is the order type name, which comes from stored
configuration the owner entered in French rather than from a language file.

On the third: `config/currency.php` in the package sets `formatter` to null and
the project publishes no override, so amounts are formatted by `number_format()`
using the separators held on the currency record. The locale-driven `PHPIntl`
formatter exists but is not in use. Changing translations would not change how
money reads.

Money handling stays on the acceptance list, but for its own reason: the
CA$5.00 fee, the CA$80.00 free threshold, and the CA$20.00 minimum are compared
numerically somewhere, and that comparison is worth confirming regardless of how
the amounts are displayed.

### Mail: the config file governs, the admin Mail settings are inert, and test revisions redirect

Read-only investigation of 2026-08-22, recorded because the facts decide how
mail can be tested without touching the live site.

**Which configuration sends mail.** `config/mail.php` reads the transport and
credentials from `MAIL_*` environment variables. The only code that would
write the admin's Mail settings from the database into that configuration is
`Igniter\System\Classes\MailManager::applyMailerConfigValues()`, and it is
called only when `Igniter::usingMailerConfigFile()` is false, while
`Igniter\System\Providers\MailServiceProvider::boot()` calls
`Igniter::useMailerConfigFile()` unconditionally and nothing ever clears the
flag. So the database Mail settings are inert in the installed version; the
admin page itself shows a banner saying changes there do not take effect.
What the database still decides is *who receives*: the order, reservation,
and registration recipient toggles, the site address, and each location's
address, all shared by every revision.

**Consequence.** Environment variables are per revision, so one 0%-traffic
revision can carry `MAIL_MAILER=smtp` with real credentials while the
revision serving traffic keeps `MAIL_MAILER=log`. Mail is queued on the
`sync` connection, so a transport failure surfaces inside the request that
sent it, which is one more reason to prove the transport on a copy first.

**Test redirect.** `MAIL_TEST_REDIRECT_TO=<address>` on a revision makes
every message that revision sends go to that one address: To replaced, Cc and
Bcc dropped, through Laravel's global to-address that every resolved mailer
applies as `Mailer::alwaysTo()`. `App\Mail\MailTestRedirect` applies it from
`config('mail.test_redirect_to')` during provider registration; the value
goes through config because the runtime caches configuration. An invalid
value refuses to boot rather than send to real recipients. Covered by
`tests/Feature/Mail/MailTestRedirectTest.php`. Without it, a test revision's
alerts would reach the owner's real inbox, because the recipients come from
the shared database.

**Rules agreed on 2026-08-23.**

- No revision carries `MAIL_MAILER=smtp` until the redirect is merged.
- Credentials live in Secret Manager under the three names the runtime
  document reserved, `tastyigniter-mail-host`, `tastyigniter-mail-username`,
  `tastyigniter-mail-password`; the user pastes the values in the console;
  no value appears in a command line, a log, or a conversation.
- A mail test revision is deployed with `--no-traffic` and a tag, with
  `APP_URL`, `ASSET_URL`, and `CLOUD_RUN_SERVICE_URL` pinned to the tag (the
  order-view link in the mail is built from `APP_URL`), and FP-1 is taken on
  the main-traffic revision before and after.
- Verification: a synthetic pickup order on that revision, the four resulting
  messages read in the test inbox, and the log checked for recipient leakage.
  Waits for the user's Brevo domain verification and credential placement.
- A boot-time assertion that production must use a real transport and no
  redirect is deferred to launch, when `APP_ENV` for production is decided.

**First run, 2026-08-23.** Revision `le-chateau-canada-staging-mail-3a603e53`
was deployed at 0% traffic under tag `mail-3a603e53`, image built from
`4.x` at `3a603e53` (the redirect merged), URLs pinned to the tag,
`MAIL_MAILER=smtp` with the three secrets by reference, the redirect set,
`DELIVERY_ENABLED=false`. FP-1 on the main revision was identical before and
after. A synthetic pickup order was placed on it. The send failed at SMTP
authentication: the provider answered `525 5.7.1 Unauthorized IP address`
to every authenticator, which is the provider refusing Cloud Run's egress
address under its authorized-IP restriction; no credential fixes that, and
Cloud Run has no fixed egress address without a VPC connector and Cloud NAT.
Nothing was delivered anywhere, so nothing reached a real inbox. Three
findings from that one attempt:

- The order row is written, and marked processed, before the mail event
  fires, and mail goes out on the sync queue. A transport failure therefore
  lands on the customer after the order exists: the checkout page shows an
  error instead of the success page while the order is already in the
  admin. A retry in the same session redirects to that order rather than
  duplicating it.
- The theme shows the raw transport exception on the checkout page. That
  text included the SMTP username. It did not reach the log, which carried
  no recipient and no credential identifier, but the customer-facing page
  is a worse place. Production needs either a queue with a worker, so that
  transport failures never run inside a customer request, or a project-side
  catch in the mail path, and in any case no raw transport text on a page.
- The `MAIL_TEST_REDIRECT_TO` mechanism was not exercised end to end, because
  authentication failed before the envelope mattered; its tests cover it,
  the live proof waits for a successful send.

**Second run, 2026-08-23 13:12.** The user found the cause on the provider
side: the SMTP key's authorized-IP restriction had been activated with an
empty allow-list, and deactivated it. A second synthetic pickup order on the
same revision went through to the success page as order #7 ("Received"), so
the customer confirmation and the two alerts were handed to the provider
inside the request without error, every one addressed to the test inbox by
the redirect. The revision log for the window carried no e-mail address of
any kind, no credential identifier, and no error. **Rehearsal result, read by the user.** Three messages reached the test
inbox, all in the inbox rather than spam, sender name and address correct:
the order confirmation and one order alert at 13:12, and "Your Order Update -
7" at 13:42 after the user set order #7 to Preparation with notify from the
admin. The owner's real inbox received nothing. The provider's transactional
log, which is authoritative, shows exactly two messages at 13:12, so one of
the two alerts was never handed over: in
`ti-ext-cart/src/Extension.php:403-405` the confirmation and the two alerts
are three independent `mailSend()` calls, each building its own recipients
from `setting('order_email')` and the stored addresses
(`Order::mailGetRecipients()`), and `SendsMailTemplate::mailSend()` sends
nothing, silently and without a log line, when the list is empty. The user
read the admin directly: the order-notification setting has customer and
admin ticked and **location unticked**, and the site e-mail and the
location's e-mail are the same address, the owner's. So the missing alert
is the location copy, by configuration, and it is **not a defect**: with one
location and both addresses identical, leaving location unticked is what
prevents the owner receiving every alert twice. Nothing was changed. **If
the location is ever given its own mailbox, come back and tick "To
location"** in the order-notification setting, or its alerts will not be
sent.

**Cleanup, 2026-08-23.** Both synthetic orders were removed on the user's
explicit authorization through a disposable same-image Cloud Run Job
(`qa-cleanup-orders-6-7`) built from the mail revision's spec with the
secrets as references and `MAIL_MAILER=log`. A read-back execution first
listed exactly orders #6 and #7 with their synthetic fields and child-row
counts; the delete execution refused unless exactly two orders with the
synthetic last name and test note were found, then removed, inside one
transaction, 2 menu rows, 4 totals, 3 status-history rows, 0 payment logs,
0 stock-history rows, and the 2 orders; read-back after: 0 remaining, total
orders 6 to 4, so orders #2 to #5 were untouched. The revision's log for the
hour carried no address and no credential identifier. The job's own log
carries the synthetic test identity it printed, nothing real. The job
resource was deleted on the user's confirmation and read back absent; its
two execution logs remain under the platform's retention.

**Rehearsal closed, 2026-08-23.** Three of the four message types delivered
and read; the fourth traced to a deliberate, correct configuration; the
redirect proven end to end; the synthetic orders removed; the logs clean.

**Still open on mail.** Every shipped template is English with a single
body per template and no per-recipient language; a French customer receives
English mail. The rehearsal supplied the evidence: the three messages read
by the user were English or mixed, "You just received a Cueillette order",
"Sous-total", "Your Order Update". That is a production-gate item under the
French-language requirements above, not a defect in this change.

### Nominatim production gate

Public Nominatim is not approved for production Delivery traffic for the
identity, attribution, shared rate-limit, fallback fan-out, and autocomplete
reasons documented above.

**What satisfying this gate now requires, established 2026-08-29.** The
main-traffic revision `d2fix-31821289` sets no geocoder environment variable,
and its image was built at commit `31821289` (2026-07-19), five weeks before
`App\Delivery\GeocoderChainOverride` existed (#86, 2026-08-21). It therefore
runs the stored `chain` setting and **would reach Nominatim the moment Delivery
is enabled on it**. It cannot be pinned by setting a variable, because nothing
in that image reads one. Closing this gate needs either the deployment that
carries #86 or a change to the shared stored setting - it is not a
configuration flip. Delivery being closed today is what keeps the exposure
latent rather than live. Detail in `DEPLOYMENT_IMPACT_TIMEZONE_FIX.md`
section 7, and the per-copy picture in `D3C_PROGRESS.md`.

**Where this can be observed before launch.** `d3c-a2ee559c` runs the same image
digest as main traffic and differs from it in exactly one variable -
`DELIVERY_ENABLED=true` against `false` - with no geocoder variable on either. It
is therefore main traffic with Delivery already open, and the gate's condition
can be watched there on 0% traffic instead of being reasoned about. It shares the
live database, so read-only. That copy is held out of the test-copy cleanup until
the deployment rehearsal; see "The main-traffic twin" in `D3C_PROGRESS.md`.

#### Closing this gate takes two steps, and the second is the one that gets lost

Deploying a current image gives the production revision the **capability** to
pin the chain. It does not pin it. `App\Delivery\GeocoderChainOverride` returns
early when the environment says nothing, and **the production revision has never
carried `DELIVERY_GEOCODER_DRIVER` or `DELIVERY_GEOCODER_PROVIDERS`** - the
2026-08-29 read-back confirms neither is set. So a deployed production revision
with no variables still runs the stored `chain` and still reaches Nominatim.

1. Deploy an image containing #86, **setting
   `DELIVERY_GEOCODER_DRIVER` and `DELIVERY_GEOCODER_PROVIDERS` in the same
   deploy command**. Cloud Run revisions are immutable, so the variables cannot
   be added to a serving revision afterwards without deploying again and cutting
   traffic a second time - which would leave a window where the live site runs
   the new image unpinned.
2. **Read those two variables back off the new revision before cutting traffic
   to it.** A successful image deploy is not evidence that they are set.

Step 2 is at risk precisely because step 1 gets described as "the deployment
solves the geocoder". It does not. This is the same failure shape recorded for
the week-start fix, where a merged and deployed correction was explicitly noted
as *not* releasing the hours change that depended on it (`D3C_PROGRESS.md`,
"This Done does not release the hours change two rows below"). Marking the
deployment Done must not be read as releasing this gate.

#### An alternative that takes one step and no deployment

Change the shared stored setting `default_geocoder` from `chain` to `google`.
TastyIgniter reads it into `igniter-geocoder.default` on every geocoder
resolution (`System/ServiceProvider.php`), so it takes effect on **every
revision at once, including today's production image**, with nothing deployed.
Checked for side effects: the front-end map library is chosen by that same
stored value - Leaflet only when it reads `nominatim`, Google Maps JS otherwise
- and both `chain` and `google` take the same branch, so the map assets do not
change.

Cost of each path, one line each:

- **Deploy then set variables:** two steps, no shared-setting write, and the
  live site is unaffected until the deployment itself; the risk is step 2 being
  dropped.
- **Change the stored setting:** one step and no deployment, but it is a
  shared-settings write that takes effect on the live site immediately, so it
  needs its own approval, the prior value recorded, a read-back after, and a
  stated way back.

Neither is executed. At launch this is a **choice between two paths**, not a
side effect of deploying.

### Continuous integration: live since 2026-08-29

The pipeline (PR #122) runs on every push and pull request: PHP 8.3, 8.4,
and 8.5 in a matrix, extensions aligned to `Dockerfile.cloudrun`,
tastyigniter/* installed from source (the registry dist checksums fail on
fresh machines, below), a health-checked throwaway MySQL 8.4 service,
`igniter:up`, then the full suite. The first fully green run was
33223931822 (2026-08-29, 209 tests, 925 assertions, zero errors, zero
failures on every row), reached through PR #124 (the fresh-install
migration-ordering fix) and PR #126 (two test-expectation fixes).
Mainline's own first run, 33224362271 on the #122 merge commit, is green.

**The merge gate: a code PR merges only on a green check.** A red run, or
checks that have not finished, block the merge — the section 20 stop
condition on blocking CI findings is now live. Docs-only PRs remain
exempt, per section 15. The old reading — "CI has never run, an empty
check list means untested" — is retired; it described 2026-08-28 and
earlier, when the workflows reported `active` with zero runs ever and the
committed pipeline contained no test step at all.

The road there is in `CHANGELOG_AI.md` (2026-08-20 through 2026-08-29)
and `CI_ENABLEMENT_PLAN.md`: the 82 container-run errors of 2026-08-21
decomposed into driver/environment noise, schema absence, and the
migration-ordering defect, each solved in turn across five Actions runs.

One durable finding from that road outlives it: **a fresh machine cannot
install the locked dependencies from the tastyigniter.com registry's
dists — the zips fail the lock file's checksum verification** (seen
identically on all three PHP rows for `ti-ext-local`, `ti-ext-user`,
`ti-ext-reservation`, `ti-ext-payregister`, `ti-ext-socialite`, and
others). `Dockerfile.cloudrun` has been masking exactly this for image
builds with `composer config "preferred-install.tastyigniter/*" source`,
and the CI workflow now carries the same setting. Any other fresh
environment — a new workstation, a rebuild — needs it too, until the
registry and the lock file agree again.

### Log redaction does not reach inside a Throwable

`App\Logging\RedactUrlProcessor` strips absolute URLs from a log record's
message, context strings, and extra strings. Monolog normalizes a Throwable
placed in log context at format time, which runs after processors, so a URL
inside an exception object passed as context is not covered.

Project-owned code does not pass exceptions into log context; see
`App\Livewire\DeliveryLocalSearch`, which catches provider failures without
binding the exception. The gap therefore matters only for code that logs an
exception object directly, which in practice means Laravel's own exception
handler. It is asserted in `tests/Unit/Logging/RedactUrlProcessorTest.php` and
again through the real channel in
`tests/Feature/Logging/RedactUrlChannelTest.php`, so it stays visible rather
than being assumed closed. Closing it would need formatter-level redaction,
which is a larger change and a separate decision.

The gap is demonstrated, not inferred. Running a record with an exception in
context through the configured `stderr` handler and its real `LineFormatter`
leaves the URL intact inside the serialized exception, and the same output also
carries the PHP file path and a stack trace. Section 20 lists a PHP path as a
leak class of its own, so an exception reaching the log costs more than the
URL.

### Where geocoder provider errors actually reach the log

Worth recording, because it is not where it looks. `AbstractProvider::log()`
in `tastyigniter/core` does not write to Laravel at all; it appends to an
in-memory array read back through `getLogs()`. The entry that appears as
`staging.ERROR: Provider "…" could not geocode address, "…"` is written by
`Igniter\Orange\Livewire\Concerns\SearchesNearby`, which calls
`Log::error(implode(PHP_EOL, Geocoder::getLogs()))`.

That places the provider's raw text in the record **message**, not in context,
which is why `RedactUrlProcessor` covers the leak observed on Canada staging.
`App\Livewire\DeliveryLocalSearch` uses that concern, so the path is live for
Delivery address lookups.

The test asserts that the gap exists. It cannot stop new code from introducing
a Throwable into log context, which is the way the gap would actually start to
matter. A static check that fails when project-owned code passes a Throwable
into a logging call would close that route. Recorded as a deferred task, not a
blocker.

## 11. Production gates

Production work has not started and production is unchanged. Before any
production launch, separately approve and verify at least:

- Delivery provider operating model and Nominatim replacement/approval.
- Full D3C isolated acceptance and an explicitly approved staging cutover.
- Business decisions still marked pending in
  `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md` where they affect launch behavior.
- Quebec tax enablement per `QUEBEC_TAX_PLAN.md` (2026-08-24): GST 5% plus
  QST 9.975% on the same pre-tax base per the official pages read that day;
  the vendor's single integer-cast rate cannot express QST, so the
  implementation is project-owned; enabling tax changes live Pickup totals
  and is separately approved with owner-agreed timing; the restaurant
  WEB-SRM mandatory-billing question is open for the accountant.
- **Timezone integrity on a cold-started production instance.** The
  timezone fail-open recorded in section 10 affects the live path of
  every revision, production included: an instance that cannot read the
  stored setting at boot keeps the configured fallback for its whole
  life, and the storefront then shows the shop closed while it is open,
  or open while it is closed, with every stored value correct. The fix
  makes the fallback correct and audible, but the check belongs on this
  list because it is a property of a running instance, not of the code:
  **after a forced cold start, read the storefront's open/closed state
  against the wall clock**, at an hour where Montreal and UTC fall on
  opposite sides of a boundary — before noon, or after 20:00 local — so
  the two answers are distinguishable. Also confirm no
  "Timezone setting unavailable at boot" warning is in the log for that
  instance.
- Payment, webhook, refund, authentication, email verification, and outbound
  notification architecture.
- Real domain/DNS, TLS, production environment variables, backups, restore
  rehearsal, monitoring, and rollback.
- Fresh-install migration ordering and migration safety.

Never experiment directly in production.

### French-language requirements: consultation answered 2026-08-23

Quebec has statutory French-language requirements for commercial websites, under
the Charter of the French Language as amended by Bill 96. The Canada staging
storefront currently shows English text to customers: the checkout refusal
message reads "Your selected order time is outside our Cueillette hours", and it
is not the only string affected, because the package translations were never
imported. That is Q-001, still open, translation count 0 of 2992.

The owner sought professional advice, and on 2026-08-23 the user relayed the
conclusions. They are recorded here as received; this document still adds no
legal interpretation of its own, and the conclusions are the owner's to apply:

- **The default language is French, and the French experience is pure
  French** — no English mixed into pages served in the default language.
- **French coverage must be complete** on the customer-facing surfaces. The
  English strings falling out of Q-001, and the English-only mail templates
  recorded in section 10, are therefore build items, not accepted state.
- **An English language switch stays.** The site is bilingual with French as
  the default, so the storefront needs a visible language switch entry point.
  Q-002 — the storefront has no visible language switcher — is upgraded from
  an open question to a required build item.

The implementation planning that follows from these answers (Q-001 import
route, the Q-002 switcher, mail templates, and the currency-format question
considered alongside them) is the language-and-localization workstream the
user calls 板块二; its plan is `LOCALIZATION_WORKSTREAM_PLAN.md`
(2026-08-24), and planning precedes any implementation. Two facts verified
on 2026-08-24 reshape Q-002: the current theme already renders
Français | English header links (`/language/{locale}`), and switching to
`en_CA` does not take effect — the page stays French, consistent with
Q-005's fallback record; the switcher work is therefore verify-and-fix, not
build-from-scratch. Questions the relayed conclusions did not address —
whether staging is in scope before launch, and what applies to the
staff-facing admin interface — keep their previous status: unsettled, and
not to be assumed.

### Upgrading to a paid account must set the Geocoding quota in the same act

The Geocoding daily usage cap of 500 requests, confirmed on 2026-08-20, is not
in place. A free-trial billing account cannot edit quota overrides, so the
value is recorded but unenforced.

What protects the project today is the trial itself. Trial usage draws down the
trial credit, and producing a real bill requires manually upgrading the
account. That protection ends at the instant of upgrade, and the quota becomes
settable at that same instant.

Set the cap **inside the same operation as the upgrade**. Not afterwards, not
as a follow-up task, not in the same week. Between upgrading and setting the
cap there is no ceiling on Geocoding spend, and the failure the cap exists for
is a looping defect that spends fast.

The quota lives under `APIs & Services > Geocoding API > Quotas & System
Limits`, on the requests-per-day limit for the Canada staging project.

### The free trial has an expiry that can delete Canada staging

Google states that resources created under a free trial may be removed, and not
recovered, if the trial ends without an upgrade to a paid account.

Everything in Canada staging sits in that project: the Cloud Run service and
every revision, the Cloud SQL instance with its configuration and data, the
Artifact Registry images, the media bucket, and the Secret Manager entries. The
Render and DigitalOcean fallbacks are outside it, but they do not carry the
Canada staging state.

This is an operational risk with a date on it, not a background concern. The
upgrade has to happen before that date whether or not launch is ready, and the
date belongs in the launch schedule rather than in someone's memory.

Read from the console on 2026-08-21. The billing account currency is CAD.

| Item | Value |
| --- | --- |
| Trial credit granted | CA$425.27 |
| Trial credit remaining | CA$204.13, 48% |
| Trial period | 2026-07-09 to 2026-10-08 |
| Days remaining at readback | 48 |
| Gross cost, 2026-08-01 to 2026-08-21 | CA$103 |
| Net cost for that period | CA$0.00, fully offset by credit and free tier |

Neither figure is exposed by the Cloud Billing API; `gcloud billing accounts
describe` returns only currency, display name, and open state. Both come from
`Billing > Credits` in the console.

**The effective deadline is earlier than 2026-10-08.** The console states that
the trial ends at whichever comes first, the credit being spent or the period
ending, and that it can be neither paused nor extended. August ran at roughly
CA$5.00 a day gross, so CA$204.13 covers about 41 more days and lands near
2026-10-01, about a week before the period ends.

That date is an estimate from an observed rate, not a date Google has given.
Treat 2026-10-01 as the working deadline, and re-read the balance rather than
trusting the projection: the rate will move as D3C and later phases add usage.

When the trial ends the console states that every resource created during it
stops. That is the Cloud Run service, the Cloud SQL instance and its data, and
everything else listed above.

**Schedule the upgrade for mid-September 2026**, not the week before the
deadline. Four things are done in the same sitting:

1. upgrade the billing account to paid;
2. set the Geocoding daily cap to 500 requests;
3. create the budget alert;
4. split the browser API key from the server API key.

They are one operation because their risks all become real at the same instant.
Upgrading ends the trial's spending ceiling. A public launch points the exposed
browser key at the open internet. A paid account means a real invoice rather
than a credit balance. Doing them on separate days leaves a window in which the
protection of one has ended and the protection meant to replace it does not yet
exist.

The budget alert does not exist yet. Until the account is upgraded it is the
only spend alarm available, because a trial account cannot set a quota
override.

### The Maps key is public and unrestricted

`Igniter\Orange\Livewire\Concerns\SearchesNearby` exposes `$mapKey` as a
public Livewire property, taken from `setting('maps_api_key')`. The same
setting supplies the server-side geocoder's `apiKey`, so one key serves both a
browser context and a server context. The key carries no HTTP referrer or IP
restriction, and its API allow-list includes Geocoding, which is billable.

**This is pre-existing, not a D3C regression.** The revision serving main
traffic exposes the key with `DELIVERY_ENABLED=false`, and always has. Do not
record it as something D3C introduced.

Removing Places from the key's allow-list was considered and rejected as
useless: neither `places.googleapis.com` nor `places-backend.googleapis.com` is
enabled on the project, so Places calls already fail for any holder of the key.
Project-level API enablement, not the allow-list, is the operative control. The
live exposure is Geocoding, which is enabled and is required.

The fix is two keys: a browser key restricted by referrer and limited to Maps
JavaScript, and a server key that is never rendered. TastyIgniter supplies both
from one setting, so separating them needs a project-owned change. It is item 4
of the upgrade above.

## 12. Fallback infrastructure

Render staging and its DigitalOcean database remain operational fallbacks. Do
not delete or reconfigure them during D3C.

Retirement conditions:

1. The replacement production platform is stable for 7-14 days.
2. Backup and restore have been verified.
3. Application dependencies on the fallback are removed and documented.
4. Retire the fallback application first.
5. Retire the fallback database last and only with explicit destructive
   approval.

Do not delete Cloud SQL, Cloud Storage, Secret Manager resources, retained
rollback revisions, historical logs, Render, or DigitalOcean as incidental
cleanup.

## 13. New agent workflow

The project now uses the risk-based autonomous review workflow in
`AGENT_WORKFLOW.md`:

`Implement -> Verify -> Review A -> Adversarial Review B -> fix findings ->`
`final verification -> Ready PR -> merge gate -> post-merge continuation`

Routine docs, UI, focused fixes, and isolated staging validation no longer
default to a second agent review. Independent review remains appropriate for
payment, authentication/authorization, privacy architecture, destructive
migrations, money/availability races, production incidents, major refactors,
security boundaries, or when the user requests it.

## 14. Risk levels

- Level 0: read-only, audit, planning, runtime readback, and documentation.
- Level 1: isolated project-owned code with no schema/auth/payment/production
  effect.
- Level 2: non-production runtime/configuration and isolated staging work.
- Level 3: production, public traffic, real payment/mail, auth/security,
  destructive schema/data, account/permission, secret, or infrastructure
  deletion.

Classify every task before acting. Level 3 always stops for explicit approval.

## 15. Merge rules

- Create Ready PRs only after no blocking self-review finding remains.
- Level 0 docs-only PRs may be auto-merged under the standing authorization the
  user granted on 2026-08-20. That authorization applies only when **all** of
  the following hold:
  1. every changed path ends in `.md`;
  2. the change records decisions, findings, or state the user already
     confirmed, and introduces no new business, pricing, tax, schedule, or
     policy value the user has not confirmed;
  3. Review A and Review B leave no blocking finding;
  4. `git diff --check` and the sensitive-data scan are clean.
  A PR is still created, and the merge is reported to the user with the PR
  number and what changed.
  The authorization does **not** cover any non-`.md` path (including
  `.env.example`, configuration, code, and CI/workflow files), any change to
  the risk, merge, destructive, or secret rules, any change to the
  authorization itself, or any Level 1/2/3 work. Those still stop for
  confirmation.
- Level 1 normally stops once at `Ready to merge` for user confirmation.
- Level 2 may combine an approved merge and isolated staging execution, but
  main-traffic cutover still requires separate approval.
- Level 3 uses an explicit approval at every high-risk gate.
- After an approved merge, sync `4.x`, verify the merge SHA and clean worktree,
  update phase status, and continue the next already-approved low/medium-risk
  step without asking an empty “continue?” question.

## 16. Destructive-operation rules

Every deletion or difficult-to-recover action requires explicit confirmation,
including Cloud Run Jobs/revisions, database rows/databases, logs/data,
fallback infrastructure, or cloud resources.

Before asking, list exact resource names and intended scope. Never use wildcard,
broad, or inferred deletion targets. Re-read exact resource state before and
after deletion, then report recoverability.

## 17. Secret handling

The agent performs all non-sensitive automation. If a password, payment
credential, API secret, private key, token, or verification code is required,
the user enters it through the provider UI, Secret Manager, or another secure
prompt. Never ask the user to paste it into chat.

Record only `configured`, `not configured`, `validated`, or `failed` plus a
non-sensitive error category. Do not display, copy, cache, commit, screenshot,
or log the value.

## 18. Exact next phase

The handoff PR is merged and the baseline is recorded in section 2, so this
phase is unblocked. Begin:

`Delivery D3C isolated enablement`

Create a Canada staging revision whose only intended gate change is
`DELIVERY_ENABLED=true`, route 0% main traffic, and expose a tagged URL. Do not
modify the current 100%-traffic revision, cut over traffic, modify production,
or start Birthday/payment work.

Before the first D3C action, record FP-1 for the revision holding main traffic.
Record it again after the last one. Section 19 states what the pair proves.

## 19. D3C acceptance criteria

Use synthetic data and verify at minimum:

- health, assets, runtime/config fingerprint, database connectivity, and clean
  logs on the tagged revision;
- FP-1 for the main-traffic revision, captured before the first D3C action and
  recomputed after the last, identical across the pair;
- polygon inside, outside, and inclusive boundary behavior;
- CAD 5.00 fee below the free threshold;
- free-threshold and minimum edges at the values in the decision table of
  `DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md` — from 2026-08-23 those are free at
  or above CAD 50.00 with no Delivery minimum; the earlier 80/20 pair is
  exercised once more only in the one-time defect-contrast reading recorded
  in `D3C_PROGRESS.md`, taken before the new values are written;
- the Delivery schedule as the decision table holds it (every day
  12:00-21:00 since 2026-08-28; Monday-Friday with weekends closed before
  that);
- unrecognized/incomplete/out-of-area address behavior and address changes;
- Pickup to Delivery and Delivery to Pickup transitions;
- stale session cleanup, cart preservation, totals, spoof resistance, and API
  policy;
- desktop, 390px mobile, keyboard/accessibility, and browser console;
- geocoder/public/Livewire/log privacy with no address, URL, credential,
  geometry, SQL, ID, or raw exception leak;
- Pickup, Birthday, Reservation, Admin, media, and health regressions;
- no real Order, Customer, Reservation, Birthday, payment, or notification;
- exact cleanup of synthetic data and disposable resources after approval.

The FP-1 pair is the evidence that D3C never touched the live path. D3C only
creates a 0%-traffic tagged revision, so the revision serving main traffic must
be identical in configuration at both ends. Run `tools/fp1.py` before starting
and again after finishing, and record both results in
`CLOUD_RUN_CANADA_STAGING_RUNTIME.md`.

An identical pair is the accepted evidence. "No change was intended" is not
evidence and does not substitute for it. If the pair differs, stop and use the
per-field digest table to name the field that moved before doing anything else.
A changed `revision` or `traffic_percent` means traffic moved, which is outside
D3C scope and needs the separate cutover gate.

FP-1 refuses to run while traffic is split across revisions, so a split part way
through D3C surfaces as an error instead of a silent pass.

Only after every item passes may the agent request explicit user approval for
a Canada staging main-traffic cutover.

## 20. Stop conditions

Stop and request direction if any of these occurs:

- the frozen baseline in section 2 cannot be verified, or `HEAD` does not
  contain it;
- the worktree is dirty with overlapping user changes;
- runtime readback differs materially from this freeze and the change is not
  already documented;
- a secret or private account input is required;
- production, public traffic, real mail/payment, auth/security, destructive
  data/schema, permissions, or infrastructure deletion enters scope;
- a full address, provider URL, credential, geometry, SQL/internal ID, or PII
  appears in logs or public output;
- tests, CI, rollback checks, or self-review retain a blocking finding;
- a new business decision would materially change Delivery, Birthday, payment,
  tax, or fallback behavior.

Delivery D3C isolated enablement is the current phase. It starts from the
frozen handoff baseline recorded in section 2.
