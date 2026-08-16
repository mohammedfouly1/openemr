# EV-064/081/084/085 — HOSTING PROVISIONING, VENDOR-QUOTE, BACKUP-POLICY, MONITORING AND TLS/DNS PACK

**Requirements:** RDY-0064 (hosting), RDY-0081 (backup policy), RDY-0084 (monitoring), RDY-0085 (TLS/domain/DNS)
**Gates:** RDY-0064 → G3 G6 · RDY-0081 → G3 · RDY-0084 → G3 · RDY-0085 → G3 G6 (unchanged — §0.0 Rule 3, no gate count recalculated by this entry)
**Owner (role):** Founder / Product Owner + DevOps / Infrastructure
**Produced:** 2026-08-16 · **Agent D (AGENT-HOSTING)**, Phase 2B, CONTINUATION per `AGENT-CLAIMS.md` row `0064/0081/0084/0085`

---

## 0. What this file is, and is not

This is a **continuation**, not new research. It reshapes and extends work that already exists into
the four specific shapes the Owner asked for. It does **not** re-derive anything already settled.
**Source material read in full before writing anything below:**

| Existing artefact | What it already establishes | Disposition here |
|---|---|---|
| Requirements doc §8434 (RDY-0064 card) + PB-022 (`:7518`) | Decision: KSA / Google Cloud / `me-central2` (Dammam), AWS/Azure comparison closed unless a technical blocker appears, provisioning BLOCKED — EXTERNAL | Cross-referenced, not repeated. §2 below adds the ordered pre-account checklist that was missing |
| Requirements doc §8603 (RDY-0081 card) + PB-021/PB-023 (`:7540`) | Target backup architecture diagram, closure-condition table, "local copy is staging only," "no cloud credential in source" rule | Cross-referenced. §3 below fills in the schedule/retention/encryption specifics the architecture names but does not yet specify in numbers |
| `docs/evidence/EV-084-monitoring-requirements.md` | All six signals defined; **M-6 is already specified as "2 × the service's own `execute_interval`"** (`EV-084:26`), not "any overdue moment" | **Already correct — no rewrite needed.** §4 below confirms this reading and adds the concrete per-service numbers EV-084 states as a formula but does not tabulate |
| `docs/evidence/EV-047-deployment-runbook.md` §9 (Step 7) and §10.5 (Step 8.5) | §9 already names the console-session dependency by name and forbids copying it to a customer instance; §10.5 already specifies `win-acme`/ACME/redirect-vhost as a specification, flagged NOT executed | Cross-referenced. §5 below extends §10.5 with the domain/DNS sequencing this pack's provisioning checklist needs, and elevates §9's warning into an explicit vendor-facing requirement (§6) |
| Requirements doc PB-142 (`:2758`) / PB-181 (`:2632`) | Live-reproduced defect: trigger runs interactive-only, does not survive logoff; ~10 hours overdue observed; converting to a non-interactive logon type was refused (`Access is denied`) even before hitting the deeper `G:` per-session-mount blindness that would defeat it anyway | Treated as a live, reproduced defect throughout — not theoretical. §6 is built directly on this finding |
| `docs/evidence/EV-055-audit-phi-determination.md` §on backups (`:68`, `:90`) | "Backups are unencrypted... encryption at rest is an open RDY-0081 item" — the audit log a backup contains is PHI in base64, not encrypted | Cited in §3.3 as the reason CMEK is a privacy control, not only an availability one |
| No `EV-081` standalone file exists | Confirmed by direct filesystem search (`docs/evidence/EV-081*` → no match) and by `AGENT-CLAIMS.md:262` (`RDY-0081 \| AGENT-DOC \| DONE (not closed) \| PB-150`) — AGENT-DOC read PB-021/PB-023 in full on 2026-08-16 and found them "current and accurate... no change needed," and never extracted a standalone file | This document is the first standalone `EV-081`-labelled artefact; §3 supersedes nothing, it adds the numbers PB-021/PB-023 left as architecture rather than policy |

**Closure discipline, stated up front so it is not missed at the bottom:** nothing in this document
closes RDY-0064, RDY-0081, RDY-0084 or RDY-0085. All four remain blocked on work only the Owner or a
real external vendor can perform — an account with a payment method, a provisioned region, an actual
quote, a real certificate issued against a real domain. This document is the specification that makes
that external execution well-defined; it is not a substitute for it. See §7.

