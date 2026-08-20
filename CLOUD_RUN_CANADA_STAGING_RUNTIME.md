# Cloud Run Canada Staging Runtime

Date: 2026-07-09

Environment: Canada staging only

Primary region: Montreal `northamerica-northeast1`

Fallback: Render staging remains active and unchanged

## Purpose

This document records the runtime plan for deploying the TastyIgniter staging
app to Google Cloud Run in Canada. It is intentionally limited to staging
runtime readiness. It does not create a Cloud Run service, does not change
production, and does not include any secret values.

## Current Google Cloud Resources

The following staging resources have been prepared in the Google Cloud project
`le-chateau-canada-staging`:

- Artifact Registry repository: `tastyigniter-staging`
- Artifact Registry region: `northamerica-northeast1`
- Cloud SQL instance: `le-chateau-staging-mysql`
- Cloud SQL engine: MySQL-compatible
- Cloud SQL region: `northamerica-northeast1`
- Cloud SQL staging database: `tastyigniter_staging`
- Cloud Storage bucket: `le-chateau-canada-staging-media`
- Cloud Storage bucket region: `northamerica-northeast1`
- Runtime service account:
  `cloud-run-staging-runtime@le-chateau-canada-staging.iam.gserviceaccount.com`

The runtime service account has:

- Project role: Cloud SQL Client
- Project role: Secret Manager Secret Accessor
- Bucket-level role on `le-chateau-canada-staging-media`: Storage Object Admin

No service account key has been created or downloaded.

## Runtime Entry Point

`Dockerfile.render` and `docker/render/start.sh` remain Render-specific and are
kept unchanged so Render staging can continue as the fallback environment.

Cloud Run uses a separate image entry point:

- `Dockerfile.cloudrun`
- `docker/cloudrun/start.sh`

The Cloud Run Dockerfile starts from the same PHP-FPM plus Nginx shape as the
Render image, publishes the same TastyIgniter assets, and reuses the existing
Nginx template. The separate start script keeps Cloud Run behavior isolated
from Render behavior.

Cloud Run must listen on the platform-provided `$PORT`. The image defaults to
`PORT=8080`, but the start script always honors the runtime `$PORT` value.

## Cloud SQL Connection

Cloud Run staging should use the Cloud Run Cloud SQL connector with a Unix
socket path. Google recommends keeping Cloud Run and Cloud SQL in the same
region to reduce latency, cost, and regional failure risk. The current target
keeps both in `northamerica-northeast1`.

