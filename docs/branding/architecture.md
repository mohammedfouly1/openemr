# Branding layer architecture — the canonical reference

**Deliverable F1** (`docs/RebrandingPlan.md` §7.1): "The five-plane model, extension-point map with
source citations, token contract, folder structure, configuration precedence, cache/revision model,
threat model. The canonical answer to *'how does branding work here?'*"

**Scope and method.** This document synthesises the design in `docs/RebrandingPlan.md` §3 with the
actual state of `interface/modules/custom_modules/oe-module-thiqa-branding/` on
`feat/thiqa-branding-foundation` (commits `a1c22b6a1`…`c6c3f9e6e`, confirmed committed via `git log
--oneline -10` at the time of writing). Every claim below is one of: (a) a direct file:line citation
from source read in this session, (b) a citation to `docs/branding/changes.md`,
`remaining-dependencies.md`, `coverage-matrix.md` or `multi-tenant-white-label-readiness.md` where that
document already carries the evidence (not re-derived here), or (c) explicitly marked as the plan's
stated intent where no corresponding code was found. Where the plan's design and the shipped code
differ, the difference is stated plainly rather than smoothed over — see §8, and the individual
callouts throughout. This follows the project's own corrections-register culture
(`docs/rebranding.md` §18): an unflagged discrepancy is a worse outcome than an admitted one.

**Read this alongside, not instead of:**

| Document | What it adds |
|---|---|
| `docs/branding/changes.md` (F2) | Per-BRAND-ID verification: what shipped, what didn't, with evidence |
| `docs/branding/remaining-dependencies.md` (F3) | The D-register, acceptance tests A1–A8, invariant checks V-01–V-10 |
| `docs/branding/coverage-matrix.md` | The 45-area discovery-vs-implementation matrix |
| `docs/branding/multi-tenant-white-label-readiness.md` (F4) | What a second tenant / full white-label needs |

---

## 1. The five-plane model

The design (`docs/RebrandingPlan.md` §3.2) splits the layer into five planes with one-directional
dependencies: an authority plane, a materialisation plane, a runtime-resolution plane, four runtime
attachment surfaces, and a shared build artefact. This section restates the model and marks, plane by
plane, what actually exists in the working tree versus what is still design intent.

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ PLANE 1 — AUTHORITY (Control Plane, PostgreSQL — MVP-014)                    │
│   saas_branding_profile · saas_branding_token · saas_branding_asset_ref      │
│   saas_branding_revision · saas_branding_materialisation_log                 │
│                                                                                │
│   STATUS: NOT BUILT (D-5). No such system exists yet. Confirmed live:        │
│   `thiqa-branding:verify --site=default` reports "never materialised /       │
│   revision 0" — there is nothing to be authoritative over yet.               │
│   (remaining-dependencies.md §4, D-5)                                        │
└───────────────┬──────────────────────────────────────────────────────────────┘
                │  out-of-band, tenant-scoped, idempotent  (NEVER on a request path)
                ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ PLANE 2 — MATERIALISATION (CLI, tenant-scoped)                               │
│   BrandingMaterialiser · QueryUtilsBrandingGlobalsWriter · TokenCssWriter ·   │
│   AtomicFileWriter · JsonFileTier1PaletteProvider · MaterialisationLogger ·   │
│   BrandingHealthCheck                                                        │
│   Commands (6): ApplyProfileCommand, VerifyCommand, MaterialiseCommand,      │
│   ProvisionReportAclCommand, BackupCommand, SeedDemoCommand — the last       │
│   three own gacl ACOs, ~19 demo tables and backup artefacts (see §8.4)       │
│   (Bootstrap::registerConsoleCommands(), on CommandRunnerFilterEvent)        │
│                                                                                │
│   STATUS: BUILT, isolated-tested, RUN LIVE then ROLLED BACK. Corrected       │
│   2026-08-24 (S2-P1-18): `materialise` HAS run against sites/default —       │
│   twice (RB-11, PRE-16) — and both runs were restored afterwards, so         │
│   verify reports revision 0 again. Read that as "no overlay is live",        │
│   NOT as "materialise never ran": the RB-11 stylesheets are still on         │
│   disk. `apply-profile` (Tier-1 globals) has run and was not rolled          │
│   back. (multi-tenant-white-label-readiness.md §1.5)                         │
└───────────────┬──────────────────────────────────────────────────────────────┘
                │  writes only into the tenant's own scope — SiteId + assertBoundTo()
                ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ PLANE 3 — RUNTIME RESOLUTION (in-request, read-only)                         │
│   BrandingService implements BrandingServiceInterface                        │
│   (src/Service/BrandingService.php)                                          │
│                                                                                │
│   STATUS: BUILT and live. Reads OEGlobalsBag (already loaded once per        │
│   request) + the filesystem. Zero network calls — enforced, not just         │
│   documented, by ForbiddenBrandingHttpClientRule (§7 below).                 │
└───────────────┬──────────────────────────────────────────────────────────────┘
                │
     ┌──────────┴───────────┬──────────────────────┬─────────────────────┐
     ▼                      ▼                      ▼                     ▼
┌───────────┐        ┌─────────────┐       ┌──────────────┐      ┌──────────────┐
│ PLANE 4a  │        │  PLANE 4b   │       │   PLANE 4c   │      │  PLANE 4d    │
│ HTML head │        │   Twig      │       │  Logo slots  │      │  Machine     │
│ Style-    │        │ overrides   │       │ Logo-        │      │  contracts   │
│ Injection │        │ (login vars │       │ Override-    │      │  (SMART)     │
│ Listener  │        │ + SMART     │       │ Listener      │      │              │
│ (E1)      │        │ template    │       │ (E3)          │      │              │
│           │        │ rewrite,    │       │              │      │              │
│           │        │ E4/E5/E6)   │       │              │      │              │
│  BUILT,   │        │  BUILT,     │       │  BUILT,      │      │  SPLIT — see │
│  live     │        │  live       │       │  live        │      │  §1.1 below  │
└───────────┘        └─────────────┘       └──────────────┘      └──────────────┘
                                    ▲
