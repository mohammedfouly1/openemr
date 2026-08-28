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
