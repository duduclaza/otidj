<?php
echo "Sistema SGQ-OTI DJ funcionando!<br>";
echo "Data/Hora: " . date('d/m/Y H:i:s') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Servidor: " . $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' . "<br>";

// Testar se o autoload funciona
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "✅ Autoload encontrado<br>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoload carregado com sucesso<br>";
} else {
    echo "❌ Autoload não encontrado<br>";
}

// Testar se o .env funciona
if (file_exists(__DIR__ . '/../.env')) {
    echo "✅ Arquivo .env encontrado<br>";
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
        echo "✅ .env carregado com sucesso<br>";
        echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'N/A') . "<br>";
    } catch (Exception $e) {
        echo "❌ Erro ao carregar .env: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Arquivo .env não encontrado<br>";
}

// Testar conexão com banco
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'test';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    echo "✅ Conexão com banco de dados OK<br>";
} catch (Exception $e) {
    echo "❌ Erro de conexão com banco: " . $e->getMessage() . "<br>";
}

echo "<br><a href='/public/'>Ir para o sistema principal</a>";
?>
