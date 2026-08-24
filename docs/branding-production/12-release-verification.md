# 12 — Release Verification (CORRECTED — REVISION 6)

**Status:** **PASS** (current — see Revision 6)

**Revision 6 (2026-08-24) — current. Manifest re-issued for the S1-P1-05 evidence correction.**

Current `brand/qa/wcag-contrast-results.json` was independently re-derived as **38 pairs / 35 PASS /
3 ADVISORY / 0 FAIL**. `08-wcag-contrast.md` still reported revision 2's 34 / 31 / 3 counts and omitted
the four passing `borderStrong` UI rows already present in the machine evidence. The document was corrected
without changing the token source or machine evidence. Per RB-25, the entries for
`08-wcag-contrast.md` and this self-referential release-verification document were re-issued, never removed;
the verifier must pass 123/123 after both final document bytes are fixed.

**Revision 5 (2026-08-24). Manifest re-issued for four documents; the gate had been RED for five days, undetected.**

`php tools/branding/verify-brand-manifest.php` reported `119/123 verified, 4 problem(s)`, exit code **1**,
with hash mismatches on:

| Document | Recorded (stale) | Actual |
|---|---|---|
| `11-asset-manifest.md` | `96d2e073…` | `8430d6ab…` |
| `13-final-qa-matrix.md` | `2c8f0eb6…` | `71d38514…` |
| `16-conflict-resolutions.md` | `3c051c90…` | `53d135e2…` |
| `FINAL-GROUP-1.5B-CERTIFICATION.md` | `8df6fecf…` | `3f50e739…` |

**Why they drifted.** All four were edited on 2026-08-19 by the documentation-correction pass that added the
`FLAGGED FOR HUMAN REVIEW` notes and the D-15 correction. Their content changes were legitimate and are
committed. What did not happen was the hash re-issue. The manifest was last re-issued `b3b821ffa`
(2026-08-10), so from 2026-08-19 the release gate was failing and nobody ran the verifier to notice.

**This is exactly the RB-25 failure mode**, whose standing obligation reads: *"run
`php tools/branding/verify-brand-manifest.php` before **and** after editing anything under
`docs/branding-production/`."* The obligation existed, was written down, and was not followed. Recording that
plainly rather than silently re-issuing, because the process gap is the more useful finding.

