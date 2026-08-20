# Risk-Based Agent Workflow

Date: 2026-08-20
Status: project workflow for Codex, Claude, and other coding agents

Read this file before starting a project task. The objective is to reduce
manual review handoffs while keeping merge, production, destructive, traffic,
and secret gates explicit.

## 1. Default lifecycle

```text
Task
  -> classify risk
  -> sync latest 4.x and create a focused branch
  -> inspect before changing
  -> implement the smallest project-owned change
  -> verify
  -> Review A: implementation review
  -> Review B: adversarial self-review
  -> fix every blocking finding
  -> repeat final verification
  -> create Ready PR
  -> merge gate
  -> sync 4.x after approved merge
  -> continue the next already-approved phase
```

Do not create a Ready PR with a known blocker. Routine Level 0/1/2 work does
not automatically require a separate agent review.

## 2. Start-of-task checks

1. State the objective, environment, write scope, real-data impact, and risk
   level.
2. Checkout `4.x`, fetch/pull `origin/4.x`, and verify the worktree is clean.
3. Create a focused task branch from the latest mainline. Never continue new
   work on an old feature branch.
4. Do not read, modify, or stage `.codex-tmp/`.
5. Identify relevant code, configuration, documentation, tests, and prior
   accepted behavior before editing.
6. If runtime/database work is involved, record the exact before-state,
   rollback, data class, and whether the operation writes anything.

Preserve unrelated user changes. Never stage with `git add .`, `git add -A`, or
an equivalent broad command.

## 3. Risk classification

### Level 0 — read-only or documentation

Examples: audit, source inspection, planning, changelog, runtime readback,
documentation, and reports.

Required flow:

1. Complete the read-only work or docs.
2. Fact-check against current source/runtime rather than copying old records.
3. Self-review scope and clarity.
4. Run `git diff --check` and a sensitive-data scan.
5. Create a Ready docs-only PR.

No independent agent review is required. Auto-merge is allowed only when the
user has explicitly granted standing docs-only merge authorization; otherwise
stop once at the merge gate.

### Level 1 — low-risk isolated code

Examples: UI copy, isolated view override, focused tests, logging sanitization,
or a small project-owned bug fix without schema, auth, payment, production, or
public-traffic effect.

Required flow:

1. Implement the smallest project-owned change.
2. Run focused tests and relevant regression tests.
3. Run syntax, lint/Pint, and build/view checks that apply.
4. Complete Review A and Review B.
5. Fix findings and repeat affected verification.
6. Run `git diff --check` and a sensitive-data scan.
7. Create a Ready PR and report `Ready to merge`.

Normally wait for one `确认合并` response. Do not ask the user to copy the PR to
another agent by default.

### Level 2 — medium-risk non-production runtime/configuration

Examples: Canada staging configuration, a 0%-traffic Cloud Run revision,
Delivery parameters, disposable Cloud Run Jobs, schema-safe operational work,
or other non-production infrastructure.

The agent may perform an already-approved task continuously through audit,
before-state backup, isolated implementation, automated validation, rollback
verification, cleanup, documentation, and a Ready PR.

Additional rules:

- Do not modify production.
- Prefer 0%-traffic/tagged isolation.
- Do not cut over main traffic without explicit approval.
- Do not perform destructive cleanup without a separate exact-resource
  confirmation.
- Never request or expose secret values.
- Stop for a new security/privacy risk, destructive scope, or undocumented
  runtime drift.

An approved Level 2 task may include merge plus isolated staging execution, but
not public/main-traffic cutover unless that cutover was separately authorized.

### Level 3 — high risk

Includes production, official domains, real payment, real email/SMS,
authentication/security architecture, destructive database/schema operations,
account/permission changes, secrets, public/main-traffic cutover, data deletion,
and fallback-infrastructure deletion.

Level 3 always requires explicit user approval at the relevant gate. Standing
autonomy or approval for a lower-risk task never carries into Level 3.

## 4. Review A — implementation review

Review the complete diff and behavior for:

- scope alignment and minimality;
- functional correctness and edge cases;
- preservation of upstream/success behavior;
- failure-closed behavior where required;
- regression coverage;
- security, privacy, and data integrity;
- project ownership with no vendor/core patch;
- rollback and cleanup correctness;
- documentation/tracker accuracy.

Record or fix every blocking finding before proceeding.

## 5. Review B — adversarial self-review

Re-read the change as a skeptical reviewer who did not implement it:

