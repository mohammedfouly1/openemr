# 08 — Billing / Claims / Insurance (US-EDI vs NPHIES-FHIR)

Read-only audit. Every claim cites `file:line`. Cross-references Phase 3 (`04-database-schema.md`),
Phase 0 (`02-tech-stack.md`), Phase 12 (`13-i18n-localization.md`).

**Note:** Phase 5 (`06-api-surface.md`) did not exist in `docs/00-discovery/` at time of writing;
FHIR surface facts below were re-derived from source. Flagged as UNKNOWN where relevant.
**`06-api-surface.md` was added later and is now present in this directory** — see that
file for the full FHIR resource-per-verb table (§4) and route inventory.

---

## 1. End-to-end billing data flow

### 1.1 Stage diagram — encounter → claim → payment

| # | Stage | Table(s) written | Code (authoritative) | Format |
|--:|---|---|---|---|
| 1 | Clinician opens Fee Sheet on encounter, enters CPT/HCPCS + ICD10 + modifier + fee | (read `form_encounter`, `codes`, `list_options`) | `interface/forms/fee_sheet/new.php:23` (uses `OpenEMR\Billing\BillingUtilities`); form class `library/FeeSheetHtml.class.php` | HTML form |
| 2 | Fee-sheet save inserts per-line row into `billing` | `billing` (activity=1, billed=0) | `src/Billing/BillingUtilities.php:1467-1474` — literal `INSERT INTO billing (date, encounter, code_type, code, code_text, pid, authorized, user, groupname, activity, billed, provider_id, modifier, units, fee, ndc_info, justify, notecodes, pricelevel, revenue_code, payer_id) VALUES (NOW(), ?, ?, …)` | SQL row |
| 3 | User opens Billing Manager and queues encounter for X12 837P | `billing.bill_process`, `billing.bill_date`, `billing.payer_id`, `billing.target`, `billing.x12_partner_id`; new row in `claims` (composite PK `patient_id, encounter_id, version`) | `interface/billing/billing_process.php:23,32` → `new BillingProcessor($_POST)` (`src/Billing/BillingProcessor/BillingProcessor.php`) | claim state machine |
| 4 | Generator runs, emits 837P segment stream | `billing.billed=1`, `billing.process_date`, `billing.process_file`, `claims.status=2`, `claims.bill_process=2` | `src/Billing/BillingProcessor/Tasks/GeneratorX12.php:63-171` calls `X125010837P::genX12837P()` → `src/Billing/X125010837P.php:40`; delegates to `src/Billing/Claim.php` (class `Claim`, x12 version const `'005010X222A1'` at line 29) | X12 5010 837P batch file `.txt` in `edi/` |
| 5 | Batch shipped to clearinghouse via SFTP (partner-dependent) | (reads `x12_partners.x12_sftp_*`) | `src/Billing/BillingProcessor/Tasks/GeneratorX12.php:210` (`auto_sftp_claims_to_x12_partner` global); partner row `sql/database.sql:10025-10059` | SFTP put |
| 6 | Clearinghouse returns ERA (835) file | — | `src/Billing/ParseERA.php:19-60`; loop key `parseERA2100` for 2100/2110 loops | X12 835 file |
| 7 | User uploads ERA in EOB search; posts payment | new row in `ar_session` (one per remittance/check); N rows in `ar_activity` (per-line adjudication) | UI: `interface/billing/sl_eob_search.php`, `sl_eob_process.php`, `sl_eob_invoice.php`, `sl_eob_patient_note.php`, `era_payments.php` (all under `interface/billing/`). Backend: `src/Billing/SLEOB.php` + `src/Billing/ParseERA.php` | SQL rows |
| 8 | Claim marked complete or secondary re-queued | `claims.status`, new `claims` version row on re-open | Callback in `BillingUtilities::updateClaim` — signature/comment at `src/Billing/BillingUtilities.php:1492-1519` (documents every state transition) | state |

### 1.2 Authoritative claim codec

There is **no** `gen_x12_837.inc.php` in the repository (glob `**/gen_x12_837*` → 0 hits). The legacy
procedural file cited in older OpenEMR docs no longer exists in this fork. The **only** X12 837
codec is the modern class:

- `src/Billing/X125010837P.php` (Professional 837P) — 1640 lines, entry `X125010837P::genX12837P()` at line 40.
- `src/Billing/X125010837I.php` (Institutional 837I) — sibling class for UB04.

