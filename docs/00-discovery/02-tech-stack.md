# Phase 0 — Technology Stack

_Read-only audit. All observations are byte-derived from `composer.json`,
`composer.lock`, `package.json`, `sql/database.sql`, `.github/workflows/*.yml`,
the `docker/development-easy/` compose file, and grep/glob scans over the
tracked source tree. No packages installed, no code executed._

---

## 1. Language / Runtime Ranges

| Runtime | Minimum | Maximum tested in CI | Composer platform pin | Evidence |
|---------|---------|----------------------|-----------------------|----------|
| PHP | **8.2.0** | 8.6 (syntax-check only), 8.5 (full test matrix) | `8.2` (locks Composer resolver) | `composer.json:14`, `composer.json:243`, `src/Common/Compatibility/Checker.php:19`, `.github/workflows/syntax.yml:28`, `.github/workflows/phpstan.yml:26`, `.github/workflows/isolated-tests.yml:30`, `.github/workflows/test.yml:47-70` |
| Node.js | **24.0.0** | 24.x (host has 24.11.0) | n/a | `package.json:28-30` |
| MariaDB (dev-easy default) | **11.8.8** (pinned by digest) | see integration matrix | n/a | `docker/development-easy/docker-compose.yml:4` |
| MariaDB (CI matrix) | 10.6 | 12 | n/a | `.github/workflows/integration-tests.yml:39-51` |
| MySQL (CI matrix) | 5.7 | 9.7 | n/a | `.github/workflows/integration-tests.yml:30-36` |
| Charset / collation | `utf8mb4` | — | — | `docker/development-easy/docker-compose.yml:5` (`--character-set-server=utf8mb4`) |

### PHP min-version evidence

Two independent locks agree:

- **Composer resolver** — `composer.json:14`: `"php": ">=8.2.0"`; platform block
  (lines 209-244) pins the resolver to `"php": "8.2"` so `composer.lock` will
  never depend on anything requiring >8.2.
- **Runtime self-check** — `src/Common/Compatibility/Checker.php:19` hard-codes
  `$minimumPhpVersion = "8.2.0"` and is invoked from `setup.php`,
  `sql_upgrade.php`, `sql_patch.php`, `acl_upgrade.php`, `admin.php`, and
  `globals.php` (per the class docblock, lines 8-9). Note this file appears in
  `composer.json:189` under `exclude-from-classmap` — it is intentionally
  autoloader-independent so it can run on a below-8.2 host and still print a
  friendly error rather than fataling on a namespaced-class lookup.

### PHP max-version evidence

CI tests PHP up through 8.5 as the primary target; syntax-check goes as far as
8.6 on `master` pushes (`syntax.yml:28`). PHPStan runs on 8.5 (`phpstan.yml:26`),
Rector on 8.5 (`rector.yml:24`), isolated tests on 8.2/8.3/8.4/8.5. There is no
declared upper bound in `composer.json`.

### DB min/max

The **canonical version-support matrix** in the repo is `.github/workflows/integration-tests.yml`:

```
mysql:5.7 | mysql:8.4 | mysql:9.7
mariadb:10.6 | mariadb:10.11 | mariadb:11.4 | mariadb:11.8 | mariadb:12
```

Additional docs corroborate: `ci/README.md:18` states "MariaDB/MySQL version: 11.6"
as the default CI target; `docker/development-easy` runs `mariadb:11.8.8`.
Root-level `README.md` and `Documentation/api/DEVELOPER_GUIDE.md:71` require
"MySQL/MariaDB" generically without a version pin. The default charset shipped
by `sql/database.sql` is `utf8mb4` (per the `--character-set-server=utf8mb4`
apply-time flag in dev-easy compose; no `DEFAULT CHARSET` clause is embedded in
the CREATE TABLE statements themselves — verified against `sql/database.sql:13-27`).

---

## 2. Template Engines — Definitive Footprint

The initial "0 Smarty hits" from Phase 1 was a Windows-PowerShell escaping
artefact; re-verified with the grep tool (regex `Smarty` in `*.php`) → **100+
matches, evidence-truncated at the grep-tool cap**. Same for Twig.

