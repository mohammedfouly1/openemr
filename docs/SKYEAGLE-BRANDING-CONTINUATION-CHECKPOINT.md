# SKYEAGLE BRANDING — PHASE B CONTINUATION CHECKPOINT

> **Scope.** This is the Phase-B execution log: the actual Thiqa → SkyEagle brand migration,
> authorized by the Owner after PRE-SKYEAGLE certification passed. The PRE checkpoint
> (`docs/PRE-SKYEAGLE-CONTINUATION-CHECKPOINT.md`) remains the historical record of the
> preparation/audit programme and is **not** rewritten here. This file is the continuation point if
> a session ends mid-migration — a future session must resume from here, not restart Phase B.

**Revision: 1**
**Started:** 2026-08-26
**Certified PRE baseline SHA:** `826d194eb`
**Branch:** `feat/skyeagle-branding`
**Current HEAD:** `826d194eb` (branch just created, no Phase-B commits yet)
**OpenEMR baseline (frozen, must not drift):** 8.2.0 / rel-820

---

## 1. Authorization

```text
PRE-SKYEAGLE CERTIFICATION: PASS
MASTER INTEGRATION: COMPLETE
SKYEAGLE BRAND MIGRATION: READY FOR OWNER AUTHORIZATION
```

Owner authorization received 2026-08-26: `APPROVE PHASE B — START SKYEAGLE MIGRATION`, with a
detailed execution prompt establishing the branch rule, phase sequence, Owner Decision Gates, and
final certification format used throughout this document.

## 2. Initial branch relationship — verified

```text
master                          = 826d194eb
origin/master                   = 826d194eb
feat/skyeagle-branding           = 826d194eb  (created from master, not reset onto it)
origin/feat/skyeagle-branding    = 826d194eb  (pushed, tracking configured)
```

No merge/rebase/cherry-pick/revert in progress. Working tree real-content-clean (confirmed via
full-tree `git diff` = 0 lines; `git status` shows the same DriveFS stat-cache noise documented
extensively in `CLAUDE.local.md` and the PRE checkpoint — not real changes, not staged, not
committed).

## 3. Product baseline freeze

SkyEagle remains based on **OpenEMR 8.2.0 / rel-820**. No upstream sync, no dependency upgrades
beyond what PRE already integrated, no unrelated modernization. Any out-of-scope defect found during
migration is classified `PHASE-B-BLOCKER` / `OUT-OF-SCOPE-HIGH/MEDIUM/LOW` / `OBSERVATION` and
recorded in §9 below, not silently fixed.

## 4. Approved brand identity (as given by the Owner, 2026-08-26)

```text
Product name (EN):     SkyEagle
Product name (AR):     سكاي إيجل           (KG-05: this exact spelling, already correct in the
                                            existing profile — verified below)
Tagline (EN):           Better care begins here.
Tagline (AR):           من هنا تبدأ رعاية أفضل.
Example tenant (EN):    International Healthcare Center
Example tenant (AR):    مركز الرعاية الصحية الدولي
Support URL:             https://skyeagle.uk/en/contact   (already correct — ADR-BRAND-006)
Resources/manual URL:    https://skyeagle.uk/en/resources  (already correct — ADR-BRAND-006)
```

**Do not conflate:** SkyEagle = product/vendor identity; "International Healthcare Center" = example
healthcare tenant (demo data, not product configuration); OpenEMR = upstream/base software identity,
preserved wherever technically/historically required (licensing, upstream attribution).

## 5. Authoritative source documents reconstructed this session

| Source | What it governs | Status |
|---|---|---|
| `interface/modules/custom_modules/oe-module-skyeagle-branding/config/branding-profile.json` | The canonical, declarative product-level identity — the **only** input to `skyeagle-branding:apply-profile`. Full content re-read this session (§6 below). | Authoritative, current |
| `docs/PRE-SKYEAGLE-CONTINUATION-CHECKPOINT.md` §11 "OWNER DECISIONS KG-01…KG-09 — DO NOT REOPEN" | Locked visual/technical migration decisions taken during PRE, specifically for Phase B | Authoritative, binding, re-read in full this session |
| `docs/PRE-SKYEAGLE-CONTINUATION-CHECKPOINT.md` §12 "PROVISIONAL 43-KEY SKYEAGLE TOKEN PLAN" | The worked color-token migration plan (derivation rules, WCAG validation, provenance categories O/P/D/N) — marked NOT IMPLEMENTED, gated on S1-P0-09's repair | S1-P0-09 confirmed FIXED — VERIFIED (§21.2, commit `566b14ea6`) — precondition satisfied; plan itself must still be **revalidated**, not blindly trusted, before `skyeagle-tokens.json` is written |
| `brand/tokens/thiqa-tokens.json` | Current (Thiqa-era) token source the generator reads | Untouched since `a12e63471`/`d9757fc55` (pre-dates the SkyEagle rename entirely) — confirmed via `git log` |
| `brand/master/*.svg` | Master logo/symbol vectors | Untouched since the same two commits — still the Thiqa-era artwork, colors, and (per KG-03) needs **recoloring, not redesign** |

### 5.1 The nine locked KG decisions, restated for Phase-B reference

- **KG-01** — Full internal module identity rename during Phase B. *Largely already satisfied*: the
  active module and namespace were renamed in the PRE session (`48f09c523`,
  `oe-module-thiqa-branding` → `oe-module-skyeagle-branding`, `ThiqaBranding` →
  `SkyEagleBranding`). Preserve genuinely brand-neutral identifiers (see KG-06).
- **KG-02** — SkyEagle CTA maps to `interactive.primary`, not Thiqa's coral-primary/navy-secondary
  scheme. Construct `interactive.secondary` separately from the approved palette.
- **KG-03** — Authoritative colours: primary `#0B376E`, accent `#1E5A96`, link `#0B4E91`. Override
  artwork-sampled and live-site values. **Recolour the existing master vector — geometry,
  proportions, negative space, beak direction, S curvature, E bars, feather forms, silhouette must
  NOT change.** (This resolves what looked like a GATE-1 "missing asset" concern in §6/B2 below: B2
  is a recolor operation on an existing, unmodified vector, not a new design commission.)
- **KG-04** — Preserve validated functional/semantic colours; mechanically derive structural values;
  classify every token's provenance O(wner)/P(reserved)/D(erived)/N(ew). Surface any material new
  visible brand decision before executing it.
- **KG-05** — No AI redesign of the trademark. Dedicated Arabic wordmark preferred; **until one is
  available, the approved symbol + English wordmark is an explicitly disclosed interim** for Arabic
  contexts — never silently presented as a finished Arabic wordmark. Arabic textual brand name is
  `سكاي إيجل`.
- **KG-06** — Theme labels become "SkyEagle Light" / "SkyEagle Dark". **CSS filenames unchanged**
  (`style_light.css` etc. stay as-is — this is why `interface/themes/thiqa/` and the `--thiqa-*`
  custom-property names are NOT renamed in Phase B; they are permanent technical plumbing, not
  user-facing). Admin selector's filename-derived Light/Dark behaviour preserved.
