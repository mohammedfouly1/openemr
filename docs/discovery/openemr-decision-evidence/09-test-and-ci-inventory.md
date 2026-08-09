# 09 — Tests & CI Inventory

_Auditor: opencode. Mode: READ-ONLY. Output confined to `docs/discovery/openemr-decision-evidence/` and `tools/discovery/openemr-decision-evidence/`. Baseline: FORK_SHA `631f2b38…` is an ancestor of `upstream/master` `608f9ae3…`, so all CI/workflow drift vs upstream is upstream-ahead-of-fork (per `02-repository-baseline.md`)._

## 1. Executive

- **PHPUnit configs:** 3 tracked (`phpunit.xml`, `phpunit-isolated.xml`, `phpunit.integration.xml`), all schema `11.5`. Only two are wired to CI; `phpunit.integration.xml` is **vestigial** — its target directory `tests/Tests/Integration/` does not exist on disk (0 tracked files) and no workflow / composer / npm / devtools alias invokes it.
- **Isolated suite (Q75): YES**, run in CI — `.github/workflows/isolated-tests.yml:50` executes `vendor/bin/phpunit -c phpunit-isolated.xml` across PHP 8.2/8.3/8.4/8.5/8.6 on every push and PR to `master`/`rel-*`; uploads Clover coverage + JUnit results to Codecov (flags `isolated-php<ver>`).
- **Coverage (Q51):** Codecov's public v2 API returned live master coverage of **27.53%** (files=3950, lines=422697, hits=116390) at 2026-07-21T07:07:16Z — this is the actual current number, materially higher than the ~4% historical baseline mentioned in `codecov.yml:24`. CI wires coverage uploads at 6 codecov-action call-sites across 4 workflows.
- **DB strategy (Q52):** Two divergent patterns coexist. `integration-tests.yml` uses **GitHub-native `services:` containers** (fresh MySQL/MariaDB per matrix arm, ports `3306:3306`) — genuinely fresh per job. `test.yml` (invoked by `test-all.yml` and `test-scheduled.yml`) uses **compose-stack DBs** (mariadb / mysql from `ci/compose-shared-*.yml`) — the `setup` job installs OpenEMR, dumps DB via `dump_database > db-dump.sql`, and each parallel `test` matrix job **restores** that snapshot before running its suite. So: fresh service container per matrix arm, but same snapshot restored to it across suites within one arm.
- **Panther/ChromeDriver (Q53):** `symfony/panther ^2.0` in `composer.json:159`; Selenium standalone-chromium `149.0.7827.155` (SHA-pinned) is defined in `ci/compose-shared-selenium/docker-compose.yml:2-3` and consumed by every apache/nginx CI config; drive URL `http://selenium:4444/wd/hub` (`tests/Tests/E2e/Base/BaseTrait.php:26,37,72`). Local ChromeDriver fallback exists (`BaseTrait.php:79 static::createPantherClient()` when `SELENIUM_USE_GRID != true`) and is theoretically viable on Windows if `chromedriver.exe` is on `PATH`, but requires PHP + `chromedriver` + Chrome installed on the host — not exercised by any workflow.

## 2. PHPUnit config landscape

Full inventory: `evidence/manifests/phpunit-config-inventory.csv` (3 rows).

| Config | Bootstrap | Requires DB? | Requires browser? | Invoked by | Line count |
|---|---|---|---|---|---:|
| `phpunit.xml` | `tests/bootstrap.php` (`phpunit.xml:14`) → `interface/globals.php` (`bootstrap.php:32`) → ADODB via `sites/default/sqlconf.php` | **YES** | **YES** (e2e suite; `phpunit.xml:38` sets `PANTHER_WEB_SERVER_DIR`) | `test.yml` (via `ci/ciLibrary.source build_test`), `test-frontcontroller.yml`, `integration-tests.yml`, `.github/actions/test-actions-core/action.yml` | 117 |
| `phpunit-isolated.xml` | none (safety-net Extension only at `:32`) | **NO** | **NO** | `isolated-tests.yml:50` (direct `-c` invocation) | 78 |
| `phpunit.integration.xml` | none | (unknowable — 0 tests would resolve) | Config sets `PANTHER_WEB_SERVER_DIR` at `:27` but suites are empty | **NONE** | 63 |

