# 20 — Unresolved External Inputs

Questions that **cannot** be closed by repository inspection, no matter how thorough. For each:
what the repository *did* establish, what external fact is still required, the exact question to put to the
responsible party, and a safe provisional default to proceed under in the meantime.

**Rule applied throughout:** no legal conclusion is drawn from code. Where a regulation is named, the
repository fact is stated separately from the regulatory requirement.

14 questions require some external input; 3 are blocked outright pending it (Q2, Q22, Q45).

---

## Legal / regulatory

### Q45 — Saudi PDPL data residency  ·  **EVIDENCE-BLOCKED**

- **Repository facts established:** Each tenant is a physically separate database plus a separate filesystem
  tree under `sites/<site>/` (`interface/globals.php:277-335`). Per-region tenant placement is therefore
  mechanically straightforward — no code change is needed to host different tenants in different regions.
- **External fact required:** Which regions are lawful for Saudi personal health data, and whether any
  cross-border transfer is permitted for backups, log shipping, telemetry and error reporting.
- **Who must answer:** Legal / Data Protection Officer.
- **Exact question:** *"Which cloud regions are approved for Saudi personal health data under PDPL, and what
  is our position on cross-border transfer for backups, observability data and vendor telemetry?"*
- **Safe provisional default:** Assume **no** cross-border transfer of anything derived from tenant data,
  including logs, metrics and crash reports. Kingdom-only region. This is the most restrictive posture and
  can only be relaxed later, never tightened cheaply.
- **Why it cannot be repository-answered:** It is a legal interpretation of a statute, not a property of code.

### Q21 — ZATCA e-invoicing phase

- **Repository facts established:** Zero ZATCA support (no `zatca`, `fatoora`, `invoice_hash`,
  `qr_code_invoice` matches). Tax support is a **rates registry only** — `list_options list_id='taxrate'`
  (`sql/database.sql:4354`) and a `codes.taxrates` colon-list (`:1135`). **There is no tax-amount column on
  `billing`**, so the schema cannot record the tax actually charged.
- **External fact required:** Which ZATCA wave this taxpayer falls in and the binding deadline.
- **Who must answer:** Legal / Finance.
- **Exact question:** *"Which ZATCA integration wave are we assigned to, and what is our binding compliance
  date for Phase 2 (structured XML + Fatoora clearance)?"*
- **Safe provisional default:** Plan for Phase 2, but sequence the work as: **(1) add tax-amount
  persistence**, (2) Phase 1 QR, (3) Phase 2 clearance. Step 1 is a prerequisite for *both* phases and is
  worth starting regardless of the answer.

### Q48 — Audit-log retention floor

- **Repository facts established:** No retention policy, pruner or archival job exists for `log` / `api_log`;
  the seeded `background_services` rows (`sql/database.sql:209-217`) contain no log-maintenance service.
  Tables grow unbounded. The `background_services` mechanism is the natural place to add one **without a
  core patch**.
- **External fact required:** The binding minimum retention period.
- **Who must answer:** Legal.
- **Exact question:** *"What is the binding minimum retention period for audit logs and for health records
  under PDPL and any applicable MOH rules, and does archived-but-offline storage satisfy it?"*
- **Safe provisional default:** 7 years, retained online, with the pruner **built but disabled** until the
  number is confirmed. Building it early is safe; deleting early is not.

---

## Business / product

### Q2 — Upstream rebase cadence

- **Repository facts established:** Upstream cut 3 stable releases in ~5 months (v8_0_0_3 2026-03-25,
  v8_1_0 2026-06-01, v8_2_0 2026-07-08) and accumulates ~373 commits per ~5 weeks of master. **The fork
  currently carries zero patches, so today's rebase is a fast-forward at zero cost.**
- **External fact required:** Sustained engineering capacity and acceptable patch lag.
- **Who must answer:** Engineering leadership.
- **Exact question:** *"How many engineer-days per month can we commit to upstream merges, and what is the
  maximum acceptable lag between an upstream security fix and our production deploy?"*
