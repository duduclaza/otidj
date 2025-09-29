<?php
/**
 * CRIAR PACOTE VENDOR PARA UPLOAD DIRETO
 * 
 * Este script cria um arquivo ZIP com as dependências
 * para upload direto no servidor de produção
 */

echo "<h1>📦 CRIANDO PACOTE VENDOR PARA PRODUÇÃO</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

// Verificar se o vendor existe localmente
$vendorDir = __DIR__ . '/vendor';
$composerJson = __DIR__ . '/composer.json';

if (!is_dir($vendorDir)) {
    echo "❌ Diretório vendor não encontrado localmente<br>";
    echo "⚠️ Execute primeiro: <code>composer install</code><br>";
    exit;
}

if (!file_exists($composerJson)) {
    echo "❌ composer.json não encontrado<br>";
    exit;
}

echo "✅ Dependências locais encontradas<br>";

// Criar diretório temporário para o pacote
$tempDir = __DIR__ . '/temp_package';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

echo "✅ Diretório temporário criado<br>";

// Copiar arquivos essenciais
$filesToCopy = [
    'vendor' => 'vendor',
    'composer.json' => 'composer.json',
    'composer.lock' => 'composer.lock'
];

foreach ($filesToCopy as $source => $dest) {
    $sourcePath = __DIR__ . '/' . $source;
    $destPath = $tempDir . '/' . $dest;
    
    if (file_exists($sourcePath)) {
        if (is_dir($sourcePath)) {
            // Copiar diretório recursivamente
            copyDirectory($sourcePath, $destPath);
            echo "✅ Diretório $source copiado<br>";
        } else {
            // Copiar arquivo
            copy($sourcePath, $destPath);
            echo "✅ Arquivo $source copiado<br>";
        }
    } else {
        echo "⚠️ $source não encontrado<br>";
    }
}

// Criar diretórios necessários
$dirsToCreate = [
    'storage',
    'storage/logs'
];

foreach ($dirsToCreate as $dir) {
    $dirPath = $tempDir . '/' . $dir;
    if (!is_dir($dirPath)) {
        mkdir($dirPath, 0755, true);
        echo "✅ Diretório $dir criado<br>";
    }
}

// Criar arquivo .htaccess para storage
$htaccessStorage = 'Deny from all';
file_put_contents($tempDir . '/storage/.htaccess', $htaccessStorage);

// Criar arquivo de instruções
$instructions = '# INSTRUÇÕES DE INSTALAÇÃO

## 1. Upload dos Arquivos

1. Extraia este ZIP no diretório raiz do seu site
2. Os arquivos devem ficar em:
   - /vendor/ (diretório completo)
   - /storage/ (diretório com logs)
   - composer.json
   - composer.lock

## 2. Verificação

Acesse: https://djbr.sgqoti.com.br/verify_installation.php

## 3. Teste

Se a verificação mostrar tudo ✅, acesse:
https://djbr.sgqoti.com.br/

## 4. Permissões (se necessário)

Se houver problemas de permissão, execute via SSH:
```bash
chmod -R 755 vendor/
chmod -R 755 storage/
```

## 5. Limpeza

Após confirmar que tudo funciona, você pode deletar:
- verify_installation.php
- debug_production_500.php
- install_composer.php
- INSTALL_INSTRUCTIONS.txt

---
SGQ OTI DJ - Sistema de Gestão da Qualidade
Data: ' . date('d/m/Y H:i:s') . '
';

file_put_contents($tempDir . '/INSTALL_INSTRUCTIONS.txt', $instructions);
echo "✅ Instruções criadas<br>";

// Criar arquivo ZIP
$zipFile = __DIR__ . '/vendor_package_' . date('Y-m-d_H-i-s') . '.zip';

if (class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        // Adicionar arquivos ao ZIP
        addDirectoryToZip($zip, $tempDir, '');
        $zip->close();
        
        echo "✅ Pacote ZIP criado: " . basename($zipFile) . "<br>";
        echo "📁 Tamanho: " . formatBytes(filesize($zipFile)) . "<br>";
    } else {
        echo "❌ Falha ao criar arquivo ZIP<br>";
    }
} else {
    echo "❌ Extensão ZipArchive não disponível<br>";
    echo "⚠️ Você precisará compactar manualmente o diretório temp_package/<br>";
}

// Limpar diretório temporário
removeDirectory($tempDir);
echo "✅ Diretório temporário removido<br>";

echo "<hr>";

echo "<h2>📋 INSTRUÇÕES FINAIS</h2>";

if (file_exists($zipFile)) {
    echo "<div style='background: #e6ffe6; padding: 15px; border-left: 4px solid #00cc00; margin: 10px 0;'>";
    echo "<h3>✅ PACOTE CRIADO COM SUCESSO!</h3>";
    echo "<p><strong>Arquivo:</strong> " . basename($zipFile) . "</p>";
    echo "<p><strong>Próximos passos:</strong></p>";
    echo "<ol>";
    echo "<li>Faça upload do arquivo ZIP para o servidor</li>";
    echo "<li>Extraia no diretório raiz do site</li>";
    echo "<li>Acesse: <code>https://djbr.sgqoti.com.br/verify_installation.php</code></li>";
    echo "<li>Se tudo estiver ✅, teste: <code>https://djbr.sgqoti.com.br/</code></li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #ffe6e6; padding: 15px; border-left: 4px solid #ff0000; margin: 10px 0;'>";
    echo "<h3>⚠️ PACOTE NÃO CRIADO</h3>";
    echo "<p>Você precisará copiar manualmente:</p>";
    echo "<ul>";
    echo "<li>Diretório <code>vendor/</code> completo</li>";
    echo "<li>Arquivos <code>composer.json</code> e <code>composer.lock</code></li>";
    echo "<li>Criar diretório <code>storage/logs/</code></li>";
    echo "</ul>";
    echo "</div>";
}

// Funções auxiliares
function copyDirectory($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function removeDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

function addDirectoryToZip($zip, $dir, $zipPath) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $dir . '/' . $file;
        $zipFilePath = $zipPath . $file;
        
        if (is_dir($filePath)) {
            $zip->addEmptyDir($zipFilePath);
            addDirectoryToZip($zip, $filePath, $zipFilePath . '/');
        } else {
            $zip->addFile($filePath, $zipFilePath);
        }
    }
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}
?>
