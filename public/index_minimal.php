<?php
// Teste mínimo para identificar o problema
echo "<!DOCTYPE html><html><head><title>Teste Mínimo</title></head><body>";
echo "<h1>🔧 Teste Mínimo do Sistema</h1>";

try {
    echo "<p>✅ PHP funcionando</p>";
    
    // Teste 1: Session
    session_start();
    echo "<p>✅ Session OK</p>";
    
    // Teste 2: Autoload
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        echo "<p>✅ Autoload encontrado</p>";
    } else {
        throw new Exception("Autoload não encontrado em: $autoloadPath");
    }
    
    // Teste 3: Environment
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
        echo "<p>✅ Environment carregado</p>";
    } else {
        throw new Exception(".env não encontrado em: $envPath");
    }
    
    // Teste 4: Classes básicas
    if (class_exists('App\\Core\\Router')) {
        echo "<p>✅ Router class OK</p>";
    } else {
        throw new Exception("Router class não encontrada");
    }
    
    // Teste 5: Database
    try {
        $db = new PDO(
            "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "<p>✅ Database conectado</p>";
    } catch (Exception $dbError) {
        echo "<p>⚠️ Database erro: " . $dbError->getMessage() . "</p>";
    }
    
    echo "<h2>🎉 Todos os testes básicos passaram!</h2>";
    echo "<p>O problema pode estar em uma classe específica ou rota.</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERRO ENCONTRADO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}

echo "</body></html>";
?>
