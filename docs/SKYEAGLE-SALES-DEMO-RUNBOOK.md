# SkyEagle Sales Demo Runbook

**Audience:** SkyEagle sales/solutions staff running a live demo against
`https://demo.skyeagle.uk`. Every step below was actually performed and verified live during
the SkyEagle Post-Deployment certification programme (see
`docs/SKYEAGLE-POST-DEPLOYMENT-DEMO-RELEASE-CHECKPOINT.md` for full evidence) — nothing here
is speculative. Follow the "known pitfalls" section closely; several are real, reproducible
quirks of this specific install, not user error.

**Credentials:** never written in this file, per this project's own convention. Obtain the
`admin` and the 6 named role-account credentials from the Owner's credential store before a
demo. Do not type credentials into chat, email, or any unencrypted channel.

---

## 1. Pre-demo checklist (do this the day of, not weeks ahead)

1. Confirm the site is up: `https://demo.skyeagle.uk/interface/login/login.php?site=default`
   should load in under 2 seconds and show the SkyEagle-branded login page.
2. Confirm you have working credentials for at least: `admin` (or a Physicians-role account
   for the clinical walkthrough) and, if doing the role-separation section, one Front
   Office and one Accounting account.
3. Open the browser in a clean profile/incognito window — a demo audience should never see
   your own browser history, bookmarks, or unrelated tabs.
4. Have a fallback: a screen recording or screenshots of the golden-patient walkthrough
   (§3 below), in case of a live connectivity hiccup mid-demo.

## 2. What to show first — branding and login

- Load the login page. Point out: SkyEagle logo, tagline ("Better care begins here."),
  language switcher.
- **Optional but effective:** switch the language selector to **Arabic** and log in with an
  Arabic-preference account (e.g. `n.alqahtani`). The entire shell renders correctly
  right-to-left — verified clean across the login page, top navigation, Patient Finder,
  clinical dashboard, and Ledger. This is a strong, low-risk visual moment for a
  Saudi/Gulf-market audience.
- **Known gap, decide in advance whether to route around it:** several UI strings remain
  untranslated in Arabic — top-nav "Finder"/"Modules"/"Recalls", the Patient Finder's search
  labels, a few dashboard tab labels, the Ledger's "Print Ledger" button. These are genuine
  upstream OpenEMR translation gaps, not a SkyEagle defect, but a sharp-eyed bilingual
  audience member may notice. If the audience is Arabic-first, consider a brief "translation
  coverage is an active workstream" framing rather than pretending it's complete.

## 3. The golden-patient clinical walkthrough (core of the demo)

Patient: **Amal Albishi (pid 3)** — the deliberately-selected "golden" demo patient. Clean
record, no data contradictions (unlike several other seeded patients — do not substitute a
different patient without re-checking, see §5). Diagnosis on file: Primary open-angle
glaucoma — a coherent, demo-friendly ophthalmology story.

**Recommended account:** a Physicians or Clinicians-role account (`y.alharbi`, `s.almutairi`,
or `m.alzahrani`) — these have full clinical access with no visible Admin menu clutter,
which reads as a cleaner "this is what a doctor sees" narrative than logging in as `admin`.

Live-proven flow:

1. **Patient Finder** → search "Albishi" or open pid 3 directly.
2. **Dashboard** → point out the existing problem list (glaucoma diagnosis), the existing
   Latanoprost + Artificial Tears prescriptions, and the insurance policy (Meridian Gulf
   Health) already on file.
3. **Appointment** → there is a standing future appointment (2026-09-01, Ophthalmological
   Services). Open it from the Calendar to show scheduling.
4. **Encounter** → create a new encounter (or use the existing one from the certification
   pass if still present) tied to that visit reason.
5. **Vitals** → enter a new vitals reading. **Use the form's own displayed units** (this
   install shows US units — lbs/inches/°F — the entry form and the live side-by-side
   conversion display are both correct for a *new* entry). Do not scroll back through this
   patient's older vitals history mid-demo — see the known-pitfalls note in §5.
6. **Link the diagnosis to the visit** via "Link/Add Issues to This Visit" — a good moment to
   show how a standing problem gets tied to today's encounter rather than re-entered.
