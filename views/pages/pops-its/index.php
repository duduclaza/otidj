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
            <h1 class="text-3xl font-bold text-gray-900">📚 POPs e ITs</h1>
            <p class="text-gray-600 mt-2">Procedimentos Operacionais Padrão e Instruções de Trabalho</p>
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
                            <!-- Tipo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                                <select name="tipo" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Selecione o tipo...</option>
                                    <option value="POP">POP - Procedimento Operacional Padrão</option>
                                    <option value="IT">IT - Instrução de Trabalho</option>
                                </select>
                            </div>

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
                        </div>

                        <!-- Título -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Título do POP/IT *</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="titulo" 
                                    id="tituloInput"
                                    required 
                                    maxlength="255"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                    placeholder="Digite o título do procedimento..."
                                    autocomplete="off"
                                >
                                <!-- Lista de sugestões -->
                                <div id="tituloSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 hidden max-h-48 overflow-y-auto">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">O sistema verificará automaticamente se já existe um título similar</p>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
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

            <!-- ABA 2: MEUS REGISTROS -->
            <?php if ($canViewMeusRegistros): ?>
            <div id="content-registros" class="tab-content hidden">
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Meus Registros</h3>
                    <p class="text-gray-600 mb-4">Em construção</p>
                    <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Funcionalidade em desenvolvimento
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ABA 3: PENDENTE APROVAÇÃO (Apenas Admin) -->
            <?php if ($canViewPendenteAprovacao): ?>
            <div id="content-pendentes" class="tab-content hidden">
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Pendente Aprovação</h3>
                    <p class="text-gray-600 mb-4">Em construção</p>
                    <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Funcionalidade em desenvolvimento
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 rounded-full">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Acesso restrito a administradores
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ABA 4: VISUALIZAÇÃO -->
            <?php if ($canViewVisualizacao): ?>
            <div id="content-visualizacao" class="tab-content hidden">
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Visualização</h3>
                    <p class="text-gray-600 mb-4">Em construção</p>
                    <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Funcionalidade em desenvolvimento
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mensagem caso não tenha permissão para nenhuma aba -->
            <?php if (!$canViewCadastroTitulos && !$canViewMeusRegistros && !$canViewPendenteAprovacao && !$canViewVisualizacao): ?>
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
                
                // Carregar dados da aba se for cadastro
                if (tabId === 'cadastro') {
                    console.log('🔄 Carregando títulos ao clicar na aba...');
                    loadTitulos();
                }
            }
        });
    });
    
    // Ativar primeira aba disponível se nenhuma estiver ativa
    const firstTab = document.querySelector('.tab-button');
    if (firstTab && !document.querySelector('.tab-button.active')) {
        firstTab.click();
    }
    
    // Carregar dados da primeira aba ativa imediatamente (após um pequeno delay para garantir que a aba foi ativada)
    setTimeout(() => {
        const activeTab = document.querySelector('.tab-button.active');
        if (activeTab) {
            const tabId = activeTab.id.replace('tab-', '');
            console.log('🎯 Aba ativa detectada:', tabId);
            if (tabId === 'cadastro') {
                console.log('📋 Carregando títulos da aba ativa...');
                loadTitulos();
            }
        }
    }, 100);
    
    // Configurar autocomplete para títulos
    setupTituloAutocomplete();
    
    // Configurar formulário de cadastro
    setupFormularioCadastro();
    
    // Fallback: garantir que os títulos sejam carregados se a aba de cadastro estiver visível
    setTimeout(() => {
        const cadastroContent = document.getElementById('content-cadastro');
        if (cadastroContent && !cadastroContent.classList.contains('hidden')) {
            console.log('🔄 Fallback: Carregando títulos...');
            loadTitulos();
        }
    }, 500);
});

// Autocomplete para títulos
function setupTituloAutocomplete() {
    const input = document.getElementById('tituloInput');
    const suggestions = document.getElementById('tituloSuggestions');
    const tipoSelect = document.querySelector('select[name="tipo"]');
    
    if (!input || !suggestions) return;
    
    let timeout;
    
    input.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestions.classList.add('hidden');
            return;
        }
        
        timeout = setTimeout(() => {
            const tipo = tipoSelect.value;
            searchTitulos(query, tipo);
        }, 300);
    });
    
    // Fechar sugestões ao clicar fora
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.classList.add('hidden');
        }
    });
}

