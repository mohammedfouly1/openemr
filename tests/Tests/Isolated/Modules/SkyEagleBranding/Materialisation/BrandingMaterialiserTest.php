<?php

/**
 * BrandingMaterialiser: idempotence, atomicity, ordering, tenant scope, re-validation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation;

use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Asset\LogoSlot;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\AssetPlacement;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\AtomicFileWriter;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\BrandingMaterialiser;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\GlobalsDelta;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\JsonFileTier1PaletteProvider;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\MaterialisationJob;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\TenantBrandingPaths;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\TokenCssWriter;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use OpenEMR\Modules\SkyEagleBranding\Theme\ThemeVariant;
use OpenEMR\Modules\SkyEagleBranding\Token\CssVariableRenderer;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenKey;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenSetParser;
use OpenEMR\Modules\SkyEagleBranding\Token\TokenValidator;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Token\ContrastCalculatorStub;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

require_once __DIR__ . '/materialisation_autoloader.php';

final class BrandingMaterialiserTest extends TestCase
{
    use CertifiedAssetTrait;
    use TemporaryTreeTrait;

    private const SITE = 'tenantalpha';

    private const OTHER_SITE = 'tenantbeta';

    /** Path to the shipped Tier 1 document, relative to this file. */
    private const TOKEN_DOCUMENT = '/../../../../../../brand/tokens/thiqa-tokens.json';

    private SiteId $site;

    private RecordingGlobalsWriter $globals;

    private TenantBrandingPaths $paths;

    private TokenCssWriter $cssWriter;

    private BrandingMaterialiser $materialiser;

    private string $cssRoot = '';

    private string $sitesRoot = '';

    protected function setUp(): void
    {
        $root = $this->makeTree();
        $this->cssRoot = $root . '/module/public/branding';
        $this->sitesRoot = $root . '/sites';

        $this->site = new SiteId(self::SITE);
        $this->globals = new RecordingGlobalsWriter($this->site);
        $this->paths = new TenantBrandingPaths($this->cssRoot, $this->sitesRoot);

        $files = new AtomicFileWriter();
        $this->cssWriter = new TokenCssWriter(new CssVariableRenderer(), $files, $this->paths);

        $this->materialiser = new BrandingMaterialiser(
            new TokenValidator(new ContrastCalculatorStub()),
            new JsonFileTier1PaletteProvider(new TokenSetParser(), __DIR__ . self::TOKEN_DOCUMENT),
            $this->cssWriter,
            $files,
            $this->paths,
            $this->globals,
            FrozenClock::at('2026-08-09T12:00:00+00:00'),
            new NullLogger(),
        );
    }

    protected function tearDown(): void
    {
        $this->removeTree();
    }

    // ------------------------------------------------------------------- job factories

    /**
     * A job that overrides one link colour per variant with an accessible value.
     */
    private function validJob(int $revision): MaterialisationJob
    {
        return new MaterialisationJob(
            $this->site,
            new BrandingRevision($revision),
            ['link.default' => '#1E4574'],
            ['link.default' => '#B7D9F5'],
            GlobalsDelta::empty()->with(BrandingGlobalKey::OpenemrName, 'Thiqa'),
            [],
        );
    }

    private function lightCss(): string
    {
        return $this->paths->tokenCssFile($this->site, ThemeVariant::Light);
    }

    private function darkCss(): string
    {
        return $this->paths->tokenCssFile($this->site, ThemeVariant::Dark);
    }

    // ------------------------------------------------------------------- the happy path

    public function testAppliedRunWritesBothStylesheetsAndTheGlobals(): void
    {
        $result = $this->materialiser->materialise($this->validJob(1));

        self::assertTrue($result->succeeded(), implode(' | ', $result->messages()));
        self::assertTrue($result->changed());
        self::assertSame(1, $result->revision()->value);
        self::assertFalse($result->isRetryable());

        self::assertFileExists($this->lightCss());
        self::assertFileExists($this->darkCss());
        self::assertStringContainsString('--link-default: #1E4574;', (string) file_get_contents($this->lightCss()));
        self::assertStringContainsString('--link-default: #B7D9F5;', (string) file_get_contents($this->darkCss()));

        self::assertSame('1', $this->globals->stored[BrandingGlobalKey::Revision->value]);
        self::assertSame('Thiqa', $this->globals->stored[BrandingGlobalKey::OpenemrName->value]);
        self::assertSame(
            '2026-08-09T12:00:00+00:00',
            $this->globals->stored[BrandingGlobalKey::MaterialisedAt->value],
        );
        self::assertSame(
            '{"link.default":"#1E4574"}',
            $this->globals->stored[BrandingGlobalKey::TokensLight->value],
        );
    }

    public function testNoTemporaryOrBackupFilesSurviveASuccessfulRun(): void
    {
        $this->materialiser->materialise($this->validJob(1));
        $this->materialiser->materialise($this->validJob(2));

        foreach ($this->filesUnder($this->cssRoot) as $file) {
            self::assertStringNotContainsString('.tmp-', $file);
            self::assertStringNotContainsString('.bak-', $file);
        }
    }

    // ---------------------------------------------------------------------- IDEMPOTENCE

    public function testSecondRunOfTheSameRevisionIsACompleteNoOp(): void
    {
        $first = $this->materialiser->materialise($this->validJob(1));
        self::assertTrue($first->changed());

        $before = (string) file_get_contents($this->lightCss());
        $storedBefore = $this->globals->stored;
        $this->globals->forget();

        $second = $this->materialiser->materialise($this->validJob(1));

        self::assertTrue($second->succeeded());
        self::assertFalse($second->changed(), 'The second run must not change anything.');
        self::assertSame(1, $second->revision()->value);

        self::assertSame([], $this->globals->operations, 'The second run performed a database write.');
        self::assertSame($storedBefore, $this->globals->stored);
        self::assertSame($before, (string) file_get_contents($this->lightCss()));
    }

    public function testAnEarlierRevisionIsRefusedAsAlreadyApplied(): void
    {
        $this->materialiser->materialise($this->validJob(4));
        $this->globals->forget();

        $result = $this->materialiser->materialise($this->validJob(3));

        self::assertTrue($result->succeeded());
        self::assertFalse($result->changed());
        self::assertSame(4, $result->revision()->value);
        self::assertSame([], $this->globals->operations);
    }

    public function testABumpWithIdenticalTokensLeavesTheStylesheetBytesUntouched(): void
    {
        $this->materialiser->materialise($this->validJob(1));
        $bytes = (string) file_get_contents($this->lightCss());
        $mtime = filemtime($this->lightCss());

        $result = $this->materialiser->materialise($this->validJob(2));

        self::assertTrue($result->succeeded(), implode(' | ', $result->messages()));
        self::assertSame(2, $result->revision()->value);
        self::assertSame($bytes, (string) file_get_contents($this->lightCss()));
        self::assertSame($mtime, filemtime($this->lightCss()));
    }

    // ------------------------------------------------------- ORDERING: the revision LAST

    public function testTheRevisionGlobalIsTheVeryLastValueWritten(): void
    {
        $this->materialiser->materialise($this->validJob(1));

        $written = $this->globals->writtenNames();

        self::assertNotSame([], $written);
        self::assertSame(BrandingGlobalKey::Revision->value, end($written));

        // ONE transaction, not two. The previous expectation encoded a begin/commit pair
        // around the delta followed by a second pair around the revision, which is
        // exactly the partial-application seam Q76 forbids (audit finding AR-P2-001):
        // a failure between the two commits left the delta live under a stale revision.
        // The revision is still written last, but now last *within* the single unit.
        self::assertSame(
            [
                'begin',
                'write:' . BrandingGlobalKey::OpenemrName->value,
                'write:' . BrandingGlobalKey::TokensLight->value,
                'write:' . BrandingGlobalKey::TokensDark->value,
                'write:' . BrandingGlobalKey::MaterialisedAt->value,
                'write:' . BrandingGlobalKey::Revision->value,
                'commit',
            ],
            $this->globals->operations,
        );

        self::assertSame(1, substr_count(implode(' ', $this->globals->operations), 'begin'));
        self::assertSame(1, substr_count(implode(' ', $this->globals->operations), 'commit'));
    }

    /**
     * The regression test for AR-P2-001.
     *
     * A failure on the revision write must leave every earlier global at its previous
     * value. Under the old two-transaction design the delta had already committed by this
     * point, so `openemr_name` would read 'Thiqa' while the revision still read 1 — a
     * half-applied state that the run then reported as failed.
     */
    public function testAFailedRevisionWriteLeavesEveryEarlierGlobalUnchanged(): void
    {
        $this->materialiser->materialise($this->validJob(1));
        $nameAtRevisionOne = $this->globals->stored[BrandingGlobalKey::OpenemrName->value];

        $this->globals->failOnWriteOf = BrandingGlobalKey::Revision->value;
        $this->globals->forget();

        $job = new MaterialisationJob(
            $this->site,
            new BrandingRevision(2),
            ['link.default' => '#0B1B4D'],
            [],
            GlobalsDelta::empty()->with(BrandingGlobalKey::OpenemrName, 'Half applied'),
            [],
        );

        $result = $this->materialiser->materialise($job);

        self::assertTrue($result->isFailure());
        self::assertSame(1, $result->revision()->value);

        // The whole point: the delta must NOT have survived the failed unit.
        self::assertSame(
            $nameAtRevisionOne,
            $this->globals->stored[BrandingGlobalKey::OpenemrName->value],
            'A failed revision write left the globals delta committed — the AR-P2-001 seam has reopened.',
        );
        self::assertNotSame('Half applied', $this->globals->stored[BrandingGlobalKey::OpenemrName->value]);
        self::assertSame('1', $this->globals->stored[BrandingGlobalKey::Revision->value]);
        self::assertContains('rollback', $this->globals->operations);
    }

    public function testBothStylesheetsAreAlreadyLiveAtTheInstantTheRevisionIsWritten(): void
    {
        // The revision write is the observation point. Every branding URL carries the
        // revision, so the moment it flips to n the browser starts asking for revision n
        // bytes — which must therefore already be at their final paths. Observing from
        // inside the transaction is the only way to assert that, rather than asserting
        // the end state and hoping the order was right.
        $stateAtRevisionWrite = null;
        $lightCss = $this->lightCss();
        $darkCss = $this->darkCss();

        $this->globals->observer = static function (string $name) use (&$stateAtRevisionWrite, $lightCss, $darkCss): void {
            if ($name !== BrandingGlobalKey::Revision->value) {
                return;
            }

            $stateAtRevisionWrite = [
                'light' => is_file($lightCss),
                'dark' => is_file($darkCss),
            ];
        };

        $result = $this->materialiser->materialise($this->validJob(1));

        self::assertTrue($result->succeeded(), implode(' | ', $result->messages()));
        self::assertSame(['light' => true, 'dark' => true], $stateAtRevisionWrite);
    }

    // ----------------------------------------------------------------------- ATOMICITY

    public function testAFailedStylesheetWriteLeavesNoPartialFileAndNoDatabaseWrite(): void
    {
        // A regular file where the tenant's stylesheet directory must go: staging cannot
        // create the directory, so step 3 fails before anything is applied.
        mkdir($this->cssRoot, 0o777, true);
        file_put_contents($this->paths->tokenCssDirectory($this->site), 'not a directory');

        $result = $this->materialiser->materialise($this->validJob(1));

        self::assertTrue($result->isFailure());
        self::assertTrue($result->isRetryable());
        self::assertSame(0, $result->revision()->value, 'Revision n-1 must remain active.');
        self::assertSame([], $this->globals->operations);
        self::assertArrayNotHasKey(BrandingGlobalKey::Revision->value, $this->globals->stored);

        self::assertSame(
            ['module/public/branding/' . self::SITE],
            $this->filesUnder($this->cssRoot),
            'No partial stylesheet may exist.',
        );
    }

    public function testAFailedGlobalsWriteRollsBackTheFilesAndTheTransaction(): void
    {
        $this->materialiser->materialise($this->validJob(1));
        $revisionOne = (string) file_get_contents($this->lightCss());

        $this->globals->failOnWriteOf = BrandingGlobalKey::OpenemrName->value;
        $this->globals->forget();

        $job = new MaterialisationJob(
            $this->site,
            new BrandingRevision(2),
            ['link.default' => '#0B1B4D'],
            [],
            GlobalsDelta::empty()->with(BrandingGlobalKey::OpenemrName, 'Rejected'),
            [],
        );

        $result = $this->materialiser->materialise($job);

        self::assertTrue($result->isFailure());
        self::assertTrue($result->isRetryable());
        self::assertSame(1, $result->revision()->value);

        // Revision 1's stylesheet is back, byte for byte.
        self::assertSame($revisionOne, (string) file_get_contents($this->lightCss()));
        self::assertSame('1', $this->globals->stored[BrandingGlobalKey::Revision->value]);
        // Revision 1 legitimately wrote openemr_name => 'Thiqa', so rollback must
        // RESTORE that value, not erase the key. Asserting absence would test the wrong
        // property and would pass even if rollback wiped previously-good state. What
        // proves atomicity here is that the rejected value never survives.
        self::assertSame('Thiqa', $this->globals->stored[BrandingGlobalKey::OpenemrName->value]);
        self::assertNotSame('Rejected', $this->globals->stored[BrandingGlobalKey::OpenemrName->value]);
        self::assertContains('rollback', $this->globals->operations);

        foreach ($this->filesUnder($this->cssRoot) as $file) {
            self::assertStringNotContainsString('.tmp-', $file);
            self::assertStringNotContainsString('.bak-', $file);
        }
    }

    public function testAFailedRevisionWriteLeavesTheTenantOnTheOldRevision(): void
    {
        $this->materialiser->materialise($this->validJob(1));
        $this->globals->failOnWriteOf = BrandingGlobalKey::Revision->value;

        $result = $this->materialiser->materialise($this->validJob(2));

        self::assertTrue($result->isFailure());
        self::assertTrue($result->isRetryable());
        self::assertSame('1', $this->globals->stored[BrandingGlobalKey::Revision->value]);
    }

    // -------------------------------------------------------------------- TENANT SCOPE

    public function testASiteIdIsMandatoryAndCannotBeBlank(): void
    {
        self::assertNull(SiteId::tryFrom(''));
        self::assertNull(SiteId::tryFrom('..'));
        self::assertNull(SiteId::tryFrom('alpha/../beta'));
    }

    public function testNothingIsWrittenOutsideTheTenantsOwnDirectories(): void
    {
        $this->materialiser->materialise($this->validJob(1));

        foreach ($this->filesUnder($this->treeRoot()) as $file) {
            self::assertTrue(
                str_starts_with($file, 'module/public/branding/' . self::SITE . '/')
                    || str_starts_with($file, 'sites/' . self::SITE . '/'),
                sprintf('"%s" is outside the tenant scope.', $file),
            );
        }

        self::assertDirectoryDoesNotExist($this->cssRoot . '/' . self::OTHER_SITE);
        self::assertDirectoryDoesNotExist($this->sitesRoot . '/' . self::OTHER_SITE);
    }

    public function testAssetsArePlacedInsideTheTenantsOwnSiteTree(): void
    {
        $bytes = 'GIF89a-not-a-real-image';
        $job = new MaterialisationJob(
            $this->site,
            new BrandingRevision(1),
            [],
            [],
            GlobalsDelta::empty(),
            [$this->certifiedPlacement(LogoSlot::CoreLoginPrimary)],
        );

        $result = $this->materialiser->materialise($job);

        self::assertTrue($result->succeeded(), implode(' | ', $result->messages()));
        self::assertFileExists($this->sitesRoot . '/' . self::SITE . '/images/logos/core/login/primary/logo.png');
        self::assertDirectoryDoesNotExist($this->sitesRoot . '/' . self::OTHER_SITE);
    }

    // ------------------------------------------------------------------ RE-VALIDATION

    /**
     * A payload the Control Plane has already "approved" is validated again here, and a
     * value that fails is refused rather than materialised (plan §4.4 step 2).
     *
     * @param array<array-key, mixed> $overlay
     */
    #[DataProvider('refusedOverlayProvider')]
    public function testAnInvalidOverlayIsRefusedEvenThoughItCameFromTheControlPlane(
        array $overlay,
        string $expectedFragment,
    ): void {
        $job = new MaterialisationJob(
            $this->site,
            new BrandingRevision(1),
            $overlay,
            [],
            GlobalsDelta::empty(),
            [],
        );

        $result = $this->materialiser->materialise($job);

        self::assertTrue($result->isFailure());
        self::assertFalse($result->isRetryable(), 'A refused payload is not worth retrying.');
        self::assertSame(0, $result->revision()->value);
        self::assertSame([], $this->globals->operations);
        self::assertFileDoesNotExist($this->lightCss());
        self::assertStringContainsString($expectedFragment, implode(' | ', $result->messages()));
    }

    /**
     * @return array<string, array{array<array-key, mixed>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedOverlayProvider(): array
    {
        return [
            'malformed colour' => [['link.default' => 'red'], 'link.default'],
            'css injection attempt' => [['link.default' => '#FFF; } body { display:none'], 'link.default'],
            'unknown key' => [['link.evil' => '#123456'], 'link.evil'],
            'clinical semantic key is not overridable' => [
                ['semantic.critical.bg' => '#00FF00'],
                'semantic.critical.bg',
            ],
            'structural surface is not overridable' => [['background' => '#000000'], 'background'],
            'brand identity is not overridable' => [['brand.navy' => '#FF00FF'], 'brand.navy'],
            'contrast gate failure' => [['link.default' => '#FAFAF7'], 'below the required'],
        ];
    }

    public function testAJobMayNotSupplyTheMaterialiserOwnedGlobals(): void
    {
        $job = new MaterialisationJob(
            $this->site,
            new BrandingRevision(1),
            [],
            [],
            GlobalsDelta::empty()->with(BrandingGlobalKey::Revision, '99'),
            [],
        );

        $result = $this->materialiser->materialise($job);

        self::assertTrue($result->isFailure());
        self::assertFalse($result->isRetryable());
        self::assertStringContainsString('computed by the materialiser', implode(' | ', $result->messages()));
        self::assertSame([], $this->globals->operations);
    }

    public function testAnAssetWhoseDeclaredChecksumIsWrongIsRefused(): void
    {
        $job = new MaterialisationJob(
            $this->site,
            new BrandingRevision(1),
            [],
            [],
            GlobalsDelta::empty(),
            [AssetPlacement::fromValidated(
                $this->certifiedAsset(LogoSlot::CoreMenuPrimary),
                str_repeat('a', 64),
            )],
        );

        $result = $this->materialiser->materialise($job);

        self::assertTrue($result->isFailure());
        self::assertFalse($result->isRetryable());
        self::assertStringContainsString('declared checksum does not match', implode(' | ', $result->messages()));
        self::assertSame([], $this->filesUnder($this->sitesRoot));
    }

    public function testAValueLongerThanTheGlobalsColumnIsRefusedRatherThanTruncated(): void
    {
        $job = new MaterialisationJob(
            $this->site,
            new BrandingRevision(1),
            [],
            [],
            GlobalsDelta::empty()->with(
                BrandingGlobalKey::LoginTaglineText,
                str_repeat('x', BrandingMaterialiser::MAX_GLOBAL_VALUE_LENGTH + 1),
            ),
            [],
        );

        $result = $this->materialiser->materialise($job);

        self::assertTrue($result->isFailure());
        self::assertFalse($result->isRetryable());
        self::assertStringContainsString('above the 255-byte column limit', implode(' | ', $result->messages()));
        self::assertSame([], $this->globals->operations);
    }

    public function testAnOverlayTouchingEveryOverridableKeyOverflowsTheGlobalsColumn(): void
    {
        // A finding, pinned as a test: `globals.gl_value` is varchar(255), but a
        // WCAG-clean overlay covering all eleven tenant-overridable keys encodes to
        // roughly 460 bytes of JSON. The values below all pass their contrast gates, so
        // the only thing that can stop this payload is the column limit — and it must
        // stop it, because a silently truncated overlay is JSON that
        // TokenOverlay::fromJson() discards whole, degrading the tenant to Tier 1 with no
        // error recorded anywhere.
        $overlay = [
            'interactive.primary.default' => '#1E4574',
            'interactive.primary.hover' => '#1E4574',
            'interactive.primary.active' => '#1E4574',
            'interactive.primary.disabled' => '#EAC1BA',
            'interactive.primary.textOn' => '#FFFFFF',
            'interactive.secondary.default' => '#0B1B4D',
            'interactive.secondary.hover' => '#0B1B4D',
            'interactive.secondary.textOn' => '#FFFFFF',
            'interactive.focusRing' => '#1E4574',
            'link.default' => '#1E4574',
            'link.hover' => '#1E4574',
        ];

        self::assertCount(
            count($overlay),
            TokenKey::tenantOverridableKeys(),
            'The overlay must still cover every overridable key.',
        );

        $job = new MaterialisationJob(
            $this->site,
            new BrandingRevision(1),
            $overlay,
            [],
            GlobalsDelta::empty(),
            [],
        );

        $result = $this->materialiser->materialise($job);

        self::assertTrue($result->isFailure());
        self::assertFalse($result->isRetryable());
        self::assertStringContainsString(
            BrandingGlobalKey::TokensLight->value,
            implode(' | ', $result->messages()),
        );
        self::assertStringContainsString('column limit', implode(' | ', $result->messages()));
        self::assertSame([], $this->globals->operations);
    }
}
