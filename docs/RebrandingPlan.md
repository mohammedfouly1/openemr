# Group 2 — Rebranding & Identity Implementation Plan (Branding Layer Architecture)

**Document type:** PLAN ONLY — no application code, configuration, asset or database change is authorised
by this document.
**Programme phase:** Group 2 (implementation planning), entered under `VERDICT A0 — GROUP 1 CLOSED;
GROUP 2 MAY START` (`docs/rebranding.md` §22).
**Implements:** `MVP-010` (safe tenant branding), the branding portion of `MVP-014` (Control Plane
branding model), and `R-SMART-DARK`.
**Date:** 2026-08-09
**Repository state assumed:** branch `master`, HEAD `631f2b38cf633769c305233f88cdf9c73ca80657`, zero
fork-owned commits.

---

## 0. Binding references and how this plan uses them

| # | Reference | Status in this plan | Role |
|---|---|---|---|
| R1 | `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md` (Q1–Q77, Invariants 1–10, Control Plane §1–§12) | **BINDING — authoritative** | No decision may be reinterpreted here. Where this plan and the register differ, the register wins. |
| R2 | `Locked Desicions/OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria-UPDATED-2026-08-09.md` | **BINDING** | Supplies the acceptance criteria this plan must satisfy (`MVP-010`, `MVP-014`). |
| R3 | `Locked Desicions/OpenEMR-SaaS-Decision-Documents-SHA256-UPDATED-2026-08-09.txt` | **BINDING (integrity)** | 2-line manifest covering R1 and R2. |
| R4 | `docs/rebranding.md` (Group 1A–1D certified discovery, BRAND-001…136) | **BINDING inventory** | The complete work surface. Every Group 2 item traces to a BRAND ID. |
| R5 | `docs/branding-production/` (15 evidence documents incl. the Group 1.5B certification and the decision record) | **BINDING design package** | Validated tokens, WCAG results, RTL/channel evidence, EN/AR string map, owner decisions. |
| R6 | `brand/` (103 assets, manifests, SHA256SUMS) | **BINDING asset kit** | The physical replacement assets and the token/typography source of truth. |
| R7 | `CLAUDE.md` | **BINDING coding standards** | PHP 8.2+, `declare(strict_types=1)`, PSR-4/PER-CS, PHPStan level 10, DI, enums, immutability, `saas_` prefix. |
| R8 | `CLAUDE.local.md` | **BINDING local runtime** | Native Apache/PHP/MariaDB on this host; no Docker; front-end build must run off the `G:` mount. |

### 0.1 Reference-integrity check performed before planning

- R3 lists exactly two hashes, for R1 and R2. Those two hashes are the **re-issued** post-Section-L
  values (`de8548a3…` for R1, `cb2367ad…` for R2).
- `docs/rebranding.md` §1 quotes a *different* pair (`cf806777…`, `38ba60c9…`) as verified. That quotation
  records the state **at the time §1 was written**, before Section L (`Q76`/`Q77`) was appended to R1 and
  before the four `MVP-010` / one `MVP-014` criteria were appended to R2. `docs/rebranding.md` §17.4-B
  discloses exactly those three governance-file edits and states the manifest was re-issued. The two hash
  pairs are therefore **sequential, not contradictory**, and R3 is the current manifest.
- **Re-verified 2026-08-09:** both governance documents hash-match R3 exactly (2/2 MATCH, PHP `hash_file`).
  No locked document has drifted. The 2026-08-09 branding decisions changed only the brand package, so R3
  needed no re-issue; `brand/manifests/SHA256SUMS` was re-issued and verifies 118/118 under two independent
  hashers. Repeat this check at each release (plan §7.3).
- All 136 BRAND IDs, the 11 permitted Group 2 actions, and the per-ID mapping in `docs/rebranding.md`
  §16.2 were read in full and are reproduced by reference (not restated) in this plan's traceability
  appendix.

### 0.2 What this plan is not

It does not reopen any locked decision, does not propose a new architecture option where one is already
locked, and does not treat any Group 1 finding as re-litigable. Where it proposes a **different delivery
mechanism** from the one implied by a Group 1 note, the locked *outcome* is preserved and the change is
recorded explicitly in §1 with evidence.

---

## 1. Conflicts, discrepancies and clarifications found during review

Per the task instruction to stop and report conflicts before proceeding: **no conflict was found that
blocks planning.** Fifteen items require the reader's attention. Eight are documentation-level (CR-*),
seven are open external dependencies (carried into the Phase 4 blocking register as D-*).

### 1.0 Decision status (updated 2026-08-09)

The project owner has ruled on every item that was awaiting a decision. Rulings, and the changes made to
the brand package as a result, are recorded in
[`docs/branding-production/15-decision-record.md`](branding-production/15-decision-record.md).

| Item | Ruling | State |
|---|---|---|
| CR-1 | Fix the wrong section references now | **APPLIED** to `14-string-replacement-map.md` |
| CR-2 | Correct the table title, with a strict audit of related references | **APPLIED** — heading now states 35 rows / 33 branding globals + 2 portal globals |
| CR-3 | Accept: store the plain `style_light.css` for every language; drop the two `rtl_`-prefixed rows | **APPLIED** — rows 25/28 rewritten; runtime rationale recorded |
| CR-7 | SMART dark contract approved — *"create and develop required SMART file"* | **APPROVED SCOPE** — content frozen; built in Phase 2 as a module override (§1.0.1) |
| CR-8 | Login logo caption approved — *"add needed logo caption"* | **APPROVED SCOPE** — values frozen; built in Phase 2 as a module override (§1.0.1) |
| CR-9 | Accept: keep `style_light.css` / `style_dark.css`; *Saudi Light / Saudi Dark* are product labels | **DECIDED** — recorded in the string map and §3.7.2 |
| D-1 | Accept the contrast fix: promote `#2C5F94` to link default, introduce a darker hover | **APPLIED** — `light.link.default = #2C5F94`, `light.link.hover = #1E4574`; **0 WCAG FAIL pairs remain** |
| D-2 | Production domain is **`skyeagle.uk`** | **APPLIED** — every `thiqa.example` placeholder replaced |
| Fingerprints | Re-issue after the changes | **APPLIED** — `brand/manifests/SHA256SUMS` re-issued, **118/118 verified by two hashers**; the governance manifest was re-verified and is **unchanged**, since no locked decision was amended |

**New correction K-20 (found while applying D-1).** Revision 1 of `08-wcag-contrast.md` quoted `#1E4574`
at 9.66 / 10.09. Two independent recomputations — PHP 8.3 and PowerShell, each reproducing every other
figure in that document exactly — give **9.31 / 9.73**. Both values pass SC 1.4.3 comfortably, so the
decision is unaffected, but the recorded figures were wrong and are now the computed ones.

#### 1.0.1 Why the two approved build items are not yet created

Both must be delivered as branding-module template overrides, which is precisely what keeps them out of the
OpenEMR core tree (CR-7, CR-8). The module does not exist yet — it is Phase 2, work package WP-2.1.
Creating the two files anywhere else would produce exactly the core-edit rebase burden this architecture
exists to avoid. Their **final content is frozen** by the decision record (§2.1 and §2.2 there), so
implementation is mechanical once the module skeleton lands.

### 1.0.2 External audit of this register (2026-08-09)

Two independent reviews were recorded in `docs/RebrandingPlanConflicts.md` and audited against the codebase
and the locked register. Outcome: **five new findings accepted** (CR-16 through CR-20 below), **two
rejected on evidence**, and **two mechanism proposals in this plan withdrawn and replaced** (CR-7, CR-8).
The consolidated list of what remains open is §1.3.

| New ID | Origin | Verdict after codebase audit |
|---|---|---|
| **CR-16** | AC-1 — PDF fonts conflict with `Q25` | **ACCEPTED — the most serious finding of the review.** `Q25` names *Amiri and/or Noto Naskh Arabic*; §3.7.4 had proposed IBM Plex Sans Arabic for PDF. Corrected. |
| **CR-17** | AC-2 — `Q38` namespace vs. unnamespaced Twig shadowing | **ACCEPTED in substance.** A better, fully namespaced mechanism exists for CR-7; CR-8's zero-core-edit claim is retracted. |
| **CR-18** | AC-3 — theme display labels | **ACCEPTED.** Verified at `interface/super/edit_globals.php:736-742`: the selector renders `ucfirst(str_replace('_',' ', substr($file, 6)))`, so `style_light.css` displays as **"Light"**, not "Saudi Light". |
| **CR-19** | AC-4 — writable CSS inside the module code tree | **ACCEPTED.** Leads to a design change that removes dependency D-8 entirely (§3.2.2 revised). |
| **CR-20** | AC-5 / first audit item 2 — cache-key composition underspecified | **ACCEPTED.** Needs a canonical per-asset-type URL specification and tests. *(Note: the first audit cited `?v=$v_database`; the actual variable is `$v_js_includes` — `interface/globals.php:479`.)* |
| — | D-ID numbering drift | **ACCEPTED.** Four misnumbered references corrected (PDF fonts D-10→D-9 ×2, registration D-11→D-10, counsel D-12→D-11), plus the *"9 strings"* → *"9 IDs / 10 strings"* count. |
| — | First audit, *Additional Conflict 4* — `LogoService` falls back to `sites/default/` | **REJECTED on evidence.** `src/Services/LogoService.php:77-87` builds its search path from `OE_SITE_DIR` and `getImagesAbsolute()`; the fallback is **`public/images/logos/`**, never another site's directory. The underlying *concern* — an unprovisioned tenant showing upstream marks — is real and was already addressed by the build-layer product-default overlay (§3.7.6). |
| — | First audit, *Additional Conflict 1* — npm build on the Drive mount | **NOT NEW.** Already carried as risk R-7 (§9) with the same mitigation, sourced from `CLAUDE.local.md` §6. |
| — | First audit, *Additional Conflict 3* — RTL compact resolution | **NOT NEW.** A restatement of CR-3; adds no information beyond it. |

### 1.1 Conflicts and discrepancies register

The *Proposed disposition* column records the analysis as first presented; §1.0 above records the owner's
ruling and whether it has been applied.

