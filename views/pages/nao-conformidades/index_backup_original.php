<?php
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin']);
$userId = $_SESSION['user_id'];
?>
<style>
.tab-button { padding: 12px 24px; border-bottom: 3px solid transparent; transition: all 0.3s; cursor: pointer; font-weight: 500; }
.tab-button.active { color: #dc2626; border-bottom-color: #dc2626; background: #fef2f2; }
.tab-button:hover:not(.active) { background: #f8fafc; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.nc-card { transition: all 0.2s; cursor: pointer; }
.nc-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.status-badge { padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-em_andamento { background: #dbeafe; color: #1e40af; }
.status-solucionada { background: #d1fae5; color: #065f46; }
</style>

<div class="container mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
            <span class="text-4xl">🚨</span>Não Conformidades (NC)
        </h1>
        <p class="text-gray-600 mt-2">Gestão completa de não conformidades e ações corretivas</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200 flex">
            <button class="tab-button active" onclick="switchTab('apontar')">
                📝 Apontar NC
            </button>
            <button class="tab-button" onclick="switchTab('pendentes')">
                ⏳ NC Pendentes <span class="ml-1 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs"><?= count($pendentes) + count($emAndamento) ?></span>
            </button>
            <button class="tab-button" onclick="switchTab('solucionadas')">
                ✅ NC Solucionadas <span class="ml-1 px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs"><?= count($solucionadas) ?></span>
            </button>
        </div>
    </div>

    <!-- ABA: APONTAR NC -->
    <div id="tab-apontar" class="tab-content active">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold mb-6">📝 Registrar Nova Não Conformidade</h2>
            <form id="formNovaNC" class="space-y-4" enctype="multipart/form-data">
                <div>
                    <label class="block text-sm font-medium mb-2">Título da NC <span class="text-red-500">*</span></label>
                    <input type="text" name="titulo" required class="w-full px-4 py-2 border rounded-lg" placeholder="Ex: Peça fora das especificações">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Descrição <span class="text-red-500">*</span></label>
                    <textarea name="descricao" required rows="5" class="w-full px-4 py-2 border rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Responsável <span class="text-red-500">*</span></label>
                    <select name="responsavel_id" required class="w-full px-4 py-2 border rounded-lg">
                        <option value="">Selecione</option>
                        <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Evidências (PNG, JPG, PDF, MP4 - máx 30MB)</label>
                    <input type="file" name="anexos[]" multiple accept=".png,.jpg,.jpeg,.pdf,.mp4" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <button type="submit" class="w-full bg-red-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-700">🚨 Registrar NC</button>
            </form>
        </div>
    </div>

    <!-- ABA: PENDENTES -->
    <div id="tab-pendentes" class="tab-content">
        <div class="space-y-4">
            <?php if (empty($pendentes) && empty($emAndamento)): ?>
                <div class="bg-white rounded-lg p-12 text-center">
                    <div class="text-6xl mb-4">✅</div>
                    <h3 class="text-xl font-bold mb-2">Nenhuma NC Pendente</h3>
                </div>
            <?php else: ?>
                <?php foreach (array_merge($pendentes, $emAndamento) as $nc): ?>
                <div class="nc-card bg-white rounded-lg shadow-sm p-6 border-l-4 <?= $nc['status']==='pendente'?'border-yellow-500':'border-blue-500' ?>" onclick="abrirDetalhes(<?= $nc['id'] ?>)">
                    <div class="flex justify-between mb-3">
                        <h3 class="font-bold text-lg"><?= e($nc['titulo']) ?></h3>
                        <span class="status-badge status-<?= $nc['status'] ?>"><?= $nc['status']==='pendente'?'Pendente':'Em Andamento' ?></span>
                    </div>
                    <p class="text-gray-700 mb-4"><?= e(substr($nc['descricao'], 0, 150)) ?>...</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500">Apontado:</span> <b><?= e($nc['criador_nome']) ?></b></div>
                        <div><span class="text-gray-500">Responsável:</span> <b><?= e($nc['responsavel_nome']) ?></b></div>
                        <div><span class="text-gray-500">Data:</span> <?= date('d/m/Y H:i', strtotime($nc['created_at'])) ?></div>
                        <div><span class="text-gray-500">Anexos:</span> <?= $nc['total_anexos'] ?></div>
                    </div>
                    <?php if ($nc['usuario_responsavel_id'] == $userId || $isAdmin): ?>
                    <button onclick="event.stopPropagation(); abrirFormAcao(<?= $nc['id'] ?>)" class="mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">✍️ Registrar Ação</button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ABA: SOLUCIONADAS -->
    <div id="tab-solucionadas" class="tab-content">
        <div class="space-y-4">
            <?php if (empty($solucionadas)): ?>
                <div class="bg-white rounded-lg p-12 text-center">
                    <div class="text-6xl mb-4">📋</div>
                    <h3 class="text-xl font-bold mb-2">Nenhuma NC Solucionada</h3>
                </div>
            <?php else: ?>
                <?php foreach ($solucionadas as $nc): ?>
                <div class="nc-card bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500" onclick="abrirDetalhes(<?= $nc['id'] ?>)">
                    <div class="flex justify-between mb-3">
                        <h3 class="font-bold text-lg"><?= e($nc['titulo']) ?></h3>
                        <span class="status-badge status-solucionada">Solucionada</span>
                    </div>
                    <p class="text-gray-700 mb-4"><?= e(substr($nc['descricao'], 0, 150)) ?>...</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500">Apontado:</span> <b><?= e($nc['criador_nome']) ?></b></div>
                        <div><span class="text-gray-500">Responsável:</span> <b><?= e($nc['responsavel_nome']) ?></b></div>
                        <div><span class="text-gray-500">Criada:</span> <?= date('d/m/Y', strtotime($nc['created_at'])) ?></div>
                        <div><span class="text-gray-500">Solucionada:</span> <b class="text-green-600"><?= date('d/m/Y', strtotime($nc['data_solucao'])) ?></b></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Detalhes -->
<div id="modalDetalhes" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4" onclick="fecharModal(event)">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="p-6" id="conteudoDetalhes"></div>
    </div>
</div>

<!-- Modal Ação -->
<div id="modalAcao" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4" onclick="fecharModalAcao(event)">
    <div class="bg-white rounded-lg max-w-2xl w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">✍️ Registrar Ação Corretiva</h2>
            <form id="formAcao" enctype="multipart/form-data">
                <input type="hidden" id="nc_id_acao">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Ação Tomada <span class="text-red-500">*</span></label>
                        <textarea name="acao_corretiva" required rows="5" class="w-full px-4 py-2 border rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Evidências (opcional)</label>
                        <input type="file" name="anexos[]" multiple accept=".png,.jpg,.jpeg,.pdf,.mp4" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">✅ Registrar</button>
                        <button type="button" onclick="fecharModalAcao()" class="px-6 py-3 border rounded-lg">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    event.target.closest('.tab-button').classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

document.getElementById('formNovaNC').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const res = await fetch('/nao-conformidades/criar', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        alert('❌ Erro ao criar NC');
    }
});

async function abrirDetalhes(id) {
    const modal = document.getElementById('modalDetalhes');
    const conteudo = document.getElementById('conteudoDetalhes');
    conteudo.innerHTML = '<div class="text-center py-8">⏳ Carregando...</div>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    try {
        const res = await fetch(`/nao-conformidades/detalhes/${id}`);
        const data = await res.json();
        if (data.success) {
            const nc = data.nc;
            const anexos = data.anexos;
            const statusLabel = {'pendente':'Pendente','em_andamento':'Em Andamento','solucionada':'Solucionada'};
            const anexosIniciais = anexos.filter(a => a.tipo_anexo === 'evidencia_inicial');
            const anexosAcao = anexos.filter(a => a.tipo_anexo === 'evidencia_acao');
            
            let html = `
                <div class="flex justify-between mb-6">
                    <div><h2 class="text-2xl font-bold">${nc.titulo}</h2><p class="text-gray-600">NC #${nc.id}</p></div>
                    <button onclick="fecharModal()" class="text-2xl">&times;</button>
                </div>
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <span class="status-badge status-${nc.status}">${statusLabel[nc.status]}</span>
                    </div>
                    <div><h3 class="font-bold mb-2">📝 Descrição</h3><p class="whitespace-pre-wrap">${nc.descricao}</p></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><h4 class="text-sm text-gray-500 mb-1">Apontado por</h4><p class="font-medium">${nc.criador_nome}</p><p class="text-sm text-gray-600">${new Date(nc.created_at).toLocaleString('pt-BR')}</p></div>
                        <div><h4 class="text-sm text-gray-500 mb-1">Responsável</h4><p class="font-medium">${nc.responsavel_nome}</p></div>
                    </div>`;
            
            if (anexosIniciais.length > 0) {
                html += `<div><h3 class="font-bold mb-2">📎 Evidências Iniciais (${anexosIniciais.length})</h3><div class="space-y-2">`;
                anexosIniciais.forEach(a => {
                    html += `<div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span>📄 ${a.nome_arquivo}</span>
                        <a href="/nao-conformidades/anexo/${a.id}" target="_blank" class="text-blue-600">Download</a>
                    </div>`;
                });
                html += `</div></div>`;
            }
            
            if (nc.acao_corretiva) {
                html += `<div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-bold mb-2">✍️ Ação Corretiva</h3>
                    <p class="whitespace-pre-wrap mb-3">${nc.acao_corretiva}</p>
                    <p class="text-sm text-gray-600">Por ${nc.acao_nome} em ${new Date(nc.data_acao).toLocaleString('pt-BR')}</p>`;
                
                if (anexosAcao.length > 0) {
                    html += `<div class="mt-4"><h4 class="font-medium mb-2">📎 Evidências (${anexosAcao.length})</h4>`;
                    anexosAcao.forEach(a => {
                        html += `<div class="flex justify-between items-center p-2 bg-white rounded mt-2">
                            <span>📄 ${a.nome_arquivo}</span>
                            <a href="/nao-conformidades/anexo/${a.id}" target="_blank" class="text-blue-600">Download</a>
                        </div>`;
                    });
                    html += `</div>`;
                }
                html += `</div>`;
            }
            
            if (nc.status === 'solucionada') {
                html += `<div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-bold text-green-800">✅ NC Solucionada</h3>
                    <p class="text-sm text-gray-600">Por ${nc.solucao_nome} em ${new Date(nc.data_solucao).toLocaleString('pt-BR')}</p>
                </div>`;
            } else if (nc.usuario_criador_id == <?= $userId ?> || nc.usuario_responsavel_id == <?= $userId ?> || <?= $isAdmin ? 'true' : 'false' ?>) {
                html += `<button onclick="marcarSolucionada(${nc.id})" class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">✅ Marcar como Solucionada</button>`;
            }
            
            html += `</div>`;
            conteudo.innerHTML = html;
        }
    } catch (error) {
        conteudo.innerHTML = '<div class="text-center py-8 text-red-600">Erro ao carregar</div>';
    }
}

function fecharModal(event) {
    if (!event || event.target.id === 'modalDetalhes') {
        document.getElementById('modalDetalhes').classList.add('hidden');
        document.getElementById('modalDetalhes').classList.remove('flex');
    }
}

function abrirFormAcao(id) {
    document.getElementById('nc_id_acao').value = id;
    document.getElementById('formAcao').reset();
    document.getElementById('modalAcao').classList.remove('hidden');
    document.getElementById('modalAcao').classList.add('flex');
}

function fecharModalAcao(event) {
    if (!event || event.target.id === 'modalAcao') {
        document.getElementById('modalAcao').classList.add('hidden');
        document.getElementById('modalAcao').classList.remove('flex');
    }
}

document.getElementById('formAcao').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('nc_id_acao').value;
    const formData = new FormData(this);
    try {
        const res = await fetch(`/nao-conformidades/registrar-acao/${id}`, { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        alert('❌ Erro ao registrar ação');
    }
});

async function marcarSolucionada(id) {
    if (!confirm('Marcar esta NC como solucionada?')) return;
    try {
        const res = await fetch(`/nao-conformidades/marcar-solucionada/${id}`, { method: 'POST' });
        const result = await res.json();
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        alert('❌ Erro ao marcar como solucionada');
    }
}
</script>
