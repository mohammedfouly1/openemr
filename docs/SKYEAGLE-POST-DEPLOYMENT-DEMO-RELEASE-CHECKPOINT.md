# SkyEagle Post-Deployment → Full Demo & Release Certification — Continuation Checkpoint

**Authoritative continuation file for this programme.** A future session resumes from here
without repeating completed work. Update after every stage. Never rewrite history below —
append.

---

## Stage 1 — State Reconstruction (2026-08-28)

### Timestamp / identity

| Item | Value |
|---|---|
| Timestamp | 2026-08-28, continuing directly from the live authenticated browser certification session |
| Local branch | `master` |
| Local HEAD | `131491f8dffca551f7ce6515a964a45e880ced0b` |
| origin/master | `131491f8dffca551f7ce6515a964a45e880ced0b` (in sync) |
| Deployed SHA (Ubuntu) | `663035f0bda91c09a0238de561d25069035914e8` |
| VM identity | `demo-openemr`, GCP project `project-c2365b97-e364-4ea0-bc2`, zone `us-central1-a` |
| DB identity | `openemr` schema on the VM's local MariaDB (loopback-only) |
| Live URL | `https://demo.skyeagle.uk` |

**Note on the SHA gap above (663035f0b deployed vs 131491f8d local):** expected and correct,
not a divergence to chase. The two local-only commits since the deployment
(`docs(deploy): record SkyEagle Ubuntu demo deployment certification` and
`docs(deploy): record live authenticated browser certification on demo.skyeagle.uk`) are
**documentation-only** — they record what was already done to the VM, they don't change
application behaviour. Re-deploying the VM to pick up two docs commits would be pointless
churn; the VM's `663035f0b` is the actual certified application state and remains correct.

### Git — verified, not assumed

- `git status --short` reports the known DriveFS-noise pattern (361 lines, all `M`, zero real
  content — `git diff --stat` on a sample confirms only CRLF-warning lines, no actual diff).
  Consistent with every prior pass this program; not re-litigated further here.
- 5 worktrees exist (`.claude/worktrees/agent-*` ×3, `OpenEMR.worktrees/sds`,
  `worktrees/OpenEMR/jade-ibis`). **Not touched.** Listed for completeness only, per the
  standing instruction never to clean worktrees that aren't this session's own.
- No unexplained divergence anywhere.

### Ubuntu — verified live, read-only

| Check | Result |
|---|---|
| Deployed HEAD | `663035f0bda91c09a0238de561d25069035914e8` — matches certified source exactly |
| `apache2` | active |
| `mariadb` | active |
| `openemr-background-services.timer` | active |
| `openemr-monitoring.timer` | active |
| `openemr-offsite-backup.timer` | active |
| Disk | 89G free / 96G (8% used) |
| HTTPS (`login.php?site=default`) | 200 |

### Database — read-only verified

| Item | Value |
|---|---|
| SkyEagle module | `mod_name='SkyEagle Branding'`, `mod_directory='oe-module-skyeagle-branding'`, `mod_active=1` |
| Facility (id=3) | `International Healthcare Center` |
| Branding globals | `openemr_name=SkyEagle`, `login_tagline_text='Better care begins here.'`, `main_menu_logo_title='SkyEagle Health Information System'`, `online_support_link='https://skyeagle.uk/en/contact'`, `user_manual_link='https://skyeagle.uk/en/resources'` — all correct |
| Portal | `portal_onsite_two_enable = 0` — **confirmed disabled**, matches prior finding |
| Patients | 30 |
| Encounters | 72 |
| Appointments | 37 (36 seeded + 1 pre-existing, per the historical seeder's own documented target) |
| Prescriptions | 12 |
| Billing charges | 36 |
| Insurance payers | 2 |
| Users | `admin` (active=1), `phimail-service`/`portal-user`/`oe-system` (system accounts, inactive), 6 named demo role accounts (`n.alqahtani`, `y.alharbi`, `s.almutairi`, `r.aldosari`, `k.alotaibi`, `m.alzahrani`, all active=1) |

**New observation, not previously flagged on this host:** the `admin` account is `active=1`
here. The Windows dev instance's own local convention (RDY-0011/RDY-0017, recorded in
`CLAUDE.local.md`) is that `admin` should never appear in a customer-facing demo and its
password was deliberately rotated off the installer default there. Whether the same applies
to this specific Ubuntu demo host, and whether `admin` here still carries an installer-default
or comparably weak credential, is **not yet independently checked** — flagged for Stage 12
(Security & Hardening Review), not assumed either way here.

