<?php
// No-cache headers for all responses
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Load environment first
$basePath = dirname(__DIR__);

// Composer autoload
require $basePath . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable($basePath);
$dotenv->safeLoad();

// Error reporting configuration
$isDebug = $_ENV['APP_DEBUG'] ?? 'false';
$isProduction = ($_ENV['APP_ENV'] ?? 'production') === 'production';

if ($isDebug === 'true' && !$isProduction) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    // Log errors instead of displaying them
    ini_set('log_errors', '1');
    ini_set('error_log', $basePath . '/storage/logs/php_errors.log');
}

// Start session for flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load helpers
require_once $basePath . '/src/Support/helpers.php';

// Run auto-migrations
use App\Core\Migration;
use App\Core\Router;
use App\Setup\MelhoriaContinuaSetup;

try {
    $migration = new Migration();
    $migration->runMigrations();
    // Ensure Melhoria Contínua tables exist
    (new MelhoriaContinuaSetup())->ensure();
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
$router->post('/toners/amostragens', [App\Controllers\AmostragemController::class, 'store']);
$router->delete('/toners/amostragens/{id}', [App\Controllers\AmostragemController::class, 'delete']);
$router->get('/api/users', [App\Controllers\UsersController::class, 'getUsers']);

// Profile routes
$router->get('/profile', [App\Controllers\ProfileController::class, 'index']);
$router->get('/api/profile', [App\Controllers\ProfileController::class, 'getProfile']);
$router->post('/api/profile/photo', [App\Controllers\ProfileController::class, 'updatePhoto']);
$router->post('/api/profile/password', [App\Controllers\ProfileController::class, 'updatePassword']);
$router->get('/api/profile/photo/{id}', [App\Controllers\ProfileController::class, 'getPhoto']);
$router->get('/garantias', [App\Controllers\PageController::class, 'garantias']);
$router->get('/controle-de-descartes', [App\Controllers\PageController::class, 'controleDeDescartes']);
$router->get('/femea', [App\Controllers\PageController::class, 'femea']);
$router->get('/pops-e-its', [App\Controllers\PageController::class, 'popsEIts']);
$router->get('/fluxogramas', [App\Controllers\PageController::class, 'fluxogramas']);
$router->get('/melhoria-continua', [App\Controllers\MelhoriaContinuaController::class, 'index']);
$router->get('/melhoria-continua/solicitacoes', [App\Controllers\MelhoriaContinuaController::class, 'index']);
$router->post('/melhoria-continua/solicitacoes/create', [App\Controllers\MelhoriaContinuaController::class, 'apiCreateSolicitacao']);
$router->get('/melhoria-continua/solicitacoes/list', [App\Controllers\MelhoriaContinuaController::class, 'apiListSolicitacoes']);
$router->get('/melhoria-continua/solicitacoes/{id}/print', [App\Controllers\MelhoriaContinuaController::class, 'print']);
$router->get('/melhoria-continua/solicitacoes/{id}/anexos', [App\Controllers\MelhoriaContinuaController::class, 'viewAnexos']);
$router->get('/melhoria-continua/solicitacoes/{id}/anexos/list', [App\Controllers\MelhoriaContinuaController::class, 'apiListAnexos']);
$router->get('/melhoria-continua/anexos/{id}/download', [App\Controllers\MelhoriaContinuaController::class, 'downloadAnexo']);
$router->get('/melhoria-continua/pendentes', [App\Controllers\MelhoriaContinuaController::class, 'index']);
$router->get('/melhoria-continua/pendentes/list', [App\Controllers\MelhoriaContinuaController::class, 'apiListPendentes']);
$router->post('/melhoria-continua/pendentes/update-status', [App\Controllers\MelhoriaContinuaController::class, 'apiUpdateStatus']);
$router->post('/melhoria-continua/pendentes/delete', [App\Controllers\MelhoriaContinuaController::class, 'apiDelete']);
$router->get('/melhoria-continua/historico', [App\Controllers\MelhoriaContinuaController::class, 'index']);
$router->get('/melhoria-continua/historico/logs', [App\Controllers\MelhoriaContinuaController::class, 'apiLogs']);
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

// Email routes
$router->get('/email/test-connection', [App\Controllers\EmailController::class, 'testConnection']);
$router->post('/email/send-test', [App\Controllers\EmailController::class, 'sendTest']);

// Auth routes
$router->get('/login', [App\Controllers\AuthController::class, 'login']);
$router->post('/auth/login', [App\Controllers\AuthController::class, 'authenticate']);
$router->get('/register', [App\Controllers\AuthController::class, 'register']);
$router->post('/auth/register', [App\Controllers\AuthController::class, 'requestInvitation']);
$router->get('/logout', [App\Controllers\AuthController::class, 'logout']);

// Admin routes
$router->get('/admin', [App\Controllers\AdminController::class, 'dashboard']);
$router->get('/admin/users', [App\Controllers\AdminController::class, 'users']);
$router->get('/admin/invitations', [App\Controllers\AdminController::class, 'invitations']);
$router->post('/admin/users/create', [App\Controllers\AdminController::class, 'createUser']);
$router->post('/admin/users/update', [App\Controllers\AdminController::class, 'updateUser']);
$router->post('/admin/users/delete', [App\Controllers\AdminController::class, 'deleteUser']);
$router->get('/admin/users/{id}/permissions', [App\Controllers\AdminController::class, 'userPermissions']);
$router->post('/admin/permissions/update', [App\Controllers\AdminController::class, 'updatePermissions']);
$router->post('/admin/invitations/approve', [App\Controllers\AdminController::class, 'approveInvitation']);
$router->post('/admin/invitations/reject', [App\Controllers\AdminController::class, 'rejectInvitation']);

// Profiles routes
$router->get('/admin/profiles', [App\Controllers\ProfilesController::class, 'index']);
$router->post('/admin/profiles/create', [App\Controllers\ProfilesController::class, 'create']);
$router->post('/admin/profiles/update', [App\Controllers\ProfilesController::class, 'update']);
$router->post('/admin/profiles/delete', [App\Controllers\ProfilesController::class, 'delete']);
$router->get('/admin/profiles/{id}/permissions', [App\Controllers\ProfilesController::class, 'getPermissions']);
$router->get('/api/profiles', [App\Controllers\ProfilesController::class, 'getProfilesList']);


// Dispatch request
try {
    // Verificar permissões antes de executar a rota
    $currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Aplicar middleware de permissões
    \App\Middleware\PermissionMiddleware::handle($currentRoute, $method);
    
    $router->dispatch();
} catch (\Exception $e) {
    echo '<pre>Router Error: ' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<pre>Stack trace: ' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    http_response_code(500);
}