| ID | Finding | Evidence | Severity | Proposed disposition |
|---|---|---|---|---|
| **CR-1** | `docs/branding-production/14-string-replacement-map.md` cites *"33 items from `docs/rebranding.md` §13"*, *"8 mandatory items from §14"* and *"5 conditional from §15"*. In R4 those sections are §3/§9.4 (globals), §15.1 (mandatory) and §15.2 (conditional); §13 is the `Q76` materialisation analysis and §14 is the `Q77` theme analysis. `FINAL-GROUP-1.5B-CERTIFICATION.md` repeats the *"§14"* error. | R4 §13, §14, §15.1, §15.2 vs R5 `14-…md` §Parts 1–3 | Low — documentation only | Correct the cross-references in the Phase 5 documentation set. The **content** of the string map matches R4's actual mandatory/conditional lists item-for-item; only the section numbers are wrong. No content is invalidated. |
| **CR-2** | The same file's Part 1 is titled *"33 items"* but contains **35 numbered rows** (rows 34–35 add `portal_onsite_two_address` and `portal_onsite_two_enable`). | R5 `14-…md:15-53`; R4 §3 count table (33 branding-relevant globals) | Low | Both extra rows are legitimate BRAND items (BRAND-070, BRAND-064). Restate the heading as **35 configuration rows covering the 33 branding-relevant globals plus 2 portal-enablement globals**. |
| **CR-3** | Part 1 rows 25 and 28 propose setting `css_header` / `portal_css_header` to **`rtl_style_light.css`** for Arabic. OpenEMR applies the RTL substitution itself from `language_direction`; the value stored in `globals` must stay the **LTR filename**. Storing an `rtl_`-prefixed value makes `interface/globals.php:494` derive `compact_header = compact_rtl_style_light.css`, which is never built (the built name is `rtl_compact_style_light.css`), and `interface/globals.php:551-611` then skips its own override because the value already contains `rtl`. | `interface/globals.php:474-495`, `:551-611`; `webpack.themes.js` entry names `rtl_compact_style_*`; R4 BRAND-082 | **Medium — would ship a broken compact stylesheet on Arabic sessions** | **Reject rows 25/28 as written.** `css_header` and `portal_css_header` are set to `style_light.css` for **all** languages; RTL is selected at runtime. Recorded as a Phase 3 WS-A rule and as an acceptance check (`V-07`). Requires sign-off from the string-map owner. |
| **CR-4** | Part 1 row 35 proposes `portal_onsite_two_enable = 1`. Enabling the patient portal is a product/provisioning decision with security surface, not a branding action; R4 assigns BRAND-064 the action SET-CONFIG but the *baseline* value is `0`. | R5 `14-…md:53`; R4 BRAND-064; Q32 | Low — scope | Branding work must be **portal-enablement-agnostic**. The branding layer configures the portal correctly whether it is on or off. The value of `portal_onsite_two_enable` is owned by provisioning, not by this plan. Portal branding acceptance (A6) is gated on it being enabled in a test tenant. |
| **CR-5** | `FINAL-GROUP-1.5B-CERTIFICATION.md` remaining-item 3 says *"Materialise `brand/tokens/thiqa-tokens.json` into `interface/themes/*.scss` (Q76 governance)"*. `Q76` governs **per-tenant** token materialisation into the tenant runtime; compiling the **product** palette into shared SCSS is `Q34`/`Q77` build-layer work over a *shared immutable bundle*. | R1 Q76 vs Q34/Q77; R5 cert §Remaining | Low — terminology | Clarified in this plan's two-tier token model (§2.4, §3.4): **Tier 1 product tokens = build-time, immutable, identical for all tenants** (Q34/Q77); **Tier 2 tenant overlay = runtime materialised** (Q76). Both are planned; they are different mechanisms with different governance. |
| **CR-6** | `10-channel-evidence.md` states *"no per-tenant colour tokens; product palette is global; only tenant NAME and tenant LOGO vary per site."* `MVP-010` AC-1 requires *"Tenant can change approved logo **and tokenized palette**"*. | R5 `10-…md` §Tenant vs R2 `MVP-010` | **Medium — capability gap if taken literally** | Not a contradiction: the design package describes the **Thiqa product default** (empty tenant overlay); `MVP-010` describes the **capability that must exist**. The architecture builds the validated per-tenant overlay and ships it **empty by default**. Both statements then hold simultaneously. No decision needed. |
| **CR-7** | R4 §15.1 item 4 classes the SMART dark contract as a **mandatory core patch**. Source evidence shows `SMARTAuthorizationController::smartAppStyles()` already composes the template name dynamically as `/api/smart/smart-<coreTheme>.json.twig` with a light default, so a dark theme resolves `smart-style_dark.json.twig` **if that template exists**. | `src/RestControllers/SMART/SMARTAuthorizationController.php:419-434`; `renderTwigJson():356-376`; R4 §11.2 (runtime-proven light fallback) | Informational — **reduces** core-edit footprint | **REVISED 2026-08-09 after external audit (AC-2).** The original proposal — an unnamespaced `FilesystemLoader::prependPath()` shadowing the core `/api/smart/...` path — is **withdrawn**: it relies on global loader order, which is the exact fragility `Q38` exists to remove. Replaced by a `Q38`-clean mechanism: the module registers its own **namespaced** path (`addPath($dir, 'oe-module-thiqa-branding')`) and a listener on `TemplatePageEvent` (dispatched unnamed in `renderTwigJson():359`, so keyed on the class name) rewrites the template to `@oe-module-thiqa-branding/api/smart/smart-style_dark.json.twig` when `getPageName() === 'oauth2/authorize/smart-style'`. Still **zero modification of any tracked core file**, and no unnamespaced resolution. Canonical action for BRAND-121–123 stays **TOKENIZE**. |
| **CR-8** | R4 §16.2 assigns BRAND-053 (empty `alt=""` on the primary login logo) the action **PATCH**. `templates/login/partials/html/primary_logo.html.twig` hardcodes `alt=""`, but `interface/login/login.php:272-273` dispatches `TemplatePageEvent::RENDER_EVENT` with the layout name and view arguments, and Twig template resolution is module-overridable. | `templates/login/partials/html/primary_logo.html.twig:14,19`; `interface/login/login.php:268-273`; `src/Common/Twig/TwigContainer.php:89-90` | Informational | **REVISED 2026-08-09 after external audit (AC-2).** The unnamespaced-shadow route is **withdrawn** for the same `Q38` reason as CR-7, and the claim that **`Trk` becomes NO** is **retracted** — BRAND-053 stays a tracked (conditional) patch. Verified: `interface/login/login.php:272-279` renders `$event->getTwigTemplate()` with `$event->getTwigVariables()`, so a namespaced full-layout override *is* possible, but the partial hardcodes `alt=""` with no variable, so that route means forking a layout **and** a partial. **Recommended instead:** a one-line upstream-first change to `primary_logo.html.twig` reading `{{ primaryLogoAlt|default('') }}`, with the value supplied through the existing event. Smallest total maintenance, genuinely upstream-worthy (an accessibility fix), and no template duplication. |
| **CR-9** | Neither R4 nor R5 fixes the **filenames** of the two Saudi theme variants. `Q77` names them *"Saudi Light"* and *"Saudi Dark"*; the runtime fallback constant is the literal `style_light.css` (`interface/globals.php:476`), the admin selector is a filesystem scan matching `^style_.*\.css$` (`interface/super/edit_globals.php:714-732`), and `config/config.yaml:60-78` binds by `%css_header%`. | `interface/globals.php:474-483`; `interface/super/edit_globals.php:714-732`; `config/config.yaml:60-78`; R5 `14-…md` row 25 assumes `style_light.css` | **Medium — needs a decision before the theme build changes** | **Recommended: keep the filenames `style_light.css` / `style_dark.css`** and treat *Saudi Light / Saudi Dark* as the product-facing labels. Renaming would require patching the hardcoded fallback in a core bootstrap file (breaching Invariant 4 and creating a permanent rebase conflict) for zero user benefit — the selector shows a de-underscored filename, not a product label. Requires product-owner acknowledgement. |
| **CR-10** | `07-token-validation.md` records two named governance gaps: `border.strong` is not defined, and `surface.input` is undefined for the dark theme. | R5 `07-…md` §Governance flags 2–3 | Low | Both are resolved **inside the token contract** in Phase 1 (§3.4): `border.strong` derives from `text.secondary`; dark `surface.input` = `dark.surface` on body, `dark.background` on raised cards — exactly as the validation document proposes. Recorded as token-owner ratification items, not blockers. |
| **CR-11** | `13-final-qa-matrix.md` claims source-tree purity but lists `tmp/` and `tools/branding_production.py` among the new paths; `08-wcag-contrast.md` references `tmp/task-wcag.ps1` as the reproducible implementation. | R5 `13-…md` §source tree purity; `08-…md` §Method | Low — reproducibility | The WCAG reference implementation lives in a scratch path that is not part of the certified package. Phase 2 re-implements contrast checking as a **first-class, tested tool** inside the repository (§4.3 WP-2.6) so the numbers are reproducible in CI rather than from `tmp/`. |
| **CR-12** | The brand kit supplies `brand/logos/portal/portal-login-secondary-300x100.png`, closing BRAND-021 (*"no default asset exists anywhere"*). | R6 `brand/logos/portal/`; R4 BRAND-021 | Informational — **gap closed** | Record BRAND-021 as satisfied by the kit; the Phase 3 asset overlay provisions the slot. |
| **CR-13** | R4 BRAND-125 records `$font-family-sans-serif: "Lato"` as *declared, never shipped or loaded*. The kit vendors Inter + IBM Plex Sans Arabic (8 × woff2). `public/assets/*` is gitignored except `public/assets/modified`. | `interface/themes/default-variables.scss:85`; `.gitignore:16-18`; R6 `brand/typography/fonts/` | Low — needs a delivery route | Fonts are delivered by a **fork-owned build sync step** into `public/assets/fonts/thiqa/` (§3.7.4), keeping upstream directories untouched and avoiding a tracked binary drop. |
| **CR-14** | R4 §16.2 assigns BRAND-102/103/104 the action SET-TRANSLATION, i.e. Arabic catalogue work; Arabic catalogue ownership is `MVP-004`, not `MVP-010`. | R4 §16.2; R2 `MVP-004` | Low — ownership | The branding layer **supplies the EN/AR string values** (R5 `14-…md` Parts 4–5) and their `lang_constant` mapping; **materialising them into `lang_definitions` is executed under `MVP-004`**. Phase 3 WS-D delivers the source-of-truth list and the import script; the catalogue import runs on `MVP-004`'s schedule. |
| **CR-15** | `Q17` freezes the six session/application identity constants and the session cookie named `OpenEMR`; the brand kit and string map correctly leave them alone, but the constants are the most visible remaining *machine-facing* OpenEMR identity. | R1 Q17; R4 §15.3, BRAND-089–093/131; R5 `14-…md:55` | Informational | **PRESERVE.** Explicitly re-stated as constraint C6 and enforced by a CI guard (§4.3 WP-2.7) that fails the build if `SessionUtil.php` identity constants change. |

### 1.2 CONSOLIDATED REGISTER — RESOLVED 2026-08-09

All nine items were resolved on the recommended advice. Resolutions are recorded in
[`docs/branding-production/16-conflict-resolutions.md`](branding-production/16-conflict-resolutions.md).

| # | Item | Outcome |
|---|---|---|
| 1 | CR-16 / D-9 — Arabic PDF fonts vs `Q25` | **RESOLVED IN DESIGN.** Amiri selected (Noto Naskh acceptable); no ADR sought. Full mPDF + dompdf configuration spec and 7 acceptance tests written. Discovered en route: mPDF bundles **XB Riyaz** as its only Arabic face — not a `Q25` font and not a deliberate choice, so it must not be relied on; dompdf has none. **One manual step remains:** obtain the two TTFs |
| 2 | CR-17 / D-14 — `Q38` template delivery | **CLOSED.** Namespaced `TemplatePageEvent` route adopted as the standing pattern; `prependPath()` prohibited for SaaS modules; both dispatch keys documented (they differ between the SMART and login paths); CI rule specified |
| 3 | D-3 — product name clearance | **SCOPED.** The machine-facing surface is now an exact 3-line inventory: **one** HL7 `MSH-3` emission (a syndromic-surveillance ADT, not general lab/rad traffic) plus two QRDA organisation fields. Materially narrower than assumed. Legal + integration sign-off remain external |
| 4 | D-10 — registration endpoint | **CLOSED.** Registration **disabled**, not repointed — no Thiqa endpoint need exist or be operated |
| 5 | D-11 — acknowledgements page | **SCOPED.** Five-item disposition request prepared for counsel; authentication-gating identified as separable from the content review |
| 6 | D-4 — Arabic proofreading | **SCOPED.** Handoff package defined with safety-priority review ordering and required return evidence |
| 7 | CR-20 / D-16 — cache keys | **CLOSED.** Canonical per-asset-type specification (§3.8.1) + 8-case test matrix |
| 8 | CR-18 / D-15 — theme labels | **CLOSED.** "Light" / "Dark" accepted in the admin selector; `Q77` itself argues against patching that file |
| 9 | CR-10 / D-12 — token gaps | **CLOSED.** `borderStrong`, `surfaceInput`, `surfaceInputOnRaised` ratified into the token contract; 4 new contrast pairs, all PASS; 38 pairs total, 0 FAIL |

#### Update — 2026-08-09, later the same day

**Item 1 is CLOSED.** Amiri Regular + Bold (TTF, OFL-1.1) are installed at `brand/typography/fonts/pdf/`
and registered as THIQA-100–103. Verified: genuine TrueType signature, licence text present, manifest
re-issued at **107 assets + 16 docs = 123 entries, 123/123 under two hashers**. `Q25` is satisfied at the
asset level; engine configuration remains ordinary Phase 3 work.

**Scope amendment — MVP marketing demo.** The owner has set the current target as a public marketing demo:
no hospital deployment, no FHIR connection, no claims traffic, synthetic data only, with compliance and
registration steps taken when a real customer adopts the product. Consequences:

- **Item 3 (product name)** — the *integration* half is moot: with no live receiver, the single
  syndromic-surveillance `MSH-3` emission and the two QRDA fields have nothing to break. **Must be
  re-opened before any real interface is connected.** The *legal* half is deferred by owner decision.
- **Item 5 (acknowledgements page)** — counsel review is the wrong instrument at this scope. Replaced by
  a cheaper, fully reversible control that alters no page content: hide the links
  (`display_acknowledgements=0`, `display_acknowledgements_on_login=0`) **and** add a web-server deny rule,
  because the file is static and reachable by direct URL regardless of those globals. See
  `docs/branding-production/16-conflict-resolutions.md` §12.
- **Item 6 (Arabic proofreading)** — unchanged, and arguably higher priority: for a demo aimed at Saudi
  buyers, Arabic quality is the shop window.

**Remaining external action — one:** MSA proofreading (item 6).

#### Superseded: the register as it stood before resolution

| # | Open item | Type | Blocks | Owner | Recommended resolution |
|---|---|---|---|---|---|
| **1** | **CR-16 / D-9 — Arabic PDF fonts violate `Q25`.** The kit ships IBM Plex Sans Arabic WOFF2; `Q25` locks *Amiri and/or Noto Naskh Arabic* for PDF with explicit engine configuration, and the product carries **two** engines (`mpdf ^8.2.7`, `dompdf ^3.1.4`) | Locked-decision conflict | Arabic print/PDF work; A5 | Brand + platform | Add Amiri **or** Noto Naskh Arabic in TTF/OTF with licence and hashes; configure both engines explicitly; keep IBM Plex as the **web** face. Substituting IBM Plex for Q25's named fonts would need a new ADR — not recommended, the named fonts are free and purpose-built |
| **2** | **CR-17 / D-14 — `Q38` interpretation for template delivery.** Shadowing a core Twig path needs the main namespace, so it is inherently unnamespaced and order-dependent | Governance / mechanism | CR-7 and CR-8 delivery | Architecture owner | Adopt the namespaced `TemplatePageEvent` route for SMART (already redesigned, §1.1 CR-7). Ratify as the standing pattern, and add the CI check `Q38`/`MVP-008` already require. No ADR needed if the namespaced route is adopted |
| **3** | **D-3 — product name legal + integration clearance** | External | `openemr_name`; module slug freeze | Legal + integration | Two separate sign-offs. Do **not** assume every receiver filters on MSH-3 — inventory the actual integrations, then agree a coordinated cutover per interface |
| **4** | **D-10 — product registration endpoint** | Business decision | Core patch 3 | Product owner | Recommend **disabling** registration outright rather than repointing: it is opt-in telemetry to a vendor you are replacing, and a disabled switch needs no endpoint to exist |
| **5** | **D-11 — counsel review of the acknowledgements page** | Legal | Public launch | Legal | Page is `HTTP 200` unauthenticated with third-party identities and personal emails. Ask counsel for an itemised disposition; consider authentication-gating as a separate, non-branding decision |
| **6** | **D-4 — native Arabic proofreading** | External | Arabic go-live; lifting the string map from DRAFT | Localisation | MSA clinical review with safety-priority ordering; return signed evidence naming the exact constants |
| **7** | **CR-20 / D-16 — cache-key specification** | Design gap | `MVP-010` AC-4 acceptance | Architecture + platform | Write the canonical per-asset-type URL rule (core `?v=$v_js_includes` preserved; tenant revision added as a **separate** parameter, never a replacement), plus the test matrix in §6.5.1 |
| **8** | **CR-18 / D-15 — theme selector labels** | Product/UX | Acceptance wording only | Product owner | Recommend accepting **"Light" / "Dark"** in the admin selector. `Q77` explicitly discourages patching that selector, the string is admin-only, and no patient or clinician sees it |
| **9** | **CR-10 / D-12 — `border.strong` and dark `surface.input`** | Token governance | Token contract freeze | Token owner | Ratify the two derivations into the canonical token contract before themes are generated |

