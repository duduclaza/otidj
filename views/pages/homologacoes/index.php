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
        <div class="flex gap-3">
            <?php if ($canCreate): ?>
            <button onclick="openModalNovaHomologacao()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <span>➕</span>
                <span>Nova Homologação</span>
            </button>
            <?php endif; ?>
            
            <?php if ($isAdmin || $isSuperAdmin): ?>
            <button onclick="openModalChecklists()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <span>📋</span>
                <span>Cadastros de Checklist</span>
            </button>
            <?php endif; ?>
        </div>
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
                        <?php if (!empty($h['departamento_nome'])): ?>
                        <div class="text-xs text-purple-700 font-medium mb-2 flex items-center gap-1">
                            <span>📍</span><span><?= e($h['departamento_nome']) ?></span>
                        </div>
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
                        <?php if (!empty($h['departamento_nome'])): ?>
                        <div class="text-xs text-purple-700 font-medium mb-2 flex items-center gap-1">
                            <span>📍</span><span><?= e($h['departamento_nome']) ?></span>
                        </div>
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
                        <?php if (!empty($h['departamento_nome'])): ?>
                        <div class="text-xs text-purple-700 font-medium mb-2 flex items-center gap-1">
                            <span>📍</span><span><?= e($h['departamento_nome']) ?></span>
                        </div>
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
                        <?php if (!empty($h['departamento_nome'])): ?>
                        <div class="text-xs text-purple-700 font-medium mb-2 flex items-center gap-1">
                            <span>📍</span><span><?= e($h['departamento_nome']) ?></span>
                        </div>
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
                        <?php if (!empty($h['departamento_nome'])): ?>
                        <div class="text-xs text-purple-700 font-medium mb-2 flex items-center gap-1">
                            <span>📍</span><span><?= e($h['departamento_nome']) ?></span>
                        </div>
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
                <label class="block text-sm font-medium text-slate-700 mb-1">📍 Localização (Departamento) <span class="text-red-500">*</span></label>
                <select name="departamento_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Selecione o departamento --</option>
                    <?php foreach ($departamentos as $dept): ?>
                    <option value="<?= $dept['id'] ?>"><?= e($dept['name']) ?></option>
                    <?php endforeach; ?>
                </select>
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

<!-- Modal: Cadastros de Checklist -->
<div id="modalChecklists" class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target === this) closeModalChecklists()">
    <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800">📋 Gerenciar Checklists</h2>
            <button onclick="closeModalChecklists()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 mb-6">
            <button onclick="switchChecklistTab('novo')" id="tabNovoChecklist" class="px-4 py-2 border-b-2 border-blue-600 text-blue-600 font-medium">
                ➕ Novo Checklist
            </button>
            <button onclick="switchChecklistTab('lista')" id="tabListaChecklists" class="px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-blue-600">
                📋 Lista de Checklists
            </button>
        </div>

        <!-- Tab: Novo Checklist -->
        <div id="checklistTabNovo">
            <form id="formNovoChecklist" onsubmit="salvarChecklist(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Título do Checklist *</label>
                    <input type="text" id="checklistTitulo" required 
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Ex: Checklist de Homologação de Toners">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Descrição (opcional)</label>
                    <textarea id="checklistDescricao" rows="2" 
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Descrição do checklist..."></textarea>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-slate-700">Itens do Checklist *</label>
                        <button type="button" onclick="adicionarItemChecklist()" 
                                class="text-sm px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200">
                            + Adicionar Item
                        </button>
                    </div>
                    
                    <div id="checklistItens" class="space-y-2">
                        <!-- Itens adicionados dinamicamente -->
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        💾 Salvar Checklist
                    </button>
                    <button type="button" onclick="cancelarNovoChecklist()" 
                            class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab: Lista de Checklists -->
        <div id="checklistTabLista" class="hidden">
            <div id="listaChecklists" class="space-y-3">
                <!-- Carregado via JavaScript -->
            </div>
        </div>
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
            
            // Se status for "em_homologacao", carregar checklists no dropdown
            if (result.homologacao.status === 'em_homologacao') {
                setTimeout(() => {
                    carregarChecklistsNoCard(id);
                }, 100);
            }
        }
    } catch (error) {
        document.getElementById('cardDetailsContent').innerHTML = '<p class="text-center text-red-500">Erro</p>';
    }
}

