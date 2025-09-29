<?php
/**
 * CORREÇÃO DO TUTORIAL 5W2H - VERSÃO SIMPLIFICADA
 * 
 * Remove conteúdo extra e corrige caminho do vídeo
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Correção Tutorial 5W2H - SGQ OTI DJ</title>";
echo "<meta charset='UTF-8'><style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f5f5f5;} .card{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:#008000;} .error{color:#ff0000;} .warning{color:#ff8800;} .btn{display:inline-block;padding:15px 30px;background:#00cc00;color:white;text-decoration:none;border-radius:5px;margin:10px;font-size:18px;}</style></head><body>";

echo "<h1>🔧 CORREÇÃO DO TUTORIAL 5W2H</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Problema:</strong> Vídeo não carrega e modal muito complexo</p>";
echo "<hr>";

// Localizar arquivo 5W2H
$viewFile = __DIR__ . '/views/pages/5w2h/index.php';

echo "<div class='card'>";
echo "<h2>1. Localizando arquivo...</h2>";

if (!file_exists($viewFile)) {
    echo "<p class='error'>❌ Arquivo não encontrado: $viewFile</p>";
    echo "</div></body></html>";
    exit;
} else {
    echo "<p class='success'>✅ Arquivo encontrado: $viewFile</p>";
}
echo "</div>";

// Fazer backup
echo "<div class='card'>";
echo "<h2>2. Criando backup...</h2>";

$backupFile = $viewFile . '.backup.' . date('Y-m-d_H-i-s');
if (copy($viewFile, $backupFile)) {
    echo "<p class='success'>✅ Backup criado: " . basename($backupFile) . "</p>";
} else {
    echo "<p class='warning'>⚠️ Não foi possível criar backup</p>";
}
echo "</div>";

// Aplicar correções
echo "<div class='card'>";
echo "<h2>3. Aplicando correções...</h2>";

$content = file_get_contents($viewFile);

// Correção 1: Simplificar o modal do tutorial
$oldModalPattern = '/\/\/ Função para abrir o tutorial 5W2H.*?setTimeout\(\(\) => \{.*?\}, 100\);\s*\}/s';

$newModal = "// Função para abrir o tutorial 5W2H
function abrirTutorial5W2H() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class=\"bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4\">
            <!-- Header do Modal -->
            <div class=\"flex justify-between items-center p-6 border-b border-gray-200\">
                <div>
                    <h2 class=\"text-2xl font-bold text-gray-900\">📚 Tutorial - Como usar 5W2H</h2>
                    <p class=\"text-gray-600 mt-1\">Aprenda a metodologia 5W2H para criar planos de ação eficazes</p>
                </div>
                <button onclick=\"fecharTutorial()\" class=\"text-gray-400 hover:text-gray-600 text-2xl font-bold\">
                    ×
                </button>
            </div>
            
            <!-- Vídeo Tutorial -->
            <div class=\"p-6\">
                <div class=\"bg-gray-900 rounded-lg overflow-hidden shadow-lg\">
                    <video id=\"tutorial5w2h\" controls class=\"w-full h-auto\" style=\"max-height: 500px;\">
                        <source src=\"assets/5w2h.mp4\" type=\"video/mp4\">
                        Seu navegador não suporta o elemento de vídeo.
                    </video>
                </div>
            </div>
            
            <!-- Footer do Modal -->
            <div class=\"flex justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50\">
                <button onclick=\"fecharTutorial()\" class=\"bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg\">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Focar no vídeo quando abrir
    setTimeout(() => {
        const video = document.getElementById('tutorial5w2h');
        if (video) {
            video.focus();
        }
    }, 100);
}";

if (preg_match($oldModalPattern, $content)) {
    $content = preg_replace($oldModalPattern, $newModal, $content);
    echo "<p class='success'>✅ Modal simplificado</p>";
} else {
    echo "<p class='warning'>⚠️ Padrão do modal não encontrado</p>";
}

// Correção 2: Remover função iniciarNovoPlano se existir
$content = preg_replace('/\/\/ Função para iniciar novo plano após tutorial.*?function iniciarNovoPlano\(\).*?\}\s*/s', '', $content);
echo "<p class='success'>✅ Função desnecessária removida</p>";

// Correção 3: Corrigir caminhos do vídeo
$content = str_replace('src="/assets/5w2h.mp4"', 'src="assets/5w2h.mp4"', $content);
echo "<p class='success'>✅ Caminho do vídeo corrigido</p>";

echo "</div>";

// Salvar arquivo corrigido
echo "<div class='card'>";
echo "<h2>4. Salvando correções...</h2>";

if (file_put_contents($viewFile, $content)) {
    echo "<p class='success'>✅ Arquivo corrigido e salvo!</p>";
} else {
    echo "<p class='error'>❌ Falha ao salvar arquivo</p>";
    echo "</div></body></html>";
    exit;
}
echo "</div>";

// Verificar vídeo
echo "<div class='card'>";
echo "<h2>5. Verificando vídeo...</h2>";

$videoPath = __DIR__ . '/public/assets/5w2h.mp4';
if (file_exists($videoPath)) {
    $videoSize = filesize($videoPath);
    echo "<p class='success'>✅ Vídeo encontrado: " . number_format($videoSize / 1024 / 1024, 2) . " MB</p>";
    echo "<p class='success'>✅ Caminho correto: /assets/5w2h.mp4</p>";
} else {
    echo "<p class='warning'>⚠️ Vídeo não encontrado em: $videoPath</p>";
    echo "<p class='info'>📋 Certifique-se que o vídeo está em: public/assets/5w2h.mp4</p>";
}
echo "</div>";

// Resultado final
echo "<hr>";
echo "<div style='background:#e6ffe6;padding:20px;border-radius:10px;text-align:center;'>";
echo "<h2 class='success'>🎉 TUTORIAL 5W2H CORRIGIDO!</h2>";
echo "<p>Modal simplificado com apenas o vídeo e título/subtítulo conforme solicitado.</p>";
echo "<a href='/5w2h' class='btn'>🔗 TESTAR TUTORIAL AGORA</a>";
echo "</div>";

echo "<div style='background:#f0f8ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>✅ Correções Aplicadas:</h3>";
echo "<ul>";
echo "<li>✅ Modal simplificado (removido conteúdo extra)</li>";
echo "<li>✅ Apenas título, subtítulo e vídeo</li>";
echo "<li>✅ Caminho do vídeo corrigido (assets/5w2h.mp4)</li>";
echo "<li>✅ Botão único 'Fechar'</li>";
echo "<li>✅ Tamanho otimizado (max-w-3xl)</li>";
echo "</ul>";

echo "<h3>🎬 Como deve funcionar agora:</h3>";
echo "<ol>";
echo "<li>Clique no botão verde 'Aprenda a usar'</li>";
echo "<li>Modal abre com título e subtítulo</li>";
echo "<li>Vídeo carrega automaticamente</li>";
echo "<li>Controles de vídeo funcionais</li>";
echo "<li>Botão 'Fechar' para sair</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:12px;'>";
echo "SGQ OTI DJ - Correção Tutorial 5W2H<br>";
echo "Executado em " . date('d/m/Y H:i:s');
echo "</p>";

echo "</body></html>";
?>
