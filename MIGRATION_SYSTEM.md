# AxionPHP Migration System

## 🎯 Overview

AxionPHP now includes a complete Laravel-like migration system that allows you to manage your database schema with version control. The migration system provides:

- **Migration Creation**: Generate migration files with proper structure
- **Migration Execution**: Run pending migrations in order
- **Migration Rollback**: Rollback migrations in batches
- **Migration Status**: Check which migrations have been run
- **Schema Builder**: Full Laravel Eloquent schema builder support

## 📁 File Structure

```
axionphp/
├── core/
│   ├── Migration/
│   │   ├── Migration.php              # Base migration class
│   │   ├── MigrationRepository.php    # Migration tracking
│   │   ├── Migrator.php              # Migration runner
│   │   └── MigrationCreator.php      # Migration file creator
│   └── Console/
│       ├── MigrateCommand.php        # Run migrations
│       ├── MigrateRollbackCommand.php # Rollback migrations
│       ├── MigrateStatusCommand.php   # Migration status
│       ├── MigrateStatusDemoCommand.php # Demo status (no DB)
│       └── MakeMigrationCommand.php   # Create migrations
├── database/
│   └── migrations/                   # Migration files directory
│       ├── 2025_06_15_062234_create_users_table.php
│       ├── 2025_06_15_062305_create_posts_table.php
│       └── 2025_06_15_062331_add_avatar_to_users_table.php
└── axion                            # CLI application
```

## 🚀 Usage Examples

### Creating Migrations

```bash
# Create a new table
php axion make:migration create_users_table --create=users

# Modify existing table
php axion make:migration add_avatar_to_users_table --table=users

# Generic migration
php axion make:migration update_user_permissions
```

### Running Migrations

```bash
# Run all pending migrations
php axion migrate

# Check migration status (requires database)
php axion migrate:status

# Check migration status (demo mode - no database)
php axion migrate:status:demo

# Rollback last batch
php axion migrate:rollback

# Rollback multiple batches
php axion migrate:rollback --step=3
```

### Composer Scripts

```bash
composer migrate              # Run migrations
composer migrate:rollback     # Rollback migrations
composer migrate:status       # Check migration status
composer make:migration       # Create new migration
```

## 📝 Migration Examples

### Creating a Table

```php
<?php

use Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('users');
    }
}
```

### Modifying a Table

```php
<?php

use Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAvatarToUsersTable extends Migration
{
    public function up(): void
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->text('bio')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'bio']);
        });
    }
}
```

### Complex Migration with Foreign Keys

```php
<?php

use Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('slug')->unique();
            $table->boolean('published')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('posts');
    }
}
```

## 🔧 Schema Builder Methods

The migration system supports all Laravel schema builder methods:

### Column Types
- `id()`, `bigIncrements()`, `increments()`
- `string()`, `text()`, `mediumText()`, `longText()`
- `integer()`, `bigInteger()`, `smallInteger()`, `tinyInteger()`
- `boolean()`, `decimal()`, `double()`, `float()`
- `date()`, `dateTime()`, `timestamp()`, `time()`
- `json()`, `jsonb()`, `binary()`
- `enum()`, `set()`

### Column Modifiers
- `nullable()`, `default()`, `unsigned()`
- `unique()`, `index()`, `primary()`
- `after()`, `first()`, `comment()`
- `autoIncrement()`, `charset()`, `collation()`

### Foreign Keys
- `foreign()`, `references()`, `on()`
- `foreignId()`, `constrained()`
- `onDelete()`, `onUpdate()`
- `cascadeOnDelete()`, `restrictOnDelete()`

### Indexes
- `index()`, `unique()`, `primary()`
- `spatialIndex()`, `fullText()`
- `dropIndex()`, `dropUnique()`, `dropPrimary()`

## 🎯 Key Features

1. **Laravel Compatible**: Uses the same syntax and methods as Laravel migrations
2. **Batch Tracking**: Migrations are tracked in batches for easy rollback
3. **Automatic Timestamps**: Migration files include timestamps for ordering
4. **Class Name Generation**: Automatic conversion from file names to class names
5. **Schema Builder Integration**: Full access to Laravel's schema builder
6. **CLI Integration**: Symfony Console commands for easy management
7. **Demo Mode**: Test migrations without database connection

## 🔄 Migration Workflow

1. **Create Migration**: `php axion make:migration create_table_name --create=table_name`
2. **Edit Migration**: Add your schema changes in the `up()` and `down()` methods
3. **Run Migration**: `php axion migrate`
4. **Check Status**: `php axion migrate:status:demo`
5. **Rollback if Needed**: `php axion migrate:rollback`

## 📋 Migration Repository

The system automatically creates a `migrations` table to track:
- Migration name
- Batch number
- Execution timestamp

This allows for:
- Preventing duplicate migrations
- Batch rollbacks
- Migration status tracking
- Proper ordering of migrations

## 🎉 Benefits

- **Version Control**: Database schema changes are version controlled
- **Team Collaboration**: Consistent database structure across environments
- **Rollback Safety**: Easy rollback of problematic migrations
- **Environment Consistency**: Same schema across development, staging, and production
- **Laravel Familiarity**: Uses familiar Laravel migration syntax

## 🎉 Conclusion

The migration system is now fully functional and ready for use! It provides the same developer experience as Laravel migrations while being integrated into the lightweight AxionPHP framework.
