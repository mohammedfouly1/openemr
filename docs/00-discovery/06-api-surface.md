# Phase 6 — API Surface

Read-only archaeological inventory of every HTTP API surface exposed by this OpenEMR fork. Cross-references Phase 2 (`03-directory-map.md`), Phase 4 (`05-auth-and-acl.md`), Phase 7 (`08-billing-claims-insurance.md`), and Phase 8 (`09-frontend-ui.md`). Every claim below cites `file:line`.

---

## 1. Route-map file inventory

`git ls-files apis/` returns:

| Path | Purpose | Line count of route entries |
|------|---------|-----------------------------|
| `apis/.htaccess` | Apache rewrite front-controller shim (all traffic → `dispatch.php`) | — |
| `apis/dispatch.php` | Entry point: builds `HttpRestRequest`, hands off to `ApiApplication` (`apis/dispatch.php:27-30`) | 45 lines total |
| `apis/routes/_rest_routes_standard.inc.php` | Standard REST (`/api/...`) | **96** route entries (Select-String on `^\s*"(GET|POST|PUT|PATCH|DELETE)`) |
| `apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php` | FHIR R4 (`/fhir/...`) | **71** route entries |
| `apis/routes/_rest_routes_portal.inc.php` | Patient portal (`/portal/...`) | **5** route entries |

Route maps are `include`d — not by `dispatch.php` directly — but by three **route-finder services** invoked from inside the kernel:

- `src/RestControllers/Finder/StandardRouteFinder.php:31` — `include __DIR__ . '/../../../apis/routes/_rest_routes_standard.inc.php';`
- `src/RestControllers/FHIR/Finder/FhirRouteFinder.php:25` — includes the FHIR file
- `src/RestControllers/Finder/PortalRouteFinder.php:27` — includes the portal file
- `src/RestControllers/FHIR/FhirMetaDataRestController.php:52` — the FHIR CapabilityStatement generator **re-includes** the FHIR route map to enumerate resources
- `src/Common/Command/CreateAPIDocumentationCommand.php:62-65` — Symfony console command that scans `apis/routes` + `src/RestControllers` for OpenAPI attributes

Each route file `return`s an associative array whose keys are `"<VERB> <path-pattern>"` (colon-prefixed URL params, e.g. `":puuid"`) and whose values are `\Closure(...routeParams, HttpRestRequest, OEGlobalsBag): mixed`.

---

## 2. Dispatcher classes & flow

- **`apis/dispatch.php:26-30`** — bootstraps `HttpRestRequest::createFromGlobals()`, instantiates `ApiApplication`, calls `->run($request)`.
- **`src/RestControllers/ApiApplication.php:71-135`** — orchestrates a **Symfony HttpKernel** (`OEHttpKernel`) with 11 event subscribers (Exception, Telemetry, ApiResponseLogger, SessionCleanup, SiteSetup, CORS, OAuth2Authorization, Authorization, RoutesExtension, ViewRenderer). Contrary to the Phase 2 summary describing a "bespoke router not Symfony HttpKernel," this fork **does** wrap Symfony's HttpKernel — the bespoke element is the route-matching layer.
- **`src/RestControllers/Subscriber/SiteSetupListener.php:41-52,99-109`** — extracts `<siteId>` from the leading path segment (`/apis/<site>/...`); defaults to `default`. Rejects invalid site IDs with HTTP error.
- **`src/RestControllers/Subscriber/AuthorizationListener.php:93-100`** — builds an authorization strategy chain (`SkipAuthorizationStrategy`, `BearerTokenAuthorizationStrategy`, local-API strategies) and short-circuits on `isLocalApi()` or `skipAuthorization` request attribute (lines 137-156).
- **`src/Common/Http/HttpRestRouteHandler.php:40-98`** — iterates the loaded routes array, matches via `HttpRestParsedRoute`, runs `checkSecurity(...)`, sets `_controller` attribute to `fn() => $routeCallback(...$params)` for the kernel's `ControllerResolver` to invoke.
- **`src/Common/Http/HttpRestRequest.php:88,109,577-629`** — augmented Symfony `Request` carrying `requestSite`, `apiType` (`fhir`|`oemr`|`port`), OAuth client id, user UUID+role, resource, operation, patient-context flag.
- **`src/RestControllers/RestControllerHelper.php`** — shared response-shaping helpers (JSON envelope, validation errors, FHIR resource-list construction; used by every REST controller).

