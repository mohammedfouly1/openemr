# EV-090 — Live rendered-surface review, 2026-08-19

**RDY:** RDY-0090  
**Verdict:** **OPEN**  
**Reviewer role:** Codex browser reviewer. This is fresh browser evidence, but it is **not an independent human/second-person approval**. The required independent reviewer sign-off remains open.  
**Date/time:** 2026-08-19, 18:46–18:56 UTC  
**Application URL:** `http://localhost:8300/` (`site=default`)  
**Browser:** Codex in-app browser  
**Application version / commit:** Thiqa/OpenEMR 8.2.0; `ffba66cbb18c077602b7f842ad1d14a93089eee5`  
**Demo account/role:** `r.aldosari` — Front Office (password not recorded)  
**Synthetic patient:** Hessa Alamri, `SYN-0015`, pid 15. All visible patients and appointments were the seeded synthetic demo set.  

The local application loaded successfully, was not an `ERR_CONNECTION_REFUSED` page, displayed real Thiqa/OpenEMR application controls, and accepted live clicks. No credentials or real patient data are present in this evidence.

## Numbered EV-090 walk checklist

1. **PASS — Login favicon.** Live DOM points to `/public/images/logos/core/favicon/favicon.ico`; the login page rendered normally. Evidence: `RDY-0090-login-live-20260819.png`. Limitation: the in-app browser screenshot API captures the page viewport, not the native browser tab strip; DOM/title inspection supplies the title/favicon result.
2. **PASS — Root fallback favicon.** The tested login and application surfaces resolved the branded core favicon without a broken image.
3. **PASS — 32×32/favicon family.** No blank/generic favicon was observed on the tested live surfaces; full asset-family source integrity remains covered by the earlier inventory.
4. **BLOCKED — Zend-module favicon.** No safely reachable Zend-module customer surface was available in the Front Office menu. Next action: Administrator/operator reviewer opens one representative Zend screen without changing configuration.
5. **NOT APPLICABLE — LForms favicon.** Third-party/licensing-governed and not reached in this demo walk. Keep under RDY-0095/legal treatment.
6. **PASS for tested surfaces — Pages omitting a favicon.** Login, main application, and About all exposed the branded favicon; no generic/blank icon was observed. Untested screens remain covered by their individual BLOCKED rows.
7. **PASS — Login title.** Exact live title: `Thiqa Login`; no `OpenEMR` title. Evidence: `RDY-0090-login-live-20260819.png`.
8. **BLOCKED — Multi-site administration.** Not exposed to the Front Office role; no configuration change attempted. Next action: Administrator/operator reviewer checks `admin.php` read-only.
9. **PASS — Main application title.** Exact live title: `Thiqa`; branded favicon and main-menu logo loaded. Evidence: `RDY-0090-main-screen-live-20260819.png`.
10. **NOT APPLICABLE — Installer title.** Operator-only and unsafe/unnecessary for the customer demo walk.
11. **PARTIAL / BLOCKED — Interior titles.** Calendar, Patient Finder, synthetic Patient Dashboard and About rendered inside the titled Thiqa shell; the top-level title stayed `Thiqa`. ACL/report screens not reachable under this role remain blocked below.
12. **PASS — Login logo.** Visible Thiqa logo, natural image size 1053×390, no broken image, no stock OpenEMR logo, no external/placeholder links. Evidence: `RDY-0090-login-live-20260819.png`.
13. **NOT APPLICABLE — Portal auto-login logo.** Patient portal is disabled. Before enablement, a pilot reviewer must perform the live portal walk.
14. **BLOCKED — Printed patient statement logo.** Front Office received `Patient Ledger by Date — Not Authorized`; no statement/print output could be generated safely. Evidence: `RDY-0090-patient-ledger-not-authorized-20260819.png`. Next action: Accounting role generates a statement for `SYN-0015`, opens print preview and saves the generated PDF.
15. **BLOCKED — Eye Exam web logo.** Not reachable from this Front Office session. Next action: Physician role opens the seeded eye-exam encounter.
16. **BLOCKED — Eye Exam print/PDF logo.** No authorized Eye Exam output was reachable. Next action: Physician role generates the real Eye Exam print/PDF and an independent reviewer inspects every page.
17. **BLOCKED — Shared/per-form PDF behavior.** No PDF-producing screen was authorized in this role. Next action: Accounting and Physician role walks generate each named PDF through the normal UI.
18. **BLOCKED — Printed report headers.** Report menus were not available to this role and Visit History was denied. Next action: Accounting/Physician reviewers open and print 2–3 representative reports.
19. **BLOCKED — Statement/superbill layout.** No authorized statement or superbill output. Next action: Accounting reviewer generates both, using only synthetic data.
20. **NOT APPLICABLE — Portal enablement.** Portal is documented disabled.
21. **BLOCKED — Portal placeholder address.** Existing evidence records `your_web_site.com`; the disabled portal was not made reachable. Next action: Product/tenant provisioning owner replaces or explicitly approves the configured address before portal enablement.
22. **NOT APPLICABLE — Portal logo.** Disabled feature; live pilot verification required before enablement.
23. **BLOCKED — Reminder sender name.** No local mail-capture/preview surface was available in the tested role.
24. **BLOCKED — Reminder sender email.** Same blocker as item 23.
25. **BLOCKED — Return/reply-to identity.** Same blocker as item 23.
26. **BLOCKED — Rendered email.** No approved mail-capture service, test mailbox, draft preview or safe rendered-email UI was identified during this walk. Next action: Infrastructure/application owner provides a local mail-capture or approved test mailbox, then test reminder, reset, portal invitation and notification variants without real recipients.
27. **PARTIAL — Visible OpenEMR string sweep.** No unintended `OpenEMR`, `open-emr.org`, `localhost`, placeholder, broken-logo or generic-title text was visible on the tested Login, main Calendar, Patient Finder, Patient Dashboard or About surfaces. Unreached templates remain blocked.
28. **NOT APPLICABLE — OAuth login template.** OAuth is documented disabled/unreachable. Classification: DISABLED FEATURE, not a live PASS.
29. **BLOCKED — Remaining likely-rendered templates.** Not all customer paths were reachable under Front Office. Next action: multi-role human walk through Administrator, Physician and Accounting surfaces.
30. **NOT APPLICABLE — LForms third-party asset.** LEGAL/THIRD-PARTY treatment remains governed by RDY-0095.
31. **PASS for tested About surface — Required attribution not removed.** About showed product/version/support/manual identity; no licence text was removed or rewritten in this pass. Full legal attribution remains governed by RDY-0095.
32. **NOT APPLICABLE — `acknowledge_license_cert.html`.** Operator/legal-controlled and deliberately not opened. Do not treat suppression as branding closure.
33. **PARTIAL / BLOCKED — Product-registration modal.** Live About page passed (`About Thiqa`, version 8.2.0, `skyeagle.uk` support/docs, branded favicon), but the registration modal did not appear. Evidence: `RDY-0090-about-thiqa-live-20260819.png`. Next action: Administrator/operator reviewer captures the modal only if safely and naturally reachable; do not alter registration state.
34. **NOT APPLICABLE — Certification instruction.** LEGAL/CLAIMS REVIEW REQUIRED; not silently treated as a branding defect.

