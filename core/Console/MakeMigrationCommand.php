<?php

namespace Core\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Migration\MigrationCreator;

class MakeMigrationCommand extends Command
{
    protected static $defaultName = 'make:migration';
    protected static $defaultDescription = 'Create a new migration file';

    private MigrationCreator $creator;

    public function __construct()
    {
        parent::__construct();
        // MigrationCreator doesn't need database connection
        $this->creator = new MigrationCreator();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the migration'
        );

        $this->addOption(
            'create',
            null,
            InputOption::VALUE_OPTIONAL,
            'The table to be created'
        );

        $this->addOption(
            'table',
            null,
            InputOption::VALUE_OPTIONAL,
            'The table to migrate'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $name = $input->getArgument('name');
            $table = $input->getOption('table');
            $create = $input->getOption('create');

            if ($create) {
                $table = $create;
                $isCreate = true;
            } else {
                $isCreate = false;
            }

            $path = $this->creator->create($name, $table, $isCreate);

            $io->success("Migration created: " . basename($path));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to create migration: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
