# 02 — Repository Baseline

_Auditor: opencode driving general subagents. Mode: READ-ONLY. All investigation output goes ONLY to `docs/discovery/openemr-decision-evidence/` and `tools/discovery/openemr-decision-evidence/`._

## Working tree — verified safe

| Item | Value | Evidence |
|------|-------|----------|
| Root | `D:/OpenEmr` | `git rev-parse --show-toplevel` |
| Branch | `master` | `git branch --show-current` |
| Tracked-file changes vs HEAD | **NONE** | `git status --short` shows only 3 untracked local files |
| Untracked local files (left untouched) | `SETUP-STATUS.md`, `docs/00-discovery/`, `fix-docker-virtualization.ps1` | `git status --short` |
| Submodules present | 2 (see below) | `.gitmodules` |
| Git LFS in use | No | `.gitattributes` has no `filter=lfs` |
| Sparse checkout | Disabled | `git config core.sparseCheckout` → `false` |
| Repository shallow | **Yes** — `.git/shallow` exists | `git rev-parse --is-shallow-repository` → `true` |
| Vendor populated | No (0 files) | `Test-Path vendor` + count |
| node_modules populated | No (0 files) | `Test-Path node_modules` + count |

### Submodules

| Path | URL | Notes |
|------|-----|-------|
| `ci/inferno/onc-certification-g10-test-kit` | https://github.com/openemr/onc-certification-g10-test-kit | `ignore = dirty` |
| `ci/inferno/inferno-files` | https://github.com/openemr/inferno-files.git | `shallow = true` |

_(Both submodules NOT initialized/populated in this working tree — outside the scope of this audit.)_

## Fork identity

| Item | Value |
|------|-------|
| **Fork commit SHA** | `631f2b38cf633769c305233f88cdf9c73ca80657` |
| Commit date (UTC) | 2026-07-04T04:57:07Z |
| Commit author | Jerry Padgett <sjpadgett@gmail.com> |
| Commit subject | `refactor(oe-module-faxsms): Fix SignalWire fax support in oe-module-faxsms (#12526)` |
| Origin URL (redacted, no creds detected) | `https://github.com/mohammedfouly1/openemr` |
| Is GitHub fork? | Yes — user-namespace clone of `openemr/openemr` |

### OpenEMR version (from `version.php:18-42`)

```
v_major   = 8
v_minor   = 3
v_patch   = 0
v_tag     = -dev            ← pre-release / development snapshot
v_realpatch = 0
v_database = 541
v_acl      = 13
v_js_includes = 82
```

**Effective version = OpenEMR 8.3.0-dev** (schema v541, ACL v13).

## Host toolchain

| Tool | Version | Notes |
|------|---------|-------|
| OS | Microsoft Windows NT 10.0.19045.0 | Windows 10 |
| Shell | Windows PowerShell 5.1.19041.7548 | |
| Node.js | v24.11.0 | ✓ meets `package.json` `engines.node >=24.0.0` |
| npm | 11.12.1 | |
| Docker | 29.6.1 (build 8900f1d) | Docker Desktop present |
| Python | 3.13.5 | Available for helper scripts |
| **PHP** | **NOT installed on host** | Per `CLAUDE.md`, all PHP toolchain runs inside `docker/development-easy` |
| **Composer** | **NOT installed on host** | Same |
| **MySQL client** | **NOT installed on host** | |

**Implication for this audit:** any evidence that would require running PHP, Composer, PHPUnit, PHPStan, Rector, PHPCS, or executing SQL is **evidence-blocked at the toolchain layer** unless Docker Desktop's engine is brought up. Per §21 of the mission spec, runtime tests are permitted only when safe/local/non-production; this audit **did not** boot the Docker stack (out of §21's "check whether dependencies are already available" guard — `docker info` was not executed because §1.2 forbids mutating anything beyond output paths). Every count and every structural claim in this evidence package is therefore derived from static source inspection.

## Upstream reference — established during this audit

Per §4.2, `upstream` remote was added (**read-only intent, no code modification**):

```
git remote add upstream https://github.com/openemr/openemr.git
```

### Selected upstream stable

