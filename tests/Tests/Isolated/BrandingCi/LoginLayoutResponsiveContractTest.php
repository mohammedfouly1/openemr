<?php

/**
 * Regression guard for the login page's responsive breakpoint fix (V-02/V-03).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use PHPUnit\Framework\TestCase;

/**
 * `vertical_band.html.twig` narrows its own container to 36% width once the
 * viewport is wide enough. Two live defects were found by screenshot at the
 * previous, incorrect trigger:
 *
 *  - at exactly 768px, 36% left too little room for the `col-sm-4`/`col`
 *    label+input split, causing the Username/Password/Language labels to
 *    overlap their controls;
 *  - `p-5` alone (no smaller-viewport override) overflowed narrow phone
 *    widths, clipping the input fields' right edge.
 *
 * This reads the twig source as text rather than rendering it: rendering
 * this template hangs indefinitely on the native Windows/Google-Drive dev
 * host (documented in CLAUDE.local.md — TwigExtension::getGlobals() blocks
 * on session read), so a compilation/render test cannot run here. Asserting
 * on the literal CSS values still catches the regression this fix guards
 * against: either breakpoint value drifting back to its old, defective
 * setting, or the mobile padding override disappearing.
 */
final class LoginLayoutResponsiveContractTest extends TestCase
{
    private const TEMPLATE_PATH = __DIR__ . '/../../../../templates/login/layouts/vertical_band.html.twig';

    public function testNarrowBandTriggersAtDesktopNotTablet(): void
    {
        $contents = $this->readTemplate();

        $this->assertStringContainsString(
            '@media (min-width: 992px)',
            $contents,
            'The .vertical-band max-width:36% rule must trigger at the lg (992px) breakpoint, '
                . 'not at 768px -- at 768px it leaves too little room for the label+input row and '
                . 'the Username/Password/Language labels overlap their controls.'
        );

        $this->assertStringNotContainsString(
            '@media (min-width: 768px)',
            $contents,
            'The narrow-band trigger regressed back to the 768px breakpoint that caused the '
                . 'tablet label/input overlap.'
        );
    }

    public function testMobileViewportHasReducedPadding(): void
    {
        $contents = $this->readTemplate();

        $this->assertMatchesRegularExpression(
            '/class="[^"]*\bp-3\b[^"]*\bp-md-5\b[^"]*vertical-band[^"]*"/',
            $contents,
            'The .vertical-band container must use a responsive padding scale (p-3, growing to '
                . 'p-5 at md and up) -- an unconditional p-5 overflows narrow phone viewports and '
                . 'clips the input fields.'
        );
    }

    private function readTemplate(): string
    {
        $contents = file_get_contents(self::TEMPLATE_PATH);
        self::assertIsString($contents, 'Unable to read ' . self::TEMPLATE_PATH);

        return $contents;
    }
}