- **KG-07** — Carry the Arabic-PDF Option C deferral forward. Do not reintroduce Amiri. Do not claim
  Arabic PDF support that hasn't passed real rendering.
- **KG-08** — Light theme intentionally flat: `background = surface = #FFFFFF`. Hierarchy via
  borders/sunken surfaces/spacing/elevation, not a background/surface color split.
- **KG-09** — Telemetry: already closed. OFF by three independent mechanisms, live-proven in PRE
  Scan 2H. No action needed in Phase B.

## 6. Live state reconstructed this session (read-only checks)

**Current `branding-profile.json` values** (full file re-read, all 33+ rows): `product_name` =
`SkyEagle` ✓; `product_name_ar` = `سكاي إيجل` ✓ (matches KG-05 exactly); `saas_branding_product_name_ar`
= `سكاي إيجل` ✓; support/resources URLs already correct (ADR-BRAND-006) ✓.

**Two findings, both real, neither previously recorded:**

- **B1-F01** — `login_tagline_text` = `"Clinical confidence, connected care."` /
  `"ثقة إكلينيكية، رعاية مترابطة."` — this is the **old** tagline. The Owner's newly-approved tagline
  (`"Better care begins here."` / `"من هنا تبدأ رعاية أفضل."`) has never been applied anywhere.
- **B1-F02** — `main_menu_logo_title`'s `value` (English) correctly reads `"SkyEagle Health
  Information System"`, but its `value_ar` documentation field still reads
  `"نظام ثقة للمعلومات الصحية"` (contains `ثقة`/Thiqa) — a residue the PRE-session rename missed.
  `value_ar` is documentation-only in this file (not written to `globals` directly, per the file's
  own header), but it is the authoritative record a translator would work from, so it is in scope.

**Live database state** (`SELECT` only, no mutation — `openemr` DB, site `default`):

```text
openemr_name                    = SkyEagle                          (current, matches profile)
saas_branding_product_name_ar   = سكاي إيجل                          (current, matches profile)
login_tagline_text              = Clinical confidence, connected care.  (STALE — old tagline, source
                                                                          not yet updated either, see B1-F01)
main_menu_logo_title            = SkyEagle Health Information System  (current)
online_support_link             = https://skyeagle.uk/support         (STALE — pre-ADR-BRAND-006 value;
                                                                          source already fixed, this
                                                                          tenant was never re-synced)
user_manual_link                = https://skyeagle.uk/docs            (STALE — same cause)
```

This confirms, with live evidence, the B9 concern the Owner's prompt anticipated: the `default`
tenant's database has never had `skyeagle-branding:apply-profile` re-run since ADR-BRAND-006 (or,
after this session, since the tagline fix). **Per explicit instruction, this is not touched now.**

**Example tenant / demo data** (`SELECT` only):

```text
facility.name (id 3) = "Thiqa Demo Eye Clinic"
```

This is tenant/demo data (the branding profile's own `omitted` section explicitly classifies
`facility.name` as "tenant data, not product configuration, and not a global"). `SeedDemoCommand.php`
reads whatever facility already exists rather than hardcoding a name — no source-level script
generates "Thiqa Demo Eye Clinic" as a literal, so there is no code-level fix available here; renaming
it is a live-data write, in scope for B9/GATE-4, not before.

**Visual assets** (`git log` on `brand/master/`, `brand/tokens/thiqa-tokens.json`): untouched since
the original Thiqa asset-kit commits (`a12e63471`, `d9757fc55`), predating the SkyEagle rename
entirely. No SkyEagle-recolored vector or token file has ever been produced. B2/B3 start from a clean
slate, using KG-02/03/04/08 and the provisional 43-key plan as the specification.

## 7. Phase sequence (planned, per Owner's execution prompt)

```text
B1  Source-of-truth product identity        — tagline fix, B1-F02 fix, drift verification
B2  Visual assets                            — recolor master vector per KG-03, NOT a redesign
B3  Design tokens / themes                   — revalidate + implement the provisional 43-key plan
B4  User-facing English identity             — residue sweep beyond what PRE already closed
B5  Arabic identity + RTL                    — tagline AR, KG-05 interim wordmark disclosure
B6  Translation catalogue migration          — durable-contract migration of the tagline string
B7  Technical module identity                — verify KG-01 already-done state; find any residue
B8  CLI / installer / fresh install          — verify install-time defaults, trace real CLI invocation
B9  Existing-tenant compatibility            — PLAN ONLY; live writes need separate GATE-4 authorization
B10 Reports / print / PDF / email            — non-browser surfaces
B11 Complete residue scan                    — scans A–D
B12 Security/privacy regression check
B13 PHPStan (host-local config, RB-24 discipline)
B14 branding-ci + full test matrix
B15 Negative controls on critical guardrails
B16 Push Phase-B branch, check CI (billing lock may still apply)
```

Executing autonomously through phases with sufficient authoritative evidence (B1 onward). Will stop
at a genuine Owner Decision Gate, safety blocker, or the final Phase-B certification boundary — not
before.

## 8. Migration ledger (initial — grows as each phase executes)

