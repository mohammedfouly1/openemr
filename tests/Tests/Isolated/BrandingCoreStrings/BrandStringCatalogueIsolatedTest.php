<?php

/**
 * Brand string catalogue lifecycle and tenant-safety regression guards.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenAI
 * @copyright Copyright (c) 2026 OpenAI
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCoreStrings;

use OpenEMR\Branding\RetiredEnglishOverrideDecision;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BrandStringCatalogueIsolatedTest extends TestCase
{
    private const PROJECT_ROOT = __DIR__ . '/../../../..';

    /** @var array<string, mixed> */
    private array $catalogue;

    protected function setUp(): void
    {
        $raw = file_get_contents(self::PROJECT_ROOT . '/tools/branding/brand-strings.json');
        self::assertIsString($raw);

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        $this->catalogue = $decoded;
    }

    public function testOnlyLiveZendKeysRemainActive(): void
    {
        $active = array_column($this->catalogue['english_overrides'], 'english', 'constant');

        self::assertSame([
            'OpenEMR Application' => 'Thiqa Application',
            'Welcome to OpenEMR' => 'Welcome to Thiqa',
            'OpenEMR' => 'Thiqa',
        ], $active);

        foreach ($this->catalogue['english_overrides'] as $entry) {
            self::assertNotEmpty($entry['consumers']);
            foreach ($entry['consumers'] as $consumer) {
                $contents = $this->readProjectFile($consumer);
                self::assertStringContainsString($entry['constant'], $contents);
            }
        }
    }

    public function testDeadOAuthKeysHaveExactValueRetirementMetadata(): void
    {
        $retired = array_column($this->catalogue['retired_english_overrides'], null, 'constant');

        self::assertSame(
            ['OpenEMR Authorization', 'OpenEMR Login'],
            array_keys($retired)
        );
        self::assertSame('Thiqa Authorization', $retired['OpenEMR Authorization']['managed_english']);
        self::assertSame('Thiqa Login', $retired['OpenEMR Login']['managed_english']);

        foreach ($retired as $constant => $entry) {
            self::assertArrayNotHasKey('consumers', $entry);
            self::assertNotSame('', $entry['reason']);
            foreach ($this->oauthTemplatePaths() as $template) {
                self::assertStringNotContainsString($constant, $this->readProjectFile($template));
            }
        }
    }

    public function testOAuthUsesTenantTitleAndTranslatedActionPhrases(): void
    {
        foreach ($this->oauthTemplatePaths() as $template) {
            $contents = $this->readProjectFile($template);
            self::assertStringContainsString('{{ applicationTitle }} {{ "Authorization"|xlt }}', $contents);
        }

        $login = $this->readProjectFile('templates/oauth2/oauth2-login.html.twig');
        self::assertStringContainsString('{{ applicationTitle }} {{ "Login"|xlt }}', $login);
    }

    public function testNeutralDatabaseUpgradeContractIsNotReintroducedAsCarryForward(): void
    {
        self::assertSame([], $this->catalogue['carry_forward']);

        $raw = $this->readProjectFile('contrib/util/language_translations/contracts/database-upgrade.json');
        $contract = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('%s Database Upgrade', $contract['target_key']);
        self::assertArrayHasKey('OpenEMR Database Upgrade', $contract['legacy_keys']);
        self::assertArrayHasKey('Thiqa Database Upgrade', $contract['legacy_keys']);
    }

    #[DataProvider('retirementDecisionProvider')]
    public function testRetirementDecisionPreservesFreshUpgradeAndCustomizedTenants(
        ?string $existing,
        string $managed,
        string $expected
    ): void {
        require_once self::PROJECT_ROOT . '/tools/branding/src/RetiredEnglishOverrideDecision.php';

        self::assertSame(
            $expected,
            RetiredEnglishOverrideDecision::forDefinition($existing, $managed)->name
        );
    }

    /**
     * @return array<string, array{?string, string, string}>
     */
    public static function retirementDecisionProvider(): array
    {
        return [
            'fresh install: row absent is already clean' => [
                null,
                'Thiqa Login',
                'AlreadyAbsent',
            ],
            'upgrade: exact former managed row is deleted' => [
                'Thiqa Login',
                'Thiqa Login',
                'DeleteManaged',
            ],
            'existing tenant: custom English text is preserved' => [
                'Clinic Sign In',
                'Thiqa Login',
                'PreserveDifferent',
            ],
            'exact comparison is case sensitive' => [
                'thiqa Login',
                'Thiqa Login',
                'PreserveDifferent',
            ],
            'exact comparison is Unicode safe' => [
                'ثقة Login',
                'ثقة Login',
                'DeleteManaged',
            ],
        ];
    }

    public function testApplyScriptConstrainsRetirementToExactEnglishDefinition(): void
    {
        $contents = $this->readProjectFile('tools/branding/apply-brand-strings.php');

        self::assertStringContainsString("BINARY constant_name = ?", $contents);
        self::assertStringContainsString("AND lang_id = ? AND BINARY definition = ?", $contents);
        self::assertStringContainsString('RetiredEnglishOverrideDecision::forDefinition', $contents);
        self::assertStringContainsString('ENGLISH_LANG_ID', $contents);
        self::assertStringNotContainsString('DELETE FROM lang_constants', $contents);
    }

    /** @return non-empty-list<string> */
    private function oauthTemplatePaths(): array
    {
        return [
            'templates/oauth2/oauth2-login.html.twig',
            'templates/oauth2/patient-select.html.twig',
            'templates/oauth2/scope-authorize.html.twig',
        ];
    }

    private function readProjectFile(string $relativePath): string
    {
        $contents = file_get_contents(self::PROJECT_ROOT . '/' . $relativePath);
        self::assertIsString($contents, sprintf('Unable to read %s', $relativePath));

        return $contents;
    }
}
