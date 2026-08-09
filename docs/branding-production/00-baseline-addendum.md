# 00 — Baseline Addendum (CORRECTED INPUT INVENTORY)

**Purpose.** The original [00-baseline.md](00-baseline.md) authored by the prior run recorded only the 12 files declared in `docs/Thiqa_Group_1_5B_Handoff/README.md`. The handoff folder actually contains additional evidence files at its root that were NOT declared in the README but ARE explicitly named in the prompt's §INPUTS clause ("Any other approved Thiqa design references, token JSON, Brand Design Requirements Document, prior asset manifest, mockups, and Group 1 branding report available in the project/workspace MUST be treated as evidence sources when present.").

This addendum inventories the full input surface.

## Extended input inventory (files previously not recorded)

| Relative path | Bytes | SHA-256 | Purpose |
|---|---:|---|---|
| `BRAND DESIGN REQUIREMENTS DOCUMENT.md` | 25079 | — | 63-section brand design contract (product name, palette rules, RTL, SMART, print, tenant, typography acceptance criteria) |
| `file.json` | 2504 | `f19be7fc6698ae4644818c7acc6ed36d2381c7150a6e8dee6e418ab10e3f57e6` | Authoritative Thiqa Light + Dark token JSON |
| `table.md` | 1292 | — | Design asset inventory (LOGO-01–05, COLOR-01/02, TYPE-01, LOGIN-01/02/03 incl. Arabic, NAV-01/02 incl. Arabic, PORTAL-01, SMART-01/02, PRINT-01/02) |
| `table (1).md` | 1550 | — | Pre-computed WCAG contrast QA matrix with measured ratios |
| `table (2).md` | 404 | — | Duplicate of `inputs/reference_docs/typography-weight-contract.md` |
| `First project.zip` | 9566368 | — | 26 additional Recraft PNGs (navbar English + Arabic, ring symbol variants, login/portal/SMART surfaces `image (1)…(14).png`) |
| `First project (1).zip` | 34235866 | — | 32 Recraft PNGs (3D product renders, palette swatch sheets COLOR-01/02, typography specimen TYPE-01, print statement designs PRINT-01/02) |
| `image (5).zip` | 82639 | — | Redundant copy of the 11 SVG masters already in `inputs/svg_masters_unmapped/` |
| `cd61f749-19eb-4cad-98a4-2f6c5671718b.png` | 2084266 | — | Unrelated personal image; not brand evidence — excluded |

Additionally the operator has since supplied 8 finalised Recraft mockups covering the previously-uncovered channel surfaces at `docs/Thiqa_Group_1_5B_Handoff/inputs/design_evidence/`:

| File | Bytes | Purpose |
|---|---:|---|
| `Arabic Login.png` | 4842427 | RTL Arabic Login evidence |
| `Arabic Clinical Form.png` | 4301342 | Arabic clinical form evidence |
| `Arabic Data Table.png` | 4527494 | Arabic data table evidence |
| `Arabic Portal.png` | 4443006 | Arabic patient portal evidence |
| `Dark Login.png` | 4774783 | Dark theme login parity |
| `Bilingual Email.png` | 4590639 | Email branding evidence (EN+AR) |
| `SMART Light+Dark.png` | 4511191 | SMART consent evidence (both themes) |
| `Print Color+Mono.png` | 4872626 | Print/PDF evidence (color + monochrome) |

## Delta vs original 00-baseline.md

- Original: recorded only 12 inputs (11 SVGs + typography contract).
- Corrected: 20 evidence inputs total (12 originally recorded + 8 mockups + 1 token JSON + 1 requirements doc + 3 table docs + 3 zips; 1 unrelated PNG excluded).

## Impact on Phase gates

The following Codex-declared BLOCKED statuses were incorrect because the required authoritative inputs were in fact present:

| Codex claim | Corrected finding |
|---|---|
| Phase 7 "token JSON was not supplied" | `file.json` supplied at handoff root |
| Phase 8 "cannot compute WCAG without tokens" | Same |
| Phase 9 "no Arabic evidence exists" | Arabic Login/Navbar mockups in `First project (1).zip` + `First project.zip`; plus 4 Arabic surface mockups in `design_evidence/` |
| Phase 10 SMART Light/Dark "no mapping" | `file.json` + SMART Light+Dark mockup in `design_evidence/` |
| Phase 10 Print "no proof" | 2 print statement mockups in `First project.zip` + `Print Color+Mono.png` in `design_evidence/` |
| Phase 10 Email "no spec" | `BRAND DESIGN REQUIREMENTS DOCUMENT.md` §31 + `Bilingual Email.png` in `design_evidence/` |
| Phase 10 Tenant "no evidence" | Navbar mockups show product+tenant co-branding ("Thiqa | King Faisal Medical Center") |

## Root cause

Codex trusted the README line "SVG files supplied: 11" and did not scan the handoff root. The prompt's §INPUTS clause explicitly warned to treat any additional design references present as evidence. Future runs should `Get-ChildItem -Recurse` the entire handoff folder in Phase 0.