async function searchTitulos(query, tipo = '') {
    try {
        const url = `/pops-its/titulos/search?q=${encodeURIComponent(query)}&tipo=${encodeURIComponent(tipo)}`;
        const response = await fetch(url);
        const result = await response.json();
        
        const suggestions = document.getElementById('tituloSuggestions');
        
        if (result.success && result.data.length > 0) {
            suggestions.innerHTML = result.data.map(item => `
                <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" 
                     onclick="selectTitulo('${item.titulo}', '${item.tipo}')">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900">${item.titulo}</span>
                        <span class="text-xs px-2 py-1 rounded-full ${item.tipo === 'POP' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}">${item.tipo}</span>
                    </div>
                </div>
            `).join('');
            suggestions.classList.remove('hidden');
        } else {
            suggestions.classList.add('hidden');
        }
    } catch (error) {
        console.error('Erro na busca:', error);
    }
}

function selectTitulo(titulo, tipo) {
    document.getElementById('tituloInput').value = titulo;
    document.querySelector('select[name="tipo"]').value = tipo;
    document.getElementById('tituloSuggestions').classList.add('hidden');
}

// Configurar formulário de cadastro
function setupFormularioCadastro() {
    const form = document.getElementById('formCadastroTitulo');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Desabilitar botão durante envio
        submitBtn.disabled = true;
        submitBtn.textContent = 'Cadastrando...';
        
        try {
            const response = await fetch('/pops-its/titulo/create', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ ' + result.message);
                form.reset();
                loadTitulos(); // Recarregar lista
            } else {
                alert('❌ ' + result.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao cadastrar título');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Cadastrar Título';
        }
    });
}

// Carregar lista de títulos
async function loadTitulos() {
    try {
        console.log('🔄 Carregando títulos...');
        const response = await fetch('/pops-its/titulos/list');
        console.log('📡 Response status:', response.status);
        
        const result = await response.json();
        console.log('📊 Resultado:', result);
        
        const tbody = document.getElementById('listaTitulos');
        
        if (result.success && result.data.length > 0) {
            // Verificar se usuário é admin (baseado na presença da aba pendente aprovação)
            const isAdmin = document.getElementById('tab-pendentes') !== null;
            
            tbody.innerHTML = result.data.map(titulo => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${titulo.tipo === 'POP' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}">
                            ${titulo.tipo}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">${titulo.titulo}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${titulo.departamento_nome || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${titulo.criador_nome || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${formatDate(titulo.criado_em)}
                    </td>
                    ${isAdmin ? `
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button onclick="excluirTitulo(${titulo.id}, '${titulo.titulo.replace(/'/g, "\\'")}', '${titulo.tipo}')" 
                                class="text-red-600 hover:text-red-900 hover:bg-red-50 px-2 py-1 rounded transition-colors"
                                title="Excluir título">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                    ` : ''}
                </tr>
            `).join('');
        } else {
            // Verificar se usuário é admin para ajustar colspan
            const isAdmin = document.getElementById('tab-pendentes') !== null;
            const colspan = isAdmin ? 6 : 5;
            
            tbody.innerHTML = `
                <tr>
                    <td colspan="${colspan}" class="px-6 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">Nenhum título cadastrado</p>
                            <p class="text-gray-500">Comece cadastrando o primeiro título usando o formulário acima</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar títulos:', error);
        // Verificar se usuário é admin para ajustar colspan
        const isAdmin = document.getElementById('tab-pendentes') !== null;
        const colspan = isAdmin ? 6 : 5;
        
        document.getElementById('listaTitulos').innerHTML = `
            <tr>
                <td colspan="${colspan}" class="px-6 py-4 text-center text-red-500">
                    Erro ao carregar títulos
                </td>
            </tr>
        `;
    }
}

function limparFormulario() {
    document.getElementById('formCadastroTitulo').reset();
    document.getElementById('tituloSuggestions').classList.add('hidden');
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}


// Excluir título (apenas admin)
async function excluirTitulo(id, titulo, tipo) {
    // Confirmação dupla para segurança
    const confirmacao1 = confirm(`⚠️ Tem certeza que deseja excluir o ${tipo}:\n"${titulo}"?\n\nEsta ação não pode ser desfeita.`);
    
    if (!confirmacao1) return;
    
    const confirmacao2 = confirm(`🔴 CONFIRMAÇÃO FINAL\n\nVocê está prestes a excluir permanentemente:\n${tipo}: "${titulo}"\n\nDigite OK para confirmar ou Cancelar para abortar.`);
    
    if (!confirmacao2) return;
    
    try {
        const formData = new FormData();
        formData.append('titulo_id', id);
        
        const response = await fetch('/pops-its/titulo/delete', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            loadTitulos(); // Recarregar lista
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao excluir título:', error);
        alert('❌ Erro ao excluir título');
    }
}
</script>




