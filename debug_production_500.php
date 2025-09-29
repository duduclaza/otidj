<?php
/**
 * SCRIPT DE DIAGNÓSTICO PARA ERRO HTTP 500 EM PRODUÇÃO
 * 
 * Este script deve ser executado diretamente para diagnosticar
 * o problema sem passar pelo sistema de rotas.
 */

// Ativar exibição de erros para diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 DIAGNÓSTICO DE ERRO HTTP 500</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

// 1. Verificar se o arquivo .env existe
echo "<h2>1. Verificação do arquivo .env</h2>";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "✅ Arquivo .env encontrado<br>";
    $envContent = file_get_contents($envPath);
    echo "📄 <strong>Conteúdo do .env:</strong><br>";
    echo "<pre>" . htmlspecialchars($envContent) . "</pre>";
} else {
    echo "❌ Arquivo .env NÃO encontrado em: " . $envPath . "<br>";
}

echo "<hr>";

// 2. Verificar autoload do Composer
echo "<h2>2. Verificação do Composer Autoload</h2>";
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "✅ Autoload encontrado<br>";
    try {
        require_once $autoloadPath;
        echo "✅ Autoload carregado com sucesso<br>";
    } catch (Exception $e) {
        echo "❌ Erro ao carregar autoload: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Autoload NÃO encontrado em: " . $autoloadPath . "<br>";
}

echo "<hr>";

// 3. Verificar carregamento do .env
echo "<h2>3. Verificação do DotEnv</h2>";
try {
    if (class_exists('Dotenv\Dotenv')) {
        echo "✅ Classe Dotenv disponível<br>";
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
        echo "✅ Variáveis de ambiente carregadas<br>";
        
        // Mostrar algumas variáveis importantes (sem senhas)
        echo "<strong>Variáveis carregadas:</strong><br>";
        echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'não definido') . "<br>";
        echo "APP_ENV: " . ($_ENV['APP_ENV'] ?? 'não definido') . "<br>";
        echo "APP_DEBUG: " . ($_ENV['APP_DEBUG'] ?? 'não definido') . "<br>";
        echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'não definido') . "<br>";
        echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'não definido') . "<br>";
    } else {
        echo "❌ Classe Dotenv não disponível<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao carregar DotEnv: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 4. Testar conexão com banco de dados
echo "<h2>4. Teste de Conexão com Banco de Dados</h2>";
try {
    if (class_exists('App\Config\Database')) {
        echo "✅ Classe Database disponível<br>";
        
        $db = App\Config\Database::getInstance();
        echo "✅ Instância do banco criada<br>";
        
        // Testar uma query simples
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        if ($result && $result['test'] == 1) {
            echo "✅ Conexão com banco funcionando<br>";
        } else {
            echo "❌ Falha na query de teste<br>";
        }
        
    } else {
        echo "❌ Classe Database não disponível<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro de conexão com banco: " . $e->getMessage() . "<br>";
    echo "📋 <strong>Detalhes do erro:</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";

// 5. Verificar se as classes principais existem
echo "<h2>5. Verificação das Classes Principais</h2>";
$classes = [
    'App\Core\Router',
    'App\Services\PermissionService',
    'App\Controllers\AdminController',
    'App\Controllers\HomeController',
    'App\Middleware\PermissionMiddleware'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ $class disponível<br>";
    } else {
        echo "❌ $class NÃO disponível<br>";
    }
}

echo "<hr>";

// 6. Testar inicialização de sessão
echo "<h2>6. Teste de Sessão</h2>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "✅ Sessão iniciada com sucesso<br>";
    } else {
        echo "✅ Sessão já ativa<br>";
    }
    echo "📋 Session ID: " . session_id() . "<br>";
} catch (Exception $e) {
    echo "❌ Erro ao iniciar sessão: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 7. Verificar permissões de arquivo
echo "<h2>7. Verificação de Permissões</h2>";
$paths = [
    __DIR__ . '/../storage/logs',
    __DIR__ . '/../vendor',
    __DIR__ . '/..',
];

foreach ($paths as $path) {
    if (is_readable($path)) {
        echo "✅ $path é legível<br>";
    } else {
        echo "❌ $path NÃO é legível<br>";
    }
    
    if (is_writable($path)) {
        echo "✅ $path é gravável<br>";
    } else {
        echo "⚠️ $path NÃO é gravável<br>";
    }
}

echo "<hr>";

// 8. Informações do servidor
echo "<h2>8. Informações do Servidor</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'não disponível') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'não disponível') . "<br>";
echo "Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'não disponível') . "<br>";

echo "<hr>";

// 9. Teste do Router
echo "<h2>9. Teste do Sistema de Rotas</h2>";
try {
    if (class_exists('App\Core\Router')) {
        $router = new App\Core\Router(__DIR__);
        echo "✅ Router criado com sucesso<br>";
        
        // Testar uma rota simples
        $router->get('/test', function() {
            return "Teste OK";
        });
        echo "✅ Rota de teste adicionada<br>";
        
    } else {
        echo "❌ Classe Router não disponível<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no Router: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 10. Logs de erro recentes
echo "<h2>10. Logs de Erro Recentes</h2>";
$logPath = __DIR__ . '/../storage/logs/error.log';
if (file_exists($logPath)) {
    echo "✅ Arquivo de log encontrado<br>";
    $logContent = file_get_contents($logPath);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -20); // Últimas 20 linhas
    
    echo "<strong>Últimas 20 linhas do log:</strong><br>";
    echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow-y: auto;'>";
    echo htmlspecialchars(implode("\n", $recentLines));
    echo "</pre>";
} else {
    echo "⚠️ Arquivo de log não encontrado em: $logPath<br>";
}

echo "<hr>";
echo "<h2>✅ DIAGNÓSTICO CONCLUÍDO</h2>";
echo "<p>Execute este script acessando: <strong>https://djbr.sgqoti.com.br/debug_production_500.php</strong></p>";
?>
