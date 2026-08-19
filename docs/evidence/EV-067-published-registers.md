# EV-067 — PUBLISHED STATUS REGISTERS

**Requirement:** RDY-0067 · **Gates:** G5, G6 · **Owner:** Product Marketing
**Source of every count and every entry:** `docs/HISModulesUsers.md` (Source B), report date
**2026-08-09**, §6, §19.3, §23.3, §25, §26, §35.2.
**Produced:** 2026-08-14 · **Agent B**, Phase 2B

---

## 0. What this document is, and what it is for

This is the artefact behind **Pillar 4 (disclosure)** and **differentiator D-1 (verifiability)**.
Its purpose is to publish, in the vendor's own words and ahead of being asked, **everything the
product does not do today** — split into four registers by *why* it does not do it, because the
four have completely different costs to change.

Source C found that **very few competitors publish exclusions at all** — the exact frequency is
withheld under **RDY-0088**, because it is computed over 16 scored competitors while 9 of 26
dossiers remain unverified. **Publish the mechanism, not the number.** GTM §17.7 makes this content
priority #1 — ahead of pricing, ahead of the recorded demo.

**The rule that makes this credible:** a register that quietly drops an inconvenient row is worse
than no register. The counts below are mechanically derived and reproducible (§6), and the
derivation command is printed so a reader can re-run it.

---

## 1. The five categories, and what each costs to change

| Category | Count | What it means | Typical effort to move it up |
|---|---:|---|---|
| **Active** | **177** | Working now on this installation — *once configured with your data*. **Two of the 177 are marked `Active / Operationally BLOCKED`** — see §5.5 | — |
| **Disabled** | **47** | Fully built, switched off by a named configuration flag. **No development.** | **Minutes** — flip one global |
| **Uninstalled** | **27** | Code is on disk, not registered. 16 clinical forms, 11 modules | **Minutes** (form) to **hours** (module) |
| **Requires Integration** | **18** | Built, but useless without a third-party service, contract or credential | **Weeks to months**, mostly non-engineering |
| **Missing** | **60** | Not in the product | **Months**; for the Saudi set, a programme |

**These five must never be merged into a single "Features" list.** That merge is the specific
misrepresentation this document exists to prevent.

---

## 2. REGISTER 1 — DISABLED (47)

> **Mandatory phrasing for every entry in this register:** *"Present in the software and switched
> off by `<flag>`. Turning it on is a configuration change, not development."* **Always name the
> flag.** A Disabled capability must never be described as "available" without the flag named in
> the same sentence.

**39 of the 47 are removed by just 12 flag settings.** For a commercial conversation this is the
single most useful fact in the whole catalogue: most of what looks missing is one configuration
session away.

### 2.1 The 39 that are flag-grouped

| Flag(s) | Off | Capabilities suppressed | What they are |
|---|---|---:|---|
| `rest_api`, `rest_fhir_api`, `rest_portal_api`, `rest_system_scopes_api` | 0 / 0 / 0 / 0 | **10** — CAP-0193…0200, 0207, 0245 | Standard REST API · FHIR R4 US Core · CapabilityStatement & discovery · SMART on FHIR launch · Bulk FHIR `$export` · Portal API · OAuth2 authorization server · dynamic client registration · API client administration (×2 surfaces) |
| `portal_onsite_two_enable` | 0 | **9** — CAP-0031, 0173…0180 | Patient portal: issue credentials · patient login · self-registration · secure messaging · appointment requests · document upload/download · ledger view · online payment · consent signing |
| `inhouse_pharmacy` | 0 | **8** — CAP-0113…0118, 0166, 0249 | Dispensary: drug & product master · lot and expiry tracking · dispense to patient · stock transfer · destroy stock · inventory reporting (4 reports) · warehouse scoping |
| `enable_group_therapy` | 0 | **5** — CAP-0087…0091 | Group therapy: group registry · group appointments · group encounters · attendance · therapist messaging |
| `ub04_support` | 0 | **2** — CAP-0129, 0131 | X12 5010 837I institutional claim · UB-04 (CMS-1450) print |
| `auto_sftp_claims_to_x12_partner` | 0 | **2** — CAP-0133, 0134 | Push claim batches to a partner by SFTP · claim-file delivery tracking |
| `enable_hylafax`, `enable_scanner` | 0 / 0 | **2** — CAP-0187, 0188 | Send a fax via HylaFAX · fax/scan queue |
| `specific_application` (IPPF) | 0 | **1** — CAP-0171 | IPPF / family-planning statistics (5 reports) |

