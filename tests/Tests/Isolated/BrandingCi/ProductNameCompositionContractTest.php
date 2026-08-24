<?php

/**
 * Regression contract: product identity is composed inside a translatable unit, never juxtaposed.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use OpenEMR\Common\Translation\MissingIdentityPolicy;
use OpenEMR\Common\Translation\ProductContextTranslation;
use OpenEMR\Common\Translation\TranslationCatalogueContractSet;
use OpenEMR\Common\Translation\TranslationDerivation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Findings S2-P1-23 and S2-P1-24. A template writing
 * `{{ "About"|xlt }} {{ applicationTitle|text }}` has made a word-order decision in PHP that no
 * translator can reach, so a right-to-left locale renders the phrase translated with the product
 * name still trailing it — `حول Thiqa` on the live Arabic shell. Seven call sites across five
 * templates did this.
 *
 * The repair is a placeholder-bearing key composed through the `xlp` filter. What makes it safe
 * rather than a different regression is the **carry-forward**: each new key derives its
 * translations from the constant the call site used before, so a locale that had a translation
 * keeps it. Without that, moving `About` to `About %s` would silently drop 24 languages.
 *
 * This contract pins both halves. The juxtaposition must not come back, and every neutral key a
 * template asks for must have a contract that populates it — a key with no contract renders its
 * own English text in every locale, which is the leak these findings describe, just relocated.
 */
#[Group('isolated')]
final class ProductNameCompositionContractTest extends TestCase
{
    /**
     * The call sites converted away from juxtaposition, and the key each now uses.
     *
     * Listed explicitly so that deleting a call site is a deliberate edit here rather than a
     * silently shrinking scan.
     */
    private const CONVERTED_SITES = [
        'templates/oauth2/oauth2-login.html.twig' => ['%s Authorization', '%s Login'],
        'templates/oauth2/patient-select.html.twig' => ['%s Authorization'],
        'templates/oauth2/scope-authorize.html.twig' => ['%s Authorization'],
        'templates/core/about.html.twig' => ['About %s'],
        'templates/insurance_companies/general_list.html.twig' => ['Insurance Companies %s'],
        'templates/product_registration/product_reg.js.twig' => ['%s Product Registration'],
    ];

    /** Templates are scanned for this shape; matching it means the defect is back. */
    private const JUXTAPOSITION = '~\{\{\s*applicationTitle[^}]*\}\}\s*\{\{|\}\}\s*\{\{\s*applicationTitle~';

    public function testNoTemplateJuxtaposesTheProductNameWithATranslatedPhrase(): void
    {
        $offenders = [];

        foreach ($this->templates() as $file => $contents) {
            if (preg_match(self::JUXTAPOSITION, $contents) === 1) {
                $offenders[] = substr($file, strlen($this->root()) + 1);
            }

            // The Twig concatenation form the product-registration template used:
            // `applicationTitle ~ " " ~ ("..."|xla)`. Delimited with `#` because the pattern
            // itself has to contain a literal `~`.
            if (preg_match('#applicationTitle\s*~#', $contents) === 1) {
                $offenders[] = substr($file, strlen($this->root()) + 1);
            }
        }

        self::assertSame(
            [],
            $offenders,
            'These templates place the product name beside a translated phrase, so the word order '
            . 'is hardcoded English and no translator can move it. Use a "<phrase> %s"|xlp key.',
        );
    }

    public function testEveryConvertedSiteUsesTheCompositionFilterWithExactlyOneEscaper(): void
    {
        foreach (self::CONVERTED_SITES as $relativePath => $keys) {
            $contents = $this->read($this->root() . '/' . $relativePath);

            foreach ($keys as $key) {
                self::assertMatchesRegularExpression(
                    '~"' . preg_quote($key, '~') . '"\|xlp\|(text|attr)~',
                    $contents,
                    $relativePath . ' must compose ' . $key . ' through xlp and escape it exactly once.',
                );
            }
        }
    }

    /**
     * `TwigContainer` builds its environment with `autoescape => false`, so an unescaped `{{ }}`
     * emits raw output. The composed value carries a tenant-supplied product name, so every call
     * site has to name its own escaper.
     */
    public function testTheCompositionFilterIsNeverEmittedUnescaped(): void
    {
        foreach ($this->templates() as $file => $contents) {
            $matches = [];
            preg_match_all('~\|xlp(\|[a-z_]+)?~', $contents, $matches);

            foreach ($matches[1] as $following) {
                self::assertContains(
                    $following,
                    ['|text', '|attr'],
                    substr($file, strlen($this->root()) + 1) . ' emits |xlp without an escaper.',
                );
            }
        }
    }

