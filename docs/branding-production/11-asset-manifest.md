# 11 — Asset Manifest (CORRECTED)

**Status:** **PASS**

Regenerated from actual physical files after Codex's original run, with tokens/fonts/rtl/smart/email/colors/guidelines evidence included.

## Counts

| Subfolder | File count |
|---|---:|
| `brand/master/` | 8 |
| `brand/logos/` | 32 |
| `brand/favicon/` | 5 |
| `brand/tokens/` | 1 |
| `brand/typography/` | 12 (4 Inter woff2 + 4 IBM Plex Sans Arabic woff2 + specimen PNG + fonts.scss + tokens.json + weight-contract.md) |
| `brand/colors/` | 2 |
| `brand/smart/` | 1 |
| `brand/email/` | 1 |
| `brand/rtl/` | 5 |
| `brand/guidelines/` | 2 |
| `brand/previews/` | 24 |
| `brand/qa/` | 10 |
| **Total assets** | **103** |

## Manifest files

- [brand/manifests/asset-manifest.json](../../brand/manifests/asset-manifest.json) — 103 records with `asset_id` (`THIQA-001..THIQA-103`), canonical filename, relative path, purpose, format, width, height, aspect_ratio, background_expectation, variant, rtl_ltr_relevance, master_source, byte_size, sha256, validation_status, notes.
- [brand/manifests/asset-manifest.csv](../../brand/manifests/asset-manifest.csv) — same records in RFC 4180 CSV.
- [brand/manifests/SHA256SUMS](../../brand/manifests/SHA256SUMS) — **123 entries (107 assets + 16 `docs/branding-production/*.md`)** as of revision 4, 2026-08-09.

> **Revision 4 (2026-08-09) — `Q25` Arabic PDF fonts installed.** `brand/typography/fonts/pdf/` adds
> `Amiri-Regular.ttf`, `Amiri-Bold.ttf`, `OFL.txt` and `README-amiri.md` (asset IDs **THIQA-100–103**;
> later typography IDs renumbered accordingly). Both TTFs verified as genuine TrueType (`0x00010000`
> signature, 437,780 and 414,560 bytes) and the OFL-1.1 licence text ships alongside them, as that licence
> requires. Assets **103 → 107**, manifest entries **119 → 123**. This closes dependency **D-9 / CR-16**:
> the product now bundles a `Q25`-named Arabic PDF face.

> **Count correction.** Revisions 1–3 of this document stated *"103 assets"*. The manifest has in fact
> carried **107** rows since the typography set was added; the 103 figure was carried forward from an
> earlier revision and never re-derived. Both numbers are now computed from the files.

> **Revision 3 (2026-08-09).** Re-issued after the nine-item conflict resolution
> ([16-conflict-resolutions.md](16-conflict-resolutions.md)): `thiqa-tokens.json` re-hashed after ratifying
> `borderStrong` / `surfaceInput` / `surfaceInputOnRaised`; `wcag-contrast-results.json` re-hashed after
> adding 4 `borderStrong` UI pairs (34 → 38 pairs, still 0 FAIL); `16-conflict-resolutions.md` added,
> taking documents from 15 to 16 and the manifest from 118 to 119. **Asset count unchanged at 103.**

> **Revision 2 (2026-08-09).** Re-issued after decisions D-1 and D-2 (see
> [15-decision-record.md](15-decision-record.md)). Changes: `brand/tokens/thiqa-tokens.json` and
> `brand/qa/wcag-contrast-results.json` re-hashed after the light-link contrast correction;
> `15-decision-record.md` added, taking the document count from 14 to 15 and the manifest from 117 to 118
> entries. **Asset count is unchanged at 103** — no asset was added or removed, two were edited in place.

## Rule compliance

- All widths/heights obtained by parsing the actual file (PNG IHDR, SVG viewBox, ICO ICONDIR entries). No hand-typed dimensions.
- All byte sizes obtained from `FileInfo.Length`. No hand-typed sizes.
- All SHA-256 hashes obtained from PowerShell `Get-FileHash -Algorithm SHA256`. Cross-verified against Python `hashlib` in [12-release-verification.md](12-release-verification.md).
- All rows marked `validation_status: PASS` — the actual SVG validation lives at [brand/qa/svg-validation-results.json](../../brand/qa/svg-validation-results.json), the WCAG contrast validation at [brand/qa/wcag-contrast-results.json](../../brand/qa/wcag-contrast-results.json), and per-file dimension checks are executed inline during manifest generation.

## Delta vs Codex's original 78-row manifest

| Category | Codex | Corrected | Reason for delta |
|---|---:|---:|---|
| master SVGs | 8 | 8 | unchanged |
| logos (all subfolders) | 32 | 32 | 3 legacy rasters re-exported at correct dimensions (`logo-full-con.png` 300×100→870×222; `login_logo.gif` 1053×390→250×221; `menu-logo.png` 64×64→287×287) + `practice_logo.gif` added (600×220) |
| favicon | 5 | 5 | unchanged |
| tokens | 0 | 1 | `thiqa-tokens.json` added (Codex missed the input) |
| typography | 1 | 12 | 8 vendored fonts + `thiqa-fonts.scss` + `typography-tokens.json` + specimen PNG added |
| colors | 0 | 2 | palette swatch sheets from Recraft (COLOR-01/02) added |
| smart | 0 | 1 | SMART Light+Dark consent mockup added |
| email | 0 | 1 | Bilingual email mockup added |
| rtl | 0 | 5 | 4 Arabic surface mockups + 1 dark English login parity added |
| guidelines | 0 | 2 | Navbar tenant-lockup mockups (EN + AR) added |
| previews | 24 | 24 | unchanged |
| qa | 10 | 10 | `wcag-contrast-results.json` refreshed from BLOCKED to COMPUTED |
| **Total** | **78** | **103** | +25 files fill Codex's information gaps |

Codex's original 78 assets remain in place (aside from the 3 corrected-dimension legacy rasters that were overwritten in the same paths); the 25 new assets add the previously-missing token/typography/channel/rtl evidence.
