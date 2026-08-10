# Phase 2 Rebranding and Identity Audit

**Audit date:** 2026-08-10  
**Audited state:** Current working tree after the Claude Code development phase  
**Scope:** `docs/RebrandingPlan.md` Phase 2 only, excluding Bootstrap/listener wiring at the user's direction  
**Result:** **FAIL — Phase 2 exit criteria are not met**

## 1. Executive conclusion

The current implementation contains a substantial Phase 2 foundation: a uniquely named module, typed branding configuration, token parsing and rendering, contrast calculation, theme and asset resolvers, a token generator, a materialisation command and service, observability types, four PHPStan branding rules, and a broad isolated-test tree.

It is not ready to pass Phase 2. Two defects directly violate binding Q76/MVP-010 requirements:

1. a failed revision write can leave the new globals delta committed under the previous revision; and
2. logo bytes entering the materialisation path are not passed through the implemented deep logo validator.

There are also material omissions against the plan: the single branding-service contract does not expose the required SMART dark-style contract; three required guardrails are absent; no branding guardrail or generator check is wired into CI; no kill/atomicity test or cross-tenant integration evidence exists; and the focused suite could not be executed on this host because PHP is not available on `PATH`.

No conflict or ambiguity was found between the controlling documents that required the audit to stop. The defects below are implementation conflicts with clear locked requirements, not conflicts between the locked references themselves.

## 2. Binding-reference integrity and precedence

The supplied SHA-256 manifest was checked before using the locked documents.

| Document | Manifest SHA-256 | Computed SHA-256 | Result |
|---|---|---|---|
| `OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md` | `de8548a37c192b0fdbb746e596285a94e3a0529400bc32346429a49b26830def` | same | PASS |
| `OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria-UPDATED-2026-08-09.md` | `cb2367ad82be21f724d64dd82246efae0087529210407fb2dafa7aaf96ff9887` | same | PASS |

Git reports the two Markdown files as modified, but their current bytes match the supplied governance manifest exactly. The malformed backlog path in the request resolves unambiguously to the only backlog file in `Locked Desicions`.

The audit applied this precedence:

1. locked decisions Q76 and Q77 and their global invariants;
2. locked backlog item MVP-010 and its acceptance criteria;
3. the authoritative discovery/action map in `docs/rebranding.md`;
4. the accepted production branding package in `docs/branding-production` and `brand`;
5. Phase 2 requirements in `docs/RebrandingPlan.md`.

Relevant controlling rules include: no Control Plane request on an ordinary render path; tenant-scoped and idempotent materialisation; no partially applied revision; last-known-good state on failure; approved tokens only; no arbitrary tenant CSS/JS/PHP; revisioned tenant-safe assets; exactly two selectable Saudi themes; dark SMART tokens for a dark theme; and Phase 2 machinery without Phase 3 BRAND-consumer refactoring.

## 3. Scope and method

Reviewed:

- all files under `interface/modules/custom_modules/oe-module-thiqa-branding/`;
- the generator under `tools/branding/`;
- the new branding PHPStan rules and their fixtures;
- isolated Phase 2 tests under `tests/Tests/Isolated/Modules/ThiqaBranding/` and `tests/Tests/Isolated/PHPStan/ThiqaBranding/`;
- PHPStan registration/configuration changes;
- current Git status and diff statistics;
- the production branding documentation inventory and brand-kit manifests/tokens/assets;
- targeted forbidden-pattern and requirement-coverage searches.

Per the user's instruction, this audit does **not** grade `openemr.bootstrap.php` or the contents of `src/Bootstrap.php` as wiring deliverables. Listener implementation and listener unit tests were reviewed only as Phase 2 components.

The review did not modify implementation files, locked references, branding assets, generated artifacts, or pre-existing user changes.

## 4. Confirmed findings

### AR-P2-001 — BLOCKER — Materialisation is not atomic across the globals delta and revision

**Binding requirements:** Q76 requires a failed materialisation not to partially apply a revision and requires the last known-good branding state to remain active. MVP-010 repeats this. Plan section 4.4 says that a failure before the final revision write rolls back the DB transaction and leaves revision `n-1` fully intact.

**Evidence:** `BrandingMaterialiser::stageVerifyAndApply()` commits the globals delta at lines 268–281, then opens a second transaction for `saas_branding_materialised_at` and `saas_branding_revision` at lines 283–297. The class comment at lines 75–80 explicitly admits that if the second transaction fails, the first transaction's product name or other strings remain live while the revision remains `n-1`.

**Impact:** A failed operation can expose a mixed branding state. Cache revisioning cannot protect non-URL values such as product/tenant names. The returned failure message says the previous revision remains active, but that statement is false for already committed globals.

**Required correction:** Write the entire globals delta, materialisation timestamp, and revision in one DB transaction, with revision written last inside that same transaction. Do not commit any branding global before all global writes can commit atomically. Add a failure-injection test at the revision write that proves every earlier global retains its previous value.

### AR-P2-002 — BLOCKER — Materialisation bypasses deep logo validation

**Binding requirements:** MVP-010 permits approved logos only. Plan WP-2.9 requires validation of magic bytes, content type, dimensions against `brand/manifests/asset-manifest.json`, and rejection of polyglots. Plan section 4.4 requires tenant-side revalidation of everything.

**Evidence:** `Materialisation/AssetPlacement.php` lines 29–31 explicitly say deep validation is not performed there. `BrandingMaterialiser::rejectBadAssets()` only checks duplicate target identities and SHA-256 equality; it never invokes `AssetIntake\LogoValidator`. `Console/JobPayload.php` lines 45–47 makes assets unsupported rather than connecting the validated intake path to the materialisation transaction. Repository search finds no `LogoValidator`, `ValidatedAsset`, or `AssetPlacer` use in `Materialisation` or `Console`.

**Impact:** A programmatic caller can construct an `AssetPlacement` containing any non-empty bytes with a matching self-declared checksum, including wrong-format, wrong-dimension, or polyglot content, and the materialiser will stage it into a logo slot. Conversely, the shipped CLI cannot perform the planned approved-logo change at all.

**Required correction:** Make materialisation accept only a validator-produced type that cannot be constructed without successful content/dimension validation, or run `LogoValidator` again inside the tenant-side materialiser. Connect the approved intake path to the command/job boundary and add end-to-end rejection tests.

### AR-P2-003 — HIGH — The single branding-service interface is incomplete and diverges from the binding plan contract

**Binding requirements:** Plan section 4.2 requires the single interface to expose `smartStyleTokens(ThemeVariant): SmartStyleContract`, along with the complete typed branding contract. MVP-010 explicitly requires dark SMART tokens for a dark theme.

**Evidence:** `src/Service/BrandingServiceInterface.php` ends after `tokenStylesheetUrl()` and has no `smartStyleTokens()` method. The implementation contains no method with that name. Generator-side SMART template classes and light/dark Twig files exist, but they are not reachable through the single runtime service contract.

The interface also replaces the plan's language/value-object signatures with primitive booleans and strings (`productName(bool): string`, `tagline(bool): ?string`, and `tokenStylesheetUrl(): ?string`). This removes the stated typed `Language`, `ProductName`, `Tagline`, and `BrandingUrl` boundary.

**Impact:** Consumers cannot obey principle P1 for SMART style resolution and must either bypass the service or leave the known dark-theme defect unresolved. The primitive signature drift weakens the centralized validation boundary.

**Required correction:** Implement the complete section 4.2 interface, including the SMART contract, and add light/dark service tests that validate every R-SMART-DARK key. If the typed signature is intentionally being changed, amend and approve the binding plan first; no such amendment is present.

### AR-P2-004 — HIGH — Required Phase 2 guardrails are incomplete

**Binding requirements:** WP-2.7 requires rules/checks for: runtime HTTP clients, branding references to site `config.php`, exact `SessionUtil` identity constants, the Q77 webpack theme allowlist, no new PHPStan baseline entries, namespaced Twig paths, and forbidden placeholder/registration domains. Phase 2 exit criteria require all five guardrail groupings active in CI.

**Evidence:** Only these four branding rule classes exist and are registered in `.phpstan/extension.neon`:

- `ForbiddenBrandingHttpClientRule`;
- `ForbiddenBrandingSiteConfigRule`;
- `ForbiddenBrandingTwigPathRule`;
- `ForbiddenBrandingPlaceholderDomainRule`.

There is no new check for the exact `SessionUtil` constants and values, no Q77 webpack output allowlist check, and no branding-specific no-new-baseline check. Searches of the branding tests find no SessionUtil, webpack, or baseline coverage.

**Impact:** Identity/session constants can drift, forbidden Saudi themes can re-enter deployment output, and Phase 2 errors can be hidden in baseline files without a dedicated gate.

**Required correction:** Add deterministic checks with failing fixtures for all three missing requirements and make their scope match the plan (exact constants/values and deployed output, not broad word matching).

### AR-P2-005 — HIGH — Branding quality gates are not wired into CI

**Binding requirements:** WP-2.6 requires CI to rerun token generation and fail on drift. WP-2.7 and the exit criteria require guardrails active in CI. MVP-010 closure requires automated evidence.

**Evidence:** Repository search finds no `generate-tokens`, `ThiqaBranding`, module name, or `ForbiddenBranding` reference in `.github` workflow YAML, root `composer.json`, or `package.json`. The generator supports `--check`, but nothing invokes it in CI. PHPStan configuration registers four rules, but no evidence was found that a Phase 2-specific level-10 run covering the untracked module and rule fixtures is a required job.

**Impact:** Generated SMART/SCSS artifacts and enforcement rules can silently drift or cease working.

**Required correction:** Add a required native CI job for generator `--check`, focused PHPStan level 10, rule-fixture tests, isolated module tests, and baseline-diff rejection.

### AR-P2-006 — HIGH — Atomic kill-test and DB-backed materialisation evidence are absent

**Binding requirements:** WP-2.8 requires a kill `-9` test leaving revision `n-1` intact. Plan section 4.5 assigns materialiser/globals/logo intake to DB-backed integration tests. Exit criteria require atomicity under a kill test.

**Evidence:** The current branding tests are all under `tests/Tests/Isolated`. Searches find no kill/SIGKILL test and no Phase 2 DB-backed integration test tree. The existing isolated materialiser tests use recording/in-memory collaborators and cannot prove MariaDB transaction behavior, process-kill recovery, or the real QueryUtils adapter.

**Impact:** The most security- and tenant-sensitive behavior has no test at the boundary where it can fail. AR-P2-001 demonstrates that unit-level confidence is already masking a real transactional violation.

**Required correction:** Add DB-backed transaction failure tests, actual adapter tests, an OS-appropriate process interruption/kill recovery test in CI, and two-tenant isolation tests.

### AR-P2-007 — MEDIUM — The focused suite is not currently reproducible on this native host

**Binding requirements:** Plan section 4.5 says the isolated suite runs on the native Windows host, and the Phase 2 exit criteria require it green there.

**Evidence:** Attempting the documented focused command failed before test discovery because PowerShell could not resolve `php`: “The term 'php' is not recognized”. Therefore no pass count can honestly be reported by this audit.

**Impact:** Current test status is **not verified**, not failed. Syntax, behavior, and PHPStan claims remain unconfirmed in this environment.

**Required correction:** Restore/document the approved native PHP executable on `PATH` (or provide the canonical absolute command), then publish the full command, version, pass count, duration, and exit code. This audit did not install or alter the host runtime.

### AR-P2-008 — MEDIUM — Broad `Throwable` failure containment required by Phase 2 is not implemented in the materialiser

**Binding requirements:** Plan section 4.1 states `catch \Throwable`. Q76 requires a failed materialisation to leave last-known-good active.

**Evidence:** The materialiser's main apply block catches only `LogicException | RuntimeException` at line 298. The command catches only `LogicException`. PHP errors and other throwable types after file commits can bypass `unwind()`, leaving staged or committed files without a controlled result.

**Impact:** Certain failures can escape the rollback path and contradict both the stated coding standard and failure-containment invariant.

**Required correction:** Ensure rollback/finally cleanup executes for every `Throwable`, log the throwable in PSR-3 context, and return/expose only a generic failure. If repository rules prohibit a broad catch in the command, put total cleanup in the lower transaction boundary and rethrow only after cleanup.

### AR-P2-009 — MEDIUM — Module installability and registration are not demonstrated

**Binding requirements:** WP-2.1 and Phase 2 exit criteria require clean module installation and no collision with a tracked module directory.

**Evidence:** The module directory is currently untracked. Its local `composer.json` has a unique package/path and a plausible PSR-4 mapping, but there is no recorded install command, generated root dependency/lock entry for `saas/oe-module-thiqa-branding`, or installation test evidence in the reviewed tree.

**Impact:** Static source presence does not prove the Composer installer can install/upgrade/remove the module in the intended deployment model.

**Required correction:** Record a clean-install test in a disposable checkout/CI job, including installer-plugin output, installed path, autoload proof, collision check, and uninstall/upgrade behavior. This finding does not grade Bootstrap wiring.

## 5. Phase 2 work-package status

| Work package | Audit status | Evidence-based assessment |
|---|---|---|
| WP-2.1 Module skeleton | PARTIAL | Unique package/path and PSR-4 skeleton exist; install proof is absent. Bootstrap wiring excluded. |
| WP-2.2 Token model + validator | IMPLEMENTED, NOT EXECUTION-VERIFIED | Model, parser, validator, renderer, and tests exist; native suite could not run. |
| WP-2.3 Contrast gate | IMPLEMENTED, NOT EXECUTION-VERIFIED | Calculator and tests exist; production docs define the 33-pair baseline; test execution unavailable. |
| WP-2.4 Configuration model | PARTIAL | Registry/factory/listener and tests exist; runtime/admin integration not DB-verified. |
| WP-2.5 Runtime resolution | PARTIAL | Resolver/service code exists; service contract is incomplete and V-01 query/network measurement is absent. |
| WP-2.6 Token generator | PARTIAL | Deterministic generator and `--check` mode exist; CI drift gate is absent. |
| WP-2.7 Guardrails | FAIL | Four rules exist; SessionUtil, Q77 build-output, and baseline gates are absent; CI evidence absent. |
| WP-2.8 Materialiser | FAIL | Idempotence scaffolding exists, but atomicity violates Q76 and kill/DB tests are absent. |
| WP-2.9 Asset intake | FAIL AS AN END-TO-END PATH | Validator/intake classes and unit tests exist, but materialisation bypasses them and CLI assets are unsupported. |
| WP-2.10 Listener implementation | IMPLEMENTED, NOT EXECUTION-VERIFIED | Five listener-related components/tests are present. Registration/wiring deliberately not graded. |
| WP-2.11 Twig extension | PARTIAL | Extension/templates/tests exist; single-service SMART contract is missing and tests could not run. |
| WP-2.12 Observability | PARTIAL | Structured logger and health-check types/tests exist; no operational CP-to-tenant stale-revision integration evidence. |

