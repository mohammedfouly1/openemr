# EV-034 — §34 WEBSITE PROOF-READINESS MATRIX RESYNC

**Requirement:** none — this closes no RDY item · **Gates:** none recalculated (§0.0 Rule 3)
**Produced:** 2026-08-19 · Orchestrator (main session), PB-480
**Subject:** `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` §34, and the three
summaries derived from it — §41.2, §42.1, §43

---

## 0. What this is, and what it deliberately is not

**It is a staleness correction, not a closure.** §34 is the contract between product readiness
and the future marketing website. Its cells were written at **Phase 2A (2026-08-13)** and were
never refreshed as the 2026-08-19 closure wave landed. The result was a matrix that asserted, as
present-tense fact, three things that had already stopped being true:

- *"no capture exists"* — on 8 rows
- *"no recording exists"* — on the flagship audit-integrity row
- *"they exist only inside the audit"* — on the four status-register rows

**No RDY status was changed by this pass. No gate count was recalculated. No capture, recording
or document was produced.** Every correction below points at an artefact another agent already
produced and evidenced, or at a register row another agent already closed.

This document exists because §0.0 Rule 2 prefers a new file to a long edit, and because the §34
edit needs a derivation someone can re-run rather than trust.

---

## 1. Method

Two independent passes, both re-runnable:

1. **Artefact existence** — directory listing and byte sizes for every proof asset §34 cites.
2. **Register truth** — for every RDY ID cited anywhere in §34, read that ID's own register row
   in §7 and record whether its final verdict contains `CLOSED`. Gate membership and priority
   read from the same row. **Never inferred from prose** (§47 rule 7).

Cross-check: the set of still-open P0 items derived in pass 2 must reconcile with §1.4's
per-gate figures. It does — see §4.

---

## 2. Pass 1 — the artefacts exist

```
ls -l docs/evidence/captures/2026-08-19/publication-ready/
ls -l docs/evidence/captures/2026-08-19/RDY-0062-audit-log-tamper-refresh-recording.gif
```

**Result — 13 files, all twelve SS captures plus the SS-06 retake:**

| Asset | File | Bytes |
|---|---|---:|
| SS-01 | `SS-01-audit-log-tamper-report.jpg` | 85,478 |
| SS-02 | `SS-02-acl-administration-matrix.jpg` | 159,992 |
| SS-03 | `SS-03-front-office-visit-history-permission-boundary-SYN-0006.png` | 271,075 |
| SS-04 | `SS-04-physician-visit-history-SYN-0006-retake.png` | 584,555 |
| SS-05 | `SS-05-layout-editor-draft-marker-fully-visible.png` | 867,022 |
| SS-06 | `SS-06-calendar-current-week-two-providers-20260819.png` | 508,075 |
| SS-06 *(retake)* | `SS-06-calendar-current-week-two-providers-20260819-retake.png` | 891,921 |
| SS-07 | `SS-07-flow-board-today-mixed-statuses-20260819.png` | 795,809 |
| SS-08 | `SS-08-completed-soap-note-SYN-0006-retake.png` | 788,985 |
| SS-09 | `SS-09-ophthalmology-retina-panel-encounter-23-retake.png` | 704,366 |
| SS-10 | `SS-10-patient-ledger-csv-control.jpg` | 266,206 |
| SS-11 | `SS-11-exported-csv-open-in-spreadsheet.png` | 171,228 |
| SS-12 | `SS-12-arabic-rtl-demographics-direct-navigation.png` | 960,517 |
| **EV-062 recording** | `RDY-0062-audit-log-tamper-refresh-recording.gif` | **170,671** |

The recording is the one §34 called *"no recording exists"*. Per PB-439 it was produced in an
isolated single-tab session as `n.alqahtani` (Administrator, **not** `admin`), captures the
interactive `[Refresh]` action itself, and the run returned clean twice.

**Register artefact, same check:** `docs/evidence/EV-067-published-registers.md` exists, carries
all four registers (47 Disabled / 27 Uninstalled / 18 Requires-Integration / 60 Missing) with the
derivation command printed, and passed claim review APPROVED FOR PUBLICATION at PB-371.

---

## 3. Pass 2 — register truth for every RDY §34 cites

Command shape used per ID:

```
grep -m1 "^| \*\*<NNNN>\*\*" docs/Marketing-MVP-and-Launch-Readiness-Requirements.md
```