**End-to-end flow (5 hops):** `apis/.htaccess` → `apis/dispatch.php:29` → `ApiApplication::run()` → `OEHttpKernel::handle()` → subscribers set `_controller` via `HttpRestRouteHandler::dispatch()` → `ControllerResolver` invokes the route closure → `ViewRendererListener` serializes the return value into an HTTP response.

---

## 3. Standard REST endpoints (`/apis/default/api/...`)

Extracted via `Select-String` on `apis/routes/_rest_routes_standard.inc.php`. **No** endpoint declares an HTTP `PATCH` verb; where a controller's internal method is named `patch(...)` it is invoked from a `PUT` route (RFC-noncompliant partial-update-via-PUT). Confirmed at `apis/routes/_rest_routes_standard.inc.php:69-75` (`PUT /api/facility/:fuuid` → `FacilityRestController->patch(...)`) and line 205 (`PUT /api/practitioner/:pruuid` → `PractitionerRestController->patch(...)`).

| Resource | HTTP methods present | Routes | Controller |
|---|---|---|---|
| Facility | GET (list, one), POST, PUT | `/api/facility`, `/api/facility/:fuuid` | `FacilityRestController` |
| Patient | GET (list, one), POST, PUT | `/api/patient`, `/api/patient/:puuid` | `PatientRestController` |
| Encounter | GET (list, one), POST, PUT | `/api/patient/:puuid/encounter[/:euuid]` | `EncounterRestController` |
| SOAP note | GET, POST, PUT | `/api/patient/:pid/encounter/:eid/soap_note[/:sid]` | `EncounterRestController` (soap sub-actions) |
| Vital | GET, POST, PUT | `/api/patient/:pid/encounter/:eid/vital[/:vid]` | `EncounterRestController` |
| Practitioner | GET, POST, PUT | `/api/practitioner[/:pruuid]` | `PractitionerRestController` |
| Medical Problem (Condition) | GET, POST, PUT, DELETE | `/api/medical_problem[/:muuid]`, `/api/patient/:puuid/medical_problem[/:muuid]` | `ConditionRestController` |
| Allergy | GET, POST, PUT, DELETE | `/api/allergy[/:auuid]`, `/api/patient/:puuid/allergy[/:auuid]` | `AllergyIntoleranceRestController` |
| Medication | GET, POST, PUT, DELETE | `/api/patient/:pid/medication[/:mid]` | `MedicationRestController` |
| Surgery | GET, POST, PUT, DELETE | `/api/patient/:pid/surgery[/:sid]` | `SurgeryRestController` |
| Dental Issue | GET, POST, PUT, DELETE | `/api/patient/:pid/dental_issue[/:did]` | `DentalIssueRestController` |
| Appointment | GET, POST, DELETE | `/api/appointment[/:eid]`, `/api/patient/:pid/appointment[/:eid]` | `AppointmentRestController` |
| List (option-set) | GET | `/api/list/:list_name` | `ListRestController` |
| User | GET | `/api/user[/:uuid]` | `UserRestController` |
| Version / Product | GET | `/api/version`, `/api/product` (public — see §7) | `VersionRestController` / `ProductRegistrationRestController` |
| Insurance Company | GET, POST, PUT | `/api/insurance_company[/:iid]` | `InsuranceCompanyRestController` |
| Insurance Type | GET | `/api/insurance_type` | (list endpoint) |
| Patient Insurance | GET, POST, PUT | `/api/patient/:puuid/insurance[/:uuid]` | `InsuranceRestController` |
| Employer | GET | `/api/patient/:puuid/employer` | `EmployerRestController` |
| Document | GET, POST | `/api/patient/:pid/document[/:did]` | `DocumentRestController` |
| Message (pnote) | POST, PUT, DELETE | `/api/patient/:pid/message[/:mid]` | `MessageRestController` |
| Transaction | GET, POST, PUT | `/api/patient/:pid/transaction`, `/api/transaction/:tid` | `TransactionRestController` |
| Immunization | GET | `/api/immunization[/:uuid]` | `ImmunizationRestController` |
| Procedure | GET | `/api/procedure[/:uuid]` | `ProcedureRestController` |
| Drug | GET | `/api/drug[/:uuid]` | `DrugRestController` |
| Prescription | GET, POST, DELETE | `/api/prescription[/:uuid]` | `PrescriptionRestController` |
| Background Service | GET, POST | `/api/background_service[/:name]`, `/api/background_service/:name/run` | `BackgroundServiceRestController` |

