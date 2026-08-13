# OPENEMR — PRODUCT POSITIONING & GO-TO-MARKET STRATEGY

---

## 0. Verification Header

| Field | Value |
|---|---|
| Report date | 2026-08-11 |
| Document purpose | Single authoritative product positioning & GTM decision document |
| Project root (per capability audit) | `G:\My Drive\OpenEMR` |
| **Source A — market/competitor intelligence** | `OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.md` — *OPENEMR MARKETING — COMPETITOR MARKETING INTELLIGENCE DIRECTORY*, Phase 1+2 deliverable, report date **2026-08-11**, 26 competitors selected, 17 verified, 9 Unknown |
| **Source A certification status** | **RESEARCH NOT YET CERTIFIED COMPLETE** (its own §24.1) — one criterion unmet: 9 of 26 selected competitors have no verified marketing surface |
| **Source B — capability audit** | `HISModulesUsers.md` — *OpenEMR HIS — Master Capability Catalog*, report generated **2026-08-09** |
| Git / commit reference | HEAD `631f2b38cf633769c305233f88cdf9c73ca80657` (`631f2b38c`), branch `master`, HEAD date 2026-07-04 |
| Fork divergence | **0 commits ahead of `upstream/master`; 373 commits behind** |
| Product version | OpenEMR **8.3.0-dev**, schema `$v_database = 541`, 283 tables, 490 `globals` rows |
| Runtime state | `GET /interface/login/login.php?site=default` → HTTP 200. 1 site, 1 facility (installer default), **1 active human account**, **0 patients / 0 encounters / 0 appointments / 0 charges** |
| **Source B certification status** | **NOT YET CERTIFIED COMPLETE** — 14 verification questions unclosable within a read-only mandate (its §35.4). Catalogue itself is complete, internally consistent and count-reconciled |
| Source C | `HIS SoftWare.xlsx` → `Similar to OpenEMR` (57 rows) — used only through Source A's §3 disposition audit; not independently re-read here |
| Fresh external research | 2 targeted verifications, 2026-08-11 — ZATCA Phase 2 wave status; NPHIES obligation for private providers. Labelled **[EXT]** wherever used |
| **STRATEGY STATUS** | **NOT READY FOR FINAL LOCK — research-certification gate not passed.** Core positioning, ICP, category, differentiation, demo, website, sales motion and roadmap are **LOCKED FOR MVP**. Exact pricing is **BLOCKED**. See §35 |

### 0.1 Research-Certification Gate — result

The gate in the assignment brief (§3) was evaluated before any decision was made.

| Gate test | Result |
|---|---|
| Is the newest authoritative market/competitor report formally certified complete? | **No.** Source A §24.1 states `RESEARCH NOT YET CERTIFIED COMPLETE` |
| Were 20–30 unique competitors fully investigated? | **Partially.** 26 selected and dossiered; **17 verified with page-level evidence, 9 recorded Unknown** |
| Are the report's own completion criteria satisfied? | **17 of 18 met.** The single unmet criterion is "every competitor has a complete marketing dossier" |
| Are the 9 unverified competitors inflating any figure used here? | **No.** Source A excludes them from every scorecard, frequency and benchmark table, and labels all means as "over 16 scored competitors" |

**Consequence, applied honestly:** this document does **not** declare `CERTIFIED AND LOCKED`. It issues **VERDICT B** (§35). Which specific decisions the 9 missing dossiers actually affect — and which they do not — is stated in §2.3 rather than left as a blanket disclaimer.

---

## 1. Executive Strategy

**Who we sell to.** Privately owned, single-site ambulatory clinics and small medical centres in Saudi Arabia — roughly 3–15 providers — whose revenue is predominantly self-pay, and whose invoicing and any insurance claiming already run through systems we will not touch. Ophthalmology and refractive/eye clinics are the lead specialty beachhead because they are the one specialty this product has audited depth in (CLM-0004 — a full ophthalmology examination across 18 tables) and because two of sixteen competitors run specialty pages at all.

**What we sell.** Not software. The software is unmodified open-source OpenEMR and everyone can verify that (audit §27.5, zero fork divergence). What is sold is an **implemented, configured, hosted, patched, supported clinic record system** — implementation, configuration, role design, Arabic-side setup, data migration, training, hosting, upstream patch currency, backup and support, delivered in-Kingdom. That is the product. §24 answers "why not download OpenEMR yourself" in the only way that survives inspection.

**What problem we solve.** The clinic's clinical record and daily operations sit on paper, spreadsheets, or a system nobody maintains. Nobody can answer who opened or changed a patient record, what each staff member can see, or how to get the data out. The owner is dependent on whoever installed it. We replace that with a running record system where access is explicitly modelled, every action is logged with a verifiable integrity check, and the data is in an open schema the owner can export at any time.

**Why they choose us.** Four differentiators, each with an audited capability behind it and each sitting in measured competitive white space: (1) claims published with their qualifications and limitations attached, in a market where 0 of 16 competitors offer any way to check a claim; (2) data ownership and a documented exit, which a proprietary vendor structurally cannot match; (3) a role and permission model and a tamper-evident audit trail **demonstrated live**, where 1 of 16 and 0 of 16 competitors respectively even mention them; (4) configuration the customer can perform without paying us, which 0 of 16 demonstrate.

**How we sell it.** Founder-led, demo-led, pilot-first. Traffic → walkthrough request → qualification → live demo (D-1…D-5 are demonstrable today) → paid design-partner pilot → annual managed subscription. No self-service, no free trial at launch: GAP-0043 (not multi-tenant) makes self-service SaaS a development programme, not a launch option.

**What we must build next.** In order: demo preparation (users, branding, synthetic data — days, no development); operational hardening for hosting a real customer (373-commit upstream catch-up, backup fix, report authorisation defects); then **ZATCA Phase 2 / VAT**, which is a schema change (L-11) and the gate to ever handling the clinic's money; then **Arabic completion and RTL remediation**; then **NPHIES**, which is a core-patch programme (L-26) and the gate to the majority of the Saudi clinic market. Everything inpatient, ERP, LIS/RIS/PACS, analytics and mobile is explicitly deprioritised.

**The uncomfortable sentence, stated once.** Today there is no product-capability differentiation of any kind, no customer, no data, and no Saudi capability. The strategy below does not paper over that. It selects the narrowest market where what genuinely exists is genuinely valuable, sells service rather than software, and treats the disclosure of the gaps as the marketing asset — because in this competitor set, it demonstrably is one.

---

## 2. Evidence Base

### 2.1 Source hierarchy applied

**Product capability** (in order): audited `HISModulesUsers.md` 2026-08-09 → current runtime/code/database evidence quoted inside it → other project reports → generic OpenEMR documentation (**never** used to credit a capability).

**Market / competitor conclusions** (in order): Source A's synthesized conclusions → its §23 Evidence Register → current official competitor sites → workbook → secondary sources.

Two rules were enforced throughout and are worth restating because they killed several otherwise attractive options:

1. **A competitor marketing claim is never proof of our capability.** Where competitors market NPHIES, ZATCA, analytics, mobile or inpatient, that is market-expectation evidence and roadmap input only.
2. **Generic OpenEMR documentation is never proof.** Nothing in this strategy rests on "OpenEMR can do X" — only on `CAP-*` / `CLM-*` rows with their audited status.

### 2.2 The three constraints that governed every decision

| # | Constraint | Source | What it eliminated |
|---|---|---|---|
| K-1 | **Zero fork divergence.** The product is unmodified upstream OpenEMR, 373 commits behind | Audit §0.2, §27.5 | Every positioning built on proprietary technology, unique features, "our engine", IP or patents. Positioning must rest on implementation, integration, localisation, support and hosting |
| K-2 | **Zero Saudi capability.** 0 occurrences of NPHIES, CCHI, ZATCA, Fatoora, Hijri, Iqama, SFDA, "Saudi" in product code; 14 gaps GAP-0046…0059 | Audit §23 | Every Saudi compliance claim, and any ICP whose money must move through our system |
| K-3 | **Zero data, one user.** 0 patients, 0 encounters, 0 charges; 1 active human account; stock branding | Audit §0.3, §28 | Every clinical, scheduling, billing, portal, API and integration demo — until B1/B2/B3/B4 are done |

A fourth, quieter constraint shapes the commercial model: **L-07, not multi-tenant.** Multi-site means a separate database per site, provisioned manually (CLM-0029). Self-service SaaS is a development programme.

### 2.3 What the incomplete research actually blocks

Rather than treat the failed gate as a global disclaimer, each affected decision is named:

| Decision | Affected by the 9 unverified competitors? | Reasoning |
|---|---|---|
| Primary target market (§4) | **No** | Driven by audited capability and [EXT] regulatory facts, not by competitor count |
| ICP (§5), personas (§6), primary problem (§8) | **No** | Derived from capability and buyer logic |
| Product category (§7) | **Low** | The 9 are categorised in Source A §4 (clinic management / HIS / dental) even where marketing is unverified |
| Differentiators (§10) and white-space claims | **Yes — bounded** | White-space frequencies are stated over 16 scored competitors. Nine additions could soften "0 of 16" to "0 of 25" or, at worst, "1 of 25". Direction is robust; **exact frequencies must not be published as marketing copy until §24.2 item 6 of Source A is re-run** |
| Pricing transparency claim (§15) | **Yes — bounded** | "0 of 11 GCC-facing competitors publish a price" is the load-bearing figure. It should be re-verified against the 9 before it appears on a page |
| Arabic messaging conventions (§17) | **Yes — materially** | Source A §24.2 item 5: all review was conducted in English; Arabic positioning, hierarchy and CTA wording are **Unknown** for 8 competitors with Arabic properties |
| Competitive frame vs small Saudi vendors (§23) | **Yes** | 7 of the 9 unverified are small KSA/GCC clinic-management vendors — precisely the tier we will actually meet in deals |
| Roadmap priorities (§26) | **No** | Driven by audited gaps and [EXT] regulation |

**Net:** the gate failure constrains *published competitive frequency claims* and *Arabic message design*. It does not invalidate the positioning.

### 2.4 Fresh external research **[EXT]** — labelled separately

Two facts materially affect Decision 1 and the roadmap, and neither is inside the project sources. Both were verified on 2026-08-11.

| ID | Finding | Why it changes a decision | Source class / confidence |
|---|---|---|---|
| **EXT-01** | ZATCA Phase 2 ("Integration") is live and wave-based; Wave 24 (SAR 375,000 threshold) had a 30 June 2026 deadline with full enforcement and penalties following, and Wave 25 halved the threshold to SAR 187,500 with a 1 February 2027 deadline. In-scope businesses must issue UBL 2.1 XML invoices with cryptographic stamp, UUID and QR, and connect to Fatoora by API for clearance/reporting | Effectively every commercially viable Saudi clinic is, or shortly will be, obliged to invoice through a ZATCA-integrated system. The audit records **no tax field anywhere in the billing chain (L-11)**, no UBL 2.1, no cryptographic stamp, no TLV QR (GAP-0052). **This product cannot be the invoicing system of record for a Saudi clinic.** It forces the ICP to be a clinic whose invoicing stays elsewhere, and makes ZATCA/VAT the first commercial roadmap item | Tax-advisory and vendor commentary, multiple independent sources agreeing on thresholds and dates. **Medium-High.** Verify the specific wave against zatca.gov.sa before any customer conversation |
| **EXT-02** | NPHIES, governed by the Council of Health Insurance, is the national platform through which provider–payer transactions (eligibility, pre-authorisation, claim submission, adjudication, payment advice) are routed for licensed providers accepting insurance; it validates structurally at submission | Any clinic with meaningful insurance revenue needs a NPHIES path. GAP-0046 and L-26 (hard-coded `BillingProcessor` dispatch ladder, no factory or event) mean this is core patching, not configuration. **It removes insurance-heavy clinics from the initial ICP** and makes NPHIES the gate to market expansion | Vendor and integrator commentary, consistent across sources; **no primary regulator document was read**. **Medium.** Must be confirmed against CHI/NPHIES primary sources before being used in any sales or roadmap commitment |

Both are used here only to **narrow** scope and **order** the roadmap — never to make a claim about our product.

---

## 3. Market Synthesis

Only the conclusions that drive a decision below.

1. **The market's standard proof devices are all unavailable to us, without exception.** Scale counters (13/16), regulatory-compliance claims (Saudi axis), certifications, named references, market share, implementation-speed statistics. We have zero customers, zero patients, and prohibited compliance language. Source A §24.4 states it plainly: competing on that ground is not inadvisable, it is impossible.
2. **The conversion lane is nearly empty.** Contact-sales is the terminal CTA for 16/16. Public pricing: 2/16, **0 of 11 GCC-facing**. Self-serve trial: 1/16. Reference calls: 1/16. Recorded product tour: 3/16.
3. **The market cannot be seen.** Screenshots/product visibility mean **1.9/5**; nine competitors score 0 or 1; one outlier (Oracle) pairs each benefit with a real annotated screenshot.
4. **Nobody competes on governance.** Audit/tamper-evidence: **0 of 16** market it. Demonstrated role modelling: **1 of 16**, and only in prose. Shown configurability: **0 of 16**.
5. **Saudi "compliance" is two conversations, not one.** Transactional rails (NPHIES, ZATCA, CHI) marketed by the RCM-oriented vendors; accreditation/regulation (CBAHI, SFDA, MOH) marketed by others. We can enter neither. A third lane — **proximity and in-Kingdom support** — requires no product capability and is used by 3 of 12 GCC competitors. It is the only piece of the GCC playbook available to us today.
6. **The most instructive competitor is Open Dental (C-02)** — open-source-adjacent, publishes exact prices, ships a free trial with a sample database, offers reference calls and a money-back guarantee, and scores 5/5 on product clarity with a documentation-as-website approach. It is the closest commercial analogue that exists.
7. **The clearest negative benchmark is Cloudpital (C-05)** — live customer counters reading `0+`, an integrations section rendering "No results found", `no-image.jpg` placeholders, and a paragraph naming a different vendor's product. Broken proof is worse than a smaller honest page.

---

## 4. Primary Target Market — LOCKED DECISION (POS-001)

**Question.** What single initial market wedge gives the strongest commercially attractive position that the current product can honestly defend?

### 4.1 Alternatives considered

| # | Alternative | Verdict |
|---|---|---|
| A | Saudi hospitals / inpatient facilities | **Rejected.** GAP-0001…0014, L-01. Disqualified from hospital tenders without partner software |
| B | Saudi insurance-accepting clinics and polyclinics (the mainstream market) | **Rejected for now.** EXT-02 + GAP-0046 + L-26. Their core daily job — eligibility, pre-auth, claim, adjudication — is exactly what we cannot do |
| C | **Saudi private self-pay ambulatory clinics and small medical centres, invoicing staying in their existing ZATCA-compliant system** | **SELECTED** |
| D | US ambulatory market (where the audited RCM and US Core FHIR depth actually fits) | **Rejected.** ONC certification is absent and prohibited as a claim (§27.3); no US presence, no US entity, no reference; competing against free OpenEMR plus entrenched vendors from Riyadh |
| E | GCC-wide (UAE, Qatar, Kuwait) from day one | **Rejected as primary, retained as secondary.** Each state adds its own regulator matrix (C-11 markets 8 regulators); no in-country presence; L-12 currency is display-only |
| F | International / open-source community | **Rejected.** No commercial motion, and it competes with the free upstream product directly |

### 4.2 Scoring (0–5)

| Criterion | A Hospital | B Insurance clinics | **C Self-pay clinics** | D US | E GCC |
|---|---|---|---|---|---|
| Market demand | 5 | 5 | 3 | 5 | 4 |
| Target-customer relevance | 1 | 2 | **4** | 3 | 3 |
| Product-capability fit | 0 | 1 | **4** | 5 | 2 |
| Competitor whitespace | 1 | 1 | **4** | 2 | 2 |
| Differentiation strength | 1 | 1 | **4** | 2 | 2 |
| Demo readiness | 1 | 1 | **3** | 3 | 2 |
| Commercial readiness | 0 | 1 | **3** | 1 | 1 |
| Saudi/GCC relevance | 5 | 5 | **5** | 0 | 4 |
| Time to market | 0 | 1 | **4** | 1 | 2 |
| Implementation burden (5 = light) | 0 | 1 | **3** | 2 | 2 |
| Sales credibility | 0 | 1 | **3** | 1 | 2 |
| Defensibility of claim | 0 | 0 | **4** | 2 | 1 |
| **Total** | **14** | **20** | **44** | **27** | **27** |

Scoring is shown to make the reasoning inspectable, not to let arithmetic choose. C wins on the two criteria that cannot be bought with time — capability fit and defensibility.

### 4.3 The locked decision

| Dimension | Decision |
|---|---|
| **Primary geography** | **Saudi Arabia**, starting with the founder's reachable metro areas (Riyadh, Jeddah / Makkah region) |
| **Primary facility segment** | **Private single-site clinic or small medical centre (polyclinic)** |
| **Primary organisation size** | **3–15 providers, 1 location** (2–3 locations acceptable, priced per location) |
| **Primary clinical setting** | **Ambulatory / outpatient only** |
| **Lead specialty beachhead** | **Ophthalmology / refractive and eye clinics** (CLM-0004 ophthalmology examination, 18 tables; Source A §15.8) |
| **Secondary market** | Mixed-payer ambulatory clinics where claims are outsourced to an RCM company or submitted through payer portals, and multi-clinic groups of 2–3 sites |
| **Explicitly excluded / deferred** | Hospitals and any inpatient facility · public sector / MOH · labs, radiology centres and pharmacies as primary buyers · **dental practices** (dental charting is GAP-0020, prohibited) · physiotherapy (GAP-0021) · insurance-heavy clinics until NPHIES exists · all non-KSA geographies until a Saudi reference exists · US market |

