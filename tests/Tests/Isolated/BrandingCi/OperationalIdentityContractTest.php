<?php

/**
 * Keeps reusable operational tooling neutral without rewriting compatibility history.
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

final class OperationalIdentityContractTest extends TestCase
{
    private const PROJECT_ROOT = __DIR__ . '/../../../..';

    public function testReusableDateRebaseLabelsAreBrandNeutral(): void
    {
        $script = $this->read('docs/evidence/ubuntu-infra-scripts/05-seed001-demo-date-rebase.sh');

        self::assertStringNotContainsString('Thiqa', $script);
        self::assertStringContainsString('OpenEMR demo seed whole-week date re-base', $script);
        self::assertStringContainsString('Run the OpenEMR demo date re-base daily', $script);
    }

    public function testHistoricalAndCompatibilityIdentifiersAreExplicitlyPreserved(): void
    {
        $readme = $this->read('docs/evidence/ubuntu-infra-scripts/README.md');
        $deployment = $this->read('docs/evidence/ubuntu-infra-scripts/07-deploy-code-update.sh');

        self::assertStringContainsString('Operational identity classification (S1-P1-11', $readme);
        self::assertStringContainsString('`feat/thiqa-branding-foundation`', $readme);
        self::assertStringContainsString('`oe-module-thiqa-branding`', $readme);
        self::assertStringContainsString('Pre-existing external bucket identifier', $readme);
        self::assertStringNotContainsString('r2:thiqa-demo-backups/', $readme);
        self::assertStringContainsString('BRANCH=feat/thiqa-branding-foundation', $deployment);
        self::assertStringContainsString('oe-module-thiqa-branding/', $deployment);
        self::assertStringContainsString('IDENTITY CLASSIFICATION (S1-P1-11)', $deployment);
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents(self::PROJECT_ROOT . '/' . $relativePath);
        self::assertIsString($contents, sprintf('Unable to read %s', $relativePath));

        return $contents;
    }
}
