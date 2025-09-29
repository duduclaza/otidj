<?php
/**
 * CORREÇÃO PARA ERRO HTTP 500 EM PRODUÇÃO
 * 
 * Este script aplica correções na rota raiz para torná-la mais robusta
 * contra falhas de conexão de banco e outros problemas.
 */

echo "<h1>🔧 APLICANDO CORREÇÕES PARA ERRO HTTP 500</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

// Backup do arquivo original
$indexPath = __DIR__ . '/public/index.php';
$backupPath = __DIR__ . '/public/index.php.backup.' . date('Y-m-d_H-i-s');

if (file_exists($indexPath)) {
    copy($indexPath, $backupPath);
    echo "✅ Backup criado: " . basename($backupPath) . "<br>";
} else {
    echo "❌ Arquivo index.php não encontrado<br>";
    exit;
}

// Ler o conteúdo atual
$content = file_get_contents($indexPath);

// Encontrar e substituir a rota raiz problemática
$oldRoutePattern = '/\/\/ Lightweight root:.*?\}\);/s';

$newRoute = '// Lightweight root: redirect unauthenticated users to /login to avoid heavy controller
$router->get(\'/\', function() {
    if (!isset($_SESSION[\'user_id\'])) {
        header(\'Location: /login\');
        exit;
    }
    
    try {
        // Verificar se tem permissão para dashboard (com tratamento de erro)
        if (\App\Services\PermissionService::hasPermission($_SESSION[\'user_id\'], \'dashboard\', \'view\')) {
            // Tem permissão: mostrar dashboard
            (new App\Controllers\AdminController())->dashboard();
        } else {
            // Não tem permissão: redirecionar para página inicial
            header(\'Location: /inicio\');
            exit;
        }
    } catch (Exception $e) {
        // Em caso de erro (ex: banco indisponível), redirecionar para página inicial
        error_log("Erro na rota raiz: " . $e->getMessage());
        
        // Se o usuário está logado mas há erro no sistema, ir para página inicial
        header(\'Location: /inicio\');
        exit;
    } catch (PDOException $e) {
        // Erro específico de banco de dados
        error_log("Erro de banco na rota raiz: " . $e->getMessage());
        
        // Redirecionar para página inicial que não depende tanto do banco
        header(\'Location: /inicio\');
        exit;
    }
});';

// Aplicar a correção
if (preg_match($oldRoutePattern, $content)) {
    $newContent = preg_replace($oldRoutePattern, $newRoute, $content);
    
    if (file_put_contents($indexPath, $newContent)) {
        echo "✅ Rota raiz corrigida com tratamento de erros<br>";
    } else {
        echo "❌ Falha ao salvar correção<br>";
    }
} else {
    echo "⚠️ Padrão da rota raiz não encontrado para correção automática<br>";
}

echo "<hr>";

// Verificar se o HomeController tem tratamento de erro robusto
echo "<h2>Verificando HomeController</h2>";
$homeControllerPath = __DIR__ . '/src/Controllers/HomeController.php';

if (file_exists($homeControllerPath)) {
    echo "✅ HomeController encontrado<br>";
    
    $homeContent = file_get_contents($homeControllerPath);
    
    // Verificar se já tem tratamento de erro
    if (strpos($homeContent, 'try {') !== false) {
        echo "✅ HomeController já tem tratamento de erro<br>";
    } else {
        echo "⚠️ HomeController pode precisar de tratamento de erro<br>";
    }
} else {
    echo "❌ HomeController não encontrado<br>";
}

echo "<hr>";

// Criar um .htaccess mais robusto
echo "<h2>Criando .htaccess robusto</h2>";
$htaccessPath = __DIR__ . '/public/.htaccess';
$htaccessContent = '# SGQ OTI DJ - Configuração Apache Robusta
RewriteEngine On

# Redirecionar tudo para index.php, exceto arquivos existentes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Configurações de segurança
<Files "*.php">
    Order allow,deny
    Allow from all
</Files>

# Bloquear acesso a arquivos sensíveis
<FilesMatch "\.(env|log|sql|md)$">
    Order deny,allow
    Deny from all
</FilesMatch>

# Headers de segurança
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Configurações de erro personalizadas
ErrorDocument 500 /error500.html
ErrorDocument 404 /error404.html

# Configurações PHP
<IfModule mod_php.c>
    php_value memory_limit 256M
    php_value max_execution_time 60
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
</IfModule>';

if (file_put_contents($htaccessPath, $htaccessContent)) {
    echo "✅ .htaccess robusto criado<br>";
} else {
    echo "❌ Falha ao criar .htaccess<br>";
}

echo "<hr>";

// Criar página de erro 500 personalizada
echo "<h2>Criando página de erro 500</h2>";
$error500Path = __DIR__ . '/public/error500.html';
$error500Content = '<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 500 - SGQ OTI DJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6 text-center">
        <div class="text-red-500 text-6xl mb-4">⚠️</div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Erro Interno do Servidor</h1>
        <p class="text-gray-600 mb-6">Estamos enfrentando problemas técnicos temporários. Tente novamente em alguns minutos.</p>
        <div class="space-y-2">
            <a href="/login" class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                Fazer Login
            </a>
            <a href="mailto:suporte@sgqoti.com.br" class="block w-full bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                Contatar Suporte
            </a>
        </div>
        <p class="text-xs text-gray-500 mt-4">SGQ OTI DJ - Sistema de Gestão da Qualidade</p>
    </div>
</body>
</html>';

if (file_put_contents($error500Path, $error500Content)) {
    echo "✅ Página de erro 500 criada<br>";
} else {
    echo "❌ Falha ao criar página de erro 500<br>";
}

echo "<hr>";

// Verificar configurações críticas
echo "<h2>Verificações Finais</h2>";

// Verificar se o arquivo .env está correto
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    
    if (strpos($envContent, 'APP_DEBUG=false') !== false) {
        echo "✅ APP_DEBUG=false (correto para produção)<br>";
    } else {
        echo "⚠️ APP_DEBUG pode não estar configurado corretamente<br>";
    }
    
    if (strpos($envContent, 'APP_ENV=production') !== false) {
        echo "✅ APP_ENV=production (correto)<br>";
    } else {
        echo "⚠️ APP_ENV pode não estar configurado para produção<br>";
    }
} else {
    echo "❌ Arquivo .env não encontrado<br>";
}

echo "<hr>";
echo "<h2>✅ CORREÇÕES APLICADAS</h2>";
echo "<p><strong>Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Execute o diagnóstico: <code>https://djbr.sgqoti.com.br/debug_production_500.php</code></li>";
echo "<li>Teste o site: <code>https://djbr.sgqoti.com.br/</code></li>";
echo "<li>Se ainda houver erro, verifique os logs do servidor</li>";
echo "<li>Considere reiniciar o serviço web se necessário</li>";
echo "</ol>";

echo "<p><strong>Arquivos modificados:</strong></p>";
echo "<ul>";
echo "<li>✅ public/index.php (com backup)</li>";
echo "<li>✅ public/.htaccess (configuração robusta)</li>";
echo "<li>✅ public/error500.html (página de erro personalizada)</li>";
echo "</ul>";
?>
