<?php

declare(strict_types=1);

/*
 | Front controller: todas las peticiones entran por aquí.
 */

// Con el servidor embebido de PHP (composer serve), los archivos estáticos se sirven directo.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

$basePath = dirname(__DIR__);

if (!is_file($basePath . '/vendor/autoload.php')) {
    http_response_code(500);
    exit('Faltan dependencias: ejecuta "composer install" en la raíz del proyecto.');
}

require $basePath . '/vendor/autoload.php';

use App\Core\App;
use App\Core\Router;

App::boot($basePath);

$router = new Router();
(require $basePath . '/config/routes.php')($router);

App::run($router);
