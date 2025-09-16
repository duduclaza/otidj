<?php
// Teste direto do endpoint /api/toner
require_once 'src/Config/Database.php';

use App\Config\Database;

try {
    $db = Database::getInstance();
    
    // Verificar se a tabela existe
    $stmt = $db->query("SHOW TABLES LIKE 'toners'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Tabela 'toners' não existe!\n";
        exit;
    }
    
    echo "✅ Tabela 'toners' existe\n";
    
    // Contar registros
    $stmt = $db->query("SELECT COUNT(*) as total FROM toners");
    $count = $stmt->fetch();
    
    echo "📊 Total de toners cadastrados: " . $count['total'] . "\n";
    
    if ($count['total'] > 0) {
        // Mostrar alguns registros
        $stmt = $db->query("SELECT id, modelo, peso_cheio, peso_vazio, preco_toner FROM toners LIMIT 5");
        $toners = $stmt->fetchAll();
        
        echo "\n📋 Primeiros 5 toners:\n";
        foreach ($toners as $toner) {
            echo "- ID: {$toner['id']}, Modelo: {$toner['modelo']}, Peso Cheio: {$toner['peso_cheio']}g\n";
        }
    } else {
        echo "⚠️ Nenhum toner cadastrado na tabela!\n";
        echo "\n💡 Para testar, vamos inserir um toner de exemplo:\n";
        
        $stmt = $db->prepare("INSERT INTO toners (modelo, peso_cheio, peso_vazio, capacidade_folhas, preco_toner, cor, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['HP CF280A', 350.0, 50.0, 2700, 89.90, 'Black', 'Original']);
        
        echo "✅ Toner de exemplo inserido!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
