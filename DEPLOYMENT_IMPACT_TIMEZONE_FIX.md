# Deployment impact: what reaches the live site if the main-traffic revision is redeployed

Status: **assessment only, nothing executed.** Produced 2026-08-29 on the
user's instruction, to be reviewed before any deployment decision.

## 1. The build point, established rather than assumed

The revision holding 100% of traffic is
`le-chateau-canada-staging-d2fix-31821289`, created **2026-07-19T15:08:14Z**,
running image digest `sha256:72371b61...689102`. That digest carries the
Artifact Registry tag `31821289df9ae4a162cabd0cac7a3ac6fb04cd0c`, which is
commit `31821289` — "Merge pull request #65", 2026-07-19 09:53 -0400. The
revision name, the image tag, and the commit all agree, so the build point is
not a guess.

Main HEAD is `e8fe12c1`, the timezone fix merged today.

**The gap is six weeks, not two: 71 first-parent commits.** Of those, **15
touch anything outside `.md`, and only 8 change runtime behaviour.** The rest
are documentation, tests, CI, and local tooling.

Main traffic's relevant environment, read back today: `DELIVERY_ENABLED=false`,
`MAIL_MAILER=log`, no `MAIL_TEST_REDIRECT_TO`, no geocoder variables,
`APP_TIMEZONE=America/Toronto`, `RUN_CONFIG_CACHE=true`. Several impacts below
are neutralised by those values, and that is *why* they are neutral — change the
variable and the impact comes back.

## 2. Every code-bearing change, with its effect on a live customer

| Change | Customer-visible on main traffic? |
| --- | --- |
| #69 `3fc841d1` — `DeliveryLocalSearch` Livewire component | **No** — reachable only through the delivery path, which `DELIVERY_ENABLED=false` closes |
| #76 `a26897fb`, #78 `8a91b5cb` — `tools/fp1.py`, `.gitattributes`, `.gitignore` | **No** — local tooling, never in the image |
| #79 `e9a4f7ca` — URL redaction in log records, `config/logging.php` | **No** — changes only what is written to logs |
| #82 `879ed5c7` — pin shell scripts to LF | **No** to a customer, but it changes how `start.sh` lands in the image. Build hygiene; if anything it removes a latent CRLF startup hazard |
| #85 `70d65cc5`, #98 `f4ce8f93`, #126 `7673cee4` — tests only | **No** |
| #86 `1f8f0c75` — `GeocoderChainOverride`, `config/delivery.php` | **No, conditionally** — `DELIVERY_GEOCODER_DRIVER`/`_PROVIDERS` are unset on main traffic, and null is documented as "use the stored configuration unchanged". Set those variables and it stops being neutral |
| #90 `be6835a9` — `WeekdayScheduleCorrection` | **YES — see section 3. This is the one to worry about** |
| #101 `de3f6dd3` — Delivery minimum read from the stored setting | **No** — delivery-gated |
| #104 `f02f5e30` — `MailTestRedirect`, `config/mail.php` | **No** — `MAIL_TEST_REDIRECT_TO` is unset, and `MAIL_MAILER=log` sends nothing anyway. **Footgun:** setting that variable on a traffic-serving revision silently redirects every customer e-mail to one inbox. The config comment says to leave it unset; nothing enforces it |
| #124 `443b250c` — Birthday migration relocation | **No** on deploy; a hazard only when migrations are run. See section 4 |
| #122 `491365b2` — `.github/workflows/pipeline.yml` | **No** — CI, not in the image |
| #135 `e8fe12c1` — timezone fix | **YES, and intentionally** — a drifted instance stops serving hours four or five hours off |

## 3. The change that actually moves customer-visible behaviour

