<?php

namespace Onlyoung4u\BunnyMigration\Commands;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Filesystem\Filesystem;
use Onlyoung4u\BunnyMigration\Migrations\Migrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class AbstractCommand extends Command
{
    protected InputInterface $input;
    protected OutputInterface $output;

    /**
     * Bootstrap
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function bootstrap(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Configure
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption('database', '-d', InputOption::VALUE_OPTIONAL, 'The database connection to use', 'default');
    }

    /**
     * Get the configuration value
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        return config("plugin.onlyoung4u.bunny-migration.app.{$key}", $default);
    }

    /**
     * Get the environment
     *
     * @return string
     */
    protected function environment(): string
    {
        return $this->getConfig('environment', 'production');
    }

    /**
     * Get the migration table
     *
     * @return string
     */
    protected function migrationTable(): string
    {
        return $this->getConfig('migrations_table', 'migrations');
    }

    /**
     * Get the migration path
     *
     * @return string
     */
    protected function migrationPath(): string
    {
        return $this->getConfig('migrations_path', base_path('database/migrations'));
    }

    /**
     * Get the seed path
     *
     * @return string
     */
    protected function seedPath(): string
    {
        return $this->getConfig('seeds_path', base_path('database/seeds'));
    }

    /**
     * Get the database connection
     *
     * @return string
     */
    protected function getDatabase(): string
    {
        return $this->input->getOption('database');
    }

    /**
     * Get the database connection
     *
     * @return DatabaseManager
     */
    protected function getDatabaseManager(): DatabaseManager
    {
        $database = $this->getDatabase();

        if ($database === 'default') {
            $key = config('database.default');
        } else {
            $key = $database;
        }

        $capsule = new Manager();

        $capsule->addConnection(config("database.connections.{$key}"), $database);

        return $capsule->getDatabaseManager();
    }

    /**
     * Get the repository and migrator
     *
     * @return array [DatabaseMigrationRepository, Migrator]
     */
    protected function getRepositoryAndMigrator(): array
    {
        $databaseManager = $this->getDatabaseManager();

        $repository = new DatabaseMigrationRepository($databaseManager, $this->migrationTable());
        $migrator = new Migrator($repository, $databaseManager, new Filesystem);

        $repository->setSource($this->getDatabase());
        $migrator->setConnection($this->getDatabase());

        return [$repository, $migrator];
    }

    /**
     * Confirm
     *
     * @param string $message
     * @return bool
     */
    public function confirm(string $message): bool
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($message, false);

        if (!$helper->ask($this->input, $this->output, $question)) {
            return false;
        }

        return true;
    }

    /**
     * Confirm to proceed
     *
     * @param string $warning
     * @return bool
     */
    protected function confirmToProceed(string $warning = 'Application In Production'): bool
    {
        $shouldConfirm = $this->environment() === 'production';

        if ($shouldConfirm) {
            if ($this->input->hasOption('force') && $this->input->getOption('force')) {
                return true;
            }

            $this->output->writeln("<comment>$warning</comment>\n");

            $confirmed = $this->confirm('Are you sure you want to run this command? ');

            if (!$confirmed) {
                $this->output->writeln('<comment>Command cancelled.</comment>');

                return false;
            }
        }

        return true;
    }

    /**
     * Table output
     *
     * @param array $headers
     * @param array $contents
     * @return void
     */
    protected function table(array $headers, array $contents): void
    {
        $table = new Table($this->output);

        $table->setHeaders($headers)
            ->setRows($contents)
            ->render();

        $this->output->write(PHP_EOL);
    }

    /**
     * Call command
     *
     * @param string $command
     * @param array $arguments
     * @return int
     * @throws CommandNotFoundException|ExceptionInterface
     */
    public function call(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;

        return $this->getApplication()
            ->find($command)
            ->run(
                new ArrayInput($arguments),
                $this->output
            );
    }
}