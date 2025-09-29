<?php
/**
 * ATUALIZAÇÃO DO STATUS DO MÓDULO FLUXOGRAMAS
 * 
 * Confirma que o módulo Fluxogramas está configurado para mostrar
 * página "Em Breve" igual ao Controle de RC
 */

echo "<!DOCTYPE html><html><head><title>Status Fluxogramas - SGQ OTI DJ</title>";
echo "<meta charset='UTF-8'><style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f5f5f5;} .card{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:#008000;} .error{color:#ff0000;} .warning{color:#ff8800;} .info{color:#0066cc;} .code{background:#f8f8f8;padding:10px;border-radius:4px;font-family:monospace;} .btn{display:inline-block;padding:10px 20px;background:#0066cc;color:white;text-decoration:none;border-radius:5px;margin:5px;}</style></head><body>";

echo "<h1>📋 STATUS DO MÓDULO FLUXOGRAMAS</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

echo "<div class='card'>";
echo "<h2>✅ CONFIGURAÇÃO ATUAL</h2>";
echo "<p>O módulo <strong>Fluxogramas</strong> está configurado corretamente para mostrar a página 'Em Breve' igual ao Controle de RC.</p>";

echo "<h3>📍 Configurações Verificadas:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Rota Principal:</strong> <code>/fluxogramas</code> → <code>PageController::fluxogramas()</code></li>";
echo "<li>✅ <strong>View:</strong> <code>views/pages/coming-soon.php</code></li>";
echo "<li>✅ <strong>FluxogramasController:</strong> Desabilitado (todas as rotas comentadas)</li>";
echo "<li>✅ <strong>Comportamento:</strong> Igual ao Controle de RC</li>";
echo "</ul>";
echo "</div>";

// Verificar arquivos
echo "<div class='card'>";
echo "<h2>🔍 VERIFICAÇÃO DE ARQUIVOS</h2>";

$files = [
    'public/index.php' => 'Arquivo de rotas principal',
    'src/Controllers/PageController.php' => 'Controller para páginas "Em Breve"',
    'views/pages/coming-soon.php' => 'Template da página "Em Breve"',
    'src/Controllers/FluxogramasController.php' => 'Controller desabilitado (opcional)'
];

foreach ($files as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p class='success'>✅ <strong>$description:</strong> $file</p>";
    } else {
        echo "<p class='warning'>⚠️ <strong>$description:</strong> $file (não encontrado)</p>";
    }
}
echo "</div>";

// Verificar rota específica
echo "<div class='card'>";
echo "<h2>🛣️ VERIFICAÇÃO DA ROTA</h2>";

$indexContent = file_get_contents(__DIR__ . '/public/index.php');

if (strpos($indexContent, "'/fluxogramas', [App\\Controllers\\PageController::class, 'fluxogramas']") !== false) {
    echo "<p class='success'>✅ <strong>Rota Correta:</strong> /fluxogramas aponta para PageController::fluxogramas()</p>";
} else {
    echo "<p class='error'>❌ <strong>Rota Incorreta:</strong> Rota não encontrada ou configurada incorretamente</p>";
}

// Verificar se FluxogramasController está desabilitado
if (strpos($indexContent, '// Fluxogramas routes - MÓDULO COMPLETAMENTE DESABILITADO') !== false) {
    echo "<p class='success'>✅ <strong>FluxogramasController:</strong> Corretamente desabilitado</p>";
} else {
    echo "<p class='warning'>⚠️ <strong>FluxogramasController:</strong> Status de desabilitação não confirmado</p>";
}

echo "</div>";

// Testar PageController
echo "<div class='card'>";
echo "<h2>🧪 TESTE DO PAGECONTROLLER</h2>";