Runtime mapping:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_SOCKET=/cloudsql/<INSTANCE_CONNECTION_NAME>
DB_DATABASE=<from Secret Manager>
DB_USERNAME=<from Secret Manager>
DB_PASSWORD=<from Secret Manager>
DB_PREFIX=<staging table prefix>
MYSQL_ATTR_INIT_COMMAND=SET SESSION sql_require_primary_key = OFF
```

The start script also supports this safer mapping:

```text
DB_INSTANCE_CONNECTION_NAME=<from Secret Manager>
DB_SOCKET=<empty>
```

When `DB_SOCKET` is empty and `DB_INSTANCE_CONNECTION_NAME` is present,
`docker/cloudrun/start.sh` sets:

```text
DB_SOCKET=/cloudsql/${DB_INSTANCE_CONNECTION_NAME}
```

Do not record the real instance connection name, username, or password in git,
PR descriptions, screenshots, logs, or chat.

## Secret Manager Mapping

Secret values must be entered by the user in Google Cloud Secret Manager or
Cloud Run secret bindings. The repository records only secret names and their
intended runtime environment variables.

Existing staging secrets:

| Secret name | Runtime variable | Purpose |
| --- | --- | --- |
| `tastyigniter-app-key` | `APP_KEY` | Laravel application encryption key |
| `tastyigniter-db-password` | `DB_PASSWORD` | Staging Cloud SQL database password |
| `tastyigniter-db-username` | `DB_USERNAME` | Staging Cloud SQL database user |
| `tastyigniter-db-database` | `DB_DATABASE` | Staging database name |
| `tastyigniter-db-connection-name` | `DB_INSTANCE_CONNECTION_NAME` | Cloud SQL connector instance name |
| `tastyigniter-media-bucket` | `MEDIA_BUCKET` | Staging media bucket name |

Recommended additional staging secrets or non-secret Cloud Run variables before
service creation:

| Name | Runtime variable | Purpose |
| --- | --- | --- |
| `tastyigniter-app-url` | `APP_URL` | Cloud Run staging URL after service creation |
| `tastyigniter-asset-url` | `ASSET_URL` | Usually the same as `APP_URL` for staging |
| `tastyigniter-mail-host` | `MAIL_HOST` | Placeholder only if mail testing is enabled later |
| `tastyigniter-mail-username` | `MAIL_USERNAME` | Placeholder only if mail testing is enabled later |
| `tastyigniter-mail-password` | `MAIL_PASSWORD` | Placeholder only if mail testing is enabled later |

No real mail credentials should be configured for this staging experiment unless
mail testing is explicitly approved later.

## Media Storage Strategy

The current application media disk is local and points at `public/media` in
`config/filesystems.php`.

Render currently preserves uploads by preparing these paths and symlinks:

```text
storage/app/media
storage/app/media/media/uploads
storage/app/media/media/attachments
storage/app/public/media -> storage/app/media/media
public/storage -> storage/app/public
public/media -> storage/app/media
```

For the first Cloud Run staging experiment, mount the Cloud Storage bucket
`le-chateau-canada-staging-media` at:

```text
/var/www/html/storage/app/media
```

This keeps the existing application media path intact:

```text
public/media -> storage/app/media
```

It also preserves the nested TastyIgniter media layout:

```text
storage/app/media/media/uploads
storage/app/media/media/attachments
```

The start script deliberately avoids recursively changing ownership or
permissions under `storage/app/media`, because that path may be a Cloud Storage
FUSE mount.

Cloud Storage FUSE constraints to validate before production:

- It is not a complete POSIX file system.
- It should not be treated as a strong concurrent write-locking store.
- Mount startup and metadata operations can affect cold start behavior.
- Read and write caching behavior must be tested with TastyIgniter thumbnails.
- Media Manager upload, media URL serving, redeploy persistence, and concurrent
  upload behavior must be validated in staging.

The current bucket role is Storage Object Admin for the runtime service account.
After staging upload behavior is proven, consider whether it can be reduced to a
less privileged object role. Do not change the role until that is separately
approved.

## Artifact Registry Image

Recommended image path pattern:

```text
northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:<git-sha>
```

Acceptable build and push options:

- Cloud Build builds `Dockerfile.cloudrun` and pushes to Artifact Registry.
- Local or CI Docker build pushes the same image to Artifact Registry.

Do not enable automatic production deploys. Do not overwrite or remove the
Render staging deployment.

## Cloud Run Service Creation Checklist

Before creating the Cloud Run service, confirm:

- Image has been built and pushed to Artifact Registry.
- Runtime service account is selected.
- Cloud SQL connection is attached to the service.
- Cloud Storage volume is mounted at `/var/www/html/storage/app/media`.
- Secret Manager values are bound to runtime environment variables.
- `$PORT` is provided by Cloud Run and the container listens on it.
- `/healthz` is available.
- `APP_ENV=staging`.
- `APP_DEBUG=false` unless a short diagnostic window is explicitly approved.
- `RUN_CONFIG_CACHE=true`.
- `RUN_ROUTE_CACHE=false`.
- `RUN_VIEW_CACHE=false`.
- No production secrets are bound.
- No real payment provider is configured.
- No production domain is connected.
- No real customer, order, reservation, menu, or payment data is imported.

## Deployment Acceptance Checklist

After the first Cloud Run staging deploy, validate:

- `/healthz`
- `/`
- `/default/menus`
- `/cart`
- `/default/reservation`
- `/admin/login`
- authenticated `/admin/dashboard`
- Livewire JavaScript URL
- frontend CSS and JavaScript
- admin CSS and JavaScript
- Media Manager upload with a test image only
- uploaded media URL returns 200
- uploaded media still exists after redeploy
- PDO new connection timing
- PDO same-connection `select 1` timing
- Laravel first-query timing
- Laravel same-connection `select 1` timing
- TTFB for `/`
- TTFB for `/default/menus`
- TTFB for `/cart`
- TTFB for `/default/reservation`
- TTFB for `/admin/login`
- TTFB for authenticated `/admin/dashboard`
- Cloud Run logs have no PHP fatal error
- Cloud Run logs have no Laravel exception
- Cloud Run logs have no 500 response burst
- Cloud Run logs have no storage permission error

Do not submit test orders or test reservations unless the user explicitly
approves a staging database write.

## Rollback And Fallback

Render staging remains the fallback environment.

Do not delete:

- Render staging service
- Render persistent disk
- DigitalOcean staging database
- Existing Render environment variables

Do not change:

- Production domain
- Production secrets
- Production database
- Payment configuration

If Canada staging fails validation, stop the migration path, keep Render staging
active, record the failure reason, and create a focused follow-up task.

## Next Gates

The next user-approved gates are:

1. Build and push the Cloud Run staging image.
2. Create the Cloud Run staging service.
3. Bind Cloud SQL, Secret Manager, and Cloud Storage volume mount.
4. Run the Cloud Run staging acceptance checklist.
5. Compare Canada staging DB latency and dynamic HTML TTFB against Render
   staging.

Each gate may create billable Google Cloud usage and requires user confirmation
before proceeding.

## References

- Google Cloud SQL from Cloud Run:
  https://docs.cloud.google.com/sql/docs/mysql/connect-run
- Google Cloud Run Cloud Storage volume mounts:
  https://docs.cloud.google.com/run/docs/configuring/services/cloud-storage-volume-mounts

## Cloud Storage FUSE Visibility Note

The mounted media path is not a full POSIX filesystem and does not support
all chmod/visibility operations used by Laravel's local filesystem adapter.
For the Cloud Run staging runtime only, set FILESYSTEM_SKIP_VISIBILITY=true
after the corresponding configuration change is deployed. The default remains
disabled so Render staging keeps its existing public-disk behavior.

## 2026-07-10 - Validation record

PR #40 was deployed from git SHA `44940004` to Cloud Run Canada staging.
Image: `northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:44940004`.
Revision `le-chateau-canada-staging-00009-tvs` serves 100% of traffic at
`https://le-chateau-canada-staging-j675sib2hq-nn.a.run.app` with
`FILESYSTEM_SKIP_VISIBILITY=true`; Render staging was not changed.

