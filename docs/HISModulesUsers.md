# OpenEMR HIS — Master Capability Catalog

**Authoritative, evidence-based capability catalog of the CURRENT project.**
Every positive statement below is traceable to this repository's code, this
machine's live MariaDB instance, or an observed runtime response. Generic
OpenEMR documentation is never used as proof of a current capability.

This document supersedes the earlier "OpenEMR HIS — Modules & Users" report at
the same path. Corrections to that report are recorded in §33.

---

## 0. Verification Header

### 0.1 Project fingerprint

| Field | Value |
|---|---|
| Report generated | 2026-08-09 |
| Project root | `G:\My Drive\OpenEMR` |
| Git branch | `master` |
| HEAD commit | `631f2b38cf633769c305233f88cdf9c73ca80657` |
| HEAD short | `631f2b38c` |
| HEAD date / author / subject | 2026-07-04 · Jerry Padgett · `refactor(oe-module-faxsms): Fix SignalWire fax support in oe-module-faxsms (#12526)` |
| `origin` remote | `https://github.com/mohammedfouly1/openemr` (fork) |
| `upstream` remote | `https://github.com/openemr/openemr.git` |
| Tracking branch | `origin/master` (identical SHA to HEAD) |
| Fork divergence | **0 commits ahead of `upstream/master`; 373 commits behind.** `git merge-base --is-ancestor HEAD upstream/master` → true |
| Working tree | 1 modified tracked file (`sites/default/sqlconf.php`), 1 staged deletion, 8 untracked paths |
| PHP | 8.3.33 (cli), ZTS, Visual C++ 2019, x64 |
| Database engine | MariaDB 11.8.8 |
| OpenEMR version (code) | 8.3.0-dev (`version.php:18-21`); schema version `$v_database = 541` (`version.php:34`) |
| Sites discovered | **1** — `sites/default` |
| Default/selected site | `default` |
| Application base URL | `http://localhost:8300/` |
| Runtime probe | `GET /interface/login/login.php?site=default` → **HTTP 200, 9,375 bytes** |
| Database schema | `openemr` @ `127.0.0.1:3306` (credentials not reproduced — see `sites/default/sqlconf.php`) |
| Tables in schema | **283** |
| `globals` rows | **490** |
| Prior report SHA-256 (before this update) | `db17ee1222b8ad2a88c4e125ac648a1173807c95ba5c089435ed6a97aa4a4fee` |
| Prior report line count | 356 |

### 0.2 The single most important fingerprint fact

**This project contains no fork-specific code.** `HEAD` is a plain ancestor of
`upstream/master`; `git rev-list --count upstream/master..HEAD` returns `0`.
Every file in this working tree is upstream OpenEMR at commit `631f2b38c`.

Consequences that govern the whole catalog:

1. There are **no current-project-specific capabilities** beyond stock OpenEMR
   8.3.0-dev. §27 (Current Fork vs Generic OpenEMR) therefore lists zero
   fork-only features.
2. Everything that distinguishes *this deployment* is **configuration and data
   state**, not code — and the configuration is 100 % stock defaults (§19.4).
3. The product is 373 upstream commits stale as of this audit.

### 0.3 Live data state (governs all "Demo Ready" verdicts)

| Table | Rows |
|---|---|
| `patient_data` | **0** |
| `form_encounter` | **0** |
| `openemr_postcalendar_events` (appointments) | **0** |
| `billing` | **0** |
| `ar_activity` / `ar_session` / `claims` / `payments` | **0** / 0 / 0 / 0 |
| `insurance_companies` / `insurance_data` | **0** / 0 |
| `drugs` / `drug_inventory` / `drug_sales` | **0** / 0 / 0 |
| `documents` | **0** |
| `prescriptions` | **0** |
| `x12_partners` / `x12_remote_tracker` | **0** / 0 |
| `oauth_clients` / `api_token` | **0** / 0 |
| `login_mfa_registrations` | **0** |
| `report_results` (CQM/AMC runs) | **0** |
| `facility` | 1 (`Your Clinic Name Here`, installer default) |
| `users` | 4 (1 active human, 3 inactive service accounts) |
| `log` (audit) | 4,280 |

**There is no clinical, financial, or scheduling data in this installation.**
This is a freshly installed, unconfigured system. It does not affect whether a
capability *exists*, but it decides every Demo-Readiness verdict in §28.

---

## 1. Executive Summary

### 1.1 What this system is

A **stock, current-generation, ambulatory (outpatient) Electronic Health Record
and practice-management system** — OpenEMR 8.3.0-dev — installed and running,
with a complete clinical, scheduling and US revenue-cycle feature set, and with
essentially every optional and external-facing capability switched off.

It is **not** a hospital information system in the inpatient sense. There is no
admission, no ward, no bed, no eMAR, no operating theatre, no nursing
documentation module. Those were searched for exhaustively (§26, §31) and are
absent.

### 1.2 Verified totals

| Metric | Count |
|---|---|
| Normalized functional domains | **21** |
| Atomic capabilities catalogued (`CAP-*`) | **270** |
| — Active | **177** |
| — Disabled (implemented, switched off) | **47** |
| — Uninstalled (code present, not registered) | **27** |
| — Requires Integration (external dependency unmet) | **18** |
| — Missing (in-catalog) | **1** |
| Market-comparison capabilities searched and absent (`GAP-0001…0059`) | **59** |
| **Total Missing (in-catalog + market register)** | **60** |
| Screens / routes catalogued (`SCR-*`) | **128** |
| Workflows catalogued (`WF-*`) | **14** |
| Reports catalogued (`RPT-*`) | **55** |
| Clinical forms on disk | **35** (18 registered & active, 17 unregistered) |
| Defined ACL roles / ARO groups (`ROL-*`) | **7** |
| ACL permission objects (ACOs) in 13 sections | **65** |
| Active ACL grants (`gacl_acl` rows) | **19** |
| User accounts (`USR-*`) | **4** |
| — Active human staff accounts | **1** |
| — Inactive service/system accounts | **3** |
| Integrations catalogued (`INT-*`) | **36** |
| — Configured (credentials/endpoint set) | **0** |
| — Runtime-verified operational | **0** |
| API families | **4** (REST, FHIR R4, Portal, OAuth2/SMART) — **all disabled** |
| API routes defined | **183** (98 standard + 80 FHIR + 5 portal) |
| Product-relevant configuration items catalogued (`CFG-*`) | **120** (of 491 defined, 490 stored) |
| Sites (tenants) | **1** |
| Facilities | **1** |
| Pluggable modules registered & active | **5** |
| Pluggable modules present but not installed | **17** |
| Unresolved knowledge gaps (`GAP-0060…0073`) | **14** |

### 1.3 The five findings that matter most commercially

1. **Zero fork divergence.** This is unmodified upstream OpenEMR. Nothing here
   is proprietary, differentiated, or Saudi-adapted. Marketing must not imply
   otherwise (§27).
2. **The system is installed but not configured.** All 490 `globals` rows match
   code defaults; the only deviations are OS-path defaults and an
   auto-generated UUID (§19.4). Vendor placeholders (`your_web_site.com`,
   `Model Registry`, `phimail.example.com`, OpenEMR branding and donation
   links) are live.
3. **Every external-facing surface is off.** REST, FHIR, Portal and system
   scopes are all `0`; no OAuth client exists; no clearinghouse, eRx vendor,
   SMS/fax provider, telehealth provider, lab network or payment gateway is
   configured. Eighteen capabilities are gated behind an unmet external
   dependency.
4. **There is no data.** Zero patients, encounters, appointments, charges or
   documents. No demo scenario is executable today without first creating
   users, a facility, insurers, fee schedules and patients (§28).
5. **Saudi-market readiness is nil.** Across the entire product source there
   are zero occurrences of NPHIES, CCHI, ZATCA, Fatoora, Hijri, Iqama, SFDA or
   "Saudi". The only hits repo-wide are inside this project's own planning and
   discovery documents. Arabic UI translation is genuinely present and loaded
   (47.5 % coverage) and RTL themes ship — that is the entire Saudi-adjacent
   asset base (§23).

### 1.4 Where the real strength is

Clinical documentation breadth, US revenue-cycle depth (837P/837I, CMS-1500,
UB-04, 835 ERA, 270/271), a genuine rule-based clinical decision support
engine, a complete FHIR R4 US Core server with SMART and Bulk Export, a
mature role/permission model, and a real extensibility framework (~90 Symfony
events, two module frameworks). These are catalogued as Active or Disabled —
that is, they exist and work or need only a switch — and they are defensible in
front of a customer.

---

## 2. Audit Methodology

### 2.1 Discovery passes

Seven independent read-only discovery passes were run in parallel, then
reconciled by the primary auditor. No pass's output was accepted without
cross-checking against a second evidence source where one existed.

| Pass | Scope | Principal artefacts examined |
|---|---|---|
| **A** | Functional UI, menus, screens, routes | 7 menu JSON files (parsed recursively via PHP), `src/Menu/*`, `interface/main/tabs/*`, 228 PHP files across 6 interface directories, 176 modal call sites |
| **B** | Clinical EMR | `registry` table, 35 `interface/forms/` dirs, `interface/patient_file/**`, `library/clinical_rules.php` (3,532 lines), `src/ClinicalDecisionRules/`, `interface/orders/**` |
| **C** | Revenue cycle & insurance | `src/Billing/**`, 31 files in `interface/billing/`, `library/edihistory/` (13 files), `library/FeeSheet.class.php`, `controllers/C_X12Partner.class.php`, all billing globals |
| **D** | Administration, security, ACL, audit | `src/Common/Auth/**`, `src/Common/Acl/**`, `src/Common/Logging/**`, all 20 `gacl_*` tables, `users`/`users_secure`, `interface/super/**`, `interface/usergroup/**`, `background_services` |
| **E** | Modules, APIs, integrations | `src/Core/ModulesApplication.php`, Module Manager, 14 Laminas + 8 custom module dirs, 3 route files (183 routes), `src/Events/**` (~90 events), vendor-name sweep |
| **F** | Reports, analytics, configuration | 47 files in `interface/reports/`, `library/globals.inc.php` (4,563 lines, 491 settings, 23 tabs), full `globals` diff vs code defaults, `user_settings`, site config |
| **G** | Market-comparison negative scan | Repo-wide ripgrep with synonym expansion across 6 capability groups + Saudi/localisation register; DB schema sweeps; `patient_data` column inspection |

### 2.2 Reconciliation rules applied by the primary auditor

- Subagent conclusions were treated as **candidate findings**, not results. Each
  material claim was re-derived or spot-checked directly.
- Where a pass reported a capability as present on the strength of a filename or
  a generated class, the claim was downgraded unless an implementation, a route,
  and (where relevant) a DB table were all found. This removed several false
  positives — notably FHIR `Claim`/`ExplanationOfBenefit` (generated model
  classes only, no service, no route) and `FHIRMedicationAdministration` (not an
  eMAR).
- Where the live database contradicted a static-source conclusion, **the
  database won**. This produced the Arabic-translation correction in §33.
- Menu visibility was recomputed against actual `globals` values rather than
  assumed, which moved 47 capabilities from Active to Disabled.

### 2.3 Read-only guarantee

No application source file, database row, configuration value, module
registration, user account or feature flag was modified. All SQL was
`SELECT`/`SHOW`/`DESCRIBE`. All HTTP was `GET`. No password hash was cracked;
no secret is reproduced; no PHI exists in this installation to leak. The only
persistent artefact written by this audit is this file.

---

## 3. Evidence Hierarchy

Applied strictly, strongest first. A capability's Evidence Confidence (§8) is a
direct function of how many independent tiers corroborate it.

| Tier | Source | Used for |
|---|---|---|
| 1 | Observed runtime behaviour | Login page HTTP 200/9,375 B; `password_verify()` against the stored hash |
| 2 | Live MariaDB state | Module registration, form registry, ACL grants, user accounts, all 490 globals, row counts |
| 3 | Current codebase | Implementation existence, routes, services, generators, enforcement points |
| 4 | Site configuration | `sites/default/config.php`, `sqlconf.php` (`$config=1`) |
| 5 | Module registration state | `modules`, `module_acl_*`, `module_configuration`, `modules_hooks_settings` |
| 6 | Menu / ACL / route / form / report definitions | 7 menu JSONs, `gacl_aco`, `registry`, route maps |
| 7 | Automated tests / fixtures | Corroborating only (e.g. breakglass tests confirm the ARO group name) |
| 8 | Repository documentation | Secondary; `docs/00-discovery/`, `docs/discovery/`, module READMEs — re-verified, never trusted alone |
| 9 | External OpenEMR documentation | **Never used as proof of a current capability.** |

**Rule enforced:** a capability that exists in generic OpenEMR but is absent
from this repository is not reported as a capability of this project.

---

## 4. Status Definitions

Every capability carries exactly one **Primary Status**.

| Status | Meaning | Test applied |
|---|---|---|
| **Active** | Implemented and currently available/enabled in this installation | Code exists **and** no config flag disables it **and** (where pluggable) it is registered and enabled |
| **Disabled** | Implemented and present, but a current configuration flag switches it off | Code exists; a specific `globals` value or menu `global_req` gate turns it off. The flag is named in every case. |
| **Uninstalled** | Pluggable/registrable component present on disk but not registered/installed | No `modules` row, or no `registry` row; and its DB tables are absent |
| **Requires Integration** | Implemented, but meaningful operation needs an external service/vendor/credential that is not currently configured, connected or licensed | Code exists; the external endpoint/credential/partner record is empty or absent |
| **Missing** | Part of the HIS comparison baseline; targeted synonym searches performed; no implementation evidence found | Negative search recorded in §26/§31 with terms used and locations searched |

**Operational State** (added in the Group 2 reconciliation). Primary Status
describes whether a capability is *implemented and enabled*. It does **not**
describe whether it can actually execute in this environment. Where the two
diverge, the row carries an explicit operational qualifier:

| Operational State | Meaning |
|---|---|
| *(unmarked)* | No known environmental impediment; runtime simply not exercised |
| **Op: BLOCKED** | Implemented and enabled, but a proven configuration or environment fault prevents execution. Currently: CAP-0201 (C-CDA service not listening), CAP-0240 (backup binary path absent) |

No capability may be described as Active while being described elsewhere as
failing, without this qualifier.

Two further independent dimensions are also carried:

**Commercial Readiness** — `Ready` · `Needs Configuration` · `Needs Module
Installation` · `Needs External Integration` · `Needs Product Work` · `Missing`.

**Demo Ready** — `Yes` · `Partial` · `No`. Never inferred from Active: a
capability can be fully Active and still undemonstrable for want of data or a
user account (§28).

---

## 5. Product Architecture / Functional Domain Map

### 5.1 The 21 normalized domains

| # | Domain ID | Domain | Capabilities | A | D | U | RI | M |
|---|---|---|---|---:|---:|---:|---:|---:|
| 1 | D01 | Front Office & Patient Access | 19 | 19 | 0 | 0 | 0 | 0 |
| 2 | D02 | Patient Administration | 13 | 11 | 2 | 0 | 0 | 0 |
| 3 | D03 | Clinical Documentation | 18 | 18 | 0 | 0 | 0 | 0 |
| 4 | D04 | Clinical Data Domains | 12 | 12 | 0 | 0 | 0 | 0 |
| 5 | D05 | Clinical Decision Support | 8 | 7 | 0 | 0 | 1 | 0 |
| 6 | D06 | Dormant Clinical Forms | 16 | 0 | 0 | 16 | 0 | 0 |
| 7 | D07 | Group / Behavioural Therapy | 5 | 0 | 5 | 0 | 0 | 0 |
| 8 | D08 | Orders & Results | 14 | 12 | 0 | 0 | 2 | 0 |
| 9 | D09 | Prescribing & Pharmacy | 13 | 4 | 6 | 0 | 3 | 0 |
| 10 | D10 | Revenue Cycle | 24 | 20 | 4 | 0 | 0 | 0 |
| 11 | D11 | Insurance & Eligibility | 8 | 5 | 1 | 0 | 2 | 0 |
| 12 | D12 | EDI History | 4 | 4 | 0 | 0 | 0 | 0 |
| 13 | D13 | Payments & Gateways | 6 | 1 | 0 | 0 | 5 | 0 |
| 14 | D14 | Reporting & Quality Measures | 12 | 9 | 2 | 0 | 1 | 0 |
| 15 | D15 | Patient Engagement | 10 | 2 | 8 | 0 | 0 | 0 |
| 16 | D16 | Communications | 10 | 3 | 4 | 0 | 3 | 0 |
| 17 | D17 | Interoperability & APIs | 16 | 6 | 9 | 1 | 0 | 0 |
| 18 | D18 | Security, Privacy & Audit | 17 | 13 | 3 | 0 | 0 | 1 |
| 19 | D19 | Administration & Configuration | 20 | 18 | 1 | 0 | 1 | 0 |
| 20 | D20 | Multi-site, Localisation, Extensibility | 15 | 13 | 2 | 0 | 0 | 0 |
| 21 | D21 | Uninstalled Custom & Optional Modules | 10 | 0 | 0 | 10 | 0 | 0 |
| | | **TOTAL** | **270** | **177** | **47** | **27** | **18** | **1** |

### 5.2 Layered architecture (as implemented)

```
┌────────────────────────────────────────────────────────────────────┐
│ PRESENTATION   Knockout SPA shell (interface/main/tabs/main.php)   │
│                one iframe per tab; 7 menu JSONs; Twig + Smarty +   │
│                legacy PHP; 13 themes incl. 13 RTL variants         │
├────────────────────────────────────────────────────────────────────┤
│ ACCESS         GACL (phpGACL 3.3.7) 13 ACO sections / 65 objects   │
│                × 4 levels × 7 ARO groups; sensitivity; ownership   │
│                scope; facility scope; menu role                    │
├────────────────────────────────────────────────────────────────────┤
│ APPLICATION    src/ (PSR-4 OpenEMR\) services · library/ legacy    │
│                procedural · controllers/ legacy MVC                │
├────────────────────────────────────────────────────────────────────┤
│ EXTENSION      Symfony EventDispatcher (~90 events) · Laminas MVC  │
│                modules (type=1) · custom modules (type=0)          │
├────────────────────────────────────────────────────────────────────┤
│ INTEROP        REST 98 · FHIR R4 US Core 80 · Portal 5 · OAuth2/   │
│                SMART · C-CDA (node ccdaservice) · CCR · HL7 v2 ·   │
│                X12 5010 · DICOM viewer          [ALL OFF]          │
├────────────────────────────────────────────────────────────────────┤
│ DATA           MariaDB 11.8.8 · 283 tables · single site `default` │
└────────────────────────────────────────────────────────────────────┘
```

### 5.3 Terminology discipline used throughout

These are kept strictly separate and are **never** collectively called
"modules":

| Concept | Count here | Identifier |
|---|---|---|
| Functional domain | 21 | `D01`–`D21` |
| Capability (atomic user/business action) | 270 | `CAP-*` |
| Pluggable module (Module-Manager installable) | 22 on disk, 5 installed | named |
| Clinical form (encounter form / `registry` entry) | 35 on disk, 18 registered | `FORM-*` |
| Screen / route | 128 | `SCR-*` |
| Report | 55 | `RPT-*` |
| Workflow | 14 | `WF-*` |
| Integration | 36 | `INT-*` |
| Role (ARO group) | 7 | `ROL-*` |
| User account | 4 | `USR-*` |
| Configuration item / feature flag | 120 catalogued | `CFG-*` |
| Gap (market-missing or unresolved) | 73 | `GAP-*` |

---

## 6. Master Capability Catalog

**How to read this catalog.** One row = one normalized business capability, at
the `Domain → Feature → Sub-feature/Action` altitude. Navigation duplicates are
normalized away — a capability reachable from three menus is one row with three
`SCR-*` references (§10).

Column key:
- **Roles** — the ACL groups that can exercise it: `Adm` Administrators,
  `Phy` Physicians, `Cln` Clinicians, `FO` Front Office, `Acc` Accounting,
  `EL` Emergency Login. Derived from the 19 live `gacl_acl` grants (§13.4).
- **ACL** — the governing `section|object` pair.
- **Status** — Primary Status per §4. **CR** — Commercial Readiness.
  **Conf** — Evidence Confidence. **RT** — Runtime Verified.
- Marketing-safe claims and their qualifications are in §27, keyed by `CAP` ID.
  Screen, workflow, report, integration and configuration cross-references are
  in §10, §11, §16, §17 and §19 respectively.

Status legend: `A` Active · `D` Disabled · `U` Uninstalled ·
`RI` Requires Integration · `M` Missing.
CR legend: `Ready` · `Cfg` Needs Configuration · `Mod` Needs Module
Installation · `Ext` Needs External Integration · `Work` Needs Product Work.

### 6.1 D01 — Front Office & Patient Access

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0001 | Registration → Register new patient | Create a patient record with demographics, contacts and identifiers | Adm, Phy, Cln, FO, EL | `patients\|demo` w/addonly | A | Ready | High | No | `interface/new/new.php`; `standard.json:78,93` |
| CAP-0002 | Search → Find patient (Finder) | Search and select a patient by name, DOB, ID or phone | Adm, Phy, Cln, FO, EL | `patients\|demo` | A | Ready | High | No | `interface/main/finder/dynamic_finder.php`; `standard.json:19` |
| CAP-0003 | Search → Any-field demographic search | Free-text search across all demographic fields from the top bar | Adm, Phy, Cln, FO, EL | `patients\|demo` | A | Ready | High | No | `tabs/js/user_data_view_model.js:39-70`; `main.php:504-514` |
| CAP-0004 | Demographics → View / edit patient record | Maintain the full demographic dataset via the configurable DEM layout | Adm, Phy, Cln, FO, EL | `patients\|demo` | A | Ready | High | No | `patient_file/summary/demographics.php` (2,080 ln), `demographics_full.php` |
| CAP-0005 | Demographics → Patient images | Attach and view patient photographs | Adm, Phy, Cln, EL | `patients\|demo` | A | Ready | Med | No | `demographics.php:771,783` (image modals) |
| CAP-0006 | Scheduling → Book appointment | Create, move and cancel appointments on the practice calendar | Adm, Phy, Cln, FO, EL | `patients\|appt` | A | Ready | High | No | `interface/main/calendar/`, `add_edit_event.php`; `standard.json:3` |
| CAP-0007 | Scheduling → Provider & facility calendars | View the calendar filtered by provider, facility and category | Adm, Phy, Cln, FO, EL | `patients\|appt` | A | Ready | High | No | `main/main_info.php`; `openemr_postcalendar_*` |
| CAP-0008 | Scheduling → Recurring appointments | Define a repeating appointment series | Adm, Phy, Cln, FO, EL | `patients\|appt` | A | Ready | Med | No | `main/calendar/add_edit_event.js:283` |
| CAP-0009 | Scheduling → Appointment category configuration | Define appointment types, durations and colours | Adm, EL | `admin\|calendar` | A | Ready | High | No | `standard.json:786`; `openemr_postcalendar_categories` |
| CAP-0010 | Scheduling → Import public holidays | Bulk-load non-working days into the calendar | Adm, EL | `admin\|super` | A | Ready | High | No | `interface/main/holidays/import_holidays.php`; `src/Services/HolidayService.php` |
| CAP-0011 | Patient flow → Track visit status in-clinic | Live board of arrived / roomed / with-provider / checked-out status | Adm, Phy, Cln, FO, EL | `patients\|appt` | A | Ready | High | No | `interface/patient_tracker/`; tables `patient_tracker`, `patient_tracker_element` |
| CAP-0012 | Recalls → Manage recall / follow-up board | Track patients due to return and drive outreach lists | Adm, Phy, Cln, FO, EL | `patients\|appt` | A | Ready | Med | No | `messages.php?go=Recalls`; `medex_recalls` |
| CAP-0013 | Payments → Take a front-desk payment | Record a patient payment or copay at the desk and print a receipt | Adm, Acc, EL | `acct\|bill` write | A | Ready | High | No | `patient_file/front_payment.php` (1,876 ln) → `ar_session`, `payments` |
| CAP-0014 | Checkout → Point-of-sale checkout | Complete a visit: line items, discount, payment, receipt | Adm, Acc, EL | `acct\|bill` write | A | Ready | High | No | `pos_checkout.php` → `pos_checkout_normal.php` (1,255 ln) |
| CAP-0015 | Labels → Print chart / barcode / address labels | Produce patient-identifying labels on demand | Adm, Phy, Cln, FO, EL | `patients\|demo` | A | Ready | Med | No | `label.php`, `barcode_label.php`, `addr_label.php`; `standard.json:2266-2292` |
| CAP-0016 | Directory → Maintain external address book | Store referring providers, pharmacies and external contacts | Adm, Acc, EL | `admin\|practice` | A | Ready | High | No | `usergroup/addrbook_list.php`, `addrbook_edit.php`, `npi_lookup.php` |
| CAP-0017 | Data quality → Detect duplicate patients | Score and list probable duplicate patient records | Adm, EL | `admin\|super` | A | Ready | High | No | `patient_file/manage_dup_patients.php`; `library/dupscore.inc.php:18` |
| CAP-0018 | Data quality → Merge two patient records | Consolidate a duplicate into a surviving record across all tables | Adm, EL | `admin\|super` | A | Ready | High | No | `patient_file/merge_patients.php:304` `resolveTargets()` |
| CAP-0019 | Records → Track physical chart location | Check paper charts in and out and report on their location | Adm, Phy, Cln, FO, EL | `patients\|appt` | A | Ready | Med | No | `custom/chart_tracker.php`; `chart_tracker`; CFG-0031 `disable_chart_tracker=0` |

### 6.2 D02 — Patient Administration

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0020 | Insurance → Record patient coverage | Capture primary, secondary and tertiary policies with subscriber detail | Adm, Phy, Cln, FO, Acc, EL | `patients\|demo` | A | Ready | High | No | `summary/insurance_edit.php`; `insurance_data.type` enum |
| CAP-0021 | Demographics → Record employer | Store the patient's employer for occupational and SDOH purposes | Adm, Phy, Cln, FO, EL | `patients\|demo` | A | Ready | Med | No | `employer_data`; CFG-0036 `omit_employers=0` |
| CAP-0022 | Documents → Upload and categorise patient documents | Store scanned or uploaded files against a patient in a category tree | Adm, Phy, Cln, EL | `patients\|docs` w/addonly | A | Ready | High | No | `controllers/C_Document.class.php`; `documents`, `categories` |
| CAP-0023 | Documents → Manage document / letter templates | Author reusable templates for letters, consents and legal documents | Adm, Acc, EL | `admin\|practice` | A | Ready | High | No | `super/manage_document_templates.php`; `document_templates` |
| CAP-0024 | Records → Record a patient amendment | Log a HIPAA-style amendment request and its resolution | Adm, Phy, Cln, EL | `patients\|amendment` | A | Ready | High | No | `summary/add_edit_amendments.php`; `amendments`, `amendments_history`; CFG-0035 `amendments=1` |
| CAP-0025 | Records → Account for a disclosure | Record who received patient information, when and why | Adm, Phy, Cln, EL | `patients\|disclosure` | A | Ready | High | No | `summary/record_disclosure.php`; `EventAuditLogger::recordDisclosure()` → `extended_log` |
| CAP-0026 | Referrals → Create and print a referral | Raise an outbound referral transaction and print the letter | Adm, Phy, Cln, EL | `patients\|trans` | A | Ready | High | No | `patient_file/transaction/`; `transactions`, `lbt_data`; layout `LBTref` |
| CAP-0027 | Records → Handle a record request | Log and fulfil a request for the patient's record | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | Med | No | `transaction/record_request.php`; `standard.json:179` |
| CAP-0028 | Authorisation → Work the encounter authorisation queue | Review and authorise encounters awaiting sign-off | Adm, Phy, Cln, EL | `encounters\|auth`, `auth_a` | A | Ready | Med | No | `main/authorizations/authorizations.php`; `standard.json:2027` |
| CAP-0029 | Directives → Record advance directives | Capture and surface the patient's advance directives | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | Med | No | `summary/advancedirectives.php`; CFG-0037 `advance_directives_warning=0` |
| CAP-0030 | Care team → Maintain the patient's care team | Record team members, their roles and their period of involvement | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `src/Services/CareTeamService.php`; `care_teams`, `care_team_member` |
| CAP-0031 | Portal → Issue patient portal credentials | Create a portal login for a patient from the chart | Adm, Phy, Cln, FO, EL | `patients\|demo` | **D** | Cfg | High | No | `summary/create_portallogin.php`; blocked by CFG-0071 `portal_onsite_two_enable=0` |
| CAP-0032 | Records → Delete a patient record | Permanently remove a patient and dependent rows | Adm, EL | `admin\|super` | **D** | Cfg | High | No | `patient_file/deleter.php`; blocked by CFG-0038 `allow_pat_delete=0` |

### 6.3 D03 — Clinical Documentation

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0033 | Encounter → Create a visit | Open a dated clinical encounter with provider, facility, class and reason | Adm, Phy, Cln, EL | `patients\|appt` | A | Ready | High | No | `interface/forms/newpatient/`; `form_encounter`, `forms`; FORM-0001 |
| CAP-0034 | Encounter → Add forms to a visit | Attach any registered clinical form to the open encounter | Adm, Phy, Cln, EL | per-form `aco_spec` | A | Ready | High | No | `patient_file/encounter/forms.php`, `load_form.php`; `MainMenuRole::updateVisitForms()` |
| CAP-0035 | Documentation → SOAP note | Structured subjective/objective/assessment/plan note | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/soap/`; `form_soap`; FORM-0004 |
| CAP-0036 | Documentation → Vitals with growth charts | Record vital signs; plot paediatric growth and compute BMI | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/vitals/` + `growthchart/`; `form_vitals`, `form_vital_details`; FORM-0005 |
| CAP-0037 | Documentation → Review of Systems | Narrative/structured systems review | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/ros/`; `form_ros`; FORM-0006 |
| CAP-0038 | Documentation → Review of Systems Checks | Checkbox variant of the systems review | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/reviewofs/`; `form_reviewofs`; FORM-0007 |
| CAP-0039 | Documentation → Clinical Notes (USCDI) | USCDI-aligned clinical note types with document linkage | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/clinical_notes/`; `form_clinical_notes` + 2 link tables; FORM-0008 |
| CAP-0040 | Documentation → Clinical Instructions | Patient-facing instructions recorded in the visit | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/clinical_instructions/`; FORM-0009 |
| CAP-0041 | Documentation → Care Plan | Goal/intervention care plan form (C-CDA care-plan aligned) | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/care_plan/`; `form_care_plan`; FORM-0010 |
| CAP-0042 | Documentation → Functional & Cognitive Status | Record functional and cognitive status assessments | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/functional_cognitive_status/`; FORM-0011 |
| CAP-0043 | Documentation → Observations | Structured coded observations with status and value | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/observation/`; `src/Services/ObservationService.php:37-39`; FORM-0012 |
| CAP-0044 | Documentation → Speech dictation | Free-text dictated note | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/dictation/`; `form_dictation`; FORM-0013 |
| CAP-0045 | Documentation → Ophthalmology eye exam | Full specialty eye examination across 18 linked tables | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | High | No | `interface/forms/eye_mag/`; `form_eye_base` + 17; FORM-0014 |
| CAP-0046 | Documentation → Questionnaire assessment | Render and capture FHIR Questionnaires via LForms web components | Adm, EL | `admin\|forms` | A | Ready | High | No | `interface/forms/questionnaire_assessments/`; `questionnaire_repository`, `questionnaire_response`; FORM-0017 |
| CAP-0047 | Documentation → Layout-Based Form engine | Build custom encounter forms from configurable field layouts, no code | Adm, EL | `admin\|super` | A | Cfg | High | No | `interface/forms/LBF/`; `library/registry.inc.php:138`. **Zero LBF forms configured** — `layout_options` has no `LBF*` form_id |
| CAP-0048 | Signing → Electronically sign a form or encounter | Apply an authenticated e-signature and optionally lock the record | Adm, Phy, Cln, EL | form `aco_spec` | A | Ready | High | No | `library/ESign/`; `esign_signatures`; CFG-0060 `esign_individual=1`, CFG-0061 `esign_all=0` |
| CAP-0049 | Signing → Lock a signed record | Prevent further edits to a signed individual form | Adm, Phy, Cln, EL | form `aco_spec` | A | Ready | High | No | CFG-0062 `lock_esign_individual=1`; `patient_file/encounter/forms.php:540-545` |
| CAP-0050 | Reporting → Generate the full patient chart report | Assemble a printable report across every clinical domain | Adm, Phy, Cln, EL | `patients\|pat_rep` | A | Ready | High | No | `patient_file/report/patient_report.php`, `custom_report.php` |

### 6.4 D04 — Clinical Data Domains

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0051 | Problems → Maintain the problem list | Add, code, verify and resolve medical problems | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `summary/add_edit_issue.php` (1,065 ln); `lists` type `medical_problem`; `issue_types` |
| CAP-0052 | Allergies → Maintain the allergy list | Record allergen, reaction, severity and verification status | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `lists` type `allergy` (`reaction`, `severity_al`, `verification`); `AllergyIntoleranceService` |
| CAP-0053 | Medications → Maintain the medication list | Track active and historical medications distinct from prescriptions | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `lists` type `medication` + `lists_medication`; `MedicationPatientIssueService` |
| CAP-0054 | Immunisations → Record immunisations | Capture administered vaccines, lot, site, route and observations | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `summary/immunizations.php` (1,275 ln); `immunizations`, `immunization_observation`; CFG-0033 `disable_immunizations=0` |
| CAP-0055 | History → Record social & family history | Capture tobacco, alcohol, exercise, substance use and family history | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `patient_file/history/history.php` (HIS layout); `history_data`; `SocialHistoryService.php:29,65` |
| CAP-0056 | SDOH → Record social determinants | Capture USCDI v3 social-determinant screening and health concerns | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `history/history_sdoh*.php`; `src/Services/SDOH/HistorySdohService.php`; `form_history_sdoh` |
| CAP-0057 | History → Record surgical history | Maintain past surgical procedures as a coded issue list | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `src/Services/SurgeryService.php` over `lists` type `surgery` |
| CAP-0058 | History → Record dental issues | Maintain a dental problem list (list only — no odontogram) | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | Med | No | `issue_types` category `default` id `dental`; see GAP-0020 for charting |
| CAP-0059 | Devices → Record an implanted device (UDI) | Scan/parse a UDI barcode and record the implantable device | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `src/MedicalDevice/MedicalDevice.php` (GS1/HIBCC/ICCBBA); `lists` type `medical_device` |
| CAP-0060 | Notes → Patient notes & internal messaging on a chart | Thread notes against a patient and route them to users | Adm, Phy, Cln, EL | `patients\|notes` | A | Ready | High | No | `summary/pnotes_full.php` (807 ln); `pnotes`; CFG-0039 `ignore_pnotes_authorization=1` |
| CAP-0061 | Notes → Office notes | Practice-level (non-patient) notes ledger | Adm, Phy, Cln, EL | `encounters\|notes` | A | Ready | Med | No | `main/onotes/office_comments.php`; `standard.json:2074` |
| CAP-0062 | Preferences → Record care & treatment preferences | Capture patient care-experience and treatment-intervention preferences | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | Med | No | `CareExperiencePreferenceService`, `TreatmentInterventionPreferenceService` |

### 6.5 D05 — Clinical Decision Support

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0063 | CDR → Run the clinical rule engine | Evaluate rule filters and targets across the patient population | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `library/clinical_rules.php` (3,532 ln); 80 rules in `clinical_rules`; CFG-0040 `enable_cdr=1` |
| CAP-0064 | CDR → Show passive reminders in the chart | Surface due care gaps as a dashboard widget | Adm, Phy, Cln, EL | `patients\|alert` | A | Ready | High | No | `clinical_rules.php:67` `clinical_summary_widget()`; CFG-0043 `enable_cdr_crw=1` |
| CAP-0065 | CDR → Raise active alerts on chart open | Pop an alert when a rule fires for the patient being opened | Adm, Phy, Cln, EL | `patients\|alert` | A | Ready | High | No | `reminder/active_reminder_popup.php`; `clinical_rules.php:224`; CFG-0042 `enable_cdr_new_crp=1` |
| CAP-0066 | CDR → Generate patient reminders | Materialise per-patient reminders with due status for outreach | Adm, Phy, Cln, EL | `patients\|reminder` | A | Ready | High | No | `library/reminders.php:107,206`; `patient_reminders` |
| CAP-0067 | Safety → Allergy vs medication conflict check | Warn when an active allergy title matches a medication or prescription | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `clinical_rules.php:309-385` `allergy_conflict()`; CFG-0041 `enable_allergy_check=1`. **Exact string match only** |
| CAP-0068 | CDR → Author and edit rules | Build rules from filters, targets, actions and reminder timing in the UI | Adm, EL | `admin\|super` | A | Ready | High | No | `interface/super/rules/`; `src/ClinicalDecisionRules/` (56 files); rule types `activealert`, `passivealert`, `patientreminder` |
| CAP-0069 | CDR → Activate/deactivate a rule per patient | Override rule applicability for an individual patient | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `clinical_rules.php:2098,2261`; `clinical_rules` keyed `(id,pid)` |
| CAP-0070 | Safety → Drug–drug interaction checking | Screen a new prescription against existing therapy | — | `patients\|rx` | **RI** | Ext | High | No | No interaction engine or database in-tree; `drug_drug` in `C_Prescription.class.php:190,242` is a **result flag returned by the external eRx vendor**. Depends on INT-0009 |

### 6.6 D06 — Dormant Clinical Forms (present on disk, not registered)

All 16 have complete source and a `table.sql`, but no `registry` row and no
backing table in the schema. Registering one through Admin → Forms → Forms
Administration installs its SQL and activates it. Roles/ACL shown are what the
form declares once registered.

| ID | Form | Plain-English description | ACL (declared) | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|
| CAP-0071 | PHQ-9 | Nine-item depression severity screener | `encounters\|notes` | U | Mod | High | No | `interface/forms/phq9/`; no `form_phq9` table |
| CAP-0072 | GAD-7 | Seven-item anxiety severity screener | `encounters\|notes` | U | Mod | High | No | `interface/forms/gad7/`; no `form_gad7` table |
| CAP-0073 | Physical Exam | Structured physical examination with linked diagnoses | `encounters\|notes` | U | Mod | High | No | `interface/forms/physical_exam/` (+`edit_diagnoses.php`) |
| CAP-0074 | Treatment Plan | Structured treatment plan form | `encounters\|notes` | U | Mod | High | No | `interface/forms/treatment_plan/` |
| CAP-0075 | Transfer Summary | Transfer-of-care summary form | `encounters\|notes` | U | Mod | High | No | `interface/forms/transfer_summary/` |
| CAP-0076 | Aftercare Plan | Post-visit aftercare instructions form | `encounters\|notes` | U | Mod | High | No | `interface/forms/aftercare_plan/` |
| CAP-0077 | Graphic Pain Map | Body-diagram pain localisation | `encounters\|notes` | U | Mod | High | No | `interface/forms/painmap/C_FormPainMap.class.php` |
| CAP-0078 | CAMOS | Reusable clinical content/template library with its own admin | `encounters\|notes` | U | Mod | High | No | `interface/forms/CAMOS/` (4 tables, `admin.php`, `rx_print.php`) |
| CAP-0079 | Clinic Note | Read-only clinic note (view/report, no save) | `encounters\|notes` | U | Work | Med | No | `interface/forms/clinic_note/` — no `save.php` |
| CAP-0080 | Work/School Note | Printable work or school excuse note | `encounters\|notes` | U | Mod | High | No | `interface/forms/note/` (+`print.php`) |
| CAP-0081 | Bronchitis | Condition-specific bronchitis assessment | `encounters\|notes` | U | Mod | Med | No | `interface/forms/bronchitis/` |
| CAP-0082 | Ankle Evaluation | Condition-specific ankle injury assessment | `encounters\|notes` | U | Mod | Med | No | `interface/forms/ankleinjury/` |
| CAP-0083 | Social Screening Tool (form-based SDOH) | Alternative form-based SDOH screener + portal variant | `encounters\|notes` | U | Mod | High | No | `interface/forms/sdoh/` (+`patient_portal.php`). Distinct from the active CAP-0056 path |
| CAP-0084 | Lab Requisition | Printable lab requisition with barcode | — | U | Mod | Med | No | `interface/forms/requisition/` (+`barcode.php`) — print-only, no save/report |
| CAP-0085 | Prior Authorization (form) | Encounter-level prior-authorisation form | `encounters\|coding` | U | Mod | High | No | `interface/forms/prior_auth/`; confirmed absent from `registry` (all 18 rows `state=1`, none `prior_auth`) |
| CAP-0086 | Track Anything | User-defined longitudinal tracking of arbitrary measures | — | U | Mod | High | No | `interface/forms/track_anything/` (3 tables); menu item "Configure Tracks" gated on `track_anything_state`, which reads `registry.state` — currently no row |

### 6.7 D07 — Group / Behavioural Therapy

Entire domain hidden by one flag: the `Groups` menu carries
`global_req: "enable_group_therapy"` (`standard.json:203`) and CFG-0044
`enable_group_therapy = 0`.

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0087 | Groups → Maintain therapy group registry | Create and manage standing therapy groups and membership | Adm, EL | `groups\|gadd` | **D** | Cfg | High | No | `interface/therapy_groups/`; `standard.json:207,227` |
| CAP-0088 | Groups → Schedule a group appointment | Book group sessions on the shared calendar | Adm, Phy, Cln, FO, EL | `groups\|gcalendar` | **D** | Cfg | High | No | `standard.json:271`; ACL grants 19, 23 |
| CAP-0089 | Groups → Create a group encounter | Open a clinical encounter attached to a group rather than one patient | Adm, Phy, Cln, EL | `groups\|glog` | **D** | Cfg | High | No | `interface/forms/newGroupEncounter/`; `form_groups_encounter`; FORM-0016 |
| CAP-0090 | Groups → Record attendance | Capture which members attended a group session | Adm, Phy, Cln, EL | `encounters\|notes` | **D** | Cfg | High | No | `interface/forms/group_attendance/`; FORM-0015 |
| CAP-0091 | Groups → Message the personal therapist | Route a note from the group therapist to the member's own therapist | Adm, EL | `groups\|gm` | **D** | Cfg | Med | No | ACO `groups\|gm` exists (`gacl_aco`); granted only to Adm/EL |

### 6.8 D08 — Orders & Results

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0092 | Orders → Place a lab or procedure order | Order tests against an encounter with priority, specimen and diagnosis | Adm, Phy, Cln, EL | `patients\|lab` | A | Ready | High | No | `interface/forms/procedure_order/`; `procedure_order`, `procedure_order_code`; FORM-0018 |
| CAP-0093 | Orders → Maintain lab / order provider registry | Register the labs and providers that receive orders, with transport config | Adm, EL | `admin\|super` | A | Cfg | High | No | `orders/procedure_provider_list.php`, `procedure_provider_edit.php` (protocols `DL`, `SFTP`) |
| CAP-0094 | Orders → Configure the orderable-test compendium | Build the tree of orderable procedures, panels and result codes | Adm, EL | `admin\|super` | A | Cfg | High | No | `orders/types.php`, `types_edit.php`; `procedure_type` |
| CAP-0095 | Orders → Bulk-load a lab compendium | Import a lab's orderable list and AOE questions from CSV | Adm, EL | `admin\|super` | A | Cfg | High | No | `orders/load_compendium.php` → `procedure_type`, `procedure_questions` |
| CAP-0096 | Orders → Ask-on-order-entry questions | Prompt required clinical questions at order time and store answers | Adm, Phy, Cln, EL | `patients\|lab` | A | Ready | High | No | `orders/qoe.inc.php`; `procedure_questions`, `procedure_answers` |
| CAP-0097 | Orders → Generate an HL7 v2 order message | Build a conformant HL7 v2.3 ORM message for the order | Adm, Phy, Cln, EL | `patients\|lab` | A | Ready | High | No | `orders/gen_hl7_order.inc.php`; per-lab variants under `interface/procedure_tools/` |
| CAP-0098 | Orders → Transmit the order to the lab | Deliver the HL7 order by SFTP, filesystem drop or browser download | — | `patients\|lab` | **RI** | Ext | High | No | `gen_hl7_order.inc.php:399-449`; requires a configured `procedure_providers` row + SFTP endpoint. INT-0013 |
| CAP-0099 | Results → Import inbound HL7 results | Parse ORU/MDM messages into reports, results and attached documents | — | `patients\|lab` | **RI** | Ext | High | No | `orders/receive_hl7_results.inc.php` (`rhl7FlushMain`, `rhl7FlushMDM`, `match_patient`, `match_lab`); requires a lab feed. INT-0014 |
| CAP-0100 | Results → Review and sign off a result | Move a report from `received` to `reviewed` and complete the order | Adm, Phy, Cln, EL | `patients\|lab`, `patients\|sign` | A | Ready | High | No | `orders/single_order_results.php:47,52`; `procedure_report.review_status` |
| CAP-0101 | Results → Work the pending-review queue | See all results awaiting clinician review across the practice | Adm, Phy, Cln, EL | `patients\|lab` | A | Ready | High | No | `orders/orders_results.php?review=1`; `standard.json:631` |
| CAP-0102 | Results → Enter results in batch | Key results for many orders in one screen | Adm, Phy, Cln, EL | `patients\|lab` | A | Ready | High | No | `orders/orders_results.php?batch=1`; `standard.json:667` |
| CAP-0103 | Results → Lab overview and trend graphing | View and graph a patient's results over time by result code | Adm, Phy, Cln, EL | `patients\|lab` | A | Ready | High | No | `summary/labdata.php` (517 ln), `labdata_fragment.php` |
| CAP-0104 | Results → Electronic reports inbox | Poll and process inbound electronic report files | Adm, Phy, Cln, EL | `patients\|lab` | A | Ready | High | No | `orders/list_reports.php` |
| CAP-0105 | Results → Match an unidentified result to a patient | Manually reconcile an inbound result that failed auto-matching | Adm, Phy, Cln, EL | `patients\|lab` | A | Ready | High | No | `orders/patient_match_dialog.php`; `receive_hl7_results.inc.php:448` |

### 6.9 D09 — Prescribing & Pharmacy

The whole in-house dispensary sub-domain (CAP-0113…0118) is hidden by CFG-0045
`inhouse_pharmacy = 0`, which gates the `Inventory` menu
(`standard.json:519`) and the `Reports → Inventory` group (`standard.json:1722`).

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0106 | Prescribing → Record a prescription | Capture drug, dose, form, route, quantity, refills, SIG and indication | Adm, Phy, Cln, EL | `patients\|rx` | A | Ready | High | No | `controllers/C_Prescription.class.php`; `prescriptions` (49 cols); CFG-0034 `disable_prescriptions=0` |
| CAP-0107 | Prescribing → Print a prescription | Produce a printable/faxable prescription with optional logo and signature | Adm, Phy, Cln, EL | `patients\|rx` | A | Ready | High | No | `templates/prescription/`; `src/Rx/RxList.php`; CFG-0063 `rx_use_fax_template=1` |
| CAP-0108 | Prescribing → Maintain the pharmacy directory | Hold external pharmacies and assign a default per patient | Adm, Acc, EL | `admin\|practice` | A | Ready | High | No | `controllers/C_Pharmacy.class.php`; `pharmacies`; `src/Pharmacy/Services/ImportPharmacies.php` |
| CAP-0109 | Prescribing → Prescriptions & dispensations report | List prescriptions written and products dispensed over a period | Adm, Phy, Cln, EL | `patients\|rx` | A | Ready | High | No | `reports/prescriptions_report.php`; RPT-0004 |
| CAP-0110 | eRx → Transmit an electronic prescription | Hand the prescription to the external e-prescribing network | — | `patients\|rx` | **RI** | Ext | High | No | `interface/eRx.php` auto-POSTs XML to `erx_newcrop_path`; CFG-0064 `erx_enable=0`, credentials empty. INT-0009 |
| CAP-0111 | eRx → Handle renewal requests | Receive and action pharmacy renewal requests | — | `patients\|rx` | **RI** | Ext | High | No | `interface/eRx.php?page=status`; same dependency |
| CAP-0112 | eRx → EPCS controlled-substance administration | Administer the controlled-substance prescribing entitlement | — | `patients\|rx` | **RI** | Ext | High | No | `interface/eRx.php?page=epcs-admin`; gated on `newcrop_user_role_erxadmin`; `erx_narcotics` |
| CAP-0113 | Dispensary → Maintain drug & product master | Define dispensable drugs/products, forms, units and pricing | Adm, Phy, Cln, EL | `admin\|drugs` | **D** | Cfg | High | No | `interface/drugs/add_edit_drug.php`; `drugs`; blocked by CFG-0045 |
| CAP-0114 | Dispensary → Track lots and expiry | Receive stock into lots with expiry and supplier | Adm, EL | `inventory\|lots` | **D** | Cfg | High | No | `interface/drugs/add_edit_lot.php`; `drug_inventory` |
| CAP-0115 | Dispensary → Dispense to a patient | Issue product to a patient and post the charge to the encounter | Adm, EL | `inventory\|sales` | **D** | Cfg | High | No | `interface/drugs/dispense_drug.php`; `drug_sales`; `FeeSheet.class.php:800-830` |
| CAP-0116 | Dispensary → Transfer stock between locations | Move inventory between warehouses/dispensaries | Adm, EL | `inventory\|transfers` | **D** | Cfg | High | No | `interface/drugs/drug_inventory.php`; `product_warehouse` |
| CAP-0117 | Dispensary → Destroy stock and report | Record destruction of expired/controlled stock for compliance | Adm, EL | `inventory\|destruction` | **D** | Cfg | High | No | `interface/drugs/destroy_lot.php`; `reports/destroyed_drugs_report.php`; RPT-0037 |
| CAP-0118 | Dispensary → Inventory reporting | Report on-hand stock, movement and transactions | Adm, EL | `inventory\|reporting` | **D** | Cfg | High | No | `reports/inventory_list.php`, `inventory_activity.php`, `inventory_transactions.php`; RPT-0034…0036 |

### 6.10 D10 — Revenue Cycle

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0119 | Charge capture → Complete the fee sheet | Select services and products for a visit and generate charges | Adm, Phy, Cln, EL | `encounters\|coding` write | A | Ready | High | No | `interface/forms/fee_sheet/new.php` (1,820 ln); `library/FeeSheet.class.php` (1,618 ln); FORM-0002 |
| CAP-0120 | Coding → Assign service and diagnosis codes | Attach CPT4/HCPCS and ICD-10 codes and link diagnosis to service | Adm, Phy, Cln, Acc, EL | `encounters\|coding`, `coding_a` | A | Ready | High | No | `patient_file/encounter/coding.php`, `diagnosis.php`; `billing.justify`; 19 rows in `code_types` |
| CAP-0121 | Coding → Search the code sets | Look up codes dynamically across the installed code systems | Adm, Phy, Cln, Acc, EL | `encounters\|coding` | A | Ready | High | No | `encounter/find_code_dynamic.php`, `find_code_popup.php`, `search_code.php` |
| CAP-0122 | Coding → Maintain the practice superbill | Curate the practice's quick-pick service list | Adm, EL | `admin\|superbill` | A | Ready | High | No | `encounter/superbill_custom_full.php`; `fee_sheet_options` (10 stock E/M rows) |
| CAP-0123 | Pricing → Apply price levels | Price services by patient price level | Adm, Acc, EL | `acct\|bill`, `acct\|disc` | A | Cfg | High | No | `FeeSheet.class.php:1590-1612`; `prices`. **Only one level (`standard`) defined; `prices` empty** |
| CAP-0124 | Pricing → Apply a discount at checkout | Reduce the balance by money or percentage with a reason code | Adm, Phy, Acc, EL | `acct\|disc` | A | Ready | High | No | `pos_checkout_normal.php:476,677-686,1132`; CFG-0046 `discount_by_money=1`; `list_options.adjreason` (18) |
| CAP-0125 | Claims → Capture claim-level HCFA attributes | Record boxes 10–23: accident, referral, hospitalisation, prior auth | Adm, Phy, Cln, Acc, EL | `encounters\|coding` | A | Ready | High | No | `interface/forms/misc_billing_options/`; `form_misc_billing_options`; FORM-0003 |
| CAP-0126 | Claims → Work the Billing Manager | Search, filter, batch, validate and act on outstanding claims | Adm, Acc, EL | `acct\|eob` OR `acct\|bill` write | A | Ready | High | No | `interface/billing/billing_report.php` (1,472 ln) |
| CAP-0127 | Claims → Validate a claim before release | Run validation-only or validate-and-clear passes over a batch | Adm, Acc, EL | `acct\|bill` write | A | Ready | High | No | `src/Billing/BillingProcessor/BillingProcessor.php:204-221` (`validate-only`, `validate-and-clear`) |
| CAP-0128 | Claims → Generate an X12 5010 837P claim | Produce a professional electronic claim batch file | Adm, Acc, EL | `acct\|bill` write | A | Ready | High | No | `src/Billing/X125010837P.php:40` (1,640 ln); tasks `GeneratorX12`, `GeneratorX12Direct` |
| CAP-0129 | Claims → Generate an X12 5010 837I claim | Produce an institutional electronic claim batch file | Adm, Acc, EL | `acct\|bill` write | **D** | Cfg | High | No | `src/Billing/X125010837I.php` (1,225 ln); blocked by CFG-0047 `ub04_support=0` |
| CAP-0130 | Claims → Print a CMS-1500 | Render the professional paper claim as text, PDF, or PDF over a form image | Adm, Acc, EL | `acct\|bill` write | A | Ready | High | No | `src/Billing/Hcfa1500.php` (762 ln); `GeneratorHCFA`, `GeneratorHCFA_PDF`, `GeneratorHCFA_PDF_IMG` |
| CAP-0131 | Claims → Print a UB-04 (CMS-1450) | Render the institutional paper claim | Adm, Acc, EL | `acct\|bill` write | **D** | Cfg | High | No | `interface/billing/ub04_form.php` (1,420 ln); blocked by CFG-0047 |
| CAP-0132 | Claims → Manage EDI control numbers | Allocate and track ISA/GS interchange control numbers per batch | Adm, Acc, EL | `acct\|bill` write | A | Ready | High | No | `BillingClaimBatchControlNumber.php`; `edi_sequences` |
| CAP-0133 | Claims → Push claim batches to the partner by SFTP | Automatically deliver generated batches to the clearinghouse | Adm, Acc, EL | `acct\|bill` write | **D** | Cfg+Ext | High | No | `X12RemoteTracker.php` (phpseclib3); blocked by CFG-0048 `auto_sftp_claims_to_x12_partner=0`; background service `X12_SFTP` `active=0`. Also needs INT-0011 |
| CAP-0134 | Claims → Track claim-file delivery | Monitor and retry SFTP claim-file deliveries | Adm, Acc, EL | `acct\|eob` OR `acct\|bill` write | **D** | Cfg | High | No | `interface/billing/billing_tracker.php`; menu gated on CFG-0048; `x12_remote_tracker` empty |
| CAP-0135 | Payments → Post an 835 ERA automatically | Parse an electronic remittance and post payments and adjustments | Adm, Acc, EL | `acct\|eob` | A | Ready | High | No | `src/Billing/ParseERA.php`; `interface/billing/sl_eob_process.php` (915 ln), `era_payments.php`; CFG-0049 `enable_posting=1` |
| CAP-0136 | Payments → Post an EOB manually | Key insurer payments and adjustments invoice by invoice | Adm, Acc, EL | `acct\|eob` | A | Ready | High | No | `sl_eob_search.php` (1,408 ln) → `sl_eob_invoice.php` (819 ln) |
| CAP-0137 | Payments → Enter a batch payment | Record a cheque/deposit and allocate it across accounts | Adm, Acc, EL | `acct\|eob` OR `acct\|bill` write | A | Ready | High | No | `interface/billing/new_payment.php` → `ar_session`, `ar_activity`; CFG-0050 `enable_batch_payment=1` |
| CAP-0138 | Payments → Allocate, adjust and reverse | Apply, edit, search and delete payment allocations with logging | Adm, Acc, EL | `acct\|eob` | A | Ready | High | No | `edit_payment.php` (1,115 ln), `search_payments.php` (615 ln); `src/PaymentProcessing/Recorder.php` |
| CAP-0139 | AR → Patient ledger | Show a patient's charges, payments and adjustments over a period | Adm, Acc, EL | `acct\|rep`, `rep_a` | A | Ready | High | No | `reports/pat_ledger.php`; RPT-0028; CFG-0051 `ledger_begin_date=Y1` |
| CAP-0140 | AR → Collections and aging | Age receivables into configurable buckets and drive collections | Adm, Acc, EL | `acct\|rep_a` | A | Ready | High | No | `reports/collections_report.php:114-122,649-655` (N cols × M days, default 3×30); RPT-0027 |
| CAP-0141 | Payments → Void a checkout or receipt | Reverse a completed checkout with an audit trail | Adm, Acc, EL | `acct\|bill` write | A | Ready | High | No | `patient_file/void_dialog.php`; `voids`; `BillingUtilities::doVoid` |
| CAP-0142 | AR → Produce patient statements | Generate patient billing statements with optional dunning messages | Adm, Acc, EL | `acct\|rep_a` | A | Cfg | Med | No | `collections_report.php` statement path; CFG-0052 `use_custom_statement=0`; dunning globals defined but absent from `globals` |

### 6.11 D11 — Insurance & Eligibility

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0143 | Payers → Maintain insurance companies | Hold payers with CMS/payer IDs, addresses and default routing | Adm, Acc, EL | `admin\|practice` | A | Cfg | High | No | `interface/practice/ins_list.php`, `ins_search.php`; `insurance_companies` (**0 rows**) |
| CAP-0144 | Payers → Configure an X12 partner | Define envelope IDs, submitter data and SFTP credentials per trading partner | Adm, Acc, EL | `admin\|practice` | A | Cfg | High | No | `controller.php?practice_settings&x12_partner&action=list` → `controllers/C_X12Partner.class.php`; `templates/x12_partners/` (35 editable fields); **0 rows** |
| CAP-0145 | Coverage → Record primary/secondary/tertiary | Order a patient's policies and set effective dates and copays | Adm, Phy, Cln, FO, Acc, EL | `patients\|demo` | A | Ready | High | No | `insurance_data.type` enum; `summary/insurance_edit.php` |
| CAP-0146 | Coverage → Coordinate benefits to the secondary | Roll a balance to the next payer after the primary adjudicates | Adm, Acc, EL | `acct\|eob` | A | Ready | High | No | `src/Billing/SLEOB.php:271` `arSetupSecondary()`; `Claim.php:1004` `payerSequence()`; `form_encounter.last_level_billed` |
| CAP-0147 | Eligibility → Generate a 270 batch enquiry | Build an X12 270 eligibility batch for a set of patients | Adm, Acc, EL | `patients\|demo` | **RI** | Ext | High | No | `interface/billing/edi_270.php`; `src/Billing/EDI270.php` (1,162 ln). Needs an `x12_partners` row (0 present). INT-0011 |
| CAP-0148 | Eligibility → Real-time eligibility (CAQH CORE 2.2.0) | Query a payer for live eligibility over HTTPS and show the result | — | `patients\|demo` | **D** | Cfg+Ext | High | No | `EDI270.php:776-870` (CORE MIME envelope); blocked by CFG-0053 `enable_eligibility_requests=0`; endpoint empty |
| CAP-0149 | Eligibility → Import and display a 271 response | Load a payer's eligibility response and render benefits | — | `patients\|demo` | **RI** | Ext | High | No | `interface/billing/edi_271.php`; `library/edihistory/edih_271_html.php`; needs a real 271 file |
| CAP-0150 | Authorisation → Record a prior-authorisation number | Store the payer authorisation number on the claim (CMS-1500 box 23) | Adm, Phy, Cln, Acc, EL | `encounters\|coding` | A | Ready | High | No | `form_misc_billing_options.prior_auth_number`; the only native prior-auth storage |

### 6.12 D12 — EDI History

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0151 | EDI → Upload and index X12 files | Ingest X12 files and build a searchable CSV index | Adm, Acc, EL | `acct\|eob` | A | Ready | High | No | `library/edihistory/edih_uploads.php`, `edih_csv_parse.php` (1,597 ln) |
| CAP-0152 | EDI → View 835/271/277/278/999 content | Render remittances, eligibility, claim status, prior auth and acknowledgements | Adm, Acc, EL | `acct\|eob` | A | Ready | High | No | `edih_835_html.php` (1,695), `edih_271_html.php`, `edih_277_html.php`, `edih_278_html.php`, `edih_997_error.php` |
| CAP-0153 | EDI → Trace a claim through the files | Find a claim by trace number, control number or encounter | Adm, Acc, EL | `acct\|eob` | A | Ready | High | No | `edih_io.php:382,580` (`csv_file_by_trace`, `csv_file_by_controlnum`) |
| CAP-0154 | EDI → Archive historic EDI files | Age off old index entries and files | Adm, Acc, EL | `acct\|eob` | A | Ready | Med | No | `edih_archive.php` (1,305 ln). **Never initialised here**: `sites/default/documents/edi/history/` does not exist |

### 6.13 D13 — Payments & Gateways

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0155 | Gateway → Record payments in-house (no gateway) | Take cash/cheque/card-offline payments recorded directly in OpenEMR | Adm, Acc, EL | `acct\|bill` write | A | Ready | High | No | CFG-0054 `payment_gateway = InHouse`; `library/payment.inc.php`; `list_options.payment_method` (6) |
| CAP-0156 | Gateway → Stripe card payments | Charge a card online via Stripe | — | `acct\|bill` write | **RI** | Ext | High | No | `front_payment_cc.php:21-23,115-173`; `portal_payment.stripe.js`. Keys empty. INT-0019 |
| CAP-0157 | Gateway → Stripe Terminal (card present) | Take an in-clinic card-present payment on a terminal | — | `acct\|bill` write | **RI** | Ext | High | No | `front_payment_terminal.php`; CFG-0055 `cc_stripe_terminal=0`. INT-0019 |
| CAP-0158 | Gateway → Authorize.Net | Charge a card via Authorize.Net | — | `acct\|bill` write | **RI** | Ext | **High** | No | `PaymentGateway.php:145-146` `Omnipay::create("AuthorizeNetApi_Api")`. **Driver IS installed**: `academe/omnipay-authorizenetapi ^3.1.2` in `composer.json:48`, present in `composer.lock:74` and `vendor/academe/omnipay-authorizenetapi`. Only credentials are missing. INT-0020. GAP-0067 **CLOSED** |
| CAP-0159 | Gateway → Sphere / TrustCommerce | Charge a card via Sphere | — | `acct\|bill` write | **RI** | Ext | High | No | `src/PaymentProcessing/Sphere/`; 10 `sphere_*` globals all empty. INT-0021 |
| CAP-0160 | Gateway → Rainforest Pay | Charge a card via Rainforest with webhook confirmation | — | `acct\|bill` write | **RI** | Ext | High | No | `src/PaymentProcessing/Rainforest/`; `interface/webhooks/payment/rainforest.php`; 4 globals empty. INT-0022 |

### 6.14 D14 — Reporting & Quality Measures

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0161 | Reporting → Clinical & patient reports | Cohort search and clinical listings across dx, meds, allergies, labs | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `reports/clinical_reports.php`, `referrals_report.php`, `message_list.php`; RPT-0001…0007 |
| CAP-0162 | Reporting → Build patient lists | Generate configurable patient lists with per-column ACL enforcement | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `reports/patient_list_creation.php:826`; RPT-0003 |
| CAP-0163 | Reporting → Appointment & encounter reports | Report on appointments, encounters, flow board and missing charges | Adm, Phy, Cln, FO, Acc, EL | `patients\|appt`, `encounters\|coding_a` | A | Ready | High | No | `reports/appointments_report.php`, `encounters_report.php`, `appt_encounter_report.php`; RPT-0008…0018 |
| CAP-0164 | Reporting → Financial reports | Sales, receipts, payment method, aging, ledger, service-code summary | Adm, Acc, EL | `acct\|rep`, `rep_a` | A | Ready | High | No | `reports/sales_by_item.php`, `svc_code_financial_report.php`, `receipts_by_method_report.php`, …; RPT-0024…0032 |
| CAP-0165 | Reporting → Insurance reports | Payer distribution, indigent patients, unique seen patients | Adm, Acc, EL | `acct\|rep_a`, `patients\|demo` | A | Ready | High | No | `reports/insurance_allocation_report.php`, `billing/indigent_patients_report.php`; RPT-0038…0040 |
| CAP-0166 | Reporting → Inventory reports | Stock list, movement activity and transaction ledger | Adm, EL | `inventory\|reporting` | **D** | Cfg | High | No | RPT-0034…0037; menu gated by CFG-0045 `inhouse_pharmacy=0` |
| CAP-0167 | Reporting → Procedure & lab reports | Pending results, pending follow-up and result statistics | Adm, Phy, Cln, EL | `patients\|lab` | A | Ready | High | No | `orders/pending_orders.php`, `pending_followup.php`, `procedure_stats.php`; RPT-0041…0043 |
| CAP-0168 | Quality → Run clinical quality measures (CQM) | Calculate CQM numerators/denominators over a reporting period | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `reports/cqm.php`; **18 CQM rules** in `clinical_rules`; CFG-0056 `enable_cqm=1`. Measures are 2011/2014-era |
| CAP-0169 | Quality → Run automated measure calculations (AMC) | Calculate the AMC/MU objective measures | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `reports/cqm.php?type=amc`; **42 AMC rules**; CFG-0057 `enable_amc=1` |
| CAP-0170 | Quality → Export QRDA Category I / III | Produce certified QRDA quality-reporting files | — | `patients\|med` | **RI** | Ext | High | No | `src/Services/Qrda/`, `src/Cqm/`, `src/Services/Qdm/` (26 datatype services). `MeasureService.php:23,108-114` resolves measures from `ccdaservice/node_modules/oe-cqm-parsers/<year>/json_measures` — **directory absent** ⇒ zero measures available |
| CAP-0171 | Reporting → IPPF / family-planning statistics | Member-association, GCAC, MA, CYP and daily-record statistics | Adm, Acc, EL | `acct\|rep_a` | **D** | Cfg | High | No | `reports/ippf_statistics.php`, `ippf_cyp_report.php`, `ippf_daily.php`; menu gated on `ippf_specific`; CFG-0058 `specific_application=0`; RPT-0044…0048 |
| CAP-0172 | Reporting → Report results history | Re-open previously generated CDR/quality report runs | Adm, Phy, Cln, EL | `patients\|med` | A | Ready | High | No | `reports/report_results.php`; `report_results` (**0 rows — never run here**); RPT-0019 |

### 6.15 D15 — Patient Engagement

The entire onsite portal is implemented and complete on disk, and is switched
off by one flag: CFG-0071 `portal_onsite_two_enable = 0`. Its address is still
the installer placeholder `https://your_web_site.com/openemr/portal`
(CFG-0072), and the reCAPTCHA keys required for self-registration and password
reset are empty (CFG-0073/0074).

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0173 | Portal → Patient logs in to the portal | Give patients a secure web account to view their record | Patient | `patientportal\|portal` | **D** | Cfg | High | No | `portal/index.php`, `portal/home.php`; `patient_access_onsite`; CFG-0071 |
| CAP-0174 | Portal → Patient self-registration | Let a patient create their own portal account | Patient | — | **D** | Cfg | High | No | `portal/account/register.php`, `verify.php`; CFG-0075 `portal_onsite_two_register=0`; reCAPTCHA keys empty |
| CAP-0175 | Portal → Secure patient messaging | Two-way secure messages between patient and practice | Patient, Adm, Phy, Cln | `patientportal\|portal` | **D** | Cfg | High | No | `portal/messaging/`; `onsite_mail`, `onsite_messages` |
| CAP-0176 | Portal → Patient requests an appointment | Let the patient request or book an appointment online | Patient | `patients\|appt` | **D** | Cfg | High | No | `portal/add_edit_event_user.php`, `find_appt_popup_user.php`; CFG-0076 `allow_portal_appointments=1` (but portal off) |
| CAP-0177 | Portal → Patient uploads / downloads documents | Exchange documents and forms with the patient | Patient | `patients\|docs` | **D** | Cfg | High | No | `portal/get_patient_documents.php`, `import_template_ui.php`; CFG-0077 `allow_portal_uploads=1`, CFG-0078 `portal_onsite_document_download=1` |
| CAP-0178 | Portal → Patient views their ledger | Show the patient their account balance and history | Patient | — | **D** | Cfg | High | No | CFG-0079 `portal_two_ledger=1` (portal off) |
| CAP-0179 | Portal → Patient pays online | Take a card payment from the patient through the portal | Patient | — | **D** | Cfg+Ext | High | No | `portal/portal_payment.php`, `portal/lib/paylib.php`; CFG-0080 `portal_two_payments=0`; also needs a gateway (CAP-0156…0160) |
| CAP-0180 | Portal → Patient signs a consent / document | Capture a patient e-signature on a template-driven document | Patient | — | **D** | Cfg | High | No | `portal/sign/`, `assets/signer_api.js`; `onsite_signatures`, `onsite_documents` |
| CAP-0181 | Education → Look up patient education material | Fetch condition-specific education from an external library | Adm, Phy, Cln, EL | — | A | Ready | High | No | `patient_file/education.php:38` → MedlinePlus Connect; `reports/patient_edu_web_lookup.php`; INT-0028 (public, no credential) |
| CAP-0182 | Outreach → Batch communication tool | Build and run a bulk patient communication list | Adm, Acc, EL | `admin\|batchcom` OR `admin\|practice` | A | Ready | Med | No | `interface/batchcom/batchcom.php`, `batch_reminders.php`, `smsnotification.php` |

### 6.16 D16 — Communications

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0183 | Messaging → Internal staff messaging | Send, route and action messages between staff, optionally patient-linked | Adm, Phy, Cln, FO, EL | `patients\|notes` | A | Ready | High | No | `interface/main/messages/messages.php`; `pnotes`; `standard.json:62` |
| CAP-0184 | Messaging → Dated user reminders | User-to-user reminders with a due date and an alert counter | Adm, Phy, Cln, FO, EL | `patients\|notes` | A | Ready | High | No | `interface/main/dated_reminders/`; `dated_reminders`, `dated_reminders_link`; CFG-0059 `dated_reminders_max_alerts_to_show=5` |
| CAP-0185 | Email → Send outbound email | Deliver mail via SMTP from a queued background service | — | — | **RI** | Ext | High | No | CFG-0081 `EMAIL_METHOD=SMTP`; background service `Email_Service` `active=1`; **`practice_return_email_path` and `patient_reminder_sender_email` blank ⇒ sends no-op**. INT-0026 |
| CAP-0186 | SMS → Send an SMS notification | Send text notifications through a gateway | — | — | **RI** | Ext | High | No | `interface/batchcom/smsnotification.php`; `SMS_GATEWAY_USENAME`/`_PASSWORD`/`_APIKEY` all empty. INT-0025 |
| CAP-0187 | Fax → Send a fax via HylaFAX | Fax documents and prescriptions through a local HylaFAX spool | — | `patients\|docs` | **D** | Cfg+Ext | High | No | CFG-0082 `enable_hylafax=0`; `hylafax_server=localhost`. INT-0027 |
| CAP-0188 | Fax → Fax / scan queue | Work an inbound and outbound fax/scan queue in the UI | — | `patients\|docs` | **D** | Cfg | High | No | `interface/fax/faxq.php`, `fax_dispatch.php`; menu `global_req: ["enable_hylafax","enable_scanner"]` — **both 0**, so hidden (CFG-0082, CFG-0083) |
| CAP-0189 | Direct → Send/receive Direct secure messages | Exchange clinical documents over the DIRECT protocol via phiMail | — | `patients\|docs` | **D** | Cfg+Ext | High | No | `library/direct_message_check.inc.php`; CFG-0084 `phimail_enable=0`; server is the placeholder `https://phimail.example.com:32541`; background service `phimail` `active=0, running=-1`. INT-0016 |
| CAP-0190 | Outreach → MedEx recall & messaging service | Automated patient recall/outreach campaigns | — | `patients\|appt` | **D** | Cfg+Ext | High | No | `library/MedEx/`; CFG-0085 `medex_enable=0`; background service `MedEx` `active=0`, `execute_interval=0`. INT-0029 |
| CAP-0191 | Reminders → Generate appointment reminders | Build the reminder list from rules and appointments | Adm, Phy, Cln, FO, EL | `patients\|reminder` | A | Ready | High | No | `interface/batchcom/batch_reminders.php`; `library/reminders.php:342` `send_reminders()` |
| CAP-0192 | Reminders → Deliver reminders to patients | Actually send the generated reminders by email/SMS/voice | — | `patients\|reminder` | **RI** | Ext | High | No | `library/reminders.php:383` sets `email_status`; **voice/SMS send is commented out at `:423`**; depends on CAP-0185/0186 |

### 6.17 D17 — Interoperability & APIs

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0193 | API → Standard REST API | 98 routes over patients, encounters, clinical data, insurance, documents | API client | per-route (11 ACL pairs) | **D** | Cfg | High | Yes¹ | `apis/routes/_rest_routes_standard.inc.php` (717 ln); CFG-0086 `rest_api=0` |
| CAP-0194 | API → FHIR R4 US Core API | 80 routes over 35 FHIR resources, read/search plus 3 writable resources | API client | per-route | **D** | Cfg | High | Yes¹ | `_rest_routes_fhir_r4_us_core_3_1_0.inc.php` (876 ln); FHIR `4.0.1` (`FhirMetaDataRestController.php:59-61`); CFG-0087 `rest_fhir_api=0` |
| CAP-0195 | API → FHIR CapabilityStatement & discovery | Publish server capability and SMART configuration metadata | API client | — | **D** | Cfg | High | Yes¹ | `GET /fhir/metadata` (`:817`), `/.well-known/smart-configuration` (`:821`), `OperationDefinition` (`:826,833`) |
| CAP-0196 | API → SMART on FHIR app launch | EHR-launch and standalone launch of third-party clinical apps | API client | `admin\|super` | **D** | Cfg | High | No | `src/FHIR/SMART/SmartLaunchController.php:40-41,52,114`; `interface/smart/ehr-launch-client.php` |
| CAP-0197 | API → Bulk FHIR export ($export) | System, Patient and Group level bulk data export with status polling | API client | `admin\|super` | **D** | Cfg | High | No | Routes `:598,366,842,858,868`; `src/FHIR/Export/`; CFG-0089 `rest_system_scopes_api=0` |
| CAP-0198 | API → Patient Portal API | 5 read-only routes scoped to the authenticated patient's own data | Patient | patient-bound | **D** | Cfg | High | No | `_rest_routes_portal.inc.php`; CFG-0088 `rest_portal_api=0` |
| CAP-0199 | API → OAuth2 authorization server | Issue tokens by authorization-code+PKCE, refresh and client-credentials | API client | — | **D** | Cfg | High | Yes¹ | `src/RestControllers/AuthorizationController.php:709-760`; `oauth2/authorize.php`; **`oauth_clients` = 0 rows**, `site_addr_oath` empty |
| CAP-0200 | API → Dynamic client registration & management | Self-service OAuth client registration (RFC 7591/7592) and introspection | API client | — | **D** | Cfg | High | No | `AuthorizationController.php:1552,1590,1609`; CFG-0090 `oauth_app_manual_approval=0` |
| CAP-0201 | Interop → Generate and import C-CDA | Produce and reconcile Continuity-of-Care documents for transitions of care | Adm, Phy, Cln, EL | module ACL | A / **Op: BLOCKED** | Cfg | High | **Yes (negative)** | Module **Carecoordination** active (`mod_id=5`); but `ccdaservice/serveccda.js` binds 127.0.0.1:6661 and **nothing is listening** — `Get-NetTCPConnection -LocalPort 6661` returns no listener. C-CDA generation cannot run. GAP-0065 **CLOSED** |
| CAP-0202 | Interop → Generate a CCR | Produce an ASTM Continuity of Care Record | Adm, Phy, Cln, EL | module ACL | A | Ready | High | No | Laminas module **Ccr** (`mod_id=4`, active); `ccr/createCCR.php` + 8 builders; CFG-0091 `activate_ccr_ccd_report=1` |
| CAP-0203 | Interop → Immunisation registry reporting | Export immunisation data for a state/national registry | Adm, Phy, Cln, EL | module ACL | A | Cfg | Med | No | Laminas module **Immunization** (`mod_id=1`, active); `reports/immunization_report.php` (HL7 v2.5.1 generation at `:260`); RPT-0007 |
| CAP-0204 | Interop → Syndromic surveillance reporting | Generate HL7 v2 ADT messages for public-health surveillance | Adm, Phy, Cln, EL | module ACL | A | Cfg | High | No | Laminas module **Syndromicsurveillance** (`mod_id=2`, active); `SyndromicsurveillanceTable.php:157-166,255-256` (MSH/PID/OBX, `ADT^A04`); `reports/non_reported.php:165` (`ADT^A01`) |
| CAP-0205 | Imaging → View DICOM studies | Open DICOM files stored as patient documents in a browser viewer | Adm, Phy, Cln, EL | `patients\|docs` | A | Ready | High | No | `library/dicom_frame.php`; DWV viewer in `public/assets/dwv/`; `standard.json:2007`. **Viewer only — no PACS query/retrieve** |
| CAP-0206 | Interop → ONC (b)(10) EHI export | Export the complete electronic health information set for a patient | — | — | **U** | Mod | High | No | `interface/modules/custom_modules/oe-module-ehi-exporter/`; not in `modules`; its 4 tables absent |
| CAP-0207 | API → Administer API clients | Register, approve and revoke API/SMART client applications | Adm, EL | `admin\|super` | **D** | Cfg | High | No | `interface/smart/admin-client.php`; `standard.json:1152`; useless while CAP-0193/0194 are off |
| CAP-0208 | Extensibility → Module event/hook API | Let modules extend menus, dashboards, billing, API routes and scopes | Developer | — | A | Ready | High | No | `src/Events/` — ~90 event constants incl. `menu.update`, `globals.initialized`, `restConfig.route_map.create`, `api.scope.get-supported-scopes`, `documents.remote.storage.location` |

¹ Runtime-verified as *reachable but rejecting*: `api_log` holds 15 anonymous
probe rows from 2026-08-09 against `/oauth2/authorize.php/default/smart/smart-style`
and `/apis/dispatch.php/default/fhir/metadata`. The endpoints answer; the
feature flags deny the function.

### 6.18 D18 — Security, Privacy & Audit

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0209 | Auth → Authenticate with username & password | Verify credentials against a modern password hash | All | — | A | Ready | High | **Yes** | `src/Common/Auth/AuthUtils.php:275-499`; `AuthHash.php:52-124`; effective algorithm **bcrypt cost 10**; verified by `password_verify()` |
| CAP-0210 | Auth → Enforce password policy | Minimum/maximum length, complexity, expiry, grace and reuse history | All | — | A | Ready | High | No | `AuthUtils.php:1017-1104,726-750`; live: min 9, max 72, complexity on, expiry 180 d + 30 d grace, history 5 |
| CAP-0211 | Auth → Lock an account after failed logins | Block a user after N failures, auto-releasing after a timeout | All | — | A | Ready | High | No | `AuthUtils.php:1163-1188,1260-1279`; live: 20 attempts / 3,600 s |
| CAP-0212 | Auth → Block an abusive IP | Track and block source IPs after repeated failures, or manually | All | — | A | Ready | High | No | `AuthUtils.php:1194-1231`; live: 100 attempts / 3,600 s; `ip_tracking` (1 row, clean) |
| CAP-0213 | Auth → Administer IP blocks | Review, reset, block and unblock source IPs | Adm, EL | `admin\|super` | A | Ready | High | No | `reports/ip_tracker.php`; `AuthUtils.php:1315-1354`; RPT-0052 |
| CAP-0214 | Session → Expire idle sessions | End a session after a configurable idle period | All | — | A | Ready | High | No | `src/Common/Session/SessionTracker.php:40-63`; CFG-0092 `timeout=7200`, CFG-0093 `portal_timeout=1800` |
| CAP-0215 | Session → Invalidate sessions on credential change | Force logout everywhere when a password changes or an account deactivates | All | — | A | Ready | High | No | `AuthUtils::authCheckSession()` `:837-861`; `library/auth.inc.php:97-101` |
| CAP-0216 | MFA → Time-based one-time password (TOTP) | Enrol an authenticator app as a second factor | All (opt-in) | — | A | Work | High | No | `src/Common/Auth/MfaUtils.php:136-187`; `library/classes/Totp.class.php`; enrol at `usergroup/mfa_totp.php`. **Challenged at browser login for enrolled users** (`main_screen.php:148-330`) and on the OAuth2 path. Cannot be mandated — see CAP-0218. **0 enrolments** |
| CAP-0217 | MFA → U2F security key | Enrol a FIDO U2F hardware key as a second factor | All (opt-in) | — | A | Work | High | No | `MfaUtils.php:194-225`; `vendor/yubico/u2flib-server`; enrol at `usergroup/mfa_u2f.php`. **Also challenged at browser login for enrolled users** (`main_screen.php:68-92,179-290`). Cannot be mandated. **0 enrolments** |
| CAP-0218 | MFA → **Mandate** MFA for all users | Force every practitioner to use a second factor, whether or not they enrol | — | — | **M** | Work | High | **Yes (negative)** | MFA *is* challenged at browser login, but **only for already-enrolled users**: `main_screen.php:153-170` reads `login_mfa_registrations` and sets ``; `:171` gates the entire challenge on it. An unenrolled user completes login with password alone. **No `force_mfa`/`mfa_required`/`require_mfa`/`gbl_mfa` global exists** anywhere in `library/globals.inc.php`. GAP-0060 **CLOSED — confirmed product limitation** |
| CAP-0219 | Auth → LDAP / Active Directory sign-in | Authenticate users against a corporate directory | — | — | **D** | Cfg | High | No | `AuthUtils.php:871-976` (StartTLS supported); CFG-0094 `gbl_ldap_enabled=0`; host/DN blank |
| CAP-0220 | Auth → Google Sign-In | Let staff sign in with a Google identity | — | — | **D** | Cfg+Ext | High | No | `AuthUtils.php:1443-1517`; CFG-0095 `google_signin_enabled=0`; client ID blank; all `users.google_signin_email` NULL |
| CAP-0221 | Access → Role-based access control | Grant or deny each function per role at four permission levels | Adm, EL | `admin\|acl` | A | Ready | High | No | `src/Common/Acl/AclMain.php:166-238`, `AclExtended.php` (1,169 ln); 13 sections / 65 ACOs / 7 groups / 19 grants |
| CAP-0222 | Privacy → Restrict sensitive encounters | Hide encounters marked `high` sensitivity from users without the right | Adm, Phy, EL | `sensitivities\|normal`, `high` | A | Ready | High | No | `EncounterService.php:449-450,711-721`; `C_EncounterVisitForm.class.php:536-539`; `patient_file/history/encounters.php:483-554` |
| CAP-0223 | Access → Break-glass emergency login | Provide full emergency access with forced complete audit logging | EL | all | A | Ready | High | No | ARO group `breakglass` (id 16) with all 65 ACOs; `src/Common/Logging/BreakglassChecker.php`; CFG-0096 `gbl_force_log_breakglass=1`. **No user assigned** |
| CAP-0224 | Audit → Log and tamper-detect all activity | Record events and SQL activity and prove the log has not been altered | Adm, EL | `admin\|users`, `admin\|super` | A | Ready | **High** | **Yes** | `src/Common/Logging/EventAuditLogger.php`, `Audit/LogTablesSink.php:63,83` (SHA3-512); `reports/audit_log_tamper_report.php:249,255`. **Verified: 200/200 recomputed checksums matched** |
| CAP-0225 | Audit → Forward audit events to a syslog/ATNA collector | Stream audit events to an external SIEM over TLS syslog | — | — | **D** | Cfg+Ext | High | No | `src/Common/Logging/Audit/AtnaSink.php`; CFG-0097 `enable_atna_audit=0`; host and certs blank |

### 6.19 D19 — Administration & Configuration

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0226 | Config → Edit system settings | Change any of 491 settings across 23 categories from the UI | Adm, EL | `admin\|super` | A | Ready | High | No | `interface/super/edit_globals.php`; `library/globals.inc.php` (4,563 ln, `$GLOBALS_METADATA` at `:183`) |
| CAP-0227 | Users → Create and manage user accounts | Add users, set roles, provider status, facility, NPI and credentials | Adm, EL | `admin\|users` | A | Ready | High | No | `usergroup/usergroup_admin.php`, `usergroup_admin_add.php`, `user_admin.php` |
| CAP-0228 | Access → Administer the ACL | Create and edit permission grants and role membership | Adm, EL | `admin\|acl` | A | Ready | High | No | `usergroup/adminacl.php` driving `AclExtended` |
| CAP-0229 | Org → Manage facilities | Define clinics with address, NPI, POS code, tax IDs and billing role | Adm, EL | `admin\|users` | A | Cfg | High | No | `usergroup/facilities.php`, `facility_admin.php`; `facility` (1 row, default name, `primary_business_entity=0`) |
| CAP-0230 | Org → Assign users to facilities | Scope a user to one or more facilities and per-facility identifiers | Adm, EL | `admin\|users` | A | Cfg | High | No | `usergroup/facility_user.php`, `facility_user_admin.php`; `users_facility` and `facility_user_ids` both **0 rows** |
| CAP-0231 | UI → Assign a menu role to a user | Give a user a different navigation set without changing permissions | Adm, EL | `admin\|users` | A | Ready | High | No | `src/Menu/MainMenuRole.php:83-110`, `PatientMenuRole.php:97-120`; `users.main_menu_role`, `patient_menu_role` |
| CAP-0232 | Config → Edit screen layouts | Change which fields appear on demographics, history and custom forms | Adm, EL | `admin\|super` | A | Ready | High | No | `super/edit_layout.php`, `edit_layout_props.php`; `layout_options`, `layout_group_properties` |
| CAP-0233 | Config → Edit picklists | Maintain every dropdown list in the product | Adm, EL | `admin\|super` | A | Ready | High | No | `super/edit_list.php`; `list_options` |
| CAP-0234 | Config → Administer clinical forms | Register, enable, disable and order encounter forms | Adm, EL | `admin\|forms` | A | Ready | High | No | `interface/forms_admin/forms_admin.php:38-40` → `library/registry.inc.php:88` `installSQL()` |
| CAP-0235 | Config → Load native code sets | Install the bundled code sets into the code tables | Adm, EL | `admin\|super` | A | Ready | High | No | `super/load_codes.php`; `code_types` (19 rows; 9 active) |
| CAP-0236 | Config → Load external code sets | Download and install ICD-10, SNOMED, RxNorm, CQM value sets | — | `admin\|super` | **RI** | Ext | High | No | `interface/code_systems/dataloads_ajax.php`; requires outbound access to external terminology distributors. INT-0030 |
| CAP-0237 | Config → Translate the interface | Edit and import UI translations per language | Adm, EL | `admin\|language` | A | Ready | High | No | `interface/language/language.php`; `lang_constants` (13,234), `lang_definitions` (237,509) |
| CAP-0238 | Modules → Install and manage modules | Register, install SQL/ACL, enable, configure and unregister modules | Adm, EL | `admin\|manage_modules` | A | Ready | High | No | `zend_modules/module/Installer/`; states documented in §8.2 |
| CAP-0239 | Config → Manage site files | Upload and edit files under the site directory (logos, certificates) | Adm, EL | `admin\|super` | A | Ready | High | No | `super/manage_site_files.php` |
| CAP-0240 | Ops → Back up the database and documents | Produce a full backup of the schema, documents and configuration | Adm, EL | `admin\|super` | A / **Op: BLOCKED** | Cfg | High | No | `backup.php:126` `realpath(mysql_bin_dir)` → path absent ⇒ **false**; `:457-458` builds `\mysql`/`\mysqldump`; **no fallback exists**. Screen loads; the dump cannot execute. CFG-0120. GAP-0064 **CLOSED — confirmed configuration defect** |
| CAP-0241 | Audit → View the activity log | Search and review the audit trail by user, patient, event and date | Adm, EL | `admin\|users` | A | Ready | High | No | `interface/logview/logview.php`; `log` (4,280 rows); RPT-0049 |
| CAP-0242 | Ops → Run system diagnostics | Check the environment and configuration for problems | Adm, EL | `admin\|super` | A | Ready | Med | No | `main/calendar/index.php?...func=testSystem`; `standard.json:1128` |
| CAP-0243 | Ops → Test email delivery | Send a test message to validate SMTP configuration | Adm, EL | `admin\|super` | A | Ready | High | No | `usergroup/email_send_test.php`; `standard.json:1140` |
| CAP-0244 | Ops → Manage background services | View, enable, disable and force-run scheduled services | Adm, EL | `admin\|super` | A | Ready | High | No | `reports/background_services.php`; `src/Services/Background/BackgroundServiceRunner.php`; 5 services, **2 active**; RPT-0050 |
| CAP-0245 | Ops → Administer API/SMART clients | Approve, enable and revoke registered API applications | Adm, EL | `admin\|super` | **D** | Cfg | High | No | Duplicate surface of CAP-0207 from the admin side; inert while APIs are off |

### 6.20 D20 — Multi-site, Localisation & Extensibility

| ID | Feature → Action | Plain-English description | Roles | ACL | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| CAP-0246 | Tenancy → Run multiple sites from one codebase | Serve several independent practices, each with its own database | Adm | — | A | Cfg | High | No | `sites/<id>/sqlconf.php`; `?site=` selector (`index.php:10-24`, path-validated `:20-22`). **Only `default` provisioned.** Not SaaS isolation — see GAP-0043 |
| CAP-0247 | Org → Operate multiple facilities in one database | Run several clinics/locations under one practice with their own identity | Adm, EL | `admin\|users` | A | Cfg | High | No | `facility` table (38+ columns incl. `facility_npi`, `pos_code`, `x12_sender_id`, `oid`); 1 row |
| CAP-0248 | Billing → Scope billing to a facility | Bill under a facility distinct from the service location | Adm, Acc, EL | `admin\|practice` | A | Cfg | High | No | `users.billing_facility_id`; `facility.billing_location`; `Claim.php:739-810` |
| CAP-0249 | Inventory → Scope stock to a warehouse | Restrict inventory by warehouse/facility | — | `inventory\|*` | **D** | Cfg | High | No | `product_warehouse`; CFG-0099 `gbl_fac_warehouse_restrictions=0`; parent domain disabled (CFG-0045) |
| CAP-0250 | Access → Restrict users to their facility | Limit a user's data visibility to their assigned facility | — | `admin\|users` | **D** | Cfg | High | No | CFG-0100 `restrict_user_facility=0`; CFG-0101 `login_into_facility=0`; `users_facility` empty |
| CAP-0251 | i18n → Run the interface in another language | Switch the UI language per user or system-wide | All | — | A | Ready | High | No | `lang_languages` (59 defined, **47 with translations**); CFG-0102 `language_menu_showall=1`; CFG-0103 `language_default=English (Standard)` |
| CAP-0252 | i18n → Arabic user interface | Present the application in Arabic | All | — | A | Cfg | **High** | **Yes** | `lang_definitions` **lang_id 22 = 6,290 rows of 13,234 constants = 47.5 %**. Spot-verified live: `Patient→مريض`, `Calendar→التقويم`, `Billing→الفاتورة`, `Male→ذكر`, `Female→أنثى` |
| CAP-0253 | i18n → Right-to-left layout | Mirror the interface for RTL languages | All | — | A | Cfg | High | No | `lang_languages.lang_is_rtl=1` for Arabic/Hebrew/Persian/Urdu; **13 prebuilt `rtl_*` themes** in `public/themes/`; `library/auth.inc.php:45` sets `language_direction`. Shallow — see §22.3 |
| CAP-0254 | Locale → Configure currency presentation | Set the currency symbol, decimals and separators | Adm, EL | `admin\|super` | A | Cfg | High | No | CFG-0104…0107; live `gbl_currency_symbol='$'`, decimals 2. **Display only — no ISO code, no multi-currency, no currency column on any financial table** |
| CAP-0255 | Locale → Configure date and time format | Choose the date order and 12/24-hour clock | Adm, EL | `admin\|super` | A | Cfg | High | No | CFG-0108 `date_display_format=0`, CFG-0109 `time_display_format=0`, CFG-0110 `gbl_time_zone=''` (**unset**). Only 3 date formats offered |
| CAP-0256 | Locale → Configure units of measure | Switch vitals and clinical units between US and metric | Adm, EL | `admin\|super` | A | Ready | High | No | CFG-0111 `units_of_measurement=1` (US primary + metric); 4 options incl. metric-only |
| CAP-0257 | UI → Define custom menus | Ship a bespoke navigation set as a JSON file per site | Adm, EL | `admin\|users` | A | Ready | High | No | `sites/<site>/documents/custom_menus/*.json`; `MainMenuRole.php:89-106`. One present (`Custom.json`, byte-identical to `standard.json`) |
| CAP-0258 | Extensibility → Add a custom module | Drop in a module that adds screens, menus, tables and settings | Adm, EL | `admin\|manage_modules` | A | Ready | High | No | `src/Core/ModulesApplication.php:39,141,188`; `openemr.bootstrap.php` convention; 8 examples on disk |
| CAP-0259 | Extensibility → Extend an existing screen via events | Inject widgets, columns, buttons and filters without patching core | Developer | — | A | Ready | High | No | ~90 events; e.g. `patientDemographics.render.section.before`, `appointment.set`, `billing.payment.action.post.front.payment` |
| CAP-0260 | Extensibility → Override Twig templates | Replace a shipped template with a site- or module-specific version | Developer | — | A | Ready | High | No | `src/Events/Core/TwigEnvironmentEvent.php:38` (`core.twig.environment.create`) |

### 6.21 D21 — Uninstalled Custom & Optional Modules

None of these has a `modules` row, a `module_configuration` row, or any of its
DB tables. `module_configuration`, `modules_hooks_settings` and
`modules_settings` are all **empty** — no module has ever been configured on
this installation.

| ID | Module | Plain-English description | External dependency | Status | CR | Conf | RT | Evidence |
|---|---|---|---|---|---|---|---|---|
| CAP-0261 | ClaimRev Connect | Clearinghouse connector: claim upload, ERA download, eligibility, denial analytics, AR aging, patient statements | ClaimRev (Claim Revolution, LLC) API via Azure AD B2C OAuth2 | U | Mod+Ext | High | No | `oe-module-claimrev-connect/`; **gitignored** (`.gitignore:15`), composer-installed; 6 `mod_claimrev_*` tables absent. INT-0012 |
| CAP-0262 | Comlink Telehealth | Video visits: conference rooms, waiting room, provider/patient provisioning | Comlink video registration + video API | U | Mod+Ext | High | No | `oe-module-comlink-telehealth/`; `TelehealthGlobalConfig.php`; 3 tables absent; no `comlink*` globals. INT-0017 |
| CAP-0263 | Dashboard Context Manager | Show/hide patient-dashboard widgets by care context (primary care, outpatient, ED, specialty) | none (local) | U | Mod | High | No | `oe-module-dashboard-context/`; 6 tables absent; hooks `MenuEvent::MENU_UPDATE`, `PatientDemographics\RenderEvent` |
| CAP-0264 | DORN | Diagnostic Ordering Result Network: HL7 order generation, result receipt, compendium install, routing | DORN ConnectorApi | U | Mod+Ext | High | No | `oe-module-dorn/`; `src/ConnectorApi.php`; 3 `mod_dorn_*` tables absent. INT-0015 |
| CAP-0265 | Fax / SMS / Voice | Multi-vendor fax, SMS, email and voice dispatch with an appointment-notification runner | Twilio, SignalWire, etherFAX, RingCentral, Clickatell | U | Mod+Ext | High | No | `oe-module-faxsms/`; 2 tables absent; none of its 6 gating globals exist. INT-0023, INT-0024 |
| CAP-0266 | Prior Authorizations | Track authorisation numbers, unit entitlement and remaining units through their life cycle | none — but calls out to a third party at registration | U | Work | High | No | `oe-module-prior-authorizations/`; `module_prior_authorizations` absent. **Defects found**: hard-coded `facility WHERE id = 3`; queries a non-existent `patient_status` table; no ACL on its patient page; `registration()` POSTs clinic name/phone/email to `https://api.affordablecustomehr.com/register.php` |
| CAP-0267 | Weno eRx | Weno Exchange e-prescribing, pharmacy directory sync and log sync | Weno Exchange | U | Mod+Ext | High | No | `oe-module-weno/`; 3 `weno_*` tables absent. INT-0010 |
| CAP-0268 | Patient Filter (reference module) | Upstream sample module demonstrating versioned SQL + ACL install and event hooks | none | U | Mod | High | No | `zend_modules/module/PatientFilter/` (has `sql/install.sql`, 2 upgrade scripts, `acl/`, `version.php`) |
| CAP-0269 | Patient Validation | Additional patient demographic validation rules | none | U | Mod | High | No | `zend_modules/module/Patientvalidation/`; route `/patientvalidation[...]`; not in `modules` |
| CAP-0270 | Prescription Templates | HTML and PDF prescription template editor | none | U | Mod | High | No | `zend_modules/module/PrescriptionTemplates/`; routes `/prescription-html-template`, `/prescription-pdf-template`; not in `modules` |

> **Operational note.** `InstallerController::scanAndRegisterCustomModules()`
> (`:81-137`) auto-registers any unregistered module directory the first time
> an administrator opens the Module Manager. The absence of CAP-0268/0269/0270
> from the `modules` table is therefore positive evidence that **the Module
> Manager has never been opened on this installation**.

---

## 7. Functional Modules / Areas (top-level navigation)

These are core functional areas, **not** installable modules. Source:
`interface/main/tabs/menu/menus/standard.json` — 16 top-level nodes, 183 nodes
at all depths, parsed recursively.

| # | Area | Visible now? | Gate | Capabilities |
|---|---|---|---|---|
| 1 | **Calendar** | Yes | `!disable_calendar`, `!ippf_specific` — both satisfied | CAP-0006…0010 |
| 2 | **Finder** | Yes | ACL `patients\|demo` | CAP-0002, CAP-0003 |
| 3 | **Flow** | Yes | `!disable_pat_trkr`, `!disable_calendar` — satisfied | CAP-0011 |
| 4 | **Recalls** | Yes | `!disable_rcb` — satisfied | CAP-0012 |
| 5 | **Messages** | Yes | ACL `patients\|notes` | CAP-0183, CAP-0184 |
| 6 | **Patient** | Yes | — | CAP-0001, 0004, 0027, 0033, 0034 + dynamic Visit Forms |
| 7 | **Groups** | **No** | `enable_group_therapy = 0` | CAP-0087…0091 |
| 8 | **Fees** (billing) | Yes | `enable_fees_in_left_menu = 1` | CAP-0013, 0014, 0119, 0126, 0135…0137 |
| 9 | **Modules** | Yes | ACL `menus\|modle` | CAP-0238 |
| 10 | **Inventory** | **No** | `inhouse_pharmacy = 0` | CAP-0113…0118 |
| 11 | **Procedures** | Yes | ACL `patients\|lab` / `admin\|super` | CAP-0092…0105 |
| 12 | **Ensora eRx** | **No** | `global_req_strict: ["erx_enable","newcrop_user_role"]` — `erx_enable=0` | CAP-0110…0112 |
| 13 | **Admin** | Yes | ACL union of 7 admin ACOs | CAP-0226…0245 |
| 14 | **Reports** | Yes (partly) | Sub-groups individually gated | CAP-0161…0172 |
| 15 | **Miscellaneous** | Yes (partly) | Items individually gated | CAP-0019, 0028, 0181, 0182, 0205 |
| 16 | **Popups** | Yes | Per-item `global_req` | CAP-0015, 0013, 0014, 0051 |

### 7.1 Sub-areas currently hidden inside visible areas

| Hidden item | Parent | Gate (current value) |
|---|---|---|
| Fees → Charges | Fees | `use_charges_panel = 0` |
| Fees → Claim File Tracker | Fees | `auto_sftp_claims_to_x12_partner = 0` |
| Admin → Export (IPPF) | Admin | `ippf_specific` unset |
| Admin → Other → eRx Logs | Admin | `erx_enable = 0` |
| Reports → Inventory (3 reports) | Reports | `inhouse_pharmacy = 0` |
| Reports → Statistics (5 IPPF reports) | Reports | `ippf_specific` unset |
| Reports → Visits → Pending F/U | Reports | `ippf_specific` unset |
| Reports → Clients → Rx | Reports | `!disable_prescriptions` — **visible** (flag is 0) |
| Miscellaneous → Portal Dashboard | Misc | `portal_onsite_two_enable = 0` |
| Miscellaneous → Fax/Scan | Misc | `enable_hylafax = 0` **and** `enable_scanner = 0` |
| Miscellaneous → Configure Tracks | Misc | `track_anything_state` (reads `registry.state`; form unregistered) |
| Popups → Checkout | Popups | `inhouse_pharmacy = 0` |
| Popups → Export / Import | Popups | `!ippf_specific` — **visible** |

### 7.2 How menu visibility is actually decided

Hiding is entirely server-side, in `MenuRole::menuApplyRestrictions()`
(`src/Menu/MenuRole.php:69-197`). An entry is dropped when any of:

| Mechanism | Semantics | Evidence |
|---|---|---|
| `acl_req` flat array | Hidden unless `AclMain::aclCheckCore()` passes; leading `!` inverts | `MenuRole.php:76-93`, `:202-229` |
| `acl_req` array-of-arrays | Loose OR — hidden only if none pass | `MenuRole.php:77-87` |
| `global_req` scalar | Hidden if the global is falsy; `!name` inverts | `MenuRole.php:121-133` |
| `global_req` array | **Loose OR** | `MenuRole.php:98-119` |
| `global_req_strict` array | **Strict AND** | `MenuRole.php:140-161` |
| Header pruning | A url-less node with no surviving children is removed | `MenuRole.php:186-191` |

The separate `requirement` integer (0–5) is a **client-side enable/disable**
flag, not a visibility flag: `0` always enabled, `1` needs a patient, `2`/`3`
need a patient + encounter (`2` additionally runs an encounter-lock check),
`4`/`5` need a therapy group (+ encounter). Evidence:
`templates/interface/main/tabs/menu_json.html.twig:53-85`;
`tabs/js/tabs_view_model.js:327-335,371-381`.

---

## 8. Pluggable Modules

### 8.1 Two frameworks, one registry

| Framework | `modules.type` | Location | Bootstrap |
|---|---|---|---|
| Laminas (ex-Zend) MVC | `1` | `interface/modules/zend_modules/module/<Name>/` | Laminas ModuleManager, list injected from DB (`ModulesApplication.php:119-130`) |
| Custom / Symfony-event | `0` | `interface/modules/custom_modules/<dir>/` | `include openemr.bootstrap.php` + shared EventDispatcher (`ModulesApplication.php:39,141,188`) |

Safety behaviours worth noting for a security conversation:
`checkModuleScriptPathForEnabledModule()` (`:86-106`) throws
`AccessDeniedException` if a script under a **disabled** module directory is
hit directly; `isSafeModuleFileForInclude()` (`:219-293`) contains path
traversal; and a module whose bootstrap goes missing self-disables after three
retries (`:150-156`).

### 8.2 The distinct module states (all six are separate)

| State | DB representation |
|---|---|
| Unregistered | no row in `modules` |
| Registered, not installed | row present, `mod_active=0`, `sql_run=0` |
| SQL install/upgrade pending | `sql_run=0` with `sql/install.sql`, or a newer `*_upgrade.sql` than `sql_version` |
| ACL install/upgrade pending | `acl/acl_setup.php` present with empty `acl_version` |
| Installed + enabled | `mod_active=1` |
| Installed + disabled | `mod_active=0`, `sql_run=1` |

Derived from `InstallerController.php:591-655` and
`view/installer/installer/index.phtml:176-249`.

### 8.3 Registered and active — the complete list (5)

| mod_id | Module | Directory | type | active | sql_run | Registered | Capability |
|---|---|---|---|---|---|---|---|
| 1 | Immunization | `Immunization` | 1 | **1** | 1 | 2026-08-07 05:26:08 | CAP-0203 |
| 2 | Syndromicsurveillance | `Syndromicsurveillance` | 1 | **1** | 1 | 2026-08-07 05:26:08 | CAP-0204 |
| 3 | Documents | `Documents` | 1 | **1** | 1 | 2026-08-07 05:26:08 | CAP-0022 |
| 4 | Ccr | `Ccr` | 1 | **1** | 1 | 2026-08-07 05:26:08 | CAP-0202 |
| 5 | Carecoordination | `Carecoordination` | 1 | **1** | 1 | 2026-08-07 05:26:08 | CAP-0201 |

All five were installed by the base installer, not by an administrator.
`module_acl_sections` holds the matching 5 rows;
`module_acl_group_settings` holds exactly one row — `(module 5, group 11,
section 5, allowed 1)`, i.e. Carecoordination granted to Administrators.

### 8.4 Laminas framework modules — not Module-Manager entries (6)

Excluded from the Module Manager list by `InstallerController.php:74,86` and
always loaded via `application.config.php:14-25`.

| Directory | Role |
|---|---|
| `Acl` | Module-permission engine used by the Module Manager |
| `Application` | Base Laminas app, layout, translation listener, phiMail plugin, `sendto`/`soap` controllers |
| `CodeTypes` | Code-type mapping registration service |
| `FHIR` | FHIR UUID population and FHIR utility services |
| `Installer` | **The Module Manager itself** |
| `PatientFlowBoard` | Patient flow board support services |

### 8.5 Installable but not registered (17)

3 Laminas + 14 custom/optional. All are catalogued as capabilities:
CAP-0261…0270 (10 modules) plus CAP-0206 (EHI exporter), and the 6 dormant
**forms** are separately at CAP-0071…0086 (§6.6).

| Directory | Framework | Capability |
|---|---|---|
| `PatientFilter` | Laminas | CAP-0268 |
| `Patientvalidation` | Laminas | CAP-0269 |
| `PrescriptionTemplates` | Laminas | CAP-0270 |
| `oe-module-claimrev-connect` | custom | CAP-0261 |
| `oe-module-comlink-telehealth` | custom | CAP-0262 |
| `oe-module-dashboard-context` | custom | CAP-0263 |
| `oe-module-dorn` | custom | CAP-0264 |
| `oe-module-ehi-exporter` | custom | CAP-0206 |
| `oe-module-faxsms` | custom | CAP-0265 |
| `oe-module-prior-authorizations` | custom | CAP-0266 |
| `oe-module-weno` | custom | CAP-0267 |

Disk-vs-DB reconciliation: 14 Laminas directories = 5 registered + 6 framework
+ 3 unregistered. 8 custom directories = 0 registered + 8 unregistered.
**22 module directories, 5 installed, no unexplained mismatch.**

### 8.6 Legacy module tables still carrying data

| Table | Rows | Content |
|---|---|---|
| `openemr_modules` | 1 | PostNuke-era `PostCalendar` registration |
| `openemr_module_vars` | 19 | PostCalendar preferences (`pcTime24Hours`, `pcFirstDayOfWeek`, `pcDefaultView=day`, …) |

These are dead PostNuke-era artefacts on the module path — technical debt, not
a capability.

---

## 9. Clinical Forms

35 directories exist under `interface/forms/`. 18 hold a `registry` row, all
with `state = 1` (verified: `SELECT state,COUNT(*) FROM registry GROUP BY
state` → `1 | 18`; there are **no disabled forms**). 17 directories have no
registry row — 16 of them are dormant forms (CAP-0071…0086) and one (`LBF`) is
the Layout-Based Form **engine**, not a form.

### 9.1 Registered and active (18)

| ID | Form | Directory | Category | `aco_spec` | Patient enc. | Backing tables | Capability |
|---|---|---|---|---|---|---|---|
| FORM-0001 | New Encounter Form | `newpatient` | Administrative | `patients\|appt` | Yes | `form_encounter`, `forms` | CAP-0033 |
| FORM-0002 | Fee Sheet | `fee_sheet` | Administrative | `encounters\|coding` | Yes | `billing`, `drug_sales` | CAP-0119 |
| FORM-0003 | Misc Billing Options HCFA | `misc_billing_options` | Administrative | `encounters\|coding` | Yes | `form_misc_billing_options` | CAP-0125, CAP-0150 |
| FORM-0004 | SOAP | `soap` | Clinical | `encounters\|notes` | Yes | `form_soap` | CAP-0035 |
| FORM-0005 | Vitals | `vitals` | Clinical | `encounters\|notes` | Yes | `form_vitals`, `form_vital_details`, `form_vitals_calculation*` | CAP-0036 |
| FORM-0006 | Review Of Systems | `ros` | Clinical | `encounters\|notes` | Yes | `form_ros` | CAP-0037 |
| FORM-0007 | Review of Systems Checks | `reviewofs` | Clinical | `encounters\|notes` | Yes | `form_reviewofs` | CAP-0038 |
| FORM-0008 | Clinical Notes | `clinical_notes` | Clinical | `encounters\|notes` | Yes | `form_clinical_notes` + 2 link tables | CAP-0039 |
| FORM-0009 | Clinical Instructions | `clinical_instructions` | Clinical | `encounters\|notes` | Yes | `form_clinical_instructions` | CAP-0040 |
| FORM-0010 | Care Plan | `care_plan` | Clinical | `encounters\|notes` | Yes | `form_care_plan` | CAP-0041 |
| FORM-0011 | Functional and Cognitive Status | `functional_cognitive_status` | Clinical | `encounters\|notes` | Yes | `form_functional_cognitive_status` | CAP-0042 |
| FORM-0012 | Observation | `observation` | Clinical | `encounters\|notes` | Yes | `form_observation` | CAP-0043 |
| FORM-0013 | Speech Dictation | `dictation` | Clinical | `encounters\|notes` | Yes | `form_dictation` | CAP-0044 |
| FORM-0014 | Eye Exam | `eye_mag` | Clinical | `encounters\|notes` | Yes | `form_eye_base` + 17 | CAP-0045 |
| FORM-0015 | Group Attendance Form | `group_attendance` | Clinical | `encounters\|notes` | **No** (group) | `form_group_attendance` | CAP-0090 |
| FORM-0016 | New Group Encounter Form | `newGroupEncounter` | Clinical | `patients\|appt` | **No** (group) | `form_groups_encounter` | CAP-0089 |
| FORM-0017 | New Questionnaire | `questionnaire_assessments` | Questionnaires | `admin\|forms` | Yes | `form_questionnaire_assessments`, `questionnaire_repository`, `questionnaire_response` | CAP-0046 |
| FORM-0018 | Procedure Order | `procedure_order` | Orders | `patients\|lab` | Yes | `procedure_order`, `procedure_order_code`, `procedure_answers` | CAP-0092 |

### 9.2 On disk but unregistered (17)

| ID | Directory | Declared name | Would create | Capability |
|---|---|---|---|---|
| FORM-0019 | `phq9` | PHQ-9 | `form_phq9` | CAP-0071 |
| FORM-0020 | `gad7` | GAD-7 | `form_gad7` | CAP-0072 |
| FORM-0021 | `physical_exam` | Physical Exam | `form_physical_exam`, `form_physical_exam_diagnoses` | CAP-0073 |
| FORM-0022 | `treatment_plan` | Treatment Plan | `form_treatment_plan` | CAP-0074 |
| FORM-0023 | `transfer_summary` | Transfer Summary | `form_transfer_summary` | CAP-0075 |
| FORM-0024 | `aftercare_plan` | Aftercare Plan | `form_aftercare_plan` | CAP-0076 |
| FORM-0025 | `painmap` | Graphic Pain Map | `form_painmap` | CAP-0077 |
| FORM-0026 | `CAMOS` | CAMOS | `form_CAMOS` + 3 | CAP-0078 |
| FORM-0027 | `clinic_note` | Clinic Note | `form_clinic_note` | CAP-0079 |
| FORM-0028 | `note` | Work/School Note | `form_note` | CAP-0080 |
| FORM-0029 | `bronchitis` | Bronchitis Form | `form_bronchitis` | CAP-0081 |
| FORM-0030 | `ankleinjury` | Ankle Evaluation Form | `form_ankleinjury` | CAP-0082 |
| FORM-0031 | `sdoh` | Social Screening Tool | `form_sdoh` | CAP-0083 |
| FORM-0032 | `requisition` | Lab Requisition | `requisition` | CAP-0084 |
| FORM-0033 | `prior_auth` | Prior Authorization | `form_prior_auth` | CAP-0085 |
| FORM-0034 | `track_anything` | Track anything | `form_track_anything` + 2 | CAP-0086 |
| FORM-0035 | `LBF` | *(no `info.txt`)* | — | **Engine, not a form** — CAP-0047 |

Verification: `SHOW TABLES LIKE 'form_%'` returns only tables belonging to
registered forms. None of the 16 dormant forms' tables exist.

---

## 10. Screens and Routes

**128 meaningful user-facing screens** are catalogued. The **55 report screens
are catalogued separately as `RPT-*` in §16** and are deliberately not repeated
here, to avoid inflating counts.

Reachability legend: `Menu` linked from a menu JSON · `Modal` opened as a
dialog from another screen · `Frame` a frameset/iframe child · `POST` a form
target · `Nav` reached from the shell chrome (user dropdown, patient panel) ·
`Direct` web-servable but not linked from anywhere in-app.

**Caution used throughout:** an HTTP 200 from a screen proves the page renders;
it does not prove the underlying capability is operational. Only CAP-0209 and
CAP-0224 were runtime-proven end-to-end.

### 10.1 Shell, authentication and session (8)

| ID | Screen | Route | Reach | ACL / gate | Status |
|---|---|---|---|---|---|
| SCR-0001 | Site dispatcher | `/index.php` | Direct | site id validated `index.php:20-22` | A |
| SCR-0002 | Login | `/interface/login/login.php?site=default` | Direct | none (public) | **A — HTTP 200 verified** |
| SCR-0003 | Login post-processing / MFA challenge | `/interface/main/main_screen.php` | POST | credentials | A |
| SCR-0004 | Application shell (tabbed SPA) | `/interface/main/tabs/main.php?token_main=<tok>` | POST redirect | one-shot token (`main.php:78-92`) | A |
| SCR-0005 | Logout | `/interface/logout.php` | Nav | session | A |
| SCR-0006 | Own password change | `/interface/usergroup/user_info.php` | Nav (user menu) | self-scoped, CSRF | A |
| SCR-0007 | Own MFA registrations | `/interface/usergroup/mfa_registrations.php` | Nav (user menu) | self-scoped, CSRF | A |
| SCR-0008 | Password-expiry alert | `/interface/main/pwd_expires_alert.php` | Nav (auto-prepended tab) | session | A |

### 10.2 Front office and patient access (18)

| ID | Screen | Route | Reach | ACL / gate | Status |
|---|---|---|---|---|---|
| SCR-0009 | Calendar | `/interface/main/main_info.php` | Menu | `patients\|appt`; `!disable_calendar` | A |
| SCR-0010 | Add/edit appointment | `/interface/main/calendar/add_edit_event.php` | Modal | `patients\|appt` | A |
| SCR-0011 | Find-patient popup | `/interface/main/calendar/find_patient_popup.php` | Modal (7 call sites) | `patients\|demo` | A |
| SCR-0012 | Find-group popup | `/interface/main/calendar/find_group_popup.php` | Modal | `groups\|gadd` | D |
| SCR-0013 | Calendar configuration | `/interface/main/calendar/index.php?...func=modifyconfig` | Menu | `admin\|calendar` | A |
| SCR-0014 | Import holidays | `/interface/main/holidays/import_holidays.php` | Menu | `admin\|super` | A |
| SCR-0015 | Dynamic patient finder | `/interface/main/finder/dynamic_finder.php` | Menu | `patients\|demo` | A |
| SCR-0016 | Document select finder | `/interface/main/finder/document_select.php` | Modal | `patients\|docs` | A |
| SCR-0017 | New patient (short) | `/interface/new/new.php` | Menu | `patients\|demo` w/addonly | A |
| SCR-0018 | New patient (comprehensive) | `/interface/new/new_comprehensive.php` | Frame | `patients\|demo` w/addonly | A |
| SCR-0019 | Patient flow board | `/interface/patient_tracker/patient_tracker.php` | Menu | `patients\|appt` | A |
| SCR-0020 | Flow board status update | `/interface/patient_tracker/patient_tracker_status.php` | Modal | `patients\|appt` | A |
| SCR-0021 | Messages / recalls | `/interface/main/messages/messages.php` | Menu | `patients\|notes` | A |
| SCR-0022 | Dated reminders | `/interface/main/dated_reminders/dated_reminders.php` | Frame | `patients\|notes` | A |
| SCR-0023 | Add dated reminder | `/interface/main/dated_reminders/dated_reminders_add.php` | Modal | `patients\|notes` | A |
| SCR-0024 | Office notes | `/interface/main/onotes/office_comments.php` | Menu | `encounters\|notes` | A |
| SCR-0025 | Chart tracker | `/custom/chart_tracker.php` | Menu | `patients\|appt`; `!disable_chart_tracker` | A |
| SCR-0026 | Batch communication tool | `/interface/batchcom/batchcom.php` | Menu | `admin\|batchcom`/`practice` | A |

### 10.3 Patient chart (28)

| ID | Screen | Route (`interface/patient_file/…`) | Reach | ACL / gate | Status |
|---|---|---|---|---|---|
| SCR-0027 | Patient dashboard | `summary/demographics.php` | Menu | `patients\|demo` | A |
| SCR-0028 | Demographics edit | `summary/demographics_full.php` | Frame | `patients\|demo` | A |
| SCR-0029 | Demographics save | `summary/demographics_save.php` | POST | `patients\|demo` write | A |
| SCR-0030 | Demographics print | `summary/demographics_print.php` | Modal | — | A |
| SCR-0031 | Issues / problem list (full) | `summary/stats_full.php?active=all` | Nav (patient menu) | `patients\|med` | A |
| SCR-0032 | Add/edit issue | `summary/add_edit_issue.php` | Modal (5 call sites) | `patients\|med` | A |
| SCR-0033 | Issue ↔ encounter link | `problem_encounter.php` | Menu (Popups) | `patients\|med` | A |
| SCR-0034 | Immunisations | `summary/immunizations.php` | Frame | `patients\|med` | A |
| SCR-0035 | Shot record (print) | `summary/shot_record.php` | Frame | `patients\|med` | A |
| SCR-0036 | Lab data / result graphs | `summary/labdata.php` | Menu | `patients\|lab` | A |
| SCR-0037 | Patient notes (full) | `summary/pnotes_full.php` | Frame (8 call sites) | `patients\|notes` | A |
| SCR-0038 | Amendments list / add / print | `summary/list_amendments.php`, `add_edit_amendments.php`, `print_amendments.php` | Modal | `patients\|amendment` | A |
| SCR-0039 | Disclosures | `summary/disclosure_full.php`, `record_disclosure.php` | Frame/Modal | `patients\|disclosure` | A |
| SCR-0040 | Advance directives | `summary/advancedirectives.php` | Modal | `patients\|med` | A |
| SCR-0041 | Insurance edit | `summary/insurance_edit.php` | Modal | `patients\|demo` | A |
| SCR-0042 | Create portal login | `summary/create_portallogin.php` | Modal | `patients\|demo` | **D** |
| SCR-0043 | History (social/family) | `history/history.php`, `history_full.php` | Nav | `patients\|med` | A |
| SCR-0044 | SDOH assessment widget/list/save | `history/history_sdoh_widget.php`, `_list.php`, `_save.php` | Nav | `patients\|med` | A |
| SCR-0045 | Encounter history | `history/encounters.php` | Menu | `patients\|appt` | A |
| SCR-0046 | Transactions / referrals | `transaction/transactions.php`, `add_transaction.php` | Nav | `patients\|trans` | A |
| SCR-0047 | Print referral | `transaction/print_referral.php` | Menu (Popups) | — | A |
| SCR-0048 | Patient record request | `transaction/record_request.php` | Menu | `patients\|med` | A |
| SCR-0049 | Full patient report | `report/patient_report.php`, `custom_report.php` | Nav | `patients\|pat_rep` | A |
| SCR-0050 | Patient letter | `letter.php` | Menu (Popups) | `patients\|med` | A |
| SCR-0051 | Labels (chart/barcode/address/appt) | `label.php`, `barcode_label.php`, `addr_label.php`, `addr_appt_label.php` | Menu (Popups) | `patients\|demo` | A |
| SCR-0052 | Clinical reminders / active alert popup | `reminder/clinical_reminders.php`, `reminder/active_reminder_popup.php` | Modal | `patients\|alert` | A |
| SCR-0053 | Patient reminders (admin mode) | `reminder/patient_reminders.php` | Menu | `patients\|reminder` / `admin\|super` | A |
| SCR-0054 | Universal delete confirm | `deleter.php` | Modal (13 call sites) | context ACL | A |

### 10.4 Encounter and clinical forms (12)

| ID | Screen | Route | Reach | ACL / gate | Status |
|---|---|---|---|---|---|
| SCR-0055 | Encounter shell (top) | `patient_file/encounter/encounter_top.php` | Menu | `patients\|appt` | A |
| SCR-0056 | Encounter shell (bottom/charges) | `patient_file/encounter/encounter_bottom.php` | Menu | `acct\|bill` write; `use_charges_panel=0` | **D** |
| SCR-0057 | Encounter forms list | `patient_file/encounter/forms.php` | Frame | per-form `aco_spec` | A |
| SCR-0058 | Load / view / delete a form | `encounter/load_form.php`, `view_form.php`, `delete_form.php` | Frame | per-form `aco_spec` | A |
| SCR-0059 | New encounter | `interface/forms/newpatient/new.php` | Menu | `patients\|appt` | A |
| SCR-0060 | New group encounter | `interface/forms/newGroupEncounter/new.php` | Menu | `groups\|gcalendar` | **D** |
| SCR-0061 | Fee sheet | `encounter/load_form.php?formname=fee_sheet` | Menu | `encounters\|coding` write | A |
| SCR-0062 | Coding / diagnosis frames | `encounter/coding.php`, `diagnosis.php`, `diagnosis_full.php` | Frame | `encounters\|coding` | A |
| SCR-0063 | Code search (dynamic / popup / history) | `encounter/find_code_dynamic.php`, `find_code_popup.php`, `find_code_history.php`, `select_codes.php` | Modal (25+ call sites) | `encounters\|coding` | A |
| SCR-0064 | Superbill (custom, full) | `encounter/superbill_custom_full.php` | Menu | `admin\|superbill` | A |
| SCR-0065 | Printed fee sheet / superbill | `patient_file/printed_fee_sheet.php` | Menu (Popups) | `patients\|med` | A |
| SCR-0066 | E-sign viewer / signature log | `interface/esign/index.php`; `library/ESign/views/esign_signature_log.php` | Frame | form `aco_spec` | A |

### 10.5 Orders and results (11)

| ID | Screen | Route (`interface/orders/…`) | Reach | ACL / gate | Status |
|---|---|---|---|---|---|
| SCR-0067 | Procedure provider list | `procedure_provider_list.php` | Menu | `admin\|super` | A |
| SCR-0068 | Procedure provider edit | `procedure_provider_edit.php` | Modal | `admin\|super` | A |
| SCR-0069 | Procedure type configuration | `types.php`, `types_edit.php`, `types_ajax.php` | Menu/Modal | `admin\|super` | A |
| SCR-0070 | Load compendium | `load_compendium.php` | Menu | `admin\|super` | A |
| SCR-0071 | Order entry form | `interface/forms/procedure_order/common.php` | Frame | `patients\|lab` | A |
| SCR-0072 | Find order popup | `find_order_popup.php` | Modal | `patients\|lab` | A |
| SCR-0073 | Order manifest | `order_manifest.php` | Frame | `patients\|lab` | A |
| SCR-0074 | Patient results / pending review / batch | `orders_results.php` (+`?review=1`, `?batch=1`) | Menu | `patients\|lab` | A |
| SCR-0075 | Single order results | `single_order_results.php` | Frame | `patients\|lab` | A |
| SCR-0076 | Electronic reports inbox | `list_reports.php` | Menu | `patients\|lab` | A |
| SCR-0077 | Patient match dialog | `patient_match_dialog.php` | Modal | `patients\|lab` | A |

### 10.6 Revenue cycle (21)

| ID | Screen | Route (`interface/billing/…` unless shown) | Reach | ACL / gate | Status |
|---|---|---|---|---|---|
| SCR-0078 | Billing Manager | `billing_report.php` | Menu | `acct\|eob`/`bill` write | A |
| SCR-0079 | Billing process endpoint | `billing_process.php` | POST | `acct\|bill` write | A |
| SCR-0080 | Claim file download | `get_claim_file.php` | Frame | `acct\|bill` write | A |
| SCR-0081 | Claim file tracker | `billing_tracker.php` | Menu | `auto_sftp_claims_to_x12_partner=0` | **D** |
| SCR-0082 | Billing log clear / customise | `clear_log.php`, `customize_log.php` | Modal | `acct\|bill` write | A |
| SCR-0083 | UB-04 form / submit / dispose | `ub04_form.php`, `ub04_submit.php`, `ub04_dispose.php` | Modal | `ub04_support=0` | **D** |
| SCR-0084 | EOB search (posting, page 1) | `sl_eob_search.php` | Menu | `acct\|eob` | A |
| SCR-0085 | EOB invoice (posting, page 2) | `sl_eob_invoice.php` | Modal | `acct\|eob` | A |
| SCR-0086 | Automated 835 posting | `sl_eob_process.php` | Frame | `acct\|eob` | A |
| SCR-0087 | ERA payments | `era_payments.php` | Frame | `acct\|bill`/`eob` write | A |
| SCR-0088 | Patient billing note | `sl_eob_patient_note.php` | Modal | `acct\|eob` | A |
| SCR-0089 | Batch payment entry | `new_payment.php` | Menu | `acct\|eob`/`bill` write; `enable_batch_payment=1` | A |
| SCR-0090 | Edit payment | `edit_payment.php` | Frame | `acct\|eob` | A |
| SCR-0091 | Search payments | `search_payments.php` | Frame | `acct\|eob` | A |
| SCR-0092 | EDI History viewer | `edih_view.php`, `edih_main.php` | Menu | `acct\|eob` | A |
| SCR-0093 | Eligibility 270 batch | `edi_270.php` | Menu | `patients\|demo` (no in-file ACL) | **RI** |
| SCR-0094 | Eligibility 271 upload | `edi_271.php` | Menu | `patients\|demo` (no in-file ACL) | **RI** |
| SCR-0095 | Front payment | `patient_file/front_payment.php` | Menu | `acct\|bill` write | A |
| SCR-0096 | Front payment — card / terminal | `patient_file/front_payment_cc.php`, `front_payment_terminal.php` | Modal | `acct\|bill` write | **RI** |
| SCR-0097 | Checkout (normal / IPPF) | `patient_file/pos_checkout.php` → `_normal.php` / `_ippf.php` | Menu | `acct\|bill` write | A |
| SCR-0098 | Void dialog | `patient_file/void_dialog.php` | Modal | `acct\|bill` write | A |

### 10.7 Administration (22)

| ID | Screen | Route | Reach | ACL / gate | Status |
|---|---|---|---|---|---|
| SCR-0099 | Global settings editor | `interface/super/edit_globals.php` | Menu | `admin\|super` | A |
| SCR-0100 | User preferences (user-mode globals) | `interface/super/edit_globals.php?mode=user` | Nav | session user | A |
| SCR-0101 | Layout editor | `interface/super/edit_layout.php` | Menu | `admin\|super` | A |
| SCR-0102 | Layout properties | `interface/super/edit_layout_props.php` | Modal | `admin\|super` | A |
| SCR-0103 | List editor | `interface/super/edit_list.php` | Menu | `admin\|super` | A |
| SCR-0104 | Native code loader | `interface/super/load_codes.php` | Menu | `admin\|super` | A |
| SCR-0105 | External code loader | `interface/code_systems/dataloads_ajax.php` | Menu | `admin\|super` | **RI** |
| SCR-0106 | Document template manager | `interface/super/manage_document_templates.php` | Menu | `admin\|practice` | A |
| SCR-0107 | Site file manager | `interface/super/manage_site_files.php` | Menu | `admin\|super` | A |
| SCR-0108 | Clinical rules admin | `interface/super/rules/index.php` | Menu | delegated (`AccessDeniedHelper`) | A |
| SCR-0109 | Clinical alerts manager | `interface/super/rules/index.php?action=alerts!listactmgr` | Menu | delegated | A |
| SCR-0110 | User list | `interface/usergroup/usergroup_admin.php` | Menu | `admin\|users` | A |
| SCR-0111 | Add user | `interface/usergroup/usergroup_admin_add.php` | Frame | `admin\|users` | A |
| SCR-0112 | Edit user | `interface/usergroup/user_admin.php` | Frame | `admin\|users` | A |
| SCR-0113 | ACL administration | `interface/usergroup/adminacl.php` | Menu | `admin\|acl` | A |
| SCR-0114 | Facilities list / add / edit | `usergroup/facilities.php`, `facilities_add.php`, `facility_admin.php` | Menu/Modal | `admin\|users` | A |
| SCR-0115 | Facility ↔ user assignment | `usergroup/facility_user.php`, `facility_user_admin.php` | Frame | `admin\|users` | A |
| SCR-0116 | Address book list / edit / NPI lookup | `usergroup/addrbook_list.php`, `addrbook_edit.php`, `npi_lookup.php` | Menu/Modal | `admin\|practice` | A |
| SCR-0117 | Practice settings (legacy MVC) | `/controller.php?practice_settings&...&action=list` | Menu | `admin\|practice` | A |
| SCR-0118 | X12 partner list / edit | `/controller.php?practice_settings&x12_partner&action=list` | Direct/Menu | `admin\|practice` (see GAP-0062) | A |
| SCR-0119 | Module Manager | `/interface/modules/zend_modules/public/Installer` | Menu | `admin\|manage_modules` | A |
| SCR-0120 | Backup / language / logs / diagnostics / email test / API clients | `main/backup.php`, `language/language.php`, `logview/logview.php`, `...testSystem`, `usergroup/email_send_test.php`, `smart/admin-client.php` | Menu | `admin\|super`/`users`/`language` | A (API clients **D**) |

### 10.8 Patient portal (8) — all Disabled

| ID | Screen | Route (`portal/…`) | Reach | Gate | Status |
|---|---|---|---|---|---|
| SCR-0121 | Portal login | `index.php` | Direct | `portal_onsite_two_enable=0` | **D** |
| SCR-0122 | Portal home / dashboard | `home.php` | Direct | same | **D** |
| SCR-0123 | Portal self-registration | `account/register.php`, `verify.php` | Direct | same + reCAPTCHA keys empty | **D** |
| SCR-0124 | Portal password reset | `account/index_reset.php` | Direct | `portal_two_pass_reset=0` | **D** |
| SCR-0125 | Portal messaging | `messaging/messages.php` | Direct | same | **D** |
| SCR-0126 | Portal documents / templates | `get_patient_documents.php`, `import_template_ui.php` | Direct | same | **D** |
| SCR-0127 | Portal payment | `portal_payment.php` | Direct | `portal_two_payments=0` + gateway | **D** |
| SCR-0128 | Portal document signing | `sign/` | Direct | same | **D** |

### 10.9 Modal architecture

All menu-driven popups route through `popMenuDialog(url, title)` →
`dlgopen(url,'menupopup','modal-mlg',…)` at
`tabs/js/tabs_view_model.js:307-312`, invoked whenever a menu item has
`target === 'pop'` (`:320-323`). **176 `dlgopen`/`popMenuDialog` call sites**
exist under `interface/` (excluding `interface/modules/`). The heaviest reused
modals are `find_code_popup.php` (15 call sites), `deleter.php` (13),
`find_code_dynamic.php` (10) and `find_patient_popup.php` (7).

A behavioural quirk worth knowing before a demo: menu items with `target:'pop'`
declare `requirement: 1` (patient required) but `menuActionClick()`
short-circuits to `popMenuDialog()` **before** the enable check
(`tabs_view_model.js:320-323`), so they render greyed yet still open with no
patient loaded.

---

## 11. Workflows

Every workflow below is derived from the implementation — the state values,
status transitions and screen sequence were read out of the code and the
`list_options` tables, not assumed from general healthcare practice. Where the
implementation differs from the textbook flow, the implementation is what is
documented.

### WF-0001 — Outpatient patient journey · **Active (data-blocked)**

**Objective:** take a patient from arrival to a completed, charged visit.
**Actors:** Front Office, Physician/Clinician. **Trigger:** appointment or
walk-in. **Prerequisite:** a patient record and an active provider.

| # | Step | Screen | Capability | State change |
|---|---|---|---|---|
| 1 | Register or find the patient | SCR-0017 / SCR-0015 | CAP-0001 / CAP-0002 | `patient_data` row created |
| 2 | Book the appointment | SCR-0010 | CAP-0006 | `openemr_postcalendar_events` row |
| 3 | Check in — set arrival status | SCR-0019/0020 | CAP-0011 | `patient_tracker` status advances |
| 4 | Create the visit | SCR-0059 | CAP-0033 | `form_encounter` + `forms` rows |
| 5 | Document (vitals, SOAP, ROS…) | SCR-0057/0058 | CAP-0035…0045 | `form_*` rows |
| 6 | Update problems / meds / allergies | SCR-0032 | CAP-0051…0053 | `lists` rows |
| 7 | Order tests or prescribe | SCR-0071 / CAP-0106 | CAP-0092 / CAP-0106 | `procedure_order` / `prescriptions` |
| 8 | Complete the fee sheet | SCR-0061 | CAP-0119 | `billing` rows |
| 9 | Sign the encounter | SCR-0066 | CAP-0048/0049 | `esign_signatures`; encounter locked |
| 10 | Check out and take payment | SCR-0097/0095 | CAP-0014/0013 | `ar_session`, `ar_activity`, `payments` |

**Gaps:** every step is implemented and enabled; none is executable today
because there are no patients, providers or fee schedules (§28).

### WF-0002 — Revenue cycle to claim submission · **Requires Integration**

| # | Step | Screen | Capability | State |
|---|---|---|---|---|
| 1 | Charges captured on the fee sheet | SCR-0061 | CAP-0119 | `billing.activity=1` |
| 2 | Claim-level HCFA data recorded | FORM-0003 | CAP-0125 | `form_misc_billing_options` |
| 3 | Claim selected in Billing Manager | SCR-0078 | CAP-0126 | — |
| 4 | Validate-only pass | SCR-0079 | CAP-0127 | errors reported, nothing written |
| 5 | Generate 837P batch | SCR-0079 | CAP-0128 | `billing.bill_process`, `claims` row, batch file in `sites/default/edi` |
| 6 | Deliver to the clearinghouse | — | CAP-0133 | **BLOCKED** — `x12_partners` empty, `auto_sftp…=0`, `X12_SFTP` inactive |
| 7 | Receive 999/277 acknowledgement | SCR-0092 | CAP-0152 | view only |
| 8 | Receive and post the 835 | SCR-0086/0087 | CAP-0135 | `ar_session`, `ar_activity` |
| 9 | Roll balance to the secondary payer | — | CAP-0146 | `arSetupSecondary()`; `last_level_billed` |
| 10 | Age and collect | SCR/RPT-0027 | CAP-0140 | — |

**Break point:** step 6. Everything upstream works offline (a batch file can be
generated and downloaded manually); nothing downstream can occur without a
trading-partner record and a clearinghouse relationship. Evidence:
`x12_partners` 0 rows; `sites/default/documents/edi/` contains only
`README.txt`; `sites/default/documents/edi/history/` does not exist.

### WF-0003 — Lab order to result in chart · **Requires Integration**

Order (`order_status` empty) → `pending` → HL7 ORM generated →
transmitted (`date_transmitted` set) → `routed` → lab returns ORU →
`procedure_report.review_status = received` → clinician reviews →
`reviewed` and `order_status = complete`.

State vocabularies are real and enumerated in `list_options`: `ord_status`
(`pending` 10, `routed` 20, `complete` 30, `canceled` 40), `proc_rep_status`
(`final`, `review`, `prelim`, `cancel`, `error`, `correct`), `proc_res_status`
(+ `incomplete`), `ord_priority`, `proc_specimen`, `proc_body_site`,
`proc_lat`, `proc_route`, `proc_unit`, `proc_res_abnormal`.
**Break point:** transmission and receipt (CAP-0098/0099) require a configured
`procedure_providers` row with a `DL` or `SFTP` protocol. None exists.

### WF-0004 — Prescription · **Requires Integration beyond print**

Record prescription (CAP-0106) → **either** print/fax locally (CAP-0107,
Active) **or** hand off to the external e-prescribing network (CAP-0110,
blocked by `erx_enable=0`). Renewal (CAP-0111) and EPCS (CAP-0112) exist only
on the vendor path. There is no in-product interaction or formulary check — the
`drug_drug` and formulary flags rendered in `templates/prescription/` are
values *returned by* the vendor.

### WF-0005 — Prior authorisation · **Partially implemented**

Implemented: record the authorisation number on the claim
(`form_misc_billing_options.prior_auth_number`, CAP-0150), and view an inbound
X12 278 response in EDI History (CAP-0152). **Not implemented:** there is no
278 *request* generator anywhere in the codebase, and no life-cycle tracking —
that lives in the uninstalled CAP-0266, which additionally has the defects
listed in §6.21. So the real current workflow is: obtain the authorisation
outside the system, type the number onto the claim.

### WF-0006 — Clinical decision support cycle · **Active**

Rules defined (`clinical_rules`, 80 rows: 16 passive-alert, 18 CQM, 42 AMC,
0 active-alert, 0 patient-reminder) → engine evaluates filters
(`test_filter()` `clinical_rules.php:1581`) → targets (`test_targets()`
`:1911`) → results surface as a chart widget (`:67`), a popup (`:224`) or a
materialised patient reminder (`library/reminders.php:206`) → alerts logged to
`clinical_rules_log`.
**Note for expectation-setting:** the 80 shipped rules all have their alert
flags at 0 — the engine is live but **no rule is currently firing**.

### WF-0007 — Quality measure reporting · **Split**

Legacy path (Active): `reports/cqm.php` → `library/clinical_rules.php` →
`report_results`. 18 CQM + 42 AMC measures, all 2011/2014 Meaningful-Use era.
Modern path (Requires Integration): `src/Services/Qdm/` + `src/Services/Qrda/`
with 26 QDM datatype services and QRDA Cat I/III mustache templates, but
`MeasureService::fetchMeasureSourceOptions()` reads measures from
`ccdaservice/node_modules/oe-cqm-parsers/<year>/json_measures`, which is
**absent** — so the certified export path has zero measures loaded.
`report_results` has 0 rows: no measure run has ever occurred here.

### WF-0008 — Transition of care (C-CDA) · **Active, untested**

Carecoordination module (installed, active) → `ccdaservice/serveccda.js` Node
service on `127.0.0.1:6661` → generate or import a C-CDA → reconcile into the
chart via `cda.component.pre/post.parse` events. All five `ccda*` tables are
empty; the Node service's availability on this host was not probed (GAP-0065).

### WF-0009 — Patient onboarding to the portal · **Disabled**

Staff issue credentials (CAP-0031) → patient registers/logs in (CAP-0173/0174)
→ messaging, appointments, documents, ledger, signing (CAP-0175…0180).
Blocked at step 1 by `portal_onsite_two_enable = 0`. Additionally the portal
address is still the placeholder and reCAPTCHA keys are empty, so
self-registration would fail even after enabling.

### WF-0010 — Inventory / dispensing cycle · **Disabled**

Define product (CAP-0113) → receive lot with expiry (CAP-0114) → dispense to
patient, posting a charge (CAP-0115) → transfer between warehouses (CAP-0116) →
destroy expired stock with a compliance report (CAP-0117) → report (CAP-0118).
Fully implemented; hidden entirely by `inhouse_pharmacy = 0`.

### WF-0011 — Group therapy cycle · **Disabled**

Create group (CAP-0087) → schedule group session (CAP-0088) → open group
encounter (CAP-0089) → record attendance (CAP-0090) → message the member's own
therapist (CAP-0091). Hidden by `enable_group_therapy = 0`.

### WF-0012 — User provisioning and access assignment · **Active**

Create user (SCR-0111) → assign ACL group (SCR-0113) → set provider/authorized
flags, facility and billing facility (SCR-0112) → set main and patient menu
role (CAP-0231) → optionally enrol MFA (CAP-0216/0217, self-service only) →
user signs in at SCR-0002. This is the workflow required before any demo
(§28) and is fully operational today.

### WF-0013 — Audit and tamper verification · **Active and runtime-proven**

Every logged action writes a `log` row plus a `log_comment_encrypt` row holding
a SHA3-512 hash over all 13 mutable columns (`LogTablesSink.php:63`) →
`reports/audit_log_tamper_report.php:249` recomputes and compares → deleted
`log` rows are detected by the LEFT OUTER JOIN at `:231-235`.
**Empirically verified during this audit: 200 of the last 200 rows recomputed
to their stored checksum, 0 mismatches.**
Design limits to disclose: it is a plain hash, not an HMAC (an actor with DB
write access can recompute it), rows are not chained (deleting both rows of a
pair is undetectable), and empty checksums are skipped rather than flagged
(`:242-244`).

### WF-0014 — Module installation · **Active**

Open Module Manager (SCR-0119) → auto-scan registers unregistered directories
at `mod_active=0` → Install → run `sql/install.sql` → install ACL → Enable →
Configure. Verified never exercised on this system (§8.5 note).

### 11.1 Domains with no complete workflow

| Domain | Finding |
|---|---|
| Inpatient / ADT | No workflow exists — nothing to document (§26 Group 1) |
| Denial management | No worklist, appeal or root-cause workflow; only adjustment/remit codes visible during posting |
| Referral closed loop | Outbound referral letter and status only; no inbound acknowledgement or loop closure |
| Telehealth | Entire workflow lives in the uninstalled CAP-0262 |
| Claim status (276/277) | No 276 generator; 277 can only be viewed after a manual file upload |

---

## 12. Users

### 12.1 There is one staff login URL, not one per role

OpenEMR has a **single interactive login page**. Role is decided by ACL group
membership, never by which URL is visited. There is no `/admin`,
`/accountant` or `/reception` entry point, and none can be created without
custom development.

| Entry point | URL | Status |
|---|---|---|
| **Staff / clinical login (all roles)** | `http://localhost:8300/interface/login/login.php?site=default` | **Active — HTTP 200, 9,375 bytes** |
| Patient portal | `http://localhost:8300/portal/index.php` | Disabled (CFG-0071) |
| Portal self-registration | `/portal/account/register.php` | Disabled |
| Portal password reset | `/portal/account/index_reset.php` | Disabled (CFG-0112 `portal_two_pass_reset=0`) |
| REST API | `/apis/default/api/…` | Disabled (CFG-0086) |
| FHIR R4 API | `/apis/default/fhir/…` | Disabled (CFG-0087) |
| Portal API | `/apis/default/portal/…` | Disabled (CFG-0088) |
| OAuth2 authorize | `/oauth2/default/authorize.php` | Inactive — 0 registered clients |
| Setup / installer | `/setup.php` | Present; `$config = 1` so it redirects |

### 12.2 Every account on this system (4)

| ID | Username | Display name | Active | Authorized (provider) | `see_auth` | Calendar | Facility | Billing facility | Main menu role | Patient menu role | `portal_user` | Type |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| USR-0001 | `admin` | Administrator | **1** | **1** | 1 | 1 | **3** | 0 | `standard` | `standard` | 0 | **Human — the only usable login** |
| USR-0002 | `phimail-service` | phiMail Gateway | 0 | 0 | 1 | 0 | 0 | 0 | `standard` | `standard` | 0 | Service placeholder |
| USR-0003 | `portal-user` | Patient Portal User | 0 | 0 | 1 | 0 | 0 | 0 | `standard` | `standard` | 0 | Service placeholder |
| USR-0004 | `oe-system` | System Operation User | 0 | 0 | 1 | 0 | 0 | 0 | `standard` | `standard` | 0 | Service placeholder |

Supplementary: `npi` NULL for all four; `taxonomy` is the installer default
`207Q00000X` (Family Medicine) for all four; `email` and
`google_signin_email` NULL for all; `supervisor_id = 0` and
`physician_type` NULL everywhere — **no supervisory hierarchy is configured**.

### 12.3 Credential status

| ID | Username | Credential | Status |
|---|---|---|---|
| USR-0001 | `admin` | **`LOCAL DEVELOPMENT CREDENTIAL — SECURITY SENSITIVE — MUST NOT BE USED IN PUBLIC DEMO`** | Confirmed valid: `password_verify()` against the stored bcrypt hash in `users_secure` returned true. The plaintext is the OpenEMR installer default and is deliberately **not** reproduced in this document, and must not appear in any marketing, brochure or demo material. |
| USR-0002…0004 | service accounts | none | **No `users_secure` row exists.** `users` has 4 rows, `users_secure` has 1. They would be rejected at `AuthUtils.php:382-391` before the `active=0` check even applied. |

`admin` password state: `last_update_password = 2026-08-07 05:26:37` ⇒ expires
2027-02-03 with a hard cutoff of 2027-03-05 under the live 180 + 30 day policy.
Failed-login counters are all zero.

### 12.4 Role occupancy — the operational headline

| Role | Assigned accounts |
|---|---|
| Administrators | `admin`, `oe-system` |
| Clinicians | **none** |
| Physicians | **none** |
| Front Office | **none** |
| Accounting | **none** |
| Emergency Login | **none** |

Only 2 of 7 roles are populated, and one of those two is a service account.
**No accountant, reception, physician or clinician user exists.** Five of the
six working roles have never been exercised on this system.

Note also that `oe-system` — a service identity — sits in **Administrators**
(`gacl_groups_aro_map`). That is upstream's default, and it is worth reviewing.

### 12.5 Creating the missing role accounts

1. Sign in as `admin`.
2. **Admin → Users** (SCR-0110) → Add User (SCR-0111): set username and a
   policy-compliant password (≥ 9 chars, upper + lower + digit + symbol).
3. Set the **ACL group**: `Accounting` for an accountant, `Front Office` for
   reception, `Physicians` or `Clinicians` for clinical staff.
4. Set **Facility** (only facility id 3 exists) and, for providers, tick
   `authorized` and set NPI/taxonomy.
5. Optionally set **main menu role** to `front_office` for a reception user —
   this is what actually produces a "reception view" (§14).
6. They then sign in at the **same URL** as `admin`.

---

## 13. Roles and ACL

### 13.1 Engine

phpGACL 3.3.7, schema 2.1 (`gacl_phpgacl`), wrapped as `OpenEMR\Gacl\Gacl`.
Runtime checks go through `AclMain::aclCheckCore()`
(`src/Common/Acl/AclMain.php:166-238`); administration through `AclExtended`
(1,169 lines). Module permissions use a **separate** engine,
`AclMain::zhAclCheck()` (`:252-330`), over `module_acl_user_settings` /
`module_acl_group_settings` with precedence *user deny > user allow > group
deny > group allow*.

### 13.2 Live ACL table state

| Table | Rows | Table | Rows |
|---|---:|---|---:|
| `gacl_aco` | **65** | `gacl_aro` | **2** |
| `gacl_aco_sections` | **13** | `gacl_aro_sections` | 1 |
| `gacl_aco_map` | 203 | `gacl_aro_groups` | **7** |
| `gacl_acl` | **19** | `gacl_aro_groups_map` | 19 |
| `gacl_acl_sections` | 2 (`system`, `user`) | `gacl_groups_aro_map` | 2 |
| `gacl_axo` and all 5 other AXO tables | **0** | | |

**The AXO (access-extension-object) subsystem is entirely unused.** All 19
ACLs are in the `system` section and all are `allow=1` — **there are no deny
rules in this installation.**

### 13.3 The four permission levels

`view` · `addonly` · `wsome` (partly modify) · `write`
(`AclMain.php:153-158`).

**These are not ordinal.** `aclCheckCore('patients','demo','user','write')`
matches only an ACL whose `return_value` is literally `write`; `write` does not
imply `view`. Callers wanting a range must pass an array. This is a genuine
correctness trap and is called out at `AclMain.php:219-228`.

Two helpers **fail open**: `aclCheckAcoSpec()` returns `true` on an empty spec
(`:337-339`) and `aclCheckIssue()` likewise (`:369-371`).

### 13.4 The seven roles

| ID | Code | Role | Parent | Grants | Business persona |
|---|---|---|---|---|---|
| ROL-0001 | `users` | OpenEMR Users | — | 0 | Container only; no rights of its own |
| ROL-0002 | `admin` | **Administrators** | `users` | ACL 10: `write` on **all 65 ACOs** | System owner / practice manager |
| ROL-0003 | `clin` | **Clinicians** | `users` | ACL 15 (`view`, 1), 16 (`addonly`, 13), 17 (`write`, 1), 18 (`wsome`, 1), 19 (`write`, 7) | Nurse / allied health: documents and adds, rarely amends |
| ROL-0004 | `doc` | **Physicians** | `users` | ACL 11 (`view`, 1), 12 (`addonly`, 1), 13 (`wsome`, 1), 14 (`write`, **28**) | Treating clinician with full clinical authority |
| ROL-0005 | `front` | **Front Office** | `users` | ACL 20 (`view`, 1), 21/22 (placeholder), 23 (`write`, 3) | Reception: scheduling and demographics only |
| ROL-0006 | `back` | **Accounting** | `users` | ACL 24 (`view`, 1), 25/26 (placeholder), 27 (`write`, **12**) | Biller / revenue-cycle staff |
| ROL-0007 | `breakglass` | **Emergency Login** | `users` | ACL 28: `write` on **all 65 ACOs** | Audited emergency access; no user assigned |

### 13.5 The 13 ACO sections and 65 objects (from the database, not the installer)

| Section | Code | Objects |
|---|---|---|
| Accounting | `acct` | `bill`, `disc`, `eob`, `rep`, `rep_a` |
| Administration | `admin` | `acl`, `batchcom`, `calendar`, `database`, `drugs`, `forms`, `language`, `manage_modules`, `menu`, `practice`, `super`, `superbill`, `users` |
| Encounters | `encounters` | `auth`, `auth_a`, `coding`, `coding_a`, `date_a`, `notes`, `notes_a`, `relaxed` |
| Groups | `groups` | `gadd`, `gcalendar`, `gdlog`, `glog`, `gm` |
| Inventory | `inventory` | `adjustments`, `consumption`, `destruction`, `lots`, `purchases`, `reporting`, `sales`, `transfers` |
| Lists | `lists` | `country`, `default`, `ethrace`, `language`, `state` |
| Menus | `menus` | `modle` |
| Nation Notes | `nationnotes` | `nn_configure` |
| Patient Portal | `patientportal` | `portal` |
| Patients | `patients` | `alert`, `amendment`, `appt`, `demo`, `disclosure`, `docs`, `docs_rm`, `lab`, `med`, `notes`, `pat_rep`, `reminder`, `rx`, `sign`, `trans` |
| Placeholder | `placeholder` | `filler` (holds empty ACLs open) |
| Sensitivities | `sensitivities` | `high`, `normal` |
| Squads | `squads` | *(section exists; **no objects**)* |

Two divergences from the class docblock in `AclMain.php:11-107`, with the
database authoritative: the doc names `menus\|module` but the DB holds
`menus\|modle` (a long-standing upstream typo), and the doc's narrative omits
`patients\|docs_rm`, which does exist.

### 13.6 Cross-role feature access matrix

Derived from the 19 live grants. `Full` = write · `Part` = wsome/mixed ·
`Add` = addonly · `View` = view · `—` = no access.

| Functional area | Admin | Physician | Clinician | Front Office | Accounting | Emergency |
|---|---|---|---|---|---|---|
| Appointments / calendar | Full | Full | Full | **Full** | **Full** | Full |
| Patient demographics | Full | Full | **Add** | **Full** | **Full** | Full |
| Medical history / problems | Full | Full | **Full** | — | — | Full |
| Clinical notes (own encounters) | Full | Full | **Add** | — | — | Full |
| Clinical notes (any encounter) | Full | Full | — | — | — | Full |
| Encounter authorise (own / any) | Full | Full | own only | — | **any only** | Full |
| Coding (own / any) | Full | Full | own only | — | **any only** | Full |
| Fix encounter dates | Full | Full | — | — | **Full** | Full |
| Prescriptions | Full | Full | **Add** | — | — | Full |
| Lab results | Full | Full | **Add** | — | — | Full |
| Sign lab results | Full | Full | — | — | — | Full |
| Documents | Full | Full | **Add** | — | — | Full |
| Documents — delete | **Full** | — | — | — | — | Full |
| Patient report | View | **View** | **View** | — | — | Full |
| Clinical alerts | Full | Full | **Add** | **View** | **View** | Full |
| Amendments / disclosures | Full | Full | **Add** | — | — | Full |
| Billing | Full | — | — | — | **Full** | Full |
| Price discounting | Full | **Full** | — | — | **Full** | Full |
| EOB / payment posting | Full | — | — | — | **Full** | Full |
| Financial reports — own | Full | **Full** | — | — | **Full** | Full |
| Financial reports — all | Full | — | — | — | **Full** | Full |
| Practice settings | Full | — | — | — | **Full** | Full |
| Superbill administration | Full | — | — | — | **Full** | Full |
| Inventory administration | Full | **Full** | **Full** | — | — | Full |
| Inventory operations (8 ACOs) | Full | — | — | — | — | Full |
| Sensitivity — normal | Full | **Full** | **Add** | **—** | **—** | Full |
| Sensitivity — high | Full | **Full** | **—** | **—** | **—** | Full |
| User / ACL / module administration | Full | — | — | — | — | Full |
| Groups (all 5 ACOs) | Full | calendar+log | calendar+log | calendar | — | Full |

Three consequences worth stating plainly to a customer:

1. **Front Office and Accounting have no sensitivity permission at all.** Any
   encounter carrying a non-empty sensitivity value is invisible to them
   entirely — not redacted, invisible.
2. **Clinicians cannot see `high`-sensitivity encounters.** Physicians can.
3. **Accounting can authorise, code and re-date *any* encounter** (`auth_a`,
   `coding_a`, `date_a`) but cannot open clinical notes. That is a deliberate
   biller design, and it surprises people.

---

## 14. Menu Roles

A dimension entirely independent of ACL. Two users in the same ACL group can
see completely different navigation.

| Column | Purpose | Setter |
|---|---|---|
| `users.main_menu_role` | Which main navigation set to load | `MainMenuRole::displayMenuRoleSelector()` `:83-110` |
| `users.patient_menu_role` | Which patient-chart navigation to load | `PatientMenuRole.php:97-120` |

### 14.1 Available main menu roles

| Role value | Offered in the picker? | Nodes | Contents |
|---|---|---|---|
| `standard` | Yes | **183** | Full 16-area menu (§7) |
| `front_office` | Yes | **32** | File · View (Calendar, Flow Board, Recall Board, Address Book) · Patient (Find, Add, Report Generator) · Financial (Payment, Checkout, Front Rec, Patient Ledger) · Popups · Miscellaneous. **No Admin, no clinical, no billing manager.** |
| `answering_service` | Yes | **11** | File · View (Calendar, Flow Board, Address Book) · Messages · Patient (Find, Add) |
| `chart_review` | **No** | **2** | Finder + Patient Report only |
| `Custom.json` | Yes (site file) | **183** | `sites/default/documents/custom_menus/Custom.json` — **byte-identical to `standard.json`** (md5 `36bf8b01e143cf1876e4438ad12dfe3d` for both) |

**`chart_review` is a hidden capability.** `displayMenuRoleSelector()` emits
only `standard`, `answering_service`, `front_office` plus custom `*.json`
(`MainMenuRole.php:86-88`), so `chart_review` can be selected only by writing
`users.main_menu_role = 'chart_review'` directly in the database. See §24.

### 14.2 Patient menu roles

| Role value | Nodes | Contents |
|---|---|---|
| `standard` | 12 | Dashboard · History · Assessments → SDOH Assessment · Report · Documents · Transactions · Issues · Ledger · External Data · PRO · Modules (dynamic) |
| `patient_menus/Custom.json` | 10 | Same minus the Assessments/SDOH pair; PRO uses loose `global_req` instead of strict |

### 14.3 A defect in the front-office menu worth knowing before deployment

`front_office.json` provides `Patient → Add Patient` gated on
`global_req: "full_new_patient_form"` but — unlike `standard.json:93` — ships
**no `!full_new_patient_form` counterpart**. If that global is off, a Front
Office user with the `front_office` menu role has **no way to add a patient**.

### 14.4 Runtime-generated menu content (not in any JSON)

| Menu node | Generated from | Method |
|---|---|---|
| Patient → Visit Forms | `registry` (category headers + one leaf per active form) | `MainMenuRole.php:131-177` |
| Reports → Blank Forms | `registry` LBF-only forms, **appended** | `MainMenuRole.php:183-220` |
| Patient menu → Modules | `modules_hooks_settings` where `fld_type=3 AND mod_active=1 AND attached_to='demographics'` | `PatientMenuRole.php:149-193` |

Two upstream defects observed while reading this code, both material to a
deployment: `MainMenuRole.php:169-171` pushes a form entry only
`if (!empty($catEntry->children))`, which **silently drops the first form in
every category**; and because `updateBlankForms()` is keyed on the label
`"Blank Forms"` alone, it also augments the unrelated
`Miscellaneous → Blank Forms` header.

---

## 15. Access / Sensitivity / Ownership / Facility Model

A user's effective experience is the **intersection of six independent
dimensions**, each verified separately:

```
  effective access
      = ACL group        (7 roles × 65 objects × 4 levels)
      × menu role        (4 shipped sets + site custom)
      × sensitivity      (normal / high, encounter-level)
      × ownership scope  ("my" vs "any" — auth/coding/notes/reports)
      × facility scope   (users.facility_id, billing_facility_id, warehouse)
      × site             (sites/<id>, separate database)
```

### 15.1 Sensitivity — how it actually gates data

Sensitivity is stored on `form_encounter.sensitivity` /
`form_groups_encounter.sensitivity` and enforced by
`AclMain::aclCheckCore('sensitivities', $value)` — with **no return value
passed**, so any allow grant on that ACO suffices (`AclMain.php:212-218`).

| Enforcement point | Evidence |
|---|---|
| Single-encounter fetch | `src/Services/EncounterService.php:449-450`, `:711-721` |
| Encounter view / edit | `C_EncounterVisitForm.class.php:536-539` |
| Sensitivity dropdown filtering | `C_EncounterVisitForm.class.php:250-278` (omits levels the user cannot set) |
| Encounter history list | `patient_file/history/encounters.php:483, 506-508, 524, 554, 680` |
| Forms panel inside an encounter | `patient_file/encounter/forms.php:557-563, 691-699` |
| Group encounter equivalents | `newGroupEncounter/common.php:37,207-232`, `save.php:110-111` |
| Inbound HL7 results | `orders/receive_hl7_results.inc.php:567` — auto-creates encounters at `normal` |

**Two limitations that must be stated in any privacy conversation:**

1. **An empty sensitivity means no restriction.** Every call site is guarded by
   `if ($result['sensitivity'] && !aclCheckCore(…))`. Newly created encounters
   default to blank unless a user actively picks a level.
2. **Sensitivity gates the encounter and its forms only.** It is *not* applied
   to `patient_data`, `lists` (problems, medications, allergies), `pnotes`,
   `documents`, or the REST/FHIR surface. It is an encounter-visibility
   control, not a record-level confidentiality label.

On this installation the sole active user is an Administrator with `write` on
both levels, so **sensitivity currently has zero practical effect**.

### 15.2 Ownership scope — "my" vs "any"

Duplicated ACOs, not a runtime filter: `auth`/`auth_a`,
`coding`/`coding_a`, `notes`/`notes_a`, `rep`/`rep_a`, plus `date_a` which
exists only in the "any" form. Physicians hold both scopes for clinical
objects; Accounting holds only the `_a` variants for encounter authorise/code/
re-date; Clinicians hold only the "my" variants.

### 15.3 Facility and organisational scope

| Scope | Mechanism | Live state |
|---|---|---|
| Site / tenant | `sites/<id>/sqlconf.php`, `?site=` | 1 (`default`) |
| Facility | `facility` table, `users.facility_id` | 1 facility; only `admin` assigned |
| Multi-facility user assignment | `users_facility` | **0 rows** |
| Per-facility provider identifiers | `facility_user_ids` | **0 rows** |
| Billing facility | `users.billing_facility_id`, `facility.billing_location` | all 0 |
| Warehouse | `users.default_warehouse`, `product_warehouse` | unused (domain disabled) |
| Facility restriction enforcement | `restrict_user_facility`, `login_into_facility` | both **0** — no restriction in force |
| Patient cohort | ACO section `squads` | section exists, **no objects** — unusable |

Also note `facility.primary_business_entity = 0` on the single facility: no
facility is flagged as the primary business entity, which breaks billing-entity
resolution in several report paths.

---

## 16. Reports and Analytics

**55 distinct reports.** Source files: 47 in `interface/reports/` (of which 2
are include-only fragments — `criteria.tab.php` and `report.script.php` — and
are excluded), plus 7 report-like screens in `interface/billing/` and 3 in
`interface/orders/`. Reports linked from more than one menu are counted once.

"In-file ACL" is the `AclMain::aclCheckCore()` call **inside** the report.
Where it is `menu-only`, the sole gate is `acl_req` in the menu JSON, which
hides the link but does **not** protect the URL.

### 16.1 Clients / clinical (7)

| ID | Report | File | Purpose | Key filters | Export | In-file ACL | Status |
|---|---|---|---|---|---|---|---|
| RPT-0001 | Patient List | `reports/patient_list.php` | Patients seen in a date range | from/to date | **CSV** | **none — menu-only** | A |
| RPT-0002 | Message List | `reports/message_list.php` | Patient notes and messages over a period | from/to date | **CSV** | `patients\|med` | A |
| RPT-0003 | Patient List Creation | `reports/patient_list_creation.php` | Configurable patient-list builder with per-column ACL | dynamic column set | **CSV** | `patients\|med` + per-option `:826` | A |
| RPT-0004 | Prescriptions and Dispensations | `reports/prescriptions_report.php` | Prescriptions written and products dispensed | drug, lot, patient, dates | print | `patients\|rx` | A |
| RPT-0005 | Clinical Reports | `reports/clinical_reports.php` | Cohort search across dx, allergies, meds, immunisations, labs, service codes | 11 criteria | print | `patients\|med` | A |
| RPT-0006 | Referrals | `reports/referrals_report.php` | Referral transactions in a period | from/to date | print | `patients\|med` | A |
| RPT-0007 | Immunization Registry | `reports/immunization_report.php` | Registry extract with **HL7 v2.5.1 generation** | code, dates | **HL7 file** | `patients\|med` | A |

### 16.2 Visits, scheduling and operations (11)

| ID | Report | File | Purpose | In-file ACL | Status |
|---|---|---|---|---|---|
| RPT-0008 | Daily Summary Report | `reports/daily_summary_report.php` | End-of-day activity summary | `acct\|rep_a` | A |
| RPT-0009 | Appointments Report | `reports/appointments_report.php` | Appointments by provider, category and date; **CSV** | `patients\|appt` | A |
| RPT-0010 | Patient Flow Board Report | `reports/patient_flow_board_report.php` | Throughput through the flow board; drug-screen selection | **none — menu-only** | A |
| RPT-0011 | Encounters Report | `reports/encounters_report.php` | Encounter counts, new-patient flag, e-sign status | `encounters\|coding_a` | A |
| RPT-0012 | Appointments and Encounters | `reports/appt_encounter_report.php` | Reconciles appointments vs encounters vs billing — **missing-charge finder** | `acct\|rep_a` | A |
| RPT-0013 | Superbill (date range) | `reports/custom_report_range.php` | Superbill print for a patient over a range | `encounters\|coding_a` | A |
| RPT-0014 | Chart Location Activity | `reports/chart_location_activity.php` | Paper-chart check-in/out history | **none — menu-only** | A |
| RPT-0015 | Charts Checked Out | `reports/charts_checked_out.php` | Paper charts currently out | **none — menu-only** | A |
| RPT-0016 | Services by Category | `reports/services_by_category.php` | Service-code volume by category | **none** | A |
| RPT-0017 | Syndromic Surveillance — Non Reported | `reports/non_reported.php` | Reportable conditions not yet transmitted; HL7 generation | `patients\|med` | A |
| RPT-0018 | Patient Education Web Lookup | `reports/patient_edu_web_lookup.php` | External education-material lookup by diagnosis | **none** | A |

### 16.3 Clinical decision support and quality (6)

| ID | Report | File | Purpose | In-file ACL | Status |
|---|---|---|---|---|---|
| RPT-0019 | Report Results / History | `reports/report_results.php` | Re-open previous CDR/quality runs | `patients\|med` | A — **`report_results` has 0 rows** |
| RPT-0020 | Standard Measures | `reports/cqm.php?type=standard` | Non-CQM standard rule measures | `patients\|med` | A |
| RPT-0021 | Clinical Quality Measures | `reports/cqm.php` | 18 CQM measures (2011/2014 era) | `patients\|med` | A |
| RPT-0022 | Automated Measure Calculations | `reports/cqm.php?type=amc` | 42 AMC/MU objective measures | `patients\|med` | A |
| RPT-0023 | Alerts Log | `reports/cdr_log.php` | CDR alert and action log | delegated via controller | A |
| RPT-0024 | 2026 Real World Testing Report | `reports/rwt_2026_report.php` | ONC Real World Testing metrics | `admin\|super` | A — **dates hard-coded `2026-04-01`→`2026-09-30`** (`:32-33`) |

### 16.4 Financial (9)

| ID | Report | File | Purpose | Export | In-file ACL | Status |
|---|---|---|---|---|---|---|
| RPT-0025 | Sales by Item | `reports/sales_by_item.php` | Revenue by service/product code | CSV | `acct\|rep`/`rep_a` | A |
| RPT-0026 | Financial Summary by Service Code | `reports/svc_code_financial_report.php` | Charges/payments/adjustments per CPT | CSV | `acct\|rep_a` | A |
| RPT-0027 | Collections and Aging | `reports/collections_report.php` | **AR aging, dunning, insurance vs patient debt, statements** | CSV + PDF | `acct\|rep_a` | A |
| RPT-0028 | Patient Ledger by Date | `reports/pat_ledger.php` | Per-patient charges, payments, adjustments | CSV | `acct\|rep` | A |
| RPT-0029 | Receipts Summary (by method) | `reports/receipts_by_method_report.php` | Receipts by payment method / provider / procedure | print | `acct\|rep_a` | A |
| RPT-0030 | Front Office Receipts | `reports/front_receipts_report.php` | Front-desk cash and copay receipts | print | `acct\|rep_a` | A |
| RPT-0031 | Payment Processing | `reports/payment_processing_report.php` | Card-gateway transaction log | print | `acct\|rep_a` | A |
| RPT-0032 | Cash Receipts by Provider | `billing/sl_receipts_report.php` | Cash receipts by rendering provider | print | `acct\|rep`/`rep_a` | A |
| RPT-0033 | End-of-day sheets (3 variants) | `billing/print_daysheet_report_num{1,2,3}.php` | Day-sheet close-out; variant chosen by `use_custom_daysheet` | print | via Billing Manager | A |

### 16.5 Inventory (4) — all Disabled by `inhouse_pharmacy = 0`

| ID | Report | File | Purpose | In-file ACL | Status |
|---|---|---|---|---|---|
| RPT-0034 | Inventory List | `reports/inventory_list.php` | On-hand stock by warehouse, expiry, reorder | `admin\|drugs` OR `inventory\|reporting` | **D** |
| RPT-0035 | Inventory Activity | `reports/inventory_activity.php` | Receipt/dispense/transfer/destroy movement | `acct\|rep` | **D** |
| RPT-0036 | Inventory Transactions | `reports/inventory_transactions.php` | Transaction-level ledger by type | `acct\|rep` | **D** |
| RPT-0037 | Destroyed Drugs | `reports/destroyed_drugs_report.php` | Destroyed lots in a period (DEA-relevant) | **none — menu-only** | **D** |

### 16.6 Insurance (3) and Procedures (3)

| ID | Report | File | Purpose | In-file ACL | Status |
|---|---|---|---|---|---|
| RPT-0038 | Patient Insurance Distribution | `reports/insurance_allocation_report.php` | Patient counts and revenue by payer | `acct\|rep_a` | A |
| RPT-0039 | Indigent Patients | `billing/indigent_patients_report.php` | Encounters for uninsured patients with totals | `acct\|rep_a` | A |
| RPT-0040 | Unique Seen Patients | `reports/unique_seen_patients_report.php` | Distinct patients seen; optional mailing labels | **none — menu-only** | A |
| RPT-0041 | Pending Orders | `orders/pending_orders.php` | Ordered procedures with no result | `patients\|lab` | A |
| RPT-0042 | Pending Followup from Results | `orders/pending_followup.php` | Abnormal results awaiting follow-up | `acct\|rep` ⚠ | **D** (menu gated on `ippf_specific`) |
| RPT-0043 | Procedure Statistics | `orders/procedure_stats.php` | Lab/procedure result statistics and abnormal rates | `patients\|lab` | A |

⚠ RPT-0042 is an **ACL mismatch**: the menu declares `patients|lab`
(`standard.json:1776`) while the file enforces `acct|rep` (`:27`).

### 16.7 IPPF / family-planning statistics (5) — Disabled

| ID | Report | File | Status |
|---|---|---|---|
| RPT-0044 | Member Association Statistics | `reports/ippf_statistics.php?t=i` | **D** |
| RPT-0045 | GCAC Statistics | `reports/ippf_statistics.php?t=g` | **D** |
| RPT-0046 | MA Statistics | `reports/ippf_statistics.php?t=m` | **D** |
| RPT-0047 | CYP Report | `reports/ippf_cyp_report.php` | **D** |
| RPT-0048 | IPPF Daily Record | `reports/ippf_daily.php` | **D** |

### 16.8 Administrative, audit and EDI (7)

| ID | Report | File | Purpose | In-file ACL | Status |
|---|---|---|---|---|---|
| RPT-0049 | Activity / audit log viewer | `interface/logview/logview.php` | Search the audit trail | `admin\|users` | A |
| RPT-0050 | Background Services | `reports/background_services.php` | Service status, last run, crash state | `admin\|super` | A |
| RPT-0051 | Direct Message Log | `reports/direct_message_log.php` | phiMail send/receive log | `admin\|super` | A — `direct_message_log` 0 rows |
| RPT-0052 | IP Tracker | `reports/ip_tracker.php` | Failed-login IP tracking and blocking | `admin\|super` | A |
| RPT-0053 | Audit Log Tamper Report | `reports/audit_log_tamper_report.php` | Verify the audit log's integrity hashes | `admin\|super` | **A — runtime-verified 200/200** |
| RPT-0054 | AMC Full Report | `reports/amc_full_report.php` | Itemised AMC report renderer | **NONE — no ACL anywhere** ⚠ | A (orphan) |
| RPT-0055 | EDI History | `billing/edih_view.php` | X12 837/835/277/999/271/278 file browser and parser | `acct\|eob` | A |

Also present but unlinked: RPT-0033-adjacent `reports/amc_tracking.php`
(AMC numerator data entry, ACL `patients|med`, no menu link) and
`reports/external_data.php` (patient-file nav only, no ACL). See §24.

### 16.9 Report totals (reconciles with §1.2)

| Category | Count |
|---|---|
| Clients / clinical | 7 |
| Visits, scheduling, operations | 11 |
| CDS and quality | 6 |
| Financial | 9 |
| Inventory | 4 |
| Insurance | 3 |
| Procedures / lab | 3 |
| IPPF statistics | 5 |
| Administrative, audit, EDI | 7 |
| **Total distinct reports** | **55** |
| — Active | **44** |
| — Disabled | **10** (4 inventory, 5 IPPF, 1 pending-followup) |
| — Requires Integration | **1** (RPT-0051 Direct Message Log — needs phiMail) |
| Reports with **no in-file authorisation** | **11 of 55** |
| Reports exporting CSV | 8 |
| Reports generating HL7 | 2 |

### 16.10 There is no analytics or BI layer

Every one of the 55 reports is a static HTML table with optional CSV or print
output. Verified absent: charting, trending, drill-down, scheduled delivery,
KPI surfaces, dashboards in the analytic sense, and any BI tool integration
(`business intelligence`, `power bi`, `grafana`, `metabase`, `data mart`, `ETL`
→ **0 hits each**).

`chart.js` 4.5.1 and `chartjs-adapter-date-fns` are declared in `package.json`
and vendored into `public/assets/`. A repository-wide search for `new Chart(`,
`Chart.register`, `chart.js` and `chartjs` across `**/*.{php,js,twig,html,json}`
returns **no first-party application consumer**: the only `new Chart(`
occurrences are in `Documentation/EHI_Export/schemaspy/layout/bower/admin-lte/`
and `Documentation/EHI_Export/docs/bower/admin-lte/` — vendored AdminLTE demo
files inside generated schema documentation, not application code. GAP-0066
**CLOSED — asset present, no application consumer.**

The only screens called "dashboard" are the patient chart summary
(`patient_file/summary/demographics.php` with its card fragments) and the
portal provider view. They are chart summaries, not analytics.

---

## 17. Integrations

**36 external or interoperability touchpoints.** Every one was located by
searching for the vendor/standard name across the codebase and then opening the
implementation — a filename match alone was never accepted.

**Not one integration is configured or operational on this installation.**
Every credential field is empty, `x12_partners` and `oauth_clients` are empty,
and no custom module is registered. Secrets are shown as `[REDACTED]`; all of
them are in fact empty strings.

### 17.1 Interoperability standards (core, no vendor)

| ID | Integration | Standard | Direction | Implementation | Config | Current state | Cap |
|---|---|---|---|---|---|---|---|
| INT-0001 | C-CDA document exchange | HL7 C-CDA R2.1 | Bi-directional | `ccdaservice/serveccda.js` (Node, bound `127.0.0.1:6661`), `ccda_gateway.php`, Carecoordination module, CDA2 XSDs | `ccda_alt_service_enable=0` | Module **active**; all 5 `ccda*` tables empty; Node service reachability unprobed | CAP-0201 |
| INT-0002 | CCR generation | ASTM E2369 | Outbound | `ccr/createCCR.php` + 8 builder classes; Ccr module | `activate_ccr_ccd_report=1` | Module **active**, never exercised | CAP-0202 |
| INT-0003 | Immunisation registry | HL7 v2.5.1 | Outbound | Immunization module; `reports/immunization_report.php:260` | `disable_immunizations=0` | Module active; no registry endpoint configured | CAP-0203 |
| INT-0004 | Syndromic surveillance | HL7 v2 `ADT^A04`/`A01` | Outbound | `SyndromicsurveillanceTable.php:157-166,255-256`; `reports/non_reported.php:165` | module config | Module active; no receiver configured | CAP-0204 |
| INT-0005 | Lab order transmission | HL7 v2.3 ORM | Outbound | `orders/gen_hl7_order.inc.php:399-449` — protocols `DL`, `SFTP`, `FS` | `procedure_providers` row | **0 providers configured** | CAP-0098 |
| INT-0006 | Lab result receipt | HL7 v2 ORU / MDM | Inbound | `orders/receive_hl7_results.inc.php` (`rhl7FlushMain`, `rhl7FlushMDM`, `match_patient`, `match_lab`, `parseZPS`) | provider route | No feed configured | CAP-0099 |
| INT-0007 | Claims, remittance, eligibility | X12 5010 837P/837I/835/270/271; 277/278/999 read-only | Bi-directional | `src/Billing/X125010837P.php`, `X125010837I.php`, `ParseERA.php`, `EDI270.php`; `library/edihistory/` | `x12_partners` | **0 partners; no file ever generated** | CAP-0128, 0135, 0147 |
| INT-0008 | DICOM image viewing | DICOM (file) | Inbound (view only) | `library/dicom_frame.php`; DWV in `public/assets/dwv/` | none | Active for uploaded files. **No PACS/DIMSE/DICOMweb client — `orthanc`/`dcm4che`/`dicomweb` = 0 hits** | CAP-0205 |
| INT-0009 | Ensora / NewCrop e-prescribing | vendor XML over HTTPS POST | Bi-directional | `interface/eRx.php` auto-POSTs to `erx_newcrop_path`; `eRxSOAP.php`, `eRxXMLBuilder.php`, `eRx_xml.php` | `erx_enable=0`; account id/name/password `[REDACTED — empty]`; endpoints default to `secure.newcropaccounts.com` | **Not configured** | CAP-0110…0112 |
| INT-0010 | Weno Exchange e-prescribing | vendor REST | Bi-directional | `oe-module-weno/` (`TransmitProperties.php`, pharmacy search, log sync) | module config | **Module unregistered**; `weno_*` tables absent | CAP-0267 |
| INT-0011 | Generic clearinghouse | X12 file + SFTP; CAQH CORE 2.2.0 for real-time eligibility | Bi-directional | `X12RemoteTracker.php` (phpseclib3); `EDI270.php:776-870` (multipart MIME, `PayloadType: X12_270_Request_005010X279A1`) | `x12_partners.x12_sftp_*`, `x12_eligibility_endpoint` | **0 partners**; `X12_SFTP` service `active=0` | CAP-0133, 0147 |
| INT-0030 | External terminology distributors | ICD-10, SNOMED CT, RxNorm, CQM value sets | Inbound download | `interface/code_systems/dataloads_ajax.php` | outbound internet access + licences | Not exercised | CAP-0236 |

### 17.2 Named vendor integrations

| ID | Vendor | What it does | Implementation | Configuration surface | Current state | Cap |
|---|---|---|---|---|---|---|
| INT-0012 | **ClaimRev** (Claim Revolution, LLC) | Clearinghouse: claim upload, ERA download, eligibility, denial analytics, AR aging, statements | `oe-module-claimrev-connect/`; `api.claimrev.com`; Azure AD **B2C** OAuth2 client-credentials | `oe_claimrev_config_clientid`/`_clientsecret`/`_environment` | **Unregistered; source is gitignored and composer-installed; 6 tables absent; 0 globals** | CAP-0261 |
| INT-0013 | **DORN** | Diagnostic Ordering Result Network — lab ordering and results | `oe-module-dorn/src/ConnectorApi.php`, `DornGenHl7Order.php`, `ReceiveHl7Results.php` | module config pages | **Unregistered**; 3 tables absent | CAP-0264 |
| INT-0014 | **Quest Diagnostics** | Lab order transmit | Event hooks `lab.transmit`, `lab.post_order_load` (`src/Events/Services/QuestLabTransmitEvent.php:20,26`) + `interface/procedure_tools/quest/gen_hl7_order.inc.php` | listener supplied by a module | **No listener registered** (no modules active) | CAP-0098 |
| INT-0015 | **LabCorp** | Lab order generation | `interface/procedure_tools/labcorp/gen_hl7_order.inc.php` | `procedure_providers` | Not configured | CAP-0097 |
| INT-0016 | **phiMail** (EMR Direct) | DIRECT secure messaging; CCD/CCR transmit | `library/direct_message_check.inc.php` (112 refs), `ccr/transmitCCD.php`, Application module Phimail plugin | `phimail_enable=0`; server `https://phimail.example.com:32541` (**placeholder**); username/password `[REDACTED — empty]` | **Disabled**; background service `phimail` `active=0, running=-1` | CAP-0189 |
| INT-0017 | **Comlink** | Telehealth video: registration API + video API | `oe-module-comlink-telehealth/`; `TelehealthGlobalConfig.php`; `TeleHealthRemoteRegistrationService` | `moduleConfig.php?setup=` (org uid + password, encrypted) | **Unregistered**; 3 tables absent; no globals | CAP-0262 |
| INT-0018 | **SMART / OAuth2 app ecosystem** | Third-party clinical apps launching against the FHIR API | `src/Common/Auth/OpenIDConnect/`, `src/FHIR/SMART/` | `interface/smart/admin-client.php`; `oauth_clients` | **0 clients, 0 tokens**; `site_addr_oath` empty | CAP-0196, 0199 |
| INT-0019 | **Stripe** | Card payments and card-present Terminal | `front_payment_cc.php:21-23,115-173`; `portal_payment.stripe.js`; Omnipay | `gateway_api_key`, `gateway_public_key` `[REDACTED — empty]`; `cc_stripe_terminal=0` | Inactive | CAP-0156, 0157 |
| INT-0020 | **Authorize.Net** | Card payments | `portal/lib/paylib.php:105-113` via Omnipay `AuthorizeNetApi_Api` | `gateway_api_key`, `gateway_transaction_key` `[REDACTED — empty]` | Inactive — **driver installed** (`academe/omnipay-authorizenetapi`, `vendor/academe/`), credentials empty | CAP-0158 |
| INT-0021 | **Sphere / TrustCommerce** | Card payments | `src/PaymentProcessing/Sphere/` | 10 `sphere_*` globals `[REDACTED — all empty]` | Inactive | CAP-0159 |
| INT-0022 | **Rainforest Pay** | Card payments with webhook confirmation | `src/PaymentProcessing/Rainforest/`; `interface/webhooks/payment/rainforest.php` | 4 `rainforest_*` globals `[REDACTED — all empty]` | Inactive | CAP-0160 |
| INT-0023 | **Twilio** | SMS | `oe-module-faxsms/src/Controller/TwilioSMSClient.php`; `src/RestClient/Twilio/` | `module_faxsms_credentials` | **Module unregistered; table absent** | CAP-0265 |
| INT-0024 | **SignalWire** | SMS / voice, with webhook signature validation | `SignalWireClient.php` (75 refs); `src/Utils/SignalWireWebhookValidator.php` | same | Same | CAP-0265 |
| INT-0025 | **etherFAX** | Fax send and receive | `oe-module-faxsms/src/EtherFax/` (`EtherFaxClient`, `FaxAccount`, `FaxStatus`, `FaxReceive`) | same | Same | CAP-0265 |
| INT-0026 | **RingCentral** | Fax and voice | `RCFaxClient.php`; `src/RCVoice/VoiceFunctionsTrait.php` | `setup_rc.php`, `setup_voice.php` | Same | CAP-0265 |
| INT-0027 | **Clickatell** | SMS | `ClickatellSMSClient.php` | same | Same | CAP-0265 |
| INT-0028 | **MedlinePlus Connect** | Patient education content by diagnosis code | `patient_file/education.php:38` → `https://connect.medlineplus.gov/application` | none (public service) | **Operational — the only integration needing no credential** | CAP-0181 |
| INT-0029 | **SMTP mail server** | Outbound email delivery | PHPMailer via `MyMailer`; `Email_Service` background task | `EMAIL_METHOD=SMTP`; `practice_return_email_path` **empty** | Service `active=1` but **sends will no-op** for want of a sender address | CAP-0185 |

### 17.3 Other external touchpoints

| ID | Touchpoint | Purpose | Current state | Cap |
|---|---|---|---|---|
| INT-0031 | Core SMS gateway (non-module) | SMS notifications from the batch tool | `SMS_GATEWAY_USENAME` (sic), `_PASSWORD`, `_APIKEY` all `[REDACTED — empty]`; `SMS_NOTIFICATION_HOUR=50` | CAP-0186 |
| INT-0032 | HylaFAX | Local fax spool | `enable_hylafax=0`; `hylafax_server=localhost`, `hylafax_basedir=/var/spool/hylafax` (**Unix paths on a Windows host**) | CAP-0187 |
| INT-0033 | MedEx | Patient recall/outreach messaging service | `medex_enable=0`; background service `MedEx` `active=0`, `execute_interval=0` (excluded from the runner's scan entirely) | CAP-0190 |
| INT-0034 | Google | reCAPTCHA (portal) and Google Sign-In (staff) | `google_recaptcha_site_key`/`_secret_key` `[REDACTED — empty]`; `google_signin_enabled=0` | CAP-0220, CAP-0174 |
| INT-0035 | USPS Address API v3 | Address validation | `usps_apiv3_client_id`/`_client_secret` `[REDACTED — empty]` | CAP-0004 |
| INT-0036 | Offsite / cloud document storage | Store patient documents in S3/Azure/etc. | **Hook points exist and have no listener** — `documents.remote.storage.location`, `remote.document.retrieve.location`. No first-party cloud storage implementation exists. `scanner_output_directory=/mnt/scan_docs` is a local path | GAP-0045 |

### 17.4 Integration summary

| Measure | Count |
|---|---|
| Integrations catalogued | **36** |
| **Configured** (credentials or endpoint actually set) | **0** |
| **Runtime-verified operational** (exercised from this installation) | **0** |
| Credential-free by design, so reachable without configuration — but **never exercised here** | 1 (INT-0028) |
| Implemented in core, awaiting configuration | 11 |
| Implemented in an **uninstalled module** | 9 |
| Standards implemented but with no counterparty configured | 8 |
| Hook point with no implementation | 1 (INT-0036) |
| Payment gateways present, none configured | 4 |
| Fax/SMS/voice vendors present, none configured | 5 |
| Clearinghouses: named integrations | 1 (uninstalled) + a generic X12/SFTP path |

---

## 18. APIs and Interoperability

### 18.1 Four API families — all disabled

| Family | Base route | Routes | Feature flag | Current | Auth |
|---|---|---|---|---|---|
| Standard REST | `/apis/<site>/api/…` | **98** | `rest_api` | **0** | OAuth2 bearer, `users` role |
| FHIR R4 US Core | `/apis/<site>/fhir/…` | **80** | `rest_fhir_api` | **0** | OAuth2 bearer, `users` or `patient` role |
| Patient Portal | `/apis/<site>/portal/…` | **5** | `rest_portal_api` | **0** | OAuth2 bearer, `patient` role, self-scoped |
| System / bulk scopes | (FHIR `$export`) | — | `rest_system_scopes_api` | **0** | client-credentials |

Loader: `_rest_routes.inc.php:31-35`; dispatcher `apis/dispatch.php` →
`OpenEMR\RestControllers\ApiApplication`.

**Runtime evidence that they are reachable but denying:** `api_log` holds 15
anonymous probe rows dated 2026-08-09 — 8 against
`/oauth2/authorize.php/default/smart/smart-style` and 2 against
`/apis/dispatch.php/default/fhir/metadata`.

### 18.2 Standard REST API — resource inventory (98 routes)

| Verb | Count | | Resource | Endpoints |
|---|---:|---|---|---:|
| GET | 49 | | medical_problem | 7 |
| POST | 18 | | allergy | 7 |
| PUT | 15 | | medication, surgery, dental_issue, appointment | 5 each |
| DELETE | 8 | | facility, encounter, vital, soap_note, practitioner, insurance, prescription | 4 each |
| **PATCH** | **0** | | patient, insurance_company | 4 / 5 |
| | | | document, message, transaction, background_service | 3 each |
| | | | immunization, procedure, drug, user, version/product | 2 each |
| | | | employer, list | 1 each |

ACL enforcement is per route via
`RestConfig::request_authorization_check($request, <section>, <sub>)`:
`patients|med` ×36, `encounters|notes` ×11, `patients|demo` ×10,
`admin|users` ×8, `patients|appt` ×6, `admin|super` ×5, `acct|bill` ×5,
`patients|trans` ×3, `patients|notes` ×3, `patients|docs` ×3,
`lists|default` ×1.

**There is no REST endpoint anywhere for charges, claims, X12, payments,
`ar_activity`, `ar_session`, ledger, statements, aging or ERA.** The revenue
cycle has no programmatic integration surface at all (§31, limitation L-14).

### 18.3 FHIR R4 US Core API — 80 routes, 35 resources

FHIR version **4.0.1** (`FhirMetaDataRestController.php:59-61`); the
CapabilityStatement instantiates
`http://hl7.org/fhir/us/core/CapabilityStatement/us-core-server` (`:55`).

| Capability | Support |
|---|---|
| Resources with search + read | 35 |
| Resources supporting **create/update** | **3 only** — Patient, Practitioner, Organization |
| PATCH / DELETE / `_history` / transaction bundles | **None** |
| CapabilityStatement | `GET /fhir/metadata` |
| SMART discovery | `GET /fhir/.well-known/smart-configuration` |
| OperationDefinition | `GET /fhir/OperationDefinition[/:operation]` |
| Bulk Data Export | `GET /fhir/$export`, `GET /fhir/Patient/$export`, `GET /fhir/Group/:id/$export` |
| Bulk status | `GET`/`DELETE /fhir/$bulkdata-status` |
| `$docref` | `POST /fhir/DocumentReference/$docref` |

Resources: AllergyIntolerance, Appointment, Binary, CarePlan, CareTeam,
Condition, Coverage, Device, DiagnosticReport, DocumentReference, Encounter,
Goal, Group, Immunization, Location, Media, Medication, MedicationDispense,
MedicationRequest, Observation, Organization, Patient, Person, Practitioner,
PractitionerRole, Procedure, Provenance, Questionnaire, QuestionnaireResponse,
RelatedPerson, ServiceRequest, Specimen, ValueSet, + the system-level
operations.

**Billing-domain FHIR is limited to `Coverage` (read/search only).** `Claim`,
`ClaimResponse`, `ExplanationOfBenefit`, `CoverageEligibilityRequest`/
`Response`, `Account`, `Invoice`, `ChargeItem` exist **only** as auto-generated
model classes under `src/FHIR/R4/FHIRResource/` — no service, no controller, no
route. This is the single most common false-positive in FHIR capability claims
and must not be advertised (§27, claim CLM-0016).

**GAP-0068 CLOSED — there is no version mismatch.** The three concepts separate
cleanly:

1. **Configured**: `fhir_us_core_profile_version = 8.0.0`. The enum name is
   explicit — `GlobalConnectorsEnum.php:26`
   `FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION` — this is the *maximum
   supported* version, not an assertion of conformance.
2. **Implemented**: the services are **multi-version aware**, emitting
   different profile URIs per configured version via `getProfileForVersions()`
   (e.g. `FhirConditionService.php:132`,
   `FhirConditionProblemListItemService.php:271`). Comments confirm the
   supported set: `FhirPatientService.php:968` — *"Version 8.0.0 and version
   7.0.0 are backwards compatible with 3.1.1"*. The route filename
   `_rest_routes_fhir_r4_us_core_3_1_0.inc.php` is a **stale filename**, not a
   version constraint, and `FhirMetaDataRestController.php:55` instantiates the
   **version-agnostic** `us-core-server` URL.
3. **Conformance-tested**: **not verified.** The Inferno harness ships
   (`tests/Tests/Certification/HIT1/US_Core_311/`, `ci/inferno/`) but was not
   run. Marketing may state the implemented version; it may **not** state
   certified conformance.

### 18.4 Portal API (5 routes)

All `GET`, patient role only, every one scoped to
`$request->getPatientUUIDString()` — no path patient id is accepted:
`/portal/patient`, `/portal/patient/encounter`,
`/portal/patient/encounter/:euuid`, `/portal/patient/appointment`,
`/portal/patient/appointment/:auuid`.

### 18.5 OAuth2 / SMART

| Grant | Class | Enabled |
|---|---|---|
| `authorization_code` (+ PKCE) | `CustomAuthCodeGrant` | Yes (code TTL `PT1M`, audience-validated) |
| `refresh_token` | `CustomRefreshTokenGrant` | Yes |
| `client_credentials` | `CustomClientCredentialsGrant` | Yes (for `system/` scopes) |
| `password` | `CustomPasswordGrant` | **No** — `oauth_password_grant = 0` |

Endpoints: `/authorize`, `/token`, `/registration` (RFC 7591 dynamic client
registration), `/manage` (RFC 7592), `/introspect` (RFC 7662).
Scopes are generated per resource with `patient/`, `user/` and `system/`
prefixes, plus the fixed set `openid`, `fhirUser`, `offline_access`, `launch`,
`launch/patient`, `system/Patient.$export`, `system/Group.$export`,
`system/*.$export`, `system/*.$bulkdata-status`.

**Live state: `oauth_clients` = 0, `oauth_trusted_user` = 0, `api_token` = 0,
`api_refresh_token` = 0.** Additionally `site_addr_oath` is **empty**, which
would break token audience validation
(`AuthorizationController.php:700,706`) the moment the API is enabled — a
configuration prerequisite, not a defect (GAP-0069).

---

## 19. Configuration and Feature Flags

### 19.1 Scale and structure

`library/globals.inc.php` is 4,563 lines; `$GLOBALS_METADATA` is declared at
`:183` and defines **491 settings across 23 tabs** (plus 2 conditional IPPF
tabs appended at runtime). The `globals` table holds **490 rows**.

| # | Tab | Line | Settings | | # | Tab | Line | Settings |
|---|---|---|---:|---|---|---|---|---:|
| 1 | Appearance | 187 | 28 | | 13 | Notifications | 2451 | 18 |
| 2 | Branding | 436 | 10 | | 14 | CDR | 2591 | 21 |
| 3 | Login Page | 509 | 14 | | 15 | Logging | 2757 | 23 |
| 4 | Locale | 634 | 26 | | 16 | Miscellaneous | 2939 | 17 |
| 5 | Features | 855 | 34 | | 17 | Portal | 3071 | 22 |
| 6 | Report | 1120 | 6 | | 18 | Connectors | 3241 | **57** |
| 7 | Billing | 1190 | **51** | | 19 | Rx | 3732 | 18 |
| 8 | E-Sign | 1575 | 8 | | 20 | PDF | 3892 | 17 |
| 9 | Documents | 1634 | 16 | | 21 | Patient Banner Bar | 4267 | 1 |
| 10 | Calendar | 1745 | **37** | | 22 | Encounter Form | 4280 | 18 |
| 11 | Insurance | 2059 | 5 | | 23 | Questionnaires | 4408 | 16 |
| 12 | Security | 2106 | 30 | | | **Total** | | **491** |

### 19.2 The 120 product-relevant configuration items

Secrets are `[REDACTED]` — **all of them are in fact empty**. "Sec." marks
security-sensitive settings.

#### Feature master switches (CFG-0001…0030)

| ID | Setting | Meaning | Current | Effect when changed | Caps |
|---|---|---|---|---|---|
| CFG-0001 | `rest_api` | Standard REST API on/off | **0** | Enables 98 routes | CAP-0193 |
| CFG-0002 | `rest_fhir_api` | FHIR R4 API on/off | **0** | Enables 80 routes | CAP-0194 |
| CFG-0003 | `rest_portal_api` | Portal API on/off | **0** | Enables 5 routes | CAP-0198 |
| CFG-0004 | `rest_system_scopes_api` | `system/` scopes & bulk export | **0** | Enables `$export` | CAP-0197 |
| CFG-0005 | `oauth_password_grant` | OAuth2 password grant | **0** | Sec. Enables a weaker grant | CAP-0199 |
| CFG-0006 | `oauth_app_manual_approval` | Manual approval of registered apps | 0 | Sec. 0 = auto-approve dynamic registration | CAP-0200 |
| CFG-0007 | `oauth_ehr_launch_authorization_flow_skip` | Skip the EHR-launch consent screen | 1 | Sec. | CAP-0196 |
| CFG-0008 | `site_addr_oath` | OAuth issuer base URL | **empty** | **Must be set before enabling APIs** | CAP-0199 |
| CFG-0009 | `fhir_us_core_profile_version` | **Maximum** supported US Core profile version (`GlobalConnectorsEnum.php:26`) | `8.0.0` | Selects emitted profile URIs; services support 3.1.1 / 7.0.0 / 8.0.0 | CAP-0194 |
| CFG-0010 | `api_log_option` | API request/response logging level | 2 | | CAP-0224 |
| CFG-0011 | `enable_cdr` | Clinical decision rules master switch | **1** | Turns the whole CDS domain on/off | CAP-0063 |
| CFG-0012 | `enable_cqm` | CQM reporting | **1** | | CAP-0168 |
| CFG-0013 | `enable_amc` | AMC reporting | **1** | | CAP-0169 |
| CFG-0014 | `enable_amc_prompting` | AMC prompting in the chart | 1 | | CAP-0169 |
| CFG-0015 | `cqm_performance_period` | eCQM reporting year | `2025` | Selects the measure bundle | CAP-0170 |
| CFG-0016 | `enable_alert_log` | Log CDR alerts | 1 | | CAP-0063 |
| CFG-0017 | `enable_cdr_crw` | Clinical reminder widget | 1 | | CAP-0064 |
| CFG-0018 | `enable_cdr_new_crp` | Active reminder popup | 1 | | CAP-0065 |
| CFG-0019 | `enable_cdr_crp` / `enable_cdr_prw` | Reminder popup / patient reminder widget | 1 / 1 | | CAP-0064, 0066 |
| CFG-0020 | `enable_allergy_check` | Allergy vs medication check | **1** | | CAP-0067 |
| CFG-0021 | `report_itemizing_standard`/`_cqm`/`_amc` | Itemise report output | 1 / 1 / 1 | | CAP-0168 |
| CFG-0022 | `disable_calendar` | Turn the calendar off | **0** | Hides Calendar, appointment reports | CAP-0006 |
| CFG-0023 | `disable_pat_trkr` | Turn the flow board off | **0** | Hides Flow | CAP-0011 |
| CFG-0024 | `disable_rcb` | Turn the recall board off | **0** | Hides Recalls | CAP-0012 |
| CFG-0025 | `disable_prescriptions` | Turn prescriptions off | **0** | Hides Rx report and Rx entry | CAP-0106 |
| CFG-0026 | `disable_immunizations` | Turn immunisations off | **0** | | CAP-0054 |
| CFG-0027 | `enable_fees_in_left_menu` | Show the Fees area | **1** | Hides the whole billing menu if 0 | CAP-0119 |
| CFG-0028 | `enable_edihistory_in_left_menu` | Show EDI History | 1 | | CAP-0151 |
| CFG-0029 | `enable_group_therapy` | Group therapy master switch | **0** | **Hides D07 entirely (5 caps)** | CAP-0087…0091 |
| CFG-0030 | `enable_help` | In-app help modal | 1 | | — |

#### Clinical, records and forms (CFG-0031…0045)

| ID | Setting | Meaning | Current | Caps |
|---|---|---|---|---|
| CFG-0031 | `disable_chart_tracker` | Paper chart tracking off | **0** | CAP-0019 |
| CFG-0032 | `text_templates_enabled` | Text templates in encounter forms | 1 | CAP-0034 |
| CFG-0033 | `use_custom_immun_list` | Custom immunisation list | 0 | CAP-0054 |
| CFG-0034 | `observation_results_immunization` | Immunisation observation results | 1 | CAP-0054 |
| CFG-0035 | `amendments` | Amendments feature | **1** | CAP-0024 |
| CFG-0036 | `omit_employers` | Hide employer capture | 0 | CAP-0021 |
| CFG-0037 | `advance_directives_warning` | Warn on advance directives | 0 | CAP-0029 |
| CFG-0038 | `allow_pat_delete` | Allow admins to delete patients | **0** | **CAP-0032 Disabled** |
| CFG-0039 | `ignore_pnotes_authorization` | Skip authorisation of patient notes | 1 | CAP-0060 |
| CFG-0040 | `enc_sensitivity_visibility` | Which sensitivity levels appear on the encounter form | `show_both` | CAP-0222 |
| CFG-0041 | `enc_enable_discharge_disposition` | Show discharge disposition | `show_both` | CAP-0125 |
| CFG-0042 | `enc_in_collection` | Show the "in collection" flag | `show_both` | CAP-0140 |
| CFG-0043 | `expand_form` | Expand encounter forms by default | 1 | CAP-0034 |
| CFG-0044 | `hide_dashboard_cards` | Which dashboard cards to hide | **not stored** (multi-select, empty) | CAP-0004 |
| CFG-0045 | `inhouse_pharmacy` | In-house dispensary master switch | **0** | **Hides 6 caps + 4 reports** — CAP-0113…0118 |

#### Billing, claims and insurance (CFG-0046…0060)

| ID | Setting | Meaning | Current | Caps |
|---|---|---|---|---|
| CFG-0046 | `discount_by_money` | Discount entered as money vs percent | **1** (money) | CAP-0124 |
| CFG-0047 | `ub04_support` | Institutional (UB-04 / 837I) billing | **0** | **CAP-0129, CAP-0131 Disabled** |
| CFG-0048 | `auto_sftp_claims_to_x12_partner` | Auto-push claim batches by SFTP | **0** | **CAP-0133, CAP-0134 Disabled** |
| CFG-0049 | `enable_posting` | EOB/ERA posting | **1** | CAP-0135 |
| CFG-0050 | `enable_batch_payment` | Batch payment entry | **1** | CAP-0137 |
| CFG-0051 | `ledger_begin_date` | Ledger lookback window | `Y1` | CAP-0139 |
| CFG-0052 | `use_custom_statement` | Custom statement layout | 0 | CAP-0142 |
| CFG-0053 | `enable_eligibility_requests` | Real-time eligibility | **0** | **CAP-0148 Disabled** |
| CFG-0054 | `payment_gateway` | Which card gateway to use | **`InHouse`** | CAP-0155…0160 |
| CFG-0055 | `cc_stripe_terminal` / `cc_front_payments` | Card-present terminal / front card payments | 0 / 0 | CAP-0157, 0156 |
| CFG-0056 | `gateway_mode_production` | Gateway live vs test | 0 | Sec. CAP-0156 |
| CFG-0057 | `gen_x12_based_on_ins_co` | One batch per partner vs one per run | 0 | CAP-0128 |
| CFG-0058 | `support_encounter_claims` | Allow reporting-only encounter claims | 0 | CAP-0126 |
| CFG-0059 | `force_claim_balancing` | Enforce ERA balancing | **1** | CAP-0135 |
| CFG-0060 | `specific_application` (`ippf_specific`) | IPPF/family-planning vertical | **0** | **CAP-0171 + 5 reports Disabled** |

#### E-signature, security and authentication (CFG-0061…0080)

| ID | Setting | Meaning | Current | Sec. | Caps |
|---|---|---|---|---|---|
| CFG-0061 | `esign_individual` | Sign individual forms | **1** | | CAP-0048 |
| CFG-0062 | `esign_all` | Sign the whole encounter | 0 | | CAP-0048 |
| CFG-0063 | `lock_esign_individual` | Lock a signed form | **1** | ✓ | CAP-0049 |
| CFG-0064 | `esign_lock_toggle` | Let users toggle the lock | 0 | ✓ | CAP-0049 |
| CFG-0065 | `timeout` | Idle session timeout (s) | **7200** | ✓ | CAP-0214 |
| CFG-0066 | `portal_timeout` | Portal idle timeout (s) | 1800 | ✓ | CAP-0214 |
| CFG-0067 | `secure_password` | Require all 4 character classes | **1** | ✓ | CAP-0210 |
| CFG-0068 | `gbl_minimum_password_length` | Minimum password length | **9** | ✓ | CAP-0210 |
| CFG-0069 | `gbl_maximum_password_length` | Maximum password length | 72 | ✓ | CAP-0210 |
| CFG-0070 | `password_expiration_days` / `password_grace_time` | Expiry and grace | **180 / 30** | ✓ | CAP-0210 |
| CFG-0071 | `password_history` | Passwords remembered | **5** | ✓ | CAP-0210 |
| CFG-0072 | `password_max_failed_logins` | Per-user lockout threshold | **20** | ✓ | CAP-0211 |
| CFG-0073 | `ip_max_failed_logins` | Per-IP lockout threshold | **100** | ✓ | CAP-0212 |
| CFG-0074 | `time_reset_password_max_failed_logins` / `ip_…` | Auto-unblock windows (s) | 3600 / 3600 | ✓ | CAP-0211/0212 |
| CFG-0075 | `gbl_auth_hash_algo` | Password hash algorithm | `DEFAULT` → **bcrypt cost 10** | ✓ | CAP-0209 |
| CFG-0076 | `gbl_ldap_enabled` | LDAP/AD authentication | **0** | ✓ | CAP-0219 |
| CFG-0077 | `google_signin_enabled` | Google Sign-In | **0** | ✓ | CAP-0220 |
| CFG-0078 | `secure_upload` | Upload whitelist enforcement | 1 | ✓ | CAP-0022 |
| CFG-0079 | `database_encryption` / `drive_encryption` | Encrypt DB fields / stored files | **1 / 1** | ✓ | CAP-0022 |
| CFG-0080 | `hide_document_encryption` | Hide encrypt/decrypt controls | 0 | ✓ | CAP-0022 |

#### Audit and logging (CFG-0081…0090)

| ID | Setting | Meaning | Current | Caps |
|---|---|---|---|---|
| CFG-0081 | `enable_auditlog` | Audit master switch | **1** | CAP-0224 |
| CFG-0082 | `audit_events_query` | Log SELECT statements too | **1** | CAP-0224 — see GAP-0061 |
| CFG-0083 | `audit_events_patient-record` / `-scheduling` / `-order` / `-lab-results` / `-security-administration` / `-backup` / `-http-request` / `-other` | Per-category audit | all **1** | CAP-0224 |
| CFG-0084 | `audit_events_cdr` | Audit CDR events | **0** | CAP-0063 |
| CFG-0085 | `audit_events_lab-order` | Audit lab-order events | **Not defined at all.** `EventAuditLogger.php:77` reads it, but it is absent from `$GLOBALS_METADATA` (`globals.inc.php:2785-2850` defines 10 `audit_events_*` keys and not this one) ⇒ no row can exist ⇒ `getBoolean()` false ⇒ `procedure_order` / `procedure_order_code` events **never audited**. GAP-0070 **CLOSED — upstream software defect** | CAP-0224 |
| CFG-0086 | `gbl_force_log_breakglass` | Always fully log break-glass users | **1** | CAP-0223 |
| CFG-0087 | `enable_atna_audit` | Forward audit to ATNA/syslog | **0** | CAP-0225 |
| CFG-0088 | `atna_audit_host` / `_localcert` / `_cacert` / `_port` | ATNA endpoint | blank / blank / blank / 6514 | CAP-0225 |
| CFG-0089 | `billing_log_option` / `gbl_print_log_option` | Billing and print logging | 1 / 2 | CAP-0224 |
| CFG-0090 | `system_error_logging` / `user_debug` / `user_php_debug` | Error verbosity | `WARNING` / 0 / 0 | — |

#### Portal, communications and notifications (CFG-0091…0105)

| ID | Setting | Meaning | Current | Caps |
|---|---|---|---|---|
| CFG-0091 | `portal_onsite_two_enable` | **Patient portal master switch** | **0** | **CAP-0173…0180, CAP-0031 all Disabled** |
| CFG-0092 | `portal_onsite_two_address` | Portal public URL | `https://your_web_site.com/openemr/portal` (**placeholder**) | CAP-0173 |
| CFG-0093 | `portal_onsite_two_register` | Allow self-registration | 0 | CAP-0174 |
| CFG-0094 | `portal_two_pass_reset` | Allow portal password reset | 0 | CAP-0174 |
| CFG-0095 | `portal_two_payments` | Portal payments | 0 | CAP-0179 |
| CFG-0096 | `portal_two_ledger` | Portal ledger view | 1 | CAP-0178 |
| CFG-0097 | `allow_portal_appointments` / `allow_portal_uploads` | Portal booking / uploads | 1 / 1 | CAP-0176/0177 |
| CFG-0098 | `portal_onsite_document_download` | Portal document download | 1 | CAP-0177 |
| CFG-0099 | `use_email_for_portal_username` / `enforce_signin_email` | Portal identity by email | 1 / 1 | CAP-0173 |
| CFG-0100 | `google_recaptcha_site_key` / `_secret_key` | Portal anti-bot keys | `[REDACTED — empty]` | CAP-0174 |
| CFG-0101 | `EMAIL_METHOD` | Mail transport | `SMTP` | CAP-0185 |
| CFG-0102 | `practice_return_email_path` / `patient_reminder_sender_email` | Sender addresses | **both blank ⇒ sends no-op** | CAP-0185 |
| CFG-0103 | `SMS_GATEWAY_USENAME` / `_PASSWORD` / `_APIKEY` | Core SMS gateway | `[REDACTED — all empty]` | CAP-0186 |
| CFG-0104 | `enable_hylafax` / `hylafax_server` / `hylafax_basedir` | Fax spool | **0** / `localhost` / `/var/spool/hylafax` | CAP-0187 |
| CFG-0105 | `enable_scanner` / `scanner_output_directory` | Scanner intake | **0** / `/mnt/scan_docs` | CAP-0188 |

#### eRx, Direct and outreach (CFG-0106…0112)

| ID | Setting | Meaning | Current | Caps |
|---|---|---|---|---|
| CFG-0106 | `erx_enable` | External e-prescribing master switch | **0** | **CAP-0110…0112** |
| CFG-0107 | `erx_account_id` / `_name` / `_partner_name` / `_password` | eRx vendor account | 1 / blank / blank / `[REDACTED — empty]` | CAP-0110 |
| CFG-0108 | `erx_newcrop_path` / `_soap` | eRx vendor endpoints | vendor defaults | CAP-0110 |
| CFG-0109 | `phimail_enable` | DIRECT messaging master switch | **0** | CAP-0189 |
| CFG-0110 | `phimail_server_address` / `_username` / `_password` | phiMail endpoint | **placeholder** / blank / `[REDACTED — empty]` | CAP-0189 |
| CFG-0111 | `phimail_ccd_enable` / `_ccr_enable` / `_interval` | DIRECT document options | 0 / 0 / 5 | CAP-0189 |
| CFG-0112 | `medex_enable` | MedEx outreach service | **0** | CAP-0190 |

#### Locale, multi-site and operations (CFG-0113…0120)

| ID | Setting | Meaning | Current | Caps |
|---|---|---|---|---|
| CFG-0113 | `language_default` / `language_menu_showall` | Default language / show all | `English (Standard)` / 1 | CAP-0251 |
| CFG-0114 | `translate_layout` / `_lists` / `_gacl_groups` / `_form_titles` / `_document_categories` / `_appt_categories` | What gets translated | **all 1** | CAP-0252 |
| CFG-0115 | `disable_translation` / `translation_preload_cache` | Translation off / cache | 0 / 0 | CAP-0251 |
| CFG-0116 | `gbl_currency_symbol` / `currency_decimals` / `currency_dec_point` / `currency_thousands_sep` | Currency presentation | `$` / 2 / `.` / `,` | CAP-0254 |
| CFG-0117 | `date_display_format` / `time_display_format` / `gbl_time_zone` | Date, clock, timezone | 0 (ISO) / 0 (24 h) / **empty** | CAP-0255 |
| CFG-0118 | `units_of_measurement` / `us_weight_format` / `phone_country_code` | Units, weight, phone country | 1 / 1 / **1 (US/NANP)** | CAP-0256 |
| CFG-0119 | `restrict_user_facility` / `login_into_facility` / `set_facility_cookie` / `gbl_fac_warehouse_restrictions` | Facility scoping | **0 / 0 / 0 / 0** | CAP-0249, 0250 |
| CFG-0120 | `mysql_bin_dir` / `perl_bin_dir` / `temporary_files_dir` / `backup_log_dir` | OS tool paths | `C:/xampp/mysql/bin` ⚠ / `C:/xampp/perl/bin` ⚠ / `C:/windows/temp` / `C:/windows/temp` | **CAP-0240 operationally blocked** — proven at `backup.php:126,457-458`; GAP-0064 closed |

### 19.3 Feature flags that hide the most capability

| Flag | Value | Capabilities suppressed |
|---|---|---|
| `portal_onsite_two_enable` | 0 | **9** (CAP-0031, 0173…0180) |
| `inhouse_pharmacy` | 0 | **7** (CAP-0113…0118, 0249) + 4 reports |
| `rest_api` + `rest_fhir_api` + `rest_portal_api` + `rest_system_scopes_api` | 0 | **9** (CAP-0193…0200, 0207/0245) |
| `enable_group_therapy` | 0 | **5** (CAP-0087…0091) |
| `erx_enable` | 0 | **3** (CAP-0110…0112) |
| `ub04_support` | 0 | 2 (CAP-0129, 0131) |
| `auto_sftp_claims_to_x12_partner` | 0 | 2 (CAP-0133, 0134) |
| `specific_application` (IPPF) | 0 | 1 capability + 5 reports |
| `enable_hylafax` + `enable_scanner` | 0 + 0 | 2 (CAP-0187, 0188) |

**Recounted precisely (unique CAP IDs, not references): 39 of the 47 Disabled
capabilities are removed by 12 individual feature-flag settings, shown above as
8 grouped rows.** The grouping matters — the API row is four separate settings
(`rest_api`, `rest_fhir_api`, `rest_portal_api`, `rest_system_scopes_api`) and
the fax row is two (`enable_hylafax`, `enable_scanner`).

Per-flag unique-capability coverage: portal 9 · in-house pharmacy 8 · APIs 10 ·
group therapy 5 · UB-04 2 · claim SFTP 2 · fax/scanner 2 · IPPF 1 = **39**.
The remaining **8** Disabled capabilities are individually gated
(CAP-0032 `allow_pat_delete`; CAP-0148 `enable_eligibility_requests`;
CAP-0189 `phimail_enable`; CAP-0190 `medex_enable`; CAP-0219 `gbl_ldap_enabled`;
CAP-0220 `google_signin_enabled`; CAP-0225 `enable_atna_audit`;
CAP-0250 `restrict_user_facility`).

For a commercial conversation this remains the single most useful fact in the
document: most of what appears "missing" is one configuration session away.

### 19.4 Configuration drift analysis — the headline

`$GLOBALS_METADATA` (491 defaults) was loaded programmatically and diffed
against all 490 stored rows.

| Category | Count |
|---|---|
| Stored rows | 490 |
| In metadata but not stored | 2 (`hide_dashboard_cards`, `language_menu_other` — both empty multi-selects) |
| Stored with no metadata entry | 1 (`hidden_auth_dummy_hash` — internal timing-attack decoy, `[REDACTED]`) |
| **Values differing from the code default** | **6** |
| **Deliberate deployment configuration** | **effectively 0** |

All six "differences" are environmental, not chosen:

| Setting | Code default (Linux branch) | Stored | Why |
|---|---|---|---|
| `mysql_bin_dir` | `/usr/bin` | `C:/xampp/mysql/bin` | Windows branch default, `globals.inc.php:91` |
| `perl_bin_dir` | `/usr/bin` | `C:/xampp/perl/bin` | Windows branch default, `:92` |
| `temporary_files_dir` | `/tmp` | `C:/windows/temp` | `:93` |
| `backup_log_dir` | `/tmp` | `C:/windows/temp` | `:94` |
| `post_to_date_benchmark` | computed at read time | `2026-07-28` | dynamic date default |
| `unique_installation_id` | random UUID on first use | `611cc714-…` | auto-generated |

**Conclusion: the `globals` table is at 100 % stock OpenEMR defaults.** No
practice branding, no time zone, no locale choice, no portal address, no SMTP
sender, no eRx/API/portal enablement, no payment gateway. Live placeholder
values include `openemr_name='OpenEMR'`,
`login_tagline_text='The most popular open-source Electronic Health Record…'`,
`main_menu_logo_link='https://www.open-emr.org/'`,
`pqri_registry_name='Model Registry'`, `pqri_registry_id='125789123'`,
`display_donations_link=1` and `display_review_link=1`.

### 19.5 User-level settings

`user_settings` holds **33 rows for 2 users** (`setting_user` 0 and 1), and all
of them are UI state, not configuration: 16 `*_ps_expand` dashboard-card
expand/collapse flags, `gacl_protect`, `setting_bootstrap_submenu` and 5
message-filter persistence keys. **No user has any user-specific global
override**, though `$USER_SPECIFIC_GLOBALS`/`$USER_SPECIFIC_TABS` are wired at
`globals.inc.php:4560`.

### 19.6 Site configuration files

`sites/` contains exactly one directory, `default`.

`sites/default/sqlconf.php`: `$host=127.0.0.1`, `$port=3306`,
`$login=openemr`, `$pass=[REDACTED]`, `$dbase=openemr`, `$config=1`
(install complete). **This file is tracked by git and now carries local
credentials — it shows as modified and must not be committed.**

`sites/default/config.php` is stock and contains three settings that cannot
work on this host:

| Setting | Value | Problem |
|---|---|---|
| `OPENEMR_PRINT_COMMAND` | `lpr -P HPLaserjet6P …` | Unix `lpr` on Windows |
| `OPENEMR_HYLAFAX_ENSCRIPT` | `enscript -M Letter …` | Unix-only |
| `oer_config['documents']['file_command_path']` | `/usr/bin/file` | Unix path on Windows |
| `oer_config['ofx']['bankid']` / `['acctid']` | `123456789` | Placeholder |

### 19.7 Background services

| Service | Active | Interval | Next run | Assessment |
|---|---:|---:|---|---|
| `Email_Service` | **1** | 2 min | **2021-01-18** | Seed value never advanced ⇒ **the runner has never executed it** |
| `UUID_Service` | **1** | 240 min | **2021-01-18** | Same |
| `MedEx` | 0 | **0** | 2017-05-09 | `execute_interval=0` excludes it from the runner's scan entirely (`BackgroundServiceRunner.php:361`) |
| `phimail` | 0 | 5 min | 2026-08-07 | `running = -1` — the disabled sentinel |
| `X12_SFTP` | 0 | 1 min | 2021-01-18 | Required for CAP-0133 |

**Two of five services are nominally active and neither has ever run. GAP-0063
is CLOSED — the root cause is proven from configuration alone:**

| Trigger path | State |
|---|---|
| Browser-driven REST call — `main.php:270` fetches `/apis/<site>/api/background_service/$run` | **Dead.** That route lives in the standard REST map (`_rest_routes_standard.inc.php:705-715`) and `rest_api = 0` |
| Legacy AJAX — `library/ajax/execute_background_services.php` | **Never called.** A repository search finds references only in `.phpstan/baseline/` files — no application caller |
| CLI — `BackgroundServicesCommand` | Exists, but nothing invokes it: **no Windows scheduled task** references OpenEMR (`Get-ScheduledTask` scan) |
| Env kill-switch — `OPENEMR__NO_BACKGROUND_TASKS` (`main.php:61`) | **Not the cause** — no `.env` file exists; only `.env.example` with the value empty |

**There is no live trigger of any kind.** Enabling the REST API, adding a
scheduled task, or invoking the CLI command would each restore execution.

---

## 20. Security / Privacy / Audit

A deliberate separation is maintained throughout this section between a
**technical control that exists** and **certified compliance with a
regulation**. This system implements many controls associated with HIPAA and
ONC certification. **This audit makes no compliance claim whatsoever** — see
§27 for the wording rules.

### 20.1 Authentication (verified)

| Control | Implementation | Live setting |
|---|---|---|
| Password hashing | `AuthHash.php:52-124`; supports bcrypt, Argon2i, Argon2id, SHA512-crypt; graceful degradation | **bcrypt, cost 10** (confirmed from the `$2y$10$` prefix of the decoy hash) |
| Legacy hash support | Pre-5.0.0 21-char-salt bcrypt re-derived and `hash_equals`-compared (`:206-224`) | — |
| Rehash on login | `passwordNeedsRehash()` → new hash written (`AuthUtils.php:450-458`) | active |
| Pre-auth gate order | IP block → empty creds → user exists → `active=1` → in an auth group → in a GACL group → `users_secure` row → failed-login counter → hash validity → verify → rehash → expiry (`AuthUtils.php:275-499`) | — |
| Username comparison | `BINARY username` ⇒ **case-sensitive usernames** | `:329, :380, :1092` |
| Timing-attack mitigation | Verifies against a stored decoy hash on every failure branch (`:1406-1416`) | active |
| Session variables | `authUser`, `authPass` (the **hash**), `authUserID`, `authProvider`, `userauthorized` | `:1526-1539` |
| Per-request revalidation | Re-queries the user and compares the session hash; forced logout on mismatch | `:837-861`; `library/auth.inc.php:97-101` |
| Login-page hardening | `X-Frame-Options: DENY`; `Content-Security-Policy: frame-ancestors 'none'` | `interface/login/login.php:31-32` |
| Shell hardening | One-shot `token_main` (single-use URL); `window.opener = null`; per-window session migration | `main.php:78-92, 120-121`; `library/restoreSession.php` |

### 20.2 Multi-factor authentication — the most important security finding

| Factor | Implemented | Wired into browser login | Wired into OAuth2/API | Enrolled users |
|---|---|---|---|---|
| TOTP | **Yes** (`MfaUtils.php:136-187`, `Totp.class.php` — 160-bit secret, 6 digits, 30 s, SHA-1) | **Yes — for enrolled users** (`main_screen.php:171,295-340`) | Yes | **0** |
| U2F | **Yes** (`MfaUtils.php:194-225`, `yubico/u2flib-server`) | **Yes — for enrolled users** (`main_screen.php:68-92,179-290`) | Yes | **0** |
| WebAuthn / FIDO2 / passkeys | **No** | — | — | — |
| SMS / email OTP as a second factor | **No** — the portal's `enforce_signin_email` is documented in-code as *"the legacy 'passaddon' feature, **not MFA**"* (`PatientPortalLoginController.php:93`) | — | — | — |

**Correction to an earlier reading of this codebase.** `interface/login/login.php`
is only a Twig form renderer and `library/auth.inc.php` contains no MFA code —
but the browser login does **not** end there. The POST target is
`interface/main/main_screen.php`, and that file **does** implement the MFA
challenge: `:148` enters the block, `:153-156` queries
`login_mfa_registrations` for the authenticated user, `:160-169` sets
`$isU2F`/`$isTOTP`, `:171` gates the challenge on `$registrationAttempt`, and
`:295-340` renders the TOTP form (`:68-92` the U2F flow). Progression is
blocked until the response validates.

**Current behaviour, precisely:**

1. An **enrolled** user *is* challenged for TOTP or U2F at browser login.
2. An **unenrolled** user completes login with a password alone.
3. There is **no way for an administrator to mandate MFA** — no
   `force_mfa`/`mfa_required`/`require_mfa`/`gbl_mfa` global exists.
4. `login_mfa_registrations` is empty, so **no user is currently challenged**.

The limitation is therefore *mandatory enforcement*, not *wiring*. CAP-0216 and
CAP-0217 protect the browser login for enrolled users; CAP-0218 (mandate)
remains `Missing`. GAP-0060 is closed as a confirmed product limitation.

### 20.3 Authorization

Covered in §13. Key security-relevant characteristics: four non-ordinal
permission levels; explicit deny always wins (`AclMain.php:232-237`);
superuser short-circuit at `:174-176`; and two fail-open helpers
(`aclCheckAcoSpec`, `aclCheckIssue` return `true` on an empty spec).

### 20.4 Audit

| Property | Finding |
|---|---|
| Architecture | `EventAuditLogger` → `MultiSink` → `LogTablesSink` (+ optional `AtnaSink`). **Uses a separate DBAL connection** to avoid `lastInsertId()` cross-talk (`EventAuditLogger.php:42-44`) |
| Named events | `login`, `logout`, `auth`, `api`, `portalapi`, `oauth2`, `password-create`, `password-change`, `disclosure`, `delete`, `http-request-*` |
| Automatic SQL auditing | 66-table map (`:132-199`) plus a `form_*` wildcard; categories `patient-record`, `order`, `lab-order`, `lab-results`, `scheduling`, `security-administration`, `other`; 17 sub-categories via `eventCategoryFinder()` (`:746-810`) |
| Tamper detection | SHA3-512 over all 13 mutable columns, recomputed and compared by the report. **Verified: 200/200 matched, 0 mismatches** |
| Deleted-row detection | LEFT OUTER JOIN from `log_comment_encrypt` to `log` (`audit_log_tamper_report.php:231-235`) |
| Log encryption | **OFF, and the code path was deliberately removed** (`EventAuditLogger.php:662-663`). Comments are **base64-encoded, not encrypted** (`:666`); all 4,280 rows are `encrypt='No'`, `version=4` |
| Live volume | 4,280 rows over ~2 days |

Three limitations to disclose honestly:

1. **It is a plain hash, not an HMAC.** No secret key is involved, so anyone
   with database write access can alter a `log` row and recompute a valid
   checksum. It detects accidental or naive tampering, not a privileged
   attacker.
2. **Rows are not chained.** Deleting a `log` row *and* its
   `log_comment_encrypt` partner is completely undetectable.
3. **SQL bind parameters are appended verbatim to `log.comments`**
   (`:457-461`). On a system with real data that means PHI — and any bound
   secret — lands in the audit table in plaintext base64.

**Signal-to-noise problem:** 3,984 of 4,280 rows (**93 %**) are
`security-administration-select`, generated because `audit_events_query=1`
combined with all 24 `gacl_*` tables being classified `security-administration`
means *every ACL check that touches the database writes an audit row* —
roughly 2,000 rows/day on a completely idle single-user system (GAP-0061).

Also: `audit_events_lab-order` is read by the code (`:77`) but is **not defined
in `$GLOBALS_METADATA` at all** — so no row can ever exist and lab-order
events are silently never audited. This is an upstream defect, not a local
misconfiguration (GAP-0070, closed).

`audit_master`/`audit_details` are **not** the security audit trail — they are
the pending-approval staging area for CCDA/portal-submitted data. Both are
empty.

### 20.5 Privacy controls

| Control | State |
|---|---|
| Encounter sensitivity (normal/high) | Active, but see the two limitations in §15.1 |
| Disclosure accounting | Active; `extended_log` **0 rows** |
| Amendments | Active; `amendments` **0 rows** |
| Break-glass with forced logging | Active; **no user assigned**; `Emergency_Login_email_id` blank so no alert is sent |
| Field-level DB encryption | `database_encryption=1`, `drive_encryption=1` |
| E-signature with lock | Active (`esign_signatures` 0 rows) |
| IP tracking and blocking | Active; 1 clean row (`::1`) |

### 20.6 Security observations arising from this audit

These are findings, not capabilities. They are recorded because a capability
catalogue that hides them is not honest.

| # | Observation | Evidence |
|---|---|---|
| 1 | MFA **is** wired into the browser login for enrolled users, but **cannot be mandated** — an unenrolled user logs in with a password alone and no enforcement global exists | §20.2; `main_screen.php:153-171` |
| 2 | 11 of 55 reports have **no in-file authorisation** and rely only on menu hiding; two of them (`patient_list.php`, `unique_seen_patients_report.php`) return patient identifiers and export CSV | §16 |
| 3 | `reports/amc_full_report.php` is a **directly reachable page with no ACL check of any kind** and no menu link | RPT-0054 |
| 4 | `interface/super/layout_listitems_ajax.php` is an admin endpoint with CSRF but **no `aclCheckCore`** | `:22` |
| 5 | ACL mismatch: RPT-0042 menu declares `patients\|lab`, the file enforces `acct\|rep` | `standard.json:1776` vs `pending_followup.php:27` |
| 6 | **`checkControllerAcl()` returns early for any controller absent from `CONTROLLER_ACL_MAP`** (`Controller.class.php:131-135`), and that map has only **2 entries** (`practice_settings`, `prescription`) for **10 registered controllers**. `Controller::act()` takes the *first* query key as the controller, so the menu path `?practice_settings&x12_partner&…` **is** gated on `admin\|practice` — but a direct `?x12_partner&action=list` routes straight to `C_X12Partner` with **no ACL check at all**, and that controller performs none internally. `controller.php:9` requires `interface/globals.php`, so this is authenticated-user-only, not anonymous. GAP-0062 **CLOSED — confirmed access-control defect** | `Controller.class.php:131-135,164-186`; `C_X12Partner.class.php` |
| 7 | The uninstalled prior-auth module POSTs clinic name/phone/email to an undisclosed third party at registration | `AuthorizationService::registration()` → `api.affordablecustomehr.com` |
| 8 | `oe-system`, a service identity, is a member of **Administrators** | `gacl_groups_aro_map` |
| 9 | Audit comments carry bound SQL parameters in plaintext base64 | `EventAuditLogger.php:457-461` |
| 10 | LDAP and Google Sign-In paths **bypass the failed-login counter and password expiry** (both currently off, so latent) | `AuthUtils.php:411-422, 1088-1090, 1443-1517` |
| 11 | Account-block notification emails cannot be delivered — sender addresses are blank | `AuthUtils.php:1364-1374` + CFG-0102 |
| 12 | `oe-module-claimrev-connect` is **gitignored** and composer-installed: its source is not under version control here — a supply-chain provenance gap | `.gitignore:15` |

---

## 21. Multi-site / Multi-facility / Scoping

### 21.1 What exists

| Layer | Mechanism | Live state |
|---|---|---|
| **Site (tenant)** | `sites/<id>/` with its own `sqlconf.php` pointing at its **own database**; selected by `?site=` and validated against `[A-Za-z0-9\-.]` (`index.php:20-22`) | **1** — `default` |
| **Facility** | `facility` table with 38+ columns including `facility_npi`, `pos_code`, `x12_sender_id`, `oid`, `service_location`, `billing_location`, `primary_business_entity` | **1** — `Your Clinic Name Here`, `primary_business_entity=0` |
| **User ↔ facility** | `users.facility_id` scalar; `users_facility` many-to-many | scalar set for `admin` only; `users_facility` **0 rows** |
| **Per-facility provider IDs** | `facility_user_ids` | **0 rows** |
| **Billing facility** | `users.billing_facility_id`, `facility.billing_location` | all 0 |
| **Warehouse** | `product_warehouse`, `users.default_warehouse` | unused (D09 disabled) |
| **Squad (patient cohort)** | ACO section `squads` | section exists with **no objects** — unusable |

### 21.2 This is OpenEMR multi-site, not SaaS tenant isolation

The distinction matters commercially and must not be blurred.

| Property | OpenEMR multi-site (what exists) | True SaaS tenancy (what does not) |
|---|---|---|
| Data isolation | Separate database per site | ✓ equivalent |
| Tenant provisioning | **Manual** filesystem + database creation per customer | Automated API |
| Tenant registry | **None** — no `tenant`/`tenants`/`organization` table among 283 | Required |
| Row-level tenant discriminator | **None** | Common |
| Process isolation | **Shared** PHP process, session store and `globals` bootstrap | Per-tenant or pooled with isolation |
| Per-tenant key management | **None** | Required |
| Tenant-aware routing / onboarding / metering / billing | **None** | Required |

Negative evidence: repo-wide search for `multi.?tenan` yields **one** hit — a
code comment in `AccessTokenRepository.php:94` about OAuth token scoping.
`\btenant\b` yields **zero** other hits. No tenant table exists.
Recorded as **GAP-0043 (Missing)**.

---

## 22. Localisation / Internationalisation

### 22.1 Languages — live database state

`lang_languages` defines **59 language entries**; `lang_definitions` holds
**237,509 rows across 47 languages**; `lang_constants` holds **13,234**
translatable constants.

Four languages are flagged right-to-left (`lang_is_rtl = 1`): **Arabic (22)**,
Hebrew (7), Persian (37), Urdu (51).

Best-covered languages by definition count: Ukrainian 12,382 · Romanian 12,376
· Persian 11,933 · Portuguese (Brazilian) 11,753 · Portuguese (European/
Angolan) 11,747 · Dutch 8,350 · Greek 8,279 · Hebrew 8,126 · Armenian 6,898 ·
Marathi 6,837 · Spanish (Latin American) 6,870 · Italian 6,758 ·
**Arabic 6,290**.

### 22.2 Arabic — the one genuine localisation asset

| Property | Finding |
|---|---|
| Loaded in this database | **Yes** — `lang_id 22`, `lang_code ar`, `lang_is_rtl = 1` |
| Coverage | **6,290 of 13,234 constants = 47.5 %** |
| Quality | **Human-quality, spot-verified live**: `Patient → مريض`, `Calendar → التقويم`, `Appointment → موعد`, `Billing → الفاتورة`, `Male → ذكر`, `Female → أنثى`. Not machine placeholders |
| Selectable at login | Yes — `language_menu_showall = 1` |
| Currently default | No — `language_default = English (Standard)` |
| Frontend coverage | The i18next frontend reads the same `lang_*` tables (`main.php:335-349`), so no separate catalogue is needed |

**What Arabic coverage does *not* include** — and this is the material
qualification: the 47.5 % covers **UI chrome only**. Not translated are
`list_options` (185 language rows, 848 provider specialties, 213 remit codes),
`layout_options` field labels, and ICD/CPT/SNOMED code descriptions. The
visible gap to an Arabic-speaking user is therefore considerably larger than
47.5 % suggests.

### 22.3 RTL support — real but shallow

Present: **13 prebuilt RTL stylesheets** in `public/themes/`
(`rtl_style_light/dark/solar/manila/forest_green/cobalt_blue/pdf.css` plus 6
`rtl_compact_style_*` variants); a build pipeline (`webpack.themes.js`,
`sass-bsimport-loader.js`); a runtime direction flag set from
`lang_languages.lang_is_rtl` via `getLanguageDir()` and applied at
`library/auth.inc.php:45`; and consumers in the tab shell
(`TabsWrapper.php:72`), the calendar, the portal, and TCPDF output
(`portal_custom_report.php:75-76` `SetDirectionality('rtl')`).

Not present: consistent RTL treatment across legacy screens. Roughly **20
places** in application code consume the direction flag. Most of
`interface/patient_file/`, `interface/reports/` and `interface/billing/`
relies on hard-coded `text-align:left`, float layouts and `<table>` markup that
the RTL stylesheet does not fully invert. **Expect substantial per-screen RTL
remediation.**

Two related gaps: the RTL Bootstrap dependency is a pinned single-commit zip of
an unmaintained third-party GitHub fork (`package.json:113`), and CKEditor is
**never configured for Arabic or RTL** — `@ckeditor/ckeditor5-language` is
installed but the OpenEMR CKEditor configs
(`library/js/nncustom_config.js:198`, `limitedcustom_config.js:259`) contain
zero `language`/`direction`/`rtl` settings.

### 22.4 Other locale dimensions

| Dimension | State | Limitation |
|---|---|---|
| Date format | `date_display_format=0` (ISO `YYYY-MM-DD`) | **Only 3 options** (ISO, MM/DD/YYYY, DD/MM/YYYY). No format string, no locale derivation, no per-user override |
| Time format | `time_display_format=0` (24-hour) | — |
| Time zone | `gbl_time_zone` **empty** | Must be set; `bootstrap.php:30` defaults to UTC |
| Currency | Symbol/decimals/separators configurable; `$`, 2, `.`, `,` | **Display only.** No ISO 4217 code field, no multi-currency, **no currency column on `billing`, `ar_activity`, `payments`, `prices` or `fee_schedule`** |
| Units of measure | `units_of_measurement=1` (US primary + metric); metric-only available | Full metric support exists |
| Phone | `phone_country_code=1` (US/NANP); phone fields are free-text `varchar` | Formatting/validation helpers assume NANP; **`libphonenumber` = 0 hits** |
| Address | Schema is generic `varchar` | **`list_options.state` is hard-seeded with 52 US states/DC/PR**; `country` has 1 row; no structured international address |
| PDF | `rtl_style_pdf.css` ships | **No Arabic-shaping font in the tree** — `git ls-files` finds no `amiri*`, `noto*naskh*`, `noto*sans*arabic*` or `dejavu*` |

---

## 23. Saudi-Market Readiness

### 23.1 Method

A full-repository, case-insensitive search was run (excluding only `vendor/`,
`node_modules/`, `.git/`) for: `nphies`, `nphies.sa`, `zatca`, `fatoora`,
`SFDA`, `CCHI`, `council of health insurance`, `umalqura`, `ummalqura`,
`hijri`, `islamic`, `iqama`, `saudi`, `KSA`, `ACHI`, `SBS`, `e-invoic`, `VAT`,
`national id`. The `patient_data` column list was inspected directly
(136 columns), as were `list_options` seeds and the `code_types` registry.

### 23.2 The result

**Every single hit landed in exactly three non-product locations:**

1. `Locked Desicions/*.md` — this project's own planning documents
2. `tools/discovery/openemr-decision-evidence/*.py` — this project's own
   discovery tooling, which contains the search keywords *as data*
3. `docs/discovery/…` and `docs/00-discovery/…` — this project's own prior
   evidence write-ups

**Zero hits in `src/`, `library/`, `interface/`, `portal/`, `templates/`,
`sql/`, `sites/`, `apis/`, `controllers/`, `custom/`, `ccdaservice/`,
`config/`, `cli/` or `public/`.**

> **Methodological warning for anyone repeating this scan:** because this
> repository contains its own Saudi-focused planning corpus, a naïve grep for
> `nphies`/`zatca`/`hijri` **will always return hits**. Any future scan must
> exclude `docs/`, `tools/discovery/` and `Locked Desicions/` or it will
> produce false positives.

### 23.3 Saudi readiness register

| GAP | Capability | Verdict | Evidence |
|---|---|---|---|
| GAP-0046 | NPHIES connectivity | **Missing** | 0 product hits. Billing is 100 % US X12 5010. The only reusable foundation is the FHIR R4 server (§18.3) |
| GAP-0047 | CHI / CCHI (Council of Health Insurance) | **Missing** | 0 hits |
| GAP-0048 | Saudi National ID / Iqama identifier | **Missing** | `patient_data` identifier columns are `ss` (US SSN), `drivers_license`, `pubpid`, `pid`, `uuid`. **No 10-digit Saudi ID field, no 1/2 prefix rule, no checksum, no identifier-type discriminator** |
| GAP-0049 | Saudi payer identifiers | **Missing** | Payer identity is modelled as X12/CMS payer IDs (`insurance_companies.cms_id`, `eligibility_id`). No CHI-licensed-insurer registry |
| GAP-0050 | Saudi provider / facility identifiers | **Missing** | Provider identity is **US NPI + NUCC taxonomy + EIN**. No MOH facility licence, no SCFHS practitioner licence |
| GAP-0051 | SFDA drug registration | **Missing** | Drug identity is RxNorm/NDC. 0 SFDA hits |
| GAP-0052 | ZATCA / Fatoora e-invoicing | **Missing** | 0 hits for `zatca`/`fatoora`. All 69 `e-invoic` matches are the substring inside `updateInvoiceRefNumber()`. No UBL 2.1, no cryptographic stamp, no XAdES, no TLV QR, no invoice hash chain |
| GAP-0053 | Saudi VAT (15 %) | **Missing** | The only `VAT` hits are the ISO country code for Vatican City. **`billing`, `ar_activity`, `payments`, `prices` and `fee_schedule` have no tax rate, tax amount or tax category column.** Tax rates exist only as a `list_options` registry (`taxrate`) and a colon-list on `codes.taxrates` |
| GAP-0054 | ACHI / ACCI / ICD-10-AM coding | **Missing** | 0 real `ACHI` hits (all matches were inside "machine"/"achieve"); all 747 `ACCI` matches are inside "vaccine". `code_types` supports ICD-10-CM/PCS, CPT4, HCPCS, SNOMED, LOINC, RxNorm, CVX |
| GAP-0055 | SBS (Saudi Billing System) | **Missing** | 0 hits |
| GAP-0056 | Saudi FHIR IG / NPHIES profiles | **Missing** | The FHIR server is profiled to **US Core** for ONC certification (Inferno tests present). No `sa-core`, no NPHIES `StructureDefinition`, no Saudi ValueSets |
| GAP-0057 | Arabic patient name structure | **Missing** | Name columns are Western given/middle/family (`fname`, `mname`, `lname`, `suffix`, `preferred_name`, `birth_*`). **No second/third (father/grandfather) name decomposition, and no parallel Arabic-script name set** — a NPHIES/MOH expectation with nowhere to live except generic `usertext` slots |
| GAP-0058 | Hijri calendar / Hijri DOB | **Missing** | 0 hits for `hijri`/`islamic`/`umalqura`. `patient_data.DOB` is a single Gregorian `date`. `library/date_functions.php:43-54` is hard-Gregorian |
| GAP-0059 | Saudi national address structure | **Missing** | `list_options.state` is seeded with 52 US states; `country` has 1 row. No building number / additional number / district / short code |

### 23.4 What *is* usable as a starting point

| Asset | Status | Value toward Saudi readiness |
|---|---|---|
| Arabic UI translation (47.5 %, loaded, human-quality) | **Active** | Real and substantial. Needs completion + Saudi terminology review + `list_options`/`layout_options` translation |
| RTL flag, 13 RTL themes, RTL PDF stylesheet | **Active** | Scaffold present; per-screen remediation required |
| FHIR R4 server with SMART, OAuth2, Bulk Export | **Disabled** (flag) | The only credible technical foundation for a future NPHIES adapter |
| `ext-intl` is a hard composer requirement | Present | A Hijri display layer via `IntlDateFormatter` with `@calendar=islamic-umalqura` has its dependency **already satisfied** — it was simply never built |
| Configurable currency symbol/separators | **Active** | Displaying `SAR`/`ر.س` is a one-field change; tax and multi-currency are schema work |
| Configurable timezone (`gbl_time_zone`) | **Active** (unset) | `Asia/Riyadh` is a one-setting change |
| Module framework + ~90 events + REST route/scope extension events | **Active** | A NPHIES module could register routes and scopes without patching core — **but not a billing generator**: `BillingProcessor::buildProcessingTaskFromPost()` is a hard-coded if/elseif ladder (`BillingProcessor.php:161-192`) with **no factory, registry or event dispatch anywhere in `src/Billing/`** |

### 23.5 Saudi readiness verdict

**Verified Saudi-specific capabilities: 0. Verified Saudi-market gaps: 14
(GAP-0046…0059).**

Arabic language and RTL scaffolding are genuine and worth citing. Everything
else Saudi-specific — NPHIES, CCHI, ZATCA, VAT, Hijri, Saudi identifiers, SBS,
ACHI, Saudi FHIR profiles, Arabic name structure, Saudi addressing — is
greenfield development, not configuration.

---

## 24. Hidden / Non-Menu / Orphaned Capabilities

Discovered by normalising all 133 distinct menu URLs across the 7 menu JSONs
against 228 PHP files in six interface directories, then cross-referencing
every non-menu file against a 5,762-file reference scan.

### 24.1 Truly dead — zero references anywhere in executable code (3)

| File | Assessment |
|---|---|
| `interface/reports/amc_tracking.php` | A functional AMC numerator data-entry screen with a proper `patients\|med` ACL, unreachable from any UI |
| `interface/patient_file/summary/dashboard_header_simple_return.php` | Dead dashboard fragment |
| `interface/super/layout_service_codes.php` | Dead layout/service-code linkage screen |

### 24.2 Reachable but not on any menu (notable)

| Capability | Route | How it is actually reached |
|---|---|---|
| **AMC Full Report** (RPT-0054) | `reports/amc_full_report.php` | Only from a Twig template; **no ACL check anywhere in the file** — see §20.6 #3 |
| External Data | `reports/external_data.php` | Patient-menu nav and `pat_ledger.php:451`; no ACL check |
| Own password change | `usergroup/user_info.php` | User dropdown → Change Password |
| Own MFA management | `usergroup/mfa_registrations.php` | User dropdown → MFA Management |
| Encounter **review** mode | `encounter/forms.php?review_id=` in tab `rev` | `tabs_view_model.js:216-224` — a distinct tab target that appears in no menu |
| User preferences | `super/edit_globals.php?mode=user` | User dropdown → Settings |
| Portal audits / payments views | `portal/patient/onsiteactivityviews` | Portal badges in the shell |
| Fax/SMS message UI | `oe-module-faxsms/messageUI.php?type=sms\|fax` | Shell badges — module uninstalled, so inert |
| Arbitrary tabs at login | any path in `list_options.default_open_tabs` | Filtered by `DefaultTabsFilter::filter()` + `SafeIncludeResolver` |
| Full-page app replacement | `$_POST['appChoice']` → `$_SESSION['app1']` | `main_screen.php:501-503` — replaces the entire shell with an iframe |

### 24.3 Hidden menu role

**`chart_review`** — a complete 2-item menu role (Finder + Patient Report)
that exists as a shipped JSON file but is **not offered in the role picker**
(`MainMenuRole.php:86-88`). Selectable only by writing
`users.main_menu_role = 'chart_review'` directly in the database. This is a
genuine, useful capability — a read-only reviewer persona — that no
administrator can discover through the UI.

### 24.4 Dormant code inventory (summary)

| Category | Count | Catalogued as |
|---|---|---|
| Clinical forms on disk, unregistered | 16 | CAP-0071…0086 |
| Modules on disk, unregistered | 11 | CAP-0206, CAP-0261…0270 |
| Report screens with no menu link | 3 | §24.1, §24.2 |
| Menu roles not offered in the picker | 1 | §24.3 |
| Legacy PostNuke module tables still populated | 2 tables, 20 rows | §8.6 |
| Schema orphans (no reader anywhere) | `fee_schedule` (0 consumers in `src/`, `library/`, `interface/`); `x12_partners.processing_format`, `x12_token_endpoint`, `x12_claim_status_endpoint`, `x12_attachment_endpoint`, `x12_client_id`, `x12_client_secret` (editable in the admin UI, **read by nothing**) | §31 |

**None of this is advertised.** Dormant code is catalogued as `Uninstalled`,
and schema orphans are recorded as limitations, not capabilities.

---

## 25. Current vs Optional Capability Matrix

The mandatory separation. These five categories must never be merged into a
single "Features" list.

| Category | Count | What it means for a customer | What must be said alongside it |
|---|---|---|---|
| **Active — usable today** | **177** | Working now on this installation | "…once configured with your data" — there is no data (§28) |
| **Disabled — switched off** | **47** | Fully built; needs a configuration change, no development | Name the flag. 35 of the 47 come from just 9 flags |
| **Uninstalled — optional code present** | **27** | On disk; needs registration through the Module Manager, and often an external service too | 16 are clinical forms (a 2-minute install each); 11 are modules |
| **Requires Integration — external dependency** | **18** | Built, but useless without a third-party service, contract or credential | Name the vendor and say the contract is the customer's to obtain |
| **Missing — searched, absent** | **60** | Not in the product. 1 in-catalogue (MFA enforcement) + 59 market-comparison gaps | Never present as a roadmap commitment without engineering sign-off |

### 25.1 Effort ladder to move a capability up

| From | To | Typical effort |
|---|---|---|
| Disabled → Active | flip one global in Admin → Config | **Minutes** |
| Uninstalled (form) → Active | Admin → Forms → register + install SQL | **Minutes** |
| Uninstalled (module) → Active | Module Manager → Register → Install SQL → Install ACL → Enable → Configure | **Hours** |
| Requires Integration → Active | Commercial contract + credentials + endpoint configuration + testing | **Weeks to months**, mostly non-engineering |
| Missing → Active | New development | **Months**; for the Saudi set, a programme |

---

## 26. Market-Comparison Coverage Matrix

Every row was searched with synonym expansion across the whole repository
(excluding `vendor/`, `node_modules/`, `.git/`, minified JS and source maps),
plus DB schema sweeps and inspection of the live feature registries
(`globals`, `modules`, `registry`, `gacl_aco`, `list_options`,
`standard.json`).

**Disambiguation rule enforced:** `src/FHIR/R4/**` contains machine-generated
FHIR R4 model classes for *every* FHIR resource type — including
`FHIRMedicationAdministration`, `FHIRImagingStudy` and `FHIRNutritionOrder`.
Presence there is **not** a capability. The same applies to
`ccdaservice/oe-blue-button-meta/**`, the CDA2 XSDs, and
`library/edihistory/codes/**` (X12 lookup tables). Several apparent
capabilities were rejected on this basis.

### 26.1 Group 1 — Care settings (14 gaps)

| GAP | Capability | Search terms | Found | Verdict |
|---|---|---|---|---|
| — | Ambulatory / OPD | `encounter`, menu extract, `registry` | The entire product | **Implemented / Active** |
| GAP-0001 | Emergency Department / triage | `triage`, `emergency department`, `\bED\b`, `acuity`, `ESI`, `ambulance` | `triage` = **2 hits repo-wide**, both prose in a module user guide. "Emergency Department" = CMS Place-of-Service label 23 (`BillingUtilities.php:936-937`) and an HL7 `encounter-types` list value | **Missing** |
| GAP-0002 | Inpatient / IPD | `inpatient`, `\bIPD\b`, `hospitalization`, `length of stay` | 246 `inpatient` hits, **all** POS codes 21/51/61, generated FHIR classes, SQL list seeds, UB-04 code tables. `\bIPD\b` = 0 | **Missing** |
| GAP-0003 | ADT (admission/discharge/transfer) workflow | `\bADT\b`, `admission`, `discharge`, `transfer` | `ADT` = 21 hits, all **outbound HL7 `ADT^A01`/`A04` for syndromic surveillance**. `discharge` = a single `discharge-disposition` dropdown for claims | **Missing** (message emission ≠ workflow) |
| GAP-0004 | Ward management | `\bward\b`, `ward management` | 8 hits, all lexical: UB-04 revenue code 0150 "Room & Board (Ward)", condition code 37, `next_of_kin_relationship` value `Ward of court` | **Missing** |
| GAP-0005 | Bed management | `bed management`, `bed_`, `\bbed\b`, `SHOW TABLES LIKE '%bed%'` | **0 tables, 0 code hits** | **Missing** |
| GAP-0006 | Bed census / occupancy | `census`, `occupancy`, `bed board` | 3 hits, all `adxp.censusTract` (a US postal address part) in a CDA XSD | **Missing** |
| GAP-0007 | Nursing documentation | `nursing`, `nurse note`, `nursing assessment` | 99 hits, all POS 31/32 "Skilled Nursing Facility" and CPT nursing-facility E/M ranges | **Missing** |
| GAP-0008 | Nursing task / shift task list | `nursing task`, `task`, `care plan` | Care Plan form exists (C-CDA aligned, CAP-0041); `form_taskman` belongs to the eye module | **Missing** (no task assignment/acknowledgement engine) |
| GAP-0009 | eMAR / medication administration record | `\bemar\b`, `medication administration`, `MAR`, `administered` | `emar` = **0**. `medication administration` = 4 hits, all the generated `FHIRMedicationAdministration` class and an enum docblock. **No administration table** — meds exist only as `lists`, `prescriptions` and `drug_sales` | **Missing** |
| GAP-0010 | ICU / critical care | `\bICU\b`, `critical care`, `intensive care` | 216 `ICU` hits, all inside eCQM measure titles and CPT critical-care code ranges (99291/99292) | **Missing** |
| GAP-0011 | Operating theatre / OR management | `operating (room\|theat)`, `theatre`, `OR schedul` | 21 hits, **all** UB-04 revenue codes 0360-0369 and 0975 | **Missing** |
| GAP-0012 | Surgical scheduling / case booking | `surger`, `surgical schedul`, `case booking` | `SurgeryService.php` opened and read: it is CRUD over `lists` where `type='surgery'` — the patient's **past-surgery history**, exposed as FHIR `Procedure`. No case, no booking, no theatre slot | **Missing** |
| GAP-0013 | Anaesthesia record | `anesthes*`, `anaesthes*`, `perioperative` | 74+11+7 hits: anaesthesia **billing** CPT/modifier handling, `X125010837I.php:1060`, and one "Anesthesia type" dropdown in the IPPF layout | **Missing** (no time-based record, vitals capture or drug log) |
| GAP-0014 | Day surgery / ambulatory surgery centre | `day surgery`, `ambulatory surgery` | `day surgery` = **0**; "Ambulatory Surgery" = UB-04 revenue codes 0490/0499 | **Missing** |

### 26.2 Group 2 — Ancillary services (8 gaps)

| GAP | Capability | Found | Verdict |
|---|---|---|---|
| — | Pharmacy dispensing (in-clinic) | `interface/drugs/` — lot tracking, expiry, destruction, sales-to-encounter posting, 3 reports, 8 inventory ACOs | **Implemented / Disabled** (`inhouse_pharmacy=0`) — CAP-0113…0118 |
| — | Drug inventory with multi-warehouse | `drug_inventory`, `product_warehouse`, `gbl_fac_warehouse_restrictions` | **Implemented / Disabled** |
| GAP-0015 | Full Pharmacy Information System | `src/Pharmacy/Services/` contains exactly **one** file: `ImportPharmacies.php` (imports an external retail pharmacy directory). `pharmacies` = external pharmacies for eRx routing | **Missing** — no order verification, compounding, unit dose, IV admixture or pharmacist queue |
| — | Formulary | `templates/prescription/general_edit.html.twig:133-138` — a **display-only flag returned by the eRx vendor**; no local formulary table, tier logic or restriction rules | **Requires Integration** (INT-0009) |
| — | Drug–drug interaction checking | `drug_drug` in `C_Prescription.class.php:190,242` — same vendor-returned pattern | **Requires Integration** — CAP-0070 |
| GAP-0016 | Laboratory Information System | Order/result interface exists (CAP-0092…0105) with specimen metadata. **`worklist` = 0 hits, `analyzer` = 0 hits.** No accessioning, instrument interface, QC, result validation queue or specimen routing | **Missing** (lab *interface* ≠ LIS) |
| GAP-0017 | Radiology Information System | No RIS files. "Radiology" only in UB-04 revenue codes and CPT ranges. `FHIRImagingStudy` is a generated model with **no backing service** | **Missing** |
| GAP-0018 | PACS integration | `PACS` = 0 in code. `orthanc`/`dcm4che`/`dicomweb` = **0 hits**. `wado` = 5 hits, all inside the vendored DWV bundle. No AE-title or endpoint configuration anywhere | **Missing** |
| — | DICOM viewing | `library/dicom_frame.php` + DWV viewer, menu-linked | **Implemented / Active** — CAP-0205 (viewer only) |
| GAP-0019 | Blood bank | `blood bank` = 6 hits, all provider-specialty/taxonomy strings and an X12 service-type code. `crossmatch`/`transfusion`/donor unit = 0 | **Missing** |
| GAP-0020 | Dental charting / odontogram | **`odontogram` = 0 hits repo-wide.** `dental chart` = 0. No dental form among 35 form dirs, no dental table. Only a `dental` issue-list type | **Missing** |
| GAP-0021 | Physiotherapy / rehabilitation | `physiotherap` = **0**. `rehab` only in questionnaire narrative text and CPT descriptions. `interface/forms/physical_exam` is an *examination* form | **Missing** |
| GAP-0022 | Dietary / nutrition | Hits only in the generated `FHIRNutritionOrderOralDiet` class — no service, no route. No diet order, meal plan or nutrition assessment | **Missing** |
| — | Medical records / HIM coding | `code_types`, `codes`, ICD-10-CM/PCS tables, `chart_tracker`, coding ACLs, external data loads | **Implemented / Active** (no DRG grouper, no deficiency management) |

### 26.3 Group 3 — Access and engagement (5 gaps)

| GAP | Capability | Found | Verdict |
|---|---|---|---|
| — | Patient portal | `portal/` complete app; 8 `onsite_*` tables; ACL `patientportal\|portal` | **Implemented / Disabled** — CAP-0173…0180 |
| GAP-0023 | Mobile patient app | `cordova`/`capacitor`/`ionic` = **0 hits**; `react-native` = 2 hits in an upstream README | **Missing** |
| GAP-0024 | Mobile clinician app | Same search, same result | **Missing** |
| GAP-0025 | Queue management / token display | Patient Tracker/Flow Board is live (CAP-0011), but **`queue management` = 0 and `token display` = 0** — no numbered-token issuance, no public display screen | **Missing** |
| GAP-0026 | Call centre / telephony | `call cent` = **0 hits**. Nearest is RingCentral voice inside the uninstalled fax/SMS module | **Missing** |
| GAP-0027 | CRM | `\bCRM\b` = **0 hits** across `src library interface portal templates controllers custom sql config apis cli`. `misc_address_book` is a contact directory | **Missing** |
| — | Patient feedback / survey | `satisfaction survey`/`patient feedback` = 0, **but** the LForms/FHIR Questionnaire engine (CAP-0046) and PROMIS integration could carry an instrument | **Requires Integration** |
| — | Electronic consent | `document_templates`, `documents_legal_*`, `onsite_documents`, `onsite_signatures`, `portal/sign/` | **Implemented / Disabled** on the patient side (portal off); staff-side signing Active |
| — | Electronic signature | `library/ESign/` full framework + `esign_signatures` | **Implemented / Active** — CAP-0048 |
| — | Patient education | `education.php` → MedlinePlus Connect | **Implemented / Active** — CAP-0181 |
| — | SMS / Email / Fax | Core gateway + module vendors | **Requires Integration / Disabled** — CAP-0185…0188 |
| — | Patient messaging | `pnotes` staff-side Active; `onsite_mail`/`onsite_messages` portal-side Disabled | **Split** |
| — | Appointment reminders | `medex_recalls`, `patient_reminders`, `batch_reminders.php` | **Active** (generation) / **Requires Integration** (delivery) |

### 26.4 Group 4 — Revenue and back office (12 gaps)

| GAP | Capability | Found | Verdict |
|---|---|---|---|
| — | Insurance eligibility (270/271) | `EDI270.php` (1,162 ln), `edi_270.php`, `edi_271.php`, `eligibility_verification` | **Requires Integration / Disabled** — CAP-0147, 0148 |
| — | Prior authorisation | Number capture on the claim (CAP-0150); 278 **response viewer** only | **Partial** — no 278 request generator |
| — | Claims | 837P/837I, CMS-1500, UB-04, full Billing Manager | **Implemented / Active** (837I & UB-04 Disabled) |
| GAP-0028 | Denial management | **`denial management` = 0 hits.** Denials surface only as `msp_remit_codes` (213), `adjreason` (18) and `payment_adjustment_code` (5) during posting, plus 277/997 viewing. **No denial worklist, appeal tracking or root-cause analytics** | **Missing** |
| — | ERA / EOB | `ParseERA.php`, `sl_eob_*`, `era_payments.php`, `sites/default/era/` | **Implemented / Active** — CAP-0135, 0136 |
| — | Accounts receivable | `ar_activity`, `ar_session`, aging, ledger, collections | **Implemented / Active** — CAP-0139, 0140 |
| GAP-0029 | General Ledger | **`general ledger` = 0 hits** across `src library interface portal templates controllers custom sql config apis cli`. `pat_ledger.php` is a *patient* AR ledger. No `gl_*`/`ledger`/`journal` table among 283. (`globals.gl_name` means "global", not "general ledger") | **Missing** |
| GAP-0030 | Full accounting / ERP | No accounting package. The `sl_*` filenames are vestigial SQL-Ledger naming; `SLEOB.php` writes only `ar_session`/`ar_activity` | **Missing** |
| GAP-0031 | Accounts payable | `accounts payable` = **0 hits** | **Missing** |
| GAP-0032 | Procurement | `procurement` = 0. `interface/forms/requisition/` is a **lab** requisition (and unregistered). `list_options.CTLSupplier` is a drug-supplier lookup | **Missing** |
| GAP-0033 | Purchase orders | `purchase order` = **1 hit**, an X12 reference-qualifier string. A drug lot purchase can be *recorded*; there is no PO document, approval chain or goods receipt | **Missing** |
| GAP-0034 | Warehouse management (WMS) | `product_warehouse` provides named stock locations and inter-location transfers. **No bins, picking, receiving or cycle counts** | **Missing** (as WMS) |
| GAP-0035 | Asset / equipment management | `asset management`/`equipment management` = **0 hits**. `biomedical` = 13 hits, all provider-taxonomy strings | **Missing** |
| GAP-0036 | HR | `human resource` = **0 hits**. `employer_data` is the *patient's* employer. `users` has no HR fields | **Missing** |
| GAP-0037 | Payroll | `payroll` = **0 hits** | **Missing** |
| GAP-0038 | Staff rostering / duty scheduling | `rostering`, `roster`, `duty schedul`, `shift schedul`, `staff schedul` = **0 hits each**. Provider in-office/out-of-office calendar events are the only adjacent concept | **Missing** |
| GAP-0039 | Time and attendance | `time and attendance` = 0, `timesheet` = 0. `form_group_attendance` is **patient** group-therapy attendance; `session_tracker` tracks logins | **Missing** |

### 26.5 Group 5 — Data and platform (6 gaps)

| GAP | Capability | Found | Verdict |
|---|---|---|---|
| GAP-0040 | Analytics / BI | `business intelligence`, `grafana`, `metabase`, `power bi`, `data mart`, `ETL`, `OLAP` = **0 hits each**. 55 static HTML reports; `chart.js` vendored with **no consumer** | **Missing** |
| GAP-0041 | Data warehouse | `data warehouse`, `star schema`, `OLAP` = **0 hits** | **Missing** |
| GAP-0042 | Device / vitals-monitor integration | `src/MedicalDevice/MedicalDevice.php` opened: it parses a **UDI barcode** (GS1/HIBCC/ICCBBA) for implantable-device documentation. `form_vitals` is manual entry. **No monitor feed, no inbound ORU vitals listener, no interface engine** | **Missing** (UDI ≠ device integration) |
| GAP-0043 | True SaaS tenant isolation | `multi.?tenan` = **1 hit**, a code comment about OAuth token scoping. `\btenant\b` = 0 other hits. No tenant/organization table among 283. See §21.2 | **Missing** |
| GAP-0044 | CDS Hooks | `cds.?hooks` = **0 hits**. The native CDR engine (CAP-0063) is not CDS Hooks | **Missing** |
| GAP-0045 | Offsite / cloud document storage | Hook points `documents.remote.storage.location` and `remote.document.retrieve.location` exist with **no listener**; no first-party S3/Azure implementation | **Missing** |
| — | Population health | `CqmPopulation.php`, `AmcPopulation.php`, ~40 `NQF_*` population classes, `patient_list_creation.php` | **Implemented / Active** — CAP-0162 |
| — | Quality measures / CQM | 18 CQM + 42 AMC; QRDA Cat I/III code complete | **Active** / **Requires Integration** — CAP-0168…0170 |
| — | Clinical decision support | Native CDR engine + ONC HTI-1 DSI source-attribute transparency (`dsi_source_attributes`) | **Implemented / Active** — CAP-0063 |
| — | Referral management | `transactions`, `lbt_data`, `print_referral.php`, referrals report | **Implemented / Active** (no closed loop) — CAP-0026 |
| — | Care coordination | Carecoordination module installed and active; `care_teams`, `care_team_member` | **Implemented / Active** — CAP-0201, CAP-0030 |
| — | Audit trail | `log`, `log_comment_encrypt`, tamper report, 4,280 rows | **Implemented / Active, runtime-verified** — CAP-0224 |
| — | MFA | TOTP + U2F implemented, API-path only, 0 enrolments, **cannot be mandated** | **Active (partial)** + **CAP-0218 Missing** |
| — | Break-glass | `breakglass` ARO group + forced logging | **Implemented / Active** (no user assigned) — CAP-0223 |
| — | Data sensitivity levels | 2-tier encounter sensitivity | **Implemented / Active** (encounter-scope only) — CAP-0222 |
| — | Backup / restore | `interface/main/backup.php`, `backuplog.sh` | **Active but broken here** (CFG-0120) — restore is manual/documented, not a UI feature |
| — | Multi-facility / multi-site | `facility` table; `sites/` | **Implemented / Active**, 1 of each provisioned |

### 26.6 Group 6 — Localisation

| Capability | Verdict | Note |
|---|---|---|
| Arabic language support | **Implemented / Active (47.5 %)** | Loaded, human-quality, spot-verified — CAP-0252 |
| RTL layout | **Implemented / Active (shallow)** | 13 themes; ~20 code consumers — CAP-0253 |
| Hijri / Islamic calendar | **Missing** | GAP-0058 (Saudi register) |
| Date/time format | **Active (3 options only)** | CAP-0255 |
| Currency configuration | **Active (display only)** | CAP-0254 |
| Units of measure | **Active** | CAP-0256 |
| International phone | **Active (US-default, weak validation)** | `libphonenumber` = 0 hits |
| International address | **Requires product work** | `state` list hard-seeded with 52 US states |

### 26.7 Group 7 — Saudi market

All 14 rows are in §23.3 as **GAP-0046…GAP-0059**, every one **Missing**.

### 26.8 Coverage roll-up

| Verdict | Count |
|---|---|
| Implemented / Active | 24 comparison areas |
| Implemented / Disabled | 7 |
| Available module / Uninstalled | 4 |
| Requires Integration | 6 |
| **Missing** | **59** (GAP-0001…GAP-0059) |

---

## 27. Marketing-Safe Claims

### 27.1 Language rules for anyone writing from this document

**Never use** — unless a specific, cited capability independently proves the
exact statement: *best · leading · complete · comprehensive · enterprise-grade ·
AI-powered · seamless · fully integrated · Saudi compliant · HIPAA compliant ·
NPHIES compliant · certified · hospital-grade · end-to-end*.

**Never state or imply:**
- That a capability is available when its status is Disabled, Uninstalled,
  Requires Integration or Missing — without saying so in the same sentence.
- That a technical control equals regulatory compliance. This system implements
  audit logging, access control and encryption controls. It has not been
  certified by this audit against any regulation.
- That anything in this product is proprietary or differentiated. It is
  unmodified upstream OpenEMR (§27.5).
- Any Saudi-market capability. There are none.
- The `admin` credential, in any material, ever.

### 27.2 Approved claims

Each claim is traceable to capability IDs. **Qualification** text is not
optional — it must travel with the claim.

| Claim ID | Approved marketing claim | Supporting caps | Status | Conf | Required qualification | Prohibited stronger claim |
|---|---|---|---|---|---|---|
| CLM-0001 | "Integrated appointment scheduling with provider and facility calendars, recurring appointments and holiday management." | CAP-0006…0010 | Active | High | — | "AI-optimised scheduling"; "resource and theatre scheduling" |
| CLM-0002 | "Live patient flow tracking from arrival to checkout." | CAP-0011 | Active | High | It is an in-office status board | "Queue management with token display" (GAP-0025) |
| CLM-0003 | "Configurable patient registration and demographics, with duplicate detection and record merge." | CAP-0001, 0004, 0017, 0018 | Active | High | — | "Master patient index"; "enterprise MPI" |
| CLM-0004 | "Eighteen ready-to-use clinical forms covering SOAP, vitals with growth charts, review of systems, care plans, clinical notes and a full ophthalmology examination." | CAP-0035…0046, FORM-0001…0018 | Active | High | State the count; a further 16 forms ship uninstalled | "Specialty EMR for every discipline" |
| CLM-0005 | "Build your own clinical forms without code using the layout-based form engine." | CAP-0047 | Active | High | **Zero layout-based forms are configured out of the box** | "Hundreds of pre-built specialty templates" |
| CLM-0006 | "Structured problem, allergy, medication and immunisation lists with coded terminology." | CAP-0051…0054 | Active | High | — | "Automatic reconciliation" |
| CLM-0007 | "USCDI-aligned social determinants of health screening." | CAP-0056 | Active | High | — | "Population SDOH analytics" |
| CLM-0008 | "A rule-based clinical decision support engine with configurable alerts, reminders and care-gap detection." | CAP-0063…0069 | Active | High | 80 rules ship; **all shipped rules have their alert flags off** and must be activated | "AI clinical decision support"; "CDS Hooks support" (GAP-0044) |
| CLM-0009 | "Allergy checking against the active medication list." | CAP-0067 | Active | High | **Exact name match only — not an ingredient-level or interaction engine** | "Drug interaction checking" (that is CAP-0070, Requires Integration) |
| CLM-0010 | "Electronic signature with record locking and a signature audit log." | CAP-0048, 0049 | Active | High | — | "Legally binding digital signatures" |
| CLM-0011 | "Lab and procedure ordering with HL7 v2 order generation, an orderable-test compendium and a results review and sign-off queue." | CAP-0092…0105 | Active + RI | High | **Transmission and result receipt require a lab interface to be established** | "Laboratory Information System" (GAP-0016) |
| CLM-0012 | "Prescription recording and printing, with an external e-prescribing pathway available." | CAP-0106…0108, 0110 | Active + RI | High | E-prescribing **requires a vendor contract**; it is not enabled | "Electronic prescribing included"; "EPCS included" |
| CLM-0013 | "In-clinic dispensary with lot tracking, expiry, dispensing to the encounter, inter-location transfers and destruction reporting." | CAP-0113…0118 | **Disabled** | High | **Must say: available as an optional module, switched off by default** | "Pharmacy Information System" (GAP-0015) |
| CLM-0014 | "Charge capture through a configurable fee sheet, with service and diagnosis coding, price levels and discounting." | CAP-0119…0124 | Active | High | Price levels ship with a single level and an empty price table | "Automated coding"; "coding compliance engine" |
| CLM-0015 | "Professional claim production in X12 5010 837P and printed CMS-1500 formats, with batch management, validation and control-number handling." | CAP-0126…0132 | Active | High | **US claim formats only.** Institutional (837I/UB-04) ships disabled | "Global claims support"; "NPHIES claims" (GAP-0046) |
| CLM-0016 | "Automated remittance posting from X12 835 electronic remittance advice, plus manual EOB entry, batch payments and coordination of benefits to secondary payers." | CAP-0135…0138, 0146 | Active | High | Requires a clearinghouse relationship to receive the 835 | "FHIR claims and ExplanationOfBenefit" — **these do not exist**, only generated model classes (§18.3) |
| CLM-0017 | "Accounts receivable with configurable ageing buckets, patient ledger and collections reporting." | CAP-0139…0142 | Active | High | Aging buckets are user-defined (default 3 × 30 days) | "General ledger"; "accounting"; "ERP" (GAP-0029, GAP-0030) |
| CLM-0018 | "Insurance eligibility enquiry using X12 270/271, including a real-time CAQH CORE pathway." | CAP-0147…0149 | RI / Disabled | High | **Requires a trading-partner record and a payer relationship**; real-time is switched off | "Instant eligibility verification" |
| CLM-0019 | "Fifty-five built-in reports across clinical, operational, financial, insurance, inventory, quality and audit domains, with CSV and print output." | CAP-0161…0172, RPT-0001…0055 | Active (44) | High | 10 are disabled with their parent feature; **there is no BI or dashboard layer** (GAP-0040) | "Analytics platform"; "real-time dashboards" |
| CLM-0020 | "Clinical quality measure and automated measure calculation reporting." | CAP-0168, 0169 | Active | High | **Measures are 2011/2014 Meaningful-Use era.** The certified QRDA export path has no measure bundle loaded | "Current-year eCQM certified reporting" |
| CLM-0021 | "A FHIR R4 US Core API covering 35 resources, with SMART on FHIR app launch, OAuth2 and bulk data export. Profile support spans US Core 3.1.1, 7.0.0 and 8.0.0." | CAP-0194…0199 | **Disabled** | High | **Must say: switched off by default; enable and register OAuth clients.** Write support covers 3 resources only; no PATCH/DELETE. **Conformance is not Inferno-tested** — say "implements", never "certified" | "Open API platform"; "FHIR billing"; "NPHIES-ready FHIR"; "US Core certified" |
| CLM-0022 | "A REST API with 98 endpoints across clinical and administrative resources." | CAP-0193 | **Disabled** | High | Switched off by default. **No billing/claims/payments endpoints exist** | "Complete API coverage" |
| CLM-0023 | "Standards-based document exchange: C-CDA, CCR, HL7 v2.5.1 `VXU^V04` immunisation messages, HL7 v2 syndromic surveillance, and DICOM image viewing." | CAP-0201…0205 | Active (**C-CDA operationally blocked**) | High | Each needs a receiving counterparty. **C-CDA cannot currently run — its Node service is not listening on 127.0.0.1:6661.** DICOM is **viewing only, no PACS** (GAP-0018) | "Full interoperability suite"; "PACS integration"; any live C-CDA demo |
| CLM-0024 | "Role-based access control with four permission levels across 65 permission objects, plus data-sensitivity restriction, break-glass emergency access and ownership scoping." | CAP-0221…0223, ROL-0001…0007 | Active | High | Sensitivity applies at **encounter level only** and not to the API | "Field-level security"; "42 CFR Part 2 segmentation" |
| CLM-0025 | "A tamper-evident audit trail covering user activity and database changes, with an integrity-verification report." | CAP-0224 | Active | **Runtime-verified** | It is a hash, **not an HMAC**, and rows are not chained | "Immutable audit log"; "blockchain audit"; "HIPAA compliant" |
| CLM-0026 | "Enforced password policy with configurable length, complexity, expiry, reuse history and account lockout, plus per-IP brute-force protection." | CAP-0209…0213 | Active | High | — | "Multi-factor authentication enforced" — **it cannot be** (CAP-0218) |
| CLM-0027 | "Optional two-factor authentication using authenticator apps or FIDO U2F security keys, applied at sign-in for enrolled users." | CAP-0216, 0217 | Active | High | **Must say: enrolment is per-user and voluntary — an administrator cannot require it, and users who do not enrol sign in with a password alone** | "MFA protects all logins"; "MFA enforced"; "passkey/WebAuthn support" |
| CLM-0028 | "A patient portal for messaging, appointment requests, document exchange, ledger viewing and consent signing." | CAP-0173…0180 | **Disabled** | High | **Must say: included but switched off; requires configuration including a public address and anti-bot keys** | "Patient engagement platform"; "mobile app" (GAP-0023) |
| CLM-0029 | "Operate multiple clinics and multiple independent practices from one deployment." | CAP-0246…0248 | Active | High | Multi-site means a **separate database per site, provisioned manually**. **Not SaaS tenancy** (GAP-0043) | "Multi-tenant SaaS platform"; "automated tenant provisioning" |
| CLM-0030 | "Interface available in 47 languages, including Arabic, with right-to-left layout support." | CAP-0251…0253 | Active | **High, spot-verified** | **Arabic covers 47.5 % of interface strings, chrome only** — picklists, layout labels and code descriptions are untranslated, and RTL needs per-screen review | "Fully localised for Arabic"; "Saudi-ready"; "Arabic EMR" |
| CLM-0031 | "Extensible through a documented module framework with roughly 90 integration events, allowing partners to add screens, menus, dashboard widgets, API routes and OAuth scopes without modifying core." | CAP-0208, 0258…0260 | Active | High | **Billing generators cannot be added by a module** — `BillingProcessor` uses a hard-coded dispatch ladder | "Unlimited extensibility"; "no-code customisation" |
| CLM-0032 | "Group therapy management with group registry, group encounters and attendance." | CAP-0087…0091 | **Disabled** | High | **Must say: optional, switched off by default** | — |

### 27.3 Claims that must NOT be made — with the reason

| Prohibited claim | Why |
|---|---|
| Any inpatient, ward, bed, ADT, eMAR, ICU, theatre or nursing-documentation capability | GAP-0001…0014 — searched exhaustively, absent |
| "Hospital Information System" without qualification | The product is ambulatory-only. If "HIS" is used as the product category, the outpatient scope must be stated in the same breath |
| Laboratory Information System, RIS, PACS, blood bank, dental charting, physiotherapy, dietary | GAP-0016…0022 |
| General ledger, accounting, ERP, accounts payable, procurement, purchase orders, HR, payroll, rostering, asset management | GAP-0029…0039 |
| Analytics, BI, dashboards, data warehouse | GAP-0040, GAP-0041 |
| Denial management | GAP-0028 |
| Device or monitor integration | GAP-0042 — UDI parsing is not device integration |
| Multi-tenant SaaS | GAP-0043 |
| Mobile applications | GAP-0023, GAP-0024 |
| CDS Hooks | GAP-0044 |
| Cloud/offsite document storage | GAP-0045 — hook only, no listener |
| **Anything Saudi**: NPHIES, CCHI, ZATCA, Fatoora, Saudi VAT, Hijri, Iqama/National ID, SFDA, ACHI, SBS, Saudi FHIR profiles, Arabic name structure | GAP-0046…0059 — zero occurrences in product code |
| FHIR Claim / ClaimResponse / ExplanationOfBenefit / eligibility | Generated model classes only; no service, controller or route |
| "Certified", "compliant", "HIPAA", "ONC certified" | This audit certifies capability presence, not regulatory conformance |
| Any proprietary or differentiated feature | Zero fork divergence (§27.5) |

### 27.4 Traceability rule

Every statement in customer-facing material derived from this document must
cite at least one `CAP-*` ID in the source brief. Example:

> Claim: *"Role-based access controls let different staff categories receive
> different operational access."*
> Trace: CLM-0024 → CAP-0221, CAP-0222, CAP-0223 → ROL-0001…0007 →
> `gacl_acl` (19 grants), `gacl_aco` (65 objects) → §13.4, §13.6.

### 27.5 Current fork vs generic OpenEMR

| Question | Answer |
|---|---|
| Does this project contain any code not in upstream OpenEMR? | **No.** `git merge-base --is-ancestor HEAD upstream/master` → true; `git rev-list --count upstream/master..HEAD` → **0** |
| Current-project-specific capabilities | **Zero** |
| How far behind upstream? | **373 commits** (HEAD dated 2026-07-04) |
| What differentiates this deployment? | Configuration and data state only — and both are at installer defaults |
| Anything in this catalogue "not proven in the current project"? | Nothing was admitted on generic-documentation evidence. Where upstream documentation implies a capability this code does not support, the row says Missing with negative evidence |

**Consequence for marketing:** every capability in this catalogue is an
OpenEMR capability, freely verifiable by any competitor or customer against
the open-source project. Positioning must rest on implementation, integration,
localisation, support and hosting — not on the software being unique.

---

## 28. Demo-Readiness Matrix

**Demo readiness is not implied by Active status.** The blocking constraint on
this installation is uniform and absolute: **there is no data and there is only
one user account.**

### 28.1 Universal blockers

| # | Blocker | Effect | Fix effort |
|---|---|---|---|
| B1 | **0 patients, 0 encounters, 0 appointments** | Every clinical and scheduling screen renders empty | Create demo patients — hours |
| B2 | **Only `admin` exists; 5 of 7 roles unpopulated** | No role-based demo is possible; every screen is shown as a superuser | Create 4–5 users — minutes (WF-0012) |
| B3 | **0 insurance companies, 0 X12 partners, empty `prices`, single price level** | No billing or claim demo can complete | Configure payers and fees — hours to days |
| B4 | **Facility is `Your Clinic Name Here`; branding is stock OpenEMR** | Demo visibly shows the open-source product, donation links and all | Brand and configure — hours |
| B5 | **0 drugs, 0 documents, 0 prescriptions** | Dispensary, documents and Rx demos are empty | Seed data — hours |
| B6 | **Background service runner has never executed** (`next_run` stuck in 2021) | Reminders, email queue and UUID backfill do not run | Investigate — GAP-0063 |
| B7 | **Backup will fail** (`mysql_bin_dir` → XAMPP path) | Do not demonstrate Admin → Backup | One config change — GAP-0064 |

### 28.2 Scenario matrix

| Scenario | Domain | Role needed | Role account exists? | Data exists? | Module enabled? | Integration configured? | Capability status | **Demo Ready** | Blocking item |
|---|---|---|---|---|---|---|---|---|---|
| Reception: register → schedule → check in → flow board → front payment | D01 | Front Office | **No** | **No** | Yes | n/a | Active | **No** | B1, B2 |
| Physician: open chart → start encounter → vitals → SOAP → diagnosis | D03/D04 | Physician | **No** | **No** | Yes | n/a | Active | **No** | B1, B2 |
| Physician: place a lab order | D08 | Physician | **No** | **No** | Yes | **No provider** | Active/RI | **No** | B1, B2, INT-0005 |
| Physician: write and print a prescription | D09 | Physician | **No** | **No** | Yes | n/a (print) | Active | **No** | B1, B2 |
| Physician: electronic prescribing | D09 | Physician | **No** | **No** | Yes | **No** | RI | **No** | INT-0009 contract |
| Billing: fee sheet → coding → 837P generation | D10 | Accounting | **No** | **No** | Yes | n/a (file only) | Active | **No** | B1, B2, B3 |
| Billing: submit claim to a clearinghouse | D10 | Accounting | **No** | **No** | Yes | **No** | Disabled | **No** | INT-0011 contract |
| Billing: post an 835 remittance | D10 | Accounting | **No** | **No** | Yes | **No 835 file** | Active | **No** | B3, a sample 835 |
| Billing: collections and aging report | D14 | Accounting | **No** | **No** | Yes | n/a | Active | **No** | B1, B3 |
| Administrator: create users, facilities, ACL | D19 | Administrator | **Yes** | n/a | Yes | n/a | Active | **Yes** | — |
| Administrator: global settings tour (491 settings, 23 tabs) | D19 | Administrator | **Yes** | n/a | Yes | n/a | Active | **Yes** | — |
| Administrator: layout and list editors | D19 | Administrator | **Yes** | n/a | Yes | n/a | Active | **Yes** | — |
| Administrator: Module Manager tour | D19 | Administrator | **Yes** | n/a | Yes | n/a | Active | **Yes** | Opening it will auto-register 3 modules — expected, but know it happens |
| Administrator: clinical rule builder | D05 | Administrator | **Yes** | rules exist (80) | Yes | n/a | Active | **Partial** | Rules can be shown and edited; none fires without patients |
| Administrator: **audit log + tamper verification** | D18 | Administrator | **Yes** | **4,280 rows** | Yes | n/a | Active | **Yes** | The single strongest live demo available today |
| Administrator: IP tracker, background services, diagnostics | D18/D19 | Administrator | **Yes** | present | Yes | n/a | Active | **Yes** | Background services will show two overdue services |
| Administrator: Arabic UI + RTL switch | D20 | any | **Yes** | 6,290 strings | Yes | n/a | Active | **Yes (partial)** | Genuinely demonstrable; expect untranslated picklists and imperfect RTL on legacy screens |
| Patient: portal login and self-service | D15 | Patient | **No** | **No** | **No** | n/a | **Disabled** | **No** | CFG-0091 + portal address + reCAPTCHA |
| API: FHIR resource query from an external app | D17 | API client | **No client** | **No** | n/a | **No** | **Disabled** | **No** | CFG-0002, register an OAuth client, set `site_addr_oath` |
| Telehealth video visit | D21 | any | **No** | **No** | **No** | **No** | **Uninstalled** | **No** | Install CAP-0262 + Comlink contract |
| Inventory / dispensing | D09 | Administrator | **Yes** | **No** | **Disabled** | n/a | **Disabled** | **No** | CFG-0045 + seed drugs |
| Group therapy | D07 | any | **No** | **No** | **Disabled** | n/a | **Disabled** | **No** | CFG-0029 + data |

### 28.3 Summary

| Demo readiness | Count |
|---|---|
| **Yes** | **6** — all administrative or platform scenarios |
| **Partial** | **2** — clinical rule builder, Arabic/RTL |
| **No** | **14** |

**What can honestly be demonstrated today:** the administrative and platform
depth — user and role management, the 65-object permission model, 491
configuration settings, the layout and list editors, the module framework, the
clinical rule builder, the Arabic interface, and a live, verified audit trail
with integrity checking.

**What cannot:** any clinical, scheduling, billing, portal, API or integration
scenario.

**Estimated work to a credible full clinical + billing demo:** create 4–5 role
users (minutes), brand the facility (an hour), seed payers, fee schedules and
20–30 synthetic patients with encounters and charges (1–3 days). No development
is required — this is configuration and data.

---

## 29. Verified Product Strengths

### 29.1 Sales and comparison dimensions

| # | Dimension | Verified strength | Active | Disabled | Uninstalled | Needs integration | Verified gap | Marketing emphasis | Do **not** advertise |
|---|---|---|---|---|---|---|---|---|---|
| 1 | Clinical breadth | 18 active forms + a no-code form engine + 12 structured data domains | 30 | 0 | 16 | 1 | No inpatient anything | Ambulatory depth, USCDI alignment, specialty eye exam | Inpatient, nursing, eMAR |
| 2 | Patient administration | Registration, merge, dedupe, amendments, disclosures, care teams | 11 | 2 | 0 | 0 | — | Records governance | MPI |
| 3 | Front-office operations | Calendar, flow board, recalls, front payments, labels | 19 | 0 | 0 | 0 | Queue/token, call centre, CRM | End-to-end front desk | Queue display, CRM |
| 4 | Revenue cycle | 837P, CMS-1500, 835 posting, AR aging, ledger, batch payments | 20 | 4 | 0 | 0 | Denial mgmt, GL, AP | US RCM depth | ERP, accounting, denials |
| 5 | Insurance | Payer master, X12 partners, COB, 270/271 | 5 | 1 | 0 | 2 | Saudi payers, payer rules | Coordination of benefits | NPHIES, payer-rule engine |
| 6 | Laboratory / orders | Order entry, compendium, HL7 ORM/ORU, review and sign-off | 12 | 0 | 0 | 2 | No LIS, RIS, PACS | Closed-loop ordering *once interfaced* | LIS, RIS, PACS |
| 7 | Prescribing / pharmacy | Rx record and print; full dispensary; pharmacy directory | 4 | 6 | 0 | 3 | No PIS, no DDI engine | Dispensary as an optional module | PIS, interaction checking |
| 8 | Inventory | Lots, expiry, transfers, destruction, 4 reports | 0 | 7 | 0 | 0 | No WMS, PO, procurement | Controlled-substance handling | WMS, procurement |
| 9 | Patient engagement | Complete portal; education lookup; batch outreach | 2 | 8 | 0 | 0 | No mobile app | Portal as an optional module | Mobile app, engagement platform |
| 10 | Reporting | 55 reports across 9 families, CSV/print | 44 | 10 | 0 | 1 | No BI layer | Operational reporting breadth | Analytics, dashboards |
| 11 | Analytics | — | 0 | 0 | 0 | 0 | **GAP-0040, GAP-0041** | *(nothing)* | Any analytics claim |
| 12 | Interoperability | FHIR R4 US Core 35 resources, SMART, Bulk Export, C-CDA, CCR, HL7 v2, X12, DICOM view | 6 | 9 | 1 | 0 | No PACS, no CDS Hooks | Standards depth — **stating it is off by default** | PACS, FHIR billing |
| 13 | Security / privacy | bcrypt, policy, lockout, RBAC (65 objects × 4 levels), sensitivity, break-glass, verified tamper-evident audit | 13 | 3 | 0 | 0 | **MFA cannot be enforced** | Audit integrity, RBAC granularity | MFA enforcement, compliance |
| 14 | Administration | 491 settings, layout/list editors, ACL admin, module manager | 18 | 1 | 0 | 1 | — | Configurability without code | — |
| 15 | Multi-site / multi-facility | Site-per-database, facility model, billing facility | 13 | 2 | 0 | 0 | **Not SaaS** | Multi-clinic operation | SaaS multi-tenancy |
| 16 | Localisation | 47 languages, Arabic 47.5 %, 13 RTL themes | included above | | | | Hijri, Saudi formats | Arabic availability, honestly scoped | "Fully localised" |
| 17 | Saudi readiness | **None** | 0 | 0 | 0 | 0 | **14 gaps** | *(nothing)* | Everything Saudi |
| 18 | Extensibility | Two module frameworks, ~90 events, REST route/scope extension | included above | | | | Billing generators not pluggable | Partner ecosystem | "Unlimited" |
| 19 | Deployment / operations | Single-site install verified running; backup tool; diagnostics; 5 background services | included above | | | | Runner never executed; backup misconfigured | Straightforward deployment | Managed service claims |
| 20 | Demo readiness | 6 scenarios demonstrable today | | | | | 14 blocked | Administrative and platform depth | Clinical/billing demos |

### 29.2 The five defensible headline strengths

1. **Clinical documentation breadth** — 18 active forms, 12 structured data
   domains, a no-code form builder, and a full ophthalmology module across 18
   tables. Verifiable, working, and unusually deep for the price point.
2. **US revenue-cycle depth** — genuine X12 5010 837P/837I generation,
   CMS-1500 and UB-04, real 835 ERA parsing and auto-posting, coordination of
   benefits, and configurable AR ageing. This is a complete RCM implementation,
   not a stub.
3. **A real clinical decision support engine** — filters, targets, intervals,
   per-patient overrides, reminder scheduling and alert logging, with an
   administrator-facing rule builder. Plus ONC HTI-1 DSI source-attribute
   transparency fields.
4. **Interoperability surface** — FHIR R4 US Core across 35 resources with
   SMART launch, OAuth2 and Bulk Data Export, alongside C-CDA, CCR, HL7 v2 and
   X12. Off by default, but complete.
5. **A verified tamper-evident audit trail** — the only capability in this
   catalogue proven end-to-end at runtime during the audit (200/200 checksums).

---

## 30. Verified Product Limitations

Only limitations established by investigation are listed.

| ID | Limitation | Evidence | Impact |
|---|---|---|---|
| L-01 | **Ambulatory only** — no inpatient, ADT, ward, bed, eMAR, ICU, theatre or nursing documentation | GAP-0001…0014 | Disqualifies the product from hospital tenders without partner software |
| L-02 | **No Saudi-market capability of any kind** | GAP-0046…0059 | Saudi entry is a development programme, not a configuration exercise |
| L-03 | **MFA cannot be enforced and is not wired into the browser login** | §20.2 | Fails many enterprise security questionnaires |
| L-04 | **No analytics or BI layer**; 55 static reports; `chart.js` vendored unused | GAP-0040, §16.10 | Loses head-to-head against products with dashboards |
| L-05 | **No general ledger, AP, procurement, PO, HR, payroll or asset management** | GAP-0029…0039 | Needs an ERP alongside it |
| L-06 | **No denial management** | GAP-0028 | A visible gap in RCM comparisons |
| L-07 | **Not SaaS multi-tenant** — manual per-site provisioning, shared process, no tenant registry | GAP-0043, §21.2 | Blocks a self-service SaaS business model without platform work |
| L-08 | **Arabic covers only 47.5 % of UI strings, chrome only** — picklists, layout labels and code descriptions untranslated | §22.2 | Arabic UX is materially incomplete |
| L-09 | **RTL is shallow** — ~20 code consumers; legacy screens use hard-coded left alignment and float/table layouts | §22.3 | Per-screen remediation required |
| L-10 | **No Arabic-shaping PDF font in the tree** | §22.4 | Arabic PDF output will not render correctly as shipped |
| L-11 | **No tax field anywhere in the billing chain** | GAP-0053 | VAT is a schema change, not configuration |
| L-12 | **Currency is display-only** — no ISO code, no multi-currency, no currency column | §22.4 | Multi-country operation needs schema work |
| L-13 | **Every external integration is unconfigured**; 4 payment gateways, 5 fax/SMS vendors, 2 eRx vendors, 1 clearinghouse — none live | §17.4 | Each requires a commercial contract before it can be demonstrated |
| L-14 | **The revenue cycle has no API surface** — no REST or FHIR endpoint touches charges, claims, payments or AR | §18.2, §18.3 | External RCM integration must go via the database or a file drop |
| L-15 | **FHIR write support covers 3 resources**; no PATCH, DELETE, `_history` or transaction bundles | §18.3 | Limits bidirectional FHIR integrations |
| L-16 | **Quality measures are 2011/2014-era**, and the modern QRDA path has no measure bundle installed | CAP-0170, §11 WF-0007 | Cannot support current-year certified quality reporting as shipped |
| L-17 | **Configuration is 100 % stock**, including live vendor placeholders and OpenEMR branding/donation links | §19.4 | The product currently presents as unbranded open-source software |
| L-18 | **No data at all** — 0 patients, encounters, appointments, charges, documents | §0.3 | No clinical or financial demo is possible today |
| L-19 | **Only 1 human account; 5 of 7 roles never populated** | §12.4 | Role-based behaviour is untested in practice |
| L-20 | **Background service runner has never executed**; two "active" services have `next_run` in 2021 | §19.7, GAP-0063 | Reminders, email queue and UUID backfill silently do not run |
| L-21 | **Backup is misconfigured** (`mysql_bin_dir` → XAMPP path) and `config.php` holds three Unix-only commands on a Windows host | CFG-0120, §19.6 | Backup, printing, faxing and MIME detection will fail |
| L-22 | **Audit log has a 93 % noise rate** (~2,000 rows/day idle) and `audit_events_lab-order` is never logged | GAP-0061, GAP-0070 | Storage growth and poor forensic signal |
| L-23 | **Audit integrity is a hash, not an HMAC, and is unchained**; bound SQL parameters (future PHI) are stored in plaintext base64 | §20.4 | Overstating it as "immutable" would be false |
| L-24 | **11 of 55 reports have no in-file authorisation**; one report has none at all | §20.6 | Direct-URL access bypasses menu hiding |
| L-25 | **Schema and UI orphans** — `fee_schedule` has zero consumers; six `x12_partners` columns (OAuth client id/secret, token/claim-status/attachment endpoints, `processing_format`) are editable and read by nothing | §24.4 | Administrators can configure settings that do nothing |
| L-26 | **Billing generators cannot be added by a module** — hard-coded dispatch ladder with no factory or event | `BillingProcessor.php:161-192` | A NPHIES or other national claim format would require core patching |
| L-27 | **373 commits behind upstream**, and one custom module (`oe-module-claimrev-connect`) is gitignored and composer-installed | §0.2, §20.6 #12 | Patch currency and supply-chain provenance both need addressing |
| L-28 | **Sensitivity gating covers encounters only** — not demographics, problem lists, notes, documents or the API | §15.1 | Not a record-level confidentiality model |

---

## 31. Missing Capabilities / Market Gaps

The complete Missing register is **60 items**: 59 market-comparison gaps
(`GAP-0001…GAP-0059`, detailed with negative-search evidence in §26 and §23.3)
plus one in-catalogue capability (CAP-0218, MFA enforcement).

### 31.1 By theme

| Theme | Count | GAP IDs |
|---|---|---|
| Inpatient and acute care settings | 14 | GAP-0001…0014 |
| Ancillary clinical services | 8 | GAP-0015…0022 |
| Patient access and engagement channels | 5 | GAP-0023…0027 |
| Back-office finance and administration | 12 | GAP-0028…0039 |
| Data platform and architecture | 6 | GAP-0040…0045 |
| Saudi market | 14 | GAP-0046…0059 |
| In-catalogue | 1 | CAP-0218 |
| **Total** | **60** | |

### 31.2 The gaps that most affect competitive position

| Rank | Gap | Why it matters |
|---|---|---|
| 1 | Inpatient / ADT / ward / bed / eMAR (GAP-0002…0009) | Removes the product from hospital opportunities entirely |
| 2 | Saudi localisation set (GAP-0046…0059) | Blocks the target market outright |
| 3 | Analytics / BI / dashboards (GAP-0040) | Now table stakes in most HIS evaluations |
| 4 | SaaS tenant isolation (GAP-0043) | Blocks a self-service commercial model |
| 5 | MFA enforcement (CAP-0218) | Fails routine enterprise security review |
| 6 | LIS / RIS / PACS (GAP-0016…0018) | Forces third-party ancillary systems |
| 7 | Denial management (GAP-0028) | Weakest point in an otherwise strong RCM story |
| 8 | Mobile apps (GAP-0023, GAP-0024) | Increasingly expected by patients and clinicians |

---

## 32. Gap Register — Reclassified and Dispositioned

The original `GAP-0060…0073` register conflated five different kinds of thing.
The Group 2 reconciliation separated them. **Thirteen of the fourteen are now
closed**; the one that remains is an external-vendor question that cannot be
answered from this source tree.

Classification types used: **A** current-state knowledge gap · **B** runtime
validation pending · **C** confirmed product limitation · **D** configuration
or operational defect · **E** engineering backlog · **F** external-vendor
evidence.

### 32.0 Disposition summary

| GAP | Subject | Type | Closed? | Outcome |
|---|---|---|---|---|
| GAP-0060 | Mandatory MFA | **C** | **Yes** | MFA *is* wired into browser login for enrolled users; **mandating it is impossible** — no enforcement global |
| GAP-0061 | Audit log noise | **E** | **Yes** (as backlog) | Current behaviour fully known; the open question is an optimisation experiment |
| GAP-0062 | X12 partner ACL | **C/D** | **Yes** | `checkControllerAcl()` early-returns for unmapped controllers — confirmed access-control defect |
| GAP-0063 | Background runner never executes | **D** | **Yes** | Root cause proven: **no live trigger exists** on any of the four paths |
| GAP-0064 | Backup operability | **D** | **Yes** | `realpath()` on an absent path yields `false`; command becomes `\mysqldump`; **no fallback** |
| GAP-0065 | C-CDA Node service | **D** | **Yes** | **Nothing is listening on 127.0.0.1:6661** — C-CDA operationally blocked |
| GAP-0066 | `chart.js` consumer | **A** | **Yes** | No first-party consumer; only vendored AdminLTE demo files under `Documentation/` |
| GAP-0067 | Authorize.Net driver | **A** | **Yes** | Driver **is** declared and installed; only credentials are missing |
| GAP-0068 | US Core version | **A** (+**B**) | **Yes** for A | **No mismatch** — services are multi-version (3.1.1/7.0.0/8.0.0); the global is the *maximum supported*. Inferno conformance remains **B** |
| GAP-0069 | API enablement prerequisites | **A** (+**B**) | **Yes** for A | Full prerequisite checklist derived statically (§32.1). Live enablement remains **B** |
| GAP-0070 | `audit_events_lab-order` | **C/D** | **Yes** | The global is **not defined in `$GLOBALS_METADATA` at all** — upstream defect; lab-order events can never be audited |
| GAP-0071 | Dormant form installability | **B** | **Yes** | **16/16 installed cleanly** in a disposable database; current status stays `Uninstalled` |
| GAP-0072 | Immunisation HL7 message type | **A** | **Yes** | `MSH-9 = VXU^V04^VXU_V04`, HL7 **2.5.1**, with PID / ORC / RXA |
| GAP-0073 | External eRx vendor capability | **F** | **No** | Not knowable from this source tree; no authoritative vendor evidence obtained |

### 32.1 GAP-0069 — API Enablement Prerequisite Checklist (derived statically)

This closes the *knowledge* question. Performing the enablement remains a
separate runtime activity and must not be done on the authoritative install.

| # | Prerequisite | Current value | Why it is required |
|---|---|---|---|
| 1 | `rest_api` | `0` | Gates all 98 standard routes |
| 2 | `rest_fhir_api` | `0` | Gates all 80 FHIR routes |
| 3 | `rest_portal_api` | `0` | Gates the 5 portal routes |
| 4 | `rest_system_scopes_api` | `0` | Gates `system/` scopes and every `$export` operation |
| 5 | **`site_addr_oath`** | **empty** | **Hard blocker.** Used as the token audience/issuer base (`AuthorizationController.php:700-708`); an empty value breaks audience validation for every issued token |
| 6 | At least one `oauth_clients` row | **0 rows** | No client ⇒ no authorization-code or client-credentials flow |
| 7 | OAuth2 signing keypair | `OAuth2KeyConfig` present | Must resolve, else `OAuth2KeyMissing` |
| 8 | Redirect URI(s) registered per client | n/a | Required by `CustomAuthCodeGrant` + PKCE |
| 9 | Scopes granted to the client | dynamic | Built by `ScopeRepository::buildScopeValidatorArray()` |
| 10 | HTTPS origin for SMART launch | site is HTTP on :8300 | SMART and most FHIR clients require TLS |
| 11 | `oauth_password_grant` | `0` | Leave **off** — weaker grant, not needed |
| 12 | `oauth_app_manual_approval` | `0` | Currently auto-approves dynamic registration; review before exposure |
| 13 | `api_log_option` | `2` | Already on; API calls will be audited |

**Side effect worth flagging:** enabling `rest_api` also restores the
background-service trigger (§19.7), because `main.php:270` drives it through
`/api/background_service/$run`.

### 32.2 GAP-0071 — Installability runtime verification (disposable environment)

A throwaway database `openemr_capability_audit_tmp` was created, each dormant
form's `table.sql` executed against it, and the database then **dropped**. The
authoritative `openemr` database was not touched (re-verified afterwards: 283
tables, 18 registry rows, 490 globals — unchanged).

| Result | Detail |
|---|---|
| Forms tested | **16 / 16** |
| Installed without error | **16 / 16** |
| Tables created | **22** — `form_aftercare_plan`, `form_ankleinjury`, `form_bronchitis`, `form_camos`(+`_category`,`_item`,`_subcategory`), `form_clinic_note`, `form_gad7`, `form_note`, `form_painmap`, `form_phq9`, `form_physical_exam`(+`_diagnoses`), `form_prior_auth`, `form_sdoh`, `form_track_anything`(+`_results`,`_type`), `form_transfer_summary`, `form_treatment_plan`, `requisition` |
| DDL compatibility with MariaDB 11.8.8 | **Clean** — no errors, no warnings |
| Disposable database | **Dropped**; existence re-verified as 0 |

**Current status of all 16 remains `Uninstalled`** on the authoritative
installation. What changed is confidence: *installability* is now
runtime-verified rather than inferred, which is what CLM-0004 needs.

Not covered by this test (and therefore still **B**): UI rendering of each
form, `registry` registration through the Forms Administration screen, and
absence of PHP runtime errors on load.

### 32A. Remaining Current-State Knowledge Gaps

**None.** Every question about what this deployment currently is, or currently
does, has been answered.

### 32B. Runtime Validation Pending

Known implementation whose execution has not been exercised. None of these
changes a status; each qualifies a claim.

| # | Item | Why it matters | How to close |
|---|---|---|---|
| RV-01 | US Core conformance not Inferno-tested | CLM-0021 may say "implements", never "certified" | Run `ci/inferno/` against an isolated instance |
| RV-02 | API enablement not performed | The §32.1 checklist is derived, not exercised | Enable in a disposable copy and complete one OAuth flow |
| RV-03 | Backup not executed after correcting the path | Would confirm the fix as well as the fault | Correct `mysql_bin_dir` in a disposable copy and run one backup |
| RV-04 | Background runner not invoked | Would confirm services execute once a trigger exists | Invoke `BackgroundServicesCommand` in a disposable copy |
| RV-05 | Dormant forms not loaded in the UI | SQL install proven; screen rendering not | Register one form in a disposable copy and open it |
| RV-06 | X12 partner ACL bypass not reproduced with a live non-admin session | Static trace is conclusive; a live request would be belt-and-braces | Create a synthetic Front Office user in a disposable copy and request the URL |
| RV-07 | C-CDA generation never run | Blocked anyway by RV/GAP-0065 | Start the Node service in a disposable copy |

### 32C. Confirmed Product Limitations

Known, proven, and **not** knowledge gaps.

| # | Limitation | Evidence | Catalogued as |
|---|---|---|---|
| PL-01 | **MFA cannot be mandated.** Enrolled users are challenged; unenrolled users sign in with a password alone; no enforcement global exists | `main_screen.php:153-171`; `globals.inc.php` search | CAP-0218 (`Missing`), L-03 |
| PL-02 | **`audit_events_lab-order` is undefined**, so lab-order events can never be audited | `EventAuditLogger.php:77` vs `globals.inc.php:2785-2850` | L-22 |
| PL-03 | **Legacy controllers are largely ungated**: `CONTROLLER_ACL_MAP` covers 2 of 10 controllers, and `checkControllerAcl()` returns early for the rest | `Controller.class.php:131-135` | §20.6 #6, L-24 |

### 32D. Configuration / Operational Defects

Implementation exists; this environment prevents it working.

| # | Defect | Proof | Affected | Fix |
|---|---|---|---|---|
| OD-01 | **Backup cannot execute** — `mysql_bin_dir` = `C:/xampp/mysql/bin`, which does not exist; `realpath()` returns `false`; command degrades to `\mysqldump`; no fallback | `backup.php:126,457-458`; `Test-Path` = False | CAP-0240 (**Op: BLOCKED**) | Set `mysql_bin_dir` to `C:/openemr-stack/mariadb/bin` (both `mysqldump.exe` and `mariadb-dump.exe` are present) |
| OD-02 | **C-CDA service not running** — nothing listening on 127.0.0.1:6661 | `Get-NetTCPConnection -LocalPort 6661` → no listener | CAP-0201 (**Op: BLOCKED**), CLM-0023 | Start the `ccdaservice` Node service |
| OD-03 | **Background services never execute** — no live trigger on any of four paths | §19.7 | L-20 | Enable `rest_api`, or add a scheduled task, or invoke the CLI command |
| OD-04 | **Unix-only commands configured on a Windows host** — `lpr`, `enscript`, `/usr/bin/file` | `sites/default/config.php` | Printing, faxing, MIME detection | Replace with Windows equivalents |
| OD-05 | **Email sends silently no-op** — sender addresses blank | CFG-0102 | CAP-0185 | Set `practice_return_email_path` and `patient_reminder_sender_email` |

### 32E. Engineering Backlog / Recommended Fixes

Known current state; the remaining question is whether and how to improve it.
**These are not knowledge gaps and are not counted as such.**

| # | Item | Rationale | Effort |
|---|---|---|---|
| EB-01 | Add a `force_mfa` global and wire it into `main_screen.php` | Closes the most commonly-failed security-questionnaire item (PL-01) | Small |
| EB-02 | Add `audit_events_lab-order` to `$GLOBALS_METADATA` | Restores lab-order auditability (PL-02) | Trivial |
| EB-03 | Extend `CONTROLLER_ACL_MAP` to all 10 controllers, or enforce ACL inside each controller | Closes PL-03 | Small |
| EB-04 | Evaluate `audit_events_query = 0`, or reclassify `gacl_*` reads | 93 % of the audit log is ACL-read noise (~2,000 rows/day idle). **This is an optimisation experiment, not an unknown** — current behaviour is fully characterised | Small + validation |
| EB-05 | Add in-file ACL checks to the 11 unprotected reports, especially `amc_full_report.php` | Closes L-24 | Small |
| EB-06 | Correct `mysql_bin_dir`, `perl_bin_dir` and the `config.php` Unix commands for this host | Closes OD-01, OD-04 | Trivial |
| EB-07 | Establish a background-service trigger | Closes OD-03 | Small |
| EB-08 | Rename `_rest_routes_fhir_r4_us_core_3_1_0.inc.php` to reflect multi-version support | Removes a recurring source of false "version mismatch" findings | Trivial |
| EB-09 | Refactor `BillingProcessor::buildProcessingTaskFromPost()` into a task registry with an event | Prerequisite for any national claim-format module (e.g. NPHIES) without patching core | Medium |
| EB-10 | Bring the fork current with upstream (373 commits behind) and vendor `oe-module-claimrev-connect` into version control | Patch currency and supply-chain provenance | Medium |

### 32F. External Vendor Unknowns

| GAP | Question | Status | Effect |
|---|---|---|---|
| **GAP-0073** | What does the external e-prescribing vendor (NewCrop / "Ensora", `secure.newcropaccounts.com`) actually provide — drug–drug interaction checking, formulary/benefit data, EPCS, Surescripts participation? | **OPEN** | Not knowable from this source tree. This codebase only builds an XML payload and POSTs it (`interface/eRx.php`); the `drug_drug` and formulary flags it renders are values *returned by* the vendor. **CLM-0012 is therefore already downgraded** to claim only the hand-off, never the vendor's clinical checking. No further downgrade is required, and no marketing claim depends on closing this. |

---

## 33. Corrections / Superseded Findings

The prior report at this path (SHA-256 `db17ee12…`, 356 lines) was reverified
in full. Its structure and most findings were sound. The following material
statements are corrected.

| # | Prior statement | Corrected finding | Evidence for the change |
|---|---|---|---|
| C-01 | Implied that the 5 registered Zend modules plus 8 custom modules were the module picture | **22 module directories exist**: 5 registered, 6 Laminas framework components that are not Module-Manager entries, and 11 installable-but-unregistered. The prior report classified 9 dirs as "framework/infrastructure"; the correct figure is 6, with `PatientFilter`, `Patientvalidation` and `PrescriptionTemplates` being genuine installable modules | `InstallerController.php:74,86` `coreModules`; `application.config.php:14-25`; `modules` table |
| C-02 | "18 clinical forms" presented as the form inventory | **35 form directories exist**; 18 are registered. 16 dormant forms (PHQ-9, GAD-7, Physical Exam, Prior Auth, Track Anything, …) and the LBF engine were not reported | `interface/forms/` listing vs `registry`; `SHOW TABLES LIKE 'form_%'` |
| C-03 | Menu areas listed as capabilities without visibility checking | **47 capabilities are currently hidden by feature flags** — Groups, Inventory, eRx, Portal, Fax/Scan, IPPF reports, UB-04, claim SFTP and the four APIs. The prior report listed Inventory, Groups and eRx as though available | `globals` values recomputed against `global_req`/`global_req_strict` in the menu JSONs |
| C-04 | Report families summarised (10 families) | **55 distinct reports** individually catalogued, with 11 lacking in-file authorisation and one (`amc_full_report.php`) having none at all | Full read of `interface/reports/` (47 files) + billing/orders report screens |
| C-05 | "Multi-language" mentioned only as a dimension | **Arabic is loaded and live**: 6,290 of 13,234 constants (47.5 %), `lang_is_rtl=1`, human-quality text spot-verified. 13 RTL themes ship | Live DB query + rendered Arabic strings |
| C-06 | *(Prior in-repo discovery doc)* "Arabic translations are not loaded by the default installer" | **Superseded.** That statement was true of the shipped SQL file, but **this installation has them loaded** — 237,509 definitions across 47 languages | `SELECT lang_id, COUNT(*) FROM lang_definitions GROUP BY lang_id` |
| C-07 | Fork status not addressed | **Zero fork divergence** established — HEAD is a plain ancestor of `upstream/master`, 0 ahead / 373 behind. No current-project-specific capability exists | `git merge-base --is-ancestor`; `git rev-list --count` |
| C-08 | Data state not addressed | **The installation is empty** — 0 patients, encounters, appointments, charges, insurers, drugs and documents. This governs every demo verdict | Row counts across 15 tables |
| C-09 | Configuration state not addressed | **All 490 globals are at stock defaults**; the only 6 deviations are OS-path defaults and a generated UUID. Vendor placeholders are live | Programmatic diff of `$GLOBALS_METADATA` against `globals` |
| C-10 | MFA listed under "Authentication & session controls" as an available control | **MFA is not wired into the browser login, cannot be mandated, and has 0 enrolments.** It applies only to the OAuth2/API path | `login.php` + `auth.inc.php` read in full; `login_mfa_registrations` empty |
| C-11 | "REST API" and "FHIR API" listed as switched off — correct, but unquantified | Quantified: **183 routes** (98 + 80 + 5), 35 FHIR resources, 3 writable, no PATCH/DELETE, and **no billing endpoints at all** | Route-file enumeration |
| C-12 | Sensitivity described as gating "sensitive charts" | Corrected scope: sensitivity gates **encounters and their forms only** — not demographics, problem lists, notes, documents or the API — and an empty value means no restriction | `EncounterService.php:449-450`; `forms.php:557-563`; all call sites guarded by `if ($sensitivity && …)` |
| C-13 | "OpenEMR multi-site" described alongside SaaS tenancy as a distinction to make | Hardened with negative evidence: **no tenant table, no provisioning API, no tenant discriminator, no per-tenant keys.** `multi.?tenan` = 1 comment hit | Schema sweep of 283 tables; repo grep |
| C-14 | ACL described from `Installer.class.php` source | Rebuilt from the **live database**: 65 ACOs, 13 sections, 19 grants, 7 groups, 2 AROs, all AXO tables empty. Two divergences from the source docblock found (`menus\|modle` typo; `docs_rm` omitted from the narrative) | `gacl_*` queries |
| C-15 | Login URL and credential presented in a marketing-adjacent table | The credential is retained as a verified fact but is now explicitly labelled **`LOCAL DEVELOPMENT CREDENTIAL — SECURITY SENSITIVE — MUST NOT BE USED IN PUBLIC DEMO`**, the plaintext is **not reproduced**, and §27.1 forbids its appearance in any marketing material | Policy applied per §0.5 of the audit brief |

Findings from the prior report that were **reverified and stand unchanged**:
the single staff login URL for all roles; the 7 ACL roles and their permission
shape; only `admin` and `oe-system` assigned; portal and all APIs disabled;
the 5 registered active modules; the access-intersection model.

---

## 34. Evidence Index

### 34.1 Primary code evidence

| Area | Principal files |
|---|---|
| Menus | `interface/main/tabs/menu/menus/{standard,front_office,chart_review,answering_service}.json`, `patient_menus/standard.json`, `sites/default/documents/custom_menus/*.json` |
| Menu engine | `src/Menu/MenuRole.php:44-229`, `MainMenuRole.php:41-220`, `PatientMenuRole.php:42-285` |
| Shell | `interface/main/tabs/main.php`, `main_screen.php`, `tabs/js/{tabs_view_model,user_data_view_model,custom_bindings,frame_proxies}.js`, `templates/interface/main/tabs/*.twig` |
| Authentication | `src/Common/Auth/{AuthUtils,AuthHash,MfaUtils,AuthGlobal,OneTimeAuth,AuthEvent}.php`, `library/auth.inc.php`, `library/classes/Totp.class.php` |
| Authorization | `src/Common/Acl/{AclMain,AclExtended,AccessDeniedException,AccessDeniedHelper}.php` |
| Audit | `src/Common/Logging/{EventAuditLogger,SystemLogger,AuditConfig,BreakglassChecker}.php`, `Audit/{MultiSink,LogTablesSink,AtnaSink}.php`, `interface/reports/audit_log_tamper_report.php` |
| Clinical forms | `interface/forms/` (35 dirs), `library/registry.inc.php`, `interface/forms_admin/forms_admin.php` |
| Clinical chart | `interface/patient_file/**` |
| CDS | `library/clinical_rules.php` (3,532 ln), `library/reminders.php`, `src/ClinicalDecisionRules/` (56 files), `interface/super/rules/` |
| Orders | `interface/orders/**`, `interface/procedure_tools/{quest,labcorp,gen_universal_hl7}/` |
| Billing | `src/Billing/**` (incl. `X125010837P.php`, `X125010837I.php`, `Hcfa1500.php`, `ParseERA.php`, `SLEOB.php`, `EDI270.php`, `BillingProcessor/`), `interface/billing/` (31 files), `library/FeeSheet.class.php`, `library/edihistory/` (13 files), `controllers/C_X12Partner.class.php`, `templates/x12_partners/` |
| Modules | `src/Core/{ModulesApplication,ModulesClassLoader}.php`, `interface/modules/zend_modules/module/Installer/`, `interface/modules/{zend_modules,custom_modules}/` |
| Events | `src/Events/**` (~90 event constants), `src/Menu/{MenuEvent,PatientMenuEvent}.php` |
| APIs | `_rest_routes.inc.php`, `apis/routes/_rest_routes_{standard,fhir_r4_us_core_3_1_0,portal}.inc.php`, `apis/dispatch.php`, `src/RestControllers/**`, `src/FHIR/**`, `src/Common/Auth/OpenIDConnect/**` |
| Configuration | `library/globals.inc.php` (4,563 ln), `interface/super/edit_globals.php`, `sites/default/{config.php,sqlconf.php}` |
| Quality | `interface/reports/cqm.php`, `library/classes/rulesets/`, `src/Cqm/`, `src/Services/Qdm/`, `src/Services/Qrda/` |
| Localisation | `library/translation.inc.php`, `contrib/util/language_translations/`, `public/themes/rtl_*.css`, `interface/themes/oemr-rtl.scss` |

### 34.2 Database evidence

| Purpose | Tables queried |
|---|---|
| Users and credentials | `users` (4 rows, all columns), `users_secure` (structure + 1 row, hash never printed) |
| Roles and permissions | `gacl_aco`, `gacl_aco_sections`, `gacl_aco_map`, `gacl_acl`, `gacl_acl_sections`, `gacl_aro`, `gacl_aro_groups`, `gacl_aro_groups_map`, `gacl_groups_aro_map`, all 6 AXO tables, `gacl_phpgacl` |
| Modules | `modules` (full dump), `module_acl_sections`, `module_acl_group_settings`, `module_acl_user_settings`, `module_configuration`, `modules_hooks_settings`, `modules_settings`, `openemr_modules`, `openemr_module_vars` |
| Forms | `registry` (all 18 rows, all columns), `SHOW TABLES LIKE 'form_%'` |
| Configuration | `globals` (490 rows, diffed against 491 code defaults), `user_settings` (33 rows) |
| Clinical rules | `clinical_rules` (80), `clinical_plans` (9), `list_options` (`clinical_rules`) |
| Localisation | `lang_languages` (59), `lang_constants` (13,234), `lang_definitions` (237,509, grouped by language; Arabic rows sampled) |
| Operations | `background_services` (5), `facility` (1), `users_facility` (0), `facility_user_ids` (0), `ip_tracking` (1) |
| Audit | `log` (4,280, event distribution + 200-row checksum recomputation), `log_comment_encrypt` (4,280), `audit_master` (0), `audit_details` (0), `extended_log` (0), `api_log` (15) |
| Emptiness verification | `patient_data`, `form_encounter`, `openemr_postcalendar_events`, `billing`, `ar_activity`, `ar_session`, `claims`, `payments`, `insurance_companies`, `insurance_data`, `drugs`, `drug_inventory`, `drug_sales`, `documents`, `prescriptions`, `x12_partners`, `x12_remote_tracker`, `oauth_clients`, `api_token`, `login_mfa_registrations`, `report_results`, `esign_signatures`, `ccda*` — all **0** |
| Scale | `information_schema.tables` → 283 |

### 34.3 Runtime evidence

| Check | Result |
|---|---|
| `GET /interface/login/login.php?site=default` | **HTTP 200, 9,375 bytes** |
| `password_verify()` against `users_secure.password` for `admin` | **true** |
| SHA3-512 recomputation over the last 200 `log` rows vs `log_comment_encrypt` | **200 match, 0 mismatch** |
| `api_log` anonymous probes 2026-08-09 | 15 rows — FHIR metadata and SMART style endpoints answered |

### 34.4 Secondary evidence, reverified not trusted

`docs/00-discovery/` (16 files), `docs/discovery/openemr-decision-evidence/`
(24 artefacts), `tools/discovery/openemr-decision-evidence/` (13 scripts),
`Locked Desicions/` (3 documents), module `README.md`/`info.txt` files.
These were read for orientation. Where they conflicted with the live database,
the database won (see C-06).

---

## 35. Final Certification

### 35.1 Certification checklist

| Criterion | Result |
|---|---|
| **A. File integrity** | `docs/HISModulesUsers.md` is the only persistent report created or modified. No competing discovery report was produced. Verified findings from the prior version are retained or explicitly corrected in §33 |
| **B. Evidence integrity** | Every material capability cites current-project evidence. Generic OpenEMR documentation is used nowhere as sole proof. No password was cracked; no secret is reproduced (all shown `[REDACTED]`, and all are in fact empty). No PHI exists in this installation and none is in this document |
| **C. Coverage integrity** | Features, sub-features, actions, modules, forms, screens, routes, workflows, users, roles, ACL, menu roles, sensitivity, ownership scope, facility/site scope, reports, integrations, APIs, interoperability, configuration, feature flags, security, audit, localisation, Saudi readiness, market-comparison gaps, demo readiness and marketing-safe claims are all covered |
| **D. Status integrity** | All 270 catalogued capabilities carry exactly one of Active / Disabled / Uninstalled / Requires Integration / Missing. No unlabelled capability remains |
| **E. Count reconciliation** | Executive-summary totals equal the detailed tables — verified below |
| **F. ID integrity** | `CAP-0001…0270`, `SCR-0001…0128`, `WF-0001…0014`, `ROL-0001…0007`, `USR-0001…0004`, `RPT-0001…0055`, `INT-0001…0036`, `CFG-0001…0120`, `FORM-0001…0035`, `GAP-0001…0073`, `CLM-0001…0032`, `L-01…L-28`, `D01…D21` — contiguous, no duplicates, all cross-references resolve |
| **G. Negative-evidence integrity** | All 59 market-gap `Missing` verdicts record the search terms used, where they were searched, and what was found. The `src/FHIR/R4/**` generated-class disambiguation rule was applied and rejected several false positives |
| **H. Marketing integrity** | No claim in §27 exceeds its evidence. Every claim carries a mandatory qualification and an explicit prohibited stronger claim. All Saudi and compliance claims are forbidden |

### 35.2 Count reconciliation

| Status | Catalogue (`CAP-*`) | Market register (`GAP-*`) | Total |
|---|---:|---:|---:|
| Active | 177 | — | **177** |
| Disabled | 47 | — | **47** |
| Uninstalled | 27 | — | **27** |
| Requires Integration | 18 | — | **18** |
| Missing | 1 | 59 | **60** |
| **Total** | **270** | **59** | **329** |

Domain roll-up (§5.1) sums to 270: 19+13+18+12+8+16+5+14+13+24+8+4+6+12+10+10+16+17+20+15+10.

### 35.3 Read-only compliance

No application source file was modified. No database row was inserted,
updated or deleted. No configuration value, feature flag, module registration,
user account or password was changed. No module was installed. No external
request that creates a record was made. `git status` shows exactly one changed
file relative to the pre-audit state: `docs/HISModulesUsers.md`.

### 35.4 Verdict

**NOT YET CERTIFIED COMPLETE — 14 material gaps remain.**

The defined discovery surfaces were systematically searched, reconciled,
classified, evidenced and audited, and the catalogue is complete and internally
consistent. However, 14 verification questions could not be closed **within a
strictly read-only mandate**, because closing them requires mutating state
(enabling a flag, installing a form, running a backup, triggering the service
runner), probing a local port, inspecting `vendor/`, or obtaining information
from a third-party vendor.

| GAP | Why it could not be closed |
|---|---|
| GAP-0060 | Wiring MFA into browser login would require a code change |
| GAP-0061 | Testing reduced audit noise requires changing a global |
| GAP-0062 | Confirming the X12-partner ACL gate requires a live non-admin request |
| GAP-0063 | Diagnosing the background runner requires triggering it |
| GAP-0064 | Proving the backup failure requires running a backup |
| GAP-0065 | Confirming the C-CDA Node service requires probing port 6661 |
| GAP-0066 | One recursive grep timed out on the Drive-backed filesystem |
| GAP-0067 | `vendor/` is gitignored and was not inspected |
| GAP-0068 | Resolving the US Core version requires running the Inferno suite |
| GAP-0069 | Validating the API-enablement prerequisites requires enabling the API |
| GAP-0070 | Adding the missing audit global is a mutation |
| GAP-0071 | Proving the 16 dormant forms install requires running `installSQL()` |
| GAP-0072 | Confirming HL7 VXU^V04 requires generating a message |
| GAP-0073 | Vendor-side capability is not knowable from this source tree |

**None of the 14 changes any status in the catalogue.** Eight block or qualify
a specific marketing claim (flagged in §32), and five block a specific demo
scenario. They are recorded, owned and prioritised rather than silently
resolved in the product's favour.

