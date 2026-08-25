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
        // DELIBERATELY the old directory name, and it must stay that way. These scripts
        // describe the live `demo-openemr` host, where the deployed directory really is
        // `oe-module-thiqa-branding` until that host is redeployed. Rewriting the reference
        // would make the runbook lie about the machine it operates. S1-P1-11 classified this
        // as historical operational identity; the SkyEagle rename changes the repository, not
        // the record of what is currently deployed elsewhere.
        //
        // The SkyEagle bulk rename DID rewrite these two assertions, and this test caught it —
        // which is precisely the job it was written for.
        self::assertStringContainsString('`oe-module-thiqa-branding`', $readme);
        self::assertStringContainsString('Pre-existing external bucket identifier', $readme);
        self::assertStringNotContainsString('r2:thiqa-demo-backups/', $readme);
        self::assertStringContainsString('BRANCH=feat/thiqa-branding-foundation', $deployment);
        // Same reason as the README assertion above: the deployment script's path is the one
        // that exists on the live host today.
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
