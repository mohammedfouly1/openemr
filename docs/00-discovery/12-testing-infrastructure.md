# 12 — Testing Infrastructure

Read-only audit of the test scaffolding available for our SaaS custom modules. All citations are `path:line` from the repo at `D:\OpenEmr`.

## 1. `tests/` top-level layout

Enumerated via `git ls-files tests/ | ForEach-Object { ($_ -split '/')[1] } | Group-Object`.

| Subdir | Files | Purpose |
|---|---:|---|
| `tests/Tests/` | 678 | Namespaced PHPUnit test tree — `OpenEMR\Tests\*` (PSR-4 root, autoloaded via `composer.json`). |
| `tests/bats/` | 40 | Bash Automated Testing System (`.bats`) scripts covering CI byte-identical sync helpers (`tests/bats/ci-scripts/sync-byte-identical/sync.bats`). |
| `tests/PHPStan/` | 25 | Custom PHPStan rules + SQL registries + snapshots (see §8). |
| `tests/eventdispatcher/` | 12 | Example Zend/Laminas module `RestApiEventHookExample` — documentation demo, excluded from coverage (`codecov.yml:9`). |
| `tests/Rector/` | 9 | Custom Rector rules referenced from `rector.php:7-8` (`OpenEMR\Rector\Rules\*`). |
| `tests/js/` | 4 | Jest tests (see §11). |
| `tests/PHPUnit/` | 2 | The `OpenEMR\PHPUnit\Extension` safety-net bootstrap class (referenced at `tests/bootstrap.php:11`, `phpunit.xml:33`). |
| `tests/api/` | 2 | Legacy pre-namespace `InternalApiTest.php`, `InternalFhirTest.php`. |
| `tests/certification/` | 1 | `tests.md` docs. |
| `tests/bootstrap.php` | 1 | Integration-suite bootstrap (loads `interface/globals.php`). |
| `tests/README.md` | 1 | Top-level README. |

## 2. PHPUnit configuration

Three top-level XML configs exist (`phpunit.xml.dist` does **not** — the checked-in file is `phpunit.xml`):

| File | Bootstrap | Purpose |
|---|---|---|
| `phpunit.xml` | `tests/bootstrap.php` (`phpunit.xml:14`) | DB-backed integration suite. Requires container + MySQL. |
| `phpunit-isolated.xml` | none (no `bootstrap=` attr) | Pure-PHP tests; no DB, no `interface/globals.php`. |
| `phpunit.integration.xml` | none | References a `tests/Tests/Integration/` sub-suite (api + RestControllers/Subscriber). **Resolved 2026-08-19** — see `docs/discovery/openemr-decision-evidence/09-test-and-ci-inventory.md §3`: the directory is vestigial, not small — `git ls-files "tests/Tests/Integration/**"` returns 0 rows and neither path this config references exists on disk. Nothing invokes this config anywhere (0 hits in workflows, composer.json, package.json, devtools). |

Both DB and isolated configs register the same PHPUnit extension: `OpenEMR\PHPUnit\Extension` (`phpunit.xml:33`, `phpunit-isolated.xml:32`). The isolated config includes a shutdown safety-net that aborts with exit 70 if the extension did not bootstrap (`tests/bootstrap.php:22-28`).

`tests/bootstrap.php:30-32` sets `$_GET['site']='default'; $ignoreAuth=true; require_once interface/globals.php` — that single line is the reason the DB suite must run inside the openemr container.

### Suites — `phpunit.xml`

| Suite (`phpunit.xml:44-92`) | Directory |
|---|---|
| `ECQM` | `tests/Tests/ECQM` |
| `unit` | `tests/Tests/Unit` (excludes `Common/Http`, `Common/Utils`, `PaymentProcessing`, `library` — those moved to isolated) |
| `e2e` | `tests/Tests/E2e` (excludes 4 email tests) |
| `api` | `tests/Tests/Api` |
| `fixtures` | `tests/Tests/Fixtures` |
| `services` | `tests/Tests/Services` |
| `validators` | `tests/Tests/Validators` |
| `controllers` | `tests/Tests/RestControllers` |
| `common` | `tests/Tests/Common` (excludes `Session/Predis`) |
| `redis-sentinel` | `tests/Tests/Common/Session/Predis` |
| `certification` | `tests/Tests/Certification/HIT1` |
| `email` | 4 explicit E2e email files |

