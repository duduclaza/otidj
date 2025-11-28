<?php
// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
$isAdmin = in_array(strtolower($_SESSION['user_role'] ?? ''), ['admin', 'super_admin', 'superadmin', 'administrador', 'master']);
?>

<section class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Requisições Pendentes</h1>
            <p class="text-gray-600 mt-1">Requisições aguardando processamento para registro de garantia</p>
        </div>
        <div class="flex space-x-3">
            <a href="/garantias" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Voltar</span>
            </a>
            <button onclick="carregarRequisicoes()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Atualizar</span>
            </button>
        </div>
    </div>

    <!-- Card de Total -->
    <div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-white/20 rounded-xl p-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-white/80 font-medium">Total de Requisições Pendentes</p>
                    <p id="totalPendentes" class="text-4xl font-bold text-white">0</p>
                </div>
            </div>
            <div class="text-white/60 text-6xl">📋</div>
        </div>
    </div>

    <!-- Lista de Requisições -->
    <div id="listaRequisicoes" class="space-y-4">
        <!-- Loading -->
        <div id="loadingRequisicoes" class="bg-white rounded-xl shadow-lg p-12 text-center">
            <svg class="w-12 h-12 text-blue-500 mx-auto mb-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-600 font-medium">Carregando requisições...</p>
        </div>
    </div>
</section>

<!-- Modal de Detalhes da Requisição -->
<div id="modalDetalhes" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" style="z-index: 999999;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 flex items-center justify-between sticky top-0">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Detalhes da Requisição
            </h2>
            <button onclick="fecharModalDetalhes()" class="text-white/80 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Ticket</p>
                    <p id="detTicket" class="text-lg font-bold text-blue-600 font-mono">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Data de Abertura</p>
                    <p id="detData" class="text-lg font-semibold text-gray-800">-</p>
                </div>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-sm text-gray-500">Requisitante</p>
                <p id="detRequisitante" class="text-lg font-semibold text-gray-800">-</p>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-sm text-gray-500">Produto</p>
                <p id="detProduto" class="text-lg font-semibold text-gray-800">-</p>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-sm text-gray-500">Descrição do Defeito</p>
                <p id="detDescricao" class="text-gray-700 bg-gray-50 p-3 rounded-lg">-</p>
            </div>
            
            <div id="detImagensContainer" class="border-t pt-4 hidden">
                <p class="text-sm text-gray-500 mb-3">Imagens Anexadas</p>
                <div id="detImagens" class="grid grid-cols-2 md:grid-cols-3 gap-3"></div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 flex gap-3">
            <button onclick="puxarParaRegistro()" class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-3 px-6 rounded-xl flex items-center justify-center gap-2 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Puxar para Registro
            </button>
            <button onclick="fecharModalDetalhes()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-xl transition-colors">
                Fechar
            </button>
        </div>
    </div>
</div>

<!-- Modal de Imagem Ampliada -->
<div id="modalImagem" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center p-4" style="z-index: 9999999;">
    <button onclick="fecharModalImagem()" class="absolute top-4 right-4 text-white/80 hover:text-white">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
    <img id="imagemAmpliada" src="" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
</div>

<script>
let requisicaoAtual = null;
const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Mover modais para o body
    const modais = ['modalDetalhes', 'modalImagem'];
    modais.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
    
    carregarRequisicoes();
});

