<?php
// Arquivo de teste de conexão com o banco de dados

try {
    $host = 'srv1890.hstgr.io';
    $dbname = 'u230868210_djsgqpro';
    $username = 'u230868210_dusou';
    $password = 'Pandora@1989';
    $port = 3306;
    
    // Tentar conexão
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão bem-sucedida com o banco de dados!\n";
    
    // Testar consulta simples
    $stmt = $conn->query("SELECT VERSION() AS mysql_version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Versão do MySQL: " . $version['mysql_version'] . "\n";
    
    // Verificar tabelas existentes
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Total de tabelas encontradas: " . count($tables) . "\n";
    
} catch (PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n";
    
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