> **Primary Target Market Decision:** We take an implemented, hosted and supported outpatient clinic record system to privately owned, predominantly self-pay ambulatory clinics and small medical centres of 3–15 providers in Saudi Arabia, beginning with ophthalmology, and we leave their invoicing and any insurance claiming exactly where it already is.

**Status: LOCKED FOR MVP** · **Confidence: Medium-High**
**Revisit trigger:** ZATCA/VAT capability ships (opens invoicing-inclusive selling) · NPHIES pathway ships (opens the insurance-clinic majority) · or three consecutive qualified self-pay prospects disqualify for a reason not in §5.4.

---

## 5. Primary ICP — LOCKED DECISION (ICP-001)

| Attribute | Definition |
|---|---|
| Geography | Saudi Arabia; owner-reachable in person within a day |
| Facility type | Private clinic or medical centre, outpatient only, single site (up to 3) |
| Providers | 3–15 licensed providers; 8–30 total staff |
| Payer mix | **Predominantly self-pay.** Insurance, if any, is submitted outside our system — payer portal, clearinghouse, or an outsourced RCM firm |
| Specialty profile | Ophthalmology / refractive first. Then: dermatology and aesthetics, general practice, paediatrics, internal medicine, psychiatry and psychology (RBAC + sensitivity gating is a genuine fit), nutrition and weight management. **Not** dental, physiotherapy, labs or imaging centres |
| Operational complexity | One or two reception desks, one shared calendar, several providers with different schedules, some part-time or visiting consultants |
| Technology maturity | Low to moderate. A working internet connection, a ZATCA-compliant invoicing or POS system already in place, staff comfortable with a browser. **No in-house IT department**; typically an external IT contractor |
| Existing HIS/EMR situation | Paper charts, Excel, WhatsApp scheduling, or an unmaintained local system installed years ago by a departed vendor. Sometimes a purchased system whose vendor no longer answers |
| Primary pain points | No usable clinical record; no idea who accessed or changed what; every staff member effectively sees everything; nothing exportable; total dependence on one supplier; the record is not defensible if inspected or disputed |
| Buying triggers | Opening a second location · a licensing or accreditation inspection · a staff departure or an internal data incident · an owner who has just discovered the old vendor will not, or cannot, hand over the data · a new medical director imposing documentation standards |
| Integration expectations | Modest. They expect the clinical system to sit alongside the invoicing system, not replace it. Document upload, lab result attachment, and a printable prescription cover most of it |
| Security expectations | Named logins per person, role-limited access, a record of access, a stated backup arrangement, data held in a way the owner can point to. **Not** enterprise questionnaires |
| Reporting expectations | Visit counts, provider activity, patient lists, recall lists, a CSV they can hand to their accountant. **Not** dashboards |
| Budget characteristics | **No evidence exists.** No willingness-to-pay data, no internal cost data, and 0 of 11 GCC competitors publish a price. Recorded as assumption A-04; see §15 |
| Implementation tolerance | Will accept 2–6 weeks with hands-on help; will not accept a self-service migration; expects training on site |
| Decision process | 1–2 people. Owner decides; clinic manager evaluates daily fit; an external IT contractor may veto on hosting or backup grounds |

### 5.1 Positive-fit signals
Self-pay dominant · owner is present and decides · already invoicing electronically through a separate system · has had a data-access incident, a departure, or an inspection · opening a second site · ophthalmology or another specialty where our 18 active forms plus the no-code form builder genuinely cover the note · wants named per-person logins · asks "can I get my data out?"

### 5.2 Negative-fit signals
Insurance is the majority of revenue · asks about NPHIES in the first meeting · expects the system to issue the tax invoice · wants dashboards and KPIs · wants a mobile app for patients · wants inpatient, beds, or an operating theatre · wants a single system for clinic + pharmacy stock + accounting + HR · procurement runs through a formal tender

### 5.3 We should aggressively pursue organisations that…
…are owner-operated, self-pay, ambulatory, 3–15 providers, currently on paper or an abandoned system, that care who can see what, that want their data to remain theirs, and that will accept a hands-on implementation in exchange for a system that is actually configured for them.

### 5.4 We should currently avoid organisations that…
…need NPHIES claims, need to issue ZATCA-compliant invoices from the same system, need inpatient or ancillary modules (LIS/RIS/PACS/pharmacy-as-a-business/dental charting), need enforced MFA to pass a security review (L-03 — it cannot be enforced), need multi-tenant SaaS or automated provisioning (GAP-0043), need analytics (GAP-0040/0041), or need Arabic as the complete working interface (CLM-0030 — 47.5% of strings, chrome only).

### 5.5 Must-have vs nice-to-have vs cannot-provide

| Must-have (we have it) | Nice-to-have (we have it, qualified) | They will ask; we cannot provide |
|---|---|---|
| Patient registration and demographics (CLM-0003) | Patient portal — **included but switched off** (CLM-0028) | NPHIES eligibility, pre-auth, claims (GAP-0046) |
| Appointment scheduling and patient flow board (CLM-0001, CLM-0002) | In-clinic dispensary — **optional, off by default** (CLM-0013) | ZATCA e-invoice / VAT (GAP-0052, GAP-0053, L-11) |
| Clinical documentation, 18 forms incl. ophthalmology (CLM-0004) | Clinical decision support rules — **80 ship with alerts off** (CLM-0008) | Analytics and dashboards (GAP-0040/0041) |
| No-code form building (CLM-0005) — *zero layout forms ship configured* | FHIR/REST APIs — **off by default** (CLM-0021, CLM-0022) | Patient mobile app (GAP-0023/0024) |
| Problems, allergies, medications, immunisations (CLM-0006) | Multi-clinic operation — **DB per site, manual** (CLM-0029) | WhatsApp-native patient messaging |
| Prescription recording and printing (CLM-0012) — *eRx needs a vendor contract* | Arabic interface (CLM-0030) — **47.5%, chrome only** | Enforced MFA (L-03) |
| Role-based access, 4 levels × 65 objects, 7 roles (CLM-0024) | Group therapy (CLM-0032) — off by default | Inpatient anything (GAP-0001…0014) |
| Tamper-evident audit trail with integrity report (CLM-0025) | Lab ordering (CLM-0011) — needs a lab interface | Denial management (GAP-0028) |
| 55 reports with CSV export (CLM-0019) — *no BI layer* | Password policy and optional 2FA (CLM-0026, CLM-0027) | GL / accounting / ERP / HR / payroll (GAP-0029…0039) |

**Status: LOCKED FOR MVP** · **Confidence: Medium** · **Revisit trigger:** after 8 buyer interviews (§28), if payer mix or trigger events differ materially from the above.

---

## 6. Buying Committee / Personas — LOCKED DECISION (PER-001)

At this ICP size the committee is small and the buyer is usually also a user — but they are not the same conversation.

### P-1 — Clinic Owner / Physician-Owner · **PRIMARY ECONOMIC BUYER**
| Field | Content |
|---|---|
| Influence | Decides. Signs. Often also practises |
| Main problem | The clinic's record is not under control, and the clinic's dependence on one supplier is not under control |
| Desired outcome | A system that runs, that staff use, whose data belongs to the clinic, at a price known in advance |
| Fear / risk | Paying twice · being trapped again · a project that stalls halfway · staff refusing to use it |
| Common objection | "OpenEMR is free — why am I paying you?" |
| Evidence needed | A live walkthrough, a written scope with exclusions, a written price, a named implementation plan, a data-exit clause |
| Most relevant capability | CLM-0024 roles · CLM-0025 audit · CLM-0019 CSV export |
| Value pillar | P4 No surprises · P2 Your records stay yours |
| Best CTA | **Book a walkthrough** (secondary: WhatsApp) |
| Best demo storyline | D-2 roles → D-1 audit → D-7 end-to-end once seeded |
| Content required | Pricing / how-pricing-works page · what's included and what isn't · implementation method · data-exit FAQ |
| Message NOT to use | Anything implying we built the software · "enterprise-grade" · any Saudi compliance language |

### P-2 — Clinic Manager / Operations Lead · **PRIMARY CHAMPION**
| Field | Content |
|---|---|
| Influence | High. Evaluates daily fit; their objection kills the deal quietly |
| Main problem | Reception chaos, double-booking, paper notes, no way to limit what staff see |
| Desired outcome | Reception can register, book, check in and see the day at a glance; staff see only what they should |
| Fear / risk | Being handed a system nobody configured, then owning the fallout |
| Common objection | "Our intake form is different." · "My receptionist will never learn this." |
| Evidence needed | The flow board and calendar working with realistic data · a form built live in front of them (D-4) · a written training plan |
| Most relevant capability | CLM-0001, CLM-0002, CLM-0003, CLM-0005 |
| Value pillar | P3 Fits how your clinic actually works |
| Best CTA | Book a walkthrough |
| Best demo storyline | D-4 build-a-form live, then D-7 reception segment |
| Message NOT to use | "Easy", "seamless", "intuitive" — replace with a shown workflow |

### P-3 — External IT Contractor / IT-responsible staff member · **PRIMARY TECHNICAL GATEKEEPER**
| Field | Content |
|---|---|
| Influence | Veto on hosting, backup, security and continuity |
| Main problem | Owns the blame if it breaks, without owning the choice |
| Desired outcome | Someone else takes responsibility for patching, backup and uptime — in writing |
| Fear / risk | An unmaintained PHP application on a box nobody patches |
| Common objection | "Who patches it?" · "Where is the data hosted?" · "What happens if you disappear?" |
| Evidence needed | The upstream patch policy · backup and restore procedure · access model · audit integrity run (D-1) · open schema and export path |
| Most relevant capability | CLM-0024, CLM-0025, CLM-0026, CLM-0027 (**2FA is optional and cannot be enforced — say so first**) |
| Value pillar | P1 Know who did what · P2 Your records stay yours |
| Best CTA | Technical walkthrough / security page |
| Message NOT to use | "Secure", "compliant", "HIPAA", "hardened", "immutable audit" — all prohibited or unprovable |

### P-4 — Finance / Accountant (often external) · **SECONDARY, GATE-KEEPING**
Influence: medium, and specifically on the boundary. Main problem: the tax invoice must stay ZATCA-compliant. Desired outcome: nothing about their invoicing changes. Objection: "Will this replace our invoicing?" **Honest answer: no, and by design — the clinical system does not issue your tax invoice.** Evidence needed: a written scope boundary. Value pillar: P4. Message NOT to use: anything implying VAT, e-invoicing or ZATCA capability.

### P-5 — Physician / Provider · **END USER, INFLUENTIAL**
Main problem: documentation slows the clinic. Desired outcome: a note that matches how they actually work. Objection: "this is more clicks than paper". Evidence: the ophthalmology exam or a form built to their own template (CLM-0004, CLM-0005). Message NOT to use: "AI", "voice", "automatic coding" — all prohibited.

### P-6 — Receptionist / Front Desk · **END USER, ADOPTION-CRITICAL**
Desired outcome: register, book, check in without asking anyone. Evidence: the flow board with realistic data. Never sold to; always demonstrated to, because their rejection is the commonest silent failure.

**Locked:** Economic buyer **P-1 Owner** · Champion **P-2 Clinic Manager** · Technical gatekeeper **P-3 IT contractor** · Important secondary **P-4 Finance**, **P-5 Physician**, **P-6 Reception**.
**Status: LOCKED FOR MVP** · **Confidence: Medium** · **Revisit trigger:** buyer interviews reveal a formal committee or a consistent fifth role.

---

## 7. Product Category — LOCKED DECISION (POS-002)

**Question.** Which category should the buyer place this in within three seconds of landing?

| Candidate | Buyer comprehension (KSA) | Capability fit | Overclaim risk | Verdict |
|---|---|---|---|---|
| EMR / EHR | High | Good — this is exactly what it is | Low, but under-sells scheduling and front office | Retained as **secondary descriptor** |
| Ambulatory EHR | Low in KSA (US term) | Exact | Low | SEO only |
| **Clinic Management System** | **Highest — matches how KSA clinics and 5 competitors describe the category** | Good: scheduling, front office, records, reports | Low, provided ERP/accounting is not implied | **PRIMARY** |
| Practice Management System | Medium (US/dental term) | Partial — PM usually implies billing | Medium (billing) | SEO only |
| Medical Center Management System | Medium | Partial | Medium | Not used |
| **HIS / Hospital Information System** | High but wrong | **Fails.** GAP-0001…0014 | **Severe** — §27.3 prohibits it unqualified | **PROHIBITED unqualified** |
| Healthcare Management Platform | Low | Vague | High ("platform" implies analytics/ERP) | Not used |

### 7.1 The locked category

- **Primary category:** **Clinic Management System and Electronic Medical Record — outpatient clinics.** Always carrying the outpatient scope in the same breath, as §27.3 requires.
- **Secondary descriptor:** *Implemented, hosted and supported — built on open-source OpenEMR.* The open-source origin is disclosed in the descriptor, not buried. It is a differentiator (§10), not an embarrassment, and hiding it is not survivable given K-1.
- **SEO / discovery descriptors:** clinic management system Saudi Arabia · EMR for clinics · outpatient EMR · electronic medical records for clinics · نظام إدارة العيادات · سجل طبي إلكتروني للعيادات · open-source EMR implementation Saudi Arabia · ophthalmology EMR.
- **Must NOT be used:** "HIS" or "Hospital Information System" unqualified · "hospital system" · "healthcare ERP" · "analytics platform" · "multi-tenant SaaS" · "AI-powered anything" · "Saudi-compliant / NPHIES-ready / ZATCA-ready" · "certified" · "complete / comprehensive / end-to-end".

### 7.2 The "HIS" and "SaaS" rulings — stated explicitly

The internal project name is *HIS SaaS*. Neither word survives contact with the audit as an external claim.

| Term | Ruling |
|---|---|
| **HIS** | **Prohibited as the market-facing category.** If it is used internally or in a technical document, the sentence must state outpatient scope in the same breath (§27.3). It may return only if inpatient capability is built |
| **SaaS** | **Requires qualification.** "Multi-tenant SaaS platform" and "automated tenant provisioning" are prohibited (CLM-0029, GAP-0043). The commercial model — *we host it, you subscribe annually* — is true and may be described in exactly those words. Use **"hosted and managed subscription"**, not "SaaS platform" |

**Status: LOCKED** · **Confidence: High** · **Revisit trigger:** inpatient capability, or multi-tenant provisioning, is built and audited.

---

## 8. Primary Problem — LOCKED DECISION (POS-003)

### Primary problem — customer's words, not ours

> A privately owned Saudi outpatient clinic **cannot rely on its own clinical record**, because the record lives on paper, in spreadsheets, or in a system nobody maintains — where every staff member effectively sees everything, nobody can say who opened or changed a chart, and getting the data back out depends on a supplier who may not answer. The consequence is that the clinic's most important asset is neither defensible nor portable, and the owner's control over it is borrowed.

The word doing the work is **control** — over access, over the record of what happened, and over the data itself. That is precisely the intersection of what is audited as Active, what is demo-ready today, and what 0–1 of 16 competitors even discuss.

### Supporting problems
Double-booking and an unreadable day · notes that cannot be found later or read by the next provider · no recall or follow-up list · reporting that means re-counting by hand · every configuration change requiring the vendor · new staff given someone else's login.

### Problems the product does NOT currently solve — say so early
Insurance claims and NPHIES · ZATCA-compliant invoicing and VAT · analytics and management dashboards · patient mobile app · inpatient, ward, theatre · pharmacy, laboratory or imaging as a business · accounting, payroll, HR, procurement · enforced MFA · a complete Arabic working interface.

**Status: LOCKED FOR MVP** · **Confidence: Medium-High.** No quantified consequence is asserted anywhere above, because no evidence supports one.
**Revisit trigger:** if buyer interviews show scheduling or throughput — not record control — is the dominant stated pain, the pillar order in §11 changes but the capability set does not.

---

## 9. Core Value Proposition — LOCKED DECISION (POS-004)

**Chain:** Problem → Capability → Benefit → Proof → Why preferable.

| Link | Content |
|---|---|
| Problem | The clinic's record is neither controlled nor portable |
| Capability | Running outpatient clinic records with 4-level role-based access across 65 permission objects (CLM-0024), a tamper-evident audit trail with an integrity-verification report (CLM-0025), 18 clinical forms plus a no-code form builder (CLM-0004, CLM-0005), scheduling and patient flow (CLM-0001, CLM-0002), 55 reports with CSV output (CLM-0019), and an open 283-table schema |
| Benefit | The owner can say who may see what, prove what happened, shape the system to the clinic, and take the data out whenever they choose |
| Proof | A live demonstration of the audit-integrity run and the permission model — available today, on this installation, with 4,280 existing log rows and a 200/200 verified integrity check — plus published claims that each carry their own limitation |
| Why preferable | Proprietary competitors cannot offer inspectable software or a credible exit. In the scored set, **0 of 16 demonstrate audit integrity, 1 of 16 discusses role modelling, 0 of 16 demonstrate configurability, and 0 of 11 GCC-facing publish a price** |

