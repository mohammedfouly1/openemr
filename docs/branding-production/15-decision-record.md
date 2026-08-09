# 15 — Branding Decision Record

**Date:** 2026-08-09
**Authority:** decisions taken by the project owner in response to the conflict register in
`docs/RebrandingPlan.md` §1.
**Effect:** closes the Group 2 pre-implementation decision gate. Six items are **applied in this
revision**; two are **approved scope** awaiting the branding module (Phase 2); three remain **open** and
are tracked as dependencies.

None of these decisions amends `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md`
(Q1–Q77) or the implementation backlog. They are implementation-level choices made **within** the locked
architecture, so the governance manifest
`Locked Desicions/OpenEMR-SaaS-Decision-Documents-SHA256-UPDATED-2026-08-09.txt` is unaffected and
remains valid — re-verified at the time of writing (see §5).

---

## 1. Decisions applied in this revision

| ID | Decision | Applied to |
|---|---|---|
| **CR-1** | Correct the wrong `docs/rebranding.md` section references in the string-replacement map | `14-string-replacement-map.md` — new *Cross-reference correction* table; Parts 2 and 3 headings |
| **CR-2** | Correct the Part 1 row-count heading and audit related references | `14-string-replacement-map.md` — heading now reads *"35 rows: the 33 branding-relevant globals … plus 2 portal-enablement globals"*, with a note explaining both figures |
| **CR-3** | **Never store an `rtl_`-prefixed theme filename in `globals`.** Store `style_light.css` for all languages and let OpenEMR perform the RTL substitution | `14-string-replacement-map.md` rows 25 and 28 rewritten; new *RTL note* records the withdrawn instruction and the runtime evidence |
| **CR-9** | Keep the CSS filenames `style_light.css` / `style_dark.css`; use *Saudi Light* / *Saudi Dark* as product-facing labels only | `14-string-replacement-map.md` — new *Theme filename note*; `docs/RebrandingPlan.md` §3.7.2 / CR-9 |
| **D-1** | Adopt the proposed contrast correction: `light.link.default` → `#2C5F94`, `light.link.hover` → `#1E4574` | `brand/tokens/thiqa-tokens.json`; `brand/qa/wcag-contrast-results.json`; `08-wcag-contrast.md` (revision 2); `13-final-qa-matrix.md`; `FINAL-GROUP-1.5B-CERTIFICATION.md` |
| **D-2** | Production domain is **`skyeagle.uk`**; all `thiqa.example` placeholders replaced | `14-string-replacement-map.md` Parts 1, 2 and 6 |
| **Hashes** | Re-issue fingerprints after the above changes | `brand/manifests/SHA256SUMS`, `asset-manifest.json`, `asset-manifest.csv`; `11-asset-manifest.md`; `12-release-verification.md` |

### 1.1 D-1 in detail

| Token | Before | After | Ratio on `#FAFAF8` | Ratio on `#FFFFFF` | Criterion |
|---|---|---|---:|---:|---|
| `light.link.default` | `#3E7FBD` (4.04 / 4.22 — **FAIL**) | **`#2C5F94`** | 6.34 | 6.62 | SC 1.4.3 (≥ 4.5:1) |
| `light.link.hover` | `#2C5F94` | **`#1E4574`** | 9.31 | 9.73 | SC 1.4.3 (≥ 4.5:1) |

Unchanged, deliberately: `light.brand.sky` and `light.interactive.focusRing` remain `#3E7FBD` (focus ring is
a non-text UI component under SC 1.4.11, ≥ 3:1, and passes at 4.04:1); the SMART `color_highlight` token
remains `#3E7FBD` as an accent, not body text.

**Correction K-20.** Revision 1 of `08-wcag-contrast.md` quoted `#1E4574` at 9.66 / 10.09. Two independent
recomputations (PHP 8.3 and PowerShell), both of which reproduce every other figure in that document
exactly, give **9.31 / 9.73**. The conclusion is unchanged — both pass with margin — but the recorded
figures are now the computed ones.

**Effect on certification.** The Group 1.5B package was accepted *conditionally* on this correction. With
D-1 applied, the condition is discharged: **0 FAIL pairs remain**, and the acceptance is now unconditional
on contrast grounds.

### 1.2 CR-3 in detail — the defect this prevents

Revision 1 instructed setting `css_header` / `portal_css_header` to `rtl_style_light.css` for Arabic.
Runtime evidence shows two consequences:

1. `interface/globals.php:494` builds the compact stylesheet name by prefixing the stored value, producing
   `compact_rtl_style_light.css`. The build never emits that file — the real artefact is
   `rtl_compact_style_light.css` (`webpack.themes.js` entry `rtl_compact_style_light`). Every Arabic
   session using the compact layout would request a missing stylesheet and render unstyled.
2. `interface/globals.php:551-611` skips its own RTL override when the stored value already contains
   `rtl`, so the built-in mechanism is suppressed as well.

**Correct behaviour:** store the plain LTR filename for every language. Verified by plan check `V-07`.

---

## 2. Approved scope — implemented in Phase 2 with the branding module

Both items are **approved to be built**. Neither is applied in this revision, because both must be
delivered as branding-module template overrides rather than edits to the OpenEMR core tree, and the module
does not exist yet (plan WP-2.1). Implementing them any other way would create exactly the core-edit
rebase burden the architecture was designed to avoid.

### 2.1 SMART dark style contract (`R-SMART-DARK`, BRAND-121–123)

**Decision:** create and develop the required SMART dark style file.

