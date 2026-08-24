# PRE-SKYEAGLE CONTINUATION CHECKPOINT

> **This is a continuation checkpoint, not a competing readiness report and not final certification.**
> It exists so a fresh Claude Code session can resume the three-scan PRE-SKYEAGLE audit from this exact
> point without repeating Scan 1 or losing any finding, decision, agent result, or next action.
> It supersedes nothing. `docs/rebranding.md`, `docs/RebrandingPlan.md`, `docs/branding/**` and
> `docs/RebrandingBugs.md` remain the historical record.

### Checkpoint revision history

| Rev | What changed |
|---|---|
| 1 | Initial write. PRE-10 (guardrail inert-rule behaviour) recorded as **the one unproven Scan-1 claim**, after Agent 2A failed and two direct PHPUnit runs timed out. |
| 2 | **PRE-10 CLOSED — proven by execution.** Orchestrator read the real `MODULE_NAMESPACE` constants from all four shipped rule files and evaluated the shipped comparison against both namespaces; all four go inert under a SkyEagle namespace. Ledger row and §16 rewritten; §4 scan states and §1 git status refreshed. See §16 PRE-10 for the evidence and the one residual gap. |
| 3 | **PRE-09 remediation BEGUN — first item complete. S2-P1-25 FIXED.** Brand-manifest gate restored from RED (119/123, exit 1) to GREEN (123/123, exit 0) by re-issuing five hashes; reason recorded in `12-release-verification.md` Revision 5. Committed `45e9eb4f3`. **HEAD is no longer the scan baseline** — see §1. |
| 4 | **HEAD literal removed from §1** — a checkpoint cannot hold its own commit hash, and every later commit would stale a pinned value; the remediation-commit table is now the durable record. **New finding S2-P1-26** added from Agent 2E addendum 3 (20 reachable unbranded keys; a leak class with no catalogue row that SET-TRANSLATION structurally cannot fix; `applicationTitle` already 7 call sites deep). P1 count 16 → 17 open. |
| 5 | **Phase 1 continuation reconstruction complete (2026-08-24).** Re-derived branch `feat/thiqa-branding-foundation`, HEAD `26c32fcb3c027702d9b6fe167017487469a19e5d`, status `?? .claude/`, five registered worktrees, and the five commits after the scan baseline. MariaDB and Apache were both unavailable during the bounded probes. Toolchain paths/versions were inventoried without running tests or analysis. Active task is verification of already-landed commits: `45e9eb4f3`, then `aebcfdfc5` + `26c32fcb3`; no duplicate repair has been started. |

*If you amend this file again, add a row here. A checkpoint that silently changes is worse than one that
admits what moved — that is the same corrections-register discipline the rest of this corpus uses.*

---

## 1. CHECKPOINT IDENTITY

```text
CHECKPOINT PURPOSE:
Resume the three-scan PRE-SKYEAGLE audit/remediation programme after Claude Code session/usage interruption.

PROJECT:                        G:\My Drive\OpenEMR
BRANCH:                         feat/thiqa-branding-foundation
BASELINE HEAD AT SCAN START:    65616d4b28d34e8ba077d6397b9e916aab030d11
CURRENT HEAD:                   RE-DERIVE ON RESUME (`git rev-parse HEAD`).
                                Deliberately NOT pinned here: a checkpoint cannot contain the hash of its
                                own commit, and every later remediation commit would make a pinned value
                                stale. The durable record is the remediation-commit table below — trust
                                that, not a HEAD literal.
                                *** HEAD IS NO LONGER THE SCAN BASELINE — remediation has begun ***
CURRENT OBSERVED HEAD:          26c32fcb3c027702d9b6fe167017487469a19e5d (Phase 1, 2026-08-24)
CURRENT GIT STATUS:             ?? .claude/
                                (this checkpoint is committed as of Rev 3)
                                tree otherwise clean — no tracked file modified outside the commit below
                                sites/default/sqlconf.php is skip-worktree (flag S) — do not commit
SKYEAGLE MIGRATION STARTED:     NO
SKYEAGLE BRANDING CHANGES:      NONE
REPOSITORY WRITES THIS SESSION: NONE except this checkpoint file
CURRENT PROGRAMME:              PRE-SKYEAGLE three-scan audit/remediation/certification
FINAL TARGET:                   PRE-SKYEAGLE CERTIFICATION: PASS
                                SAFE TO START SKYEAGLE MIGRATION: YES
```

**Pre-brand remediation commits (never conflate these with the scan baseline):**

| Commit | Task | What it did |
|---|---|---|
| `45e9eb4f3` | PRE-09 (1st item) | `prebrand: re-issue four stale brand-manifest hashes and record why` — restored the release gate from RED (119/123, exit 1) to GREEN (123/123, exit 0). Five entries re-issued (the four drifted documents plus `12-release-verification.md`, which was edited to record the reason and is itself manifest-covered). Entries **re-issued, never deleted**, per RB-25. No SkyEagle change. |
| `aebcfdfc5` | PRE-09 / S1-P0-01 (verification pending) | Added PR-23…PR-28 coverage for the eight previously unrecorded core branding edits. Phase 1 confirmed this commit is landed; substantive invariant verification has not yet run. |
| `26c32fcb3` | PRE-09 / S1-P0-01 (verification pending) | Corrected the stale PR-30 reference in the patch-record reconciliation table. Phase 1 confirmed this commit is landed; substantive invariant verification has not yet run. |

Add a row for every further remediation commit.

**Live stack state at checkpoint time (re-derived, not inherited):**

```text
MariaDB 127.0.0.1:3306   NOT RESPONDING   (Phase 1: SELECT probe exit 1, connection refused)
Apache  localhost:8300   NOT RESPONDING   (Phase 1: curl HTTP 000, exit 1, 2 s connection timeout)
```

**Phase 1 continuation state (2026-08-24): COMPLETE.** Governing checkpoint, `CLAUDE.md`,
`CLAUDE.local.md`, and applicable root `AGENTS.md` were read in full. The branch, HEAD, status, five
registered worktrees, latest 15 commits, five commits after baseline, and changed-file names for every
recent PRE-SKYEAGLE commit were re-derived with bounded read-only commands. Tool inventory: PHP 8.3.33 at
`C:\openemr-stack\php\php.exe`; PHPUnit 11.5.55 at `vendor\bin\phpunit`; PHPStan 2.1.56 at
`vendor\bin\phpstan`; Composer 2.10.2 at `C:\openemr-stack\composer.phar`; Node v24.18.1 and npm 11.16.0
under `C:\Program Files\nodejs`; required PHPStan override
`C:\openemr-stack\phpstan-localtmp.neon` exists. No tests, analysis, broad scan, service repair, database
mutation, or PRE repair was run in Phase 1. **Currently active task:** verify work already landed.
**Exact next incomplete item:** E1 / S2-P1-25 verification of `45e9eb4f3`, followed by E2 / S1-P0-01
substantive invariant verification of `aebcfdfc5` and `26c32fcb3`, before any duplicate repair.

Apache was started successfully earlier this session (`C:\openemr-stack\start-openemr.ps1`) and served many
requests. It stopped responding after a `GET /apis/default/fhir/metadata` request hung — consistent with the
documented Twig-render/session-lock wedge in `CLAUDE.local.md` §9, which states the hang holds the PHP session
file lock until Apache is restarted. **A resuming session should restart Apache before any runtime work.**

**Worktrees (4 registered — see S1-P2-14):**

```text
G:/My Drive/OpenEMR                                            65616d4b2  [feat/thiqa-branding-foundation]
G:/My Drive/OpenEMR/.claude/worktrees/agent-a0be56487a171bfdd  631f2b38c
G:/My Drive/OpenEMR/.claude/worktrees/agent-a2d5c8fbfdf82dc79  631f2b38c
G:/My Drive/OpenEMR/.claude/worktrees/agent-a987c6bd7f63e0e19  631f2b38c
G:/My Drive/OpenEMR.worktrees/sds                              631f2b38c  [agents/sds]   <-- SIBLING, outside repo
```

---

## 2. THE MASTER PROGRAMME

```text
SCAN 1  Static / forensic / architecture / governance audit
   ↓
Scan-1 reconciliation
   ↓
FIX-NOW pre-brand remediation
   ↓
SCAN 2  Executable / runtime / build / guardrail audit
   ↓
Scan-2 reconciliation
   ↓
FIX-NOW remediation
   ↓
Re-run Scan 1 + Scan 2 affected checks
   ↓
SCAN 3  Fresh-agent adversarial / red-team audit
   ↓
Final reconciliation
   ↓
PRE-SKYEAGLE CERTIFICATION
```

**The Thiqa → SkyEagle migration is forbidden until final certification passes.**

Category A (pre-existing defects) may be remediated now. Category B (SkyEagle identity changes — renaming
the module, changing tokens to SkyEagle values, replacing logos, changing product strings) must NOT be
implemented yet.

---

## 3. MASTER TASK LEDGER

