# 11 — DevOps: Docker & CI

Read-only audit of `docker/`, `.github/`, and `ci/`. Sibling of
`00-environment.md` and `01-repo-inventory.md`.

Counts from `git ls-files`: `docker/` = 230, `.github/` = 87 (57 workflows),
`ci/` = 45.

---

## 1. `docker/` inventory

### 1.1 Subdirectory purpose

Source of truth: `docker/README.md:1-40`.

| Path | Kind | Purpose |
|---|---|---|
| `docker/release/` | Dockerfile (published) | Production image `openemr/openemr:<version>` / `:latest` / `:next` / `:dev`. Per-branch; bakes OpenEMR source at build time. `docker/release/Dockerfile:1-30` |
| `docker/flex/` | Dockerfile (published) | Dev/test image `openemr/openemr:flex*`. Master-only; fetches source at container start via `FLEX_REPOSITORY*` env. `docker/flex/Dockerfile:1-24` |
| `docker/binary/` | Dockerfile (published) | Offline/appliance builds; publishes specific binary tags. `docker/binary/Dockerfile` |
| `docker/production/` | Compose (consumer) | Example prod stack (amd64 + arm64). `docker/production/docker-compose.yml` |
| `docker/development-easy/` | Compose (consumer) | Primary contributor dev stack, driven by `openemr-cmd`. `docker/development-easy/docker-compose.yml` |
| `docker/development-easy-light/` | Compose | Minimal easy stack (mariadb+openemr+phpmyadmin only) |
| `docker/development-easy-redis/` | Compose | Easy stack + Redis master/replicas for session/cache testing |
| `docker/development-insane/` | Compose | Kitchen-sink multi-PHP/multi-DB matrix (PHP 8.1–8.6, mysql 5.7/8.0/8.4, mariadb 10.6/10.11/11.4/11.8, nginx+fpm variants, redis, orthanc, ibm-fhir, ldap). `docker/development-insane/docker-compose.yml:1-70` |
| `docker/container_benchmarking/` | Shell + compose | Benchmark harness for container startup/build |
| `docker/dockerhub/` | Docs generator | Renders Docker Hub README from templates; has golden tests |
| `docker/library/` | Shared assets | SSL cert bundles (`sql-ssl-certs-keys/`, `couchdb-config-ssl-cert-keys/`, `ldap-ssl-certs-keys/`), plus `dockers/dev-nginx/`, `dockers/dev-php-fpm-8-{1..6}[-redis]/` — the images consumed by insane stack |

Legacy `common/` and `production/` k8s directories are **absent**. See §4.

### 1.2 Per-stack breakdown

#### `docker/production/docker-compose.yml` (63 lines)

| Service | Image | Ports | Volumes | Notes |
|---|---|---|---|---|
| `mysql` | `mariadb:11.8.8` (sha pinned) `:8` | none published | `databasevolume:/var/lib/mysql` | root pw `root`; SSL disabled |
| `openemr` | `openemr/openemr:latest` (sha pinned) `:28` | `80:80`, `443:443` | `logvolume01:/var/log`, `sitevolume:/…/sites` | `MYSQL_HOST=mysql`, `MYSQL_ROOT_PASS=root`, `MYSQL_USER/PASS=openemr`, `OE_USER=admin`, `OE_PASS=pass` `:36-41` |

Two named volumes. No phpmyadmin, no Redis, no reverse proxy. Health-check hits `https://localhost/meta/health/readyz` `:54`.

#### `docker/development-easy/docker-compose.yml` (211 lines)

| Service | Image | Ports (default) | Purpose |
|---|---|---|---|
| `mysql` | `mariadb:11.8.8` | `8320:3306` | SSL-enabled via bind-mounted certs `:9-11` |
| `openemr` | `openemr/openemr:flex` | `8300:80`, `9300:443` | Bind-mounts entire repo at `/openemr:ro` **and** `/var/www/localhost/htdocs/openemr:rw` `:34-35`; named volumes for `vendor/`, `node_modules`, `sites/`, `public/assets`, `public/themes`, phpstan cache, webpack cache, `/var/log`, couchdb data |
| `selenium` | `selenium/standalone-chromium:149.0.7827.155` | `4444:4444`, `7900:7900` (VNC) | Test harness `:126-145` |
| `phpmyadmin` | `phpmyadmin:5.2.3` | `8310:80` | `:146-155` |
| `couchdb` | `couchdb:3.5.2` | `5984`, `6984` | For CCDA / example dbase `:156-170` |
| `openldap` | `openemr/dev-ldap:easy` | none | LDAP auth testing `:171-178` |
| `mailpit` | `axllent/mailpit:v1.30.3` | `8025:8025` (UI), `1025:1025` (SMTP) | SMTP capture `:179-197` |

