<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Router;
use App\Controllers\IndexController;
use App\Controllers\AffiliateController;
use App\Controllers\AuthController;
use App\Controllers\ClickController;
use App\Controllers\ProductController;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

header('Content-Type: application/json');

$router = new Router();

$router->add('GET', '/', [IndexController::class, 'home']);
$router->add('GET', '/affiliate/{slug}', [AffiliateController::class, 'show']);
$router->add('POST', '/auth/register', [AuthController::class, 'register']);
$router->add('PUT', '/affiliate/{slug}', [AffiliateController::class, 'update']);
$router->add('POST', '/auth/login', [AuthController::class, 'login']);
$router->add('POST', '/click-track', [ClickController::class, 'track']);
$router->add('GET', '/affiliate/{slug}/stats', [ClickController::class, 'stats']);
$router->add('POST', '/affiliate/{slug}/product', [ProductController::class, 'create']);
$router->add('PUT', '/affiliate/{slug}/product', [ProductController::class, 'update']);
$router->add('DELETE', '/affiliate/{slug}/product', [ProductController::class, 'delete']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
