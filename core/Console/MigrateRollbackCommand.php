<?php

namespace Core\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Migration\Migrator;

class MigrateRollbackCommand extends Command
{
    protected static $defaultName = 'migrate:rollback';
    protected static $defaultDescription = 'Rollback database migrations';

    private Migrator $migrator;

    public function __construct()
    {
        parent::__construct();
        $this->migrator = new Migrator();
    }

    protected function configure(): void
    {
        $this->addOption(
            'step',
            's',
            InputOption::VALUE_OPTIONAL,
            'Number of migration batches to rollback',
            1
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $steps = (int) $input->getOption('step');
            
            $io->title('Rolling Back Migrations');

            $migrations = $this->migrator->rollback($steps);

            if (empty($migrations)) {
                $io->success('Nothing to rollback.');
                return Command::SUCCESS;
            }

            $io->listing($migrations);
            $io->success(sprintf('Rolled back %d migration(s).', count($migrations)));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Rollback failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