**Not blocking Phase 2.** Items 1, 3, 4, 5, 6 are external inputs; items 2, 7, 8, 9 are decisions that can
be taken in parallel with building the module skeleton, token model and validator.

### 1.3 Statement of compliance

No item above requires reopening a locked decision, and no item changes any Group 2 **action** assigned in
R4 §16.2. CR-7 and CR-8 change only the **delivery mechanism** in the direction Invariant 4 mandates
(fewer core edits). CR-3 rejects one row of a **draft** document (R5 `14-…md` is marked `DRAFT`) on
runtime evidence. **Planning proceeds.**

---

## 2. Governing constraints digest

Everything below is quoted or derived from R1/R2/R4 and is treated as non-negotiable input.

### 2.1 The eight Group 2 constraints (R4 §20 register D)

| # | Constraint | Source | How this plan satisfies it |
|---|---|---|---|
| C1 | No arbitrary tenant CSS/JS/PHP; `sites/<site>/config.php` must never be a branding seam | Invariant 9, `MVP-010`, `Q76` | The layer accepts **only** allowlisted token keys with typed values and approved logo binaries. No free-text CSS field exists anywhere in the data model. CI guard rejects any read of `sites/*/config.php` from branding code (§4.3 WP-2.7). |
| C2 | Per-tenant branding = validated tokens + approved logos over a shared immutable bundle | Q34, Q59, `Q76` | Two-tier token model (§3.4); logos resolve through `LogoService`'s existing per-site path. |
| C3 | Exactly two Saudi variants (light + dark, RTL-capable); surplus themes absent from build output | `Q77` | Webpack entry-map pruning of 16 entries (§3.7.2). |
| C4 | Prefer configuration / assets / modules / upstream PRs over core edits | Invariant 4 | Extension-point map (§3.6) routes 127 of 136 BRAND IDs through non-core mechanisms; the residual mandatory core-edit set is 9 IDs across 6 files (§5.4). |
| C5 | Control Plane is source of truth; tenant `globals` is a materialisation target; no Control Plane call during page rendering | `Q76`, CP §2/§8/§10 | Materialisation plane is strictly out-of-request (§3.2); runtime plane reads only `globals` + filesystem. A CI guard forbids any HTTP client in the runtime namespace (§4.3 WP-2.7). |
| C6 | Do not change `SessionUtil` identity constants for branding | Q17 | PRESERVE; CI guard. |
| C7 | Do not alter GPL headers, CMS-1500/UB-04, card marks, ONC claims | Legal | PRESERVE; the asset overlay has an explicit deny-list (§5.2). |
| C8 | Branding cache keys must carry a tenant-safe revision preventing stale or cross-tenant branding | `Q76`, `MVP-010` | `BrandingRevision` in every branding URL, in the token CSS filename, and in the SMART response (§3.8). |

### 2.2 `MVP-010` acceptance criteria → plan location

| AC | Criterion | Delivered by |
|---|---|---|
| 1 | Tenant can change approved logo and tokenized palette only | §3.4 token allowlist + §3.2 asset plane; A3/A4 |
| 2 | Invalid CSS/value payloads are rejected | §4.3 WP-2.2 `TokenValidator`; A3 |
| 3 | No tenant-uploaded CSS/JS is executed | §3.9 threat model; A4 |
| 4 | Cache keys/revisions prevent cross-tenant bleed | §3.8; A1/A2 |
| 5 | (Q76) No Control Plane request during page rendering | §3.2 plane separation; V-01 |
| 6 | (Q76) Tenant-scoped, idempotent materialisation; `globals` never the source of truth | §4.4 WP-2.8/2.9; V-02 |
| 7 | (Q76) CP unavailable → last-good branding; failed materialisation leaves previous revision intact | §3.2 materialisation transaction model; V-03 |
| 8 | (Q77) Deployed `public/themes/` holds only the two variants; four surplus themes absent and unselectable | §3.7.2; V-04 |
| 9 | (R-SMART-DARK) SMART style endpoint returns dark tokens for a dark theme | §3.7.5; A8 |

### 2.3 `MVP-014` branding criterion → plan location

> *(Q76) The branding model stores authoritative tokens, approved logo references and a per-tenant branding
> revision, and exposes a tenant-scoped idempotent materialisation path that runs outside the OpenEMR
> request path.*

Delivered by §3.3 (Control Plane–side component set) and §4.4. The Control Plane service itself is
`MVP-014`'s deliverable; this plan specifies **the branding contract it must expose** and builds the
tenant-side receiver so the two can be integrated when `MVP-014` lands (dependency D-5).

### 2.4 The two-tier token model (resolves CR-5 / CR-6)

| Tier | Contents | Authority | Storage | Materialisation | Per-request cost |
|---|---|---|---|---|---|
| **Tier 1 — Product tokens** | The whole Thiqa palette, typography scale, radii, spacing (`brand/tokens/thiqa-tokens.json`, `brand/typography/typography-tokens.json`) | Brand/design owner, via the repository | Compiled into `public/themes/style_{light,dark}.css` as SCSS values **and** as `:root` CSS custom properties | Build time (immutable bundle) | **Zero** |
| **Tier 2 — Tenant overlay** | A strictly allowlisted subset (initially: primary/secondary interactive colours, link colours, navbar accents — never structural or semantic-state colours) | Control Plane (PostgreSQL 18) | Authoritative in CP; materialised into tenant `globals` + a generated per-site CSS-variable file | Out-of-request sync (`Q76`) | One extra `<link>` (static file), served with a revision cache key |

Tier 2 ships **empty by default**, so the Thiqa product renders exactly the certified design until a tenant
is deliberately granted an override. This is what reconciles `10-channel-evidence.md` with `MVP-010` AC-1.

---

# PHASE 1 — BRANDING ARCHITECTURE DESIGN

**Objective:** produce a complete, reviewed architecture before a single line is written. Phase 1 output is
design documentation and interface contracts only.

## 3.1 Architectural principles

| # | Principle | Consequence |
|---|---|---|
| P1 | **One layer, one owner.** All branding knowledge lives behind a single module and a single service facade. | No other component may read a branding global directly; they call `BrandingService`. |
| P2 | **Isolation over intrusion.** The layer attaches to OpenEMR through *published extension points* only. | Every attachment is an event listener, a Twig loader path, a per-site asset drop, a validated global, or a build-config entry. |
| P3 | **Core edits are a last resort and each one is a numbered record.** | Residual core edits are enumerated, justified, sized for rebase, and each carries an upstream-PR intent (Q1, Invariant 4). |
| P4 | **Parse at the boundary; never re-validate downstream.** | Raw token JSON is parsed once into typed value objects; the renderer cannot receive an invalid colour. |
| P5 | **The request path is read-only and dependency-free.** | Runtime reads `globals` (already loaded once per request) and the filesystem. No network, no Control Plane, no cache warm-up. |
| P6 | **Fail to last-known-good, never to upstream identity.** | If materialisation fails, or a token file is missing, the tenant keeps its previous branding; the layer never renders an OpenEMR-branded fallback. |
| P7 | **Revision-stamp everything a browser can cache.** | Every branding URL carries the tenant branding revision, in addition to the existing `?t=<mtime>`. |
| P8 | **Multi-tenant from day one, white-label ready by construction.** | Nothing in the runtime resolves a value by anything other than "the current site's own state". |

## 3.2 The Branding Layer — five planes

The layer is deliberately split into five planes with one-directional dependencies. This is the core
architectural decision of Phase 1.

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ PLANE 1 — AUTHORITY (Control Plane, PostgreSQL 18)          [MVP-014 owns]   │
│   saas_branding_profile · saas_branding_token · saas_branding_asset_ref      │
│   saas_branding_revision · saas_branding_materialisation_log                 │
│   Validates → assigns revision → emits a materialisation job.                │
└───────────────┬──────────────────────────────────────────────────────────────┘
                │  out-of-band, tenant-scoped, idempotent  (NEVER on a request path)
                ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ PLANE 2 — MATERIALISATION (CLI / worker, tenant-scoped)                      │
│   BrandingMaterialiser: transaction = { globals rows, token CSS file,        │
│   logo binaries, revision bump } — all-or-nothing, retryable, audited.       │
│   Runs as `bin/console --site=<tenant> saas:branding:materialise`.           │
└───────────────┬──────────────────────────────────────────────────────────────┘
                │  writes only into the tenant's own scope
                ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ PLANE 3 — RUNTIME RESOLUTION (in-request, read-only)                         │
│   BrandingService reads the already-loaded globals + filesystem.             │
│   Zero network. Zero DB queries beyond the one OpenEMR already performs.     │
└───────────────┬──────────────────────────────────────────────────────────────┘
                │
     ┌──────────┴───────────┬──────────────────────┬─────────────────────┐
     ▼                      ▼                      ▼                     ▼
┌───────────┐        ┌─────────────┐       ┌──────────────┐      ┌──────────────┐
│ PLANE 4a  │        │  PLANE 4b   │       │   PLANE 4c   │      │  PLANE 4d    │
│ HTML head │        │   Twig      │       │  Logo slots  │      │  Machine     │
│ StyleFil- │        │ overrides + │       │ LogoFilter-  │      │  contracts   │
│ terEvent  │        │ extension   │       │ Event        │      │  (SMART/FHIR)│
└───────────┘        └─────────────┘       └──────────────┘      └──────────────┘
                                    ▲
