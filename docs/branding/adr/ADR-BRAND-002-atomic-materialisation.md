# ADR-BRAND-002: Materialisation is one atomic transaction, revision written last

**Status:** Accepted and implemented; supersedes an earlier, defective design found and fixed during this
project (audit finding **AR-P2-001**).

## Context

Locked `Q76` requires: "tenant-scoped, idempotent materialisation; `globals` never the source of truth"
and "CP unavailable → last-good branding; failed materialisation leaves previous revision intact"
(`docs/RebrandingPlan.md` §2.2, AC 6–7). Principle **P6** states the layer must "fail to last-known-good,
never to upstream identity" (§3.1).

**The defect (AR-P2-001).** An earlier revision of `BrandingMaterialiser` wrote the `globals` delta (token
overlays, branding strings) and the revision bump (`saas_branding_revision`) in **two separate database
transactions**. A failure between the two left the delta live under a stale revision number — a partially
applied state that `Q76` explicitly forbids, and one that revision-based cache-busting could not mask,
because not every branding value is revision-addressed (the product name and other `globals` strings are
read directly, not through a revisioned URL). This is documented directly in the shipped source's own
docblock: `BrandingMaterialiser.php:78-85`, "The seam that used to be here is closed... Audit finding
AR-P2-001."

## Decision

Collapse the whole write side of a materialisation run into **one** database transaction, via
`BrandingGlobalsWriterInterface::writeAll()` (implemented by
`QueryUtilsBrandingGlobalsWriter::writeAll()`, `src/Materialisation/QueryUtilsBrandingGlobalsWriter.php:102-127`),
with the transaction's contents committed in this order, and the revision explicitly **last inside the
transaction** (not merely last in wall-clock time — same transaction, same commit):

1. the globals delta (token overlays, branding strings),
2. the materialisation timestamp,
3. `saas_branding_revision = target`.

The reasoning is spelled out in the class's own docblock (`BrandingMaterialiser.php:60-69`): every branding
URL the application emits carries the revision (`?rev=<n>` on the token stylesheet, `&rev=<n>` on each
logo, inside the SMART style payload). Until the revision write commits, every URL the running application
emits is still an *n-1* URL, and every cache entry behind those URLs is an *n-1* entry — even though the
*n* bytes may already be sitting at their final filesystem paths from steps 3–5a/5b of the same run (file
staging happens *before* the database transaction; see `BrandingMaterialiser.php:225-317`). The switch from
*n-1* to *n* is therefore a single integer write inside a transaction that a browser can never observe
half-done: it sees revision *n-1* consistently, or revision *n* consistently, and nothing in between.

Filesystem writes (token stylesheets, logo binaries) are staged to temporary paths, verified by re-reading
and re-hashing, and only renamed into place *before* the database transaction opens
(`BrandingMaterialiser.php:44-58`, steps 3–5a/5b). If the database transaction itself fails, the renamed
files are reverted (`unwind()`, `BrandingMaterialiser.php:325-339`) — but the database side needs no
special unwind logic, because `writeAll()` owns and rolls back its own transaction on any failure. The
combination means a failed run leaves the tenant on revision *n-1*, filesystem and database both, provably
rather than by convention.

## Consequences

- **Idempotence is now a precondition, not a hope.** `BrandingMaterialiser::materialise()` reads the live
  revision first and returns `unchanged()` without staging or writing anything if the target is not
  strictly greater (`BrandingMaterialiser.php:149-158`) — see `docs/branding/runbook.md` §3 for why this
  also means there is no "roll back to revision N" operation, only "roll forward to a new revision
  carrying old values."
- **Recovery from a failed run needs no separate command.** A plain retry of the same
  `thiqa-branding:materialise` invocation is the documented recovery path (`docs/branding/runbook.md` §7),
  because the previous revision was never partially overwritten in the first place.
- **Verified in isolated tests, not yet live.** `docs/branding/remaining-dependencies.md` V-02/V-03: the
  `Materialisation` isolated PHPUnit suite (50/51 pass on a full run, `MaterialiserKillRecoveryTest`
  individually 1/1 pass) exercises this transaction model, including a simulated kill mid-run. **No live
  materialisation has ever executed against the one existing tenant** (`thiqa-branding:verify --site=default`
  reports `never materialised` / `revision 0`, reconfirmed live in this session — see
  `docs/branding/runbook.md` §6.1) — so idempotence and the atomic-commit property are code- and
  test-verified, not yet operationally exercised.

## References

- `interface/modules/custom_modules/oe-module-thiqa-branding/src/Materialisation/BrandingMaterialiser.php:38-86` (design docblock, AR-P2-001 note)
- `interface/modules/custom_modules/oe-module-thiqa-branding/src/Materialisation/QueryUtilsBrandingGlobalsWriter.php:102-127` (`writeAll()`)
- `docs/RebrandingPlan.md` §2.2 AC 6–7 (`Q76`), §3.1 P6
- `docs/branding/coverage-matrix.md` row 42 ("code and design complete, live exit criterion... never executed")
- `docs/branding/remaining-dependencies.md` §3, rows V-02/V-03
