<?php

/**
 * Prevents delivered PDF font assets from being mistaken for runtime Arabic PDF support.
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

final class PdfFontCapabilityClaimContractTest extends TestCase
{
    private const PROJECT_ROOT = __DIR__ . '/../../../..';

    public function testRuntimeAndInstallerDescribeTheCurrentCapabilityTruthfully(): void
    {
        $installer = $this->read('tools/branding/install-assets.php');
        $config = $this->read('src/Pdf/Config_Mpdf.php');
        $lock = json_decode($this->read('composer.lock'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($lock);

        self::assertStringNotContainsString('registered with mPDF', $installer);
        self::assertStringContainsString(
            'Q25 Arabic PDF candidate asset; not registered with a PDF engine (D-9 open)',
            $installer
        );
        self::assertStringNotContainsString('Amiri', $config);
        self::assertStringNotContainsString('NotoNaskh', $config);
        self::assertStringContainsString("'default_font' => 'dejavusans'", $config);

        $packagesRaw = $lock['packages'] ?? [];
        self::assertIsArray($packagesRaw);
        $packages = array_column($packagesRaw, null, 'name');
        $mpdf = $packages['mpdf/mpdf'] ?? null;
        self::assertIsArray($mpdf);
        self::assertSame('v8.3.1', $mpdf['version'] ?? null);
    }

    public function testPublishedEvidenceKeepsArabicPdfUnavailableAndD9Open(): void
    {
        $rtl = $this->read('docs/branding-production/09-rtl-bilingual-evidence.md');
        $release = $this->read('docs/branding-production/12-release-verification.md');
        $dependencies = $this->read('docs/branding/remaining-dependencies.md');
        $evidence = $this->read('docs/evidence/EV-RB14-mpdf-gpos.md');

        self::assertStringContainsString('Arabic PDF remains an explicitly accepted pilot limitation', $rtl);
        self::assertStringNotContainsString('Arabic PDF rendering test using the vendored `IBM Plex', $rtl);
        self::assertStringContainsString('**Revision 7 (2026-08-24) — current.', $release);
        self::assertStringContainsString('not registered with either', $release);
        self::assertStringContainsString('**OPEN — Owner accepted the limitation through pilot', $dependencies);
        self::assertStringContainsString('**ADOPTED: Option C — accept open through pilot.**', $evidence);
        self::assertStringContainsString('GPOS Lookup Type 5, Format 3 not supported', $evidence);
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents(self::PROJECT_ROOT . '/' . $relativePath);
        self::assertIsString($contents, sprintf('Unable to read %s', $relativePath));

        return $contents;
    }
}