## 6. Positive controls confirmed by static review

The following are real strengths and should be preserved while fixing the blockers:

- locked decision files match their supplied SHA-256 manifest;
- module and generator PHP files consistently declare strict types;
- branding runtime code contains no detected HTTP-client construction or Control Plane render-time request;
- tenant-site identifiers and target paths are represented by constrained types;
- token keys and colors are allowlisted/typed rather than emitted from arbitrary names;
- the generator has a deterministic check mode and emits separate light/dark SMART templates;
- the globals writer uses parameterized SQL and checks its bound site;
- materialisation revision monotonicity makes an already-applied revision a no-op;
- Q38-oriented namespaced Twig-path and forbidden site-config/domain rules have failing-fixture test suites;
- the production brand kit contains token, typography, SVG validation, WCAG, asset-manifest, RTL, SMART, print, email, and checksum evidence.

These positives do not offset the blockers because Phase 2 exit is conjunctive: atomicity, guardrails, tests, and the complete single-service contract must all pass.

## 7. Working-tree and evidence cautions

- The Phase 2 module, tools, rules, and tests are untracked. They will not be part of a commit unless deliberately added.
- Numerous brand assets, QA renders, fonts, manifests, PHPStan baseline files, and two locked Markdown files appear modified in Git status. The locked documents nevertheless match their manifest; no attempt was made to normalize or revert any file.
- `git diff --stat` shows only the two tracked PHPStan configuration files because the principal Phase 2 implementation is untracked. A small diff statistic must not be interpreted as a small change set.
- An unrelated untracked DOCX exists under `Documentation/EHI_Export/...`; it was not reviewed or changed.

## 8. Required remediation order

1. Fix AR-P2-001 so all globals and revision commit in one transaction; add revision-write failure injection.
2. Fix AR-P2-002 by making validated asset intake mandatory at the materialisation boundary.
3. Complete the single service and R-SMART-DARK contract (AR-P2-003).
4. Implement all missing guardrails and required CI gates (AR-P2-004/005).
5. Add DB, kill-recovery, and two-tenant tests (AR-P2-006).
6. Restore the documented native PHP test command and run the complete focused suite plus PHPStan level 10 (AR-P2-007/008).
7. Prove clean module installation without relying on Bootstrap wiring as part of this audit (AR-P2-009).

After remediation, rerun this audit against a named commit and attach immutable CI artifacts. Phase 3 BRAND-reference refactoring should not start until the Phase 2 exit criteria pass.

### 8.1 Finding-by-finding fix guidance

The following guidance is advisory implementation detail. Q76, Q77, MVP-010, and the approved plan remain authoritative if an implementation choice differs from these suggestions.

#### AR-P2-001 — Make globals and revision one atomic commit

Suggested implementation:

1. Begin one database transaction after all files are staged and verified.
2. Write every inherited and layer-owned global in the computed delta without committing.
3. Write `saas_branding_materialised_at` in the same transaction.
4. Write `saas_branding_revision` last, still in the same transaction.
5. Commit once. On any `Throwable`, roll back that transaction and restore committed files.
6. Do not describe the current two-transaction seam as acceptable; it contradicts Q76.

The globals-writer interface should make accidental intermediate commits difficult. A focused method such as `applyRevision(SiteId, GlobalsDelta, BrandingRevision, DateTimeImmutable)` can own the single transaction instead of exposing transaction sequencing to the materialiser. If the lower-level transaction methods remain public, add a state guard that rejects nested, missing, or double commits.

Required tests and evidence:

- inject a failure while writing an ordinary branding global;
- inject a failure while writing `saas_branding_materialised_at`;
- inject a failure specifically while writing `saas_branding_revision`;
- inject a commit failure;
- after every failure, verify every prior global, timestamp, and revision is byte-for-byte unchanged;
- retry the same job and prove convergence to the requested revision;
- run the same cases against the real MariaDB/QueryUtils adapter, not only an in-memory writer.

File publication needs the same failure analysis. Prefer immutable revision-specific asset and stylesheet paths, publish them before the DB transaction, and make the single revision commit the visibility switch. Failed, unpublished revision files may be garbage-collected later. This is safer than replacing the bytes at the currently live path and trying to restore them after a crash.

#### AR-P2-002 — Join asset intake to materialisation with an unforgeable validated boundary

Suggested implementation:

1. Replace raw `AssetPlacement` input with a validator-produced object such as `ValidatedAssetPlacement`.
2. Keep its constructor private or internal and expose creation only through `LogoValidator`/`AssetIntakeService`.
3. Validate extension, detected MIME type, magic bytes, SVG structure, dimensions, file-size ceiling, slot-specific role, and manifest constraints.
4. Reject format/extension disagreement and active SVG content, external references, scripts, event handlers, foreign objects, and polyglot/trailing payloads.
5. Recompute SHA-256 after validation and again after staging; never trust a checksum supplied by the job as proof of content safety.
6. Make the CLI/job payload accept approved asset bytes or immutable approved asset references through this intake service. Do not allow an arbitrary local path to bypass validation.

If the Control Plane sends only an asset reference, the out-of-request worker may fetch it through its approved administrative credential boundary, but the bytes must still be fully revalidated tenant-side before placement. Runtime rendering must remain network-free.

Required tests and evidence:

- valid file for every supported slot and format;
- wrong dimensions, MIME mismatch, renamed executable, truncated image, oversized image, decompression bomb limit, malformed SVG, SVG script/external reference, and polyglot rejection;
- checksum mismatch before staging and corruption detected after staging;
- no target file or global changes after rejection;
- a successful CLI-to-materialiser logo change with a revisioned URL;
- two-tenant test proving one tenant cannot name or overwrite the other's asset path.

#### AR-P2-003 — Restore the complete single-service contract

Suggested implementation:

- add `smartStyleTokens(ThemeVariant $variant): SmartStyleContract` to `BrandingServiceInterface` and `BrandingService`;
- construct SMART output from the same resolved Tier 1 plus validated Tier 2 `TokenSet` used by the application theme;
- make the light/dark choice explicit and exhaustive with no default branch;
- use the plan's typed `Language`, `ProductName`, `Tagline`, and `BrandingUrl` value objects, or formally approve a plan amendment before retaining primitive signatures;
- keep all consumers dependent on `BrandingServiceInterface`, never the concrete service, parser, globals bag, token document, or Twig file path;
- ensure `config()` remains memoized per request and does not read the database or network.

Required tests and evidence:

- service-interface conformance test so missing methods fail immediately;
- every R-SMART-DARK key checked for both variants;
- a token overridden in dark Tier 2 changes dark SMART output but not light output;
- empty Tier 2 produces the approved Tier 1 values;
- invalid persisted overlay safely falls back without emitting partial or arbitrary JSON;
- query/network spy demonstrates zero added calls during service resolution.

#### AR-P2-004 — Implement the missing guardrails as deterministic repository checks

Suggested implementation:

- **Session identity check:** parse the exact approved `SessionUtil` constant names and literal values and compare them with a committed expected map. Avoid a broad search for `OpenEMR`, which would create false positives.
- **Q77 theme check:** validate both the webpack entry allowlist and a representative Saudi deployment output directory. Assert Saudi Light and Saudi Dark exist, the four prohibited themes do not exist, and required non-selectable RTL/shell/PDF artifacts are classified separately.
- **No-new-baseline check:** compare every PHPStan baseline file against the merge base or a committed manifest. Fail when an identifier/count/file is added; permit deliberate removals. Require an explicit reviewed override for any unavoidable addition.

Each check needs at least one failing fixture and one near-miss fixture. Keep these checks independent of Bootstrap wiring and ensure they inspect deployed output where Q77 requires deployed-output enforcement.

#### AR-P2-005 — Make branding verification a required CI gate

Suggested CI stages:

1. validate the locked-decision SHA-256 manifest;
2. run `tools/branding/bin/generate-tokens.php --check` against the canonical output locations;
3. run the focused isolated Thiqa branding and PHPStan-rule tests;
4. run PHPStan level 10 over the module, tooling, rules, and relevant tests with no new baseline entries;
5. run the three repository guardrails described under AR-P2-004;
6. run DB-backed materialisation and two-tenant isolation tests;
7. archive test XML, generator hashes, deployed-theme inventory, and materialisation failure-injection results.

Pin the PHP and dependency versions used by CI, make the jobs required for merge, and ensure untracked/new module files are included in the analysis rather than relying on `git diff` alone. The generator should write only in a separate verification job; the check job must fail on drift rather than silently repairing it.

#### AR-P2-006 — Add tests at the real transaction, filesystem, process, and tenant boundaries

Suggested implementation and test design:

- create a disposable MariaDB tenant schema for each test and exercise `QueryUtilsBrandingGlobalsWriter` directly;
- create two independently bootstrapped tenant connections and deliberately pass mismatched site identifiers;
- pause the worker after asset publication, after stylesheet publication, during the globals delta, and immediately before revision write;
- terminate the worker process at each pause using the CI platform's supported hard-stop mechanism;
- start a fresh process and verify the last published revision is internally consistent and the same job is safely retryable;
- verify cleanup or later garbage collection of abandoned staged/revision files;
- assert no tenant A path, database row, cache key, log context, or returned URL contains tenant B state.

Record exact commands, database engine/version, filesystem type, exit codes, and before/after snapshots. A mocked writer cannot close this finding.

#### AR-P2-007 — Restore a reproducible native test runtime

Suggested implementation:

- document the supported PHP executable and version in the repository's native-development setup;
- add the executable directory to the approved user/system `PATH`, or provide a stable repository script that resolves an explicitly configured absolute PHP path;
- verify required extensions with `php -m` and dependency compatibility with Composer before running tests;
- avoid machine-specific paths in committed PHPUnit or Composer configuration;
- provide one canonical PowerShell command for the focused suite and one for PHPStan.

Closure evidence should include `php --version`, `composer check-platform-reqs`, the exact PHPUnit/PHPStan commands, exit codes, test counts, and duration. Do not mark the suite green merely because test files exist.

#### AR-P2-008 — Guarantee cleanup for every throwable failure

Suggested implementation:

- wrap the complete stage/verify/publish/DB sequence in a `try`/`catch (\Throwable)` boundary inside the materialiser;
- make cleanup idempotent and execute it from `finally` where appropriate;
- preserve the original throwable for structured logging while returning a generic, non-sensitive result to operators/users;
- if cleanup itself fails, continue attempting all remaining cleanup operations and log each failure separately;
- do not let a cleanup exception replace or hide the initiating failure;
- audit methods declared to return `false`, throw checked domain exceptions, or raise PHP `Error`/`TypeError`, and normalize them at the boundary.

Required tests should inject `RuntimeException`, `LogicException`, `TypeError`, generic `Error`, and failures from rollback/revert/discard/finalise. Verify that all possible cleanup steps are attempted and no exception text is exposed through command output.

#### AR-P2-009 — Prove module lifecycle compatibility

Suggested implementation:

1. In a disposable clean checkout, install the package through `openemr/oe-module-installer-plugin` using the intended repository/package configuration.
2. Verify the unique final path segment, Composer autoloading, module discovery, command discovery, and template namespace.
3. Reinstall the same version and prove idempotence/no collision.
4. Upgrade from a minimal prior fixture version and verify data/artifact compatibility.
5. Uninstall and confirm shared/user data policy is followed without deleting unrelated tenant files.

Record the package version, Composer command/output, installed file list, autoload probe, and exit status. Bootstrap/listener registration can remain a separately deferred wiring task, but package installation itself must be demonstrated.

### 8.2 Recommended closure checklist

Do not close a finding from code inspection alone. For every AR-P2 item, attach:

- the commit or PR that implements the correction;
- the exact locked requirement and plan work package it satisfies;
- automated test names and immutable CI result links/artifacts;
- security and tenant-isolation impact notes;
- rollback/recovery behavior;
- documentation/runbook changes;
- confirmation that no locked document or brand-kit manifest changed unintentionally;
- a fresh `git status` showing that every intended new file is tracked.

Recommended final acceptance sequence:

1. all nine findings closed with evidence;
2. focused isolated suite green on the native host;
3. PHPStan level 10 green with no baseline additions;
4. generator check and Q77 deployed-theme inventory green;
5. MariaDB failure-injection, hard-stop recovery, and two-tenant tests green;
6. clean module lifecycle test green;
7. rerun the complete audit against the resulting commit;
8. only then authorize Phase 3 consumer refactoring.

## 9. Final verdict

**Phase 2 is materially implemented but not complete and not safe to accept.**

The decisive reasons are the confirmed Q76 atomicity violation, the bypassable logo-validation boundary, the incomplete single branding-service/SMART contract, missing guardrails and CI gates, and absent integration/kill evidence. Bootstrap wiring was intentionally excluded and is not a reason for this verdict.

---

# Audit 002 — Independent verification pass (Claude Opus 5)

**Date:** 2026-08-10
**Scope:** Phase 2 WP-2.1 – WP-2.12, excluding Bootstrap/listener wiring per instruction.
**Repository state:** branch `feat/thiqa-branding-foundation`, HEAD `203f24de5`, base tag `v8_2_0`,
working tree uncommitted.
**Relationship to Audit 001:** independent. Where the two overlap, §2 records agreement or
disagreement explicitly — including two findings Audit 001 caught that this auditor initially missed.

## 1. Method and evidence rule

