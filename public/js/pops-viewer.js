/**
 * Sistema de Visualização de Arquivos POPs e ITs
 * Suporta: PDF (visualização inline), PPT/PPTX (visualizadores online), Imagens
 */

// Função principal para abrir/visualizar arquivo
function visualizarArquivo(registroId, nomeArquivo, extensao) {
    const baseUrl = `/pops-its/arquivo/${registroId}`;
    
    // Normalizar extensão
    extensao = extensao.toLowerCase().replace('.', '');
    
    console.log(`📄 Visualizando arquivo: ${nomeArquivo} (${extensao})`);
    
    // PDF e Imagens: Visualização direta no navegador
    if (['pdf', 'png', 'jpg', 'jpeg', 'gif'].includes(extensao)) {
        window.open(baseUrl, '_blank');
        return;
    }
    
    // PPT/PPTX: Mostrar modal com opções
    if (['ppt', 'pptx'].includes(extensao)) {
        mostrarOpcoesVisualizacaoPPT(registroId, nomeArquivo, baseUrl);
        return;
    }
    
    // Outros arquivos: Download direto
    downloadArquivo(registroId);
}

// Modal com opções para visualizar PPT/PPTX
function mostrarOpcoesVisualizacaoPPT(registroId, nomeArquivo, fileUrl) {
    // Registrar log de visualização IMEDIATAMENTE quando modal abre
    registrarLogVisualizacao(registroId);
    
    const modal = document.createElement('div');
    modal.id = 'modalVisualizacaoPPT';
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center p-4';
    
    // Construir URLs dos visualizadores
    const googleDocsUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(window.location.origin + fileUrl)}&embedded=true`;
    const officeUrl = `https://view.officeapps.live.com/op/view.aspx?src=${encodeURIComponent(window.location.origin + fileUrl)}`;
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full transform transition-all">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex items-center space-x-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Visualizar Apresentação</h3>
                        <p class="text-sm text-blue-100">${nomeArquivo}</p>
                    </div>
                </div>
                <button onclick="fecharModalVisualizacao()" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600">
                    📊 Arquivos PowerPoint não podem ser visualizados diretamente no navegador. 
                    Escolha uma das opções abaixo:
                </p>
                
                <!-- Opção 1: Google Docs Viewer -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:bg-blue-50 transition-colors cursor-pointer"
                     onclick="abrirVisualizadorOnline('google', '${googleDocsUrl}')">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.372 0 0 5.373 0 12s5.372 12 12 12c6.627 0 12-5.373 12-12S18.627 0 12 0zm.14 19.018c-3.868 0-7-3.14-7-7.018s3.132-7.018 7-7.018c1.89 0 3.47.697 4.682 1.829l-1.974 1.978v-.004c-.735-.702-1.667-1.062-2.708-1.062-2.31 0-4.187 1.956-4.187 4.273 0 2.315 1.877 4.277 4.187 4.277 2.096 0 3.522-1.202 3.816-2.852H12.14v-2.737h6.585c.088.47.135.96.135 1.474 0 4.01-2.677 6.86-6.72 6.86z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">🌐 Visualizar com Google Docs</h4>
                            <p class="text-sm text-gray-600 mt-1">Visualização online gratuita (recomendado para arquivos menores)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Opção 2: Microsoft Office Viewer -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-orange-300 hover:bg-orange-50 transition-colors cursor-pointer"
                     onclick="abrirVisualizadorOnline('office', '${officeUrl}')">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13.5 1.5v21l9-4.5V6l-9-4.5zM11 1.5l-9 4v13l9 4.5v-21zM22.5 7.5v9l-9 4.5v-18l9 4.5z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">📊 Visualizar com Microsoft Office</h4>
                            <p class="text-sm text-gray-600 mt-1">Visualizador oficial da Microsoft (recomendado para arquivos maiores)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Opção 3: Download -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-green-300 hover:bg-green-50 transition-colors cursor-pointer"
                     onclick="downloadArquivoDireto('${fileUrl}', '${nomeArquivo}')">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">💾 Baixar Arquivo</h4>
                            <p class="text-sm text-gray-600 mt-1">Fazer download e abrir no Microsoft PowerPoint local</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Os visualizadores online podem levar alguns segundos para carregar arquivos grandes
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Animação de entrada
    setTimeout(() => {
        modal.querySelector('.bg-white').style.transform = 'scale(1)';
        modal.querySelector('.bg-white').style.opacity = '1';
    }, 10);
}

// Fechar modal
function fecharModalVisualizacao() {
    const modal = document.getElementById('modalVisualizacaoPPT');
    if (modal) {
        modal.remove();
    }
}

// Abrir visualizador online
function abrirVisualizadorOnline(tipo, url) {
    console.log(`🌐 Abrindo visualizador ${tipo}:`, url);
    
    // Abrir em nova aba
    const newWindow = window.open(url, '_blank');
    
    if (!newWindow) {
        alert('⚠️ Pop-up bloqueado! Permita pop-ups para este site e tente novamente.');
        return;
    }
    
    // Fechar modal
    fecharModalVisualizacao();
    
    // Feedback
    setTimeout(() => {
        if (tipo === 'google') {
            console.log('✅ Visualizador Google Docs aberto');
        } else {
            console.log('✅ Visualizador Microsoft Office aberto');
        }
    }, 500);
}

// Download direto
function downloadArquivoDireto(url, nomeArquivo) {
    console.log('💾 Iniciando download:', nomeArquivo);
    
    // Criar link temporário para forçar download
    const link = document.createElement('a');
    link.href = url;
    link.download = nomeArquivo;
    link.style.display = 'none';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Fechar modal
    fecharModalVisualizacao();
    
    console.log('✅ Download iniciado');
}

// Função legada de download (manter compatibilidade)
function downloadArquivo(registroId) {
    window.open(`/pops-its/arquivo/${registroId}`, '_blank');
}

// Registrar log de visualização via AJAX
async function registrarLogVisualizacao(registroId) {
    try {
        console.log(`📊 Registrando log de visualização para registro ${registroId}...`);
        console.log(`🔗 Endpoint: /pops-its/registrar-log`);
        
        const formData = new FormData();
        formData.append('registro_id', registroId);
        
        console.log(`📤 Enviando: registro_id=${registroId}`);
        
        const response = await fetch('/pops-its/registrar-log', {
            method: 'POST',
            body: formData
        });
        
        console.log(`📡 Status da resposta: ${response.status} ${response.statusText}`);
        
        const result = await response.json();
        console.log(`📊 Resposta completa:`, result);
        
        if (result.success) {
            console.log('✅ Log de visualização registrado com sucesso!');
            console.log('💡 Verifique a aba "Log de Visualizações" para confirmar');
        } else {
            console.warn('⚠️ Falha ao registrar log:', result.message);
            console.warn('📋 Detalhes:', result);
        }
    } catch (error) {
        console.error('❌ Erro ao registrar log de visualização:', error);
        console.error('📋 Stack:', error.stack);
        // Não bloqueia a visualização se o log falhar
    }
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModalVisualizacao();
    }
});

console.log('📄 Sistema de visualização de arquivos POPs e ITs carregado');
