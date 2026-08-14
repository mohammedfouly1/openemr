# EV-004 — PROHIBITED-CLAIM CONTROL: BINDING INPUT TO PHASES 3, 4 AND 5

**Requirement:** RDY-0004 · **Gates:** G0, G4, G5, G6 · **Owner:** Product Marketing
**Acceptance:** *"Each downstream phase brief contains §32 verbatim, and each names the reviewer from
RDY-0003."*
**Issued:** 2026-08-14 · **Agent B**, Phase 2B

---

## 1. The binding list — and why it is not copied into this file

**The binding control list is §32 of `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md`:
26 prohibited claim categories and the "pages that must not exist" list.**

**It is deliberately not duplicated here.** A second copy of a 26-row control list is how two
versions come to exist, and the moment they differ the weaker one gets cited. §32 is already
complete — each row carries its prohibition, its reason, its source ID, and its separate Phase 3 /
Phase 4 / Phase 5 impact.

**So this document is the *adoption instrument*, not a second copy.** Its job is to make §32 binding
downstream, name who enforces it, and record that each phase received it.

### 1.1 How a phase brief embeds it

1. **Copy §32 verbatim** — the full table plus the *pages that must not exist* list — into the phase
   brief.
2. **Record the source and the version you copied** in §4's adoption record, so a later reader can
   tell whether the brief carries a current copy.
3. **Do not summarise, reorder, or drop the "why" and "source" columns.** A prohibition without its
   reason gets argued with; a prohibition with `GAP-0046` beside it does not.

### 1.2 Verify you have the current version before embedding

```bash
cd "G:/My Drive/OpenEMR"
awk '/^## 32\. Prohibited Claims/,/^---$/' docs/Marketing-MVP-and-Launch-Readiness-Requirements.md \
  | sha256sum
# Record the hash in the adoption record. If it differs from the last adoption, §32 has changed
# and every downstream brief needs re-issuing.
```

---

## 2. The enforcing reviewer

| Field | Value |
|---|---|
| **Claim reviewer (RDY-0003)** | **Mohammed Elfouly** |
| Appointed | 2026-08-14 by the Owner (PB-077) |
| Review procedure | `docs/evidence/EV-003-claim-review-procedure.md` |
| Register entry | HR-04, **HR-06** |

**Every Phase 3, 4 and 5 brief must name him**, and every artefact those phases produce passes
through the `EV-003` review step before use. **A binding register with nobody enforcing it is
advisory** — GTM R-02 rates drift back to prohibited language as **High**.

---

## 3. What each phase must carry

| Phase | Must carry | The prohibitions that bite hardest |
|---|---|---|
| **Phase 3 — Brand & product identity** | §32 verbatim + the reviewer's name | **#1** no brand cue implying hospital scale · **#2** the name and tagline must not imply "HIS" · **#14** no certification badge or visual · **#15** the brand must not imply proprietary software — **fork divergence is zero** |
| **Phase 4 — Messaging & claims** | §32 verbatim + the reviewer's name + the mandatory qualifications from RDY-0056/0057/0088 | **#16** tamper-evident, never "immutable"/"blockchain" · **#17** MFA is voluntary and **cannot be mandated** · **#18** Arabic is 47.5 % chrome-only · **#24** the banned adjectives · **#25** no manufactured trust — **no uptime figure has ever been measured** · **#26** publish the mechanism, not the number |
| **Phase 5 — Website PRD** | §32 verbatim + the reviewer's name + the **pages that must not exist** list | Every prohibited page: compliance · NPHIES/ZATCA/Saudi-readiness · analytics/BI · mobile · inpatient · ERP · LIS/RIS/PACS · dental · multi-tenant SaaS · certifications · customer logos · **a "customers" page with no customers** |

**#23 binds all three and has no exceptions: the `admin` credential must never appear in any
material, ever** — not in a screenshot, not in a recording, not in a walkthrough.

---

## 4. Adoption record — one row per phase, completed when the brief is issued

**No phase brief may be issued without its row.**

| Phase | §32 embedded verbatim? | §32 version (sha256) | Reviewer named in the brief? | Issued by | Date | Accepted by phase owner |
|---|---|---|---|---|---|---|
| **Phase 3 — Brand** | ☐ | | ☐ | | | |
| **Phase 4 — Messaging** | ☐ | | ☐ | | | |
| **Phase 5 — Website PRD** | ☐ | | ☐ | | | |

**Nothing is pre-ticked.**

---

## 5. Acceptance

| Criterion | Result |
|---|---|
| The control list is packaged as a standalone binding downstream input | **MET** — this document, with §32 incorporated by reference and an anti-drift verification step |
| It names the reviewer from RDY-0003 | **MET** — Mohammed Elfouly, §2 |
| **Each downstream phase brief contains §32 verbatim** | **NOT MET — no phase brief exists yet.** §4 is empty |
| Each brief names the reviewer | **NOT MET** — same reason |

### Status: **RDY-0004 — NOT CLOSED.** The instrument exists; the briefs it binds do not.

**This is a genuine sequencing constraint, not a gap in the work.** RDY-0004's acceptance is written
in terms of artefacts that Phases 3, 4 and 5 produce, and **those phases have not started** — G4 is
blocked on RDY-0095 and G5 on proof assets. **RDY-0004 closes on the day the three briefs are issued
carrying §32 and Mr Elfouly's name**, and §4 is the form that records it.

**One dependency now satisfied:** RDY-0004's card lists **RDY-0003** as its dependency, and the
reviewer it needed is named as of PB-077. **The blocker has moved from "nobody to name" to "nothing
yet to bind".**

**`Blocks`: G0 G4 G5 G6.** No gate count moved (§0.0 Rule 3).
