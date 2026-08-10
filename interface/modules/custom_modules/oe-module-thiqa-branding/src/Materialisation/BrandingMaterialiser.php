<?php

/**
 * Applies one branding revision to one tenant, all-or-nothing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Materialisation;

use DateTimeInterface;
use JsonException;
use LogicException;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\Config\BrandingGlobalKey;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use OpenEMR\Modules\ThiqaBranding\Token\TokenKey;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSet;
use OpenEMR\Modules\ThiqaBranding\Token\TokenValidationResult;
use OpenEMR\Modules\ThiqaBranding\Token\TokenValidator;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Plane 2 of the branding layer (plan §3.2): out-of-request, tenant-scoped, idempotent.
 * This is the one component of the layer permitted to be slow — nothing on a request
 * path may call it, and the PHPStan plane rule (WP-2.7) keeps it unreachable from a web
 * entry point.
 *
 * The transaction model is plan §4.4, implemented in that order:
 *
 *   1. receive the job (tenant, target revision, tokens, asset refs, strings);
 *   2. **re-validate everything tenant-side** — TokenValidator runs here as well as in
 *      the Control Plane, because the tenant does not trust the Control Plane blindly
 *      (plan §3.9). The Tier 1 base it measures against comes from product data shipped
 *      in the image, never from the job, so a hostile payload cannot supply a base that
 *      makes its own overlay pass;
 *   3. stage: token stylesheets and asset binaries written to temporary names beside
 *      their targets, globals delta computed in memory;
 *   4. verify: staged bytes re-read and re-hashed; declared asset checksums recomputed;
 *   5. apply, each step individually reversible —
 *        a. rename staged assets into place,
 *        b. rename staged stylesheets into place,
 *        c. write the globals delta, the materialisation timestamp and
 *           `saas_branding_revision = target` in ONE database transaction, with the
 *           revision written last inside that transaction;
 *   6. on any failure: revert committed renames and discard staged files. The database
 *      needs no unwinding here — the writer owns its transaction and has already rolled
 *      itself back — so revision n-1 stays fully active and the run returns
 *      MaterialisationResult::failed() marked retryable.
 *
 * **Why the revision is written last.** Every branding URL carries the revision
 * (plan §3.8.1): the token stylesheet as `?rev=<n>`, every logo as `&rev=<n>`, the SMART
 * style contract inside its payload. A browser therefore addresses branding by revision,
 * not by path alone. Until step (d) commits, the tenant's revision global still reads
 * n-1, so every URL the application emits is an n-1 URL and every cache entry behind
 * those URLs is an n-1 entry — even though the n bytes are already sitting at their
 * final paths. The switch from n-1 to n is thus a single integer write, and a partially
 * applied state is never observable by a browser: it sees revision n-1 consistently, or
 * revision n consistently, and nothing in between. Writing the revision anywhere earlier
 * would invert that, publishing URLs for bytes that may never arrive.
 *
 * **Idempotence.** The first thing the run does is read the live revision; if the target
 * is not strictly greater, it returns `unchanged()` having read nothing else, staged
 * nothing and written nothing. Re-running a completed job is therefore free and safe,
 * which is what makes retry (step 6) a sound recovery strategy rather than a gamble.
 * Within a run every write is an idempotent upsert, so a retry after a partial failure
 * converges on the same state.
 *
 * **The seam that used to be here is closed.** An earlier revision of this class wrote the
 * globals delta and the revision in two separate transactions, so a failure between them
 * left the delta live under a stale revision — a partial application, which locked
 * decision Q76 forbids, and one that revisioning could not mask because non-URL values
 * such as the product name are not addressed by revision at all. Audit finding
 * AR-P2-001. Both now commit as a single unit via
 * {@see BrandingGlobalsWriterInterface::writeAll()}; the tenant is wholly on n or wholly
 * on n-1, never between.
 */
