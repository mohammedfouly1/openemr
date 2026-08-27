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