**Placement:** `interface/modules/custom_modules/oe-module-thiqa-branding/templates/api/smart/smart-style_dark.json.twig`,
supplied to Twig through `TwigEnvironmentEvent::EVENT_CREATED` → `FilesystemLoader::prependPath()`.
`SMARTAuthorizationController.php:433-434` already requests `smart-<coreTheme>.json.twig` and falls back to
light only when that template is absent, so no PHP change is required.

**Final content (frozen by this decision; generated from the `dark` block of
`brand/tokens/thiqa-tokens.json`):**

| SMART key | Light value | Dark value |
|---|---|---|
| `color_background` | `#FAFAF8` | `#0B1220` |
| `color_error` | `#8E271D` | `#F29088` |
| `color_highlight` | `#3E7FBD` | `#8FC1EE` |
| `color_modal_backdrop` | `#0B1B4D` (60% opacity) | `#000000` (60% opacity) |
| `color_success` | `#2F6B45` | `#8FD1A6` |
| `color_text` | `#0B1B4D` | `#F5F6F8` |
| `dim_border_radius` | `6px` | `6px` |
| `dim_font_size` | `14px` | `14px` |
| `dim_spacing_size` | `20px` | `20px` |
| `font_family_body` | `'Inter','IBM Plex Sans Arabic',sans-serif` | same |
| `font_family_heading` | `'Inter','IBM Plex Sans Arabic',sans-serif` | same |
| `logo_primary` | absolute URL via `LogoService` + branding revision | dark-variant mark via `LogoFilterEvent` |

A matching light override is generated from the same token source, so the two contracts cannot drift
(`docs/rebranding.md` §16.1). Acceptance: test **A8** — requesting `smart-style` with a dark
`css_header` must return dark tokens.

### 2.2 Login logo alternative text (BRAND-053)

**Decision:** add the needed logo caption.

**Placement:** `interface/modules/custom_modules/oe-module-thiqa-branding/templates/login/partials/html/primary_logo.html.twig`,
overriding the core template through the same prepended Twig loader path.

**Final content (frozen by this decision):**

| Image | English `alt` | Arabic `alt` | Source |
|---|---|---|---|
| Primary login logo | `Thiqa` | `ثقة` | `productName()` from `BrandingService`, language-aware |
| Secondary login logo | tenant display name, or empty if none is set | tenant display name (AR) | `saas_branding_tenant_display_name[_ar]` |

Rationale: an empty `alt` gives a screen-reader user nothing where the brand is, and shows a blank box if
the image fails to load. The value is resolved through the branding service rather than hardcoded, so a
white-label tenant gets its own name automatically.

---

## 3. Open dependencies

| ID | Item | Owner | Blocks |
|---|---|---|---|
| **D-3** | Legal registration of `Thiqa` / `ثقة`, **and** integration-owner clearance for HL7 `MSH-3` and QRDA organisation fields | Legal + integration owners | Setting `openemr_name`; freezing the module slug |
| **D-10** | Product registration endpoint: repoint to `https://reg.skyeagle.uk/api/registration` or disable registration | Product owner | Mandatory core patch #3 (`ProductRegistrationService.php:121`) |
| **D-4** | Native-Arabic linguistic proofreading of all Arabic strings | Localisation owner | Lifting `14-string-replacement-map.md` out of DRAFT; Arabic go-live |

Dependencies D-5 to D-9 and D-11 to D-13 in `docs/RebrandingPlan.md` §6.5 are unchanged by this record.

---

## 4. Files changed in this revision

| File | Change |
|---|---|
| `brand/tokens/thiqa-tokens.json` | `light.link.default` and `light.link.hover` updated (D-1) |
| `brand/qa/wcag-contrast-results.json` | 3 light link rows updated; 1 row added (`link hover on surface`); 33 → 34 pairs; 0 FAIL |
| `docs/branding-production/08-wcag-contrast.md` | Revision 2 — status PASS, summary counts, link rows, remediation → applied resolution, correction K-20 |
| `docs/branding-production/13-final-qa-matrix.md` | WCAG Light row → unconditional PASS; remaining-items list updated |
| `docs/branding-production/14-string-replacement-map.md` | Revision 2 — CR-1, CR-2, CR-3, CR-9, D-2 |
| `docs/branding-production/FINAL-GROUP-1.5B-CERTIFICATION.md` | Conditional acceptance discharged; remaining-items list updated |
| `docs/branding-production/11-asset-manifest.md` | Manifest entry count 117 → 118 (103 assets + 15 docs) |
| `docs/branding-production/12-release-verification.md` | Verification counts refreshed for the re-issued manifest |
| `docs/branding-production/15-decision-record.md` | **New** — this file |
| `brand/manifests/SHA256SUMS` | Re-issued (see §5) |
| `brand/manifests/asset-manifest.json` / `.csv` | `byte_size` + `sha256` refreshed for the two changed asset rows |
| `docs/RebrandingPlan.md` | §1 dispositions updated to DECIDED; §6.5 dependency register updated |

**No OpenEMR application file, asset or database value was changed.** All changes are to the brand design
package and its documentation.

---

## 5. Integrity re-issue

Two manifests exist in this project and they cover different things:

| Manifest | Covers | Status after this revision |
|---|---|---|
| `Locked Desicions/OpenEMR-SaaS-Decision-Documents-SHA256-UPDATED-2026-08-09.txt` | The 2 governance documents (Q1–Q77 register; implementation backlog) | **Unchanged and still valid** — no governance document was edited. Re-verified: both hashes match. |
| `brand/manifests/SHA256SUMS` | The brand design package (assets + `docs/branding-production/*.md`) | **Re-issued** — regenerated from the physical files after the changes above, and verified by two independent hashers. |

Verification method and results are recorded in `12-release-verification.md`.
