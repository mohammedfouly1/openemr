# PHASE 2B — CONTINUATION PROMPT (paste this into a new Claude session)

> ⚠ **This snapshot is from 2026-08-13 and is now stale in several respects** (notably §2's
> "418 behind upstream and divergent" — the `upstream/rel-820` merge actually landed 2026-08-17,
> leaving only a 1-commit residual gap; also the P0/gate counts, HEAD SHA and closed-item list below
> have all moved). **Before acting on any figure in this document, re-verify it against
> `docs/gap-inventory-and-fix-groups-2026-08-19.md` and the live register in
> `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md`.** The rules, locked decisions and
> known-defect list further down are still a reasonable orientation; the numbers are not.

---

You are continuing **Phase 2B — Execute & Close P0 Readiness Gaps** on the OpenEMR/Thiqa project.
Work was paused mid-phase. **Read this whole prompt before running anything.**

## 1. ORIENT FIRST — read these, in this order

1. **`CLAUDE.local.md`** — how this machine actually runs. **No Docker, no `openemr-cmd`, no Selenium.**
   Native Windows stack: Apache 8300 / PHP 8.3.33 / MariaDB 11.8.8. Start with
   `C:\openemr-stack\start-openemr.ps1`.
   ⚠ **One section of it is now STALE:** it documents the login as `admin` / the installer default.
   **That credential was rotated (RDY-0017) and no longer works.**
2. **`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md`** — ~5,900 lines, the **single
   authoritative document**. All status, evidence and closure decisions live here. **Do not create a
   parallel report.** Read at minimum: §0.2, §3, §7.19–§7.21, §47 (gate-count rule), and every
   `PB-0xx` execution-log entry — those are the record of what has actually been done.
3. **`docs/ScreenShoots/`** — browser verification evidence: `BrowserVervication.md` (Round 1),
   `BrowserRetest.md` (Round 2), 53 screenshots, and the two agent prompts.

## 2. CURRENT STATE (verify, do not assume — it may have moved)

| | |
|---|---|
| Repo | `G:\My Drive\OpenEMR`, branch `feat/thiqa-branding-foundation`, HEAD `a4ae30356` |
| **Uncommitted work** | **15 modified source files + 2 new console commands — NOTHING IS COMMITTED OR PUSHED.** This is real work at risk; ask the user whether to commit before doing anything destructive |
| Product | OpenEMR **8.2.0** (rel-820-based), schema 541, 418 behind upstream and **divergent** |
| Database | `openemr` @ 127.0.0.1:3306 — **10 users, 0 patients**, globals 495, log ~43k rows |
| Credentials | `C:\openemr-stack\secrets\thiqa-demo-credentials.json` — six demo accounts + rotated `admin`. **Never write a password into any document, output or screenshot** |
| **P0 progress** | **70 open at Phase 2B start · 16 closed · 54 open** |
| **Gates** | G0 3 · G1 16 · G2 20 · G3 19 · G4 3 · G5 14 · G6 21 |
| Closed | RDY-0001, 0010, 0011, 0012, 0013, 0014, 0015, 0017, 0032, 0036, 0037, 0038, 0050, 0051, 0052, 0080 (+ P1s 0053, 0054) |

## 3. RULES THAT GOVERN THIS WORK — these are why it has held together

1. **NO CLOSURE WITHOUT ACCEPTANCE EVIDENCE.** "Code changed", "config saved", "looks right" are
   explicitly **not** closure. Every closure needs the requirement's own acceptance criteria to pass,
   demonstrated. This has already caught several would-be false closures — honour it.
2. **Never infer a browser result from a database value.** Six accounts once had perfect `users` rows
   and none could log in. A DB value proves the row was written, nothing more.
3. **Use a negative control wherever a "pass" could be vacuous.** Two real defects were caught this
   way. If every role sees the same thing, your test proves nothing.
