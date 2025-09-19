<?php
require_once 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Start session
session_start();
$_SESSION['user_id'] = 1; // Simular admin logado

// Simular dados POST
$_POST = [
    'name' => 'Teste Usuario',
    'email' => 'teste' . time() . '@teste.com',
    'setor' => 'TI',
    'filial' => 'Matriz',
    'role' => 'user',
    'profile_id' => '2' // ID do perfil
];

echo "=== Teste de Criação de Usuário ===\n";
echo "Dados enviados:\n";
print_r($_POST);

try {
    $controller = new \App\Controllers\AdminController();
    
    // Capturar output
    ob_start();
    $controller->createUser();
    $output = ob_get_clean();
    
    echo "\n=== Resposta do Controller ===\n";
    echo $output . "\n";
    
    // Tentar decodificar JSON
    $response = json_decode($output, true);
    if ($response) {
        echo "\n=== Resposta Decodificada ===\n";
        print_r($response);
    }
    
} catch (Exception $e) {
    echo "\n=== ERRO ===\n";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
