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
| 6 | **Landed-work verification complete.** S2-P1-25 independently verified at 123/123, exit 0. S1-P0-01 verified all eight formerly unrecorded files are covered by PR-23…PR-28; the residual V-09 six-file scope was corrected to the authoritative 33-file inventory and reverified against current `upstream/master` (47 conflict records; only PR-14 inside the recorded set; none of the eight new files). Next: S1-P0-09 / PRE-16 runtime proof. |
| 7 | **PRE-16 runtime proof executed and exactly restored.** A valid revision-1 overlay reached DB, generated CSS, live endpoint, page link, and browser custom properties, while `.btn-primary` remained on the static `--thiqa-*` value. Preflight DB values, CSS hashes/timestamps, verify state, page links, and browser styles all matched after restoration. PRE-16 is DONE — VERIFIED; S1-P0-09 remains confirmed and open for repair. |
| 8 | **S1-P0-09 FIXED — VERIFIED; repaired PRE-16 chain passed and was exactly restored.** Commit `566b14ea68dd106fe0e38d5d77f2df46b5da25a8` made the bare, identity-neutral properties canonical, retained `--thiqa-*`/`--oe-*` compatibility aliases, and connected real component consumers with safe Tier-1 fallbacks. Automated light/dark coverage passed (239 tests, 987 assertions); the live Login button changed to the materialised light and dark tenant values, then DB, files, theme selector, page links, browser styles and temporary cache state were restored. No SkyEagle identity work began. |
| 9 | **S1-P0-13 + S2-P0-21 implementation begun after bounded reconciliation.** Selected design: `%s Database Upgrade` as the identity-neutral literal key; product name supplied from `openemr_name` before one context-specific escape; a checked-in JSON contract generates a separate SQL supplement loaded after either installer translation source; branch-cut regeneration reasserts that supplement after replacing `currentLanguage_utf8.sql`; upgrades use a transaction-journalled migrator that resolves exact names once, asserts uniqueness, then mutates only stable IDs. Live DB inspection was read-only: both legacy keys still have the same 28-language set; total counts 13,235 constants / 237,542 definitions; 0 orphans; 0 duplicate pairs. Status remains **IN PROGRESS** pending implementation and the full matrix. |
| 10 | **S1-P0-13 + S2-P0-21 FIXED — VERIFIED.** Commits `948e4a6d1`, `02671f0c9`, and `2baf7322a` implement the neutral renderer, authoritative 28-language contract, generated installer supplement, release-prep regeneration, journal schema, automatic upgrade migration, deterministic rollback command, and full regression matrix. Final targeted PHPUnit: 41 tests / 134 assertions / exit 0. PHPCS: 18 files / exit 0. The final supplement was applied twice to a disposable MariaDB database: 1 target constant, 28 definitions, 3 RTL definitions, 0 orphans, 0 duplicate pairs; the exact database was dropped and confirmed absent. The live `openemr` DB was never mutated. PR-29…PR-32 expand the patch inventory from 33 to 49 distinct production files. Next incomplete task: S1-P1-03 CI wiring. |
| 11 | **PRE-09 / S1-P1-03 FIXED — VERIFIED; PRE-11 completed.** `597276b09d507cc0e61a2c8784bd49c670c1d020` adds the canonical `composer branding-ci` gate to the existing `Isolated Tests` workflow; `ff6e35b4f4ddca0b16eb7a52c4489e7390664157` adds the runbook and PR-33 governance record. Final aggregate: 12/12 generated artefacts, 123/123 manifest entries, 91 tests / 264 assertions, exit 0. Reversible negative controls proved generated drift exit 3, manifest mismatch exit 1, 12 guardrail violations across all four required identifiers exit 1, and zero tests exit 1 after the repair (versus exit 0 before). All temporary mutations were exactly restored. Workflow YAML, Composer config, PHP syntax and PHPCS passed locally. GitHub-hosted execution: NO. Next incomplete task: S1-P1-15 backup retention. |
| 12 | **PRE-09 / S1-P1-15 FIXED — VERIFIED.** `77d2b3e12914cd172d72d47b6fe4543750b70735` introduces the neutral `managed-db-backup-v1-<label>-<timestamp>.sql` contract, strict verified legacy recognition, deterministic mixed-family retention and defensive deletion. `8eb4ea7f8dbaf92f023f2a745a17f767f6a15f07` adds the full temporary-directory matrix to `composer branding-ci`; `64d2ba23c46336ee567cfcac944f08668be466a5` documents migration/rollback and PR-34. Final targeted result: 56 tests / 1,422 assertions / exit 0, no skips. Canonical gate: 12/12 artefacts, 123/123 manifest, 147 tests / 1,687 assertions / exit 0. Negative control proved a neutral-prefix-only selector ignored three legacy files while the repaired selector retained one ordered archive and left unrelated SQL untouched. All fixture directories removed. Real backup directory touched: NO. Live database touched: NO. Next incomplete task: S1-P1-17 disabled-token contract decision. |
| 13 | **PRE-09 / S1-P1-17 FIXED — VERIFIED.** `0af1ce1740f1e1caf6d8536a18cee4c2c50917a5` preserves the deliberate 11-key tenant allowlist, keeps inactive controls outside WCAG SC 1.4.3/1.4.11, and adds a separate 1.5:1 product distinguishability floor between the disabled primary fill and both the enabled fill and page background. Exact source counts are 11 overridable / 10 WCAG-gated / 1 product-gated. Light/dark, malformed value, empty-overlay/downstream, canonical/legacy CSS names, fixed disabled opacity and live consumer tests passed. Final targeted: 261 tests / 773 assertions; downstream: 153 / 577; canonical gate: 12/12 artefacts, 123/123 manifest, 147 / 1,687; all exit 0. A reversible threshold mutation made the named negative test fail (exit 1) and exact source hash restoration was verified before the same test passed. Eight compiled theme files were updated by hash; all 28 runtime theme files match the complete local build. Next incomplete task: S1-P1-02 dead translation overrides. |
| 14 | **PRE-09 / S1-P1-02 FIXED — VERIFIED.** `2b801e668097599ac3e870448d340567929b4767` removes exactly the two dead OAuth overrides from the active catalogue and records exact-value English retirement metadata; the three Zend overrides remain active and verified against live consumers. Fresh/upgrade/customized-tenant policy, tenant-title English rendering, no stale consumers and the neutral database-upgrade contract are automated. Targeted result: 44 tests / 227 assertions / exit 0. Canonical gate: 12/12 artefacts, 123/123 manifest, 157 tests / 1,760 assertions / exit 0. Live dry-run planned exactly two deletes; before/after hashes for all five definitions of each constant were identical. A reversible decision mutation produced two failures / exit 1, then exact source hash restoration and the full targeted pass were verified. Next incomplete task: S1-P1-05 WCAG evidence counts. |
| 15 | **PRE-09 / S1-P1-05 FIXED — VERIFIED.** `b400546a2ffa1f1a6edb6b802315124bcef2bd67` re-derived the machine evidence as 38 pairs / 35 PASS / 3 ADVISORY / 0 FAIL, added the four missing passing `borderStrong` rows to the human record, and installed a row-level drift guard in the canonical gate. Source/calculator proof: 110 tests / 264 assertions; combined targeted: 113 / 632; canonical gate: 12/12 artefacts, 123/123 manifest, 160 / 2,128; all exit 0. Two covered-document hashes were re-issued, never removed, with the reason recorded in release verification Rev 6. A reversible 35→34 summary mutation failed exit 1; exact document hash restoration and final passes were verified. Next incomplete task: S1-P1-06 D-8 status reconciliation. |
| 16 | **PRE-09 / S1-P1-06 FIXED — VERIFIED; D-8 correctly remains OPEN.** `88ff342894c9543fe30b8e6b6a046ca2f56fd1b7` reconciles `RebrandingPlan.md`, `remaining-dependencies.md` and ADR-BRAND-001 with current execution: the endpoint is the browser-serving route, but `BrandingMaterialiser` still invokes `TokenCssWriter` and commits both static variants, including for an empty overlay. The new contract guard passed 2 tests / 19 assertions; materialiser/writer plus contract passed 34 / 165; canonical gate passed 12/12 artefacts, 123/123 manifest and 162 / 2,147; all exit 0. A reversible false-closure mutation failed 1/1, exit 1, and the plan was restored exactly to SHA-256 `796AC7096B052EE984E327A7DF2411351AA73AA3860D8675886422335820F828`. No runtime state was changed. Next incomplete task: S1-P1-10 false PDF-font capability claim. |
| 17 | **PRE-09 / S1-P1-10 FIXED — VERIFIED; Arabic PDF capability remains unavailable by recorded Owner decision.** `723170df701ddb948a95b8177d100411fc71b0d9` removes the false installer claim that Amiri is registered with mPDF, distinguishes asset delivery from engine support, corrects the stale IBM-Plex PDF recommendation, and installs a canonical truthfulness guard. Current runtime evidence is mPDF 8.3.1, dompdf 3.1.5, DejaVu Sans default, and no Amiri/Noto registration; EV-RB14 records the shaping failure and Owner-adopted Option C through pilot. Targeted 2 tests / 20 assertions and canonical 164 / 2,167 passed; manifest returned from the expected 121/123 pre-reissue failure to 123/123 after exactly two documented hash reissues. The old claim caused the named test to fail exit 1 and exact installer restoration was verified. Next incomplete task: S1-P1-11 operational Thiqa identity classification/neutralization. |
| 18 | **PRE-09 / S1-P1-11 FIXED — VERIFIED; live infrastructure and compatibility history preserved.** `29be1fcd5b14001ce2a1a0d1aa390ef10aa617a0` classifies the ten relevant operational occurrences, neutralizes only the reusable date-rebase labels and stale hypothetical bucket example, and explicitly preserves the historical deployment branch, production module directory, pre-existing external bucket and historical records. No remote host, installed unit, branch, module, bucket or backup was changed. The contract passed 2 tests / 14 assertions; both edited shells passed `bash -n`; canonical gate passed 12/12 artefacts, 123/123 manifest and 166 / 2,181. A reintroduced Thiqa service label failed the named test exit 1, followed by exact script restoration and final passes. Next incomplete task: S2-P1-18 branding-health truthfulness. |
| 19 | **PRE-09 / S2-P1-18 FIXED — VERIFIED; the live tenant now reports the truth and exits 0.** `1474263b4` introduces `BrandingObservationPlane`, which names the served route (overlay globals → revision-keyed `<link>` → `branding-tokens.php`) and the unserved static-artefact route (`public/branding/<site>/tokens-*.css`, D-8) and owns the single severity rule. Every finding declares its plane; the report keeps served-plane inconsistencies and static-artefact advisories in separate lists and refuses a finding placed on the wrong plane; only served-plane findings reach `statusFor()`. Two new served-plane cases (`overlay_without_revision`, `unrenderable_token_overlay`) close the under-measured half, and the overlay is read through the runtime's own `TokenOverlay::fromJson()`. The gate gap is closed: the Observability suite and `VerifyCommandTest` now run inside `composer branding-ci`, pinned by a new truthfulness contract. Live `verify` moved from `inconsistent` / exit 1 to `never materialised` / exit 0 with one advisory, with the database and both generated files unchanged. Targeted 65 tests / 684 assertions; canonical gate 12/12 artefacts, 123/123 manifest, 219 tests / 2,413 assertions; all exit 0. Next incomplete task: S1-P1-04 guardrail namespace cross-check. |
| 20 | **PRE-09 / S1-P1-04 FIXED — VERIFIED. S2-P1-20 PARTLY REFUTED (Correction K), remainder FIXED — VERIFIED.** `2df9b5eb1`. S1-P1-04: `ThiqaBrandingGuardrailScopeTest` locates the module by a brand-neutral anchor (`src/Config/BrandingGlobalKey.php` / the Q58 `saas_branding_` prefix), derives the production namespace from its PSR-4 autoload prefix, cross-checks that against all 92 shipped source files, and asserts each rule's `MODULE_NAMESPACE` equals it — failing in both directions. Negative control renamed production to a SkyEagle prefix with the constants left alone: 7 failures / exit 1, naming all four rules as "would go inert". S2-P1-20: the four identical Inter hashes reproduced exactly, but a WOFF2 table-directory decode found `fvar`/`gvar`/`HVAR` in every Inter file and none in any IBM Plex file — Inter is one **variable** face, the shipped CSS declares it at `font-weight: 400 700`, and RB-22's "FIXED" is accurate. The rendering claim is withdrawn as Correction K. The real gap — nothing verified that a *static* family's declared weights map to distinct faces — is closed by `BrandingFontFaceDistinctnessContractTest`; the three unreferenced duplicate binaries are pinned rather than deleted, since retiring them is an Owner asset-governance decision. Targeted 9/240 and 5/218; full guardrail suite 63/320; canonical gate 12/12 artefacts, 123/123 manifest, 233 tests / 2,871 assertions; all exit 0. Next incomplete task: S2-P1-23 RTL word-order regression. |
| 21 | **All four P2 findings FIXED — VERIFIED. S2-P1-22/23/24 investigated to a specified, evidence-backed stop.** `97f6952cf`. S1-P2-07: counts re-derived on disk (18 top-level CSS, 19 entries, 28 files) and corrected to 18/4 in the runbook and changes.md row 076. S1-P2-12: `Config\ModulePaths` becomes the single owner of the module directory name; `Bootstrap`, `BrandingService` and the Twig namespace now derive every path, and `ModulePathContractTest` guards the three consumers that cannot share a PHP constant (`install-assets.php`, `.gitignore`, the Twig rule tip). One inherited claim corrected: `webpack.themes.js` does **not** reference the module directory. S1-P2-14: `RebrandingBugs.md` §10 now records four worktrees, explains why no gitignore mechanism can reach the sibling, and adds it as a path exclusion plus a standing `git worktree list` check. S1-P2-16: command count corrected 3 → 6 in all three places, with the blast-radius consequence stated. **S2-P1-23/22/24: a repair was implemented across all seven juxtaposition sites and then deliberately reverted** — live catalogue counts showed the finding's own recommended fix orphans 10–34 translations per call site (the RB-01 failure mode). The tree was restored exactly and nothing was committed; the safe design, the blocking single-key limitation of the contract subsystem, and the unescaped-`applicationTitle` trap are now specified in full under S2-P1-23. Targeted 93 tests / 830 assertions; canonical gate 12/12 artefacts, 123/123 manifest, 238 tests / 2,886 assertions; all exit 0. |

| 22 | **PRE-09 / S2-P1-23 + S2-P1-22 + the `About` half of S2-P1-24 FIXED — VERIFIED.** The design specified at Rev 21 was built. Contract schema **v2** adds `derive_from` (`source_key` + `placement: prefix\|suffix`), and the contracts *directory* replaced the single named file: `TranslationCatalogueContractSet` loads every `*.json` sorted, rejects duplicate ids/targets and refuses derivation chains, and `sql_upgrade.php`, the migration command and the release-prep mutator all iterate it. Ten call sites (7 Twig via the new `xlp` filter, 3 Zend layouts) now compose one translatable unit. **The carry-forward is what makes it safe:** each locale's new pattern is its own existing translation with `%s` where the template put the name, so rendering is byte-identical in every language. Proven on disposable database `openemr_prebrand_xlate_multi_20260824_174721`: `%s Login` → `%s تسجيل الدخول`, `About %s` → `حول %s` / `אודות %s`, `%s Application` → `Aplicacion %s` / `%s Anwendung`; the `%`-bearing source, the case-only near-duplicate and the unbranded legacy row were all correctly skipped; applied twice with identical counts (15 constants, 53 definitions, 0 orphans, 0 duplicate pairs); dropped and confirmed absent. v1 `database-upgrade.json` is unmodified and still renders its 28 explicit inserts. `english_overrides` is now empty, all five keys retired by exact value, dry-run planned exactly 5 deletions with no writes. Targeted: translation 42/145, core-strings 46/290, composition contract 5/437, mutator 6/13; all exit 0. Live database mutated: NO. Next: S2-P1-26, then the second half of S2-P1-24. |

| 23 | **PRE-09 / S2-P1-26 SUBSTANTIALLY FIXED — VERIFIED. Class B complete; Class A specified.** The finding's counts were re-derived by exact quoted-literal extraction rather than the substring matching it used — which had inflated the bare `OpenEMR` key to "52 call sites". True figures over 4,105 files: **Class A** (has a catalogue row, a contract can neutralise) 20 literals / 22 sites; **Class B** (no row at all, no override can ever reach it) 22 literals / 25 sites; 49 dead entries. The structural half is therefore *larger* than recorded, not smaller. Class B is fully converted (15 literals / 18 sites) via a new PHP `xlp()` helper mirroring the Twig filter — safe by construction, since with no catalogue row nothing can be orphaned. **Six strings naming the OpenEMR Foundation, the upstream community or ONC certification are deliberately preserved and now locked by test**: neutralising them would make the software assert something untrue about who holds a certification or who should receive a report. Two more are excluded for mechanical reasons (one JS-side `xl()`, one carrying two `OpenEMR` occurrences against a one-placeholder contract). Class A remains open with its mechanism already built and its recipe written down. Targeted 13 tests / 474 assertions; canonical gate 12/12 artefacts, 123/123 manifest, 253 tests / 3,224 assertions, exit 0. The gate also gained an explicit 1800 s `config.process-timeout` budget after a **passing** suite was killed by Composer's 300 s default — the per-script `@putenv` form was tried first and does not work, because Composer reads the timeout at startup. |

| 24 | **PRE-09 / S2-P1-26 Class A CLOSED — VERIFIED. S2-P1-26 is now fully fixed.** All 20 catalogued brand-bearing literals have a schema-v2 `legacy_keys` contract and all 22 call sites are converted, including `interface/login_screen.php:29` — the login page, the finding's most-exposed surface. **A latent defect shipped at Rev 22 was found and fixed first:** the generated installer SQL skipped a translation that never named the product, while the PHP upgrade migration **threw** on it — the same contract producing different catalogues on fresh install versus upgrade, which is the divergence S2-P0-21 exists to prevent. Measured, not hypothetical: every brand-bearing key has 1–4 such rows, and the two contracts shipped at Rev 22 (`OpenEMR Application`, `Welcome to OpenEMR`) have two each, so they **would have aborted a real upgrade**. My own new contract test caught it, not inspection. `MissingIdentityPolicy` now makes the choice explicit per contract — `fail` stays the default because silently losing a locale is worse than a loud stop; `skip` is the declared opt-in on all 22 legacy contracts, with its cost stated (those locales fall back to the neutral English pattern at that one call site). Disposable-database proof on `openemr_prebrand_classa_20260824_202517`: applied twice, identical counts (31 constants, 46 definitions, 0 orphans, 0 duplicate pairs), Arabic carried forward as `يتطلب %s جافاسكريبت…` with the placeholder in the *translator's* word order, and the rows lacking the literal correctly skipped; dropped and confirmed absent, live DB intact at 13,235 constants. Targeted 61 tests / 495 assertions; canonical gate 12/12 artefacts, 123/123 manifest, 255 tests / 3,271 assertions; PHPCS 27/27; all exit 0. |

