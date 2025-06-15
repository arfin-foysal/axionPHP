<?php

namespace Core\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class StartCommand extends Command
{
    protected static $defaultName = 'start';
    protected static $defaultDescription = 'Start the AxionPHP development server';

    protected function configure(): void
    {
        $this->addOption(
            'port',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Port to run the server on',
            '8000'
        );

        $this->addOption(
            'host',
            null,
            InputOption::VALUE_OPTIONAL,
            'Host to bind the server to',
            'localhost'
        );

        $this->addOption(
            'public',
            null,
            InputOption::VALUE_OPTIONAL,
            'Public directory path',
            'public'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $port = $input->getOption('port');
        $host = $input->getOption('host');
        $publicDir = $input->getOption('public');

        // Check if public directory exists
        if (!is_dir($publicDir)) {
            $io->error("Public directory '{$publicDir}' does not exist.");
            return Command::FAILURE;
        }

        // Check if index.php exists
        if (!file_exists($publicDir . '/index.php')) {
            $io->error("Entry point '{$publicDir}/index.php' does not exist.");
            return Command::FAILURE;
        }

        $serverAddress = "{$host}:{$port}";
        $url = "http://{$serverAddress}";

        $io->title('AxionPHP Development Server');
        $io->info("Starting server on {$url}");
        $io->info("Document root: " . realpath($publicDir));
        $io->info("Press Ctrl+C to stop the server");
        $io->newLine();

        // Try to find an available port if the specified one is in use
        $originalPort = $port;
        $maxAttempts = 10;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            if ($this->isPortAvailable($host, $port)) {
                break;
            }

            $attempts++;
            $port = $originalPort + $attempts;
            
            if ($attempts === 1) {
                $io->warning("Port {$originalPort} is already in use, trying port {$port}...");
            }
        }

        if ($attempts >= $maxAttempts) {
            $io->error("Could not find an available port after {$maxAttempts} attempts.");
            return Command::FAILURE;
        }

        if ($port !== $originalPort) {
            $serverAddress = "{$host}:{$port}";
            $url = "http://{$serverAddress}";
            $io->success("Found available port: {$port}");
            $io->info("Server URL: {$url}");
            $io->newLine();
        }

        // Start the PHP built-in server
        $command = [
            PHP_BINARY,
            '-S',
            $serverAddress,
            '-t',
            $publicDir
        ];

        $process = new Process($command);
        $process->setTimeout(null);

        try {
            $process->run(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Failed to start server: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function isPortAvailable(string $host, int $port): bool
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, 1);
        
        if ($connection) {
            fclose($connection);
            return false; // Port is in use
        }
        
        return true; // Port is available
    }
}
