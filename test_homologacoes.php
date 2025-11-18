<?php
// Script de teste para verificar se o controller está funcionando

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Iniciando teste...\n<br>";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    
    echo "Autoload OK\n<br>";
    
    require_once __DIR__ . '/src/Config/Database.php';
    echo "Database OK\n<br>";
    
    require_once __DIR__ . '/src/Controllers/HomologacoesKanbanController.php';
    echo "Controller carregado OK\n<br>";
    
    session_start();
    $_SESSION['user_id'] = 1; // Simular usuário logado
    
    $controller = new \App\Controllers\HomologacoesKanbanController();
    echo "Controller instanciado OK\n<br>";
    
    echo "\nTeste concluído com sucesso!\n<br>";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n<br>";
    echo "Arquivo: " . $e->getFile() . "\n<br>";
    echo "Linha: " . $e->getLine() . "\n<br>";
    echo "Stack trace:\n<br><pre>" . $e->getTraceAsString() . "</pre>";
}