| Ref | Value | Chosen because |
|-----|-------|----------------|
| **UPSTREAM_STABLE_TAG** | `v8_2_0` | Latest release with `prerelease=false, draft=false` per GitHub REST API `/releases?per_page=10` |
| **UPSTREAM_STABLE_SHA** | `6125a2fd8089c8bcc3848071c1293c60e27a7585` | `git rev-parse v8_2_0^{commit}` |
| Stable release published | 2026-07-08T20:43:40Z | `releases[0].published_at` |
| Stable commit date | 2026-07-08T20:33:51Z | `git log -1 --format=%ci` |
| Stable subject | `chore(release): prep 8.2.0 (#12742)` | |
| **UPSTREAM_MASTER_SHA** | `608f9ae37ccaea5d5c251a0aad84793e801ca485` | `git rev-parse upstream/master` |
| **FORK_SHA** | `631f2b38cf633769c305233f88cdf9c73ca80657` | `git rev-parse HEAD` |
| **MERGE_BASE_STABLE_SHA** | `b91c12aee3f6022954dd071c53917b2047eabf95` | `git merge-base HEAD v8_2_0` |
| **MERGE_BASE_MASTER_SHA** | `631f2b38cf633769c305233f88cdf9c73ca80657` (= FORK_SHA) | `git merge-base HEAD upstream/master` |

### Critical baseline finding — reproduced in §5

Because `merge-base(HEAD, upstream/master) == HEAD`, **the fork's HEAD is an ancestor of upstream/master**. Formally:

```
git merge-base --is-ancestor HEAD upstream/master ; $?  → 0 (true)
```

This means **the fork contains ZERO commits that upstream does not also have**. All 431 file differences between `HEAD` and `upstream/master` are upstream commits made AFTER the fork's HEAD date (2026-07-04). None are fork-only edits.

Diff counts (evidence: `git diff --name-status`):

| Comparison | Modified | Added | Deleted | Renamed | Total paths |
|------------|---------:|------:|--------:|--------:|------------:|
| `HEAD` vs `upstream/master` | 358 | 67 | 5 | 1 | 431 |
| `HEAD` vs `v8_2_0` | 110 | 3 | 4 | 0 | 117 |

The 117-path drift against `v8_2_0` is smaller because the stable tag lags upstream/master; the 3 "Added" and 4 "Deleted" files vs the fork are commits that WERE in the 8.2.0 release train that our HEAD does not have (fork lags by 4 days).

### Practical implications for §5–§18

1. **Q36 (module byte identity)** is answerable at HIGH-to-CONFIRMED confidence via `git ls-tree -r <ref>` and blob-SHA comparison — the tag and master trees are locally available.
2. **§5 core-modification-inventory** collapses to a near-trivial finding: **there are no fork-only modifications** to inventory; the drift matrix is entirely "upstream-ahead-of-fork", not "fork-ahead-of-upstream".
3. **The 5-decade-of-open-questions "add upstream remote and measure drift" (Q1)** is now closed by this baseline itself.

### Fetch constraints (per §1.5 disclosure)

- Bulk `git fetch upstream --tags --prune` **FAILED** (curl 28 low-speed timeout).
- Recovery path: single-tag refspec fetch (`refs/tags/v8_2_0:refs/tags/v8_2_0 --depth=1`) SUCCEEDED, and `git fetch upstream master --depth=1 --deepen=2000` SUCCEEDED after retry.
- **Upstream repository state is shallow** in the local mirror: 2,766 commits reachable from `upstream/master`, 501 from `v8_2_0`. This is sufficient for:
  - Blob-level (tree) comparison against `HEAD`.
  - Establishing ancestry (`merge-base --is-ancestor`).
  - File-level `git diff` between two known refs.
- It is **insufficient** for walking commit history back to the earliest fork point (would require full `--unshallow`). No question in Q1–Q75 needs that walk, so this is not a blocker.

## Non-modification confirmation

At the end of this baseline, `git status --short` still reports only the same 3 untracked local files:

```
?? SETUP-STATUS.md
?? docs/00-discovery/
?? fix-docker-virtualization.ps1
```

