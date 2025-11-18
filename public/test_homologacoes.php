<?php
// Script de teste para verificar se o controller está funcionando

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Homologações</h1>";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p>✅ Autoload OK</p>";
    
    require_once __DIR__ . '/../src/Config/Database.php';
    echo "<p>✅ Database OK</p>";
    
    require_once __DIR__ . '/../src/Controllers/HomologacoesKanbanController.php';
    echo "<p>✅ Controller carregado OK</p>";
    
    session_start();
    $_SESSION['user_id'] = 1; // Simular usuário logado
    
    $controller = new \App\Controllers\HomologacoesKanbanController();
    echo "<p>✅ Controller instanciado OK</p>";
    
    echo "<h2 style='color: green;'>TESTE CONCLUÍDO COM SUCESSO!</h2>";
    echo "<p>O problema NÃO está no controller.</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>ERRO ENCONTRADO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<details><summary>Stack trace</summary><pre>" . $e->getTraceAsString() . "</pre></details>";
}
?>
