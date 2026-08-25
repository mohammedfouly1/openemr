<?php

/**
 * BrandingHealthCheck: served-state truth, static-artefact advisories, read-only behaviour.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Observability;

use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\BrandingGlobalsWriterInterface;
use OpenEMR\Modules\SkyEagleBranding\Observability\BrandingHealthCheck;
use OpenEMR\Modules\SkyEagleBranding\Observability\BrandingHealthStatus;
use OpenEMR\Modules\SkyEagleBranding\Observability\BrandingInconsistency;
use OpenEMR\Modules\SkyEagleBranding\Observability\BrandingObservationPlane;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation\FrozenClock;
use OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation\RecordingGlobalsWriter;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';

final class BrandingHealthCheckTest extends TestCase
{
    private const SITE = 'tenantalpha';

    private const NOW = '2026-08-09T12:00:00+00:00';

    /** One hour before NOW: comfortably inside any staleness threshold. */
    private const FRESH_STAMP = '2026-08-09T11:00:00+00:00';

    /** Sixty-nine days before NOW: past the thirty-day default. */
    private const OLD_STAMP = '2026-06-01T12:00:00+00:00';

    /** A shape TokenOverlay accepts: dotted keys, six-digit hex values. */
    private const VALID_OVERLAY = '{"interactive.primary.default":"#0B376E","link.default":"#0B4E91"}';

    private SiteId $site;

    private RecordingGlobalsWriter $globals;

    private RecordingLogger $logger;

    protected function setUp(): void
    {
        $this->site = new SiteId(self::SITE);
        $this->globals = new RecordingGlobalsWriter($this->site);
        $this->logger = new RecordingLogger();
    }

    // ------------------------------------------------------------------- the healthy cases

    public function testHealthyWhenRevisionStampAndStylesheetsAgree(): void
    {
        $this->store(revision: 4, stamp: self::FRESH_STAMP);

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertSame(BrandingHealthStatus::Healthy, $report->status);
        self::assertFalse($report->isFailure());
        self::assertFalse($report->hasInconsistencies());
        self::assertFalse($report->hasAdvisories());
        self::assertSame(4, $report->revision->value);
        self::assertSame(3600, $report->ageInSeconds);
        self::assertTrue($report->bothStylesheetsPresent());
    }

    public function testNeverMaterialisedIsNotAFailure(): void
    {
        $report = $this->check(new InMemoryStylesheetProbe(light: false, dark: false))->check($this->site);

        self::assertSame(BrandingHealthStatus::NeverMaterialised, $report->status);
        self::assertFalse($report->isFailure());
        self::assertSame(0, $report->revision->value);
        self::assertNull($report->materialisedAt);
        self::assertNull($report->ageInSeconds);
        self::assertFalse($report->servesTenantOverlay);
    }

    public function testStaleIsReportedButIsNotAFailure(): void
    {
        $this->store(revision: 2, stamp: self::OLD_STAMP);

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertSame(BrandingHealthStatus::Stale, $report->status);
        self::assertTrue($report->isStale());
        self::assertFalse($report->isFailure());
        self::assertFalse($report->hasInconsistencies());
    }

    // --------------------------------------------------------------- what is actually served

    /**
     * The overlay globals, not the files on disk, decide whether a `<link>` is emitted at
     * all. A tenant with none of them renders the shared Tier 1 palette, and the report has
     * to say so rather than leave a reader to infer it from a stylesheet row.
     */
    public function testAnAbsentOverlayIsReportedAsRenderingTheProductPalette(): void
    {
        $this->store(revision: 3, stamp: self::FRESH_STAMP);

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertFalse($report->servesTenantOverlay);
        self::assertSame(0, $report->servedOverlayTokenCount());
        self::assertSame(BrandingHealthStatus::Healthy, $report->status);
    }

    public function testAServedOverlayIsCountedThroughTheRuntimeParser(): void
    {
        $this->store(revision: 3, stamp: self::FRESH_STAMP);
        $this->storeOverlay(light: self::VALID_OVERLAY, dark: '{"link.default":"#FFD166"}');

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertTrue($report->servesTenantOverlay);
        self::assertSame(2, $report->lightOverlayTokenCount);
        self::assertSame(1, $report->darkOverlayTokenCount);
        self::assertSame(3, $report->servedOverlayTokenCount());
        self::assertFalse($report->isFailure());
    }

    /**
     * A blank global is unconfigured, not a broken overlay. Whitespace has to read the same
     * way, because BrandingConfigFactory trims before deciding and the check must agree with
     * the page rather than invent a second emptiness rule.
     */
    public function testAWhitespaceOnlyOverlayIsTreatedAsUnconfigured(): void
    {
        $this->store(revision: 3, stamp: self::FRESH_STAMP);
        $this->storeOverlay(light: "   \n  ", dark: '');

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertFalse($report->servesTenantOverlay);
        self::assertSame([], $report->inconsistencies);
        self::assertSame(BrandingHealthStatus::Healthy, $report->status);
    }

    /**
     * TokenOverlay::fromJson() is total by design — a malformed overlay degrades to Tier 1
     * instead of breaking the login page. The degradation is correct; being unable to see it
     * is not, so the check names it.
     */
    public function testAStoredButUnrenderableOverlayIsAServedFailure(): void
    {
        $this->store(revision: 3, stamp: self::FRESH_STAMP);
        $this->storeOverlay(light: '{"interactive.primary.default":"not-a-colour"}', dark: '');

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertSame([BrandingInconsistency::UnrenderableTokenOverlay], $report->inconsistencies);
        self::assertSame(BrandingHealthStatus::Inconsistent, $report->status);
        self::assertTrue($report->isFailure());
        self::assertFalse($report->servesTenantOverlay);
    }

    public function testUnparseableJsonInAnOverlayIsAServedFailure(): void
    {
        $this->store(revision: 3, stamp: self::FRESH_STAMP);
        $this->storeOverlay(light: '', dark: '{ this is not json');

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertSame([BrandingInconsistency::UnrenderableTokenOverlay], $report->inconsistencies);
    }

    /**
     * The revision is the overlay's cache key. Serving tenant colours while the revision
     * still reads 0 means the overlay arrived outside the materialisation transaction.
     */
    public function testAnOverlayWithoutARevisionIsAServedFailure(): void
    {
        $this->storeOverlay(light: self::VALID_OVERLAY, dark: '');

        $report = $this->check(new InMemoryStylesheetProbe(light: false, dark: false))->check($this->site);

        self::assertSame([BrandingInconsistency::OverlayWithoutRevision], $report->inconsistencies);
        self::assertTrue($report->isFailure());
        self::assertTrue($report->servesTenantOverlay);
    }

    // ------------------------------------------------- static artefacts are never a failure

    /**
     * The live tenant at the time finding S2-P1-18 was written: revision 0, no overlay, both
     * generated files still on disk from an earlier run. Nothing serves those files, so the
     * served state is exactly "never materialised, rendering product defaults" — and calling
     * that `inconsistent`, exit 1, is what the finding recorded as untruthful.
     */
    public function testStylesheetsPresentWithoutARevisionAreAnAdvisoryNotAFailure(): void
    {
        $report = $this->check(new InMemoryStylesheetProbe(light: true, dark: true))->check($this->site);

        self::assertSame(BrandingHealthStatus::NeverMaterialised, $report->status);
        self::assertFalse($report->isFailure());
        self::assertSame([], $report->inconsistencies);
        self::assertSame([BrandingInconsistency::StylesheetWithoutRevision], $report->advisories);
        self::assertTrue($report->hasAdvisories());
    }

    public function testOneStylesheetPresentWithoutARevisionIsAlsoOnlyAnAdvisory(): void
    {
        $report = $this->check(new InMemoryStylesheetProbe(light: true, dark: false))->check($this->site);

        self::assertFalse($report->isFailure());
        self::assertSame([BrandingInconsistency::StylesheetWithoutRevision], $report->advisories);
    }

    public function testARevisionWithAMissingStylesheetIsAnAdvisoryNotAFailure(): void
    {
        $this->store(revision: 7, stamp: self::FRESH_STAMP);

        $report = $this->check(new InMemoryStylesheetProbe(light: false, dark: true))->check($this->site);

        self::assertSame(BrandingHealthStatus::Healthy, $report->status);
        self::assertFalse($report->isFailure());
        self::assertSame([], $report->inconsistencies);
        self::assertSame([BrandingInconsistency::RevisionWithoutStylesheet], $report->advisories);
        self::assertFalse($report->bothStylesheetsPresent());
    }

    public function testBothStylesheetsAbsentWithARevisionIsReportedOnce(): void
    {
        $this->store(revision: 7, stamp: self::FRESH_STAMP);

        $report = $this->check(new InMemoryStylesheetProbe(light: false, dark: false))->check($this->site);

        self::assertSame([BrandingInconsistency::RevisionWithoutStylesheet], $report->advisories);
    }

    /** An advisory alongside a real fault must not dilute or absorb the fault. */
    public function testAnAdvisoryAndAServedFailureAreReportedSeparately(): void
    {
        $this->store(revision: 7, stamp: null);

        $report = $this->check(new InMemoryStylesheetProbe(light: false, dark: false))->check($this->site);

        self::assertSame([BrandingInconsistency::MissingMaterialisationStamp], $report->inconsistencies);
        self::assertSame([BrandingInconsistency::RevisionWithoutStylesheet], $report->advisories);
        self::assertSame(BrandingHealthStatus::Inconsistent, $report->status);
        self::assertTrue($report->isFailure());
    }

    /**
     * The severity rule lives on the plane enum, so every finding's classification is
     * derivable rather than restated in each caller.
     */
    public function testEveryReportedFindingSitsOnItsDeclaredPlane(): void
    {
        $this->store(revision: 7, stamp: null);

        $report = $this->check(new InMemoryStylesheetProbe(light: false, dark: false))->check($this->site);

        foreach ($report->inconsistencies as $finding) {
            self::assertSame(BrandingObservationPlane::Served, $finding->plane());
            self::assertTrue($finding->plane()->failsHealth());
        }

        foreach ($report->advisories as $finding) {
            self::assertSame(BrandingObservationPlane::StaticArtefact, $finding->plane());
            self::assertFalse($finding->plane()->failsHealth());
        }
    }

    // ------------------------------------------------------------- the served stamp/revision

    public function testRevisionWithoutAStampIsInconsistent(): void
    {
        $this->store(revision: 3, stamp: null);

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertSame([BrandingInconsistency::MissingMaterialisationStamp], $report->inconsistencies);
        self::assertTrue($report->isFailure());
    }

    public function testStampWithoutARevisionIsInconsistent(): void
    {
        $this->store(revision: null, stamp: self::FRESH_STAMP);

        $report = $this->check(new InMemoryStylesheetProbe(light: false, dark: false))->check($this->site);

        self::assertSame([BrandingInconsistency::StampWithoutRevision], $report->inconsistencies);
    }

    public function testAnUnparseableStampIsInconsistent(): void
    {
        $this->store(revision: 3, stamp: 'last Tuesday');

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertSame([BrandingInconsistency::UnreadableMaterialisationStamp], $report->inconsistencies);
        self::assertNull($report->materialisedAt);
        self::assertNull($report->ageInSeconds);
    }

    public function testAStampAheadOfTheClockIsInconsistent(): void
    {
        $this->store(revision: 3, stamp: '2026-09-01T12:00:00+00:00');

        $report = $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertSame([BrandingInconsistency::StampInTheFuture], $report->inconsistencies);
        self::assertNotNull($report->ageInSeconds);
        self::assertLessThan(0, $report->ageInSeconds);
    }

    // --------------------------------------------------------------------- unreadable state

    public function testUnreadableStateIsReportedRatherThanThrown(): void
    {
        $check = new BrandingHealthCheck(
            new UnreadableGlobalsWriter(),
            new InMemoryStylesheetProbe(),
            FrozenClock::at(self::NOW),
            $this->logger,
        );

        $report = $check->check($this->site);

        self::assertSame(BrandingHealthStatus::Unreadable, $report->status);
        self::assertTrue($report->isFailure());
        self::assertSame([BrandingInconsistency::StateUnreadable], $report->inconsistencies);
        self::assertSame([], $report->advisories);
        self::assertSame(0, $report->revision->value);
        self::assertFalse($report->servesTenantOverlay);
    }

    /**
     * The adapter's exception text can carry SQL and connection detail. It belongs in the
     * log's context under `exception`, never in the report an operator sees.
     */
    public function testTheUnderlyingExceptionIsLoggedInContextAndNotInTheReport(): void
    {
        $check = new BrandingHealthCheck(
            new UnreadableGlobalsWriter(),
            new InMemoryStylesheetProbe(),
            FrozenClock::at(self::NOW),
            $this->logger,
        );

        $report = $check->check($this->site);

        self::assertCount(1, $this->logger->records);
        $record = $this->logger->records[0];

        self::assertSame('error', $record['level']);
        self::assertStringNotContainsString(UnreadableGlobalsWriter::FAILURE_MESSAGE, $record['message']);
        self::assertStringNotContainsString(self::SITE, $record['message']);
        self::assertSame('branding.health.unreadable', $record['context']['event'] ?? null);
        self::assertSame(self::SITE, $record['context']['site'] ?? null);
        self::assertArrayHasKey('exception', $record['context']);

        foreach ($report->messages() as $message) {
            self::assertStringNotContainsString(UnreadableGlobalsWriter::FAILURE_MESSAGE, $message);
        }
    }

    // ---------------------------------------------------------------------------- read-only

    public function testTheCheckNeverMutatesTenantState(): void
    {
        $this->store(revision: 5, stamp: self::FRESH_STAMP);
        $this->storeOverlay(light: self::VALID_OVERLAY, dark: '');
        $before = $this->globals->stored;

        $this->check(new InMemoryStylesheetProbe())->check($this->site);

        self::assertSame([], $this->globals->operations);
        self::assertSame($before, $this->globals->stored);
    }

    public function testTheCheckStaysInsideTheTenantItWasGiven(): void
    {
        $this->store(revision: 5, stamp: self::FRESH_STAMP);
        $probe = new InMemoryStylesheetProbe();

        $this->check($probe)->check($this->site);

        self::assertSame([self::SITE, self::SITE], $probe->sitesProbed);
    }

    // ------------------------------------------------------------------------- the context

    public function testTheReportFlattensToAMachineQueryableContext(): void
    {
        $this->store(revision: 4, stamp: self::FRESH_STAMP);
        $this->storeOverlay(light: self::VALID_OVERLAY, dark: '');

        $context = $this->check(new InMemoryStylesheetProbe())->check($this->site)->toContext();

        self::assertSame([
            'site' => self::SITE,
            'status' => 'healthy',
            'revision' => 4,
            'materialised' => true,
            'materialised_at' => self::FRESH_STAMP,
            'age_seconds' => 3600,
            'serves_tenant_overlay' => true,
            'overlay_tokens_light' => 2,
            'overlay_tokens_dark' => 0,
            'stylesheet_light' => true,
            'stylesheet_dark' => true,
            'inconsistencies' => [],
            'advisories' => [],
        ], $context);
    }

    /** The advisory keeps its historical wire identifier; only its severity moved. */
    public function testTheContextReportsAdvisoriesUnderTheirStableIdentifiers(): void
    {
        $context = $this->check(new InMemoryStylesheetProbe())->check($this->site)->toContext();

        self::assertSame('never_materialised', $context['status']);
        self::assertSame([], $context['inconsistencies']);
        self::assertSame(['stylesheet_without_revision'], $context['advisories']);
    }

    public function testTheConfiguredStalenessThresholdIsReported(): void
    {
        $check = new BrandingHealthCheck(
            $this->globals,
            new InMemoryStylesheetProbe(),
            FrozenClock::at(self::NOW),
            $this->logger,
            120,
        );

        self::assertSame(120, $check->stalenessThresholdSeconds());
    }

    // ---------------------------------------------------------------------------- fixtures

    private function check(InMemoryStylesheetProbe $probe): BrandingHealthCheck
    {
        return $this->checkFor($this->globals, $probe);
    }

    private function checkFor(
        BrandingGlobalsWriterInterface $globals,
        InMemoryStylesheetProbe $probe,
    ): BrandingHealthCheck {
        return new BrandingHealthCheck(
            $globals,
            $probe,
            FrozenClock::at(self::NOW),
            $this->logger,
        );
    }

    /**
     * Seed the double's storage directly rather than through write(), so the fixture does
     * not depend on the transaction machinery the check is asserted never to touch.
     */
    private function store(?int $revision = null, ?string $stamp = null): void
    {
        if ($revision !== null) {
            $this->globals->stored[BrandingGlobalKey::Revision->value] = (string) $revision;
        }

        if ($stamp !== null) {
            $this->globals->stored[BrandingGlobalKey::MaterialisedAt->value] = $stamp;
        }
    }

    private function storeOverlay(string $light, string $dark): void
    {
        $this->globals->stored[BrandingGlobalKey::TokensLight->value] = $light;
        $this->globals->stored[BrandingGlobalKey::TokensDark->value] = $dark;
    }
}
