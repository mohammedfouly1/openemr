<?php

/**
 * Stage / commit / revert for a single file, with no observable half-written state.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Materialisation;

/**
 * The filesystem half of the materialisation transaction (plan §4.4 steps 3, 5a, 5b, 6).
 *
 * Why temp-then-rename rather than a plain write: `file_put_contents()` on a live target
 * is observable in its intermediate states. A browser fetching the stylesheet during the
 * write gets a truncated file, and a truncated stylesheet is a broken page that outlives
 * the request that caused it. `rename()` within one filesystem is atomic, so the target
 * only ever holds a complete previous or a complete next version.
 *
 * Why the target is moved aside rather than overwritten: plan §4.4 requires each apply
 * step to be individually reversible, and it also keeps the writer off the one
 * rename-onto-an-existing-file path whose semantics differ between platforms. Commit is
 * therefore two renames — target aside, temporary into place — and revert is their
 * inverse.
 *
 * Nothing here knows about tenants; TenantBrandingPaths has already decided the target.
 */
final readonly class AtomicFileWriter
{
    private const DIRECTORY_MODE = 0o755;

    private const FILE_MODE = 0o644;

    /** Random suffix width, in bytes, for temporary and displaced names. */
    private const SUFFIX_BYTES = 8;

    /**
     * Write $contents beside $targetPath under a name nothing serves.
     *
     * The temporary file is deliberately created in the *same directory* as the target:
     * rename() is only atomic within a filesystem, and a system temp directory is
     * routinely a different one.
     *
     * @throws FilesystemException
     */
    public function stage(string $targetPath, string $contents): StagedFile
    {
        $directory = dirname($targetPath);
        $this->ensureDirectory($directory);

        $temporaryPath = $targetPath . '.tmp-' . bin2hex(random_bytes(self::SUFFIX_BYTES));

        if (file_put_contents($temporaryPath, $contents, LOCK_EX) !== strlen($contents)) {
            $this->removeQuietly($temporaryPath);
            throw FilesystemException::notWritten($temporaryPath);
        }

        // Best effort: Windows ignores the mode, and a failure here is not a reason to
        // abandon an otherwise complete staged file.
        @chmod($temporaryPath, self::FILE_MODE);

        return new StagedFile($temporaryPath, $targetPath, hash('sha256', $contents));
    }

    /**
     * Re-read the staged bytes and confirm they still hash to what was staged.
     *
     * Plan §4.4 step 4 verifies staged content before anything is applied. Reading the
     * file back rather than trusting the in-memory string is the point: it catches a
     * short write, a full disk and a concurrent clobber, all of which would otherwise
     * only surface as a corrupt live stylesheet.
     *
     * @throws FilesystemException
     */
    public function verify(StagedFile $staged): bool
    {
        $written = file_get_contents($staged->temporaryPath);
        if ($written === false) {
            throw FilesystemException::notReadable($staged->temporaryPath);
        }

        return hash_equals($staged->checksum, hash('sha256', $written));
    }

    /**
     * Move the staged file into place, displacing any current occupant.
     *
     * @throws FilesystemException
     */
    public function commit(StagedFile $staged): CommittedFile
    {
        $previousPath = null;

        if (is_file($staged->targetPath)) {
            $candidate = $staged->targetPath . '.bak-' . bin2hex(random_bytes(self::SUFFIX_BYTES));
            if (!@rename($staged->targetPath, $candidate)) {
                throw FilesystemException::notRenamed($staged->targetPath, $candidate);
            }

            $previousPath = $candidate;
        }

        if (!@rename($staged->temporaryPath, $staged->targetPath)) {
            // Put the displaced original straight back: this commit never happened.
            if ($previousPath !== null) {
                @rename($previousPath, $staged->targetPath);
            }

            throw FilesystemException::notRenamed($staged->temporaryPath, $staged->targetPath);
        }

        return new CommittedFile($staged->targetPath, $previousPath);
    }

    /** Drop a staged file that will never be applied. Safe to call twice. */
    public function discard(StagedFile $staged): void
    {
        $this->removeQuietly($staged->temporaryPath);
    }

    /**
     * Undo a commit: restore the displaced original, or remove the file if there was none.
     *
     * Deliberately total — revert runs on the failure path, where throwing would mask
     * the original fault and abandon the remaining files mid-rollback.
     */
    public function revert(CommittedFile $committed): void
    {
        if ($committed->previousPath === null) {
            $this->removeQuietly($committed->targetPath);

            return;
        }

        $this->removeQuietly($committed->targetPath);
        @rename($committed->previousPath, $committed->targetPath);
    }

    /** Confirm a commit: the displaced original is no longer needed. Safe to call twice. */
    public function finalise(CommittedFile $committed): void
    {
        if ($committed->previousPath === null) {
            return;
        }

        $this->removeQuietly($committed->previousPath);
    }

    /**
     * @throws FilesystemException
     */
    private function ensureDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (file_exists($directory)) {
            throw FilesystemException::notADirectory($directory);
        }

        // The recursive mkdir can lose a race with a concurrent worker; the is_dir()
        // recheck is what distinguishes "someone else created it" from a real failure.
        if (!@mkdir($directory, self::DIRECTORY_MODE, true) && !is_dir($directory)) {
            throw FilesystemException::directoryNotCreatable($directory);
        }
    }

    private function removeQuietly(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