- **Safe provisional default:** Rebase per upstream point release (~quarterly), plus out-of-band for any
  security advisory. Revisit the moment the first local patch is carried — cost is zero now and rises with
  each patch.

### Q22 — MSA vs Saudi-dialect terminology

- **Repository facts established:** The bundled catalogue is MSA at 47.53% coverage (6,290 of 13,234 unique
  constants). Fidelity is unmeasured.
- **External fact required:** Linguistic register preference for clinical vs patient-facing screens.
- **Who must answer:** Product, with a Saudi clinical reviewer.
- **Exact question:** *"Do we standardise on MSA everywhere, or use Saudi dialect for patient-facing
  screens — and who owns the glossary?"*
- **Safe provisional default:** MSA everywhere. Mixing registers without a glossary owner produces the worst
  outcome, so a single register is the safe starting point.

### Q13 — Tenant-count ceiling

- **Repository facts established:** The binding constraints are measurable. Connection pooling is per-{Apache
  worker × DSN} (`src/BC/DatabaseConnectionFactory.php:113,137-151`), so DB-per-tenant implies up to
  N_tenants × M_workers persistent handles. Cross-site iteration is an `opendir(sites/)` walk
  (`admin.php:75-115`). Background jobs run once per site per cron.
- **External fact required:** Commercial tenant forecast.
- **Who must answer:** Product.
- **Exact question:** *"What is the 24-month tenant forecast, and at what tenant count do we commit to a
  jobs consolidator?"*
- **Safe provisional default:** Design for 500, revisit at 200. Cap Apache workers or disable pooling early.

### Q15 — Cross-tenant analytics timing

- **Repository facts established:** Under the locked Model A there is **no** cross-tenant query path. Sites
  are physically separate databases and site-local ids collide by design (two tenants both have
  `documents.id = 1`). Analytics requires an ETL keyed on `(site_name, entity_id)` or UUIDs.
- **External fact required:** Whether HQ dashboards are an MVP commitment.
- **Who must answer:** Product.
- **Exact question:** *"Are cross-tenant / multi-clinic dashboards an MVP commitment, or Phase 2?"*
- **Safe provisional default:** Day-N for dashboards — **but enforce the `(site_name, entity_id)` / UUID rule
  from the very first `saas_` table**, because retrofitting global identity later is expensive.

### Q19 — Hijri calendar scope

- **Repository facts established:** Zero Hijri support anywhere (no `hijri`, `IntlCalendar`, `moment-hijri`
  matches); `library/date_functions.php:43-54` is hard-Gregorian. Entirely greenfield.
- **External fact required:** Which fields must display Hijri.
- **Who must answer:** Product with a Saudi clinician.
- **Exact question:** *"Which specific fields must show Hijri — DOB, appointments, billing/invoices, lab
  timestamps — and dual-display or Hijri-primary?"*
- **Safe provisional default:** Dual-display on appointment and billing screens; Gregorian-only on clinical
  timestamps. Cost scales with surface count, so scope by field list, not by "add Hijri support".

### Q32 — Patient portal strategy

- **Repository facts established:** The portal is a separate Smarty app with its own credentials
  (`patient_access_onsite`), its own logo resolution (`portal/index.php:62-64`) and its own theme selection
  (`interface/globals.php:486-495`). Swapping it does not disturb the main UI.
- **External fact required:** Patient-facing mobile expectations at launch.
- **Who must answer:** Product.
- **Exact question:** *"Is an app-like patient portal an MVP requirement, or is a rebrand of the existing
  portal acceptable for launch?"*
- **Safe provisional default:** Rebrand for MVP. The portal's proven isolation means a greenfield SPA can be
  built alongside and cut over tenant-by-tenant later — a genuinely low-regret path.

### Q40 — Inferno ONC certification scope

- **Repository facts established:** Inferno ships as two git submodules
  (`ci/inferno/onc-certification-g10-test-kit`, `ci/inferno/inferno-files`) plus `inferno-test.yml`. It
  validates US ONC / US Core conformance, which carries no Saudi regulatory force.