Observed results:

- Homepage, menus, cart, reservation, admin login, and Livewire returned HTTP
  200 after the redeploy.
- Test object `media/uploads/IMG_2484.png` remained present after redeploy,
  returned HTTP 200 with `image/png`, and was 109065 bytes. The image is not
  stored in git.
- The media response used `Cache-Control: public, max-age=604800`.
- Cloud Run logs showed normal GCSFuse mount activity and no new visibility,
  chmod, permission, cache-directory, Cloud SQL, PHP-FPM, Nginx, fatal,
  exception, or 500 errors.
- Warm HTTP TTFB was approximately 0.66s homepage, 0.60s menus, 0.60s cart,
  0.62s reservation, 0.39s admin login, and 0.34s Livewire JavaScript.
- `/healthz` remains a separate Cloud Run frontend 404. It requires a focused
  health-check routing task and is not included in the FUSE change.

The `staging-inspector` database user is retained for Canada staging
maintenance and read-only investigations. It is not the Cloud Run application
runtime account and is not a production credential. Its password remains only
in the user-managed secret store.

PDO new-connection, same-connection, Laravel first-query, and Laravel
reconnect timings were not inferred from the HTTP measurements. They remain
an explicit follow-up before final database architecture conclusions.

Canada staging core runtime readiness is achieved for the current smoke-test
scope. Full readiness remains pending the separate `/healthz` routing fix and
the explicit connection-latency sample.

## Health check routing decision

In the current Canada staging service, the exact `/healthz` request on the
canonical Cloud Run `run.app` URL was observed to return a Google frontend 404
before reaching the container. The container already has an exact `/healthz`
Nginx response for Render and local runtime, but a Laravel route cannot
override the observed Cloud Run frontend behavior.

The focused Cloud Run workaround is `/healthz/`, which is served directly by
Nginx as `200 ok` and will be used for the Cloud Run liveness probe. The
canonical public `/healthz` observation remains documented separately and is
not treated as an application failure. Render's existing `/healthz` path is
unchanged.

## 2026-07-10 - Health and database latency validation

PR #42 was deployed from git SHA `2796d2c6` as Cloud Run revision
`le-chateau-canada-staging-00010-fh9` in `northamerica-northeast1`. The revision
is Ready and serves 100% of traffic. The liveness probe uses `/healthz/`, which
returns HTTP 200 and is visible in Cloud Run request logs. Bare `/healthz`
remains a separate Google frontend 404 observation; Render's route is
unchanged.

The approved one-time read-only Job used the application database account and
the Cloud SQL connector, did not write data, and was deleted after sampling.
Results were:

| Measurement | Count | Average | P50 | Max |
| --- | ---: | ---: | ---: | ---: |
| PDO new connection | 8 | 140.85 ms | 16.81 ms | 349.62 ms |
| PDO same connection `select 1` | 40 | 2.23 ms | 2.20 ms | 2.60 ms |
| Laravel reconnect first `select 1` | 8 | 147.87 ms | 24.28 ms | 356.22 ms |
| Laravel same connection `select 1` | 40 | 4.33 ms | 4.32 ms | 5.11 ms |

Compared with the former Render averages, the approximate average reductions
are 57%, 97%, 77%, and 97%. These measurements are staging-only and do not
include SQL bindings or credentials. `staging-inspector` remains a separate
Canada staging maintenance account and is not the Cloud Run runtime account.

Core public/admin-login/media smoke checks passed. Authenticated dashboard
verification should be repeated before calling the entire Canada staging
acceptance checklist complete. Render staging remains the rollback fallback;
production was not changed.
## 2026-07-10 - Canada staging dashboard acceptance and reservation audit

Environment: Canada staging only. Status: Dashboard acceptance resolved;
reservation requirements audit pending business requirements.

- The authenticated dashboard session successfully opened the dashboard,
  Orders, Reservations, Categories, Menus, Media Manager, Settings, Extensions,
  and Staff members pages.
- The tested page assets returned HTTP 200 and no localhost or old Render URL
  was observed. No browser console errors or page-level 5xx responses were
  observed. The existing `Broadcast is not defined` warning is non-blocking.
