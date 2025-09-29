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
        <div class="flex gap-3">
            <button onclick="abrirTutorial5W2H()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m2-10v18a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h6l2 2z"></path>
                </svg>
                Aprenda a usar
            </button>
            <button onclick="toggleFormulario()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span id="btnText">Novo Plano 5W2H</span>
            </button>
        </div>
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

    <!-- Formulário Inline -->
    <div id="formularioInline" class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 mb-6" style="display: none;">
        <div class="border-b border-gray-600 pb-4 mb-6">
            <h2 class="text-xl font-semibold text-white">Novo Plano 5W2H</h2>
            <p class="text-gray-300 mt-1">Preencha os campos abaixo para criar um novo plano de ação</p>
        </div>

        <form id="planoForm" class="ajax-form">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- TÍTULO -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        Título do Plano <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="titulo" name="titulo" required 
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                           placeholder="Digite um título resumido para o plano...">
                </div>

                <!-- O QUE (What) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        O QUE será feito? <span class="text-red-400">*</span>
                    </label>
                    <textarea id="what" name="what" rows="3" required 
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                            placeholder="Descreva detalhadamente o que será realizado..."></textarea>
                </div>

                <!-- POR QUE (Why) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        POR QUE será feito? <span class="text-red-400">*</span>
                    </label>
                    <textarea id="why" name="why" rows="3" required 
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                            placeholder="Justifique a necessidade desta ação..."></textarea>
                </div>

                <!-- QUEM (Who) -->
                <div>
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        QUEM será o responsável? <span class="text-red-400">*</span>
                    </label>
                    <select id="who" name="who" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        QUANDO será realizado? <span class="text-red-400">*</span>
                    </label>
                    <input type="date" id="when" name="when" required 
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- ONDE (Where) -->
                <div>
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        ONDE será executado? <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="where" name="where" required 
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                           placeholder="Local de execução">
                </div>

                <!-- COMO (How) -->
                <div>
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        COMO será executado? <span class="text-red-400">*</span>
                    </label>
                    <textarea id="how" name="how" rows="3" required 
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                            placeholder="Descreva o método de execução..."></textarea>
                </div>

                <!-- QUANTO CUSTA (How Much) -->
                <div>
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        QUANTO custará? <span class="text-gray-400 text-xs">(opcional - padrão R$ 0,00)</span>
                    </label>
                    <input type="number" id="howMuch" name="howMuch" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                           placeholder="Deixe vazio para R$ 0,00">
                </div>

                <!-- Departamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        Departamento <span class="text-red-400">*</span>
                    </label>
                    <select id="departamento" name="departamento" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                    <label class="block text-sm font-medium text-gray-200 mb-2">Status</label>
                    <select id="status" name="status" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="pendente">Pendente</option>
                        <option value="em_andamento">Em Andamento</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <!-- Anexos -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        Anexos <span class="text-gray-400">(até 5 arquivos - JPG, PNG, GIF, PDF - máx 5MB cada)</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-600 rounded-lg p-4 bg-gray-700">
                        <input type="file" id="anexos" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf" 
                               class="w-full text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                        <div id="anexosPreview" class="mt-3 space-y-2"></div>
                        <p class="text-xs text-gray-400 mt-2">Arraste arquivos aqui ou clique para selecionar</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-600">
                <button type="button" onclick="cancelarFormulario()" class="px-4 py-2 text-gray-300 border border-gray-500 rounded-lg hover:bg-gray-700 hover:text-white transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Salvar Plano
                </button>
            </div>
        </form>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prazo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anexos</th>
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


<script>
// Variáveis globais
let currentPlanoId = null;

// Carregar dados ao inicializar
document.addEventListener('DOMContentLoaded', function() {
    loadPlanos();
});

// Toggle formulário inline
function toggleFormulario() {
    const formulario = document.getElementById('formularioInline');
    const btnText = document.getElementById('btnText');
    
    if (formulario.style.display === 'none' || formulario.style.display === '') {
        // Mostrar formulário
        formulario.style.display = 'block';
        btnText.textContent = 'Cancelar';
        currentPlanoId = null;
        document.getElementById('planoForm').reset();
        // Scroll suave para o formulário
        formulario.scrollIntoView({ behavior: 'smooth' });
    } else {
        // Esconder formulário
        formulario.style.display = 'none';
        btnText.textContent = 'Novo Plano 5W2H';
        currentPlanoId = null;
    }
}

