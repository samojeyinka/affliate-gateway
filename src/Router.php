<?php

namespace App;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, array $handler): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $regex = preg_replace('#\{[a-zA-Z]+\}#', '([^/]+)', $route['pattern']);

            if (preg_match('#^' . $regex . '$#', $path, $matches)) {
                array_shift($matches);
                [$class, $action] = $route['handler'];
                (new $class())->$action(...$matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
}