┌───────────────────────────────────┴──────────────────────────────────────────┐
│ PLANE 5 — SHARED IMMUTABLE BUNDLE (build artefact, identical for all tenants)│
│   public/themes/style_{light,dark}.css (+ compact/rtl/rtl_compact)           │
│   public/assets/fonts/thiqa/*.woff2                                          │
│                                                                                │
│   STATUS: BUILT. webpack.themes.js:160-173 confirmed — only style_light /    │
│   style_dark entries point at the Thiqa SCSS sources; solar/manila/          │
│   cobalt_blue/forest_green are absent from the entry map (grep, this         │
│   session). Fonts delivered by tools/branding/install-assets.php, NOT by     │
│   the sync-css.js step the plan names for this role — see §8.2.              │
└──────────────────────────────────────────────────────────────────────────────┘
```

**Dependency rule** (plan): Plane 3 may not reference Plane 1 or Plane 2 classes; Plane 2 may not be
reachable from any web entry point. Confirmed enforced: `ForbiddenBrandingHttpClientRule` (§7) flags
any HTTP client construction, `curl_*` call, or `file_get_contents()`/`fopen()` on an `http(s)://`
literal anywhere under `OpenEMR\Modules\ThiqaBranding\*` **except** the `\Materialisation` sub-namespace
(`tests/PHPStan/Rules/ForbiddenBrandingHttpClientRule.php:53-148`) — this is the Plane-3-cannot-reach-
Plane-1/2 rule expressed as a static-analysis gate, not a comment. Plane 2's own unreachability from a
web entry point is structural rather than rule-enforced: the six console commands are registered only
on `CommandRunnerFilterEvent`, which fires exclusively from `bin/console`
(`Bootstrap.php:132-135,147-195`); no controller or route constructs `BrandingMaterialiser`.

### 1.1 Plane 4d is two mechanisms, not one — and only one of them is wired

The plan describes Plane 4d ("machine contracts") as a single SMART style delivery mechanism: a
Twig-namespace template substitution (E4/E6, §2 below) that serves `smart-style_light.json.twig` /
`smart-style_dark.json.twig` in place of core's light-only template. That mechanism **is** built and
live — `TwigOverrideListener::onTemplatePage()` rewrites the SMART style page's template name to
`@oe-module-thiqa-branding/api/smart/smart-style_{light,dark}.json.twig` based on
`BrandingService::themeVariant()` (`src/Listener/TwigOverrideListener.php:137-167`), and both template
files exist on disk (`templates/api/smart/smart-style_light.json.twig`,
`…smart-style_dark.json.twig` — confirmed by directory listing).

Separately, `BrandingServiceInterface` also declares `smartStyleTokens(ThemeVariant $variant):
SmartStyleContract` (`src/Service/BrandingServiceInterface.php:75`), backed by
`src/Theme/SmartStyleContract.php`, deriving the same 12-key contract from the same `TokenSet` in PHP
rather than Twig. This is a **second, parallel implementation of the same concept**, and per
`remaining-dependencies.md` A8/§5(6): it "has no dedicated test anywhere in `tests/`" (only a stub
implementation in `StubBrandingService.php` for an unrelated listener test) and "no production
controller/route calls `smartStyleTokens()` — it is defined on the interface and implementation but
appears wired to nothing." The live SMART dark-style behaviour a browser or SMART app actually observes
comes entirely from the Twig-template path; `smartStyleTokens()`/`SmartStyleContract` is dead code from
a production standpoint today, not a bug in the Twig path. Treat "Plane 4d" as delivered by the Twig
mechanism; treat the PHP-side contract object as built-but-orphaned until it either gains a caller or a
direct unit test.

---

## 2. Extension-point map (verified against source)

Every attachment is a **published** OpenEMR seam. The table below restates the plan's map
(`docs/RebrandingPlan.md` §3.6) with each row re-verified against `Bootstrap.php` and the listener
classes it wires, in this session.

| # | Seam | Source (re-verified) | Listener | Verified behaviour |
|---|---|---|---|---|
| E1 | `StyleFilterEvent::EVENT_NAME` (`html.head.style.filter`) | `Bootstrap.php:104-107` | `StyleInjectionListener::onStyleFilter` (`src/Listener/StyleInjectionListener.php:69-100`) | Appends the Tier-2 token stylesheet URL to the existing style list, **only** when `BrandingService::tokenStylesheetUrl()` is non-null (i.e. a tenant overlay exists); never replaces the array; de-dupes if already present. Confirms plan §3.2.2's "when Tier 2 is empty, no `<link>` is added at all." |
| E2 | `ScriptFilterEvent` | Not referenced anywhere in module source (`grep -r ScriptFilterEvent src/` → 0 hits) | none | Confirmed genuinely unused, matching the plan's "reserved; not used." |
| E3 | `LogoFilterEvent::EVENT_NAME` (`logo.filter.url`) | `Bootstrap.php:109-117` | `LogoOverrideListener::onLogoFilter` (`src/Listener/LogoOverrideListener.php:111-181`) | Redirects a logo slot to a dark-variant mark shipped inside the module, **only** for `ThemeVariant::Dark` (light is left to core/`LogoService` unchanged) and **only** when the target web path resolves under `/interface/modules/` — `ModulesApplication::filterSafeLocalModuleFiles()` silently drops anything else, so the listener pre-checks and declines rather than emitting a path core would blank. Revision-stamped (`?rev=<cacheKey>`). |
| E4 | `TwigEnvironmentEvent::EVENT_CREATED` → namespaced `addPath()` | `Bootstrap.php:119-123` | `TwigOverrideListener::onTwigEnvironmentCreated` (`src/Listener/TwigOverrideListener.php:86-125`) | Registers the module's `templates/` directory under `Bootstrap::TWIG_NAMESPACE = 'oe-module-thiqa-branding'` via `addPath($dir, $namespace)` — **never** `prependPath()`, and never `addPath()` without the namespace argument. Both are structurally forbidden by `ForbiddenBrandingTwigPathRule` (§7). |
| E5 | `TemplatePageEvent::RENDER_EVENT` (login) | `Bootstrap.php:127-130` | `LoginTemplateListener::onTemplatePage` (`src/Listener/LoginTemplateListener.php:77-123`) | Merges `primaryLogoAlt`, `secondaryLogoAlt`, `brandProductName`, `brandTagline` into the login page's existing Twig variables (merge, never replace); each key dropped if empty; the array is untouched if nothing survives. Deliberately does **not** write `title`/`tagline`, which core already populates from `openemr_name`/`login_tagline_text`. |
| E6 | `TemplatePageEvent::class` (SMART/OAuth, unnamed dispatch) | `Bootstrap.php:125` | `TwigOverrideListener::onTemplatePage` (same class as E4, `:137-167`) | Rewrites only the page named `oauth2/authorize/smart-style` to the namespaced template for the current `ThemeVariant`, and only if that variant's template file actually exists on disk — else logs a warning and leaves core's own template selection in place. **Confirmed registered on both keys** (`TemplatePageEvent::class` at line 125 for E6, `TemplatePageEvent::RENDER_EVENT` at line 128 for E5) — the plan's warning that "a listener registered on only one will silently miss the other" is honoured by construction, via two separate listener registrations rather than one dual-purpose one. |
| E7 | `GlobalsInitializedEvent::EVENT_HANDLE` (`globals.initialized`) | `Bootstrap.php:107` | `GlobalsRegistrationListener::onGlobalsInitialized` (`src/Config/GlobalsRegistrationListener.php:65-76`) | Creates an Administration section `"Thiqa Branding"` and registers all 7 layer-owned globals (§4 below): the 3 tenant-name strings as ordinary editable text fields, and the 4 materialiser-owned values (`Revision`, `TokensLight`, `TokensDark`, `MaterialisedAt`) as **read-only HTML display sections with no input element at all** — so there is no text box anywhere in Administration a person could type CSS/JS/HTML into for those four keys. `library/globals.inc.php` is never touched. **Divergence from the plan's folder map**: this listener class lives in `src/Config/`, not `src/Listener/` — see §8.1. |
| E8 | `LogoService` per-site path (`OE_SITE_DIR/images/logos/<slot>/logo.*`, last-match-wins) | Core, unmodified; consumed via `BrandAssetResolver` (`src/Asset/BrandAssetResolver.php`, constructed in `Bootstrap.php:203`) | — | Per-tenant logo binaries need no new mechanism; a tenant override at `sites/<tenant>/images/logos/<slot>/` wins automatically. |
| E9 | `webpack.themes.js` entry map | `webpack.themes.js:160-173` (re-grepped this session) | — (build-time, fork-owned) | Only `style_light`/`style_dark` (+ `compact_style_*`, `rtl_style_*`, `rtl_compact_style_*`) resolve to `oe-styles/style_thiqa_{light,dark}.scss`; no `solar`/`manila`/`cobalt_blue`/`forest_green` entry exists. Matches `Q77` and is guarded by `BrandingGovernanceGuardTest` (`coverage-matrix.md` row 10). |
| E10 | Font/asset overlay build step | **Not `scripts/sync-css.js`** — that script only copies compiled CSS (`grep woff2 scripts/sync-css.js` → 0 hits; it copies files matching a CSS glob, confirmed by reading it this session). The actual mechanism is `tools/branding/install-assets.php` (SHA-256-manifested, per `changes.md`'s REPLACE-ASSET table and `coverage-matrix.md` row 12/85) | — | **Divergence from the plan** — see §8.2. |
| E11 | `globals` table rows | `interface/globals.php:457` (plan citation, not re-verified independently this pass) | `QueryUtilsBrandingGlobalsWriter` (`src/Materialisation/QueryUtilsBrandingGlobalsWriter.php`) | Materialisation target for `apply-profile`/`materialise`; the writer is constructed for exactly one `SiteId` and every method calls `assertBoundTo()` first (`multi-tenant-white-label-readiness.md` §1.2). |
| E12 | `lang_definitions`/`xl()` catalogue | — | — | All SET-TRANSLATION items route through here in design; in practice 5 of 8 are **NOT DONE** (`changes.md` SET-TRANSLATION table) — this seam exists and is unmodified, but is mostly unused by the current implementation. |
| E13 | Twig `getLogo(type, filename)` | `src/Common/Twig/TwigExtension.php:274-279` (plan citation) | — | Core, unmodified; template-level slot access. |

**Deliberately unused seams (confirmed):** `sites/<site>/config.php` is not merely avoided by convention
— `ForbiddenBrandingSiteConfigRule` rejects any string literal inside `OpenEMR\Modules\ThiqaBranding\*`
naming `config.php`, in whole or as a concatenated tail (§7). `grep "sites/.*config.php|SITE_CONFIG"`
against the module's `src/` returns zero matches (`remaining-dependencies.md` V-08).

**Prohibited mechanism, confirmed enforced.** The plan states `FilesystemLoader::prependPath()` is
prohibited and `addPath()` without an explicit namespace is prohibited (locked `Q38`/CR-17). Both are
structural rule violations, not just code-review guidance: `ForbiddenBrandingTwigPathRule` flags
`prependPath()` outright and flags `addPath()` calls with fewer than 2 arguments, scoped to the branding
namespace (`tests/PHPStan/Rules/ForbiddenBrandingTwigPathRule.php:79-109`).

---

## 3. Token contract

`TokenKey` (`src/Token/TokenKey.php`) is the closed allowlist the plan's §3.4.1 describes — but the
actual enum differs from the plan's category table in exact membership, so the counts below are read
from the enum itself rather than the plan's prose.

**43 cases total**, one dot-path per case (`brand.navy`, `interactive.primary.default`, etc.), each
mapping to exactly one literal CSS custom-property name via an exhaustive `match` with no `default`
branch (`TokenKey::cssVariableName()`, lines 97-143) — so a new case cannot ship without an author
deciding its CSS name, and PHPStan enforces exhaustiveness on every future edit.

| Group | Keys | Tenant-overridable | WCAG-gated |
|---|---|---|---|
| `brand.*` (navy, coral, coralDeep, sage, sky, amber, critical) | 7 | No — identity/trademark | No |
| Structural surfaces (`background`, `surface`, `surfaceRaised`, `surfaceSunken`, `surfaceInput`, `surfaceInputOnRaised`) | 6 | No — structural | No |
| Borders/dividers (`border`, `borderStrong`, `divider`) | 3 | No | No |
| Text (`text.primary/secondary/disabled/inverse`) | 4 | No | No |
| Semantic/clinical (`semantic.{success,warning,critical,info}.{bg,text,border}`) | 12 | **No — clinical safety, not negotiable per tenant** (docblock, `TokenKey.php:154-159`) | No (Tier 1 only) |
| Interactive (`interactive.primary.{default,hover,active,disabled,textOn}`, `interactive.secondary.{default,hover,textOn}`, `interactive.focusRing`) | 9 | **Yes — all 9** | 8 of 9 use WCAG gates; `disabled` instead uses the product distinguishability rule below |
| Links (`link.default`, `link.hover`) | 2 | **Yes** | Yes |

**Exactly 11 keys are `isTenantOverridable() === true`** (`TokenKey.php:164-212`) — the interactive and
link surfaces, and nothing else. This matches the count `multi-tenant-white-label-readiness.md` §1.6
already established. The clinical-safety non-overridability of the 12 semantic keys is enforced the same
way as everything else in this enum: `isTenantOverridable()` returns `false` for all 12 via an exhaustive
match, so there is no code path — not a validation rule, a structural absence — that could move them.

Exactly **10 of those 11** carry WCAG contrast rules. `interactive.primary.disabled` is intentionally the
sole exception because WCAG 2.2 exempts inactive controls from SC 1.4.3 and SC 1.4.11. It remains
tenant-overridable, but it is not unconstrained: `TokenValidator` requires at least **1.5:1 luminance
separation** from both `interactive.primary.default` and `background`. This is a product
distinguishability floor, not a WCAG threshold. Either a disabled-fill change or an enabled-primary change
re-runs it. The component layer also retains Bootstrap's fixed `$btn-disabled-opacity`; native disabled
semantics, the non-interactive pointer behaviour, and the fixed opacity do not depend on tenant colour.

**Validation gate.** `TokenValidator` (constructed with `ContrastCalculator`, `Bootstrap.php:171-172`)
is the boundary parser referenced throughout the plan; every overridable key that carries a
`contrastRule()` is checked against its paired background/foreground token via `ContrastCalculator`
before a Tier-2 overlay can be materialised. The same calculator supplies the separate disabled-state
product rule described above, without labelling its 1.5:1 floor as WCAG. **Divergence from the plan's folder map**:
`ContrastCalculator` lives in `src/Accessibility/`, not under `src/Token/` as §3.5.1 lists it — see §8.1.

**Rendering.** `CssVariableRenderer` (constructed in `Bootstrap.php:174` and again in
`public/branding-tokens.php:39`) is the only class that emits CSS text, and it accepts only typed
`DesignToken` objects — there is no string-passthrough path from a tenant-supplied value to a stylesheet
byte. This is the mechanism, not merely the intent, behind `Q76`/Invariant 9 ("no tenant-supplied CSS or
JS") — see §7.

---

## 4. Configuration precedence and the globals registry

**Precedence (unchanged from the plan, §3.4.3, and consistent with the shipped code):**

```
Tier-1 product token   (build-time, shared immutable bundle — Plane 5)
      overridden by
Tier-2 tenant overlay  (validated, allowlisted, revision-stamped — Plane 2/3)
      overridden by
Per-user theme choice  (existing user_settings mechanism — variant selection ONLY, never token values)
```

No per-site CSS, no `sites/<site>/config.php` participation (structurally forbidden, §7), and no
per-role branding (`multi-tenant-white-label-readiness.md` A7: zero ACL/role references anywhere in the
module's source).

**The globals registry.** `BrandingGlobalKey` (`src/Config/BrandingGlobalKey.php`) is a backed enum with
two disjoint groups, each with an exhaustive `definition()` match (lines 177-430) that is the single
source of truth for every key's type, label, description, editability and default:

- **33 inherited globals** (`BrandingGlobalKey.php:55-87`) — pre-existing OpenEMR globals the layer
  materialises but never registers (they already exist in `library/globals.inc.php`). Every default is
  a Thiqa value or an intentionally-safe blank, never an upstream identity value — the docblock is
  explicit that this is deliberate: a blank `main_menu_logo_title`/`user_manual_link` both make upstream
  auto-generate `open-emr.org` content, so both carry an explicit Thiqa default rather than a blank one.
- **7 layer-owned globals** (`BrandingGlobalKey.php:93-99`), every one prefixed `saas_branding_`
  (locked `Q58`): `Revision` (int, cache key, default 0/"never materialised"), `TokensLight`/`TokensDark`
  (validated JSON overlay, empty by default), `ProductNameArabic`, `TenantDisplayName`,
  `TenantDisplayNameArabic`, `MaterialisedAt` (ISO-8601). These match the plan's §3.4.2 table exactly by
  name and purpose.

`isLayerOwned()` derives ownership from the reserved prefix rather than a hand-maintained list
(`BrandingGlobalKey.php:108-111`), so a new case cannot be silently misclassified.

Two of the plan's inherited globals — `portal_onsite_two_address`, `portal_onsite_two_enable` — are
explicitly and deliberately **absent** from this enum (docblock, lines 19-21): they are
portal-enablement/provisioning settings, not branding, and the layer claims no authority over them. This
is why BRAND-070 and BRAND-064 remain outside the profile in `changes.md`'s findings — not an oversight,
a scope boundary drawn in the type itself.

---

## 5. Folder structure

### 5.1 What the plan specified (§3.5.1) vs. what shipped

The module tree exists at `interface/modules/custom_modules/oe-module-thiqa-branding/`, matching the
plan's install path and the composer package naming convention (`Q37`). The `src/` subtree, however,
has grown beyond the plan's original namespace list — four namespaces exist in the shipped code with no
corresponding entry in the plan's §3.5.1 tree, and two classes the plan placed in one namespace actually
live in a different one:

| Namespace (actual, `find src -type d`) | In the plan's §3.5.1 tree? | Note |
|---|---|---|
| `Config/` | Yes | Matches; also now holds `GlobalsRegistrationListener` (plan put it under `Listener/`) |
| `Token/` | Yes | Matches; `ContrastCalculator` moved out (see below) |
| `Theme/` | Yes | Matches; holds `ThemeVariant`, `ThemeResolver`, and `SmartStyleContract` (plan named this class `SmartStyleMapper` in its component inventory, §3.3.1) |
| `Asset/` | Yes | Matches — `LogoSlot`, `BrandAssetResolver`, `BrandingRevision` |
| `Service/` | Yes | Matches — `BrandingService`, `BrandingServiceInterface` |
| `Materialisation/` | Yes | Matches |
| `Listener/` | Yes | Matches, minus `GlobalsRegistrationListener` (see above) |
| `Twig/` | Yes | Present as a namespace directory (plan expected `BrandingTwigExtension` here) |
| `Console/` | Yes | Matches — `ApplyProfileCommand`, `VerifyCommand`, `MaterialiseCommand` (plan's component inventory instead named `materialise, verify, export-tokens, diff` — no `ApplyProfileCommand`, no `export-tokens`, no `diff` exist; `ApplyProfileCommand` isn't in the plan at all) |
| `Accessibility/` | **No** | Holds `ContrastCalculator` — the plan's §3.5.1 lists this class under `Token/` |
| `AssetIntake/` | **No** | Holds `LogoValidator`, `SvgInspector`, `RasterImageReader`, `ImageDimensions`, `ImageFormat`, `AssetRejectionReason`, `AssetInspectionException`, `ValidatedAsset` — an entire tenant-logo security-validation subsystem (§7 below) with no corresponding entry anywhere in the plan's folder structure or component inventory |
| `Observability/` | **No** | Holds `BrandingHealthCheck`, `FilesystemStylesheetProbe`, `MaterialisationLogger` — backs the `verify` command's output; not in the plan |
| `Tenant/` | **No** | Holds `SiteId`, the tenant-identity value object used throughout the module (`multi-tenant-white-label-readiness.md` §1.2); not in the plan |

None of this is a red flag on its own — `AssetIntake` and `Accessibility` in particular are
well-motivated, security-relevant subsystems that simply didn't exist as separate concerns when the plan
was written. It is recorded here because the plan's folder structure is presented as a specification
("D1.6 Folder structure + naming... validated against Q37/Q38/Q58", §3.11) and a maintainer diffing the
plan against the tree should not have to rediscover four extra namespaces and two relocated classes
independently.

### 5.2 Templates: the module ships fewer templates than the plan describes

Plan §3.5.1 lists three template overrides under `templates/`:

```
templates/
├── api/smart/smart-style_light.json.twig    # core-path override (tokenised)
├── api/smart/smart-style_dark.json.twig     # core-path override (NEW — R-SMART-DARK)
└── login/partials/html/primary_logo.html.twig  # core-path override (alt text, CR-8)
```

A directory listing of the shipped `templates/` tree (`find … -type f`, this session) returns only the
first two:

```
templates/api/smart/smart-style_dark.json.twig
templates/api/smart/smart-style_light.json.twig
```

**`templates/login/partials/html/primary_logo.html.twig` does not exist in the module.** This is not an
oversight this document is newly discovering — `changes.md` (BRAND-053) and `coverage-matrix.md` (row 7)
already found and explained it: the module's own copy of this template was **dead code**, unreachable
because of where core's Twig `{% include %}` boundary sits, and the actual fix that shipped edits the
**core** template directly — `templates/login/partials/html/primary_logo.html.twig:15,20` (the tracked,
upstream-owned file) now reads `alt="{{ primaryLogoAlt|default('')|attr }}"` instead of a hardcoded
empty string, with the value supplied by `LoginTemplateListener` (E5, §2 above).

**Correction to an earlier draft of this section.** That draft cited only plan §1.0.1 ("both must be
delivered as branding-module template overrides") as CR-8's approval and characterised the shipped core
edit as a violation of it. That is wrong: §1.0.1 is the *original* 2026-08-09 ruling, but the same
document's §1.0.2 ("External audit of this register") records that this exact mechanism was
**"withdrawn and replaced"** for CR-8 that same day, and the plan's detailed CR-8 entry (line 124)
is explicit about it: *"The unnamespaced-shadow route is withdrawn... Recommended instead: a one-line
upstream-first change to `primary_logo.html.twig` reading `{{ primaryLogoAlt|default('') }}`... genuinely
upstream-worthy (an accessibility fix), and no template duplication."* Plan line 934 shows this as a
struck-through revision in its own patch inventory: `~~Module template override (E4) — CR-8~~` →
`core edit`. **The shipped mechanism — a narrow, tokenised core edit — is exactly what the plan's own
revised CR-8 recommends, not a departure from it.** This is a real example of the plan document itself
containing two dated-the-same-day layers (an initial ruling, then a same-day audit revision), and it is
worth reading §1.0 through §1.0.2 together rather than citing §1.0.1 alone when checking any CR-item's
current status. See `changes.md` row BRAND-053 for the full root-cause account of *why* the module
template turned out to be dead code, which is what originally motivated the revision.

### 5.3 What matches the plan without qualification

- `openemr.bootstrap.php` + `src/Bootstrap.php` as the sole wiring point — confirmed, and it is the only
  place the module touches global state (`Bootstrap.php` docblock, lines 4-9).
- `interface/themes/oe-styles/style_thiqa_{light,dark}.scss` as new, fork-owned entry files, upstream
  `style_light.scss`/`style_dark.scss` untouched — confirmed via `webpack.themes.js:160-173`.
- `interface/themes/thiqa/` partials tree (tokens, typography, css-variables, overrides) — confirmed
  present (`coverage-matrix.md` row 77: 7 files).
- `public/branding-tokens.php` as the CR-19-recommended token-CSS delivery endpoint (option (a): no
  writable directory, `Cache-Control: public, max-age=31536000, immutable` when a tenant overlay exists,
  `no-store` with an empty body otherwise) — confirmed by direct read, §6 below.

---

## 6. Cache and revision model

The plan's canonical cache-key table (§3.8.1) is reproduced here with each row's live mechanism cited
against the actual delivering code, not just restated:

| Asset | URL form | Verified against |
|---|---|---|
| Theme CSS (shared bundle) | `…/public/themes/<file>.css?v=<v_js_includes>` — no tenant revision, identical for all tenants | Core, unchanged; not independently re-verified this pass (plan cites `interface/globals.php:479-480`) |
| Tier-2 tenant token CSS | `…/oe-module-thiqa-branding/public/branding-tokens.php`, `Cache-Control: public, max-age=31536000, immutable` when a tenant overlay exists; `no-store` + empty body otherwise | Confirmed by direct read, `public/branding-tokens.php:77-87`. The immutability rests on the caller appending `?rev=<n>` to the URL (built by `BrandingService::tokenStylesheetUrl()` and consumed by `StyleInjectionListener`, E1) — the endpoint itself does not need to branch on the revision, since a new revision is a new URL by construction |
| Logos (dark-variant override) | `<override path>?rev=<branding_revision>` | Confirmed, `LogoOverrideListener.php:174-181` — the core `?t=<mtime>` cache-buster is a `LogoService` concern for the *default* (light) path; the dark override URL as built here carries only `?rev=`, appended after the resolved asset path |
| Favicon | `?t=<mtime>` via `LogoService` | Core, unchanged; not independently re-verified this pass |
| SMART style JSON | Uncached by OpenEMR; served via the Twig-namespace rewrite (E6) | The response itself carries no cache header search performed in this pass; `logo_primary`'s revision-stamping inside the SMART payload was not independently re-checked here (see `remaining-dependencies.md` A8 for the wider caveat about this contract's test coverage) |

**Rules, confirmed structurally rather than by convention:**

1. **Never remove or overwrite a core cache parameter; only append.** `StyleInjectionListener` reads the
   existing `$styles` array from the event and appends one element (`StyleInjectionListener.php:88-99`)
   — it never reassigns or filters the array, so no other module's contribution or core's own entries
   can be dropped by this listener.
2. **No URL may carry a tenant identifier, site name, or topology-disclosing value.** The token
   stylesheet endpoint takes no `site` query parameter at all (`branding-tokens.php` docblock, lines
   43-56) — tenant scope comes from the same session/host resolution every other unauthenticated
   OpenEMR entry point uses (`$ignoreAuth = true`, then `globals.php` resolves site normally), so the
   endpoint itself cannot be used to switch tenant context.
3. **The revision is monotonic and tenant-scoped**, sourced from `globals.saas_branding_revision`
   (`BrandingGlobalKey::Revision`, default `0`, meaning "never materialised" — confirmed live via
   `thiqa-branding:verify --site=default`, `remaining-dependencies.md` §4 D-5).

**What is unverified, stated plainly rather than assumed:** the plan's 8-case acceptance matrix
(upgrade, revision bump, two tenants at equal revision, rollback, CDN keying, reordered parameters,
empty Tier 2, logo replaced without a bump) has not executed against a live revision — because no
revision has ever been materialised for the one tenant that exists (`remaining-dependencies.md` V-02/
V-03/§4 D-5, D-6). The mechanism is source- and isolated-test-verified; the cache behaviour has not been
observed end-to-end against real HTTP responses in this environment.

---

## 7. Threat model

The plan's own security-model table (`docs/RebrandingPlan.md` §3.9) states seven controls at the level
of intent. This section restates each one anchored to the actual enforcement mechanism — a PHPStan rule,
a validator class, or a test — so the threat model describes what is checked, not what was planned to be
checked.

### 7.1 No tenant-supplied CSS or JavaScript reaches a rendered page (locked Invariant 9)

**Structural, not policy.** `TokenKey` is a closed backed enum (43 cases, §3 above) — there is no code
path that turns an arbitrary tenant-supplied string into a `TokenKey`; `TokenKey::tryFrom()` on anything
outside the declared cases returns `null`. `CssVariableRenderer` is the *only* class that emits CSS
text, and its input type is `DesignToken`, never a raw string — so the renderer can express nothing but
`--known-name: #RRGGBB;` pairs. `TokenValidator` additionally enforces `^#[0-9A-Fa-f]{6}$` on every
Tier-2 value before it can be materialised (§3 above), which forecloses `expression()`, `url()`,
`</style><script>`, and every other CSS-context escape at the value level, not just the key level.

### 7.2 No tenant-side network reachability from the runtime plane (locked Q76 / constraint C5)

Enforced by `ForbiddenBrandingHttpClientRule`
(`tests/PHPStan/Rules/ForbiddenBrandingHttpClientRule.php`), which flags, anywhere under
`OpenEMR\Modules\ThiqaBranding\*` **except** the `\Materialisation` sub-namespace:

- construction of or static calls into any class matching `GuzzleHttp\Client*`,
  `Symfony\Component\HttpClient\*`, `Symfony\Contracts\HttpClient\*`, `Psr\Http\Client\*`, or
  `OpenEMR\Common\Http\*` (imports, `new`, and static calls are all checked — lines 99-124, 201-211);
- any `curl_*` function call (line 218-220);
- `file_get_contents()`/`fopen()` where the first argument statically resolves to an `http(s)://` literal
  — including through string concatenation and interpolation, by walking to the leftmost literal
  fragment (lines 238-263).

This rule has a passing, independently-run test (`A4` in `remaining-dependencies.md`: 54/54 tests, 80
assertions, `phpunit-isolated.xml tests\Tests\Isolated\PHPStan\ThiqaBranding`).

### 7.3 No re-introduction of the per-site `config.php` seam (constraint C1, BRAND-120)

`ForbiddenBrandingSiteConfigRule` (`tests/PHPStan/Rules/ForbiddenBrandingSiteConfigRule.php`) rejects
any string literal under `OpenEMR\Modules\ThiqaBranding\*` matching `(?:^|[/\\])config\.php(?:$|[?#])`,
case-insensitively, whether written whole (`sites/default/config.php`) or as a constructed path tail —
scoped to the branding namespace only, so core's own legitimate reads of `sites/*/config.php` are
unaffected. Confirmed by its own passing test:
`ForbiddenBrandingSiteConfigRuleTest` → `OK (10 tests, 12 assertions)`
(`changes.md`, BRAND-120 row).

### 7.4 No unnamespaced Twig path shadowing (locked Q38 / CR-17)

`ForbiddenBrandingTwigPathRule` (`tests/PHPStan/Rules/ForbiddenBrandingTwigPathRule.php`) rejects
`FilesystemLoader::prependPath()` outright, and rejects `addPath()` called with fewer than two arguments
(i.e. without an explicit namespace), scoped to the branding namespace and matching both instance and
static call forms so a fatal-but-plausible-looking static-call bypass attempt is still caught rather than
silently ignored (lines 43-48, 81-109).

### 7.5 No placeholder or upstream-brand endpoint ships in production config (WP-2.7g)

`ForbiddenBrandingPlaceholderDomainRule`
(`tests/PHPStan/Rules/ForbiddenBrandingPlaceholderDomainRule.php`) rejects the literal `thiqa.example`,
the literal `reg.open-emr.org`, and any RFC 2606 `.example` host (e.g. `cp.thiqa.example`) appearing in
non-test code under the branding namespace — `Tests` segments in the namespace path are explicitly
exempted (lines 106-121), so fixtures can use placeholder hosts freely.

Taken together, §7.2–7.5 are the four rules `remaining-dependencies.md` A4 confirms are "present and
tested: 54/54 tests, 80 assertions, OK." This is the concrete backing for the plan's Plane-boundary
diagram note ("enforced by a PHPStan rule, §4.3 WP-2.7") — four separate rules, not one, each targeting
a distinct seam.

### 7.5a All four rules are scoped by a constant, and that constant is now cross-checked

Every one of the four decides whether it applies by comparing PHPStan's `Scope::getNamespace()` against a
private `MODULE_NAMESPACE` constant. That makes the rules' reach a piece of **duplicated configuration**:
rename the namespace the module ships under and miss the constants, and all four keep loading, keep
running, match nothing, and report `0 errors` — indistinguishable from compliance.

Finding **S1-P1-04** recorded that nothing checked the two against each other, and that
`ThiqaBrandingRuleRegistrationTest` cannot: its own docblock says it proves wiring, not matching. The
fixtures could not close it either, because they declare the same literal, so rule, fixture and
expectation agreed with each other by construction.

`ThiqaBrandingGuardrailScopeTest` closes it. It locates the module by a brand-neutral anchor
(`src/Config/BrandingGlobalKey.php`, whose `saas_branding_` prefix locked decision Q58 forbids renaming),
derives the production namespace from that module's own PSR-4 autoload prefix, checks all 92 shipped
source files declare it, and asserts each rule constant equals it. A rename in either direction — module
without constants, or constants without module — fails deterministically and names the rules that would
have gone inert. It runs inside `composer branding-ci`.

### 7.6 Tenant-supplied logo binaries: the last gate before same-origin bytes

The plan's threat table names this control abstractly ("content-type + magic-byte + dimension
validation... logos land in the site image directory, which serves static files only"). The actual
implementation is `LogoValidator` (`src/AssetIntake/LogoValidator.php`), which:

- **Runs unconditionally**, even though the Control Plane is expected to validate the same file — the
  class docblock states this duplication is deliberate: "a compromised or buggy CP cannot push a hostile
  asset into a tenant's site directory" (lines 21-32).
- **Trusts none of**: the filename extension (compared against sniffed content, never used to decide
  format), any client-supplied MIME type (not accepted as a parameter at all), or the fact that an
  upstream system already approved the file.
- Stats and caps the file **before** reading it into memory (128 KiB logos, 32 KiB favicon — sized to
  clear the largest certified asset with headroom, `LogoValidator.php:36-52`), rejects empty/too-small
  files, detects format from byte signatures (not extension), and rejects ambiguous multi-signature
  polyglots (`detectFormat()`, lines 205-226).
- Enforces per-slot format constraints — e.g. the core favicon slot is requested by literal filename
  `favicon.ico`, so a validated PNG placed there would simply never be served; the validator refuses it
  rather than let a tenant believe their favicon applied (`checkSlotAcceptsFormat()`, lines 228-257).
- For SVG specifically, delegates to `SvgInspector` (below) rather than treating an SVG as an opaque
  image.
- Logs every acceptance and rejection with a severity keyed to how hostile the reason is
  (`AssetRejectionReason::isHostile()`), and the log message never echoes attacker-controlled bytes back
  — element/attribute names are clipped and character-filtered before being interpolated into a log
  message (`safeName()`, `SvgInspector.php:407-413`).

### 7.7 SVG intake: allowlist, not blocklist

`SvgInspector` (`src/AssetIntake/SvgInspector.php`) treats an SVG as "a program's input," per its own
docblock, not a picture — because inside one, `<script>`, an `onload=` handler, a `<foreignObject>`
carrying arbitrary HTML, an `<image>`/`xlink:href` pointing off-origin, or a DOCTYPE with local-file or
exponential-expansion entities can all run with the tenant's session once served same-origin. Three
independent layers, all confirmed by direct reading of the source:

1. **Byte-level pre-checks before any parser touches the input** (`assertNoForbiddenBytes()`, lines
   156-175): rejects embedded NUL bytes, any `<!DOCTYPE` or `<!ENTITY` occurrence (case-insensitive),
   and any `<?php` open tag.
2. **A libxml parse with network access and external entity resolution both disabled**
   (`parse()`, lines 185-206): `LIBXML_NONET` blocks retrieval; a null-returning external entity loader
   (`libxml_set_external_entity_loader(static fn (): null => null)`) means even a resolver that ignored
   `NONET` gets nothing back. `LIBXML_NOENT` is deliberately *not* passed — despite the name, it turns
   entity substitution **on**.
3. **A depth-capped (64), fully-typed DOM walk** (`walk()`/`vetElement()`/`vetAttribute()`, lines
   216-350) that:
   - allowlists exactly 17 elements (`svg`, `g`, `defs`, `metadata`, `title`, `desc`, `path`, `rect`,
     `circle`, `ellipse`, `line`, `polyline`, `polygon`, `clipPath`, `linearGradient`, `radialGradient`,
     `stop`) — notably **`style` is not on the list**, on the stated reasoning that it is "a text node
     whose content is a second language with its own `url()` fetches and historic `expression()`
     execution," and nothing certified needs it since fills are attributes on paths;
   - names 14 specific threats with dedicated rejection reasons (`script`, inline event handlers,
     `foreignObject`, `<image>`/`<use>`/`<iframe>`/`<embed>`/`<object>`/`<audio>`/`<video>` as
     external-reference vectors, the `animate*`/`set` SMIL family, and `style` itself) rather than a
     generic "not allowlisted" message, so an operator report names the actual attack;
   - rejects any `on*`-prefixed attribute (covers `onload`, `onclick`, `onmouseover`, `onbegin`,
     `onerror`, and any future addition by pattern, not enumeration);
   - permits `href`/`xlink:href` only as a same-document fragment reference (`#id`), and rejects
     `src`/`srcset`/`formaction`/`action`/`ping`/`data` attributes outright;
   - strips whitespace/control characters before matching `javascript:`, `vbscript:`,
     `data:text/html`, `expression(`, `@import` against attribute values — closing the
     `"java\nscript:"`/`"java&#9;script:"` whitespace-smuggling class of bypass;
   - permits `url(...)` only in the exact same-document paint-server shape `url(#id)`, so
     `fill="url(#gradient)"` keeps working while `fill="url(https://evil.invalid/x)"` is refused;
   - tolerates foreign-namespace content **only** inside `<metadata>` (where the certified masters embed
     a C2PA provenance manifest, per `coverage-matrix.md` row 38) — anywhere else, foreign-namespace
     markup is treated as either an XHTML injection or a namespace-confusion attempt and rejected.

