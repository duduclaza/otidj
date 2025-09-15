<?php
// Exemplo de uso do sistema SGQ PRO com detecção automática de ambiente

require_once __DIR__ . '/bootstrap.php';

// Exemplo 1: Verificar o ambiente atual
echo "<h2>Ambiente Atual: " . Environment::getInstance()->getEnvironment() . "</h2>";

// Exemplo 2: Usar a conexão com banco de dados
try {
    $db = getDB();
    
    // Exemplo de consulta simples
    $result = $db->fetchAll("SHOW TABLES");
    echo "<h3>Tabelas no banco:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro na conexão: " . $e->getMessage() . "</p>";
}

// Exemplo 3: Usar configurações do ambiente
echo "<h3>Configurações do Banco:</h3>";
echo "<pre>";
print_r(config('db'));
echo "</pre>";

// Exemplo 4: Verificar se está em produção
if (isProduction()) {
    echo "<p style='color: green;'>Sistema rodando em PRODUÇÃO</p>";
} else {
    echo "<p style='color: blue;'>Sistema rodando em DESENVOLVIMENTO</p>";
}

// Exemplo 5: Debug (só funciona em desenvolvimento)
debug("Esta mensagem só aparece em desenvolvimento", false);
?>
