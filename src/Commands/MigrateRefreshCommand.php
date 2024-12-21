<?php

namespace Onlyoung4u\BunnyMigration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MigrateRefreshCommand extends AbstractCommand
{
    protected static string $defaultName = 'migrate:refresh';
    protected static string $defaultDescription = 'Reset and re-run all migrations';

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production');
        $this->addOption('seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run');
        $this->addOption('seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bootstrap($input, $output);

        if (!$this->confirmToProceed()) return self::FAILURE;

        [$repository, $migrator] = $this->getRepositoryAndMigrator();

        if (!$migrator->repositoryExists()) $repository->createRepository();

        $this->call('migrate:reset', array_filter([
            '--force' => true,
            '--database' => $this->getDatabase(),
        ]));

        $this->call('migrate:run', array_filter([
            '--force' => true,
            '--database' => $this->getDatabase(),
        ]));

        if ($input->getOption('seed') || $input->getOption('seeder')) {
            $this->call('seed:run', array_filter([
                '--force' => true,
                '--database' => $this->getDatabase(),
                '--class' => $this->input->getOption('seeder'),
            ]));
        }

        return self::SUCCESS;
    }
}