async function carregarRequisicoes() {
    const lista = document.getElementById('listaRequisicoes');
    lista.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <svg class="w-12 h-12 text-blue-500 mx-auto mb-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-600 font-medium">Carregando requisições...</p>
        </div>
    `;
    
    try {
        const response = await fetch('/garantias/requisicoes/list');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            document.getElementById('totalPendentes').textContent = result.data.length;
            
            lista.innerHTML = result.data.map(req => `
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-bold font-mono">${req.ticket}</span>
                                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-sm font-medium">${req.status}</span>
                                    ${req.imagens ? '<span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs">📷 Com imagens</span>' : ''}
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 mb-1">${escapeHtml(req.produto)}</h3>
                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">${escapeHtml(req.descricao_defeito)}</p>
                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        ${escapeHtml(req.nome_requisitante)}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        ${formatarData(req.created_at)}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 ml-4">
                                <button onclick="verDetalhes(${req.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Ver Detalhes
                                </button>
                                <button onclick="puxarParaRegistroDireto(${req.id})" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Puxar para Registro
                                </button>
                                ${isAdmin ? `
                                <button onclick="excluirRequisicao(${req.id}, '${req.ticket}')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Excluir
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            
        } else {
            document.getElementById('totalPendentes').textContent = '0';
            lista.innerHTML = `
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-600 font-medium text-lg">Nenhuma requisição pendente</p>
                    <p class="text-sm text-gray-500 mt-2">Todas as requisições foram processadas!</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
        lista.innerHTML = `
            <div class="bg-red-50 rounded-xl p-6 text-center">
                <p class="text-red-600 font-medium">Erro ao carregar requisições</p>
                <button onclick="carregarRequisicoes()" class="mt-3 text-blue-600 hover:text-blue-700 font-medium">Tentar novamente</button>
            </div>
        `;
    }
}

async function verDetalhes(id) {
    try {
        const response = await fetch(`/garantias/requisicoes/${id}`);
        const result = await response.json();
        
        if (result.success) {
            requisicaoAtual = result.data;
            
            document.getElementById('detTicket').textContent = result.data.ticket;
            document.getElementById('detData').textContent = formatarData(result.data.created_at);
            document.getElementById('detRequisitante').textContent = result.data.nome_requisitante;
            document.getElementById('detProduto').textContent = result.data.produto;
            document.getElementById('detDescricao').textContent = result.data.descricao_defeito;
            
            // Imagens
            const imgContainer = document.getElementById('detImagensContainer');
            const imgDiv = document.getElementById('detImagens');
            
            if (result.data.imagens) {
                const imagens = JSON.parse(result.data.imagens);
                if (imagens.length > 0) {
                    imgContainer.classList.remove('hidden');
                    imgDiv.innerHTML = imagens.map((img, i) => `
                        <div class="relative cursor-pointer" onclick="ampliarImagem('data:${img.tipo};base64,${img.conteudo}')">
                            <img src="data:${img.tipo};base64,${img.conteudo}" class="w-full h-32 object-cover rounded-lg border hover:opacity-80 transition-opacity">
                            <p class="text-xs text-gray-500 truncate mt-1">${img.nome}</p>
                        </div>
                    `).join('');
                } else {
                    imgContainer.classList.add('hidden');
                }
            } else {
                imgContainer.classList.add('hidden');
            }
            
            document.getElementById('modalDetalhes').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar detalhes');
    }
}

function fecharModalDetalhes() {
    document.getElementById('modalDetalhes').classList.add('hidden');
    requisicaoAtual = null;
}

function ampliarImagem(src) {
    document.getElementById('imagemAmpliada').src = src;
    document.getElementById('modalImagem').classList.remove('hidden');
}

function fecharModalImagem() {
    document.getElementById('modalImagem').classList.add('hidden');
}

function puxarParaRegistro() {
    if (requisicaoAtual) {
        puxarParaRegistroDireto(requisicaoAtual.id);
    }
}

function puxarParaRegistroDireto(id) {
    // Redirecionar para a página de registro de garantias com o ID da requisição
    window.location.href = `/garantias?requisicao_id=${id}`;
}

async function excluirRequisicao(id, ticket) {
    if (!confirm(`Tem certeza que deseja excluir a requisição ${ticket}?\n\nEsta ação não pode ser desfeita.`)) {
        return;
    }
    
    try {
        const response = await fetch(`/garantias/requisicoes/${id}/excluir`, {
            method: 'POST'
        });
        const result = await response.json();
        
        if (result.success) {
            alert('Requisição excluída com sucesso!');
            carregarRequisicoes();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir requisição');
    }
}

function formatarData(dataStr) {
    const data = new Date(dataStr);
    return data.toLocaleDateString('pt-BR') + ' ' + data.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
