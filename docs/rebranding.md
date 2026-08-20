# OpenEMR Rebranding & Identity — Authoritative Discovery and Implementation-Input Report

**Final Group 1 Certification — Discovery Complete / Decisions Locked.**

This document is the single authoritative rebranding-discovery input for the implementation phase
(`MVP-010`). It consolidates and supersedes the Group 1A (initial discovery), Group 1B (validation),
Group 1C (gap closure) and Group 1D (decision closure) reports of 2026-08-09.

**Governance status:** the two branding decisions opened by this audit were adopted by the user and are
now formally locked as **`Q76`** (branding token materialisation) and **`Q77`** (Saudi theme surface) in
`Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md`, Section L, with the SHA-256
governance manifest re-issued and independently verified (§21).

> **FLAGGED FOR HUMAN REVIEW (2026-08-19).** This document treats `Q76`/`Q77` and the wider Locked
> Decisions register as unconditionally binding on Group 2 implementation throughout (§1, §13.5, §14.4,
> §20, §22). A later Owner ruling (recorded 2026-08-19, per this repo's memory of the governance
> discussion) holds that the Locked Decisions corpus is "future-phase roadmap, not binding on the
> current MVP" — a nuanced governance question this document predates and does not address, and which
> this pass does not attempt to resolve on the document's behalf. A maintainer should reconcile whether
> `Q76`/`Q77` specifically (as opposed to the corpus generally) are still treated as binding for the
> current MVP before citing this document's verdict as current governance status.

---

## 0-A. AUTHORITATIVE ARTIFACT IDENTITY

Recorded at the **start** of the Group 1C audit, against the file at
`G:\My Drive\OpenEMR\docs\rebranding.md`.

| Field | Value at Group 1C start | Expectation | Match |
|---|---|---|---|
| Absolute path | `G:\My Drive\OpenEMR\docs\rebranding.md` | — | — |
| Byte size | 72,306 | — | — |
| Line count | **964** | ~964 | ✅ |
| SHA-256 | `51d1d5222479a961aed28050938f4913c6dcd06cd3f40a7bac2521ce661939e0` | — | — |
| Git branch | `master` | — | — |
| HEAD commit | `631f2b38cf633769c305233f88cdf9c73ca80657` | — | — |
| Working tree vs HEAD | 2 modified/deleted tracked, 7 untracked (pre-existing) | — | — |
| Document title | *OpenEMR Rebranding & Identity — Authoritative Discovery Report* | — | — |
| Highest `BRAND-xxx` | **BRAND-120** | 120 | ✅ |
| Unique `BRAND-xxx` count | **120** (range 001–120, **no gaps**) | 120, no gaps | ✅ |
| `G-xx` knowledge gaps | **10** (G-01…G-10) | 10 | ✅ |
| `CONFLICT-xx` entries | **4** (CONFLICT-01…04) | 4 | ✅ |
| Verdict at start | **VERDICT A — READY FOR GROUP 2** | — | — |

**The actual file matched every stated expectation.** No forcing was required. This file — not any
exported, cached or summarised copy — was used as the source of truth.

### Final Group 1 certification end state (post-governance, current)

| Field | Final value |
|---|---|
| Unique `BRAND-xxx` | **136** (range 001–136, **no gaps**, no duplicates) |
| Original `G-xx` gaps | 10 → **9 closed, 1 partially closed, 0 open** (§12.1) |
| Residual items | 4, counted separately (§12.2) |
| `CONFLICT-xx` | 4 — all dispositioned (§15) |
| `K-xx` corrections | **19** (K-01…K-05 Group 1C · K-06…K-14 Group 1D · K-15…K-19 final repair — §18) |
| Discovery knowledge gaps | **0** |
| Locked Decisions required before Group 2 | **0** — adopted as **`Q76`**, **`Q77`** |
| Implementation-dependent acceptance tests | **8** (§20 register C) |
| Verdict | **VERDICT A0 — GROUP 1 CLOSED; GROUP 2 MAY START** (§22) |
| Database | restored to baseline (§17) |
| Working tree | unchanged from start except this file (§17.4) |

*Line count and end-state SHA-256 are reported in the terminal handoff rather than embedded here, since a
file cannot contain its own hash.*

---

## 0. Audit header

| Field | Value |
|---|---|
| **Repository path** | `G:\My Drive\OpenEMR` |
| **Current branch** | `master` |
| **Exact HEAD commit** | `631f2b38cf633769c305233f88cdf9c73ca80657` |
| **Upstream relationship** | `git rev-list --count upstream/master..HEAD` = **0** (zero fork-owned commits; fork is a strict ancestor of `upstream/master`) |
| **Upstream stable baseline** | `v8_2_0` = `6125a2fd8089c8bcc3848071c1293c60e27a7585` |
| **Version** | OpenEMR 8.3.0-dev (`$v_database = 541`, `$v_acl = 13`) |
| **Working tree differs from HEAD?** | **YES** — see §0.4 |
| **Audit date** | 2026-08-09 |
| **Audit generation** | **Group 1D — FINAL DECISION CLOSURE AND CERTIFICATION** (supersedes 1A discovery, 1B validation, 1C gap-closure) |
| **Audit type** | DISCOVERY / VERIFICATION / DECISION-RECOMMENDATION — **no implementation** |
| **Runtime verification** | **PERFORMED** — Apache 2.4.57 + PHP 8.3.33 + MariaDB 11.8.8 live on `http://localhost:8300` |
| **Database inspected** | **YES** — `openemr` schema, 490 `globals` rows (see methodology for the controlled temporary writes) |

### 0.1 Scope boundaries (absolute)

This is a **discovery-only** task. Nothing was rebranded, replaced, patched, refactored or configured.
No logo, product name, theme, global, template or database value was modified. No Branding Layer was
built. No recommendation was implemented.

**Files created by this audit:** none inside the application. One analysis helper created in Group 1A
(`tools/discovery/openemr-decision-evidence/analyze-brand-assets.py`) was reused read-only.

**Accurate statement of non-read-only actions.** Groups 1C and 1D performed a small number of *controlled,
recorded, reversed and verified* mutations in order to obtain runtime evidence that static analysis cannot
produce. These are disclosed rather than concealed:

| Action | Scope | Restored? | Proof |
|---|---|---|---|
| `UPDATE globals` × 4 rows (`portal_onsite_two_enable`, `extra_portal_logo_login`, `rest_api`, `css_header`) | one row at a time, one test each | **YES** | §17 |
| Authenticated HTTP session (`POST` login as `admin`, then logout) | synthetic instance, 0 patients | **YES** — logged out | §12.2 G-04 |
| Apache restarted twice with a temporary `OPENSSL_CONF` environment variable | process environment only; **no file, config or source change** | **YES** — restarted without it; FHIR returns to `HTTP 500` | §12.5 |
| OAuth2/crypto key material auto-generated by the app during the FHIR test (4 files + 4 `keys` rows) | created by OpenEMR itself, not authored | **YES** — all deleted after proving nothing depended on them | §17.3 |

All other DB access was `SELECT`-only and all other HTTP access was `GET`/`HEAD`.

### 0.2 Audit methodology

Five independent evidence channels, deliberately chosen so that each can falsify the others:

| # | Channel | Purpose | Covers the weakness of |
|---|---|---|---|
| 1 | `git grep <pattern> HEAD` | Commit-pinned source truth | — |
| 2 | **Working-tree filesystem inspection** | Generated, ignored and untracked files | Channel 1 cannot see build output or ignored files |
| 3 | **Live MariaDB read-only queries** | Effective runtime configuration | Channels 1–2 only show source *defaults* |
| 4 | **Live HTTP runtime probing** | What is actually rendered/served | Channels 1–3 cannot prove render paths |
| 5 | **Bidirectional asset analysis** (source→asset, asset→consumer) | Orphan and duplicate detection | A one-way grep proves neither |

Group 1A relied on channel 1 almost exclusively. **That is the root cause of every gap corrected below.**

### 0.3 Runtime verification status

| Surface | Status | Evidence |
|---|---|---|
| Login page | ✅ **VERIFIED** | `HTTP 200`, 9,375 bytes, full HTML parsed |
| Favicon resolution | ✅ **VERIFIED** | `/public/images/logos/core/favicon/favicon.ico?t=1783217252` → `HTTP 200` |
| Broken favicon path | ✅ **VERIFIED BROKEN** | `/public/images/favicon.ico` → **`HTTP 404`** |
| Theme CSS served | ✅ **VERIFIED** | `/public/themes/style_light.css?v=82` |
| Multisite admin | ✅ **VERIFIED** | `HTTP 200`, `<title>OpenEMR Site Administration</title>`, **unauthenticated** |
| Acknowledgements page | ✅ **VERIFIED** | `HTTP 200`, 24,739 bytes, **unauthenticated** |
| Swagger UI | ✅ **VERIFIED** | `HTTP 200`, `<title>Swagger UI</title>`, **unauthenticated** |
| HTTP response headers | ✅ **VERIFIED** | `Set-Cookie: App=OpenEMR` (see BRAND-089) |
| Database globals | ✅ **VERIFIED** | 490 rows queried read-only |
| Portal login | ✅ **VERIFIED** | Controlled reversible enable→test→restore; `HTTP 200`, 13,620 bytes (§12.2 G-01) |
| Authenticated Administrator shell | ✅ **VERIFIED** | `HTTP 200`, 68,618 bytes; title, navbar logo/link, favicon, both themes (§12.2 G-04) |
| About page | ✅ **VERIFIED** | `HTTP 200`; all 3 external links rendered live |
| **FHIR `/metadata`** | ✅ **VERIFIED** | `HTTP 200`, 35,809 bytes under temporary correct `OPENSSL_CONF`; `software.name` + `implementation.description` (§11.2) |
| **SMART style JSON** | ✅ **VERIFIED** | `HTTP 200`; 6 colours, typography, `logo_primary` (§11.2) |
| **SMART dark-theme fallback defect** | ✅ **VERIFIED** | `css_header=style_dark.css` still returns **light** tokens (§11.2) |
| REST/API error surface | ✅ **VERIFIED** | `"OpenEMR Error: API is disabled"` observed live (BRAND-134) |
| Missing-secondary-portal-logo behaviour | ✅ **VERIFIED** | Enabling the gate emits `<img src=''>` (BRAND-021) |
| Tenant boundary guard | ✅ **VERIFIED** | Traversal and null-byte site ids → `HTTP 400` (§12.2 G-10a) |
| OAuth2 authorization **screens** | ◐ **SOURCE-PROVEN** | 4 strings in `templates/oauth2/**`, all `xlt`-wrapped; full client flow not executed |
| Portal **post-authentication** | ◑ **IMPLEMENTATION-DEPENDENT ACCEPTANCE** | Needs a portal patient credential; 0 patients (A6) |
| PDF / print output | ◑ **IMPLEMENTATION-DEPENDENT ACCEPTANCE** | Needs ≥1 patient; 0 patients (A5) |
| Reception / Accountant shells | ◐ **SOURCE-PROVEN role-independent** | Branding is role-agnostic (§12.2 G-04r); runtime smoke test deferred (A7) |

> **HISTORICAL — state before Group 1C/1D:** earlier generations of this report recorded the portal,
> FHIR `/metadata`, SMART surfaces and authenticated sessions as *blocked* or *not performed*. All four
> were subsequently verified; the portal via a controlled reversible test (§17.1) and the FHIR/SMART
> surfaces after isolating the blocker to an **unset `OPENSSL_CONF` environment variable** — an
> environment defect, not an OpenEMR application defect (§11.2).

### 0.4 Working tree vs HEAD

`git status --short --branch` at audit time:

```
## master...origin/master
 D Documentation/EHI_Export/docs/diagrams/tables/lists_medication.2degrees.dot
 M sites/default/sqlconf.php                     <- local DB credentials; NEVER read into this report
?? Documentation/EHI_Export/.../lists_medication.2degrees.docx
?? "Locked Desicions/"
?? SETUP-STATUS.md
?? docs/00-discovery/   ?? docs/discovery/   ?? docs/rebranding.md
?? fix-docker-virtualization.ps1               ?? tools/discovery/
```

**No tracked application file is modified.** The single modified tracked file (`sqlconf.php`) is local DB
config and is not branding-relevant.

**Ignored-but-runtime-relevant paths that HEAD cannot see (the Group 1A blind spot):**

| Path | State | Branding relevance |
|---|---|---|
| `public/themes/` | **44 compiled CSS files present** | **HIGH** — these are the themes actually served |
| `public/assets/` | ~40 npm packages, **368 untracked images** | LOW — all third-party (verified, §7.6) |
| `interface/modules/custom_modules/oe-module-claimrev-connect/` | Present, composer-installed | LOW — **0 image assets** (verified) |
| `CLAUDE.local.md`, `.claude/` | Present | None |

---

## 1. Locked Decisions compliance

Reviewed and treated as **binding**. SHA-256 manifest verified — both documents hash-match
`OpenEMR-SaaS-Decision-Documents-SHA256-UPDATED-2026-08-09.txt`:

```
cf806777cab7b6ecb00447de39bc0a6665dfb483d1bec98191cc655151b3fbad  ...Locked-Decisions...md   ✅
38ba60c92936781bf0c8d3a8fd3414679cf3ae6462236e7e23a0fd2973ac73fc  ...Implementation-Backlog...md ✅
```

### 1.1 Binding constraints governing branding

| Ref | Constraint | Effect on this audit |
|---|---|---|
| **Invariant 4** | *No core edits by default.* Prefer modules, overlays, configuration and upstream PRs. | Every NEEDS-PATCH item must justify itself; upstream-PR is the preferred route |
| **Invariant 9** | *Arbitrary tenant CSS/JS is prohibited.* | Rules out any per-tenant stylesheet mechanism |
| **Invariant 10** | *Claims must describe actual controls, not inferred capabilities.* | Nothing marked VERIFIED without evidence |
| **Control Plane §2** | Control Plane stores **branding tokens** (managed PostgreSQL 18) | Tenant branding values are Control-Plane-owned, not OpenEMR-`globals`-owned, at target state |
| **Control Plane §8** | Tenant runtime receives only its own credentials | Branding resolution must not cross tenants |
| **Q34** (LOCKED) | Ship **two** Saudi variants (light + dark, RTL-capable). Per-tenant branding limited to **validated design tokens/CSS variables + tenant logos over a shared immutable bundle**. | Constrains theme scope to 2, not 6 |
| **Q59** (LOCKED) | `sites/<tenant>/documents/theme/` is **not** a runtime override. Branding = shared immutable assets + validated tokens + per-site logos. | Confirmed by this audit (§4, claim C-14) |
| **Q32** (LOCKED) | MVP = **rebrand and harden the existing portal**; greenfield SPA is Phase 2 | Portal branding is in MVP scope |
| **Q33** (LOCKED) | Keep main UI on BS4/Twig for MVP | No shell replacement |
| **Q35** (LOCKED) | CKEditor `ar` + RTL | Localization-adjacent |
| **Q17** (LOCKED) | Accept one-tenant-per-browser Day-1; patch `SessionUtil` only if Q16 flips | **Constrains BRAND-089–093, BRAND-131** (session/cookie identity) |
| **MVP-010** | Tenant may change *approved logo and tokenized palette only*; invalid payloads rejected; no tenant CSS/JS executed; cache keys prevent cross-tenant bleed | The acceptance target this discovery must satisfy |
| **ADR-DEV-001** | Native Apache/PHP/MariaDB dev runtime; Docker optional | Why runtime verification was possible at all |

### 1.2 Conflicts between discovery findings and Locked Decisions

These four conflicts were **identified** during Group 1B discovery and **all four are now resolved**.
The *Finding*, *Locked text* and *Nature of conflict* lines below are retained as discovery evidence; the
**Status** line on each states the final, current disposition.

#### CONFLICT-01 — `sites/<site>/config.php` is an arbitrary per-tenant PHP execution seam

- **Finding:** `interface/globals.php:649` `require_once`s `sites/<site>/config.php` — arbitrary PHP, executed per tenant, every request.
- **Locked text:** Invariant 9 prohibits *"arbitrary tenant CSS/JS"*. Q59's own evidence explicitly names this file as *"the only per-site executable seam"*.
- **Nature of conflict:** PHP is neither CSS nor JS, so the letter of Invariant 9 is not breached; the **intent** (no tenant-supplied executable content) plainly is. A tenant able to write this file has strictly more power than one able to inject CSS.
- **Status:** **RESOLVED — EXISTING OPENEMR SEAM, PROHIBITED FOR THIS MVP.** The seam exists in OpenEMR and is not removed, but it **must not** be used as a tenant branding mechanism. Determined by existing locked text — Invariant 9 (no arbitrary tenant CSS/JS), Q34 and Q59 (per-tenant branding is validated tokens + approved logos over a shared immutable bundle), `MVP-010` (*"no tenant-uploaded CSS/JS is executed"*), reinforced by **`Q76`** (branding reaches tenants only through validated token materialisation). Recorded at **BRAND-120** with Group 2 action **PROHIBITED**, and as constraint **C1** (§20 register D).
- **Correction:** Group 1A listed this under "what can be changed with zero tracked-file modifications", which read as an endorsement. That framing is **withdrawn**. Arbitrary per-tenant PHP is **not** an approved branding mechanism.
- **Not implemented.** No change made to the application.

#### CONFLICT-02 — Theme count: 6 built vs 2 locked

- **Finding:** 6 user-selectable themes build today (`light`, `dark`, `solar`, `manila`, `cobalt_blue`, `forest_green`) → 44 compiled CSS files on disk.
- **Locked text:** Q34 locks **two** Saudi variants (light + dark, RTL-capable).
- **Nature of conflict:** Not a contradiction — an inherited surplus. Q34 governs what the **Saudi product ships**; the extra four are upstream inheritance. The theme dropdown is a **filesystem scan** (`edit_globals.php:714-731`), so any compiled CSS left in `public/themes/` remains selectable by an admin.
- **Status:** **RESOLVED — `Q77`.** Only **Saudi Light** and **Saudi Dark** (both RTL-capable) form the supported Saudi user-selectable theme surface. The four surplus inherited themes (`solar`, `manila`, `cobalt_blue`, `forest_green`) are **not included in the Saudi deployment build output** and therefore cannot appear in the Administration selector or be reached through stale `globals`/`user_settings` values. Build-and-hide was rejected. Required non-selectable artifacts (tabs/shell, directional/RTL, PDF) remain in the build. See §14.

#### CONFLICT-03 — Branding token storage location

