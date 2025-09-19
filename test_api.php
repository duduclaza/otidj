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
    try {
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<pre>" . print_r($tables, true) . "</pre>";
    } catch (Exception $e) {
        // Para SQLite, usar uma query diferente
        try {
            $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<pre>" . print_r($tables, true) . "</pre>";
        } catch (Exception $e2) {
            echo "Erro ao listar tabelas: " . $e2->getMessage();
        }
    }
    
    echo "<h4>Usuários com setores:</h4>";
    $stmt = $db->query("SELECT DISTINCT setor FROM users WHERE setor IS NOT NULL AND setor <> '' ORDER BY setor");
    $setores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($setores, true) . "</pre>";
    
    echo "<h4>Usuários com filiais:</h4>";
    $stmt = $db->query("SELECT DISTINCT filial FROM users WHERE filial IS NOT NULL AND filial <> '' ORDER BY filial");
    $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($filiais, true) . "</pre>";
    
    // Testar tabelas específicas
    echo "<h4>Teste tabela departments:</h4>";
    try {
        $stmt = $db->query("SELECT * FROM departments LIMIT 5");
        $deps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($deps, true) . "</pre>";
    } catch (Exception $e) {
        echo "Tabela departments não existe ou erro: " . $e->getMessage() . "<br>";
    }
    
    echo "<h4>Teste tabela departamentos:</h4>";
    try {
        $stmt = $db->query("SELECT * FROM departamentos LIMIT 5");
        $deps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($deps, true) . "</pre>";
    } catch (Exception $e) {
        echo "Tabela departamentos não existe ou erro: " . $e->getMessage() . "<br>";
    }
    
    echo "<h4>Teste tabela filiais:</h4>";
    try {
        $stmt = $db->query("SELECT * FROM filiais LIMIT 5");
        $fils = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($fils, true) . "</pre>";
    } catch (Exception $e) {
        echo "Tabela filiais não existe ou erro: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