┌───────────────────────────────────┴──────────────────────────────────────────┐
│ PLANE 5 — SHARED IMMUTABLE BUNDLE (build artefact, identical for all tenants)│
│   public/themes/style_{light,dark}.css (+ compact/rtl/rtl_compact)           │
│   public/assets/fonts/thiqa/*.woff2 · product logo/favicon overlay           │
│   Generated from brand/tokens/*.json by a fork-owned build step.             │
└──────────────────────────────────────────────────────────────────────────────┘
```

**Dependency rule (enforced by a PHPStan rule, §4.3 WP-2.7):** Plane 3 may not reference Plane 1 or Plane 2
classes; Plane 2 may not be reachable from any web entry point.

### 3.2.1 Why these planes (evidence)

| Design force | Evidence | Resulting plane boundary |
|---|---|---|
| The whole `globals` set is read once per request with **no cache layer** | `interface/globals.php:457`; `OEGlobalsBag` has no cache | Materialising into `globals` costs **zero** extra per-request work → Plane 2 writes, Plane 3 reads for free |
| A runtime read-through would need a bootstrap change and a CP credential in the tenant runtime | `Q76` §Evidence; CP §8 | Plane 1 is unreachable from Plane 3 by construction |
| Logos already resolve per-site, per-request, with `?t=<mtime>` cache-busting and a filter event | `src/Services/LogoService.php:75-159`, `:100-107` | Plane 4c needs no new resolution mechanism, only a filter listener |
| Head assets can be injected by modules without touching core | `src/Core/Header.php:106-108`; `StyleFilterEvent` | Plane 4a exists and is the *only* clean global CSS injection point |
| Twig environments dispatch a creation event, so a module can register a **namespaced** template path; render paths dispatch `TemplatePageEvent`, so a listener can redirect a render to that namespace | `src/Common/Twig/TwigContainer.php:89-90`; `SMARTAuthorizationController.php:359`; `interface/login/login.php:272` | Plane 4b can substitute templates with zero tracked-file change **and** without unnamespaced shadowing (`Q38`) |
| Theme selection is a filename gated only by `file_exists()`; the selector is a filesystem scan | `interface/globals.php:474-483`; `interface/super/edit_globals.php:714-732` | Plane 5 (build output) is the correct control point for `Q77` |

### 3.2.2 The one hard constraint on Plane 4a

`StyleFilterEvent::setStyles()` passes every path through
`ModulesApplication::filterSafeLocalModuleFiles()`, which resolves the real path and **accepts it only if
it lives under `interface/modules/`** (`src/Core/ModulesApplication.php:240-292`). Therefore:

> The generated per-tenant token CSS file **must physically reside under the branding module's directory**.

This is a genuine, evidence-based constraint, not a preference. It does **not**, however, force a writable
directory — see the revised option table below (CR-19), where the recommended route emits the CSS from a
module endpoint and writes nothing.

**Three options were considered. The recommendation was revised to (a) on 2026-08-09 after the external
audit (CR-19).**

| Option | Mechanism | Verdict |
|---|---|---|
| **(a) Module PHP endpoint emitting `text/css`** | `…/oe-module-thiqa-branding/public/branding-tokens.php?rev=<n>`; reads the already-loaded `globals`, emits `--key: value;` pairs, sends `Cache-Control: public, max-age=31536000, immutable` | **RECOMMENDED (revised).** Requires **no writable directory at all**, so the container image stays read-only and immutable — which is what `Q39`/the OCI-artifact model assumes. With an immutable cache header keyed on the revision, a browser fetches it **once per revision**, not once per page. The earlier objection about site resolution does not hold: the endpoint takes no `site` parameter and inherits the session/host resolution every other module entry point uses, so it cannot be used to switch tenant context (Q12 / `BLK-005` unaffected). |
| (b) Static file written at materialisation time | `…/public/branding/<site_id>/tokens.css`, atomic temp-file + rename | **Demoted to fallback.** Clean cache semantics, but it requires runtime writes beneath installed module code, weakening supply-chain and incident-response guarantees for signed images. Retain only where a platform prefers a genuinely static asset; then mount **only** that exact path read-write, deny script execution, enforce a MIME/filename allowlist, and use atomic revision directories. |
| (c) Inline `<style>` in the head | No head-HTML injection event exists for the main shell (only `Header::setupHeader()` string output); would need a core patch | **Rejected** — breaches Invariant 4 for no benefit |

**Consequence of the revision:** dependency **D-8 (writable, execution-denied volume) is eliminated** on
the recommended path. It returns only if a deployment chooses fallback (b).

Under option (b), when Tier 2 is empty (the default) **no file is emitted and no `<link>` is added at
all** — the product renders purely from the immutable bundle, and the branding layer adds literally zero
bytes to the page.

## 3.3 Component inventory

### 3.3.1 Tenant-side (this plan builds)

| Component | Plane | Responsibility |
|---|---|---|
| `Bootstrap` (`openemr.bootstrap.php` + `src/Bootstrap.php`) | wiring | Registers every listener; the only place the module touches global state |
| `BrandingService` | 3 | **The single service.** Facade over configuration, tokens, assets, theme and product identity |
| `BrandingConfig` (readonly DTO) + `BrandingConfigFactory` | 3 | Parses the branding globals into one immutable typed object, once per request |
| `BrandingGlobalKey` (backed enum) | 3 | The closed set of globals the layer owns — the *central configuration registry* |
| `TokenKey` (backed enum) + `TokenSet` + `ColorValue` + `DesignToken` | 3/2 | Typed token model; `TokenKey` is the allowlist that makes C1 enforceable |
| `TokenSetParser` / `TokenValidator` | 2 | Boundary parsing and rejection of invalid payloads (`MVP-010` AC-2) |
| `CssVariableRenderer` | 2 | `TokenSet` → CSS custom-property text; the only component that emits CSS |
| `ContrastCalculator` | 2 | WCAG 2.2 relative-luminance implementation used as a **validation gate**, not documentation |
| `ThemeVariant` (unit enum) + `ThemeResolver` | 3 | `css_header` → Light/Dark, RTL-aware; the single answer to "which variant am I in" |
| `LogoSlot` (backed enum) + `BrandAssetResolver` | 3 | The nine runtime lookups as a closed set; wraps `LogoService`, adds the revision |
| `BrandingRevision` (domain primitive) | 2/3 | Monotonic, tenant-scoped; participates in every cache key |
| `SmartStyleMapper` | 3 | `TokenSet` → the 12-key SMART contract, for both variants |
| `BrandingMaterialiser` + `TokenCssWriter` + `MaterialisationResult` | 2 | Idempotent, all-or-nothing application of a revision |
| Listeners: `StyleInjection`, `LogoOverride`, `TwigOverride`, `LoginTemplate`, `GlobalsRegistration` | 4 | The five attachment points |
| `BrandingTwigExtension` | 4b | `brandingToken()`, `brandLogo()`, `productName()` for templates |
| Console commands | 2 | `materialise`, `verify`, `export-tokens`, `diff` |

### 3.3.2 Control-Plane-side (this plan specifies; `MVP-014` builds)

| Table (`saas_branding_*` per Q58) | Purpose |
|---|---|
| `saas_branding_profile` | One row per tenant: current revision, status, updated_at, updated_by |
| `saas_branding_token` | `(tenant_id, token_key, token_value)` — token_key constrained to the published allowlist |
| `saas_branding_asset_ref` | `(tenant_id, logo_slot, storage_reference, checksum, dimensions, content_type)` — **reference only**, never bytes |
| `saas_branding_revision` | Append-only revision history enabling rollback and audit |
| `saas_branding_materialisation_log` | Per-attempt outcome, retryable, per `Q76`'s "failed revision is retryable/auditable" |

Contract requirements the Control Plane must honour (from `Q76`): validation happens **before** a revision
is issued; a revision is atomic; the tenant runtime never holds a CP credential; assets are stored in the
tenant's own file/object scope with the CP holding only the reference and checksum.

## 3.4 Central configuration model

### 3.4.1 Token contract (Tier 1 + Tier 2)

Source of truth: `brand/tokens/thiqa-tokens.json` (light + dark) and
`brand/typography/typography-tokens.json`. Phase 1 freezes a **canonical token key list** derived from
those files, resolving the two gaps from CR-10:

| Category | Keys | Tier 2 overridable? |
|---|---|---|
| `brand.*` | navy, coral, coralDeep, sage, sky, amber, critical | No (identity) |
| `background`, `surface`, `surfaceSunken`, `surfaceRaised` | 4 | No (structural) |
| `border`, `divider` | 2 | No |
| **`border.strong`** (**new**, derived from `text.secondary`) | 1 | No |
| `text.*` | primary, secondary, disabled, inverse | No |
| `semantic.{success,warning,critical,info}.{bg,text,border}` | 12 | **No — clinical safety.** Status colours must not vary per tenant |
| `interactive.primary.{default,hover,active,disabled,textOn}` | 5 | **Yes** |
| `interactive.secondary.{default,hover,textOn}` | 3 | **Yes** |
| `interactive.focusRing` | 1 | Yes (contrast-gated) |
| `link.{default,hover}` | 2 | **Yes** (contrast-gated) |
| **`surface.input`** (**new**, dark = `dark.surface`; on raised cards = `dark.background`) | 1 | No |
| Typography family/weight/scale | per `typography-tokens.json` | No |

Every Tier 2 override passes `TokenValidator`, which enforces: key ∈ allowlist; value matches
`^#[0-9A-Fa-f]{6}$`; and **the resulting pair passes its WCAG gate** (text ≥ 4.5:1, UI ≥ 3:1) computed by
`ContrastCalculator`. A tenant therefore *cannot* configure an inaccessible palette — this is the
mechanism that turns `MVP-010` AC-2 from a promise into a property.

### 3.4.2 Globals registry (the materialisation target)

The layer owns a closed set of `globals` keys, declared once in `BrandingGlobalKey`:

- **Inherited OpenEMR globals it materialises** — the 33 branding-relevant globals of R4 §3, corrected per
  CR-2/CR-3/CR-4.
- **New layer-owned globals** (registered through `GlobalsInitializedEvent`, so `library/globals.inc.php`
  is never patched):
  | Key | Type | Purpose |
  |---|---|---|
  | `saas_branding_revision` | integer | Cache key for every branding URL (C8) |
  | `saas_branding_tokens_light` | JSON string (validated, allowlisted) | Tier 2 light overlay; empty by default |
  | `saas_branding_tokens_dark` | JSON string (validated, allowlisted) | Tier 2 dark overlay; empty by default |
  | `saas_branding_product_name_ar` | string | Arabic product name for RTL surfaces |
  | `saas_branding_tenant_display_name` | string | Tenant lockup text (EN) |
  | `saas_branding_tenant_display_name_ar` | string | Tenant lockup text (AR) |
  | `saas_branding_materialised_at` | ISO-8601 string | Observability / staleness detection |

  Naming follows `Q58` (`saas_` reserved prefix). These are **materialisation targets**; the Control Plane
  remains authoritative (C5).

### 3.4.3 Configuration precedence (single, documented rule)

```
Tier-1 product token  (build-time, shared immutable bundle)
      ⤶ overridden by ⤷
Tier-2 tenant overlay (validated, allowlisted, revision-stamped)
      ⤶ overridden by ⤷
Per-user theme choice (existing user_settings mechanism — variant selection ONLY, never token values)
```

No other precedence exists. Notably **no per-site CSS, no `sites/<site>/config.php` participation, and no
per-role branding** (R4 G-04r proved branding is role-agnostic).

## 3.5 Folder structure

### 3.5.1 Module tree (new; installs to a unique last path segment per Q37)

Composer package `saas/oe-module-thiqa-branding`, type `openemr-module` → installs to:

```
interface/modules/custom_modules/oe-module-thiqa-branding/
├── openemr.bootstrap.php            # required entry name (ModulesApplication::CUSTOM_MODULE_BOOSTRAP_NAME)
├── composer.json                    # saas/oe-module-thiqa-branding, unique final segment (Q37)
├── info.txt · README.md · version.php
├── src/
│   ├── Bootstrap.php
│   ├── Config/          BrandingConfig · BrandingConfigFactory · BrandingGlobalKey
│   ├── Token/           TokenKey · DesignToken · ColorValue · TokenSet · TokenSetParser
│   │                    TokenValidator · ContrastCalculator · CssVariableRenderer
│   ├── Theme/           ThemeVariant · ThemeResolver · SmartStyleMapper
│   ├── Asset/           LogoSlot · BrandAssetResolver · BrandingRevision
│   ├── Service/         BrandingServiceInterface · BrandingService
│   ├── Materialisation/ BrandingMaterialiser · TokenCssWriter · MaterialisationResult
│   ├── Listener/        StyleInjectionListener · LogoOverrideListener · TwigOverrideListener
│   │                    LoginTemplateListener · GlobalsRegistrationListener
│   ├── Twig/            BrandingTwigExtension
│   └── Console/         MaterialiseCommand · VerifyCommand · ExportTokensCommand
├── templates/                       # Twig namespace @oe-module-thiqa-branding (Q38)
│   ├── api/smart/smart-style_light.json.twig   # core-path override (tokenised)
│   ├── api/smart/smart-style_dark.json.twig    # core-path override (NEW — R-SMART-DARK)
│   └── login/partials/html/primary_logo.html.twig  # core-path override (alt text, CR-8)
├── public/
│   ├── branding-tokens.php             # RECOMMENDED (CR-19): emits text/css from globals; no writes
│   └── branding/<site_id>/tokens.css   # FALLBACK route only (b): generated, needs a writable mount
├── sql/                              # only if CP-side mirror tables are needed tenant-side
└── tests/                            # isolated (no DB) + integration
```

### 3.5.2 Brand source tree (exists; becomes build input)

`brand/` stays exactly as certified — it is **source, never served**. Nothing under `brand/` is
web-reachable; the build copies derived artefacts out of it. `brand/manifests/SHA256SUMS` becomes a
**release gate** (§7.3).

### 3.5.3 Theme source additions (new files only; no upstream SCSS modified)

```
interface/themes/
├── oe-styles/
│   ├── style_light.scss            # UPSTREAM — untouched
│   ├── style_dark.scss             # UPSTREAM — untouched
│   ├── style_thiqa_light.scss      # NEW fork-owned entry → compiles to style_light.css
│   └── style_thiqa_dark.scss       # NEW fork-owned entry → compiles to style_dark.css
└── thiqa/                          # NEW fork-owned partials
    ├── _tokens-light.scss          # GENERATED from brand/tokens/thiqa-tokens.json
    ├── _tokens-dark.scss           # GENERATED
    ├── _typography.scss            # GENERATED from typography-tokens.json + @font-face
    ├── _css-variables.scss         # :root custom properties (both --oe-* compat and --thiqa-*)
    └── _overrides.scss             # hand-authored component corrections
```

**Why new entry files rather than editing the upstream themes:** `webpack.themes.js` maps entry *name* →
output filename, so `style_light: entry("oe-styles/style_thiqa_light.scss")` still produces
`public/themes/style_light.css`. This keeps `css_header`, `config/config.yaml`, the `file_exists()`
fallback and the admin selector working unchanged (CR-9) while leaving both upstream theme files
byte-identical for rebase. The new entry files **must** live in `oe-styles/` because
`webpack/loaders/sass-bsimport-loader.js:28-31` decides relative import depth from the path containing
`/oe-styles/` or `/colors/`, and **must** contain the `// bs4import` marker, which the loader replaces per
variant — the loader only processes the entry file, not its imports.

### 3.5.4 Generated/derived output (build artefacts, gitignored)

```
public/themes/style_light.css · style_dark.css · compact_* · rtl_* · rtl_compact_*
             tabs_style_* · style_pdf.css · directional.css · misc/*      (Q77-pruned set)
public/assets/fonts/thiqa/*.woff2                                        (8 files, sync step)
public/images/logos/**                                                   (product-default overlay)
public/images/favicon.ico                                                (BRAND-029 — new file)
```

## 3.6 Extension-point map (the heart of the isolation strategy)

Every attachment is a **published** OpenEMR seam with a source citation.

| # | Seam | Source evidence | Used for | Core edit? |
|---|---|---|---|---|
| E1 | `StyleFilterEvent` (`html.head.style.filter`) | `src/Core/Header.php:106-108`; `src/Events/Core/StyleFilterEvent.php` | Inject the Tier 2 token CSS `<link>` on every `setupHeader()` page | No |
| E2 | `ScriptFilterEvent` | `src/Core/Header.php:102-104` | Reserved; **not used** (no branding JS is required) | No |
| E3 | `LogoFilterEvent` (`logo.filter.url`) | `src/Services/LogoService.php:100-107` | Slot-level logo redirection (e.g. dark-variant navbar mark) | No |
| E4 | `TwigEnvironmentEvent::EVENT_CREATED` → `FilesystemLoader::addPath($dir, '<module_slug>')` — **namespaced only** (`Q38`); combined with E6 to redirect a render into the namespace | `src/Common/Twig/TwigContainer.php:89-90` | Supply the SMART light/dark contracts from the module namespace. *(The unnamespaced `prependPath()` shadowing used by `oe-module-claimrev-connect` is **not** adopted — see CR-7/CR-17.)* | No |
| E5 | `TemplatePageEvent::RENDER_EVENT` (login) | `interface/login/login.php:268-273` | Supply branding view variables to the login layout | No |
| E6 | `TemplatePageEvent` (SMART/OAuth2 render paths) | `SMARTAuthorizationController.php:334-337,359-362`; `AuthorizationController.php:953-957`; `ClientAdminController.php:245-249` | Variant-aware template selection for authorisation screens | No |
| E7 | `GlobalsInitializedEvent` (`globals.initialized`) | `src/Events/Globals/GlobalsInitializedEvent.php:28` | Register the seven `saas_branding_*` globals without patching `library/globals.inc.php` | No |
| E8 | `LogoService` per-site path (`OE_SITE_DIR/images/logos/<slot>/logo.*`, last-match-wins) | `src/Services/LogoService.php:75-108,141-159` | Per-tenant logo binaries | No |
| E9 | `webpack.themes.js` entry map | `Q77` §Evidence; `webpack.themes.js` | Two-variant theme surface; Thiqa entry files | Fork-owned **build config** |
| E10 | `scripts/sync-css.js` sibling step | `package.json:12` | Font + product-asset overlay | Fork-owned **build config** |
| E11 | `globals` table rows | `interface/globals.php:457` | Materialisation target for all SET-CONFIG items | No (data) |
| E12 | `lang_definitions` / `xl()` catalogue | R4 BRAND-101–104; `MVP-004` | All SET-TRANSLATION items | No (data) |
| E13 | Twig `getLogo(type, filename)` | `src/Common/Twig/TwigExtension.php:274-279` | Template-level slot access | No |

**Deliberately unused seams:** `sites/<site>/config.php` (**PROHIBITED** — C1/BRAND-120) and
`sites/<tenant>/documents/theme/` (**no runtime behaviour** — Q59/BRAND-084).

**Prohibited mechanism (standing pattern, resolves CR-17 / D-14).** SaaS modules must register Twig paths
**only** under their own namespace (`addPath($dir, '<module_slug>')`) and substitute templates by rewriting
the template name to `@<module_slug>/…` from a `TemplatePageEvent` listener.
`FilesystemLoader::prependPath()` into the main namespace is **prohibited** — it is unnamespaced and
resolution-order dependent, which is the fragility `Q38` exists to remove. Note the two dispatch keys
differ: the SMART path dispatches `TemplatePageEvent` **unnamed** (listen on the class), while login
dispatches `TemplatePageEvent::RENDER_EVENT`. A listener registered on only one will silently miss the
other. CI enforces this (WP-2.7).

## 3.7 Theme, token and asset build architecture

### 3.7.1 Token pipeline (single source of truth → four consumers)

```
brand/tokens/thiqa-tokens.json  +  brand/typography/typography-tokens.json
                    │
                    ▼   tools/branding/generate-tokens  (new, fork-owned, deterministic, CI-verified)
   ┌────────────────┼─────────────────┬──────────────────────┬────────────────────┐
   ▼                ▼                 ▼                      ▼                    ▼
_tokens-light   _tokens-dark    _css-variables.scss   smart-style_{light,       PDF token
   .scss           .scss        (:root --thiqa-*,      dark}.json.twig          partial for
                                 --oe-* compat)        (12-key contract)        style_pdf
```

This satisfies R4 §16.1's requirement that *"SMART tokens must derive from the same design-token source as
the web CSS variables, not be maintained separately"*. The generator is **idempotent** and CI re-runs it
and fails on any diff, so hand-editing a generated file cannot silently drift.

### 3.7.2 `Q77` theme-surface enforcement (build layer, no core patch)

Remove **16** entries from `webpack.themes.js` — the four surplus themes × four variants each:

| Theme | Entries removed |
|---|---|
| `solar` | `style_solar`, `compact_style_solar`, `rtl_style_solar`, `rtl_compact_style_solar` |
| `manila` | `style_manila`, `compact_style_manila`, `rtl_style_manila`, `rtl_compact_style_manila` |
| `cobalt_blue` | `style_cobalt_blue`, `compact_style_cobalt_blue`, `rtl_style_cobalt_blue`, `rtl_compact_style_cobalt_blue` |
| `forest_green` | `style_forest_green`, `compact_style_forest_green`, `rtl_style_forest_green`, `rtl_compact_style_forest_green` |

Retained: `style_light`, `style_dark` (+ compact/rtl/rtl_compact = 8 entries, repointed to the Thiqa entry
files), `tabs_style_full`, `tabs_style_compact`, `rtl_tabs_style_full`, `rtl_tabs_style_compact`, `style`,
`style_pdf`, `directional`, and the 10 `misc/*` entries — all *required non-selectable artifacts* that
`Q77` explicitly keeps.

Upstream `style_solar.scss`, `style_manila.scss`, `style_cobalt_blue.scss` and `style_forest_green.scss`
**remain in the repository** for rebase compatibility, exactly as `Q77` permits; only the build output is
constrained. Because `interface/globals.php:476` gates on `file_exists()`, a stale `globals` or
`user_settings` value pointing at a removed theme falls back to `style_light.css` automatically — the
existing gate enforces the two-variant surface with **no core patch**, which is precisely why `Q77` chose
this option.

**Rebase guard:** CI asserts the entry map contains exactly the approved set, so a future upstream theme
cannot enter the product implicitly (`Q77` §Upgrade behaviour).

### 3.7.3 RTL

RTL remains OpenEMR's filename-substitution mechanism (`rtl_<name>.css`, `interface/globals.php:551-611`),
driven by `language_direction`. The Thiqa entries build all four variants, so `rtl_style_light.css`,
`rtl_compact_style_light.css` and the dark equivalents all exist. **No `globals` value is ever set to an
`rtl_`-prefixed filename** (CR-3). `Q24`'s vendored bootstrap-rtl requirement is inherited unchanged;
the branding layer adds no new RTL dependency. Logo mirroring is not required — the Thiqa symbol is
horizontally symmetric (R5 `09-rtl-bilingual-evidence.md` §Logo mirroring).

### 3.7.4 Typography

- 8 × woff2 (Inter 400/500/600/700, IBM Plex Sans Arabic 400/500/600/700), SIL OFL 1.1.
- Delivered to `public/assets/fonts/thiqa/` by a new fork-owned sync step invoked from `build:sync`
  (`package.json:12`). `public/assets/*` is gitignored except `modified/`, so no binaries enter the tracked
  tree and no upstream directory is repurposed (CR-13).
- `@font-face` blocks generated into `interface/themes/thiqa/_typography.scss` with the `unicode-range`
  split already authored in `brand/typography/thiqa-fonts.scss`, so Arabic glyphs load only on Arabic
  content.
- `$font-family-sans-serif` is overridden **in the Thiqa entry files before `@import "../default-variables"`**
  — the upstream declaration is `!default` (`interface/themes/default-variables.scss:85`), so no upstream
  file is edited. This retires BRAND-125 (Lato declared, never shipped).
- **PDF fonts are a separate, locked requirement — corrected 2026-08-09 (CR-16).** `Q25` names **Amiri
  and/or Noto Naskh Arabic** for PDF and requires the PDF engines to be configured explicitly. An earlier
  revision of this section proposed IBM Plex Sans Arabic TTF/OTF for PDF; that would not satisfy `Q25`.
  The product depends on **two** PDF engines (`mpdf/mpdf ^8.2.7` and `dompdf/dompdf ^3.1.4`), both of which
  must be configured. IBM Plex Sans Arabic remains the **web** face (no locked decision governs the web
  Arabic font); Amiri and/or Noto Naskh Arabic must be added for print. **Dependency D-9.**

### 3.7.5 SMART style contract (R-SMART-DARK)

`smartAppStyles()` derives `coreTheme` from `basename(css_header)` and resolves
`/api/smart/smart-<coreTheme>.json.twig`, falling back to the light template
(`SMARTAuthorizationController.php:419-434`). Delivering `smart-style_dark.json.twig` on a module-prepended
Twig path therefore fixes the runtime-proven dark defect with **zero core modification** (CR-7). Both
templates are generated from the same token source, using the 12-key mapping already specified in R5
`10-channel-evidence.md`:

| Key | Light | Dark |
|---|---|---|
| `color_background` | `#FAFAF8` | `#0B1220` |
| `color_error` | `#8E271D` | `#F29088` |
| `color_highlight` | `#3E7FBD` | `#8FC1EE` |
| `color_modal_backdrop` | `#0B1B4D` @60% | `#000000` @60% |
| `color_success` | `#2F6B45` | `#8FD1A6` |
| `color_text` | `#0B1B4D` | `#F5F6F8` |
| `dim_border_radius` / `dim_font_size` / `dim_spacing_size` | `6px` / `14px` / `20px` | same |
| `font_family_body` / `font_family_heading` | `'Inter','IBM Plex Sans Arabic',sans-serif` | same |
| `logo_primary` | absolute URL via `LogoService` + revision | dark-variant mark via `LogoFilterEvent` |

Also fixed in the same override: the response is currently **double-JSON-encoded** (R4 §11.2) — the
override templates keep the existing wire format unless the Phase 4 client-compatibility check clears a
change, since third-party SMART apps may depend on the observed behaviour.

### 3.7.6 Product asset overlay

Rather than committing replaced binaries over upstream defaults, the build/deploy step copies the approved
kit assets into place (mirroring `Q77`'s build-layer philosophy):

| Target | Source (kit) | BRAND |
|---|---|---|
| `public/images/logos/core/login/primary/logo.png` | `brand/logos/login/login-primary-1053x390.png` | 014 |
| `…/core/login/secondary/logo.png` | `login-secondary-300x100.png` | 015 |
| `…/core/login/small_logo_{1,2}/logo.png` | `login-small-{a,b}-101x100.png` | 016/017 |
| `…/core/menu/primary/logo.svg` | `brand/master/brand-symbol.svg` (legible at 16 px — `main.php:48` renders `height="16"`) | 018 |
| `…/core/favicon/favicon.ico` | `brand/favicon/favicon.ico` (real 16/32/48 multi-frame) | 019 |
| `…/portal/login/primary/logo.png` | `portal-login-primary-1053x390.png` | 020 |
| `…/portal/login/secondary/logo.png` | `portal-login-secondary-300x100.png` — **closes the CR-12 gap** | 021 |
| `…/portal/menu/primary/logo.png` | `portal-navbar-870x222.png` | 022 |
| `public/images/favicon.ico` | `brand/favicon/favicon.ico` — **new file, fixes 5 × HTTP 404** | 029 |
| `public/images/{login-logo.png, logo-full-con.png, menu-logo.png, favicon-32x32.png}` | matching legacy exports | 025–028 |
| `sites/<site>/images/{login_logo.gif, logo_1.png, logo_2.png, practice_logo.gif}` | `brand/logos/legacy/*` | 030–033 |

**Deny-list (never overwritten, C7):** `public/images/cms1500.png`, `public/images/ub04.svg`,
`visa_mc_disc_credit_card_logos_176x35.gif`, and everything under `Documentation/`.

Per-tenant logos use the *same* slots under `sites/<tenant>/images/logos/…`, which win by last-match-wins
(`LogoService.php:141-159`) — so a tenant override needs no new mechanism.

## 3.8 Cache and revision model (C8, `MVP-010` AC-4)

| Surface | Cache key | Rationale |
|---|---|---|
| Logo URLs | existing `?t=<mtime>` **+** `&rev=<branding_revision>` | mtime alone can collide across a restore; the revision is tenant-scoped and monotonic |
| Tier 2 token CSS | filename path contains `<site_id>`; URL carries `?rev=<n>` | Two tenants can never resolve the same URL |
| Theme CSS | existing `?v=<v_js_includes>` | Shared immutable bundle — identical for all tenants **by design**; must **not** carry a tenant revision |
| SMART style JSON | `logo_primary` carries the revision; response is not cached by OpenEMR | Machine contract must reflect the current revision |
| Favicon | `?t=<mtime>` via `LogoService` | Already per-site |

**Invariants tested in Phase 4:** (i) no branding URL is derivable without the tenant's own site id or
revision; (ii) changing a token increments the revision, which changes every affected URL; (iii) a
rollback to revision *n−1* restores byte-identical output.

### 3.8.1 Canonical cache-key specification (resolves CR-20 / D-16)

**Principle: the tenant revision is always *added alongside* existing core cache identifiers, never
substituted for them.** Replacing `?v=` leaves stylesheets stale across an application upgrade; omitting
the revision leaves branding stale across a branding change. Both identifiers are required.

Verified core behaviour: `interface/globals.php:479-480` appends `?v=<$v_js_includes>` (**not**
`$v_database`, as one review stated) to `css_header` and `compact_header`.

| Asset | URL form | Owner |
|---|---|---|
| Theme CSS (shared bundle) | `…/public/themes/<file>.css?v=<v_js_includes>` — **no tenant revision**; the bundle is identical for all tenants by design and must stay cacheable across them | Core, unchanged |
| Tier 2 tenant token CSS | `…/oe-module-thiqa-branding/public/branding-tokens.php?rev=<branding_revision>`, served `Cache-Control: public, max-age=31536000, immutable` | Branding layer |
| Logos | `<LogoService path>?t=<mtime>&rev=<branding_revision>` | Core `?t=` preserved; `&rev=` appended |
| Favicon | `?t=<mtime>` | Core, unchanged |
| SMART style JSON | uncached; `logo_primary` carries `?t=` and `&rev=` | Branding layer |
| Fonts | `…/public/assets/fonts/thiqa/<file>.woff2` — release-addressed, no tenant component | Build |

**Rules.** (1) Never remove or overwrite a core cache parameter; only append. (2) Fixed parameter order
(`?t=` then `&rev=`; `?v=` then `&rev=`) so query-string-keyed proxies do not fragment. (3)
`branding_revision` is a monotonic tenant-scoped integer in `globals.saas_branding_revision`. (4) **No URL
may carry a tenant identifier, site name or any topology-disclosing value** — tenant scope comes from
host/session only (`Q12`, `BLK-005`). (5) Rollback to revision *n−1* must reproduce byte-identical URLs.

The 8-case acceptance matrix (upgrade, revision bump, two tenants at equal revision, rollback, CDN keying,
reordered parameters, empty Tier 2, logo replaced without a bump) is in
`docs/branding-production/16-conflict-resolutions.md` §7.3.

## 3.9 Security model

| Threat | Control | Where |
|---|---|---|
| Tenant injects CSS/JS through a branding field | No free-text field exists; `TokenKey` is a closed enum; values are `ColorValue`/enumerated units; `CssVariableRenderer` emits only `--key: value;` pairs from typed objects and can express nothing else | §3.4.1, WP-2.2 |
| Tenant escapes the CSS value context (`}`, `expression()`, `url()`) | Values never reach the renderer as strings — they are reconstructed from parsed components | WP-2.2 |
| Cross-tenant branding bleed | Site-scoped file paths + revision cache keys; `LogoService` never composes a cross-site path; session is cleared on site change (`interface/globals.php:310-322`) | §3.8, A1/A2 |
| Tenant-supplied logo is a polyglot/script file | Content-type + magic-byte + dimension validation at the Control Plane **and** at materialisation; logos land in the site image directory, which serves static files only | WP-2.9 |
| Materialisation partially applies | Staged write + atomic rename + single revision bump last; failure leaves revision *n−1* wholly intact | WP-2.8 |
| Control Plane compromise pushes hostile tokens | Allowlist + format + contrast validation is re-run **tenant-side** at materialisation; the tenant does not trust the CP blindly | WP-2.2/2.8 |
| Branding code reaches the network at render time | PHPStan rule forbidding HTTP clients in Plane 3 namespaces | WP-2.7 |
| Someone re-enables `sites/<site>/config.php` as a branding seam | PHPStan rule + code-review checklist item | WP-2.7 |

## 3.10 Forward compatibility: multi-tenant and white-label

The architecture is already multi-tenant; white-label is a **configuration expansion, not a redesign**:

| Future need | Already supported | Change required |
|---|---|---|
| Second tenant with different logos | Yes — per-site slots | None |
| Second tenant with a different accent palette | Yes — Tier 2 overlay | Populate the CP token rows |
| Full white-label (different product name, favicon, emails) | Partly | `openemr_name` is already per-site; favicon and email logo already resolve per site. Remaining: the 6 residual core-edit strings become globals or translation constants (§5.4) |
| Reseller-level branding hierarchy (reseller → tenant) | No | CP-side inheritance in `saas_branding_profile`; tenant runtime unchanged, since it only ever receives a resolved revision |
| Per-tenant custom fonts | No — deliberately | Would breach the shared-immutable-bundle rule (C2); requires a new ADR |
| Tenant self-service branding UI | No | CP-side UI over the same validated contract; no tenant-runtime change |

The single most important property: **the tenant runtime never learns that other tenants exist.** Adding
tenants changes data, not code.

## 3.11 Phase 1 deliverables and exit criteria

| Deliverable | Form |
|---|---|
| D1.1 Architecture Decision Record `ADR-BRAND-001` | Records the five-plane model, option (b) for token CSS delivery, and the new-entry-file theme strategy |
| D1.2 Token contract specification | Canonical key list, tier assignment, validation rules, `border.strong` / dark `surface.input` resolutions |
| D1.3 Interface contracts | `BrandingServiceInterface` + all value-object signatures (no bodies) |
| D1.4 Control Plane branding contract | Table shapes, revision semantics, materialisation job payload — handed to `MVP-014` |
| D1.5 Extension-point map | §3.6, with source citations, reviewed against upstream `v8_2_0` |
| D1.6 Folder structure + naming | §3.5, validated against `Q37`/`Q38`/`Q58` |
| D1.7 Threat model | §3.9, reviewed by the security owner |
| D1.8 Decision requests | CR-3, CR-9, D-1, D-2, D-3 packaged for sign-off |

**Exit criteria:** all eight deliverables reviewed; every one of the 136 BRAND IDs has a named mechanism in
the extension-point map; no unresolved conflict with R1; CR-3/CR-9 signed off.

---

# PHASE 2 — SINGLE BRANDING SERVICE AND CENTRALISED CONFIGURATION

**Objective:** build the layer itself — one service, one configuration model, zero references from the rest
of the codebase into branding internals. No BRAND item is refactored in this phase; Phase 2 delivers the
machinery Phase 3 consumes.

## 4.1 Coding standards applied (R7)

`declare(strict_types=1)` in every file; PSR-4 under `OpenEMR\Modules\ThiqaBranding\`; constructor
injection everywhere (no `new` for services, no static locators, no superglobals); `readonly` value objects
and DTOs; `final` on value objects; unit enums for runtime-only state (`ThemeVariant`), backed enums only
where the value is persisted (`TokenKey`, `LogoSlot`, `BrandingGlobalKey`); exhaustive `match` with no
`default`; `DateTimeImmutable` and injected `ClockInterface`; PSR-3 context arrays, never interpolation;
catch `\Throwable`; never expose `getMessage()` to users; PHPStan level 10 with **no new baseline entries**.

## 4.2 The single service (P1)

```php
interface BrandingServiceInterface
{
    public function config(): BrandingConfig;                 // immutable, per-request
    public function productName(?Language $language = null): ProductName;
    public function tagline(?Language $language = null): ?Tagline;
    public function themeVariant(): ThemeVariant;             // Light | Dark
    public function isRtl(): bool;
    public function tokens(ThemeVariant $variant): TokenSet;  // Tier1 ⊕ validated Tier2
    public function logo(LogoSlot $slot): BrandAsset;         // URL + revision + alt text
    public function revision(): BrandingRevision;
    public function tokenStylesheetUrl(): ?BrandingUrl;       // null when Tier 2 is empty
    public function smartStyleTokens(ThemeVariant $variant): SmartStyleContract;
}
```

Everything else in the codebase depends on **this interface only**. `BrandingConfig` is constructed once
per request by `BrandingConfigFactory` from the already-loaded `OEGlobalsBag` — no additional query.

## 4.3 Work packages

| WP | Scope | Key acceptance |
|---|---|---|
| **WP-2.1** Module skeleton | `composer.json` (unique final segment, Q37), `openemr.bootstrap.php`, `Bootstrap`, module registration, Twig namespace `@oe-module-thiqa-branding` (Q38) | Module installs via the composer plugin without colliding with a tracked module directory (`MVP-008` AC-1) |
| **WP-2.2** Token model + validator | `TokenKey`, `ColorValue`, `DesignToken`, `TokenSet`, `TokenSetParser`, `TokenValidator`, `CssVariableRenderer` | Property-based tests: no input produces CSS outside `--key: #rrggbb;`; every non-allowlisted key is rejected; every malformed value is rejected |
| **WP-2.3** Contrast gate | `ContrastCalculator` implementing WCAG 2.2 relative luminance | Reproduces all 33 pairs of R5 `08-wcag-contrast.md` to 2 dp, including the 2 known FAILs (CR-11) |
| **WP-2.4** Configuration model | `BrandingGlobalKey`, `BrandingConfig`, `BrandingConfigFactory`, `GlobalsRegistrationListener` | The 7 `saas_branding_*` globals appear in Administration without any change to `library/globals.inc.php` |
| **WP-2.5** Runtime resolution | `ThemeResolver`, `LogoSlot`, `BrandAssetResolver`, `BrandingRevision`, `BrandingService` | Zero DB queries and zero network calls added per request (measured, V-01) |
| **WP-2.6** Token generator tool | `tools/branding/generate-tokens` → SCSS partials, CSS variables, SMART twigs, PDF partial | Deterministic; CI re-runs and fails on diff; retires the `tmp/` script (CR-11) |
| **WP-2.7** Guardrails | PHPStan rules in `tests/PHPStan/Rules/`: (a) no HTTP client in Plane 3; (b) no `sites/*/config.php` reference in branding code; (c) `SessionUtil` identity constants unchanged — compare the **exact** constants and values, not any mention of the word OpenEMR (C6); (d) webpack entry map matches the `Q77` allowlist; (e) no new PHPStan baseline entries; (f) **no `FilesystemLoader::prependPath()` and no unnamespaced `addPath()` in SaaS module code** (`Q38`, CR-17); (g) no `.example` domain and no `reg.open-emr.org` reference in shipped configuration | Each rule has a failing-fixture test |
| **WP-2.8** Materialiser | `BrandingMaterialiser`, `TokenCssWriter`, `MaterialisationResult`, `saas:branding:materialise` console command | Idempotent (second run is a no-op); atomic (kill −9 mid-run leaves revision *n−1* intact); tenant-scoped (`--site=` required, refuses to run without it) |
| **WP-2.9** Asset intake | Logo validation (magic bytes, content type, dimensions vs `brand/manifests/asset-manifest.json`), atomic placement into `sites/<site>/images/logos/<slot>/` | A polyglot file is rejected; a wrong-dimension logo is rejected with a precise message |
| **WP-2.10** Listener wiring | The five listeners of §3.3.1 registered in `Bootstrap` | Each listener has an isolated test proving it is a no-op when branding is unconfigured |
| **WP-2.11** Twig extension | `brandingToken()`, `brandLogo()`, `productName()` | Twig compilation tests pass (`openemr-cmd`-independent isolated suite, per R8) |
| **WP-2.12** Observability | Structured PSR-3 logs on materialisation start/success/failure; `saas_branding_materialised_at`; a health check comparing CP revision to materialised revision | A stale tenant is detectable without querying the tenant DB by hand |

## 4.4 Materialisation transaction model (`MVP-010` AC-6/AC-7)

```
1. Receive job {tenant_id, target_revision, tokens[], asset_refs[], strings[]}
2. Re-validate everything tenant-side (do not trust the Control Plane blindly)
3. Stage:  tokens.css.tmp   ·  logo.<ext>.tmp  ·  globals delta computed in memory
4. Verify: checksums match the CP-declared values; contrast gates pass
5. Apply (ordered, each step individually reversible):
      a. rename staged assets into place (atomic per file)
      b. rename tokens.css.tmp → tokens.css     (atomic)
      c. write globals delta in a single DB transaction
      d. write saas_branding_revision = target_revision   ← LAST