- Which exception, race, stale-session, or invalid-input path was missed?
- Is any catch broader than intended?
- Can success behavior or upstream semantics change accidentally?
- Can a test pass without exercising the intended failure?
- Can client input control amount, payable, area, fee, slot, status, or identity?
- Can logs, validation payloads, console output, or docs expose a secret, URL,
  address, geometry, SQL, internal ID, provider payload, or PII?
- Does the change write unexpected rows or leave temporary resources?
- Does it touch vendor/core, schema, auth, payment, production, or traffic
  beyond the classified level?
- Is rollback exact and non-broad?
- Could a new agent misunderstand what is enabled versus merely configured?

Fix findings, then rerun every affected test and check. `No blocking findings`
is required before a Ready PR.

## 6. Verification standard

Choose checks proportionate to the change, and report exact results:

- focused and related regression tests;
- syntax and lint/Pint;
- build, view compilation, or asset checks;
- health, page, API, browser console, mobile, and accessibility checks;
- database connectivity/readback, row-count integrity, and migration status;
- exact runtime revision, traffic, image, config, and log checks;
- `git diff --check`;
- staged-file review and sensitive-data scan.

Never claim a check that did not run. If an environment blocks a check, state
the limitation and decide whether it is a blocker rather than silently
substituting weaker evidence.

## 7. When independent review is still needed

Use or recommend a second independent review for:

1. payment or refunds;
2. authentication or authorization;
3. PII/privacy architecture;
4. destructive migrations;
5. races affecting money, inventory, or availability;
6. production incident fixes;
7. major refactors;
8. security boundaries; or
9. an explicit user request.

Routine docs, UI, focused bug fixes, and isolated staging validation do not
default to a second reviewer.

## 8. PR readiness and merge gates

Before creating a PR:

- verify staged and unstaged scope;
- stage only exact approved paths;
- confirm no `.env`, `.local`, dump, secret, credential, real data, complete
  address, provider URL, or temporary test artifact;
- include what changed, why, files, exclusions, exact verification, risks, and
  next step in the PR description;
- mark the PR Ready only when no blocking finding remains.

Merge policy:

- Level 0: auto-merge only under explicit standing docs-only authorization.
- Level 1: normally request one merge confirmation.
- Level 2: an already-approved task may continue through merge and isolated
  staging, but main-traffic cutover is a separate gate.
- Level 3: explicit approval at each high-risk gate.

CI failure, merge conflict, a new review blocker, or scope expansion stops the
merge. Never merge merely because checks are absent.

## 9. Post-merge continuation

After the user approves merge:

1. Merge the exact PR.
2. Checkout `4.x`.
3. Fetch/pull latest `origin/4.x`.
4. Verify the merge SHA is contained in `HEAD`.
5. Confirm the worktree is clean.
6. Update the phase status and project records as needed.
7. Begin the next already-approved Level 0/1/2 step automatically.

Do not ask an empty “是否继续？” question. Stop only if the next step is Level
3, destructive, production/main-traffic, requires a secret/business decision,
or was not previously approved.

## 10. Destructive operations

Deletion and difficult-to-recover changes always require explicit confirmation.
Examples include Cloud Run Jobs/revisions, database rows/databases, logs/data,
cloud resources, and fallback infrastructure.

The approval request must name every exact target and explain impact and
recoverability. Never use broad filters, wildcards, or computed/unverified
targets. Read back exact existence before and absence/state after the action.

## 11. Secret handling

The user enters passwords, payment credentials, API secrets, private keys,
tokens, and verification codes through the provider UI, Secret Manager, or a
secure platform prompt. Do not ask for values in chat and do not place them in
shell history, logs, screenshots, Git, test fixtures, or documentation.

Record only non-sensitive state such as `configured`, `not configured`,
`validated`, or `failed`, plus a safe error category.

## 12. Project-specific standing boundaries

- Canada staging Delivery remains closed until the separately approved D3C
  isolated phase.
- Production and public/main-traffic changes are Level 3.
- Public Nominatim is not approved for production Delivery traffic.
- Birthday is not a food Order; final Reservation creation follows verified
  payment.
- Payment and food/Birthday workflows remain separate above shared transaction
  primitives.
- Render and DigitalOcean remain fallbacks and may not be deleted incidentally.
- Do not modify vendor/core or read/stage `.codex-tmp/`.
- Do not run `migrate:fresh`, `migrate:refresh`, or `db:seed` without an
  explicitly approved, verified disposable environment—and never on staging or
  production as routine troubleshooting.