- **Finding:** Every branding value today is an OpenEMR `globals` row in the **tenant** MariaDB database (verified live: 490 rows).
- **Locked text:** Control Plane §2 states the Control Plane (PostgreSQL 18) stores **branding tokens**.
- **Nature of conflict:** Target-state architecture diverged from current implementation; the materialisation mechanism was unspecified.
- **Status:** **RESOLVED — `Q76`.** Final boundary: the **Control Plane PostgreSQL database is authoritative** for tenant branding tokens, approved logo references and the branding revision. Where a value is already represented by validated OpenEMR `globals`, the tenant MariaDB `globals` row is a **runtime materialisation target, not the source of truth**. Materialisation is **push/sync** — tenant-scoped and idempotent — and **OpenEMR performs no Control Plane network request during ordinary page rendering**; there is **no runtime read-through** and no per-request Control Plane dependency. During a Control Plane outage the tenant continues serving its **last successfully materialised (last-known-good)** branding, and a failed materialisation never partially applies a revision. Every change carries a **tenant-safe branding revision** used in cache keys to prevent stale or cross-tenant branding. Only validated tokens and approved logos may be materialised. See §13.

#### CONFLICT-04 — `portal_onsite_two_address` placeholder

- **Finding:** live value is `https://your_web_site.com/openemr/portal` (verified in DB).
- **Locked text:** Q12 locks subdomain-per-tenant routing.
- **Status:** **RESOLVED AS IMPLEMENTATION INPUT — SET TENANT PORTAL ADDRESS ACCORDING TO Q12.** Not an architecture decision: the shipped value is an upstream placeholder that provisioning must set per tenant to match the Q12 subdomain-per-tenant routing scheme. Tracked as **BRAND-070** with Group 2 action **SET-CONFIG**.

---

## 2. Audit of the previous audit — claim-by-claim validation

Every material claim in the Group 1A report, independently re-tested. **34 claims** assessed.

**Result: 18 VERIFIED · 7 VERIFIED WITH QUALIFICATION · 6 INCOMPLETE · 3 CONTRADICTED.**

| # | Group 1A claim | Verdict | Evidence / correction |
|---|---|---|---|
| C-01 | Fork has zero own commits | **VERIFIED** | `git rev-list --count upstream/master..HEAD` = 0 |
| C-02 | `openemr_name` global drives product name; **"20 consumers"** | **VERIFIED WITH QUALIFICATION** | 20 consumer *sites* confirmed, but the claim implied *completeness* of product-name coverage. **7 hardcoded `<title>` tags bypass it entirely** (§5.1). Coverage is not total. |
| C-03 | `LogoService` resolves logos; site overrides default (last-match-wins) | **VERIFIED** | `LogoService.php:75-159`; runtime confirms `?t=` cache-bust |
| C-04 | **"Seven logo slots"** | **CONTRADICTED** | There are **9 distinct runtime lookup types** (§7.4). Group 1A itself enumerated `7`, `7a`, `7b` — an internally inconsistent count. |
| C-05 | 8 shipped default logo assets | **VERIFIED** | `find public/images/logos -type f` = 8 |
| C-06 | `public/images/favicon.ico` referenced ×5 but does not exist | **VERIFIED** — now **runtime-proven** | `HTTP 404` live. Group 1A inferred it; this audit proved it. |
| C-07 | **"Login Page: 11 globals"** | **CONTRADICTED** | **14 globals** (lines 510–629). Missed `tiny_logo_1` (:617) and `tiny_logo_2` (:624). |
| C-08 | **"Main menu / header: 3 globals"** (exec summary) | **CONTRADICTED** | The `Branding` section contains **10 globals** (:437–:500). The summary table under-counted; §7 partially compensated. |
| C-09 | 6 login layouts | **VERIFIED** | `templates/login/layouts/` = 6 |
| C-10 | 25 login template files | **VERIFIED WITH QUALIFICATION** | 25 confirmed — but a **26th is referenced and missing**: `tiny_logo.html.twig` (§5.3). Dead branch, not a live defect. |
| C-11 | Portal shares slots "+2 own" | **INCOMPLETE** | Portal has **3 own logo lookups** *and* **5 own branding globals** that Group 1A never found (§5.2) |
| C-12 | 6 themes × 4 variants, "~41 CSS outputs" | **VERIFIED WITH QUALIFICATION** | **44 files actually on disk** (34 top-level + 10 `misc/`). "~41" was a source-side estimate never checked against build output. |
| C-13 | `#2d9bd6` appears in no source file | **VERIFIED** | `git grep -in "2d9bd6" HEAD` = 0 matches |
| C-14 | `sites/<tenant>/documents/theme/` has no runtime behaviour | **VERIFIED** | 0 matches; consistent with locked Q59 |
| C-15 | **"1,028 tracked raster+vector images"** (exec summary) | **CONTRADICTED** | **1,008**. §9.1 of the same report said 1,008. The 1,028 figure was an arithmetic slip (1,008 + the 20 `.woff` fonts). §7.1 reconciles. |
| C-16 | 1,008 images by extension breakdown | **VERIFIED** | svg 504, png 359, gif 115, jpg 26, ico 3, bmp 1 = 1,008 |
| C-17 | ~12 images carry brand identity | **VERIFIED WITH QUALIFICATION** | Directionally right; precise figure is **12 brand-bearing tracked assets** (§7.5), now enumerated rather than estimated |
| C-18 | Duplicate logos: 3× and 2× by blob SHA | **VERIFIED** | Re-derived independently |
| C-19 | **"Email templates contain ZERO branding"** | **VERIFIED WITH QUALIFICATION** | True for `templates/emails/**`. But **email *subjects*** are branded via `openemr_name` (`C_Prescription.class.php:1016,1132`) and the telehealth module embeds the login logo. "Zero branding in email" is too strong as a category statement. |
| C-20 | PDFs/reports are facility-scoped, not product-scoped | **VERIFIED** | Plus newly found `statement_logo` **global** (§5.4) which Group 1A missed |
| C-21 | 2,872 open-emr URLs, 2,645 docblock | **VERIFIED** | Re-derived |
| C-22 | **"Only one outbound call to OpenEMR infrastructure"** | **VERIFIED WITH QUALIFICATION** | Broad audit across `curl_init`/Guzzle/`file_get_contents`/`fetch` over **29 first-party HTTP call sites** found exactly one *hardcoded upstream-branding* endpoint (`reg.open-emr.org`). Group 1A's method (URL grep) could not have proven this; the conclusion survives a proper test. |
| C-23 | FHIR `software.name` hardcoded `"OpenEMR"` | **VERIFIED** | `FhirMetaDataRestController.php:84` confirmed in source and later **runtime-verified** (`HTTP 200`, `"software":{"name":"OpenEMR"}`, §11.2). *Historical note: Groups 1A–1C recorded this as runtime-blocked; the blocker was an unset `OPENSSL_CONF` environment variable, not an application defect.* |
| C-24 | **"Only eight distinct issues require a code patch"** | **CONTRADICTED** | Canonical reconciled list is **11** (§7.2). Two Group 1A entries were misclassified; five items were missing. |
| C-25 | PHP namespace = 3,611 files, DO-NOT-TOUCH | **VERIFIED** | Re-derived |
| C-26 | 62 translatable `OpenEMR` code strings; 924 catalogue lines | **VERIFIED** | Re-derived. DB holds **237,509** `lang_definitions` rows. |
| C-27 | 22 theme screenshots, 16 orphaned | **VERIFIED** | Consumed only by installer theme picker |
| C-28 | Fonts: none first-party; all under `Documentation/EHI_Export` | **VERIFIED** | 78 font files, all in that tree |
| C-29 | **"~85% SAFE-CONFIG/SAFE-ASSET"** | **UNVERIFIED** | An unfalsifiable estimate with no stated denominator. Replaced by the canonical inventory's countable classification (§9). |
| C-30 | Login/About external links inventory | **VERIFIED WITH QUALIFICATION** | Correct as far as it went; **missed the entire acknowledgements-page third-party sponsor block** (§5.6) |
| C-31 | Installer/setup surfaces flagged (favicon only) | **INCOMPLETE** | Group 1A caught the favicon but missed **7 hardcoded titles**, `navbar-brand` strings and headings (§5.1) |
| C-32 | Error pages | **INCOMPLETE** | Category entirely absent from Group 1A. **5 branded strings** across HTML+JSON error templates (§5.5) |
| C-33 | HTTP headers / machine identity | **INCOMPLETE** | Category absent. **`Set-Cookie: App=OpenEMR`** on every response + session-name constants (§5.7). *Group 1C: the constant set is **6**, not 5, and the PHP session cookie is itself named `OpenEMR` — see §13, K-02.* |
| C-34 | Database-driven branding | **INCOMPLETE** | Category absent — Group 1A never queried the DB. All effective values now captured (§3) |

---

## 3. Current database-driven branding (live values)

Read-only queries against the running `openemr` schema. **Every branding value is at OpenEMR factory
default — no rebranding has occurred.**

| Global | Source default | **Live DB value** | Scope | Consumer(s) | Admin location |
|---|---|---|---|---|---|
| `openemr_name` | `OpenEMR` | **`OpenEMR`** | site | 20 sites (§5.0) | Globals → Branding |
| `login_tagline_text` | *(open-source advert)* | **`The most popular open-source Electronic Health Record and Medical Practice Management solution.`** | site | `tagline.html.twig` | Globals → Login Page |
| `show_tagline_on_login` | `1` | **`1`** (shown) | site | `tagline.html.twig` | Login Page |
| `main_menu_logo_link` | `https://www.open-emr.org/` | **`https://www.open-emr.org/`** | site | `main.php:489-495` | Branding |
| `main_menu_logo_title` | `''` | **`''`** → renders `title=""` (**empty**) — see correction K-01 | site | `main.php:490` | Branding |
| `display_main_menu_logo` | `1` | **`1`** | site | `main.php:487` | Branding |
| `online_support_link` | `http://open-emr.org/` | **`http://open-emr.org/`** (plain HTTP) | site | About page | Branding |
| `user_manual_link` | `''` | **`''`** → auto-generates open-emr.org wiki URL | site | `about_page.php:38-40` | Branding |
| `support_phone_number` | `''` | **`''`** | site | About page | Branding |
| `display_acknowledgements` | `1` | **`1`** | site | About page | Branding |
| `display_review_link` | `1` | **`1`** → softwareadvice.com link **live** | site | `about.html.twig:68` | Branding |
| `display_donations_link` | `1` | **`1`** → open-emr.org/donate **live** | site | `about.html.twig:76` | Branding |
| `display_acknowledgements_on_login` | `1` | **`1`** | site | login | Login Page |
| `login_page_layout` | `vertical_band` | **`login/layouts/vertical_band.html.twig`** | site | login | Login Page |
| `primary_logo_width` | `w-50` | **`w-50`** | site | login | Login Page |
| `secondary_logo_width` | `w-50` | **`w-50`** | site | login | Login Page |
| `logo_position` | `flex-column` | **`flex-column`** | site | login | Login Page |
| `show_primary_logo` | `1` | **`1`** | site | login | Login Page |
| `extra_logo_login` | `0` | **`0`** | site | login | Login Page |
| `secondary_logo_position` | `second` | **`second`** | site | login | Login Page |
| `show_labels_on_login_form` | `1` | **`1`** | site | login | Login Page |
| `show_label_login` | `0` | **`0`** | site | login | Login Page |
| **`tiny_logo_1`** | `0` | **`0`** | site | `login.php:201` → `small_logo.html.twig` | Login Page |
| **`tiny_logo_2`** | `0` | **`0`** | site | `login.php:202` | Login Page |
| `css_header` | `style_default.css`¹ | **`style_light.css`** | site + per-user | theme loader | Appearance |
| **`theme_tabs_layout`** | `tabs_style_full.css` | **`tabs_style_full.css`** | site | `config/config.yaml:67` | Appearance |
| **`window_title_add_patient_name`** | `0` | **`0`** | site | browser title | Appearance |
| **`portal_css_header`** | `style_light.css` | **`style_light.css`** | site | `config/config.yaml:76`, `globals.php:486-495` | Portal |
| **`show_portal_primary_logo`** | `1` | **`1`** | site | `portal/index.php:607,618` | Portal |
| **`extra_portal_logo_login`** | `0` | **`0`** | site | `portal/index.php:610,615` | Portal |
| **`secondary_portal_logo_position`** | `second` | **`second`** | site | `portal/index.php:606,614` | Portal |
| **`portal_primary_menu_logo_height`** | `30` | **`30`** | site | `portal/home.php:374` | Portal |
| **`statement_logo`** | `practice_logo.gif` | **`practice_logo.gif`** | site | `sites/default/statement.inc.php:86-87` | PDF |
| `portal_onsite_two_address` | — | **`https://your_web_site.com/openemr/portal`** | site | portal | Portal |
| `portal_onsite_two_enable` | `0` | **`0`** — portal **disabled** | site | portal | Portal |

¹ `style_default.css` **does not exist on disk** — caught by the `file_exists()` gate at
`interface/globals.php:476` which forces `style_light.css`. Latent, not live (§7.3).

**Bold** rows = globals **Group 1A never identified**. That is **11 previously-unknown branding globals.**

Section counts, machine-derived from `library/globals.inc.php`:

| Globals section | Total globals | Branding-relevant |
|---|---:|---:|
| `Branding` | **10** | 10 |
| `Login Page` | **14** | 14 |
| `Portal` | 22 | **5** |
| `Appearance` | 28 | **3** |
| `PDF` | 17 | **1** (`statement_logo`) |
| **Total branding-relevant globals** | | **33** |

Other DB facts: `facility` table holds one row — **`Your Clinic Name Here`** (placeholder, appears on
statements/reports). `user_settings` contains **no** `css_header`/theme override rows. `lang_definitions`
= 237,509 rows.

*No password, secret, token, PHI or unrelated sensitive value was read or is reproduced here.*

---

## 4. What Group 1A got right (preserved, re-verified)

Retained because independently re-confirmed:

- **The branding subsystem is real and first-class.** A dedicated `Branding` globals section, a
  `Login Page` section, and `LogoService` with per-site override, extension-agnostic `logo.*` lookup,
  `?t=<mtime>` cache-busting, and a `LogoFilterEvent` module hook.
- **Site logo wins over shipped default** — `$siteDir` appended after `$publicDir`, last-match-wins loop
  (`LogoService.php:141-159`). Runtime-confirmed.
- **`#2d9bd6` exists only inside raster images**, in no source file. Logos and themes are decoupled.
- **`sites/<tenant>/documents/theme/` has no runtime behaviour** — consistent with locked Q59.
- **`templates/emails/**` contains no branding markup.**
- **Printed output is facility-branded, not product-branded.**
- **Regulatory assets** (`cms1500.png` 2550×3300, `ub04.svg` 934×1210) and **third-party trademarks**
  (`visa_mc_disc_credit_card_logos_176x35.gif`) are DO-NOT-TOUCH.