Both are invoked from the `BillingProcessor` task tree (`src/Billing/BillingProcessor/Tasks/GeneratorX12.php:70`,
`GeneratorUB04X12.php`, `GeneratorX12Direct.php`). The `Claim` domain class
(`src/Billing/Claim.php:27`) is the shared model — it hydrates `procs`/`diags`/`x12_partner`/`encounter`/
`facility`/`billing_facility`/`provider`/`insurance_numbers`/`payers` from the DB, and the two
codec classes read from it. `Claim.php:29` hard-codes `X12_VERSION = '005010X222A1'` — a US-EDI version tag.

### 1.3 `ar_session` / `ar_activity` model (posted-payment schema)

`sql/database.sql:10158-10179` (`ar_session`) and `sql/database.sql:10188-10212` (`ar_activity`):

- `ar_session` = **one payer's remittance session** (one check / one EOB). Key columns:
  `session_id` PK, `payer_id` (`0=pt else references insurance_companies.id`, line 10160),
  `user_id`, `closed`, `reference` (check/EOB number), `check_date`, `deposit_date`, `pay_total`,
  `global_amount`, `payment_type`, `adjustment_code`, `patient_id`, `payment_method`.
- `ar_activity` = **per-line adjudication rows**, PK `(pid, encounter, sequence_no)`. Key columns:
  `code_type`, `code` (empty = claim level, line 10193), `modifier`, `payer_type`
  (`0=pt, 1=ins1, 2=ins2, etc`, line 10195), `session_id` FK to `ar_session`, `pay_amount`,
  `adj_amount`, `memo` (adjustment reason), `reason_code`, `follow_up`, `deleted`, `post_date`,
  `payer_claim_number`.
- Comment on line 10200: `"either pay or adj will always be 0"` — every row is either a payment or
  an adjustment, never both. This encodes the classic EOB posting model
  (charge → allowed → paid → adjustment → patient responsibility).

Note: `ar_session.payer_id=0` is the sentinel for **patient payment** (not payer). This is the
single point where the payer/patient responsibility split is expressed.

---

## 2. Insurance model

### 2.1 `insurance_companies` (`sql/database.sql:3279-3297`)

| Column | Type | Purpose |
|---|---|---|
| `id` | `int(11) NOT NULL default 0` (manual, no AI) | PK |
| `uuid` | `binary(16)` | FHIR reference |
| `name`, `attn` | `varchar(255)` | display |
| `cms_id` | `varchar(15)` | **CMS payer ID** (US-specific) |
| `alt_cms_id` | `varchar(15)` | alternate CMS ID |
| `ins_type_code` | `int(11)` | FK to `insurance_type_codes` list |
| `x12_receiver_id` | `varchar(25)` | X12 ISA08 for this payer |
| `x12_default_partner_id` | `int(11)` | default `x12_partners.id` |
| `eligibility_id` | `varchar(32)` | 270/271 payer ID |
| `x12_default_eligibility_id` | `int(11)` | default eligibility partner |
| `cqm_sop` | `int` | **HL7 Source of Payment for eCQMs** (Medicare/Medicaid/Commercial/BCBS/Other classification — US quality-measure concept) |
| `inactive`, `date_created`, `last_updated` | | audit |

**No `currency` column, no `country` column, no `tax_registration_number`.** All identifiers
assume US clearinghouse conventions.

### 2.2 `insurance_data` (`sql/database.sql:3306-3344`)

- `type` is `enum('primary','secondary','tertiary')` (line 3309) — **hard-coded three-tier
  coverage chain**, one row per priority per patient. Unique key `(pid, type, date)` (line 3343)
  means a patient can have multiple *historical* rows per priority (by effective date), but only
  one *active* row per priority per effective date.
- Full subscriber demographics duplicated on this row (lines 3314-3335): subscriber_lname/mname/
  fname/relationship/ss/DOB/street(+line_2)/city/state/postal/country/phone/sex/employer(+street/
  city/state/postal/country).
- `provider varchar(255)` (line 3310) — **stringly-typed FK** to `insurance_companies.id` (not
  enforced; see Phase 3 §6 "zero FKs").