**Per-flag unique-capability coverage:** APIs 10 · portal 9 · in-house pharmacy 8 · group therapy 5
· UB-04 2 · claim SFTP 2 · fax/scanner 2 · IPPF 1 = **39**.

### 2.2 The 8 that are individually gated

| CAP | Capability | Flag | Note |
|---|---|---|---|
| CAP-0032 | Delete a patient record | `allow_pat_delete = 0` | Off by design — deletion is destructive |
| CAP-0148 | Real-time eligibility (CAQH CORE 2.2.0) | `enable_eligibility_requests = 0` | Also needs an endpoint and a payer contract |
| CAP-0189 | Direct secure messaging (send/receive) | `phimail_enable = 0` | Server is still the upstream placeholder |
| CAP-0190 | MedEx recall & messaging service | `medex_enable = 0` | Also an external service |
| CAP-0219 | LDAP / Active Directory sign-in | `gbl_ldap_enabled = 0` | StartTLS supported; host and DN blank |
| CAP-0220 | Google Sign-In | `google_signin_enabled = 0` | Client ID blank |
| CAP-0225 | Forward audit events to a syslog/ATNA collector | `enable_atna_audit = 0` | Host and certificates blank |
| CAP-0250 | Restrict users to their facility | `restrict_user_facility = 0` | With `login_into_facility = 0`; `users_facility` empty |

**39 + 8 = 47.** ✅

---

## 3. REGISTER 2 — UNINSTALLED (27)

> **Mandatory phrasing:** *"The code ships with the product but is not registered on this
> installation. Registration is `<the exact step>`."* Say what registration requires — and where a
> module also needs an external service, say that too, because "installable" and "usable" are not
> the same thing.

### 3.1 Clinical forms (16) — registration is Admin → Forms → register + install SQL, **minutes each**

| CAP | Form | CAP | Form |
|---|---|---|---|
| CAP-0071 | PHQ-9 | CAP-0079 | Clinic Note |
| CAP-0072 | GAD-7 | CAP-0080 | Work / School Note |
| CAP-0073 | Physical Exam | CAP-0081 | Bronchitis |
| CAP-0074 | Treatment Plan | CAP-0082 | Ankle Evaluation |
| CAP-0075 | Transfer Summary | CAP-0083 | Social Screening Tool (SDOH) |
| CAP-0076 | Aftercare Plan | CAP-0084 | Lab Requisition |
| CAP-0077 | Graphic Pain Map | CAP-0085 | Prior Authorization (form) |
| CAP-0078 | CAMOS | CAP-0086 | Track Anything |

**Runtime-verified:** all **16 of 16** installed cleanly into a disposable database, creating 22
tables, with no DDL errors or warnings on MariaDB 11.8.8 (Source B GAP-0071). The disposable
database was dropped afterwards.

**Stated honestly:** that test proves *installability*. It does **not** prove UI rendering, or
absence of PHP runtime errors on load. Three of these are visibly incomplete —
**Clinic Note has no `save.php`**, **Lab Requisition is print-only with no save or report**, and
**Track Anything's configuration menu is gated on its own registry state**. Do not present any of
the sixteen as a finished feature.

### 3.2 Modules (11) — registration is Module Manager → Register → Install SQL → Install ACL → Enable → Configure, **hours**

| CAP | Module | External dependency — **the contract is the customer's to hold** |
|---|---|---|
| CAP-0206 | ONC (b)(10) EHI export | none |
| CAP-0261 | ClaimRev Connect (clearinghouse) | ClaimRev / Claim Revolution, LLC — API via Azure AD B2C OAuth2 |
| CAP-0262 | Comlink Telehealth (video visits) | Comlink video registration + video API |
| CAP-0263 | Dashboard Context Manager | none |
| CAP-0264 | DORN (diagnostic ordering / results) | DORN ConnectorApi |
| CAP-0265 | Fax / SMS / Voice | Twilio, SignalWire, etherFAX, RingCentral or Clickatell |
| CAP-0266 | Prior Authorizations | none, but its registration POSTs clinic details to a third party |
| CAP-0267 | Weno eRx | Weno Exchange |
| CAP-0268 | Patient Filter (upstream reference module) | none |
| CAP-0269 | Patient Validation | none |
| CAP-0270 | Prescription Templates | none |

