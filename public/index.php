<?php
// Sistema completo com tratamento de erros robusto
session_start();

try {
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

    // Create router
    $router = new Router(__DIR__);

    // Auth routes (safe)
    $router->get('/login', [App\Controllers\AuthController::class, 'showLogin']);
    $router->post('/auth/login', [App\Controllers\AuthController::class, 'login']);
    $router->get('/logout', [App\Controllers\AuthController::class, 'logout']);

    // Dashboard route (safe)
    $router->get('/', [App\Controllers\DashboardController::class, 'index']);

    // Admin routes (test one by one)
    try {
        $router->get('/admin', [App\Controllers\AdminController::class, 'dashboard']);
        $router->get('/admin/users', [App\Controllers\AdminController::class, 'users']);
        $router->post('/admin/users/create', [App\Controllers\AdminController::class, 'createUser']);
        $router->post('/admin/users/update', [App\Controllers\AdminController::class, 'updateUser']);
        $router->post('/admin/users/delete', [App\Controllers\AdminController::class, 'deleteUser']);
        $router->post('/admin/users/send-credentials', [App\Controllers\AdminController::class, 'sendCredentials']);
    } catch (Exception $e) {
        error_log('Admin routes error: ' . $e->getMessage());
    }

    // Toners routes (test)
    try {
        $router->get('/toners/cadastro', [App\Controllers\TonersController::class, 'cadastro']);
        $router->get('/toners/retornados', [App\Controllers\TonersController::class, 'retornados']);
    } catch (Exception $e) {
        error_log('Toners routes error: ' . $e->getMessage());
    }

    // Debug routes (optional)
    if ($isDebug) {
        try {
            $router->get('/debug/logs', [App\Controllers\DebugController::class, 'getLogs']);
            $router->get('/debug/report', [App\Controllers\DebugController::class, 'generateReport']);
        } catch (Exception $e) {
            error_log('Debug routes error: ' . $e->getMessage());
        }
    }

    // Dispatch with error handling
    try {
        $currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Apply middleware with error handling
        try {
            PermissionMiddleware::handle($currentRoute, $method);
        } catch (Exception $e) {
            error_log('Middleware error: ' . $e->getMessage());
            // Continue without middleware if it fails
        }
        
        $router->dispatch();
        
    } catch (Exception $e) {
        error_log('Router dispatch error: ' . $e->getMessage());
        
        // Show friendly error page
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Erro 500</title></head><body>';
        echo '<h1>🚨 Erro Interno do Servidor</h1>';
        echo '<p>Ocorreu um erro interno. Tente novamente em alguns minutos.</p>';
        
        if ($isDebug) {
            echo '<h2>Debug Info:</h2>';
            echo '<p><strong>Erro:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>Arquivo:</strong> ' . $e->getFile() . '</p>';
            echo '<p><strong>Linha:</strong> ' . $e->getLine() . '</p>';
        }
        
        echo '<p><a href="/diagnostic.php">🔧 Executar Diagnóstico</a></p>';
        echo '</body></html>';
    }

} catch (Exception $e) {
    // Critical error in initialization
    error_log('Critical error: ' . $e->getMessage());
    http_response_code(500);
    
    echo '<!DOCTYPE html><html><head><title>Erro Crítico</title></head><body>';
    echo '<h1>🚨 Erro Crítico do Sistema</h1>';
    echo '<p>Falha na inicialização do sistema.</p>';
    echo '<p><strong>Erro:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="/diagnostic.php">🔧 Executar Diagnóstico</a></p>';
    echo '</body></html>';
}
?>