### Suites — `phpunit-isolated.xml`

| Suite (`phpunit-isolated.xml:59-72`) | Directory |
|---|---|
| `isolated` | `tests/Tests/Isolated` |
| `unit-isolated` | `tests/Tests/BC`, `tests/Tests/Unit/Common/Http`, `Common/Utils`, `PaymentProcessing`, `library` |

### Coverage config

`phpunit.xml:31` declares `<coverage> </coverage>` (empty — no HTML/Clover output configured in-tree). Both configs use `<source ignoreIndirectDeprecations="true">` with an `include` of `.` and `exclude` of `bin, ccdaservice, ccr, ci, config, contrib, db, meta/health, public, sphere, templates/super/rules, tools/release, vendor, rector*.php` (`phpunit.xml:94-116`). This drives PHPUnit 11's built-in source discovery for deprecation reporting — coverage report generation itself is handled externally (CLI `--coverage-*`).

**Default vs isolated:** default runs `interface/globals.php` → session, `$GLOBALS`, DB connection, ADODB, all of legacy OpenEMR. Isolated skips bootstrap entirely and lives in `tests/Tests/Isolated/` — fast, pure-PHP, safe to run on the host.

## 3. Test suite categorization

Counts of `*Test.php` files under `tests/Tests/`:

| Subdir | *Test.php count | Notes |
|---|---:|---|
| `Isolated/` | 244 | Fast, no DB. Biggest bucket by far. Includes 317 total files (fixtures, helpers). |
| `Services/` | 55 | Service-layer DB-backed tests. |
| `Unit/` | 32 | Legacy unit tests, split across DB/isolated by exclusion in `phpunit.xml:47-52`. |
| `Api/` | 17 | OAuth2-authenticated REST/FHIR API tests. |
| `E2e/` | 17 | Selenium Panther browser tests. |
| `RestControllers/` | 12 | Controller unit tests. |
| `Certification/` | 8 | ONC HIT1 conformance. |
| `Common/` | 4 | Session/Predis/etc. |
| `Validators/` | 3 | |
| `ECQM/` | 2 | Clinical quality measures. |
| `BC/` | 2 | Backward-compat. |
| `Fixtures/` | 2 | Test files ABOUT fixtures — see §6. |

## 4. E2E stack

Confirmed:

- **Client:** `Symfony\Component\Panther\Client` (`tests/Tests/E2e/Base/BaseTrait.php:26`), via `Facebook\WebDriver\Remote\DesiredCapabilities` (`:22`).
- **Grid URL:** `http://{$seleniumHost}:4444/wd/hub` where `$seleniumHost` defaults to `selenium` (`BaseTrait.php:37, 72`).
- **App URL:** `SELENIUM_BASE_URL` env, defaults to `http://openemr` (`BaseTrait.php:38`).
- **Session bootstrap:** `Client::createSeleniumClient($seleniumUrl, $capabilities, $e2eBaseUrl)` (`BaseTrait.php:73`) with Chrome args `--window-size=1920,1080 --no-sandbox --disable-dev-shm-usage --disable-gpu` and `unhandledPromptBehavior=accept` (`BaseTrait.php:51-67`).
- **Fallback:** Local ChromeDriver via `static::createPantherClient()` when `SELENIUM_USE_GRID != "true"` (`BaseTrait.php:79`).
- **E2E test count:** 17 `*Test.php` files under `tests/Tests/E2e/`.
- **Selenium container defined:** yes — `docker/development-easy/docker-compose.yml:126` service `selenium:`, image `selenium/standalone-chromium:149.0.7827.155@sha256:9b10a9…`, port `${WT_SELENIUM_PORT:-4444}:4444` (`:128-130`). App container passes `SELENIUM_USE_GRID=true`, `SELENIUM_HOST=selenium`, `SELENIUM_BASE_URL=http://openemr` (`:78-81`).

