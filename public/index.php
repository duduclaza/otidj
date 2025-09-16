<?php
// No-cache headers for all responses
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Always show errors for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
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

// Load helpers
require_once $basePath . '/src/Support/helpers.php';

// Run auto-migrations
use App\Core\Migration;
use App\Core\Router;

try {
    $migration = new Migration();
    $migration->runMigrations();
} catch (\Exception $e) {
    // Skip migrations if connection limit exceeded, but don't break the app
    if (strpos($e->getMessage(), 'max_connections_per_hour') === false) {
        echo '<pre>Migration Error: ' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<pre>Stack trace: ' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
}

$router = new Router(basePath: $basePath);

// Define routes
$router->get('/', [App\Controllers\PageController::class, 'home']);
// Controle de Toners - páginas separadas
$router->get('/toners/cadastro', [App\Controllers\TonersController::class, 'cadastro']);
$router->post('/toners/cadastro', [App\Controllers\TonersController::class, 'store']);
$router->post('/toners/cadastro/edit', [App\Controllers\TonersController::class, 'update']);
$router->post('/toners/cadastro/delete', [App\Controllers\TonersController::class, 'delete']);
$router->get('/toners/retornados', [App\Controllers\TonersController::class, 'retornados']);
$router->post('/toners/retornados', [App\Controllers\TonersController::class, 'storeRetornado']);
$router->delete('/toners/retornados/delete/{id}', [App\Controllers\TonersController::class, 'deleteRetornado']);
$router->post('/toners/retornados/import-row', [App\Controllers\TonersController::class, 'importRow']);
$router->get('/api/toner', [App\Controllers\TonersController::class, 'getTonerData']);
$router->get('/api/parameters', [App\Controllers\TonersController::class, 'getParameters']);
$router->post('/toners/import', [App\Controllers\TonersController::class, 'import']);

$router->get('/homologacoes', [App\Controllers\PageController::class, 'homologacoes']);
$router->get('/toners/amostragens', [App\Controllers\AmostragemController::class, 'index']);
$router->get('/garantias', [App\Controllers\PageController::class, 'garantias']);
$router->get('/controle-de-descartes', [App\Controllers\PageController::class, 'controleDeDescartes']);
$router->get('/femea', [App\Controllers\PageController::class, 'femea']);
$router->get('/pops-e-its', [App\Controllers\PageController::class, 'popsEIts']);
$router->get('/fluxogramas', [App\Controllers\PageController::class, 'fluxogramas']);
$router->get('/melhoria-continua', [App\Controllers\PageController::class, 'melhoriaContinua']);
$router->get('/controle-de-rc', [App\Controllers\PageController::class, 'controleDeRc']);

// Registros Gerais - páginas separadas
$router->get('/registros/filiais', [App\Controllers\RegistrosController::class, 'filiais']);
$router->post('/registros/filiais', [App\Controllers\RegistrosController::class, 'storeFilial']);
$router->post('/registros/filiais/edit', [App\Controllers\RegistrosController::class, 'updateFilial']);
$router->post('/registros/filiais/delete', [App\Controllers\RegistrosController::class, 'deleteFilial']);

$router->get('/registros/departamentos', [App\Controllers\RegistrosController::class, 'departamentos']);
$router->post('/registros/departamentos', [App\Controllers\RegistrosController::class, 'storeDepartamento']);
$router->post('/registros/departamentos/edit', [App\Controllers\RegistrosController::class, 'updateDepartamento']);
$router->post('/registros/departamentos/delete', [App\Controllers\RegistrosController::class, 'deleteDepartamento']);

$router->get('/registros/fornecedores', [App\Controllers\RegistrosController::class, 'fornecedores']);
$router->post('/registros/fornecedores', [App\Controllers\RegistrosController::class, 'storeFornecedor']);
$router->post('/registros/fornecedores/edit', [App\Controllers\RegistrosController::class, 'updateFornecedor']);
$router->post('/registros/fornecedores/delete', [App\Controllers\RegistrosController::class, 'deleteFornecedor']);

$router->get('/registros/parametros', [App\Controllers\RegistrosController::class, 'parametros']);
$router->post('/registros/parametros', [App\Controllers\RegistrosController::class, 'storeParametro']);
$router->post('/registros/parametros/edit', [App\Controllers\RegistrosController::class, 'updateParametro']);
$router->post('/registros/parametros/delete', [App\Controllers\RegistrosController::class, 'deleteParametro']);

// Configurações
$router->get('/configuracoes', [App\Controllers\ConfigController::class, 'index']);
$router->post('/configuracoes/setup-banco', [App\Controllers\ConfigController::class, 'setupBanco']);

// Dispatch request
try {
    $router->dispatch();
} catch (\Exception $e) {
    echo '<pre>Router Error: ' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<pre>Stack trace: ' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    http_response_code(500);
}
