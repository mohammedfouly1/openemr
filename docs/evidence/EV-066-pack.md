# EV-066 — SCOPE & EXCLUSIONS TEMPLATE (customer-facing document)

**Requirement:** RDY-0066 · **Gates:** G3, G6 · **Deps:** RDY-0067 · **Owner:** Sales / Pilot Owner
**Acceptance:** *"The template names invoicing/VAT/ZATCA and insurance claims/NPHIES as excluded, in
customer-facing language; it requires a signed scope acknowledgement; the P-4 finance conversation
is a required step before signature."* **Verification:** legal/compliance review recorded.
**Issued:** 2026-08-16 · **AGENT-COMMERCIAL**, Phase 2B — **CONTINUATION**

**Builds on `EV-065-066-069-commercial-artefacts.md` Part B (Agent B, 2026-08-14), which already
established the exclusion wording, the two required pre-signature steps, and the cross-reference
discipline. That content is the source of truth for the exclusion language and is reproduced here
only as far as needed to make this an actual attachable template — not restated as a separate
requirements analysis.** The delta this pack adds: the template rendered as a fillable document with
a named-clinic header, an explicit integration-boundary clause, an explicit migration-boundary
clause, a data-exit clause that points to `EV-073` by reference rather than restating it, an
attachment list that points to `EV-067`'s four registers by reference rather than restating them,
and the two signature/record blocks (B-1, B-2) as usable forms rather than a requirement statement.

---

## TEMPLATE — begins below this line

*(For attachment to the pilot/services agreement. Fill in the bracketed fields. Do not remove any
section — every exclusion below exists because a specific risk (R-06) names it.)*

### Scope of Engagement — [Clinic Name], [Date]

#### 1. What is included

Implementation and configuration · facility and branding setup · role and ACL design · form
building from the 18 active clinical forms · list and layout configuration · fee-schedule setup ·
hosting, patching, backup and support for the term (§9 below) · training at implementation (§9
below) · a documented exit (§5 below).

#### 2. What is excluded — read this section before signing

> **This system does not issue your tax invoice, and it does not submit your insurance claims.**

The following are **excluded, and not deliverable at any price under this engagement**:

- **Invoicing, VAT and ZATCA e-invoicing.** There is no tax field anywhere in the billing chain.
  If you require ZATCA-compliant invoicing, it must come from a separate system you hold.
- **Insurance claims, NPHIES, eligibility checking and pre-authorisation.**
- **Inpatient, beds, theatre, and ancillary systems** — laboratory information, radiology
  information, PACS, dental charting.
- **Analytics, dashboards and KPI reporting.** Fifty-five reports with CSV export are included;
  there is no business-intelligence layer.
- **A patient mobile application.**
- **Enforced multi-factor authentication.** Two-factor is supported and voluntary; it cannot be
  administratively mandated.
- **Migration into another vendor's system on exit.** Exit under this agreement means CSV export
  and full database access (§5) — not a migration service into a named competitor's product.

#### 3. Integration boundary

Any third-party integration — laboratory interface, e-prescribing, payment gateway, fax/SMS
gateway, or any other external service — requires a **contract that you hold directly with that
third party**. We do not hold, negotiate, or guarantee third-party contracts on your behalf.
Integration work against a contract you hold is priced and scheduled as a **separate project**,
after the contract exists — never bundled into this scope, and never estimated before the
third party's own interface is known.

#### 4. Migration boundary

Data migration from a prior system is **quoted after inspection of your source data**, never
fixed-price sight-unseen. The inspection determines the migration hours (tracked as C-3 in the cost
instrumentation, `EV-065-066-069` Part C) and is the basis for the quote you receive — not an
estimate given before we have seen your data.

#### 5. Data-exit clause

If this engagement ends, for any reason, the termination and handover procedure at
**`EV-073-termination-and-handover.md`** governs — steps T-1 through T-9, including the final backup,
the export package (database, report CSVs, documents, manifest, checksums), and delivery. **That
procedure is incorporated into this agreement by reference and is not restated here**, so the two
documents cannot drift apart. You are entitled to request and read `EV-073` in full before signing.

