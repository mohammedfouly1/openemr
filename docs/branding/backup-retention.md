# Managed database-backup naming and retention

This is the canonical contract for the local verified-backup set created by
`thiqa-branding:backup`. The command name remains the existing module command; backup filenames and retention
are identity-neutral so a future product rename does not split the archive.

## Managed filename contract

New backups use:

```text
managed-db-backup-v1-<label>-<YYYYMMDD-HHMMSS>.sql
```

`v1` versions the parser contract. A label is 1–63 ASCII letters, digits, underscores or hyphens, must start
with a letter or digit, and cannot contain dots, whitespace, a slash, a backslash, a drive/URI separator or a
parent-directory segment. Invalid labels fail; they are never silently sanitized. The timestamp must be a real
calendar value in the exact `YYYYMMDD-HHMMSS` grammar. A pre-existing file or sidecar with the same label and
timestamp makes creation fail rather than overwrite.

For compatibility, retention also recognizes the exact legacy format produced by earlier versions:

```text
thiqa-<legacy-label>-<YYYYMMDD-HHMMSS>.sql
```

The legacy prefix is case-sensitive because the old command emitted lower-case `thiqa-` exactly. Existing
legacy files are not renamed, copied or modified merely to migrate the application. Legacy recognition remains
enabled for the compatibility life of this module unless a future, separately approved migration proves the
archive contains none.

## Recognition boundary

A database dump is a managed retention candidate only when all of these are true:

1. it is a regular non-link file directly inside the resolved target directory;
2. its basename strictly matches the neutral or legacy grammar;
3. it has a regular non-link sibling named `<dump>.sha256`;
4. that sidecar contains 64 lower-case hexadecimal characters, two spaces, the exact dump basename and an
   optional final newline—the format written only after the command's dump/table-count verification succeeds.

This closed parser means unrelated SQL, compressed files, partial dumps, directories, case variants, malformed
dates, missing/invalid sidecars, links and unexpected extensions are never retention candidates. Compression is
not expanded here because the command does not produce a compressed variant. Managed-looking but unverified or
malformed files are counted and warned about, then left untouched.

## Ordering and `--keep`

Neutral and legacy files form one logical archive. The timestamp parsed from the filename—not mutable filesystem
mtime—is authoritative. Newest sorts first; equal parsed timestamps use descending bytewise basename order as a
stable, documented tie-breaker.

`--keep=N` means:

- `N` must be a whole number greater than zero; negative, zero, decimal, overflow and non-numeric values fail;
- `N=1` retains the newest managed backup;
- `N` equal to or greater than the managed count deletes nothing;
- zero managed backups is a successful, explicit empty inventory for the discovery/selection layer;
- directory absence, a non-directory target, unreadability or scan failure is an error, never an empty archive.

The backup command creates and verifies its new artifact before scanning, so a normal successful invocation sees
at least that one managed file. It reports the resolved target, new path, total/neutral/legacy counts, keep value,
candidate count and every successful deletion. A scan or deletion failure exits nonzero and is not described as
successful retention.

## Deletion safety

Before each deletion, the implementation re-resolves the configured directory and candidate, verifies that the
candidate is still a direct regular non-link child, reparses its basename, and revalidates its sidecar. It deletes
only the selected dump and its exact `.sha256` sibling. A failure stops pruning and returns an error. Target inputs
containing a `..` path segment or NUL byte are rejected, and a target exposed by PHP as a symbolic/reparse link is
rejected. Operators should use a real directory rather than a junction or symlink.

## Target directory and portability

The historical default `C:/openemr-stack/backups` is retained solely to avoid silently changing the destination
of existing scheduled deployments. It is not a portable production recommendation. Every other host must pass
an explicit `--target=<directory>` from its deployment configuration. The target may be an absolute or safe
relative path and may contain spaces; the implementation resolves it and builds children with the platform's
directory separator. A missing validated target is created with owner-only requested permissions. No repository,
profile or real backup location is used by the isolated tests.

## Migration and rollback

Migration is passive: deploy the repaired version and let new runs create neutral files. The first repaired run
discovers legacy and neutral verified artifacts together and applies one retention window; it does not rename old
files. No duplicate compatibility copy is created.

Rolling back to an older application version does not make neutral backups unreadable—the files and SHA-256
sidecars remain ordinary dump artifacts—but the old retention implementation sees only `thiqa-*.sql`. Before a
planned rollback, run the repaired discovery/retention tests and ensure capacity exists for neutral files during
the rollback window. While rolled back, monitor the target explicitly and do not rename neutral files into the
legacy family, because collisions and false chronology could result. Re-deploying the repaired version resumes
mixed-family retention without a migration step. This is a rollback limitation, not a reason to duplicate or
automatically rename real archives.

## Operator verification

From the repository root, without pointing at a real backup directory, verify the deterministic contract with:

```powershell
C:\openemr-stack\php\php.exe vendor\bin\phpunit -c phpunit-isolated.xml --no-coverage `
  --fail-on-empty-test-suite --fail-on-incomplete --fail-on-risky `
  tests\Tests\Isolated\Modules\ThiqaBranding\Console\BackupRetentionTest.php
```

The test creates a new uniquely named directory under the operating-system temporary directory for every test,
validates every deletion target, and removes all fixtures afterward. It never invokes `mysqldump`, reads the live
database or touches `C:/openemr-stack/backups`. The release-equivalent deterministic check remains
`composer branding-ci`.
