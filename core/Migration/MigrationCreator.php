<?php

namespace Core\Migration;

use Symfony\Component\Filesystem\Filesystem;

class MigrationCreator
{
    private Filesystem $filesystem;
    private string $migrationPath;

    public function __construct(string $migrationPath = null)
    {
        $this->filesystem = new Filesystem();
        $this->migrationPath = $migrationPath ?? __DIR__ . '/../../database/migrations';
    }

    /**
     * Create a new migration file.
     */
    public function create(string $name, string $table = null, bool $create = false): string
    {
        $this->ensureMigrationDirectoryExists();

        $stub = $this->getStub($table, $create);
        $className = $this->getClassName($name);
        $filename = $this->getFilename($name);
        $path = $this->migrationPath . '/' . $filename . '.php';

        $stub = str_replace(
            ['{{class}}', '{{table}}'],
            [$className, $table ?? 'table_name'],
            $stub
        );

        $this->filesystem->dumpFile($path, $stub);

        return $path;
    }

    /**
     * Get the migration stub.
     */
    private function getStub(string $table = null, bool $create = false): string
    {
        if ($create) {
            return $this->getCreateStub();
        }

        if ($table) {
            return $this->getUpdateStub();
        }

        return $this->getBlankStub();
    }

    /**
     * Get the create table stub.
     */
    private function getCreateStub(): string
    {
        return <<<'STUB'
<?php

use Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class {{class}} extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema->create('{{table}}', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('{{table}}');
    }
}
STUB;
    }

    /**
     * Get the update table stub.
     */
    private function getUpdateStub(): string
    {
        return <<<'STUB'
<?php

use Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class {{class}} extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema->table('{{table}}', function (Blueprint $table) {
            // Add your columns here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema->table('{{table}}', function (Blueprint $table) {
            // Drop your columns here
        });
    }
}
STUB;
    }

    /**
     * Get the blank migration stub.
     */
    private function getBlankStub(): string
    {
        return <<<'STUB'
<?php

use Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class {{class}} extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add your migration logic here
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add your rollback logic here
    }
}
STUB;
    }

    /**
     * Get the class name for the migration.
     */
    private function getClassName(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    /**
     * Get the filename for the migration.
     */
    private function getFilename(string $name): string
    {
        return date('Y_m_d_His') . '_' . $name;
    }

    /**
     * Ensure the migration directory exists.
     */
    private function ensureMigrationDirectoryExists(): void
    {
        if (!$this->filesystem->exists($this->migrationPath)) {
            $this->filesystem->mkdir($this->migrationPath, 0755);
        }
    }
}