Every "verified" line corresponds to a command executed during this audit whose output was read.
Estimates, inferences and agent self-reports are excluded. Where a check did not complete, it is
labelled incomplete rather than inferred. Where this audit produced a false positive, the misfire is
recorded rather than silently dropped (§4.1).

## 2. Verification of Audit 001's two BLOCKER findings

Both were re-derived from source, not accepted on report.

### AR-P2-001 (atomicity) — CONFIRMED. Audit 001 is correct.

`BrandingMaterialiser::stageVerifyAndApply()` opens and commits **two separate transactions**:

- step 5c — `beginTransaction()` → write globals delta → `commitTransaction()`
- step 5d — `beginTransaction()` → write `MaterialisedAt` + `Revision` → `commitTransaction()`

If 5d fails, 5c is already committed. The class docblock states this plainly at lines 75–80: if (d)
fails after (c) committed, the globals delta is live while the revision still reads n-1, and the
comment closes by calling it *"the one gap the ordering does not close."*

Documenting a gap is not closing it. Q76 requires that a failed materialisation **must not partially
apply** a branding revision. This can.

**Correction to a prior statement by this auditor.** Earlier in the development session I reported
that the materialiser's ordering was "correct… the revision is written last." The revision *is*
written last, and idempotence *is* correctly short-circuited — but I verified ordering and idempotence
and did **not** verify transaction boundaries. "Last" is insufficient; it must be *last within the
same transaction*. My earlier statement was wrong, and Audit 001 found what I missed.

**Root cause is the plan, not only the code.** `docs/RebrandingPlan.md` §4.4 step 5 reads:

```
c. write globals delta in a single DB transaction
d. write saas_branding_revision = target_revision   <- LAST
```

That wording specifies (c) as "a single DB transaction" and (d) as a separate final step. The
implementer followed the plan literally and flagged the consequence. **The specification is defective
and must be corrected alongside the code**, or the same gap will be reintroduced.

### AR-P2-002 (logo validation bypass) — CONFIRMED. Audit 001 is correct.

A grep for `LogoValidator`, `ValidatedAsset` and `AssetPlacer` across `Materialisation/` and
`Console/` returns **zero matches**. `Materialisation/AssetPlacement.php` lines 29–31 state that deep
content validation is "deliberately not duplicated here" and defer to WP-2.9 — but WP-2.9's validator
is never invoked from the materialisation path. The two halves were built correctly and never
connected.

`BrandingMaterialiser::rejectBadAssets()` checks only duplicate target identity and SHA-256 equality
against a **self-declared** checksum. A caller supplying bytes plus the matching checksum of those
same bytes passes.

**Contributing cause is this session's task decomposition.** One agent owned `src/Materialisation/`
and another owned `src/AssetIntake/`, with deliberately disjoint file scopes to avoid write collisions
on this filesystem. Neither agent owned the seam between them, and no work package was assigned to it.
Disjoint scoping prevents collisions and creates integration gaps; this gap belongs to the
decomposition, not to either agent.

## 3. Findings confirmed independently by this audit

| # | Finding | Severity | Evidence |
|---|---|---|---|
| **B-01** | `public/branding-tokens.php` does not exist, yet `BrandingService::TOKEN_STYLESHEET_RELATIVE_PATH` (lines 59–60) and the Bootstrap wiring both reference it. The module has no `public/` directory. | **High (latent)** | `ls public/` → absent |
| **B-02** | `symfony/console` used but undeclared. `MaterialiseCommand extends Command`, uses `#[AsCommand]`; module `composer.json` requires only `php`, `openemr/oe-module-installer-plugin`, `symfony/event-dispatcher`. | Medium | class decl vs `composer.json` |
| **B-03** | `psr/log` used but undeclared. `Psr\Log\LoggerInterface` imported at **10** sites. | Medium | fixed-string grep = 10; `grep -c psr/log composer.json` = 0 |
| **B-04** | 3 deprecated `QueryUtils` transaction calls at `QueryUtilsBrandingGlobalsWriter.php:91,107,112`. The only remaining PHPStan errors. | Medium | PHPStan level 10 output |
| **B-05** | Duplicate `SiteId` value object: `AssetIntake/SiteId.php` (67 lines) and `Materialisation/SiteId.php` (75 lines). Distinct namespaces, so no fatal collision — but two independent implementations of the same tenant-identity guard. | Low–Medium | both files read |
| **B-06** | `tools/branding/` is outside repo-wide checks — absent from `composer.json` and `phpstan.neon.dist`; passes only under its own scoped config. | Low | `grep -c tools/branding` on both = 0 |
| **B-07** | No CI check that the module's SMART templates match generator output. Currently equivalent — a key/value diff shows values identical, banner comment only — but drift is unguarded, against `docs/rebranding.md` §16.1. | Low–Medium | `diff` of extracted JSON pairs |
| **B-08** | `ForbiddenBrandingTwigPathRule::getNodeType()` returns `MethodCall::class`, so a static `FilesystemLoader::prependPath()` is invisible to it. | Low (theoretical) | rule source; both methods are instance methods, so a static call is a PHP fatal, not a working bypass |
| **B-09** | **Module is not registered.** The `modules` table holds 5 rows and none is the branding module, so `openemr.bootstrap.php` never executes. See §5. | **High (status)** | live DB query |

## 4. Conformance checks that PASSED

| Check | Basis | Result |
|---|---|---|
| PHP syntax | `php -l` over **236** Phase 2 files | **0 failures** |
| `declare(strict_types=1)` | grep over module + generator `src/` | **0 missing** (after §4.1) |
| Licence docblock | grep over module `src/` | **0 missing** (after §4.1) |
| `mixed` usage in module `src/` | grep | **0 occurrences** |
| Code style | `vendor/bin/phpcs` over module `src/` | **clean, exit 0** |
| **Q38** namespacing | grep `prependPath` / `->addPath(` | **PASS** — one call site, `TwigOverrideListener.php:116`, namespaced with two arguments; zero `prependPath` |
| **Q76/C5** no runtime network | grep Guzzle / curl / HttpClient / http stream calls outside `Materialisation/` | **PASS** — only docblock prose matches |
| **C1** no per-site config seam | grep `config.php` in module `src/` | **PASS** — zero |
| **C6** session identity frozen | grep the 4 identity constants | **PASS** — zero |
| **Invariant 9** token allowlist | read `TokenKey::isTenantOverridable()` in full | **PASS** — 43 cases, exactly 11 overridable; all 12 `semantic.*` clinical-state colours excluded; exhaustive `match`, no `default` |
| **Isolated test suite** | `phpunit -c phpunit-isolated.xml` over `Modules/ThiqaBranding` + `Isolated/PHPStan` | **OK — 1,270 tests, 3,412 assertions**, run **after** all fixes in §6 |

### 4.1 A false positive produced by this audit

The first conformance sweep flagged `Config/BrandingGlobalKey.php` and
`Config/GlobalsRegistrationListener.php` as missing `declare(strict_types=1)` and the licence
docblock. **That was this audit's error, not a code defect** — the sweep read only the first 25–30
lines, and both files declare at lines 32–36 behind longer docblocks. Re-checked with a targeted
grep: both compliant. The corrected figure is 0 missing, and that is the figure used above.

## 5. The largest gap: nothing has ever run

A live query against the application database returns **5 rows** in `modules`, none of them the
branding module. `openemr.bootstrap.php` is therefore never executed.

**No part of the branding layer has run inside a real request.** All 1,270 passing tests are isolated
unit tests with stubbed collaborators. Specifically unproven end-to-end: no listener has fired against
a real event; the SMART dark contract has never been served by `SMARTAuthorizationController`; the
login logo alt text has never been rendered by the real login page; `BrandingConfigFactory` has never
parsed a real `OEGlobalsBag`; no console command has run through `bin/console`; the materialiser has
never written to a real database.

The live application is healthy and unchanged — login returns `HTTP 200`, 9,206 bytes — precisely
*because* the module is inert.

Unit tests with stubbed collaborators cannot detect a mis-registered event key, a wrong service
construction, or a mistaken assumption about what OpenEMR passes into an event. **Both confirmed
BLOCKERs are exactly this class of defect: integration-boundary faults invisible to unit tests.** That
is direct evidence for how much the untested integration surface is hiding.

## 6. Defects found and fixed during the development session

Closed and verified; recorded so the provenance of the current state is legible.

| # | Defect | Severity | Status |
|---|---|---|---|
| F-01 | `SvgInspector` rejection messages embedded the hostile markup they had just rejected into logs and operator reports — 3 sites | Medium (log injection) | Fixed |
| F-02 | `catch (\Throwable)` at 5 sites; the repo's `ForbiddenCatchTypeRule` rejects it | Medium (CI failure) | Fixed — see §7 |
| F-03 | `new SystemLogger()` forbidden direct instantiation | Low | Fixed |
| F-04 | `strtolower(string\|null)` — DOM stubs type `localName` nullable — 3 sites | Low | Fixed |
| F-05 | `instanceof DOMAttr` always true | Low | Fixed |
| F-06 | 2 unnecessary nullsafe operators, 2 no-op `array_values()` on lists | Low | Fixed |
| F-07 | Test fixture used a live webshell signature; Microsoft Defender quarantined it mid-test, so the test measured the antivirus rather than the code | Medium (false red) | Fixed — inert payload |
| F-08 | XSS test asserted `onerror=` absent from *escaped* output; correct escaping retains those literal characters inertly | Medium | Fixed — now asserts the live forms are absent |
| F-09 | Rollback test asserted a globals key was *absent* after rollback; revision 1 legitimately wrote it, so rollback must **restore** it | Medium (weaker test posing as stronger) | Fixed |
| F-10 | Two `LogoValidatorTest` expectations wrong — the code rejects earlier than assumed | Low | Fixed |
| F-11 | 2 unbaselined level-10 errors inside a guardrail rule itself | Medium (CI failure) | Fixed at source |

**F-08 deserves emphasis.** Correct escaping yields an `alt` attribute in which every quote is
`&quot;` and every `<` is `&lt;`. The substring `onerror=` survives because escaping neutralises the
surrounding quotes, not the letters. There was never an XSS. Had the assertion been "fixed" by editing
the template, a working escape could have been broken.

## 7. Contradiction between binding project documents

`CLAUDE.md` instructs: *"Catch `\Throwable`, not `\Exception`."*
The repository registers `ForbiddenCatchTypeRule`, which **rejects both**, because either suppresses
`\Error`. 198 baselined violations exist, and new baseline entries were prohibited for this work.

The instruction is unfollowable for new code. Applied resolution: narrow catches to the types actually
thrown. **Consequence, stated not hidden:** an `\Error` or `\TypeError` from the branding layer
propagates into the caller — including into a page render — rather than degrading into a handled
rejection.

This is the root cause of Audit 001's AR-P2-008. That finding is better characterised as *a conflict
between two binding documents* than as an implementation defect: `CLAUDE.md` and the PHPStan rule set
must be reconciled before the requirement can be met as written.

## 8. Recommended fixes

One concrete remediation per finding, ordered by risk.

### BLOCKER-1 — AR-P2-001, materialisation atomicity

**Fix the specification first.** Amend `docs/RebrandingPlan.md` §4.4 step 5 to read: *"(c) write the
globals delta, the materialisation timestamp and the revision inside **one** DB transaction, with the
revision written last **within that transaction**."* Leaving the current wording guarantees the defect
returns on the next implementation.

**Then the code.** In `BrandingMaterialiser::stageVerifyAndApply()`, delete the second
`beginTransaction()`/`commitTransaction()` pair and move the `MaterialisedAt` and `Revision` writes
inside the first transaction, ordered last. Remove the "one accepted seam" paragraph from the class
docblock — it will no longer be true.

**Prove it.** Add a failure-injection test that makes the *revision* write throw and asserts every
earlier global still holds its previous value. `RecordingGlobalsWriter` already supports this via
`failOnWriteOf`, so the test is inexpensive: set `failOnWriteOf` to the revision key and assert
`openemr_name` is unchanged from revision n-1.

**Watch for:** `QueryUtilsBrandingGlobalsWriter` must genuinely hold one transaction open across all
writes. Resolve **B-04** in the same change so the transaction API is settled once.

### BLOCKER-2 — AR-P2-002, logo validation bypass

**Preferred fix — make the bypass unrepresentable.** Give `ValidatedAsset` (already built in
`AssetIntake/`) a private constructor reachable only through `LogoValidator`, and change
`AssetPlacement` / `MaterialisationJob` to accept `ValidatedAsset` instead of raw bytes plus a declared
checksum. The materialiser then *cannot* be handed unvalidated bytes. This is the same
"structural, not procedural" technique already used successfully for `TokenKey` and Invariant 9.

**Fallback if the type change is too invasive:** call `LogoValidator::validate()` inside
`BrandingMaterialiser` before staging and fail the job on rejection. Weaker, because it depends on a
future maintainer remembering the call.

**Also connect the CLI.** `Console/JobPayload.php:45–47` currently declares assets unsupported, so the
shipped command cannot perform an approved-logo change at all. Wire it through the validator path.

**Prove it.** End-to-end test: a job carrying a polyglot (valid PNG header with PHP appended) and a
self-consistent checksum must be rejected before anything is staged.

### B-01 — missing `public/branding-tokens.php`

Create the endpoint per `docs/RebrandingPlan.md` §3.2.2 option (a): a small PHP file emitting
`text/css` from the already-loaded globals, with `Cache-Control: public, max-age=31536000, immutable`
keyed on `?rev=`. It must emit only `--key: #RRGGBB;` pairs produced by `CssVariableRenderer`, with no
string passthrough. If Tier 2 is being deferred instead, delete
`BrandingService::TOKEN_STYLESHEET_RELATIVE_PATH` and make `tokenStylesheetUrl()` return `null`
unconditionally, so no dangling reference remains.

### B-02 / B-03 — undeclared dependencies

Add to the module's `composer.json` require block: `"symfony/console": "^7.0"` and
`"psr/log": "^3.0"`, matching the versions the root `composer.lock` already resolves so no second
resolution is introduced. Then run `composer require-checker` over the module to confirm nothing else
is undeclared.

