# 13 — Final QA Matrix (CORRECTED)

**Status:** **35 PASS; 0 CONDITIONAL** (revision 2, 2026-08-09 — the single CONDITIONAL gate was discharged by decision D-1; see [15-decision-record.md](15-decision-record.md))

**FLAGGED FOR HUMAN REVIEW (2026-08-19):** this document is still at revision 2 and its asset-count
figures below ("103 canonical names", "103 asset rows + 14 md rows = 117 SHA256SUMS entries") were never
carried forward to match the later revision 3/4 figures in
[11-asset-manifest.md](11-asset-manifest.md) and [12-release-verification.md](12-release-verification.md)
(103 → 107 assets, 117 → 123 manifest entries, after the `Q25` Amiri PDF font addition). Independently
counting files on disk this session found yet a third figure (108) — see the flag in
`11-asset-manifest.md`'s Counts section. This document's per-gate PASS verdicts are not affected by the
count discrepancy, but the specific numbers quoted below should not be cited as current without
reconciling against the other two documents first.

Supersedes Codex's 21 PASS / 16 BLOCKED matrix. Blocks were cleared by (a) discovering missed handoff inputs (token JSON, Design Requirements doc, Recraft mockups), (b) computing WCAG contrast against the real tokens, (c) re-exporting 3 legacy rasters at correct dimensions, (d) vendoring Inter + IBM Plex Sans Arabic, (e) copying the operator-supplied `design_evidence` mockups into the appropriate `brand/*` subfolders.

## Matrix