### Browser — verified live

The browser session from the immediately-preceding live-certification pass is still alive and
authenticated (Arabic, tab title `سكاي إيجل`) against `https://demo.skyeagle.uk` — direct,
current proof the application is genuinely rendering and reachable, not inferred from the
HTTPS check alone.

### Stage-1 output

```
STATE RECONSTRUCTION: PASS
```

No unexplained divergence found anywhere. Everything from the prior deployment and live
certification passes holds exactly as recorded.

---

## Scope assessment before proceeding past Stage 1

The remaining 23 stages of this programme span several genuinely large, independent efforts:
designing and populating a new fictional golden demo dataset (patient, insurance, appointment,
encounter, medication, labs, billing, claims) directly against the live production demo
database; a 6+ role ACL and functional-workflow certification requiring individual logins and
UI actions per role; a full end-to-end reversible clinical/billing/claims workflow walkthrough;
a bounded security hardening review; a non-destructive disaster-recovery restore drill against
isolated infrastructure; CI/documentation reconciliation; and three new substantial
documentation artifacts (sales demo runbook, marketing screenshot plan, marketing video plan)
plus actual marketing capture.

This is reported back to the Owner rather than executed unattended in one continuous pass, per
this program's own Section 29 instruction to stop and check in on genuinely large,
judgment-heavy, or live-system-mutating scope rather than silently barrel through it. Stage 1
is complete and clean; the next action is Owner direction on pacing/priority for Stages 2
onward, not a unilateral decision to proceed through all of them.

**Next exact action:** awaiting Owner direction on which of Stages 2–24 to execute now, and in
what order/depth.

---

## Stage 2 — Closing the Small Visual Gaps (2026-08-28)

Owner direction (via structured selection): "Close the small visual gaps first" — Stage 2 only
(dark theme, tablet, mobile, print, PDF), report back before touching demo data. All actions
below are read-only or trivially reversible; no demo data was created, modified, or deleted.

### 5.1 Authenticated dark theme

