# CLAUDE.md

Project guidance for Claude Code and other coding agents working in this
repository.

## Read these first

Two documents are authoritative. This file is a pointer and a summary of the
non-negotiable rules; where it disagrees with them, they win.

1. `AGENT_WORKFLOW.md` — the risk-based lifecycle, review standard, merge
   gates, destructive-operation rules, and standing project boundaries.
2. `CLAUDE_HANDOFF.md` — current project state, frozen baseline,
   infrastructure, Delivery/Birthday/payment architecture, known issues,
   production gates, the exact next phase, and stop conditions.

Read both in full before the first change of a session. Do not rely on this
summary alone.

## Start of every task

1. State the objective, environment, write scope, real-data impact, and risk
   level before acting.
2. Checkout `4.x`, fetch/pull `origin/4.x`, verify the worktree is clean.
3. Branch from the latest mainline. Never continue new work on an old branch.
4. Inspect the relevant code, config, docs, tests, and prior accepted behavior
   before editing.
5. For runtime or database work, record the exact before-state, rollback path,
   data class, and whether the operation writes anything.

Never stage with `git add .`, `git add -A`, or any broad equivalent. Stage only
exact approved paths. Preserve unrelated user changes.

## Risk levels

- **Level 0** — read-only, audit, planning, runtime readback, documentation.
- **Level 1** — isolated project-owned code, no schema/auth/payment/production
  effect.
- **Level 2** — non-production runtime/configuration, isolated staging work.
- **Level 3** — production, public traffic, real payment/mail, auth/security,
  destructive schema/data, account/permission, secret, or infrastructure
  deletion.

Classify before acting. Level 3 always stops for explicit approval.

## Merge gates

- Level 0 docs-only PRs may auto-merge under the standing authorization
  granted 2026-08-20. It applies only when every changed path ends in `.md`,
  the change records already-confirmed decisions rather than introducing new
  ones, and self-review, `git diff --check`, and the sensitive-data scan are
  all clean. Still open a PR and report the merge. See `CLAUDE_HANDOFF.md`
  section 15 for the full conditions and exclusions; anything outside them
  stops for confirmation.
- Level 1 normally stops once at `Ready to merge` for user confirmation.
- Level 2 may combine an approved merge with isolated staging execution, but
  main-traffic cutover is a separate gate.
- Level 3 requires explicit approval at every high-risk gate.

Never merge merely because checks are absent. CI failure, merge conflict, a new
blocking finding, or scope expansion stops the merge.

## Verification standard

Run checks proportionate to the change and report exact results.

**Never claim a check that did not run.** If the environment blocks a check,
state the limitation plainly and decide whether it is a blocker. Do not
substitute weaker evidence silently, and do not copy a prior document's readback
in place of an actual measurement.

Always run `git diff --check`, review staged scope, and scan the diff for
secrets, credentials, complete addresses, provider URLs, geometry, SQL,
internal IDs, and PII before opening a PR.

## Destructive operations

Every deletion or difficult-to-recover action requires explicit confirmation,
including Cloud Run Jobs and revisions, database rows or databases, logs, data,
cloud resources, and fallback infrastructure.

Name every exact target before asking. Never use wildcards, broad filters, or
inferred targets. Read back exact existence before and absence or state after,
then report recoverability.

## Secrets

The user enters passwords, payment credentials, API secrets, private keys,
tokens, and verification codes through the provider UI, Secret Manager, or
another secure prompt. **Never ask for a value in chat.** Never place one in
shell history, logs, screenshots, Git, test fixtures, or documentation.

Record only `configured`, `not configured`, `validated`, or `failed`, plus a
non-sensitive error category.

## Standing boundaries

- Canada staging Delivery stays closed until the separately approved D3C
  isolated phase.
- Production and public/main-traffic changes are Level 3.
- Public Nominatim is not approved for production Delivery traffic.
- Birthday is not a food Order; final Reservation creation follows verified
  payment.
- Payment and food/Birthday workflows stay separate above shared transaction
  primitives.
- Render and DigitalOcean remain fallbacks and may not be deleted incidentally.
- Do not modify vendor/core. Do not read or stage `.codex-tmp/`.
- Do not run `migrate:fresh`, `migrate:refresh`, or `db:seed` without an
  explicitly approved, verified disposable environment. Never on staging or
  production as routine troubleshooting.

## Current state

As of 2026-08-20, Delivery is configured but globally closed
(`DELIVERY_ENABLED=false`), and all 24 previously pending business decisions in
`DELIVERY_D3_BUSINESS_PARAMETER_PLAN.md` are confirmed. The next approved phase
is **Delivery D3C isolated enablement**: a Canada staging revision whose only
intended gate change is `DELIVERY_ENABLED=true`, at 0% main traffic behind a
tagged URL, verified with synthetic data only.

`CLAUDE_HANDOFF.md` sections 18-20 hold the exact next phase, the D3C
acceptance criteria, and the stop conditions. Treat that document as current
truth rather than this paragraph.

## Records to update

Substantive changes append a dated entry to `CHANGELOG_AI.md` and, when
configuration or runtime state is involved, to `ADMIN_CONFIGURATION_TRACKER.md`
and the relevant runtime document.
