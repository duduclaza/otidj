<?php
/**
 * Script para executar migration de produtos em garantias_itens
 * Execute: php executar_migration_produtos.php
 */

require __DIR__ . '/vendor/autoload.php';

// Carregar configurações
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Conectar ao banco
    $db = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "🔌 Conectado ao banco de dados: {$_ENV['DB_NAME']}\n\n";
    
    // Ler arquivo de migration
    $migrationFile = __DIR__ . '/database/migrations/add_produto_fields_garantias_itens.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration não encontrada: {$migrationFile}");
    }
    
    echo "📄 Lendo migration: add_produto_fields_garantias_itens.sql\n\n";
    
    $sql = file_get_contents($migrationFile);
    
    // Remover comentários e linhas vazias
    $sql = preg_replace('/^--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Separar por comandos SET e ALTER
    $statements = [];
    $currentStatement = '';
    
    foreach (explode("\n", $sql) as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $currentStatement .= $line . "\n";
        
        // Detectar fim de statement
        if (preg_match('/;$/', $line) || 
            preg_match('/DEALLOCATE PREPARE stmt/', $line)) {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }
    
    // Executar cada statement
    $executed = 0;
    foreach ($statements as $index => $statement) {
        if (empty($statement)) continue;
        
        try {
            echo "⚙️ Executando statement " . ($index + 1) . "...\n";
            $db->exec($statement);
            $executed++;
            echo "✅ OK\n\n";
        } catch (PDOException $e) {
            // Ignorar erros de coluna duplicada
            if (strpos($e->getMessage(), 'Duplicate column') !== false ||
                strpos($e->getMessage(), 'Duplicate key') !== false) {
                echo "⚠️ Coluna ou índice já existe (OK)\n\n";
            } else {
                throw $e;
            }
        }
    }
    
    echo "===========================================\n";
    echo "✅ MIGRATION CONCLUÍDA COM SUCESSO!\n";
    echo "===========================================\n\n";
    echo "📊 Statements executados: {$executed}\n\n";
    
    // Verificar colunas
    echo "🔍 Verificando colunas na tabela garantias_itens:\n";
    $stmt = $db->query("SHOW COLUMNS FROM garantias_itens WHERE Field IN ('tipo_produto', 'codigo_produto', 'nome_produto', 'produto_id')");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns) > 0) {
        foreach ($columns as $col) {
            echo "   ✅ {$col['Field']} ({$col['Type']})\n";
        }
    } else {
        echo "   ❌ Colunas não encontradas!\n";
    }
    
    echo "\n✅ Agora você pode recarregar a página de garantias!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
