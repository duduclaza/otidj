<?php
if (!function_exists('env')) {
    function env(string $key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $base = rtrim($_ENV['APP_URL'] ?? '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
