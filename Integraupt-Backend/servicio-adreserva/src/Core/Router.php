<?php

namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, string $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void {
        // Convertir parámetros de ruta tipo {nombreParam} en grupos de captura de regex
        // Por ejemplo: /api/admin/reservas/{reservaId}/gestionar -> ^/api/admin/reservas/([^/]+)/gestionar$
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Limpiar barras duplicadas o finales excepto la raíz
        if ($requestUri !== '/') {
            $requestUri = rtrim($requestUri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $requestUri, $matches)) {
                array_shift($matches); // Quitar el primer match completo

                // Extraer el nombre de la clase y el método
                $parts = explode('@', $route['handler']);
                $className = $parts[0];
                $methodName = $parts[1];

                if (!class_exists($className)) {
                    throw new HttpException("Clase controladora no encontrada: $className", 500);
                }

                $controller = new $className();

                if (!method_exists($controller, $methodName)) {
                    throw new HttpException("Método del controlador no encontrado: $methodName en $className", 500);
                }

                // Invocar el método del controlador pasando los parámetros capturados de la ruta
                call_user_func_array([$controller, $methodName], $matches);
                return;
            }
        }

        throw new HttpException("Ruta no encontrada: $requestMethod $requestUri", 404);
    }
}
