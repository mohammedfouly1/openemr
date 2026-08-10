<?php

/**
 * Outcome of validating one candidate tenant logo.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\AssetIntake;

/**
 * All-or-nothing by construction, exactly like
 * {@see \OpenEMR\Modules\ThiqaBranding\Token\TokenValidationResult}: `asset()` is
 * non-null only when there are no rejections.
 *
 * A "mostly valid" image does not exist. Every check here is a security boundary, so
 * one failure refuses the file and the tenant keeps the logo from revision n-1, which
 * is the same failure posture as the materialiser (plan section 4.4).
 */
final readonly class AssetValidationResult
{
    /**
     * @param list<AssetRejection> $rejections
     */
    private function __construct(
        private ?ValidatedAsset $asset,
        private array $rejections,
    ) {
    }

    public static function accepted(ValidatedAsset $asset): self
    {
        return new self($asset, []);
    }

    public static function rejected(AssetRejection ...$rejections): self
    {
        return new self(null, array_values($rejections));
    }

    public function isValid(): bool
    {
        return $this->rejections === [];
    }

    /** The placeable asset, or null when the candidate was refused. */
    public function asset(): ?ValidatedAsset
    {
        return $this->asset;
    }

    /** @return list<AssetRejection> */
    public function rejections(): array
    {
        return $this->rejections;
    }

    /** @return list<string> one human-readable line per rejection, safe to log */
    public function messages(): array
    {
        return array_map(
            static fn (AssetRejection $rejection): string => $rejection->message(),
            $this->rejections,
        );
    }
}
