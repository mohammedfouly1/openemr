# EV-046 — MODULE PROVENANCE DETERMINATION

**Requirement:** RDY-0046 · **Gate:** G3 · **Date:** 2026-08-14
**Module:** `oe-module-claimrev-connect`
**Determination:** **RETAIN — fully accounted for. It is an upstream OpenEMR dependency, not an
unexplained artefact of this fork.**

---

## 1. Provenance — every field the acceptance criteria ask for

| Field | Value | Source |
|---|---|---|
| **Package** | `claimrevolution/oe-module-claimrev-connect` | `composer.json:52` |
| **Constraint** | `^2.1` | `composer.json:52` |
| **Installed version** | **v2.1.6** | `composer.lock` |
| **Origin** | `https://github.com/claimrevolution/oe-module-claimrev-connect.git` — public GitHub | `composer.lock` |
| **Pinned reference** | **`978b0dd498e0e166992259926d6fa77bf56266d4`** | `composer.lock` |
| **Licence** | **GPL-3.0** | `composer.lock` |
| **Type** | `openemr-module` | `composer.lock` |
| **Released** | 2026-06-23 | `composer.lock` |
| **Purpose** | ClaimRev Connect — **claims / revenue-cycle connectivity** (clearinghouse integration) | package name, `moduleConfig.php`, `ModuleManagerListener.php` |
| **On disk** | 134 files | `find` |
| **Tracked in this tree** | **0 files** — by `.gitignore:15` | `git ls-files`, `git check-ignore` |

## 2. The audit's framing was wrong, and the evidence is unambiguous

Source B recorded this as a module *"of unknown provenance"* and RDY-0046 calls it *"a supply-chain
provenance gap."* **Both overstate it.**

| Claim | Finding |
|---|---|
| Unknown provenance | **Fully determinable** from `composer.lock`: public repo, exact commit, GPL-3.0, dated release |
| A gap peculiar to this fork | **`upstream/rel-820:composer.json` line 52 requires it too.** Verified with `git show upstream/rel-820:composer.json` |
| Gitignored suspiciously | **`.gitignore:15` is upstream's own rule**, added by the same commit |
| Introduced here | Added by **`248783e99` — `feat(claimrev): install ClaimRev Connect module as Composer dep`**, authored by **`claimrevolution`**, 2026-05-29, **in upstream** |

**It is a declared, pinned, licensed dependency of upstream OpenEMR, gitignored by upstream's own
policy because composer-installed modules are not vendored.** That is ordinary composer practice, not
a supply-chain gap.

## 3. Is it needed for the locked ICP? No — and it is already inert

The locked ICP explicitly does not do claims. GTM DEM-003 keeps billing out of the demo narrative and
the register states plainly: *"we do not submit insurance claims."* A claims/RCM connector is
therefore outside scope.

**It is already doing nothing.** Verified live:

| Check | Result |
|---|---|
| Registered in the `modules` table | **NO.** Six modules are registered — `Immunization`, `Syndromicsurveillance`, `Documents`, `Ccr`, `Carecoordination`, `Thiqa Branding`. **`claimrev` is not among them** |
| Therefore booted at runtime | **No** — OpenEMR loads modules from that table |
| Referenced by Thiqa code, `src/` or `library/` | **None** |

**The module sits on disk, unregistered and unloaded.** It transmits nothing, contacts nothing, and
appears on no screen.

## 4. Why removal is the wrong call

Removal means editing `composer.json` to drop a dependency that **upstream declares**. That would:

1. **Diverge from upstream's dependency set** — directly enlarging the merge conflict surface that
   EV-045 just measured. Having established the runtime gap to `rel-820` is three lines, deliberately
   adding a `composer.json` divergence to remove an inert package is a poor trade.
2. **Create a recurring conflict** on every future catch-up, in the one file most likely to conflict.
3. **Gain nothing measurable** — the module is already unregistered and unloaded. Removing it changes
   no runtime behaviour, no attack surface that is currently reachable, and no screen.

**The disciplined position is to account for it, not to delete it.**

## 5. Does this satisfy D-1's "freely verifiable" claim?

Yes, and this was the substantive concern. The claim is that *every capability is freely verifiable
against the open-source project*. This module is:

- **published at a public URL**, under **GPL-3.0**;
- **pinned to an exact commit** (`978b0dd4…`) in `composer.lock`, **which is tracked in this
  repository**;
- **required by upstream OpenEMR itself**, so verifying it is verifying upstream.

Anyone can fetch that exact commit and read it. **The claim and the artefact do not disagree.**

> **One wording tension, stated rather than glossed.** RDY-0046's acceptance says *"if retained, it is
> under version control."* The module's **files** are not vendored into this tree. Its **identity and
> exact commit are** — `composer.lock` is tracked and pins the reference. I read the criterion's
> intent as *"we can account for and verify it,"* which is fully met. **Vendoring the 134 files would
> satisfy the words while diverging from upstream and worsening RDY-0045.** If the Owner reads the
> criterion literally instead, the disposition changes and this determination should be revisited.

## 6. Determination

**RETAIN.** Origin, version, pinned reference, licence, purpose and disposition are all recorded
above. The module is inert in this instance, is upstream's dependency rather than this fork's, and
its source is publicly verifiable at a tracked, pinned commit.

**Residual items, neither blocking:**

- **If the ICP ever adds claims**, this module becomes in-scope and needs a real evaluation —
  functionality, data flows, and whether it transmits outside the Kingdom. **That evaluation has not
  been done and must not be skipped at that point.**
- **It should be confirmed still unregistered after any upgrade**, since module upgrades can register
  themselves. A one-line check belongs in the RDY-0047 deployment runbook.