**PATCH availability (HTTP verb): none.** Partial updates are folded into PUT semantics.

---

## 4. FHIR endpoints (`/apis/default/fhir/...`)

**FHIR version: R4 (4.0.1)** — set at `src/RestControllers/FHIR/FhirMetaDataRestController.php:59-61` (`$fhirVersion->setValue("4.0.1")`). CapabilityStatement declares conformance to US Core server: `->addInstantiates('http://hl7.org/fhir/us/core/CapabilityStatement/us-core-server')` (line 55). The routes file name pins US Core: `_rest_routes_fhir_r4_us_core_3_1_0.inc.php`.

| Resource | read | search-type | create | update | patch | delete | Operations | Controller |
|---|:-:|:-:|:-:|:-:|:-:|:-:|---|---|
| AllergyIntolerance | ✓ | ✓ | — | — | — | — | — | `FhirAllergyIntoleranceRestController` |
| Appointment | ✓ | ✓ | — | — | — | — | — | `FhirAppointmentRestController` |
| CarePlan | ✓ | ✓ | — | — | — | — | — | `FhirCarePlanRestController` |
| CareTeam | ✓ | ✓ | — | — | — | — | — | `FhirCareTeamRestController` |
| Condition | ✓ | ✓ | — | — | — | — | — | `FhirConditionRestController` |
| **Coverage** | ✓ | ✓ | — | — | — | — | — | `FhirCoverageRestController` (methods: only `getAll` L110, `getOne` L189 — confirmed Phase 7) |
| Device | ✓ | ✓ | — | — | — | — | — | `FhirDeviceRestController` |
| DiagnosticReport | ✓ | ✓ | — | — | — | — | — | `FhirDiagnosticReportRestController` |
| DocumentReference | ✓ | — | — | — | — | — | `$docref` (POST) | `FhirDocumentReferenceRestController` + `FhirOperationDocRefRestController` |
| Encounter | ✓ | ✓ | — | — | — | — | — | `FhirEncounterRestController` |
| Goal | ✓ | ✓ | — | — | — | — | — | `FhirGoalRestController` |
| Group | ✓ (id only) | — | — | — | — | — | `$export` (GET `/Group/:id/$export`) | `FhirGroupRestController` + `FhirOperationExportRestController` |
| Immunization | ✓ | ✓ | — | — | — | — | — | `FhirImmunizationRestController` |
| Location | ✓ | ✓ | — | — | — | — | — | `FhirLocationRestController` |
| Media | ✓ | ✓ | — | — | — | — | — | `FhirMediaRestController` |
| Medication | ✓ | ✓ | — | — | — | — | — | `FhirMedicationRestController` |
| MedicationDispense | ✓ | ✓ | — | — | — | — | — | `FhirMedicationDispenseRestController` |
| MedicationRequest | ✓ | ✓ | — | — | — | — | — | `FhirMedicationRequestRestController` |
| Observation | ✓ | ✓ | — | — | — | — | — | `FhirObservationRestController` |
| **Organization** | ✓ | ✓ | ✓ (POST) | ✓ (PUT) | — | — | — | `FhirOrganizationRestController` (PUT internally routes to `->patch(...)` L556) |
| **Patient** | ✓ | ✓ | ✓ (POST) | ✓ (PUT) | — | — | `$export` (system + per-Patient) | `FhirPatientRestController` |
| Person | ✓ | ✓ | — | — | — | — | — | `FhirPersonRestController` |
| **Practitioner** | ✓ | ✓ | ✓ (POST) | ✓ (PUT) | — | — | — | `FhirPractitionerRestController` (PUT → `->patch(...)` L687) |
| PractitionerRole | ✓ | ✓ | — | — | — | — | — | `FhirPractitionerRoleRestController` |
| Procedure | ✓ | ✓ | — | — | — | — | — | `FhirProcedureRestController` |
| Provenance | ✓ | ✓ | — | — | — | — | — | `FhirProvenanceRestController` |
| Questionnaire | ✓ (list only) | — | — | — | — | — | — | `FhirQuestionnaireRestController` |
| QuestionnaireResponse | ✓ | ✓ | — | — | — | — | — | `FhirQuestionnaireResponseRestController` |
| RelatedPerson | ✓ | ✓ | — | — | — | — | — | `FhirRelatedPersonRestController` |
| ServiceRequest | ✓ | ✓ | — | — | — | — | — | `FhirServiceRequestRestController` |
| Specimen | ✓ | ✓ | — | — | — | — | — | `FhirSpecimenRestController` |
| ValueSet | ✓ | ✓ | — | — | — | — | — | `FhirValueSetRestController` |
| OperationDefinition | ✓ | ✓ | — | — | — | — | — | `FhirOperationDefinitionRestController` |

