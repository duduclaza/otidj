<?php
// No-cache headers for all responses
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Display errors only if APP_DEBUG=true
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Project base path
$basePath = dirname(__DIR__);

// Start session for flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Composer autoload
require $basePath . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable($basePath);
$dotenv->safeLoad();

if (filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// Run auto-migrations
use App\Core\Migration;
use App\Core\Router;

try {
    $migration = new Migration();
    $migration->runMigrations();
} catch (\Exception $e) {
    // Log error but don't break the app
    if (filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN)) {
        echo '<pre>Migration Error: ' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
}

$router = new Router(basePath: $basePath);

// Define routes
$router->get('/', [App\Controllers\PageController::class, 'home']);
$router->get('/controle-de-toners', [App\Controllers\PageController::class, 'controleDeToners']);
$router->get('/homologacoes', [App\Controllers\PageController::class, 'homologacoes']);
$router->get('/amostragens', [App\Controllers\PageController::class, 'amostragens']);
$router->get('/garantias', [App\Controllers\PageController::class, 'garantias']);
$router->get('/controle-de-descartes', [App\Controllers\PageController::class, 'controleDeDescartes']);
$router->get('/femea', [App\Controllers\PageController::class, 'femea']);
$router->get('/pops-e-its', [App\Controllers\PageController::class, 'popsEIts']);
$router->get('/fluxogramas', [App\Controllers\PageController::class, 'fluxogramas']);
$router->get('/melhoria-continua', [App\Controllers\PageController::class, 'melhoriaContinua']);
$router->get('/controle-de-rc', [App\Controllers\PageController::class, 'controleDeRc']);

// Registros Gerais (com abas e forms)
$router->get('/registros-gerais', [App\Controllers\RegistrosController::class, 'index']);
$router->post('/registros-gerais/filiais', [App\Controllers\RegistrosController::class, 'storeFilial']);
$router->post('/registros-gerais/departamentos', [App\Controllers\RegistrosController::class, 'storeDepartamento']);
$router->post('/registros-gerais/fornecedores', [App\Controllers\RegistrosController::class, 'storeFornecedor']);
$router->post('/registros-gerais/parametros', [App\Controllers\RegistrosController::class, 'storeParametro']);

// Configurações
$router->get('/configuracoes', [App\Controllers\ConfigController::class, 'index']);
$router->post('/configuracoes/setup-banco', [App\Controllers\ConfigController::class, 'setupBanco']);

// Dispatch request
$router->dispatch();
