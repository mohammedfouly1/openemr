# EV-094 — DEMO REHEARSAL SCRIPT

**Requirement:** RDY-0094 · **Gates:** G1 G2 · **Deps:** RDY-0080, 0083 · **Owner:** Founder / Product Owner
**Continuation of:** `EV-094-demo-no-go-register.md` (which adopts §40 as the register itself — this
document does not re-author the register, it sequences and scripts it into what a presenter actually
says, in order, live)
**Date:** 2026-08-16 · **AGENT-BRANDING** (Agent D / Wave execution)

---

## 0. What this is, what it is not

`EV-094-demo-no-go-register.md` established that §40's 13-row register **is** RDY-0094's register and
scored its acceptance: register exists (MET), every row has an unlock condition (MET), **rehearsal
performed aloud and witnessed (NOT MET)**, **D-7 cross-check against the register (NOT MET / NOT
REACHABLE)**.

**This document does not change that scoring.** It produces the missing input to the first NOT-MET
criterion: an actual script, in demo order, that a human presenter can read aloud, have witnessed, and
sign off on. **Writing the script is not performing the rehearsal.** RDY-0094 stays NOT CLOSED until a
named presenter reads every line below aloud, in front of a second person, against the live product,
and that is logged.

---

## 1. Governing rule — say it before they notice it

GTM `Product-Positioning-and-GTM-Locked-Strategy.md` §16.2, the paragraph immediately under the D-1…D-13
storyline table:

> **"Two operational rules carried into every demo:** never open Admin → Backup (it will fail —
> `mysql_bin_dir` points at an XAMPP path, B7); **and expect two overdue background services on the
> diagnostics screen (B6) — mention it before the prospect notices it.**"

That second clause is the rule this script exists to satisfy. The background-services disclosure is
**not** a response to a question — it is scripted to be **said first, unprompted, before the
diagnostics screen is ever opened**, exactly the way §40's own stated principle puts it: *"A no-go item
is not something to hide — it is something to say first."*

**This script therefore places the background-services line at the top, before D-1 opens, not as an
answer to "what's that red icon."**

---

## 2. Demo sequence (per GTM §16.2's closing line)

> *"The demo sequence that converts: open with D-1 (nobody else can do it) → D-2 (their actual anxiety)
> → D-4 (their actual objection) → close with D-7 once seeded. Never a feature tour."*

The script below follows that order, with no-go disclosures inserted at the point in the sequence
where the corresponding screen would otherwise be reached.

---

## 3. THE SCRIPT

### 3.1 Opening — before any screen is shown

**[SAY THIS FIRST, before logging in, before D-1 opens.]**

> *"Before we start — two housekeeping notes, so nothing on screen surprises you. First: if you see a
> couple of background services flagged as overdue on the diagnostics screen later, that's expected on
> this demo environment specifically — I'll explain why when we get there, and it's not something that
> happens on a live customer deployment the way it does here. Second: everything I show you today is
> real product behaviour on seeded, synthetic data — no real patient information. I'll tell you when
> something isn't switched on rather than working around it."*

This satisfies §16.2's "mention it before the prospect notices it" for the background-services item
**and** sets the disclosure posture (POS-002/R-04) that the rest of the demo depends on.

### 3.2 D-1 — Audit trail + integrity verification (opens the demo)

Login as `admin` is the only account named for D-1 in §16.2's own table — **row 11 of §40 still
applies for every other screen**: *"The `admin` account — Prohibited in any material, ever... use the
demo administrator."* Confirm before this session which account is actually being used; if it is the
literal `admin` login, this is screen-recorded/photographed material and row 11 blocks it.

> *"This is the audit trail. Every one of the 4,280 log rows here is a real system event, not a
> summary. I'm going to run the integrity verification live, on this data, right now — not a
> screenshot from a marketing deck."*

**No no-go items live inside D-1 itself.** Proceed.

### 3.3 D-2 — Roles and the permission model

> *"Two logins, same menu, different reality — that's the point."*

No no-go items inside D-2. If asked about multi-role licensing/whether more roles exist: this is
answerable honestly and isn't a §40 item.

### 3.4 D-4 — Build a clinical form with no code

> *"I'm going to build a field live, from your actual paper form if you brought one."*

**Qualify per §16.2's own table entry for D-4:** *"zero layout forms ship configured"* — say so if the
prospect asks whether this is prebuilt: *"None of this is a canned demo form — I'm building it now,
which is also why it isn't pre-populated with your specialty's fields yet."*

### 3.5 If Module Manager is opened at any point (§40 row 8)

**[SAY THIS BEFORE clicking into Module Manager, not after.]**

> *"Opening this registers a few modules — that's the product doing what it does, and I'd rather tell
> you than have you notice."*

Do not open Module Manager casually while narrating something else — §40 row 8 calls this a **state
change mid-demo**, and the presenter must choose to open it deliberately, with the line above said
first.

### 3.6 If the Background Services / diagnostics screen is reached (§40 row 12)

**[This is the disclosure already pre-empted at §3.1. If the screen is actually opened, repeat the
specific, corrected line — do not rely on the opener alone if the screen is visually reached.]**