- Effective dates: `date` (start, line 3333) and `date_end` (end, line 3340).
- Payer-facing fields: `plan_name`, `policy_number`, `group_number`, `copay` (`varchar(255)` — not
  a decimal! line 3332), `accept_assignment` (`varchar(5) DEFAULT 'TRUE'`), `policy_type varchar(25)`.

### 2.3 Prior-authorization

**No prior_auth column on `insurance_data`.** Prior-auth data lives in three separate places:

| Location | Evidence |
|---|---|
| `form_misc_billing_options` — encounter-scoped misc billing form has column `prior_auth_number` | Grep `.phpstan/baseline/offsetAccess.nonOffsetAccessible.php:7930,36005`; consumed by `src/Billing/Claim.php:1548` (`$this->billing_options['prior_auth_number']`) |
| `form_prior_auth` — a legacy per-encounter form (`interface/forms/prior_auth/`, files `new.php`, `save.php`, `view.php`, `report.php`, `FormPriorAuth.class.php`, `C_FormPriorAuth.class.php`) with column `prior_auth_number` | Grep hits in `.phpstan/baseline/missingType.property.php:410` and `interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/ListAuthorizations.php:92-95` |
| `module_prior_authorizations` — patient-scoped auth table shipped by custom module `oe-module-prior-authorizations` (columns: `pid`, `auth_num`, `start_date`, `end_date`) | `interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/ListAuthorizations.php:30,70,74`; installed at module-install time |

Authoritative field for X12 REF*G1 (prior auth) segment is `form_misc_billing_options.prior_auth_number`
(`src/Billing/Claim.php:1548`).

### 2.4 270/271 eligibility

**In-tree** (not a claimrev-only feature):

- `src/Billing/EDI270.php` — 1162-line class that builds X12 270 eligibility requests and parses
  271 responses. Uses `edih_271_codes` codebook at `library/edihistory/codes/edih_271_code_class.php:1957`.
- UI: `interface/billing/edi_270.php`, `interface/billing/edi_271.php`.
- Historical EDI viewer: `library/edihistory/` directory (many `edih_*` files).
- Partner glue: `x12_partners.processing_format enum(..,'oa_eligibility','availity_eligibility')`
  (`sql/database.sql:10031`); OAuth token/endpoint fields `x12_token_endpoint`,
  `x12_eligibility_endpoint`, `x12_client_id`, `x12_client_secret` (lines 10052-10057).

**Grep for `EligibilityService` / `checkEligibility` / `eligibility_check` in `src/` returned zero.**
There is **no** modern `EligibilityService` PSR-4 class — eligibility is legacy X12 270 only.

Claimrev-connect **augments** this with REST-based eligibility that stores results in
`mod_claimrev_eligibility` (`interface/modules/custom_modules/oe-module-claimrev-connect/table.sql:2-15`)
and has parallel classes `EligibilityInquiryRequest`, `EligibilityData`, `Eligibility_ClaimRev_Service`,
`EligibilitySweepService`. The 270/271 substrate is US-EDI end-to-end.

---

## 3. US-specific vs reusable

### 3.1 US-only components (NOT reusable for NPHIES)

| Component | File evidence |
|---|---|
| X12 5010 837P codec | `src/Billing/X125010837P.php` (1640 lines); `X12_VERSION='005010X222A1'` at `src/Billing/Claim.php:29` |
| X12 5010 837I codec (institutional) | `src/Billing/X125010837I.php` |
| X12 270/271 eligibility codec | `src/Billing/EDI270.php`; `library/edihistory/codes/edih_271_code_class.php` |
| X12 835 ERA parser | `src/Billing/ParseERA.php` (561 lines) |
| X12 278 auth (viewer only) | `library/edihistory/edih_278_html.php` |
| ISA/GS envelope config on `x12_partners` | `sql/database.sql:10025-10059` — `x12_isa01..isa15`, `x12_gs02/gs03` |
| CMS-1500 (HCFA) paper generator | `src/Billing/Hcfa1500.php`, `src/Billing/HCFAInfo.php`; `src/Billing/BillingProcessor/Tasks/GeneratorHCFA.php`, `GeneratorHCFA_PDF.php`, `GeneratorHCFA_PDF_IMG.php` |
| UB04 paper/EDI generator | `src/Billing/BillingProcessor/Tasks/GeneratorUB04Form_PDF.php`, `GeneratorUB04NoForm.php`, `GeneratorUB04X12.php`; UI at `interface/billing/ub04_form.php`, `ub04_submit.php`, `ub04_dispose.php`, `ub04_codes.inc.php`; globals `ub04_support`, `ub04_top_margin` at `library/globals.inc.php:1209-1226` |
| NPI (National Provider Identifier) | `users.npi` and `facility.npi` columns; NPPES lookup proxy at `interface/usergroup/npi_lookup.php:1-149`; validator `tests/Tests/Isolated/Validators/PractitionerValidatorTest.php:142-173` (10-digit numeric rule); rendered in reports (`library/report_database.inc.php:301`), address book (`interface/usergroup/addrbook_list.php:172`), providers layout (`library/layout.inc.php:35`) |
| CMS payer ID (`cms_id`, `alt_cms_id`) | `sql/database.sql:3284,3288` |
| ICD-10-CM (US clinical modification) | `code_types` seed includes ICD10 (Phase 3 §4 table); actual ICD-10-CM is US-specific but not distinguished in schema |
| Source-of-Payment for eCQM (`cqm_sop`) | `sql/database.sql:3292` — HL7 SOP is a US quality-measure taxonomy |
| Medicare/Medicaid assumptions in reports | `interface/reports/collections_report.php`, `insurance_allocation_report.php` (US A/R conventions) |

