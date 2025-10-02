<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Request;
use App\Core\Router;

$router = new Router();

// Register routes
require BASE_PATH . '/routes/web.php';

// Dispatch
$request = Request::capture();
$response = $router->dispatch($request);
$response->send();
