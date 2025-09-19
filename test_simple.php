<?php
// Teste básico para verificar se o autoload funciona
echo "=== Teste Básico ===\n";

try {
    require_once 'vendor/autoload.php';
    echo "✅ Autoload OK\n";
    
    // Load environment
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    echo "✅ Environment OK\n";
    
    // Test database connection
    $db = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database OK\n";
    
    // Test debug logger
    $logger = \App\Core\DebugLogger::getInstance();
    echo "✅ DebugLogger OK\n";
    
    echo "\n=== Tudo funcionando! ===\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}