- **PHP namespace `OpenEMR\` (3,611 files) and GPL copyright headers are DO-NOT-TOUCH.**
- **Duplicate brand assets** confirmed by blob SHA: `963ff96bfff6` ×3, `752983be1ed3` ×2.

---

## 5. New findings — surfaces Group 1A missed

### 5.0 Product-name consumer coverage is not total

`openemr_name` has 20 consumers, but **7 hardcoded `<title>` tags bypass it**, so setting the global does
**not** rebrand every page.

### 5.1 Hardcoded product-name titles and headings (7 titles + 8 headings)

| Location | String | Runtime status |
|---|---|---|
| `admin.php:40` | `<title>OpenEMR Site Administration</title>` | ✅ **verified live, unauthenticated** |
| `admin.php:53` | `<h2>OpenEMR Multi Site Administration</h2>` | ✅ verified live |
| `setup.php:145` / `:356` | `<title>OpenEMR Setup Tool</title>` | static |
| `setup.php:160` / `:452` | `<a class="navbar-brand">OpenEMR Setup</a>` | static |
| `setup.php:976` | `<legend>OpenEMR Initial User Details</legend>` | static |
| `setup.php:522,524,526,1530,1747` | body copy naming OpenEMR | static |
| `sql_patch.php:47` | `<title>OpenEMR … Database Patch</title>` | static |
| `sql_patch.php:54` / `:106` | large centred `OpenEMR` version banner | static |
| `sql_upgrade.php:128` | `<title>OpenEMR Database Upgrade</title>` | static |
| `sql_upgrade.php:313` | `<h2>OpenEMR Database Upgrade</h2>` | static |
| **`ippf_upgrade.php:104`** | `<title>OpenEMR IPPF Upgrade</title>` | **file never mentioned by Group 1A** |

### 5.2 An entire Portal branding globals group (5 globals + own render block)

`portal/index.php:606-620` is a **self-contained portal logo renderer** with its own globals —
structurally parallel to the login page, and entirely absent from Group 1A:

```php
if ($globalsBag->get('secondary_portal_logo_position') === 'second') {
    if ($globalsBag->getBoolean('show_portal_primary_logo')) { ... }
    if ($globalsBag->getBoolean('extra_portal_logo_login'))  { ... }
} elseif ($globalsBag->get('secondary_portal_logo_position') === 'first') { ... }
```

Plus `portal_primary_menu_logo_height` (`portal/home.php:374`, default `30`) and `portal_css_header`.

### 5.3 A referenced-but-missing login template (dead branch)

`templates/login/layouts/vertical_box.html.twig:32-36`:

```twig
{% if displayTinyLogo %}
    <div class="mb-5">
        {% include "login/partials/html/tiny_logo.html.twig" %}
    </div>
{% endif %}
```

- `tiny_logo.html.twig` **does not exist** — not in git, not on the filesystem.
- `displayTinyLogo` is **never assigned** anywhere: `git grep displayTinyLogo HEAD` returns exactly one hit — this line.
- Twig is constructed `new Environment($twigLoader, ['autoescape' => false])` (`TwigContainer.php:70`) — **`strict_variables` is off**, so an undefined variable is falsy.

**Conclusion: dead code, NOT a live defect.** The working mini-logo path is
`tiny_logo_1/2` → `$displaySmallLogo` (`login.php:201-209`) → `small_logo.html.twig` (exists).
Group 1A missed both the dead branch and the working path.

### 5.4 `statement_logo` — a branding global driving printed output

`library/globals.inc.php:1372`, live value `practice_logo.gif`, consumed at
`sites/default/statement.inc.php:86-87` via `convert_safe_file_dir_name()`. Group 1A described the
statement logo as a bare filesystem path and missed that it is **configurable**.

### 5.5 Error pages — a wholly missed category (5 strings)

| File:line | String | Visibility |
|---|---|---|
| `templates/error/400.html.twig:3` | `"OpenEMR 400 Error"` | human |
| `templates/error/404.html.twig:3` | `"OpenEMR 404 Error"` | human |
| `templates/error/general_http_error.html.twig:4` | `"OpenEMR Error"` | human |
| `templates/error/400.json.twig:4` | `"OpenEMR 400 Error"` in JSON `location` | **machine/API** |
| `templates/error/404.json.twig:4` | `"OpenEMR 404 Error"` in JSON `location` | **machine/API** |

All `|xlt`/`|xl` wrapped → translatable → SAFE-CONFIG.

### 5.6 Third-party sponsor branding on a publicly reachable page

`acknowledge_license_cert.html` — **`HTTP 200` unauthenticated, 24,739 bytes**, linked from both the
login page and the About page. It carries OpenEMR Foundation branding **and** at least **8 third-party
company identities** with websites, social handles and **personal email addresses**:

`OpenEMR Foundation` · `OpenCoreEMR Inc.` (site, GitHub, X/@openCoreEMR, LinkedIn, Bluesky, two personal
emails) · `nahahealthclinic.com` · `discoverandchange.com` · `infeg.com` ·
`affordablecustomehr.com` · `openplanit.com` / `openplanit.ie` · `github.com/epsdky`

Separately, `opencoreemr.com` appears **530 times as `@author` docblocks** across 553 files — contributor
attribution, **not** user-visible, same class as `@link` headers (**DO-NOT-TOUCH**, GPL attribution).

### 5.7 Machine-facing identity in HTTP headers and session names

**Runtime-verified:** every response carries **two** identity cookies —
`Set-Cookie: OpenEMR=<session-id>` (the PHP **session cookie name** is the product name) and
`Set-Cookie: App=OpenEMR` (a generic cookie name whose **value** is the product name).

`src/Common/Session/SessionUtil.php:81-90` defines **six** session/application identity constants —
**five session-name identifiers plus the application cookie name**:

| # | Constant | Value | Kind | Set at |
|---|---|---|---|---|
| 1 | `CORE_SESSION_ID` | `OpenEMR` | session name → becomes the **cookie name** via `session_name()` | main app; `login.php:49` |
| 2 | `PORTAL_SESSION_ID` | `PortalOpenEMR` | session name | `portal/index.php:44` |
| 3 | `OAUTH_SESSION_ID` | `authserverOpenEMR` | session name | OAuth2 flows |
| 4 | `API_SESSION_ID` | `apiOpenEMR` | session name | REST/FHIR |
| 5 | `SETUP_SESSION_ID` | `setupOpenEMR` | session name | installer |
| 6 | **`APP_COOKIE_NAME`** | `App` | **cookie name** (generic); its *value* carries the product identity | `setAppCookie()` `:258-272` |

**Cookie name vs value — the distinction that matters for rebranding:** constant 1 puts the product name
in a **cookie name** (`OpenEMR=<sid>`); constant 6 puts it in a **cookie value** (`App=OpenEMR`).
Constants 2–5 are session names for other entry points. Full classification and the recommendation to
leave all six unchanged is in §13; canonical items are BRAND-089–093 and BRAND-131.

> **HISTORICAL — Group 1B undercount:** an earlier generation of this report described this as *"five
> product-identity constants"*, omitting `APP_COOKIE_NAME`. Superseded by **K-02**; the correct count is
> **6 constants + 2 runtime identity cookies**.

> ⚠️ **Interacts with locked Q17**, which says to accept one-tenant-per-browser Day-1 and patch
> `SessionUtil` only if Q16 flips. Renaming these for branding would touch the same code Q17 governs.
> **Flagged, not actioned.**

### 5.8 `config/config.yaml` — an asset/theme registry Group 1A never opened

Maps themes and assets by global substitution (`%KEY%` → `$GLOBALS['KEY']`):

```yaml
main-theme:    { alreadyBuilt: true, link: '%css_header%',        autoload: true }
compact-theme: { alreadyBuilt: true, link: '%compact_header%',    autoload: true }
tabs-theme:    { basePath: '%webroot%/public/themes/', link: '%theme_tabs_layout%',
                 rtl: { link: 'rtl_%theme_tabs_layout%' } }
portal-theme:  { alreadyBuilt: true, link: '%portal_css_header%' }
pdf-style:     { basePath: '%webroot%/public/themes/', link: style_pdf.css }
```

This is the **canonical theme→asset binding** and a required read for any theme rebranding.

### 5.9 Duplicate favicon `<link>` emission

Runtime login HTML contains `<link rel="shortcut icon" …>` **twice** (identical href). Cosmetic; likely
`Header::setupHeader()` invoked alongside a template-level emission. Low priority.

### 5.10 Empty `alt` on the primary login logo

Runtime: `<img src="…/logo.png?t=…" class="img-fluid" alt="">`. No accessible brand name and no text
fallback if the image fails. Accessibility + branding gap.

---

## 6. Bidirectional asset analysis

### Direction A — source → asset (9 lookups → resolved files)

| # | Lookup type | Caller(s) | Resolved default | Present? |
|---|---|---|---|---|
| 1 | `core/login/primary` | `login.php:62`; `SMARTAuthorizationController.php:427`; telehealth mailer `:92` | `…/core/login/primary/logo.png` | ✅ |
| 2 | `core/login/secondary` | `login.php:63` | `…/core/login/secondary/logo.png` | ✅ |
| 3 | `core/login/small_logo_1` | `login.php:64` | `…/small_logo_1/logo.png` | ✅ |
| 4 | `core/login/small_logo_2` | `login.php:65` | `…/small_logo_2/logo.png` | ✅ |
| 5 | `core/menu/primary` | `main.php:48` | `…/core/menu/primary/logo.svg` | ✅ |
| 6 | `core/favicon` (`favicon.ico`) | `Header.php:138` | `…/core/favicon/favicon.ico` | ✅ runtime-verified |
| 7 | `portal/login/primary` | `portal/index.php:63` | `…/portal/login/primary/logo.png` | ✅ |
| 8 | **`portal/login/secondary`** | `portal/index.php:64` | — | ❌ **no default, no directory** |
| 9 | `portal/menu/primary` | `portal/home.php:362` | `…/portal/menu/primary/logo.png` | ✅ |

Plus a generic Twig accessor `getLogo(type, filename)` (`TwigExtension.php:274-279`) allowing any
template to resolve any slot.

### Direction B — asset → consumer (classification)

| Asset | Classification | Consumers |
|---|---|---|
| `logos/core/login/primary/logo.png` | **actively used** | login (runtime-verified), SMART authorize, telehealth email |
| `logos/core/login/secondary/logo.png` | **conditionally used** | `extra_logo_login=0` today |
| `logos/core/login/small_logo_{1,2}/logo.png` | **conditionally used** | `tiny_logo_{1,2}=0` today |
| `logos/core/menu/primary/logo.svg` | **actively used** | main navbar (`display_main_menu_logo=1`) |
| `logos/core/favicon/favicon.ico` | **actively used** | runtime-verified |
| `logos/portal/login/primary/logo.png` | **portal only** | `portal_onsite_two_enable=0` in the baseline; **render runtime-verified** via controlled enable (§12.2 G-01) |
| `logos/portal/menu/primary/logo.png` | **portal only** | same; post-auth render is acceptance test A6 |
| `public/images/login-logo.png` | **legacy but reachable** | duplicate blob; legacy path |
| `public/images/logo-full-con.png` | **legacy but reachable** | duplicate blob |
| `public/images/menu-logo.png` | **legacy but reachable** | contains `#2d9bd6` |
| `public/images/favicon-32x32.png` | **legacy but reachable** | contains `#2d9bd6` |
| `sites/default/images/login_logo.gif` | **legacy but reachable** | `globals.php:688`; Eye-Magic hardcodes `sites/default` |
| `sites/default/images/logo_{1,2}.png` | **conditionally used** | `tiny_logo_*` + `register-app.php:396-402` |
| `sites/default/images/practice_logo.gif` | **conditionally used** (not shipped) | `statement_logo` global |
| `public/images/stylesheets/style_*.png` (22) | **installer/setup only** | installer theme picker; **16 orphaned** |
| `public/images/cms1500.png`, `ub04.svg` | **regulatory asset** | DO-NOT-TOUCH |
| `visa_mc_disc_credit_card_logos_176x35.gif` | **third-party trademark** | DO-NOT-TOUCH |
| `swagger/favicon-{16,32}.png` | **documentation only** | Swagger UI (unauthenticated) |
| `interface/modules/zend_modules/public/images/favicon.ico`, `zf2-logo.png` | **module-specific / legacy** | legacy Zend UI |
| 368 untracked `public/assets/**` images | **third-party** | npm libraries; **verified brand-free** |

---

## 7. Reconciliation of Group 1A inconsistencies

### 7.1 (A) Image count — 1,028 vs 1,008 → **1,008 is correct**

| Extension | Count |
|---|---:|
| `.svg` | 504 |
| `.png` | 359 |
| `.gif` | 115 |
| `.jpg` | 26 |
| `.ico` | 3 |
| `.bmp` | 1 |
| **Total tracked** | **1,008** |

**Why they differed:** the Group 1A executive summary added the **20 `.woff` font files** to the image
total (1,008 + 20 = 1,028). §9.1 of that same report already carried the correct 1,008. A transcription
error, not a measurement disagreement.

**Additional figure Group 1A never had:** **368 untracked images on disk** (all under `public/assets/`,
npm-installed, gitignored). All verified third-party — 156 have brand-ish names (`favicon.ico`,
`ui-icons_*.png`) but every one belongs to DataTables or jQuery UI. **Working-tree image total: 1,376.**

### 7.2 (B) "Only eight patches" → Group 1B reconciliation

> **HISTORICAL / SUPERSEDED.** This subsection records the Group 1B reconciliation of the Group 1A claim.
> It was itself re-derived in Group 1D across all 136 items. **The authoritative patch inventory is
> §15** (8 mandatory / 5 conditional). Do not implement from this subsection.

Group 1A's executive summary and §7.5 listed overlapping but non-identical sets. One canonical list:

| # | Item | Location | True classification | Was in 1A? |
|---|---|---|---|---|
| 1 | FHIR `software.name` hardcoded | `FhirMetaDataRestController.php:85` | **NEEDS-PATCH** | ✅ |
| 2 | Registration phone-home | `ProductRegistrationService.php:121` | **NEEDS-PATCH** | ✅ |
| 3 | `public/images/favicon.ico` missing (×5 refs) | `admin.php`, `setup.php`×2, `sql_patch.php`, `sql_upgrade.php` | **SAFE-ASSET** (drop one file) — *1A listed it as a patch* | ✅ (misclassified) |
| 4 | Review link → softwareadvice.com | `about.html.twig:68` | **SAFE-CONFIG** to hide / NEEDS-PATCH to repoint | ✅ (misclassified) |
| 5 | Donate link → open-emr.org | `about.html.twig:76` | **SAFE-CONFIG** to hide / NEEDS-PATCH to repoint | ✅ (misclassified) |
| 6 | `$openemr_name='OpenEMR'` bootstrap fallback | `interface/globals.php:633` | **NEEDS-PATCH** (low) | ✅ |
| 7 | Eye-Magic hardcodes `sites/default` | `eye_mag/help.php:45`, `eye_mag_functions.php:4033` | **NEEDS-PATCH** (low) | ✅ |
| 8 | HL7 MSH sender = uppercased product name | `non_reported.php:162` | **NOT A DEFECT** — intentional; follows `openemr_name` | ✅ (mislabelled) |
| 9 | **7 hardcoded `<title>`/heading blocks** | `admin.php`, `setup.php`, `sql_patch.php`, `sql_upgrade.php`, `ippf_upgrade.php` | **NEEDS-PATCH** | ❌ **new** |
| 10 | **5 error-page brand strings** | `templates/error/*.twig` | **SAFE-CONFIG** (translatable) | ❌ **new** |
| 11 | **6 session/cookie identity constants** (Group 1C corrected from 5) | `SessionUtil.php:81-90` | **NEEDS-PATCH** — *constrained by locked Q17; §13 recommends leaving unchanged* | ❌ **new** |
| 12 | **`style_default.css` missing** | `interface/globals.php:634` | **NOT A DEFECT** — `file_exists()` gate catches it | ❌ **new** |
| 13 | **Dead `tiny_logo.html.twig` include** | `vertical_box.html.twig:32` | **NOT A DEFECT** — unreachable branch | ❌ **new** |
| 14 | **Duplicate favicon `<link>`** | runtime | **NEEDS-PATCH** (cosmetic) | ❌ **new** |
| 15 | **Empty `alt` on login logo** | runtime / `primary_logo.html.twig` | **NEEDS-PATCH** (a11y) | ❌ **new** |

**Genuine NEEDS-PATCH total = 11** (items 1, 2, 6, 7, 9, 11, 14, 15 → 8 distinct code areas; plus items
4 and 5 if repointing rather than hiding, and item 3 if fixed in code rather than by asset drop).

**Stated canonically: 8 mandatory code patches; 3 further items become patches only if you choose the
patch route over the config/asset route.**

### 7.3 `style_default.css` — assessed, not a defect

`interface/globals.php:634` sets the bootstrap default to `style_default.css`, which does **not** exist
in `public/themes/` (44 files verified). The `file_exists()` gate at `:476` forces `style_light.css`.
Live DB value is already `style_light.css`. **Latent only.**

### 7.4 (D) Logo-slot count → **9 distinct runtime lookup types**

Group 1A said "seven" while enumerating `7`, `7a`, `7b`. Correct enumeration is the 9 in §6 Direction A:
4 core-login + 1 core-menu + 1 favicon + 2 portal-login + 1 portal-menu = **9**.
**8 have shipped defaults; `portal/login/secondary` has none.**

### 7.5 (C) Portal logo path reconciliation

| Path | Read by code? | Default asset? | Site README? | Verdict |
|---|---|---|---|---|
| `portal/login/primary` | ✅ `portal/index.php:63` | ✅ | ❌ | **Valid** — replacement target |
| `portal/login/secondary` | ✅ `portal/index.php:64` | ❌ | ❌ | **Valid but unprovisioned** — needed if `extra_portal_logo_login=1` |
| `portal/menu/primary` | ✅ `portal/home.php:362` | ✅ | ✅ | **Valid** — replacement target |
| `portal/primary` | ❌ **never read** | ❌ | ✅ `README.md` | **TRAP** — misleading placeholder |
| `portal/secondary` | ❌ **never read** | ❌ | ✅ `README.md` | **TRAP** — misleading placeholder |

Confirmed: `sites/default/images/logos/` ships READMEs for `portal/primary` and `portal/secondary`
(which no code reads) while shipping **no** README for `portal/login/primary` or
`portal/login/secondary` (which code does read).

### 7.6 (E) Phase-2 replacement inventory — items Group 1A's list omitted

| Missing item | Why it matters |
|---|---|
| `portal/login/secondary` | Referenced by code; no default asset anywhere |
| `sites/<site>/images/logo_1.png`, `logo_2.png` | Reachable via `tiny_logo_*` and `register-app.php:396-402` |
| `public/images/login-logo.png` (legacy dup) | Same blob as core login primary; separate path |
| `public/images/logo-full-con.png` (legacy dup) | Same blob as portal menu |
| `public/images/menu-logo.png` | Contains `#2d9bd6` |
| `public/images/favicon-32x32.png` | Contains `#2d9bd6` |
| `public/images/favicon.ico` | **Does not exist**; 5 refs → HTTP 404 |
| `statement_logo` global value | Configurable, not just a path |
| 22 installer theme screenshots | Product-branded UI shown during setup |

---

## 8. Outbound network identity audit (broad-pattern)

Method: searched `curl_init`, `GuzzleHttp\Client`, `new Client(`, `HttpClient::create`,
`file_get_contents('http…')`, `fopen('http…')`, JS `fetch`/`XMLHttpRequest` across first-party code
(excluding `tests/`, `vendor/`, `.phpstan/`). **29 first-party HTTP call sites** identified.

External hosts hardcoded in first-party code (by frequency):

| Host | Occurrences | Nature | Branding-relevant? |
|---|---:|---|---|
| `github.com` | 2,272 | `@license` docblock URLs | ❌ docblock |
| `www.open-emr.org` | 1,366 | mostly `@link` docblocks | ⚠️ partly (§13 of 1A) |
| `www.hl7.org` | 932 | FHIR spec URLs | ❌ standards |
| **`opencoreemr.com`** | 202 | 530 `@author` docblocks + acknowledgements page | ⚠️ page only |
| `medexbank.com`, `emrdirect.com`, `newcropaccounts.com` | 14 | third-party service integrations | ❌ vendor, not upstream brand |

**Upstream-product outbound calls with a hardcoded endpoint: exactly one.**

| Endpoint | Location | Trigger | Data | Default | Config | Relevance |
|---|---|---|---|---|---|---|
| `https://reg.open-emr.org/api/registration` | `ProductRegistrationService.php:121` | Product registration action | Registration email + install UUID | Opt-in (user action) | none | **HIGH** — transmits to OpenEMR project infra |

Consumed by `about_page.php:44` via `getRegistrationEmail()`. No telemetry, update-check or
version-check endpoint exists. No remote image/script/style references to upstream hosts.

*No secret, token or PHI was read or transmitted during this audit.*

---

## 9. CANONICAL MASTER BRANDING INVENTORY

Stable IDs for implementation. Isolation ratings: **SAFE-CONFIG** (DB/admin) · **SAFE-ASSET** (per-site
file drop) · **SAFE-OVERLAY** (module event) · **NEEDS-PATCH** (tracked file) · **DO-NOT-TOUCH**.

`Vis` = H human-visible · M machine-visible · I internal.
`Trk` = tracked-file modification required.

### 9.1 Product name and titles

