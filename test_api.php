<?php
// Teste direto das APIs
require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Controllers\ApiController;

echo "<h2>Teste das APIs de Setores e Filiais</h2>";

$api = new ApiController();

echo "<h3>Testando /api/setores:</h3>";
ob_start();
$api->getSetores();
$setoresResponse = ob_get_clean();
echo "<pre>" . htmlspecialchars($setoresResponse) . "</pre>";

echo "<h3>Testando /api/filiais:</h3>";
ob_start();
$api->getFiliais();
$filiaisResponse = ob_get_clean();
echo "<pre>" . htmlspecialchars($filiaisResponse) . "</pre>";

// Teste direto no banco
echo "<h3>Teste direto no banco:</h3>";
try {
    $db = App\Config\Database::getInstance();
    
    echo "<h4>Tabelas existentes:</h4>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>" . print_r($tables, true) . "</pre>";
    
    echo "<h4>Usuários com setores:</h4>";
    $stmt = $db->query("SELECT DISTINCT setor FROM users WHERE setor IS NOT NULL AND setor <> '' ORDER BY setor");
    $setores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($setores, true) . "</pre>";
    
    echo "<h4>Usuários com filiais:</h4>";
    $stmt = $db->query("SELECT DISTINCT filial FROM users WHERE filial IS NOT NULL AND filial <> '' ORDER BY filial");
    $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($filiais, true) . "</pre>";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
