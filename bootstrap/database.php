<?php

use Illuminate\Database\Capsule\Manager as Capsule;

// Initialize Eloquent ORM
$capsule = new Capsule;

$dbConnection = $_ENV['DB_CONNECTION'] ?? 'sqlite';

if ($dbConnection === 'sqlite') {
    $dbPath = __DIR__ . '/../' . ($_ENV['DB_DATABASE'] ?? 'database.sqlite');
    $capsule->addConnection([
        'driver' => 'sqlite',
        'database' => $dbPath,
        'prefix' => '',
    ]);
} else {
    $capsule->addConnection([
        'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'phpgo',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ]);
}

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

// Create users table if it doesn't exist (for development)
if ($_ENV['APP_ENV'] === 'development') {
    try {
        $capsule->schema()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    } catch (Exception $e) {
        // Table already exists or other error - ignore in development
    }
}
