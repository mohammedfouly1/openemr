<?php

/**
 * The outcome of one materialisation attempt.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Materialisation;

use LogicException;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;

/**
 * A returned value, not an exception. A tenant sending a colour that fails a contrast
 * gate, or a Control Plane declaring a checksum that does not match the bytes, are
 * expected events on this path — `Q76` requires a failed revision to be *retryable and
 * auditable*, which means the caller has to be able to read the outcome rather than
 * unwind a stack. The only throws here are for programmer error: claiming a failure
 * with nothing to report.
 *
 * `revision()` is the revision that is **live when this object is returned**, never the
 * one that was attempted. On success that is the target; on failure it is n-1, still
 * fully serving, because the revision global is written last (plan §4.4).
 *
 * `retryable` distinguishes the two failure kinds:
 *  - a transient fault (disk, database) — retry the same payload;
 *  - a rejected payload — retrying is pointless, the payload must change.
 */
final readonly class MaterialisationResult
{
    /**
     * @param list<string> $messages
     */
    private function __construct(
        private bool $succeeded,
        private bool $changed,
        private BrandingRevision $revision,
        private array $messages,
        private bool $retryable,
    ) {
        if (!$succeeded && $messages === []) {
            throw new LogicException('A failed materialisation must carry at least one message.');
        }

        if ($succeeded && $retryable) {
            throw new LogicException('A successful materialisation is never retryable.');
        }
    }

    /** The target revision is now live and the tenant's files and globals were changed. */
    public static function applied(BrandingRevision $revision): self
    {
        return new self(true, true, $revision, [], false);
    }

    /**
     * The tenant is already at or past the target: nothing was read, staged or written.
     *
     * This is the idempotence contract (plan §4.3 WP-2.8, "second run is a no-op") in
     * value form — the caller can assert on it rather than inferring a no-op from the
     * absence of side effects.
     */
    public static function unchanged(BrandingRevision $revision): self
    {
        return new self(true, false, $revision, [], false);
    }

    /**
     * The payload was refused. Revision n-1 is untouched and retrying will refuse again.
     *
     * @param list<string> $messages one line per rejection, already safe to log
     */
    public static function rejected(BrandingRevision $activeRevision, array $messages): self
    {
        return new self(false, false, $activeRevision, $messages, false);
    }

    /**
     * A transient fault stopped the run. Revision n-1 is fully active; retry the payload.
     *
     * @param list<string> $messages generic, caller-safe lines — never an exception message
     */
    public static function failed(BrandingRevision $activeRevision, array $messages): self
    {
        return new self(false, false, $activeRevision, $messages, true);
    }

    public function succeeded(): bool
    {
        return $this->succeeded;
    }

    public function isFailure(): bool
    {
        return !$this->succeeded;
    }

    /** False when the run was a no-op because the revision was already applied. */
    public function changed(): bool
    {
        return $this->changed;
    }

    /** The revision live now: the target on success, n-1 on failure. */
    public function revision(): BrandingRevision
    {
        return $this->revision;
    }

    /** @return list<string> */
    public function messages(): array
    {
        return $this->messages;
    }

    /** True only for transient faults; a refused payload is not retryable. */
    public function isRetryable(): bool
    {
        return $this->retryable;
    }
}