// Cancelar formulário
function cancelarFormulario() {
    const formulario = document.getElementById('formularioInline');
    const btnText = document.getElementById('btnText');
    
    formulario.style.display = 'none';
    btnText.textContent = 'Novo Plano 5W2H';
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
                <div class="text-sm font-medium text-gray-900">${plano.titulo || ''}</div>
                <div class="text-xs text-gray-500">${(plano.what || '').substring(0, 50)}${(plano.what || '').length > 50 ? '...' : ''}</div>
            </td>
            <td class="px-4 py-3">
                <div class="text-sm text-gray-900">${plano.responsavel_nome || ''}</div>
            </td>
            <td class="px-4 py-3">
                <div class="text-sm text-gray-900">${formatDate(plano.when_inicio) || ''}</div>
            </td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(plano.status)}">
                    ${getStatusText(plano.status)}
                </span>
            </td>
            <td class="px-4 py-3">
                <div class="flex gap-1">
                    ${(plano.anexos_count || 0) > 0 ? `
                        <button onclick="viewAnexos(${plano.id})" class="text-blue-600 hover:text-blue-800" title="${plano.anexos_count} anexo(s)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                        </button>
                        <span class="text-xs text-gray-500">${plano.anexos_count}</span>
                    ` : '<span class="text-xs text-gray-400">-</span>'}
                </div>
            </td>
            <td class="px-4 py-3">
                <div class="flex gap-1">
                    <button onclick="viewPlano(${plano.id})" class="text-blue-600 hover:text-blue-800" title="Visualizar Detalhes">
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
                    <button onclick="printPlano(${plano.id})" class="text-purple-600 hover:text-purple-800" title="Imprimir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
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
            cancelarFormulario();
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

// Visualizar detalhes do plano
function viewPlano(id) {
    fetch(`/5w2h/details/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPlanoDetails(data.plano);
            } else {
                showNotification(data.message || 'Erro ao carregar detalhes', 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao carregar detalhes', 'error');
        });
}

// Editar plano
function editPlano(id) {
    fetch(`/5w2h/details/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadPlanoForEdit(data.plano);
            } else {
                showNotification(data.message || 'Erro ao carregar plano', 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao carregar plano', 'error');
        });
}

// Imprimir plano
function printPlano(id) {
    window.open(`/5w2h/print/${id}`, '_blank');
}

// Visualizar anexos
function viewAnexos(id) {
    fetch(`/5w2h/anexos/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAnexosModal(data.anexos);
            } else {
                showNotification(data.message || 'Erro ao carregar anexos', 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao carregar anexos', 'error');
        });
}

// Carregar plano para edição
function loadPlanoForEdit(plano) {
    currentPlanoId = plano.id;
    
    // Preencher campos
    document.getElementById('titulo').value = plano.titulo || '';
    document.getElementById('what').value = plano.what || '';
    document.getElementById('why').value = plano.why || '';
    document.getElementById('who').value = plano.who_id || '';
    document.getElementById('when').value = plano.when_inicio || '';
    document.getElementById('where').value = plano.where_local || '';
    document.getElementById('how').value = plano.how || '';
    document.getElementById('howMuch').value = plano.how_much || '';
    document.getElementById('departamento').value = plano.setor_id || '';
    document.getElementById('status').value = plano.status || '';
    
    // Mostrar formulário
    const formulario = document.getElementById('formularioInline');
    const btnText = document.getElementById('btnText');
    formulario.style.display = 'block';
    btnText.textContent = 'Cancelar';
    
    // Scroll para o formulário
    formulario.scrollIntoView({ behavior: 'smooth' });
}

// Mostrar detalhes do plano em modal
function showPlanoDetails(plano) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Detalhes do Plano 5W2H</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-900 mb-2">Título</h4>
                            <p class="text-gray-700">${plano.titulo || ''}</p>
                        </div>
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-900 mb-2">O QUE será feito?</h4>
                            <p class="text-gray-700">${plano.what || ''}</p>
                        </div>
                        <div class="md:col-span-2">
                            <h4 class="font-semibold text-gray-900 mb-2">POR QUE será feito?</h4>
                            <p class="text-gray-700">${plano.why || ''}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">QUEM é o responsável?</h4>
                            <p class="text-gray-700">${plano.responsavel_nome || ''}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">QUANDO será realizado?</h4>
                            <p class="text-gray-700">${formatDate(plano.when_inicio) || ''}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">ONDE será executado?</h4>
                            <p class="text-gray-700">${plano.where_local || ''}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">COMO será executado?</h4>
                            <p class="text-gray-700">${plano.how || ''}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">QUANTO custará?</h4>
                            <p class="text-gray-700">R$ ${parseFloat(plano.how_much || 0).toFixed(2)}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Status</h4>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(plano.status)}">
                                ${getStatusText(plano.status)}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t">
                    <div class="flex justify-end gap-3">
                        <button onclick="printPlano(${plano.id})" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Imprimir
                        </button>
                        <button onclick="editPlano(${plano.id}); this.closest('.fixed').remove();" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Editar
                        </button>
                        <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Excluir plano
function deletePlano(id) {
    if (confirm('Tem certeza que deseja excluir este plano?')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch(`/5w2h/delete`, {
            method: 'POST',
            body: formData
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

// Preview de arquivos
document.getElementById('anexos').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const preview = document.getElementById('anexosPreview');
    
    if (files.length > 5) {
        alert('Máximo de 5 arquivos permitidos');
        e.target.value = '';
        return;
    }
    
    preview.innerHTML = '';
    files.forEach((file, index) => {
        if (file.size > 5 * 1024 * 1024) {
            alert(`Arquivo ${file.name} é muito grande (máx 5MB)`);
            return;
        }
        
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between bg-gray-600 p-2 rounded text-sm';
        div.innerHTML = `
            <span class="text-gray-200">${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)</span>
            <button type="button" onclick="removeFile(${index})" class="text-red-400 hover:text-red-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        preview.appendChild(div);
    });
});

