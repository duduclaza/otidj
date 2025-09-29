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
// Permitir forçar debug via query string (?debug=1)
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    $isDebug = true;
}
if ($isDebug) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Migrations system removed - using direct queries now

// Create router
$router = new Router(__DIR__);

// Do NOT run migrations on every request to avoid DB connection/timeout issues in production

// Auth routes (match AuthController methods: login = show page, authenticate = process)
$router->get('/login', [App\Controllers\AuthController::class, 'login']);
$router->post('/auth/login', [App\Controllers\AuthController::class, 'authenticate']);
$router->get('/logout', [App\Controllers\AuthController::class, 'logout']);
$router->get('/register', [App\Controllers\AuthController::class, 'register']);
$router->post('/auth/register', [App\Controllers\AuthController::class, 'processRegister']);

// Access Request routes
$router->get('/request-access', [App\Controllers\AccessRequestController::class, 'requestAccess']);
$router->post('/access-request/process', [App\Controllers\AccessRequestController::class, 'processRequest']);
$router->get('/access-request/filiais', [App\Controllers\AccessRequestController::class, 'getFiliais']);
$router->get('/access-request/departamentos', [App\Controllers\AccessRequestController::class, 'getDepartamentos']);

// Admin Access Request routes
$router->get('/admin/access-requests', [App\Controllers\AccessRequestController::class, 'index']);
$router->get('/admin/access-requests/list', [App\Controllers\AccessRequestController::class, 'listPendingRequests']);
$router->get('/admin/access-requests/profiles', [App\Controllers\AccessRequestController::class, 'listProfiles']);
$router->post('/admin/access-requests/approve', [App\Controllers\AccessRequestController::class, 'approveRequest']);
$router->post('/admin/access-requests/reject', [App\Controllers\AccessRequestController::class, 'rejectRequest']);

// Lightweight root: redirect unauthenticated users to /login to avoid heavy controller
$router->get('/', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    
    // Verificar se tem permissão para dashboard
    if (\App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view')) {
        // Tem permissão: mostrar dashboard
        (new App\Controllers\AdminController())->dashboard();
    } else {
        // Não tem permissão: redirecionar para página inicial
        header('Location: /inicio');
        exit;
    }
});

// Home/Início route - acessível a todos os usuários autenticados
$router->get('/inicio', [App\Controllers\HomeController::class, 'index']);

// Dashboard route - com URL específica
$router->get('/dashboard', [App\Controllers\AdminController::class, 'dashboard']);

// Rota de diagnóstico POPs (apenas para admins)
$router->get('/admin/diagnostico/pops-pendentes', [App\Controllers\PopItsController::class, 'diagnosticoPendentes']);

// Rota de diagnóstico de permissões (apenas para admins)
$router->get('/admin/diagnostico/permissoes-usuario', [App\Controllers\AdminController::class, 'diagnosticoPermissoes']);

// Admin routes
$router->get('/admin', [App\Controllers\AdminController::class, 'dashboard']);
$router->get('/admin/dashboard/data', [App\Controllers\AdminController::class, 'getDashboardData']);
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
$router->get('/homologacoes', [App\Controllers\PageController::class, 'homologacoes']);
$router->get('/fluxogramas', [App\Controllers\PageController::class, 'fluxogramas']);
$router->get('/controle-de-rc', [App\Controllers\PageController::class, 'controleDeRc']);
$router->get('/toners/amostragens', [App\Controllers\AmostragemController::class, 'index']);
// Amostragens actions
$router->post('/toners/amostragens', [App\Controllers\AmostragemController::class, 'store']);
$router->post('/toners/amostragens/test', [App\Controllers\AmostragemController::class, 'testStore']);
$router->post('/toners/amostragens/{id}/update', [App\Controllers\AmostragemController::class, 'update']);
$router->delete('/toners/amostragens/{id}', [App\Controllers\AmostragemController::class, 'delete']);
$router->get('/toners/amostragens/{id}/pdf', [App\Controllers\AmostragemController::class, 'show']);
$router->get('/toners/amostragens/{id}/evidencias', [App\Controllers\AmostragemController::class, 'getEvidencias']);
$router->get('/toners/amostragens/{id}/evidencia/{evidenciaId}', [App\Controllers\AmostragemController::class, 'evidencia']);
// Garantias routes
$router->get('/garantias', [App\Controllers\GarantiasController::class, 'index']);
$router->post('/garantias', [App\Controllers\GarantiasController::class, 'create']); // Rota para o formulário
$router->get('/garantias/list', [App\Controllers\GarantiasController::class, 'list']);
$router->get('/garantias/fornecedores', [App\Controllers\GarantiasController::class, 'listFornecedores']);
$router->post('/garantias/create', [App\Controllers\GarantiasController::class, 'create']);
$router->get('/garantias/{id}', [App\Controllers\GarantiasController::class, 'show']);
$router->post('/garantias/{id}/update', [App\Controllers\GarantiasController::class, 'update']);
$router->post('/garantias/{id}/update-status', [App\Controllers\GarantiasController::class, 'updateStatus']);
$router->post('/garantias/{id}/delete', [App\Controllers\GarantiasController::class, 'delete']);
$router->get('/garantias/anexo/{id}', [App\Controllers\GarantiasController::class, 'downloadAnexo']);
$router->get('/garantias/{id}/anexos/download-all', [App\Controllers\GarantiasController::class, 'downloadAllAnexos']);
$router->post('/garantias/anexo/{id}/delete', [App\Controllers\GarantiasController::class, 'deleteAnexo']);

