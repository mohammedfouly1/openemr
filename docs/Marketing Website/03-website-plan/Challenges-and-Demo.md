# THIQA — WEBSITE CHALLENGES AND DEMO PLAN

## 0. Context this file assumes

| Fact | Value |
|---|---|
| Product | **Thiqa** (ثقة) — outpatient clinic management system and EMR, implemented, hosted and supported. Built on open-source OpenEMR, disclosed |
| Vendor | **SkyEagle** · `skyeagle.uk` |
| Live demo instance | **`https://demo.skyeagle.uk`** — verified 2026-08-19: valid TLS, HTTP→HTTPS 301, HSTS, `X-Content-Type-Options: nosniff`, Cloudflare in front, `<title>Thiqa Login</title>`, no user-facing stock OpenEMR identity |
| Demo host | Ubuntu `demo-openemr`, with systemd-managed backup, background-service scheduler and monitoring already running |
| Decision that creates these challenges | The marketing website will drive prospects **into the live demo**, rather than only into a booked, presenter-guided walkthrough. **Ruled 2026-08-20 as `STEP0-001` (Reading B): a gated evaluation credential issued as the fulfilment of a booked walkthrough — not an open self-serve trial.** See `../01-governance/CommitteeSystem.md` §13.1 |

**Why that decision matters.** The locked GTM strategy (DEM-001) deliberately **deferred** a
self-service seeded demo, on the grounds that it *"requires seeding plus per-visitor isolation;
GAP-0043 makes isolation manual."* An open self-serve trial would reverse that deferral.
**`STEP0-001` does not reverse it** — it rules that DEM-001's own revisit trigger, *"multi-tenancy
or seeding automation ships,"* fires once the scheduled reset + date re-base ships (Challenges 2
and 3, sequenced first and CONFIRMED), and that a form-gated, time-boxed, non-admin credential
offered inside the "Book a walkthrough" booking therefore needs no reopening. **Either way the four
problems move** from *"the presenter handles it"* to *"the product and the website must handle it."*
Those four are §1–§4 below.

---

## 1. Challenge 1 — The gap: it's a login wall, not a trial

A prospect clicking through from the marketing site hits a username/password box. Nothing lets
them in.

So *"demo trial for customers"* needed a decision: **do they get a credential automatically after a
short form, or is one issued by hand — and is it a trial at all?** That decision is now made. It is
recorded below and, in its full form with rationale, as the step-0 instrument `STEP0-001` in
`../01-governance/CommitteeSystem.md` §13.1.

### Why it matters

This is the single point at which the whole marketing funnel either converts or stops. Every
other page on the site exists to get a qualified prospect to this door. Until 2026-08-20 the door
was shut and the site had nothing to say about how it opens; it now opens on a **booked
walkthrough**, which the evaluation credential fulfils.

### What has been decided — `STEP0-001`, 2026-08-20

| Question | Ruling | Basis |
|---|---|---|
| How does a visitor get in? | **A gated evaluation credential**, issued as the fulfilment of a booked walkthrough, after the qualifying form. **Published shared credentials are rejected**; an open unqualified trial is rejected | `STEP0-001` Reading B — `../01-governance/CommitteeSystem.md` §13.1 |
| Automatic or manual issuance? | **Manual and time-limited to start.** Automatic issuance is a later option, and only once abuse and load evidence exists | §13.3 gate item 2 |
| Is access time-boxed? | **Yes, and the expiry is stated *before* the form**, not after submission | D-b |
| Which credential? | **Front Office + Physician. Never Administrator** — an admin credential also destroys the §5 differentiator | D-c |
| Is the request form a qualification step? | **Yes.** It applies GTM §5.1/§5.2, and it *is* RDY-0065's qualification checklist rather than a second artefact beside it | D-a |
| Does it change the primary CTA? | **No.** *"Book a walkthrough"* stays the single primary CTA on every page; the credential is offered *within* that booking, never instead of it | D-d, WEB-001 |

**The ruling is conditional.** Reading B is a finding, not a label. If any of D-a…D-d fails in the
delivered design, it **is** Reading A, and DEM-001 · WEB-001 · GTM-001 · GTM-003 must be reopened in
writing before anything ships.

### Still open beneath the ruling