**Action taken:** the four entries were **re-issued, not deleted** (per RB-25's rule), against the committed
content. This document was updated first and its own entry re-issued in the same pass — note that
`12-release-verification.md` is itself one of the 16 manifest-covered documents, so *documenting a re-issue
necessarily invalidates the manifest again*. The correct order is always: edit the document, then recompute,
then verify.

**Structural note for whoever wires the CI gate (S1-P1-03).** `verify-brand-manifest.php` is invoked by
**zero** of the repository's 64 GitHub workflows. It is a correct tool that nothing runs automatically —
which is why a red gate survived five days. It also only detects manifest→disk drift, never disk→manifest:
a new unmanifested file under `brand/` passes silently.

**Revision 4 (2026-08-09).** Re-verified after the `Q25` Arabic PDF fonts were installed and registered (`brand/typography/fonts/pdf/`, asset IDs THIQA-100–103): **107 assets + 16 docs = 123 SHA256SUMS entries**, verified 123/123 by two independent hashers. Both Amiri TTFs carry a valid `0x00010000` TrueType signature and ship with their OFL-1.1 licence text.

**Revision 3 (2026-08-09).** Re-issued after the nine-item conflict resolution
([16-conflict-resolutions.md](16-conflict-resolutions.md)): **103 assets + 16 docs = 119 SHA256SUMS
entries**. Changed since revision 2: `thiqa-tokens.json` (three ratified tokens),
`wcag-contrast-results.json` (34 → 38 pairs), and the new resolution document.

**Revision history.** Revision 1: 14 docs = 117 entries. Revision 2: 15 docs = 118 entries, after decisions
D-1 and D-2 ([15-decision-record.md](15-decision-record.md)). Revision 3: 16 docs = 119 entries. All
superseded by revision 4.

> **Count correction (revision 4).** Revisions 1–3 described the asset side as *"103 assets"*. That figure
> was carried forward and never re-derived: the manifest has in fact held **107** rows since the typography
> set was added. Revision 4 states counts computed from the files — 107 assets (including the 4 new PDF
> font files) + 16 documents = 123 entries.

## Method 1: PowerShell `Get-FileHash -Algorithm SHA256`

```powershell
$sums = Get-Content 'brand/manifests/SHA256SUMS'
$ok = 0; $fail = 0
foreach ($l in $sums) {
  if ($l -match '^([0-9a-f]{64})\s+(.+)$') {
    $expected = $Matches[1]; $path = $Matches[2].Trim()
    if (Test-Path $path) {
      $actual = (Get-FileHash -Algorithm SHA256 $path).Hash.ToLower()
      if ($actual -eq $expected) { $ok++ } else { $fail++ }
    } else { $fail++ }
  }
}
```

**Result (revision 4):** `OK = 123, FAIL = 0`.

## Method 2: Python `hashlib` (independent)

```python
import hashlib, os
root = r'G:\My Drive\OpenEMR'
sums = open(os.path.join(root, 'brand', 'manifests', 'SHA256SUMS'), encoding='utf-8').read().splitlines()
ok, bad = 0, 0
for line in sums:
    if not line.strip(): continue
    h, p = line.split('  ', 1)
    full = os.path.join(root, p.replace('/', os.sep))
    if not os.path.exists(full):
        bad += 1; continue
    d = hashlib.sha256(open(full, 'rb').read()).hexdigest()
    if d == h.lower(): ok += 1
    else: bad += 1
```

**Result (revision 4):** `ok = 123, bad = 0`. *(Revisions 2 and 3 used PHP 8.3 `hash_file()` as the second
implementation — the native development host has PHP but no Python; the check is equivalent.)*

## Non-manifest integrity checks

| Check | Method | Result |
|---|---|---|
| Every `SHA256SUMS` path exists | `Test-Path` per line | PASS (123/123) |
| No zero-byte files in manifest | `FileInfo.Length -gt 0` | PASS |
| PNG dimensions match declared width×height in manifest | `System.Drawing.Image.FromFile` on 44 PNGs | PASS |
| SVG viewBox matches declared width×height in manifest | XML parse of `<svg viewBox="…">` on 24 SVGs | PASS |
| ICO is real multi-resolution container | ICONDIR byte parse | PASS — 3 entries: 16×16, 32×32, 48×48 (32-bpp) |
| GIF is valid GIF89a | Signature byte parse | PASS — both `login_logo.gif` and `practice_logo.gif` |
| WOFF2 files load in browsers | `[System.IO.File]::ReadAllBytes` first 4 bytes = `wOF2` | PASS (8/8) |

## Two-method agreement

Revision 1: PowerShell `Get-FileHash` and Python `hashlib` agreed across all 117 entries.
Revision 2: PowerShell and PHP 8.3 `hash_file()` agreed across all 118 entries.
Revision 3: agreement across 119 entries.
**Revision 4: PowerShell `Get-FileHash` and PHP 8.3 `hash_file()` agree across all 123 entries.** No
divergence in any revision. Manifest is authoritative.

## Governance manifest cross-check (revision 4)

The brand package manifest is separate from the governance manifest. Both were verified in the same pass:

| Manifest | Scope | Result |
|---|---|---|
| `brand/manifests/SHA256SUMS` | 107 assets + 16 docs | **123/123 verify**, two independent hashers |
| `Locked Desicions/OpenEMR-SaaS-Decision-Documents-SHA256-UPDATED-2026-08-09.txt` | The 2 locked governance documents | **2/2 MATCH** — unchanged; no decision D-1/D-2/CR-* amended a locked decision, so no re-issue was required |