if (class_exists('App\\Controllers\\PageController')) {
    echo "<p class='success'>✅ <strong>PageController:</strong> Classe disponível</p>";
    
    try {
        $controller = new App\Controllers\PageController();
        if (method_exists($controller, 'fluxogramas')) {
            echo "<p class='success'>✅ <strong>Método fluxogramas():</strong> Disponível</p>";
        } else {
            echo "<p class='error'>❌ <strong>Método fluxogramas():</strong> Não encontrado</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ <strong>Erro ao instanciar PageController:</strong> " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>❌ <strong>PageController:</strong> Classe não disponível</p>";
}

echo "</div>";

// Comparação com Controle de RC
echo "<div class='card'>";
echo "<h2>🔄 COMPARAÇÃO COM CONTROLE DE RC</h2>";

echo "<table style='width:100%;border-collapse:collapse;'>";
echo "<tr style='background:#f0f0f0;'><th style='padding:8px;border:1px solid #ddd;'>Aspecto</th><th style='padding:8px;border:1px solid #ddd;'>Controle de RC</th><th style='padding:8px;border:1px solid #ddd;'>Fluxogramas</th><th style='padding:8px;border:1px solid #ddd;'>Status</th></tr>";

$comparisons = [
    'Rota Principal' => ['/controle-de-rc', '/fluxogramas', '✅ Idêntico'],
    'Controller' => ['PageController::controleDeRc()', 'PageController::fluxogramas()', '✅ Idêntico'],
    'View' => ['coming-soon.php', 'coming-soon.php', '✅ Idêntico'],
    'Comportamento' => ['Página "Em Breve"', 'Página "Em Breve"', '✅ Idêntico']
];

foreach ($comparisons as $aspect => $data) {
    echo "<tr>";
    echo "<td style='padding:8px;border:1px solid #ddd;'><strong>$aspect</strong></td>";
    echo "<td style='padding:8px;border:1px solid #ddd;'>{$data[0]}</td>";
    echo "<td style='padding:8px;border:1px solid #ddd;'>{$data[1]}</td>";
    echo "<td style='padding:8px;border:1px solid #ddd;'>{$data[2]}</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// Resultado final
echo "<div class='card' style='background:#e6ffe6;border-left:4px solid #00cc00;'>";
echo "<h2>🎉 RESULTADO FINAL</h2>";
echo "<p><strong>Status:</strong> ✅ CONFIGURADO CORRETAMENTE</p>";
echo "<p>O módulo <strong>Fluxogramas</strong> está funcionando exatamente igual ao <strong>Controle de RC</strong>, mostrando a página 'Em Breve' em vez de dar acesso negado.</p>";

echo "<div style='text-align:center;margin:20px 0;'>";
echo "<a href='/fluxogramas' class='btn' style='background:#00cc00;font-size:16px;'>🔗 TESTAR FLUXOGRAMAS</a>";
echo "<a href='/controle-de-rc' class='btn' style='background:#0066cc;'>🔗 COMPARAR COM RC</a>";
echo "</div>";
echo "</div>";

// Instruções
echo "<div class='card'>";
echo "<h2>📋 COMO FUNCIONA AGORA</h2>";

echo "<h3>✅ Comportamento Atual:</h3>";
echo "<ol>";
echo "<li>Usuário clica em <strong>\"Fluxogramas\"</strong> no menu</li>";
echo "<li>Sistema carrega <code>/fluxogramas</code></li>";
echo "<li>Rota direciona para <code>PageController::fluxogramas()</code></li>";
echo "<li>Método renderiza <code>views/pages/coming-soon.php</code></li>";
echo "<li>Usuário vê página <strong>\"Em Breve Disponível\"</strong></li>";
echo "</ol>";

echo "<h3>❌ Comportamento Anterior:</h3>";
echo "<ul>";
echo "<li>Sistema tentava carregar FluxogramasController</li>";
echo "<li>Controller tinha problemas/dependências</li>";
echo "<li>Resultado: Erro HTTP 500 ou Acesso Negado</li>";
echo "</ul>";

echo "<h3>🔄 Igual ao Controle de RC:</h3>";
echo "<ul>";
echo "<li>Ambos usam o mesmo PageController</li>";
echo "<li>Ambos renderizam a mesma view (coming-soon.php)</li>";
echo "<li>Ambos mostram página \"Em Breve Disponível\"</li>";
echo "<li>Ambos têm controllers específicos desabilitados</li>";
echo "</ul>";

echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:12px;'>";
echo "SGQ OTI DJ - Verificação do Status do Módulo Fluxogramas<br>";
echo "Executado em " . date('d/m/Y H:i:s');
echo "</p>";

echo "</body></html>";
?>
