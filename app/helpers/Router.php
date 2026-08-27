<?php
declare(strict_types=1);

class Router
{
    private static array $routes = [];
    private static array $apiRoutes = [];

    public static function get(string $path, string $handler): void
    {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post(string $path, string $handler): void
    {
        self::$routes['POST'][$path] = $handler;
    }

    public static function apiGet(string $path, string $handler): void
    {
        self::$apiRoutes['GET'][$path] = $handler;
    }

    public static function apiPost(string $path, string $handler): void
    {
        self::$apiRoutes['POST'][$path] = $handler;
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $routeParam = $_GET['route'] ?? '';
        $route = '/' . ltrim($routeParam, '/');

        if (str_starts_with($route, '/api/')) {
            self::dispatchApi($method, $route);
            return;
        }

        $routes = self::$routes[$method] ?? [];

        foreach ($routes as $path => $handler) {
            if ($path === $route) {
                self::callHandler($handler);
                return;
            }

            if (preg_match('#^' . str_replace(['{id}'], ['(\d+)'], $path) . '$#', $route, $matches)) {
                array_shift($matches);
                $_GET['params'] = $matches;
                self::callHandler($handler);
                return;
            }
        }

        http_response_code(404);
        require dirname(__DIR__, 2) . '/views/404.php';
    }

    private static function dispatchApi(string $method, string $route): void
    {
        $routes = self::$apiRoutes[$method] ?? [];

        foreach ($routes as $path => $handler) {
            if ($path === $route) {
                self::callHandler($handler);
                return;
            }

            if (preg_match('#^' . str_replace(['{id}'], ['(\d+)'], $path) . '$#', $route, $matches)) {
                array_shift($matches);
                $_GET['params'] = $matches;
                self::callHandler($handler);
                return;
            }
        }

        Response::error('API endpoint not found', [], 404);
    }

    private static function callHandler(string $handler): void
    {
        [$className, $method] = explode('@', $handler);
        $controllerFile = dirname(__DIR__, 2) . '/app/controllers/' . $className . '.php';
        
        if (!file_exists($controllerFile)) {
            throw new RuntimeException("Controller file not found: {$className}");
        }

        require_once $controllerFile;
        
        $controller = new $className();
        $controller->$method();
    }
}
