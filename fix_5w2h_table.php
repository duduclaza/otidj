<?php
// Script temporário para corrigir tabela 5W2H
// EXECUTE APENAS UMA VEZ e depois DELETE este arquivo

require_once __DIR__ . '/src/Config/Database.php';

try {
    $db = App\Config\Database::getInstance();
    
    echo "<h2>Corrigindo tabela 5W2H...</h2>\n";
    
    // 1. Corrigir ENUM da tabela existente
    echo "<p>1. Corrigindo ENUM da coluna status...</p>\n";
    $db->exec("ALTER TABLE planos_5w2h MODIFY COLUMN status ENUM('pendente', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'pendente'");
    echo "<p style='color: green;'>✅ ENUM corrigido!</p>\n";
    
    // 2. Verificar estrutura
    echo "<p>2. Verificando estrutura da tabela...</p>\n";
    $stmt = $db->query("DESCRIBE planos_5w2h");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Inserir dados de exemplo se não existirem
    echo "<p>3. Verificando dados de exemplo...</p>\n";
    $stmt = $db->query("SELECT COUNT(*) as total FROM planos_5w2h");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($count == 0) {
        echo "<p>Inserindo dados de exemplo...</p>\n";
        $stmt = $db->prepare("
            INSERT INTO planos_5w2h (
                titulo, what, why, who_id, when_inicio, where_local, how, how_much, 
                setor_id, status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Exemplo 1
        $stmt->execute([
            'Implementar Sistema de Backup Automático',
            'Implementar sistema de backup automático para todos os dados críticos do sistema SGQ',
            'Para garantir a segurança dos dados e evitar perdas em caso de falhas no sistema',
            1, // user_id
            '2024-01-15',
            'Servidor de Produção',
            'Configurar scripts automatizados que executem backup diário às 02:00h, com retenção de 30 dias',
            2500.00,
            1, // departamento_id
            'pendente',
            1 // created_by
        ]);
        
        // Exemplo 2
        $stmt->execute([
            'Treinamento Equipe 5W2H',
            'Realizar treinamento da equipe sobre metodologia 5W2H',
            'Para padronizar o uso da metodologia e melhorar a qualidade dos planos de ação',
            1,
            '2024-01-20',
            'Sala de Treinamento',
            'Apresentação teórica seguida de exercícios práticos com casos reais',
            800.00,
            1,
            'em_andamento',
            1
        ]);
        
        echo "<p style='color: green;'>✅ Dados de exemplo inseridos!</p>\n";
    } else {
        echo "<p style='color: blue;'>ℹ️ Já existem {$count} registros na tabela.</p>\n";
    }
    
    echo "<h3 style='color: green;'>🎉 TABELA 5W2H CORRIGIDA COM SUCESSO!</h3>";
    echo "<p><strong>Agora você pode:</strong></p>";
    echo "<ul>";
    echo "<li>Testar o formulário 5W2H</li>";
    echo "<li>Criar novos planos de ação</li>";
    echo "<li>DELETAR este arquivo (fix_5w2h_table.php)</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Tente executar a query manualmente no phpMyAdmin.</p>";
}
?>
