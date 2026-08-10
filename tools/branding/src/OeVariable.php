<?php

/**
 * One `--oe-*` compatibility alias and the Thiqa token it resolves to.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * The alias is variant-aware because a few OpenEMR roles deliberately invert:
 * the light theme's navbar is a dark bar for contrast, so it maps to the navy
 * brand colour rather than to a light surface.
 */
final readonly class OeVariable
{
    public function __construct(
        public string $group,
        public string $name,
        public string $lightPath,
        public string $darkPath,
        public ?string $note = null,
    ) {
    }

    public function pathFor(Variant $variant): string
    {
        return match ($variant) {
            Variant::Light => $this->lightPath,
            Variant::Dark => $this->darkPath,
        };
    }
}
