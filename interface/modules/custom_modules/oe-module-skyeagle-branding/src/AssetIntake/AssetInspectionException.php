<?php

/**
 * Internal signal that a candidate image failed one specific inspection step.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\AssetIntake;

use RuntimeException;

/**
 * Thrown only by {@see RasterImageReader} and {@see SvgInspector}, and caught only by
 * {@see LogoValidator}, which converts it into an {@see AssetRejection}. It never
 * escapes the package and its message is never shown to a user.
 *
 * A structural parser fails at a dozen different depths; threading a result object back
 * up through every one of them would obscure the parsing logic that is the security
 * boundary. The throw sites are all inside this package and the single catch is at the
 * package boundary, so the exception is a local jump, not error-handling by exception.
 *
 * @internal
 */
final class AssetInspectionException extends RuntimeException
{
    private function __construct(
        public readonly AssetRejectionReason $reason,
        public readonly string $detail,
    ) {
        parent::__construct($detail);
    }

    /** @param string|null $detail Must be built from fixed text and integers only. */
    public static function because(AssetRejectionReason $reason, ?string $detail = null): self
    {
        return new self($reason, $detail ?? $reason->summary());
    }
}
