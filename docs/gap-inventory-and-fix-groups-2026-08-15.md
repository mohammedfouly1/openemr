# REMAINING-GAP INVENTORY AND FIX-GROUP PLAN

> **Superseded for currency, not for method, by
> [`docs/gap-inventory-and-fix-groups-2026-08-19.md`](gap-inventory-and-fix-groups-2026-08-19.md).**
> That document re-derives live state as of 2026-08-19 and cross-references every item below into its
> own gate-by-gate (G0–G6) view. The 12-group fix-mechanism taxonomy in this document is still the
> reference for *how* an item closes; only the point-in-time facts (open/closed counts, live-verification
> results) are current only as of 2026-08-15.

**Produced:** 2026-08-15 · **Basis:** manual read of `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md`
(9,938 lines) and every gap register it references, plus live re-verification against the running
instance.
**Nothing here closes anything.** This is an inventory and a fix-plan; the closure contract in §0.0
Rule 5 of the readiness document is untouched.

---

## 0. Method, and the limits of what follows

| What | How it was established |
|---|---|
| Readiness register state | Read §7.1–§7.21 (all 114 rows) plus **every PB entry, PB-001 … PB-085**, because the register's `Status` cells are stale (see F-1 below) and the PB log is the real record |
| Blocking analysis | `docs/evidence/EV-000-blocked-items-register.md`, `docs/evidence/AGENT-CLAIMS.md`, and the 26 `EV-*` evidence files' outcomes as reported in their PB entries |
| Deployment gaps | `docs/demo-deployment-readiness.md` §23 (DG-001…DG-017) and §24 (B-01…B-34) — **untracked, produced 2026-08-15** |
| Branding gaps | `docs/branding/remaining-dependencies.md` §4 (D-1…D-16), §2 (A1–A8), §3 (V-01…V-10); `docs/RebrandingBugs.md` remediation ledger (RB-01…RB-25) |
| Platform backlog | `Locked Desicions/OpenEMR-SaaS-Implementation-Backlog-and-Acceptance-Criteria-UPDATED-2026-08-09.md` — 38 items, 323 acceptance criteria |
| Live state | SELECT-only MariaDB, `git`, filesystem and grep, executed 2026-08-15 (§2 below). No write of any kind was made to the database, the configuration or the seeded dataset |

**Not done, and it matters:** Sources A (GTM), B (capability audit) and C (competitor intelligence)
were used as cited by the readiness document, not re-derived. Individual `EV-*` files were read where
a status was ambiguous, not all in full. Nothing below is asserted from a document alone where a live
check was cheap — where the two disagree, the live check is quoted.

---

## 1. There is not one gap register. There are five.

The question "what is still open?" has no single answer today because five independent registers are
in flight, with different owners, numbering schemes and currency dates. **This is itself the first
finding**, and it is why items keep being re-discovered.

| # | Register | Location | Items | Open now |
|---|---|---|---|---|
| **R1** | **Launch-readiness register** (`RDY-*`) | `docs/Marketing-MVP-and-Launch-Readiness-Requirements.md` §7 | 114 | **84** (43 P0, 25 P1, 10 P2, 6 P3) |
| **R2** | **Demo-deployment register** (`DG-*`, `B-*`) | `docs/demo-deployment-readiness.md` §23–§24 | 17 + 34 | **51** (8 P0 blockers, 10 P1, 10 P2, 6 P3, 17 knowledge gaps) |
| **R3** | **Branding dependency register** (`D-*`) | `docs/branding/remaining-dependencies.md` §4 | 16 | **12** (D-3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16) |
| **R4** | **Branding audit ledger** (`RB-*`) | `docs/RebrandingBugs.md` | 25 | **4 non-terminal** (RB-04, RB-14, RB-17, RB-22) |
| **R5** | **SaaS locked-decisions backlog** (`BLK/MVP/PROD/P2/OPT`) | `Locked Desicions/…Backlog…UPDATED-2026-08-09.md` | 38 | **38 — every one of 323 acceptance checkboxes is unticked** |