**Mechanism found:** `General Theme` is a genuine **per-user** preference
(`interface/super/edit_globals.php?mode=user`, `form_1`, options `style_light.css` /
`style_dark.css`), distinct from the tenant-wide Admin > Globals > Appearance setting this
programme has deliberately never touched. Scoped to the single acting demo account
(`n.alqahtani`), takes effect after logout/login (per the page's own note), fully reversible.

**Procedure:** captured original value (`style_light.css`, the default) → set to
`style_dark.css` → saved → logged out/in (dark theme confirmed active site-wide, English UI) →
verified pages → set back to `style_light.css` → saved → logged out/in → confirmed restored to
Light (screenshot-verified, matches pre-change state exactly).

**Verified clean under dark theme:** top nav bar, header "SE" logo mark (renders as a light
glyph on dark background, no white-box artifact), user-account dropdown menu, Calendar (mini
calendar widget, provider columns, all readable), Patient Finder search table (links, rows,
input fields all good contrast), Patient Dashboard (section cards, edit icons, headings — the
small circular "no photo" placeholder avatar is a pre-existing light default unrelated to
branding, present identically in light mode), Patient Ledger form controls (date inputs,
Submit/Export buttons), Patient Reports page (checkboxes, Generate/Download buttons). No Thiqa
residue observed on any screen visited.

**Defect found (pre-existing, not branding-introduced):** the Patient Ledger report's
highlighted rows use **hardcoded inline light backgrounds** —
`background-color:#FFFFDD` (encounter-group row), `background-color: var(--gray300)`
(Encounter Balance row), `background-color:#DDFFFF` (Grand Total row) — while the dark theme's
global text color computes to `rgb(245,246,248)` (near-white) on all three. Confirmed via
direct DOM/computed-style inspection (`getComputedStyle`), not hover state, not a screenshot
artifact:
```
{"styleAttr":"background-color:#FFFFDD;","computedColor":"rgb(245, 246, 248)", ...}
{"styleAttr":"background-color: var(--gray300)","computedColor":"rgb(245, 246, 248)", ...}
{"styleAttr":"background-color: #DDFFFF;","computedColor":"rgb(245, 246, 248)", ...}
```
Result: near-white text on a near-white/pale-yellow/pale-cyan background — the encounter-group
label, "Eye exam, established patient" line item, and Grand Total row are effectively illegible
in dark mode (screenshot evidence captured). This is stock OpenEMR ledger-report styling
(classic `#FFFFDD`/`#DDFFFF` highlight colors used across core billing reports for a long time,
not something introduced by the SkyEagle branding work) that was never designed against a dark
theme. Not fixed here — flagged as a finding, per Stage 2's scope being verification/evidence
gathering, not a code-fix pass.

**Verdict: `AUTHENTICATED DARK THEME: CONDITIONAL`** — theme mechanism, restoration, and the
overwhelming majority of authenticated screens are clean; one reproducible, evidenced
contrast/legibility defect on the Patient Ledger report's highlighted rows keeps this from a
clean PASS.

### 5.2 / 5.3 Tablet (~768px) / Mobile (~390px)

`resize_window` (768×1024) was called and reported success, but a direct
`window.innerWidth` check immediately after read back **1366** (unchanged) — re-confirms the
same genuine tool limitation already documented earlier in this programme: this tool does not
actually change the real viewport on a live, extension-connected (non-headless) Chrome browser,
only the reported outer window frame. No CDP-level device-metrics override is exposed by this
toolset, so there is no legitimate route available in this session to produce a real mobile/tablet
viewport and honestly verify responsive layout live against `demo.skyeagle.uk`.

**Verdict: `TABLET LAYOUT: NOT VERIFIED`, `MOBILE LAYOUT: NOT VERIFIED`** (tool limitation, not a
product finding) — not fabricated. Note: `tests/Tests/Isolated/BrandingCi/LoginLayoutResponsiveContractTest.php`
already exists as a static-source regression guard for the one responsive defect previously found
and fixed on the login page (V-02/V-03: the `.vertical-band` breakpoint and mobile padding) — that
prior, real defect is independently covered and does not need re-verification here.

### 5.4 Print

`Ctrl+P` triggers a native OS print-preview dialog that renders outside the page DOM — already a
documented tool limitation (not screenshot-capturable via this browser automation). As a
legitimate alternative, inspected the actual loaded `@media print` CSS rules via the DOM/CSSOM
(not fabricated from source reading — read from the live, rendered document): only generic
Bootstrap print resets were found (`.d-print-*` utilities, shadow/border resets, `#report_parameters`
visibility toggle on report pages). **No explicit rule was found that forces the dark theme's
background back to light/white specifically for print.** In practice, most browsers omit CSS
background colors from printed output by default (unless the user has enabled "print background
graphics"), so a printed page is likely to render on a plain white page regardless of the active
theme — but this could not be confirmed by rendering an actual print preview, so it is not
asserted as fact.

**Verdict: `PRINT LAYOUT: NOT VERIFIED`** (tool limitation on the rendering side; static CSS
inspection found no red flag, but did not positively confirm safe print output either).

### 5.5 PDF

Found a genuine, safe, existing PDF-generation route: Patient > Report tab > "Patient Report"
section (Demographics + Billing checked) > **Download PDF** button
(`POST /interface/patient_file/report/custom_report.php`), tested against the existing synthetic
demo patient Hessa Alamri (SYN-0015) — read-only, no data created/modified.

The browser automation session's own network monitor reported this request as **HTTP 503**, twice
in a row. Investigated via the VM's Apache access log (read-only `tail`/`grep` over SSH) rather
than accepting the client-side signal at face value: the **origin server actually returned HTTP
200 with a ~143KB response body both times** —
```
POST /interface/patient_file/report/custom_report.php HTTP/1.1" 200 143061
POST /interface/patient_file/report/custom_report.php HTTP/1.1" 200 143061
```
— i.e., the PDF was genuinely generated successfully server-side on both attempts. The 503 the
browser tool observed did not originate at the OpenEMR/Apache layer; site traffic is
Cloudflare-proxied (`104.22.x.x`/`172.7x.x.x` edge IPs throughout the logs), so the most likely
explanation is an edge/proxy-layer anomaly between Cloudflare and the automated browser session,
not an application defect. Not chased further (would just repeat the same result); noted honestly
rather than either claimed as a clean PASS or written up as a false FAIL.

**Verdict: `PDF GENERATION: CONDITIONAL`** — server-side generation confirmed working (verified
at the access-log level, not inferred); end-to-end delivery to a real user's browser was not
positively confirmed in this session due to the observed edge-layer 503. Worth a plain manual
browser retest outside of automation if this matters for a specific demo/sales moment.

### Stage 2 summary

```
AUTHENTICATED DARK THEME: CONDITIONAL  (one evidenced, pre-existing ledger-report contrast defect)
TABLET LAYOUT:             NOT VERIFIED (tool limitation — resize_window does not affect real viewport)
MOBILE LAYOUT:              NOT VERIFIED (same tool limitation)
PRINT LAYOUT:               NOT VERIFIED (tool limitation; static CSS check found no red flag)
PDF GENERATION:             CONDITIONAL (origin confirmed 200/success; client-side delivery unconfirmed)
```

All changes made during this stage (the per-user theme toggle) were fully reverted and verified
restored. No demo data was touched. No destructive or irreversible action was taken.

**Next exact action:** report back to Owner with the above before proceeding into Stage 3
(demo dataset design/population) or any other later stage, per the same pacing agreement as
Stage 1.

Owner direction (2026-08-28): re-pasted the full master prompt verbatim and authorized proceeding
through all remaining stages continuously, checkpointing after each, stopping only on a genuine
Section-29 condition.

---

## Stage 3 — Demo Data Inventory (2026-08-28)

All queries below are read-only (`SELECT`/`SHOW`/`DESCRIBE`) against the live `openemr` schema via
`sudo mariadb --defaults-extra-file=/root/.my.cnf.openemr-backup`. No writes.

### Users

| Username | Name | active | authorized | facility | ACL group |
|---|---|---|---|---|---|
| admin | Administrator | 1 | 1 | 3 | Administrators |
| phimail-service | phiMail Gateway | 0 | 0 | — | (none) |
| portal-user | Patient Portal User | 0 | 0 | — | (none) |
| oe-system | System Operation User | 0 | 0 | — | Administrators |
| n.alqahtani | Noura Alqahtani | 1 | 0 | 3 | Administrators |
| y.alharbi | Yousef Alharbi | 1 | 1 | 3 | Physicians |
| s.almutairi | Sara Almutairi | 1 | 1 | 3 | Physicians |
| r.aldosari | Reem Aldosari | 1 | 0 | 3 | Front Office |
| k.alotaibi | Khalid Alotaibi | 1 | 0 | 3 | Accounting |
| m.alzahrani | Maha Alzahrani | 1 | 1 | 3 | Clinicians |

Stock OpenEMR ACL groups present: `Administrators`, `Clinicians`, `Physicians`, `Front Office`,
`Accounting`, `Emergency Login` (breakglass) — no dedicated `Lab` or `Pharmacist` group exists in
stock OpenEMR; those functions are covered under `Clinicians`/`Physicians` ACL sections rather than
a separate group, so Stage 7's role matrix will map Lab/Pharmacist checks onto those two groups
rather than expecting a distinct account.

**Coverage: the 6 active named demo accounts already span 5 of the 6 stock groups** (Administrators,
Physicians ×2, Front Office, Accounting, Clinicians) — good baseline for Stage 7 role/ACL testing,
no new demo user accounts appear necessary.

**Finding, not yet investigated further:** `n.alqahtani` (the account used throughout this session's
live browser certification) is a member of the `Administrators` ACL group but has `authorized=0`,
while every other active named account with `authorized=1` is not in `Administrators`. In OpenEMR,
`authorized` primarily governs co-signing/supervising authority rather than login capability
(`active` governs login), so this is not necessarily a defect — flagged for a closer look during
Stage 7 rather than assumed either way here. `admin` (`active=1`) remains the only other
`Administrators`-group account able to log in; its own security posture is Stage 12's concern (per
the Stage 1 flag already recorded above).

### Patients

30 synthetic patients (`SYN-0001`–`SYN-0030`), Saudi-context names, cities cycling through
Riyadh/Jeddah/Dammam/Khobar. **Two exact name+DOB repeats** confirmed (pid 1 "Hessa Alharthi"
1948-01-01 vs pid 29 identical; pid 8 "Talal Alsubaie" 1977-02-22 vs pid 30 identical) — consistent
with a deterministic seeder cycling a small name pool past 26 entries; a pre-existing, low-severity
data-quality note, not a defect introduced by this programme.

**Chart-depth ranking** (encounters / issues / prescriptions / billing rows / forms), top 5:

| pid | pubpid | Name | enc | issues | rx | billing | forms |
|---|---|---|---|---|---|---|---|
| 2 | SYN-0002 | Turki Alqarni | 3 | 3 | 1 | 2 | 6 |
| 1 | SYN-0001 | Hessa Alharthi | 3 | 2 | 1 | 2 | 6 |
| 3 | SYN-0003 | Amal Albishi | 3 | 2 | 1 | 2 | 6 |
| 4 | SYN-0004 | Majed Alshamrani | 3 | 2 | 1 | 2 | 6 |
| 5 | SYN-0005 | Dalal Aldawsari | 3 | 2 | 1 | 2 | 6 |

**Significant finding — pid 2 is disqualified as a golden-patient candidate despite ranking
highest:** its `lists` (problem/allergy) record shows an **active allergy to "Timolol 0.5% eye
drops"**, while its own `prescriptions` record shows the patient is **currently prescribed that
exact drug** (`id=2, patient_id=2, drug='Timolol 0.5% eye drops'`). A live, self-contradictory
allergy/prescription pair is exactly the kind of thing a sales/demo viewer would notice and that
would undermine confidence in the product during a live demonstration. Not fixed in this
read-only inventory stage — carried into Stage 4/6 as a required repair regardless of which
patient is ultimately chosen.

**Recommended golden-patient candidate: pid 3, Amal Albishi (SYN-0003).** Same chart depth as the
next-best clean candidates (pid 1, 4, 5), no allergy/prescription contradiction, and its existing
diagnosis — **Primary open-angle glaucoma** — is the natural, classic ophthalmology demo story that
already matches this dataset's existing fee-schedule codes (92014 Eye exam, 92083 Visual field
exam) and prescription pool (Latanoprost/Timolol/Artificial tears/Prednisolone — all real glaucoma
management drugs). Final selection deferred to Stage 4 (design), not decided unilaterally here.

