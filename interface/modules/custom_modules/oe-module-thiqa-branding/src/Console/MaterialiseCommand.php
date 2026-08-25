<?php

/**
 * Applies one branding revision to one tenant from the command line.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use LogicException;
use OpenEMR\Modules\ThiqaBranding\Asset\BrandingRevision;
use OpenEMR\Modules\ThiqaBranding\AssetIntake\LogoValidator;
use OpenEMR\Modules\ThiqaBranding\Materialisation\AssetPlacement;
use OpenEMR\Modules\ThiqaBranding\Materialisation\BrandingMaterialiser;
use OpenEMR\Modules\ThiqaBranding\Materialisation\MaterialisationJob;
use OpenEMR\Modules\ThiqaBranding\Materialisation\MaterialisationResult;
use OpenEMR\Modules\ThiqaBranding\Observability\BrandingHealthCheck;
use OpenEMR\Modules\ThiqaBranding\Observability\BrandingHealthStatus;
use OpenEMR\Modules\ThiqaBranding\Observability\MaterialisationLogger;
use OpenEMR\Modules\ThiqaBranding\Observability\MaterialisationOutcome;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The CLI face of Plane 2 (plan §3.2): out-of-request, tenant-scoped, idempotent. This is
 * the one place in the branding layer where being slow is acceptable, and the only entry
 * point permitted to reach BrandingMaterialiser at all.
 *
 * The command itself decides almost nothing. Tenant scope is enforced by SiteId and by the
 * materialiser; validation, staging, ordering and rollback are the materialiser's; the
 * outcome vocabulary and the log shape are MaterialisationLogger's. What is left here is
 * exactly three things: turn `--site`, `--revision` and `--payload` into a
 * MaterialisationJob, render the MaterialisationResult for a human, and turn the outcome
 * into an exit code for a machine.
 *
 * **Exit codes**, chosen so cron and CI can branch without parsing output:
 *
 * | code | meaning | what a caller should do |
 * |------|---------|-------------------------|
 * | `0`  | applied, or unchanged because the revision was already live | nothing |
 * | `1`  | a transient fault; revision n-1 is fully active | retry the same invocation |
 * | `2`  | the invocation or the payload was refused | fix it; retrying is pointless |
 *
 * `0` covering "unchanged" is the idempotence contract made operational: rerunning a
 * completed job is a no-op, so a retry loop converges instead of alarming.
 */
#[AsCommand(
    name: 'thiqa-branding:materialise',
    description: 'Apply a branding revision to one tenant (out-of-request; requires --site).',
)]
final class MaterialiseCommand extends TenantScopedBrandingCommand
{
    /** Ten digits cannot overflow a 32-bit int cast; no real revision approaches it. */
    private const MAX_REVISION_DIGITS = 9;

    public function __construct(
        private readonly BrandingMaterialiser $materialiser,
        private readonly BrandingHealthCheck $health,
        private readonly MaterialisationLogger $log,
        private readonly LogoValidator $logoValidator,
        SiteScopeNotice $siteScopeNotice,
    ) {
        parent::__construct($siteScopeNotice);
    }

    protected function configure(): void
    {
        $this->getDefinition()->addOption(SiteOption::define());

        $this
            ->addOption(
                'revision',
                null,
                InputOption::VALUE_REQUIRED,
                'Target revision (1 or greater). Defaults to the tenant\'s current revision plus one.',
            )
            ->addOption(
                'payload',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to a JSON payload of token overlays and branding globals. '
                    . 'Omit to bump the revision without changing anything else.',
            )
            ->setHelp(
                'Applies one branding revision to one tenant, all-or-nothing. Runs out of the '
                . 'request path and is safe to rerun: a revision that is already live is reported '
                . 'as unchanged and exits 0.' . PHP_EOL . PHP_EOL
                . 'The tenant is never implied. --site is mandatory and has no default, and it must '
                . 'name the same site bin/console was bootstrapped against.',
            );
    }