R1 and R2 overlap heavily but not identically. R5 barely intersects R1 at all — and R5 contains **six
BLOCKER-priority items** (BLK-001…BLK-006) that the readiness register never traced. R1's own row
**RDY-0092** ("recover and reconcile the `Locked Desicions/` corpus") is the ticket for that
reconciliation, and it is still open. The corpus is present, has been present since at least
2026-08-13, and has still not been reconciled.

---

## 2. Live re-verification, 2026-08-15 — what the documents get wrong now

Run before writing anything below, because several registers were written on 2026-08-13/14 and the
instance has moved.

| Claim in the documents | Live result today | Verdict |
|---|---|---|
| Zero patients / encounters / appointments (§1.2, §48.B, "PHASE 2 IN 20 LINES" item 1) | patients **30** · encounters **72** · appointments **37** · documents **10** · prescriptions **12** · payers **2** · charges **36** | **Document stale.** The seeded dataset exists |
| Facility is `Your Clinic Name Here` (§47, §47.1, §48.B) | `Thiqa Demo Eye Clinic`, `3100 Fictional Boulevard`, `+966 11 000 000` | **Document stale** |
| Regional config untouched — empty timezone, `$`, `phone_country_code = 1` (§7.21, §47) | `gbl_time_zone = Asia/Riyadh` · `gbl_currency_symbol = SAR` · `phone_country_code = 966` | **Document stale** |
| One usable login (§1.2, item 7 of "PHASE 2 IN 20 LINES") | `users` **10**, `users_secure` **7**; ACL: admin 3, doc 2, back 1, clin 1, front 1 | **Document stale** |
| Background-service runner "has never executed"; `next_run` stuck at 2021-01-18 | `Email_Service` next_run **2026-08-15 22:53**, `UUID_Service` **2026-08-16 01:23** — both current, trigger ticking | **Document stale** (PB-071 already corrected it; ~12 statements elsewhere still assert the old fact) |
| `patients\|bulk_rep` ACO "does not exist — not even on the dev instance" (`B-06`, 2026-08-15) | Exists. Six report ACOs live: `rep`, `rep_a`, `pat_rep`, `reporting`, **`bulk_rep`**, **`op_rep`** | **Contradicted — re-verify B-06.** The *target-instance* provisioning step still stands |
| 418 behind / 92 ahead / 71 unpushed (`demo-deployment-readiness` §25.A) | `git rev-list --left-right --count upstream/master...HEAD` → **418 92**; unpushed → **71** | **Confirmed exactly** |
| RDY-0044-B v2 baseline ships 13 NULL UUIDs (PB-081) | Newest protected baseline is still `thiqa-rdy0044b-v2-baseline-20260814-064532.sql`, mtime **03:45**, four minutes *before* the 03:49 UUID fix. **No v3 exists** | **Still unfixed** |
| D-9 / RB-14: Amiri sourced, engine wiring absent | `grep -rl "Amiri\|NotoNaskh" src/ interface/ library/` → **zero matches** | **Confirmed still open** |
| RB-22: Inter faces deduplicated in SCSS, needs theme rebuild | `public/themes/*.css` still reference all four `Inter-{Regular,Medium,SemiBold,Bold}.woff2` | **Confirmed — rebuild not yet run** |
| RDY-0035 pqri placeholders live | `pqri_registry_name = Model Registry`, `pqri_registry_id = 125789123` | **Confirmed open** |
| RDY-0049 Unix commands on a Windows host | `config.php:10` `lpr …`, `:13` `enscript …`, `:26` `/usr/bin/file`; `perl_bin_dir = C:/xampp/perl/bin` (a path that does not exist) | **Confirmed open** |
| D-2 residual placeholder | `portal_onsite_two_address = https://your_web_site.com/openemr/portal` | **Confirmed open** |
| Break-glass unassigned (RDY-0019) | `breakglass` ACL group **0 members**; `Emergency_Login_email_id` empty | **Confirmed open** |
| Accounting demo actor (DG-010 / B-24) | `users` (Accounting) ACL group **0 members** | **Confirmed open** |
| Demo instance must not send mail (B-12 / DG-011) | `Email_Service` **active**, 2-minute tick, `rx_send_email = 1` | **Confirmed open** |
| `log` table size | **70,638** rows | Confirmed (B-17, B-32, RDY-0106) |

