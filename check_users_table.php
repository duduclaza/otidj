<?php
require_once 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $db = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => true
        ]
    );
    
    echo "=== Estrutura da tabela users ===\n";
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Default']}\n";
    }
    
    echo "\n=== Verificar se tabela profiles existe ===\n";
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM profiles");
        $count = $stmt->fetchColumn();
        echo "Tabela profiles existe com $count registros\n";
        
        echo "\n=== Perfis disponíveis ===\n";
        $stmt = $db->query("SELECT id, name, is_default FROM profiles");
        $profiles = $stmt->fetchAll();
        foreach ($profiles as $profile) {
            $default = $profile['is_default'] ? ' (PADRÃO)' : '';
            echo "- ID: {$profile['id']}, Nome: {$profile['name']}{$default}\n";
        }
    } catch (Exception $e) {
        echo "Erro ao acessar tabela profiles: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Usuários existentes ===\n";
    $stmt = $db->query("SELECT id, name, email, status, profile_id FROM users LIMIT 5");
    $users = $stmt->fetchAll();
    foreach ($users as $user) {
        echo "- ID: {$user['id']}, Nome: {$user['name']}, Email: {$user['email']}, Status: {$user['status']}, Profile: {$user['profile_id']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro de conexão: " . $e->getMessage() . "\n";
}
