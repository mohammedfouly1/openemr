# EV-048 — SECRETS HANDLING

**Requirement:** RDY-0048 · **Gates:** G3 · **Owner:** DevOps / Infrastructure
**Acceptance:** *"No live credential is present in any tracked file; a history scan finds no
committed credential, or a remediation is recorded; the runbook (RDY-0047) contains the handling."*
**Executed:** 2026-08-14 · **Agent B**, Phase 2B

---

## 1. ⚠ The finding: the mitigation everyone has been discussing protects a secret that is not secret

The readiness document has tracked this row through three states — *"tracked and credential-bearing"*
→ *"`skip-worktree` set"* → *"committed blob pristine, **no credential has ever been committed**"* —
and recorded it as **DRIFT — IMPROVED**, a *candidate closure*.

**Every one of those statements is true. Together they miss the actual exposure.**

### 1.1 What the history scan actually shows

| Check | Result |
|---|---|
| Tracked files with a non-`H` git flag | **1** — `S sites/default/sqlconf.php` (`skip-worktree`) |
| Commits touching `sqlconf.php`, all refs | **11**, resolving to **9 distinct blobs** |
| Committed blob at `HEAD` | `e6be8476dadc010e5bc07b00b7e851418e5d5abe` |
| Same blob at `upstream/master` | **`e6be8476…` — byte-identical** |
| Same blob at the audited baseline `631f2b38c` | **`e6be8476…` — byte-identical** |
| `$config` in the committed blob | **`0`** — the "not yet configured" marker |

**So: this project committed nothing.** The blob at HEAD is upstream's, unchanged. The document's
claim *"no credential has ever been committed"* is **correct as to this project's commits**, and
`skip-worktree` genuinely does prevent an accidental `git add`.

### 1.2 What that framing misses

The committed blob — the upstream one — contains:

```php
$login  = 'openemr';
$pass   = 'openemr';
$dbase  = 'openemr';
```

And the **live** working-tree file contains:

```php
$pass   = 'openemr';
$config = 1;
```

**The live database password is `openemr`. It is the upstream placeholder, unchanged.**

That value is not confidential and never was: it ships in every clone of OpenEMR in the world, it is
in this repository's own history, and it is the first credential any attacker would try. `CLAUDE.local.md`
§3 records it as the app DB credential in plain text, which is appropriate precisely because it is
not a secret.

**The consequence for RDY-0048's acceptance is direct.** *"No live credential is present in any
tracked file"* — the tracked file contains `openemr` / `openemr`, and that **is** the live
credential. **The criterion is not met.** It is not met for a reason nobody had framed correctly:
not because a secret leaked, but because **the credential was never changed from a public default.**

### 1.3 What limits the exposure today

Stated so this is not over-read — this is a **pilot-blocker**, not a live incident:

- **MariaDB binds `127.0.0.1` only.** Not reachable off-host.
- **Least privilege is real and was proven, not assumed.** PB-007 found the app user holds
  `GRANT ALL PRIVILEGES ON \`openemr\`.*` and nothing wider — a `CREATE DATABASE` attempt was
  **denied**, which is why the restore test had to use a separate admin account. That is correct
  least privilege and is itself evidence for the security pitch.
- **There is no real patient data on this instance.**

**None of that survives contact with a hosted pilot**, where the same install pattern would run on a
reachable host with real records.

---

## 2. Remediation — specified, not applied

**Not applied**, because changing the live DB password alters the running application's
configuration mid-flight while another agent is working against it, and it belongs in the RDY-0047
runbook as a provisioning step rather than as a one-off edit here.

| # | Action | Where it belongs |
|---|---|---|
| **R-1** | **Generate a unique database password per instance at provisioning.** Never the upstream default, never shared between instances | RDY-0047 runbook, mandatory step |
| **R-2** | Keep `sqlconf.php` out of the repository entirely on customer instances — generate it at provisioning from a template | RDY-0047 |
| **R-3** | Record `skip-worktree` as a **developer convenience on this machine**, not a security control. It lives in one local git index, does not travel with a clone, is invisible to review, and will silently mask a legitimate upstream change to that file | `§45.1.3` already says this correctly — keep it |
| **R-4** | Rotate the `openemr` DB password on this demo instance before any external party touches it | Before RDY-0060 captures / any guided demo |

**R-1 is the one that matters.** The others are hygiene around it.

---

## 3. Acceptance

| Criterion | Result |
|---|---|
| No live credential is present in any tracked file | **NOT MET** — the tracked `sqlconf.php` carries `openemr`/`openemr`, which is the live credential |
| A history scan finds no committed credential, **or a remediation is recorded** | **MET (second limb)** — 9 distinct blobs scanned; every one is upstream's; **this project committed nothing**, and the remediation is recorded in §2 |
| The runbook (RDY-0047) contains the handling | **NOT MET** — RDY-0047 does not exist yet |

### Status: **RDY-0048 — NOT CLOSED, and the "candidate closure" recorded at Phase 2A should be withdrawn.**

**This is a correction to a status this document was moving toward, not a new gap.** §7.21 and
§45.1.2 row 8 both mark this row `DRIFT — IMPROVED` and *"LIVE EVIDENCE SUGGESTS STATUS CHANGE —
FORMAL CLOSURE DEFERRED TO PHASE 2B"*. **Phase 2B has now looked, and the answer is no.** The
improvement was real but partial, and closing on it would have shipped a pilot with a publicly known
database password.

**`Blocks`: G3.** No gate count moved (§0.0 Rule 3) — nothing closed, and nothing newly opened
either; the row was already open.

---

## 4. Reproduce

```bash
git ls-files -v | grep -v '^H '                       # -> S sites/default/sqlconf.php
git rev-parse HEAD:sites/default/sqlconf.php          # -> e6be8476...
git rev-parse upstream/master:sites/default/sqlconf.php  # -> e6be8476... (identical)
git show HEAD:sites/default/sqlconf.php | grep -E '\$pass|\$config'
grep -E '\$pass|\$config' sites/default/sqlconf.php   # working tree: $config = 1
```