### 2.1 Twig (modern, dominant)

| Metric | Value | Evidence |
|--------|-------|----------|
| Composer package | `twig/twig ^3.22.2` | `composer.json:133` |
| OpenEMR wrapper class | `OpenEMR\Common\Twig\TwigContainer` | `src/Common/Twig/TwigContainer.php` (95 lines) |
| Central extension | `OpenEMR\Common\Twig\TwigExtension` | `src/Common/Twig/TwigExtension.php` |
| Instantiation pattern | `(new TwigContainer(null, $kernel))->getTwig()` | e.g. `portal/index.php:113`, `src/RestControllers/AuthorizationController.php:213`, `src/OeUI/OemrUI.php:106`, `src/PostCalendar/CalendarRenderer.php:68` |
| Autoescape | **off** (`['autoescape' => false]`) | `src/Common/Twig/TwigContainer.php:70` — templates escape manually |
| Loader | `FilesystemLoader` rooted at `$projectDir/templates` + optional per-caller path | same file, lines 52-69 |
| Template files | **284 `.twig`** across the tree | Phase 1 count; concentrated in `interface/forms` (57), `templates/patient` (44), `templates/portal` (32), `interface/modules` (28), `templates/login` (25), `templates/interface` (18), `templates/reports` (15), `templates/calendar` (15), `templates/emails` (12) |
| PHP consumers | `use OpenEMR\Common\Twig\TwigContainer` appears in **50+ files** (grep result truncated at 100 matches → floor of ~50 unique files across `src/`, `library/`, `interface/`, `portal/`, `controllers/`, `ccr/`) | grep `TwigContainer\|Twig\\Environment` |

Notable Twig consumers: `src/RestControllers/AuthorizationController.php`,
`src/FHIR/SMART/*`, `src/PostCalendar/CalendarRenderer.php`,
`src/Services/Cda/CdaValidateDocuments.php`, `src/OeUI/OemrUI.php`,
`portal/index.php`, `portal/home.php`, and — modern-idiom outliers —
`src/Services/PatientAccessOnsiteService.php:65` which stores the `Environment`
as a constructor-injected `readonly` property.

### 2.2 Smarty (legacy, contained)

| Metric | Value | Evidence |
|--------|-------|----------|
| Composer package | `smarty/smarty ^4.5.6` | `composer.json:115` |
| OpenEMR-owned Smarty plugins | **11 files** under `library/smarty/plugins/` | glob `library/smarty/**/*.php` |
| Plugin names | `function.{xlt,xlj,xla,headerTemplate,dispatchPatientDocumentEvent,datetimepickerSupport,assetVersionNumber,assetsTemplate,amcCollect}.php` + `modifier.{xlt,xla}.php` | same |
| `.tpl` template files | **27** (mostly `gacl/` and `contrib/portal_templates/`) | Phase 1 |
| Primary Smarty consumer | **Patient portal `Phreeze` framework** at `portal/patient/fwk/libs/verysimple/Phreeze/` — `PortalController` extends Smarty; render engine is pluggable (Smarty / Savant / PHP) but Smarty is the default | `portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php:46,89,766-787`, `portal/patient/_global_config.php:43,104` |
| PHPStan baseline references | 15+ entries mention `Smarty_*` internals or the `PortalController::$Smarty` property | e.g. `.phpstan/baseline/argument.type.php:1740,1755`, `.phpstan/baseline/property.deprecated.php:5` |
| CI regression net | `tests/Tests/RestControllers/ControllerRoutingTest.php:190-220` explicitly guards against Smarty's `__call` magic method being mistaken for a controller method — evidence the "controller extends Smarty" pattern is still live in `controllers/C_*.class.php` | same file |

**Conclusion**: Smarty is confined to (a) the patient portal's inherited
Phreeze legacy layer, (b) the legacy `controllers/C_*.class.php` MVC layer
(which extends Smarty as base class), and (c) 11 hand-written Smarty
plugins that mirror OpenEMR's `xl`/`xlt`/`xla`/`xlj` i18n helpers so templates
can call them. It is *not* used in modern `src/` code. Twig is the strategic
template engine going forward.

