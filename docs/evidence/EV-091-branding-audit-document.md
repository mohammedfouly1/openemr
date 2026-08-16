# EV-091 — BRANDING / REBRANDING AUDIT DOCUMENT: LOCATED

**Requirement:** RDY-0091 · **Gates:** G4 · **Deps:** RDY-0090 · **Owner:** Brand
**Acceptance:** *"Located, or created from RDY-0090."*
**Issued:** 2026-08-16 · **AGENT-DOC**, Phase 2B

---

## 1. Located — four documents, not absent

§7.21 of the readiness document already recorded (2026-08-13) that these are **PRESENT**, correcting
the original audit's "not present in this environment" finding. This artefact is the first time RDY-0091
itself has been formally closed out against that finding rather than left as a row that "remains open"
by inertia (§7.9 line: *"§18's derived inventory can now be replaced by a real branding audit. RDY-0091
remains open"*).

| Document | Lines | Date | What it is |
|---|---:|---|---|
| `docs/AuditRebranding.md` | 1,899 | **2026-08-10** | A **Phase 2 rebranding and identity audit** — engineering-focused, checks the branding module implementation against the locked decisions (Q76/Q77/MVP-010), scored **FAIL** with two binding-requirement defects and material omissions named |
| `docs/rebranding.md` | 1,795 | — | The **authoritative discovery/action map** the audit above cites as its #3 precedence source |
| `docs/RebrandingPlan.md` | 1,319 | — | The Phase 2 implementation plan the audit graded against |
| `docs/RebrandingBugs.md` | 2,061 | — | A defect register — cited directly by `EV-090` §5 (RB-01, the `xlt()` orphaning defect) |

**This satisfies RDY-0091's acceptance on the "located" branch.** A branding/rebranding audit document
exists, is substantial (1,899 lines), is dated, is scoped, and is method-documented (its own §2–§3
record a SHA-256 manifest check against the locked decisions before use — the same discipline this
readiness document's own §0.0 asks of every agent).

## 2. ⚠ What "located" does not mean: currency

**`AuditRebranding.md` is six days old relative to this entry (2026-08-10 → 2026-08-16) on a branch
with substantial subsequent branding work** — the readiness document's own PB log records branding
commits after that date (e.g. the Q77 theme-pruning work referenced in `CLAUDE.local.md` §6, RB-14's
Arabic PDF font decision pack, RB-22's Inter-font theme rebuild — both dated 2026-08-16 per
`docs/evidence/AGENT-CLAIMS.md`'s Agent D section). **`AuditRebranding.md`'s FAIL verdict and its two
named defects have not been re-checked against current `HEAD`** — this artefact does not attempt that
re-check; it only locates the audit and states plainly that it is not current.

**`EV-090` (2026-08-14, branding **surface** inventory — favicons, titles, portal, email, rendered
strings) is the fresher branding-observation artefact** and should be read alongside
`AuditRebranding.md` rather than in place of it: `AuditRebranding.md` audits the **branding module's
engineering correctness** against locked decisions; `EV-090` audits the **product's visible identity
surface**. They answer different questions and neither substitutes for the other.

## 3. Reconciliation against RDY-0090's inventory (`EV-090`)

| Question | `AuditRebranding.md` (2026-08-10) | `EV-090` (2026-08-14) |
|---|---|---|
| Scope | Module implementation vs. locked decisions Q76/Q77/MVP-010 | Live product surfaces (favicons, titles, portal, email, templates) |
| Method | Static code review + SHA-256 manifest check | `git hash-object` diffing against upstream + live HTTP probes |
| Overlap | Both reference `docs/RebrandingBugs.md` RB-01 (the `xlt()` string-orphaning defect) — **consistent, not contradictory** | Same |
| Gap between them | Does not enumerate favicons, browser titles, portal address, or email-identity globals — `EV-090`'s entire §1–§5 | Does not re-grade the branding module's Phase 2 engineering conformance — `AuditRebranding.md`'s entire §4 |

**No conflict found between the two.** They are complementary, not competing, sources — recorded here
so a future reader does not have to re-derive that.

## 4. What remains

**A re-audit against current `HEAD`** is the honest next step, not performed here (it is a
substantial engineering review, not a documentation-location task, and outside this session's scope).
Recorded as an open item rather than silently assumed resolved:

| Item | Status |
|---|---|
| `AuditRebranding.md`'s two binding defects (revision-write atomicity; logo-bytes validator bypass) | **Unknown current state** — not re-checked here |
| `AuditRebranding.md`'s "material omissions" (SMART dark-style contract, CI wiring, kill/atomicity test) | **Unknown current state** — not re-checked here |

## 5. Acceptance

| Criterion | Result |
|---|---|
| Located, or created from RDY-0090 | **MET** — located; four documents, cross-referenced against `EV-090` in §3 |

### Status: **RDY-0091 — VERIFIED READY** on the "located" criterion as literally written.
**Not closed by this agent** — recommended for confirmation at gate sync. **§4's re-audit is a
separate, larger piece of work this document does not attempt and does not count as blocking this
item's own narrow acceptance.**

**`Blocks`:** G4. No gate count moved (§0.0 Rule 3).