**Work is in flight right now, uncommitted**, and must not be duplicated: `SeedDemoCommand.php` is
modified and `BaselineOption.php` + `BaselineOptionTest.php` are new — a `--baseline-path` option that
resolves **DG-005 / B-03** (the hard-coded `C:/openemr-stack/…` seeder precondition), with a patch
record being added to `docs/branding/adr/patch-records.md` (PR-17).

---

## 3. The consolidated open-gap inventory, grouped by the fix that closes it

Twelve groups. The grouping is deliberately **by fix mechanism, not by domain**, because that is what
determines who can move an item and in what order. Group 1–4 and 11 are the only ones that can be
started without another person.

---

### GROUP 1 — Code changes, no external dependency (executable now)

Small, self-contained source edits. Each needs a numbered patch record where it touches core (Q1 /
Invariant 4).

| Item | Register | What the fix is |
|---|---|---|
| **DG-005 / B-03** | R2 | `--baseline-path` for `SeedDemoCommand` — **in flight, uncommitted** (PR-17). Finish, test, commit |
| **RDY-0049** | R1 | Replace `lpr` / `enscript` / `/usr/bin/file` in `sites/default/config.php` and repoint `perl_bin_dir` off the absent XAMPP path |
| **RB-14 / D-9** | R3/R4 | Arabic PDF font wiring. Amiri is **proven incompatible** with the bundled mPDF (`GPOS Lookup Type 5 Format 3 not supported`); the recommended substitute is **Noto Naskh Arabic**, already permitted by Q25. Fix = source the face, register it in `src/Pdf/Config_Mpdf.php` + dompdf, add the 7 acceptance tests |
| **RB-04 / D-8** | R3/R4 | Decide which Tier-2 delivery route ships (materialised CSS files vs endpoint), then delete the other. Currently both ship and one is unserved |
| **RDY-0108** | R1 (P2) | Add `audit_events_lab-order` to `$GLOBALS_METADATA` — audit records "Trivial" |
| **RDY-0099** | R1 (P1) | Wire `force_mfa` into `main_screen.php` — audit records "Small" |
| **DG-014 / DG-015** | R2 | Case-collision audit (`git ls-files \| tr A-Z a-z \| sort \| uniq -d`) and CRLF check on `bin/console`. Pure verification; minutes |
| **B-19** | R2 | Apache 2.2 syntax in `bin/.htaccess` and `documents/.htaccess` — add 2.4-syntax deny blocks in the vhost rather than editing tracked files |
| **DG-012 / B-13** | R2 | Read the fork's `TelemetryService` / `ProductRegistrationService` diffs and record, with evidence, that neither phones home by default |
| **RDY-0092** | R1 (P1) | Reconcile the `Locked Desicions/` corpus (R5) against the GTM and this register. It is a read-and-map task, not engineering |

---

### GROUP 2 — Build and artefact regeneration (executable now, off the Drive mount)

| Item | Register | What the fix is |
|---|---|---|
| **RB-22** | R4 | Theme rebuild in `C:\openemr-stack\build`, purge `public/themes/*` first (Q77), then robocopy back. Until then the four identical `Inter-*.woff2` are still all referenced and the saving is not real |
| **B-01 / B-02 / DG-003** | R2 | `vendor/`, `public/assets/`, `public/themes/` are all gitignored: the deployment target needs `composer install --no-dev` + Node 24 + `npm ci && npm run build`, or those three trees shipped as artefacts. `napa` pulls 9 non-registry archives — confirm egress first |
| **B-08 / DG-017** | R2 | Push the branch and **tag** `4d09baef1`; the remote is 71 commits stale, so today there is no identifiable deployment artefact |
| **DG-004 / B-22** | R2 | Transfer by `git clone`/`rsync`, never `git archive` — `.gitattributes:20` `export-ignore`s `tools/`, which carries the branding tooling |

---

### GROUP 3 — Live-instance data or configuration mutation (needs Owner authorisation + one re-baseline)

Every item here writes to the accepted dataset or the live `globals`. The dataset carries two named
human sign-offs, so PB-046's precedent applies: **batch them, and pay one re-baseline, not five.**

