<?php

declare(strict_types=1);

namespace Config;

use Controller\AuthController;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        // Strip query string
        $path = strtok($uri, '?');

        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && $route['path'] === $path) {
                ($route['handler'])();
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Ruta no encontrada.'], JSON_UNESCAPED_UNICODE);
    }

    public static function register(): self
    {
        $router     = new self();
        $controller = new AuthController();

        $router->add('POST', '/api/auth/login',    [$controller, 'login']);
        $router->add('POST', '/api/auth/validate', [$controller, 'validate']);
        $router->add('POST', '/api/auth/logout',   [$controller, 'logout']);
        $router->add('GET',  '/api/auth/health',   [$controller, 'health']);

        return $router;
    }
}
