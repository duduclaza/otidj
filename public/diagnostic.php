<?php
// Diagnóstico simples
echo "<!DOCTYPE html><html><head><title>Diagnóstico SGQ</title></head><body>";
echo "<h1>🔧 Diagnóstico do Sistema</h1>";

try {
    echo "<h2>✅ 1. PHP Básico</h2>";
    echo "PHP Version: " . PHP_VERSION . "<br>";
    echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
    echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
    
    echo "<h2>✅ 2. Autoload</h2>";
    require_once '../vendor/autoload.php';
    echo "Composer autoload OK<br>";
    
    echo "<h2>✅ 3. Environment</h2>";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
    echo "Environment loaded OK<br>";
    echo "APP_ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . "<br>";
    echo "APP_DEBUG: " . ($_ENV['APP_DEBUG'] ?? 'not set') . "<br>";
    
    echo "<h2>✅ 4. Database</h2>";
    $db = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Database connection OK<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "Users table: " . $result['count'] . " registros<br>";
    
    echo "<h2>✅ 5. Classes</h2>";
    $classes = [
        'App\\Core\\Router',
        'App\\Core\\DebugLogger',
        'App\\Controllers\\AdminController',
        'App\\Services\\PermissionService'
    ];
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "✅ $class<br>";
        } else {
            echo "❌ $class<br>";
        }
    }
    
    echo "<h2>✅ 6. Diretórios</h2>";
    $dirs = [
        '../storage/logs',
        '../views',
        '../src/Controllers',
        '../src/Core'
    ];
    
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            $writable = is_writable($dir) ? 'writable' : 'read-only';
            echo "✅ $dir ($writable)<br>";
        } else {
            echo "❌ $dir<br>";
        }
    }
    
    echo "<h2>✅ 7. Debug Logger Test</h2>";
    try {
        $logger = \App\Core\DebugLogger::getInstance();
        $logger->info('Teste de diagnóstico');
        echo "DebugLogger funcionando<br>";
    } catch (Exception $e) {
        echo "❌ DebugLogger error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h2>🎉 Diagnóstico Completo</h2>";
    echo "Sistema parece estar funcionando corretamente!<br>";
    echo "<a href='/'>← Voltar ao sistema</a>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERRO ENCONTRADO</h2>";
    echo "Erro: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>
