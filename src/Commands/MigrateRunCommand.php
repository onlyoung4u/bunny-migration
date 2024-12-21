<?php

namespace Onlyoung4u\BunnyMigration\Commands;

use Illuminate\Console\OutputStyle;
use Onlyoung4u\BunnyMigration\Migrations\Migrator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MigrateRunCommand extends AbstractCommand
{
    protected static string $defaultName = 'migrate:run';
    protected static string $defaultDescription = 'Run the database migrations';

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production');
        $this->addOption('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run');
        $this->addOption('step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bootstrap($input, $output);

        if (!$this->confirmToProceed()) return self::FAILURE;

        [$repository, $migrator] = $this->getRepositoryAndMigrator();

        if (!$migrator->repositoryExists()) $repository->createRepository();

        $migrator->setOutput(new OutputStyle($input, $output))
            ->run([$this->migrationPath()], [
                'pretend' => $input->getOption('pretend'),
                'step' => $input->getOption('step'),
            ]);

        return self::SUCCESS;
    }
}