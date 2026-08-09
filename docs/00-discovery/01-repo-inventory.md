# Phase 0 — Repository Inventory & Identity

## Fork Identity

| Item | Value | Evidence |
|------|-------|----------|
| Git remote (origin) | `https://github.com/mohammedfouly1/openemr` (fetch & push) | `git remote -v` |
| Upstream remote (`openemr/openemr`) | **NOT configured** | Only `remote.origin.*` in `git config`; `git remote` returns only `origin` |
| Current branch | `master` (tracking `origin/master`, up to date) | `git status`, `git branch --show-current` |
| HEAD commit | `631f2b38cf633769c305233f88cdf9c73ca80657` | `git log -1` |
| HEAD commit date | 2026-07-04 04:57:07 +0000 | `git log -1 --format=%ci` |
| HEAD commit subject | `refactor(oe-module-faxsms): Fix SignalWire fax support in oe-module-faxsms (#12526)` | `git log -1` |
| Total commits in history | 12,803 | `git rev-list --count HEAD` |
| Oldest commit in history | `7080e87b` — 2005-03-09 | `git log --reverse -1` (full OpenEMR history preserved) |
| Distance behind upstream | **UNKNOWN — cannot be computed without upstream remote.** _Recommendation_: add `upstream` remote pointing at `https://github.com/openemr/openemr.git` and run `git fetch upstream && git log --oneline HEAD..upstream/master`. **Not executed here — outside read-only mandate; requires operator approval.** | n/a |

## OpenEMR Version (Pinned)

Read verbatim from `version.php:18-42`:

| Variable | Value | Meaning |
|----------|-------|---------|
| `$v_major` | `8` | major version |
| `$v_minor` | `3` | minor version |
| `$v_patch` | `0` | release version (comment notes this is a misnomer for release) |
| `$v_tag` | `-dev` | non-empty ⇒ **this is a pre-release / development snapshot, NOT a tagged production release** |
| `$v_realpatch` | `0` | true patch counter (0 = no post-release patches applied) |
| `$v_database` | `541` | schema version — every DB upgrade file targets this progression |
| `$v_acl` | `13` | ACL schema version |
| `$v_js_includes` | `82` | cache-buster for JS/CSS (dev env uses `md5(microtime())` instead) |

**Effective version string: `OpenEMR 8.3.0-dev` (database schema v541, ACL v13).** No stand-alone `VERSION` file exists at the repo root.

> **Critical for Document 0:** This fork is pinned to an *unreleased* `8.3.0-dev` HEAD, not to a tagged release like `v7.0.3.x`. Every downstream lock (PHP range, MariaDB range, FHIR resources, API surface) must be qualified as "as of commit `631f2b38` on 2026-07-04". Any subsequent upstream rebase can move these locks.

## Working-Tree Cleanliness

`git status` (verbatim):

```
On branch master
Your branch is up to date with 'origin/master'.

Untracked files:
  SETUP-STATUS.md
  docs/00-discovery/
  fix-docker-virtualization.ps1

nothing added to commit but untracked files present
```

`git diff --stat HEAD` returns empty. **No tracked file has been modified.** The only local artifacts are three untracked items:

| Path | Nature | Action recommended |
|------|--------|--------------------|
| `SETUP-STATUS.md` | Scratch note from a prior Windows/Docker setup session; self-declared disposable (see its own final note). Not part of upstream. | Retain during audit; can be deleted or moved into `docs/local/` later. |
| `fix-docker-virtualization.ps1` | Local PowerShell helper (not inspected here). Presumed related to Docker Desktop / WSL2 setup on this host. | Retain, treat as local dev tooling; do NOT commit to fork. |
| `docs/00-discovery/` | Created by THIS audit run. Contains the reports being produced. | Intended output. |

**Conclusion: the fork's tracked source matches `origin/master` exactly.** No prior in-repo customizations to inventory.

## Repository Size

| Metric | Value | Source |
|--------|-------|--------|
| Tracked files (git-managed) | 8,745 | `git ls-files` |
| Total on-disk files (all, incl. untracked & `.git`) | 8,951 | `Get-ChildItem -Recurse -File -Force` |
| Total on-disk size | ~955 MB | same enumeration (`Length` sum). Includes `.git` history. |
| `vendor/` | present but **empty** | dir exists; 0 files (no `composer install` on host) |
| `node_modules/` | present but **empty** | dir exists; 0 files (no `npm install` on host) |
| `composer.lock` | present | tracked |
| `package-lock.json` | present | tracked |

