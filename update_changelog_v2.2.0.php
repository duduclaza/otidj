<?php
/**
 * ATUALIZAÇÃO DO CHANGELOG - VERSÃO 2.2.0
 * 
 * Adiciona nova versão sobre melhorias do sistema POPs e ITs
 * na página inicial do sistema
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Atualização Changelog v2.2.0 - SGQ OTI DJ</title>";
echo "<meta charset='UTF-8'><style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f5f5f5;} .card{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:#008000;} .error{color:#ff0000;} .warning{color:#ff8800;} .info{color:#0066cc;} .code{background:#f8f8f8;padding:10px;border-radius:4px;font-family:monospace;font-size:12px;} .btn{display:inline-block;padding:15px 30px;background:#00cc00;color:white;text-decoration:none;border-radius:5px;margin:10px;font-size:18px;}</style></head><body>";

echo "<h1>📋 ATUALIZAÇÃO DO CHANGELOG - VERSÃO 2.2.0</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Nova Versão:</strong> 2.2.0 - Aprimoramento Completo do Sistema POPs e ITs</p>";
echo "<hr>";

// Localizar HomeController
$homeControllerFile = __DIR__ . '/src/Controllers/HomeController.php';

echo "<div class='card'>";
echo "<h2>1. Localizando HomeController...</h2>";

if (!file_exists($homeControllerFile)) {
    echo "<p class='error'>❌ Arquivo não encontrado: $homeControllerFile</p>";
    
    // Tentar outros locais
    $possiblePaths = [
        __DIR__ . '/src/Controllers/HomeController.php',
        __DIR__ . '/app/Controllers/HomeController.php',
        __DIR__ . '/Controllers/HomeController.php'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $homeControllerFile = $path;
            echo "<p class='success'>✅ Arquivo encontrado em: $path</p>";
            break;
        }
    }
    
    if (!file_exists($homeControllerFile)) {
        echo "<p class='error'>❌ HomeController.php não encontrado em nenhum local</p>";
        echo "</div></body></html>";
        exit;
    }
} else {
    echo "<p class='success'>✅ Arquivo encontrado: $homeControllerFile</p>";
}
echo "</div>";

// Fazer backup
echo "<div class='card'>";
echo "<h2>2. Criando Backup...</h2>";

$backupFile = $homeControllerFile . '.backup.' . date('Y-m-d_H-i-s');
if (copy($homeControllerFile, $backupFile)) {
    echo "<p class='success'>✅ Backup criado: " . basename($backupFile) . "</p>";
} else {
    echo "<p class='warning'>⚠️ Não foi possível criar backup</p>";
}
echo "</div>";

// Ler conteúdo atual
echo "<div class='card'>";
echo "<h2>3. Analisando Versão Atual...</h2>";

$content = file_get_contents($homeControllerFile);

// Verificar versão atual
if (preg_match("/\\\$systemVersion = '([^']+)'/", $content, $matches)) {
    $currentVersion = $matches[1];
    echo "<p class='info'>📋 Versão atual: <strong>$currentVersion</strong></p>";
    
    if ($currentVersion === '2.2.0') {
        echo "<p class='warning'>⚠️ Versão 2.2.0 já aplicada</p>";
    } else {
        echo "<p class='success'>✅ Pronto para atualizar para v2.2.0</p>";
    }
} else {
    echo "<p class='warning'>⚠️ Não foi possível detectar versão atual</p>";
}

// Verificar se já tem a nova entrada
if (strpos($content, 'Aprimoramento Completo do Sistema POPs e ITs') !== false) {
    echo "<p class='warning'>⚠️ Changelog v2.2.0 já existe</p>";
} else {
    echo "<p class='success'>✅ Novo changelog será adicionado</p>";
}
echo "</div>";

// Aplicar atualização
echo "<div class='card'>";
echo "<h2>4. Aplicando Atualização...</h2>";

// Atualizar versão do sistema
$content = preg_replace(
    "/\\\$systemVersion = '[^']+';/",
    "\$systemVersion = '2.2.0';",
    $content
);

$content = preg_replace(
    "/\\\$lastUpdate = '[^']+';/",
    "\$lastUpdate = '29/09/2025';",
    $content
);

echo "<p class='success'>✅ Versão do sistema atualizada para 2.2.0</p>";

// Adicionar nova entrada no changelog (se não existir)
if (strpos($content, 'Aprimoramento Completo do Sistema POPs e ITs') === false) {
    $newChangelogEntry = "            [
                'version' => '2.2.0',
                'date' => '29/09/2025',
                'type' => 'Melhoria',
                'title' => 'Aprimoramento Completo do Sistema POPs e ITs',
                'description' => 'Melhorias significativas no módulo POPs e ITs com correções de acesso e otimizações',
                'items' => [
                    'Corrigido sistema de acesso para páginas \"Em Breve\"',
                    'Fluxogramas agora mostra interface amigável em vez de erro',
                    'Otimizado PermissionMiddleware para rotas públicas',
                    'Melhorada experiência do usuário em módulos em desenvolvimento',
                    'Sistema de diagnóstico e correção automática implementado'
                ]
            ],
            ";
    
    // Encontrar onde inserir (após $allUpdates = [)
    $content = preg_replace(
        "/(\\\$allUpdates = \[\s*)/",
        "$1$newChangelogEntry",
        $content
    );
    
    echo "<p class='success'>✅ Nova entrada do changelog adicionada</p>";
} else {
    echo "<p class='info'>📋 Entrada do changelog já existe</p>";
}
echo "</div>";

// Salvar arquivo atualizado
echo "<div class='card'>";
echo "<h2>5. Salvando Alterações...</h2>";

if (file_put_contents($homeControllerFile, $content)) {
    echo "<p class='success'>✅ Arquivo atualizado com sucesso!</p>";
} else {
    echo "<p class='error'>❌ Falha ao salvar arquivo atualizado</p>";
    echo "</div></body></html>";
    exit;
}
echo "</div>";

// Verificar sintaxe
echo "<div class='card'>";
echo "<h2>6. Verificando Sintaxe...</h2>";

$output = [];
$returnVar = 0;
exec("php -l " . escapeshellarg($homeControllerFile) . " 2>&1", $output, $returnVar);

if ($returnVar === 0) {
    echo "<p class='success'>✅ Sintaxe PHP válida!</p>";
} else {
    echo "<p class='error'>❌ Erro de sintaxe:</p>";
    echo "<div class='code'>" . implode('<br>', $output) . "</div>";
}
echo "</div>";

// Mostrar resumo da nova versão
echo "<div class='card'>";
echo "<h2>7. Resumo da Nova Versão</h2>";

echo "<div class='code'>";
echo "<strong>🎯 VERSÃO 2.2.0 - 29/09/2025</strong><br>";
echo "<strong>Título:</strong> Aprimoramento Completo do Sistema POPs e ITs<br><br>";

echo "<strong>📋 Melhorias Implementadas:</strong><br>";
echo "• Corrigido sistema de acesso para páginas \"Em Breve\"<br>";
echo "• Fluxogramas agora mostra interface amigável em vez de erro<br>";
echo "• Otimizado PermissionMiddleware para rotas públicas<br>";
echo "• Melhorada experiência do usuário em módulos em desenvolvimento<br>";
echo "• Sistema de diagnóstico e correção automática implementado<br><br>";

echo "<strong>🔧 Impacto Técnico:</strong><br>";
echo "• PermissionMiddleware atualizado com rotas \"Em Breve\"<br>";
echo "• Fluxogramas, Controle de RC e Homologações acessíveis<br>";
echo "• Interface consistente para módulos em desenvolvimento<br>";
echo "• Sistema de diagnóstico para identificação de problemas<br>";
echo "</div>";
echo "</div>";

// Resultado final
echo "<hr>";
if ($returnVar === 0) {
    echo "<div style='background:#e6ffe6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='success'>🎉 CHANGELOG ATUALIZADO COM SUCESSO!</h2>";
    echo "<p>A versão 2.2.0 foi adicionada ao sistema. Os usuários verão a nova atualização na página inicial.</p>";
    echo "<a href='/inicio' class='btn'>🏠 VER PÁGINA INICIAL</a>";
    echo "<a href='/' class='btn' style='background:#0066cc;'>🔗 TESTAR SISTEMA</a>";
    echo "</div>";
} else {
    echo "<div style='background:#ffe6e6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='error'>⚠️ PROBLEMAS NA ATUALIZAÇÃO</h2>";
    echo "<p>Há erros de sintaxe que precisam ser corrigidos.</p>";
    echo "</div>";
}

echo "<div style='background:#f0f8ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>📋 O que foi atualizado:</h3>";
echo "<ul>";
echo "<li>✅ Versão do sistema: 2.2.0</li>";
echo "<li>✅ Data da última atualização: 29/09/2025</li>";
echo "<li>✅ Nova entrada no changelog adicionada</li>";
echo "<li>✅ Backup do arquivo original criado</li>";
echo "</ul>";

echo "<h3>👀 Como visualizar:</h3>";
echo "<ol>";
echo "<li>Acesse a página inicial do sistema</li>";
echo "<li>Role até a seção \"Últimas Atualizações\"</li>";
echo "<li>A versão 2.2.0 deve aparecer no topo</li>";
echo "<li>Clique para ver detalhes das melhorias</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:12px;'>";
echo "SGQ OTI DJ - Atualização do Changelog<br>";
echo "Executado em " . date('d/m/Y H:i:s') . " - Versão 2.2.0";
echo "</p>";

echo "</body></html>";
?>
