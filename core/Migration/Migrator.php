<?php

namespace Core\Migration;

use Symfony\Component\Filesystem\Filesystem;
use Exception;

class Migrator
{
    private MigrationRepository $repository;
    private Filesystem $filesystem;
    private string $migrationPath;

    public function __construct(string $migrationPath = null)
    {
        $this->repository = new MigrationRepository();
        $this->filesystem = new Filesystem();
        $this->migrationPath = $migrationPath ?? __DIR__ . '/../../database/migrations';
    }

    /**
     * Run all pending migrations.
     */
    public function run(): array
    {
        $migrations = $this->getPendingMigrations();
        $batch = $this->repository->getNextBatchNumber();
        $ran = [];

        foreach ($migrations as $migration) {
            $this->runMigration($migration, $batch);
            $ran[] = $migration;
        }

        return $ran;
    }

    /**
     * Rollback the last batch of migrations.
     */
    public function rollback(int $steps = 1): array
    {
        $rolledBack = [];

        for ($i = 0; $i < $steps; $i++) {
            $batch = $this->repository->getLastBatch();
            
            if (empty($batch)) {
                break;
            }

            foreach (array_reverse($batch) as $migration) {
                $this->rollbackMigration($migration);
                $rolledBack[] = $migration;
            }
        }

        return $rolledBack;
    }

    /**
     * Get all migration files.
     */
    public function getMigrationFiles(): array
    {
        if (!$this->filesystem->exists($this->migrationPath)) {
            return [];
        }

        $files = glob($this->migrationPath . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Get pending migrations.
     */
    public function getPendingMigrations(): array
    {
        $ran = $this->repository->getRan();
        $files = $this->getMigrationFiles();

        return array_diff($files, $ran);
    }

    /**
     * Run a single migration.
     */
    private function runMigration(string $migration, int $batch): void
    {
        $instance = $this->loadMigration($migration);
        
        try {
            $instance->up();
            $this->repository->log($migration, $batch);
        } catch (Exception $e) {
            throw new Exception("Migration failed: {$migration}. Error: " . $e->getMessage());
        }
    }

    /**
     * Rollback a single migration.
     */
    private function rollbackMigration(string $migration): void
    {
        $instance = $this->loadMigration($migration);
        
        try {
            $instance->down();
            $this->repository->delete($migration);
        } catch (Exception $e) {
            throw new Exception("Rollback failed: {$migration}. Error: " . $e->getMessage());
        }
    }

    /**
     * Load a migration instance.
     */
    private function loadMigration(string $migration): Migration
    {
        $file = $this->migrationPath . '/' . $migration . '.php';
        
        if (!$this->filesystem->exists($file)) {
            throw new Exception("Migration file not found: {$file}");
        }

        require_once $file;

        // Extract class name from migration file name
        $className = $this->getMigrationClassName($migration);
        
        if (!class_exists($className)) {
            throw new Exception("Migration class not found: {$className}");
        }

        return new $className();
    }

    /**
     * Get migration class name from file name.
     */
    private function getMigrationClassName(string $migration): string
    {
        // Remove timestamp prefix and convert to PascalCase
        $parts = explode('_', $migration);
        
        // Remove the first part (timestamp)
        array_shift($parts);
        
        // Convert to PascalCase
        $className = '';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }

        return $className;
    }

    /**
     * Get migration status.
     */
    public function getStatus(): array
    {
        $files = $this->getMigrationFiles();
        $ran = $this->repository->getRan();
        $status = [];

        foreach ($files as $file) {
            $status[] = [
                'migration' => $file,
                'status' => in_array($file, $ran) ? 'Ran' : 'Pending'
            ];
        }

        return $status;
    }

    /**
     * Reset all migrations.
     */
    public function reset(): array
    {
        $migrations = array_reverse($this->repository->getRan());
        $rolledBack = [];

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration);
            $rolledBack[] = $migration;
        }

        return $rolledBack;
    }
}
