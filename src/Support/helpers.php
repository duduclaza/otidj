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

if (!function_exists('url')) {
    function url(string $path = '/'): string {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        $path = '/' . ltrim($path, '/');
        return $base ? $base . $path : $path;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if ($message === null) {
            $msg = $_SESSION['flash'][$key] ?? null;
            if (isset($_SESSION['flash'][$key])) unset($_SESSION['flash'][$key]);
            return $msg;
        }
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $to): void {
        header('Location: ' . url($to));
        exit;
    }
}