All ports overridable via `WT_*` env vars for `openemr-cmd worktree` port offsets (e.g. `WT_HTTP_PORT`, `WT_MYSQL_PORT` `:7,31`).

`HOST_UID`/`HOST_GID` passthrough `:57-58` — entrypoint adopts host uid to keep bind-mount writes host-owned. Docs at `docker/development-easy/docker-compose.yml:48-56`.

#### `docker/development-insane/docker-compose.yml` (871 lines)

Matrix stack — 6 apache-mode openemr containers (PHP 8.1/8.2/8.3/8.4/8.5/edge, ports 8083–8088 / 9083–9088), 6 redis variants (8093–8098), 12 nginx+php-fpm variants via one shared `nginx` service (ports 8103–8158), plus:
- `mariadb`/`mariadb-ssl`/`mariadb-old` (11.4)/`mariadb-very-old` (10.11)/`mariadb-very-very-old` (10.6) `:501-596`
- `mysql` (8.4)/`mysql-old` (8.0)/`mysql-old-old` (5.7) `:597-643`
- `phpmyadmin` `:644-650`, `couchdb` `:651-665`, `orthanc/orthanc-plugins:1.12.11` (DICOM) `:666-671`
- `redis:8.8.0` `:814-816`, `openldap` `:817-824`, `ibmcom/ibm-fhir-server:4.11.1` `:825-829`, `selenium` `:830-849`, `mailpit` `:850-868`

Only `openemr-8-5` gets full auto-config env; the rest boot `EMPTY: yes` for manual install testing `:87,215`.

#### `docker/development-easy-light/docker-compose.yml`

Trimmed easy stack: mariadb + openemr (flex) + phpmyadmin only. Same image pins as `development-easy/`.

#### `docker/development-easy-redis/docker-compose.yml`

Easy stack + Redis Sentinel (redis-master + 2 replicas), all `redis:8.8.0`. Password `strongpassword`. Also has a `php.ini` override.

### 1.3 PHP / DB versions

| Version-carrying artifact | Value |
|---|---|
| flex image default | Alpine 3.23 + PHP 8.5 (`docker/flex/Dockerfile:30-36`) |
| release image default | Alpine 3.23 + PHP 8.5 (`docker/release/Dockerfile:23-30`) |
| production compose DB | MariaDB 11.8.8 (`docker/production/docker-compose.yml:8`) |
| dev-easy DB | MariaDB 11.8.8 (`docker/development-easy/docker-compose.yml:4`) |
| insane matrix DBs | mariadb 10.6.27 / 10.11.16 / 11.4.10 / 11.8.6; mysql 5.7.44 / 8.0.45 / 8.4.8 |
| insane matrix PHP | 8.1 / 8.2 / 8.3 / 8.4 / 8.5 / edge (via `flex-3.17`, `flex-3.22-php-8.2`, `flex-3.23-php-8.{3,4,5}`, `flex-edge`) |

---

## 2. Dockerfiles

Two production Dockerfiles + several dev-only ones under `docker/library/dockers/`.

### 2.1 `docker/flex/Dockerfile` (354 lines)