### Insurance

- **Payers configured:** 2 — `Meridian Gulf Health (SYNTHETIC)` (id 2, CMS ID `SYN001`),
  `Northwind Care Cooperative (SYNTHETIC)` (id 3, CMS ID `SYN002`). No `attn` contact set on either.
- **Policies:** `insurance_data` table is **completely empty (0 rows)** — no patient, including the
  golden-patient candidates above, currently has an insurance policy attached, despite payers
  existing. This is the single largest gap for Stage 4's "Insurance" component — a policy must be
  created via the supported Patient > Insurance workflow for whichever patient is selected.
- **EDI/clearinghouse:** `x12_partners` table is **empty (0 rows)** — no EDI trading-partner
  configured. Directly relevant to Stage 10's claims-capability classification: real EDI claim
  submission is not configured on this instance.

### Clinical

- **Problems/allergies (`lists`):** only 6 of 30 patients (pid 1–6) have any `lists` rows at all —
  one allergy + one medical problem each (pid 2 has an extra allergy, see contradiction above). The
  other 24 patients have an empty problem list.
- **Forms used:** `newpatient` ×72 (the demographics/History form recorded once per encounter — this
  matches the encounter count exactly, i.e. every encounter has this base form), `soap` ×18,
  `vitals` ×12, `eye_mag` ×8 (an ophthalmology exam form — confirms the dataset's ophthalmology
  theme). Clinical documentation depth is real but thin and inconsistent — most of the 72 encounters
  carry only the base demographics form, not SOAP/vitals/exam detail.
