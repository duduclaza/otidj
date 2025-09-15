<?php
// Teste de conexão com banco de dados em produção
require_once __DIR__ . '/bootstrap.php';

echo "<h2>Teste de Conexão - SGQ PRO</h2>";

try {
    $environment = Environment::getInstance();
    echo "<p><strong>Ambiente detectado:</strong> " . $environment->getEnvironment() . "</p>";
    
    $config = $environment->getConfig();
    echo "<p><strong>Host do banco:</strong> " . $config['db']['host'] . "</p>";
    echo "<p><strong>Database:</strong> " . $config['db']['database'] . "</p>";
    echo "<p><strong>Usuário:</strong> " . $config['db']['username'] . "</p>";
    
    $db = getDB();
    echo "<p style='color: green;'>✅ Conexão com banco estabelecida com sucesso!</p>";
    
    // Testa consulta simples
    $tables = $db->fetchAll("SHOW TABLES");
    echo "<h3>Tabelas encontradas:</h3><ul>";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>" . $tableName . "</li>";
    }
    echo "</ul>";
    
    // Testa dados das tabelas principais
    echo "<h3>Dados das Tabelas:</h3>";
    
    $filiais = $db->fetchAll("SELECT COUNT(*) as total FROM filiais");
    echo "<p><strong>Filiais:</strong> " . $filiais[0]['total'] . " registros</p>";
    
    $departamentos = $db->fetchAll("SELECT COUNT(*) as total FROM departamentos");
    echo "<p><strong>Departamentos:</strong> " . $departamentos[0]['total'] . " registros</p>";
    
    $fornecedores = $db->fetchAll("SELECT COUNT(*) as total FROM fornecedores");
    echo "<p><strong>Fornecedores:</strong> " . $fornecedores[0]['total'] . " registros</p>";
    
    echo "<h3 style='color: green;'>🎉 Sistema funcionando perfeitamente com banco real!</h3>";
    echo "<p><a href='index.php'>← Voltar ao Sistema</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Erro na conexão:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    if (!isProduction()) {
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>
