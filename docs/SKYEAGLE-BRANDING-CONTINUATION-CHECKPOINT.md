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
| B9-01 | `sites/default` tenant globals | Stale (old tagline, old URLs) | Synced to current profile | DATABASE VALUE | branding-profile.json | Yes | Yes — live write | GATE-4 | B9 | PLAN ONLY |
| B9-02 | `facility.name` (id 3) | Thiqa Demo Eye Clinic | International Healthcare Center | TENANT DATA | Owner instruction 2026-08-26 | Yes | Yes — live write | GATE-4 | B9 | PLAN ONLY |
| B9-03 | `sites/rdy0082restore` tenant | Unknown, not yet inspected | TBD | — | — | Yes | Yes — live write | GATE-4 | B9 | Not yet inspected |

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

## 13. Live database mutation state

```text
LIVE DATABASE MUTATED THIS PHASE: NO
```

Only read-only `SELECT` queries have been run against the live `openemr` database this session (§6).

---

*Checkpoint revision 5, updated after B5. Next update: after B6.*
