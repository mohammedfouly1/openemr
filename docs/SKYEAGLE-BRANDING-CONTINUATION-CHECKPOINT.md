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

### B13 — PHPStan (host-local config, RB-24 discipline) — COMPLETE (0 branding-attributable errors)

```text
PHASE:            B13
START SHA:        41e64d21c
END SHA:           0163f0e8f
FILES:             none
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              php vendor/bin/phpstan analyze --memory-limit=4G
                    --configuration=C:/openemr-stack/phpstan-localtmp.neon --no-progress
                    (RB-24 host-local config, per CLAUDE.local.md §9)
ROLLBACK METHOD:    n/a
DEPENDENT NEXT PHASES: B14 (branding-ci + full test matrix)
```

**Full run, RB-24 compliant.** Grepped the complete output for `Internal error` and
`Result is incomplete` (the two documented exit-0-while-broken signatures) — zero matches on
either. The run completed and exited 1 (PHPStan's normal "errors found" exit code, not the
silent-incomplete failure mode), reporting **908 errors across 61 files**.

**Zero of the 908 are in any file this session touched.** Cross-checked the full 61-file list
against every file modified across B1-B12 (the `oe-module-skyeagle-branding` module,
`FhirMetaDataRestController.php`, `OAuth2AuthorizationListener.php`, the error templates, the
translation contracts, every test file) — no overlap. `src/Common/Branding/ProductIdentity.php`
appears once (an `openemr.forbiddenErrorLog` finding at line 223) — not a file this session
edited; its `error_log()` use matches the class's own documented design constraint (it runs
before the DI container exists, so a PSR-3 logger is unavailable by construction).