> *"Here's what I mentioned earlier. If it's been a while since anyone signed into this demo host, you
> may see two services showing overdue — the trigger that runs them is real and proven, and it
> self-heals the moment someone's signed back in. On this particular demo host, that trigger depends on
> a console session staying open, because of how this host's storage happens to be mounted. On a real
> pilot host, the same trigger runs as a proper Windows service account with no such dependency — so
> what you're seeing here is specific to this environment, not the product."*

**This is the corrected §40 row-12 wording verbatim** (AGENT-OPS, PB-181, 2026-08-16) — it names the
console-session dependency explicitly, states the mechanism (works, proven, session-bound on this
host), and distinguishes the demo host from a pilot/production host. **Do not use the earlier, retired
wording** ("the trigger is built and it's one decision away from being switched on") — that phrasing
understated the live regression PB-142 found (~10 hours overdue after an unobserved session end) and
omitted the actual cause.

**Additional disclosure this script adds, not present verbatim in §40 but implied by RDY-0083's
finding and owed to the same "say it before they notice it" discipline:** if the demo session has
visibly been interrupted (browser reloaded to a login screen unexpectedly, or the app was unreachable
for a stretch), say so proactively rather than waiting to be asked:

> *"If the app dropped out for a moment there, that's this specific host's session-bound setup, the
> same thing I just described for the background services — a console-session logoff on this box takes
> the whole stack down with it, including that trigger. That's a property of this demo box's hosting,
> not of the product; a pilot instance runs as always-on services and doesn't have that dependency."*

**Source for this addition:** `AGENT-CLAIMS.md` PB-181 / `docs/evidence/EV-083...` — the same
investigation that corrected §40 row 12 established that **the entire demo stack (`httpd`, `mariadbd`)
is bound to the console session on this host**, not only the background-service trigger; a session
end takes both down together. §40 row 12's wording covers the background-service *symptom*; this
addition covers the shared *cause* so a presenter isn't caught explaining a full outage with only the
background-service line, which would undersell what actually happened.

### 3.7 Boundary statement — before any billing screen, for a Saudi prospect (§40 row 7)

**[SAY THIS BEFORE opening any billing screen. Permanent discipline — the statement always precedes
the screen, per §40's own row.]**

> *"Before I show this: we do not issue your tax invoice and we do not submit insurance claims. Those
> stay where they are."*

### 3.8 D-7 — Reception → physician → billing journey (closes the demo)

Only attempted once RDY-0041's D-7 rehearsal has actually run (§7.6 of the requirements document:
*"Not attempted"* as of the evidence this script is built on). **RDY-0094's own acceptance criterion**
— *"the D-7 rehearsal does not touch any registered item"* — can only be checked once that run exists;
this script cannot pre-certify it. When D-7 is run, the presenter narrating it should have every line
in §3.1-§3.7 already rehearsed, since D-7 is the sequence most likely to incidentally cross into
Module Manager, a billing screen, or the diagnostics screen.

### 3.9 If asked about anything else on the no-go list

Every §40 row has its own "what the presenter says if asked" column already written; this script does
not duplicate rows the presenter is not expected to proactively raise (Admin → Backup, C-CDA,
external integrations, API screens, the patient portal, Arabic PDF, the RDY-0050 report set,
telehealth/dispensary/group therapy). Read those directly from §40 if asked — they are reactive
answers, not lines that need pre-empting, because §16.2 only names Backup and background services as
the two rules that must be said **before** being asked.

---

## 4. Rehearsal log (blank — to be completed by the named presenter)

| Field | Value |
|---|---|
| Presenter (named individual) | |
| Witness (named individual, per §40 Owner field: Founder / Product Owner) | |
| Date rehearsed | |
| Environment used (demo host / URL) | |
| Each line in §3.1-§3.7 read aloud, in order | ☐ |
| Witness confirms each disclosure was said **before** the corresponding screen, not after | ☐ |
| Any line that did not match the live screen's actual behaviour (record verbatim) | |

**RDY-0094's "rehearsed aloud" criterion is met only when this table is filled in by an actual person,
not by this document.**

---

## 5. What remains, precisely

| Item | What is needed | Who |
|---|---|---|
| The rehearsal itself | A named presenter reads §3.1-§3.7 aloud, witnessed, §4 completed | Founder / Product Owner (per RDY-0094's own `Owner` field) |
| D-7 cross-check | Once RDY-0041 (D-7) actually executes, its run log is checked against every §40 row for an accidental touch | Whoever executes RDY-0041 |
| Keep §3.6's wording in sync with §40 row 12 | If AGENT-OPS or anyone else revises row 12 again, this script's §3.6 must be re-copied, not left stale | Whoever next edits §40 row 12 |

**Neither of the first two can be produced by writing more documentation.** This script closes the gap
between "the honest answer exists" (already true per §40) and "a presenter has said it out loud in the
right order" — it does not close RDY-0094 itself.

---

## 6. Status

**RDY-0094 — NOT CLOSED.** Unchanged from `EV-094-demo-no-go-register.md`'s scoring: register exists
(MET), unlock conditions recorded (MET), rehearsal performed (**still NOT MET — this script is the
input to it, not the rehearsal**), D-7 cross-check (**still NOT MET / NOT REACHABLE** — RDY-0041 has
not run).

**`Blocks`:** G1 G2. No gate count moved (§0.0 Rule 3).