| ID | Item | Vis | Surface | Current value | Source | Consumers | MVP? | Mechanism | Isolation | Trk | Rebase risk | Verify |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| BRAND-001 | `openemr_name` global | H | app-wide | `OpenEMR` | `globals.inc.php:437` / DB `globals` | 20 sites | **YES** | Admin→Globals→Branding | SAFE-CONFIG | NO | none | DB+runtime ✅ |
| BRAND-002 | Login page `<title>` | H | login | `OpenEMR Login` | `base.html.twig:24` | — | YES | via BRAND-001 + `xlt("Login")` | SAFE-CONFIG | NO | none | runtime ✅ |
| BRAND-003 | Main app `<title>` | H | shell | `OpenEMR` | `main.php:102` | — | YES | via BRAND-001 | SAFE-CONFIG | NO | none | source ✅ |
| BRAND-004 | JS window-title base | H | shell | `OpenEMR` | `main.php:153` | — | YES | via BRAND-001 | SAFE-CONFIG | NO | none | source ✅ |
| BRAND-005 | `admin.php` title | H | multisite admin | `OpenEMR Site Administration` | `admin.php:40` | — | YES | hardcoded | **NEEDS-PATCH** | YES | low | runtime ✅ |
| BRAND-006 | `admin.php` heading | H | multisite admin | `OpenEMR Multi Site Administration` | `admin.php:53` | — | YES | hardcoded | **NEEDS-PATCH** | YES | low | runtime ✅ |
| BRAND-007 | `setup.php` titles ×2 | H | installer | `OpenEMR Setup Tool` | `setup.php:145,356` | — | NO¹ | hardcoded | **NEEDS-PATCH** | YES | low | source ✅ |
| BRAND-008 | `setup.php` navbar-brand ×2 | H | installer | `OpenEMR Setup` | `setup.php:160,452` | — | NO¹ | hardcoded | **NEEDS-PATCH** | YES | low | source ✅ |
| BRAND-009 | `setup.php` legend + body copy | H | installer | 5 strings | `setup.php:522,524,526,976,1530,1747` | — | NO¹ | hardcoded | **NEEDS-PATCH** | YES | low | source ✅ |
| BRAND-010 | `sql_patch.php` title + banner | H | DB patch | `OpenEMR … Database Patch` | `sql_patch.php:47,54,106` | — | NO¹ | hardcoded | **NEEDS-PATCH** | YES | low | source ✅ |
| BRAND-011 | `sql_upgrade.php` title + `<h2>` | H | DB upgrade | `OpenEMR Database Upgrade` | `sql_upgrade.php:128,313` | — | NO¹ | hardcoded | **NEEDS-PATCH** | YES | low | source ✅ |
| BRAND-012 | `ippf_upgrade.php` title | H | IPPF upgrade | `OpenEMR IPPF Upgrade` | `ippf_upgrade.php:104` | — | NO¹ | hardcoded | **NEEDS-PATCH** | YES | low | source ✅ |
| BRAND-013 | `window_title_add_patient_name` | H | shell | `0` | `globals.inc.php:209` / DB | title composer | NO | Admin→Appearance | SAFE-CONFIG | NO | none | DB ✅ |

¹ Operator-only surfaces; MVP-optional but recommended before customer-facing installs.

### 9.2 Logos (9 runtime lookups)

| ID | Slot | Vis | Default asset | Bytes | Dim | Colours | Consumers | MVP? | Isolation | Trk | Verify |
|---|---|---|---|---|---:|---|---|---|---|---|---|
| BRAND-014 | `core/login/primary` | H | `…/core/login/primary/logo.png` | 35,917 | **1053×390** | full-colour | `login.php:62`; SMART `:427`; telehealth mail `:92` | **YES** | SAFE-ASSET | NO | runtime ✅ |
| BRAND-015 | `core/login/secondary` | H | `…/secondary/logo.png` | 2,245 | **300×100** | `#e5e5e5 #a3a3a3 #b3b3b3 #757575` | `login.php:63` | YES | SAFE-ASSET | NO | source ✅ |
| BRAND-016 | `core/login/small_logo_1` | H | `…/small_logo_1/logo.png` | 1,708 | **101×100** | `#e5e5e5 #757575 #a3a3a3` | `login.php:64` | COND | SAFE-ASSET | NO | source ✅ |
| BRAND-017 | `core/login/small_logo_2` | H | `…/small_logo_2/logo.png` | 1,795 | **101×100** | `#e5e5e5 #757575 #a3a3a3` | `login.php:65` | COND | SAFE-ASSET | NO | source ✅ |
| BRAND-018 | `core/menu/primary` | H | `…/core/menu/primary/logo.svg` | 1,532 | **287×287** viewBox | `blue` | `main.php:48` — rendered at **fixed `height="16"`** | **YES** | SAFE-ASSET | NO | source ✅ |
| BRAND-019 | `core/favicon` | H | `…/core/favicon/favicon.ico` | 15,086 | **48+32+16** | — | `Header.php:138` | **YES** | SAFE-ASSET | NO | runtime ✅ |
| BRAND-020 | `portal/login/primary` | H | `…/portal/login/primary/logo.png` | 35,917 | **1053×390** | dup of BRAND-014 | `portal/index.php:63` | YES | SAFE-ASSET | NO | source ✅ |
| BRAND-021 | **`portal/login/secondary`** | H | **NONE — absent** | — | — | — | `portal/index.php:64` | COND | SAFE-ASSET | NO | **gap** ⚠️ |
| BRAND-022 | `portal/menu/primary` | H | `…/portal/menu/primary/logo.png` | 30,769 | **870×222** | wordmark | `portal/home.php:362` | YES | SAFE-ASSET | NO | source ✅ |
| BRAND-023 | Twig `getLogo()` accessor | I | — | — | — | — | `TwigExtension.php:274-279` | — | SAFE-OVERLAY | NO | source ✅ |
| BRAND-024 | `LogoFilterEvent` hook | I | — | — | — | — | `LogoService.php:100-107` | — | SAFE-OVERLAY | NO | source ✅ |

### 9.3 Legacy / duplicate / orphaned assets

| ID | Asset | Bytes | Dim | Colours | Classification | Consumers | MVP? | Isolation |
|---|---|---:|---|---|---|---|---|---|
| BRAND-025 | `public/images/login-logo.png` | 35,917 | 1053×390 | — | legacy dup (blob `963ff96bfff6` ×3) | legacy path | YES | SAFE-ASSET |
| BRAND-026 | `public/images/logo-full-con.png` | 30,769 | 870×222 | — | legacy dup (blob `752983be1ed3` ×2) | legacy path | YES | SAFE-ASSET |
| BRAND-027 | `public/images/menu-logo.png` | 18,754 | 287×287 | **`#2d9bd6`** | legacy but reachable | legacy | YES | SAFE-ASSET |
| BRAND-028 | `public/images/favicon-32x32.png` | 1,973 | 32×32 | **`#2d9bd6`** | legacy but reachable | legacy | YES | SAFE-ASSET |
| BRAND-029 | **`public/images/favicon.ico`** | — | — | — | **MISSING → HTTP 404** | `admin.php:46`, `setup.php:150,362`, `sql_patch.php:48`, `sql_upgrade.php:130` | **YES** | SAFE-ASSET (drop file) |
| BRAND-030 | `sites/default/images/login_logo.gif` | 10,112 | 250×221 | GIF-256 | legacy but reachable | `globals.php:688`; eye_mag ×2 (hardcodes `sites/default`) | YES | SAFE-ASSET + NEEDS-PATCH |
| BRAND-031 | `sites/default/images/logo_1.png` | 357 | 86×43 | `#000000 #ffffff` | conditionally used | `globals.php:691`; `register-app.php:396` | COND | SAFE-ASSET |
| BRAND-032 | `sites/default/images/logo_2.png` | 395 | 86×43 | **`#5030f0`** purple | conditionally used | `globals.php:692`; `register-app.php:398` | COND | SAFE-ASSET |
| BRAND-033 | `sites/<site>/images/practice_logo.gif` | — | — | — | not shipped; referenced | `statement.inc.php:86-87`; eye_mag `:4345-4365` | **YES** | SAFE-ASSET |
| BRAND-034 | `public/images/review-logo.png` / `.svg` | 30,549 / 37,706 | 280×109 / 400×155 | `#003a5f #00395e` | legacy but reachable | review mark | NO | SAFE-ASSET |
| BRAND-035 | 22 × `public/images/stylesheets/style_*.png` | 7.7–42 KB | 501×489 / 789×495 | incl. `#2d9bd6` | **installer/setup only; 16 orphaned** | `Installer.class.php:1893,1925,1975,2011`; `setup.php:1936` | NO | SAFE-ASSET |
| BRAND-036 | `swagger/favicon-{16,32}x*.png` | — | 16/32 | — | documentation only (**unauthenticated**) | Swagger UI | NO | SAFE-ASSET |
| BRAND-037 | `zend_modules/public/images/{favicon.ico,zf2-logo.png}` | 1,406 / 738 | 16×16 / 31×15 | `#68b604` | module-specific legacy | legacy Zend UI | NO | SAFE-ASSET |
| BRAND-038 | `public/images/cms1500.png` | 2,036,937 | 2550×3300 | — | **regulatory asset** | claim form | **NO** | **DO-NOT-TOUCH** |
| BRAND-039 | `public/images/ub04.svg` | 146,742 | 934×1210 | `#231f20 #e7e8e9` | **regulatory asset** | claim form | **NO** | **DO-NOT-TOUCH** |
| BRAND-040 | `visa_mc_disc_credit_card_logos_176x35.gif` | 1,852 | 176×35 | — | **third-party trademark** | payment screens | **NO** | **DO-NOT-TOUCH** |
| BRAND-041 | 368 untracked `public/assets/**` images | — | — | — | **third-party** (verified brand-free) | npm libs | NO | n/a |

### 9.4 Login screen configuration (14 globals)

| ID | Global | Live value | Consumer | MVP? | Isolation |
|---|---|---|---|---|---|
| BRAND-042 | **`login_tagline_text`** | *"The most popular open-source EHR…"* | `tagline.html.twig` | **YES — highest visibility** | SAFE-CONFIG |
| BRAND-043 | `show_tagline_on_login` | `1` | idem | YES | SAFE-CONFIG |
| BRAND-044 | `login_page_layout` | `vertical_band` (of 6) | layout selector | YES | SAFE-CONFIG |
| BRAND-045 | `primary_logo_width` / `secondary_logo_width` | `w-50` / `w-50` | login | YES | SAFE-CONFIG |
| BRAND-046 | `logo_position` | `flex-column` | login | YES | SAFE-CONFIG |
| BRAND-047 | `show_primary_logo` / `extra_logo_login` / `secondary_logo_position` | `1` / `0` / `second` | login | YES | SAFE-CONFIG |
| BRAND-048 | `show_labels_on_login_form` / `show_label_login` | `1` / `0` | login | NO | SAFE-CONFIG |
| BRAND-049 | `tiny_logo_1` / `tiny_logo_2` | `0` / `0` | `login.php:201-209` → `small_logo.html.twig` | COND | SAFE-CONFIG |
| BRAND-050 | `display_acknowledgements_on_login` | `1` | acknowledgements link | YES | SAFE-CONFIG |
| BRAND-051 | 25 login Twig partials (class-list overridable) | — | `templates/login/**` | NO | SAFE-OVERLAY |
| BRAND-052 | Dead `tiny_logo.html.twig` include | unreachable | `vertical_box.html.twig:32` | NO | **not a defect** |
| BRAND-053 | Empty `alt=""` on primary logo | runtime-verified | `primary_logo.html.twig` | YES | NEEDS-PATCH (a11y) |

### 9.5 Header / menu / About / external links

| ID | Item | Live value | Source | MVP? | Isolation |
|---|---|---|---|---|---|
| BRAND-054 | `display_main_menu_logo` | `1` | `globals.inc.php:444` | YES | SAFE-CONFIG |
| BRAND-055 | **`main_menu_logo_link`** | **`https://www.open-emr.org/`** | `:451`; `main.php:489` | **YES** | SAFE-CONFIG |
| BRAND-056 | **`main_menu_logo_title`** | `''` → renders `title=""` (**runtime-verified empty**, not the fallback — correction K-01) | `:458`; `main.php:490` | **YES** | SAFE-CONFIG |
| BRAND-057 | **`online_support_link`** | `http://open-emr.org/` (plain HTTP) | `:465` | **YES** | SAFE-CONFIG |
| BRAND-058 | **`user_manual_link`** | `''` → auto-generates open-emr.org wiki URL | `:472`; `about_page.php:38-40` | **YES** | SAFE-CONFIG |
| BRAND-059 | `support_phone_number` | `''` | `:479` | YES | SAFE-CONFIG |
| BRAND-060 | **`display_review_link`** | `1` → softwareadvice.com/product/68077-openemr | `:493`; `about.html.twig:68` | **YES** | SAFE-CONFIG (hide) |
| BRAND-061 | **`display_donations_link`** | `1` → open-emr.org/donate | `:500`; `about.html.twig:76` | **YES** | SAFE-CONFIG (hide) |
| BRAND-062 | `display_acknowledgements` | `1` | `:486` | YES | SAFE-CONFIG |
| BRAND-063 | `acknowledge_license_cert.html` | **HTTP 200 unauthenticated**, 24,739 B; OpenEMR Foundation + 8 third-party companies + personal emails | repo root | **YES** (review) | **DO-NOT-TOUCH** w/o counsel |

### 9.6 Portal (Q32: in MVP scope)

| ID | Item | Live value | Source | MVP? | Isolation |
|---|---|---|---|---|---|
| BRAND-064 | `portal_onsite_two_enable` | **`0` — portal disabled** | `globals.inc.php:3073` | YES | SAFE-CONFIG |
| BRAND-065 | **`portal_css_header`** | `style_light.css` | `:3087`; `config.yaml:76` | YES | SAFE-CONFIG |
| BRAND-066 | **`show_portal_primary_logo`** | `1` | `:3214`; `portal/index.php:607,618` | YES | SAFE-CONFIG |
| BRAND-067 | **`extra_portal_logo_login`** | `0` | `:3221`; `portal/index.php:610,615` | COND | SAFE-CONFIG |
| BRAND-068 | **`secondary_portal_logo_position`** | `second` | `:3228`; `portal/index.php:606,614` | COND | SAFE-CONFIG |
| BRAND-069 | **`portal_primary_menu_logo_height`** | `30` | `:3144`; `portal/home.php:374` | YES | SAFE-CONFIG |
| BRAND-070 | **`portal_onsite_two_address`** | **`https://your_web_site.com/openemr/portal`** | `:3080` | **YES** | SAFE-CONFIG |
| BRAND-071 | Portal login legend | `<name> Portal Login` | `portal/index.php:623` | YES | via BRAND-001 |
| BRAND-072 | Portal page title | `<name> Portal` | `portal/home.php:375` | YES | via BRAND-001 |
| BRAND-073 | **Trap dirs** `logos/portal/{primary,secondary}` | READMEs shipped, **never read** | `sites/default/images/logos/` | — | documentation fix |

### 9.7 Themes, colours, fonts

| ID | Item | Value | Source | MVP? | Isolation |
|---|---|---|---|---|---|
| BRAND-074 | `css_header` | `style_light.css` | DB; `globals.php:437-483` | YES | SAFE-CONFIG |
| BRAND-075 | `theme_tabs_layout` | `tabs_style_full.css` | DB; `config.yaml:67` | YES | SAFE-CONFIG |
| BRAND-076 | Compiled themes on disk | **44 CSS files** (34 + 10 `misc/`) | `public/themes/` (**gitignored**) | YES | Build-time |
| BRAND-077 | Theme SCSS sources | 78 files; 6 buildable themes | `interface/themes/**` | YES | Build-time |
| BRAND-078 | Per-theme colour variables | e.g. `$link-color:#1d4ed8`, `$body-bg:#f9fafb` | `oe-styles/style_light.scss:13-41` | YES | Build-time |
| BRAND-079 | **No `$brand-primary` token exists** | — | `theme-defaults.scss` | **YES** | Build-time (**gap vs Q34**) |
| BRAND-080 | Brand blue `#2d9bd6` | **in images only**, 0 source refs | raster assets | YES | SAFE-ASSET |
| BRAND-081 | `config/config.yaml` asset registry | theme→asset bindings | `config/config.yaml:60-78` | YES | NEEDS-PATCH if changed |
| BRAND-082 | RTL theme mechanism | `rtl_<name>.css` filename swap | `globals.php:551-611` | YES | Build-time |
| BRAND-083 | Theme dropdown | **filesystem scan** of `public/themes/` | `edit_globals.php:714-731` | YES | Build-time |
| BRAND-084 | `style_default.css` referenced, absent | caught by `file_exists()` gate | `globals.php:634` / `:476` | NO | not a defect |
| BRAND-085 | Fonts | 78 files, **all** in `Documentation/EHI_Export`; FA from npm | — | NO | Build-time |
| BRAND-086 | **No Arabic-capable font** | absent | — | **YES** (Q18/Q25) | Build-time |

### 9.8 Machine-facing identity

| ID | Item | Value | Source | MVP? | Isolation |
|---|---|---|---|---|---|
| BRAND-087 | **FHIR `software.name`** | hardcoded `"OpenEMR"` | `FhirMetaDataRestController.php:85` | **YES** | **NEEDS-PATCH** |
| BRAND-088 | FHIR `software.version` | **Absent from the runtime response** — `composer.json` has **no `version` key**, so `$composerObj["version"]` is undefined and the field is never emitted (runtime-verified §11.2; correction K-11). The code path reads `composer.json` at runtime but currently yields nothing. | `:81-85` | NO | NO-ACTION |
| BRAND-089 | **`Set-Cookie: App=OpenEMR`** | runtime-verified | `SessionUtil.php:81,90,258`; `login.php:49` | REVIEW | **NEEDS-PATCH** — *locked Q17* |
| BRAND-090 | `PORTAL_SESSION_ID` | `PortalOpenEMR` | `SessionUtil.php:86` | REVIEW | NEEDS-PATCH — *Q17* |
| BRAND-091 | `OAUTH_SESSION_ID` | `authserverOpenEMR` | `:82` | REVIEW | NEEDS-PATCH — *Q17* |
| BRAND-092 | `API_SESSION_ID` | `apiOpenEMR` | `:84` | REVIEW | NEEDS-PATCH — *Q17* |
| BRAND-093 | `SETUP_SESSION_ID` | `setupOpenEMR` | `:88` | REVIEW | NEEDS-PATCH — *Q17* |
| BRAND-094 | `composer.json` name/description/support | `openemr/openemr` + 5 URLs | `composer.json:2-11` | NO | NEEDS-PATCH |
| BRAND-095 | `package.json` name/description | `openemr-interface` | `package.json:2-4` | NO | NEEDS-PATCH |
| BRAND-096 | HL7 MSH-3 sender | uppercased `openemr_name` | `non_reported.php:162` | — | via BRAND-001 |
| BRAND-097 | QRDA Cat III org name | `openemr_name` | `ExportCat3Service.php:430`; `QrdaReportService.php:218` | — | via BRAND-001 |
| BRAND-098 | Swagger UI | `<title>Swagger UI</title>`, **unauthenticated** | `swagger/index.html` | NO | NEEDS-PATCH |
| BRAND-099 | No PWA manifest / `application-name` / `og:` / `theme-color` | **absent (verified)** | — | — | n/a |
| BRAND-100 | `X-Powered-By: PHP/8.3.33`, `Server: Apache` | runtime | webserver config | NO | infra config |