| # | Question | Where it is answered |
|---|---|---|
| 1 | The **expiry window** — how many days, and what happens at expiry | Task 1 pack |
| 2 | When manual issuance may become automatic, and on what evidence | §13.3 item 2 |
| 3 | The eleven §13.3 security-gate results, each with a residual risk accepted in writing by the Owner, by name | `../01-governance/CommitteeSystem.md` §13.3 |
| 4 | **Where lead-form data lands under Saudi PDPL** — the form is the ruling's load-bearing component and its residency is undecided | §13.8 Q1 · §13.3 item 11 |

### Status

**RULED — `STEP0-001` (Reading B), 2026-08-20.** The website can now specify its call to action:
*Book a walkthrough*, with the evaluation credential as what the booking delivers. Build is gated on
the §13.3 security items, not on a further access decision.

---

## 2. Challenge 2 — The seeded data decays, and today is already empty

This is the big one, and it is reproducing right now.

In a guided demo you talk over it. In a self-serve trial, **the first thing a prospect sees in a
clinic system is an empty calendar and an empty patient board — and it gets worse every day.**

### What the database actually says — measured 2026-08-20, local instance

| | |
|---|---|
| Appointments dated today | **0.** `SUM(pc_eventDate = CURDATE())` on non-recurring rows |
| Non-recurring appointments | **36**, spanning **five** dates, `2026-08-17` → `2026-08-23` (7 / 4 / 16 / 4 / 5), plus **1** recurring series |
| Newest clinical record | `form_encounter` max `date` = `2026-08-19` — the chart ages with the calendar |
| Decay surface | **Six tables**: `form_encounter` (74), `prescriptions`, `lists`, `form_vitals`, `billing`, `documents` — all anchored to the same seed run |
| Recurring series | Hard-stops at `pc_endDate = 2026-10-05` |

### Three corrections to what this file used to say

**1 · "All 37 appointments are dated 2026-08-14" is false.** They span five dates. The figure came
from **`pc_time`**, which is a `datetime` recording *when the row was written* — all 37 rows share
`2026-08-14 06:43:38`, the second the seed ran, because
`interface/main/calendar/add_edit_event.php:635,757` sets `pc_time = NOW()`. The appointment date is
**`pc_eventDate`**. PB-441 measured the wrong column; the symptom it reported was real, the
mechanism was not.

**2 · The `pc_startTime` hazard is a real mechanism that did not cause damage.**
`DATE_ADD(CAST('09:00:00' AS TIME), INTERVAL 5 DAY)` returns **`129:00:00`** — confirmed on this
project's own MariaDB. But the live column reads `min 08:00:00`, `max 16:30:00`, **zero NULLs**. So
the statement recorded for PB-454 is not what ran. Closed as a defect; retained as a rule — **no
date-shifting job may write to `pc_startTime` or `pc_endTime`**, and none needs to.

**3 · The weekday pattern is wrong for a Saudi clinic.** Appointments fall on Mon/Tue/Wed/**Sat**/Sun
— four on the Saudi weekend, **zero on Thursday**, a working day. Before PB-454's `+5 DAY` the
16-visit cluster sat on a **Friday**. A raw-day shift rotates weekday meaning; that is the shape of
fix this file used to point at.

### The fix — decided as `SEED-001`, 2026-08-20

**Whole-week re-base, plus a one-time correction to the Saudi working week.**

- The offset is always a **multiple of 7 days**, so every appointment keeps its weekday forever.
- **Every seeded clinical date** moves by the same offset, so time-of-day and the relative
  chronology between an appointment, its encounter, its prescription and its bill are preserved
  exactly. Recurring-event end dates are included and given runway.
- **One time**, Saturday appointments move −2 days and Friday −1, both landing on Thursday: that
  clears the Saudi weekend and fills the empty working day in a single move, inventing no new rows.
  Verified distribution afterwards: **Sun 5 · Mon 7 · Tue 4 · Wed 16 · Thu 4 · Fri 0 · Sat 0.**

**The sequencing detail that matters.** Today's computed offset is **0** — the seed already sits in
the current week — yet today is empty. **The weekday correction is what closes the symptom now;** the
whole-week re-base is what keeps it closed as weeks pass. Doing only the re-base today changes
nothing.

### Verification status — read this before acting

Everything above is the **local instance**. It establishes what the SQL does and what the local data
looks like. It does **not** establish the state of `demo.skyeagle.uk`, and `SEED-001` requires that
host to be checked, and backup and rollback both exercised there, before anything is scheduled.

### Status

**RULED — `SEED-001`, 2026-08-20. Implementation prepared, not executed.**
`docs/evidence/ubuntu-infra-scripts/05-seed001-demo-date-rebase.sh`. Next action is
`sudo ./05-seed001-demo-date-rebase.sh check` on the demo host — read-only, changes nothing.