## 5. API tests

`tests/Tests/Api/ApiTestClient.php` is a GuzzleHttp-based OAuth2 client (`:7`):

- Wraps `/oauth2/default/token` for token acquisition (`:34`).
- Uses `OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository` to programmatically register a test OAuth2 client (`:8`) — no session cookie, no CSRF.
- Full OAuth2 scope list embedded (`:64+`), including `patient/*.read`, `system/*.read`, `api:fhir`, `api:oemr`, `api:port`.

**Example test** (`tests/Tests/Api/AllergyIntoleranceFhirApiTest.php:19-33`) — the standard pattern:

```php
protected function setUp(): void {
    $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
    $this->testClient = new ApiTestClient($baseUrl, false);
    $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    $this->fixtureManager = new FixtureManager();
    $this->fhirFixture = (array) $this->fixtureManager->getSingleFhirPatientFixture();
    ...
}
public function tearDown(): void {
    $this->fixtureManager->removePatientFixtures();
    $this->testClient->cleanupRevokeAuth();
    $this->testClient->cleanupClient();
}
```

No test-user creation — the API tests rely on the OAuth2 admin flow to mint tokens against the running instance.

## 6. Fixtures

`tests/Tests/Fixtures/` (28 files) holds **fixture managers**, not tests-under-test. Pattern:

| File | Role |
|---|---|
| `FixtureManager.php` | Patient/FHIR fixture loader (`:25`). Loads `patients.php`, `addresses.php`, `contacts.php`, `FHIR/patients.json` (`:42-46`). |
| `BaseFixtureManager.php` | Base class. |
| `AppointmentFixtureManager.php`, `EncounterFixtureManager.php`, `FacilityFixtureManager.php`, `PractitionerFixtureManager.php`, `CarePlanFixtureManager.php`, `ConditionFixtureManager.php`, `GaclFixtureManager.php`, `LayoutFieldFixtureManager.php`, `MedicationDispenseFixtureManager.php`, `CryptoFixtureManager.php` | Per-entity managers. |
| `InMemoryKeyStorage.php` | In-memory OAuth2 key storage helper. |
| `FHIR/{patients,practitioners,facility}.json`, `addresses.php`, `contacts.php`, `contact-addresses.php`, `allergy-intolerance.php`, `patients.php` | Raw JSON/PHP fixture data. |
| `EventAuditLoggerFixturesTest.php`, `FixtureManagerTest.php` | Tests OF the fixture managers themselves. |

**Isolation strategy:** each test calls `installFixtures()` in `setUp()` and `removeXxxFixtures()` in `tearDown()`. Removal is **prefix-based** — `FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX = "test-fixture"` (`FixtureManager.php:27`) and DELETE-by-prefix. There is **no `TransactionalTestCase` base class, no truncate-all-tables between tests, no DB snapshot/restore**. Cross-test pollution is possible if `tearDown()` is missed or a fixture manager lacks a prefix.

UUID generation via `OpenEMR\Common\Uuid\UuidRegistry` + `Ramsey\Uuid\Uuid` (`FixtureManager.php:5-7`).

## 7. Coverage reality

- **`phpunit.xml` coverage block:** empty `<coverage> </coverage>` at `:31` — no in-config Clover/HTML output.
- **Codecov:** `codecov.yml` present (`:1`). Ignores `interface/modules/custom_modules/oe-module-claimrev-connect/`, `config/`, `tests/PHPStan/`, `tests/Rector/`, `tests/eventdispatcher/`, plus two portal entry-point scripts. Path fixes strip Apache/nginx/CI prefixes (`codecov.yml:15-18`). **Project coverage floor: `auto` with 1% threshold** (was 4%). **Patch coverage target: 50%** (was 0%) with 5% threshold (`codecov.yml:20-29`). Historical baseline was ~4% — this is not a high-coverage codebase.
- **`@codeCoverageIgnore` on data providers:** confirmed as project convention. `tests/bootstrap.php:5` itself uses it (`@codeCoverageIgnore Bootstrap files run before coverage instrumentation starts.`). CLAUDE.md documents the pattern with exact wording expected on every data provider.
- **No checked-in coverage reports.**

