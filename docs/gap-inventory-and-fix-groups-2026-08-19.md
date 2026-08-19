# REMAINING-GAP INVENTORY BY GATE (G0–G6) — 2026-08-19

**Produced:** 2026-08-19 · **Supersedes:** `docs/gap-inventory-and-fix-groups-2026-08-15.md` (2026-08-15) for
currency, not for method — that document's 12-group fix-mechanism taxonomy is still the right way to think
about *how* an item closes, and every item below is cross-referenced back into it.

**Method.** Three independent read-only audits were run in parallel against
`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` (~12,000 lines), `docs/evidence/AGENT-CLAIMS.md`,
every relevant `EV-*` file, git history through commit `7588b42e5`, and the live native-stack instance
(MariaDB + filesystem, SELECT-only / read-only) — covering (1) everything logged between PB-201 and HEAD,
(2) live re-verification of the same checks the 08-15 inventory ran, and (3) Groups 6–9 (human decisions,
legal/regulatory, hosting, market validation) plus the orphaned SaaS locked-decisions backlog. Their
findings were then cross-checked against the register itself: every open-P0-per-gate count below was
independently re-derived from the §7.2–§7.18 register's own `Blocks` fields under the document's own
§47 "GATE COUNTING METHOD — LOCKED FOR PHASE 2" rule, and matches the register exactly. Full citation
trail is in `docs/evidence/EV-GATE-SYNC-20260819.md`.

**Nothing here closes anything beyond what is explicitly marked closed with evidence.** This is an
inventory. The closure contract in §0.0 Rule 5 of the readiness document is unchanged and was applied
throughout: no item below is marked CLOSED without a re-runnable command or file:line already on record.

---

## 0. What changed since 2026-08-15 — the headline

