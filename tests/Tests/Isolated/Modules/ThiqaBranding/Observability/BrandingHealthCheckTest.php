<?php

/**
 * BrandingHealthCheck: consistency detection, staleness, read-only behaviour.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Observability;

use OpenEMR\Modules\ThiqaBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\ThiqaBranding\Materialisation\BrandingGlobalsWriterInterface;
use OpenEMR\Modules\ThiqaBranding\Observability\BrandingHealthCheck;
use OpenEMR\Modules\ThiqaBranding\Observability\BrandingHealthStatus;
use OpenEMR\Modules\ThiqaBranding\Observability\BrandingInconsistency;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation\FrozenClock;
use OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Materialisation\RecordingGlobalsWriter;
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

    // ------------------------------------------------------------- the inconsistent cases

    /**
     * The headline case: the revision global says a materialisation completed, but the
     * stylesheet that completion was supposed to produce is not there.
     */
    public function testRevisionSetButStylesheetAbsentIsInconsistent(): void
    {
        $this->store(revision: 7, stamp: self::FRESH_STAMP);

        $report = $this->check(new InMemoryStylesheetProbe(light: false, dark: true))->check($this->site);

        self::assertSame(BrandingHealthStatus::Inconsistent, $report->status);
        self::assertTrue($report->isFailure());
        self::assertSame([BrandingInconsistency::RevisionWithoutStylesheet], $report->inconsistencies);
        self::assertFalse($report->bothStylesheetsPresent());
    }

    public function testBothStylesheetsAbsentWithARevisionIsReportedOnce(): void
    {
        $this->store(revision: 7, stamp: self::FRESH_STAMP);

        $report = $this->check(new InMemoryStylesheetProbe(light: false, dark: false))->check($this->site);

        self::assertSame([BrandingInconsistency::RevisionWithoutStylesheet], $report->inconsistencies);
    }

    public function testStylesheetPresentWithoutARevisionIsInconsistent(): void
    {
        $report = $this->check(new InMemoryStylesheetProbe(light: true, dark: false))->check($this->site);

        self::assertSame(BrandingHealthStatus::Inconsistent, $report->status);
        self::assertSame([BrandingInconsistency::StylesheetWithoutRevision], $report->inconsistencies);
    }

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
        self::assertSame(0, $report->revision->value);
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

        $context = $this->check(new InMemoryStylesheetProbe())->check($this->site)->toContext();

        self::assertSame([
            'site' => self::SITE,
            'status' => 'healthy',
            'revision' => 4,
            'materialised' => true,
            'materialised_at' => self::FRESH_STAMP,
            'age_seconds' => 3600,
            'stylesheet_light' => true,
            'stylesheet_dark' => true,
            'inconsistencies' => [],
        ], $context);
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
}
