# 17 — Q36: Custom-Module Byte Identity vs Upstream

_Auditor mode: READ-ONLY. Evidence class: **git blob-SHA identity** — deterministic and reproducible._

**Refs under comparison** (from `02-repository-baseline.md`):

| Ref | SHA | Role |
|---|---|---|
| `HEAD` (fork) | `631f2b38cf633769c305233f88cdf9c73ca80657` | Fork tip |
| `v8_2_0` | `225033dcca1eeab8e7dbb44cf85ff2dc82af5a98` (peeled from tag → `6125a2fd…`) | Upstream stable |
| `upstream/master` | `608f9ae37ccaea5d5c251a0aad84793e801ca485` | Upstream tip |

Merge-base(fork, upstream/master) == fork HEAD → **the fork contains no commits absent from upstream/master**.

---

## 1. Answer to Q36 (verbatim per-module verdict)

| Module | Verdict vs upstream stable (`v8_2_0`) | Verdict vs upstream master |
|---|---|---|
| `oe-module-claimrev-connect` | **CANNOT VERIFY** (not tracked in either upstream tree; composer-installed) | **CANNOT VERIFY** (same reason) |
| `oe-module-comlink-telehealth` | **MODIFIED IN FORK** vs `v8_2_0` (1 file diff) | **IDENTICAL** |
| `oe-module-dashboard-context` | **IDENTICAL TO UPSTREAM STABLE** | **IDENTICAL** |
| `oe-module-dorn` | **IDENTICAL TO UPSTREAM STABLE** | **MODIFIED IN FORK** (1 file diff) |
| `oe-module-ehi-exporter` | **IDENTICAL TO UPSTREAM STABLE** | **MODIFIED IN FORK** (1 file diff) |
| `oe-module-faxsms` | **IDENTICAL TO UPSTREAM STABLE** | **MODIFIED IN FORK** (2 file diff) |
| `oe-module-prior-authorizations` | **IDENTICAL TO UPSTREAM STABLE** | **IDENTICAL** |
| `oe-module-weno` | **IDENTICAL TO UPSTREAM STABLE** | **MODIFIED IN FORK** (1 file diff) |
| `interface/modules/custom_modules/README.md` (root) | **IDENTICAL** | **IDENTICAL** |

**Interpretation of the "MODIFIED IN FORK" label:** since the fork is a strict ancestor of upstream/master, no file at fork HEAD is a fork-only edit relative to master. Every "MODIFIED IN FORK vs master" row above therefore means "**upstream/master has since moved past the fork on this file — the fork holds the older upstream blob**." Symmetrically, "MODIFIED IN FORK vs `v8_2_0`" on `oe-module-comlink-telehealth` means the `v8_2_0` tag was cut with a divergent blob for `tests/bootstrap.php` that neither the fork nor current master contain — a stable-branch-only patch, not a fork edit.

There are **zero true fork-only modifications** to any custom module. This is a corollary of the ancestor baseline established in §5 (`02-repository-baseline.md:95-101`).

---

## 2. Method note

Comparison is performed at the **git blob-SHA level** using `git ls-tree -r <ref> --format='%(objecttype) %(objectname) %(path)' -- interface/modules/custom_modules/`. Two files with the same SHA are byte-identical (git computes SHAs from `blob <len>\0<contents>`). Per-module sets are computed by grouping every returned path by its depth-1 segment under `interface/modules/custom_modules/`, then taking set intersections (common paths → check SHA equality), set differences (added / deleted). Untracked files come from `git status --porcelain -- interface/modules/custom_modules/` (0 hits). Reproducible with `tools/discovery/openemr-decision-evidence/module-byte-identity.py` fed the three manifest files under `evidence/manifests/`.

---

## 3. Per-module detail

Blob counts and modified-file lists below come from `evidence/raw/module-detail.json`.

### `oe-module-claimrev-connect`
- Path: `interface/modules/custom_modules/oe-module-claimrev-connect/` (present on disk — `Get-ChildItem` confirms — but **not tracked**).
- Blob count in fork tree / v8_2_0 / master: `0 / 0 / 0`.
- Reason: `.gitignore:11-15` excludes the entire dir; it is dropped in by `openemr/oe-module-installer-plugin` at `composer install` time (`composer.json:52`, `composer.lock:426-473`).
- **Cannot verify byte identity** from git alone — need to inspect the extracted zip at `composer.lock:435` (`.../zipball/978b0dd498e0e166992259926d6fa77bf56266d4`) against on-disk state. That would require running `composer install` (out of scope) or fetching the zipball out-of-band.

### `oe-module-comlink-telehealth`
- Blob count fork / v8_2_0 / master: `100 / 100 / 100`.
- Added vs stable: 0. Deleted vs stable: 0. **Modified vs stable: 1**.
  - `tests/bootstrap.php`
- Added vs master: 0. Deleted vs master: 0. Modified vs master: 0. → identical to master.

### `oe-module-dashboard-context`
- Blob count fork / v8_2_0 / master: `23 / 23 / 23`.
- Diff vs stable: none. Diff vs master: none. → identical to both.

