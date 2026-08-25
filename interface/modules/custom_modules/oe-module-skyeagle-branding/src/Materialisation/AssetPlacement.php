<?php

/**
 * One logo binary a materialisation job wants placed into a tenant's slot.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Materialisation;

use InvalidArgumentException;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\AssetIntake\ValidatedAsset;

/**
 * Parse, don't validate, at the asset boundary: by the time an AssetPlacement exists,
 * its filename is a bare safe name and its declared checksum is a well-formed SHA-256
 * digest. Neither can therefore be re-checked wrongly downstream, and neither can
 * contribute a path segment.
 *
 * The Control Plane declares the checksum (plan §3.3.2: it holds "the reference and
 * checksum", never the bytes). `matchesDeclaredChecksum()` is the tenant-side half of
 * plan §4.4 step 4 — the tenant recomputes rather than believing the declaration.
 *
 * **Deep content validation is mandatory and cannot be skipped.** An AssetPlacement can
 * only be built from a {@see ValidatedAsset}, whose constructor is private and whose sole
 * factory is {@see LogoValidator}. Magic bytes, extension-versus-content agreement,
 * dimensions, size bounds, SVG allowlisting and polyglot detection have therefore all run
 * before this type can exist.
 *
 * That is a deliberate change from an earlier design in which this type accepted raw bytes
 * plus a self-declared checksum. Under that design nothing invoked the validator from the
 * materialisation path at all, so a caller supplying bytes together with the matching hash
 * of those same bytes passed every check — audit finding AR-P2-002. Making the bypass
 * unrepresentable is stronger than remembering to call the validator, and is the same
 * technique used for TokenKey and locked Invariant 9.
 */
final readonly class AssetPlacement
{
    /** A bare filename: alphanumeric start, no separators, no dot-leading forms, one extension. */
    private const FILENAME_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9_-]{0,47}\.[A-Za-z0-9]{1,8}\z/';

    /** Lower-case hexadecimal SHA-256. */
    private const CHECKSUM_PATTERN = '/\A[0-9a-f]{64}\z/';

    private function __construct(
        public LogoSlot $slot,
        public string $filename,
        public string $contents,
        public string $declaredChecksum,
    ) {
        if (preg_match(self::FILENAME_PATTERN, $filename) !== 1) {
            // The rejected name is untrusted and is never echoed back into the message.
            throw new InvalidArgumentException('Asset filename must be a bare name of the form "name.ext".');
        }

        if ($contents === '') {
            throw new InvalidArgumentException('Asset contents must not be empty.');
        }

        if (preg_match(self::CHECKSUM_PATTERN, $declaredChecksum) !== 1) {
            throw new InvalidArgumentException('Asset checksum must be a lower-case hex SHA-256 digest.');
        }
    }

    /**
     * The only way to obtain an AssetPlacement.
     *
     * Requiring a ValidatedAsset is what closes AR-P2-002: the parameter type cannot be
     * satisfied without LogoValidator having certified the bytes first.
     *
     * The declared checksum is still carried and still recomputed later by
     * {@see matchesDeclaredChecksum()}. That is not redundant with validation: it detects
     * corruption between the Control Plane's declaration and the bytes that arrived, which
     * is a transport concern rather than a content one.
     */
    public static function fromValidated(ValidatedAsset $asset, string $declaredChecksum): self
    {
        return new self(
            $asset->slot,
            $asset->targetFilename(),
            $asset->contents,
            $declaredChecksum,
        );
    }

    /**
     * Convenience for a caller that trusts the validator's own digest.
     *
     * ValidatedAsset::$sha256 is computed by the validator from the bytes it inspected, so
     * this cannot disagree with the contents.
     */
    public static function fromValidatedSelfChecked(ValidatedAsset $asset): self
    {
        return self::fromValidated($asset, $asset->sha256);
    }

    /** The tenant's own recomputation of the digest the Control Plane declared. */
    public function actualChecksum(): string
    {
        return hash('sha256', $this->contents);
    }

    public function matchesDeclaredChecksum(): bool
    {
        return hash_equals($this->declaredChecksum, $this->actualChecksum());
    }
}