// Controle de Descartes routes
$router->get('/controle-descartes', [App\Controllers\ControleDescartesController::class, 'index']);
$router->get('/controle-descartes/list', [App\Controllers\ControleDescartesController::class, 'listDescartes']);
$router->post('/controle-descartes/create', [App\Controllers\ControleDescartesController::class, 'create']);
$router->post('/controle-descartes/update', [App\Controllers\ControleDescartesController::class, 'update']);
$router->post('/controle-descartes/delete', [App\Controllers\ControleDescartesController::class, 'delete']);
$router->get('/controle-descartes/{id}', [App\Controllers\ControleDescartesController::class, 'getDescarte']);
$router->get('/controle-descartes/anexo/{id}', [App\Controllers\ControleDescartesController::class, 'downloadAnexo']);
$router->get('/controle-descartes/relatorios', [App\Controllers\ControleDescartesController::class, 'relatorios']);

// Auditorias routes
$router->get('/auditorias', [App\Controllers\AuditoriasController::class, 'index']);
$router->get('/auditorias/list', [App\Controllers\AuditoriasController::class, 'listAuditorias']);
$router->post('/auditorias/create', [App\Controllers\AuditoriasController::class, 'create']);
$router->post('/auditorias/update', [App\Controllers\AuditoriasController::class, 'update']);
$router->post('/auditorias/delete', [App\Controllers\AuditoriasController::class, 'delete']);
$router->get('/auditorias/{id}', [App\Controllers\AuditoriasController::class, 'getAuditoria']);
$router->get('/auditorias/anexo/{id}', [App\Controllers\AuditoriasController::class, 'downloadAnexo']);
$router->get('/auditorias/relatorios', [App\Controllers\AuditoriasController::class, 'relatorios']);

// 5W2H routes
$router->get('/5w2h', [App\Controllers\Planos5W2HController::class, 'index']);
$router->get('/5w2h/list', [App\Controllers\Planos5W2HController::class, 'listPlanos']);
$router->post('/5w2h/create', [App\Controllers\Planos5W2HController::class, 'create']);
$router->post('/5w2h/update', [App\Controllers\Planos5W2HController::class, 'update']);
$router->post('/5w2h/delete', [App\Controllers\Planos5W2HController::class, 'delete']);
$router->get('/5w2h/{id}', [App\Controllers\Planos5W2HController::class, 'getPlano']);
$router->get('/5w2h/details/{id}', [App\Controllers\Planos5W2HController::class, 'details']);
$router->get('/5w2h/print/{id}', [App\Controllers\Planos5W2HController::class, 'printPlano']);
$router->get('/5w2h/anexos/{id}', [App\Controllers\Planos5W2HController::class, 'anexos']);
$router->get('/5w2h/anexo/{id}', [App\Controllers\Planos5W2HController::class, 'downloadAnexo']);
$router->get('/5w2h/relatorios', [App\Controllers\Planos5W2HController::class, 'relatorios']);

// Admin/Config maintenance endpoints
$router->post('/admin/db/patch-amostragens', [App\Controllers\ConfigController::class, 'patchAmostragens']);
$router->post('/admin/db/run-migrations', [App\Controllers\ConfigController::class, 'runMigrations']);
$router->get('/admin/db/run-migrations', [App\Controllers\ConfigController::class, 'runMigrations']);
// Admin: sincronizar permissões do Administrador
$router->post('/admin/sync-admin-permissions', [App\Controllers\ConfigController::class, 'syncAdminPermissions']);
$router->get('/admin/sync-admin-permissions', [App\Controllers\ConfigController::class, 'syncAdminPermissions']);
// Debug temporário POPs e ITs
$router->get('/debug/pops-its', [App\Controllers\ConfigController::class, 'debugPopIts']);

