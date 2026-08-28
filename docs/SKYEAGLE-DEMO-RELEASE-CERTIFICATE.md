# SkyEagle Demo Release Certificate

**Programme:** SkyEagle Post-Deployment → Full Demo & Release Certification
**Date:** 2026-08-28
**Scope:** `https://demo.skyeagle.uk` (Ubuntu VM `demo-openemr`), staff-facing sales/operational
demo of the SkyEagle-branded OpenEMR fork.
**Full evidence trail:** `docs/SKYEAGLE-POST-DEPLOYMENT-DEMO-RELEASE-CHECKPOINT.md` (all 24
stages, every claim below traces back to a specific dated entry there — this certificate is a
synthesis, not a new source of truth).

---

## A. Deployment identity

| Item | Value |
|---|---|
| Release tag | `skyeagle-demo-v1` |
| Local/pushed HEAD at freeze | `ba3fec007474b87cb43d8a85b66759a48f3fc7c9` |
| Deployed application SHA | `663035f0bda91c09a0238de561d25069035914e8` (unchanged since before this programme began — every commit since is checkpoint documentation, not application code) |
| OpenEMR base | 8.2.0 |
| PHP | 8.3.6 (NTS) |
| Apache | 2.4.58 (Ubuntu) |
| MariaDB | 10.11.14-MariaDB |
| Live URL | `https://demo.skyeagle.uk` |
| Fronting | Cloudflare (TLS, WAF-capable edge) |

## B. Per-stage verdict summary

| Stage | Subject | Verdict |
|---|---|---|
| 1 | State reconstruction | PASS |
| 2 | Visual gaps (dark theme, tablet/mobile/print, PDF) | CONDITIONAL — one ledger-report dark-theme contrast bug found; tablet/mobile/print NOT VERIFIED (tooling limitation, not a known defect) |
| 3 | Demo data inventory | Complete — golden patient identified (pid 3), one data contradiction found (pid 2) |
| 4 | Golden demo dataset design | Complete |
| 5 | Pre-mutation backup | PASS |
| 6 | Populate/repair demo data | Complete — all 6 planned changes done, including a payment-flow bug diagnosed and resolved same-session |
| 7 | Role & ACL certification | PASS — stock GACL rule set confirmed unmodified, 5 of 6 roles live-tested |
| 8 | End-to-end functional workflow | Patient/Clinical PASS, Billing CONDITIONAL at the time (workaround needed) — **now PASS**, see Stage 22 |
| 9 | Arabic workflow | PASS — RTL layout clean; several upstream (not SkyEagle) translation gaps catalogued |
| 10 | Claims/insurance capability | CONDITIONAL — demonstrable slice proven real; X12/EDI and NPHIES genuinely not installed |
| 11 | Portal decision | KEEP DISABLED (reasoned decision, not a gap) |
| 12 | Security & hardening review | **PASS** (after remediation — see §C) |
| 13 | Backup restore/DR drill | PASS — checksum-verified restore into an isolated DB, production untouched |
| 14 | Monitoring & operations | CONDITIONAL — detection logic sound and live; no active alert delivery, no log rotation (both P2/P3, documented) |
| 15 | GitHub/CI closure | CONDITIONAL — CI blocked by a GitHub account billing lock, unrelated to code; no code-quality risk given this programme's docs-only commits |
| 16 | Source/documentation reconciliation | Found and closed a real gap in Stage 12's own fix (see §C) |
| 17 | Translation migration journal | RESOLVED — already applied, verified correct |
| 18 | Sales demo runbook | CREATED (`docs/SKYEAGLE-SALES-DEMO-RUNBOOK.md`) |
| 19/20 | Marketing screenshot/video plan | CREATED (`docs/SKYEAGLE-MARKETING-CAPTURE-RUNBOOK.md`) |
| 21 | Marketing capture (actual) | **DEFERRED** — pre-conditions (data fixes) now met; not executed this session due to demonstrated browser-automation instability on this host (see §F) |
| 22 | Final demo reset / golden state | PASS — both outstanding data fixes applied and independently verified |
| 23 | Release freeze | COMPLETE — tag `skyeagle-demo-v1` |
| 24 | This certificate | — |

## C. Security certification detail (Stage 12/16)

Two P0s found and fixed, entirely at the Apache origin (not edge-masking):

1. **`.git` directory publicly downloadable** — fixed via `<DirectoryMatch>`/`<FilesMatch>`
   deny rules.
2. **5 unauthenticated-by-design maintenance scripts publicly reachable**
   (`admin.php`, `setup.php`, `sql_upgrade.php`, `sql_patch.php`, `ippf_upgrade.php`) — all
   confirmed unauthenticated **by upstream OpenEMR design** (read directly from source, not
   assumed), fixed via the same `<FilesMatch>` mechanism. The first remediation pass missed 2
   of the 5 (`sql_patch.php`, `ippf_upgrade.php`) — found during Stage 16's reconciliation
   pass against a pre-existing planning document, corrected same-day.

