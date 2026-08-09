# 05 — Upstream / Fork Drift Audit

_Auditor: opencode (github-copilot/claude-opus-4.7). Mode: READ-ONLY. All investigation output goes ONLY to `docs/discovery/openemr-decision-evidence/` and `tools/discovery/openemr-decision-evidence/`._

## Companion machine-readable artifact

- `07-core-modification-inventory.csv` — 431 rows, one per changed file between `HEAD` and `upstream/master`. Columns per §5 mission spec.

## 1. Executive drift finding — CONFIRMED

**The fork's HEAD is a plain-ancestor of `upstream/master`. All 431 file differences visible in `git diff HEAD upstream/master` are upstream commits landed AFTER the fork's HEAD date (2026-07-04); none are fork-only modifications.** This was established in `02-repository-baseline.md` by `git merge-base HEAD upstream/master` returning the fork SHA itself (`631f2b38cf633769c305233f88cdf9c73ca80657`), and is re-confirmed at the blob level in section 9 of this report: 25 out of 25 sampled paths' fork-HEAD blob SHAs are present in `upstream/master`'s per-path commit history. **Therefore the `likely_core_modification` column in `07-core-modification-inventory.csv` is `FALSE` for every row** — the drift matrix is entirely "upstream-ahead-of-fork", not "fork-ahead-of-upstream".

Framing note for downstream readers of this dossier: **`git diff HEAD upstream/master` here shows what upstream added after the fork, not what the fork changed.** The mental model "diff = local edits" that applies to a typical downstream fork does NOT apply here.

## 2. Diff summary (mirrored from 02-repository-baseline.md)

Evidence: `evidence/raw/diff-HEAD-vs-upstream-master.txt`, `evidence/raw/diff-HEAD-vs-v8_2_0.txt`.

| Comparison | Modified | Added | Deleted | Renamed | Total paths |
|------------|---------:|------:|--------:|--------:|------------:|
| `HEAD` vs `upstream/master` | 358 | 67 | 5 | 1 | 431 |
| `HEAD` vs `v8_2_0`          | 110 |  3 | 4 | 0 | 117 |

Aggregate line-delta from `git diff --shortstat HEAD upstream/master` (see `evidence/raw/diffstat-HEAD-vs-upstream-master.txt` and `evidence/raw/diffstat-full-HEAD-vs-upstream-master.txt`): **431 files changed, ~39,143 insertions(+), ~4,651 deletions(-)** — dominated by the `tests/` (+28,023) and `tools/` (+3,418) directories (release-tooling additions upstream).

## 3. Classification breakdown

Computed from `07-core-modification-inventory.csv`. Evidence: `evidence/raw/drift-summary-stats.txt`.

| classification | count | percentage |
|---|---:|---:|
| `upstream_unmodified`     | 414 | 96.1% |
| `custom_module`           |  15 |  3.5% |
| `dependency_lock_change`  |   2 |  0.5% |

**All three classifications map to the same underlying finding**: every row is a change originating in upstream. The `custom_module` label reflects the *directory location* (`custom/`, `interface/modules/custom_modules/`, `interface/modules/zend_modules/`) rather than fork authorship — the 15 rows in that bucket are upstream commits touching modules that happen to live under those paths. `dependency_lock_change` covers `composer.lock` and `package-lock.json` — upstream bumped these along with dep bumps (see section 5). Nothing classifies as `saas_control_plane`, `saudi_localization`, `nphies_integration`, `security_patch`, `temporary_hotfix`, `generated_asset`, or `fork_configuration`, because no fork-only edits exist.

## 4. Directory-level drift (top-level)

Evidence: `evidence/raw/drift-summary-stats.txt` (Python-aggregated from CSV numstat columns).

| top-level dir | files changed | added_lines | deleted_lines |
|---|---:|---:|---:|
| `interface/`   | 79 | 1,159  | 1,015 |
| `tests/`       | 58 | 28,023 | 158   |
| `src/`         | 49 | 890    | 673   |
| `.phpstan/`    | 44 | 131    | 1,751 |
| `.github/`     | 28 | 2,571  | 234   |
| `ci/`          | 27 | 47     | 32    |
| `tools/`       | 25 | 3,418  | 3     |
| `(root)`       | 19 | 1,923  | 239   |
| `library/`     | 19 | 175    | 58    |
| `ccdaservice/` | 17 | 316    | 215   |
| `docker/`      | 16 | 55     | 45    |
| `templates/`   | 14 | 42     | 43    |
| `controllers/` | 10 | 44     | 34    |
| `ccr/`         |  9 | 30     | 41    |
| `config/`      |  6 | 148    | 12    |
| `portal/`      |  5 | 15     | 15    |
| `custom/`      |  3 | 38     | 37    |
| `docs/`        |  2 | 97     | 26    |
| `gacl/`        |  1 | 1      | 1     |

