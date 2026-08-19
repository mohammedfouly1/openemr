# Gate-count sync — 2026-08-19 evening (orchestrator)

**Method (Rule 3 sync point):** every register row in §7.2–§7.18 (lines ~959–1146,
`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md`) parsed programmatically
— split on `|`, take the priority field (P0/P1/P2/P3) and the last non-empty field
(Verdict) per row, filter to `Pri == P0` and `Verdict` starting with `NOT READY` or
`BLOCKED`. Re-runnable:

```python
import re
with open('docs/Marketing-MVP-and-Launch-Readiness-Requirements.md', encoding='utf-8') as f:
    lines = f.readlines()
for i in range(958, 1146):
    line = lines[i]
    m = re.match(r'^\|\s*\*\*(\d{4})\*\*', line)
    if not m: continue
    fields = [c.strip() for c in line.split('|') if c.strip()]
    pri = next((c.strip('*').strip() for c in fields if c.strip('*').strip() in ('P0','P1','P2','P3')), None)
    verdict = fields[-1]
    if pri == 'P0' and (verdict.startswith('NOT READY') or verdict.startswith('BLOCKED')):
        print(m.group(1), verdict[:60])
```

This supersedes §1.4's "The counts" table's **Open P0: 23** figure (last synced
PB-424, 2026-08-19 mid-day) — §1.4 itself is not rewritten here (Rule 2: prefer a
new file; its table is large, hand-cross-referenced, and a partial rewrite risks
introducing new errors) but should be treated as **stale as of this entry** until
someone does a full table edit. A short pointer was added at §1.4 itself.

## Open P0 items: 15 (not 23)

| RDY | Blocks | Why still open |
|---|---|---|
| 0004 | G0 G4 G5 G6 | Internal sequencing — Phase 3/4/5 not started |
| 0016 | G1 G3 G5 | Sensitivity-ACL bypass fixed and verified (PB-444); a separate cross-physician non-sensitive-note policy question deliberately left for the Owner, not decided by an agent |
| 0041 | G2 G6 | D-7 "twice" wording question (does it mean twice from the same reset, or two resets) flagged for the Owner, not decided |
| 0047 | G3 G6 | Deployment runbook needs an independent human operator to execute it end-to-end — not something an agent can do by definition |
| 0048 | G3 | DB credential rotation — categorically outside what an agent does directly (modifying credentials) |
| 0060 | G1 G5 G6 | All 12 screenshots content-correct and qualification-embedded (2026-08-19 evening); needs an independent (non-capturer, non-embedder) second-party sign-off, plus a P-3 browser-chrome check the capture tool doesn't include |
| 0061 | G1 G5 | Same underlying gap as 0060 — the independent §8 review itself |
| 0065 | G3 G6 | Real customer qualification calls — external |
| 0069 | G6 | Real pilot cost instrumentation — external |
| 0073 | G3 G6 | External delivery channel + reviewer for handover docs |
| 0075 | G6 | V-1 market validation — real customer interviews |
| 0076 | G6 | V-2 market validation — real customer interviews |
| 0077 | G6 | V-3 market validation — real customer interviews |
| 0085 | G3 G6 | TLS/HTTPS runbook for a *future* customer-pilot host (distinct from the current demo's HTTPS, already verified working) |
| 0090 | G1 G4 | Print/PDF/email templates confirmed clean by source read but never visually inspected on a rendered page; 2 latent-fallback branding items are a governance question (RDY-0095), deliberately not touched |

## Closed today (2026-08-19), not yet reflected in §1.4's hand-maintained list

RDY-0016 (partially — sensitivity bypass leg only, see above for what stays open),
RDY-0025, RDY-0029, RDY-0045, RDY-0062 (eighth browser-check agent, Codex/PB-434-442),
RDY-0081, RDY-0083, RDY-0084 (Ubuntu infra, this session), RDY-0086, RDY-0094
(direct-navigation fix + appointment date-shift, this session + Codex).

## Open P0 per gate (bare `Blocks` token, matching §47's canonical counting rule)

| Gate | Open P0 count | Items |
|---|---|---|
| G0 | 1 | 0004 |
| G1 | 4 | 0016, 0060, 0061, 0090 |
| G2 | 1 | 0041 |
| G3 | 6 | 0016, 0047, 0048, 0065, 0073, 0085 |
| G4 | 2 | 0004, 0090 |
| G5 | 4 | 0004, 0016, 0060, 0061 |
| G6 | 10 | 0004, 0047, 0060, 0065, 0069, 0073, 0075, 0076, 0077, 0085 |

**Not re-derived here** (out of scope for this pass, needs its own dedicated
cross-reference against §6's full gate-readiness criteria, not just a P0 count):
whether any gate's categorical status (READY/PARTIAL/NOT READY/BLOCKED) should
change. G2 dropping to 1 open P0 (from 3) and G0 dropping to 1 (from a higher
count) are the most likely candidates for re-categorization, but that requires
checking §6's full criteria per gate, not just counting — flagged for whoever
does the next full sync, not decided unilaterally here.

**Total P0 requirements, closed count, and the 71/48 baseline figures in §1.4**
were not re-derived in this pass either — only the *open* list was rigorously
re-verified, since that's what was actually needed and what the register rows
directly support without needing the full historical closure list re-audited.