// Profiles routes
$router->get('/admin/profiles', [App\Controllers\ProfilesController::class, 'index']);
$router->post('/admin/profiles/create', [App\Controllers\ProfilesController::class, 'create']);
$router->post('/admin/profiles/update', [App\Controllers\ProfilesController::class, 'update']);
$router->post('/admin/profiles/delete', [App\Controllers\ProfilesController::class, 'delete']);
$router->get('/admin/profiles/{id}/permissions', [App\Controllers\ProfilesController::class, 'getPermissions']);

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
$router->get('/api/setores', [App\Controllers\RegistrosController::class, 'getDepartamentos']);
$router->get('/api/filiais', [App\Controllers\RegistrosController::class, 'getFiliais']);
$router->get('/api/parametros', [App\Controllers\RegistrosController::class, 'getParametros']);

// Profile routes
$router->get('/profile', [App\Controllers\ProfileController::class, 'index']);

// Profile API routes
$router->get('/api/profile', [App\Controllers\ProfileController::class, 'getProfile']);
$router->post('/api/profile/password', [App\Controllers\ProfileController::class, 'changePassword']);
$router->post('/api/profile/photo', [App\Controllers\ProfileController::class, 'uploadPhoto']);

// Notifications routes
$router->get('/api/notifications', [App\Controllers\NotificationsController::class, 'getNotifications']);
$router->post('/api/notifications/{id}/read', [App\Controllers\NotificationsController::class, 'markAsRead']);
$router->post('/api/notifications/read-all', [App\Controllers\NotificationsController::class, 'markAllAsRead']);
$router->post('/api/notifications/clear-history', [App\Controllers\NotificationsController::class, 'clearHistory']);

// FMEA routes
$router->get('/fmea', [App\Controllers\FMEAController::class, 'index']);
$router->get('/fmea/list', [App\Controllers\FMEAController::class, 'list']);
$router->post('/fmea/store', [App\Controllers\FMEAController::class, 'store']);
$router->get('/fmea/{id}', [App\Controllers\FMEAController::class, 'show']);
$router->post('/fmea/{id}/update', [App\Controllers\FMEAController::class, 'update']);
$router->delete('/fmea/{id}/delete', [App\Controllers\FMEAController::class, 'delete']);
$router->get('/fmea/charts', [App\Controllers\FMEAController::class, 'chartData']);
$router->get('/fmea/{id}/print', [App\Controllers\FMEAController::class, 'print']);

// POPs e ITs routes
$router->get('/pops-e-its', [App\Controllers\PopItsController::class, 'index']);
$router->get('/pops-its/diagnostico', [App\Controllers\PopItsController::class, 'diagnostico']);
$router->get('/pops-its/teste', [App\Controllers\PopItsController::class, 'testeTitulos']);
// Aba 1: Cadastro de Títulos
$router->post('/pops-its/titulo/create', [App\Controllers\PopItsController::class, 'createTitulo']);
$router->get('/pops-its/titulos/list', [App\Controllers\PopItsController::class, 'listTitulos']);
$router->get('/pops-its/titulos/search', [App\Controllers\PopItsController::class, 'searchTitulos']);
$router->post('/pops-its/titulo/delete', [App\Controllers\PopItsController::class, 'deleteTitulo']);
// Aba 2: Meus Registros
$router->post('/pops-its/registro/create', [App\Controllers\PopItsController::class, 'createRegistro']);
$router->get('/pops-its/registros/meus', [App\Controllers\PopItsController::class, 'listMeusRegistros']);
$router->get('/pops-its/arquivo/{id}', [App\Controllers\PopItsController::class, 'downloadArquivo']);
$router->get('/pops-its/debug-arquivo/{id}', [App\Controllers\PopItsController::class, 'debugArquivo']);
$router->post('/pops-its/registro/update', [App\Controllers\PopItsController::class, 'updateRegistro']);
$router->post('/pops-its/registro/delete', [App\Controllers\PopItsController::class, 'deleteRegistro']);
// Aba 3: Pendente Aprovação
$router->get('/pops-its/pendentes/list', [App\Controllers\PopItsController::class, 'listPendentesAprovacao']);
$router->post('/pops-its/registro/aprovar', [App\Controllers\PopItsController::class, 'aprovarRegistro']);
$router->post('/pops-its/registro/reprovar', [App\Controllers\PopItsController::class, 'reprovarRegistro']);
// Aba 4: Visualização
$router->get('/pops-its/visualizacao/list', [App\Controllers\PopItsController::class, 'listVisualizacao']);
$router->get('/pops-its/visualizar/{id}', [App\Controllers\PopItsController::class, 'visualizarArquivo']);
// Aba 5: Log de Visualizações
$router->get('/pops-its/logs/visualizacao', [App\Controllers\PopItsController::class, 'listLogsVisualizacao']);
// Endpoint de teste
$router->get('/pops-its/test', [App\Controllers\PopItsController::class, 'testEndpoint']);
// Sistema de Solicitações
$router->post('/pops-its/solicitacao/create', [App\Controllers\PopItsController::class, 'createSolicitacao']);
$router->get('/pops-its/solicitacoes/list', [App\Controllers\PopItsController::class, 'listSolicitacoes']);
$router->post('/pops-its/solicitacao/aprovar', [App\Controllers\PopItsController::class, 'aprovarSolicitacao']);
$router->post('/pops-its/solicitacao/reprovar', [App\Controllers\PopItsController::class, 'reprovarSolicitacao']);

