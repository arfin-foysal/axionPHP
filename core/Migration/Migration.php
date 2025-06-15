<?php

namespace Core\Migration;

use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Capsule\Manager as DB;

abstract class Migration
{
    /**
     * The schema builder instance.
     */
    protected Builder $schema;

    public function __construct()
    {
        $this->schema = DB::schema();
    }

    /**
     * Run the migrations.
     */
    abstract public function up(): void;

    /**
     * Reverse the migrations.
     */
    abstract public function down(): void;

    /**
     * Get the migration name.
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Get the migration file name.
     */
    public function getFileName(): string
    {
        $reflection = new \ReflectionClass($this);
        return basename($reflection->getFileName(), '.php');
    }
}
