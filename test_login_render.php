<?php
require_once __DIR__ . '/vendor/autoload.php';

session_start();

// Load env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $controller = new \App\Controllers\AuthController();
    ob_start();
    $controller->login();
    $html = ob_get_clean();
    echo "=== Rendered HTML length: " . strlen($html) . "\n";
    echo substr($html, 0, 300) . "\n...\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
