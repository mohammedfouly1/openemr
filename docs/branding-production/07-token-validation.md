# 07 — Token Validation (CORRECTED)

**Status:** **PASS**

Supersedes the earlier Codex report `BLOCKED — MISSING AUTHORITATIVE INPUT`. The authoritative Thiqa token JSON was supplied at the handoff root as `docs/Thiqa_Group_1_5B_Handoff/file.json`; Codex read only files declared in the handoff README and missed it. See [00-baseline-addendum.md](00-baseline-addendum.md).

## Source

Authoritative input: [docs/Thiqa_Group_1_5B_Handoff/file.json](../Thiqa_Group_1_5B_Handoff/file.json), SHA-256 `f19be7fc6698ae4644818c7acc6ed36d2381c7150a6e8dee6e418ab10e3f57e6`. Byte-identical copy placed at [brand/tokens/thiqa-tokens.json](../../brand/tokens/thiqa-tokens.json).

## Validation gates

| Gate | Result |
|---|---|
| Valid JSON syntax | PASS — `ConvertFrom-Json` succeeds; no errors |
| Root has both `light` and `dark` themes | PASS — top-level keys: `light`, `dark` |
| Light categories present | PASS — `brand`, `background`, `surface`, `surfaceSunken`, `border`, `divider`, `text`, `semantic`, `interactive`, `link` |
| Dark categories present | PASS — same as Light plus `surfaceRaised` |
| Every hex value is `#RRGGBB` (6 hex digits) | PASS — regex `^#[0-9A-Fa-f]{6}$` matches every colour leaf |
| Semantic roles cover success/warning/critical/info | PASS — each has `bg` + `text` + `border` |
| Interactive roles cover primary/secondary/focusRing | PASS — `primary` has `default`+`hover`+`active`+`disabled`+`textOn`; `secondary` has `default`+`hover`+`textOn` |
| No duplicate keys / no null placeholders | PASS — `ConvertTo-Json → ConvertFrom-Json` round-trip is stable |
| Light and Dark do NOT alias identically | PASS — every category differs (e.g. `light.text.primary=#0B1B4D` vs `dark.text.primary=#F5F6F8`; `light.background=#FAFAF8` vs `dark.background=#0B1220`) |

## Coverage vs prompt Phase 7 required semantic keys

| Required semantic key | Present as |
|---|---|
| `brand.primary` | `light.brand.navy` / `dark.brand.navy` (per palette sheet + tagline sheet) |
| `brand.primaryHover` | `light.interactive.primary.hover` = `#A8351F` (CTA hover) |
| `brand.primaryActive` | `light.interactive.primary.active` = `#8E2B18` |
| `brand.secondary` | `light.brand.coral` = `#FF6F5E` |
| `brand.accent` | `light.brand.coralDeep` = `#C43F2E` (also `interactive.primary.default`) |
| `surface.body` | `light.background` / `dark.background` |
| `surface.primary` | `light.surface` / `dark.surface` |
| `surface.secondary` | `light.surfaceSunken` / `dark.surfaceSunken` |
| `surface.card` | `light.surface` / `dark.surfaceRaised` |
| `surface.input` | ✓ as `surfaceInput` / `surfaceInputOnRaised` (ratified revision 2) |
| `text.primary` | ✓ |
| `text.secondary` | ✓ |
| `text.muted` | maps to `text.secondary` |
| `text.disabled` | ✓ |
| `text.inverse` | ✓ |
| `border.default` | ✓ (as `border`) |
| `border.strong` | ✓ as `borderStrong` (ratified revision 2) |
| `interactive.link` | ✓ as `link.default` |
| `interactive.linkHover` | ✓ as `link.hover` |
| `interactive.focus` | ✓ as `interactive.focusRing` |
| `state.success` | ✓ (bg+text+border) |
| `state.warning` | ✓ |
| `state.danger` | ✓ (named `critical`) |
| `state.info` | ✓ |

## Governance flags — RESOLVED (revision 2, 2026-08-09)

Flags 2 and 3 below were closed by token-owner ratification (open item 9; see
[16-conflict-resolutions.md](16-conflict-resolutions.md)). `brand/tokens/thiqa-tokens.json` now carries
both tokens explicitly, so no implementation has to infer them:

| Token | Light | Dark | Derivation | Contrast |
|---|---|---|---|---|
| `borderStrong` | `#4B5266` | `#AEB5C4` | `text.secondary`, as proposed by flag 2 | 7.45 / 7.78 (light), 9.10 / 8.41 (dark) — all ≥ 3:1 under SC 1.4.11 |
| `surfaceInput` | `#FFFFFF` | `#121A2E` | light `surface`; dark `surface` (body context), per flag 3 | body text 16.43 (light), 16.01 (dark) |
| `surfaceInputOnRaised` | `#FFFFFF` | `#0B1220` | dark `background` for inputs on raised cards, per flag 3 | body text 17.31 (dark) |

The two `surfaceInput*` tokens are named as flat siblings of `surface`/`surfaceSunken`/`surfaceRaised`,
matching the existing schema style. Flags 1 and 4 remain informational and require no action.

## Governance flags as originally recorded (retained as evidence)

1. **Dual-primary design is intentional.** Identity primary is `brand.navy` `#0B1B4D`; interactive CTA primary is `interactive.primary.default = coralDeep` `#C43F2E`. Confirmed by `docs/Thiqa_Group_1_5B_Handoff/table (1).md` note: *"production buttons must use interactive.primary (coralDeep), which passes at ≈5.1:1"*.
2. **`border.strong` is not explicitly named.** The palette ships `border` `#E4E2DC` (Light) / `#26314A` (Dark) and `divider` `#ECEAE5` / `#1E2740`. Group 2 must either materialise `border.strong` from `text.secondary` or flag it for governance addition.
3. **`surface.input` for Dark theme** is not explicitly named. Group 2 should use `dark.surface` for input backgrounds on the base body, and `dark.background` for inputs on raised cards.
4. **Custom schema.** The JSON is authored in a compact custom schema, not DTCG/W3C Design Tokens format. Group 2 chooses the target token pipeline and maps accordingly.