| Item | Register | What the fix is |
|---|---|---|
| **PB-081 defect** | R1 | ⚠ **Highest-value item in this group.** The RDY-0044-B v2 baseline — the demo reset source — was taken 4 minutes before the authorised UUID fix and ships 13 NULL UUIDs. Live is clean, so this needs **only a re-dump**: force a `UUID_Service` run, confirm `SUM(uuid IS NULL)=0` on `form_vitals` and `insurance_companies`, re-take, supersede v2, re-hash, re-verify the reset proof |
| **RDY-0016 residual** | R1 | Seed **one sensitivity-flagged encounter** and **one clinician-authored form** — authorised at PB-077 (D-3 change 2), never applied. Without them 4 matrix rows cannot be executed at all, and **RDY-0057's sensitivity claim has never been exercised in either direction** |
| **RDY-0035** | R1 (P1) | Clear the two `pqri_*` placeholders |
| **D-2 residual** | R3 | Repoint `portal_onsite_two_address` off `your_web_site.com` |
| **RDY-0019** | R1 (P2) | Assign or deliberately withhold break-glass; set `Emergency_Login_email_id` |
| **DG-010 / B-24** | R2 | Create the Accounting demo actor — the `users` ACL group has 0 members, so a billing walkthrough has no actor |
| **B-12 / DG-011** | R2 | Deactivate `Email_Service` (also relaxes the cron tick 2 min → 240 min) and confirm no MTA on `localhost:25` |
| **RDY-0083 caveat** | R1 | The trigger runs **as the logged-on user** and dies on logoff — correct for this host, **must not be copied into the pilot runbook** |
| **Post-reset check** | R1 | Every demo reset returns both active services to overdue and (until the re-baseline) wipes the UUIDs. Add a post-reset background-service check to `EV-044` |

---

### GROUP 4 — One manual browser session (a person, an hour, no engineering)

This is the single highest-leverage hour available. **One session discharges eight items.**

| Item | What must be seen |
|---|---|
| **RDY-0013 / 0014 / 0015** | The role accounts behave correctly in-application (curl cannot reach `main.php` — PB-016) |
| **RDY-0042** | Under `r.aldosari` with the `front_office` role, `Add Patient` is reachable **and completes a registration**, tested with the global on and off |
| **RDY-0043** | The encounter-forms menu renders every form in every category (PR-15 is proven at source and by harness, never in the UI) |
| **RDY-0025** | The 10 seeded documents visibly carry `SYNTHETIC DEMO / NOT A REAL PATIENT` |
| **RDY-0082 leg 6** | The one remaining restore leg — authenticated login against the restored instance |
| **RDY-0023 (part)** | SOAP-note review by a clinician — but see the growth-chart decision in Group 6 |

---

### GROUP 5 — Execution by a second person who did not author the artefact

The procedure exists and is written. Its acceptance criterion is *someone else running it cold.*

| Item | Register | Blocked on |
|---|---|---|
| **RDY-0047** | R1 | An independent provisioner building a clinic instance from `EV-047`. **The Ubuntu demo deployment is its first execution and its acceptance test** (DG-013 — and `EV-047` has no Ubuntu variant yet, B-28) |
| **RDY-0071** | R1 | An outside reviewer opening the export package cold. Also genuinely incomplete: **1 of 8** CSV-capable reports has been exported |
| **RDY-0041** | R1 | Two D-7 rehearsals **driven by a person**, with real elapsed time — V-8 and PRC-003 both consume that number |
| **RDY-0060 / 0062** | R1 | The screenshot inventory (SS-01…SS-12) and the flagship audit-integrity recording. `EV-061` §8 is the review instrument |
| **RDY-0061** | R1 | Rules are written; the per-image check cannot run until RDY-0060's captures exist |
| **RDY-0094** | R1 | The no-go register exists in §40; the **rehearsal against it** has not happened |

---

### GROUP 6 — Human decisions and named sign-offs (no engineering, no predecessor)