- **FROM** `alpine:${ALPINE_VERSION:-3.23}` (`:31`), multi-stage `base` → `kcov` → `final` (`:31,308,354`)
- **Args**: `PHP_VERSION=8.5` `:35`, `DEMO_SQL_REPO_SHA` `:272` (pins demo SQL to a specific commit)
- **Key RUN steps**:
  - `apk upgrade` + install dev tools (bash, vim, nano, tree, unzip) `:45-59`
  - Install apache2, certbot, git, imagemagick, mariadb-client, nodejs/npm, `su-exec`, etc. `:72-94`
  - Install PHP + ~30 extensions parameterized by `PHP_VERSION_ABBR` `:108-145`
  - `usermod -u 1000 apache` `:160`
  - `composer` installed via `getcomposer.org/installer` `:171`
  - Fetch demo SQL from `openemr-devops` (SHA-pinned + sha256 verified) `:274-276`
  - Rsync `/etc/ssl` → `/swarm-pieces/` for Docker Swarm bootstrap `:287-290`
- **COPY** `openemr.sh ssl.sh xdebug.sh pcov.sh auto_configure.php` → `/var/www/localhost/htdocs/` `:239`; `utilities/devtools` + `devtoolsLibrary.source` → `/root/` `:262-263`
- **Entrypoint**: `CMD [ "./openemr.sh" ]` `:297`
- **kcov stage** (`:308-347`) installs kcov from source for coverage builds

### 2.2 `docker/release/Dockerfile`

Same skeleton as flex but bakes OpenEMR source at build time. Also `alpine:3.23` + PHP 8.5. Multi-stage with composer/npm build in an intermediate stage (per `docker/flex/Dockerfile:189-193` comment referencing this). Full read not necessary — auto-config surface is identical.

### 2.3 First-boot auto-config surface (release/flex, both use `openemr.sh` + `auto_configure.php`)

From `docker/flex/openemr.sh:22-38, 139-185`:

| Env var | Default | Meaning |
|---|---|---|
| `MYSQL_HOST` | `mysql` | DB hostname (required for install) |
| `MYSQL_PORT` | `3306` | |
| `MYSQL_ROOT_PASS` | `root` | Root pw; `BLANK` = empty |
| `MYSQL_USER` | `openemr` | App DB user (created) |
| `MYSQL_PASS` | `openemr` | App DB pw |
| `MYSQL_DATABASE` | `openemr` | DB name |
| `OE_USER` | `admin` | Initial admin login |
| `OE_USER_NAME` | `Administrator` | Admin display name |
| `OE_PASS` | `pass` | Initial admin pw |
| `FLEX_REPOSITORY` | `https://github.com/openemr/openemr.git` | Source repo (flex only) |
| `FLEX_REPOSITORY_BRANCH` / `FLEX_REPOSITORY_TAG` | `master` / — | Ref to clone |
| `EASY_DEV_MODE` | `no` | Skip permission chown |
| `EASY_DEV_MODE_NEW` | `no` | Use bind-mounted repo, don't clone |
| `INSANE_DEV_MODE` | `no` | Enable devtools |
| `EMPTY` | `no` | Skip auto-install (manual setup) |
| `FORCE_NO_BUILD_MODE` | `no` | Skip composer/npm at boot |
| `DEVELOPER_TOOLS` | `no` | Install extra dev tools |
| `SWARM_MODE` | `no` | Restore `/etc/ssl`, `sites/` from `/swarm-pieces` |
| `GITHUB_COMPOSER_TOKEN[_ENCODED[_ALTERNATE]]` | — | Rate-limit avoidance |
| `REDIS_SERVER`, `REDIS_USERNAME`, `REDIS_PASSWORD` | — | PHP session backend |
| `XDEBUG_ON`, `XDEBUG_CLIENT_HOST` | `no`, — | Debugger |
| `PCOV_ON` | `no` | Coverage (mutually exclusive w/ Xdebug) |
| `HOST_UID`, `HOST_GID` | `1000` | Adopt host uid inside apache |
| `OPENEMR_DOCKER_ENV_TAG` | — | Free-form tag identifying the env |

`auto_configure.php` (`docker/flex/auto_configure.php:63-127`) maps these into `$installSettings`: `iuser`, `iuname`, `iuserpass`, `igroup`, `server`, `loginhost`, `port`, `root`, `rootpass`, `login`, `pass`, `dbname`, `collate=utf8mb4_general_ci`, `site=default`, plus `source_site_id`, `clone_database`, `no_root_db_access`, `development_translations`. Supports `-f "key=value ..."` bundled form `:143-167`; `"BLANK"` sentinel → empty string `:176-181`.

