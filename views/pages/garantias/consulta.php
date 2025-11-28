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
            <h1 class="text-2xl font-semibold text-gray-900">Consulta de Garantias</h1>
            <p class="text-gray-600 mt-1">Pesquise o status de uma garantia pelo número do ticket ou série</p>
        </div>
        <a href="/garantias" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Voltar</span>
        </a>
    </div>

    <!-- Formulário de Busca -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar por</label>
                <div class="relative">
                    <input type="text" id="termoBusca" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 pl-12 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Digite o número do ticket (ex: TKG-20251128-0001) ou número de série">
                    <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-end">
                <button onclick="pesquisarGarantia()" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Pesquisar
                </button>
            </div>
        </div>
    </div>

    <!-- Resultado da Busca -->
    <div id="resultadoBusca" class="hidden">
        <!-- Preenchido via JavaScript -->
    </div>

    <!-- Estado Inicial -->
    <div id="estadoInicial" class="bg-white rounded-xl shadow-lg p-12 text-center">
        <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="text-xl font-medium text-gray-600 mb-2">Consulte uma garantia</h3>
        <p class="text-gray-500">Digite o número do ticket ou número de série para ver os detalhes da garantia</p>
    </div>

    <!-- Carregando -->
    <div id="estadoCarregando" class="hidden bg-white rounded-xl shadow-lg p-12 text-center">
        <svg class="w-12 h-12 text-blue-500 mx-auto mb-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-600 font-medium">Pesquisando...</p>
    </div>

    <!-- Nenhum Resultado -->
    <div id="semResultado" class="hidden bg-white rounded-xl shadow-lg p-12 text-center">
        <svg class="w-24 h-24 text-yellow-400 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-xl font-medium text-gray-600 mb-2">Nenhuma garantia encontrada</h3>
        <p class="text-gray-500" id="msgSemResultado">Verifique se o número digitado está correto</p>
    </div>
</section>

<script>
// Permitir busca ao pressionar Enter
document.getElementById('termoBusca').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        pesquisarGarantia();
    }
});

