# THIQA — MARKETING WEBSITE

**Working document.** Started 2026-08-20. This is the working file for the Thiqa marketing
website. It begins with the four challenges the site must solve and the one advantage it should
be built around, because those four constraints shape every later decision — information
architecture, page inventory, copy, and the conversion mechanism itself.

Nothing here is a locked decision yet. Later sections (IA, page specifications, copy decks,
bilingual/RTL handling, proof-asset placement, build choices) will be added to this file as they
are produced.

---

## 0. Context this file assumes

| Fact | Value |
|---|---|
| Product | **Thiqa** (ثقة) — outpatient clinic management system and EMR, implemented, hosted and supported. Built on open-source OpenEMR, disclosed |
| Vendor | **SkyEagle** · `skyeagle.uk` |
| Live demo instance | **`https://demo.skyeagle.uk`** — verified 2026-08-19: valid TLS, HTTP→HTTPS 301, HSTS, `X-Content-Type-Options: nosniff`, Cloudflare in front, `<title>Thiqa Login</title>`, no user-facing stock OpenEMR identity |
| Demo host | Ubuntu `demo-openemr`, with systemd-managed backup, background-service scheduler and monitoring already running |
| Decision that creates these challenges | The marketing website will drive prospects **into the live demo as a self-serve trial**, rather than only into a booked, presenter-guided walkthrough |

**Why that decision matters.** The locked GTM strategy (DEM-001) deliberately **deferred** a
self-service seeded demo, on the grounds that it *"requires seeding plus per-visitor isolation;
GAP-0043 makes isolation manual."* Using the running demo as a customer trial reverses that
deferral. That is a legitimate call to make — but it moves four problems from *"the presenter
handles it"* to *"the product and the website must handle it."* Those four are §1–§4 below.

---

## 1. Challenge 1 — The gap: it's a login wall, not a trial

A prospect clicking through from the marketing site hits a username/password box. Nothing lets
them in.

So *"demo trial for customers"* needs a decision that has not yet been made: **do they get a
credential automatically after a short form, or do you issue one by hand?**

### Why it matters

This is the single point at which the whole marketing funnel either converts or stops. Every
other page on the site exists to get a qualified prospect to this door. Today the door is shut,
and the site has nothing to say about how it opens.

### What has to be decided

| Question | Options | Consequence |
|---|---|---|
| How does a visitor get in? | Automatic credential after a short form · manually issued by the founder · a published shared credential on the site | Automatic maximises reach and is the only version that works while you sleep; manual keeps qualification control but caps volume; published invites automated abuse |
| Is access time-boxed? | Yes / no | Untimed access to a shared instance compounds Challenge 3 |
| Is the request form a qualification step? | Yes / no | The GTM's funnel puts qualification *before* the demo; a self-serve trial inverts that unless the form does the work |

### Status

**OPEN — decision required before the website can specify its primary call to action.**

---

## 2. Challenge 2 — The seeded data is date-frozen, and it's already showing empty screens

This is the big one.

All 37 seeded appointments are dated **2026-08-14**. Anything the app filters by *"today"*
therefore renders empty. Confirmed live at **PB-441**: the Flow Board showed
**`Total patients: 0`**, and the current-week calendar was nearly bare.

In a guided demo you talk over it. In a self-serve trial, **the first thing a prospect sees in a
clinic system is an empty calendar and an empty patient board — and it gets worse every day.**

### Verification status — read this before acting

This was verified on the **local instance**. **It needs re-checking on `demo.skyeagle.uk`
specifically before you send any traffic there.** The two instances are not the same system, and
this file does not assume the local finding transfers.

### The fix

Cheap, and already specified. §16.2 of the readiness document requires appointment data to be
**re-based relative to today on every reset**:

> Date-relative data | Appointments are "the current week". **Re-base on every reset**, or the
> demo shows last month.

The requirement exists. It simply **is not wired to a schedule.** Wiring it is the fix.

### Status

**OPEN — highest-impact item. Re-verify on `demo.skyeagle.uk`, then schedule the re-base.**

---

## 3. Challenge 3 — One database, shared by every visitor

The product is **not multi-tenant** (GAP-0043 / L-07). That is precisely why the strategy
deferred a self-serve sandbox in the first place.

Visitor A registers a patient; Visitor B sees it. Visitor A edits something; Visitor B's trial is
degraded.

### What already exists

A **proven reset** — two byte-identical repeat resets from the v4 baseline, recorded at
**PB-424**. The mechanism works and has been demonstrated twice.

### What is missing

It **needs to run on a schedule, not on demand.** A reset that only fires when someone remembers
to fire it does not survive contact with unattended public traffic.

### Status

**OPEN — the reset exists and is proven; the schedule does not.** Interacts directly with
Challenge 2: the same scheduled job should perform both the reset and the date re-base, because a
reset that restores the frozen dates re-creates Challenge 2 every time it runs.

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

### What this requires

Those spoken lines have to become **on-screen notices or restricted routes**. Every no-go item in
§40 needs re-classifying for an unaccompanied visitor:

| Handling | Meaning |
|---|---|
| **Restrict the route** | The visitor cannot reach it at all |
| **On-screen notice** | The visitor reaches it, and the qualification is displayed before or beside it |
| **Accept and disclose** | The visitor reaches it, and the site says so in advance |

### Status

**OPEN — §40 needs a self-serve column.** Note that this is not only a UX task: several of these
lines exist to keep the product inside the §32 prohibited-claims control. A billing screen reached
with no boundary statement is a claim-discipline failure, not merely an awkward moment.

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
Challenge 2 (frozen dates)  ─┐
                             ├─► one scheduled job fixes both
Challenge 3 (shared DB)     ─┘

Advantage 5 (two credentials) ─► answers Challenge 1's "which credential"
                              └─► and bounds Challenge 4, since neither role is admin

Challenge 1 (the door)      ─► still needs its own decision: automatic, manual, or published
Challenge 4 (no presenter)  ─► needs §40 re-classified for an unaccompanied visitor
```

**Suggested order of work:**

1. **Re-verify Challenge 2 on `demo.skyeagle.uk`.** Everything else is guesswork until the live
   state of that specific host is known.
2. **Schedule reset + date re-base as one job** — closes Challenges 2 and 3 together.
3. **Adopt the two-credential model** (§5) — closes the *which* half of Challenge 1.
4. **Decide the access mechanism** — the *how* half of Challenge 1.
5. **Re-classify §40 for self-serve** — Challenge 4.

---

## 7. What this file does not yet contain

To be added as it is produced:

- Information architecture and page inventory
- Per-page specifications and copy decks
- Bilingual English/Arabic handling, and RTL
- Proof-asset placement — where SS-01…SS-12 and the audit-integrity recording go
- Build and hosting choices for the marketing site itself
- The claim-discipline review pass against §32

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
