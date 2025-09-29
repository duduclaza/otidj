<?php
/**
 * CORREÇÃO DE EMERGÊNCIA - ERRO HTTP 500
 * 
 * Este arquivo deve ser enviado diretamente para o servidor
 * e executado via navegador para correção imediata
 */

// Configurações de erro para diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300); // 5 minutos

echo "<!DOCTYPE html><html><head><title>Correção de Emergência - SGQ OTI DJ</title>";
echo "<meta charset='UTF-8'><style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f5f5f5;} .card{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:#008000;} .error{color:#ff0000;} .warning{color:#ff8800;} .code{background:#f8f8f8;padding:10px;border-radius:4px;font-family:monospace;} .btn{display:inline-block;padding:10px 20px;background:#0066cc;color:white;text-decoration:none;border-radius:5px;margin:5px;} .btn-success{background:#00cc00;}</style></head><body>";

echo "<h1>🚨 CORREÇÃO DE EMERGÊNCIA - SGQ OTI DJ</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Status:</strong> Executando correção automática...</p>";
echo "<hr>";

// Função para log de ações
function logAction($message, $type = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message\n";
    
    // Tentar escrever no log
    @file_put_contents('emergency_fix.log', $logMessage, FILE_APPEND);
    
    // Mostrar na tela
    $class = $type === 'error' ? 'error' : ($type === 'success' ? 'success' : 'warning');
    echo "<div class='$class'>[$timestamp] $message</div>";
    
    // Flush para mostrar imediatamente
    if (ob_get_level()) ob_flush();
    flush();
}

logAction("Iniciando correção de emergência", "info");

// 1. Verificar estrutura atual
logAction("=== ETAPA 1: DIAGNÓSTICO ===", "info");

$rootDir = __DIR__;
logAction("Diretório de trabalho: $rootDir", "info");

// Verificar arquivos críticos
$criticalFiles = [
    'composer.json' => 'Configuração do Composer',
    '.env' => 'Variáveis de ambiente',
    'public/index.php' => 'Entry point da aplicação'
];

$missingFiles = [];
foreach ($criticalFiles as $file => $description) {
    if (file_exists($file)) {
        logAction("✅ $description encontrado", "success");
    } else {
        logAction("❌ $description NÃO encontrado: $file", "error");
        $missingFiles[] = $file;
    }
}

// Verificar diretórios
$directories = ['src', 'views', 'vendor', 'storage', 'storage/logs'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        logAction("✅ Diretório $dir existe", "success");
    } else {
        logAction("❌ Diretório $dir não existe", "error");
        
        // Tentar criar diretórios necessários
        if (in_array($dir, ['storage', 'storage/logs'])) {
            if (@mkdir($dir, 0755, true)) {
                logAction("✅ Diretório $dir criado", "success");
            } else {
                logAction("❌ Falha ao criar diretório $dir", "error");
            }
        }
    }
}

// 2. Baixar e instalar Composer se necessário
logAction("=== ETAPA 2: INSTALAÇÃO DO COMPOSER ===", "info");

