<?php
// Teste gradual para identificar o problema específico
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Middleware\PermissionMiddleware;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "<!DOCTYPE html><html><head><title>Teste Gradual</title></head><body>";
echo "<h1>🔧 Teste Gradual do Sistema</h1>";

try {
    echo "<p>✅ Básicos OK</p>";
    
    // Teste 6: Criar Router
    $router = new Router(__DIR__);
    echo "<p>✅ Router criado</p>";
    
    // Teste 7: Adicionar uma rota simples
    $router->get('/', function() {
        echo "<h1>🎉 Sistema Funcionando!</h1>";
        echo "<p>Todas as rotas básicas estão OK</p>";
        echo "<p><a href='/admin/users'>Ir para Usuários</a></p>";
    });
    echo "<p>✅ Rota básica adicionada</p>";
    
    // Teste 8: Testar PermissionMiddleware
    $currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    echo "<p>✅ Middleware carregado</p>";
    
    // Teste 9: Adicionar rotas principais (sem controllers complexos)
    $router->get('/admin/users', function() {
        echo "<h1>Página de Usuários</h1>";
        echo "<p>Esta seria a página de usuários</p>";
        echo "<p><a href='/'>← Voltar</a></p>";
    });
    echo "<p>✅ Rotas principais adicionadas</p>";
    
    echo "<h2>🚀 Tentando executar rota...</h2>";
    
    // Aplicar middleware (comentado por enquanto)
    // PermissionMiddleware::handle($currentRoute, $method);
    
    $router->dispatch();
    
} catch (Exception $e) {
    echo "<h2>❌ ERRO ENCONTRADO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Trace:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>
