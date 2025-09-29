<?php
/**
 * VERIFICAÇÃO FINAL DE SAÚDE DO SISTEMA
 * 
 * Este script faz uma verificação completa do sistema
 * após a correção do erro HTTP 500
 */

set_time_limit(60);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html><head><title>Verificação de Saúde - SGQ OTI DJ</title>";
echo "<meta charset='UTF-8'>";
echo "<style>
body{font-family:Arial,sans-serif;max-width:1000px;margin:20px auto;padding:20px;background:#f5f5f5;} 
.card{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
.success{color:#008000;} .error{color:#ff0000;} .warning{color:#ff8800;} .info{color:#0066cc;}
.status-ok{background:#e6ffe6;border-left:4px solid #00cc00;padding:10px;margin:5px 0;}
.status-error{background:#ffe6e6;border-left:4px solid #ff0000;padding:10px;margin:5px 0;}
.status-warning{background:#fff8e6;border-left:4px solid #ff8800;padding:10px;margin:5px 0;}
.code{background:#f8f8f8;padding:8px;border-radius:4px;font-family:monospace;font-size:12px;}
table{width:100%;border-collapse:collapse;margin:10px 0;}
th,td{padding:8px;text-align:left;border-bottom:1px solid #ddd;}
th{background:#f0f0f0;}
.btn{display:inline-block;padding:10px 20px;background:#0066cc;color:white;text-decoration:none;border-radius:5px;margin:5px;}
.btn-success{background:#00cc00;} .btn-warning{background:#ff8800;} .btn-danger{background:#ff0000;}
</style>";
echo "</head><body>";

echo "<h1>🏥 VERIFICAÇÃO DE SAÚDE DO SISTEMA SGQ OTI DJ</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Função para mostrar status
function showStatus($condition, $successMsg, $errorMsg, $warningMsg = null) {
    if ($condition === true) {
        echo "<div class='status-ok'>✅ $successMsg</div>";
        return true;
    } elseif ($condition === false) {
        echo "<div class='status-error'>❌ $errorMsg</div>";
        return false;
    } else {
        echo "<div class='status-warning'>⚠️ " . ($warningMsg ?: $errorMsg) . "</div>";
        return null;
    }
}

// 1. Verificação do Composer e Autoload
echo "<div class='card'>";
echo "<h2>1. 📦 Composer e Autoload</h2>";

$autoloadExists = file_exists(__DIR__ . '/vendor/autoload.php');
showStatus($autoloadExists, 
    "Autoload encontrado e disponível", 
    "Autoload NÃO encontrado - sistema não funcionará");

if ($autoloadExists) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        showStatus(true, "Autoload carregado com sucesso", "");
        
        // Verificar classes principais
        $classes = [
            'Dotenv\\Dotenv' => 'DotEnv para variáveis de ambiente',
            'PHPMailer\\PHPMailer\\PHPMailer' => 'PHPMailer para envio de emails'
        ];
        
        foreach ($classes as $class => $description) {
            $exists = class_exists($class);
            showStatus($exists, "$description disponível", "$description NÃO disponível");
        }
        
    } catch (Exception $e) {
        showStatus(false, "", "Erro ao carregar autoload: " . $e->getMessage());
    }
}
echo "</div>";

// 2. Verificação do Ambiente (.env)
echo "<div class='card'>";
echo "<h2>2. 🔧 Configuração de Ambiente</h2>";

$envExists = file_exists(__DIR__ . '/.env');
showStatus($envExists, "Arquivo .env encontrado", "Arquivo .env NÃO encontrado");

if ($envExists && class_exists('Dotenv\\Dotenv')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
        showStatus(true, "Variáveis de ambiente carregadas", "");
        
        // Verificar variáveis críticas
        $requiredVars = [
            'APP_NAME' => 'Nome da aplicação',
            'APP_ENV' => 'Ambiente (production/development)',
            'DB_HOST' => 'Host do banco de dados',
            'DB_DATABASE' => 'Nome do banco de dados',
            'DB_USERNAME' => 'Usuário do banco de dados'
        ];
        
        echo "<table>";
        echo "<tr><th>Variável</th><th>Valor</th><th>Status</th></tr>";
        
        foreach ($requiredVars as $var => $description) {
            $value = $_ENV[$var] ?? 'não definido';
            $status = !empty($_ENV[$var]) ? '✅' : '❌';
            
            // Mascarar senhas
            if (strpos($var, 'PASSWORD') !== false && !empty($_ENV[$var])) {
                $value = str_repeat('*', strlen($_ENV[$var]));
            }
            
            echo "<tr><td>$var</td><td>$value</td><td>$status</td></tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        showStatus(false, "", "Erro ao carregar .env: " . $e->getMessage());
    }
}
echo "</div>";

// 3. Teste de Conexão com Banco de Dados
echo "<div class='card'>";
echo "<h2>3. 🗄️ Conexão com Banco de Dados</h2>";

if (class_exists('App\\Config\\Database')) {
    try {
        $db = App\Config\Database::getInstance();
        showStatus(true, "Conexão com banco estabelecida", "");
        
        // Testar query simples
        $stmt = $db->query("SELECT 1 as test, NOW() as current_time");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['test'] == 1) {
            showStatus(true, "Query de teste executada com sucesso", "");
            echo "<div class='code'>Horário do servidor DB: " . $result['current_time'] . "</div>";
        } else {
            showStatus(false, "", "Falha na query de teste");
        }
        
        // Verificar tabelas principais
        $tables = ['users', 'profiles', 'profile_permissions', 'toners', 'retornados'];
        echo "<h3>Verificação de Tabelas:</h3>";
        
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) as count FROM $table LIMIT 1");
                $count = $stmt->fetchColumn();
                showStatus(true, "Tabela '$table' acessível ($count registros)", "");
            } catch (Exception $e) {
                showStatus(false, "", "Tabela '$table' não acessível: " . $e->getMessage());
            }
        }
        
    } catch (Exception $e) {
        showStatus(false, "", "Erro de conexão com banco: " . $e->getMessage());
    }
} else {
    showStatus(false, "", "Classe Database não disponível");
}
echo "</div>";

// 4. Verificação das Classes do Sistema
echo "<div class='card'>";
echo "<h2>4. 🏗️ Classes do Sistema</h2>";

$systemClasses = [
    'App\\Core\\Router' => 'Sistema de roteamento',
    'App\\Services\\PermissionService' => 'Serviço de permissões',
    'App\\Controllers\\AdminController' => 'Controller administrativo',
    'App\\Controllers\\HomeController' => 'Controller da página inicial',
    'App\\Controllers\\AuthController' => 'Controller de autenticação',
    'App\\Middleware\\PermissionMiddleware' => 'Middleware de permissões'
];

foreach ($systemClasses as $class => $description) {
    $exists = class_exists($class);
    showStatus($exists, "$description disponível", "$description NÃO disponível");
}
echo "</div>";

// 5. Verificação de Diretórios e Permissões
echo "<div class='card'>";
echo "<h2>5. 📁 Diretórios e Permissões</h2>";

$directories = [
    'vendor' => 'Dependências do Composer',
    'storage' => 'Diretório de armazenamento',
    'storage/logs' => 'Diretório de logs',
    'src' => 'Código fonte da aplicação',
    'views' => 'Templates do sistema'
];

foreach ($directories as $dir => $description) {
    $exists = is_dir($dir);
    $readable = $exists ? is_readable($dir) : false;
    $writable = $exists ? is_writable($dir) : false;
    
    if ($exists && $readable) {
        $status = $writable ? "✅ $description (leitura/escrita)" : "⚠️ $description (apenas leitura)";
        showStatus($writable ? true : null, $status, "");
    } else {
        showStatus(false, "", "$description não acessível");
    }
}
echo "</div>";

// 6. Teste das Rotas Principais
echo "<div class='card'>";
echo "<h2>6. 🛣️ Teste de Rotas</h2>";

if (class_exists('App\\Core\\Router')) {
    try {
        $router = new App\Core\Router(__DIR__ . '/public');
        showStatus(true, "Router instanciado com sucesso", "");
        
        // Simular algumas rotas
        $testRoutes = [
            '/' => 'Página inicial',
            '/login' => 'Página de login',
            '/inicio' => 'Página de início'
        ];
        
        foreach ($testRoutes as $route => $description) {
            // Apenas verificar se não há erro na criação da rota
            try {
                $router->get($route, function() { return "test"; });
                showStatus(true, "Rota '$route' ($description) configurável", "");
            } catch (Exception $e) {
                showStatus(false, "", "Erro na rota '$route': " . $e->getMessage());
            }
        }
        
    } catch (Exception $e) {
        showStatus(false, "", "Erro ao instanciar Router: " . $e->getMessage());
    }
} else {
    showStatus(false, "", "Classe Router não disponível");
}
echo "</div>";

// 7. Informações do Servidor
echo "<div class='card'>";
echo "<h2>7. 🖥️ Informações do Servidor</h2>";

$serverInfo = [
    'PHP Version' => phpversion(),
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'não disponível',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'não disponível',
    'Memory Limit' => ini_get('memory_limit'),
    'Max Execution Time' => ini_get('max_execution_time') . 's',
    'Upload Max Filesize' => ini_get('upload_max_filesize'),
    'Post Max Size' => ini_get('post_max_size')
];

echo "<table>";
foreach ($serverInfo as $key => $value) {
    echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
}
echo "</table>";
echo "</div>";

// 8. Resumo Final e Ações
echo "<div class='card'>";
echo "<h2>8. 📋 Resumo Final</h2>";

// Determinar status geral
$criticalIssues = !file_exists(__DIR__ . '/vendor/autoload.php') || 
                  !class_exists('App\\Core\\Router') ||
                  !file_exists(__DIR__ . '/.env');

if (!$criticalIssues) {
    echo "<div class='status-ok'>";
    echo "<h3>🎉 SISTEMA FUNCIONANDO CORRETAMENTE!</h3>";
    echo "<p>Todas as verificações críticas passaram. O sistema SGQ OTI DJ está operacional.</p>";
    echo "<div style='text-align:center;margin:20px 0;'>";
    echo "<a href='/' class='btn btn-success'>🚀 ACESSAR SISTEMA PRINCIPAL</a>";
    echo "<a href='/login' class='btn'>🔐 Página de Login</a>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<div class='status-error'>";
    echo "<h3>❌ PROBLEMAS CRÍTICOS ENCONTRADOS</h3>";
    echo "<p>O sistema ainda apresenta problemas que impedem seu funcionamento normal.</p>";
    echo "<div style='text-align:center;margin:20px 0;'>";
    echo "<a href='quick_install.php' class='btn btn-warning'>🔧 EXECUTAR INSTALAÇÃO RÁPIDA</a>";
    echo "<a href='debug_production_500.php' class='btn'>🔍 DIAGNÓSTICO DETALHADO</a>";
    echo "</div>";
    echo "</div>";
}

// Limpeza recomendada
echo "<h3>🧹 Limpeza Recomendada (Após Confirmação)</h3>";
echo "<p>Após confirmar que o sistema está funcionando, você pode deletar os seguintes arquivos de diagnóstico:</p>";
echo "<ul>";
$cleanupFiles = [
    'debug_production_500.php',
    'fix_composer_production.php', 
    'fix_production_500.php',
    'quick_install.php',
    'final_health_check.php',
    'create_vendor_package.php',
    'composer.phar'
];

foreach ($cleanupFiles as $file) {
    $exists = file_exists($file);
    echo "<li>" . ($exists ? "✅" : "❌") . " $file</li>";
}
echo "</ul>";

echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:12px;'>";
echo "SGQ OTI DJ - Sistema de Gestão da Qualidade<br>";
echo "Verificação de saúde executada em " . date('d/m/Y H:i:s') . "<br>";
echo "Versão do sistema: 2.1.5+";
echo "</p>";

echo "</body></html>";
?>