**System-level operations** (from `apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php`):

| Verb | Path | Purpose | Line |
|---|---|---|---|
| GET | `/fhir/metadata` | CapabilityStatement | 830s |
| GET | `/fhir/.well-known/smart-configuration` | SMART discovery | 830s |
| GET | `/fhir/OperationDefinition[/:operation]` | Operation catalog | 830s |
| POST | `/fhir/DocumentReference/$docref` | US Core `$docref` | 259 |
| GET | `/fhir/Group/:id/$export` | Bulk Data — group | 366 |
| GET | `/fhir/Patient/$export` | Bulk Data — patient | 598 |
| GET | `/fhir/$export` | Bulk Data — system | 842 |
| GET | `/fhir/$bulkdata-status` | Bulk Data async status | 858 |
| DELETE | `/fhir/$bulkdata-status` | Cancel bulk export job | 868 |

**Write-capable FHIR resources: only `Organization`, `Patient`, `Practitioner`** (POST + PUT). Everything else is **read-only**. **No HTTP PATCH.** **No FHIR DELETE for any clinical resource.**

---

## 5. US Core / IG conformance

- CapabilityStatement instantiates `http://hl7.org/fhir/us/core/CapabilityStatement/us-core-server` (`src/RestControllers/FHIR/FhirMetaDataRestController.php:55`).
- Route-map filename pins the version: **US Core 3.1.0** (`_rest_routes_fhir_r4_us_core_3_1_0.inc.php`).
- No `USCDI` string or newer US Core version (4.x/5.x/6.x/7.x) declared. UNKNOWN whether more-recent US Core profiles are honored by the underlying services (would require deeper trace).
- No non-US IG (e.g. IPS, IHE, **NPHIES**, Bahmni) declared.

---

## 6. Patient portal API

Complete enumeration from `apis/routes/_rest_routes_portal.inc.php:29-49` — **exactly 5 routes**, all `GET`, all patient-scoped and driven by `$request->getPatientUUIDString()`:

| Verb | Path | Controller call | Line |
|---|---|---|---|
| GET | `/portal/patient` | `PatientRestController::getOne(patientUuid)` | 29-32 |
| GET | `/portal/patient/encounter` | `EncounterRestController::getAll(patientUuid)` | 33-36 |
| GET | `/portal/patient/encounter/:euuid` | `EncounterRestController::getOne(patientUuid, euuid)` | 37-40 |
| GET | `/portal/patient/appointment` | `AppointmentRestController::getAllForPatientByUuid(patientUuid)` | 41-44 |
| GET | `/portal/patient/appointment/:auuid` | `AppointmentRestController::getOneForPatient(auuid, patientUuid)` | 45-48 |

Only-patient-role enforcement is noted at line 26-27 of the same file.

---

## 7. Auth per route

Authorization is layered:

1. **`SkipAuthorizationStrategy`** — explicit allow-list, registered in `src/RestControllers/Subscriber/AuthorizationListener.php:93-100`. Public (unauthenticated) endpoints:
   - `/fhir/metadata`
   - `/fhir/.well-known/smart-configuration`
   - `/fhir/OperationDefinition`
   - `/api/version`
   - `/api/product`
   - Also `/.well-known/openid-configuration` handled separately in `OAuth2AuthorizationListener.php:129`.