All three register the same safety-net PHPUnit extension `OpenEMR\PHPUnit\Extension` (`phpunit.xml:33`, `phpunit-isolated.xml:32`, `phpunit.integration.xml:22`). `tests/bootstrap.php:22-28` aborts with exit 70 if the extension did not bootstrap.

## 3. Q74 — Integration test tree

**Direct answer: VESTIGIAL. `tests/Tests/Integration/` does not exist.**

Evidence (`evidence/raw/integration-test-tree-manifest.txt`):

```
git ls-files "tests/Tests/Integration/**"   -> 0 rows
Test-Path tests\Tests\Integration           -> False
Test-Path tests\Tests\Integration\Api       -> False
Test-Path tests\Tests\Integration\RestControllers -> False
```

`phpunit.integration.xml:34,37` references two directories, both nonexistent:

```
<directory>tests/Tests/Integration/Api/</directory>
<directory>tests/Tests/Integration/RestControllers/Subscriber/</directory>
```

Cross-tree invocation check for `phpunit.integration`:

- `.github/workflows/*.yml`: **0 hits**
- `composer.json` scripts: **0 hits** (only `phpunit-isolated` script key exists at `composer.json:306`)
- `package.json`: **0 hits**
- `docker/flex/utilities/devtools`: **0 hits** (no `phpunit-integration` alias — `pi` maps to `phpunit-isolated`)
- `bin/`, `tools/`: **0 hits**

The most recent commit touching the config file is `2026-05-26 a3f8159514 test: exclude non-logic dirs from phpunit coverage (#12268)` — a coverage-scope edit, not a directory revival. The tests-directory-flavored refactor commit `2026-04-15 6cd0ba6da chore(testing): move database-independent tests to isolated suite (#11637)` is the plausible point at which the Integration/ tree was either drained into `Isolated/` or simply never populated.

Files with the substring "Integration" in the name (11 `*IntegrationTest.php` + 1 `.test.js`) all live under `tests/Tests/{Common,RestControllers,Services}/` — that is a **naming convention**, not an occupant of the vestigial `Integration/` config. Those files run under `phpunit.xml`'s existing `common`/`controllers`/`services` suites.

**Recommendation for follow-up (not for this audit):** delete `phpunit.integration.xml`; nothing consumes it.

## 4. Q75 — Isolated tests in CI

**Direct answer: YES.** Executed by `.github/workflows/isolated-tests.yml`.

| Field | Value |
|---|---|
| Workflow name | `Isolated Tests` (`isolated-tests.yml:7`) |
| Triggers | `push` to `master`/`rel-*`, `pull_request` to `master`/`rel-*` (`isolated-tests.yml:10-18`) |
| PHP matrix | `8.2, 8.3, 8.4, 8.5, 8.6` (`isolated-tests.yml:30-35`) — **broader than any other suite** (only isolated tests PHP 8.6) |
| Runner OS | `ubuntu-24.04` (`:26`) |
| Setup action | `.github/actions/setup-php-composer` (`:43`) with `coverage: xdebug` |
| Exact command | `vendor/bin/phpunit -c phpunit-isolated.xml --coverage-clover=clover.xml --log-junit=junit.xml` (`:50-52`) |
| Coverage upload | `codecov-action@v7` files=`clover.xml` flags=`isolated-php<ver>` (`:76-82`) |
| Test-results upload | `codecov-action@v7` files=`junit.xml` `report_type=test_results` flags=`isolated-php<ver>` (`:54-61`) |
| JUnit artifact | `actions/upload-artifact@v7` name=`junit-isolated-tests-php-<ver>` (`:63-68`) |
| Failure blocking | Standard `runs: ...` step (no `continue-on-error`) — failure blocks the workflow. `all-checks-passed.yml` (required-check aggregator, `.github/workflows/all-checks-passed.yml:19` uses `GITHUB_TOKEN`) waits on this workflow. |
| README badge | Not audited here (README rendering is external); grep of repo root README files shows badges exist but no specific "Isolated Tests" badge could be located in `README.md`. Marked as UNKNOWN. |

