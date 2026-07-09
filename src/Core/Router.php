<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Attributes\Route;
use App\Exceptions\NotFoundException;
use ReflectionClass;
use ReflectionMethod;

class Router
{
    private array $routes = [];

    public function __construct(
        private readonly Logger $logger,
        private readonly bool $debug = false
    ) {
    }

    public function register(array $controllerClasses): void
    {
        foreach ($controllerClasses as $controllerClass) {
            $reflection = new ReflectionClass($controllerClass);

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $attributes = $method->getAttributes(Route::class);

                foreach ($attributes as $attribute) {
                    $route = $attribute->newInstance();

                    foreach ($route->methods as $httpMethod) {
                        $this->routes[$httpMethod][$route->path] = [
                            $controllerClass,
                            $method->getName(),
                        ];
                    }
                }
            }
        }
    }

    public function get(string $path, array $callback): void
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post(string $path, array $callback): void
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $pos = strpos((string) $uri, '?');
        if ($pos !== false) {
            $uri = substr((string) $uri, 0, $pos);
        }
        return $uri;
    }

    public function getQueryParams(): array
    {
        $params = [];
        foreach ($_GET as $key => $value) {
            $params[$key] = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        }
        return $params;
    }

    public function getBody(): array
    {
        $body = [];
        if ($this->getMethod() === 'POST') {
            foreach ($_POST as $key => $value) {
                $body[$key] = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            }
        }
        return $body;
    }

    public function getQueryParam(string $name, ?string $default = null): ?string
    {
        $params = $this->getQueryParams();
        return $params[$name] ?? $default;
    }

    public function getBodyParam(string $name, ?string $default = null): ?string
    {
        $body = $this->getBody();
        return $body[$name] ?? $default;
    }
    public function resolve(): void
    {
        $method = $this->getMethod();
        $uri    = $this->getUri();

        if (!isset($this->routes[$method][$uri])) {
            $this->logger->warning("404: {$method} {$uri}");
            throw new NotFoundException('Страница', 404);
        }

        $callback        = $this->routes[$method][$uri];
        $controllerClass = $callback[0];
        $action          = $callback[1];

        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $creator = new \Nyholm\Psr7Server\ServerRequestCreator(
            $factory,
            $factory,
            $factory,
            $factory
        );
        $request = $creator->fromGlobals();

        $handler = new \App\Core\Http\RequestHandler(
            function () use ($controllerClass, $action): void {
                $controller = new $controllerClass($this, $this->logger);
                $controller->$action();
            }
        );

        if ($this->debug) {
            $middleware = new \App\Core\Middleware\RequestLoggingMiddleware($this->logger);
            $middleware->process($request, $handler);
        } else {
            $handler->handle($request);
        }
    }
}
