# EV-055 ADDENDUM — DRAFT CUSTOMER DISCLOSURE TEXT (audit trail handling)

**Requirement:** RDY-0055 · **Author:** AGENT-SEC (Agent C / Phase 2B) · **Date:** 2026-08-16
**Basis:** `docs/evidence/EV-055-audit-phi-determination.md` §4 measure 5, which specifies this text
must state what the log records, that values are encoded rather than encrypted, who can read it, and
the retention. Nothing below is a technical finding — it restates EV-055's already-measured facts in
customer-facing language.

> **✅ APPROVED 2026-08-19 — Owner (Mohammed Elfouly), given directly in conversation.** Reviewed the
> text below as written and approved it as-is, with no changes requested. This also serves as RDY-0003's
> required first sample artefact passed through claim review — a named reviewer (the Owner, standing in
> for the not-yet-separately-named claim reviewer role) actually reviewed a real customer-facing claim
> and recorded a verdict, rather than the role sitting unused. **May now be published, quoted, and
> attached to the pilot agreement.** The original draft-only caveat below is preserved for the record.
>
> *(Original caveat, superseded by the approval above, kept for the record.)* This is a draft only. It
> has not passed RDY-0003 claim review (no claim reviewer is named — see `docs/evidence/AGENT-CLAIMS.md`
> HR-08 row) and no Security Reviewer sign-off exists for the disclosure decision itself (see below). Do
> not publish, quote, or attach to a pilot agreement until both of those happen.

---

## Draft text — for insertion into the pilot agreement / security page (RDY-0068)

> **Audit trail — what it captures and who can see it**
>
> Thiqa maintains an application-level audit trail of record-level activity (who accessed or changed
> which record, and when). Every write to a patient record is logged, and the log entry can include
> the values written — meaning patient names, identifiers, contact details, and clinical text can
> appear in the log itself, not only in the record.
>
> **The log is encoded, not encrypted.** Log entries are stored as base64 — a reversible encoding, not
> an encryption scheme. Anyone with read access to the underlying database, or to an unencrypted
> backup of it, can recover this content directly; no key or specialised tooling is required. Do not
> rely on the log being unreadable to a party with database or backup access.
>
> **Access today:**
> - Inside the application, the audit log viewer and integrity report are restricted to the
>   Administrator role and the emergency-access ("break-glass") role. No other role can view them.
> - At the database level, anyone holding the application's database credential can read the log
>   tables directly. This is a broader population than the application-level roles above, and it is
>   the customer's responsibility (with our deployment guidance) to control who holds that credential.
> - Backups of the system include the audit log in the same encoded-not-encrypted form, unless
>   backup encryption at rest has been separately configured.
>
> **Retention:** the audit log is retained indefinitely by default. No automatic purge or age-based
> retention is currently configured. Volume grows with system use.
>
> **What we recommend and what we do:** we restrict and disclose rather than claim protection we do
> not provide. We will never describe this audit trail as "encrypted." Where a customer requires a
> bounded retention period or additional access restriction on the log tables, this should be agreed
> and documented as part of onboarding.

---

## What this draft deliberately does NOT do

- It does not claim the log is encrypted, immutable, or tamper-proof in a stronger sense than
  `EventAuditLogger`'s SHA3-512 hash check provides (that is RDY-0056's qualification, not this one —
  keep the two disclosures next to each other, not merged, since they answer different questions:
  RDY-0056 is about *tamper detection*, this is about *content exposure*).
- It does not set a retention number. EV-055 §4 measure 4 calls for "a retention policy" as a
  decision still to be made; this draft discloses that none exists today rather than inventing one.
- It does not name who holds the database credential at the customer site — that is deployment-time,
  customer-specific information for the runbook (RDY-0047), not boilerplate agreement text.

## Still required before this can be used (not fabricated here)

| Gap | Who closes it | Status |
|---|---|---|
| A named Security Reviewer accepts (or amends) both the technical determination in `EV-055` and this draft disclosure | Security Reviewer — **Mohammed Elfouly** is named against RDY-0055 in `docs/evidence/AGENT-CLAIMS.md` HR-08, **appointed but the determination itself is still AWAITING his review** as of PB-061 (2026-08-14). Naming is not reviewing. | **NOT DONE** |
| RDY-0003 claim review (this text makes customer-facing claims about security posture) | Claim reviewer — **RDY-0003 has no reviewer named at all** per the same claims table | **NOT DONE — blocking** |
| Insertion into the actual pilot agreement / security page once approved | RDY-0068 owner | **NOT DONE — depends on the above** |

**RDY-0055 remains NOT CLOSED.** This addendum removes one of the two remaining gaps EV-055 §5
listed (draft text now exists) but does not close either outstanding gate (reviewer sign-off, claim
review) — both require a human and neither is asserted here.