6. On any failure before (d): delete staged files, roll back the DB transaction,
   log MaterialisationResult::failed(), leave revision n−1 fully active, mark retryable
7. Emit an audit event to the Control Plane (best-effort; failure here does not fail the job)
```

Because the revision is written **last** and every URL carries it, a partially-applied state is never
observable by a browser: until step (d) the tenant serves revision *n−1* consistently.

## 4.5 Testing strategy (Phase 2)

| Layer | Suite | Notes (R8: native Windows host, no Docker) |
|---|---|---|
| Token model, validator, contrast, renderer, resolvers, mappers | **Isolated** (`phpunit-isolated.xml`) | Runs on the host — `php vendor\bin\phpunit -c phpunit-isolated.xml`. This is the reliable suite on this machine |
| Twig template compile + render fixtures | Isolated | Regenerate fixtures via `composer update-twig-fixtures`; review the diff |
| Materialiser, globals registration, logo intake | Integration (DB-backed) | **Not validated on the native stack** (R8 §9) — must run in CI. Plan CI jobs accordingly (`MVP-009`) |
| Cross-tenant isolation (A1/A2) | Integration + E2E | Requires two provisioned tenants — **D-6** |
| Coverage gates | `MVP-009`: ≥80% patch, ≥60% module total | Data providers annotated `@codeCoverageIgnore` per R7 |

## 4.6 Phase 2 exit criteria

Module installs cleanly; `BrandingService` resolves complete branding for a default site; materialiser is
idempotent and atomic under a kill test; PHPStan level 10 clean with no new baseline entries; all five
guardrail rules active in CI; isolated suite green on the native host.

---

# PHASE 3 — REFACTOR THE GROUP 1 REFERENCES ONTO THE BRANDING LAYER

**Objective:** move every one of the 136 canonical BRAND items onto the layer, using the mechanism assigned
in §3.6 and honouring the single action assigned in R4 §16.2. **Core edits are minimised, not eliminated —
and every residual one is recorded.**

## 5.1 Workstream overview (136 items, 11 actions)

| WS | Action | Count | BRAND IDs (per R4 §16.2) | Mechanism |
|---|---|---:|---|---|
| WS-A | SET-CONFIG | 37 | 001–004, 013, 042–050, 054–059, 062, 064–072, 074, 075, 096, 097, 107, 109, 110 | E11 globals via materialiser |
| WS-B | REPLACE-ASSET | 20 | 014–022, 025–029, 031–033, 108, 112, 133 | E8 site slots + build overlay |
| WS-C | PATCH | 18 | 005–012, 030, 053, 087, 113, 119, 126, 130, 134, 135, 136 | Minimised — see §5.4 |
| WS-D | SET-TRANSLATION | 8 | 101–104, 127–129, 132 | E12 catalogue (executed under `MVP-004`, CR-14) |
| WS-E | TOKENIZE | 6 | 078–080, 121–123 | E4 + token pipeline |
| WS-F | BUILD-SHARED-THEME | 9 | 076, 077, 081–083, 085, 086, 111, 125 | E9/E10 build layer |
| WS-G | HIDE | 3 | 060, 061, 105 | E11 globals (`display_review_link=0`, `display_donations_link=0`, help hidden) |
| WS-H | DEFER | 8 | 034–037, 094, 095, 098, 124 | Recorded, scheduled post-MVP |
| WS-I | PRESERVE / NO-ACTION / PROHIBITED | 15 / 11 / 1 | 038–040, 063, 089–093, 114–118, 131 / 023, 024, 041, 051, 052, 073, 084, 088, 099, 100, 106 / 120 | Guardrails + explicit non-action record |

Total 37+20+18+8+6+9+3+8+15+11+1 = **136** ✔ (matches R4 §16 exactly).

## 5.2 WS-A — SET-CONFIG (37 items)

All 37 become rows in a single declarative **branding profile manifest** (YAML/JSON in the module,
version-controlled), consumed by the materialiser. Values come from R5 `14-string-replacement-map.md`
Part 1, **with the CR-2/CR-3/CR-4 corrections applied**:

- `openemr_name` → `Thiqa` (BRAND-001) — flows automatically to BRAND-002/003/004, 071, 072, 096 (HL7
  MSH-3), 097 (QRDA), 107 (prescription email subject). **D-3 gates this**: MSH-3 and QRDA are integration
  contracts; changing the sender name requires integration-owner sign-off (R4 §10.1).
- `login_tagline_text` → `Clinical confidence, connected care.` / `ثقة إكلينيكية، رعاية مترابطة.`
- `main_menu_logo_title`, `online_support_link`, `user_manual_link`, `main_menu_logo_link` → **must be set
  explicitly**; blank values leak upstream defaults (BRAND-056/058 are auto-generating). **D-2 gates the
  final URLs.**
- `display_review_link=0`, `display_donations_link=0` (WS-G).
- `css_header=style_light.css`, `portal_css_header=style_light.css` — **never `rtl_*`** (CR-3).
- `statement_logo=practice_logo.gif` with the Thiqa monochrome asset provisioned per site (BRAND-109/033).
- `facility` name (BRAND-110) is **tenant data**, set by provisioning, not by the product profile.
- Portal globals (BRAND-064–070) configured branding-correctly regardless of `portal_onsite_two_enable`
  (CR-4); `portal_onsite_two_address` is per-tenant and must match `Q12` subdomain routing (BRAND-070).

**Acceptance:** a fresh tenant, materialised from the profile, renders zero OpenEMR strings on login, main
shell, About and portal login (verified by HTML assertion, not eyeball).

## 5.3 WS-B — REPLACE-ASSET (20 items)

Two sub-tracks:

1. **Product defaults** (build overlay, §3.7.6) — guarantees no upstream mark can ever appear, satisfying
   `Q76`'s *"never fall back to an unbranded state that leaks upstream identity"*.
2. **Per-tenant** (`sites/<site>/images/logos/<slot>/`) — written by the materialiser (WP-2.9).

Special cases: BRAND-029 is a **new file**, not a replacement (fixes 5 × HTTP 404). BRAND-108 (telehealth
invitation email logo) resolves to an **absolute, publicly reachable URL** — verify it is reachable from
outside the cluster. BRAND-112 (report/receipt logos) is facility-scoped, not product-scoped — do not
overwrite with the product mark. BRAND-133 (portal reuses the **core** favicon slot) means the portal
favicon needs no separate asset.

## 5.4 WS-C — PATCH (18 items): core-edit minimisation

The starting position is 18 PATCH-actioned IDs across ~11 files. Applying the extension-point map:

| BRAND | Item | Mechanism after minimisation | Tracked-file change? |
|---|---|---|---|
| 053 | Empty `alt=""` on login logo | **Module template override** (E4) — CR-8 | **No** |
| 121–123 (TOKENIZE, delivered under §15.1 #4) | SMART light + **dark** contract | **Module template override** (E4) — CR-7 | **No** |
| 119 | Duplicate favicon `<link>` | Cosmetic; `Header::setupHeader()` emits one and a template emits another. Defer unless the duplicate breaks a client | **No (deferred)** |
| 030 | Eye-Magic hardcodes `sites/default` | Conditional — only if the Eye Magic form is enabled. Not enabled in the Saudi product ⇒ **not patched** | **No** |
| 007–012 | Installer / `sql_patch` / `sql_upgrade` / `ippf_upgrade` titles | Operator-only surfaces. **Conditional** (R4 §15.2). Recommend patching before customer-facing installs, as one grouped commit with an upstream-PR intent | Yes (6 files) — **conditional** |
| **005, 006** | `admin.php` title + heading | **Unauthenticated and runtime-verified.** Must patch | **Yes** |
| **087, 126** | FHIR `software.name`, `implementation.description` | Hardcoded literals in one method | **Yes** (1 file) |
| **113** | Registration phone-home to `reg.open-emr.org` | Repoint or disable — **D-10** | **Yes** (1 file) |
| **134** | `"OpenEMR Error: API is disabled"` | Raw, non-translatable JSON error string | **Yes** (1 file) |
| **135, 136** | Pre-bootstrap fatals (openssl / aes-256-cbc) | Emitted before translation exists | **Yes** (1 file) |
| **130** | Zend Module Installer wiki links ×2 | Hardcoded `open-emr.org/wiki` in admin UI | **Yes** (1 file) |

**Residual mandatory core edits: 9 BRAND IDs / 10 strings / 6 files.**

> **Count correction (2026-08-09).** An earlier revision stated *"6 files / 9 strings"*. The 9 is the count
> of **BRAND IDs** (005, 006, 087, 113, 126, 130, 134, 135, 136); the **string** count is **10**, because
> BRAND-130 is a single ID covering two hardcoded URLs. Files remain 6.

| # | File | Strings | Rebase risk | Upstream-PR intent |
|---|---|---|---|---|
| 1 | `admin.php` | 2 | Low | Yes — replace literals with `$openemr_name`-derived text |
| 2 | `src/RestControllers/FhirMetaDataRestController.php` | 2 | Low | **Strong** — `software.name` should honour `openemr_name`; genuinely useful upstream |
| 3 | `src/Services/ProductRegistrationService.php` | 1 | Low | Configurable endpoint / disable switch |
| 4 | `src/RestControllers/Subscriber/OAuth2AuthorizationListener.php` | 1 | Low | Message should not embed the product name |
| 5 | `interface/globals.php` | 2 | **Medium** — hot bootstrap file | Yes — messages should use a constant |
| 6 | Zend `Installer/.../index.phtml` | 2 | Low | Configurable docs URL |

Each patch gets a numbered downstream patch record (`Q1`: *"any unavoidable core change requires a numbered
ADR/patch record and an upstream-first path"*), a minimal diff, and a rebase test. **Down from the 24
NEEDS-PATCH items and ~11 files implied by the raw inventory to 6 files** — this is the concrete measure of
the "minimise core modifications" objective.

## 5.5 WS-D — SET-TRANSLATION (8 items)

BRAND-101 (5 error strings), 127–129 (OAuth2 + Zend titles), 132 (portal login title), 102–104 (the 62 code
strings, 924 catalogue lines, 237,509 `lang_definitions` rows). All are `xl()`/`xlt()`-wrapped, so they are
**data, not code**. Deliverable: a reviewed EN/AR constant→value list (R5 `14-…md` Parts 4–5) plus an
idempotent import script. **Execution is `MVP-004`'s** (CR-14) and is gated on native-Arabic proofreading
(**D-4**).

## 5.6 WS-E / WS-F — TOKENIZE (6) and BUILD-SHARED-THEME (9)

- BRAND-078/079/080: introduce the token layer; `$brand-primary` finally exists (BRAND-079 was the explicit
  gap vs `Q34`); `#2d9bd6` disappears with the raster replacements (BRAND-080).
