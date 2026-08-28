# CI Enablement Plan

Date: 2026-08-28
Status: plan only. **Enabling CI, editing any workflow file, and the first
run are each separately approved.** Workflow files are explicitly outside
the docs-only auto-merge authorization.

## 1. Where CI stands today

- `.github/workflows/pipeline.yml` and `release.yml` report `active`, but
  `gh run list` has always returned zero runs: CI has never executed on any
  branch or PR. An empty check list means **untested**, not passing
  (recorded in `CLAUDE_HANDOFF.md` section 10).
- **The pipeline as committed runs no tests.** `pipeline.yml` is 24 lines:
  checkout, PHP setup (matrix 8.3 / 8.4 / 8.5), `composer install` — and
  ends. Even with Actions enabled it would go green without executing a
  single test. Any enablement must add a test step, or the green is a
  stronger version of the empty-checks trap.
- Evidence base for what a real run would report: the 2026-08-21 container
  run of the full suite — 187 tests, 301 assertions, **82 errors, 0
  failures** on `php:8.3-cli` / PHPUnit 11.5.56. 78 of the 82 are
  `PDOException: could not find driver` (the image has no `pdo_mysql`); the
  remaining 4 were not individually classified. Later partial runs add: the
  Delivery suite errors are "no database, no APP_KEY" environment errors.
  A live re-classification was attempted on 2026-08-28 but the local Docker
  engine was down; it becomes step 0 below.

## 2. Proposed shape

Keep `setup-php` (no custom runner image needed) and make the job real:

1. **Extensions**: add `extensions:` to the setup-php step, aligned with
   what `Dockerfile.cloudrun` installs (read the exact list at execution;
   `pdo_mysql` is the known blocker, `intl`, `gd`, `zip` are the usual
   TastyIgniter suspects).
2. **Database**: a `mysql:8.4` service container (staging is MYSQL_8_4),
   with a throwaway database and `DB_*` env pointed at it. Synthetic
   `APP_KEY` generated in the job. No real credential, secret, or staging
   resource is ever referenced from CI.
3. **Test step**: `php artisan package:discover`, migrations against the
   throwaway service DB, then `vendor/bin/phpunit --no-coverage`.
4. **Matrix**: keep 8.3/8.4/8.5 initially; if 8.5 fails on platform or
   dependency grounds, shrinking the matrix is a recorded triage decision,
   not a silent edit. Staging runs 8.3, which is the row that gates.

## 3. Disposition of the 82 errors

| Class | Count (per 2026-08-21 evidence) | Disposition |
| --- | --- | --- |
| `PDOException: could not find driver` | 78 | Expected to vanish with `pdo_mysql` plus the service DB; verify by count, not assumption |
| Environment (no `APP_KEY`, no DB) | within the remainder | Vanishes with job env; verify |
| Unclassified residue | 4 at most | Step 0 names them; each then gets a fix-or-quarantine decision recorded in the PR — no silent skips |

Zero assertion failures is the recorded baseline: the suite's substance
already passes where the environment lets it run.

## 4. Path to first green

0. **Re-run locally with a mysql-capable container** (the recorded recipe
   plus `pdo_mysql`, or a compose pair) and produce the named list of
   whatever is not driver/env noise. Read-only with respect to the project;
   needs the local Docker engine up.
1. **One Level 1 PR** editing `pipeline.yml` as in section 2. Stops for
   explicit merge confirmation (workflow files are outside every standing
   authorization).
2. **Enable Actions on the fork** (repository settings; the likely reason
   for zero runs is Actions disabled on forks). User action, or agent via
   `gh api` on explicit approval. If the repository is private, note the
   Actions minutes quota before enabling.
3. **First run on a no-op branch** (workflow_dispatch), triage per
   section 3, fix or quarantine with records, re-run to green.
4. **Declare the gate**: from first green onward, an empty or red check
   list blocks code PRs; `AGENT_WORKFLOW.md` and `CLAUDE_HANDOFF.md`
   section 10 are updated to retire the "CI never ran" reading. Docs-only
   PRs remain exempt.

## 5. Why now

`CLAUDE_HANDOFF.md` section 10 already fixed the decision point: after
D3C, and before substantive code work — Birthday checkout, payment,
webhooks, authentication — decide CI explicitly, because "code changes
should not [proceed without it], indefinitely". The payment workstream
(`PAYMENT_WORKSTREAM_PLAN.md`) is exactly that substantive code work, so
CI enablement is sequenced before the D-step code PR merges.
