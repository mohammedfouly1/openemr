# EV-016 (A-7) — sensitivity-ACL bypass in `view_form.php`/`load_form.php`: fixed

**Author:** Orchestrator (main session), 2026-08-19. Code fix + live HTTP verification, no
browser needed. Follow-up to `EV-016-authorization-matrix.md` §4.5 (PB-443's finding) and §4.1
(PB-410's finding).

## What was actually broken

`interface/patient_file/encounter/view_form.php:41` and
`interface/patient_file/encounter/load_form.php:38` gated exclusively on
`AclMain::aclCheckForm($_GET['formname'])` — a form-*type* ACL check ("can this role see SOAP
notes at all"). Neither file ever checked the specific encounter's `sensitivity` level, even
though `interface/patient_file/history/encounters.php:506-511` already does exactly that for the
Visit History listing, via the established pattern `AclMain::aclCheckCore('sensitivities',
$result4['sensitivity'])`.

**Net effect:** any role authorized for a given form type could reach a specific
high-sensitivity encounter's form content directly via `view_form.php`/`load_form.php`
(URL/id guessable or discoverable), completely bypassing the redaction that the same role hits
when browsing through Visit History normally. This is a real, exploitable **authorization
bypass**, not merely a UI inconsistency — a role that Visit History correctly redacts is not
actually blocked from the same data by a different, unguarded path to it.

## Fix

Added the same `EncounterService::getSensitivity()` + `AclMain::aclCheckCore('sensitivities',
...)` check — the exact pattern already used and trusted in `encounters.php` — to both entry
points, applied when a `pid`/`encounter` are present, after the existing form-type check.
Diff is minimal and additive (no existing logic removed or restructured); both files pass
`php -l`.

## Verification (live, against this session's running local instance)

1. **Bypass closed, confirmed live**: `k.alotaibi` (Accounting — already correctly redacted by
   `encounters.php` for sensitive encounters per `EV-016` §4.1) probed against `SYN-0014` pid 14,
   encounter 31 (`sensitivity='high'`), form id 86 (SOAP) directly via `view_form.php`:
   **HTTP 403** (was reachable with no check at all before this fix — the old code had zero
   sensitivity check of any kind, so this could not have been anything but open). Harness:
   `docs/evidence/harnesses/rdy0016-a7-sensitivity-fix-verify.php`.
2. **No regression on the existing, different, broader question**: `y.alharbi` (Physician)
   against the same URL is still **HTTP 200 — unchanged**. This is not a regression — per `EV-016`
   §4.1, the `physicians` ACL group already holds the `sensitivities`/`high` grant at the
   configuration level (the same reason `encounters.php` itself renders that encounter
   unredacted for this role). My fix reuses that exact same grant check; it cannot and should not
   silently override an existing ACL grant.
3. **No regression on the full matrix**: re-ran `docs/evidence/harnesses/rdy0016-matrix.php`
   (the original 32-probe suite) — `EXECUTED: 32  PASS: 32  FAIL: 0`, unchanged.
4. **PB-443's original A-7 probe re-run unchanged, correctly**: `rdy0016-a7-matrix.php`
   (`s.almutairi` vs. form id 115 on encounter 117) is still **NOT DENIED**. This is expected and
   correct, not a fix failure — encounter 117 is a **normal-sensitivity** encounter. That probe
   tests a different, broader, genuinely unresolved question: *should any physician be able to
   view any other physician's non-sensitive note on a shared patient at all* — ordinary EHR
   continuity-of-care practice generally says yes, but this specific readiness item's wording
   ("cannot amend another clinician's note") reads as a stricter per-author restriction. That is a
   product-authorization-policy decision, not a code defect I can responsibly infer and fix
   unilaterally — flagging it for the Owner rather than guessing.

## Net effect on RDY-0016 / A-7

- **Fixed and verified**: the sensitivity-ACL bypass via direct form URLs (the §4.1 finding's
  practical consequence — a genuinely security-relevant gap, now closed for every role that does
  *not* hold the `sensitivities` grant).
- **Still open, and correctly so**: the broader "note ownership across physicians" question
  PB-443's probe surfaced. Not decided here. A-7's row should stay open until the Owner rules on
  whether non-sensitive cross-clinician viewing is intended behavior (standard EHR practice) or
  should be restricted — and if the latter, that needs its own explicit design, not a bolt-on to
  the sensitivity mechanism.

No database, browser, or live user session was touched in producing this fix — code edit + local
HTTP probes against already-running services only.