Both fixes independently re-verified externally (through Cloudflare) and origin-bypass
(`--resolve` direct to Apache), with zero regression to login, static assets, the branding
CLI, the database, or live monitoring, each time from this session's own tools rather than
accepted on the Owner's report alone.

**Non-blocking findings carried forward** (all documented in Stage 12's findings table,
S12-04 through S12-10): PHP `expose_php=On` (not actually leaked publicly, P3); session
cookies missing explicit `Secure`/`HttpOnly` on one cookie (P2, app-code change, out of this
stage's scope); the `admin` account's password not confirmed rotated since this VM's install
day (P1 — **recommend Owner verify/rotate before any customer-facing use**); an inactive
system account (`oe-system`) retains stale Administrators ACL membership (P3); the
`contrib/`-directory-wide Apache deny rule referenced in planning docs was not independently
re-verified (P2/P3-equivalent, `InstallerAuto.php`'s own app-level gate already makes this
low-risk).

## D. Data integrity

Golden patient: **pid 3, Amal Albishi** — clean record, no contradictions, full
clinical-to-billing workflow proven live (appointment → encounter → vitals → diagnosis link →
billing → CMS-1500 claim → payment → report).

Two historical data-quality bugs diagnosed with exact root-cause and fixed, both independently
re-verified after application:
- **Vitals unit-storage bug** (seeded data written in the wrong unit convention) — fixed for
  all 12 originally-affected rows; confirmed via fresh SQL and through the live UI for two
  patients.
- **Fee-schedule `code_type` mistagging** (4 codes orphaned from any real code-type row,
  making the standard Fee Sheet search silently return zero results) — fixed; confirmed via
  fresh SQL, with the causal search-filter mechanism independently confirmed from source in
  Stage 3/8. Interactive UI click-through confirmation was attempted extensively but blocked
  by browser-automation tooling limitations on this host — recorded honestly in Stage 22
  rather than glossed over; the causal evidence stands independently of that one unconfirmed
  interaction.

One known remaining data anomaly, **not fixed, deliberately out of scope**: `pid 2` (Turki
Alqarni) has an allergy/prescription contradiction unrelated to the golden-patient path —
already excluded from all demo/sales/marketing materials by explicit instruction in
`docs/SKYEAGLE-SALES-DEMO-RUNBOOK.md`.

## E. Backup & disaster recovery

Nightly offsite backup to Cloudflare R2 confirmed healthy and continuously running throughout
this programme. A full restore drill (Stage 13) proved a real, checksum-verified backup
restores correctly into an isolated database without ever touching production. A dedicated
pre-change backup was taken before the final two data fixes (Stage 22,
`20260828-074626`, independently verified present on R2). The regular nightly timer will
produce a golden-state backup covering the corrected data on its normal schedule.

## F. Outstanding items for the Owner (not blocking this certificate, tracked here)

1. **Verify/rotate the `admin` account's production password** on `demo-openemr`
   specifically (S12-10, P1).
2. **Apply monitoring alert delivery** (email/webhook/paging) — detection logic is sound but
   currently log-only (Stage 14, P2).
3. **Add log rotation** for `/var/log/openemr-monitoring.log` (Stage 14, P3 — not urgent at
   current growth rate).
4. **Resolve the GitHub Actions billing lock** on the `mohammedfouly1` account (Stage 15) —
   routine account maintenance, unrelated to code.
5. **Execute Stage 21's actual marketing capture pass** — the shot list and video plan are
   ready (`docs/SKYEAGLE-MARKETING-CAPTURE-RUNBOOK.md`); this session's Claude-in-Chrome
   connection proved too unstable on this host to complete it reliably (repeated CDP
   timeouts, consistent with this session's own prior documented history) — recommend either
   a follow-up session on a more stable connection, or the documented Selenium/Panther
   fallback path.
6. Optional, low-priority: independently verify `contrib/`'s Apache-level exposure (Stage 16
   finding 3); clean up `oe-system`'s stale Administrators ACL mapping (S12-07).

None of these are P0. None block using the demo for its intended staff-facing sales purpose.

## G. Final verdict

```
SKYEAGLE DEMO RELEASE CERTIFICATION: PASS (with 6 documented, non-blocking follow-ups)
```

The application is live, secure (both P0s closed and independently re-verified), functionally
proven end-to-end for its core clinical-to-billing-to-reporting workflow, role-separated
correctly, bilingual with known and documented translation gaps, backed up and
restore-tested, and has a ready sales demo runbook and marketing capture plan. Every verdict
above reflects what was actually tested and observed in this session, including the items
that stayed CONDITIONAL rather than being inflated to PASS — consistent with this programme's
own instruction never to convert NOT VERIFIED into PASS.

---

*This certificate synthesizes `docs/SKYEAGLE-POST-DEPLOYMENT-DEMO-RELEASE-CHECKPOINT.md`
Stages 1-23. It should be re-issued (not silently edited) if a future session materially
changes the deployment, applies the outstanding items in §F, or completes Stage 21's deferred
capture.*
