<?php

/**
 * Reports one tenant's branding state without changing any of it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use OpenEMR\Modules\ThiqaBranding\Observability\BrandingHealthCheck;
use OpenEMR\Modules\ThiqaBranding\Observability\BrandingHealthReport;
use OpenEMR\Modules\ThiqaBranding\Tenant\SiteId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Plan §4.3 WP-2.12: "a stale tenant is detectable without querying the tenant DB by hand".
 * This is that command — and it is deliberately nothing more than a printer.
 *
 * All of the judgement lives in BrandingHealthCheck, which holds no console dependency, so
 * the same verdict backs a monitoring endpoint or an administration screen without being
 * restated. If a rule about what "inconsistent" means ever needs changing, there is exactly
 * one place it can be changed, and it is not here.
 *
 * **Read-only.** The command opens no transaction and writes nothing, on any path,
 * including the failure paths: it holds only the health check, which itself calls just the
 * two read methods of the globals interface. It is safe to run against production on a
 * schedule.
 *
 * **Exit codes**: `0` when the tenant's *served* state is coherent — including a tenant that
 * has never been materialised, which renders product defaults and is a correct state,
 * including a merely stale one, and including one carrying static-artefact advisories.
 * `1` when the served state contradicts itself or could not be read, so the command works
 * directly as a health probe. `2` when the invocation itself was wrong (no `--site`), which
 * is an operator error rather than a finding about the tenant.
 *
 * **Advisories never change the exit code.** They describe the generated
 * `public/branding/<site>/tokens-*.css` files, which no page links (D-8), so they are worth
 * printing and worth eventually clearing but cannot make a correctly rendering tenant fail a
 * probe. Failing on one is exactly what finding S2-P1-18 recorded.
 */
#[AsCommand(
    name: 'thiqa-branding:verify',
    description: 'Report one tenant\'s branding revision, freshness and consistency (read-only).',
)]
final class VerifyCommand extends Command
{
    private const SECONDS_PER_DAY = 86400;

    public function __construct(private readonly BrandingHealthCheck $health)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->getDefinition()->addOption(SiteOption::define());

        $this->setHelp(
            'Reports the tenant\'s branding revision, when it was last materialised, whether a '
            . 'tenant token overlay is actually being served, and whether those facts agree '
            . 'with each other.' . PHP_EOL . PHP_EOL
            . 'The generated token stylesheets on disk are reported too, but they are not '
            . 'served to any page, so a disagreement about them prints as an advisory and '
            . 'never changes the exit code.' . PHP_EOL . PHP_EOL
            . 'Nothing is written, on any path. Exits non-zero when the served state is '
            . 'inconsistent or unreadable, so it can be used unchanged as a health check.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $resolved = SiteOption::resolve($input);
        if (!$resolved->site instanceof SiteId) {
            $io->error($resolved->error ?? 'A tenant site id is required.');

            return Command::INVALID;
        }

        $report = $this->health->check($resolved->site);

        $this->render($io, $report);

        return $report->isFailure() ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Served facts first, then the unserved ones, each labelled with which it is.
     *
     * The stylesheet rows used to sit beside the revision with nothing to say that no page
     * loads them, so an operator reading `present` next to `Revision 0` had every reason to
     * conclude the tenant was broken. They now say so on their own line.
     */
    private function render(SymfonyStyle $io, BrandingHealthReport $report): void
    {
        $io->definitionList(
            ['Site' => $report->site->value],
            ['Status' => $report->status->label()],
            ['Revision' => (string) $report->revision->value],
            ['Materialised' => $report->revision->isMaterialised() ? 'yes' : 'never'],
            ['Materialised at' => $report->materialisedAtIso() ?? 'not recorded'],
            ['Age' => $this->age($report)],
            ['Serves tenant overlay' => $this->overlay($report)],
            ['Light token stylesheet (not served)' => $report->lightStylesheetPresent ? 'present' : 'absent'],
            ['Dark token stylesheet (not served)' => $report->darkStylesheetPresent ? 'present' : 'absent'],
        );

        if ($report->hasAdvisories()) {
            $io->section('Advisories (generated files nothing serves — rendering is unaffected)');
            $io->listing($report->advisoryMessages());
        }

        if ($report->hasInconsistencies()) {
            $io->section('Inconsistencies');
            $io->listing($report->messages());
            $io->error('This tenant\'s served branding state is not self-consistent.');

            return;
        }

        if ($report->isStale()) {
            $io->warning(sprintf(
                'The last materialisation is older than the %d-day staleness threshold.',
                intdiv($this->health->stalenessThresholdSeconds(), self::SECONDS_PER_DAY),
            ));

            return;
        }

        $io->success('This tenant\'s served branding state is self-consistent.');
    }

    /** What the browser would actually fetch, stated as a count rather than a bare flag. */
    private function overlay(BrandingHealthReport $report): string
    {
        if (!$report->servesTenantOverlay) {
            return 'no (rendering the product palette)';
        }

        return sprintf(
            'yes (%d light, %d dark token overrides)',
            $report->lightOverlayTokenCount,
            $report->darkOverlayTokenCount,
        );
    }

    /** Whole days where that reads naturally, seconds while it is still fresh. */
    private function age(BrandingHealthReport $report): string
    {
        $age = $report->ageInSeconds;
        if ($age === null) {
            return 'unknown';
        }

        if ($age < 0) {
            return 'in the future';
        }

        if ($age < self::SECONDS_PER_DAY) {
            return sprintf('%d seconds', $age);
        }

        return sprintf('%d days', intdiv($age, self::SECONDS_PER_DAY));
    }
}