**Fork-vs-upstream parity for isolated-tests.yml:** `git diff --name-only HEAD upstream/master -- .github/workflows/` lists 20 changed workflow files; `isolated-tests.yml` is **not** in that list, so the fork uses byte-identical config to upstream master for this workflow (as expected given ancestor status — the 20 diffs are all upstream-added/modified after fork HEAD).

## 5. Q51 — Coverage

**Direct answer:** Codecov v2 API returns **27.53%** for `openemr/openemr:master` at 2026-07-21T07:07:16Z (files=3950, lines=422697, hits=116390, misses=306307). This is a **live** number, not scraped from a badge.

`codecov.yml` (29 lines) configures:

- **Ignores** (`:1-11`): `interface/modules/custom_modules/oe-module-claimrev-connect/`, `config/`, `tests/PHPStan/`, `tests/Rector/`, `tests/eventdispatcher/`, `portal/messaging/messages.php`, `portal/messaging/handle_note.php`
- **Path fixes** (`:15-18`): strip `/var/www/localhost/htdocs/openemr/`, `/usr/share/nginx/html/openemr/`, `/home/runner/work/openemr/openemr/`
- **Project status target** (`:23-25`): `auto` with 1% threshold (comment: "Use current coverage as floor — no regression allowed (was 4%)")
- **Patch status target** (`:26-29`): `50%` with 5% threshold (comment: "was 0%")

Coverage upload points enumerated from workflow grep (`codecov-action` = 6 call-sites across 4 workflows):

| Workflow | Line | Coverage flag pattern |
|---|---|---|
| `isolated-tests.yml` | `:76-82` | `isolated-php<ver>` |
| `integration-tests.yml` | `:102-106` | (default — no explicit flag) |
| `test.yml` | `:641-647` | `<suite>,php<ver>,<webserver>,<db><dbver>[,upgrade]` |
| `test.yml` | `:651-657` | `<suite>-tests,...` (API/E2E test-file coverage) |
| `test-frontcontroller.yml` | `:91-93` | (default) |
| `inferno-test.yml` | `:340,353,365` | `inferno,phpunit,php<ver>` etc |

Coverage is collected for **one** matrix arm on PRs (`apache_82_118` — `test-all.yml:126`) and one on push (`apache_85_118`); full-matrix coverage runs in `test-scheduled.yml` nightly at 09:00 UTC. Isolated tests upload coverage on every arm.

## 6. Q52 — CI DB strategy

**Direct answer: fresh-per-job**, with two distinct mechanisms:

### 6a. GitHub-native service containers (`integration-tests.yml`)

`integration-tests.yml:63-78` uses `services: mysql:` with a matrix of 8 DB images (MySQL 5.7/8.4/9.7, MariaDB 10.6/10.11/11.4/11.8/12) × 4 PHP versions. Each matrix arm gets a **new container** at job start (GitHub Actions default); DB is created fresh, `./cli install` runs (`:91`), then `vendor/bin/phpunit --testsuite common,controllers,fixtures,validators,unit` (`:100`). No inter-job persistence — each matrix cell is a self-contained VM + fresh DB.

### 6b. Compose-stack setup+dump / test-matrix restore (`test.yml` reusable → `test-all.yml`, `test-scheduled.yml`)

`test.yml` splits into `setup` and `test` jobs:

- **`setup`** (`:81-464`): starts the docker-compose stack (`dockers_env_start`), installs OpenEMR (`install_configure`), dumps DB via `dump_database > db-dump.sql` (`:444-448`), uploads `db-dump.sql` + `sqlconf.php` as an artifact.
- **`test`** (`:466-799`, matrix on `suite`): a **new runner VM** downloads the setup artifact, chmods, brings up the compose stack fresh (`dockers_env_start`, `:560-566`), imports the DB snapshot (`import_database < db-dump.sql`, `:568-573`), then runs its assigned suite. Because each matrix arm is a new VM with a freshly-built stack that imports the same snapshot, **the DB state is identical at test start**, and no cross-suite pollution is possible within a docker-dir arm.

Across docker-dir arms (`test-all.yml` fans out to all 21 `ci/*` configs), each is a fully isolated pipeline. So: **fresh per job in both senses** — fresh service container per matrix arm, fresh restored snapshot per suite within an arm.