    /**
     * The half that makes the repair safe rather than a relocation of the leak: every key a
     * template composes must be created and populated by a checked-in contract.
     */
    public function testEveryComposedKeyIsBackedByATranslationContract(): void
    {
        $set = TranslationCatalogueContractSet::fromProjectDirectory($this->root());

        $targets = [];
        foreach ($set->all() as $contract) {
            $targets[$contract->targetKey] = $contract;
        }

        foreach (self::CONVERTED_SITES as $relativePath => $keys) {
            foreach ($keys as $key) {
                self::assertArrayHasKey(
                    $key,
                    $targets,
                    $relativePath . ' composes "' . $key . '", which no translation contract creates. '
                    . 'Without one the key renders its own English text in every locale.',
                );
            }
        }
    }

    /**
     * A derived contract must not point at a key that no longer exists to derive from — that would
     * carry nothing forward and leave the neutral key English-only, which is the failure this whole
     * mechanism exists to avoid. `Product Registration` is the one deliberate exception: it has no
     * catalogue row anywhere, so there is nothing to lose.
     */
    public function testEveryDerivationNamesAPlausibleSourceKey(): void
    {
        $set = TranslationCatalogueContractSet::fromProjectDirectory($this->root());

        foreach ($set->all() as $contract) {
            $derivation = $contract->derivation;
            if (!$derivation instanceof TranslationDerivation) {
                continue;
            }

            self::assertNotSame('', trim($derivation->sourceKey));
            self::assertStringNotContainsString(
                '%',
                $derivation->sourceKey,
                $contract->id . ' derives from a key that is itself a placeholder pattern.',
            );
        }
    }

    // ------------------------------------------------- S2-P1-26: the PHP-side leak surface

    /**
     * The PHP composition helper exists and delegates to the same parser as everything else.
     */
    public function testThePhpCompositionHelperUsesTheSharedParser(): void
    {
        $source = $this->read($this->root() . '/library/translation.inc.php');

        self::assertStringContainsString('function xlp(', $source);
        self::assertStringContainsString('ProductContextTranslation::compose', $source);
        self::assertStringContainsString("getString('openemr_name')", $source);
    }

    /**
     * The converted call sites, each with the literal that must no longer appear there.
     *
     * These are all **class B** in the S2-P1-26 re-derivation: the literal had no `lang_constants`
     * row at all, so no translation override could ever have reached it and changing the key
     * orphans nothing. That is why they need no carry-forward contract, unlike the class-A set.
     *
     * @return array<string, list<string>>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function convertedPhpSiteProvider(): array
    {
        return [
            'globals: theme + api + hostname labels' => ['library/globals.inc.php', [
                'OpenEMR Auto Select Dark/Light Themed Version',
                'OpenEMR Light Theme Version Always',
                'OpenEMR Dark Theme Version Always',
                'this OpenEMR instance',
                'default (OpenEMR Website)',
            ]],
            'portal title' => ['portal/index.php', ['OpenEMR Patient Portal']],
            'main menu website link' => ['interface/main/tabs/main.php', ["xl('OpenEMR Website')"]],
            'auth block notifications' => ['src/Common/Auth/AuthUtils.php', [
                'For OpenEMR Admin',
            ]],
            'oauth scope descriptions' => [
                'src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php',
                ['the OpenEMR standard api', 'the OpenEMR FHIR api', 'the OpenEMR apis from inside'],
            ],
            'oauth scope list entity' => [
                'src/Common/Auth/OpenIDConnect/Entities/ServerScopeListEntity.php',
                ['the OpenEMR standard api', 'the OpenEMR FHIR api', 'the OpenEMR apis from inside'],
            ],
        ];
    }

    /**
     * @param list<string> $retiredLiterals
     */
    #[DataProvider('convertedPhpSiteProvider')]
    public function testConvertedPhpSitesNoLongerBakeTheProductNameIn(
        string $relativePath,
        array $retiredLiterals,
    ): void {
        $source = $this->read($this->root() . '/' . $relativePath);

        self::assertStringContainsString('xlp(', $source, $relativePath . ' should compose via xlp().');

        foreach ($retiredLiterals as $literal) {
            self::assertStringNotContainsString(
                $literal,
                $source,
                $relativePath . ' still bakes "' . $literal . '" into a translatable key.',
            );
        }
    }

