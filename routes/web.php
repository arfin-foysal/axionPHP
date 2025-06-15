<?php

use App\Controllers\HomeController;

$router = app()->getRouter();

// Home routes
$router->get('/', [HomeController::class, 'index']);
$router->get('/health', [HomeController::class, 'health']);

// Simple closure route example
$router->get('/test', function () {
    return [
        'message' => 'This is a test route using closure',
        'timestamp' => date('Y-m-d H:i:s')
    ];
});
