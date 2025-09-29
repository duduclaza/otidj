<?php
/**
 * INSTALAÇÃO RÁPIDA VIA NAVEGADOR
 * 
 * Este script baixa e instala o Composer automaticamente
 * Deve ser executado diretamente no servidor via navegador
 */

set_time_limit(300); // 5 minutos
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html><head><title>Instalação Rápida - SGQ OTI DJ</title>";
echo "<meta charset='UTF-8'>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;} .success{color:#008000;} .error{color:#ff0000;} .warning{color:#ff8800;} .code{background:#f0f0f0;padding:10px;border-radius:5px;font-family:monospace;}</style>";
echo "</head><body>";

echo "<h1>🚀 INSTALAÇÃO RÁPIDA - SGQ OTI DJ</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

// Verificar se já está instalado
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<div class='success'>";
    echo "<h2>✅ SISTEMA JÁ INSTALADO!</h2>";
    echo "<p>O Composer e as dependências já estão instalados.</p>";
    echo "<p><a href='/' style='background:#008000;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔗 Acessar Sistema</a></p>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

echo "<h2>📦 Iniciando Instalação Automática...</h2>";

// Função para executar comandos e mostrar output
function runCommand($command, $description) {
    echo "<h3>$description</h3>";
    echo "<div class='code'>";
    echo "$ $command<br>";
    
    $output = [];
    $returnVar = 0;
    exec($command . ' 2>&1', $output, $returnVar);
    
    foreach ($output as $line) {
        echo htmlspecialchars($line) . "<br>";
    }
    echo "</div>";
    
    if ($returnVar === 0) {
        echo "<p class='success'>✅ Sucesso!</p>";
        return true;
    } else {
        echo "<p class='error'>❌ Erro (código: $returnVar)</p>";
        return false;
    }
}

// Passo 1: Baixar Composer
echo "<h2>1. Baixando Composer...</h2>";

$composerInstaller = file_get_contents('https://getcomposer.org/installer');
if ($composerInstaller === false) {
    echo "<p class='error'>❌ Falha ao baixar o installer do Composer</p>";
    echo "<p>Tente novamente ou use a instalação manual.</p>";
    echo "</body></html>";
    exit;
}

file_put_contents('composer-setup.php', $composerInstaller);
echo "<p class='success'>✅ Installer baixado</p>";

// Passo 2: Instalar Composer
if (!runCommand('php composer-setup.php', '2. Instalando Composer')) {
    echo "<p class='error'>Falha na instalação do Composer. Tentando método alternativo...</p>";
    
    // Método alternativo: baixar composer.phar diretamente
    echo "<h3>Tentativa alternativa...</h3>";
    $composerPhar = file_get_contents('https://getcomposer.org/download/latest-stable/composer.phar');
    if ($composerPhar !== false) {
        file_put_contents('composer.phar', $composerPhar);
        chmod('composer.phar', 0755);
        echo "<p class='success'>✅ Composer baixado diretamente</p>";
    } else {
        echo "<p class='error'>❌ Falha no método alternativo</p>";
        echo "</body></html>";
        exit;
    }
}

// Limpar installer
if (file_exists('composer-setup.php')) {
    unlink('composer-setup.php');
}

// Passo 3: Verificar se composer.phar existe
if (!file_exists('composer.phar')) {
    echo "<p class='error'>❌ composer.phar não foi criado</p>";
    echo "</body></html>";
    exit;
}

// Passo 4: Instalar dependências
echo "<h2>3. Instalando Dependências...</h2>";

if (!runCommand('php composer.phar install --no-dev --optimize-autoloader', 'Instalando pacotes PHP')) {
    echo "<p class='error'>❌ Falha na instalação das dependências</p>";
    
    // Tentar sem otimizações
    echo "<h3>Tentando instalação simplificada...</h3>";
    if (!runCommand('php composer.phar install --no-dev', 'Instalação simplificada')) {
        echo "<p class='error'>❌ Falha na instalação simplificada</p>";
        echo "<p>Verifique se o arquivo composer.json existe e está correto.</p>";
        echo "</body></html>";
        exit;
    }
}

// Passo 5: Criar diretórios necessários
echo "<h2>4. Criando Estrutura de Diretórios...</h2>";

$directories = [
    'storage',
    'storage/logs'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p class='success'>✅ Diretório $dir criado</p>";
        } else {
            echo "<p class='warning'>⚠️ Falha ao criar diretório $dir</p>";
        }
    } else {
        echo "<p class='success'>✅ Diretório $dir já existe</p>";
    }
}

// Passo 6: Verificar instalação
echo "<h2>5. Verificando Instalação...</h2>";

$checks = [
    'vendor/autoload.php' => 'Autoload do Composer',
    'vendor/vlucas/phpdotenv' => 'Biblioteca DotEnv',
    'vendor/phpmailer/phpmailer' => 'Biblioteca PHPMailer',
    'storage' => 'Diretório de storage',
    'storage/logs' => 'Diretório de logs'
];

$allGood = true;

foreach ($checks as $path => $description) {
    if (file_exists($path)) {
        echo "<p class='success'>✅ $description</p>";
    } else {
        echo "<p class='error'>❌ $description não encontrado</p>";
        $allGood = false;
    }
}

// Passo 7: Teste final
echo "<h2>6. Teste Final...</h2>";

if ($allGood) {
    try {
        require_once 'vendor/autoload.php';
        echo "<p class='success'>✅ Autoload carregado com sucesso!</p>";
        
        // Testar classes principais
        if (class_exists('Dotenv\Dotenv')) {
            echo "<p class='success'>✅ DotEnv disponível</p>";
        } else {
            echo "<p class='error'>❌ DotEnv não disponível</p>";
            $allGood = false;
        }
        
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            echo "<p class='success'>✅ PHPMailer disponível</p>";
        } else {
            echo "<p class='error'>❌ PHPMailer não disponível</p>";
            $allGood = false;
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao carregar autoload: " . $e->getMessage() . "</p>";
        $allGood = false;
    }
}

// Resultado final
echo "<hr>";

if ($allGood) {
    echo "<div style='background:#e6ffe6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='success'>🎉 INSTALAÇÃO CONCLUÍDA COM SUCESSO!</h2>";
    echo "<p>O sistema SGQ OTI DJ está pronto para uso.</p>";
    echo "<p><a href='/' style='background:#008000;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:18px;'>🚀 ACESSAR SISTEMA</a></p>";
    echo "<hr>";
    echo "<h3>🧹 Limpeza (Opcional)</h3>";
    echo "<p>Você pode deletar os seguintes arquivos de instalação:</p>";
    echo "<ul style='text-align:left;'>";
    echo "<li>quick_install.php (este arquivo)</li>";
    echo "<li>debug_production_500.php</li>";
    echo "<li>fix_composer_production.php</li>";
    echo "<li>composer.phar (manter se quiser usar Composer no futuro)</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background:#ffe6e6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='error'>❌ INSTALAÇÃO COM PROBLEMAS</h2>";
    echo "<p>Alguns componentes não foram instalados corretamente.</p>";
    echo "<p>Verifique os erros acima e tente novamente.</p>";
    echo "<p><a href='?' style='background:#ff8800;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔄 Tentar Novamente</a></p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:12px;'>";
echo "SGQ OTI DJ - Sistema de Gestão da Qualidade<br>";
echo "Instalação automática executada em " . date('d/m/Y H:i:s');
echo "</p>";

echo "</body></html>";
?>
