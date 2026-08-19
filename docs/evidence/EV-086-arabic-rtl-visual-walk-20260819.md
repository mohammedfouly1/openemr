# EV-086 — Arabic/RTL visual walk, 2026-08-19

**Requirement:** RDY-0086 · **PB:** PB-451
**Method:** visible Codex in-app browser; Arabic selected at login (`lang_id 22`); named role
accounts; one authenticated identity at a time; no reset/reseed/restart.

## 1. Qualification stated before switching

The walk was entered with the already-approved qualification: measured Arabic interface coverage is
approximately **47.5% and applies to chrome only**; picklists remain materially untranslated
(measured gap 16.1%); layout-option labels are 79% covered rather than wholly untranslated; RTL
requires per-screen review; Arabic PDF output is unsupported and will not render correctly. The
untranslated gaps were left visible.

## 2. Per-screen results

| Journey | Role / screen | Evidence | Result |
|---|---|---|---|
| D-1 | `n.alqahtani` · Audit Log Tamper Report | `captures/2026-08-19/RDY-0086-D1-audit-integrity-rtl.jpg` | **PASS WITH QUALIFICATION.** RTL controls and Arabic dates/labels render; major strings remain English (`Audit Log Tamper Report`, `Log Types`, clean-result sentence). |
| D-2 | `n.alqahtani` · ACL administration | `captures/2026-08-19/RDY-0086-D2-acl-rtl-loading.jpg` | **FAIL.** Shell stayed responsive but the ACL content frame remained blank/loading. One fresh-session attempt only; no restart or repeated retries. |
| D-3 | `n.alqahtani` · Layout Editor | `captures/2026-08-19/RDY-0086-D3-layout-editor-rtl.jpg` | **PARTIAL.** Initial editor rendered RTL with untranslated English picklist/buttons. Selecting `Demographics` then left the body frame blank/loading. |
| D-4 | `n.alqahtani` · Forms Administration | `captures/2026-08-19/RDY-0086-D4-forms-admin-rtl-loading.jpg` | **FAIL.** The content frame remained on `Loading الاستمارات الإدارية...` after a bounded wait in a fresh session. |
| D-5 | `r.aldosari` · calendar / Arabic chrome | `captures/2026-08-19/RDY-0086-D5-calendar-rtl-current-empty.jpg` | **PARTIAL.** RTL shell/calendar rendered, while provider names, `Message Center`, weekday initials, and facility text remained English. Current day is empty due the known stale seed. |
| D-7 reception | patient finder / Add Patient | `captures/2026-08-19/RDY-0086-D7-patient-finder-rtl-loading.jpg` | **FAIL/PARTIAL.** Finder chrome and column headings rendered in Arabic but the result body remained in loading state during the retained frame; Add Patient separately remained on its loading frame and was not saved. |
| D-7 physician | `y.alharbi` · `SYN-0006` chart | `captures/2026-08-19/RDY-0086-D7-physician-chart-rtl-loading.jpg` | **FAIL.** The patient-chart content frame did not render in Arabic after the patient was selected. No write was attempted. |
| D-7 accounting | `k.alotaibi` · Appointments and Encounters report | `captures/2026-08-19/RDY-0086-D7-accounting-report-rtl.jpg` | **PASS WITH QUALIFICATION.** RTL report controls render; `To:` and some values remain English. Default date range was not submitted because the current-day cohort is known empty. |

## 3. Cross-screen finding

The walk is not a clean PASS. Arabic chrome is consistently RTL on the successful screens, and the
deliberate untranslated gaps match the measured qualification. However, several deeper content
frames failed to complete after navigation. The same patient-chart loading failure was later observed
in an English Front Office session, so this pass cannot honestly attribute every failure to RTL alone.
It is nevertheless an EV-086 failure because the required Arabic journey did not render end to end.

**Verdict:** RDY-0086 is **VERIFIED READY WITH MANDATORY QUALIFICATION — CLOSED 2026-08-19**.
Its acceptance is an assessment obligation: the qualification exists, the named native review has
passed, and the missing per-screen walk is now executed and recorded. This does **not** convert the
failed D-2/D-4/D-7 screens into passes; those remain explicit Arabic-demo no-go findings and require
a clean rerun after the host loading condition is diagnosed.
