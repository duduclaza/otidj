<?php
// Teste de carregamento do .env
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Carregamento do .env</h1>";

echo "<h2>1. Verificar arquivo</h2>";
$envPath = __DIR__ . '/../.env';
echo "<p><strong>Caminho:</strong> " . $envPath . "</p>";
echo "<p><strong>Existe:</strong> " . (file_exists($envPath) ? '✅ SIM' : '❌ NÃO') . "</p>";

if (file_exists($envPath)) {
    echo "<p><strong>Tamanho:</strong> " . filesize($envPath) . " bytes</p>";
    echo "<p><strong>Permissões:</strong> " . substr(sprintf('%o', fileperms($envPath)), -4) . "</p>";
    
    echo "<h3>Primeiras linhas do .env:</h3>";
    $lines = array_slice(file($envPath), 0, 5);
    echo "<pre>" . htmlspecialchars(implode('', $lines)) . "</pre>";
}

echo "<h2>2. Carregar Autoload</h2>";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p>✅ Autoload OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>3. Carregar Dotenv</h2>";
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "<p>✅ Dotenv carregado!</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar: " . $e->getMessage() . "</p>";
    echo "<p><strong>Tipo:</strong> " . get_class($e) . "</p>";
    exit;
}

echo "<h2>4. Verificar variáveis</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Variável</th><th>Valor</th></tr>";

$vars = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_NAME'];
foreach ($vars as $var) {
    $value = $_ENV[$var] ?? '❌ NÃO DEFINIDA';
    if ($var === 'DB_PASSWORD' && $value !== '❌ NÃO DEFINIDA') {
        $value = str_repeat('*', strlen($value)); // Ocultar senha
    }
    echo "<tr><td><strong>$var</strong></td><td>$value</td></tr>";
}
echo "</table>";

echo "<h2>5. Testar conexão com banco</h2>";
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $dbname = $_ENV['DB_DATABASE'] ?? 'sgqpro';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    echo "<p>Conectando em: $username@$host:$port/$dbname</p>";
    
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    
    echo "<p>✅ <strong>CONEXÃO COM BANCO OK!</strong></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}

echo "<h2 style='color: green;'>Teste concluído!</h2>";
?>
