<?php
/**
 * Script para executar migration: Upgrade POPs e ITs arquivo para LONGBLOB
 * Execute: php upgrade_pops_arquivo.php
 */

// Carregar configurações
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config/Database.php';

try {
    echo "🔄 Iniciando migration: Upgrade arquivo para LONGBLOB...\n\n";
    
    $db = \App\Config\Database::getInstance()->getConnection();
    
    // Verificar estado atual
    echo "📊 Estado atual da coluna:\n";
    $stmt = $db->query("
        SELECT 
            COLUMN_NAME,
            DATA_TYPE
        FROM 
            INFORMATION_SCHEMA.COLUMNS
        WHERE 
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'pops_its_registros'
            AND COLUMN_NAME = 'arquivo'
    ");
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($current) {
        echo "   - Tipo atual: " . strtoupper($current['DATA_TYPE']) . "\n";
        if ($current['DATA_TYPE'] === 'mediumblob') {
            echo "   - Capacidade: 16MB\n";
        } else if ($current['DATA_TYPE'] === 'longblob') {
            echo "   - Capacidade: 4GB\n";
        }
        echo "\n";
    } else {
        echo "   ⚠️  Coluna 'arquivo' não encontrada!\n\n";
        exit(1);
    }
    
    // Executar migration
    if ($current['DATA_TYPE'] === 'mediumblob') {
        echo "⚙️  Executando ALTER TABLE (pode demorar alguns segundos)...\n";
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
            DATA_TYPE
        FROM 
            INFORMATION_SCHEMA.COLUMNS
        WHERE 
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'pops_its_registros'
            AND COLUMN_NAME = 'arquivo'
    ");
    $final = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($final) {
        echo "   - Tipo final: " . strtoupper($final['DATA_TYPE']) . "\n";
        echo "   - Capacidade: 4GB\n";
        echo "\n";
    }
    
    echo "🎉 Migration concluída com sucesso!\n";
    echo "📄 Arquivos PPT/PPTX de até 50MB agora são suportados.\n";
    echo "\n";
    echo "💡 Você pode testar fazendo upload de um arquivo PPT/PPTX no módulo POPs e ITs.\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao executar migration: " . $e->getMessage() . "\n";
    echo "\n";
    echo "💡 Você também pode executar manualmente o SQL:\n";
    echo "   ALTER TABLE pops_its_registros MODIFY COLUMN arquivo LONGBLOB NOT NULL;\n";
    exit(1);
}
