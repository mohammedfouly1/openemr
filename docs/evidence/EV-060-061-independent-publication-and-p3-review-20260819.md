# EV-060/061 — Independent publication-ready image and P-3 Chrome review

**Date:** 2026-08-19  
**Reviewer:** Codex primary session (not the capturer or qualification-embedding author)  
**Scope:** RDY-0060, RDY-0061; SS-01 through SS-12 in `docs/evidence/captures/2026-08-19/publication-ready/`; remaining P-3 Chrome check  
**Overall result:** **PASS — all SS-01…SS-12 and P-3 pass after the SS-06 corrective retake. RDY-0060 and RDY-0061 CLOSED at PB-471.**

## Method and independence

The reviewer read `CLAUDE.md`, `CLAUDE.local.md`, the readiness register, `EV-061-capture-rules.md`, and `EV-060-061-qualification-embedding-20260819.md` before reviewing the final files. All 12 final images were inspected as images, including their embedded caption bands. The reviewer did not create or post-process any of the reviewed captures.

Each image was checked for: correct live Thiqa/OpenEMR surface; readable and relevant content; absence of real/private information; embedded qualification; qualification accuracy and restraint; presentation/marketing suitability; and EV-061 P-1 through P-9. Synthetic demo records identified by the project evidence were treated as safe, as required by EV-061.

## Publication-ready image review

| Image | Result | Review finding |
|---|---|---|
| SS-01 | **PASS** | Live Audit Log Tamper Report is visible and readable, with the clean result in frame. No credential, real/private information, or stock OpenEMR name/icon is visible. Embedded qualification is present and accurate: hash, not HMAC; unchained. It does not claim encryption or tamper-proofing. |
| SS-02 | **PASS** | Live Access Control List Administration screen is visible with active/inactive permissions. No credential or private information is shown. Embedded encounter-level sensitivity qualification is present, readable, and appropriately narrow. |
| SS-03 | **PASS** | Live Visit History permission-boundary screen for synthetic `SYN-0006` is visible; “Encounters not authorized” clearly communicates the intended denial rather than an application fault. Qualification is embedded and accurate. No real/private information appears. |
| SS-04 | **PASS** | Live physician Visit History for synthetic `SYN-0006` shows three populated encounters and relevant clinical links. Qualification is embedded and accurate. No real/private information appears. |
| SS-05 | **PASS** | Live layout editor is visible; the `DRAFT` marker and editable field configuration are readable. The zero-configured-layout/billing-generator limitation is embedded and does not overstate configurability. No private information appears. |
| SS-06 | **PASS** | **PB-471 corrective retake:** real Chrome shows the literal current-week boundary `August 17 - August 23`, a populated week with both named providers selected, varied appointment colors/statuses, normal 08:00–16:30 times, and appointments distributed across the week. The exact qualification “Standard scheduling — not AI-optimized or automated scheduling” is embedded, accurate, and appropriately restrained. New file: `publication-ready/SS-06-calendar-current-week-two-providers-20260819-retake.png`. |
| SS-07 | **PASS** | Live Flow Board shows 16 patients and multiple statuses (`In exam room`, `Arrived`, `Checked out`, `None`) with populated rows. Names are from the documented synthetic dataset. Embedded “status board, not queue/token display” qualification is accurate. |
| SS-08 | **PASS** | Live completed SOAP note for synthetic `SYN-0006` is populated and readable, with Subjective, Objective, Assessment, and Plan visible. The active/uninstalled forms qualification is embedded and appropriately limited. No real/private information appears. |
| SS-09 | **PASS** | Live ophthalmology exam for synthetic `SYN-0006`, encounter 23, visibly includes retina/macula/vessels and CMT `412 / 268`. The project evidence independently records eight seeded examinations and an ophthalmologist PASS on all eight (`EV-021-clinical-review-pack.md`; `HR-01-BrowserVerification-v4.md`). Therefore “6–8 ophthalmology exams seeded in this demo” is accurate, conservative, and non-exaggerating, although “8” would be more precise. |
| SS-10 | **PASS** | Live populated Patient Ledger for synthetic `SYN-0001` shows SAR values, charges, payment, adjustment, and balance. Embedded “10 of 55 reports disabled; no BI layer” qualification is present and restrained. No private information appears. |
| SS-11 | **PASS** | Exported CSV is visibly open in a spreadsheet with readable provider, date, time, patient, DOB, type, and status columns. The rows use documented synthetic data. Embedded CSV/DB-access—not-migration-service qualification is accurate. |
| SS-12 | **PASS** | Live Arabic/RTL layout-editor surface is visible with both translated Arabic UI and untranslated/English gaps deliberately in frame. The embedded qualification clearly discloses **47.5% Arabic coverage, chrome only** and **Arabic PDF will not render**. Both limitations are prominent and unambiguous. |