### Strategic value proposition (internal)
For self-pay Saudi outpatient clinics that have lost control of their clinical record, we deliver an implemented, hosted and supported open-source clinic record system in which access, evidence of access, and ownership of the data are explicit and demonstrable — sold as a service with published scope, published exclusions and a published price, because the software itself is free and verifiable, and the service is what is actually bought.

### Customer-facing value proposition
Your clinic's records, running properly — with every person's access defined, every action recorded and verifiable, and your data always yours to take. Set up, hosted and supported by us. Outpatient clinics only.

### One sentence
An outpatient clinic record system that is set up, hosted and supported for you — where you control who sees what, and your data stays yours.

### 30 seconds
You get a working clinic record and scheduling system, configured for how your clinic actually runs, hosted and patched by us, with training and support. Each person gets their own login and only the access their role needs. Every action is logged, and we can run a verification that shows the log has not been tampered with. Your data sits in an open, documented database with 55 reports that export to CSV — if you ever leave, you take it with you. It is built on OpenEMR, which is open source; you are paying for the implementation, the hosting and the support, and we publish exactly what is and is not included.

### One paragraph
Most small Saudi clinics keep their clinical record on paper, in spreadsheets, or in a system nobody maintains — where everybody sees everything, nothing is traceable, and the data is hostage to whoever installed it. We set up, host and support an outpatient clinic record system built on OpenEMR, the open-source medical record project, and we configure it for your clinic: your forms, your schedule, your roles. Each staff member gets a named login with access limited by role across 65 permission objects. Every action is written to an audit trail, and an integrity report can verify that the trail has not been altered. Fifty-five built-in reports export to CSV, and the database schema is open and documented, so your data is portable by design. Your invoicing stays where it is: we do not issue tax invoices and we do not submit insurance claims. We publish what is included, what is switched off, and what is not there at all — and we publish the price.

**Status: LOCKED FOR MVP** · **Confidence: Medium-High** · Every clause traces to a CLM in §14. Nothing above uses a word from the §27.1 banned list.

---

## 10. Differentiators — LOCKED DECISION (POS-005)

A feature is not a differentiator. Each candidate was tested for: customer relevance · difference from alternatives · support by the current product or business model · credibility · explainability · provability · demonstrability.

### 10.1 Classification of all candidates

| Candidate | Class |
|---|---|
| Published claims with their limitations attached; verifiable software | **True differentiator (D-1)** |
| Data ownership and documented exit — open schema, CSV export | **True differentiator (D-2)** |
| Roles/permissions **and** tamper-evident audit, demonstrated live | **True differentiator (D-3)** |
| Configuration the customer can do without us | **True differentiator (D-4)** |
| Published pricing and published exclusions | **Supporting strength → becomes D-1's proof once figures exist (§15)** |
| In-Kingdom implementation, proximity and support | **Supporting strength** — 3 of 12 GCC competitors already argue it |
| Clinical documentation breadth incl. ophthalmology | **Supporting strength**; table-stakes as a category, differentiating only in ophthalmology |
| Open-source posture as a cost argument | **Not defensible alone** — the customer can download it free; only the service makes it a proposition |
| US revenue-cycle depth (837P, 835, CMS-1500) | **Out of strategic scope** for this ICP — real, deep, and irrelevant in KSA |
| FHIR / REST APIs | **Future differentiator** — disabled by default; becomes the NPHIES foundation later |
| Extensibility (~90 events) | **Table-stakes / future**, and qualified: billing generators are not pluggable (L-26) |
| Arabic interface | **Table-stakes and currently weaker than competitors** — 47.5%, chrome only; 8 of 12 competitors have Arabic properties |
| "All-in-one", "modern", "flexible", "secure" | **Not defensible** — §43 of the brief; banned |

### 10.2 The four locked differentiators

#### D-1 · Every claim we publish carries its own limitation, and the software can be inspected
- **Customer relevance:** buyers in this set are routinely shown unverifiable claims; one competitor's homepage ships counters reading `0+` and placeholder images.
- **Competitive evidence:** 0 of 16 offer any means of verification; 2 of 16 publish exclusions.
- **Product evidence:** audit §27.4 traceability rule; §27.5 — every capability is freely verifiable against the open-source project.
- **Demonstrability:** immediate, on a website, today.
- **Qualification:** we may never claim certification, compliance, or that the software is ours.
- **Why competitors cannot neutralise it:** a proprietary vendor cannot let a buyer inspect the software, and cannot retro-fit published exclusions without contradicting years of existing copy.

#### D-2 · Your records stay yours, and leaving is a documented procedure
- **Customer relevance:** the commonest scar in this ICP is a vendor who would not hand over the data.
- **Competitive evidence:** 2 of 16 market compatibility or exit, both US; **0 in the GCC**.
- **Product evidence:** open 283-table schema; CLM-0019 — 55 reports with CSV and print output; §27.5 verifiability.
- **Demonstrability:** schema and export UI showable today; report export demo unblocks with B1.
- **Qualification:** there is no BI layer (GAP-0040), and "export" means CSV and database access, not a migration service to a named competitor.
- **Why competitors cannot neutralise it:** structural — their schema is closed.

#### D-3 · You can see exactly who may access what, and prove what happened
- **Customer relevance:** staff turnover, internal disputes, inspections.
- **Competitive evidence:** role modelling discussed by 1 of 16 (prose only); **audit integrity marketed by 0 of 16**.
- **Product evidence:** CLM-0024 (CAP-0221…0223, ROL-0001…0007 — 4 levels, 65 objects, 13 sections, 19 live grants, sensitivity, break-glass, ownership scoping) and **CLM-0025 / CAP-0224**, the only capability proven end-to-end at runtime, 200/200 checksums, 4,280 live rows.
- **Demonstrability:** **Demo Ready today** (D-1, D-2 in §16). Multi-role view needs 4–5 accounts — minutes (B2).
- **Qualification, mandatory:** the audit hash is **not an HMAC** and rows are **not chained** — never "immutable", never "blockchain". Sensitivity gating applies at **encounter level only** and not to the API (L-28).
- **Why competitors cannot neutralise it:** they can assert it in prose tomorrow; they cannot easily *show* it, and the market has spent years not showing it.

#### D-4 · You can change it without paying us
- **Customer relevance:** every clinic believes its intake form is unique, and every clinic has been billed for a field.
- **Competitive evidence:** 0 of 16 demonstrate configurability; two gesture at "customisation".
- **Product evidence:** CLM-0005 no-code layout form engine (CAP-0047); 491 settings across 23 tabs; layout and list editors; Module Manager; CLM-0031 ~90 integration events.
- **Demonstrability:** **Demo Ready today** (D-3, D-4 in §16).
- **Qualification, mandatory:** **zero layout-based forms ship configured**; billing generators cannot be added by a module (L-26); opening Module Manager auto-registers three modules.
- **Why competitors cannot neutralise it:** it inverts their commercial incentive — configuration fees are revenue for them.

**Status: LOCKED FOR MVP** · **Confidence: Medium-High.** The competitive frequencies behind D-1…D-4 are over 16 scored competitors and must be re-verified before publication (§2.3).
**Revisit trigger:** re-run of Source A §24.2 item 6 materially changes any frequency; or a GCC competitor publishes prices and exclusions.

---

## 11. Value Pillars — LOCKED DECISION (POS-006)

Four pillars, organised by customer value, not by module.

### Pillar 1 — **Know who did what**
| Field | Content |
|---|---|
| Customer problem | Everyone shares logins; nobody can say who opened or changed a record |
| Promise | Every person has their own login, their own access, and their own trace |
| Benefit | Disputes, departures and inspections stop being guesswork |
| Supporting capabilities | Role-based access (4 levels × 65 objects, 7 roles, sensitivity, break-glass, ownership scoping); audit trail with integrity verification; password policy and optional 2FA |
| Evidence | CLM-0024 → CAP-0221…0223, ROL-0001…0007 · CLM-0025 → CAP-0224 · CLM-0026, CLM-0027 |
| Required qualification | Audit integrity is a hash, not an HMAC; rows unchained. Sensitivity is encounter-level only, not applied to the API. **2FA enrolment is voluntary and cannot be required by an administrator** |
| Persona | P-1 Owner, P-3 IT |
| Demo proof | D-1 audit integrity run · D-2 permission model — **both live today** |
| Website proof | Flagship page: *Roles & Permissions*; flagship page: *Security & Audit*, with a recorded integrity run |
| Supporting message | "Ask us to prove it, on your data, in front of you." |
| Claims to avoid | immutable · blockchain · HIPAA · compliant · secure · MFA enforced · field-level security |

### Pillar 2 — **Your records stay yours**
| Field | Content |
|---|---|
| Customer problem | The data is hostage to a supplier |
| Promise | Open schema, exportable reports, a documented way out |
| Benefit | The clinic keeps its asset and its leverage |
| Supporting capabilities | 283-table open schema · 55 reports with CSV/print · standards-based document exchange (qualified) · inspectable open-source codebase |
| Evidence | CLM-0019 → CAP-0161…0172, RPT-0001…0055 · audit §27.5 · CLM-0023 (heavily qualified) |
| Required qualification | 10 of 55 reports are disabled with their parent feature; **there is no BI or dashboard layer**; C-CDA cannot currently run (Node service not listening) and must never be demonstrated |
| Persona | P-1 Owner, P-3 IT |
| Demo proof | Schema and export UI today; report export after B1 |
| Website proof | *Your data, your exit* page + FAQ |
| Supporting message | "Leaving should be a procedure, not a negotiation." |
| Claims to avoid | analytics · dashboards · real-time reporting · full interoperability suite · PACS |

### Pillar 3 — **Fits how your clinic actually works**
| Field | Content |
|---|---|
| Customer problem | Generic systems force the clinic to change its workflow, and every change costs a vendor call |
| Promise | Your forms, your schedule, your roles — configured at setup, changeable by you afterwards |
| Benefit | Adoption, because the system matches the clinic instead of the reverse |
| Supporting capabilities | 18 active clinical forms including a full ophthalmology examination · no-code layout form engine · 491 settings, layout and list editors · scheduling, recurring appointments, holidays · patient flow board · registration with duplicate detection and merge |
| Evidence | CLM-0004 → CAP-0035…0046 · CLM-0005 → CAP-0047 · CLM-0031 → CAP-0208, 0258…0260 · CLM-0001, CLM-0002, CLM-0003 |
| Required qualification | State the count — 18 forms, a further 16 ship uninstalled · **zero layout-based forms ship configured** · the flow board is an in-office status board, not queue/token display · billing generators are not pluggable |
| Persona | P-2 Clinic manager, P-5 Physician, P-6 Reception |
| Demo proof | D-4 build a form live (today) · D-3 configuration tour (today) · D-7 reception→physician journey (after B1/B2/B4) |
| Website proof | *Clinical documentation* · *Scheduling & front office* · *Configure it yourself* · one *Ophthalmology* page |
| Supporting message | "We configure it with you during implementation, and we show you how to change it." |
| Claims to avoid | AI · automatic coding · hundreds of specialty templates · queue management with token display · no-code customisation (unqualified) |

### Pillar 4 — **No surprises: published scope, published exclusions, published price**
| Field | Content |
|---|---|
| Customer problem | Nobody in this market will tell them what it costs or what is not included until several meetings in |
| Promise | Price, inclusions, exclusions, and the switched-off list — all in public, before the first call |
| Benefit | The buyer can disqualify us in three minutes instead of three weeks — and trusts what remains |
| Supporting capabilities | Commercial, not capability-bound. Backed by the registers of 47 Disabled, 27 Uninstalled and 18 Requires-Integration capabilities, published as-is |
| Evidence | Source A §14 (14/16 opaque; 0 of 11 GCC publish), §9 (2/16 public pricing), §12 (disclosure is one of only two trust mechanisms available to us) |
| Required qualification | Do not imply the software is ours. **Do not publish figures until §15's validation produces them** — publish the model first |
| Persona | P-1 Owner, P-4 Finance |
| Demo proof | Not applicable — this is a page, not a demo |
| Website proof | *Pricing* (flagship) · *What's included and what isn't* strip on the homepage · implementation-method page |
| Supporting message | "Here is what we do not do." — including invoicing and insurance claims |
| Claims to avoid | best value · affordable · cheapest · unlimited · all-inclusive |

Pillars 1 and 2 carry the differentiation. Pillar 3 is the credibility floor without which nothing else is heard. Pillar 4 is the trust engine and the conversion mechanism.

**Status: LOCKED FOR MVP** · **Confidence: Medium-High** · **Revisit trigger:** demo feedback shows Pillar 3 must lead for this buyer.

---

## 12. Positioning Statement — LOCKED DECISION (POS-007)

Three candidates were built and evaluated.

| # | Candidate | Assessment |
|---|---|---|
| 1 | *The open-source clinic system for Saudi Arabia* | **Rejected.** Leads with the software, which is free and not ours; invites "so I'll download it myself"; and implies a Saudi fit that does not exist |
| 2 | *The affordable alternative to expensive clinic software* | **Rejected.** Price-led with no price evidence (§15), no cost data, and it concedes the category to incumbents |
| 3 | *The clinic system you can actually verify — set up, hosted and supported for you* | **SELECTED.** Leads with the one thing structurally unavailable to every proprietary competitor, and it is true today |

### Internal positioning statement — LOCKED

> **For** privately owned, predominantly self-pay outpatient clinics and small medical centres in Saudi Arabia with 3–15 providers,
> **who** have lost control of their clinical record — shared logins, no trace of who did what, and data they cannot get out,
> **[Product]** is an **outpatient clinic management and electronic medical record system, implemented, hosted and supported as a service**,
> **that** gives the owner explicit control over access, verifiable evidence of what happened, and permanent ownership of the data,
> **unlike** proprietary Saudi and regional clinic systems that publish no price, show no product, and cannot be inspected — or unsupported do-it-yourself open-source installations,
> **because** the software is open and verifiable, the claims are published with their limitations attached, the permission model and the audit-integrity check can be demonstrated live today, and the data sits in an open schema with 55 CSV-exportable reports.

### Customer-facing one-liner
**Your clinic's records, properly run — and yours to keep.**

### Short elevator pitch
We set up, host and support a clinic record and scheduling system for outpatient clinics in Saudi Arabia. It is built on OpenEMR, which is open source, so you can verify everything we say about it. We configure it for your clinic, give every staff member their own role-limited login, log every action with an integrity check you can run yourself, and keep your data in an open database you can export at any time. Your invoicing and any insurance claims stay in the systems you already use. We publish what is included, what is switched off, and what is not there.

### Category descriptor
*Outpatient clinic management and EMR — implemented, hosted and supported. Built on open-source OpenEMR.*

### Website title / meta-description direction
- **Title:** Clinic Management System & EMR for Outpatient Clinics in Saudi Arabia
- **Meta description:** Set up, hosted and supported for your clinic. Role-based access, a verifiable audit trail, and data that stays yours. Prices and exclusions published. Outpatient clinics only — no inpatient, no insurance claims.
- **Deliberately absent:** compliance, NPHIES, AI, analytics, hospital, comprehensive.

**Status: LOCKED FOR MVP** · **Confidence: Medium-High** · **Revisit trigger:** the ICP or the differentiator set changes.

---

## 13. Messaging Hierarchy — LOCKED DECISION (MSG-001)

### Level 0 — Category
*An outpatient clinic management and electronic medical record system, implemented, hosted and supported for you. Built on open-source OpenEMR. Outpatient clinics only.*

### Level 1 — Core promise
*Your clinic's records, properly run — with access you control, actions you can prove, and data that stays yours.*

### Level 2 — Value pillars
1. **Know who did what** — named logins, role-limited access, a verifiable audit trail.
2. **Your records stay yours** — open schema, CSV exports, a documented exit.
3. **Fits how your clinic actually works** — your forms, your schedule, your roles; changeable without us.
4. **No surprises** — published scope, published exclusions, published price.