2. **OAuth2 bearer** — enforced for every other `/fhir/...` and `/api/...` route. See Phase 4 (`05-auth-and-acl.md`) for token issuance, JWKS, PKCE, SMART launch, introspection.
3. **Legacy session (local-API)** — `HttpRestRequest::isLocalApi()` / `attributes.skipAuthorization` short-circuit the bearer check (`AuthorizationListener.php:154`). Used by internal server-to-server invocations from PHP UI code.
4. **ACL (per-controller)** — after auth, each standard-REST route closure calls `RestConfig::request_authorization_check($request, "<section>", "<value>")` (`src/RestControllers/Config/RestConfig.php:180`); e.g. `apis/routes/_rest_routes_standard.inc.php:70` requires `admin/super` for facility mutation, line 77 requires `patients/demo`. This is the same PHP-GACL surface documented in Phase 4.
5. **Portal endpoints** — require an `api:port` scope plus patient-role session; only patient-scoped data is accessible (patient UUID pulled from session, not request).

No public/anonymous `/health` endpoint exists in this codebase. `/api/version` doubles as a liveness probe.

---

## 8. OAuth2 scopes enumerated

From `src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php:246-251` (and constants used across `AuthorizationController` / `BearerTokenAuthorizationStrategy`):

**Site scopes (API surface selectors, OpenEMR-specific):**
- `api:oemr` — permission to use the standard REST API (line 249)
- `api:fhir` — permission to use the FHIR API (line 250)
- `api:port` — permission to use the portal API (line 251)

**OIDC / OAuth2 base scopes:**
- `openid`, `profile`, `email`, `address`, `phone`, `fhirUser`
- `offline_access` — refresh-token issuance (line 246)
- `launch`, `launch/patient`, `launch/encounter` — SMART on FHIR

**SMART resource scopes** — pattern `patient/<Resource>.read`, `user/<Resource>.read`, `system/<Resource>.read|write|*` for every FHIR resource enumerated in §4. Enforcement in `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php:343-369`; per-request scope-context resolution in `HttpRestRouteHandler.php:65` (`$dispatchRestRequest->getScopeContextForResource(...)`). Cross-reference Phase 4 for the full SMART scope grammar.

---

## 9. API versioning / URL structure

Dispatched via `apis/dispatch.php` (mapped by `apis/.htaccess`). Path grammar resolved in `src/RestControllers/Subscriber/SiteSetupListener.php:41-52` and `src/Common/Http/HttpRestRequest.php:610-620`:

```
/apis/<site>/api/<resource>...       → apiType = 'oemr'   (standard REST)
/apis/<site>/fhir/<Resource>...      → apiType = 'fhir'   (FHIR R4)
/apis/<site>/portal/<...>            → apiType = 'port'   (portal)
```

- `<site>` defaults to `default` (`HttpRestRequest.php:88`) and is validated against `sites/<siteId>/` on disk (`SiteSetupListener.php:49`).
- **There is no numeric API version segment (no `/v1/`, no `/v2/`).** All versioning is implicit in the resource shape and in the pinned US Core route-map filename. Breaking-change strategy: UNKNOWN — requires product-owner input.

---

## 10. Rate-limit / pagination / search parameters

- **Rate limiting:** No repo-wide `rate_limit` or `throttle` implementation was found in `src/` or `library/` (repo-wide grep returned only `src/Common/Session/SessionTracker.php`, unrelated). Assumed to be handled at the reverse-proxy layer, not application-layer. **NPHIES gap:** UNKNOWN — no application-tier rate limiter present.
- **Pagination:** Standard `_offset` / `_count` on FHIR search is provided by `RestControllerHelper` / the FHIR search infrastructure (`src/Services/FHIR/*Service.php`); Bundle `link` entries constructed in the common helper. Bulk Data uses the async status endpoint (`GET /fhir/$bulkdata-status`) at `apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php:858`.
- **Search parameters** are declared per-service in `loadSearchParameters()` methods across 36 files in `src/Services/FHIR/*Service.php`. Example — `src/Services/FHIR/FhirCoverageService.php:97-102` exposes only `patient`, `payor`, `type`. Full per-resource enumeration deferred (36 files); the CapabilityStatement (`GET /fhir/metadata`) is the authoritative runtime source.

---

## 11. OpenAPI / Swagger

- `swagger/` directory contains: Swagger-UI JS/CSS bundle **and** `swagger/openemr-api.yaml` (340 560 bytes — the generated spec).
- Generation: **auto-generated from PHP 8 attributes** using `zircote/swagger-php ^6.0` (declared at `composer.json:136`).
- Generator: `src/Common/Command/CreateAPIDocumentationCommand.php` (Symfony console command). Scans `_rest_routes.inc.php`, `apis/routes`, `src/RestControllers` (`lines 62-65`) using `OpenApi\Generator` + `ReflectionAnalyser` + `AttributeAnnotationFactory` (lines 14-16, 48-65).
- The generated YAML is committed to the repo (not built on the fly). Attributes are visible in every REST controller — e.g. `AllergyIntoleranceRestController.php` lines 22-286 are dense `#[OA\...]` declarations.