---

## 3. Challenge 3 — One database, shared by every visitor

The product is **not multi-tenant** (GAP-0043 / L-07). That is precisely why the strategy
deferred a self-serve sandbox in the first place.

Visitor A registers a patient; Visitor B sees it. Visitor A edits something; Visitor B's trial is
degraded.

### What already exists — and what it actually is

A **proven reset**: two byte-identical repeat resets from the v4 baseline, recorded at **PB-424**.
That result is real. But `docs/evidence/EV-044-demo-reset-runbook.md` is a **local Windows manual
runbook** — PowerShell, `Stop-Process -Name httpd`, baseline artefacts in a protected folder on the
local machine. It contains **no** mention of `skyeagle`, `ubuntu`, `demo-openemr`, `cron`, `systemd`
or any date re-base.

**So what exists is a repeatable reset on the wrong host.** On `demo.skyeagle.uk` the mechanism and
the baseline dump do not exist at all.

### What is missing

Not just a schedule. In order: a **baseline dump taken on the demo host**; that baseline **re-baked
to include post-baseline fixes**; a **Linux reset procedure**; **restore verification**; and only
then a schedule.

The re-baking step is not hypothetical. PB-442 recorded that a reset silently reverted
`facility.primary_business_entity` to `0`, a previously-fixed value — confirmed still `0` on the
local instance 2026-08-20. **A scheduled job that periodically undoes fixes is worse than no
scheduled job.**

### Status

**RULED — `SEED-001`, 2026-08-20: the reset is separated from the re-base and stays MANUAL and
Owner-authorised** until a clean demo-host baseline exists, folds in all post-baseline fixes, and
passes restore verification.

**This deviates from the "one job" rule that used to close this section, deliberately and on
evidence.** The original reasoning — *a reset that restores the frozen dates re-creates Challenge 2
every time it runs* — is still correct, and is not discarded. It becomes a **binding condition on
the reset**: when the reset is eventually scheduled, the re-base must run inside the same job, after
the restore. Until then there is no reset to bundle with, and bundling would only hold the safe half
hostage to work that has not started.

---

## 4. Challenge 4 — Nobody is there to say the things a presenter says

The demo no-go register (§40) assumes a human is in the room. Two examples:

1. **Opening Module Manager auto-registers three modules** — a state change a curious visitor
   will trigger, and one the register currently handles by having the presenter mention it before
   the prospect notices.
2. **The invoicing boundary** — *"we do not issue your tax invoice and we do not submit insurance
   claims"* — is specified as **spoken before any billing screen appears** (§40 row 7, and the
   D-7 script's step 14). It is a permanent discipline, not a temporary caveat.

**Self-serve has no speaker.**

### What it turned out to require — far less than expected

Every no-go item was re-classified for an unaccompanied visitor as **restrict the route** (cannot
reach it) / **on-screen notice** (reaches it, qualified in place) / **accept and disclose** (reaches
it, the site said so first).

**The answer is that `STEP0-001` D-c already did most of it.** Choosing Front Office + Physician and
never Administrator restricts **12 of the 14 rows** with no new UI — including both rows this section
named. Measured 2026-08-20 as authenticated HTTP GETs for both roles, with OpenEMR's error log read
back to confirm each denial was a real ACL decision:

