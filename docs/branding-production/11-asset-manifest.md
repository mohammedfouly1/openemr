# 11 — Asset Manifest (CORRECTED)

**Status:** **PASS**

Regenerated from actual physical files after Codex's original run, with tokens/fonts/rtl/smart/email/colors/guidelines evidence included.

## Integrity coverage model (added 2026-08-25, finding S3-P2-35)

`brand/manifests/SHA256SUMS` used to hold 123 entries and every one was a **source** artefact under
`brand/` or `docs/branding-production/`. Not one covered anything the product actually serves, so every
deployed logo, favicon and font could be replaced while `verify-brand-manifest.php` printed
`123/123 verified` and exited 0. The gate was not lying about what it checked; it was silent about what
it did not, which is the more dangerous shape and is why this section exists.

Coverage is assigned **by ownership class**, because a single hashing sweep would have been wrong in
three different ways. Only like is ever compared with like:

| Class | What | How it is verified | Count |
|---|---|---|---:|
| 1. Source artefact | `brand/**`, `docs/branding-production/*.md` | Recorded SHA-256 | 123 |
| 2. Deployed immutable | `public/images/logos/**`, legacy `public/images/*`, `sites/default/images/*`, the module's dark marks | Recorded SHA-256 | 21 |
| 3. Mirrored deployment | `public/assets/fonts/thiqa/**` | **Equality with the recorded source**, not a second hash | 11 |
| 4. Generated deterministic | `public/themes/*.css` | Out of scope here — see below | — |
| 5. Tenant materialised | `public/branding/<site>/**` | Excluded by design — see below | — |
| 6. Runtime data | `globals` overlay rows | Excluded by design — see below | — |

**Why class 3 is not simply hashed.** `public/assets/` is gitignored build output copied byte-for-byte
from `brand/typography/fonts/`. A recorded hash there would go stale on every legitimate rebuild and
train maintainers to re-issue entries without reading them — the precise habit that let the gate sit RED
for five days undetected (Revision 5 of [12-release-verification.md](12-release-verification.md)).
Equality-with-source stays true across rebuilds and fails the moment a deployed font stops matching what
it was built from. The check runs in **both** directions: a source file with no deployed counterpart is
reported as unshipped, not ignored. One deliberate exception is declared in the verifier,
`brand/typography/fonts/pdf/README-amiri.md`, which is maintainer provenance and is not shipped;
`OFL.txt` **is** shipped, because SIL OFL 1.1 requires the licence to travel with the fonts.

**Why classes 4-6 are excluded, and why that is not a gap.** Class 4 is webpack output whose correct
check is a build-output contract, not a static hash; that is finding S3-P2-36, enforced by
`BrandingGovernanceGuardTest` against locked decision Q77. Classes 5 and 6 are per-tenant and revisioned
by construction — asserting a shared fixed hash over them would encode an invariant that is false by
design. Each exclusion is a decision recorded here, not an oversight.

### What this changes about the re-issue discipline

The RB-25 rule is unchanged in spirit and wider in scope: **re-issue, never delete.** What is new is
that a single edit can now oblige **more than one** entry.

- Editing a **source** artefact re-issues its own entry **and every class-2 deployed entry fed from it**.
  A brand logo and its deployed copy are two manifest rows describing one decision; updating only the
  source is how a deployed asset silently drifts.
- Editing a class-3 **source** font requires re-running the asset install so the mirrored deployment
  matches, since nothing is recorded for the deployed side to re-issue.
- Editing any `docs/branding-production/*.md` — **including this file and the release-verification
  document itself** — re-issues that document's own entry in the same change.

`php tools/branding/verify-brand-manifest.php` now reports each class and its count separately, so
`123/123` can never again be read as "everything deployed is intact".

## Counts

**FLAGGED FOR HUMAN REVIEW (2026-08-19):** the table below was never updated after the Revision 4 note
further down this document (the `Q25` Amiri PDF font addition). Revision 4 states the asset count grew
103 → 107 with 4 new files under `brand/typography/fonts/pdf/`
(`Amiri-Regular.ttf`, `Amiri-Bold.ttf`, `OFL.txt`, `README-amiri.md`), but the `brand/typography/` row
below still reads 12 and the Total still reads 103, unchanged from before that revision. Independently
counting the files on disk this session (`Get-ChildItem -Recurse -File`, excluding `manifests/`) found
**108** files under `brand/`, not 107 or 103 — one more than Revision 4's own stated figure. This
document does not resolve which of 103/107/108 is the live correct count; a maintainer should
regenerate this table from `brand/manifests/asset-manifest.json` (or re-run the counting script) rather
than trust either number below.

| Subfolder | File count |
|---|---:|
| `brand/master/` | 8 |
| `brand/logos/` | 32 |
| `brand/favicon/` | 5 |
| `brand/tokens/` | 1 |
| `brand/typography/` | 12 (4 Inter woff2 + 4 IBM Plex Sans Arabic woff2 + specimen PNG + fonts.scss + tokens.json + weight-contract.md) — **stale, see flag above; 16 files found on disk this session (includes the 4-file `fonts/pdf/` Amiri addition from Revision 4)** |
| `brand/colors/` | 2 |
| `brand/smart/` | 1 |
| `brand/email/` | 1 |
| `brand/rtl/` | 5 |
| `brand/guidelines/` | 2 |
| `brand/previews/` | 24 |
| `brand/qa/` | 10 |
| **Total assets** | **103 — stale, see flag above** |

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