    /**
     * Class A: the literal HAD a catalogue row, so the call-site change is only safe alongside a
     * contract that carries every locale's translation onto the neutral key. Converting one
     * without the other is the orphaning regression this programme already caught once and
     * reverted, so the pairing is asserted rather than trusted.
     */
    public function testEveryClassAConversionHasAContractThatPopulatesIt(): void
    {
        $set = TranslationCatalogueContractSet::fromProjectDirectory($this->root());

        $legacyTargets = [];
        foreach ($set->all() as $contract) {
            if ($contract->legacyKeys !== []) {
                $legacyTargets[$contract->targetKey] = $contract;
            }
        }

        // Every neutral key a converted class-A site composes, and the file it lives in.
        $conversions = [
            'interface/login_screen.php' => '%s requires Javascript to perform user authentication.',
            'interface/main/backup.php' => 'Dumping %s database',
            'interface/patient_file/letter.php' => 'Ensure %s has write privileges to directory',
            'interface/super/edit_list.php' => '%s Application Category',
            'library/classes/Installer.class.php' => '%s Users',
            'library/formdata.inc.php' => 'There was an %s SQL Escaping ERROR of the following string',
            'interface/smart/register-app.php' => '%s App Registration',
            'interface/usergroup/mfa_totp.php' => 'In order to register your device, please provide your %s login password',
        ];

        foreach ($conversions as $relativePath => $neutralKey) {
            $source = $this->read($this->root() . '/' . $relativePath);

            self::assertStringContainsString(
                $neutralKey,
                $source,
                $relativePath . ' should compose "' . $neutralKey . '".',
            );
            self::assertArrayHasKey(
                $neutralKey,
                $legacyTargets,
                $relativePath . ' composes "' . $neutralKey . '" but no carry-forward contract '
                . 'creates it, so every locale that had a translation would silently drop to English.',
            );
        }
    }

    /**
     * A carry-forward contract must state what happens to a translation that never named the
     * product. The default is to refuse; choosing to leave those rows behind is a real, bounded
     * loss of one locale at one call site, so it has to be written down in the contract rather
     * than inferred.
     */
    public function testEveryLegacyContractDeclaresItsMissingIdentityPolicy(): void
    {
        $set = TranslationCatalogueContractSet::fromProjectDirectory($this->root());

        foreach ($set->all() as $contract) {
            if ($contract->legacyKeys === [] || $contract->definitions !== []) {
                continue;
            }

            // A legacy contract shipping no explicit definitions relies entirely on the transform,
            // so it is exactly the case where a missing literal would otherwise abort an upgrade.
            self::assertSame(
                MissingIdentityPolicy::Skip,
                $contract->onMissingIdentity,
                $contract->id . ' carries translations forward with no explicit definitions, so it '
                . 'must state how a translation lacking the brand literal is handled.',
            );
        }
    }

    /**
     * Every pattern any contract can put into the catalogue must actually compose.
     *
     * `ProductContextTranslation` accepts exactly one `%s`/`%1$s` plus a literal `%%` and throws on
     * anything else — so a target key or definition carrying a stray `%` would not fail a build, it
     * would fatal whichever page renders it, in whichever locale reached it. That is a runtime
     * crash reachable only through translated content, which is close to the worst place to
     * discover one.
     */
    public function testEveryContractPatternComposes(): void
    {
        $set = TranslationCatalogueContractSet::fromProjectDirectory($this->root());
        $checked = 0;

        foreach ($set->all() as $contract) {
            $patterns = array_merge([$contract->targetKey], array_values($contract->definitions));

            foreach ($patterns as $pattern) {
                self::assertNotSame(
                    '',
                    ProductContextTranslation::compose($pattern, 'Thiqa'),
                    $contract->id . ' ships a pattern that does not compose: ' . $pattern,
                );
                $checked++;
            }
        }

        // A floor, so a broken loader silently yielding nothing cannot pass this vacuously.
        self::assertGreaterThanOrEqual(28, $checked);
    }