### 3.2 Reusable components (conceptually language- and payer-neutral)

| Component | File evidence |
|---|---|
| Fee-sheet UI (code + fee + modifier + units per encounter) | `interface/forms/fee_sheet/new.php`, `codes.php`, `view.php`, `report.php`; codebase-agnostic — driven by `code_types` seed |
| `billing` table as line-item store | `sql/database.sql:245-278` — pure lifecycle columns (`activity`, `billed`, `bill_process`, `bill_date`, `process_date`, `process_file`, `payer_id`, `target`); no US-specific columns *except* `x12_partner_id` (line 269) and `ndc_info` (US NDC drug code, line 270) |
| Claim lifecycle state machine (`billing.bill_process`, `billing.billed`, `claims.status`, `claims.version`) | `sql/database.sql:378-393` `claims` table; documented state transitions at `src/Billing/BillingUtilities.php:1492-1519` |
| `ar_session` / `ar_activity` posting model | `sql/database.sql:10158-10212` — payer_id/payer_type generic; adjustment/pay split generic; `reason_code varchar(255)` free-text (line 10206) so any code system works |
| Coverage-priority chain (primary/secondary/tertiary) | `insurance_data.type enum` (`sql/database.sql:3309`) — three-tier is a healthcare universal, not US-specific |
| Adjustment reason codes (memo/reason_code) | `sql/database.sql:10199,10206` — schema is opaque to code system; can hold CARC/RARC or NPHIES adjudication codes |
| Patient responsibility split (`ar_session.payer_id=0`) | `sql/database.sql:10160` sentinel; carries through to `ar_activity.payer_type=0` |
| EOB posting UI (charge/allowed/paid/adj/PR) | `interface/billing/sl_eob_search.php`, `sl_eob_invoice.php`, `sl_eob_process.php` — generic column headings; ParseERA is US-format but posting UI is codec-agnostic |
| `codes` + `code_types` overlay | `sql/database.sql` §9 (Phase 3); can host Saudi CCHI code systems by adding new `code_types.ct_id` rows |

---

## 4. NPHIES attachment strategy — three options

### Option A — via FHIR Coverage/Claim controllers

**Coverage: read-only, no write path.**

- Sole controller: `src/RestControllers/FHIR/FhirCoverageRestController.php:22` — methods `getAll()`
  (line 112) and `getOne()` (line 191). No `create()`, `update()`, `delete()`.
- OpenAPI declarations (`src/RestControllers/OpenApi/OpenApiDefinitions.php:40,69,102`) advertise
  only `Coverage.rs` (read/search) scopes — **no `Coverage.cud` (create/update/delete)**.
- Routes registered at `apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php:180,183,191,194` —
  GET-only.
- Currency hard-coded to USD: `src/Services/FHIR/FhirCoverageService.php:294`
  (`$valueMoney->setCurrency('USD')`) — per Phase 12 §6.
- Coverage status derived from `insurance_data.date`/`date_end`
  (`FhirCoverageService.php:314-329`).

**Claim resource: not routed at all.**

