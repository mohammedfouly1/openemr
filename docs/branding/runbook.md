# Branding operations runbook (F5)

Operational procedures for the Thiqa branding layer (`interface/modules/custom_modules/oe-module-thiqa-branding/`),
per `docs/RebrandingPlan.md` §7.1 row F5. Every command below was read from its actual source in this
session — `interface/modules/custom_modules/oe-module-thiqa-branding/src/Console/*.php`,
`.../src/Materialisation/BrandingMaterialiser.php`, `tools/branding/bin/generate-tokens.php` — not
guessed from the plan's prose. Two commands (`thiqa-branding:verify`, `generate-tokens.php --check`)
were actually **run** against this host's live tree in this session, read-only, and their real output is
reproduced below. Nothing that mutates the database or the deployed asset tree was executed as part of
writing this document.

## 0. Conventions used throughout

**Native-host form** (this machine, per `CLAUDE.local.md` — no Docker, Apache/PHP/MariaDB run as console
processes):

```powershell
C:\openemr-stack\php\php.exe bin\console <command> --site=<site> [options]
```

Run from the repo root (`G:\My Drive\OpenEMR`). `bin\console` itself parses `--site=` out of `argv` and
bootstraps `interface/globals.php` against that site before the branding command ever runs — this is
existing OpenEMR CLI infrastructure the module reuses, not something the module built
(`docs/branding/multi-tenant-white-label-readiness.md` §1.4).

**CI / Docker-stack form**, per the tracked `CLAUDE.md`'s workflow — **documented here, not executed in
this environment.** This host cannot run Docker (`CLAUDE.local.md` §1: no nested virtualization), so every
command in a "CI/Docker" line below is transcribed from the module's own CLI contract and the tracked
`CLAUDE.md`'s documented invocation pattern, not verified live:

```bash
openemr-cmd worktree exec <branch> e 'php bin/console <command> --site=<site> [options]'
# or, against the primary clone's stack (non-worktree mode):
openemr-cmd e 'php bin/console <command> --site=<site> [options]'
```

**`--site` is always mandatory and has no default**, on every branding command. `SiteOption::define()`
(`src/Console/SiteOption.php:50-58`) declares it with no fallback, and `SiteOption::resolve()` refuses a
missing value with `"The --site option is required. Branding commands never assume a tenant."` — this is
deliberate (every *other* OpenEMR CLI command defaults `--site` to `'default'`; branding commands must
not, because a materialisation that silently targets whatever tenant happens to be the fallback is exactly
the accident tenant scoping exists to prevent). On this system only `sites/default` exists
(`docs/branding/multi-tenant-white-label-readiness.md` §1.8), so every example below uses
`--site=default`; substitute the real tenant id once a second site is provisioned.

**Exit codes are the same shape across all three branding console commands** (`MaterialiseCommand.php:48-57`,
`ApplyProfileCommand.php:55-61`, `VerifyCommand.php:40-44`): `0` = success/no-op, a command-specific
non-zero for "refused" vs "failed" so scripts can branch without parsing output. See each procedure below
for the specific codes.

---

## 1. Provision a tenant's branding

