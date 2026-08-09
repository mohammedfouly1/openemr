# GROUP 1.5B — THIQA MECHANICAL PRODUCTION, VERIFICATION & FINAL BRAND CERTIFICATION

## ROLE AND AUTHORITY
Act as the senior production engineer, SVG/asset QA engineer, accessibility verifier, and release-certification engineer for the locked Thiqa Enterprise Healthcare Identity System.

This is NOT a design task.
This is NOT permission to redesign, reinterpret, regenerate, restyle, recolor, simplify, or creatively modify Thiqa.
Do not use generative image creation.
Do not modify OpenEMR application source code in this task.

Your job is to take the supplied, already-approved Thiqa vector/source assets and close every remaining production, packaging, validation, manifest, accessibility, and QA gap using deterministic tools and evidence.

Target final status:
`BRAND DESIGN PACKAGE — ACCEPTED FOR GROUP 2`

You may output that status ONLY if every mandatory gate below actually passes.

## INPUTS
The handoff package contains:
- `inputs/svg_masters_unmapped/` — the actual SVG files exported from Recraft. Their current filenames are non-canonical (for example `image.svg`, `image (1).svg`, etc.). Never infer semantic role from filename order.
- `inputs/reference_docs/typography-weight-contract.md` — locked typography weight usage.
- Any other approved Thiqa design references, token JSON, Brand Design Requirements Document, prior asset manifest, mockups, and Group 1 branding report available in the project/workspace MUST be treated as evidence sources when present.

Before changing anything, inventory all available inputs and record SHA-256 hashes.

## NON-NEGOTIABLE RULES
1. Preserve the locked Thiqa identity exactly.
2. Do not invent evidence.
3. Do not mark PASS from an assertion; require a file, deterministic test, or documented source.
4. Never map `image (N).svg` to a canonical role from N/order alone.
5. Determine SVG roles by structural + visual evidence and comparison to approved reference assets.
6. If a role cannot be proven, mark it BLOCKED and do not guess.
7. Never overwrite original inputs. Work only in a new output directory.
8. All generated raster derivatives must come from the approved canonical SVG master, not from screenshots/mockups.
9. No stretching/distortion. Preserve artwork aspect ratio inside required canvases using transparent padding/centering where necessary.
10. No Group 2 OpenEMR integration changes in this task.
11. Every command, result, hash, dimension, validation finding, and disposition must be reproducible.

## OUTPUT ROOT
Create:
`docs/branding-production/`
and
`brand/`

Recommended structure:

brand/
  master/
  logos/
    primary/
    compact/
    symbol/
    monochrome/
    login/
    portal/
    navbar/
    print/
    legacy/
  favicon/
  colors/
  typography/
  tokens/
  smart/
  email/
  rtl/
  guidelines/
  previews/
  qa/
  manifests/

Do not alter the supplied originals.

# PHASE 0 — BASELINE AND INPUT PURITY
1. Record:
   - git branch
   - HEAD SHA
   - git status --porcelain
   - input file list
   - input byte sizes
   - SHA-256 of every input
2. Save as:
   `docs/branding-production/00-baseline.md`
3. At task end prove that no unauthorized source/application file changed.

# PHASE 1 — MAP ALL SVGs TO CANONICAL ROLES
For every supplied SVG:
1. Parse XML.
2. Record:
   - filename
   - SHA-256
   - bytes
   - width/height if declared
   - viewBox
   - number of paths
   - number of groups
   - fills/strokes
   - text elements
   - embedded `<image>` elements
   - external references
   - scripts
   - filters/masks/clipPaths
3. Render each SVG to a deterministic PNG preview on white, dark, and transparent/checkerboard backgrounds.
4. Compare visually and structurally with approved Thiqa references.
5. Assign semantic role only when supported by evidence.

Expected canonical roles include, as applicable:
- `brand-symbol.svg`
- `brand-symbol-black.svg`
- `brand-symbol-white.svg`
- `brand-logo-primary.svg`
- `brand-logo-primary-dark.svg`
- `brand-logo-compact.svg`
- `brand-logo-black.svg`
- `brand-logo-white.svg`

There may be extra SVGs. Classify extras explicitly as:
- approved alternate,
- duplicate,
- reference-only,
- or unknown.

Create:
`docs/branding-production/01-svg-role-map.md`

For each mapping include:
`Original file | Canonical role | Evidence | Confidence | SHA-256 | Disposition`

Do not proceed with a required canonical role if mapping confidence is not evidence-based.

# PHASE 2 — FULL AUTOMATED SVG VALIDATION
Validate every canonical SVG with deterministic tools.

Mandatory gates:
- valid XML
- SVG root exists
- viewBox exists
- no embedded raster `<image>`
- no script
- no unsafe external resource reference
- no broken href
- no malformed path data
- render succeeds
- transparent background where required
- finite/nonzero bounding box
- artwork not clipped
- no unexpected blank canvas
- no accidental OpenEMR artwork/text
- identical locked symbol geometry across variants where variants are expected to differ only by color

Use an available standards-capable renderer/parser such as:
- librsvg/rsvg-convert,
- CairoSVG,
- Inkscape CLI,
- browser renderer,
plus XML parsing.