7. **Billing (Fee Sheet)** → **do not use the CPT4/procedure text search box** — see §5, it
   returns zero results on this install. Instead use the **"Established Patient" E/M-level
   button** (Brief/Limited/Extended/Detailed/Comprehensive grid) and pick a level (e.g.
   "Limited" → 99212/99213). This is a completely standard, supported OpenEMR path and reads
   naturally in a demo — it does not need to be flagged to the audience as a workaround.
8. **Generate the claim** → Fees > Billing Manager > "HCFA FORM > CMS 1500 PDF > Continue".
   Produces a real, correctly-formatted CMS-1500 PDF. **Do not click "Generate X12"** — this
   install has no EDI trading partner configured and will show an error; stick to the paper
   claim path for the demo.
9. **Record payment** → Fees > Payment (Check Payment method). **The "Check or Reference
   Number" field is required** — if left blank the form silently redisplays with no visible
   error. Always fill it (any value, e.g. a mock check number) before submitting.
10. **Reports** → Reports > Financial > Sales by Item shows the new charge correctly — a
    good closing beat ("and it flows straight into reporting").

## 4. Role-separation demo (optional, second act)

If the audience cares about staff access control (common for larger practices), log into 2-3
different role accounts in sequence and show the differences — all confirmed live and stock
OpenEMR-correct:

- **Front Office** (`r.aldosari`): demographics, scheduling, billing-view — no clinical
  widgets appear at all (not just empty, genuinely absent), no Admin menu.
- **Accounting** (`k.alotaibi`): same clinical exclusion; Fees menu shows only the back-office
  items (Billing Manager, Batch/Posting Payments, EDI History) — Fee Sheet/Payment/Checkout
  are visibly greyed out; Admin menu is narrower (Practice/Coding/Documents/Address Book
  only).
- **Physicians/Clinicians** (`y.alharbi`/`m.alzahrani`): full clinical access, Fee Sheet only
  (no back-office billing tools), no Admin menu.

This is a genuinely effective "the system enforces this, it's not just policy" moment.

## 5. Known pitfalls — avoid these live, or know how to recover gracefully

| Pitfall | What happens | What to do |
|---|---|---|
| Fee Sheet CPT4/procedure text search | Returns 0 results for any code, even ones already in use | Use the E/M-level picker instead (§3 step 7) — do not attempt the text search live |
| Payment form, blank reference number | Silently redisplays with no visible error | Always fill "Check or Reference Number" before submitting |
| Prescription "Add" from the list-widget modal | Broken link (double `?` in the URL), returns a blank/error page | Navigate to Patient > Rx > "Prescribe new medication" directly instead of the widget's inline Add link |
| Older vitals entries for pid 3, and **any** vitals for patients other than pid 3 | Height/weight/temperature display as physically impossible values (e.g. "36.8°F" body temperature) — a historical seeding-unit bug, fix already staged but not yet applied as of this writing | Stick to pid 3's **most recent** vitals entry (created live in the certification pass, correct units); avoid opening vitals history or any other patient's vitals card during a live demo |
| Generate X12 button | Errors — no EDI partner configured on this install | Do not click; use the CMS-1500 PDF path only |
| Two patients (pid 1, pid 2/5) with duplicate name+DOB pairs | Confusing if searched by name during a live demo | Always search/select by the specific patient you intend (pid 3 for the main walkthrough); don't free-search by a common name live |
| Patient pid 2 (Turki Alqarni) | Has a data contradiction (allergic to a drug also prescribed) — being addressed separately from the demo path | Do not use pid 2 as a demo patient |

## 6. If something breaks mid-demo

- A page or click that seems to hang for 15-90+ seconds is a known, reproducible
  browser/extension-side quirk in prior testing sessions, not necessarily a server problem —
  give it a moment before assuming the app is down.
- If the app genuinely seems unresponsive, check `https://demo.skyeagle.uk/interface/login/login.php?site=default`
  in a fresh tab — if that loads fine, the issue was local to the stuck page/tab, not the
  server.
- Have the fallback recording/screenshots ready (§1.4) rather than troubleshooting live in
  front of an audience.

---

**Maintenance note:** this runbook should be re-verified (or explicitly re-confirmed current)
before each significant sales push, and immediately updated if the fee-schedule or vitals
fixes referenced in §5 are ever applied — those two workarounds would become unnecessary and
should be removed from this document rather than left as stale caution.
