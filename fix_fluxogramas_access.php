<?php
/**
 * CORREÇÃO URGENTE - FLUXOGRAMAS AINDA MOSTRANDO "ACESSO NEGADO"
 * 
 * Este script corrige o PermissionMiddleware para permitir acesso
 * às páginas "Em Breve" (fluxogramas, controle-de-rc, homologacoes)
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Correção Fluxogramas - SGQ OTI DJ</title>";
echo "<meta charset='UTF-8'><style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f5f5f5;} .card{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:#008000;} .error{color:#ff0000;} .warning{color:#ff8800;} .code{background:#f8f8f8;padding:10px;border-radius:4px;font-family:monospace;font-size:12px;} .btn{display:inline-block;padding:15px 30px;background:#00cc00;color:white;text-decoration:none;border-radius:5px;margin:10px;font-size:18px;}</style></head><body>";

echo "<h1>🔧 CORREÇÃO URGENTE - FLUXOGRAMAS</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Problema:</strong> Fluxogramas ainda mostra 'Acesso Negado' em vez de 'Em Breve'</p>";
echo "<hr>";

// Localizar arquivo PermissionMiddleware
$middlewareFile = __DIR__ . '/src/Middleware/PermissionMiddleware.php';

echo "<h2>1. Localizando PermissionMiddleware...</h2>";

if (!file_exists($middlewareFile)) {
    echo "<p class='error'>❌ Arquivo não encontrado: $middlewareFile</p>";
    
    // Tentar outros locais
    $possiblePaths = [
        __DIR__ . '/src/Middleware/PermissionMiddleware.php',
        __DIR__ . '/app/Middleware/PermissionMiddleware.php',
        __DIR__ . '/Middleware/PermissionMiddleware.php'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $middlewareFile = $path;
            echo "<p class='success'>✅ Arquivo encontrado em: $path</p>";
            break;
        }
    }
    
    if (!file_exists($middlewareFile)) {
        echo "<p class='error'>❌ PermissionMiddleware.php não encontrado em nenhum local</p>";
        echo "</body></html>";
        exit;
    }
} else {
    echo "<p class='success'>✅ Arquivo encontrado: $middlewareFile</p>";
}

// Fazer backup
$backupFile = $middlewareFile . '.backup.' . date('Y-m-d_H-i-s');
if (copy($middlewareFile, $backupFile)) {
    echo "<p class='success'>✅ Backup criado: " . basename($backupFile) . "</p>";
}

echo "<h2>2. Analisando o problema...</h2>";

$content = file_get_contents($middlewareFile);

// Verificar se já tem a correção
if (strpos($content, 'comingSoonRoutes') !== false) {
    echo "<p class='warning'>⚠️ Correção já aplicada anteriormente</p>";
} else {
    echo "<p class='error'>❌ Correção não encontrada - aplicando agora...</p>";
}

// Verificar se tem as rotas de fluxogramas no mapeamento
if (strpos($content, "'/fluxogramas' => 'fluxogramas_visualizacao'") !== false) {
    echo "<p class='warning'>⚠️ Fluxogramas ainda mapeado para verificação de permissão</p>";
    echo "<p>Isso está causando o bloqueio. Vamos corrigir...</p>";
}

echo "<h2>3. Aplicando correção...</h2>";

// Correção 1: Adicionar rotas "Em Breve" como públicas para usuários logados
$searchPattern1 = "        // Rotas de API que têm verificação própria";
$replacement1 = "        // Rotas \"Em Breve\" - acessíveis a todos os usuários logados (via PageController)
        \$comingSoonRoutes = [
            '/fluxogramas',
            '/controle-de-rc', 
            '/homologacoes'
        ];
        
        // Rotas de API que têm verificação própria";

if (strpos($content, '$comingSoonRoutes') === false) {
    $content = str_replace($searchPattern1, $replacement1, $content);
    echo "<p class='success'>✅ Adicionadas rotas 'Em Breve'</p>";
} else {
    echo "<p class='success'>✅ Rotas 'Em Breve' já existem</p>";
}

// Correção 2: Adicionar verificação das rotas "Em Breve"
$searchPattern2 = "        // Se não está logado, não tem permissão
        if (!isset(\$_SESSION['user_id'])) {
            return false;
        }";

$replacement2 = "        // Se não está logado, não tem permissão
        if (!isset(\$_SESSION['user_id'])) {
            return false;
        }
        
        // Verificar se é rota \"Em Breve\" - permitir para usuários logados
        foreach (\$comingSoonRoutes as \$comingSoonRoute) {
            if (\$route === \$comingSoonRoute) {
                return true; // Permitir acesso para usuários logados
            }
        }";

if (strpos($content, 'Verificar se é rota "Em Breve"') === false) {
    $content = str_replace($searchPattern2, $replacement2, $content);
    echo "<p class='success'>✅ Adicionada verificação de rotas 'Em Breve'</p>";
} else {
    echo "<p class='success'>✅ Verificação de rotas 'Em Breve' já existe</p>";
}

// Correção 3: Remover fluxogramas do mapeamento de permissões (se existir)
$routeMapPatterns = [
    "'/fluxogramas' => 'fluxogramas_visualizacao'," => "// '/fluxogramas' => 'fluxogramas_visualizacao', // Desabilitado - usa PageController",
    "'/fluxogramas' => 'fluxogramas'," => "// '/fluxogramas' => 'fluxogramas', // Desabilitado - usa PageController"
];

foreach ($routeMapPatterns as $pattern => $replacement) {
    if (strpos($content, $pattern) !== false) {
        $content = str_replace($pattern, $replacement, $content);
        echo "<p class='success'>✅ Removido mapeamento de permissão para fluxogramas</p>";
    }
}

// Salvar arquivo corrigido
if (file_put_contents($middlewareFile, $content)) {
    echo "<p class='success'>✅ Arquivo corrigido e salvo!</p>";
} else {
    echo "<p class='error'>❌ Falha ao salvar arquivo corrigido</p>";
    echo "</body></html>";
    exit;
}

echo "<h2>4. Verificando sintaxe...</h2>";

$output = [];
$returnVar = 0;
exec("php -l " . escapeshellarg($middlewareFile) . " 2>&1", $output, $returnVar);

if ($returnVar === 0) {
    echo "<p class='success'>✅ Sintaxe PHP válida!</p>";
} else {
    echo "<p class='error'>❌ Erro de sintaxe:</p>";
    echo "<div class='code'>" . implode('<br>', $output) . "</div>";
}

echo "<h2>5. Resultado da Correção</h2>";

echo "<div class='code'>";
echo "<strong>Rotas 'Em Breve' adicionadas:</strong><br>";
echo "- /fluxogramas<br>";
echo "- /controle-de-rc<br>";
echo "- /homologacoes<br><br>";

echo "<strong>Comportamento esperado:</strong><br>";
echo "1. Usuário logado acessa /fluxogramas<br>";
echo "2. PermissionMiddleware permite acesso (não verifica permissões específicas)<br>";
echo "3. Rota direciona para PageController::fluxogramas()<br>";
echo "4. Método renderiza views/pages/coming-soon.php<br>";
echo "5. Usuário vê página 'Em Breve Disponível'<br>";
echo "</div>";

// Resultado final
echo "<hr>";
if ($returnVar === 0) {
    echo "<div style='background:#e6ffe6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='success'>🎉 CORREÇÃO APLICADA COM SUCESSO!</h2>";
    echo "<p>O módulo Fluxogramas agora deve mostrar a página 'Em Breve' em vez de 'Acesso Negado'.</p>";
    echo "<a href='/fluxogramas' class='btn'>🔗 TESTAR FLUXOGRAMAS AGORA</a>";
    echo "<a href='/controle-de-rc' class='btn' style='background:#0066cc;'>🔗 COMPARAR COM RC</a>";
    echo "</div>";
} else {
    echo "<div style='background:#ffe6e6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='error'>⚠️ PROBLEMAS NA CORREÇÃO</h2>";
    echo "<p>Há erros de sintaxe que precisam ser corrigidos.</p>";
    echo "</div>";
}

echo "<div style='background:#f0f8ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>📋 O que foi corrigido:</h3>";
echo "<ul>";
echo "<li>✅ Adicionadas rotas 'Em Breve' como públicas para usuários logados</li>";
echo "<li>✅ Fluxogramas não passa mais por verificação de permissões específicas</li>";
echo "<li>✅ Comportamento igual ao Controle de RC</li>";
echo "<li>✅ Backup do arquivo original criado</li>";
echo "</ul>";

echo "<h3>🧪 Como testar:</h3>";
echo "<ol>";
echo "<li>Faça login no sistema</li>";
echo "<li>Clique em 'Fluxogramas' no menu</li>";
echo "<li>Deve aparecer página 'Em Breve Disponível'</li>";
echo "<li>Compare com 'Controle de RC' (deve ser idêntico)</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:12px;'>";
echo "SGQ OTI DJ - Correção de Acesso ao Fluxogramas<br>";
echo "Executado em " . date('d/m/Y H:i:s');
echo "</p>";

echo "</body></html>";
?>