---

## 12. NPHIES-relevant FHIR gap analysis

For each financial resource required by NPHIES (Saudi national health information exchange) — search performed across `src/RestControllers/**`:

| Resource | Data class (`src/FHIR/R4/`) | REST controller | Route(s) | Status |
|---|:-:|:-:|:-:|---|
| **Coverage** | present | `FhirCoverageRestController` | `GET /fhir/Coverage[/:uuid]` (`_rest_routes_fhir_...inc.php`) | **READ-ONLY** — only `getAll` (L110) and `getOne` (L189); no POST/PUT/DELETE |
| **Claim** | `src/FHIR/R4/FHIRDomainResource/FHIRClaim.php` present (schema only) | **NOT PRESENT** | — | **NOT PRESENT** — no controller, no route |
| **ClaimResponse** | `FHIRClaimResponse.php` present | **NOT PRESENT** | — | **NOT PRESENT** |
| **ExplanationOfBenefit** | UNKNOWN (not searched) | **NOT PRESENT** | — | **NOT PRESENT** — grep for controller returned zero results |
| **CoverageEligibilityRequest** | UNKNOWN | **NOT PRESENT** | — | **NOT PRESENT** |
| **CoverageEligibilityResponse** | UNKNOWN | **NOT PRESENT** | — | **NOT PRESENT** |
| **PaymentNotice** | UNKNOWN | **NOT PRESENT** | — | **NOT PRESENT** |

Cross-reference Phase 7 (`08-billing-claims-insurance.md`): the billing pipeline is X12 837/835-oriented (`src/Billing/*`), not FHIR-financial-oriented.

---

## 13. Findings — FHIR surface as a NPHIES foundation

The FHIR R4 surface in this fork is a **read-heavy, US Core 3.1.0-scoped implementation** built around ONC certification concerns (Bulk Data `$export`, `$docref`, SMART on FHIR). Of the 32 FHIR resource endpoints exposed, **29 are read-only**; only `Patient`, `Practitioner`, and `Organization` accept `POST`/`PUT`. No resource accepts HTTP `PATCH` or `DELETE`. The Symfony HttpKernel wrapper, closure-based route table, OAuth2/SMART enforcement, per-resource `FhirService` classes with declared search parameters, and auto-generated OpenAPI spec together constitute a **serviceable scaffold** on which additional resource controllers can be added without changing the framework layer.

Against NPHIES's transactional financial-resource requirements the surface is **skeletal**: `Coverage` exists but is read-only; `Claim`, `ClaimResponse`, `ExplanationOfBenefit`, `CoverageEligibilityRequest`, `CoverageEligibilityResponse`, and `PaymentNotice` have **no REST controllers at all** — for `Claim`/`ClaimResponse` the underlying FHIR R4 data classes exist under `src/FHIR/R4/FHIRDomainResource/`, giving type surface but no HTTP surface or service-layer mapping. The billing subsystem (Phase 7) operates on X12 EDI, disjoint from these FHIR types. No FHIR `$submit`/`$evaluate`/`$process-message` operations are wired. No non-US Implementation Guide is declared in the CapabilityStatement. No application-tier rate limiter is present.

---

## UNKNOWNs

- Whether US Core versions newer than 3.1.0 are honored by the underlying `FhirService` classes even though the route-map filename pins 3.1.0.
- Numeric API versioning strategy for breaking changes (no `/v1/` segment) — **requires product-owner input**.
- Application-tier rate-limit policy — none in code; may be enforced at reverse proxy — **requires infra/product-owner input**.
- Full per-resource FHIR SearchParameter enumeration (36 `FhirService.php` files); CapabilityStatement at `GET /fhir/metadata` is the runtime authority.
- Whether `PaymentNotice`, `ExplanationOfBenefit`, `CoverageEligibility*` FHIR R4 data classes exist under `src/FHIR/R4/FHIRDomainResource/` (only `FHIRClaim`, `FHIRClaimResponse` were confirmed present).