- The `/healthz/` liveness path, Cloud Run revision, media persistence result,
  and read-only database latency result remain as documented in the previous
  validation. Render remains the fallback.
- Reservation inspection was read-only. The public form exposes date, guest
  count, and time selection before `Find Table`; it was not submitted. Current
  location settings expose reservation enablement, automatic table assignment,
  interval, stay time, guest limits, advance window, cancellation timeout,
  start-time behavior, and opening schedules. Global settings expose
  reservation email recipients and status mappings.
- No reservation, order, customer, menu, payment, or mail data was written.

Before production planning, obtain explicit business requirements for date and
time rules, table/capacity behavior, conflict handling, customer details and
notes, notifications, bilingual copy, and mobile validation. Any confirmed
change should be a small, reversible staging-only PR and must not modify core,
vendor, or unrelated order/payment/authentication logic.

## 2026-07-10 - Birthday reservation rules implementation

Environment: local build only. Status: Pending PR review and Canada staging
deployment.

- The feature flag `BIRTHDAY_BOOKING_RULES_ENABLED` defaults to false and must
  be set to true only on Canada staging after the PR is merged. Render remains
  false and unchanged.
- The Cloud Run image build includes an additive migration for the
  `birthday_booking` boolean field with default false, nullable
  `birthday_slot_code` and `birthday_slot_key` fields, plus the unique
  `(location_id, birthday_slot_key)` index on `reservations`.
- The custom Birthday page exposes only `12:00-16:00` and `16:00-20:00`, uses
  the venue timezone, validates the plus-2 through plus-60 window on the
  server, and reuses the existing reservation customer form/save path without
  entering payment, registration, or add-ons.
- The Birthday model guard only processes records explicitly marked with
  `birthday_booking`; non-Birthday reservations remain on the standard path.
  Status-only maintenance does not reapply the creation date window, and the
  unique slot conflict is returned as a readable validation error.
- Local validation passed PHP lint, Dockerfile.cloudrun build, config cache,
  and 7 Birthday rules tests/15 assertions. No Cloud Run deployment, migration,
  staging write, or real notification was performed.

After merge, Canada staging must be migrated only after confirming the target
database, then smoke-tested for occupied/free slots, cancellation release,
admin conflict rejection, concurrent duplicate prevention, and existing page
regressions. No production action is included.

## 2026-07-11 - Birthday reservation rules staging validation

Environment: Canada staging only. PR #48 was deployed from SHA `0a19c37f` as
revision `le-chateau-canada-staging-00014-2kd`; the existing runtime account,
Cloud SQL connector, Secret Manager bindings, Cloud Storage mount, and liveness
configuration were retained.

Service-side validation completed; end-to-end browser submission remains
pending because the telephone widget blocked the form.

- `/healthz/`, public pages, Livewire, admin login, dashboard, and the tested
  admin management pages loaded successfully.
- The Birthday form rendered only `12:00-16:00` and `16:00-20:00`, with the
  Toronto-local date window from two through sixty days ahead.
- Service-side QA verified slot occupancy, occupying/non-occupying status
  behavior, and cancellation release. The two-task concurrent execution
  produced one successful claim and one expected unique-conflict failure, with
  one final occupying row. The losing write followed the service-side conflict
  path; the browser-facing readable business validation message was not
  separately exercised. SQL bindings and index details were not exposed.
- PR #45 was closed as superseded by PR #46 and subsequent staging
  fixes/validation. Synthetic QA records were deleted.
- Temporary Birthday QA Jobs were deleted. Cloud Run logs had no new matching
  fatal, exception, 500, cache-directory, Cloud SQL, FUSE, or storage
  permission errors.

The browser telephone widget rejected the synthetic phone number before the
reservation form could be submitted. No browser-created reservation remains.
This is a separate UX/input follow-up. No real data, payment, mail, production
configuration, or destructive database operation was used. Render staging and
the DigitalOcean fallback remain available.

Next step: review the validation record PR, then decide whether the telephone
widget needs its own focused staging PR.

## 2026-07-12 - Birthday browser submission validation after PR #50

Environment: Canada staging only. Status: Resolved for the Birthday browser
validation gate.

- PR #50 image SHA `fceead8b` was deployed as revision
  `le-chateau-canada-staging-00015-vnj`. A follow-up staging-only configuration
  revision `le-chateau-canada-staging-00016-2tj` uses `MAIL_MAILER=log` so the
  synthetic test cannot send external mail.
- `/healthz/`, the homepage, menus, cart, reservation entry, admin login,
  Livewire JavaScript, and retained media passed HTTP smoke checks. The
  retained non-business media object `IMG_2484.png` remained available as
  `image/png` after the redeploy.
- One synthetic browser Birthday reservation reached the success page and was
  visible in the authenticated Reservations admin list as Pending. The
  selected slot became unavailable, cancellation released it, and the record
  was deleted through the admin UI after validation. No real data or payment
  operation was involved.
