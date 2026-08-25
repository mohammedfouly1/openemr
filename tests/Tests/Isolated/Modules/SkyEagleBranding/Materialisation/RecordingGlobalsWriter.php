<?php

/**
 * In-memory, genuinely transactional stand-in for the tenant globals table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\SkyEagleBranding\Materialisation;

use LogicException;
use OpenEMR\Modules\SkyEagleBranding\Asset\BrandingRevision;
use OpenEMR\Modules\SkyEagleBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\BrandingGlobalsWriterInterface;
use OpenEMR\Modules\SkyEagleBranding\Materialisation\GlobalsDelta;
use OpenEMR\Modules\SkyEagleBranding\Tenant\SiteId;
use RuntimeException;

/**
 * Not a mock. The materialiser's contract is about *ordering* and *rollback*, so the
 * double has to model both: it records every operation in sequence and it snapshots
 * state on begin so a rollback really restores it. A mock returning canned values would
 * let a materialiser that wrote the revision first still pass.
 *
 * Site binding mirrors the shipped QueryUtils adapter: a call naming a site this writer
 * was not constructed for is programmer error and throws.
 */
final class RecordingGlobalsWriter implements BrandingGlobalsWriterInterface
{
    /** @var list<string> ordered operation log: 'begin', 'write:<gl_name>', 'commit', 'rollback' */
    public array $operations = [];

    /** @var array<string, string> */
    public array $stored = [];

    /** Write of this globals name throws, to exercise the step-5 failure path. */
    public ?string $failOnWriteOf = null;

    /**
     * Called with the globals name at the instant of each write, before it is stored.
     *
     * This is how a test observes the world *during* the transaction rather than after
     * it — the only way to prove that the stylesheets were already in place when the
     * revision flipped.
     *
     * @var (callable(string): void)|null
     */
    public $observer = null;

    /** @var array<string, string>|null */
    private ?array $snapshot = null;

    public function __construct(private readonly SiteId $boundSite)
    {
    }

    public function currentRevision(SiteId $site): BrandingRevision
    {
        $this->assertBoundTo($site);

        $raw = $this->stored[BrandingGlobalKey::Revision->value] ?? null;
        if ($raw === null || !ctype_digit($raw)) {
            return BrandingRevision::initial();
        }

        return new BrandingRevision((int) $raw);
    }

    /**
     * @return array<string, string>
     */
    public function readBrandingGlobals(SiteId $site): array
    {
        $this->assertBoundTo($site);

        return $this->stored;
    }

    /**
     * The single atomic unit, mirroring the shipped QueryUtils adapter.
     *
     * Snapshot on entry, restore on any failure: a rollback here really does put the
     * previous values back, so a test asserting "revision n-1 survived" is measuring
     * restoration rather than a mock's canned answer.
     */
    public function writeAll(
        SiteId $site,
        GlobalsDelta $delta,
        BrandingRevision $revision,
        string $materialisedAt,
    ): void {
        $this->assertBoundTo($site);

        $this->operations[] = 'begin';
        $this->snapshot = $this->stored;

        try {
            foreach ($delta->all() as $name => $value) {
                $key = BrandingGlobalKey::tryFrom($name);
                if (!$key instanceof BrandingGlobalKey) {
                    throw new LogicException('Globals delta carried a name outside the branding registry.');
                }

                $this->applyOne($key, $value);
            }

            $this->applyOne(BrandingGlobalKey::MaterialisedAt, $materialisedAt);

            // LAST, inside the same unit.
            $this->applyOne(BrandingGlobalKey::Revision, (string) $revision->value);
        } catch (LogicException | RuntimeException $throwable) {
            $this->operations[] = 'rollback';
            // ?? [] because $snapshot is nullable: a throw before the snapshot is taken must
            // still leave $stored a valid array rather than assigning null into it.
            $this->stored = $this->snapshot ?? [];
            $this->snapshot = null;

            throw $throwable;
        }

        $this->operations[] = 'commit';
        $this->snapshot = null;
    }

    private function applyOne(BrandingGlobalKey $key, string $value): void
    {
        $this->operations[] = 'write:' . $key->value;

        if (is_callable($this->observer)) {
            ($this->observer)($key->value);
        }

        if ($this->failOnWriteOf === $key->value) {
            throw new RuntimeException('Simulated database failure.');
        }

        $this->stored[$key->value] = $value;
    }

    public function forget(): void
    {
        $this->operations = [];
    }

    /** @return list<string> the globals names written, in order */
    public function writtenNames(): array
    {
        $names = [];
        foreach ($this->operations as $operation) {
            if (str_starts_with($operation, 'write:')) {
                $names[] = substr($operation, strlen('write:'));
            }
        }

        return $names;
    }

    private function assertBoundTo(SiteId $site): void
    {
        if (!$site->equals($this->boundSite)) {
            throw new LogicException('Refusing a branding globals call for an unbound site.');
        }
    }
}
