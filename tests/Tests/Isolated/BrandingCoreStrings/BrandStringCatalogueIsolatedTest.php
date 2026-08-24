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

    /**
     * S2-P1-22 removed the last three. An English override is a `lang_id=1` row, and English is
     * the only locale it reaches — every other locale fell through to the unbranded upstream
     * literal, which is why Arabic users saw OpenEMR branding on those surfaces. The composed
     * `%s`-key architecture covers all locales at once, so the mechanism has no remaining use.
     */
    public function testNoEnglishCatalogueOverridesRemainActive(): void
    {
        self::assertSame([], $this->catalogue['english_overrides']);
    }

    public function testEveryRetiredKeyCarriesExactValueRetirementMetadata(): void
    {
        $retired = array_column($this->catalogue['retired_english_overrides'], null, 'constant');

        self::assertSame(
            [
                'OpenEMR Application',
                'Welcome to OpenEMR',
                'OpenEMR',
                'OpenEMR Authorization',
                'OpenEMR Login',
            ],
            array_keys($retired)
        );
        self::assertSame('Thiqa Authorization', $retired['OpenEMR Authorization']['managed_english']);
        self::assertSame('Thiqa Login', $retired['OpenEMR Login']['managed_english']);
        self::assertSame('Thiqa Application', $retired['OpenEMR Application']['managed_english']);
        self::assertSame('Welcome to Thiqa', $retired['Welcome to OpenEMR']['managed_english']);
        self::assertSame('Thiqa', $retired['OpenEMR']['managed_english']);

        foreach ($retired as $entry) {
            // A retired entry must not name consumers: that field is what made the dead OAuth
            // overrides look live for months.
            self::assertArrayNotHasKey('consumers', $entry);
            self::assertNotSame('', $entry['reason']);
        }
    }

    /**
     * No former consumer may still reference a retired constant, in either family: the OAuth
     * templates or the Zend layouts. A lingering reference would mean the key is still live and
     * the retirement metadata is lying.
     */
    public function testNoRetiredConstantIsStillReferencedByItsFormerConsumer(): void
    {
        $consumers = array_merge($this->oauthTemplatePaths(), [
            'interface/modules/zend_modules/module/Application/view/layout/layout.phtml',
            'interface/modules/zend_modules/module/Documents/view/layout/layout.phtml',
            'interface/modules/zend_modules/module/Application/view/layout/sendto.phtml',
        ]);

        foreach ($this->catalogue['retired_english_overrides'] as $entry) {
            foreach ($consumers as $consumer) {
                self::assertStringNotContainsString(
                    "xl('" . $entry['constant'] . "')",
                    $this->readProjectFile($consumer),
                    $consumer . ' still translates the retired constant ' . $entry['constant'] . '.',
                );
            }
        }
    }

    /**
     * S2-P1-23: the OAuth surfaces compose a single translatable unit rather than juxtaposing the
     * product name with a separately translated word, so word order is translator-controlled.
     */
    public function testOAuthComposesASingleTranslatableUnit(): void
    {
        foreach ($this->oauthTemplatePaths() as $template) {
            $contents = $this->readProjectFile($template);
            self::assertStringContainsString('{{ "%s Authorization"|xlp|text }}', $contents);
            self::assertStringNotContainsString('{{ applicationTitle }}', $contents);
        }

        $login = $this->readProjectFile('templates/oauth2/oauth2-login.html.twig');
        self::assertStringContainsString('{{ "%s Login"|xlp|text }}', $login);
    }

    /**
     * S2-P1-22: the Zend layouts compose the product name in rather than translating a
     * brand-bearing key, and the bare-name surface reads the global directly.
     */
    public function testZendLayoutsComposeTheProductName(): void
    {
        $application = $this->readProjectFile(
            'interface/modules/zend_modules/module/Application/view/layout/layout.phtml',
        );
        self::assertStringContainsString("xl('%s Application')", $application);
        self::assertStringContainsString('ProductContextTranslation::compose', $application);

        $documents = $this->readProjectFile(
            'interface/modules/zend_modules/module/Documents/view/layout/layout.phtml',
        );
        self::assertStringContainsString("xl('Welcome to %s')", $documents);

        $sendto = $this->readProjectFile(
            'interface/modules/zend_modules/module/Application/view/layout/sendto.phtml',
        );
        self::assertStringContainsString("getString('openemr_name')", $sendto);
        self::assertStringNotContainsString('translate()->xl', $sendto);
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