| § | No-go item | Measured, 2026-08-20 | Self-serve handling |
|---|---|---|---|
| 1 | Admin → Backup | HTTP **403** both roles; log `admin/super: Backup` | **Already restricted** |
| 2 | C-CDA | Nothing listening on `127.0.0.1:6661`; disabled-module paths throw `AccessDeniedException` (`ModulesApplication.php:102`) | **Already restricted** |
| 3 | External integrations | Address Book **403** (`admin/practice`); all integration config sits behind admin routes, all 403 | **Already restricted** |
| 4 | Any API screen | 0-byte 200; log `ClientAdminController::checkSecurity` **CRITICAL** ACL failure | **Already restricted** · *blank response is a denial-UX defect, see below* |
| 5 | Patient portal | HTTP **403** both roles | **Already restricted** |
| 6 | Any empty screen | Today's appointments = 0 | **Handled by `SEED-001`**, not by a notice |
| 7 | Billing without the boundary spoken | `billing_report` **403** · `front_payment` **403** · `pos_checkout` **403** · `sl_eob_search` **403** · Fee Sheet denied (`form: Fee Sheet`) — **both roles** | **Accept and disclose.** No reachable billing screen exists; the site states billing is out of scope for the evaluation |
| 8 | Module Manager, casually | 14-byte denial stub; `admin\|manage_modules` | **Already restricted** — the three-module auto-registration cannot be triggered |
| 9 | Arabic PDF output | **Custom Report REACHED by Physician (HTTP 200, 4,114 B)**; Front Office **403** | **RESTRICT THE ROUTE** — `NOGO-001`, demo Physician role only |
| 10 | RDY-0050 report set | Already protected; PB-013, 127/127 PASS | **Already restricted** |
| 11 | The `admin` account | Not a route — a materials rule | **Already satisfied** by `STEP0-001` D-c: no admin credential is ever issued |
| 12 | Background services | HTTP **403** both roles | **Already restricted** |
| 13 | Telehealth · dispensary · group therapy | HTTP **401**, disabled-module denial | **Already restricted** |
| 14 | `full_new_patient_form` toggle | Globals **403** both roles | **Already restricted** |

**Row 8 is closed by that table, not by a notice.** Module Manager returns a 14-byte denial stub, so
the three-module auto-registration a curious visitor might trigger **cannot be triggered.**

**Row 7 is closed more strongly than a notice could.** Every billing surface — billing manager,
front payment, point-of-sale checkout, EOB search, and the Fee Sheet — is denied to both roles. The
line specified as *spoken before any billing screen appears* has **no screen to precede**, and
`STEP0-001`'s qualifying form already asks the invoicing question before login. **Ruled `NOGO-001`:
accept, and say so explicitly on the site** — one claim-reviewed line stating billing is out of
scope for the evaluation and why, so the absence is a disclosed boundary rather than a gap the
visitor discovers.

### The two things actually left

**1 · Row 9, the one genuine register residue.** The Physician role reaches Custom Report (HTTP 200,
4,114 B), the live path to Arabic PDF output that mPDF cannot shape. **Ruled `NOGO-001`: restrict the
route for the demo Physician role only**, leaving production-role defaults untouched. Revisit when
Arabic PDF shaping is fixed, or when a render/export permission split allows the on-screen report
without the broken export. Prepared implementation:
`docs/evidence/ubuntu-infra-scripts/06-nogo001-demo-physician-role.php` — `check` run and clean,
**`apply` not yet executed**.

**2 · The denial UX, which is not in the register at all.** Five different shapes were measured for a
blocked route: 403 with a 1.8 KB page, 200 with 0 bytes, 200 with 14 bytes, 401 with 0 bytes, and
**HTTP 500 on the Fee Sheet**. A presenter narrates past a blank page; **a visitor alone concludes
the software is broken.** Making a locked door look locked is the remaining work — one branded
"not included in this evaluation" response, not fourteen notices.

### Verification status — read this before acting

Local instance only, same caveat as `SEED-001`. Only GET was probed, not POST. The Front Office leg
of a third probe covering the clinical journey failed on a harness fault and was **not** re-derived
— PB-425 and PB-442 remain the evidence for that journey.

**And the ACL change must be folded into the clean baseline**, or the first reset silently re-grants
Patient Reports — the same regression shape PB-442 recorded.

### Status

**RULED — `NOGO-001`, 2026-08-20.** 12 of 14 rows already enforced; row 9 restricted; row 7 accepted
and disclosed. Two open items: the consistent denial page, and one line of site copy for `04-content`.
This was never only a UX task — several of these lines keep the product inside the §32
prohibited-claims control, and a billing screen reached with no boundary statement is a
claim-discipline failure, not merely an awkward moment.

---

## 5. The marketing unique advantage — issue two credentials, not one

**Issue two credentials, not one — a Front Office login and a Physician login — and tell the
visitor on the marketing page:**

> **"Log in as both, open the same patient, and see the difference."**

### Why this is the strongest move available

That turns the trial into the **Pillar 1 proof, unaccompanied.**

| Reason | Detail |
|---|---|
| It is the strongest thing the product owns | Demonstrated role modelling is marketed by **0 of 16** scored competitors in any comparable form; audit integrity by **0 of 16**. This is measured white space, not an assumption |
| It needs **zero development** | The role accounts exist. The permission model exists. Nothing has to be built |
| The proof already exists as stills | **SS-03** (Front Office — the clinical area absent) and **SS-04** (Physician — the same chart, fuller) are captured, reviewed and publication-ready. The trial simply lets the prospect reproduce them live |
| It converts the constraint into the pitch | The visitor does not read a claim about access control. They *perform* it |