| ID | Surface | Current | Target | Classification | Source of truth | Runtime? | DB? | Compat needed? | Phase | Status |
|---|---|---|---|---|---|---|---|---|---|---|
| B1-01 | `login_tagline_text` (EN) | Clinical confidence, connected care. | Better care begins here. | USER-FACING PRODUCT IDENTITY | Owner instruction 2026-08-26 | Yes | Yes (profile + live tenant) | Source now; tenant sync is B9 | B1 | Pending |
| B1-02 | `login_tagline_text` (AR, `value_ar` doc field) | ثقة إكلينيكية، رعاية مترابطة. | من هنا تبدأ رعاية أفضل. | USER-FACING PRODUCT IDENTITY | Owner instruction 2026-08-26 | Yes (via translation catalogue) | Yes | Source now; tenant sync is B9 | B1/B6 | Pending |
| B1-03 | `main_menu_logo_title` `value_ar` doc field | نظام ثقة للمعلومات الصحية | (to be derived — SkyEagle Arabic equivalent) | TRANSLATION VALUE / DOCUMENTATION | Discovered this session | Documentation-only in this file | No | No | B1 | Pending |
| B2-01 | `brand/master/*.svg` (8 files) + all live-deployed logo/favicon PNG/GIF/ICO slots | Thiqa-era artwork, Thiqa navy/coral | New verified SkyEagle vector artwork (deep navy `rgb(9,58,116)`/tonal blue `rgb(21,84,150)`), Owner-redirected away from recolor-in-place | USER-FACING PRODUCT IDENTITY (asset) | Owner instruction 2026-08-26 (supersedes KG-03 recolor plan — see §9) + `OpenEMRWebSite/docs/Assets/Entity.md` | Yes (feeds all derived logo variants) | No | No | B2 | COMPLETE |
| B3-01 | `brand/tokens/thiqa-tokens.json` | Thiqa 43-key palette | SkyEagle 43-key palette (KG-03 anchors, KG-08 flat light bg, KG-04 semantic preserved) | USER-FACING PRODUCT IDENTITY (tokens); filename itself stays (KG-06) | PRE §12 provisional plan + KG-02/03/04/08, role-mapping confirmed with Owner 2026-08-26 | Yes | No | No | B3 | COMPLETE |
| B7-VERIFY | Module namespace/directory | `SkyEagleBranding` / `oe-module-skyeagle-branding` | (no change expected) | TECHNICAL MODULE IDENTITY | KG-01 (already executed in PRE) | — | — | — | B7 | To verify, not re-execute |
| B9-01 | `sites/default` `globals` (5 rows: `login_tagline_text`, `main_menu_logo_title`, `online_support_link`, `user_manual_link`) | Stale, itemised in §9 B9 plan | Synced to current profile, itemised in §9 B9 plan | DATABASE VALUE | branding-profile.json | Yes | Yes — live write | GATE-4 | B9 | PLAN COMPLETE, not executed |
| B9-02 | `facility.name` (id 3), both tenants | Thiqa Demo Eye Clinic | International Healthcare Center | TENANT DATA | Owner instruction 2026-08-26 | Yes | Yes — live write | GATE-4 | B9 | PLAN COMPLETE, not executed |
| B9-03 | `sites/rdy0082restore` `globals`, same 5 rows | Inspected 2026-08-26 (read-only): identical drift to `default` — see §9 B9 plan | Synced to current profile, itemised in §9 B9 plan | DATABASE VALUE | branding-profile.json | Yes | Yes — live write | GATE-4 | B9 | PLAN COMPLETE, not executed |
| B11-01 | `modules` row id 6, both tenants | `mod_name`/`mod_directory`/`mod_ui_name` = Thiqa/oe-module-thiqa-branding, inactive | Corrected or removed (executor's judgment) | STALE REGISTRATION DATA | B11 finding, 2026-08-26 | No (inactive) | Yes — live write | GATE-4 | B11/B9 | Found, not executed |

| B4-01 | `src/RestControllers/FHIR/FhirMetaDataRestController.php` `openemr_name` fallback | literal `'Thiqa'` | `ProductIdentity::name()` | USER-FACING (FHIR CapabilityStatement) | Found by B4 residue sweep | Yes | No | No | B4 | COMPLETE |
| B4-02 | `OAuth2AuthorizationListener.php` API-disabled exception (BRAND-134) | literal `"Thiqa Error: API is disabled"` | `ProductIdentity::name() . " Error: ..."` | USER-FACING (OAuth2 error response) | Found by B4 residue sweep; mandatory-patch test confirms BRAND-134 is deliberate | Yes | No | No | B4 | COMPLETE |
| B4-03 | 5 error-page templates (BRAND-101): `general_http_error`, `400`/`404` `.html.twig`/`.json.twig` | literal `"Thiqa ... Error"` | `"%s ... Error"|xlp|text` (HTML) / `|xlp|json_encode` (JSON) | USER-FACING (every 400/404/general error page) | Found by B4 residue sweep | Yes | No | No | B4 | COMPLETE — translation contract deferred to B6 (see §9) |
| B4-04 | `SeedDemoCommand.php` console title | literal `'Thiqa demo seed — profile %s%s'` | TBD | OPERATOR-FACING (CLI console output) | Found by B4 residue sweep | Yes | No | No | B8 | Deferred — CLI-specific, in B8's explicit scope |

More rows are added as each phase's own reconnaissance runs.

## 9. Out-of-scope / observation register

**B2 pivot, 2026-08-26 — recolor-in-place abandoned, replaced with new verified artwork.**
Original B2 plan (per KG-03) was to recolor the existing Thiqa `brand/master/*.svg` silhouettes
in place, preserving their geometry. Mid-phase, resolving KG-03's three named colour roles onto
the master SVG's actual two shapes required a clarifying question; the Owner's answer changed the
approach entirely: **no old visual asset may be reused at all — logo and every other visual must
be replaced with new SkyEagle artwork**, sourced first from
`G:\My Drive\OpenEMRWebSite\docs\Assets` (a separate, already-verified asset delivery for the
`skyeagle.uk` website project), falling back to the Owner's Recraft.ai API key only for assets
genuinely missing there. That folder's own `DELIVERY.md`/`assets.md` records that generative AI
was tested and **rejected** for anything carrying the trademark (two documented failed attempts:
text-to-image and image-to-image both corrupted the beak/feathers/E-bars/wordmark) — the only
approved method is Recraft's **vectorize** endpoint against the real approved master raster
(`Intial Logo.png`), i.e. tracing, not generating. That delivery was found to be complete for
every asset OpenEMR needed, so the Recraft API key was never invoked this phase — it stayed
unused, and is still not written anywhere in the repository.

**Symbol/wordmark geometry, confirmed against `Entity.md`:** S+E monogram (flowing S evoking an
eagle, beak-like upper-right termination, feather shapes lower-left, E as three bold parallel
bars), two-tone SkyEagle blue (`rgb(9,58,116)` dominant / `rgb(21,84,150)` tonal, used on feather
planes) — matches Entity.md's own symbol description verbatim. This is a materially different
palette from KG-03's `#0B376E`/`#1E5A96`/`#0B4E91` three-role split; KG-03 is treated as
superseded for the mark itself by this newer, Owner-sourced, already-verified artwork. Whether
KG-03's values still govern the *non-logo* UI token palette (buttons, links, etc.) is a B3
question, not resolved here.

**Deliberately deferred, not silently skipped — catalog-only brand-kit completeness gap.**
`install-assets.php`'s mapping table (BRAND-014..033 + dark-variant rows) defines the actual
live-deployed asset surface; every file it references was replaced this phase. Not wired into any
live UI slot (grep-confirmed) and left on old Thiqa artwork for now: `brand/logos/compact/*`,
`brand/logos/symbol/*` (black/white/cream variants), `brand/logos/monochrome/brand-logo-black.svg`
and its cream-background sibling, plus the non-logo evidence trees `brand/colors/`, `brand/email/`,
`brand/guidelines/`, `brand/previews/`, `brand/qa/`, `brand/rtl/`, `brand/smart/`. These still carry
Thiqa colours/geometry and their `asset-manifest.json`/`SHA256SUMS` rows are untouched. A follow-up
pass should either replace them the same way or formally retire the ones (cream-background
variants especially) that have no equivalent in the new Owner-approved kit.

**`brand-logo-compact.svg` design choice.** Not wired into any live slot. Rather than fabricate a
stacked (symbol-over-wordmark) composition not present in the verified source set — which would
require guessing at spacing/proportions no one has approved — it was set to the symbol-only mark,
a standard "compact = mark without wordmark" brand-kit convention. Documented here as a visible
decision per KG-04's "surface material new visible decisions before executing."

**Vectorizer artifact fixed, not inherited.** Every source SVG from the website delivery carried
`preserveAspectRatio="none"`, which `SvgGeometryInvariantTest`/`LogoValidator` correctly reject
(it would let a renderer stretch the mark non-uniformly). Replaced with `xMidYMid meet` — an
attribute-only change, zero effect on path geometry — across all 8 master SVGs plus the two files
copied from them (`brand/favicon/favicon.svg`, `brand/logos/monochrome/brand-logo-white.svg`).

**B4: three new `xlp`-composed keys need a B6 translation contract.** The 5 error-page templates
(B4-03) now compose `"%s Error"`, `"%s 400 Error"`, `"%s 404 Error"` through the established
`|xlp` mechanism (same RTL-safe pattern as `setup.php`/`sql_patch.php`/the OAuth2 templates),
replacing the old literal `"Thiqa ... Error"` strings. Unlike those other conversions, these three
keys are **not yet** registered in `ProductNameCompositionContractTest::CONVERTED_SITES` and have
**no backing translation contract** in `contrib/util/language_translations/contracts/` — both are
deliberately deferred to B6 ("Translation catalogue migration"), matching the exact precedent
already set by B1-02 (`login_tagline_text` AR: "Source now; tenant sync is B9" — same English-now,
migration-later split, just for a different downstream mechanism). They render correctly in
English today (the `|xlp` filter degrades gracefully with no contract, per its own design — this
was independently confirmed by the full isolated suite passing 1674/1674 after the change), they
just are not yet translatable. Not a silent gap: `testNoTemplateJuxtaposesTheProductNameWithA
TranslatedPhrase` and `testNoRendererPassesARawProductNameIntoTemplateScope` already cover them
generically (they scan the whole template tree, not just `CONVERTED_SITES`), so the only missing
piece is contract-backed translatability, which is B6's job by design.

**B4: extended the `|xlp` escaper allowlist to include `json_encode`.** `ProductNameComposition
ContractTest::testTheCompositionFilterIsNeverEmittedUnescaped` only recognised `|text`/`|attr`
because no prior call site composed a product name into a JSON sink. The two `.json.twig` error
templates are the first to do so; `|json_encode` is exactly as valid an escaper for that sink as
`|text` is for HTML, so the allowlist was extended rather than the templates forced into an
HTML-escaper that would corrupt the JSON. Documented in the test's own docblock.

**B4: BRAND-134 conflict caught by the regression-guard test, not silently shipped.** My first
attempt at the OAuth2 "API is disabled" message matched it to a *different* line in the same file
("OpenEMR Error: ..." for an internal 500) by pattern-matching instead of checking history — wrong,
and `MandatoryCoreStringPatchesIsolatedTest`'s BRAND-134 row caught it immediately (mustContain
still expected the pre-fix literal). The correct fix routes through `ProductIdentity::name()`,
matching the file's own sibling `interface/globals.php` pre-bootstrap pattern; the guard-test row
itself is updated to expect that mechanism instead of either literal. Recorded here because it is
exactly the kind of self-correction the checkpoint's evidence-first discipline exists to surface.

