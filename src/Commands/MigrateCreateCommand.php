<?php

namespace Onlyoung4u\BunnyMigration\Commands;

use Illuminate\Database\Console\Migrations\TableGuesser;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrateCreateCommand extends AbstractCommand
{
    protected static string $defaultName = 'migrate:create';
    protected static string $defaultDescription = 'Create a new migration file';

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the migration');
        $this->addOption('create', null, InputOption::VALUE_OPTIONAL, 'The table to be created');
        $this->addOption('table', null, InputOption::VALUE_OPTIONAL, 'The table to migrate');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bootstrap($input, $output);

        $creator = new MigrationCreator(
            new Filesystem,
            __DIR__ . '/../../stubs'
        );

        $name = Str::snake(trim($input->getArgument('name')));

        $table = $input->getOption('table');
        $create = $input->getOption('create') ?: false;

        if (!$table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        if (!$table) {
            [$table, $create] = TableGuesser::guess($name);
        }

        $filePath = $creator->create($name, $this->migrationPath(), $table, $create);

        $output->writeln("<info>Created Migration:</info> {$filePath}");

        return self::SUCCESS;
    }
}