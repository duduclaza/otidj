<?php
/**
 * Script para atualizar campos calculados dos toners existentes
 * Execute este script uma vez para atualizar todos os toners
 * Acesse: https://djbr.sgqoti.com.br/run_update_toners_fields.php
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
    <title>Atualizar Campos Calculados - Toners</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid green; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid red; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid blue; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h1>🔧 Atualização de Campos Calculados - Toners</h1>
";

try {
    $db = Database::getInstance();
    echo "<div class='success'>✅ Conectado ao banco de dados</div>";
    
    // Contar toners antes da atualização
    echo "<h2>📊 Status ANTES da Atualização:</h2>";
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_toners,
            SUM(CASE WHEN gramatura IS NOT NULL THEN 1 ELSE 0 END) as com_gramatura,
            SUM(CASE WHEN gramatura_por_folha IS NOT NULL THEN 1 ELSE 0 END) as com_gram_folha,
            SUM(CASE WHEN custo_por_folha IS NOT NULL THEN 1 ELSE 0 END) as com_custo_folha,
            SUM(CASE WHEN gramatura IS NULL AND peso_cheio IS NOT NULL AND peso_vazio IS NOT NULL THEN 1 ELSE 0 END) as sem_gramatura
        FROM toners
    ");
    $antes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Métrica</th><th>Quantidade</th></tr>";
    echo "<tr><td>Total de Toners</td><td><strong>" . $antes['total_toners'] . "</strong></td></tr>";
    echo "<tr><td>Com Gramatura</td><td>" . $antes['com_gramatura'] . "</td></tr>";
    echo "<tr><td>Com Gramatura/Folha</td><td>" . $antes['com_gram_folha'] . "</td></tr>";
    echo "<tr><td>Com Custo/Folha</td><td>" . $antes['com_custo_folha'] . "</td></tr>";
    echo "<tr><td><strong>Sem Gramatura (serão atualizados)</strong></td><td><strong style='color: red;'>" . $antes['sem_gramatura'] . "</strong></td></tr>";
    echo "</table>";
    
    if ($antes['sem_gramatura'] == 0) {
        echo "<div class='info'>ℹ️ Todos os toners já possuem os campos calculados. Nenhuma atualização necessária.</div>";
    } else {
        echo "<h2>⚙️ Executando Atualização...</h2>";
        
        // Executar atualização
        $updateSQL = "
            UPDATE toners 
            SET 
                gramatura = peso_cheio - peso_vazio,
                gramatura_por_folha = (peso_cheio - peso_vazio) / capacidade_folhas,
                custo_por_folha = preco_toner / capacidade_folhas
            WHERE 
                peso_cheio IS NOT NULL 
                AND peso_vazio IS NOT NULL 
                AND capacidade_folhas > 0
                AND preco_toner IS NOT NULL
                AND (gramatura IS NULL OR gramatura_por_folha IS NULL OR custo_por_folha IS NULL)
        ";
        
        $updated = $db->exec($updateSQL);
        
        echo "<div class='success'>✅ Atualização concluída! <strong>$updated</strong> toners foram atualizados.</div>";
        
        // Contar toners após atualização
        echo "<h2>📊 Status DEPOIS da Atualização:</h2>";
        $stmt = $db->query("
            SELECT 
                COUNT(*) as total_toners,
                SUM(CASE WHEN gramatura IS NOT NULL THEN 1 ELSE 0 END) as com_gramatura,
                SUM(CASE WHEN gramatura_por_folha IS NOT NULL THEN 1 ELSE 0 END) as com_gram_folha,
                SUM(CASE WHEN custo_por_folha IS NOT NULL THEN 1 ELSE 0 END) as com_custo_folha,
                SUM(CASE WHEN gramatura IS NULL AND peso_cheio IS NOT NULL AND peso_vazio IS NOT NULL THEN 1 ELSE 0 END) as sem_gramatura
            FROM toners
        ");
        $depois = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Métrica</th><th>Quantidade</th></tr>";
        echo "<tr><td>Total de Toners</td><td><strong>" . $depois['total_toners'] . "</strong></td></tr>";
        echo "<tr><td>Com Gramatura</td><td><strong style='color: green;'>" . $depois['com_gramatura'] . "</strong></td></tr>";
        echo "<tr><td>Com Gramatura/Folha</td><td><strong style='color: green;'>" . $depois['com_gram_folha'] . "</strong></td></tr>";
        echo "<tr><td>Com Custo/Folha</td><td><strong style='color: green;'>" . $depois['com_custo_folha'] . "</strong></td></tr>";
        echo "<tr><td>Sem Gramatura</td><td>" . $depois['sem_gramatura'] . "</td></tr>";
        echo "</table>";
        
        // Mostrar exemplos de toners atualizados
        echo "<h2>📋 Exemplos de Toners Atualizados:</h2>";
        $stmt = $db->query("
            SELECT modelo, peso_cheio, peso_vazio, gramatura, capacidade_folhas, 
                   gramatura_por_folha, preco_toner, custo_por_folha 
            FROM toners 
            WHERE gramatura IS NOT NULL 
            LIMIT 5
        ");
        $exemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($exemplos) > 0) {
            echo "<table>";
            echo "<tr><th>Modelo</th><th>Gramatura</th><th>Gram/Folha</th><th>Custo/Folha</th></tr>";
            foreach ($exemplos as $ex) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($ex['modelo']) . "</td>";
                echo "<td>" . number_format($ex['gramatura'], 2) . "g</td>";
                echo "<td>" . number_format($ex['gramatura_por_folha'], 4) . "g</td>";
                echo "<td>R$ " . number_format($ex['custo_por_folha'], 4) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    echo "<hr>";
    echo "<div class='success'><strong>✅ Processo concluído com sucesso!</strong></div>";
    echo "<p><a href='/toners/cadastro' style='display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>← Voltar para Cadastro de Toners</a></p>";
    
} catch (Exception $e) {
    echo "<div class='error'><h2>❌ ERRO</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "</body></html>";
