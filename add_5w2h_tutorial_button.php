<?php
/**
 * ADIÇÃO DO BOTÃO "APRENDA A USAR" NO MÓDULO 5W2H
 * 
 * Este script adiciona o botão de tutorial com vídeo explicativo
 * ao lado do botão "Novo Plano 5W2H"
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Tutorial 5W2H - SGQ OTI DJ</title>";
echo "<meta charset='UTF-8'><style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f5f5f5;} .card{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:#008000;} .error{color:#ff0000;} .warning{color:#ff8800;} .info{color:#0066cc;} .code{background:#f8f8f8;padding:10px;border-radius:4px;font-family:monospace;font-size:12px;} .btn{display:inline-block;padding:15px 30px;background:#00cc00;color:white;text-decoration:none;border-radius:5px;margin:10px;font-size:18px;}</style></head><body>";

echo "<h1>📚 ADIÇÃO DO TUTORIAL 5W2H</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Funcionalidade:</strong> Botão 'Aprenda a usar' com vídeo tutorial</p>";
echo "<hr>";

// Localizar arquivo 5W2H
$viewFile = __DIR__ . '/views/pages/5w2h/index.php';

echo "<div class='card'>";
echo "<h2>1. Localizando View do 5W2H...</h2>";

if (!file_exists($viewFile)) {
    echo "<p class='error'>❌ Arquivo não encontrado: $viewFile</p>";
    
    // Tentar outros locais
    $possiblePaths = [
        __DIR__ . '/views/pages/5w2h/index.php',
        __DIR__ . '/resources/views/5w2h/index.php',
        __DIR__ . '/templates/5w2h/index.php'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $viewFile = $path;
            echo "<p class='success'>✅ Arquivo encontrado em: $path</p>";
            break;
        }
    }
    
    if (!file_exists($viewFile)) {
        echo "<p class='error'>❌ View do 5W2H não encontrada em nenhum local</p>";
        echo "</div></body></html>";
        exit;
    }
} else {
    echo "<p class='success'>✅ Arquivo encontrado: $viewFile</p>";
}
echo "</div>";

// Verificar se o vídeo existe
echo "<div class='card'>";
echo "<h2>2. Verificando Vídeo Tutorial...</h2>";

$videoFile = __DIR__ . '/public/assets/5w2h.mp4';
if (file_exists($videoFile)) {
    $videoSize = filesize($videoFile);
    echo "<p class='success'>✅ Vídeo encontrado: " . number_format($videoSize / 1024 / 1024, 2) . " MB</p>";
} else {
    echo "<p class='warning'>⚠️ Vídeo não encontrado em: $videoFile</p>";
    echo "<p class='info'>📋 O vídeo deve estar em: /public/assets/5w2h.mp4</p>";
}
echo "</div>";

// Fazer backup
echo "<div class='card'>";
echo "<h2>3. Criando Backup...</h2>";

$backupFile = $viewFile . '.backup.' . date('Y-m-d_H-i-s');
if (copy($viewFile, $backupFile)) {
    echo "<p class='success'>✅ Backup criado: " . basename($backupFile) . "</p>";
} else {
    echo "<p class='warning'>⚠️ Não foi possível criar backup</p>";
}
echo "</div>";

// Ler conteúdo atual
echo "<div class='card'>";
echo "<h2>4. Analisando Conteúdo Atual...</h2>";

$content = file_get_contents($viewFile);

// Verificar se já tem o botão tutorial
if (strpos($content, 'abrirTutorial5W2H') !== false) {
    echo "<p class='warning'>⚠️ Botão tutorial já existe</p>";
} else {
    echo "<p class='success'>✅ Pronto para adicionar botão tutorial</p>";
}

// Verificar se tem o botão "Novo Plano"
if (strpos($content, 'Novo Plano 5W2H') !== false) {
    echo "<p class='success'>✅ Botão 'Novo Plano 5W2H' encontrado</p>";
} else {
    echo "<p class='error'>❌ Botão 'Novo Plano 5W2H' não encontrado</p>";
}
echo "</div>";

// Aplicar modificações
echo "<div class='card'>";
echo "<h2>5. Aplicando Modificações...</h2>";

$modificado = false;

// Modificação 1: Adicionar botão "Aprenda a usar"
if (strpos($content, 'abrirTutorial5W2H') === false) {
    $oldButtonPattern = '<button onclick="toggleFormulario()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">';
    
    $newButtonsHtml = '<div class="flex gap-3">
            <button onclick="abrirTutorial5W2H()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m2-10v18a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h6l2 2z"></path>
                </svg>
                Aprenda a usar
            </button>
            <button onclick="toggleFormulario()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">';
    
    if (strpos($content, $oldButtonPattern) !== false) {
        $content = str_replace($oldButtonPattern, $newButtonsHtml, $content);
        
        // Adicionar fechamento da div
        $content = str_replace(
            '<span id="btnText">Novo Plano 5W2H</span>
        </button>',
            '<span id="btnText">Novo Plano 5W2H</span>
            </button>
        </div>',
            $content
        );
        
        echo "<p class='success'>✅ Botão 'Aprenda a usar' adicionado</p>";
        $modificado = true;
    } else {
        echo "<p class='error'>❌ Padrão do botão não encontrado para modificação</p>";
    }
}

// Modificação 2: Adicionar JavaScript do tutorial
if (strpos($content, 'function abrirTutorial5W2H') === false) {
    $tutorialJs = '
// Função para abrir o tutorial 5W2H
function abrirTutorial5W2H() {
    const modal = document.createElement(\'div\');
    modal.className = \'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50\';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <!-- Header do Modal -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">📚 Tutorial - Como usar 5W2H</h2>
                    <p class="text-gray-600 mt-1">Aprenda a metodologia 5W2H para criar planos de ação eficazes</p>
                </div>
                <button onclick="fecharTutorial()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
                    ×
                </button>
            </div>
            
            <!-- Conteúdo do Modal -->
            <div class="p-6">
                <!-- Vídeo Tutorial -->
                <div class="mb-6">
                    <div class="bg-gray-900 rounded-lg overflow-hidden shadow-lg">
                        <video id="tutorial5w2h" controls class="w-full h-auto" style="max-height: 400px;">
                            <source src="/assets/5w2h.mp4" type="video/mp4">
                            Seu navegador não suporta o elemento de vídeo.
                        </video>
                    </div>
                </div>
                
                <!-- Resumo da Metodologia -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-blue-800 mb-3">🎯 Os 5 W\'s</h3>
                        <div class="space-y-2">
                            <div class="flex items-start gap-2">
                                <span class="font-semibold text-blue-700">What:</span>
                                <span class="text-gray-700">O que será feito?</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="font-semibold text-blue-700">Why:</span>
                                <span class="text-gray-700">Por que será feito?</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="font-semibold text-blue-700">Who:</span>
                                <span class="text-gray-700">Quem fará?</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="font-semibold text-blue-700">When:</span>
                                <span class="text-gray-700">Quando será feito?</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="font-semibold text-blue-700">Where:</span>
                                <span class="text-gray-700">Onde será feito?</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-green-800 mb-3">💡 Os 2 H\'s</h3>
                        <div class="space-y-2">
                            <div class="flex items-start gap-2">
                                <span class="font-semibold text-green-700">How:</span>
                                <span class="text-gray-700">Como será feito?</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="font-semibold text-green-700">How Much:</span>
                                <span class="text-gray-700">Quanto custará?</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-3 bg-green-100 rounded-lg">
                            <h4 class="font-semibold text-green-800 mb-2">✨ Dica Importante:</h4>
                            <p class="text-sm text-green-700">
                                Responder essas 7 perguntas garante que seu plano de ação seja 
                                completo, claro e executável!
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Benefícios -->
                <div class="mt-6 bg-yellow-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-3">🏆 Benefícios da Metodologia 5W2H</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl mb-2">🎯</div>
                            <h4 class="font-semibold text-yellow-800">Clareza</h4>
                            <p class="text-sm text-yellow-700">Objetivos bem definidos</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl mb-2">⚡</div>
                            <h4 class="font-semibold text-yellow-800">Eficiência</h4>
                            <p class="text-sm text-yellow-700">Execução mais rápida</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl mb-2">📊</div>
                            <h4 class="font-semibold text-yellow-800">Controle</h4>
                            <p class="text-sm text-yellow-700">Acompanhamento facilitado</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer do Modal -->
            <div class="flex justify-between items-center p-6 border-t border-gray-200 bg-gray-50">
                <div class="text-sm text-gray-600">
                    💡 Assista ao vídeo completo para dominar a metodologia!
                </div>
                <div class="flex gap-3">
                    <button onclick="iniciarNovoPlano()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Criar Meu Primeiro Plano
                    </button>
                    <button onclick="fecharTutorial()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                        Fechar Tutorial
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Focar no vídeo quando abrir
    setTimeout(() => {
        const video = document.getElementById(\'tutorial5w2h\');
        if (video) {
            video.focus();
        }
    }, 100);
}

// Função para fechar o tutorial
function fecharTutorial() {
    const modal = document.querySelector(\'.fixed.inset-0.bg-black.bg-opacity-75\');
    if (modal) {
        // Pausar o vídeo antes de fechar
        const video = modal.querySelector(\'video\');
        if (video) {
            video.pause();
        }
        modal.remove();
    }
}

// Função para iniciar novo plano após tutorial
function iniciarNovoPlano() {
    fecharTutorial();
    toggleFormulario();
    // Scroll para o formulário
    setTimeout(() => {
        document.getElementById(\'formularioInline\').scrollIntoView({ 
            behavior: \'smooth\', 
            block: \'start\' 
        });
    }, 300);
}

// Fechar modal com ESC
document.addEventListener(\'keydown\', function(e) {
    if (e.key === \'Escape\') {
        fecharTutorial();
    }
});';

    // Adicionar antes do fechamento do script
    $content = str_replace('</script>', $tutorialJs . "\n</script>", $content);
    echo "<p class='success'>✅ JavaScript do tutorial adicionado</p>";
    $modificado = true;
}

echo "</div>";

// Salvar arquivo modificado
echo "<div class='card'>";
echo "<h2>6. Salvando Modificações...</h2>";

if ($modificado) {
    if (file_put_contents($viewFile, $content)) {
        echo "<p class='success'>✅ Arquivo atualizado com sucesso!</p>";
    } else {
        echo "<p class='error'>❌ Falha ao salvar arquivo modificado</p>";
        echo "</div></body></html>";
        exit;
    }
} else {
    echo "<p class='info'>📋 Nenhuma modificação necessária</p>";
}
echo "</div>";

// Verificar sintaxe
echo "<div class='card'>";
echo "<h2>7. Verificação Final...</h2>";

// Verificar se o arquivo foi salvo corretamente
$newContent = file_get_contents($viewFile);
if (strpos($newContent, 'abrirTutorial5W2H') !== false) {
    echo "<p class='success'>✅ Botão tutorial presente no arquivo</p>";
} else {
    echo "<p class='error'>❌ Botão tutorial não encontrado</p>";
}

if (strpos($newContent, 'function abrirTutorial5W2H') !== false) {
    echo "<p class='success'>✅ JavaScript do tutorial presente</p>";
} else {
    echo "<p class='error'>❌ JavaScript do tutorial não encontrado</p>";
}
echo "</div>";

// Resultado final
echo "<hr>";
if ($modificado) {
    echo "<div style='background:#e6ffe6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='success'>🎉 TUTORIAL 5W2H ADICIONADO COM SUCESSO!</h2>";
    echo "<p>O botão 'Aprenda a usar' foi adicionado ao módulo 5W2H com vídeo tutorial completo.</p>";
    echo "<a href='/5w2h' class='btn'>🔗 TESTAR MÓDULO 5W2H</a>";
    echo "</div>";
} else {
    echo "<div style='background:#fff8e6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='warning'>📋 TUTORIAL JÁ ESTAVA PRESENTE</h2>";
    echo "<p>O botão tutorial já existia no sistema.</p>";
    echo "<a href='/5w2h' class='btn' style='background:#ff8800;'>🔗 VERIFICAR MÓDULO 5W2H</a>";
    echo "</div>";
}

echo "<div style='background:#f0f8ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>📋 Funcionalidades Adicionadas:</h3>";
echo "<ul>";
echo "<li>✅ Botão 'Aprenda a usar' (verde) ao lado do 'Novo Plano'</li>";
echo "<li>✅ Modal com vídeo tutorial (/assets/5w2h.mp4)</li>";
echo "<li>✅ Resumo da metodologia 5W2H</li>";
echo "<li>✅ Benefícios e dicas da metodologia</li>";
echo "<li>✅ Botão para criar plano após tutorial</li>";
echo "<li>✅ Controles de vídeo (play, pause, volume)</li>";
echo "<li>✅ Fechamento com ESC ou botão X</li>";
echo "</ul>";

echo "<h3>🎯 Como usar:</h3>";
echo "<ol>";
echo "<li>Acesse o módulo 5W2H</li>";
echo "<li>Clique no botão verde 'Aprenda a usar'</li>";
echo "<li>Assista ao vídeo tutorial</li>";
echo "<li>Leia o resumo da metodologia</li>";
echo "<li>Clique em 'Criar Meu Primeiro Plano' para começar</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:12px;'>";
echo "SGQ OTI DJ - Tutorial 5W2H<br>";
echo "Executado em " . date('d/m/Y H:i:s');
echo "</p>";

echo "</body></html>";
?>