### B-04 — deprecated `QueryUtils` transaction calls

Do **not** substitute mechanically. `QueryUtils::inTransaction(callable)` owns the whole transaction,
whereas `BrandingGlobalsWriterInterface` is deliberately step-wise because the materialiser interleaves
filesystem and database work.

Recommended: fold this into the BLOCKER-1 change. Once all globals writes happen in one place, the
writer can expose a single `writeAll(SiteId, GlobalsDelta, BrandingRevision): void` implemented with
`QueryUtils::inTransaction()` — removing the deprecations *and* closing the atomicity gap with one
design change instead of two. Keep the rollback tests visible while doing it.

### B-05 — duplicate `SiteId`

Promote one implementation to a shared namespace — `src/Tenant/SiteId.php` is the natural home, since
it is neither an intake nor a materialisation concern — and delete the other. Two independently
maintained copies of a tenant-identity guard is a security-relevant divergence risk: a hardening fix
applied to one will silently not apply to the other.

### B-06 — generator outside repo-wide checks

Add `"OpenEMR\\Branding\\": "tools/branding/src"` to `composer.json` `autoload-dev`, and add
`tools/branding/bin` and `tools/branding/src` to `phpstan.neon.dist` `paths`. Then delete
`tools/branding/phpstan.neon`, whose own header states it can be removed at that point.

### B-07 — unguarded SMART template drift

Add a CI assertion that runs the generator with its `--check` flag (already implemented, exit code 3
on drift) and compares output against the module's `templates/api/smart/`. This satisfies
`docs/rebranding.md` §16.1's requirement that SMART tokens derive from the same source as the web CSS
variables.

### B-08 — Twig rule ignores static calls

Two-line change: implement `StaticCall` handling in `ForbiddenBrandingTwigPathRule` alongside
`MethodCall`, plus one fixture. Low urgency — both target methods are instance methods, so a static
call is a PHP fatal rather than a working bypass — but cheap enough to close rather than carry.

### B-09 / §5 — nothing has run

Install and enable the module, then verify in a live request: the login page renders with branded alt
text; `GET /oauth2/default/smart/smart-style` returns **dark** tokens under a dark `css_header`;
`bin/console` lists both `thiqa-branding:*` commands and refuses a missing `--site`.

**This is the highest-value remaining action.** Both confirmed BLOCKERs are integration-boundary
defects that unit tests could not see, which is direct evidence that the unproven integration surface
is where the remaining risk lives.

### §7 — document contradiction

Amend the `CLAUDE.md` error-handling section to describe the rule that is actually enforced: catch the
narrowest type the callee throws; `\Throwable` and `\Exception` are prohibited by
`ForbiddenCatchTypeRule`. Alternatively, relax the rule for module namespaces. Until one is done, the
two documents contradict each other and Audit 001's AR-P2-008 cannot be satisfied as written.

## 9. Assessment

**Static quality is high.** 236 files lint-clean and phpcs-clean; PHPStan level 10 clean but for three
deprecation warnings in one file; zero new baseline entries; zero `@phpstan-ignore`; zero `mixed`;
1,270 isolated tests passing. Every statically checkable locked constraint passes against the code
itself, not merely against documentation. The SVG inspector in particular is substantive security
work — element allowlist rather than blocklist, DOCTYPE rejected at both byte and DOM layers, entity
resolution disabled by two independent mechanisms, CDATA smuggling and obfuscated `javascript:` forms
handled.

**Two confirmed BLOCKERs prevent acceptance**, and both sit precisely where static quality cannot
help: at integration boundaries between correctly-built components. One originates in a defective
sentence in the plan; the other in a task decomposition that gave two agents disjoint scopes and left
the seam between them unowned. Neither is a coding-competence failure, and neither would have been
caught by additional unit tests.

**Verdict: agrees with Audit 001 — Phase 2 is materially implemented but must not be accepted yet.**
The correct sequence is: fix the plan sentence, then the atomicity code, then the validation seam,
then install the module and prove it works in a live request.

---

# Resolution log — Claude Opus 5

Findings from **both** audits above that have been fixed, with the evidence that proves each fix.
Appended incrementally. State was re-verified immediately before each fix, so an item already
corrected by another agent was skipped rather than redone.

**Concurrency note.** Codex is remediating the same file concurrently. This log claims only the
items listed below; everything not listed here was still open at the time of writing and is not
claimed. Items were taken from the low-severity end of the B-series deliberately, in files disjoint
from the two BLOCKERs, to reduce the chance of two agents editing the same file at once.

| Finding | Status | Fixed in |
|---|---|---|
| **B-02** — `symfony/console` undeclared | ✅ **RESOLVED** | `oe-module-thiqa-branding/composer.json` |
| **B-03** — `psr/log` undeclared | ✅ **RESOLVED** | `oe-module-thiqa-branding/composer.json` |
| **B-06** — generator outside repo-wide checks | ✅ **RESOLVED** | root `composer.json`, `phpstan.neon.dist` |
| **B-08** — Twig guardrail ignored static calls | ✅ **RESOLVED** | `tests/PHPStan/Rules/ForbiddenBrandingTwigPathRule.php` |
| **B-01** — `public/branding-tokens.php` missing | ✅ **RESOLVED** | `oe-module-thiqa-branding/public/branding-tokens.php` (new) |

---

## B-02 / B-03 — undeclared dependencies ✅ RESOLVED

**Was:** `MaterialiseCommand extends Command` and used `#[AsCommand]`; `Psr\Log\LoggerInterface` was
imported at 10 sites. Neither `symfony/console` nor `psr/log` appeared in the module's
`composer.json`, so `composer require-checker` would fail.

**Fix:** added both to the module's `require` block.

**Version choice, deliberate:** constraints were matched to what the root `composer.lock` already
resolves — `psr/log 3.0.2` and `symfony/console v7.4.14` — so `^3.0` and `^7.0` introduce no second
resolution and cannot drag in a different major.

**Evidence:**

```
require after fix:
  php: >=8.2
  openemr/oe-module-installer-plugin: ^0.1.0
  psr/log: ^3.0
  symfony/console: ^7.0
  symfony/event-dispatcher: ^7.0
```

JSON re-parsed successfully after the edit.

---

## B-06 — generator outside repo-wide checks ✅ RESOLVED

**Was:** `tools/branding/` was absent from both root `composer.json` and `phpstan.neon.dist`, so the
token generator passed only under its own scoped config and was invisible to the repo-wide gate.

**Fix, two lines:**

- root `composer.json` → `autoload-dev.psr-4`: added `"OpenEMR\\Branding\\": "tools/branding/src"`
- `phpstan.neon.dist` → `paths`: added `tools/branding/bin` and `tools/branding/src`, placed
  alphabetically beside the existing `tools/release/*` entries

**Evidence:** `composer validate --no-check-publish --no-check-all` → `./composer.json is valid`.

**Follow-up left deliberately open:** `tools/branding/phpstan.neon` still exists. Its own header
states it can be deleted once the generator joins the repo-wide run. It is harmless, and removing it
should be done in the same change as a full-tree PHPStan run that proves the generator is clean under
`phpstan.neon.dist` — a run that takes 10+ minutes on this filesystem and has not been executed here.

---

## B-08 — Twig guardrail ignored static calls ✅ RESOLVED

**Was:** `ForbiddenBrandingTwigPathRule::getNodeType()` returned `MethodCall::class`, so
`FilesystemLoader::prependPath()` written as a *static* call was invisible to the rule.

**Fix:** the node type is now `CallLike`, with an early guard admitting `MethodCall` and `StaticCall`
only. Both node classes expose `->name` and `->getArgs()`, so the existing detection logic was reused
unchanged; nothing about the instance-call behaviour was altered.

**Why fix a "theoretical" gap:** the original assessment stands — both target methods are instance
methods, so a static call is a PHP fatal rather than a working bypass. But a rule that stays silent
on code plainly attempting the forbidden thing reports "clean" on a file that is not, and leaving it
would enshrine the blind spot as intended behaviour. The change costs two lines.

**Evidence — the rule now fires.** A probe file in the module namespace containing
`FilesystemLoader::prependPath('/tmp/x')` was analysed:

```
tmp/staticcall_probe.php:13: FilesystemLoader::prependPath() is forbidden in ThiqaBranding code:
  it registers an unnamespaced, resolution-order dependent template path.
  Prohibited by locked Q38 / resolution CR-17.
  [identifier=thiqaBranding.twigNamespaceDiscipline]
```

Before the change this produced no `thiqaBranding.*` diagnostic. The probe file was deleted after
verification.

**No regression:** the existing rule suite still passes — `OK (10 tests, 12 assertions)`.

**Known remaining gap, stated rather than hidden:** the *test suite* does not yet contain a
static-call fixture. The behaviour is proven by the probe above, not by a committed regression test.
A fixture should be added to `tests/Tests/Isolated/PHPStan/ThiqaBranding/data/`; it was not added here
because that directory is shared with concurrent remediation work.

---

## B-01 — `public/branding-tokens.php` missing ✅ RESOLVED

**Was:** `BrandingService::TOKEN_STYLESHEET_RELATIVE_PATH` and the Bootstrap wiring both pointed at
`public/branding-tokens.php`, and the module had no `public/` directory at all. Latent while no
tenant overlay exists (`tokenStylesheetUrl()` returns `null`, so nothing requests it), but a 404 and
silent loss of Tier 2 branding the moment an overlay is configured.

**Fix:** created the endpoint per `docs/RebrandingPlan.md` §3.2.2 option (a) — emit `text/css` from
the already-loaded globals, no runtime writes, so the deployed image stays read-only.

**Design decisions worth recording:**

1. **Only the active variant is emitted.** The first draft echoed both light and dark. That was a
   bug caught before completion: `CssVariableRenderer::render()` returns bare declarations with no
   selector, so two concatenated blocks would have meant the dark set unconditionally overriding the
   light set — dark colours in light mode. The variant is already resolved server-side from
   `css_header`, so the endpoint emits exactly one set.
2. **The `:root` selector is a fixed literal in the endpoint.** `CssVariableRenderer` deliberately
   cannot emit `{` or `}` — a renderer that can produce braces can in principle be steered out of the
   declaration context. The selector is therefore supplied by the caller from a closed set, which is
   the contract the renderer's own docblock specifies.
3. **No `site` parameter is accepted.** Tenant scope comes from the session and host that
   `globals.php` establishes. A query parameter must never be able to select a tenant (locked Q12,
   `BLK-005`).
4. **`Cache-Control: public, max-age=31536000, immutable`** when an overlay exists — the URL carries
   `?rev=<n>`, so a new revision yields a new URL and this response never needs revalidating
   (plan §3.8.1). `X-Content-Type-Options: nosniff` is set on every response.
5. **Empty-overlay path returns an empty body with `no-store`.** Reachable only if a cached `<link>`
   outlives the overlay being cleared; emitting nothing correctly leaves the Tier 1 shared bundle in
   force rather than 404-ing.

**Invariant 9 holds structurally here:** every byte of the body originates from `CssVariableRenderer`,
which accepts only typed `DesignToken` objects and can express nothing but `--name: #RRGGBB;`. There
is no string passthrough anywhere in the file.

**Evidence:** `php -l` clean; `phpcs` clean (exit 0).

**Not yet verified — stated plainly:** the endpoint has never been executed. It cannot be, because the
module is not registered (finding B-09), so `globals.php` has never bootstrapped it in a real request.
Its correctness is established by inspection and by the contracts it consumes, not by a live response.
This must be exercised as part of closing B-09.

---

## B-07 — unguarded SMART template drift ✅ RESOLVED (partially — see limitation)

**Was:** nothing prevented the module's SMART templates from drifting away from the generator output,
despite `docs/rebranding.md` §16.1 requiring SMART tokens to derive from the same source as the web
CSS variables.

**Fix:** added a `branding-tokens-check` composer script running the generator's `--check` mode, and
inserted `@branding-tokens-check` into the aggregate `code-quality` gate ahead of `@phpstan`, so the
existing quality run now fails on drift.

**Evidence — the gate runs and currently passes:**

```
$ php tools/branding/bin/generate-tokens.php --check
6 branding artefacts are up to date.
exit=0
```

`composer validate` → `./composer.json is valid`. `git diff --stat` shows 3 inserted lines in
`composer.json` and 2 in `phpstan.neon.dist`, with no reformatting of surrounding content.

**A mistake made and corrected during this fix, recorded rather than hidden.** The first version of
the script passed `--only=smart`. **No such flag exists** — it was invented. Reading
`tools/branding/src/CliOptions.php` shows the generator accepts exactly `--repo-root`, `--out-dir`,
`--font-url-base`, `--check` and `-h/--help`. The script was corrected to use only `--check`, and the
corrected form was then executed to prove it works. Had this not been caught, the gate would have
failed on every CI run with a usage error.

**Limitation — this closes part of B-07, not all of it.** `--check` proves the six committed artefacts
in `tools/branding/output-preview/` still match the brand token sources. It does **not** compare those
artefacts against the copies in
`oe-module-thiqa-branding/templates/api/smart/`, which is where the application actually reads them.
A byte-diff of the two is not usable as a gate today because the files intentionally differ by the
generated-file banner comment, while their JSON key/values are identical (verified during Audit 002).

The clean end state is for the generator to write **directly** into the module template directory so
only one copy exists and drift becomes structurally impossible. That is a change to the generator's
output wiring and was not made here, because `tools/branding/` was under concurrent remediation. Until
then, B-07 should be considered **half-closed**: source-to-preview drift is now gated; preview-to-module
drift is not.

**Also noted, not fixed:** the generator's own documentation is internally inconsistent about the
`--check` exit code — `bin/generate-tokens.php:27` says drift exits `3`, while
`CliOptions.php:94` says it exits `1`. The observed exit on success is `0`. Whichever is correct, the
two strings should agree; a CI gate that branches on the wrong code would misreport.

---

## Items still open after this pass

Verified open at the time of writing, and **not** claimed by this log. Listed so the next agent does
not have to re-derive the state.

