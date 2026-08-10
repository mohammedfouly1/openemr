<?php

/**
 * One step of the Thiqa type scale.
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
 * Sizes and line heights are integer pixels in the token source; the renderer
 * appends the unit so the JSON stays unit-agnostic.
 */
final readonly class TypeScaleStep
{
    public function __construct(
        public string $key,
        public int $size,
        public int $lineHeight,
        public int $weight,
    ) {
    }

    /**
     * `bodyLarge` becomes `body-large`.
     */
    public function slug(): string
    {
        $kebab = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $this->key);
        if ($kebab === null) {
            throw new GeneratorException(sprintf('Type scale key "%s" could not be slugified.', $this->key));
        }

        return strtolower($kebab);
    }
}
