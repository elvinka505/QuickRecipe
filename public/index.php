<?php

session_start();
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|svg|woff2?)$/', (string) $_SERVER['REQUEST_URI'])) {
    return false;
}

define('PROJECT_ROOT', dirname(__DIR__));

require PROJECT_ROOT . '/vendor/autoload.php';

use App\Core\Logger;
use App\Core\Config;
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\RecipeController;
use App\Controllers\AuthController;
use App\Controllers\FavoritesController;
use App\Exceptions\ConfigException;
use App\Exceptions\NotFoundException;
use App\Exceptions\AppException;
use App\Core\Middleware\RequestLoggingMiddleware;

date_default_timezone_set('Europe/Moscow');

set_error_handler(function (int $level, string $message, string $file, int $line): bool {
    throw new \ErrorException($message, 0, $level, $file, $line);
});

$logger = new Logger();

try {
    $config = new Config(PROJECT_ROOT, $logger);
    $logger = new Logger($config->isDebug());

    $router = new Router($logger, $config->isDebug());
    $router->register([
        HomeController::class,
        RecipeController::class,
        AuthController::class,
        FavoritesController::class,
    ]);
    $router->resolve();
} catch (NotFoundException $e) {
    $logger->warning($e->getMessage());
    http_response_code(404);
    require PROJECT_ROOT . '/src/Views/errors/404.php';
} catch (ConfigException $e) {
    $logger->logException($e);
    http_response_code(503);
    require PROJECT_ROOT . '/src/Views/errors/503.php';
} catch (AppException | \Throwable $e) {
    $logger->logException($e);
    http_response_code(500);
    require PROJECT_ROOT . '/src/Views/errors/500.php';
}
