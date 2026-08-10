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
 * **Exit codes**: `0` when the tenant's state is coherent — including a tenant that has
 * never been materialised, which renders product defaults and is a correct state, and
 * including a merely stale one. `1` when the state contradicts itself or could not be read,
 * so the command works directly as a health probe. `2` when the invocation itself was wrong
 * (no `--site`), which is an operator error rather than a finding about the tenant.
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
            'Reports the tenant\'s branding revision, when it was last materialised, whether '
            . 'the generated token stylesheets are present, and whether those facts agree with '
            . 'each other.' . PHP_EOL . PHP_EOL
            . 'Nothing is written, on any path. Exits non-zero when the state is inconsistent '
            . 'or unreadable, so it can be used unchanged as a health check.',
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

    private function render(SymfonyStyle $io, BrandingHealthReport $report): void
    {
        $io->definitionList(
            ['Site' => $report->site->value],
            ['Status' => $report->status->label()],
            ['Revision' => (string) $report->revision->value],
            ['Materialised' => $report->revision->isMaterialised() ? 'yes' : 'never'],
            ['Materialised at' => $report->materialisedAtIso() ?? 'not recorded'],
            ['Age' => $this->age($report)],
            ['Light token stylesheet' => $report->lightStylesheetPresent ? 'present' : 'absent'],
            ['Dark token stylesheet' => $report->darkStylesheetPresent ? 'present' : 'absent'],
        );

        if ($report->hasInconsistencies()) {
            $io->section('Inconsistencies');
            $io->listing($report->messages());
            $io->error('This tenant\'s branding state is not self-consistent.');

            return;
        }

        if ($report->isStale()) {
            $io->warning(sprintf(
                'The last materialisation is older than the %d-day staleness threshold.',
                intdiv($this->health->stalenessThresholdSeconds(), self::SECONDS_PER_DAY),
            ));

            return;
        }

        $io->success('This tenant\'s branding state is self-consistent.');
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
