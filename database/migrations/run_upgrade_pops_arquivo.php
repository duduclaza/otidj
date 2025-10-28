<?php
/**
 * Script para executar migration: Upgrade POPs e ITs arquivo para LONGBLOB
 * Data: 27/10/2025
 */

require_once __DIR__ . '/../../config/database.php';

try {
    echo "🔄 Iniciando migration: Upgrade arquivo para LONGBLOB...\n\n";
    
    $db = \App\Config\Database::getInstance()->getConnection();
    
    // Verificar estado atual
    echo "📊 Estado atual da coluna:\n";
    $stmt = $db->query("
        SELECT 
            COLUMN_NAME,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH
        FROM 
            INFORMATION_SCHEMA.COLUMNS
        WHERE 
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'pops_its_registros'
            AND COLUMN_NAME = 'arquivo'
    ");
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($current) {
        echo "   - Tipo atual: {$current['DATA_TYPE']}\n";
        echo "   - Capacidade: ";
        if ($current['DATA_TYPE'] === 'mediumblob') {
            echo "16MB (16.777.215 bytes)\n";
        } else if ($current['DATA_TYPE'] === 'longblob') {
            echo "4GB (4.294.967.295 bytes)\n";
        }
        echo "\n";
    }
    
    // Executar migration
    if ($current['DATA_TYPE'] === 'mediumblob') {
        echo "⚙️  Executando ALTER TABLE...\n";
        $db->exec("
            ALTER TABLE pops_its_registros 
            MODIFY COLUMN arquivo LONGBLOB NOT NULL 
            COMMENT 'Arquivo do documento (suporta até 50MB para PPT/PPTX)'
        ");
        echo "✅ Coluna alterada com sucesso!\n\n";
    } else if ($current['DATA_TYPE'] === 'longblob') {
        echo "✅ Coluna já está como LONGBLOB. Nenhuma alteração necessária.\n\n";
    }
    
    // Verificar estado final
    echo "📊 Estado final da coluna:\n";
    $stmt = $db->query("
        SELECT 
            COLUMN_NAME,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH
        FROM 
            INFORMATION_SCHEMA.COLUMNS
        WHERE 
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'pops_its_registros'
            AND COLUMN_NAME = 'arquivo'
    ");
    $final = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($final) {
        echo "   - Tipo final: {$final['DATA_TYPE']}\n";
        echo "   - Capacidade: 4GB (4.294.967.295 bytes)\n";
        echo "\n";
    }
    
    echo "🎉 Migration concluída com sucesso!\n";
    echo "📄 Arquivos PPT/PPTX de até 50MB agora são suportados.\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao executar migration: " . $e->getMessage() . "\n";
    exit(1);
}