## 10. Owner Decision Gates encountered

**B2 pivot (2026-08-26).** Not a numbered GATE from the execution prompt, but a genuine
mid-phase redirect: a clarifying question about mapping KG-03's three colour roles onto the
master SVG's two shapes led the Owner to abandon recolor-in-place entirely in favour of new
verified artwork. See §9.

**B3 role-mapping question (2026-08-26).** KG-02 says the SkyEagle CTA maps to the
`interactive.primary` token; KG-03 names three colours (primary/accent/link) without saying
which is the CTA. Asked before deriving all 43×2 keys, since a wrong mapping would mean redoing
every downstream generated artefact. Owner confirmed: **accent (#1E5A96) → interactive.primary
(CTA)**; primary (#0B376E) → brand.navy / interactive.secondary / text.primary (main brand/nav
colour); link (#0B4E91) → link.default, unchanged from its existing role.

## 11. Rollback boundaries

```text
Immutable root boundary: 826d194eb  — never rewritten.
```

Per-phase boundaries recorded as each phase completes (§12).

## 12. Phase log

*(filled in as phases complete — format: PHASE / START SHA / END SHA / FILES / DB MUTATION /
GENERATED ARTEFACTS / TESTS / ROLLBACK METHOD / DEPENDENT NEXT PHASES)*

### B1 — Source-of-truth product identity — COMPLETE

```text
PHASE:            B1
START SHA:        826d194eb (via c860e2dae, checkpoint-only)
END SHA:           27685109e
FILES:             branding-profile.json (tagline EN/AR, main_menu_logo_title value_ar residue);
                    BrandingGlobalKey.php (tagline default, kept in sync);
                    3 test files (BrandingConfigFactoryTest, BrandingProfileLoaderTest,
                    BrandingServiceTest) — assertions updated to match
DB MUTATION:       NONE — source only; live default tenant still carries the old tagline/URLs,
                    deliberately not touched (B9/GATE-4)
GENERATED ARTEFACTS: none regenerated by this phase's own content (tagline isn't part of the
                    pre-database ProductIdentity artefact); 6 files had CRLF working-tree
                    corruption normalized back to their already-committed LF content — zero net
                    commit diff, not a real change
TESTS:              composer branding-ci — 1674 tests, 8412 assertions, exit 0
ROLLBACK METHOD:    git revert 27685109e (single commit, no dependents yet)
DEPENDENT NEXT PHASES: B6 (translation catalogue must carry the new tagline string through the
                    durable migration mechanism), B9 (existing-tenant sync, gated)
```

**Deliberately not touched, with reasoning:** `LoginTemplateListenerTest.php`'s six "Thiqa"-stub
assertions (test fixture proving generic rewrite mechanics, not the shipped identity — see commit
message); `docs/branding-production/14-string-replacement-map.md` (historical Thiqa-era planning
document); the live `default` tenant DB; `facility.name` = "Thiqa Demo Eye Clinic" (tenant data).

### B2 — Visual identity replacement (logo, symbol, favicon) — COMPLETE

```text
PHASE:            B2
START SHA:        e6549eee4
END SHA:           30d45f00a
FILES:             8x brand/master/*.svg (recolored/composed from verified SkyEagle vector
                    source, not the old Thiqa geometry — see §9 for the pivot);
                    brand/favicon/{favicon.ico,favicon.svg,favicon-16x16.png,favicon-32x32.png,
                    favicon-48x48.png}; brand/logos/login/*.png (4); brand/logos/portal/*.png (3);
                    brand/logos/legacy/* (9, incl. 2 GIF); brand/logos/monochrome/brand-logo-white.svg;
                    brand/manifests/{SHA256SUMS,asset-manifest.json,asset-manifest.csv}
                    (30 source-class + 21 deployed-class rows re-hashed);
                    tests/.../LogoValidatorTest.php (2 certified-dimension fixtures updated to the
                    new assets' real intrinsic size — not a weakened assertion, see §9)
DB MUTATION:       NONE
GENERATED ARTEFACTS: 21 deployed-slot files regenerated via `php tools/branding/install-assets.php`
                    from the new brand/ sources (public/images/logos/**, public/images/{login-logo,
                    logo-full-con,menu-logo,favicon,favicon-32x32}.*, sites/default/images/*, and
                    the module's 3 dark-variant marks under
                    interface/modules/custom_modules/oe-module-skyeagle-branding/public/logos/dark/)
TESTS:              composer branding-ci — branding-tokens-check PASS, branding-identity-check PASS,
                    verify-brand-manifest.php (123/123 source + 21/21 deployed + 11/11 font-equality),
                    isolated suite 1674 tests / 8412 assertions, exit 0
ROLLBACK METHOD:    git revert this commit (single commit; no dependent commits yet)
DEPENDENT NEXT PHASES: B3 (color tokens — must resolve whether KG-03's 3-role palette still governs
                    non-logo UI tokens now that the mark itself uses a different verified 2-tone
                    palette), B9 (existing-tenant asset sync, gated)
```

**Provenance discipline applied.** Every new pixel/vector byte in this phase traces to one of: (a)
`derived/{symbol,symbol-transparent}.svg` and `derived/lockups/skyeagle-logo-horizontal-{transparent,
dark}.svg` in the OpenEMRWebSite delivery — themselves produced by Recraft's vectorize endpoint
against the real approved master raster, per that project's own tested-and-documented no-AI-on-
trademark rule; (b) deterministic recolor of those files' `fill=` attributes only (`brand-symbol-white.svg`,
`brand-symbol-black.svg`, `brand-logo-black.svg` — geometry/path data untouched, verified by XML
validation + a grep for zero remaining Thiqa hex values); (c) deterministic contain-fit/resize via PHP
GD from the delivery's own square icon source (`derived/icon-512.png`) for legacy raster slots with no
size-matched delivered file (`menu-logo.png`, the 86x43 legacy trio, the two GIFs). No text-to-image or
image-to-image generation was used anywhere in this phase; the Owner's Recraft API key was not invoked.

### B3 — Colour tokens — COMPLETE

```text
PHASE:            B3
START SHA:        ea007a19a
END SHA:           86ef1ae96
FILES:             brand/tokens/thiqa-tokens.json (43-key palette, both themes); 6 generated
                    artefacts under interface/themes/thiqa/{_tokens-light,_tokens-dark,
                    _css-variables}.scss and the module's smart-style_{light,dark}.json.twig
                    (+ their tools/branding/output-preview/ copies); brand/manifests/{SHA256SUMS,
                    asset-manifest.json,asset-manifest.csv}; 3 test files
                    (TokenSetParserTest, TokenValidatorTest, TokenGeneratorIsolatedTest) —
                    fixtures reading the real shipped document updated to the new values
DB MUTATION:       NONE
GENERATED ARTEFACTS: regenerated via `php tools/branding/bin/generate-tokens.php`, deployed by
                    byte-exact copy (SCSS) / header-preserving payload splice (Twig, per
                    DeployedArtefacts' own documented convention), verified via
                    `generate-tokens.php --check` (12/12 up to date, 6 preview + 6 deployed)
TESTS:              composer branding-ci — tokens-check PASS, identity-check PASS,
                    verify-brand-manifest.php 123/123 + 21/21 + 11/11, isolated suite 1674
                    tests / 8412 assertions, exit 0. WCAG contrast independently checked for
                    every text/background and textOn/interactive pair (light 7.10–11.76:1,
                    dark 6.68–17.31:1, all well above the 4.5:1 AA floor)
ROLLBACK METHOD:    git revert this commit (single commit; no dependent commits yet)
DEPENDENT NEXT PHASES: B4 onward per the original phase sequence (§7) — module/manifest/DB
                    phases still gated per §10/GATE-4
```

**Derivation method, not eyeballed.** hover = shade(default, 13%) · active = shade(default, 22%)
· disabled = mix(default, background, 30%) · dark tints = tint(light, 45%) — all per the PRE
checkpoint's §12 documented formulas. One deviation from that plan's stated 25% disabled-mix:
25% measured 1.48:1 against background, below this module's own `TokenValidator
::DISABLED_STATE_SEPARATION_MINIMUM` (1.5, "not a WCAG contrast gate" — a separate
product-distinguishability rule). 30% clears it at 1.61:1 with headroom; recorded here because
it is a real, if small, deviation from the provisional plan's own number.

**KG-04 applied literally.** Only the identity-linked keys changed: `brand.navy`, `brand.coral`,
`brand.coralDeep`, `brand.sky`, `text.primary`, `interactive.primary.*`,
`interactive.secondary.default/hover`, `interactive.focusRing`, `link.*`, plus `background` and
`surfaceSunken` (light only, KG-08). Everything else — `semantic.*` (success/warning/critical/
info), `brand.sage/amber/critical`, `border`, `divider`, `borderStrong`, `text.secondary`,
`text.disabled`, `text.inverse`, `surface`/`surfaceInput`/`surfaceInputOnRaised`/`surfaceRaised`
— is untouched, preserved exactly as validated.

### B4 — User-facing English identity residue sweep — COMPLETE

```text
PHASE:            B4
START SHA:        e2a628979
END SHA:           be54165ce
FILES:             src/RestControllers/FHIR/FhirMetaDataRestController.php (hardcoded 'Thiqa'
                    fallback -> ProductIdentity::name()); src/RestControllers/Subscriber/
                    OAuth2AuthorizationListener.php (BRAND-134 message -> ProductIdentity::name(),
                    with an import added); 5 error-page templates under templates/error/
                    (BRAND-101: literal "Thiqa ... Error" -> "%s ... Error"|xlp composed);
                    2 regression-guard test files updated to match (MandatoryCoreStringPatches
                    IsolatedTest counts/mechanisms, ProductNameCompositionContractTest escaper
                    allowlist)
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              composer branding-ci — tokens-check PASS, identity-check PASS,
                    verify-brand-manifest.php 123/123 + 21/21 + 11/11, isolated suite 1674
                    tests / 8424 assertions, exit 0
ROLLBACK METHOD:    git revert this commit (single commit; no dependent commits yet)
DEPENDENT NEXT PHASES: B6 (translation contracts for the 3 new xlp keys, see §9); B8 (the
                    SeedDemoCommand.php CLI-title finding, B4-04, deferred there by scope)
```

Methodology: `interface/`, `library/`, `src/`, `templates/`, `portal/`, `public/` swept
case-insensitively for `\bTHIQA\b`, cross-checked against `MandatoryCoreStringPatchesIsolated
Test`'s own D-3 checklist (which independently confirmed the same 2 PHP files + 5 templates were
the only remaining live-app hits — everything else already closed in PRE). Findings and the one
self-caught mistake are recorded in §8/§9.

### B5 — Arabic identity + RTL — COMPLETE (verification, 2 doc-comment fixes)

```text
PHASE:            B5
START SHA:        5806296ce
END SHA:           6455e7911
FILES:             branding-profile.json (2 stale "Thiqa" documentation notes corrected to
                    SkyEagle -- comment-only, does not feed the identity generator, hash
                    unchanged, confirmed via generate-product-identity.php --check)
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              tests/Tests/Isolated/Modules/SkyEagleBranding/{Config,Asset,Listener,Service}
                    — 432 tests / 1285 assertions, exit 0
ROLLBACK METHOD:    git revert this commit (single commit; no dependent commits yet)
DEPENDENT NEXT PHASES: B6 (translation catalogue migration for B4's 3 deferred xlp keys, and
                    for the tagline AR delivery path B1-02 already flagged)
```

Findings, both already correct — verified, not newly implemented:

- **Tagline AR.** `login_tagline_text` value_ar = "من هنا تبدأ رعاية أفضل." and `product_name_ar`
  / `saas_branding_product_name_ar` = "سكاي إيجل" were already set correctly in B1. Confirmed
  present and unchanged.
- **KG-05 interim wordmark disclosure.** `BrandAssetResolver::altTextArabic()` (BRAND-053) already
  composes an accurate Arabic accessible name ("شعار %s" / "%s - الصفحة الرئيسية" with the real
  Arabic product name) for every logo slot, rather than either fabricating a claim of a genuine
  Arabic wordmark graphic or silently falling back to the Latin alt text on an Arabic page. This
  is covered by `BrandAssetResolverTest.php` and already part of the passing suite. No RTL
  mirroring rule exists anywhere in `interface/themes/thiqa/*.scss` that would flip the mark's
  geometry, satisfying KG-05's "never mirror the eagle/beak direction" clause by simple absence.
  Read as satisfying KG-05 as already implemented; no new UI work identified.
- **Arabic-script residue sweep.** Grepped for `ثقة` across the module and the durable
  translation-contracts SQL — the only two hits are historical change-log prose (already-fixed
  audit notes), not live rendered strings. Clean.

Two stale documentation `note` fields in `branding-profile.json` were corrected in passing
("...Thiqa kit" / "...Thiqa monochrome navy mark..." -> SkyEagle) — comment-only, confirmed via
`generate-product-identity.php --check` that the generated artefact hash is unaffected.

### B6 — Translation catalogue migration — COMPLETE

```text
PHASE:            B6
START SHA:        bc896da42
END SHA:           7b7a3121b
FILES:             3 new translation contracts (contrib/util/language_translations/contracts/
                    {general-http-error,error-400,error-404}.json); regenerated
                    durableTranslationContracts_utf8.sql (29 -> 32 contracts);
                    ProductNameCompositionContractTest.php (5 error templates registered in
                    CONVERTED_SITES; escaper regex extended to recognise json_encode there too)
DB MUTATION:       NONE — this generates install/upgrade-time SQL; nothing runs it in this phase
GENERATED ARTEFACTS: durableTranslationContracts_utf8.sql, regenerated via
                    TranslationContractSqlRenderer::renderSet() over the full contract set
TESTS:              tests/Tests/Isolated/{BrandingCi,BrandingCoreStrings,Common/Translation,...}
                    — 1724 tests / 8631 assertions, exit 0
ROLLBACK METHOD:    git revert this commit (single commit; no dependent commits yet)
DEPENDENT NEXT PHASES: B7 (module identity verification), B9 (tenant DB sync, gated)
```

Closes the gap B4 deliberately left open: the three new `xlp`-composed keys ("%s Error",
"%s 400 Error", "%s 404 Error") introduced by B4's error-page fix are now backed by real
translation contracts, matching the established pattern (`about-product.json`,
`oauth-authorization.json`, `database-upgrade.json`).

- **"%s Error"** derives from the pre-existing, richly-translated bare-word constant "Error"
  (35 locales, Arabic included) with `placement: prefix` — the same "About"/"Authorization"
  pattern the other conversions already use.
- **"%s 400 Error" / "%s 404 Error"** have no plausible pre-existing source to derive from
  (checked the live catalogue: neither "400 Error" nor "404 Error" exists as a standalone
  constant). `legacy_keys` records their historical predecessor ("Thiqa 400/404 Error") for
  provenance, honestly: the live catalogue was also checked and confirmed those literals were
  **never translated in any locale** (zero rows, not merely zero definitions) — so there is
  nothing to lose and nothing to carry forward. Functionally identical to before for every
  non-English locale (still renders English), but the product name is now genuinely dynamic
  rather than frozen as a literal.
- All three set `on_missing_identity: skip` explicitly, per
  `ProductNameCompositionContractTest::testEveryLegacyContractDeclaresItsMissingIdentityPolicy`
  — required for any legacy-only, no-explicit-definitions contract; caught immediately by the
  test when first omitted (defaults to `fail`, which is the wrong choice here — there is no
  locale data to lose, so failing an upgrade over it would be pure friction).

**Not in scope for B6, on reflection.** `login_tagline_text`'s EN/AR values are a tenant-scoped
branding *global* (`BrandingConfigFactory`/`BrandingGlobalKey`), materialised directly per tenant
— not looked up through `lang_constants`/`xlt()` at all. The checkpoint's earlier "B1/B6" phase
tag for that row (§8, B1-01/B1-02) was imprecise: propagating that value to a live tenant's
`globals` table is a B9/GATE-4 database-write concern, not a translation-contract one. Corrected
here rather than silently left inconsistent.

### B7 — Technical module identity — COMPLETE (KG-01 verified + 13 residue fixes)

```text
PHASE:            B7
START SHA:        4995b1a53
END SHA:           d3cc96ecb
FILES:             13 files under interface/modules/custom_modules/oe-module-skyeagle-branding/
                    (openemr.bootstrap.php, Bootstrap.php, ContrastCalculator.php,
                    GlobalsRegistrationListener.php, SuppressionGlobalKey.php,
                    ApplyProfileCommand.php, JobPayload.php, ProvisionReportAclCommand.php,
                    TokenCssWriter.php, ThemeResolver.php, TokenKey.php, both smart-style twig
                    headers) + TokenCssWriterTest.php (fixture updated to match)
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              composer branding-ci equivalent + Common/Translation — 1724 tests / 8631
                    assertions, exit 0
ROLLBACK METHOD:    git revert this commit (single commit; no dependent commits yet)
DEPENDENT NEXT PHASES: B8 (CLI/installer sweep — SeedDemoCommand.php's console title, B4-04,
                    still deferred there)
```

**KG-01 verified, not re-executed.** Module directory (`oe-module-skyeagle-branding`), PSR-4
namespace (`OpenEMR\Modules\SkyEagleBranding\`), `composer.json` package name/description/
keywords are all already correctly SkyEagle-identified — confirmed by direct inspection, no
change needed to any of them.

**Most significant finding: `GlobalsRegistrationListener::SECTION = 'Thiqa Branding'`.** This is
the literal Admin > Globals section heading grouping every branding setting — a prominent,
genuinely admin-visible string, not a comment. Fixed to `'SkyEagle Branding'`. Its own docblock
explains why it stays a plain literal rather than an `xlp`-composed pattern like B4/B6's fixes:
`edit_globals.php` passes the section key itself through `xlt()`, so "the key must be the
untranslated literal" — this is existing, deliberate design, not a gap to close here.

**Two Symfony Console command descriptions/titles** (`ApplyProfileCommand`,
`ProvisionReportAclCommand` — description text shown by `bin/console list`/`--help`, plus the
latter's `$io->title()` output) carried the literal brand name; corrected. Operator-facing, same
class of finding as B4's OAuth2/FHIR fixes, just in console rather than HTTP surfaces.

**One generated-artefact literal:** `TokenCssWriter::HEADER` is embedded as a comment in every
CSS file this materialiser writes (`/* Generated by the ... materialiser. */`) — developer-visible
in browser devtools. Fixed, with its test fixture updated to match.

**Remaining 9 fixes are documentation comments** (docblocks, a JSON example payload, a deployed
Twig template's own header prose) describing the *current* system, not historical audit notes —
corrected for accuracy though none render to an end user. Distinguished throughout this sweep from
the many *legitimate* `thiqa` references left untouched: `brand/tokens/thiqa-tokens.json`,
`interface/themes/thiqa/`, `--thiqa-*` custom properties, and `ManagedBackupArtifact::LEGACY_PREFIX
= 'thiqa-'` are all permanent technical plumbing (KG-06) or deliberate legacy-compatibility
constants, not residue.

### B8 — CLI / installer / fresh install — COMPLETE

```text
PHASE:            B8
START SHA:        ebe0377b6
END SHA:           a9fac8b0e
FILES:             SeedDemoCommand.php (B4-04: console title 'Thiqa demo seed' -> 'SkyEagle
                    demo seed')
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              tests/Tests/Isolated/Modules/SkyEagleBranding/Console — 125 tests / 1593
                    assertions, exit 0
ROLLBACK METHOD:    git revert this commit (single commit; no dependent commits yet)
DEPENDENT NEXT PHASES: B9 (existing-tenant compatibility, PLAN ONLY / GATE-4)
```

Applied B4-04, the one finding explicitly deferred to this phase by scope. Verified the rest of
the install-time surface directly: `library/globals.inc.php`'s `openemr_name` default already
resolves through `ProductIdentity::name()` (not a literal); `contrib/util/installScripts/
InstallerAuto.php`, `library/classes/Installer.class.php`, `bin/console` and `src/Console/`
(the Symfony Application bootstrap) are all clean of the brand literal. This is not a fresh
finding — `MandatoryCoreStringPatchesIsolatedTest`'s own D-3 checklist already exhaustively
covers `setup.php`/`sql_patch.php`/`sql_upgrade.php`/`ippf_upgrade.php`/`admin.php`/the Zend
installer view and is already passing, so B8's own scan targeted the surfaces that checklist
does not — the automated installer script and the console bootstrap — rather than re-deriving
what B4/that test already established.

### B9 — Existing-tenant compatibility — PLAN ONLY, GATE-4, no live write executed

```text
PHASE:            B9
START SHA:        64d822f18
END SHA:           2475c2023
FILES:             docs/SKYEAGLE-BRANDING-CONTINUATION-CHECKPOINT.md only (this plan)
DB MUTATION:       NONE — read-only SELECTs against both live databases (openemr,
                    openemr_rdy0082_restore); the plan below is not executed
GENERATED ARTEFACTS: none
TESTS:              n/a (no code changed)
ROLLBACK METHOD:    n/a
DEPENDENT NEXT PHASES: B10-B16 do not depend on B9 executing; B9's actual write remains a
                    separate, explicitly gated action per the Owner's own Phase-B authorization
```

**Per the phase table, B9 is PLAN ONLY — live writes need separate GATE-4 authorization.** This
entry produces that plan via read-only inspection of both live tenant databases. No `UPDATE`,
`INSERT` or `DELETE` was run against either.

**Both tenants inspected — identical drift, both still on pre-Phase-B values:**

| `globals.gl_name` | Current (both `default` and `rdy0082restore`) | Target (branding-profile.json) |
|---|---|---|
| `login_tagline_text` | `Clinical confidence, connected care.` | `Better care begins here.` |
| `main_menu_logo_title` | `SkyEagle Health Information System` | *(unchanged — already correct)* |
| `online_support_link` | `https://skyeagle.uk/support` | `https://skyeagle.uk/en/contact` (ADR-BRAND-006) |
| `user_manual_link` | `https://skyeagle.uk/docs` | `https://skyeagle.uk/en/resources` (ADR-BRAND-006) |
| `openemr_name` | `SkyEagle` | *(unchanged — already correct)* |
| `saas_branding_product_name_ar` | `سكاي إيجل` | *(unchanged — already correct)* |
| `facility.name` (id 3) | `Thiqa Demo Eye Clinic` | `International Healthcare Center` |

Three of seven checked rows are already correct on both tenants (`main_menu_logo_title`,
`openemr_name`, `saas_branding_product_name_ar`) — a prior partial sync evidently already ran.
The four still-stale rows are exactly what B9-01/B9-03 track. `rdy0082restore`'s
`openemr_rdy0082_restore` database exists locally and was reachable read-only; both tenants show
byte-identical drift, so one plan covers both.

**Mechanism, when GATE-4 is authorized.** The correct execution path is `php bin/console
skyeagle-branding:apply-profile --site=default` (and again with `--site=rdy0082restore`) — the
console command this module already ships (B7 verified it) — **not** a hand-written `UPDATE
globals` statement. Hand-writing the SQL would bypass whatever validation/materialisation
`ApplyProfileCommand` performs (token materialisation, revision bookkeeping per
`saas_branding_revision`/`saas_branding_materialised_at`, which currently show empty on both
tenants — consistent with the command never having been run against either).

**Open question, not resolved here, flagged for whoever executes B9.** `BrandingConfigFactory`
reads `login_tagline_text` as a single un-suffixed value (`$this->text(BrandingGlobalKey::
LoginTaglineText)`) — there is no `login_tagline_text_ar` sibling global (unlike
`saas_branding_tenant_display_name` / `_ar`, which do exist as a pair). How the Arabic tagline
(`value_ar` in the profile) actually reaches an Arabic session — a translation-catalogue lookup
keyed on the English value, a materialisation-time swap, or genuinely not yet wired — was not
traced in this phase. Worth resolving before or during B9's actual execution, since applying the
profile without understanding this could silently leave the Arabic tagline on the old string
even after the English one updates correctly.

`facility.name` sync is a plain tenant-data `UPDATE`, unrelated to `apply-profile`, and is not
guarded by any command this module ships — whoever executes B9 should decide whether that goes
through a similar Console command or a direct, reviewed `UPDATE facility SET name = ... WHERE
id = 3` on each database.

### B10 — Reports / print / PDF / email — COMPLETE (verification, no findings)

```text
PHASE:            B10
START SHA:        9b614e72e
END SHA:           481d1c266
FILES:             none
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              n/a (no code changed)
ROLLBACK METHOD:    n/a
DEPENDENT NEXT PHASES: B11 (complete residue scan)
```

Targeted sweep of `interface/reports/`, `interface/forms/`, `library/classes/Pdf.php`,
`library/statement.inc.php`, and email-template paths found no residue. This domain sits entirely
within the directories B4's comprehensive sweep already covered
(`interface/`, `library/`, `src/`, `templates/`); nothing new surfaced. The print-specific brand
assets (`brand/logos/print/*`) remain the deliberately-deferred catalog-only files noted in B2 —
confirmed here that `install-assets.php`'s mapping table never references that directory, so
nothing live actually reads it; the asset that IS live for print/statement use
(`brand/logos/legacy/practice_logo.gif`) was already replaced with real SkyEagle content in B2.

### B11 — Complete residue scan — COMPLETE (2 findings, both GATE-4/deferred)

```text
PHASE:            B11
START SHA:        b5819ea1e
END SHA:           cfc057b35
FILES:             none — both findings below require a DB write, not a code change
DB MUTATION:       NONE — read-only inspection only
GENERATED ARTEFACTS: none
TESTS:              n/a (no code changed)
ROLLBACK METHOD:    n/a
DEPENDENT NEXT PHASES: B12 (security/privacy regression check)
```

Final multi-angle sweep beyond B4/B7/B10's text-literal scans: old Thiqa hex colors anywhere
live, and direct database inspection (`lang_constants`, `modules`) for identity residue no
filesystem grep could find.

**Finding 1 — stale tenant-materialised CSS, harmless and self-correcting.**
`interface/modules/custom_modules/oe-module-skyeagle-branding/public/branding/default/
tokens-{light,dark}.css` still contain the pre-B3 Thiqa palette (`--brand-navy: #0B1B4D`,
`--brand-coral: #FF6F5E`, etc.) and the pre-B7 "Generated by the Thiqa branding materialiser"
header. **Not hand-edited** — these are generated output (`TokenCssWriter`), and
`VerifyCommand.php`'s own docblock confirms "no page links" this static file (D-8); the live
stylesheet path is `public/branding-tokens.php`, rendered per-request from the current source.
Consistent with B9's finding that `saas_branding_revision`/`saas_branding_materialised_at` are
empty on both tenants — materialisation has never run since this module was installed, on either
the old palette or the new one. Will self-correct the moment B9's `apply-profile` run (GATE-4)
materialises against the current (SkyEagle) source; no separate action needed beyond B9 itself.

**Finding 2 — orphaned `modules` table row, both tenants, requires GATE-4 to clean up.**
```sql
mod_id=6, mod_name='Thiqa Branding', mod_directory='oe-module-thiqa-branding',
mod_active=0, mod_ui_active=0, mod_ui_name='Thiqa Branding'
```
Present, identical, on both `openemr` and `openemr_rdy0082_restore`. `mod_directory` points at
`oe-module-thiqa-branding` — a directory that has not existed since KG-01's internal rename in
PRE (the live module is `oe-module-skyeagle-branding`, loaded via `openemr.bootstrap.php`
filesystem auto-discovery, independent of this table — confirmed by grep: nothing in the
module's own source reads `mod_directory`/the `modules` table). `mod_active=0` and
`mod_ui_active=0` mean this row is inert for module *functionality* — the branding module works
correctly regardless of it, as every prior phase's passing tests already prove indirectly. Its
only live effect is cosmetic/administrative: an inactive "Thiqa Branding" entry pointing at a
missing directory would appear in Admin > Modules > Manage Modules' registration list. A genuine,
if low-severity, data-hygiene finding — fixing it (either an `UPDATE` to the current name/
directory, or a `DELETE` of the dead row, whichever the eventual executor judges cleaner) is a
live database write on tenant-shared infrastructure, so it is recorded here for GATE-4 rather
than executed. Not previously listed in the B9 migration ledger (§8) because it is a table this
session had not queried before B11 — added to §8 now for completeness.

| ID | Surface | Current | Target | Classification | Source of truth | Runtime? | DB? | Compat needed? | Phase | Status |
|---|---|---|---|---|---|---|---|---|---|---|
| B11-01 | `modules` row id 6, both tenants | `mod_name`/`mod_directory`/`mod_ui_name` = Thiqa/oe-module-thiqa-branding | Corrected or removed (executor's judgment) | STALE REGISTRATION DATA | This session's B11 finding | No (inactive) | Yes — live write | GATE-4 | B11/B9 | Found, not executed |

### B12 — Security/privacy regression check — COMPLETE (no findings)

```text
PHASE:            B12
START SHA:        b925d5b49
END SHA:           de020fec5
FILES:             none
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              n/a (no code changed)
ROLLBACK METHOD:    n/a
DEPENDENT NEXT PHASES: B13 (PHPStan)
```

The concrete risk from this phase's own changes was SVG asset injection (B2's replacement assets
originated from an external vectorize API): grepped all new/replaced SVGs for `<script>`, event
handlers (`onload`/`onerror`/`onclick`) and `javascript:` URIs — none found, and this is not just
a grep-level check: every one of those files already passed `LogoValidator`/`SvgInspector`'s own
XSS-scanning validation as part of B2's `branding-ci` run (`testCertifiedAssetIsAccepted` exercises
these exact files). `ProductIdentity::name()` — used raw (no HTML escaping) in B4's OAuth2 fix — is
safe by construction: the generator that produces its source artefact refuses to emit any value
containing `< > " ' & \`` or a backtick, enforced before the artefact is ever written, not at the
call site. No PHI or patient-data surface was touched by any Phase-B change; B9/B11's live-DB
reads were limited to `globals`, `facility.name`, `lang_constants` and `modules` — tenant
metadata, not clinical data.

## 13. Live database mutation state

```text
LIVE DATABASE MUTATED THIS PHASE: NO
```

Only read-only `SELECT` queries have been run against the live `openemr` database this session
(§6), extended in B9 to also read-only query `openemr_rdy0082_restore` (§12 B9 entry), and in
B11 to read the `lang_constants` and `modules` tables on both databases (§12 B11 entry). No write
has been issued against either database at any point in Phase B.

---

*Checkpoint revision 12, updated after B12. Next update: after B13.*