### 2.4 Global settings via `OPENEMR_SETTING_*`

From `docker/flex/utilities/devtoolsLibrary.source:134-164`: any env var of the form `OPENEMR_SETTING_<gl_name>=<value>` triggers a MariaDB `UPDATE globals SET gl_value = '<value>' WHERE gl_name = '<gl_name>'` post-install. **This is the mechanism for automatable tenant provisioning.**

Examples used in `development-easy` `:83-107`: `site_addr_oath`, `oauth_password_grant`, `rest_api`, `rest_fhir_api`, `rest_portal_api`, `couchdb_host/port/user/pass/dbase`, `gbl_ldap_host/dn`, `EMAIL_METHOD`, `SMTP_HOST/PORT/USER/PASS/SECURE/Auth`, `practice_return_email_path`, `Patient_Reminder_Sender_Name`.

### 2.5 Other Dockerfiles

`docker/library/dockers/dev-php-fpm-8-{1..6}[-redis]/Dockerfile` — PHP-FPM sidecars for the insane-nginx variant, published to Docker Hub as `openemr/dev-php-fpm:<ver>[-redis]` and consumed by `development-insane`. `docker/library/dockers/dev-nginx/Dockerfile` — matching nginx front. `docker/library/ldap-ssl-certs-keys/{easy,insane}/Dockerfile` — builds LDAP test images.

---

## 3. Production practices

### 3.1 What the repo actually recommends

`docker/README.md:1-40` and `docker/production/README.md` point consumers at `DOCKER_README.md` (repo root, exists — confirmed). `docker/production/docker-compose.yml` is a **single-node reference stack**: one MariaDB + one openemr, two named volumes, published on 80/443. No reverse proxy, no Redis, no read-replica. It embeds default `root`/`openemr`/`admin`/`pass` credentials `:36-41` — clearly a "you-will-customize-this" template, not a hardened deployment.

### 3.2 Redis / SMTP / LDAP separation

Redis is present in `development-easy-redis/` and every `*_redis*` insane / CI stack (sentinel + TLS variants under `ci/apache_85_118_redis_sentinel*`) — so the **image supports** external Redis via `REDIS_SERVER`, `REDIS_USERNAME`, `REDIS_PASSWORD` (`docker/flex/openemr.sh:180-183`) but the production compose does not wire it. Same for SMTP (mailpit is dev-only) and LDAP.

### 3.3 Kubernetes / Helm

Filesystem search (`Get-ChildItem -Recurse -Directory -Filter Helm*/k8s*/chart*`) returned **zero matches**. There is no `Chart.yaml`, no `values.yaml`, no `kustomization.yaml`, no `k8s/` directory anywhere in the repo. Multi-node orchestration hooks are limited to Docker Swarm (`SWARM_MODE=yes` restores volumes from `/swarm-pieces/` — `docker/flex/Dockerfile:287-290`, `docker/flex/openemr.sh:594-618`).

**Verdict:** No official k8s / Helm artifacts in tree. If we need k8s we build it ourselves (or crib from the sister repo `openemr-devops`, referenced by SHA in `docker/flex/Dockerfile:272`).

---

## 4. `.github/workflows/` (57 files)

### 4.1 Categorized inventory

Names collected from each file's `name:` line.

**(a) CI tests — DB-backed matrix**

| Workflow | Purpose |
|---|---|
| `test.yml` | Reusable workflow: takes a `docker_dir` from `ci/` and runs full test suite (unit/e2e/api/services/…) inside that compose stack. Selectable via `workflow_dispatch` from 21 configs. |
| `test-all.yml` "Test All Configurations" | Fans out `test.yml` across every `ci/apache_*` and `ci/nginx_*` dir on push/PR. On PRs runs only PHP 8.2; `Test-Mode: full` trailer opts into full matrix. |
| `test-scheduled.yml` | Nightly (09:00 UTC) full matrix with coverage on every config |
| `integration-tests.yml` "Integration tests" | Cross-service integration suite |
| `isolated-tests.yml` | DB-free unit tests (fast; matches `openemr-cmd phpunit-isolated`) |
| `js-test.yml` "JS Unit Test" | Jest suite |
| `test-frontcontroller.yml` "Test Front Controller" | Legacy front-controller test |
| `test-byte-identical-scripts.yml` | Validates `sync-byte-identical.sh` outputs |
| `database.yml` / `database-version.yml` | DB schema / version-file checks |
| `inferno-test.yml` "Inferno Certification Test" | ONC (§170) g10 test kit via `ci/inferno/` |
| `check-vendored-contracts.yml` | Vendored-file drift detector |

