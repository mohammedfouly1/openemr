# EV-084 — MONITORING REQUIREMENTS FOR A HOSTED PILOT

**Requirement:** RDY-0084 · **Gates:** G3 · **Owner:** DevOps / Infrastructure
**Acceptance:** *"Each of the six has a defined signal, threshold, destination and owner; the
monitoring decision record names whether tooling has been selected, or states explicitly that it has
not."*
**Produced:** 2026-08-14 · **Agent B**, Phase 2B

**The brief is explicit: name requirements, not a vendor.** No tool is chosen here, and §5 states
that plainly rather than leaving it to be inferred.

---

## 1. The six signals

Owners are **roles**, per the §36 RACI. **No individual's name is invented anywhere in this
document.**

| # | Signal | What is measured | Threshold | Destination | Owner (role) |
|---|---|---|---|---|---|
| **M-1** | **Application availability** | HTTP `GET` of the login page returns **200** with a body > 5 KB | **2 consecutive failures**, checked every 60 s | Paging channel — must wake someone | DevOps / Infrastructure |
| **M-2** | **Error rate** | New `E_ERROR` / `E_PARSE` / uncaught-exception lines in the PHP error log | **Any** fatal, or **> 10** warnings in 5 min | Non-paging alert channel, reviewed same business day | DevOps / Infrastructure |
| **M-3** | **Disk capacity** | Free space on the volume holding the database **and** `sites/*/documents/` | **Warn < 20 %, page < 10 %** | Warn → alert channel; page → paging channel | DevOps / Infrastructure |
| **M-4** | **Database status** | `mariadbd` reachable; a `SELECT 1` succeeds; replication/lag if ever introduced | **2 consecutive failures**, checked every 60 s | Paging channel | DevOps / Infrastructure |
| **M-5** | **Backup success** | A backup **completed** in the last 24 h **and** its file is non-zero, has the expected `CREATE TABLE` count, and its checksum was recorded | **No successful backup in 24 h**, or table count ≠ expected | Paging channel — this one is not "review it tomorrow" | DevOps / Infrastructure |
| **M-6** | **Background-service health** | No `active = 1` service has `next_run` in the past by more than **2 × its own `execute_interval`** | Any active service overdue by that margin | Alert channel | DevOps / Infrastructure |

---

## 2. Why each threshold is what it is

Recorded because an unexplained threshold gets tuned away the first time it is inconvenient.

- **M-1 / M-4 use two consecutive failures**, not one. A single failed poll on this stack is more
  likely to be a transient than an outage, and a monitor that cries wolf is switched off.
- **M-2 pages on *any* fatal**, because a PHP fatal on a clinical screen is a clinician unable to
  finish a consultation, not a statistic.
- **M-3 watches the documents volume as well as the database.** `mysqldump` does not capture document
  payloads (`EV-071` §3.1), so the two grow independently and a document-volume fill is a distinct
  failure.
- **M-5 pages rather than warns.** RDY-0082's whole point is that a backup nobody has restored is an
  assumption with a file attached; a backup that silently stopped running is worse, because the
  assumption persists.
- **M-6's "2 × interval" tolerance** is derived from live behaviour, not guessed. `Email_Service`
  runs on a 2-minute interval and `UUID_Service` on 240; a fixed threshold would either spam on the
  first or never fire on the second.

---

## 3. ⚠ M-6 is the signal that already has a live failure to detect

**This is not a hypothetical control.** As of 2026-08-14 both active services are overdue on this
instance — `Email_Service` and `UUID_Service` last claimed at ≈13:02–13:13 on 2026-08-13, with no
recurring trigger since (`EV-083`).

**M-6 would have fired on 2026-08-13, and nobody would have had to notice it by reading a table.**
The RDY-0083 trigger is built and held disabled pending one decision; **M-6 is what stops the same
condition recurring silently on a customer instance**, where the symptom would be unsent
appointment reminders.

---

## 4. What the product already provides, and why it is not monitoring

Named so that nobody mistakes an existing screen for a solved requirement:

| Surface | What it gives | Why it is not enough |
|---|---|---|
| Diagnostics screen | Environment and configuration state | **Someone must remember to open it** |
| Background-services report | `next_run` per service | Same — and it is precisely the table that showed 2021 dates for months without anyone acting |
| IP tracker | Failed-login and block state | Security-adjacent, not availability |
| `log` / audit trail | 74k+ rows | Forensic, not alerting; 82 % of it is ACL-read noise (L-22) |

**All four are pull, and monitoring is push.** That is the whole gap.

---

## 5. Tooling decision — explicitly NOT made

**No monitoring tool has been selected.** This is stated as required by the acceptance criterion
rather than left implicit.

The choice is deliberately deferred because it depends on **RDY-0064 (hosting)**: a managed platform
may supply M-1, M-3 and M-4 natively, which would make selecting a tool now premature and possibly
wasteful. **M-5 and M-6 will need bespoke checks regardless of platform**, because no general-purpose
monitor understands "a backup with the right `CREATE TABLE` count" or "`next_run` relative to
`execute_interval`".

**Named constraints for whoever chooses:** it must be able to run a custom check returning an exit
code (for M-5/M-6), distinguish a paging destination from a non-paging one, and not require an agent
that cannot run in the customer's hosting arrangement.

---

## 6. Acceptance

| Criterion | Result |
|---|---|
| Each of the six has a defined **signal** | **MET** — M-1…M-6 |
| …a defined **threshold** | **MET**, with the derivation for each in §2 |
| …a defined **destination** | **MET** — paging vs alert channel distinguished per signal |
| …a defined **owner** | **MET at role level** — DevOps / Infrastructure, per §36 RACI. **No individual is named**, because inventing a person's name is forbidden and no staffing decision exists |
| The decision record names whether tooling has been selected, **or states explicitly that it has not** | **MET** — §5 states explicitly that it has not, and why |

### Status: **RDY-0084 — requirements COMPLETE. Recommended for closure, with one reservation stated below.**

**The reservation, so the Owner closes it knowingly rather than on my say-so.** The criterion asks
for an *owner*, and this document supplies a **role**, not a person. Every other owner field in the
register is also a role, and §36 is a role-based RACI, so this is consistent — **but if the Owner
reads "owner" as requiring a named individual, this criterion is not met and RDY-0084 stays open.**
That is a one-word decision and it is not mine to make.

**Nothing here is implemented.** These are requirements for a hosted pilot; there is no hosted pilot,
and implementation is gated on RDY-0064.

**`Blocks`: G3.** No gate count moved (§0.0 Rule 3) — **Agent B does not recalculate gate counts, and
does not mark its own work closed.** If the Owner accepts the role-level owner, this is a closure for
the next sync pass.