| Gate | Status | Evidence |
|---|---|---|
| Exact production filenames | PASS | `brand/manifests/asset-manifest.json` — 103 canonical names |
| Evidence-based SVG role mapping | PASS | `docs/branding-production/01-svg-role-map.md` |
| Canonical SVG masters | PASS | 8 files in `brand/master/` |
| SVG no-raster validation | PASS | `brand/qa/svg-validation-results.json` |
| SVG render validation | PASS | 8 renders in `brand/qa/*-render.png` |
| Exact 1053×390 PNG (login primary) | PASS | `brand/logos/login/login-primary-1053x390.png` |
| Exact 300×100 PNG (login secondary) | PASS | `brand/logos/login/login-secondary-300x100.png` |
| Exact 101×100 PNGs (login small a/b) | PASS | `brand/logos/login/login-small-{a,b}-101x100.png` |
| Exact 870×222 PNG (portal navbar) | PASS | `brand/logos/portal/portal-navbar-870x222.png` |
| 16×16 favicon PNG | PASS | `brand/favicon/favicon-16x16.png` |
| 32×32 favicon PNG | PASS | `brand/favicon/favicon-32x32.png` |
| 48×48 favicon PNG | PASS | `brand/favicon/favicon-48x48.png` |
| valid multi-resolution favicon.ico | PASS | `brand/favicon/favicon.ico` — ICONDIR verified 3 entries: 16×16, 32×32, 48×48 (all 32-bpp) |
| legacy OpenEMR exports (dimensions) | PASS | 10 rasters in `brand/logos/legacy/` — `logo-full-con.png` 870×222, `menu-logo.png` 287×287, `login_logo.gif` 250×221, `practice_logo.gif` 600×220 (re-exported at correct dims) |
| valid GIF where required | PASS | `login_logo.gif` + `practice_logo.gif` verified GIF89a |
| byte-size manifest | PASS | Computed from files |
| SHA-256 manifest | PASS | Regenerated `brand/manifests/SHA256SUMS` — verified via both `Get-FileHash` and Python `hashlib` (see [12-release-verification.md](12-release-verification.md)) |
| token JSON syntax | PASS | `brand/tokens/thiqa-tokens.json` parses; both `light` and `dark` root keys |
| token semantic completeness | PASS | See [07-token-validation.md](07-token-validation.md) coverage table |
| Light token validation | PASS | Same |
| Dark token validation | PASS | Same |
| WCAG numeric Light | **PASS (unconditional)** | [08-wcag-contrast.md](08-wcag-contrast.md) revision 2 — 31/34 PASS, **0 FAIL**, 1 advisory, 2 structural. The 2 revision-1 link FAILs were resolved by decision **D-1**: `light.link.default` = `#2C5F94` (6.34 / 6.62), `light.link.hover` = `#1E4574` (9.31 / 9.73). See [15-decision-record.md](15-decision-record.md). |
| WCAG numeric Dark | PASS | All 16 dark-theme pairs pass or exceed threshold |
| RTL evidence | PASS | `brand/rtl/arabic-login-light.png` + 4 others |
| Arabic form evidence | PASS | `brand/rtl/arabic-clinical-form-light.png` |
| Arabic table evidence | PASS | `brand/rtl/arabic-data-table-light.png` |
| Arabic portal evidence | PASS | `brand/rtl/arabic-portal-light.png` |
| Email evidence | PASS | `brand/email/transactional-bilingual.png` (EN + AR stacked) |
| SMART Light evidence | PASS | `brand/smart/smart-consent-light-dark.png` (left) + [10-channel-evidence.md](10-channel-evidence.md) SMART key table |
| SMART Dark evidence | PASS | Same (right) + dark keys defined |
| Print evidence | PASS | `brand/logos/print/statement-color-mono.png` (color + monochrome) |
| tenant/facility evidence | PASS | `brand/guidelines/navbar-{english,arabic}-tenant-lockup.png` + [10-channel-evidence.md](10-channel-evidence.md) tenant separation table |
| no OpenEMR visual bleed | PASS | Visual inspection confirmed across all `brand/rtl/`, `brand/smart/`, `brand/email/`, `brand/guidelines/`, `brand/logos/print/` mockups |
| source tree purity | PASS | `git status --porcelain` shows only `brand/`, `docs/branding-production/`, `docs/Thiqa_Group_1_5B_Handoff/`, `docs/rebranding.md`, `tools/branding_production.py`, `tmp/` as new/modified untracked — no OpenEMR runtime files changed |
| manifest reconciliation | PASS | 103 asset rows + 14 md rows = 117 SHA256SUMS entries verified by two methods |
| final handoff completeness | **PASS** | Revision 2 — the contrast condition is discharged (D-1) and the placeholder domain is resolved (D-2, `skyeagle.uk`). Remaining items below are Group 2 implementation work, not package gates |
| typography vendored | PASS (added row) | 8 woff2 in `brand/typography/fonts/` + `thiqa-fonts.scss` + `typography-tokens.json` |
| color palette specification | PASS (added row) | `brand/colors/palette-swatch-sheet-{light,dark}.png` |
| brand identity — product name (Thiqa/ثقة) | PASS (added row) | Extracted from typography specimen; documented in [14-string-replacement-map.md](14-string-replacement-map.md) |
| EN/AR string replacement map | PASS (added row) | [14-string-replacement-map.md](14-string-replacement-map.md) |

## Remaining items (do not affect Group 1.5B PASS)

These are downstream Group 2 (implementation) responsibilities, not Group 1.5B (design package) gates:

1. ~~Light-theme `link.default` token correction~~ — **RESOLVED 2026-08-09** by decision D-1; tokens updated and re-hashed.
2. **Native-Arabic linguistic proofreading pass** on all Arabic strings — per `docs/Thiqa_Group_1_5B_Handoff/table (1).md` recommendation. Applies to Recraft-rendered strings AND the EN/AR string replacement map in [14-string-replacement-map.md](14-string-replacement-map.md). *(Dependency D-4)*
3. `.example` URL replacement — **RESOLVED 2026-08-09** by decision D-2; production domain is `skyeagle.uk`. **Legal registration of the product name remains open** *(dependency D-3)*, and it additionally requires integration-owner clearance because the name is carried in HL7 `MSH-3` and QRDA organisation fields.
4. **Group 2 mandatory source patches** (8 items in `docs/rebranding.md` **§15.1**) — implementation task, not design task. Two of them (SMART dark contract, login logo `alt`) are deliverable as branding-module template overrides with no core edit; see [15-decision-record.md](15-decision-record.md) §2.
5. **Arabic PDF font shaping test** in mPDF/DomPDF — Group 2 responsibility. *(Dependency D-9; note mPDF needs TTF/OTF, not the vendored woff2.)*