**(b) Linting**

`composer.yml` (composer.json validate/normalize), `composer-require-checker.yml`, `phpstan.yml`, `phpstan-baseline-diff.yml`, `phpstan-types.yml`, `rector.yml`, `styling.yml`, `syntax.yml`, `whitespace.yml`, `shellcheck.yml`, `spellcheck.yml`, `pre-commit.yml`, `docker-compose-lint.yml`, `docker-lint-hadolint.yml`, `conventional-commits.yml`, `validate-codecov.yml`.

**(c) Security scanning**

`semgrep.yml` "Semgrep Security Scan" (only dedicated SAST). Dependabot config at `.github/dependabot.yml`; auto-merge at `dependabot-auto-merge.yml`.

**(d) Release / publish**

`release-prep.yml` "Release Prep Conductor", `release-permissions-check.yml`, `branch-cut-automation.yml`, `patch-prep-automation.yml`, `notify-release-targets-changed.yml`, `docker-release-orchestrator.yml`, `docker-push-dockerhub-readme.yml`.

**(e) Docker image build**

`docker-build-flex-core.yml`, `docker-build-322.yml` "Flex 3.22 Dockers Build", `docker-build-323.yml`, `docker-build-edge.yml` (nightly), `docker-build-release.yml`, `build-dev-php-fpm-docker.yml` (nightly 8.6 fpm), `weekly-build-php-fpm-dockers.yml`. Companion validation: `docker-validate-byte-identical.yml`, `docker-validate-release-targets.yml`, `sync-byte-identical.yml`, `docker-test-bats.yml`, `docker-test-container-functionality.yml`, `docker-test-core.yml` "OpenEMR Docker Test Core", `docker-test-flex-{322,323,edge}.yml`, `docker-test-release.yml`.

**(f) Maintenance / meta**

`all-checks-passed.yml` (required-check aggregator), `api-docs.yml` (Swagger freshness), `refresh-reserved-word-supplement.yml`, `claude-review.yml` + `claude-review-reusable.yml` (AI PR review).

### 4.2 Matrix dimensions

- **OS**: `ubuntu-24.04` throughout (e.g. `test-all.yml:41`, `test-scheduled.yml:27`)
- **PHP versions tested**: 8.2, 8.3, 8.4, 8.5 (5.7-through-12.2 mariadb suffixes appear in `ci/` dir names)
- **DB versions tested** (from `ci/` dir names, `ci/README.md:9`): mariadb 10.6, 10.11, 11.4, 11.8, 12.2; mysql 5.7, 8.0, 8.4; plus redis sentinel variants (plain / TLS / mTLS)
- **Test-Mode: full** commit-message trailer expands PR coverage `test-all.yml:9-17`

### 4.3 Reusable actions & meta

`.github/actions/setup-php-composer/action.yml` (composite: PHP + composer with cache), `.github/actions/test-actions-core/action.yml`. Problem matchers at `.github/problem-matchers/{php-syntax,phpcs}.json`. Actionlint config `.github/actionlint.yaml`.

---

## 5. `ci/` directory

`ci/README.md:1-45` explains: not a fork of `.github/workflows/` — this is the **matrix input**. Each subdirectory is a named test configuration consumed by the reusable `test.yml` workflow.