### 2.3 Other template dialects

| Dialect | Count | Notes |
|---------|------:|-------|
| `.mustache` | 104 | **Third-party bundled JS libraries** (per Phase 1) — not OpenEMR templates. `openemr/mustache ^2.15.2` (`composer.json:95`) is the PHP-side runtime, used by CDA/CCR generation. |
| `.html` | 523 | Mixed: some are Twig fragments named `*.html.twig`, many are static docs/screenshots. Not a coherent engine. |

---

## 3. Templating vs. Raw-PHP-Page Breakdown

**Sampling method**: 40 random files drawn from `git ls-files interface/*.php`
(N=934) tested for the substrings `TwigContainer`, `new Smarty`, `->render(`,
or `use OpenEMR\Common\Twig`.

| Result | Count in sample | Extrapolated to interface/ |
|--------|----------------:|---------------------------:|
| Uses a template engine (Twig or Smarty) | 3 / 40 (7.5%) | ~70 of 934 |
| Raw PHP + inline HTML `echo`s (or non-templating logic) | 37 / 40 (92.5%) | ~860 of 934 |

**Interpretation**: The `interface/` directory remains overwhelmingly a legacy
"echo HTML from procedural PHP" surface. New rendering paths (Twig) exist and
are used at high-value integration points (SMART/FHIR auth pages, portal,
calendar, layout helpers, error pages, emails) but the day-to-day CRUD screens
under `interface/usergroup/`, `interface/drugs/`, `interface/patient_file/`,
etc. are still raw-echo PHP. Sample evidence: `interface/usergroup/user_admin.php`,
`interface/usergroup/usergroup_admin.php`, and `interface/drugs/dispense_drug.php`
all contain hundreds of direct `sqlStatement()` + `echo`/`<?= ?>` pairs with
no template engine.

Note: `src/` (the modern PSR-4 layer) is **not** template-based at all — it
provides services/controllers/entities that call `TwigContainer` when they
need to render.

---

## 4. Autoloading Structure

Defined at `composer.json:171-194`. Three composers-autoload primitives are
stacked to bridge modern and legacy code:

| Mechanism | Target | Purpose |
|-----------|--------|---------|
| `psr-4` | `"OpenEMR\\": "src"` | Namespaced modern code — all of `/src/` |
| `classmap` | `["library/classes"]` | Legacy class files that pre-date namespaces (e.g. `library/classes/Document.php`, `library/classes/Installer.class.php`). Loaded by filename→class-name mapping without `use` statements. |
| `files` | 8 files (see below) | Legacy **procedural** bootstraps auto-`require`d on every request |

### The 8 `files` bootstraps

Every one of these declares free functions that are called globally across
the legacy code — they *must* be loaded before any legacy PHP script runs:

| # | Path | Nature |
|--:|------|--------|
| 1 | `library/global_functions.inc.php` | Misc utility functions |
| 2 | `library/htmlspecialchars.inc.php` | Escaping helpers |
| 3 | `library/formdata.inc.php` | Form-data sanitizers (`formData()`, `attr()`, etc.) |
| 4 | `library/sanitize.inc.php` | Input sanitization |
| 5 | `library/formatting.inc.php` | Number/date formatting |
| 6 | `library/date_functions.php` | Legacy date math |
| 7 | `library/validation/validate_core.php` | Legacy validators |
| 8 | `library/translation.inc.php` | `xl()`, `xlt()`, `xla()`, `xlj()` — the i18n primitives |

### `exclude-from-classmap` (composer.json:188-193)

Four entries — each has a specific reason:

| Path | Reason |
|------|--------|
| `src/Common/Compatibility/Checker.php` | Must run *before* the autoloader (used by setup / upgrade scripts to warn about outdated PHP before hitting any namespaced code). See §1 evidence. |
| `library/classes/ClinicalTypes/` | Contains classes whose file/class name mapping would clash with the classmap generator |
| `library/classes/rulesets/` | Same — legacy rule-set classes not intended to be autoloaded |
| `library/classes/smtp/` | Same — bundled SMTP library with its own namespacing conventions |