function closeCardDetails() {
    document.getElementById('modalCardDetails').classList.add('hidden');
    unlockBodyScroll();
}

// Função para retornar apenas próximos status (sem permitir voltar)
function getProximosStatus(statusAtual) {
    const fluxoStatus = {
        'aguardando_recebimento': [
            { value: 'recebido', label: 'Recebido' },
            { value: 'reprovado', label: 'Reprovado' }
        ],
        'recebido': [
            { value: 'em_analise', label: 'Em Análise' },
            { value: 'reprovado', label: 'Reprovado' }
        ],
        'em_analise': [
            { value: 'em_homologacao', label: 'Em Homologação' },
            { value: 'aprovado', label: 'Aprovado' },
            { value: 'reprovado', label: 'Reprovado' }
        ],
        'em_homologacao': [
            { value: 'aprovado', label: 'Aprovado' },
            { value: 'reprovado', label: 'Reprovado' }
        ],
        'aprovado': [],  // Status final
        'reprovado': []  // Status final
    };
    
    const proximos = fluxoStatus[statusAtual] || [];
    
    if (proximos.length === 0) {
        return '<option value="">✅ Status Final - Não pode ser alterado</option>';
    }
    
    return proximos.map(s => 
        `<option value="${s.value}">${s.label}</option>`
    ).join('');
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
            
            ${h.status === 'em_homologacao' || h.checklist_id ? `
            <div class="bg-purple-50 p-4 rounded-lg border-2 border-purple-200">
                <h3 class="font-bold mb-3 text-purple-800">📋 Checklist de Homologação</h3>
                
                ${h.status === 'em_homologacao' ? `
                    <!-- MODO EDIÇÃO: Pode selecionar e preencher -->
                    <div id="checklistSection${h.id}">
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-2">Selecionar Checklist</label>
                            <select id="selectChecklist${h.id}" onchange="carregarItensChecklist(${h.id}, this.value)" 
                                    class="w-full px-3 py-2 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">-- Escolha um checklist --</option>
                            </select>
                        </div>
                        <div id="itensChecklist${h.id}"></div>
                    </div>
                ` : h.checklist_id ? `
                    <!-- MODO VISUALIZAÇÃO: Apenas consulta do que foi preenchido -->
                    <div class="bg-blue-50 p-3 rounded-lg mb-3">
                        <span class="text-sm font-medium">✅ Checklist já preenchido</span>
                    </div>
                    <div id="checklistRespostas${h.id}"></div>
                ` : ''}
            </div>
            ` : ''}
            
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-bold mb-3">📎 Anexar Evidências</h3>
                <form id="formUploadAnexo" class="space-y-3">
                    <input type="hidden" name="homologacao_id" value="${h.id}">
                    <input type="file" name="anexo" required class="w-full px-3 py-2 border rounded-lg">
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Enviar Anexo</button>
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
            
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="font-bold mb-3">🔄 Atualizar Status</h3>
                <div class="mb-3 p-3 bg-blue-100 rounded-lg">
                    <span class="text-sm font-medium">Status Atual:</span>
                    <span class="ml-2 badge-status badge-${h.status}">${statusLabels[h.status]}</span>
                </div>
                <form id="formUpdateStatus" class="space-y-3">
                    <input type="hidden" name="homologacao_id" value="${h.id}">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Avançar para Status</label>
                            <select name="status" required class="w-full px-3 py-2 border rounded-lg">
                                ${getProximosStatus(h.status)}
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
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Atualizar Status</button>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('cardDetailsContent').innerHTML = html;
    
    // Se tem checklist_id e não está em homologação, carregar respostas
    if (h.checklist_id && h.status !== 'em_homologacao') {
        carregarRespostasChecklist(h.id, h.checklist_id);
    }
    
    // Se está em homologação, carregar dropdown de checklists
    if (h.status === 'em_homologacao') {
        carregarChecklistsDropdown(h.id);
    }
    
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

// ===== FUNÇÕES DE CHECKLIST =====

let checklistItemCounter = 0;

// Abrir modal de checklists
function openModalChecklists() {
    document.getElementById('modalChecklists').classList.remove('hidden');
    switchChecklistTab('novo'); // Mudar para abrir em "novo" ao invés de "lista"
    lockBodyScroll();
}

// Fechar modal de checklists
function closeModalChecklists() {
    document.getElementById('modalChecklists').classList.add('hidden');
    unlockBodyScroll();
}

// Trocar entre abas
function switchChecklistTab(tab) {
    const tabLista = document.getElementById('tabListaChecklists');
    const tabNovo = document.getElementById('tabNovoChecklist');
    const contentLista = document.getElementById('checklistTabLista');
    const contentNovo = document.getElementById('checklistTabNovo');
    
    if (tab === 'lista') {
        tabLista.classList.add('border-blue-600', 'text-blue-600', 'font-medium');
        tabLista.classList.remove('border-transparent', 'text-gray-600');
        tabNovo.classList.remove('border-blue-600', 'text-blue-600', 'font-medium');
        tabNovo.classList.add('border-transparent', 'text-gray-600');
        contentLista.classList.remove('hidden');
        contentNovo.classList.add('hidden');
    } else {
        tabNovo.classList.add('border-blue-600', 'text-blue-600', 'font-medium');
        tabNovo.classList.remove('border-transparent', 'text-gray-600');
        tabLista.classList.remove('border-blue-600', 'text-blue-600', 'font-medium');
        tabLista.classList.add('border-transparent', 'text-gray-600');
        contentNovo.classList.remove('hidden');
        contentLista.classList.add('hidden');
        
        // Adicionar primeiro item automaticamente
        if (document.getElementById('checklistItens').children.length === 0) {
            adicionarItemChecklist();
        }
    }
}

// Adicionar item ao checklist
function adicionarItemChecklist() {
    const container = document.getElementById('checklistItens');
    const index = checklistItemCounter++;
    
    const itemHtml = `
        <div class="flex gap-2 items-start p-3 bg-gray-50 rounded-lg" id="checklistItem${index}">
            <div class="flex-1">
                <input type="text" 
                       id="checklistItemTitulo${index}" 
                       required
                       placeholder="Descrição do item (ex: Verificar qualidade de impressão)"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="w-32">
                <select id="checklistItemTipo${index}" 
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="checkbox">✓ Checkbox</option>
                    <option value="sim_nao">Sim/Não</option>
                    <option value="texto">Texto</option>
                    <option value="numero">Número</option>
                </select>
            </div>
            <button type="button" onclick="removerItemChecklist(${index})" 
                    class="px-2 py-2 text-red-600 hover:bg-red-50 rounded">
                🗑️
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
}

// Remover item do checklist
function removerItemChecklist(index) {
    const item = document.getElementById(`checklistItem${index}`);
    if (item) {
        item.remove();
    }
}

// Salvar checklist
async function salvarChecklist(event) {
    event.preventDefault();
    
    const titulo = document.getElementById('checklistTitulo').value;
    const descricao = document.getElementById('checklistDescricao').value;
    
    // Coletar itens
    const itens = [];
    const container = document.getElementById('checklistItens');
    const itemDivs = container.querySelectorAll('[id^="checklistItem"]');
    
    if (itemDivs.length === 0) {
        alert('⚠️ Adicione pelo menos um item ao checklist!');
        return;
    }
    
    itemDivs.forEach((div, ordem) => {
        const index = div.id.replace('checklistItem', '');
        const tituloItem = document.getElementById(`checklistItemTitulo${index}`)?.value;
        const tipo = document.getElementById(`checklistItemTipo${index}`)?.value;
        
        if (tituloItem) {
            itens.push({
                titulo: tituloItem,
                tipo_resposta: tipo,
                ordem: ordem
            });
        }
    });
    
    try {
        const response = await fetch('/homologacoes/checklists/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ titulo, descricao, itens })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Checklist criado com sucesso!');
            cancelarNovoChecklist();
            switchChecklistTab('lista');
            carregarChecklists();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao salvar checklist');
    }
}

// Cancelar novo checklist
function cancelarNovoChecklist() {
    document.getElementById('formNovoChecklist').reset();
    document.getElementById('checklistItens').innerHTML = '';
    checklistItemCounter = 0;
}

// Carregar lista de checklists
async function carregarChecklists() {
    try {
        const response = await fetch('/homologacoes/checklists/list');
        const result = await response.json();
        
        const container = document.getElementById('listaChecklists');
        
        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map(checklist => `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg text-gray-900">${checklist.titulo}</h3>
                            ${checklist.descricao ? `<p class="text-sm text-gray-600 mt-1">${checklist.descricao}</p>` : ''}
                            <p class="text-xs text-gray-500 mt-2">
                                ${checklist.total_itens} itens • 
                                Criado em ${new Date(checklist.criado_em).toLocaleDateString('pt-BR')}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="visualizarChecklist(${checklist.id})" 
                                    class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                👁️ Ver
                            </button>
                            <button onclick="excluirChecklist(${checklist.id})" 
                                    class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg mb-2">📋 Nenhum checklist cadastrado</p>
                    <p class="text-sm">Clique em "Novo Checklist" para criar o primeiro</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
        document.getElementById('listaChecklists').innerHTML = '<p class="text-center text-red-500">Erro ao carregar checklists</p>';
    }
}

