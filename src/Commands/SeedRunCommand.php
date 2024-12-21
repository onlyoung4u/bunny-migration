<?php

namespace Onlyoung4u\BunnyMigration\Commands;

use support\Db;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SeedRunCommand extends AbstractCommand
{
    protected static string $defaultName = 'seed:run';
    protected static string $defaultDescription = 'Seed the database with records';

    protected function configure(): void
    {
        $this->addOption('class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder');
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bootstrap($input, $output);

        if (!$this->confirmToProceed()) return self::FAILURE;

        $db = Db::connection($this->getDatabase());

        try {
            $db->beginTransaction();

            if ($class = $input->getOption('class')) {
                $this->runSeeder('database\\seeds\\' . $class, $output);
            } else {
                $this->runAllSeeders($output);
            }

            $db->commit();

            $output->writeln("\n<info>Database seeding completed successfully.</info>");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $db->rollBack();

            $output->writeln("\n<error>Database seeding failed: {$e->getMessage()}</error>");

            return self::FAILURE;
        }
    }

    protected function runSeeder(string $class, OutputInterface $output): void
    {
        if (!class_exists($class)) {
            throw new \RuntimeException("Seeder class '$class' does not exist.");
        }

        $output->writeln("<info>Running seeder: {$class}</info>");

        $seeder = new $class($this->getDatabase());

        $seeder->run();
    }

    protected function runAllSeeders(OutputInterface $output): void
    {
        $seederPath = $this->seedPath();

        if (!is_dir($seederPath)) {
            throw new \RuntimeException("Seeders directory does not exist.");
        }

        $files = glob($seederPath . '/*Seeder.php');

        if (empty($files)) {
            throw new \RuntimeException("No seeders found in {$seederPath}.");
        }

        foreach ($files as $file) {
            $className = 'database\\seeds\\' . basename($file, '.php');

            $reflectionClass = new \ReflectionClass($className);
            if ($reflectionClass->isAbstract() || $reflectionClass->isInterface()) {
                continue;
            }

            $this->runSeeder($className, $output);
        }
    }
}