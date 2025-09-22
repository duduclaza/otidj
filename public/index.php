<?php
// Sistema SGQ OTI DJ - Versão Corrigida
session_start();

// No-cache headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Middleware\PermissionMiddleware;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Error reporting
$isDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
if ($isDebug) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Run migrations automatically
try {
    $migration = new \App\Core\Migration();
    $migration->runMigrations();
} catch (\Exception $e) {
    // Skip migrations if connection limit exceeded or other issues
    if (strpos($e->getMessage(), 'max_connections_per_hour') === false) {
        error_log("Migration error: " . $e->getMessage());
    }
}

// Create router
$router = new Router(__DIR__);

// Do NOT run migrations on every request to avoid DB connection/timeout issues in production

// Auth routes (match AuthController methods: login = show page, authenticate = process)
$router->get('/login', [App\Controllers\AuthController::class, 'login']);
$router->post('/auth/login', [App\Controllers\AuthController::class, 'authenticate']);
$router->get('/logout', [App\Controllers\AuthController::class, 'logout']);

// Lightweight root: redirect unauthenticated users to /login to avoid heavy controller
$router->get('/', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    // Authenticated: send to admin dashboard
    (new App\Controllers\AdminController())->dashboard();
});

// Admin routes
$router->get('/admin', [App\Controllers\AdminController::class, 'dashboard']);
$router->get('/admin/users', [App\Controllers\AdminController::class, 'users']);
$router->get('/admin/invitations', [App\Controllers\AdminController::class, 'invitations']);
$router->post('/admin/users/create', [App\Controllers\AdminController::class, 'createUser']);
$router->post('/admin/users/update', [App\Controllers\AdminController::class, 'updateUser']);
$router->post('/admin/users/delete', [App\Controllers\AdminController::class, 'deleteUser']);
$router->post('/admin/users/send-credentials', [App\Controllers\AdminController::class, 'sendCredentials']);
$router->get('/admin/users/{id}/permissions', [App\Controllers\AdminController::class, 'userPermissions']);
$router->post('/admin/users/{id}/permissions', [App\Controllers\AdminController::class, 'updateUserPermissions']);

// Toners routes
$router->get('/toners/cadastro', [App\Controllers\TonersController::class, 'cadastro']);
$router->post('/toners/cadastro', [App\Controllers\TonersController::class, 'store']);
$router->post('/toners/update', [App\Controllers\TonersController::class, 'update']);
$router->post('/toners/delete', [App\Controllers\TonersController::class, 'delete']);
$router->get('/toners/retornados', [App\Controllers\TonersController::class, 'retornados']);
$router->post('/toners/retornados', [App\Controllers\TonersController::class, 'storeRetornado']);
$router->delete('/toners/retornados/delete/{id}', [App\Controllers\TonersController::class, 'deleteRetornado']);
$router->get('/toners/retornados/export', [App\Controllers\TonersController::class, 'exportRetornados']);
$router->post('/toners/retornados/import', [App\Controllers\TonersController::class, 'importRetornados']);
$router->post('/toners/import', [App\Controllers\TonersController::class, 'import']);
$router->get('/toners/export', [App\Controllers\TonersController::class, 'exportExcelAdvanced']);

// Other routes
$router->get('/homologacoes', [App\Controllers\HomologacoesController::class, 'index']);
$router->get('/toners/amostragens', [App\Controllers\AmostragemController::class, 'index']);
// Amostragens actions
$router->post('/toners/amostragens', [App\Controllers\AmostragemController::class, 'store']);
$router->post('/toners/amostragens/test', [App\Controllers\AmostragemController::class, 'testStore']);
$router->delete('/toners/amostragens/{id}', [App\Controllers\AmostragemController::class, 'delete']);
$router->get('/toners/amostragens/{id}/pdf', [App\Controllers\AmostragemController::class, 'show']);
$router->get('/toners/amostragens/{id}/evidencias', [App\Controllers\AmostragemController::class, 'getEvidencias']);
$router->get('/toners/amostragens/{id}/evidencia/{evidenciaId}', [App\Controllers\AmostragemController::class, 'evidencia']);
$router->get('/garantias', [App\Controllers\GarantiasController::class, 'index']);

// Admin/Config maintenance endpoints
$router->post('/admin/db/patch-amostragens', [App\Controllers\ConfigController::class, 'patchAmostragens']);
$router->post('/admin/db/run-migrations', [App\Controllers\ConfigController::class, 'runMigrations']);

// Profiles routes
$router->get('/admin/profiles', [App\Controllers\ProfilesController::class, 'index']);
$router->post('/admin/profiles/create', [App\Controllers\ProfilesController::class, 'create']);
$router->post('/admin/profiles/update', [App\Controllers\ProfilesController::class, 'update']);
$router->post('/admin/profiles/delete', [App\Controllers\ProfilesController::class, 'delete']);

// Melhoria Continua routes
$router->get('/melhoria-continua/solicitacoes', [App\Controllers\MelhoriaContinuaController::class, 'index']);
$router->get('/melhoria-continua/solicitacoes/create', [App\Controllers\MelhoriaContinuaController::class, 'create']);
$router->post('/melhoria-continua/solicitacoes/store', [App\Controllers\MelhoriaContinuaController::class, 'store']);
$router->get('/melhoria-continua/solicitacoes/list', [App\Controllers\MelhoriaContinuaController::class, 'list']);
$router->get('/melhoria-continua/solicitacoes/{id}/details', [App\Controllers\MelhoriaContinuaController::class, 'details']);
$router->get('/melhoria-continua/solicitacoes/{id}/print', [App\Controllers\MelhoriaContinuaController::class, 'print']);
$router->post('/melhoria-continua/solicitacoes/update-status', [App\Controllers\MelhoriaContinuaController::class, 'updateStatus']);

// API routes
$router->get('/api/users', [App\Controllers\UsersController::class, 'getUsers']);
$router->get('/api/profiles', [App\Controllers\ProfilesController::class, 'getProfilesList']);
$router->get('/api/toner', [App\Controllers\TonersController::class, 'getTonerData']);

// Dispatch
try {
    $currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Apply middleware only for protected routes
    $isPublicAuthRoute = (
        strpos($currentRoute, '/login') === 0 ||
        strpos($currentRoute, '/auth/') === 0 ||
        strpos($currentRoute, '/register') === 0 ||
        strpos($currentRoute, '/logout') === 0
    );

    if (!$isPublicAuthRoute) {
        PermissionMiddleware::handle($currentRoute, $method);
    }
    
    $router->dispatch();
    
} catch (\Exception $e) {
    error_log('Application error: ' . $e->getMessage());
    
    if ($isDebug) {
        echo '<h1>Erro: ' . htmlspecialchars($e->getMessage()) . '</h1>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Erro 500</title></head><body>';
        echo '<h1>Erro Interno do Servidor</h1>';
        echo '<p>Tente novamente em alguns minutos.</p>';
        echo '</body></html>';
    }
}
?>