// Melhoria Contínua routes
$router->get('/melhoria-continua', [App\Controllers\MelhoriaContinuaController::class, 'index']);
$router->get('/melhoria-continua/list', [App\Controllers\MelhoriaContinuaController::class, 'list']);
$router->get('/melhoria-continua/departamentos', [App\Controllers\MelhoriaContinuaController::class, 'getDepartamentos']);
$router->get('/melhoria-continua/usuarios', [App\Controllers\MelhoriaContinuaController::class, 'getUsuarios']);
$router->post('/melhoria-continua/store', [App\Controllers\MelhoriaContinuaController::class, 'store']);
$router->post('/melhoria-continua/{id}/status', [App\Controllers\MelhoriaContinuaController::class, 'updateStatus']);
$router->post('/melhoria-continua/{id}/pontuacao', [App\Controllers\MelhoriaContinuaController::class, 'updatePontuacao']);
$router->post('/melhoria-continua/{id}/observacao', [App\Controllers\MelhoriaContinuaController::class, 'updateObservacao']);
$router->post('/melhoria-continua/{id}/resultado', [App\Controllers\MelhoriaContinuaController::class, 'updateResultado']);
$router->delete('/melhoria-continua/{id}/delete', [App\Controllers\MelhoriaContinuaController::class, 'delete']);
$router->get('/melhoria-continua/{id}/print', [App\Controllers\MelhoriaContinuaController::class, 'print']);
$router->get('/melhoria-continua/{id}/anexos', [App\Controllers\MelhoriaContinuaController::class, 'getAnexos']);
$router->get('/melhoria-continua/anexo/{anexoId}', [App\Controllers\MelhoriaContinuaController::class, 'downloadAnexo']);

// Registros routes
$router->get('/registros/filiais', [App\Controllers\RegistrosController::class, 'filiais']);
$router->get('/registros/departamentos', [App\Controllers\RegistrosController::class, 'departamentos']);
$router->get('/registros/fornecedores', [App\Controllers\RegistrosController::class, 'fornecedores']);
$router->get('/registros/parametros', [App\Controllers\RegistrosController::class, 'parametros']);

// Store routes
$router->post('/registros/filiais/store', [App\Controllers\RegistrosController::class, 'storeFilial']);
$router->post('/registros/departamentos/store', [App\Controllers\RegistrosController::class, 'storeDepartamento']);
$router->post('/registros/fornecedores/store', [App\Controllers\RegistrosController::class, 'storeFornecedor']);
$router->post('/registros/parametros/store', [App\Controllers\RegistrosController::class, 'storeParametro']);

// Update routes
$router->post('/registros/filiais/update', [App\Controllers\RegistrosController::class, 'updateFilial']);
$router->post('/registros/departamentos/update', [App\Controllers\RegistrosController::class, 'updateDepartamento']);
$router->post('/registros/fornecedores/update', [App\Controllers\RegistrosController::class, 'updateFornecedor']);
$router->post('/registros/parametros/update', [App\Controllers\RegistrosController::class, 'updateParametro']);

// Delete routes
$router->post('/registros/filiais/delete', [App\Controllers\RegistrosController::class, 'deleteFilial']);
$router->post('/registros/departamentos/delete', [App\Controllers\RegistrosController::class, 'deleteDepartamento']);
$router->post('/registros/fornecedores/delete', [App\Controllers\RegistrosController::class, 'deleteFornecedor']);
$router->post('/registros/parametros/delete', [App\Controllers\RegistrosController::class, 'deleteParametro']);

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
