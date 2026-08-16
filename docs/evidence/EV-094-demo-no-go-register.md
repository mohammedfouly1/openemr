# EV-094 — DEMO NO-GO REGISTER: ADOPTED, WITH THE DICOM CLASSIFICATION RESOLVED

**Requirement:** RDY-0094 · **Gates:** G1 G2 · **Deps:** RDY-0080, 0083 · **Owner:** Founder / Product Owner
**Acceptance:** *"The register exists; the presenter has rehearsed each no-go response aloud; the D-7
rehearsal (RDY-0041) does not touch any registered item; each item has a recorded unlock condition."*
**Issued:** 2026-08-16 · **AGENT-DOC**, Phase 2B

---

## 1. The register already exists, and is current

`§40 Demo No-Go Register` of the readiness document is the artefact this requirement asks for. It is
not a draft — it carries **13 rows**, each with reason, current status, unlock condition and the exact
spoken line, and it has been **actively maintained through 2026-08-16** (row 12, background services,
was corrected twice today — PB-142, PB-181 — to reflect a live regression found and diagnosed by
AGENT-OPS). **This document adopts §40 as RDY-0094's register rather than re-authoring a parallel one**
— duplicating it would immediately create a second copy to keep in sync.

## 2. The DICOM classification — resolved, and it is not a no-go item

Checked because the orchestrating session's task briefing named a "DICOM viewing inventory" as
in-scope. It resolves to an existing classification already in the document, not a missing one:

| Source | Finding |
|---|---|
| §32 capability interaction table | `DICOM — Active — Mentionable, qualified — (no PACS) — Viewing only, no PACS` |
| §32 row (MC-24) | *"C-CDA, CCR, HL7 v2, DICOM… Each needs a receiving counterparty… **DICOM is viewing only, no PACS. Never demonstrate C-CDA**"* |

**DICOM viewing is `Active` and `Mentionable, qualified` — it is explicitly distinguished from C-CDA
(which is the item actually on the no-go list, §40 row 2, because nothing listens on 6661).** DICOM
does not appear as its own no-go row because it is not blocked the way C-CDA is: it can be mentioned
with the stated qualification ("viewing only, no PACS") rather than avoided entirely. **No new no-go
row is added for it** — adding one would misrepresent a demonstrable, qualified capability as a
blocked one. It is, however, correctly swept into §40 row 3 ("Any external integration… every one
needs a third-party contract") for the specific claim that DICOM *receives* from a PACS, which it does
not do without one.

## 3. Acceptance, checked against §40 as it stands today

| Criterion | Result |
|---|---|
| **The register exists** | **MET** — §40, 13 rows, current as of 2026-08-16 |
| **Each item has a recorded unlock condition** | **MET** — §40's "Unlock condition" column is populated for all 13 rows, including the honest **"Never"** entries (rows 3–4, 7–9, 11, 13) where no unlock is expected under the locked MVP scope |
| **The presenter has rehearsed each no-go response aloud** | **NOT MET** — no rehearsal has been logged anywhere in the document. This requires a named human presenter and a witnessed rehearsal; it is not something this session can perform or simulate |
| **The D-7 rehearsal (RDY-0041) does not touch any registered item** | **NOT MET / NOT REACHABLE YET** — RDY-0041 itself has not run (§7.6: *"Not attempted"*). This criterion can only be checked once a D-7 run exists to check it against |

## 4. What remains, precisely

| Item | What is needed | Who |
|---|---|---|
| Rehearsal of all 13 spoken lines, aloud, witnessed | A named presenter reads each line in §40's last column and a second person confirms it was said before the corresponding screen, not after | Founder / Product Owner (per the item's own `Owner` field) |
| Cross-check against a completed D-7 run | Once RDY-0041 executes, the run log is checked against §40's 13 rows for any touch | Whoever executes RDY-0041 |

**Neither of these can be produced by writing more documentation.** They are the two human/execution
steps the register itself cannot substitute for, and this artefact does not pretend otherwise.

## 5. Status

### Status: **RDY-0094 — NOT CLOSED.** Two of four criteria are met (register exists; every item has
a recorded unlock condition, including honest "Never" entries). The remaining two require a witnessed
human rehearsal and a completed D-7 run, neither of which exists yet. **The DICOM question the task
briefing raised is resolved** — DICOM viewing is correctly classified `Active/Mentionable, qualified`
and does not belong on the no-go list; C-CDA is the item that does, and it already has its row (§40
row 2).

**`Blocks`:** G1 G2. No gate count moved (§0.0 Rule 3).
