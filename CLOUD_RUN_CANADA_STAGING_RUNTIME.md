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