| RDY | Pri | Verdict | Effect on §34 |
|---|---|---|---|
| 0020–0028 | P0 | **CLOSED** | Removes "no data" from every clinical/scheduling/reporting row |
| 0032, 0033, 0034 | P0 | **CLOSED** | Removes "stock branding / vendor links" from the hero and flagship rows |
| 0038, 0042, 0043 | P0 | **CLOSED** | Removes the locale-seed and menu-defect blockers |
| 0041 | P0 | **CLOSED** (PB-476) | Demo page: D-7 acceptance met |
| 0050, 0051 | P0 | **CLOSED** (2026-08-13, 127/127 PASS) | **Removes "blocked by a defect" from Roles & Permissions** |
| 0016 | P0 | **CLOSED** (PB-477) | Authorization matrix 32/32 |
| 0056, 0057 | P0 | **CLOSED** (PB-372) | Claim-discipline records exist |
| 0058, 0059 | P0 | **CLOSED** | Reports non-empty; CSV opens |
| **0060, 0061** | P0 | **CLOSED** (PB-471) | **Kills "no capture exists" on 8 rows** |
| **0062** | P0 | **CLOSED** (PB-439) | **Kills "no recording exists"** |
| 0064 | P0 | **CLOSED** (PB-301) | Hosting residency decided — Dammam / `me-central2` |
| **0067** | P0 | **CLOSED** (PB-371) | **Kills "they exist only inside the audit" on 4 rows** |
| 0071 | P0 | **CLOSED** (PB-407) | Export procedure written and exercised |
| 0088 | P0 | **CLOSED** (PB-372) | **The hold is recorded and review-passed. The prohibition is NOT lifted** — §32 item 26 stands |
| 0095 | P0 | **CLOSED** (PB-301) | About page attribution determined |
| **0047** | P0 | **OPEN** | Implementation page stays model-only |
| **0065** | P0 | **OPEN** | Resources page: buyer's checklist missing |
| **0069** | P0 | **OPEN** | Pricing figures stay BLOCKED (PRC-003) |
| **0073** | P0 | **OPEN** | *Your data, your exit* stays Partial — the termination procedure is what O-3 promises is published before signature |
| **0085** | P0 | **OPEN** | No TLS/domain ⇒ no service-level hosting statement |
| 0079 | **P1** | OPEN | Ophthalmology beachhead validation — **not** a proof blocker |
| 0093 | **P1** | OPEN | No WhatsApp channel ⇒ no WhatsApp CTA |
| 0101 | **P1** | OPEN | Two-or-three-locations stays qualified-text-only |

---

## 4. Reconciliation against §1.4

Pass 2 yields exactly five still-open P0 items among those §34 cites: **0047, 0065, 0069, 0073,
0085.** Adding **0004** (prohibited-claim packaging, which §34 does not cite but which blocks
G0/G4/G5/G6) and the three unrun validations **0075/0076/0077** gives **9 open P0** — matching
§1.4's stated total and its per-gate line `G0 1 · G1 0 · G2 0 · G3 4 · G4 1 · G5 1 · G6 9`.

**The two derivations agree.** That is the check that makes this resync safe to apply.

---

## 5. What changed in the document

| Section | Change |
|---|---|
| §0.0 Rule 1 | PB-480…489 range claimed |
| **§34** | Correction banner added; matrix rewritten with current availability, current blockers, and a net-position line. The `After Phase 2?` column was dropped — it asked a question that 2026-08-19 has answered |
| §41.2 | Screenshot-inventory row struck through and marked RESOLVED |
| §42.1 | Four Phase-4 package rows moved MISSING → READY/PARTIAL with their artefacts named |
| §43 | Page-class summary replaced; superseded counts retained struck-through; §43.1 preconditions given a status column |
| §43.1 | **Verdict unchanged — G6 NOT READY** |

---

## 6. The honest reading

Before this resync, §34 said the website was blocked on **proof**. It is not, and has not been
since 2026-08-19. **21 of 30 page rows are ready on evidence that exists on disk today.**

What the website is actually blocked on is **G3 operational readiness** — a deployment runbook, a
termination procedure, TLS and a domain — plus two commercial documents and one unrun set of
validation interviews. That is a materially different problem from the one the stale matrix
described, and it changes what should be worked on next.

**It does not make G6 ready.** GTM §25 Phase 3's risk is explicit and unchanged: *publishing
before operational readiness creates demand we cannot safely serve.* The correction moves the
bottleneck; it does not remove it.

---

## 7. Limits of this pass, stated

- **No artefact was opened and visually assessed.** Existence and byte size were verified; the
  independent content review of the twelve captures is PB-471's, not this pass's.
- **No RDY row's own acceptance criteria were re-executed.** Closure states were read, not
  re-proven.
- **No gate count was recalculated** (Rule 3). If a later sync finds §1.4 and §47 disagree with
  §4 above, that sync is authoritative and this file should be corrected, not defended.
