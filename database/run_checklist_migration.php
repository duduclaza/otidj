<?php
/**
 * Script para criar tabelas de Checklist de Homologações
 * Execute este arquivo no navegador: http://seusite.com/database/run_checklist_migration.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Migração - Checklists de Homologações</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid green; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid red; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid blue; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Migração - Sistema de Checklists</h1>
";

try {
    $db = Database::getInstance()->getConnection();
    echo "<div class='info'>✅ Conexão com banco de dados estabelecida</div>";
    
    // Ler arquivo SQL
    $sqlFile = __DIR__ . '/migrations/create_homologacao_checklists.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo SQL não encontrado: $sqlFile");
    }
    
    echo "<div class='info'>📄 Lendo arquivo: create_homologacao_checklists.sql</div>";
    
    $sql = file_get_contents($sqlFile);
    
    // Dividir em statements individuais
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    echo "<div class='info'>📊 Total de comandos SQL: " . count($statements) . "</div>";
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $db->exec($statement);
            
            // Detectar tipo de comando
            if (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $statement, $matches)) {
                echo "<div class='success'>✅ Tabela criada/verificada: {$matches[1]}</div>";
            } elseif (preg_match('/ALTER TABLE (\w+)/i', $statement, $matches)) {
                echo "<div class='success'>✅ Tabela alterada: {$matches[1]}</div>";
            } else {
                echo "<div class='success'>✅ Comando executado com sucesso</div>";
            }
            
            $success++;
            
        } catch (PDOException $e) {
            // Ignorar erros de "já existe"
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "<div class='info'>ℹ️ Elemento já existe (pulando): " . substr($statement, 0, 50) . "...</div>";
            } else {
                echo "<div class='error'>❌ Erro: " . $e->getMessage() . "</div>";
                echo "<pre>" . htmlspecialchars(substr($statement, 0, 200)) . "</pre>";
                $errors++;
            }
        }
    }
    
    echo "<hr>";
    echo "<h2>📊 Resumo da Migração</h2>";
    echo "<div class='success'>✅ Comandos executados com sucesso: $success</div>";
    
    if ($errors > 0) {
        echo "<div class='error'>❌ Erros encontrados: $errors</div>";
    }
    
    // Verificar tabelas criadas
    echo "<hr>";
    echo "<h2>🔍 Verificando Tabelas Criadas</h2>";
    
    $tables = [
        'homologacao_checklists',
        'homologacao_checklist_itens',
        'homologacao_checklist_respostas'
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Contar registros
                $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "<div class='success'>✅ Tabela '$table' existe ($count registros)</div>";
            } else {
                echo "<div class='error'>❌ Tabela '$table' NÃO foi criada</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erro ao verificar tabela '$table': " . $e->getMessage() . "</div>";
        }
    }
    
    // Verificar coluna checklist_id em homologacoes
    echo "<hr>";
    echo "<h2>🔍 Verificando Coluna em Homologações</h2>";
    try {
        $stmt = $db->query("SHOW COLUMNS FROM homologacoes LIKE 'checklist_id'");
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "<div class='success'>✅ Coluna 'checklist_id' existe na tabela 'homologacoes'</div>";
        } else {
            echo "<div class='error'>❌ Coluna 'checklist_id' NÃO existe na tabela 'homologacoes'</div>";
        }
    } catch (Exception $e) {
        echo "<div class='info'>ℹ️ Tabela 'homologacoes' pode não existir ainda</div>";
    }
    
    echo "<hr>";
    echo "<div class='success'><h2>🎉 MIGRAÇÃO CONCLUÍDA!</h2></div>";
    echo "<p>Agora você pode usar o sistema de checklists em Homologações.</p>";
    echo "<p><a href='/homologacoes'>← Voltar para Homologações</a></p>";
    
} catch (Exception $e) {
    echo "<div class='error'><h2>❌ ERRO NA MIGRAÇÃO</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "</body></html>";
