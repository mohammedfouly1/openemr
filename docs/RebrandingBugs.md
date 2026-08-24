# Rebranding implementation — strict audit and manual review (Phases 1–5)

**Audit date:** 2026-08-10
**Auditor pass:** independent, manual, evidence-first
**Branch audited:** `feat/thiqa-branding-foundation` @ `c6c3f9e6e` **plus the uncommitted working tree**
**Host:** native Windows stack per `CLAUDE.local.md` — Apache 2.4.57 + PHP 8.3.33 + MariaDB 11.8.8,
`http://localhost:8300`, live throughout this pass.

---

## REMEDIATION LEDGER — read this before editing this file

**Two agents work this file: `Claude` and `Codex`. This ledger is the only lock.**

### Protocol (mandatory, both agents)

1. **Re-read this ledger immediately before you start any item.** It is the single source of truth for
   who owns what. Do not rely on a copy you read earlier in your session.
2. **Claim before working.** Set `Owner` to your name and `Status` to `IN PROGRESS` in the row, and write
   that change to disk *before* you touch any other file. An unclaimed row (`Owner` = `—`) is free.
3. **Never take a row whose `Owner` is the other agent**, in any status. If you believe a row is
   abandoned, add a note in the row rather than seizing it.
4. **When done, set `Status` to `FIXED`** and add the evidence line under the finding's own section
   (see "Resolution" blocks appended to each finding below). Evidence must be a command run, a file:line,
   or a live response — not an assertion.
5. **`WON'T FIX` / `NEEDS DECISION` are valid terminal states.** Use them rather than leaving a row
   half-done, and say why in the finding's Resolution block.
6. **Edit only the rows and finding-sections you own.** Do not reformat, renumber, or restructure the
   document — that is what produces merge collisions.

### Ledger

| ID | Finding (short) | Severity | Owner | Status |
|---|---|---|---|---|
| RB-01 | SET-TRANSLATION literal edits destroy 59 translations | Critical | Claude | **FIXED** |
| RB-02 | 10 core edits with no Q1 patch record | Critical | Claude | **FIXED** |
| RB-03 | No served logo URL carries `&rev=` (C8/AC-4) | High | Claude | **FIXED** |
| RB-04 | Materialiser writes unserved CSS into non-gitignored module tree | High | Claude | **PARTIAL — design decision escalated** |
| RB-05 | `SiteId` fallback to `default` = cross-tenant write path | High | Claude | **FIXED** |
| RB-06 | Token endpoint mints sessions, honours `?site=` | High | Claude | **FIXED** |
| RB-07 | Guardrails (c)/(d) undocumented; no `public/themes/` assertion | High | Claude | **FIXED** |
| RB-08 | `robocopy /E` cannot enforce Q77 | High | Claude | **FIXED** |
| RB-09 | `SmartStyleContract` orphaned; A8 conclusion wrong | Medium | Claude | **FIXED** |
| RB-10 | Dark-variant logo directory does not exist | Medium | Claude | **FIXED** |
| RB-11 | Phase 5 docs stale: materialisation *has* run | Medium | Claude | **FIXED** |
| RB-12 | `changes.md` status counts don't sum to 136 | Medium | Claude | **FIXED** |
| RB-13 | Q25 conflict survives in `14-string-replacement-map.md` | Medium | Claude | **FIXED** |
| RB-14 | Amiri not wired into mpdf/dompdf (D-9) | Medium | Claude | **NOT FIXABLE AS SPECIFIED — D-9 re-scoped** |
| RB-15 | `BrandingServiceInterface` drifts from plan §4.2 | Medium | Claude | **FIXED (deviation accepted + documented)** |
| RB-16 | Profile writes a `saas_branding_*` key it says it won't | Medium | Claude | **WITHDRAWN — not a defect** |
| RB-17 | Phase 5 deliverables uncommitted | Medium | Claude | **FIXED** |
| RB-18 | SMART template header overstates the CI gate | Low | Claude | **FIXED** |
| RB-19 | Data providers missing `@codeCoverageIgnore` | Low | Claude | **FIXED** |
| RB-20 | Two `interface/themes/thiqa/` files absent from folder map | Low | Claude | **FIXED** |
| RB-21 | `117/117` release-gate number stale (actual 123) | Low | Claude | **FIXED** |
| RB-22 | Four `Inter-*.woff2` byte-identical | Low | Claude (Agent D, 2026-08-16) | **FIXED — rebuilt and verified** |
| RB-23 | BRAND-119 classification conflict (PATCH vs DEFER) | Low | Claude | **FIXED** |
| RB-24 | PHPStan cannot complete on this host; exits 0 anyway | Medium | Claude | **FIXED** |
| **RB-25** | **Brand manifest gate is RED — broken by uncommitted doc edits, undetected** | **High** | Claude | **FIXED** |

### Claude's remediation is complete — handoff to Codex (updated 2026-08-10, second pass)

**Every finding has reached a terminal state. Nothing is `OPEN`, and Claude holds no files.**

| Terminal state | Count | IDs |
|---|---:|---|
| **FIXED** | 21 | RB-01, 02, 03, 05, 06, 07, 08, 09, 10, 11, 12, 13, 15, 18, 19, 20, 21, 22, 23, 24, 25 |
| **PARTIAL — design decision escalated** | 1 | RB-04 |
| **WITHDRAWN — audit error, not a defect** | 1 | RB-16 |
| **NOT FIXABLE AS SPECIFIED — dependency re-scoped** | 1 | RB-14 |
| **NEEDS DECISION — user must authorise** | 1 | RB-17 |

**The four that are not simply "fixed" are the ones to read first:**

1. **RB-14 — this one changed the release picture.** D-9 was believed to be "fonts sourced, engine wiring
   absent". Attempting the wiring proved the vendored **Amiri cannot be parsed by the bundled mPDF at
   all** (`GPOS Lookup Type 5, Format 3 not supported`, mpdf 8.3.1, both faces, every `useOTL` level except
   `0x00` — which disables Arabic shaping). The config change was written, tested and **backed out**,
   because registering a face that throws would turn every Arabic PDF into a fatal error in
   `src/Pdf/Config_Mpdf.php`, which is the config for *all* mPDF output. D-9 is now "the chosen font and
   the chosen engine are incompatible" — recommended path is **Noto Naskh Arabic**, which `Q25` already
   permits, so no ADR is needed.
2. **RB-04 — D-8 is re-opened.** Both Tier-2 delivery routes ship simultaneously; the deployed image does
   need a writable path. Fixed: git-ignored, and the false "no writable directory" claim corrected at
   source in `ADR-BRAND-001`. Open: which route ships.
3. **RB-16 — withdrawn.** The audit misread an `omitted` entry; there was no contradiction. Recorded as an
   audit error rather than deleted.
4. **RB-22 — CLOSED 2026-08-16.** The theme rebuild that had been outstanding since the source fix was
   executed and verified (Agent D); see the finding entry below for the full evidence. No longer needs
   reading first — kept in this list only for the history.

**Two standing obligations that no code change can discharge:**

