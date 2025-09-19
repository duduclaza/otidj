<?php
// Test endpoint directly
session_start();

// Simulate being logged in as admin
$_SESSION['user_id'] = 1;

// Simulate POST data
$_POST['user_id'] = 1;

// Include the autoloader
require_once 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $controller = new \App\Controllers\AdminController();
    $controller->sendCredentials();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