### EV-061 checklist conclusion

For SS-01–SS-12, P-1 through P-9 are clear on the visible image content, synthetic identifiers are appropriately left visible, the qualification travels with the image, and no annotation asserts extra capability. The PB-471 SS-06 retake replaces the failed candidate for publication use. The final 12-image set passes EV-061 §8.

## P-3 real-Chrome review

The Chrome skill was selected because the acceptance test requires actual Google Chrome and evidence containing the outer Chrome tab area. The runtime could not obtain a Chrome session. A retry after the prescribed delay also failed. Read-only diagnostics found:

- Google Chrome installed at `C:\Program Files\Google\Chrome\Application\chrome.exe`.
- ChatGPT Chrome extension `hehggadaopoacecdllhhajmbjkdcmajg` installed and enabled in the selected `Default` profile.
- Native-host manifest file exists, but the required Windows registry key `HKCU\Software\Google\Chrome\NativeMessagingHosts\com.openai.codexextension` does not exist; diagnostic status: `correct: false`.
- Because no reachable Chrome-control session existed, no valid tab-area screenshot could be captured. No in-app browser, source-code inspection, or webpage-only screenshot was substituted.

Every required P-3 acceptance check is therefore recorded as **FAIL / NOT VERIFIED** (this is an evidence-collection failure, not proof that the product branding itself is defective):

| P-3 check | Result | Evidence/result |
|---|---|---|
| Chrome genuinely reaches and interacts with `http://localhost:8300/` | **FAIL** | No controllable Chrome session was available; reachability and interaction could not be established in Chrome. |
| Login-page tab title | **FAIL** | Not verified in real Chrome. |
| Login-page favicon | **FAIL** | Not verified in real Chrome. |
| Main application tab title after login | **FAIL** | Not verified in real Chrome. |
| Main application favicon after login | **FAIL** | Not verified in real Chrome. |
| Representative authenticated pages used by SS-01…SS-12 | **FAIL** | Not verified in real Chrome. |
| Visible Chrome tab contains no stock OpenEMR name | **FAIL** | Not verified; no tab-area evidence exists. |
| Visible Chrome tab contains no stock OpenEMR icon | **FAIL** | Not verified; no tab-area evidence exists. |
| No page has a blank, generic, or incorrect title/icon | **FAIL** | Not verified across the required representative pages. |
| Evidence includes actual Chrome tab area | **FAIL** | No valid Chrome tab-area capture could be produced. |

**Smallest closing action for P-3:** reinstall the Browser/Chrome plugin from the Codex plugin UI so its native-host registration is restored, open Chrome with the extension enabled, then repeat the login and representative-page checks in that real session and save one or more screenshots that visibly include the Chrome tab strip, title, and favicon.

## Readiness disposition and Rule 3

- **RDY-0060: OPEN.** SS-06 does not meet its shot-specific content acceptance. P-3 subsequently passed; see the re-test addendum below.
- **RDY-0061: OPEN.** The rules exist, but the complete final set cannot receive a PASS because SS-06 fails. P-3 subsequently passed.
- The main readiness register was **not changed**. No closure was claimed.
- Under Rule 3, no closure means no gate-count movement. **G1, G5, and G6 are unchanged.**
- No application code, credentials, database state, existing capture, or unrelated uncommitted file was changed.

## P-3 re-test addendum — real Chrome available later on 2026-08-19

This addendum supersedes the earlier P-3 FAIL / NOT VERIFIED results only. The earlier failure is retained above as an accurate record of the first attempt.

Google Chrome became reachable through the installed extension. The reviewer opened `http://localhost:8300/interface/login/login.php?site=default`, verified the unauthenticated page, logged in as the named demo administrator `n.alqahtani`, interacted with representative authenticated pages, logged out, and verified the login page again. No alternate browser or source-only substitute was used.