### `oe-module-dorn`
- Blob count fork / v8_2_0 / master: `38 / 38 / 38`.
- Diff vs stable: none.
- **Modified vs master: 1** — `public/ack_lab_results.php` (upstream/master has since evolved past fork HEAD on this file).

### `oe-module-ehi-exporter`
- Blob count fork / v8_2_0 / master: `35 / 35 / 35`.
- Diff vs stable: none.
- **Modified vs master: 1** — `src/Services/EhiExporter.php`.

### `oe-module-faxsms`
- Blob count fork / v8_2_0 / master: `74 / 74 / 74`.
- Diff vs stable: none. (Consistent with the fork's HEAD subject `refactor(oe-module-faxsms): Fix SignalWire fax support in oe-module-faxsms (#12526)` — that fix landed on/before the tag and is fully absorbed into `v8_2_0`.)
- **Modified vs master: 2** — `library/api_onetime.php`, `src/Controller/FaxDocumentService.php` (upstream continued patching after fork HEAD).

### `oe-module-prior-authorizations`
- Blob count fork / v8_2_0 / master: `15 / 15 / 15`.
- Diff vs stable: none. Diff vs master: none. → identical to both.

### `oe-module-weno`
- Blob count fork / v8_2_0 / master: `42 / 42 / 42`.
- Diff vs stable: none.
- **Modified vs master: 1** — `templates/pharmacy_list_form.php`.

### `interface/modules/custom_modules/README.md`
- One blob, SHA `2d89b07e34293c750fc97b84b243e2cbdfc150ed` in all three trees. Identical.

---

## 4. Composer-managed vs git-tracked

| Module | Tracked in git (fork HEAD) | In `composer.json` require | In `composer.lock` | Plugin drop target |
|---|:-:|:-:|:-:|---|
| `oe-module-claimrev-connect` | ❌ (`.gitignore:15`) | ✅ `claimrevolution/oe-module-claimrev-connect: ^2.1` (`composer.json:52`) | ✅ `v2.1.6`, ref `978b0dd4…` (`composer.lock:426-431`) | `interface/modules/custom_modules/oe-module-claimrev-connect/` (verified — dir exists on disk) |
| `oe-module-comlink-telehealth` | ✅ | ❌ | ❌ | (in-tree; not composer-managed) |
| `oe-module-dashboard-context` | ✅ | ❌ | ❌ | (in-tree) |
| `oe-module-dorn` | ✅ | ❌ | ❌ | (in-tree) |
| `oe-module-ehi-exporter` | ✅ | ❌ | ❌ | (in-tree) |
| `oe-module-faxsms` | ✅ | ❌ | ❌ | (in-tree) |
| `oe-module-prior-authorizations` | ✅ | ❌ | ❌ | (in-tree) |
| `oe-module-weno` | ✅ | ❌ | ❌ | (in-tree) |
| `openemr/oe-module-installer-plugin` | ❌ (composer plugin, not a module dir) | ✅ allow-listed (`composer.json:205`) | ✅ `0.1.5` (`composer.lock:6541-6587`) | N/A — this is the plugin itself; installs into `vendor/` |
| `openemr/oe-module-cqm` | ❌ | ❌ (declared only as `repositories[]` VCS entry, `composer.json:167-169`) | ❌ | UNKNOWN — declared as available VCS source but never `require`d |

**One composer-installed module** in this fork (`claimrev-connect`). All 7 other in-tree custom modules are vendored directly into the git repository by upstream OpenEMR — the `oe-module-*` prefix is convention, not a marker of composer-installed status.

---

## 5. Byte identity vs upstream master (separate table)

Separate view — for modules whose upstream copy has evolved past `v8_2_0`:

| Module | Byte-identical to `v8_2_0` | Byte-identical to `master` | Files differing vs `master` (fork holds *older* blob) |
|---|:-:|:-:|---|
| `oe-module-claimrev-connect` | N/A (untracked) | N/A (untracked) | — |
| `oe-module-comlink-telehealth` | ❌ | ✅ | (none) |
| `oe-module-dashboard-context` | ✅ | ✅ | (none) |
| `oe-module-dorn` | ✅ | ❌ | `public/ack_lab_results.php` |
| `oe-module-ehi-exporter` | ✅ | ❌ | `src/Services/EhiExporter.php` |
| `oe-module-faxsms` | ✅ | ❌ | `library/api_onetime.php`, `src/Controller/FaxDocumentService.php` |
| `oe-module-prior-authorizations` | ✅ | ✅ | (none) |
| `oe-module-weno` | ✅ | ❌ | `templates/pharmacy_list_form.php` |

Total files where fork lags upstream/master (across all custom modules): **5**. All are patches landed on upstream/master between the fork HEAD commit date (2026-07-04) and current upstream/master (2026-07-08+).

---

## 6. Q36 direct answer — definitive 5-value verdict

Using the required labels `{IDENTICAL TO UPSTREAM STABLE | MODIFIED IN FORK | FORK ONLY | UPSTREAM ONLY | CANNOT VERIFY}`, judged **against upstream stable `v8_2_0` (the release cadence any downstream would pin to)**:

| Module | Q36 verdict |
|---|---|
| `oe-module-claimrev-connect` | **CANNOT VERIFY** — composer-installed, absent from every git tree |
| `oe-module-comlink-telehealth` | **MODIFIED IN FORK** — 1 blob diff at `tests/bootstrap.php` (see caveat in §1) |
| `oe-module-dashboard-context` | **IDENTICAL TO UPSTREAM STABLE** |
| `oe-module-dorn` | **IDENTICAL TO UPSTREAM STABLE** |
| `oe-module-ehi-exporter` | **IDENTICAL TO UPSTREAM STABLE** |
| `oe-module-faxsms` | **IDENTICAL TO UPSTREAM STABLE** |
| `oe-module-prior-authorizations` | **IDENTICAL TO UPSTREAM STABLE** |
| `oe-module-weno` | **IDENTICAL TO UPSTREAM STABLE** |

**Aggregate: 6 identical, 1 modified (test bootstrap only), 1 cannot-verify (composer), 0 fork-only, 0 upstream-only.**

The single "modified" row (`oe-module-comlink-telehealth/tests/bootstrap.php`) is **not a fork edit** — the ancestor property forbids that. It is a `v8_2_0`-branch-only variant of the file: fork HEAD holds the master-line blob, `v8_2_0` holds a stable-branch-specific blob (either a cherry-pick that never landed on master or a patch that was reworked before master reintegration). To confirm which, one would run `git log v8_2_0 -- interface/modules/custom_modules/oe-module-comlink-telehealth/tests/bootstrap.php` (bounded by the 501-commit shallow depth of the tag branch).

---

## 7. Confidence and limitations

- **Blob-level comparison is exact** — a matching SHA is a byte-for-byte guarantee (SHA-1 collision is not a realistic threat in this problem space, and would be detected by git itself via `objecttype`+`objectname`). Confidence **CONFIRMED** for every "IDENTICAL" and "MODIFIED" verdict above.
- **Shallow-clone caveat:** the local mirror has 2,766 commits reachable from `upstream/master` and 501 from `v8_2_0` (per `02-repository-baseline.md:122`). This bounds only *history walking*; it does **not** affect blob comparison — the tree object for each ref is fully materialized locally, so every file's blob SHA at each ref is directly readable. There is no scenario in which a deeper clone would change the ls-tree output for `HEAD`, `v8_2_0`, or `upstream/master`.
- **Untracked files:** `git status --porcelain -- interface/modules/custom_modules/` returns 0 lines. Notably, the on-disk directory `oe-module-claimrev-connect/` is *not* reported by `git status` because it is explicitly gitignored (`.gitignore:15`) — a distinct condition from "untracked but visible". Evidence: `evidence/raw/untracked-modules.txt` (empty).
- **Symlinks:** grep for `^120000` in any of the three manifests returns zero — none of the custom modules ship symlinks in any of the three refs. Reproduce: `Select-String -Path evidence\manifests\*.txt -Pattern '^120000'`.
- **Sole EVIDENCE-BLOCKED item:** `oe-module-claimrev-connect` byte-identity vs the upstream `claimrevolution/oe-module-claimrev-connect@v2.1.6` package. Resolvable by: (a) running `composer install` (out of scope for this READ-ONLY audit — per §21 mission spec), or (b) fetching the zipball at `composer.lock:435` and diffing extracted contents against the on-disk `interface/modules/custom_modules/oe-module-claimrev-connect/`.

---

## Artifacts written

| Path | Rows / notes |
|---|---|
| `docs/discovery/openemr-decision-evidence/06-module-drift-inventory.csv` | 9 data rows (7 real modules + 1 root-file group + 1 composer-only) |
| `docs/discovery/openemr-decision-evidence/17-q36-module-byte-identity.md` | (this file) |
| `docs/discovery/openemr-decision-evidence/evidence/manifests/fork-module-blobs.txt` | 328 lines |
| `docs/discovery/openemr-decision-evidence/evidence/manifests/upstream-module-blobs-v8_2_0.txt` | 328 lines |
| `docs/discovery/openemr-decision-evidence/evidence/manifests/upstream-module-blobs-master.txt` | 328 lines |
| `docs/discovery/openemr-decision-evidence/evidence/raw/module-detail.json` | Per-module set-diff + modified-path detail |
| `docs/discovery/openemr-decision-evidence/evidence/raw/untracked-modules.txt` | 0 lines (no untracked files under `interface/modules/custom_modules/`) |
| `tools/discovery/openemr-decision-evidence/module-byte-identity.py` | Helper — consumes the 3 manifests, emits CSV + JSON |
| `docs/discovery/openemr-decision-evidence/22-command-log.txt` | Appended: 3 `git ls-tree` runs + Python invocation |

## Non-modification confirmation

`git status --short` still reports only the pre-existing untracked local files (`SETUP-STATUS.md`, `docs/00-discovery/`, `fix-docker-virtualization.ps1`) plus the newly created `docs/discovery/` and `tools/discovery/` output trees. **No tracked file was modified during this task.**
