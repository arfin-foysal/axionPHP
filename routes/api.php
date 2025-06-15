<?php

use App\Controllers\AuthController;
use App\Controllers\DemoAuthController;

$router = app()->getRouter();

// API routes with /api prefix
$router->group(['prefix' => 'api'], function ($router) {

    // Demo authentication routes (public) - using in-memory storage
    $router->group(['prefix' => 'demo'], function ($router) {
        $router->post('/register', [DemoAuthController::class, 'register']);
        $router->post('/login', [DemoAuthController::class, 'login']);
        $router->post('/refresh', [DemoAuthController::class, 'refresh']);
        $router->get('/users', [DemoAuthController::class, 'users']);

        // Protected routes (require JWT)
        $router->group(['middleware' => ['jwt']], function ($router) {
            $router->get('/profile', [DemoAuthController::class, 'profile']);
            $router->post('/logout', [DemoAuthController::class, 'logout']);
        });
    });

    // Authentication routes (public) - requires database
    $router->group(['prefix' => 'auth'], function ($router) {
        $router->post('/register', [AuthController::class, 'register']);
        $router->post('/login', [AuthController::class, 'login']);
        $router->post('/refresh', [AuthController::class, 'refresh']);

        // Protected routes (require JWT)
        $router->group(['middleware' => ['jwt']], function ($router) {
            $router->get('/profile', [AuthController::class, 'profile']);
            $router->post('/logout', [AuthController::class, 'logout']);
        });
    });

    // Protected API routes
    $router->group(['middleware' => ['jwt']], function ($router) {
        $router->get('/user', [AuthController::class, 'profile']);

        // Example protected route
        $router->get('/protected', function () {
            $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            $user = $request->attributes->get('user');
            return [
                'message' => 'This is a protected route',
                'user' => $user,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        });
    });
});
