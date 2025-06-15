# AxionPHP — A Lightweight PHP Micro Framework with JWT API Support

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Framework](https://img.shields.io/badge/Framework-AxionPHP-orange)

AxionPHP is a lightweight, high-performance PHP micro-framework built using Symfony Components and powered by Laravel's Eloquent ORM. Inspired by Express.js and designed for modern API development, AxionPHP is fully customizable and extensible — making it perfect for building RESTful APIs and web applications.

## 🎯 Key Features

### ⚙️ Core Architecture
- **Symfony Components**: Built on Symfony Routing, HTTP Foundation, Dotenv, and Event Dispatcher
- **PSR-4 Compliant**: Fully Composer-based with PSR-4 autoloading
- **Modular Structure**: Clean, modular, and scalable architecture

### 🧠 Developer Experience
- **Environment Configuration**: `.env` based configuration for different environments
- **Debug Mode**: Developer-friendly error handling with detailed stack traces
- **Simple Structure**: Easy to understand and extend

### 🔄 Routing & Middleware
- **RESTful Routing**: Symfony Router-based routing system
- **Middleware Pipeline**: Request/response lifecycle management
- **Route Grouping**: Support for route prefixes and middleware groups
- **Resource Routes**: Automatic CRUD route generation

### 🛢️ ORM & Database
- **Laravel Eloquent**: Elegant database interaction with Eloquent ORM
- **Multiple Databases**: Support for MySQL, SQLite, PostgreSQL
- **Laravel-like Migrations**: Full migration system with CLI commands
- **Schema Builder**: Powerful database schema management

### 🔐 JWT Authentication
- **Built-in JWT**: Complete JWT implementation using firebase/php-jwt
- **Secure Endpoints**: Login, logout, profile, and refresh token endpoints
- **Middleware Protection**: JWT middleware for protected routes
- **Token Management**: Token verification, expiration, and refresh handling

### 📦 API-Ready
- **JSON Responses**: Consistent JSON API response format
- **Status Codes**: Proper HTTP status code handling
- **CORS Support**: Built-in CORS headers for API consumption

## 🚀 Quick Start

### Installation

1. **Clone or create your project:**
```bash
git clone <your-repo> axionphp-app
cd axionphp-app
```

2. **Install dependencies:**
```bash
composer install
```

3. **Configure environment:**
```bash
cp .env .env.local
# Edit .env.local with your database credentials
```

4. **Start development server:**
```bash
# Using AxionPHP CLI (recommended)
php axion start
# or
php axion start:dev

# Using Composer scripts
composer start
# or
composer serve

# Manual PHP server
php -S localhost:8000 -t public
```

### Basic Usage

#### 1. Define Routes
```php
// routes/web.php
$router = app()->getRouter();

$router->get('/', 'App\Controllers\HomeController::index');
$router->post('/users', 'App\Controllers\UserController::store');
```

#### 2. Create Controllers
```php
// app/Controllers/UserController.php
<?php
namespace App\Controllers;

class UserController extends BaseController
{
    public function index()
    {
        return $this->success(['users' => User::all()]);
    }
}
```

#### 3. Use Models
```php
// app/Models/Post.php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id'];
}
```

## 📚 API Documentation

### Authentication Endpoints

#### Register User
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
}
```

#### Login User
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Get Profile (Protected)
```http
GET /api/auth/profile
Authorization: Bearer <jwt-token>
```

#### Refresh Token
```http
POST /api/auth/refresh
Authorization: Bearer <jwt-token>
```

### Response Format
```json
{
    "success": true,
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

## 🏗️ Project Structure

```
axionphp/
├── app/
│   ├── Controllers/          # Application controllers
│   │   ├── BaseController.php
│   │   ├── AuthController.php
│   │   └── HomeController.php
│   ├── Models/              # Eloquent models
│   │   └── User.php
│   └── Middleware/          # Custom middleware
├── bootstrap/               # Framework bootstrap
│   ├── app.php             # Application setup
│   └── database.php        # Database configuration
├── core/                   # Framework core
│   ├── Router.php          # Routing system
│   ├── JwtHelper.php       # JWT utilities
│   └── JwtMiddleware.php   # JWT middleware
├── routes/                 # Route definitions
│   ├── web.php            # Web routes
│   └── api.php            # API routes
├── public/                # Public directory
│   └── index.php          # Entry point
├── .env                   # Environment configuration
├── composer.json          # Dependencies
└── README.md             # Documentation
```

## 🔧 Configuration

### Environment Variables
```env
# Application
APP_NAME=AxionPHP
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=axionphp
DB_USERNAME=root
DB_PASSWORD=

# JWT
JWT_SECRET=your-super-secret-jwt-key
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600
```

## 🗄️ Database Migrations

AxionPHP includes a powerful Laravel-like migration system for managing your database schema.

### Creating Migrations

```bash
# Create a new table migration
php axion make:migration create_users_table --create=users

# Create a table modification migration
php axion make:migration add_avatar_to_users_table --table=users

# Create a blank migration
php axion make:migration update_user_permissions
```

### Running Migrations

```bash
# Run all pending migrations
php axion migrate

# Check migration status
php axion migrate:status

# Rollback the last batch of migrations
php axion migrate:rollback

# Rollback multiple batches
php axion migrate:rollback --step=3
```

### Migration Examples

#### Creating a Table
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
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('users');
    }
}
```

#### Modifying a Table
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
            $table->text('bio')->nullable();
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

### Available Schema Methods

The migration system supports all Laravel schema builder methods:

- **Column Types**: `string()`, `text()`, `integer()`, `bigInteger()`, `boolean()`, `date()`, `datetime()`, `timestamp()`, `json()`, etc.
- **Column Modifiers**: `nullable()`, `default()`, `unique()`, `index()`, `after()`, `first()`, etc.
- **Foreign Keys**: `foreignId()`, `constrained()`, `onDelete()`, `onUpdate()`
- **Indexes**: `index()`, `unique()`, `primary()`, `foreign()`

### Composer Scripts

You can also use Composer scripts for convenience:

```bash
# Server commands
composer start                # Start development server
composer start:dev            # Start development server (alias)
composer serve                # Start server (legacy)

# Migration commands
composer migrate              # Run migrations
composer migrate:rollback     # Rollback migrations
composer migrate:status       # Check migration status
composer make:migration       # Create new migration (requires name argument)
```

## 🧪 Testing

Run the built-in development server:
```bash
composer serve
```

Test the API endpoints:
```bash
# Test framework info
curl http://localhost:8000/

# Test health check
curl http://localhost:8000/health

# Test user registration (demo - in-memory)
curl -X POST http://localhost:8000/api/demo/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'
```

## 📝 License

This project is licensed under the MIT License.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For support and questions, please open an issue on GitHub.

---

**AxionPHP** - Built with ❤️ for modern PHP development
