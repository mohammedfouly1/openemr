<?php

/**
 * A validated tenant/site identifier.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Tenant;

use InvalidArgumentException;
use Stringable;

/**
 * The single tenant-identity guard for the whole branding layer.
 *
 * Callers that touch the filesystem or the database ({@see \OpenEMR\Modules\ThiqaBranding\AssetIntake\AssetPlacer},
 * {@see \OpenEMR\Modules\ThiqaBranding\Materialisation\BrandingMaterialiser}) accept this
 * type rather than a string, so every traversal payload — `../../..`, `/etc`,
 * `C:\Windows`, a UNC prefix, an embedded NUL that truncates the path inside a C library
 * — is rejected at construction, far from the call that would act on it.
 *
 * The allowlist is positive: one alphanumeric leading character, then alphanumerics,
 * underscores and hyphens. `.` is excluded entirely, which makes `..` unrepresentable
 * rather than merely filtered, and also rules out `.git`-style dot directories.
 *
 * The `\z` anchor is deliberate. `$` would also match immediately before a trailing
 * newline, so `"default\n"` would pass a `$`-anchored pattern.
 *
 * **Why this lives in its own namespace.** Two independent copies of this class
 * previously existed, one under `AssetIntake` and one under `Materialisation` (audit
 * finding B-05). Both were correct, but a security guard maintained in two places is a
 * guard that will eventually be hardened in one place only. There is now exactly one.
 */
final readonly class SiteId implements Stringable
{
    /**
     * Upper bound on the raw string.
     *
     * The two implementations this class replaced disagreed: one capped at 64, the other
     * at 63. The stricter bound is kept deliberately — a consolidation that quietly
     * relaxes a security guard is precisely the failure mode the consolidation exists to
     * prevent (audit finding B-05). The explicit length check runs before the pattern so
     * an oversized value fails with a length message rather than a vaguer character-set
     * one.
     */
    public const MAX_LENGTH = 63;

    private const PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9_-]{0,62}\z/';

    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Site id must not be empty.');
        }

        if (strlen($value) > self::MAX_LENGTH) {
            throw new InvalidArgumentException('Site id exceeds the maximum permitted length.');
        }

        // Checked explicitly rather than left to the pattern, so the failure is named:
        // a NUL is a truncation attack, not a typo.
        if (str_contains($value, "\0")) {
            throw new InvalidArgumentException('Site id contains a NUL byte.');
        }

        if (preg_match(self::PATTERN, $value) !== 1) {
            throw new InvalidArgumentException('Site id contains characters outside the permitted set.');
        }
    }

    /** Null instead of an exception, for callers parsing a batch of jobs or CLI input. */
    public static function tryFrom(string $value): ?self
    {
        try {
            return new self($value);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