| Kind | Contents |
|---|---|
| Named test configs (21) | `apache_82_118/`, `apache_83_118/`, `apache_84_118/`, `apache_85_{57,80,84,93,106,114,118,122,1011}/`, `apache_85_118_upgrade/`, `apache_82_118_upgrade/`, `apache_85_118_redis_sentinel[_tls\|_mtls]/`, `apache_82_118_redis_sentinel_mtls/`, `nginx_{82..86}/` — each just a `docker-compose.yml` that overrides image tags and pulls in shared partials via `x-includes` (`ci/apache_85_118/docker-compose.yml:1-6`) |
| Shared compose fragments | `compose-shared-apache.yml`, `compose-shared-mariadb.yml`, `compose-shared-mysql.yml`, `compose-shared-selenium/`, `compose-shared-mailpit/`, `compose-shared-nginx/`, `compose-shared-redis-sentinel/` |
| Shell library | `ciLibrary.source` — defines `run_unit_tests`, `run_e2e_tests`, etc. Sourced by every job. |
| Helpers | `parse_docker_dir.sh` (extract webserver/php/db from dir name), `auto_prepend.php` (Xdebug/PCOV coverage prepend), `convert-coverage`, `phpinfo.php` |
| Nginx assets | `ci/nginx/{nginx.conf,dummy-cert,dummy-key,php.ini}` and `ci/nginx_8{2..6}/docker-compose.yml` |
| Inferno | `ci/inferno/{compose.yml,run.sh,inferno-files/,onc-certification-g10-test-kit/,test_configs/}` — ONC certification harness |
| Coverage docs | `ci/README.md`, `ci/README-COVERAGE.md` |

Naming convention: `{webserver}_{phpversion}_{dbversion}[_redis_sentinel[_tls|_mtls]][_upgrade]`; `_no-e2e` suffix skips e2e (`ci/README.md:17-19`).

---

## 6. `openemr-cmd` and `/root/devtools`

`openemr-cmd` (host-side script, distributed separately per CONTRIBUTING.md) is a thin wrapper over `docker compose exec openemr /root/devtools <cmd>`. The **in-container** target is `docker/flex/utilities/devtools`, copied to `/root/devtools` at image build time (`docker/flex/Dockerfile:262,279`).

Command groups exposed by `devtools` (`docker/flex/utilities/devtools:1-100`, header comment enumerates all commands):

| Group | Commands |
|---|---|
| Logging | `php-log`, `xdebug-log`, `list-xdebug-profiles` |
| Code quality | `psr12-report`/`fix`, `psr12-src-report`, `lint-themes-{report,fix}`, `lint-javascript-{report,fix}`, `php-parserror`, `rector-dry-run`/`process`, `phpstan`, `phpstan-generate[-reset]`, `codespell`, `conventional-commits-check`, `require-checker`, `composer-validate`, `composer-normalize[-fix]`, `composer-checks`, `code-quality` |
| Build | `build-themes`, `build-api-docs` |
| Testing | `unit-test`, `javascript-unit-test`, `jut-reports-build`, `api-test`, `e2e-test`, `fixtures-test`, `services-test`, `validators-test`, `controllers-test`, `common-test`, `phpunit-isolated`, `update-twig-fixtures`, `update-layout-field-fixtures`, `clean-sweep`, `clean-sweep-tests` |
| Dev lifecycle | `dev-reset`, `dev-install`, `dev-reset-install`, `dev-reset-install-demodata`, `dev-sqldrive`, `dev-reset-install-sqldrive` |
| Multisite | `list-multisites`, `enable-multisite`, `disable-multisite`, `set-swagger-to-multisite <site>`, `generate-multisite-bank <count>` |
| Backup | `backup`, `restore`, `list-snapshots`, `list-capsules` |
| Upgrade | `upgrade <version>`, `change-encoding-collation` |
| SSL/TLS | `force-https`, `un-force-https`, `sql-ssl[-client][-off]`, `couchdb-ssl[-client][-off]` |

The library `docker/flex/utilities/devtoolsLibrary.source` (538 lines) provides shared functions — notably `setGlobalSettings()` (`:147-164`) which drives `OPENEMR_SETTING_*` → `globals` table updates, and `resetOpenemr()` (`:184-194`) which drops DB + wipes sites/.

---

## 7. Reusable-for-our-fork verdict