// Visualizar detalhes do checklist
async function visualizarChecklist(id) {
    try {
        const response = await fetch(`/homologacoes/checklists/${id}`);
        const result = await response.json();
        
        if (result.success) {
            const checklist = result.data;
            alert(`📋 ${checklist.titulo}\n\nItens:\n${checklist.itens.map((item, i) => `${i+1}. ${item.titulo} (${item.tipo_resposta})`).join('\n')}`);
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

// Excluir checklist
async function excluirChecklist(id) {
    if (!confirm('⚠️ Deseja realmente excluir este checklist?')) return;
    
    try {
        const response = await fetch(`/homologacoes/checklists/${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Checklist excluído!');
            carregarChecklists();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao excluir checklist');
    }
}

// ===== CHECKLIST NO CARD ===== 

// Carregar checklists disponíveis no dropdown do card
async function carregarChecklistsNoCard(homologacaoId) {
    try {
        const response = await fetch('/homologacoes/checklists/list');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const select = document.getElementById(`selectChecklist${homologacaoId}`);
            if (select) {
                result.data.forEach(checklist => {
                    const option = document.createElement('option');
                    option.value = checklist.id;
                    option.textContent = checklist.titulo;
                    select.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar checklists:', error);
    }
}

// Carregar checklists disponíveis no dropdown
async function carregarChecklistsDropdown(homologacaoId) {
    try {
        const response = await fetch('/homologacoes/checklists/list');
        const result = await response.json();
        
        if (result.success) {
            const select = document.getElementById(`selectChecklist${homologacaoId}`);
            if (select) {
                result.data.forEach(checklist => {
                    const option = document.createElement('option');
                    option.value = checklist.id;
                    option.textContent = `${checklist.titulo} (${checklist.total_itens} itens)`;
                    select.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar checklists:', error);
    }
}

// Carregar itens do checklist selecionado
async function carregarItensChecklist(homologacaoId, checklistId) {
    if (!checklistId) {
        document.getElementById(`itensChecklist${homologacaoId}`).innerHTML = '';
        return;
    }
    
    try {
        const response = await fetch(`/homologacoes/checklists/${checklistId}`);
        const result = await response.json();
        
        if (result.success) {
            const checklist = result.data;
            renderizarItensChecklist(homologacaoId, checklistId, checklist.itens);
        }
    } catch (error) {
        console.error('Erro ao carregar itens:', error);
    }
}

// Renderizar itens do checklist
function renderizarItensChecklist(homologacaoId, checklistId, itens) {
    const container = document.getElementById(`itensChecklist${homologacaoId}`);
    
    const html = `
        <div class="space-y-3 mt-4">
            <h4 class="font-semibold text-sm text-purple-800 border-b pb-2">Preencha os itens abaixo:</h4>
            ${itens.map(item => renderizarItem(homologacaoId, checklistId, item)).join('')}
            <button onclick="salvarRespostasChecklist(${homologacaoId}, ${checklistId})" 
                    class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 mt-4">
                💾 Salvar Checklist
            </button>
        </div>
    `;
    
    container.innerHTML = html;
}

// Renderizar um item baseado no tipo
function renderizarItem(homologacaoId, checklistId, item) {
    const inputId = `item_${homologacaoId}_${item.id}`;
    
    switch (item.tipo_resposta) {
        case 'checkbox':
            return `
                <div class="flex items-center gap-2 p-3 bg-white rounded-lg border">
                    <input type="checkbox" id="${inputId}" class="w-4 h-4 text-purple-600">
                    <label for="${inputId}" class="text-sm flex-1">${item.titulo}</label>
                </div>
            `;
        
        case 'sim_nao':
            return `
                <div class="p-3 bg-white rounded-lg border">
                    <label class="text-sm font-medium mb-2 block">${item.titulo}</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="${inputId}" value="sim" class="text-purple-600">
                            <span class="text-sm">✓ Sim</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="${inputId}" value="nao" class="text-purple-600">
                            <span class="text-sm">✗ Não</span>
                        </label>
                    </div>
                </div>
            `;
        
        case 'texto':
            return `
                <div class="p-3 bg-white rounded-lg border">
                    <label for="${inputId}" class="text-sm font-medium mb-2 block">${item.titulo}</label>
                    <textarea id="${inputId}" rows="2" 
                              class="w-full px-3 py-2 border rounded-lg text-sm"
                              placeholder="Digite sua resposta..."></textarea>
                </div>
            `;
        
        case 'numero':
            return `
                <div class="p-3 bg-white rounded-lg border">
                    <label for="${inputId}" class="text-sm font-medium mb-2 block">${item.titulo}</label>
                    <input type="number" id="${inputId}" 
                           class="w-full px-3 py-2 border rounded-lg text-sm"
                           placeholder="Digite um número...">
                </div>
            `;
        
        default:
            return '';
    }
}

// Salvar respostas do checklist
async function salvarRespostasChecklist(homologacaoId, checklistId) {
    try {
        // Buscar checklist para pegar os itens
        const responseChecklist = await fetch(`/homologacoes/checklists/${checklistId}`);
        const resultChecklist = await responseChecklist.json();
        
        if (!resultChecklist.success) {
            alert('Erro ao buscar checklist');
            return;
        }
        
        const itens = resultChecklist.data.itens;
        const respostas = [];
        
        // Coletar respostas
        itens.forEach(item => {
            const inputId = `item_${homologacaoId}_${item.id}`;
            let resposta = '';
            let concluido = false;
            
            switch (item.tipo_resposta) {
                case 'checkbox':
                    const checkbox = document.getElementById(inputId);
                    concluido = checkbox?.checked || false;
                    resposta = concluido ? 'checked' : 'unchecked';
                    break;
                
                case 'sim_nao':
                    const radio = document.querySelector(`input[name="${inputId}"]:checked`);
                    resposta = radio?.value || '';
                    concluido = !!resposta;
                    break;
                
                case 'texto':
                case 'numero':
                    const input = document.getElementById(inputId);
                    resposta = input?.value || '';
                    concluido = !!resposta;
                    break;
            }
            
            respostas.push({
                item_id: item.id,
                resposta: resposta,
                concluido: concluido
            });
        });
        
        // Salvar via API
        const response = await fetch('/homologacoes/checklists/salvar-respostas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                homologacao_id: homologacaoId,
                checklist_id: checklistId,
                respostas: respostas
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Checklist salvo com sucesso!');
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao salvar checklist');
    }
}

// Carregar e exibir respostas salvas do checklist (modo visualização)
async function carregarRespostasChecklist(homologacaoId, checklistId) {
    try {
        // Buscar informações do checklist
        const responseChecklist = await fetch(`/homologacoes/checklists/${checklistId}`);
        const resultChecklist = await responseChecklist.json();
        
        if (!resultChecklist.success) {
            console.error('Erro ao buscar checklist');
            return;
        }
        
        const checklist = resultChecklist.data;
        const container = document.getElementById(`checklistRespostas${homologacaoId}`);
        
        // Buscar respostas salvas
        const responseRespostas = await fetch(`/homologacoes/${homologacaoId}/checklist-respostas`);
        const resultRespostas = await responseRespostas.json();
        
        const respostas = resultRespostas.success ? resultRespostas.data : [];
        
        // Criar mapa de respostas por item_id
        const respostasMap = {};
        respostas.forEach(r => {
            respostasMap[r.item_id] = r;
        });
        
        // Renderizar respostas
        let html = `
            <div class="bg-white p-4 rounded-lg border border-purple-200">
                <h4 class="font-semibold text-purple-800 mb-3">${checklist.titulo}</h4>
                ${checklist.descricao ? `<p class="text-sm text-gray-600 mb-4">${checklist.descricao}</p>` : ''}
                
                <div class="space-y-3">
                    ${checklist.itens.map(item => {
                        const resposta = respostasMap[item.id];
                        const respostaTexto = resposta ? resposta.resposta : '-';
                        const concluido = resposta?.concluido;
                        
                        let respostaFormatada = respostaTexto;
                        if (item.tipo_resposta === 'checkbox') {
                            respostaFormatada = respostaTexto === 'checked' ? '✅ Sim' : '❌ Não';
                        } else if (item.tipo_resposta === 'sim_nao') {
                            respostaFormatada = respostaTexto === 'sim' ? '✅ Sim' : respostaTexto === 'nao' ? '❌ Não' : '-';
                        }
                        
                        return `
                            <div class="flex items-start justify-between p-3 bg-gray-50 rounded">
                                <div class="flex-1">
                                    <div class="font-medium text-sm">${item.titulo}</div>
                                    <div class="text-xs text-gray-500 mt-1">Tipo: ${
                                        item.tipo_resposta === 'checkbox' ? 'Checkbox' :
                                        item.tipo_resposta === 'sim_nao' ? 'Sim/Não' :
                                        item.tipo_resposta === 'texto' ? 'Texto' : 'Número'
                                    }</div>
                                </div>
                                <div class="ml-4 text-right">
                                    <div class="font-semibold ${concluido ? 'text-green-600' : 'text-gray-500'}">
                                        ${respostaFormatada}
                                    </div>
                                    ${concluido ? '<div class="text-xs text-green-600 mt-1">✓ Concluído</div>' : ''}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    } catch (error) {
        console.error('Erro ao carregar respostas:', error);
    }
}
</script>

<?php // Fim da view de Homologações ?>