| Item | Register | The single action |
|---|---|---|
| **RDY-0002** | R1 | Record acceptance of the GTM's VERDICT B and its seven provisional items |
| **RDY-0003** | R1 | Reviewer **named** (Mohammed Elfouly, PB-077) — but **naming is not reviewing.** The review step must actually run |
| **RDY-0056 / 0057 / 0067 / 0088** | R1 | All four are complete artefacts waiting on that same sign-off |
| **RDY-0004** | R1 | Instrument issued (`EV-004`); binds when the Phase 3/4/5 briefs exist |
| **RDY-0023 decision** | R1 | The growth-chart criterion is **unsatisfiable** — no seeded patient is under 21. Owner must waive the criterion or authorise a paediatric patient |
| **RDY-0084 decision** | R1 | One word: does *"owner"* in the monitoring requirements mean a role or a named individual? |
| **RDY-0096** | R1 | Published support hours and response targets — a staffing decision |
| **RDY-0055** | R1 | The audit-log PHI exposure is **measured** (`EV-055`); what remains is the *determination* of how it is handled at pilot |
| **RB-17, D-12, D-14, D-15, D-16** | R3/R4 | Ratifications and wording decisions carried since 2026-08-10 |
| **PB-057 letterhead question** | R1 | Whether the facility/letterhead fix rides along under the same authorisation — explicitly **not** covered by D-3 |

---

### GROUP 7 — Legal and regulatory determinations (external, commissioned or not)

| Item | Register | State |
|---|---|---|
| **RDY-0095 / U-3 / D-3** | R1/R3 | Licence-and-attribution determination — **commissioned to SkyEagle (PB-077), determination outstanding.** It alone blocks **RDY-0033, RDY-0034, RDY-0090 and gate G4 entirely** |
| **D-11** | R3 | Counsel review of `acknowledge_license_cert.html`. It is 403-blocked at Apache and its links are suppressed — **access suppression is not legal clearance** |
| **D-4** | R3 | Native-Arabic proofreading. No code change can close it |
| **RDY-0078 / U-4 / U-5** | R1 | **V-10** — read *primary* ZATCA and NPHIES sources and record the date accessed. Everything published today rests on secondary sources |
| **RDY-0028 residual** | R1 | Closed at PB-045, but note the synthetic-data control's standing obligation: the scans must be re-run on every dataset change |
| **B-11 / B-16** | R2 | Public exposure needs D-3/D-4/D-11 clearance; until then, IP allowlist. Real NewCrop production endpoints stay reachable behind `erx_enable = 0` only |

---

### GROUP 8 — Infrastructure provisioning (hosting chain — one blocker, five dependants)

| Item | Register | Blocked on |
|---|---|---|
| **RDY-0064** | R1 | Region **decided** (Dammam, `me-central2`, PB-022). **Provisioning is external and has not happened** — this is the root of the chain |
| **RDY-0081** | R1 | Backup policy needs an **off-instance target**, which does not exist until hosting does |
| **RDY-0082** | R1 | Data legs pass; a disposable-target restore needs the target |
| **RDY-0085 / B-10** | R1/R2 | TLS needs a domain and certificate |
| **RDY-0084 / B-29** | R1/R2 | Monitoring requirements are written (`EV-084`, six signals); implementation needs the host |
| **B-09 / B-18 / RDY-0048** | R2/R1 | ⚠ **The live DB password is the unchanged upstream default `openemr`.** The Phase 2A candidate closure for RDY-0048 **should be withdrawn** (`EV-048`). Loopback binding and "no real data" are what limit it today — **neither survives a public VM** |
| **B-05 / Option 3** | R2 | Adopt "fresh database + controlled configuration/data migration"; cloning the dev DB imports four known-wrong states (version row `8.3.0-dev`, four Windows-path globals, `saas_branding_revision = 1` against gitignored stylesheets, 70,638 dev audit rows) |
| **B-06 / B-07** | R2 | At provisioning: run `thiqa-branding:provision-report-acl`, and insert the branding module's `modules` row — **files alone give an unbranded app with no error** |

---

### GROUP 9 — Market validation and primary research (interviews and calls)

Zero engineering. Zero technical predecessors. **This is the group that has moved least.**

