<?php

namespace Onlyoung4u\BunnyMigration\Commands;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MigrateResetCommand extends AbstractCommand
{
    protected static string $defaultName = 'migrate:reset';
    protected static string $defaultDescription = 'Rollback all database migrations';

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production');
        $this->addOption('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run');

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
            ->reset([$this->migrationPath()], $input->getOption('pretend'));

        return self::SUCCESS;
    }
}