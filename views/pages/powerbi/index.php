<!-- APIs para Power BI -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <span class="text-4xl">📊</span>
                        APIs para Power BI
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Integre os dados do SGQ com seus dashboards do Power BI
                    </p>
                </div>
                <a href="/api/powerbi/documentacao" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Documentação
                </a>
            </div>
        </div>

        <!-- Informações de Autenticação -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">🔐 Autenticação</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p><strong>Para Power BI:</strong> Adicione o token diretamente na URL:</p>
                        <code class="block mt-2 p-2 bg-blue-100 rounded text-xs">
                            ?api_token=sgqoti2024@powerbi
                        </code>
                        <p class="mt-2 text-xs text-blue-600">
                            💡 <strong>Exemplo completo:</strong> /api/powerbi/garantias<strong>?api_token=sgqoti2024@powerbi</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid de APIs Disponíveis -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- API Garantias -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white">Garantias</h3>
                        <span class="text-3xl">🛡️</span>
                    </div>
                    <p class="text-blue-100 text-sm mt-2">Dados completos de garantias</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase">Endpoint</span>
                            <code class="block mt-1 p-2 bg-gray-100 rounded text-xs text-gray-800 break-all">
                                GET /api/powerbi/garantias
                            </code>
                        </div>
                        
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase">Dados Disponíveis</span>
                            <ul class="mt-2 space-y-1 text-sm text-gray-600">
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Dados da garantia
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Itens detalhados
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Dados do fornecedor
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Estatísticas agregadas
                                </li>
                            </ul>
                        </div>

                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase">Filtros Disponíveis</span>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">data_inicio</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">data_fim</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">status</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">fornecedor_id</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">origem</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2">
                        <button onclick="testarAPI('garantias')" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            Testar API
                        </button>
                        <button onclick="copiarURL('garantias')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card: Mais APIs em Breve -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden opacity-60">
                <div class="bg-gradient-to-r from-gray-400 to-gray-500 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white">Toners</h3>
                        <span class="text-3xl">💧</span>
                    </div>
                    <p class="text-gray-100 text-sm mt-2">Em breve</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-500 text-sm">API de toners em desenvolvimento</p>
                    <div class="mt-4 px-3 py-2 bg-gray-100 rounded-lg text-center">
                        <span class="text-xs text-gray-500 font-semibold">EM DESENVOLVIMENTO</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden opacity-60">
                <div class="bg-gradient-to-r from-gray-400 to-gray-500 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white">Amostragens</h3>
                        <span class="text-3xl">🔬</span>
                    </div>
                    <p class="text-gray-100 text-sm mt-2">Em breve</p>
                </div>
                <div class="p-6">
                    <p class="text-gray-500 text-sm">API de amostragens em desenvolvimento</p>
                    <div class="mt-4 px-3 py-2 bg-gray-100 rounded-lg text-center">
                        <span class="text-xs text-gray-500 font-semibold">EM DESENVOLVIMENTO</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Exemplo de Uso no Power BI -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Como Usar no Power BI
            </h2>
            <div class="prose max-w-none">
                <ol class="space-y-4 text-sm text-gray-700">
                    <li>
                        <strong>Abra o Power BI Desktop</strong>
                    </li>
                    <li>
                        <strong>Obter Dados</strong> → <em>Web</em>
                    </li>
                    <li>
                        Cole a URL da API <strong>com o token incluído</strong>:
                        <code class="block mt-1 p-2 bg-green-100 rounded text-xs break-all">
                            <?php echo $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br'; ?>/api/powerbi/garantias<strong class="text-green-700">?api_token=sgqoti2024@powerbi</strong>
                        </code>
                    </li>
                    <li>
                        Clique em <strong>OK</strong> e os dados serão carregados automaticamente
                    </li>
                    <li>
                        Você pode adicionar filtros na URL, por exemplo:
                        <code class="block mt-1 p-2 bg-gray-100 rounded text-xs break-all">
                            /api/powerbi/garantias?api_token=sgqoti2024@powerbi&data_inicio=2024-01-01&status=Em%20andamento
                        </code>
                    </li>
                </ol>
            </div>
        </div>

    </div>
</div>

<!-- Modal de Teste -->
<div id="modalTeste" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900">Teste da API</h3>
            <button onclick="fecharModalTeste()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <div id="resultadoTeste" class="text-sm"></div>
        </div>
    </div>
</div>

<script>
function testarAPI(tipo) {
    const modal = document.getElementById('modalTeste');
    const resultado = document.getElementById('resultadoTeste');
    
    modal.classList.remove('hidden');
    resultado.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 text-gray-600">Carregando...</p></div>';
    
    let url = '/api/powerbi/' + tipo;
    
    fetch(url, {
        headers: {
            'Authorization': 'Bearer sgqoti2024@powerbi'
        }
    })
    .then(response => response.json())
    .then(data => {
        resultado.innerHTML = `
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-green-600 font-semibold">✓ Sucesso</span>
                    <span class="text-xs text-gray-500">${data.generated_at || ''}</span>
                </div>
                <pre class="p-4 bg-gray-900 text-green-400 rounded-lg overflow-x-auto text-xs">${JSON.stringify(data, null, 2)}</pre>
            </div>
        `;
    })
    .catch(error => {
        resultado.innerHTML = `
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-800 font-semibold">❌ Erro ao testar API</p>
                <p class="text-red-600 text-sm mt-2">${error.message}</p>
            </div>
        `;
    });
}

function fecharModalTeste() {
    document.getElementById('modalTeste').classList.add('hidden');
}

function copiarURL(tipo) {
    const baseUrl = '<?php echo $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br'; ?>';
    const url = `${baseUrl}/api/powerbi/${tipo}?api_token=sgqoti2024@powerbi`;
    
    navigator.clipboard.writeText(url).then(() => {
        alert('✓ URL copiada para a área de transferência! Cole no Power BI.');
    });
}

// Fechar modal ao clicar fora
document.getElementById('modalTeste')?.addEventListener('click', (e) => {
    if (e.target.id === 'modalTeste') {
        fecharModalTeste();
    }
});
</script>