final readonly class BrandingMaterialiser
{
    /**
     * `globals.gl_value` is `varchar(255)`.
     *
     * A value longer than the column is refused rather than written, because MySQL in a
     * non-strict mode truncates silently — and a silently truncated token overlay is a
     * JSON fragment that `TokenOverlay::fromJson()` discards whole, degrading the tenant
     * to Tier 1 with no error anywhere. Refusing here turns that into an auditable
     * rejection.
     */
    public const MAX_GLOBAL_VALUE_LENGTH = 255;

    /** Globals the materialiser computes; a job that tries to set them is refused. */
    private const RESERVED_KEYS = [
        BrandingGlobalKey::Revision,
        BrandingGlobalKey::MaterialisedAt,
        BrandingGlobalKey::TokensLight,
        BrandingGlobalKey::TokensDark,
    ];

    public function __construct(
        private TokenValidator $validator,
        private Tier1PaletteProviderInterface $palettes,
        private TokenCssWriter $cssWriter,
        private AtomicFileWriter $files,
        private TenantBrandingPaths $paths,
        private BrandingGlobalsWriterInterface $globals,
        private ClockInterface $clock,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Apply the job. Never throws for a rejected payload or a failed apply.
     *
     * @throws LogicException only on programmer error — a derived path that escapes the
     *                        tenant's scope, which means a bug in this class rather than
     *                        anything a payload can cause
     */
    public function materialise(MaterialisationJob $job): MaterialisationResult
    {
        $this->logger->info('Branding materialisation starting', [
            'site' => $job->site->value,
            'target_revision' => $job->targetRevision->value,
        ]);

        try {
            $current = $this->globals->currentRevision($job->site);
        } catch (LogicException | RuntimeException $throwable) {
            $this->logger->error('Branding materialisation could not read the current revision', [
                'site' => $job->site->value,
                'target_revision' => $job->targetRevision->value,
                'exception' => $throwable,
            ]);

            return MaterialisationResult::failed(
                BrandingRevision::initial(),
                ['The tenant\'s current branding revision could not be read.'],
            );
        }

        // Step 0 — idempotence. Nothing below this line runs for an already-applied revision.
        if ($job->targetRevision->value <= $current->value) {
            $this->logger->info('Branding materialisation skipped: revision already applied', [
                'site' => $job->site->value,
                'target_revision' => $job->targetRevision->value,
                'current_revision' => $current->value,
            ]);

            return MaterialisationResult::unchanged($current);
        }

        return $this->apply($job, $current);
    }

    private function apply(MaterialisationJob $job, BrandingRevision $current): MaterialisationResult
    {
        // ------------------------------------------------------------ step 2: re-validate
        try {
            $lightBase = $this->palettes->paletteFor(ThemeVariant::Light);
            $darkBase = $this->palettes->paletteFor(ThemeVariant::Dark);
        } catch (LogicException $throwable) {
            $this->logger->error('Branding materialisation could not load the product token document', [
                'site' => $job->site->value,
                'exception' => $throwable,
            ]);

            return MaterialisationResult::failed(
                $current,
                ['The product token document could not be loaded.'],
            );
        }

        $light = $this->validator->validateOverlay($job->lightOverlay, $lightBase);
        $dark = $this->validator->validateOverlay($job->darkOverlay, $darkBase);

        $messages = [
            ...self::prefix('light', $light->messages()),
            ...self::prefix('dark', $dark->messages()),
            ...$this->rejectReservedGlobals($job),
            ...$this->rejectBadAssets($job),
        ];

        $lightTokens = $light->resolved();
        $darkTokens = $dark->resolved();

        if ($messages === [] && ($lightTokens instanceof TokenSet) && ($darkTokens instanceof TokenSet)) {
            $delta = $this->computeDelta($job, $light, $dark);
            if ($delta === null) {
                $messages[] = 'The validated token overlay could not be encoded for storage.';
            } else {
                $messages = [...$messages, ...$this->rejectOversizedValues($delta)];
                if ($messages === []) {
                    return $this->stageVerifyAndApply($job, $current, $lightTokens, $darkTokens, $delta);
                }
            }
        }

        if ($messages === []) {
            // isValid() with a null resolved set would mean TokenValidationResult broke its
            // own invariant. Report rather than dereference null.
            $messages[] = 'The token overlay produced no resolved palette.';
        }

        $this->logger->warning('Branding materialisation rejected the payload', [
            'site' => $job->site->value,
            'target_revision' => $job->targetRevision->value,
            'current_revision' => $current->value,
            'rejections' => $messages,
        ]);

        return MaterialisationResult::rejected($current, $messages);
    }

    /**
     * Steps 3 to 5. Everything from here is reversible until the revision write commits.
     */
    private function stageVerifyAndApply(
        MaterialisationJob $job,
        BrandingRevision $current,
        TokenSet $lightTokens,
        TokenSet $darkTokens,
        GlobalsDelta $delta,
    ): MaterialisationResult {
        /** @var list<StagedFile> $staged */
        $staged = [];
        /** @var list<CommittedFile> $committed */
        $committed = [];

        try {
            // -------------------------------------------------------------- step 3: stage
            // Assets first so that the commit loop below runs 5a before 5b.
            foreach ($job->assets as $asset) {
                $target = $this->paths->logoFile($job->site, $asset);
                $this->assertTenantScope($job->site, $target);
                $staged[] = $this->files->stage($target, $asset->contents);
            }

            foreach ([ThemeVariant::Light, ThemeVariant::Dark] as $variant) {
                $tokens = $variant === ThemeVariant::Light ? $lightTokens : $darkTokens;

                // Byte-identical output is skipped so an unchanged stylesheet keeps its
                // mtime; the revision still moves, which is what the URLs key on.
                if ($this->cssWriter->isCurrent($job->site, $variant, $tokens)) {
                    continue;
                }

                $target = $this->cssWriter->targetPath($job->site, $variant);
                $this->assertTenantScope($job->site, $target);
                $staged[] = $this->cssWriter->stage($job->site, $variant, $tokens);
            }

            // ------------------------------------------------------------- step 4: verify
            foreach ($staged as $file) {
                if (!$this->files->verify($file)) {
                    throw FilesystemException::notWritten($file->temporaryPath);
                }
            }

            // --------------------------------------------------- steps 5a and 5b: rename
            foreach ($staged as $file) {
                $committed[] = $this->files->commit($file);
            }

            // --------------- step 5c+5d: delta, timestamp and revision, ONE transaction
            //
            // Previously two transactions, which meant a failure between them left the
            // delta live under a stale revision (audit finding AR-P2-001). The writer
            // now commits all three together, with the revision written last inside the
            // unit, so the tenant is either wholly on n or wholly on n-1.
            $this->globals->writeAll(
                $job->site,
                $delta,
                $job->targetRevision,
                $this->clock->now()->format(DateTimeInterface::ATOM),
            );
        } catch (LogicException | RuntimeException $throwable) {
            $this->unwind($committed, $staged);

            $this->logger->error('Branding materialisation failed; the previous revision is still active', [
                'site' => $job->site->value,
                'target_revision' => $job->targetRevision->value,
                'active_revision' => $current->value,
                'exception' => $throwable,
            ]);

            return MaterialisationResult::failed($current, [
                sprintf(
                    'Materialisation of revision %d failed; revision %d remains active.',
                    $job->targetRevision->value,
                    $current->value,
                ),
            ]);
        }

        // The displaced originals are no longer needed once the revision is live.
        foreach ($committed as $file) {
            $this->files->finalise($file);
        }

        $this->logger->info('Branding materialisation applied', [
            'site' => $job->site->value,
            'revision' => $job->targetRevision->value,
            'previous_revision' => $current->value,
            'files_written' => count($committed),
            'globals_written' => $delta->count(),
        ]);

        return MaterialisationResult::applied($job->targetRevision);
    }

    /**
     * Step 6. Total by construction: rollback must not itself throw and abandon the rest.
     *
     * @param list<CommittedFile> $committed
     * @param list<StagedFile>    $staged
     */
    private function unwind(array $committed, array $staged): void
    {
        // No database unwind here any more. writeAll() is a single transaction owned by
        // the writer, which rolls itself back and rethrows on failure, so by the time
        // this runs the database is already consistent. Only the filesystem needs
        // reversing (audit finding AR-P2-001).

        // Reverse order: the last file placed is the first one taken back.
        foreach (array_reverse($committed) as $file) {
            $this->files->revert($file);
        }

        foreach ($staged as $file) {
            $this->files->discard($file);
        }
    }

    /**
     * A job may not set the globals the materialiser owns.
     *
     * The revision, the materialisation stamp and the two token overlays are computed
     * from the run itself. Accepting them from the payload would let the Control Plane
     * declare a revision it had not actually applied, or install a token overlay that
     * never passed TokenValidator — the exact bypass step 2 exists to prevent.
     *
     * @return list<string>
     */
    private function rejectReservedGlobals(MaterialisationJob $job): array
    {
        $messages = [];
        foreach (self::RESERVED_KEYS as $key) {
            if ($job->strings->has($key)) {
                $messages[] = sprintf(
                    'globals "%s": this value is computed by the materialiser and cannot be supplied.',
                    $key->value,
                );
            }
        }

        return $messages;
    }

    /**
     * Step 4's tenant-side half for assets: recompute what the Control Plane declared.
     *
     * @return list<string>
     */
    private function rejectBadAssets(MaterialisationJob $job): array
    {
        $messages = [];
        $seen = [];

        foreach ($job->assets as $asset) {
            $identity = $asset->slot->value . '/' . $asset->filename;
            if (isset($seen[$identity])) {
                $messages[] = sprintf('asset "%s": the same target is placed twice in one job.', $identity);
                continue;
            }

            $seen[$identity] = true;

            if (!$asset->matchesDeclaredChecksum()) {
                $messages[] = sprintf(
                    'asset "%s": the declared checksum does not match the supplied bytes.',
                    $identity,
                );
            }
        }

        return $messages;
    }

    /**
     * @return list<string>
     */
    private function rejectOversizedValues(GlobalsDelta $delta): array
    {
        $messages = [];
        foreach ($delta->all() as $name => $value) {
            if (strlen($value) > self::MAX_GLOBAL_VALUE_LENGTH) {
                $messages[] = sprintf(
                    'globals "%s": value is %d bytes, above the %d-byte column limit.',
                    $name,
                    strlen($value),
                    self::MAX_GLOBAL_VALUE_LENGTH,
                );
            }
        }

        return $messages;
    }

    /**
     * The globals delta of step 3: the job's strings plus the two computed token overlays.
     *
     * Null when the accepted overlay cannot be encoded, which is a programmer-level
     * impossibility (every value is a `#RRGGBB` literal) reported rather than thrown.
     */
    private function computeDelta(
        MaterialisationJob $job,
        TokenValidationResult $light,
        TokenValidationResult $dark,
    ): ?GlobalsDelta {
        $lightJson = $this->encodeOverlay($job->lightOverlay, $light);
        $darkJson = $this->encodeOverlay($job->darkOverlay, $dark);

        if ($lightJson === null || $darkJson === null) {
            return null;
        }

        return $job->strings
            ->with(BrandingGlobalKey::TokensLight, $lightJson)
            ->with(BrandingGlobalKey::TokensDark, $darkJson);
    }

    /**
     * Canonical JSON for the accepted overlay entries, or '' when the overlay is empty.
     *
     * Emitted in TokenKey declaration order rather than payload order so that two jobs
     * carrying the same overlay produce byte-identical globals — plan §3.8's invariant
     * that a rollback reproduces byte-identical output applies to the stored overlay as
     * much as to the stylesheet.
     *
     * Values are read back out of the *resolved* palette, so what is stored is the
     * normalised colour the validator accepted, not the literal the payload sent.
     *
     * @param array<array-key, mixed> $overlay
     */
    private function encodeOverlay(array $overlay, TokenValidationResult $result): ?string
    {
        $resolved = $result->resolved();
        if (!$resolved instanceof TokenSet) {
            return null;
        }

        $canonical = [];
        foreach (TokenKey::cases() as $key) {
            if (!array_key_exists($key->value, $overlay)) {
                continue;
            }

            $value = $resolved->valueOf($key);
            if ($value === null) {
                continue;
            }

            $canonical[$key->value] = $value->value;
        }

        if ($canonical === []) {
            // Empty means "the shared Tier 1 palette applies unchanged", which is exactly
            // BrandingGlobalKey::TokensLight's documented blank default.
            return '';
        }

        try {
            return json_encode($canonical, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException) {
            return null;
        }
    }

    /**
     * Post-condition on every derived path: it must be inside this tenant's scope.
     *
     * TenantBrandingPaths already makes an escape unrepresentable, so a failure here is
     * a bug in this class, not a hostile payload — hence LogicException rather than a
     * rejection.
     */
    private function assertTenantScope(SiteId $site, string $path): void
    {
        if (!$this->paths->isWithinTenantScope($site, $path)) {
            throw new LogicException('Refusing to write a branding file outside the tenant\'s own scope.');
        }
    }

    /**
     * @param list<string> $messages
     *
     * @return list<string>
     */
    private static function prefix(string $label, array $messages): array
    {
        return array_map(
            static fn (string $message): string => $label . ' ' . $message,
            $messages,
        );
    }
}