*(Note, recorded honestly rather than concealed: `EV-073` itself flags one open point — the timing
of data and backup deletion after handover (its step T-9) is not yet decided; it depends on a
separate retention policy, RDY-0074, and a Legal decision. That gap exists in `EV-073` independent
of this template and is not resolved by referencing it here.)*

#### 6. Attachments — the four status registers

The following registers are **attached to and form part of this agreement**:
**`EV-067-published-registers.md`** — Active (177) · Disabled (47) · Uninstalled (27) · Requires
Integration (18) · Missing (60). **Read them before signing.** They list, in the vendor's own words,
every capability that is switched off, uninstalled, dependent on a third party, or absent from the
product today. This template does not reproduce their contents — `EV-067` is the source of truth and
is versioned/republished independently whenever a new capability audit is produced.

#### 7. Scope acknowledgement — signature block (required, separate from the contract signature)

| Field | Entry |
|---|---|
| I/we acknowledge having read and understood §2 (exclusions) and §6 (attached registers) | ☐ |
| Clinic representative name | |
| Title | |
| Signature | |
| Date | |
| Our representative (witness) | |

**This acknowledgement exists so that "we thought it did invoicing" cannot survive the first month**
(GTM R-06 mitigation). It is separate from, and precedes, the main contract signature.

#### 8. P-4 finance-conversation record (required before signature)

The finance persona (P-4) must hear the invoicing and claims exclusions from us directly, in their
own meeting — **not from their accountant afterward**.

| Field | Entry |
|---|---|
| Date of finance conversation | |
| Attendees (ours) | |
| Attendees (clinic finance) | |
| Confirmed: heard the invoicing/VAT/ZATCA exclusion (§2) directly | ☐ |
| Confirmed: heard the claims/NPHIES exclusion (§2) directly | ☐ |
| Notes / questions raised | |

#### 9. Support, escalation and training

**Level 1 — Business Hours**, selected by the Owner 2026-08-19 (`EV-096-options.md` ADDENDUM
2026-08-19; the lightest of three tiers that document specifies — see it for the full comparison
against Levels 2 and 3, not restated here beyond what this template needs to stand alone):

| Element | Definition |
|---|---|
| **Support channels** | Email / ticket portal only |
| **Support hours** | Sunday–Thursday, standard clinic business hours (e.g. 09:00–17:00 AST) |
| **Response target** | First response within 1 business day |
| **Escalation path** | Single tier — the implementer handles the ticket; unresolved after [X] days escalates directly to the founder. No after-hours path |
| **Training plan** | One session per role at implementation. No scheduled refresher; further training available at a published day rate |
| **Staffing this implies** | One person, part-time, covering the published window only — recorded so the commitment above is one the team can actually deliver, not a promise made on paper |

**No uptime or availability figure is stated anywhere in this template** — none has been measured
(GTM §15.3; `RDY-0084`'s monitoring-requirements territory, not this one).

### TEMPLATE — ends above this line

---

## Acceptance

| Criterion | Result |
|---|---|
| Names invoicing/VAT/ZATCA and claims/NPHIES as excluded, in customer-facing language | **MET** — §2 |
| Requires a signed scope acknowledgement | **MET** — §7 |
| The P-4 finance conversation is a required step before signature | **MET** — §8 |
| Four status registers attached | **MET** — §6, by reference to `EV-067` (not duplicated) |
| Integration boundary stated | **MET** — §3 |
| Migration boundary stated | **MET** — §4 |
| Data-exit clause present, referencing `EV-073` | **MET** — §5 |
| **Legal/compliance review recorded** | **NOT MET** |

### Status: **RDY-0066 — NOT CLOSED.** The template is complete and attachable; the outstanding gap is
the same one `EV-065-066-069` already recorded: **legal/compliance review has not happened.** This
pack does not, and cannot, supply that review — see `EV-068-073-reviewer-packs.md` for the reviewer
pack format used for the two items that explicitly require one. RDY-0066's own card requires the
same kind of review; a reviewer pack for it can be produced on the same shape if the Owner wants one,
but was not in this session's assigned scope.

**`Blocks`:** G3, G6 (per RDY-0066's own card). No gate count recalculated here (§0.0 Rule 3).
