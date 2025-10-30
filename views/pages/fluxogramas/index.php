<?php
// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
?>

<script>
// Disponibilizar departamentos globalmente para JavaScript
const departamentos = <?= json_encode($departamentos ?? []) ?>;
</script>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🔄 Fluxogramas</h1>
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
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-gray-900">📋 Títulos Cadastrados</h4>
                            <!-- Busca Inteligente -->
                            <div class="flex items-center space-x-2">
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="buscaTitulosCadastro"
                                        placeholder="🔍 Buscar título..."
                                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onkeyup="filtrarTitulosCadastro()"
                                    >
                                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
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
                                    <td colspan="<?= $canViewPendenteAprovacao ? 6 : 5 ?>" class="px-6 py-4 text-center text-gray-500">
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
                <!-- Formulário de Registro -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📄 Criar Novo Registro</h3>
                    
                    <form id="formCriarRegistro" class="space-y-4" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Título -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Título *</label>
                                <select name="titulo_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Selecione um título...</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">O sistema definirá automaticamente a próxima versão</p>
                            </div>

                            <!-- Arquivo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo * (PNG, JPEG, PDF - Max 10MB)</label>
                                <input type="file" name="arquivo" required accept=".pdf,.png,.jpg,.jpeg" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, PNG, JPEG</p>
                            </div>
                        </div>

                        <!-- Visibilidade -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Visibilidade *</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="visibilidade" value="publico" class="mr-2">
                                    <span class="text-sm">📢 Público (todos os usuários podem visualizar)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="visibilidade" value="departamentos" checked class="mr-2">
                                    <span class="text-sm">🏢 Departamentos específicos</span>
                                </label>
                            </div>
                        </div>

                        <!-- Departamentos Permitidos -->
                        <div id="departamentosSection" class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Departamentos Permitidos</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-32 overflow-y-auto border border-gray-200 rounded p-3 bg-white">
                                <?php if (isset($departamentos)): ?>
                                    <?php foreach ($departamentos as $dept): ?>
                                    <label class="flex items-center text-sm">
                                        <input type="checkbox" name="departamentos_permitidos[]" value="<?= $dept['id'] ?>" class="mr-2">
                                        <?= e($dept['nome']) ?>
                                    </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500">Selecione os departamentos que poderão visualizar este registro</p>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="limparFormularioRegistro()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                                Limpar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                📝 Registrar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de Meus Registros -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-900">📋 Meus Registros</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Versão</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arquivo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visibilidade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="listaMeusRegistros" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        <div class="flex items-center justify-center">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Carregando registros...
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ABA 3: PENDENTE APROVAÇÃO (Apenas Admin) -->
            <?php if ($canViewPendenteAprovacao): ?>
            <div id="content-pendentes" class="tab-content hidden">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Registros Pendentes de Aprovação</h3>
                        <p class="mt-1 text-sm text-gray-500">Gerencie os registros que aguardam aprovação</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Versão</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Autor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anexo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="listaPendentes" class="bg-white divide-y divide-gray-200">
                                <!-- Conteúdo carregado via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Seção de Solicitações de Exclusão -->
                <div class="bg-white shadow rounded-lg mt-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Solicitações de Exclusão</h3>
                        <p class="mt-1 text-sm text-gray-500">Gerencie as solicitações de exclusão de registros</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocolo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registro</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="listaSolicitacoes" class="bg-white divide-y divide-gray-200">
                                <!-- Conteúdo carregado via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ABA 4: VISUALIZAÇÃO -->
            <?php if ($canViewVisualizacao): ?>
            <div id="content-visualizacao" class="tab-content hidden">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Registros Aprovados</h3>
                                <p class="mt-1 text-sm text-gray-500">Visualize e acesse os registros aprovados</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <!-- Filtro por Departamento -->
                                <select 
                                    id="filtroDepartamentoVisualizacao"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="filtrarVisualizacao()"
                                >
                                    <option value="">📁 Todos os Departamentos</option>
                                    <?php foreach ($departamentos as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept['nome']) ?>"><?= htmlspecialchars($dept['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <!-- Busca Inteligente -->
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="buscaVisualizacao"
                                        placeholder="🔍 Buscar por título, versão ou autor..."
                                        class="pl-10 pr-4 py-2 w-80 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onkeyup="filtrarVisualizacao()"
                                    >
                                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Versão</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Autor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprovado em</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visibilidade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="listaVisualizacao" class="bg-white divide-y divide-gray-200">
                                <!-- Conteúdo carregado via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ABA 5: LOG DE VISUALIZAÇÕES (Apenas Admin) -->
            <?php if ($canViewLogsVisualizacao): ?>
            <div id="content-logs" class="tab-content hidden">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Log de Visualizações</h3>
                        <p class="mt-1 text-sm text-gray-500">Histórico de acessos aos documentos</p>
                        
                        <!-- Filtros de Busca -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <input type="text" id="searchLogs" placeholder="Buscar usuário, documento..." 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <input type="date" id="dataInicio" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <input type="date" id="dataFim" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="filtrarLogs()" 
                                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                                    Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Versão</th>
                                </tr>
                            </thead>
                            <tbody id="listaLogs" class="bg-white divide-y divide-gray-200">
                                <!-- Conteúdo carregado via JavaScript -->
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
                    console.log('🔄 Carregando títulos ao clicar na aba...');
                    loadTitulos();
                } else if (tabId === 'registros') {
                    console.log('🔄 Carregando registros ao clicar na aba...');
                    loadMeusRegistros();
                    loadTitulosDropdown();
                } else if (tabId === 'pendentes') {
                    console.log('🔄 Carregando pendências ao clicar na aba...');
                    loadPendentesAprovacao();
                    loadSolicitacoes();
                } else if (tabId === 'visualizacao') {
                    console.log('🔄 Carregando visualização ao clicar na aba...');
                    loadVisualizacao();
                } else if (tabId === 'logs') {
                    console.log('🔄 Carregando logs ao clicar na aba...');
                    loadLogsVisualizacao();
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
    
    // Configurar formulário de registros
    setupFormularioRegistros();
    
    // Fallback: garantir que os dados sejam carregados se alguma aba estiver visível
    setTimeout(() => {
        const cadastroContent = document.getElementById('content-cadastro');
        const registrosContent = document.getElementById('content-registros');
        
        if (cadastroContent && !cadastroContent.classList.contains('hidden')) {
            console.log('🔄 Fallback: Carregando títulos...');
            loadTitulos();
        } else if (registrosContent && !registrosContent.classList.contains('hidden')) {
            console.log('🔄 Fallback: Carregando registros...');
            loadMeusRegistros();
            loadTitulosDropdown();
        }
    }, 500);
});

// Autocomplete para títulos
function setupTituloAutocomplete() {
    const input = document.getElementById('tituloInput');
    const suggestions = document.getElementById('tituloSuggestions');
    
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
            searchTitulos(query);
        }, 300);
    });
    
    // Fechar sugestões ao clicar fora
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.classList.add('hidden');
        }
    });
}

