<?php
// Test script for send credentials endpoint
require_once 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Start session
session_start();

// Simulate admin login
$_SESSION['user_id'] = 1; // Assuming admin user ID is 1

// Test the endpoint
$url = 'https://djbr.sgqoti.com.br/admin/users/send-credentials';
$data = ['user_id' => 1]; // Test with admin user

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

// Also test locally
echo "\n--- Testing locally ---\n";

try {
    $controller = new \App\Controllers\AdminController();
    $_POST['user_id'] = 1;
    
    ob_start();
    $controller->sendCredentials();
    $output = ob_get_clean();
    
    echo "Local test output: " . $output . "\n";
} catch (Exception $e) {
    echo "Local test error: " . $e->getMessage() . "\n";
}