- The browser session had no error-level console entries. Cloud Run logs had
  no new fatal, exception, 500, Cloud SQL, cache-directory, FUSE, or storage
  permission error. Existing dashboard Broadcast/configuration warnings are
  non-blocking and unrelated to Birthday rules.
- The `/healthz/` liveness path remains `/healthz/`; the bare-path Google
  frontend behavior remains a separate known observation. Render staging and
  the DigitalOcean fallback were not changed.

Next step: review the documentation-only validation PR, then continue with
separate staging-only Birthday enhancements. Keep payment, registration,
add-ons, notification delivery, and production changes out of scope.

## 2026-07-17 - Birthday catalog navigation and final runtime acceptance

Environment: Canada staging only. Status: Catalog runtime gate resolved.

- PR #53 merge SHA `ac20afa4853694d5fe4572492e55baf12a694035`
  produced the initial catalog deployment through Cloud Build
  `aec22814-3f81-46f7-a74c-12aaa7edff7d` and revision
  `le-chateau-canada-staging-00017-t64`.
- PR #54 merge SHA `53960a9e705b271823e375056c2bfce93dcc95d1` was
  built with Cloud Build `50de4c46-c51e-4cd4-86c0-723ce7d712f7` as the
  full-SHA Artifact Registry image and deployed as revision
  `le-chateau-canada-staging-00018-neb`.
- The new revision was Ready at 0% traffic, returned `200 ok` from its tagged
  `/healthz/` route, and then received 100% traffic. Runtime service account,
  Cloud SQL connector, Secret Manager bindings, Cloud Storage mount, liveness,
  environment, scaling, ingress, and resource settings were preserved, as
  confirmed by matching configuration fingerprints.
- Restaurant navigation renders `Birthday Packages` and `Birthday Add-ons`
  exactly once and both routes work. The list/create pages retain CAD currency,
  intended fields, Archived filters, no quantity field, and no destructive
  Delete action. Raw translation keys are no longer visible.
- No migration ran. A temporary read-only Job used the application runtime
  account to confirm both catalog tables and the extension migration record,
  then was deleted. No catalog or business row was created or changed.
- Public/admin smoke, Livewire, retained media, two fixed Birthday slots,
  Toronto plus-2/plus-60 bounds, telephone input, and both browser consoles
  passed. Packages, add-ons, prices, Booking, hold, and payment remain absent
  from the public Birthday flow.
- The current-revision audit found zero error-severity entries, HTTP 5xx,
  fatal/unhandled exceptions, translation/language failures, package/route
  errors, Cloud SQL failures, or FUSE/storage/cache permission errors. Container
  logs recorded successful `/healthz/` requests.
- Rollback remains available through Ready revisions `00017-t64` and
  `00016-2tj`, with Render and DigitalOcean unchanged. Production, real data,
  payment, outbound notification, secrets, and service-account keys were not
  changed.

Known separate issue: the full fresh-install migration order can run the root
Birthday reservation migration before its Reservation extension dependency.
It does not block the initialized Canada staging schema and must not be mixed
into this documentation-only acceptance PR.

Next step: review and merge the final catalog validation record, then begin the
separately scoped Birthday Booking domain and immutable price snapshot phase.

## 2026-07-17 - Birthday Booking snapshot runtime acceptance

Canada staging now runs git SHA
`e2ca19d4407064bb9d34d4fe8fe947cd1624c5c2` as Ready revision
`le-chateau-canada-staging-00024-dof` at 100% traffic. Cloud Build
`2392136c-e2ce-44d7-bced-7b33450958cb` produced:

`northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:e2ca19d4407064bb9d34d4fe8fe947cd1624c5c2`

The image digest is
`sha256:a7f5cfbd2f7bee65040f675c02a7dbb92ffdee7821ac958c239f9590224c2f17`.
The tagged revision passed `/healthz/` before traffic changed, and the runtime
configuration fingerprint matched accepted revision `00018-neb`. Revisions
`00018-neb` and `00021-fom` remain Ready rollback points. No service account,
Cloud SQL connector, Secret Manager binding, Cloud Storage mount, liveness,
resource, scaling, ingress, domain, or environment setting changed.

No migration ran. Application-account Cloud Run Jobs verified the existing
Booking schema, UTC/DST round trips, deterministic add-on snapshot hydration,
immutable history, model guards, transaction rollback, and same-slot
non-occupancy. Authenticated admin list/detail acceptance and public/static/media
regression passed. Current-revision error severity, HTTP 5xx, and matching
fatal/UTC/SQL/FUSE/cache error counts were zero.

All synthetic data and all disposable preflight/validation/cleanup Jobs were
removed. Reservation, Order, Payment, and Payment Log baselines were unchanged;
no slot-hold table exists. Render and DigitalOcean remain unchanged fallbacks,
and production is unchanged.

Current gate: review and merge the documentation-only validation record before
starting any slot-hold, availability-lock, payment, registration, public-flow,
or notification work. Full fresh-install migration ordering remains a separate
known issue.