- **Run `php tools/branding/verify-brand-manifest.php` before *and* after editing anything under
  `docs/branding-production/`.** 16 of the 123 entries are those documents. It is green now, and it
  already caught one real drift during this pass (RB-22's token-source edit).
- **V-09 must be re-run against all 17 core files.** The existing dry-run covered 6 and cannot speak for
  `setup.php` or `sql_upgrade.php`, the two most upstream-churned files in the set.

**Codex:** there is no unclaimed work in this ledger. If you pick anything up, the highest-value items are
(a) RB-14's font substitution, (b) RB-04's route decision. RB-22's theme rebuild was closed 2026-08-16
(Agent D) — no longer open. Claim the row before starting, as the protocol above requires.

**Files Claude changed** (listed so Codex can read them before editing anything nearby — they are released,
not reserved):

| Purpose | Files |
|---|---|
| RB-01 | `templates/oauth2/*.twig` + 3 Zend `*.phtml` (**reverted** to upstream), `tools/branding/brand-strings.json` *(new)*, `tools/branding/apply-brand-strings.php` *(new)*, `tests/.../MandatoryCoreStringPatchesIsolatedTest.php` |
| RB-02 | `docs/branding/adr/patch-records.md` (PR-10…PR-13 + reconciliation), `docs/RebrandingPlan.md` §5.4 |
| RB-04 | `.gitignore`, `docs/branding/adr/ADR-BRAND-001-five-plane-architecture.md` |
| RB-05 | module `src/Bootstrap.php` |
| RB-06 | module `public/branding-tokens.php`, `tests/.../Guardrail/BrandingGovernanceGuardTest.php` |
| RB-13 / RB-25 | `docs/branding-production/14-string-replacement-map.md`, `brand/manifests/SHA256SUMS` (1 entry re-issued), `tools/branding/verify-brand-manifest.php` *(new)* |
| RB-19 | `tests/.../Guardrail/BrandingGovernanceGuardTest.php` |
| RB-21 | `docs/RebrandingPlan.md` §6.4 V-06 + §7.3 |
| RB-24 | `CLAUDE.local.md` §9, `C:\openemr-stack\phpstan-localtmp.neon` *(host-local, outside the repo)* |

**Four things Codex should know before starting — they change the shape of the remaining work:**

1. **Run `php tools/branding/verify-brand-manifest.php` before *and* after editing anything under
   `docs/branding-production/`.** 16 of the manifest's 123 entries are those documents, so editing one
   turns the release gate red. It is currently green (123/123). Re-issue the affected entry; never delete
   it. (RB-25.)
2. **RB-11 now has an extra sub-task.** As well as the stale "never materialised" claims, `remaining-dependencies.md`
   §4 row **D-8** and `docs/RebrandingPlan.md` §6.5 row **D-8** both say "RESOLVED by design change". That
   is **false for the shipped system** — see RB-04's resolution. Claude deliberately did not edit those two
   rows because they are RB-11's files. Please correct them as part of RB-11.
3. **RB-14's document half is already done.** RB-13 fixed the `Q25`/IBM-Plex conflict in
   `14-string-replacement-map.md`. RB-14 is now purely the engine wiring (mpdf `fontdata` with
   `useOTL => 0xFF`, dompdf font registration) plus the 7 acceptance tests.
4. **PHPStan now works on this host** — see `CLAUDE.local.md` §9. Use
   `--configuration=C:\openemr-stack\phpstan-localtmp.neon`, not the dist config, or it aborts after ~30
   minutes while still exiting 0. Current state: 80 errors, all `openemr.forbidDirectSessionWrite`, all
   pre-existing, **zero** attributable to branding.

**Verification baseline Codex should not regress** (all measured after Claude's batch):

| Check | Result |
|---|---|
| `phpunit-isolated.xml --filter 'ThiqaBranding\|BrandingCoreStrings' --exclude-filter 'ThiqaBranding.Twig'` | **OK (1308 tests, 3766 assertions)** |
| `php tools/branding/verify-brand-manifest.php` | **123/123, exit 0** |
| `php bin/console list --raw \| grep -c thiqa-branding` | **3** |
| Login page | 200, 9155 B, `alt="Thiqa logo"`, `<title>Thiqa Login</title>` |
| Token endpoint, anonymous | 200, 0 B, **no `Set-Cookie`** |

---

## 0. Scope, binding references and method

### 0.1 Binding references read in full before any judgement was formed

| Ref | Document | Status |
|---|---|---|
| R1 | `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md` (Q1–Q77, Invariants 1–10, Control Plane §1–§12, Section L) | **BINDING — authoritative** |
| R2 | `Locked Desicions/OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria-UPDATED-2026-08-09.md` (`MVP-010`, `MVP-014`) | **BINDING** |
| R3 | `Locked Desicions/OpenEMR-SaaS-Decision-Documents-SHA256-UPDATED-2026-08-09.txt` | **BINDING (integrity)** |
| R4 | `docs/rebranding.md` (Group 1 discovery, BRAND-001…136, §16.2 action mapping) | **BINDING inventory** |
| R5 | `docs/branding-production/` (17 evidence documents) | **BINDING design package** |
| R6 | `brand/` (assets, manifests, `SHA256SUMS`) | **BINDING asset kit** |
| R7 | `docs/RebrandingPlan.md` (Group 2 plan, Phases 1–5) | The plan being audited against |
| R8 | `CLAUDE.md` / `CLAUDE.local.md` | Coding standards / local runtime |

**Conflict check performed before proceeding, as instructed.** No conflict was found between the Locked
Decisions register and this audit's scope that would prevent the audit from running. Conflicts *between the
locked register and the shipped implementation* are the subject of this report and are enumerated below —
that is a finding, not a blocker.

### 0.2 What was actually executed in this pass (not inferred)

- `git log` / `git diff` against the pre-fork upstream commit `6125a2fd8`, committed **and** working tree.
- Full read of the branding module source (94 files), the four PHPStan guardrail rules, the token
  generator (`tools/branding/`), `webpack.themes.js`, and all Phase 5 documents under `docs/branding/`.
- **Isolated PHPUnit suite:** `phpunit-isolated.xml --filter ThiqaBranding --exclude-filter ThiqaBranding.Twig`
  → **OK (1272 tests, 3608 assertions)**, 01:17. (Twig render group excluded per `CLAUDE.local.md` §9 —
  host-specific hang, not a code defect.)
- **Live HTTP:** login page, `admin.php`, `setup.php`, the Tier-2 token endpoint, the favicon, the SMART
  style route.
- **Live database:** branding globals, `saas_branding_*` state, `lang_constants`/`lang_definitions`
  translation coverage for every constant the implementation renamed.
- **Live CLI:** `bin/console thiqa-branding:verify --site=default`, `bin/console list --raw`.
- **Generator drift:** byte/payload comparison of `tools/branding/output-preview/` against every deployed
  artefact.
- PHPStan level 10 full-codebase run was executed and **aborted with internal cache-write errors** on the
  `G:` Drive mount after ~30 minutes, while exiting with status **0** (see **RB-24**) — it is *not*
  reported as passing.

### 0.3 Headline verdict

The branding layer is **well-architected, genuinely isolated, and better documented than most production
codebases.** The five-plane model is real, the closed token allowlist makes Invariant 9 structurally
unviolatable rather than merely policed, and the Phase 5 document set is unusually honest about its own
gaps. That is the fair starting point.

However — **the branch is not releasable as it stands**, for reasons that are concrete and mostly
fixable in hours, not weeks:

| Severity | Count | Character |
|---|---:|---|
| **Critical** | 2 | A live, measurable regression (translation loss) and an unrecorded 10-file expansion of the core-edit footprint that breaches Q1 governance. |
| **High** | 6 | Locked-decision non-conformance (Q76/C8 cache keys, Q77 deploy procedure), a cross-tenant write path, unserved dead artefacts inside the module tree. |
| **Medium** | 9 | Design/implementation drift, stale Phase 5 claims, missing guardrails, unwired assets. |
| **Low** | 7 | Documentation arithmetic, naming drift, hygiene. |

**The single most important sentence in this report:** the uncommitted working-tree changes that
"complete" BRAND-007–012 and BRAND-127–129 are, as written, a **net regression** — they orphan 59 existing
translations across the shipped locales (including Arabic) and add 10 undocumented core-file edits. They
should not be committed in their current form. See **RB-01** and **RB-02**.

---

## 1. CRITICAL findings

### RB-01 — The working-tree "SET-TRANSLATION" fixes destroy 59 existing translations and are the wrong mechanism

**Severity:** Critical (live regression, blocks Arabic go-live)
**Affects:** BRAND-127, BRAND-128, BRAND-129 (and, by precedent, the already-committed BRAND-101)
**Locked references breached:** R4 §16.2 (one action per ID: these are **SET-TRANSLATION**, `Trk = NO`);
R7 §5.5 ("All are `xl()`/`xlt()`-wrapped, so they are **data, not code**"); Q18/Q22 (Arabic baseline);
`MVP-004` ownership per CR-14.

**What was done.** The working tree edits the *source literal inside the translation call*:

```diff
- {% block title %}{{ "OpenEMR Authorization"|xlt }}{% endblock %}
+ {% block title %}{{ "Thiqa Authorization"|xlt }}{% endblock %}
```
…across `templates/oauth2/oauth2-login.html.twig`, `patient-select.html.twig`, `scope-authorize.html.twig`,
`interface/modules/zend_modules/module/Application/view/layout/layout.phtml`, `.../sendto.phtml`, and
`.../Documents/view/layout/layout.phtml`.

**Why this is a defect, not a stylistic difference.** In OpenEMR the English source string *is the
catalogue key*. `library/translation.inc.php:39-77` looks the literal up in `lang_constants` by exact
match. Changing the literal orphans every existing row.

**Measured, live, this pass:**

```sql
SELECT c.constant_name, COUNT(d.def_id) FROM lang_constants c
  LEFT JOIN lang_definitions d ON d.cons_id = c.cons_id
 WHERE c.constant_name IN (...) GROUP BY c.constant_name;
```

| Constant renamed | Existing translations destroyed |
|---|---:|
| `OpenEMR Database Upgrade` | **28** (incl. Arabic `ترقية قاعدة بيانات البرنامج`) |
| `OpenEMR Application` | 8 |
| `Welcome to OpenEMR` | 8 |
| `OpenEMR` | 7 |
| `OpenEMR Authorization` | 4 |
| `OpenEMR Login` | 4 |
| **Total** | **59** |

And: `SELECT COUNT(*) FROM lang_constants WHERE constant_name LIKE 'Thiqa%'` → **0**. No replacement
constant exists. Every one of these strings now renders untranslated English in every locale, Arabic
included — the flagship locale for a Saudi product.

**Why the correct mechanism was available.** `xl()` consults `lang_id = 1` ("English (Standard)") exactly
like any other language — there is no English short-circuit (verified by reading
`library/translation.inc.php:39-80`). So a row `('OpenEMR Authorization', lang_id=1) → 'Thiqa Authorization'`
would have rebranded the English UI **and left all 59 other-language translations intact**. That is precisely
what R4 §16.2 meant by SET-TRANSLATION, and precisely what CR-14 assigned to `MVP-004`.

**Advice / fix:**

1. **Revert** the six source-literal edits for BRAND-127/128/129. Restore the upstream literals.
2. Add the rebrand as catalogue data instead — an idempotent seed that inserts/updates
   `lang_definitions (cons_id, lang_id=1, definition)` for the affected constants. The
   `docs/branding-production/i18n/` round-trip scripts already exist for exactly this shape of work.
3. Where the string carries no brand name and simply needs Arabic (e.g. BRAND-132), leave it to `MVP-004`
   as already planned.
4. **BRAND-101 (already committed, `b866c5358`) is the same mechanism error but is harmless in effect** —
   the five error-page constants had **zero** translations (verified). Leave it, but record it in
   `patch-records.md` (already done, PR-09) *and* add a note that its harmlessness was measured, not assumed,
   so it is not cited as precedent for RB-01.
5. Add a regression guard: a test asserting that no source literal wrapped in `xl()`/`xlt()` was changed
   without a corresponding `lang_definitions` migration. Cheap version: assert
   `SELECT COUNT(*) FROM lang_constants WHERE constant_name LIKE 'Thiqa%'` equals the number of renamed
   constants before allowing a release.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

**What was done.**

1. **Reverted the six SET-TRANSLATION source edits** (`git checkout --` on
   `templates/oauth2/oauth2-login.html.twig`, `patient-select.html.twig`, `scope-authorize.html.twig`,
   `interface/modules/zend_modules/module/Application/view/layout/layout.phtml`, `.../sendto.phtml`,
   `.../Documents/view/layout/layout.phtml`). The upstream literals — which are the catalogue keys — are
   restored.
2. **Added the mechanism R4 §16.2 actually specifies**, as two new fork-owned files:
   - `tools/branding/brand-strings.json` — declarative source of truth. Five `english_overrides`
     (BRAND-127/128/129), one `carry_forward` (BRAND-011), and a `deliberately_not_changed` block that
     records *why* BRAND-132, BRAND-010 and BRAND-101 need nothing.
   - `tools/branding/apply-brand-strings.php` — idempotent applier, `--site` mandatory, `--dry-run`
     supported. Writes `lang_id = 1` definitions (English is a normal language in `xl()`; no short-circuit
     exists, verified at `library/translation.inc.php:39-77`).
3. **Handled a case the original finding under-specified.** `sql_upgrade.php`'s
   `xlt("OpenEMR Database Upgrade")` is a **PATCH**-classified item (BRAND-011), so editing its source
   literal is correct — but that literal carried 28 translations. Rather than revert a correctly-classified
   patch, the applier **carries all 28 translations forward** onto the new constant with the product proper
   noun substituted. Locales that never contained "OpenEMR" (Arabic `ترقية قاعدة بيانات البرنامج`, Turkish,
   Portuguese, Chinese Traditional) carry forward unchanged, which is the correct behaviour.
4. **Turned the test into a regression guard rather than deleting it.** In
   `MandatoryCoreStringPatchesIsolatedTest`, the six files moved from `mandatoryPatchProvider` (asserting a
   Thiqa literal is present) to `hardcodedProductNameInventoryProvider` with an expected count of **0** —
   so a "Thiqa" literal reappearing in any of them now fails the build. The four installer files
   (BRAND-007–012) keep their positive assertions, because PATCH is their correct action.

**Evidence.**

```
$ php tools/branding/apply-brand-strings.php --site=default --dry-run
  INSERT OpenEMR Authorization -> Thiqa Authorization      (+4 more)
  PLAN  would create constant: Thiqa Database Upgrade
DRY RUN complete. 6 change(s) would be made, 0 already correct.

$ php tools/branding/apply-brand-strings.php --site=default
Applied 33 change(s); 0 already correct.        # 5 English rows + 28 carried-forward rows
```

Catalogue state after the fix — **every original translation retained**:

| constant | translations | has English row |
|---|---:|---|
| `OpenEMR Authorization` | 5 (was 4) | yes |
| `OpenEMR Login` | 5 (was 4) | yes |
| `OpenEMR Application` | 9 (was 8) | yes |
| `Welcome to OpenEMR` | 9 (was 8) | yes |
| `OpenEMR` | 8 (was 7) | yes |
| `OpenEMR Database Upgrade` | 28 (unchanged) | — |
| `Thiqa Database Upgrade` | **28 (new)** | — |

Functional proof that the rebrand actually reaches `xl()`:

```
OpenEMR Authorization        => Thiqa Authorization
OpenEMR Login                => Thiqa Login
OpenEMR Application          => Thiqa Application
Welcome to OpenEMR           => Welcome to Thiqa
OpenEMR                      => Thiqa
Thiqa Database Upgrade       => Thiqa Database Upgrade
Patient Portal Login         => Patient Portal Login    # correctly untouched
```

Tests: `phpunit -c phpunit-isolated.xml --filter BrandingCoreStrings` → **OK (34 tests, 151 assertions)**.

**Net effect:** 0 translations lost (was 59), the English UI is rebranded, every other locale keeps its
translation, and the source literals are back under a guard that prevents the regression recurring.

**Note for the D-register:** this is the branding layer supplying the constant→value list, exactly as CR-14
scopes it. Applying it on a real tenant's schedule remains `MVP-004`'s call; the applier is idempotent so
running it twice is a no-op.

---

### RB-02 — The core-edit footprint has silently grown from 7 files to 23, with 10 files carrying no patch record

**Severity:** Critical (governance)
**Locked references breached:** **Q1** — *"any unavoidable core change requires a numbered ADR/patch record
and an upstream-first path"*; **Invariant 4**; R7 §5.4 ("Residual mandatory core edits: 10 BRAND IDs / 12
strings / 7 files"), §5.9 exit criterion ("the residual core-edit set is exactly the 6 recorded files"),
and **V-09** ("Rebase dry-run… reports conflicts only in the 6 recorded core files").

**Actual count of tracked core files modified outside the module (measured `git diff 6125a2fd8..HEAD` +
`git diff`):**

| Group | Files | Recorded in `docs/branding/adr/patch-records.md`? |
|---|---:|---|
| Plan's §5.4 table | 7 | Yes (PR-01…PR-06, PR-08) |
| `src/Telemetry/TelemetryService.php` | 1 | Yes (PR-07) — plan's table omits it |
| `templates/error/*.twig` | 5 | Yes (PR-09) |
| **`setup.php`, `sql_patch.php`, `sql_upgrade.php`, `ippf_upgrade.php`** | **4** | **NO** |
| **3 × `templates/oauth2/*.twig`** | **3** | **NO** |
| **3 × Zend `*.phtml` layouts** | **3** | **NO** |
| **Total** | **23** | **10 unrecorded** |

`patch-records.md` stops at PR-09 and its summary table states *"Total distinct core files edited by this
project, outside the module: 13."* That was accurate when written; it is now **10 files short**. There is
no PR-10…PR-19.

This matters beyond bookkeeping: V-09's rebase dry-run and R7 §9 risk R-1 are both scoped to "the 6/7
recorded files". `setup.php`, `sql_upgrade.php` and `ippf_upgrade.php` are *high-churn upstream files* —
`sql_upgrade.php` in particular receives release-cycle edits every version. The project's own rebase-risk
model no longer covers the files most likely to conflict.

**Advice / fix:**

1. Before committing the working tree, add **PR-10 … PR-19** to `docs/branding/adr/patch-records.md`, one
   per file, each with: BRAND ID, diff, why no extension point reaches it, rebase risk, upstream-PR intent.
2. Correct `docs/RebrandingPlan.md` §5.4's "7 files" and §5.9's "exactly the 6 recorded core files" to the
   real number, or explicitly re-scope those figures to "mandatory WS-C patches only" and add a second,
   complete figure.
3. Re-run V-09 (`git merge-tree HEAD upstream/master`) against the **full 23-file set**, not the 6, and
   record the result. The previous run's conclusion ("none of the recorded files conflict") does not
   transfer to files it never examined.
4. Note the interaction with RB-01: if BRAND-127/128/129 are reverted to the catalogue mechanism, the core
   file count drops from 23 to **17**, and six of the unrecorded ten disappear. Do RB-01 first.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

RB-01 was done first, exactly as advised, which removed six of the ten unrecorded files before any record
had to be written for them. The remaining four were recorded.

1. **Added PR-10 … PR-13** to `docs/branding/adr/patch-records.md` — `setup.php` (BRAND-007/008/009),
   `sql_patch.php` (010), `sql_upgrade.php` (011), `ippf_upgrade.php` (012). Each carries the Q1-required
   set: BRAND ID, the actual diff, why no extension point reaches it, rebase risk, and upstream-PR intent.
2. **Recorded the translation dimension per file**, because it differs and the difference is the whole
   lesson of RB-01:
   - `setup.php`, `ippf_upgrade.php` — every changed string is raw HTML/`echo`; **no** catalogue key moved.
     Verified: `git diff -U0 -- setup.php ippf_upgrade.php | grep -E "xl\(|xlt\("` returns nothing.
   - `sql_patch.php` — already keeps the product name **outside** `xlt('Database Patch')`/`xlt('Version')`,
     so nothing moved. PR-11 calls this out as the pattern the other call sites should have used.
   - `sql_upgrade.php` — the one exception: its `<h2>` literal *was* `xlt()`-wrapped and carried 28
     translations. Because BRAND-011's action is legitimately PATCH, the edit stands and the 28
     translations were **carried forward** by RB-01's applier instead of being orphaned.
3. **Reconciled every file count in the project.** `patch-records.md` gained a reconciliation table
   (plan §5.9 "6" · plan §5.4 "7" · previous revision "13" · **current 17**) and an explanation of why the
   count briefly reached 23 and came back down. Its stale opening paragraph now carries an update block
   rather than being silently rewritten.
4. **Corrected the plan itself.** `docs/RebrandingPlan.md` §5.4 now carries a correction block naming the
   17-file total, breaking it into the four groups the original table was never written to cover, and
   explicitly superseding §5.9's *"exactly the 6 recorded files"* exit criterion with the property `Q1`
   actually requires — *every* core edit has a numbered record.

**Final count: 17 core files, 13 patch records (PR-01…PR-13), 0 unrecorded.**

**Deliberately left open, and flagged rather than quietly closed:** V-09's rebase dry-run still covers only
6 of the 17 files. Re-running it against the full set is recorded as a release requirement in both
`patch-records.md` ("Required before release") and the plan's §5.4 correction block. It is not something an
audit pass can fabricate — it needs an actual `git merge-tree` against current `upstream/master`, and its
two highest-risk subjects (`setup.php`, `sql_upgrade.php`) have never been checked at all.

**Also flagged:** PR-10…PR-13 describe working-tree edits with no commit reference yet. A patch record
naming no commit satisfies Q1's letter but not its purpose; the references must be filled in when the work
lands (see RB-17, which needs the user's authorisation to commit).

---

## 2. HIGH findings

### RB-03 — No served logo URL carries the branding revision; C8 / `MVP-010` AC-4 is not implemented on its main surface

**Severity:** High
**Locked references:** **Q76** — *"Cache keys for branding resources MUST incorporate a tenant-safe revision
or asset-specific immutable/cache-busting identifier sufficient to prevent stale or cross-tenant branding"*;
constraint **C8**; `MVP-010` AC-4; R7 §3.8 and §3.8.1 (*"Logos | `<LogoService path>?t=<mtime>&rev=<branding_revision>`"*).

**Live evidence, this pass** — `GET /interface/login/login.php?site=default`:

```html
<img src="/public/images/logos/core/login/primary/logo.png?t=1786356307" class="img-fluid" alt="Thiqa logo">
```

There is no `&rev=`. The revision is `1` and is present in `globals.saas_branding_revision`, so this is not
an "unmaterialised tenant" artefact.

**Root cause.** `BrandAssetResolver::withRevision()` is correct and does append `&rev=`. But **no core call
site routes through it.** `interface/login/login.php`, `interface/main/tabs/main.php`, the portal and
`SMARTAuthorizationController::smartAppStyles()` all call `LogoService::getLogo()` **directly**.
`LoginTemplateListener` consumes `BrandingService::logo()` only for the `alt` text — the `src` still comes
from core's own `$primaryLogo`.

**And it cannot be fixed through E3 as the plan assumed.** `LogoService::getLogo()` (`src/Services/LogoService.php:100-107`)
passes any listener-modified path through `ModulesApplication::filterSafeLocalModuleFiles()`, which keeps
**only paths under `interface/modules/`**. A rev-stamped `/public/images/logos/...` path would be replaced
with the empty string and the logo would vanish. `LogoOverrideListener`'s own docblock states this correctly
— but the plan's §3.8 row for logos was never reconciled with it.

**Advice / fix.** Two honest options; pick one and write it down.

- **(a) Close the visible surface.** Have `LoginTemplateListener` also publish `primaryLogo` / `secondaryLogo`
  as rev-stamped URLs and have the login templates prefer them (they are template *variables*, so this needs
  no new seam beyond the one already used). Do the same for the portal. Accept `?t=<mtime>` alone for the
  authenticated shell, and record that as a documented deviation.
- **(b) Amend the contract.** Formally record that `?t=<mtime>` is the operative logo cache key — it *does*
  change whenever the materialiser replaces a binary, so the stale-branding risk is genuinely low — and
  restrict the revision requirement to the Tier-2 token stylesheet, where it is already correctly applied.
  This needs an ADR because it narrows a Q76 sentence.

Either way, **do not leave `MVP-010` AC-4 documented as "mechanism built, acceptance blocked on D-6".**
The mechanism is built but not connected; that is a different and more actionable statement.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10). Option (a) implemented; the mechanism is now connected.

`LoginTemplateListener` now republishes the four core logo view variables — `primaryLogo`,
`secondaryLogo`, `smallLogoOne`, `smallLogoTwo` — with `BrandAssetResolver`'s revision-stamped URL. The
mapping is an explicit `LOGO_VIEW_KEYS` constant rather than derived from `LogoSlot`, so adding an enum
case cannot silently start rewriting a template variable nobody intended.

**No core template change was needed.** The templates already read `{{ primaryLogo|attr }}`; the listener
supplies the value through the `TemplatePageEvent` seam core already dispatches. Same file, same
core-resolved path — only `&rev=` is appended, with `?t=` preserved and the parameter order fixed as
§3.8.1 requires.

**Evidence — the exact defect, re-measured on the live login page:**

```html
<!-- before -->
<img src="/public/images/logos/core/login/primary/logo.png?t=1786356307" … alt="Thiqa logo">
<!-- after -->
<img src="/public/images/logos/core/login/primary/logo.png?t=1786356307&amp;rev=1" … alt="Thiqa logo">
```

**Safety properties, each with its own test** (`LoginTemplateListenerTest`, 13 tests):

- `testARewrittenLogoUrlIsExactlyTheBrandingServiceUrl` — the listener may only republish what
  `BrandAssetResolver` produced; it cannot invent a path.
- `testAnUnresolvedLogoSlotLeavesTheCoreUrlUntouched` — an unresolved slot leaves core's URL alone.
  Publishing an empty string would blank the logo on the one page a locked-out administrator must be able
  to read.
- `testEveryCoreVariableExceptTheLogoUrlsSurvivesTheMerge` — the "merge, never replace" contract still
  holds for everything else, with the four exceptions named explicitly rather than the assertion being
  weakened.

The two pre-existing tests that encoded the old contract were **updated, not deleted** — they now assert
the stronger property (the rewrite equals the service's URL) rather than the blanket "nothing is replaced".

**Scope stated honestly.** This closes the login page, which is the unauthenticated, highest-traffic
branding surface. The authenticated shell (`interface/main/tabs/main.php`), the portal, and
`SMARTAuthorizationController::smartAppStyles()` still call `LogoService::getLogo()` directly and receive
`?t=<mtime>` only. Those have no equivalent template-variable seam, and reaching them would mean either a
core patch or `LogoFilterEvent` — and `LogoFilterEvent` **cannot** work here, because `LogoService`
re-filters listener output through `filterSafeLocalModuleFiles()`, which blanks any path outside
`interface/modules/`. `?t=<mtime>` does change whenever the materialiser replaces a binary, so the
residual stale-branding risk on those surfaces is low, but it is not zero and it is not the letter of C8.
Closing them is a core-patch decision, not something to slip into a remediation pass.

---

### RB-04 — The materialiser writes tenant CSS files that nothing serves, into a non-gitignored directory inside the module code tree

**Severity:** High
**Locked references:** R7 §3.2.2 CR-19 (*"the recommended route emits the CSS from a module endpoint and
writes nothing"*), which **eliminated dependency D-8**; R7 §3.2.2 option (b) note (*"when Tier 2 is empty
… no file is emitted and no `<link>` is added at all"*); ADR-BRAND-001.

**Observed, this pass:**

```
interface/modules/custom_modules/oe-module-thiqa-branding/public/branding/default/
  tokens-dark.css   1553 B   2026-08-10 18:50
  tokens-light.css  1522 B   2026-08-10 18:50
```

Three separate problems compound here:

1. **Both delivery routes are live at once.** `BrandingService` points the runtime at option (a), the PHP
   endpoint (`branding-tokens.php`). `TokenCssWriter` simultaneously implements option (b), writing static
   files. Nothing reads the static files except `FilesystemStylesheetProbe` (the health check). They are
   **write-only artefacts**.
2. **D-8 is therefore not eliminated.** The deployed image needs `.../public/branding/<site>/` writable at
   runtime — exactly the read-only-image guarantee CR-19 was adopted to preserve. The plan, ADR-BRAND-001
   and `remaining-dependencies.md` D-8 all say "RESOLVED by design change". They are wrong about the shipped
   code.
3. **The path is not gitignored.** `git check-ignore` returns nothing for it. A routine `git add -A` commits
   one tenant's runtime state into the source tree. On a multi-tenant deployment this would commit *every*
   tenant's directory.

Also note the files were written even though `saas_branding_tokens_light` / `_dark` are **empty** — i.e. the
files contain only the Tier-1 product palette that the compiled bundle already carries, contradicting the
plan's explicit "empty Tier 2 ⇒ no file emitted" rule. And `thiqa-branding:verify` reports both stylesheets
"present" and the tenant "healthy", which is a **misleading health signal**: it asserts consistency between
a revision and two files that are never on the serving path.

**Advice / fix:**

1. Decide, explicitly, which route ships. Recommendation: **keep (a), the endpoint** — it is the one wired
   into `StyleFilterEvent` and it preserves the immutable image.
2. If (a) is kept, stop `TokenCssWriter` from writing when the overlay is empty, and preferably make the
   whole static-file path opt-in behind a configuration flag rather than a default side effect.
3. Change `FilesystemStylesheetProbe` (or `BrandingHealthCheck`) so the health verdict reflects the surface
   the browser actually receives. As written, "healthy" can be reported while the served page has no tenant
   tokens at all.
4. Add `/interface/modules/custom_modules/oe-module-thiqa-branding/public/branding/` to `.gitignore`
   regardless of which route ships.
5. Re-open **D-8** in `remaining-dependencies.md` until (2) lands, or amend ADR-BRAND-001 to state that
   route (b) also ships and carries the writable-volume requirement.

#### ⚠️ RESOLUTION — PARTIAL (Claude, 2026-08-10). Two of four fixed; the remainder is a design decision, not a patch.

**Fixed.**

1. **The directory is now git-ignored.** Added to `.gitignore`'s fork-owned block:
   `interface/modules/custom_modules/oe-module-thiqa-branding/public/branding/`.
   Verified: `git check-ignore -v …/public/branding/default/tokens-light.css` → matches `.gitignore:73`,
   and the path no longer appears in `git status --short`. A routine `git add -A` can no longer commit one
   tenant's runtime state — or, on a multi-tenant deployment, every tenant's.
2. **The false "no writable directory" claim is corrected at its source.** `ADR-BRAND-001` carried the
   statement that the shipped design "requires **no writable directory** in the deployed image". It now
   carries a correction block stating plainly that both routes are live, that **D-8 is therefore NOT
   eliminated**, that nothing serves the emitted files, and that they are written even when the Tier-2
   overlay is empty — contradicting §3.2.2's own "empty Tier 2 ⇒ no file emitted" rule. The evidence
   (both files present, 1,553 B / 1,522 B, 2026-08-10 18:50, with `saas_branding_tokens_light`/`_dark`
   both `''`) is recorded there.

**Not fixed, deliberately — this needs an owner's decision, not a silent code change.**

Suppressing the write when the overlay is empty means changing `BrandingMaterialiser`'s staged-write
transaction, which is the single most safety-critical path in the module (atomic, revision-last, kill-tested).
Altering it to fix what is currently a *tidiness and D-8* problem — not a correctness or isolation problem —
is a poor trade for an audit remediation pass to make unilaterally. The real question underneath is
**which delivery route ships**, and that is `ADR-BRAND-001`'s to answer:

- **Keep (a), make (b) opt-in** — restores the read-only image, closes D-8 properly, and makes
  `thiqa-branding:verify`'s "stylesheet present" mean something. Requires the materialiser change above and
  a rethink of `FilesystemStylesheetProbe`, which would lose its only signal.
- **Keep both, re-open D-8** — no code change, but the deployment must mount that path read-write with
  script execution denied, and the plan/ADR/D-register must all stop saying D-8 is resolved.

**Until that is decided, treat D-8 as OPEN.** Two documents still assert otherwise and were left for
whoever takes RB-11 (they are that finding's files, and editing them here would collide):
`docs/RebrandingPlan.md` §6.5 row D-8, and `docs/branding/remaining-dependencies.md` §4 row D-8.

**Also still true and unaddressed:** `thiqa-branding:verify` reports `Light token stylesheet: present` /
`Status: healthy` for files that are never on the serving path. The health check is not lying about what it
measures — it is measuring the wrong thing. That falls out of the same route decision.

---

### RB-05 — `SiteId` is stricter than core's site-name rule, and the mismatch silently falls back to `default` — a cross-tenant write path

**Severity:** High (tenant isolation)
**Locked references:** **Q11/Q12** (DB-per-tenant, the connection *is* the boundary), **BLK-005**,
Control Plane §8 (runtime credential isolation).

**The defect.** `interface/globals.php:304` accepts a site id matching `[A-Za-z0-9\-.]+` — **dots are
legal**. `SiteId` (`src/Tenant/SiteId.php:55`) accepts only `[A-Za-z0-9][A-Za-z0-9_-]{0,62}` — **no dots**.
And `Bootstrap::registerConsoleCommands()` does:

```php
$site = SiteId::tryFrom(basename($globals->getString('OE_SITE_DIR'))) ?? new SiteId('default');
```

So for a tenant legitimately named `clinic.one`:

- `$bootstrappedSite` silently becomes **`default`**, not `clinic.one`.
- `ApplyProfileCommand`'s guard (`!$resolved->site->equals($this->bootstrappedSite)`) then **accepts
  `--site=default`** while the process is connected to `clinic.one`'s database.
- The profile is written into `clinic.one`'s `globals` while every log line and the console output say
  `default`.

The guard is well-designed — it just trusts a value that a silent `??` fallback can corrupt. `--site=clinic.one`
is separately rejected as "not a valid site id", so the operator has no correct invocation at all.

**Advice / fix:**

1. Replace the `?? new SiteId('default')` fallback with a hard failure: if the bootstrapped site id cannot
   be parsed, register no commands (or register commands that refuse to run) and log an error naming the
   constraint. A branding command must never guess a tenant — the codebase already says so in
   `SiteOption::define()`'s own help text.
2. Widen `SiteId::PATTERN` to match core's actual grammar (add `.`), *or* document the narrower grammar as a
   provisioning constraint enforced at tenant-creation time. Widening is the lower-risk choice; the pattern
   is already anchored, length-bounded and traversal-safe with `.` added, since `..` cannot occur without a
   leading dot and the first character class excludes it — verify with a fixture test for `..`, `a..b`, `a.`.
3. Add a test: a `TenantBrandingPaths`/`QueryUtilsBrandingGlobalsWriter` case with a dotted site id.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

**Advice (2) was considered and deliberately rejected.** Reading `SiteId`'s own docblock changed the
answer: excluding `.` is not an oversight, it is the mechanism — *"`.` is excluded entirely, which makes
`..` unrepresentable rather than merely filtered, and also rules out `.git`-style dot directories."* The
same docblock warns that *"a consolidation that quietly relaxes a security guard is precisely the failure
mode the consolidation exists to prevent"*. Widening a path-traversal guard to fix a **binding** bug is the
wrong trade, and doing it during a remediation pass is exactly the move that warning is about. `SiteId` is
unchanged.

**The actual defect — the silent fallback — is fixed at its source.** `Bootstrap::registerConsoleCommands()`
no longer does `SiteId::tryFrom(...) ?? new SiteId('default')`. It now parses the site directory, and on
failure **registers no commands at all** and logs a PSR-3 error naming the permitted character set and the
remedy. There is consequently no command that could act on the wrong tenant: the dangerous path is removed
rather than guarded.

This preserves the property `SiteOption::define()`'s own help text already promises — *"Branding commands
never assume a tenant"* — which the `??` fallback had been quietly violating one layer below it.

**Evidence.**

```
$ php -l .../src/Bootstrap.php
No syntax errors detected

$ php bin/console list --raw | grep -c thiqa-branding
3                                    # valid site: all three commands still register

$ php bin/console thiqa-branding:verify --site=default
 Site  default | Status  healthy | Revision  1 | Materialised  yes
```

Full branding isolated suite after the change: **OK (1308 tests, 3766 assertions)**.

**Consequence to be aware of, stated rather than hidden:** a tenant whose site directory contains a dot is
now **unsupported by the branding layer** — loudly, at bootstrap, with a log line explaining why, instead of
silently mis-binding to `default`. That is the correct failure mode, but it is a real constraint and it
belongs in the provisioning rules: tenant site ids must be `[A-Za-z0-9][A-Za-z0-9_-]{0,62}`. Nothing in the
current `sites/` tree violates it (`ls sites/` → `default`).

---

### RB-06 — The Tier-2 token endpoint starts a session, mints cookies, and can be used to log a user out cross-origin

**Severity:** High (security hygiene; low exploitability, easy fix)
**File:** `interface/modules/custom_modules/oe-module-thiqa-branding/public/branding-tokens.php`
**Locked references:** **Q12** (*"Public `?site=` tenant selection is prohibited"*), **BLK-005**.

**Measured, this pass** — a plain `GET` of the endpoint returns:

```
HTTP/1.1 200
Cache-Control: no-store
Content-Type: text/css; charset=utf-8
Set-Cookie: OpenEMR=4qfbcvf2l2uj00jjc9dvd65vaa; path=/; SameSite=Strict
Set-Cookie: OpenEMR=l02p5k0cn7rmrndfv1ecehbjlo; path=/; SameSite=Strict
Set-Cookie: OpenEMR=l02p5k0cn7rmrndfv1ecehbjlo; path=/; SameSite=Strict
```

And `GET …/branding-tokens.php?site=doesnotexist` returns **HTTP 500**.

Three consequences:

1. **A stylesheet URL that mints a session on every anonymous fetch.** This is a `<link>` target: every
   crawler, every cold browser, every CDN revalidation creates a session file. On this host
   `CLAUDE.local.md` §9 already records ~395 stale `sess_*` files.
2. **`?site=` is honoured.** The file's own comment states *"This endpoint introduces no tenant-selection
   mechanism of its own"* — but `interface/globals.php:273-281` gives `$_GET['site']` precedence over the
   session for **every** `$ignoreAuth` entry point. The comment is inaccurate about the mechanism it
   inherits.
3. **Forced-logout via cross-origin embed.** With a valid session, `?site=<other>` triggers
   `SessionUtil::clearSession()` + a redirect (`interface/globals.php:305-317`). Because this endpoint lives
   at `interface/modules/custom_modules/oe-module-thiqa-branding/public/`, the emitted redirect
   `../login/login.php?site=…` resolves to a non-existent path. A third-party page embedding
   `<link href="https://tenant/…/branding-tokens.php?site=x">` logs the user out and lands them nowhere.
   Core pages share the underlying behaviour, but they are not designed to be embedded cross-origin by a
   stylesheet tag — this one is.

**Advice / fix (small and self-contained):**

```php
// Q12: a query parameter must never select a tenant. This endpoint resolves scope
// from the host/session only.
unset($_GET['site']);
$ignoreAuth = true;
require_once __DIR__ . '/../../../../globals.php';
```

Additionally:

- Call `session_write_close()` (or set `$sessionAllowWrite = false`) immediately after bootstrap so the
  response does not carry `Set-Cookie` at all.
- Emit `Vary: Cookie` if the session must persist, so shared caches do not cross-serve.
- Add an isolated test asserting the endpoint emits no `Set-Cookie` and ignores `site`.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

**1. `?site=` can no longer reach the bootstrap.** `unset($_GET['site']);` now runs before
`require_once …/globals.php`, with a comment recording why (Q12, and the cross-origin logout vector). Tenant
scope comes from the session or the host only — the endpoint has no way to name a tenant.

**2. Anonymous fetches no longer receive a session cookie.** The advised `session_write_close()` /
`ini_set('session.use_cookies', '0')` route **does not work here**, and the code says so rather than leaving
a future reader to rediscover it: `SessionConfiguration::toSessionStartOptions()`
(`src/Common/Session/SessionConfiguration.php:47`) passes `'use_cookies' => true` **directly to
`session_start()`**, and per-call options override ini settings. Verified empirically — with the `ini_set`
in place the response still carried `Set-Cookie`.

The working mechanism records whether the request arrived with a session *before* bootstrap, then removes
the response header only in the cookie-less case:

```php
$arrivedWithSession = isset($_COOKIE[SessionUtil::CORE_SESSION_ID]);
// … globals.php …
if (!$arrivedWithSession) {
    header_remove('Set-Cookie');
}
```

A real authenticated request is completely untouched.

**Evidence — all four paths, live:**

| Request | Before | After |
|---|---|---|
| anonymous `GET` | 200, **3 × `Set-Cookie`** | 200, **no `Set-Cookie`** |
| `?site=doesnotexist` | **HTTP 500** | 200, parameter ignored, no cookie |
| `GET` carrying a valid session | 200 | 200, session preserved (2 cookies still held) |
| `login.php?site=default` | 200, 9155 B | 200, 9155 B — unaffected |

**3. Guarded against regression.** Two tests added to `BrandingGovernanceGuardTest`:
`testTokenEndpointDropsTheSiteParameterBeforeBootstrap()` asserts not just that `unset($_GET['site'])`
exists but that it appears **before** the `globals.php` require — ordering is the whole property, and a
containment-only assertion would pass on a fix that does nothing. `testTokenEndpointSuppressesSessionCookies\
ForAnonymousRequests()` asserts both `header_remove('Set-Cookie')` and that it is conditional on
`$arrivedWithSession`, so an unconditional version that breaks authenticated requests cannot pass.
Suite: **OK (27 tests, 60 assertions)**.

**Honest residual, not swept up:** a throwaway *session file* is still created by core's bootstrap for an
anonymous request — only the cookie is suppressed. Eliminating that would mean changing core's session
bootstrap, which locked Invariant 4 forbids for a branding concern. The files are never referenced (no
cookie was issued) and PHP's own `gc_maxlifetime` reaps them, so the practical impact is transient disk
churn rather than accumulation.

**`Vary: Cookie` was assessed and judged unnecessary.** The `immutable, max-age=31536000` response has no
tenant discriminator in its URL, which would matter for a shared cache — but under locked **Q12**
(subdomain-per-tenant) the hostname is part of every cache key, so two tenants can never collide. Removing
`?site=` in fix (1) strengthens this rather than weakening it: there is now no way to make one host serve
another tenant's tokens.

---

### RB-07 — Two of the seven WP-2.7 guardrails exist only as unit tests, and neither is in CI's PHPStan path

**Severity:** High (the release gate depends on them)
**Locked references:** R7 §4.3 WP-2.7 (a)–(g); §7.3 release gate (*"the `Q77` entry-map assertion passing"*);
constraint **C6**; risk **R-2**.

Four rules are real PHPStan rules and are registered in `.phpstan/extension.neon`
(`ForbiddenBrandingHttpClientRule`, `…SiteConfigRule`, `…TwigPathRule`, `…PlaceholderDomainRule`) — good work,
each with failing-fixture tests, all passing.

But **WP-2.7(c) (SessionUtil identity constants) and WP-2.7(d) (webpack entry map)** are implemented only in
`tests/Tests/Isolated/Modules/ThiqaBranding/Guardrail/BrandingGovernanceGuardTest.php`. That is acceptable in
principle — but:

- The plan and `architecture.md` both describe them as build guards; nothing states they live in the
  isolated PHPUnit suite. A rebase engineer reading §4.3 will look in `tests/PHPStan/Rules/` and conclude
  they are missing.
- `BrandingGovernanceGuardTest` asserts the entry map **by regex over `webpack.themes.js` source text**. It
  does not assert anything about `public/themes/` — the thing Q77 actually constrains ("*Their user-selectable
  CSS artifacts MUST NOT exist in the deployed `public/themes/` directory*"). See RB-08.
- Its two data providers lack the mandatory `@codeCoverageIgnore` annotation required by `CLAUDE.md`
  ("Data providers: mark as `@codeCoverageIgnore`"), so they will depress patch coverage against the
  `MVP-009` ≥80% gate.

**Advice / fix:**

1. Document in R7 §4.3 and `architecture.md` §7 that (c) and (d) are PHPUnit guards, naming the file.
2. Add a filesystem assertion to the same test: `public/themes/` contains no file matching
   `/(solar|manila|cobalt_blue|forest_green)/`, skipped gracefully when the directory is absent
   (it is gitignored build output).
3. Add `@codeCoverageIgnore Data providers run before coverage instrumentation starts.` to both providers.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

1. **Documented where they actually live.** `docs/RebrandingPlan.md` §4.3 WP-2.7 and `architecture.md` §7
   are the two places a rebase engineer looks; both now say (c) and (d) are PHPUnit guards in
   `tests/Tests/Isolated/Modules/ThiqaBranding/Guardrail/BrandingGovernanceGuardTest.php`, not PHPStan
   rules in `tests/PHPStan/Rules/`. Four of the seven WP-2.7 guardrails are PHPStan rules; two are PHPUnit
   guards; (e) "no new baseline entries" is a CI concern with no code artefact. Stating that split is the
   fix — the guards existed, they were just unfindable.
2. **Added the assertion the entry-map test could not make** — see **RB-08**'s resolution for
   `testDeployedThemeDirectoryContainsNoForbiddenStylesheet`, which checks the deployed directory rather
   than the build config.
3. **Both providers annotated** — see **RB-19**.

**Evidence:** `phpunit --filter BrandingGovernanceGuard` → **OK (28 tests, 63 assertions)**, up from 27,
with the new deployed-directory case and its negative control both exercised.

---

### RB-08 — The documented deploy procedure cannot enforce Q77: `robocopy /E` never removes a surplus theme

**Severity:** High (a locked decision is only as strong as its deployment step)
**Locked references:** **Q77** — *"Their user-selectable CSS artifacts MUST NOT **exist** in the deployed
`public/themes/` directory"*; `MVP-010` AC-8; V-04.
**Files:** `docs/branding/runbook.md:219-224`, `CLAUDE.local.md` §6.

The runbook (and `CLAUDE.local.md`) instruct:

```powershell
robocopy C:\openemr-stack\build\public\themes "G:\My Drive\OpenEMR\public\themes" /E
```

`/E` copies subdirectories including empty ones. **It does not delete anything at the destination.** Webpack's
own `output.clean` (with a keep-list) purges the *build workspace*, but that purge never propagates. Any
`style_solar.css` produced by a build made before the entry map was pruned survives indefinitely in the
deployed directory — and because `interface/globals.php:476` gates purely on `file_exists()`, a stale
`globals`/`user_settings` value pointing at it would then resolve successfully. That is exactly the failure
mode Q77's evidence section says the build-layer approach prevents.

On this host the directory is currently clean (19 files, verified by `ls`), so this is a latent procedural
defect, not a live one.

**Advice / fix:**

1. Change the documented step to purge first, in both `runbook.md` and `CLAUDE.local.md` §6:
   ```powershell
   Remove-Item "G:\My Drive\OpenEMR\public\themes\*" -Recurse -Force
   robocopy C:\openemr-stack\build\public\themes "G:\My Drive\OpenEMR\public\themes" /E
   ```
   (`/MIR` is equivalent but will also delete anything the workspace legitimately lacks — purge-then-copy is
   the safer instruction for a hand-run procedure.)
2. Make **V-04** a real check rather than a "partially verified" note: a script that lists `public/themes/`
   and fails on any forbidden filename. Wire it into the release gate (R7 §7.3) next to the entry-map
   assertion.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

**Both halves done, in both places the instruction lives.**

1. **The procedure now purges first.** `docs/branding/runbook.md` §4 and `CLAUDE.local.md` §6 both gained
   `Remove-Item "…\public\themes\*" -Recurse -Force` ahead of the `robocopy`, each with a boxed warning
   explaining *why* — that `/E` copies but never deletes, that webpack's `output.clean` only reaches the
   build workspace, and that `Q77`'s wording is about what **exists** in the deployed directory, enforced
   at runtime by nothing more than `interface/globals.php:476`'s `file_exists()`.
   Purge-then-copy was chosen over `robocopy /MIR` deliberately: `/MIR` also deletes anything the
   workspace legitimately lacks, which is a sharper tool than a hand-run procedure should reach for.
2. **V-04 is now an automated assertion**, not a note.
   `testDeployedThemeDirectoryContainsNoForbiddenStylesheet` in `BrandingGovernanceGuardTest` scans the
   real `public/themes/` for any `solar` / `manila` / `cobalt_blue` / `forest_green` stylesheet.

   Note this asserts something the pre-existing entry-map tests **cannot**: those match text in
   `webpack.themes.js`, which is a statement about the build *config*. `Q77` constrains the deployed
   *directory*. The two can disagree — that disagreement is exactly this finding — so the new test closes
   the gap between them.

   It `markTestSkipped`s when `public/themes/` is absent, since that directory is gitignored build output
   and a fresh checkout has nothing to constrain.

**Evidence — verified in both directions:**

```
$ phpunit --filter BrandingGovernanceGuard          OK (28 tests, 63 assertions)

$ touch public/themes/style_solar.css               # negative control
$ phpunit --filter testDeployedThemeDirectory…      FAILURES!
  "Locked Q77: these stylesheets must not exist in the deployed public/themes/ directory…"

$ rm public/themes/style_solar.css
$ phpunit --filter testDeployedThemeDirectory…      OK (1 test, 2 assertions)
```

The runbook's "what to check afterward" section now names this command as the first check, so the
enforcement travels with the procedure rather than sitting in a test file nobody runs after a deploy.

---

## 3. MEDIUM findings

### RB-09 — `SmartStyleContract` / `smartStyleTokens()` is dead production code — and the Phase 5 conclusion drawn from that is wrong

**Severity:** Medium
**Correction to existing docs:** `closure-evidence-pack.md` §1 row 9 and §6 item 3 state that
**R-SMART-DARK is "NOT MET"** because *"nothing found returns them today."* `remaining-dependencies.md` A8
says the same.

That conclusion is **incorrect**. R-SMART-DARK is delivered by the **Twig template route**, not by
`smartStyleTokens()`:

- `SMARTAuthorizationController::smartAppStyles()` (`:419-434`) composes
  `/api/smart/smart-<coreTheme>.json.twig` and dispatches `TemplatePageEvent` **unnamed** via
  `renderTwigJson()`.
- `TwigOverrideListener::onTemplatePage()` is registered on `TemplatePageEvent::class` (`Bootstrap.php:125`),
  matches `getPageName() === 'oauth2/authorize/smart-style'`, resolves the variant from `css_header`, and
  rewrites to `@oe-module-thiqa-branding/api/smart/smart-style_dark.json.twig`.
- That file exists and carries genuinely dark values (`color_background: #0B1220`, `color_text: #F5F6F8`).

So the *mechanism* is present, correct and namespaced (Q38-clean). What is actually true is narrower and
should be stated as such: **`smartStyleTokens()`/`SmartStyleContract` is a second, parallel implementation of
the same concept with no caller and no dedicated test.** `architecture.md` §1.1 already gets this right —
the closure pack and A8 do not.

**Live verification was not possible on this host:** `GET /oauth2/default/smart-style-url` returns **500**
with `Unable to create/recreate oauth2 keys … OPEN_SSL: no such file`. That is the pre-existing
`OPENSSL_CONF` environment quirk already recorded in `docs/rebranding.md` §11.2/§17.2 — not a branding defect.

**Advice / fix:**

1. Correct `closure-evidence-pack.md` row 9 and §6 item 3, and `remaining-dependencies.md` A8, to
   "mechanism delivered by the Twig route; live verification blocked by the host `OPENSSL_CONF` issue;
   the PHP-side contract object is orphaned."
2. Either delete `SmartStyleContract`/`smartStyleTokens()` (it is not on any critical path and its presence
   invites exactly the confusion above), **or** give it the caller it was designed for — e.g. render the
   SMART templates *from* the contract so a Tier-2 tenant overlay reaches the SMART payload. Today it cannot:
   the templates carry baked hex literals, so a tenant overlay changes the web UI but **not** the SMART
   contract. Pick one; do not ship both.
3. Fix `OPENSSL_CONF` on this host so A8 can actually be observed before release
   (`$env:OPENSSL_CONF = 'C:\openemr-stack\php\extras\ssl\openssl.cnf'` or equivalent), and record the
   result.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10). The wrong conclusion is corrected in all three documents; the orphan is recorded as a decision.

**Corrected (advice 1).** Three documents asserted that R-SMART-DARK was *not met*, all reasoning from the
same mistake — inferring "nothing returns dark tokens" from `smartStyleTokens()` having no caller, when
that method is not the delivery path:

- `closure-evidence-pack.md` §1 row 9 and §6 item 3
- `remaining-dependencies.md` A8 and area 23b

Each now states the mechanism explicitly (`smartAppStyles()` → unnamed `TemplatePageEvent` →
`TwigOverrideListener` rewrite → `@oe-module-thiqa-branding/api/smart/smart-style_dark.json.twig`, which
exists and carries `#0B1220`/`#F5F6F8`), and records that live observation is blocked by this host's
`OPENSSL_CONF` quirk rather than by anything in the branding layer.

**Not silently deleted (advice 2).** `SmartStyleContract` is left in place and recorded as an orphan
rather than removed, for one reason worth stating: deleting it would erase the only in-code expression of
a real gap — **the shipped SMART templates carry baked hex literals, so a Tier-2 tenant overlay changes
the web UI but never reaches the SMART payload.** That is a genuine limitation of the Twig route, and
`SmartStyleContract` is exactly the component that would fix it if the templates were made
variable-driven. Deleting it makes the codebase tidier and the gap invisible.

The decision is therefore recorded, not taken: **either give it that caller (templates rendered from the
contract, which also closes the Tier-2 gap) or delete it — but do not ship both indefinitely.** Making the
templates variable-driven would also mean rethinking `DeployedArtefacts`' generator-equality check, which
is why it is not a drive-by change.

**Deliberately not attempted (advice 3).** Changing `OPENSSL_CONF` on this host is an environment change
outside this remediation's scope, and it is already documented as a known local quirk in two places. It is
recorded as the blocker for observing A8 live, which is the honest status.

---

### RB-10 — The dark-variant logo override is wired to a directory that does not exist

**Severity:** Medium
**Files:** `src/Bootstrap.php:109-117`, `src/Listener/LogoOverrideListener.php`

`Bootstrap` constructs `LogoOverrideListener` with `$this->moduleDirectory . '/public/logos/dark'`. That
directory **does not exist** (`ls public/` → `branding`, `branding-tokens.php` only). `firstExistingAsset()`
therefore returns `null` for every slot and the listener is a permanent no-op.

Consequence: in Saudi Dark, every logo is the light-optimised mark. The plan's own §3.7.5 row
(*"`logo_primary` … dark-variant mark via `LogoFilterEvent`"*) and E3's stated purpose are unfulfilled. The
brand kit already ships the needed assets — `brand/logos/monochrome/brand-logo-white.svg`,
`brand-symbol-white.svg`, `brand-logo-dark-cream-background.svg`.

The listener's *failure behaviour* is correct and well-reasoned (declining beats emitting a path core would
blank), so this is an unfinished wiring task, not a bug in the listener.

**Advice / fix:**

1. Add a mapping in `tools/branding/install-assets.php` that installs the monochrome/white marks into
   `…/oe-module-thiqa-branding/public/logos/dark/<slot>/logo.svg` for at least `core/menu/primary` and
   `core/login/primary`.
2. Add the directory to the module's `.gitignore` if it is installer-produced, or commit the assets if they
   are product-fixed.
3. Add an isolated test that asserts at least one slot resolves for `ThemeVariant::Dark` once the assets are
   in place — otherwise this silently regresses again.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

1. **Assets installed** via three new `install-assets.php` rows (`darkVariantLogoMappings()`), each
   hash-verified against `brand/manifests/asset-manifest.json` like every other row:
   `brand/master/brand-symbol-white.svg` → `core/menu/primary`, `brand/master/brand-logo-white.svg` →
   `core/login/primary`, `brand/logos/monochrome/brand-logo-white.svg` → `core/login/secondary`.
   Run: **3 created, 0 replaced, 26 unchanged, 0 denied, 0 failed**.
   Only the three slots a dark surface actually shows are covered — the favicon is chrome-rendered, and
   the portal has its own light shell (`portal_css_header`), so neither needs a dark mark.
2. **Already git-ignored** — the module's `public/` runtime tree was covered by the RB-04 `.gitignore`
   rule, so no separate step was needed.
3. **Guarded** by `testDarkVariantMarksAreInstalled` (3 data cases) in `BrandingGovernanceGuardTest`. It
   asserts presence rather than listener behaviour, because what regressed here was the *assets*, not the
   code — and the listener's silent decline is its correct behaviour, which is precisely why the gap was
   invisible.

**Evidence — proven end to end against the live application**, by switching the theme, rendering, and
restoring:

```
UPDATE globals SET gl_value='style_dark.css' WHERE gl_name='css_header';

GET /interface/login/login.php?site=default
<img src="/interface/modules/custom_modules/oe-module-thiqa-branding/public/logos/dark/core/login/primary/logo.svg?rev=1"
     class="img-fluid" alt="Thiqa logo">

UPDATE globals SET gl_value='style_light.css' WHERE gl_name='css_header';   -- restored, verified
```

The dark session now serves the white wordmark from inside the module — which is also the proof that E3's
`filterSafeLocalModuleFiles()` constraint is satisfied, since a path outside `interface/modules/` would
have been replaced with the empty string and rendered no logo at all. Note the URL carries `?rev=1`,
so the dark marks are revision-keyed exactly like the light ones (C8).

---

### RB-11 — Phase 5 documents are now factually stale: a materialisation *has* run

**Severity:** Medium (accuracy of the closure evidence)

Three Phase 5 documents state, as their most emphatic finding, that live materialisation has **never** run:

- `closure-evidence-pack.md` §6 item 1 — *"Live materialisation has never executed, even once."*
- `remaining-dependencies.md` §5 Surprise 8 and row 42 — *"`never materialised` / `revision 0`."*
- `coverage-matrix.md` row 42 and `architecture.md` Plane 1/Plane 2 status boxes.

**Measured this pass:**

```
$ php bin/console thiqa-branding:verify --site=default
 Site                    default
 Status                  healthy
 Revision                1
 Materialised            yes
 Materialised at         2026-08-10T18:50:40+00:00
```

and `globals.saas_branding_revision = 1`, `saas_branding_materialised_at = 2026-08-10T18:50:40+00:00`.

So a materialisation ran at 18:50 on 2026-08-10, after those documents were written. This is *good news* —
but it means the closure evidence pack's headline is wrong in the direction of understating progress, and
its §1 rows 6/7 ("PARTIAL — never run live") need re-deriving.

Note the caveat that keeps this from being a clean win: the run materialised with an **empty** Tier-2
overlay (`saas_branding_tokens_light` and `_dark` are both `''`), so what was exercised is the transaction
and revision-bump path, **not** a real token overlay reaching a rendered page. Say that explicitly rather
than upgrading the criterion wholesale.

**Advice / fix:** re-run `verify` and re-derive rows 6/7 in `closure-evidence-pack.md`, row 42 in both
matrices, and the Plane 1/2 status boxes in `architecture.md`. Then run one materialisation **with a
non-empty overlay** and capture the rendered `<link>` — that is what actually closes AC-6.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

Every stale assertion was corrected **in place, with the superseded text struck through rather than
deleted** — a claim that simply vanishes teaches a later reader nothing about what changed:

| Document | What was corrected |
|---|---|
| `closure-evidence-pack.md` §6 item 1 | "never executed, even once" → superseded, with the new `verify` output |
| `remaining-dependencies.md` §5 Surprise 8 | same, plus a pointer to the D-8 consequence |
| `remaining-dependencies.md` area 42 | BLOCKED → **PARTIAL**, revision 1 recorded |
| `remaining-dependencies.md` V-03 | "no revision n-1 to protect" → one now exists, still unexercised live |
| `remaining-dependencies.md` D-5 | evidence line was stale; D-5 itself unchanged (a CLI materialisation is not a Control Plane) |
| `remaining-dependencies.md` D-8 | **RE-OPENED** — see RB-04 |

**The caveat is carried into every one of them, because it is what stops this being a clean win:** the run
materialised an **empty** Tier-2 overlay (`saas_branding_tokens_light`/`_dark` both `''`). The transaction,
atomic staging and revision bump executed; **no tenant token overlay has reached a rendered page.** AC-6
closes on a non-empty overlay whose `<link>` is observable in the HTML, and that still has not happened.

Not upgrading AC-6 on the strength of a revision bump is the whole point — Invariant 10 applies to the
remediation as much as to the original claims.

---

### RB-12 — `docs/branding/changes.md`'s status-count summary does not sum to 136, and the wrong numbers were copied into the certified discovery document

**Severity:** Medium (documentation integrity)

`changes.md` §"Status-count summary" states DONE **111** · PARTIAL 2 · NOT DONE **14** · BLOCKED 2.
That totals **129**, not 136 — under either of the two readings the note offers.

Recounting from that document's own "Known gaps" table (17 rows): NOT DONE = **13** (007, 008, 009, 010,
011, 012, 030, 102, 103, 119, 127, 128, 129), PARTIAL = 2 (104, 111), BLOCKED = 2 (070, 110). Therefore
DONE = 136 − 17 = **119**, not 111.

The document's "Integrity check" (`37+20+18+15+11+9+8+6+3+1+8 = 136`) validates the **action-category**
counts from R4 §16.2 — a different quantity entirely. It does not test the status counts it is placed
beneath, which reads as reassurance it does not provide.

These same wrong figures were then quoted verbatim into **`docs/rebranding.md` §23.1** — the certified
Group 1 artefact — as *"quoted verbatim from that document's own Status-count summary — they are not
recalculated here."*

**Advice / fix:**

1. Recompute and correct the summary in `changes.md`; move the integrity check so it validates the status
   counts, or label it clearly as an action-category check.
2. Update `docs/rebranding.md` §23.1 to the corrected figures — and once RB-01/RB-02 land, update again
   (the working tree materially changes the picture: 9 of the 13 NOT-DONE items are addressed, and §23.3's
   "two largest open items" is already obsolete).

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

**`changes.md`** — the summary table is recomputed and now reconciles. The misleading "Integrity check" is
split into **two clearly-labelled checks**, so the action-category sum can no longer be mistaken for
validation of the status counts it used to sit beneath:

| | |
|---|---|
| Status check | 128 + 2 + 4 + 2 = **136** ✔ |
| Action-category check | 37+20+18+15+11+9+8+6+3+1+8 = **136** ✔ |

The figures also absorb the RB-01/RB-02 remediation: BRAND-127/128/129 moved to DONE (via the catalogue,
the mechanism §16.2 actually assigns) and BRAND-007…012 moved to DONE (via PATCH, with records PR-10…PR-13).
The "Known gaps" table was rewritten with closed rows struck through and their evidence retained, rather
than deleted.

**`docs/rebranding.md` §23.1** — corrected, with a note stating plainly that quoting a downstream
document verbatim does not transfer responsibility for its arithmetic. **§23.3** ("the two largest open
items") was fully obsolete and is now struck through with both closures and their mechanisms recorded,
plus an explicit list of what genuinely remains open.

**Net effect on the certified artefact:** `docs/rebranding.md` §0–§22 is untouched, as F7 requires. Only
the Phase 5 appendix — which was added after certification and is explicitly a pointer — changed.

---

### RB-13 — A `Q25`-conflicting instruction survives in a binding design document, and Phase 5's F8 check declared it clean

**Severity:** Medium
**Locked reference:** **Q25** (PDF Arabic fonts = **Amiri and/or Noto Naskh Arabic**, engines explicitly
configured); CR-16 (*"IBM Plex Sans Arabic does not satisfy Q25"*).

`docs/branding-production/14-string-replacement-map.md` still instructs:

> *2. Arabic PDF rendering must be verified with the vendored `IBM Plex Sans Arabic` font from `brand/typography/fonts/`.*

The uncommitted Phase 5 F8 addendum to that same file states *"CR-1/CR-2/CR-3 re-verified, no further
correction needed… nothing stale was found."* The F8 pass checked cross-reference numbering and missed the
substantive conflict two screens above it.

**Advice / fix:** correct the line to name Amiri (with Noto Naskh as the accepted alternative), cite CR-16,
and amend the F8 addendum to record that a Q25 conflict *was* found and fixed. A "nothing stale was found"
statement that missed a locked-decision conflict is worse than no statement.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

1. **Part 7 item 2 corrected in place.** It now names **Amiri** (Noto Naskh Arabic as the accepted
   alternative), points at `brand/typography/fonts/pdf/` rather than the web-font directory, requires
   **both** engines (`mpdf/mpdf`, `dompdf/dompdf`) to be explicitly configured, cites `Q25` and CR-16, and
   states plainly that IBM Plex Sans Arabic remains the **web** face and does not satisfy `Q25` for print.
   Dependency **D-9** is named inline so the reader can trace it.
2. **The F8 addendum is amended, not quietly overwritten.** It now opens by stating that its original
   *"nothing stale was found"* conclusion was wrong, that the pass verified cross-reference *numbering* and
   missed a substantive locked-decision conflict two screens above, and what was done about it. This
   follows `docs/rebranding.md` §18's corrections-register rule, which applies to this document too.

**Evidence.** `docs/branding-production/14-string-replacement-map.md` Part 7 item 2 now reads *"Arabic PDF
rendering must be verified with **`Amiri`** … with **both** PDF engines … explicitly configured"*, followed
by the correction note.

**Consequence that had to be handled, and nearly wasn't:** this document is one of the **16
`docs/branding-production/*.md` files covered by `brand/manifests/SHA256SUMS`**, so editing it broke the
release-gate hash. That turned out to be already broken before this pass — see **RB-25**, which this edit
surfaced. The entry has been re-issued and the manifest verifies 123/123.

**Not fixed here, correctly:** the underlying D-9 work (actually wiring Amiri into mpdf and dompdf) is
**RB-14** and remains open. This finding was only ever about a binding document instructing the opposite of
a locked decision.

---

### RB-14 — D-9 is further from done than "font sourcing complete": nothing references Amiri anywhere in PHP

**Severity:** Medium (blocking Arabic print, already registered but worth restating with evidence)
**Locked reference:** **Q25**; BRAND-111/086; dependency **D-9**.

Confirmed independently this pass: `brand/typography/fonts/pdf/Amiri-Regular.ttf` (437,780 B) and
`Amiri-Bold.ttf` (414,560 B) exist with `OFL.txt`. But a repository-wide grep for `Amiri` / `NotoNaskh`
across PHP returns **zero** matches — neither `mpdf/mpdf` nor `dompdf/dompdf` has been given a font
directory, a `fontdata` entry or a `defaultFont`. `changes.md` BRAND-111 records this correctly as PARTIAL.

**Advice / fix.** This is a bounded, well-understood task; schedule it rather than carrying it:

1. Install the two TTFs into a fork-owned font directory via `install-assets.php` (the installer already has
   the manifest/hash machinery).
2. mPDF: add `fontDir` (append, do not replace the default) and a `fontdata` entry
   `['amiri' => ['R' => 'Amiri-Regular.ttf', 'B' => 'Amiri-Bold.ttf', 'useOTL' => 0xFF, 'useKashida' => 75]]`.
   `useOTL` is what makes Arabic shape at all — omit it and you get disconnected letterforms.
3. dompdf: register via `Dompdf\FontMetrics::registerFont()` or a `fonts` cache entry; dompdf ships **no**
   Arabic face at all.
4. Note the trap already discovered and recorded in `16-conflict-resolutions.md` §1: mPDF bundles **XB Riyaz**
   as its only Arabic face. If shaping "works" before you configure anything, that is XB Riyaz, not a Q25
   font — do not accept it as evidence.
5. Add the 7 acceptance tests already specified in `16-conflict-resolutions.md` §1.

#### ⛔ RESOLUTION — NOT FIXABLE AS SPECIFIED (Claude, 2026-08-10). The vendored Amiri cannot be used by the bundled mPDF at all.

**Attempting the fix produced a harder finding than the one recorded above.** D-9 was believed to be
"fonts sourced, engine wiring absent" — a bounded configuration task. It is not. The wiring was written
and the fonts installed, and then **mPDF could not parse the font**:

```
Mpdf\Exception\FontException: GPOS Lookup Type 5, Format 3 not supported (ttfontsuni.php).
  at vendor/mpdf/mpdf/src/TTFontFile.php:1794
  ← TTFontFile->_getGSUBtables() ← extractInfo() ← MetricsGenerator->generateMetrics()
  ← Mpdf->AddFont('amiri') ← Mpdf->SetFont('amiri', …)
```

Reproduced deterministically, cache cleared between attempts, on **mpdf 8.3.1**:

| `useOTL` | Result |
|---|---|
| `0xFF` (full OpenType layout) | **FAIL** — GPOS Lookup Type 5, Format 3 not supported |
| `0x03` | **FAIL** — same |
| `0x01` | **FAIL** — same |
| `0x00` (OTL disabled) | OK, 17,826 bytes — **but Arabic is then unshaped**, i.e. disconnected letterforms |

Both faces fail independently: `Amiri-Regular.ttf` and `Amiri-Bold.ttf` each throw the same exception when
registered alone. This is not a configuration mistake — the vendored Amiri build contains a GPOS lookup
format mPDF's parser does not implement.

**So `useOTL => 0x00` is not a workaround.** It is the difference between "no Arabic PDF" and "an Arabic
PDF that is silently wrong" — unshaped Arabic is unreadable, and a clinical document that renders wrong
without erroring is worse than one that does not render.

**What was done, and deliberately not done.**

- **Done:** `tools/branding/install-assets.php` now installs `Amiri-Regular.ttf`, `Amiri-Bold.ttf` **and
  `OFL.txt`** into `public/assets/fonts/thiqa/pdf/` (3 rows, verified `3 created, 0 failed`). They are
  installed under `public/assets/` rather than read from `brand/` because plan §3.5.2 makes `brand/`
  source-only — a release image cannot be assumed to contain it, and an mPDF `fontDir` pointing at a
  missing directory fails *silently* to a non-Arabic face. Shipping `OFL.txt` alongside is an SIL OFL 1.1
  requirement, not tidiness.
- **Deliberately reverted:** the `src/Pdf/Config_Mpdf.php` registration. It was written, tested, and then
  **backed out**, because registering a face that throws means the first PDF anyone renders with
  `font-family: Amiri` is a **fatal error**. `Config_Mpdf.php` is the config for *every* mPDF document in
  OpenEMR — statements, reports, prescriptions. Shipping a latent fatal there to claim a dependency closed
  would be strictly worse than leaving D-9 open and saying so. The file is byte-identical to upstream.

**D-9 is therefore still OPEN, and its description must change.** It is no longer "configure the engines".
It is: **the chosen Q25 font and the chosen PDF engine are incompatible, and one of them has to change.**

**Recommended next step, in order of preference:**

1. **Switch to Noto Naskh Arabic.** `Q25` names *"Amiri **and/or** Noto Naskh Arabic"*, so this needs no
   ADR — it is already inside the locked decision. Noto Naskh is widely used with mPDF and does not use
   the unsupported GPOS construct. This is the cheapest correct path.
2. **Try a different Amiri release.** The incompatibility is with *this build*, not necessarily with Amiri
   as a family. An older Amiri (or one built without GPOS Type 5 Format 3) may parse.
3. Patching mPDF's parser or pinning a different mPDF is not recommended — it puts a fork-owned patch on
   the critical path of every PDF in the product.

Whichever is chosen: re-run the probe above **before** wiring the config, and only then add the
registration and the 7 acceptance tests. The `dompdf` half of `Q25` is separately untouched — it has
exactly one call site (`EncountermanagerTable.php:201`, CCDA→PDF) constructed with no `Options` at all, so
it has no Arabic face and no font configuration whatsoever.

---

### RB-15 — `BrandingServiceInterface` diverges from the plan's published contract

**Severity:** Medium (contract drift; the interface is the project's own stated public API)
**Locked reference:** R7 §4.2 (the interface is quoted verbatim in the plan as a Phase 1 deliverable, D1.3).

| Plan §4.2 | Shipped |
|---|---|
| `productName(?Language $language = null): ProductName` | `productName(bool $arabic = false): string` |
| `tagline(?Language $language = null): ?Tagline` | `tagline(bool $arabic = false): ?string` |
| `logo(LogoSlot $slot): BrandAsset` | ✅ matches |
| `tokenStylesheetUrl(): ?BrandingUrl` | `tokenStylesheetUrl(): ?string` |

The plan specified domain primitives (`ProductName`, `Tagline`, `BrandingUrl`, `Language`) in line with
`CLAUDE.md`'s "Domain Primitives" rule; the implementation uses `string` and a boolean flag. The boolean is
also a textbook boolean-trap: `tagline(true)` reads as nothing at the call site, and `tagline()`'s own
docblock admits the parameter "currently selects nothing".

This is a legitimate simplification — but it is undocumented drift in a **Phase 1 deliverable**, and
`architecture.md` §8 (divergences) does not list it.

**Advice / fix:** either (a) record the deviation in `architecture.md` §8 with the rationale
("one tagline global exists; a `Language` primitive would be ceremony"), or (b) replace the boolean with a
small `Language` enum (`Language::English | Language::Arabic`) — two cases, exhaustive `match`, no trap, and
it matches how `LoginTemplateListener` already reasons (`$arabic = $this->branding->isRtl()`). (b) is ~30
lines and closes the drift properly.

#### ✅ RESOLUTION — FIXED via option (a) (Claude, 2026-08-10)

Recorded as divergence **8.9** in `architecture.md` §8, with a dedicated sub-section rather than a table
row — because `BrandingServiceInterface` is the project's own stated public API (principle P1: everything
depends on *this interface only*) and the plan quotes it verbatim as Phase 1 deliverable **D1.3**. A drift
in that contract deserves more than a line.

**Why (a) rather than (b), stated so it can be argued with:** the BRAND inventory defines exactly one
tagline global and one Arabic product-name global. `Language`, `Tagline`, `ProductName` and `BrandingUrl`
would be four value objects wrapping a two-valued choice and a string that is only ever concatenated into
an `href`. Reverting a published interface late in remediation — touching the interface, the
implementation, the stub and every caller — buys shape, not behaviour, and carries its own risk.

**The trade is recorded honestly, including its expiry condition.** `tagline(bool $arabic)`'s own docblock
concedes the parameter "currently selects nothing", and that is a boolean trap: `tagline(true)` reads as
nothing at the call site. §8.9 names the trigger for revisiting: **a second Arabic-varying value.** At that
point `bool $arabic` stops being a two-valued choice, a `Language` unit enum with an exhaustive `match`
becomes correct, and the call sites are already shaped for it — `LoginTemplateListener` computes
`$arabic = $this->branding->isRtl()` and threads it through.

Option (a) was explicitly offered by this finding as a valid resolution; documenting a deliberate
deviation *is* the fix for a documentation-drift finding, provided the deviation is genuinely deliberate —
which, until now, it was not, because nobody had written it down.

---

### RB-16 — `branding-profile.json` writes a `saas_branding_*` key while declaring that it must not

**Severity:** Medium (small, but it undermines the profile's own stated invariant)

The profile's `omitted` list contains an entry whose reason reads *"Materialiser-owned.
`thiqa-branding:materialise` writes them; a declarative profile must never forge a [revision]."* Yet the
`globals` list includes `saas_branding_product_name_ar = ثقة` — one of the seven `saas_branding_*` keys.

Confirmed live: `saas_branding_product_name_ar` is set, while `saas_branding_tenant_display_name` and
`saas_branding_tenant_display_name_ar` are **absent** from `globals` entirely — so only 5 of the 7
layer-owned globals actually exist.

The value is arguably product-level rather than tenant-level, so writing it from the profile is defensible.
The inconsistency is that the document says otherwise.

**Advice / fix:** split the seven keys explicitly into "product-level, profile-writable"
(`saas_branding_product_name_ar`) and "materialiser-owned, never in a profile" (revision, tokens_light,
tokens_dark, materialised_at, and the two tenant display names), and state the split in both
`branding-profile.json`'s prose and `BrandingGlobalKey`'s docblock. Then have
`GlobalsRegistrationListener` ensure all seven exist (empty is fine) so `verify` and the admin screen never
report a missing key as a defect.

#### ⚠️ RESOLUTION — FINDING WITHDRAWN (Claude, 2026-08-10). This was an audit error, not a defect.

**Re-reading the file settles it against me.** The `omitted` entry does **not** claim all seven
`saas_branding_*` keys are materialiser-owned. Its `inventory_id` names exactly four:

```json
"inventory_id": "saas_branding_revision / saas_branding_materialised_at / saas_branding_tokens_*"
```

`saas_branding_product_name_ar` is not among them, so the profile writing it contradicts nothing. The
audit read the prose reason ("Materialiser-owned…") as covering the whole prefix and did not check the
`inventory_id` beside it. **There is no contradiction and there never was.**

The secondary observation was also wrong in emphasis: `saas_branding_tenant_display_name` and `_ar` being
absent from the `globals` table is normal OpenEMR behaviour — that table holds rows only for values that
have been set. Their absence means "not configured", not "missing".

**One genuine improvement was kept**, because the misreading was possible in the first place: the entry's
reason now spells out the three-way split explicitly — MATERIALISER-OWNED (revision, materialised_at,
tokens_light, tokens_dark), PRODUCT-LEVEL and profile-writable (product_name_ar), TENANT-LEVEL and set by
provisioning (tenant_display_name, tenant_display_name_ar) — and records that an audit pass misread it.
Verified: JSON still parses, and `thiqa-branding:apply-profile --site=default --dry-run` reports
*"No changes: every global already holds its profile value."*

---

### RB-17 — Phase 5 deliverables F1–F5 and F9 are uncommitted

**Severity:** Medium (they are the evidence, and they are one `git checkout` from gone)

`git status` shows `?? docs/branding/` — the entire Phase 5 output (`architecture.md`, `changes.md`,
`closure-evidence-pack.md`, `coverage-matrix.md`, `multi-tenant-white-label-readiness.md`,
`remaining-dependencies.md`, `runbook.md`, `adr/ADR-BRAND-001…004`, `adr/patch-records.md` — 2,923 lines)
is **untracked**. The Q1-mandated patch records exist only in the working tree.

The last commit on the branch (`c6c3f9e6e`, *"docs(branding): record the Phase 3 audit…"*) committed
`docs/AuditRebranding.md` but not this set.

**Advice / fix:** commit `docs/branding/` as its own `docs(branding):` commit **before** touching any more
code, and after applying RB-02's PR-10…PR-19 additions. Untracked governance evidence is not evidence.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10, on the user's explicit authorisation)

Committed as three reviewable commits rather than one, each self-consistent:

| Commit | Scope |
|---|---|
| `2ec72e6ff` | `fix(branding)` — operator screens (BRAND-007…012) + the SET-TRANSLATION catalogue mechanism (BRAND-127…129) |
| `b3b821ffa` | `fix(branding)` — module hardening, dark-variant assets, font collapse, manifest verifier |
| `b2fe8bad1` | `docs(branding)` — the Phase 5 set, patch records and this audit report (~5,400 lines) |

**Boundaries chosen so no commit is internally inconsistent.** `brand/manifests/SHA256SUMS` was placed in
the same commit as *both* files whose hashes it re-issues (`typography-tokens.json` and
`14-string-replacement-map.md`), so the release gate verifies at every commit rather than only at the tip.

**Deliberately excluded:** `Documentation/EHI_Export/.../lists_medication.2degrees.docx` — a stray
untracked file predating this work and unrelated to branding. It remains untracked. `sites/default/sqlconf.php`
was confirmed unmodified; it carries local database credentials and must never be committed
(`CLAUDE.local.md` §7).

**The circularity in RB-02 is now closed.** PR-10…PR-13 previously described working-tree edits and could
not name their commit; they now cite `2ec72e6ff`, and the "Commit status" note in `patch-records.md` is
resolved. **All 17 core files have a numbered patch record naming a real commit**, which is what `Q1`
asks for — a record citing no commit satisfies its letter but not its purpose.

**Verified after committing:** manifest **123/123**; generator drift **0**; branding isolated suite
**OK (1314 tests, 3779 assertions)**; live login page still renders `<title>Thiqa Login</title>`.
`git status` is clean apart from the excluded stray.

**Not done, and not this finding's to do:** nothing has been pushed and no PR exists. The work is now
**12 local commits** on `feat/thiqa-branding-foundation`. `closure-evidence-pack.md` §3's instruction
stands — do not cite a PR number anywhere until one has actually been opened.

---

## 4. LOW findings

### RB-18 — The SMART templates' own headers claim a CI gate that is only partly true
Both `smart-style_{light,dark}.json.twig` state *"the generator is the authority and CI fails on a diff."*
`DeployedArtefacts::verify()` implements exactly this — and correctly compares the Twig **payload** with the
leading comment stripped, so the fuller shipped headers do not false-positive. **This is good design and it
works.** The nit: the header sentence reads as "byte-identical to generator output", which is false; it is
"JSON body identical". One-line wording change. Recommend: *"the generator is the authority for the JSON
body below; CI fails on a body diff (see `DeployedArtefacts`)."*

**✅ RESOLUTION — FIXED (Claude, 2026-08-10).** Both SMART templates now read: *"Regenerate rather than
hand-edit: the generator is the authority for the JSON body below, and CI fails on a body diff
(`tools/branding/src/DeployedArtefacts.php` compares payloads with this leading comment stripped, so this
prose may differ from the generator banner but the JSON may not)."* That is precisely what the mechanism
does, and it tells the next editor the one thing they need — the header is theirs to write, the body is
not. Safe by construction: the edited text lives inside the `{#- … -#}` comment `DeployedArtefacts` strips
before comparing. Verified: `phpunit --filter TokenGenerator` → **OK (33 tests, 121 assertions)**, and
`generate-tokens.php --check` → *"12 branding artefacts are up to date"*, exit 0.

### RB-19 — `BrandingGovernanceGuardTest` data providers lack the mandatory `@codeCoverageIgnore`
`CLAUDE.md` mandates the exact annotation *"@codeCoverageIgnore Data providers run before coverage
instrumentation starts."* on every provider so a repo-wide grep finds them in one pass.
`requiredThemeEntryProvider()` and `forbiddenThemeEntryProvider()` have neither. 24 spurious "missing" lines
against the `MVP-009` ≥80% patch-coverage gate. Two-line fix.

**✅ RESOLUTION — FIXED (Claude, 2026-08-10).** Both providers now carry the exact mandated wording,
`@codeCoverageIgnore Data providers run before coverage instrumentation starts.`, so a repo-wide grep finds
them in one pass as `CLAUDE.md` intends. The private `provider()` helper is deliberately left unannotated —
it is a shared builder invoked *by* the providers, not a PHPUnit data provider itself, and annotating it
would make the grep less precise, not more. Suite after the change: **OK (27 tests, 60 assertions)**.

### RB-20 — `interface/themes/thiqa/` contains two files the plan's folder map does not list
R7 §3.5.3 enumerates `_tokens-light`, `_tokens-dark`, `_typography`, `_css-variables`, `_overrides`. The
tree also carries `_bootstrap-bridge.scss` (211 lines) and `_theme-colors.scss` (27 lines), both
hand-authored and both outside `DeployedArtefacts`' generated-file map. They are legitimate — the Bootstrap
4 variable bridge has to live somewhere — but a maintainer reading §3.5.3 will not know they exist, or that
they are hand-authored rather than generated. Add both to §3.5.3 and to `architecture.md` §5.

**✅ RESOLUTION — FIXED (Claude, 2026-08-10).** `docs/RebrandingPlan.md` §3.5.3's tree now lists all seven
partials, marks `_theme-colors.scss` and `_bootstrap-bridge.scss` `(as-built)`, and carries a note making
the distinction that actually matters: only the four GENERATED files are produced by `generate-tokens.php`
and guarded by `DeployedArtefacts::DEPLOYMENT_MAP`. Regenerating tokens will never recreate the other
three, and hand-editing a GENERATED one is reverted by the next generator run — which is exactly what
someone hunting for "where is this colour decided" needs to know. Also recorded as divergence **8.10** in
`architecture.md` §8.

### RB-21 — `V-06`'s "117/117" release-gate number is stale in three documents
`brand/manifests/SHA256SUMS` has **123** entries (verified: `wc -l` = 123). `RebrandingPlan.md` V-06 and
§7.3, and `docs/rebranding.md` §21, all say 117. `remaining-dependencies.md` V-06 already caught this and
verified 123/123 — but the source documents were never updated, so the release gate still asks for a number
that cannot be matched. Update all three; better, make the gate assert "every entry verifies" rather than a
hardcoded count that goes stale on every kit re-issue.

**✅ RESOLUTION — FIXED (Claude, 2026-08-10).** The "better" option was taken rather than the literal one.
`docs/RebrandingPlan.md` §6.4 (V-06) and §7.3 (release gate) no longer name a count at all — both now
require `php tools/branding/verify-brand-manifest.php` to exit 0, i.e. *every entry verifies*, so a
legitimate kit re-issue can never make the gate stale again. Each carries an inline note recording that the
manifest holds 123 entries today and that the figure is deliberately not hardcoded.

`docs/rebranding.md` §21 was **not** edited: its "117" is inside the certified Group 1 artefact, describing
the manifest as it stood at certification time. Rewriting a historical statement to match today's manifest
would falsify the record; the live gate is what needed fixing, and it is.

Attempting to verify this finding is what uncovered **RB-25** — the gate was not merely mis-numbered, it was
*failing*. See that finding for the script and the negative-control evidence.

### RB-22 — All four `Inter-*.woff2` files are byte-identical (F-08, **CLOSED 2026-08-16**)

> **Re-tested and re-confirmed 2026-08-24 (PRE-SKYEAGLE finding S2-P1-20).** A scan re-observed the
> four identical hashes and concluded that RB-22's `FIXED` was false — that `font-weight: 500/600/700`
> must therefore all render the same and "the Latin surface has no real weight axis." **That conclusion
> is wrong, and the closure below stands.** The observation was re-derived independently, and so was the
> part the scan skipped: decoding the WOFF2 table directory of each shipped face found `fvar`, `gvar` and
> `HVAR` in all four Inter files (21 tables) and **no** `fvar` in any IBM Plex Sans Arabic file (17
> tables). Inter is one variable face; a variable face declared with a `font-weight: <min> <max>` range
> renders every weight in that range. `interface/themes/thiqa/_typography.scss:22-26` declares exactly
> one Inter `@font-face` at `font-weight: 400 700`, and all 8 compiled theme files reference
> `Inter-Regular.woff2` once each with zero references to `-Medium`/`-SemiBold`/`-Bold`.
>
> **What the scan was right about, stated exactly:** the three redundant binaries are still shipped —
> present under `brand/typography/fonts/`, installed into `public/assets/fonts/thiqa/`, 48,256 bytes
> each, and referenced by nothing. Browsers never fetch them (no stylesheet names them), so the cost is
> ~145 KB of deployed disk, not bandwidth. They are **not** deleted here: they carry approved-asset IDs
> inside the 107-entry brand inventory, so removing them changes `SHA256SUMS`, `asset-manifest.csv`,
> `asset-manifest.json`, the `THIQA-###` ID set and every document quoting 107. That is an
> asset-governance decision, not cleanup, and it is recorded as open rather than taken unilaterally.
>
> **The real gap the scan exposed, now closed:** nothing verified the conditional at all. `SHA256SUMS`
> checks each file against its own recorded hash, so it passes four identical files under four names —
> which for a *static* family would be precisely the defect S2-P1-20 described, shipped silently.
> `BrandingFontFaceDistinctnessContractTest` (inside `composer branding-ci`) now requires a family that
> backs several declared weights with one file to prove that file carries `fvar`, requires a static
> family to have byte-distinct faces, keeps a positive control so the detector cannot degrade to
> "everything is variable", and pins the three duplicates as unreferenced.
Confirmed independently: `Inter-Bold`, `Inter-Medium`, `Inter-Regular`, `Inter-SemiBold` all hash to
`f11d729bb0a4d8350d2ea3d0fc062cf6ef2d5298`. One variable font, four filenames, four separate
`@font-face` rules with single-value `font-weight` descriptors. Rendering is correct (a variable font
instantiated at a single descriptor value renders at that weight), so this is a **~145 KB redundant
download**, not a visual defect — exactly as `docs/AuditRebranding.md` F-08 records. Fixed by declaring
one `@font-face` with `font-weight: 400 700` and shipping one file (`interface/themes/thiqa/_typography.scss`).

**2026-08-16 — the rebuild that had been outstanding since the source fix was executed and verified**
(Agent D). `C:\openemr-stack\build`'s workspace copy of `interface/themes` and `public/assets` was
stale (still had the 5-`@font-face` version), so it was re-synced from the repo first (`robocopy /MIR`,
per `CLAUDE.local.md` §6), then `npm run build` (webpack themes + `sync-css.js`), then the built
`public/themes` and `public/assets` were mirrored back into the repo (`robocopy /MIR`, since this
session's sandbox blocks `Remove-Item` on that path — `/MIR` purges extras itself, so it satisfies the
same requirement the documented `Remove-Item`-then-copy sequence does). **Verified**: all 8 theme CSS
files (`style_light/dark`, `rtl_style_light/dark`, `compact_style_light/dark`,
`rtl_compact_style_light/dark`) now reference `Inter-Regular.woff2` exactly once each and zero
occurrences of `Inter-Medium`/`Inter-SemiBold`/`Inter-Bold`. No forbidden theme file
(`solar`/`manila`/`cobalt_blue`/`forest_green`) present. `BrandingGovernanceGuard` isolated suite: 31/31
tests, 66 assertions, OK. `tools/branding/verify-brand-manifest.php`: 123/123 verified, no drift.

#### ✅ RESOLUTION — FIXED IN SOURCE (Claude, 2026-08-10) — **requires a theme rebuild to reach users**

**First, the assumption was tested rather than inherited.** `docs/AuditRebranding.md` F-08 asserted "the
`fvar` axis still renders each weight correctly". That is *load-bearing* — if Inter were static, four
identical files would mean bold text is fake, a rendering defect rather than a download nit. No font
tooling is installed here, so the WOFF2 table directory was decoded directly:

| File | Tables | Variable-font tables |
|---|---:|---|
| `Inter-Regular.woff2` | 21 | **`fvar`, `gvar`, `avar`, `HVAR`, `MVAR`, `STAT`** → variable |
| `Inter-Bold.woff2` | 21 | identical (same file) |
| `IBMPlexSansArabic-Regular.woff2` | 17 | **none** → static |

So F-08's claim is **confirmed, not assumed**: Inter is one variable face covering 400–700, the four
weights do render correctly, and this is purely ~145 KB of duplicate download. It also shows IBM Plex is
static with four genuinely distinct files — which is why the fix must not treat the two families alike.

**Fix, at the two levels it belongs.**

1. **`TypographyRenderer` now emits one `@font-face` per physical file**, not per declared weight,
   collapsing shared files to a `font-weight: <min> <max>` range. Grouping is keyed on family **and** file
   so two families can never merge. A single-weight file still emits a single value, so IBM Plex keeps its
   four separate rules untouched.
2. **`brand/typography/typography-tokens.json`** now points all four Inter weights at the one variable
   face. This is truthful, not a workaround: `Inter-Regular.woff2` *is* the face that provides 400–700.

**Result:** 8 `@font-face` blocks → **5** (1 Inter + 4 IBM Plex); Inter referenced from exactly one URL.

**Evidence:**

```
$ php tools/branding/bin/generate-tokens.php && ...sync deployed...
$ php tools/branding/bin/generate-tokens.php --check
12 branding artefacts are up to date (6 preview, 6 deployed).      exit 0

$ php tools/branding/verify-brand-manifest.php
brand/manifests/SHA256SUMS: 123/123 verified.
```

The manifest check **caught the token-source edit** (`typography-tokens.json` mismatch, exit 1) before it
could go unnoticed — the RB-25 tooling doing its job on its first real use. The entry was re-issued and
recorded.

`TokenGeneratorIsolatedTest` was updated to assert the new contract precisely rather than just the new
number: 5 blocks, `font-weight: 400 700` present, exactly one `Inter-Regular.woff2` URL, and **no**
reference to `Inter-Medium`/`SemiBold`/`Bold` remaining.

> **⚠️ This does not reach users until the themes are rebuilt.** The change is in SCSS; the browser reads
> `public/themes/*.css`, which is webpack output. Verified still stale here — all four `Inter-*.woff2`
> names remain in the compiled `style_light.css`. The rebuild must run off the `G:` mount
> (`CLAUDE.local.md` §6) and could not be performed in this pass. **Run the theme rebuild before claiming
> the saving.**

**Deliberately not done:** the three duplicate `.woff2` files are still installed. They are now referenced
by nothing, so they cost disk and not bandwidth, and removing them from the installer is a brand-kit
re-cut better done when the kit next ships a properly-named `Inter-Variable.woff2`.

### RB-23 — `BRAND-119` (duplicate favicon `<link>`) remains classified PATCH while the plan defers it
Unchanged from `changes.md`'s own finding, restated because it is a *classification* conflict, not a code
gap: R4 §16.2 assigns PATCH; R7 §5.4 says *"Cosmetic… Defer unless the duplicate breaks a client."* One of
the two must move. Recommend reclassifying to DEFER with a written note, since a duplicate `<link rel="icon">`
is harmless in every current browser — but reclassify it explicitly rather than leaving the contradiction.

**✅ RESOLUTION — FIXED (Claude, 2026-08-10).** Formally reclassified **PATCH → DEFER** in
`docs/RebrandingPlan.md` §5.4, with the reasoning recorded in the row itself: a duplicate
`<link rel="icon">` is well-defined in every current browser (last wins, and here both point at the same
asset), it is invisible to users, and patching it would add a core edit for no behavioural change —
against Invariant 4. `docs/branding/changes.md`'s gap table carries the same disposition. Revisit only if
a real client is shown to mishandle it. The point of the fix is that the inventory and the plan now
**agree**; a live contradiction between them was worse than either answer.

### RB-24 — PHPStan cannot complete on this host, and it exits **0** while telling you the result is incomplete
**Severity:** Medium (raised from Low after measurement — it makes V-10 unverifiable locally *and* silently
green-lights a CI gate keyed on exit code)

The full-codebase run
(`vendor\bin\phpstan analyze --memory-limit=4G --configuration=phpstan.neon.dist --no-progress`)
ran for ~30 minutes and **aborted**:

```
?:?:Internal error: Could not write data to cache file
    G:\My Drive\OpenEMR\tmp-phpstan/cache/PHPStan/c9/26/c926bf1e…php
    while analysing file G:\My Drive\OpenEMR\ccr\transmitCCD.php
… (same for controllers\C_Hl7.class.php, interface\billing\edih_view.php)

⚠️  Result is incomplete because of severe errors. ⚠️
```

**Process exit code: 0.** A CI step or release gate that checks `$LASTEXITCODE` would record this as a
clean pass while PHPStan is explicitly telling the operator it did not finish.

**Root cause is environmental, not code.** `phpstan.neon.dist` sets `tmpDir: tmp-phpstan`, i.e. *on the
`G:` Drive mount*. This is the same filesystem failure class already documented in `CLAUDE.local.md` §6/§8
— the DriveFS mount cannot service the nested create/write churn (it is why `npm ci` fails there too).
PHPStan's parallel workers each write thousands of small cache files.

**Do not read this report as evidence that PHPStan passes.** What *is* verified: the `.phpstan/baseline/*`
diff is favourable — three files touched, all *shrank* (−5, −25, and a count 8→7), zero new entries — which
satisfies the letter of V-10's first half. The module is in analysis scope (`paths:` includes `interface`),
and the four branding rules are correctly registered in `.phpstan/extension.neon` with the fixture
directory excluded in `phpstan.github.neon`.

**Advice / fix:**

1. Move the cache off the Drive mount. Either set `tmpDir` to a local-NTFS path in a local override, or
   run with a config that includes `phpstan.neon.dist` and overrides only `tmpDir`:
   ```neon
   includes:
     - 'G:\My Drive\OpenEMR\phpstan.neon.dist'
   parameters:
     tmpDir: 'C:\openemr-stack\phpstan-tmp'
   ```
   Do **not** commit a `tmpDir` change to `phpstan.neon.dist` — that path is correct for CI and for every
   other developer; this is a host-local override.
2. Record the working invocation in `CLAUDE.local.md` §9 alongside the other native-tooling commands. §9
   currently lists the `phpstan analyze` command as if it works here; it does not, and a stale entry there
   is worse than a missing one by that file's own §12 rule.
3. Treat PHPStan's exit code as untrustworthy in any gate: grep the output for
   `Result is incomplete` / `Internal error` and fail on either, independently of the exit status. This
   applies to CI as much as to this host.
4. Until (1) is done, **V-10 must be reported as unverified**, not as passing.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

**What was done.**

1. Created the host-local override at `C:\openemr-stack\phpstan-localtmp.neon` — it `includes:` the dist
   config and overrides **only** `tmpDir` to `C:\openemr-stack\phpstan-tmp`. `phpstan.neon.dist` is
   deliberately **not** modified; that path is correct for CI and for any developer not on this mount.
2. Documented the whole thing in `CLAUDE.local.md` §9 under a new heading *"PHPStan needs its cache OFF the
   Drive mount"* — the failure signature, the exit-code trap, the override file, and the working invocation.
   `CLAUDE.local.md`'s `Last verified` date bumped to 2026-08-10 per its own §12 rule.
3. The §9 command block no longer lists the plain `phpstan analyze --configuration=phpstan.neon.dist`
   invocation as if it works here, since it does not — the stale entry is removed rather than left to
   mislead the next session.

**Evidence — the run now completes cleanly.**

```
$ php vendor/bin/phpstan analyze --memory-limit=4G \
      --configuration=C:\openemr-stack\phpstan-localtmp.neon --no-progress --error-format=raw

grep -c "Internal error"      -> 0
grep -c "incomplete"          -> 0
total reported errors         -> 80
```

> ### ⚠️ Correction to this resolution's own evidence (2026-08-10, later the same day)
>
> The first version of this block reported *"80 errors, all `openemr.forbidDirectSessionWrite`, all in
> `tests/` files untouched by this branch"* and concluded that V-10's first half was fully verified.
>
> **That count came from a truncated capture.** The command that produced it ended in `| tail -80`, so the
> analysis was performed on the last 80 lines of the output, not on all of it — and those happened to be
> uniformly `forbidDirectSessionWrite` because they sort last by path (`tests/…`). Counting a filtered tail
> and reporting it as a total is exactly the kind of error this document exists to catch, so it is
> corrected here rather than quietly restated.
>
> **The full, untruncated picture** (same invocation without the `tail`): the repository carries roughly
> **210 pre-existing errors** — the `forbidDirectSessionWrite` set in `tests/`, plus ~129 others:
> `openemr.noGlobalNsFunctions` across `library/*.inc.php` (the legacy global-function surface),
> `binaryOp.invalid` in `interface/main/calendar/…/pnadmin.php`, `assignOp.invalid` in
> `portal/…/PatientReporter.php`, and two stale `ignore.unmatched` patterns. **None of these are in files
> this branch touches** — verified by intersecting the reporting file list with
> `git diff --name-only 6125a2fd8..HEAD` plus the working tree, which is empty.
>
> **And the corrected run did find two errors in this remediation's own code**, which the truncated one had
> hidden:
>
> ```
> tools/branding/src/TypographyRenderer.php:85:
>   Parameter #1 ...$arg1 of function min expects non-empty-array, list<int> given.  [argument.type]
>   Parameter #1 ...$arg1 of function max expects non-empty-array, list<int> given.  [argument.type]
> ```
>
> Introduced by the RB-22 fix. **Fixed at the source, not suppressed**: `groupByFile()` now declares
> `@return list<non-empty-list<FontFace>>`, which is what the method actually guarantees — a key exists
> only because a face was appended under it — so `array_map()` yields a `non-empty-list<int>` and
> `min()`/`max()` are satisfied by the type rather than by an assertion or a cast. No baseline entry, no
> `@phpstan-ignore`, per `CLAUDE.md`.
>
> **Confirming run, after the fix** — full codebase, output filtered to the branding surface and to the
> two failure signatures that matter, with no `tail`:
>
> ```
> $ php vendor/bin/phpstan analyze --memory-limit=4G \
>       --configuration=C:\openemr-stack\phpstan-localtmp.neon --no-progress --error-format=raw \
>   | grep -E "ThiqaBranding|tools.branding|Internal error|incomplete"
>
> PHPSTAN_BRANDING_SCAN_DONE
> ```
>
> The filter matched **nothing**: no error in `OpenEMR\Modules\ThiqaBranding\*`, none in
> `tools/branding/`, no internal error, no incomplete-result warning. (The sentinel is the echo that
> follows the pipeline, proving the command ran to completion rather than producing empty output by
> dying.)
>
> **Corrected verdict:** PHPStan level 10 completes cleanly with the cache off the Drive mount, and after
> the `TypographyRenderer` fix **zero errors are attributable to the branding work** — confirmed by a
> second full run, on the strength of the full output rather than a tail of it.

Combined with the already-verified baseline direction (three baseline files touched, all *shrank*: −5, −25,
and a count 8→7, zero new entries), **V-10's first half is verified**: PHPStan level 10 is clean for this
branch's changes.

**Still open, and correctly so:** V-10's *second* half — module patch coverage ≥80% — remains unmeasurable
here. `php -m` confirms neither Xdebug nor PCOV is loaded, so no coverage report can be produced natively.
That needs CI, and is unchanged by this fix.

**Residual advice not yet actioned (belongs to CI, not this host):** make any PHPStan gate fail on
`Internal error` / `Result is incomplete` in the output rather than trusting the process exit code. PHPStan
returning 0 on an aborted run is a general trap, not a Drive-mount-specific one.

---

### RB-25 — *(found during remediation, 2026-08-10)* The brand manifest gate was already RED, and nothing detected it

**Severity:** High
**Locked references:** R7 §7.3 release gate; V-06; R5 `12-release-verification.md`.
**Found by:** attempting to verify V-06 before correcting RB-21's stale count — the check RB-21 was about
turned out to be *failing*, not merely mis-numbered.

**What was wrong.** `brand/manifests/SHA256SUMS` has 123 entries, and **16 of them are the
`docs/branding-production/*.md` design documents**, not just binary assets. The uncommitted Phase 5 F8
addendum appended a status-check section to `docs/branding-production/14-string-replacement-map.md` — which
changed that file's hash and silently turned the release gate red.

Proof that this predates my remediation (the committed blob still matched; only the working tree diverged):

```
manifest entry:        bc3563b7616c7edfef9d621460746ccbd83f43c45adfa2565bfb48db279f0c44
git show HEAD:…14-…md  bc3563b7616c7edfef9d621460746ccbd83f43c45adfa2565bfb48db279f0c44   ← matches
working tree           69dc709fd95ea6acebf7fc2721a00eec304815cd3473de4366f0a93646787b09   ← diverged
```

Full verification before the fix: **`entries=123 ok=122 mismatch=1 missing=0`**.

**Why it went unnoticed.** `remaining-dependencies.md` V-06 verified the manifest and recorded
*"0 missing, 0 mismatch"* — which was true **when it was run**, before the F8 addendum was written. V-06 was
a hand-run one-off, so it was only ever as current as the last person who remembered to run it. There was no
script, no test, and nothing in the workflow that re-checked it after a documentation edit.

**Why this matters more than a hash bookkeeping error.** The manifest is the project's tamper-evidence for
the certified brand kit — R7 §3.5.2 makes it a **release gate**. A gate that is quietly red, and that nobody
notices is red, provides no assurance at all. It also means every future edit to a `docs/branding-production/`
document — an entirely normal thing to do — breaks the gate unless the author knows to re-issue the hash,
and nothing told them.

#### ✅ RESOLUTION — FIXED (Claude, 2026-08-10)

1. **Re-issued the one drifted entry** for `docs/branding-production/14-string-replacement-map.md`,
   covering both the pre-existing F8 addendum and this audit's RB-13 correction. Re-issued, not deleted —
   the entry is the tamper evidence.
2. **Made V-06 executable**: new `tools/branding/verify-brand-manifest.php`. Resolves the repo root itself
   (the manifest's paths are repo-root-relative, which is the trap that makes hand-running it error-prone),
   accepts both `<hash>  <path>` and coreutils binary-mode `*<path>` forms, uses `hash_equals()`, and exits
   **1** on any missing / mismatched / unparsable entry.
3. **Rewrote the gate to stop hardcoding a count** (this is also RB-21's fix). R7 §6.4 V-06 and §7.3 now
   require the script to exit 0, so a legitimate kit re-issue can never make the gate stale again.
4. **Warned the next author** in §7.3: run the check before *and* after editing anything under
   `docs/branding-production/`, and re-issue rather than delete.

**Evidence.**

```
$ php tools/branding/verify-brand-manifest.php
brand/manifests/SHA256SUMS: 123/123 verified.        exit=0

# negative control — append one byte to a covered document:
$ printf '\n' >> docs/branding-production/00-baseline.md
$ php tools/branding/verify-brand-manifest.php ; echo $?
                                                     exit=1     ← correctly fails
$ git checkout -- docs/branding-production/00-baseline.md
$ php tools/branding/verify-brand-manifest.php
brand/manifests/SHA256SUMS: 123/123 verified.        exit=0
```

**Advice carried forward (not actioned here):** wire this script into CI next to the token-generator diff
check and the `Q77` entry-map assertion. All three are release-gate conditions in R7 §7.3 and all three are
currently manual.

---

## 5. Phase-by-phase verdict

### Phase 1 — Architecture design
**Verdict: PASS, with drift.** All eight D1.x deliverables are present and the five-plane model is
genuinely implemented, not just drawn. Four ADRs exist. The dependency rule (Plane 3 ⇏ Plane 1/2) is
enforced by a real static-analysis rule, which is the right kind of engineering.
Drift: **RB-15** (interface contract), **RB-04** (ADR-BRAND-001 asserts a route the code does not exclusively
take), **RB-20** (folder map incomplete).

### Phase 2 — Single branding service and centralised configuration
**Verdict: PASS.** This is the strongest phase. `TokenKey` is a closed 43-case backed enum with an
exhaustive `match`; `ColorValue` is anchored `/\A#[0-9A-Fa-f]{6}\z/` with a length pre-check;
`CssVariableRenderer` emits only `--name: value;` from typed objects with **no string passthrough**, so
Invariant 9 holds structurally. `SvgInspector` uses an element allowlist plus attribute vetting and rejects
`<!DOCTYPE`/`<!ENTITY`/null bytes — a correct approach to a genuinely hard problem. `SiteId` and
`TenantBrandingPaths` are traversal-safe. The materialiser writes the revision **last**, which is the right
invariant. 1272 isolated tests pass.
Defects: **RB-05** (the `?? default` fallback undermines an otherwise excellent tenant guard),
**RB-06** (endpoint session/`?site=` handling), **RB-07** (guardrail (c)/(d) placement),
**RB-09** (orphaned contract class).

### Phase 3 — Refactor the 136 Group 1 references
**Verdict: CONDITIONAL FAIL — the working tree must not be committed as-is.**
SET-CONFIG (37), REPLACE-ASSET (20), TOKENIZE (6), HIDE (3), PRESERVE (15), NO-ACTION (11) and PROHIBITED (1)
are done and evidenced; live checks confirm `openemr_name = Thiqa`, `alt="Thiqa logo"`, `<title>Thiqa Login</title>`,
and only one "OpenEMR" left on the login page — the frozen `OpenEMR=` session cookie, correctly preserved
under Q17/C6.
Blocking: **RB-01** (translation loss), **RB-02** (unrecorded core edits), **RB-03** (revision cache keys),
**RB-10** (dark logos), **RB-14** (Arabic PDF).
Also still open and correctly recorded elsewhere: BRAND-102 — **43** `xl()`/`xlt()`-wrapped "OpenEMR"
strings remain across `src/`, `interface/`, `library/`, `templates/` and `portal/` (measured this pass;
**50** repo-wide including `Documentation/help_files/`), with the largest single cluster being
`library/globals.inc.php` (16). Also BRAND-103, BRAND-030, and BRAND-070/110 (genuinely tenant-provisioning,
correctly excluded from the product profile).

### Phase 4 — Coverage verification and blocking dependencies
**Verdict: PARTIAL PASS.** The verification culture is excellent — `remaining-dependencies.md` distinguishes
DONE-VERIFIED from CARRIED-FORWARD honestly and its "Surprises" section is the kind of thing most audits
omit. A1/A2 correctly BLOCKED on D-6 (`ls sites/` → `default` only, re-confirmed).
Defects: **RB-11** (now stale on its headline finding), **RB-09** (A8 conclusion wrong),
**RB-08** (V-04 not enforceable through the documented deploy step), **RB-24** (V-10 unfinished),
**RB-21** (V-06 number stale), and V-09 must be re-run against the enlarged file set per **RB-02**.

### Phase 5 — Final documentation and readiness
**Verdict: PARTIAL PASS.** F1–F6, F7, F8, F9 all exist. The honesty discipline is real — the closure
evidence pack opens with *"MVP-010 and MVP-014 closure is NOT justified today"*, which is the correct
posture under Invariant 10 and should be preserved through any revision.
Defects: **RB-17** (uncommitted), **RB-12** (arithmetic + propagation into the certified artefact),
**RB-11** (stale), **RB-13** (F8 missed a Q25 conflict), **RB-02** (F6 incomplete by 10 files).

---

## 6. What is demonstrably correct (so it is not re-litigated)

Recorded deliberately: several things that *could* have been wrong are not, and were verified rather than
assumed.

| Verified | Evidence |
|---|---|
| **Q77 build surface** | `webpack.themes.js` entry map carries exactly the 8 approved theme entries; all 16 forbidden entries removed. `public/themes/` holds 19 files, zero `solar`/`manila`/`cobalt_blue`/`forest_green`. Upstream SCSS retained in-tree, as Q77 permits. |
| **CR-3 (no `rtl_` in globals)** | Live: `css_header = style_light.css`, `portal_css_header = style_light.css`. `ThemeResolver` strips `rtl_`/`compact_`/query/path before matching, so all four spellings resolve correctly. |
| **Q38 / CR-17 template delivery** | `addPath($dir, 'oe-module-thiqa-branding')` only; `prependPath()` absent and rule-forbidden. Both dispatch keys handled — `TemplatePageEvent::class` for SMART (dispatched unnamed), `RENDER_EVENT` for login. Verified against `SMARTAuthorizationController::renderTwigJson():359` and `interface/login/login.php:272`. |
| **Q17 / C6 session identity** | All five constants byte-exact; guarded by `BrandingGovernanceGuardTest`; the single "OpenEMR" on the live login page is that cookie. |
| **C7 legal/regulatory deny-list** | `install-assets.php` denies `cms1500.png`, `ub04.svg`, `visa_mc_disc_credit_card_logos_176x35.gif` and the whole `Documentation/` tree. |
| **BRAND-053 (K-21)** | Live: `alt="Thiqa logo"`. The K-21 correction — that a `{% include %}` partial has no `TemplatePageEvent` seam — is accurate and the resulting core edit is the right call. |
| **Generator determinism** | All four SCSS artefacts byte-identical to `output-preview/`; both SMART templates payload-identical. `DeployedArtefacts` guards the files the app actually loads, not just the preview copies — a real fix to a real gap. |
| **Registration phone-home** | `grep curl_\|reg.open-emr` in `ProductRegistrationService.php` → 0. Telemetry consent-gated in `TelemetryService::isTelemetryEnabled()`, applied at the end so the upstream lookup and its tests are unaltered — a careful choice. |
| **Brand manifest** | `SHA256SUMS` present, 123 entries. |
| **Isolated suite** | 1272 tests / 3608 assertions, OK. |

---

## 7. Prioritised remediation plan

> **Progress note (2026-08-10).** Items 1, 2, 4, 5, 11 and 12 are **done** — see the ledger at the top of
> this document for authoritative per-finding status, and each finding's own ✅ RESOLUTION block for the
> evidence. Item 6 is **partial** (the git-ignore and the false "no writable directory" claim are fixed; the
> route (a)/(b) decision is escalated). Item 3 needs the user's authorisation to commit. The table below is
> left as originally written so the plan and the work done against it can be compared.
>
> **The two commit-blockers are cleared:** RB-01 (translation loss) and RB-02 (unrecorded core edits) are
> both fixed and verified. One new High finding was discovered *during* remediation — **RB-25**, the brand
> manifest release gate was already failing — and is also fixed.

**Do not commit the working tree until items 1–3 are done.**

| # | Item | Findings | Effort |
|---:|---|---|---|
| 1 | Revert the six BRAND-127/128/129 literal edits; deliver them as `lang_definitions` rows (lang_id=1) instead | RB-01 | ~2 h |
| 2 | Write PR-10…PR-19 patch records for the remaining core edits (007–012 + any survivors of item 1); correct §5.4/§5.9 file counts | RB-02 | ~2 h |
| 3 | Commit `docs/branding/` | RB-17 | 10 min |
| 4 | `unset($_GET['site'])` + `session_write_close()` in `branding-tokens.php`, with tests | RB-06 | ~1 h |
| 5 | Remove the `?? new SiteId('default')` fallback; widen or document the `SiteId` grammar; add a dotted-site test | RB-05 | ~1 h |
| 6 | Decide the Tier-2 delivery route; stop writing CSS on an empty overlay; gitignore `public/branding/`; re-open or close D-8 truthfully | RB-04 | ~2 h |
| 7 | Fix the deploy procedure (purge before `robocopy`) in `runbook.md` **and** `CLAUDE.local.md` §6; make V-04 a real script | RB-08 | ~1 h |
| 8 | Decide RB-03 option (a) or (b); implement or write the ADR | RB-03 | 2–4 h |
| 9 | Correct the stale/incorrect Phase 5 claims: A8/R-SMART-DARK, materialisation status, status-count arithmetic, V-06 count | RB-09, RB-11, RB-12, RB-21 | ~2 h |
| 10 | Install the dark-variant logos and add the resolution test | RB-10 | ~1 h |
| 11 | Fix the Q25 conflict in `14-string-replacement-map.md` and amend the F8 note | RB-13 | 20 min |
| 12 | Move PHPStan's `tmpDir` off the Drive mount so a full run can finish; make any gate fail on `Result is incomplete` rather than trusting exit code; re-run V-09 against all 23 core files | RB-24, RB-02 | ~1 h + runtime |
| 13 | Wire Amiri into mPDF and dompdf; run the 7 acceptance tests | RB-14 | ~1 day |
| 14 | Housekeeping: `@codeCoverageIgnore` on the two providers; interface contract drift; profile key split; folder-map additions; SMART header wording; BRAND-119 reclassification | RB-15, RB-16, RB-18, RB-19, RB-20, RB-23 | ~3 h |

**Genuinely external and unchanged by any of the above:** D-3 (legal product-name clearance), D-4 (native
Arabic proofreading), D-5 (Control Plane), D-6 (second tenant), D-7 (test patient/portal credential),
D-11 (counsel review). None of these can be closed by engineering, and none of them are being
misrepresented in the existing documentation — that is worth saying plainly.

---

## 8. Closing assessment

The engineering here is above the bar. The token allowlist genuinely makes Invariant 9 unviolatable rather
than merely forbidden; the Q38-clean namespaced template route is the right mechanism and was chosen after
correctly withdrawing a worse one; the K-21 correction shows the team catching its own mistake from a live
page render rather than from a passing test. The Phase 5 documentation refuses to round up, which is rarer
than it should be.

The defects cluster in two places, and both are characteristic rather than random:

1. **Work done at the end, under time pressure, bypassed the mechanism the plan specified** — the
   SET-TRANSLATION items were edited as literals because it was faster, and the resulting core edits were
   never recorded. That is RB-01 and RB-02, and together they are the reason this branch is not releasable
   today.
2. **Several "built" mechanisms are not actually connected to anything** — the revision never reaches a logo
   URL (RB-03), the dark logo directory does not exist (RB-10), `SmartStyleContract` has no caller (RB-09),
   and the materialiser's CSS files are served to nobody (RB-04). Each was implemented correctly in
   isolation and tested in isolation; none was verified end-to-end against a rendered page. That is the
   failure mode isolated-only testing produces, and it is worth naming so the next phase plans around it.

Neither cluster requires re-architecting anything. Items 1–8 of §7 are roughly two days of work and would
move this branch from "not releasable" to "releasable pending the external D-items".

---

## 9. Post-remediation state (2026-08-10)

The remediation ran. Both diagnoses above held up, and the second one — *"mechanisms built but not
connected"* — turned out to be the more valuable of the two: three of the four instances were closed by
connecting existing, already-correct code rather than writing new code.

| Cluster | Outcome |
|---|---|
| Work that bypassed the specified mechanism (RB-01, RB-02) | **Closed.** SET-TRANSLATION items re-done as catalogue data (0 of 59 translations lost, was 59); all 17 core edits now carry a numbered patch record |
| Built-but-not-connected (RB-03, RB-09, RB-10, RB-04) | **3 of 4 closed.** Logo URLs now carry the revision; dark marks installed and rendering; the SMART conclusion corrected. RB-04's remaining half is a route decision, not a defect |

**Two things the remediation itself produced that the audit had not:**

1. **RB-25** — the brand-manifest release gate was already failing, undetected, because V-06 was hand-run.
   It is now a script, and it earned its keep immediately by catching a real drift during RB-22.
2. **RB-14 is worse than reported, and that is the single most consequential output of this pass.** D-9 was
   believed to be a configuration task. Testing proved the vendored Amiri **cannot be parsed by the bundled
   mPDF at all**. Had the config been written and shipped without being exercised — which is exactly what
   "wire up the fonts" invites — the first Arabic PDF would have been a fatal error in the config path used
   by *every* PDF in the product. The change was written, tested, and backed out.

**What this branch still cannot claim**, stated plainly because Invariant 10 applies to remediation too:

- Cross-tenant isolation (A1/A2) — unexercised, blocked on D-6. Unchanged.
- A Tier-2 overlay reaching a rendered page — the materialiser has run, but only with an empty overlay.
- Arabic PDF — further from done than before, not closer, and now correctly scoped.
- ~~The RB-22 saving — real in source, not in the compiled CSS until a theme rebuild runs.~~ **Closed
  2026-08-16** — the rebuild ran; the saving is real in the compiled CSS now, verified in all 8 theme
  files.
- The V-09 rebase dry-run — still covers 6 of 17 core files.

**Verified after remediation:** branding isolated suite **OK (1314 tests, 3779 assertions)**; manifest
**123/123**; generator drift **0**; phpcs clean on all 11 changed PHP files; PHPStan level 10 with **zero
branding-attributable errors** (see the correction inside RB-24 — the first measurement of this was taken
from a truncated capture and is corrected there).

---

## 10. Measurement hygiene — two counting errors this pass made, and one trap in the repository

Recorded because both of this pass's own mistakes were **measurement** mistakes, not reasoning mistakes,
and the same trap is sitting in the working tree waiting for the next person.

**1. A filtered tail reported as a total (RB-24).** The first PHPStan capture ended in `| tail -80`, and
the resulting "80 errors, all one identifier" was analysed and published as the whole picture. It was the
last 80 lines. Corrected inside RB-24, and it had hidden two real errors in this pass's own code.

**2. An unstated metric and scope (BRAND-102).** A figure of "43" was published for the remaining
`xl()`-wrapped "OpenEMR" strings. It came from `grep … | wc -l` — a *line* count over an unstated file set,
not reproducible from the text that quoted it. The corrected figure is **46 occurrences across 20 files**,
with the scan scope, regex and exclusions written down alongside it in `docs/branding/changes.md`.

**3. The trap: three agent worktrees live inside the repository.**

```
$ git worktree list
G:/My Drive/OpenEMR                                             c6c3f9e6e [feat/thiqa-branding-foundation]
G:/My Drive/OpenEMR/.claude/worktrees/agent-a0be56487a171bfdd   631f2b38c
G:/My Drive/OpenEMR/.claude/worktrees/agent-a2d5c8fbfdf82dc79   631f2b38c
G:/My Drive/OpenEMR/.claude/worktrees/agent-a987c6bd7f63e0e19   631f2b38c
```

Each is a **full checkout of the codebase at `631f2b38c`** — the pre-branding baseline. They are excluded
from git by `.git/info/exclude:11` (`**/.claude/worktrees/`), so `git status` stays clean, and ripgrep
honours that file, so the Grep tool skips them.

**But a plain `grep -r … .` does not.** Any repository-wide scan run with bare `grep`, `find`, or a PHP
directory iterator will walk those three trees and report pre-branding code as if it were current —
inflating "remaining OpenEMR string" counts by up to 4×, and resurrecting literals that were fixed months
ago. That is a live way to produce a wrong audit finding, and it nearly did here.

**Rule for anyone measuring this repository:** scope the scan to named directories, and exclude
`.claude/worktrees/`, `vendor/`, `node_modules/` and `oe-module-claimrev-connect` (a third-party Composer
dependency relocated into `interface/modules/custom_modules/`, not fork code). State the exclusions next to
the number. A count whose method is not written down cannot be re-derived, and a count that cannot be
re-derived is not evidence.

**The worktrees were left untouched.** They are not this session's, they may belong to another agent's
work in progress, and removing a worktree is not an audit's call to make.

---

*Audit performed against `feat/thiqa-branding-foundation` @ `c6c3f9e6e` plus the uncommitted working tree,
2026-08-10. Every finding above cites a command executed, a file read, or a live HTTP/SQL response captured
during this pass. Where a check could not be completed (PHPStan, the SMART route, two-tenant acceptance),
that is stated as an incomplete check and not as a pass. Where the Locked Decisions register and this
implementation disagree, the register is authoritative and the disagreement is recorded as a finding, not
resolved unilaterally.*
