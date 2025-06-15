<?php

namespace Core\Console;

class StartDevCommand extends StartCommand
{
    protected static $defaultName = 'start:dev';
    protected static $defaultDescription = 'Start the AxionPHP development server (alias for start)';

    protected function configure(): void
    {
        parent::configure();
        
        // Override the description for the dev command
        $this->setDescription('Start the AxionPHP development server with development settings');
    }
}
