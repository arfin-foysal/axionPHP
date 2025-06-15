<?php

namespace Core\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Migration\Migrator;

class MigrateStatusCommand extends Command
{
    protected static $defaultName = 'migrate:status';
    protected static $defaultDescription = 'Show migration status';

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
            $io->title('Migration Status');

            $status = $this->migrator->getStatus();

            if (empty($status)) {
                $io->info('No migrations found.');
                return Command::SUCCESS;
            }

            $rows = [];
            foreach ($status as $migration) {
                $rows[] = [
                    $migration['migration'],
                    $migration['status'] === 'Ran' ? '<info>Ran</info>' : '<comment>Pending</comment>'
                ];
            }

            $io->table(['Migration', 'Status'], $rows);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Failed to get migration status: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
