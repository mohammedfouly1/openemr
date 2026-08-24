<?php

/** @package OpenEMR */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Translation\QueryUtilsTranslationCatalogueStore;
use OpenEMR\Common\Translation\TranslationCatalogueContractSet;
use OpenEMR\Common\Translation\TranslationCatalogueMigration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class TranslationCatalogueMigrationCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:translation-catalogue-migrate')
            ->setDescription('Apply or roll back durable identity-neutral translation contracts')
            ->addOption('rollback', null, InputOption::VALUE_NONE, 'Restore the exact journalled pre-migration state')
            ->addOption('contract', null, InputOption::VALUE_REQUIRED, 'Act on one contract id instead of all of them')
            ->setHelp(
                'Applies every contract in ' . TranslationCatalogueContractSet::RELATIVE_DIRECTORY . '.'
                . PHP_EOL . PHP_EOL
                . 'Each contract runs in its own transaction and keeps its own journal row, so one '
                . 'failing contract neither half-applies nor blocks the others from being rolled '
                . 'back individually. Rolling back reverses the order contracts were applied in.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $set = TranslationCatalogueContractSet::fromProjectDirectory(dirname(__DIR__, 3));
        $migration = new TranslationCatalogueMigration();
        $store = new QueryUtilsTranslationCatalogueStore();

        $only = $input->getOption('contract');
        $rollback = (bool) $input->getOption('rollback');

        $contracts = $set->all();
        if (is_string($only) && $only !== '') {
            $contracts = array_values(array_filter(
                $contracts,
                static fn ($contract): bool => $contract->id === $only,
            ));
            if ($contracts === []) {
                $output->writeln('<error>No translation contract with id: ' . $only . '</error>');

                return Command::INVALID;
            }
        }

        // Rollback reverses application order. With independent contracts it makes no practical
        // difference today, but it is the order a reader expects and it stays correct if an
        // ordering dependency is ever introduced.
        if ($rollback) {
            $contracts = array_reverse($contracts);
        }

        foreach ($contracts as $contract) {
            $result = $rollback
                ? $migration->rollback($contract, $store)
                : $migration->forward($contract, $store);

            $output->writeln(sprintf(
                '<info>%s: %s; definitions changed: %d; target cons_id: %s</info>',
                $contract->id,
                $result->action,
                $result->definitionsChanged,
                $result->targetId === null ? 'none' : (string) $result->targetId,
            ));
        }

        return Command::SUCCESS;
    }
}