No tracked file was modified. The only new artifacts are (a) the `upstream` remote in `.git/config`, (b) tag/branch refs under `.git/refs/remotes/upstream/` and `.git/refs/tags/v8_2_0`, and (c) the `docs/discovery/openemr-decision-evidence/` output directory.

---

# ADDENDUM — Audit run 2 (2026-08-07)

_Appended, not rewritten: run 1's content above is preserved verbatim so existing citations stay valid.
Where run 2 supersedes a value, it is stated here._

## What changed between runs

| Field | Run 1 (2026-07-21) | Run 2 (2026-08-07) | Comment |
|---|---|---|---|
| Repository root | `D:/OpenEmr` | `G:/My Drive/OpenEMR` | Different machine; **same fork commit** |
| Fork SHA | `631f2b38…` | `631f2b38…` | **Unchanged** — all run-1 findings remain valid |
| `UPSTREAM_STABLE_TAG` | `v8_2_0` | `v8_2_0` | Unchanged (tags are immutable) |
| `UPSTREAM_STABLE_SHA` | `6125a2fd…` | `6125a2fd…` | Unchanged |
| `UPSTREAM_MASTER_SHA` | `608f9ae3…` | `dad28263…` | Upstream advanced ~17 days |
| Upstream tag fetch | Partial (single-tag, shallow) | **Complete** (`git fetch upstream --tags --prune` succeeded) | Run 2 could rank all tags by date |
| `vendor/` | **Empty** | **Populated** (95 dirs, 247 composer packages) | Enabled empirical Q31/Q37/Q55/Q70 |
| `node_modules/` | Empty | Effectively empty (3 entries) | `npm ci` cannot run on the Drive mount |
| PHP toolchain | Absent | **PHP 8.3.33 native** | Enabled reading installed plugin source |
| Docker | CLI + engine present | CLI only, **engine cannot start** | No nested virtualization on this GCE VM |

Because the stable tag is immutable, **all production-drift conclusions are anchored to `v8_2_0`** and are
unaffected by upstream master moving. Master is used only as supplementary evidence for post-release changes.

## Re-verification of the central finding

Run 2 re-derived the ancestry finding independently rather than trusting run 1:

```
git merge-base --is-ancestor HEAD upstream/master   -> true
git rev-list --count upstream/master..HEAD          -> 0        (zero fork-only commits)
git rev-list --count HEAD..upstream/master          -> 373
git rev-list --count v8_2_0..HEAD                   -> 17
git merge-base HEAD upstream/master                 -> 631f2b38…  (== HEAD)
git merge-base HEAD v8_2_0                          -> b91c12ae…
```

**Confirmed: the fork has zero commits of its own.**

### On the shallow clone

The clone is shallow (`git rev-parse --is-shallow-repository` → `true`). This does **not** weaken the
ancestry finding: `merge-base` resolves to HEAD itself and the `upstream/master..HEAD` range is empty, both
computed from refs present locally. It *does* limit deep historical archaeology (e.g. full blame across all
history), which no conclusion in this package depends on.

## Working tree at run 2

Dirty, pre-existing, and untouched by the audit:

- deleted: `Documentation/EHI_Export/docs/diagrams/tables/lists_medication.2degrees.dot`
- modified: `sites/default/sqlconf.php` — **contains local DB credentials; never read into any artifact**
- untracked: `Documentation/EHI_Export/.../lists_medication.2degrees.docx`, `SETUP-STATUS.md`,
  `docs/00-discovery/`, `docs/discovery/`, `fix-docker-virtualization.ps1`, `tools/discovery/`

No pre-existing changed or untracked file was edited, staged, stashed, reset or removed. Audit output was
confined to `docs/discovery/openemr-decision-evidence/` and `tools/discovery/openemr-decision-evidence/`.

## Method note carried into every run-2 citation

All run-2 repository searches use `git grep <pattern> HEAD` (packfile reads, ~1s) rather than filesystem
walks (minutes, frequently timing out on this Drive mount). A side benefit is that every citation is pinned
to an exact commit, as the evidence schema requires. Consequently **all run-2 line numbers are HEAD line
numbers**, and are valid for the working tree too, since no cited file is locally modified.