    protected function executeForSite(InputInterface $input, SymfonyStyle $io, SiteId $site): int
    {
        // Reading the live revision here, rather than letting the materialiser discover it,
        // is what lets --revision default to "the next one" and what lets the summary show
        // the operator what they moved from.
        $report = $this->health->check($site);
        if ($report->status === BrandingHealthStatus::Unreadable) {
            $io->error(
                'The tenant\'s branding state could not be read, so no revision can be applied. '
                . 'See the application log for the underlying error.',
            );

            return Command::FAILURE;
        }

        $target = $this->targetRevision($input, $report->revision);
        if (!$target instanceof BrandingRevision) {
            $io->error($target);

            return Command::INVALID;
        }

        $payload = $this->payload($input);
        if (!$payload->isValid()) {
            $io->error('The payload was refused; nothing has been changed.');
            $io->listing($payload->errors);

            return Command::INVALID;
        }

        $assets = $this->resolveAssets($payload);
        if (!$assets->isValid()) {
            $io->error('One or more assets were refused; nothing has been changed.');
            $io->listing($assets->rejections);

            return Command::INVALID;
        }

        $job = $payload->applyTo(MaterialisationJob::forRevision($site, $target))
            ->withAssets($assets->placements);

        $this->log->started($site, $target);

        try {
            $result = $this->materialiser->materialise($job);
        } catch (LogicException $exception) {
            // LogicException — a derived path escaping the tenant's scope — is the one thing
            // the materialiser documents letting out; everything else it converts into a
            // result. It is logged with its context and summarised generically, because an
            // exception message can carry SQL and filesystem paths.
            //
            // A narrow type rather than \Throwable or \Exception: this repo's
            // ForbiddenCatchTypeRule forbids any catch that would suppress \Error or
            // \ErrorException, so a genuine bug still reaches the console's own handler.
            $this->log->crashed($site, $target, $exception);
            $io->error(
                'The materialisation run ended in an unexpected error and was not completed. '
                . 'See the application log for details.',
            );

            return Command::FAILURE;
        }

        $outcome = $this->log->outcome($site, $target, $result);

        $this->summarise($io, $site, $target, $result, $outcome);

        return match ($outcome) {
            MaterialisationOutcome::Applied, MaterialisationOutcome::Unchanged => Command::SUCCESS,
            MaterialisationOutcome::Rejected => Command::INVALID,
            MaterialisationOutcome::Failed => Command::FAILURE,
        };
    }

    /**
     * The revision to aim at, or the operator-facing reason the option could not be read.
     *
     * Defaulting to current + 1 keeps the common case ("push what I just configured")
     * free of arithmetic, while an explicit value stays available for replaying a specific
     * revision in an incident.
     */
    private function targetRevision(InputInterface $input, BrandingRevision $current): BrandingRevision|string
    {
        $raw = $input->getOption('revision');
        if ($raw === null) {
            return $current->next();
        }

        if (!is_string($raw) || $raw === '') {
            return 'The --revision option needs a whole number of 1 or greater as its value.';
        }

        if (!ctype_digit($raw) || strlen($raw) > self::MAX_REVISION_DIGITS) {
            return 'The --revision option must be a whole number of 1 or greater.';
        }

        $value = (int) $raw;
        if ($value < 1) {
            return 'Revision 0 means "never materialised" and cannot be a target; use 1 or greater.';
        }

        return new BrandingRevision($value);
    }

    private function payload(InputInterface $input): JobPayload
    {
        $raw = $input->getOption('payload');
        if ($raw === null) {
            return JobPayload::empty();
        }

        if (!is_string($raw) || $raw === '') {
            return JobPayload::empty();
        }

        return JobPayload::fromFile($raw);
    }

    /**
     * Run every asset request in the payload through the real validator.
     *
     * This is the seam audit finding AR-P2-002 named: JobPayload cannot construct a
     * LogoValidator (it is a static parser with no DI), so requests stay unresolved until
     * here, where the command's own validator instance is the only thing that can turn one
     * into a placeable AssetPlacement. All-or-nothing, like a token overlay: one rejected
     * request means none of the requests in this payload are placed.
     */
    private function resolveAssets(JobPayload $payload): AssetResolution
    {
        $placements = [];
        $rejections = [];

        foreach ($payload->assets as $request) {
            $result = $this->logoValidator->validate($request->slot, $request->sourcePath, $request->declaredFilename);
            $asset = $result->asset();
            if ($asset === null) {
                array_push($rejections, ...$result->messages());
                continue;
            }

            $placements[] = AssetPlacement::fromValidatedSelfChecked($asset);
        }

        return $rejections === [] ? AssetResolution::accepted($placements) : AssetResolution::rejected($rejections);
    }

    /**
     * The human half of the output. Every machine-readable fact is already in the log.
     */
    private function summarise(
        SymfonyStyle $io,
        SiteId $site,
        BrandingRevision $target,
        MaterialisationResult $result,
        MaterialisationOutcome $outcome,
    ): void {
        $io->definitionList(
            ['Site' => $site->value],
            ['Target revision' => (string) $target->value],
            ['Live revision' => (string) $result->revision()->value],
            ['Outcome' => $outcome->label()],
            ['Retryable' => $result->isRetryable() ? 'yes' : 'no'],
        );

        if ($result->messages() !== []) {
            $io->section('Rejections');
            $io->listing($result->messages());
        }

        match ($outcome) {
            MaterialisationOutcome::Applied => $io->success(
                sprintf('Revision %d is now live.', $result->revision()->value),
            ),
            MaterialisationOutcome::Unchanged => $io->success(
                sprintf('Nothing to do: revision %d is already live.', $result->revision()->value),
            ),
            MaterialisationOutcome::Rejected => $io->warning(
                sprintf(
                    'The payload was refused. Revision %d remains live; change the payload before retrying.',
                    $result->revision()->value,
                ),
            ),
            MaterialisationOutcome::Failed => $io->error(
                sprintf(
                    'The run failed and was unwound. Revision %d remains live; the same payload can be retried.',
                    $result->revision()->value,
                ),
            ),
        };
    }
}