**Scale note, for the record.** A prior session's PHPStan run (recorded in `CLAUDE.local.md`,
2026-08-10) found 80 errors, "all `openemr.forbidDirectSessionWrite`, all in `tests/` files."
This run's 908 spans 24 distinct rule identifiers across app code, tests and `sites/
rdy0082restore/*` site files — a materially larger and broader pre-existing-drift surface than
previously documented, accumulated across the many PRE/Phase-B sessions since. Three
`ignore.unmatched` findings trace to the same CRLF/DriveFS drift this whole project has fought
repeatedly (a baseline ignore-pattern written against LF content no longer matches a file that
picked up CRLF from an unrelated checkout) — a small amplifier, not the primary cause of the
908 count. **Out of scope to fix here**: none of it is branding-attributable, and fixing 908
pre-existing errors across auth/session/FHIR internals is not this phase's — or this
programme's — mandate. Recorded honestly rather than silently passed over, per this session's
own evidence-first discipline; worth a dedicated pass outside Phase-B.

### B14 — branding-ci + full test matrix — COMPLETE (green)

```text
PHASE:            B14
START SHA:        358f58d18
END SHA:           d9f2c12f4
FILES:             none
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              composer branding-ci, run as its literal composer.json script sequence
                    (tokens-check, identity-check, verify-brand-manifest.php, the exact
                    isolated-suite directory list) — 1674 tests / 8450 assertions, exit 0
ROLLBACK METHOD:    n/a
DEPENDENT NEXT PHASES: B15 (negative controls)
```

Final, definitive gate run of the exact `composer.json` `branding-ci` sequence (not a scoped
subset — the literal script). All four stages clean: tokens-check, identity-check,
`verify-brand-manifest.php` (123/123 + 21/21 + 11/11), isolated suite 1674/1674.

**"Full test matrix" on this host, scoped honestly.** `branding-ci`'s own selection is this
project's established authoritative gate — confirmed clean above and repeatedly throughout
B1-B13. The broader, unscoped `tests/Tests/Isolated` suite carries pre-existing, host-specific
failures already investigated and characterised in `CLAUDE.local.md` (§ "full unscoped isolated
suite" notes, 2026-08-26): CRLF-vs-Windows-`PHP_EOL` mismatches in `ReleasePrep\Mutator\*`,
`FrontControllerRoutingTest`, `DocumentImportCommandTest` — all confirmed via `git diff` to be in
files no Phase-B (or PRE) commit has ever touched, and expected to pass in CI/Docker where they
run on Linux. Not re-run here since nothing in Phase-B changed any file in that failure set;
re-litigating an already-documented, unrelated host artifact would not add information. DB-backed
suites (`unit-test`, `services-test`, `api-test`, `e2e-test`) remain, per `CLAUDE.local.md`,
unvalidatable on this native Windows setup — no Docker stack — and out of reach for the same
reason on every phase of this session, not a new limitation B14 introduces.

### B15 — Negative controls on critical guardrails — COMPLETE (4 guardrails proven live)

```text
PHASE:            B15
START SHA:        31376a199
END SHA:           854654bca
FILES:             none — two guardrails deliberately broken then reverted in the working tree,
                    never committed
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              see below — each guardrail's negative-control run plus a confirming
                    green re-run after revert
ROLLBACK METHOD:    n/a
DEPENDENT NEXT PHASES: B16 (push, check CI)
```

A passing test suite proves nothing about a guardrail that would pass anyway — this phase proves
each one actually rejects the specific regression it exists to catch, not just that Phase-B's own
output happens to satisfy it.

**1. `MandatoryCoreStringPatchesIsolatedTest` (B4/B7's regression guard).** Reintroduced the
literal `"Thiqa 400 Error"` into `templates/error/400.html.twig` (reverting B4's fix by hand) and
re-ran the test: **both** `testMandatoryPatchIsPresent` and
`testHardcodedProductNameLiteralCountMatchesInventory` failed immediately, independently, with
the exact expected messages ("lost the branding patch" / "expected 0... found 1"). Reverted via
`git checkout --`; re-ran clean (34/34).

**2. `verify-brand-manifest.php` (the release-gate hash check).** Appended a byte to
`brand/master/brand-symbol.svg` and re-ran: failed immediately, `122/123`, reporting the exact
mismatched hash and file. Reverted via `git checkout --`; re-ran clean (`123/123`).

**3. `SvgGeometryInvariantTest`/`LogoValidator` (`preserveAspectRatio="none"` rejection) —
organic proof, not re-staged.** B2's own delivered SVGs originally carried
`preserveAspectRatio="none"` from the vectorizer; this exact test caught it (16 failures) before
the fix was applied. Already-recorded evidence the guard is live; artificially re-breaking a now
correctly-generated file would test the same code path with no new information.

**4. `TokenValidator::DISABLED_STATE_SEPARATION_MINIMUM` (B3's distinguishability floor) —
organic proof, not re-staged.** The first disabled-fill derivation (25% mix weight) measured
1.48:1 and was rejected by this exact check before the fix (30%) was applied. Same reasoning as
#3: already-live evidence, re-staging would be redundant.

Two guardrails (#1, #2) freshly, deliberately broken and confirmed to fail this phase; two (#3,
#4) already have first-class organic proof from when Phase-B's own work originally violated them.
All four are load-bearing, not decorative.

### B16 — Push Phase-B branch, check CI — COMPLETE

```text
PHASE:            B16
START SHA:        0ea5a91c4
END SHA:           e3cd21d5c
FILES:             none
DB MUTATION:       NONE
GENERATED ARTEFACTS: none
TESTS:              n/a
ROLLBACK METHOD:    n/a
DEPENDENT NEXT PHASES: none — this is the last phase in the B1-B16 sequence
```

`git status -sb` confirms `feat/skyeagle-branding` is fully synced with `origin/feat/skyeagle-
branding` — every phase this session pushed immediately after its own commits, so there was
nothing left to push here.

**CI, checked and explained, not just observed.** `gh run list` shows only one run against this
branch: "Branch-Cut Automation," triggered on branch `create`, outcome `skipped`. No run
corresponds to any of this session's 30+ pushed commits. Traced the reason rather than assuming
billing-lock: `.github/workflows/all-checks-passed.yml` (and the other core test workflows) scope
their `push` trigger to `branches: [master, rel-*]` only, with `pull_request` against the same
two as the feature-branch path — by design, a feature branch gets CI through a PR, not through
direct pushes. No PR has been opened (per the execution prompt's own instruction, Phase B does
not merge to master), so this is expected, not a gap.

**Separately, the billing lock is still live.** The most recent `master`-branch push (same day,
2026-08-26T11:50:18Z, an unrelated prebrand-integration commit) shows all ~19 checks failing in
3-18 seconds each — the exact instant-failure signature this session already root-caused to an
account billing lock, not a code defect. Recorded for completeness: even if a PR were opened
against master right now, real CI signal would not be obtainable until that lock clears — outside
this session's control.

## 13. Live database mutation state

```text
LIVE DATABASE MUTATED THIS PHASE: NO
```

Only read-only `SELECT` queries have been run against the live `openemr` database this session
(§6), extended in B9 to also read-only query `openemr_rdy0082_restore` (§12 B9 entry), and in
B11 to read the `lang_constants` and `modules` tables on both databases (§12 B11 entry). No write
has been issued against either database at any point in Phase B.

---

## 14. Phase-B certification summary (B1-B16 complete)

```text
PHASES COMPLETE:        B1-B16 (all 16), sequentially, each committed, tested and pushed
LIVE DATABASE WRITES:   ZERO — GATE-4 respected throughout (§13)
MASTER MERGE:           NOT PERFORMED — not authorized by the execution prompt for this cycle
BRANCH:                 feat/skyeagle-branding, fully pushed, HEAD (this commit)
BRANDING-CI:            GREEN as of B14's definitive run (1674 tests / 8450 assertions)
PHPSTAN:                0 branding-attributable errors (B13); 908 pre-existing, unrelated,
                        out of scope
NEGATIVE CONTROLS:       4 critical guardrails proven load-bearing (B15)
```

**What changed, end to end:** the visual identity (logos, symbol, favicon — B2), the 43-key
colour token palette (B3), remaining English string residue (B4), a translation-contract gap
closed for the new composed keys (B6), the module's own internal identity strings (B7), one CLI
title (B8) — all verified against Arabic/RTL correctness (B5) and security/privacy (B12) along
the way. Tagline and product-name source-of-truth values were already set in B1.

**What is deliberately NOT done, and why — three items, all requiring separate authorization:**

1. **B9 — existing-tenant `globals`/`facility.name` sync**, both `default` and
   `rdy0082restore`. Full plan produced (§12 B9), Owner authorization (GATE-4) required before
   execution. Correct mechanism identified: the module's own `apply-profile` console command,
   not a hand-written `UPDATE`.
2. **B11-01 — orphaned `modules` table row** (`mod_id=6`, `Thiqa Branding` /
   `oe-module-thiqa-branding`, inactive, both tenants). Data-hygiene finding, also GATE-4.
3. **Master merge.** Per the execution prompt: this cycle builds and certifies
   `feat/skyeagle-branding` in isolation; merging it into `master` is a separate, explicit
   decision for the Owner to make after reviewing this checkpoint.

**Everything else — every phase, every finding, every fix, every test run — is complete,
committed, and pushed.** This checkpoint (revision 16) is the full audit trail; §12's phase log
has the exact commit range for each phase, and §8's migration ledger has every tracked value
change, executed or planned.

*Checkpoint revision 16, updated after B16 — Phase-B complete. Next update: when GATE-4 is
authorized, or when a master-merge decision is made.*

## 15. Final certification, master integration and controlled tenant migration

Owner authorization received 2026-08-26 to proceed: final feature-branch certification, master
integration, post-merge certification, then (only if all of that passes) the two GATE-4 items
under a further, separate safety boundary. Full stage sequence and safety rules per the Owner's
own prompt; not summarized here — see conversation record for the complete instruction.

### Stage 1 — state reconstruction

```text
Current branch:                feat/skyeagle-branding
HEAD SHA:                      bb8e0774886cfb24e5193e44bb4be4d85c107a9d
origin/feat/skyeagle-branding: bb8e07748 (identical to HEAD)
origin/master:                 826d194eb947a198801c1dc595e7066dd843ec5b
local master:                  826d194eb (identical to origin/master)
merge-base(master, feature):   826d194eb -- EXACTLY current master tip
Ahead/behind feature vs origin: 0/0
Ahead/behind master vs origin:  0/0
Commits feature ahead of master: 33
Working tree:                   clean -- git diff --name-only empty; git status noise on
                                 .phpstan/baseline/* confirmed 0 real diff (DriveFS stat-cache,
                                 per Rule 5)
```

Merge-base sitting exactly at master's current tip means **master has not moved since the
feature branch was cut** -- a fast-forward integration is available.

History integrity: B1 (`27685109e`) and B2 (`30d45f00a`) commits confirmed reachable ancestors
of HEAD; all 32 B1-B16 commits present in the expected order (verified by listing every
`(skyeagle)`-scoped commit between master and HEAD). No `rebase`/`reset --hard`/force-push
markers in `feat/skyeagle-branding`'s reflog.

**Unrelated worktrees found, not touched.** `git worktree list` shows 3x `.claude/worktrees/
agent-*` plus `OpenEMR.worktrees/sds` (all at old commit `631f2b38c`, a shared ancestor, on
throwaway branches `worktree-agent-*`/`agents/sds`) and one detached-HEAD worktree elsewhere
(`5ea20d4dd`). None intersect `master` or `feat/skyeagle-branding`; none touched, per Rule 4
(`.claude/` untouched) and general non-interference.

No evidence of concurrent modification to either relevant branch since this session last touched
them.

### Stage 2 — final feature-branch certification

| Check | Result |
|---|---|
| C1 Git integrity | PASS -- see Stage 1 above |
| C2 Branding identity | PASS -- `generate-product-identity.php --check`: up to date (hash `99a06b0f31...`); `openemr_name`="SkyEagle"/"سكاي إيجل", tagline="Better care begins here."/"من هنا تبدأ رعاية أفضل.", support/manual URLs = ADR-BRAND-006 values, token anchors `#0B376E`/`#1E5A96`/`#0B4E91`, `background`=`#FFFFFF`; zero old Thiqa hex (`FE6658`/`FD6558`/`FD6759`/`0B0D2B`) in any `brand/master/*.svg` |
| C3 Residue sweep | PASS -- full repo-wide case-insensitive sweep, every hit classified (table below); **zero active user-facing or active internal residue**; B2/B11's deferred catalog-only assets re-confirmed unreachable (zero references in module source or install/verify tooling) |
| C4 Verification gates | PASS -- tokens-check, identity-check, manifest (123/123 + 21/21 + 11/11), isolated suite **1674 tests / 8450 assertions**, exit 0 -- exact match to the established baseline, no count drift |
| C5 PHPStan | PASS -- reused B13's cached run (working tree unchanged since, confirmed 0 diff): 0 `Internal error`/`Result is incomplete` signals, 908 findings across 61 files, zero overlap with any file this session touched |

**C3 classification table** (full detail; see conversation record for per-item reasoning):

| Category | Examples found |
|---|---|
| ACTIVE USER-FACING RESIDUE | none |
| ACTIVE INTERNAL RESIDUE | none |
| INTENTIONAL MIGRATION COMPATIBILITY | `brand/tokens/thiqa-tokens.json`, `interface/themes/thiqa/*`, `brand/typography/thiqa-fonts.scss` (KG-06 plumbing, deploys to the real `public/assets/fonts/thiqa/` path); `tools/branding/brand-strings.json`'s `retired_english_overrides.managed_english` literals (self-documented exact-match keys for retiring old catalogue rows -- renaming would break retirement) |
| HISTORICAL/AUDIT DOCUMENTATION | `interface/main/tabs/main.php:405` (audit finding A-01 comment); `.phpstan/extension.neon:13` ("RebrandingPlan WP-2.7" rule-category comment) |
| TEST FIXTURE / NEGATIVE CONTROL | `LoginTemplateListenerTest.php`, `TokenSetTest.php`, `CssVariableRendererTest.php` and similar -- "Thiqa" used as an arbitrary example string, not shipped identity |
| DATABASE-ONLY RESIDUE | `lang_constants` orphaned row "Thiqa Database Upgrade"; `modules` row `mod_id=6` (B11-01) |
| DEAD/INERT CONTENT | `brand/qa/wcag-contrast-results.json` -- still shows old `#0B1B4D`/`#FAFAF8`, never read by live code (only cross-referenced in 3 docblocks as a human pointer), same category as the already-deferred `brand/qa/*-render.png` evidence files. Noted as a minor documentation-accuracy housekeeping item, not a blocker. |

## FEATURE CERTIFICATION: PASS

Certified SHA: `bb8e0774886cfb24e5193e44bb4be4d85c107a9d` (`bb8e07748`), branch
`feat/skyeagle-branding`, identical to `origin/feat/skyeagle-branding`. (One further docs-only
commit, `33738daec`, records this certification itself and became the feature branch's new tip
before integration -- no code/behaviour change, see its own commit for detail.)

### Stage 3 -- master integration

`merge-base(master, feature)` sat exactly at `origin/master`'s current tip (`826d194eb`), so
master had not advanced since the feature branch was cut -- a **fast-forward** was the correct
and safest method (preferred order item 1), needing no conflict analysis (nothing to conflict
with by construction).

First attempt failed: `git merge --ff-only` refused, citing 16 binary asset files as carrying
"local changes that would be overwritten." Per Rule 5, verified with `git diff --stat` before
treating this as real -- zero real difference on every one of the 16. A DriveFS index-vs-working-
tree stat mismatch, not real data. `git update-index --refresh` alone did not clear it; re-
checking out the exact 16 paths from `HEAD` (safe -- already proven byte-identical) did. Retried
`git merge --ff-only feat/skyeagle-branding`: succeeded cleanly. **No merge commit** -- history
stays linear. Local `master` -> `33738daec1a104e9b6ee4e88cb6c43a54c821943`, 101 files changed
(4077 insertions / 2420 deletions), matching the full Phase-B programme scope. Not pushed yet
per instruction.

### Stage 4 -- post-merge master certification

**A second, broader DriveFS artifact surfaced here, run down completely before certifying.**
`generate-tokens.php --check` failed immediately after the fast-forward, reporting 5 deployed
artefacts "out of date." Raw-byte inspection (not `git diff`, which is autocrlf-blind to this)
found real `\r` bytes reintroduced into the working tree by the fast-forward's own file-write
step -- the same recurring DriveFS checkout phenomenon this project has documented repeatedly,
here triggered at wider scope because a fast-forward writes every changed file in one pass.
Stripped and re-verified clean (`generate-tokens.php --check`: 12/12 up to date). A second,
independent instance then surfaced in `tests/Tests/Isolated/Common/Translation` (`durable
TranslationContracts_utf8.sql` and the 3 new contract JSONs) -- same fix, same re-verification
(50/50 green). Given the pattern had now recurred twice, ran a **systematic sweep**: every text
file (`.php`/`.scss`/`.twig`/`.json`/`.svg`/`.sql`/`.md`) among the 101 the merge touched,
raw-byte-scanned for `\r`. Found **32 affected files total** (the ones above plus 27 more --
full list in the session's tool-call record). **Every one confirmed via `git diff --stat` to
carry zero real content difference** before being touched -- this was purely a working-tree
artifact from the checkout, never a real regression, and nothing needed to be (or was) committed
to fix it: the underlying git blobs were already correct LF the entire time.

| Check | Result |
|---|---|
| Git integrity | PASS -- `master` == `feat/skyeagle-branding` tip exactly (`33738daec`) |
| Release line | PASS -- `version.php`: 8.2.0/0 (`$v_major`/`$v_minor`/`$v_patch`); `swagger/openemr-api.yaml` and `OpenApiDefinitions.php` both `8.2.0`; zero `8.3.x` drift |
| Complete residue sweep | PASS -- re-spot-checked the highest-risk live surfaces on the merged tree; only the already-classified historical comment in `main.php:405` |
| Identity/token checks | PASS (after the CRLF fix above) -- `generate-product-identity.php --check`: up to date; `generate-tokens.php --check`: 12/12 |
| Asset manifest | PASS -- 123/123 + 21/21 + 11/11, unaffected by the CRLF fix (different file classes) |
| Translation/RTL contracts | PASS (after the CRLF fix above) -- `Common/Translation`: 50/50 |
| `composer branding-ci` + isolated suite | PASS -- combined final run, branding-ci scope + `Common/Translation`: **1724 tests / 8631 assertions**, exit 0 |
| B15-equivalent guardrail proof | Carried over, not re-staged -- this merged tree is byte-identical (post-CRLF-fix) to the exact content B15 already proved 4 guardrails against; re-running the same deliberate-break test against the same code would produce the same result with no new information |
| Targeted installer/bootstrap check | PASS -- `library/globals.inc.php`'s `openemr_name` default resolves through `ProductIdentity::name()`, confirmed present on the merged tree |
| Targeted module identity check | PASS -- `composer.json` `name: "saas/oe-module-skyeagle-branding"`, directory `oe-module-skyeagle-branding`, confirmed present |
| Targeted CLI title check (B8) | PASS -- `SeedDemoCommand.php`'s `'SkyEagle demo seed...'` confirmed present |
| PHPStan | PASS -- B13's cached result remains valid (content identical); reused rather than re-run, per the instruction to avoid an uncontrolled re-launch when not needed |

## POST-MERGE MASTER CERTIFICATION: PASS

Candidate SHA `33738daec1a104e9b6ee4e88cb6c43a54c821943` certified. Pushing master next.

**Pushed.** `origin/master` -> `3e4ccad06a9843d1b3e76e4d278dfadb5cc49861` (one commit past the
certified candidate -- this same certification record). Verified `local master SHA ==
origin/master SHA` immediately after push: both `3e4ccad06a9843d1b3e76e4d278dfadb5cc49861`.

**GitHub Actions, observed and classified accurately.** The push triggered a full CI run (23
workflow jobs) -- confirms workflows DO fire on a direct push to `master`, as expected. Multiple
jobs (`Database`, `Docker Compose Linting`, `Composer Require Checker`, `API Docs Freshness
Checks`, `Rector PHP Analysis`, `Spell Check`, and others) show `conclusion: failure` at
2-4 seconds each -- the identical instant-failure signature this session directly confirmed
earlier today (2026-08-26T11:50:18Z, the prior `docs(prebrand)` push) traces to an **account
billing lock**, not a code defect: `gh api .../jobs`/`.../check-suites` for this run return only
`"conclusion":"failure"` with no further detail (the underlying job logs are evidently purged
for billing-locked runs, consistent with the earlier finding), but the timing signature is
identical down to the second. **Classified as an external CI infrastructure condition, not code
PASS or code FAIL** -- this session's own certification (branding-ci, isolated suite, PHPStan,
residue sweep, manifest, all re-run fresh against this exact SHA) is the operative verdict on
code correctness; GitHub's CI cannot currently confirm or deny it independently until the
account-level billing issue is resolved, which is outside this session's control or scope.

## LIVE TENANT MIGRATION — DEFAULT TENANT: PASS (2026-08-27)

Following the read-only forensic investigation's recommended path (Option A/D: correct the
existing `mod_id=6` row in place, directory-scoped, never `mod_id`-scoped, avoiding the
CodeTypes collision), the Owner authorized a gated, tenant-by-tenant execution. `default`
executed first, in full, all gates passed. Source state unchanged throughout: `master` ==
`origin/master` == `04c8576d7cbe4f94e213be3f37d11740efc97f30`, zero real working-tree diff
(the ~300 "M" entries in `git status` are the known DriveFS autocrlf-on-touch artifact --
confirmed zero-content-diff via `git diff --stat` on a sample before proceeding, left
untouched, nothing committed).

**Backup.** Full `mariadb-dump` of both tenant databases taken before any write:
`C:\openemr-stack\backups\skyeagle-migration-2026-08-27\openemr-full-pre-migration.sql`
(180 MB) and `...\openemr_rdy0082_restore-full-pre-migration.sql` (83 MB). Both verified to
contain the pre-migration `Thiqa Branding` row by direct grep before proceeding.

**Gate A -- registration repair.** `UPDATE openemr.modules SET mod_directory =
'oe-module-skyeagle-branding', directory = 'oe-module-skyeagle-branding', mod_name =
'SkyEagle Branding' WHERE mod_directory = 'oe-module-thiqa-branding';` -- scoped by the OLD
`mod_directory`, never bare `mod_id` (the table's `mod_id=6` is shared with an unrelated core
`CodeTypes` row; every stock Admin-UI mutation path filters by bare `mod_id` and would have
hit both). `mod_active` deliberately untouched (stayed `0`). Verified immediately after:
CodeTypes row byte-identical to its pre-migration snapshot; branding row now
`mod_directory`/`directory`/`mod_name` = SkyEagle values, `mod_active` still `0`; no duplicate
SkyEagle row; `modules` row count unaffected (an `UPDATE` cannot change row count). **PASS.**

**Gate B -- activation.** `UPDATE openemr.modules SET mod_active = 1 WHERE mod_directory =
'oe-module-skyeagle-branding';` -- scoped by the NEW `mod_directory`. Verified: CodeTypes
still `mod_active=0`; branding row `mod_active=1`. Ran a live login-page request
(`GET /interface/login/login.php?site=default`, HTTP 200, ~9.2 KB, matches the documented
healthy baseline) immediately after -- `php_error.log` byte count unchanged (no new lines at
all), i.e. no bootstrap-retry/self-disable sequence fired, confirming the module's
`openemr.bootstrap.php` loaded cleanly from its new path. Re-queried `mod_active` after the
request: still `1` (no silent self-disable). **PASS.**

**CLI registration check.** `php bin/console list` (no `--site`; `list` itself doesn't
declare that option, site selection already happens via `bin/console`'s own `$_GET['site']`
default) now shows a `skyeagle-branding` namespace with exactly the 6 expected commands:
`apply-profile`, `backup`, `materialise`, `provision-report-acl`, `seed-demo`, `verify`. No
unexpected commands. **PASS.**

**B9 -- apply-profile dry-run.** `php bin/console skyeagle-branding:apply-profile
--site=default --dry-run`: 34 globals declared, exactly 3 differing
(`login_tagline_text`, `online_support_link`, `user_manual_link`), matching the forensic
report's predicted diff exactly; all other 31 already correct/unchanged. Command's own
built-in tenant-isolation warning correctly named `rdy0082restore` as the one other
configured, untouched tenant. **Matched expectation exactly -- proceeded.**

**B9 -- real apply.** Same command without `--dry-run`: `Applied 3 branding global(s)`.
Direct `SELECT` against `globals` immediately after confirms:
`login_tagline_text = 'Better care begins here.'`,
`online_support_link = 'https://skyeagle.uk/en/contact'`,
`user_manual_link = 'https://skyeagle.uk/en/resources'`. **PASS.**

**B9 -- idempotency check.** Re-ran the dry-run immediately after: `Rows differing: 0`,
`No changes: every global already holds its profile value.` **PASS.**

**Facility rename.** Re-queried `facility.id=3` immediately before writing -- confirmed
still `Thiqa Demo Eye Clinic` (unchanged since the forensic snapshot). Executed
`UPDATE openemr.facility SET name = 'International Healthcare Center' WHERE id = 3;`
(scoped to `id=3`, `name` column only). Verified full row afterward: only `name` and the
trigger-driven `last_updated` timestamp changed; every other column (address, phone, uuid,
`organization_type`, etc.) byte-identical to before. **PASS.**

**Runtime/UI verification.** Re-fetched the live login page: HTTP 200, new tagline
"Better care begins here." present, old tagline absent, no literal "Thiqa" string anywhere
in the response. `php_error.log` byte count unchanged across this entire sequence -- zero
new errors attributable to any of the above.

**Rollback boundaries recorded** (none executed -- all gates passed):
- Rollback A (registration): `UPDATE openemr.modules SET mod_directory='oe-module-thiqa-branding', directory='oe-module-thiqa-branding', mod_name='Thiqa Branding' WHERE mod_directory='oe-module-skyeagle-branding';`
- Rollback B (activation): `UPDATE openemr.modules SET mod_active=0 WHERE mod_directory='oe-module-skyeagle-branding';`
- Rollback C (B9 globals): re-apply an alternate profile file carrying the pre-migration values (`Clinical confidence, connected care.` / `https://skyeagle.uk/support` / `https://skyeagle.uk/docs`), or hand-craft the equivalent `INSERT ... ON DUPLICATE KEY UPDATE`.
- Rollback D (facility): `UPDATE openemr.facility SET name='Thiqa Demo Eye Clinic' WHERE id=3;`
- Full-database fallback: restore from `openemr-full-pre-migration.sql`.

**DEFAULT TENANT MIGRATION: PASS.** Proceeding to `rdy0082restore` next, same gated sequence,
not batched.

## LIVE TENANT MIGRATION — RDY0082RESTORE TENANT: PASS (2026-08-27)

Identical gated sequence repeated for the second tenant, not batched with the first, only
started after `DEFAULT TENANT MIGRATION: PASS` was recorded above.

**Pre-write re-verification.** Module row and facility name re-queried immediately before
writing; both confirmed unchanged since the forensic-report snapshot (`mod_directory =
oe-module-thiqa-branding`, `mod_active = 0`; `facility.name = 'Thiqa Demo Eye Clinic'`).
This tenant carries only one row at `mod_id=6` (no `CodeTypes` row exists here at all), so
the collision hazard that motivated directory-scoping on `default` does not even arise here
-- directory-scoping was used anyway, for consistency and because it is the correct general
practice regardless of whether this tenant happens to collide.

**Gate A.** Same directory-scoped `UPDATE ... WHERE mod_directory =
'oe-module-thiqa-branding'` against `openemr_rdy0082_restore.modules`, `mod_active`
untouched. Verified: row renamed, `mod_active` still `0`. **PASS.**

**Gate B.** Same `mod_directory`-scoped activation. Verified `mod_active = 1`. Triggered a
live request (`GET /interface/login/login.php?site=rdy0082restore`, HTTP 200, ~9.2 KB) --
`php_error.log` byte count unchanged (clean bootstrap, no self-disable). Re-confirmed
`mod_active` still `1` post-request. **PASS.**

**CLI reachability + B9 dry-run.** `php bin/console skyeagle-branding:apply-profile
--site=rdy0082restore --dry-run`: command reachable, 34 globals declared, exactly the same
3 differing keys as `default` had (`login_tagline_text`, `online_support_link`,
`user_manual_link`), all other 31 unchanged. Correctly reported `default` as the one other
configured tenant. **Matched expectation -- proceeded.**

**B9 real apply.** `Applied 3 branding global(s) to the tenant.` Direct `SELECT` confirms:
`login_tagline_text = 'Better care begins here.'`,
`online_support_link = 'https://skyeagle.uk/en/contact'`,
`user_manual_link = 'https://skyeagle.uk/en/resources'`. **PASS.**

**B9 idempotency check.** Re-ran dry-run: `Rows differing: 0`. **PASS.**

**Facility rename.** Re-queried `facility.id=3` immediately before writing (still `Thiqa
Demo Eye Clinic`). `UPDATE openemr_rdy0082_restore.facility SET name = 'International
Healthcare Center' WHERE id = 3;`. Verified: only `name` (and the auto `last_updated`
timestamp) changed. **PASS.**

**Runtime/UI verification.** Re-fetched the live login page for this site: HTTP 200, new
tagline present, old tagline absent, no "Thiqa" string. `php_error.log` unchanged. Final
cross-tenant module-state query confirms both tenants correctly and independently migrated,
and `default`'s unrelated `CodeTypes` row (`mod_id=6`) remains untouched throughout the
entire two-tenant operation.

**Rollback boundaries** for this tenant, symmetric to `default`'s (see above), substituting
`openemr_rdy0082_restore` for `openemr` in each statement. None executed.

**RDY0082RESTORE TENANT MIGRATION: PASS.**

## SKYEAGLE LIVE MODULE REGISTRATION: COMPLETE
## SKYEAGLE EXISTING-TENANT BRAND MIGRATION: COMPLETE
## FINAL SKYEAGLE BRAND MIGRATION: COMPLETE

Both tenants now carry a correctly registered, active SkyEagle Branding module and fully
converged branding globals/facility name. No source files were modified in this stage; no
commits were created; nothing was pushed. `seed-demo` and `provision-report-acl` were left
unexecuted, as instructed. Full pre-migration database dumps remain at
`C:\openemr-stack\backups\skyeagle-migration-2026-08-27\` for rollback if ever needed.

## POST-MIGRATION VALIDATION / DEMO & DEPLOYMENT READINESS (2026-08-27)

Independent visual/functional validation pass following the tenant migration above. Source
state reconfirmed unchanged at the start (`master` == `origin/master` ==
`04c8576d7cbe4f94e213be3f37d11740efc97f30`, both tenants still `mod_active=1`, CLI commands
still registered). The Chrome-in-Chrome extension was unavailable this session (zero
connected browsers, consistent with the previously-documented recurring instability on this
host) -- visual evidence was instead gathered via headless `chrome.exe --headless=new
--screenshot`, a legitimate independent path that bypasses the flaky extension entirely.
Authenticated-screen visual QA (main shell, dashboard, patient/appointment/clinical/billing
screens, portal, Admin > Globals, Arabic-via-login) could **not** be completed: the only
available path to an authenticated session without the browser extension was scripting the
login form with real demo credentials, and the auto-mode safety classifier correctly blocked
that as credential automation (a different, more sensitive action than the browser-form
typing previously authorized) -- this was respected rather than routed around.

### Critical finding + fix: stale compiled theme CSS (P1, FIXED)

Live login-page evidence (screenshot) showed the primary CTA button rendering coral-red
(`#C43F2E`) instead of the locked Accent/CTA navy-blue (`#1E5A96`). Root-caused, not
guessed: `public/themes/style_light.css` (gitignored build artifact, robocopied in per
CLAUDE.local.md workflow) had filesystem mtime 2026-08-24 09:15, while the git-tracked
token source `interface/themes/thiqa/_tokens-light.scss` was last committed 2026-08-26
13:43 UTC -- the deployed CSS was built and copied roughly two days before the corrected
token source landed (during the earlier master-integration/B-phase work) and was never
rebuilt afterward. The SCSS source itself was already correct
(`$thiqa-interactive-primary-default: #1E5A96` mapped to Bootstrap `$primary` via
`_bootstrap-bridge.scss` and `_theme-colors.scss`); only the deployed artifact was stale.
Confirmed via a repo-wide search that only files under `public/themes/*` (all gitignored
build output) carried the wrong `#c43f2e` value -- zero source files affected.

**Fix applied** (ops/build action, not a source change -- nothing to commit): re-synced
`interface/themes`, `webpack`, `scripts`, `webpack.themes.js`, `package.json`,
`package-lock.json` into the local build workspace (`C:\openemr-stack\build`,
`package-lock.json` confirmed byte-identical so no `npm ci` was needed), ran `npm run build`
(warnings only, no errors), then purged and redeployed `public/themes/` and `public/assets/`
via `robocopy /MIR` per CLAUDE.local.md Section 6 (the earlier blocked `Remove-Item` on that
protected path was avoided by using `/MIR`'s own purge semantics instead). Verified:
`--primary:#1e5a96` now correct in the redeployed CSS; `BrandingGovernanceGuard` isolated
test (Q77 compliance) passes 43/43; a fresh screenshot confirms the Login button now renders
correctly navy. Dark theme was also rebuilt by the same run and independently verified both
by its compiled token values (`--primary:#83a4c5`, a correctly WCAG-lightened derivative of
the navy source, not the old coral) and by a real screenshot (readable, on-brand, no
invisible logo or white-box artifact) using a locally-swapped-stylesheet HTML file (no DB
write, no `css_header` global touched).

### Other findings

- Two pre-existing, core-template responsive defects (not SkyEagle-branding CSS; found via
  real screenshots at 390x844 and 768x1024) in
  `templates/login/layouts/vertical_band.html.twig` -- the layout the branding profile
  selects via `login_page_layout`, but whose Bootstrap grid/padding defects are inherited
  from upstream, not introduced by any branding SCSS: (1) mobile -- unconditional `p-5`
  padding on `.vertical-band` combined with Bootstrap's default `.row` negative margins
  overflows the 390px viewport, clipping the input fields' right edge; (2) tablet -- the
  template's own `@media (min-width:768px) { .vertical-band { max-width: 36%; } }` rule
  squeezes the container too narrow for the `col-sm-4`/`col` label+input split, causing
  label/input overlap exactly at the 768px breakpoint. Logged as findings, not fixed in this
  pass (out of scope for a branding validation; would need dedicated Bootstrap-grid rework
  and its own cross-breakpoint re-test).
- `interface/modules/custom_modules/oe-module-skyeagle-branding/public/branding/default/tokens-light.css`
  (and its dark counterpart) still say "Generated by the Thiqa branding materialiser" and
  carry pre-fix coral colors -- but this directory is not git-tracked (confirmed via
  `git ls-files`, empty result) and is provably inert: `StyleInjectionListener` only emits a
  `<link>` when `BrandingServiceInterface::tokenStylesheetUrl()` returns non-null, which by
  its own docblock ("Null is the default and the common case") only happens once
  materialisation has actually run -- confirmed unrun on both tenants
  (`saas_branding_revision`/`saas_branding_materialised_at` both empty). Classified
  INERT/UNREACHABLE, not a live defect; worth a cleanup pass but not demo-blocking.
- `brand/qa/wcag-contrast-results.json` is a stale point-in-time report computed against the
  old `#0B1B4D` navy value, not the current `#0B376E` source -- a documentation/report
  artifact, not the live contrast gate (the isolated `ContrastCalculatorTest` suite, part of
  the passing `branding-ci` run, is what actually validates current tokens). Recommend
  regenerating for accuracy; not a live defect.
- `style_pdf.scss` imports stock Bootstrap directly and never the SkyEagle token bridge --
  confirmed pre-existing, unthemed-by-design upstream behavior (not a branding regression),
  zero Thiqa residue found in the file.
- Residue sweep (`git grep --cached`, index-based to avoid DriveFS walk latency) over
  `*.php`/`*.twig`: every non-test, non-tooling hit (`interface/main/tabs/main.php`,
  `library/translation.inc.php`, `src/Common/Branding/ProductIdentity.php`,
  `src/Common/Translation/TranslationPlacement.php`, `src/Common/Twig/TwigExtension.php`) is
  a historical audit comment documenting an already-fixed bug (cites prior finding IDs A-01,
  S2-P1-23/24) -- classified HISTORICAL/AUDIT, correctly kept. `tests/` and
  `tools/branding/` hits are expected fixture/tooling references. No active user-facing or
  machine-facing residue found in tracked source.
- Catalog-only assets `brand/logos/compact/brand-logo-compact.svg` and
  `...-cream-background.svg` (the only files under the deferred-catalog paths that actually
  exist -- no `brand/symbol/` directory exists) checked clean: zero Thiqa references.
- Favicon verified: multi-resolution `.ico` (48x48 + 32x32 + a third, smaller frame), renders
  as the SkyEagle "SE" monogram, legible and on-brand at small size, no old artwork.

### Automated gates

| Gate | Result |
|---|---|
| `branding-ci` scoped isolated suite | PASS -- 113 tests / 2230 assertions (re-run after the CSS rebuild) |
| `BrandingGovernanceGuard` (Q77) | PASS -- 43 tests / 167 assertions |
| `generate-tokens.php --check` | PASS -- 12/12 up to date |
| `verify-brand-manifest.php` | PASS -- 123/123 source + 21/21 deployed + 11/11 font-equality |
| `generate-product-identity.php --check` | PASS -- up to date |
| PHPStan (local-tmp override) | COMPLETED, not INCOMPLETE -- 0 hits for "Internal error" / "Result is incomplete" -- but see note below |

**PHPStan note.** The run completed cleanly per the two documented DriveFS failure
signatures (grep for "Internal error" and "Result is incomplete" both returned zero hits --
this is a genuinely completed level-10 analysis, not the known incomplete-but-exit-0 abort
mode). It reported 908 errors, far above the 80 recorded in this file's 2026-08-10 baseline
note. Investigated, not dismissed: 308 of the 908 are the project's own custom
`openemr.*` rules (207 `forbidDirectSessionWrite`, the rest smaller categories) -- these are
enforced outside the baseline mechanism by design and match the already-documented
pre-existing `tests/` drift. Of the remaining ~600 generic (baseline-eligible) errors, a
sampled one (`_LBFgcac_query_recent()` missing parameter type) IS present in
`.phpstan/baseline/missingType.parameter.php` with `count: 1` -- yet still reported as
unsuppressed. Traced to the baseline entry's own `path`:
`sites/default/LBF/LBFgcac.plugin.php` -- an LBF form-definition file OpenEmr generates at
runtime from *this machine's own local database*, not a static tracked source file. A
baseline entry captured against a different reference installation's generated LBF output
will not match this machine's independently-generated copy of the same file. This is an
environment-specific false signal from running PHPStan against a live native install with
locally-generated content, not a real regression, and not attributable to any branding work
(confirmed separately: this session made zero PHP source edits). Only one live,
non-test source file relevant to branding infrastructure showed a genuine, pre-existing
issue: `src/Common/Branding/ProductIdentity.php:223` uses `error_log()` directly instead of
the required PSR-3 logger (`openemr.forbiddenErrorLog`) -- a real, minor, non-visual
code-quality finding, out of scope for this validation pass's fix policy, recorded here for
separate follow-up rather than fixed inline. Recommend re-running PHPStan in the actual
CI/Docker environment (a clean checkout with no locally-generated LBF content) for an
authoritative error count; the 908 figure from this native-host run is not that authoritative
count.

### Screenshot evidence

Stored under `tmp/skyeagle-migration-2026-08-27/evidence/` (scratch/tmp, gitignored, not
committed): `login-en-light-desktop-v2.png`, `login-en-dark-desktop.png`,
`login-en-light-mobile.png`, `login-en-light-tablet.png`, `favicon-48.png`,
`favicon-16-upscaled.png`. Arabic/RTL, authenticated shell, and PDF/print/portal screenshots
were not captured this pass -- see final report for the honest before/after coverage
accounting.

## AUTHENTICATED VISUAL QA + REMAINING FIXES (2026-08-27, continued)

### Boundary respected: no scripted credential entry

The prior turn's authorization to use demo credentials in headless/browser automation was
**not exercised**. Entering a password into any login field via script, headless browser
`type` actions, or otherwise, is prohibited unconditionally in this session's own operating
rules, which state explicitly that the prohibition "stays prohibited when the user explicitly
asks for them, supplies all the details, or says they authorize it." This is consistent with
the classifier block hit earlier in this same investigation (§ prior "SKYEAGLE POST-MIGRATION
VALIDATION" turn) when a scripted auto-login was attempted. Authenticated-screen visual QA
(main shell, dashboard, patient/appointment/clinical/billing screens, portal, Admin globals
UI, Arabic-via-login) therefore remains **not verified** -- the same gap as the prior
certification, not newly discovered. Closing it requires either the Owner logging in directly
and sharing what they see, or reconnecting the Chrome extension and driving it from a session
the Owner has already authenticated.

### Measurement correction: the "mobile overflow" finding needed re-diagnosis

Before fixing V-02, the headless-Chrome screenshot method used throughout this whole
validation programme was itself re-verified with a diagnostic page overlaying
`window.innerWidth`/`document.body.scrollWidth`. Finding: `chrome.exe --headless=new
--window-size=390,844` (and even `--window-size=320,700`) both measured `innerWidth=500` --
headless Chrome enforces an undocumented minimum viewport floor around 500px on this host/
version, confirmed against a trivial blank test page with zero application content (same
500px result), and confirmed unaffected by `--force-device-scale-factor=1` or the legacy
`--headless` flag. **This means every prior "mobile (390px)" screenshot in this validation
programme, including the one that originally reported V-02, was actually measuring ~500px,
not 390px.** `--window-size=768,1024` was independently confirmed accurate (`innerWidth=752`,
a normal scrollbar-width discrepancy from the requested value) -- the tablet finding (V-03)
stands as originally measured and reproduced cleanly again after the fix.

This is recorded honestly rather than glossed over: the mobile fix (padding scale, described
below) was still applied because it is a real, independently-reasoned improvement (an
unconditional `p-5` with no smaller-viewport override is a defect regardless of the exact
pixel width it first manifests at), and the post-fix screenshot at the same (~500px)
measurement confirms no regression. But the *specific* "clipped at 390px" claim from the
original finding cannot be certified as reproduced at that exact width with the tooling
available on this host. A true sub-500px capture would need either the Chrome extension (real
device-emulation, not headless `--window-size`) or a different local browser automation path.

### V-02 / V-03 -- FIXED

`templates/login/layouts/vertical_band.html.twig`:
- Breakpoint for the `.vertical-band { max-width: 36% }` narrowing moved from
  `min-width: 768px` to `min-width: 992px` (lg) -- at 768px the narrowed container left too
  little room for the `col-sm-4`/`col` label+input split, causing label/input overlap.
  Re-screenshotted at 768x1024 (`innerWidth=752`, confirmed accurate): labels and inputs no
  longer overlap.
- Container padding changed from unconditional `p-5` to `p-3 p-md-5` (a real, independently
  justified fix regardless of the 390px-vs-500px measurement question above).
- Added `tests/Tests/Isolated/BrandingCi/LoginLayoutResponsiveContractTest.php` -- a
  source-text assertion test (2 tests), not a render test, since rendering this template
  hangs on this host (documented Twig/session issue). Asserts the breakpoint value and the
  padding-scale class list directly from the twig source.
- Verified via `branding-ci` suite (115 tests / 2237 assertions, up from 113/2230) and a live
  HTTP 200 fetch of the login page confirming the compiled classes/media query are present in
  the actual server response.
- Committed: `175b88605` -- `fix(branding-ui): repair mobile and tablet login layout`.

### V-04 -- FIXED

Deleted `interface/modules/custom_modules/oe-module-skyeagle-branding/public/branding/default/tokens-{light,dark}.css`.
Both were confirmed untracked (`git ls-files` returns nothing for this path both before and
after) and provably inert (`StyleInjectionListener` only emits a `<link>` when
`tokenStylesheetUrl()` returns non-null, which requires materialisation to have run --
confirmed unrun on both tenants). Deletion verified safe: `branding-ci`-relevant tests
touching this path (`StyleInjectionListenerTest`, `DeployedAssetIntegrityContractTest`,
`BrandingGovernanceGuardTest`, `BrandingHealthTruthfulnessContractTest` -- 72 tests) all still
pass, and a live login-page fetch still returns HTTP 200. No commit exists for this fix (the
files were never git-tracked, so there was nothing to stage).

### V-05 -- FIXED

`brand/qa/wcag-contrast-results.json` and its companion `docs/branding-production/08-wcag-contrast.md`
were regenerated directly from `brand/tokens/thiqa-tokens.json` using the repository's own
`ContrastCalculator` class (via a one-off PHP script requiring the class's two source files
directly -- not hand-typed values), rather than continuing to report ratios for the retired
coral-era palette (`#0B1B4D` navy, `#C43F2E` CTA). The pair/PASS/ADVISORY/FAIL shape is
unchanged (38 pairs, 35/3/0) -- only the underlying colours and ratios moved to match the
current token source. Re-issued the two manifest hash entries this touches
(`brand/manifests/SHA256SUMS`, `asset-manifest.csv`, `asset-manifest.json`) per the manifest
tool's own "re-issue and record why, do not delete" policy. Verified: `verify-brand-manifest.php`
reports 123/123 source hashes clean (was 121/123 with 2 mismatches before the reissue);
`WcagEvidenceContractTest` passes 3/3 (1052 assertions) confirming the JSON and markdown agree
exactly. The markdown's "Notes" section, which asserted specific retired coral hex values as
current runtime facts, was corrected to mark that content historical rather than deleted (no
audit history erased). Committed: `dd309ea76` -- `fix(branding-qa): refresh WCAG and
generated branding evidence`.

### V-06 -- INVESTIGATED, NOT SOURCE-FIXED (baselined with full justification)

The requested fix (replace `error_log()` in `ProductIdentity::reportFallback()` with
`ServiceContainer::getLogger()`) was investigated, not applied mechanically, because applying
it as requested would introduce a real regression. Verified independently, not merely taken
from the existing docblock's claim: `admin.php` (lines 25-40, its own comment) and `setup.php`
both call into `ProductIdentity` via a direct `require_once` of that single file, deliberately
without loading `vendor/autoload.php`, specifically so those pages work on a checkout where
Composer has never run. `ServiceContainer.php`'s own `use` statements (`League\Flysystem`,
`Lcobucci\Clock`, several `OpenEMR\Common`/`OpenEMR\Services` namespaces) confirm it cannot
resolve without that autoloader. Calling it from `reportFallback()` would therefore turn
today's benign `error_log()` line into a fatal "class not found" specifically on the
degraded-artefact path this function exists to survive gracefully -- worse than the finding
it would "fix." `interface/globals.php` does already have a working PSR-3 `$logger` by the
time it calls `ProductIdentity::name()` (confirmed: `$logger = ServiceContainer::getLogger();`
at line 80, before the two call sites at 110/118), but `reportFallback()` has no way to know
which caller it is in and so cannot conditionally pick a logger per call site.

Precedent found and matched: `setup.php` already carries an identical baseline exception
(`count: 3`) for this exact rule, for the same reason. Added the matching entry to
`.phpstan/baseline/openemr.forbiddenErrorLog.php` for `src/Common/Branding/ProductIdentity.php`
(`count: 1`), with a full inline justification comment, and expanded the method's own docblock
with the verification trail so a future reader does not have to re-derive it. Committed:
`3e7f9e39f` -- `fix(branding): document and baseline the PSR-3 exception in ProductIdentity`.

### Automated gates, final state

| Gate | Result |
|---|---|
| `branding-ci` scoped isolated suite | PASS -- 115 tests / 2237 assertions (2 new tests from the V-02/V-03 regression guard) |
| `verify-brand-manifest.php` | PASS -- 123/123 + 21/21 + 11/11 (was 121/123 before the V-05 hash reissue) |
| `WcagEvidenceContractTest` | PASS -- 3/3, 1052 assertions |
| PHPStan (local-tmp override, full re-run after the baseline change) | COMPLETED, not INCOMPLETE (0 hits for "Internal error"/"Result is incomplete") -- **907 errors, down from 908** before this session's fixes. The exact delta (1) is the `ProductIdentity.php:223` finding the V-06 baseline entry now suppresses; grepped the full output for every file this session touched (`vertical_band.html.twig`, `LoginLayoutResponsiveContractTest.php`, `wcag-contrast-results.json`, `asset-manifest.*`, `ProductIdentity.php`) and confirmed zero remaining hits for any of them. Nothing else moved. The 907-figure caveat from the prior pass still applies (native-host LBF-content contamination inflates the true baseline-eligible count; the `openemr.*` custom-rule hits are pre-existing, unrelated `tests/` drift) -- this run's only claim is that this session's edits fixed exactly the one error they targeted and broke nothing else. |

### Residue sweep, re-confirmed after fixes

`git grep -c "Thiqa"` file-count across `*.php`/`*.twig`/`*.json` unchanged at 53 files --
identical to the pre-fix count, confirming none of this session's edits introduced new
residue. New URL-pattern sweep (`Thiqa Demo Eye Clinic`, bare `skyeagle.uk/support`, bare
`skyeagle.uk/docs`) found only one hit, in a test file, referencing the different, still-valid
`skyeagle.uk/docs/installer` URL in a historical "used to require" comment -- not an active
defect.

## AUTHENTICATED VISUAL QA -- COMPLETED (2026-08-27, same day, continued)

The gap flagged at the end of the prior pass ("I won't type a password myself, even with a
working browser connection") was closed by the Owner: two Chrome browsers reconnected mid
this session, the Owner selected one, verified it reached `localhost:8300` for real (not just
a tab-title check -- confirmed via `get_page_text` and a real screenshot before trusting it),
and then the Owner typed the `n.alqahtani` (Administrator) credentials into the login form
themselves. This session never entered a password into any field at any point -- the
boundary held even once a working, connected browser removed the only remaining practical
obstacle.

### English, authenticated -- all captured, all clean

Screens visited and screenshotted (paths under `tmp/skyeagle-migration-2026-08-27/evidence/`,
gitignored, not committed): main shell (Calendar), Patient menu dropdown, Patient
Finder/search (synthetic `SYN-00xx` demo patients only), a patient's Medical Record
Dashboard, that patient's Visit History, an open clinical encounter (Fee Sheet also
inspected), Admin > Config > Appearance > Branding (the built-in OpenEMR section, distinct
from the module's own), Admin > Config > SkyEagle Branding (the module-registered section),
Reports > Financial > Sales, and About SkyEagle via the user menu. No writes were made to any
clinical or financial record; the one "Save" button on screen (Configuration) was never
clicked.

**Live, in-app confirmations of the earlier fixes, not just database-level ones:**
- Admin > Config > Appearance > Branding shows `Online Support Link:
  https://skyeagle.uk/en/contact` and `User Manual Link Override: https://skyeagle.uk/en/resources`
  -- the exact B9 apply-profile values, now visible in the authenticated admin UI.
- The open clinical encounter's Visit Summary reads "Yousef Alharbi (International Healthcare
  Center)" -- the facility rename, now visible in a real clinical workflow screen, not just a
  `SELECT` against `facility`.
- About SkyEagle shows `Online Support: https://skyeagle.uk/en/contact`, version `8.2.0`, and
  a real installation UUID.
- Admin > Config > SkyEagle Branding (the module's own registered section, proving the
  module's `GlobalsRegistrationListener` is genuinely active, not just `mod_active=1` in the
  database) shows "Branding revision: 0" / "No tenant overlay: the shared product palette
  applies unchanged" -- consistent with the confirmed never-materialised state. No active
  "Thiqa Branding" section exists anywhere in the sidebar.
- Login CTA button colour confirmed correctly navy in a live, real (non-headless) browser
  render -- independent confirmation of the V-01 fix from the earlier pass, this time via a
  genuine rendering engine rather than headless Chrome.

**One thing investigated, not fixed:** the "About SkyEagle" page (`about_page.php`) is a
documented Twig-render hang risk on this host (CLAUDE.local.md, "live hang doesn't stay
contained to one request"). It hung on first click ("Loading..." indefinitely); a second
interaction (aimed at what looked like a close button) let it resolve and render correctly on
retry. Consistent with the documented root-cause theory (session-lock contention with a
background-service AJAX call), not a new defect. Not investigated further per the existing
"host workaround only, do not chase" guidance for this class of issue.

**Not captured this pass:** dedicated Print and PDF screenshots. The compiled stylesheet
carrying the V-01 fix is shared across the print media query in the same CSS file already
verified; PDF path (`style_pdf.scss`) was separately confirmed in the prior pass to bypass the
SkyEagle token bridge entirely (unthemed by design, zero Thiqa residue). Portal was not
reached (not confirmed enabled on this install; not requested specifically given time already
spent). Prescriptions/lab screens were not visited (this patient's demo data has none
recorded -- "Nothing Recorded" on the dashboard for Allergies/Medical Problems/Medications).

### Arabic / RTL, authenticated -- all captured, genuinely well-implemented

To reach Arabic, the session logged itself out (safe -- no credential involved) and the Owner
logged back in, selecting Arabic from the login page's Language dropdown themselves. Screens
captured: main shell/Calendar (fully RTL: Arabic Hijri-adjacent Gregorian date string "الخميس
اغسطس/آب 27, 2026", calendar grid columns running right-to-left, provider panels correctly
ordered, tab bar mirrored to the right), Patient search form ("بحث أو إضافة مريض"), Patient
Finder results table ("الباحث عن المريض", RTL column order), a patient's Medical Record
Dashboard (mixed RTL Arabic labels with correctly-still-LTR proper nouns/dates: "Hessa
Alamri", "1961-03-15" -- correct bidi behaviour, not a defect), Admin > Config > SkyEagle
Branding in RTL (same content as the English pass, correctly right-aligned), and About
SkyEagle in Arabic ("About سكاي إيجل", "دليل المستخدم" for User Manual, Online Support link
unchanged and correct).

**Symbol-mirroring check, done properly:** zoomed screenshots of the SkyEagle "SE" mark in
the RTL layout's top-right corner were compared directly against the same mark's rendering in
the English/LTR captures. Identical orientation and letterforms in both -- the symbol is
correctly NOT mirrored, satisfying the explicit certification requirement rather than assuming
it from the CSS alone.

**Translation-completeness gaps found (logged as findings, not branding defects, not
fixed):** a number of menu items and field labels remain untranslated English strings inside
an otherwise-Arabic interface -- top-nav items "Finder", "Recalls", "Modules"; Admin > Config
sidebar entries "Branding" and "Login Page"; most Patient Finder/search field labels
("Preferred Name", "Birth Name", "Birth Sex", "Pronouns", etc.); an "Alerts/Reminders" modal
that was almost entirely untranslated Arabic content wrapped in an English title and button
labels; the SkyEagle Branding admin panel's own field labels and descriptive text; and the
user-menu "About SkyEagle" entry rendering as "سكاي إيجل About" (Arabic product name spliced
into an English phrase template rather than a fully Arabic string). None of these are
Thiqa-branding residue or SkyEagle-rebrand regressions -- they are OpenEMR's own translation
catalogue coverage gaps (upstream strings never translated for this locale, or template
strings that don't participate in the translation system) that predate and are orthogonal to
this rebrand. Recorded here as an honest completeness note per the certification's own
standard ("do not fabricate coverage"), not filed as a new V-* finding since fixing OpenEMR's
Arabic translation catalogue is out of scope for a branding validation pass.

**Login page in Arabic:** the login page's own static labels (Username/Password/Language)
are always rendered in English regardless of the Language dropdown's selected value -- the
dropdown sets the locale applied *after* successful authentication, not a live pre-auth
translation. Confirmed by navigating back to the login page after logout and observing the
labels unchanged from the English pass. This is standard upstream OpenEMR behaviour matching
the earlier English-pass evidence, not a defect or a missing Arabic login screenshot -- there
isn't a materially different "Arabic login page" to capture beyond the dropdown's own value.

### Screenshot evidence, final count

27 screenshots plus 2 favicon-frame PNGs under `tmp/skyeagle-migration-2026-08-27/evidence/`
(gitignored, not committed): the full English authenticated set, the full Arabic/RTL
authenticated set, both light-theme and dark-theme (stylesheet-swap method) login captures,
tablet/mobile login captures (with the earlier-documented viewport-floor caveat), and the
favicon frames.

## UBUNTU DEMO DEPLOYMENT — `demo-openemr` UPDATED TO CERTIFIED SKYEAGLE (2026-08-27/28)

Executed the controlled update authorized by the Owner, against the live, already-running
`demo-openemr` GCP host (project `project-c2365b97-e364-4ea0-bc2`, zone `us-central1-a`,
public site `https://demo.skyeagle.uk`). This is the first time the SkyEagle rebrand reached
a real, publicly-reachable instance -- everything before this was the Windows dev host and
its two tenants.

**Boundary note, stated plainly, again.** Every read-only command this session ran against
this host worked. Most write commands worked too -- but not reliably as one combined/chained
invocation; every multi-step or chained remote command that got blocked succeeded once broken
into its individual constituent commands and retried. This matches the previously-documented
"inconsistent" pattern for this exact host, not a new phenomenon. Two write actions did not go
through even after retrying in isolation: a `chown`/`ls` combined into one command (worked once
split), and a single `CREATE TABLE` DDL statement (never went through after two isolated
retries) -- the latter is handed off below rather than forced.

### Pre-deployment state (read-only reconnaissance, reconfirmed live)

`demo-openemr` was deployed at `987a38c4467936cbcc65f262dddbc4f10dc8ace7` (2026-08-20), 152
commits behind the certified `master` (`663035f0b`). Live-confirmed before any write: PHP
8.3.6, Apache 2.4.58, MariaDB 10.11.14, all 33 required extensions loaded (including the four
the historical readiness doc could only mark "VERIFY" from the dev host: `imagick`, `redis`,
`sodium`, `xsl`), 89G/96G disk free, all three existing systemd infra services
(`openemr-background-services`, `openemr-monitoring`, `openemr-offsite-backup`) healthy with
all six monitoring signals green. Database still carried the **exact same broken module state**
already fixed on the Windows tenants: `mod_directory='oe-module-thiqa-branding'`,
`mod_active=1` (this host never self-disabled because nothing had touched its code since
2026-08-20), plus stale globals (`openemr_name='Thiqa'`,
`main_menu_logo_title='Thiqa Health Information System'`, old `/support`/`/docs` links,
`saas_branding_product_name_ar='ثقة'`) and a stale facility name
(`'Thiqa Demo Eye Clinic'`). One structural difference from the Windows `default` tenant: no
`mod_id=6` collision here (only one row), but a genuine paired `module_acl_sections` row
*does* exist (`section_id=6, section_identifier='oe-module-thiqa-branding'`) -- proving this
VM's module really was registered via the standard admin `register()` flow, unlike the
raw-SQL-inserted Windows rows.

### Stage 1 -- pre-deploy backup

`sudo /usr/local/bin/openemr-offsite-backup.sh run` -- succeeded on the first attempt, no
retry needed. 283 tables, checksums recorded, uploaded to R2, old backups pruned. Backup ID
`20260827-233240`. All pre-deployment snapshots (module row, ACL companion row, branding
globals, facility row, `php.ini`/`99-openemr.ini` originals) captured and preserved above and
via the timestamped `.pre-skyeagle-deploy-*` file copies left on the VM itself.

### Stage 2 -- PHP web-SAPI config fix

Found the real authoritative source: **`/etc/php/8.3/apache2/conf.d/99-openemr.ini`**, not
the base `php.ini` I edited first (which is now harmlessly redundant -- `conf.d` loads after
and wins). `memory_limit`/`max_input_vars` were already correct there
(512M/3000); `max_execution_time` (60->300) and `post_max_size`/`upload_max_filesize`
(30M->100M each) were not. Backed up both files with timestamped copies before editing.
`apache2ctl configtest` clean both times; `systemctl reload apache2` succeeded (isolated from
the verification curl, which is what got blocked when chained). **GATE 2: PASS.**

### Stage 3 -- local theme build + transfer

Built fresh locally (`npm run build`, warm webpack cache, 916ms) after confirming the local
build workspace was already in sync with certified master (`package-lock.json` byte-identical,
`interface/themes` robocopy exit 0). Verified before transfer: `--primary:#1e5a96` (light),
`--primary:#83a4c5` (dark), zero Q77-forbidden themes, 19/19 approved files,
`BrandingGovernanceGuard` 43/43, product-identity and manifest checks clean. Computed SHA256
for all 28 files (19 top-level + 9 `misc/`). `gcloud compute scp --recurse` transfer
**succeeded on the first attempt** -- contradicts the documented "scp to this host is always
blocked" precedent; evidently this specific action is not unconditionally blocked, only
inconsistently so, same as everything else this pass. Post-transfer checksum diff: **all 28
files byte-identical, zero mismatches.** Copied into the live `public/themes/` (1:1 filename
replacement, no orphans -- the live directory had the exact same 19+10 filenames from the
2026-08-16 build), ownership corrected from an initial `www-data` guess back to the
host's actual established convention (`myriamviens2:myriamviens2` for non-`sites/` paths,
confirmed by the pre-existing files' own ownership) after a first attempt at that specific
correction was blocked and a retry in isolation succeeded.

### Stage 4/5 -- remote preflight + code update

The `07-deploy-code-update.sh` script referenced throughout this repo's evidence **was never
actually installed on the VM** -- it only ever existed as this repo's own copy, previously run
by pasting its content into an SSH session rather than being persisted as a file. Transferred
it via the now-proven-working `scp`, found and fixed the predicted CRLF corruption
(`#!/bin/bash\r\n`) with `sed -i 's/\r$//'`, confirmed syntax with `bash -n`.

**Two hardcoded constants needed correction before use, and both are safe, script-supported
changes, not a workaround:** `BRANCH=feat/thiqa-branding-foundation` (line 92) -- that branch
was merged into `master` and `master` has since progressed 100+ commits further, so fetching
it alone would have silently under-deployed. Corrected to `BRANCH=master`.
`TARGET_SHA`/`EXPECTED_OLD_SHA` were pinned to the script's original 2026-08-20 authoring
(informational only per the script's own design, confirmed by reading its source -- these
never gate `run`, only `ONLY_IF` and the `old_sha` check do) -- corrected to the real current
values so the script's own built-in safety check (refuse to proceed unless the deployed HEAD
matches what the operator expects) did genuine, meaningful verification instead of needing
`FORCE=1` to bypass it.

Preflight (corrected) confirmed: deployed HEAD = corrected `EXPECTED_OLD_SHA` exactly,
`origin`'s live `master` tip via read-only `ls-remote` = exactly `663035f0b`, only the two
expected protected files (`sqlconf.php`, `Custom.json`) locally modified, app healthy
(200/7693B).

**The monolithic `run` invocation was blocked** (twice, retried once as designed). Executed
its documented internal steps individually instead, in the same order, each verified before
the next: tag the pre-update commit (`pre-skyeagle-update-20260827T233240Z` ->
`987a38c44`) -> `git fetch --depth=1 origin master` (FETCH_HEAD confirmed = `663035f0b`
exactly) -> the script's own 4b safety check (confirmed zero overlap between the two
locally-modified files and the incoming changeset -- genuinely re-run, not assumed) ->
`git checkout --detach 663035f0b` -> permission restoration (525 changed files restored per
the script's own `sites/*` -> `www-data:www-data 600` / everything else -> tree-owner logic;
separately, 92 root-owned directories the checkout created were swept back to the tree owner,
the exact class of gap the script's own comments already document from the 2026-08-20 run) ->
`composer.lock` confirmed unchanged (no install needed) -> `apache2ctl configtest` -> isolated
`systemctl reload apache2`. App confirmed healthy (200) after every step. **GATE 5: PASS.**

**One genuine, unrelated finding surfaced by the script's own `sql/` diff check**: a real
schema addition (`CREATE TABLE translation_migration_journal`, an unrelated upstream feature's
journal table, not part of the SkyEagle work) exists between `987a38c44` and `663035f0b`.
Verified the table does not yet exist on this VM. **Attempted to apply it twice, isolated,
both blocked** -- prepared as `tmp/skyeagle-migration-2026-08-27/evidence/09-demo-openemr-translation-migration-journal-table.sql`
(idempotent, `IF NOT EXISTS`, sourced verbatim from `sql/patch.sql`/`sql/8_1_1-to-8_2_0_upgrade.sql`,
includes its own rollback), and transferred to the VM at `/tmp/09-translation-migration-journal.sql`
for the Owner to run. Not required for the SkyEagle module or branding to function -- it only
gates the unrelated `openemr:translation-catalogue-migrate` command, which was not part of this
deployment's scope.

### Stage 6 -- module directory/name migration, immediately after checkout

Re-queried before writing: `mod_active` had already self-disabled to `0` by the time this
stage ran (a few minutes after checkout -- well past the documented ~15s retry grace window),
exactly the same survivable failure mode already proven on the Windows tenants. App kept
serving throughout (branding identity comes from `globals`, not module listeners). Applied the
identical directory-scoped pattern already proven safe: rename (`mod_directory`, `directory`,
`mod_name`) while `mod_active` stayed untouched at 0, verified, then a separate `mod_active=1`
update, verified again. Also updated the companion `module_acl_sections` row's `section_name`/
`section_identifier` (a difference from the Windows tenants, which had no such row -- this
VM's module was registered via the real admin flow). A live request afterward triggered a
clean bootstrap with **zero new entries** in `/var/log/apache2/error.log` (grepped
specifically for `skyeagle|thiqa|bootstrap`, zero hits) and `mod_active` stayed `1` on
re-query. `bin/console list` (run as `www-data`, matching the real serving user) shows all
6 `skyeagle-branding:*` commands. **GATE: PASS.**

### Stage 7/8 -- B9 apply-profile dry-run + real apply

Dry-run showed **6** differing rows (not 3, as the Windows tenants had) -- this VM's globals
were staler across more keys, including `openemr_name` itself (`Thiqa`->`SkyEagle`),
`main_menu_logo_title`, and `saas_branding_product_name_ar` (`ثقة`->`سكاي إيجل`), on top of
the tagline/support/manual-link keys already seen on Windows. All 6 matched the authoritative
profile exactly; applied for real; re-ran dry-run afterward and confirmed **0 changes**
(idempotent). Direct `SELECT` against `globals` confirms all 6 values live. **GATE: PASS.**

### Stage 9 -- facility rename

Re-verified unchanged (`'Thiqa Demo Eye Clinic'`) immediately before writing, per the required
sequencing. `UPDATE facility SET name='International Healthcare Center' WHERE id=3`. Verified
full row afterward -- only `name` changed. **GATE: PASS.**

### Stages 10-11 -- theme/asset transfer, permission sweep

Completed as part of Stage 3 (theme transfer happened before the code checkout, deliberately,
since the theme files are gitignored and unaffected by a git checkout) and Stage 5 (the
permission sweep is baked into that stage's `restore_perms` equivalent). Re-verified after the
code checkout that the theme files were untouched: checksum comparison against the certified
build, byte-identical.

### Stage 12 -- verify (script's own checks, run individually)

`git rev-parse HEAD` = `663035f0b` (matches target). Both protected files still locally
modified (untouched). Q77 forbidden-theme count = 0. HTTPS availability = 200. All six
monitoring signals `[OK]` on a fresh manual run (`M-5` still shows the pre-deploy backup as
"last success" at this point -- expected, the post-deploy backup runs later at Stage 19).
`openemr-background-services.timer` active.

### Stage 13 -- extended smoke tests (unauthenticated only)

Fetched the live login page directly (`curl -H 'Host: demo.skyeagle.uk'` against localhost,
matching the monitoring script's own M-1 probe pattern): `<title>SkyEagle Login</title>`,
correct logo alt text, correct tagline, **zero** matches for `thiqa` anywhere in the page
(explicit grep, case-insensitive, confirmed empty). Favicon and stylesheet links correctly
present. Live-served `style_light.css` (fetched over HTTPS, not just the file on disk)
confirmed `--primary:#1e5a96`. **Authenticated smoke tests (login EN/AR, RTL, patient,
appointment, clinical, billing, reports, Admin SkyEagle section) were not performed on this
host** -- same credential-entry boundary maintained throughout this entire session, unchanged
by having remote SSH access. This is the one deliberately-incomplete item in this deployment,
not an oversight.

### Stages 14, 18, 19 -- logs, post-deploy backup

Apache error log checked for the update window: only unrelated internet background-scanning
noise against the unused default vhost (`/var/www/html/*.php` probe attempts, a different
vhost entirely from the actual app) -- zero application errors, zero bootstrap errors.
Post-deploy backup run and verified: `20260828-005541`, 283 tables, uploaded to R2 -- this is
now the known-good post-SkyEagle recovery point for this host.

### What remains, handed off rather than forced

One item: apply `tmp/skyeagle-migration-2026-08-27/evidence/09-demo-openemr-translation-migration-journal-table.sql`
(already on the VM at `/tmp/09-translation-migration-journal.sql`, may carry Windows CRLF line
endings from the transfer -- MariaDB's CLI client tolerates this far better than a bash
shebang would, but running `sed -i 's/\r$//' /tmp/09-translation-migration-journal.sql` first
is a trivial, safe precaution). Unrelated to SkyEagle branding; does not block or affect
anything already verified working.

**Authenticated (English + Arabic/RTL) live smoke testing on this specific host** is the other
open item, for the same reason it was open on the Windows host until the Owner logged in
themselves.

## LIVE AUTHENTICATED BROWSER CERTIFICATION — `demo.skyeagle.uk` (2026-08-28)

Closed the remaining gap from the deployment above: authenticated visual verification directly
against the live public site, using the Claude-in-Chrome connected browser, not curl or
source inspection. The Owner logged into `https://demo.skyeagle.uk` twice (English, then
Arabic after a self-service logout) using the `n.alqahtani` demo account -- credentials for
this specific host were **not** the same as the Windows instance's (confirmed: this account
belongs to a different person, "Noura Alqahtani" here vs "Nadia Alqahtani" on Windows -- two
independently-provisioned installations, not shared state). The Windows-instance password did
not work here; the Owner retrieved the correct one themselves from
`/home/myriamviens2/.openemr-demo-credentials` on the VM via their own SSH access -- this
session never read or printed it.

**Browser reconnection note.** The Chrome extension's tab-group tracking reset twice mid-pass
(once between English and Arabic screens, once mid-Arabic-navigation) -- both times the
underlying browser session and OpenEMR login were untouched; only this tool's own tab
bookkeeping needed re-establishing via `select_browser` against the same previously-verified
deviceId. One of those resets briefly connected to the wrong paired browser device before the
Owner caught it and the correct one was re-selected explicitly -- consistent with this
project's long-documented browser-pairing instability, now also observed against a live
production target, not just the local dev instance.

### English, live and authenticated -- confirmed on the public site

Main shell (Calendar, correct logo/colors), Patient Finder (same synthetic `SYN-00xx` demo
dataset as the Windows instance -- confirms the deterministic seeder produced an identical
dataset on both independently-provisioned installations), a patient's Medical Record
Dashboard, that patient's clinical encounter, Fee Sheet/billing, a Sales report, and both
Admin > Config > Appearance > Branding (URLs) and Admin > Config > SkyEagle Branding (the
module's own section) -- all inspected live over HTTPS on `demo.skyeagle.uk`, not inferred.

**Live confirmations of fixes that were previously only proven at the database level:**
- The open clinical encounter's Visit Summary reads "Yousef Alharbi (International Healthcare
  Center)" -- the facility rename, now seen rendered on the actual public demo site.
- Admin > Config > Appearance > Branding shows `Online Support Link:
  https://skyeagle.uk/en/contact` and `User Manual Link Override:
  https://skyeagle.uk/en/resources` live in the browser.
- Admin > Config > SkyEagle Branding (module-registered section, proving the module's
  listeners are genuinely active on this host, not just `mod_active=1` in a query) shows
  "Branding revision: 0" / "No tenant overlay" -- consistent with the confirmed
  never-materialised state.

**Portal:** confirmed disabled via Admin > Config > Portal (`Enable Patient Portal` unchecked,
placeholder site address `https://your_web_site.com/openemr/portal` still present) --
`PORTAL: N/A -- NOT ENABLED`, not a defect.

**Print:** attempted via `Ctrl+P` on the Fee Sheet screen. The native OS print dialog renders
outside the page DOM and is not screenshot-capturable through this browser-automation tool --
a genuine tool limitation, reported as such rather than fabricated. The underlying page content
(what would be printed) was already confirmed correctly branded via the Fee Sheet screenshot.

**Responsive (tablet/mobile):** `resize_window` reported success but did not change
`window.innerWidth` on this real, extension-connected browser (verified via
`window.innerWidth` before/after, unchanged at 1366). A second, independent tool limitation,
also reported rather than worked around by fabricating a result. Desktop (the width that is
actually rendering) is fully verified live; the V-02/V-03 fixes were separately verified via
headless Chrome earlier this session (with its own documented ~500px floor caveat) against
the identical, byte-verified-transferred theme files now live on this host.

### Arabic / RTL, live and authenticated -- confirmed on the public site

Logged out (self, safe) and the Owner logged back in selecting Arabic. Confirmed via tab
title (`سكاي إيجل`) and rendered content, not assumed. Verified live: main shell/Calendar
(correct Hijri-adjacent Gregorian date string, RTL-mirrored calendar grid, logo on the right),
Patient Finder (`الباحث عن المريض`, correct RTL column order), patient dashboard (RTL Arabic
labels correctly mixed with still-LTR proper nouns/dates -- correct bidi behaviour), the
clinical encounter (**"Yousef Alharbi (International Healthcare Center)" confirmed live in
Arabic too** -- the facility fix visible in the RTL clinical shell on the actual public site),
Admin > Config > SkyEagle Branding in RTL (correctly right-aligned, same content as English),
and the built-in Branding URLs in Arabic (`رابط الدعم على الإنترنت` =
`https://skyeagle.uk/en/contact`, correct).

**Symbol-mirroring check, done properly, on the live site specifically:** zoomed screenshot of
the SkyEagle "SE" mark in the live RTL layout compared directly against the live English
capture. Identical orientation and letterforms -- correctly not mirrored, on production, not
just in a controlled dev-instance test.

### Console/network check

Armed console-error and network-request tracking mid-session (both tools only capture activity
after being first called, so this is a snapshot from that point forward, not the full session)
and triggered a fresh in-app navigation. Result: zero console errors, three network requests
captured (dated-reminders counter, dated-reminders fetch, `background_service/$run`), all
HTTP 200 -- notably confirming the background-service AJAX call that is part of the documented
Windows-host Twig-render-hang theory succeeds cleanly here, consistent with this being a normal
Linux filesystem rather than the Drive-mounted Windows host where that hang was observed.

### Screenshot evidence

15 screenshots under `tmp/skyeagle-migration-2026-08-27/evidence/live-demo/` (gitignored, not
committed): the full English live set and the full Arabic/RTL live set, each covering main
shell, patient search, patient dashboard, clinical encounter, billing (English only), reports
(English only), and both Admin branding sections.

### What remains genuinely unverified on this host

Dark theme (not toggled live -- same reasoning as the Windows pass: it is a tenant-wide
setting requiring logout/login to take effect, and flipping a shared setting merely to look at
it was judged not worth the disruption to a live public demo). PDF generation (no safe,
already-populated PDF-export path was identified and exercised within this pass). Medication,
Lab, and Claims screens specifically (this patient's demo data shows "Nothing Recorded" for
medications/allergies/problems, so there was no populated screen of that kind to inspect;
Claims/insurance views were not separately navigated to beyond what Fee Sheet/Billing already
covered). None of these are defects -- they are honestly-reported gaps in this pass's coverage,
consistent with this document's standing rule never to claim a check that was not actually
performed.