### Legacy ↔ modern coexistence

- Legacy procedural PHP scripts under `interface/`, `library/`, `controllers/`
  begin with `require_once __DIR__ . '/../globals.php'` (or similar), which
  transitively pulls in Composer's autoloader (via `vendor/autoload.php`)
  and executes the 8 `files` bootstraps.
- Modern services in `src/` can then be instantiated by legacy pages via
  `use OpenEMR\...` + `new` — as evidenced by every `TwigContainer` consumer
  under `interface/` and `library/`.
- The autoload-dev block (lines 195-200) also maps `OpenEMR\\` → `tests/`
  and `OpenEMR\\Release\\` → `tools/release/src/` for test/tooling code.

---

## 5. Framework Surface — What Is Actually Wired In

Grep methodology: `^use <Namespace>\\` in `*.php`, hits capped by the grep tool
at 100 matches (with "more matches available" indicator).

### 5.1 Laminas MVC — legacy "zend_modules" only

- **Where it lives**: 100% of Laminas `use` statements found are under
  `interface/modules/zend_modules/module/{Carecoordination, Ccr, CodeTypes,
  Documents, FHIR, Immunization, Installer, Patientvalidation, PatientFilter,
  PatientFlowBoard, PrescriptionTemplates, Syndromicsurveillance}/`.
- **What's wired**: `Laminas\Mvc\Controller\AbstractActionController`,
  `Laminas\Router\Http\Segment`, `Laminas\ModuleManager\ModuleManager`,
  `Laminas\ServiceManager`, `Laminas\Form\Form`, `Laminas\InputFilter\*`,
  `Laminas\View\Model\{ViewModel,JsonModel}`, `Laminas\EventManager`.
- **Isolation**: The `Laminas MVC` shell exists to host the Zend-Framework-era
  module system. Modern `src/` code does **not** import from `Laminas\Mvc`.
  Modern integration tests confirm the module system is wrapped in an OpenEMR
  bridge — see `tests/Tests/Isolated/Core/Routing/ZendModuleApplicationIsolatedTest.php`
  and `ServiceManagerControllerLocatorIsolatedTest.php` (this is a
  ***custom router shim*** that maps zend_modules routes without booting the
  full Laminas MVC stack).
- **Concrete entry point**: `interface/modules/zend_modules/public/index.php`
  (Phase 5 will detail).
- **Verdict**: **Laminas MVC is a legacy island.** All modernization work
  will need to keep it working (Carecoordination is the CCDA import/export
  path — critical) but should not extend it.

### 5.2 Symfony — pervasive as a component library, no full-stack framework

- **Where it lives**: `src/`, `tests/`, `apis/dispatch.php`, `controller.php`,
  `config/database.php`, `tools/release/*`.
