<?php
// Teste da classe Database::getInstance()
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste Database::getInstance()</h1>";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p>✅ Autoload OK</p>";

    // Carregar .env
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "<p>✅ .env carregado. DB_HOST = " . ($_ENV['DB_HOST'] ?? 'NÃO DEFINIDO') . "</p>";

    require_once __DIR__ . '/../src/Config/Database.php';
    echo "<p>✅ Database.php carregado</p>";

    $pdo = App\Config\Database::getInstance();
    echo "<p>✅ Database::getInstance() retornou conexão</p>";

    $stmt = $pdo->query('SELECT 1 as teste');
    $row = $stmt->fetch();
    echo "<p>✅ SELECT 1 executado. Resultado: " . htmlspecialchars(json_encode($row)) . "</p>";

    echo "<h2 style='color:green;'>Tudo OK com Database::getInstance()</h2>";
} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Erro PDO:</h2>";
    echo "<p>Mensagem: " . $e->getMessage() . "</p>";
} catch (Throwable $e) {
    echo "<h2 style='color:red;'>Erro:</h2>";
    echo "<p>Tipo: " . get_class($e) . "</p>";
    echo "<p>Mensagem: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
}
