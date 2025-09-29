<?php
/**
 * CORREÇÃO CRÍTICA: INSTALAR COMPOSER E DEPENDÊNCIAS EM PRODUÇÃO
 * 
 * Este script verifica e corrige a ausência do vendor/autoload.php
 */

echo "<h1>🔧 CORREÇÃO CRÍTICA - COMPOSER EM PRODUÇÃO</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Problema:</strong> vendor/autoload.php não encontrado</p>";
echo "<hr>";

// Verificar estrutura atual
echo "<h2>1. Verificação da Estrutura Atual</h2>";

$rootDir = __DIR__;
$vendorDir = $rootDir . '/vendor';
$composerJson = $rootDir . '/composer.json';
$composerLock = $rootDir . '/composer.lock';

echo "📁 <strong>Diretório raiz:</strong> $rootDir<br>";

// Verificar composer.json
if (file_exists($composerJson)) {
    echo "✅ composer.json encontrado<br>";
    $composerContent = json_decode(file_get_contents($composerJson), true);
    echo "📋 <strong>Dependências:</strong><br>";
    foreach ($composerContent['require'] as $package => $version) {
        echo "- $package: $version<br>";
    }
} else {
    echo "❌ composer.json NÃO encontrado<br>";
}

// Verificar composer.lock
if (file_exists($composerLock)) {
    echo "✅ composer.lock encontrado<br>";
} else {
    echo "⚠️ composer.lock NÃO encontrado<br>";
}

// Verificar diretório vendor
if (is_dir($vendorDir)) {
    echo "✅ Diretório vendor existe<br>";
    $autoloadFile = $vendorDir . '/autoload.php';
    if (file_exists($autoloadFile)) {
        echo "✅ autoload.php encontrado<br>";
    } else {
        echo "❌ autoload.php NÃO encontrado dentro do vendor<br>";
    }
} else {
    echo "❌ Diretório vendor NÃO existe<br>";
}

echo "<hr>";

// Verificar se Composer está disponível no sistema
echo "<h2>2. Verificação do Composer no Sistema</h2>";

// Tentar diferentes comandos do composer
$composerCommands = [
    'composer --version',
    '/usr/local/bin/composer --version',
    '/usr/bin/composer --version',
    'php composer.phar --version'
];

$composerFound = false;
$composerCommand = '';

foreach ($composerCommands as $cmd) {
    $output = [];
    $returnVar = 0;
    exec($cmd . ' 2>&1', $output, $returnVar);
    
    if ($returnVar === 0 && !empty($output)) {
        echo "✅ Composer encontrado: <code>$cmd</code><br>";
        echo "📋 Versão: " . implode(' ', $output) . "<br>";
        $composerFound = true;
        $composerCommand = explode(' ', $cmd)[0];
        break;
    }
}

if (!$composerFound) {
    echo "❌ Composer não encontrado no sistema<br>";
    echo "⚠️ <strong>Será necessário instalar o Composer primeiro</strong><br>";
}

echo "<hr>";

// Criar script de instalação
echo "<h2>3. Criando Scripts de Instalação</h2>";

// Script para instalar Composer
$installComposerScript = '#!/bin/bash
# Script para instalar Composer no Hostinger

echo "🔧 Instalando Composer..."

# Baixar Composer
cd ' . $rootDir . '
curl -sS https://getcomposer.org/installer | php

# Tornar executável
chmod +x composer.phar

echo "✅ Composer instalado como composer.phar"

# Instalar dependências
echo "📦 Instalando dependências..."
php composer.phar install --no-dev --optimize-autoloader

echo "✅ Dependências instaladas!"

# Verificar instalação
if [ -f "vendor/autoload.php" ]; then
    echo "✅ autoload.php criado com sucesso!"
else
    echo "❌ Falha na criação do autoload.php"
fi

# Criar diretórios necessários
mkdir -p storage/logs
chmod 755 storage/logs

echo "✅ Estrutura de diretórios criada!"
';

$scriptPath = $rootDir . '/install_composer.sh';
if (file_put_contents($scriptPath, $installComposerScript)) {
    chmod($scriptPath, 0755);
    echo "✅ Script install_composer.sh criado<br>";
} else {
    echo "❌ Falha ao criar script de instalação<br>";
}

// Script PHP alternativo
$installPhpScript = '<?php
/**
 * Instalação do Composer via PHP (alternativa)
 */

echo "🔧 Instalando Composer via PHP...\\n";

// Baixar installer do Composer
$installerUrl = "https://getcomposer.org/installer";
$installer = file_get_contents($installerUrl);

if ($installer === false) {
    die("❌ Falha ao baixar installer do Composer\\n");
}

// Salvar installer
file_put_contents("composer-setup.php", $installer);

// Executar installer
$output = [];
$returnVar = 0;
exec("php composer-setup.php 2>&1", $output, $returnVar);

echo "📋 Output da instalação:\\n";
echo implode("\\n", $output) . "\\n";

// Limpar installer
unlink("composer-setup.php");

