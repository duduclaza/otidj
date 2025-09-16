<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->routes['DELETE'][$this->normalize($path)] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $normalized = $this->normalize($uri);

        // First try exact match
        $handler = $this->routes[$method][$normalized] ?? null;
        
        // If no exact match, try pattern matching for dynamic routes
        if (!$handler && isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $pattern => $routeHandler) {
                if ($this->matchRoute($pattern, $normalized)) {
                    $handler = $routeHandler;
                    break;
                }
            }
        }
        
        if (!$handler) {
            // If route exists for other method, return 405
            foreach ($this->routes as $m => $set) {
                if (isset($set[$normalized])) {
                    http_response_code(405);
                    header('Allow: ' . implode(', ', array_keys($this->routes)));
                    echo '405 Method Not Allowed';
                    return;
                }
            }
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        if (is_array($handler)) {
            [$class, $methodName] = $handler;
            $instance = new $class();
            $instance->$methodName();
            return;
        }

        call_user_func($handler);
    }

    private function matchRoute(string $pattern, string $uri): bool
    {
        // Convert pattern like /toners/retornados/delete/{id} to regex
        $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        
        return preg_match($regex, $uri);
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }
}