## Additional live surfaces

- **PASS — Calendar/main screen:** title `Thiqa`, branded favicon/logo, facility `Thiqa Demo Eye Clinic`, and populated synthetic appointments. Evidence: `RDY-0090-main-screen-live-20260819.png`.
- **PASS — Patient Finder:** rendered synthetic patient list and synthetic IDs under the Thiqa shell. Evidence: `RDY-0090-patient-finder-live-20260819.png`.
- **PASS — Patient Dashboard:** `SYN-0015` dashboard rendered after a bounded 25-second load; no unintended stock branding observed. Evidence: `RDY-0090-patient-dashboard-live-20260819.png`.
- **BLOCKED — Visit History:** live result was `(Encounters not authorized)` for Front Office. Next action: Physician and Accounting role checks.
- **BLOCKED — Patient Ledger:** live result was `Patient Ledger by Date — Not Authorized`. Next action: Accounting role check and print/PDF generation.
- **BLOCKED — ACL administration and Flow Board:** not exposed in this role during this pass. Existing evidence may cover Flow Board separately, but this review does not claim a fresh RDY-0090 PASS without its own capture.

## Print, PDF and email result

No print preview, generated PDF, or rendered email could be produced from the authorized Front Office surfaces. These categories are **BLOCKED**, not PASS. No native print-dialog limitation was used to infer success. No email was sent.

