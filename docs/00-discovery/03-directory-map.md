# Phase 0 — Directory Map & Extension Points

_Read-only audit. Fork HEAD `631f2b38` (OpenEMR 8.3.0-dev). No files outside this document were created or modified._

Legend for classification tags:

- **[CORE-DO-NOT-TOUCH]** — modifying breaks upgrade path; upstream owns it.
- **[SAFE-EXTENSION-POINT]** — official, upgrade-safe customization surface.
- **[LEGACY]** — procedural / pre-PSR-4 code; still core, still shipped, still edited by upstream. Do not fork-patch unless coordinating with upstream.
- **[BUILD/TOOLING]** — dev-time only; not shipped to production runtime path (or shipped but not exercised).
- **[DOCS]** — reference material.
- **[UNKNOWN]** — investigated; describe what was found and open the question.

---

## 1. Annotated Top-Level Tree

| # | Path | Tag | Purpose (evidence-based) | Representative evidence |
|---|------|-----|--------------------------|-------------------------|
| 1 | `src/` | **[CORE-DO-NOT-TOUCH]** | Modern PSR-4 code under `OpenEMR\` namespace — services, controllers, FHIR, Kernel, event system. This is where upstream is actively migrating code. | `composer.json` PSR-4 map; `src/Core/Kernel.php:33` `class Kernel`; 35 subnamespaces incl. `Services/`, `RestControllers/`, `FHIR/`, `Events/`, `Core/`. |
| 2 | `interface/` | **[LEGACY]** | Legacy web UI entry-point scripts and per-page controllers. Mixed procedural PHP + jQuery + Smarty. `interface/globals.php` is the bootstrap for essentially every legacy page. | `interface/globals.php`; `interface/README.md` (describes SASS/theme build); dirs `patient_file/`, `main/`, `billing/`, `orders/`, `themes/`, `super/`. |
| 3 | `interface/forms/` | **[LEGACY]** + **[SAFE-EXTENSION-POINT]** for _new_ forms | Encounter-form plugin dir. Each subdir is one form module with a fixed 5-file skeleton: `new.php`, `save.php`, `view.php`, `report.php`, `print.php`, plus `table.sql`. Adding a new form here is the sanctioned way to add clinical forms. Editing shipped forms is a core edit. | `interface/forms/note/` contains exactly `info.txt`, `new.php`, `print.php`, `report.php`, `save.php`, `table.sql`, `view.php`. Same shape across ~45 form subdirs (`soap`, `vitals`, `ros`, `care_plan`, `LBF`, …). |
| 4 | `interface/modules/` | container | Two-child container: `custom_modules/` (modern module system, Composer-integrated) and `zend_modules/` (legacy Zend Framework module runtime). | `git ls-tree` shows only these two children. |
| 5 | `interface/modules/custom_modules/` | **[SAFE-EXTENSION-POINT]** — primary | Sanctioned modern module surface. Each `oe-module-*` directory is a Composer-installable module with `ModuleManagerListener.php`, `openemr.bootstrap.php`, `moduleConfig.php`, `info.txt`, own `src/`. Registered via OpenEMR Module Manager UI and Composer `openemr/oe-module-installer-plugin`. | `interface/modules/custom_modules/README.md` (single line: "Custom Modules"); real modules: `oe-module-comlink-telehealth`, `oe-module-dashboard-context`, `oe-module-dorn`, `oe-module-ehi-exporter`, `oe-module-faxsms`, `oe-module-prior-authorizations`, `oe-module-weno`. `composer.json` uses `openemr/oe-module-installer-plugin` (allow-plugin entry). |
| 6 | `interface/modules/zend_modules/` | **[LEGACY]** — extension surface, discouraged | Zend/Laminas-based legacy module runtime. Kept for old modules; not the recommended path for new ones. | Subdirs `config/`, `module/`, `public/`. |
| 7 | `library/` | **[LEGACY]** | Procedural PHP helpers used by `interface/`. Includes `library/classes/` for pre-PSR-4 classes, `library/ajax/`, `library/admin/`, ADODB logging wrapper. Referenced everywhere legacy code lives. | `library/classes/`, `library/ADODB_mysqli_log.php`, `library/api.inc.php`, `library/auth.inc.php`, `library/clinical_rules.php`. |
| 8 | `apis/` | **[CORE-DO-NOT-TOUCH]** | REST/FHIR API dispatch layer. `apis/dispatch.php` is the front controller (`ApiApplication`); `apis/routes/` contains the three route maps. | `apis/dispatch.php:20-30` `use OpenEMR\RestControllers\ApiApplication; $apiApplication->run($request);`. Route maps: `apis/routes/_rest_routes_standard.inc.php`, `_rest_routes_fhir_r4_us_core_3_1_0.inc.php`, `_rest_routes_portal.inc.php`. |
| 9 | `_rest_routes.inc.php` (repo root) | **[CORE-DO-NOT-TOUCH]** | Thin loader that assigns the three route maps in `apis/routes/` to `RestConfig::$ROUTE_MAP`, `$FHIR_ROUTE_MAP`, `$PORTAL_ROUTE_MAP`. Modules extend routes via the `RestApiCreateEvent` (see §2). | `_rest_routes.inc.php:32-36`. |
| 10 | `sql/` | **[CORE-DO-NOT-TOUCH]** | Baseline schema + versioned upgrade files (`X_Y_Z-to-A_B_C_upgrade.sql`). Any patch here bumps `$v_database` in `version.php`. Doctrine migrations under `db/` are not yet the source of truth (see `db/README.md`). | `sql/2_6_0-to-2_6_1_upgrade.sql` … `sql/*.sql` chain. |
| 11 | `sites/` | **[SAFE-EXTENSION-POINT]** | Multi-site root. One subdir per tenant (default: `sites/default/`) holds per-site DB creds, uploaded documents, LBF (Layout-Based Forms) definitions, letterhead, statement template — everything site-specific. | `sites/default/` contains `sqlconf.php`, `config.php`, `documents/`, `images/`, `LBF/`, `faxcover.txt`, `referral_template.html`, `statement.inc.php`, `clickoptions.txt`. |
| 12 | `templates/` | **[LEGACY]** | Smarty + Twig template roots for legacy pages. `.twig` files are the modern layer (284 across the repo per Phase 0); Smarty `.tpl`/`.html` are legacy. | Phase-0 file-ext count: 284 `.twig`, 523 `.html` (many are Smarty templates). |
| 13 | `portal/` | **[LEGACY]** (feature-alive) | Patient-facing portal — separate web-exposed entrypoints (`portal/index.php`, `portal/home.php`, `portal/messaging/`, `portal/patient/`, `portal/sign/`). Separate auth from the clinician side. | `portal/index.php`, `portal/verify_session.php`. Portal REST routes are in `apis/routes/_rest_routes_portal.inc.php`. |
| 14 | `ccdaservice/` | **[CORE-DO-NOT-TOUCH]** | Node.js microservice that generates C-CDA XML documents. Bundles `oe-blue-button-*` npm packages as vendored code under `ccdaservice/oe-blue-button-generate/`, `.../oe-blue-button-meta/`, `.../oe-blue-button-util/`, plus `serveccda.js` and `ccda_gateway.php` (the PHP bridge). Has its own `package.json`. | `ccdaservice/README.md`, `ccdaservice/serveccda.js`, `ccdaservice/ccda_gateway.php`. |
| 15 | `ccr/` | **[LEGACY]** | Continuity of Care Record (CCR XML) export — older HL7 export format, distinct from C-CDA. Standalone PHP scripts + XSLT. | `ccr/createCCR.php`, `ccr/ccr_view_retrieve.xsl`, `ccr/createCCRMedication.php`. |
| 16 | `oauth2/` | **[CORE-DO-NOT-TOUCH]** | OAuth2 authorize endpoint (`oauth2/authorize.php`) + `.htaccess`. Actual OAuth logic lives in `src/RestControllers/AuthorizationController.php` (called via `event_dispatcher` per grep of `$this->kernel->getEventDispatcher()`). | `oauth2/authorize.php`, `oauth2/.htaccess`. |
| 17 | `Documentation/` | **[DOCS]** | Bundled reference manuals + PDFs (Clinical Decision Rules, Vaccine listing, IPPF guides, Payment Posting, phpgacl README, EHI Export, API sub-tree). Includes `Documentation/api/`, `Documentation/help_files/`, `Documentation/images/`, `Documentation/privileged_db/`. Not exercised at runtime. | `Documentation/README.phpgacl`, `Documentation/EHI_Export/`, `Documentation/api/`. |
| 18 | `docker/` | **[BUILD/TOOLING]** | Docker stacks: `development-easy/` (canonical dev per `CLAUDE.md`), plus additional flavors. Not part of the shipped app tarball. | Referenced in `CLAUDE.md`; Phase-0 count 230 files. |
| 19 | `tests/` | **[BUILD/TOOLING]** | PHPUnit + Jest suites (`Tests/Unit`, `Tests/Isolated`, `Tests/Services`, `Tests/E2e`, `Tests/Fixtures`), plus custom PHPStan rules under `tests/PHPStan/Rules/`. | `phpunit.xml`, `phpunit-isolated.xml`, `phpunit.integration.xml`, `jest.config.js`, `tests/PHPStan/Rules/` (per `CLAUDE.md`). |
| 20 | `contrib/` | **[LEGACY]** (data-heavy) | Contributed content — vendored code sets (`icd9/`, `icd10/`, `snomed/`, `rxnorm/`, `cqm_valueset/`, `dsmiv/`), contributed `forms/`, portal templates, `venom/`, `util/`, plus `zirmed.tar.gz`. Some import scripts referenced by the installer. Not runtime-critical code that a customization would extend. | `contrib/icd10/`, `contrib/rxnorm/`, `contrib/cqm_valueset/`, `contrib/forms/`, `contrib/zirmed.tar.gz`. |
| 21 | `custom/` | **[LEGACY]** — misnamed as an extension point | Contrary to its name, this directory is **NOT** the recommended customization surface. It ships with concrete upstream scripts (`BillingExport.csv.php`, `chart_tracker.php`, `code_types.inc.php`, `export_qrda_xml.php`, `qrda_category1.inc.php`, `download_qrda.php`, etc.) tracked in git. Overwriting or adding here is a core edit. See §2 for the correct sanctioned surfaces. | 20 tracked files, e.g. `custom/qrda_category1.inc.php`, `custom/code_types.inc.php`, `custom/BillingExport.csv.php`, `custom/assets/`. |
| 22 | `gacl/` | **[LEGACY]** — third-party vendored | Vendored copy of the abandoned `phpGACL` ACL library (php-gacl) with local mods. Underlies OpenEMR's ACL layer (schema version `$v_acl=13` per `version.php`). New code should use `OpenEMR\Common\Acl\*` in `src/`; do not touch `gacl/` directly. | `gacl/README`, `gacl/AUTHORS`, `gacl/gacl.ini.php`, `gacl/admin/`, `Documentation/README.phpgacl`. |
| 23 | `public/` | **[CORE-DO-NOT-TOUCH]** | Web-exposed static assets — `public/assets/`, `public/images/`, `public/certs/`, `public/themes/` (webpack output target per `interface/README.md`). Some `public/index.php` shim. | `public/index.php`, `public/assets/`, `scripts/sync-css.js` (syncs to `public/themes/`). |
| 24 | `controllers/` | **[LEGACY]** | Very old MVC controllers (`C_Document.class.php`, `C_Prescription.class.php`, `C_Pharmacy.class.php`, `C_X12Partner.class.php`, `C_InsuranceCompany.class.php`, …). Pre-PSR-4 naming (`C_*.class.php`). Still routed to via `controller.php` at repo root. | `controllers/C_Document.class.php`, repo-root `controller.php`. |
| 25 | `config/` | **[CORE-DO-NOT-TOUCH]** | PHP + YAML app config: `app.php`, `services.php` (DI container), `psr.php`, `env.php`, `database.php`, `cli.php`, `audit.php`, `config.yaml`. Referenced by `src/Core/` bootstrap. | `config/services.php`, `config/config.yaml`, `config/README.md`. |
| 26 | `bin/` | **[BUILD/TOOLING]** / core CLI | CLI entry points: `bin/console` (Symfony console — see also root-level `cli`), `bin/command-runner`, plus dev scripts `generate-phpstan-types.php`, `get-v-database.php`, `refresh-reserved-word-supplement.php`. | `bin/console`, `bin/command-runner`. |
| 27 | `.github/` | **[BUILD/TOOLING]** | GitHub Actions workflows, issue/PR templates, Dependabot, actionlint config, custom `docker-byte-identical.yml` check, release-targets config, problem-matchers. | `.github/workflows/`, `.github/release-targets.yml`, `.github/docker-byte-identical.yml`, `.github/copilot-instructions.md`. |
| 28 | `ci/` | **[BUILD/TOOLING]** | CI PHP-version × MariaDB/MySQL matrix. Every `apache_XX_YY/` dir is one compose stack (e.g. `apache_82_118`, `apache_85_1011`, `apache_85_57`). Plus shared compose fragments (`compose-shared-apache.yml`, `compose-shared-mariadb.yml`, `compose-shared-mysql.yml`, `compose-shared-nginx/`, `compose-shared-redis-sentinel/`, `compose-shared-selenium/`, `compose-shared-mailpit/`) and `auto_prepend.php` for CI-injected coverage. | `ci/README.md`, `ci/apache_85_118/`, `ci/auto_prepend.php`, `ci/convert-coverage`. |
| 29 | `swagger/` | **[BUILD/TOOLING]** / [DOCS] | Vendored Swagger UI 5.x + `openemr-api.yaml` (the OpenAPI spec published at the `/swagger/` URL). Rebuilt copies of `swagger-ui-bundle.js`, `.css`, etc. | `swagger/index.html`, `swagger/openemr-api.yaml`, `swagger/swagger-ui-bundle.js`. |
| 30 | `webpack/` | **[BUILD/TOOLING]** | Webpack config helpers. Only `webpack/loaders/` and the root-level `webpack.themes.js`. | `webpack/loaders/`, `webpack.themes.js`. |
| 31 | `sphere/` | **[CORE-DO-NOT-TOUCH]** | Sphere payment-gateway callback handlers. Four PHP scripts that handle Sphere's cross-origin postback for credit-card payments while preserving OpenEMR CSRF/session. Not "SPA framework" or anything similar — it is a vendor integration namespace. | `sphere/initial_response.php:6-14` — file docblock: _"Special script to allow callback from Sphere to avoid cross origin breakage. Csrf security is maintained. Call to top.restoreSession() happens to ensure directed to correct session."_ Files: `initial_response.php`, `process_response.php`, `process_revert_response.php`, `token.php`. |
| 32 | `db/` | **[BUILD/TOOLING]** (aspirational core) | Doctrine Migrations scaffolding. Contains `db/Migrations/`, `db/migration-template.php.tpl`, `db/README.md`. **Not yet the source of truth** — see own README. Real schema changes still go through `sql/*_upgrade.sql`. | `db/README.md:10-12` — _"The Doctrine Migrations system is NOT fully integrated into OpenEMR yet. Don't make database changes using this until #10708 is completed."_ |
| 33 | `docs/` | **[DOCS]** | Modern dev docs (small, distinct from `Documentation/`): `RELEASE_PROCESS.md`, `docker-migration-from-devops.md`, `release-automation-plan.md`. Also the target dir for this Phase-0 audit's own reports. | `docs/RELEASE_PROCESS.md`, `docs/release-automation-plan.md`. |
| 34 | `meta/` | **[CORE-DO-NOT-TOUCH]** | Kubernetes/Docker health-probe endpoint. Single subdir `meta/health/` with `.htaccess` + `index.php`. | `meta/health/index.php:4-14` — file docblock: _"Health Check Entry Point — Provides liveness and readiness probe endpoints for Kubernetes and Docker."_ Uses `OpenEMR\Health\HealthChecker`. Contributed by OpenCoreEMR (2025). |
| 35 | `scripts/` | **[BUILD/TOOLING]** | Node build helpers: `scripts/install-assets.js` and `scripts/sync-css.js` (the latter is invoked by `npm run build:sync` per `CLAUDE.md`). | `scripts/sync-css.js`, `scripts/install-assets.js`. |
| 36 | `tools/` | **[BUILD/TOOLING]** | Miscellaneous developer utilities (19 files per Phase 0). Not on the runtime path. | Directory count from Phase 0 inventory. |
| 37 | `.phpstan/` | **[BUILD/TOOLING]** | PHPStan level-10 config surface: `phpstan.ci.neon`, `phpstan.github.neon`, `extension.neon`, `phpstan_include_paths.php`, `phpstan_legacy_aliases.php`, `phpstan_panther_alias.php`, `reset-baseline.php`, plus `baseline/` (177 files worth of legacy suppressions). | `.phpstan/README.md`, `.phpstan/baseline/`. |
| 38 | `.claude/` | **[BUILD/TOOLING]** | Claude Code local tooling (1 file per Phase 0). Local IDE integration only. | Phase-0 inventory. |

### Notes on paths already covered elsewhere

- `interface/forms/` and `interface/modules/*` are dedicated rows above so they carry their own tags.
- `_rest_routes.inc.php` at repo root is row 9.

---

## 2. Sanctioned Extension Points

### 2.1 `interface/modules/custom_modules/` — **primary modern extension surface**

Structure of a real module (`oe-module-faxsms/` as reference implementation):

| File | Role |
|------|------|
| `ModuleManagerListener.php` | Called by OpenEMR's Module Manager on install/enable/disable/uninstall. |
| `openemr.bootstrap.php` | Registered event subscribers — the module's "main". |
| `moduleConfig.php` | Config UI wiring. |
| `info.txt` | Module manifest (name, version, description). |
| `README.md` / `README-SETUP.md` / `README-GUIDE.md` | Human docs. |
| `src/` | PSR-4 code (namespace declared in the module's own `composer.json`). |
| `public/` | Web-exposed assets. |
| `library/` (optional) | Module-local legacy helpers. |
| `.htaccess` | URL protection. |

`interface/modules/custom_modules/README.md` itself is a one-liner (`Custom Modules`) — there is no in-tree developer guide. The canonical developer surface is discovered by copying an existing `oe-module-*`.

### 2.2 `interface/forms/<name>/` — sanctioned surface for new **encounter forms**

Fixed skeleton (verified against `interface/forms/note/`):

```
info.txt         # form title / metadata
new.php          # blank-form entry
save.php         # POST handler
view.php         # existing-form editor
report.php       # per-form section in encounter/patient report
print.php        # print rendering
table.sql        # form's own DB table(s), applied at install time
```

Adding a new subdir here is the intended way to add a clinical form. **Modifying existing subdirs is a core edit** — those forms ship with upstream.

### 2.3 Symfony EventDispatcher — the modern hook system

- Instantiated in the OpenEMR **Kernel**: `src/Core/Kernel.php:60-72`. Kernel builds a Symfony `ContainerBuilder`, registers a `RegisterListenersPass`, defines `event_dispatcher` as an `EventDispatcher` service, optionally accepts a pre-built dispatcher via constructor.
- Accessed globally as `OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()`. Grep shows this pattern in 100+ locations under `src/Services/`, `src/Patient/Cards/`, `src/Menu/`, `src/RestControllers/`, `src/Events/`.
- Base service infrastructure exposes it: `src/Services/BaseService.php:72,85` (`getEventDispatcher()`), `src/Services/BaseServiceInterface.php:24-26`.
- **Event classes** live under `src/Events/**` — 22+ subject areas: `Appointments/`, `Billing/`, `CDA/`, `Codes/`, `Command/`, `Core/`, `Encounter/`, `Facility/`, `Globals/`, `Main/`, `Messaging/`, `Patient/`, `PatientDemographics/`, `PatientDocuments/`, `PatientFinder/`, `PatientPortal/`, `PatientReport/`, `PatientSelect/`, `RestApiExtend/`, `Services/`, `User/`, `UserInterface/`. Plus root `AbstractBoundFilterEvent.php`, `BoundFilter.php`.
- Notable REST-extending events: `src/Events/RestApiExtend/` — dispatched from `src/RestControllers/Finder/StandardRouteFinder.php:37`, `src/RestControllers/FHIR/Finder/FhirRouteFinder.php:31`, `src/RestControllers/Finder/PortalRouteFinder.php:33` (all fire `RestApiCreateEvent::EVENT_HANDLE`). This is **how modules register new REST/FHIR endpoints without editing `apis/routes/*`**.
- REST pipeline subscribers (built-in): `src/RestControllers/Subscriber/*` — `AuthorizationListener`, `CORSListener`, `ApiResponseLoggerListener`, `TelemetryListener`, `SessionCleanupListener`, `SiteSetupListener`, `OAuth2AuthorizationListener`, `ExceptionHandlerListener`, `RoutesExtensionListener`, `ViewRendererListener`.

### 2.4 Legacy hook system

Grep for `HookEvent`, `invokeHook`, `register_hook` under `library/` returns **no matches**. There is no separate pre-Symfony hook mechanism to worry about; legacy code that needs to be extensible has been migrated (or is expected to be migrated) to the Symfony EventDispatcher above. Older Zend module hooks would live under `interface/modules/zend_modules/` if used.

### 2.5 `sites/<site>/` — per-site overrides

`sites/default/` inventory (all upgrade-safe per-tenant surface):

| Path | Purpose |
|------|---------|
| `sites/default/sqlconf.php` | Per-site DB creds (host, port, user, pass, dbname). |
| `sites/default/config.php` | Per-site PHP config (print command, HylaFAX enscript, OFX bank/acct id, prescription format, `oer_config['documents']['repopath']`, etc.). |
| `sites/default/documents/` | Uploaded patient documents blob store (path resolved at runtime via `$GLOBALS['OE_SITE_DIR']`). |
| `sites/default/images/` | Practice logo, letterhead artwork. |
| `sites/default/LBF/` | Layout-Based Forms definitions (site-authored dynamic forms). |
| `sites/default/faxcover.txt`, `faxtitle.eps` | Fax cover-page templates. |
| `sites/default/referral_template.html` | Referral letter template. |
| `sites/default/statement.inc.php` | Billing-statement template. |
| `sites/default/clickoptions.txt` | Configurable click-option lists. |
| `sites/default/docker-version` | Version marker for docker-built sites. |

Adding a new tenant = copy `sites/default/` to `sites/<newtenant>/`, edit `sqlconf.php`.

### 2.6 Composer-installed modules

From `composer.json` (`git show HEAD:composer.json | Select-String oe-module|claimrevolution`):

- **Runtime dep:** `claimrevolution/oe-module-claimrev-connect: ^2.1` — installed via Composer.
- **VCS repository:** `https://github.com/openemr/oe-module-cqm` — declared as a Composer repository, indicating opt-in installation of the CQM module.
- **Installer plugin:** `openemr/oe-module-installer-plugin` — allow-listed under `config.allow-plugins`. This is the Composer plugin that copies installed modules into `interface/modules/custom_modules/`.

The seven `oe-module-*` directories under `interface/modules/custom_modules/` (comlink-telehealth, dashboard-context, dorn, ehi-exporter, faxsms, prior-authorizations, weno) are **tracked in git** in this fork — they are vendored in-tree rather than Composer-installed at build time. Fresh installs of the same modules would land at the same paths via `oe-module-installer-plugin`.

### 2.7 REST/FHIR route extension

Two entry points to add API endpoints:

1. **Core-edit path (NOT safe):** append to `apis/routes/_rest_routes_standard.inc.php` / `_rest_routes_fhir_r4_us_core_3_1_0.inc.php` / `_rest_routes_portal.inc.php`.
2. **Extension path (safe):** subscribe to `RestApiCreateEvent::EVENT_HANDLE` from a custom module. Fired by `StandardRouteFinder`, `FhirRouteFinder`, `PortalRouteFinder` — all three finders let subscribers register routes at dispatch time. Corresponding event classes under `src/Events/RestApiExtend/`.

---

## 3. Ranked Extension Mechanisms

| Mechanism | Upgrade-safe? | Evidence | When to use |
|-----------|---------------|----------|-------------|
| **Custom module under `interface/modules/custom_modules/oe-module-<name>/`** | ✅ Yes — designed for this | 7 real modules co-existing; Composer `openemr/oe-module-installer-plugin`; `ModuleManagerListener.php` + `openemr.bootstrap.php` skeleton | Any net-new feature: new pages, new services, new subscribers, new REST endpoints, third-party integrations. Default choice. |
| **Symfony event subscriber (from within a module)** | ✅ Yes | `src/Core/Kernel.php:60-72`; `src/Events/**` (22 subject areas); 100+ dispatch sites | Extending or filtering existing behavior — patient CRUD hooks (`PatientCreatedEvent`, `PatientUpdatedEvent`), menu injection (`MenuEvent`, `PatientMenuEvent`), FHIR resource decoration, CDA generation, REST-route registration, service pre/post-save (`ServiceSaveEvent`, `ServiceDeleteEvent`). |
| **REST/FHIR API layer wrapper (module subscribes to `RestApiCreateEvent`)** | ✅ Yes | `src/RestControllers/Finder/StandardRouteFinder.php:37`, `.../FHIR/Finder/FhirRouteFinder.php:31`, `.../Finder/PortalRouteFinder.php:33` | Adding endpoints without touching `_rest_routes*.inc.php`. |
| **New encounter form at `interface/forms/<myform>/`** | ✅ Yes for new subdirs, ❌ for edits to shipped ones | 5-file skeleton verified in `interface/forms/note/`; ~45 shipped forms follow identical shape | Clinical-form additions that need to appear inside encounters and reports. |
| **Per-site override at `sites/<site>/`** | ✅ Yes — that's its entire purpose | `sites/default/config.php`, `sqlconf.php`, `LBF/`, `documents/`, `statement.inc.php`, `referral_template.html`, `faxcover.txt` | Tenant-specific config, credentials, templates, LBFs, document storage. Never for code. |
| **Layout-Based Forms (LBF) under `sites/<site>/LBF/` + Layout Editor UI** | ✅ Yes | `sites/default/LBF/` (per-site tree) | Data-driven forms editable via admin UI — no code, no deployment. |
| **DB-driven config: `list_options`, `globals`, `user_settings` tables** | ✅ Yes | Runtime tables surfaced via `OEGlobalsBag`; docs in `CLAUDE.md` (§"Global settings") | User- or site-scoped preferences without file edits. |
| **Composer dependency (`claimrevolution/oe-module-claimrev-connect` pattern)** | ✅ Yes | `composer.json` runtime `require` block | Distributing a module for install via `composer require` rather than vendoring. |
| **Zend/Laminas module under `interface/modules/zend_modules/module/<name>/`** | ⚠️ Legacy — works but discouraged | `interface/modules/zend_modules/{config,module,public}/` exists | Only if extending an already-Zend-based subsystem; new modules should use `custom_modules/`. |
| **New file under `custom/`** | ❌ NOT upgrade-safe (despite the name) | 20 upstream-shipped scripts already tracked here (`custom/qrda_*.php`, `custom/BillingExport.csv.php`, `custom/code_types.inc.php`, `custom/chart_tracker.php`, …) | Do not use. Name is misleading; treat as core. |
| **Doctrine migration under `db/Migrations/`** | ⚠️ Not yet — aspirational | `db/README.md:10-12` explicit warning; use `sql/*_upgrade.sql` until upstream issue #10708 closes | Do not use for real changes yet. Watch for readiness. |
| **Direct edit of `src/`, `library/`, `interface/`, `apis/`, `_rest_routes.inc.php`, `sql/*.sql`, `controllers/`, `ccdaservice/`, `ccr/`, `gacl/`, `templates/`, `portal/`, `public/`, `oauth2/`, `sphere/`, `meta/`, `config/`, `bin/`** | ❌ NOT upgrade-safe | Every one of these carries file-header `@copyright` upstream authorship and receives upstream churn | Only when contributing a patch back to `openemr/openemr`. Otherwise, fork = permanent merge cost. |

---

## Report Summary

- Produced: `docs/00-discovery/03-directory-map.md`.
- Classified all 38 top-level surfaces with evidence. `sphere/` = Sphere payment gateway callbacks; `db/` = not-yet-live Doctrine Migrations scaffold; `meta/` = k8s health probe (2025 OpenCoreEMR contribution); `apis/` = REST dispatcher + three route-map files.
- Sanctioned extension points documented: `interface/modules/custom_modules/*` (primary), `interface/forms/<new>/`, Symfony EventDispatcher (Kernel at `src/Core/Kernel.php:60-72`, events under `src/Events/**`, dispatch via `OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()`), `sites/<site>/` per-tenant overrides, and Composer-installed `oe-module-*` packages (installer plugin `openemr/oe-module-installer-plugin`, live example `claimrevolution/oe-module-claimrev-connect`).
- Key myth-busts: `custom/` is not a customization surface (ships upstream code); `db/` migrations are not yet live per its own README; there is no separate legacy hook system — Symfony EventDispatcher is the only one.
- Ranked extension mechanism table produced at §3.

## UNKNOWNs

- **Distance from `openemr/openemr` upstream** — carried from `01-repo-inventory.md`; no `upstream` remote configured, so we cannot tell whether the seven `oe-module-*` dirs in this fork are byte-identical to upstream or fork-modified. Requires product-owner approval to `git remote add upstream https://github.com/openemr/openemr.git && git fetch upstream`.
- **`tools/` contents** — 19 files, not opened in this pass. Classified `[BUILD/TOOLING]` on the basis of directory name and Phase-0 inventory; a file-by-file confirmation is deferred to a later phase.
- **Which `oe-module-*` are vendored vs. composer-installed at runtime** — all seven are tracked in git in this fork, but `composer.json` lists only `claimrevolution/oe-module-claimrev-connect` as a runtime `require`. Whether the other six are intended to be composer-installed on top (and would overlay tracked files) requires product-owner input on the fork's install workflow.