> **⚠ Disclosed against our own interest — CAP-0266 Prior Authorizations.** Source B found real
> defects in this module: a hard-coded `facility WHERE id = 3`, a query against a `patient_status`
> table that does not exist, **no ACL on its patient page**, and a `registration()` call that POSTs
> the clinic's name, phone and email to an external endpoint. **We do not recommend installing it**
> and we will not install it in a customer instance without a written decision. It is listed here
> because the register would be dishonest without it.

**16 + 11 = 27.** ✅

---

## 4. REGISTER 3 — REQUIRES INTEGRATION (18)

> **Mandatory phrasing:** *"Built and ready to connect. It needs `<named vendor / service>`, and
> **the commercial contract and credentials are yours to hold, not ours**."* Never present an
> integration as included. Integration work is priced as a project, and **only after the customer
> holds the third-party contract**.

| CAP | Capability | Named external dependency |
|---|---|---|
| CAP-0070 | Drug–drug interaction checking | **No interaction engine ships in-tree.** The interaction flag is a *result returned by the external eRx vendor* |
| CAP-0098 | Transmit a lab / procedure order (HL7) | A configured lab `procedure_providers` row + an SFTP endpoint |
| CAP-0099 | Import inbound HL7 results | A live lab result feed |
| CAP-0110 | Transmit an electronic prescription | NewCrop eRx — credentials empty, `erx_enable = 0` |
| CAP-0111 | Handle eRx renewal requests | NewCrop eRx |
| CAP-0112 | EPCS controlled-substance administration | NewCrop eRx |
| CAP-0147 | Generate a 270 eligibility batch enquiry | An `x12_partners` clearinghouse row — **0 present** |
| CAP-0149 | Import and display a 271 response | A real 271 file from a payer |
| CAP-0156 | Stripe card payments | **Stripe** — keys empty |
| CAP-0157 | Stripe Terminal (card present) | **Stripe** |
| CAP-0158 | Authorize.Net payments | **Authorize.Net** — the Omnipay driver *is* installed |
| CAP-0159 | Sphere / TrustCommerce payments | **Sphere / TrustCommerce** — 10 globals empty |
| CAP-0160 | Rainforest Pay | **Rainforest** — 4 globals empty |
| CAP-0170 | Export QRDA Category I / III | An external measure bundle — none present |
| CAP-0185 | Send outbound email | An SMTP service **and** a sender address — both blank today |
| CAP-0186 | Send an SMS notification | An SMS gateway — username, password and API key all empty |
| CAP-0192 | Deliver reminders to patients | Depends on CAP-0185/0186. **Voice and SMS send are commented out in source** |
| CAP-0236 | Load external code sets | Outbound access to external terminology distributors |

**18.** ✅

> **Two of these carry a stronger warning than "not configured".** CAP-0070 has **no interaction
> engine in the product at all** — drug-interaction checking is entirely the eRx vendor's. CAP-0192
> has its voice and SMS delivery path **commented out in the source**, so it will not send by those
> channels even once a gateway exists. Neither may ever be described as "included".

---

## 5. REGISTER 4 — MISSING (60)

> **Mandatory phrasing:** *"Not in the product."* **Never present a Missing item as a roadmap
> commitment without engineering sign-off.** A date given here becomes a promise; there is no
> engineering estimate behind any row below, and none is implied.

**60 = 1 in-catalogue + 59 market-comparison gaps.**

### 5.1 In-catalogue (1)

| CAP | Capability | The precise limitation |
|---|---|---|
| CAP-0218 | **Mandate** MFA for all users | MFA *is* challenged at login — but **only for users who have already enrolled**. There is no global that forces enrolment, so MFA **cannot be mandated**. This must be stated before MFA is discussed, not after |

### 5.2 Saudi market (14) — GAP-0046…0059

| GAP | Capability | GAP | Capability |
|---|---|---|---|
| GAP-0046 | NPHIES connectivity | GAP-0053 | Saudi VAT (15 %) |
| GAP-0047 | CHI / CCHI | GAP-0054 | ACHI / ACCI / ICD-10-AM coding |
| GAP-0048 | Saudi National ID / Iqama identifier | GAP-0055 | SBS (Saudi Billing System) |
| GAP-0049 | Saudi payer identifiers | GAP-0056 | Saudi FHIR IG / NPHIES profiles |
| GAP-0050 | Saudi provider / facility identifiers | GAP-0057 | Arabic patient name structure |
| GAP-0051 | SFDA drug registration | GAP-0058 | Hijri calendar / Hijri DOB |
| GAP-0052 | ZATCA / Fatoora e-invoicing | GAP-0059 | Saudi national address structure |