- **Most-imported components** (grep of `^use Symfony\Component\<X>\\`):
  1. `Symfony\Component\HttpFoundation\*` — `Request`, `Response`,
     `Session\Session`, `Session\SessionInterface`,
     `Session\Storage\MockArraySessionStorage`, `InputBag`, `ServerBag`.
     Used by every FHIR REST controller test and by
     `src/RestControllers/AuthorizationController.php`.
  2. `Symfony\Component\Console\*` — `Application`, `Command`,
     `SingleCommandApplication`, `Input*`, `Output*`. Owns all of
     `tools/release/bin/*` and `bin/generate-phpstan-types.php`.
  3. `Symfony\Component\EventDispatcher\{EventDispatcher, GenericEvent}` —
     the event system referenced in CLAUDE.md ("Event system uses Symfony
     EventDispatcher"). Used by CustomModules, Smarty plugins for
     `dispatchPatientDocumentEvent`, and 30+ test files.
  4. `Symfony\Component\HttpKernel\Exception\{AccessDeniedHttpException,
     BadRequestHttpException, NotFoundHttpException, HttpExceptionInterface}` —
     imported at the top level in `controller.php` (root-level dispatcher).
  5. `Symfony\Component\Process\Process` — `tests/Tests/Services/Background/*`,
     `tools/release/*`.
  6. `Symfony\Component\Cache\Adapter\ArrayAdapter` — used by
     `config/database.php:43` as the Doctrine metadata cache.
- **Composer**: 16 Symfony components required at `^7.3` (`composer.json:118-132`).
- **Symfony HttpKernel**: Only the *exceptions* subpackage is imported at
  application level (not `HttpKernel` the full-stack dispatcher). OpenEMR
  wraps its own kernel at `OpenEMR\Core\Kernel` (referenced from
  `TwigContainer.php:18`).

### 5.3 Doctrine — DBAL dominant, ORM minimal

- **Composer**: `doctrine/dbal ^4.4`, `doctrine/migrations ^3.9`,
  `doctrine/orm ^3.6` (`composer.json:54-56`).
- **`^use Doctrine\\`** grep: **44 matches** total across the whole tree.

| Sub-package | File count (approx.) | Notes |
|-------------|---------------------:|-------|
| `Doctrine\DBAL\{Connection, DriverManager, Query, Schema, Types, Platforms}` | ~14 | The strategic DB layer for new code. Used by `src/BC/Database.php`, `src/BC/DatabaseConnectionFactory.php`, `src/Common/Database/ConnectionManager.php`, `src/Common/Logging/Audit/LogTablesSink.php`, `src/Services/HolidayService.php`, `src/Encryption/Storage/PlaintextKeyInDbKeysTable.php`. Per CLAUDE.md: "MySQL via Doctrine DBAL 4.x (ADODB surface API for legacy code)". |
| `Doctrine\Migrations\{AbstractMigration, Configuration, DependencyFactory}` | 3 | Wired in `config/database.php:20-27`. Migration files live under `db/Migrations/`, e.g. `db/Migrations/Version00000000000000.php`. |
| `Doctrine\ORM\{EntityManager, EntityManagerInterface, Mapping}` | **only 2 real production consumers** | `src/Console/Command/ShellCommand.php:18` and `src/Services/CodeTypes/CodeTypeMappingUpdater.php:17`. Entity classes: `src/Entities/{ListOption, CodeType, Code}.php`. `config/database.php:126-127` binds `EntityManagerInterface::class => EntityManager::class` in the DI container. |
| `Doctrine\Common\EventManager` | 1 | `config/database.php:15` |

### 5.4 The DB reality: ADODB dominates in the legacy tree

- `adodb/adodb-php ^5.22.11` is required (`composer.json:49`).
- Grep `sqlStatement\(|sqlQuery\(|QueryUtils::` in `*.php` returns
  **100 matches (grep-tool cap, "more matches available")** — every legacy
  interface script is threaded with these calls. Sample: `sql_upgrade.php`,
  `interface/usergroup/*.php`, `interface/drugs/*.php`,
  `interface/usergroup/mfa_u2f.php`, etc.
- The `sqlStatement()`/`sqlQuery()`/`sqlFetchArray()` global functions are
  the *ADODB surface API* — declared in `library/` and used everywhere.
- **Ratio**: raw-count-wise, ADODB legacy calls outnumber Doctrine imports by
  well over 100:1. **Doctrine ORM is only used in 2 production services.**
  All modernization DB work is at the DBAL (not ORM) layer.

---

## 6. Frontend Toolchain

| Layer | Version | Evidence |
|-------|---------|----------|
| Webpack | 5.108.1 | `package.json:65` |
| Webpack CLI | 5.1.4 | `package.json:66` |
| Config file | `webpack.themes.js` (root) + 2 module-scoped in `interface/modules/custom_modules/oe-module-comlink-telehealth/` | glob `webpack*.js` |
| SASS | 1.101.0 (with `sass-loader` 13.3.3) | `package.json:58-59` |
| PostCSS | 8.5.16 (+ `postcss-loader` 7.3.4, `autoprefixer` 10.5.2) | `package.json:56-57,46` |
| CSS extract/minify | `mini-css-extract-plugin` 2.10.2, `css-minimizer-webpack-plugin` 8.0.0 | `package.json:48,54` |
| Jest | 29.7.0 (with `jest-environment-jsdom` 30.4.1, `@types/jest` 29.5.14) | `package.json:52-53,45` |
| ESLint | 9.39.4 (+ `eslint-plugin-import` 2.32.0, `eslint-plugin-jest` 28.14.0) | `package.json:49-51` |
| Stylelint | 16.26.1 (+ `stylelint-config-sass-guidelines` 12.1.0, `stylelint-config-standard` 37.0.0, `stylelint-order` 6.0.4, `stylelint-scss` 6.14.0) | `package.json:60-64` |
| Sync scripts | `scripts/sync-css.js`, `scripts/install-assets.js` | glob `scripts/*.js` |
| Build commands | `npm run build` → `build:webpack:prod` + `build:sync` (`scripts/sync-css.js`) | `package.json:8-14` |

### ⚠️ `napa` postinstall — external un-versioned fetches

`package.json:7` runs `napa` as `postinstall`, which fetches these
**directly from GitHub archives** on every `npm install`:

| Package | Source URL | Notes |
|---------|-----------|-------|
| `bootstrap-rtl` | `github.com/PerseusTheGreat/bootstrap-4-rtl/archive/643a8f9e.zip` | Pinned to commit SHA — reproducible but bespoke |
| `jquery-creditcardvalidator` | `github.com/PawelDecowski/.../v1.1.0.tar.gz` | Tag pinned |
| `jquery-panelslider` | `github.com/eduardomb/.../1.0.0.tar.gz` | Tag pinned |
| **`jquery-ui`** | `jqueryui.com/resources/download/jquery-ui-1.12.1.zip` | 1.12.1 (2016) — critically out of maintenance |
| `jquery-ui-themes` | same — 1.12.1 | same |
| `literallycanvas` | `github.com/literallycanvas/.../v0.4.14.tar.gz` | Drawing library |
| **`react`** | `github.com/facebook/react/releases/.../react-15.1.0.zip` | **React 15.1.0 (June 2016)** — pinned to prehistoric version. Only shipped as bundled artefact; not a modern React dep. |
| `lforms` | `clinicaltables.nlm.nih.gov/lforms-versions/lforms-33.0.0.zip` | NLM Forms library, off-registry |

**Impact**: (a) these are not in `package-lock.json` and are not
integrity-hashed by npm; (b) reproducible builds depend on those URLs
remaining live; (c) React 15.x is a critical flag for the frontend
modernization roadmap — this fork is *not* on React 16/17/18/19. **Any
migration decision must first enumerate which OpenEMR UI actually consumes
this React 15 bundle.** _Deferred to Phase 8._

### Browserslist targets (package.json:31-43)

Extremely permissive — includes `ie >= 8`, `bb >= 10`, `ios >= 7`,
`android >= 4`. This means Webpack/Babel targets are set for IE8+ output,
which drags in a lot of legacy polyfills. Modernization opportunity flagged
for Phase 8.

---

## 7. PSR Standards Adopted

Combined evidence: `composer.json` explicit `psr/*` requires plus grep-verified
production usage.

| PSR | Package required | Usage evidence |
|-----|------------------|----------------|
| **PSR-1** | (implicit in `slevomat/coding-standard`, `squizlabs/php_codesniffer`) | Enforced by `composer phpcs`. See `composer.json:157-158`. |
| **PSR-3** (Logger) | `psr/log ^3.0.2` (`composer.json:110`) | `Psr\Log\LoggerInterface` imported in 30+ files (grep-truncated at 100). Concrete impl: `monolog/monolog ^3.9.0` (`composer.json:89`). Consumers: `src/Telemetry/TelemetryService.php:19,33,45`, plus every FHIR service `setSystemLogger()` call site. |
| **PSR-4** (Autoloading) | (implicit) | Composer autoload block, `composer.json:171-194` — see §4. |
| **PSR-7** (HTTP Messages) | `psr/http-message ^1.1`, `nyholm/psr7 ^1.8.2`, `guzzlehttp/psr7 ^2.8` | `composer.json:109,92,63`. `nyholm/psr7-server ^1.1.0` for server-side (`composer.json:93`). |
| **PSR-11** (Container) | `psr/container ^1.1` (`composer.json:106`) | Concrete impl: `firehed/container ^1.1` (`composer.json:59`). `config/database.php` wires services (lines 90-127). |
| **PSR-15** | *(not required directly)* | UNKNOWN — no `psr/http-server-middleware` in `composer.json` require. May be pulled transitively; not verified. |
| **PSR-17** (HTTP Factories) | `psr/http-factory ^1.1` (`composer.json:108`) | Concrete impl: `nyholm/psr7` provides factories. |
| **PSR-18** (HTTP Client) | `psr/http-client ^1.0` (`composer.json:107`) | Concrete impl: `guzzlehttp/guzzle ^7.10.0` + `php-http/discovery` (`composer.json:62,99`). |
| **PSR-20** (Clock) | `psr/clock ^1.0` (`composer.json:105`) | Concrete impl: `lcobucci/clock ^2.3 \|\| ^3.0` (`composer.json:81`). `Psr\Clock\ClockInterface` used in production `src/`- and test-side code (e.g. `tests/Tests/Isolated/Modules/FaxSMS/Notification/AppointmentNotificationRunnerTest.php:47,170`, `tests/Tests/Unit/Common/Logging/EventAuditLoggerTest.php:26,64`). |
| **PER-CS 3.0 / PSR-12** | via `squizlabs/php_codesniffer ^4.0` + `slevomat/coding-standard ^8.28` | Style enforcement — see `composer phpcs`. |

---

## 8. Notable Framework-Surface Constraints

- **Two distinct HTTP dispatch paths coexist**:
  1. Laminas MVC — routes the `zend_modules` subsystem (`Carecoordination`
     drives CCDA, `FHIR` drives some legacy FHIR bits, `Installer` drives
     module install/uninstall). Cannot be removed without rewriting these.
  2. A custom OpenEMR REST router — `apis/dispatch.php` +
     `src/Common/Http/HttpRestRouteHandler` + `src/RestControllers/*`. This
     is where the *modern* FHIR/REST API lives (`AuthorizationController`,
     `SMART` scoped controllers, `FhirPatientRestController`, etc.). It
     uses Symfony's `HttpFoundation` `Request`/`Response` but **not**
     Symfony HttpKernel routing — the routing is bespoke (see
     `tests/Tests/RestControllers/ControllerRoutingTest.php`).
- **DB access is bimodal**: Doctrine DBAL for new code (via
  `OpenEMR\BC\Database`, `OpenEMR\Common\Database\ConnectionManager`) sits
  on top of the same MySQL connection ADODB uses. The legacy `sqlStatement()`
  free functions dispatch through ADODB. **This means schema changes must
  keep both surfaces happy** — a Doctrine migration will be visible to
  ADODB and vice versa, but Doctrine's ORM metadata cache (see
  `config/database.php:43`, `ArrayAdapter`) will not be invalidated by
  raw-SQL DDL.
