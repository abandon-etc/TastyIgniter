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
| Hours | Monday-Friday 12:00-21:00 |
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

### Fresh-install migration ordering

The root Birthday reservation migration can run before the Reservation
extension creates `ti_reservations`. The already-initialized Canada staging
database is not affected. Fix this in a separate focused PR; do not hide it
inside Delivery or payment work.

### Nominatim production gate

Public Nominatim is not approved for production Delivery traffic for the
identity, attribution, shared rate-limit, fallback fan-out, and autocomplete
reasons documented above.

### Continuous integration has never run

`.github/workflows/pipeline.yml` and `release.yml` are both reported `active`
by `gh workflow list`, but `gh run list` returns zero runs for this repository.
CI has never executed here, on any branch or pull request, including PRs #74,
#75, and #76. The most likely cause is that Actions is disabled for this fork.

The practical consequence is a reading trap. An empty check list on a pull
request page means **untested**, not **passing**. Section 15 already forbids
merging merely because checks are absent, and this is the condition that rule
exists for. The section 20 stop condition covering a blocking CI finding is
inert for the same reason: CI produces no findings at all.

This is a decision point, not a defect to fix quietly. After D3C, and before
any substantive code work such as Birthday checkout, payment, webhooks, or
authentication, decide explicitly whether to enable CI. Documentation-only
changes can proceed without it; code changes should not, indefinitely.

Expect enabling it to fail broadly at first. `vendor/` has never been installed
in this working copy, the PHP suite's current state against the PHP 8.3, 8.4,
and 8.5 matrix is unknown, and that matrix has never been exercised. Such a
failure would be pre-existing state becoming visible, not a regression
introduced by enabling CI. Budget a triage pass for it rather than treating the
first red run as a blocker on whichever change happens to trigger it.

The suite can be run in a container today, which changes the shape of this
decision. On 2026-08-20 a container running PHP 8.3.32 and PHPUnit 11.5.56
executed a project test file successfully, with no local PHP toolchain and no
CI. Whether enabling CI would fail broadly is therefore measurable in advance
rather than a bet: run the suite in a container, triage what it reports, then
decide. What blocked verification was tooling, not the code.

### Log redaction does not reach inside a Throwable

`App\Logging\RedactUrlProcessor` strips absolute URLs from a log record's
message, context strings, and extra strings. Monolog normalizes a Throwable
placed in log context at format time, which runs after processors, so a URL
inside an exception object passed as context is not covered.

Project-owned code does not pass exceptions into log context; see
`App\Livewire\DeliveryLocalSearch`, which catches provider failures without
binding the exception. The gap therefore matters only for vendor code that logs
an exception object directly. It is asserted in
`tests/Unit/Logging/RedactUrlProcessorTest.php` so it stays visible rather than
being assumed closed. Closing it would need formatter-level redaction, which is
a larger change and a separate decision.

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
- Payment, webhook, refund, authentication, email verification, and outbound
  notification architecture.
- Real domain/DNS, TLS, production environment variables, backups, restore
  rehearsal, monitoring, and rollback.
- Fresh-install migration ordering and migration safety.

Never experiment directly in production.

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

The remaining credit and the expiry date are **not recorded here yet**.
Neither is exposed by the Cloud Billing API: `gcloud billing accounts
describe` returns only currency, display name, and open state. Both are
readable from `Billing > Credits` in the console for the billing account
linked to this project, whose currency is CAD. Record the actual values here
once read; the trial amount varies by currency and must not be assumed.

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
- CAD 80.00 free threshold and CAD 20.00 minimum at below/equal/above edges;
- Monday-Friday 12:00-21:00 and weekends closed;
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