| Item | What it is |
|---|---|
| **RDY-0075 (V-1)** | Does a reachable population of self-pay Saudi outpatient clinics exist? **It validates the wedge the entire plan serves, and §45.2 recorded months ago that there is no technical reason it has not started** |
| **RDY-0076 (V-2)** | Will they accept a clinical system separate from invoicing? |
| **RDY-0077 (V-3)** | Are record control and portability *felt pains*, not merely true statements? |
| **RDY-0079 (V-9, P1)** | 30 named ophthalmology clinics, 5 reached; the founder-network count |
| **RDY-0065** | Use the qualification checklist on **three consecutive calls** and record each in/out decision |
| **RDY-0066** | Legal review of the scope template |
| **RDY-0068** | Draft the pilot agreement — it must cite RDY-0073 |
| **RDY-0069** | Cost instrumentation. **Needs a pilot to exist. Not startable** |
| **RDY-0070 (P1)** | Funnel instrumentation with baselines, not targets |
| **RDY-0088 / 0089 (P1)** | Re-run Source C against its 9 unverified dossiers before any competitive frequency is published |
| **RDY-0086 residual** | Arabic coverage is **measured** (`EV-086`, picklists 16.1 %); the screen walk needs a **named Arabic Reviewer, who does not exist** |

---

### GROUP 10 — Upstream patch programme

| Item | Register | State |
|---|---|---|
| **RDY-0045** | R1 | 418 behind / 92 ahead / **71 unpushed**, no longer an ancestor of upstream. `EV-045` analyses the target; **the `master` vs `rel-820` decision has not been made** and no merge may start before it and RDY-0082 both land |
| **V-09 re-run** | R4 | The dry-run covered 6 of 17 core patch files. PB-054 re-ran it against all 16 patch records (one mechanical conflict, not a patched file), but `setup.php` and `sql_upgrade.php` — the two most upstream-churned — are still unspoken for |
| **B-26** | R2 | A security-patch pipeline must exist before this becomes a customer instance |
| **RDY-0046** | R1 | **Closed** (PB-048) — provenance determined, upstream's, inert. Listed only so it is not re-opened |

---

### GROUP 11 — Register hygiene and reconciliation (documentation only, executable now)

**This group is a prerequisite for trusting any of the others.** Every item is a factual defect in the
record, found this pass.

| ID | Finding |
|---|---|
| **F-1** | **The §7 register is stale by 28 rows.** Only `RDY-0001` and `RDY-0080` carry `CLOSED` in §7.2–§7.18. The other 28 closures (0010–0015, 0017, 0020–0022, 0024, 0026–0028, 0032, 0036–0038, 0040, 0044, 0046, 0050–0054, 0058, 0059) exist **only in the PB log**. Anyone reading §7 alone gets a materially wrong picture |
| **F-2** | **The counts do not reconcile.** §1.4 states *31 closed / 83 open / 42 open P0*. Deriving mechanically from the PB log gives **30 unique closed IDs / 84 open / 43 open P0** (the extra is likely `RDY-0044-A` being counted as its own row, which §1.4 elsewhere explicitly forbids). Gate counts inherit the same off-by-one |
| **F-3** | **The gate dashboard (§47) is stale.** Its table is the PB-051 sync; §1.4 carries the PB-059 sync (G0 3 · G1 22 · G2 16 · G3 20 · G4 3 · G5 13 · G6 21); §47's own table still shows G1 28 · G2 32 · G3 26. Three different sets of figures are live in one document |
| **F-4** | **~12 statements still assert the background-service runner "has never executed."** PB-071 corrected this on 2026-08-14 and listed the survivors; they were never swept. §1.2, §47, §48.B and "PHASE 2 IN 20 LINES" item 12 all still carry it |
| **F-5** | **The headline sections still describe an empty, single-user, `Your Clinic Name Here` system** (§1.2, §1.3, §47.1, §48.B, PHASE 2 IN 20 LINES items 1, 2, 7). Live: 30 patients, 7 logins, `Thiqa Demo Eye Clinic`, Saudi locale. See §2 above |
| **F-6** | **Agent A and Agent B disagree on the record.** `EV-000` §2 lists RDY-0013/0014/0015 as blocked on a browser session; §1.4 lists all three as **closed at PB-029**. One of the two is wrong, and both are committed |
| **F-7** | **`B-06` in the deployment register is contradicted live** — the `patients\|bulk_rep` ACO exists on this instance (six report ACOs present). The target-instance provisioning step is still required; the "not even on dev" evidence is not |
| **F-8** | **RDY-0006/0007/0009 (evidence repository, change control, gate sign-off) are still open** while 26 `EV-*` files, a PB log and five registers have accumulated. The governance mechanism is being outrun by its own output — F-1 through F-7 are the direct consequence |
| **F-9** | **`docs/demo-deployment-readiness.md` is untracked.** A 146 KB certified deployment analysis exists only in the working tree of one machine |
| **F-10** | **PR-15's verification harness (`scratchpad/menu-verify.php`) is untracked and gitignored**, so its "re-runnable" claim does not resolve for anyone else. `docs/evidence/harnesses/` exists for exactly this |

