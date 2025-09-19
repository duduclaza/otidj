<?php
require_once 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Create router
$router = new \App\Core\Router(__DIR__);

// Add the route
$router->post('/admin/users/send-credentials', [App\Controllers\AdminController::class, 'sendCredentials']);

// Test if route is registered
echo "Testing route registration...\n";

// Simulate the request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/admin/users/send-credentials';

// Start session
session_start();
$_SESSION['user_id'] = 1;
$_POST['user_id'] = 1;

try {
    echo "Dispatching route...\n";
    $router->dispatch();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