> **This is the register that must never be softened.** There is **no tax field anywhere** in
> `billing`, `ar_activity`, `payments`, `prices` or `fee_schedule`. Patient identifiers are US
> (`ss`), payer identity is X12/CMS, provider identity is US NPI + NUCC taxonomy, drug identity is
> RxNorm/NDC, the FHIR server is profiled to US Core, names are Western given/middle/family, and
> `list_options.state` ships seeded with **52 US states**. The words *"Saudi compliant"*,
> *"ZATCA compliant"* and *"NPHIES compliant"* are **prohibited** in every artefact.

### 5.3 Care settings (14) — GAP-0001…0014

Emergency Department / triage · Inpatient / IPD · ADT workflow · and eleven further inpatient and
care-setting capabilities. **This is an outpatient product.** Hospital language is prohibited.

### 5.4 Ancillary services (8), Access and engagement (5), Revenue and back office (12), Data and platform (6) — GAP-0015…0045

Includes LIS / RIS / PACS, ERP, analytics/BI platform, mobile applications and multi-tenancy. Each
was searched with synonym expansion across the whole repository, with the search terms, the
locations searched and what was found recorded per row in Source B §26.

> **Negative-evidence discipline, stated because it is the reason to trust the register.** Source B
> rejected several apparent capabilities on an explicit disambiguation rule: `src/FHIR/R4/**`
> contains machine-generated model classes for *every* FHIR resource type, so the presence of
> `FHIRImagingStudy` is **not** an imaging capability. The same rule was applied to the CDA2 XSDs
> and the X12 lookup tables. **Finding a filename is not finding a feature.**

**1 + 59 = 60.** ✅

### 5.5 The two `Active / Operationally BLOCKED` capabilities

Not a fifth register — a warning attached to two rows of the Active one. Source B marks these
`A / Op: BLOCKED`: the code is Active, but on the audited installation it **could not actually
run**. Publishing them as plain "Active" would be the single most misleading thing in this
document, because a demonstration of either would fail in front of the customer.

| CAP | Capability | Why it was blocked (2026-08-09) | State on this instance today |
|---|---|---|---|
| CAP-0201 | Generate and import C-CDA | The Carecoordination module is active, but the C-CDA Node service is **not listening on 127.0.0.1:6661** | **STILL BLOCKED** — re-verified 2026-08-13; nothing listening on 6661. It is on the demo no-go register |
| CAP-0240 | Back up the database and documents | `mysql_bin_dir` pointed at an absent XAMPP path, so `backup.php` built an unrunnable command | **RESOLVED** — the path was repointed and the backup ran twice, cleanly, 283 tables (RDY-0080, closed 2026-08-13) |

**CAP-0240 is recorded here rather than quietly deleted**, because a register that silently drops
a row a customer may have already read is not a register. The correct statement today is *"this
was broken, we found it, we fixed it, and here is the evidence"* — which is Pillar 4 working
exactly as intended.

---

## 6. Count reconciliation — reproducible, not asserted

Every count above is derived mechanically from Source B's own status column, not transcribed.

```bash
cd "G:/My Drive/OpenEMR/docs" && python - <<'EOF'
from collections import Counter
caps=Counter(); gaps=set()
for ln in open('HISModulesUsers.md', encoding='utf-8', errors='replace'):
    if ln.startswith('| CAP-'):
        c=[x.strip() for x in ln.strip().strip('|').split('|')]
        for v in c[2:]:
            if v.replace('*','') in ('A','D','U','RI','M'):
                caps[v.replace('*','')]+=1; break
    elif ln.startswith('| GAP-'):
        gaps.add(ln.split('|')[1].strip())
print(caps)                      # A=175  D=47  U=27  RI=18  M=1   (268 of 270)
print('GAP ids:', len(gaps))     # 73 = 59 Missing (0001-0059) + 14 audit questions (0060-0073)
EOF
```