`App\Delivery\WeekdayScheduleCorrection` (#90) is registered
**unconditionally** in `AppServiceProvider` —
`Event::listen(WorkingScheduleCreatedEvent::class, ...)`, with no delivery gate.
It rebuilds **every** working schedule: delivery, collection/pickup, **and the
general opening schedule**.

What it fixes: `WorkingHour::getDay()` resolves weekday indices through
`Carbon::startOfWeek()`, which under this site's `fr_CA` locale starts on
Sunday, so every stored day lands one day late — a schedule saved Monday to
Friday is applied Sunday to Thursday.

**Main traffic is running without this correction today.** Its opening and
pickup schedules are therefore currently shifted by one day relative to what is
stored. Deploying corrects them, which means **the days the shop appears open to
a customer will change**. That is a fix, not a regression, but it is a visible
change in live behaviour, and it is the single item that most deserves a
before/after reading on the storefront rather than a code argument.

Note the interaction with the 2026-08-28 stored-hours write: the delivery rows
and the hours JSON were changed then, and opening and collection were
deliberately left alone. After this deploy, opening and collection render on
their stored days without the shift, so the reading has to be taken against the
*stored* values, not against what the site shows today.

## 4. Shared database, migrations, and what a rollback cannot undo

**Nothing in this range writes to the shared database on deploy.**
`docker/cloudrun/start.sh` runs only `package:discover`, `config:cache`,
`route:cache` and `view:cache` — **it does not run migrations.** A deploy is
therefore schema-neutral by itself.

The one migration change (#124) moved
`2026_07_10_000000_add_birthday_booking_fields_to_reservations_table.php` out of
the root `database/migrations/` and into the `abandon.birthday` extension group,
rewriting it to be guarded: on an already-initialized database `up()` sees the
`birthday_booking` column and returns a no-op. That is correct, and it was
clearly thought through.

Two things still deserve stating plainly:

- **The ledger row survives an image rollback.** Whenever migrations are next
  run, a new row is recorded under the `abandon.birthday` group. Rolling the
  image back to `31821289` does not remove it. It is benign — no schema change
  on an initialized database — but it is the only part of this deploy that
  redeploying the old image does not undo.
- **The relocated `down()` is now a live data-loss path that did not exist
  before.** Rolling that migration group back drops `birthday_booking`,
  `birthday_slot_code`, `birthday_slot_key` and the unique index — real
  reservation columns. Nothing in a deploy triggers it. It is a hazard worth
  knowing about before anyone reaches for a migration rollback.

Everything else in the range is code and configuration baked into the image, and
is fully reverted by redeploying the previous image.

## 5. Two ways to do it

### Option A — deploy main HEAD

One build, one revision, everything in one step. CI is green on exactly this
tree, on 8.3, 8.4 and 8.5.

- **Cost:** six weeks of change reach the live site at once, and the schedule
  correction of section 3 changes customer-visible open and closed days in the
  same event as the timezone fix. If the storefront then looks wrong, two
  candidate causes landed simultaneously and separating them costs a second
  deploy.
- **Risk concentration:** every "neutral because unset" item in section 2 stays
  neutral only while those variables stay unset.

### Option B — a minimal timezone-only build from the existing build point

Branch from `31821289`, the exact commit the live image was built from, and
apply only what the incident needs:

- `config/app.php`: `'timezone' => 'UTC'` becomes `env('APP_TIMEZONE', 'UTC')`;
- `docker/render/php-production.ini`: add `date.timezone=America/Toronto`.

Two lines. No `AppServiceProvider` change, and therefore **no schedule
correction, no geocoder override, no mail redirect, no migration relocation**.
The blast radius is the timezone and nothing else.

- **Deliberately dropped:** `TimezoneIntegrity`, the warning class.
  Cherry-picking #135 whole would touch `AppServiceProvider`, which #86, #90,
  #101, #104 and #124 have all since edited — the pick would conflict, and
  dragging in the resolution defeats the point of a minimal build. Dropping it
  costs little today: the warning has no subscriber, so it changes nothing
  operationally until the alert policy exists.
- **Cost:** the deployed tree then matches no branch anyone maintains, and CI's
  green is on HEAD rather than on this ad-hoc branch, so it would need its own CI
  run. It is also work done twice, since HEAD gets deployed eventually anyway.

### Recommendation

**Option B first, Option A later as its own deliberate event.** The incident
being fixed is the clock. Option B fixes exactly that and nothing else, so if
the live site misbehaves afterwards there is only one candidate cause. Option A
then happens on its own schedule, with section 3's before/after storefront
reading planned in rather than discovered.

If Option A is chosen instead, the minimum that should accompany it is a
storefront open and closed reading against the stored hours, taken before and
after, on the same day of the week.

## 6. Not decided here

Nothing is deployed, built, or scheduled by this document. The log-based alert
policy waits on deployment, because the code that emits the warning does not
exist on the live revision until then; its notification channel will be the
address already receiving the budget alert, and not a new one.
