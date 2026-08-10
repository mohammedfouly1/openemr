<?php

/**
 * The complete `--oe-*` custom-property surface OpenEMR themes already consume.
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
 * These thirteen names are the full set declared in
 * `interface/themes/oe-styles/style_light.scss`. Emitting all of them as
 * aliases of Thiqa tokens means every existing selector that reads
 * `var(--oe-…)` keeps working after the retheme, with no selector edits.
 */
final readonly class OeVariableMap
{
    private const GROUP_CANVAS = 'Main canvas';
    private const GROUP_SURFACE = 'Surface / cards / modals';
    private const GROUP_NAVBAR = 'Top navbar';
    private const GROUP_FIELDSET = 'Fieldsets & boxes';
    private const GROUP_SEARCH = 'Search bar / global search';
    private const GROUP_PANEL = 'General surface override for panels, widgets, etc';

    /**
     * @param list<OeVariable> $variables
     */
    private function __construct(private array $variables)
    {
    }

    public static function create(): self
    {
        return new self([
            new OeVariable(self::GROUP_CANVAS, '--oe-body-bg', 'background', 'background'),
            new OeVariable(self::GROUP_CANVAS, '--oe-body-color', 'text.primary', 'text.primary'),

            new OeVariable(self::GROUP_SURFACE, '--oe-surface-bg', 'surface', 'surface'),

            new OeVariable(
                self::GROUP_NAVBAR,
                '--oe-navbar-bg',
                'brand.navy',
                'surfaceRaised',
                'variant-specific: the navy brand colour in light, the raised surface in dark',
            ),
            new OeVariable(self::GROUP_NAVBAR, '--oe-navbar-color', 'text.inverse', 'text.primary'),
            new OeVariable(
                self::GROUP_NAVBAR,
                '--oe-navbar-link-hover-bg',
                'surfaceSunken',
                'border',
                'must read lighter than the bar it sits on, in either variant',
            ),
            new OeVariable(self::GROUP_NAVBAR, '--oe-navbar-link-hover-color', 'text.primary', 'text.primary'),
            new OeVariable(
                self::GROUP_NAVBAR,
                '--oe-navbar-link-active-bg',
                'interactive.primary.default',
                'interactive.primary.default',
            ),
            new OeVariable(
                self::GROUP_NAVBAR,
                '--oe-navbar-link-active-color',
                'interactive.primary.textOn',
                'interactive.primary.textOn',
            ),

            new OeVariable(self::GROUP_FIELDSET, '--oe-fieldset-bg', 'surfaceSunken', 'surfaceSunken'),
            new OeVariable(self::GROUP_FIELDSET, '--oe-fieldset-color', 'text.primary', 'text.primary'),

            new OeVariable(
                self::GROUP_SEARCH,
                '--oe-search-bg',
                'divider',
                'divider',
                'one step more recessed than a fieldset',
            ),

            new OeVariable(self::GROUP_PANEL, '--oe-panel-bg', 'surface', 'surface'),
        ]);
    }

    /**
     * @return list<OeVariable>
     */
    public function variables(): array
    {
        return $this->variables;
    }
}