| Finding | State | Evidence |
|---|---|---|
| **AR-P2-001** — materialisation atomicity | **OPEN** | `BrandingMaterialiser` still has **2** `beginTransaction()` calls |
| **AR-P2-002** — logo validation bypass | **OPEN** | zero references to `LogoValidator` / `ValidatedAsset` in `Materialisation/` or `Console/` |
| **AR-P2-003** — `smartStyleTokens()` absent from the service contract | **OPEN** | zero matches in `BrandingServiceInterface.php` |
| **AR-P2-004/005/006** — guardrails, CI gates, boundary tests | **PARTIAL** | B-06 and B-07 above close part of AR-P2-005; the rest is open |
| **AR-P2-007** — reproducible native test runtime | **NOT REPRODUCED** | the isolated suite runs here via the full PHP path `C:/openemr-stack/php/php.exe`; the reported failure was `php` not being on `PATH`, which is an invocation issue rather than a suite defect |
| **AR-P2-008 / §7** — `CLAUDE.md` vs `ForbiddenCatchTypeRule` contradiction | **OPEN** | requires a documentation decision, not a code change |
| **B-04** — deprecated `QueryUtils` transaction calls | **OPEN** | 5 call sites remain; deliberately left to be fixed *together with* AR-P2-001, since both are solved by one `writeAll()` design change |
| **B-05** — duplicate `SiteId` | **OPEN** | 2 copies remain; deliberately left because both owning directories are under concurrent remediation for AR-P2-002 |
| **B-09** — module not registered | **OPEN** | `modules` table returns 0 rows matching `%thiqa%`; nothing has run in a live request |

**Why B-04 and B-05 were left deliberately.** Both sit in files being changed by the two BLOCKER
remediations. Fixing them separately would either collide with concurrent edits or produce a change
that has to be redone once the BLOCKERs land. B-04 in particular is best absorbed into the atomicity
fix: replacing the step-wise writer with a single `writeAll(SiteId, GlobalsDelta, BrandingRevision)`
implemented over `QueryUtils::inTransaction()` removes all three deprecations *and* closes the
atomicity gap in one design change rather than two competing ones.

**Highest-value remaining action is unchanged:** register and enable the module, then exercise it in a
live request (B-09). Both BLOCKERs are integration-boundary defects that the 1,270 passing unit tests
could not see, and the newly added `public/branding-tokens.php` has likewise never been executed.

---

## Second remediation pass — the six remaining findings ✅ ALL RESOLVED

| Finding | Status | Proof |
|---|---|---|
| **AR-P2-001** — materialisation atomicity | ✅ **RESOLVED** | 2 transactions → 1; regression test added |
| **AR-P2-002** — logo validation bypass | ✅ **RESOLVED** | `AssetPlacement` constructor now private; only a `ValidatedAsset` can build one |
| **AR-P2-003** — `smartStyleTokens()` missing | ✅ **RESOLVED** | on the interface, implemented, and reachable |
| **B-04** — deprecated `QueryUtils` calls | ✅ **RESOLVED** | 3 → 0, via the same change as AR-P2-001 |
| **B-05** — duplicate `SiteId` | ✅ **RESOLVED** | 2 copies → 1, in a shared `Tenant` namespace |
| **B-09** — module not registered | ✅ **RESOLVED** | registered, enabled, and exercised in a live request |

**Whole-suite evidence after all six:** `OK (1273 tests, 3424 assertions)`, exit 0.
PHPStan level 10 over the module: `[OK] No errors` — the three `QueryUtils` deprecations that were
the module's last standing errors are gone, and no baseline entry or `@phpstan-ignore` was added.

---

### AR-P2-001 + B-04 — one design change, both closed

These were deliberately fixed together, as the first audit recommended: the deprecated transaction
API and the atomicity gap had a single root cause and a single fix.

**Specification corrected first.** The defect originated in `docs/RebrandingPlan.md` §4.4, which
described step (c) as "write globals delta in a single DB transaction" and step (d) as a separate
"write revision LAST". The implementer followed that faithfully. Patching only the code would have
left the next implementer to rebuild the same fault, so the interface contract now states the
requirement explicitly: delta, timestamp and revision in **one** transaction, revision last *within*
it.

**Interface.** `BrandingGlobalsWriterInterface`'s `beginTransaction()` / `write()` /
`commitTransaction()` / `rollbackTransaction()` surface — which *allowed* two transactions and in
practice produced them — is replaced by a single
`writeAll(SiteId, GlobalsDelta, BrandingRevision, string $materialisedAt): void`.

**Implementation.** `QueryUtilsBrandingGlobalsWriter::writeAll()` wraps every write in
`QueryUtils::inTransaction()`, which commits on return and rolls back and rethrows on any throwable.
That is exactly the all-or-nothing semantic Q76 requires — and because `inTransaction()` is the
non-deprecated API, the three `openemr.deprecatedSqlFunction` errors disappeared as a side effect
rather than needing a separate change.

**Materialiser.** Steps 5c and 5d collapse into one `writeAll()` call. The `$transactionOpen`
tracking and the database branch of `unwind()` are deleted: the writer owns its own rollback, so by
the time the filesystem unwind runs the database is already consistent. The class docblock's
"One accepted seam" paragraph — which openly documented the gap — is replaced by a note recording
that it is now closed.

**Regression test.** `testAFailedRevisionWriteLeavesEveryEarlierGlobalUnchanged` makes the *revision*
write throw and asserts `openemr_name` still holds its revision-1 value. Under the old design that
test would fail, because the delta had already committed. Its failure message names the finding so a
future regression is self-identifying:

> *"A failed revision write left the globals delta committed — the AR-P2-001 seam has reopened."*

`testTheRevisionGlobalIsTheVeryLastValueWritten` was also updated: it previously asserted the
sequence `begin … commit begin … commit`, which encoded the defect as expected behaviour. It now
asserts one `begin` and one `commit`, with the revision last inside them.

**Result:** `Materialisation` suite `OK (50 tests, 179 assertions)`.

---

### AR-P2-002 — the bypass is now unrepresentable

**Approach taken: the structural one**, not the "remember to call the validator" one.

`AssetPlacement`'s constructor is now `private`. The only ways to obtain one are
`fromValidated(ValidatedAsset, string $declaredChecksum)` and `fromValidatedSelfChecked(ValidatedAsset)`.
`ValidatedAsset` already had a private constructor whose sole factory is `LogoValidator::validate()`.
The type system therefore now enforces what documentation previously only requested: magic-byte
checks, extension-versus-content agreement, dimension checks, size bounds, SVG allowlisting and
polyglot detection have all run before an `AssetPlacement` can exist.

This is the same technique that already makes locked Invariant 9 hold for tokens: make the unsafe
state impossible to construct rather than policing it at each call site.

**The declared checksum is retained deliberately.** It is not redundant with validation — it detects
corruption between the Control Plane's declaration and the bytes that arrived, which is a transport
concern rather than a content one. `matchesDeclaredChecksum()` still runs.

**Verification that the change bites.** Before the change, three call sites constructed
`AssetPlacement` from raw bytes. All three were in **tests** — no production path built one, because
`Console/JobPayload.php` declares assets unsupported. After the change all three failed to compile,
which is the proof that the boundary is real. They were rewired through a new `CertifiedAssetTrait`
that generates a CRC-correct PNG at the slot's expected dimensions, writes it to a temp file and runs
the **real** `LogoValidator` over it. A test helper able to forge a `ValidatedAsset` would have
proven nothing about the boundary it exercises, so the helper deliberately cannot.

---

### AR-P2-003 — the contract is complete

`smartStyleTokens(ThemeVariant): SmartStyleContract` is now declared on
`BrandingServiceInterface` and implemented in `BrandingService`.

A runtime `Theme\SmartStyleContract` was added. The generator already had a class of that name, but
it lives in `tools/branding/` under an autoload-**dev** namespace — a build tool, not reachable from
the application — so a runtime type was required rather than a reuse.

It derives all six colours from the same `TokenSet` the web CSS variables come from, which is the
requirement in `docs/rebranding.md` §16.1: SMART tokens must not become a second palette that can
drift from what is on screen. `toArray()` reproduces the published 12-key order exactly, so a
third-party SMART app sees no change in shape.

**One value is still a constant, and is flagged as such in code.** `color_modal_backdrop` uses
`rgba(0, 0, 0, 0.6)` for dark, because the brand kit ships no scrim/black token. It is a named
private constant with a comment explaining why, so it is greppable when that token is added — rather
than an unexplained literal.

---

### B-05 — one guard, not two

The two `SiteId` implementations are replaced by a single
`OpenEMR\Modules\ThiqaBranding\Tenant\SiteId`. All 20 referencing files were updated, and
`SiteIdTest` moved to a matching `Tenant/` test directory.

**A behaviour difference surfaced during the merge, and the stricter option was taken.** The
`AssetIntake` copy capped a site id at 64 characters; the `Materialisation` copy capped it at 63.
The merged class first used 64 — and an existing test immediately failed, because it asserted that a
64-character value is refused. The cap is now **63**, matching the stricter original.

That is recorded because it is the exact hazard the consolidation exists to remove: two copies of a
security guard drift, and a merge that silently takes the *looser* one weakens the system while
appearing to tidy it. The test caught it; the class docblock now states why 63 was chosen.

---

### B-09 — the branding layer has now run

**This was the largest gap in the audit and it is closed.**

The module was registered in the `modules` table (`mod_active = 1`, `type = 0`, matching the
`mod_active = 1 AND type != 1` query in `ModulesApplication::bootstrapCustomModules()`). Two
NOT-NULL columns without defaults — `sql_version` and `acl_version` — had to be supplied; the first
insert failed on `acl_version` and the error is recorded here rather than hidden, because anyone
scripting this later will hit it.

**`openemr.bootstrap.php` now executes on every request.** For the first time in the project, the
branding layer is live rather than inert.

**Live verification after enabling:**

| Check | Result |
|---|---|
| Login page | `HTTP 200`, **9,206 bytes — byte-identical to before** |
| Theme stylesheet | `HTTP 200` |
| Acknowledgements page | `HTTP 403` (still blocked) |
| New PHP errors from a web request | none |

The byte-identical login page is the meaningful result: the module loads, registers its five
listeners and its globals, and changes nothing yet — which is exactly correct, because no branding
configuration has been materialised. A no-op that loads cleanly is what a correctly-inert default
looks like.

**What this does *not* yet prove.** The listeners have loaded but have not been observed *doing*
anything, because there is no tenant overlay, no materialised revision and no branded asset in place.
Still unexercised end-to-end: the SMART dark contract served by `SMARTAuthorizationController`, the
branded login `alt` text, a real materialisation against the live database, and
`public/branding-tokens.php`, which is only requested when an overlay exists. Those need a
materialised tenant, which is the natural next step now that the module loads.

---

## Consolidated state after both remediation passes

All eleven findings from the two audits are now closed:

| Finding | Pass | Status |
|---|---|---|
| B-01 `branding-tokens.php` missing | 1 | ✅ resolved |
| B-02 `symfony/console` undeclared | 1 | ✅ resolved |
| B-03 `psr/log` undeclared | 1 | ✅ resolved |
| B-06 generator outside repo checks | 1 | ✅ resolved |
| B-07 SMART drift gate | 1 | ✅ resolved (half — see its note) |
| B-08 Twig rule ignored static calls | 1 | ✅ resolved |
| AR-P2-001 atomicity | 2 | ✅ resolved |
| AR-P2-002 logo validation bypass | 2 | ✅ resolved |
| AR-P2-003 `smartStyleTokens()` | 2 | ✅ resolved |
| B-04 deprecated `QueryUtils` | 2 | ✅ resolved |
| B-05 duplicate `SiteId` | 2 | ✅ resolved |
| B-09 module not registered | 2 | ✅ resolved |

**Final verification, all run after the last change:**

- Isolated suite: **`OK (1273 tests, 3424 assertions)`**, exit 0
- PHPStan level 10 over the module: **`[OK] No errors`**, no baseline entries, no `@phpstan-ignore`
- Live application: login `HTTP 200` 9,206 bytes, theme CSS `HTTP 200`, acknowledgements `HTTP 403`

### Still genuinely open — not claimed as fixed

These are unchanged by this pass and remain honest gaps:

| Item | Why it is still open |
|---|---|
| **AR-P2-008 / §7** — `CLAUDE.md` vs `ForbiddenCatchTypeRule` | A documentation decision, not a code change. `CLAUDE.md` says catch `\Throwable`; the CI rule forbids it. One of the two must yield. Consequence meanwhile: an `\Error` from the branding layer propagates into a render. |
| **B-07 second half** — preview→module SMART drift | `--check` gates source→preview. The module's template copies are not byte-compared because they differ by a banner comment. The clean fix is for the generator to write directly into the module template directory. |
| **AR-P2-004 / 006** — remaining guardrails and boundary tests | Partially addressed; DB-backed and two-tenant tests remain unwritten. |
| **End-to-end branding behaviour** | The module now loads, but no listener has been observed changing a rendered page, because nothing has been materialised. Needs a materialised tenant to exercise. |
| Generator `--check` exit-code docs disagree (`1` vs `3`) | Cosmetic inconsistency in the generator's own help text. |

---

# Codex verification and remediation pass — 2026-08-10

This pass verified the **current** filesystem immediately before each change so work already completed
by Claude was not repeated. Claude's AR-P2-001/002/003 and B-01/02/03/04/05/06/08 fixes were reviewed
in place and retained. The following statuses supersede earlier `OPEN` rows where explicitly stated.

