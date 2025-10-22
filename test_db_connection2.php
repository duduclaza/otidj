<?php
// Carrega as variáveis do .env
$envFile = __DIR__ . '/.env';
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$config = [];
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue; // Pula comentários
    
    list($key, $value) = explode('=', $line, 2);
    $config[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
}

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s',
        $config['DB_HOST'],
        $config['DB_PORT'],
        $config['DB_DATABASE']
    );
    
    echo "🔍 Tentando conectar ao banco de dados...\n";
    echo "📡 Host: {$config['DB_HOST']}:{$config['DB_PORT']}\n";
    echo "💾 Database: {$config['DB_DATABASE']}\n";
    echo "👤 Usuário: {$config['DB_USERNAME']}\n";
    
    $conn = new PDO($dsn, $config['DB_USERNAME'], $config['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✅ Conexão bem-sucedida!\n\n";
    
    // Testar consulta simples
    $stmt = $conn->query("SELECT VERSION() AS mysql_version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Versão do MySQL: " . $version['mysql_version'] . "\n";
    
    // Listar tabelas
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Total de tabelas: " . count($tables) . "\n";
    
    // Verificar se a tabela de auditorias existe
    $auditoriasExists = in_array('auditorias', array_map('strtolower', $tables));
    echo "🔍 Tabela 'auditorias' existe: " . ($auditoriasExists ? '✅ Sim' : '❌ Não') . "\n";
    
    // Verificar permissões do usuário
    $stmt = $conn->query("SHOW GRANTS FOR CURRENT_USER()");
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n🔑 Permissões do usuário:\n";
    foreach ($grants as $grant) {
        echo "- " . $grant . "\n";
    }
    
} catch (PDOException $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    
    // Verificar erros comuns
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "🔒 Erro de autenticação. Verifique usuário e senha.\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "💾 O banco de dados especificado não existe.\n";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "🔌 Não foi possível conectar ao servidor. Verifique o host e porta.\n";
    } else {
        echo "🔍 Detalhes do erro: " . $e->getMessage() . "\n";
    }
}
