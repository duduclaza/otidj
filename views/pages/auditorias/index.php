<?php
// Helpers protegidos contra redeclaração
if (!function_exists('hasPermission')) {
    function hasPermission($module, $action = 'view') {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        // Admin e Super Admin sempre tem acesso
        $userRole = $_SESSION['user_role'] ?? '';
        if (in_array($userRole, ['admin', 'super_admin'])) {
            return true;
        }
        // Usar PermissionService para usuários comuns
        $profile = $_SESSION['profile'] ?? ($_SESSION['user_profile']['profile_name'] ?? null);
        if ($profile === 'Administrador') { return true; }

        $permissions = $_SESSION['permissions'] ?? ($_SESSION['user_profile']['permissions'] ?? []);
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if (($permission['module'] ?? null) === $module) {
                    switch ($action) {
                        case 'view': return (bool)$permission['can_view'];
                        case 'edit': return (bool)$permission['can_edit'];
                        case 'delete': return (bool)$permission['can_delete'];
                        case 'import': return (bool)$permission['can_import'];
                        case 'export': return (bool)$permission['can_export'];
                    }
                }
            }
        }

        // Fallback final: consultar serviço
        try {
            $map = ['view'=>'view','edit'=>'edit','delete'=>'delete','import'=>'import','export'=>'export'];
            $actionKey = $map[$action] ?? 'view';
            return \App\Services\PermissionService::hasPermission((int)$_SESSION['user_id'], $module, $actionKey);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('e')) {
    function e($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}

// Verificar permissões
$canEdit = hasPermission('auditorias', 'edit');
$canDelete = hasPermission('auditorias', 'delete');
$canExport = hasPermission('auditorias', 'export');
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Auditorias</h1>
                <p class="mt-2 text-gray-600">Gerenciamento de auditorias por filial</p>
            </div>
            <div class="flex space-x-3">
                <?php if ($canExport): ?>
                <button onclick="exportarAuditorias()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Exportar
                </button>
                <?php endif; ?>
                <?php if ($canEdit): ?>
                <button onclick="abrirModalAuditoria()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nova Auditoria
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros de Busca</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filial</label>
                <select id="filtro-filial" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as filiais</option>
                    <?php foreach ($filiais as $filial): ?>
                        <option value="<?= $filial['id'] ?>"><?= e($filial['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
                <input type="date" id="filtro-data-inicio" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
                <input type="date" id="filtro-data-fim" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="mt-4 flex justify-end space-x-3">
            <button onclick="limparFiltros()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                Limpar
            </button>
            <button onclick="aplicarFiltros()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                Buscar
            </button>
        </div>
    </div>

    <!-- Tabela de Auditorias -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Lista de Auditorias</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filial</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período da Auditoria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anexo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Criação</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-auditorias" class="bg-white divide-y divide-gray-200">
                    <!-- Dados carregados via JavaScript -->
                </tbody>
            </table>
        </div>
        <div id="no-data" class="text-center py-8 hidden">
            <p class="text-gray-500">Nenhuma auditoria encontrada.</p>
        </div>
    </div>
</div>

<!-- Modal Auditoria -->
<div id="modal-auditoria" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modal-titulo">Nova Auditoria</h3>
                <button onclick="fecharModalAuditoria()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="form-auditoria" enctype="multipart/form-data">
                <input type="hidden" id="auditoria-id" name="id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filial *</label>
                    <select id="filial-id" name="filial_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione uma filial</option>
                        <?php foreach ($filiais as $filial): ?>
                            <option value="<?= $filial['id'] ?>"><?= e($filial['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data de Início da Auditoria *</label>
                        <input type="date" id="data-auditoria-inicio" name="data_auditoria_inicio" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data de Fim da Auditoria *</label>
                        <input type="date" id="data-auditoria-fim" name="data_auditoria_fim" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Anexo da Auditoria (PDF ou DOC)</label>
                    <input type="file" id="anexo-auditoria" name="anexo_auditoria" accept=".pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <small class="text-gray-500">Formatos aceitos: PDF, DOC, DOCX. Máximo 15MB</small>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Observações sobre a auditoria..."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalAuditoria()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let auditorias = [];

// Carregar dados ao inicializar
document.addEventListener('DOMContentLoaded', function() {
    carregarAuditorias();
});

// Carregar lista de auditorias
function carregarAuditorias() {
    // Remover loading - carregar diretamente
    document.getElementById('no-data').classList.add('hidden');
    
    const params = new URLSearchParams();
    const filialId = document.getElementById('filtro-filial').value;
    const dataInicio = document.getElementById('filtro-data-inicio').value;
    const dataFim = document.getElementById('filtro-data-fim').value;
    
    if (filialId) params.append('filial_id', filialId);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    
    fetch(`/auditorias/list?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                auditorias = data.data;
                renderizarTabela();
            } else {
                alert('Erro ao carregar auditorias: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar auditorias');
        });
}

// Renderizar tabela
function renderizarTabela() {
    const tbody = document.getElementById('tabela-auditorias');
    
    if (auditorias.length === 0) {
        document.getElementById('no-data').classList.remove('hidden');
        tbody.innerHTML = '';
        return;
    }
    
    document.getElementById('no-data').classList.add('hidden');
    
    tbody.innerHTML = auditorias.map(auditoria => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${escapeHtml(auditoria.filial_nome || '')}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex flex-col">
                    <span class="font-medium">Início: ${formatarData(auditoria.data_auditoria_inicio)}</span>
                    <span>Fim: ${formatarData(auditoria.data_auditoria_fim)}</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${auditoria.tem_anexo ? 
                    `<a href="/auditorias/anexo/${auditoria.id}" class="text-blue-600 hover:text-blue-800 flex items-center" title="Baixar anexo">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Baixar
                    </a>` : 
                    '<span class="text-gray-400">Sem anexo</span>'
                }
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${escapeHtml(auditoria.criado_por_nome || '')}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${formatarDataHora(auditoria.created_at)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <?php if ($canEdit): ?>
                    <button onclick="editarAuditoria(${auditoria.id})" class="text-blue-600 hover:text-blue-800" title="Editar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                    <?php if ($canDelete): ?>
                    <button onclick="excluirAuditoria(${auditoria.id})" class="text-red-600 hover:text-red-800" title="Excluir">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    `).join('');
}

// Aplicar filtros
function aplicarFiltros() {
    carregarAuditorias();
}

// Limpar filtros
function limparFiltros() {
    document.getElementById('filtro-filial').value = '';
    document.getElementById('filtro-data-inicio').value = '';
    document.getElementById('filtro-data-fim').value = '';
    carregarAuditorias();
}

// Abrir modal para nova auditoria
function abrirModalAuditoria() {
    document.getElementById('modal-titulo').textContent = 'Nova Auditoria';
    document.getElementById('form-auditoria').reset();
    document.getElementById('auditoria-id').value = '';
    document.getElementById('modal-auditoria').classList.remove('hidden');
}

// Fechar modal
function fecharModalAuditoria() {
    document.getElementById('modal-auditoria').classList.add('hidden');
}

// Editar auditoria
function editarAuditoria(id) {
    const auditoria = auditorias.find(a => a.id == id);
    if (!auditoria) return;
    
    document.getElementById('modal-titulo').textContent = 'Editar Auditoria';
    document.getElementById('auditoria-id').value = auditoria.id;
    document.getElementById('filial-id').value = auditoria.filial_id;
    document.getElementById('data-auditoria-inicio').value = auditoria.data_auditoria_inicio;
    document.getElementById('data-auditoria-fim').value = auditoria.data_auditoria_fim;
    document.getElementById('observacoes').value = auditoria.observacoes || '';
    
    document.getElementById('modal-auditoria').classList.remove('hidden');
}

// Excluir auditoria
function excluirAuditoria(id) {
    if (!confirm('Tem certeza que deseja excluir esta auditoria?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    fetch('/auditorias/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Auditoria excluída com sucesso!');
            carregarAuditorias();
        } else {
            alert('Erro ao excluir auditoria: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao excluir auditoria');
    });
}

// Submit do formulário
document.getElementById('form-auditoria').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const isEdit = document.getElementById('auditoria-id').value !== '';
    const url = isEdit ? '/auditorias/update' : '/auditorias/create';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            fecharModalAuditoria();
            carregarAuditorias();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar auditoria');
    });
});

// Funções auxiliares
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatarData(data) {
    if (!data) return '-';
    const date = new Date(data + 'T00:00:00');
    return date.toLocaleDateString('pt-BR');
}

function formatarDataHora(dataHora) {
    if (!dataHora) return '-';
    const date = new Date(dataHora);
    return date.toLocaleString('pt-BR');
}

function exportarAuditorias() {
    // TODO: Implementar exportação
    alert('Funcionalidade de exportação será implementada em breve');
}
</script>
