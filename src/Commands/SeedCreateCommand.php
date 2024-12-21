<?php

namespace Onlyoung4u\BunnyMigration\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class SeedCreateCommand extends AbstractCommand
{
    protected static string $defaultName = 'seed:create';
    protected static string $defaultDescription = 'Create a new seeder class';

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the seeder');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bootstrap($input, $output);

        $name = Str::studly($input->getArgument('name'));

        if (!str_ends_with($name, 'Seeder')) $name .= 'Seeder';

        $path = $this->seedPath();

        if (!is_dir($path)) mkdir($path, 0755, true);

        $filePath = $path . '/' . $name . '.php';

        if (file_exists($filePath)) {
            $output->writeln("<error>Seeder {$name} already exists!</error>");

            return self::FAILURE;
        }

        file_put_contents($filePath, $this->getStub($name));

        $output->writeln("<info>Seeder {$name} created successfully.</info>");

        return self::SUCCESS;
    }

    protected function getStub(string $name): string
    {
        return <<<EOF
<?php

namespace database\seeds;

use support\Db;

class {$name}
{
    private string \$database;
    
    public function __construct(string \$database)
    {
        \$this->database = \$database;
    }
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Db::connection(\$this->database)
        //  ->table('table_name')
        //  ->insert([]);
    }
}
EOF;
    }
}