## 2026-07-18 - Birthday slot-hold runtime validation

PR #60 merge/deployed SHA
`f1d5dc9c8a576e81b8f72f618080e1efb09db6b9` is live on Ready revision
`le-chateau-canada-staging-slot60-f1d5dc9c` at 100% traffic. Cloud Build
`c41097bb-06b6-45a3-a4ff-a10b5405ff73` produced:

`northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:f1d5dc9c8a576e81b8f72f618080e1efb09db6b9`

Image digest:
`sha256:4e56e80eaa8c9dcd704ea5ea67255e320817d2c457c064d5893801b289c5ec6c`.
The tagged candidate returned `200 ok` from `/healthz/` before cutover. The
runtime template fingerprint is
`sha256:ef47dbf662b6b4cc7617f534b329624640c4999d016bc8c626b5804375f1bbad`.
Existing service account, Cloud SQL, Secret Manager, Cloud Storage mount,
liveness, environment, scaling, ingress, and `MAIL_MAILER=log` settings were
retained. Ready revisions `le-chateau-canada-staging-slot59-7e6a1e6d` and
`le-chateau-canada-staging-00024-dof` remain rollback points.

No migration ran. The PR #59 hold migration was read-only verified with its
single migration record, 14 columns, five required indexes, two restrictive
foreign keys, and no residual preflight data. Three synthetic hold rows each
measured exactly 900 seconds.

Authenticated list/detail validation covered active, released,
effective-expired before cleanup, persisted expired after
`birthday:expire-slot-holds`, and no-hold states. Empty timestamps rendered
blank and non-empty timestamps retained the UTC suffix. The admin surface
remained read-only. Core public/admin/Livewire/media checks passed, the retained
media object returned `image/png`, and current-revision error and HTTP 5xx
counts were zero.

All synthetic package, Customer, Booking, snapshot, and hold data and all
temporary Jobs were removed. Reservation, Order, Payment, and Payment Log
baselines remained 0/0/6/0. Render and DigitalOcean remain unchanged fallbacks;
production and secrets were unchanged. Full fresh-install migration ordering
remains an independent issue. The next gate is review of the documentation-only
validation PR, not payment or registration implementation.

## Planned Delivery feature flag runtime setting

The project-owned Delivery gate reads `DELIVERY_ENABLED` through
`config('delivery.enabled')`. The variable defaults to false and invalid values
also fail closed. A future Canada staging deployment of the Delivery gate must
set or retain:

```text
DELIVERY_ENABLED=false
```

This variable is non-secret. Because Canada staging enables Laravel config
cache, changing it requires deploying a new revision so the cached value is
rebuilt. Delivery additionally requires the current Location's existing
`delivery.is_enabled` setting; neither flag replaces Delivery Area, address,
hours, minimum-order, totals, or fee validation.

This entry is planning documentation only: the Cloud Run service and current
revision have not been changed for this feature. Delivery Areas remain
unconfigured, the storefront Delivery UI is not restored, and D2/D3 remain
future phases. Render and DigitalOcean continue as unchanged fallbacks and
production remains unchanged.

## 2026-07-18 - Delivery D1 runtime acceptance

Canada staging now serves PR #62 merge SHA
`6a1ccc1d95e25050abe13e36377a38db7c80e438` from Ready revision
`le-chateau-canada-staging-d1-6a1ccc1d` at 100% traffic. Cloud Build
`48710aec-3904-46a5-8842-0e8d1aa5a719` produced:

`northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:6a1ccc1d95e25050abe13e36377a38db7c80e438`

Image digest:
`sha256:724e82849b6fd8d5befd27d213823e78a271349e53ac32758b9cadd4fe772095`.
The tagged candidate passed `/healthz/` at 0% traffic before cutover. Runtime
template fingerprint `0f5d7552c062fac4` confirms the existing service
account, Cloud SQL connector, Secret Manager bindings, Cloud Storage mount,
liveness, resources, scaling, ingress, and non-Delivery environment remained
in place.

`DELIVERY_ENABLED=false` is explicit on the Canada revision and resolved to
false from the generated Laravel config cache. The Location's stored Delivery
and Collection flags remain true and Delivery Areas remain 0, proving the
project flag overlays rather than destroys Location configuration. Runtime
active order types contain Collection/Pickup only. No migration or schema
command ran.

Disposable Jobs verified stale Delivery-session normalization, strict
order-type/cart/checkout gates, API Delivery rejection, Pickup API
create/read/update, and unchanged business baselines. Browser acceptance then
verified a Pickup item could be added, incremented, decremented, removed, and
used to open checkout without a Delivery fee or address requirement. No Order
was submitted. All synthetic rows, tokens, users, carts, and Jobs were removed.
There was no historical Delivery row in this staging database, so live history
read was not applicable; automated PR #62 coverage remains the evidence for
unchanged historical reads.

