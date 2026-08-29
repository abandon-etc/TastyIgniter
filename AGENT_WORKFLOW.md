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

Since 2026-08-29 the pipeline is live (checkout, extensions, throwaway
MySQL 8.4 service, `igniter:up`, full suite, PHP 8.3/8.4/8.5): **a code PR
merges only on a green check**, and checks that are red or have not
finished block. Docs-only PRs remain exempt. The pre-2026-08-29 reading
that an empty check list merely meant untested is retired with the era it
described.

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
react to.

The same rule caught a third case on 2026-08-29, and this one had already
produced a wrong entry. A script set a debounced Livewire field and
submitted immediately, so the server received the *previous* value and
answered with a genuine-looking "we couldn't locate that address"; a
retry then succeeded because the value had synchronised by then. It had
been recorded as a transient provider failure. When driving a bound
field, read the component's own state back and confirm it holds the value
before submitting — the input's DOM value is not the thing being sent. Both browser-automation toolsets available here share the keypress
limitation, so keyboard-activation checks are settled by a person or recorded
as not run.

The same rule caught a second case the same day, in the shell rather than the
browser. A container test run looked like a pass: Git Bash had rewritten the
container's working directory, `-w /app`, into a Windows path, the container
failed at once, and the command still exited 0 because the last stage of the
pipeline was `tail`. An exit code belongs to the last command in a pipeline,
not to the check; read the check's own output, or take `${PIPESTATUS[0]}`,
before calling it run. Pass `MSYS_NO_PATHCONV=1` to `docker` under Git Bash.

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

### Overriding vendor behaviour: enumerate the consumers, then prove nothing else broke

Two checks belong to every project-side override of a vendor method, and
neither is covered by the override's own tests.

**Enumerate the consumers.** Proving who constructs the object is not proving
who reads the value. Before merging, list every reader of the overridden
method and of anything it is derived from, across the vendor packages, every
installed extension, the theme, the API, mail templates, admin views, the
project's own extension and theme, and JavaScript. A reader that derives the
same quantity by another route keeps the old behaviour and diverges from the
storefront; either bring it under the override or record it as a known
difference. Record the enumeration with the override so that a later reader
sees it was done. The delivery-minimum override was merged only after this
showed that the label, the checkout button, the checkout security check,
and the deprecated wrappers all reach the one method, and that nothing else
derives a minimum from the rules. The weekday correction had the same check
earlier: a plausible off-by-one in `getWorkingHourByDateAndType` was
discarded because it had no callers.

**Prove "nothing newly broken" with a before-and-after run, not a pass.** A
green run after the change says the change's own tests pass and that the
suite is no worse than whatever it was; it does not say what it was. In the
same container, run the relevant suite on the changed tree, then `git stash
push -u`, run it again on the unchanged base, `git stash pop`, and compare:
test and assertion counts, and the list of erroring or failing tests by
name. Report both sides. The delivery-minimum override reported 99 tests and
197 assertions with 25 environment errors before, 105 and 223 with the same
25 after, which is a stronger statement than "all passing" and an honest one
where the environment cannot run everything. Say why the environment errors
are environment errors, from their messages, not from their count.

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


## 方法教训:供应商的功能描述不是事实依据

记于 2026-08-29,当日三例。

**规则:供应商的产品页、市场条目、官方文档所述的功能,不作为事实依据。以源码,
或一次实测,为准。** 在两者之一到手之前,基于宣称的功能所做的排期与设计都是待定的。

当日三例:

1. **市场页面**称 igniter-translate 自带"前台与后台 locale 选择器"。源码里
   `Extension.php` 只注册了后台表单控件,**没有任何路由、没有前台组件**。若照页面
   描述排期,会以为它顺带解决了切换器问题。
2. **Carte Key 文档**回避了绑定流程要写 `.env` 这件事,于是在容器化部署上必然
   失败,而失败信息只有一句 `{"message":"Server Error"}`。
3. **本方自己**把"composer.json 里没有这类扩展"升级成了"系统装不了一道菜两个
   语言"。**没装不等于装不了。**

三例同型:**把观察或宣称当成了结论。** 第三例尤其要记住,因为它说明这条规则不只
约束外部信息源——同一个跳跃在自己身上同样会犯。

对照当日几次奏效的做法:先 grep 再决定要不要跑探针;先读镜像标签再谈构建点;
先拿一次带对照的日志查询再谈根因。**代价最低的取证先做,结论后下。**
