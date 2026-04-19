<?php

namespace App;

class Router
{
    private $routes = [];

    public function register(string $method, string $path, array $handler, array $middleware = []): self
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
        return $this;
    }

    public function get(string $path, array $handler, array $middleware = []): self
    {
        return $this->register('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): self
    {
        return $this->register('POST', $path, $handler, $middleware);
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/documind/public', '', $path) ?: '/';

        foreach ($this->routes as $route) {
            if ($this->matches($route, $method, $path)) {
                $this->handle($route);
                return;
            }
        }

        http_response_code(404);
        include __DIR__ . '/../views/errors/404.php';
    }

    private function matches(array $route, string $method, string $path): bool
    {
        if ($route['method'] !== $method) {
            return false;
        }

        // Convert route pattern to regex
        $pattern = $route['path'];
        
        // Handle {id} style parameters
        $pattern = preg_replace('/\{(\w+)\}/', '(\w+)', $pattern);
        
        // Handle (?P<id>\d+) style regex patterns
        $pattern = "#^{$pattern}$#";

        return preg_match($pattern, $path);
    }

    private function handle(array $route): void
    {
        // Run middleware
        foreach ($route['middleware'] as $middlewareClass) {
            $middleware = new $middlewareClass();
            $middleware->handle();
        }

        // Execute controller action
        [$controller, $action] = $route['handler'];
        $instance = new $controller();
        
        // Extract path parameters
        $pattern = $route['path'];
        
        // Convert {id} to regex
        $pattern = preg_replace('/\{(\w+)\}/', '(\w+)', $pattern);
        
        $pattern = "#^{$pattern}$#";
        $path = str_replace('/documind/public', '', $_SERVER['REQUEST_URI']) ?: '/';
        $path = parse_url($path, PHP_URL_PATH);

        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches); // Remove full match
            // Convert string parameters to integers if they're numeric
            $args = [];
            foreach ($matches as $m) {
                $args[] = is_numeric($m) ? (int)$m : $m;
            }
            call_user_func_array([$instance, $action], $args);
        } else {
            call_user_func([$instance, $action]);
        }
    }
}