| ID | Task | Owner | R/W | Status | Evidence / result |
|---|---|---|---|---|---|
| PRE-01 | Establish scan baseline | orchestrator | R | **DONE** | §1 above |
| PRE-02 | Scan-1A Thiqa residual surface | Agent 1A | R | **DONE** | §5, §7 — all 6 area counts reproduced exactly |
| PRE-03 | Scan-1B Translation/catalogue forensics | Agent 1B | R | **NEVER RETURNED** | Superseded by Scan-2E, which did the work live against the DB. Do not re-dispatch. |
| PRE-04 | Scan-1C Guardrail static audit | Agent 1C | R | **DONE** | §6 S1-P1-04, Correction A |
| PRE-05 | Scan-1D Core patch / V-09 | Agent 1D | R | **DONE** | §5 S1-P0-01 |
| PRE-06 | Scan-1E Architecture / persisted state | Agent 1E | R | **DONE** + 3 sub-fork addenda | §9, §10 |
| PRE-07 | Scan-1F Documentation drift | Agent 1F | R | **DONE** | §6 S1-P1-05, S1-P1-06, Correction F |
| PRE-08 | Scan-1 reconciliation | orchestrator | R | **SUBSTANTIALLY DONE** | Every P0 and high-severity P1 independently reproduced by orchestrator. Not formally closed because remediation (PRE-09) has not run. |
| PRE-09 | Scan-1 FIX-NOW remediation | orchestrator | **W** | **IN PROGRESS — 1 of ~6 items done** | ✅ S2-P1-25 manifest gate restored (`45e9eb4f3`). **Remaining:** S1-P0-01 patch records PR-23…PR-30 · S1-P0-09 token-namespace bridge (runtime-prove first) · S1-P0-13 + S2-P0-21 migration/rollback tooling · S1-P1-03 CI wiring · S1-P1-15 brand-neutral backup retention · S1-P1-17 disabled-token contract decision · doc corrections (S1-P1-05, S1-P1-06, S1-P1-10, S1-P2-07). |
| PRE-10 | Scan-2A Guardrail execution proof | Agent 2A → orchestrator | R | **AGENT FAILED (6 empty returns); CLAIM SUBSEQUENTLY PROVEN BY ORCHESTRATOR** | Agent burned ~117k tokens / 63 tool calls with zero findings — **do not re-dispatch it.** Orchestrator proved the inert-rule behaviour directly; see §16 PRE-10. |
| PRE-11 | Scan-2B Test-harness truthfulness | Agent 2B | R | **INTERRUPTED / NO RESULT RECEIVED** | Dispatched; never returned findings. |
| PRE-12 | Scan-2C Generator/theme reproducibility | Agent 2C | R | **DONE** | §15 SCAN2C |
| PRE-13 | Scan-2D Manifest/asset pipeline | Agent 2D | R | **DONE** | §15 SCAN2D |
| PRE-14 | Scan-2E Translation runtime forensics | Agent 2E | R | **DONE** + addendum | §15 SCAN2E |
| PRE-15 | Scan-2F Runtime surfaces | Agent 2F | R | **DONE** (two runs) | §15 SCAN2F |
| PRE-16 | Scan-2G Materialisation / tenant safety | Agent 2G | R | **DONE** — runtime test DESIGNED, NOT EXECUTED | §15 SCAN2G |
| PRE-17 | Scan-2H Telemetry / network egress | Agent 2H | R | **DONE** | §15 SCAN2H |
| PRE-18…24 | Scan-3A…3G adversarial red-team | fresh agents | R | **NOT STARTED** | Gated behind Scan-1 exit |
| PRE-25 | Final reconciliation / certification | orchestrator | R | **NOT STARTED** | — |

**Orchestrator's own guardrail proof:** two direct PHPUnit runs exceeded timeout on the Drive mount without
capturing output. The claim was then **proven by a faster route** — reading the real `MODULE_NAMESPACE`
constants out of the four shipped rule files and evaluating the shipped comparison against both namespaces.
All four rules go inert under a SkyEagle namespace. **PRE-10 is closed.** See §16 PRE-10.

---

## 4. CERTIFICATION STATE

```text
PRE-SKYEAGLE CERTIFICATION:  NOT YET PASSED
SKYEAGLE MIGRATION:          NOT AUTHORIZED

SCAN 1:  COMPLETE as an investigation — every P0 and high-severity P1 independently reproduced by the
         orchestrator, and the last outstanding claim (guardrail inert-rule behaviour) is now PROVEN
         BY EXECUTION (§16 PRE-10). Reconciliation done. REMEDIATION NOT DONE — that is PRE-09.
SCAN 2:  IN PROGRESS — 6 of 8 agent workstreams landed (2C, 2D, 2E, 2F, 2G, 2H).
         2A failed (6 empty returns) but its target claim was proven directly by the orchestrator.
         2B never returned — the zero-match-filter experiment is the only genuinely missing piece.
SCAN 3:  NOT STARTED

KNOWN P0 FINDINGS:  S1-P0-01, S1-P0-09, S1-P0-13, S2-P0-21   (all still open)
REGISTERS:          P0 4 · P1 17 · P2 4   (OPEN findings)
                    FIXED so far: S2-P1-25 (manifest gate, commit 45e9eb4f3).
                    NEW since: S2-P1-26 (English leak surface + uncatalogued leak class).
                    So P1 went 17 -> 16 (fix) -> 17 (new finding). Net movement, not drift.
                    Note: S1-P1-04 is execution-proven but NOT fixed; proving a defect is not
                    repairing it. It stays open until the guardrail constants gain a real
                    cross-check against the production namespace.

SAFE TO START SKYEAGLE:  NO
```

---

## 5. P0 FINDINGS

### S1-P0-01 — Eight undocumented core branding edits (Q1 governance breach)

- **Severity** P0 · **Origin** Agent 1D · **Independently reproduced by orchestrator: YES**
- **Evidence:** `git diff --name-status b91c12aee..HEAD` shows all eight modified (`M`); seven identifiers
  return **0** hits in `docs/branding/adr/patch-records.md`.

```text
M interface/main/tabs/main.php
M src/RestControllers/AuthorizationController.php
M src/RestControllers/SMART/SMARTAuthorizationController.php
M templates/product_registration/product_reg.js.twig
M templates/oauth2/oauth2-login.html.twig
M templates/oauth2/patient-select.html.twig
M templates/oauth2/scope-authorize.html.twig
M webpack.themes.js
```