### 9.9 Content, communication, printed

| ID | Item | Value | Source | MVP? | Isolation |
|---|---|---|---|---|---|
| BRAND-101 | 5 error-page brand strings | `"OpenEMR 400/404/Error"` (HTML + **JSON**) | `templates/error/*.twig` | **YES** | SAFE-CONFIG (translatable) |
| BRAND-102 | 62 translatable `OpenEMR` code strings | `xl()`/`xlt()`/`xla()` | 21 files | YES | SAFE-CONFIG |
| BRAND-103 | 924 catalogue lines w/ `OpenEMR` | of 13,234 constants | `currentLanguage_utf8.sql` | YES | SAFE-CONFIG |
| BRAND-104 | `lang_definitions` live | **237,509 rows** | DB | YES | SAFE-CONFIG |
| BRAND-105 | In-app help pages | ~180 wiki links + brand prose | `Documentation/help_files/**` | NO (hide) | NEEDS-PATCH to rewrite |
| BRAND-106 | Email templates (12) | **no branding markup** | `templates/emails/**` | NO | ✅ nothing to do |
| BRAND-107 | Prescription email subject/body | `openemr_name` prefix | `C_Prescription.class.php:1016,1122,1132` | YES | via BRAND-001 |
| BRAND-108 | Telehealth invitation email logo | absolute URL to `core/login/primary` | `TeleHealth…MailerService.php:92` | YES | via BRAND-014 |
| BRAND-109 | `statement_logo` global | `practice_logo.gif` | `globals.inc.php:1372`; `statement.inc.php:86` | **YES** | SAFE-CONFIG |
| BRAND-110 | Facility name (printed) | **`Your Clinic Name Here`** | DB `facility` | **YES** | SAFE-CONFIG |
| BRAND-111 | PDF stylesheets | `style_pdf.scss`, `rtl_style_pdf.css` | `interface/themes/` | YES | Build-time |
| BRAND-112 | Report/receipt logos | facility-scoped | `custom_report.php:202,211`; `cash_receipt.php`; `portal_custom_report.php` | YES | SAFE-ASSET |

### 9.10 Network, legal, code identity

| ID | Item | Value | Source | MVP? | Isolation |
|---|---|---|---|---|---|
| BRAND-113 | **Registration phone-home** | `https://reg.open-emr.org/api/registration` | `ProductRegistrationService.php:121` | **YES** | **NEEDS-PATCH** |
| BRAND-114 | 2,645 docblock `@link`/`@license` | not user-visible | ~2,645 files | NO | **DO-NOT-TOUCH** |
| BRAND-115 | 530 `@author …@opencoreemr.com` | not user-visible | 553 files | NO | **DO-NOT-TOUCH** |
| BRAND-116 | PHP namespace `OpenEMR\` | 3,611 files | — | NO | **DO-NOT-TOUCH** |
| BRAND-117 | GPL-3.0 licence + copyright headers | ~2,645 files | — | NO | **DO-NOT-TOUCH (legal)** |
| BRAND-118 | ONC certification claims | in acknowledgements page | `acknowledge_license_cert.html` | NO | **DO-NOT-TOUCH w/o counsel** |
| BRAND-119 | Duplicate favicon `<link>` | runtime-verified | `Header.php:95` + template | NO | NEEDS-PATCH (cosmetic) |
| BRAND-120 | `sites/<site>/config.php` per-site PHP | arbitrary execution seam | `globals.php:649` | — | **CONFLICT-01** |

**Inventory totals:** 120 items — SAFE-CONFIG 48 · SAFE-ASSET 26 · SAFE-OVERLAY 3 · NEEDS-PATCH 24 ·
DO-NOT-TOUCH 9 · build-time 10 · n/a 0.

---

## 10. Replacement input requirements

What must be supplied before implementation. **Discovery only — nothing created.**

### 10.1 Text / configuration values

| Ref | Input | Current | Notes |
|---|---|---|---|
| BRAND-001 | **Product name** | `OpenEMR` | Flows to UI, emails, **HL7 MSH-3** and **QRDA XML** — confirm with integration owners |
| BRAND-042 | **Login tagline** | open-source advert | Highest-visibility string; supply EN + AR |
| BRAND-055 | Main menu logo URL | `https://www.open-emr.org/` | Or blank for non-clickable |
| BRAND-056 | Menu logo tooltip | `''` → *"OpenEMR Website"* | **Must set explicitly**; blank leaks the brand |
| BRAND-057 | Support URL | `http://open-emr.org/` | Use HTTPS |
| BRAND-058 | User manual URL | `''` → OpenEMR wiki | **Must set explicitly** |
| BRAND-059 | Support phone | `''` | Optional |
| BRAND-070 | Portal address | `https://your_web_site.com/openemr/portal` | Must match Q12 subdomain routing |
| BRAND-110 | Facility name/address/phone | `Your Clinic Name Here` | Per tenant; appears on printed output |
| BRAND-102/103 | AR + EN brand strings | — | For translation catalogue |

### 10.2 Graphical assets

| Ref | Asset | Format | Dimensions | Ratio | Transparency | Background | Appears |
|---|---|---|---|---|---|---|---|
| BRAND-014 | Login primary | PNG or SVG | **1053×390** (2.70:1) | wide | **Required** | light + dark | Login, SMART authorize, **telehealth email** (must be publicly reachable) |
| BRAND-015 | Login secondary | PNG/SVG | **300×100** (3:1) | wide | Required | light | Login (if `extra_logo_login=1`) |
| BRAND-016/017 | Small logos ×2 | PNG/SVG | **101×100** (~1:1) | square | Required | light | Login (if `tiny_logo_*=1`); SMART register-app |
| BRAND-018 | Menu/navbar | **SVG preferred** | **287×287** source; **renders at height 16 px** | **must read at 16 px** | Required | light navbar | Main shell navbar |
| BRAND-019 | Favicon | **ICO multi-frame** | **16 + 32 + 48** | 1:1 | Required | both | Browser tab (all `Header::setupHeader()` pages) |
| BRAND-029 | Root favicon | **ICO** | 16 + 32 | 1:1 | Required | both | `admin.php`, `setup.php`, `sql_patch.php`, `sql_upgrade.php` — **currently 404** |
| BRAND-020 | Portal login | PNG/SVG | **1053×390** | wide | Required | light | Portal login |
| BRAND-021 | **Portal login secondary** | PNG/SVG | ~300×100 | wide | Required | light | Portal login — **no default exists** |
| BRAND-022 | Portal menu | PNG/SVG | **870×222** (3.9:1) | wide | Required | light | Portal navbar; height capped by `portal_primary_menu_logo_height` (**30 px**) |
| BRAND-033 | Practice/statement logo | **GIF/PNG** | ~150 px wide | wide | Optional | white paper | Statements, receipts, reports |
| BRAND-031/032 | Legacy tiny logos ×2 | PNG | **86×43** (2:1) | wide | Optional | light | Legacy + SMART register-app |
| BRAND-025–028 | Legacy duplicates ×4 | match originals | as originals | — | Required | — | Legacy paths; replace to avoid old-mark bleed |

**File-size guidance:** existing assets run 357 B – 36 KB. Keep logos **< 50 KB**; favicon **< 20 KB**.
Provide SVG where possible — resolution-independent and smallest for flat marks.

### 10.3 Theme / colour tokens (Q34 = 2 variants: light + dark, RTL-capable)

| Ref | Input | Current | Notes |
|---|---|---|---|
| BRAND-079 | **`$brand-primary` token** | **does not exist** | Must be introduced; Q34 requires a validated token set |
| BRAND-078 | Light palette | `$link-color:#1d4ed8`, `$body-bg:#f9fafb`, `$gray-100…900`, `$body-color`, `$secondary-color`, `$login-text-color` | Supply full set |
| BRAND-078 | Dark palette | as `style_dark.scss` | Supply full set |
| BRAND-080 | Legacy brand blue | `#2d9bd6` (images only) | Replace inside raster assets |
| BRAND-086 | **Arabic font** | **none tracked** | Amiri / Noto Naskh Arabic — web **and** PDF |

> **Locked constraint (Invariant 9 / Q34 / MVP-010):** tenant input must be limited to *validated tokens
> and approved logos*. No tenant-supplied CSS or JS may be accepted or executed.

---

## 11. GROUP 1D — FINAL DECISION CLOSURE AND CERTIFICATION

Sections 11–20 are Group 1D output. They **supersede** the Group 1C coverage matrix, gap accounting,
patch totals and verdict. Sections 0–10 remain valid discovery evidence, with corrections applied inline
and listed in §18.

### 11.1 What Group 1D changed

| Area | Group 1C state | Group 1D outcome |
|---|---|---|
| FHIR `/metadata` runtime | source-proven only | **RUNTIME-VERIFIED** (§11.2) |
| SMART style tokens | source-only | **RUNTIME-VERIFIED**, incl. dark-fallback defect proven (§11.2) |
| G-04r role branding | open | **CLOSED — SOURCE-PROVEN** (§12.2) |
| G-05 PDF | "knowledge gap" | reclassified **ACCEPTANCE-ONLY** (§12.2) |
| G-01r portal post-auth | "knowledge gap" | reclassified **ACCEPTANCE-ONLY** (§12.2) |
| G-10b | "requires external system" | reclassified **DEFERRED IMPLEMENTATION-DEPENDENT ACCEPTANCE TEST** (§12.2) |
| G-07 | open, undecided | **recommendation authored; requires user adoption** (§13) |
| G-09 | open, undecided | **recommendation authored; requires user adoption** (§14) |
| Patch counts | 8 mandatory / 3 conditional (from 120 items) | **re-derived from all 136 items** → 8 mandatory / 5 conditional (§15) |
| Coverage matrix | 30 complete / 10 partial | **rebuilt from final evidence** (§17) |

### 11.2 New runtime evidence obtained in Group 1D

