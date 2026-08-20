# RebrandingItemsPrompts

**Purpose.** Ready-to-paste **Recraft.ai** prompts covering every brand-and-identity component that OpenEMR ever surfaces, cross-referenced to [docs/rebranding.md](rebranding.md). Use this file whenever a new brand is being generated for OpenEMR — the base parameters change, but the surface list and the prompt structure do not.

**How this file is organised.**

1. [Section 1 — Fill-in parameter block](#section-1--fill-in-parameter-block). Replace the `{{PLACEHOLDERS}}` once. Every prompt below reads them.
2. [Section 2 — How to use this file](#section-2--how-to-use-this-file). 6-step workflow, ~1 page.
3. [Section 3 — The Global Lockdown block](#section-3--the-global-lockdown-block). Paste it into **every** Recraft generation so the tokens/typography/logo never drift.
4. [Section 4 — The prompts](#section-4--the-prompts), grouped:
   - A. Master logo system (9 prompts)
   - B. Production raster exports (8 prompts)
   - C. Colour + typography specification sheets (3 prompts)
   - D. Login / dashboard / core clinician surfaces (10 prompts)
   - E. Patient portal surfaces (4 prompts)
   - F. SMART / FHIR consent surfaces (2 prompts)
   - G. Print / PDF surfaces (4 prompts)
   - H. Email surfaces (4 prompts)
   - I. Brand-guideline supporting sheets (5 prompts)
5. [Section 5 — Post-generation checklist](#section-5--post-generation-checklist).

The full list is **49 prompts** — every surface named in [docs/rebranding.md](rebranding.md) plus every acceptance surface in the Brand Design Requirements Document is covered. *(The `§§4–14` figure previously here, and the per-row section numbers in the "Coverage cross-reference" table near the end of this file, do not match `docs/rebranding.md`'s current section numbering — see the flag note above that table, added 2026-08-19.)*

---

## Section 1 — Fill-in parameter block

Copy this block into a scratchpad, replace every `{{PLACEHOLDER}}`, then use the filled values everywhere below. Anything you leave as `{{...}}` will confuse Recraft.

```
{{BRAND_NAME_EN}}                 e.g.  Thiqa
{{BRAND_NAME_AR}}                 e.g.  ثقة
{{BRAND_NAME_AR_TRANSLIT}}        e.g.  Thiqah
{{TAGLINE_EN}}                    e.g.  Clinical confidence, connected care.
{{TAGLINE_AR}}                    e.g.  ثقة إكلينيكية، رعاية مترابطة.
{{TENANT_EXAMPLE_EN}}             e.g.  King Faisal Medical Center
{{TENANT_EXAMPLE_AR}}             e.g.  مركز الملك فيصل الطبي

--- LIGHT THEME COLOURS ---
{{L_BG}}                          body background, e.g. #FAFAF8
{{L_SURFACE}}                     card surface, e.g. #FFFFFF
{{L_SURFACE_SUNKEN}}              secondary surface, e.g. #F2F1EE
{{L_BORDER}}                      default border, e.g. #E4E2DC
{{L_TEXT_PRIMARY}}                primary text, e.g. #0B1B4D
{{L_TEXT_SECONDARY}}              secondary text, e.g. #4B5266
{{L_TEXT_DISABLED}}               disabled text, e.g. #9CA0AC
{{L_BRAND_PRIMARY}}               identity primary (navy/logo), e.g. #0B1B4D
{{L_BRAND_ACCENT}}                identity accent (coral/orange), e.g. #FF6F5E
{{L_CTA_BG}}                      interactive.primary CTA bg, e.g. #C43F2E
{{L_CTA_TEXT}}                    text on CTA bg, e.g. #FFFFFF
{{L_LINK}}                        hyperlink, e.g. #2C5F94  (MUST be ≥ 4.5:1 on {{L_BG}})
{{L_FOCUS_RING}}                  focus ring, e.g. #3E7FBD
{{L_SUCCESS_BG}} / {{L_SUCCESS_TEXT}}  e.g. #E9F5EE / #2F6B45
{{L_WARNING_BG}} / {{L_WARNING_TEXT}}  e.g. #FCEFE0 / #8A5314
{{L_CRITICAL_BG}} / {{L_CRITICAL_TEXT}} e.g. #FBE9E7 / #8E271D
{{L_INFO_BG}} / {{L_INFO_TEXT}}   e.g. #E8F0FA / #264C74

--- DARK THEME COLOURS ---
{{D_BG}}                          body bg, e.g. #0B1220
{{D_SURFACE}}                     card surface, e.g. #121A2E
{{D_SURFACE_RAISED}}              raised card, e.g. #17213A
{{D_BORDER}}                      default border, e.g. #26314A
{{D_TEXT_PRIMARY}}                e.g. #F5F6F8
{{D_TEXT_SECONDARY}}              e.g. #AEB5C4
{{D_CTA_BG}}                      e.g. #FF6F5E
{{D_CTA_TEXT}}                    text on {{D_CTA_BG}}, e.g. #0B1220
{{D_LINK}}                        e.g. #8FC1EE
{{D_FOCUS_RING}}                  e.g. #8FC1EE

--- TYPOGRAPHY ---
{{FONT_LATIN}}                    e.g.  Inter
{{FONT_ARABIC}}                   e.g.  IBM Plex Sans Arabic

--- LOGO SYMBOL DESCRIPTION ---
{{SYMBOL_DESCRIPTION}}            e.g.  two interlocked geometric rings, one in {{L_BRAND_PRIMARY}} and one in {{L_BRAND_ACCENT}}, rotationally symmetric under horizontal flip.

--- RTL RULE ---
{{RTL_LOGO_RULE}}                 usually:  the symbol is language-neutral and MUST NOT be mirrored for RTL. Wordmark in Arabic uses the Arabic wordmark asset, not a mirrored Latin wordmark.
```

Once filled in, the prompts below will inline these values consistently across all 49 generations. Recraft's "same project" memory then keeps the brand identity locked between renders.

---

## Section 2 — How to use this file

1. **Fill in Section 1** with the new brand values. Keep a copy in the Recraft project's notes so future contributors do not drift.
2. **Open Section 3** and copy the **Global Lockdown block** to your clipboard.
3. For each surface you need, **paste the Global Lockdown block first, then the surface-specific prompt from Section 4**. Recraft will treat the Lockdown block as brand-level rules and the surface prompt as the render instruction.
4. **Attach reference images** as directed in each prompt's `Attach` line — usually the palette swatch sheet, the typography specimen, and the previously-generated primary logo lockup. Recraft locks the mark that way.
5. **Save each render** into [docs/Thiqa_Group_1_5B_Handoff/inputs/design_evidence/](Thiqa_Group_1_5B_Handoff/inputs/design_evidence/) (or a new folder if this is a new brand) using the exact filename shown in the prompt heading, so the audit tools in [docs/branding-production/](branding-production/) recognise them.
6. When all needed surfaces are done, re-run Group 1.5B (or the equivalent packaging step for the new brand). The certification tools regenerate manifests, SHA-256 sums, and the QA matrix.

---

## Section 3 — The Global Lockdown block

Paste this block into **every** Recraft generation, before the surface-specific prompt. Every value here is substituted from Section 1.

```
STYLE LOCKDOWN — DO NOT CHANGE across generations in this project.

Brand name (English): {{BRAND_NAME_EN}}
Brand name (Arabic):  {{BRAND_NAME_AR}}
Tagline (English):    {{TAGLINE_EN}}
Tagline (Arabic):     {{TAGLINE_AR}}
Symbol:               {{SYMBOL_DESCRIPTION}}
                      Use the exact geometry from the reference image. Never redraw. Do not simplify. Do not stylise. Aspect ratio preserved. Never mirrored.

TYPOGRAPHY
  Latin  UI + wordmark: {{FONT_LATIN}} at weights 400/500/600/700.
  Arabic UI + wordmark: {{FONT_ARABIC}} at weights 400/500/600/700.
  Numeric fields use tabular lining figures.
  0/O, 1/I/l, 5/S must remain visually distinct.

PALETTE — LIGHT THEME
  body-bg               {{L_BG}}
  surface               {{L_SURFACE}}
  surface-sunken        {{L_SURFACE_SUNKEN}}
  border                {{L_BORDER}}
  text.primary          {{L_TEXT_PRIMARY}}
  text.secondary        {{L_TEXT_SECONDARY}}
  text.disabled         {{L_TEXT_DISABLED}}
  brand.primary         {{L_BRAND_PRIMARY}}
  brand.accent          {{L_BRAND_ACCENT}}
  interactive.primary   bg {{L_CTA_BG}}  text {{L_CTA_TEXT}}   (WCAG-safe CTA)
  link                  {{L_LINK}}
  focus ring            {{L_FOCUS_RING}}
  success               bg {{L_SUCCESS_BG}}   text {{L_SUCCESS_TEXT}}
  warning               bg {{L_WARNING_BG}}   text {{L_WARNING_TEXT}}
  critical              bg {{L_CRITICAL_BG}}  text {{L_CRITICAL_TEXT}}
  info                  bg {{L_INFO_BG}}      text {{L_INFO_TEXT}}

PALETTE — DARK THEME
  body-bg               {{D_BG}}
  surface               {{D_SURFACE}}
  surface-raised        {{D_SURFACE_RAISED}}
  border                {{D_BORDER}}
  text.primary          {{D_TEXT_PRIMARY}}
  text.secondary        {{D_TEXT_SECONDARY}}
  interactive.primary   bg {{D_CTA_BG}}  text {{D_CTA_TEXT}}
  link                  {{D_LINK}}
  focus ring            {{D_FOCUS_RING}}

RTL RULE
  {{RTL_LOGO_RULE}}
  Arabic text right-aligned. Form field labels ABOVE inputs (not to the side).
  Numeric values LTR internally inside RTL text runs. Directional icons flip
  (breadcrumbs, next/previous). Non-directional icons do NOT flip (bell, gear,
  avatar, ring symbol).

PROHIBITIONS
  No OpenEMR visual bleed (no OpenEMR logo, no OpenEMR blue, no open-emr.org).
  No generic red cross, stethoscope, ECG line, caduceus, hospital building.
  No 3D renders, no drop shadows crossing the mark, no gradients through the
  symbol, no filters. Flat, clean, enterprise healthcare.
  No stock medical imagery. No cartoon.
```

---

## Section 4 — The prompts

### A. Master logo system (9 prompts)

Deliverables that go into `brand/master/` and drive every other raster.

Each prompt below should be rendered at ≥ 2000×2000 for symbol variants and ≥ 2528×1696 for horizontal lockups. Export as PNG on transparent background (Recraft supports this); the SVG source is either supplied by Recraft or traced from the Recraft PNG in the packaging stage.

---

#### A1 — `brand-symbol.svg` (canonical symbol)

**Attach:** none (this is the seed generation for the whole brand).

```
INCLUDE the Section 3 Global Lockdown block above.

Design the {{BRAND_NAME_EN}} brand symbol — the standalone mark.

Constraints:
- Original geometric mark, {{SYMBOL_DESCRIPTION}}.
- Two colours only: {{L_BRAND_PRIMARY}} (primary) and {{L_BRAND_ACCENT}} (accent).
- Symmetric under horizontal flip (so it is not mirrored in RTL layouts).
- Recognisable at 16 px rendered height.
- No internal fine detail thinner than 4 % of the shortest side.
- Optically balanced within a square canvas.

Output canvas: 2048 × 2048 PNG, TRANSPARENT background.
```

---

#### A2 — `brand-symbol-black.svg`

**Attach:** the render from A1.

```
INCLUDE the Section 3 Global Lockdown block above.

Reproduce the {{BRAND_NAME_EN}} symbol from the reference image EXACTLY —
same geometry, same proportions, same negative space — but rendered in
solid pure black #000000 on transparent background.

No colour, no gradient, no outline. Fill only.

Output canvas: 2048 × 2048 PNG, TRANSPARENT background.
```

---

#### A3 — `brand-symbol-white.svg`

**Attach:** the render from A1.

```
INCLUDE the Section 3 Global Lockdown block above.

Reproduce the {{BRAND_NAME_EN}} symbol from the reference image EXACTLY —
same geometry, same proportions, same negative space — but rendered in
solid pure white #FFFFFF on transparent background.

For preview purposes only, place the render on a dark {{D_BG}} background
below the transparent output so a reviewer can visually confirm the shape
before download.

Output canvas: 2048 × 2048 PNG, TRANSPARENT background.
```

---

#### A4 — `brand-logo-primary.svg` (horizontal lockup, light bg)

**Attach:** the render from A1.

```
INCLUDE the Section 3 Global Lockdown block above.

Compose the {{BRAND_NAME_EN}} PRIMARY HORIZONTAL LOGO.

Layout: [SYMBOL] [WORDMARK "{{BRAND_NAME_EN}}"]
- Symbol on the LEFT, wordmark on the RIGHT.
- Wordmark uses {{FONT_LATIN}} at weight 700 (Bold), colour {{L_BRAND_PRIMARY}}.
- Cap-height of the wordmark equals ~60% of the symbol's height.
- Optical spacing between symbol and wordmark ≈ 25% of symbol's width.
- Baseline of the wordmark visually centred on the symbol.

Output canvas: 2528 × 1696 PNG, TRANSPARENT background.
Aspect ratio 2.7:1 preserved by adding transparent padding.
```

---

#### A5 — `brand-logo-primary-dark.svg` (horizontal lockup, dark bg)

**Attach:** the render from A4.

```
INCLUDE the Section 3 Global Lockdown block above.

Reproduce the primary horizontal logo from A4 EXACTLY, but with the
wordmark rendered in {{D_TEXT_PRIMARY}} so it remains legible when
placed on a dark {{D_BG}} background.

The SYMBOL colours remain {{L_BRAND_PRIMARY}} + {{L_BRAND_ACCENT}} —
do NOT recolour the symbol for dark theme (identity constant).

Output canvas: 2528 × 1696 PNG, TRANSPARENT background.
Preview overlay: place a swatch of {{D_BG}} behind the render for review.
```

---

#### A6 — `brand-logo-compact.svg`

**Attach:** the render from A4.

```
INCLUDE the Section 3 Global Lockdown block above.

Compose the {{BRAND_NAME_EN}} COMPACT LOGO — a stacked or reduced variant
of the primary horizontal logo, optimised for constrained surfaces
(sidebar, mobile navbar, small cards).

Layout:
- Symbol on TOP, wordmark BELOW.
- Wordmark: {{BRAND_NAME_EN}} in {{FONT_LATIN}} weight 700, colour {{L_BRAND_PRIMARY}}.
- Both symbol and wordmark optically centred inside a portrait-oriented canvas.

Output canvas: 1856 × 2304 PNG, TRANSPARENT background.
Aspect ratio ~4:5 preserved.
```

---

#### A7 — `brand-logo-black.svg` (monochrome black)

**Attach:** the render from A4.

```
INCLUDE the Section 3 Global Lockdown block above.

Produce the {{BRAND_NAME_EN}} horizontal logo in PURE BLACK monochrome —
symbol and wordmark BOTH rendered in solid #000000, transparent background,
no gradients, no outlines, no colour.

Use case: print (colour-safe), fax, monochrome documents, low-ink printers.

Output canvas: 2528 × 1696 PNG, TRANSPARENT background.
```

---

#### A8 — `brand-logo-white.svg` (monochrome white)

**Attach:** the render from A4.

```
INCLUDE the Section 3 Global Lockdown block above.

Produce the {{BRAND_NAME_EN}} horizontal logo in PURE WHITE monochrome —
symbol and wordmark BOTH rendered in solid #FFFFFF, transparent background,
no gradients, no outlines, no colour.

Use case: dark surfaces where the coloured mark loses contrast.

Output canvas: 2528 × 1696 PNG, TRANSPARENT background.
Preview overlay: place {{D_BG}} behind the render for review.
```

---

#### A9 — `brand-wordmark-arabic.svg`

**Attach:** the render from A1 + the typography specimen sheet from C2.

```
INCLUDE the Section 3 Global Lockdown block above.

Produce the {{BRAND_NAME_EN}} PRIMARY HORIZONTAL LOGO with the Arabic
wordmark instead of the Latin wordmark.

Layout: [SYMBOL] [WORDMARK "{{BRAND_NAME_AR}}"]
- In RTL languages this variant is used where a text-carrying wordmark is required.
- Wordmark rendered in {{FONT_ARABIC}} at weight 700, colour {{L_BRAND_PRIMARY}}.
- Cap-height equivalent to A4 sizing.
- The SYMBOL colours and geometry remain identical to A1. Not mirrored.

Output canvas: 2528 × 1696 PNG, TRANSPARENT background.
```

---

### B. Production raster exports (8 prompts)

These are exact-canvas rasters that OpenEMR loads at fixed pixel dimensions. Each is derived from the SVG master using deterministic rasterisation in the packaging step — but Recraft can also produce a first-pass render at the correct canvas for visual QA.

---

#### B1 — `login-primary-1053x390.png`

**Attach:** the render from A4.

```
INCLUDE the Section 3 Global Lockdown block above.

Reproduce the {{BRAND_NAME_EN}} primary horizontal logo from A4 exactly,
positioned centered inside a 1053 × 390 canvas.
Transparent padding preserves the logo aspect ratio.
No stretching, no distortion.

Output canvas: 1053 × 390 PNG, TRANSPARENT background.
```

---

#### B2 — `login-secondary-300x100.png`

**Attach:** the render from A4.

```
INCLUDE the Section 3 Global Lockdown block above.

Reproduce the {{BRAND_NAME_EN}} primary horizontal logo, downsized to fit
a 300 × 100 canvas, centred, with transparent padding.

Output canvas: 300 × 100 PNG, TRANSPARENT background.
```

---

#### B3 — `login-small-a-101x100.png` / `login-small-b-101x100.png`

**Attach:** the render from A1.

```
INCLUDE the Section 3 Global Lockdown block above.

Render the {{BRAND_NAME_EN}} symbol from A1 at 101 × 100 with transparent
padding, centred. Both files (a and b) may share the same image — OpenEMR
supports two independent small-logo slots but does not require them to
differ.

Output canvas: 101 × 100 PNG, TRANSPARENT background.
```

---

#### B4 — `navbar-symbol.png` (retina-safe)

**Attach:** the render from A1.

```
INCLUDE the Section 3 Global Lockdown block above.

Render the {{BRAND_NAME_EN}} symbol at 64 × 64, retina-scaled 4× for a
target CSS render height of 16 px. Symbol optically centred with
transparent padding.

Output canvas: 64 × 64 PNG, TRANSPARENT background.
```

---

#### B5 — `portal-login-primary-1053x390.png`

**Attach:** the render from A4.

```
INCLUDE the Section 3 Global Lockdown block above.

Identical to B1 but reserved for the patient portal login slot. Same
1053 × 390 canvas, same rendering.

Output canvas: 1053 × 390 PNG, TRANSPARENT background.
```

---

#### B6 — `portal-login-secondary-300x100.png`

**Attach:** the render from A4.

```
INCLUDE the Section 3 Global Lockdown block above.

Identical to B2 but reserved for the patient portal secondary logo slot.

Output canvas: 300 × 100 PNG, TRANSPARENT background.
```

---

#### B7 — `portal-navbar-870x222.png`

**Attach:** the render from A4.

```
INCLUDE the Section 3 Global Lockdown block above.

Render the {{BRAND_NAME_EN}} primary horizontal logo scaled to fit a
870 × 222 canvas — aspect ratio ~3.9:1. Optically centre with transparent
padding. Used as the patient-portal navbar wordmark, rendered at 30 px
CSS height.

Output canvas: 870 × 222 PNG, TRANSPARENT background.
```

---

#### B8 — Favicon package (16 / 32 / 48 + ICO source)

**Attach:** the render from A1.

```
INCLUDE the Section 3 Global Lockdown block above.

Produce THREE separate renders on ONE canvas, arranged left-to-right,
each on a transparent tile:
  1) 16 × 16
  2) 32 × 32
  3) 48 × 48

Each render is the {{BRAND_NAME_EN}} symbol from A1, optically re-balanced
for its target pixel size — thicker strokes and less internal detail at
16 px, near-full detail at 48 px. NO wordmark.

Output canvas: composite PNG at 256 × 96 (three tiles + separators),
each tile transparent. Provide each tile as an independent export too
if the tool supports it.
```

---

### C. Colour + typography specification sheets (3 prompts)

Reference sheets that live in `brand/colors/` and `brand/typography/`. Also serve as attach-references for every other prompt.

---

#### C1 — `palette-swatch-sheet-light.png`

**Attach:** none.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a clean, flat brand-colour palette swatch sheet on a plain
{{L_BG}} background. Layout as a grid of labelled rectangular swatches:

Row 1 — Brand identity:
  brand.primary   {{L_BRAND_PRIMARY}}
  brand.accent    {{L_BRAND_ACCENT}}
  interactive.primary  {{L_CTA_BG}}

Row 2 — Surfaces + text:
  body-bg         {{L_BG}}
  surface         {{L_SURFACE}}
  surface-sunken  {{L_SURFACE_SUNKEN}}
  border          {{L_BORDER}}
  text.primary    {{L_TEXT_PRIMARY}}
  text.secondary  {{L_TEXT_SECONDARY}}
  text.disabled   {{L_TEXT_DISABLED}}

Row 3 — Semantic states:
  success bg + text pair  {{L_SUCCESS_BG}} / {{L_SUCCESS_TEXT}}
  warning bg + text pair  {{L_WARNING_BG}} / {{L_WARNING_TEXT}}
  critical bg + text pair {{L_CRITICAL_BG}} / {{L_CRITICAL_TEXT}}
  info bg + text pair     {{L_INFO_BG}} / {{L_INFO_TEXT}}

Row 4 — Interactive:
  link   {{L_LINK}}
  focus  {{L_FOCUS_RING}}

Each swatch: 200 × 200 px rectangle with the hex value labelled below in
{{FONT_LATIN}} weight 500, {{L_TEXT_SECONDARY}}. Group titles in
{{FONT_LATIN}} weight 600, {{L_TEXT_PRIMARY}}.

Output canvas: 1920 × 1200 PNG.
```

---

#### C2 — `palette-swatch-sheet-dark.png`

**Attach:** the render from C1 for style consistency.

```
INCLUDE the Section 3 Global Lockdown block above.

Same layout as C1 but on a {{D_BG}} background, using the DARK theme
palette values. Rows list bg / surface / raised / border / text / brand /
interactive / semantic. Swatch labels in {{FONT_LATIN}} weight 500,
{{D_TEXT_SECONDARY}}. Group titles in {{D_TEXT_PRIMARY}} weight 600.

Output canvas: 1920 × 1200 PNG.
```

---

#### C3 — `typography-specimen.png`

**Attach:** the render from A4 (for the wordmark reference).

```
INCLUDE the Section 3 Global Lockdown block above.

Design a bilingual typography specimen sheet on a plain {{L_BG}} background.

Section 1 — Wordmark:
  Present the wordmark "{{BRAND_NAME_EN}}" ({{FONT_LATIN}} 700, colour {{L_BRAND_PRIMARY}})
  and the Arabic wordmark "{{BRAND_NAME_AR}}" ({{FONT_ARABIC}} 700, colour {{L_BRAND_PRIMARY}})
  side by side.

Section 2 — Latin scale ({{FONT_LATIN}}):
  Display 40/48 700    "The most trusted health record system"
  H1      32/40 700    "Patient records"
  H2      24/32 600    "Vital signs"
  Body    14/20 400    "Blood pressure 120/80 mmHg, pulse 76, temperature 36.7 °C"
  Label   13/16 500    "PATIENT ID"
  Caption 12/16 400    "Last updated 3 minutes ago"

Section 3 — Arabic scale ({{FONT_ARABIC}}):
  H1      32/40 700    "السجلات الطبية"
  H2      24/32 600    "العلامات الحيوية"
  Body    14/20 400    "ضغط الدم ١٢٠/٨٠ ملم زئبق، نبض ٧٦، درجة حرارة ٣٦٫٧ °م"
  Label   13/16 500    "رقم المريض"
  Caption 12/16 400    "آخر تحديث قبل ٣ دقائق"

Section 4 — Numeric distinctness proof:
  0 O   1 I l   5 S   at 24/32 400 tabular.

Section 5 — Weight usage note:
  400 body / 500 label / 600 heading / 700 wordmark.

Output canvas: 2000 × 2000 PNG.
```

---

### D. Login / dashboard / core clinician surfaces (10 prompts)

These are the highest-visibility surfaces per rebranding.md §14 and §17. Each has an English (LTR) variant and an Arabic (RTL) variant.

---

#### D1 — `login-english-light.png`

**Attach:** C1 palette, C3 typography, A4 logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design the {{BRAND_NAME_EN}} login screen — LIGHT theme, English, LTR.

Surface: 1440 × 900 desktop viewport.

Layout:
- Left half: soft branded panel using the symbol from A1 as an abstract,
  low-opacity pattern on {{L_BG}}. Centred A4 primary logo lockup.
- Right half: {{L_SURFACE}} card, padding 64px, containing:
    Tagline "{{TAGLINE_EN}}" ({{FONT_LATIN}} 400, 16/24, {{L_TEXT_SECONDARY}}).
    Heading "Sign in" ({{FONT_LATIN}} 700, 28/32, {{L_TEXT_PRIMARY}}).
    Label "Username" + input ({{FONT_LATIN}} 400, 14/20).
    Label "Password" + input with eye-toggle icon on right (LTR trailing edge).
    Label "Language" + dropdown showing "English (Standard)".
    Primary CTA "Sign in" — bg {{L_CTA_BG}}, text {{L_CTA_TEXT}},
      {{FONT_LATIN}} 600, height 44px, radius 8px.
    Link below CTA: "Need help?" in {{L_LINK}}.
    Footer link: "Acknowledgements, Licensing and Certification".
- Top-right corner: language toggle "AR | EN" with EN selected.

Output canvas: 1440 × 900 PNG, body {{L_BG}}, card {{L_SURFACE}}.
```

---

#### D2 — `login-english-dark.png`

**Attach:** C2 dark palette, A5 dark logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Same layout as D1 but DARK theme:
- Left panel: {{D_BG}} background, A5 dark-primary logo, symbol pattern at
  low opacity in {{D_SURFACE_RAISED}} tint.
- Right card: {{D_SURFACE_RAISED}}, 1px border {{D_BORDER}}, radius 16px.
- Text.primary {{D_TEXT_PRIMARY}}, secondary {{D_TEXT_SECONDARY}}.
- Inputs: bg {{D_SURFACE}}, border {{D_BORDER}}, text {{D_TEXT_PRIMARY}}.
- Primary CTA: bg {{D_CTA_BG}}, text {{D_CTA_TEXT}}, {{FONT_LATIN}} 600.
- Link {{D_LINK}}. Focus ring {{D_FOCUS_RING}}.

Output canvas: 1440 × 900 PNG.
```

---

#### D3 — `login-arabic-light.png`

**Attach:** C1 palette, A9 Arabic wordmark, D1 for structure.

```
INCLUDE the Section 3 Global Lockdown block above.

Same layout as D1 but ARABIC, RTL (mirror the panel structure):
- Right half: branded panel with A9 Arabic-wordmark logo centred.
- Left half: {{L_SURFACE}} card with form contents right-aligned:
    Tagline "{{TAGLINE_AR}}" (right-aligned, {{FONT_ARABIC}} 400).
    Heading "تسجيل الدخول" ({{FONT_ARABIC}} 700, 28/32).
    Label "اسم المستخدم" + input.
    Label "كلمة المرور" + input (eye-toggle icon on LEFT — RTL trailing edge).
    Label "اللغة" + dropdown showing "العربية".
    Primary CTA "دخول" full-width, same tokens as D1.
    Link "المساعدة" below CTA.
    Footer link "الإقرارات، الترخيص، والاعتماد".
- Top-LEFT corner (RTL leading edge): language toggle "AR | EN" with AR selected.

RTL rules per Global Lockdown. Logo NOT mirrored.

Output canvas: 1440 × 900 PNG.
```

---

#### D4 — `login-arabic-dark.png`

**Attach:** C2 dark palette, A9 Arabic wordmark, D2 structure.

```
INCLUDE the Section 3 Global Lockdown block above.

Combine D2 (dark theme rules) with D3 (RTL / Arabic layout rules).

Output canvas: 1440 × 900 PNG.
```

---

#### D5 — `navbar-english-tenant-lockup.png`

**Attach:** A4 logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design the authenticated app navbar — LIGHT theme, English, LTR.

Surface: 1440 × 64.

Layout (left to right):
- {{BRAND_NAME_EN}} symbol + wordmark (rendered from A4, height 32px).
- Vertical divider (1px, {{L_BORDER}}).
- Tenant name: "{{TENANT_EXAMPLE_EN}}" ({{FONT_LATIN}} 500, 14/20, {{L_TEXT_SECONDARY}}).
- Spacer.
- Nav items ({{FONT_LATIN}} 500): Home | Appointments | Records | Messages | Billing.
    Active item (Records) uses {{FONT_LATIN}} 600 with a 3px underline in {{L_CTA_BG}}.
- Right-aligned: search icon, notification bell (with {{L_BRAND_ACCENT}} dot),
  user avatar circle labelled "Ahmed Al-Harbi", language toggle "AR | EN".

Body {{L_SURFACE}}. Border-bottom 1px {{L_BORDER}}.

Output canvas: 1440 × 64 PNG.
```

---

#### D6 — `navbar-arabic-tenant-lockup.png`

**Attach:** A9 Arabic wordmark.

```
INCLUDE the Section 3 Global Lockdown block above.

Same navbar as D5 but ARABIC, RTL:
- RIGHT edge (RTL start): {{BRAND_NAME_EN}} symbol + Arabic wordmark "{{BRAND_NAME_AR}}"
  from A9, height 32px. Divider. Tenant "{{TENANT_EXAMPLE_AR}}".
- Nav items in reversed reading order: الرئيسية | المواعيد | السجلات | الرسائل | الفوترة.
  Active السجلات styled per D5 rules.
- LEFT edge (RTL end): search icon, bell, avatar, language toggle.

Output canvas: 1440 × 64 PNG.
```

---

#### D7 — `dashboard-english-light.png`

**Attach:** A4 logo, D5 navbar.

```
INCLUDE the Section 3 Global Lockdown block above.

Design the clinician dashboard home — LIGHT, English, LTR.

Surface: 1440 × 900.

Layout:
- D5 navbar at top.
- Left rail (256px): quick navigation menu with sections
    Dashboard (active — bg {{L_SURFACE_SUNKEN}} + 3px {{L_CTA_BG}} left bar)
    My Patients / Schedule / Messages / Tasks / Reports.
- Main content:
    Page heading "Dashboard" ({{FONT_LATIN}} 700, 32/40).
    Row of 4 KPI cards ({{L_SURFACE}}, radius 12px, 1px border {{L_BORDER}}):
      "Patients today" 27, "Appointments" 41, "Unread messages" 8, "Open tasks" 12.
      Each KPI number in {{FONT_LATIN}} 700 40/48 {{L_TEXT_PRIMARY}}.
    Two-column row:
      Left card: "Upcoming appointments" — table of 4 rows (time, patient, reason).
      Right card: "Recent lab results" — 3 rows with success/warning/critical badges
        using the semantic pairs from the palette.

Output canvas: 1440 × 900 PNG.
```

---

#### D8 — `dashboard-arabic-light.png`

**Attach:** D7 for structure, A9 for Arabic wordmark.

```
INCLUDE the Section 3 Global Lockdown block above.

Same as D7 but Arabic, RTL. Everything mirrored.
Nav items: لوحة التحكم | مرضاي | الجدول | الرسائل | المهام | التقارير.
KPI card labels: المرضى اليوم, المواعيد, الرسائل غير المقروءة, المهام المفتوحة.
Cards: "المواعيد القادمة" and "أحدث النتائج المخبرية".

Output canvas: 1440 × 900 PNG.
```

---

#### D9 — `clinical-form-english-light.png`

**Attach:** D5 navbar.

```
INCLUDE the Section 3 Global Lockdown block above.

Design the clinical encounter form — LIGHT, English, LTR.

Surface: 1440 × 900. Navbar (D5) + left rail (D7).

Main content:
- Page heading "New Clinical Visit — Vital Signs" ({{FONT_LATIN}} 700, 24/32).
- Patient strip card: name "Fatimah Al-Qahtani", MRN 100234, Age 34, Sex F,
  Visit date 2026-08-09.
- Section "Vital Signs" with two-column form, labels above inputs:
    Blood pressure [120/80] mmHg
    Pulse [76] bpm
    Temperature [36.7] °C
    Respiratory rate [16] /min
    SpO₂ [98] %
    Height [162] cm
    Weight [58] kg
    BMI (auto-calculated, disabled state)
- Notes textarea (3 rows).
- Sticky bottom action bar:
    "Cancel" secondary button (LEFT — trailing edge — {{L_BRAND_PRIMARY}} outline + text).
    Middle: "Save and continue" link.
    "Save visit" primary CTA (RIGHT — end — {{L_CTA_BG}} bg + {{L_CTA_TEXT}}).

Output canvas: 1440 × 900 PNG.
```

---

#### D10 — `clinical-form-arabic-light.png`

**Attach:** D9 structure, D6 navbar.

```
INCLUDE the Section 3 Global Lockdown block above.

Same as D9 but Arabic, RTL. All labels right-aligned, numeric fields
LTR internally. Primary CTA "حفظ الزيارة" on LEFT (RTL end),
Cancel "إلغاء" on RIGHT (RTL start).

Arabic field labels:
  ضغط الدم / نبض القلب / درجة الحرارة / معدل التنفس / SpO₂
  الطول / الوزن / مؤشر كتلة الجسم.

Output canvas: 1440 × 900 PNG.
```

---

### E. Patient portal surfaces (4 prompts)

Softer, warmer, patient-facing — same tokens but more whitespace and rounded corners.

---

#### E1 — `portal-home-english-light.png`

**Attach:** A4 logo (portal wordmark from B7 for navbar).

```
INCLUDE the Section 3 Global Lockdown block above.

Design the {{BRAND_NAME_EN}} Patient Portal home page — LIGHT, English, LTR.

Surface: 1440 × 900.

Layout:
- Portal navbar (64px, marketing-toned):
    Left: portal wordmark from B7 scaled to 30px height.
    Center: Home | Appointments | Medical Record | Prescriptions | Bills | Messages.
      Active "Home".
    Right: notifications bell, avatar "Fatimah Al-Qahtani ▾", language toggle "AR | EN".
- Hero card ({{L_SURFACE}}, 16px radius, subtle shadow):
    Left: greeting "Hi, Fatimah" ({{FONT_LATIN}} 700 32/40),
      subline "You have an upcoming appointment on Thursday 12:30 PM with Dr. Sarah Al Anzi."
    Right: soft illustration re-using the symbol at low opacity on a
      {{L_BRAND_ACCENT}}-tinted gradient.
    Two CTAs: "Book a new appointment" (primary {{L_CTA_BG}}) and
      "View medical record" (secondary {{L_BRAND_PRIMARY}} outline).
- Three-column card row:
    1. "Upcoming appointments" — 2 rows (date/time, doctor, specialty, Reschedule / Cancel links).
    2. "Recent lab results" — 3 rows with status badges (Normal success / Follow-up warning).
    3. "Active prescriptions" — 3 rows with dosage + "Refill" secondary CTA.
    Each card: {{L_SURFACE}}, 1px border {{L_BORDER}}, radius 12px, padding 24px.
- Bottom strip: "Messages from your care team" preview — 2 message previews.
- Footer: tenant "{{TENANT_EXAMPLE_EN}}" on LEFT; copyright + terms on RIGHT.

Output canvas: 1440 × 900 PNG.
```

---

#### E2 — `portal-home-arabic-light.png`

**Attach:** E1 for structure, A9 for Arabic wordmark.

```
INCLUDE the Section 3 Global Lockdown block above.

Same as E1 but ARABIC, RTL. Greeting "أهلاً، فاطمة". Nav items mirrored.
Card titles: المواعيد القادمة / أحدث النتائج المخبرية / الوصفات النشطة.
Primary CTA "حجز موعد جديد", secondary "عرض الملف الطبي".

Output canvas: 1440 × 900 PNG.
```

---

#### E3 — `portal-appointment-booking.png`

**Attach:** E1 structure.

```
INCLUDE the Section 3 Global Lockdown block above.

Design the patient portal appointment-booking flow — LIGHT, English, LTR.

Surface: 1440 × 900.

Layout: 3-step wizard progress indicator at top ("1. Specialty  2. Doctor & time  3. Confirm").
- Currently on step 2.
- Left rail: filters (specialty, facility, insurance).
- Main: 7-day calendar strip + list of available doctors below.
    Each doctor row: photo, name "Dr. Sarah Al Anzi", specialty "Internal Medicine",
    facility "{{TENANT_EXAMPLE_EN}}", next-available time slots as chips.
- Right rail: booking summary card with primary CTA "Continue to confirmation"
  (bg {{L_CTA_BG}}).

Output canvas: 1440 × 900 PNG.
```

---

#### E4 — `portal-medical-record-arabic.png`

**Attach:** E2 for structure, A9 Arabic wordmark.

```
INCLUDE the Section 3 Global Lockdown block above.

Design the "My Medical Record" tab of the patient portal — Arabic, RTL.

Surface: 1440 × 900.

Layout:
- Portal navbar per E2 (Arabic).
- Tabs row: "الملخص", "الحساسيات", "الأدوية", "الفحوصات المخبرية", "الأشعة",
  "التطعيمات" (active: "الأدوية").
- Content: table of active medications with columns
    الدواء | الجرعة | التكرار | الطبيب الوصف | تاريخ البدء.
    Row-status badges use semantic tokens.
  Right sidebar card: "معلومات هامة" info box with the info bg/text pair.

Output canvas: 1440 × 900 PNG.
```

---

### F. SMART / FHIR consent surfaces (2 prompts)

---

#### F1 — `smart-consent-light.png`

**Attach:** C1 palette, A4 logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design the SMART-on-FHIR authorisation/consent screen — LIGHT theme, English.

Surface: 1200 × 1200 (centred consent card).

Body background {{L_BG}}. Centre a card 640 × 720:
  {{L_SURFACE}}, 16px radius, 1px border {{L_BORDER}}, subtle shadow.

Card contents (top-down):
- {{BRAND_NAME_EN}} horizontal logo from A4, height 74px.
- Sub-header "Authorization Request" ({{FONT_LATIN}} 600 20/28, {{L_TEXT_SECONDARY}}).
- Requesting-app row: 48x48 app icon placeholder, name "MyHealth Reader"
  ({{FONT_LATIN}} 600 16/24 {{L_TEXT_PRIMARY}}), developer "Ithra Health Apps"
  ({{FONT_LATIN}} 400 14/20 {{L_TEXT_SECONDARY}}).
- Body copy: "MyHealth Reader is requesting access to your {{BRAND_NAME_EN}}
  health record. Please review the requested permissions."
- Permission list:
    ✓ Read your demographic information
    ✓ Read your medications
    ✓ Read your allergies
    ✓ Read your recent lab results
    ✗ Write access (grayed, {{L_TEXT_DISABLED}})
- Info banner: bg {{L_INFO_BG}}, border {{L_INFO_TEXT}} 1px, text {{L_INFO_TEXT}}:
  "You can revoke this app's access at any time from Settings."
- Buttons row (right-aligned):
    Secondary "Deny" ({{L_BRAND_PRIMARY}} outline + text).
    Primary "Allow" (bg {{L_CTA_BG}}, text {{L_CTA_TEXT}}, {{FONT_LATIN}} 600).
- Footer under card: small centred "Powered by {{BRAND_NAME_EN}} on FHIR R4".

Output canvas: 1200 × 1200 PNG.
```

---

#### F2 — `smart-consent-dark.png`

**Attach:** C2 dark palette, A5 dark logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Same as F1 but DARK theme. Substitutions:
- Body {{D_BG}}. Card {{D_SURFACE}}, border {{D_BORDER}}.
- Logo: A5 (dark-primary variant).
- Text.primary {{D_TEXT_PRIMARY}}, secondary {{D_TEXT_SECONDARY}}.
- Primary CTA: bg {{D_CTA_BG}}, text {{D_CTA_TEXT}}.
- Info banner: matching dark semantic info tokens.
- Focus ring: {{D_FOCUS_RING}}.

Output canvas: 1200 × 1200 PNG.
```

---

### G. Print / PDF surfaces (4 prompts)

---

#### G1 — `print-statement-color.png` (A4 300 DPI)

**Attach:** A4 logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a {{BRAND_NAME_EN}} patient billing statement — A4 portrait
(2480 × 3508 at 300 DPI), FULL-COLOUR variant for screen + colour print.

Layout:
- Top band (15% height):
    Left: A4 primary logo (100mm equivalent).
    Right: tenant identity — "{{TENANT_EXAMPLE_EN}}", English address, phone,
      VAT. Below in smaller type: Arabic name "{{TENANT_EXAMPLE_AR}}" and
      Arabic address.
- Meta strip: Statement number | Date | Due date | Account number.
- Patient / guarantor block (right column) + Insurance / payer block (left column).
- Services table: Date | Code | Description | Qty | Unit Price (SAR) | Total (SAR).
    6-8 realistic healthcare line items with subtotals per department.
- Totals block (right-aligned): Subtotal / VAT (15%) / Amount paid / Balance due.
    Balance-due chip: text {{L_CRITICAL_TEXT}} on bg {{L_CRITICAL_BG}},
    1px border {{L_CRITICAL_TEXT}}.
- Payment instructions with QR-code placeholder square.
- Footer with statement disclaimer and page indicator.

Colour usage:
  Headings {{L_BRAND_PRIMARY}}. Coral {{L_CTA_BG}} only on Balance-due chip
  border and "Pay online" underline. Table header row bg {{L_SURFACE_SUNKEN}}.
  Zebra rows {{L_SURFACE}} / {{L_BG}}.

Output canvas: 2480 × 3508 PNG.
```

---

#### G2 — `print-statement-monochrome.png`

**Attach:** A7 black logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Same layout as G1 but MONOCHROME — for black-and-white print, fax,
photocopy fidelity.

Rules:
- ONLY black (#000000) and white (#FFFFFF). NO greys derived from screening.
- Logo: A7 pure-black variant.
- Emphasis through weight and 1-2pt rules, never colour.
- Balance-due chip: 2pt black border, black bold text on white.
- Zebra striping replaced with 0.5pt border-bottom on every row.

Output canvas: 2480 × 3508 PNG.
```

---

#### G3 — `print-prescription.png`

**Attach:** A4 logo, A7 black logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a bilingual prescription document — A4 portrait 2480 × 3508.

Layout:
- Header: tenant + {{BRAND_NAME_EN}} logo (A4) on the left; prescription
  serial number and issue date on the right.
- Patient block: name (English + Arabic), MRN, DoB, allergies (semantic
  critical badge if any).
- Prescription table:
    Drug (generic name) | Strength | Dosage | Duration | Notes.
    3-5 realistic entries.
- Prescriber signature block: printed name, license number, e-signature line.
- Footer disclaimer + QR-code for verification.

Colour usage: minimal — {{L_BRAND_PRIMARY}} for the tenant name only; body
text {{L_TEXT_PRIMARY}}.

Output canvas: 2480 × 3508 PNG.
```

---

#### G4 — `print-lab-report.png`

**Attach:** A4 logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a laboratory results report — A4 portrait 2480 × 3508, English.

Layout:
- Header: tenant + {{BRAND_NAME_EN}} logo (A4). Report number, collection
  date, resulting date.
- Patient info block, ordering physician info block.
- Results table:
    Test | Result | Unit | Reference range | Flag.
    Include 10-12 realistic panels. Use semantic tokens for flags:
      Normal (no badge), High/Low = warning bg + text, Critical = critical bg + text.
- Interpretation notes section.
- Signature and lab director credential block.

Output canvas: 2480 × 3508 PNG.
```

---

### H. Email surfaces (4 prompts)

Simple, email-client-safe HTML aesthetics. Use system-font fallback stacks (Gmail/Outlook strip @font-face).

---

#### H1 — `email-appointment-bilingual.png`

**Attach:** A4 logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a bilingual transactional email — two versions stacked on ONE 600px
wide canvas. English on top, Arabic below.

For BOTH versions:
- Outer page bg {{L_BG}}. Inner card {{L_SURFACE}}, 12px radius, 32px padding.
- Header: A4 primary logo, 240 × 88 rendered.
- Body copy per language.
- Details card {{L_SURFACE_SUNKEN}}, 16px padding, 12px radius, with
  DATE / TIME / DOCTOR / LOCATION rows.
- Primary CTA button 240px wide, bg {{L_CTA_BG}}, text {{L_CTA_TEXT}},
  {{FONT_LATIN}} 600, 12px radius, 44px tall.
- Secondary "Reschedule or cancel" text link.
- Footer: small print with tenant, unsubscribe link, tiny 32x32 symbol.

English body: "Hi Fatimah, your appointment with Dr. Sarah Al Anzi is
confirmed for Thursday, 12 Aug 2026 at 12:30 PM at {{TENANT_EXAMPLE_EN}}."
CTA: "View Appointment".

Arabic body: "مرحباً فاطمة، تم تأكيد موعدك مع الدكتورة سارة العنزي
يوم الخميس ١٢ أغسطس ٢٠٢٦ في الساعة ١٢:٣٠ م في {{TENANT_EXAMPLE_AR}}."
CTA: "عرض الموعد".

Email-client constraint: no web fonts. Use system stacks:
  Latin: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif.
  Arabic: 'Segoe UI', Tahoma, Arial, sans-serif.

Output: single PNG 600 wide × sufficient height for both emails.
```

---

#### H2 — `email-password-reset-bilingual.png`

**Attach:** A4 logo, H1 for style consistency.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a bilingual password-reset email — same 600px canvas layout as H1,
same header + footer + CTA styling.

English body: "We received a request to reset your {{BRAND_NAME_EN}}
account password. If this was you, click the button below within 30 minutes
to set a new password. If not, you can safely ignore this email."
CTA: "Reset password".

Arabic body: equivalent right-aligned.
CTA: "إعادة تعيين كلمة المرور".

Security notice below CTA (both languages): small info box with the info
semantic tokens, listing "Never share this link. {{BRAND_NAME_EN}} will
never ask for your password by email or phone."

Output: single PNG 600 × sufficient height.
```

---

#### H3 — `email-prescription-notification-bilingual.png`

**Attach:** A4 logo, H1 for style consistency.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a bilingual prescription-issued email — 600px canvas, H1 styling.

English body: "Dr. Sarah Al Anzi has issued a new prescription for you at
{{TENANT_EXAMPLE_EN}}. You can pick it up at your preferred pharmacy or
have it delivered."
Details card: Prescription ID, Issue date, Prescriber, 2-3 medication rows.
CTA: "View prescription".

Arabic body: right-aligned equivalent.
CTA: "عرض الوصفة".

Add allergy-warning info box IF the patient record has known allergies.

Output: single PNG 600 × sufficient height.
```

---

#### H4 — `email-telehealth-invitation-bilingual.png`

**Attach:** A4 logo, H1 for style consistency.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a bilingual telehealth-visit invitation email — 600px canvas.

The primary logo MUST be reachable at a public URL because telehealth
emails embed absolute logo references (per {{BRAND_NAME_EN}} email rule
in Global Lockdown).

English body: "Your telehealth visit with Dr. Sarah Al Anzi is scheduled
for Thursday, 12 Aug 2026 at 12:30 PM. Please join 5 minutes early to test
your camera and microphone."
CTA: "Join visit".
Below CTA: bulleted list of technical prerequisites (browser, camera, mic,
stable connection).

Arabic body: right-aligned equivalent.
CTA: "الانضمام إلى الزيارة".

Output: single PNG 600 × sufficient height.
```

---

### I. Brand-guideline supporting sheets (5 prompts)

These live in `brand/guidelines/` and are shipped to Group 2 + downstream implementers.

---

#### I1 — `guidelines-logo-clear-space.png`

**Attach:** A4 logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a clear-space rule diagram for the {{BRAND_NAME_EN}} primary
horizontal logo.

Layout:
- Centre the A4 logo on a plain {{L_BG}} canvas.
- Overlay a 1px {{L_TEXT_SECONDARY}} dashed rectangle around it representing
  the minimum clear space.
- Label the clear-space unit as "1 × x", where x = the width of the symbol's
  central negative-space element.
- Arrows on each side of the logo indicating "≥ 1 x on all sides".
- Below: caption "Never place text, artwork, or edges of layout inside the
  dashed area."

Output canvas: 1920 × 1200 PNG.
```

---

#### I2 — `guidelines-logo-minimum-size.png`

**Attach:** A1 symbol, A4 logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a minimum-size diagram showing:
- Horizontal logo at 120px, 96px, 72px — with a green check on 120px, warnings on 96px, red X on 72px.
- Symbol-only mark at 32px, 24px, 16px, 12px — with the 12px marked "below minimum".
- Print equivalents: horizontal 25mm, symbol 8mm minimum, labelled.

Body {{L_BG}}. Labels in {{FONT_LATIN}} 500 12/16 {{L_TEXT_SECONDARY}}.

Output canvas: 1920 × 1200 PNG.
```

---

#### I3 — `guidelines-logo-backgrounds.png`

**Attach:** A4, A5, A7, A8.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a background-rule chart showing the correct logo variant to use on
each background type:

Row of 5 background swatches (each 400x400):
  1) {{L_BG}} → A4 (primary horizontal, coloured)
  2) {{L_BRAND_PRIMARY}} → A8 (white monochrome)
  3) {{D_BG}} → A5 (dark-theme primary)
  4) Neutral light photo (blurred abstract) → A7 (black monochrome) or A4, depending on contrast
  5) {{L_BRAND_ACCENT}} → A8 (white monochrome)

Each swatch shows the logo variant placed correctly.
Below each swatch a label: "USE {A#}" and the reason
("colour on light neutral", "reversed for brand block", etc.).

Output canvas: 2400 × 1200 PNG.
```

---

#### I4 — `guidelines-logo-misuse.png`

**Attach:** A4 logo.

```
INCLUDE the Section 3 Global Lockdown block above.

Design a "DO NOT" misuse chart. Show the A4 logo with 6 forbidden treatments,
each with a red diagonal strike-through:

  1) Stretched horizontally.
  2) Rotated 15°.
  3) Drop shadow applied.
  4) Coloured with a non-brand hue (e.g. purple).
  5) Placed over noisy photo without a clear-space plate.
  6) Symbol and wordmark separated / rearranged.