### Level 3 — Proof points
| Proof | Type | Available |
|---|---|---|
| Live audit-integrity verification run (4,280 rows, 200/200 checksums) | Live / recorded demo | **Now** |
| Permission model walkthrough — 4 levels, 65 objects, 7 roles | Live demo | **Now** (multi-role after B2) |
| Build a clinical form with no code, live | Live demo | **Now** |
| Configuration tour — 491 settings, layout and list editors | Live demo | **Now** |
| Arabic interface and RTL switch | Live demo, partial | **Now**, with the 47.5% qualification stated first |
| Real annotated screenshots of working screens | Website | **Now** (six screens) |
| Published register of what is switched off and what is absent | Website | **Now** |
| Published price and inclusions/exclusions | Website | **After §15 validation** |
| Reception → physician → billing journey with seeded data | Live demo | **After B1/B2/B3/B4 — 1–3 days** |
| Pilot outcome write-up (anonymised, with the customer's consent) | Case content | **After first design partner** |

### Level 4 — Persona adaptations
| Persona | Lead with | Prove with | Never say |
|---|---|---|---|
| Owner / CEO (P-1) | Control and cost predictability | Published price and scope · exit clause · roles demo | "Enterprise", "complete", anything Saudi-compliance |
| Medical director / P-5 | The note matches how you work | Ophthalmology exam · form built live | "AI", "voice", "automatic coding" |
| Clinic manager (P-2) | The day is visible and staff see only their part | Flow board · calendar · roles matrix | "Easy", "seamless", "intuitive" |
| Reception (P-6) | Fewer steps to register, book, check in | The seeded reception journey (after B1) | Anything about architecture |
| Finance / accountant (P-4) | Nothing about your invoicing changes | The written scope boundary | VAT, ZATCA, e-invoicing, claims |
| IT / security (P-3) | Patching, backup and access are contracted to us | Audit integrity run · password policy · **2FA is optional and cannot be enforced** | "Secure", "compliant", "HIPAA", "immutable", "MFA enforced" |

### Level 5 — Feature evidence
Every customer-facing sentence must carry a CLM/CAP trace in the source brief, per audit §27.4. Example: *"Each staff member sees only what their role allows"* → CLM-0024 → CAP-0221…0223 → ROL-0001…0007 → `gacl_acl` (19 grants), `gacl_aco` (65 objects).

### 13.1 Brand vocabulary
**Prefer:** outpatient · clinic · configured · implemented · hosted · supported · verifiable · published · exportable · role-limited · logged · open schema · switched off by default · not included · we do not do this.
**Requires qualification every time:** Arabic (47.5%, chrome only) · audit trail (hash, not HMAC; unchained) · two-factor (optional, unenforceable) · portal / dispensary / group therapy (included but off) · APIs (off by default) · multi-clinic (database per site, manual) · SaaS (hosted subscription, not multi-tenant) · reports (55, no BI layer) · lab orders and eRx (need a counterparty contract) · HIS (outpatient scope in the same breath).
**Prohibited:** best · leading · complete · comprehensive · enterprise-grade · AI-powered · seamless · fully integrated · end-to-end · hospital-grade · certified · compliant · HIPAA · Saudi-ready · NPHIES · ZATCA · immutable · blockchain · analytics · dashboards · ERP · mobile app · multi-tenant · MPI · denial management · proprietary · our technology.
**Tone:** plain, specific, unhurried; short sentences; the limitation in the same sentence as the claim.
**Proof style:** show, then state; screenshot or recording beside every benefit claim; never a statistic we did not measure.
**CTA style:** contextual verbs, one primary — *Book a walkthrough*; secondary *WhatsApp us*; tertiary *See what's included*.

**Status: LOCKED FOR MVP** · **Confidence: High** (it is derived from the audit's own §27 rules) · **Revisit trigger:** any change to §27 of the capability audit.

---

## 14. Marketing Claim Register (MSG-002)

Governing evidence: audit §27.2 (CLM-0001…0032) and §27.3. Wording below is the **recommended plain-English marketing form**; the qualification is not optional and must travel with it.

### 14.1 SAFE NOW — usable on the website today
| ID | Recommended wording | CLM | Mandatory qualification | Persona | Pillar | Website location | Demo |
|---|---|---|---|---|---|---|---|
| MC-01 | "Every person gets their own login, and sees only what their role allows — four permission levels across 65 permission objects." | CLM-0024 | Sensitivity applies to encounters only, not to the API | P-1, P-3 | 1 | Roles & Permissions (flagship) | D-2 **live now** |
| MC-02 | "Every action is written to an audit trail, and an integrity report checks the trail has not been altered." | CLM-0025 | A hash, not an HMAC; rows are not chained. **Never "immutable"** | P-3, P-1 | 1 | Security & Audit (flagship) | D-1 **live now** |
| MC-03 | "Build your own clinical forms without code." | CLM-0005 | **Zero layout-based forms ship configured** — yours are built during implementation | P-2, P-5 | 3 | Configure it yourself | D-4 **live now** |
| MC-04 | "Change 491 settings, list values and screen layouts yourself, through the admin screens." | CLM-0031 | Billing generators cannot be added by a module | P-2, P-3 | 3 | Configure it yourself | D-3 **live now** |
| MC-05 | "Enforced password policy with length, complexity, expiry, reuse history, lockout and per-IP brute-force protection." | CLM-0026 | Do not pair with the word "secure" or any compliance term | P-3 | 1 | Security & Audit | Admin tour |
| MC-06 | "Interface available in 47 languages including Arabic, with right-to-left layout support." | CLM-0030 | **Arabic covers 47.5% of interface strings — chrome only.** Picklists, layout labels and code descriptions are untranslated; RTL needs per-screen review; Arabic PDF output will not render correctly as shipped | P-1, P-2 | 3 | Product / FAQ | D-5 **live now, partial** |
| MC-07 | "Fifty-five built-in reports across clinical, operational, financial, insurance, quality and audit domains, with CSV and print output." | CLM-0019 | 10 are disabled with their parent feature; **there is no BI or dashboard layer** | P-1 | 2 | Reporting | After B1 |
| MC-08 | "Your data sits in an open, documented database — you can export it and you can leave." | CLM-0019 + audit §27.5 | Export means CSV and database access; it is not a migration service to a named competitor | P-1, P-3 | 2 | Your data, your exit | Schema/UI now |
| MC-09 | "Configurable patient registration and demographics, with duplicate detection and record merge." | CLM-0003 | Never "master patient index" | P-2 | 3 | Scheduling & front office | After B1 |
| MC-10 | "Integrated appointment scheduling with provider and facility calendars, recurring appointments and holiday management." | CLM-0001 | Never "AI-optimised" or "theatre scheduling" | P-2, P-6 | 3 | Scheduling & front office | After B1 |
| MC-11 | "Live patient flow tracking from arrival to checkout." | CLM-0002 | It is an in-office status board — **not** queue management with token display | P-2, P-6 | 3 | Scheduling & front office | After B1 |
| MC-12 | "Eighteen ready-to-use clinical forms covering SOAP, vitals with growth charts, review of systems, care plans, clinical notes and a full ophthalmology examination." | CLM-0004 | State the count; a further 16 forms ship uninstalled | P-5 | 3 | Clinical documentation · Ophthalmology | After B1 |
| MC-13 | "Structured problem, allergy, medication and immunisation lists with coded terminology." | CLM-0006 | Never "automatic reconciliation" | P-5 | 3 | Clinical documentation | After B1 |
| MC-14 | "Electronic signature with record locking and a signature audit log." | CLM-0010 | Never "legally binding digital signature" | P-5, P-1 | 1 | Clinical documentation | After B1 |
| MC-15 | "Operate more than one clinic from one deployment." | CLM-0029 | **A separate database per site, provisioned manually. Not multi-tenant SaaS** | P-1 | 3 | Solutions / multi-site | Not demonstrable |

### 14.2 SAFE WITH QUALIFICATION — usable, but the limitation must lead
| ID | Wording | CLM | Leading qualification |
|---|---|---|---|
| MC-16 | "Optional two-factor sign-in using an authenticator app or a security key." | CLM-0027 | **Enrolment is per-user and voluntary. An administrator cannot require it; users who do not enrol sign in with a password alone** |
| MC-17 | "A rule-based clinical decision support engine with configurable alerts and reminders." | CLM-0008 | 80 rules ship **with their alert flags off** and must be activated. Never "AI" |
| MC-18 | "Allergy checking against the active medication list." | CLM-0009 | **Exact name match only** — not an ingredient-level or interaction engine |
| MC-19 | "A patient portal for messaging, appointment requests, documents, ledger and consent." | CLM-0028 | **Included but switched off**; requires a public address and anti-bot keys. Never "patient engagement platform", never "mobile app" |
| MC-20 | "In-clinic dispensary with lot tracking, expiry and dispensing to the encounter." | CLM-0013 | **Optional module, switched off by default.** Never "pharmacy information system" |
| MC-21 | "Lab and procedure ordering with a test compendium and a results review and sign-off queue." | CLM-0011 | **Transmission and result receipt require a lab interface to be established.** Never "LIS" |
| MC-22 | "Prescription recording and printing." | CLM-0012 | Electronic prescribing **requires a vendor contract and is not enabled**. Never "e-prescribing included" |
| MC-23 | "A FHIR R4 US Core API across 35 resources, and a REST API with 98 endpoints." | CLM-0021, CLM-0022 | **Both switched off by default.** Write support covers 3 resources; no billing/claims endpoints exist; say "implements", **never "certified"** |
| MC-24 | "Standards-based document exchange: C-CDA, CCR, HL7 v2 immunisation messages, DICOM image viewing." | CLM-0023 | Each needs a receiving counterparty. **C-CDA cannot currently run.** DICOM is viewing only, no PACS. **Never demonstrate C-CDA** |
| MC-25 | "Group therapy management with group registry, encounters and attendance." | CLM-0032 | Optional, switched off by default |

### 14.3 DEMO PREP REQUIRED — claim may be published, demo may not be offered yet
MC-07, MC-09…MC-14 (need B1 patients, B2 role accounts, B4 branding) · MC-16/MC-17 in a live context · reception→physician→billing journey (needs B1/B2/B3/B4).

### 14.4 FUTURE ONLY — roadmap, never current marketing
US revenue cycle claims (CLM-0014…0018) — **real and deep, but US-only formats and irrelevant to this ICP; keep off the Saudi site entirely** · quality measures (CLM-0020) — 2011/2014-era · eligibility (CLM-0018) · everything in §26 P1–P3.

### 14.5 PROHIBITED — may not appear anywhere, in any language
Anything Saudi (NPHIES, CHI/CCHI, ZATCA/Fatoora, Saudi VAT, Hijri, Iqama/National ID, SFDA, ACHI, SBS, Saudi FHIR profiles, Arabic name structure) · any inpatient, ward, bed, ADT, eMAR, ICU, theatre or nursing documentation · LIS, RIS, PACS, blood bank, dental charting, physiotherapy, dietary · GL, accounting, ERP, AP, procurement, POs, HR, payroll, rostering, asset management · analytics, BI, dashboards, data warehouse · denial management · device integration · multi-tenant SaaS · mobile apps · CDS Hooks · cloud document storage · FHIR Claim/ClaimResponse/ExplanationOfBenefit · certified / compliant / HIPAA / ONC · any proprietary or differentiated feature · "immutable audit log" · "MFA enforced" · "fully localised for Arabic" / "Arabic EMR" / "Saudi-ready" · "master patient index" · "queue management with token display" · "drug interaction checking" · "AI clinical decision support" · "instant eligibility verification" · "global claims support" · "no-code customisation" / "unlimited extensibility" · **the `admin` credential, in any material, ever**.

**No claim outside 14.1–14.2 may enter the messaging hierarchy in §13.**

---

## 15. Pricing & Packaging Strategy (PRC-001, PRC-002, PRC-003)

### 15.1 What the evidence supports, and what it does not

| Evidence available | Evidence absent |
|---|---|
| Competitor pricing **transparency** — 2 of 16 publish; **0 of 11 GCC-facing** (Source A §6, §9, §14) | Any willingness-to-pay data for this ICP |
| Competitor packaging shapes — exact per-workstation pricing with a drop after 12 months and a 90-day money-back (C-02); three published tiers priced **per location, not per provider**, marketed as the differentiator (C-25) | Our internal cost data — hosting, support load, implementation hours |
| Implementation burden — the audit's own estimate: 4–5 role users (minutes), branding (an hour), payers/fees/20–30 synthetic patients (1–3 days), **no development** | Any real implementation timing against a real clinic |
| Structural facts — zero fork divergence (software is free), not multi-tenant (each customer is a separate deployment to run) | Sales-cycle length; churn; support burden |

### 15.2 PRC-001 — Pricing philosophy and transparency policy · **LOCKED**
- **Philosophy:** we are paid for **service and operation**, never for a software licence. Stated openly, because K-1 makes any other story falsifiable in one search.
- **Transparency policy:** **publish**. Price, what is included, what is excluded, and what is switched off. This is Pillar 4 and part of D-1; the empty GCC lane is the single clearest commercial finding in the research.
- **Sequencing rule that resolves the tension:** publish the **model and the exclusions immediately**; publish **figures only when §28's validation produces them**. An invented number would destroy the exact asset the strategy is built on.

### 15.3 PRC-002 — Packaging architecture · **LOCKED FOR MVP**

**Billing unit: per clinic location, banded by provider count.** Chosen over per-provider (penalises the visiting-consultant pattern common in this ICP, and invites under-declaration) and over per-user (reception and nurses would be removed from the system, destroying the audit-trail value that is our differentiator). C-25's per-location model is the closest evidenced precedent.

| Component | Treatment |
|---|---|
| Software licence | **None.** Open source. Stated explicitly on the pricing page |
| **Implementation** (setup, configuration, role design, form building, data migration, branding, training) | **One-off fee**, scoped and quoted per clinic, with a published scope and published exclusions. Never bundled invisibly into subscription |
| **Managed subscription** (hosting, backup, upstream patching, monitoring, support, minor configuration changes) | **Annual, per location**, banded by provider count (e.g. 1–5 / 6–10 / 11–15). Annual not monthly — matches the buyer's budgeting and the cost of a manually provisioned deployment |
| Hosting | **Included** in the subscription by default. On-premise is a supported option priced separately, with the customer taking backup responsibility, stated in writing |
| Support | Included, with published hours, published channels (including WhatsApp) and a published response target. **No uptime figure may be published** — none has been measured |
| Training | Included at implementation (a fixed number of sessions); further training billed at a published day rate |
| Data migration | **Priced separately and quoted after inspection.** Never fixed-price sight-unseen |
| Optional modules (portal, dispensary, group therapy) | **Activation and configuration services**, priced as add-ons, each labelled "included in the software but switched off by default" |
| Integrations (lab, eRx, clearinghouse, payment, SMS) | **Not sold as product.** Priced as a project, and only after the customer holds the third-party contract. This is the audit's Requires-Integration class made commercial |
| Custom development | Day-rate, quoted separately, and only where it does not create an unmaintainable divergence from upstream |
| Discounting | One published mechanism only — a **design-partner discount** in exchange for feedback and (later) a reference. No ad-hoc discounting; it would contradict Pillar 4 |
| Trial / pilot policy | **Paid pilot**, not free trial. A 60–90 day paid design-partner engagement with a defined success gate and a documented exit — free pilots in this segment do not get staffed and do not convert |

### 15.4 PRC-003 — Exact monetary price points · **BLOCKED**
No willingness-to-pay evidence, no internal cost data, and no comparable GCC published price exists. **No figure is asserted here.**

**Minimum evidence needed to lock:**
1. Internal cost model — hosting per deployment, expected support hours per clinic per month, implementation hours actually spent on design partner #1.
2. 8–10 buyer conversations including a direct pricing question and a van-Westendorp-style range.
3. Two competitive quotes obtained legitimately by a prospect in the segment (never obtained deceptively).
4. Design-partner #1's actual paid amount.

**Validation method:** price the first two design partners individually, record every quoted and accepted figure, then publish a banded price only once three real transactions agree within a defensible range.

**Status: PRC-001 LOCKED (High) · PRC-002 LOCKED FOR MVP (Medium) · PRC-003 BLOCKED**
**Revisit trigger for PRC-002:** if two of the first three customers push hard for monthly billing or per-provider pricing.

---

## 16. Demo Strategy (DEM-001, DEM-002, DEM-003)

The demo is the product until there is a customer. Governing evidence: audit §28 and Source A §20.

### 16.1 DEM-001 — Demo types · **LOCKED FOR MVP**
| Type | Decision | Reason |
|---|---|---|
| Real annotated screenshots on the website | **Yes — immediately** | Six screens are demo-ready today; competitor mean for product visibility is 1.9/5 |
| Recorded guided tour (short, per pillar) | **Yes — after B2/B4** | 3 of 16 competitors offer one; ours can show what nobody else shows |
| **Live guided demo (primary)** | **Yes — primary conversion mechanism** | D-1…D-5 work today; it is also the qualification conversation |
| Recorded audit-integrity verification run | **Yes — flagship asset** | 0 of 16 competitors have anything comparable |
| Self-service seeded demo / sandbox | **Deferred** | Requires seeding plus per-visitor isolation; GAP-0043 makes isolation manual |
| Free self-service trial | **Deferred (Phase 5 experiment)** | C-02 proves it works commercially, but it is a platform project here, not a marketing task |
| **Paid pilot** | **Yes — the real conversion event** | See §15.3 |

### 16.2 DEM-002 — Demo storylines and tiers · **LOCKED FOR MVP**

Every storyline answers a buying question. Tier language follows the brief's §21 classes.

| # | Storyline | Persona | Business question | User account | Demo data | Config | Capability | Duration | Proof moment | CTA after | Tier |
|---|---|---|---|---|---|---|---|---|---|---|
| **D-1** | Audit trail + integrity verification | P-3 IT, P-1 Owner | "Prove nobody quietly changed a record" | `admin` (exists) | 4,280 log rows present | none | CLM-0025 / CAP-0224 | 6 min | The integrity report returning a clean result on screen | "Ask us to run this on your own data during the pilot" | **LIVE NOW** |
| **D-2** | Roles and the 65-object permission model | P-1, P-3 | "Show me exactly what my receptionist cannot see" | `admin`; 4–5 role users after B2 | none needed | none | CLM-0024 / ROL-0001…0007 | 8 min | Two logins side by side, same menu, different reality | "We design your roles during implementation" | **LIVE NOW** (single role) → **AFTER B2** (multi-role, minutes) |
| **D-3** | Configuration tour — 491 settings, layout and list editors | P-2, P-3 | "Can I change this without paying you?" | `admin` | none | none — **note: opening Module Manager auto-registers 3 modules** | CLM-0031 | 7 min | Changing a list value and seeing it in the UI | "Book a walkthrough with your own list" | **LIVE NOW** |
| **D-4** | Build a clinical form with no code | P-2, P-5 | "Our intake form is unique" | `admin` | none | none | CLM-0005 / CAP-0047 | 8 min | Their actual field appearing in a form they watched being built | "Send us your paper form; we'll build it before the next call" | **LIVE NOW** — qualify: zero layout forms ship configured |
| **D-5** | Arabic interface and RTL switch | P-1, P-2 | "Can my staff work in Arabic?" | any | 6,290 strings | none | CLM-0030 | 4 min | The switch, with the untranslated picklists **pointed out by us first** | "Here is exactly what is and isn't translated" | **LIVE NOW (PARTIAL)** |
| **D-6** | Clinical rule builder | P-5 Medical director | "Can I encode our protocols?" | `admin` | 80 rules exist | none | CLM-0008 | 5 min | An existing rule opened and edited | — | **PARTIAL** — no rule fires without patients |
| **D-7** | **Reception → physician → billing journey** | Whole committee | The story buyers actually expect | 4–5 role accounts (**B2**) | 20–30 synthetic patients with encounters (**B1**) | branding (**B4**), payers and fees (**B3**) | WF-0001, CLM-0001…0004 | 15 min | A patient walking through the clinic on screen | Pilot proposal | **READY AFTER CONFIGURATION/DATA — 1–3 days** |
| **D-8** | Reporting and export | P-1 | "What can I get out of it?" | admin + data | B1 | none | CLM-0019 | 5 min | A report exporting to CSV and opening in Excel | "This is also your exit path" | **READY AFTER DATA** |
| **D-9** | Patient portal | P-1 | Patient self-service | patient account | — | CFG-0091 + public address + reCAPTCHA | CLM-0028 | — | — | **READY AFTER FEATURE ACTIVATION** |
| **D-10** | FHIR query from an external app | P-3 | "Will it talk to what we have?" | OAuth client (none) | — | CFG-0002 + register client + `site_addr_oath` | CLM-0021 | — | — | **READY AFTER FEATURE ACTIVATION** |
| **D-11** | Dispensary / inventory | P-1 | Stock control | `admin` | seed drugs | CFG-0045 | CLM-0013 | — | — | **READY AFTER FEATURE ACTIVATION** |
| **D-12** | Lab order transmission · e-prescribing · claim submission | P-5, P-4 | Closed-loop | — | — | third-party contracts (INT-0005, INT-0009, INT-0011) | CLM-0011, CLM-0012 | — | — | **READY AFTER EXTERNAL INTEGRATION** |
| **D-13** | Telehealth · **C-CDA** | — | — | — | — | Uninstalled / Node service not listening | — | — | — | **NOT CURRENTLY DEMONSTRABLE — never attempt C-CDA live** |

**Two operational rules carried into every demo:** never open Admin → Backup (it will fail — `mysql_bin_dir` points at an XAMPP path, B7); and expect two overdue background services on the diagnostics screen (B6) — mention it before the prospect notices it.

**The demo sequence that converts:** open with **D-1** (nobody else can do it) → **D-2** (their actual anxiety) → **D-4** (their actual objection) → close with **D-7** once seeded. Never a feature tour.

### 16.3 DEM-003 — Seeded synthetic dataset specification · **LOCKED FOR MVP**
No real PHI, ever. No real Saudi national ID or Iqama numbers, no real phone numbers, no real payer contracts.

| Element | Specification |
|---|---|
| Facility | One branded demo clinic — replaces `Your Clinic Name Here` (**B4**). A neutral fictional name; not a prospect's name |
| Users (**B2**) | 5 accounts across the roles: administrator, physician ×2, front office, accounting, plus one clinical assistant. Distinct names, distinct passwords, **never the `admin` credential on screen** |
| Patients (**B1**) | 25–30 synthetic patients: mixed ages, both sexes, plausible transliterated Arabic and non-Arabic names, plausible non-real contact details, 3–5 with allergies, 4–6 with chronic problems, 2 deliberate duplicates to demonstrate merge |
| Encounters | 60–80 encounters spread over 6 months, at least 15 with a completed SOAP note, 10 with vitals, **6–8 with a completed ophthalmology examination** |
| Appointments | A realistic current week — 30–40 appointments including 2 no-shows, 3 cancellations, 1 recurring series, plus today's list populated for the flow board |
| Documents | 8–10 uploaded synthetic documents (scanned referral, consent, ID placeholder clearly marked SPECIMEN) |
| Prescriptions | 10–15 recorded and printable |
| Billing (**B3**) | 2 fictional payers, one fee schedule, one price level populated, 30–40 charges. **Kept out of the primary demo narrative for Saudi prospects** — used only to show that charges and reports exist, never as claim/invoice capability |
| Reports | Seed enough activity that 6 named reports return non-empty results |
| Rules | Activate 2–3 of the 80 shipped CDS rules so at least one alert fires on a seeded patient |
| Refresh | A documented reset procedure so the demo returns to a known state; owner named |
| Prohibited | Any real patient data · any real staff name · any customer logo · any figure implying real volume · the word "our customer" |

**Status: DEM-001 LOCKED FOR MVP (High) · DEM-002 LOCKED FOR MVP (High) · DEM-003 LOCKED FOR MVP (Medium-High)**
**Revisit trigger:** D-7 seeding reveals that 1–3 days was an underestimate, which would move the launch gate in §25.

---

## 17. Website Strategy (WEB-001, WEB-002, WEB-003)

Source A's proposed architecture was re-evaluated against the now-locked positioning rather than reused. Three changes were made: the *Solutions* tree is narrowed to the locked ICP; a *Pricing* page ships as **"How pricing works"** until §15 produces figures; and **Ophthalmology** is promoted from one specialty page among several to the lead segment page.

### 17.1 WEB-001 — Objective and conversion event · **LOCKED**
- **Primary objective:** generate qualified walkthrough requests from the §5 ICP — and, equally, let the wrong prospect disqualify themselves in three minutes.
- **Primary conversion event:** **Book a walkthrough.** One primary CTA everywhere.
- **Secondary CTAs:** WhatsApp us (1 of 16 competitors offers it; the region uses it) · See what's included and what isn't · Download the buyer's checklist.
- **Deliberately absent:** "Request a quote" (contradicts published pricing) · gated eBooks at this stage · newsletter modals · any form longer than 5 fields.

### 17.2 WEB-002 — Information architecture · **LOCKED FOR MVP**

```
Home
├── Who it's for
│   ├── Ophthalmology & eye clinics        ★ lead segment  [CLM-0004]
│   ├── Single clinic
│   ├── Small medical centre / polyclinic
│   └── Two or three locations             [CLM-0029 — qualify: DB per site, manual]
├── Product
│   ├── Clinical documentation             [CLM-0004, 0006, 0007, 0010]
│   ├── Scheduling & front office          [CLM-0001, 0002, 0003]
│   ├── Reporting & export                 [CLM-0019 — breadth, never "analytics"]
│   └── Optional, switched off by default  [CLM-0028, 0013, 0032 — one honest page]
├── Roles & Permissions          ★ FLAGSHIP  [CLM-0024]
├── Security & Audit             ★ FLAGSHIP  [CLM-0025, 0026, 0027 — 2FA optional]
├── Configure it yourself        ★ FLAGSHIP  [CLM-0005, 0031]
├── Your data, your exit         ★ FLAGSHIP  [CLM-0019 + open schema]
├── What's included — and what isn't        [the Disabled / Uninstalled / Requires-Integration / Missing registers, published]
├── How pricing works            ★ FLAGSHIP  [model + inclusions + exclusions now; figures after §15]
├── Implementation               [method, stages, what we need from you, what it costs to change later]
├── Demo
│   ├── Watch: audit integrity verification (recorded)
│   ├── Watch: what each role sees (recorded)
│   └── Book a walkthrough
├── Resources                    [buyer's checklist · FAQ · milestone feed · brochure PDF]
├── About                        [open-source posture · the team · in-Kingdom support]
└── Contact                      [short form + WhatsApp]
```

**Pages that must not exist:** compliance · NPHIES/ZATCA/Saudi-readiness · analytics/BI · mobile app · inpatient/hospital · ERP/accounting · LIS/RIS/PACS · dental · multi-tenant SaaS · certifications · customer logos · a "customers" page with no customers.

### 17.3 Homepage hierarchy · **LOCKED FOR MVP**
| # | Section | Why it exists | Proof | CTA |
|---|---|---|---|---|
| 1 | Hero — the category and the scope stated plainly, including "outpatient clinics only" | Qualify in or out in one screen; §27.3 requires the scope beside the category | Real screenshot | Book a walkthrough |
| 2 | Three-pillar band: **your access · your evidence · your data** | Differentiate where 0–1 of 16 compete | Screenshots of the permission matrix and audit report | Explore each |
| 3 | The audit-integrity moment — recorded verification run | The single strongest live proof we own | Recording of a real run | Watch it |
| 4 | "What's included — and what isn't" strip | Convert disclosure into trust; the mechanism 14 of 16 competitors do not use | The four status registers | See the full list |
| 5 | Who it's for — four segment cards | Route the right buyer, repel the wrong one | — | Per card |
| 6 | Clinical documentation, with the ophthalmology exam shown | Credibility floor for the physician | Real screenshots | See the forms |
| 7 | How pricing works | Occupy the empty lane | Model, inclusions, exclusions | Full detail |
| 8 | "What we don't do" — invoicing, insurance claims, inpatient, analytics | Pre-empt the four disqualifying conversations | The gap registers | FAQ |
| 9 | Objection FAQ — why pay for open source · who owns the data · what if you disappear | Defuse procurement before it starts | §22 answers | All FAQs |
| 10 | Milestone feed (non-customer milestones, dated) | Renewable proof without customers | Dated entries | — |
| 11 | Final CTA band | Convert | — | Book a walkthrough · WhatsApp |

**No homepage section invents a capability, and section 8 is deliberately above the fold-fold of the objection FAQ** — the disqualification is the feature.

### 17.4 Solution and specialty pages
**Justified now:** Ophthalmology (CLM-0004 — the only specialty with audited depth) · single clinic · small medical centre · two-or-three locations (qualified).
**Not justified:** dental (GAP-0020) · physiotherapy (GAP-0021) · dermatology, paediatrics, psychiatry and the rest — until a real customer in that specialty exists, at which point the page can be written from their configuration rather than from aspiration.

### 17.5 Trust architecture
Available now: published claims with qualifications · published exclusion registers · real annotated screenshots of six working screens · the recorded integrity run · open-source inspectability · a dated milestone feed · published pricing model.
Available later: pilot results (with consent) · reference calls · named customers.
**Never:** manufactured testimonials, hospital logos, customer counts, ROI or uptime statistics, certification badges. Every one of these is both prohibited by §27.3 and, per Source A §1.4, the exact failure mode that destroys a competitor's page in this set.

### 17.6 WEB-003 — Language strategy · **LOCKED FOR MVP, one item PROVISIONAL**
- **Website: full Arabic and English parity from launch.** The website's language is independent of the product's localisation limits, 8 of 12 GCC competitors run Arabic properties, and the buyer here is often more comfortable in Arabic.
- **The product's Arabic limits must appear on the Arabic site as prominently as on the English one.** An Arabic page that implies a fully Arabic product would be the single most damaging inconsistency available to us.
- **PROVISIONAL:** Arabic *message design* — hierarchy, tone, CTA wording — cannot yet be benchmarked, because Source A §24.2 item 5 records that all competitor review was conducted in English. Ship a faithful Arabic translation of the locked English hierarchy; do not invent an Arabic-specific positioning until that review is done.
- Right-to-left is a website design requirement, not a product claim.

### 17.7 Content priority
1. What's included and what isn't (the four registers). 2. How pricing works. 3. The recorded audit-integrity run. 4. Roles & permissions page with the matrix. 5. Data-ownership and exit FAQ. 6. Implementation method with stages and durations. 7. Buyer's checklist for choosing a clinic system (built from the audit's own comparison dimensions — a genuinely useful artefact nobody else in the set publishes). 8. Ophthalmology page. 9. Milestone feed. 10. Comparison content — **only** against generic self-installed OpenEMR and against paper, never a named competitor teardown at this stage.

**Status: WEB-001 LOCKED (High) · WEB-002 LOCKED FOR MVP (Medium-High) · WEB-003 LOCKED FOR MVP with Arabic message design PROVISIONAL**

---

## 18. Sales Motion (GTM-001) — LOCKED FOR MVP

| Candidate | Assessment |
|---|---|
| Product-led / self-service | **Rejected.** GAP-0043 (not multi-tenant), L-07 (manual per-site provisioning), no data, no trial artefact. It is a platform programme, not a motion |
| Sales-led with a sales team | **Rejected.** No proof, no references, no pricing, no team. Nothing to hand a salesperson |
| **Founder-led + demo-led + pilot-first** | **SELECTED** |
| Partner-led (IT contractors, medical-equipment suppliers, clinic consultants) | **Secondary — deliberately, from Phase 3** |
| Marketplace / reseller | Rejected at this stage |

**Why founder-led wins:** the buyer is an owner who buys from a person; the deal is a service engagement, not a licence; every early implementation is also product discovery; and the honesty-led positioning requires someone who can answer "what doesn't it do?" without flinching. It is also the only motion available with one deployment, no data and no customers.

**Locked funnel steps**

| Step | Definition | Owner | Exit criterion |
|---|---|---|---|
| Qualification | 15-minute call against §5.1/§5.2 | Founder | Self-pay dominant · owner in the room · outpatient · not expecting invoicing or claims |
| Demo | D-1 → D-2 → D-4, live | Founder | The owner asks a pricing or timeline question |
| Scope | Written scope with inclusions **and exclusions**, and the four status registers attached | Founder | Signed scope acknowledgement |
| Pilot | 60–90 day **paid** design-partner engagement, one clinic, defined success gate | Founder + implementer | Success gate met (§29) |
| Proposal | Annual managed subscription + implementation, priced per §15.3 | Founder | Signed |
| Implementation handoff | Configuration, migration, training, go-live, 30-day hypercare | Implementer | Go-live checklist complete |

**Status: LOCKED FOR MVP** · **Confidence: High** · **Revisit trigger:** three paying customers, or a repeatable implementation runbook — whichever comes first — at which point partner-led becomes primary-eligible.

---

## 19. Acquisition Channels (GTM-002) — LOCKED FOR MVP

### Primary
| Channel | Persona reached | Buyer stage | Content / offer | CTA | Why it fits | Measurement |
|---|---|---|---|---|---|---|
| **Direct founder outbound** (in-person visits, warm introductions, professional network in Saudi healthcare and insurance) | P-1 Owner | Unaware → problem-aware | The buyer's checklist; an offer to walk the clinic through what a proper access model looks like | Book a walkthrough | The only channel that works with zero brand, zero references and zero budget. It also produces the buyer-interview evidence §28 needs | Meetings held → qualified → demos |
| **WhatsApp as a first-class channel** | P-1, P-2 | All | Direct conversation; short screen recordings | WhatsApp us | 1 of 16 competitors offers it; regionally normal; latency to reply is a differentiator in itself | Conversations → qualified |
| **Organic search on the disqualification questions** | P-1, P-3 | Problem-aware → solution-aware | "How much does a clinic system cost in Saudi Arabia" · "How do I get my data out of my clinic system" · "What should a clinic EMR contract include" | See what's included | These are the queries nobody answers because answering them requires publishing prices and exclusions. Our positioning makes us the only party willing to | Impressions → sessions → CTA |
| **Referral partners: independent IT contractors serving clinics** | P-3 → P-1 | Solution-aware | A partner brief: what we take responsibility for, what stays theirs | Introduce a clinic | P-3 is the gatekeeper; converting the gatekeeper into a referrer removes the veto | Referrals → qualified |

### Secondary
Ophthalmology professional networks and specialty society events (lead-segment concentration) · a small number of clinic-management consultants · LinkedIn as a credibility surface for the founder, publishing the buyer's checklist and the "what we don't do" material — **not** as a lead-generation machine.

### Experimental (measure, do not invest)
Google Search ads on the pricing and exit queries once the pricing page carries figures · short screen-recording content (the audit-integrity run) · one webinar for clinic owners on access control and record-keeping.

### Do not prioritise
Conferences and exhibition stands (cost per meeting is not defensible with no product proof) · broad content marketing · email sequences to purchased lists · medical association sponsorships · SEO for "HIS Saudi Arabia" and NPHIES-adjacent terms — those queries lead directly to the two conversations we cannot have.

**Status: LOCKED FOR MVP** · **Confidence: Medium** · **Revisit trigger:** after 90 days, if founder outbound produces fewer than 2 qualified prospects per month.

---

## 20. Conversion Funnel (GTM-003) — LOCKED FOR MVP

```
Founder outbound / WhatsApp / organic search
        ↓
Website visitor  →  self-disqualification is a success outcome, not a leak
        ↓
Walkthrough request (primary CTA)
        ↓
Qualification call (15 min, §5.1/§5.2)
        ↓
Live demo  D-1 → D-2 → D-4   [→ D-7 once seeded]
        ↓
Written scope with published exclusions
        ↓
PAID DESIGN-PARTNER PILOT (60–90 days, one clinic)
        ↓
Annual managed subscription + implementation
        ↓
Go-live → 30-day hypercare → reference / case content (with consent)
```

Two deliberate departures from the market's `traffic → lead form → sales call → demo` convention: the pricing and exclusion content sits **before** the form, so the form is smaller and the leads are pre-qualified; and the paid pilot replaces the free trial that 15 of 16 competitors also do not offer — but for a different reason. Ours is a capability constraint honestly handled, not a lead-capture tactic.

---

## 21. Trust & Proof Strategy (GTM-004) — LOCKED

| Proof available NOW | Proof after demo preparation | Proof requiring real customers |
|---|---|---|
| Recorded audit-integrity verification run | Reception → physician journey recording | Pilot outcome write-up (with consent) |
| Permission-model screenshots and matrix | Report export to CSV, on screen | Named reference clinic |
| Real annotated screenshots of six working screens | Multi-role side-by-side comparison | Reference calls (the C-02 mechanism) |
| Published claim register with every qualification attached | Ophthalmology examination walkthrough | Implementation-duration evidence |
| Published registers: 47 Disabled · 27 Uninstalled · 18 Requires-Integration · 60 Missing | Seeded demo available on request | Retention and support-load evidence |
| Open-source inspectability — "check every claim against the upstream project" | | |
| Published pricing model, inclusions and exclusions | | |
| Dated milestone feed (non-customer milestones only) | | |
| Written data-exit procedure | | |

**Never manufactured, under any circumstance:** testimonials · hospital or clinic logos · customer counts · ROI statistics · uptime or performance figures · implementation-time claims · certification badges · "trusted by" strips. Source A §1.4 documents precisely what this looks like when a competitor does it, and the damage it causes.

**The one-sentence trust proposition:** *we are the only vendor in this comparison whose claims a buyer can independently verify* — a direct commercial reading of the audit's own §27.5.

---

## 22. Objection Handling — LOCKED FOR MVP

| # | Objection | Persona | Honest answer | Evidence | Where it lives |
|---|---|---|---|---|---|
| O-1 | "OpenEMR is free — why pay you?" | P-1 | You are not paying for software; you are paying for it to be implemented, configured to your clinic, hosted, patched, backed up, supported and trained on, by a named party with responsibility. Downloading it is genuinely free; running it properly is the work | §24, audit §27.5, effort ladder §25.1 | Pricing · About · FAQ |
| O-2 | "Who owns my data?" | P-1, P-3 | You do. It is in an open, documented 283-table schema; 55 reports export to CSV; database access is yours; the exit procedure is written into the contract | CLM-0019, audit §27.5 | Your data, your exit |
| O-3 | "Can I leave later?" | P-1 | Yes, and the procedure is published before you sign. We do not hold the schema, the format or the credentials hostage | Same | Same |
| O-4 | "Is it Saudi compliant?" | P-1, P-4 | **No, and we will not claim it.** There is no NPHIES, no ZATCA e-invoicing and no VAT handling in this product today. Your invoicing and any insurance claims stay in the systems you already use. If those are your priority, we are not your vendor yet | GAP-0046…0059, L-11, EXT-01, EXT-02 | FAQ · What we don't do · first qualification call |
| O-5 | "Does it support NPHIES?" | P-1, P-4 | No. It is on the roadmap and it is a substantial engineering programme, not a switch — we will tell you when it exists, not before | GAP-0046, L-26 | FAQ |
| O-6 | "Is it cloud? Is it SaaS?" | P-1, P-3 | We host it for you and you subscribe annually — that part is true. It is **not** a multi-tenant platform: each clinic gets its own database and its own deployment, provisioned by us | CLM-0029, GAP-0043, L-07 | FAQ · Implementation |
| O-7 | "Does it have Arabic?" | P-1, P-2 | Partly, and here is exactly how much: about 47.5% of interface strings, and only the interface chrome. Picklists, layout labels and code descriptions are untranslated, right-to-left needs per-screen review, and Arabic PDF output will not render correctly as shipped. Completing it is on the roadmap | CLM-0030, L-08…L-10 | FAQ · demo D-5, stated before it is shown |
| O-8 | "Can it manage multiple clinics?" | P-1 | Yes — as separate sites, each with its own database, provisioned manually by us. Not as one pooled tenant | CLM-0029 | Two-or-three-locations page |
| O-9 | "Does it support hospitals / inpatient?" | P-1 | No. No admissions, wards, beds, theatre, eMAR or nursing documentation. Outpatient only, deliberately | GAP-0001…0014, L-01 | Hero · FAQ |
| O-10 | "What integrations exist?" | P-3 | The software implements the standards — HL7 v2, FHIR R4, X12, DICOM viewing — but every external connection needs a third-party contract that is yours to hold. Today, none is configured. We will not present an integration as included | §17.4 audit, L-13, CLM-0011/0012/0023 | Product · FAQ |
| O-11 | "What if you disappear?" | P-1, P-3 | The software is open source and continues to exist without us; your data is in an open schema on infrastructure we will name in the contract; the escrow-equivalent is the contract's exit clause plus the fact that any competent PHP/MariaDB engineer can take it over | Audit §27.5 | FAQ · Implementation |
| O-12 | "Is it secure?" | P-3 | We will not use that word as a claim. Here is what exists: bcrypt, an enforced password policy, lockout, per-IP brute-force protection, role-based access across 65 objects, encounter-level sensitivity, break-glass, and a logged audit trail with an integrity check. Here is what does not: **two-factor cannot be enforced**, and nothing here is a compliance certification | CLM-0024/0026/0027, L-03, audit §27.3 | Security & Audit |
| O-13 | "Can it be customised?" | P-2 | Screens, lists, layouts and forms — yes, by you, and we show you how. Module extension — yes, ~90 events. Billing generators — no, they are not pluggable | CLM-0005, CLM-0031, L-26 | Configure it yourself |
| O-14 | "Why you rather than Waseel / Insta / Cloudpital / eCarePlus?" | P-1 | For insurance-heavy clinics that need NPHIES today, choose them — and we will say so. For a self-pay clinic that wants its record controlled, verifiable and portable, with a published price and a published list of what is not included, none of them competes on that ground | Source A §6, §9, §14 | Comparison content · qualification call |
| O-15 | "You have no customers." | P-1 | Correct, and we will not pretend otherwise. That is why the first engagements are design partnerships at a discount, with a written success gate, and why everything we claim is verifiable without a reference | §0.3, §15.3 | FAQ · pilot proposal |

**Rule:** never evade a genuine gap. The gaps are the credibility mechanism — that is the whole strategy.

---

## 23. Competitive Positioning — LOCKED FOR MVP

### 23.1 Strategic groups
| Group | Examples | Our stance |
|---|---|---|
| Enterprise inpatient HIS | TrakCare (C-07), Oracle Health (C-09), YASASII (C-08), Simplex (C-10) | **Different market.** Never compare. Study their information architecture and objection handling only |
| Saudi/GCC HIS for hospitals and groups | OASIS Plus (C-12), CareWare (C-13), Nexen Care (C-04), MEDAS (C-11), VIDA (C-14) | **Avoid head-to-head.** They win on references, national integrations and scale — all unavailable to us |
| Saudi/GCC clinic management and RCM | Waseel (C-03), Insta (C-01), eCarePlus (C-06), Cloudpital (C-05), Belal Soft (C-17), plus most of the 9 unverified | **The real competitive set.** Compete only in the self-pay segment and only on disclosure, verifiability, governance and configurability |
| Open-source-adjacent commercial peers | Open Dental (C-02) | **Not a competitor in KSA — the commercial model to learn from.** Published prices, trial with sample data, reference calls, money-back guarantee, documentation-as-website |
| Specialty / dental | DentiMax (C-25), DenTech (C-24), Avicenna (C-26) | Out of scope — dental charting is GAP-0020 |
| The alternatives that are not software | Paper and Excel · WhatsApp scheduling · an abandoned legacy system · **generic self-installed OpenEMR** | **Where most deals are actually won or lost** |

### 23.2 The strongest competitive frame
Not "us versus Waseel". The frame that fits the ICP and the evidence is:

> **"A system somebody is responsible for, versus a system nobody is responsible for."**

Against paper and abandoned systems, the comparison is about control, traceability and someone answering the phone. Against self-installed OpenEMR, it is about who patches 373 commits of upstream drift, who fixes the backup configuration, who designs the ACL, who builds the forms, and who is accountable at 9am on a Sunday. Both comparisons are winnable today; the NPHIES-vendor comparison is not.

### 23.3 Competitors we will actively compare against
Only two, and only in educational content: **generic self-installed OpenEMR** and **paper/spreadsheet operation**. Named-competitor comparison content is deferred until (a) Source A's 9 unverified dossiers are completed, and (b) we have a customer — comparing without either invites a factual challenge we would lose on evidence, not on merit.

**Status: LOCKED FOR MVP** · **Confidence: Medium** (constrained by §2.3 — 7 of the 9 unverified competitors sit in the group we will actually meet).

---

## 24. Product-vs-Service Commercial Model (GTM-006) — LOCKED

Because the software is open source and unmodified, this is the most important commercial section in the document.

| Element | Who provides it | Is it what the customer is buying? |
|---|---|---|
| Software | Upstream OpenEMR — free, public, inspectable | **No.** Never charged for, never described as ours |
| **Implementation** — requirements, facility and branding setup, role and ACL design, form building, list and layout configuration, fee/price setup where relevant | Us | **Yes — core** |
| **Configuration** — the 491 settings, menus, sensitivity, ownership scoping | Us | **Yes — core** |
| **Data migration** — from paper, Excel, or a legacy database | Us, quoted after inspection | **Yes** |
| **Localisation setup** — Arabic interface enablement, RTL review of the screens the clinic actually uses, timezone, currency display | Us | **Yes**, with the 47.5% limit stated |
| **Hosting and operations** — deployment, backup (and fixing the shipped backup misconfiguration), monitoring, restores | Us | **Yes — core** |
| **Patch currency** — closing and then maintaining the 373-commit upstream gap, and resolving the gitignored composer-installed module's provenance | Us | **Yes — and it is the single most under-appreciated line item** |
| **Support** — named channels, published hours, WhatsApp | Us | **Yes — core** |
| **Training** — role-based, at go-live and on request | Us | **Yes** |
| **Integrations** — lab, eRx, clearinghouse, payment, SMS | Us as a project; **the third-party contract is the customer's** | Yes, as a separately priced project |
| **Custom development** | Us, day-rate, only where it does not create unmaintainable divergence | Yes, exceptional |
| Invoicing, VAT, ZATCA e-invoicing | **Not us.** The customer's existing system | **Explicitly excluded — in writing** |
| Insurance claims, NPHIES, eligibility, pre-authorisation | **Not us.** The customer's existing arrangement | **Explicitly excluded — in writing** |

### Why should a customer pay us instead of downloading OpenEMR themselves?

> Because downloading it is the easiest 2% of the job. What we sell is the other 98%: a system installed and kept current against an upstream project that moves hundreds of commits between releases; an access model designed for your actual staff rather than one shared administrator login; your forms built rather than a blank form engine; your data migrated; your staff trained; backups that actually run rather than a shipped configuration that fails; and a named party who is responsible when something breaks. The audit that governs this document is public evidence of how much of that work is real: on a stock installation, six of twenty-two demo scenarios are even possible, the background service runner has never executed, the backup tool is misconfigured, and every external-facing capability is switched off. That is what a clinic that downloads it themselves is buying — for free, and at their own risk.

This answer appears verbatim (or in its Arabic equivalent) on the pricing page, the About page and the FAQ, and is the founder's answer in every first meeting.

**Status: LOCKED** · **Confidence: High**

---

## 25. Launch Sequence (GTM-005) — LOCKED FOR MVP

Dependencies, not dates. Nothing advances until the previous gate is met.

### Phase 0 — Positioning locked
- **Preconditions:** this document reviewed and accepted; the §14 claim register adopted as binding.
- **Success gate:** every future marketing artefact traces to a claim in §14.1 or §14.2.
- **Risk:** the register is treated as advisory; the first piece of copy re-introduces a prohibited word.

### Phase 1 — Demo preparation *(no development required)*
- **Product:** B2 create 4–5 role users (minutes) · B4 brand the facility (hours) · B1 seed 25–30 synthetic patients and encounters (1–3 days) · B3 payers and fee schedule · fix B7 backup path · investigate B6 service runner · resolve the D-4/D-5 caveats so they are stated before they are seen.
- **Demo:** D-1…D-5 rehearsed and recorded; D-7 built; the reset procedure documented.
- **Marketing assets:** six real annotated screenshots; the recorded integrity run.
- **Success gate:** D-7 runs end-to-end twice without an empty screen or an error.
- **Risk:** seeding takes materially longer than the audit's 1–3 day estimate (A-06).

### Phase 2 — Operational readiness for a real customer
- **Product:** close the 373-commit upstream gap and establish an ongoing patch cadence · resolve the gitignored composer-installed module's provenance · fix the 11 reports lacking in-file authorisation (L-24) and the one with none · document deployment, backup and restore · decide the hosting region and state it publicly.
- **Success gate:** a documented, repeatable deployment of a fresh clinic instance, and a successful restore test.
- **Risk:** this phase is invisible to marketing and is therefore the one most likely to be skipped. **It must not be** — selling hosting for an unpatched deployment is the most serious commercial risk in this plan.

### Phase 3 — Website launch (bilingual)
- **Preconditions:** Phases 1 and 2 gates met.
- **Assets:** IA per §17.2 · the four registers published · "How pricing works" (model only) · recorded demos · buyer's checklist · WhatsApp channel live.
- **Success gate:** a stranger in the ICP can determine in three minutes whether they are a fit, without contacting us.
- **Risk:** publishing before Phase 2 creates demand we cannot safely serve.

### Phase 4 — Design partners *(2–3 paid pilots)*
- **Preconditions:** website live; demo rehearsed; scope template with exclusions; pilot agreement with a success gate and an exit clause.
- **Success gate:** at least 2 pilots reach go-live and renew into an annual subscription; implementation hours recorded; support hours recorded.
- **Risk:** free-of-charge scope creep; an ophthalmology pilot that exposes a form gap; a customer who assumed invoicing was included (mitigated by O-4 and the written exclusions).

### Phase 5 — Published pricing and controlled acquisition
- **Preconditions:** three real transactions agreeing within a defensible band (§15.4); cost model built from Phase 4 actuals.
- **Actions:** publish figures; open the referral-partner programme; begin paid search on the pricing and exit queries.
- **Success gate:** inbound qualified walkthrough requests without founder outbound.
- **Risk:** publishing a price the cost model cannot sustain.

### Phase 6 (conditional) — Roadmap-gated market expansion
Triggered only by shipped capability: ZATCA/VAT ships → invoicing-inclusive selling; Arabic completion ships → Arabic-first positioning; NPHIES ships → the insurance-clinic majority becomes addressable, and §4's primary market decision is re-opened.

---

## 26. Market-Driven Product Gap Roadmap

Roadmap follows the chosen strategy, not competitor feature parity. Nothing in this section may appear in current marketing.

### P0 — Blocks initial GTM
| Item | Ref | Nature | Blocks ICP? | Blocks KSA commercialisation? | Note |
|---|---|---|---|---|---|
| Demo data, role accounts, branding, payers | B1–B4 | Configuration + data, **no development** | **Yes** | — | 1–3 days. Blocks D-7/D-8 and every clinical claim's demo |
| Upstream patch currency (373 commits) + module provenance | L-27 | Engineering / operations | **Yes** — cannot host a customer on it | Yes | Also the prerequisite for any security conversation |
| Backup misconfiguration; Unix-only commands on a Windows host | L-21, CFG-0120 | Configuration | **Yes** | Yes | Selling "we back it up" without this is indefensible |
| Background service runner never executed | L-20, GAP-0063 | Defect | **Yes** — reminders and email queue silently do not run | — | |
| Reports without in-file authorisation (11 + 1) | L-24 | Defect | **Yes** — contradicts Pillar 1 directly | — | Our differentiator is access control; this is the one defect that undermines the pitch |
| Stock branding, donation links, vendor placeholders | L-17, §19.4 | Configuration | **Yes** | — | The demo currently presents as unbranded open-source software |

### P1 — High-value near-term
| Item | Ref | Competitors marketing it | Importance | Nature | Strengthens differentiation? | Note |
|---|---|---|---|---|---|---|
| **ZATCA Phase 2 / VAT in the billing chain** | GAP-0052, GAP-0053, L-11, L-12, **EXT-01** | C-01, C-03, C-05, C-06 | **Critical** | **Schema change** — no tax field exists anywhere | Yes — converts us from "clinical only" to running the clinic's money | The first roadmap item that changes the addressable market |
| **Arabic completion + RTL remediation + Arabic-shaping PDF font** | L-08, L-09, L-10, CLM-0030 | 8 of 12 GCC competitors | High | Translation + per-screen engineering | Yes — removes our weakest comparison | Includes `list_options` and `layout_options`, not just chrome |
| **MFA enforcement** | CAP-0218, L-03 | C-01 | High | Development | Yes — Pillar 1 is incomplete without it | Currently we must state "cannot be enforced" in every security conversation |
| **NPHIES pathway** | GAP-0046, L-26, **EXT-02** | C-01, C-03, C-06, C-13 | **Critical for expansion**, not for the initial ICP | **Core patching** — `BillingProcessor` has a hard-coded dispatch ladder with no factory or event | Yes — but it is a programme, not a feature | Requires: FHIR enablement, Saudi identifiers (GAP-0048/0050), Arabic name structure (GAP-0057), Saudi coding (GAP-0054), payer registry (GAP-0049). **Sequence it after ZATCA, and scope it as a programme with engineering sign-off** |
| Repeatable multi-customer provisioning | GAP-0043, L-07 | All cloud competitors | High operationally | Automation over the existing per-site model | Indirect | Not full multi-tenancy — just making Phase 4 repeatable |

### P2 — Competitive enhancement
Basic operational dashboards (GAP-0040/0041 — `chart.js` is vendored but unused; scope narrowly, do not promise "analytics") · patient portal enablement as a supported offering (CLM-0028) · Saudi identifiers and Hijri display, whose `ext-intl` dependency is already satisfied (GAP-0048, GAP-0058) · sensitivity gating extended beyond encounters and to the API (L-28) · audit-log noise reduction (L-22, 93% noise, ~2,000 rows/day idle) and `audit_events_lab-order` logging (GAP-0070) · audit integrity strengthened from a plain hash toward an HMAC and chained rows (L-23) — this directly upgrades our flagship claim.

### P3 — Later / optional
Denial management (GAP-0028) · telehealth (CAP-0262 + Comlink contract) · lab and eRx interfacing once a customer holds the contract · schema and UI orphan cleanup (L-25) · current-year quality measures (L-16).

### Explicitly deprioritised — do **not** build for parity
| Not building | Why |
|---|---|
| Inpatient, ADT, ward, bed, eMAR, ICU, theatre, nursing documentation (GAP-0001…0014) | It is a different product, a different buyer and a different sales motion; building it abandons the locked positioning |
| LIS, RIS, PACS, blood bank (GAP-0016…0018) | Ancillary businesses; partner or ignore |
| Dental charting (GAP-0020), physiotherapy (GAP-0021) | Out of the locked ICP |
| GL, accounting, ERP, AP, procurement, HR, payroll, asset management (GAP-0029…0039) | The clinic already has these, and Pillar 4 says so out loud |
| Full analytics / BI platform (GAP-0040/0041) | P2 is deliberately "a few operational reports", not a BI programme |
| Patient mobile app (GAP-0023/0024) | Expensive, undifferentiated, and irrelevant to record control |
| Full multi-tenant SaaS (GAP-0043) | Revisit only if self-service becomes the strategy — which §18 rejects |
| US market work: ONC certification, current-year eCQM, 837I/UB-04 | The audited US RCM depth is real and, for this strategy, out of scope. Do not spend on it |
| CDS Hooks (GAP-0044), device integration (GAP-0042) | No buyer in the ICP asks |

---

## 27. Assumption Register

| ID | Assumption | Why it matters | Supporting evidence | Contrary evidence | Confidence | Validation method | Decision affected |
|---|---|---|---|---|---|---|---|
| **A-01** | The founder has a usable Saudi healthcare and insurance network and domain credibility to open doors | The entire primary channel and the founder-led motion rest on it | Project context outside Sources A and B | **Not evidenced in either source document** | Medium | Confirm explicitly; count warm introductions available in 30 days | §18 sales motion, §19 channels |
| **A-02** | A meaningful population of Saudi outpatient clinics is predominantly self-pay | The ICP exists only if this is true | Cash-pay concentration in refractive, aesthetic and some GP settings is a common industry understanding | **No source in this project measures it** | **Low-Medium** | 8–10 buyer interviews; ask payer mix first, before anything else | §4, §5 — **the highest-risk assumption in the document** |
| **A-03** | Those clinics will accept a clinical system that does not issue their tax invoice | Without it, EXT-01 disqualifies the whole ICP | Clinics commonly run separate accounting/POS software | None gathered | **Low-Medium** | Same interviews: "would you run this alongside your invoicing system?" | §4, §5, §24 |
| **A-04** | The ICP will pay an annual per-location subscription plus a one-off implementation fee | The packaging architecture | C-25 markets per-location pricing as a differentiator | 0 of 11 GCC competitors publish anything, so no regional anchor exists | Low | Pricing interviews; first two design-partner transactions | §15 |
| **A-05** | Record control, traceability and portability are felt pains, not just true statements | Pillars 1 and 2 lead the messaging | 0–1 of 16 competitors address them, which cuts both ways: white space, or no demand | The same fact is also consistent with "buyers do not care" | **Medium — and deliberately flagged** | Landing-page message test; track which pillar prospects ask about first | §8, §10, §11 |
| **A-06** | Demo preparation is 1–3 days of configuration with no development | Phase 1 gate and the whole launch sequence | The audit's own estimate, §28.3 | The audit also records that no one has attempted it | Medium-High | Do it and record actual hours | §16, §25 |
| **A-07** | Ophthalmology is a viable beachhead with reachable clinics | Lead segment page and outbound targeting | CLM-0004 audited depth; 2 of 16 competitors run specialty pages | No count of Saudi ophthalmology clinics was gathered | Medium | Build a target list of 30 named clinics before committing the page | §4, §17.4 |
| **A-08** | Buyers will read published exclusions as trustworthy rather than as weakness | Pillar 4 and D-1 depend on it | C-02 succeeds commercially doing exactly this; KLAS publishes limitations alongside ratings and buyers accept it | It is untested in this market, and Arabic-language reaction is entirely unknown | Medium | A/B the homepage "what isn't included" strip; ask directly in interviews | §10, §11, §17 |
| **A-09** | EXT-01 and EXT-02 are accurate as applied to small private clinics | Determines whether the ICP is real | Multiple consistent secondary sources | **No primary regulator document was read for either** | Medium | Read zatca.gov.sa wave criteria and CHI/NPHIES primary material; ideally confirm with two clinic finance managers | §4, §5, §26 |
| **A-10** | Hosting in-Kingdom or a named acceptable region is achievable at a cost the pricing can carry | Hosting is a core paid component | Not evaluated in either source | — | **Low — unevaluated** | Get two hosting quotes and confirm the data-residency position before publishing anything about hosting | §15, §24, §25 Phase 2 |
| **A-11** | The 9 unverified competitors do not change the white-space frequencies materially | Every published competitive figure | They are excluded from all current figures, so no figure is inflated | 7 of 9 sit in the tier we will actually meet | Medium | Complete Source A §24.2 | §10, §17, §23 |
| **A-12** | Self-pay clinics still generate enough insurance-adjacent work that "claims stay outside" is workable rather than fatal | Scope boundary credibility | — | EXT-02 shows insurance workflow is centralised nationally, which raises the cost of any split | Medium | Interviews: "how do you handle the insurance patients you do see?" | §5, §22 O-4 |

**No assumption above is presented as a fact anywhere else in this document.**

---

## 28. Validation Plan

| # | Hypothesis | Target respondent | Test | Success signal | Failure signal | Decision affected |
|---|---|---|---|---|---|---|
| V-1 | A reachable population of self-pay Saudi outpatient clinics exists (A-02) | 10 clinic owners/managers, mixed specialty, Riyadh + Jeddah | 30-minute structured interview opening with payer mix | ≥4 of 10 report majority self-pay | ≤2 of 10 | §4, §5 — **run this first; everything else depends on it** |
| V-2 | They will accept a clinical system separate from invoicing (A-03) | Same 10, plus 3 clinic accountants | Direct question with the boundary drawn explicitly | ≥5 say yes without hesitation | Repeated "then what's the point?" | §4, §24 |
| V-3 | Record control and portability are felt pains (A-05) | Same 10 | Open question before any pitch: "what goes wrong with your records today?" | ≥4 raise access, traceability or data ownership unprompted | 0–1 raise them | §8, §11 |
| V-4 | The disclosure-led homepage converts (A-08) | Cold traffic, both languages | Two landing-page variants: disclosure-led vs capability-led | Disclosure-led produces more walkthrough requests | Materially fewer | §17 |
| V-5 | The demo storyline converts (D-1→D-2→D-4) | First 10 demos | Record which moment produces the pricing question | ≥6 of 10 ask about price or timeline within the demo | ≤3 | §16 |
| V-6 | The packaging model fits (A-04) | 8 pricing conversations | Present the model without figures; ask what shape they expected | ≥5 accept per-location annual + one-off implementation | Consistent push to monthly or per-provider | §15 |
| V-7 | Price band (PRC-003) | Same 8 + first 2 transactions | Range questions plus real quotes | 3 transactions within a defensible band | Wide dispersion | §15 — **the gate that unblocks published pricing** |
| V-8 | Implementation is deliverable at the assumed effort (A-06) | Design partner #1 | Record every hour | Within 1.5× the estimate | Beyond 3× | §15, §25 |
| V-9 | Ophthalmology beachhead (A-07) | Target-list build + 5 ophthalmology conversations | Can 30 named clinics be listed and 5 reached? | Yes to both | Neither | §4, §17.4 |
| V-10 | Regulatory position (A-09) | Primary sources + 2 clinic finance managers | Read zatca.gov.sa and CHI/NPHIES material | Confirms EXT-01/EXT-02 as applied to small clinics | Contradicts either | §4, §26 |

**Sequence:** V-10 and V-1 before anything is built. V-2, V-3 with the same interviews. V-4, V-5 at website and demo launch. V-6, V-7, V-8 during design partnerships. **No result is invented; where a test has not run, the corresponding decision stays PROVISIONAL or BLOCKED.**

---

## 29. GTM Measurement Framework

Only metrics relevant to a founder-led, demo-led, pilot-first motion. **No targets are asserted** — none is supportable without a baseline.

| Metric | Definition | Target |
|---|---|---|
| Qualified conversations per month | Meets §5.1, fails none of §5.2 | Baseline first |
| Self-disqualification rate | Prospects who correctly rule themselves out before a call — **a success metric, not a leak** | Baseline first |
| Walkthrough requests per 100 sessions | Website → primary CTA | Baseline first |
| Qualification → demo | | Baseline first |
| Demo → written scope | | Baseline first |
| Scope → paid pilot | The real commercial signal | Baseline first |
| Pilot → annual subscription | The only conversion that matters in Phase 4 | Baseline first |
| Sales-cycle length | First contact → signed pilot | Baseline first |
| **Implementation hours per clinic** | Actual, recorded | **Feeds PRC-003 directly** |
| **Support hours per clinic per month** | Actual, recorded | **Feeds PRC-003 directly** |
| Activation | % of seeded roles logging in weekly by week 4 | Baseline first |
| Adoption depth | Encounters documented per provider per week | Baseline first |
| Retention | Annual renewal | Baseline first |
| Objection frequency | Which of O-1…O-15 appears, and where the deal stalls | Reviewed monthly; drives §17 and §22 |

The two bolded metrics are the highest-value instrumentation in the plan: they are the missing evidence that unblocks pricing.

---

## 30. Risk Register

| ID | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| R-01 | The self-pay segment turns out to be too small or unreachable (A-02) | Medium | **Fatal to the locked ICP** | V-1 before any build; fallback is an RCM-partnered motion where the partner handles NPHIES and we handle the record |
| R-02 | Marketing drifts back to NPHIES/ZATCA/AI language because competitors use it | **High** | Severe — one prohibited claim destroys D-1 permanently | §14 register is binding; every artefact reviewed against it; one named owner |
| R-03 | Selling hosting on an unpatched, 373-commit-behind deployment | Medium | **Severe — security and reputational** | Phase 2 is a hard gate before Phase 3 |
| R-04 | A prospect or competitor points out the product is stock OpenEMR | **Certain** | Low, **if** we said it first | We disclose it in the category descriptor. It only becomes damage if we concealed it |
| R-05 | The demo shows an empty screen or an error | Medium | High — the disclosure positioning makes a broken demo doubly damaging | Rehearse; never open Backup; state the two overdue background services first; documented reset |
| R-06 | Design partner assumed invoicing or claims were included | Medium | High | O-4 in qualification, written exclusions in scope, P-4 finance conversation held before signature |
| R-07 | Pricing published before cost evidence exists | Medium | High | PRC-003 stays BLOCKED until V-7 |
| R-08 | Arabic site implies a fully Arabic product | Medium | High — contradicts the entire positioning in the market's own language | The 47.5% limitation appears on the Arabic site with equal prominence; native review before launch |
| R-09 | Implementation effort exceeds the estimate and margin disappears | Medium | High | V-8 instrumentation; only 2–3 design partners before pricing is set |
| R-10 | Founder-led motion does not scale beyond a handful of customers | **Certain, eventually** | Medium | It is a phase, not a strategy; partner-led is queued behind a repeatable runbook |
| R-11 | Competitors copy the disclosure and audit messaging | Low near-term | Medium | D-2 (open schema) and D-1 (inspectable software) are structural; the copyable parts are the least valuable |
| R-12 | The 9 unverified competitors change a published frequency claim | Medium | Low-Medium | Publish the mechanism, not the number, until Source A is completed |
| R-13 | A regulator or payer development makes the "claims stay outside" boundary untenable | Low-Medium | High | Monitor CHI/NPHIES; re-open §4 if it happens |
| R-14 | The reports lacking authorisation (L-24) are discovered by a customer | Medium | **Severe — it directly contradicts Pillar 1** | P0 fix before any customer deployment |

---

## 31. Decisions NOT Taken

| Rejected | Why |
|---|---|
| Target Saudi hospitals or the inpatient market | GAP-0001…0014. No amount of positioning survives the first RFP question |
| Target insurance-accepting clinics now | EXT-02 + GAP-0046 + L-26. Their core daily workflow is the one we cannot perform |
| Enter the US ambulatory market where the audited RCM and FHIR depth actually fits | No ONC certification (and it is a prohibited claim), no US presence, and free OpenEMR competes directly |
| Lead with "open source" as the value proposition | Invites "then I'll download it myself"; §24 shows the answer is service, not licence |
| Lead on price / "affordable" | No price evidence, no cost model, and it concedes the category |
| Use "HIS" as the market-facing category | §27.3 prohibits it unqualified; the product is ambulatory-only |
| Use "SaaS platform" | GAP-0043, L-07. "Hosted and managed subscription" is true; "multi-tenant SaaS" is not |
| Free self-service trial at launch (the C-02 model) | Commercially proven elsewhere, but a platform programme here — deferred, not dismissed |
| Build a specialty-page farm (the C-05 pattern) | Only ophthalmology has audited depth; the rest would be exactly the unverifiable claim we are positioning against |
| Named-competitor comparison content now | 9 of 26 dossiers unverified, and 7 of those are in the tier we will meet |
| Publish exact prices immediately despite the empty lane | The single most tempting error available. An invented number would destroy the verifiability asset the whole strategy rests on |
| Market the US revenue-cycle depth (837P, 835, CMS-1500, COB) | Genuinely the product's deepest audited capability, and genuinely irrelevant to the locked ICP. Kept off the site entirely |
| Build analytics to reach feature parity | GAP-0040/0041 is real, but P2 is "a few operational reports", not a BI programme |
| Treat NPHIES as the first roadmap item | It is the largest prize and the largest programme (core patching, L-26). ZATCA/VAT is smaller, universally required, and unblocks revenue handling first |

---

## 32. Locked Decision Register

| ID | Question | Decision | Status | Conf. | Evidence | Alternatives rejected | Revisit trigger |
|---|---|---|---|---|---|---|---|
| **POS-001** | Primary target market | Saudi private self-pay outpatient clinics, 3–15 providers, 1 site; ophthalmology beachhead | LOCKED FOR MVP | Med-High | Audit §23, §26, §28; Source A §13; EXT-01/02 | Hospitals; insurance clinics; US; GCC-wide; open-source community | ZATCA or NPHIES ships; 3 consecutive novel disqualifications |
| **ICP-001** | Primary ICP | §5 profile | LOCKED FOR MVP | Medium | Audit §27–§30; Source A §15, §16 | Multi-site groups; hospitals; dental | After 8 buyer interviews |
| **ICP-002** | Disqualifiers | §5.4 | LOCKED | High | GAP registers; L-01…L-12 | — | Capability change |
| **PER-001** | Buying committee | Owner (buyer) · Clinic manager (champion) · IT contractor (gatekeeper) · Finance, physician, reception | LOCKED FOR MVP | Medium | Buyer logic + Source A §10 audience-first IA | Formal committee model | Interviews reveal a consistent fifth role |
| **POS-002** | Product category | Clinic management system and EMR — outpatient. "HIS" prohibited unqualified; "SaaS platform" prohibited | LOCKED | High | Audit §27.3; Source A §4 | HIS; healthcare platform; PMS | Inpatient or multi-tenancy is built |
| **POS-003** | Primary problem | Loss of control over the clinical record — access, evidence, portability | LOCKED FOR MVP | Med-High | Audit §29.2; Source A §14, §15 | Scheduling pain; billing pain | Interviews show throughput dominates |
| **POS-004** | Core value proposition | §9 | LOCKED FOR MVP | Med-High | CLM-0024, 0025, 0019, 0004, 0005 | Price-led; open-source-led | ICP change |
| **POS-005** | Differentiators | D-1 verifiability · D-2 data ownership/exit · D-3 roles+audit shown · D-4 configurability | LOCKED FOR MVP | Med-High | Source A §14 (0/16, 1/16, 2/16, 0/16); audit §27.5, CLM-0024/0025/0005/0031 | Arabic; clinical breadth; open-source; US RCM | Source A re-run changes a frequency |
| **POS-006** | Value pillars | Know who did what · Your records stay yours · Fits how your clinic works · No surprises | LOCKED FOR MVP | Med-High | As above | Module-organised pillars | Demo feedback reorders them |
| **POS-007** | Positioning statement | §12 | LOCKED FOR MVP | Med-High | Composite | Two candidates in §12 | ICP or differentiator change |
| **MSG-001** | Messaging hierarchy | §13, levels 0–5 + vocabulary rules | LOCKED FOR MVP | High | Audit §27.1–27.4 | — | Any audit §27 change |
| **MSG-002** | Claim register | §14 — 15 Safe Now, 10 Safe With Qualification, rest deferred or prohibited | LOCKED | High | CLM-0001…0032 | — | New capability audit |
| **PRC-001** | Pricing philosophy and transparency | Service-priced; publish the model now, figures after validation | LOCKED | High | Source A §6 (mean 0.7/5), §9, §14 | Opaque pricing; publish figures now | — |
| **PRC-002** | Packaging architecture | One-off implementation + annual managed subscription, per location, banded by provider count | LOCKED FOR MVP | Medium | C-02/C-25 precedent; L-07; audit §25.1 | Per provider; per user; monthly; licence | 2 of first 3 customers push back |
| **PRC-003** | Exact price points | **None asserted** | **BLOCKED** | — | No WTP data, no cost data, no GCC anchor | — | V-7 completes |
| **DEM-001** | Demo types | Screenshots + recordings + live guided demo + paid pilot. No free trial | LOCKED FOR MVP | High | Audit §28; Source A §9, §20 | Self-service trial; sandbox | Multi-tenancy or seeding automation ships |
| **DEM-002** | Demo storylines | D-1→D-2→D-4 now; D-7 after seeding | LOCKED FOR MVP | High | Audit §28.2 | Feature tour | Seeding completes |
| **DEM-003** | Synthetic dataset | §16.3 | LOCKED FOR MVP | Med-High | Audit §28.1 blockers | Real data (prohibited) | Effort exceeds estimate |
| **WEB-001** | Website objective and CTA | Qualified walkthrough requests; one primary CTA | LOCKED | High | Source A §9, §12 | Quote requests; gated content | — |
| **WEB-002** | Information architecture | §17.2 | LOCKED FOR MVP | Med-High | Source A §10, §17; audit §27.3 | Source A's sitemap as-is; specialty farm | Positioning change |
| **WEB-003** | Language strategy | Full Arabic + English site parity; product limits stated equally in both. **Arabic message design PROVISIONAL** | LOCKED FOR MVP / PROVISIONAL | Medium | Source A §13 (8/12 Arabic), §24.2 item 5 | English-only; Arabic-first | Arabic competitor review completed |
| **GTM-001** | Sales motion | Founder-led, demo-led, pilot-first; partner-led secondary | LOCKED FOR MVP | High | Audit §0.3; Source A §9 | Product-led; sales-led; partner-led now | 3 paying customers or a repeatable runbook |
| **GTM-002** | Channels | Founder outbound · WhatsApp · organic search on disqualification questions · IT-contractor referrals | LOCKED FOR MVP | Medium | Source A §9 (WhatsApp 1/16), §14 | Conferences; paid ads; broad content | 90-day review |
| **GTM-003** | Funnel | §20 | LOCKED FOR MVP | Med-High | Composite | Free trial funnel | Motion change |
| **GTM-004** | Trust and proof | Disclosure + inspectability + live demo; nothing manufactured | LOCKED | High | Source A §12; audit §27.3 | Logos, counts, testimonials | Real customers exist |
| **GTM-005** | Launch sequence | Phases 0–6, dependency-gated | LOCKED FOR MVP | Med-High | Audit §28.3; L-17…L-27 | Dated plan; website-first | Gate failure |
| **GTM-006** | Product vs service | Software free and disclosed; implementation, hosting, patching, support are the product | LOCKED | High | Audit §27.5, §25.1, §28 | Licence model; software-led | — |
| **COMP-001** | Competitive frame | "Responsible for, versus nobody responsible for" — against paper and self-installed OpenEMR, not against NPHIES vendors | LOCKED FOR MVP | Medium | Source A §4, §6, §13 | Head-to-head with Saudi HIS vendors | 9 dossiers completed + first customer |
| **GAP-P0** | What blocks GTM | Demo prep · patch currency · backup · service runner · report authorisation · branding | LOCKED | High | Audit §28.1, L-17…L-27 | — | Completion |
| **GAP-P1** | Near-term roadmap | ZATCA/VAT → Arabic → MFA enforcement → NPHIES programme → repeatable provisioning | LOCKED FOR MVP | Medium | GAP registers; EXT-01/02; L-11, L-26 | NPHIES first; analytics first; inpatient | Market or regulatory change |
| **GAP-DEP** | Deprioritised | Inpatient · ancillaries · ERP · BI platform · mobile · multi-tenancy · US certification | LOCKED FOR MVP | High | §4 and §5 positioning | Feature parity | Positioning change |

---

## 33. Evidence Traceability Matrix

Chain: **market evidence → competitor evidence → ICP need → positioning/pillar → CLM claim → CAP capability → commercial readiness → demo readiness → execution.**

| Recommendation | Market/competitor evidence | ICP need | Pillar | CLM | CAP | Commercial | Demo | Execution | Chain complete? |
|---|---|---|---|---|---|---|---|---|---|
| Flagship *Roles & Permissions* page | Role modelling: 1 of 16, prose only | Staff see everything today | P1 | CLM-0024 | CAP-0221…0223 | Sellable now | **Yes today** (multi-role after B2) | Page + D-2 | **Yes** |
| Flagship *Security & Audit* page with recorded run | Audit integrity: **0 of 16** | Disputes, departures, inspections | P1 | CLM-0025 | CAP-0224 | Sellable now, with the hash/HMAC qualification | **Yes — strongest today** | Page + D-1 recording | **Yes** |
| *Configure it yourself* page | Configurability shown: 0 of 16 | "Our form is unique" | P3 | CLM-0005, CLM-0031 | CAP-0047, 0208, 0258…0260 | Sellable now, with "zero layout forms ship configured" | **Yes today** | Page + D-3, D-4 | **Yes** |
| *Your data, your exit* page | Anti-lock-in: 2 of 16, 0 in GCC | Vendor-hostage scar | P2 | CLM-0019 + audit §27.5 | CAP-0161…0172 | Sellable now | Schema now; export after B1 | Page + FAQ | **Yes** |
| Published pricing model and exclusions | Pricing transparency mean **0.7/5**; 0 of 11 GCC | Cannot get a price from anyone | P4 | n/a (commercial) | n/a | **Model yes; figures BLOCKED** | n/a | "How pricing works" page | **Partial — figures blocked** |
| Clinical documentation page | Category table-stakes | The note must fit the clinic | P3 | CLM-0004, 0006, 0007, 0010 | CAP-0035…0046, 0051…0054 | Sellable now with counts stated | **After B1/B2** | Page + D-7 | **Yes (demo deferred)** |
| Ophthalmology segment page | Specialty pages: 2 of 16 | Lead beachhead | P3 | CLM-0004 | CAP-0035…0046 | Sellable now | After B1 | Page | **Yes (demo deferred)** |
| Real annotated screenshots | Product visibility mean **1.9/5** | Buyers cannot see the software | All | Various | Various | Now, for six screens | **Yes today** | Homepage + product pages | **Yes** |
| Arabic website | 8 of 12 GCC competitors | Buyer's language | P4 | CLM-0030 (product Arabic only) | CAP-0251…0253 | Website yes; **product Arabic qualified at 47.5%** | D-5 partial | Bilingual site | **Yes, with mandatory qualification** |
| WhatsApp channel | 1 of 16 | Regional norm | P4 | n/a | n/a | Now | n/a | Contact + CTA | **Yes** |
| Multi-clinic segment page | Segment cards: several competitors | 2–3 locations | P3 | CLM-0029 | CAP-0246…0248 | Yes, **qualified: DB per site, manual** | Not demonstrable | Page with the qualification inline | **Partial — no demo** |
| Portal / dispensary / group therapy | Competitors market these heavily | Occasional ask | P3 | CLM-0028, 0013, 0032 | CAP-0173…0180, 0113…0118, 0087…0091 | Only as "included but switched off" | After activation | One honest "optional, off by default" page | **Partial — must not be a feature page** |
| Interoperability / APIs | Co-existence messaging (C-03) | Rare in this ICP | — | CLM-0021, 0022, 0023 | CAP-0193…0205 | Off by default; **never "certified"** | Blocked | FAQ mention only | **Partial — sub-section only** |
| US revenue cycle | C-03 RCM depth | **Not an ICP need in KSA** | — | CLM-0014…0018 | CAP-0119…0149 | Real but out of scope | Blocked | **Not published on the Saudi site** | **Chain breaks at ICP need — deliberately excluded** |
| Anything Saudi-compliance | 4 of 12 market NPHIES; 4 of 12 ZATCA | Strong ICP need | — | **none** | **none** | **Prohibited** | — | Roadmap only | **Chain breaks at capability — PROHIBITED** |
| Analytics / dashboards | Marketed by 3 competitors | Asked for | — | **none** | **none** | **Prohibited** | — | Roadmap P2, narrowly scoped | **Chain breaks — PROHIBITED** |

Every row whose chain breaks is excluded from marketing, not softened.

---

## 34. Strategic Consistency Audit

| Test | Result |
|---|---|
| Target market → ICP | **Consistent.** Saudi self-pay outpatient clinics, 3–15 providers; the ICP is the market statement made operational |
| ICP → primary problem | **Consistent.** Loss of record control is a problem of clinics running on paper or abandoned systems, which is the ICP's defining state |
| Primary problem → current product | **Consistent, and this is the load-bearing check.** Access control, audit integrity, configuration and export are all Active, and three of the four are demo-ready today |
| Core value → capabilities | **Consistent.** Every clause of §9 traces to CLM-0024, 0025, 0019, 0004, 0005, 0001–0003 |
| Differentiators → buyer relevance | **Partly evidenced.** D-1…D-4 are competitively rare and product-supported; that buyers *care* is A-05, explicitly flagged as the second-highest risk and tested by V-3 |
| Value pillars → positioning | **Consistent.** Pillars 1–2 carry differentiation, 3 carries credibility, 4 carries trust and conversion |
| Messaging → safe claims | **Consistent.** §13 contains no claim outside §14.1–14.2; every banned word from audit §27.1 is enumerated and excluded |
| Pricing → ICP + sales motion | **Consistent in shape, incomplete in substance.** Per-location annual + implementation matches an owner-buyer and a founder-led motion; figures are BLOCKED and honestly marked |
| Demo → value pillars | **Consistent.** Pillar 1 → D-1/D-2 (live now); Pillar 3 → D-3/D-4 (live now); Pillar 2 → schema/export (after B1); Pillar 4 → not a demo |
| Website → positioning | **Consistent.** The four flagship pages are the four differentiators; there is no page for a capability we do not have |
| Channels → personas | **Consistent.** Founder outbound and WhatsApp reach P-1/P-2; disqualification-question SEO reaches P-1/P-3; IT-contractor referrals convert the P-3 veto into a source |
| Roadmap → market blockers | **Consistent.** P0 clears the blockers to selling anything at all; P1 clears the blockers to expanding beyond the wedge; deprioritised items are the ones that would abandon the wedge |
| **Contradiction found and resolved #1** | Pillar 4 says "publish the price" while PRC-003 says "no price exists". **Resolved** by publishing the model and exclusions immediately and gating figures behind V-7 |
| **Contradiction found and resolved #2** | The product's deepest audited strength (US revenue cycle) is irrelevant to the locked market. **Resolved** by explicitly excluding it from marketing rather than diluting the positioning to accommodate it |
| **Contradiction found and resolved #3** | Arabic is a stated asset in the audit and a weakness against 8 of 12 competitors. **Resolved** by separating website language (full parity, now) from product localisation (qualified at 47.5%, P1 roadmap) |
| **Contradiction found and resolved #4** | Pillar 1 claims controlled access while L-24 records 11 reports with no in-file authorisation. **Resolved** by making L-24 a P0 fix — the pillar cannot be sold before the defect is closed |

No unresolved contradiction remains.

---

## 35. Final Certification

### Gate result
The market/competitor research is **NOT CERTIFIED COMPLETE** (Source A §24.1 — 9 of 26 competitors lack a verified marketing surface). The capability audit is likewise **NOT YET CERTIFIED COMPLETE** (Source B §35.4 — 14 questions unclosable under a read-only mandate), though its catalogue is complete, reconciled and internally consistent.

Therefore **VERDICT A is unavailable**, and this document does not claim it.

### Verdict

> # PRODUCT POSITIONING & GO-TO-MARKET STRATEGY — LOCKED FOR MVP WITH PROVISIONAL ITEMS
> **(VERDICT B)** — and, per the research-certification gate, formally: **GTM STRATEGY — NOT READY FOR FINAL LOCK.**

Core positioning, ICP, personas, category, problem, value proposition, differentiators, pillars, messaging, claim register, demo strategy, website strategy, sales motion, channels, funnel, trust strategy, objection handling, competitive frame, commercial model, launch sequence and roadmap are **strong enough to execute now**. They are not certified, and they should not be described as certified.

### Provisional, blocked and gated items

| Item | Status | Revisit trigger |
|---|---|---|
| **PRC-003 — exact price points** | **BLOCKED** | V-7: three real transactions within a defensible band, plus a cost model from Phase 4 actuals |
| **Published competitive frequency claims** ("0 of 16", "0 of 11 GCC") | **PROVISIONAL** | Source A §24.2 item 6 re-run after the 9 dossiers are completed. Publish the mechanism, not the number, until then |
| **WEB-003 — Arabic message design** | **PROVISIONAL** | Arabic-language competitor review (Source A §24.2 item 5) |
| **COMP-001 — competitive frame vs small Saudi vendors** | **PROVISIONAL** | Completion of the 9 dossiers, 7 of which are in that tier |
| **POS-001/ICP-001 — the self-pay wedge** | **LOCKED FOR MVP, contingent on A-02/A-03** | V-1 and V-2. If both fail, the primary market decision re-opens and an RCM-partnered motion becomes the leading alternative |
| **EXT-01 / EXT-02 regulatory basis** | **PROVISIONAL — secondary sources only** | V-10: primary ZATCA and CHI/NPHIES sources |
| **A-10 — hosting cost and data residency** | **UNEVALUATED** | Two hosting quotes and a stated residency position before anything about hosting is published |

### What remains to be resolved before a final lock can be claimed
1. Complete the 9 unverified competitor dossiers and re-run the frequency tables (Source A §24.2).
2. Conduct the Arabic-language competitor review.
3. Run V-1, V-2, V-3 and V-10 — the four tests that validate the wedge itself.
4. Complete Phase 1 and Phase 2 (demo preparation and operational readiness).
5. Produce the cost model and price band from real transactions.

### Completion test — all 28 required decisions present
Primary target market §4 · ICP §5 · personas §6 · category §7 · primary problem §8 · value proposition §9 · differentiators §10 · value pillars §11 · positioning statement §12 · messaging hierarchy §13 · safe claims §14 · pricing/packaging §15 · demo strategy §16 · website strategy §17 · sales motion §18 · channels §19 · funnel §20 · trust/proof §21 · objections §22 · competitive strategy §23 · product-vs-service §24 · launch sequence §25 · product gaps §26 · assumptions §27 · validation §28 · metrics §29 · risks §30 · locked-decision register §32 · traceability §33.

---

## 36. THE STRATEGY IN 15 LINES

1. **We sell to** privately owned, predominantly self-pay outpatient clinics and small medical centres in Saudi Arabia with 3–15 providers, starting with ophthalmology.
2. **Our primary ICP is** a single-site clinic on paper or an abandoned system, with no in-house IT, whose invoicing and any insurance claiming already happen somewhere else and will stay there.
3. **The economic buyer is** the owner — usually a practising physician — championed by the clinic manager, with an external IT contractor holding the veto.
4. **We compete in the category** outpatient clinic management and EMR, implemented, hosted and supported. Never "HIS", never "multi-tenant SaaS".
5. **Their primary problem is** that they have lost control of their clinical record: everyone sees everything, nothing is traceable, and the data is hostage to a supplier.
6. **Our core value proposition is** your clinic's records, properly run — access you control, actions you can prove, data that stays yours — set up, hosted and supported for you.
7. **Our main differentiators are** claims published with their limitations attached on inspectable software; data ownership and a documented exit; roles and audit integrity demonstrated live rather than asserted; and configuration you can do without paying us.
8. **Our value pillars are** know who did what · your records stay yours · fits how your clinic actually works · no surprises.
9. **Our positioning is** the clinic system you can actually verify — the only one in this comparison whose claims a buyer can check independently.
10. **Our pricing approach is** a one-off implementation fee plus an annual managed subscription per location banded by provider count, published openly — with the figures themselves BLOCKED until three real transactions produce them.
11. **Our primary conversion motion is** founder-led and demo-led, converting through a written scope with published exclusions into a **paid** 60–90 day design-partner pilot.
12. **Our demo strategy is** to open with the audit-integrity verification and the permission model — both live today, both marketed by essentially nobody — then build a form in front of them, and add the seeded reception-to-physician journey after 1–3 days of data work.
13. **Our website strategy is** bilingual, disclosure-led, with four flagship pages that are the four differentiators, a published register of everything switched off or absent, and one CTA: book a walkthrough.
14. **The most important product gaps are** demo data and operational hardening (P0, days), then ZATCA/VAT which is a schema change, Arabic completion, MFA enforcement, and NPHIES — which is a core-patching programme and the gate to the rest of the market.
15. **The next execution phase is** Phase 1 demo preparation and the four validation tests that confirm the self-pay wedge is real — before a single line of website copy is written.

---

*Prepared against `HISModulesUsers.md` (2026-08-09, HEAD `631f2b38c`, schema 541) and `OPENEMR_MARKETING_COMPETITOR_INTELLIGENCE.md` (2026-08-11, 26 competitors, 17 verified). 32 marketing claims and their mandatory qualifications carried through unchanged. No capability, customer, statistic, price or compliance status is asserted anywhere in this document that is not traceable to those sources or explicitly labelled as an assumption.*