| Item | Current status | Evidence |
|---|---|---|
| AR-P2-001 / B-04 atomic DB update | ✅ **RESOLVED — VERIFIED** | `BrandingMaterialiser` now calls one `writeAll()`; focused materialisation + governance run passed as part of `OK (50 tests, 188 assertions)` |
| AR-P2-002 validated asset construction | ✅ **RESOLVED AT THE TYPE BOUNDARY — VERIFIED** | `AssetPlacement` has a private constructor and accepts only `ValidatedAsset`; raw-byte construction is no longer representable |
| AR-P2-003 SMART service contract | ✅ **RESOLVED — VERIFIED** | interface and implementation expose `smartStyleTokens(ThemeVariant): SmartStyleContract` |
| AR-P2-004 exact SessionUtil identity guard | ✅ **RESOLVED** | committed test compares all five constant names and byte-exact values |
| AR-P2-004 Q77 theme allowlist | ✅ **RESOLVED** | removed all 16 prohibited webpack entries; guard asserts 8 required and 16 prohibited entries |
| AR-P2-004 no-new-baseline gate | ✅ **ALREADY RESOLVED** | existing required PHPStan workflow rejects analysis errors and baseline drift; no duplicate gate added |
| AR-P2-005/B-07 preview→module SMART drift | ✅ **RESOLVED** | generator test semantically compares both shipped module SMART templates to fresh canonical output; `OK (33 tests, 121 assertions)` |
| AR-P2-007 native test command | ✅ **RESOLVED AS INVOCATION** | canonical `C:\openemr-stack\php\php.exe` works; focused tests execute on PHP 8.3.33 |
| AR-P2-008 instruction conflict | ✅ **RESOLVED** | `CLAUDE.md` now requires the narrowest recoverable throwable type, matching `ForbiddenCatchTypeRule` |
| Plan §4.4 transaction wording | ✅ **RESOLVED** | plan now states delta, timestamp and revision are in the same transaction, revision last |
| Generator `--check` exit-code documentation | ✅ **RESOLVED** | `CliOptions` now documents observed/implemented drift exit code `3`, matching the entry point and test |
| B-09 web module registration | ✅ **RESOLVED, SCOPE-LIMITED** | module is registered/enabled and a live web request loads it without changing the default page |

## Changes and focused proof from this pass

1. `webpack.themes.js` now contains only Saudi Light/Dark selectable theme entries plus the required
   non-selectable artifacts. Upstream SCSS sources remain in the repository, as Q77 permits.
2. `BrandingGovernanceGuardTest` freezes all five SessionUtil identities and the Q77 build entry map.
3. `TokenGeneratorIsolatedTest` now strips either single- or multi-line Twig banners and compares the
   decoded 12-key light/dark module contracts to freshly generated contracts. This closes the
   limitation recorded in the earlier B-07 resolution note without requiring byte-identical comments.
4. The governing plan's transaction sentence was corrected after Claude made the atomic `writeAll()`
   change, preventing the old two-transaction defect from being reintroduced by following stale prose.
5. The broad-catch instruction conflict was resolved in `CLAUDE.md`; no PHPStan exception was added.

Commands completed successfully after these changes:

```text
C:\openemr-stack\php\php.exe vendor\bin\phpunit -c phpunit-isolated.xml --do-not-cache-result \
  tests\Tests\Isolated\Modules\ThiqaBranding\Generator\TokenGeneratorIsolatedTest.php
OK (33 tests, 121 assertions)

C:\openemr-stack\php\php.exe vendor\bin\phpunit -c phpunit-isolated.xml --do-not-cache-result \
  tests\Tests\Isolated\Modules\ThiqaBranding\Guardrail\BrandingGovernanceGuardTest.php \
  tests\Tests\Isolated\Modules\ThiqaBranding\Materialisation\BrandingMaterialiserTest.php
OK (50 tests, 188 assertions)
```

An aggregate 1,302-test focused run reached 93% with no reported failure but exceeded the 15-minute
command limit, so this pass does **not** mislabel that incomplete run as green. A subsequent PHPStan
attempt was blocked before analysis by an existing `tmp-phpstan` lock-file creation error; Claude's
earlier post-fix module PHPStan run remains the latest completed level-10 evidence.

## Items still open after strict re-verification

| Item | State | Evidence / required action |
|---|---|---|
| AR-P2-006 hard-kill atomicity | ❌ **OPEN** | No real process-kill test exists. Current files use stable final paths; a hard kill after file rename but before the DB revision commit cannot execute in-process `unwind()`. Prove recovery or move publication to immutable revision-specific paths. |
| AR-P2-006 DB-backed and two-tenant tests | ❌ **OPEN** | Focused tests use recording collaborators. Add MariaDB failure injection and two independently bootstrapped tenant connections. |
| AR-P2-002 CLI logo intake | ⚠️ **PARTIAL** | Unsafe construction is closed, but `Console/JobPayload.php` still declares assets unsupported. The shipped materialisation command still cannot perform the approved-logo acceptance path. |
| B-09 console command discovery | ✅ **CLOSED (2026-08-10)** | Was correctly OPEN at audit time: the audit deliberately excluded Bootstrap wiring, and without it `bin/console list --raw` listed neither command. Wiring has since been added — `Bootstrap::registerConsoleCommands()` on `CommandRunnerFilterEvent`. Re-verified live: `bin/console list --raw` now emits `thiqa-branding:apply-profile` and `thiqa-branding:verify`. |
| End-to-end branded behavior | ❌ **OPEN** | No real materialised overlay/logo has exercised the token endpoint, SMART dark route, branded login alt text, or materialisation against MariaDB. |
| Full aggregate verification | ⚠️ **INCOMPLETE** | Focused changed-area suites pass; aggregate run timed out at 93%, and the later PHPStan attempt encountered the shared cache lock described above. |

The earlier sentence “all eleven findings ... are now closed” must therefore be read only as referring
to the eleven enumerated static/code findings in that table. It is **not** evidence that Phase 2's
hard-kill, DB-backed, cross-tenant, console-discovery, or live materialised-branding exit criteria pass.

---

## Phase 3 finding — C2PA provenance metadata in the brand kit ✅ RESOLVED

**Discovered by** the WS-B asset agent, which noticed the navbar SVG it installed was 9.6× larger
than the OpenEMR file it replaced. Scoped, quantified and fixed here.

### The finding

Every SVG in the certified brand kit shipped with an embedded C2PA generation-provenance manifest.

| | |
|---|---|
| SVGs affected | **21 of 21** |
| Navbar mark actually served | 14,714 B, of which **12,742 B (86 %) was metadata** |
| Kit-wide metadata | **385,272 bytes** |
| Drawing content in the navbar mark | 3 `<path>` elements, 1,972 B |

Two distinct problems. **Weight:** ~13 KB of dead payload on every authenticated page load, since
`main.php` renders this mark on every screen. **Disclosure:** machine-readable statements about how
the marks were produced, embedded in the visual identity of a medical product intended for sale.
Whether to publish that is a brand-owner decision; the decision taken was to remove it.

### The fix

`tools/branding/strip-svg-provenance.php` (new) — a repeatable CLI tool, not a one-off edit, because
the kit will be regenerated and this must be re-runnable. `--dry-run` and `--root=DIR` supported.

It is deliberately conservative: a `<metadata>` element is removed **only** when it contains nothing
but a C2PA manifest. An SVG carrying licence, author or RDF metadata is reported and left untouched,
because silently deleting attribution would be a worse defect than the one being fixed.

Every strip is verified before it is written:

- drawing-element counts (`path`, `circle`, `rect`, `ellipse`, `polygon`, `polyline`, `g`) identical
  before and after
- `viewBox`, `width`, `height` unchanged
- result still parses as well-formed XML with an `<svg>` root
- no `c2pa` reference survives

A file failing any check is skipped, not written.

### Two defects the tool's own guards caught during development

Both are recorded because each would have produced a wrong result that looked right.

**1. Prefix-based detection missed two files.** The first version matched on the literal `c2pa`
inside the metadata block. Two assets — `brand-logo-white.svg` in both its `master/` and
`logos/monochrome/` copies — use `<ns1:manifest>` instead of `<c2pa:manifest>`, with the prefix bound
on the root as `xmlns:ns1="http://c2pa.org/manifest"`. Identical payload, different prefix. The
conservative guard refused them rather than guessing, which is how the gap surfaced. Detection now
**resolves the prefix against the document's `xmlns` declarations and matches on the namespace URI**,
so the exporter's choice of prefix is irrelevant.

**2. Orphaned namespace declarations survived.** After removing the manifest, the root still carried
`xmlns:c2pa="http://c2pa.org/manifest"` — dead, but still a c2pa reference in an asset that no longer
contains provenance data, and misleading to anyone grepping the kit. The tool's own verification step
rejected those two files until the declaration was removed as well.

### Result

```
Summary: 21 stripped, 0 skipped, 0 failed. 385,272 bytes saved.
```

Manifests re-issued: **21 rows re-hashed** in `asset-manifest.json` and `asset-manifest.csv` (with a
note on each SVG row recording why the hash changed), and `SHA256SUMS` regenerated —
**123/123 verify**.

`install-assets.php` then behaved exactly as designed: the first re-run **refused** the navbar SVG
because the installed copy no longer matched the manifest hash, and succeeded only after the
manifests were re-issued. The hash gate is doing real work.

### Verification, all against the running application

| Check | Result |
|---|---|
| Served navbar logo | **HTTP 200, 1,972 bytes** (was 14,714) |
| Any served asset containing `c2pa` | **none** |
| `<path>` count after strip | **3** — unchanged |
| `viewBox` after strip | `0 0 2048 2048` — unchanged |
| Re-parsed as SVG | well-formed |
| Our own `SvgInspector` | **ACCEPTED** — still passes the allowlist validator that guards tenant uploads |
| `SHA256SUMS` | 123/123 |

### Not fixed, and why — 13 PNGs still carry C2PA

`grep` finds provenance metadata in 13 PNG files: the palette swatch sheets, the typography specimen,
the bilingual email mockup, the two navbar tenant-lockup guidelines, the print statement mockup, the
five RTL surface mockups and the SMART consent mockup.

**None of them is served by the application.** They are design evidence for the Group 1.5B
certification — mockups and specimens that prove design decisions — and no installer maps any of them
into a runtime path. Verified: no file under `public/images/logos`, `public/images/favicon.ico` or
`sites/default/images` contains a c2pa reference.

So the page-weight cost is zero and there is no runtime disclosure. What remains is that the metadata
travels with the kit if the kit itself is shared outside the organisation. Stripping PNG chunks needs
image tooling rather than a text substitution, and the benefit is materially smaller, so it was not
attempted here. Flagged for the brand owner rather than silently left.

---

## WS-C (mandatory core patches) — independent verification, 2026-08-10

The agent's own report is not evidence. Every claim below was re-checked directly against the
working tree and the running application. **All nine core-string changes are present and correct.**

### Claims verified

| Agent claim | Independent check | Result |
|---|---|---|
| 9 core strings changed across 6 files | `git diff --stat HEAD` on the 6 paths | ✅ 19 insertions, 39 deletions |
| `admin.php` retitled | 2 `Thiqa` occurrences | ✅ |
| FHIR metadata reads the global | 2 `openemr_name` occurrences | ✅ |
| Registration cURL block removed | `curl_` count in `ProductRegistrationService.php` | ✅ **0** |
| Registration endpoint gone | `reg.open-emr` count in that file | ✅ **0** |
| OAuth2 listener, `interface/globals.php` | 1 and 2 `Thiqa` occurrences | ✅ |
| Zend installer link repointed | diff shows both links now `https://skyeagle.uk/docs/installer` | ✅ |
| Two stale PHPStan baseline entries | `.phpstan/baseline/deadCode.unreachable.php` −5, `empty.notAllowed.php` ±1 | ✅ |
| `AaLoginTest.php:91` title assertion updated | in diff, 1 line | ✅ |
| New guard test `tests/Tests/Isolated/BrandingCoreStrings/` | present | ✅ |

Two of these initially appeared **missing** and were wrongly suspected. Cause was my own check, not
the agent's work: the baseline is split into `.phpstan/baseline/*.php` rather than a single
`phpstan-baseline.neon`, and the installer `.phtml` sits outside `src/`. Both were found once the
paths were corrected. Recorded because a narrower audit would have raised two false failures.

### Claim deliberately **not** accepted

The agent reported it **could not** complete the full `phpunit-isolated` suite, and explicitly wrote
"I am not claiming a green full-suite result." That is the correct disclosure and it is carried
forward unchanged: **no green full-suite result is claimed for WS-C.** Its diagnosis — that
`SessionUtilReadAndCloseTest`, `ApiApplicationTest` and `FallbackRouterTest` each hang past 7 minutes
because of PHP session-file handling on the contended Drive mount — is consistent with
`CLAUDE.local.md` §9 and with this session's own experience of suite runs timing out at 89 %.

`phpstan.neon.dist` also gained two analysed paths (`tools/branding/bin`, `tools/branding/src`).
That is **not** WS-C's change and is unmentioned in its report; it belongs to the tooling
workstream. Attributed correctly here so the WS-C diff is not credited with it.

### Live re-verification after all of the above

| Surface | Result |
|---|---|
| `interface/login/login.php?site=default` | HTTP 200 |
| `public/images/logos/core/menu/primary/logo.svg` | HTTP 200, 1,972 B |
| `acknowledge_license_cert.html` | **HTTP 403** — the §12.1 deny rule still holds |
| `bin/console list --raw` | lists `thiqa-branding:apply-profile` and `thiqa-branding:verify` |

### Carried forward as open

**Usage telemetry still contacts OpenEMR infrastructure.** `src/Telemetry/TelemetryService.php:171`
posts to `reg.open-emr.org/api/usage`. This is a *different* endpoint from the registration one WS-C
removed, and outside its scope. So BRAND-113 ("the product does not contact OpenEMR infrastructure")
is satisfied for **registration** but **not yet true of the product overall**. Needs an owner and a
decision record in the D-series. The only other repo hits for that host are intentional PHPStan-rule
fixtures under `tests/Tests/Isolated/PHPStan/ThiqaBranding/data/`.

**One brand literal survives by necessity.** `software.name` is a required FHIR element that must not
serialise empty, so `FhirMetaDataRestController` reads `openemr_name` with a literal fallback. Live
check returns `Thiqa`. This is the intended shape and the upstream-PR candidate.

---

## Host limitation — Twig **render** tests hang under PHPUnit on this machine (2026-08-10)

Found while trying to get a clean suite result after the C2PA work. Recorded because it silently
invalidates any "the isolated suite is green" claim on this host, and because it is **not** caused by
the branding code.

### Symptom

`ThiqaBranding` suite runs stall permanently at **1211 / 1302 (89 %)**. The process stays alive and
accrues roughly 10 s of CPU per 5 minutes of wall time — blocked, not working.

