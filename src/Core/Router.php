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

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $normalized = $this->normalize($uri);

        $handler = $this->routes[$method][$normalized] ?? null;
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

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }
}
