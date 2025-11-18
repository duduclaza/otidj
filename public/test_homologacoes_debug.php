<?php
// Debug completo do erro 500
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Homologações - Erro 500</h1>";

try {
    echo "<p>1. Carregando autoload...</p>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p>✅ Autoload OK</p>";
    
    echo "<p>2. Testando Carbon...</p>";
    $teste = \Carbon\Carbon::now();
    echo "<p>✅ Carbon OK: " . $teste->format('Y-m-d H:i:s') . "</p>";
    
    echo "<p>3. Carregando Database...</p>";
    require_once __DIR__ . '/../src/Config/Database.php';
    echo "<p>✅ Database OK</p>";
    
    echo "<p>4. Iniciando sessão...</p>";
    session_start();
    $_SESSION['user_id'] = 1;
    echo "<p>✅ Session OK</p>";
    
    echo "<p>5. Carregando Controller...</p>";
    require_once __DIR__ . '/../src/Controllers/HomologacoesKanbanController.php';
    echo "<p>✅ Controller carregado</p>";
    
    echo "<p>6. Instanciando Controller...</p>";
    $controller = new \App\Controllers\HomologacoesKanbanController();
    echo "<p>✅ Controller instanciado</p>";
    
    echo "<p>7. Testando método index...</p>";
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    echo "<p>✅ Método index executado! Tamanho: " . strlen($output) . " bytes</p>";
    
    echo "<h2 style='color: green;'>TODOS OS TESTES PASSARAM!</h2>";
    echo "<p>Se chegou até aqui, o erro não é no controller.</p>";
    
} catch (\Error $e) {
    echo "<h2 style='color: red;'>ERRO FATAL:</h2>";
    echo "<p><strong>Tipo:</strong> " . get_class($e) . "</p>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<details><summary>Stack trace</summary><pre>" . $e->getTraceAsString() . "</pre></details>";
} catch (\Exception $e) {
    echo "<h2 style='color: red;'>EXCEÇÃO:</h2>";
    echo "<p><strong>Tipo:</strong> " . get_class($e) . "</p>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<details><summary>Stack trace</summary><pre>" . $e->getTraceAsString() . "</pre></details>";
}
?>