Use more than one validation route where practical.

Create:
`docs/branding-production/02-svg-validation.md`

A canonical SVG cannot PASS merely because XML parses.

# PHASE 3 — CANONICAL FILENAMES
After evidence-based role mapping, copy—not destructively rename—the approved SVGs into `brand/master/` and relevant subfolders with canonical production filenames.

Required:
- brand-symbol.svg
- brand-symbol-black.svg
- brand-symbol-white.svg
- brand-logo-primary.svg
- brand-logo-primary-dark.svg
- brand-logo-compact.svg
- brand-logo-black.svg
- brand-logo-white.svg

If an exact required role does not exist and cannot be deterministically derived solely by color substitution from an identical approved geometry, STOP and report the gap. Do not redesign.

# PHASE 4 — EXACT PNG PRODUCTION EXPORTS
Render from canonical SVGs using deterministic vector rendering.

Required exact canvases:
- `login-primary-1053x390.png`
- `login-secondary-300x100.png`
- `login-small-a-101x100.png`
- `login-small-b-101x100.png`
- `navbar-symbol.png` (document actual chosen production canvas and verify 16px rendered-height use)
- `portal-login-primary-1053x390.png`
- `portal-login-secondary-300x100.png`
- `portal-navbar-870x222.png`
- `favicon-16x16.png`
- `favicon-32x32.png`
- `favicon-48x48.png`
- `practice-logo-print.png`
- `legacy-logo-86x43-a.png`
- `legacy-logo-86x43-b.png`

Rules:
- exact pixel dimensions, verified after export
- preserve aspect ratio
- center optically
- use transparent padding rather than distortion
- preserve alpha
- no interpolation artifacts caused by scaling from a raster source
- no blur
- no unintended background
- record renderer and command used

For each output verify with an image parser:
`filename | width | height | mode | alpha | bytes | SHA-256`

# PHASE 5 — FAVICON PACKAGE
Create from canonical symbol:
- favicon.svg
- favicon-16x16.png
- favicon-32x32.png
- favicon-48x48.png
- favicon.ico

The ICO MUST be a real ICO container and include at minimum 16, 32, and 48 px representations.

Verify:
- magic/container type
- embedded sizes
- successful decode
- visual identity at each size

Do not simply rename PNG to `.ico`.

# PHASE 6 — LEGACY OPENEMR COMPATIBILITY EXPORTS
Using only canonical Thiqa assets, create technical derivatives required by the Brand Design Requirements / Group 1 inventory, including where applicable:
- login-logo.png
- logo-full-con.png
- menu-logo.png
- favicon-32x32.png
- login_logo.gif
- logo_1.png
- logo_2.png
- practice-logo-compatible raster
- legacy-logo-86x43-a.png
- legacy-logo-86x43-b.png

For GIF:
- create from approved raster/vector source
- verify it is a valid GIF container
- do not redesign for GIF
- document color/alpha limitations if any

Do not modify OpenEMR runtime files yet; place derivatives only in the brand production package.

# PHASE 7 — COMPLETE TOKEN VALIDATION
Locate the approved Thiqa token JSON in project inputs/workspace.

If no token JSON is available, STOP this phase and report `MISSING AUTHORITATIVE TOKEN INPUT`; do not invent a replacement.

If present:
1. Validate JSON syntax.
2. Validate required Light and Dark semantic roles.
3. Detect duplicates, missing keys, invalid hex colors, unresolved placeholders, and Light/Dark accidental aliasing.
4. Preserve locked existing values.
5. Do not silently change brand colors.
6. If prior approved documentation explicitly defines a derived completion token, reconcile it.
7. Any new token not already approved must be flagged for governance rather than invented.

Required semantic coverage should include at minimum:
- brand.primary
- brand.primaryHover
- brand.primaryActive
- brand.secondary
- brand.accent
- surface.body
- surface.primary
- surface.secondary
- surface.card
- surface.input
- text.primary
- text.secondary
- text.muted
- text.disabled
- text.inverse
- border.default
- border.strong
- interactive.link
- interactive.linkHover
- interactive.focus
- state.success
- state.warning
- state.danger
- state.info
and necessary semantic foreground/background/border variants.

Create:
`docs/branding-production/07-token-validation.md`

# PHASE 8 — WCAG 2.2 NUMERIC CONTRAST VALIDATION
Use the actual approved token values, not screenshots.

Implement the W3C relative-luminance/contrast-ratio formula and calculate numeric ratios.

Test at minimum:
- primary text/body
- primary text/surface
- secondary text/body
- required muted text/background
- link/body
- link/surface
- primary CTA text/background
- secondary CTA text/background
- success text/background
- warning text/background
- danger text/background
- info text/background
- focus indicator/adjacent background
- all corresponding Dark-theme pairs
- meaningful UI component boundaries where required

Acceptance:
- normal text >= 4.5:1
- large text >= 3:1
- non-text/UI component contrast >= 3:1 where WCAG 2.2 SC 1.4.11 applies