- Glob `src/**/FhirClaim*` → **zero files**.
- Grep for route registrations `FhirClaim|routeMap.*Claim` in `*.php` → zero hits.
- The FHIR resource `Claim` is not exposed by OpenEMR's REST API surface. FHIR-side, claims
  currently do not exist as an outbound resource.

**What would need to be built for FHIR-Claim-to-Saudi-payer:**

1. `src/Services/FHIR/FhirClaimService.php` — read from `billing` + `claims` + `form_encounter` +
   `insurance_data` + `insurance_companies`, project to FHIR R4 `Claim` resource (KSA IG profile
   `nphies.sa/StructureDefinition/Claim`).
2. `src/RestControllers/FHIR/FhirClaimRestController.php` with `create()` (POST `$submit` operation
   per NPHIES).
3. New `FhirClaimResponseService` to consume `ClaimResponse` and post to `ar_session`/`ar_activity`.
4. Fix hard-coded `'USD'` at `FhirCoverageService.php:294` → per-payer or per-globals lookup.
5. Add `Coverage` write path (currently read-only).
6. Register a Saudi identifier system for `Coverage.subscriberId` / `Coverage.identifier` (e.g.
   national ID / iqama).

### Option B — custom `oe-module-nphies` module subscribing to a billing event

**Does the event exist? No.**

