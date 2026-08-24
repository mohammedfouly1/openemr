# Multi-tenant / white-label readiness — Thiqa branding layer

**Status:** Phase 4/5 readiness assessment (F4, `docs/RebrandingPlan.md` §7.1). Written against the
working tree of branch `feat/thiqa-branding-foundation` at repo root `G:\My Drive\OpenEMR`. As of
writing, `git log --oneline -15` shows the module and its supporting work already landed as a
committed sequence (`a1c22b6a1` "add the Thiqa branding layer module" through `c6c3f9e6e` "record the
Phase 3 audit, remediation and plan corrections"), and `git status --short` shows a clean tree aside
from this document set (`docs/branding/`) and one unrelated untracked file. Earlier guidance in this
session described the module as uncommitted working-tree state; that has since changed — the commit
history, not working-tree diffs, is now the record of what Phase 2/3 built. Nothing below depends on
which state that was in — every citation is a file:line or a command run against the current tree.

**Evidence discipline.** Every claim below is either (a) a direct file:line citation, (b) the literal
output of a command run in this session, or (c) explicitly marked as **not verified** where no
second tenant, Control Plane, or live materialisation was available to test against. Per
`docs/rebranding.md` §18 and Invariant 10, no claim here describes an inferred capability as if it
were observed.

---

## 1. What works today, for one tenant (`sites/default`)

### 1.1 The tenancy model is "one DB connection per site," not a `tenant_id` column

Confirmed directly from the module's own documentation of the mechanism it binds to:

> "OpenEMR resolves a site to a database at bootstrap; the `globals` table itself has no tenant
> column. This adapter is therefore constructed *for* one site and refuses any call naming a
> different one."
> — `interface/modules/custom_modules/oe-module-thiqa-branding/src/Materialisation/QueryUtilsBrandingGlobalsWriter.php:28-35`

This matches the locked architecture, not just this module's assumption:

- **Q11 (LOCKED)** — "Model A: one physically separate OpenEMR database per tenant... No core table
  has a tenant_id/site_id column... Tenancy is enforced by which `sqlconf.php` is loaded — the
  connection IS the boundary." (`Locked Desicions/OpenEMR-SaaS-Locked-Decisions-UPDATED-2026-08-09.md:322-342`)
- `interface/globals.php:277-335` resolves `site_id` (from `$_GET['site']` or session), sets
  `OE_SITE_DIR` to `<sites-base>/<site_id>`, and later (`interface/globals.php:643`)
  `require_once`s that site's `config.php`, which is what actually opens the DB connection.

So "per-tenant branding" in this codebase concretely means: whichever site's `sites/<site>/config.php`
the current process loaded, that is the only tenant whose `globals` rows this code can read or write.
There is no tenant column to filter by and no possibility of a query accidentally spanning tenants at
the SQL level — the isolation is structural (a different TCP connection to a different database
entirely), not a `WHERE tenant_id = ?` clause.

### 1.2 The module enforces this binding at runtime, not just by convention

`QueryUtilsBrandingGlobalsWriter` is constructed with exactly one `SiteId` (`__construct(private
SiteId $boundSite)`, same file, line 44) and every read/write method calls `assertBoundTo($site)`
first (lines 63, 108, 140, 151-159), which throws `LogicException` if the site passed in doesn't
match the site the writer was built for. The exception message is deliberately generic — "the site is
never interpolated... topology detail must not leak into it" (lines 154-155) — so a cross-tenant
attempt doesn't leak the target site's name into a log another tenant might read.

`Tenant\SiteId` (`interface/modules/custom_modules/oe-module-thiqa-branding/src/Tenant/SiteId.php`)
is the single tenant-identity value object used everywhere in the module (consolidated from two prior
duplicate implementations per audit finding B-05, per that file's docblock lines 36-39). It rejects
empty strings, values over 63 characters, embedded NUL bytes, and anything outside
`[A-Za-z0-9][A-Za-z0-9_-]{0,62}` — which makes path-traversal segments (`..`, `/etc`, `C:\Windows`,
UNC prefixes) unrepresentable as a `SiteId` at all, not merely filtered.

### 1.3 Materialisation is one atomic transaction, revision written last

`QueryUtilsBrandingGlobalsWriter::writeAll()` (same file, lines 102-127) wraps the entire globals
delta, the `MaterialisedAt` timestamp, and the revision bump in a single `QueryUtils::inTransaction()`
call, with the revision upsert explicitly last inside that transaction ("a database that flushes
partially cannot expose a revision newer than its own data" — lines 99-100). This closes audit finding
AR-P2-001, confirmed independently in `docs/AuditRebranding.md:1126` ("`BrandingMaterialiser` now
calls one `writeAll()`; focused materialisation + governance run passed").

### 1.4 CLI commands are wired and discoverable, and require an explicit tenant

Ran directly in this session:

```
C:\openemr-stack\php\php.exe bin\console list --raw | grep -i thiqa
thiqa-branding:apply-profile     Apply the declarative Thiqa branding profile to one tenant (requires --site).
thiqa-branding:materialise       Apply a branding revision to one tenant (out-of-request; requires --site).
thiqa-branding:verify            Report one tenant's branding revision, freshness and consistency (read-only).
```

Registration happens via `Bootstrap::registerConsoleCommands()`, attached to
`CommandRunnerFilterEvent::EVENT_NAME` (`Bootstrap.php:132-135`), not a container — the module has
none of its own (comment at lines 143-146).

`SiteOption` (`src/Console/SiteOption.php`) declares `--site` with **no default value**, and
documents why: "Every other OpenEMR command declares `--site` with a `'default'` fallback. Branding
must not: a materialisation run that silently targets whichever tenant happens to be the fallback is
exactly the accident tenant scoping exists to prevent." (lines 31-34). Omitting `--site` is refused
with a non-zero exit, not defaulted.

`bin/console` itself already understands `--site=<name>` — this is pre-existing OpenEMR
infrastructure, not something the branding module built: it extracts `--site=` from `argv`
(`bin/console:32-33`), sets `$_GET['site']`, and bootstraps `interface/globals.php` against it
(`bin/console:48-49`), which is the same site-resolution path a web request goes through.

`Bootstrap::registerConsoleCommands()` derives the tenant it binds `QueryUtilsBrandingGlobalsWriter`
and the CLI commands to from `basename($globals->getString('OE_SITE_DIR'))`
(`Bootstrap.php:153`) — i.e., from whatever site `bin/console` actually bootstrapped, not a literal
`'default'`. This is source-proven to be site-agnostic; §2.3 below states plainly that it has not been
run against a second site.

### 1.5 Even the *one* existing tenant has never had a Tier-2 overlay materialised

This matters more than it first appears, so it is stated plainly rather than folded into a footnote.
`docs/AuditRebranding.md:1675` (the "Verified sound" table, Audit 003) records: running
`verify --site=default` reports "never materialised / revision 0", and explains this is "correct, not
a bug — that is the Tier-2 tenant overlay, absent by design; Tier-1 product globals are applied." The
same audit's open-items list is more direct: "End-to-end branded behavior — OPEN — No real
materialised overlay/logo has exercised the token endpoint, SMART dark route, branded login alt text,
or materialisation against MariaDB." (`docs/AuditRebranding.md:1177`).

So today, for the *only* tenant that exists on this system: the **product-level** profile
(`openemr_name`, tagline, etc., applied via `thiqa-branding:apply-profile`) is live and confirmed by a
live login page render (`docs/AuditRebranding.md:1676`: title "Thiqa Login", tagline rendered, logo
200). The **tenant-overlay** path (`thiqa-branding:materialise`, which is the mechanism §2 depends on
for a second tenant to look different from the first) has never actually been run against a real
database in this repository's history — it has been exercised only through unit tests using recording
collaborators (`docs/AuditRebranding.md:1174`), not a live `writeAll()` transaction against MariaDB.
I did not run it myself in this session either, for the same reason noted in §2.3 (out of scope for a
docs-only task, and it would mutate live database state).

### 1.6 The token model is a closed allowlist, not open configuration

`Token\TokenKey` (`src/Token/TokenKey.php`) is a backed enum with 43 total cases; only 11 are
`isTenantOverridable() === true` — the `interactive.*` and `link.*` surfaces (lines 161-209). Brand
identity colours, structural surfaces, borders, text colours, and all four semantic/clinical-safety
colour groups (`success`, `warning`, `critical`, `info`) return `false` and cannot be moved by a
tenant overlay. The docblock states the reasoning explicitly and ties it to patient safety: "A tenant
that could recolour 'critical' toward its brand palette... would be changing the meaning of an alert,
not its styling. This one is a patient-safety boundary and is not negotiable per tenant." (lines
154-159). Every override that is possible is still gated through `TokenValidator` before it can be
materialised. Ten interactive/link pairs use the applicable WCAG 2.2 thresholds. The eleventh,
`interactive.primary.disabled`, is exempt from SC 1.4.3/1.4.11 but must retain the separate 1.5:1 product
distinguishability floor against both the enabled primary fill and the page background. It also retains
the component layer's fixed disabled opacity. The 1.5:1 floor is deliberately not claimed as WCAG.

### 1.7 The shared bundle vs. tenant overlay split matches Q34/Q59, and is running

- **Shared, single-per-deployment:** the Saudi Light/Dark compiled theme CSS
  (`public/themes/style_{light,dark}*.css`) and the brand fonts, built once from
  `brand/tokens/*.json` — this is Plane 5 in the architecture (`docs/RebrandingPlan.md:308-313`).
- **Per-tenant overlay:** the Tier-2 token stylesheet is served from a module PHP endpoint,
  `public/branding-tokens.php`, not written to disk — confirmed by reading that file's docblock
  (lines 1-19: "nothing here writes to disk... the response is immutable and revision-keyed") and by
  `Listener\StyleInjectionListener::onStyleFilter()` (`src/Listener/StyleInjectionListener.php:69-100`),
  which appends the endpoint URL to the page's `<style>` list **only** when
  `BrandingService::tokenStylesheetUrl()` returns non-null, i.e. only when a tenant actually has an
  overlay. This is Plan §3.2.2's recommended option (a); the alternative static-file-on-disk route
  (option (b), which `TenantBrandingPaths`/`TokenCssWriter` also implement) exists for the CLI
  materialiser and `verify` command's health check, not as the runtime delivery path.
- **Per-tenant, but product-level (not token overlay):** `openemr_name` and the other ~30 branding
  globals in `Config\BrandingGlobalKey` (`src/Config/BrandingGlobalKey.php:55-99`) are applied from a
  single declarative JSON file, `config/branding-profile.json`, via `thiqa-branding:apply-profile
  --site=<x>`. This file currently encodes one product identity ("Thiqa" / "ثقة", domain
  `skyeagle.uk`) — it is data applied per invocation to whichever `--site` is given, not a
  per-tenant-branded file itself.
- **Confirmed already patched, not just planned:** the FHIR `software.name` literal now reads
  `$this->globalsBag->getString('openemr_name') ?: 'Thiqa'`
  (`src/RestControllers/FHIR/FhirMetaDataRestController.php:74-92`), committed in `df3cc18f2` ("repoint
  the residual core product strings"). `git diff master -- src/RestControllers/FHIR/FhirMetaDataRestController.php`
  confirms this replaces two hardcoded `"OpenEMR"` / `"OpenEMR FHIR API"` literals.

### 1.8 What this section does **not** claim

Only `sites/default` exists on this machine (`ls sites/` → `default`, run in this session). Every
statement above about "the module is site-agnostic" is a claim about the *source code's* behaviour,
verified by reading it and by running `bin/console list --raw` and `git diff`/`grep` against that one
site. **No branding command has been run against a second, differently-databased site in this
session** — see §2 for exactly what would be needed to do that, and why it hasn't happened here.

---

## 2. What a second tenant needs (the D-6/A1/A2 gap, made concrete)

### 2.1 The mechanism already exists in OpenEMR — this module does not need to build it

Standing up a second OpenEMR tenant is not a branding-layer problem. It is OpenEMR's own, pre-existing
multi-site feature, locked at Q11: create a new `sites/<tenant2>/` directory (its own `config.php`,
`sqlconf.php` pointing at a distinct database, `documents/`, `images/` subtree), install/seed that
database, and OpenEMR's own site-resolution code (`interface/globals.php:277-335`) does the rest —
including, transitively, giving the branding module a distinct `QueryUtilsBrandingGlobalsWriter` bound
to that site the next time a request or console invocation targets it.

### 2.2 The literal steps to provision one and exercise branding against it (not run here)

1. Create `sites/<tenant2>/` with its own `config.php`/`sqlconf.php` and an installed database — the
   same process `CLAUDE.local.md` §7 documents for `sites/default` on this host, repeated with a
   distinct `dbname=`.
2. `C:\openemr-stack\php\php.exe bin\console --site=<tenant2> thiqa-branding:verify` — should report
   revision 0 / not-yet-materialised for the new site, proving the writer resolved a genuinely
   different `SiteId`/connection.
3. `C:\openemr-stack\php\php.exe bin\console --site=<tenant2> thiqa-branding:apply-profile` and/or
   `... thiqa-branding:materialise --site=<tenant2> --payload=<overlay.json>` to give it distinct
   tokens/logos.
4. Compare rendered output (login page, SMART style contract) between `?site=default` and
   `?site=<tenant2>` to confirm no bleed — this is what acceptance tests A1/A2 are.

### 2.3 This has not been done, and I have not simulated it

No second `sites/` directory exists on this machine, and creating one plus a second MariaDB database
and running an installer against it is a provisioning action out of scope for a documentation task (it
would also modify application state, which this task was explicitly told not to do). Everything in
§2.2 is therefore **an evidence-based plan, not a verified result**. This is exactly what
`docs/RebrandingPlan.md`'s own blocking-dependency register already says:

| ID | Dependency | Blocks | Owner | Severity |
|---|---|---|---|---|
| **D-6** | Second provisioned tenant (G-10b) | A1, A2 | Provisioning | Blocking `MVP-010` acceptance |

(`docs/RebrandingPlan.md:1111`). D-6 is explicitly owned by **Provisioning**, not by the branding
module's implementation — nothing in this session's Phase 2/3 work was capable of closing it, and
nothing here claims to have.

### 2.4 The cross-tenant safety net itself is source-proven but not integration-tested

`QueryUtilsBrandingGlobalsWriter::assertBoundTo()` (§1.2 above) would throw if a `MaterialisationJob`
carrying tenant B's `SiteId` ever reached a writer bound to tenant A's connection. This is a real,
structural check — but `docs/AuditRebranding.md:1174` records it as still open at the integration
level: "AR-P2-006 DB-backed and two-tenant tests — OPEN — Focused tests use recording collaborators.
Add MariaDB failure injection and two independently bootstrapped tenant connections." In other words:
the guard exists and is unit-tested against fakes, but no test in this repository has yet opened two
real database connections and proven the guard rejects a real cross-tenant write. That gap is
unchanged by this session.

### 2.5 What this buys once D-6 closes

Given §1's evidence, closing D-6 (provisioning a second tenant) should *not* require any branding-code
change — the code already resolves site from `OE_SITE_DIR`/`--site`, already refuses mismatched
writes, and already stores CSS/logos under `<site>`-scoped paths (`TenantBrandingPaths`, see §3.2).
That is a prediction grounded in reading the code, not a tested outcome — flagged as such per this
project's evidence discipline.

---

## 3. What full white-label needs (architectural, not just provisioning)

"Full white-label" here means an **arbitrary new tenant** — not a second *known* Thiqa-branded tenant,
but one that could plausibly want a different product name, palette range, font, or even reseller
hierarchy. This is a materially larger ask than §2, and the plan is explicit that most of it is out of
scope by design, not by oversight.

### 3.1 The token model is closed by construction, not by policy

`TokenKey` (§1.6) is a PHP backed enum. There is no code path that turns an arbitrary tenant-supplied
string into a `TokenKey` — `TokenKey::tryFrom()` on any string outside the 43 declared cases returns
`null`. Even within the 11 overridable keys, only a validated `#RRGGBB` colour reaches CSS (via
`CssVariableRenderer`, referenced in `TokenKey.php:18-25`). A "different theme entirely" for one
tenant is not representable in this model at all — a tenant can move 11 named colours within their
WCAG-gated bounds, and nothing else.

### 3.2 The Control Plane (Plane 1) does not exist yet

The architecture assumes an authoritative Control Plane (PostgreSQL, `MVP-014`) that validates and
assigns tenant token overlays before the OpenEMR-side CLI ever runs (`docs/RebrandingPlan.md:278-284`).
Per `docs/RebrandingPlan.md:1110`, **D-5** — "Control Plane (`MVP-014`) not yet built" — blocks
end-to-end `Q76` materialisation and A1/A2. Concretely today: the only way to give a tenant a token
overlay is to hand-author a JSON payload and run `thiqa-branding:materialise --site=<x>
--payload=<file>` by hand. That is functionally possible (`Console\JobPayload` parses and validates
such a file — `src/Console/JobPayload.php`, `assets` and token-overlay parsing present, and
`MaterialiseCommand::resolveAssets()` at `src/Console/MaterialiseCommand.php:242-259` runs each
requested asset through the real `LogoValidator`) but is not self-service, not backed by an audit
trail beyond `MaterialisationLogger`, and has no UI. This matches
the plan's own honest statement: "Tenant self-service branding UI — No — CP-side UI over the same
validated contract; no tenant-runtime change." (`docs/RebrandingPlan.md:755`).

*Note on a discrepancy in the audit record.* `docs/AuditRebranding.md:1692` (the "Still open from
earlier audits" list, dated 2026-08-10) states "`Console/JobPayload.php` still declares assets
unsupported, so the shipped command cannot perform the approved-logo acceptance path." Reading the
current source directly contradicts this: asset parsing and validation are wired end-to-end as cited
above, with no `unsupported`/`TODO`/`FIXME` marker left in `JobPayload.php`, `AssetIntakeRequest.php`
or `AssetResolution.php` (checked directly, none found), and all three files are already committed
with no working-tree diff against `HEAD`. This document defers to what the code does today rather
than silently reconciling the older audit note; whoever next updates `docs/AuditRebranding.md` should
close or correct that line.

### 3.3 Fonts and themes are single-per-deployment by locked decision, not by omission

Q34 (LOCKED) restricts the Saudi build to exactly two selectable variants (light + dark, RTL-capable);
Q77 (LOCKED, `docs/rebranding.md:224`) restricts the *build output* itself, not just the admin
dropdown, to those two — the four surplus upstream themes are excluded from the Saudi
`webpack.themes.js` entry map entirely. `docs/AuditRebranding.md:1141-1142` and 1130 record this as
implemented and guarded by a test (`BrandingGovernanceGuardTest`) that asserts "8 required and 16
prohibited [webpack] entries." There is no per-tenant theme selection mechanism, and none is planned —
Plane 5 is explicitly "identical for all tenants" (`docs/RebrandingPlan.md:309`).

### 3.4 Product name / favicon / email are "partly" white-label-ready today

Per the plan's own forward-compatibility table (reproduced in full in §4): `openemr_name` is already a
per-site global, and favicon/email logo already resolve per-site through the existing `LogoService`
mechanism. What is **not** yet per-tenant: the residual hardcoded core-edit strings. The count of these
moved during this session's audit passes — `docs/AuditRebranding.md:1822` records "Residual core edits:
9 → 10 IDs, 6 → 7 files" after the F-02 logo-caption fix — and the plan's own §5.4 is where these are
meant to become globals or translation constants. I did not re-enumerate all 7 files in this pass;
citing the audit's count rather than re-deriving it, since re-deriving would require the same file
sweep the audit already performed.

### 3.5 Reseller hierarchy has no design at all yet

Per the plan's table (§4 below), a reseller→tenant hierarchy is "No" — not designed, not partially
built. It would require Control Plane-side inheritance in `saas_branding_profile`; the tenant runtime
is stated to be unaffected "since it only ever receives a resolved revision"
(`docs/RebrandingPlan.md:753`). This is a plan-level statement of intent, not something this session
verified against any code, since no such code exists yet.

---

## 4. Forward-compatibility notes — what would require a new ADR

Reproduced from `docs/RebrandingPlan.md` §3.10 (lines 744-758), which this section is required to
include per the F4 deliverable spec:

| Future need | Already supported? | Change required |
|---|---|---|
| Second tenant with different logos | Yes — per-site slots | None |
| Second tenant with a different accent palette | Yes — Tier 2 overlay | Populate the CP token rows |
| Full white-label (different product name, favicon, emails) | Partly | `openemr_name` is already per-site; favicon and email logo already resolve per site. Remaining: the residual core-edit strings become globals or translation constants (§5.4) |
| Reseller-level branding hierarchy (reseller → tenant) | No | CP-side inheritance in `saas_branding_profile`; tenant runtime unchanged, since it only ever receives a resolved revision |
| **Per-tenant custom fonts** | **No — deliberately** | **Would breach the shared-immutable-bundle rule (C2); requires a new ADR** |
| Tenant self-service branding UI | No | CP-side UI over the same validated contract; no tenant-runtime change |

The plan's own single-sentence summary, unchanged by anything built this session: **"the tenant runtime
never learns that other tenants exist. Adding tenants changes data, not code."**
(`docs/RebrandingPlan.md:757-758`.) This session's evidence in §1-§3 above is consistent with that
claim at the source-code level for the "second known tenant" case, and explicitly does not extend it
to the reseller-hierarchy or per-tenant-font cases, both of which the plan itself flags as needing new
design work before being attempted.

**The one item explicitly requiring a new ADR before any attempt:** per-tenant custom fonts. Nothing
in this session's work — module code, tests, or documentation — moves that status; it remains a
plan-level statement (`docs/RebrandingPlan.md:754`), not something re-derived or re-argued here.

---

## 5. Summary table

| Question | Answer | Confidence |
|---|---|---|
| Does the module scope every write to one tenant's DB connection? | Yes — structural, via `SiteId` + `assertBoundTo()` | Source-verified (§1.2) |
| Is the CLI site-agnostic (no hardcoded `default`)? | Yes — derives from `OE_SITE_DIR`/`--site` | Source-verified (§1.4); **not run against a second site** |
| Is a second tenant provisioned on this system today? | No — only `sites/default` exists | Verified (`ls sites/`) |
| Has A1/A2 (cross-tenant isolation) executed? | No | Confirmed still open (`docs/RebrandingPlan.md:1111`, `:1137-1138`) |
| Is the token model open to arbitrary tenant CSS? | No — closed 11-key enum allowlist; 10 WCAG-gated and 1 product-separation-gated | Source-verified (§1.6, §3.1) |
| Has the Tier-2 overlay ever been materialised, even for the one existing tenant? | No — `verify --site=default` still reports revision 0 | Confirmed (§1.5) |
| Can a tenant get a wholly different theme/fonts? | No, by locked decision (Q34/Q77); fonts explicitly need a new ADR | Source-verified (§3.3), plan-stated (§4) |
| Does full arbitrary white-label (self-service, reseller) exist? | No — depends on Control Plane (`MVP-014`), which is not built (D-5) | Confirmed (§3.2, §3.5) |