- **Medications:** 12 active prescriptions total, one per patient for pid 1–12 (patients 13–30 have
  none), all real glaucoma/ophthalmology drugs (Latanoprost, Timolol, Artificial tears, Prednisolone
  acetate), cycling in that order.
- **Labs:** `procedure_order` table is **empty (0 rows)** — no lab order exists anywhere in this
  database. This is a hard gap for Stage 4's "Laboratory" component; needs to be verified against
  what lab configuration (if any) is installed before deciding whether to populate one.

### Financial

- **Billing (`billing` table):** 36 rows, all `billed=1`. Codes used: CPT4 99213 (Office visit,
  established, $250), 99214 (Office visit, extended, $400), 92014 (Eye exam, established, $350),
  92083 (Visual field exam, $300) — cycling across patients, consistent with the ophthalmology
  theme and with the fee amounts already observed live on Hessa Alamri's ledger in Stage 2.
- **Fee-schedule master (`codes` table) data-quality finding:** the same four CPT codes exist in the
  `codes` master table but are tagged `code_type=12`, which does **not** correspond to any row in
  `code_types` (the real CPT4 type is `ct_id=1`) — and all four have a **NULL default fee**, meaning
  the fee values seen on real billing rows were set ad hoc at billing time rather than pulled from a
  configured master fee schedule. A new encounter using the standard Fee Sheet code-picker for these
  same codes today would show no default price. Not fixed here (Stage 3 is inventory-only); a
  candidate repair item for Stage 6.