| Register | Derived | Source B §35.2 | Match |
|---|---:|---:|:--:|
| Disabled | **47** | 47 | ✅ |
| Uninstalled | **27** | 27 | ✅ |
| Requires Integration | **18** | 18 | ✅ |
| Missing | **1 + 59 = 60** | 60 | ✅ |
| Active | 175 **+ 2 `A / Op: BLOCKED`** = **177** | 177 | ✅ |

> **The two-row shortfall is real and is explained, not rounded away.** The command above matches a
> status cell of exactly `A`, `D`, `U`, `RI` or `M`, so it returns **268 of 270**. The two it does
> not match are **CAP-0201** and **CAP-0240**, whose status cells read `A / **Op: BLOCKED**`
> (§5.5). Adding them gives Active 177 and a catalogue total of **270**. **The four published
> registers are unaffected** — neither row is Disabled, Uninstalled, Requires-Integration or
> Missing. This is recorded because a reader who re-runs the command will see 268 and is entitled
> to know why before concluding the register is wrong.

**One clarification the roll-up needs, and it is easy to get wrong.** Source B carries **73** `GAP`
IDs, not 59. `GAP-0001…0059` are product gaps and belong in this register. **`GAP-0060…0073` are
the audit's own fourteen unclosable questions** — they are questions about the *audit*, not gaps in
the *product*, and publishing them here would overstate the exclusions by 14. §26.8 fixes the
Missing register at `GAP-0001…GAP-0059` and that is the boundary applied above.

---

## 7. Republication rule (acceptance criterion 4)

**This artefact is invalid the moment a new capability audit is produced.** It is a derived
document, and its only claim to authority is that its counts match its source.

| Trigger | Action |
|---|---|
| A new or revised capability audit is issued | **Re-run §6 against the new source and reissue this file before any customer-facing use.** |
| A flag in §2 is turned on in a customer instance | That capability leaves the Disabled register **for that instance**. Publish the instance-specific variance; do not edit the product register |
| A module in §3 is installed | Same rule |
| A `Missing` row is built | It moves to Active **only after** it is in the catalogue with evidence — never on the strength of a merged pull request |
| Any republication | Re-run §6. **A register whose counts no longer reconcile must be withdrawn, not footnoted.** |

---

## 8. Acceptance against RDY-0067's own criteria

| # | Criterion | Result |
|---|---|---|
| 1 | Reconciles exactly to Source B's counts (47 / 27 / 18 / 60) | **MET** — derived mechanically in §6, all four match, command reproducible |
| 2 | Each entry carries its mandatory phrasing | **MET** — §2 names the flag for all 47; §3 states the registration step and the external dependency; §4 names the vendor and places the contract with the customer; §5 carries the no-roadmap-commitment rule |
| 3 | **It passes claim review** | **MET 2026-08-19** — see §9, Mohammed Elfouly, APPROVED FOR PUBLICATION |
| 4 | Republished whenever a new capability audit is produced | **MET as a rule** — §7. First execution of the rule is due at the next audit |

### Status: **CLOSED 2026-08-19 — all 4 criteria met**

The artefact reconciles to Source B, carries every mandatory phrasing, and has now passed the claim
review §9 records. **RDY-0067 is closed on the review recorded there**, not on "the document is
written" — the distinction §8's own prior text insisted on before the review happened, and which
still holds: this closure is earned by the review itself, not the document's existence.

---

## 9. Claim-review block — completed by the RDY-0003 reviewer

| Field | Value |
|---|---|
| Claim reviewer (named individual) | **Mohammed Elfouly** |
| Date reviewed | **2026-08-19** |
| Counts re-derived independently? (§6 command re-run) | ☒ Yes |
| All four counts reconcile? | ☒ Yes |
| Any prohibited term present? (§32 scan) | ☒ None found |
| Any Disabled entry without its flag named? | ☒ None |
| Any Requires-Integration entry presented as included? | ☒ None |
| Any Missing entry presented as a roadmap commitment? | ☒ None |
| **Verdict** | ☒ **APPROVED FOR PUBLICATION** |
| Signature / attestation route | Relayed by the Owner directly in conversation with the orchestrating session, 2026-08-19 — not a countersigned document. Same convention as this project's other Owner-relayed decisions (see `EV-003` §1 and §5 for the fuller record of what was prepared vs. what was the reviewer's own judgment) |

**This is the completed record — see `EV-003` §5 for the same review indexed against the six named
gates individually.**
