<?php
// Página de diagnóstico para identificar erros 500
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico do Sistema</h1>";

try {
    echo "<h2>1. Testando autoload...</h2>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoload carregado com sucesso<br>";
    
    echo "<h2>2. Testando .env...</h2>";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
    echo "✅ .env carregado com sucesso<br>";
    echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'não definido') . "<br>";
    echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'não definido') . "<br>";
    
    echo "<h2>3. Testando conexão com banco...</h2>";
    $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'];
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    echo "✅ Conexão com banco OK<br>";
    
    echo "<h2>4. Testando Router...</h2>";
    $router = new App\Core\Router(__DIR__);
    echo "✅ Router criado com sucesso<br>";
    
    echo "<h2>5. Testando Controller...</h2>";
    $controller = new App\Controllers\MelhoriaContinua2Controller();
    echo "✅ MelhoriaContinua2Controller criado com sucesso<br>";
    
    echo "<h2>6. Testando sessão...</h2>";
    session_start();
    echo "✅ Sessão iniciada<br>";
    
    echo "<h2>✅ TODOS OS TESTES PASSARAM!</h2>";
    echo "<p>O sistema deveria estar funcionando. Tente acessar: <a href='/melhoria-continua-2'>/melhoria-continua-2</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERRO ENCONTRADO:</h2>";
    echo "<pre style='background: #f00; color: #fff; padding: 10px;'>";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
} catch (Error $e) {
    echo "<h2>❌ ERRO FATAL ENCONTRADO:</h2>";
    echo "<pre style='background: #f00; color: #fff; padding: 10px;'>";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

echo "<hr>";
echo "<h3>Informações do PHP:</h3>";
echo "Versão PHP: " . PHP_VERSION . "<br>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . "<br>";
?>