- **Claims (`claims` table):** **0 rows** — no claim has ever been generated on this instance.
- **Payments (`payments` table):** **0 rows** — no payment has ever been posted, consistent with the
  `SAR 0.00` payment column already observed live in Stage 2's ledger screenshot.

### Facility

Single facility confirmed (id 3, "International Healthcare Center", Riyadh) — no facility-mismatch
risk for any of the above.

### Stage 3 decision (per the programme's own preference to reuse over create)

Reuse the existing 30-patient, single-facility, single-theme (ophthalmology) dataset and the 6
existing named role accounts as the foundation. New data needed, minimally, to complete a coherent
golden demo journey:

1. An insurance policy record for the chosen golden patient (none exist at all today).
2. A repair of the pid-2 allergy/prescription contradiction, regardless of whether pid 2 is used.
3. A decision on whether to populate a lab order/result (Stage 4 will check what lab support this
   installation actually has before deciding to create one).
4. At least one claim and one payment to demonstrate the billing→claims→payment tail of the journey
   (currently zero of either exist anywhere in the database).

No golden-patient selection, insurance policy, lab order, claim, or payment has been created yet —
that is Stage 4 (design) and Stage 6 (populate), gated behind Stage 5 (backup) per the programme's
own sequencing. Nothing in Stage 3 wrote to the database.

**Next exact action:** proceed to Stage 4 — design the golden demo dataset specification.

---

## Stage 4 — Golden Demo Dataset Design (2026-08-28)

Design only — no writes performed in this stage. All items below are gated behind Stage 5
(backup) before any is executed in Stage 6.

### Selected golden patient: pid 3, Amal Albishi (SYN-0003)