Top-20 second-level directories (`evidence/raw/drift-summary-stats.txt`): `tests/Tests` (51), `.phpstan/baseline` (41), `tools/release` (25), `.github/workflows` (20), `interface/forms` (18), `src/Common` (18), `interface/patient_file` (15), `src/Services` (14), `interface/modules` (12), `docker/library` (10), `templates/super` (10), `interface/reports` (9), `ccdaservice/oe-blue-button-generate` (7), `interface/main` (7), `.github/scripts` (5), `ccdaservice/utils` (5), `interface/usergroup` (5), `src/Patient` (5), `tests/bats` (5), `interface/orders` (4).

## 5. Upstream-only-since-fork commits — categorized (top 40)

Total commits in `HEAD..upstream/master` = **172**. Evidence: `evidence/raw/log-HEAD-to-upstream-master.txt`, `evidence/raw/commit-categorization.txt`.

By Conventional-Commit type prefix:

| type | count |
|---|---:|
| chore    | 83 |
| fix      | 37 |
| feat     | 16 |
| ci       | 11 |
| test     |  8 |
| docs     |  7 |
| refactor |  7 |
| style    |  2 |
| build    |  1 |

The **chore** bucket is dominated by dependabot `chore(deps): bump ...` bumps (composer packages, docker base images, npm dev-deps). The **feat** bucket is dominated by the "Phase 2 release-tooling" work (`feat(release): ...` — PRs #13062, #13079, #13087, #13092 — building out the PackageAssembler/Taskfile/build-release workflows). See `evidence/raw/commit-categorization.txt` for the full top-40 list.

Notable non-`chore(deps)` items in the top 40 (all landed after fork HEAD, all trivially replayable):

- `#13092` `feat(release)` retarget openemr-tag dispatch to openemr (Phase 2 PR 2c)
- `#13087` `feat(release)` build-release + build-release-on-tag + build-patch workflows
- `#13079` `feat(release)` Taskfile + bin scripts for build workflows
- `#13085` `build(docker)` bake pcov coverage driver into dev-php-fpm images
- `#12914` `test(api)` add coverage for version/product/list endpoints
- `#13062` `feat(release)` copy PackageAssembler + PreflightChecker from openemr-devops
- `#13048` `chore(hardening)` Remove all remaining first-party use of eval — note this is the commit that deleted `.phpstan/baseline/openemr.forbiddenEval.php` (see section 6).
- `#12419` `test(api)` add appointment endpoint tests
- `#13040` `fix(ci)` point truncated Release body at rel branch + version anchor

## 6. Files removed from fork's HEAD in upstream/master (5 D-status)

Evidence: `07-core-modification-inventory.csv` filtered on `change_type=D`; upstream removal commit identified by `git log --oneline -1 HEAD..upstream/master -- <path>`.

| path | removed by upstream commit |
|---|---|
| `.github/docker-byte-identical.yml`             | see `git log HEAD..upstream/master -- .github/docker-byte-identical.yml` — superseded by the renamed byte-identical workflow (R094 pair, section 8) |
| `.phpstan/baseline/openemr.forbiddenEval.php`   | `#13048` `chore(hardening): Remove all remaining first-party use of eval` |
| `ccr/uuid.php`                                  | absorbed by `OpenEMR\Common\Uuid\UuidRegistry` (verifiable via `git log HEAD..upstream/master -- ccr/uuid.php`) |
| `interface/expand_contract_js.php`              | JS extracted / replaced by asset-pipeline delivery |
| `src/Common/Command/CreateReleaseChangelogCommand.php` | superseded by the new release-tooling in `tools/release/` (Phase 2 PR series `#13062`/`#13079`) |

**Impact on the fork if upgraded to upstream/master**: these are deletions — they will simply disappear from the working tree. None are referenced by fork-only code (there is no fork-only code).

## 7. Files added upstream since fork (67 A-status)

Grouped by top-level dir (evidence: `evidence/raw/drift-summary-stats.txt`):

| top-level | added files |
|---|---:|
| `tests/`     | 29 |
| `tools/`     | 21 |
| `.github/`   |  8 |
| `src/`       |  6 |
| `.phpstan/`  |  1 |
| `config/`    |  1 |
| `library/`   |  1 |

The two largest buckets (`tests/`, `tools/`) are the release-tooling test suites and executables introduced by the Phase-2 release-workflow PRs. See CSV rows filtered on `change_type=A` for the full list.

## 8. Renamed (1 R-status)

`R094 .github/workflows/validate-byte-identical.yml` — a 94%-similarity rename. Pair identifiable from the raw diff:

```
R094  .github/workflows/docker-validate-byte-identical.yml  ->  .github/workflows/validate-byte-identical.yml
```

Part of the byte-identical workflow rework that also deleted `.github/docker-byte-identical.yml` (section 6).

## 9. Ancestor claim — blob-level verification (30-sample)

Method: pick 25 paths spread across `src/`, `library/`, `interface/`, `sql/` (targeted 30; `sql/` produced 0 M-status candidates so effective n=25). For each path, resolve the fork's blob SHA at `HEAD`, then walk every `upstream/master` commit that touched that path and check whether any historical blob for that path at that commit equals the fork's blob SHA. If yes, the fork's version of the file WAS the upstream version at some past commit — a direct blob-level confirmation of ancestry.

Script: `tools/discovery/openemr-decision-evidence/verify-ancestor-blobs.py`. Full output: `evidence/raw/drift-verification-sample.txt`.

**Result: 25 / 25 sampled paths have their fork-HEAD blob present in upstream/master's history at the same path.** No exceptions. This is a stronger verification than `merge-base` alone: it confirms not only that fork HEAD is an ancestor commit, but that every sampled file was byte-identical to some historical upstream version.

Sampled paths (each with `MATCH` result):

```
src/Services/Utils/SQLUpgradeService.php
src/Common/Command/ReleasePrepCommand.php
src/Billing/EdiHistory/X12File.php
src/Core/Kernel.php
src/Common/Uuid/UniqueInstallationUuid.php
src/Common/Session/SessionWrapperFactory.php
src/Common/Command/ZfcModule.php
src/Common/Auth/MfaUtils.php
src/Common/Auth/AuthUtils.php
src/Services/FHIR/FhirPersonService.php
library/ESign/Form/Controller.php
library/ESign/Encounter/Controller.php
library/ajax/adminacl_ajax.php
library/ajax/sql_server_status.php
library/smarty/plugins/function.assetsTemplate.php
library/edihistory/edih_csv_data.php
library/edihistory/edih_csv_parse.php
library/smarty/plugins/function.headerTemplate.php
library/htmlspecialchars.inc.php
library/options.inc.php
interface/reports/sales_by_item.php
interface/patient_file/pos_checkout_ippf.php
interface/main/finder/dynamic_finder_ajax.php
interface/patient_file/summary/immunizations.php
interface/usergroup/facilities_add.php
```

Confidence: **CONFIRMED**.

## 10. 117-path drift restricted to `HEAD..v8_2_0`

Evidence: `evidence/raw/diff-HEAD-vs-v8_2_0.txt`, `evidence/raw/commit-categorization.txt`.

| Comparison | M | A | D |
|---|---:|---:|---:|
| `HEAD` vs `v8_2_0` | 110 | 3 | 4 |

The 3 files ADDED in `v8_2_0` that fork does not have:

- `A library/ajax/sql_upgrade_version_check.php` — upstream added a version-check AJAX endpoint used during upgrade
- `A tests/Tests/Unit/Core/HeaderTest.php`
- `A tests/Tests/data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/ccda-cert-data.xml`

The 4 files DELETED in `v8_2_0` (present in fork HEAD, removed by upstream on the 8.2.0 train):

- `D sql/8_2_0-to-8_3_0_upgrade.sql` — the fork's HEAD still contains this stub because it was created for 8.3-dev then reverted/moved on the 8.2.0 release branch (fork is dev-branch snapshot, v8_2_0 is release-branch snapshot; this is expected drift between a dev tip and its most recent release cut).
- `D src/BC/Deprecation.php`
- `D src/BC/DeprecationMode.php`
- `D tests/Tests/Isolated/BC/DeprecationTest.php` — the `BC\Deprecation` machinery was removed on the release branch, along with its test.

**Interpretation**: The fork is on the `8.3.0-dev` train and the 8.2.0 release tag *diverged* (release-branch backports produced deletions that the dev branch doesn't have). Merging `v8_2_0` INTO the fork would surface these 4 deletions as conflicts unless resolved by explicitly preferring the release-branch outcome. This is why `HEAD..upstream/master` (dev-to-dev) is a clean ancestor while `HEAD..v8_2_0` (dev-to-release) is not — they're on different lineages.

## 11. Recommendations for Document 0 (findings only, not decisions)

Restating: this section reports what the evidence shows; it does not prescribe a course of action.

1. **Upgrading the fork to `upstream/master` is a fast-forward merge.** No file-level conflicts are possible — `git merge upstream/master` would resolve to `git reset --hard upstream/master` semantics for tracked content, because HEAD is an ancestor. Verified by `git merge-base --is-ancestor HEAD upstream/master` → 0, and by 25/25 blob-level ancestry matches in section 9.
2. **Upgrading the fork to the `v8_2_0` release tag is NOT a fast-forward.** The merge-base is `b91c12aee3f6022954dd071c53917b2047eabf95` (not HEAD), and 4 files present in fork HEAD are deleted on the release tag (section 10). This would require a real 3-way merge with delete/modify handling for `src/BC/Deprecation*.php`, `sql/8_2_0-to-8_3_0_upgrade.sql`, and the corresponding test. This is a *release-branch vs dev-branch* asymmetry, not a fork-vs-upstream problem.
3. **No fork-only edits exist in tracked content.** The only fork-local artifacts on disk are the three untracked scratch files documented in `02-repository-baseline.md`: `SETUP-STATUS.md`, `docs/00-discovery/`, `fix-docker-virtualization.ps1`. All are self-declared local scratch (per their filenames and status) and none are in `.gitignore`-tracked positions that would affect merges.
4. **The 172 upstream commits since fork HEAD are dominated by dependabot bumps (83 chore) and release-tooling work (16 feat).** No commit in the top 40 appears to alter database schema in a breaking way; the largest schema-adjacent change is `library/ajax/sql_upgrade_version_check.php` added in v8_2_0 (an upgrade-flow helper, not a schema mutation).
5. **The `custom/`, `interface/modules/custom_modules/oe-module-*/`, and `interface/modules/zend_modules/` paths — the traditional "sanctioned extension surfaces" documented in `docs/00-discovery/07-modules-and-extensibility.md` — contain 15 modified files, all authored by upstream.** The fork has NOT taken advantage of these surfaces to add local modules. If a decision is later made to add local business logic (Saudi localization, NPHIES integration, SaaS control-plane), these directories are the sanctioned surfaces per prior audit — and putting new work there keeps `likely_core_modification=FALSE` going forward.

## 12. Provenance / reproducibility

| Artifact | How to regenerate |
|---|---|
| `07-core-modification-inventory.csv` | `python tools/discovery/openemr-decision-evidence/build-drift-inventory.py` (reads `evidence/raw/`; writes CSV) |
| `evidence/raw/drift-verification-sample.txt` | `python tools/discovery/openemr-decision-evidence/verify-ancestor-blobs.py` |
| `evidence/raw/drift-summary-stats.txt` | `$env:PYTHONIOENCODING="utf-8"; python tools/discovery/openemr-decision-evidence/summarize-drift.py` |
| `evidence/raw/commit-categorization.txt` | `$env:PYTHONIOENCODING="utf-8"; python tools/discovery/openemr-decision-evidence/categorize-commits.py` |
| `evidence/raw/numstat-HEAD-vs-upstream-master.txt` | `git diff --numstat HEAD upstream/master` |
| `evidence/raw/ls-tree-HEAD.txt`, `ls-tree-upstream-master.txt`, `ls-tree-v8_2_0.txt` | `git ls-tree -r <ref>` |
| `evidence/raw/log-HEAD-to-upstream-master.txt` | `git log --oneline HEAD..upstream/master` |

All commands appended to `22-command-log.txt`.

## 13. Confidence summary

| Claim | Confidence |
|---|---|
| Fork HEAD is an ancestor of upstream/master | CONFIRMED (merge-base + 25/25 blob-level sample) |
| Every listed diff row is upstream-authored, not fork-authored | CONFIRMED |
| `HEAD → upstream/master` upgrade is fast-forward-able | HIGH (implied by ancestor relationship) |
| `HEAD → v8_2_0` upgrade is not fast-forward | CONFIRMED (`merge-base` = `b91c12aee3...`, ≠ HEAD; 4 D-status paths) |
| Classification bucket assignment | HIGH for `custom_module`/`dependency_lock_change` (directory-name deterministic); HIGH for `upstream_unmodified` (ancestor claim collapses the label) |
| Commit categorization | HIGH (parser is line-regex over `git log --oneline`) |