Public and admin routes, Livewire, static assets, retained media, and final
logs passed. Error-severity and HTTP 5xx counts were zero. Two expected API
422 responses were recorded, with zero unexpected fulfillment 422s. The only
runtime keyword match was the normal informational Cloud SQL socket startup
message.

Ready rollback points remain:

- `le-chateau-canada-staging-slot60-f1d5dc9c`
- `le-chateau-canada-staging-slot59-7e6a1e6d`
- `le-chateau-canada-staging-00024-dof`

Render and DigitalOcean remain unchanged fallbacks; production is unchanged.
D2 storefront Delivery UI and D3 Delivery configuration have not started, and
Canada staging must continue to use `DELIVERY_ENABLED=false` until separately
approved.

## 2026-07-19 - Delivery D2 Pickup-only runtime validation

Cloud Build `7ae74bf0-1943-4f15-a87d-d5fe43dac2af` built full merge SHA
`31821289df9ae4a162cabd0cac7a3ac6fb04cd0c` with `Dockerfile.cloudrun` and
pushed:

`northamerica-northeast1-docker.pkg.dev/le-chateau-canada-staging/tastyigniter-staging/tastyigniter:31821289df9ae4a162cabd0cac7a3ac6fb04cd0c`

Digest:
`sha256:72371b610a2dff66d29dcee09a2095c72c2f6bb0d932d33744db3444c3689102`.
Revision `le-chateau-canada-staging-d2fix-31821289` passed tagged 0% health,
HTML, Livewire, eight direct CSS/JavaScript assets, config-cache, database, and
startup-log preflight before receiving 100% traffic. The redacted runtime
configuration fingerprint `74bfef31792cdfcf` matched D1, covering the service
account, Cloud SQL connector, secret reference types, media mount, port, and
accepted execution settings.

`DELIVERY_ENABLED=false` remains explicit and cached false. Location Delivery
and Collection settings remain stored, effective Delivery is false, Delivery
Areas are 0, and no migration/schema command ran. Pickup-only desktop/mobile
pages, public Birthday/Reservation, authenticated admin modules, Livewire, and
the retained `IMG_2484.png` object passed. The object returned `image/png`,
109065 bytes, and `Cache-Control: public, max-age=604800`.

A disposable production-image Job verified stale Delivery session fallback,
Delivery-state cleanup, cart/Birthday/Reservation preservation, and fail-closed
422 behavior for storefront and Orders API spoofing without leaking internal
details. Read-only business counts matched preflight and the Job was deleted.
Current-revision error severity, HTTP 5xx, unexpected 422, fatal, Cloud SQL,
FUSE, cache, and permission checks were zero.

Ready rollback remains `le-chateau-canada-staging-d1-6a1ccc1d`. The failed
`le-chateau-canada-staging-d2-fab804d2` revision remains tagged at 0% for
audit. Render and DigitalOcean are unchanged fallbacks; production is
unchanged. D3, payment, real mail, real data, domain, and fresh-install
migration ordering remain separate.

Non-blocking follow-ups are Q-005 (`en_CA` still falls back to `fr_CA`) and
Q-010 (the upstream Orange scheduled Pickup time select does not synchronize a
changed same-day value through its current `wire:ignore` binding). Future
orders remain disabled and neither observation changes this D2 deployment.

## 2026-07-19 - Delivery D3A planning gate

The active Canada revision remains
`le-chateau-canada-staging-d2fix-31821289`, Ready at 100%, with explicit and
cached `DELIVERY_ENABLED=false`. D3A performed read-only source and admin
inspection only. Stored Delivery/Collection settings remain enabled, Delivery
Areas remain 0, and Pickup remains the only effective storefront fulfillment.

The approved D3 sequence is documented in
`DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md`: confirm all business parameters,
configure and verify while the global gate is false (D3B), then deploy an
isolated 0%-traffic tagged revision with the gate true before any staging
cutover (D3C). The accepted D2 revision remains the immediate runtime rollback.

Q-011 is an explicit D3C gate: controlled synthetic provider failure must prove
that no address query, provider credential, geometry, SQL, or internal ID is
leaked to logs or public errors, and the Google/Nominatim quota,
identification, attribution, and fallback policy must be accepted. No runtime,
secret, database, schema, Delivery, tax, payment, mail, domain, or production
configuration changed during D3A.

## 2026-08-20 - Delivery D3 Q-011 final clean acceptance

The merged project-owned geocoder redaction at commit
`3fc841d12e65155c445c9c747ee7f97ec3ea0f49` was built as image digest
`sha256:388a60cd43539746cc1e5725066a4fd7fd9bee0c538401ab8646da68608df2d1`
and validated in tagged revision `le-chateau-canada-staging-q011-3fc841d` at 0%
traffic. The accepted revision `le-chateau-canada-staging-d2fix-31821289`
retained 100% traffic. `DELIVERY_ENABLED=false` remained unchanged throughout.

Before the clean matrix, one scoped transaction corrected Delivery hours from
Monday closed and Tuesday-Saturday 12:00-21:00 to Monday-Friday 12:00-21:00
with Saturday and Sunday closed. Pickup and the polygon, fee, free threshold,
minimum, surcharge, tax, and all other D3B values were unchanged.

