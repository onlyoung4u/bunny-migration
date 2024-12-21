<?php

namespace Onlyoung4u\BunnyMigration\Commands;

use Illuminate\Support\Collection;
use Onlyoung4u\BunnyMigration\Migrations\Migrator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class MigrateStatusCommand extends AbstractCommand
{
    protected static string $defaultName = 'migrate:status';
    protected static string $defaultDescription = 'Show the status of each migration';

    protected Migrator $migrator;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bootstrap($input, $output);

        [, $migrator] = $this->getRepositoryAndMigrator();

        $this->migrator = $migrator;

        if (!$this->migrator->repositoryExists()) {
            $output->writeln('<error>Migration table not found.</error>');

            return self::FAILURE;
        }

        $ran = $this->migrator->getRepository()->getRan();
        $batches = $this->migrator->getRepository()->getMigrationBatches();

        $migrations = $this->getStatusFor($ran, $batches);

        if (count($migrations) > 0) {
            $this->table(['Migration', 'Ran?', 'Batch'], $migrations->toArray());
        } else {
            $output->writeln('<info>No migrations found.</info>');
        }

        return self::SUCCESS;
    }

    protected function getStatusFor(array $ran, array $batches): Collection
    {
        return (new Collection($this->getAllMigrationFiles()))
            ->map(function ($migration) use ($ran, $batches) {
                $migrationName = $this->migrator->getMigrationName($migration);

                $status = in_array($migrationName, $ran)
                    ? '<fg=green;options=bold>Yes</>'
                    : '<fg=yellow;options=bold>No</>';

                $batch = $batches[$migrationName] ?? null;

                return [$migrationName, $status, $batch];
            });
    }

    protected function getAllMigrationFiles(): array
    {
        return $this->migrator->getMigrationFiles([$this->migrationPath()]);
    }
}