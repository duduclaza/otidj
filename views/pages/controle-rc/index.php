<!-- Header -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
        📋 Controle de RC
    </h1>
    <p class="text-gray-600 mt-1">Registro e controle de reclamações</p>
</div>

<style>
    /* Estilos para dropdown de status */
    .status-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
        transition: all 0.2s ease;
    }
    
    .status-select:hover {
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .status-select:focus {
        outline: none;
        ring: 2px;
        ring-color: #3b82f6;
    }
    
    /* Animação de notificação */
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    #notificacao-status {
        animation: slideIn 0.3s ease-out;
    }
</style>

        <!-- Formulário Inline (Tema Escuro) -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-white" id="formTitle">Novo Registro de RC</h2>
                <button type="button" onclick="cancelarFormulario()" id="btnCancelar" class="hidden px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Cancelar Edição
                </button>
            </div>

            <form id="formRC" enctype="multipart/form-data" class="ajax-form">
                <input type="hidden" id="rc_id" name="id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Data de Abertura -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Data de Abertura *</label>
                        <input type="date" name="data_abertura" id="data_abertura" required 
                               class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Origem -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Origem da Reclamação *</label>
                        <select name="origem" id="origem" required 
                                class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Selecione...</option>
                            <option value="Telefone">Telefone</option>
                            <option value="E-mail">E-mail</option>
                            <option value="Presencial">Presencial</option>
                            <option value="Formulário">Formulário</option>
                            <option value="Contrato">Contrato</option>
                            <option value="Auditoria">Auditoria</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <!-- Cliente/Empresa -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nome do Cliente/Empresa *</label>
                        <input type="text" name="cliente_nome" id="cliente_nome" required 
                               class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Digite o nome...">
                    </div>

                    <!-- Categoria -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Categoria da Reclamação *</label>
                        <select name="categoria" id="categoria" required 
                                class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Selecione...</option>
                            <option value="Técnica">Técnica</option>
                            <option value="Atendimento">Atendimento</option>
                            <option value="Logística">Logística</option>
                            <option value="Contrato">Contrato</option>
                            <option value="Faturamento">Faturamento</option>
                            <option value="Qualidade">Qualidade</option>
                            <option value="Prazos">Prazos</option>
                            <option value="Produto">Produto</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <!-- Detalhamento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Detalhamento</label>
                        <textarea name="detalhamento" id="detalhamento" rows="2"
                                  class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Detalhamento adicional..."></textarea>
                    </div>

                    <!-- Qual Produto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Qual Produto</label>
                        <select name="qual_produto" id="qual_produto" 
                                class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Selecione...</option>
                            <option value="Suprimentos">Suprimentos</option>
                            <option value="Atendimento">Atendimento</option>
                            <option value="Atendimento Técnico">Atendimento Técnico</option>
                            <option value="Equipamento">Equipamento</option>
                        </select>
                    </div>

                    <!-- Número de Série -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Número de Série/Identificação</label>
                        <input type="text" name="numero_serie" id="numero_serie" 
                               class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Opcional">
                    </div>

                    <!-- Fornecedor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Fornecedor</label>
                        <select name="fornecedor_id" id="fornecedor_id" 
                                class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Selecione...</option>
                            <?php foreach ($fornecedores as $forn): ?>
                                <option value="<?= $forn['id'] ?>"><?= htmlspecialchars($forn['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status do RC -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Status do RC</label>
                        <select name="status" id="status" 
                                class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="Em analise">Em análise</option>
                            <option value="Aguardando ações do fornecedor">Aguardando ações do fornecedor</option>
                            <option value="Aguardando retorno do produto">Aguardando retorno do produto</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Concluída">Concluída</option>
                        </select>
                    </div>
                </div>

                <!-- Campos Texto Longo -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
                    <!-- Testes Realizados -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Testes Realizados</label>
                        <textarea name="testes_realizados" id="testes_realizados" rows="4" 
                                  class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Descreva os testes realizados..."></textarea>
                    </div>

                    <!-- Ações Realizadas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Ações Realizadas</label>
                        <textarea name="acoes_realizadas" id="acoes_realizadas" rows="4" 
                                  class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Descreva as ações realizadas..."></textarea>
                    </div>

                    <!-- Conclusão -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Conclusão</label>
                        <textarea name="conclusao" id="conclusao" rows="4" 
                                  class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Conclusão final do registro..."></textarea>
                    </div>
                </div>

                <!-- Upload de Evidências -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Evidências (Fotos, PDFs, etc.)</label>
                    <input type="file" name="evidencias[]" id="evidencias" multiple accept="image/*,.pdf" 
                           class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-sm text-gray-400 mt-1">Formatos aceitos: JPG, PNG, GIF, PDF (máx. 5MB cada)</p>
                </div>

                <!-- Botões -->
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <span id="btnText">💾 Salvar Registro</span>
                    </button>
                    <button type="reset" onclick="limparFormulario()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        🔄 Limpar
                    </button>
                </div>
            </form>
        </div>

        <!-- Barra de Ações do Grid -->
        <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Busca Inteligente -->
                <div class="flex-1 flex gap-2">
                    <select id="searchColumn" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">Todas as colunas</option>
                        <option value="1">Número Registro</option>
                        <option value="3">Origem</option>
                        <option value="4">Cliente/Empresa</option>
                        <option value="5">Categoria</option>
                        <option value="8">Fornecedor</option>
                        <option value="9">Testes Realizados</option>
                        <option value="10">Ações Realizadas</option>
                        <option value="11">Conclusão</option>
                        <option value="12">Status</option>
                    </select>
                    <div class="flex-1 relative">
                        <input type="text" id="searchRC" placeholder="Digite para buscar..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="button" onclick="window.searchRC()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            🔍
                        </button>
                    </div>
                    <button type="button" onclick="window.clearSearch()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Limpar
                    </button>
                </div>

                <!-- Botões de Ação -->
                <div class="flex gap-2">
                    <button type="button" onclick="exportarSelecionados()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        📊 Exportar Selecionados
                    </button>
                    <button type="button" onclick="selecionarTodos()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        ✅ Selecionar Todos
                    </button>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600">
                <span id="resultsCount"></span>
            </div>
        </div>

        <!-- Grid de Registros -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nº Registro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Abertura</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origem</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente/Empresa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nº Série</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evidências</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Testes Realizados</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações Realizadas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conclusão</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="rcTbody" class="bg-white divide-y divide-gray-200">
                        <!-- Dados serão carregados via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

    <script>
        // Carregar data de hoje por padrão
        document.getElementById('data_abertura').valueAsDate = new Date();

        // Carregar registros ao iniciar
        document.addEventListener('DOMContentLoaded', () => {
            carregarRegistros();
        });

        // Função para carregar registros
        async function carregarRegistros() {
            try {
                const response = await fetch('/controle-rc/list');
                const data = await response.json();

                if (data.success) {
                    renderizarGrid(data.data);
                } else {
                    console.error(data.message);
                }
            } catch (error) {
                console.error('Erro ao carregar registros:', error);
            }
        }

        // Renderizar grid
        function renderizarGrid(registros) {
            const tbody = document.getElementById('rcTbody');
            tbody.innerHTML = '';

            if (registros.length === 0) {
                tbody.innerHTML = '<tr><td colspan="15" class="px-6 py-4 text-center text-gray-500">Nenhum registro encontrado</td></tr>';
                updateResultsCount(0, 0);
                return;
            }

            registros.forEach(reg => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                
                const dataFormatada = new Date(reg.data_abertura).toLocaleDateString('pt-BR');
                const evidencias = reg.total_evidencias > 0 
                    ? `<span class="text-blue-600">📎 ${reg.total_evidencias}</span>` 
                    : '<span class="text-gray-400">-</span>';
                
                // Função para truncar texto longo
                const truncateText = (text, maxLength = 50) => {
                    if (!text) return '-';
                    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
                };

                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" class="rc-checkbox" value="${reg.id}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">${reg.numero_registro}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${dataFormatada}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reg.origem}</td>
                    <td class="px-6 py-4">${reg.cliente_nome}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">${reg.categoria}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${reg.numero_serie || '-'}</td>
                    <td class="px-6 py-4">${reg.fornecedor_nome || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">${evidencias}</td>
                    <td class="px-6 py-4" title="${reg.testes_realizados || ''}">${truncateText(reg.testes_realizados)}</td>
                    <td class="px-6 py-4" title="${reg.acoes_realizadas || ''}">${truncateText(reg.acoes_realizadas)}</td>
                    <td class="px-6 py-4" title="${reg.conclusao || ''}">${truncateText(reg.conclusao)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <select onchange="alterarStatusDireto(${reg.id}, this.value)" 
                                data-status-anterior="${reg.status}"
                                class="status-select px-2 py-1 text-xs rounded-full border-0 ${getStatusColor(reg.status)} cursor-pointer">
                            <option value="Em analise" ${reg.status === 'Em analise' ? 'selected' : ''}>Em análise</option>
                            <option value="Aguardando ações do fornecedor" ${reg.status === 'Aguardando ações do fornecedor' ? 'selected' : ''}>Aguardando ações do fornecedor</option>
                            <option value="Aguardando retorno do produto" ${reg.status === 'Aguardando retorno do produto' ? 'selected' : ''}>Aguardando retorno do produto</option>
                            <option value="Finalizado" ${reg.status === 'Finalizado' ? 'selected' : ''}>Finalizado</option>
                            <option value="Concluída" ${reg.status === 'Concluída' ? 'selected' : ''}>Concluída</option>
                        </select>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${reg.usuario_nome}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex gap-2">
                            <button onclick="editarRegistro(${reg.id})" class="text-blue-600 hover:text-blue-800" title="Editar">
                                ✏️
                            </button>
                            <button onclick="imprimirRegistro(${reg.id})" class="text-green-600 hover:text-green-800" title="Imprimir">
                                🖨️
                            </button>
                            <button onclick="excluirRegistro(${reg.id})" class="text-red-600 hover:text-red-800" title="Excluir">
                                🗑️
                            </button>
                        </div>
                    </td>
                `;
                
                tbody.appendChild(tr);
            });

            updateResultsCount(registros.length, registros.length);
        }

        // Submit do formulário
        document.getElementById('formRC').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const isEdit = document.getElementById('rc_id').value !== '';
            const url = isEdit ? '/controle-rc/update' : '/controle-rc/create';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    limparFormulario();
                    carregarRegistros();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                alert('Erro ao salvar: ' + error.message);
            }
        });

        // Editar registro
        async function editarRegistro(id) {
            try {
                const response = await fetch(`/controle-rc/${id}`);
                const data = await response.json();

                if (data.success) {
                    const reg = data.data;
                    
                    document.getElementById('rc_id').value = reg.id;
                    document.getElementById('data_abertura').value = reg.data_abertura;
                    document.getElementById('origem').value = reg.origem;
                    document.getElementById('cliente_nome').value = reg.cliente_nome;
                    document.getElementById('categoria').value = reg.categoria;
                    document.getElementById('detalhamento').value = reg.detalhamento || '';
                    document.getElementById('qual_produto').value = reg.qual_produto || '';
                    document.getElementById('numero_serie').value = reg.numero_serie || '';
                    document.getElementById('fornecedor_id').value = reg.fornecedor_id || '';
                    document.getElementById('status').value = reg.status || 'Em analise';
                    document.getElementById('testes_realizados').value = reg.testes_realizados || '';
                    document.getElementById('acoes_realizadas').value = reg.acoes_realizadas || '';
                    document.getElementById('conclusao').value = reg.conclusao || '';

                    document.getElementById('formTitle').textContent = 'Editar Registro: ' + reg.numero_registro;
                    document.getElementById('btnText').textContent = '💾 Atualizar Registro';
                    document.getElementById('btnCancelar').classList.remove('hidden');

                    // Scroll para o formulário
                    document.getElementById('formRC').scrollIntoView({ behavior: 'smooth' });
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                alert('Erro ao carregar registro: ' + error.message);
            }
        }

        // Excluir registro
        async function excluirRegistro(id) {
            if (!confirm('Tem certeza que deseja excluir este registro?')) {
                return;
            }

            try {
                const response = await fetch('/controle-rc/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    carregarRegistros();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                alert('Erro ao excluir: ' + error.message);
            }
        }

        // Imprimir registro
        function imprimirRegistro(id) {
            window.open(`/controle-rc/${id}/print`, '_blank');
        }

        // Cancelar edição
        function cancelarFormulario() {
            limparFormulario();
        }

        // Limpar formulário
        function limparFormulario() {
            document.getElementById('formRC').reset();
            document.getElementById('rc_id').value = '';
            document.getElementById('formTitle').textContent = 'Novo Registro de RC';
            document.getElementById('btnText').textContent = '💾 Salvar Registro';
            document.getElementById('btnCancelar').classList.add('hidden');
            document.getElementById('data_abertura').valueAsDate = new Date();
        }

        // Toggle selecionar todos
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.rc-checkbox');
            const selectAll = document.getElementById('selectAll').checked;
            checkboxes.forEach(cb => cb.checked = selectAll);
        }

        // Selecionar todos visíveis
        function selecionarTodos() {
            const checkboxes = document.querySelectorAll('.rc-checkbox');
            const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
                const row = cb.closest('tr');
                return row.style.display !== 'none';
            });
            
            const allChecked = visibleCheckboxes.every(cb => cb.checked);
            visibleCheckboxes.forEach(cb => cb.checked = !allChecked);
        }

        // Exportar selecionados
        async function exportarSelecionados() {
            const checkboxes = document.querySelectorAll('.rc-checkbox:checked');
            
            if (checkboxes.length === 0) {
                alert('Selecione pelo menos um registro para exportar');
                return;
            }

            const ids = Array.from(checkboxes).map(cb => cb.value);

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/controle-rc/export';
            
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // BUSCA INTELIGENTE
        window.searchRC = function() {
            const searchInput = document.getElementById('searchRC');
            const searchColumn = document.getElementById('searchColumn');
            const tbody = document.getElementById('rcTbody');
            
            const searchTerm = searchInput.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            const column = searchColumn.value;
            const rows = tbody.getElementsByTagName('tr');
            
            let visibleCount = 0;
            const totalRows = rows.length;

            if (searchTerm === '') {
                for (let row of rows) {
                    row.style.display = '';
                    visibleCount++;
                }
                updateResultsCount(visibleCount, totalRows);
                return;
            }

            const searchTerms = searchTerm.split(' ').filter(t => t.length > 0);

            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                
                if (cells.length === 0) continue;

                let textToSearch = '';
                
                if (column === 'all') {
                    for (let i = 1; i < cells.length - 1; i++) {
                        textToSearch += cells[i].textContent + ' ';
                    }
                } else {
                    const colIndex = parseInt(column);
                    textToSearch = cells[colIndex]?.textContent || '';
                }

                const normalizedText = textToSearch.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                const matches = searchTerms.every(term => normalizedText.includes(term));

                if (matches) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            if (visibleCount === 0) {
                tbody.innerHTML = '<tr><td colspan="15" class="px-6 py-4 text-center text-gray-500">🔍 Nenhum resultado encontrado para "' + searchInput.value + '"</td></tr>';
            }

            updateResultsCount(visibleCount, totalRows);
        };

        window.clearSearch = function() {
            document.getElementById('searchRC').value = '';
            document.getElementById('searchColumn').value = 'all';
            window.searchRC();
        };

        function updateResultsCount(visible, total) {
            const counter = document.getElementById('resultsCount');
            counter.textContent = `Mostrando ${visible} de ${total} registros`;
        }

        // Função para definir cores do status
        function getStatusColor(status) {
            switch(status) {
                case 'Em analise':
                    return 'bg-yellow-100 text-yellow-800';
                case 'Aguardando ações do fornecedor':
                    return 'bg-orange-100 text-orange-800';
                case 'Aguardando retorno do produto':
                    return 'bg-blue-100 text-blue-800';
                case 'Finalizado':
                    return 'bg-purple-100 text-purple-800';
                case 'Concluída':
                    return 'bg-green-100 text-green-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        // Função para alterar status diretamente no dropdown
        async function alterarStatusDireto(id, novoStatus) {
            try {
                // Mostrar loading no dropdown
                const select = event.target;
                const statusAnterior = select.dataset.statusAnterior || select.value;
                select.disabled = true;
                select.style.opacity = '0.6';

                const response = await fetch('/controle-rc/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&status=${encodeURIComponent(novoStatus)}`
                });

                const data = await response.json();

                if (data.success) {
                    // Mostrar notificação de sucesso
                    mostrarNotificacao('Status atualizado com sucesso!', 'success');
                    
                    // Atualizar a cor do dropdown
                    select.className = `px-2 py-1 text-xs rounded-full border-0 ${getStatusColor(novoStatus)} cursor-pointer hover:opacity-80 focus:ring-2 focus:ring-blue-500`;
                    select.dataset.statusAnterior = novoStatus;
                } else {
                    // Reverter para status anterior em caso de erro
                    select.value = statusAnterior;
                    mostrarNotificacao('Erro: ' + data.message, 'error');
                }
            } catch (error) {
                // Reverter para status anterior em caso de erro
                select.value = statusAnterior;
                mostrarNotificacao('Erro ao alterar status: ' + error.message, 'error');
            } finally {
                // Remover loading
                select.disabled = false;
                select.style.opacity = '1';
            }
        }

        // Função para mostrar notificações
        function mostrarNotificacao(mensagem, tipo = 'info') {
            // Remover notificação anterior se existir
            const notificacaoExistente = document.getElementById('notificacao-status');
            if (notificacaoExistente) {
                notificacaoExistente.remove();
            }

            // Criar nova notificação
            const notificacao = document.createElement('div');
            notificacao.id = 'notificacao-status';
            notificacao.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 ${
                tipo === 'success' ? 'bg-green-500 text-white' : 
                tipo === 'error' ? 'bg-red-500 text-white' : 
                'bg-blue-500 text-white'
            }`;
            notificacao.textContent = mensagem;

            document.body.appendChild(notificacao);

            // Remover após 3 segundos
            setTimeout(() => {
                notificacao.style.opacity = '0';
                notificacao.style.transform = 'translateX(100%)';
                setTimeout(() => notificacao.remove(), 300);
            }, 3000);
        }

        // Event listeners para busca em tempo real
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchRC');
            const searchColumn = document.getElementById('searchColumn');

            let debounceTimer;
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => window.searchRC(), 150);
            });

            searchInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    clearTimeout(debounceTimer);
                    window.searchRC();
                }
            });

            searchColumn.addEventListener('change', () => window.searchRC());
        });
    </script>