function removeFile(index) {
    const input = document.getElementById('anexos');
    const dt = new DataTransfer();
    const files = Array.from(input.files);
    
    files.forEach((file, i) => {
        if (i !== index) dt.items.add(file);
    });
    
    input.files = dt.files;
    input.dispatchEvent(new Event('change'));
}

// Mostrar modal de anexos
function showAnexosModal(anexos) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Anexos do Plano (${anexos.length})</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    ${anexos.length === 0 ? 
                        '<p class="text-gray-500 text-center py-8">Nenhum anexo encontrado</p>' :
                        anexos.map(anexo => `
                            <div class="flex items-center justify-between p-3 border rounded-lg mb-3 hover:bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        ${getFileIcon(anexo.tipo_arquivo)}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">${anexo.nome_original}</p>
                                        <p class="text-xs text-gray-500">
                                            ${(anexo.tamanho_arquivo / 1024 / 1024).toFixed(2)} MB • 
                                            Enviado por ${anexo.uploaded_by_nome} • 
                                            ${formatDate(anexo.uploaded_at)}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="downloadAnexo(${anexo.id})" 
                                            class="text-blue-600 hover:text-blue-800 p-1" title="Baixar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        `).join('')
                    }
                </div>
                <div class="p-6 border-t">
                    <div class="flex justify-end">
                        <button onclick="this.closest('.fixed').remove()" 
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Ícone do arquivo baseado no tipo
function getFileIcon(tipo) {
    if (tipo.includes('image')) {
        return `<svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>`;
    } else if (tipo.includes('pdf')) {
        return `<svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>`;
    } else {
        return `<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>`;
    }
}

// Download de anexo
function downloadAnexo(anexoId) {
    window.open(`/5w2h/anexo/${anexoId}`, '_blank');
}

// Função para abrir o tutorial 5W2H
function abrirTutorial5W2H() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4">
            <!-- Header do Modal -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">📚 Tutorial - Como usar 5W2H</h2>
                    <p class="text-gray-600 mt-1">Aprenda a metodologia 5W2H para criar planos de ação eficazes</p>
                </div>
                <button onclick="fecharTutorial()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
                    ×
                </button>
            </div>
            
            <!-- Vídeo Tutorial -->
            <div class="p-6">
                <div class="bg-gray-900 rounded-lg overflow-hidden shadow-lg">
                    <video id="tutorial5w2h" controls class="w-full h-auto" style="max-height: 500px;">
                        <source src="public/assets/5w2h.mp4" type="video/mp4">
                        Seu navegador não suporta o elemento de vídeo.
                    </video>
                </div>
            </div>
            
            <!-- Footer do Modal -->
            <div class="flex justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
                <button onclick="fecharTutorial()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Focar no vídeo quando abrir
    setTimeout(() => {
        const video = document.getElementById('tutorial5w2h');
        if (video) {
            video.focus();
        }
    }, 100);
}

// Função para fechar o tutorial
function fecharTutorial() {
    const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-75');
    if (modal) {
        // Pausar o vídeo antes de fechar
        const video = modal.querySelector('video');
        if (video) {
            video.pause();
        }
        modal.remove();
    }
}


// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharTutorial();
    }
});
</script>
