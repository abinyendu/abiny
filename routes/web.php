<?php

use App\Core\Response;
use App\Core\Request;
use App\Controllers\HomeController;

/* @var $router App\Core\Router */

$router->get('/', [HomeController::class, 'index']);
$router->get('/health', function () {
    return Response::json(['status' => 'ok', 'time' => date('c')]);
});
