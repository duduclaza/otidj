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

        // Debug para fluxogramas
        if (strpos($normalized, '/fluxogramas/registros/') !== false) {
            error_log("Router - Method: {$method}, URI: {$uri}, Normalized: {$normalized}");
        }

        // First try exact match
        $handler = $this->routes[$method][$normalized] ?? null;
        $route = $normalized;
        
        if (strpos($normalized, '/fluxogramas/registros/') !== false) {
            error_log("Router - Exact match: " . ($handler ? 'SIM' : 'NÃO'));
        }
        
        // If no exact match, try pattern matching for dynamic routes
        if (!$handler && isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $pattern => $routeHandler) {
                if ($this->matchRoute($pattern, $normalized)) {
                    if (strpos($normalized, '/fluxogramas/registros/') !== false) {
                        error_log("Router - Pattern matched: {$pattern}");
                    }
                    $handler = $routeHandler;
                    $route = $pattern;
                    break;
                }
            }
        }
        
        if (!$handler) {
            // If route exists for other method, return 405
            $allowedMethods = [];
            foreach ($this->routes as $m => $set) {
                if (isset($set[$normalized])) {
                    $allowedMethods[] = $m;
                }
            }
            
            if (!empty($allowedMethods)) {
                http_response_code(405);
                header('Allow: ' . implode(', ', $allowedMethods));
                echo '405 Method Not Allowed';
                return;
            }
            
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        if (is_array($handler)) {
            [$class, $methodName] = $handler;
            
            if (strpos($normalized, '/fluxogramas/registros/') !== false) {
                error_log("Router - Handler encontrado: {$class}::{$methodName}");
                error_log("Router - Route pattern: {$route}");
            }
            
            $instance = new $class();
            
            // Extract parameters from URL for dynamic routes
            $params = $this->extractParams($normalized, $route);
            
            if (strpos($normalized, '/fluxogramas/registros/') !== false) {
                error_log("Router - Parâmetros extraídos: " . json_encode($params));
            }
            
            if (!empty($params)) {
                $instance->$methodName(...$params);
            } else {
                $instance->$methodName();
            }
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

    private function extractParams(string $uri, string $pattern): array
    {
        // Convert pattern to regex and extract parameters
        $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        
        if (preg_match($regex, $uri, $matches)) {
            // Remove the full match, keep only captured groups
            array_shift($matches);
            return $matches;
        }
        
        return [];
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }
}