Confirmed via full-profile read: Female, DOB 1982-03-23, Dammam, fictional address ("1002
Fictional Street"), existing standing diagnosis **Primary open-angle glaucoma**, 3 existing
encounters (2026-03-14, 2026-05-28, 2026-08-11) telling a natural glaucoma follow-up arc. The most
recent encounter (encounter 6, 2026-08-11) already carries full documentation: `newpatient` +
`soap` + `vitals` + `eye_mag` forms, and a billed $350 "Eye exam, established patient" (CPT
92014). No allergy/prescription contradiction (unlike pid 2). This patient requires the least new
data to reach a complete, coherent journey and is the design target below.

### Facility

Reuse existing (id 3, "International Healthcare Center", Riyadh) — unchanged.

### Patient

Reuse existing pid 3 as-is — no demographic changes planned. Note for the record: OpenEMR core
`patient_data` has no dedicated Arabic-name column (`fname`/`lname`/`mname` are single, untagged
fields) — the Arabic UI shows this same Latin name under RTL layout/labels, it does not store a
separate Arabic name. Not a defect; documented here so Stage 9 (Arabic workflow) doesn't expect a
distinct Arabic name field that doesn't exist.

### Insurance — NEW

Create one policy for pid 3 against the existing payer **Meridian Gulf Health (SYNTHETIC)**
(id 2) via the supported Patient > Insurance UI (not raw SQL — `insurance_data` has no supported
"safe" direct-SQL path since it interacts with subscriber/relationship logic in the app layer).
Fictional subscriber = self, fictional member/policy ID, effective date backdated to before the
earliest existing encounter (2026-03-14) so it appears active across the whole existing chart
history.

### Appointment — NEW

Both of pid 3's existing appointments (`pc_eid` 9 and 39, both 2026-08-12, statuses `x`/`>`) are
already in the past relative to today (2026-08-28). Create one **future-dated** appointment via
the Calendar UI (role: Front Office / `r.aldosari`) with a Physicians-group provider
(`y.alharbi` or `s.almutairi`), reason "Glaucoma follow-up (SYNTHETIC DEMO)", status Scheduled —
this becomes the live check-in → encounter demonstration in Stage 8, rather than fabricating
history on the two already-past appointments.

### Encounter

No new encounter created in Stage 6 population. Encounter 6 (2026-08-11) stands as reusable chart
history/continuity evidence. The Stage 8 end-to-end workflow walkthrough will check in the new
appointment above and create a genuinely new, live encounter as part of that demonstration —
keeping Stage 6 a data-completeness pass and Stage 8 the actual workflow proof, rather than
pre-fabricating an encounter that Stage 8 would then just replay.

### Diagnosis

pid 3's "Primary open-angle glaucoma" problem exists in `lists` but has **no row in
`issue_encounter`** — i.e. it is a standing problem, not linked to any specific visit. Rather than
retroactively editing encounter 6's history, this link will be made live during the Stage 8
encounter (the natural point where a clinician reviews/reconciles the problem list against the
current visit) — consistent with the same "let the workflow do it" principle as the Encounter
item above.

### Medication — repair + addition

pid 3's only active prescription is "Artificial tears" — not a first-line glaucoma therapy
(artificial tears treat dry eye, not intraocular pressure). Add **Latanoprost 0.005% eye drops**
(already an established drug in this dataset's own formulary, used for other patients) as a second
active prescription for pid 3, via the supported medication/e-prescribe module. This is additive
(existing Artificial tears prescription untouched) and makes the medication list clinically
consistent with the glaucoma diagnosis.

### Laboratory — classified N/A, not populated

`procedure_type` (lab test catalog) and `procedure_providers` (lab facility) are **both empty (0
rows)** — this installation has no lab module configuration at all, not merely an absence of
orders. Standing up a full lab subsystem (test catalog + provider record + order + result) is
configuration work substantially beyond "populate demo data" and was not something any prior
stage of this programme established as needed. Per the master prompt's own conditional language
("if the installed configuration supports it"), this is classified **`LABORATORY: N/A — NOT
CONFIGURED`** rather than forced. Recorded as a known limitation for the release certificate
(Section O of the final certificate); can be revisited as a separate, explicitly-scoped initiative
if the Owner later wants a Lab demo capability.

### Billing

Reuse existing billed rows on pid 3 (encounter 6: $350 Eye exam 92014; encounter 36: $250 Office
visit 99213). The Stage 8 live encounter will generate one new billing row (Visual field exam
92083 — $300, already in the fee schedule and clinically appropriate for glaucoma monitoring)
during the workflow walkthrough itself, not pre-created here.

### Claims — NEW, internal-only

Generate exactly **one** demo claim from pid 3's existing billed encounter(s) to Meridian Gulf
Health, via the supported Billing > Claims UI. `x12_partners` is empty (no EDI trading partner
configured), so external/EDI transmission is not even mechanically possible on this instance —
the claim will exist purely as an internal record, satisfying Stage 8/Stage 10 without any risk of
external submission (consistent with Section 2/29's explicit prohibition on that).

### Payment — NEW

Post one fictional payment (patient copay and/or insurance payment, a plausible fractional amount
against the $350 or $250 billed charge) via the standard Payment posting workflow. `payments` is
currently 0 rows database-wide — this closes the financial tail (charge → claim → payment) end to
end for the golden patient.

### Bundled data-quality repairs (Stage 6, via supported UI, not raw SQL)

1. **pid 2 allergy/prescription contradiction** (Stage 3 finding): deactivate (not delete) the
   conflicting "Timolol 0.5% eye drops" prescription for pid 2 via the medication module's own
   discontinue action. Chosen over removing the allergy record because allergies are the more
   clinically load-bearing fact to preserve, and deactivating (vs. hard-deleting) the prescription
   keeps full audit history intact while removing the live contradiction from the active-medication
   view. This is independent of the golden-patient choice — left in place, it would surface in a
   demo or QA pass on pid 2 regardless of which patient is used as the primary story.
2. **Fee-schedule `code_type=12` orphan tagging** (Stage 3 finding): **not** repaired here — fixing
   the master `codes` table's type tagging is a system-configuration change, not demo/business
   data, and out of proportion to this stage's scope. Recorded as a known limitation for the
   release certificate instead.

### Rollback / backup gating

None of the above is executed until Stage 5's backup is taken and verified `PASS`. Each Stage 6
action will be performed as the smallest supported UI mutation, verified immediately, with the
Stage 5 backup as the rollback mechanism of record for the whole batch (per Section 30, a single
coherent backup checkpoint covering this whole demo-data population pass, rather than a
separate backup per micro-action).

**Next exact action:** proceed to Stage 5 — take and verify a pre-demo-data backup before any of
the above is written.

---

## Stage 5 — Pre-Demo-Data Backup (2026-08-28)

The existing `openemr-offsite-backup.timer` had already fired at its scheduled time
(2026-08-28 03:00) — 19 minutes before this stage began, and before any Stage 6 write. Rather
than trigger a second, redundant backup, this run is adopted as the Stage 5 checkpoint since it
genuinely precedes all planned Stage 6 mutations.

**Verified, not assumed:** `systemctl status openemr-offsite-backup.service` shows
`status=0/SUCCESS`. The script's own "Verifying upload" step runs
`rclone ls r2:skyeaglebucket/$STAMP/` — i.e. it lists the **actual remote R2 bucket contents**,
not a local staging directory — and the log confirms 4 files genuinely present offsite:

```
PRE-DEMO-DATA BACKUP ID: 20260828-030018
  checksums-20260828-030018.txt        366 bytes
  deployed-ref-20260828-030018.txt       8 bytes
  openemr-db-20260828-030018.sql.gz  9,110,644 bytes
  openemr-documents-20260828-030018.tar.gz  20,636 bytes
```

**BEFORE STATE** (captured by this backup, matches Stage 1/3's live-verified inventory exactly,
since no writes occurred between Stage 3's inventory and this backup): 30 patients, 72 encounters,
37 appointments, 12 prescriptions, 36 billing rows, 2 insurance payers, 0 insurance policies,
0 claims, 0 payments, 0 lab orders, `oe-module-skyeagle-branding` active, deployed SHA
`663035f0bda91c09a0238de561d25069035914e8`.

**Rollback mechanism of record for Stage 6:** `openemr-db-20260828-030018.sql.gz` (R2 bucket
`skyeaglebucket/20260828-030018/`), decompressed and imported into a fresh `openemr` schema,
restores exactly this pre-population state. (The existing `sqlconf.php`/session-token surface is
unaffected by a DB-only restore; this mirrors the same recovery mechanism already exercised by
this programme's own prior deployment work.)

**Verdict: `PRE-DEMO-DATA BACKUP: PASS`**

**Next exact action:** proceed to Stage 6 — populate the golden dataset per Stage 4's design,
smallest supported mutation at a time, verified immediately after each.