Two independent steps: the **product-level identity profile** (name, tagline, logo widths, link URLs —
`~33` `globals` keys applied from a single JSON file, per `docs/branding/changes.md`'s SET-CONFIG section)
and, separately, a **Tier-2 token/asset overlay** (materialised, revisioned). A brand-new tenant normally
needs only the first; the second is opt-in per tenant and starts empty by default
(`docs/RebrandingPlan.md` §2.4 — "Tier 2 ships empty by default").

### 1.1 Apply the product-identity profile

**Native:**
```powershell
C:\openemr-stack\php\php.exe bin\console thiqa-branding:apply-profile --site=default
```
Add `--dry-run` first to preview the before/after table without writing anything — this is the safe way to
see what a fresh tenant would receive. `--profile=<path>` overrides the shipped
`config/branding-profile.json` if a tenant needs a different identity file.

**CI/Docker (documented, not executed here):**
```bash
openemr-cmd worktree exec <branch> e 'php bin/console thiqa-branding:apply-profile --site=default --dry-run'
```

The command prints a `Site / Profile / Source / Globals declared / Rows differing / Mode` summary and a
per-global `Global | Provenance | Before | After | Status` table (`ApplyProfileCommand.php:174-183`), then
writes only the rows that actually differ, inside one `QueryUtils::inTransaction()` call
(`ApplyProfileCommand.php:266-277`). It is safe to rerun: a second run finds zero differing rows and opens
no transaction at all.

**Exit codes:** `0` applied / already up to date / dry run completed · `1` the database refused the write,
nothing committed · `2` the invocation or the profile file was refused, nothing attempted.

**What to check afterward:**
- The command's own table already shows every row's before/after — no separate query needed for the
  common case.
- Independently: `mariadb -u root --host=127.0.0.1 --port=3306 openemr -e "SELECT gl_name, gl_value FROM globals WHERE gl_name='openemr_name'"` should read the new product name.
- A live, unauthenticated check: `GET /interface/login/login.php?site=default` should render the new
  title (this is exactly how `docs/branding/changes.md` row BRAND-001/002 was verified).

### 1.2 (Optional) Give the tenant a Tier-2 token/asset overlay

Only needed if the tenant gets a distinct accent palette or logo beyond the product default. This is the
`thiqa-branding:materialise` command — see §2 below, which is the same procedure as "change a token,"
just for a tenant's first revision (target revision 1) rather than a later one.

---

## 2. Change a token

Token changes go through `thiqa-branding:materialise --payload=<file>`. There is **no way to change a
single token in isolation via a CLI flag** — the payload is a JSON file naming every override you want in
effect for that revision (`JobPayload.php:23-33`'s documented format):

```json
{
  "light":   { "interactive.primary.default": "#1E4574" },
  "dark":    { "interactive.primary.default": "#5FA8E0" },
  "strings": { "openemr_name": "Thiqa" },
  "assets":  [ { "slot": "core/login/primary", "path": "/var/staging/logo.png" } ]
}
```

All four top-level keys are optional; omit `light`/`dark`/`strings`/`assets` entirely to touch nothing but
still bump the revision. **Only 11 token keys are tenant-overridable at all** — the closed allowlist in
`TokenKey::isTenantOverridable()` (`src/Token/TokenKey.php:161-209`): the `interactive.primary.*` (5),
`interactive.secondary.*` (3), `interactive.focusRing`, and `link.default`/`link.hover` keys. Every other
key (brand identity colours, structural surfaces, borders, text, and all four semantic/clinical-safety
colour groups — success/warning/critical/info) is closed to tenant overlay by design; submitting one is
rejected by `TokenValidator`, re-run tenant-side inside the materialiser itself regardless of what the
Control Plane already validated (plan §3.9, "the tenant does not trust the Control Plane blindly").

Ten of the 11 keys use the applicable WCAG contrast gate. `interactive.primary.disabled` remains
overridable, but inactive controls are exempt from SC 1.4.3/1.4.11, so it uses a separate product rule:
the disabled fill must be at least 1.5:1 apart from both `interactive.primary.default` and `background`.
Changing either primary fill re-runs that rule, and the stylesheet retains Bootstrap's fixed disabled
opacity. A rejection with `insufficient_state_separation` is therefore a product distinguishability
failure, not a WCAG failure.

**Native:**
```powershell
C:\openemr-stack\php\php.exe bin\console thiqa-branding:materialise --site=default --payload=C:\path\to\overlay.json
```
Omit `--revision` to target "current + 1" automatically (`MaterialiseCommand.php:196-217`), or pass an
explicit `--revision=<n>` (must be a whole number ≥ 1; revision 0 means "never materialised" and cannot be
a target).

**CI/Docker (documented, not executed here):**
```bash
openemr-cmd worktree exec <branch> e 'php bin/console thiqa-branding:materialise --site=default --payload=/path/to/overlay.json'
```

**What actually happens, in order** (`BrandingMaterialiser::apply()`/`stageVerifyAndApply()`,
`BrandingMaterialiser.php:163-317`): re-validate both overlays against the shipped Tier-1 palette →
stage token stylesheets and any asset binaries under temporary names → re-verify staged bytes and
checksums → rename staged files into place → write the `globals` delta, the materialisation timestamp,
and `saas_branding_revision` in **one** database transaction with the revision written last. A browser
therefore only ever observes revision *n-1* consistently or revision *n* consistently, never a mixture.

**Exit codes** (`MaterialiseCommand.php:48-57`): `0` applied, or unchanged because the revision was
already live · `1` a transient fault — revision *n-1* is fully active, safe to retry the same invocation ·
`2` the invocation or the payload was refused — fix it, retrying is pointless.

**What to check afterward:**
```powershell
C:\openemr-stack\php\php.exe bin\console thiqa-branding:verify --site=default
```
Confirm `Revision` incremented to the target, `Materialised` reads `yes`, `Materialised at` is recent, and
both `Light token stylesheet` / `Dark token stylesheet` read `present`. See §6 for the full shape of this
output (captured live in this session).

---

## 3. Roll back a revision

**This is not a separate command, and the plan's prose ("roll back a revision") is more aspirational than
the code.** Reading `BrandingMaterialiser::materialise()` directly (`BrandingMaterialiser.php:127-161`):

```php
if ($job->targetRevision->value <= $current->value) {
    return MaterialisationResult::unchanged($current);
}
```

The idempotence guard refuses to move *backward or sideways* — a target revision that is not strictly
greater than the live one is treated as a no-op, not as "revert to that revision." There is no
`thiqa-branding:rollback` command, no `--revision` value that means "go back," and no code path in this
module that decrements `saas_branding_revision`. Two genuinely different situations get called
"rollback" in casual conversation, and they are handled two different ways:

**3.1 A materialisation run failed partway through (the automatic case).** This needs no operator action
at all beyond re-running the same invocation. `stageVerifyAndApply()`'s `catch` block
(`BrandingMaterialiser.php:284-301`) calls `unwind()`, which reverses any committed file renames and
discards any staged files (`unwind()`, lines 325-339) — and because `writeAll()` owns its own single
database transaction and rolls itself back on failure, there is nothing left for the materialiser to undo
on the DB side. The tenant is left on revision *n-1*, fully active, exactly as before the attempt. This is
covered in full in §7 ("recover a failed materialisation") — it is the same mechanism, not a different one.

**3.2 A revision materialised successfully, but an operator wants an *earlier* state back (the
"true rollback" case).** The only way to do this is a **forward roll carrying the old values**: re-run
`thiqa-branding:materialise` with a *new*, higher target revision whose payload reproduces the earlier
state (the same overlay JSON that produced revision *n-1*, if it was kept; or one hand-authored to match
it). This is a deliberate consequence of principle P6 ("fail to last-known-good, never to upstream
identity") and matches how the plan itself describes recovery elsewhere (V-03: "CP unavailable → renders
last-good"): the system always *moves forward* to a known-good state, it never decrements a revision
counter. Practically:

```powershell
C:\openemr-stack\php\php.exe bin\console thiqa-branding:materialise --site=default --payload=C:\path\to\previous-overlay.json
```
(no `--revision` needed — it defaults to current+1, which is exactly "the next revision, containing the
old values").

**Operational implication:** keep every payload file used for a materialisation run (they are the only
record of what revision *n* actually contained beyond what's visible in `globals`/`verify` output — the
module itself does not store payload history beyond the resolved token JSON in `globals`). Treat
`saas_branding_tokens_light`/`saas_branding_tokens_dark` (readable via `thiqa-branding:verify`'s underlying
globals, or a direct `SELECT` — see §6) as the closest thing to an audit trail available today; full
materialisation history is `MaterialisationLogger`'s job (application log), not a queryable revision store.

**What to check afterward:** identical to §2 — `thiqa-branding:verify --site=default`, confirm `Revision`
now equals the new (higher) target and the tokens match what the "rolled back to" state should be.

---

## 4. Rebuild themes

This is the Plane 5 shared immutable bundle (`public/themes/style_{light,dark}.css` + compact/RTL
variants), built once per deployment from `brand/tokens/*.json` via webpack — **not** a per-tenant
operation, and not a branding-module CLI command at all.

**Native (per `CLAUDE.local.md` §6 — must happen off the `G:` Drive mount):**
```powershell
cd C:\openemr-stack\build
npm ci
npm run build

# PURGE FIRST — see the warning below. /E copies; it never deletes.
Remove-Item "G:\My Drive\OpenEMR\public\themes\*" -Recurse -Force -ErrorAction SilentlyContinue

robocopy C:\openemr-stack\build\public\themes "G:\My Drive\OpenEMR\public\themes" /E
robocopy C:\openemr-stack\build\public\assets "G:\My Drive\OpenEMR\public\assets" /E
```

> ### ⚠️ Purge `public/themes/` before copying, or `Q77` is not enforced
>
> **`robocopy /E` copies subdirectories, including empty ones. It deletes nothing at the
> destination.** Webpack's `output.clean` (with its keep-list for the static CSS that
> `build:sync` supplies) purges the **build workspace** only — that purge never reaches the
> deployed directory.
>
> So a `style_solar.css` produced by any build made *before* the entry map was pruned survives
> at the destination indefinitely. Locked **`Q77`** does not say "must not be built"; it says the
> surplus themes' artifacts **"MUST NOT exist in the deployed `public/themes/` directory"** —
> and `interface/globals.php:476` gates theme selection purely on `file_exists()`, so a stale
> `globals` or `user_settings` value pointing at a surviving file would resolve it and render it.
> That is precisely the failure mode `Q77` chose the build-layer approach to prevent.
>
> Recorded as `docs/RebrandingBugs.md` RB-08. Purge-then-copy is used rather than `robocopy /MIR`
> because `/MIR` also deletes anything the workspace legitimately lacks, which is a sharper tool
> than a hand-run procedure wants.

`robocopy` exit codes 0–7 mean success. Re-copy any changed source file
(`interface/themes/thiqa/*.scss`, `webpack.themes.js`, etc.) into the `C:\openemr-stack\build` workspace
before building — the workspace mirrors the repo layout but is a separate checkout on local NTFS.

**CI/Docker (documented, not executed here), per the tracked `CLAUDE.md`:**
```bash
npm run build             # production build: webpack + CSS sync
# or individually:
npm run build:webpack:prod
npm run build:sync
```

**What to check afterward:**
- **Run the automated Q77 check** — this is the one that actually enforces the locked decision:
  ```powershell
  C:\openemr-stack\php\php.exe vendor\bin\phpunit -c phpunit-isolated.xml `
      --filter 'BrandingGovernanceGuard' --no-coverage
  ```
  `testDeployedThemeDirectoryContainsNoForbiddenStylesheet` fails if any `solar` / `manila` /
  `cobalt_blue` / `forest_green` stylesheet survived the copy. It skips (rather than fails) when
  `public/themes/` has not been built, so it is safe to run on a fresh checkout.
- File listing: `public/themes/` should contain exactly the Q77-approved set (17 CSS files per
  `docs/branding/changes.md` row 076 — `style_{light,dark}`, `compact_style_{light,dark}`,
  `rtl_style_{light,dark}`, `rtl_compact_style_{light,dark}`, `style_pdf`, `rtl_style_pdf`,
  `tabs_style_{full,compact}`, `rtl_tabs_style_{full,compact}` + non-theme shells) — zero
  `solar`/`manila`/`cobalt_blue`/`forest_green` outputs.
- `tools\branding\bin\generate-tokens.php --check` (§6) should report the deployed SCSS/CSS artefacts as
  up to date against `brand/tokens/*.json` — a rebuild that used stale token source would show up here as
  drift.
- Live: `GET /interface/login/login.php?site=default` and inspect the served `<link>` stylesheet for the
  expected Thiqa palette values.

---

## 5. Regenerate tokens

`tools/branding/bin/generate-tokens.php` reads `brand/tokens/thiqa-tokens.json` and
`brand/typography/*`, and writes the SCSS token partials, the CSS custom-property partial, the typography
partial, and both SMART style templates (light + dark). Output is byte-identical across runs — no
timestamps, no absolute paths, LF endings throughout (script docblock, `generate-tokens.php:20-24`).

**Native and CI/Docker are identical here** — this is a plain PHP CLI script with no container dependency,
runnable anywhere PHP is available:

```powershell
C:\openemr-stack\php\php.exe tools\branding\bin\generate-tokens.php
```

Exit codes (script docblock, line 27): `0` success · `1` token/validation failure · `2` bad usage · `3`
`--check` found drift (see §6 — that mode is read-only and does not apply this way).

**What to check afterward:**
- The command itself prints one `<sha256>  <filename>` line per artefact plus a
  `Wrote N artefacts to <dir>` summary — confirm the count matches expectations (12 in this session's
  environment: 6 preview + 6 deployed, per the live `--check` run in §6).
- `git diff` (or `git status`) over the written paths (`interface/themes/thiqa/*.scss`,
  `.../templates/api/smart/smart-style_{light,dark}.json.twig`, etc.) to see exactly what changed before
  committing — this script writes to the working tree, so review the diff like any other generated-code
  change.
- Re-run with `--check` (§6) to confirm the regenerated files now match themselves (a no-op check
  immediately after a successful write should report zero drift).

---

## 6. Verify a release

Two independent, both genuinely read-only, both run for real in this session against the live host.

### 6.1 Per-tenant state: `thiqa-branding:verify`

**Native:**
```powershell
C:\openemr-stack\php\php.exe bin\console thiqa-branding:verify --site=default
```

**Actual output, captured live in this session (2026-08-10, this host, `sites/default`):**
```
 ------------------------ -------------------------------------------------
  Site                     default
  Status                   never materialised (rendering product defaults)
  Revision                 0
  Materialised             never
  Materialised at          not recorded
  Age                      unknown
  Light token stylesheet   absent
  Dark token stylesheet    absent
 ------------------------ -------------------------------------------------

 [OK] This tenant's branding state is self-consistent.
```
This independently reproduces `docs/branding/remaining-dependencies.md`'s area-42 finding: the one
existing tenant has never had a Tier-2 overlay materialised. "Never materialised" is reported as a
**consistent, healthy state** (`BrandingHealthCheck::statusFor()`, lines 172-190 — it is
`BrandingHealthStatus::NeverMaterialised`, not `Inconsistent`), because it correctly describes a tenant
rendering pure Tier-1 (product) defaults with no overlay ever attempted — this is by design, not a defect.

**CI/Docker (documented, not executed here):**
```bash
openemr-cmd worktree exec <branch> e 'php bin/console thiqa-branding:verify --site=default'
```

The command is **structurally read-only**: `VerifyCommand` holds only `BrandingHealthCheck`, which itself
calls exactly two read methods (`currentRevision()`, `readBrandingGlobals()`) and never opens a
transaction (`BrandingHealthCheck.php:34-40`) — confirmed by reading the source before running it, per this
task's constraint. It is documented as safe to run against production on a schedule
(`VerifyCommand.php:35-38`).

**Exit codes:** `0` the tenant's state is coherent (including "never materialised" and "merely stale") ·
`1` the state contradicts itself or could not be read — usable directly as a health probe · `2` the
invocation itself was wrong (missing `--site`).

**What "inconsistent" would look like**, so it's recognisable if seen: `BrandingInconsistency` cases fire
when the three things a materialisation moves together (stylesheets, the materialisation stamp, the
revision) have drifted apart — e.g. a revision recorded as materialised but a stylesheet missing, or a
stylesheet present with no revision recorded. Any of these prints under an `Inconsistencies` section and
the command exits `1`.

### 6.2 Generated-artefact drift: `generate-tokens.php --check`

**Native and CI/Docker identical** (plain PHP script):
```powershell
C:\openemr-stack\php\php.exe tools\branding\bin\generate-tokens.php --check
```

**Actual output, captured live in this session:**
```
12 branding artefacts are up to date (6 preview, 6 deployed).
```

This mode is read-only by construction — confirmed from source before running: the `--check` branch
(`generate-tokens.php:62-90`) only ever calls `$writer->verify($files)` and
`(new DeployedArtefacts(...))->verify($files)`; the mutating `$writer->write($files)` call
(line 92) is in a separate branch that `--check` does not reach. It checks **two** independent things
deliberately — the preview copies and the actually-deployed SCSS/SMART-contract files the application
loads — because checking only the preview was audit finding F-04 (`docs/branding/coverage-matrix.md`
row 23).

Exit codes: `0` up to date · `3` drift found (with each drifted file listed to stderr, plus "Re-run …
and commit the result").

### 6.3 The full release-gate checklist (`docs/RebrandingPlan.md` §7.3)

| Gate | How to check | Status as of this session |
|---|---|---|
| `brand/manifests/SHA256SUMS` fully verifies | No dedicated script exists; manual re-hash, e.g. `Get-ChildItem brand -Recurse -File \| Get-FileHash -Algorithm SHA256` compared against the manifest | 123/123 verified in `docs/branding/remaining-dependencies.md` V-06 (note: plan's own "117" figure is stale — 123 is current) |
| Token generator reports no diff | §6.2 above | Confirmed 0 drift, this session |
| `Q77` entry-map assertion passes | `BrandingGovernanceGuardTest` (isolated PHPUnit) | Passing per `docs/branding/changes.md` row 076/`coverage-matrix.md` row 10 |
| V-01…V-10 green | See `docs/branding/remaining-dependencies.md` §3 | Mixed — several are source/isolated-test-verified but not live-exercised (V-02, V-03, V-04, V-07 partial); see that document for the honest per-item breakdown, not summarised here |
| D-register: no open **Blocking-for-release** item | `docs/branding/remaining-dependencies.md` §4 | D-3, D-9, D-10, D-11 remain open and blocking — **do not treat a passing `verify`/`--check` pair as a release green light on its own** |

**What to check afterward (both commands together):** a release is *not* ready merely because both
commands above exit `0` — that only proves "the one existing tenant's globals are internally consistent"
and "the checked-in generated artefacts match their source." Cross-reference the D-register in
`docs/branding/remaining-dependencies.md` before calling anything a release.

---

## 7. Recover a failed materialisation

Covered in mechanism by §3.1 above; this section is the operator-facing checklist.

**Step 1 — read the failure.** `MaterialiseCommand::summarise()` (`MaterialiseCommand.php:264-303`) prints
`Outcome`, `Retryable` (yes/no), and a `Live revision` that is always the *actually active* revision, not
the attempted target. For a failure specifically:
```
The run failed and was unwound. Revision <n-1> remains live; the same payload can be retried.
```
Exit code `1` (transient fault) means retry is meaningful; exit code `2` (rejected) means the payload
itself needs fixing first — retrying an invalid payload unchanged will fail the same way.

**Step 2 — confirm the tenant is genuinely undamaged**, don't just trust the message:
```powershell
C:\openemr-stack\php\php.exe bin\console thiqa-branding:verify --site=default
```
Expect `Status: self-consistent` and `Revision` still at the pre-attempt value. If `verify` instead reports
an `Inconsistencies` section, that is a different, more serious situation than an ordinary failed
materialisation — see the note at the end of this section.

**Step 3 — retry.** Because `MaterialiseCommand`'s idempotence check means "a revision that is already live
is reported as unchanged and exits 0" (class docblock, `MaterialiseCommand.php:56-57`), the exact same
invocation (same `--site`, same or higher `--revision`, same `--payload`) is safe to run again:
```powershell
C:\openemr-stack\php\php.exe bin\console thiqa-branding:materialise --site=default --payload=C:\path\to\overlay.json
```
No special "recovery mode" flag exists or is needed — a plain retry *is* the recovery procedure, per
principle P6 and the materialiser's own step 6 design (`BrandingMaterialiser.php:52-58`,
"the database needs no unwinding here... revision n-1 stays fully active").

**What "recovery" does *not* mean here:** there is no separate cleanup command to run against stray
staged/temporary files. `AtomicFileWriter`'s stage/verify/commit/discard model
(referenced from `BrandingMaterialiser.php:222-265`) means a failed run's temporary files are discarded as
part of the same `unwind()` call that runs synchronously inside the failed `materialise()` invocation —
by the time the CLI prints its error message, cleanup has already happened. Nothing is left for a human or
a cron job to sweep up under normal failure (a mid-process crash — e.g. `kill -9` — is the one case that
could leave a stale temporary file; this is exactly what `MaterialiserKillRecoveryTest` exercises, per
`docs/branding/remaining-dependencies.md` V-02/V-03, and it is proven in isolated tests, not live, since no
live materialisation has ever run against the one existing tenant).

**If `verify` reports an `Inconsistencies` section** (not just a failed materialise attempt, but a
genuinely self-contradictory state — e.g. hand-edited `globals`, or a filesystem restore that didn't match
the database): this is outside what a retry fixes, since the materialiser's own idempotence check compares
against the *recorded* revision, which may itself be the inconsistent value. This situation is not covered
by any command in this module — it needs manual inspection of the specific `BrandingInconsistency` case
reported (`BrandingHealthCheck.php:110-146` enumerates the four possible cases:
`UnreadableMaterialisationStamp`, `StampInTheFuture`, `RevisionWithoutStylesheet`/`MissingMaterialisationStamp`,
`StylesheetWithoutRevision`/`StampWithoutRevision`) before deciding whether a targeted `globals` correction
or a fresh materialisation (§2) is the right fix. No live example of this state has been observed on this
system; nothing in this codebase automates its resolution.

---

## 8. Create and retain verified database backups

The existing module command remains:

```powershell
C:\openemr-stack\php\php.exe bin\console thiqa-branding:backup --site=default `
  --target=C:\configured\backup-directory --label=scheduled --keep=7
```

Supply the deployment's real configured target explicitly. The historical
`C:/openemr-stack/backups` default is retained only so existing schedules do not silently change destination;
it is not the portable target recommendation.

New verified dumps use `managed-db-backup-v1-<label>-<YYYYMMDD-HHMMSS>.sql`. Retention recognizes both this
neutral format and verified legacy `thiqa-<label>-<YYYYMMDD-HHMMSS>.sql` files as one chronological set. It
does not rename existing files. `--keep` must be a positive whole number; zero and malformed values fail rather
than being silently clamped. The command reports the resolved target, new backup path, neutral/legacy counts,
selection count and each deletion result. Any scan or deletion failure returns nonzero.

Before changing a schedule or planning rollback, read the full format, safety, mixed-archive and rollback
contract in [`docs/branding/backup-retention.md`](backup-retention.md). Unrelated SQL, partial files, compressed
files, malformed names, directories and files without a valid command-written `.sha256` sidecar are never
retention candidates.
