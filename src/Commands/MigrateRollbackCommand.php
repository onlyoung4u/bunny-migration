<?php

namespace Onlyoung4u\BunnyMigration\Commands;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MigrateRollbackCommand extends AbstractCommand
{
    protected static string $defaultName = 'migrate:rollback';
    protected static string $defaultDescription = 'Rollback the last database migration';

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production');
        $this->addOption('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run');
        $this->addOption('step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted');
        $this->addOption('batch', null, InputOption::VALUE_REQUIRED, 'The batch of migrations (identified by their batch number) to be reverted');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bootstrap($input, $output);

        if (!$this->confirmToProceed()) return self::FAILURE;

        [, $migrator] = $this->getRepositoryAndMigrator();

        if (!$migrator->repositoryExists()) {
            $this->output->writeln('<error>Migration table not found.</error>');

            return self::FAILURE;
        }

        $migrator->setOutput(new OutputStyle($input, $output))
            ->rollback([$this->migrationPath()], [
                'pretend' => $input->getOption('pretend'),
                'step' => $input->getOption('step'),
                'batch' => $input->getOption('batch'),
            ]);

        return self::SUCCESS;
    }
}