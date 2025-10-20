<?php // Renderizada via views/layouts/main.php; não incluir header/footer aqui ?>

<style>
/* Layout geral: faixa horizontal com scroll */
.kanban-wrap { overflow-x: auto; }
.kanban-row { display: flex; gap: 16px; padding-bottom: 8px; min-width: max-content; }

/* Scrollbar superior sincronizada */
.kanban-scroll-top { height: 14px; overflow-x: auto; overflow-y: hidden; margin-bottom: 8px; }
.kanban-scroll-top::-webkit-scrollbar { height: 12px; }
.kanban-scroll-top::-webkit-scrollbar-thumb { background: rgba(100,116,139,0.5); border-radius: 6px; }
.kanban-scroll-top::-webkit-scrollbar-track { background: rgba(148,163,184,0.2); }

/* Colunas com contraste mais forte */
.kanban-column {
    min-height: 520px;
    background: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 100%);
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08), inset 0 0 0 1px rgba(15,23,42,0.05);
    width: 360px;
}

.kanban-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 12px;
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
    cursor: pointer;
    transition: all 0.2s;
    border-left: 4px solid;
}

.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
}

/* Cores por status */
.status-aguardando_recebimento { border-left-color: #ca8a04; background: #fef9c3; }
.status-recebido { border-left-color: #1d4ed8; background: #dbeafe; }
.status-em_analise { border-left-color: #c2410c; background: #ffedd5; }
.status-em_homologacao { border-left-color: #7c3aed; background: #ede9fe; }
.status-aprovado { border-left-color: #16a34a; background: #dcfce7; }
.status-reprovado { border-left-color: #dc2626; background: #fee2e2; }

.badge-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.badge-aguardando_recebimento { background: #eab308; }
.badge-recebido { background: #3b82f6; }
.badge-em_analise { background: #f97316; }
.badge-em_homologacao { background: #a855f7; }
.badge-aprovado { background: #22c55e; }
.badge-reprovado { background: #ef4444; }
</style>

<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Homologações</h1>
            <p class="text-slate-600 mt-1">Gestão de homologações de produtos</p>
        </div>
        <?php if ($canCreate): ?>
        <button onclick="openModalNovaHomologacao()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
            <span>➕</span>
            <span>Nova Homologação</span>
        </button>
        <?php endif; ?>
    </div>

    <!-- Scrollbar superior -->
    <div class="kanban-scroll-top" id="kanbanScrollTop"><div id="kanbanScrollTopInner" style="height:1px"></div></div>

    <!-- Kanban Board (horizontal scroll) -->
    <div class="kanban-wrap" id="kanbanWrap">
      <div class="kanban-row">
        <!-- Coluna: Aguardando Recebimento -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">📦</span>
                <h3 class="font-bold text-slate-800">Aguardando Recebimento</h3>
                </div>
                <span class="bg-yellow-600/15 text-yellow-800 text-xs px-2 py-1 rounded-full border border-yellow-600/20 font-semibold"><?= count($homologacoes['aguardando_recebimento']) ?></span>
            </div>
            <div class="kanban-column">
                <?php foreach ($homologacoes['aguardando_recebimento'] as $h): ?>
                    <div class="kanban-card status-aguardando_recebimento relative" onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">
                            🗑️
                        </button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">👤 <?= e(substr($h['responsaveis_nomes'] ?? 'N/A', 0, 20)) ?></span>
                            <?php if ($h['total_anexos'] > 0): ?>
                            <span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Recebido -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">✅</span>
                <h3 class="font-bold text-slate-800">Recebido</h3>
                </div>
                <span class="bg-blue-600/15 text-blue-800 text-xs px-2 py-1 rounded-full border border-blue-600/20 font-semibold"><?= count($homologacoes['recebido']) ?></span>
            </div>
            <div class="kanban-column">
                <?php foreach ($homologacoes['recebido'] as $h): ?>
                    <div class="kanban-card status-recebido relative" onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">👤 <?= e(substr($h['responsaveis_nomes'] ?? 'N/A', 0, 20)) ?></span>
                            <?php if ($h['total_anexos'] > 0): ?>
                            <span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Em Análise -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">🔍</span>
                <h3 class="font-bold text-slate-800">Em Análise</h3>
                </div>
                <span class="bg-orange-600/15 text-orange-800 text-xs px-2 py-1 rounded-full border border-orange-600/20 font-semibold"><?= count($homologacoes['em_analise']) ?></span>
            </div>
            <div class="kanban-column">
                <?php foreach ($homologacoes['em_analise'] as $h): ?>
                    <div class="kanban-card status-em_analise relative" onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">👤 <?= e(substr($h['responsaveis_nomes'] ?? 'N/A', 0, 20)) ?></span>
                            <?php if ($h['total_anexos'] > 0): ?>
                            <span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Em Homologação -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">🧪</span>
                <h3 class="font-bold text-slate-800">Em Homologação</h3>
                </div>
                <span class="bg-purple-600/15 text-purple-800 text-xs px-2 py-1 rounded-full border border-purple-600/20 font-semibold"><?= count($homologacoes['em_homologacao']) ?></span>
            </div>
            <div class="kanban-column">
                <?php foreach ($homologacoes['em_homologacao'] as $h): ?>
                    <div class="kanban-card status-em_homologacao relative" onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <?php if ($h['local_homologacao']): ?>
                        <div class="text-xs text-slate-500 mb-1">📍 <?= e($h['local_homologacao']) ?></div>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">👤 <?= e(substr($h['responsaveis_nomes'] ?? 'N/A', 0, 20)) ?></span>
                            <?php if ($h['total_anexos'] > 0): ?>
                            <span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Aprovado -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">✔️</span>
                <h3 class="font-bold text-slate-800">Aprovado</h3>
                </div>
                <span class="bg-green-600/15 text-green-800 text-xs px-2 py-1 rounded-full border border-green-600/20 font-semibold"><?= count($homologacoes['aprovado']) ?></span>
            </div>
            <div class="kanban-column">
                <?php foreach ($homologacoes['aprovado'] as $h): ?>
                    <div class="kanban-card status-aprovado relative" onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">👤 <?= e(substr($h['responsaveis_nomes'] ?? 'N/A', 0, 20)) ?></span>
                            <?php if ($h['total_anexos'] > 0): ?>
                            <span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Reprovado -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">❌</span>
                <h3 class="font-bold text-slate-800">Reprovado</h3>
                </div>
                <span class="bg-red-600/15 text-red-800 text-xs px-2 py-1 rounded-full border border-red-600/20 font-semibold"><?= count($homologacoes['reprovado']) ?></span>
            </div>
            <div class="kanban-column">
                <?php foreach ($homologacoes['reprovado'] as $h): ?>
                    <div class="kanban-card status-reprovado relative" onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">👤 <?= e(substr($h['responsaveis_nomes'] ?? 'N/A', 0, 20)) ?></span>
                            <?php if ($h['total_anexos'] > 0): ?>
                            <span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
      </div>
    </div>
</div>

<!-- Modal: Nova Homologação -->
<div id="modalNovaHomologacao" class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target === this) closeModalNovaHomologacao()">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-slate-800">📋 Nova Homologação</h2>
            <button onclick="closeModalNovaHomologacao()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>

        <form id="formNovaHomologacao" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cód. Referência <span class="text-red-500">*</span></label>
                <input type="text" name="cod_referencia" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                <textarea name="descricao" required rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Avisar Logística da chegada?</label>
                <div class="flex gap-4">
                    <label class="flex items-center"><input type="radio" name="avisar_logistica" value="1" class="mr-2"><span>Sim</span></label>
                    <label class="flex items-center"><input type="radio" name="avisar_logistica" value="0" checked class="mr-2"><span>Não</span></label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Responsável(is) <span class="text-red-500">*</span></label>
                <select name="responsaveis[]" multiple required size="6" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['id'] ?>"><?= e($usuario['name']) ?> (<?= e($usuario['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-slate-500 mt-1">Segure Ctrl para selecionar múltiplos</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Observação</label>
                <textarea name="observacao" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg"></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Registrar Nova Homologação</button>
                <button type="button" onclick="closeModalNovaHomologacao()" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Detalhes -->
<div id="modalCardDetails" class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target === this) closeCardDetails()">
    <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[85vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-slate-800">Detalhes da Homologação</h2>
            <button onclick="closeCardDetails()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>
        <div id="cardDetailsContent"><p class="text-center text-slate-500">Carregando...</p></div>
    </div>
</div>

<script>
// Variáveis globais
const usuarios = <?= json_encode($usuarios) ?>;

// Util: mover modais para o container global para sobrepor sidebar e layout
document.addEventListener('DOMContentLoaded', () => {
    const globalContainer = document.getElementById('global-modals-container');
    if (globalContainer) {
        const nova = document.getElementById('modalNovaHomologacao');
        const detalhes = document.getElementById('modalCardDetails');
        if (nova && nova.parentElement !== globalContainer) globalContainer.appendChild(nova);
        if (detalhes && detalhes.parentElement !== globalContainer) globalContainer.appendChild(detalhes);
    }

    // Sincronizar scrollbar superior com o container principal
    const wrap = document.getElementById('kanbanWrap');
    const topBar = document.getElementById('kanbanScrollTop');
    const topInner = document.getElementById('kanbanScrollTopInner');
    if (wrap && topBar && topInner) {
        const syncWidths = () => { topInner.style.width = wrap.scrollWidth + 'px'; };
        syncWidths();
        window.addEventListener('resize', syncWidths);
        // Sync scroll positions
        let syncing = false;
        wrap.addEventListener('scroll', () => {
            if (syncing) return; syncing = true; topBar.scrollLeft = wrap.scrollLeft; syncing = false;
        });
        topBar.addEventListener('scroll', () => {
            if (syncing) return; syncing = true; wrap.scrollLeft = topBar.scrollLeft; syncing = false;
        });
    }
});

// Helpers de scroll-lock
function lockBodyScroll() { document.documentElement.style.overflow = 'hidden'; document.body.style.overflow = 'hidden'; }
function unlockBodyScroll() { document.documentElement.style.overflow = ''; document.body.style.overflow = ''; }

// Modal Nova Homologação
function openModalNovaHomologacao() {
    document.getElementById('modalNovaHomologacao').classList.remove('hidden');
    lockBodyScroll();
}

function closeModalNovaHomologacao() {
    document.getElementById('modalNovaHomologacao').classList.add('hidden');
    unlockBodyScroll();
}

// Submit
document.getElementById('formNovaHomologacao').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('/homologacoes/store', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        alert('❌ Erro ao criar homologação');
    }
});

// Detalhes
async function openCardDetails(id) {
    document.getElementById('modalCardDetails').classList.remove('hidden');
    lockBodyScroll();
    document.getElementById('cardDetailsContent').innerHTML = '<p class="text-center">Carregando...</p>';
    
    try {
        const response = await fetch(`/homologacoes/${id}/details`);
        const result = await response.json();
        
        if (result.success) {
            renderDetails(result);
        }
    } catch (error) {
        document.getElementById('cardDetailsContent').innerHTML = '<p class="text-center text-red-500">Erro</p>';
    }
}

function closeCardDetails() {
    document.getElementById('modalCardDetails').classList.add('hidden');
    unlockBodyScroll();
}

function renderDetails(data) {
    const h = data.homologacao;
    const statusLabels = {
        'aguardando_recebimento': 'Aguardando Recebimento',
        'recebido': 'Recebido',
        'em_analise': 'Em Análise',
        'em_homologacao': 'Em Homologação',
        'aprovado': 'Aprovado',
        'reprovado': 'Reprovado'
    };
    
    let html = `
        <div class="space-y-4">
            <div class="bg-slate-50 p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-3">
                    <div><span class="text-sm text-slate-600">Código:</span><p class="font-bold">${h.cod_referencia}</p></div>
                    <div><span class="text-sm text-slate-600">Status:</span><span class="badge-status badge-${h.status}">${statusLabels[h.status]}</span></div>
                    <div class="col-span-2"><span class="text-sm text-slate-600">Descrição:</span><p class="mt-1">${h.descricao}</p></div>
                </div>
            </div>
            
            <div>
                <h3 class="font-bold mb-2">👤 Responsáveis</h3>
                ${data.responsaveis.map(r => `<div class="bg-blue-50 px-3 py-2 rounded mb-2">${r.name} - ${r.email}</div>`).join('')}
            </div>
            
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="font-bold mb-3">🔄 Atualizar Status</h3>
                <form id="formUpdateStatus" class="space-y-3">
                    <input type="hidden" name="homologacao_id" value="${h.id}">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Novo Status</label>
                            <select name="status" required class="w-full px-3 py-2 border rounded-lg">
                                <option value="recebido">Recebido</option>
                                <option value="em_analise">Em Análise</option>
                                <option value="em_homologacao">Em Homologação</option>
                                <option value="aprovado">Aprovado</option>
                                <option value="reprovado">Reprovado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Local</label>
                            <input type="text" name="local_homologacao" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Data Início</label>
                            <input type="date" name="data_inicio_homologacao" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Alerta</label>
                            <input type="date" name="alerta_finalizacao" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                    </div>
                    <textarea name="observacao" rows="2" placeholder="Observação" class="w-full px-3 py-2 border rounded-lg"></textarea>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Atualizar</button>
                </form>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-bold mb-3">📎 Anexar Evidências</h3>
                <form id="formUploadAnexo" class="space-y-3">
                    <input type="hidden" name="homologacao_id" value="${h.id}">
                    <input type="file" name="anexo" required class="w-full px-3 py-2 border rounded-lg">
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Enviar</button>
                </form>
                ${data.anexos.length > 0 ? `
                    <div class="mt-3 space-y-2">
                        ${data.anexos.map(a => `
                            <div class="flex justify-between bg-white px-3 py-2 rounded">
                                <span class="text-sm">${a.nome_arquivo}</span>
                                <a href="/homologacoes/anexo/${a.id}" target="_blank" class="text-blue-600 text-sm">Download</a>
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('cardDetailsContent').innerHTML = html;
    
    // Event listeners
    document.getElementById('formUpdateStatus').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await fetch('/homologacoes/update-status', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) { alert('✅ ' + result.message); location.reload(); } 
            else { alert('❌ ' + result.message); }
        } catch (error) { alert('❌ Erro'); }
    });
    
    document.getElementById('formUploadAnexo').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await fetch('/homologacoes/upload-anexo', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) { alert('✅ ' + result.message); openCardDetails(h.id); } 
            else { alert('❌ ' + result.message); }
        } catch (error) { alert('❌ Erro'); }
    });
}

// Excluir homologação (global)
async function deleteHomologacao(id) {
    try {
        if (!confirm('Tem certeza que deseja excluir esta homologação?')) return;
        const fd = new FormData();
        fd.append('id', id);
        const res = await fetch('/homologacoes/delete', { method: 'POST', body: fd });
        const result = await res.json();
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Erro ao excluir'));
        }
    } catch (e) {
        alert('❌ Erro ao excluir');
    }
}
</script>

<?php // Fim da view de Homologações ?>