- BRAND-121–123: SMART contract from the same source (§3.7.5).
- BRAND-076/077/081/082/083: theme build, `config/config.yaml` binding (unchanged — filenames preserved per
  CR-9), RTL mechanism, selector behaviour.
- BRAND-085/086/125: fonts vendored, Arabic font finally present, Lato retired.
- BRAND-111: PDF stylesheets retokenised; **`Q25`-compliant Amiri / Noto Naskh Arabic** bundled and both
  PDF engines configured; Arabic PDF shaping verified (**D-9**).

## 5.7 WS-G / WS-H / WS-I

- **HIDE (3):** `display_review_link=0`, `display_donations_link=0`, in-app help hidden (BRAND-105 —
  ~180 wiki links; rewriting is out of MVP scope).
- **DEFER (8):** BRAND-034–037 (review-logo, installer screenshots, Swagger favicons, Zend legacy images),
  094/095 (composer/package metadata), 098 (Swagger UI title — note it is **unauthenticated**), 124
  (orphaned static SMART style file). Each gets a scheduled post-MVP ticket, not a silent drop.
- **PRESERVE (15):** session identity ×6 (C6/Q17), regulatory and trademark assets (C7), GPL/docblock/
  namespace identity, acknowledgements page and ONC claims (**D-11** — counsel review before any change).
