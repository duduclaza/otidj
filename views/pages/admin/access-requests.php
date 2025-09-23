<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Solicitações de Acesso</h1>
            <p class="text-gray-600 mt-1">Gerencie as solicitações pendentes de novos usuários</p>
        </div>
        <div class="flex items-center space-x-2">
            <button id="btnRefresh" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Atualizar
            </button>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pendentes</p>
                    <p id="totalPendentes" class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Aprovadas Hoje</p>
                    <p id="aprovadasHoje" class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rejeitadas Hoje</p>
                    <p id="rejeitadasHoje" class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Solicitações -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Solicitações Pendentes</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Setor/Filial</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Justificativa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaSolicitacoes" class="bg-white divide-y divide-gray-200">
                    <!-- Dados carregados via JavaScript -->
                </tbody>
            </table>
        </div>
        
        <div id="emptyState" class="hidden text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma solicitação pendente</h3>
            <p class="mt-1 text-sm text-gray-500">Todas as solicitações foram processadas.</p>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" class="text-center py-8 hidden">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Carregando...</p>
    </div>
</div>

<!-- Modal de Aprovação -->
<div id="modalAprovacao" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Aprovar Solicitação</h3>
            </div>
            
            <form id="formAprovacao" class="p-6">
                <input type="hidden" id="aprovarRequestId" name="request_id">
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Selecione o perfil que será atribuído ao usuário:
                    </p>
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">Perfil *</label>
                    <select id="profileSelect" name="profile_id" required class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">Selecione um perfil</option>
                        <!-- Perfis carregados via JavaScript -->
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" id="btnCancelarAprovacao" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                        Aprovar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Rejeição -->
<div id="modalRejeicao" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Rejeitar Solicitação</h3>
            </div>
            
            <form id="formRejeicao" class="p-6">
                <input type="hidden" id="rejeitarRequestId" name="request_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Motivo da Rejeição</label>
                    <textarea id="rejectionReason" name="rejection_reason" rows="4" 
                              class="w-full border border-gray-300 rounded-md px-3 py-2"
                              placeholder="Explique o motivo da rejeição (opcional)"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" id="btnCancelarRejeicao" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                        Rejeitar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variáveis globais
let solicitacoes = [];
let perfis = [];

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarSolicitacoes();
    carregarPerfis();
    configurarEventos();
});

// Configurar eventos
function configurarEventos() {
    document.getElementById('btnRefresh').addEventListener('click', carregarSolicitacoes);
    document.getElementById('btnCancelarAprovacao').addEventListener('click', () => fecharModal('modalAprovacao'));
    document.getElementById('btnCancelarRejeicao').addEventListener('click', () => fecharModal('modalRejeicao'));
    document.getElementById('formAprovacao').addEventListener('submit', aprovarSolicitacao);
    document.getElementById('formRejeicao').addEventListener('submit', rejeitarSolicitacao);
}

// Carregar solicitações
async function carregarSolicitacoes() {
    try {
        document.getElementById('loading').classList.remove('hidden');
        const response = await fetch('/admin/access-requests/list');
        const result = await response.json();
        
        if (result.success) {
            solicitacoes = result.data;
            renderizarTabela(solicitacoes);
            atualizarEstatisticas();
        } else {
            alert('Erro ao carregar solicitações: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar solicitações');
    } finally {
        document.getElementById('loading').classList.add('hidden');
    }
}

// Carregar perfis
async function carregarPerfis() {
    try {
        const response = await fetch('/admin/access-requests/profiles');
        const result = await response.json();
        
        if (result.success) {
            perfis = result.data;
            preencherSelectPerfis();
        }
    } catch (error) {
        console.error('Erro ao carregar perfis:', error);
    }
}

// Preencher select de perfis
function preencherSelectPerfis() {
    const select = document.getElementById('profileSelect');
    
    // Limpar opções existentes (exceto a primeira)
    while (select.children.length > 1) {
        select.removeChild(select.lastChild);
    }
    
    perfis.forEach(perfil => {
        const option = document.createElement('option');
        option.value = perfil.id;
        option.textContent = `${perfil.name} - ${perfil.description}`;
        select.appendChild(option);
    });
}

// Renderizar tabela
function renderizarTabela(dados) {
    const tbody = document.getElementById('tabelaSolicitacoes');
    const emptyState = document.getElementById('emptyState');
    
    tbody.innerHTML = '';
    
    if (dados.length === 0) {
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    dados.forEach(solicitacao => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${solicitacao.name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${solicitacao.email}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                    ${solicitacao.setor || 'N/A'} / ${solicitacao.filial || 'N/A'}
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-900 max-w-xs truncate" title="${solicitacao.justificativa}">
                    ${solicitacao.justificativa}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${formatarData(solicitacao.created_at)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="abrirModalAprovacao(${solicitacao.id})" 
                        class="text-green-600 hover:text-green-900 mr-3">
                    Aprovar
                </button>
                <button onclick="abrirModalRejeicao(${solicitacao.id})" 
                        class="text-red-600 hover:text-red-900">
                    Rejeitar
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

// Atualizar estatísticas
function atualizarEstatisticas() {
    document.getElementById('totalPendentes').textContent = solicitacoes.length;
    // As outras estatísticas podem ser implementadas com dados do backend se necessário
}

// Abrir modal de aprovação
function abrirModalAprovacao(requestId) {
    document.getElementById('aprovarRequestId').value = requestId;
    document.getElementById('modalAprovacao').classList.remove('hidden');
}

// Abrir modal de rejeição
function abrirModalRejeicao(requestId) {
    document.getElementById('rejeitarRequestId').value = requestId;
    document.getElementById('modalRejeicao').classList.remove('hidden');
}

// Fechar modal
function fecharModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Aprovar solicitação
async function aprovarSolicitacao(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('/admin/access-requests/approve', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            fecharModal('modalAprovacao');
            carregarSolicitacoes();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao aprovar solicitação');
    }
}

// Rejeitar solicitação
async function rejeitarSolicitacao(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('/admin/access-requests/reject', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            fecharModal('modalRejeicao');
            carregarSolicitacoes();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao rejeitar solicitação');
    }
}

// Formatar data
function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>
