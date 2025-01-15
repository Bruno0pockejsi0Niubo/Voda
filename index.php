<?php

// Composer autoload:
require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;
use App\Controllers\UserController;

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Jednoduchý router:
if ($uri === '/api/v1/health' && $method === 'GET') {
    echo json_encode(['status' => 'OK', 'service' => 'PHP']);
    exit;
}

// Autentizace
if ($uri === '/api/v1/auth/login' && $method === 'POST') {
    $controller = new AuthController();
    $controller->login();
    exit;
}

// Uživatelé
if (preg_match('@^/api/v1/users$@', $uri) && $method === 'POST') {
    $controller = new UserController();
    $controller->createUser();
    exit;
}

// ... Pokračujte dle potřeby, např. /api/v1/users/:id PUT/DELETE ...
// ... /api/v1/properties ...

// Pokud nic nesedí:
http_response_code(404);
echo json_encode(['error' => 'Not Found']);
