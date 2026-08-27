# 08 — WCAG 2.2 Numeric Contrast Validation (CORRECTED — REVISION 4)

**Status:** **PASS — 0 FAIL pairs, 0 gates blocked.**

**Revision 4 (2026-08-27):** regenerated from `brand/tokens/thiqa-tokens.json` as it exists today,
using the repository's own `ContrastCalculator` class rather than hand-typed figures. This is a
palette-tracking refresh, not a new correction: the token source itself moved on since revision 3
(part of the SkyEagle rebrand's token pass) — light-theme brand navy went from `#0B1B4D` to
`#0B376E`, the light-theme primary interactive/CTA colour from `#C43F2E` to `#1E5A96`, and light-theme
link colours from `#2C5F94`/`#1E4574` to `#0B4E91`/`#0A447E` — but this evidence file and document had
not been regenerated to match, so it was still reporting ratios for colours the live product no longer
uses. Two other values converge to the same hex under the current source and are not new: light
`background` and `surface` are both `#FFFFFF` in the current token file, so every "on background" /
"on surface" pair in the Light theme table below is now the same figure by construction, not a
generation error. The pair/PASS/ADVISORY/FAIL shape is unchanged at **38 evaluated pairs: 35 PASS, 3
ADVISORY, 0 FAIL** — the counts happen to match revision 3 exactly; only the underlying colours and
ratios moved.

**Revision 3 (2026-08-24):** current machine evidence was re-derived rather than inherited. The JSON
contains **38 evaluated pairs: 35 PASS, 3 ADVISORY, 0 FAIL**. Four passing `borderStrong` UI pairs had
been added to the machine evidence during conflict resolution, but this document still reported revision
2's 34 / 31 / 3 counts and omitted those rows. The four rows and current counts are now synchronized.

**Revision 2 (2026-08-09):** the two Light-theme link FAIL pairs recorded in revision 1 are **resolved**.
The token owner adopted the proposed correction (decision **D-1**, recorded in
[15-decision-record.md](15-decision-record.md)): `light.link.default` is now `#2C5F94` (was `#3E7FBD`) and
`light.link.hover` is now `#1E4574` (was `#2C5F94`). `brand/tokens/thiqa-tokens.json` has been updated
accordingly and re-hashed. Revision 1's FAIL rows are retained below under *Superseded rows* as evidence.

Supersedes the earlier Codex report `BLOCKED — MISSING AUTHORITATIVE INPUT`. Ratios below are computed by implementing the W3C relative-luminance formula against [brand/tokens/thiqa-tokens.json](../../brand/tokens/thiqa-tokens.json) — no screenshot colours were sampled.

## Method

Standard: WCAG 2.2, SC 1.4.3 (normal text ≥ 4.5:1), SC 1.4.11 (UI ≥ 3:1), SC 1.4.6 (advisory large-text).
Formula: `L1 = 0.2126·R' + 0.7152·G' + 0.0722·B'` where each channel is linearised via `c ≤ 0.03928 → c/12.92; else ((c+0.055)/1.055)^2.4`, then `(max+0.05)/(min+0.05)`.
Implementation (revision 1): `tmp/task-wcag.ps1` — a scratch PowerShell reference implementation.
Implementation (revision 2): recomputed independently **twice** — once in PHP 8.3 and once in PowerShell —
with both implementations agreeing to 2 dp on every pair, and reproducing every revision-1 figure exactly.
Group 2 replaces the scratch script with a tested in-repository `ContrastCalculator` (plan WP-2.3).
Full machine-readable output: [brand/qa/wcag-contrast-results.json](../../brand/qa/wcag-contrast-results.json) — **38** evaluated pairs.

## Result summary (revision 3)

| Verdict | Count |
|---|---:|
| PASS | 35 |
| FAIL | **0** |
| ADVISORY | 3 — 1 × `text.disabled` (WCAG explicitly exempts inactive UI components from 1.4.3) + 2 × `border.default` vs `background` at 1.24 / 1.44 (a subtle divider by design; borders here convey no information, so SC 1.4.11 does not apply) |

> **CORRECTION K-21.** In revision 1 the two `border.default` rows carried `"status": "FAIL"` in
> `brand/qa/wcag-contrast-results.json` while their own `"criterion"` field read
> `SC 1.4.11 UI 3:1 (advisory)` and the prose called them a *structural non-issue* — the machine output
> contradicted itself and the document. Their status is now `ADVISORY`, matching the vocabulary already
> used for `text.disabled`. **No ratio was changed:** the borders still measure 1.24 and 1.44, and that is
> the intended visual design. At revision 2, machine output and prose agreed on 31 PASS, 3 ADVISORY,
> 0 FAIL, 34 pairs.

> **CORRECTION S1-P1-05 (revision 3).** Machine evidence later gained four passing `borderStrong` UI
> pairs without the prose being carried forward. Current machine output and prose now agree on
> **35 PASS, 3 ADVISORY, 0 FAIL, 38 pairs**.

Pair count rose from 33 to 34 in revision 2 because `link hover on surface` was not evaluated in revision
1. Revision 3 records the four `borderStrong` pairs already present in the current machine evidence.

## Light theme

| Pair | fg | bg | Ratio | Required | Status |
|---|---|---|---:|---:|---|
| body text on background | `#0B376E` | `#FFFFFF` | 11.76 | 4.5 | PASS |
| body text on surface | `#0B376E` | `#FFFFFF` | 11.76 | 4.5 | PASS |
| secondary text on background | `#4B5266` | `#FFFFFF` | 7.78 | 4.5 | PASS |
| secondary text on surface | `#4B5266` | `#FFFFFF` | 7.78 | 4.5 | PASS |
| disabled text on background | `#9CA0AC` | `#FFFFFF` | 2.61 | 3.0 (exempt) | ADVISORY — WCAG exempts disabled UI text |
| link default on background | `#0B4E91` | `#FFFFFF` | 8.36 | 4.5 | PASS |
| link default on surface | `#0B4E91` | `#FFFFFF` | 8.36 | 4.5 | PASS |
| link hover on background | `#0A447E` | `#FFFFFF` | 9.81 | 4.5 | PASS |
| link hover on surface | `#0A447E` | `#FFFFFF` | 9.81 | 4.5 | PASS |
| primary CTA text on primary bg (interactive.primary) | `#FFFFFF` | `#1E5A96` | 7.10 | 4.5 | PASS |
| secondary CTA text on secondary bg | `#FFFFFF` | `#0B376E` | 11.76 | 4.5 | PASS |
| focus ring on background (UI) | `#1E5A96` | `#FFFFFF` | 7.10 | 3.0 | PASS |
| success text on success bg | `#2F6B45` | `#E9F5EE` | 5.67 | 4.5 | PASS |
| warning text on warning bg | `#8A5314` | `#FCEFE0` | 5.58 | 4.5 | PASS |
| critical text on critical bg | `#8E271D` | `#FBE9E7` | 7.29 | 4.5 | PASS |
| info text on info bg | `#264C74` | `#E8F0FA` | 7.72 | 4.5 | PASS |
| border.default on background (UI) | `#E4E2DC` | `#FFFFFF` | 1.30 | 3.0 (advisory) | ADVISORY — subtle divider by design |
| borderStrong on background (UI) | `#4B5266` | `#FFFFFF` | 7.78 | 3.0 | PASS |
| borderStrong on surface (UI) | `#4B5266` | `#FFFFFF` | 7.78 | 3.0 | PASS |

Light-theme `background` and `surface` are both `#FFFFFF` in the current token source, which is why
every "on background" / "on surface" row above shares the same figure — not a duplication error.

## Dark theme

| Pair | fg | bg | Ratio | Required | Status |
|---|---|---|---:|---:|---|
| body text on background | `#F5F6F8` | `#0B1220` | 17.31 | 4.5 | PASS |
| body text on surface | `#F5F6F8` | `#121A2E` | 16.01 | 4.5 | PASS |
| body text on raised surface | `#F5F6F8` | `#17213A` | 14.77 | 4.5 | PASS |
| secondary text on background | `#AEB5C4` | `#0B1220` | 9.10 | 4.5 | PASS |
| secondary text on surface | `#AEB5C4` | `#121A2E` | 8.41 | 4.5 | PASS |
| disabled text on background | `#6B7280` | `#0B1220` | 3.87 | 3.0 | PASS |
| link default on background | `#799EC3` | `#0B1220` | 6.68 | 4.5 | PASS |
| link default on surface | `#799EC3` | `#121A2E` | 6.18 | 4.5 | PASS |
| link hover on background | `#6989AA` | `#0B1220` | 5.13 | 4.5 | PASS |
| primary CTA text on primary bg (interactive.primary) | `#0B1220` | `#83A4C5` | 7.21 | 4.5 | PASS |
| secondary CTA text on secondary bg | `#0B1220` | `#F5F6F8` | 17.31 | 4.5 | PASS |
| focus ring on background (UI) | `#83A4C5` | `#0B1220` | 7.21 | 3.0 | PASS |
| success text on success bg | `#8FD1A6` | `#173425` | 7.62 | 4.5 | PASS |
| warning text on warning bg | `#F0B45C` | `#3A2A12` | 7.49 | 4.5 | PASS |
| critical text on critical bg | `#F29088` | `#3A1815` | 6.90 | 4.5 | PASS |
| info text on info bg | `#8FC1EE` | `#132437` | 8.26 | 4.5 | PASS |
| border.default on background (UI) | `#26314A` | `#0B1220` | 1.44 | 3.0 (advisory) | ADVISORY — subtle divider by design |
| borderStrong on background (UI) | `#AEB5C4` | `#0B1220` | 9.10 | 3.0 | PASS |
| borderStrong on surface (UI) | `#AEB5C4` | `#121A2E` | 8.41 | 3.0 | PASS |

## Resolution of the 2 revision-1 FAIL pairs (Light theme link colour) — APPLIED

**Superseded rows (revision 1, retained as evidence):**

| Pair | fg | bg | Ratio | Required | Status |
|---|---|---|---:|---:|---|
| link default on background | `#3E7FBD` | `#FAFAF8` | 4.04 | 4.5 | FAIL |
| link default on surface | `#3E7FBD` | `#FFFFFF` | 4.22 | 4.5 | FAIL |

**Decision D-1 — ADOPTED by the token owner, 2026-08-09.** The recommended correction was accepted and
applied to `brand/tokens/thiqa-tokens.json`:

| Token | Before | After | Ratio on `#FAFAF8` | Ratio on `#FFFFFF` |
|---|---|---|---:|---:|
| `light.link.default` | `#3E7FBD` | **`#2C5F94`** | 6.34 ✅ | 6.62 ✅ |
| `light.link.hover` | `#2C5F94` | **`#1E4574`** | 9.31 ✅ | 9.73 ✅ |

The sky-family hue is preserved; both pairs now clear SC 1.4.3 with margin.

> **CORRECTION K-20.** Revision 1 of this document quoted `#1E4574` at **9.66 / 10.09**. Two independent
> recomputations (PHP 8.3 and PowerShell), each of which reproduces every other figure in this document
> exactly, give **9.31 / 9.73**. Revision 1's figures for that row were wrong. The conclusion is unaffected
> — both values pass SC 1.4.3 comfortably — but the recorded numbers are now the computed ones.

**Not changed, and why:** `light.brand.sky` and `light.interactive.focusRing` remain `#3E7FBD`. The focus
ring is a non-text UI component governed by SC 1.4.11 (≥ 3:1); at 4.04:1 it passes. The SMART
`color_highlight` token likewise remains `#3E7FBD` — it is an accent, not body text. Only the two `link`
roles were in scope for D-1.

## Notes

- Logo/wordmark colours are exempt from SC 1.4.3 per WCAG (essential logos).
- **Revision 4 update:** the coral family (`#FF6F5E`, `#C43F2E`) has been fully retired from the
  token source — `interactive.primary.default` is now `#1E5A96` (light) / `#83A4C5` (dark), and no
  `brand.coral*` value in the current `thiqa-tokens.json` matches the coral hexes referenced in the
  historical cross-check below. That cross-check is kept as a dated record of a prior verification
  pass, not a claim about current runtime colours.
- **Historical (revision ≤3), retained as evidence — do not treat as current:**
  `docs/Thiqa_Group_1_5B_Handoff/table (1).md` pre-computed, at the time:
  - "white text on bright coral ≈ 2.7:1" → matched `#FFFFFF` on `#FF6F5E` = 2.63:1 (that pair was
    never used at runtime; `interactive.primary.default` was then `coralDeep` `#C43F2E`, passing at
    5.12:1).
  - "coral brand color as text/icon on dark background ~6.9:1" → matched `dark.brand.coral`
    (`#FF6F5E`) on `dark.background` (`#0B1220`) = 6.85:1.
  Neither `#FF6F5E` nor `#C43F2E` exists in the token source as of revision 4; the current runtime
  primary/CTA pair is `#FFFFFF` on `#1E5A96` (light, 7.10:1) / `#0B1220` on `#83A4C5` (dark, 7.21:1),
  both recorded in the tables above.
- Numeric ratios above are reproducible from [brand/tokens/thiqa-tokens.json](../../brand/tokens/thiqa-tokens.json) using the W3C formula stated under *Method*; revision 2 was verified by two independent implementations, revision 3 by the repository `ContrastCalculatorTest`, and revision 4 was generated directly by that same `ContrastCalculator` class (not hand-typed) against the current token file.