This is a materially more detailed and more defensive implementation than the plan's one-line "SVG
validation" mention implies — it is worth a reader's attention as one of the module's most carefully
reasoned components, not a routine input check.

### 7.8 Materialisation atomicity (partial application)

`QueryUtilsBrandingGlobalsWriter::writeAll()` wraps the globals delta, the `MaterialisedAt` timestamp,
and the revision bump in a single `QueryUtils::inTransaction()` call, with the revision upsert
**explicitly last** — "a database that flushes partially cannot expose a revision newer than its own
data" (`multi-tenant-white-label-readiness.md` §1.3, citing the writer's own docblock). Cross-tenant
isolation is enforced by construction: the writer is bound to exactly one `SiteId` at construction time
and every read/write method calls `assertBoundTo()` first, throwing `LogicException` on a mismatch with
a deliberately generic message that never interpolates the target site's name (so a cross-tenant attempt
does not leak the target site's identity into a log another tenant might read) — same source, §1.2. This
guard is unit-tested against fakes but, per `multi-tenant-white-label-readiness.md` §2.4, has never been
exercised against two real, independently-bootstrapped database connections in this repository's
history — the structural check exists; the integration proof of it does not yet.

### 7.9 What the threat model does not yet cover

Stated plainly rather than left implicit: the Control Plane (Plane 1) does not exist (§1 above), so
none of the CP-side controls the plan's table lists ("allowlist + format + contrast validation... is
re-run tenant-side... the tenant does not trust the CP blindly") have a CP to be defended against yet —
the tenant-side re-validation described in §7.6/§7.7 is real and independently sufficient today, but it
is currently the *only* layer, not a second layer behind a CP that also validates. Cross-tenant bleed
(§7.8) is source-proven but not integration-tested with two live tenants (D-6, unresolved). Both gaps
are already tracked in `remaining-dependencies.md`'s D-register and are not re-litigated here.

