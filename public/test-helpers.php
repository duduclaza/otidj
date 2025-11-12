<?php
// Teste rápido de helpers
require_once __DIR__ . '/../vendor/autoload.php';

echo "Testando funções helpers...\n\n";

// Teste 1: Função e()
try {
    $test = e("<script>alert('xss')</script>");
    echo "✅ Função e() funcionando: " . $test . "\n";
} catch (Error $e) {
    echo "❌ Erro na função e(): " . $e->getMessage() . "\n";
}

// Teste 2: Função env()
try {
    $env = env('APP_NAME', 'Default');
    echo "✅ Função env() funcionando: " . $env . "\n";
} catch (Error $e) {
    echo "❌ Erro na função env(): " . $e->getMessage() . "\n";
}

// Teste 3: Verificar carregamento de .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
    echo "✅ Arquivo .env carregado\n";
    echo "   APP_NAME: " . ($_ENV['APP_NAME'] ?? 'não definido') . "\n";
    echo "   DB_HOST: " . ($_ENV['DB_HOST'] ?? 'não definido') . "\n";
} catch (Exception $e) {
    echo "❌ Erro ao carregar .env: " . $e->getMessage() . "\n";
}

// Teste 4: Conexão com banco
try {
    $db = App\Config\Database::getInstance();
    echo "✅ Conexão com banco estabelecida\n";
    
    // Testar query simples
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Total de usuários: " . $result['total'] . "\n";
} catch (Exception $e) {
    echo "❌ Erro de conexão com banco: " . $e->getMessage() . "\n";
}

echo "\n✅ Todos os testes concluídos!\n";
echo "\nPode deletar este arquivo após verificar o resultado.\n";