async function searchTitulos(query) {
    try {
        const url = `/fluxogramas/titulos/search?q=${encodeURIComponent(query)}`;
        const response = await fetch(url);
        const result = await response.json();
        
        const suggestions = document.getElementById('tituloSuggestions');
        
        if (result.success && result.data.length > 0) {
            suggestions.innerHTML = result.data.map(item => `
                <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" 
                     onclick="selectTitulo('${item.titulo}')">
                    <span class="text-sm text-gray-900">${item.titulo}</span>
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

function selectTitulo(titulo) {
    document.getElementById('tituloInput').value = titulo;
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
            const response = await fetch('/fluxogramas/titulo/create', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ ' + result.message);
                form.reset();
                loadTitulos(); // Recarregar lista
                loadTitulosDropdown(); // Atualizar dropdown na aba registros
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

// Configurar formulário de registros
function setupFormularioRegistros() {
    const form = document.getElementById('formCriarRegistro');
    if (!form) return;
    
    // Configurar toggle de visibilidade
    const radioButtons = form.querySelectorAll('input[name="visibilidade"]');
    const departamentosSection = document.getElementById('departamentosSection');
    
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'publico') {
                departamentosSection.style.display = 'none';
            } else {
                departamentosSection.style.display = 'block';
            }
        });
    });
    
    // Configurar submissão do formulário
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Validar se pelo menos um departamento foi selecionado (se não for público)
        const visibilidade = formData.get('visibilidade');
        if (visibilidade === 'departamentos') {
            const departamentosSelecionados = formData.getAll('departamentos_permitidos[]');
            if (departamentosSelecionados.length === 0) {
                alert('❌ Selecione pelo menos um departamento ou escolha visibilidade pública');
                return;
            }
        }
        
        // Desabilitar botão durante envio
        submitBtn.disabled = true;
        submitBtn.textContent = 'Registrando...';
        
        try {
            const response = await fetch('/fluxogramas/registro/create', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ ' + result.message);
                form.reset();
                // Resetar visibilidade para departamentos
                document.querySelector('input[name="visibilidade"][value="departamentos"]').checked = true;
                departamentosSection.style.display = 'block';
                loadMeusRegistros(); // Recarregar lista
            } else {
                alert('❌ ' + result.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao criar registro');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = '📝 Registrar';
        }
    });
}

// Carregar lista de títulos
async function loadTitulos() {
    try {
        console.log('🔄 Carregando títulos...');
        const response = await fetch('/fluxogramas/titulos/list');
        console.log('📡 Response status:', response.status);
        
        const result = await response.json();
        console.log('📊 Resultado:', result);
        
        const tbody = document.getElementById('listaTitulos');
        
        if (result.success && result.data.length > 0) {
            // Verificar se usuário é admin (baseado na presença da aba pendente aprovação)
            const isAdmin = document.getElementById('tab-pendentes') !== null;
            
            tbody.innerHTML = result.data.map(titulo => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        Fluxograma
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">${titulo.titulo}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${titulo.departamento_nome || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${titulo.criado_por_nome || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${formatDate(titulo.criado_em)}
                    </td>
                    ${isAdmin ? `
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button onclick="excluirTitulo(${titulo.id}, '${titulo.titulo.replace(/'/g, "\\'")}')" 
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
            // Verificar se usuário é admin para ajustar colspan (5 colunas base + 1 ações se admin)
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
        // Verificar se usuário é admin para ajustar colspan (5 colunas base + 1 ações se admin)
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

function limparFormularioRegistro() {
    const form = document.getElementById('formCriarRegistro');
    form.reset();
    // Resetar visibilidade para departamentos
    document.querySelector('input[name="visibilidade"][value="departamentos"]').checked = true;
    document.getElementById('departamentosSection').style.display = 'block';
}

// Carregar títulos para dropdown
async function loadTitulosDropdown() {
    try {
        const response = await fetch('/fluxogramas/titulos/list');
        const result = await response.json();
        
        const select = document.querySelector('#formCriarRegistro select[name="titulo_id"]');
        if (!select) return;
        
        select.innerHTML = '<option value="">Selecione um título...</option>';
        
        if (result.success && result.data.length > 0) {
            result.data.forEach(titulo => {
                const option = document.createElement('option');
                option.value = titulo.id;
                option.textContent = `${titulo.titulo} (${titulo.departamento_nome || 'N/A'})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar títulos para dropdown:', error);
    }
}

// Carregar meus registros
async function loadMeusRegistros() {
    try {
        console.log('🔄 Carregando meus registros...');
        const response = await fetch('/fluxogramas/registros/meus');
        const result = await response.json();
        
        const tbody = document.getElementById('listaMeusRegistros');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(registro => {
                const statusColor = getStatusColor(registro.status);
                const statusText = getStatusText(registro.status);
                
                // Tratar departamentos_permitidos (pode ser string ou array)
                let departamentosTexto = 'Departamentos';
                if (registro.departamentos_permitidos) {
                    if (Array.isArray(registro.departamentos_permitidos)) {
                        departamentosTexto = registro.departamentos_permitidos.join(', ');
                    } else if (typeof registro.departamentos_permitidos === 'string') {
                        departamentosTexto = registro.departamentos_permitidos;
                    }
                }
                
                const visibilidade = registro.publico ? 'Público' : departamentosTexto;
                
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">${registro.titulo || 'N/A'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            v${registro.versao}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusColor}">
                                ${statusText}
                            </span>
                            ${registro.observacao_reprovacao ? `<div class="text-xs text-red-600 mt-1">${registro.observacao_reprovacao}</div>` : ''}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${registro.nome_arquivo}</div>
                            <div class="text-xs text-gray-500">${registro.extensao.toUpperCase()} - ${formatFileSize(registro.tamanho_arquivo)}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${registro.publico ? '🌍 Público' : '🏢 Restrito'}</div>
                            ${!registro.publico && registro.departamentos_permitidos ? 
                                `<div class="text-xs text-gray-500">${departamentosTexto}</div>` : ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${formatDate(registro.criado_em)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <button onclick="baixarFluxograma(${registro.id})" 
                                    class="text-green-600 hover:text-green-900 hover:bg-green-50 px-2 py-1 rounded"
                                    title="Baixar arquivo">
                                📥 Baixar
                            </button>
                            ${registro.status === 'REPROVADO' ? 
                                `<button onclick="editarRegistro(${registro.id})" 
                                         class="text-green-600 hover:text-green-900 hover:bg-green-50 px-2 py-1 rounded"
                                         title="Editar registro reprovado">
                                    ✏️
                                 </button>` : ''}
                            <button onclick="solicitarExclusao(${registro.id}, '${registro.titulo}', '${registro.nome_arquivo}')" 
                                    class="text-red-600 hover:text-red-900 hover:bg-red-50 px-2 py-1 rounded"
                                    title="Solicitar exclusão">
                                🗑️
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">Nenhum registro encontrado</p>
                            <p class="text-gray-500">Crie seu primeiro registro usando o formulário acima</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar registros:', error);
        document.getElementById('listaMeusRegistros').innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-red-500">
                    Erro ao carregar registros
                </td>
            </tr>
        `;
    }
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

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getStatusColor(status) {
    switch(status) {
        case 'PENDENTE': return 'bg-yellow-100 text-yellow-800';
        case 'APROVADO': return 'bg-green-100 text-green-800';
        case 'REPROVADO': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'PENDENTE': return 'Pendente';
        case 'APROVADO': return 'Aprovado';
        case 'REPROVADO': return 'Reprovado';
        default: return status;
    }
}

// Funções de ação para registros
async function baixarFluxograma(registroId) {
    try {
        // Abrir em nova aba para download
        window.open(`/fluxogramas/arquivo/${registroId}`, '_blank');
    } catch (error) {
        console.error('Erro ao baixar:', error);
        alert('Erro ao baixar arquivo');
    }
}

async function downloadArquivo(registroId) {
    try {
        window.open(`/pops-its/arquivo/${registroId}`, '_blank');
    } catch (error) {
        console.error('Erro ao baixar arquivo:', error);
        alert('❌ Erro ao baixar arquivo');
    }
}

function editarRegistro(registroId) {
    // Criar modal de edição
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.id = 'modalEdicao';
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-medium text-gray-900">📝 Editar Registro Reprovado</h3>
                <button onclick="fecharModalEdicao()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="formEdicaoRegistro" enctype="multipart/form-data" class="p-6">
                <input type="hidden" name="registro_id" value="${registroId}">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Novo Arquivo * (PDF, PNG, JPEG - Max 10MB)
                    </label>
                    <input type="file" name="arquivo" required 
                           accept=".pdf,.png,.jpg,.jpeg" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Substitua o arquivo reprovado por uma nova versão</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalEdicao()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                        💾 Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Configurar submissão do formulário
    const form = document.getElementById('formEdicaoRegistro');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Desabilitar botão durante envio
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Salvando...';
        
        try {
            const response = await fetch('/fluxogramas/registro/editar', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ ' + result.message);
                fecharModalEdicao();
                loadMeusRegistros(); // Recarregar lista
            } else {
                alert('❌ ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao editar registro:', error);
            alert('❌ Erro ao editar registro');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '💾 Salvar Alterações';
        }
    });
}

function fecharModalEdicao() {
    const modal = document.getElementById('modalEdicao');
    if (modal) {
        modal.remove();
        document.body.style.overflow = 'auto';
    }
}

// Solicitar exclusão de registro
async function solicitarExclusao(registroId, titulo, nomeArquivo) {
    // Modal de confirmação com campo de motivo
    const motivo = prompt(`🗑️ SOLICITAÇÃO DE EXCLUSÃO\n\nVocê está solicitando a exclusão do registro:\n"${titulo}" (${nomeArquivo})\n\n⚠️ Esta solicitação será enviada para aprovação.\n\nPor favor, informe o motivo da exclusão:`);
    
    if (!motivo || motivo.trim() === '') {
        return; // Usuário cancelou ou não informou motivo
    }
    
    // Confirmação final
    const confirmacao = confirm(`✅ CONFIRMAR SOLICITAÇÃO\n\nRegistro: ${titulo}\nArquivo: ${nomeArquivo}\nMotivo: ${motivo}\n\n⚠️ Esta solicitação será enviada para aprovação pelos administradores.\n\nConfirma a solicitação de exclusão?`);
    
    if (!confirmacao) return;
    
    try {
        const formData = new FormData();
        formData.append('registro_id', registroId);
        formData.append('motivo', motivo.trim());
        
        const response = await fetch('/fluxogramas/solicitacao/create', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(`✅ Solicitação enviada com sucesso!\n\n📋 Protocolo: #${result.solicitacao_id}\n\n⏳ Sua solicitação será avaliada pelos administradores.\nVocê será notificado sobre a decisão.`);
            loadMeusRegistros(); // Recarregar lista
        } else {
            alert('❌ ' + result.message);
        }
        
    } catch (error) {
        console.error('Erro ao solicitar exclusão:', error);
        alert('❌ Erro ao enviar solicitação de exclusão');
    }
}


// Excluir título (apenas admin)
async function excluirTitulo(id, titulo) {
    // Confirmação dupla para segurança
    const confirmacao1 = confirm(`⚠️ ATENÇÃO: Exclusão de Título\n\nDeseja excluir o fluxograma: "${titulo}"?\n\n⚠️ IMPORTANTE: Se existirem registros vinculados a este título, a exclusão será bloqueada.\n\nContinuar?`);
    
    if (!confirmacao1) return;
    
    const confirmacao2 = confirm(`🔴 CONFIRMAÇÃO FINAL\n\nVocê confirma a exclusão de:\nFluxograma: "${titulo}"\n\n✅ Clique OK para confirmar\n❌ Clique Cancelar para abortar`);
    
    if (!confirmacao2) return;
    
    try {
        const formData = new FormData();
        formData.append('titulo_id', id);
        
        const response = await fetch('/fluxogramas/titulo/delete', {
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

// ===== ABA 3: PENDENTE APROVAÇÃO =====

// Carregar registros pendentes de aprovação
async function loadPendentesAprovacao() {
    try {
        console.log('🔄 Carregando registros pendentes...');
        const response = await fetch('/fluxogramas/pendentes/list');
        const result = await response.json();
        
        const tbody = document.getElementById('listaPendentes');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(registro => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">${registro.titulo}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        v${registro.versao}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${registro.autor_nome}</div>
                        <div class="text-sm text-gray-500">${registro.autor_email}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${formatDate(registro.criado_em)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="/fluxogramas/arquivo/${registro.id}" target="_blank" 
                           class="text-blue-600 hover:text-blue-900 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            ${registro.nome_arquivo}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <button onclick="aprovarRegistro(${registro.id})" 
                                    class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition-colors">
                                ✓ Aprovar
                            </button>
                            <button onclick="reprovarRegistro(${registro.id})" 
                                    class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700 transition-colors">
                                ✗ Reprovar
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">Nenhum registro pendente</p>
                            <p class="text-gray-500">Todos os registros foram processados</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar pendências:', error);
        document.getElementById('listaPendentes').innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-red-500">
                    Erro ao carregar registros pendentes
                </td>
            </tr>
        `;
    }
}

// Carregar solicitações de exclusão
async function loadSolicitacoes() {
    try {
        console.log('🔄 Carregando solicitações de exclusão...');
        const response = await fetch('/fluxogramas/solicitacoes/list');
        const result = await response.json();
        
        const tbody = document.getElementById('listaSolicitacoes');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(solicitacao => {
                const statusColor = getStatusColorSolicitacao(solicitacao.status);
                const statusText = getStatusTextSolicitacao(solicitacao.status);
                
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono text-gray-900">#${solicitacao.id}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">${solicitacao.titulo}</div>
                            <div class="text-xs text-gray-500">v${solicitacao.versao}</div>
                            <div class="text-xs text-gray-400">${solicitacao.nome_arquivo}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${solicitacao.solicitante_nome}</div>
                            <div class="text-xs text-gray-500">${solicitacao.solicitante_email}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs truncate" title="${solicitacao.motivo}">
                                ${solicitacao.motivo}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${formatDate(solicitacao.solicitado_em)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusColor}">
                                ${statusText}
                            </span>
                            ${solicitacao.avaliado_em ? `<div class="text-xs text-gray-500 mt-1">em ${formatDate(solicitacao.avaliado_em)}</div>` : ''}
                            ${solicitacao.observacoes_avaliacao ? `<div class="text-xs text-gray-600 mt-1" title="${solicitacao.observacoes_avaliacao}">📝 ${solicitacao.observacoes_avaliacao.substring(0, 30)}${solicitacao.observacoes_avaliacao.length > 30 ? '...' : ''}</div>` : ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            ${solicitacao.status === 'PENDENTE' ? `
                                <div class="flex space-x-2">
                                    <button onclick="aprovarSolicitacaoExclusao(${solicitacao.id}, '${solicitacao.titulo}')" 
                                            class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition-colors">
                                        ✓ Aprovar
                                    </button>
                                    <button onclick="reprovarSolicitacaoExclusao(${solicitacao.id}, '${solicitacao.titulo}')" 
                                            class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700 transition-colors">
                                        ✗ Reprovar
                                    </button>
                                </div>
                            ` : `
                                <span class="text-gray-500 text-xs">
                                    ${solicitacao.avaliado_por_nome ? `Por: ${solicitacao.avaliado_por_nome}` : 'Processada'}
                                </span>
                            `}
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">Nenhuma solicitação de exclusão</p>
                            <p class="text-gray-500">Não há solicitações pendentes ou processadas</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar solicitações:', error);
        document.getElementById('listaSolicitacoes').innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-red-500">
                    Erro ao carregar solicitações de exclusão
                </td>
            </tr>
        `;
    }
}

// Funções auxiliares para status das solicitações
function getStatusColorSolicitacao(status) {
    switch (status) {
        case 'PENDENTE': return 'bg-yellow-100 text-yellow-800';
        case 'APROVADA': return 'bg-green-100 text-green-800';
        case 'REPROVADA': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getStatusTextSolicitacao(status) {
    switch (status) {
        case 'PENDENTE': return '⏳ Pendente';
        case 'APROVADA': return '✅ Aprovada';
        case 'REPROVADA': return '❌ Reprovada';
        default: return status;
    }
}

// Aprovar solicitação de exclusão
async function aprovarSolicitacaoExclusao(solicitacaoId, titulo) {
    const observacoes = prompt(`✅ APROVAR EXCLUSÃO\n\nVocê está aprovando a exclusão do registro:\n"${titulo}"\n\n⚠️ O registro será PERMANENTEMENTE excluído do sistema.\n\nObservações (opcional):`);
    
    if (observacoes === null) return; // Usuário cancelou
    
    const confirmacao = confirm(`🔴 CONFIRMAÇÃO FINAL\n\nVocê confirma a APROVAÇÃO da exclusão?\n\nRegistro: ${titulo}\nObservações: ${observacoes || 'Nenhuma'}\n\n⚠️ Esta ação é IRREVERSÍVEL!`);
    
    if (!confirmacao) return;
    
    try {
        const formData = new FormData();
        formData.append('solicitacao_id', solicitacaoId);
        formData.append('observacoes', observacoes || '');
        
        const response = await fetch('/fluxogramas/solicitacao/aprovar', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(`✅ ${result.message}`);
            loadSolicitacoes(); // Recarregar lista de solicitações
            loadPendentesAprovacao(); // Recarregar lista de pendentes
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao aprovar solicitação:', error);
        alert('❌ Erro ao aprovar solicitação');
    }
}

// Reprovar solicitação de exclusão
async function reprovarSolicitacaoExclusao(solicitacaoId, titulo) {
    const observacoes = prompt(`❌ REPROVAR EXCLUSÃO\n\nVocê está reprovando a exclusão do registro:\n"${titulo}"\n\n📝 Informe o motivo da reprovação (obrigatório):`);
    
    if (!observacoes || observacoes.trim() === '') {
        if (observacoes !== null) { // Se não cancelou
            alert('❌ O motivo da reprovação é obrigatório');
        }
        return;
    }
    
    const confirmacao = confirm(`✅ CONFIRMAR REPROVAÇÃO\n\nRegistro: ${titulo}\nMotivo: ${observacoes}\n\nO solicitante será notificado sobre a reprovação.`);
    
    if (!confirmacao) return;
    
    try {
        const formData = new FormData();
        formData.append('solicitacao_id', solicitacaoId);
        formData.append('observacoes', observacoes.trim());
        
        const response = await fetch('/fluxogramas/solicitacao/reprovar', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(`✅ ${result.message}`);
            loadSolicitacoes(); // Recarregar lista
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao reprovar solicitação:', error);
        alert('❌ Erro ao reprovar solicitação');
    }
}

// Aprovar registro
async function aprovarRegistro(registroId) {
    if (!confirm('Tem certeza que deseja aprovar este registro?')) return;
    
    try {
        const formData = new FormData();
        formData.append('registro_id', registroId);
        
        const response = await fetch('/fluxogramas/registro/aprovar', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            loadPendentesAprovacao(); // Recarregar lista
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao aprovar registro:', error);
        alert('❌ Erro ao aprovar registro');
    }
}

// Reprovar registro
async function reprovarRegistro(registroId) {
    const observacao = prompt('Digite a observação de reprovação:');
    if (!observacao || observacao.trim() === '') {
        alert('Observação é obrigatória para reprovação');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('registro_id', registroId);
        formData.append('observacao', observacao.trim());
        
        const response = await fetch('/fluxogramas/registro/reprovar', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            loadPendentesAprovacao(); // Recarregar lista
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao reprovar registro:', error);
        alert('❌ Erro ao reprovar registro');
    }
}

// Função auxiliar para exibir visibilidade com departamentos
function getVisibilidadeDisplay(registro) {
    if (registro.publico) {
        return `
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                🌐 Público
            </span>
        `;
    } else {
        // Documento restrito - mostrar departamentos permitidos
        const departamentos = registro.departamentos_permitidos;
        
        if (departamentos && departamentos.trim() !== '') {
            // Limitar exibição para não quebrar layout
            const deptList = departamentos.split(', ');
            const maxVisible = 2;
            
            if (deptList.length <= maxVisible) {
                return `
                    <div class="text-xs">
                        <span class="inline-flex px-2 py-1 font-semibold rounded-full bg-yellow-100 text-yellow-800 mb-1">
                            🔒 Restrito
                        </span>
                        <div class="text-gray-600 text-xs">
                            ${departamentos}
                        </div>
                    </div>
                `;
            } else {
                const visibleDepts = deptList.slice(0, maxVisible).join(', ');
                const remainingCount = deptList.length - maxVisible;
                
                return `
                    <div class="text-xs">
                        <span class="inline-flex px-2 py-1 font-semibold rounded-full bg-yellow-100 text-yellow-800 mb-1">
                            🔒 Restrito
                        </span>
                        <div class="text-gray-600 text-xs" title="${departamentos}">
                            ${visibleDepts}
                            <span class="text-gray-400">+${remainingCount} mais</span>
                        </div>
                    </div>
                `;
            }
        } else {
            // Documento restrito sem departamentos específicos
            // Para admins, mostrar como "Restrito - Admin" 
            // Para usuários comuns, não deveria aparecer na lista
            return `
                <div class="text-xs">
                    <span class="inline-flex px-2 py-1 font-semibold rounded-full bg-orange-100 text-orange-800 mb-1">
                        🔒 Restrito
                    </span>
                    <div class="text-gray-600 text-xs">
                        Sem departamentos definidos
                    </div>
                </div>
            `;
        }
    }
}

// Função auxiliar para gerar botão de visualização baseado no tipo de arquivo
function getVisualizarButton(registro) {
    const extensao = registro.extensao.toLowerCase();
    const tiposImagem = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'];
    
    // Verificar se usuário é admin
    const isAdmin = document.getElementById('tab-pendentes') !== null;
    
    let botoes = '';
    
    if (extensao === 'pdf') {
        botoes = `
            <button onclick="visualizarArquivo(${registro.id}, '${registro.nome_arquivo}', 'pdf')" 
                    class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition-colors flex items-center"
                    title="Visualizar PDF">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                👁️ Ver
            </button>
        `;
    } else if (tiposImagem.includes(extensao)) {
        botoes = `
            <button onclick="visualizarArquivo(${registro.id}, '${registro.nome_arquivo}', 'imagem')" 
                    class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition-colors flex items-center"
                    title="Visualizar Imagem">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                👁️ Ver
            </button>
        `;
    } else {
        botoes = `<span class="text-gray-500 text-xs">Tipo não suportado</span>`;
    }
    
    // Adicionar botão de download APENAS para admins
    if (isAdmin && (extensao === 'pdf' || tiposImagem.includes(extensao))) {
        botoes += `
            <button onclick="baixarFluxograma(${registro.id})" 
                    class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition-colors flex items-center ml-2"
                    title="Baixar arquivo (Admin)">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                📥 Baixar
            </button>
        `;
    }
    
    return botoes;
}

// ===== ABA 4: VISUALIZAÇÃO =====

// Carregar registros aprovados para visualização
async function loadVisualizacao() {
    try {
        console.log('🔄 Carregando registros aprovados...');
        const response = await fetch('/fluxogramas/visualizacao/list');
        const result = await response.json();
        
        const tbody = document.getElementById('listaVisualizacao');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(registro => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">${registro.titulo}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        v${registro.versao}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${registro.autor_nome}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${formatDate(registro.aprovado_em)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${getVisibilidadeDisplay(registro)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            ${getVisualizarButton(registro)}
                        </div>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">Nenhum registro aprovado</p>
                            <p class="text-gray-500">Aguardando aprovação de registros</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar visualização:', error);
        document.getElementById('listaVisualizacao').innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-red-500">
                    Erro ao carregar registros aprovados
                </td>
            </tr>
        `;
    }
}

// ===== ABA 5: LOG DE VISUALIZAÇÕES =====

// Carregar logs de visualização
async function loadLogsVisualizacao() {
    try {
        console.log('🔄 Carregando logs de visualização...');
        
        // Obter filtros
        const search = document.getElementById('searchLogs')?.value || '';
        const dataInicio = document.getElementById('dataInicio')?.value || '';
        const dataFim = document.getElementById('dataFim')?.value || '';
        
        // Construir URL com parâmetros
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (dataInicio) params.append('data_inicio', dataInicio);
        if (dataFim) params.append('data_fim', dataFim);
        
        const response = await fetch(`/fluxogramas/logs/visualizacao?${params}`);
        const result = await response.json();
        
        const tbody = document.getElementById('listaLogs');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(log => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${formatDateTime(log.visualizado_em)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${log.usuario_nome}</div>
                        <div class="text-sm text-gray-500">${log.usuario_email}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">${log.titulo}</div>
                        <div class="text-sm text-gray-500">${log.nome_arquivo}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        v${log.versao}
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">Nenhum log encontrado</p>
                            <p class="text-gray-500">Não há visualizações registradas com os filtros aplicados</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar logs:', error);
        document.getElementById('listaLogs').innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-red-500">
                    Erro ao carregar logs de visualização
                </td>
            </tr>
        `;
    }
}

// Filtrar logs
function filtrarLogs() {
    loadLogsVisualizacao();
}

// Função removida - testarLogs() não é mais necessária

// Teste simples de notificação
async function testarNotificacaoSimples() {
    try {
        console.log('🧪 Testando notificação manual...');
        
        const response = await fetch('/pops-its/teste-notificacao-manual', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                teste: true
            })
        });
        
        const result = await response.json();
        console.log('📊 Resultado:', result);
        
        if (result.success) {
            alert(`✅ TESTE CONCLUÍDO!\n\n${result.message}\n\nVerifique:\n1. Logs no servidor\n2. Sininho de notificações\n3. Console do navegador`);
        } else {
            alert(`❌ ERRO NO TESTE:\n\n${result.message || result.error}`);
        }
        
    } catch (error) {
        console.error('❌ Erro:', error);
        alert('❌ Erro ao executar teste');
    }
}

// Variáveis globais para zoom e pan
let currentZoom = 1;
let isDragging = false;
let startX, startY, scrollLeft, scrollTop;

// Visualizar arquivo em iframe (modal) com proteções e zoom
function visualizarArquivo(registroId, nomeArquivo, tipo) {
    // Criar modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50';
    modal.id = 'modalVisualizacao';
    
    const icone = tipo === 'pdf' ? '📄' : '🖼️';
    const titulo = tipo === 'pdf' ? 'PDF' : 'Imagem';
    
    // Controles de zoom apenas para imagens
    const zoomControls = tipo === 'imagem' ? `
        <div class="flex items-center space-x-2 bg-gray-100 rounded px-2 py-1">
            <button onclick="zoomOut()" class="px-2 py-1 bg-white rounded hover:bg-gray-200" title="Diminuir zoom (Scroll Down)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                </svg>
            </button>
            <span id="zoomLevel" class="text-sm font-medium text-gray-700 w-16 text-center">100%</span>
            <button onclick="zoomIn()" class="px-2 py-1 bg-white rounded hover:bg-gray-200" title="Aumentar zoom (Scroll Up)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                </svg>
            </button>
            <button onclick="resetZoom()" class="px-2 py-1 bg-white rounded hover:bg-gray-200 text-xs" title="Reset (100%)">
                ↺
            </button>
        </div>
    ` : '';
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl w-11/12 h-5/6 max-w-6xl relative flex flex-col">
            <div class="flex justify-between items-center p-4 border-b bg-gray-50 rounded-t-lg">
                <h3 class="text-lg font-medium text-gray-900">${icone} ${titulo}: ${nomeArquivo}</h3>
                <div class="flex items-center space-x-3">
                    ${zoomControls}
                    <span class="text-xs text-blue-600 font-medium">💡 Use scroll do mouse para zoom</span>
                    <span class="text-xs text-red-600 font-medium">🔒 Protegido</span>
                    <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="imageContainer" class="flex-1 overflow-auto relative bg-gray-100" style="cursor: ${tipo === 'imagem' ? 'grab' : 'default'};">
                ${tipo === 'imagem' ? `
                    <img src="/fluxogramas/visualizar/${registroId}" 
                         id="zoomableImage"
                         class="transition-transform duration-200"
                         style="transform-origin: center center; max-width: none; display: block; margin: auto;"
                         oncontextmenu="return false;"
                         ondragstart="return false;">
                ` : `
                    <iframe src="/fluxogramas/visualizar/${registroId}" 
                            class="w-full h-full border-0" 
                            title="Visualização protegida"
                            style="background: #f8f9fa;">
                    </iframe>
                `}
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Configurar zoom e pan para imagens
    if (tipo === 'imagem') {
        setTimeout(() => {
            setupImageZoomAndPan();
        }, 100);
    }
    
    // Aplicar proteções adicionais
    aplicarProtecoesSistema();
}

// Configurar zoom e pan na imagem
function setupImageZoomAndPan() {
    const container = document.getElementById('imageContainer');
    const image = document.getElementById('zoomableImage');
    
    if (!container || !image) return;
    
    // Zoom com scroll do mouse
    container.addEventListener('wheel', (e) => {
        e.preventDefault();
        
        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        currentZoom = Math.max(0.5, Math.min(5, currentZoom + delta));
        
        applyZoom();
    });
    
    // Pan (arrastar) quando imagem está com zoom
    container.addEventListener('mousedown', (e) => {
        if (currentZoom > 1) {
            isDragging = true;
            container.style.cursor = 'grabbing';
            startX = e.pageX - container.offsetLeft;
            startY = e.pageY - container.offsetTop;
            scrollLeft = container.scrollLeft;
            scrollTop = container.scrollTop;
        }
    });
    
    container.addEventListener('mouseleave', () => {
        isDragging = false;
        if (currentZoom > 1) container.style.cursor = 'grab';
    });
    
    container.addEventListener('mouseup', () => {
        isDragging = false;
        if (currentZoom > 1) container.style.cursor = 'grab';
    });
    
    container.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const y = e.pageY - container.offsetTop;
        const walkX = (x - startX) * 2;
        const walkY = (y - startY) * 2;
        container.scrollLeft = scrollLeft - walkX;
        container.scrollTop = scrollTop - walkY;
    });
}

// Funções de controle de zoom
function zoomIn() {
    currentZoom = Math.min(5, currentZoom + 0.25);
    applyZoom();
}

function zoomOut() {
    currentZoom = Math.max(0.5, currentZoom - 0.25);
    applyZoom();
}

function resetZoom() {
    currentZoom = 1;
    applyZoom();
}

function applyZoom() {
    const image = document.getElementById('zoomableImage');
    const zoomLevel = document.getElementById('zoomLevel');
    const container = document.getElementById('imageContainer');
    
    if (image) {
        image.style.transform = `scale(${currentZoom})`;
        
        if (zoomLevel) {
            zoomLevel.textContent = Math.round(currentZoom * 100) + '%';
        }
        
        // Atualizar cursor
        if (container) {
            container.style.cursor = currentZoom > 1 ? 'grab' : 'default';
        }
    }
}

// Fechar modal
function fecharModal() {
    const modal = document.querySelector('.fixed.inset-0');
    if (modal) {
        modal.remove();
        document.body.style.overflow = 'auto'; // Restaurar scroll
        removerProtecoesSistema(); // Remover proteções ao fechar
        
        // Reset zoom
        currentZoom = 1;
        isDragging = false;
    }
}

// Aplicar proteções do sistema
function aplicarProtecoesSistema() {
    // Bloquear teclas de atalho
    document.addEventListener('keydown', bloquearTeclasProibidas);
    
    // Bloquear menu de contexto
    document.addEventListener('contextmenu', bloquearContextMenu);
    
    // Bloquear seleção de texto
    document.addEventListener('selectstart', bloquearSelecao);
    
    // Bloquear arrastar
    document.addEventListener('dragstart', bloquearArrastar);
    
    // Detectar tentativas de print
    window.addEventListener('beforeprint', bloquearPrint);
    
    // Bloquear F12, Ctrl+Shift+I, etc.
    document.addEventListener('keydown', bloquearFerramentasDesenvolvedor);
}

// Remover proteções do sistema
function removerProtecoesSistema() {
    document.removeEventListener('keydown', bloquearTeclasProibidas);
    document.removeEventListener('contextmenu', bloquearContextMenu);
    document.removeEventListener('selectstart', bloquearSelecao);
    document.removeEventListener('dragstart', bloquearArrastar);
    window.removeEventListener('beforeprint', bloquearPrint);
    document.removeEventListener('keydown', bloquearFerramentasDesenvolvedor);
}

// Bloquear teclas proibidas (com exceções para PDFs)
function bloquearTeclasProibidas(e) {
    // Verificar se é PDF para permitir algumas teclas de navegação
    const modal = document.getElementById('modalVisualizacao');
    const isPDF = modal && modal.innerHTML.includes('📄');
    
    // Ctrl+S (Salvar), Ctrl+P (Print) - sempre bloqueados
    if (e.ctrlKey && (e.key === 's' || e.key === 'p')) {
        e.preventDefault();
        mostrarAvisoProtecao('Função bloqueada por segurança');
        return false;
    }
    
    // Ctrl+A (Selecionar tudo) - bloquear apenas para imagens
    if (e.ctrlKey && e.key === 'a' && !isPDF) {
        e.preventDefault();
        mostrarAvisoProtecao('Seleção bloqueada por segurança');
        return false;
    }
    
    // Print Screen
    if (e.key === 'PrintScreen') {
        e.preventDefault();
        mostrarAvisoProtecao('Print Screen bloqueado por segurança');
        return false;
    }
    
    // Permitir teclas de navegação para PDFs (setas, Page Up/Down, Home, End)
    if (isPDF && (e.key === 'ArrowUp' || e.key === 'ArrowDown' || 
                  e.key === 'PageUp' || e.key === 'PageDown' || 
                  e.key === 'Home' || e.key === 'End' || 
                  e.key === 'Space')) {
        // Permitir navegação em PDFs
        return true;
    }
}

// Bloquear menu de contexto (botão direito)
function bloquearContextMenu(e) {
    e.preventDefault();
    mostrarAvisoProtecao('Menu de contexto bloqueado por segurança');
    return false;
}

// Bloquear seleção de texto (exceto para PDFs)
function bloquearSelecao(e) {
    // Verificar se é PDF para permitir seleção (necessária para scroll)
    const modal = document.getElementById('modalVisualizacao');
    const isPDF = modal && modal.innerHTML.includes('📄');
    
    // Permitir seleção em PDFs para funcionalidade de scroll
    if (isPDF) {
        return true;
    }
    
    e.preventDefault();
    return false;
}

// Bloquear arrastar elementos
function bloquearArrastar(e) {
    e.preventDefault();
    return false;
}

// Bloquear print
function bloquearPrint(e) {
    e.preventDefault();
    mostrarAvisoProtecao('Impressão bloqueada por segurança');
    return false;
}

// Bloquear ferramentas de desenvolvedor
function bloquearFerramentasDesenvolvedor(e) {
    // F12
    if (e.key === 'F12') {
        e.preventDefault();
        mostrarAvisoProtecao('Ferramentas de desenvolvedor bloqueadas');
        return false;
    }
    
    // Ctrl+Shift+I (DevTools)
    if (e.ctrlKey && e.shiftKey && e.key === 'I') {
        e.preventDefault();
        mostrarAvisoProtecao('Ferramentas de desenvolvedor bloqueadas');
        return false;
    }
    
    // Ctrl+Shift+C (Inspect)
    if (e.ctrlKey && e.shiftKey && e.key === 'C') {
        e.preventDefault();
        mostrarAvisoProtecao('Inspeção de elementos bloqueada');
        return false;
    }
    
    // Ctrl+U (View Source)
    if (e.ctrlKey && e.key === 'u') {
        e.preventDefault();
        mostrarAvisoProtecao('Visualização de código fonte bloqueada');
        return false;
    }
}

// Mostrar aviso de proteção
function mostrarAvisoProtecao(mensagem) {
    // Criar toast de aviso
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-red-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 flex items-center';
    toast.innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        🔒 ${mensagem}
    `;
    
    document.body.appendChild(toast);
    
    // Remover toast após 3 segundos
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}

// Editar visibilidade inline (popup compacto)
async function editarVisibilidadeInline(registroId, isPublico, departamentosAtuais) {
    try {
        console.log('🔍 Editando visibilidade inline - ID:', registroId);
        
        // Buscar dados do registro
        const response = await fetch(`/fluxogramas/registros/${registroId}`);
        
        if (!response.ok) {
            alert('Erro ao buscar dados do registro');
            return;
        }
        
        const responseText = await response.text();
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('❌ Erro ao fazer parse do JSON:', e);
            alert('Erro: Resposta inválida do servidor');
            return;
        }
        
        if (!result.success || !result.data) {
            alert('Erro: ' + (result.message || 'Dados não encontrados'));
            return;
        }
        
        const registro = result.data;
        
        // Criar popup compacto inline
        const popupHTML = `
            <div id="popupVisibilidade" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50" onclick="if(event.target === this) fecharPopupVisibilidade()">
                <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4">
                    <div class="px-4 py-3 border-b border-gray-200 bg-purple-50 rounded-t-lg">
                        <h3 class="text-sm font-semibold text-gray-900">✏️ Editar Visibilidade</h3>
                        <p class="text-xs text-gray-600 mt-1">${registro.titulo} - v${registro.versao}</p>
                    </div>
                    
                    <div class="p-4">
                        <form id="formVisibilidadeInline" class="space-y-3">
                            <input type="hidden" name="registro_id" value="${registroId}">
                            
                            <!-- Toggle Público/Restrito -->
                            <div class="flex items-center gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="publico" value="1" ${registro.publico ? 'checked' : ''} 
                                           onchange="document.getElementById('deptsInline').style.display='none'" 
                                           class="mr-2">
                                    <span class="text-sm">🌍 Público</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="publico" value="0" ${!registro.publico ? 'checked' : ''} 
                                           onchange="document.getElementById('deptsInline').style.display='block'" 
                                           class="mr-2">
                                    <span class="text-sm">🏢 Restrito</span>
                                </label>
                            </div>
                            
                            <!-- Departamentos -->
                            <div id="deptsInline" style="display: ${registro.publico ? 'none' : 'block'}">
                                <label class="block text-xs font-medium text-gray-700 mb-2">Selecione os departamentos:</label>
                                <div class="border border-gray-300 rounded-lg p-2 max-h-48 overflow-y-auto space-y-1">
                                    ${departamentos.map(dept => `
                                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded text-sm">
                                            <input type="checkbox" name="departamentos[]" value="${dept.id}" class="mr-2">
                                            ${dept.nome}
                                        </label>
                                    `).join('')}
                                </div>
                            </div>
                            
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" onclick="fecharPopupVisibilidade()" 
                                        class="px-3 py-1.5 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                    Cancelar
                                </button>
                                <button type="submit" 
                                        class="px-3 py-1.5 text-sm bg-purple-600 text-white rounded hover:bg-purple-700">
                                    💾 Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', popupHTML);
        
        // Marcar departamentos já selecionados
        if (!registro.publico && registro.departamentos_ids) {
            const deptsArray = Array.isArray(registro.departamentos_ids) 
                ? registro.departamentos_ids 
                : registro.departamentos_ids.split(',');
            
            deptsArray.forEach(deptId => {
                const checkbox = document.querySelector(`#popupVisibilidade input[name="departamentos[]"][value="${deptId}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
        
        // Handler do formulário
        document.getElementById('formVisibilidadeInline').addEventListener('submit', async (e) => {
            e.preventDefault();
            await salvarVisibilidadeInline(e.target);
        });
        
    } catch (error) {
        console.error('Erro ao abrir popup de visibilidade:', error);
        alert('Erro ao carregar formulário');
    }
}

function fecharPopupVisibilidade() {
    const popup = document.getElementById('popupVisibilidade');
    if (popup) popup.remove();
}

async function salvarVisibilidadeInline(form) {
    try {
        const formData = new FormData(form);
        
        const response = await fetch('/fluxogramas/registros/atualizar-visibilidade', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Visibilidade atualizada!');
            fecharPopupVisibilidade();
            loadMeusRegistros(); // Recarregar lista
        } else {
            alert('❌ ' + (result.message || 'Erro ao atualizar'));
        }
    } catch (error) {
        console.error('Erro ao salvar visibilidade:', error);
        alert('❌ Erro ao salvar');
    }
}

// Editar visibilidade do registro (MODAL COMPLETO - manter para compatibilidade)
async function editarVisibilidade(registroId) {
    try {
        console.log('🔍 Buscando registro ID:', registroId);
        
        // Buscar dados do registro
        const response = await fetch(`/fluxogramas/registros/${registroId}`);
        console.log('📡 Response status:', response.status);
        
        if (!response.ok) {
            alert('Erro ao buscar dados do registro (HTTP ' + response.status + ')');
            return;
        }
        
        const responseText = await response.text();
        console.log('📄 Response text:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('❌ Erro ao fazer parse do JSON:', e);
            alert('Erro: Resposta inválida do servidor');
            return;
        }
        
        if (!result.success || !result.data) {
            alert('Erro ao carregar dados do registro: ' + (result.message || 'Dados não encontrados'));
            return;
        }
        
        const registro = result.data;
        console.log('✅ Registro carregado:', registro);
        
        // Criar modal dinâmico
        const modalHTML = `
            <div id="modalVisibilidade" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="if(event.target === this) fecharModalVisibilidade()">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-purple-50">
                        <h3 class="text-lg font-semibold text-gray-900">👁️ Editar Visibilidade</h3>
                        <button onclick="fecharModalVisibilidade()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800"><strong>Registro:</strong> ${registro.titulo} - v${registro.versao}</p>
                            <p class="text-xs text-blue-600 mt-1">Altere a visibilidade sem necessidade de nova aprovação</p>
                        </div>
                        
                        <form id="formEditarVisibilidade" class="space-y-4">
                            <input type="hidden" name="registro_id" value="${registroId}">
                            
                            <!-- Visibilidade -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Visibilidade *</label>
                                <div class="space-y-2">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="publico" value="1" ${registro.publico ? 'checked' : ''} 
                                               onchange="document.getElementById('deptsVisibilidade').style.display='none'" 
                                               class="mr-2">
                                        <span class="text-sm">🌍 <strong>Público</strong> - Todos os usuários podem visualizar</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="publico" value="0" ${!registro.publico ? 'checked' : ''} 
                                               onchange="document.getElementById('deptsVisibilidade').style.display='block'" 
                                               class="mr-2">
                                        <span class="text-sm">🏢 <strong>Restrito</strong> - Apenas departamentos selecionados</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Departamentos (se restrito) -->
                            <div id="deptsVisibilidade" style="display: ${registro.publico ? 'none' : 'block'}">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departamentos Permitidos *</label>
                                <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto space-y-2">
                                    ${departamentos.map(dept => `
                                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                            <input type="checkbox" name="departamentos[]" value="${dept.id}" class="mr-2">
                                            <span class="text-sm">${dept.nome}</span>
                                        </label>
                                    `).join('')}
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button type="button" onclick="fecharModalVisibilidade()" 
                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                                    Cancelar
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                                    💾 Salvar Visibilidade
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Marcar departamentos já selecionados
        if (!registro.publico && registro.departamentos_ids) {
            const deptsArray = Array.isArray(registro.departamentos_ids) 
                ? registro.departamentos_ids 
                : registro.departamentos_ids.split(',');
            
            deptsArray.forEach(deptId => {
                const checkbox = document.querySelector(`input[name="departamentos[]"][value="${deptId}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
        
        // Handler do formulário
        document.getElementById('formEditarVisibilidade').addEventListener('submit', async (e) => {
            e.preventDefault();
            await salvarVisibilidade(e.target);
        });
        
    } catch (error) {
        console.error('Erro ao abrir modal de visibilidade:', error);
        alert('Erro ao carregar formulário de visibilidade');
    }
}

function fecharModalVisibilidade() {
    const modal = document.getElementById('modalVisibilidade');
    if (modal) modal.remove();
}

async function salvarVisibilidade(form) {
    try {
        const formData = new FormData(form);
        
        const response = await fetch('/fluxogramas/registros/atualizar-visibilidade', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Visibilidade atualizada com sucesso!');
            fecharModalVisibilidade();
            loadMeusRegistros(); // Recarregar lista
        } else {
            alert('❌ ' + (result.message || 'Erro ao atualizar visibilidade'));
        }
    } catch (error) {
        console.error('Erro ao salvar visibilidade:', error);
        alert('❌ Erro ao salvar visibilidade');
    }
}

// Aplicar proteções específicas por tipo de arquivo
function aplicarProtecoesPorTipo(tipo) {
    if (tipo === 'pdf') {
        // Para PDFs, permitir scroll mas bloquear outras interações
        const style = document.createElement('style');
        style.textContent = `
            .pdf-iframe {
                -webkit-user-select: none !important;
                -moz-user-select: none !important;
                -ms-user-select: none !important;
                user-select: none !important;
                /* Permitir pointer-events para scroll em PDFs */
                pointer-events: auto !important;
            }
        `;
        document.head.appendChild(style);
        
        // Adicionar classe específica ao iframe do PDF
        const iframe = document.querySelector('iframe[title="Visualização protegida"]');
        if (iframe) {
            iframe.classList.add('pdf-iframe');
        }
    } else {
        // Para imagens, bloquear todas as interações
        const style = document.createElement('style');
        style.textContent = `
            .image-iframe {
                -webkit-user-select: none !important;
                -moz-user-select: none !important;
                -ms-user-select: none !important;
                user-select: none !important;
                pointer-events: none !important;
            }
        `;
        document.head.appendChild(style);
        
        // Adicionar classe específica ao iframe da imagem
        const iframe = document.querySelector('iframe[title="Visualização protegida"]');
        if (iframe) {
            iframe.classList.add('image-iframe');
        }
    }
}

// Manter função antiga para compatibilidade
function aplicarProtecoes() {
    aplicarProtecoesPorTipo('imagem');
}

// Função auxiliar para formatar data e hora
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// ===== FUNÇÕES DE BUSCA INTELIGENTE =====

// Filtrar Títulos Cadastrados
function filtrarTitulosCadastro() {
    const input = document.getElementById('buscaTitulosCadastro');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('listaTitulos');
    const tr = table.getElementsByTagName('tr');

    for (let i = 0; i < tr.length; i++) {
        const tdTitulo = tr[i].getElementsByTagName('td')[1]; // Coluna do título
        const tdDept = tr[i].getElementsByTagName('td')[2];   // Coluna do departamento
        const tdCriador = tr[i].getElementsByTagName('td')[3]; // Coluna do criador
        
        if (tdTitulo || tdDept || tdCriador) {
            const txtTitulo = tdTitulo ? tdTitulo.textContent || tdTitulo.innerText : '';
            const txtDept = tdDept ? tdDept.textContent || tdDept.innerText : '';
            const txtCriador = tdCriador ? tdCriador.textContent || tdCriador.innerText : '';
            
            const txtValue = txtTitulo + ' ' + txtDept + ' ' + txtCriador;
            
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

// Filtrar Visualização (Registros Aprovados)
function filtrarVisualizacao() {
    const input = document.getElementById('buscaVisualizacao');
    const filter = input.value.toUpperCase();
    const selectDept = document.getElementById('filtroDepartamentoVisualizacao');
    const deptFilter = selectDept ? selectDept.value.toUpperCase() : '';
    const table = document.getElementById('listaVisualizacao');
    const tr = table.getElementsByTagName('tr');

    for (let i = 0; i < tr.length; i++) {
        const tdTitulo = tr[i].getElementsByTagName('td')[0];  // Título
        const tdVersao = tr[i].getElementsByTagName('td')[1];  // Versão
        const tdAutor = tr[i].getElementsByTagName('td')[2];   // Autor
        const tdVisibilidade = tr[i].getElementsByTagName('td')[4]; // Visibilidade (inclui departamentos)
        
        if (tdTitulo || tdVersao || tdAutor) {
            const txtTitulo = tdTitulo ? tdTitulo.textContent || tdTitulo.innerText : '';
            const txtVersao = tdVersao ? tdVersao.textContent || tdVersao.innerText : '';
            const txtAutor = tdAutor ? tdAutor.textContent || tdAutor.innerText : '';
            const txtVisibilidade = tdVisibilidade ? tdVisibilidade.textContent || tdVisibilidade.innerText : '';
            
            // Filtro de busca por texto
            const txtValue = txtTitulo + ' ' + txtVersao + ' ' + txtAutor;
            const matchesSearch = !filter || txtValue.toUpperCase().indexOf(filter) > -1;
            
            // Filtro por departamento
            const matchesDept = !deptFilter || txtVisibilidade.toUpperCase().indexOf(deptFilter) > -1;
            
            // Mostrar apenas se passar em ambos os filtros
            if (matchesSearch && matchesDept) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

</script>




