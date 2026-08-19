# EV-078 — REGULATORY PRIMARY-SOURCE VERIFICATION (V-10)

**Requirement:** RDY-0078 · **Gate:** G5 G6 · **Owner:** Founder / Product Owner
**Acceptance:** *"A dated record citing primary sources for both EXT-01 and EXT-02, stating whether
each is confirmed, refined or contradicted as applied to small private clinics."*
**Date accessed:** 2026-08-19 · **Method:** web search + fetch against official government domains,
not secondary/vendor commentary. No product-compliance claim is made anywhere in this document —
per the item's own instruction, this is regulatory-environment research, not a capability claim.

---

## 0. What this settles, and what it does not

This closes RDY-0078's specific acceptance bar: primary sources, dated, for EXT-01 and EXT-02. It
does **not** mean the product is compliant with either regime — it is not, and no downstream artefact
may say otherwise (§32 item 12). It narrows what is *true about the Saudi regulatory environment* the
locked ICP (self-pay outpatient clinics) sits inside.

## 1. EXT-01 — ZATCA e-invoicing (Phase 2 "Integration")

**Primary source:** ZATCA's own site, `zatca.gov.sa` — "ZATCA Determines the Criteria for Selecting
the Targeted Taxpayers in Wave 24 for 'Integration Phase' of E-invoicing"
(`https://zatca.gov.sa/en/Pages/news_1426.aspx`), cross-confirmed against ZATCA's own
Roll-out-phases and FAQ pages under `zatca.gov.sa/en/E-Invoicing/`. Accessed 2026-08-19.

**What it states:** Phase 2 ("Integration") requires a taxpayer's e-invoicing solution to connect
directly to ZATCA's Fatoora platform, issuing UBL 2.1 XML e-invoices (or PDF/A-3 with embedded XML).
Wave 24 — the most recent wave at time of access — covers every taxpayer whose VAT-liable revenue
exceeded **SAR 375,000** during 2022, 2023 or 2024, with a compliance deadline of **30 June 2026**.
ZATCA directly notifies targeted taxpayers at least six months ahead of their integration date.

**As applied to the locked ICP (small, self-pay, private Saudi outpatient clinics):** **CONFIRMED as
applying, in general.** This is a VAT/tax obligation tied to business revenue, not to insurance
participation or clinical scope — a purely self-pay clinic is not exempted by being self-pay. A
clinic's actual obligation (which wave, which deadline) depends on its own VAT-liable revenue history,
which this document cannot determine for a hypothetical prospect. **The prohibited claim remains
prohibited regardless**: the product has zero ZATCA/e-invoicing capability (§32 item 12), and this
finding does not change that — it only confirms the *regulatory backdrop* a prospect operates under is
real, not merely vendor-commentary conjecture.

## 2. EXT-02 — NPHIES / CHI provider obligation

**Primary sources:** the CHI (Council of Health Insurance, formerly CCHI) government site,
`chi.gov.sa` — including its knowledge-center circulars (e.g. `chi.gov.sa/en/knowledge-center/
resolutionscirculars/`) — and the NPHIES platform's own implementation-guide portal,
`portal.nphies.sa`. Accessed 2026-08-19.

**What they state:** NPHIES is the CHI/CCHI-regulated national platform for **insurance eligibility,
pre-authorization and claims exchange** between healthcare providers and payers. Connectivity is
required for providers who transact with insurance payers through it; new facilities generally need a
connectivity plan as part of licensing. The obligation is framed throughout CHI/NPHIES's own material
around **insurance transactions** — payer-provider exchange — not around clinical encounters in
general.

**As applied to the locked ICP:** **REFINED, not flatly confirmed.** The GTM's ICP is explicitly
**self-pay** outpatient clinics (A-02) — a clinic that never bills an insurance payer has no NPHIES
*transaction* to make, even though NPHIES connectivity may still be a general licensing expectation
independent of a given clinic's payer mix. This is a real distinction the secondary/vendor commentary
sources reviewed earlier did not draw clearly: "NPHIES is mandatory for every provider" (a common
vendor-blog framing) overstates it for a pure self-pay operator, while "no obligation at all" would
understate it if licensing itself expects a connectivity plan regardless of payer mix. **Neither
extreme is asserted here** — the honest position is that the obligation's *weight* depends on whether
and how much insurance business a given prospect actually does, which this document cannot determine
in the abstract. **This does not change the product's own capability status**: NPHIES/claims remains
Missing (§32 item 12, RDY-0100), and no claim of NPHIES capability or compliance may be made regardless
of a prospect's own obligation level.

## 3. Acceptance

| Criterion | Result |
|---|---|
| Primary source cited for EXT-01, with access date | **MET** — `zatca.gov.sa`, 2026-08-19 |
| Primary source cited for EXT-02, with access date | **MET** — `chi.gov.sa` / `portal.nphies.sa`, 2026-08-19 |
| States whether each is confirmed, refined or contradicted, as applied to small private clinics | **MET** — EXT-01 confirmed as generally applying (revenue-dependent); EXT-02 refined (payer-mix-dependent), neither overstated nor dismissed |
| Regulatory research not converted into a compliance claim | **MET** — no claim of product compliance appears anywhere in this document |

### Status: **RDY-0078 — CLOSED 2026-08-19.**

**What this does not do:** determine any specific prospect's actual obligation (that needs their own
revenue/payer-mix facts, not available here); replace the "ideally confirm with two clinic finance
managers" suggestion in the item's own card, which remains a nice-to-have that was never a hard
acceptance criterion; or change any product-capability status — §32 item 12's prohibition list is
unaffected and unaffectable by this finding.

**`Blocks`:** G5 G6. Gate-count decrement recorded in the main readiness document's next PB sync,
per §0.0 Rule 3 — not recalculated here.