| 25 | **PRE-09 / S2-P1-24 text half FIXED — VERIFIED; logo half blocked on a non-existent asset. Every documented P0, P1 and P2 finding is now closed or explicitly blocked.** `xl_product_name()` makes the authenticated shell's `<title>` and `WindowTitleBase` return `saas_branding_product_name_ar` when the session language is Arabic. **The predicate is the language, not the direction** — `lang_languages` marks four locales RTL (Hebrew, Arabic, Persian, Urdu) and an Arabic wordmark is right for exactly one; keying on `lang_is_rtl` would put Arabic script in front of Hebrew and Persian users, a worse error than the one being fixed. A contract test asserts the `'ar'` comparison and forbids `lang_is_rtl` inside that function's body (the file's own `getLanguageDir()` uses it correctly). The helper degrades to `openemr_name` when no Arabic name is set or the branding layer is absent. `translation.inc.php` loads on every request, so the login page was re-fetched after the change: HTTP 200, 9,165 bytes, byte-identical to the recorded baseline. The **Arabic logo variant remains open and is not a code problem** — §13 records that no dedicated Arabic wordmark exists, `Entity.md` forbids deriving one from the Latin artwork, and KG-05 requires an approved asset over a fabrication. Targeted 16 tests / 330 assertions; canonical gate 12/12 artefacts, 123/123 manifest, 256 tests / 3,280 assertions; all exit 0. **Remaining programme work is now Scan 3 (PRE-18…24) and PRE-25 certification**, plus two unlabelled P1 slots inherited from Scan-2's register that were never transcribed here. |

| 26 | **Self-review pass over this session's own work — two checks, both now permanent.** Explicitly **not** the independent Scan-3 audit: this is the author reviewing the author, and independence is the property it lacks. (1) **Installer-time safety.** `library/globals.inc.php` is `require`d by `library/classes/Installer.class.php:827` *during installation*, and `xlp()` now runs inside it — adding an `OEGlobalsBag::getInstance()->getString('openemr_name')` call to a path where no database, session or bootstrap exists. Exercised cold: `getInstance()` succeeds, `getString()` returns `''`, and composition yields `'Enable  Standard REST API'` — a double space, **not** a throw. Benign, and those label strings are not displayed during install; recorded rather than "fixed", because the honest alternative (falling back to a hardcoded name) would reintroduce the leak. (2) **Every shipped pattern composes.** `ProductContextTranslation` throws on a stray `%`, so a bad target key or definition would not fail a build — it would fatal whichever page rendered it, in whichever locale reached it. All 56 contract patterns and all 39 `xlp()` literals in the tree compose; the 14 literals with no contract are exactly the Class-B set, as expected. That invariant is now a gated test (`testEveryContractPatternComposes`) rather than a one-off script. **A scratch-scanner bug is recorded too:** the first run reported 3 patterns "would throw", which was a greedy Twig regex capturing whole template blocks as if they were literals — the tool was wrong, not the code, and the corrected run is clean. Targeted 17 tests / 388 assertions; PHPCS 1/1; all exit 0. |

| 27 | **SCAN 3 DISPATCHED — five independent fresh agents.** PRE-09 is complete (apart from the asset-blocked Arabic logo variant), so the programme moved to its adversarial stage. The orchestrator did **not** run Scan 3 itself: having performed the remediation for 20+ findings, an orchestrator-run adversarial pass cannot supply the independence PRE-25 depends on, and a certification resting on a self-audit would be precisely the false green this corpus exists to prevent. Spawning was put to the Owner and explicitly authorised before dispatch. The five workstreams — 3A translation-contract subsystem, 3B CI gate and guardrail false-greens, 3C brand leaks (live + source, incl. Arabic/RTL), 3D documentation truthfulness (told to falsify this file's own claims), 3E rename blast radius — are all read-only, forbidden from mutating the live database, and briefed on the host's traps (no Docker, hanging Twig render tests, timing-out repo-wide greps, and the sibling worktree that poisons any drive-rooted scan). Results are **not yet in**; nothing here should be read as a Scan-3 outcome. Next: reconcile the five reports, then PRE-25. |

| 28 | **SCAN 3 COMPLETE — five independent agents, one P0 found and fixed, six findings open.** `5d519342d`. **Scan-3A found S3-P0-28 and proved it by execution:** the installer SQL and the PHP upgrade migration disagreed on whether an explicit contract definition or the neutralised legacy string wins. They differ on real shipped data (French: `de %s` vs `d'%s`), so every site installed from this branch would die on its first `sql_upgrade.php` with an uncaught `Conflicting target definition for lang_id 8` — after DDL, before the version bump, unrecoverable without hand-editing, and **not reproducible on this dev database**. Pre-existing since S1-P0-13; survived Scan 1, Scan 2, and the orchestrator's own self-review at Rev 26. Fixed with one precedence rule in both paths, three regression tests, and a negative control that reproduces the exact exception. Also fixed: S3-P2-29 (SQL could store an un-renderable two-placeholder value; now requires exactly one occurrence, proven on a disposable DB seeded with a deliberate double). **Scan-3B** hardened six gate assertions that checked a guard's *text* rather than its *effect* — including one where deleting `'8.2'` from the matrix switched the entire branding gate off with every leg green. **Scan-3D** found the PRE-09 ledger row still reading "IN PROGRESS — 13 items verified" beside its own evidence cell listing 18 and concluding complete, two stale `architecture.md` line refs, and an arithmetic error in this file's own Class-B accounting (corrected to 14 literals / 17 sites). **Scan-3C** found the sharpest critique of this session's architecture: `xl_product_name()` has two callers while `xlp()` and the `|xlp` filter — the very functions built to compose the product name — both bypass it, so Arabic sessions still get a Latin wordmark almost everywhere (S3-P1-32, open). **Scan-3E** corrected an orchestrator docblock that claimed test coverage it did not have, and contributed the Arabic substring trap (`ثقة` inside `منبثقة`). One 3E claim was **refuted** on verification. Gate: 12/12 artefacts, 123/123 manifest, 261 tests / 3,370 assertions, exit 0. **Certification remains NOT PASSED** — six findings open. |

| 29 | **PRE-09 / S3-P1-32 + S3-P1-34 + S3-P1-31 FIXED — VERIFIED. Three of the six open Scan-3 findings closed; three remain.** `f18f75080`, `59d5c14df`. **S3-P1-32** was Scan-3C's critique of this programme's own architecture and it was right: `xlp()` and the `\|xlp` filter now resolve through `xl_product_name()`, so every composed surface gets the session's product name rather than only the shell's browser tab. **The guard that should have caught it was itself the Scan-3B false-green shape** — `assertStringContainsString("getString('openemr_name')")` was *file-wide*, so it kept passing after the read moved to the neighbouring function in the same file. It is now scoped to each function's own body, with comments stripped through `token_get_all()` so a comment recording the fix can neither satisfy nor break the assertion. **It is also proven by execution with no database:** `xl_product_name()` memoises per request, so resolving once under one configured name, changing the name, then composing discriminates a routed `xlp()` (returns the first value) from a bypassing one (returns the second). PHPUnit's `RunInSeparateProcess` was tried first and **hangs on this host**; the probe therefore runs in-process, restores the bag in a `finally`, and its memo residue is documented rather than hidden. Negative control reverted both entry points: 3 failures / exit 1 including the executable one; sources restored to SHA-256 `61C40FC3…` / `C2305A40…` and the suite passed again. **One inherited claim corrected:** the resolver's new `catch (\RuntimeException)` was documented as protecting the *installer*. It does not — `Installer::insert_globals()` never reaches it because `saas_branding_product_name_ar` does not exist yet and the `has()` check returns first (verified cold: an unpopulated bag returns `''` without touching session or database). What it *does* protect is `sql_upgrade.php:551` / `sql_patch.php:74`, which require `globals.inc.php` **after** echoing and flushing — reproduced directly in a cold CLI process as `RuntimeException: Failed to start the session because headers have already been sent`. Uncaught, that aborts an upgrade mid-run: the S3-P0-28 shape. A database failure is explicitly **not** guarded, because `sqlQuery()` ends in `HelpfulDie()`'s `exit(1)` and no catch can reach it. **S3-P1-34** converts the questionnaire copyright disclaimer — Rev 23's one *mechanical* exclusion, since its literal was an argument to the browser-side `xl()`. Composed in PHP, emitted through `js_escape()`; round-tripped for Latin, Arabic and quote-bearing names. It has no `lang_constants` row, so nothing is orphaned. **S3-P1-31** guards both editor write paths, and its interesting half is the *classification*: `compose()` itself decides what is a pattern, measured against real data as 0 false positives across all 16 `%`-bearing catalogue constants and 0 false negatives across all 28 shipped contract targets. Negative control removing the update-path guard failed the named test, exit 1, then restored. Also initialises `$go`, which the surrounding block reads unconditionally and nothing ever set. Canonical gate: 12/12 artefacts, 123/123 manifest, **289 tests / 3,460 assertions**, exit 0; PHPCS 6/6; live login page HTTP 200 / 9,165 bytes, matching the recorded baseline. Live database mutated: NO. **Certification remains NOT PASSED** — S3-P1-33, S3-P2-35 and S3-P2-36 are open. |

| 30 | **A-01/A-02/A-03 reconciled into the record; SKY-F01 found, narrowed and FIXED; three open findings re-verified by execution.** `5202b0253` had landed before this session began and was ahead of the checkpoint - it is now recorded (Section 15C). **SKY-F01 is new**, and its correction history is preserved rather than rewritten: the original claim was that RTL non-Arabic sessions could receive an Arabic product name, tagline and logo alt text; `tagline()` ignoring its argument is deliberate and documented (REFUTED), `brandProductName`/`brandTagline` reach no template (LATENT), and the live half is the two logo accessible names, which render on every login. `02bcae75c` replaces the direction predicate with a language predicate. **The three open Scan-3 findings were re-verified rather than inherited** - S3-P1-33, S3-P2-35 and S3-P2-36 are all genuinely still open, each reproduced by execution (Section 15C). **S3-P1-27, S3-OBS-01 and S3-P1-30 were verified read-only by an independent agent**; all recorded numbers confirmed exactly, S3-OBS-01 confirmed reachable live, and S3-P1-27 confirmed an unapplied migration rather than a code defect. Live database mutated: NO. |

| 31 | **S3-P2-36 and the bulk of S3-P2-35 FIXED — VERIFIED; PRE-ORCH-02 fixed; S3-P1-33 and the SVG safeguard left as UNCOMMITTED work-in-progress after a quota stop.** `e203d5bdd` makes the locked Q77 deployed-theme check actually execute in CI - the environment now declares its obligation via `OPENEMR_DEPLOYED_THEMES_REQUIRED` instead of the guard inferring it from whether the directory happened to exist, CI builds the themes on one leg, and the negative controls are encoded as permanent tests rather than run once. `57e51286c` assigns manifest coverage by **ownership class** - 123 source + 21 deployed-immutable by recorded hash, 11 deployed font copies by equality-with-source, generated themes deferred to S3-P2-36, tenant/runtime output excluded by design. **S3-P2-35 is NOT fully closed**: `11-asset-manifest.md` and `12-release-verification.md` still describe the old re-issue discipline, and both are themselves manifest-covered so updating them requires re-issuing their entries in the same change. `d42a7d6d4` fixes **PRE-ORCH-02**, a sixth worktree at a path the S1-P2-14 exclusion rule did not match. **Four remediation agents were cut off mid-task by a session usage limit**; two finished and are the commits above, two did not - see §15D for exactly where each stopped and what a resuming session must do. Live database mutated: NO. |

| 32 | **ALL FOUR REMAINING CODE BLOCKERS CLOSED — VERIFIED. Scan-4A…4E dispatched.** `55738dc82` SKY-Q08 production-logo geometry safeguard (27/27 shipped SVGs pass, zero flagged; the 29 failures it arrived with were an interrupted agent's own negative-control injection left in the fixture builder, not a defect — recorded in §15E because "the tests were red" and "the code was wrong" are different claims). `57e51286c` + `c154215d9` **S3-P2-35 fully closed**, mechanism and governance: coverage by ownership class, 123 source / 21 deployed / 11 mirrored-by-equality, with the re-issue discipline rewritten because one edit can now oblige more than one entry. `e203d5bdd` + `cb685e1f9` **S3-P2-36 closed** — the environment now declares its obligation instead of the guard inferring it, negative controls are permanent tests, and four level-10 PHPStan findings were fixed in source with no baseline. `e16913d5b` + **ADR-BRAND-005** (written first, as SKY-Q11 required) **S3-P1-33 closed** — `setup.php` product literals 10 → 0, zero user-facing leaks, PRESERVE class re-derived on disk. `d42a7d6d4` **PRE-ORCH-02**, a sixth worktree that escaped the S1-P2-14 path rule. **One self-inflicted regression is recorded rather than quietly fixed**: adding the new drift gate shifted the script array that `BrandingCiContractTest` read positionally, turning a strengthened gate red. Canonical gate: **317 tests / 3,574 assertions, exit 0**. Owner decisions of the day recorded in §15E. Live database mutated: NO. **Certification still NOT PASSED** — PRE-25 not started, Scan-4 outstanding, two unlabelled P1 slots unresolved. |

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
CURRENT OBSERVED HEAD:          59d5c14df (S3-P1-31; before the Rev 29 checkpoint commit)
CURRENT GIT STATUS:             ?? .claude/ (observed after the Rev 22 commit; .claude/ stays untracked
                                after its atomic commit the tracked tree is expected to be clean)
                                sites/default/sqlconf.php is skip-worktree (flag S) — do not commit
SKYEAGLE MIGRATION STARTED:     NO
SKYEAGLE BRANDING CHANGES:      NONE
REPOSITORY WRITES THIS SESSION: S2-P1-22/23/24 composition workstream (contract schema v2, contract-set
                                loader, ten converted call sites, five retired overrides);
                                S2-P1-18 branding-health plane separation and CI gating; S1-P1-04
                                guardrail-scope cross-check; S2-P1-20 font-face distinctness contract
                                (finding refuted in part); all four P2 repairs including
                                Config\ModulePaths; documentation corrections; this checkpoint.
                                (S2-P1-22/23/24 were first implemented WITHOUT the carry-forward,
                                proven to regress 10–34 locales, and reverted exactly; the committed
                                version carries every translation forward. Both are recorded.)
CURRENT PROGRAMME:              PRE-SKYEAGLE three-scan audit/remediation/certification
FINAL TARGET:                   PRE-SKYEAGLE CERTIFICATION: PASS
                                SAFE TO START SKYEAGLE MIGRATION: YES
```

**Pre-brand remediation commits (never conflate these with the scan baseline):**

| Commit | Task | What it did |
|---|---|---|
| `45e9eb4f3` | PRE-09 (1st item) | `prebrand: re-issue four stale brand-manifest hashes and record why` — restored the release gate from RED (119/123, exit 1) to GREEN (123/123, exit 0). Five entries re-issued (the four drifted documents plus `12-release-verification.md`, which was edited to record the reason and is itself manifest-covered). Entries **re-issued, never deleted**, per RB-25. No SkyEagle change. |
| `aebcfdfc5` | PRE-09 / S1-P0-01 | Added PR-23…PR-28 coverage for the eight previously unrecorded core branding edits. Independently verified during continuation; grouped records are intentional. |
| `26c32fcb3` | PRE-09 / S1-P0-01 | Corrected the stale PR-30 reference in the patch-record reconciliation table. Independently verified during continuation. |
| `566b14ea6` | PRE-09 / S1-P0-09 | `prebrand(S1-P0-09): connect tenant tokens to live consumers` — canonical identity-neutral custom properties, legacy aliases, safe live consumers, and automated light/dark regression coverage. Live proof and exact restoration recorded in §5 and §16. |
| `ba0078c62` | PRE-09 / S1-P0-13 + S2-P0-21 | Recorded the selected neutral translation architecture and rejected unsafe alternatives before implementation. |
| `948e4a6d1` | PRE-09 / S1-P0-13 | Introduced the strict one-placeholder product-context renderer and changed both database-upgrade title consumers to compose `openemr_name` before exactly one HTML escape. |
| `02671f0c9` | PRE-09 / S2-P0-21 | Added the checked-in 28-language contract, deterministic SQL supplement, installer ordering, and release-prep regeneration. |
| `2baf7322a` | PRE-09 / S1-P0-13 + S2-P0-21 | Added the transaction-journalled stable-ID migration/rollback engine, operator command, fresh/upgrade journal schema, automatic upgrade invocation, and migration/schema matrix tests. |
| `597276b09` | PRE-09 / S1-P1-03 + PRE-11 | Added the canonical fail-closed `composer branding-ci` aggregate, wired it into the existing `Isolated Tests` workflow's PHP 8.2 leg, added CI-contract regression tests, and aligned the stale database-upgrade core-string assertion with the already-verified neutral title contract. |
| `ff6e35b4f` | PRE-09 / S1-P1-03 | Added the canonical local/CI runbook and PR-33 patch record; the authoritative production/delivery patch inventory is now 51 files. |
| `77d2b3e12` | PRE-09 / S1-P1-15 | Added neutral versioned backup names, verified legacy compatibility, parsed-timestamp mixed retention, fail-closed scan/selection/deletion logic, and explicit command reporting. |
| `8eb4ea7f8` | PRE-09 / S1-P1-15 | Added the destructive-safety/migration matrix using only validated unique temporary directories and wired it into the canonical branding CI gate. |
| `64d2ba23c` | PRE-09 / S1-P1-15 | Added the canonical backup naming/retention/migration/rollback contract, operator runbook section and PR-34 governance record. |
| `0af1ce174` | PRE-09 / S1-P1-17 | Preserved all 11 intended tenant overrides, added the separate 1.5:1 disabled-state product rule, locked light/dark component opacity, and reconciled source, tests and documentation. |
| `2b801e668` | PRE-09 / S1-P1-02 | Retired exactly two dead OAuth English overrides by exact managed value, preserved customized/non-English definitions and the three live Zend overrides, removed stale consumer metadata, and added fresh/upgrade/existing-tenant regression coverage without disturbing the neutral database-upgrade contract. |
| `b400546a2` | PRE-09 / S1-P1-05 | Re-derived 38 WCAG pairs from machine evidence, synchronized the 35 PASS / 3 ADVISORY / 0 FAIL prose and all four missing `borderStrong` rows, added a canonical drift guard, and re-issued the two affected manifest hashes with an explicit Rev 6 reason. |
| `88ff34289` | PRE-09 / S1-P1-06 | Reconciled all three authoritative D-8 records with shipped materialisation behavior, retained D-8 as OPEN, corrected the associated risk/closure counts, and added a canonical regression guard against false closure. |
| `723170df7` | PRE-09 / S1-P1-10 | Removed the false Amiri/mPDF registration claim, kept D-9 and the accepted pilot limitation explicit, corrected stale PDF evidence, added a truthfulness guard, and re-issued exactly two covered-document hashes. |
| `29be1fcd5` | PRE-09 / S1-P1-11 | Classified operational identity, neutralized only safe reusable labels/stale examples, preserved live/historical/compatibility identifiers, and added a canonical regression guard. |
| `177d5dc97` | PRE-09 / S2-P1-26 (Class A) | 20 schema-v2 `legacy_keys` contracts and all 22 call sites, including the login page. Added `MissingIdentityPolicy` after discovering that the installer SQL skipped a translation lacking the brand literal while the PHP upgrade migration threw — a fresh-install-vs-upgrade divergence that would have aborted real upgrades on the two contracts shipped at Rev 22. |
| `6da352e9a` | PRE-09 / S2-P1-26 (Class B) | Re-derived the leak surface exactly (20/22 Class A, 22/25 Class B, 49 dead), added the PHP `xlp()` helper, converted all 15 safely-convertible uncatalogued literals across 18 sites, locked the Foundation/ONC/community preserve list by test, and raised the gate's process-timeout budget after a passing suite was killed by Composer's 300 s default. |
| `6edc03b8b` | PRE-09 / S2-P1-22 + S2-P1-23 + S2-P1-24 (juxtaposition half) | Added contract schema v2 (`derive_from` + placement), the contract-**set** loader, SQL carry-forward for both derivation kinds, and multi-contract iteration in `sql_upgrade.php` / the migration command / the release-prep mutator. Converted ten call sites to compose one translatable unit via the new `xlp` filter, retired the last three English overrides, and proved the whole supplement twice on a disposable database. v1 contract untouched; live database never written to. |
| `97f6952cf` | PRE-09 / S1-P2-07 + S1-P2-12 + S1-P2-14 + S1-P2-16 | Made `Config\ModulePaths` the single owner of the module directory name and guarded the three consumers that cannot share a PHP constant; corrected the CSS release counts to 18/4, the worktree hygiene rule to four (naming the un-excludable sibling), and the console-command count to six with its blast-radius consequence. Also corrected the inherited claim that `webpack.themes.js` references the module directory — it does not. |
| `2df9b5eb1` | PRE-09 / S1-P1-04 + S2-P1-20 | Added the independent guardrail-scope cross-check (module PSR-4 prefix vs all four rule constants, both directions), and the font-face distinctness contract (a shared face must be variable; a static family must be byte-distinct). Refuted S2-P1-20's rendering claim with a WOFF2 table-directory decode and recorded it as Correction K. No production source, font binary or asset manifest changed. |
| `1474263b4` | PRE-09 / S2-P1-18 | Separated the served branding plane from the unserved static-artefact plane, made only served-plane findings fail a health probe, added two served-plane cases the old model could not see, read the overlay through the runtime parser, gated the health suite in `composer branding-ci`, and corrected the three records that had drifted on the RB-11 reading. |
| `f18f75080` | PRE-09 / S3-P1-32 + S3-P1-34 | Routed `xlp()` and the `\|xlp` Twig filter through `xl_product_name()`; rescoped the file-wide guard to each function's own body with comments stripped; added an executable, database-free discrimination between routing and bypass; converted the questionnaire copyright disclaimer across the PHP/JS boundary via `js_escape()`; and corrected the resolver's own docblock about which caller its `RuntimeException` guard actually protects. |
| `59d5c14df` | PRE-09 / S3-P1-31 | Added the product-context placeholder guard to both write paths of `interface/language/lang_definition.php`, classifying with `compose()` itself rather than a substring test (0 false positives over the 16 `%`-bearing catalogue constants, 0 false negatives over the 28 contract targets), reported rejections by constant name, and initialised `$go`. |

Add a row for every further remediation commit.

**Live stack state at checkpoint time (re-derived, not inherited):**

```text
MariaDB 127.0.0.1:3306   RESPONDING       (Rev 8 preflight query exit 0)
Apache  localhost:8300   RESPONDING       (Rev 8 login HTTP 200, 9165 bytes)
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
Those landed-work verification steps and the next eleven PRE-09 repairs are now complete; see revisions 6…18.
**Exact next incomplete item (updated at Rev 32):** all four remaining code blockers are FIXED —
VERIFIED (§15E): SKY-Q08 `55738dc82`, S3-P2-35 `57e51286c`+`c154215d9`, S3-P2-36 `e203d5bdd`+`cb685e1f9`,
S3-P1-33 `e16913d5b` with ADR-BRAND-005, plus PRE-ORCH-02 `d42a7d6d4`. **The next incomplete work is
PRE-25**, gated on Scan-4A…4E returning and on resolving the two unlabelled P1 slots. Superseded
pointer from Rev 30 follows, kept because the three findings it names are now closed and the reader
should see what moved:

**Superseded (Rev 30):** SKY-F01 is FIXED — VERIFIED (`02bcae75c`), and
A-01/A-02/A-03 (`5202b0253`) are now reconciled into the record — see §15C for both, and for the
independent read-only verification of S3-P1-27, S3-OBS-01 and S3-P1-30. **The same three Scan-3
findings remain open, and all three were re-verified by execution on 2026-08-25 rather than
inherited** (§15C), in this order:

1. **S3-P1-33 — `setup.php` is a mixed-brand installer.** Not a mechanical conversion, and the reason
   matters: `setup.php` runs *before the database exists*, so `xl()`, `xlp()` and every branding global
   are unavailable there. It already carries **10 hardcoded `Thiqa` literals** (`:145`, `:160`, `:356`,
   `:452`, `:522`, `:524`, `:526`, `:976`, `:1530`, `:1747`) alongside roughly 20 surviving `OpenEMR`
   ones, so the same page currently says both `Thiqa Setup Tool` and
   `Congratulations! OpenEMR is now installed.` Making it merely *consistent* by hardcoding the tenant
   name into ~30 places is the wrong repair for a programme whose whole purpose is to make the next
   rename a single edit. The real decision is where a pre-database product identity should live, and
   that is an architecture choice deserving its own recorded design step before implementation —
   the same discipline `ba0078c62` used for the translation architecture.
2. **S3-P2-35** — deployed assets (logos, favicon, fonts under `public/`, compiled `public/themes/*.css`)
   sit outside the 123-entry manifest, so they can be replaced while the gate prints `123/123 verified`.
   Governance: closing it changes the documented re-issue discipline.
3. **S3-P2-36** — the Q77 deployed-theme check calls `markTestSkipped` when `public/themes/` is absent,
   CI has no webpack step, and the gate passes no `--fail-on-skipped`, so a locked decision's check runs
   in no CI job while reporting green. CI infrastructure.

**PRE-25 certification cannot be attempted while these are open.** Separately, `S3-OBS-01` (admin.php
served without authentication) is outside branding and needs its own review.

**Recorded, not fixed, from the Rev 29 work:** `js_escape()` is `json_encode()` with no `JSON_HEX_TAG`,
so a product name containing `</script>` would break out of the surrounding block. This is pre-existing
and identical at `interface/main/tabs/main.php`'s `js_escape(xl_product_name())`; the value is
admin-configured, not user input. Noted so it is not discovered later and mistaken for new.

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
| PRE-09 | Scan-1 FIX-NOW remediation | orchestrator | **W** | **COMPLETE — 18 items verified** (one sub-item, S2-P1-24's Arabic logo variant, is blocked on an approved asset that does not exist) | ✅ S2-P1-25 manifest gate restored (`45e9eb4f3`). ✅ S1-P0-01 inventory invariant fixed (`aebcfdfc5`, `26c32fcb3`). ✅ S1-P0-09 token-consumer contract fixed and live-verified (`566b14ea6`). ✅ S1-P0-13 neutral translation migration/rollback fixed (`948e4a6d1`, `2baf7322a`). ✅ S2-P0-21 install/rebuild/release durability fixed (`02671f0c9`, `2baf7322a`). ✅ S1-P1-03 deterministic CI wiring and false-green protection fixed (`597276b09`, `ff6e35b4f`). ✅ S1-P1-15 neutral mixed-family backup retention fixed (`77d2b3e12`, `8eb4ea7f8`, `64d2ba23c`). ✅ S1-P1-17 disabled-token product contract fixed (`0af1ce174`). ✅ S1-P1-02 dead overrides retired safely (`2b801e668`). ✅ S1-P1-05 WCAG evidence synchronized (`b400546a2`). ✅ S1-P1-06 D-8 status reconciled while retaining the open dependency (`88ff34289`). ✅ S1-P1-10 false PDF-font capability claim removed (`723170df7`). ✅ S1-P1-11 operational identity safely classified/neutralized (`29be1fcd5`). ✅ S2-P1-18 branding health now measures the served state and is gated in CI (`1474263b4`). ✅ S1-P1-04 guardrail scope cross-checked against the real module, and S2-P1-20 refuted-in-part with its real gap closed (`2df9b5eb1`). ✅ All four P2 findings closed (`97f6952cf`). ✅ S2-P1-22 + S2-P1-23 + the juxtaposition half of S2-P1-24 closed together by the multi-key contract subsystem and the `xlp` composition filter (`6edc03b8b`). ✅ S2-P1-26 Class B — the uncatalogued leak class no override could reach — converted, with the Foundation/ONC preserve list locked by test (`6da352e9a`). ✅ S2-P1-26 Class A: 20 legacy contracts + 22 call sites, after fixing a latent install-vs-upgrade divergence that would have aborted real upgrades (`177d5dc97`). ✅ S2-P1-24 text half: the shell selects the Arabic product name by language. **PRE-09 IS COMPLETE** apart from S2-P1-24's Arabic logo variant, which is blocked on an approved asset that does not exist. ✅ Scan-3 remediation, Rev 29: S3-P1-32 + S3-P1-34 (`f18f75080`) and S3-P1-31 (`59d5c14df`). **Rev 32 (2026-08-25): PRE-09 IS NOW COMPLETE.** The three items this row listed as next are all closed and verified — S3-P1-33 (`e16913d5b`, with ADR-BRAND-005 recorded first as SKY-Q11 required), S3-P2-35 (`57e51286c` mechanism + `c154215d9` governance) and S3-P2-36 (`e203d5bdd` + `cb685e1f9`) — along with the Owner-directed SKY-Q08 logo-geometry safeguard (`55738dc82`) and PRE-ORCH-02 (`d42a7d6d4`). See §15E. **Next: PRE-25**, gated on Scan-4A…4E returning and on resolving the two unlabelled P1 slots. |
| PRE-10 | Scan-2A Guardrail execution proof | Agent 2A → orchestrator | R | **AGENT FAILED (6 empty returns); CLAIM SUBSEQUENTLY PROVEN BY ORCHESTRATOR** | Agent burned ~117k tokens / 63 tool calls with zero findings — **do not re-dispatch it.** Orchestrator proved the inert-rule behaviour directly; see §16 PRE-10. |
| PRE-11 | Scan-2B Test-harness truthfulness | Agent 2B → orchestrator | R/W | **DONE — VERIFIED** | Deliberately nonexistent `--filter SkyEagleBranding` executed 0 tests: exit 0 without the supported guard; exit 1 with `--fail-on-empty-test-suite`. The canonical gate includes that flag plus fail-on-incomplete/risky, and its contract test prevents removal. See §16 PRE-11. |
| PRE-12 | Scan-2C Generator/theme reproducibility | Agent 2C | R | **DONE** | §15 SCAN2C |
| PRE-13 | Scan-2D Manifest/asset pipeline | Agent 2D | R | **DONE** | §15 SCAN2D |
| PRE-14 | Scan-2E Translation runtime forensics | Agent 2E | R | **DONE** + addendum | §15 SCAN2E |
| PRE-15 | Scan-2F Runtime surfaces | Agent 2F | R | **DONE** (two runs) | §15 SCAN2F |
| PRE-16 | Scan-2G Materialisation / tenant safety | orchestrator continuation | R/W (reversible runtime proof) | **DONE — VERIFIED; LIVE STATE RESTORED** | §15 SCAN2G and §16 continuation evidence |
| PRE-17 | Scan-2H Telemetry / network egress | Agent 2H | R | **DONE** | §15 SCAN2H |
| PRE-18…24 | Scan-3A…3E adversarial red-team | fresh agents | R | **COMPLETE (2026-08-24) — 5 of 5 returned; 1 P0 found and fixed** | All five reported. Scan-3A found **S3-P0-28**, a fresh-install-versus-upgrade precedence divergence that would have wedged every upgrade uncatchably; fixed and regression-pinned. 3B hardened six gate assertions; 3C found the composition architecture's real reach gap; 3D found three documentation defects including a stale headline status; 3E found one live-state gap and corrected an orchestrator docblock. **Rev 29 closed S3-P1-31, S3-P1-32 and S3-P1-34; 4 remain unclosed** — 1 accepted trade, 1 P1 needing a design step, 2 governance/CI P2s. See §15B. Originally: | Five independent read-only agents, launched only after the Owner explicitly authorised spawning them. Scoped to five attack surfaces rather than the original seven letters: **3A** translation-contract subsystem (orphaning, page-fatal patterns, install-vs-upgrade divergence, rollback); **3B** CI gate and guardrail false-greens; **3C** brand leaks on live and source surfaces incl. Arabic/RTL; **3D** documentation truthfulness (falsify the corpus's own claims, including this file's); **3E** rename blast radius and silent breakage. Each was told to falsify rather than inherit, given the host constraints (no Docker, Twig render tests hang, scoped searches, sibling-worktree exclusion), and bound to read-only with no live-DB mutation. |
| PRE-25 | Final reconciliation / certification | orchestrator | R | **VERDICT: FAIL (§22.1).** Scan-4 has since returned, found a fifth P0 and 40 further findings, and the §21 reconciliation predates all of it. §22 carries the Scan-4 register; §23 carries the remediation of its HIGH findings and of the guard defects. PRE-25 must be re-run against the corrected ledger. Superseded row follows: **RECONCILIATION COMPLETE (§21); VERDICT PENDING** | Every documented code blocker closed at Rev 32 (§15E). Three inputs still outstanding before it can begin: Scan-4A…4E (dispatched at `e16913d5b`), the full PHPStan run (exit code alone is not proof on this host — grep for `Internal error` / `Result is incomplete`), and the two unlabelled P1 slots at the S2-P1-19 / S2-P1-21 numbering gap, which must be resolved from Scan-2's register rather than filled with invented findings. |

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
SCAN 2:  IN PROGRESS — 7 of 8 workstreams are now complete (2B, 2C, 2D, 2E, 2F, 2G, 2H).
         2A failed (6 empty returns) but its target claim was proven directly by the orchestrator.
         2B's missing zero-match-filter experiment was completed during the S1-P1-03 repair.
SCAN 3:  COMPLETE 2026-08-24 — five independent fresh agents, all returned, results
         reconciled in §15B. Independence was the point: the orchestrator performed the
         remediation for 20+ findings, so an orchestrator-run Scan 3 could not supply the
         property PRE-25 depends on. Spawning was authorised by the Owner before dispatch.
         IT PAID FOR ITSELF: Scan-3A found a P0 (S3-P0-28) that would have wedged every
         upgrade of every site installed from this branch, uncatchably, mid-run — a defect
         that had survived the entire Scan-1/Scan-2 programme and the orchestrator's own
         self-review, and that does not reproduce on this dev database.
         REMEDIATION AT REV 29: S3-P1-31, S3-P1-32 and S3-P1-34 are FIXED — VERIFIED.
         4 findings remain unclosed: 1 accepted trade (S3-P1-30, quantified),
         1 P1 needing a design step (S3-P1-33), and 2 governance/CI-infrastructure
         P2s (S3-P2-35, S3-P2-36).

KNOWN OPEN P0 FINDINGS:  NONE. (S3-P0-28 was found by Scan-3A on 2026-08-24 and FIXED the
                         same day — see §15B. It is the only P0 raised after Scan 1.)

SCAN-3 REGISTER:    S3-P0-28  translation precedence divergence      FIXED — VERIFIED
                    S3-P2-29  SQL could store a 2-placeholder value  FIXED — VERIFIED
                    S3-P1-30  `skip` costs Arabic 8 strings          ACCEPTED, quantified
                    S3-P1-31  language editor has no `%s` guard      FIXED — VERIFIED (59d5c14df)
                    S3-P1-32  xlp()/|xlp bypass the Arabic resolver  FIXED — VERIFIED (f18f75080)
                    S3-P1-33  setup.php is a mixed-brand installer   FIXED — VERIFIED (e16913d5b,
                                                                     ADR-BRAND-005 written first;
                                                                     literals 10 → 0) (§15E)
                    S3-P1-34  questionnaire disclaimer names OpenEMR FIXED — VERIFIED (f18f75080)
                    S3-P2-35  deployed assets outside the manifest   FIXED — VERIFIED (57e51286c
                                                                     mechanism, c154215d9 the
                                                                     governance half) (§15E)
                    S3-P2-36  Q77 theme check skips in CI            FIXED — VERIFIED (e203d5bdd,
                                                                     cb685e1f9) (§15E)
                    SKY-Q08   production SVG geometry safeguard      FIXED — VERIFIED (55738dc82);
                              (Owner-directed PRE deliverable)       27/27 shipped SVGs pass (§15E)
                    PRE-ORCH-02 a 6th worktree escaped the S1-P2-14
                              path exclusion                         FIXED — VERIFIED (d42a7d6d4)
                    S3-OBS-01 admin.php renders its site table with
                              no authentication — OUTSIDE branding,
                              recorded so it is not dropped          CONFIRMED LIVE 2026-08-25;
                                                                     NOT FIXED by Owner decision;
                                                                     STILL NEEDS OWN REVIEW

POST-SCAN-3 REGISTER (2026-08-25, see §15C):
                    A-01      shell user menu juxtaposed `حول Thiqa`  FIXED — VERIFIED (5202b0253)
                    A-02      questionnaire theme help-text leak      FIXED — VERIFIED (5202b0253)
                    A-03      leak surface was a hand-written list    FIXED — VERIFIED (5202b0253)
                    SKY-F01   Arabic branding selected by DIRECTION
                              not LANGUAGE; live on the login logos'
                              accessible names, latent elsewhere,
                              and REFUTED for the tagline             FIXED — VERIFIED (02bcae75c)
                    S3-P1-27  neutral keys absent from THIS database  OPERATIONAL — verified an
                                                                     unapplied migration, not a
                                                                     code defect; deliberately
                                                                     not run (Owner, 2026-08-25)
REGISTERS:          P0 5 total (0 open, 5 fixed) · P1 2 unlabelled slots only · P2 0 open (4 fixed)
                    [S4D-05, corrected 2026-08-25: this line long read "P0 4 total". PRE-25 §21.1
                    re-derived five, the fifth being S3-P0-28. Scan 4 then added S4-P0-40, so the
                    running total is SIX P0 findings, none open. See §22.]
                    FIXED so far: S1-P0-01; S1-P0-09; S1-P0-13; S1-P1-02; S1-P1-03; S1-P1-04; S1-P1-05; S1-P1-06; S1-P1-10; S1-P1-11; S1-P1-15; S1-P1-17; S2-P0-21; S2-P1-18; S2-P1-22; S2-P1-23; S2-P1-25; S2-P1-26.
                    PARTLY FIXED: S2-P1-24 — juxtaposition and text-variant halves closed;
                    only the Arabic LOGO variant remains, blocked on an approved asset
                    that does not exist (§13, Entity.md, KG-05).
                    REFUTED IN PART, REMAINDER FIXED: S2-P1-20 (see Correction K).
                    NEW since: S2-P1-26 (English leak surface + uncatalogued leak class) - now itself FIXED at Rev 23/24.
                    P1 moved 17 -> 16 (S2-P1-25 fix) -> 17 (S2-P1-26 new) -> 16 (S1-P1-03 fix)
                    -> 15 (S1-P1-15 fix) -> 14 (S1-P1-17 fix) -> 13 (S1-P1-02 fix)
                    -> 12 (S1-P1-05 fix) -> 11 (S1-P1-06 fix) -> 10 (S1-P1-10 fix)
                    -> 9 (S1-P1-11 fix) -> 8 (S2-P1-18 fix) -> 7 (S1-P1-04 fix)
                    -> 6 (S2-P1-20 refuted-in-part + remainder fixed)
                    -> 5 (S2-P1-23 fix) -> 4 (S2-P1-22 fix) -> 3 (S2-P1-26 fix)
                    -> 2 (S2-P1-24 text half; only its logo sub-item remains, asset-blocked).
                    P2 all fixed: S1-P2-07; S1-P2-12; S1-P2-14; S1-P2-16.
                    STILL OPEN (P1): two unlabelled slots only. Every documented P1 is
                    closed except S2-P1-24's Arabic LOGO variant, which is asset-blocked.
                    S2-P1-22/23/24 were ONE workstream, fixed across Revs 22-25: ten call
                    sites compose a single translatable unit, schema-v2 derive_from and
                    legacy neutralisation carry every existing translation forward so no
                    locale lost one, and the shell now selects the Arabic product name by
                    language (not by direction — four locales are RTL and only one is
                    Arabic).
                    NOTE ON THE COUNT — **RESOLVED AT PRE-25, 2026-08-25. The two
                    "unlabelled P1 slots" DO NOT EXIST.** They were a bookkeeping
                    artefact, not lost findings, and the note that created them was
                    itself wrong about where they sat.

                    Method: every finding ID in this checkpoint was enumerated and the
                    sequence reconstructed. The counter is **global and sequential across
                    scans AND severities**, not per-severity — which is the assumption
                    that produced the phantom slots. 34 IDs are assigned across the range
                    01..36. The only numbers never issued are **08 and 19**.

                    **S2-P1-21 was never a gap.** Number 21 is `S2-P0-21` (the RB-01
                    remediation not surviving a database rebuild) — a P0, FIXED —
                    VERIFIED at Rev 10. Reading the sequence as P1-only turned an
                    existing P0 into an imaginary missing P1.

                    Findings 08 and 19 were never issued at all: no section, no commit,
                    no mention in `RebrandingBugs.md`, `rebranding.md` or
                    `RebrandingPlan.md`, and nothing in `git log --all -S` beyond this
                    checkpoint's own notes about the gap. Concurrent agents assigning IDs
                    from a shared counter skipped two numbers. Nothing is missing.

                    Re-derived P1 totals: **22 documented P1 findings**
                    (S1: 02,03,04,05,06,10,11,15,17 · S2: 18,20,22,23,24,25,26 ·
                    S3: 27,30,31,32,33,34). Of these, 19 are FIXED — VERIFIED, and the
                    three that are not closed are each closed *as a disposition*:
                    S2-P1-24's Arabic LOGO variant (asset-blocked, KG-05/SKY-Q07),
                    S3-P1-27 (OPERATIONAL — an unapplied migration, not a code defect),
                    and S3-P1-30 (ACCEPTED, quantified at 22 contracts / 8 Arabic
                    strings). **No P1 is open as unaddressed work.**

STATE AT REV 32 (2026-08-25): every documented CODE blocker is closed and verified.
                    What remains is verification and reconciliation, not construction:
                      · Scan-4A…4E — five independent adversarial agents dispatched at e16913d5b
                      · PHPStan full run — exit code alone is NOT proof on this host
                      · Two unlabelled P1 slots — RESOLVED at PRE-25: they do not exist
                      · PRE-25 — NOT STARTED
                      · Push and the local-disk certification copy — deferred by Owner
                        decision until PRE-25 passes

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

**Status: FIXED — VERIFIED (continuation Rev 6).** The historical finding below is retained as evidence.

**Continuation verification and residual repair (2026-08-24):**

```yaml
TASK/FINDING ID: S1-P0-01
Previous status: OPEN; landed documentation commits were verification pending
Current status: FIXED — VERIFIED
What changed: Verified all eight files are changed from the fork baseline and represented by PR-23…PR-28; corrected only the residual six-file V-09 scope in the active plan/evidence documents.
Files changed: docs/RebrandingPlan.md; docs/branding/remaining-dependencies.md; docs/branding/adr/patch-records.md; this checkpoint
Repair commits verified: aebcfdfc5da63686901c1cf403b851afb01e0e40; 26c32fcb3c027702d9b6fe167017487469a19e5d
Tests/commands executed: per-file baseline diffs and patch-record filename checks for all eight; targeted V-09 text inspection; git merge-tree --write-tree HEAD upstream/master
Exact exit codes: eight git diff checks each 1 meaning changed; documentation searches 0; merge-tree 1 meaning conflicts present
Tests and assertions executed: 8/8 formerly undocumented files changed; 8/8 represented; authoritative inventory 33 files; 47 merge conflict records; 1 recorded-file conflict; 0/8 newly recorded files conflict
Runtime evidence: upstream/master 6cb9c0b91728190f30e09c03c026c827e9430579; merge preview 2026-08-24T06:41:14.5090816Z–06:41:17.9069063Z; 3.398 s; no timeout; working tree not mutated
Independent verification: actual eight-file delta == PR-23…PR-28 substance; V-09 now explicitly scopes the full authoritative 33-file inventory.
Rollback method: Branch at the parent commit and cherry-pick accepted changes; do not use destructive reset/revert.
Remaining risks: Current upstream integration has 47 conflict records, including EncounterService.php inside the recorded set; this is classified release-integration work, not missing patch-record coverage.
Next task at Rev 6: PRE-09 / S1-P0-09 and PRE-16 Tier-2 runtime proof before token-overlay repair.
```

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
- **Runtime proof required?** YES — initial failure and repaired light/dark chain both executed; see §15 SCAN2G and §16.
- **Blocks SkyEagle?** NO — **FIXED — VERIFIED** in continuation Rev 8. Other PRE-SKYEAGLE blockers remain.

**Status: FIXED — VERIFIED (continuation Rev 8).** The historical failure evidence above is retained.

```yaml
TASK/FINDING ID: S1-P0-09
Previous status: CONFIRMED, REPAIR PENDING
Current status: FIXED — VERIFIED; LIVE STATE RESTORED
What changed: Made bare CSS custom properties the identity-neutral Tier-1/Tier-2 contract; retained --thiqa-* and --oe-* aliases; moved real links, buttons, focus rings, tabs and document links onto canonical vars with Sass fallbacks.
Files changed: tools/branding/src/ColorToken.php; tools/branding/src/CssVariablesRenderer.php; generated _css-variables.scss preview/deployed copies; interface/themes/thiqa/_overrides.scss; TokenGeneratorIsolatedTest.php; this checkpoint
Repair commit: 566b14ea68dd106fe0e38d5d77f2df46b5da25a8
Automated evidence: targeted TokenGeneratorIsolatedTest OK (34 tests, 152 assertions); expanded branding regression OK (239 tests, 987 assertions); PHPCS 3/3 files exit 0; generator check 12/12 artefacts exit 0; webpack built all 8 Thiqa outputs exit 0 with 187 warnings and no errors.
Live light evidence: revision-1 overlay link present; --interactive-primary-default #0B376E; --link-default #5A2CA0; real .btn-primary background/border changed rgb(196, 63, 46) -> rgb(11, 55, 110).
Live dark evidence: style_dark.css loaded; same isolated revision-1 endpoint; --interactive-primary-default #64D8CB; --link-default #FFD166; real .btn-primary background/border changed to rgb(100, 216, 203).
Acceptance chain: allowed validated token change -> materialised CSS -> page link -> canonical var() consumer -> real browser computed-style difference: PASS in light and dark.
Safety evidence: Tenant scoping and the no-site-parameter endpoint contract were not changed; TokenValidator/WCAG code was not changed and its regression set passed; an empty overlay falls back to compiled canonical defaults.
Exact exit codes: generator pre-regeneration drift control 3; generator post-regeneration check 0; targeted PHPUnit 0; expanded PHPUnit 0; PHPCS 0; webpack build 0; materialise 0; applied verify 0; DB/file restore 0; restored verify 1 (expected inherited preflight state).
Non-pass attempts: one expanded PHPUnit invocation exceeded its 30-second capture window and one mistyped generator path exited 1; neither was called a pass, and both were rerun to complete exit-0 results.
Independent restoration: all 7 saas_branding_* globals and css_header match preflight HEX values; light overlay SHA256 43015D055A6359698608B8FF99030C5D9E79CED2A4CDB16B1906C2C521EA78E8; dark F096670815C7C76D7F9C47674970299B950984AEDC09EFBBC36A8110D480F4B4; both timestamps restored to 2026-08-10T18:50:40Z; overlay links absent; style_light.css loaded; canonical/legacy primary #c43f2e; button rgb(196, 63, 46); browser cache override disabled and temporary tab closed.
Warnings disclosed: CLI session-file permission warnings persisted but did not change true exit codes; webpack emitted 187 existing/deprecation/performance warnings; PHPUnit could not write its optional result cache in the sandbox.
Rollback method: Branch at the parent of 566b14ea6 and cherry-pick only accepted later commits; no destructive reset/revert. Runtime rollback already completed by exact DB/file/theme restoration.
Remaining risks: None specific to S1-P0-09 acceptance; certification remains blocked by the other open PRE tasks.
Next incomplete task: PRE-09 / S1-P1-03 deterministic branding-gate CI wiring.
```

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

**Status: FIXED — VERIFIED (continuation Rev 10).** The durable key is `%s Database Upgrade`; the product
identity comes only from `openemr_name`. `ProductContextTranslation` accepts exactly one `%s` or `%1$s`,
supports literal `%%`, rejects unsafe/multiple/missing placeholders, and returns an unescaped composed value.
Both `<title>` and `<h2>` then apply `text()` exactly once. English fallback, Spanish, Arabic, Hebrew,
special/HTML-sensitive names, repeated rendering, literal percent, injection rejection and double-escape
prevention are automated.

Forward migration is transactional and handles old-only, target-only, both, neither, partial/different language
sets, identical state, explicit source/target conflicts and repeated runs. Exact binary names are resolved once;
duplicates block the operation, and all writes use the resolved stable `cons_id` plus `lang_id`. Source rows are
never deleted. The InnoDB journal records exact before/after target state and the contract hash. Rollback refuses
drift, removes only rows introduced by the migration, restores an absent or partial target exactly, is idempotent,
and supports forward -> rollback -> forward. Orphans, duplicate pairs and unexplained count changes are hard
failures inside the transaction. Operator rollback is available through
`openemr:translation-catalogue-migrate --rollback`; normal SQL upgrade invokes forward automatically.

Evidence: migration matrix 15 tests / 39 assertions / exit 0; renderer/contract/install/release/migration/schema
combined suite 41 tests / 134 assertions / exit 0 (command wall 19.433 s; PHPUnit 3.983 s); PHPCS 18 files /
exit 0 (command wall 5.877 s; PHPCS 2.89 s). One earlier combined run was **NOT PASS** (41 tests / 131
assertions / exit 1) because it caught a stale generated supplement; regeneration was corrected and the full
suite was rerun green. An earlier broad combined capture was truncated and remains **UNKNOWN — NOT PASS**.
The first Windows mutator run was also **NOT PASS** (6 tests: 3 errors, 1 failure) because the stub used Unix
`true`; the harness now uses `PHP_BINARY` and reran at 6 tests / 13 assertions / exit 0.

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

**Status: FIXED — VERIFIED (continuation Rev 10).** The authoritative source is
`contrib/util/language_translations/contracts/database-upgrade.json`, with all 28 observed language IDs including
RTL 7/22/37. Its deterministic generated supplement is separate from `currentLanguage_utf8.sql`, loads
immediately after either the local or online installer translation source, and is regenerated by
`TranslationFileCopyFromPriorRelMutator` after every prior-release copy. Fresh install, upgrade and patch SQL
paths all create the migration journal; schema marker and `version.php` are synchronized at 542.

Final disposable MariaDB proof used only `openemr_prebrand_translation_20260824_final`. The final supplement
was applied twice (exit 0, 8.980 s): target constants 1; target definitions 28; RTL definitions 3; orphans 0;
duplicate pairs 0. The exact schema name was verified before deletion; drop exited 0 and a follow-up count was
0. Installer ordering tests: 2 tests / 6 assertions / exit 0. Contract/generator tests: 4 tests / 42 assertions /
exit 0. Release-copy/stale-regeneration tests: 6 tests / 13 assertions / exit 0. Schema/rebuild tests: 3 tests /
17 assertions / exit 0. Live database touched: **NO (read-only SELECTs only)**. Live database restored:
**NOT APPLICABLE**. Disposable database restored: **YES — dropped and confirmed absent**.

Repair commits: `948e4a6d145e5d76ce8f8aa7434fc289ea4d73b5`,
`02671f0c939f89197ab283296e4be31cae5065d2`, and
`2baf7322a10f19fd6ed85407016db86514fa864c`; design record
`ba0078c621f183b07049e0daa03b73acfa724271`. PR-29…PR-32 record the 16 new production files and supersede
the 33-file patch inventory with 49; PR-33 later expands the current inventory to 51. Remaining risk: none
specific to these two P0 acceptance criteria; broader PRE-SKYEAGLE certification remains blocked by the
recorded P1/P2 work, Scan 3 and PRE-25. Exact next incomplete task: **PRE-09 / S1-P1-17 — decide and
enforce the disabled-token validation contract**. No SkyEagle rebranding began.

---

## 6. P1 FINDINGS

### S1-P1-02 — Two `brand-strings.json` overrides are dead configuration
`OpenEMR Authorization` → `Thiqa Authorization` and `OpenEMR Login` → `Thiqa Login` are applied as
`lang_id=1` rows (verified live), but commit `39d3f056b` replaced those constants in the templates with
`{{ applicationTitle }} {{ "Authorization"|xlt }}`. Repo-wide: `OpenEMR Authorization` → **zero** code
references; `OpenEMR Login` → one CSS comment only. The `consumers` arrays are factually wrong.
**Agent 2E resolved the other three as LIVE:** `OpenEMR Application`, `Welcome to OpenEMR`, `OpenEMR` are
still consumed by Zend `.phtml` layouts. So cleanup is exactly **two** entries, not five.

**Status: FIXED — VERIFIED (continuation Rev 14).** The two dead keys are absent from active
`english_overrides` and recorded under `retired_english_overrides` without consumers. The tenant-scoped
apply tool deletes only a `lang_id=1` definition whose binary value exactly equals the former managed value;
an absent row is already clean, a different/custom value is preserved, and constants plus every non-English
definition are untouched. The three live Zend overrides remain active. OAuth uses tenant-provided
`applicationTitle` plus translated `Authorization` / `Login` phrases. The stale database-upgrade
`carry_forward` entry is gone; the verified `%s Database Upgrade` durable contract remains authoritative.

```yaml
TASK/FINDING ID: PRE-09 / S1-P1-02
Previous status: CONFIRMED — REPAIR PENDING
Current status: FIXED — VERIFIED
Implementation commit: 2b801e668097599ac3e870448d340567929b4767
Files changed: brand-strings.json; apply-brand-strings.php; RetiredEnglishOverrideDecision.php; BrandStringCatalogueIsolatedTest.php; MandatoryCoreStringPatchesIsolatedTest.php; changes.md; patch-records.md; rebranding.md
Dead entries confirmed: OpenEMR Authorization -> Thiqa Authorization; OpenEMR Login -> Thiqa Login
Live entries preserved: OpenEMR Application -> Thiqa Application; Welcome to OpenEMR -> Welcome to Thiqa; OpenEMR -> Thiqa
Automated compatibility matrix: fresh row absent -> AlreadyAbsent; exact managed upgrade row -> DeleteManaged; customized existing tenant -> PreserveDifferent; case-different value preserved; Unicode exact value deleted
English rendering: all three OAuth templates use tenant applicationTitle plus translated Authorization; oauth2-login also uses tenant applicationTitle plus translated Login; no compound dead key remains in a consumer
Translation safety: deletion is constrained by def_id, cons_id, lang_id=1 and binary exact definition; lang_constants and non-English definitions are never deleted
Neutral upgrade contract: carry_forward is empty; contrib/util/language_translations/contracts/database-upgrade.json remains target_key %s Database Upgrade with both legacy keys recorded
Targeted positive: 44 tests / 227 assertions / exit 0
Canonical positive: 12/12 generated artefacts; 123/123 manifest; 157 tests / 1,760 assertions / exit 0
Lint/style: PHP lint 4/4 exit 0; JSON parse exit 0; PHPCS 4/4 exit 0
Negative control: decision mutation forced every present row to PreserveDifferent; 5 tests / 15 assertions / 2 expected failures / exit 1
Restoration: enum SHA256 BE962162E74DDBBB86A5B650FEBF965C88C647A72EB31F35E26F19C6250028A5 restored exactly; targeted suite then passed 44/227 exit 0; negative source and PHP session directory removed
Live read-only proof: dry-run exit 0 planned exactly 2 deletes and found 3 active rows already correct; before/after each dead constant retained 5 definitions with identical SHA256 hashes 446e9d1c94a999d19f389f498b422c529fa6b15af01100ecccb3965c644d6817 and d2960d67f19225ce396cf365dcd0a5857f3d98759385ccec878f1aaf78c1093d
Live database mutated: NO
Not-passes retained: first command selected the DB-backed bootstrap and exited 70 before tests; first corrected isolated run executed 39 tests / 212 assertions but exited 2 on provider autoload; first PHPCS command named nonexistent phpcs.xml and exited 16; each was corrected and none was called a pass
Warnings: canonical PHPUnit could not write its optional .phpunit.result.cache on the mounted tree; complete results and true exit 0 were captured
Next incomplete PRE-* task: PRE-09 / S1-P1-06 — reconcile D-8 status across the authoritative documents
```

### S1-P1-03 — Branding gates are not wired into CI
`grep -rn "branding-tokens-check|verify-brand-manifest|generate-tokens" .github/` → **nothing**, across
**64 workflows**. CI does run the isolated suite (`isolated-tests.yml:50`) and PHPStan. Token-drift and
manifest verification fire only if a human remembers.
**Owner directive: wire deterministic required CI checks before certification.**

**Status: FIXED — VERIFIED (continuation Rev 11).** The existing `.github/workflows/isolated-tests.yml`
now invokes the canonical `composer branding-ci` command once on its PHP 8.2 matrix leg, after dependency
setup and before the full isolated suite. Workflow permission is `contents: read`; the step needs no secret,
database, Apache, browser, network call, developer-specific path, path filter, `continue-on-error`, pipe, or
success mask. Composer runs the check-only token generator, the manifest verifier, and a targeted isolated
suite containing CI-contract, core-string, PHPStan guardrail and rule-registration tests. The PHPUnit command
uses `--fail-on-empty-test-suite`, `--fail-on-incomplete`, and `--fail-on-risky`.

```yaml
TASK/FINDING ID: PRE-09 / S1-P1-03; PRE-11 completed as part of the repair
Current status: FIXED — VERIFIED (local implementation and commands); GitHub-hosted confirmation not executed
Implementation commit: 597276b09d507cc0e61a2c8784bd49c670c1d020
Runbook/governance commit: ff6e35b4f4ddca0b16eb7a52c4489e7390664157
Workflow/job: .github/workflows/isolated-tests.yml / Isolated Tests / isolated-tests
Canonical command: composer branding-ci
Files changed: .github/workflows/isolated-tests.yml; composer.json; tests/Tests/Isolated/BrandingCi/BrandingCiContractTest.php; tests/Tests/Isolated/BrandingCoreStrings/MandatoryCoreStringPatchesIsolatedTest.php; docs/branding/ci-gates.md; docs/branding/adr/patch-records.md; this checkpoint
Positive control: 12/12 generated artefacts; 123/123 manifest; PHPUnit 91 tests / 264 assertions / 0 failures / 0 errors; true exit 0; 2026-08-24T08:27:36.1217930Z–08:28:15.6162167Z; 39.494 s; no timeout or incomplete result
Generated-drift negative: one-byte _css-variables.scss mutation; true exit 3; 2026-08-24T08:30:48.7406534Z–08:30:49.2317755Z; 0.491 s; restored SHA256 91328E9F9E972CE2D4C266436A799DD50D52237AA083B8F0FBBC79CC48405BFF; clean 12/12 exit 0 in 0.465 s
Manifest-mismatch negative: one-byte typography-weight-contract.md mutation; 122/123 and true exit 1; 2026-08-24T08:20:02.4114892Z–08:20:05.5436587Z; 3.132 s; restored SHA256 C7987BD0B4B1F6F1ED6911FD3AFD117A0F7B5448CA10AD1447BED6A371C1E5AA; clean 123/123 exit 0 in 3.063 s; no hashes re-issued
Guardrail negative: existing all_violations_branding_namespace.php fixture copied temporarily into the module; rule-only PHPStan reported 12 file errors and all four identifiers (thiqaBranding.noRuntimeHttpClient, thiqaBranding.noSiteConfigSeam, thiqaBranding.twigNamespaceDiscipline, thiqaBranding.noPlaceholderEndpoint); true exit 1; 2026-08-24T08:25:28.9395696Z–08:26:11.5740653Z; 42.634 s; temporary file removed
Zero-test unguarded control: php vendor/bin/phpunit -c phpunit-isolated.xml --filter SkyEagleBranding --no-coverage; 0 tests / 0 assertions; "No tests executed!"; true exit 0; final timed confirmation 2026-08-24T08:33:06.4232339Z–08:33:27.1167938Z; 20.694 s (the initial observation was 17.319 s)
Zero-test after repair: same deliberately nonexistent filter plus --fail-on-empty-test-suite; 0 tests / 0 assertions; "No tests executed!"; true exit 1; 2026-08-24T08:28:26.0217108Z–08:28:41.2621650Z; 15.240 s
Workflow validation: Symfony YAML parse exit 0; Composer validate --strict exit 0; PHP lint exit 0; PHPCS using phpcs.xml.dist 2/2 files exit 0; dependency and shell ordering reviewed; no absolute local path or secrets in committed workflow
PHPStan completeness handling: CI executes repository-owned RuleTestCase suites directly under PHPUnit; analyzer exceptions/errors fail PHPUnit, and incomplete/risky/empty PHPUnit outcomes are nonzero. The separate full PHPStan workflow still invokes vendor/bin/phpstan directly with no downstream pipe.
GitHub-hosted execution performed: NO (no push was authorized); local workflow syntax and command behavior only
Limitations/not-passes: an initial aggregate exposed a stale core-string assertion and exited 1 before correction; one direct PHPStan attempt failed on a cache lock, one excluded-fixture attempt returned no files, and one full-config module attempt was terminated after exceeding 90 s. None was called PASS. The successful rule-only PHPStan negative control above is the recorded guardrail proof. PHPUnit emitted an optional result-cache permission warning after its successful test result; true aggregate exit remained 0.
Restoration/current status: every disposable file mutation restored or removed; generated and manifest checks green; tracked tree clean after commits; preserved untracked .claude/ only
Exact next task: PRE-09 / S1-P1-15 brand-neutral migration-safe backup retention
```

### S1-P1-04 — Nothing cross-checks guardrail namespace constants against the real module
All four rules hardcode `MODULE_NAMESPACE = 'OpenEMR\Modules\ThiqaBranding'`
(`ForbiddenBrandingHttpClientRule.php:55` + `:61`, `ForbiddenBrandingSiteConfigRule.php:40`,
`ForbiddenBrandingTwigPathRule.php:54`, `ForbiddenBrandingPlaceholderDomainRule.php:40`), matched with
`===` / `str_starts_with`. `ThiqaBrandingRuleRegistrationTest` proves **wiring, not matching** — its own
docblock says so. Fixtures and rule constants are mutually consistent *by construction*, so a rename that
misses production stays green.
**Required remediation:** `production namespace changes + rule constant does not → deterministic test failure.`

**Status: FIXED — VERIFIED (continuation Rev 20).** `ThiqaBrandingGuardrailScopeTest` derives the
production namespace from the module itself and asserts the four constants follow it, failing in both
directions. The module is located by a brand-neutral anchor — the unique custom module carrying
`src/Config/BrandingGlobalKey.php`, whose `saas_branding_` prefix locked decision Q58 forbids renaming —
so a future SkyEagle migration that renames both the namespace and the directory still resolves to the
right module and still demands the constants move with it. The namespace comes from that module's own
PSR-4 autoload prefix, which a real rename cannot skip because it is what makes the classes loadable, and
is cross-checked against the `namespace` declaration of all 92 shipped source files.

```yaml
TASK/FINDING ID: PRE-09 / S1-P1-04
Previous status: EXECUTION-PROVEN DEFECT, NOT REPAIRED (proving a defect is not repairing it)
Current status: FIXED — VERIFIED
Implementation commit: see the remediation-commit table row for S1-P1-04
Files changed: tests/Tests/Isolated/PHPStan/ThiqaBranding/ThiqaBrandingGuardrailScopeTest.php (new); docs/branding/architecture.md §7.5a; docs/branding/adr/patch-records.md (PR-36)
Production source changed: NONE — the guardrail rules themselves are untouched
Derivation chain: brand-neutral anchor file -> unique module directory -> module composer.json PSR-4 prefix -> compared against every shipped src/**/*.php namespace declaration -> compared against each rule's MODULE_NAMESPACE read by reflection
Constants covered: MODULE_NAMESPACE on all four rules; MATERIALISATION_NAMESPACE on the HTTP-client rule, additionally required to name a directory that exists
Fixture coupling closed: the in-scope violating fixtures must declare the production namespace, so a rename touching only fixtures no longer leaves the rule suites green against a namespace nothing ships under
Twig tip coupling: the rule's addPath() advice must name the real module directory basename
Targeted positive: 9 tests / 240 assertions / exit 0
Full guardrail suite after restoration: 63 tests / 320 assertions / exit 0
Canonical positive: 12/12 generated artefacts; 123/123 manifest; 228 tests / 2,653 assertions; true exit 0; 2026-08-24T17:12:06.6088840Z–17:13:01.2632943Z; 54.654 s
Lint/style: PHP lint exit 0; PHPCS 1/1 exit 0
Negative control (the finding's exact scenario): the module's PSR-4 prefix was renamed to OpenEMR\Modules\SkyEagleBranding with the rule constants deliberately left alone; 7 failures / true exit 1, naming all four rules with "would go inert: it matches a namespace the module does not ship under"
Restoration: module composer.json restored exactly to SHA256 ED6A928EA4B64923DE140699B013295F9C68BC27650C31D323A39AA4FD6FA229; temporary backup removed; full guardrail suite then passed
Why a test rather than a code change: deriving the namespace inside a PHPStan rule would put file I/O and JSON parsing on every analysed node and would fail OPEN if the manifest were unreadable. A constant verified by a deterministic gate keeps the rules trivial and fails CLOSED, before a rename can land.
Residual gap (unchanged, and honest): this proves constant-vs-production alignment, not a full end-to-end PHPStan run. The unexercised link remains PHPStan's Scope::getNamespace() returning the string these constants are compared against — the same residual PRE-10 recorded. It is mechanical, and the finding no longer depends on it.
Database / Apache / browser touched: NO / NO / NO
```

### S1-P1-05 — WCAG evidence-of-record understates itself
`brand/qa/wcag-contrast-results.json` = **38 pairs / 35 PASS / 3 ADVISORY / 0 FAIL** (verified by orchestrator).
`docs/branding-production/08-wcag-contrast.md` still says 34 / 31 / 3.

**Status: FIXED — VERIFIED (continuation Rev 15).** Revision 3 now reports the re-derived 38 / 35 / 3 / 0
counts and includes all four passing `borderStrong` UI rows already present in machine evidence. Default-border
rows use the machine `ADVISORY` vocabulary and thresholds. The new canonical regression guard derives status
counts from JSON, requires every current machine row in the human table, and checks PASS/advisory threshold
consistency. No token or machine-evidence value changed.

```yaml
TASK/FINDING ID: PRE-09 / S1-P1-05
Previous status: CONFIRMED — DOCUMENTATION DRIFT
Current status: FIXED — VERIFIED
Implementation commit: b400546a2ffa1f1a6edb6b802315124bcef2bd67
Authoritative current counts: 38 pairs; 35 PASS; 3 ADVISORY; 0 FAIL
Added human rows: light borderStrong/background 7.45; light borderStrong/surface 7.78; dark borderStrong/background 9.10; dark borderStrong/surface 8.41; all threshold 3.0 and PASS
Machine/source proof: ContrastCalculatorTest 110 tests / 264 assertions / exit 0
Documentation contract: WcagEvidenceContractTest 3 tests / 368 assertions / exit 0
Combined targeted: 113 tests / 632 assertions / exit 0
Canonical positive: 12/12 generated artefacts; 123/123 manifest; 160 tests / 2,128 assertions / exit 0
Lint/style: new test PHP lint exit 0; PHPCS 1/1 exit 0
Manifest governance: only 08-wcag-contrast.md and self-referential 12-release-verification.md entries re-issued; entries removed 0; reason recorded in release verification Rev 6; verifier 123/123 exit 0
Negative control: human PASS summary changed 35 -> 34; named contract test 1 failure / exit 1
Restoration: 08-wcag-contrast.md restored exactly to SHA256 8B2C8EC4B26BA903744E6F2F14E0F8D8279644256CF70A4E68D5FD3A3AB4D8E8; backup removed; combined targeted, manifest and canonical gate subsequently passed
Not-pass retained: first row-level contract run executed 3 tests / 172 assertions but failed on inline-code markup differing from the machine label; labels were normalized and the full 38-row contract then passed
Database / Apache / browser touched: NO / NO / NO
Next incomplete PRE-* task: PRE-09 / S1-P1-06 — reconcile D-8 across RebrandingPlan.md, remaining-dependencies.md and ADR-BRAND-001
```

### S1-P1-06 — D-8 documented CLOSED, actually RE-OPENED
`RebrandingPlan.md` §6.5 strikes D-8 as "ELIMINATED / RESOLVED by design change" and counts it in
"4 of 13 closed". RB-04 re-opened it. `remaining-dependencies.md` and `ADR-BRAND-001` carry the correction;
`RebrandingPlan.md` — the release-blocking-dependency register — never got it.

**Status: FIXED — VERIFIED (continuation Rev 16).** The three authoritative records now agree that D-8
remains OPEN: the repaired PHP endpoint is the linked browser route, while the shipped materialiser still
stages and commits both static token stylesheets through `TokenCssWriter`. S1-P0-09 fixed the consumer chain;
it did not make the module tree read-only. The register now says 3 of 13 closed, and the associated design,
risk and summary prose no longer claim the writer is absent.

```yaml
TASK/FINDING ID: PRE-09 / S1-P1-06
Previous status: CONFIRMED — DOCUMENTATION CONTRADICTION
Current status: FIXED — VERIFIED; underlying dependency D-8 remains OPEN
Implementation commit: 88ff342894c9543fe30b8e6b6a046ca2f56fd1b7
Runtime/source proof: pre-change BrandingMaterialiserTest + TokenCssWriterTest 32 tests / 146 assertions / exit 0; source and execution both prove two static variants are committed
Documentation contract: D8DependencyStatusContractTest 2 tests / 19 assertions / exit 0
Combined targeted: 34 tests / 165 assertions / exit 0
Canonical positive: 12/12 generated artefacts; 123/123 manifest; 162 tests / 2,147 assertions / exit 0
Lint/style/diff: PHP lint exit 0; PHPCS 1/1 exit 0; git diff --check exit 0
Negative control: authoritative D-8 row changed to struck/RESOLVED; named test 1 failure / exit 1
Restoration: plan restored exactly to SHA256 796AC7096B052EE984E327A7DF2411351AA73AA3860D8675886422335820F828; temporary backup removed; targeted and canonical gates subsequently passed
Not-pass retained: an invocation using the full PHPUnit config failed bootstrap exit 70; an isolated invocation with two incorrect paths also failed exit 70; neither executed the intended suite and neither is counted as PASS
Database / Apache / browser touched: NO / NO / NO
Next incomplete PRE-* task: PRE-09 / S1-P1-10 — correct the false Amiri/mPDF capability claim after verifying current code and evidence
```

### S1-P1-10 — False capability claim in live tooling
`tools/branding/install-assets.php` ~line 458 states the Amiri PDF face is *"registered with mPDF in
`src/Pdf/Config_Mpdf.php`"*. That file has **zero** Amiri references (`grep -c` → 0). Per RB-14/EV-RB14 the
registration cannot work anyway (mPDF cannot parse the font). Correct the claim after Scan-2 confirmation.

**Status: FIXED — VERIFIED (continuation Rev 17).** The installer now calls Amiri a candidate asset and
states that D-9 is open. Current source, dependency lock and preserved runtime evidence agree: files are
delivered, but mPDF and dompdf do not provide the claimed Arabic PDF capability. The Owner's adopted EV-RB14
Option C accepts this known limitation through pilot; revisit is triggered only by a customer contract.

```yaml
TASK/FINDING ID: PRE-09 / S1-P1-10
Previous status: CONFIRMED — FALSE CAPABILITY CLAIM
Current status: FIXED — VERIFIED; D-9 remains OPEN as an Owner-accepted pilot limitation
Implementation commit: 723170df701ddb948a95b8177d100411fc71b0d9
Current package/runtime evidence: mpdf/mpdf v8.3.1; dompdf/dompdf v3.1.5; Config_Mpdf default dejavusans; no Amiri/Noto registration
Approved assets verified: Amiri-Regular.ttf 437,780 bytes SHA256 CD2550C0F4C05EB341BF97958211AAA39382BCA96577BA3A67D4A3B4912C43C0; Amiri-Bold.ttf 414,560 bytes SHA256 8CFED49B66DE57E360AC61631D882DDDFA23095D3DDB39FC2133EAF93892E79A
Decision evidence: EV-RB14 reproduces GPOS Lookup Type 5 Format 3 failure across four Arabic faces and records Owner-adopted Option C through pilot; OTL-off output is unshaped and not accepted
Targeted contract: PdfFontCapabilityClaimContractTest 2 tests / 20 assertions / exit 0
Canonical positive: 12/12 generated artefacts; 123/123 manifest; 164 tests / 2,167 assertions / exit 0
Lint/style/diff: two PHP files lint exit 0; PHPCS 2/2 exit 0; git diff --check exit 0
Manifest governance: pre-edit 123/123 exit 0; intentional pre-reissue 121/123 with exactly 09 and 12 mismatching, exit 1; exactly those two entries re-issued, none removed; final 123/123 exit 0; reason recorded in release verification Rev 7
Negative control: installer note restored to the exact false "registered with mPDF" claim; named test 1 failure / exit 1
Restoration: installer restored exactly to SHA256 0A2D93693F33448939DF5233C8DC9E176ECED78B6464C2EB36FEBE9EE3D4348E; temporary backup removed; targeted, manifest and canonical gates subsequently passed
Database / Apache / browser touched: NO / NO / NO
Remaining limitation: Arabic PDF output is unavailable; this repair makes that true state explicit and does not fabricate a font or risky engine registration
Next incomplete PRE-* task: PRE-09 / S1-P1-11 — classify operational Thiqa occurrences and neutralize only safe reusable tooling
```

### S1-P1-11 — Live production artefacts carry Thiqa operational identity
`docs/evidence/ubuntu-infra-scripts/07-deploy-code-update.sh:3,26,87` pins
`BRANCH=feat/thiqa-branding-foundation`; `05-seed001-demo-date-rebase.sh` installs a **running systemd unit**
`Description=Thiqa demo seed…`; README references R2 bucket `thiqa-demo-backups`.
**Do NOT blindly rename.** Each needs classification: active product identity / operator label /
infrastructure historical name / expensive live resource / safe rename / preserve+document.

**Status: FIXED — VERIFIED (continuation Rev 18).** All ten relevant occurrences in the bounded Ubuntu
evidence set were classified. Reusable date-rebase source labels are neutral. The stale hypothetical bucket
example is gone. The SHA-pinned branch/module identifiers, pre-existing bucket identifier and historical
evidence remain exact. This changes future source behavior only; no installed remote unit was touched.

```yaml
TASK/FINDING ID: PRE-09 / S1-P1-11
Previous status: CONFIRMED — UNCLASSIFIED OPERATIONAL IDENTITY
Current status: FIXED — VERIFIED
Implementation commit: 29be1fcd5b14001ce2a1a0d1aa390ef10aa617a0
Classification: date-rebase comment/systemd source labels = safe-to-neutralize; feat/thiqa-branding-foundation = historical SHA-pinned branch; oe-module-thiqa-branding = production compatibility identifier; thiqa-demo-backups = stale hypothetical example removed; skyeaglebucket = pre-existing external resource preserved; other product-name records = historical evidence preserved
Files changed: README.md; 05-seed001-demo-date-rebase.sh; 07-deploy-code-update.sh; OperationalIdentityContractTest.php
Targeted contract: 2 tests / 14 assertions / exit 0
Shell syntax: Git Bash -n for both changed scripts / exit 0 each
Canonical positive: 12/12 generated artefacts; 123/123 manifest; 166 tests / 2,181 assertions / exit 0
Lint/style/diff: PHP lint exit 0; PHPCS 1/1 exit 0; git diff --check exit 0
Negative control: one neutral systemd description changed back to Thiqa; named test 1 failure / exit 1
Restoration: date-rebase script restored exactly to SHA256 8E0C13713C96B15B43716415131A8AA7A8E08D9895582F3AD88653E166E9FDB6; temporary backup removed; targeted, manifest and canonical gates subsequently passed
Not-pass retained: first sandboxed Git Bash attempt failed before syntax evaluation with Win32 signal-pipe error 5; it is not counted as PASS; the bounded elevated rerun supplied both real exit-0 results
Live service / branch / module / bucket / backup touched: NO / NO / NO / NO / NO
Remaining limitation: an already-installed remote systemd description may retain its historical label until a separately authorized infrastructure migration; this programme did not inspect or mutate that host
Next incomplete PRE-* task: PRE-09 / S2-P1-18 — make branding health measure the served and consumed state truthfully
```

### S1-P1-15 — Backup retention breaks silently on a prefix rename
`src/Console/BackupCommand.php:46` `DEFAULT_TARGET = 'C:/openemr-stack/backups'` (host-specific Windows path
in a shippable module); `:95` writes `thiqa-<label>-<Ymd-His>.sql`; `:153` prunes
`glob($target . '/thiqa-*.sql')`. Rename the prefix and the glob stops matching every pre-existing backup —
never pruned, and `glob()` returning fewer files than `--keep` is indistinguishable from a healthy young
archive. **No error.** Owner directive: build brand-neutral migration-safe retention now. Test matrix:
old prefix only / new prefix only / mixed archive / keep=N / zero matches / unexpected files / rollback.

**Status: FIXED — VERIFIED (continuation Rev 12).** New verified dumps use the identity-neutral, versioned
format `managed-db-backup-v1-<label>-<YYYYMMDD-HHMMSS>.sql`. The closed parser also recognizes exact legacy
`thiqa-<label>-<timestamp>.sql` names indefinitely for compatibility, but only when the dump is a direct regular
non-link child of the resolved target and has the exact valid command-written `.sha256` marker. Existing archives
are not renamed, copied or automatically migrated. Mixed families form one retention set ordered by the parsed
filename timestamp; descending basename is the deterministic equal-timestamp tie-breaker, so filesystem mtime and
scan order are irrelevant.

Labels are 1–63 ASCII letters/digits/underscores/hyphens, beginning alphanumeric; invalid, empty, punctuated,
absolute or traversal-shaped values fail instead of being sanitized. `--keep` accepts positive whole numbers
only: zero, negative, decimal, overflow and non-numeric inputs fail. `keep=1`, equal/greater counts, zero managed
files and one managed file are explicit and tested. Missing/non-directory/unreadable targets and scan failure are
errors, never healthy zero-match results. A validated missing target may be created by the backup command.

Deletion re-resolves the target and candidate, rejects links/reparse probes, requires a direct regular file,
reparses the strict name and revalidates the exact sidecar immediately before deleting only that dump and sidecar.
Unrelated SQL, unexpected extensions, partial/unverified dumps, malformed dates, case variants and matching-looking
directories remain untouched. Any scan or deletion failure exits nonzero and is not reported as success.

```yaml
TASK/FINDING ID: PRE-09 / S1-P1-15
Current status: FIXED — VERIFIED
Implementation commit: 77d2b3e12914cd172d72d47b6fe4543750b70735
Test/CI commit: 8eb4ea7f8dbaf92f023f2a745a17f767f6a15f07
Documentation/PR-34 commit: 64d2ba23c46336ee567cfcac944f08668be466a5
Files changed: BackupCommand.php; ManagedBackupArtifact.php; ManagedBackupInventory.php; ManagedBackupRetention.php; BackupRetentionTest.php; BrandingCiContractTest.php; composer.json; docs/branding/backup-retention.md; docs/branding/runbook.md; docs/branding/adr/patch-records.md; this checkpoint
Default-path decision: Preserve C:/openemr-stack/backups as a deployment-compatibility default so existing schedules do not silently change destination; require explicit --target on other hosts; retention logic has no path coupling
Migration: passive dual recognition; old files stay byte/name-identical; no duplicate copy and no automatic rename
Rollback: neutral dumps remain ordinary recoverable SQL+hash artifacts, but an older command ignores them for retention; planned rollback requires capacity monitoring and must not rename neutral files to legacy names; re-deploying the repair resumes the union
Targeted final: 56 tests / 1,422 assertions / 0 failures / 0 errors / 0 skipped; true exit 0; 2026-08-24T08:52:26.6120828Z–08:52:32.6311081Z; 6.019 s
Named negative control: 1 test / 108 assertions / exit 0; 2026-08-24T08:53:01.6605465Z–08:53:07.6779985Z; 6.017 s; a neutral-prefix-only selector saw 2 neutral and missed 3 legacy files, while repaired discovery saw all 5, selected the 3 correct oldest for keep=2, and preserved unrelated.sql
Canonical branding-ci: 12/12 generated artefacts; 123/123 manifest; 147 tests / 1,687 assertions; true exit 0; 2026-08-24T08:53:13.3397400Z–08:54:04.1112666Z; 50.772 s
CI contract: 3 tests / 31 assertions / exit 0; Composer validate --strict exit 0
PHP syntax: 6/6 changed PHP files exit 0; PHPCS: 6/6 files exit 0 (1.27 s)
Not-passes retained: first matrix run executed 53 tests / 1,323 assertions and exited 1 on a Windows 8.3-vs-long-path assertion; corrected and rerun. A later 55-test run passed but had one link-permission skip, so it was not accepted as the full matrix; link probing was made injectable and the final 56-test run had no skips.
Warnings: PHPUnit could not write its optional .phpunit.result.cache on the mounted tree; complete test results and true exits were still captured and green
Manifest-covered files changed: NONE; manifest nevertheless verified 123/123 inside branding-ci
Real backup directory touched: NO
Backup command/mysqldump executed: NO
Live database / Apache / browser touched: NO / NO / NO
Temporary directories: one unique prebrand-backup-retention-* directory per test; every deletion path validated inside it; final residue count 0
Current Git status after commits: expected only preserved untracked .claude/ before this checkpoint edit
Remaining limitations: default target is intentionally retained for schedule compatibility; real deployment execution was not authorized or required; older-version rollback cannot prune neutral files and therefore requires temporary capacity monitoring
Exact next task: PRE-09 / S1-P1-17 disabled-token validation contract decision
```

### S1-P1-17 — One token is tenant-overridable with no contrast gate, and the doc says the opposite
`TokenKey::InteractivePrimaryDisabled` is in the `=> true` arm of `isTenantOverridable()` **and** the `null`
arm of `contrastRule()`. Net: **11 overridable, 10 contrast-gated.** `architecture.md` §3 annotates the row
*"Yes, except interactive.primary.disabled"*, contradicting its own "Exactly 11" total (8+2=10≠11).
A tenant value for this key passes on hex-format validation alone. Not a WCAG violation (inactive controls
are exempt) but the contract must become deliberate.
**Decision required:** keep overridable with a distinguishability constraint / make non-overridable /
keep and document why. Source and docs must stop contradicting.

**Status: FIXED — VERIFIED (continuation Rev 13).** The authoritative 11-key allowlist remains intact.
`interactive.primary.disabled` is the sole tenant override without a WCAG contrast rule, because inactive
controls are exempt from SC 1.4.3 and SC 1.4.11. `TokenValidator` now applies a separate product rule: at
least 1.5:1 luminance separation from both `interactive.primary.default` and `background`, re-evaluated when
either tenant-overridable primary fill changes. `insufficient_state_separation` is a distinct wire reason and
is never described as a WCAG failure. The live component rule keeps the canonical tenant variable, Tier-1
fallback and Bootstrap's fixed disabled opacity.

```yaml
TASK/FINDING ID: PRE-09 / S1-P1-17
Previous status: OPEN — DECISION REQUIRED
Current status: FIXED — VERIFIED
Decision: Option B — preserve tenant capability with a separate non-WCAG distinguishability rule
Reason: ADR-BRAND-003, the runbook, readiness evidence, enum and exact allowlist tests all deliberately specify 11 overrides; making disabled non-overridable would remove intended capability, while format-only validation could let it disappear into the enabled fill or canvas
Code/files changed: TokenKey.php; TokenValidator.php; RejectionReason.php; _overrides.scss; TokenKeyTest.php; TokenValidatorTest.php; TokenGeneratorIsolatedTest.php; architecture.md; ADR-BRAND-003; multi-tenant-white-label-readiness.md; runbook.md
Tests executed: focused key/validator/generator suite; downstream service/materialiser/config/console suite; named reversible negative; restored named positive; generator check; manifest verifier; composer branding-ci; PHP lint; PHPCS; local theme build and 28-file hash reconciliation
Tests/assertions: focused 261/773; downstream 153/577; negative 1/1 failure as expected; restored positive 1/3; branding-ci 147/1687
Exit codes: focused 0; downstream 0; negative 1 expected; restored positive 0; generator 0; manifest 0; branding-ci 0; lint 0; PHPCS 0; webpack/build 0
Positive controls: exact source counts 11 overridable / 10 WCAG-gated / disabled product floor 1.5; valid light #E5B0A5 and dark #6B3D36 accepted; compiled light/dark disabled consumers use --interactive-primary-disabled and opacity .65
Negative controls: background-identical disabled fill rejected; enabled fill collapsing onto disabled rejected; malformed rgba rejected; threshold temporarily changed 1.5 -> 1.0 and named regression failed exit 1
Runtime evidence: webpack 5.108.1 built 18 top-level + 10 misc CSS with 187 warnings and no errors; exactly eight affected theme files copied after hash comparison; destination 28/28 byte-matches build; forbidden stale theme count 0
Database touched: NO
Database restored: NOT APPLICABLE
Temporary files removed/restored: threshold mutation restored in finally; TokenValidator SHA256 8E729125794B2B57DF824BF9D6584DA4C99CB0C091EC09532FAD3F4D3313AD58; orphan PHP process count returned to 0
Independent verification: final composer branding-ci rechecked 12/12 generated artefacts, 123/123 manifest and a non-empty 147-test suite; documentation scan and executable source count agree
Commit hash: 0af1ce1740f1e1caf6d8536a18cee4c2c50917a5
Not-passes retained: first focused run caught the stale #DDDDDD fixture (261 tests, 1 failure, exit 1); one group command ran zero tests and is NOT PASS; one broad run was stopped incomplete at 793/1359 and both exact orphan PHP processes were terminated; one stale generator path exited 1 before the real entry point passed
Remaining limitations: browser/live-DB mutation was neither required nor performed; the fixed opacity was proven in both compiled variants and existing S1-P0-09/PRE-16 live consumer proof remains authoritative
Next incomplete PRE-* task: PRE-09 / S1-P1-02 — remove or migrate exactly two dead translation overrides without disturbing the neutral database-upgrade contract
```

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

**Status: FIXED — VERIFIED (continuation Rev 19).** The finding was reproduced exactly before repair, then
repaired at the root: the two delivery planes are now distinct types, and only the served one can fail a
health probe. The tenant's rendering never needed changing — it was already correct — so this repair changes
what is *measured and reported*, not what is served. The live database and both generated files were read
but never written.

```yaml
TASK/FINDING ID: PRE-09 / S2-P1-18
Previous status: CONFIRMED — HEALTH CHECK MEASURES THE WRONG PLANE; NOT GATED
Current status: FIXED — VERIFIED
Implementation commit: 1474263b4
Pre-repair reproduction: verify --site=default -> Status inconsistent; Revision 0; both stylesheets present; 1 inconsistency stylesheet_without_revision; true exit 1
Post-repair live result: same tenant -> Status never materialised (rendering product defaults); Serves tenant overlay no; both stylesheets reported "(not served)"; 1 advisory; true exit 0
Root-cause repair: BrandingObservationPlane names Served vs StaticArtefact and owns failsHealth(); every BrandingInconsistency case declares its plane by exhaustive match; BrandingHealthReport carries inconsistencies and advisories separately and throws on a finding placed on the wrong plane; statusFor() receives served-plane findings only
Served-plane cases added: overlay_without_revision; unrenderable_token_overlay
Wire compatibility: revision_without_stylesheet and stylesheet_without_revision keep their exact string values and only change plane; no identifier renamed or removed
Parser reuse: overlay emptiness answered by TokenOverlay::fromJson(), the same parser BrandingConfigFactory runs per request; the contract test asserts BrandingHealthCheck contains no independent json_decode
CI gating (the finding's second half): composer branding-ci now runs tests/Tests/Isolated/Modules/ThiqaBranding/Observability and Console/VerifyCommandTest.php; BrandingCiContractTest fails if either path is removed
Files changed: BrandingObservationPlane.php (new); BrandingInconsistency.php; BrandingHealthCheck.php; BrandingHealthReport.php; VerifyCommand.php; composer.json; BrandingHealthTruthfulnessContractTest.php (new); BrandingCiContractTest.php; BrandingHealthCheckTest.php; VerifyCommandTest.php; runbook.md; architecture.md; remaining-dependencies.md; closure-evidence-pack.md; patch-records.md (PR-35)
Targeted positive: 65 tests / 684 assertions / exit 0
Canonical positive: 12/12 generated artefacts; 123/123 manifest; 219 tests / 2,413 assertions; true exit 0; 2026-08-24T16:58:55.5198033Z–17:01:16.2506635Z; 140.731 s
Lint/style/diff: PHP lint 9/9 exit 0; PHPCS 9/9 exit 0; composer validate --strict exit 0; git diff --check exit 0
Negative control: stylesheet_without_revision reclassified back to the Served plane; named contract 6 tests / 13 assertions / 2 failures / true exit 1
Restoration: BrandingInconsistency.php restored exactly to SHA256 2CDD62DDB5544CD8155FD27E2F0C6E9063F144117527FCA88D914E774A8D88CD; temporary backup removed; targeted suite then passed 65 / 684 exit 0
Documentation drift corrected: remaining-dependencies.md area 42 and surprise 8; closure-evidence-pack.md item 1; architecture.md Plane 2 status. All three carried RB-11's superseded "Status: healthy / Revision: 1" reading; each now states the current live reading beside the query that produces it
Live database mutated: NO (read-only SELECT before and after; all seven saas_branding_* values length-identical)
Generated stylesheets mutated: NO (both still 1,522 / 1,553 bytes, mtime 2026-08-10T18:50:40Z)
Apache / browser touched: NO / NO
Manifest-covered files changed: NONE (the manifest covers docs/branding-production/*.md; every document edited here is under docs/branding/); manifest nevertheless verified 123/123 inside branding-ci
Remaining limitation: D-8 stays OPEN by design — the materialiser still writes the unserved static stylesheets, and this repair deliberately does not change that. It makes the consequence visible and non-failing instead of silently wrong. The live tenant keeps printing one advisory until those two orphaned files are cleared, which is optional housekeeping rather than recovery.
Next incomplete PRE-* task: PRE-09 / S1-P1-04 — make a production namespace rename that misses a guardrail constant fail deterministically
```

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

**Status: PARTLY REFUTED, REMAINDER FIXED — VERIFIED (continuation Rev 20). See Correction K.**

- **The rendering claim is FALSE and is withdrawn.** Inter ships as a **variable** face. Decoding the WOFF2
  table directory of every shipped face found `fvar`, `gvar`, `HVAR` in all four Inter files (21 tables) and
  **no `fvar`** in any IBM Plex Sans Arabic file (17 tables). A variable face declared with a
  `font-weight: <min> <max>` range renders every weight in that range.
  `interface/themes/thiqa/_typography.scss:22-26` declares exactly one Inter `@font-face` at
  `font-weight: 400 700`; all 8 compiled theme files reference `Inter-Regular.woff2` once each, with **zero**
  references to `-Medium`/`-SemiBold`/`-Bold`. `typography-tokens.json` already points all four Inter weights
  at the one variable file. **RB-22's "FIXED" is accurate**, and the scan's inference — not its observation —
  was the error. The hash observation itself reproduced exactly.
- **The manifest-gap half is TRUE and is now closed.** `SHA256SUMS` verifies each file against its own
  recorded hash, so it would pass four identical files under four names — which for a *static* family is a
  genuine rendering defect. `BrandingFontFaceDistinctnessContractTest` (inside `composer branding-ci`) now
  requires that a family backing several declared weights with one file prove that file carries `fvar`;
  requires a static family's faces to be byte-distinct; keeps a positive control so the detector cannot
  degrade into "everything is variable"; and pins the three duplicates as unreferenced.
- **The redundant binaries are real and are recorded, not deleted.** `Inter-Medium/-SemiBold/-Bold.woff2` are
  still present under `brand/typography/fonts/` and installed into `public/assets/fonts/thiqa/`, 48,256 bytes
  each, referenced by nothing. Browsers never fetch them, so the cost is ~145 KB of deployed disk, not
  bandwidth. **Deleting them is an asset-governance decision, not cleanup:** they carry approved-asset IDs
  inside the 107-entry brand inventory, so removal changes `SHA256SUMS`, `asset-manifest.csv`,
  `asset-manifest.json`, the `THIQA-###` ID set and every document quoting 107. Left for an Owner decision;
  the contract test makes re-referencing one a deterministic failure in the meantime.

```yaml
TASK/FINDING ID: PRE-09 / S2-P1-20
Previous status: CONFIRMED — LEDGER ITEM MARKED FIXED IS NOT FIXED
Current status: REFUTED IN PART (rendering claim withdrawn); REMAINING GAP FIXED — VERIFIED
Implementation commit: see the remediation-commit table row for S2-P1-20
Method, so it is re-runnable: WOFF2 table-directory decode (flag byte + UIntBase128 walk, known-tag index table) over every file in brand/typography/fonts; SHA-256 over the same set; reference counts over public/themes/*.css
Font evidence: Inter-{Regular,Medium,SemiBold,Bold}.woff2 all 48,256 bytes, SHA-256 3100E775E8616CD2..., 21 tables, fvar+gvar+HVAR present; IBMPlexSansArabic-{Regular,Medium,SemiBold,Bold}.woff2 42,848/45,296/45,688/44,280 bytes, four distinct hashes, 17 tables, no fvar
Stylesheet evidence: Inter-Regular referenced 8 times across public/themes/*.css (once per theme file); Inter-Medium, Inter-SemiBold, Inter-Bold referenced 0 times
Contract evidence: typography-tokens.json maps all four Inter weights to fonts/Inter-Regular.woff2 and each IBM Plex weight to its own file; on-disk SHA-256 AAF223C70690613AEE22E9269FFCFBB11F98AE46016AFF7EE82EE4057D52EBFF matches the SHA256SUMS entry
Targeted positive: 5 tests / 218 assertions / exit 0
Negative control: one IBM Plex weight re-pointed at IBMPlexSansArabic-Regular.woff2, making a static family share a face; 1 failure / true exit 1 naming the family, the file and the missing fvar table
Restoration: typography-tokens.json restored exactly to SHA256 AAF223C70690613AEE22E9269FFCFBB11F98AE46016AFF7EE82EE4057D52EBFF; temporary backup removed
Fonts, assets or manifest mutated: NO
Deferred decision: whether to retire the three unreferenced duplicate binaries and renumber the 107-asset inventory. Owner call; not taken here.
Database / Apache / browser touched: NO / NO / NO
```

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

**Re-verified live 2026-08-24 (read-only), with two updates.**
(1) It is now **three** surfaces, not five: S1-P1-02 retired `OpenEMR Authorization` and `OpenEMR Login` as
dead configuration. The live counts for the three that remain are `OpenEMR` 8 defs / 1 English override /
**0 Arabic**, `OpenEMR Application` 9 / 1 / **0**, `Welcome to OpenEMR` 9 / 1 / **0** — the finding
reproduces exactly.
(2) Their consumers are three Zend `<title>` tags
(`Application/view/layout/layout.phtml:6`, `Documents/view/layout/layout.phtml:18`,
`Application/view/layout/sendto.phtml:6`), and the correct repair is the **same** mechanism S2-P1-23 needs.
**See the blocking-constraint block under S2-P1-23** — for these three keys the carry-forward is the
already-proven `neutraliseLegacyDefinition()` transform, but the contract subsystem must be generalised to
more than one key first. Do not fix this one in isolation.

**Status: FIXED — VERIFIED (continuation Rev 22).** Fixed as part of the single S2-P1-22/23/24 workstream;
the evidence block lives under S2-P1-23. Specifically: `Application/layout.phtml` composes `%s Application`
and `Documents/layout.phtml` composes `Welcome to %s`, both carrying the old keys' definitions forward with
the brand literal replaced by `%s` (`zend-application-neutral-v1`, `zend-welcome-neutral-v1`, both proven on
a disposable database — `Aplicacion %s`, `%s Anwendung`, `Bienvenido a %s`). `sendto.phtml` reads
`openemr_name` directly, because a bare product name is not a phrase to translate; that removes the last
consumer of the `OpenEMR` catalogue constant entirely.

All three English overrides are retired by exact value, so `brand-strings.json`'s `english_overrides` is now
**empty** — an English override only ever reached `lang_id=1`, which is precisely why the other locales
leaked, and the composed architecture covers every locale at once. A live read-only
`apply-brand-strings.php --dry-run` planned exactly five deletions (these three plus the two from S1-P1-02)
and wrote nothing.

### S2-P1-23 — Commit `39d3f056b` introduced an RTL regression
It replaced `{{ "OpenEMR Authorization"|xlt }}` with `{{ applicationTitle }} {{ "Authorization"|xlt }}`.
`Authorization` (cons_id 992) has 10 definitions and **no Arabic row**, where the retired constant had 5.
Word order is hardcoded English, so RTL renders `Thiqa الولوج`. **Correction to an earlier orchestrator
statement:** this commit was praised as "a better mechanism that destroys no translations" — the first half
holds, the second was incomplete. Recommended fix is a placeholder-bearing translatable key
(`xlt('%s Database Upgrade')` + `sprintf`), not bare juxtaposition.

> ### ⚠️ BLOCKING CONSTRAINT DISCOVERED 2026-08-24 — the recommended fix, applied as written, is a regression
>
> **Do not apply the placeholder-key fix without the carry-forward described below.** An implementation
> attempt was made this session, completed across all seven call sites, and then **deliberately reverted**
> after live catalogue counts showed it would silently drop existing translations. The working tree was
> restored exactly (`git status` clean); nothing was committed.
>
> **The seven sites are one defect class, not three findings.** A repo-wide template scan found the
> `{{ translatedPhrase }} {{ applicationTitle }}` juxtaposition in seven places across five templates —
> the four OAuth sites S2-P1-23 names, plus `templates/core/about.html.twig:4,10` (this is the
> `حول Thiqa` of **S2-P1-24**), `templates/insurance_companies/general_list.html.twig:4`, and
> `templates/product_registration/product_reg.js.twig:6`.
>
> **Why the naive fix regresses.** Changing the key at a call site changes what `xl()` looks up, and the
> new placeholder key has no definitions in any locale. Live counts (`lang_constants` joined to
> `lang_definitions`, this host, read-only):
>
> ```text
> constant_name           defs   lang_id=1 override   lang_id=22 (Arabic)
> About                     24            0                    1
> Authorization             10            0                    0
> Insurance Companies       33            0                    -
> Login                     34            0                    1
> OpenEMR                    8            1                    0
> OpenEMR Application        9            1                    0
> Welcome to OpenEMR         9            1                    0
> ```
>
> So `About` → `About %s` orphans 24 translations **at that call site**, `Login` → `%s Login` orphans 34,
> `Insurance Companies` 33, `Authorization` 10. Arabic specifically *loses* the `About` and `Login`
> translations it currently has. The finding's premise — that these sites are already unbranded in Arabic —
> holds only for `Authorization`; for the others the naive fix trades an RTL word-order defect for a
> translation loss across 10–34 locales. That is the RB-01 failure mode in miniature, and it is exactly what
> this corpus exists to prevent.
>
> **The safe design, specified.** Placeholder key **plus mechanical carry-forward**, where each locale's new
> pattern is its existing translation with `%s` inserted at the position the source uses today:
> `%s <translated>` where the name currently leads (`%s Authorization`, `%s Login`),
> `<translated> %s` where it currently trails (`About %s`, `Insurance Companies %s`). Rendering is then
> **byte-identical to today in every locale**, and the join point moves from PHP a translator cannot reach
> into catalogue data a translator can reorder. Nothing is authored and no grammar is fabricated.
>
> For the three brand-bearing keys (`OpenEMR`, `OpenEMR Application`, `Welcome to OpenEMR` — the S2-P1-22
> set) the carry-forward is the *existing* `TranslationCatalogueContract::neutraliseLegacyDefinition()`
> transform: replace the brand literal with `%s`. That path already works and is proven by S1-P0-13.
>
> **What blocks it.** The shipped contract subsystem is **single-key**:
> `TranslationCatalogueContract`, `TranslationContractSqlRenderer`, `TranslationCatalogueMigration`,
> `TranslationCatalogueMigrationCommand` and `TranslationFileCopyFromPriorRelMutator` are all built around
> one contract file with one `target_key` (`contracts/database-upgrade.json`). Closing S2-P1-22/23/24 needs
> that generalised to N contracts plus a `derive_from` rule (`source_key` + `placement: prefix|suffix`) so
> the ~110 carried-forward definitions are *derived mechanically at generation time* rather than hand-copied
> into JSON. Schema v2, renderer, migration, journal, release-prep regeneration, a fresh migration matrix and
> a disposable-database proof — the same shape and size as the S1-P0-13 arc that took four commits.
>
> **Also required by the same change, and easy to miss:** `TwigContainer` constructs its environment with
> `['autoescape' => false]`, so today's `{{ applicationTitle }}` in the three OAuth templates is emitted
> **unescaped**. Any composed replacement must apply exactly one explicit escaper (`|text` in HTML, `|attr`
> in an attribute), matching the compose-then-escape-once contract `sql_upgrade.php` already follows.
>
> **Recommended vehicle:** a Twig filter (`xlp`) that returns the composed value *unescaped* so the call
> site keeps choosing its own escaper. That was built and reverted with the rest; it is a ~15-line addition
> to `TwigExtension` and is not the hard part.

**Status: FIXED — VERIFIED (continuation Rev 22), together with S2-P1-22 and the `About` half of
S2-P1-24.** The design specified above was built. Contract schema v2 adds `derive_from`
(`source_key` + `placement`), the contracts *directory* replaced the single named file, and all ten call
sites now compose one translatable unit. Rendering is byte-identical to before in every locale — the
carry-forward is what makes that true — while word order is now catalogue data a translator can reorder.

```yaml
TASK/FINDING ID: PRE-09 / S2-P1-23 (+ S2-P1-22, + the About half of S2-P1-24)
Previous status: CONFIRMED; naive fix proven to regress and reverted at Rev 21
Current status: FIXED — VERIFIED
Call sites converted: 10 — oauth2-login (title + API login button), patient-select, scope-authorize, core/about (title + h1), insurance_companies/general_list, product_registration/product_reg.js, Zend Application layout, Zend Documents layout, Zend sendto
Neutral keys introduced: %s Authorization; %s Login; About %s; Insurance Companies %s; %s Product Registration; %s Application; Welcome to %s
Carry-forward mechanism: schema v2 derive_from for the five non-brand keys (prefix/suffix insertion of %s into each locale's existing translation); the existing neutraliseLegacyDefinition transform for the two brand-bearing keys, now also rendered as SQL
Why derivation and not literals: ~110 hand-copied strings across four source constants would freeze a snapshot of upstream translations and stop tracking them; the SQL derives from whatever currentLanguage_utf8.sql actually installed
Subsystem generalised: TranslationCatalogueContractSet loads every *.json sorted, rejects duplicate ids/targets and refuses derivation chains; sql_upgrade.php, TranslationCatalogueMigrationCommand and TranslationFileCopyFromPriorRelMutator all iterate the set
Schema v1 frozen: database-upgrade.json is unmodified and still renders its 28 explicit inserts with no carry-forward SQL, so no installed database's journal hash is invalidated
Disposable-database proof: schema openemr_prebrand_xlate_multi_20260824_174721; supplement applied twice, exit 0 both times; identical counts after the second run (15 constants, 53 definitions, 0 duplicate pairs, 0 orphans)
Derivation results observed live in that database: "%s Login" -> "%s تسجيل الدخول"; "About %s" -> "حول %s", "אודות %s", "Uber %s"; "%s Authorization" -> "%s Autorizacion", "%s Genehmigung"; "%s Application" -> "Aplicacion %s", "%s Anwendung"; "Welcome to %s" -> "Bienvenido a %s"
Guards proven in the same run: a source definition containing "%" was skipped (0 leaked); a case-only near-duplicate source constant was not matched (BINARY, 0 leaked); a legacy row without the brand literal was skipped (0 leaked)
Disposable database dropped: YES — confirmed absent; live openemr DB verified still holding its 7 saas_branding_* globals
Live database mutated: NO
Targeted results: translation suites 42 tests / 145 assertions / exit 0; brand-strings catalogue 46 / 290 / exit 0; composition contract 5 / 437 / exit 0; release-prep mutator 6 / 13 / exit 0
Lint/style: PHP lint 17/17 exit 0 (incl. the three .phtml layouts); PHPCS 19/19 exit 0; brand-strings.json parses
Live read-only tool proof: apply-brand-strings.php --site=default --dry-run planned exactly 5 deletions (the 3 newly retired plus the 2 from S1-P1-02), 0 already correct, no writes
brand-strings.json: english_overrides is now EMPTY; all five keys carry exact-value retirement metadata
Escaping: TwigContainer builds with autoescape => false, so the previous bare {{ applicationTitle }} was emitted unescaped. Every converted site now names exactly one escaper (|text in HTML, |attr in an attribute); a contract test fails on any |xlp without one.
Known behaviour, unchanged from S1-P0-13: a translator who removes the %s from a pattern makes compose() throw. The contract validates every checked-in definition and the derivation guarantees exactly one placeholder, but a hand-edited lang_definitions row is not protected.
Not-passes retained: two of my own assertions failed on key ORDER (the store returns sorted) and were corrected; a first version of the composition contract used '~' as both regex delimiter and literal, raising a PHP warning, and was re-delimited.
Next incomplete PRE-* task: PRE-09 / S2-P1-26, plus the second half of S2-P1-24 (session-language product-name selection for the shell title, WindowTitleBase and the Arabic logo variant)
```

### S2-P1-24 — The Arabic wordmark is stored but never rendered
`globals.saas_branding_product_name_ar = ثقة` is populated and module code to consume it exists
(`BrandingService.php:107-108`, `BrandAssetResolver.php:74,164-166`). The Arabic authenticated shell
(77,733 B, RTL stylesheets applied, UI chrome genuinely translated) still shows `<title>Thiqa</title>`,
`var WindowTitleBase = "Thiqa"`, `حول Thiqa` (verb translated, product name Latin), and a byte-identical
navbar with no Arabic logo variant.

**Partly diagnosed 2026-08-24.** The `حول Thiqa` symptom is **not** a separate defect: it is
`templates/core/about.html.twig:4,10` doing `{{ "About"|xlt }} {{ applicationTitle|text }}` — the same
bare-juxtaposition class as S2-P1-23, and two of the seven sites that scan found. It is therefore blocked
behind the same carry-forward work; `About` carries 24 definitions including a live Arabic one, so changing
its key naively would *remove* Arabic rather than fix it. See the blocking-constraint block under S2-P1-23.

**The `حول Thiqa` half is FIXED — VERIFIED (continuation Rev 22)**, as part of the S2-P1-22/23/24
workstream: `templates/core/about.html.twig` now composes `About %s`, and the `about-product-neutral-v1`
contract carries `About`'s existing translations forward by suffix derivation — proven on a disposable
database to produce `حول %s` (Arabic), `אודות %s` (Hebrew) and `Uber %s`. Arabic therefore still renders
`حول` and now places the product name where the Arabic pattern says, rather than where PHP said.

**The text half of the remainder is FIXED — VERIFIED (continuation Rev 25); the logo half stays OPEN and
is blocked on an asset that does not exist.**

`<title>` and `var WindowTitleBase` in `interface/main/tabs/main.php` now call a new core helper,
`xl_product_name()`, instead of reading `openemr_name` unconditionally. It returns
`saas_branding_product_name_ar` when the session language is Arabic and `openemr_name` otherwise,
degrading to `openemr_name` when no Arabic name is configured or the branding layer is not installed at
all.

**The predicate is the language, not the direction, and that distinction is the whole design.**
`lang_languages` marks **four** locales RTL — Hebrew (7), Arabic (22), Persian (37), Urdu (51) — and an
Arabic wordmark is correct for exactly one of them. Keying on `lang_is_rtl`, the obvious shortcut, would
put Arabic script in front of Hebrew and Persian users: a worse error than the one being fixed. A contract
test asserts the `'ar'` comparison and forbids `lang_is_rtl` **inside this function's body** (the file's
own `getLanguageDir()` uses it correctly for its own purpose).

**Still open: the Arabic logo variant**, and it is not a code problem. §13 records that **no dedicated
Arabic wordmark exists**, that `Entity.md` forbids deriving one from the Latin artwork, and that KG-05
requires an approved asset rather than a fabrication. There is nothing to select between until that asset
is commissioned, so this sub-item is blocked on an Owner/asset decision, not on implementation.

```yaml
TASK/FINDING ID: PRE-09 / S2-P1-24 (variant-selection half)
Previous status: OPEN — scoped, not built
Current status: TEXT SURFACES FIXED — VERIFIED; logo variant BLOCKED on a non-existent approved asset
New helper: xl_product_name() in library/translation.inc.php, with xl_session_language_id() and getLanguageCode()
Surfaces converted: interface/main/tabs/main.php <title> and var WindowTitleBase
Predicate: lang_languages.lang_code === 'ar'; NOT lang_is_rtl (4 RTL locales, only 1 Arabic)
Degradation: no Arabic name configured, branding layer absent, or any non-Arabic session -> openemr_name unchanged
Live smoke test: translation.inc.php loads on every request, so the login page was re-fetched after the change - HTTP 200, 9,165 bytes, byte-identical to the recorded baseline
Targeted: ProductNameCompositionContractTest 16 tests / 330 assertions / exit 0
Lint/style: PHP lint 2/2 exit 0; PHPCS 3/3 exit 0
Not-pass retained: the first version of the contract asserted `lang_is_rtl` was absent from the whole file, which failed because getLanguageDir() legitimately uses it; the assertion was scoped to the function body
Deliberately not changed: main.php:404 and :573 also read openemr_name, but they feed other template contexts that were not analysed here; converting them without that analysis would be guesswork
Database / browser touched: NO / NO (read-only lang_languages query to confirm the four RTL locales)
```

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

**Status: FIXED — VERIFIED. Class B at continuation Rev 23, Class A at Rev 24. The finding's own counts are
corrected below.**

**Measurement corrected.** The original figures came from `git grep -F -f`, which matches a key as a
*substring* of a line — so every long key's line also counted as a hit for the bare key `OpenEMR`, inflating
it to "52 call sites". Re-derived by extracting the actual quoted literal handed to each translation call
and comparing it exactly (4,105 files scanned; `vendor/`, `node_modules/`, `.claude/`,
`oe-module-claimrev-connect/` and `tests/` excluded per the §10 hygiene rule):

```text
CLASS A  has a lang_constants row (a carry-forward contract can neutralise it)
         20 distinct literals / 22 call sites
CLASS B  NO lang_constants row (no override can ever reach it; source edit only)
         22 distinct literals / 25 call sites
DEAD     row exists, never reached                                    49
```

So the structural half of the finding is **larger** than recorded — 22 uncatalogued literals, not the ~7 it
listed — and the reachable-and-branded half is 20, not 23.

**Class B is fully converted (14 literals / 17 call sites).** These are the safe ones by construction: with
no catalogue row there is nothing to orphan, so they need no contract at all. A new PHP helper `xlp()`
(`library/translation.inc.php`) mirrors the Twig filter — translate the pattern, compose `openemr_name`,
return unescaped so the call site escapes once. Converted: the six `globals.inc.php` labels (theme
selection, FHIR system scopes, the OAuth/FHIR hostname, the logo tooltip), `portal/index.php` title,
`main.php`'s website link, both `AuthUtils` block notifications, the weno restart button, and all six OAuth
scope descriptions across `ScopeRepository` and `ServerScopeListEntity`.

**Deliberately NOT converted, and now locked by a test so a later sweep cannot "finish the job":**

| Literal / site | Why it is preserved |
|---|---|
| `rwt_2026_report.php` ONC + Foundation text (×2) | Names the **OpenEMR Foundation** and an ONC certification programme. Renaming would assert something untrue — that a differently-named foundation should receive the report, or that this fork holds that certification. |
| `product_registration_modal.html.twig` Foundation text (×3) | Same: the telemetry consent names the real upstream Foundation, and the endpoint really is theirs. |
| `register-app.php` "OpenEMR community form" | Points at the upstream community, not this product. |
| `questionnaire_assessments.php:337` copyright disclaimer | **JavaScript** `xl()`, not PHP — a different mechanism needing a JS-side composition helper. |
| `globals.inc.php:4443` theme-select description | Contains **two** `OpenEMR` occurrences; `ProductContextTranslation` accepts exactly one placeholder by design. Needs either a two-placeholder contract or a rewrite. |

**Class A is now also CLOSED (continuation Rev 24).** All 20 literals have a schema-v2 contract with
`legacy_keys: {"<old key>": "OpenEMR"}` and a target that is the same sentence with the literal replaced by
`%s`, and all 22 call sites are converted — including the highest-exposure member,
`interface/login_screen.php:29`, the login page.

**A latent defect had to be fixed first, and it was already shipped in Rev 22.** The two carry-forward paths
disagreed about a translation that never named the product: the generated installer SQL skipped it
(`LOCATE(literal, definition) > 0`), while the PHP upgrade migration **threw**. Same contract, different
catalogue on fresh install versus upgrade — the divergence S2-P0-21 exists to prevent. Not hypothetical:
every brand-bearing key has 1–4 such rows, and `OpenEMR Application` / `Welcome to OpenEMR` (shipped at
Rev 22) have two each, so those contracts **would have aborted a real upgrade**. It was caught by a contract
test written for this task, not by inspection.

`MissingIdentityPolicy` now makes the choice explicit per contract: `fail` remains the default because
silently losing a locale is worse than a loud stop, and `skip` is the opt-in all 22 legacy contracts
declare. The cost is stated rather than hidden — those specific locales fall back to the neutral English
pattern at that one call site, because a sentence that never mentioned the product has no placeholder
position to infer and guessing one produces a subtly wrong string in a language the author cannot read.

```yaml
TASK/FINDING ID: PRE-09 / S2-P1-26
Previous status: CONFIRMED — leak surface + uncatalogued class, counts unverified
Current status: FIXED — VERIFIED (Class B at Rev 23; Class A at Rev 24)
Measurement method: exact quoted-literal extraction from xl/xlt/xla/xlj calls and "..."|xl* Twig filters over 4,105 files; documented exclusions applied
Counts re-derived: Class A 20 literals / 22 sites; Class B 22 literals / 25 sites; dead catalogue entries 49
Original counts corrected: 23 reachable -> 20 Class A; 46 dead -> 49; "52 sites for the bare OpenEMR key" was a substring artefact and is withdrawn
Class B converted: 15 literals / 18 call sites across 8 files
Class B preserved with reasons: 6 literals (Foundation / ONC / upstream community), 1 JS-side, 1 two-placeholder
New helper: xlp() in library/translation.inc.php, composing through ProductContextTranslation and returning unescaped
Targeted: ProductNameCompositionContractTest 13 tests / 474 assertions / exit 0
Lint/style: PHP lint 8/8 exit 0; PHPCS 8/8 exit 0
Live database touched: NO (read-only SELECT to dump the 69 brand-bearing keys)
Class A closed at Rev 24: 20 schema-v2 legacy contracts and all 22 call sites, including interface/login_screen.php:29. A latent defect was found and fixed doing it - the installer SQL skipped a translation lacking the brand literal while the PHP upgrade migration threw, so one contract produced different catalogues on install versus upgrade; the two contracts shipped at Rev 22 would have aborted a real upgrade. MissingIdentityPolicy makes the choice explicit (fail = default, skip = declared opt-in).
Remaining work, specified: a JS-side composition helper for questionnaire_assessments.php:337; a decision on the one two-placeholder string (globals.inc.php:4443)
Gate budget change: composer.json config.process-timeout raised to 1800. The 253-test gate measured 457-607 s here once the src/ edits invalidated the PHPStan cache, and CI always pays that cold-cache cost; Composer's 300 s default killed a PASSING suite and produced a red indistinguishable from a real failure. Asserted by BrandingCiContractTest. The per-script "@putenv COMPOSER_PROCESS_TIMEOUT" form was tried first and does NOT work - Composer reads the timeout at startup, before script steps run.
Next incomplete PRE-* task: the variant-selection half of S2-P1-24
```

### S2-P1-25 — Brand manifest release gate was RED (4 files) — ✅ **FIXED, commit `45e9eb4f3`**

> **STATUS: FIXED_AND_REVERIFIED (Rev 3).** Gate restored to `123/123 verified`, **exit 0**, manifest line
> count preserved at 123. Reason recorded in `docs/branding-production/12-release-verification.md` Revision 5.
> The *process* gap it exposed was subsequently fixed as **S1-P1-03** in continuation Rev 11: the verifier now
> runs inside the canonical `composer branding-ci` workflow gate. Original finding retained below as the record.

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
Remaining risks at this verification point: S1-P1-03 was still open; it was fixed and locally verified in Rev 11.
Next task at that verification point: E2 / S1-P0-01 invariant verification for aebcfdfc5 and 26c32fcb3.
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

**Status: FIXED — VERIFIED (continuation Rev 21).** Re-derived on disk: 18 top-level `.css`, 19 directory
entries, 10 files in `misc/`, 28 files in total. The 18 are the 14 theme outputs plus exactly the 4 shells
both documents were already listing (`style.css`, `directional.css`, `ajax_calendar_ie.css`,
`jquery.autocomplete.css`). Both the runbook bullet and changes.md row 076 now say 18/4 and carry a dated
note explaining the arithmetic, so the correction cannot be mistaken for a new count.

### S1-P2-12 — Module-directory literal hand-typed five times
Canonical: `BrandingService.php:60-61` (`TOKEN_STYLESHEET_RELATIVE_PATH`), `Bootstrap.php:74`
(`TWIG_NAMESPACE`, referenced symbolically everywhere — safe). Non-canonical duplicates that fail
**silently**: `Bootstrap.php:117` (dark-logo web path), `Bootstrap.php:246` (duplicates the service constant),
`tools/branding/install-assets.php:497` (third independent copy of the dark-logo subpath), plus
`Bootstrap.php:208`/`:244`/`BrandingService.php:57` for the token-JSON path.
Counter-example worth copying: `tools/branding/src/CliOptions.php:27` `DEFAULT_FONT_URL_BASE` is a single
parameterised source of truth.

**Status: FIXED — VERIFIED (continuation Rev 21).** `Config\ModulePaths` is the single owner: every
PHP-side path derives from one `DIRECTORY_NAME`. `Bootstrap::TWIG_NAMESPACE`, the dark-logo filesystem and
web paths, the tenant-branding root, the profile path, the template root and both `BrandingService` path
constants are now derived rather than retyped. The two public `BrandingService` constants keep their names
because `public/branding-tokens.php` and the module documentation reference them.

Three consumers **cannot** share a PHP constant and are guarded instead of wired, by
`ModulePathContractTest` inside `composer branding-ci`: `tools/branding/install-assets.php` (build-time
tool under the `OpenEMR\Branding\` prefix; the module's prefix is not in the root autoloader),
`.gitignore` (the most expensive silent failure — after a rename it stops matching and the next commit
sweeps in every tenant's materialised output as source), and the Twig rule's operator tip, already pinned
by PR-36.

**One inherited claim corrected:** `webpack.themes.js` appears on the §10 rename-surface list, but reading
it shows it references the SCSS source tree (`oe-styles/style_thiqa_*.scss` → `interface/themes/thiqa/`)
and **never the module directory**. It is therefore not part of this coupling and is not guarded here. A
first version of the contract asserted it and failed, which is how this was caught.

### S1-P2-14 / PRE-ORCH-01 — Sibling worktree outside exclusion coverage
`.git/info/exclude:11` excludes `**/.claude/worktrees/`, covering three worktrees. It **cannot** cover
`G:/My Drive/OpenEMR.worktrees/sds` — a sibling directory outside the repo root, registered as a git
worktree at `631f2b38c` (pre-branding baseline). `RebrandingBugs.md` §10's measurement-hygiene rule names
only `.claude/worktrees/`. Any scan rooted at `G:\My Drive\` ingests a full pre-branding codebase copy.
Must be added to: measurement hygiene · exclusion documentation · Scan-3 rollback register.

**Status: FIXED — VERIFIED (continuation Rev 21).** `RebrandingBugs.md` §10 item 3 now reads "four agent
worktrees, and only three of them are inside the repository", shows the sibling in the `git worktree list`
output, and states plainly why no gitignore mechanism can ever reach it — an exclude file governs only
paths inside its own working tree, so the sibling is invisible to exactly the tools (`git status`, a
repo-rooted ripgrep) that make the other three safe. The hygiene rule now names
`G:/My Drive/OpenEMR.worktrees/` as a **path** exclusion alongside `.claude/worktrees/`, `vendor/`,
`node_modules/` and `oe-module-claimrev-connect`, and adds the standing instruction to run
`git worktree list` before trusting any repository-wide figure, since it is the only command that reveals
the sibling and the set can change between sessions. The sibling is registered to branch `agents/sds` and
is left in place: removing another agent's worktree is not this programme's call. Re-derived live —
`git worktree list` returns 5 entries, 4 of them worktrees.

### S1-P2-16 — Architecture doc undercounts commands 3 → 6
Actual: `apply-profile`, `backup`, `materialise`, `provision-report-acl`, `seed-demo`, `verify`.
The three omitted own the **most** persisted state (gacl ACL objects, ~19 clinical/demo tables, backup
artefacts) — none of it branding state. The module has accreted a deployment/ops toolkit under a branding
namespace, so "rename the branding module" has a much wider blast radius than the doc implies.

**Status: FIXED — VERIFIED (continuation Rev 21).** `architecture.md` now says six in all three places it
counted: the Plane 2 diagram box (which listed only three by name), the Plane-boundary prose at §1 ("the
three console commands are registered only on `CommandRunnerFilterEvent`"), and divergence row §8.4 (which
said "Three commands exist"). Row 8.4 also now carries the consequence rather than just the count — that
the three unlisted commands own the module's heaviest persisted state and none of it is branding state, so
the rename blast radius is materially wider than either the plan or the previous row implied. Verified
against `Bootstrap.php`, which registers all six on `CommandRunnerFilterEvent`.

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

**Correction K — "the Latin surface has no real weight axis" (S2-P1-20) is WITHDRAWN.** Inter ships as a
**variable** face: `fvar`, `gvar` and `HVAR` are present in all four Inter WOFF2 files (21 tables); IBM Plex
Sans Arabic carries none of them (17 tables) and is genuinely four static faces. One variable file declared
at `font-weight: 400 700` renders 500 and 600 correctly, which is exactly what the shipped
`_typography.scss` and all 8 compiled themes do. **RB-22's "FIXED — rebuilt and verified" is accurate.** The
four-identical-hashes *observation* is true and reproduced exactly; the inference drawn from it was not.
Do not reassert it. What remains true: three unreferenced duplicate binaries still ship (~145 KB of deployed
disk), and until Rev 20 nothing verified that a *static* family's declared weights map to distinct faces.

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

### SCAN2G — Tier-2 effectiveness (initially confirmed; S1-P0-09 now FIXED — VERIFIED)
See §5 S1-P0-09. Additional historical findings:
- **Static token CSS is directly fetchable over HTTP:** `GET .../public/branding/default/tokens-light.css`
  → 200, `text/css`, 1522 B. **No `.htaccess` protects the path.**
- RB-04/D-8: route (a) PHP endpoint is the only route ever linked; route (b) files are written
  **unconditionally**, even with an empty overlay; only `FilesystemStylesheetProbe::isPresent()` reads them,
  and only via `is_file()` (never opens). `.gitignore:73` confirmed matching via `git check-ignore -v`.
- `verify` inconsistency root: `BrandingHealthCheck` cross-references the revision (route a) against
  file-existence on the unserved route (b) — "measuring the wrong thing".
- Initial failure proof and the repaired light/dark acceptance chain were both executed and exactly restored — see §16.

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

## 15B. SCAN-3 RESULTS (adversarial, independent agents — dispatched 2026-08-24)

> **Agent reports are evidence, not verdicts.** Every claim below was re-checked by the orchestrator
> before being recorded, and one was refuted. An adversarial finding that is wrong is still a finding
> about the audit, not about the code.

### Scan-3A — translation subsystem: **COMPLETE. Found a P0 that would have wedged every upgrade.**

**S3-P0-28 (NEW, P0) — FIXED, VERIFIED.** The SQL and PHP carry-forward paths disagreed on
*precedence*, and the disagreement was reachable on real shipped data.

The generated installer SQL inserts explicit contract definitions first and anti-joins the legacy
carry-forward against rows already present, so **explicit wins**. `desiredDefinitions()` did the
opposite — an unconditional `$desired[$languageId] = $candidate` — so **legacy won**. On
`database-upgrade.json` those differ for French: the contract declares
`Mettre à jour la base de donnée de %s`, while the upstream seed's legacy row is
`…de donnée d'OpenEMR`, neutralising to `…de donnée d'%s`. Verified directly in
`currentLanguage_utf8.sql`.

Consequence, as Scan-3A demonstrated by execution: a site installed by this branch's installer gets
the SQL value; its first `sql_upgrade.php` run computes the PHP value, finds a mismatch, and throws
`Conflicting target definition for lang_id 8`. That call site has **no try/catch**, and the version
row is bumped *after* it — so the upgrade dies mid-run with DDL already applied and the version never
advanced, and re-running throws again. Unrecoverable without hand-editing `lang_definitions`. It does
not reproduce on this dev database, which has no `%s Database Upgrade` row, so local testing would
have missed it.

**This was pre-existing, not introduced by the Rev 22 work** — the PHP path has overwritten since
S1-P0-13, and adding v2 legacy SQL only made the asymmetry reachable in more contracts.

Fixed by making one precedence rule hold in both paths (`??=`): an explicit contract definition is
authoritative, carry-forward fills gaps only. Three regression tests pin it — explicit wins, gaps
still fill, and an SQL-installed site upgrades to `already_current`. Negative control: reverting to
`=` reproduces `RuntimeException: Conflicting target definition for lang_id 8` and fails all three;
source restored by hash.

**S3-P2-29 (NEW, P2 latent) — FIXED.** The SQL legacy statement used a bare `REPLACE()` with no
composability check, while the PHP path validates every candidate through `compose()`. A source
definition naming the product **twice** would therefore install cleanly as a two-placeholder pattern
and then fatal whichever page rendered it. No such row exists today, but the derivation deliberately
re-derives from upstream translations as they change. The SQL now requires the literal to occur
**exactly once**. Proven on a disposable database seeded with a deliberate `OpenEMR … OpenEMR` row:
supplement applied twice, 47 definitions, 0 duplicate pairs, **0 two-placeholder definitions**.

**S3-P1-30 (NEW, P1) — ACCEPTED, NOT FIXED; the cost is now quantified.** Scan-3A measured what
`on_missing_identity: "skip"` actually costs: **22 contracts drop at least one locale, and Arabic —
this fork's primary target — loses 8 previously-translated strings**, including
`interface/main/backup.php:505,538`. Rev 24 described this trade honestly but did not measure it; the
number belongs on the record. The only alternative remains authoring per-language definitions, which
this programme will not fabricate. `database-upgrade.json` shows the pattern for whoever supplies
them: explicit definitions for exactly the affected locales.

**S3-P1-31 (NEW, P1) — FIXED, VERIFIED at Rev 29 (`59d5c14df`).** `interface/language/lang_definition.php`
inserted and updated translations with no placeholder validation, so an admin editing `%s Login` and
dropping the `%s` made `compose()` throw and took the OAuth2 login page to a 500 for that locale. 3A was
right that it deserved its own change, and it got one.

**The classification is the part that needed care, not the rejection.** A guard that refused too much
would be its own defect: `Atropine 1%`, `Pct (%) of rows` and `Use % alone in a field to just sort on
that column` are real catalogue constants, and demanding a placeholder in their translations would be
worse than the bug it replaced. So the classifier is `compose()` itself rather than any substring test —
the same code that will later render the constant decides whether it is a pattern. A bare `%`, a `%)`
and a trailing `%` all raise, so they fall through as "not a pattern".

Measured rather than assumed, against the live 13,235-constant catalogue and the shipped contract set:

```text
catalogue constants containing '%'                16   -> 0 classified as patterns  (0 false positives)
shipped contract target keys                      28   -> 28 classified as patterns (0 false negatives)
```

Both write paths are guarded — covering only the insert loop would have left the more common operation,
correcting an existing translation, wide open — and rejections are reported by constant name rather than
dropped, because a guard that silently discards an edit is worse than the error it prevents. Negative
control removed the update-path guard: the named test failed, exit 1; source restored and 23 tests / 64
assertions passed. Also fixed in passing: `$go` was never initialised, and the guard turns "posted a
change, wrote nothing" from an edge case into a routine outcome.

**Verified sound by 3A** (independent confirmation): no code path deletes a pre-existing translation;
forward→rollback→forward→rollback replay is byte-identical; drift is detected rather than overwritten;
the store uses `FOR UPDATE` on every decision read; contract-set invariants (duplicate id, duplicate
target, derivation chains) all reject; the deployed supplement is not stale; and every Twig call site
escapes exactly once despite `autoescape=false`.

### Scan-3B — CI gate: **COMPLETE. Gate is not lying today; six string-shaped assertions hardened.**

3B's bottom line is the useful part: the gate passes honestly right now, and its hardest attack
surface — the namespace-scoped guardrails — it could not defeat. The exposure was that several
assertions checked that a guard's *text* was present rather than that the guard had *effect*.

Fixed this session:
- **P1-1** the gate is pinned to `matrix.php-version == '8.2'` and nothing asserted 8.2 was in the
  matrix. Deleting that one line switched the whole branding gate off with every leg green.
  Now cross-asserted; negative control removed `'8.2'` and the test failed with the matrix listed,
  then the workflow was restored by hash.
- **P1-2** `|| true` was forbidden in the composer script but not in the workflow step. Now blocked
  there too, along with `|| :`, `; exit 0`, `|| echo` and `set +e`.
- **P1-4** manifest verification was strictly manifest→disk, so deleting a line un-guarded a file and
  reported one *more* clean entry. A reverse sweep now requires every file under `brand/` to be
  covered (manifests and `.gitattributes` excepted as metadata).
- **P1-5** `--fail-on-empty-test-suite` fires only when the *whole* run is empty, so a gated
  directory could lose every test and stay green. Each gated path must now exist and contain at
  least one `*Test.php`.
- **P2-1** `assertStringNotContainsString('paths:')` never blocked `paths-ignore:`, which skips the
  workflow entirely. Both spellings now blocked.

Recorded, **not** fixed — these are governance/CI-infrastructure decisions rather than defects:
- **P1-3 / P2-3** the manifest covers `brand/**` sources but **not the artefacts the product actually
  serves** — deployed logos, favicon, fonts under `public/`, and compiled `public/themes/*.css`.
  Those can be replaced with anything while the gate prints `123/123 verified`. Closing this means
  extending the manifest's scope, which changes a documented 123-entry re-issue discipline.
- **P1-6** the Q77 deployed-theme check (`BrandingGovernanceGuardTest`) calls `markTestSkipped` when
  `public/themes/` is absent, CI has no webpack step, and the gate does not pass `--fail-on-skipped`
  — so the check a locked decision depends on runs in **no** CI job while reporting green.
- **P2-2** the four guardrails are enforced by the separate PHPStan workflow, and nothing asserts the
  module directory is inside PHPStan's analysed paths; adding it to `excludePaths` would make all
  four inert with every branding test still green.
- **P2-4** the workflow triggers only on `master` and `rel-*`, so the working branch gets no branding
  gate until a PR targets one of those.

### Scan-3C — brand leaks: **COMPLETE. Confirms the composition work, and finds the surface it does
not reach.**

3C's central finding is the sharpest available critique of this session's own architecture:
**`xl_product_name()` has exactly two callers**, both in `interface/main/tabs/main.php`, while there
are ~30 raw `openemr_name` reads — *including `xlp()` itself and the `|xlp` Twig filter*. So the two
functions built to compose the product name into translated prose both bypass the Arabic resolver. An
Arabic session gets Arabic chrome with a Latin wordmark embedded in it everywhere except the main
shell's browser tab. That is a real, coherent gap, recorded as **S3-P1-32** and now
**FIXED, VERIFIED at Rev 29 (`f18f75080`)**. Both entry points resolve through `xl_product_name()`.

Three things about that repair are worth keeping:

- **The guard that should have caught it was the same false-green shape Scan-3B catalogued.**
  `assertStringContainsString("getString('openemr_name')", $source)` was *file-wide*, so it kept
  passing after the read moved to the neighbouring function in the same file. It now reads one
  function's own body, with comments stripped through `token_get_all()` so a comment recording the fix
  can neither satisfy nor break it.
- **The claim is proven by execution, without a database.** `xl_product_name()` memoises per request,
  so resolving once under one configured name, changing the name, then composing tells a routed
  `xlp()` (returns the first value) from a bypassing one (returns the second). PHPUnit's
  `RunInSeparateProcess` was tried first and **hangs on this host**, so the probe runs in-process and
  restores the bag in a `finally`; its memo residue is documented in the test rather than hidden.
  Negative control reverting both entry points: 3 failures / exit 1, including the executable one.
- **An inherited claim was corrected.** The resolver's `catch (\RuntimeException)` was documented as
  protecting the *installer*. It does not: `Installer::insert_globals()` never reaches it, because
  `saas_branding_product_name_ar` does not exist yet and the `has()` check returns first — verified
  cold, an unpopulated bag returns `''` without touching session or database. What it *does* protect is
  `sql_upgrade.php:551` and `sql_patch.php:74`, which require `globals.inc.php` **after** echoing and
  flushing; starting a session there raises `RuntimeException: Failed to start the session because
  headers have already been sent`, reproduced directly. Uncaught, that aborts an upgrade mid-run — the
  S3-P0-28 shape. A database failure is explicitly not guarded, because `sqlQuery()` ends in
  `HelpfulDie()`'s `exit(1)` and no catch can reach it.

Also from 3C: the questionnaire liability disclaimer named the wrong legal entity (**S3-P1-34**,
Rev 23's one *mechanical* exclusion because its literal was an argument to the browser-side `xl()`) —
**FIXED, VERIFIED at Rev 29** by composing in PHP and emitting through `js_escape()`; the literal has no
`lang_constants` row, so nothing is orphaned. Still open: `setup.php` (**S3-P1-33** — see the corrected
scoping in §1, which supersedes the "entirely unbranded" reading: it is a *mixed-brand* installer with
10 hardcoded `Thiqa` literals beside ~20 `OpenEMR` ones, and it runs before the database exists) and a
P2 tail of eye-exam titles, orders help text, portal metadata, a SMART JSON `publisher`, and an inactive
`*OpenEMR` apps row.

3C independently confirmed clean: the login page (1 match, the session cookie), FHIR metadata (0),
OIDC discovery (0), `admin.php`, `templates/login/**`, and `library/js/*.js`.

> **One observation from 3C that is outside branding and should not be lost:** it reports `admin.php`
> rendering its full site table **without authentication**. That is a security question, not a
> branding one, and it is recorded here only so it is not dropped — it needs its own investigation.

### Scan-3D — documentation truthfulness: **COMPLETE. Corpus held up; three real defects, all fixed.**

3D could not falsify a single "FIXED — VERIFIED" status, and independently confirmed all 24 cited
commit hashes, the patch-record arithmetic (51→59→67→76), the live login-page byte count, the 13,235
`lang_constants` figure, the four RTL locales, and the gate at 257 tests / 3,338 assertions.

Fixed from its report:
- The PRE-09 ledger row said **"IN PROGRESS — 13 items verified"** while the evidence cell in the
  same row listed 18 ✅ and concluded PRE-09 was complete. The headline status of the whole
  remediation programme was stale by five items and by its own conclusion.
- `architecture.md` cited `Bootstrap.php:97-100` for `GlobalsInitializedEvent`; those lines are
  docblock prose and the real registration is `:107` — drift from the S1-P2-12 refactor.
- `architecture.md` cited `TokenKey.php:161-209`; actual `:164-212`. (Its substantive claim,
  "exactly 11 overridable keys", 3D counted and confirmed.)
- **An arithmetic error in this checkpoint's own Rev 23**, which 3D flagged without being able to
  re-derive: Class B was written as "15 literals / 18 sites" converted, but 15 + 6 preserved + 2
  excluded = 23 against a Class-B total of 22, and Rev 26 said 14. Resolved from the source data:
  **14 literals / 17 sites** converted, 6 preserved, 2 excluded = 22. Corrected.

### Scan-3E — rename blast radius: **COMPLETE. One new P1, one correction against the orchestrator's
own work, one refuted claim.**

**S3-P1-27 (NEW, P1) — the live database has none of the neutral translation keys, so this instance
is currently rendering English where it used to render Arabic.**

Independently confirmed by direct query:

```text
lang_definitions containing 'Thiqa'                     27
lang_id = 1 (English) override rows still present        5   (all five marked "retired")
constant 'Thiqa Database Upgrade' still present          1   (cons_id 13235)
constants matching '%s'  (the 28 contracts' targets)     0   <-- none of them exist here
```

The contracts, the generated supplement and the journalled migration are all correct and proven on
disposable databases — but **this** database has been through neither a fresh install nor
`sql_upgrade.php` since they landed, so no neutral key exists in it. Today, a call site composing
`About %s` finds no row, falls back to the key's own English text, and an Arabic session sees
`About Thiqa` where it previously saw `حول Thiqa`. The old constants still hold their translations,
now unreferenced.

This is **not** a design defect — the carry-forward exists precisely so nothing is lost — it is an
**unapplied migration**. It is also the sharpest fresh-install-versus-upgrade divergence currently
live: a fresh install seeded from today's artefacts is correct; this upgraded instance is not, and
stays wrong indefinitely until the migration runs.

**Required operational step, not yet performed:**

```powershell
C:\openemr-stack\php\php.exe bin\console openemr:translation-catalogue-migrate
C:\openemr-stack\php\php.exe tools\branding\apply-brand-strings.php --site=default   # retires the 5 English overrides
```

Both are transactional and journalled, and the first supports `--rollback`. **Deliberately not run by
this session:** every task in this programme has held to "live database mutated: NO", and mutating the
demo tenant is an operator decision with an audience. The dry run planned exactly five deletions and
wrote nothing.

**Correction against the orchestrator's own work (accepted).** `ModulePaths`' docblock claimed
`webpack.themes.js` and `.gitignore` were both pinned by `ModulePathContractTest`. Only `.gitignore`
is; the webpack assertion was deliberately removed when webpack turned out never to reference the
module directory, and the docblock was not updated to match. A doc overstating its own test coverage
is exactly the defect class this programme exists to catch. Fixed.

**Refuted (do not reintroduce).** 3E reported that `style_thiqa_light.scss:73`'s comment —
"`--thiqa-*` plus the `--oe-*` compatibility aliases" — was stale because "no `--thiqa-` var is
emitted". False. Counted as occurrences rather than matching lines: `_css-variables.scss` emits
`--thiqa-*` 86 times and `--oe-*` 27; compiled `style_light.css` carries 43 and 20. Those are the
compatibility aliases S1-P0-09 deliberately retained at Rev 8. The comment is accurate.

**Already remediated, which 3E did not know.** Its recommendation to "add an assertion that the rule's
`MODULE_NAMESPACE` equals the module's actual PSR-4 prefix" is `ThiqaBrandingGuardrailScopeTest`,
added at Rev 20 — it derives the namespace from the module's own composer.json, asserts all four rule
constants against it in both directions, and pins the fixture namespaces. The hazard 3E describes is
real and was closed before it looked.

**Corroborated (independent confirmation of existing records):** the `modules.mod_directory`
self-disable (§9), the untracked font/theme paths (§9), the `Thiqa Database Upgrade` catalogue key
(S1-P0-13), and the safe raw literals in §14.

**Genuinely new and worth keeping — the Arabic substring trap.** A naive
`REPLACE(definition,'ثقة',…)` sweep during a rename would corrupt unrelated Arabic UI text: `ثقة` is a
substring of `منبثقة` ("popup"). Verified — all four rows matching `%ثقة%` are false positives
("Enable Clinical Reminder Popup", "Popups", "Pop ups need to be enabled…", "View events in a popup
window?"), and **zero** rows contain the brand itself. The Arabic product name lives only in
`globals.saas_branding_product_name_ar`, so an Arabic catalogue sweep is pure risk with no benefit.

**Also new: console command names are an out-of-repo coupling.** Seven `thiqa-branding:*` command
names are referenced by operator runbooks, cron entries and systemd units that live outside this
repository. 3E checked `docs/evidence/ubuntu-infra-scripts/` and found none of the eight scripts
currently invoke one, so live infrastructure is unaffected today; the exposure is operator procedure.

---

## 15C. POST-SCAN-3 FINDINGS AND RE-VERIFICATION (2026-08-25)

> Everything in this section was established **by execution on 2026-08-25**, not inherited from an
> earlier revision. Where it contradicts an earlier claim, both readings are kept — the corrections
> register in §8 exists because a checkpoint that quietly improves its own past is not an audit trail.

### A-01 / A-02 / A-03 — landed at `5202b0253`, recorded here

Found by an independent audit that drove a real Arabic session against the running stack instead of
reading the corpus. All three had survived four scans **because every guard checked strings that were
already on a list**, which is the structural point and the reason A-03 exists.

- **A-01** — the authenticated shell's user menu rendered `حول Thiqa`: phrase translated, wordmark
  Latin, word order decided in PHP where no translator can reach it. Exactly what S2-P1-23 exists to
  prevent, on the most-visited surface in the product. It survived because the juxtaposition guard's
  regex named only `applicationTitle`, and `interface/main/tabs/main.php` passed the same value in as
  `openemr_name` — one value, two spellings. The alternation now covers both, and a second guard
  asserts that **no renderer passes a raw product name into template scope at all**, closing the door
  one step earlier than a shape check can.
- **A-02** — the last unconverted leak, in the questionnaire theme setting's help text. It had a
  stated mechanical reason to survive: it named the product twice, and `ProductContextTranslation`
  accepts exactly one placeholder. Rewritten to name it once rather than relaxing that invariant.
  No `lang_constants` row, so nothing is orphaned.
- **A-03** — the structural gap behind both. `BrandLeakSurfaceContractTest` now re-derives the leak
  surface with PHP's tokenizer on every run and asserts it equals exactly the six preserved
  references (OpenEMR Foundation, upstream community, ONC certification). **It fails in both
  directions**: an unlisted leak fails, and a preserved entry that matches nothing fails as stale.

Evidence at the commit: negative control reintroducing the juxtaposition failed 2 tests, exit 1, then
restored; 23 tests / 422 assertions and 3 tests / 4 assertions, exit 0; the live Arabic menu moved from
`حول Thiqa` to `About ثقة`; English shell byte-identical at 69,732 bytes. Live database mutated: no.

### SKY-F01 — Arabic branding selected by DIRECTION instead of LANGUAGE — **FIXED — VERIFIED** (`02bcae75c`)

**Original claim (preserved, and it was too broad).** That an RTL non-Arabic session could receive an
Arabic product name, an Arabic tagline and Arabic logo alt text on the rendered login page.

**Narrowing, then the verified truth.** The full runtime trace was reconstructed rather than either
version being accepted:

| Surface | Selector honoured? | Rendered? | Verdict |
|---|---|---|---|
| `tagline($arabic)` | **No** — argument ignored | n/a | **REFUTED.** Deliberate and documented: one tagline global, no Arabic variant to choose between. Inventing a transliteration would be worse. |
| `brandProductName` | Yes | **No** — no template reads it | **LATENT**, not live |
| `brandTagline` | n/a | **No** — no template reads it | **LATENT**, not live |
| `primaryLogoAlt` / `secondaryLogoAlt` | Yes | **Yes** — `templates/login/partials/html/primary_logo.html.twig:15,20` | **LIVE DEFECT** |

**The defect.** `LoginTemplateListener:150` read `BrandingServiceInterface::isRtl()` — which resolves
from the `rtl_` stylesheet prefix (`ThemeResolver::isRtlStylesheet()`) that `interface/globals.php:566-586`
sets from `getLanguageDir()`. `lang_languages` marks **four** locales right-to-left — Hebrew, Arabic,
Persian, Urdu — so a Hebrew login page was served `alt="شعار ثقة"`: Arabic script announced to a Hebrew
screen-reader user. `library/translation.inc.php:149-152` already names this exact substitution as
"a worse error than the one being fixed"; the login page was the surface still making it.

**The fix.** `SessionLanguageInterface` asks the question the listener meant; `CoreSessionLanguage`
answers it through core's own `getLanguageCode()` / `xl_session_language_id()` — the same pair
`xl_product_name()` uses — so the product has **one** rule for "is this session Arabic?" instead of two
that drifted. Injected rather than called statically, because locked constraint C5 keeps the branding
runtime plane free of global state.

**Two docblock claims falsified by the change were corrected, not left standing:** the accessible name
is resolved in the page's reading *language*, and the listener's blanket "no network call, no database
query" is narrowed to the branding values it was ever true of, with the one added query named,
measured and justified in place.

**The test that should have caught this was the false green.** It set `$branding->rtl = true` and
asserted Arabic came back — which the defect *and* the fix both satisfy. Replaced by three cases that
separate the conflated variables: English, Arabic, and a right-to-left session that is **not** Arabic.
The third deliberately leaves direction true; on a left-to-right fixture it would test nothing.

**Negative control:** reverting the single line to `isRtl()` failed the Hebrew case with `'شعار ثقة'`
where `'Thiqa logo'` belongs — 1 failure, 69 tests / 179 assertions. Source restored to SHA-256
`ce38c032d8fa99ac…`; suite returned **69 tests / 183 assertions, exit 0**. PHPCS 6/6, exit 0.

**Recorded, not fixed:** `BrandingTwigExtension::productName()` and `brandLogoAlt()` take `$arabic` as
a template argument defaulting to `false`, and no shipped template calls either. Latent, and it errs
*Latin*, not Arabic-for-Hebrew — so it is not this defect. Noted so it is not later mistaken for one.

### The three open Scan-3 findings — RE-VERIFIED BY EXECUTION, all genuinely still open

Re-checked directly rather than inherited from Rev 29, because a finding recorded as open and quietly
fixed elsewhere would corrupt PRE-25 as surely as the reverse:

| ID | Verdict | Evidence re-derived 2026-08-25 |
|---|---|---|
| S3-P1-33 | **OPEN — confirmed** | `setup.php` still has exactly **10** `Thiqa` literals at the recorded lines `145,160,356,452,522,524,526,976,1530,1747`, alongside **32** `OpenEMR` occurrences |
| S3-P2-35 | **OPEN — confirmed** | `brand/manifests/SHA256SUMS` = **123** entries, every one under `brand/**` or `docs/branding-production/**`; **zero** under `public/`. Uncovered: **17 tracked** `public/images/**` branding assets, plus **8** deployed fonts at `public/assets/fonts/thiqa/*.woff2` |
| S3-P2-36 | **OPEN — confirmed, and slightly worse than recorded** | `markTestSkipped` still at `BrandingGovernanceGuardTest.php:237`; `.github/workflows/isolated-tests.yml` has **no Node/webpack step at all**, and neither `phpunit` invocation passes `--fail-on-skipped`. `/public/themes/*` is gitignored (`.gitignore:17`), so the directory is absent in every CI checkout and the Q77 check skips in **every** CI run |

### Independent read-only verification of S3-P1-27, S3-OBS-01 and S3-P1-30

Performed by a dedicated agent bound to `SELECT`-only, which started and then stopped the stack via the
documented scripts. **Live database mutated: NO.**

**S3-P1-27 — all four recorded numbers CONFIRMED exactly**, with no drift: 27 `lang_definitions` rows
containing `Thiqa`; 5 English (`lang_id = 1`) override rows; `Thiqa Database Upgrade` present at
`cons_id 13235`; **0** of the 28 contract target keys present. The parenthetical "all five marked
retired", which nobody had checked, is also confirmed — `tools/branding/brand-strings.json`'s
`retired_english_overrides` array holds exactly those five constants.

**Disposition upheld and strengthened: this is an unapplied migration, not a shipped-code defect.**
Both execution paths were verified correct — `library/classes/Installer.class.php:1764` enqueues the
supplement unconditionally for any non-clone install, the generated
`contrib/util/language_translations/durableTranslationContracts_utf8.sql` carries **28 of 28** target
keys, and `sql_upgrade.php:521-527` iterates every checked-in contract and runs `forward()`.
**New corroboration not previously recorded:** the live `version` row is `v_database = 541` while
`version.php` declares `542`, so this instance is one schema version behind and `sql_upgrade.php`
demonstrably has not run since the contracts landed. Per Owner decision of 2026-08-25 the migration
was **deliberately not run** — it remains an operator step, carried to PRE-25 as OPERATIONAL.

**S3-OBS-01 — CONFIRMED, reproduced live, and it is real.** `admin.php` (repo root, 231 lines) performs
**no** authentication: no `session_start()`, no `AclMain`, no `$_SESSION['authUser']` test, no CSRF
token, no multisite gate. A cookie-less `Invoke-WebRequest` returned **HTTP 200, 4,676 bytes**, body
containing the fully-populated site table. Nothing else gates it — `admin.php` appears nowhere in
`httpd.conf`, and no root `.htaccess` exists. The contrast with its own sibling is stark: `setup.php`
defends itself with `$allow_multisite_setup = false` (`:52`), `$allow_cloning_setup = false` (`:57`)
and explicit CSRF/state `die()`s (`:320-343`).

Disclosed: site directory names (`default`, `rdy0082restore`), database names (`openemr`,
`openemr_rdy0082_restore`), the site display name, application version `8.3.0-dev`, schema/ACL/patch
currency — and **two unauthenticated links to the state-mutating `sql_upgrade.php?site=…`**.
**Not** disclosed: no credentials. `admin.php:115` does pull `$host/$login/$pass/$port` into scope and
`:118` connects with them, but only `$sfname` and `$dbase` are echoed; confirmed empirically that the
response body contains no `root`, no password, no `127.0.0.1` and no `3306`.

**Not fixed, by Owner decision of 2026-08-25 (verify and record only).** It is **outside branding** and
must not be absorbed into the branding ledger as though PRE certified it.

**Correction, same day — the gap this section originally recorded as unclosable HAS been closed, and
the answer is the adverse one.** The paragraph here first said that whether `sql_upgrade.php` has its
own guard "was deliberately not tested, because fetching it risks mutating the database". That
reasoning confused *executing* the endpoint with *reading* it. Reading it settles the question with no
database access at all, and it was subsequently read at current HEAD:

```text
sql_upgrade.php:65    $ignoreAuth = true;   // no login required
sql_upgrade.php:493   if (!empty($_POST['form_submit']) || $cliFromVersion !== null) {
grep -c verifyCsrfToken sql_upgrade.php  ->  0
```

`CsrfUtils` appears four times in the file (`:73`, `:153`, `:213`, `:292`) but `verifyCsrfToken` is
never called; the two tokens that are generated belong to the polling endpoints
`library/ajax/sql_server_status.php` and `library/ajax/sql_upgrade_version_check.php`, not to the
upgrade POST. `acl_upgrade.php` and `sql_patch.php` show the same posture.

So the full chain is: an unauthenticated `admin.php` renders live links to `sql_upgrade.php?site=…`,
and that target requires **neither a login nor a CSRF token** to execute a schema migration. **The
disclosure is the lesser half of S3-OBS-01; the reachable state-mutating endpoint is the greater
half.** No POST was attempted and no working exploit is asserted — there may be gating inside
`interface/globals.php` under `$ignoreAuth`, or environmental constraints not evaluated. What can be
stated from source is that no guard exists in these files.

**Ownership — and this is the part that changes who is accountable.** `git show master:sql_upgrade.php`
carries the identical `$ignoreAuth = true`, and master's `admin.php` contains zero auth constructs.
This branch's only commits to either file are branding-string changes. **S3-OBS-01 is inherited
upstream OpenEMR posture, not a branding-fork regression** — these are installer-class scripts upstream
expects a deployment to remove or protect. It stays a real, reproduced finding that this product should
fix before any public exposure, but it must be booked as **pre-existing upstream behaviour**, and it is
**not** a PRE-SKYEAGLE blocker.

**S3-P1-30 — both numbers CONFIRMED (22 and 8), by a stronger method than the record used.** Rather
than counting contracts carrying `on_missing_identity: "skip"`, the carry-forward rule was read out of
`TranslationContractSqlRenderer::legacyStatement()` and replayed read-only against the live catalogue,
so the claim proved is the stronger one: all **22** `skip` contracts genuinely **drop at least one
locale**, and Arabic loses exactly **8**. The two cited call sites verify precisely —
`interface/main/backup.php:505` is `xlp('Dumping %s database')` and `:538` is
`xlp('Dumping %s web directory tree')`. Cause is exactly what `MissingIdentityPolicy`'s docblock
predicts: all eight Arabic strings render the product as **البرنامج** ("the program") rather than the
literal, so there is no literal to replace with `%s`. This corroborates the recorded disposition that
it is an **authoring** decision, not a code defect.

**Observation not previously recorded:** `lang_id 1` (English (Standard)) also drops 2, and the `dummy`
locale (59) drops all 22. Neither is part of the claim and neither changes the verdict — but if PRE-25
characterises the loss as affecting only non-English locales, those two English rows falsify that
phrasing and must not be glossed.

---

## 15D. SESSION OF 2026-08-25 — WHAT LANDED, AND EXACTLY WHERE THE QUOTA STOP LEFT THINGS

Five agents were dispatched in parallel against the open findings, partitioned by file ownership so
they could share one working tree. One was read-only verification (§15C). **Four were remediation
agents, and all four were terminated mid-task by a session usage limit** — an API quota stop, not a
task failure. Two had reached a complete, verified state and were committed. Two had not.

**All partial work was backed up before assessment** to the session scratchpad at
`agent-wip-backup/`, including a full `ALL-TRACKED-CHANGES.patch`. Nothing was discarded, reverted or
stashed. The incomplete work is **left in the working tree uncommitted**, which is where a resuming
session will find it.

### LANDED — verified and committed

| Finding | Commit | State |
|---|---|---|
| S3-P2-36 | `e203d5bdd` | **FIXED — VERIFIED** |
| S3-P2-35 | `57e51286c` | **SUBSTANTIALLY FIXED — VERIFIED**; documentation half outstanding |
| PRE-ORCH-02 | `d42a7d6d4` | **FIXED — VERIFIED** |
| SKY-F01 | `02bcae75c` | **FIXED — VERIFIED** (§15C) |

**S3-P2-35's remaining work, stated plainly so it is not mistaken for done.** The mechanism is
complete and proven, but closing this finding *changes the documented re-issue discipline*, and
`docs/branding-production/11-asset-manifest.md` and `12-release-verification.md` still describe the
old one. Both files are themselves manifest-covered, so updating them requires re-issuing their own
entries in the same change. **S3-P2-35 stays OPEN in the register until that is done.**

### NOT LANDED — uncommitted work-in-progress in the working tree

#### S3-P1-33 — pre-database product identity: mechanism built, target not converted

Present and uncommitted:

```text
src/Common/Branding/ProductIdentity.php                      (new)
tools/branding/src/ProductIdentityGenerator.php              (new)
tools/branding/src/ProductIdentityKey.php                    (new)
tools/branding/src/ProductIdentitySourceKind.php             (new)
tools/branding/src/ProductIdentityCliOptions.php             (new)
tools/branding/bin/generate-product-identity.php             (new)
library/product_identity.generated.php                       (new, generated artefact)
tools/branding/src/GeneratedHeader.php                       (modified — adds a PHP banner form)
interface/globals.php                                        (modified — consumes the artefact)
library/globals.inc.php                                      (modified — consumes the artefact)
```

**Two things are missing, and the second is a process deviation worth recording.**

1. **`setup.php` is untouched.** *(S4D-06, corrected 2026-08-25: superseded. `setup.php` carries*
   ***zero*** *product literals as of `e16913d5b`. What follows records the state at Rev 31 and is*
   *kept as the history of what was open then, not as a current claim.)*
   It still carries all **10** `Thiqa` literals at the recorded lines,
   alongside 32 `OpenEMR` occurrences. `setup.php` *is* the finding — the generator is only the means
   — so despite the volume of new code, **S3-P1-33 has not been materially advanced against its own
   target** and remains OPEN.
2. **No ADR was written.** Owner decision SKY-Q11 requires a recorded design step *before*
   implementation, following the discipline `ba0078c62` used for the translation architecture. The
   agent built first. A resuming session should write the ADR against the code that now exists and be
   willing to change the code where writing the rationale down exposes a weakness — not
   back-rationalise the implementation it happens to have inherited.

**None of this has been reviewed or tested by the orchestrator**, beyond confirming the files parse.
Treat it as an unreviewed draft, not as progress banked.

#### The SVG geometry safeguard — implemented, and its own tests fail 29 times

Present and uncommitted:

```text
interface/modules/custom_modules/oe-module-thiqa-branding/src/AssetIntake/SvgGeometry.php   (new)
interface/modules/custom_modules/oe-module-thiqa-branding/src/AssetIntake/SvgInspector.php  (modified)
interface/modules/custom_modules/oe-module-thiqa-branding/src/AssetIntake/AssetRejectionReason.php (modified)
tests/Tests/Isolated/Modules/ThiqaBranding/AssetIntake/SvgGeometryInvariantTest.php         (new)
```

Measured: **259 tests across Asset, AssetIntake, Guardrail and Listener → 29 failures, exit 1, and
every one of the 29 belongs to `SvgGeometryInvariantTest`.** No other suite regressed, which is why
the two landed commits above could be verified around it.

**Diagnosis, from the agent's own failing cases: the rule is over-broad.** The failures cluster in
`testPreserveAspectRatioIsIgnoredOnInertDrawingElements` for the `path`, `g` and `rect` data sets —
the test asserts that `preserveAspectRatio` on an inert child element is *ignored*, and the rule flags
it as a root-`svg` violation anyway. A second failure shows an enum mismatch between
`SvgDisallowedElement` and `SvgAspectRatioNotPreserved`, i.e. the rejection reasons are not yet
resolving in the right precedence. The agent had explicitly reached its **second** negative control
when it was cut off, so this is unfinished work rather than a wrong design.

**One check that matters and came back clean:** no real shipped asset was modified. `git status`
reports nothing under `public/images/`, and the real
`public/images/logos/core/menu/primary/logo.svg` carries `width="2048" height="2048"
viewBox="0 0 2048 2048"` and **no `preserveAspectRatio` attribute at all** — it is compliant. The slot
name appearing in the failure messages comes from the test's own fixtures, not from a mutated
production file. The instruction never to corrupt a real asset to test failure was honoured.

### Resumption order for the next session

1. Finish the SVG safeguard (narrow the rule to the root element / geometry-bearing elements, fix the
   rejection-reason precedence, complete the second negative control) **or** revert those four files
   from the backup if a cleaner design is preferred. Do not commit it while red.
2. Write the S3-P1-33 ADR, then reconcile the existing draft against it, then convert `setup.php`.
3. Close S3-P2-35's documentation half and re-issue the two manifest entries it touches.
4. Then, and only then, the remaining programme steps: push, local-disk certification copy, full gate,
   re-run Scans 1–3 against the current worktree inventory (**six** worktrees — see PRE-ORCH-02), and
   PRE-25.

**Certification remains NOT PASSED.** S3-P1-33 is open, S3-P2-35 is partly open, the SVG safeguard is
red, and PRE-25 has not started.

---

## 15E. THE FOUR REMAINING BLOCKERS — CLOSED 2026-08-25

> Every closure below was verified by execution, and each carries a negative control that was run and
> then exactly restored. Where a fix caused a regression elsewhere, the regression is recorded here
> rather than quietly repaired.

### SKY-Q08 — production logo geometry safeguard — **FIXED — VERIFIED** (`55738dc82`)

A logo is a trademark, and a stretched trademark is no longer the mark. Nothing in the intake path
prevented a production logo shipping with distortion-enabling geometry.

`SvgGeometry` extends the existing `SvgInspector` seam rather than sitting beside it — layers 1-3
answer "can this file hurt the tenant", this is layer 4, "can this file hurt the mark". It is
brand-neutral by construction: keyed on what a slot **is**, with no filename, brand name or colour
anywhere in it. Three clauses at two deliberately different scopes — **G1** (`preserveAspectRatio`
must not be `none`) is document-wide because a nested `svg` is allowlisted and opens a viewport of its
own; **G2** (root must declare a usable `viewBox`) and **G3** (declared `width`/`height` ratio must
agree with the viewBox) stay root-only, because a nested svg legitimately omits its viewBox to get a
1:1 viewport and requiring one there would be a false positive.

**Verified against the real corpus, not only fixtures:** all **27** shipped SVGs under `brand/`,
`public/images/` and the module's dark marks were checked. Every one has `preserveAspectRatio` absent
and a valid viewBox. **Zero flagged** — so the rule is not riding on its tolerance and no shipped
asset is grandfathered in.

Negative control: both guard call sites commented out → **18 failures, 2 errors, exit 2**. Source
restored to SHA-256 `fe043b8578327eba…`; suite returned **259 tests / 786 assertions, exit 0**.

**Why this looked broken and was not.** The work arrived from an interrupted agent with **29 failing
tests**, and the diagnosis was initially recorded here as an over-broad rule. That was wrong. The
fixture builder still carried a live negative-control injection —
`$preserveAspectRatio ??= 'none'; // NEGATIVE CONTROL INJECTION - REMOVE` — which forced the attribute
onto the root of every fixture. The implementation was correct throughout. The file was swept for
other leftover markers; there were none. Recorded because "the tests were red" and "the code was
wrong" are different claims, and only the first was true.

### S3-P2-35 — deployed-asset integrity — **FIXED — VERIFIED** (`57e51286c`, `c154215d9`)

Mechanism and governance both closed. Coverage is assigned by **ownership class** so that only like is
compared with like:

| Class | Verified how | Count |
|---|---|---:|
| Source artefact (`brand/`, `docs/branding-production/`) | recorded SHA-256 | 123 |
| Deployed immutable (`public/images/**`, `sites/default/images/*`, module dark marks) | recorded SHA-256 | 21 |
| Mirrored deployment (`public/assets/fonts/thiqa/**`) | **equality with the recorded source** | 11 |
| Generated deterministic (`public/themes/*.css`) | deferred to S3-P2-36 by decision | — |
| Tenant materialised / runtime overlay | excluded by recorded decision | — |

The mirrored class is verified by equality rather than a second recorded hash because
`public/assets/` is gitignored build output: a recorded hash there would go stale on every legitimate
rebuild and train maintainers to re-issue entries without reading them — precisely the habit that let
the gate sit RED for five days undetected at Revision 5. The check runs in **both** directions, so an
unshipped source file is reported rather than ignored.

The documentation half is what makes it governable, and it changed the re-issue discipline: **one edit
can now oblige more than one entry**, because a source artefact and its deployed copy are two manifest
rows describing one decision. Recorded in `11-asset-manifest.md` (the coverage model, including why
classes 4-6 are excluded **by decision** rather than by oversight) and `12-release-verification.md`
Revision 8.

**A live demonstration fell out of the work:** writing the two documents took the gate RED with exactly
the two expected mismatches and no others, and re-issuing returned it to 123/123 · 21/21 · 11/11,
exit 0.

### S3-P2-36 — the Q77 theme check now actually executes in CI — **FIXED — VERIFIED** (`e203d5bdd`, `cb685e1f9`)

The environment now **declares** its obligation rather than the guard inferring it.
`OPENEMR_DEPLOYED_THEMES_REQUIRED=1` is set on the one leg that builds the themes; there an absent
directory is a hard failure. Every other leg leaves it empty and the skip survives, which is correct
for a developer host that builds off-tree (`CLAUDE.local.md` §6 — on the Windows host the build
genuinely cannot run inside the repository).

Coverage is layered because neither layer alone is honest: the **projected** set is derived from
`webpack.themes.js`'s entry map and executes everywhere with no build; the **deployed directory**
itself is checked where mandatory, and that is not redundant — webpack's output cleaning applies to its
own workspace and `robocopy /E` deletes nothing at the destination, so a stale stylesheet can survive
where the entry map has no visibility of it, and `interface/globals.php`'s `file_exists()` gate
resolves it just as happily as a fresh one. A third layer parses the workflow YAML so removing the
build step or the signal breaks a test.

Negative controls are permanent tests rather than one-off runs: the audit is proven to flag a forbidden
stylesheet, a stale one outside Q77's four names, and a missing required one, and to accept a faithful
directory; and the skip semantics are pinned in both directions. `cb685e1f9` then cleared four
level-10 PHPStan findings in source — no baseline, no ignores — one of which was substantive: a preg
alternation branch that does not fire yields `''`, indistinguishable from a branch that matched empty
text, so the check could not do the job it appeared to do.

### S3-P1-33 — pre-database product identity — **FIXED — VERIFIED** (`e16913d5b`, ADR-BRAND-005)

The design was recorded **first**, as Owner decision SKY-Q11 required:
`docs/branding/adr/ADR-BRAND-005-pre-database-product-identity.md`.

One authority (the branding profile), one deterministic offline generator, one committed artefact that
`return`s an immutable array, one reader (`OpenEMR\Common\Branding\ProductIdentity`). An array rather
than `define()`d constants because constants are process-global, cannot be re-resolved for a second
site, and warn on redefinition. Values are emitted through `var_export()` after schema validation, so
profile content cannot escape its string literal — the property is guaranteed by the serializer, not by
the escaping discipline of whoever edits the generator next.

**Failure degrades, never aborts** — the deliberate inverse of this codebase's usual
parse-don't-validate posture, because every consumer is an installer, an upgrade path or a fatal-error
reporter, and in all three raising is strictly worse than rendering a neutral name (the S3-P0-28
lesson). The ADR records the one failure that is genuinely **not** guardable: a syntax error in the
artefact is an uncatchable compile-time fatal at `require`, mitigated by nothing hand-editing the file,
`php-syntax-check` linting it, and `--check` failing CI on any byte drift.

**`setup.php` — the actual finding — is converted.** Product literals: **10 → 0**. The classification
was re-derived on disk, not inherited. What remains naming the upstream product is exactly the PRESERVE
class under locked constraint C7: namespaces and code identifiers, docblocks, `error_log()` lines an
operator reads against upstream documentation, and the genuinely factual project references — the
`open-emr.org` home page and the grant-funding paragraph, which are about the OpenEMR **project** and
not about this product. **Zero user-facing product-identity leaks remain.**

Escaping is per context, not uniform: `text()` for HTML, and `js_escape()` with string concatenation
for the one occurrence inside a JavaScript string literal, where HTML-escaping would have been the
wrong helper. Both are composer `autoload.files` entries, **verified available in a cold CLI process** —
a helper that were not loaded there would have turned the openssl error path into a fatal instead of a
message.

The **D-3 rebase checklist was updated, not relaxed**. Its `mustNotContain` lists — the actual
protection against a rebase restoring upstream branding — are untouched; only the expected patched
*form* changed, and it now additionally asserts both files really do wire `ProductIdentity`.
`interface/globals.php`'s expected literal count goes **4 → 0**, which is the stronger assertion: it
fails if anyone reintroduces a hardcoded name there.

**A regression this work caused, and its fix.** Adding `@branding-identity-check` to the canonical gate
shifted the script array, and `BrandingCiContractTest` read that array **positionally**, so a correct
and strengthened gate turned it red. Steps are now located by content. Recorded because the lesson
generalises: a guard that fails when the thing it guards is *strengthened* trains people to edit the
guard, which is how a real regression eventually gets waved through.

**Category-B check.** The artefact carries `product_domain` = `skyeagle.uk` because the committed
branding profile has carried it since `b3b821ffa`, well before this change. The generator reflects
existing configuration and introduces **no new identity**. Payload remains the current pre-SkyEagle
product name.

Verification: canonical `composer branding-ci` **317 tests / 3,574 assertions, exit 0**; ProductIdentity
contract **9 tests / 38 assertions** including a drift negative control that tampers with the artefact,
requires `--check` to exit 3, restores it byte-for-byte and re-verifies; determinism proven by
regenerating and comparing bytes; PHPCS 13/13.

### PRE-ORCH-02 — a sixth worktree escaped the exclusion rule — **FIXED — VERIFIED** (`d42a7d6d4`)

The S1-P2-14 remediation named `G:/My Drive/OpenEMR.worktrees/` as a literal path. A worktree has since
been registered at `G:/My Drive/worktrees/OpenEMR/jade-ibis/OpenEMR`, which does not match that prefix,
so a scan applying the documented exclusion verbatim would ingest it. It is the **more** misleading of
the two siblings: the first sits at the pre-branding baseline where a stray hit reads as obviously
stale, while this one is **one commit behind current HEAD on this branch**, so it yields plausible
phantom findings on near-current code — including defects closed at `5202b0253` and `02bcae75c`.

The rule is generalised rather than given a third literal path: the exclusion set is **every path
`git worktree list` reports other than the repository root**, enumerated at scan time. Both siblings are
left in place; removing another agent's worktree is not this programme's call.

### Owner decisions taken 2026-08-25

| Decision | Ruling |
|---|---|
| Run the S3-P1-27 migration on the demo database? | **No.** Keep the "live database mutated: NO" invariant. It stays an operator step. |
| Fix S3-OBS-01? | **No — verify and record only.** Confirmed live, and confirmed **upstream OpenEMR posture**, not a fork regression. Outside branding; not a PRE blocker. |
| Push the branch now? | **No — push only after PRE-25 passes.** The branch stays local until certification. |
| Scope of the scan re-run? | **Full five-agent Scan 1/2/3 equivalent** from the corrected state. Dispatched as Scan-4A…4E. |

### What remains

```text
1. Two unlabelled P1 slots      numbering gap at S2-P1-19 / S2-P1-21; resolve at PRE-25,
                                do NOT invent findings to fill them
2. Scan-4A…4E                   five independent adversarial agents DISPATCHED at e16913d5b
3. PHPStan full run             running; exit code alone is NOT proof on this host --
                                grep for "Internal error" and "Result is incomplete"
4. PRE-25                       NOT STARTED
5. Push / protect branch        deferred by Owner decision until PRE-25 passes
6. Local-disk certification     must clone from git, not copy files; blocked on the push decision,
                                so it will clone from the local repository path instead
```

**Certification remains NOT PASSED.** Every documented code blocker is now closed; what remains is
verification and reconciliation, not construction.

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

**PRE-11 / Scan-2B — Test-harness truthfulness — DONE — VERIFIED (continuation Rev 11).**

The deliberately nonexistent `--filter SkyEagleBranding` control ran **0 tests / 0 assertions** and printed
`No tests executed!`. Without an empty-suite guard, PHPUnit returned **true exit 0** (final timed confirmation
2026-08-24T08:33:06.4232339Z–08:33:27.1167938Z, 20.694 s), confirming
the false-green defect. Adding PHPUnit 11.5.55's supported `--fail-on-empty-test-suite` option changed the
same zero-test result to **true exit 1** (2026-08-24T08:28:26.0217108Z–08:28:41.2621650Z, 15.240 s).
The canonical `composer branding-ci` command includes that flag, plus `--fail-on-incomplete` and
`--fail-on-risky`; `BrandingCiContractTest` fails deterministically if those boundaries or any expected suite
path disappear. The final real suite executed 91 tests / 264 assertions and exited 0, so the protection is not
itself an empty green. The nonexistent SkyEagle-shaped name was used only as a negative filter; no SkyEagle
code, namespace or branding was introduced.

**PRE-16 / Scan-2G runtime proof — EXECUTED, RESTORED, AND REPEATED AFTER REPAIR (continuation Revs 7–8).**
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

**Initial confirmation execution (2026-08-24): DONE — VERIFIED; exact state restored.**

```yaml
TASK/FINDING ID: PRE-16 / S1-P0-09 runtime proof
Previous status: DESIGNED, NOT EXECUTED
Status at Rev 7: PRE-16 DONE — VERIFIED; S1-P0-09 CONFIRMED, REPAIR PENDING
What changed: Temporary revision-1 tenant overlay only; all DB/file state restored after observation.
Files changed persistently: none
Commit hash: none (runtime experiment)
Tests/commands executed: DB snapshot; CSS SHA256/timestamps; verify; materialise twice (one invalid negative control, one valid payload); HTTP endpoint/page inspection; real browser computed-style checks; full restoration comparisons
Exact exit codes: preflight verify 1 (expected inherited inconsistency); invalid payload 2 (REJECTED — NOT PASS); valid payload 0; applied-state verify 0; restoration SQL/file copy 0; restored verify 1 (expected preflight state)
Tests and assertions executed: 7 branding DB rows; 2 CSS files; 3 light + 3 dark token overrides; live endpoint; live page link; real .btn-primary consumer; browser custom properties before/applied/restored
Runtime evidence: valid apply 2026-08-24T06:46:45.4660056Z–06:46:52.9530799Z (7.487 s); revision 1 healthy; endpoint delivered --interactive-primary-default:#0B376E; browser resolved it to #0B376E but .btn-primary stayed rgb(196,63,46) via --thiqa-interactive-primary-default:#c43f2e
Independent verification: DB returned exactly to all preflight HEX values; light CSS SHA256 43015D055A6359698608B8FF99030C5D9E79CED2A4CDB16B1906C2C521EA78E8 and timestamp 2026-08-10T18:50:40.081Z; dark CSS SHA256 F096670815C7C76D7F9C47674970299B950984AEDC09EFBBC36A8110D480F4B4 and timestamp 2026-08-10T18:50:40.120Z; overlay links absent again; browser bare variable empty and button rgb(196,63,46).
Rollback method: Completed by exact DB-value restoration plus byte-identical CSS backup restore; no repository rollback needed.
Remaining risks at Rev 7: CLI emitted local session-file permission warnings; they did not alter command outcomes but must not be hidden. The consumer gap was resolved in Rev 8.
Next task at Rev 7: S1-P0-09 brand-neutral token consumer repair with automated regression tests.
```

**Post-repair repetition (2026-08-24): PASS; exact state restored.** Commit
`566b14ea68dd106fe0e38d5d77f2df46b5da25a8` passed the full acceptance chain in both variants. The light
button resolved `#0B376E` as `rgb(11, 55, 110)`; after the reversible `css_header` switch, the dark button
resolved `#64D8CB` as `rgb(100, 216, 203)`. Applied-state verification exited 0. Restoration returned all
globals, both overlay bytes/timestamps, `style_light.css`, absent overlay links, and the baseline
`rgb(196, 63, 46)` button; restored verification exited 1 because the inherited revision-0/file-present
inconsistency was intentionally restored. The detailed test, build, warning and hash evidence is in §5.

**Also outstanding:** PRE-09 is complete apart from the asset-blocked Arabic logo variant; Scan-3 (PRE-18…24) entirely; PRE-25 final reconciliation.

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


## 21. PRE-25 — FINAL RECONCILIATION

**Executed 2026-08-25 at HEAD `d9f6fa6c7`+ (working tree carries this section).**

> Every finding this programme ever issued is reconciled below to **one** final state. Nothing is
> omitted, nothing is merged away, and no finding is closed by the fact that a test passes. Every
> commit hash cited was verified to exist with `git cat-file -e` before being written here.

### 21.1 The numbering, re-derived

The finding counter is **global and sequential across scans and severities** — not per-severity, which
is the misreading that produced the phantom "two unlabelled P1 slots". **34 IDs are assigned across the
range 01..36.** The only numbers never issued are **08** and **19**; neither has a section, a commit, or
a mention anywhere in the corpus or in `git log --all -S`. **S2-P1-21 was never a gap** — number 21 is
`S2-P0-21`, a P0, fixed and verified at Rev 10.

**Correction to an earlier count.** The register block long read `P0 4 total`. There are **five** P0
findings; the four counted excluded `S3-P0-28`, which was tracked separately in the Scan-3 register.
Both numbers were defensible in their own context and neither was wrong on the facts; a single ledger
needs a single total, and it is 5.

Totals: **5 P0 · 22 P1 · 7 P2 = 34 numbered findings**, plus 6 post-scan findings and 1 observation.

### 21.2 P0 — five findings, five closed

| ID | Finding | Final state | Evidence |
|---|---|---|---|
| S1-P0-01 | Eight undocumented core branding edits (Q1 governance breach) | **FIXED — VERIFIED** | `aebcfdfc5`, `26c32fcb3` |
| S1-P0-09 | Tier-2 token overlay structurally inert | **FIXED — VERIFIED** | `566b14ea6`; live-verified and exactly restored |
| S1-P0-13 | Brand name embedded in a translation catalogue key | **FIXED — VERIFIED** | `948e4a6d1`, `2baf7322a` |
| S2-P0-21 | RB-01 remediation did not survive a database rebuild | **FIXED — VERIFIED** | `02671f0c9`, `2baf7322a` |
| S3-P0-28 | Install-vs-upgrade translation precedence divergence | **FIXED — VERIFIED** | `5d519342d` |

**Zero P0 findings open.** S3-P0-28 is worth naming again: it would have wedged every upgrade of every
site installed from this branch, uncatchably, mid-run, and it does not reproduce on this dev database.
It was found only because Scan 3 was run adversarially by an agent that had not done the remediation.

### 21.3 P1 — twenty-two findings

Nineteen are FIXED — VERIFIED. The remaining three are each closed *as a disposition*, not left hanging:

| ID | Final state | Evidence / justification |
|---|---|---|
| S1-P1-02 | **FIXED — VERIFIED** | `2b801e668` |
| S1-P1-03 | **FIXED — VERIFIED** | `597276b09`, `ff6e35b4f` |
| S1-P1-04 | **FIXED — VERIFIED** | `2df9b5eb1` |
| S1-P1-05 | **FIXED — VERIFIED** | `b400546a2` |
| S1-P1-06 | **FIXED — VERIFIED** | `88ff34289`; D-8 correctly remains an open dependency, recorded not hidden |
| S1-P1-10 | **FIXED — VERIFIED** | `723170df7` |
| S1-P1-11 | **FIXED — VERIFIED** | `29be1fcd5` |
| S1-P1-15 | **FIXED — VERIFIED** | `77d2b3e12`, `8eb4ea7f8`, `64d2ba23c` |
| S1-P1-17 | **FIXED — VERIFIED** | `0af1ce174` |
| S2-P1-18 | **FIXED — VERIFIED** | `1474263b4` |
| S2-P1-20 | **REFUTED IN PART; REMAINDER FIXED — VERIFIED** | `2df9b5eb1`; see Correction K |
| S2-P1-22 | **FIXED — VERIFIED** | `6edc03b8b` |
| S2-P1-23 | **FIXED — VERIFIED** | `6edc03b8b` |
| S2-P1-24 | **PARTLY FIXED; logo variant DEFERRED WITH EXPLICIT JUSTIFICATION** | Juxtaposition and text halves closed (`6edc03b8b`, `02bcae75c`). The Arabic **logo** variant is blocked on an approved asset that does not exist, and Owner decision **SKY-Q07** forbids fabricating one. Permanent documented limitation, carried into Phase B. |
| S2-P1-25 | **FIXED — VERIFIED** | `45e9eb4f3` |
| S2-P1-26 | **FIXED — VERIFIED** | `6da352e9a` (Class B), `177d5dc97` (Class A) |
| S3-P1-27 | **OPERATIONAL — NOT A CODE DEFECT** | Independently verified 2026-08-25: all four counts exact; both execution paths correct (`Installer.class.php:1764`, `sql_upgrade.php:521-527`, supplement carries 28/28 targets). This host is one schema version behind (`v_database` 541 vs 542). Owner ruled the migration **deliberately not run**. Operator step, not a blocker. |
| S3-P1-30 | **ACCEPTED WITH OWNER DECISION, QUANTIFIED** | 22 contracts drop ≥1 locale; Arabic loses exactly 8 strings — re-derived by replaying the carry-forward rule against the live catalogue, which proves the *stronger* claim than the setting count. Cause: the eight Arabic strings render the product as **البرنامج**, so there is no literal to replace. An authoring decision; this programme will not fabricate translations. |
| S3-P1-31 | **FIXED — VERIFIED** | `59d5c14df` |
| S3-P1-32 | **FIXED — VERIFIED** | `f18f75080` |
| S3-P1-33 | **FIXED — VERIFIED** | `e16913d5b`, ADR-BRAND-005 recorded first per SKY-Q11 |
| S3-P1-34 | **FIXED — VERIFIED** | `f18f75080` |

**No P1 is open as unaddressed work.**

### 21.4 P2 — seven findings, seven closed

| ID | Final state | Evidence |
|---|---|---|
| S1-P2-07 | **FIXED — VERIFIED** | `97f6952cf` |
| S1-P2-12 | **FIXED — VERIFIED** | `97f6952cf` |
| S1-P2-14 | **FIXED — VERIFIED** | `97f6952cf`; superseded in scope by PRE-ORCH-02 |
| S1-P2-16 | **FIXED — VERIFIED** | `97f6952cf` |
| S3-P2-29 | **FIXED — VERIFIED** | `5d519342d` |
| S3-P2-35 | **FIXED — VERIFIED** | `57e51286c` mechanism + `c154215d9` governance |
| S3-P2-36 | **FIXED — VERIFIED** | `e203d5bdd` + `cb685e1f9` |

### 21.5 Post-scan findings and observations

| ID | Final state | Evidence |
|---|---|---|
| A-01 | **FIXED — VERIFIED** | `5202b0253` — shell user menu rendered `حول Thiqa` |
| A-02 | **FIXED — VERIFIED** | `5202b0253` — questionnaire theme help text |
| A-03 | **FIXED — VERIFIED** | `5202b0253` — leak surface now tokenizer-derived, fails in both directions |
| SKY-F01 | **FIXED — VERIFIED; PARTLY REFUTED** | `02bcae75c`. Tagline leg REFUTED (documented single-global design); product-name legs LATENT (no template consumes them); the live defect was the two login logo accessible names, selected by direction instead of language. |
| SKY-Q08 | **FIXED — VERIFIED** | `55738dc82` — production logo geometry safeguard; 27/27 shipped SVGs pass |
| PRE-ORCH-02 | **FIXED — VERIFIED** | `d42a7d6d4` — a sixth worktree escaped the S1-P2-14 literal path rule |
| S3-OBS-01 | **OUT OF SCOPE — UPSTREAM, NOT A FORK REGRESSION** | Confirmed live (HTTP 200, 4,676 bytes, cookie-less) and confirmed inherited: `master:sql_upgrade.php:59` carries the identical `$ignoreAuth = true`, `verifyCsrfToken` is never called, and master's `admin.php` has zero auth constructs. Owner ruled **verify and record, do not fix**. Real, and it needs its own review before any public exposure — but it is **not** a PRE-SKYEAGLE blocker and PRE certification does not cover it. |

### 21.6 Compatibility items preserved (Owner decision SKY-Q12)

Confirmed intact and deliberately **not** migrated: the `saas_branding_*` globals prefix (Q58); the
`--thiqa-*` CSS custom-property aliases; `contracts/database-upgrade.json` legacy keys and the
`legacy_keys` translation contracts; historical documentation, ADRs, patch records, evidence and
checkpoint history; preserved OpenEMR Foundation / ONC / community strings (locked constraint C7); and
the operational identifiers pinned by `OperationalIdentityContractTest`.

The target was never "zero occurrences of the old name everywhere" — it is **active user-facing residue
= 0**, with documented historical and compatibility exceptions. That distinction is what makes the
PRESERVE class in `setup.php` correct rather than an oversight.

### 21.7 Certification inputs still outstanding

PRE-25's reconciliation above is complete. The **verdict** is not, because three inputs had not returned
when this section was written:

```text
Scan-4A … 4E   five independent adversarial agents, dispatched at e16913d5b   PENDING
PHPStan        full level-10 run; exit code alone is NOT proof on this host   PENDING
Local-disk     certification copy; deferred behind the Owner's push decision  PENDING
```

**A verdict written before these return would be exactly the false green this programme exists to
prevent.** The reconciliation stands on its own and does not change if the scans come back clean; if
they do not, the new findings enter the ledger above and are dispositioned like every other.

---

## 22. SCAN 4 — RESULTS, AND WHY THE PRE-25 VERDICT IS FAIL

> **This section was written during the Scan-4 session and was never committed.** It survived only
> as a scratchpad file while three of its fixes landed as commits referencing finding IDs that
> existed in no ledger. That gap is itself the governance defect worth naming: for a period, the
> repository's authoritative checkpoint said "Scan-4 PENDING" while Scan-4 had returned, found a
> fifth P0, and had three of its findings fixed and committed above the PRE-25 reconciliation.

### 22.1 VERDICT

```text
PRE-SKYEAGLE CERTIFICATION:  FAIL
SKYEAGLE MIGRATION:          NOT AUTHORIZED
```

Settled, not provisional. Scan-4A found a **new P0** by execution; 4B, 4D and 4E returned 40 further
findings, three of them HIGH, against code and documents that had not been independently reviewed. A
large share were against work done earlier the same day by the same author who then reconciled PRE-25.

### 22.2 Dispatch

Five independent adversarial agents, read-only, dispatched at `e16913d5b`, each given the six-worktree
exclusion set (PRE-ORCH-02) and told to falsify rather than confirm.

| Agent | Area | Returned |
|---|---|---|
| 4A | translation-contract subsystem | **YES** — found a P0 |
| 4B | CI gate and guardrail false-greens | **YES** — 13 findings |
| 4C | brand leaks, live and source | **NO — never reported. Treat 4C's area as UNAUDITED.** |
| 4D | documentation truthfulness | **YES** — 12 findings |
| 4E | rename blast radius | **YES** — 15 findings across three reports |

### 22.3 Fixed during the Scan-4 session

| ID | Commit | Evidence |
|---|---|---|
| **S4-P0-40** | `8f84fd189` | 50 tests / 179 assertions, exit 0; negative control reproduced the defect, source restored to `13b00528dd5878d3` |
| **S4D-02** | `2d063ca3e` | SVG sweep 21 → 25 files; per-root guard added; control fired; restored to `70afe0047d829ffd` |
| **S4B-12 / S4E-04** | `a3035bf13` | 10 tests / 42 assertions, exit 0; tracked artefact byte-identical across the run |

**S4-P0-40 in one line:** the generated installer SQL *filters* unusable catalogue rows; the PHP upgrade
path *threw* on them. `sql_upgrade.php:526` has no PHP try/catch, so the throw landed after the DDL
applied and before the version row was bumped — **every upgrade of every affected site wedged
permanently**, recoverable only by hand-editing `lang_definitions`. Proven on three real French rows in
the shipped seed. The renderer's own comment claimed `compose()` was the PHP mirror of the SQL guard; it
was the opposite policy wearing the same name, which is why four scans walked past it.

### 22.4 PHPStan — now has a result, and it is not clean

The Scan-4 session recorded PHPStan as `NOT RUN`; the first attempt aborted with the **second** host
failure variant (`Child process timed out after 600.0 seconds`) while exiting `0`. The host-local
override now carries `parallel.processTimeout: 3600.0` and `maximumNumberOfProcesses: 4` — neither
weakens a rule, changes the level, or suppresses an error.

**A complete run was executed on 2026-08-25 and is recorded here.** Zero `Internal error`, zero
`Result is incomplete`, so RB-24 does not apply and the result is trustworthy. **Exit 1, 996 errors
displayed against the 1,000 cap.**

```text
658  sites/rdy0082restore/**   host artefact — an untracked second site (see 23.1), gitignored,
                               absent in CI. The baseline is path-keyed against sites/default/**,
                               so this is the same upstream file at an unbaselined path.
                               497 of them in statement.inc.php alone.
338  everything else           real, and CI would see them
~114 of those 338             in branding-module and branch-touched files, unbaselined
```

`.github/workflows/phpstan.yml` gates this in CI, and the branch has never been pushed, so that gate has
never run against any of this work. Remediation is recorded at §23.

### 22.5 Open Scan-4 findings not closed in the Scan-4 session

**HIGH** — all three worked in the session recorded at §23: S4E-01 (a second live site invisible to the
rename tooling), S4E-02 (repo-root pages printing a hardcoded brand name to anonymous visitors),
S4E-03 ("the next rename is one JSON edit" is false as written).

**MEDIUM-HIGH / MEDIUM** — S4E-15 (generator and loader enforce opposite invariants on one file),
S4B-01 (the gate's collapse detector counts nothing), S4B-02 (the matrix-pin guard reads the first
regex match of five), S4B-10 / S4E-06 (all four guardrails miss the two namespaces this programme
added), S4B-03 / S4B-04 (leak-extractor false negatives), S4E-13 (`i18n_generator.php` ships the whole
catalogue keyed by English constants), S4B-08 (the 11-font equality check runs in zero CI legs),
S4E-07 (`modules` has a second `directory` column), S4E-08 (`facility.name` is join-key data in
`BillingExport.csv.php`), S4E-09 (`ProductIdentity`'s tenancy rationale describes a capability it does
not have).

**LOW / documentation** — S4B-05, 06, 09, 11, 13; S4E-10, 11, 14; S4D-01 … S4D-12. The S4D set is
largely this register failing to keep up with itself. Three are substantive errors in ADR-BRAND-005 and
are carried into §23.

---

## 23. SESSION OF 2026-08-25 (afternoon) — THE FOUR HIGH BLOCKERS AND THE GUARD DEFECTS

Four agents were dispatched in parallel against the HIGH findings, partitioned by file ownership so
they could share one working tree; the orchestrator took the guard-integrity defects itself and held an
ownership tripwire over the protected files throughout. **No agent violated its ownership boundary, no
`@phpstan-ignore` or `markTestSkipped` was introduced, and no SkyEagle identity was added.**

Two agents were terminated by session usage limits rather than by task failure — B1 while waiting on a
PHPStan run, B3 mid-remediation. B1's work was complete and is verified below. **B3's is partial and is
recorded as partial.**

### 23.1 S4E-01 — a second configured site was invisible to the rename tooling — **FIXED — VERIFIED**

`sites/rdy0082restore/` is a complete tenant: `$config = 1`, database `openemr_rdy0082_restore`,
`openemr_name='Thiqa'`, `saas_branding_product_name_ar='ثقة'`, its own untracked logo assets, and **its
own `modules` row** (`mod_id=6, mod_directory='oe-module-thiqa-branding', mod_active=1`). It serves
HTTP 200. It is gitignored at `.gitignore:131`, which is why every static sweep walked past it.

§9 records `modules` as "**1 row**" and names `mod_directory` the highest-severity rename hazard,
because `ModulesApplication.php:141-161` runs `UPDATE modules SET mod_active = 0` when the path stops
resolving. **There are two such rows, in two databases.** Every branding command takes a mandatory
`--site` with no default and nothing enumerated sites, so a rename run against `--site=default` exits 0
having silently left a second fully-branded instance on the old identity.

Closed for **visibility**, which is the part that is code. `SiteInventory` enumerates configured tenants
from the filesystem — parsing `sqlconf.php` with `token_get_all()` rather than including it, so a
commented-out `$config` cannot be misread and no site's credentials enter scope — and every
tenant-scoped command now renders a notice naming the tenants it did **not** act on. `--site` still has
no default and must not acquire one. Exit codes are untouched on every path: a tenant left unbranded is
a decision for a human, not a probe result. Evidence:
`docs/evidence/EV-B1-second-configured-site-visibility.md`. **163 tests / 1,651 assertions, exit 0.**

**Whether that second tenant is retired remains an Owner decision and was deliberately not taken.**

### 23.2 S4E-02 (extended) — anonymous pages printing a hardcoded brand name — **FIXED — VERIFIED**

Scan-4E raised `admin.php`. There were three, all answering an unauthenticated request:
`admin.php` (2 literals), `sql_patch.php` (3), `ippf_upgrade.php` (3). None routed through
`xl_product_name()` or `ProductIdentity`; `admin.php` cannot, in its present form, because it never
loads `vendor/autoload.php`.

All eight converted. `admin.php` requires the one identity class directly rather than pulling in the
autoloader, and hand-rolls its escaping — the idiom already in that file. `sql_patch.php` and
`ippf_upgrade.php` resolve `text(xl_product_name())` **before any output**, deliberately: the resolver
may start a session to select the Arabic wordmark and both pages flush progress as they work, so a late
resolve would hit "headers already sent" and silently degrade to the Latin name.

**The structural half mattered more.** `BrandLeakSurfaceContractTest`'s `SCANNED_ROOTS` never included
the repository root, so `setup.php`'s hand-won S3-P1-33 closure had no regression guard behind it. The
root is now scanned as a non-recursive list of the executable entry points — recursing it would pull in
`vendor/`, `sites/` and `Documentation/` and make the gate machine-dependent.

**A third hole, found by the agent and not by this programme:** every assertion in that file hunts the
*upstream* name, so none of them could ever have caught a page printing the *configured* brand as a
literal — which is exactly what all three pages did. Widening the roots alone would still have passed.
A new guard reads the current name from the generated artefact (so the test names no brand and survives
the rename it protects) and scans `T_INLINE_HTML` as well as string tokens, since `admin.php` closes
its PHP block before printing anything.

Both extractor defects (S4B-03 / S4B-04) were closed in the same pass: the Twig matcher's character
class excluded both quote characters, defeating the backreference its own docblock credited; the PHP
tokenizer inspected only a first-position `T_CONSTANT_ENCAPSED_STRING`. Concatenation, heredocs,
`T_NAME_FULLY_QUALIFIED` calls and every top-level argument (covering `xl()`'s prepend/append) are now
seen. What remains undecidable — dynamic arguments, and concatenation *outside* the call such as
`xlt('Welcome to') . ' OpenEMR'` — is documented in the file rather than papered over.

`setup.php:1939` was ruled a **leak and converted**: it is none of the four C7 PRESERVE categories, and
its referent inside the same file was already converted at `:625`, so the cross-reference had been
stale against the heading it cites. **The S3-P1-33 closure's "zero user-facing leaks remain" was
therefore wrong**, and is corrected here rather than left standing.

Live verification, anonymous, after the change: `/admin.php` 200 with 0 old literals in source;
`/sql_patch.php` 200 with 0; `/ippf_upgrade.php` 200 with 0. Final gate on that file:
**6 tests / 16 assertions, exit 0**, with a transient end-to-end negative control that failed with the
three intended messages and exact `file:line`, then restored to SHA-256 `4fc4477cff5de5e5…`.

### 23.3 Two anonymous leaks the same class reached, found downstream — **FIXED — VERIFIED**

`admin.php:215` loads `Documentation/help_files/openemr_multisite_admin_help.php` into its help modal,
so that page is anonymously reachable too. It carried three user-facing product names, and
**`Documentation/` is scanned by no branding guard** — including the widened one, deliberately, for the
determinism reason above.

Converted through `ProductIdentity`, the same seam `admin.php` uses. **Separately, that page called
`xla()` in two `<script>` blocks and loaded nothing that defines it, so every direct load ended in
`Call to undefined function xla()` and a 500** — verified in the PHP error log against the unmodified
file. It has been broken for as long as those calls have been there. Live: **500 → 200**, heading
renders the configured product name, zero upstream names.

`Documentation/help_files/openemr_installation_help.php` carried three cross-references to
`setup.php`'s Step 2 heading, still spelled with the upstream literal after that heading was converted.
Those three now compose; live 200, 0 stale references, 3 converted. **The other 66 upstream references
in that file are deliberately untouched** — most are factual statements about the upstream OpenEMR
project, and converting them wholesale is exactly the "hardcode it into ~30 places" mistake S3-P1-33
warned against. They need a recorded class (a)/(b)/(c) pass, which is open work.

### 23.4 The Zend module installer view — **FIXED — VERIFIED**

`interface/modules/zend_modules/module/Installer/view/installer/installer/index.phtml:36,117` hardcoded
`href="https://skyeagle.uk/docs/installer"` twice with anchor text naming the product, introduced by
`df3cc18f2` in place of upstream's wiki URL. **No branding global, guard or CI gate reached it**, and
its target 404s (§23.6). The URL now follows the configured documentation link — omitting the link
entirely when none is configured, rather than emitting an empty `href` — and the product name composes
through `xlp()`.

That last change retires a translation-catalogue key, so it required a contract:
`contrib/util/language_translations/contracts/installer-third-party-modules.json`, carrying **both** the
`OpenEMR` and `Thiqa` legacy keys forward, with `on_missing_identity: skip`. Swapping the literal
without one would have orphaned every existing translation of it. **3 tests / 85 assertions, exit 0.**

### 23.5 The guard-integrity defects — **ALL FIXED — VERIFIED, each with a control**

| ID | Defect | Control that proves the fix |
|---|---|---|
| **S4B-01** | The collapse detector's docblock promised "a floor on the total"; the body asserted only ≥4 paths and ≥1 `*Test.php` per directory. The gate could fall 513 → 7 and stay green. | A fake tree of four empty `*Test.php` files: **old detector PASS, new detector FAIL**. Separately, raising the floor to 999 fires naming the real count (260); source restored byte-identical. |
| **S4B-02** | The matrix-pin guard `preg_match`ed the **first** `if: matrix.php-version ==` in a workflow that now has five. | A decoy step inserted above the gate: **old regex reads `7.4`, new step-scoped read returns `8.2`**. *Correction to the scan report:* line 52 **is** the branding step today, so the guard was correct by ordering accident, not validating the wrong step. The real workflow was never written to. |
| **S4B-10 / S4E-06** | All four rules gated on `MODULE_NAMESPACE`; `OpenEMR\Common\Branding` and `OpenEMR\Branding` — the newest and least supervised branding code — were outside every guardrail. | Two new fixtures, byte-identical to the in-module one from line 10 down, differing only in the namespace: both now fire. Narrowing the scope back to module-only makes exactly those two plus the structural coverage assertion fail; source restored to `c5ea364e2ecbcb0b`. **67 tests / 335 assertions, exit 0.** |
| **S4B-08** | `/public/assets/*` is gitignored and no CI step installed assets, so the 11-font byte-equality check ran in **zero** legs while printing a reassuring "not installed" line. | CI now runs `install-assets.php` before the gate, and the gate declares `OPENEMR_DEPLOYED_ASSETS_REQUIRED` on that leg — the same "environment declares its obligation" wiring `e203d5bdd` gave the Q77 theme check. Removing the install step makes the guard fail with the intended message; workflow restored byte-identical. |
| **S4E-15** | `globalsRowValue()` returned on the first matching key, so a duplicated global produced a clean committable artefact that CI accepted, while `BrandingProfile::fromEntries()` threw on the same file at runtime — failures on opposite sides of the CI boundary. | A scratch profile duplicating `main_menu_logo_link`: **generator and loader now both refuse**, the generator naming both row indices. The real artefact hash is unchanged (`4cf14f3ad97d25d1`), so the change is behaviour-preserving on the real profile. |

**Scope now has one owner.** The four private `MODULE_NAMESPACE` copies are replaced by
`tests/PHPStan/Rules/BrandingGuardrailScope.php`, and a new test forbids a rule reintroducing its own
namespace literal — the S1-P2-12 lesson applied where it had not been.

### 23.6 ADR-BRAND-006 — the shipped product URLs — **WRITTEN, AWAITING OWNER RULING**

`docs/branding/adr/ADR-BRAND-006-shipped-product-url-identity.md`. `library/globals.inc.php:454–494`
writes `openemr_name='Thiqa'` alongside three `skyeagle.uk` URLs into the globals defaults at install
time, so a fresh install from this branch is born with a name and links that disagree.

**The severity is higher than "mismatch".** `https://skyeagle.uk/support` and `https://skyeagle.uk/docs`
both return **404**, at every locale prefix; `reg.skyeagle.uk` is NXDOMAIN. The root is live and its
title names five SkyEagle product lines with **zero** occurrences of "Thiqa". Two of three shipped URL
defaults are dead links. `main_menu_logo_title` also ships **blank**, despite the profile's own note
saying it must not be.

**No guard covers this**, and eleven existing assertions actively pin the mismatch as correct. Every
gate asks "does the artefact match the profile?"; none asks "is the profile right?" — which is why four
scans passed over it.

Provenance corrected: `skyeagle.uk` entered at **`a1c22b6a1`**, not `b3b821ffa` as ADR-BRAND-005:116
states — that commit touches the profile on one line of unrelated prose. Two further ADR-BRAND-005
errors are recorded there: `:78-79` claims every read is escaped with `text()` while `setup.php:1945`
correctly uses `js_escape()` for a JS sink, and its PRESERVE citation of `:513-515` points at the
*converted* success message when the genuine preserved references are `:526-527`.

### 23.7 Owner decision taken 2026-08-25 (afternoon)

| Decision | Ruling |
|---|---|
| Apply SkyEagle branding now? | **No — hold the rename until the approved artwork exists.** There is no SkyEagle asset in this repository: the only token file is `brand/tokens/thiqa-tokens.json` (primary `#0B1B4D`), every master SVG is the current mark, and KG-03's authoritative SkyEagle colours (`#0B376E`, `#1E5A96`, `#0B4E91`) appear nowhere. KG-05 forbids fabricating one. Renaming the text alone would ship a SkyEagle-named product wearing the old mark; the rename runs as **one coherent pass** when the vector and palette arrive. |

### 23.8 B3 — PHPStan remediation — **PARTIAL, NOT VERIFIED**

The agent was terminated by a usage limit while its confirming PHPStan run was still I/O-bound (workers
showed ~3 minutes of CPU over ~2 hours of wall clock — the documented Drive-mount "blocked, not busy"
signature). Its edits are in the tree and are recorded here as **unverified against PHPStan**:

```text
…/Console/SeedDemoCommand.php                     +242/-  (the 65-error file)
…/Console/BackupCommand.php                       +145/-  (22 errors)
src/Common/Translation/QueryUtilsTranslationCatalogueStore.php   +76/-  (10)
interface/language/lang_definition.php             +28/-  (8)
interface/globals.php                               +5/-  (1)
.phpstan/baseline/variable.undefined.php           -5     stale entry REMOVED, not added
```

The baseline deletion is correct and worth naming: `CLAUDE.md` requires that a file's existing baseline
entries be fixed when the file is modified, and the removed entry (`$go` might not be defined in
`lang_definition.php`) was already producing an `ignore.unmatched (non-ignorable)` error in the full run.
**No baseline entry was added anywhere, and no `@phpstan-ignore` was introduced** — verified by an
ownership tripwire held over the whole session.

**A clean full PHPStan run is still outstanding and is the single largest remaining input to PRE-25.**

### 23.9 Recorded, not fixed

- **The live database was written to.** Verifying `sql_patch.php` by anonymous GET does not merely
  render it — it *runs the patch*: `upgradeFromSqlFile()`, `UuidRegistry::populateAllMissingUuids()`,
  and inserts of missing `globals` rows. The agent disclosed this rather than hiding it. **Checked
  afterwards: `v_database` is still 541 and all five branding values are unchanged**, so the write
  populated UUIDs and nothing of consequence — but the programme's "live database mutated: NO"
  invariant is broken and is recorded as broken. **This page cannot be verified by live GET without
  mutating state; that belongs in the method, not in a future rediscovery.**
- **S4E-07** — `sql/database.sql:7819` declares `directory VARCHAR(255)` alongside `mod_directory`;
  neither §9 nor §10 records it. `ModuleMenuSubscriber.php:112` also derives an ACL section from the
  directory name, so a rename silently re-keys an ACL check.
- **S4E-08** — `custom/BillingExport.csv.php:182` joins `LEFT OUTER JOIN facility AS f ON f.name = e.facility`.
  A facility rename empties address and CLIA on every pre-rename encounter, with no error. Not changed:
  altering a billing export's join key is a behaviour change, not a lint fix.
- **S4E-13** — `library/ajax/i18n_generator.php:23-29` ships the whole catalogue keyed by English
  constant names. Authenticated and CSRF-gated, so bounded — but the keys are where the brand survives
  a value-level rename.
- **`public/images/login-logo.svg`** is the upstream "OpenEMR Logo Vectorized" and is inlined by
  `interface/smart/register-app.php:374,383`. It sits outside `public/images/logos/`, the subtree the
  SVG sweep was narrowed to at `2d063ca3e`, so no asset guard covers it. *Correction to the original
  observation:* the page carries `$ignoreAuth = true` but dies with "Not Authorized" unless
  `rest_fhir_api` is on — verified here, 14 bytes. On a deployment with FHIR enabled it **is** an
  anonymous surface serving upstream branding. Replacing it needs an approved asset, so it is held with
  the rename.
- **Scan-4C never reported.** The live brand-leak surface is **UNAUDITED**, and live-driven audits have
  produced this programme's two highest-value findings.
- `Documentation/help_files/openemr_installation_help.php` — 66 upstream references awaiting a recorded
  PRESERVE/convert classification pass.

### 23.10 What remains before PRE-25 can be re-attempted

```text
1. PHPStan                    B3's remediation is partial and unverified; no clean full run exists
2. Scan-4C                    re-dispatch; the live leak surface is unaudited
3. ADR-BRAND-006              Owner ruling on the shipped URLs (two of three 404)
4. S4E-07 / S4E-08 / S4E-13   recorded above, unfixed by decision
5. Push / CI                  the branch is ahead of its remote and CI has never run against this work
6. PRE-25                     re-run §21 against the corrected ledger
7. SkyEagle rename            HELD pending approved artwork (§23.7)
```

**Certification remains NOT PASSED.**

---

## 24. SCAN-4C — THE UNAUDITED SURFACE, NOW AUDITED

Scan-4C never reported during the Scan-4 session, and §22.2 recorded its area as **UNAUDITED**.
It was re-dispatched and returned: `docs/evidence/EV-SCAN4C-live-leak-audit.md`. Read-only, no
repository file edited, live database not mutated — verified by an identical before/after
`globals`/`version` snapshot, and by deliberately **not** issuing a GET to any side-effect-bearing
endpoint. That last point is the method correction §23.9 asked for, applied: the audit read
`acl_upgrade.php` and confirmed it writes ACL rows on every load, then avoided executing it.

### 24.1 S4C-01 — the session machinery ships the upstream name to every visitor — **CONFIRMED; PRESERVE by locked decision**

Three surfaces, one root cause. `SessionUtil::CORE_SESSION_ID = "OpenEMR"`
(`src/Common/Session/SessionUtil.php:81`) is the PHP session cookie's own name; the same literal
is the *value* of the year-lived `App` cookie (`interface/login/login.php:49`); and it reaches
authenticated page JavaScript as `var oemr_session_name = "OpenEMR"`
(`library/restoreSession.php:32`).

**The finding is real and the disposition was already taken.** §10 lists `SessionUtil` identity
constants under *Must NOT be renamed* (locked Q17/C6), and the audit reached the same practical
conclusion independently and for the right reasons: the cookie name is machine identity, not
product identity, and renaming it breaks existing sessions, load-balancer sticky-cookie rules and
anything keyed on it. It is plumbing that happens to spell the upstream name, not a brand leak in
the sense the leak guard exists to catch.

**What is genuinely new, and worth keeping:** the audit explains *why no guard can see this class*.
`BrandLeakSurfaceContractTest` scans PHP and Twig source for rendered product-name strings. A
`Set-Cookie` header is emitted by PHP's session machinery rather than printed as page content, and
`CORE_SESSION_ID` is not an `xl()`/`xlt()` argument — so it is structurally outside every scanned
surface, and would remain so however far the roots were widened. That is a real limit of the
guard's model, and it is recorded rather than mistaken for coverage.

### 24.2 S4C-02 — the second tenant served old branding to anonymous visitors — **CLOSED by the rename**

Raised as a live escalation of S4E-01: the CLI-side fix made the second tenant *visible to an
operator*, but nothing made the served page tell anyone, and `rdy0082restore` was answering
anonymous requests fully branded to the previous identity.

**Closed the same day, by acting on what the S4E-01 notice said.** `apply-profile` was run against
both tenants; the notice fired on each run naming the other. Verified live, anonymous, after both:

```text
/interface/login/login.php?site=default          SkyEagle=2  Thiqa=0   title: SkyEagle Login
/interface/login/login.php?site=rdy0082restore   SkyEagle=2  Thiqa=0   title: SkyEagle Login
```

The split-brain that finding described no longer exists. **Whether that tenant should exist at all
is still an Owner decision and is still untaken** — it is now consistently branded rather than
retired, which is a different thing and is recorded as such.

### 24.3 S4C-03 — blast radius of the two dead URLs — **informational, feeds ADR-BRAND-006**

Both 404-ing `skyeagle.uk` URLs are reachable from exactly one page, `about_page.php`, two clicks
from any authenticated user. That is narrower than "shipped in the installer defaults" implied on
its own, and it is the number the ADR-BRAND-006 ruling should be taken against: the exposure is
real, bounded, and authenticated-only.

### 24.4 What this changes about the register

Scan 4 is now **five of five returned**. The blocker recorded at §22.2 and §23.10 item 2 is
discharged. Two of the three findings are dispositioned above; the third is informational and
attaches to an open Owner decision rather than standing alone.

**No Scan-4C finding blocks certification.** S4C-01 is a locked PRESERVE, S4C-02 is closed, S4C-03
is an input to a ruling that was already outstanding.

---

## 25. PHPSTAN CLOSED, AND THE UPDATED REMAINDER (orchestrating session, 2026-08-26)

Dispatched as one of two parallel blockers alongside the Scan-4C re-dispatch (§24). Ran long — 8
full analyse passes plus corrections, ~7.5 hours of agent wall time — but landed a genuinely
verified result, independently re-checked by the orchestrating session rather than taken on the
agent's word alone.

### 25.1 PHPStan — **CLOSED**, superseding §22.4/§23.8/§23.10 item 1

`fix(prebrand): the branch's own PHPStan errors, from 270 to 22` (`c124660a5`) plus a second
commit for two issues the orchestrator's own verification pass caught
(`fix(prebrand): regenerate the stale installer SQL supplement, fix an unsatisfiable risky-test
expectation`, `9a2300954`). Full detail, including the two fixes that needed correcting mid-pass
and the orchestrator's independent re-verification: `docs/evidence/EV-B4-phpstan-clean-run.md`.

```text
Baseline (this session, current HEAD):  1212 total, 658 rdy0082restore (out of scope), 554 real
Branch-attributable (git-blame vs merge-base b91c12aee3f6...): 270 of the 554
Fixed: 248.  Remaining: 22, all documented file:line, all deliberate (§4.8/§6 of the evidence file):
  - ~13 in interface/reports/pat_ledger.php's CSV-export $_REQUEST handling — needs a
    filter_input()/Request refactor across a ~1000-line legacy financial report; not safely
    verifiable live on this host without either mutating production-shaped billing state or a
    working browser session, so left open rather than guessed at.
  - 1 in src/Common/Branding/ProductIdentity.php:223 (error_log()) — hinges on an installer
    bootstrap-order question only the real installer can answer.
Final run (run8): 908 total, 658 rdy0082restore (unchanged, still out of scope), 250 real.
RB-24 verified clean (zero "Internal error", zero "Result is incomplete") on every one of 8 runs.
```

**One collateral fix outside branding scope, flagged for independent review rather than buried:**
`tests/PHPStan/Rules/ForbiddenGlobalNamespaceRule.php` compared Windows backslash paths against
the forward-slash paths in `composer.json`'s `autoload.files`, so every legacy global function in
every allow-listed file — not just this branch's — was misreported as forbidden on this
native-Windows host. Reproduced standalone, fixed with one `str_replace('\\', '/', ...)`, a no-op
on the POSIX paths CI actually runs on.

**Independent re-verification (orchestrating session, not the dispatched agent) found and fixed
two further real issues** that the PHPStan pass's own scope didn't cover — both confirmed via
`git stash` against the clean committed HEAD to be pre-existing/self-inflicted rather than
regressions from anything dispatched in this session:

1. `contrib/util/language_translations/durableTranslationContracts_utf8.sql` — the SQL supplement
   real installs and upgrades actually execute — was never regenerated after the
   `installer-third-party-modules-neutral-v1` contract landed (§23.4, `df3cc18f2`). The contract
   set correctly discovers and renders all 29 contracts; the deployed file only ever shipped 28.
   That contract's translations have never reached a real install or upgrade. Regenerated from the
   renderer's own output.
2. `BackupRetentionTest::testAcceptedLabelsUseOnlyThePortableGrammar` used
   `expectNotToPerformAssertions()`, which the same class's `setUp()`/`tearDown()` (which
   unconditionally assert) made unsatisfiable regardless of the method body — all 5 data-set runs
   were flagged risky. Fixed by calling the validator directly.

Full scoped isolated suite, final confirming run: **1724 tests, 8590 assertions, 0 failures,
0 risky, exit 0.** (One transient failure, a 25-second subprocess-spawn timeout in
`MaterialiserKillRecoveryTest` under full-suite Drive-mount I/O contention, was investigated and
is not a regression — passed cleanly in isolation at 12.2s; the same documented class of slowness
as the Twig-render hang, noted so a future flake on this test isn't mistaken for new.)

### 25.2 SkyEagle rename — **DONE**, superseding §23.7's "HELD"

Between the two blockers above being dispatched and this reconciliation, a concurrent session on
this same host took the Owner ruling recorded at commit `af36dbef3` ("Owner ruling, 2026-08-25:
the new branding applies") and executed it: `af36dbef3` (six profile values, everything else
composed automatically, 523 tests / 4311 assertions green, no test edited), `48f09c523` (KG-01
internal module-identity rename, `ThiqaBranding` → `SkyEagleBranding` and three sibling token
classes, bounded and enumerated first), and `bad90c7ef` (a gate-coverage defect the rename walk
surfaced: the canonical gate ran 523 of the branding module's 1,448 declared tests — nine whole
test directories were in neither the gate nor any assertion about the gate). **This orchestrating
session did not initiate or approve the rename** — it was already committed, under a cited Owner
ruling, by the time this session next checked `git log`. Recorded here for continuity rather than
re-litigated; see those three commits directly for full detail.

### 25.3 Updated remainder — supersedes §23.10

```text
1. PHPStan                    CLOSED — §25.1
2. Scan-4C                    CLOSED — §24
3. ADR-BRAND-006              Owner ruling on the shipped URLs — PARTIALLY addressed: af36dbef3
                               resolved the name/link mismatch half (product_name now agrees with
                               the skyeagle.uk links); the two 404s at /support and /docs are
                               unaffected and still need a ruling on their own terms.
4. S4E-07 / S4E-08 / S4E-13   still recorded, still unfixed by deliberate decision (§23.9)
5. Push / CI                  branch is now several commits further ahead of origin; still never
                               pushed, CI has still never run against any of this work
6. PRE-25                     still needs a re-run of §21 against this now-further-updated ledger
7. SkyEagle rename            DONE — §25.2 (no longer HELD)
```

**Certification remains NOT PASSED.** Items 3, 5 and 6 are the load-bearing gaps: 3 needs an Owner
ruling this session cannot take unilaterally, 5 needs an explicit go-ahead before pushing to a
remote (a shared-state action, not taken without asking), and 6 is a large re-reconciliation pass
that should run only after 3 and 5 are settled, not before.

---

## 26. PRE-26 — RECONCILIATION AFTER SCAN-4 AND PHPSTAN CLOSURE (orchestrating session, 2026-08-26)

The Owner authorized the push (§25.3 item 5, done — HEAD `ae9c3317e` pushed to
`origin/feat/thiqa-branding-foundation`) and asked for this reconciliation, with ADR-BRAND-006
explicitly left for their own ruling rather than acted on. This section reconciles the 40 Scan-4
findings §22.5 left open the same way §21 reconciled the original 34 — one final state per finding,
nothing merged away, no finding closed because a test passes.

### 26.1 Scan-4 HIGH — three findings, three closed

| ID | Finding | Final state | Evidence |
|---|---|---|---|
| S4E-01 | Second live site invisible to the rename tooling | **FIXED — VERIFIED** | §23.1; live-escalation half closed by §24.2 |
| S4E-02 | Repo-root pages printing a hardcoded brand name to anonymous visitors | **FIXED — VERIFIED** | §23.2, §23.3 |
| S4E-03 | "The next rename is one JSON edit" is false as written | **PARTIALLY CONFIRMED, NOT FULLY CLOSED — see below** | §25.2 |

**S4E-03 needs an honest correction rather than a clean checkmark.** §22.5 filed it under "worked in
the session recorded at §23," but no subsection of §23 carries its ID or disposition explicitly —
this reconciliation is the first place it is actually addressed. The SkyEagle rename that happened
between the two blockers in this section being dispatched (§25.2) is direct empirical evidence on
the finding's literal claim, and it cuts both ways: the **product-identity** half of a rename
genuinely is now one JSON-shaped step (`af36dbef3`: six profile values, `apply-profile` per tenant,
zero test edits) — but that rename explicitly did **not** touch the **module-identity** half, which
required a separate, real refactor commit (`48f09c523`, `ThiqaBranding` → `SkyEagleBranding` across
719 references and three sibling token classes). So the finding's claim is **still true** for
module identity and **now refuted** for product identity — it was never one undifferentiated claim,
and no prior session's text drew that line. Recorded here as the finding's actual disposition;
carrying a name change again would still mean two separate steps, not one.

### 26.2 Scan-4 guard-integrity and extractor defects — nine findings, nine closed

| ID | Finding | Final state | Evidence |
|---|---|---|---|
| S4B-01 | Collapse detector's floor didn't match its docblock's promise | **FIXED — VERIFIED** | §23.5 |
| S4B-02 | Matrix-pin guard read the first of five regex matches | **FIXED — VERIFIED** | §23.5 |
| S4B-03 | Twig leak-extractor character class excluded both quote chars | **FIXED — VERIFIED** | §23.2 |
| S4B-04 | PHP leak-extractor inspected only a first-position string token | **FIXED — VERIFIED** | §23.2 |
| S4B-08 | 11-font equality check ran in zero CI legs | **FIXED — VERIFIED** | §23.5 |
| S4B-10 / S4E-06 | Guardrails missed the two namespaces this programme added | **FIXED — VERIFIED** | §23.5 |
| S4E-15 | Generator and loader enforced opposite invariants on one file | **FIXED — VERIFIED** | §23.5 |

Seven rows for nine IDs (`S4B-10`/`S4E-06` share one disposition, as they did in §22.5's own
grouping). **Zero open in this class.**

### 26.3 Scan-4 MEDIUM — recorded, not fixed, by deliberate decision (unchanged since §23.9)

| ID | Finding | Final state | Reasoning |
|---|---|---|---|
| S4E-07 | `modules` has a second `directory` column `ModuleMenuSubscriber.php` also keys an ACL section from | **RECORDED, NOT FIXED — deliberate** | Rename blast-radius hazard, not a present defect; acting on it means widening the rename tooling's scope, not a lint fix. §23.9. |
| S4E-08 | `facility.name` is join-key data in `BillingExport.csv.php` | **RECORDED, NOT FIXED — deliberate** | A facility rename would silently empty address/CLIA on pre-rename encounters. Altering a billing export's join key is a behaviour change requiring its own review, not something to fold into a lint pass. §23.9. |
| S4E-13 | `i18n_generator.php` ships the whole catalogue keyed by English constant names | **RECORDED, NOT FIXED — deliberate** | Authenticated and CSRF-gated, so bounded; the keys are where the brand survives a value-level rename. §23.9. |

These three are dispositioned, not silent — the same class as §21.3's S2-P1-24 (Arabic logo,
deferred with explicit justification) or S3-P1-30 (accepted with Owner decision). None represent an
active, reachable brand leak or governance breach; all three are scoped, bounded, and documented.
**Not blocking.**

### 26.4 Scan-4 MEDIUM — spot-checked now, not independently re-verified against the original quote

| ID | Finding | Current state |
|---|---|---|
| S4E-09 | `ProductIdentity`'s tenancy rationale describes a capability it does not have | `src/Common/Branding/ProductIdentity.php:51-53,100-101` reads, today, as hedged/aspirational ("the direction the five-plane architecture is heading," "and, later, a multi-tenant caller") rather than a present-tense capability claim. This reconciliation spot-checked the current docblock and found no false present-tense claim — but did not diff against the exact wording Scan-4E's agent quoted, so this is recorded as **LIKELY ALREADY ACCURATE, NOT FORMALLY CLOSED** rather than claimed fixed. |

### 26.5 Scan-4 LOW / documentation — genuine, named audit debt

**Not individually re-verified in this reconciliation:** S4B-05, S4B-06, S4B-09, S4B-11, S4B-13,
S4E-10, S4E-11, S4E-14, and the S4D set (S4D-01…S4D-12, twelve items), except the three S4D items
§23.6 already named explicitly (the `skyeagle.uk` provenance correction and two ADR-BRAND-005
citation errors — those three are **FIXED**, folded into ADR-BRAND-006's text). The remaining
roughly seventeen LOW/documentation items were triaged by Scan-4's own severity label and were not
re-derived here against current source, on a deliberate resource trade-off: chasing seventeen
low-severity documentation nits to the same evidentiary standard as this section's other findings
would cost materially more than their severity justifies, and none of the seventeen were flagged by
Scan-4B/4D as affecting a user-facing brand leak, a governance breach, or a guard's correctness —
the classes this programme treats as blocking. **This is recorded as open audit debt, not closed and
not silently dropped** — a future low-priority pass should sweep these seventeen the same way §23
swept the HIGH and guard-integrity classes.

### 26.6 PHPStan and Scan-4C

Both closed. §25.1 (PHPStan, 270→22 branch-attributable, 22 deliberately deferred and documented,
plus two further real bugs found and fixed by this reconciliation's own independent verification).
§24 (Scan-4C, 5/5 Scan-4 agents now returned, all three findings dispositioned).

### 26.7 Push / CI

Pushed 2026-08-26, `65616d4b2..ae9c3317e` (plus the three commits from §25/§26 landing after that
push — a further push is needed to bring origin current with this section). **CI has still never
run against any of this work.** `.github/workflows/*.yml` trigger only on `push`/`pull_request`
targeting `master` or `rel-*` — a feature-branch push does not fire them, confirmed via
`gh run list` returning nothing for this branch. Opening a PR would trigger CI; the Owner was asked
and explicitly deferred that decision (**not now**). This remains a real, unclosed gate — not a
formality — because every other verification in this entire programme has run against this branch's
own state and no external CI system has yet independently reproduced any of it.

### 26.8 VERDICT

```text
PRE-SKYEAGLE CERTIFICATION:  CONDITIONAL — CODE AND AUDIT GATES CLEAR, CI GATE NOT YET RUN
SKYEAGLE MIGRATION:          ALREADY EXECUTED (§25.2), UNDER A CITED OWNER RULING, NOT BY THIS SESSION
```

Every P0 (5/5), every P1 (22/22), every Scan-4 HIGH (3/3) and every guard-integrity defect (9/9,
§26.2) is closed. PHPStan and Scan-4C, the two items that blocked the previous verdict outright, are
both closed (§26.6). Three MEDIUM findings remain open **by deliberate, documented decision**
(§26.3) — not oversight — and are not blocking on the same precedent §21 already established for
comparable P1/P2 dispositions. One MEDIUM (S4E-09) is very likely already accurate but not formally
re-verified (§26.4). Roughly seventeen LOW/documentation items remain genuine, named audit debt
(§26.5) — real, but never claimed by any prior scan to affect a user-facing or governance-blocking
property.

**This does not round up to an unconditional PASS**, for two reasons that are facts, not judgment
calls: (1) CI has never run against this branch in any form — every gate that has passed, has passed
only under this session's and its predecessors' own local execution, never independently; (2)
ADR-BRAND-006's two dead shipped URLs remain an open Owner ruling, per the Owner's explicit
instruction this session — flag it, do not act on it. Writing "PASS" over either of those would be
exactly the false green this programme's own §21.7 warned against issuing early. The concrete
remainder, in order:

```text
1. CI            open a PR (or otherwise get this branch through the workflows that gate master/rel-*)
2. ADR-BRAND-006 Owner ruling on the two dead skyeagle.uk URLs — explicitly deferred, not this session's to take
3. Push          one more push needed; this reconciliation's own commit lands after the last push
4. S4E-09 / LOW set   optional low-priority sweep (§26.4, §26.5) — not blocking, recorded so it isn't lost
```

---