- **Introducing commits:** `32764921c` (2026-08-10, "feat(branding): add the Thiqa theme and repoint the
  webpack entries"), `5b64dd078` (2026-08-19), `39d3f056b` (2026-08-19).
- **Root cause:** `patch-records.md` was extended incrementally; each later branding commit that touched a
  new file fell outside the tracked set. The file's last edit (`39a64d316`) came *after* `39d3f056b` and did
  not re-audit coverage.
- **Aggravating factor:** `patch-records.md:592-596` actively asserts the oauth2 template edits *"are
  correctly **absent** from this document."* That was true when written and is now false.
- **Footprint:** 25 documented + 8 undocumented = **33** core files. Every doc figure (6 / 7 / 17 / 22) is stale.
- **Owner ruling:** FIX NOW (Category A). Write `PR-23…PR-30`.
- **Required final invariant:** `actual core modifications == patch-record inventory == V-09 scope`.
- **Runtime proof required?** No. **Blocks SkyEagle?** YES.

### S1-P0-09 — Tier-2 token overlay is structurally inert

- **Severity** P0 · **Origin** ORCHESTRATOR (no agent found it) · **Independently re-derived by Agent 2G: YES**
- **Evidence:**

```text
Tier-2 materialised CSS (.../public/branding/default/tokens-light.css) emits 42 properties:
    --brand-navy, --brand-coral, --background, --surface, --interactive-primary-default, ...

Compiled public/themes/style_light.css:
    --thiqa-*  DECLARED   43
    var(--thiqa-*) REFERENCED   9
    var(--oe-*)    REFERENCED   7
    var(--brand-*) REFERENCED   0

Every one of the 42 emitted names tested against every compiled theme file:
    TOTAL REFERENCED: 0 / 42

git grep for --brand-navy / --brand-coral / --interactive-primary-default / --surface-input-on-raised
outside the module: ZERO consumers.
```

- **Root cause:** two parallel CSS custom-property namespaces. `TokenKey::cssVariableName()` emits bare
  `--brand-*`/`--background`; the SCSS pipeline emits `--thiqa-*` plus `--oe-*` aliases. `CssVariableRenderer`
  has **no prefix parameter** — a fixed `match`, no way to emit `--thiqa-`.
- **Agent 2G bridge hunt — no bridge exists.** Ruled out: `OeVariableMap`/`CssVariablesRenderer` (build-time,
  maps `$thiqa-*` → `--oe-*`, no knowledge of bare names); `_bootstrap-bridge.scss` (compile-time, bakes
  literal hex); no `var(--brand-…)` fallback anywhere; no JS `getPropertyValue`. The one candidate —
  `SmartStyleContract`/`brandingToken()` — is itself dead code (SMART twigs hardcode hex; the controller
  never constructs it).
- **Deeper consequence:** even the 9 live `var(--thiqa-*)` references are inert, because their declarations
  are themselves static Tier-1 literals baked at build time.
- **Consequence:** `MVP-010 AC-1` ("tenant can change approved logo and tokenized palette") is unsatisfiable
  for the palette half. A tenant can materialise a valid, WCAG-passing overlay and nothing renders differently.
- **Owner ruling:** Do NOT defer into SkyEagle. **Runtime-prove it in Scan 2, then FIX NOW, brand-neutral.**
- **Required acceptance chain:**
  `allowed token change → materialised CSS → page link → actual CSS consumer → browser computed-style difference`.
  A visible `<link>` alone is NOT sufficient.
- **Runtime proof required?** YES — designed, not executed. See §15 SCAN2G and §16.
- **Blocks SkyEagle?** YES.

### S1-P0-13 — Brand name embedded in a translation catalogue key, with no migration tooling

- **Severity** P0 · **Origin** 1E sub-fork · **Reproduced by orchestrator: YES** · **Runtime-proven via live DB: YES**

```text
lang_constants.constant_name = 'Thiqa Database Upgrade'   cons_id 13235
  28 definitions across 28 languages
  lang_ids: 3,4,5,6,7,8,11,12,13,14,16,17,19,20,21,22,24,27,28,29,30,33,34,37,40,47,50,59
  (22 = Arabic, 7 = Hebrew, 37 = Persian — all RTL)
Source consumers: sql_upgrade.php:159 (raw <title>), sql_upgrade.php:414 (xlt(...), catalogue-backed)
brand-strings.json carry_forward:  "OpenEMR Database Upgrade" -> "Thiqa Database Upgrade"
```

- **Root cause:** the RB-01 repair fixed the OpenEMR→Thiqa orphaning by copying 28 translations onto a NEW
  constant — but put the brand name *inside the key*, re-arming the identical trap for the next rename.
  `brand-strings.json`'s own `why_this_file_exists` field documents that RB-01 destroyed 59 translations
  and that the file exists to prevent exactly this.
- **No guard exists.** `apply-profile`, `materialise`, `provision-report-acl` are all idempotent by
  construction. The two genuinely rename-hostile DB operations — this catalogue key and
  `modules.mod_directory` — have **no migration script, no idempotency guard, no rollback path, and no
  fresh-install-vs-upgrade branching.**
- **Owner ruling:** BUILD safe idempotent migration + rollback tooling NOW. Also investigate the durable
  alternative: remove product identity from catalogue keys and compose the brand outside the constant.
- **Blocks SkyEagle?** YES.

### S2-P0-21 — The RB-01 remediation does not survive a database rebuild

- **Severity** P0 · **Origin** Agent 2E · **Reproduced by orchestrator: YES**

```text
grep -c "Thiqa" contrib/util/language_translations/currentLanguage_utf8.sql   →  0
git grep "Thiqa Database Upgrade" -- sql/ src/ contrib/                        →  no matches
```

- The 28 carried-forward translations **exist only in this one live database.** Absent from the repo, absent
  from the installer's language seed, and no migration recreates them.
- **Worse:** `src/Common/Command/ReleasePrep/Mutator/TranslationFileCopyFromPriorRelMutator.php:37,51`
  overwrites `currentLanguage_utf8.sql` wholesale from the prior release branch — so adding rows to the seed
  would be reverted by the next release-prep run.
- A fresh install, or the DB rebuild documented in `CLAUDE.local.md` §7, comes up with `cons_id 13235`
  absent and all 28 locales silently falling back to the English literal.
- **Total branding DB footprint: 33 rows across 2 cons_ids** (28 carry-forward + 5 English overrides), none
  of which exists in `sql/`, in the seed, or in any migration.
- **Blocks SkyEagle?** YES — arguably the most urgent item, since a rename would be built on ephemeral state.

---

## 6. P1 FINDINGS

### S1-P1-02 — Two `brand-strings.json` overrides are dead configuration
`OpenEMR Authorization` → `Thiqa Authorization` and `OpenEMR Login` → `Thiqa Login` are applied as
`lang_id=1` rows (verified live), but commit `39d3f056b` replaced those constants in the templates with
`{{ applicationTitle }} {{ "Authorization"|xlt }}`. Repo-wide: `OpenEMR Authorization` → **zero** code
references; `OpenEMR Login` → one CSS comment only. The `consumers` arrays are factually wrong.
**Agent 2E resolved the other three as LIVE:** `OpenEMR Application`, `Welcome to OpenEMR`, `OpenEMR` are
still consumed by Zend `.phtml` layouts. So cleanup is exactly **two** entries, not five.

### S1-P1-03 — Branding gates are not wired into CI
`grep -rn "branding-tokens-check|verify-brand-manifest|generate-tokens" .github/` → **nothing**, across
**64 workflows**. CI does run the isolated suite (`isolated-tests.yml:50`) and PHPStan. Token-drift and
manifest verification fire only if a human remembers.
**Owner directive: wire deterministic required CI checks before certification.**

### S1-P1-04 — Nothing cross-checks guardrail namespace constants against the real module
All four rules hardcode `MODULE_NAMESPACE = 'OpenEMR\Modules\ThiqaBranding'`
(`ForbiddenBrandingHttpClientRule.php:55` + `:61`, `ForbiddenBrandingSiteConfigRule.php:40`,
`ForbiddenBrandingTwigPathRule.php:54`, `ForbiddenBrandingPlaceholderDomainRule.php:40`), matched with
`===` / `str_starts_with`. `ThiqaBrandingRuleRegistrationTest` proves **wiring, not matching** — its own
docblock says so. Fixtures and rule constants are mutually consistent *by construction*, so a rename that
misses production stays green.
**Required remediation:** `production namespace changes + rule constant does not → deterministic test failure.`

### S1-P1-05 — WCAG evidence-of-record understates itself
`brand/qa/wcag-contrast-results.json` = **38 pairs / 35 PASS / 3 ADVISORY / 0 FAIL** (verified by orchestrator).
`docs/branding-production/08-wcag-contrast.md` still says 34 / 31 / 3.

### S1-P1-06 — D-8 documented CLOSED, actually RE-OPENED
`RebrandingPlan.md` §6.5 strikes D-8 as "ELIMINATED / RESOLVED by design change" and counts it in
"4 of 13 closed". RB-04 re-opened it. `remaining-dependencies.md` and `ADR-BRAND-001` carry the correction;
`RebrandingPlan.md` — the release-blocking-dependency register — never got it.

### S1-P1-10 — False capability claim in live tooling
`tools/branding/install-assets.php` ~line 458 states the Amiri PDF face is *"registered with mPDF in
`src/Pdf/Config_Mpdf.php`"*. That file has **zero** Amiri references (`grep -c` → 0). Per RB-14/EV-RB14 the
registration cannot work anyway (mPDF cannot parse the font). Correct the claim after Scan-2 confirmation.

### S1-P1-11 — Live production artefacts carry Thiqa operational identity
`docs/evidence/ubuntu-infra-scripts/07-deploy-code-update.sh:3,26,87` pins
`BRANCH=feat/thiqa-branding-foundation`; `05-seed001-demo-date-rebase.sh` installs a **running systemd unit**
`Description=Thiqa demo seed…`; README references R2 bucket `thiqa-demo-backups`.
**Do NOT blindly rename.** Each needs classification: active product identity / operator label /
infrastructure historical name / expensive live resource / safe rename / preserve+document.

### S1-P1-15 — Backup retention breaks silently on a prefix rename
`src/Console/BackupCommand.php:46` `DEFAULT_TARGET = 'C:/openemr-stack/backups'` (host-specific Windows path
in a shippable module); `:95` writes `thiqa-<label>-<Ymd-His>.sql`; `:153` prunes
`glob($target . '/thiqa-*.sql')`. Rename the prefix and the glob stops matching every pre-existing backup —
never pruned, and `glob()` returning fewer files than `--keep` is indistinguishable from a healthy young
archive. **No error.** Owner directive: build brand-neutral migration-safe retention now. Test matrix:
old prefix only / new prefix only / mixed archive / keep=N / zero matches / unexpected files / rollback.

### S1-P1-17 — One token is tenant-overridable with no contrast gate, and the doc says the opposite
`TokenKey::InteractivePrimaryDisabled` is in the `=> true` arm of `isTenantOverridable()` **and** the `null`
arm of `contrastRule()`. Net: **11 overridable, 10 contrast-gated.** `architecture.md` §3 annotates the row
*"Yes, except interactive.primary.disabled"*, contradicting its own "Exactly 11" total (8+2=10≠11).
A tenant value for this key passes on hex-format validation alone. Not a WCAG violation (inactive controls
are exempt) but the contract must become deliberate.
**Decision required:** keep overridable with a distinguishability constraint / make non-overridable /
keep and document why. Source and docs must stop contradicting.

### S2-P1-18 — The live tenant is in an INCONSISTENT branding state
`php bin/console thiqa-branding:verify --site=default`:

```text
Status: inconsistent · Revision: 0 · Materialised: never
Light/Dark token stylesheet: present
[ERROR] This tenant's branding state is not self-consistent.
```

`saas_branding_revision`, `_materialised_at`, `_tokens_light`, `_tokens_dark` are all **empty**, yet both
generated stylesheets exist on disk dated 2026-08-10 18:50. **This refutes RB-11's own correction**, which
recorded `Status: healthy, Revision: 1, Materialised at 2026-08-10T18:50:40+00:00`. The documentation has
now drifted twice on the same fact, in opposite directions. The empty revision propagates into live HTML
as `&rev=0` on the login logo. Root condition: `BrandingHealthCheck` cross-references the revision (route a)
against file-existence on the *unserved* route (b). **Nothing gates on this exit code** — no CI, no deploy script.

### S2-P1-20 — A ledger item marked FIXED is not fixed (RB-22)
Verified by orchestrator:

```text
Inter-Regular   48256  3100e775e8616cd2
Inter-Medium    48256  3100e775e8616cd2     all four byte-identical
Inter-SemiBold  48256  3100e775e8616cd2
Inter-Bold      48256  3100e775e8616cd2
IBMPlexSansArabic-Regular  42848  6010e7fd…   control: distinct
IBMPlexSansArabic-Bold     44280  04c730b4…
```

RB-22 is recorded **"FIXED — rebuilt and verified"** (2026-08-16). The four declared Inter weights are one
face under four names, so `font-weight: 500/600/700` all resolve identically — the Latin surface has no real
weight axis. The manifest *records* the duplication and passes it, because it only checks self-consistency,
never that four declared weights are four distinct faces.

### S2-P1-22 — Live Arabic brand leak on five surfaces
No `lang_id=22` (Arabic) row exists for any of the five override constants:

```text
OpenEMR                has_arabic=0  (8 defs)
OpenEMR Application    has_arabic=0  (9)
OpenEMR Authorization  has_arabic=0  (5)
OpenEMR Login          has_arabic=0  (5)
Welcome to OpenEMR     has_arabic=0  (9)
```

English overrides live at `lang_id=1`. With no Arabic row, `xl()` falls through to the **English source
literal** — still the *unbranded* `OpenEMR …` string. Arabic users see OpenEMR branding today.

### S2-P1-23 — Commit `39d3f056b` introduced an RTL regression
It replaced `{{ "OpenEMR Authorization"|xlt }}` with `{{ applicationTitle }} {{ "Authorization"|xlt }}`.
`Authorization` (cons_id 992) has 10 definitions and **no Arabic row**, where the retired constant had 5.
Word order is hardcoded English, so RTL renders `Thiqa الولوج`. **Correction to an earlier orchestrator
statement:** this commit was praised as "a better mechanism that destroys no translations" — the first half
holds, the second was incomplete. Recommended fix is a placeholder-bearing translatable key
(`xlt('%s Database Upgrade')` + `sprintf`), not bare juxtaposition.

### S2-P1-24 — The Arabic wordmark is stored but never rendered
`globals.saas_branding_product_name_ar = ثقة` is populated and module code to consume it exists
(`BrandingService.php:107-108`, `BrandAssetResolver.php:74,164-166`). The Arabic authenticated shell
(77,733 B, RTL stylesheets applied, UI chrome genuinely translated) still shows `<title>Thiqa</title>`,
`var WindowTitleBase = "Thiqa"`, `حول Thiqa` (verb translated, product name Latin), and a byte-identical
navbar with no Arabic logo variant.

### S2-P1-26 — English brand-leak surface the rename does NOT cover, and a class SET-TRANSLATION cannot reach
**Origin** Agent 2E addendum 3 · **Method:** 69 brand-bearing keys dumped, one `git grep -F -f` pass over
`interface/ library/ src/ templates/ portal/ modules/ ccdaservice/`, filtered to lines containing an actual
translation call → **55 live call-site lines**.

- **23 of 69 brand-bearing keys are reachable; 46 are dead catalogue entries.** Of the 23, only **3** carry an
  English override — so **20 keys are reachable and unbranded**, rendering `OpenEMR…` verbatim in an English
  session. Most exposed: `interface/login_screen.php:29` — *the login page*.
- **Structural finding — a class of leak SET-TRANSLATION cannot fix.** Several literals passed to `xl()`/`xlt()`
  have **no `lang_constants` row at all** (verified `COUNT(*) = 0`): `OpenEMR Website`
  (`interface/main/tabs/main.php:490`) · `OpenEMR Patient Portal` (`portal/index.php:118`) · `Restart OpenEMR`
  (weno module) · two theme-globals labels (`library/globals.inc.php:4437,4439`) ·
  `Permission to use the OpenEMR standard api.` (×2) · the product-registration consent text.
  `brand-strings.json`'s entire premise is that a leak can be fixed as catalogue data instead of a source
  edit. **For this class there is no row to override, in any locale.**
- **This is a positive argument for the composed `applicationTitle` architecture** — it is the only mechanism
  that covers both classes uniformly. And it is already the established convention here: **7 call sites deep**
  (`AuthorizationController.php:836` **and `:1111`**, `SMARTAuthorizationController.php:290,319`,
  `about_page.php:48`, `main.php:573`, `about.html.twig:4,10`, `general_list.html.twig:4`,
  `product_reg.js.twig:6`). Adopt it **with the S2-P1-23 correction**: a placeholder-bearing translatable key
  (`xlt('%s Database Upgrade')` + `sprintf`), never bare juxtaposition, so word order stays translator-controlled.
- **Lower bound, not a total:** the pathspec excluded repo-root files (so `sql_upgrade.php` does not appear),
  plus `contrib/`, `sites/`, `Documentation/`, `tests/`; fixed-string matching also misses literals split
  across lines.

### S2-P1-25 — Brand manifest release gate was RED (4 files) — ✅ **FIXED, commit `45e9eb4f3`**

> **STATUS: FIXED_AND_REVERIFIED (Rev 3).** Gate restored to `123/123 verified`, **exit 0**, manifest line
> count preserved at 123. Reason recorded in `docs/branding-production/12-release-verification.md` Revision 5.
> The *process* gap it exposed is NOT fixed and remains open as **S1-P1-03** — the verifier is still invoked
> by zero of the 64 workflows, which is why a red gate survived five days undetected. Original finding retained
> below as the record.

**Continuation verification (2026-08-24):**

```yaml
TASK/FINDING ID: S2-P1-25
Previous status: FIXED_AND_REVERIFIED (inherited checkpoint claim)
Current status: FIXED — VERIFIED
What changed: No duplicate repair. Independently verified landed commit 45e9eb4f3.
Files changed by repair: brand/manifests/SHA256SUMS; docs/branding-production/12-release-verification.md
Commit hash: 45e9eb4f3944fed783ac0ef48673cedb32b26ca2
Tests/commands executed: git show of commit and both changed files; php tools/branding/verify-brand-manifest.php
Exact exit codes: git show 0; manifest verifier 0
Tests and assertions executed: Manifest entries 123; verified 123; problems 0
Runtime evidence: START 2026-08-24T06:38:07.1424797Z; END 2026-08-24T06:38:14.7066533Z; duration 7.564 s; no timeout
Independent verification: Five hashes were re-issued (four drifted documents plus the explanation document); no entries deleted; Revision 5 durably records why.
Rollback method: Branch at the parent commit and cherry-pick subsequent accepted changes; do not use destructive reset/revert.
Remaining risks: S1-P1-03 remains open because CI still does not run the otherwise-correct verifier.
Next incomplete task: E2 / S1-P0-01 invariant verification for aebcfdfc5 and 26c32fcb3.
```

`php tools/branding/verify-brand-manifest.php` → **TRUE exit code 1**, `119/123 verified, 4 problem(s)`:

```text
docs/branding-production/11-asset-manifest.md
docs/branding-production/13-final-qa-matrix.md
docs/branding-production/16-conflict-resolutions.md
docs/branding-production/FINAL-GROUP-1.5B-CERTIFICATION.md
```

All four carry `FLAGGED FOR HUMAN REVIEW (2026-08-19)` notes. The manifest was last re-issued `b3b821ffa`
(2026-08-10); the 2026-08-19 correction pass edited these four and never re-issued their hashes.
**This is exactly the RB-25 failure mode, whose standing obligation ("run the verifier before AND after
editing anything under `docs/branding-production/`") was not followed.**
**The verifier itself is correct — it exits 1.** An earlier orchestrator suspicion of a silent-gate defect
was a pipeline artefact (`$?` after `| tail` reports tail's status) and is WITHDRAWN.
**Remediation:** re-issue the four hashes with a recorded reason. **Re-issue, never delete.**

---

## 7. P2 / METHOD FINDINGS

### S1-P2-07 — CSS release-count mismatch
`docs/branding/runbook.md` §4 and `docs/branding/changes.md` row 076 say "17 CSS files … + 3 non-theme
shells" while enumerating **4** shells. Actual: **18 `.css` files** at top level (+1 `misc/` directory = 19
entries). `docs/demo-deployment-readiness.md:1591` already says 18. Correct runbook + changes.md to 18/4.

### S1-P2-12 — Module-directory literal hand-typed five times
Canonical: `BrandingService.php:60-61` (`TOKEN_STYLESHEET_RELATIVE_PATH`), `Bootstrap.php:74`
(`TWIG_NAMESPACE`, referenced symbolically everywhere — safe). Non-canonical duplicates that fail
**silently**: `Bootstrap.php:117` (dark-logo web path), `Bootstrap.php:246` (duplicates the service constant),
`tools/branding/install-assets.php:497` (third independent copy of the dark-logo subpath), plus
`Bootstrap.php:208`/`:244`/`BrandingService.php:57` for the token-JSON path.
Counter-example worth copying: `tools/branding/src/CliOptions.php:27` `DEFAULT_FONT_URL_BASE` is a single
parameterised source of truth.

### S1-P2-14 / PRE-ORCH-01 — Sibling worktree outside exclusion coverage
`.git/info/exclude:11` excludes `**/.claude/worktrees/`, covering three worktrees. It **cannot** cover
`G:/My Drive/OpenEMR.worktrees/sds` — a sibling directory outside the repo root, registered as a git
worktree at `631f2b38c` (pre-branding baseline). `RebrandingBugs.md` §10's measurement-hygiene rule names
only `.claude/worktrees/`. Any scan rooted at `G:\My Drive\` ingests a full pre-branding codebase copy.
Must be added to: measurement hygiene · exclusion documentation · Scan-3 rollback register.

### S1-P2-16 — Architecture doc undercounts commands 3 → 6
Actual: `apply-profile`, `backup`, `materialise`, `provision-report-acl`, `seed-demo`, `verify`.
The three omitted own the **most** persisted state (gacl ACL objects, ~19 clinical/demo tables, backup
artefacts) — none of it branding state. The module has accreted a deployment/ops toolkit under a branding
namespace, so "rename the branding module" has a much wider blast radius than the doc implies.

---

## 8. CORRECTIONS / REFUTED CLAIMS — DO NOT REINTRODUCE

**Correction A — CI does NOT embed a `ThiqaBranding` filter.**
`ThiqaBranding` appears in **zero** of: `composer.json`, any `phpunit*.xml`, any of the 64 workflows. CI runs
the whole isolated suite unfiltered (`isolated-tests.yml:50`). The filter string exists only in local runbook
documentation (`AuditRebranding.md`, `RebrandingBugs.md`, `demo-deployment-readiness.md`). An earlier
orchestrator claim that "the gate command itself embeds the namespace" was WRONG for CI. Do not reassert it.

**Correction B — `--thiqa-*` custom properties = 43, not 44.** Three independent methods agree.

**Correction C — `$thiqa-*` SCSS variables = 96, not 97.** (Raw regex gives 101; 5 are interpolation fragments.)

**Correction D — "everything else" area = 19 files / 50 lines, not 18/49.** Re-derive before certification.

**Correction E — "`brand.coralDeep` has zero consumers" was too broad.** Zero SCSS consumers, but it IS
rendered live by `CssVariableRenderer` as `--brand-coral-deep`. This two-namespace distinction is what
exposed S1-P0-09.

**Correction F — `docs/branding/architecture.md` is NOT a reliable source.** Falsified in four places:
Plane 2 "materialise never ran" (it ran; artefacts on disk); §3 `interactive.primary.disabled` overridability;
§8.4 command count 3 vs 6; E12 "lang_definitions seam mostly unused" (actively written).
**Verify architecture claims against code/runtime, never inherit them.**

**Correction G — `branding-tokens.php` DOES exist.** A Scan-2F run claimed *"No `branding-tokens.php` file
exists anywhere in the tree."* FALSE — it is 7,537 bytes at
`interface/modules/custom_modules/oe-module-thiqa-branding/public/branding-tokens.php`, referenced at
`BrandingService.php:61`, and returns HTTP 200 (`Cache-Control: no-store`, 0 bytes) because the overlay is empty.

**Correction H — the manifest verifier exits 1 on mismatch.** An earlier suspicion of a silent-gate defect
was a measurement artefact of piping through `tail`. WITHDRAWN.

**Correction I — `Set-Cookie: App=OpenEMR` is NOT a new finding.** It is **BRAND-089** in the Group 1
inventory (`rebranding.md` §5.7, §9.8), action **PRESERVE**, frozen under locked decision **Q17** /
constraint **C6**, together with the `OpenEMR=<sid>` session-cookie name (BRAND-131). Deliberate and
out of scope for the rename. Do not re-litigate as a leak.

**Correction J — `setup.php` has ZERO upstream commits** since the fork base, contradicting PR-10's
"high upstream churn" rating. Actual highest-churn recorded file is `interface/globals.php` (6 commits),
rated only "Medium". V-09 re-run live gives **47 conflicting paths**, not the ~10 previously recorded —
though the "no branding-patch file conflicts" part holds (the one recorded file that conflicts,
`EncounterService.php` / PR-14, is a non-branding defect fix).

---

## 9. PERSISTED-STATE MODEL — NOT JUST `globals`

**Four database surfaces + filesystem state.** Do not let a future session assume the DB migration is
`globals` alone.

| Surface | Scope | Rename impact |
|---|---|---|
| `globals` | ~40 branding keys (33 inherited + 7 `saas_branding_*`) | values stale until `apply-profile` re-run — idempotent, safe |
| `modules` | **1 row**, no git seed anywhere | **self-disables the module** — see below |
| `lang_constants` / `lang_definitions` | 33 rows / 2 cons_ids, one brand-bearing KEY | orphans 28 translations if mishandled |
| gacl ACL | 2 ACOs (`patients\|bulk_rep`, `patients\|op_rep`) | brand-neutral objects; only the provisioning command name moves |
| demo tables | ~19 clinical tables via `seed-demo` | synthetic, regenerable, HISTORICAL |
| backup filesystem | `C:/openemr-stack/backups/thiqa-*.sql` | prefix-dependent prune — silent break (S1-P1-15) |
| materialised runtime CSS | `<module>/public/branding/<site>/tokens-{light,dark}.css` | orphaned at old path; `.gitignore:73` stops matching |
| installed fonts | `public/assets/fonts/thiqa/` (untracked) | CSS 404s until reinstalled + rebuilt; stale dir must be purged |
| compiled themes | `public/themes/` (untracked) | output filenames deliberately brand-neutral — **unaffected** |

**`modules.mod_directory` is the single highest-severity rename hazard.** `ModulesApplication.php:141-161`
concatenates it into the include path; on failure line 155 runs
`UPDATE modules SET mod_active = 0 WHERE mod_directory = ?` — the module **force-disables itself in the
database**. A second gate, `checkModuleScriptPathForEnabledModule()` (`:86-106`), re-derives `mod_directory`
from the requested URL and throws `AccessDeniedException` (403) on any direct hit before bootstrap even runs.
**No SQL install script anywhere in the repo inserts this row** — it is admin-UI-created runtime state.

Live values at checkpoint: `mod_id=6, mod_name='Thiqa Branding', mod_directory='oe-module-thiqa-branding', mod_active=1`.

**`saas_branding_*` is genuinely brand-neutral** (locked Q58 reserved prefix; `BrandingGlobalKey.php:46`
`LAYER_PREFIX`; ownership derived structurally via `isLayerOwned()`). **Do NOT rename** — it would orphan
every materialised tenant's globals.

**Already-live inconsistency:** `BrandingGlobalKey.php:49` sets `PRODUCT_URL = 'https://skyeagle.uk/'` while
`:182` still defaults `openemr_name` to `'Thiqa'`. A user today sees a "Thiqa Health Information System"
tooltip linking to `skyeagle.uk`.

---

## 10. MODULE RENAME BLAST RADIUS (classes, not immutable counts — re-derive before migrating)

~88 namespace-declaring files · ~42 tests consuming the namespace · 24 PHPStan fixtures · 6 console commands ·
21 documentation files invoking commands as copy-paste runbook text · Twig namespace (`Bootstrap.php:74`) ·
module directory · 5 hand-typed module-path literals · webpack source paths · `.gitignore:73` ·
`modules` DB registration · `.phpstan/phpstan.github.neon:23` fixture exclude path ·
`tests/Tests/Isolated/Common/Twig/TwigTemplateCompilationTest.php:54` · live Ubuntu deploy script branch pin.

**Must NOT be renamed:** `saas_branding_*` (Q58) · webpack **entry names** · CSS output filenames
`style_light.css` / `style_dark.css` (the `file_exists()` fallback at `interface/globals.php:476` hardcodes
the literal) · GPL/regulatory/trademark assets (C7) · `SessionUtil` identity constants (Q17/C6).

---

## 11. OWNER DECISIONS KG-01 … KG-09 — DO NOT REOPEN

- **KG-01** Full internal module identity rename during the future SkyEagle migration. Preserve genuinely
  brand-neutral identifiers. End state must not be a SkyEagle UI on a Thiqa-named active module.
- **KG-02** SkyEagle CTA maps to `interactive.primary`. Thiqa's coral-primary/navy-secondary mapping does
  not govern. Construct `interactive.secondary` separately from the approved palette.
- **KG-03** Authoritative colours: primary `#0B376E`, accent `#1E5A96`, link `#0B4E91`. These override
  artwork-sampled (`#093A74`/`#155496`) and live-site (`#0B1B4D`/coral) values. Recolour derivatives from
  the approved vector; **geometry, proportions, negative spaces, beak direction, S curvature, E bars,
  feather forms and silhouette must not change.**
- **KG-04** Preserve validated functional/semantic colours; mechanically derive structural values; classify
  every token's provenance (O/P/D/N). Surface any material new visible brand decision before execution.
- **KG-05** No AI redesign of the trademark. Dedicated approved Arabic wordmark preferred; until available,
  the approved symbol + English wordmark is an explicitly **disclosed interim**, never an AI fabrication.
  Arabic textual brand name remains `سكاي إيجل`.
- **KG-06** Theme labels become `SkyEagle Light` / `SkyEagle Dark`. CSS filenames unchanged. Admin selector
  behaviour (filename-derived "Light"/"Dark") preserved.
- **KG-07** Carry the Arabic-PDF Option C deferral forward. Do not reintroduce Amiri. Do not claim Arabic
  PDF support that has not passed real rendering.
- **KG-08** Light theme intentionally flat: `background = surface = #FFFFFF`. Hierarchy via borders, sunken
  surfaces, spacing, elevation.
- **KG-09** Resolve telemetry policy and RB-04/D-8 rather than carrying both silently into SkyEagle.
  **Update from Scan 2H:** telemetry is already OFF by three independent mechanisms with live proof —
  this half is a closed risk with a stale comment, not an open one.

---

## 12. PROVISIONAL 43-KEY SKYEAGLE TOKEN PLAN — NOT IMPLEMENTED

```text
STATUS: PROVISIONAL FUTURE SKYEAGLE TOKEN SPECIFICATION — NOT IMPLEMENTED
Do NOT write brand/tokens/skyeagle-tokens.json yet.
```

Provenance categories: **O** Owner-supplied · **P** Preserved semantic · **D** Mechanically derived ·
**N** Newly proposed.

Validation achieved at proposal time: **20/20 code-gated pairs PASS** (light + dark), **8/8 preserved
semantic pairs PASS**, WCAG implementation verified by reproducing 5 known Thiqa figures exactly
(15.72 / 6.34 / 6.85 / 9.73 / 7.78).

Derivation rules used: hover = `shade(default, 12–14%)` · active = `shade(default, 22%)` ·
disabled = `mix(default, background, 25%)` · dark tints = `tint(light, 35–55%)` ·
`divider` = `tint(border,45%)` light / `shade(border,25%)` dark · `borderStrong = text.secondary` ·
`surfaceInput = surface` · `surfaceInputOnRaised` = surface (light) / background (dark).

Values needing Owner eye when implemented: the whole `interactive.secondary` family (both themes),
`divider` ×2, dark `surfaceSunken`, dark `brand.navy/coral/coralDeep` tints.

Key-name assumption recorded: the 43 key *names* stay unchanged (`brand.coral` will hold a blue), per the
Owner's own principle "do not rename functional identifiers merely for cosmetic naming."

> **These results MUST be revalidated after S1-P0-09 is repaired and before `skyeagle-tokens.json` is
> ever written** — the token→CSS delivery contract is exactly what the repair changes.

---

## 13. ASSET DECISIONS

- **Master geometry reference:** `G:\My Drive\OpenEMRWebSite\docs\Assets\Intial Logo.png` (577×570).
- One unambiguous master mark; **no competing geometry** — no Owner decision needed on "which mark".
- All SVGs in that tree are Recraft **vectorize traces** of the raster, confirmed by paired API-response
  JSONs. **No hand-authored bezier master exists.**
- **No dedicated Arabic wordmark exists.** `Entity.md` requires `سكاي إيجل` and forbids deriving it from
  the Latin artwork.
- **Zero GIF files** — two legacy slots (`login_logo.gif`, `practice_logo.gif`) require GIF.
- `derived/lockups/skyeagle-logo-horizontal-on-dark.png` is **defective** — wordmark renders as outline
  only, near-invisible on dark. The companion SVG is correct. Re-export from vector.
- **Two prior AI generation attempts on the trademark were explicitly rejected by the Owner**; the accepted
  pipeline is **vectorize-and-derive only**.
- Future rule: deterministic export for trademark assets. **Recraft must NOT redesign the SkyEagle symbol.**
  Recraft may be used for non-trademark supporting/evidence/mockup assets, always with the approved master
  attached as reference.
- **No Recraft call has been made during this pre-brand audit.**

---

## 14. THIQA RESIDUAL SURFACE (Scan-1A, all six area counts independently reproduced)

| Area | Files | Lines |
|---|---:|---:|
| module `oe-module-thiqa-branding` | 94 | 330 |
| `tests/` | 96 | 586 |
| `interface/themes/` | 9 | 565 |
| `tools/` | 27 | 356 |
| `brand/` | 5 | 230 |
| `docs/` | 107 | 1017 |
| everything else | **19** | **50** |
| tracked paths containing "thiqa" | 235 | — |
| `THIQA-###` asset IDs | 107 | — |

**Zero-hit areas (negative evidence):** `library/`, `portal/`, `public/` (tracked), `sites/`, `config/`,
`bin/`, `.github/`, `Documentation/` (1,726 files), `docker/`, `sql/`, `swagger/`, `contrib/`,
`Locked Desicions/`, `composer.json`, `composer.lock`, `package.json`, all `phpunit*.xml`, `phpstan.neon.dist`.

**Core literals (24 across 8 files).** Catalogue-key-bearing (RB-01 hazard) — handle via carry-forward only:
`sql_upgrade.php:414` `xlt("Thiqa Database Upgrade")` · `templates/error/{400,404}.{html,json}.twig` ·
`templates/error/general_http_error.html.twig` · Installer `index.phtml:36`.
Safe raw literals (direct edit OK): `admin.php:40,53` · `setup.php` ×10 · `sql_patch.php` ×3 ·
`ippf_upgrade.php` ×3 · `interface/globals.php:93,100` · `FhirMetaDataRestController.php:75` ·
`OAuth2AuthorizationListener.php:108`.

**Semantic dependency with NO "thiqa" literal:** `ThemeVariant.php:46-47` returns `'Saudi Light'`/`'Saudi Dark'`
— invisible to any "thiqa" grep. **Zero call sites** — dead code; admin selector shows filename-derived
"Light"/"Dark" from `edit_globals.php:737-743`.

**Measurement blind spots in the project's canonical scan** (`xlt?\(\s*['"][^'"]*OpenEMR` over
`src/ interface/ library/ templates/ portal/`): (1) root-level files are outside the scanned dirs — which is
where the highest-risk string lives; (2) Twig filter syntax `{{ "X"|xlt }}` doesn't match the function-call
regex. Corrected BRAND-102 figure: **49 occurrences / 21 files**, not 46/20.

---

## 15. SCAN-2 RESULTS THAT LANDED

### SCAN2C — Generator / theme reproducibility: **PASS**
- `generate-tokens.php --check` → `12 branding artefacts are up to date (6 preview, 6 deployed)`, **exit 0**,
  byte-identical on two runs.
- All 6 `DeployedArtefacts::DEPLOYMENT_MAP` targets exist; zero `(missing)`.
- `interface/themes/thiqa/` = **4 GENERATED** (`_tokens-light`, `_tokens-dark`, `_css-variables`,
  `_typography`) + **3 HAND-AUTHORED** (`_bootstrap-bridge`, `_overrides`, `_theme-colors`).
  **A SkyEagle token regeneration will NOT touch the 3 hand-authored files.**
- **Negative control executed** (scratchpad harness, repo untouched) — 6 mutation tests:
  SCSS 1-byte hex change **CAUGHT** · trailing blank line **CAUGHT** (byte-exact) ·
  Twig header prose **NOT caught** (by design, header stripped) · Twig JSON body value **CAUGHT** ·
  trailing whitespace after JSON **NOT caught** (`payloadOf()` `trim()`) · file deleted **CAUGHT**.
- `public/themes/` = 18 `.css` top level + 10 in `misc/`; expected 15 entries + 3 kept + 10 = 28. **Exact match,
  zero stale files.** Q77 compliant — zero `solar`/`manila`/`cobalt_blue`/`forest_green`.
- **Not stale:** compiled output 2026-08-16 postdates newest Thiqa source 2026-08-10 by 6 days.
- SMART twigs confirmed **baked literal hex**; `logo_primary` (`{{ logo.primary }}`) is the only live variable.

### SCAN2D — Manifest / asset pipeline
- `SHA256SUMS` **123** entries = 107 `brand/**` + 16 `docs/branding-production/*.md`.
  `asset-manifest.{csv,json}` = **107** each, path sets and hash sets identical to each other.
- **Hash divergence:** `brand/typography/typography-tokens.json` — SHA256SUMS `aaf223c7…` vs CSV/JSON
  `88e22544…`. On-disk matches SHA256SUMS; **CSV/JSON are stale**, and the verifier never reads them.
- All 123 paths exist. Content check: **119 OK / 4 FAILED** (the four documents in S2-P1-25).
- **32 `AssetMapping` rows** in `install-assets.php` (not ~25): 18 inline + 3 dark-variant + 3 PDF font + 8 font.
  All 32: source exists, in manifest, target exists, byte-identical.
- **Deny-list** (`install-assets.php:73-130`) consulted FIRST in `installOne()` — before source existence and
  hashing, so no mapping edit can bypass it. Protects `cms1500.png`, `ub04.svg`, the card-network GIF, and
  everything under `Documentation/` (locked constraint C7). Currently 0 `Denied` rows — pure defence in depth.
- **Verifier is one-directional:** detects manifest→disk drift, never disk→manifest. 4 unmanifested files
  under `brand/` (all metadata: `.gitattributes`, the 3 manifest files) pass silently.
- Asset-count mystery resolved: 108 files under `brand/` minus manifests, minus 1 `.gitattributes`
  (not an asset) = **107**, matching 107 `THIQA-` IDs.

### SCAN2E — Translation runtime forensics (live DB)
- `lang_constants` **13,235** · `lang_definitions` **237,542** · `lang_languages` **59**.
- `Thiqa Database Upgrade` cons_id **13235**, 28 defs; source `OpenEMR Database Upgrade` cons_id 7567 also
  still holds 28 (now dead weight). Identical lang_id sets. 22 of 28 carried the literal and were substituted.
- **`lang_id=1` holds exactly 5 rows in the entire 237,542-row table** — precisely the five
  `english_overrides`. Upstream ships zero English definitions because the key *is* the English string.
  27 Thiqa-bearing definitions total = 22 substituted + 5 overrides. **Every Thiqa row is accounted for.**
- **69 constants carry `OpenEMR` in the KEY; 104 distinct constants carry it in at least one definition;
  1,077 definition rows contain the literal.** Of the 69, exactly 5 have an English override — **the other
  64 render `OpenEMR…` verbatim in an English session** wherever reached. Reachability untested — a separate scan.
- Arabic is nearly clean: only 2 Arabic rows contain the literal (cons_id 19, cons_id 678).
- Referential integrity clean: **0 orphaned definitions, 0 duplicate `(cons_id, lang_id)` pairs.**
- **32 duplicate `constant_name` groups** (29 trailing-whitespace variants, 3 genuinely distinct under
  `BINARY`). `xl()` ends in `LIMIT 1`, so which row wins is index-order-dependent.
  **Any migration MUST key on `cons_id`, never `constant_name`.**
- Full forward-migration SQL with assertions, merge branch and lossless rollback is recorded in the agent
  result; core assertions: `lang_constants` count 13235→13235 · defs 28→28 · substituted exactly 22 ·
  orphans 0→0 · duplicate pairs 0→0.

### SCAN2F — Runtime surface baseline (two runs; second authenticated)
| Surface | Status | Branding |
|---|---|---|
| Login | 200, 9165 B | `<title>Thiqa Login</title>`, tagline "Clinical confidence, connected care.", `alt="Thiqa logo"`, logo `?t=1786356307&rev=0` |
| Favicon ×2 paths | 200, 15086 B | byte-identical, sha256 `4026d768…` |
| Theme CSS | 200, 328066 B | `?v=82`, static serve |
| Tier-2 endpoint | **200, 0 bytes** | `Cache-Control: no-store`, **no `Set-Cookie`** |
| Tier-2 `?site=default` | **400** | `"This endpoint does not accept a site parameter."` |
| `admin.php` (unauth) | 200 | "Thiqa Site Administration" / "Thiqa Multi-Site Administration", **0** `OpenEMR` |
| `acknowledge_license_cert.html` | **403** | Apache deny rule working as documented |
| Authenticated shell | 200, 70059 B | `<title>Thiqa</title>`, `WindowTitleBase = "Thiqa"`, navbar `title="Thiqa Health Information System"` + `href="https://skyeagle.uk/"` |
| Arabic shell | 200, 77733 B | RTL stylesheets applied, UI translated, **product name stays Latin** |
| FHIR metadata | 200, 35805 B | `software.name: "Thiqa"`, `implementation.description: "Thiqa FHIR API"`, **0** `OpenEMR`, 0 `skyeagle` |
| smart-style | 404 | `{"message":"Thiqa Error: API is disabled"}` |
| Portal | **500** | `portal_onsite_two_enable` empty — disabled |

- **RB-06 is better than documented:** `?site=` is explicitly **rejected** with 400, not silently ignored.
- APIs globally disabled: `rest_api`, `rest_fhir_api`, `rest_portal_api` all empty.
- All 9 logo slots resolve 200; 4 slots exist but are emitted by no page (both login secondaries, both smalls).
- **Login page emits its `<head>` TWICE** — duplicate favicon/stylesheet. This is BRAND-119, still present.
- Residual `OpenEMR` in the shell: `var oemr_session_name = "OpenEMR"` + 3 JS comments — all BRAND-089/131
  PRESERVE (Q17/C6), not leaks.

### SCAN2G — Tier-2 effectiveness (independently CONFIRMED S1-P0-09)
See §5 S1-P0-09. Additional:
- **Static token CSS is directly fetchable over HTTP:** `GET .../public/branding/default/tokens-light.css`
  → 200, `text/css`, 1522 B. **No `.htaccess` protects the path.**
- RB-04/D-8: route (a) PHP endpoint is the only route ever linked; route (b) files are written
  **unconditionally**, even with an empty overlay; only `FilesystemStylesheetProbe::isPresent()` reads them,
  and only via `is_file()` (never opens). `.gitignore:73` confirmed matching via `git check-ignore -v`.
- `verify` inconsistency root: `BrandingHealthCheck` cross-references the revision (route a) against
  file-existence on the unserved route (b) — "measuring the wrong thing".
- **Runtime test DESIGNED, NOT EXECUTED** — see §16.

### SCAN2H — Telemetry / network egress: **OFF, triple-gated, live-proven**
- **External hosts contacted on a real page load: NONE.** Egress scan of login + authenticated shell found
  zero off-host fetches. Only external reference is an `<a href>` to `skyeagle.uk` requiring a click.
- Live: `var telemetryEnabled = 0;` in the authenticated shell.
- Three independent gates, any one sufficient: (1) `product_registration.telemetry_disabled = 1`;
  (2) `enable_usage_telemetry` absent from both `library/globals.inc.php` and the `globals` table, so the
  opt-in gate at `TelemetryService.php:95` returns 0 and is **not settable via admin UI**;
  (3) no `Telemetry_Task` row in `background_services` — the scheduled reporter is never dispatched.
- Endpoint if ever opened: `https://reg.open-emr.org/api/usage?SiteID=<uuid>` (`TelemetryService.php:202`)
  — would transmit installation UUID, server geolocation (via 4 third-party IP services, **2 over plaintext
  HTTP**), version, OS/PHP fingerprint, patient/portal-patient/user counts, module counts, encounter forms,
  UI click labels. **No PHI.** Tenant-identifying and commercially sensitive for a white-label product.
- **`executeCurlRequest()` sets NO cURL timeout** (`:317-341`) — latent availability risk on a page-driven
  path if the gate ever opens.
- **Stale comment:** `TelemetryService.php:67` says `product_registration` is empty; it holds 1 row. The
  safety conclusion holds via `telemetry_disabled=1`, but the stated reason is wrong.
- Branding module zero-network guarantee independently re-verified by grep: no `curl_`, no HTTP client
  imports, all 9 `file_get_contents`/`fopen` calls are local paths.
- `trackApiRequestEvent()` has **no callers** — dead code.

---

## 16. UNFINISHED WORKSTREAMS

```text
STATUS: INTERRUPTED / PENDING AT SESSION CHECKPOINT
Do NOT assume results. Do NOT restart the whole programme.
Resume or re-run ONLY these workstreams.
```

**PRE-10 / Scan-2A — Guardrail inert-rule behaviour — NOW PROVEN BY EXECUTION.**

Agent 2A failed six times (~117k tokens, 63 tool calls, zero findings). **Do not re-dispatch it.** Two direct
PHPUnit attempts also exceeded timeout on the Drive mount. The orchestrator then proved the claim by a faster
route: reading the **real `MODULE_NAMESPACE` constants out of the four shipped rule files** and evaluating the
exact shipped comparison (`$c === $ns || str_starts_with($c, $ns . '\')`) against both namespaces.

```text
ForbiddenBrandingHttpClientRule        const = OpenEMR\Modules\ThiqaBranding
    Thiqa (current)         OpenEMR\Modules\ThiqaBranding\Service      IN SCOPE     -> rule fires
    SkyEagle (post-rename)  OpenEMR\Modules\SkyEagleBranding\Service   OUT OF SCOPE -> RULE INERT
ForbiddenBrandingSiteConfigRule         ... identical result
ForbiddenBrandingTwigPathRule           ... identical result
ForbiddenBrandingPlaceholderDomainRule  ... identical result
```

Script preserved at `<scratchpad>/inert.php`. Combined with the already-verified registration at
`.phpstan/extension.neon:81-92`, all four rules would load, run, and **match nothing** after a namespace
rename — reporting `0 errors`, indistinguishable from compliance. **S1-P1-04 / claims C1 and C2: PROVEN.**

*Residual gap, stated honestly:* this proves the comparison semantics using the real constants, not a
full end-to-end PHPStan run. The only unexercised link is that PHPStan's `Scope::getNamespace()` returns the
namespace string these methods compare — mechanical, and consistent with Agent 1C's reading of the source.
A future session may close it with a real PHPStan run, but the finding no longer depends on it.

Optional remaining polish (the full end-to-end version, if ever wanted):
1. Positive control: run each `ForbiddenBranding*RuleTest` and record exact `Tests: N, Assertions: M` and the
   expected identifiers (`thiqaBranding.noRuntimeHttpClient`, `.noSiteConfigSeam`,
   `.twigNamespaceDiscipline`, `.noPlaceholderEndpoint`).
2. Copy a violating fixture to the **scratchpad**, change ONLY its `namespace` to a SkyEagle-shaped one,
   run the rule against it. **Expected: zero errors** — proving the rule goes silently inert.
3. Confirm nothing anywhere would alert an operator.

Practical notes for whoever runs it: PHPUnit on this Drive mount routinely exceeds 120–180 s. Redirect output
to a **file** (not a pipe — `$?` after `| tail` reports tail's status). Use
`--configuration=C:\openemr-stack\phpstan-localtmp.neon` for any PHPStan run and grep for `Internal error` /
`Result is incomplete` independently of the exit code (RB-24).

**PRE-11 / Scan-2B — Test-harness truthfulness — NO RESULT RECEIVED.**
Still needed, especially the deliberate **zero-match-filter experiment**: run
`--filter 'SkyEagleBranding'` (a namespace that does not exist) and record exactly what PHPUnit prints and
what exit code it returns. If a zero-match filter exits 0, the documented local runbook command would
silently pass after a rename — a critical finding.

**PRE-16 / Scan-2G runtime proof — DESIGNED, NOT EXECUTED, Owner-authorised to proceed.**
Pre-flight: capture `SELECT gl_name, gl_value FROM globals WHERE gl_name LIKE 'saas_branding_%'`, sha256 +
timestamps of both files in `.../public/branding/default/`, and `verify --site=default` output.
Payload (`tmp/tier2-test.json`, tenant-overridable keys only):
`{"light":{"interactive.primary.default":"#00FF00","interactive.primary.hover":"#00CC00","link.default":"#0000FF"},"dark":{...same...}}`
Apply: `php bin/console thiqa-branding:materialise --site=default --revision=1 --payload=tmp/tier2-test.json`
**Predicted:** revision→1, tokens stored, CSS rewritten, `<link href=".../branding-tokens.php?rev=1">` appears,
endpoint serves `--interactive-primary-default:#00FF00` — **but `.btn-primary` still renders `#c43f2e` and
links still `#2c5f94`**, because no rule reads those properties. That non-change is the decisive observation.
**Revert:** restore the globals rows to their captured pre-state (`saas_branding_revision` back to empty —
there is no console command to decrement; revision is forward-only, so raw SQL is the only path back),
restore both CSS files from the pre-test backup, re-run `verify` and confirm it again reports
`inconsistent / Revision 0 / never`, and diff the globals snapshot against pre-flight.

**Also outstanding:** Scan-3 (PRE-18…24) entirely; PRE-25 final reconciliation.

---

## 17. THE THREE P0 OWNER RULINGS — ALREADY DECIDED, DO NOT ASK AGAIN

**P0-09 (Tier-2 inert):** Runtime-prove during Scan 2, then **FIX NOW** if confirmed. Do not defer into
SkyEagle. The fix must be **brand-neutral** so SkyEagle needs no second architectural rewrite.

**P0-01 (patch records):** **FIX NOW.** Category A documentation/governance remediation of existing core
edits. Write `PR-23…PR-30` and reconcile until
`actual core modifications == patch-record inventory == V-09 scope`.

**P0-13 (migration/rollback tooling):** **YES — build generic safe tooling now.** Must cover
`modules.mod_directory`, the translation-catalogue migration, and backup prefix/retention migration.
Idempotent, with rollback, and with a fresh-install vs upgrade path.

**Additional Owner directives on record:** wire deterministic required CI checks (S1-P1-03) before
certification; build brand-neutral migration-safe backup retention now (S1-P1-15); make the
`interactive.primary.disabled` contract deliberate (S1-P1-17).

---

## 18. HOST / ENVIRONMENT NOTES THAT COST TIME IF FORGOTTEN

- **No Docker.** `CLAUDE.local.md` governs; `openemr-cmd` and the whole worktree workflow do not work here.
- Stack: `C:\openemr-stack\start-openemr.ps1` / `stop-openemr.ps1`. App `http://localhost:8300/`.
  DB `C:\openemr-stack\mariadb\bin\mariadb.exe -u root --host=127.0.0.1 --port=3306 openemr`.
- PHP: `C:\openemr-stack\php\php.exe`.
- **PHPStan:** always `--configuration=C:\openemr-stack\phpstan-localtmp.neon`; grep output for
  `Internal error` / `Result is incomplete` independently of exit code (RB-24).
- **Twig render tests hang** — exclude the whole `…\ThiqaBranding\Twig\` group. A live Twig render can wedge
  the PHP session lock for the whole Apache process until restart (this is what happened at checkpoint time).
- **`npm ci` cannot run on `G:`** — build in `C:\openemr-stack\build`, robocopy back, and **purge
  `public/themes/` first** (RB-08 / Q77).
- Repo-wide `git grep` times out at 2 min; repo-wide ripgrep silently under-reports. **Scope every search
  with a pathspec.**
- Exclusions for any scan: `vendor/`, `node_modules/`, `.claude/worktrees/`, **`G:/My Drive/OpenEMR.worktrees/`**
  (sibling — see S1-P2-14), `oe-module-claimrev-connect` (third-party).
- `git reset --hard` / `git revert` are blocked by this session's safety classifier. Rollback strategy is
  **branch-and-cherry-pick**, never destructive reset.

---

## 19. NEW SESSION — DO THIS FIRST

1. Read this checkpoint **in full**.
2. Read `CLAUDE.md`.
3. Read `CLAUDE.local.md`.
4. Run only read-only baseline commands: current branch · current HEAD · `git status --short` ·
   `git worktree list` · stack state (restart Apache if not responding).
5. Compare current state to §1 of this checkpoint.
6. Do **NOT** re-run completed Scan-1 workstreams merely to "get context."
7. Do **NOT** reopen KG-01 … KG-09.
8. Do **NOT** restart SkyEagle discovery from zero.
9. Identify which Scan-2 workstreams were unfinished (§16).
10. Resume only those missing workstreams.
11. Independently reproduce any pending P0/P1 result before acting on it.
12. Perform the approved FIX-NOW remediation (§17).
13. Re-run affected Scan-1 + Scan-2 gates.
14. Run full Scan 3 with **fresh** agents.
15. Reconcile.
16. Issue PRE-SKYEAGLE PASS **only** if all requirements are genuinely met.
17. **STOP before the actual SkyEagle migration.**

---

## 20. CONTINUATION INSTRUCTION FOR A NEW CLAUDE CODE SESSION

```text
CONTINUATION INSTRUCTION FOR A NEW CLAUDE CODE SESSION

We are resuming the PRE-SKYEAGLE three-scan programme.

Do NOT restart from Stage 0.

The authoritative continuation checkpoint is:

docs/PRE-SKYEAGLE-CONTINUATION-CHECKPOINT.md

Read it completely before doing anything.

Re-verify only current repository/runtime identity first.

Then resume from the first incomplete PRE-* task recorded in the checkpoint.

Do not repeat tasks marked DONE unless a later remediation specifically requires
their verification to be rerun.

Do not reopen recorded Owner decisions.

Do not begin SkyEagle rebranding.

Continue until:

SCAN 1 PASS
+
SCAN 2 PASS
+
SCAN 3 PASS
+
PRE-SKYEAGLE CERTIFICATION PASS

Then STOP and wait for:

START SKYEAGLE REBRANDING
```

---

*Checkpoint written during the PRE-SKYEAGLE audit. No SkyEagle branding change has been made. The only
repository write in this session is this file.*