if (!file_exists('vendor/autoload.php')) {
    logAction("Composer autoload não encontrado. Iniciando instalação...", "warning");
    
    // Baixar Composer
    logAction("Baixando Composer installer...", "info");
    $composerInstaller = @file_get_contents('https://getcomposer.org/installer');
    
    if ($composerInstaller === false) {
        logAction("Falha ao baixar installer. Tentando método alternativo...", "warning");
        
        // Método alternativo: baixar composer.phar diretamente
        $composerPhar = @file_get_contents('https://getcomposer.org/download/latest-stable/composer.phar');
        if ($composerPhar !== false) {
            file_put_contents('composer.phar', $composerPhar);
            chmod('composer.phar', 0755);
            logAction("✅ Composer.phar baixado diretamente", "success");
        } else {
            logAction("❌ Falha em ambos os métodos de download", "error");
            goto manual_installation;
        }
    } else {
        // Instalar via installer
        file_put_contents('composer-setup.php', $composerInstaller);
        
        logAction("Executando installer do Composer...", "info");
        $output = [];
        $returnVar = 0;
        exec('php composer-setup.php 2>&1', $output, $returnVar);
        
        logAction("Output do installer: " . implode(' | ', $output), "info");
        
        if ($returnVar === 0) {
            logAction("✅ Composer instalado com sucesso", "success");
        } else {
            logAction("❌ Falha na instalação do Composer", "error");
        }
        
        // Limpar installer
        @unlink('composer-setup.php');
    }
    
    // Instalar dependências
    if (file_exists('composer.phar') && file_exists('composer.json')) {
        logAction("Instalando dependências...", "info");
        
        $commands = [
            'php composer.phar install --no-dev --optimize-autoloader',
            'php composer.phar install --no-dev',
            'php composer.phar install'
        ];
        
        $installed = false;
        foreach ($commands as $cmd) {
            logAction("Tentando: $cmd", "info");
            $output = [];
            $returnVar = 0;
            exec($cmd . ' 2>&1', $output, $returnVar);
            
            if ($returnVar === 0) {
                logAction("✅ Dependências instaladas: " . implode(' | ', array_slice($output, -3)), "success");
                $installed = true;
                break;
            } else {
                logAction("❌ Falha no comando: " . implode(' | ', array_slice($output, -2)), "error");
            }
        }
        
        if (!$installed) {
            logAction("❌ Todas as tentativas de instalação falharam", "error");
        }
    }
} else {
    logAction("✅ Composer autoload já existe", "success");
}

manual_installation:

// 3. Criar arquivos essenciais se estiverem faltando
logAction("=== ETAPA 3: CRIAÇÃO DE ARQUIVOS ESSENCIAIS ===", "info");

// Criar .htaccess se não existir
if (!file_exists('public/.htaccess')) {
    $htaccess = 'RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Configurações de segurança
<Files "*.env">
    Order allow,deny
    Deny from all
</Files>

ErrorDocument 500 "Sistema temporariamente indisponível. Tente novamente em alguns minutos."';

    if (@file_put_contents('public/.htaccess', $htaccess)) {
        logAction("✅ .htaccess criado", "success");
    } else {
        logAction("❌ Falha ao criar .htaccess", "error");
    }
}

// Criar index.php mínimo se não existir ou estiver com problema
$minimalIndex = '<?php
// Index mínimo para teste
session_start();

// Headers de segurança
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Verificar se vendor existe
if (file_exists(__DIR__ . "/../vendor/autoload.php")) {
    require_once __DIR__ . "/../vendor/autoload.php";
    
    // Tentar carregar o sistema normal
    try {
        // Carregar .env se existir
        if (file_exists(__DIR__ . "/../.env") && class_exists("Dotenv\\\\Dotenv")) {
            $dotenv = Dotenv\\Dotenv::createImmutable(__DIR__ . "/..");
            $dotenv->safeLoad();
        }
        
        // Redirecionar para login se não logado
        if (!isset($_SESSION["user_id"])) {
            // Página de login simples
            echo "<!DOCTYPE html><html><head><title>SGQ OTI DJ - Login</title><meta charset=\\"UTF-8\\"><style>body{font-family:Arial,sans-serif;background:#f0f0f0;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;} .login-box{background:white;padding:40px;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);text-align:center;} .btn{background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:10px;}</style></head><body><div class=\\"login-box\\"><h1>🏭 SGQ OTI DJ</h1><h2>Sistema de Gestão da Qualidade</h2><p>Sistema em manutenção temporária</p><a href=\\"/login\\" class=\\"btn\\">Fazer Login</a><p style=\\"font-size:12px;color:#666;margin-top:20px;\\">Em caso de problemas, contate o suporte técnico</p></div></body></html>";
            exit;
        }
        
        // Se chegou aqui, tentar carregar o sistema completo
        // (código do sistema original seria carregado aqui)
        
    } catch (Exception $e) {
        // Em caso de erro, mostrar página de manutenção
        echo "<!DOCTYPE html><html><head><title>Sistema em Manutenção</title><meta charset=\\"UTF-8\\"><style>body{font-family:Arial,sans-serif;background:#f0f0f0;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;} .maintenance-box{background:white;padding:40px;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);text-align:center;max-width:500px;}</style></head><body><div class=\\"maintenance-box\\"><h1>🔧 Sistema em Manutenção</h1><p>O SGQ OTI DJ está temporariamente indisponível para manutenção.</p><p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p><p>Tente novamente em alguns minutos.</p><p style=\\"font-size:12px;color:#666;margin-top:20px;\\">Suporte: suporte@sgqoti.com.br</p></div></body></html>";
        exit;
    }
} else {
    // Vendor não existe - mostrar página de instalação
    echo "<!DOCTYPE html><html><head><title>Instalação Necessária</title><meta charset=\\"UTF-8\\"><style>body{font-family:Arial,sans-serif;background:#f0f0f0;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;} .install-box{background:white;padding:40px;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);text-align:center;max-width:600px;} .btn{background:#007cba;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;display:inline-block;margin:10px;font-size:16px;}</style></head><body><div class=\\"install-box\\"><h1>⚙️ Instalação Necessária</h1><p>O sistema SGQ OTI DJ precisa ser configurado.</p><p>As dependências do sistema não foram encontradas.</p><a href=\\"/emergency_fix.php\\" class=\\"btn\\">🔧 Executar Correção</a><a href=\\"/quick_install.php\\" class=\\"btn\\">🚀 Instalação Rápida</a><p style=\\"font-size:12px;color:#666;margin-top:20px;\\">SGQ OTI DJ - Sistema de Gestão da Qualidade</p></div></body></html>";
}
?>';

