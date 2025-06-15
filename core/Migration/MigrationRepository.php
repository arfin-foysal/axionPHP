<?php

namespace Core\Migration;

use Illuminate\Database\Capsule\Manager as DB;
use Exception;

class MigrationRepository
{
    private string $table = 'migrations';

    public function __construct()
    {
        $this->createRepository();
    }

    /**
     * Create the migration repository table.
     */
    public function createRepository(): void
    {
        if (!DB::schema()->hasTable($this->table)) {
            DB::schema()->create($this->table, function ($table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Get all migrations that have been run.
     */
    public function getRan(): array
    {
        return DB::table($this->table)
            ->orderBy('batch')
            ->orderBy('migration')
            ->pluck('migration')
            ->toArray();
    }

    /**
     * Get migrations for a specific batch.
     */
    public function getMigrationsByBatch(int $batch): array
    {
        return DB::table($this->table)
            ->where('batch', $batch)
            ->orderBy('migration')
            ->pluck('migration')
            ->toArray();
    }

    /**
     * Get the last migration batch number.
     */
    public function getLastBatchNumber(): int
    {
        return DB::table($this->table)->max('batch') ?? 0;
    }

    /**
     * Get the next migration batch number.
     */
    public function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * Log that a migration was run.
     */
    public function log(string $migration, int $batch): void
    {
        DB::table($this->table)->insert([
            'migration' => $migration,
            'batch' => $batch,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Remove a migration from the log.
     */
    public function delete(string $migration): void
    {
        DB::table($this->table)->where('migration', $migration)->delete();
    }

    /**
     * Get all migration records.
     */
    public function getAllMigrations(): array
    {
        return DB::table($this->table)
            ->orderBy('batch')
            ->orderBy('migration')
            ->get()
            ->toArray();
    }

    /**
     * Get migrations for the last batch.
     */
    public function getLastBatch(): array
    {
        $lastBatch = $this->getLastBatchNumber();
        
        if ($lastBatch === 0) {
            return [];
        }

        return $this->getMigrationsByBatch($lastBatch);
    }

    /**
     * Delete all migration records.
     */
    public function deleteRepository(): void
    {
        DB::schema()->dropIfExists($this->table);
    }
}