The engineering is very nearly done, and it inched forward again this week — but the readiness
document's own summary dashboards (`§1.4`, `§47`) had drifted **out of sync with the register they
summarize**, not just with reality, which is a sharper problem than before: two different "current"
gate-count tables exist in the same file today (`§47`'s main table, last refreshed at PB-140/2026-08-13;
and PB-206/PB-216's log entries, refreshed 2026-08-16) and they disagree. This document's corrections
section (§6 below) fixes that.

**Genuinely closed since 2026-08-15** (re-runnable evidence for each, see §6):

| ID | What closed | Evidence |
|---|---|---|
| **RDY-0002** | Owner accepted GTM VERDICT B directly | `docs/evidence/EV-WAVE3-decisions-20260816.md`, register row `aa39f8b7d` |
| **RDY-0037** | Currency (SAR) now renders on every `pat_ledger.php` amount | PR-18, PB-214, live re-check against the exact PB-202 scenario |
| **RDY-0035** (P1) | `pqri_registry_name`/`pqri_registry_id` placeholders cleared to empty | PB-151, live-reconfirmed 2026-08-19 |
| **RDY-0018** (P1) | `oe-system` removed from Administrators | PB-153 |
| **RB-22** (branding ledger, not an RDY item) | Inter font dedup — theme rebuild actually executed | PB-220; live-reconfirmed 2026-08-19: compiled CSS now references only `Inter-Regular.woff2` |
| **RDY-0082 leg 6** | Authenticated browser login proven against restored instance | PB-203, with a negative control |

**Advanced but not closed — worth knowing about even though they don't move a gate count:**

- **RDY-0071**: the named code defect (`pat_ledger.php` CSV export emitting raw HTML) is **fixed in code**
  (`6dea39a28`, `6ce9979f5`) but not yet live-verified, and the register row's own text still says
  "unfixed" — a fresh documentation drift, corrected below.
- **RDY-0045**: the upstream `rel-820` merge **actually happened** (`8e0eaba90`, confirmed on-branch by
  `git merge-base --is-ancestor`) — Group 10 of the 08-15 inventory, which said "the decision has not
  been made," is now stale in the *good* direction. What's left is narrower than before: a safety tag
  (`pre-rel820-merge-20260817`) exists locally but a `git push` failed HTTP 403 (wrong GitHub identity).
  This is now purely a human-with-credentials task, not a decision or an engineering task.
- **DG-010/B-24** (demo-deployment register, not RDY): the Accounting ACL group, empty on 08-15, now has
  one member (`k.alotaibi`) — live-reconfirmed, but neither register reflects it.
- **RDY-0049**: half-fixed. `perl_bin_dir`'s dangling XAMPP path reference is gone from the codebase
  entirely; the three Unix-only commands (`lpr`, `enscript`, `/usr/bin/file`) in `sites/default/config.php`
  are untouched.
- **RDY-0016**: not a new item, but substantially re-scoped. `EV-016-A10-acl-probes.md` traced a real,
  live-exploitable ACL fail-open path (unvalidated `formname` reaching `aclCheckForm()`, 16 orphaned form
  directories with no registry row), and `EV-016-A10-fix-scope.md` scoped — but did not implement — a
  narrow fix. Deliberately *not* a blanket fail-closed flip, pending a fuller audit.
- **RDY-0083** (background-service runner): live-reconfirmed both services are active and ticking
  (`Email_Service` next_run 2026-08-19 02:37, `UUID_Service` 06:35). The register row still reads
  "NOT READY — DEFECT... has never run," which appears stale relative to reality — flagged, not closed
  here, because no PB entry was found that ran this item through the formal closure contract.

**A cross-cutting finding that changes how several "browser session" items should be read:** RDY-0042,
RDY-0043, RDY-0048, and RDY-0071's live-verification leg are **all now blocked by the same root cause** —
not a product defect and not an unmade decision, but the coding-agent tooling's own permission classifier
refusing password entry or credential mutation, independent of any task-relayed human authorization
(documented twice, at PB-217 and PB-218, including one attempt where the Owner's authorization was
directly relayed and the classifier still refused). **This is one blocker with four symptoms.** It clears
the moment a human performs these specific steps directly at the keyboard, or adjusts the session's own
permission settings — no further agent delegation will move it.

---

## 1. Corrected counts (see §6 for the mechanical derivation)

| Measure | Value at last sync (PB-216, 2026-08-16) | **Corrected value, 2026-08-19** |
|---|---|---|
| P0 requirements closed | 27 | **28** (+RDY-0002) |
| P0 requirements open | 44 | **43** |
| Open P0 per gate | G0 3 · G1 15 · G2 10 · G3 16 · G4 3 · G5 13 · G6 20 | **G0 2** · G1 15 · G2 10 · G3 16 · G4 3 · G5 13 · G6 20 |
| Gates READY | 0 | 0 |
| Gates PARTIAL | 2 (G0, G1) | 2 (G0, G1) |
| Gates NOT READY | 4 (G2, G3, G5, G6) | 4 (G2, G3, G5, G6) |
| Gates BLOCKED | 1 (G4) | 1 (G4) |

Every other per-gate figure was independently re-derived from the register's `Blocks` fields (§7.2–§7.18)
under the document's own locked counting rule and matches the PB-216 baseline exactly — i.e. **no other
count has moved**, only RDY-0002's closure decrements G0.

---

## 2. Blockers by gate, with classification

Every remaining open item below is classified as exactly one of:

- **🧑 HUMAN** — needs a specific named person to decide, sign off, review, or physically perform an action
  (a login, a phone call, a `git push`, a wording choice) that no further agent delegation can substitute for.
- **🔗 DEPENDENCY** — blocked on something outside this task's control that is not a single human action:
  another open RDY item, a hosting account that doesn't exist yet, a pilot that hasn't started, a legal
  opinion from a commissioned third party.
- **⚙️ ACTIONABLE-NOW** — closable by ordinary engineering/documentation work today, with no waiting
  required. These are where further delegated agent work is actually useful right now.

Where an item is genuinely mixed (e.g. a code fix that then needs a human-blocked live check), both tags
are shown, ordered by what's needed *next*.

### G0 — STRATEGY GOVERNANCE READY
**Status: PARTIAL · Open P0: 2** (was 3; RDY-0002 closed 2026-08-16)

| ID | Pri | What's still open | Classification | What's needed next |
|---|---|---|---|---|
| RDY-0003 | P0 | Claim register has no reviewer running a review step (reviewer is *named* — Mohammed Elfouly, PB-077 — but naming isn't reviewing) | 🧑 HUMAN | The named reviewer must actually run one sample review and record it |
| RDY-0004 | P0 | Prohibited-claim list not packaged for Phase 3/4/5 | 🔗 DEPENDENCY | Waits on RDY-0003, and on Phase 3/4/5 briefs existing |
| RDY-0092 | P1 (not gate-counted, but a real G0 blocker) | Locked Decisions corpus (R5, 38 items / 323 unticked acceptance criteria) reconciled against the GTM only partially — `EV-092-locked-decisions-reconciliation.md` found a real, unresolved conflict: Locked Decisions Q5 forbids the exact `force_mfa` global approach RDY-0057/0099 assume | 🧑 HUMAN | Owner must resolve the Q5 MFA-architecture conflict; the six BLK-priority R5 items (BLK-001…006) are still completely untraced into R1 |

### G1 — DEMO FOUNDATION READY
**Status: PARTIAL · Open P0: 15** (unchanged)

| ID | Pri | What's still open | Classification | What's needed next |
|---|---|---|---|---|
| RDY-0016 | P0 | Positive/negative authorization matrix unproven; A-10 probe found a real live-exploitable ACL fail-open path, fix scoped but unimplemented | ⚙️ ACTIONABLE-NOW | Implement `EV-016-A10-fix-scope.md`'s scoped fix (deny-on-no-registry-row); separately, Owner decision needed on the 16 orphaned form directories |
| RDY-0033, RDY-0034 | P0 | Product identity strings / vendor links not yet replaced | 🔗 DEPENDENCY | Both explicitly `Deps: 0095` — cannot finalize what to replace until the licence/attribution determination lands |
| RDY-0038 | P0 | Saudi locale seeds only partially browser-verified (phone acceptance, metric units, actual form submission never tested) | 🧑 HUMAN | One authenticated registration submission — currently blocked by the same tooling-permission-classifier issue as RDY-0042/0043 |
| RDY-0042, RDY-0043 | P0 | `front_office.json` Add-Patient defect; `MainMenuRole.php` form-dropping defect — both attempted twice more since 08-15 (PB-217, PB-218), both times blocked by the permission classifier refusing credential entry, even with directly-relayed Owner authorization | 🧑 HUMAN | A human must perform these two logins directly, or adjust the session's own permission settings — **not a product defect**, the underlying code changes these items describe are unverified either way |
| RDY-0056, RDY-0057 | P0 | Audit-integrity and sensitivity/MFA disclosure discipline unenforced | 🔗 DEPENDENCY | `Deps: 0003` — waits on the claim-reviewer step above |
| RDY-0060, RDY-0061, RDY-0062 | P0 | Screenshot inventory and the flagship audit-integrity recording not captured | 🔗 DEPENDENCY | Cascades from 0033/0034/0038 (branding/locale) and 0056 (0062 only) still being open |
| RDY-0086 | P0 | Arabic/RTL screen-walk qualification script not written | 🧑 HUMAN | Owner must state HR-09 (Mohammed Elfouly)'s basis of competence for the review — re-affirmed at Wave 3 but the specific statement was declined, not supplied |
| RDY-0090 | P0 | Stock-OpenEMR identity surface not re-observed screen by screen | ⚙️ ACTIONABLE-NOW | Walk checklist already issued (`EV-090-walk-checklist.md`) — executing it needs no further decision |
| RDY-0094 | P0 | Demo no-go register not rehearsed against | ⚙️ ACTIONABLE-NOW | Rehearsal script already issued (`EV-094-rehearsal-script.md`) — needs execution, not authorship |
| RDY-0095 | P0 | Licence/attribution determination (shared with G4, see there) | 🔗 DEPENDENCY | See G4 — this is the single highest-leverage item in the whole register |

### G2 — SEEDED COMMERCIAL DEMO READY
**Status: NOT READY · Open P0: 10** (unchanged)

| ID | Pri | What's still open | Classification | What's needed next |
|---|---|---|---|---|
| RDY-0023 | P0 | Clinical depth (≥15 SOAP notes, ≥10 encounters with vitals) not seeded | ⚙️ ACTIONABLE-NOW | Data-seeding work, same mechanism as the already-closed sibling rows (0020–0027) |
| RDY-0025 | P0 | 8–10 synthetic documents not uploaded — blocked by a chronic host-level PHP session-persistence bug (`session.save_path` resolving to unwritable `C:\Windows`), re-investigated and re-confirmed at PB-215 | 🔗 DEPENDENCY | Needs a `php.ini` `session.save_path` fix + a shared Apache restart — a host-config change, not a code fix, and disruptive to concurrently-active agent sessions so deliberately not executed inline |
| RDY-0033, RDY-0034, RDY-0038, RDY-0042, RDY-0043, RDY-0094 | P0 | Same items as under G1 above (each also blocks G2) | *(see G1 rows)* | *(see G1 rows)* |
| RDY-0041 | P0 | D-7 journey never executed end-to-end from a reset baseline | 🧑 HUMAN | Needs a person to run it twice, once 0013/0042/0043's underlying defects are actually verified fixed |
| RDY-0044 | P0 | Demo reset — `0044-A` closed, `0044-B`'s UUID defect already fixed in the v3 baseline (`AGENT-CLAIMS.md`, resolved 2026-08-16) but the register still counts the parent ID open since both legs must close together | ⚙️ ACTIONABLE-NOW | Bookkeeping close-out against the already-completed v3 baseline evidence |

### G3 — OPERATIONALLY SAFE FOR A REAL PILOT
**Status: NOT READY · Open P0: 16** (unchanged)

| ID | Pri | What's still open | Classification | What's needed next |
|---|---|---|---|---|
| RDY-0016 | P0 | *(see G1 — same item, also blocks G3)* | ⚙️ ACTIONABLE-NOW | *(see above)* |
| RDY-0045 | P0 | Upstream merge **is done** (`8e0eaba90` on-branch); only the push is outstanding — `git push origin pre-rel820-merge-20260817` failed HTTP 403, this host authenticates as a GitHub identity (`midodevelopper`) with no write access to the target repo | 🧑 HUMAN | A human with correct push credentials must push the tag, then (on review) the branch |
| RDY-0047 | P0 | No deployment runbook exists | ⚙️ ACTIONABLE-NOW (partial) | Documentation task; full closure also needs 0064 (hosting) to validate against a real target |
| RDY-0048 | P0 | Live DB password is still the unrotated upstream default (`openemr`/`openemr`) — rotation attempted three separate ways at PB-213, **every mutation step blocked by the tool-permission classifier**, independent of task authorization | 🧑 HUMAN | Owner must rotate the credential directly — same root cause as the RDY-0042/0043/0038 tooling block above |
| RDY-0055 | P0 | Audit-log PHI-in-plaintext handling: measured, drafted, not determined | 🧑 HUMAN | Draft disclosure text exists (`EV-055-pilot-disclosure-draft.md`); needs RDY-0003's reviewer to sign it off |
| RDY-0064 | P0 | Hosting region **decided** (Dammam, `me-central2`), provisioning **not started** | 🔗 DEPENDENCY | Owner must open the cloud account and obtain vendor quotes — root of the entire G3/G6 hosting chain (0081, 0082's ongoing leg, 0084, 0085, 0096 all cascade from this) |
| RDY-0065, RDY-0066 | P0 | Qualification checklist and pilot scope template not written | ⚙️ ACTIONABLE-NOW | Pure documentation, `Deps` already satisfied (0065) or nearly so (0066 depends on 0067) |
| RDY-0068 | P0 | Pilot agreement not drafted | 🔗 DEPENDENCY | Needs 0065/0066/0073 first, plus Legal |
| RDY-0071 | P0 | Export-procedure reviewer sign-off outstanding; the named code defect is now fixed but not live-verified | 🧑 HUMAN (for the live check) / ⚙️ ACTIONABLE-NOW (register-row correction) | The live HTTP round-trip check hits the same permission-classifier login block; the register row itself should be corrected now regardless (it currently says "unfixed" when the code isn't) |
| RDY-0073 | P0 | Termination/handover procedure not written | ⚙️ ACTIONABLE-NOW | Documentation task |
| RDY-0081 | P0 | Backup policy needs an off-instance target | 🔗 DEPENDENCY | Blocked on RDY-0064 |
| RDY-0083 | P0 | Register says the background-service runner "has never executed" — **live-reconfirmed today that it is active and ticking**, this looks stale | ⚙️ ACTIONABLE-NOW | Needs a formal closure pass (re-verify + cite + update the row) rather than new engineering |
| RDY-0084, RDY-0085 | P0 | Monitoring and TLS — requirements/policy written, implementation needs the host | 🔗 DEPENDENCY | Blocked on RDY-0064 |
| RDY-0096 | P0 | Support-hours/staffing decision | 🧑 HUMAN | Options card already prepared (`EV-096-options.md`, ready since 2026-08-16 19:24) — Owner has not picked one; Wave 3 explicitly recorded the decision as deferred, not made |

### G4 — PHASE 3 BRAND INPUT READY
**Status: BLOCKED · Open P0: 3** (unchanged)

| ID | Pri | What's still open | Classification | What's needed next |
|---|---|---|---|---|
| RDY-0004 | P0 | *(see G0)* | 🔗 DEPENDENCY | *(see G0)* |
| RDY-0090 | P0 | *(see G1)* | ⚙️ ACTIONABLE-NOW | *(see G1)* |
| RDY-0095 | P0 | Licence/attribution determination — commissioned to SkyEagle 2026-08-14, **still outstanding**. `EV-095-background-brief.md` (issued 08-16) is explicitly labeled background research only, not a determination, and leaves the actual determination block blank. Alone gates RDY-0033, RDY-0034, RDY-0090's downstream use, and this entire gate | 🔗 DEPENDENCY (external, commissioned) | **The single highest-leverage open item in the whole register.** Nothing has moved on the actual determination since commissioning — this is the one item where the ranking hasn't changed since 08-15 |

### G5 — PHASE 4 MESSAGING INPUT READY
**Status: NOT READY · Open P0: 13** (unchanged)

| ID | Pri | What's still open | Classification | What's needed next |
|---|---|---|---|---|
| RDY-0003, RDY-0004, RDY-0016, RDY-0056, RDY-0057, RDY-0060, RDY-0061, RDY-0062, RDY-0086 | P0 | *(see G0/G1 rows — each also blocks G5)* | *(see above)* | *(see above)* |
| RDY-0067 | P0 | Status-register artefact (47 Disabled / 27 Uninstalled / 18 Requires-Integration / 60 Missing) not extracted from Source B and published | 🔗 DEPENDENCY | `Deps: 0001(closed), 0003(open)` — mechanically simple once 0003 clears, but formally waits on it |
| RDY-0071 | P0 | *(see G3)* | 🧑 HUMAN / ⚙️ ACTIONABLE-NOW | *(see G3)* |
| RDY-0078 | P0 | ZATCA/NPHIES verified against **secondary** sources only; no primary regulator document read | ⚙️ ACTIONABLE-NOW | A research task — read and cite the primary sources; `EV-092` §3.2 flags a possible primary-source lead (Locked Decisions Q21) already worth pulling forward |
| RDY-0088 | P0 | 6 of 9 unverified Source C competitor dossiers re-verified; 3 remain | ⚙️ ACTIONABLE-NOW | Finish the remaining 3, then a publication decision |

### G6 — PHASE 5 WEBSITE PRD INPUT READY
**Status: NOT READY · Open P0: 20** (unchanged)

| ID | Pri | What's still open | Classification | What's needed next |
|---|---|---|---|---|
| RDY-0004, RDY-0041, RDY-0045, RDY-0047, RDY-0060, RDY-0062, RDY-0064, RDY-0065, RDY-0066, RDY-0067, RDY-0071, RDY-0073, RDY-0078, RDY-0085, RDY-0088, RDY-0096 | P0 | *(all covered under their home gate above — G6 requires G2 and G3 to pass first, plus these)* | *(see above)* | *(see above)* |
| RDY-0069 | P0 | Pilot cost instrumentation | 🔗 DEPENDENCY | Explicitly "needs a pilot to exist — not startable" until one does |
| RDY-0075, RDY-0076, RDY-0077 | P0 | V-1/V-2/V-3 market validation — reachable self-pay clinic population, willingness to separate clinical from invoicing systems, felt-pain validation | 🔗 DEPENDENCY (external — market research) | **Zero calls made since commissioning.** `EV-079-candidates.md` is desk research only — 30 clinics named, 0 reached. This is the group that has moved least of anything in the register; nothing here has any technical predecessor |

---

## 3. The single most valuable unblocking action, ranked

Unchanged in the #1 spot from the 08-15 inventory, sharper now that the alternatives have partially
moved:

1. **RDY-0095 — the licence/attribution determination.** Still nothing beyond a commissioning record and
   a background brief that explicitly disclaims being a determination. Alone gates G4 and three other
   register rows. No technical predecessor, no engineering possible, purely a third-party legal read.
2. **A human doing the tooling-blocked items directly.** RDY-0038, 0042, 0043, 0048, and 0071's live-check
   are *all* blocked by the same permission-classifier layer, not by five separate problems. One person
   spending perhaps an hour at the keyboard — two logins, one credential rotation, one CSV download check —
   clears five rows that no further agent delegation can touch.
3. **RDY-0075 (V-1) — the first market-validation phone call.** Still exactly zero calls made. It
   validates the premise the entire plan rests on and has no predecessor of any kind.

---

## 4. Cross-reference to the 08-15 inventory's 12 groups

Groups 1, 2, 5, 11 (code changes, build/artefact regen, second-person execution, register hygiene) are
substantively where they were, with the specific exceptions logged in §0 above. Group 3 (live-data
mutation) gained one closure (pqri placeholders) and lost none. Group 4 (the manual browser session)
is **partially discharged but now blocked differently** — see the tooling-permission finding. Groups 6–10
(human decisions, legal, hosting, market validation, upstream patching) are detailed item-by-item above,
integrated into their gates rather than repeated as a separate taxonomy — the gate view is what this
document adds. Group 12 (deferred by policy) is unchanged and correctly out of scope for gate readiness.

---

## 5. Full evidence trail

See `docs/evidence/EV-GATE-SYNC-20260819.md` for: the three source audits in full, the register `Blocks`-field
derivation showing every per-gate count matches the document's own locked counting method exactly, and
the specific spot-checks run to verify agent-reported findings before they were relied on here.

---

## 6. Corrections applied to the tracking document itself

Recorded here for traceability; the actual edits are in
`docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` under PB-300 (this audit's claimed range):

1. RDY-0002's detailed spec section (previously read `Status: NOT READY`, inconsistent with its own
   already-corrected summary-table row) corrected to match.
2. §1.4 counts table: 27→28 P0 closed, 44→43 P0 open, G0 3→2.
3. §47's main dashboard table, which had been left at its PB-140 (2026-08-13) figures while PB-206/PB-216
   (2026-08-16) log entries carried the real current baseline in prose only — reconciled to the single
   correct figure, with the RDY-0002 decrement applied on top.