| Artifact | Reusable? | Notes |
|---|---|---|
| `docker/development-easy/` stack | **Yes** | Well-documented, `openemr-cmd` integrated, host-uid handling solved, has selenium + mailpit + couchdb + ldap out of box. Bind-mounts full repo; edits reflect live. Reasonable default for our dev experience. |
| `docker/production/docker-compose.yml` | **Partially** | Fine as a starting template. Missing: reverse-proxy TLS termination, Redis wiring (image supports it), external-DB pattern, secrets, backups, non-root DB creds, health-check exposure to LB. Needs hardening before any real deployment. |
| `docker/flex` / `docker/release` Dockerfile | **Yes** | Alpine 3.23 + PHP 8.5, well-parameterized (`PHP_VERSION` arg). `openemr.sh` + `auto_configure.php` + `OPENEMR_SETTING_*` combo is a genuinely automatable install pipeline — usable for tenant provisioning. |
| GitHub Actions workflows | **Selectively** | 57 workflows are tuned to upstream release cadence (Docker Hub publish, rel-branch cuts, Inferno cert). For our fork: keep `test-all.yml` + `test.yml` + `isolated-tests.yml` + `phpstan*.yml` + `rector.yml` + `styling.yml` + `pre-commit.yml` + `js-test.yml` + `semgrep.yml`. Discard: `docker-build-*` (we won't publish `openemr/openemr:*`), `docker-release-orchestrator.yml`, `docker-push-dockerhub-readme.yml`, `branch-cut-automation.yml`, `patch-prep-automation.yml`, `notify-release-targets-changed.yml`, `refresh-reserved-word-supplement.yml`, `claude-review*` (opt-in). |
| `/root/devtools` script | **Yes** | Complete developer CLI; nothing upstream-specific. Consumed via `openemr-cmd` on host or `docker compose exec openemr /root/devtools <cmd>` directly. |
| `docker-entrypoint` (`openemr.sh`) | **Yes** | Idempotent, respects `EMPTY`/`FORCE_NO_BUILD_MODE`/`EASY_DEV_MODE_NEW` flags, has swarm-mode volume restore, host-uid adoption. Battle-tested. |
| Selenium test harness | **Yes** | Runs headless-off with VNC on `:7900`; drivable from Symfony Panther (see `tests/Tests/E2e/Base/BaseTrait.php` per CLAUDE.md). No changes needed. |
| `ci/` matrix + `test.yml` reusable workflow | **Yes** (with pruning) | Elegant pattern: dir name encodes config, shared compose partials via `x-includes`. Keep the mechanism; drop configs we don't ship (e.g. mysql 5.7, mariadb 10.6). |
| k8s / Helm | **N/A** | Not in repo. `UNKNOWN — requires product-owner input` on target orchestration platform. |

---

## Summary (5 lines)

- **Path:** `docs/00-discovery/11-devops-docker-ci.md`
- `docker/` splits cleanly into three image sources (`flex/`, `release/`, `binary/`), three consumer stacks (`production/`, `development-easy/`, `development-insane/`), and shared assets in `library/`; PHP 8.5 + Alpine 3.23 + MariaDB 11.8 are the current pins.
- Auto-provisioning surface is well-defined: `MYSQL_*` + `OE_USER`/`OE_PASS` + `FLEX_REPOSITORY*` for install, `OPENEMR_SETTING_<gl_name>` env-var pattern for post-install globals — sufficient for automated tenant creation without touching the UI.
- CI is 57 workflows on `ubuntu-24.04` driving a 21-config matrix in `ci/` (PHP 8.2–8.5, mariadb 10.6/10.11/11.4/11.8/12.2, mysql 5.7/8.0/8.4, apache+nginx, redis-sentinel plain/TLS/mTLS); scheduled nightly full coverage, PRs default to PHP 8.2 only unless `Test-Mode: full` trailer is set.
- `/root/devtools` (in `docker/flex/utilities/devtools`) is the canonical dev CLI wrapped by `openemr-cmd`; reusable verbatim.

## UNKNOWNs

1. **Target orchestration platform for our fork (k8s vs Docker Compose vs Swarm)** — no Helm/k8s artifacts exist in tree. `UNKNOWN — requires product-owner input`.
2. **Whether we publish our own Docker images** (drives keep/drop decision on the ~15 `docker-*` publish workflows). `UNKNOWN — requires product-owner input`.
3. **Whether Inferno ONC certification (`inferno-test.yml`, `ci/inferno/`) is in scope** for our fork. `UNKNOWN — requires product-owner input`.