## 7. Q53 — Panther / ChromeDriver

Locations of every relevant reference:

- **composer.json:159**: `"symfony/panther": "^2.0"`
- **tests/Tests/E2e/Base/BaseTrait.php:26,37,72-73**: `Symfony\Component\Panther\Client::createSeleniumClient("http://{$seleniumHost}:4444/wd/hub", $capabilities, $e2eBaseUrl)` — default `$seleniumHost = "selenium"`, `$e2eBaseUrl = "http://openemr"`
- **tests/Tests/E2e/Base/BaseTrait.php:79**: `static::createPantherClient()` — local ChromeDriver fallback when `SELENIUM_USE_GRID != "true"`
- **tests/Tests/E2e/KkEncounterFormNavbarUrlTest.php:119-137**: same pattern, inlined
- **ci/compose-shared-selenium/docker-compose.yml:2-3**: `selenium/standalone-chromium:149.0.7827.155@sha256:9b10a9…` — the shared image used by all 20 apache/nginx CI configs (grep hits: 20 `selenium-template:` inclusions across `ci/*/docker-compose.yml`)
- **ci/compose-shared-selenium/docker-compose.yml:28**: `selenium/video:ffmpeg-7.1-20250808` for E2E video recording (uploaded on failure by `test.yml:769-774`)
- **docker/development-easy/docker-compose.yml:126-145**, **development-easy-redis/docker-compose.yml:239**, **development-insane/docker-compose.yml:830**: local dev selenium services (same image pin)
- **.github/workflows/test.yml:674-711**: selenium failure-diagnostics capture (container state, `dc top selenium`, `dmesg` grep for oom/segfault/chrome, `dc logs selenium`)
- **.github/dependabot.yml:143-158,161,266,268,313,315**: dedicated update group for `selenium/*` images
- **.github/actions/setup-php-composer/action.yml**, **integration-tests.yml**: **no** Panther/Selenium reference — integration-tests.yml intentionally excludes browser suites (per `test.yml:126-128` comment "suites common,controllers,fixtures,validators,unit are covered by integration-tests.yml"; browser-based `e2e`/`api` run only in `test.yml`).

**Windows-host fallback viability (BaseTrait.php:79 branch):**

- `createPantherClient()` needs a `chromedriver` binary discoverable via `PANTHER_CHROME_DRIVER_BINARY` env var or PATH, plus Chrome/Chromium.
- Composer/PHP are **not** installed on this Windows host per `02-repository-baseline.md:64-66`, and neither is `chromedriver.exe`. So the fallback is theoretically usable (Panther ships Windows binaries via `dbrekelmans/browser-driver-installer` on `composer install`) but currently unexercised — verifying it works on Windows requires a live run.
- No CI workflow uses the Windows fallback path — every CI E2E arm sets `SELENIUM_USE_GRID=true` (implicit via `SELENIUM_HOST=selenium`), so the fallback is dev-only.
- **Verdict on Windows fallback:** LOW-CONFIDENCE viable. Requires: (a) `composer install` on Windows host with Panther's driver installer running successfully, (b) Chrome present, (c) `PANTHER_NO_HEADLESS` handling. Not tested here.

## 8. Workflow catalog

Full CSV: `evidence/manifests/github-workflows-inventory.csv` — **59 rows** (was 57 in `11-devops-docker-ci.md §4.1`; the 2 extra are upstream additions after fork HEAD — see §9).

Highlights (from CSV):

- **Total workflows:** 59
- **Reusable (`workflow_call`):** `test.yml`, `claude-review-reusable.yml`, plus release-orchestration reusables
- **PHPUnit-invoking:**
  - `phpunit-isolated.xml` — **1 workflow** (`isolated-tests.yml`)
  - `phpunit.integration.xml` — **0 workflows**
  - `phpunit.xml` (implicitly via `vendor/bin/phpunit` with `--testsuite`) — `isolated-tests.yml`, `integration-tests.yml`, `test.yml`, `test-frontcontroller.yml`, `test-actions-core` composite action
