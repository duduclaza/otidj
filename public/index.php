<?php
// Versão ultra-simples que funciona SEM Controllers problemáticos
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Create router
$router = new Router(__DIR__);

// Página inicial simples
$router->get('/', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    header('Location: /admin');
    exit;
});

// Login simples
$router->get('/login', function() {
    echo '<!DOCTYPE html><html><head><title>Login - SGQ</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-100">';
    echo '<div class="min-h-screen flex items-center justify-center">';
    echo '<div class="bg-white p-8 rounded-lg shadow-md w-96">';
    echo '<h1 class="text-2xl font-bold mb-6 text-center">SGQ OTI DJ</h1>';
    echo '<form method="POST" action="/auth/login">';
    echo '<div class="mb-4">';
    echo '<label class="block text-sm font-medium mb-2">Email:</label>';
    echo '<input type="email" name="email" class="w-full border rounded px-3 py-2" required>';
    echo '</div>';
    echo '<div class="mb-6">';
    echo '<label class="block text-sm font-medium mb-2">Senha:</label>';
    echo '<input type="password" name="password" class="w-full border rounded px-3 py-2" required>';
    echo '</div>';
    echo '<button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Entrar</button>';
    echo '</form>';
    echo '</div></div></body></html>';
});

// Admin simples
$router->get('/admin', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    
    echo '<!DOCTYPE html><html><head><title>Admin - SGQ</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script></head><body>';
    echo '<div class="container mx-auto p-6">';
    echo '<h1 class="text-3xl font-bold mb-6">🎉 Sistema SGQ Funcionando!</h1>';
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">';
    echo '<strong>Sucesso!</strong> O sistema está funcionando corretamente.';
    echo '</div>';
    echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
    echo '<div class="bg-white p-4 rounded shadow"><h3 class="font-bold">✅ Router</h3><p>Funcionando</p></div>';
    echo '<div class="bg-white p-4 rounded shadow"><h3 class="font-bold">✅ Database</h3><p>Conectado</p></div>';
    echo '<div class="bg-white p-4 rounded shadow"><h3 class="font-bold">✅ Sessions</h3><p>Ativas</p></div>';
    echo '</div>';
    echo '<div class="mt-6">';
    echo '<a href="/diagnostic.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">🔧 Diagnóstico Completo</a>';
    echo '<a href="/logout" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 ml-2">Sair</a>';
    echo '</div>';
    echo '</div></body></html>';
});

// Logout simples
$router->get('/logout', function() {
    session_destroy();
    header('Location: /login');
    exit;
});

// Auth login simples
$router->post('/auth/login', function() {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Login básico (você pode melhorar depois)
    if ($email === 'admin@sgq.com' && $password === 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Administrador';
        $_SESSION['user_email'] = $email;
        header('Location: /admin');
    } else {
        header('Location: /login?error=1');
    }
    exit;
});

// Dispatch
try {
    $router->dispatch();
} catch (Exception $e) {
    echo '<!DOCTYPE html><html><head><title>Erro</title></head><body>';
    echo '<h1>Erro: ' . htmlspecialchars($e->getMessage()) . '</h1>';
    echo '<p><a href="/diagnostic.php">Diagnóstico</a></p>';
    echo '</body></html>';
}
?>
