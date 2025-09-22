<?php
// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">5W2H - Planos de Ação</h1>
            <p class="text-gray-600 mt-2">Gerencie seus planos de ação utilizando a metodologia 5W2H</p>
        </div>
        <button onclick="openCreateModal(); console.log('Botão clicado');" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Novo Plano 5W2H
        </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="filterStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todos os Status</option>
                    <option value="pendente">Pendente</option>
                    <option value="em_andamento">Em Andamento</option>
                    <option value="concluido">Concluído</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Responsável</label>
                <select id="filterResponsavel" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todos os Responsáveis</option>
                    <?php if (isset($usuarios) && is_array($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
                <select id="filterDepartamento" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todos os Departamentos</option>
                    <?php if (isset($departamentos) && is_array($departamentos)): ?>
                        <?php foreach ($departamentos as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['nome']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg w-full">
                    Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Planos -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Planos de Ação Cadastrados</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">O Que</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prazo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody id="planosTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Dados carregados via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Criar/Editar Plano -->
<div id="planoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Novo Plano 5W2H</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="planoForm" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- O QUE (What) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            O QUE será feito? <span class="text-red-500">*</span>
                        </label>
                        <textarea id="what" name="what" rows="3" required 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Descreva detalhadamente o que será realizado..."></textarea>
                    </div>

                    <!-- POR QUE (Why) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            POR QUE será feito? <span class="text-red-500">*</span>
                        </label>
                        <textarea id="why" name="why" rows="3" required 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Justifique a necessidade desta ação..."></textarea>
                    </div>

                    <!-- QUEM (Who) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            QUEM será o responsável? <span class="text-red-500">*</span>
                        </label>
                        <select id="who" name="who" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o responsável</option>
                            <?php if (isset($usuarios) && is_array($usuarios)): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- QUANDO (When) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            QUANDO será realizado? <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="when" name="when" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- ONDE (Where) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ONDE será executado? <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="where" name="where" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Local de execução">
                    </div>

                    <!-- COMO (How) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            COMO será executado? <span class="text-red-500">*</span>
                        </label>
                        <textarea id="how" name="how" rows="3" required 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Descreva o método de execução..."></textarea>
                    </div>

                    <!-- QUANTO CUSTA (How Much) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            QUANTO custará?
                        </label>
                        <input type="number" id="howMuch" name="howMuch" step="0.01" min="0"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0.00">
                    </div>

                    <!-- Departamento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Departamento <span class="text-red-500">*</span>
                        </label>
                        <select id="departamento" name="departamento" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o departamento</option>
                            <?php if (isset($departamentos) && is_array($departamentos)): ?>
                                <?php foreach ($departamentos as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['nome']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pendente">Pendente</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Salvar Plano
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variáveis globais
let currentPlanoId = null;

// Carregar dados ao inicializar
document.addEventListener('DOMContentLoaded', function() {
    loadPlanos();
});

// Abrir modal para criar novo plano
function openCreateModal() {
    console.log('Abrindo modal...');
    try {
        currentPlanoId = null;
        document.getElementById('modalTitle').textContent = 'Novo Plano 5W2H';
        document.getElementById('planoForm').reset();
        const modal = document.getElementById('planoModal');
        console.log('Modal encontrado:', modal);
        modal.style.display = 'block';
        console.log('Modal aberto com sucesso');
    } catch (error) {
        console.error('Erro ao abrir modal:', error);
        alert('Erro ao abrir formulário: ' + error.message);
    }
}

// Fechar modal
function closeModal() {
    document.getElementById('planoModal').style.display = 'none';
    document.getElementById('planoForm').reset();
    currentPlanoId = null;
}

// Carregar lista de planos
function loadPlanos() {
    fetch('/5w2h/list')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderPlanos(data.planos || []);
            } else {
                console.error('Erro ao carregar planos:', data.message);
                document.getElementById('planosTableBody').innerHTML = 
                    '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Erro ao carregar dados</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('planosTableBody').innerHTML = 
                '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Erro ao carregar dados</td></tr>';
        });
}

// Renderizar lista de planos
function renderPlanos(planos) {
    const tbody = document.getElementById('planosTableBody');
    
    if (planos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Nenhum plano cadastrado</td></tr>';
        return;
    }

    tbody.innerHTML = planos.map(plano => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">${plano.what || ''}</div>
            </td>
            <td class="px-4 py-3">
                <div class="text-sm text-gray-900">${plano.responsavel_nome || ''}</div>
            </td>
            <td class="px-4 py-3">
                <div class="text-sm text-gray-900">${formatDate(plano.when) || ''}</div>
            </td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(plano.status)}">
                    ${getStatusText(plano.status)}
                </span>
            </td>
            <td class="px-4 py-3">
                <div class="flex gap-2">
                    <button onclick="viewPlano(${plano.id})" class="text-blue-600 hover:text-blue-800" title="Visualizar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                    <button onclick="editPlano(${plano.id})" class="text-green-600 hover:text-green-800" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="deletePlano(${plano.id})" class="text-red-600 hover:text-red-800" title="Excluir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Aplicar filtros
function applyFilters() {
    loadPlanos(); // Por enquanto, apenas recarrega - implementar filtros no backend depois
}

// Salvar plano (criar/editar)
document.getElementById('planoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = currentPlanoId ? `/5w2h/update` : `/5w2h/create`;
    
    if (currentPlanoId) {
        formData.append('id', currentPlanoId);
    }

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            loadPlanos();
            showNotification('Plano salvo com sucesso!', 'success');
        } else {
            showNotification(data.message || 'Erro ao salvar plano', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification('Erro ao salvar plano', 'error');
    });
});

// Funções auxiliares
function getStatusClass(status) {
    const classes = {
        'pendente': 'bg-yellow-100 text-yellow-800',
        'em_andamento': 'bg-blue-100 text-blue-800',
        'concluido': 'bg-green-100 text-green-800',
        'cancelado': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusText(status) {
    const texts = {
        'pendente': 'Pendente',
        'em_andamento': 'Em Andamento',
        'concluido': 'Concluído',
        'cancelado': 'Cancelado'
    };
    return texts[status] || status;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function showNotification(message, type = 'info') {
    // Implementar sistema de notificação
    alert(message);
}

// Placeholder functions - implementar depois
function viewPlano(id) {
    console.log('Visualizar plano:', id);
}

function editPlano(id) {
    console.log('Editar plano:', id);
}

function deletePlano(id) {
    if (confirm('Tem certeza que deseja excluir este plano?')) {
        fetch(`/5w2h/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadPlanos();
                showNotification('Plano excluído com sucesso!', 'success');
            } else {
                showNotification(data.message || 'Erro ao excluir plano', 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao excluir plano', 'error');
        });
    }
}
</script>