---

### GROUP 12 — Deferred by policy — record only, do not start

Listed so nobody re-discovers them as "gaps to fix now". Their priority belongs to the GTM, not to
this analysis.

| Set | Items |
|---|---|
| **R1 P1 roadmap** | RDY-0097 ZATCA Phase 2/VAT (schema change) · 0098 Arabic completion + RTL + shaping font · 0099 MFA enforcement · 0100 NPHIES pathway · 0101 provisioning automation |
| **R1 P2** | 0102 dashboards · 0103 patient portal · 0104 Saudi identifiers/Hijri · 0105 sensitivity beyond encounters · 0106 audit-log noise (93 % ACL-read) · 0107 HMAC + chained audit rows · 0108 lab-order audit global |
| **R1 P3** | 0109 denial management · 0110 telehealth · 0111 lab/eRx interfacing · 0112 orphan schema cleanup · 0113 current-year quality measures · 0114 dispensary/group therapy |
| **R2 P2/P3** | B-19…B-34 — Apache hardening, cron user, `PrivateTmp`, OPcache tuning, `log` growth policy, `mod_deflate`/`mod_expires` |
| **R5 — the whole SaaS backlog** | BLK-001…006 (**BLOCKER priority**), MVP-001…014, PROD-001…009, P2-001…004, OPT-001…005. **323 acceptance criteria, none ticked.** ⚠ These are *not* safely deferrable by silence: R5 is the binding Locked Decisions register and it labels six items **BLOCKER** and fourteen **BEFORE MVP**, while R1 defers the overlapping ones (ZATCA, NPHIES, Arabic, MFA) to P1. **That conflict is unresolved, and RDY-0092 is the row that must resolve it** |

---

## 4. What this means in one paragraph

The engineering is very nearly done. Of 43 open P0 readiness items, **six or seven are code or build
work**; the rest are waiting on a name, a decision, a phone call, a legal opinion, a hosting account or
a person opening a browser. The three things that would move the most, in order, are: **(1)** the
licence determination (RDY-0095 — it alone unblocks four rows and all of gate G4), **(2)** one manual
browser session (it discharges eight rows), and **(3)** V-1 (RDY-0075 — it validates the premise of
everything else and has no predecessor of any kind). The one genuinely urgent *engineering* item is the
**RDY-0044-B v3 re-baseline**: today the documented demo-reset artefact restores the defect it was
authorised to remove, so every reset re-introduces 13 NULL UUIDs.

## 5. Suggested order of execution

1. **Group 11 first (F-1, F-2, F-3, F-4, F-5).** Half a day of documentation work. Until the register
   matches the PB log, every subsequent decision is made on stale figures.
2. **Group 3's re-baseline (PB-081).** One dump; removes a live defect from the reset path.
3. **Group 4 — the browser session.** Eight items, one hour, no prerequisites.
4. **Groups 6 and 7 in parallel** — they are pure waiting time and should have started already.
5. **Group 1 code batch**, each with its patch record; then **Group 2's build and tag**, which is what
   makes an Ubuntu deployment possible at all.
6. **Group 8** once hosting is provisioned; **Group 10** only after RDY-0082 and the `master`/`rel-820`
   decision; **Group 9** independently and continuously — it never blocks engineering and engineering
   never blocks it.