- **Codecov upload:** 4 workflows (`isolated-tests.yml`, `integration-tests.yml`, `test.yml`, `test-frontcontroller.yml`, `inferno-test.yml`)
- **Secret names referenced:** `CODECOV_TOKEN`, `DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN`, `GITHUB_TOKEN`, `RELEASE_APP_PRIVATE_KEY`, `AUTO_MERGE_APP_PRIVATE_KEY`, `ANTHROPIC_API_KEY`, `INFERNO_PAT`, `RESERVED_WORD_BOT_PRIVATE_KEY` (values never printed)

## 9. Fork-vs-upstream CI parity

Per baseline, `merge-base(HEAD, upstream/master) == HEAD` → fork is ancestor of upstream. Diff evidence:

```
git diff --name-only HEAD upstream/master -- .github/workflows/                      -> 20 files
git diff --name-only HEAD upstream/master -- .github/workflows/ .github/actions/ ci/ tests/  -> 105 files
git diff --stat HEAD upstream/master -- .github/workflows/ .github/actions/ ci/ tests/       -> 105 files changed, 29942 insertions(+), 276 deletions(-)
```

All 105 changed paths are upstream-added or upstream-modified after fork HEAD (2026-07-04). Sample of the 20 workflow diffs:

```
M  .github/workflows/all-checks-passed.yml
A  .github/workflows/build-patch.yml               (new upstream)
A  .github/workflows/build-release-on-tag.yml      (new upstream)
A  .github/workflows/build-release.yml             (new upstream)
M  .github/workflows/claude-review-reusable.yml
M  .github/workflows/docker-build-release.yml
M  .github/workflows/docker-test-release.yml
M  .github/workflows/docker-validate-release-targets.yml
```

The 3 `A` (added) entries account for the 59 vs 57 workflow-count discrepancy vs `11-devops-docker-ci.md §4.1` (which was taken from a slightly earlier snapshot mentioning 57). Confirmed: **the fork made zero fork-only changes to CI infrastructure** — every difference is upstream-ahead-of-fork, so upstream sync will be a straight merge with no CI conflict surface.

## 10. UNKNOWNs

1. **README badge for isolated tests.** Whether the top-level `README.md` renders an Isolated Tests status badge that would block the workflow's removal — not enumerated here.
2. **Windows Panther fallback works in practice.** `BaseTrait.php:79 static::createPantherClient()` compiles and Panther ships Windows chromedriver installers, but no CI validates the Windows path. Requires a live `composer install` + PHPUnit run on this host to prove.
3. **Whether `tests/js/` (4 Jest files) coverage is uploaded to Codecov.** `js-test.yml` runs `npm run test:js` (no `--coverage`); Codecov's coverage number of 27.53% is therefore PHP-only. Confirmed by inspection but noted as a subtle gap for anyone assuming the number covers JS.
4. **Numeric coverage attribution across flags.** Codecov v2 API returns aggregate coverage; per-flag breakdown (isolated vs api vs e2e vs unit) would require an authenticated Codecov API call and is not attempted. Marked EVIDENCE-BLOCKED-EXTERNAL for per-flag breakdowns.
5. **Whether any composer script other than `phpunit-isolated` invokes a PHPUnit config file by name.** Only `phpunit-isolated` and `update-twig-fixtures` (which also targets `phpunit-isolated.xml`) do — but the CI shells that source `ci/ciLibrary.source` may set `--configuration` implicitly via `build_test`. Not fully traced.

---

**Output produced by this section:**

- `docs/discovery/openemr-decision-evidence/09-test-and-ci-inventory.md` (this file)
- `docs/discovery/openemr-decision-evidence/evidence/manifests/phpunit-config-inventory.csv` (3 rows)
- `docs/discovery/openemr-decision-evidence/evidence/manifests/github-workflows-inventory.csv` (59 rows)
- `docs/discovery/openemr-decision-evidence/evidence/raw/integration-test-tree-manifest.txt` (0 tracked files under target dir; documented as vestigial)
- `docs/discovery/openemr-decision-evidence/evidence/raw/isolated-test-tree-manifest.txt` (317 tracked files)
- `tools/discovery/openemr-decision-evidence/build-workflows-inventory.py` (generator)
- Appended to `docs/discovery/openemr-decision-evidence/22-command-log.txt`
