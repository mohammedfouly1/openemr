<?php

/**
 * MaterialisationLogger: constant messages, structured context, correct severities.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ThiqaBranding\Observability;

use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Materialisation\MaterialisationResult;
use OpenEMR\Modules\ThiqaBranding\Observability\MaterialisationLogger;
use OpenEMR\Modules\ThiqaBranding\Observability\MaterialisationOutcome;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once __DIR__ . '/../Materialisation/materialisation_autoloader.php';

final class MaterialisationLoggerTest extends TestCase
{
    private const SITE = 'tenantalpha';

    private const TARGET = 9;

    private SiteId $site;

    private BrandingRevision $target;

    private RecordingLogger $recorder;

    private MaterialisationLogger $logger;

    protected function setUp(): void
    {
        $this->site = new SiteId(self::SITE);
        $this->target = new BrandingRevision(self::TARGET);
        $this->recorder = new RecordingLogger();
        $this->logger = new MaterialisationLogger($this->recorder);
    }

    // ------------------------------------------------------------------------- the events

    public function testStartCarriesTheTenantAndTargetInContext(): void
    {
        $this->logger->started($this->site, $this->target);

        self::assertCount(1, $this->recorder->records);
        $record = $this->recorder->records[0];

        self::assertSame('info', $record['level']);
        self::assertSame('Branding materialisation started', $record['message']);
        self::assertSame(MaterialisationLogger::EVENT_STARTED, $record['context']['event'] ?? null);
        self::assertSame(self::SITE, $record['context']['site'] ?? null);
        self::assertSame(self::TARGET, $record['context']['target_revision'] ?? null);
    }

    /**
     * A start event has no outcome yet, and inventing one would make the field mean two
     * different things in the same stream.
     */
    public function testStartCarriesNoOutcomeKey(): void
    {
        $this->logger->started($this->site, $this->target);

        self::assertArrayNotHasKey('outcome', $this->recorder->records[0]['context']);
    }

    /**
     * @param non-empty-list<string> $messages
     */
    #[DataProvider('outcomeProvider')]
    public function testEachOutcomeIsLoggedAtItsSeverityWithItsFields(
        string $factory,
        int $liveRevision,
        array $messages,
        MaterialisationOutcome $expected,
        string $expectedLevel,
        bool $expectedRetryable,
    ): void {
        $result = $this->makeResult($factory, $liveRevision, $messages);

        $outcome = $this->logger->outcome($this->site, $this->target, $result);

        self::assertSame($expected, $outcome);
        self::assertCount(1, $this->recorder->records);

        $record = $this->recorder->records[0];
        self::assertSame($expectedLevel, $record['level']);
        self::assertSame($expected->logMessage(), $record['message']);
        self::assertSame(
            MaterialisationLogger::EVENT_PREFIX . $expected->value,
            $record['context']['event'] ?? null,
        );
        self::assertSame(self::SITE, $record['context']['site'] ?? null);
        self::assertSame(self::TARGET, $record['context']['target_revision'] ?? null);
        self::assertSame($expected->value, $record['context']['outcome'] ?? null);
        self::assertSame($liveRevision, $record['context']['live_revision'] ?? null);
        self::assertSame($expectedRetryable, $record['context']['retryable'] ?? null);
    }

    /**
     * @return array<string, array{string, int, list<string>, MaterialisationOutcome, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function outcomeProvider(): array
    {
        return [
            'applied' => ['applied', 9, [], MaterialisationOutcome::Applied, 'info', false],
            'unchanged' => ['unchanged', 9, [], MaterialisationOutcome::Unchanged, 'info', false],
            'rejected' => ['rejected', 8, ['bad colour'], MaterialisationOutcome::Rejected, 'warning', false],
            'failed' => ['failed', 8, ['disk gone'], MaterialisationOutcome::Failed, 'error', true],
        ];
    }

    public function testRejectionMessagesTravelInContextRatherThanInTheMessage(): void
    {
        $result = MaterialisationResult::rejected(
            new BrandingRevision(8),
            ['light link.default: contrast 2.10 is below the required 4.5'],
        );

        $this->logger->outcome($this->site, $this->target, $result);

        $record = $this->recorder->records[0];
        self::assertSame(
            ['light link.default: contrast 2.10 is below the required 4.5'],
            $record['context']['messages'] ?? null,
        );
        self::assertStringNotContainsString('contrast', $record['message']);
    }

    public function testACrashLogsTheThrowableUnderTheConventionalKey(): void
    {
        $throwable = new RuntimeException('table `globals` is missing at /var/lib/mysql');

        $this->logger->crashed($this->site, $this->target, $throwable);

        $record = $this->recorder->records[0];
        self::assertSame('error', $record['level']);
        self::assertSame(MaterialisationLogger::EVENT_CRASHED, $record['context']['event'] ?? null);
        self::assertSame($throwable, $record['context']['exception'] ?? null);
        self::assertSame(MaterialisationOutcome::Failed->value, $record['context']['outcome'] ?? null);
        self::assertStringNotContainsString('/var/lib/mysql', $record['message']);
    }

    // ---------------------------------------------------- the property that holds over all

    /**
     * The whole point of the wrapper: not one call anywhere interpolates a value into the
     * message. Every event the class can emit is exercised, then every recorded message is
     * checked for the tenant id, the revision digits and any digit at all — an interpolated
     * value would show up as at least one of the three.
     */
    public function testNoEventEverInterpolatesAValueIntoItsMessage(): void
    {
        $this->logger->started($this->site, $this->target);
        $this->logger->outcome($this->site, $this->target, MaterialisationResult::applied($this->target));
        $this->logger->outcome($this->site, $this->target, MaterialisationResult::unchanged($this->target));
        $this->logger->outcome(
            $this->site,
            $this->target,
            MaterialisationResult::rejected(new BrandingRevision(8), ['refused']),
        );
        $this->logger->outcome(
            $this->site,
            $this->target,
            MaterialisationResult::failed(new BrandingRevision(8), ['transient']),
        );
        $this->logger->crashed($this->site, $this->target, new RuntimeException('boom'));

        self::assertCount(6, $this->recorder->records);

        foreach ($this->recorder->records as $record) {
            self::assertStringNotContainsString(self::SITE, $record['message']);
            self::assertStringNotContainsString((string) self::TARGET, $record['message']);
            self::assertSame(0, preg_match('/\d/', $record['message']), $record['message']);
            self::assertNotSame([], $record['context']);
            self::assertArrayHasKey('event', $record['context']);
            self::assertArrayHasKey('site', $record['context']);
            self::assertArrayHasKey('target_revision', $record['context']);
        }
    }

    /**
     * PSR-3 placeholder syntax would be interpolation by another name; the layer does not
     * use it, so no message may contain a `{placeholder}`.
     */
    public function testNoEventUsesPsr3PlaceholderSyntax(): void
    {
        $this->logger->started($this->site, $this->target);
        $this->logger->outcome($this->site, $this->target, MaterialisationResult::applied($this->target));
        $this->logger->crashed($this->site, $this->target, new RuntimeException('boom'));

        foreach ($this->recorder->messages() as $message) {
            self::assertSame(0, preg_match('/\{[^}]*\}/', $message), $message);
        }
    }

    /**
     * @param list<string> $messages
     */
    private function makeResult(string $factory, int $liveRevision, array $messages): MaterialisationResult
    {
        $revision = new BrandingRevision($liveRevision);

        return match ($factory) {
            'applied' => MaterialisationResult::applied($revision),
            'unchanged' => MaterialisationResult::unchanged($revision),
            'rejected' => MaterialisationResult::rejected($revision, $messages),
            'failed' => MaterialisationResult::failed($revision, $messages),
            default => self::fail('Unknown result factory: ' . $factory),
        };
    }
}
