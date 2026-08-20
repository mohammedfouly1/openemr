# Owner decisions

## Closed

| ID | Decision | Date | Effect |
|---|---|---|---|
| STEP0-001 | Reading B — gated evaluation credential | 2026-08-20 | Form-qualified, time-boxed Front Office + Physician access inside Book a walkthrough; Challenges 2 and 3 first |
| SEED-001 | **The date re-base and the database reset are separate jobs** | 2026-08-20 | Re-base is scheduled, non-destructive, whole-week, and covers every seeded clinical date. Reset stays **manual and Owner-authorised** until a clean demo-host baseline exists, folds in all post-baseline fixes, and passes restore verification. **Amends STEP0-001's sequence confirmation** |
| NOGO-001 | **§40 re-classified for an unaccompanied visitor** | 2026-08-20 | 12 of 14 rows are already enforced by `STEP0-001` D-c. Row 9 restricted for the **demo Physician role only** — production defaults untouched. Row 7 accepted and disclosed on the site. One consistent denial page replaces five inconsistent failure shapes |

## Still open beneath STEP0-001

- Expiry window for issued credentials.
- Evidence threshold for moving from manual to automatic issuance.
- Eleven security-gate results and written residual-risk acceptance.
- Lead-form data residency under Saudi PDPL.

**Held pending SEED-001.** No customer-access design or launch proceeds until the Owner rules on
what the split does to DEM-001's revisit trigger. STEP0-001 stands, but the finding it rests on —
*"seeding automation ships"* — is now only **half** true: the re-base ships on a schedule, the reset
does not. Whether half the automation fires a trigger phrased as a whole is an Owner question, and it
is not answered here.

## Still open beneath SEED-001

- **Does a scheduled re-base alone satisfy DEM-001's *"seeding automation ships"* trigger?**
  Until this is ruled, Task 1 is held and no credential is issued to anyone.
- Verification of the re-base on `demo.skyeagle.uk`, with backup and rollback both exercised on that
  host. The prepared script refuses to install its timer until both have run there.
- Creation of a **demo-host baseline** for the reset: EV-044's proven reset is a *local Windows*
  runbook, and no baseline exists on the demo host.
- Re-baking that baseline to include post-baseline fixes. `facility.primary_business_entity` is the
  known case (PB-442); whether others exist has not been established.
- Whether the Friday/Saturday correction extends to `form_encounter` and the other clinical tables,
  or stays scoped to the appointment calendar.

## Still open beneath NOGO-001

- **The consistent denial page.** Five different shapes were measured for a blocked route: 403 with a
  1.8 KB page, 200 with 0 bytes, 200 with 14 bytes, 401 with 0 bytes, and **HTTP 500 on the Fee
  Sheet**. Unaccompanied, a blank page or a 500 reads as a broken product rather than a locked door.
  Needs one branded "not included in this evaluation" response, and a decision on who builds it.
- **The row 7 site copy.** One claim-reviewed line stating billing is out of scope for the
  evaluation and why. Belongs in `04-content`; not yet written.
- **Row 9 revisit trigger:** reconsider route access when Arabic PDF shaping is fixed, or when a
  render/export permission split lets the on-screen report be shown without the broken export.
- **`NOGO-001`'s ACL change must be folded into the clean baseline.** A reset restores the baseline's
  ACL tables and would silently re-grant Patient Reports — the same regression shape PB-442 recorded.
- The `apply` path of `06-nogo001-demo-physician-role.php` has **not been executed** against any
  database. Only `check` has been run.

## Other Owner decisions

- Relative or absolute review floor.
- Thiqa or SkyEagle website branding.
- M0 fallback model ladder.

The full STEP0-001 instrument remains in [`CommitteeSystem.md`](CommitteeSystem.md) §13.1; the
SEED-001 instrument and the evidence behind it are in §13.4a of the same file. The prepared,
unexecuted implementation is
[`docs/evidence/ubuntu-infra-scripts/05-seed001-demo-date-rebase.sh`](../../evidence/ubuntu-infra-scripts/05-seed001-demo-date-rebase.sh).
The `NOGO-001` instrument is in §13.4b of the same file, and its prepared implementation is
[`06-nogo001-demo-physician-role.php`](../../evidence/ubuntu-infra-scripts/06-nogo001-demo-physician-role.php).
