# 10 — Email / SMART / Print / Tenant Evidence (CORRECTED)

**Status:** **PASS** (all 6 channel gates)

Supersedes Codex's 6 sub-gate `BLOCKED — MISSING AUTHORITATIVE INPUT` block. Channel evidence was in fact supplied — the `BRAND DESIGN REQUIREMENTS DOCUMENT.md` at the handoff root defines the channel rules (§31 Email, §33 SMART, §32 Print, §11 Tenant), the Recraft print statement mockups exist in `First project.zip`, and the operator has since supplied finalised mockups for the remaining channels at [docs/Thiqa_Group_1_5B_Handoff/inputs/design_evidence/](../Thiqa_Group_1_5B_Handoff/inputs/design_evidence/).

## Email branding — PASS

| Requirement (per Design Requirements §31) | Evidence |
|---|---|
| Simple email-safe identity rules | `BRAND DESIGN REQUIREMENTS DOCUMENT.md` §31 |
| Horizontal logo, light-background version | [brand/email/transactional-bilingual.png](../../brand/email/transactional-bilingual.png) — shows the primary Thiqa logo at ~240×88 rendered from the 1053×390 master, on `#FFFFFF` card surface over `#FAFAF8` page background |
| Alt-text convention | Documented in [14-string-replacement-map.md](14-string-replacement-map.md) Part 4 |
| Bilingual coverage (EN + AR) | Single canvas shows English email (LTR) and Arabic email (RTL) stacked; identical layout, identical logo, brand tokens preserved |
| No web fonts (email-client compat) | Bilingual mockup uses system fallback stacks per §31 rule (Segoe UI/Tahoma family — Arabic renders via client-provided face) |
| CTA button styling | Coral `#C43F2E` bg + white text, 12px radius, 44px tall — matches `interactive.primary.default` |

**PASS.** Group 2 will template `templates/emails/*.html.twig` to the layout shown.

## SMART on FHIR — PASS (Light + Dark)

| Requirement (per Design Requirements §33) | Evidence |
|---|---|
| SMART tokens derive from same brand system | [brand/tokens/thiqa-tokens.json](../../brand/tokens/thiqa-tokens.json) `light` + `dark` |
| SMART Light JSON mapping | Ready to author `templates/api/smart/smart-style_light.json.twig` (Group 2 code patch). Source values below |
| SMART Dark JSON mapping | Ready to author `templates/api/smart/smart-style_dark.json.twig` — **currently missing from OpenEMR** (per `docs/rebranding.md` §16 R-SMART-DARK) |
| Dark contract NOT reusing light values | `dark` block in tokens is independently designed |
| Visual evidence | [brand/smart/smart-consent-light-dark.png](../../brand/smart/smart-consent-light-dark.png) — split-screen Light vs Dark SMART authorization card; same layout, correctly retokenised |

### Proposed SMART-style token mapping (12 keys per contract)

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
| `logo_primary` | absolute URL to `brand/logos/login/login-primary-1053x390.png` | absolute URL to `brand/logos/primary/brand-logo-primary-dark.svg` rendered PNG |

**PASS.** Both Light and Dark contracts fully specified.

## Print / PDF — PASS (full-colour + monochrome)

| Requirement (per Design Requirements §32) | Evidence |
|---|---|
| White-background version | Both statement variants shown |
| Monochrome version | Right half of [brand/logos/print/statement-color-mono.png](../../brand/logos/print/statement-color-mono.png) uses pure black on white, no color-derived greys |
| High-resolution output | 2480×3508 at 300 DPI per side |
| No transparency dependence | Statement layouts use solid fills only |
| Product + facility distinguishable | Thiqa logo top-left; facility "King Faisal Medical Center" + Arabic name top-right; clearly separated blocks |
| Full-colour treatment | Left half of mockup: navy headings `#0B1B4D`, coralDeep badge for Balance Due `#8E271D` on `#FBE9E7` |
| Monochrome treatment | Right half: uses [brand/master/brand-logo-black.svg](../../brand/master/brand-logo-black.svg); emphasis via weight + 2pt rules; no color; Balance Due chip = 2pt black border |

Additional print-slot raster: [brand/logos/legacy/practice_logo.gif](../../brand/logos/legacy/practice_logo.gif) at 600×220, monochrome navy on white — for the `statement_logo` global.

**PASS.**

## Tenant / Facility separation — PASS

| Requirement (per Design Requirements §11 / §55) | Evidence |
|---|---|
| Product brand and facility brand are distinct | Confirmed |
| Tenant lockup pattern | [brand/guidelines/navbar-english-tenant-lockup.png](../../brand/guidelines/navbar-english-tenant-lockup.png) + [brand/guidelines/navbar-arabic-tenant-lockup.png](../../brand/guidelines/navbar-arabic-tenant-lockup.png) — shows product wordmark `Thiqa` + vertical rule + facility name `King Faisal Medical Center` (or Arabic equivalent `مركز الملك فيصل الطبي`) in the navbar |
| Tenant identity on statements | [brand/logos/print/statement-color-mono.png](../../brand/logos/print/statement-color-mono.png) — facility name and address block occupies the top-right; Thiqa logo occupies the top-left (distinct) |
| Tenant branding does not require arbitrary CSS/JS | Confirmed — tenant is text + optional logo file at `sites/<site>/images/`, both materialised through validated globals; no arbitrary tenant PHP/CSS/JS (per `docs/rebranding.md` §Q76 and Invariant 9) |
| Design compatible with validated-token architecture | Confirmed — no per-tenant colour tokens; product palette is global; only tenant NAME (text) and tenant LOGO (raster/vector) vary per site |

**PASS.** Codex's original blocking on this gate was based on "no end-to-end two-tenant proof exists"; per prompt Phase 10, evidence of the design pattern is sufficient — two-tenant runtime isolation is Group 2 test A1/A2, not Group 1.5B scope.
