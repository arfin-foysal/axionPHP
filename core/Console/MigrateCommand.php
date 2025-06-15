<?php

namespace Core\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Migration\Migrator;

class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';
    protected static $defaultDescription = 'Run database migrations';

    private Migrator $migrator;

    public function __construct()
    {
        parent::__construct();
        $this->migrator = new Migrator();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $io->title('Running Migrations');

            $migrations = $this->migrator->run();

            if (empty($migrations)) {
                $io->success('Nothing to migrate.');
                return Command::SUCCESS;
            }

            $io->listing($migrations);
            $io->success(sprintf('Migrated %d migration(s).', count($migrations)));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Migration failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
