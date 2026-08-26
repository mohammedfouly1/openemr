<?php

/**
 * Regression contract: branding health must measure the served state, not the unserved one.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BrandingCi;

use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Observability\BrandingHealthCheck;
use OpenEMR\Modules\SkyEagleBranding\Observability\BrandingHealthStatus;
use OpenEMR\Modules\SkyEagleBranding\Observability\BrandingInconsistency;
use OpenEMR\Modules\SkyEagleBranding\Observability\BrandingObservationPlane;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation\FrozenClock;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation\RecordingGlobalsWriter;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Observability\InMemoryStylesheetProbe;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Observability\RecordingLogger;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Modules/SkyEagleBranding/Materialisation/materialisation_autoloader.php';

/**
 * Finding S2-P1-18: `skyeagle-branding:verify` reported the live tenant as `inconsistent`,
 * exit 1, because the check cross-referenced a served fact (the revision) against an
 * unserved one (whether `public/branding/<site>/tokens-*.css` existed on disk). No page
 * links those files, so the tenant was rendering exactly what it should. The documentation
 * had by then drifted twice on that same fact, in opposite directions, and nothing gated on
 * the exit code — so the false alarm could neither be trusted nor noticed.
 *
 * This runs inside `composer branding-ci`. It exists so the two ways that repair can rot
 * both fail deterministically: reclassifying a static-artefact finding back into a failure,
 * and answering "is an overlay served?" with anything other than the parser the request path
 * uses.
 */
#[Group('isolated')]
final class BrandingHealthTruthfulnessContractTest extends TestCase
{
    private const SITE = 'tenantalpha';

    private const NOW = '2026-08-09T12:00:00+00:00';

    /**
     * Every finding, and the plane that decides whether it can fail a health probe.
     *
     * Enumerated rather than derived: the point of the contract is that moving a case
     * between planes is a deliberate, reviewed change, so the expectation has to be stated
     * somewhere a reviewer sees it.
     */
    private const EXPECTED_PLANES = [
        'missing_materialisation_stamp' => 'served',
        'overlay_without_revision' => 'served',
        'revision_without_stylesheet' => 'static_artefact',
        'stamp_in_the_future' => 'served',
        'stamp_without_revision' => 'served',
        'state_unreadable' => 'served',
        'stylesheet_without_revision' => 'static_artefact',
        'unreadable_materialisation_stamp' => 'served',
        'unrenderable_token_overlay' => 'served',
    ];

    public function testOnlyTheServedPlaneCanFailAHealthProbe(): void
    {
        self::assertTrue(BrandingObservationPlane::Served->failsHealth());
        self::assertFalse(BrandingObservationPlane::StaticArtefact->failsHealth());
    }

    public function testEveryFindingIsClassifiedOnItsExpectedPlane(): void
    {
        self::assertSame(self::EXPECTED_PLANES, self::actualPlanesByIdentifier());
    }

    /**
     * Built as its own method, with a general `array<string, string>` return type, rather than
     * inline in the test: PHPStan infers a dynamically-built array's type as a per-key union of
     * every value ever assigned during the loop, which it then compares against
     * self::EXPECTED_PLANES's literal per-key shape and pronounces the two "impossible" to be
     * equal — a false positive of the shape inference, not a real defect. A declared general
     * element type sidesteps that over-precise (and here wrong) shape unification while keeping
     * the actual runtime comparison exactly as strict.
     *
     * @return array<string, string>
     */
    private static function actualPlanesByIdentifier(): array
    {
        $actual = [];
        foreach (BrandingInconsistency::cases() as $case) {
            $actual[$case->value] = $case->plane()->value;
        }

        // Sorted by identifier, so reordering the enum's declaration is not a contract
        // change while adding or reclassifying a case still is.
        ksort($actual);

        return $actual;
    }

    /**
     * The exact live shape from the finding. If this ever fails again, `verify` has gone
     * back to failing a tenant whose rendering is correct.
     */
    public function testGeneratedFilesWithoutARevisionDoNotFailTheTenant(): void
    {
        $report = $this->check(new InMemoryStylesheetProbe(light: true, dark: true), [])
            ->check(new SiteId(self::SITE));

        self::assertSame(BrandingHealthStatus::NeverMaterialised, $report->status);
        self::assertFalse($report->isFailure());
        self::assertSame([], $report->inconsistencies);
        self::assertSame([BrandingInconsistency::StylesheetWithoutRevision], $report->advisories);
    }

    /** The repair must not have been a blanket weakening: served faults still fail. */
    public function testAServedOverlayWithoutARevisionStillFailsTheTenant(): void
    {
        $report = $this->check(
            new InMemoryStylesheetProbe(light: false, dark: false),
            [BrandingGlobalKey::TokensLight->value => '{"link.default":"#0B4E91"}'],
        )->check(new SiteId(self::SITE));

        self::assertTrue($report->isFailure());
        self::assertSame([BrandingInconsistency::OverlayWithoutRevision], $report->inconsistencies);
    }

    /**
     * "Does this tenant serve an overlay?" has to be answered by the same code the endpoint
     * runs. A second emptiness test would be free to disagree with the rendered page, which
     * is the class of defect this whole finding is about.
     */
    public function testTheOverlayIsReadThroughTheRuntimeParser(): void
    {
        $source = $this->read(
            'interface/modules/custom_modules/oe-module-skyeagle-branding/src/Observability/BrandingHealthCheck.php',
        );

        self::assertStringContainsString('TokenOverlay::fromJson(', $source);
        self::assertStringNotContainsString('json_decode(', $source);
    }

    /**
     * The runbook is where an operator looks when `verify` prints something unexpected. It
     * has already drifted twice on this fact, so the vocabulary is pinned here.
     */
    public function testTheRunbookDocumentsTheServedAndAdvisoryDistinction(): void
    {
        $runbook = $this->read('docs/branding/runbook.md');

        self::assertStringContainsString('Serves tenant overlay', $runbook);
        self::assertStringContainsString('not served', $runbook);
        self::assertStringContainsString('advisor', strtolower($runbook));
    }

    /**
     * @param array<string, string> $stored
     */
    private function check(InMemoryStylesheetProbe $probe, array $stored): BrandingHealthCheck
    {
        $globals = new RecordingGlobalsWriter(new SiteId(self::SITE));
        $globals->stored = $stored;

        return new BrandingHealthCheck(
            $globals,
            $probe,
            FrozenClock::at(self::NOW),
            new RecordingLogger(),
        );
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents(dirname(__DIR__, 4) . '/' . $relativePath);
        self::assertIsString($contents);

        return str_replace("\r\n", "\n", $contents);
    }
}
