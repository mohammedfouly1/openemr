<?php

/**
 * The supported Saudi theme variants.
 *
 * Locked decision Q77 fixes the product at exactly two user-selectable variants.
 * This is a unit enum because the variant is runtime state; the persisted value is
 * the stylesheet filename, which is mapped by ThemeResolver rather than backed here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Theme;

enum ThemeVariant
{
    case Light;
    case Dark;

    /**
     * The stylesheet filename the variant materialises as.
     *
     * Filenames are inherited deliberately (decision CR-9): the fallback literal at
     * interface/globals.php:476 is hardcoded to style_light.css, so renaming would
     * force a recurring core patch for no user-visible benefit. "Saudi Light" and
     * "Saudi Dark" are the product-facing labels for these two files.
     */
    public function stylesheet(): string
    {
        return match ($this) {
            self::Light => 'style_light.css',
            self::Dark => 'style_dark.css',
        };
    }

    /** The product-facing label. Not what the administration selector displays (decision CR-18). */
    public function label(): string
    {
        return match ($this) {
            self::Light => 'Saudi Light',
            self::Dark => 'Saudi Dark',
        };
    }
}
