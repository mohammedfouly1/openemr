# EV-060/061 — browser capture session, 2026-08-19

**Requirements:** RDY-0060, RDY-0061 · **PB:** PB-450 · **Capturer:** Codex built-in-browser agent ·
**Independent reviewer:** Codex independent capture reviewer
**Method:** one visible Codex in-app browser tab, one authenticated identity at a time; explicit
logout between roles; no Apache restart, database reset, or reseed.

## 1. Outcome

This pass produced seven new strong content candidates (SS-04, SS-05, SS-08…SS-12) and one
documented SS-03 candidate. It did **not** close RDY-0060 or RDY-0061. The independent §8 review
returned RETAKE for every candidate because the mandatory qualification/annotation is not embedded
in the same visual unit. SS-03 also used the wrong History surface, SS-06 is absent because the
current week is empty, and SS-07 remains the stale 2026-08-14 capture.

No evidence is promoted to publication-ready status by this file.

## 2. Capture inventory and actual result

| Shot | Account / source | Evidence | Actual result |
|---|---|---|---|
| SS-03 | `r.aldosari` Front Office | `captures/2026-08-19/SS-03-front-office-history-not-authorized-CANDIDATE.jpg` | Candidate only. Same `SYN-0006` chart as SS-04, but the selected tab is **History**, not Visit History; `(History not authorized)` reads as an error and the large blank panel conflicts with P-8/P-9. RETAKE. |
| SS-04 | `y.alharbi` Physician | `captures/2026-08-19/SS-04-physician-visit-history-present.jpg` | Correct populated Visit History for `SYN-0006`; three encounters visible. Cannot form a compliant pair until SS-03 is retaken. |
| SS-05 | `n.alqahtani` Administrator | `captures/2026-08-19/SS-05-layout-editor-mid-edit.jpg` | Real Layout Editor with a temporary `DRAFT:` edit visible. The original value was restored immediately; Save Changes was never pressed. Reviewer noted the draft text is visually truncated. |
| SS-06 | `r.aldosari` Front Office | — | **ABSENT.** Live current-day/week calendar is empty. Existing `RDY-0094-row6-calendar-month-view-today-empty.jpg` and DB evidence show the seed is fixed to 2026-08-14. An empty current-week capture would violate P-8 and cannot satisfy the 36-appointment acceptance. No historical week was mislabelled as current. |
| SS-07 | existing capture | `captures/2026-08-19/SS-07-flow-board-mixed-statuses-20260814.jpg` | Not recaptured. It is stale, contains a `Total patients: 16` volume figure (P-7), and the reviewer observed wrong-account chrome. RDY-0094 evidence showed no reseed had occurred; none was performed. |
| SS-08 | `y.alharbi` Physician | `captures/2026-08-19/SS-08-completed-soap-note.jpg` | Completed attributed SOAP note with populated S/O/A/P content. Reviewer requested a visible `SYN-` identifier and qualification packaging. |
| SS-09 | `y.alharbi` Physician | `captures/2026-08-19/SS-09-ophthalmology-retina-panel-encounter-23.jpg` | Correct `SYN-0006`, encounter 23; macula, vessels, and CMT 412/268 are in frame. Reviewer noted the adjacent `SOAP (by Administrator)` could confuse provenance. |
| SS-10 | `k.alotaibi` Accounting | `captures/2026-08-19/SS-10-patient-ledger-csv-control.jpg` | Used EV-061's permitted RPT-0028 alternative because live RPT-0012 exposes Print but no CSV control. Non-empty `SYN-0001` ledger, SAR amounts, and Export to CSV are visible. |
| SS-11 | actual RPT-0009 export | `captures/2026-08-19/SS-11-exported-csv-open-in-spreadsheet.png` | Actual export parsed as 38 data rows plus header, 7 columns, 3,871 bytes. Transliterated names render cleanly. Image shows rows 1–18 and was rendered from the exported CSV into an `.xlsx`; reviewer could not verify all 38 rows or a live spreadsheet application from the image alone. |
| SS-12 | `n.alqahtani` Arabic | `captures/2026-08-19/SS-12-arabic-rtl-with-untranslated-elements.jpg` | RTL chrome and deliberately untranslated `Demographics`, `Include inactive`, and `New Layout` controls are visible. The layout body did not finish loading; P-8 and the missing mandatory qualification require retake. |

## 3. Independent EV-061 §8 review

Reviewer identity: **Codex independent capture reviewer**. The reviewer read EV-061 in full and
visually inspected the eight new candidates.

| Shot | P-1…P-9 | Synthetic ID | Correct account/source | Qualification attached | Annotation safe | Verdict |
|---|---|---|---|---|---|---|
| SS-03 | Fail P-8/P-9 | Yes | Correct role, wrong surface | No | No annotation | **RETAKE** |
| SS-04 | Content clear; browser chrome not captured | Yes | Yes | No | No annotation | **RETAKE** |
| SS-05 | Content clear; draft truncated | n/a | Yes | No | No annotation | **RETAKE** |
| SS-06 | No candidate | n/a | n/a | No | n/a | **ABSENT** |
| SS-07 | Fail P-7; stale/wrong-account concerns | Yes | No | No | No annotation | **RETAKE** |
| SS-08 | Content clear; `SYN-` not visible | No | Yes | No | No annotation | **RETAKE** |
| SS-09 | Content target met; provenance could confuse | Yes | Yes | No | No annotation | **RETAKE** |
| SS-10 | Content target met | Yes | Yes | No | No annotation | **RETAKE** |
| SS-11 | 7 columns visible; full 38-row proof/application chrome absent | Synthetic rows | Actual export | No | No annotation | **RETAKE** |
| SS-12 | Fail P-8 | n/a | Yes | No | No annotation | **RETAKE** |

The reviewer also noted that the web captures omit browser chrome, so P-3 cannot be fully verified.
The strongest content candidates are SS-04, SS-09, and SS-10, but none is publication-ready.

## 4. Required next action

1. Refresh/reseed appointment data only after Owner coordination, then recapture SS-06 and SS-07.
2. Retake SS-03 on the actual Visit History boundary and pair it with SS-04.
3. Package every retained image with its required qualification and one non-assertive annotation in
   the same visual unit, then repeat the independent §8 review.
4. Retake SS-12 after its body finishes rendering while leaving untranslated controls visible.

**Verdict:** RDY-0060 and RDY-0061 remain **NOT READY — materially advanced, review completed,
retakes explicitly enumerated**.

## PB-461…PB-463 patient-retake addendum

The appointment rebase unblocked real SS-06/07 captures. The continuation retook
SS-03/04/06/07/08/09, retained content-correct SS-10/11, and patiently retried SS-12.
SS-03/04/06/07/08/09 now pass the independent content/surface/data review, but all still
need the orchestrator-owned qualification/annotation embedding; SS-04 also still needs
outer-browser P-3 verification. SS-05 was not retaken and SS-12 remains blank after a
45-second post-Demographics wait. Full inventory and paths:
`EV-060-061-086-patient-retakes-20260819.md`.
