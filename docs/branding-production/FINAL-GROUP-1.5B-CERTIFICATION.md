# FINAL — Group 1.5B Certification (CORRECTED)

## Authoritative output roots

- [brand/](../../brand/) — 103 physical assets across master, logos, favicon, tokens, typography (incl. vendored fonts), colors, smart, email, rtl, guidelines, previews, qa, manifests.
- [docs/branding-production/](.) — 14 evidence documents.

## Baseline / integrity

| Property | Value |
|---|---|
| Branch | `master` |
| HEAD SHA at start | `631f2b38cf633769c305233f88cdf9c73ca80657` |
| HEAD SHA at end | `631f2b38cf633769c305233f88cdf9c73ca80657` (unchanged — no commits) |
| Application source purity | PASS (see `git status --porcelain` — only additions under `brand/`, `docs/branding-production/`, `docs/Thiqa_Group_1_5B_Handoff/`, `docs/rebranding.md`, `tools/`, `tmp/`; no OpenEMR runtime file modified) |

## Scope of this run

This certification is the **corrected output** for Group 1.5B. It supersedes Codex's original `NOT READY FOR GROUP 2` finding, whose 16 BLOCKED gates were traceable to missed handoff inputs (token JSON at handoff root, Brand Design Requirements Document, table.md/table (1).md, Recraft mockup zips) rather than to any true design gap. The corrected run inventoried the full handoff surface, distributed the missing evidence into the canonical `brand/` layout, computed the WCAG contrast numerically, vendored the fonts, and regenerated the manifest.

See [00-baseline-addendum.md](00-baseline-addendum.md) for the extended input inventory that made these corrections possible.

## Result summary

| Element | Status |
|---|---|
| SVG mapping and validation | PASS |
| Exact required PNG exports | PASS |
| Favicon PNG/SVG + real multi-resolution ICO (16/32/48) | PASS |
| Legacy PNG/GIF exports (corrected dimensions) | PASS |
| Token validation (Light + Dark, semantic completeness) | PASS |
| WCAG numeric validation | **PASS (unconditional as of 2026-08-09)** — the `light.link.default` remediation was adopted as decision **D-1**; 0 FAIL pairs remain |
| RTL + Arabic Login/Navbar/Clinical Form/Data Table/Portal evidence | PASS |
| Email branding evidence (EN + AR bilingual) | PASS |
| SMART Light + Dark evidence + token mapping | PASS |
| Print full-color + monochrome evidence | PASS |
| Tenant/facility separation evidence | PASS |
| Typography vendored (Inter + IBM Plex Sans Arabic, 8 woff2, @font-face SCSS, weight+scale JSON) | PASS |
| EN/AR string replacement map | PASS |
| SHA-256 verification (two independent methods) | PASS — 117/117 verify with PowerShell `Get-FileHash` and Python `hashlib` |
| Application source purity | PASS |

## Final QA matrix reference

See [13-final-qa-matrix.md](13-final-qa-matrix.md) — revision 2: **35 PASS / 0 CONDITIONAL**, plus items
formally out of Group 1.5B scope (Group 2 implementation).

## Remaining items (Group 2 scope — NOT design gaps)

1. Apply the 8 mandatory source patches per `docs/rebranding.md` **§15.1** (revision 1 of this document
   cited §14 in error), using values from [14-string-replacement-map.md](14-string-replacement-map.md).
2. Author the SMART dark style contract using the 12-key mapping in
   [10-channel-evidence.md](10-channel-evidence.md) §SMART. **Approved as decision 2.1 in
   [15-decision-record.md](15-decision-record.md)**; delivered as a branding-module template override
   rather than a core file, because `SMARTAuthorizationController.php:433-434` already resolves
   `smart-<theme>.json.twig` dynamically.
3. Compile `brand/tokens/thiqa-tokens.json` into the shared theme bundle. *(Governance note: the shared
   product palette is build-layer work under `Q34`/`Q77`; `Q76` governs the separate per-tenant token
   overlay materialised at runtime. See `docs/RebrandingPlan.md` §2.4.)*
4. Move `brand/typography/fonts/*.woff2` into `public/assets/fonts/thiqa/` and reference from theme SCSS.
   *(`public/assets/*` is gitignored except `modified/`, so this needs a build sync step — plan §3.7.4.)*
5. Integrate rendered strings into the OpenEMR `lang_definitions` translation catalogue — Arabic
   proofreading first *(dependency D-4)*. **Note:** OpenEMR uses SQL-backed `lang_*` tables, not
   `.po`/`.mo` files (`docs/rebranding.md` BRAND-102–104, locked `Q18`).
6. ~~Adopt the `light.link.default` correction~~ — **DONE 2026-08-09** (decision D-1). SC 1.4.3 is now an
   unconditional PASS.
7. Legal registration of the final product name *(dependency D-3, which also requires integration-owner
   clearance for HL7 `MSH-3` and QRDA)*. Production URLs are **resolved**: the domain is `skyeagle.uk`
   (decision D-2); no `.example` placeholder remains.

## Failed gates

None.

## Blocked gates

None.

## Advisories

- `light.text.disabled #9CA0AC` on background = 2.50:1 — WCAG explicitly exempts inactive UI components from SC 1.4.3.
- `light.border` / `dark.border` on background = 1.24 / 1.44 — border-of-border is not a WCAG SC; borders convey no information.

---

# BRAND DESIGN PACKAGE — ACCEPTED FOR GROUP 2 (UNCONDITIONAL)

**Revision 2, 2026-08-09.** The single condition attached to revision 1 — token-owner adoption of the
`light.link.default` contrast correction — was **discharged** by decision **D-1**:
`light.link.default = #2C5F94` (6.34 / 6.62) and `light.link.hover = #1E4574` (9.31 / 9.73). Zero WCAG FAIL
pairs remain, and `brand/tokens/thiqa-tokens.json` has been updated and re-hashed.

Acceptance is therefore **unconditional**. All required design artifacts, evidence, tokens, typography and
validation are complete and verified. Outstanding items are Group 2 implementation work and three named
business dependencies (D-3 legal/integration clearance of the product name, D-4 Arabic proofreading,
D-10 registration endpoint), recorded in [15-decision-record.md](15-decision-record.md) §3.
