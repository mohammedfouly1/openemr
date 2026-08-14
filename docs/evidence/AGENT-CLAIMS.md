# AGENT WORK CLAIMS — Phase 2B

**Purpose.** `§0.0` of the readiness document allocates **PB numbers** so two agents never write the
same entry. It does **not** allocate **RDY items**, so two agents can still spend hours closing the
same requirement, or — worse — mutate the same database rows from opposite directions.

**This file is that missing half.** It is deliberately a separate file (`§0.0` Rule 2) so that
claiming work is a low-collision write.

---

## How to use it

1. **Before starting an RDY item, add a row here and commit that row on its own.** One commit,
   one line, no other change. That commit is the claim.
2. If the row already names another agent as `HELD`, **do not start it.** Pick another.
3. When you finish, set the row to `DONE` (or `RELEASED` if you stopped without closing it) in the
   same commit as your PB entry.
4. **A claim is not a reservation forever.** If a row has been `HELD` for a long time and the holder
   is demonstrably gone, release it here with a note rather than silently taking it.

**Claims are cheap; collisions are not.** Claim the whole track if you intend to work the whole track.

---

## Agent identities

| Agent | Identity | PB range (per `§0.0` Rule 1) |
|---|---|---|
| **Agent A** | Claude Code — the session that produced PB-001 … PB-046 and wrote `§0.0` | **PB-001 … PB-069** |
| **Agent B** | Claude Code — the session that produced the independent audit of 2026-08-14 and this file | **PB-070 … PB-139** |

If you are a third agent, add yourself here and claim **PB-140 …** in `§0.0` before your first entry.

---

## Track-level claims

| Track | Scope | Held by | State |
|---|---|---|---|
| **A** — Governance, validation, commercial, licensing | 0002 0003 0004 · 0056 0057 · 0065 0066 0067 0068 0069 · 0071 0073 · 0075–0078 · 0086 0088 · 0094 0096 | **Agent B** | **HELD** |
| **B** — Demo foundation, users, roles, regional, brand surface | 0016 · 0033 0034 · 0042 0043 | **Agent B** | **HELD** |
| **C** — Security & authorization | — | — | *empty — 0055 went to Agent A* |
| **D** — Synthetic data, D-7, proof | 0020–0027 acceptance · 0041 · 0060 0061 0062 | *unclaimed* | OPEN |
| **E** — Operational / pilot readiness | 0047 0048 · 0081 0083 0084 0085 | **Agent B** | **HELD** |
| **F** — Git, upstream, patch currency, provenance | 0045 0046 | **Agent A** | **HELD** — 0046 closed; 0045 in progress (EV-045) |

### ⚠ Claim collision of 2026-08-14, and how it was resolved

**Agent B's opening track claim was too wide and was overtaken within minutes.** Between the claim
commit and Agent B's first read-back, Agent A had already published **PB-048 (RDY-0046)**,
**PB-049 (RDY-0090)**, **PB-050 (RDY-0055)** and **PB-051 (the gate sync)**.

**Agent B released all four rather than duplicating or contesting them.** Agent A's work is
complete, evidenced and first. The table above is corrected accordingly.

**The lesson, recorded so the next agent does not repeat it:** claiming a *whole track* is too
coarse when another agent is already mid-flight in it. **Claim at item level, re-read the PB
headings immediately before starting each item, and treat any item with a published PB entry as
gone** — even if the ledger still shows it free. The ledger is a courtesy; **the committed PB entry
is the fact.**

**Gate counts are Agent A's.** Agent A ran the PB-051 sync and holds Rule 3. **Agent B will not
recalculate gate counts**, and records only which gates a closed RDY's `Blocks` field names.

**Track D is left unclaimed deliberately.** Agent A built the dataset, the HR-01 evidence pack and
the D-7 script, and holds the context for the remaining proof-asset work. Agent B will not mutate
the seeded dataset or the RDY-0044-B baseline without a claim recorded here first.

---

## Item-level claims

| RDY | Item | Held by | State | Note |
|---|---|---|---|---|
| 0040 | D-7 demo script | Agent A | **DONE** | Closed, PB-046; `EV-040` |
| 0046 | Module provenance | Agent A | **DONE** | Closed, PB-048; `EV-046` |
| 0045 | Upstream patch currency | Agent A | **HELD** | `EV-045` upstream-target analysis written |
| 0055 | Audit-log PHI determination | Agent A | **DONE** | PB-050; `EV-055`. Released by Agent B |
| 0090 | Branding surface inventory | Agent A | **HELD** | PB-049; `EV-090`. Not closed — needs the human walk. Released by Agent B |
| — | Gate-count sync (Rule 3) | Agent A | **HELD** | PB-051. Agent B does not recalculate |
| 0067 | Published status registers | Agent B | **DONE (not closed)** | PB-070; `EV-067`. 3 of 4 criteria met; criterion 3 blocked on RDY-0003 |
| 0016 | Authorization matrix §23.4 | Agent B | **HELD** | Unblocked by the seed; 5 rows were data-blocked at PB-014 |
| 0083 | Background-service trigger | Agent B | **HELD** | Live evidence contradicts the "never executed" finding — correction pending |
| **0043** | **Menu form-rendering defect** | **Agent A** | **DONE — FIXED** | **PB-052 · patch record PR-15 · commit `5ab88c700`.** ⚠ **Agent A took this while Agent B held it — see the note below. Do not re-work it; `src/Menu/MainMenuRole.php` is already changed and committed.** |
| **0042** | **`front_office.json` Add-Patient defect** | **Agent B** | **HELD — untouched by Agent A** | Agent A has **not** started it and will not |
| **0060 0061 0062** | **Proof assets — capture inventory, capture rules, flagship recording** | **Agent A** | **HELD** | Track D, left to Agent A by agreement. Starting with **0061** (capture rules), which gates 0060 |
| 0016 | Authorization matrix §23.4 | Agent B | HELD | ⚠ **Partial evidence already exists** — PB-037 ran all 6 locked reports × 7 accounts, and PB-034 ran `patient_list` × 7. **Read those before re-running; several rows are already evidenced** |

---

## Standing constraints that bind both agents

- **Do not recalculate gate counts inside a closure entry** (`§0.0` Rule 3). Record which gates the
  RDY's `Blocks` field names, and let the sync pass do the arithmetic.
- **The gate-count sync pass is a single-agent job.** Whoever runs it says so here first.
- **Never `git add -A`** — stage by explicit path (key-material and PHI controls, PB-035 / PB-037).
- **Nothing closes on a code change or a row count.** The requirement's own acceptance criteria must
  pass, demonstrated, with a re-runnable command or a `file:line`.