if ($returnVar === 0) {
    echo "✅ Composer instalado!\\n";
    
    // Instalar dependências
    echo "📦 Instalando dependências...\\n";
    exec("php composer.phar install --no-dev --optimize-autoloader 2>&1", $output2, $returnVar2);
    
    echo "📋 Output da instalação de dependências:\\n";
    echo implode("\\n", $output2) . "\\n";
    
    if ($returnVar2 === 0) {
        echo "✅ Dependências instaladas com sucesso!\\n";
    } else {
        echo "❌ Falha na instalação das dependências\\n";
    }
} else {
    echo "❌ Falha na instalação do Composer\\n";
}

// Criar diretórios necessários
if (!is_dir("storage")) {
    mkdir("storage", 0755, true);
}
if (!is_dir("storage/logs")) {
    mkdir("storage/logs", 0755, true);
}

echo "✅ Estrutura de diretórios criada!\\n";
?>';

$phpScriptPath = $rootDir . '/install_composer.php';
if (file_put_contents($phpScriptPath, $installPhpScript)) {
    echo "✅ Script install_composer.php criado<br>";
} else {
    echo "❌ Falha ao criar script PHP de instalação<br>";
}

echo "<hr>";

// Instruções detalhadas
echo "<h2>4. Instruções para Correção</h2>";

echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc; margin: 10px 0;'>";
echo "<h3>🎯 PASSOS PARA CORRIGIR O ERRO 500:</h3>";

echo "<h4>Opção A - Via SSH (Recomendado):</h4>";
echo "<ol>";
echo "<li>Acesse o servidor via SSH</li>";
echo "<li>Navegue até o diretório: <code>cd /home/u230868210/domains/djbr.sgqoti.com.br/public_html/..</code></li>";
echo "<li>Execute: <code>bash install_composer.sh</code></li>";
echo "<li>Aguarde a instalação das dependências</li>";
echo "<li>Teste o site: <code>https://djbr.sgqoti.com.br/</code></li>";
echo "</ol>";

echo "<h4>Opção B - Via Navegador:</h4>";
echo "<ol>";
echo "<li>Acesse: <code>https://djbr.sgqoti.com.br/install_composer.php</code></li>";
echo "<li>Aguarde a execução do script</li>";
echo "<li>Verifique se aparece 'Dependências instaladas com sucesso!'</li>";
echo "<li>Teste o site: <code>https://djbr.sgqoti.com.br/</code></li>";
echo "</ol>";

echo "<h4>Opção C - Via Painel de Controle:</h4>";
echo "<ol>";
echo "<li>Acesse o painel do Hostinger</li>";
echo "<li>Vá em File Manager</li>";
echo "<li>Navegue até o diretório raiz do site</li>";
echo "<li>Execute os comandos do Composer manualmente</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";

// Verificação pós-instalação
echo "<h2>5. Script de Verificação Pós-Instalação</h2>";

$verifyScript = '<?php
/**
 * Verificação pós-instalação
 */

echo "<h1>🔍 VERIFICAÇÃO PÓS-INSTALAÇÃO</h1>";

// Verificar autoload
if (file_exists(__DIR__ . "/vendor/autoload.php")) {
    echo "✅ vendor/autoload.php encontrado<br>";
    
    // Tentar carregar
    try {
        require_once __DIR__ . "/vendor/autoload.php";
        echo "✅ Autoload carregado com sucesso<br>";
        
        // Verificar classes principais
        $classes = [
            "Dotenv\\\\Dotenv",
            "PHPMailer\\\\PHPMailer\\\\PHPMailer"
        ];
        
        foreach ($classes as $class) {
            if (class_exists($class)) {
                echo "✅ $class disponível<br>";
            } else {
                echo "❌ $class NÃO disponível<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao carregar autoload: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ vendor/autoload.php ainda NÃO encontrado<br>";
}

// Verificar diretórios
$dirs = ["storage", "storage/logs", "vendor"];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ Diretório $dir existe<br>";
    } else {
        echo "❌ Diretório $dir NÃO existe<br>";
    }
}

echo "<hr>";
echo "<p><strong>Se tudo estiver ✅, o site deve funcionar normalmente!</strong></p>";
echo "<p><a href=\"/\">🔗 Testar Site Principal</a></p>";
?>';

$verifyPath = $rootDir . '/verify_installation.php';
if (file_put_contents($verifyPath, $verifyScript)) {
    echo "✅ Script verify_installation.php criado<br>";
} else {
    echo "❌ Falha ao criar script de verificação<br>";
}

echo "<hr>";

echo "<h2>✅ SCRIPTS CRIADOS</h2>";
echo "<p><strong>Arquivos disponíveis para correção:</strong></p>";
echo "<ul>";
echo "<li>📄 <code>install_composer.sh</code> - Script Bash para instalação</li>";
echo "<li>📄 <code>install_composer.php</code> - Script PHP alternativo</li>";
echo "<li>📄 <code>verify_installation.php</code> - Verificação pós-instalação</li>";
echo "</ul>";

echo "<div style='background: #ffe6e6; padding: 15px; border-left: 4px solid #ff0000; margin: 10px 0;'>";
echo "<h3>⚠️ ATENÇÃO:</h3>";
echo "<p>O erro HTTP 500 será resolvido assim que as dependências do Composer forem instaladas.</p>";
echo "<p>Execute uma das opções acima para restaurar o funcionamento do sistema.</p>";
echo "</div>";
?>