Important:
- logo/brand-name text is exempt from SC 1.4.3 minimum text contrast, but still document its usage.
- Do not alter locked tokens automatically merely to force PASS.
- If a required pair fails, report exact ratio, token pair, criterion, and proposed nearest correction separately. Do not silently modify the locked design.

Create machine-readable:
`brand/qa/wcag-contrast-results.json`
and human-readable:
`docs/branding-production/08-wcag-contrast.md`

# PHASE 9 — RTL / BILINGUAL EVIDENCE RECONCILIATION
Locate approved reference evidence for:
- Arabic Login
- Arabic Navbar
- Arabic Clinical Form
- Arabic Data Table
- Arabic Patient Portal

Do not generate new creative mockups in this phase.

For each:
- identify source file
- verify Arabic text renders
- verify logo is not mirrored
- verify directional behavior
- verify numeric/bidirectional examples where present

If any required evidence is absent, mark it as a remaining design-evidence gap. Do not fabricate PASS.

# PHASE 10 — EMAIL / SMART / PRINT / TENANT EVIDENCE
Reconcile the existing approved design package against:
- Email branding specification
- SMART Light mapping
- SMART Dark mapping
- Print full-color
- Print monochrome
- Tenant/facility separation

Require actual evidence/documentation. If absent, report the exact missing artifact/specification rather than inferring completion.

# PHASE 11 — COMPLETE PRODUCTION ASSET MANIFEST
Generate a manifest from the actual physical output files.

For EVERY asset include:
- Asset ID
- Canonical filename
- Relative path
- Purpose
- Format
- Width
- Height
- Aspect ratio
- Background expectation
- Light/Dark variant
- RTL/LTR relevance
- Master source
- Byte size
- SHA-256
- Validation status
- Notes

Never type byte sizes or hashes manually. Compute them from actual files.

Produce:
- `brand/manifests/asset-manifest.json`
- `brand/manifests/asset-manifest.csv`
- `docs/branding-production/11-asset-manifest.md`

# PHASE 12 — SHA-256 RELEASE MANIFEST
Create:
`brand/manifests/SHA256SUMS`

Include every final production asset and relevant JSON/documentation file intended for handoff.

Verify the manifest independently using two methods where available, e.g.:
1. `sha256sum -c`
2. Python `hashlib`

Record both results.

# PHASE 13 — FINAL QA RECONCILIATION
Create a single authoritative QA matrix:
`docs/branding-production/13-final-qa-matrix.md`

Rows must include at least:

- Exact production filenames
- Evidence-based SVG role mapping
- Canonical SVG masters
- SVG no-raster validation
- SVG render validation
- Exact 1053×390 PNG
- Exact 300×100 PNG
- Exact 101×100 PNGs
- Exact 870×222 PNG
- 16×16 favicon PNG
- 32×32 favicon PNG
- 48×48 favicon PNG
- valid multi-resolution favicon.ico
- legacy OpenEMR exports
- valid GIF where required
- byte-size manifest
- SHA-256 manifest
- token JSON syntax
- token semantic completeness
- Light token validation
- Dark token validation
- WCAG numeric Light
- WCAG numeric Dark
- RTL evidence
- Arabic form evidence
- Arabic table evidence
- Arabic portal evidence
- Email evidence
- SMART Light evidence
- SMART Dark evidence
- Print evidence
- tenant/facility evidence
- no OpenEMR visual bleed
- source tree purity
- manifest reconciliation
- final handoff completeness

Allowed status values:
- `PASS`
- `FAIL`
- `BLOCKED — MISSING AUTHORITATIVE INPUT`

Do not use vague statuses.

# PHASE 14 — FINAL PURITY / INTEGRITY CHECK
At end:
1. Compare git status with baseline.
2. Prove application source/assets/runtime configuration were not modified.
3. List every newly created handoff file.
4. Recompute hashes.
5. Verify no output is zero-byte/corrupt.
6. Verify every manifest path exists.
7. Verify every manifest SHA-256 matches.
8. Verify exact image dimensions again from files, not intended export parameters.

# FINAL CERTIFICATION REPORT
Create:
`docs/branding-production/FINAL-GROUP-1.5B-CERTIFICATION.md`

Report:
- authoritative output root
- branch
- HEAD
- baseline state
- canonical SVG count
- SVG role mapping result
- SVG validation result
- exact PNG count
- favicon result
- ICO result
- legacy export result
- token validation result
- WCAG result
- RTL evidence result
- Email/SMART/Print/Tenant evidence result
- manifest file count
- SHA-256 verification result
- source purity result
- failed gates
- blocked gates
- remaining knowledge gaps

## STRICT FINAL STATUS LOGIC
Output exactly one:

`BRAND DESIGN PACKAGE — ACCEPTED FOR GROUP 2`

ONLY IF:
- every mandatory row in the final QA matrix is PASS;
- no required authoritative input is missing;
- no required production file is absent;
- token validation passes;
- required WCAG checks pass;
- hashes verify;
- application source purity passes.

Otherwise output:

`BRAND DESIGN PACKAGE — NOT READY FOR GROUP 2`

and enumerate every failed or blocked gate.

Do not lower the standard to reach the desired status.
Do not claim PASS from prior prose claims when the actual file/evidence is absent.