### The inverse, stated so it does not happen by accident

**A single Administrator credential would do the opposite.** It hides the differentiator — an
administrator sees everything, so there is no boundary to discover — and it lets visitors change
globals, ACLs and data.

### Status

**PROPOSED — recommended as the design principle for the trial's access model.** Resolves
Challenge 1's "which credential" question and constrains Challenge 4's restriction design, because
neither issued role is an administrator.

---

## 6. How these five relate

The four challenges are not independent, and solving them in the wrong order wastes work.

```
Challenge 2 (decaying dates) ─► SEED-001: scheduled whole-week re-base   [ready to verify]
                             └─► + one-time fix to the Sun-Thu clinic week
Challenge 3 (shared DB)      ─► SEED-001: reset stays MANUAL           [blocked: no
                                 demo-host baseline exists yet]

Advantage 5 (two credentials) ─► answers Challenge 1's "which credential"
                              └─► and bounds Challenge 4, since neither role is admin

Challenge 1 (the door)      ─► RULED: STEP0-001, gated evaluation credential
                              └─► parameters remain: expiry, manual→automatic, §13.3 gate
Challenge 4 (no presenter)  ─► RULED: NOGO-001 — 12 of 14 rows already enforced by D-c
                              ├─► row 9 restricted for the demo Physician only
                              └─► left: one denial page + one line of site copy
```

**Suggested order of work:**

1. **Re-verify Challenge 2 on `demo.skyeagle.uk`.** Everything else is guesswork until the live
   state of that specific host is known. Run `05-seed001-demo-date-rebase.sh check` — read-only.
2. ~~**Schedule reset + date re-base as one job**~~ — **split by `SEED-001`.** Apply the weekday
   correction, exercise `run` and `rollback` on the demo host, then `install` the re-base timer.
   The reset stays manual until a demo-host baseline exists and is re-baked.
3. **Adopt the two-credential model** (§5) — closes the *which* half of Challenge 1. **Ruled by
   `STEP0-001` D-c: Front Office + Physician, never Administrator.**
4. ~~**Decide the access mechanism**~~ — **closed by `STEP0-001`**: gated evaluation credential,
   manual and time-limited to start. What remains is to **run the §13.3 security gate** and set the
   expiry window.
5. ~~**Re-classify §40 for self-serve**~~ — **done, `NOGO-001`.** What remains is to run
   `06-nogo001-demo-physician-role.php apply` on the demo host, build the consistent denial page,
   and write the row 7 disclosure line.

---

## 7. What this file does not yet contain

To be added as it is produced:

- Information architecture and page inventory
- Per-page specifications and copy decks
- Proof-asset placement — where SS-01…SS-12 and the audit-integrity recording go
- The claim-discipline review pass against §32

*(Method, bilingual/RTL practice and acknowledgements are in Part II, §10–§12. Tooling has moved
to [`../02-strategy/Tools-and-Skills.md`](../02-strategy/Tools-and-Skills.md). Technical architecture is in [`../06-technical/Architecture.md`](../06-technical/Architecture.md), §13.)*

---

## 8. Constraints that already bind everything above

Carried forward, not re-litigated here:

- **No price figure** may be published — PRC-003 is BLOCKED.
- **No competitive frequency figure** ("0 of 16" and similar) may appear on any page — §32
  item 26. The mechanism may be described; the number may not be printed. *The internal figures
  quoted in §5 above are internal reasoning, not publishable copy.*
- **No uptime, performance, ROI or implementation-time figure** — none has been measured.
- **Nothing Saudi-regulatory** in either language — NPHIES, ZATCA, CHI, VAT, Hijri, Iqama, SFDA.
- **Every mandatory qualification travels in the same visual unit as its claim**, never as a
  footnote.
- **The open-source origin is disclosed**, not obscured.
- **The `admin` credential appears in no material, ever.**

**These constraints also bind [`../02-strategy/MarketingMethod.md`](../02-strategy/MarketingMethod.md), [`../06-technical/Architecture.md`](../06-technical/Architecture.md), and [`../02-strategy/Tools-and-Skills.md`](../02-strategy/Tools-and-Skills.md) in full.** They
outrank every external framework, skill pack, industry convention and technical recommendation
named anywhere in this folder. Where a marketing best practice and §32 disagree, **§32 wins**.

---