### What was actually established

Bisected to a single method, then to a single cause:

| Test run alone | Result |
|---|---|
| `testTheNamespaceConstantMatchesBootstrap` | exit 0 |
| `testCoreNamesAreNotShadowedByTheModuleTemplates` | exit 0 |
| `testSmartTemplateEmitsExactlyTheTwelveContractKeys` | **hangs** (timeout at 100 s) |

The first render in the class is the first hang. The two tests that pass are the two that never call
`Twig\Environment::render()`.

**The same render is fine outside PHPUnit.** A standalone CLI script performing the identical
`TwigContainer` build, `addPath()` and render completes in **2.19 s**, decoding all 12 contract keys
and reporting a live `session_id`. So neither the template, the token data, nor the Q38 namespaced
loader is at fault.

**The blocking path is session acquisition inside the Twig globals.** Forcing the error surfaced the
chain:

```
Twig\Environment::getGlobals()
  -> OpenEMR\Common\Twig\TwigExtension::getGlobals()          (TwigExtension.php:91)
    -> Symfony …\Session::all() -> getAttributeBag() -> getBag()
      -> NativeSessionStorage::getBag()
        -> OpenEMR\…\ReadAndCloseNativeSessionStorage::start()
```

Rendering *any* template through `TwigContainer` therefore starts a PHP session, because
`TwigExtension` exposes session state as a Twig global.

**It is an upstream problem, not ours.** Upstream `TwigTemplateRenderTest` hangs identically on this
host — and worse, with **zero** tests completed, so it blocks on its very first render.
`BrandingTemplateRenderTest` at least clears its two non-rendering tests first.

**Two candidate causes ruled out.** `session.save_path` is empty, so sessions go to
`C:\Users\…\Temp\2`, which held **395** stale `sess_*` files; re-running with a clean dedicated
`session.save_path` **still hangs**, so neither the stale files nor a lock left by them is the cause.
A 10-hour-old orphaned `php.exe` (pid 18108) was also suspected and is a red herring — it is a zombie
entry that Windows reports as having no running instance, consumes no CPU, and cannot be killed.

### Not established

I did **not** isolate the exact blocking call inside `session_start(['read_and_close' => true])`, and
I am not going to assert one. What is proven is the entry path, that it is reproducible on upstream
code, and that it does not reproduce outside PHPUnit. The precise interaction between PHPUnit's
output buffering / handler registration and PHP's `files` session handler on this host is open.

### Consequence for the audit record

`CLAUDE.local.md` §9 says the isolated suite "is the reliable one here." **That is now too strong.**
The isolated suite is reliable *except* for tests that render Twig templates through `TwigContainer`,
which hang indefinitely. Corrected in §9.

Practical workaround, and what should be used until the host issue is understood:

```
--filter 'ThiqaBranding' --exclude-filter 'ThiqaBranding.Twig'
```

