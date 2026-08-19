# EV-060/061/086 — direct-navigation last-mile retakes, 2026-08-19

**Requirements:** RDY-0060, RDY-0061, RDY-0086  
**PB:** PB-464…PB-467  
**Method:** Codex in-app browser; one tab and one authenticated identity at a time; explicit
logout between roles; Arabic selected at login for SS-12 and the RDY-0086 screens. No save,
database reset/reseed, server restart, or application-data mutation was performed.

## Results

| Item | Result | Evidence | Observation |
|---|---|---|---|
| SS-05 | **FIXED — framing retake** | `captures/2026-08-19/SS-05-layout-editor-draft-marker-fully-visible.png` | Temporary `DRAFT:` marker is fully legible in the Preferred Name title field. The original value was restored immediately; **Save Changes was not pressed**. |
| SS-12 | **FIXED BY DIRECT NAVIGATION** | `captures/2026-08-19/SS-12-arabic-rtl-demographics-direct-navigation.png` | Arabic-session `edit_layout.php?layout_id=DEM` rendered a populated Demographics layout in about seven seconds. Untranslated controls (`Demographics`, `Include inactive`, `Layout Properties`, `Encounter Preview`, `Tips`, and others) remain deliberately visible. |
| D-3 | **FIXED BY DIRECT NAVIGATION** | `captures/2026-08-19/RDY-0086-D3-layout-editor-demographics-rtl-direct-navigation.png` | Same fresh Arabic Demographics direct load; populated RTL layout replaces the previously blank frame. |
| D-4 | **FIXED BY DIRECT NAVIGATION** | `captures/2026-08-19/RDY-0086-D4-forms-admin-rtl-direct-navigation.png` | `forms_admin.php` rendered the populated Arabic/English Forms Administration table after its slower load. |
| D-7 physician | **FIXED BY DIRECT NAVIGATION** | `captures/2026-08-19/RDY-0086-D7-physician-SYN-0006-rtl-direct-navigation.png` | `y.alharbi`; direct `pid=6&set_pid=6` load rendered SYN-0006 / Ziad Alghamdi's Arabic/English Medical Record Dashboard with populated problem and prescription panels. Read-only capture; no chart write. |
| D-7 accounting | **RE-CONFIRMED** | `captures/2026-08-19/RDY-0086-D7-accounting-report-rtl-direct-navigation.png` | `k.alotaibi`; direct Appointments and Encounters report load rendered RTL controls. The report was not submitted. |

## Finding

All four screens that had been blank or unattempted in PB-461…PB-463 rendered through fresh
top-level direct navigation. The evidence therefore points to the prior in-page/iframe navigation
path in this browser context, not a server endpoint hang. SS-10 and SS-11 were not touched.
Qualification/annotation embedding remains outside this pass, as requested.