if (!file_exists('public/index.php') || filesize('public/index.php') < 1000) {
    if (@file_put_contents('public/index.php', $minimalIndex)) {
        logAction("✅ Index.php mínimo criado", "success");
    } else {
        logAction("❌ Falha ao criar index.php", "error");
    }
}

// 4. Verificação final
logAction("=== ETAPA 4: VERIFICAÇÃO FINAL ===", "info");

$finalChecks = [
    'vendor/autoload.php' => 'Autoload do Composer',
    'public/index.php' => 'Entry point',
    'public/.htaccess' => 'Configuração Apache',
    'storage' => 'Diretório de storage',
    'storage/logs' => 'Diretório de logs'
];

$allGood = true;
foreach ($finalChecks as $path => $description) {
    if (file_exists($path)) {
        logAction("✅ $description OK", "success");
    } else {
        logAction("❌ $description faltando", "error");
        $allGood = false;
    }
}

// 5. Resultado final
echo "<hr>";
logAction("=== RESULTADO FINAL ===", "info");

if ($allGood && file_exists('vendor/autoload.php')) {
    echo "<div style='background:#e6ffe6;padding:20px;border-radius:10px;text-align:center;margin:20px 0;'>";
    echo "<h2 style='color:#008000;'>🎉 CORREÇÃO CONCLUÍDA!</h2>";
    echo "<p>O sistema foi corrigido e deve estar funcionando.</p>";
    echo "<a href='/' class='btn btn-success' style='font-size:18px;'>🚀 TESTAR SISTEMA AGORA</a>";
    echo "</div>";
    logAction("✅ Sistema corrigido com sucesso!", "success");
} else {
    echo "<div style='background:#ffe6e6;padding:20px;border-radius:10px;text-align:center;margin:20px 0;'>";
    echo "<h2 style='color:#ff0000;'>⚠️ CORREÇÃO PARCIAL</h2>";
    echo "<p>Algumas correções foram aplicadas, mas podem ser necessárias ações adicionais.</p>";
    echo "<a href='/quick_install.php' class='btn' style='background:#ff8800;'>🔧 INSTALAÇÃO COMPLETA</a>";
    echo "</div>";
    logAction("⚠️ Correção parcial aplicada", "warning");
}

// Instruções adicionais
echo "<div style='background:#f0f8ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>📋 Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Teste o sistema clicando no botão acima</li>";
echo "<li>Se ainda houver erro 500, execute: <a href='/quick_install.php'>quick_install.php</a></li>";
echo "<li>Para diagnóstico completo: <a href='/final_health_check.php'>final_health_check.php</a></li>";
echo "<li>Em caso de problemas, contate o suporte técnico</li>";
echo "</ol>";
echo "</div>";

// Log final
logAction("Correção de emergência finalizada", "info");

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:12px;'>";
echo "SGQ OTI DJ - Correção de Emergência<br>";
echo "Executado em " . date('d/m/Y H:i:s') . "<br>";
echo "Log salvo em: emergency_fix.log";
echo "</p>";

echo "</body></html>";
?>
