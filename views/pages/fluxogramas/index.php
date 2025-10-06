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
            <h1 class="text-3xl font-bold text-gray-900">📊 Fluxogramas</h1>
            <p class="text-gray-600 mt-2">Gestão de Fluxogramas e Processos</p>
        </div>
    </div>

    <!-- Sistema de Abas -->
    <div class="bg-white rounded-lg shadow-sm border">
        <!-- Navegação das Abas -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <!-- Aba 1: Cadastro de Títulos -->
                <?php if ($canViewCadastroTitulos): ?>
                <button id="tab-cadastro" class="tab-button active border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Cadastro de Títulos
                </button>
                <?php endif; ?>

                <!-- Aba 2: Meus Registros -->
                <?php if ($canViewMeusRegistros): ?>
                <button id="tab-registros" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Meus Registros
                </button>
                <?php endif; ?>

                <!-- Aba 3: Pendente Aprovação (Apenas Admin) -->
                <?php if ($canViewPendenteAprovacao): ?>
                <button id="tab-pendentes" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Pendente Aprovação
                </button>
                <?php endif; ?>

                <!-- Aba 4: Visualização -->
                <?php if ($canViewVisualizacao): ?>
                <button id="tab-visualizacao" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Visualização
                </button>
                <?php endif; ?>

                <!-- Aba 5: Log de Visualizações (Apenas Admin) -->
                <?php if ($canViewLogsVisualizacao): ?>
                <button id="tab-logs" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Log de Visualizações
                </button>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="p-6">
            
            <!-- ABA 1: CADASTRO DE TÍTULOS -->
            <?php if ($canViewCadastroTitulos): ?>
            <div id="content-cadastro" class="tab-content">
                <!-- Formulário de Cadastro -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 Cadastrar Novo Título</h3>
                    
                    <form id="formCadastroTitulo" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Departamento -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departamento *</label>
                                <select name="departamento_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Selecione o departamento...</option>
                                    <?php if (isset($departamentos)): ?>
                                        <?php foreach ($departamentos as $dept): ?>
                                            <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Título -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Título do Fluxograma *</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        name="titulo" 
                                        id="tituloInput"
                                        required 
                                        maxlength="255"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                        placeholder="Digite o título do fluxograma..."
                                        autocomplete="off"
                                    >
                                    <!-- Lista de sugestões -->
                                    <div id="tituloSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 hidden max-h-48 overflow-y-auto">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">O sistema verificará automaticamente se já existe um título similar</p>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="limparFormulario()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                                Limpar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                Cadastrar Título
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de Títulos Cadastrados -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-900">📋 Títulos Cadastrados</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado por</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <?php if ($canViewPendenteAprovacao): // Apenas admin pode excluir ?>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="listaTitulos" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        <div class="flex items-center justify-center">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Carregando títulos...
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mensagem caso não tenha permissão para nenhuma aba -->
            <?php if (!$canViewCadastroTitulos && !$canViewMeusRegistros && !$canViewPendenteAprovacao && !$canViewVisualizacao && !$canViewLogsVisualizacao): ?>
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Acesso Negado</h3>
                <p class="text-gray-600 mb-4">Você não possui permissão para acessar nenhuma funcionalidade deste módulo.</p>
                <p class="text-sm text-gray-500">Entre em contato com o administrador para solicitar acesso.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Sistema de Abas
document.addEventListener('DOMContentLoaded', function() {
    console.log('📊 Módulo Fluxogramas carregado');
    
    // Configurar abas
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.id.replace('tab-', '');
            
            // Remover classe ativa de todas as abas
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Adicionar classe ativa na aba clicada
            button.classList.add('active', 'border-blue-500', 'text-blue-600');
            button.classList.remove('border-transparent', 'text-gray-500');
            
            // Esconder todos os conteúdos
            tabContents.forEach(content => content.classList.add('hidden'));
            
            // Mostrar conteúdo da aba ativa
            const activeContent = document.getElementById(`content-${tabId}`);
            if (activeContent) {
                activeContent.classList.remove('hidden');
                
                // Carregar dados da aba
                if (tabId === 'cadastro') {
                    console.log('🔄 Carregando títulos...');
                    loadTitulos();
                }
            }
        });
    });
    
    // Ativar primeira aba disponível
    const firstTab = document.querySelector('.tab-button');
    if (firstTab && !document.querySelector('.tab-button.active')) {
        firstTab.click();
    }
    
    // Carregar dados iniciais
    setTimeout(() => {
        const activeTab = document.querySelector('.tab-button.active');
        if (activeTab) {
            const tabId = activeTab.id.replace('tab-', '');
            if (tabId === 'cadastro') {
                loadTitulos();
            }
        }
    }, 100);
    
    // Configurar formulário
    setupFormularioCadastro();
});

// Configurar formulário de cadastro
function setupFormularioCadastro() {
    const form = document.getElementById('formCadastroTitulo');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('/fluxogramas/titulo/create', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ Título cadastrado com sucesso!');
                form.reset();
                loadTitulos();
            } else {
                alert('❌ Erro: ' + (result.message || 'Não foi possível cadastrar o título'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao cadastrar título');
        }
    });
}

// Limpar formulário
function limparFormulario() {
    document.getElementById('formCadastroTitulo').reset();
}

// Carregar títulos
async function loadTitulos() {
    try {
        const response = await fetch('/fluxogramas/titulos/list');
        const result = await response.json();
        
        const tbody = document.getElementById('listaTitulos');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => `
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">${item.titulo}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${item.departamento_nome}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${item.criado_por_nome}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${formatDate(item.criado_em)}</td>
                    <?php if ($canViewPendenteAprovacao): ?>
                    <td class="px-6 py-4 text-sm">
                        <button onclick="deleteTitulo(${item.id})" class="text-red-600 hover:text-red-900">
                            🗑️ Excluir
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        Nenhum título cadastrado
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar títulos:', error);
    }
}

// Excluir título
async function deleteTitulo(id) {
    if (!confirm('Deseja realmente excluir este título?')) return;
    
    try {
        const response = await fetch(`/fluxogramas/titulo/${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Título excluído com sucesso!');
            loadTitulos();
        } else {
            alert('❌ Erro: ' + (result.message || 'Não foi possível excluir'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao excluir título');
    }
}

// Formatar data
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
}
</script>