- **External fact required:** Whether any customer or partner requires ONC certification.
- **Who must answer:** Product / Sales.
- **Exact question:** *"Does any current or pipeline customer require ONC certification or US Core
  conformance?"*
- **Safe provisional default:** Remove from required checks, keep the config so re-enabling is one line. It
  still provides a useful regression net over the FHIR surface that NPHIES work will touch.

---

## Deployment / operations

### Q3 — Target orchestration platform

- **Repository facts established:** **Zero** Kubernetes, Helm, Nomad or Swarm artifacts. The 26
  docker-compose files are all CI matrices and developer stacks — **they are not production artifacts** and
  must not be mistaken for a deployment baseline. Orchestration is greenfield whatever is chosen.
- **External fact required:** Platform standard and budget.
- **Who must answer:** Ops / Platform.
- **Exact question:** *"Is production Kubernetes, and do we have managed-k8s budget in an in-Kingdom region
  (which Q45 may constrain)?"*
- **Safe provisional default:** Kubernetes + Helm, charts in a separate infra repo. Nothing in-repo blocks or
  favours any option.

### Q14 / Q41 — Infra config and chart location

- **Repository facts established:** No per-tenant vhost, TLS or log-shipping config exists in-repo, and no
  charts exist — nothing to migrate either way. The only per-site executable seam is `sites/<site>/config.php`
  (`interface/globals.php:649`), which is **arbitrary PHP executed per tenant** and must be operator-controlled,
  never tenant-writable.
- **External fact required:** Repository topology and release-coupling preference.
- **Who must answer:** Ops.
- **Exact question:** *"One repo or two for app and infra, and who owns the coupling between an app release
  and an infra rollout?"*
- **Safe provisional default:** Separate infra repo, referenced by SHA from this fork's release notes.

### Q39 — Publish our own Docker images

- **Repository facts established:** 59 workflows ship, including the docker-build/release family. Divergence
  from upstream's image is **already guaranteed** by locked decisions: Arabic assets (Q18), Amiri/Noto fonts
  (Q25), a vendored `bootstrap-rtl` (Q24) and an NPHIES module.
- **External fact required:** Registry choice and signing policy.
- **Who must answer:** Ops.
- **Exact question:** *"Which private registry, and do we require image signing / SBOM attestation?"*
- **Safe provisional default:** Publish our own images to a private registry, adapting upstream's
  docker-build workflows.

---

## Summary table

| Q | Category | Owner | Blocking? | Safe provisional default |
|---|---|---|---|---|
| Q45 | Legal | Legal/DPO | **Yes** | Kingdom-only; no cross-border for anything derived from tenant data |
| Q21 | Regulatory | Legal/Finance | Partly | Phase 2; start tax-amount persistence now |
| Q48 | Regulatory | Legal | Partly | 7 years; build pruner disabled |
| Q2 | Business | Eng leadership | **Yes** | Per point release + security out-of-band |
| Q22 | Business | Product + reviewer | **Yes** | MSA everywhere |
| Q13 | Business | Product | No | Design 500, revisit 200 |
| Q15 | Business | Product | No | Day-N; enforce id rule from day one |
| Q19 | Business | Product + clinician | No | Dual-display on admin dates only |
| Q32 | Business | Product | No | Rebrand for MVP |
| Q40 | Business | Product/Sales | No | Drop from required checks, keep config |
| Q3 | Deployment | Ops/Platform | No | k8s + Helm, separate repo |
| Q14 | Deployment | Ops | No | Separate infra repo |
| Q41 | Deployment | Ops | No | Separate infra repo |
| Q39 | Deployment | Ops | No | Publish our own to a private registry |

**Recommended sequencing:** Q45 first — it constrains Q3, Q14, Q39 and Q41. Q48 and Q21 next, since both
imply build work that is cheaper to start early than to retrofit.