---

## 8. Divergences from the plan — consolidated

Individually noted above; collected here for a reader who wants the full list in one place.

| # | Plan said | Code does | Where |
|---|---|---|---|
| 8.1 | `ContrastCalculator` lives under `Token/` (§3.5.1) | Lives in `src/Accessibility/ContrastCalculator.php`, a namespace the plan's folder tree never mentions | §3, §5.1 |
| 8.2 | The SMART style mapper class is named `SmartStyleMapper` (§3.3.1 component inventory) | The shipped class is `src/Theme/SmartStyleContract.php` | §1.1, §5.1 |
| 8.3 | `GlobalsRegistrationListener` lives under `Listener/` alongside the other four listeners (§3.5.1) | Lives in `src/Config/GlobalsRegistrationListener.php` | §2 (E7), §5.1 |
| 8.4 | Console commands are `materialise, verify, export-tokens, diff` (§3.3.1); folder tree names `MaterialiseCommand · VerifyCommand · ExportTokensCommand` (§3.5.1) | **Six** commands exist, not three and not the plan's four: `ApplyProfileCommand`, `VerifyCommand`, `MaterialiseCommand`, `ProvisionReportAclCommand`, `BackupCommand`, `SeedDemoCommand` (`Bootstrap.php` registers all six on `CommandRunnerFilterEvent`). No `export-tokens`, no `diff`. *(Corrected 2026-08-24, S1-P2-07/S1-P2-16: this row previously said "Three commands exist".)* **The three unlisted ones own the most persisted state in the module** — gacl ACL objects, ~19 clinical/demo tables and backup artefacts — and none of it is branding state. The module has accreted a deployment/ops toolkit under a branding namespace, so "rename the branding module" has a materially wider blast radius than either the plan or the earlier version of this row implied | §5.1 |
| 8.5 | Font/asset overlay delivered by the `scripts/sync-css.js` "sibling step" (E10, §3.6) | Fonts are delivered by `tools/branding/install-assets.php` with SHA-256 manifest verification; `sync-css.js` only copies compiled CSS and contains no font-handling code | §1 (Plane 5), §2 (E10) |
| 8.6 | The plan's own folder-tree diagram (§3.5.1) still shows `primary_logo.html.twig` as a module-namespace override, matching CR-8's *original* §1.0.1 ruling | **Not a code divergence — the plan's decision text superseded its own diagram.** §1.0.2 ("External audit...") withdrew the module-override mechanism for CR-8 that same day, and the plan's detailed CR-8 entry (line 124) explicitly recommends the core edit that shipped. The folder-tree diagram in §3.5.1 was simply never updated to match the later revision. See the correction in §5.2 above for the full citation chain | §5.2 |
| 8.7 | `BrandingServiceInterface::smartStyleTokens()`/`SmartStyleContract` is presented as *the* mechanism the SMART contract flows through (§3.3.1) | It exists and is logically sound but has no test beyond a stub and no production caller; the actual live SMART delivery is the separate Twig-template-rewrite mechanism (E4/E6) | §1.1 |
| 8.8 | Four `AssetIntake`-, `Observability`-, `Tenant`-shaped concerns are not named anywhere in the plan's component inventory or folder structure | A full tenant-logo security-validation subsystem (`AssetIntake/`), a health-check/verify backing (`Observability/`), and the tenant-identity value object (`Tenant/`) all exist as first-class namespaces | §5.1, §7.6, §7.7 |
| 8.9 | `BrandingServiceInterface` is quoted verbatim in plan §4.2 (a **Phase 1 deliverable**, D1.3) with domain-primitive signatures: `productName(?Language $language = null): ProductName`, `tagline(?Language): ?Tagline`, `tokenStylesheetUrl(): ?BrandingUrl` | The shipped interface uses `productName(bool $arabic = false): string`, `tagline(bool $arabic = false): ?string`, `tokenStylesheetUrl(): ?string`. `Language`, `ProductName`, `Tagline` and `BrandingUrl` were never created | §4.2 vs `src/Service/BrandingServiceInterface.php` |
| 8.10 | `interface/themes/thiqa/` contains five partials (§3.5.3) | Seven exist: the four GENERATED ones plus `_overrides.scss`, and additionally the hand-authored `_theme-colors.scss` (27 lines, Bootstrap `$theme-colors` map) and `_bootstrap-bridge.scss` (211 lines, token→Bootstrap-4 variable bridge). **Corrected in the plan on 2026-08-10** (RB-20); recorded here so the as-built tree and the plan agree | §3.5.3 |

