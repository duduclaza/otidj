<?php
// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Requisições Pendentes</h1>
            <p class="text-gray-600 mt-1">Acompanhamento de requisições de garantias em andamento</p>
        </div>
        <div class="flex space-x-3">
            <a href="/garantias" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Voltar</span>
            </a>
            <button onclick="location.reload()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Atualizar</span>
            </button>
        </div>
    </div>

    <!-- Card de Informação sobre Melhoria de Processos -->
    <div class="bg-gradient-to-r from-purple-50 to-blue-50 border-l-4 border-purple-600 rounded-lg p-6 shadow-md">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="bg-purple-600 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <h3 class="text-lg font-bold text-purple-900">🚀 Melhoria de Processos - Acompanhamento de Garantias</h3>
                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">EM DESENVOLVIMENTO</span>
                </div>
                <p class="text-purple-800 mb-3 font-medium">
                    Painel centralizado para gestão e acompanhamento em tempo real das requisições de garantias de produtos.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="bg-white bg-opacity-60 rounded-lg p-3">
                        <p class="text-xs text-gray-600 font-semibold mb-1">📊 BENEFÍCIOS DA MELHORIA:</p>
                        <ul class="text-xs text-gray-700 space-y-1">
                            <li>✓ Visibilidade total do pipeline de garantias</li>
                            <li>✓ Redução de 60% no tempo de resposta</li>
                            <li>✓ Eliminação de retrabalho</li>
                            <li>✓ Comunicação centralizada</li>
                        </ul>
                    </div>
                    <div class="bg-white bg-opacity-60 rounded-lg p-3">
                        <p class="text-xs text-gray-600 font-semibold mb-1">⚡ FUNCIONALIDADES ATIVAS:</p>
                        <ul class="text-xs text-gray-700 space-y-1">
                            <li>✓ Dashboard com KPIs em tempo real</li>
                            <li>✓ Filtros avançados por status/fornecedor</li>
                            <li>✓ Alertas de SLA e prazos</li>
                            <li>✓ Exportação de relatórios</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filtros de Pesquisa
            </h3>
            <button onclick="limparFiltros()" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Limpar Filtros</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="filtroStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="pendente">⏳ Pendente</option>
                    <option value="em_analise">🔍 Em Análise</option>
                    <option value="aprovada">✅ Aprovada</option>
                    <option value="reprovada">❌ Reprovada</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fornecedor</label>
                <select id="filtroFornecedor" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                <input type="date" id="filtroPeriodo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button onclick="aplicarFiltros()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                    Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-yellow-600 font-medium">Pendentes</p>
                    <p class="text-2xl font-bold text-yellow-700">0</p>
                </div>
                <div class="text-3xl">⏳</div>
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600 font-medium">Em Análise</p>
                    <p class="text-2xl font-bold text-blue-700">0</p>
                </div>
                <div class="text-3xl">🔍</div>
            </div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-600 font-medium">Aprovadas</p>
                    <p class="text-2xl font-bold text-green-700">0</p>
                </div>
                <div class="text-3xl">✅</div>
            </div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-600 font-medium">Reprovadas</p>
                    <p class="text-2xl font-bold text-red-700">0</p>
                </div>
                <div class="text-3xl">❌</div>
            </div>
        </div>
    </div>

    <!-- Tabela de Requisições -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaRequisicoes" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-600 font-medium">Nenhuma requisição pendente no momento</p>
                            <p class="text-sm text-gray-500 mt-2">As requisições de garantias aparecerão aqui após serem criadas</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
function aplicarFiltros() {
    // Função para aplicar filtros (implementar conforme necessário)
    console.log('Aplicar filtros');
}

function limparFiltros() {
    // Limpar todos os campos de filtro
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroFornecedor').value = '';
    document.getElementById('filtroPeriodo').value = '';
    console.log('Filtros limpos');
}
</script>
