<?php
// Script para criar as tabelas do SGQ PRO automaticamente
require_once __DIR__ . '/bootstrap.php';

try {
    $db = getDB();
    
    echo "<h2>Configurando Banco de Dados SGQ PRO</h2>";
    
    // Tabela de Filiais
    $sql = "CREATE TABLE IF NOT EXISTS filiais (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $db->query($sql);
    echo "<p>✅ Tabela 'filiais' criada</p>";
    
    // Tabela de Departamentos
    $sql = "CREATE TABLE IF NOT EXISTS departamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $db->query($sql);
    echo "<p>✅ Tabela 'departamentos' criada</p>";
    
    // Tabela de Fornecedores
    $sql = "CREATE TABLE IF NOT EXISTS fornecedores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL UNIQUE,
        contato VARCHAR(255),
        email VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $db->query($sql);
    echo "<p>✅ Tabela 'fornecedores' criada</p>";
    
    // Inserir dados iniciais para Filiais
    $filiais = ['Jundiaí', 'Franca', 'Santos', 'Caçapava', 'Uberlândia', 'Uberaba'];
    foreach ($filiais as $filial) {
        try {
            $db->insert('filiais', ['nome' => $filial]);
        } catch (Exception $e) {
            // Ignora se já existe
        }
    }
    echo "<p>✅ Dados iniciais de filiais inseridos</p>";
    
    // Inserir dados iniciais para Departamentos
    $departamentos = ['Financeiro', 'Faturamento', 'Logística', 'Compras', 'Vendas', 'RH', 'TI', 'Qualidade'];
    foreach ($departamentos as $depto) {
        try {
            $db->insert('departamentos', ['nome' => $depto]);
        } catch (Exception $e) {
            // Ignora se já existe
        }
    }
    echo "<p>✅ Dados iniciais de departamentos inseridos</p>";
    
    // Inserir dados iniciais para Fornecedores
    $fornecedores = [
        ['nome' => 'Fornecedor A', 'contato' => '(11) 1234-5678', 'email' => 'contato@fornecedora.com'],
        ['nome' => 'Fornecedor B', 'contato' => '(11) 8765-4321', 'email' => 'vendas@fornecedorb.com'],
        ['nome' => 'Fornecedor C', 'contato' => '(11) 5555-5555', 'email' => 'comercial@fornecedorc.com']
    ];
    foreach ($fornecedores as $fornecedor) {
        try {
            $db->insert('fornecedores', $fornecedor);
        } catch (Exception $e) {
            // Ignora se já existe
        }
    }
    echo "<p>✅ Dados iniciais de fornecedores inseridos</p>";
    
    echo "<h3 style='color: green;'>🎉 Banco de dados configurado com sucesso!</h3>";
    echo "<p><a href='index.php'>← Voltar ao Sistema</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Erro ao configurar banco:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    if (!isProduction()) {
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>