Each panel labelled with the specific rule violated.

Output canvas: 1920 × 1200 PNG.
```

---

#### I5 — `guidelines-icon-set-style.png`

**Attach:** none (fresh iconography spec).

```
INCLUDE the Section 3 Global Lockdown block above.

Design the {{BRAND_NAME_EN}} iconography style-guide sheet — the rules
future icons must follow.

Layout on {{L_BG}}:
- 12-icon strip demonstrating the style: home, patient, calendar, records,
  prescription, lab, message, bell, gear, search, help, sign-out.
- All icons rendered at 24 × 24 with:
    2px stroke weight, round line caps, round line joins, 2px external corner radius,
    outline (not filled) as primary variant,
    single colour {{L_TEXT_SECONDARY}}.
- Below: a 2nd row showing the same 12 icons in FILLED variant using
  {{L_TEXT_PRIMARY}}.
- Grid overlay showing the 24×24 keyline construction with 1px, 2px, 4px
  safe zones labelled.
- Notes: "Never mix stroke and fill in the same navigation cluster. Match
  visual weight across the row."

Output canvas: 1920 × 1200 PNG.
```

---

## Section 5 — Post-generation checklist

After Recraft returns each PNG, before you accept it as evidence for the branding-production package:

1. Open the file and confirm the exact **canvas dimensions** requested in the prompt (no accidental crop, no aspect drift).
2. Verify the **symbol geometry** matches the A1 reference (no drift between generations — Recraft occasionally re-interprets). If drift is detected, re-attach A1 and re-render.
3. Check every **hex value** rendered in the image against the palette sheet. Recraft may output visually similar but numerically-off colours — validate with a colour picker where in doubt.
4. Confirm **typography rendering** matches the specimen sheet (weights especially — Recraft occasionally substitutes similar-weight fonts).
5. Confirm **text content** matches the prompt letter-for-letter. Save with the exact filename shown in the prompt heading.
6. Place the file into **BOTH** its design-package folder AND (for A + B prompts only) its **runtime destination** inside the OpenEMR tree. The full path map is in [Section 6 — Runtime placement map](#section-6--runtime-placement-map). Prompts C–I are evidence and stay in `brand/` only — they have no runtime path.
7. Regenerate the manifest + SHA-256 sums (`brand/manifests/asset-manifest.{json,csv}` and `brand/manifests/SHA256SUMS`).
8. Re-run the Group 1.5B QA matrix.
9. Review [Section 7 — Orphan cleanup candidates](#section-7--orphan-cleanup-candidates) and remove obsolete brand files that will no longer be referenced.

If Recraft's output diverges from the brand identity in any generation, prefer to **regenerate that single prompt** with the affected reference re-attached rather than accept a drifted render. Never fix-up Recraft output by hand for an audit deliverable — the fix would not be reproducible.

---

## Section 6 — Runtime placement map

For every Recraft prompt that produces an **asset** (Sections A + B), the table below shows the **exact filename Recraft must output**, the **design-package folder** where it lives during audit, and the **runtime destination(s)** inside the OpenEMR tree where the file must physically exist for OpenEMR to serve it. Filenames are case-sensitive on Linux deploy targets.

Prompts C–I (palette sheets, typography specimen, UI mockups, SMART consent, print statement mockups, email mockups, brand-guideline sheets) produce **evidence only** and are not copied into the OpenEMR runtime — they live in `brand/` and inform Group 2 integration.

### 6.1 Runtime resolution — how OpenEMR picks each file

OpenEMR reads brand assets in this precedence (last match wins):

1. **Per-site override** — `sites/<site_id>/images/logos/<slot>/logo.<ext>` — always highest priority.
2. **Product default** — `public/images/logos/<area>/<slot>/logo.<ext>` — served if no per-site file exists.
3. **Legacy hardcoded path** — a handful of pre-`LogoService` code paths still reference specific filenames under `public/images/…` or `sites/<site>/images/…`.

Cache-busting is automatic via `?t=<mtime>` — you do **not** need to rename files to invalidate a cache.

### 6.2 Prompt → design path + runtime path table

**Legend:**
- **Design path** — where the Recraft output goes for the audit package.
- **Runtime path(s)** — where the file MUST also exist for OpenEMR to actually serve it. Multiple paths = copy to each.
- **Ext** — the required file extension at the runtime path. Recraft returns PNG; SVG runtime slots need the SVG master exported separately (either Recraft's SVG export or an ImageMagick trace of the PNG).
- ⭐ = a per-site slot; per-tenant overrides go at `sites/<site_id>/images/logos/<same_slot>/logo.<ext>`.

#### Section A — Master logo system

| Prompt | Recraft output filename | Design path | Runtime path(s) | Ext | Notes |
|---|---|---|---|---|---|
| A1 | `brand-symbol.svg` | `brand/master/brand-symbol.svg` + `brand/logos/symbol/brand-symbol.svg` | ⭐ `public/images/logos/core/menu/primary/logo.svg` | SVG | Rendered at 16 CSS px by `interface/main/main.php:48`; also the source for B8 favicon and B4 navbar raster. |
| A2 | `brand-symbol-black.svg` | `brand/master/brand-symbol-black.svg` + `brand/logos/symbol/brand-symbol-black.svg` | — | SVG | Master only — used for monochrome print derivatives (G2, G3). |
| A3 | `brand-symbol-white.svg` | `brand/master/brand-symbol-white.svg` + `brand/logos/symbol/brand-symbol-white.svg` | — | SVG | Master only — used for dark-background raster derivatives. |
| A4 | `brand-logo-primary.svg` | `brand/master/brand-logo-primary.svg` + `brand/logos/primary/brand-logo-primary.svg` | — | SVG | Master only. The 1053×390 raster derived from it (B1) is the runtime file. |
| A5 | `brand-logo-primary-dark.svg` | `brand/master/brand-logo-primary-dark.svg` + `brand/logos/primary/brand-logo-primary-dark.svg` | — | SVG | Master only. Used for Dark-theme SMART consent (F2) and dark-nav slot. |
| A6 | `brand-logo-compact.svg` | `brand/master/brand-logo-compact.svg` + `brand/logos/compact/brand-logo-compact.svg` | — | SVG | Master only. Source for B3 tiny-logo rasters and B4 navbar retina raster. |
| A7 | `brand-logo-black.svg` | `brand/master/brand-logo-black.svg` + `brand/logos/monochrome/brand-logo-black.svg` | — | SVG | Master only. Source for the `practice_logo.gif` statement mark (§B legacy). |
| A8 | `brand-logo-white.svg` | `brand/master/brand-logo-white.svg` + `brand/logos/monochrome/brand-logo-white.svg` | — | SVG | Master only. Used by any dark-background raster. |
| A9 | `brand-wordmark-arabic.svg` | `brand/master/brand-wordmark-arabic.svg` | — | SVG | Master only. Consumed by RTL raster derivatives when an Arabic wordmark is needed. |

#### Section B — Production raster exports (these are the files the runtime actually reads)

| Prompt | Recraft output filename | Design path | Runtime path(s) | Ext | Notes |
|---|---|---|---|---|---|
| B1 | `login-primary-1053x390.png` | `brand/logos/login/login-primary-1053x390.png` | ⭐ `public/images/logos/core/login/primary/logo.png` + legacy `public/images/login-logo.png` | PNG | Both runtime paths must exist. The legacy `login-logo.png` is still referenced by pre-LogoService callers. |
| B2 | `login-secondary-300x100.png` | `brand/logos/login/login-secondary-300x100.png` | ⭐ `public/images/logos/core/login/secondary/logo.png` + legacy `public/images/logo-full-con.png` (at 870×222 — see B7) | PNG | The 300×100 goes into the login/secondary slot. The `logo-full-con.png` legacy path takes the 870×222 render from B7, not this one. |
| B3a | `login-small-a-101x100.png` | `brand/logos/login/login-small-a-101x100.png` | ⭐ `public/images/logos/core/login/small_logo_1/logo.png` | PNG | Only served when `tiny_logo_1 = 1`. |
| B3b | `login-small-b-101x100.png` | `brand/logos/login/login-small-b-101x100.png` | ⭐ `public/images/logos/core/login/small_logo_2/logo.png` | PNG | Only served when `tiny_logo_2 = 1`. |
| B4 | `navbar-symbol.png` | `brand/logos/navbar/navbar-symbol.png` | — | PNG | Retina raster (64×64) not directly served — the SVG at `logos/core/menu/primary/logo.svg` (from A1) is used. Ship the PNG only if `main.php` fallback is triggered. |
| B5 | `portal-login-primary-1053x390.png` | `brand/logos/portal/portal-login-primary-1053x390.png` | ⭐ `public/images/logos/portal/login/primary/logo.png` | PNG | Portal login primary slot. |
| B6 | `portal-login-secondary-300x100.png` | `brand/logos/portal/portal-login-secondary-300x100.png` | ⭐ `public/images/logos/portal/login/secondary/logo.png` | PNG | Portal login secondary slot — this slot did NOT exist by default in OpenEMR; ship this file to fill it. Only served when `extra_portal_logo_login = 1`. |
| B7 | `portal-navbar-870x222.png` | `brand/logos/portal/portal-navbar-870x222.png` | ⭐ `public/images/logos/portal/menu/primary/logo.png` + legacy `public/images/logo-full-con.png` | PNG | Portal navbar. The legacy `logo-full-con.png` takes this render at 870×222. |
| B8a | `favicon.svg` (source) | `brand/favicon/favicon.svg` | ⭐ `public/images/logos/core/favicon/favicon.svg` | SVG | Optional — served to modern browsers that prefer SVG favicons. |
| B8b | `favicon-16x16.png` | `brand/favicon/favicon-16x16.png` | — | PNG | Not directly served — used inside the ICO. |
| B8c | `favicon-32x32.png` | `brand/favicon/favicon-32x32.png` | Legacy `public/images/favicon-32x32.png` | PNG | Directly referenced by 2 hardcoded consumers. |
| B8d | `favicon-48x48.png` | `brand/favicon/favicon-48x48.png` | — | PNG | Not directly served — used inside the ICO. |
| B8e | `favicon.ico` | `brand/favicon/favicon.ico` | ⭐ `public/images/logos/core/favicon/favicon.ico` + legacy `public/images/favicon.ico` **(NEW file — currently missing, causes 5 × HTTP 404)** | ICO | Real multi-frame ICO with 16 + 32 + 48. The `public/images/favicon.ico` file must be **created**. |

#### Legacy compatibility rasters (derived from A + B masters — must also be shipped)

These are not standalone Recraft prompts; they are re-exports of the masters at correct legacy dimensions. Ship all three to close the last-mile compatibility gap.

| Derived from | Filename | Design path | Runtime path | Ext | Notes |
|---|---|---|---|---|---|
| A1 (symbol) | `menu-logo.png` | `brand/logos/legacy/menu-logo.png` | Legacy `public/images/menu-logo.png` | PNG **287 × 287** | Rendered at 16 CSS px; but the 287×287 canvas matches the OpenEMR expected legacy raster dimension. |
| A6 (compact) | `login_logo.gif` | `brand/logos/legacy/login_logo.gif` | Legacy `sites/default/images/login_logo.gif` | GIF **250 × 221** | Eye-Magic form login variant. Only served when the Eye-Magic form module is enabled — if it's disabled you can skip this file. |
| A7 (black wordmark) | `practice_logo.gif` | `brand/logos/legacy/practice_logo.gif` | Legacy `sites/default/images/practice_logo.gif` | GIF **~600 × ~220** | Statement / receipt / PDF logo. Referenced by the `statement_logo` global. |
| A6 (compact) | `logo_1.png` | `brand/logos/legacy/logo_1.png` | Legacy `sites/default/images/logo_1.png` | PNG **86 × 43** | Tiny logo 1 (legacy slot). |
| A6 (compact) | `logo_2.png` | `brand/logos/legacy/logo_2.png` | Legacy `sites/default/images/logo_2.png` | PNG **86 × 43** | Tiny logo 2 (legacy slot). May be byte-identical to `logo_1.png` — the spec does not require them to differ. |

#### Sections C–I — evidence only (no runtime path)

| Prompt | Design path | Runtime path |
|---|---|---|
| C1 palette light | `brand/colors/palette-swatch-sheet-light.png` | — |
| C2 palette dark | `brand/colors/palette-swatch-sheet-dark.png` | — |
| C3 typography specimen | `brand/typography/typography-specimen.png` | — |
| D1 login English light | `brand/guidelines/login-english-light.png` | — |
| D2 login English dark | `brand/rtl/english-login-dark.png` (or `brand/guidelines/`) | — |
| D3 login Arabic light | `brand/rtl/arabic-login-light.png` | — |
| D4 login Arabic dark | `brand/rtl/arabic-login-dark.png` | — |
| D5 navbar English | `brand/guidelines/navbar-english-tenant-lockup.png` | — |
| D6 navbar Arabic | `brand/guidelines/navbar-arabic-tenant-lockup.png` | — |
| D7 dashboard English | `brand/guidelines/dashboard-english-light.png` | — |
| D8 dashboard Arabic | `brand/rtl/arabic-dashboard-light.png` | — |
| D9 clinical form English | `brand/guidelines/clinical-form-english-light.png` | — |
| D10 clinical form Arabic | `brand/rtl/arabic-clinical-form-light.png` | — |
| E1 portal home English | `brand/guidelines/portal-home-english-light.png` | — |
| E2 portal home Arabic | `brand/rtl/arabic-portal-light.png` | — |
| E3 portal appointment booking | `brand/guidelines/portal-appointment-booking.png` | — |
| E4 portal medical record Arabic | `brand/rtl/arabic-portal-medical-record.png` | — |
| F1 SMART consent light | `brand/smart/smart-consent-light.png` (or split from `smart-consent-light-dark.png`) | — |
| F2 SMART consent dark | `brand/smart/smart-consent-dark.png` | — |
| G1 print statement color | `brand/logos/print/statement-color.png` | — |
| G2 print statement mono | `brand/logos/print/statement-mono.png` | — |
| G3 print prescription | `brand/logos/print/prescription.png` | — |
| G4 print lab report | `brand/logos/print/lab-report.png` | — |
| H1 email appointment | `brand/email/email-appointment-bilingual.png` | — |
| H2 email password reset | `brand/email/email-password-reset-bilingual.png` | — |
| H3 email prescription | `brand/email/email-prescription-notification-bilingual.png` | — |
| H4 email telehealth | `brand/email/email-telehealth-invitation-bilingual.png` | — |
| I1 clear-space | `brand/guidelines/guidelines-logo-clear-space.png` | — |
| I2 minimum size | `brand/guidelines/guidelines-logo-minimum-size.png` | — |
| I3 backgrounds | `brand/guidelines/guidelines-logo-backgrounds.png` | — |
| I4 misuse | `brand/guidelines/guidelines-logo-misuse.png` | — |
| I5 icon-set style | `brand/guidelines/guidelines-icon-set-style.png` | — |

### 6.3 Per-tenant overlay (multi-tenant deployments)

To brand a specific tenant differently from the product default, drop the same filenames into the per-site slot:

```
sites/<site_id>/images/logos/core/login/primary/logo.png
sites/<site_id>/images/logos/core/login/secondary/logo.png
sites/<site_id>/images/logos/core/login/small_logo_1/logo.png
sites/<site_id>/images/logos/core/login/small_logo_2/logo.png
sites/<site_id>/images/logos/core/menu/primary/logo.svg
sites/<site_id>/images/logos/core/favicon/favicon.ico
sites/<site_id>/images/logos/portal/login/primary/logo.png
sites/<site_id>/images/logos/portal/login/secondary/logo.png
sites/<site_id>/images/logos/portal/menu/primary/logo.png
```

`LogoService` picks the per-site file over the product default automatically (last-match-wins). No configuration change is required.

### 6.4 Verification after placement

After copying files to their runtime destinations:

```powershell
# From project root — confirm every runtime slot exists and its SHA matches the design package.
$root = 'G:\My Drive\OpenEMR'
$pairs = @(
  @{ src='brand\logos\login\login-primary-1053x390.png';   dst='public\images\logos\core\login\primary\logo.png' },
  @{ src='brand\logos\login\login-primary-1053x390.png';   dst='public\images\login-logo.png' },
  @{ src='brand\logos\login\login-secondary-300x100.png';  dst='public\images\logos\core\login\secondary\logo.png' },
  @{ src='brand\logos\login\login-small-a-101x100.png';    dst='public\images\logos\core\login\small_logo_1\logo.png' },
  @{ src='brand\logos\login\login-small-b-101x100.png';    dst='public\images\logos\core\login\small_logo_2\logo.png' },
  @{ src='brand\master\brand-symbol.svg';                  dst='public\images\logos\core\menu\primary\logo.svg' },
  @{ src='brand\favicon\favicon.ico';                      dst='public\images\logos\core\favicon\favicon.ico' },
  @{ src='brand\favicon\favicon.ico';                      dst='public\images\favicon.ico' },
  @{ src='brand\favicon\favicon-32x32.png';                dst='public\images\favicon-32x32.png' },
  @{ src='brand\logos\portal\portal-login-primary-1053x390.png';    dst='public\images\logos\portal\login\primary\logo.png' },
  @{ src='brand\logos\portal\portal-login-secondary-300x100.png';   dst='public\images\logos\portal\login\secondary\logo.png' },
  @{ src='brand\logos\portal\portal-navbar-870x222.png';   dst='public\images\logos\portal\menu\primary\logo.png' },
  @{ src='brand\logos\portal\portal-navbar-870x222.png';   dst='public\images\logo-full-con.png' },
  @{ src='brand\logos\legacy\menu-logo.png';               dst='public\images\menu-logo.png' },
  @{ src='brand\logos\legacy\login_logo.gif';              dst='sites\default\images\login_logo.gif' },
  @{ src='brand\logos\legacy\practice_logo.gif';           dst='sites\default\images\practice_logo.gif' },
  @{ src='brand\logos\legacy\logo_1.png';                  dst='sites\default\images\logo_1.png' },
  @{ src='brand\logos\legacy\logo_2.png';                  dst='sites\default\images\logo_2.png' }
)
foreach ($p in $pairs) {
    $sSrc = Join-Path $root $p.src
    $sDst = Join-Path $root $p.dst
    if (-not (Test-Path $sDst)) { "MISSING  $($p.dst)"; continue }
    $hSrc = (Get-FileHash -Algorithm SHA256 $sSrc).Hash
    $hDst = (Get-FileHash -Algorithm SHA256 $sDst).Hash
    if ($hSrc -ne $hDst) { "MISMATCH $($p.dst)" } else { "OK       $($p.dst)" }
}
```

Any `MISSING` or `MISMATCH` row indicates the runtime placement is incomplete.

---

## Section 7 — Orphan cleanup candidates

Files and folders currently tracked in the OpenEMR tree that have **no runtime brand role after this rebrand lands**. Review each before deleting; the ones marked ✅ are safe to remove, ⚠️ require a condition to be met first.

### 7.1 Installer theme-picker screenshots — [public/images/stylesheets/](public/images/stylesheets/) — **22 files, ~740 KB**

Each file is a preview thumbnail of an upstream OpenEMR theme, consumed only by the installer's theme picker. Per `Q77` the SaaS product ships exactly **two variants (light + dark)**, so the picker is either bypassed or restricted at build time. Once that is confirmed:

| Verdict | Files |
|---|---|
| ⚠️ Keep until installer picker is confirmed disabled | `style_light.png`, `style_dark.png` |
| ✅ Delete unconditionally (theme not shipped) | `style_ash_blue.png`, `style_burgundy.png`, `style_cadmium_yellow.png`, `style_chocolate.png`, `style_cobalt_blue.png`, `style_coral.png`, `style_deep_purple.png`, `style_dune.png`, `style_emerald.png`, `style_forest_green.png`, `style_manila.png`, `style_mauve.png`, `style_mustard_green.png`, `style_olive.png`, `style_pink.png`, `style_powder_blue.png`, `style_red.png`, `style_sienna.png`, `style_superhero.png`, `style_tangerine.png` |

Deleting the 20 unconditional files reclaims **≈ 700 KB** and removes 20 potential brand-drift surfaces.

### 7.2 Review / donations link images — **2 files, ~68 KB**

`BRAND-034` / rebranding plan §5.7 WS-G hides the review and donations links via `display_review_link = 0` and `display_donations_link = 0`. Once those globals are set:

| Verdict | File | Size |
|---|---|---:|
| ✅ Delete | `public/images/review-logo.png` | 30,549 |
| ✅ Delete | `public/images/review-logo.svg` | 37,706 |

### 7.3 Non-brand personal file in the handoff folder — **1 file, ~2 MB**

Confirmed unrelated during the second-pass handoff audit — an Arabic personal image (rose bouquet with note), no brand role:

| Verdict | File | Size |
|---|---|---:|
| ✅ Delete | `docs/Thiqa_Group_1_5B_Handoff/cd61f749-19eb-4cad-98a4-2f6c5671718b.png` | 2,084,266 |

### 7.4 Redundant handoff zip — **1 file, ~82 KB**

`image (5).zip` inside the handoff folder contains the same 11 SVG masters already extracted to `docs/Thiqa_Group_1_5B_Handoff/inputs/svg_masters_unmapped/` (SHA-256 pairs all match). Once the audit trail is archived elsewhere:

| Verdict | File | Size |
|---|---|---:|
| ⚠️ Delete once the audit is signed off | `docs/Thiqa_Group_1_5B_Handoff/image (5).zip` | 82,639 |

The two larger zips (`First project.zip`, `First project (1).zip`) contain the Recraft mockup source PNGs and stay as provenance evidence — do **not** delete.

### 7.5 Redundant / superseded design-package files

`brand/logos/legacy/` contains a `practice-logo-compatible.png` PNG which is byte-identical to `brand/logos/print/practice-logo-print.png` (same SHA-256 `b536d80d…`). The corrected `practice_logo.gif` re-export (Group 1.5B correction task) is what the runtime actually needs:

| Verdict | File | Size |
|---|---|---:|
| ✅ Delete (dup of print/practice-logo-print.png) | `brand/logos/legacy/practice-logo-compatible.png` | 118,869 |
| ✅ Delete (renamed by tooling to `-a` / `-b`) | `brand/logos/legacy/legacy-logo-86x43-a.png` | 2,884 |
| ✅ Delete (renamed by tooling to `-a` / `-b`) | `brand/logos/legacy/legacy-logo-86x43-b.png` | 2,884 |

Keep `logo_1.png`, `logo_2.png`, `login_logo.gif`, `login-logo.png`, `logo-full-con.png`, `menu-logo.png`, `practice_logo.gif` — those are the runtime-destination filenames.

### 7.6 Items that look orphaned but MUST be preserved

Do **not** delete any of the following, even though they look like orphaned brand rasters:

| File | Reason |
|---|---|
| `public/images/cms1500.png` (~2 MB) | CMS-1500 regulatory form — locked by C7 in the rebranding plan |
| `public/images/ub04.svg` (~147 KB) | UB-04 regulatory form — locked by C7 |
| `sites/default/images/visa_mc_disc_credit_card_logos_176x35.gif` (~1.8 KB) | Payment-network trademark marks — locked by C7 |
| `interface/themes/oe-styles/style_solar.scss`, `style_manila.scss`, `interface/themes/colors/style_cobalt_blue.scss`, `style_forest_green.scss` | Kept for **upstream rebase compatibility** per `Q77`. The build's webpack entry map excludes them; the SCSS files themselves stay. |
| GPL, docblock, and `OpenEMR\` namespace references throughout `src/`, `library/`, `interface/` | Legal — locked by C7 / R1 preserve list |
| Session-identity constants in `src/Common/Session/SessionUtil.php` | Locked by C6 / `Q17` |

### 7.7 Cleanup command (run only after the deploy is verified)

```powershell
# Runs ONLY after Section 6.4 verification passes AND the review/donations globals are set.
$root = 'G:\My Drive\OpenEMR'
$deletes = @(
    # 7.1 — surplus installer theme previews (20 files)
    'public\images\stylesheets\style_ash_blue.png',
    'public\images\stylesheets\style_burgundy.png',
    'public\images\stylesheets\style_cadmium_yellow.png',
    'public\images\stylesheets\style_chocolate.png',
    'public\images\stylesheets\style_cobalt_blue.png',
    'public\images\stylesheets\style_coral.png',
    'public\images\stylesheets\style_deep_purple.png',
    'public\images\stylesheets\style_dune.png',
    'public\images\stylesheets\style_emerald.png',
    'public\images\stylesheets\style_forest_green.png',
    'public\images\stylesheets\style_manila.png',
    'public\images\stylesheets\style_mauve.png',
    'public\images\stylesheets\style_mustard_green.png',
    'public\images\stylesheets\style_olive.png',
    'public\images\stylesheets\style_pink.png',
    'public\images\stylesheets\style_powder_blue.png',
    'public\images\stylesheets\style_red.png',
    'public\images\stylesheets\style_sienna.png',
    'public\images\stylesheets\style_superhero.png',
    'public\images\stylesheets\style_tangerine.png',
    # 7.2 — review / donations link images
    'public\images\review-logo.png',
    'public\images\review-logo.svg',
    # 7.3 — non-brand personal file
    'docs\Thiqa_Group_1_5B_Handoff\cd61f749-19eb-4cad-98a4-2f6c5671718b.png',
    # 7.5 — redundant design-package files
    'brand\logos\legacy\practice-logo-compatible.png',
    'brand\logos\legacy\legacy-logo-86x43-a.png',
    'brand\logos\legacy\legacy-logo-86x43-b.png'
)
foreach ($rel in $deletes) {
    $p = Join-Path $root $rel
    if (Test-Path $p) { Remove-Item -Force $p; "removed  $rel" }
    else { "not-present $rel" }
}
# Optionally the redundant handoff zip (after audit sign-off):
# Remove-Item -Force (Join-Path $root 'docs\Thiqa_Group_1_5B_Handoff\image (5).zip')
```

After deletion, regenerate `brand/manifests/SHA256SUMS` (so the removed brand/legacy files no longer appear) and re-verify with the two-method check documented in [12-release-verification.md](branding-production/12-release-verification.md).

---

## Coverage cross-reference to [docs/rebranding.md](rebranding.md)

> **FLAGGED FOR HUMAN REVIEW (2026-08-19).** The `rebranding.md item` column below cites section numbers
> (§4–§20) that do not match `docs/rebranding.md`'s current, final section structure — confirmed by
> reading that file in full this session. That document's actual sections are: §0 audit header, §1 Locked
> Decisions compliance, §2 audit-of-the-audit, §3 current DB branding, §4 what Group 1A got right, §5 new
> findings, §6 bidirectional asset analysis, §7 reconciliation of inconsistencies, §8 outbound network
> identity audit, §9 the canonical 136-item BRAND inventory (9.1–9.10), §10 replacement input
> requirements, §11 Group 1D closure, §12 final gap model, §13 G-07/`Q76`, §14 G-09/`Q77`, §15 patch
> inventory, §16 Group 2 action classification (incl. the authoritative §16.2 per-ID mapping), §17
> restoration proof, §18 corrections register, §19 final coverage matrix, §20 the three final registers,
> §21 end-state integrity, §22 final verdict, §23 Group 2/3 outcomes cross-reference. None of these match
> the topics the table below assigns to the same numbers (e.g. this table's "§8 RTL/Arabic surfaces" is
> actually `rebranding.md`'s outbound-network-call audit; its "§14 mandatory patches" is actually the
> `Q77` theme-surplus decision, with the actual mandatory-patch list at §15). This table was evidently
> written against an earlier, differently-numbered draft of `rebranding.md` and never updated after that
> document's Group 1C/1D renumbering. The prompt content above this table (Sections 1–7) is unaffected —
> only this final cross-reference table's section citations are stale. Left un-rewritten here rather than
> guessed row-by-row, since correcting 17 rows to the right §9.x subsection each requires re-deriving
> intent, not a single-fact lookup.

Every item flagged in the OpenEMR brand-and-identity discovery in [docs/rebranding.md](rebranding.md) is covered by at least one prompt above:

| rebranding.md item | Prompt(s) |
|---|---|
| §4 (canonical SVGs — symbol, primary, primary-dark, compact, monochrome pair) | A1–A9 |
| §5 (PNG raster canvases 1053×390, 300×100, 101×100, 870×222, 16/32/48) | B1–B8 |
| §6 (favicon package incl. multi-res ICO) | B8 (source rasters) |
| §7 (legacy compat exports: login-logo, logo-full-con, menu-logo, login_logo.gif, logo_1, logo_2, practice_logo.gif) | B1, B2, B4, C sources (all derivable) |
| §8 (RTL / Arabic surfaces: login, navbar, clinical form, data table, patient portal) | D3, D4, D6, D8, D10, E2, E4 |
| §9 Email branding | H1–H4 |
| §10 SMART Light + Dark | F1, F2 |
| §11 Tenant / facility separation | D5, D6, G1 |
| §12 Print colour + monochrome | G1, G2, G3, G4 |
| §13 Branding globals visual context | D1–D10, E1–E4 |
| §14 Mandatory patches surface context | D1, D2, F1, F2 |
| §15 Typography usage | C3 |
| §16 WCAG contrast pair validation | C1, C2 (palette sheets used as ground-truth) |
| §17 Safe-asset actions (logo-slot replacements) | A1–A9, B1–B8 |
| §18 Governance decisions (Q76 / Q77 token materialisation, RTL capability) | A1–A9 (identity), C1/C2 (tokens), D3+E2 (RTL evidence) |
| §19 Already-correct surfaces (baseline) | Not regenerated — only the changed items need Recraft renders |
| §20 Cross-tenant cache key | Guideline-only (I3 background rules) |
| Brand Design Requirements Document §45 (delivery directory) | Post-generation checklist Section 5 |
| Brand Design Requirements Document §46–48 (masters + exports + docs) | A + B + C + D + E + F + G + H + I |
| Brand Design Requirements Document §54 (Arabic acceptance surfaces) | D3, D4, D6, D8, D10, E2, E4 |
| Brand Design Requirements Document §55 (tenant branding compatibility) | D5, D6, G1 |
| Brand Design Requirements Document §56 (SMART Light + Dark from shared tokens) | F1, F2 |

Nothing in the discovery is uncovered.

