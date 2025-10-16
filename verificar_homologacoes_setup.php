<?php
/**
 * VERIFICAÇÃO RÁPIDA - MÓDULO HOMOLOGAÇÕES
 * 
 * Script para verificar se o módulo foi instalado corretamente
 * Não faz alterações, apenas verifica o status atual
 */

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Verificação Homologações</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #00ff00; }
        .ok { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        .info { color: #00aaff; }
        pre { background: #000; padding: 10px; border-left: 3px solid #00ff00; }
    </style>
</head>
<body>
<h1>🔍 VERIFICAÇÃO DO MÓDULO HOMOLOGAÇÕES</h1>
<pre>';

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? '';
    $username = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASS'] ?? '';
    
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<span class='ok'>✅ Conexão: OK</span>\n";
    echo "Database: {$dbname}\n\n";
    
    // Verificar tabelas
    echo "═══════════════════════════════════════════════════\n";
    echo "TABELAS\n";
    echo "═══════════════════════════════════════════════════\n";
    
    $tables = [
        'homologacoes' => 'Tabela principal',
        'homologacoes_responsaveis' => 'Responsáveis (many-to-many)',
        'homologacoes_historico' => 'Histórico de mudanças',
        'homologacoes_anexos' => 'Anexos em BLOB'
    ];
    
    $totalTables = 0;
    foreach ($tables as $table => $desc) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            // Contar registros
            $count = $pdo->query("SELECT COUNT(*) as total FROM $table")->fetch();
            echo "<span class='ok'>✅ $table</span> - $desc ({$count['total']} registros)\n";
            $totalTables++;
        } else {
            echo "<span class='error'>❌ $table</span> - NÃO ENCONTRADA\n";
        }
    }
    
    echo "\n<span class='info'>Total: $totalTables/4 tabelas</span>\n\n";
    
    // Verificar coluna department
    echo "═══════════════════════════════════════════════════\n";
    echo "ESTRUTURA USERS\n";
    echo "═══════════════════════════════════════════════════\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'department'");
    if ($stmt->rowCount() > 0) {
        $col = $stmt->fetch();
        echo "<span class='ok'>✅ Coluna department</span> - Tipo: {$col['Type']}\n";
        
        // Contar usuários com department
        $count = $pdo->query("SELECT COUNT(*) as total FROM users WHERE department IS NOT NULL")->fetch();
        echo "   Usuários com department: {$count['total']}\n";
        
        // Listar departments únicos
        $depts = $pdo->query("SELECT DISTINCT department FROM users WHERE department IS NOT NULL")->fetchAll();
        if (!empty($depts)) {
            echo "   Departments: " . implode(', ', array_column($depts, 'department')) . "\n";
        }
    } else {
        echo "<span class='error'>❌ Coluna department</span> - NÃO ENCONTRADA\n";
        echo "   <span class='warning'>ATENÇÃO:</span> Execute a migration para adicionar\n";
    }
    
    echo "\n";
    
    // Verificar permissões
    echo "═══════════════════════════════════════════════════\n";
    echo "PERMISSÕES\n";
    echo "═══════════════════════════════════════════════════\n";
    
    $stmt = $pdo->query("
        SELECT p.name, pp.can_view, pp.can_edit, pp.can_delete, pp.can_export
        FROM profile_permissions pp
        JOIN profiles p ON p.id = pp.profile_id
        WHERE pp.module = 'homologacoes'
    ");
    
    $permissions = $stmt->fetchAll();
    
    if (!empty($permissions)) {
        echo "<span class='ok'>✅ Módulo 'homologacoes' configurado</span>\n\n";
        foreach ($permissions as $perm) {
            $perms = [];
            if ($perm['can_view']) $perms[] = 'view';
            if ($perm['can_edit']) $perms[] = 'edit';
            if ($perm['can_delete']) $perms[] = 'delete';
            if ($perm['can_export']) $perms[] = 'export';
            
            echo "   {$perm['name']}: " . implode(', ', $perms) . "\n";
        }
    } else {
        echo "<span class='error'>❌ Nenhuma permissão configurada</span>\n";
        echo "   <span class='warning'>ATENÇÃO:</span> Execute a migration para configurar\n";
    }
    
    echo "\n";
    
    // Verificar índices
    echo "═══════════════════════════════════════════════════\n";
    echo "ÍNDICES (homologacoes)\n";
    echo "═══════════════════════════════════════════════════\n";
    
    if ($totalTables > 0) {
        $indexes = $pdo->query("SHOW INDEX FROM homologacoes")->fetchAll();
        $indexNames = array_unique(array_column($indexes, 'Key_name'));
        
        foreach ($indexNames as $idx) {
            $cols = array_filter($indexes, fn($i) => $i['Key_name'] === $idx);
            $colNames = array_column($cols, 'Column_name');
            echo "<span class='ok'>✅ $idx</span> (" . implode(', ', $colNames) . ")\n";
        }
    }
    
    echo "\n";
    
    // Foreign keys
    echo "═══════════════════════════════════════════════════\n";
    echo "FOREIGN KEYS\n";
    echo "═══════════════════════════════════════════════════\n";
    
    if ($totalTables > 0) {
        $fks = $pdo->query("
            SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME LIKE 'homologacoes%'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ")->fetchAll();
        
        if (!empty($fks)) {
            foreach ($fks as $fk) {
                echo "<span class='ok'>✅ {$fk['TABLE_NAME']}</span> → {$fk['REFERENCED_TABLE_NAME']}\n";
            }
        }
    }
    
    echo "\n";
    
    // Resumo final
    echo "═══════════════════════════════════════════════════\n";
    echo "RESUMO\n";
    echo "═══════════════════════════════════════════════════\n";
    
    $status = 'OK';
    if ($totalTables < 4) $status = 'INCOMPLETO';
    if ($totalTables === 0) $status = 'NÃO INSTALADO';
    
    $color = $totalTables === 4 ? 'ok' : ($totalTables > 0 ? 'warning' : 'error');
    
    echo "Status: <span class='$color'>$status</span>\n";
    echo "Tabelas: $totalTables/4\n";
    echo "Permissões: " . count($permissions) . " perfis\n";
    
    if ($totalTables === 4 && !empty($permissions)) {
        echo "\n<span class='ok'>🎉 MÓDULO INSTALADO E PRONTO PARA USO!</span>\n";
        echo "Acesse: <a href='/homologacoes' style='color:#00aaff'>/homologacoes</a>\n";
    } elseif ($totalTables < 4 || empty($permissions)) {
        echo "\n<span class='warning'>⚠️ INSTALAÇÃO INCOMPLETA</span>\n";
        echo "Execute: setup_homologacoes.php ou a migration manual\n";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ ERRO: {$e->getMessage()}</span>\n";
}

echo '</pre>
<p style="color: #666; margin-top: 20px;">
    <a href="/" style="color: #00aaff;">← Voltar ao sistema</a> | 
    <a href="setup_homologacoes.php" style="color: #00aaff;">Executar setup</a>
</p>
</body>
</html>';
