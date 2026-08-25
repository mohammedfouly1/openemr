<?php

/**
 * Keeps D-8 governance aligned with the shipped token materialisation path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenAI
 * @copyright Copyright (c) 2026 OpenAI
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use PHPUnit\Framework\TestCase;

final class D8DependencyStatusContractTest extends TestCase
{
    private const PROJECT_ROOT = __DIR__ . '/../../../..';

    public function testCurrentMaterialiserStillActivatesTheStaticWriter(): void
    {
        $materialiser = $this->read(
            'interface/modules/custom_modules/oe-module-skyeagle-branding/src/Materialisation/BrandingMaterialiser.php'
        );
        $writer = $this->read(
            'interface/modules/custom_modules/oe-module-skyeagle-branding/src/Materialisation/TokenCssWriter.php'
        );
        $paths = $this->read(
            'interface/modules/custom_modules/oe-module-skyeagle-branding/src/Materialisation/TenantBrandingPaths.php'
        );

        self::assertStringContainsString('$this->cssWriter->stage(', $materialiser);
        self::assertStringContainsString('$this->files->stage($this->targetPath(', $writer);
        self::assertStringContainsString("ThemeVariant::Light => 'tokens-light.css'", $paths);
        self::assertStringContainsString("ThemeVariant::Dark => 'tokens-dark.css'", $paths);
    }

    public function testAuthoritativeDocumentsAgreeThatD8IsOpen(): void
    {
        $plan = $this->read('docs/RebrandingPlan.md');
        self::assertStringContainsString('| **D-8** | Writable, execution-denied volume', $plan);
        self::assertStringContainsString('**OPEN — blocks a read-only module tree**', $plan);
        self::assertStringContainsString('**Current register status (reconciled 2026-08-24): 3 of 13 dependencies closed**', $plan);
        self::assertStringNotContainsString('| ~~D-8~~ |', $plan);
        self::assertStringContainsString('| R-3 | Tier 2 token CSS still requires a writable module tree', $plan);
        self::assertStringNotContainsString('| R-3 | ~~Tier 2 token CSS requires', $plan);
        self::assertStringContainsString('| V-05 | All 38 WCAG pairs recomputed', $plan);

        $dependencies = $this->read('docs/branding/remaining-dependencies.md');
        self::assertStringContainsString(
            '**RE-OPENED 2026-08-10 (RB-04); VERIFIED STILL OPEN 2026-08-24 after S1-P0-09**',
            $dependencies
        );

        $adr = $this->read('docs/branding/adr/ADR-BRAND-001-five-plane-architecture.md');
        self::assertStringContainsString('Treat D-8 as **OPEN**', $adr);
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents(self::PROJECT_ROOT . '/' . $relativePath);
        self::assertIsString($contents, sprintf('Unable to read %s', $relativePath));

        return $contents;
    }
}
