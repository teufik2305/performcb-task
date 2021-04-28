<?php

require_once __DIR__ . '/../vendor/autoload.php';

use app\Router;
use app\controllers\LogController;

$router = new Router();

$router->get('/', [LogController::class, 'index']);
$router->get('/log', [LogController::class, 'getLog']);
$router->post('/log', [LogController::class, 'createLog']);
$router->delete("/log", [LogController::class, 'deleteLog']);
$router->get('/aggregate/ip', [LogController::class, 'aggregateByIp']);
$router->get('/aggregate/method', [LogController::class, 'aggregateByMethod']);

$router->resolve();