- **Doctrine ORM is nascent — do not assume it's the persistence layer.**
  Only `CodeTypeMappingUpdater` and `ShellCommand` truly use the ORM. The
  three entities in `src/Entities/` (`ListOption`, `Code`, `CodeType`) are
  read/write ORM-managed, but the vast majority of tables are still
  accessed by raw SQL through ADODB or DBAL.
- **Twig autoescape is OFF globally** (`TwigContainer.php:70`) — every
  template must handle its own escaping. This is an XSS-defense-in-depth
  regression from Twig defaults and worth flagging for Phase 4 (security).
- **Symfony EventDispatcher is the modern event system** — used by both
  legacy (via `library/smarty/plugins/function.dispatchPatientDocumentEvent.php:19`)
  and modern code. Any refactor of module hooks must go through it.
- **`ext-*` requirements are extensive** (35 PHP extensions listed in
  `composer.json:15-47`, including `ext-imagick`, `ext-ldap`, `ext-redis`,
  `ext-sodium`, `ext-soap`, `ext-xsl`). Any Alpine-based container needs
  every one of these compiled in — Docker image size is a downstream cost.

---

## 9. Custom Composer Repositories — Fork-External Dependencies

`composer.json:161-170`:

| Type | URL | Referenced by | Nature |
|------|-----|---------------|--------|
| `vcs` | `github.com/openemr/wkhtmltopdf-openemr` | **NOT in `require` or `require-dev`** — the repo is declared but no `require` entry references it | Vestigial? Or referenced only via an optional path. See flag below. |
| `git` | `github.com/openemr/oe-module-cqm` | **NOT in `require` or `require-dev`** — same status | Same flag |