## Defects and next actions

No new confirmed branding defect was found on the surfaces that rendered. The authorization denials are expected role behavior, not branding defects. The incomplete multi-role/print/PDF/email inventory and missing independent human reviewer are closure blockers.

## Closure and gate synchronization

**RDY-0090 remains OPEN.** Required independent reviewer approval, Administrator/Physician/Accounting walks, print previews, generated PDFs, rendered emails, Eye Exam output, statement/superbill layout, OAuth classification and the product-registration modal are not all complete.

The main register was not changed. Canonical P0 gate counts remain **G1 2, G4 2** (no before/after change), and no stale-reference correction is justified by this incomplete evidence.

## Evidence files

- `docs/evidence/EV-090-live-rendered-surface-review-20260819.md`
- `docs/evidence/captures/2026-08-19/RDY-0090-login-live-20260819.png`
- `docs/evidence/captures/2026-08-19/RDY-0090-main-screen-live-20260819.png`
- `docs/evidence/captures/2026-08-19/RDY-0090-about-thiqa-live-20260819.png`
- `docs/evidence/captures/2026-08-19/RDY-0090-patient-finder-live-20260819.png`
- `docs/evidence/captures/2026-08-19/RDY-0090-patient-dashboard-live-20260819.png`
- `docs/evidence/captures/2026-08-19/RDY-0090-patient-ledger-not-authorized-20260819.png`

## Integrity check

Only synthetic demo data was viewed. Passwords were masked and not recorded. No configuration, credentials, permissions, patient records or database values were changed. Existing uncommitted files were preserved.

## Administrator continuation — `n.alqahtani`, 2026-08-19 19:07–19:16 UTC

The browser walk continued in the user-provided authenticated `n.alqahtani` session. Live navigation exposed `Admin`, `Reports`, `Fees`, `Modules`, `Procedures`, and ACL administration; for this evidence pass the effective role is recorded as **Administrator-level**. The password was not recorded.

- **PASS — Main shell:** top-level title `Thiqa`, branded favicon, Thiqa main-menu logo and facility identity remained correct.
- **PASS — ACL administration:** `Access Control List Administration` rendered fully with user memberships. No permissions were changed. Evidence: `RDY-0090-acl-administration-live-20260819.png`.
- **PASS — Flow Board:** rendered 16 synthetic patients for 2026-08-19, with `Thiqa Demo Eye Clinic`, correct providers, and mixed synthetic statuses. No stock identity or broken branding asset was observed. Evidence: `RDY-0090-flow-board-admin-live-20260819.png`.
- **PASS — Representative financial report live screen:** `Financial Summary by Service Code` rendered under the Thiqa shell with facility/provider/date criteria and produced a clean `No matches found` result for the default current-day criteria. Evidence: `RDY-0090-financial-summary-admin-live-20260819.png`.
- **BLOCKED — Financial print preview:** the live report exposed and accepted its Print action, but the in-app browser tool cannot inspect or capture the native operating-system print preview. No printable application page or PDF download was produced by that action. Next action: a human reviewer opens native print preview or the application supplies a printable page/PDF.
- **PARTIAL — Patient ledger:** the Administrator report selection page rendered and accepted synthetic patient text, but the autocomplete selection did not expose a selectable result through the browser accessibility surface. The patient-dashboard Ledger navigation was repeatedly interrupted by the application's clinical-reminder alert. No statement, ledger print or PDF was generated; this remains BLOCKED.
- **PASS — Administrator synthetic patient dashboard:** `SYN-0015` rendered with Report, Documents, Transactions, Issues and Ledger navigation under the Thiqa shell. The clinical-reminder alert contained only synthetic/general reminder content and was dismissed without changes.

This continuation advances checklist items 11, 17–18, 27 and the additional ACL/Flow Board rows. It does **not** close the required print/PDF/email/Eye Exam/independent-review legs. RDY-0090 remains **OPEN** and gate counts remain unchanged.

Additional evidence:

- `docs/evidence/captures/2026-08-19/RDY-0090-acl-administration-live-20260819.png`
- `docs/evidence/captures/2026-08-19/RDY-0090-flow-board-admin-live-20260819.png`
- `docs/evidence/captures/2026-08-19/RDY-0090-financial-summary-admin-live-20260819.png`
