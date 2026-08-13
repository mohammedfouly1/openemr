<?php

/**
 * Provisions the Thiqa report-authorization ACO so a clean deployment matches a live one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use OpenEMR\Common\Acl\AclExtended;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * RDY-0050 (Option A+) gates `interface/reports/patient_list.php` and
 * `interface/reports/unique_seen_patients_report.php` on `patients|bulk_rep`, an ACO that
 * upstream OpenEMR does not ship. Without this command that ACO would exist only in whichever
 * database someone happened to run a one-off script against, and a fresh deployment would
 * resolve the guard against a missing object — denying **every** role including Administrators,
 * and presenting as a permissions mystery rather than a missing migration.
 *
 * It lives in this module rather than in core `acl_upgrade.php` because locked-decision
 * Invariant 4 forbids core edits without a numbered patch record, and the requirement is
 * Thiqa-specific. It follows the module's established console pattern so it is discoverable
 * via `bin/console` alongside the branding commands.
 *
 * **Idempotent by construction.** `addObjectAcl()` checks `get_object_id()` before inserting and
 * `updateAcl()` re-points an existing entry rather than duplicating it, so running this on every
 * deploy and every upgrade is safe and is the intended usage.
 *
 * **Exit codes**: `0` provisioned (or already present); `1` a required group ACL was missing, so
 * a grant could not be placed and the deployment is not yet correct.
 */
#[AsCommand(
    name: 'thiqa-branding:provision-report-acl',
    description: 'Provision the Thiqa bulk-patient-report ACO and its group grants (idempotent).',
)]
final class ProvisionReportAclCommand extends Command
{
    private const SECTION_NAME = 'patients';
    private const SECTION_TITLE = 'Patients';

    /**
     * Two ACOs, deliberately distinct because the reports differ in sensitivity.
     *
     *  - bulk_rep: bulk patient-identifying reports with CSV/label export. Admin + Physician only.
     *  - op_rep:   chart-tracking and flow-board reports. Low PHI, no export. Reception genuinely
     *              needs these, so Front Office keeps them — but Accounting does not, which gives
     *              the least-privilege negative case that leaning on the broad `patients|appt`
     *              could never provide.
     *
     * @var array<string, array{title: string, groups: list<string>}>
     */
    private const ACOS = [
        'bulk_rep' => [
            'title' => 'Bulk Patient-Identifying Reports',
            'groups' => ['Administrators', 'Physicians'],
        ],
        'op_rep' => [
            'title' => 'Operational / Chart-Tracking Reports',
            'groups' => ['Administrators', 'Physicians', 'Front Office', 'Clinicians'],
        ],
    ];

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Report what would be provisioned without writing anything.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        $io->title('Thiqa report authorization provisioning');

        $missing = [];
        foreach (self::ACOS as $objectName => $spec) {
            $io->writeln(sprintf(
                'ACO %s|%s (%s) -> %s',
                self::SECTION_NAME,
                $objectName,
                $spec['title'],
                implode(', ', $spec['groups'])
            ));

            if ($dryRun) {
                continue;
            }

            AclExtended::addObjectAcl(
                self::SECTION_NAME,
                self::SECTION_TITLE,
                $objectName,
                $spec['title']
            );

            foreach ($spec['groups'] as $groupTitle) {
                $aclId = AclExtended::getAclIdNumber($groupTitle, 'write');
                if (empty($aclId)) {
                    $missing[] = $groupTitle . '/' . $objectName;
                    continue;
                }
                AclExtended::updateAcl(
                    $aclId,
                    $groupTitle,
                    self::SECTION_NAME,
                    self::SECTION_TITLE,
                    $objectName,
                    $spec['title'],
                    'write'
                );
            }
        }

        if ($dryRun) {
            $io->note('Dry run — nothing was written.');

            return self::SUCCESS;
        }

        if ($missing !== []) {
            $io->error('No write ACL found for: ' . implode(', ', $missing));

            return self::FAILURE;
        }

        $io->success('Provisioned ' . count(self::ACOS) . ' ACO(s).');

        return self::SUCCESS;
    }
}