Also present in `require`: `claimrevolution/oe-module-claimrev-connect ^2.1`
(`composer.json:52`) — this resolves via **standard Packagist**, not via one
of the custom repos. Verified in `composer.lock`: current pinned version
is **v2.1.6** at commit `978b0dd498e0e166992259926d6fa77bf56266d4`.

### 🚩 Flag

Neither `wkhtmltopdf-openemr` nor `oe-module-cqm` appears in the top-level
`require`/`require-dev`. Two possibilities:

1. They are pulled in *transitively* by one of the top-level packages
   (e.g. `openemr/mustache` or `claimrevolution/oe-module-claimrev-connect`
   may declare them). In this case the custom `repositories` entry is
   necessary for Composer's resolver to find them.
2. They are dead entries kept for historical reasons.

**Cannot be resolved without inspecting `composer.lock` in full or running
`composer why openemr/wkhtmltopdf-openemr` — deferred to a later phase.**
Flagging here because either interpretation matters: (1) means fork
depends on `openemr/*` GitHub org staying alive; (2) means the entries can
be cleaned up.

---

## 10. Summary of Locks (as of commit `631f2b38`, 2026-07-04)

| Lock | Value |
|------|-------|
| PHP min | 8.2.0 |
| PHP CI max | 8.5 (full tests), 8.6 (syntax only) |
| Node.js min | 24.0.0 |
| MariaDB (dev-easy) | 11.8.8 |
| MariaDB CI range | 10.6 – 12 |
| MySQL CI range | 5.7 – 9.7 |
| DB charset | utf8mb4 |
| Template engines | Twig 3.22 (strategic) + Smarty 4.5 (legacy, portal-confined) |
| DB API surface | ADODB (`sqlStatement()`) legacy + Doctrine DBAL 4.4 modern; Doctrine ORM barely used |
| HTTP framework | Symfony 7.3 components (HttpFoundation + Console) + bespoke REST router + Laminas MVC 3.8 for zend_modules only |
| Frontend | Webpack 5.108, SASS 1.101, jQuery 3.7.1, Bootstrap 4.6.2, no modern React (React 15.1 bundled via napa) |
| Autoescape (Twig) | **OFF** |
| PSR-4 root | `OpenEMR\` → `src/` |
| Legacy bootstraps | 8 procedural `.inc.php` files loaded on every request |

---

## UNKNOWN / Deferred Items

- **PSR-15 middleware**: not directly required. Whether pulled transitively is
  UNKNOWN without a live vendor tree — deferred until Phase 5 (routing) or a
  vendor-population phase.
- **`openemr/wkhtmltopdf-openemr` + `openemr/oe-module-cqm` transitive owners**:
  UNKNOWN — declared as repositories but not required at top level. See §9 flag.
- **Actual React 15 bundle consumers**: UNKNOWN — need to trace what UI code
  imports the napa-fetched `react/react-15.1.0.zip`. Deferred to Phase 8
  (frontend inventory).
- **DB DEFAULT CHARSET/COLLATION at CREATE TABLE level**: `sql/database.sql`
  ships schema without explicit `DEFAULT CHARSET` per table; the connection-time
  `--character-set-server=utf8mb4` flag in dev-easy compose sets it globally.
  Whether production installers set a per-database `utf8mb4_general_ci` /
  `utf8mb4_unicode_ci` collation is UNKNOWN — requires reading `Installer` in
  Phase 3 (data-model / schema).