- **NO-ACTION (11):** recorded with the proof that no action is needed (e.g. BRAND-084 `style_default.css`
  is caught by the `file_exists()` gate; BRAND-052 is an unreachable branch; BRAND-088 `software.version`
  is absent because `composer.json` has no `version` key).
- **PROHIBITED (1):** BRAND-120 `sites/<site>/config.php` — guardrail-enforced (WP-2.7).

## 5.8 Phase 3 sequencing

```
WS-F (build/theme)  ──┐
WS-E (tokens)       ──┼──►  WS-B (assets)  ──►  WS-A (config)  ──►  WS-D (translations, MVP-004)
WS-C (core patches) ──┘                                     └──►  WS-G / WS-H / WS-I (record & guard)
```

Theme and tokens first (they define the visual contract), then assets, then configuration (which references
both), then translations (gated on proofreading). Core patches run in parallel as an isolated,
independently revertible branch.

## 5.9 Phase 3 exit criteria

Every one of the 136 IDs has a completed status and a verifiable artefact; the residual core-edit set is
exactly the 6 recorded files; no guardrail is suppressed; the isolated test suite and PHPStan are green.

---

# PHASE 4 — COVERAGE VERIFICATION AND BLOCKING DEPENDENCIES

**Objective:** prove, item by item, that the Group 1 inventory is fully discharged — and state plainly what
is *not* done and why.

## 6.1 Verification method

Verification is **machine-checked wherever the evidence permits**, mirroring the five-channel methodology
that made Group 1 defensible (R4 §0.2):

| Channel | Group 2 use |
|---|---|
| 1. Source grep | A `verify-branding` tool asserts each patched literal is gone and each PRESERVE literal is untouched |
| 2. Filesystem | `public/themes/` contains exactly the `Q77` set; every kit asset is in place with a manifest-matching SHA-256 |
| 3. Database | Every SET-CONFIG global holds its profile value; no `user_settings` row points at a removed theme |
| 4. HTTP runtime | Authenticated + unauthenticated page fetches asserted for zero OpenEMR strings; SMART/FHIR JSON asserted key-by-key |
| 5. Bidirectional asset check | Every logo slot resolves to a Thiqa asset; no legacy path serves an OpenEMR mark |

**Native-host reality (R8):** channels 1–4 are all runnable on this machine (Apache 2.4.57 + PHP 8.3.33 +
MariaDB 11.8.8 on `http://localhost:8300`) — that is exactly how Group 1D obtained its runtime evidence.
DB-backed PHPUnit suites and two-tenant tests belong in CI.

## 6.2 Coverage matrix (rebuild of R4 §19's 45 areas)

Phase 4 rebuilds the 45-area matrix with Group 2 statuses: **DONE / DONE-VERIFIED / DEFERRED / BLOCKED**.
The gate is: **zero BLOCKED, and every DEFERRED item carries a ticket and a reason.** Additionally, all 136
BRAND IDs must appear exactly once in the traceability table (§8.1) — the same per-ID integrity check that
Group 1 used as certification gate 6.

## 6.3 Acceptance tests A1–A8 (R4 §20 register C)

| ID | Test | Owner | Blocked by |
|---|---|---|---|
| A1 | Two tenants, distinct branding, no cross-render | Branding + provisioning | **D-6** |
| A2 | Cache keys/revisions prevent cross-tenant bleed | Branding | **D-6** |
| A3 | Invalid token/CSS payloads rejected | Branding | none — WP-2.2 |
| A4 | No tenant-uploaded CSS/JS executes | Branding + security | none — WP-2.2/2.9 |
| A5 | Statement/PDF renders with the Thiqa practice logo | Branding | **D-7** (≥1 patient) |
| A6 | Portal post-authentication branding | Branding | **D-7** (portal credential) + CR-4 |
| A7 | Reception/Accountant smoke test | QA | roles provisioned (branding already source-proven role-agnostic) |
| A8 | SMART dark tokens returned for a dark theme | Branding | none — §3.7.5 |

## 6.4 Additional Group 2 verification checks

| ID | Check | Satisfies |
|---|---|---|
| V-01 | Instrumented page load shows **zero** outbound network calls and zero added DB queries from branding code | `MVP-010` AC-5, C5 |
| V-02 | Materialiser run twice produces byte-identical state (idempotence); `globals` overwritten by the next sync (never authoritative) | AC-6 |
| V-03 | CP unreachable → tenant renders last-good; `kill -9` mid-materialisation → revision *n−1* fully intact | AC-7 |
| V-04 | `public/themes/` has only the approved set; a `globals` row forced to `style_solar.css` falls back to `style_light.css` | AC-8, `Q77` |
| V-05 | All 33 WCAG pairs recomputed by `ContrastCalculator`; **zero FAIL** after D-1 is applied | D-1 |
| V-06 | `brand/manifests/SHA256SUMS` verifies 117/117 at release time | R5 `12-…md` |
| V-07 | Arabic session serves `rtl_style_light.css` **and** `rtl_compact_style_light.css` (the CR-3 regression test) | CR-3 |
| V-08 | `SessionUtil` identity constants unchanged; `sites/*/config.php` unreferenced by branding code | C6, C1 |
| V-09 | Rebase dry-run against upstream `master` reports conflicts only in the 6 recorded core files | `Q1`, `Q2` |
| V-10 | No new PHPStan baseline entries; module patch coverage ≥80% | R7, `MVP-009` |

## 6.5 Blocking dependency register

