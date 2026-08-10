<?php

/**
 * Raised when the Tier 1 token document does not match the token contract.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Token;

use InvalidArgumentException;

/**
 * Tier 1 tokens come from the repository, so a malformed document is a build fault,
 * not a user action — throwing is correct here. Tenant-supplied Tier 2 overlays take
 * the other path: TokenValidator reports them instead (plan §3.4.1).
 */
final class TokenParseException extends InvalidArgumentException
{
    public static function unknownKey(string $dotPath): self
    {
        return new self(sprintf('Token key "%s" is not in the allowlist.', $dotPath));
    }

    public static function malformedValue(string $dotPath): self
    {
        return new self(sprintf('Token "%s" must be a #RRGGBB string.', $dotPath));
    }

    public static function malformedDocument(string $detail): self
    {
        return new self(sprintf('Token document is malformed: %s', $detail));
    }
}