### 8.9 in detail — the interface contract deviation, and why it was accepted rather than reverted

This one is worth more than a table row, because `BrandingServiceInterface` is the project's own stated
public API — everything else in the codebase is supposed to depend on **this interface only** (principle
P1), and the plan quotes it verbatim as a Phase 1 deliverable.

**The deviation is real and was undocumented until now** (`docs/RebrandingBugs.md` RB-15). The plan's
signatures follow `CLAUDE.md`'s "Domain Primitives" rule; the shipped ones use `string` and a boolean flag.
`tagline(bool $arabic)`'s own docblock concedes the parameter "currently selects nothing", which is a
textbook boolean trap: `tagline(true)` reads as nothing at the call site.

**Accepted, with the reasoning stated rather than assumed.** The BRAND inventory defines exactly one
tagline global and one Arabic product-name global, so `Language`/`Tagline`/`ProductName` would be ceremony
around a two-valued choice — and `BrandingUrl` would wrap a string that is only ever concatenated into an
`href`. Reverting a published interface late, purely for shape, would touch the interface, the
implementation, the stub and every caller for no behavioural gain.

**The one thing that would change this judgement:** a second Arabic-varying value (a tagline, a legal
name, a support string). At that point `bool $arabic` stops being a two-valued choice and a `Language`
unit enum with an exhaustive `match` becomes the correct model — roughly 30 lines, and the natural moment
to do it. `LoginTemplateListener` already reasons in exactly those terms (`$arabic = $this->branding->isRtl()`),
so the call sites are ready for it.

None of these are regressions from the plan's *intent* — the security/observability/tenant-scoping
concerns are, if anything, more thoroughly built than the plan specified. The pattern across 8.1–8.4 and
8.8 is organic growth during implementation that the plan's folder map was never updated to reflect; 8.5
and 8.6 are places where the actually-shipped delivery *mechanism* differs from what was specified
(and, for 8.6, from what was specifically approved); 8.7 is a built-but-unused code path that reads, on
a first pass, as though it were load-bearing.

---

## 9. Cross-references

- **What shipped, BRAND-ID by BRAND-ID, with evidence:** `docs/branding/changes.md` (F2)
- **Open dependencies, acceptance tests, invariant checks:** `docs/branding/remaining-dependencies.md` (F3)
- **45-area discovery-vs-implementation matrix:** `docs/branding/coverage-matrix.md`
- **Multi-tenant/white-label gap analysis:** `docs/branding/multi-tenant-white-label-readiness.md` (F4)
- **Design source this document verifies against:** `docs/RebrandingPlan.md` §3 (Phase 1 architecture)
- **Locked decisions this architecture must not contradict:** `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md`