| ID | Dependency | Blocks | Owner | Severity |
|---|---|---|---|---|
| ~~D-1~~ | ~~`light.link.default` fails SC 1.4.3~~ — **CLOSED 2026-08-09.** Correction adopted and applied: `light.link.default = #2C5F94` (6.34 / 6.62), `light.link.hover = #1E4574` (9.31 / 9.73). 0 FAIL pairs remain; Group 1.5B acceptance is now unconditional | — | Token owner | **RESOLVED** |
| ~~D-2~~ | ~~`thiqa.example` placeholder URLs~~ — **CLOSED 2026-08-09.** Production domain is **`skyeagle.uk`**: root `https://skyeagle.uk/`, support `/support`, docs `/docs`, installer docs `/docs/installer`. Registration endpoint still depends on D-10 | — | Product owner | **RESOLVED** |
| **D-3** | Legal product-name registration (EN `Thiqa` / AR `ثقة`); confirmation that HL7 MSH-3 and QRDA may carry it | BRAND-001 and everything derived from it; module slug freeze | Legal + integration owners | **Blocking for release** |
| **D-4** | Native-Arabic linguistic proofreading of all Arabic strings | WS-D go-live | Localisation owner | Blocking for AR launch |
| **D-5** | Control Plane (`MVP-014`) not yet built | End-to-end `Q76` materialisation; A1/A2 | Platform | Blocking for AC-6/7 |
| **D-6** | Second provisioned tenant (G-10b) | A1, A2 | Provisioning | Blocking `MVP-010` acceptance |
| **D-7** | ≥1 patient and a portal patient credential in a test tenant | A5, A6 | QA/provisioning | Blocking acceptance |
| ~~D-8~~ | ~~Writable, execution-denied volume inside the module tree~~ — **ELIMINATED 2026-08-09** by the CR-19 redesign: the recommended Tier 2 route (module PHP endpoint) performs no runtime writes. Returns only if a deployment elects fallback (b) | — | Platform | **RESOLVED by design change** |
| **D-9** | **`Q25`-compliant Arabic PDF fonts** — Amiri and/or Noto Naskh Arabic in TTF/OTF, with **both** `mpdf/mpdf` and `dompdf/dompdf` explicitly configured, then shaping/embedding/RTL/numeral tests. *(Revised: IBM Plex Sans Arabic does not satisfy `Q25` — CR-16.)* | BRAND-111/086; Arabic PDFs | Branding + platform | **Blocking AR print** |
| **D-10** | Decision: repoint or disable product registration (BRAND-113) | Core patch 3 | Product owner | Blocking that patch |
| **D-11** | Counsel review of `acknowledge_license_cert.html` (OpenEMR Foundation branding, 8 third-party identities incl. personal emails, ONC claims) on a **publicly reachable, unauthenticated** page | BRAND-063/118 disposition | Legal | Blocking public launch |
| **D-12** | Ratification of `border.strong` and dark `surface.input` derivations (CR-10) | Token contract freeze | Token owner | Low |
| ~~D-13~~ | ~~Sign-off on CR-3 and CR-9~~ — **CLOSED 2026-08-09.** Both accepted as recommended; corrections applied to `14-string-replacement-map.md` | — | String-map / product owners | **RESOLVED** |

**Register status after the 2026-08-09 decisions and external audit:** 4 of 13 dependencies closed
(D-1, D-2, D-8, D-13). The remaining **blocking-for-release** items are **D-3** (legal + integration
clearance of the product name), **D-9** (`Q25`-compliant Arabic PDF fonts — *newly escalated*),
**D-10** (registration endpoint) and **D-11** (counsel review of the acknowledgements page).

### 6.5.1 Open items added by the external audit

| ID | Item | Blocks | Owner | Severity |
|---|---|---|---|---|
| **D-14** | Ratify the `Q38` interpretation for template delivery: confirm that the namespaced `TemplatePageEvent` route (CR-7 revised) is the required pattern, and that no unnamespaced core-template shadowing is permitted without an ADR | WS-C/WS-E delivery mechanism | Architecture owner | Medium — mechanism, not outcome |
| **D-15** | Decide whether the administration theme selector must literally display *"Saudi Light" / "Saudi Dark"* (it will display **"Light" / "Dark"** if filenames are retained) | Product acceptance wording only | Product owner | Low |
| **D-16** | Approve the canonical cache-key specification per asset type, and the accompanying test matrix (upgrade, rollback, two tenants at equal revision, CDN/proxy keys, query ordering, no topology disclosure) | `MVP-010` AC-4 acceptance | Architecture + platform | Medium |

## 6.6 Phase 4 exit criteria

136/136 IDs verified or explicitly deferred with a ticket; zero BLOCKED rows in the rebuilt coverage matrix;
A3, A4, A7, A8 and V-01…V-10 passing; A1, A2, A5, A6 either passing or formally recorded as gated on
D-5/D-6/D-7 with a scheduled date. **Cross-tenant branding must not be described as certified until A1 and
A2 execute** — the same discipline R4 applied to G-10b.

---

# PHASE 5 — FINAL DOCUMENTATION AND READINESS

**Objective:** leave behind a documentation set that a future maintainer, a rebase engineer, an auditor and
the multi-tenant/white-label team can each use without re-deriving anything.

## 7.1 Deliverables

| # | Document | Contents |
|---|---|---|
| **F1** | `docs/branding/architecture.md` | The five-plane model, extension-point map with source citations, token contract, folder structure, configuration precedence, cache/revision model, threat model. The canonical answer to *"how does branding work here?"* |
| **F2** | `docs/branding/changes.md` | Every change made, grouped by workstream, each row carrying BRAND ID → action → mechanism → artefact → verification evidence. Includes the 6 residual core patches with diffs, rationale, rebase risk and upstream-PR status |
| **F3** | `docs/branding/remaining-dependencies.md` | The D-register with current status and owners; the DEFER backlog (8 items); the conditional-patch set; known advisories (disabled-text and border contrast exemptions) |
| **F4** | `docs/branding/multi-tenant-white-label-readiness.md` | What works today, what a second tenant needs, what full white-label needs, what would require a new ADR (e.g. per-tenant fonts). Includes the forward-compatibility table of §3.10 |
| **F5** | `docs/branding/runbook.md` | Provision a tenant's branding; change a token; roll back a revision; rebuild themes; regenerate tokens; verify a release; recover a failed materialisation. **Native-host commands** (R8) alongside CI equivalents |
| **F6** | `ADR-BRAND-001…00n` | One ADR per architectural decision; one numbered patch record per core edit |
| **F7** | Updated `docs/rebranding.md` cross-reference note | A short appendix mapping Group 1 IDs to Group 2 outcomes. **Group 1 evidence itself is not rewritten** — it is a certified artefact |
| **F8** | Corrections to R5 | Fix CR-1/CR-2 section references and the CR-3 rows in `14-string-replacement-map.md`; lift its status from DRAFT once D-4 completes |
| **F9** | `MVP-010` / `MVP-014` closure evidence pack | PR references, test evidence, security/tenant-isolation review, runbook links — the Global Definition of Done in R2 |

## 7.2 Readiness statement (the honest summary F3 must contain)

The final documentation must state without hedging: which acceptance criteria are **met**, which are
**gated** and on what, and which behaviours are **not yet certified** (cross-tenant isolation until A1/A2;
Arabic print until D-9; light-theme links until D-1). Invariant 10 — *claims must describe actual controls,
not inferred capabilities* — applies to this plan's own output as much as to Group 1's.

## 7.3 Release gate

A release of the branding layer requires: `brand/manifests/SHA256SUMS` 117/117; the token generator
producing no diff; the `Q77` entry-map assertion passing; V-01…V-10 green; the D-register showing no
open **Blocking-for-release** item; and R3 re-verifying the two governance documents.

---

## 8. Traceability

### 8.1 BRAND-ID → workstream (authoritative mapping = R4 §16.2, one action per ID)

| Action (R4 §16.2) | Count | Workstream | Phase |
|---|---:|---|---|
| SET-CONFIG | 37 | WS-A | 3 |
| REPLACE-ASSET | 20 | WS-B | 3 |
| PATCH | 18 | WS-C (6 files residual) | 3 |
| PRESERVE | 15 | WS-I | 3 (guardrails in 2) |
| NO-ACTION | 11 | WS-I | 3 |
| BUILD-SHARED-THEME | 9 | WS-F | 3 (build design in 1) |
| SET-TRANSLATION | 8 | WS-D | 3 (executes under `MVP-004`) |
| DEFER | 8 | WS-H | post-MVP |
| TOKENIZE | 6 | WS-E | 2–3 |
| HIDE | 3 | WS-G | 3 |
| PROHIBITED | 1 | WS-I (guardrail) | 2 |
| **Total** | **136** | | |

Phase 4 expands this into a full per-ID table (136 rows) with evidence links; the aggregate above is
computed from R4 §16.2 and must reconcile exactly.

### 8.2 Locked decision → plan section

| Ref | Section |
|---|---|
| Invariant 4 (no core edits) | §3.1 P2/P3, §3.6, §5.4 |
| Invariant 9 (no tenant CSS/JS) | §3.4.1, §3.9, WP-2.2, WP-2.7 |
| Invariant 10 (honest claims) | §7.2 |
| CP §2 / §8 / §10 | §3.2, §3.3.2, §4.4 |
| Q1 / Q2 (pinned baseline, rebase cadence) | §5.4 patch records, V-09 |
| Q12 (subdomain routing) | BRAND-070 in §5.2; option (a) rejection in §3.2.2 |
| Q17 (session identity frozen) | C6, WS-I, WP-2.7 |
| Q18 / Q22 (Arabic baseline, MSA) | §3.7.4, WS-D |
| **Q25 (PDF Arabic fonts — Amiri / Noto Naskh, explicit engine config)** | §3.7.4 as corrected by **CR-16**; **D-9**. IBM Plex Sans Arabic covers the **web** face only and does not satisfy Q25 |
| Q24 (bootstrap-rtl vendored) | §3.7.3 |
| Q32 / Q33 (portal in scope, BS4 shell kept) | §5.2 portal globals, WS-B portal assets |
| Q34 (two variants, tokens + logos) | §2.4, §3.7.2 |
| Q35 (CKEditor Arabic/RTL) | out of branding scope — `MVP-004` |
| Q37 / Q38 / Q58 (module naming, Twig namespace, `saas_` prefix) | §3.5.1, §3.4.2 |
| Q59 (no per-site theme override) | §3.4.3 |
| Q76 (materialisation boundary) | §3.2, §4.4, V-01…V-03 |
| Q77 (Saudi theme surface) | §3.7.2, V-04 |
| `MVP-010` / `MVP-014` | §2.2, §2.3, §7.1 F9 |
| `MVP-008` (namespace safety) | WP-2.1 |
| `MVP-009` (CI gates) | §4.5, V-10 |
| ADR-DEV-001 (native dev runtime) | §4.5, §6.1, F5 |

### 8.3 Open decisions requiring sign-off before Phase 3

| Item | Decision needed | Owner | Blocks | Status |
|---|---|---|---|---|
| CR-3 | Do not store `rtl_`-prefixed theme filenames in `globals` | String-map owner | WS-A | ✅ **Decided + applied** 2026-08-09 |
| CR-9 | Keep the CSS filenames `style_light.css` / `style_dark.css` | Product owner | WS-F | ✅ **Decided** 2026-08-09 |
| D-1 | Light-theme link colour correction | Token owner | WS-E, production | ✅ **Decided + applied** 2026-08-09 |
| D-2 | Final production URLs | Product owner | WS-A, core patch 3 | ✅ **Decided + applied** 2026-08-09 — `skyeagle.uk` |
| D-3 | Legal product name; MSH-3/QRDA clearance | Legal + integration | WS-A | ⏳ **Open** |
| D-10 | Repoint vs disable product registration | Product owner | Core patch 3 | ⏳ **Open** |

**Phase 3 is unblocked** except for the two rows still open, both of which affect a small, well-identified
set of values (the product name and one endpoint URL) rather than the architecture.

---

## 9. Risk register

| # | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| R-1 | Upstream rebase conflicts in the 6 patched core files | Medium | Medium | Minimal diffs; upstream-first PRs; V-09 rebase dry-run each cycle (`Q2` monthly review) |
| R-2 | Upstream adds a new theme, silently entering the product | Low | Medium | Entry-map assertion (WP-2.7d), re-verified each rebase per `Q77` |
| R-3 | ~~Tier 2 token CSS requires a writable module tree, conflicting with a read-only image~~ | **Closed** | — | **Retired 2026-08-09 (CR-19).** The recommended route emits CSS from a module endpoint and writes nothing, so the image stays immutable. Risk returns only on fallback route (b) |
| R-4 | Arabic PDF shaping fails in mPDF | Medium | High for AR print | D-9 tested early, in Phase 2, not at acceptance |
| R-5 | Product name changes after slug freeze | Low | Medium | Module directory rename is cheap before first release, expensive after — freeze at D-3 |
| R-6 | `openemr_name` change breaks an HL7/QRDA integration partner | Medium | **High** | D-3 integration-owner sign-off; MSH-3 is a machine contract, not a label |
| R-7 | Front-end build cannot run on the `G:` mount | **Certain** (R8 §6) | Medium | Build in `C:\openemr-stack\build`, copy `public/themes` + `public/assets` back with robocopy; keep the workspace's copies of `interface/themes`, `webpack.themes.js`, `package.json` in sync — the new Thiqa SCSS files and the pruned entry map **must** be copied into the workspace before each build |
| R-8 | DB-backed test suites unvalidated on the native host | High | Medium | Route them through CI (`MVP-009`); keep the isolated suite as the local gate |
| R-9 | Acknowledgements page exposes third-party identities publicly | Certain (already live) | Legal | D-11 counsel review; consider gating the page behind authentication as a separate, non-branding decision |
| R-10 | Contrast gate rejects a legitimately-approved future token | Low | Low | Gate is advisory-overridable **only** by a recorded token-owner exception, never by code |

---

## 10. Summary

- **Architecture, not substitution.** Branding becomes an isolated layer with five planes, one service
  facade, one closed configuration registry and thirteen published extension points — not a search-and-
  replace over 136 references.
- **Core modification is measured and small.** 136 items; **127** handled by configuration, assets,
  translations, module events, template overrides or build configuration; **9 items across 6 core files**
  remain mandatory (plus 6 conditional operator-screen items), each with a patch record and an
  upstream-PR path.
- **Every locked decision is honoured.** `Q76` push/sync materialisation with no request-path Control Plane
  dependency; `Q77` two-variant theme surface enforced at the build layer with no core selector patch;
  Invariant 9 made structurally impossible to violate by a closed token allowlist rather than by policy.
- **Two improvements over the Group 1 assumption**, both reducing core edits: the SMART dark contract
  (CR-7) and the login logo `alt` (CR-8) are deliverable with zero tracked-file modification.
- **One real defect caught in the input** (CR-3): storing an `rtl_`-prefixed theme filename in `globals`
  would have shipped a broken compact stylesheet to every Arabic session.
- **Thirteen dependencies are named, owned and dated rather than discovered late** — three of which
  (WCAG link colour, `.example` URLs, legal product name) block release and none of which block starting.

**No code, configuration, asset or database change has been made. This document is planning output only.**
