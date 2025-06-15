<?php

namespace Core\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateStatusDemoCommand extends Command
{
    protected static $defaultName = 'migrate:status:demo';
    protected static $defaultDescription = 'Show migration status (demo - no database required)';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $io->title('Migration Status (Demo Mode)');

            $migrationPath = __DIR__ . '/../../database/migrations';
            
            if (!is_dir($migrationPath)) {
                $io->info('No migrations directory found.');
                return Command::SUCCESS;
            }

            $files = glob($migrationPath . '/*.php');
            
            if (empty($files)) {
                $io->info('No migrations found.');
                return Command::SUCCESS;
            }

            $rows = [];
            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $rows[] = [
                    $filename,
                    '<comment>Pending</comment> (Demo mode - no database connection)'
                ];
            }

            $io->table(['Migration', 'Status'], $rows);
            
            $io->note('This is demo mode. To run actual migrations, configure your database connection in .env');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Failed to get migration status: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