## 8. Custom PHPStan rules

Extracted docblock summaries from `tests/PHPStan/Rules/*.php`:

| Rule file | Purpose |
|---|---|
| `ForbidDirectSessionWriteRule.php` | Forbid direct `$_SESSION` writes. |
| `ForbiddenCatchTypeRule.php` | Forbid catch blocks that would suppress specific types (e.g. `\Exception` when `\Throwable` intended). |
| `ForbiddenClassesRule.php` | Forbid Laminas-DB usage in modern code. |
| `ForbiddenCoversRule.php` | Forbid coverage-restricting annotations/attributes. |
| `ForbiddenCurlFunctionsRule.php` | Forbid raw `curl_*` functions. |
| `ForbiddenEvalRule.php` | Forbid `eval()`. |
| `ForbiddenExitInCatchFinallyRule.php` | Forbid `exit`/`die` inside `catch` or `finally`. |
| `ForbiddenFunctionsRule.php` | Forbid legacy functions in modern code. |
| `ForbiddenGlobalKeywordRule.php` | Forbid use of the `global` keyword. |
| `ForbiddenGlobalNamespaceRule.php` | Forbid defining new functions in the global namespace. |
| `ForbiddenGlobalsAccessRule.php` | Forbid direct `$GLOBALS` access (use `OEGlobalsBag`). |
| `ForbiddenInstantiationsRule.php` | Forbid direct instantiation of ServiceContainer-managed classes. |
| `ForbiddenMethodsRule.php` | Forbid legacy SQL functions in modern code. |
| `ForbiddenRequestGlobalsRule.php` | Forbid direct request superglobal access (`$_GET`, `$_POST`, `$_REQUEST`). |
| `ForbiddenShellExecutionRule.php` | Forbid shell execution functions (`exec`, `shell_exec`, `system`, etc.). |
| `ForbiddenStaticMethodsRule.php` | Block certain static method calls. |
| `OEGlobalsBagTypedGetterRule.php` | Discourage `OEGlobalsBag->get()` when a typed getter (`getString`, `getInt`, …) is available. |
| `SoftRequiredArgumentsRule.php` | Require arguments in static analysis that are defined as optional at runtime. |
| `Sql/SqlReservedWordRule.php` | Forbid unbacktick'd reserved-word column references in SQL. |
| `Sql/ReservedWordRegistry.php` | Union of MySQL + MariaDB reserved-word sets (helper, not a rule). |
| `Sql/SchemaColumnRegistry.php` | Schema-derived identifier set for column-collision checks (helper). |
| `Sql/SqlSinkResolver.php` | Classifier for AST nodes representing OpenEMR SQL execution entry points (helper). |
| `Sql/snapshots/{mariadb,mysql}.tsv` | Data snapshots for the above. |

CLAUDE.md's claim ("forbidden globals, forbidden direct instantiations, namespace rules") is accurate and understates the scope — there are 18 rule classes plus a full SQL reserved-word analyzer.

## 9. Rector config (`rector.php`)

- **Paths** (`:20-37`): `Documentation, apis, ccdaservice, ccr, contrib, controllers, custom, gacl, interface, library, oauth2, portal, sites, sphere, src, tests`. Notably scans `library/` (legacy).
- **Skip** (`:43-45`): `interface/modules/custom_modules/oe-module-claimrev-connect` (third-party via composer installer plugin).
- **PHP target:** `PhpVersion::PHP_82` (`:67`) — hard-coded because reading from `composer.json` was reportedly broken.
- **Rulesets:**
  - `->withCodeQualityLevel(5)` (`:52`)
  - `->withDeadCodeLevel(5)` (`:58`)
  - `->withTypeCoverageLevel(5)` (`:75`)
  - `->withPhpSets()` (`:74`) — all PHP-version upgrade sets up to target.