### File count by extension (top 15)

| Count | Extension | Notes |
|------:|-----------|-------|
| 4,642 | `.php` | Bulk of the codebase |
| 523 | `.html` | Includes Smarty-legacy `.html` templates (see Phase 1) |
| 504 | `.svg` | Icons/artwork |
| 480 | `.dot` | Graphviz — likely diagrams under `Documentation/` |
| 425 | `.js` | Frontend JS (mix of AngularJS 1.x, jQuery, vanilla — Phase 1/8) |
| 359 | `.png` | Images |
| 284 | `.twig` | Modern template layer |
| 172 | `.css` | Stylesheets |
| 122 | `.yml` | CI, Docker, config |
| 116 | _(no ext)_ | Docs, LICENSE, config |
| 115 | `.gif` | Legacy imagery |
| 105 | `.md` | Documentation |
| 104 | `.mustache` | Third-party template files (likely bundled JS libs) |
| 100 | `.sql` | Schema + upgrade files |
| 81 | `.json` | Configs, i18n, dependency manifests |

**PHP:Twig ratio ≈ 16:1** — quantifies the legacy-PHP-echo vs modern-Twig imbalance flagged in Phase 1. (Refined in `02-tech-stack.md`.)

### Top-level directories, ranked by file count

| Files | Directory | Purpose (elaborated in Phase 2) |
|------:|-----------|---------------------------------|
| 2,127 | `src/` | Modern PSR-4 `OpenEMR\` namespace |
| 1,967 | `interface/` | Legacy web UI controllers + modules |
| 1,726 | `Documentation/` | Bundled reference docs (large) |
| 775 | `tests/` | Test suites |
| 617 | `library/` | Legacy procedural PHP |
| 243 | `templates/` | Smarty/Twig templates |
| 230 | `docker/` | Docker stacks |
| 198 | `portal/` | Patient portal (separate app) |
| 184 | `.phpstan/` | Static-analysis config/baselines |
| 139 | `gacl/` | Legacy php-gacl ACL library |
| 133 | `ccdaservice/` | Node.js CCDA microservice |
| 89 | `public/` | Web-exposed static assets |
| 87 | `.github/` | Workflows |
| 63 | `contrib/` | Contributed content (forms, code sets, utilities) |
| 46 | `sql/` | Schema + upgrade scripts |
| 43 | `sites/` | **Multisite** roots (Phase 9) |
| 33 | `.git/` (surface count only) | VCS |
| 32 | `ccr/` | Continuity of Care Record exports |
| 21 | `custom/` | Sanctioned customization dir |
| 19 | `tools/` | Dev tooling |
| 19 | `swagger/` | OpenAPI docs (see Phase 5) |
| 10 | `controllers/` | Legacy MVC controllers |
| 9 | `config/` | App config |
| 6 | `bin/` | CLI entry points |
| 5 | `apis/` | (Small) — verify in Phase 5 |
| 4 | `sphere/` | Unknown — investigate in Phase 2 |
| 4 | `db/` | Unknown — investigate Phase 2/3 |
| 3 | `docs/` | Pre-existing OpenEMR docs |
| 2 | `webpack/` | Webpack config |
| 2 | `oauth2/` | OAuth2 keys/config (Phase 4) |
| 2 | `scripts/` | Misc |
| 2 | `meta/` | Unknown |
| 1 | `.claude/` | Claude tooling |
| 0 | `vendor/`, `node_modules/`, `.webpack-cache/`, `tmp/`, `tmp-phpstan/` | Empty / not populated on host |

## Fork-vs-Upstream Assessment (Preliminary)

- **The fork's tracked tree is byte-identical to its own `origin/master` on GitHub.** No divergence from `mohammedfouly1/openemr` HEAD.
- **Distance from `openemr/openemr` (canonical upstream) is unknown** because no `upstream` remote is configured. This matters for the "upgrade & patch strategy" locked decision (Phase 14). **Recommendation to operator (do not execute without approval):**
  ```
  git remote add upstream https://github.com/openemr/openemr.git
  git fetch upstream --tags
  git log --oneline HEAD..upstream/master | wc -l   # commits ahead of us
  git log --oneline upstream/master..HEAD | wc -l   # our-fork-only commits
  ```
  This will be tracked as an open question in `SUMMARY-open-questions.md`.
