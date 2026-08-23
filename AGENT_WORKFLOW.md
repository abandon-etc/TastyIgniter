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

- Level 0: auto-merge is permitted under the standing docs-only authorization
  granted 2026-08-20. See `CLAUDE_HANDOFF.md` section 15 for the exact
  conditions and exclusions. Anything outside them stops for confirmation.
- Level 1: normally request one merge confirmation.
- Level 2: an already-approved task may continue through merge and isolated
  staging, but main-traffic cutover is a separate gate.
- Level 3: explicit approval at each high-risk gate.

CI failure, merge conflict, a new review blocker, or scope expansion stops the
merge. Never merge merely because checks are absent.

## 8a. Where a fact belongs

One fact is maintained in one file. Anything that changes as work proceeds
lives in `D3C_PROGRESS.md` alone; every other document points at it rather than
repeating it. A fact copied into a second file will diverge, and the copy that
is read is then the one that is wrong.

**`CLAUDE.md` is the rule layer and changes rarely.** It is loaded in full at
the start of every session, so it is kept under 150 lines; content that grows
past that moves into the document it already points to.

Update it only when a rule, boundary, or gate changes, when a phase starts or
completes, when a standing authorization is granted, or when a new global
constraint is found. A single pull request, a single acceptance item, a defect
fix, or an ordinary documentation edit does not touch it. Its current-state
section carries no progress: no phase position, no next step, no completion
count. It carries only facts that outlive the current work, plus pointers.

**`D3C_PROGRESS.md` is the state layer and is a snapshot.** It is overwritten
as work moves, stays within a page, and is deliberately not a history;
`CHANGELOG_AI.md` is append-only and keeps that. It holds the acceptance table,
which doubles as the acceptance record, the deployed test revisions and what
each is for, the outstanding items with owners, and the next concrete action.

Refresh it whenever a group of acceptance items completes, and again before
context runs short rather than after. Its readers include the project owner,
who is not technical and reads it directly: describe each check in terms of
what is being verified, not by clause number.

**`CLAUDE_HANDOFF.md` is the reference layer.** Architecture, infrastructure,
known issues, production gates, phase definitions, and stop conditions. Durable
detail rather than progress.

## 8b. Acceptance methodology

Learned during D3C, at the cost of results that had to be discarded.

### Rendering is not execution

An acceptance item worded as *offers X*, *directs to X*, *preserves X*, or
*keeps X reachable* is not satisfied by seeing the element on screen. Follow it
through: click it, complete the flow, confirm the outcome. Record which of the
two happened, because they are different evidence:

- **rendered** — a screen element was observed;
- **executed** — the action was carried out and its result confirmed.

An item whose subject *is* the rendering, such as "no technical detail is
shown", is properly satisfied by observation. Everything else is not.

Label every acceptance row with its evidence type. A row marked passed on
*rendered* alone, where the wording promises behaviour, is not passed.

### A check that never ran proves nothing in either direction

"Never claim a check that did not run" (section 6) was written to stop
unearned passes. D3C produced its first application to a negative result: a
failure from a test that never actually executed is equally not a failure, and
no defect may be recorded from it.

The case: Enter sent to a focused add button through the test tooling produced
no server request, which read as "a keyboard-only customer cannot order". An
event probe installed on the button then captured zero events during the
tooling's keypress — not even a keydown — while keydown and click dispatched
from page JavaScript were captured normally, proving the probe worked. The
keypress had never reached the page; the test had never run. A person at a
real keyboard settled it in a minute: it works.

Before recording a negative result, confirm the stimulus actually arrived —
an event probe, a log line, a network trace. "The page did not react" is
evidence only once it is established that the page was given something to
react to. Both browser-automation toolsets available here share the keypress
limitation, so keyboard-activation checks are settled by a person or recorded
as not run.

### A test that truly reproduces a defect also exercises the fix

Writing the weekday correction produced a case worth keeping. The first draft of
the listener called `WorkingSchedule::getType()` unguarded. That method is
declared to return `string` but is backed by a nullable property, so on a
schedule with no type it threw, **out of an event listener**, which would have
broken page rendering rather than degrading.