- **Explicit rules** (`:68-73`): `CallUserFuncArrayToVariadicRector`, `CatchExceptionToThrowableRector` (custom, `OpenEMR\Rector\Rules\`), `OEGlobalsBagTypedGettersRector` (custom), `SimplifyIfElseToTernaryRector`.
- **Configured:** `ClassPropertyAssignToConstructorPromotionRector` with `allow_model_based_classes=true, inline_public=false, rename_property=true` (`:53-57`).
- **Cache:** `FileCacheStorage` → `/tmp/rector` (`:46-51`).
- **Parallel:** 120s timeout, 12 processes, jobSize 12 (`:60-64`).
- **Bootstrap:** `rector-bootstrap.php` (`:17-19`).

## 10. PHPCS config (`phpcs.xml.dist`)

- **Standard:** `"PSR-12 (permissive)"` (`:3`) — does **not** apply the full `PSR12` ruleset globally; only cherry-picked sniffs.
- **Sniffs enabled:**
  - `Generic.PHP.CharacterBeforePHPOpeningTag` (`:22`)
  - `Generic.WhiteSpace.DisallowTabIndent` (`:26`)
  - `Squiz.WhiteSpace.SuperfluousWhitespace` (excludes `EndFile`, `EmptyLines`) (`:27-30`)
  - `Generic.WhiteSpace.ScopeIndent` (ignoring `T_COMMENT`, `T_DOC_COMMENT_OPEN_TAG`) (`:31-38`)
  - `PSR12.Files.FileHeader` (`:47`)
  - `SlevomatCodingStandard.Namespaces.AlphabeticallySortedUses` (`:48`)
  - `SlevomatCodingStandard.Namespaces.UselessAlias` (`:49`)
  - `SlevomatCodingStandard.Namespaces.UnusedUses` with `searchAnnotations=true` (`:50-54`)
- **Slevomat installed via** `installed_paths = vendor/slevomat/coding-standard` (`:19`).
- **Runtime:** memory 4G, tab-width 4, warning-severity 0, parallel 20 (`:6-16`).
- **Excludes** (`:64-78`): `gacl/gacl.ini.php`, `oe-module-claimrev-connect`, `node_modules`, `public/assets`, `sites/default/documents`, `/tmp-phpstan`, `/.phpstan/baseline`, `/vendor`.
- Comment at `:40-44` notes stricter rules (e.g. full `PSR12`) can be scoped per-path — not currently done.

## 11. JS tests (Jest)

- **Config:** `jest.config.js` (`:1-30`). No `jest` block in `package.json`.
- **Coverage:** dir `coverage/js-unit`, collects `**/*.js`, ignores large vendor/legacy paths incl. `interface/forms/eye_mag/js/jquery-*`, `interface/forms/questionnaire_assessments/lforms/{fhir,webcomponent}`, `interface/modules/zend_modules/public/js/lib`, `portal/patient/scripts/libs`, `swagger`, `tests`, `vendor`.
- **Scripts** (`package.json:18-19`): `test:js` → `jest`; `test:js-coverage` → `jest --coverage`. Root `"test"` script is `echo Error && exit 1` (`:15`).
- **Actual `.test.js` files:** 4 total, all in `tests/js/`:
  - `tests/js/jspdf-integration.test.js`
  - `tests/js/pdf-utils.test.js`
  - `tests/js/portal-messaging-helpers.test.js`
  - `tests/js/searchHighlight.test.js`
- **Deps** (`package.json:45-53`): `jest 29.7.0`, `@types/jest 29.5.14`, `eslint-plugin-jest 28.14.0`, `jest-environment-jsdom 30.4.1`.

**Reality:** JS test coverage is effectively unused — 4 files across a codebase with thousands of `.js` files.

## 12. Verdict — what our SaaS modules can plug into

**Reusable, ready-to-use scaffolding:**

- **PHPUnit isolated suite** (`phpunit-isolated.xml`) — drop pure-PHP tests into `tests/Tests/Isolated/<Module>/`; runs on the host without Docker. Fast feedback for domain logic (NPHIES payload builders, tenant resolvers, i18n formatters).
- **Fixture managers** (`tests/Tests/Fixtures/BaseFixtureManager.php`, `FixtureManager.php`) — extend for `TenantFixtureManager`, `NphiesClaimFixtureManager`. The prefix-based cleanup pattern is trivial to copy but leaks on `tearDown()` failure — acceptable for CI, not for parallel runs against a shared DB.
- **`ApiTestClient`** (`tests/Tests/Api/ApiTestClient.php`) — full OAuth2 flow already wired. New API endpoints get free auth coverage.
- **Panther/Selenium harness** (`tests/Tests/E2e/Base/BaseTrait.php` + `docker-compose.yml:126`) — a real Chrome-in-container is one `use BaseTrait;` away. Sufficient for user-flow tests in Arabic locales (Chrome renders RTL correctly).
- **Custom PHPStan rules** — automatically apply to new code under `src/` and `interface/modules/custom_modules/<ours>/`. `ForbiddenGlobalsAccessRule` + `ForbiddenRequestGlobalsRule` will keep us honest.
- **Twig fixture snapshotting** (per CLAUDE.md, `composer update-twig-fixtures`) — usable for our Twig templates.

**What we must build ourselves:**

- **Tenant isolation tests.** No existing `TransactionalTestCase`, no DB truncation, no scoped-connection pattern. A `TenantIsolationTestCase` that (a) spins up two tenant sites, (b) writes data as tenant A, (c) asserts tenant B cannot read it — has no prior art here. Needs to plug into whatever multitenant strategy `10-multisite-multitenant.md` recommends.
- **NPHIES mocks.** No FHIR HTTP mocking layer. Need a Guzzle mock handler (`guzzle/mockhandler`) wrapped in a `NphiesMockServer` fixture returning canned CodeSystem/ValueSet/ClaimResponse payloads. Panther can also be used to hit a WireMock container if we prefer stateful mocking.
- **Arabic / RTL i18n tests.** Nothing in `tests/Tests/` grep-matches `ar_SA`, `RTL`, or `dir="rtl"`. Need: (a) an isolated test suite for message-catalog completeness (every English key has an Arabic translation), (b) Panther-driven RTL layout assertions using screenshot diffing.
- **DB reset between tests.** Given the prefix-cleanup gap, we should introduce either a per-test DB transaction wrapper or a `mysqldump`-restore fixture for our own suites.
- **Contract tests for FHIR + NPHIES.** No JSON-schema/Pact/spectator plumbing in-tree. `openemr/validate` exists elsewhere but isn't a contract-testing lib.
- **JS test coverage.** 4 Jest tests total — if our modules ship non-trivial JS (Angular/React), we own the entire testing story.

**UNKNOWNs:**

- Whether `phpunit-isolated.xml` is actually run in CI (no `.github/workflows/` inspection performed here — deferred to a later CI-audit report). **Resolved 2026-08-19** — see `docs/discovery/openemr-decision-evidence/09-test-and-ci-inventory.md §4`: yes, `isolated-tests.yml` runs it across PHP 8.2–8.6 on every push/PR to `master`/`rel-*`, and blocks the required-check aggregator on failure.
- Whether the DB suite runs against a fresh DB per CI job or a persistent one (would determine severity of the prefix-cleanup risk).
- Current numeric coverage % — `codecov.yml:24` implies it drifted upward from the historical ~4% floor but the actual value is on Codecov's site, not in-repo. **Resolved 2026-08-19** — see `docs/discovery/openemr-decision-evidence/09-test-and-ci-inventory.md §5`: live Codecov API returned 27.53% (2026-07-21), re-measured at 28.66% (2026-08-07) — both materially above the stale ~4% figure still recorded in `codecov.yml:24`.
- Whether `symfony/panther`'s local ChromeDriver fallback (`BaseTrait.php:79`) works on Windows hosts or is Linux-only in practice.
- The `tests/Tests/Integration/` tree referenced by `phpunit.integration.xml:34-38` was not enumerated — may be small or vestigial. **Resolved 2026-08-19** — confirmed vestigial (0 tracked files); see the `phpunit.integration.xml` row in §2 above.
