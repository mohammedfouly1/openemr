<?php

/**
 * The two theme variants the branding token pipeline emits.
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
 * Unit enum: the variant is never persisted, it only selects a sub-tree of the
 * token document and names the generated artefacts.
 */
enum Variant
{
    case Light;
    case Dark;

    /**
     * Key used both as the token-document root and in generated file names.
     */
    public function key(): string
    {
        return match ($this) {
            self::Light => 'light',
            self::Dark => 'dark',
        };
    }

    /**
     * Human label used in generated file headers and comments.
     */
    public function label(): string
    {
        return match ($this) {
            self::Light => 'Light',
            self::Dark => 'Dark',
        };
    }

    /**
     * Declaration order is fixed so generated output never depends on caller order.
     *
     * @return list<self>
     */
    public static function all(): array
    {
        return [self::Light, self::Dark];
    }
}