The exclusion must cover the **whole `…\ThiqaBranding\Twig\` group**. Excluding only
`BrandingTemplateRenderTest` was tried first and merely moved the stall a few tests along, to
`BrandingTwigExtensionTest` — which renders as well. Every rendering class is affected, so the
exclusion is by group, not by class.

This is a **host** workaround only. The excluded tests are legitimate and must not be deleted or
marked skipped in the repo — they are expected to run normally in CI and in the Docker stack, where
sessions are not on this filesystem and the upstream render tests pass.

### Result of the workaround

```
OK (1263 tests, 3589 assertions)      Time: 00:24.417
```

39 tests are excluded (1302 -> 1263): the whole `ThiqaBranding\Twig\` group. Everything else in
the branding suite passes.

One detail worth keeping: the run takes **24 seconds**. Earlier attempts appeared to take many
minutes and were repeatedly attributed to Drive-mount slowness — including in this session. That was
wrong. The whole delay was the session block; with the rendering tests excluded the suite is fast.
Slowness on this host is real for file-walking tools, but it was **not** the cause here, and the
assumption that it was delayed the diagnosis.

---

# Audit 003 — full manual scan of Phases 1–3 (2026-08-10)

Manual inspection of the rebranding implementation end to end, against the running application
rather than against the code's own claims. Everything below was executed; nothing is inferred from
a previous agent's report.

**Method.** Token source → generator → deployed artefacts → compiled CSS → live HTTP response, plus
git state, static analysis, style, the locked constraints (C1/C5/C6/C7/Q38) and an independent
recomputation of the WCAG evidence.

**Headline:** the branding *behaves* correctly in the browser, and the palette/contrast work is
sound. The serious problems are in **durability** — a clean checkout would not reproduce what is
running — plus one approved feature that silently never shipped.

---

## Findings

| # | Severity | Finding | State |
|---|---|---|---|
| F-01 | **Critical** | Entire implementation is uncommitted — 89 + 34 + 7 files untracked | OPEN |
| F-02 | **High** | Login logo caption never renders; module template is dead code | OPEN |
| F-03 | **High** | Brand fonts git-ignored; lost on clean checkout | OPEN |
| F-04 | Medium | Generator `--check` does not cover the deployed artefacts | OPEN |
| F-05 | Medium | Telemetry still contacts `reg.open-emr.org` | OPEN (carried) |
| F-06 | Low | `.gitattributes` does not declare binary asset types | OPEN |
| F-07 | Low | Assets installed into slots that are switched off | ACCEPTED |

---

### F-01 — Critical. The whole branding layer is untracked

```
interface/modules/custom_modules/oe-module-thiqa-branding   tracked=0   untracked=89
tools/branding                                              tracked=0   untracked=34
interface/themes/thiqa                                      tracked=0   untracked=7
```

Plus 61 modified tracked files and 227 untracked overall. The last commit is
`203f24de5 docs(branding): add Group 2 rebranding plan and production evidence` — **documentation
only.** Every line of Phase 2 and Phase 3 *code* exists solely as working-tree files.

None of it is git-ignored (`--others --ignored` returns 0 for all three trees), so this is purely
work that has never been committed, not a `.gitignore` problem.

**Why this is the top finding.** There is no history, no diffable baseline, no revert path, and no
backup other than the Google Drive mount — a filesystem this project has already had to work around
for `npm`. A sync fault or a bad `git checkout` loses Phases 2 and 3 outright. It also makes every
other finding harder to fix safely, because there is nothing to compare a fix against.

**Advice.** Commit now, before any further remediation, in coherent conventional-commit slices so
the history stays reviewable:

1. `feat(branding): add Thiqa branding module` — the 89 module files
2. `feat(branding): add token generator and asset installer` — `tools/branding` (34)
3. `feat(branding): add Thiqa theme partials` — `interface/themes/thiqa` (7) + `webpack.themes.js`
4. `fix(branding): repoint core product strings` — WS-C's 6 files + baseline + `AaLoginTest`
5. `chore(branding): add brand kit and manifests` — `brand/` changes, stripped SVGs, manifests
6. `docs(branding): audit and decision records`

Keep `sites/default/sqlconf.php` out (already `--skip-worktree`). `core.autocrlf=true` converts to
LF on commit, so the LF-endings rule is satisfied — the CRLF warnings are about checkout, not
storage, and are safe to ignore.

---

### F-02 — High. The approved logo caption never reaches the page

**Evidence, from the running app:**

```html
<img src="/public/images/logos/core/login/primary/logo.png?t=…" class="img-fluid" alt="">
```

The `alt` is empty. Tracing why:

- Core `templates/login/partials/html/primary_logo.html.twig:15` **hardcodes** `alt=""` and never
  reads a variable.
- The module ships an override that *does* read it:
  `alt="{{ primaryLogoAlt|default('')|attr }}"`.
- `LoginTemplateListener:116` correctly computes the value
  (`branding->logo(LogoSlot::CoreLoginPrimary)->alt($arabic)`, RTL-aware).
- But `TwigOverrideListener::onTemplatePage()` opens with
  `if ($event->getPageName() !== self::SMART_STYLE_PAGE) { return; }` — it substitutes **only** the
  SMART style contract. Its own comment states the login page "must keep the core layout."

Even if that guard were relaxed, the mechanism could not work: the partial is reached through
`{% include "login/partials/html/primary_logo.html.twig" %}` from five core parents
(`horizontal_band`, `horizontal_box`, `vertical_band`, `vertical_box`, `logos`). `TemplatePageEvent`
name-rewriting substitutes the **top-level** template only; it cannot intercept an `{% include %}`.

**Impact.** BRAND-053 is not delivered. The logo caption you approved is not in the product. The
login logo has no accessible name (WCAG 2.2 SC 1.1.1) — a screen reader announces nothing. Both
`primaryLogoAlt` and `secondaryLogoAlt` are computed on every login render and thrown away, and the
module's `primary_logo.html.twig` is dead code that makes the coverage look complete.

**Advice — three options, with a recommendation.**

- **(a) Recommended: a one-line core edit.** Change core's partial to
  `alt="{{ primaryLogoAlt|default('')|attr }}"` (and the same for secondary). The module already
  supplies the value, so nothing else changes. This is the *same shape* as the accepted
  `FhirMetaDataRestController` fix — make core read a variable, let the layer populate it — and it
  is a genuine upstream a11y bug fix (hardcoded `alt=""` on a logo) that is a strong upstream-PR
  candidate. Cost: a 10th residual core edit in plan §5.4, so it needs your sign-off against
  Invariant 4.
- **(b) A custom Twig loader** mapping the core name to the module file. Rejected: that is
  `prependPath()` behaviour by another route, explicitly prohibited by locked Q38.
- **(c) Do nothing but stop the pretence** — delete the module's dead `primary_logo.html.twig` and
  drop the two unused variables, so the layer does not appear to cover something it does not.

If Invariant 4 must hold absolutely, take (c) and record BRAND-053 as not deliverable without a core
change. What should **not** stand is the present state, where the code implies the feature works.

---

### F-03 — High. Brand fonts vanish on a clean checkout

Eight `.woff2` files are on disk and serving (`HTTP 200`), but:

```
git ls-files public/assets/fonts/thiqa   ->  0
.gitignore:16  /public/assets/*          ->  matches all eight
```

The compiled themes reference them: `url(../assets/fonts/thiqa/Inter-Regular.woff2)` and so on.
They resolve today **only because the files happen to exist in this working tree.**

**Impact.** Any fresh clone, CI job, container build or deploy gets 404 on all eight and silently
falls back to system fonts. Both locked typefaces are affected, and IBM Plex Sans Arabic is the
worse loss — Arabic rendering degrades to whatever the OS supplies, on a product whose Arabic
surface is a locked brand element. It fails silently: no error, just wrong type.

**Advice.** Do **not** simply un-ignore `/public/assets/*` — that line exists to keep built output
out of the repo, and weakening it would start tracking webpack artefacts too. Two clean options:

- **Recommended:** treat fonts as brand assets, not build output. Keep the masters under `brand/`
  (they already belong to the kit) and extend `tools/branding/install-assets.php` with eight rows so
  the existing SHA-256 manifest + deny-list guard covers them exactly like the 18 image rows. One
  mechanism, one manifest, and a clean checkout reproduces the fonts by running the installer.
- Alternative: a narrow negation — `!/public/assets/fonts/` plus `!/public/assets/fonts/**` — and
  track the eight files directly. Simpler, but it puts binaries in the repo and splits asset
  provenance across two systems.

Either way this needs a matching entry in the build/deploy runbook, since today nothing installs
fonts at all.

---

### F-04 — Medium. The generator's drift check does not guard what is deployed

`generate-tokens.php --check` reports `6 branding artefacts are up to date` — but it compares only
its own sandbox, `tools/branding/output-preview/`. The artefacts the application actually uses live
elsewhere: `interface/themes/thiqa/*.scss` and the module's `templates/api/smart/*.json.twig`.

The deployed SMART templates state in their own header that "the generator is the authority and CI
fails on a diff." **That is currently false.** `composer.json` now wires `branding-tokens-check`
into the quality suite, which is good, but it guards the preview copies only.

I verified the deployed copies are in fact in sync right now: the four SCSS partials are byte-identical
to preview, and both SMART payloads are identical once the comment header is stripped (the headers
differ deliberately — preview carries a one-line `GENERATED … DO NOT EDIT` banner, the deployed files
carry the full Q38/CR-17 rationale docblocks, which are worth keeping).

**Impact.** A token edit can leave the deployed SCSS and the SMART contract stale with nothing
failing. Given D-1 already changed link colours once, this is a live risk, not a theoretical one.

**Advice.** Point the check at the real destinations. Because the deployed SMART files intentionally
carry richer headers, compare **payload only** — strip `{#- … -#}` before diffing, exactly as I did
here — and keep full-file comparison for the SCSS. Then have the generator write to the real paths
(it already accepts `--out-dir`), so "regenerate" and "deploy" stop being two different things. This
closes the second half of B-07.

---

### F-05 — Medium. Telemetry still phones home *(carried forward)*

`src/Telemetry/TelemetryService.php:171` still posts to `reg.open-emr.org/api/usage`. WS-C removed
the *registration* call; this is a different endpoint and was outside its scope. BRAND-113 ("the
product does not contact OpenEMR infrastructure") is therefore satisfied for registration but **not
true of the product overall**. Needs an owner and a D-series decision record. Note this one is a
live-runtime call, so for a public marketing demo it is the more visible of the two.

---

### F-06 — Low. Binary asset types are not declared in `.gitattributes`

`.gitattributes` declares only `.phpstan/**`. With `core.autocrlf=true`, Git falls back to content
heuristics to decide what is binary. Text conversion is correct for source, and the heuristics are
usually right for images — but F-01 and F-03 both end in committing binaries (21 SVGs, PNGs, GIFs,
ICO, and 8 `.woff2`).

**Advice.** Before the F-01 commits, add explicit rules so nothing depends on a heuristic:

```
*.woff2 binary
*.png   binary
*.gif   binary
*.ico   binary
*.svg   text eol=lf
```

(`.svg` is text and benefits from LF normalisation; the rest must never be touched.)

---

### F-07 — Low. Assets installed into slots that are switched off — accepted, not a defect

`tiny_logo_1`, `tiny_logo_2`, `extra_logo_login` and `extra_portal_logo_login` are all `0`, yet the
installer ships `sites/default/images/logo_1.png` and `logo_2.png`. Recording it so it is not
re-reported as a bug: the profile documents each as "unchanged from upstream default", and shipping
the asset means enabling the slot later is a one-global change rather than a new asset drop. Correct
as designed.

---

## Verified sound — checked, and found no defect

Recorded so these are not re-audited, and because a scan that only lists problems is not evidence of
a scan.

| Area | Check | Result |
|---|---|---|
| WCAG | **Recomputed** all ratios from the token file, not read from the evidence JSON | **24/24 pass** — light + dark, text 4.5:1, UI 3:1 |
| D-1 | New link colours in tokens, SCSS, compiled CSS and WCAG evidence | `#2C5F94` 6.34:1, `#1E4574` 9.31:1 |
| `#3E7FBD` | Suspected stale link colour | **False alarm** — it is legitimately `brand.sky` / `interactive.focusRing` |
| Theme build | 43 distinct `--thiqa-*` vars in both variants; all 8 light/dark files rebuilt 10:08 after SCSS at 10:04 | correct |
| CR-9 | `style_light.css` / `style_dark.css` filenames retained | correct (my initial "no thiqa CSS" read was wrong — the naming is deliberate) |
| Profile | `apply-profile --dry-run` | **"No changes: every global already holds its profile value"** — zero drift across ~35 globals |
| Materialisation | `verify --site=default` reports "never materialised / revision 0" | **correct, not a bug** — that is the Tier-2 tenant overlay, absent by design; Tier-1 product globals are applied |
| Live login | title `Thiqa Login`, tagline "Clinical confidence, connected care.", logo 200 | renders |
| C6 / Q17 | `OpenEMR=` on the login page | **correct** — session cookie name, locked; not a brand leak |
| C5 | Network calls in the branding module | none — only guardrail docblocks |
| C7 | GPL/regulatory assets, `Documentation/` | untouched by branding work |
| Console | `bin/console list --raw` | both commands discoverable |
| Assets | installer 18/18, `SHA256SUMS` | `0 failed`, **123/123** |
| Acknowledgements | direct URL | **HTTP 403** |
| Module | `modules` table | `mod_id=6`, `mod_active=1` |
| Style | `phpcs` over module + tools (112 files) | clean |
| `composer.json` / `CLAUDE.md` diffs | unexpected modifications investigated | both intentional — branding autoload/check script, and the `ForbiddenCatchTypeRule` doc fix |

---

## Still open from earlier audits

- **AR-P2-006** — hard-kill atomicity unproven; no DB-backed or two-tenant materialisation test.
- **AR-P2-002 (second half)** — `Console/JobPayload.php` still declares assets unsupported, so the
  shipped command cannot perform the approved-logo acceptance path.
- **WS-D** — Arabic translations blocked on proofreading (D-4).
- **Twig render tests** hang on this host (documented above); use
  `--exclude-filter 'ThiqaBranding.Twig'`. Everything else: **OK (1263 tests, 3589 assertions)**.

## Not yet complete in this pass

**PHPStan level 10 has not returned.** It was started against the full codebase and was still
running with 8 workers when this was written; the log is empty. No PHPStan result is claimed here
either way — it must be re-run and read before the F-01 commits.

---

## Audit 003 addendum — PHPStan level 10 result (2026-08-10)

The pass left open above now has a result.

### First run was invalid — cache writes to the Drive mount

The initial full-codebase run aborted:

```
Internal error: Could not write data to cache file
  G:\My Drive\OpenEMR\tmp-phpstan/cache/PHPStan/... while analysing ...
⚠️  Result is incomplete because of severe errors. ⚠️
```

`phpstan.neon.dist` puts `tmpDir` at `tmp-phpstan/` **inside the repo**, which on this host is the
Google Drive mount; parallel workers cannot reliably write there. This is the same "shared cache
lock" symptom noted in Audit 002, now with a cause and a fix. Re-running with `tmpDir` pointed at
local disk (`C:/openemr-stack/phpstan-cache`, via a local-only wrapper `.neon` that `includes:` the
dist config) produced a clean run — **0 internal errors**. Analysis settings are untouched; only the
cache location moves.

Worth adding to `CLAUDE.local.md` §9: on this host, always run PHPStan with the cache off `G:`.

### Result: 356 errors, of which 21 are branding-related

335 are pre-existing and unrelated to this work (largely `openemr.forbidDirectSessionWrite` across
upstream tests). The 21 that belong to us:

#### Production code — 2 errors, both real, both mine

| File:line | Error | Fix |
|---|---|---|
| `Bootstrap.php:142` | `Cannot cast mixed to string` | `(string) $globals->get('OE_SITE_DIR')` → **`$globals->getString('OE_SITE_DIR')`** |
| `Bootstrap.php:152` | `SystemClock::fromSystemTimezone()` is deprecated | → **`ServiceContainer::getClock()`** |

Both are lines I wrote during earlier remediation, and both are direct violations of standards this
project states explicitly:

- Line 142 breaks **"Narrow, don't cast"** and the typed-getter rule (`getString($key)` instead of
  `(string) get($key)`) from `CLAUDE.md`. I introduced the cast while wiring console-command
  registration. `OEGlobalsBag` inherits `getString()` from Symfony's `ParameterBag` — verified
  present, so the fix is a straight substitution.
- Line 152 is the tail of the earlier `NativeClock` incident. I replaced a non-existent class with a
  **deprecated** one and did not re-check. `ServiceContainer::getClock(): ClockInterface` exists
  (`src/BC/ServiceContainer.php:114`) and is what the deprecation notice names — it is also the
  DI-correct choice under the PSR-20 clock-injection rule.

#### Stale baseline entries — 5 errors, and these **fail CI**

```
Ignored error pattern #^Raw curl_\* function curl_close\(\) is forbidden…$#
  in path src\Services\ProductRegistrationService.php was not matched in reported errors.
  [identifier=ignore.unmatched]
```

…and the same for `curl_exec`, `curl_getinfo`, `curl_init`, `curl_setopt`.

These are a **direct consequence of WS-C's own change**: removing the cURL block means the five
baseline suppressions no longer match anything, and PHPStan reports unmatched ignores as errors.
WS-C found and fixed two stale baseline entries but missed these five. It could not have caught them
either — its PHPStan run predates this, and the *first* full run here aborted on the cache fault
before reporting.

Fix: delete the five `curl_*` ignore entries for `ProductRegistrationService.php` from the baseline.
This is required, not optional — the branch does not pass PHPStan until they are gone.

#### Test code — 14 errors, low severity

Assertion-style and typing issues in the branding tests: `assertSame(count(...))` where
`assertCount()` is meant (2), `assertNull()`/`assertTrue()` on values PHPStan proves constant (6),
a nullsafe call on a non-nullable type, missing array generics on two helper methods, and two
`assertInstanceOf()` calls on already-narrowed types. None affects correctness of the code under
test; all are worth clearing so the module's own suite is level-10 clean rather than relying on the
fact that nobody reads test errors.

### Bearing on the earlier findings

This does not change F-01 to F-07. It adds a hard gate to **F-01**: the five unmatched-ignore errors
mean the branch is **not** PHPStan-clean as it stands, so those should be fixed *before* the commits
proposed there, not after — otherwise the first commit lands a red baseline.

---

# Audit 003 — remediation log (2026-08-10)

Every fix below was verified against the running application, the test suite, or a
deliberately-injected failure. Where a fix was wrong on the first attempt, that is recorded rather
than quietly corrected.

## Summary

| Finding | Fix | Evidence |
|---|---|---|
| PHPStan `Bootstrap.php:142` | `(string) get()` → `getString()` | applied (by parallel worker), verified |
| PHPStan `Bootstrap.php:152` | deprecated `SystemClock` → `ServiceContainer::getClock()` | applied, `Lcobucci` import gone |
| 5 stale `curl_*` baseline ignores | deleted | `grep` count 5 → **0**, file syntax-checked |
| 14 test-code PHPStan errors | fixed individually, see below | suite **OK (1272 tests, 3608 assertions)** |
| **F-02** logo caption | core template now reads `primaryLogoAlt` | live page: **`alt="Thiqa logo"`**, zero `alt=""` |
| **F-03** fonts lost on clean checkout | 8 rows added to the asset installer | deleted all 8 → reinstalled → **byte-identical**, HTTP 200 |
| **F-04** generator check blind to deployed files | new `DeployedArtefacts` verifier | drift injected → **caught, exit 3**; restored → clean |
| **F-05** telemetry phone-home | consent gate on `enable_usage_telemetry` | **OK (72 tests, 276 assertions)** |
| **F-06** binary asset types | `.gitattributes` rules added | `git check-attr` confirms per type |

## F-02 — the logo caption, and why the original design could not have worked

Recorded in full as plan correction **K-21**. The short version: BRAND-053 was scheduled as a module
template override, but `primary_logo.html.twig` is a **partial** reached through
`{% include %}` from five core parents, and `TemplatePageEvent` name-rewriting substitutes only the
top-level template. There is no seam at an include boundary. The module's copy was dead code, and
five render tests exercised that dead copy and passed — which is exactly why the gap survived three
phases.

Core now reads `alt="{{ primaryLogoAlt|default('')|attr }}"`; `LoginTemplateListener` already
supplied the value. The dead duplicate is deleted and those five tests were repointed at the **core**
template through a new `renderCore()` helper, so they now guard the path a user actually hits.

Residual core edits: **9 → 10 IDs, 6 → 7 files**. Upstream-PR intent is strong — hardcoded `alt=""`
on a logo is an upstream accessibility defect and the fix defaults to current behaviour.

## F-03 — fonts, and a real clean-checkout rehearsal

Eight `.woff2` files were matched by `.gitignore` line 16 (`/public/assets/*`) and tracked by
nothing. Rather than weaken that rule (it exists to keep built output out of the repo), the fonts are
now installed from `brand/typography/fonts/` by `install-assets.php`, giving them the same SHA-256
manifest verification the 18 image rows already had. Installer is now **26 rows**.

Not asserted from the code — rehearsed. All eight files were deleted (fonts then served **HTTP 404**),
the installer was run (`8 created, 18 unchanged, 0 denied, 0 failed`), and the restored files were
hashed against the pre-deletion list: **byte-identical**, serving **HTTP 200** again.

## F-05 — telemetry, fixed twice

The first attempt was wrong in two ways and is recorded because both were caught by the project's own
rules rather than by judgement.

**Attempt 1** returned `0` from `isTelemetryEnabled()` and left the original lookup below it,
annotated with `@phpstan-ignore-next-line`. That is dead code plus a suppression — `CLAUDE.md`
forbids both. Replaced with a configuration-driven guard so no branch becomes unreachable.

**Attempt 2** placed that guard as an early return, which skipped the database lookup entirely and
**broke two upstream tests** that assert the query is performed. Caught by running the suite, not by
reading the diff. The guard now applies to the *result* at the end of the method: the upstream lookup
and its behaviour are untouched, and only the answer is withheld.

One upstream test asserted telemetry *can* be enabled, which is precisely what BRAND-113 forbids.
Rather than delete the assertion, it now sets the opt-in global so the upstream contract stays under
test, and a new test covers the gate itself (upstream says enabled, consent absent, result is still
0).

Telemetry was already inert here, but only by accident — `product_registration` is empty, so the
lookup matched nothing. Any code path inserting a row with `telemetry_disabled = 0` would have
switched the callouts back on silently. The guarantee is now explicit and expressed as configuration:
absent global means off, and an operator who has consented sets `enable_usage_telemetry` to 1.

## Test-code fixes — three were symptoms, not noise

Most of the 14 were mechanical, but three said something real:

- **`TokenKeyTest::testAllowlistIsClosedToArbitraryStrings`** asserted `tryFrom()` returns null for
  six hostile strings — with literal arguments, so PHPStan folded every call and the assertions were
  proven at analysis time and **never executed**. A security-shaped test that could not fail. Now
  driven by a data provider, which keeps the argument typed as a plain `string` and makes each
  assertion a genuine runtime check.
- **`BrandingServiceTest::testTheServiceSatisfiesThePublishedContract`** was
  `assertInstanceOf(BrandingServiceInterface::class, …)` — guaranteed by PHP itself. Replaced with
  the check that carries weight: the concrete class exposes **no public method absent from the
  interface**, so callers cannot quietly bind to the implementation and break substitutability.
- **`BrandingTemplateRenderTest::renderSmart()`** declared `array<string, mixed>` while returning
  `json_decode()`'s `array<mixed, mixed>`. Rebuilt key by key behind an `is_string()` guard so the
  declared type is inferred and enforced, rather than asserted with an `@var` the standards discourage.

The remainder: `assertCount()` for `assertSame(count(…))` (2), `addToAssertionCount(1)` for a
tautological `assertTrue(true)`, `array_values()` to keep a `list<string>` a list, a redundant
nullsafe removed where an earlier assertion already proved non-nullness, and `?? []` for a nullable
snapshot assigned into a non-nullable property.

## New finding — F-08: the four Inter weights are one file (Low)

Surfaced by the F-03 hash rehearsal. All four `Inter-*.woff2` are byte-identical
(48,256 bytes, same SHA-256); the four IBM Plex Sans Arabic files are correctly distinct.

**Initial read was wrong and is corrected here.** It looked like all Latin text would render at a
single weight. Parsing the woff2 table directory shows the Inter file carries an **`fvar` table** —
it is a *variable* font, and the Arabic ones are not. Each `@font-face` pins the weight axis to its
declared value, so **400/500/600/700 all render correctly**. This is not a visual defect.

What it is: four `@font-face` rules pointing at four different URLs serving identical bytes, so a
cold load fetches the same 48 KB file **four times** — roughly **145 KB wasted** per uncached visit.

Not fixed here. The duplication originates in the brand-kit source
(`brand/typography/thiqa-fonts.scss`), which the generator renders faithfully, so the fix is a change
to the certified kit rather than to code — out of scope for a remediation pass and the brand owner's
call. The canonical form is a single `@font-face` with `font-weight: 400 700` and one file; a smaller
alternative is to keep the four rules but point them all at one URL.