- Grep for `billing\.claim|claim\.submit|claim\.ready|BillingEvent|ClaimEvent` in `*.php` → **zero hits**.
- `OpenEMR\Events\` namespace enumerated via claimrev bootstrap imports
  (`interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php:28-38`):
  `AppointmentSetEvent`, `CalendarUserGetEventsFilter`, `StyleFilterEvent`,
  `TwigEnvironmentEvent`, `GlobalsInitializedEvent`, `Main\Tabs\RenderEvent`,
  `PatientDemographics\RenderEvent`, `RestApiCreateEvent`, `RestApiResourceServiceEvent`,
  `RestApiScopeEvent`, `MenuEvent`. **No `Billing\*` or `Claim\*` event class is imported by any
  module.**
- The claim submit path (`interface/billing/billing_process.php:32` → `BillingProcessor` →
  `GeneratorX12`) does **not** dispatch any Symfony event before or after
  `X125010837P::genX12837P()`. See `src/Billing/BillingProcessor/Tasks/GeneratorX12.php:63-171`
  — no `EventDispatcherInterface` injection, no `->dispatch()` calls.

**Consequence:** an `oe-module-nphies` cannot simply subscribe and route. To get an event-driven
hook, a new event (e.g. `ClaimAboutToBeSubmittedEvent` with subscriber priority) must be added to
`src/Events/Billing/` and dispatched from `BillingProcessor::process()` before generator dispatch —
that is a **core code change**, not a module change. Claimrev-connect side-steps this by adding its
own generator variant (`GeneratorX12Direct.php`) and its own background service — it does not
subscribe to a "claim ready" event because no such event exists.

### Option C — parallel service reading `billing` on a schedule

**Feasibility:** high. This is exactly the pattern claimrev-connect uses (§5).

- Read: `billing WHERE bill_process=1 AND billed=0` + join `claims`/`form_encounter`/`insurance_data`.
- Post-adjudication: write `ar_session` + `ar_activity` rows; set `billing.billed=1`,
  `billing.process_date`, `claims.status=2`.
- Scheduling: the `background_services` table (referenced by claimrev-connect
  `table.sql:35-73`) is the sanctioned periodic-task mechanism.

**What breaks:**
- Nothing in the state machine — every state column is a plain integer/datetime writable from SQL.

**What survives:**
- Fee sheet, billing/claims state machine, EOB posting, patient statements, A/R reports.

**Risk:** the schema encodes X12-specific fields (`billing.x12_partner_id`, `claims.x12_partner_id`,
`insurance_companies.x12_receiver_id`) that a NPHIES service would leave NULL, and reports assume
they are populated. A "NPHIES partner" facade (fake `x12_partners` row) may be needed to keep the
existing UI functional.

---

## 5. Claimrev-connect module — reference architecture

**Version pinned in composer:** `claimrevolution/oe-module-claimrev-connect ^2.1` (composer.json:52
per Phase 0 §9); lock at v2.1.6 (`Bootstrap.php:51`).

### 5.1 Boot sequence

- `openemr.bootstrap.php:29-30` — instantiates `Bootstrap($eventDispatcher)` then calls
  `->subscribeToEvents()`.
- `src/Bootstrap.php:96-109` — hooks into: globals init, menu, template events, REST API events,
  patient demographics render, calendar/appointment events. **Notably NOT the billing submit path.**
- REST API events subscribed at `Bootstrap.php:323-344`: `RestApiCreateEvent`,
  `RestApiResourceServiceEvent`, `RestApiScopeEvent` (line 344: `API_TYPE_FHIR` branch present).

### 5.2 Actual claim-flow integration — NOT event-driven; it's *background services + parallel generator*

`table.sql:35-74` inserts SIX rows into OpenEMR's `background_services` table:

| Service | Interval | Entrypoint | Purpose |
|---|---|---|---|
| `ClaimRev_Send` | 1 min | `Billing_Claimrev_Service.php` → `start_X12_Claimrev_send_files` | Push generated X12 files to ClaimRev |
| `ClaimRev_Receive` | 240 min | same file → `start_X12_Claimrev_get_reports` | Pull 277/835 back |
| `ClaimRev_Elig_Send_Receive` | 1 min | `Eligibility_ClaimRev_Service.php` → `start_send_eligibility` | 270/271 |
| `ClaimRev_Notifications` | 60 min | `ClaimRev_Notification_Service.php` | Poll portal msgs |
| `ClaimRev_Watchdog` | 20 min | `ClaimRev_Watchdog_Service.php` | Recover stuck jobs |
| `ClaimRev_Elig_Sweep` | 1440 min | `Eligibility_Sweep_Service.php` | Daily eligibility batch |

### 5.3 Storage — six module-owned tables

`table.sql`: `mod_claimrev_eligibility` (line 2), `mod_claimrev_claims` (line 78), 
`mod_claimrev_claim_events` (line 106), `mod_claimrev_patient_statements` (line 127), 
`mod_claimrev_notifications` (line 50), `mod_claimrev_version_check` (line 146). All named 
`mod_claimrev_*` — the `mod_` prefix is used instead of the `custom_` prefix mentioned in Phase 3.

`mod_claimrev_claims` (line 78-102) is the crucial one — a **shadow claim tracker** with FK to
`ar_session` (`ar_session_id INT(11)`, line 91) and the ClaimRev-side object ID
(`claimrev_object_id varchar(64)`, line 83). This lets the module maintain a parallel state view
without altering core `billing`/`claims` tables.

### 5.4 Does it replace X12 generation, or add on top?

**Adds on top.** The module does not override `GeneratorX12`. It uses the standard OpenEMR X12 837P
output (files produced by `BillingProcessor` land in the EDI directory), then its
`ClaimRev_Send` background service SFTP-pushes them to ClaimRev's endpoint and writes tracking
rows into `mod_claimrev_claims` and `mod_claimrev_claim_events`. Payment adjudication flows back
via `PaymentAdvicePostingService.php` which posts to native `ar_session`/`ar_activity`.

### 5.5 Mirror-ability for NPHIES — verdict

**Yes, this is a strong reference implementation for `oe-module-nphies`.** Direct mappings:

| Claimrev pattern | NPHIES equivalent |
|---|---|
| `background_services` row `ClaimRev_Send` reading generated X12 files | New background service reading `billing WHERE bill_process=?` and **projecting to FHIR Claim JSON on the fly** (skip X12 gen entirely) |
| `mod_claimrev_claims` shadow tracker table | `mod_nphies_claims` — track NPHIES bundle ID, adjudication status, `ar_session_id` linkage |
| `mod_claimrev_eligibility` request/response JSON blobs | `mod_nphies_eligibility` — cache CoverageEligibilityRequest/Response FHIR JSON |
| `PaymentAdvicePostingService` posting to `ar_session`/`ar_activity` | `NphiesPaymentNoticeService` doing the same from FHIR PaymentNotice / PaymentReconciliation |
| Register in `x12_partners` with `processing_format='...'` for endpoint config | Reuse `x12_partners` OAuth/endpoint columns (`x12_token_endpoint`, `x12_client_id`, `x12_client_secret` — lines 10052-10057 already generic) or introduce `nphies_partners` table |

The key architectural insight from claimrev: **it does not touch core tables' schema; it lives
alongside via `mod_*` tables and writes back through `ar_session`/`ar_activity`**. A NPHIES module
should follow the same rule.

---

## 6. Currency-per-payer

| Question | Finding | Evidence |
|---|---|---|
| Does `insurance_companies` have a currency column? | **No.** | `sql/database.sql:3279-3297` — 15 columns, none are currency |
| Does `billing` have a currency column? | **No.** | `sql/database.sql:245-278` — 26 columns, `fee decimal(12,2)` only, no unit |
| Does `ar_session` / `ar_activity` have currency? | **No.** | `sql/database.sql:10158-10212` — decimals with no unit |
| Site-wide symbol config | Yes — `gbl_currency_symbol` default `'$'` free-text | Phase 12 §6; `library/globals.inc.php:820` |
| Hard-coded USD in FHIR Coverage | Yes | `src/Services/FHIR/FhirCoverageService.php:294` |
| Multi-currency effort | Schema migration on every financial table (`billing`, `ar_session`, `ar_activity`, `insurance_data.copay`, `fees`, `prices`), backfill, per-report/statement rendering | — |
| Single-tenant single-currency (KSA-only) | `gbl_currency_symbol='SAR'` + fixing `FhirCoverageService.php:294` covers ~all user-visible surfaces | — |

### VAT / ZATCA e-invoicing readiness

Grep for `zatca|einvoice|invoice_hash|qr_code_invoice|fatoora` across the entire tree
(`*.php,*.js,*.sql,*.json,*.yml`) → **zero hits** in all files. **No ZATCA readiness of any kind
exists.** Confirms Phase 12 §8. Requirements for ZATCA Phase 2 (Fatoora) — invoice UUID, XML
signing, cryptographic stamp, QR code, submission to ZATCA integration gateway, seller/buyer
tax-registration numbers on every invoice — are all greenfield.

Related: no `tax_amount` / `vat` / `taxable_amount` columns on `billing` (`sql/database.sql:245-278`);
tax-rate registry only via `list_options` where `list_id='taxrate'` (`sql/database.sql:4354`) with
colon-list references on `codes.taxrates` and `drug_templates.taxrates` (Phase 12 §8).

---

## 7. Billing / A-R reports enumeration

Under `interface/reports/`:

| File | Purpose (one-line) |
|---|---|
| `collections_report.php` | Aged A/R by payer + patient — US-style aging buckets |
| `daily_summary_report.php` | Charges/receipts/adjustments for a date range |
| `svc_code_financial_report.php` | Revenue by CPT/HCPCS code |
| `pat_ledger.php` | Per-patient full transaction ledger |
| `insurance_allocation_report.php` | Payment allocation across payers |
| `receipts_by_method_report.php` | Cash/check/card/EFT breakdown |
| `payment_processing_report.php` | Payment-gateway (Rainforest etc.) transaction log |
| `front_receipts_report.php` | Front-desk receipts by user/session |
| `patient_flow_board_report.php` | Encounter workflow status (not strictly billing) |
| `unique_seen_patients_report.php` | Patient count over date range |
| `encounters_report.php` | Encounter counts by facility/provider |
| `chart_location_activity.php` | Chart movement (not billing) |
| `charts_checked_out.php` | Charts out of file room (not billing) |

Under `interface/billing/` (report-ish rather than pure-workflow):

| File | Purpose |
|---|---|
| `billing_report.php` | Master A/R and claim status workbench |
| `billing_tracker.php` | X12 batch history |
| `print_billing_report.php` | Printable A/R |
| `sl_receipts_report.php` | Cash-basis receipts |
| `sl_eob_search.php` | EOB posting entry point |
| `print_daysheet_report_num1/2/3.php` | Provider day-sheet variants |
| `indigent_patients_report.php` | Sliding-scale/charity write-off report |
| `search_payments.php` | Payment search UI |
| `era_payments.php` | ERA import viewer |
| `edih_main.php`, `edih_view.php` | EDI history browser (X12 file archive) |

All of these will need Arabic column headers + SAR formatting + date-format switch for a Saudi
deployment. Aging buckets, patient-vs-insurance responsibility, and adjustment reason display are
universal concepts and translate cleanly.

---

## 8. Verdict — findings only

### 8.1 Reusable-as-is (no schema change required)

- Fee sheet UI (`interface/forms/fee_sheet/*`)
- `codes` + `code_types` overlay (add Saudi CCHI code-type rows)
- `billing` line-item table (schema is neutral except for `ndc_info` and `x12_partner_id`)
- `claims` state versioning
- `ar_session` / `ar_activity` posting model
- Patient-responsibility split (`payer_id=0` sentinel)
- Coverage-priority chain (primary/secondary/tertiary)
- `background_services` scheduler (proven by claimrev)
- Custom module `mod_*` table pattern (proven by claimrev)

### 8.2 Wrapper needed (Saudi-market adaptation on top of existing surface)

- FHIR `Coverage` controller: **add write path** (currently read-only,
  `FhirCoverageRestController.php`), fix hard-coded USD (`FhirCoverageService.php:294`), add
  Saudi identifier systems (national ID / iqama), optional Hijri date handling.
- Reports i18n: Arabic column headers + SAR + RTL for the ~20 files enumerated in §7.
- `insurance_companies`: reinterpret `cms_id` as generic payer ID or add `nphies_payer_id` column.
- Prior-auth workflow: `form_misc_billing_options.prior_auth_number` field works as-is for NPHIES
  auth-number carriage.

### 8.3 Replace / new-build

- **New event** `ClaimAboutToBeSubmittedEvent` (or equivalent) dispatched from
  `src/Billing/BillingProcessor/BillingProcessor.php` before `GeneratorX12` fires — currently no
  such event exists (§4 Option B). Alternatively, follow claimrev's pattern and skip events
  entirely, using a background service reading the `billing` table.
- **New FHIR Claim service** `src/Services/FHIR/FhirNphiesClaimService.php` — no equivalent in tree.
- **New FHIR Claim controller** `src/RestControllers/FHIR/FhirClaimRestController.php` — glob
  returned zero.
- **New `mod_nphies_*` tables**: `mod_nphies_claims` (shadow tracker with `ar_session_id` link),
  `mod_nphies_eligibility` (CoverageEligibilityRequest/Response cache),
  `mod_nphies_payment_notices`, `mod_nphies_communications` (mirror of claimrev's design).
- **NPHIES codec module** replacing (or paralleling) `X125010837P`. If parallel: add a
  `GeneratorNphies` alongside `GeneratorX12` in `src/Billing/BillingProcessor/Tasks/`.
- **New eligibility service** consuming FHIR `CoverageEligibilityResponse` — the modern
  `EligibilityService` class does not exist in tree; `src/Billing/EDI270.php` is X12-only.
- **New PaymentNotice consumer** — mirror `SLEOB.php` + `ParseERA.php` for FHIR PaymentNotice /
  PaymentReconciliation, writing to `ar_session`/`ar_activity`.
- **ZATCA subsystem** — completely greenfield (§6): invoice UUID + XML signing + QR + Fatoora
  gateway submission. Every currently-shipped PDF/HTML statement generator would need routing
  through the ZATCA stamp step.
- **Currency policy** — decide per-tenant symbol vs schema migration; fix
  `FhirCoverageService.php:294` and the 5 other USD hard-codes catalogued in Phase 12 §6.

---

## UNKNOWNs

1. Phase 5 (`06-api-surface.md`) not yet written — full FHIR resource inventory and route table for
   this fork was re-derived by grep here rather than cited from a prior report. Recommend
   producing Phase 5 to catalog: which FHIR R4 resources have `create`/`update` (versus read-only
   like Coverage) and which are US-Core-3.1.0-only versus present in other IGs.
2. Whether `BillingProcessor` (`src/Billing/BillingProcessor/BillingProcessor.php`, not read this
   pass) contains any hookable extension points other than the `GeneratorInterface` polymorphism.
   Recommend a focused read of that class + `AbstractGenerator` in a follow-up phase.
3. Whether `claimrev-connect` sends FHIR to ClaimRev anywhere internally, or is 100% X12 — this
   pass only confirmed X12 emission via `Billing_Claimrev_Service`; deeper trace deferred.
4. Complete list of "US assumption" hard-codes in reports (`interface/reports/collections_report.php`
   etc.) not enumerated per-line; only titled.
5. Whether existing `x12_partners` OAuth columns (`x12_token_endpoint`, `x12_client_id`,
   `x12_client_secret`) can be reused as a generic "payer API partner" table for NPHIES OAuth 2.0
   flows, or a new `nphies_partners` table is cleaner. Product decision.
6. Row count / claim volume implications for whether Option C (parallel service reading `billing`)
   scales — no operational data available in a read-only audit.