Nothing in review caught it. The test caught it, and only because the test built
a real schedule and ran the real listener over it instead of asserting against a
double. The listener now degrades to a no-op.

The lesson is not "write tests". It is that a test written to genuinely
reproduce a defect has to exercise the same code path the defect lives on, and
that path then gets examined under conditions the happy path never creates. A
test that mocks its way to the assertion would have passed and taught nothing.

### An isolated test URL is not isolated until the URLs are pinned

A Cloud Run revision at 0% traffic behind a tagged URL is only isolated for as
long as nothing sends the browser elsewhere. The application builds links and
redirects from `APP_URL`, which points at the service's main hostname, so the
first server-issued redirect leaves the tagged revision and lands on whichever
revision serves main traffic.

Deploy test revisions with `APP_URL`, `ASSET_URL`, and `CLOUD_RUN_SERVICE_URL`
all set to the tagged URL. Without that, only single-page checks are valid.

**The failure is silent.** The revision serving main traffic renders a normal
page, and the only visible difference is the hostname. A multi-step result
obtained without pinning looks correct and is not; discard it rather than
reason about whether it was probably fine. Check the hostname, not the
plausibility of the result.

### A stored value says what is stored, not what the system does with it

The delivery minimum was stored as CA$20.00 and read back as CA$20.00, and
the conclusion "the system will not refuse at CA$80.00" was drawn from that.
It was wrong: the storefront computes the minimum as the larger of the stored
value and a value derived from the fee rules, and the same computed value
feeds the checkout gate. The stored value never reached the customer.

This is "rendering is not execution" in the configuration layer: a value was
read and its effect inferred. Before asserting behaviour from a setting,
follow the value to where it is consumed, and note every other input that
joins it there. A setting proves what was entered; only the consumption path
proves what happens.

Where the consumption path is vendor code, it can be pinned without a runtime:
build the recorded configuration shape in a test, drive the vendor classes
directly, and assert the outputs at the boundaries. That turns "the code
predicts" into "the semantics are established", and leaves the deployed
environment only one thing to confirm, that it runs the same code.

### Shared settings move between readings

The owner edits Location settings, hours, and fee rules directly in the admin
and has agreed to say so when they do. That notice is welcome and is not
relied on. Before and after every key reading, read the shared settings back
rather than assuming they still match the last record; a reading taken under
a premise that has since changed is a historical reading, and may not be
cited as evidence of current state.

The case: delivery was observed refusing an as-soon-as-possible order on
Friday 2026-08-21 under an ASAP/later restriction of "None". On 2026-08-22 the
owner changed the restriction to ASAP-only. The Friday observation is kept as
history; the defect's diagnosis stands because it rests on source and unit
tests, not on that observation.

Without admin access, read back from the storefront and say which signal was
used. The order-type dialog renders a "later" choice only when the restriction
allows one; the date list it offers reflects the future-orders setting; the
basket panel prints the computed minimum; and the "More info" panel on the
menu page lists each delivery area's fee rules in priority order, which is
the stored rule shape. Record the setting state alongside every reading so
that a later reader can tell a current reading from a historical one.

### Time-dependent checks wait for the time

Opening hours, cut-offs, and weekday or weekend behaviour live in the shared
database. Editing them to force a condition changes the live site at the same
instant, which destroys the isolation the phase depends on. Waiting also
produces stronger evidence than manipulation: the system is observed under the
conditions it will actually meet.

Schedule each such check into its real window and record which windows exist,
including the ones that come round rarely. A weekend-only check missed on
Saturday costs a week.

Where a condition genuinely cannot be reached by waiting, stop and ask before
changing anything. If a change is approved: record the exact prior state, keep
the window as short as possible, restore immediately afterwards, verify the
restoration field by field, and record the period during which shared state was
altered and what else it could have affected.

### One database limits what can be verified

Every revision of a service shares one database. Anything stored there cannot
be varied for a single revision, and changing it changes the live site too.
Record any acceptance item that cannot be verified by observation for this
reason, rather than substituting a weaker check for it.

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