4. **DO NOT merge / fetch / rebase / pull / reset.** The user explicitly rejected a master merge.
   Track F is **analysis only** until the upstream target is decided and RDY-0082 passes.
5. **DO NOT seed synthetic data (Track D)** until the **fresh RDY-0044-A** pre-seed snapshot exists
   and passes. Hard stop.
6. **DO NOT start Phase 3, 4 or 5.**
7. **Core file edits require a numbered patch record** (locked-decision Invariant 4 / Q1). Prefer
   module or config paths.
8. **Record method limits honestly.** Several findings here were caught only because a limitation was
   written down instead of smoothed over.

## 4. LOCKED DECISIONS (do not reopen)

- **Hosting:** Saudi Arabia · Google Cloud · `me-central2` (Dammam). Provisioning is
  `BLOCKED — EXTERNAL`. **Saudi hosting does NOT prove regulatory compliance** — never imply it does.
- **Authorization design "A+":** report-specific least privilege via two dedicated ACOs,
  `patients|bulk_rep` and `patients|op_rep`, provisioned reproducibly by
  `bin/console thiqa-branding:provision-report-acl` (idempotent). **Do not remove `patients|demo` or
  `patients|appt` from Front Office.**
- **Gate counting method** is locked in §47. Use it; do not invent another.
- **RDY-0044 is split** into **0044-A** (pre-seed rollback snapshot, *before* seeding) and **0044-B**
  (post-seed demo baseline, *after*). This resolved a real circular dependency.

## 5. IMMEDIATE NEXT ACTION

**Create the fresh RDY-0044-A pre-seed snapshot.** Track B's foundation is complete and
browser-verified, which was its stated precondition. It is the only gate in front of Track D.

Required (per the Owner's instruction recorded in the document):
1. Take a **fresh** snapshot — the old `pre-rdy0010-*.sql` predates the demo accounts and is
   explicitly **NOT** the baseline;
2. record current users, ACL groups, facility, globals and all zero-data table counts;
3. hash and protect it;
4. **restore it into a disposable target and verify it reproduces the CURRENT foundation**;
5. write the rollback procedure.

Working tooling already exists: `bin/console thiqa-branding:backup` (verifies + hashes + prunes),
and the disposable-instance method is documented in PB-024/PB-027.

## 6. KNOWN OPEN DEFECTS

| Defect | State |
|---|---|
| **Tamper report false-positives on API rows** | **Root cause fully isolated (PB-030):** `api_log.created_time` is a `TIMESTAMP`, so its read value shifts with session timezone, and the checksum hashes it. Dormant at UTC; **activated by setting Asia/Riyadh**. Upstream defect. **Fix needs a numbered patch record — do not loosen the verifier to silence it** |
| **Version mismatch** | UI reports **8.2.0**, API/DB report **8.3.0-dev**. Material to RDY-0045 |
| **Add-Patient has no visible `+966` hint** | Cosmetic; needs a core edit → Phase 3 branding |
| **`oapublic.key` permissions notice** | Benign; hardening pass |

## 7. BLOCKED — NEEDS A HUMAN, NOT MORE ENGINEERING

- **Cloud provisioning** (RDY-0081 off-instance backup, RDY-0064 region access)
- **A named individual** as DevOps/Infrastructure owner (role is assigned, person is not)
- **RDY-0082 leg 6** — admin screens against a restored DB (browser)
- **RDY-0075–0078** — real interviews and primary-source regulatory verification. **Never fabricate
  interview data**
- **RDY-0095** — legal/licensing determination

## 8. HOW TO WORK

Verify state before trusting this prompt — run `git status`, check the DB counts, confirm the stack
is up. Record every action in the authoritative document as a `PB-0xx` entry with pre-state, change,
test, result, evidence and rollback. Recalculate gate counts after each closure cluster. When you
find something that contradicts an earlier conclusion in the document, **correct it in place and say
so** — several entries already do this, and that transparency is the point.

Ask the user before anything irreversible: commits, pushes, merges, deletions, data seeding.
