<?php

/**
 * One entry in the authoritative colour-token schema.
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
 * Describes a single colour token: where it lives in the token document, the
 * group it is printed under, and the fallback used when a variant legitimately
 * omits it (the fallback is always another token, never an invented colour).
 */
final readonly class ColorToken
{
    public function __construct(
        public string $group,
        public string $path,
        public ?string $fallbackPath = null,
    ) {
    }

    /**
     * `interactive.primary.textOn` becomes `interactive-primary-text-on`.
     */
    public function slug(): string
    {
        $slug = strtr($this->path, ['.' => '-']);
        $kebab = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $slug);
        if ($kebab === null) {
            throw new GeneratorException(sprintf('Token path "%s" could not be slugified.', $this->path));
        }

        return strtolower($kebab);
    }

    public function scssVariable(): string
    {
        return '$thiqa-' . $this->slug();
    }

    public function cssVariable(): string
    {
        return '--' . $this->slug();
    }

    /**
     * Compatibility name emitted for existing compiled consumers while the
     * identity-neutral custom property above remains the canonical contract.
     */
    public function legacyCssVariable(): string
    {
        return '--thiqa-' . $this->slug();
    }
}