async function pesquisarGarantia() {
    const termo = document.getElementById('termoBusca').value.trim();
    
    if (!termo) {
        alert('Digite um número de ticket ou série para pesquisar');
        return;
    }
    
    // Esconder todos os estados
    document.getElementById('estadoInicial').classList.add('hidden');
    document.getElementById('resultadoBusca').classList.add('hidden');
    document.getElementById('semResultado').classList.add('hidden');
    document.getElementById('estadoCarregando').classList.remove('hidden');
    
    try {
        const response = await fetch(`/garantias/consulta/buscar?termo=${encodeURIComponent(termo)}`);
        const result = await response.json();
        
        document.getElementById('estadoCarregando').classList.add('hidden');
        
        if (result.success && result.data) {
            exibirResultado(result.data);
        } else {
            document.getElementById('msgSemResultado').textContent = result.message || 'Nenhuma garantia encontrada com esse termo';
            document.getElementById('semResultado').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Erro:', error);
        document.getElementById('estadoCarregando').classList.add('hidden');
        document.getElementById('msgSemResultado').textContent = 'Erro ao realizar a pesquisa. Tente novamente.';
        document.getElementById('semResultado').classList.remove('hidden');
    }
}

function exibirResultado(garantia) {
    const container = document.getElementById('resultadoBusca');
    const isPendente = garantia.origem === 'pendente';
    
    // Definir cores do status
    const statusConfig = {
        'Em análise': { bg: 'bg-yellow-100', text: 'text-yellow-800', icon: '🔍' },
        'Aguardando peças': { bg: 'bg-orange-100', text: 'text-orange-800', icon: '📦' },
        'Aguardando Recebimento': { bg: 'bg-purple-100', text: 'text-purple-800', icon: '📥' },
        'Em reparo': { bg: 'bg-blue-100', text: 'text-blue-800', icon: '🔧' },
        'Aprovada': { bg: 'bg-green-100', text: 'text-green-800', icon: '✅' },
        'Reprovada': { bg: 'bg-red-100', text: 'text-red-800', icon: '❌' },
        'Concluída': { bg: 'bg-green-100', text: 'text-green-800', icon: '🎉' },
        'Cancelada': { bg: 'bg-gray-100', text: 'text-gray-800', icon: '🚫' },
        'Processada': { bg: 'bg-green-100', text: 'text-green-800', icon: '✅' }
    };
    
    const config = statusConfig[garantia.status] || { bg: 'bg-gray-100', text: 'text-gray-800', icon: '📋' };
    
    // Cor do header baseado no tipo
    const headerGradient = isPendente 
        ? 'from-orange-500 to-orange-600' 
        : 'from-blue-600 to-blue-700';
    const headerTextLight = isPendente ? 'text-orange-200' : 'text-blue-200';
    
    container.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Tipo de Registro -->
            ${garantia.tipo_registro ? `
            <div class="px-6 py-2 ${isPendente ? 'bg-orange-50 text-orange-700' : 'bg-blue-50 text-blue-700'} text-sm font-medium flex items-center gap-2">
                <span>${isPendente ? '⏳' : '✅'}</span>
                ${garantia.tipo_registro}
            </div>
            ` : ''}
            
            <!-- Header com Status -->
            <div class="bg-gradient-to-r ${headerGradient} p-6 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="${headerTextLight} text-sm mb-1">Número do Ticket</p>
                        <h2 class="text-2xl font-bold">${garantia.numero_ticket || garantia.ticket || 'N/A'}</h2>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-lg font-semibold ${config.bg} ${config.text}">
                            <span>${config.icon}</span>
                            ${garantia.status}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Informações Principais -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 ${isPendente ? '' : 'lg:grid-cols-3'} gap-6 mb-6">
                    <!-- Cliente/Requisitante -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-500 font-medium">${isPendente ? 'Requisitante' : 'Cliente'}</span>
                        </div>
                        <p class="text-gray-900 font-semibold">${garantia.nome_cliente || garantia.nome_requisitante || 'N/A'}</p>
                    </div>
                    
                    <!-- Produto -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-500 font-medium">Produto</span>
                        </div>
                        <p class="text-gray-900 font-semibold">${garantia.produto_nome || garantia.produto || 'N/A'}</p>
                    </div>
                    
                    <!-- Número de Série (apenas para garantias registradas) -->
                    ${!isPendente ? `
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-500 font-medium">Número de Série</span>
                        </div>
                        <p class="text-gray-900 font-semibold">${garantia.numero_serie || 'N/A'}</p>
                    </div>
                    ` : ''}
                </div>
                
                <!-- Datas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg">
                        <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Data de Abertura</p>
                            <p class="text-lg font-semibold text-gray-900">${formatarData(garantia.created_at)}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg">
                        <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Última Atualização</p>
                            <p class="text-lg font-semibold text-gray-900">${formatarData(garantia.updated_at || garantia.created_at)}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Descrição do Defeito -->
                ${garantia.descricao_defeito ? `
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-yellow-800 mb-1">Descrição do Defeito</h4>
                            <p class="text-yellow-700">${garantia.descricao_defeito}</p>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                <!-- Observações -->
                ${garantia.observacoes ? `
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-blue-800 mb-1">Observações</h4>
                            <p class="text-blue-700">${garantia.observacoes}</p>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>ID: #${garantia.id}</span>
                    <span>Ticket/OS: ${garantia.numero_ticket_os || 'N/A'}</span>
                </div>
            </div>
        </div>
    `;
    
    container.classList.remove('hidden');
}

function formatarData(dataStr) {
    if (!dataStr) return 'N/A';
    const data = new Date(dataStr);
    return data.toLocaleDateString('pt-BR') + ' às ' + data.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
}
</script>
