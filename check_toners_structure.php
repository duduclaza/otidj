<?php
/**
 * Verificar estrutura da tabela toners
 * Acesse: https://djbr.sgqoti.com.br/check_toners_structure.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

// Carregar .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Config\Database;

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Estrutura da Tabela Toners</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid green; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid red; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid blue; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 Estrutura da Tabela Toners</h1>
";

try {
    $db = Database::getInstance();
    echo "<div class='success'>✅ Conectado ao banco de dados</div>";
    
    echo "<h2>📋 Estrutura da Tabela:</h2>";
    $stmt = $db->query("DESCRIBE toners");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        $extra = $col['Extra'];
        $highlight = (stripos($extra, 'GENERATED') !== false || stripos($extra, 'VIRTUAL') !== false || stripos($extra, 'STORED') !== false) 
            ? "style='background-color: #fff3cd; font-weight: bold;'" 
            : "";
        
        echo "<tr $highlight>";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>📊 Exemplo de Toners no Banco:</h2>";
    $stmt = $db->query("SELECT id, modelo, peso_cheio, peso_vazio, gramatura, capacidade_folhas, gramatura_por_folha, preco_toner, custo_por_folha FROM toners LIMIT 5");
    $toners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($toners) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Modelo</th><th>Peso Cheio</th><th>Peso Vazio</th><th>Gramatura</th><th>Cap. Folhas</th><th>Gram/Folha</th><th>Preço</th><th>Custo/Folha</th></tr>";
        foreach ($toners as $t) {
            echo "<tr>";
            echo "<td>" . $t['id'] . "</td>";
            echo "<td>" . htmlspecialchars($t['modelo']) . "</td>";
            echo "<td>" . ($t['peso_cheio'] ?? '-') . "</td>";
            echo "<td>" . ($t['peso_vazio'] ?? '-') . "</td>";
            echo "<td>" . ($t['gramatura'] ?? '<span style="color:red;">NULL</span>') . "</td>";
            echo "<td>" . ($t['capacidade_folhas'] ?? '-') . "</td>";
            echo "<td>" . ($t['gramatura_por_folha'] ?? '<span style="color:red;">NULL</span>') . "</td>";
            echo "<td>" . ($t['preco_toner'] ?? '-') . "</td>";
            echo "<td>" . ($t['custo_por_folha'] ?? '<span style="color:red;">NULL</span>') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>🔧 CREATE TABLE Statement:</h2>";
    $stmt = $db->query("SHOW CREATE TABLE toners");
    $create = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($create['Create Table']);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<div class='error'><h2>❌ ERRO</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "</body></html>";
