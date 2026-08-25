<?php

/**
 * The immutable metadata of a single branding global.
 *
 * One instance per BrandingGlobalKey case, produced by a single exhaustive match so
 * that label, description, value type and default live together in one place rather
 * than drifting across four parallel matches.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Config;

use LogicException;

final readonly class BrandingGlobalDefinition
{
    /**
     * @param string             $label                   Short human name shown in Administration.
     * @param string             $description             One-line explanation shown beside the field.
     * @param BrandingValueType  $valueType               How BrandingConfigFactory parses the raw value.
     * @param string|int|bool    $default                 Safe fallback used when the global is absent or blank.
     *                                                    Never an upstream OpenEMR identity value.
     * @param bool               $editableInAdministration Whether an administrator may type this value.
     *                                                    False for everything the materialiser owns, which is
     *                                                    what keeps locked constraint C1 true.
     */
    public function __construct(
        public string $label,
        public string $description,
        public BrandingValueType $valueType,
        public string|int|bool $default,
        public bool $editableInAdministration = false,
    ) {
    }

    /**
     * @throws LogicException when the key was not declared as a string-valued global.
     */
    public function stringDefault(): string
    {
        if (!is_string($this->default)) {
            throw new LogicException('Branding global "' . $this->label . '" does not carry a string default.');
        }

        return $this->default;
    }

    /**
     * @throws LogicException when the key was not declared as an integer-valued global.
     */
    public function intDefault(): int
    {
        if (!is_int($this->default)) {
            throw new LogicException('Branding global "' . $this->label . '" does not carry an integer default.');
        }

        return $this->default;
    }

    /**
     * @throws LogicException when the key was not declared as a boolean-valued global.
     */
    public function boolDefault(): bool
    {
        if (!is_bool($this->default)) {
            throw new LogicException('Branding global "' . $this->label . '" does not carry a boolean default.');
        }

        return $this->default;
    }

    /**
     * The default rendered the way OpenEMR stores globals: booleans as '1'/'0'.
     */
    public function defaultAsGlobalsValue(): string
    {
        if (is_bool($this->default)) {
            return $this->default ? '1' : '0';
        }

        return (string) $this->default;
    }
}