The authoritative execution `delivery-d3-q011-clean3-20260820-xc79z` ran from
2026-08-20 18:15:57Z to 18:16:12Z. Cloud SQL mounting, application bootstrap,
and database preflight passed without database diagnostics. The clean matrix
passed empty-result, forward and reverse provider failure, autocomplete, place
lookup, business validation, successful reverse, and closed Delivery API paths.
Exact-window application and Cloud Run scans found no full address, provider
URL, credential, raw provider exception, geometry, SQL/internal ID, database
diagnostic, or PHP path. Public and Livewire errors remained generic and the
Delivery path failed closed without writing an Order.

Desktop and mobile regression passed for Pickup-only storefront behavior,
Birthday and Reservation routes, read-only authenticated Admin access, health,
and absence of browser console errors. The active and isolated task windows contained no
error-severity, HTTP 5xx, PHP fatal, SQL connection, or storage permission
event. Orders, Customers, Reservations, Birthday records, slot holds, Payments,
and Payment Logs remained unchanged.

All four disposable schedule/acceptance Jobs were permanently deleted after
explicit approval, and exact API readback confirmed that each is absent. Cloud
Shell test files and local temporary read-only clones were removed. Historical
Cloud Logging entries remain under platform retention; no temporary config or
synthetic database row remains. The tagged isolated revision remains at 0% for
audit evidence.

Q-011 is `Resolved` only for the current Canada staging tested failure path.
Public Nominatim fallback is not approved for production Delivery traffic and
remains a separate production operations gate. This validation does not enable
Delivery or authorize a traffic change.

## 2026-08-20 - Post-handoff runtime readback and leftover Job cleanup

An independent read-only audit reconfirmed the frozen runtime. Main traffic
remained 100% on `le-chateau-canada-staging-d2fix-31821289` with digest
`sha256:72371b610a2dff66d29dcee09a2095c72c2f6bb0d932d33744db3444c3689102`.
`le-chateau-canada-staging-q011-3fc841d` and
`le-chateau-canada-staging-d2-fab804d2` remained tagged at 0%. The runtime
service account, Cloud SQL attachment, media volume mount at
`/var/www/html/storage/app/media`, the five bound Secret Manager references,
`/healthz/` liveness, `MAIL_MAILER=log`, and `DELIVERY_ENABLED=false` were all
unchanged. Cloud SQL read back `RUNNABLE` with automated backups enabled and the
runtime identity still had zero user-managed keys. No secret value was read.

A full revision sweep added evidence beyond the freeze record. Of the 26
revisions in this service only four define `DELIVERY_ENABLED` — `le-chateau-canada-staging-q011-3fc841d`,
`le-chateau-canada-staging-d2fix-31821289`,
`le-chateau-canada-staging-d2-fab804d2`, and
`le-chateau-canada-staging-d1-6a1ccc1d` — and all four read `false`. The other
22 predate the flag and do not define it. No revision has ever served with
Delivery enabled.

Seventeen Cloud Run Jobs remained from the July initialization phase and were
absent from every project document. Three could write to the database
(`birthday-migration-f40531e4`, `tastyigniter-empty-migrate-178366`,
`tastyigniter-admin-create-178372`), three rewrote container configuration
(`tastyigniter-init-canada-1783653711`,
`tastyigniter-init-canada-1783654052`, `tastyigniter-init-canada-staging`), and
eleven were read-only probes (`tastyigniter-admin-cols-178371`,
`tastyigniter-admin-probe-178370`, `tastyigniter-db-diagnostic`,
`tastyigniter-db-status-178365`, `tastyigniter-http-diag-178373`,
`tastyigniter-install-help`, `tastyigniter-install-help-178365`,
`tastyigniter-php-probe-178365`, `tastyigniter-prefixed-probe-178369`,
`tastyigniter-schema-probe-178367`, `tastyigniter-users-probe-178368`).

`tastyigniter-admin-create-178372` created or reset a super-admin account from a
Secret Manager password and explains the previously unexplained
`tastyigniter-admin-password` secret. None of the 17 stored a literal
credential; every secret-like environment variable resolved through Secret
Manager.

All 17 restorable definitions were exported to the operator's Cloud Shell home
directory, then all 17 were deleted after explicit per-target confirmation
against a pre-deletion snapshot, with no wildcard or inferred target. Exact
readback confirmed zero Jobs remain in the region and 17 of 17 absent.
Post-deletion readback confirmed unchanged traffic, unchanged
`DELIVERY_ENABLED=false`, and health `200 ok`. Two Secret Manager entries,
`tastyigniter-media-bucket` and `tastyigniter-admin-password`, exist but are not
bound to the serving revision; they were left in place.

This cleanup changed no service, revision, traffic, image, environment variable,
database row, schema, secret value, production, Render, or DigitalOcean state.
