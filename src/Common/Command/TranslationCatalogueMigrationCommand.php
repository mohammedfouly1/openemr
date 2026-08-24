<?php

/** @package OpenEMR */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Translation\QueryUtilsTranslationCatalogueStore;
use OpenEMR\Common\Translation\TranslationCatalogueContract;
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
            ->addOption('rollback', null, InputOption::VALUE_NONE, 'Restore the exact journalled pre-migration state');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $contract = TranslationCatalogueContract::fromFile(
            dirname(__DIR__, 3) . '/contrib/util/language_translations/contracts/database-upgrade.json',
        );
        $migration = new TranslationCatalogueMigration();
        $store = new QueryUtilsTranslationCatalogueStore();
        $result = $input->getOption('rollback')
            ? $migration->rollback($contract, $store)
            : $migration->forward($contract, $store);

        $output->writeln(sprintf(
            '<info>%s; definitions changed: %d; target cons_id: %s</info>',
            $result->action,
            $result->definitionsChanged,
            $result->targetId === null ? 'none' : (string) $result->targetId,
        ));
        return Command::SUCCESS;
    }
}
