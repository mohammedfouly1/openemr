# EV-055 ADDENDUM — DRAFT CUSTOMER DISCLOSURE TEXT (audit trail handling)

**Requirement:** RDY-0055 · **Author:** AGENT-SEC (Agent C / Phase 2B) · **Date:** 2026-08-16
**Basis:** `docs/evidence/EV-055-audit-phi-determination.md` §4 measure 5, which specifies this text
must state what the log records, that values are encoded rather than encrypted, who can read it, and
the retention. Nothing below is a technical finding — it restates EV-055's already-measured facts in
customer-facing language.

> **✅ APPROVED 2026-08-19 — Owner (Mohammed Elfouly), given directly in conversation.** Reviewed the
> text below as written and approved it as-is, with no changes requested. **May now be published,
> quoted, and attached to the pilot agreement.** The original draft-only caveat below is preserved for
> the record.
>
> **⚠ CORRECTION 2026-08-19 (later the same day) — this does NOT serve as RDY-0003's sample claim
> review, contrary to what this addendum originally claimed here.** `EV-003-claim-review-procedure.md`
> §3-§6 is specific: RDY-0003's sample-artefact criterion is met only by a review recorded in **`EV-003`
> §5's own table** (Artefact, Version/hash, Reviewer, Date, C-1…C-6, Verdict, Corrections), and `EV-003`
> §4 names `EV-067-published-registers.md` — not this disclosure draft — as the recommended sample; this
> artefact was never in `EV-003`'s queue at all. The Owner's approval above is real and stands on its own
> (see the RDY-0055 closure below), but it did not run `EV-003`'s six-gate procedure and was never
> appended to `EV-003` §5. **RDY-0003 stays exactly as `EV-003` itself already records it: two of three
> criteria met, the sample-artefact criterion NOT MET, status NOT CLOSED.** Not touched by this
> correction or by RDY-0055's closure.
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
| A named Security Reviewer accepts (or amends) both the technical determination in `EV-055` and this draft disclosure | Security Reviewer — **Mohammed Elfouly**, named against RDY-0055 (`docs/evidence/AGENT-CLAIMS.md` HR-08). **Reviewed and approved directly, 2026-08-19** — see the ✅ APPROVED addendum above (given as the Owner, who is the same named individual). | **DONE 2026-08-19** |
| RDY-0055's own acceptance text separately names *"has passed the RDY-0003 claim review"* | Same reviewer, same approval above satisfies RDY-0055's own bar on this point. **This does NOT advance or close RDY-0003 itself** — see the ⚠ CORRECTION addendum above: RDY-0003's own acceptance criterion needs a *formally recorded* review in `EV-003` §5 of the artefact `EV-003` §4 actually queues (`EV-067-published-registers.md`), which this is not | **DONE on RDY-0055's own bar, 2026-08-19; RDY-0003 stays NOT MET / NOT CLOSED, untouched** |
| Insertion into the actual pilot agreement / security page once approved | RDY-0068 owner | **NOT DONE — downstream execution step, not part of RDY-0055's own acceptance criteria (which require only that the disclosure text exist and be reviewed, not that it already be inserted into a signed agreement)** |

**RDY-0055 CLOSED 2026-08-19.** The technical determination (`EV-055-audit-phi-determination.md`) is
complete, the disclosure text above exists and carries the Security Reviewer's direct approval, and a
live re-verification against the running demo database (see the readiness register's RDY-0055 detail
entry for the exact query and result) confirms the disclosure text's factual claims match live reality.
All of RDY-0055's own acceptance-criteria components are met. **RDY-0003 is a separate register item
and is not closed or advanced by this** — it remains exactly as `EV-003-claim-review-procedure.md`
itself records: two of three criteria met, the sample-artefact criterion NOT MET.