A temporary `OPENSSL_CONF` environment variable (pointing at the stack's shipped `openssl.cnf`) was set
for the Apache process only — **no application source, configuration file or `php.ini` was modified**.
This resolved the environment defect isolated in Group 1C and unblocked the entire `/apis/` surface.

**FHIR CapabilityStatement — `GET /apis/default/fhir/metadata` → `HTTP 200`, 35,809 bytes:**

```json
"software":       { "name": "OpenEMR" }
"implementation": { "description": "OpenEMR FHIR API",
                    "url": "http://localhost:8300/apis/default/fhir" }
```

- **BRAND-087 runtime-confirmed** (`software.name`).
- **BRAND-126 runtime-confirmed** (`implementation.description`).
- **New finding:** `software.version` is **absent from the response**. `composer.json` has **no `version`
  key** (verified), so `$composerObj["version"]` is undefined and the field is never populated. The
  CapabilityStatement therefore advertises **no software version at all** — corrects BRAND-088.
- **New finding:** `/apis/default/fhir/metadata` served `HTTP 200` **while every REST global was `0`**
  (`rest_api`, `rest_fhir_api`, `rest_portal_api`, `rest_system_scopes_api`, `oauth_password_grant` all
  disabled). The metadata endpoint is **not gated** by the API-enable globals.

**SMART style JSON — `GET /oauth2/default/smart/smart-style` → `HTTP 200`** (with `rest_api` temporarily
`1`; the endpoint *is* gated, unlike metadata):

```json
{ "color_background":"#f8f9fa", "color_error":"#9e2d2d", "color_highlight":"#69b5ce",
  "color_modal_backdrop":"#343a40", "color_success":"#498e49", "color_text":"#000",
  "dim_border_radius":"6px", "dim_font_size":"13px", "dim_spacing_size":"20px",
  "font_family_body":"'Lato','Helvetica',sans-serif,…",
  "font_family_heading":"'Lato','Helvetica',sans-serif,…",
  "logo_primary":"http://localhost:8300/public/images/logos/core/login/primary/logo.png?t=1783217252" }
```

- **BRAND-121/122/123 runtime-confirmed** — 6 colours, 2 typography declarations, 3 dimensions.
- `logo_primary` resolves to an **absolute URL** carrying the `?t=` cache-bust — BRAND-014 flows into the
  machine-facing contract.
- Response is **double-JSON-encoded** (the document is returned as a quoted JSON string). Cosmetic, noted.

**Dark-theme fallback defect — PROVEN, not inferred.** With `css_header` temporarily set to
`style_dark.css`, the same endpoint returned **identical light tokens** (`color_background: #f8f9fa`,
`color_text: #000`). Confirms only `smart-style_light.json.twig` exists and every non-light theme silently
receives light tokens. This is the basis of requirement **R-SMART-DARK** (§16).

**New brand strings discovered while probing:**

| String | Source | Class |
|---|---|---|
| `"OpenEMR Error: API is disabled"` | `src/RestControllers/Subscriber/OAuth2AuthorizationListener.php:108` | **machine-facing JSON error, NOT translatable** (raw exception string) — BRAND-134 |
| `"OpenEMR Error : OpenEMR is not working since the php openssl module is not installed."` | `interface/globals.php:99` | **hardcoded pre-bootstrap fatal**, not translatable — BRAND-135 |
| `"OpenEMR Error : OpenEMR is not working since the openssl aes-256-cbc cipher is not available."` | `interface/globals.php:106` | same — BRAND-136 |

All temporary changes were reversed and proven reversed (§17).

### 11.3 Canonical inventory additions BRAND-121 – BRAND-136

Sections 9.1–9.10 define BRAND-001 – BRAND-120. The following 16 items were added by Groups 1C and 1D and
complete the canonical inventory.

| ID | Item | Vis | Source | Current value | Group 2 action | Isolation | Trk |
|---|---|---|---|---|---|---|---|
| BRAND-121 | SMART style JSON token contract | M | `templates/api/smart/smart-style_light.json.twig`; `SMARTAuthorizationController.php:419-434` | 12-key light contract, **runtime-verified** | TOKENIZE | NEEDS-PATCH | YES |
| BRAND-122 | SMART style colours (6) | M | same | `#f8f9fa #9e2d2d #69b5ce #343a40 #498e49 #000` | TOKENIZE | NEEDS-PATCH | YES |
| BRAND-123 | SMART typography + dimensions | M | same | `'Lato','Helvetica',…`; `6px/13px/20px` | TOKENIZE | NEEDS-PATCH | YES |
| BRAND-124 | Orphaned static SMART style file | M | `public/smart-styles/smart-light.json` | differs from Twig; referenced only in a commented-out line (`SMARTSessionTokenContextBuilder.php:144`) | DEFER | SAFE-ASSET | NO |
| BRAND-125 | `$font-family-sans-serif` = **Lato** — declared, never shipped or loaded | H | `interface/themes/default-variables.scss:85` | falls back to Helvetica/system | BUILD-SHARED-THEME | Build-time | YES |
| BRAND-126 | FHIR `implementation.description` | M | `FhirMetaDataRestController.php:70` | **`"OpenEMR FHIR API"`** — runtime-verified | PATCH | NEEDS-PATCH | YES |
| BRAND-127 | OAuth2 authorize titles ×3 | H | `oauth2-login:14`, `patient-select:10`, `scope-authorize:19` | `"OpenEMR Authorization"` (`xlt`) | SET-TRANSLATION | SAFE-CONFIG | NO |
| BRAND-128 | OAuth2 login button label | H | `oauth2-login.html.twig:92` | `"OpenEMR Login"` (`xlt`) | SET-TRANSLATION | SAFE-CONFIG | NO |
| BRAND-129 | Zend module page titles ×3 | H | `Application/layout.phtml:6`, `sendto.phtml:6`, `Documents/layout.phtml:18` | `"OpenEMR Application"`, `"OpenEMR"`, `"Welcome to OpenEMR"` (`xl`) | SET-TRANSLATION | SAFE-CONFIG | NO |
| BRAND-130 | Zend Module Installer wiki links ×2 | H | `Installer/.../index.phtml:36,117` | hardcoded `open-emr.org/wiki` URLs | PATCH | NEEDS-PATCH | YES |
| BRAND-131 | **PHP session cookie named `OpenEMR`** | M | `SessionUtil.php:81` via `session_name()` | `Set-Cookie: OpenEMR=<sid>; path=/; SameSite=Strict` — runtime-verified | PRESERVE | NEEDS-PATCH — *locked Q17* | YES |
| BRAND-132 | Portal login `<title>` | H | `portal/index.php`; catalogue constant 7998 | `"Patient Portal Login"` — runtime-verified | SET-TRANSLATION | SAFE-CONFIG | NO |
| BRAND-133 | Portal reuses the **core** favicon slot | H | runtime-verified on portal login | `/public/images/logos/core/favicon/favicon.ico` | REPLACE-ASSET | SAFE-ASSET | NO |
| BRAND-134 | `"OpenEMR Error: API is disabled"` | M | `OAuth2AuthorizationListener.php:108` | JSON error body — **runtime-observed**, **not translatable** | PATCH | NEEDS-PATCH | YES |
| BRAND-135 | Pre-bootstrap fatal — openssl missing | H | `interface/globals.php:99` | `"OpenEMR Error : OpenEMR is not working since the php openssl module is not installed."` — **not translatable** | PATCH | NEEDS-PATCH | YES |
| BRAND-136 | Pre-bootstrap fatal — aes-256-cbc missing | H | `interface/globals.php:106` | `"OpenEMR Error : OpenEMR is not working since the openssl aes-256-cbc cipher is not available."` — **not translatable** | PATCH | NEEDS-PATCH | YES |

---

## 12. FINAL GAP MODEL

### 12.1 Original-gap accounting (denominator = 10)

A parent gap is **CLOSED** only when every child is closed.

| Original gap | Final parent state | Basis |
|---|---|---|
| G-01 | **CLOSED** | Portal login runtime-verified; residual G-01r is acceptance-only, not a knowledge gap |
| G-02 | **CLOSED** | Runtime-verified in Group 1D (§11.2) |
| G-03 | **CLOSED** | SMART tokens runtime-verified; OAuth2 screen strings source-proven (all `xlt`) |
| G-04 | **CLOSED** | Administrator shell runtime-verified; G-04r closed source-proven (§12.2) |
| G-05 | **CLOSED (as discovery)** | Reclassified acceptance-only; every input deterministically traced |
| G-06 | **CLOSED** | Exhaustive 8-module sweep |
| G-07 | **CLOSED — LOCKED DECISION ADOPTED** | Adopted as **`Q76`** (§13) |
| G-08 | **CLOSED** | Resolved by applying existing locked text |
| G-09 | **CLOSED — LOCKED DECISION ADOPTED** | Adopted as **`Q77`** (§14) |
| G-10 | **PARTIALLY CLOSED** | Mechanism verified (G-10a); end-to-end acceptance (G-10b) is a deferred implementation-dependent acceptance test, **not** a discovery gap |

| Metric | Count |
|---|---:|
| Original gaps | **10** |
| Fully closed | **9** (G-01…G-09) |
| Partially closed | **1** (G-10) |
| Fully open | **0** |

> The Group 1C figure **"10 / 7 / 3"** is **HISTORICAL and superseded** — it counted a split parent as
> fully closed and mixed parent and child denominators.

### 12.2 Residual-item accounting (separate denominator — never mixed with the above)

| Residual | Final classification | Rationale |
|---|---|---|
| **G-01r** — portal post-authentication branding | **ACCEPTANCE-ONLY — SOURCE-PROVEN, RUNTIME PENDING** | Every input is traced: `portal/menu/primary` via `LogoService` (`home.php:362`), `portal_primary_menu_logo_height=30` (`home.php:374`), `portal_css_header=style_light.css`, core favicon reuse (runtime-proven on the login page), `PORTAL_SESSION_ID`. Requires a portal patient credential; there are 0 patients. **Not a knowledge gap.** |
| **G-04r** — Reception/Accountant shells | **CLOSED — SOURCE-PROVEN: no role-specific branding mechanism** | `main.php:48` resolves the menu logo **unconditionally** — no ACL guard. `MainMenuRole` (`src/Menu/MainMenuRole.php:58`) selects only a **menu JSON file**; it does not select layout, logo, theme or favicon. Theme/favicon resolve in `interface/globals.php` / `Header.php`, which are role-agnostic (site global + optional per-**user** `user_settings` override — per-user, not per-role). Only one active user (`admin`) exists. Retained solely as a later smoke test. |
| **G-10b** — two-tenant end-to-end isolation | **DEFERRED IMPLEMENTATION-DEPENDENT ACCEPTANCE TEST** | Q11 locks DB-per-tenant, so the test needs a second provisioned tenant database — a **product component MVP-010/provisioning must build**. Not "external system". Isolation *mechanism* is runtime- and source-proven (§ G-10a). **Cross-tenant behaviour is NOT certified until this test executes.** |
| **G-05r** — PDF/print render | **ACCEPTANCE-ONLY — SOURCE-PROVEN, RUNTIME PENDING** | `statement_logo` (live `practice_logo.gif`) → `statement.inc.php:86-87`; facility name `Your Clinic Name Here`; `style_pdf.css` present. Requires ≥1 patient; there are 0. Creating clinical records is provisioning, not discovery. |

**No residual item is a discovery unknown.** All four are runtime confirmations or implementation-dependent tests.

---

## 13. G-07 — BRANDING TOKEN MATERIALISATION

### 13.1 Governing evidence

| Source | Constraint |
|---|---|
| Control Plane §2 | Control Plane (PostgreSQL 18) stores **branding tokens** — authoritative |
| Control Plane §6 | **Logical linkage only**; no distributed ACID transaction across PostgreSQL and tenant DBs |
| Control Plane §10 | Tenant runtime-critical configuration is **cached/snapshotted locally** so a Control Plane outage does not block already-authenticated clinical work |
| Control Plane §8 | Tenant runtime receives credentials **only for its own tenant database** |
| Q34 / Q59 | Per-tenant branding = validated tokens/CSS variables + tenant logos over a **shared immutable bundle** |
| Invariant 4 | No core edits by default |
| Invariant 9 / MVP-010 | No arbitrary tenant CSS/JS executed |
| MVP-010 AC | *"Tenant can change approved logo and tokenized palette"*; *"cache keys/revisions prevent one tenant's branding appearing in another"* |

### 13.2 OpenEMR mechanism evidence (targeted, not a re-scan)

| Mechanism | Finding | Source |
|---|---|---|
| Globals read frequency | **Once per request**, single `SELECT gl_name, gl_index, gl_value FROM globals` | `interface/globals.php:457` |
| Globals cache | **None** — `OEGlobalsBag` has no caching layer | grep → 0 matches |
| Logo resolution | Filesystem, per-request, per-site, `?t=<mtime>` cache-bust | `LogoService.php:75-159` |
| Theme consumption | Filename from `globals.css_header`, gated by `file_exists()` | `globals.php:437-483` |
| Asset binding | `%global%` substitution | `config/config.yaml:60-78` |

**Decisive consequence:** because every request already performs one `globals` read with no cache,
**writing tokens into `globals` adds zero per-request cost**, whereas a read-through design would add a
cross-service call to a path that has no cache layer to hook into.

### 13.3 Decision table

| Criterion | **A. Push/sync → tenant `globals`** | B. Runtime read-through + cache | C. Deploy/provision-time materialisation |
|---|---|---|---|
| Adheres to Locked Decisions | ✅ full | ⚠️ partial | ⚠️ partial |
| Runtime dependency on Control Plane | **NO** | **YES** | NO |
| Tenant operates if CP unavailable | **YES** (Control Plane §10 satisfied by construction) | Only while cache is warm | YES |
| Per-request latency | **none** (rides existing query) | +1 network call on miss | none |
| Cache invalidation | revision column + logo `?t=` mtime | TTL/push invalidation needed | redeploy |
| Update propagation | seconds (sync job) | immediate | **requires redeploy** |
| Rollback | re-sync previous revision | re-sync | redeploy |
| Auditability | CP audit + tenant `globals` history | CP audit only | CP audit + release record |
| Cross-tenant leakage risk | **low** — values land in the tenant's own DB | **medium** — shared client/cache keyed by tenant | low |
| Credential requirements | sync service needs per-tenant DB write | tenant runtime needs CP credential (**conflicts with Control Plane §8 intent**) | provisioning only |
| Implementation complexity | **low–medium** | high | low |
| OpenEMR core modifications | **none** — writes existing `globals` rows | **yes** — new read path in bootstrap | none |
| Rebase/upstream risk | **none** | **high** | none |
| Compatible with DB-per-tenant | ✅ | ✅ | ✅ |
| Compatible with MVP-010 | ✅ | ✅ | ❌ — breaks *"tenant can change"* without redeploy |
| Future white-labeling | ✅ | ✅ | ⚠️ |
| Failure mode | stale branding until next sync (**safe**) | branding failure or CP outage impact (**unsafe**) | stale until redeploy |

### 13.4 Recommended Locked Decision

> ### RECOMMENDED LOCKED DECISION — BRANDING TOKEN MATERIALISATION
> **Selected architecture: A — Control-Plane-authoritative, push/sync materialisation into tenant `globals`.**
>
> | Element | Normative statement |
> |---|---|
> | **Source of truth** | The SaaS Control Plane (PostgreSQL 18). Branding tokens, logo references, and the branding **revision** are owned there. |
> | **Materialisation target** | The tenant's own OpenEMR MariaDB `globals` rows, plus the tenant's site logo directory. |
> | **Trigger** | (a) tenant provisioning, (b) any Control-Plane branding change, (c) a reconciliation sweep. Never on the request path. |
> | **Does MariaDB `globals` receive values?** | **Yes** — `globals` is a **materialisation target, never the source of truth**. Direct edits are permitted only as break-glass and are overwritten by the next sync. |
> | **CSS variables** | Generated **at materialisation time** into a per-tenant CSS-variable payload served from the **shared immutable bundle**; never compiled per tenant, never authored by the tenant. |
> | **Logo handling** | Control Plane stores a **reference**; bytes live in the tenant's own file/object scope and resolve through `LogoService`. No cross-tenant path is ever constructed. |
> | **Revision / cache key** | A monotonic `branding_revision` per tenant, materialised into `globals` and appended to branding asset URLs alongside the existing `?t=<mtime>`. Satisfies MVP-010's cross-tenant cache-key criterion. |
> | **Failure / fallback** | If materialisation fails, the tenant keeps its **last-good** branding. Never fall back to another tenant's branding, and never to an unbranded state that leaks upstream identity. |
> | **Control Plane unavailable** | **No clinical impact.** Tenants continue on materialised values (satisfies Control Plane §10). |
> | **Does tenant runtime ever query the Control Plane directly?** | **No.** Prohibited — preserves Control Plane §8 credential isolation and adds no per-request dependency. |
> | **Ownership of synchronisation** | The Control Plane provisioning/sync service (`MVP-014` scope), **not** OpenEMR core and **not** the tenant runtime. |
>
> **Why this and not B or C.** B places a cross-service call on a request path that has no cache layer
> (`globals.php:457`, no cache in `OEGlobalsBag`), requires an OpenEMR core bootstrap change (breaching
> Invariant 4), and gives the tenant runtime a Control-Plane credential (against Control Plane §8's intent).
> C cannot satisfy MVP-010's *"tenant can change approved logo and tokenized palette"* without a redeploy.
> A satisfies every locked constraint with **zero core edits and zero per-request cost**.

### 13.5 Governance status — **ADOPTED AND LOCKED**

# G-07 CLOSED — LOCKED DECISION ADOPTED AS `Q76`

**Adopted:** Option A, as evidenced in §13.3–§13.4.
**Alternatives retained as rationale (rejected):** B (runtime read-through), C (deploy-time materialisation).

| Governance element | Value |
|---|---|
| Governing identifier | **`Q76` — Branding token materialisation boundary** |
| Governing document | `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md`, **Section L** |
| Cross-references recorded | Control Plane §2, §8, §10; Q34; Q59; Invariant 4; Invariant 9; `MVP-010`; `MVP-014` |
| Backlog reflection | `MVP-010` (+3 criteria) and `MVP-014` (+1 criterion); task IDs preserved |
| Manifest | Re-issued and independently verified (§21) |

The normative text in §13.4 is a faithful restatement of `Q76`; **the governing document is authoritative**
if the two ever diverge.

---

## 14. G-09 — SAUDI THEME SURPLUS

### 14.1 Evidence

- **6** themes build → **44** compiled CSS files in `public/themes/` (gitignored build output).
- Q34 locks **two** Saudi variants (light + dark, both RTL-capable).
- Admin selector is a **filesystem scan** with an **already-hardcoded exclusion list**:
  `edit_globals.php:714-732` excludes `style_blue.css` and `style_pdf.css` and requires
  `preg_match("/^style_.*\.css$/")`.
- Selection is validated only by `file_exists()` (`globals.php:476`).

### 14.2 Comparison

| Criterion | **A. Do not build the 4 surplus themes** | B. Build them but hide from the selector |
|---|---|---|
| Core patch required | **No** — `webpack.themes.js` entry-map change (build config) | **Yes** — patch the hardcoded exclusion list in `edit_globals.php` |
| Build-pipeline impact | 4 fewer entries; faster builds | none |
| Upstream rebase risk | **low** — build config is fork-owned | **higher** — recurring conflict in a core admin file |
| Selectable despite being hidden? | **No** — `file_exists()` fails, forcing the fallback | **Yes** — a stale `globals`/`user_settings` row or direct DB edit still selects it |
| Guarantees exactly two variants | **Yes** | **No** |
| Maintenance burden | low | ongoing patch upkeep |

**A is strictly safer.** Under B, a hidden theme remains reachable because `file_exists()` is the only
runtime gate; under A the file is absent, so the gate itself enforces the two-variant surface.

### 14.3 Recommended Locked Decision

> ### RECOMMENDED LOCKED DECISION — SAUDI THEME SURPLUS
> **Selected disposition: A — do not build the surplus themes for the Saudi product.**
>
> | Element | Normative statement |
> |---|---|
> | **Themes that ship** | Exactly two Saudi variants — **light** and **dark**, each with its RTL counterpart, plus the required `tabs_*` shell and `style_pdf` support files. |
> | **Do the other four compile?** | **No.** `solar`, `manila`, `cobalt_blue`, `forest_green` are removed from the Saudi `webpack.themes.js` entry map (all four sub-variants each). |
> | **May their CSS exist in deployment?** | **No.** Absent from `public/themes/` in the Saudi image. |
> | **Appear in admin selector?** | **No** — the selector scans the filesystem; absent files cannot appear. **No core patch is required.** |
> | **Fallback** | A stale `css_header`/`user_settings` value pointing at a removed theme fails `file_exists()` (`globals.php:476`) and falls back to `style_light.css` — existing, safe behaviour. |
> | **Upgrade behaviour** | Upstream may add themes; the Saudi build entry map is fork-owned and explicit, so new upstream themes do not enter the product implicitly. Re-verify the entry map each rebase. |

### 14.4 Governance status — **ADOPTED AND LOCKED**

# G-09 CLOSED — LOCKED DECISION ADOPTED AS `Q77`

**Adopted:** Option A, as evidenced in §14.2–§14.3.
**Alternative retained as rationale (rejected):** B (build-and-hide) — leaves themes reachable through
`file_exists()` and requires a recurring core selector patch.

| Governance element | Value |
|---|---|
| Governing identifier | **`Q77` — Saudi theme surface** |
| Governing document | `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md`, **Section L** |
| Cross-references recorded | Q34; Q59; Invariant 4; `MVP-010` |
| Backlog reflection | `MVP-010` (+1 criterion); task IDs preserved |
| Manifest | Re-issued and independently verified (§21) |

The normative text in §14.3 is a faithful restatement of `Q77`; **the governing document is authoritative**
if the two ever diverge.

---

## 15. RECONCILED PATCH INVENTORY (derived from all 136 canonical BRAND items)

Re-derived from the full canonical inventory (BRAND-001…136), **not** carried over from Group 1B.
**This is the authoritative patch inventory for implementation.**

### 15.1 Mandatory MVP patches

| # | Item | BRAND | Location | Why mandatory |
|---|---|---|---|---|
| 1 | FHIR `software.name` = `"OpenEMR"` | 087 | `FhirMetaDataRestController.php:84` | Runtime-verified; every FHIR client sees it |
| 2 | FHIR `implementation.description` = `"OpenEMR FHIR API"` | 126 | `:70` | Runtime-verified; same response |
| 3 | Product registration phone-home | 113 | `ProductRegistrationService.php:121` | Transmits to OpenEMR project infrastructure |
| 4 | SMART style token template — **dark variant + tokenisation** | 121–123 | `templates/api/smart/` | Q34 requires dark; dark fallback defect runtime-proven |
| 5 | `admin.php` title + heading | 005, 006 | `admin.php:40,53` | **Unauthenticated**, runtime-verified |
| 6 | `"OpenEMR Error: API is disabled"` | 134 | `OAuth2AuthorizationListener.php:108` | Machine-facing, **not translatable** |
| 7 | Pre-bootstrap fatal messages ×2 | 135, 136 | `interface/globals.php:99,106` | **Not translatable**; user-visible on failure |
| 8 | Zend Module Installer wiki links ×2 | 130 | `Installer/.../index.phtml:36,117` | Hardcoded `open-emr.org` URLs in admin UI |

**Mandatory total: 8.**

### 15.2 Conditional patches (only if the feature is used)

| # | Item | BRAND | Condition |
|---|---|---|---|
| 1 | Installer/upgrade titles + headings (`setup.php`, `sql_patch.php`, `sql_upgrade.php`, `ippf_upgrade.php`) | 007–012 | Only if operators/customers see these screens |
| 2 | Eye-Magic `sites/default` hardcode ×2 | 030 | Only if the Eye Magic form is enabled |
| 3 | Duplicate favicon `<link>` emission | 119 | Cosmetic |
| 4 | Empty `alt=""` on primary login logo | 053 | Accessibility; patch or supply via template variable |
| 5 | `$openemr_name` bootstrap fallback | — (`globals.php:633`) | Only affects pre-DB bootstrap |

**Conditional total: 5.**

> The Group 1B/1C figure **"8 mandatory + 3 conditional"** is **HISTORICAL**. Mandatory remains 8 by
> coincidence — its *membership changed* (BRAND-126, 130, 134, 135, 136 added; the favicon, review and
> donate items moved out to SAFE-ASSET/SAFE-CONFIG). Conditional is now **5**.

### 15.3 Explicitly NOT patches

| Item | BRAND | Disposition | Basis |
|---|---|---|---|
| Session/cookie identity constants (6) + session cookie named `OpenEMR` | 089–093, 131 | **PRESERVE** | Locked Q17; §13 of this report — migration risk, invalidates sessions |
| Review link | 060 | **SET-CONFIG** (`display_review_link=0`) | Config gate exists |
| Donate link | 061 | **SET-CONFIG** (`display_donations_link=0`) | Config gate exists |
| Missing `public/images/favicon.ico` | 029 | **REPLACE-ASSET** | Drop one file; fixes all 5 refs, no patch |
| Error-page titles (5) + OAuth2 titles (4) + Zend titles (3) | 101, 127–129 | **SET-TRANSLATION** | All `xl`/`xlt`-wrapped |
| GPL headers, CMS-1500, UB-04, card marks, ONC claims | 114–118, 038–040 | **PRESERVE / PROHIBITED** | Legal |
| `sites/<site>/config.php` | 120 | **PROHIBITED** | Invariant 9, MVP-010, G-08 |
| `style_default.css` absent; dead `tiny_logo.html.twig` | 084, 052 | **NO-ACTION** | Proven non-defects |

---

## 16. GROUP 2 ACTION CLASSIFICATION

Every BRAND item carries **exactly one** action, so Group 2 never has to infer intent. The complete
per-ID assignment is §16.2; the aggregate below is **computed from it**, not estimated.

| Action | Count | BRAND IDs |
|---|---:|---|
| **SET-CONFIG** | **37** | 001–004, 013, 042–050, 054–059, 062, 064–072, 074, 075, 096, 097, 107, 109, 110 |
| **REPLACE-ASSET** | **20** | 014–022, 025–029, 031–033, 108, 112, 133 |
| **PATCH** | **18** | 005–012, 030, 053, 087, 113, 119, 126, 130, 134, 135, 136 |
| **PRESERVE** | **15** | 038–040, 063, 089–093, 114–118, 131 |
| **NO-ACTION** | **11** | 023, 024, 041, 051, 052, 073, 084, 088, 099, 100, 106 |
| **BUILD-SHARED-THEME** | **9** | 076, 077, 081–083, 085, 086, 111, 125 |
| **SET-TRANSLATION** | **8** | 101–104, 127–129, 132 |
| **DEFER** | **8** | 034–037, 094, 095, 098, 124 |
| **TOKENIZE** | **6** | 078–080, 121–123 |
| **HIDE** | **3** | 060, 061, 105 |
| **PROHIBITED** | **1** | 120 |
| **TOTAL** | **136** | BRAND-001 … BRAND-136 |

> **CORRECTION (K-15).** An earlier aggregate table in this section listed representative — not
> exhaustive — IDs and different per-action counts (REPLACE-ASSET 14, SET-CONFIG 46, SET-TRANSLATION 14,
> PATCH 13, PRESERVE 14, DEFER 6, NO-ACTION 10). Both versions summed to 136, but the earlier
> distribution could not be verified per-ID and is **superseded**. The table above is derived from the
> complete §16.2 mapping. No evidence-backed classification was changed to force a count.

### 16.2 Complete per-ID Group 2 action mapping (authoritative)

| BRAND | Action | BRAND | Action | BRAND | Action | BRAND | Action |
|---|---|---|---|---|---|---|---|
| 001 | SET-CONFIG | 035 | DEFER | 069 | SET-CONFIG | 103 | SET-TRANSLATION |
| 002 | SET-CONFIG | 036 | DEFER | 070 | SET-CONFIG | 104 | SET-TRANSLATION |
| 003 | SET-CONFIG | 037 | DEFER | 071 | SET-CONFIG | 105 | HIDE |
| 004 | SET-CONFIG | 038 | PRESERVE | 072 | SET-CONFIG | 106 | NO-ACTION |
| 005 | PATCH | 039 | PRESERVE | 073 | NO-ACTION | 107 | SET-CONFIG |
| 006 | PATCH | 040 | PRESERVE | 074 | SET-CONFIG | 108 | REPLACE-ASSET |
| 007 | PATCH | 041 | NO-ACTION | 075 | SET-CONFIG | 109 | SET-CONFIG |
| 008 | PATCH | 042 | SET-CONFIG | 076 | BUILD-SHARED-THEME | 110 | SET-CONFIG |
| 009 | PATCH | 043 | SET-CONFIG | 077 | BUILD-SHARED-THEME | 111 | BUILD-SHARED-THEME |
| 010 | PATCH | 044 | SET-CONFIG | 078 | TOKENIZE | 112 | REPLACE-ASSET |
| 011 | PATCH | 045 | SET-CONFIG | 079 | TOKENIZE | 113 | PATCH |
| 012 | PATCH | 046 | SET-CONFIG | 080 | TOKENIZE | 114 | PRESERVE |
| 013 | SET-CONFIG | 047 | SET-CONFIG | 081 | BUILD-SHARED-THEME | 115 | PRESERVE |
| 014 | REPLACE-ASSET | 048 | SET-CONFIG | 082 | BUILD-SHARED-THEME | 116 | PRESERVE |
| 015 | REPLACE-ASSET | 049 | SET-CONFIG | 083 | BUILD-SHARED-THEME | 117 | PRESERVE |
| 016 | REPLACE-ASSET | 050 | SET-CONFIG | 084 | NO-ACTION | 118 | PRESERVE |
| 017 | REPLACE-ASSET | 051 | NO-ACTION | 085 | BUILD-SHARED-THEME | 119 | PATCH |
| 018 | REPLACE-ASSET | 052 | NO-ACTION | 086 | BUILD-SHARED-THEME | 120 | PROHIBITED |
| 019 | REPLACE-ASSET | 053 | PATCH | 087 | PATCH | 121 | TOKENIZE |
| 020 | REPLACE-ASSET | 054 | SET-CONFIG | 088 | NO-ACTION | 122 | TOKENIZE |
| 021 | REPLACE-ASSET | 055 | SET-CONFIG | 089 | PRESERVE | 123 | TOKENIZE |
| 022 | REPLACE-ASSET | 056 | SET-CONFIG | 090 | PRESERVE | 124 | DEFER |
| 023 | NO-ACTION | 057 | SET-CONFIG | 091 | PRESERVE | 125 | BUILD-SHARED-THEME |
| 024 | NO-ACTION | 058 | SET-CONFIG | 092 | PRESERVE | 126 | PATCH |
| 025 | REPLACE-ASSET | 059 | SET-CONFIG | 093 | PRESERVE | 127 | SET-TRANSLATION |
| 026 | REPLACE-ASSET | 060 | HIDE | 094 | DEFER | 128 | SET-TRANSLATION |
| 027 | REPLACE-ASSET | 061 | HIDE | 095 | DEFER | 129 | SET-TRANSLATION |
| 028 | REPLACE-ASSET | 062 | SET-CONFIG | 096 | SET-CONFIG | 130 | PATCH |
| 029 | REPLACE-ASSET | 063 | PRESERVE | 097 | SET-CONFIG | 131 | PRESERVE |
| 030 | PATCH | 064 | SET-CONFIG | 098 | DEFER | 132 | SET-TRANSLATION |
| 031 | REPLACE-ASSET | 065 | SET-CONFIG | 099 | NO-ACTION | 133 | REPLACE-ASSET |
| 032 | REPLACE-ASSET | 066 | SET-CONFIG | 100 | NO-ACTION | 134 | PATCH |
| 033 | REPLACE-ASSET | 067 | SET-CONFIG | 101 | SET-TRANSLATION | 135 | PATCH |
| 034 | DEFER | 068 | SET-CONFIG | 102 | SET-TRANSLATION | 136 | PATCH |

**Integrity:** 136 rows · each BRAND-001…136 appears **exactly once** · no ID carries two actions ·
every action is one of the 11 permitted values.

### 16.3 PATCH-action reconciliation against §15

All **18** PATCH-actioned IDs map to a §15 patch item; no PATCH-actioned ID is unaccounted for.

| §15 class | Patch items | PATCH-actioned BRAND IDs covered |
|---|---:|---|
| **Mandatory** (§15.1) | **8** | 005, 006, 087, 113, 126, 130, 134, 135, 136 → **9 IDs** |
| **Conditional** (§15.2) | **5** | 007, 008, 009, 010, 011, 012, 030, 053, 119 → **9 IDs** |
| | | **Total 18 ✅** |

**Note on TOKENIZE vs PATCH.** Mandatory patch item §15.1 #4 (*SMART style token template — dark variant
+ tokenisation*) covers BRAND-121–123, whose canonical action is **TOKENIZE**, not PATCH. The action
describes *what Group 2 produces* (a token contract); the §15 entry records that delivering it requires
touching tracked template files. This is the only case where a mandatory patch item maps to a
non-`PATCH` action, and it is deliberate.

**PRESERVE / PROHIBITED exclusion check.** All 15 PRESERVE items and the single PROHIBITED item
(BRAND-120) are absent from both §15 tables — they are excluded from implementation mutation by design
(Q17 for session identity, legal for GPL/regulatory/trademark assets, Invariant 9 + `Q76` for BRAND-120).

**TOKENIZE / BUILD-SHARED-THEME compatibility.** All 6 TOKENIZE items (078–080, 121–123) and all 9
BUILD-SHARED-THEME items (076, 077, 081–083, 085, 086, 111, 125) are consistent with **`Q76`** (validated
tokens materialised over a shared immutable bundle) and **`Q77`** (two Saudi variants only).

### 16.1 R-SMART-DARK — explicit Group 2 requirement

Elevated from observation to requirement.

| Element | Requirement |
|---|---|
| Light contract | Retain the existing 12 keys of `smart-style_light.json.twig` |
| **Dark contract** | **Author `smart-style_dark.json.twig`** with the same 12 keys and dark-appropriate values |
| Shared token names | `color_background`, `color_error`, `color_highlight`, `color_modal_backdrop`, `color_success`, `color_text`, `dim_border_radius`, `dim_font_size`, `dim_spacing_size`, `font_family_body`, `font_family_heading`, `logo_primary` |
| Mapping to web theme | SMART tokens must derive from the **same** design-token source as the web CSS variables (§13.4), not be maintained separately |
| Logo behaviour | `logo_primary` stays an absolute URL resolved through `LogoService`; must remain per-tenant correct |
| Fallback policy | An unknown theme must fall back to the **light Saudi variant**, never to an upstream-branded default |
| Acceptance | Requesting `smart-style` with `css_header=<dark>` must return **dark** tokens (today it returns light — runtime-proven §11.2) |

---

## 17. RESTORATION PROOF (Groups 1C + 1D)

### 17.1 Database — all temporary writes reversed

| Global | Original | Temporary | Restored | Test |
|---|---|---|---|---|
| `portal_onsite_two_enable` | `0` | `1` | **`0`** | G-01 portal |
| `extra_portal_logo_login` | `0` | `1` | **`0`** | BRAND-021 |
| `rest_api` | `0` | `1` | **`0`** | SMART style |
| `css_header` | `style_light.css` | `style_dark.css` | **`style_light.css`** | SMART dark fallback |

```
SELECT gl_name, gl_value FROM globals
 WHERE gl_name IN ('portal_onsite_two_enable','extra_portal_logo_login','rest_api','css_header');
-- css_header                style_light.css
-- extra_portal_logo_login   0
-- portal_onsite_two_enable  0
-- rest_api                  0
SELECT COUNT(*) FROM globals;       -- 490  (unchanged)
SELECT COUNT(*) FROM patient_data;  --   0  (unchanged)
```

### 17.2 Environment

Apache restarted **without** `OPENSSL_CONF`. Proof: `GET /apis/default/fhir/metadata` returned to
**`HTTP 500`**, and the login page returned `HTTP 200`, **9,375 bytes** — byte-identical to baseline.

### 17.3 Application-generated key artifacts — created by the test, removed

The FHIR test caused OpenEMR to auto-generate crypto material that did not previously exist (all four
files timestamped `2026-08-09 05:12`; the directories previously held only `README.md`).

| Artifact | Disposition |
|---|---|
| `sites/default/documents/certificates/oaprivate.key` | **deleted** |
| `sites/default/documents/certificates/oapublic.key` | **deleted** |
| `sites/default/documents/logs_and_misc/methods/sevena` | **deleted** |
| `sites/default/documents/logs_and_misc/methods/sevenb` | **deleted** |
| DB `keys` rows: `oauth2key`, `oauth2passphrase`, `sevena`, `sevenb` | **deleted** |

**Safety established before deletion:** 0 documents, 0 patients, 0 `oauth_clients`, and **all 4,262
`log_comment_encrypt` rows carry `encrypt = 'No'`** — nothing was encrypted with this material.
Post-cleanup: `SELECT COUNT(*) FROM keys` → **0**; both directories contain only `README.md`.

**DATABASE UNCHANGED FROM BASELINE: YES. FILESYSTEM RESTORED: YES.**

### 17.4 Working-tree delta — three distinct concepts

These are **not** the same thing and were previously conflated. Each is stated from actual filesystem and
`git` evidence.

#### A. Pre-existing working-tree differences (not produced by this audit)

Present before the branding audit series began and untouched by it:

| Path | State | Origin |
|---|---|---|
| `Documentation/EHI_Export/.../lists_medication.2degrees.dot` | deleted (tracked) | pre-existing |
| `sites/default/sqlconf.php` | modified (tracked) | local DB credentials — **never read into any artifact** |
| `Documentation/EHI_Export/.../lists_medication.2degrees.docx` | untracked | pre-existing |
| `SETUP-STATUS.md`, `fix-docker-virtualization.ps1` | untracked | pre-existing |
| `docs/00-discovery/` | untracked | earlier discovery phase |
| `docs/discovery/`, `tools/discovery/` | untracked | earlier decision-evidence audit |
| **`docs/HISModulesUsers.md`** | untracked | **pre-existing user file — not created, read into, or modified by this audit** |
| `Locked Desicions/` | untracked **directory** | governance documents supplied by the user |

#### B. Authorized persistent changes produced by the final governance closure

Four files were persistently modified. **All four are documentation/governance — no application file:**

| File | Change | Verification |
|---|---|---|
| `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md` | Section L added (`Q76`, `Q77`); header coverage `Q1–Q75` → `Q1–Q77`; Appendix A note | hash re-issued, verified §21 |
| `Locked Desicions/OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria-UPDATED-2026-08-09.md` | `MVP-010` +4 criteria, `MVP-014` +1 criterion; **task IDs preserved** | hash re-issued, verified §21 |
| `Locked Desicions/OpenEMR-SaaS-Decision-Documents-SHA256-UPDATED-2026-08-09.txt` | manifest re-issued with both new hashes | self-verifying |
| `docs/rebranding.md` | this report | — |

#### C. Current total working-tree delta vs HEAD

`git status --short` reports **11 entries**: 2 modified/deleted tracked files (both pre-existing, §A) and
9 untracked entries.

> **Why the git listing looks unchanged despite §B.** `Locked Desicions/` contains **zero tracked files**
> (`git ls-files "Locked Desicions/"` → 0), so git collapses it to a single `??` entry and does not
> descend into it. Editing files inside it therefore changes their **bytes** without changing the
> `git status` **listing**. The earlier claim *"working-tree delta = only `docs/rebranding.md`"* was
> **incorrect** and is corrected here — three governance files were also changed (§B).

**No tracked application file was modified by any part of this audit.** The only tracked-file differences
(§A) are pre-existing and were never touched.

---

## 18. CORRECTIONS REGISTER (all generations)

### 18.1 Issued by Group 1C (retained)

| ID | Superseded claim | Correction | Evidence |
|---|---|---|---|
| **K-01** | *"Blank `main_menu_logo_title` falls back to `xl('OpenEMR Website')`, so the brand leaks in a tooltip"* | **False.** The global row **exists** with value `''`, so `getString($key, $default)` returns `''` — the default applies only when the key is **absent**. Runtime renders `title=""`. No tooltip brand leak. | RUNTIME-HTML |
| **K-02** | *"5 session-name constants"* | **6** identity constants in `SessionUtil.php:81-90`. | SOURCE |
| **K-03** | *"`SMARTAuthorizationController.php:427` = SMART/OAuth authorize screen"* | It is the **SMART style JSON** endpoint (`smartAppStyles()`, `:419-434`). The authorize screens are 3 templates under `templates/oauth2/`. | SOURCE |
| **K-04** | *"7 hardcoded `<title>` tags"* | Conflated literal-hardcoded with translatable. Correct split: **6 literal hardcoded**, **9 translatable brand titles**, **2 dynamic via `openemr_name`**. | SOURCE |
| **K-05** | FHIR hardcoded branding = 1 literal | **2** literals in the same method: `software.name` (`:84`) **and** `implementation.description` (`:70`). | SOURCE |

### 18.2 Issued by Group 1D

| ID | Superseded claim | Correction |
|---|---|---|
| **K-06** | Audit type *"Group 1B — DISCOVERY ONLY"* in the header | Now Group 1D; header and methodology corrected (§0) |
| **K-07** | *"All DB access was SELECT-only; all HTTP GET/HEAD"* | **Inaccurate.** 4 controlled `UPDATE`s, an authenticated session, and 2 Apache restarts occurred — all disclosed and reversed (§0.2, §17) |
| **K-08** | Gap accounting *"10 / 7 / 3"* | Ambiguous — split parent counted as closed. Replaced by 7 closed / 1 partial / 2 open, with residuals counted separately (§12) |
| **K-09** | G-10b *"REQUIRES EXTERNAL SYSTEM"* | Mischaracterised. The missing system is a **product component MVP-010/provisioning must build** → *deferred implementation-dependent acceptance test* |
| **K-10** | G-05, G-01r as *"knowledge gaps"* | Reclassified **ACCEPTANCE-ONLY — SOURCE-PROVEN, RUNTIME PENDING**; deterministic behaviour is not an unknown |
| **K-11** | BRAND-088 *"version read from composer.json at runtime"* | **`composer.json` has no `version` key** → `software.version` is **absent** from the CapabilityStatement |
| **K-12** | *"Zero gaps block implementation"* (1C verdict) | **False as of 1D.** G-07 and G-09 are unresolved Locked Decisions that Group 2 would otherwise have to invent |
| **K-13** | Patch totals *"8 mandatory + 3 conditional"* | Re-derived across all **136** items: **8 mandatory** (different membership) + **5 conditional** (§15) |
| **K-14** | FHIR/SMART *"runtime blocked"* | **Runtime-verified** in Group 1D (§11.2) |

### 18.3 Issued by the final certification repair

| ID | Superseded claim | Correction |
|---|---|---|
| **K-15** | §16 action aggregate listing *representative* IDs with counts REPLACE-ASSET 14 / SET-CONFIG 46 / SET-TRANSLATION 14 / PATCH 13 / PRESERVE 14 / DEFER 6 / NO-ACTION 10 | Not verifiable per-ID. Replaced by the complete §16.2 mapping and aggregates **computed from it**: SET-CONFIG 37 · REPLACE-ASSET 20 · PATCH 18 · PRESERVE 15 · NO-ACTION 11 · BUILD-SHARED-THEME 9 · SET-TRANSLATION 8 · DEFER 8 · TOKENIZE 6 · HIDE 3 · PROHIBITED 1 = **136**. No evidence-backed classification was altered. |
| **K-16** | **Duplicate BRAND-ID assignment.** `BRAND-058` was cited in five places for the `App=OpenEMR` cookie, while §9.5/§10.1 canonically assign `BRAND-058` to **`user_manual_link`** | The `App` cookie is canonically **`BRAND-089`**. All incorrect references corrected. `BRAND-058` now refers solely to `user_manual_link`. Found by the per-ID integrity check, not by rediscovery. |
| **K-17** | §17.4 / §21 *"working-tree delta = only `docs/rebranding.md`"* | **Incorrect.** The final governance closure also changed three files inside `Locked Desicions/`. The git-status *listing* was unchanged only because that directory is **wholly untracked** and git does not descend into it. Replaced by the three-way A/B/C accounting in §17.4. |
| **K-18** | §1.2 conflict statuses (*UNRESOLVED*, *scope decision*, *design input required*) | All four conflicts are resolved: CONFLICT-01 → prohibited-mechanism disposition; CONFLICT-02 → `Q77`; CONFLICT-03 → `Q76`; CONFLICT-04 → implementation input per Q12. |
| **K-19** | §5.7 *"defines five product-identity constants"* | **6** constants (five session names + `APP_COOKIE_NAME`) and **2** runtime identity cookies. The active statement is corrected in place, not left for the later K-02 row to repair. |

---

## 19. FINAL COVERAGE MATRIX (rebuilt)

Statuses: **VC** = Verified Complete · **SPC** = Source-Proven Complete · **IDA** = Implementation-Dependent
Acceptance · **BLD** = Blocked by Locked Decision · **OOS** = Out of MVP Scope.

| # | Area | Status | Evidence / what remains |
|---|---|---|---|
| 1 | Product name | **VC** | 20 consumers + 6 literal titles; DB + runtime |
| 2 | Login | **VC** | 14 globals, 6 layouts, 25 templates; runtime HTML |
| 3 | Main shell / header | **VC** | Authenticated runtime (G-04) |
| 4 | Menus | **VC** | navbar render path runtime-verified |
| 5 | Footer | **VC** | No footer branding exists |
| 6 | Portal (login) | **VC** | Runtime-verified (G-01) |
| 6b | Portal (post-auth) | **IDA** | Needs a portal patient credential (G-01r) |
| 7 | Logos | **VC** | 9 lookups bidirectional; BRAND-021 proven at runtime |
| 8 | Favicon | **VC** | 200 and 404 both proven |
| 9 | Legacy logos | **VC** | Blob-SHA dedup + consumer trace |
| 10 | Themes | **VC** | 44 compiled; 6 buildable; `config.yaml` binding |
| 11 | Colors | **VC** | Per-theme vars; `#2d9bd6` image-only |
| 12 | Fonts | **VC** | Lato declared, never shipped/loaded (BRAND-125) |
| 13 | Language strings | **VC** | 62 code + 924 catalogue + 237,509 DB rows |
| 14 | About | **VC** | Runtime — 3 external links rendered |
| 15 | Help | **VC** | `help_files/**` enumerated |
| 16 | Documentation in UI | **VC** | Acknowledgements page runtime-verified |
| 17 | Emails | **VC** | 12 templates brand-free; subjects via global |
| 18 | Notifications | **SPC** | Brand-free templates; no SMS templates exist |
| 19 | PDFs | **IDA** | Inputs traced; needs ≥1 patient (G-05r) |
| 20 | Printed reports | **IDA** | Same |
| 21 | Prescriptions | **SPC** | Subject/body via `openemr_name` |
| 22 | FHIR | **VC** | **Runtime-verified** (§11.2) |
| 23 | SMART / OAuth screens | **SPC** | 4 strings, all `xlt`; screens need a registered client |
| 23b | **SMART design-token contract** | **VC** | Runtime-verified incl. dark-fallback defect |
| 24 | REST / API errors | **VC** | JSON error surface + BRAND-134 runtime-observed |
| 25 | Swagger / OpenAPI | **VC** | Runtime 200, unauthenticated |
| 26 | HL7 | **SPC** | MSH-3 = uppercased global |
| 27 | QRDA | **SPC** | 2 consumers |
| 28 | Installer | **VC** | Titles/headings/copy enumerated |
| 29 | Upgrade | **VC** | 3 upgrade scripts incl. `ippf_upgrade.php` |
| 30 | Multisite administration | **VC** | Runtime, unauthenticated |
| 31 | Modules | **VC** | Exhaustive 8-module sweep (G-06) |
| 32 | Package metadata | **VC** | composer/npm; **no `version` key** (K-11) |
| 33 | External links | **VC** | 2,872 classified |
| 34 | Upstream network calls | **VC** | 29 call sites; exactly 1 endpoint |
| 35 | Legal / regulatory assets | **VC** | Enumerated, PRESERVE |
| 36 | Database configuration | **VC** | 490 rows; 33 branding globals |
| 37 | Generated assets | **VC** | 44 compiled themes |
| 38 | Working-tree-only assets | **VC** | 368 untracked, all third-party |
| 39 | Error pages | **VC** | 5 template strings + 3 raw strings (134–136) |
| 40 | HTTP headers / cookies | **VC** | 6 constants + 2 runtime cookies |
| 41 | **Role-independent branding** | **VC** | Source-proven (G-04r) |
| 42 | **Control Plane token ownership / materialisation** | **VC** | Governed by **`Q76`** (§13) |
| 43 | **Theme surplus disposition** | **VC** | Governed by **`Q77`** (§14) |
| 44 | **Cross-tenant branding acceptance** | **IDA** | G-10b — needs two provisioned tenants |

**Totals: VC = 35 · SPC = 6 · IDA = 4 · BLD = 0 · OOS = 0 — 45 areas.**

**Zero `BLD` rows remain** — a precondition for VERDICT A0 (§22).

> The Group 1C total *"30 VERIFIED COMPLETE / 10 PARTIAL"* is **HISTORICAL and superseded**.

---

## 20. THE THREE FINAL REGISTERS

### A. DISCOVERY KNOWLEDGE GAPS — **NONE**

No unknown fact about existing OpenEMR branding behaviour remains. Every surface has been enumerated,
every value read, every deterministic path traced, and the previously blocked runtime surfaces
(FHIR, SMART tokens, portal, authenticated shell) have been directly verified.

### B. LOCKED DECISIONS REQUIRED BEFORE GROUP 2 — **NONE**

Both decisions previously required have been **adopted by the user and formally locked**:

| Was | Decision | Adopted option | Governing identifier | Status |
|---|---|---|---|---|
| G-07 | Branding token materialisation | **A** — Control-Plane-authoritative push/sync materialisation | **`Q76`** (Section L) | **CLOSED — LOCKED** |
| G-09 | Saudi theme surface | **A** — surplus themes not built for the Saudi artifact | **`Q77`** (Section L) | **CLOSED — LOCKED** |

Both are recorded in `Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md`, reflected in
`MVP-010`/`MVP-014` acceptance criteria, and covered by the re-issued SHA-256 manifest, which verifies
byte-for-byte under two independent hashers (§21).

### C. IMPLEMENTATION-DEPENDENT ACCEPTANCE TESTS — **8 (non-blocking)**

| ID | Test | Depends on |
|---|---|---|
| A1 | Two tenants, distinct branding, no cross-render | Provisioning (G-10b) |
| A2 | Cache keys/revisions prevent cross-tenant bleed | Branding layer + revision (G-07 A) |
| A3 | Invalid token/CSS payloads rejected | Token validator |
| A4 | No tenant-uploaded CSS/JS executes | Branding layer |
| A5 | Statement/PDF renders with `practice_logo.gif` | ≥1 patient (G-05r) |
| A6 | Portal post-authentication branding | Portal patient credential (G-01r) |
| A7 | Reception/Accountant smoke test | Roles provisioned (branding already source-proven identical) |
| A8 | SMART dark tokens returned for a dark theme | R-SMART-DARK implemented |

**These are not knowledge gaps.** They do not block Group 2 from starting; they gate `MVP-010` acceptance.

### D. CONSTRAINTS GROUP 2 MUST OBEY (locked, not gaps)

| # | Constraint | Governing source |
|---|---|---|
| C1 | **No arbitrary tenant CSS/JS/PHP.** `sites/<site>/config.php` must never be used as a branding seam. | Invariant 9, `MVP-010`, `Q76` |
| C2 | Per-tenant branding = **validated tokens + approved logos over a shared immutable bundle** only. | Q34, Q59, `Q76` |
| C3 | Ship exactly **two** Saudi variants (light + dark, RTL-capable); surplus themes absent from build output. | **`Q77`** |
| C4 | Prefer configuration / assets / modules / upstream PRs over core edits. | Invariant 4 |
| C5 | Control Plane is the **source of truth**; tenant `globals` is a **materialisation target**. No Control Plane call during page rendering. | **`Q76`**, Control Plane §2/§8/§10 |
| C6 | Do **not** change `SessionUtil` identity constants for branding reasons. | Q17; §13 classification |
| C7 | Do **not** alter GPL headers, CMS-1500/UB-04, card marks, or ONC certification claims. | Legal (BRAND-114–118, 038–040) |
| C8 | Branding cache keys must incorporate a tenant-safe revision preventing stale or cross-tenant branding. | **`Q76`**, `MVP-010` |

---

## 21. END-STATE INTEGRITY

| Check | Value |
|---|---|
| Authoritative path | `G:\My Drive\OpenEMR\docs\rebranding.md` |
| BRAND count / range | **136** / BRAND-001–136, no gaps, no duplicates |
| Branding globals | **33** |
| Logo lookups | **9** (8 with defaults) |
| Tracked images | **1,008** · untracked third-party **368** |
| Session identity constants | **6** · runtime identity cookies **2** |
| Mandatory patches | **8** · conditional **5** |
| Discovery knowledge gaps | **0** |
| Locked Decisions required before Group 2 | **0** — adopted as `Q76`, `Q77` |
| Implementation-dependent acceptance tests | **8** (A1–A8) |
| Coverage matrix | 45 areas — VC 35 / SPC 6 / IDA 4 / **BLD 0** / OOS 0 |
| Group 2 action integrity | 136 IDs, exactly one action each, 0 duplicates, 0 missing (§16.2, machine-verified) |
| Governing register | `…Locked-Decisions-UPDATED-2026-08-09.md` — Q1–Q77, Section L added |
| Governance manifest | re-issued; verified byte-for-byte by `sha256sum -c` **and** Python `hashlib` |
| Database restored | **YES** (490 globals, 0 patients, 0 `keys`) |
| Filesystem restored | **YES** (4 generated key files removed) |
| Pre-existing working-tree differences | 2 tracked (deleted `.dot`, modified `sqlconf.php`) + 8 untracked entries — **none produced by this audit** (§17.4 A) |
| Authorized persistent changes by final governance closure | **4 files** — 3 in `Locked Desicions/` + `docs/rebranding.md` (§17.4 B) |
| Current total working-tree delta vs HEAD | 11 `git status --short` entries; listing unchanged because `Locked Desicions/` is wholly untracked (§17.4 C) |
| Tracked **application** files modified by this audit | **0** |

**Line count and final SHA-256 are reported in the terminal handoff** (a file cannot contain its own hash).

---

## 22. FINAL VERDICT

### 22.1 A0 certification gates

| # | Gate | Result |
|---|---|---|
| 1 | Discovery knowledge gaps = 0 | ✅ Register A empty (§20) |
| 2 | Required Locked Decisions before Group 2 = 0 | ✅ Register B empty (§20) |
| 3 | G-07 closed / formally governed | ✅ **`Q76`**, Section L |
| 4 | G-09 closed / formally governed | ✅ **`Q77`**, Section L |
| 5 | BRAND count = 136, no gaps/duplicates | ✅ 001–136 verified |
| 6 | Exactly one Group 2 action per BRAND ID | ✅ §16.2 — 136 rows, 136 unique IDs, 0 duplicates, 0 missing, all 11 values valid (machine-verified) |
| 7 | Patch inventory internally consistent | ✅ 8 mandatory / 5 conditional; all 18 PATCH-actioned IDs reconciled (§16.3) |
| 8 | Coverage matrix has zero `BLD` rows | ✅ VC 35 / SPC 6 / IDA 4 / BLD 0 / OOS 0 (§19) |
| 9 | Active stale/contradictory statements = 0 | ✅ Full-document sweep; every residual match is an italicised quotation inside a `K-xx` correction row or an explicit **HISTORICAL** label |
| 10 | Governance manifest byte-verifies | ✅ `sha256sum -c` **and** Python `hashlib` (§21) |
| 11 | Database baseline restored | ✅ 490 globals, 0 `keys`, 0 patients (§17) |
| 12 | Application source/assets unchanged | ✅ 0 tracked application files modified; the 4 authorized persistent changes are documentation/governance only (§17.4 B) |
| 13 | A1–A8 explicitly remain pending acceptance tests | ✅ §20 register C; cross-tenant isolation explicitly **not** demonstrated |
| 14 | Working-tree terminology distinguishes pre-existing vs authorized changes | ✅ §17.4 A/B/C |

**All fourteen gates pass.**

# VERDICT A0 — GROUP 1 CLOSED; GROUP 2 MAY START

**Discovery is factually complete.** All 136 branding items are enumerated with exact source, consumer,
current value, isolation mechanism, Locked Decision constraint and a single deterministic **GROUP 2
ACTION**. No unknown remains about OpenEMR's existing branding behaviour.

**Both outstanding architecture decisions are now formally governed** — `Q76` (branding token
materialisation) and `Q77` (Saudi theme surface) — in the authoritative Locked Decisions register, with
`MVP-010`/`MVP-014` acceptance criteria updated so that Group 2 cannot implement a divergent architecture,
and with the SHA-256 governance manifest re-issued and independently verified.

**Eight implementation-dependent acceptance tests (A1–A8) remain recorded and outstanding.** They do
**not** block Group 2 from starting; they gate `MVP-010` acceptance. In particular, **cross-tenant
branding isolation has NOT been demonstrated end-to-end** (A1/A2) — the mechanism is proven, the
behaviour is not certified, and it cannot be until a second tenant is provisioned.

**Group 2 has not been started by this audit.** No branding value, asset, theme, template, global or
database row was changed. All temporary test changes were reversed and reversal was proven (§17).

---

*Final Group 1 certification — 2026-08-09 — commit `631f2b38cf633769c305233f88cdf9c73ca80657`.
Governing decisions `Q76` and `Q77` are binding via
`Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md` (Section L) and the re-issued
`OpenEMR-SaaS-Decision-Documents-SHA256-UPDATED-2026-08-09.txt` manifest. Where this report and the
governing register differ, the register is authoritative.*

---

## 23. GROUP 2/3 OUTCOMES CROSS-REFERENCE (added Phase 5)

**Added 2026-08-10, as Phase 5 documentation deliverable F7 (`docs/RebrandingPlan.md` §7.1: "Updated
`docs/rebranding.md` cross-reference note — A short appendix mapping Group 1 IDs to Group 2 outcomes.
Group 1 evidence itself is not rewritten — it is a certified artifact").** This section is a pointer
appended after the fact. It does not alter, reinterpret, or supersede anything in §0 through §22 above —
the Group 1 certification and the FINAL VERDICT stand exactly as written on 2026-08-09. What has changed
since that verdict is that Group 2/3 (implementation) has now run against the 136 BRAND-ID inventory this
document certifies, and that implementation's outcomes are recorded in a separate, independently
maintained document set under `docs/branding/`, not in this file.

### 23.1 Outcome summary (sourced from `docs/branding/changes.md`, not recomputed here)

`docs/branding/changes.md` is a Phase 4 verification pass that checked all 136 BRAND-001…136 items
against the committed state of branch `feat/thiqa-branding-foundation` (commits `a1c22b6a1`..`c6c3f9e6e`)
and assigned each item exactly one of four statuses. The counts below are quoted verbatim from that
document's own "Status-count summary" table — they are not recalculated here:

| Status | Count | % of 136 |
|---|---:|---:|
| DONE | 128 | 94.1% |
| PARTIAL | 2 | 1.5% |
| NOT DONE | 4 | 2.9% |
| BLOCKED | 2 | 1.5% |

*(All 8 DEFER-action items are counted inside DONE, since for a DEFER action "done" means "correctly left
untouched." Reading them as unimplemented instead gives DONE 120 / PARTIAL 2 / NOT DONE 4 / BLOCKED 2 /
DEFERRED 8 — both readings sum to 136.)*

> **Figures corrected 2026-08-10 (`docs/RebrandingBugs.md` RB-12).** This section originally quoted
> DONE 111 / PARTIAL 2 / NOT DONE 14 / BLOCKED 2 verbatim from `docs/branding/changes.md`, on the stated
> basis that they were "not recalculated here". They should have been: that table totalled **129**, not
> 136, and its accompanying "integrity check" validated the *action-category* counts rather than the status
> counts it sat beneath. The corrected figures above also reflect the subsequent remediation of
> BRAND-007…012 and BRAND-127…129. Quoting a downstream document verbatim does not transfer responsibility
> for its arithmetic — that is the lesson worth carrying, and it is why this note exists rather than a
> silent substitution.

### 23.2 Where to find per-ID detail

This appendix is intentionally not a second copy of the 136-row mapping. For any individual BRAND-ID's
Group 2/3 implementation status, mechanism, artefact and evidence, see
[`docs/branding/changes.md`](branding/changes.md) directly — it is organised by the same eleven Group 2
action categories this document's own §16.2 assigns (SET-CONFIG, REPLACE-ASSET, PATCH, PRESERVE,
NO-ACTION, BUILD-SHARED-THEME, SET-TRANSLATION, DEFER, TOKENIZE, HIDE, PROHIBITED), and every row cites
either a live database query, a `git log`/`git diff` result, or a direct file read performed in that
verification pass.

### 23.3 The two largest open items

A reader of this certification should not have to already know `docs/branding/` exists to learn that the
certification is now partially superseded, in the "not yet implemented" sense, by two implementation
gaps larger than the rest combined:

> **Both items below were closed on 2026-08-10.** They are kept, with their resolutions, because a
> superseded gap that simply vanishes from a certification appendix teaches a later reader nothing. Full
> analysis: `docs/RebrandingBugs.md` RB-01 and RB-02.

1. ~~**The SET-TRANSLATION gap.**~~ **CLOSED.** BRAND-127/128/129 (the OAuth2 authorization screens and the
   Zend module admin screens) now render "Thiqa Authorization" / "Thiqa Login" / "Thiqa Application" /
   "Welcome to Thiqa" — delivered through the **translation catalogue**, which is the action §16.2 assigns
   them (`Trk = NO`). The source literals are deliberately unchanged.

   **The route matters more than the outcome here.** An interim attempt rebranded them by editing the
   literal inside `xl()`/`xlt()`. In OpenEMR the English source string *is* the catalogue key
   (`library/translation.inc.php:39-77` matches `lang_constants.constant_name` exactly), so that renamed
   the key and orphaned **59** existing translations across the shipped locales, Arabic included. It was
   reverted and re-done as `lang_id = 1` rows via `tools/branding/brand-strings.json`. Zero translations
   lost; a zero-occurrence test guard now prevents the regression recurring.

2. ~~**The installer/upgrade PATCH gap.**~~ **CLOSED.** `setup.php`, `sql_patch.php`, `sql_upgrade.php` and
   `ippf_upgrade.php` (BRAND-007…012) are patched, each with a numbered patch record (PR-10…PR-13) as `Q1`
   requires. One of them needed care: `sql_upgrade.php`'s `<h2>` literal *was* `xlt()`-wrapped and carried
   28 translations, so those were carried forward onto the new constant rather than orphaned.

Their existence never invalidated anything this document (§0–§22) certified about Group 1 discovery —
discovery correctly identified these 136 items and their required actions. What was incomplete was the
Group 2/3 *execution*, and for these two categories it no longer is.

**What remains open** (per `docs/branding/changes.md`'s corrected gap table): BRAND-030 (Eye-Magic
hardcode, unreachable in this product), BRAND-102/103 (the bulk `xl()`-wrapped and catalogue strings),
BRAND-119 (duplicate favicon link, reclassified to DEFER), BRAND-104/111 (partial, gated on D-4 and D-9),
and BRAND-070/110 (blocked on tenant provisioning, D-6/D-7).
