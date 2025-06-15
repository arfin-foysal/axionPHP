<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController extends BaseController
{
    public function index(): JsonResponse
    {
        return $this->success([
            'framework' => 'AxionPHP',
            'version' => '1.0.0',
            'description' => 'A Lightweight PHP Micro Framework with JWT API Support',
            'features' => [
                'Symfony Components',
                'Laravel Eloquent ORM',
                'JWT Authentication',
                'RESTful Routing',
                'Middleware Pipeline',
                'Environment Configuration',
                'JSON API Responses'
            ],
            'endpoints' => [
                'GET /' => 'Framework information',
                'POST /api/auth/register' => 'User registration',
                'POST /api/auth/login' => 'User login',
                'GET /api/auth/profile' => 'Get user profile (requires JWT)',
                'POST /api/auth/logout' => 'User logout',
                'POST /api/auth/refresh' => 'Refresh JWT token'
            ]
        ], 'Welcome to AxionPHP Framework');
    }

    public function health(): JsonResponse
    {
        return $this->success([
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => $_ENV['APP_ENV'] ?? 'production',
            'debug' => $_ENV['APP_DEBUG'] === 'true'
        ], 'System is healthy');
    }
}