---

## 1. RDY-0064 — Provisioning runbook up to "Owner creates account"

**Boundary, stated explicitly:** this checklist stops at the point where the Owner must act with a
real identity, a real payment instrument and real legal authority — none of which this agent can
supply or simulate. Steps after that boundary (actual resource creation) are sequenced and named but
**not executed**, and are handed to DevOps/Infrastructure once the account exists. Application-level
provisioning (the OS/PHP/Apache/MariaDB/OpenEMR stack itself) is **already fully specified in
`EV-047` §§1–10** and is not repeated here — this checklist covers the **infrastructure layer that
must exist before `EV-047` Step 1 can begin**, which `EV-047` §0.1 explicitly lists as "not covered"
(*"Where the instance runs... Hosting region decided (Dammam), provisioning blocked — EXTERNAL"*).

### 1.1 Ordered checklist — DevOps preparation (no account required yet)

| # | Item | Detail |
|---|---|---|
| P0-1 | Confirm the decision record is unchanged | PB-022: KSA / Google Cloud / `me-central2` (Dammam). Re-read it live before acting — decisions in a concurrently-edited document can be revised (§0.0 Rule 4) |
| P0-2 | Translate the GTM hosting model into an infrastructure shape | **Per-clinic deployment, database per site, manually provisioned** (CLM-0029/L-07, `:8342`) — i.e., **one dedicated VM + one dedicated database per customer instance**, not a shared multi-tenant platform. This determines every sizing and IAM decision below |
| P0-3 | State the capacity assumption, and label it as an assumption | **Proposed, not measured — RDY-0069 (cost instrumentation) is the mechanism that replaces this with real figures after pilot #1.** Sized for a single design-partner clinic in year 1: a handful of concurrent staff sessions, a database and document store starting near the demo baseline scale (283 tables; demo dataset `patients=30`) and growing slowly. This assumption sets a *starting* machine type and disk size for the quote request in §2 — it is not a claim about what a real clinic will need, and it must be revisited once RDY-0069 has actuals |
| P0-4 | State the isolation model | Single-tenant per clinic: dedicated Compute Engine instance, dedicated database, dedicated document storage, dedicated backup bucket path. No shared database, no shared application process between customers — matches CLM-0029's "not multi-tenant SaaS" and avoids inventing a tenancy model the product does not have |
| P0-5 | Confirm `me-central2` region availability for the account that will be created | **`me-central2` is a newer, access-restricted Google Cloud region.** Some restricted regions require an explicit region-access request/allowlist approval from Google before resources can be created there, independent of billing being active. **This must be checked at account-creation time, not assumed** — if `me-central2` is not immediately available to a fresh billing account, that is itself a fact for the decision record (PB-022 already flags *"unless a technical blocker is found in `me-central2`"*), not a silent workaround to a different region |
| P0-6 | Draft the naming/tagging convention | Project name, VM name, bucket name, service-account names — one scheme, applied consistently, so a second customer instance (RDY-0101, deferred) does not require re-deriving it. Not committed to source; recorded in the DevOps runbook only |
| P0-7 | Draft the IAM design (roles, not principals) | Three service accounts, least-privilege, matching PB-023's "no cloud credential in source" rule: (a) **compute service account** — attached to the VM, scoped to read its own backup bucket path for write, nothing else; (b) **backup service account** — write-only object-create on the backup bucket, no delete, no list-other-buckets, no KMS key access beyond the one CMEK key; (c) **break-glass/admin role** — human-assigned, not a static key, for the rare case of manual recovery. No key file is ever placed in source, Markdown or a command line — attach the service account to the VM (Workload Identity / attached service account) rather than distributing a JSON key |
| P0-8 | Draft the network posture | VPC with a single subnet in `me-central2`; firewall allowing inbound `80`/`443` only from the internet, `22`/`3389` (admin) restricted to a named admin IP range, MariaDB bound to loopback/internal only (matches `EV-047` §4's existing `127.0.0.1`-only rule) — no direct database port exposed externally |
| P0-9 | Draft the budget control | A budget alert threshold set **before** any resource is created, not after — this is a checklist item for whoever holds the account, not a number this document invents |
| P0-10 | Domain/DNS pre-requisite | Per `EV-047` §10.5's own note, the subdomain must be registered and DNS-delegated **before** `EV-047` Step 1 begins. Decide at this stage whether DNS is delegated to Google Cloud DNS (keeps the whole footprint in one console, one IAM boundary) or kept at an existing registrar with an A/CNAME record pointed at the eventual instance IP. Either is workable; record which was chosen in §12 of `EV-047` when executed |

### 1.2 The Owner's action — the boundary this checklist stops at

| # | Owner action | Why it must be the Owner, not DevOps |
|---|---|---|
| OW-1 | Create (or designate) the Google Cloud **billing account** and attach a real payment method | Requires legal/financial authority this agent does not have and must not simulate |
| OW-2 | Create the Google Cloud **project** for this pilot, using the naming convention from P0-6 | Project creation under a billing account is an account-holder action |
| OW-3 | Grant a named DevOps/Infrastructure principal (individual, not a role) **IAM Owner or a scoped Editor role** on that project | So provisioning (§1.1's remaining infrastructure steps, then `EV-047`'s application steps) can proceed without the Owner performing every subsequent click |
| OW-4 | Confirm `me-central2` region access is enabled for the new billing account (P0-5) | Only the account holder can request/verify region-access approval from Google if it turns out to be gated |
| OW-5 | Supply the resulting project ID and the delegated principal's access to DevOps/Infrastructure through the secret-handling channel `EV-047` §6 already defines (P-4: a password manager/secret store — never a text file, never committed) | Closes the loop: from here, DevOps can execute P0-1…P0-9's remaining resource creation, then hand off into `EV-047` Step 1 |

**Everything past OW-5 is DevOps/Infrastructure resource creation against the now-real project** — VM
creation, bucket creation, KMS key creation, DNS zone creation — sequenced by §1.1 above and then by
`EV-047` §§1–10 for the application layer. **None of it is executed by this document**, consistent with
the explicit instruction not to create a real cloud account.

---

## 2. RDY-0064 — What to request from each candidate vendor, so the two quotes are comparable

RDY-0064's own acceptance criterion requires **two quotes** supporting the recorded decision
(`:8441`, `:8497`). PB-022 already closed the *cloud/region* question (Google Cloud, `me-central2`,
AWS/Azure comparison closed absent a technical blocker) — so the two quotes are not "which cloud" but
**who provisions, operates and supports that Google Cloud environment**, since GTM-006 makes hosting,
patching, backup and support part of the paid product (`:733`), not merely compute pricing. Candidate
vendor categories include a Google Cloud reseller/managed-service partner and an independent local
systems integrator/MSP offering managed hosting on Google Cloud — **which vendors to contact is the
Owner's decision; none is named or implied here.**

**The only way two quotes are comparable is if both vendors price the identical spec.** The checklist
below is the spec to send to *every* candidate, unchanged between them.

### 2.1 Technical specification to send verbatim to each vendor

| Item | Value to give the vendor |
|---|---|
| Cloud | Google Cloud (decided, PB-022) |
| Region | `me-central2` (Dammam), or the vendor's confirmed equivalent if `me-central2` proves inaccessible (P0-5) — state this contingency to the vendor rather than silently substituting a region |
| Deployment model | Single-tenant: one VM, one database, one document store per clinic instance (§1.1 P0-2/P0-4). **Explicitly not multi-tenant SaaS** |
| Compute profile | Windows Server, PHP 8.3.x (33 required extensions incl. `imagick`, `redis`), Apache 2.4 with `mod_php`, MariaDB 11.x — same MSVC toolchain requirement `EV-047` P-2 already names. Ask the vendor to size the VM against the capacity assumption in §1.1 P0-3, stated as an assumption to them too |
| Storage | Database volume + a separate `sites/*/documents/` volume (per `EV-084` M-3's own reasoning that the two grow independently) |
| Backup target | An **off-instance** Cloud Storage bucket in the **same region**, versioning enabled, **CMEK** (customer-managed encryption key via Cloud KMS, key region-pinned) — ask the vendor to price both the bucket and, if they operate it, the KMS key management |
| Retention | Ask the vendor to price against the schedule in §3.4 below (daily/weekly/monthly rolling retention) rather than their own default, so quotes are retention-equivalent |
| Networking | VPC + firewall as specified in §1.1 P0-8; ask whether the vendor's offering includes a static external IP and whether a load balancer is included or additional |
| TLS/domain | ACME-issued certificate (Let's Encrypt via `win-acme` or the vendor's equivalent for a Windows/Apache target), auto-renewal, HTTP→HTTPS redirect — per `EV-047` §10.5. Ask the vendor to confirm their renewal mechanism runs under a **non-interactive service account that survives logoff/reboot** — this is not optional, see §6 |
| Background-service trigger | **⚠ Mandatory requirement — see §6.** The vendor's environment must support a scheduled task/cron-equivalent running `php bin/console background:services run` under a service-account identity that survives logoff and reboot. Ask the vendor to confirm this explicitly; do not accept "yes" without naming the mechanism (Windows Task Scheduler with a Batch/S4U/gMSA logon, or equivalent) |
| Monitoring | Ask whether the vendor's platform natively supplies M-1 (availability), M-3 (disk), M-4 (DB status) per `EV-084` §5's own note that a managed platform may cover these natively — and confirm the vendor can support a **custom check with an exit code** for M-5 (backup success) and M-6 (background-service health, 2×interval — see §4), since `EV-084` §5 states plainly that no general-purpose monitor understands either check out of the box |
| Patching | Who applies OS/PHP/Apache/MariaDB security patches, on what cadence, with what notice/maintenance-window commitment |
| Support | Response-time commitment for a paging-severity incident (per `EV-084` M-1/M-4/M-5's "paging channel" destinations) vs. a non-paging one |
| Data residency attestation | A written statement that data at rest and backups never leave `me-central2` (or the confirmed substitute region), covering both primary storage and the backup bucket — **this is an architecture attestation, not a regulatory-compliance claim** (per PB-022's own claim constraint, `:7529` — never let a vendor's residency attestation be represented as PDPL/CHI/NPHIES/ZATCA compliance) |
| Contract terms | Term length, exit/off-boarding process and timeline, data-export assistance on termination (ties to RDY-0073), any minimum commitment, and pricing broken out **by line item** (compute, storage, backup storage, egress, support, patching) rather than a single bundled number — itemization is what makes two quotes actually comparable, since a bundled figure hides which vendor is cheaper on which axis |

### 2.2 Discipline for requesting the quotes

- Send the **same written spec** (§2.1) to both vendors — not a verbal description that can drift
  between conversations.
- Request **itemized** pricing, not a single total, for the reason stated above.
- Record, for each vendor: date contacted, spec version sent, quote received date, and the raw quote
  document. **Do not summarize a quote into a single number without keeping the source document** —
  the acceptance criterion is "supported by two quotes," which means the quotes themselves are the
  evidence artefact, not a paraphrase of them.
- **This document supplies the spec only.** No quote exists yet, no vendor is named as selected, and
  no number is invented anywhere in this file.

---

## 3. RDY-0081 — Backup policy, completed for the decided Dammam/`me-central2` region

Builds directly on PB-023's target architecture (`:7540`) — reproduced here for reference, not
redefined:

```
Authoritative OpenEMR
  → thiqa-branding:backup        (implemented, PB-021)
  → verify: "Dump completed" + expected table count   (implemented)
  → SHA-256                       (implemented)
  → off-instance Google Cloud Storage                 ← this section specifies it
  → bucket location me-central2                       ← this section specifies it
  → CMEK customer-managed encryption key              ← this section specifies it
  → retention / lifecycle policy                       ← this section adds the numbers
```

What PB-021/PB-023 already proved (**not repeated, only cited**): the local execution, hashing and
verification steps are implemented and proven (`RDY-0080`, closed); the off-instance/CMEK legs are
**architecturally decided but not yet built**, blocked on RDY-0064's external provisioning leg exactly
as PB-023 states.

### 3.1 Off-instance copy — specification

| Item | Specification |
|---|---|
| Target | A Cloud Storage bucket in **`me-central2`**, dedicated to backups for this instance (one bucket per clinic instance, matching the single-tenant model in §1.1 P0-4) |
| Bucket configuration | Uniform bucket-level access; object versioning **on** (protects against an overwrite/corruption of the most recent backup, not only deletion); public access prevention enforced |
| Upload path | The existing `thiqa-branding:backup` command's output copied to the bucket **immediately after** local verification (SHA-256 + expected table count) passes — never before, so a corrupted local dump is never propagated off-instance |
| Credential | The **backup service account** from §1.1 P0-7 — attached to the VM (no distributed key file), scoped to object-create only on this one bucket, no delete/list-other-buckets/KMS-admin permission. **No cloud credential appears in source, Markdown, or a command line**, per PB-023's own rule, carried forward unchanged |
| Verification the copy landed | After upload, an object-existence + size check against the bucket, logged; this is the mechanism M-5 (`EV-084`) needs to detect "no successful backup in 24h" |

### 3.2 Retention schedule — proposed, not yet Owner/Compliance-reviewed

**Stated as a proposal, in the same register other proposed-but-unreviewed policy numbers in this
project use** (`EV-074`'s 30-day/90-day figures are the precedent for this framing — proposed defaults,
explicitly flagged as not yet Legal/Compliance reviewed). This section is scoped to **ongoing
operational backups during an active pilot/customer engagement** — not the post-termination deletion
timeline, which is `EV-074`'s scope and is cross-referenced, not re-derived, here.

| Tier | Frequency | Retention | Rationale |
|---|---|---|---|
| Daily | One backup per day | Rolling **14 days** | Covers the common recovery window (a data-entry error noticed within two weeks) without the storage growth of keeping every daily copy indefinitely |
| Weekly | One backup per week (e.g., the daily run nearest a fixed weekday) | Rolling **8 weeks** | Covers a slower-to-notice problem (a misconfiguration that took a month to surface) |
| Monthly | One backup per month | Rolling **12 months** | Matches a typical annual audit/compliance review cycle; also the ceiling this section proposes pending Owner/Compliance sign-off |

Implementation mechanism: a Cloud Storage **lifecycle policy** on the bucket (age-based rules per the
tiers above), so retention enforcement does not depend on the application or a separate cron job
remembering to delete old copies — it survives the exact "unattended trigger" failure mode named in
§6.

**This schedule requires the same Owner/Compliance review `EV-074`'s figures are still waiting on.**
It is not implemented, because there is no bucket to implement it on yet (RDY-0064 blocked).

### 3.3 Encryption at rest — CMEK

| Item | Specification |
|---|---|
| Mechanism | Customer-managed encryption key (CMEK) via Cloud KMS, **key ring located in `me-central2`** — a region-mismatched key would itself violate the residency intent PB-022 records |
| Why this matters more than a generic availability control | `EV-055` (`:68`, `:90`) already establishes that a database backup contains the audit log, and **the audit log carries PHI in base64 — an encoding, not an encryption** — on a real (non-synthetic) instance. `EV-055`'s own remediation list names *"Encrypt backups at rest"* as *"the cheapest large risk reduction available."* CMEK on the backup bucket is that control. This is a **privacy control**, not only a disaster-recovery one — carried forward from RDY-0081's own card text (`:8607`), not introduced here |
| Key access | Restricted to the backup service account (encrypt/decrypt only) and the break-glass admin role (key-admin, for rotation/recovery) — no other principal |
| Rotation | Automatic rotation on a defined period (a 90-day rotation is Cloud KMS's common default for symmetric keys) — stated as a starting default for DevOps to confirm at implementation, not a figure this document treats as final |

### 3.4 Automated success verification and failure signal

**Does not duplicate `EV-084` — cross-referenced.** RDY-0081's own acceptance criterion requires *"a
deliberately failed backup raises the defined signal"* (`:8608`). That signal **is** `EV-084`'s **M-5**
(`EV-084:25`): *"No successful backup in 24 h, or table count ≠ expected"* → paging channel. This
section's contribution is naming the two concrete failure conditions M-5 must be tested against once a
real target exists:

1. **Local execution failure** — the `thiqa-branding:backup` command itself fails or produces a
   table count below the expected 283 (or the then-current count). Already partially provable today,
   without hosting, since the command runs locally (see RDY-0080's closed evidence).
2. **Off-instance copy failure** — the local dump succeeds but the upload to the `me-central2` bucket
   fails (network, credential, or quota). **This leg cannot be tested until the bucket exists** — it is
   the specific new failure mode this section's architecture introduces, and M-5's monitoring check
   must query bucket-object-existence, not just local-file-existence, once implemented.

**Failure injection is not performed by this document.** No bucket exists to inject a failure into.
This section specifies what the injection test must cover when RDY-0064 unblocks.

### 3.5 Named human owner — still open, not fabricated here

PB-023 already records: *"Role named; individual not yet named."* This document does not name one —
per the closure contract (§0.0 Rule 5), fabricating a sign-off or an owner name is prohibited. This
remains an open item for the Owner to assign, exactly as PB-023 left it.

### 3.6 RDY-0081 closure-condition table, updated with this section's contribution

| Closure condition (PB-023) | State before this document | State after this document |
|---|---|---|
| Off-instance bucket exists | BLOCKED — EXTERNAL PROVISIONING | Unchanged — still blocked on RDY-0064. **Now fully specified** (§3.1), so creation is mechanical once unblocked |
| Encryption decision implemented (CMEK) | Pending bucket | Unchanged — pending bucket. **Now fully specified** (§3.3) |
| Retention policy exists | Local retention implemented; cloud lifecycle pending | **Cloud lifecycle schedule now specified** (§3.2), pending Owner/Compliance review and a bucket to apply it to |
| Scheduled backup copies successfully | Command scheduler-ready; schedule not installed | Unchanged |
| Backup verified present at off-instance target | Pending bucket | **Verification mechanism now specified** (§3.1 last row, §3.4) |
| Named human owner assigned | Role named; individual not yet named | Unchanged — not this document's decision to make |
| Restore from off-instance copy passes RDY-0082 | Pending bucket | Unchanged — RDY-0082's local-restore proof (six of seven legs MET, PB-182) does not substitute for an off-instance-copy restore, which needs the bucket to exist first |

**Status RDY-0081: NOT CLOSED.** Unchanged from PB-023's own assessment — every remaining condition
is blocked on RDY-0064's external provisioning leg or on Owner/Compliance review, neither of which this
document can perform.

---

## 4. RDY-0084 — Monitoring specification: M-6 threshold confirmed, and made concrete

**`EV-084` already specifies M-6 correctly.** Quoting it exactly (`EV-084:26`):

> *"No `active = 1` service has `next_run` in the past by more than **2 × its own `execute_interval`**"*

This is **already** the "2× the service's own interval" formulation the assignment asked for — **not**
"any overdue moment." `EV-084` §2 (`:44`) also already states the reasoning: *"a fixed threshold would
either spam on the first [2-minute service] or never fire on the second [240-minute service]."*
**No change to `EV-084` was needed or made.**

What was missing was the **concrete per-service number** the formula produces — `EV-084` states the
rule, not the resulting minutes. Added here as a direct, checkable extension:

| Service | `execute_interval` (from `EV-083`) | M-6 threshold (2× interval) | Meaning |
|---|---|---|---|
| `Email_Service` | 2 minutes | **4 minutes** | If `next_run` falls more than 4 minutes in the past, M-6 fires |
| `UUID_Service` | 240 minutes | **480 minutes (8 hours)** | If `next_run` falls more than 8 hours in the past, M-6 fires |

**Why this matters concretely, tied to the live PB-142 finding**: PB-142 observed both services
**~10 hours overdue** after an unobserved console-session end. Against the table above, that
observation **would have tripped M-6 for `Email_Service` at the 4-minute mark** (roughly 10 hours
before anyone happened to check) and **would have tripped M-6 for `UUID_Service` at the 8-hour mark**
(roughly 2 hours before the observation) — on a hosted instance with M-6 actually wired up. On this
demo host, no monitoring is wired up, so nobody was paged; the overdue state was only found because an
auditor happened to query the table. **This is exactly `EV-084` §3's own point** (`:56`: *"M-6 would
have fired... and nobody would have had to notice it by reading a table"*), now shown against the
specific live incident rather than argued abstractly.

**Acceptance criterion re-check (unchanged from `EV-084`):** signal, threshold, destination and owner
are all defined for M-6, per `EV-084` §6's own acceptance table — this document adds no new acceptance
claim, it confirms the existing one reads correctly against the exact wording the assignment specified
and ties it to the live incident. **Nothing here is implemented** — there is no hosted pilot to alert
on, exactly as `EV-084` §5/§6 already state.

---

## 5. RDY-0085 — TLS/DNS plan, extending `EV-047` §10.5

`EV-047` §10.5 already specifies the certificate mechanism (`win-acme`/ACME), the renewal
self-registration caveat (flagged to be verified against the same risk class RDY-0083 hit), and the
HTTP→HTTPS redirect vhost. **Not rewritten here.** This section adds the two things §10.5 does not yet
cover: how the domain/DNS step sequences against this pack's §1 provisioning checklist, and an explicit
tie to §6's mandatory service-account requirement.

| Item | Specification |
|---|---|
| Sequencing | Domain registration/DNS delegation (§1.1 P0-10) must complete **before** `EV-047` Step 1 (application install) begins, and **before** `win-acme` can request a certificate — ACME issuance requires the domain to already resolve to the target instance's IP. This pack's §1 checklist is the first point in the whole provisioning sequence where the domain decision belongs |
| DNS hosting | If Cloud DNS is chosen (§1.1 P0-10's option), the zone lives in the same Google Cloud project as the compute/storage resources — one IAM boundary, one console, no separate registrar credential to manage outside the secret-handling rule (`EV-047` §6) |
| Certificate renewal task — **the same failure class as RDY-0083, must be verified, not assumed** | `EV-047` §10.5 already flags this explicitly: *"Verify this explicitly during the first real provisioning — do not assume by analogy."* This pack adds the specific check: confirm the `win-acme` renewal Scheduled Task is registered with a **non-interactive logon type** (Batch/S4U/gMSA) that survives logoff, **on a host whose application directory is not a per-session network mount** (unlike this demo host — see §6). If the pilot host is a standard Google Compute Engine VM with a local NTFS disk, the per-session-mount blindness that defeated RDY-0083's own conversion attempt (PB-181) **does not apply**, because there is no Google-Drive-style per-session mount involved — but this must be **proven on the real host**, not inferred from that difference, exactly as `EV-047` §10.5 already insists |
| Verification once executed | Unchanged from `EV-047` §10.5: `curl -I http://<domain>/` → redirect; `curl -I https://<domain>/` → `200`; certificate validity/issuer checked; renewal exercised once or its expiry date placed under `EV-084` monitoring (a natural seventh candidate signal, though the six in `EV-084` remain the acceptance-defined set) |

**Status: unchanged from `EV-047` §10.5 — still NOT READY.** No acceptance criterion is met here
either; all require a real reachable pilot instance, blocked on RDY-0064.

---

## 6. ⚠ MANDATORY — service-account requirement, and the warning against copying this dev host's arrangement

**This is the single most load-bearing requirement in this pack, stated once here and referenced from
every other section rather than diluted across them.**

### 6.1 The live, reproduced defect (not theoretical)

Quoting the requirements document's own record of what was actually observed, twice, on this
development host:

- **PB-142 (`:2758`):** `httpd.exe`/`mariadbd.exe` fully stopped after an unobserved console-session
  end; on restart, both `Email_Service` and `UUID_Service` were found **~10 hours overdue**
  (`next_run` ≈ 05:23–05:25 against a database clock of 15:17).
- **PB-181 (`:2632`):** the same session then tested — empirically, not by inference — whether the
  trigger could be converted to a logon type that survives logoff. **Both attempts failed**:
  `schtasks /Create ... /RU <user>` (implying S4U) → `ERROR: Access is denied.`; `Register-ScheduledTask`
  with `-LogonType S4U` → `Access is denied.` **And even with elevation, the deeper cause would still
  defeat it**: the task's target lives on a Google-Drive-mounted volume that is mounted per interactive
  user session, so a non-interactive logon (S4U/Batch/ServiceAccount) is architecturally blind to it —
  the identical constraint that already forces Apache/MariaDB to run as console processes rather than
  Windows services on this host (`CLAUDE.local.md` §4a).

**This is a demo-host limitation, not a product defect** — the background-service runner itself works
correctly (`EV-083`, proven at PB-071); it is specifically the **trigger's persistence across a logoff**
that fails, and only because of this one host's storage architecture.

### 6.2 The requirement this imposes on the target hosting environment

**The hosting environment RDY-0064 provisions MUST run the background-service trigger
(`php bin/console background:services run`, per `EV-047` §9) as a proper Windows service account —
one that survives logoff, reboot, and console-session end.** This is not a nice-to-have; it is a
functional requirement of the product working at all (reminders, email queue, UUID backfill all depend
on it), and PB-142/PB-181 prove the failure mode is real, reproducible, and currently unmitigated on
the only environment that has been tested.

Concretely, for the vendor quote request in §2.1 and for whoever executes `EV-047` §9 against a real
host: the trigger must be registered as a Scheduled Task (or platform equivalent) with a **Batch,
S4U, gMSA, or dedicated-service-account logon type** — not "Interactive only," which is what this demo
host's task (`\OpenEMR-Thiqa-BackgroundServices`) is currently and unavoidably stuck at. On a standard
cloud VM with local disk (no per-session network mount), this is expected to work correctly — but
**must be verified on the real host during first provisioning**, per `EV-047` §10.5's identical caveat
about the certificate-renewal task (§5 above).

### 6.3 The warning — explicit, and where it is already partially satisfied

**This dev host's console-session-dependent arrangement must NEVER be copied into the customer-facing
deployment runbook, RDY-0047.** Checked directly: `EV-047` §9 (`:197`) **already carries this warning,
verbatim, and correctly**:

> *"⚠ On the demo host this must be the logged-on user, because Google Drive mounts `G:` per session
> and a `SYSTEM` task cannot see the app at all — so the trigger there does not survive a logoff.
> **That is a demo-host artefact. A customer instance must use a service account and must survive
> reboot. Do not copy the demo arrangement.**"*

PB-181 independently confirmed this wording needed no edit (*"Agent B's original wording already
satisfies the naming requirement... today's investigation independently confirms is the right
boundary"*). **This pack does not change `EV-047` §9** — it elevates the same warning into this pack's
§2.1 vendor spec (so a vendor is told the requirement directly, not left to infer it from an internal
runbook they will never see) and into RDY-0094's demo no-go disclosure line (already updated by
PB-181, §40 row 12, with the exact presenter language: *"...I'd rather tell you that than have the
screen surprise you"*) — cross-referenced, not re-authored.

### 6.4 What remains open

Whether the S4U/Batch conversion actually succeeds on a real, non-per-session-mounted host is **not
yet tested anywhere** — PB-181 tested it only on this host, where the drive-mount constraint made the
test moot before elevation was even the binding issue. **The first real provisioning is where this
gets proven**, exactly as `EV-047` §10.5 already insists for the analogous certificate-renewal task.
This pack does not claim the conversion will succeed on the target host — it specifies the requirement
and the verification step, consistent with the closure contract's ban on asserting an unproven result.

---

## 7. Closure discipline

**None of RDY-0064, RDY-0081, RDY-0084 or RDY-0085 close as a result of this document.** Restated
per-item, so no reader has to infer it:

| Item | Why it stays open |
|---|---|
| RDY-0064 | Decision recorded (PB-022); provisioning and the two quotes require a real account, real payment, and real vendor engagement — none performable by an agent |
| RDY-0081 | Off-instance bucket, CMEK and cloud-side retention all require RDY-0064's bucket to exist; the schedule proposed here (§3.2) also needs Owner/Compliance review, same as `EV-074`'s figures |
| RDY-0084 | Requirements complete (already, per `EV-084`); nothing is implemented because there is no hosted pilot to monitor |
| RDY-0085 | Specification complete (`EV-047` §10.5 + this pack's §5); every acceptance criterion needs a real reachable HTTPS instance |

**This document recommends; it does not self-close.** Recorded per §0.0 Rule 3 and Rule 5: no gate
count is recalculated here, and no closure is asserted beyond what a re-runnable command or a real
external action could support.

---

## 8. Single next action for the Owner

**Create the Google Cloud billing account with a real payment method, and create the project for this
pilot (§1.2, OW-1/OW-2).** Every other item in this pack — the remaining infrastructure provisioning,
the two vendor quotes, the backup bucket, the TLS certificate, the service-account-based background
trigger — sequences directly off that one action. Nothing else in RDY-0064/0081/0084/0085 can move
until it happens.