    /**
     * S2-P1-24's variant-selection half: the shell must ask for the session's product name rather
     * than the configured Latin one, and it must key on the **language**, not the direction.
     * `lang_languages` marks four locales RTL — Hebrew, Arabic, Persian, Urdu — and an Arabic
     * wordmark is right for exactly one of them, so a `lang_is_rtl` test would put Arabic script
     * in front of Hebrew and Persian users.
     */
    public function testTheAuthenticatedShellAsksForTheSessionProductName(): void
    {
        $shell = $this->read($this->root() . '/interface/main/tabs/main.php');

        self::assertStringContainsString('<title><?php echo text(xl_product_name()); ?></title>', $shell);
        self::assertStringContainsString('js_escape(xl_product_name())', $shell);

        $helper = $this->read($this->root() . '/library/translation.inc.php');
        self::assertStringContainsString('function xl_product_name(', $helper);

        // Scoped to this function's own body: `getLanguageDir()` lives in the same file and uses
        // `lang_is_rtl` entirely correctly for its own purpose.
        $start = strpos($helper, 'function xl_product_name(');
        self::assertIsInt($start);
        $end = strpos($helper, "\nfunction ", $start + 1);
        $body = substr($helper, $start, $end === false ? null : $end - $start);

        self::assertStringContainsString('saas_branding_product_name_ar', $body);
        self::assertStringContainsString("=== 'ar'", $body);
        self::assertStringNotContainsString(
            'lang_is_rtl',
            $body,
            'Direction is the wrong predicate: Hebrew, Persian and Urdu are RTL too, and an Arabic '
            . 'wordmark is correct for none of them.',
        );
    }

    /**
     * The other half of the classification, and the half that is easy to get wrong later.
     *
     * These strings name the **OpenEMR Foundation**, the upstream community, or ONC certification
     * reporting. They are not this product's identity, and neutralising them would make the
     * software assert something untrue — that a differently-named foundation should receive a
     * certification report, or that this fork holds an ONC certification it does not. They stay
     * exactly as upstream wrote them, and this test exists so a later sweep cannot quietly
     * "finish the job" by renaming them.
     */
    public function testUpstreamFoundationAndCertificationStringsArePreservedVerbatim(): void
    {
        $preserved = [
            'interface/reports/rwt_2026_report.php' => [
                'OpenEMR Foundation',
                'ONC certification',
            ],
            'templates/product_registration/product_registration_modal.html.twig' => [
                'OpenEMR Foundation',
            ],
            'interface/smart/register-app.php' => [
                'OpenEMR community form',
            ],
        ];

        foreach ($preserved as $relativePath => $literals) {
            $source = $this->read($this->root() . '/' . $relativePath);
            foreach ($literals as $literal) {
                self::assertStringContainsString(
                    $literal,
                    $source,
                    $relativePath . ' must keep "' . $literal . '" verbatim: it names the upstream '
                    . 'foundation or a certification programme, not this product.',
                );
            }
        }
    }

    /**
     * Every template, read once for the whole class.
     *
     * Walking and reading `templates/` costs real time on a network-backed filesystem, and three
     * tests here need the same corpus. Reading it per test made this class alone take 108 seconds
     * and pushed the canonical gate past Composer's 300-second process timeout — a green suite
     * that cannot finish is not a gate.
     *
     * @var array<string, string>|null path => contents
     */
    private static ?array $templateCorpus = null;

    /**
     * @return array<string, string>
     */
    private function templates(): array
    {
        if (self::$templateCorpus !== null) {
            return self::$templateCorpus;
        }

        $corpus = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root() . '/templates', \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $entry) {
            if (!$entry instanceof \SplFileInfo || !$entry->isFile()) {
                continue;
            }
            if (!str_ends_with($entry->getFilename(), '.twig')) {
                continue;
            }

            $contents = file_get_contents($entry->getPathname());
            self::assertIsString($contents);
            $corpus[$entry->getPathname()] = str_replace("\r\n", "\n", $contents);
        }

        ksort($corpus);

        return self::$templateCorpus = $corpus;
    }

    private function read(string $path): string
    {
        $contents = file_get_contents($path);
        self::assertIsString($contents);

        return str_replace("\r\n", "\n", $contents);
    }

    private function root(): string
    {
        return dirname(__DIR__, 4);
    }
}