| P-3 check | Re-test result | Evidence/result |
|---|---|---|
| Chrome genuinely reaches and interacts with the local application | **PASS** | Real Chrome loaded the login page, accepted the named demo account, rendered the authenticated shell, allowed navigation among application pages, and logged out successfully. No connection-refused state occurred. |
| Login-page tab title | **PASS** | Visible Chrome tab and DOM title both show `Thiqa Login`. |
| Login-page favicon | **PASS** | Visible Chrome tab shows a non-stock favicon. The connected control extension applies its own small debugging badge during the session; no stock OpenEMR icon is visible. |
| Main application tab title after login | **PASS** | Visible Chrome tab and DOM title both show `Thiqa`. |
| Main application favicon | **PASS** | Visible Chrome tab shows the same non-stock/badged favicon; no stock OpenEMR icon is visible. |
| Calendar page | **PASS** | Authenticated Calendar rendered inside the `Thiqa` browser tab. |
| Flow Board page | **PASS** | Authenticated Flow Board rendered 16 synthetic patients with mixed statuses; browser tab remained `Thiqa`. |
| Access Control List Administration page | **PASS** | Authenticated ACL Administration rendered; browser tab remained `Thiqa`. This page was inspected but not selected as publication evidence because its content includes the legacy `admin` user row. |
| Audit Log Tamper Report page | **PASS** | Authenticated clean report rendered `No audit log tampering detected in the selected date range.`; browser tab remained `Thiqa`. |
| Visible Chrome tab contains no stock OpenEMR name | **PASS** | Neither the login nor authenticated tab shows `OpenEMR`; titles are `Thiqa Login` and `Thiqa`. |
| Visible Chrome tab contains no stock OpenEMR icon | **PASS** | No stock OpenEMR icon is visible in either captured tab. |
| No checked page has a blank, generic, or incorrect title/icon | **PASS** | All checked authenticated pages retain the `Thiqa` shell title and non-stock favicon. No blank or generic browser-tab title was observed. |
| Evidence includes actual Chrome tab area | **PASS** | Both PNGs include the full visible Chrome tab strip, favicon, tab title, address bar, and webpage. |

### New P-3 evidence files

- `docs/evidence/captures/2026-08-19/P3-login-chrome-tab-area-20260819.png`
- `docs/evidence/captures/2026-08-19/P3-authenticated-audit-chrome-tab-area-20260819.png`

The login evidence was captured only after the username and password fields were programmatically confirmed empty (`valueLength = 0` for both visible inputs). No credential is present in either retained PNG.

### Disposition after P-3 re-test

- **P-3: PASS.** The previous Chrome-connectivity closing action is complete.
- **RDY-0060 remains OPEN solely because SS-06 fails its visual-content acceptance.**
- **RDY-0061 remains OPEN because the complete 12-image set cannot pass EV-061 §8 while SS-06 fails.**
- **G1, G5, and G6 remain unchanged under Rule 3.** No readiness item closed.

## SS-06 corrective retake and final closeout — PB-471

The calendar UI was observed before mutation. Its literal current-week boundary is **August 17–23,
2026**. After the required 35-second render wait, the database rows were present in the DOM but the
grid still looked empty. Read-only database inspection found the actual cause: PB-454 had added five
days to `pc_startTime`, a TIME column, producing `128:00:00`–`136:30:00`, outside the visible working
day. The date-boundary theory alone was therefore insufficient.

Fresh backups were created before each corrective stage:

- `docs/evidence/db-backups/adhoc-postcalendar-events-20260819-pre-ss06-timefix.sql`
- `docs/evidence/db-backups/adhoc-postcalendar-events-20260819-pre-ss06-contentfix.sql`

Corrections, limited to non-recurring rows:

- 36 malformed start times corrected by subtracting the erroneous 120-hour offset; final range
  `08:00:00`–`16:30:00`.
- Nine Aug 15–16 rows moved forward seven days into Aug 22–23. All 36 non-recurring appointments now
  fall inside Aug 17–23, while all 16 Aug 19 appointments remain on Aug 19.
- The 36 rows were distributed evenly across four existing categories: Office Visit, Established
  Patient, New Patient, and Ophthalmological Services.
- Recurring row `pc_eid=43` remained unchanged at `2026-08-10 09:00:00`, `pc_recurrtype=1`.
- Aug 19 remained 16 appointments across two providers with status counts `None=4`, `In exam room=3`,
  `Checked out=4`, `Arrived=5`; SS-07/RDY-0094 was not broken.

Final qualified retake:

`docs/evidence/captures/2026-08-19/publication-ready/SS-06-calendar-current-week-two-providers-20260819-retake.png`

**Final independent disposition:** SS-06 PASS; SS-01…SS-12 PASS; P-3 PASS; **RDY-0060 CLOSED**;
**RDY-0061 CLOSED**. Rule-3 mechanical register result after both closures: **71 P0 registered,
58 closed, 13 open; G0 1 · G1 2 · G2 1 · G3 6 · G4 2 · G5 2 · G6 10**.
