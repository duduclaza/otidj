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
        // Fallback via sessão
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
$canEdit = hasPermission('controle_descartes', 'edit');
$canDelete = hasPermission('controle_descartes', 'delete');
$canImport = hasPermission('controle_descartes', 'import');
$canExport = hasPermission('controle_descartes', 'export');

// Verificar se pode alterar status (admin ou perfil qualidade)
$canAlterarStatus = false;
$userRole = $_SESSION['user_role'] ?? '';
if ($userRole === 'admin' || $userRole === 'super_admin') {
    $canAlterarStatus = true;
} else {
    // Verificar se tem perfil qualidade
    try {
        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.nome 
            FROM user_profiles up
            JOIN profiles p ON up.profile_id = p.id
            WHERE up.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $perfis = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $canAlterarStatus = in_array('Qualidade', $perfis) || in_array('qualidade', $perfis);
    } catch (\Exception $e) {
        $canAlterarStatus = false;
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Controle de Descartes</h1>
                <p class="mt-2 text-gray-600">Gerenciamento de descartes de equipamentos</p>
            </div>
            <div class="flex space-x-3">
                <?php if ($canExport): ?>
                <button onclick="exportarDescartes()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Exportar
                </button>
                <?php endif; ?>
                <?php if ($canImport): ?>
                <button onclick="abrirModalImportacao()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Importar Excel
                </button>
                <?php endif; ?>
                <?php if ($canEdit): ?>
                <button onclick="abrirModalDescarte()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Novo Descarte
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros de Busca</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Número de Série</label>
                <input type="text" id="filtro-numero-serie" placeholder="Digite o número de série" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Número da OS</label>
                <input type="text" id="filtro-numero-os" placeholder="Digite o número da OS" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
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

    <!-- Tabela de Descartes -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Lista de Descartes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número de Série</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filial</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Descarte</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anexo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-descartes" class="bg-white divide-y divide-gray-200">
                    <!-- Dados carregados via JavaScript -->
                </tbody>
            </table>
        </div>
        <div id="no-data" class="text-center py-8 hidden">
            <p class="text-gray-500">Nenhum descarte encontrado.</p>
        </div>
    </div>
</div>

<!-- Modal Importação -->
<div id="modal-importacao" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Importar Descartes via Excel</h3>
                <button onclick="fecharModalImportacao()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h4 class="font-medium text-blue-900 mb-2">📋 Instruções:</h4>
                    <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                        <li>Clique em "Baixar Template" para obter o modelo Excel</li>
                        <li>Preencha os dados seguindo o exemplo incluído</li>
                        <li>Salve o arquivo e faça o upload abaixo</li>
                        <li>Os dados serão validados antes da importação</li>
                    </ol>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <h4 class="font-medium text-yellow-900 mb-2">⚠️ Campos obrigatórios:</h4>
                    <p class="text-sm text-yellow-800">Número de Série, Filial, Código do Produto, Descrição do Produto, Responsável Técnico</p>
                </div>

                <label class="block text-sm font-medium text-gray-700 mb-2">Selecione o arquivo Excel:</label>
                <input type="file" id="arquivo-importacao" accept=".xlsx,.xls,.csv" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <small class="text-gray-500">Formatos aceitos: .xlsx, .xls, .csv</small>
            </div>

            <div id="preview-importacao" class="hidden mb-4">
                <h4 class="font-medium text-gray-900 mb-2">Preview dos Dados:</h4>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 max-h-64 overflow-y-auto">
                    <p id="preview-count" class="text-sm text-gray-600 mb-2"></p>
                    <div id="preview-content" class="text-sm"></div>
                </div>
            </div>

            <div id="progress-importacao" class="hidden mb-4">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all" style="width: 0%"></div>
                </div>
                <p id="progress-text" class="text-sm text-gray-600 mt-2 text-center"></p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="fecharModalImportacao()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                    Cancelar
                </button>
                <button type="button" onclick="baixarTemplate()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md">
                    Baixar Template
                </button>
                <button type="button" id="btn-importar" onclick="processarImportacao()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md" disabled>
                    Importar Dados
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Descarte -->
<div id="modal-descarte" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modal-titulo">Novo Descarte</h3>
                <button onclick="fecharModalDescarte()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="form-descarte" enctype="multipart/form-data">
                <input type="hidden" id="descarte-id" name="id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Número de Série *</label>
                        <input type="text" id="numero-serie" name="numero_serie" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filial *</label>
                        <select id="filial-id" name="filial_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione uma filial</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option value="<?= $filial['id'] ?>"><?= e($filial['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Código do Produto *</label>
                        <input type="text" id="codigo-produto" name="codigo_produto" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data do Descarte</label>
                        <input type="date" id="data-descarte" name="data_descarte" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <small class="text-gray-500">Se não informada, será considerado hoje</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrição do Produto *</label>
                    <textarea id="descricao-produto" name="descricao_produto" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Número da OS</label>
                        <input type="text" id="numero-os" name="numero_os" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Responsável Técnico *</label>
                        <input type="text" id="responsavel-tecnico" name="responsavel_tecnico" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Anexo da OS Assinada</label>
                    <input type="file" id="anexo-os" name="anexo_os" accept=".png,.jpg,.jpeg,.pdf" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <small class="text-gray-500">Formatos aceitos: PNG, JPEG, PDF. Máximo 10MB</small>
                </div>

                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Notificar Pessoas (Opcional)
                    </label>
                    <select id="notificar-usuarios" name="notificar_usuarios[]" multiple 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" 
                            style="min-height: 150px;">
                        <?php foreach ($usuariosNotificacao as $usuario): ?>
                        <option value="<?= $usuario['id'] ?>">
                            <?= htmlspecialchars($usuario['name']) ?> (<?= htmlspecialchars($usuario['email']) ?>)
                            <?php if (in_array($usuario['role'], ['admin', 'super_admin'])): ?>
                                - Admin
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-gray-600 mt-2 block">
                        💡 <strong>Dica:</strong> Segure <kbd class="px-2 py-1 bg-gray-200 rounded text-xs">Ctrl</kbd> (ou <kbd class="px-2 py-1 bg-gray-200 rounded text-xs">Cmd</kbd> no Mac) e clique para selecionar múltiplas pessoas. Se nenhuma pessoa for selecionada, ninguém será notificado por email.
                    </small>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalDescarte()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancelar
                    </button>
                    <button type="button" id="btn-salvar-descarte" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Alterar Status -->
<div id="modal-alterar-status" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Alterar Status do Descarte</h3>
                <button onclick="fecharModalAlterarStatus()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="form-alterar-status">
                <input type="hidden" id="status-descarte-id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Atual:</label>
                    <p id="status-atual-display" class="text-sm text-gray-600 mb-4"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Novo Status: *</label>
                    <select id="novo-status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Selecione...</option>
                        <option value="Aguardando Descarte">⏳ Aguardando Descarte</option>
                        <option value="Itens Descartados">✅ Itens Descartados</option>
                        <option value="Descartes Reprovados">❌ Descartes Reprovados</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Justificativa:</label>
                    <textarea id="justificativa-status" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Ex: Aprovado após conferência física..."></textarea>
                    <small class="text-gray-500">Opcional, mas recomendado</small>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalAlterarStatus()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancelar
                    </button>
                    <button type="button" onclick="salvarNovoStatus()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md">
                        Salvar Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const podeAlterarStatusGlobal = <?= $canAlterarStatus ? 'true' : 'false' ?>;
let descartes = [];

// Carregar dados ao inicializar
document.addEventListener('DOMContentLoaded', function() {
    carregarDescartes();
    
    // Event listeners para filtros
    document.getElementById('filtro-numero-serie').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') aplicarFiltros();
    });
    document.getElementById('filtro-numero-os').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') aplicarFiltros();
    });
});

// Carregar lista de descartes - v2.0 (sem loading)
function carregarDescartes() {
    console.log('Carregando descartes - versão sem loading');
    // Loading removido - carregamento direto
    document.getElementById('no-data').classList.add('hidden');
    
    const params = new URLSearchParams();
    const numeroSerie = document.getElementById('filtro-numero-serie').value;
    const numeroOs = document.getElementById('filtro-numero-os').value;
    const filialId = document.getElementById('filtro-filial').value;
    const dataInicio = document.getElementById('filtro-data-inicio').value;
    const dataFim = document.getElementById('filtro-data-fim').value;
    
    if (numeroSerie) params.append('numero_serie', numeroSerie);
    if (numeroOs) params.append('numero_os', numeroOs);
    if (filialId) params.append('filial_id', filialId);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    
    fetch(`/controle-descartes/list?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                descartes = data.data;
                renderizarTabela();
            } else {
                alert('Erro ao carregar descartes: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar descartes');
        });
}

// Renderizar tabela
function renderizarTabela() {
    const tbody = document.getElementById('tabela-descartes');
    
    if (descartes.length === 0) {
        document.getElementById('no-data').classList.remove('hidden');
        tbody.innerHTML = '';
        return;
    }
    
    document.getElementById('no-data').classList.add('hidden');
    
    tbody.innerHTML = descartes.map(descarte => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${escapeHtml(descarte.numero_serie)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${escapeHtml(descarte.filial_nome || '')}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="font-medium">${escapeHtml(descarte.codigo_produto)}</div>
                <div class="text-gray-400 truncate max-w-xs">${escapeHtml(descarte.descricao_produto)}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${formatarData(descarte.data_descarte)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${escapeHtml(descarte.responsavel_tecnico)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${descarte.numero_os ? escapeHtml(descarte.numero_os) : '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                ${getStatusBadge(descarte.status || 'Aguardando Descarte')}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${descarte.tem_anexo ? 
                    `<a href="/controle-descartes/anexo/${descarte.id}" class="text-blue-600 hover:text-blue-800" title="Baixar anexo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </a>` : '-'
                }
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    ${podeAlterarStatus() ? 
                        `<button onclick="abrirModalAlterarStatus(${descarte.id}, '${escapeHtml(descarte.status || 'Aguardando Descarte')}')" class="text-purple-600 hover:text-purple-800" title="Alterar Status">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>` : ''
                    }
                    <?php if ($canEdit): ?>
                    <button onclick="editarDescarte(${descarte.id})" class="text-blue-600 hover:text-blue-800" title="Editar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                    <?php if ($canDelete): ?>
                    <button onclick="excluirDescarte(${descarte.id})" class="text-red-600 hover:text-red-800" title="Excluir">
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
    carregarDescartes();
}

// Limpar filtros
function limparFiltros() {
    document.getElementById('filtro-numero-serie').value = '';
    document.getElementById('filtro-numero-os').value = '';
    document.getElementById('filtro-filial').value = '';
    document.getElementById('filtro-data-inicio').value = '';
    document.getElementById('filtro-data-fim').value = '';
    carregarDescartes();
}

// Abrir modal para novo descarte
function abrirModalDescarte() {
    document.getElementById('modal-titulo').textContent = 'Novo Descarte';
    document.getElementById('form-descarte').reset();
    document.getElementById('descarte-id').value = '';
    
    // Limpar seleção do select múltiplo
    const selectNotificar = document.getElementById('notificar-usuarios');
    if (selectNotificar) {
        for (let i = 0; i < selectNotificar.options.length; i++) {
            selectNotificar.options[i].selected = false;
        }
    }
    
    document.getElementById('modal-descarte').classList.remove('hidden');
}

// Fechar modal
function fecharModalDescarte() {
    document.getElementById('modal-descarte').classList.add('hidden');
}

// Editar descarte
function editarDescarte(id) {
    const descarte = descartes.find(d => d.id == id);
    if (!descarte) return;
    
    document.getElementById('modal-titulo').textContent = 'Editar Descarte';
    document.getElementById('descarte-id').value = descarte.id;
    document.getElementById('numero-serie').value = descarte.numero_serie;
    document.getElementById('filial-id').value = descarte.filial_id;
    document.getElementById('codigo-produto').value = descarte.codigo_produto;
    document.getElementById('descricao-produto').value = descarte.descricao_produto;
    document.getElementById('data-descarte').value = descarte.data_descarte;
    document.getElementById('numero-os').value = descarte.numero_os || '';
    document.getElementById('responsavel-tecnico').value = descarte.responsavel_tecnico;
    document.getElementById('observacoes').value = descarte.observacoes || '';
    
    document.getElementById('modal-descarte').classList.remove('hidden');
}

// Excluir descarte
function excluirDescarte(id) {
    if (!confirm('Tem certeza que deseja excluir este descarte?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    fetch('/controle-descartes/delete', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Descarte excluído com sucesso!');
            carregarDescartes();
        } else {
            alert('Erro ao excluir descarte: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao excluir descarte');
    });
}

// Ação explícita de salvar (evita submissão nativa e qualquer navegação)
document.getElementById('btn-salvar-descarte').addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const form = document.getElementById('form-descarte');
    const formData = new FormData(form);
    const isEdit = document.getElementById('descarte-id').value !== '';
    const url = isEdit ? '/controle-descartes/update' : '/controle-descartes/create';

    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        redirect: 'manual' // evita seguir qualquer redirect (ex: login)
    })
    .then(async (response) => {
        // Se o servidor tentar redirecionar (ex: sessão expirada), response.type pode ser 'opaqueredirect'
        if (response.type === 'opaqueredirect' || (response.status >= 300 && response.status < 400)) {
            alert('Sua sessão pode ter expirado. Por favor, faça login novamente.');
            return { success: false };
        }
        try { return await response.json(); } catch (_) { return { success: false, message: 'Resposta inválida do servidor' }; }
    })
    .then(data => {
        if (data && data.success) {
            alert(data.message || 'Registro salvo com sucesso!');
            fecharModalDescarte();
            carregarDescartes();
        } else if (data && data.message) {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar descarte');
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

function exportarDescartes() {
    // TODO: Implementar exportação
    alert('Funcionalidade de exportação será implementada em breve');
}

// Verificar se pode alterar status
function podeAlterarStatus() {
    return podeAlterarStatusGlobal;
}

// Obter badge de status colorido
function getStatusBadge(status) {
    const badges = {
        'Aguardando Descarte': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">⏳ Aguardando</span>',
        'Itens Descartados': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✅ Descartados</span>',
        'Descartes Reprovados': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">❌ Reprovados</span>'
    };
    return badges[status] || badges['Aguardando Descarte'];
}

// Abrir modal para alterar status
function abrirModalAlterarStatus(descarteId, statusAtual) {
    document.getElementById('status-descarte-id').value = descarteId;
    document.getElementById('status-atual-display').innerHTML = getStatusBadge(statusAtual);
    document.getElementById('novo-status').value = '';
    document.getElementById('justificativa-status').value = '';
    document.getElementById('modal-alterar-status').classList.remove('hidden');
}

// Fechar modal alterar status
function fecharModalAlterarStatus() {
    document.getElementById('modal-alterar-status').classList.add('hidden');
}

// Salvar novo status
function salvarNovoStatus() {
    const descarteId = document.getElementById('status-descarte-id').value;
    const novoStatus = document.getElementById('novo-status').value;
    const justificativa = document.getElementById('justificativa-status').value;
    
    if (!novoStatus) {
        alert('Selecione um status');
        return;
    }
    
    const formData = new FormData();
    formData.append('id', descarteId);
    formData.append('status', novoStatus);
    formData.append('justificativa', justificativa);
    
    fetch('/controle-descartes/alterar-status', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            fecharModalAlterarStatus();
            carregarDescartes();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao alterar status');
    });
}

// ===== FUNÇÕES DE IMPORTAÇÃO =====

// Abrir modal de importação
function abrirModalImportacao() {
    document.getElementById('modal-importacao').classList.remove('hidden');
    document.getElementById('arquivo-importacao').value = '';
    document.getElementById('preview-importacao').classList.add('hidden');
    document.getElementById('progress-importacao').classList.add('hidden');
    document.getElementById('btn-importar').disabled = true;
}

// Fechar modal de importação
function fecharModalImportacao() {
    document.getElementById('modal-importacao').classList.add('hidden');
}

// Baixar template Excel
function baixarTemplate() {
    window.location.href = '/controle-descartes/template';
}

// Event listener para arquivo selecionado
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('arquivo-importacao');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                previewArquivo(file);
            }
        });
    }
});

// Preview do arquivo
function previewArquivo(file) {
    console.log('Preview arquivo:', file.name);
    
    // Validar tipo de arquivo
    const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                          'application/vnd.ms-excel', 
                          'text/csv'];
    
    if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
        alert('Formato de arquivo inválido. Use .xlsx, .xls ou .csv');
        document.getElementById('arquivo-importacao').value = '';
        return;
    }
    
    // Validar tamanho (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('Arquivo muito grande. Máximo 5MB permitido.');
        document.getElementById('arquivo-importacao').value = '';
        return;
    }
    
    // Mostrar preview simples
    document.getElementById('preview-importacao').classList.remove('hidden');
    document.getElementById('preview-count').textContent = `Arquivo selecionado: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
    document.getElementById('preview-content').innerHTML = '<p class="text-gray-600">Clique em "Importar Dados" para processar o arquivo</p>';
    document.getElementById('btn-importar').disabled = false;
}

// Processar importação
function processarImportacao() {
    const fileInput = document.getElementById('arquivo-importacao');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Selecione um arquivo para importar');
        return;
    }
    
    // Mostrar progress bar
    document.getElementById('progress-importacao').classList.remove('hidden');
    document.getElementById('progress-bar').style.width = '0%';
    document.getElementById('progress-text').textContent = 'Enviando arquivo...';
    document.getElementById('btn-importar').disabled = true;
    
    const formData = new FormData();
    formData.append('arquivo', file);
    
    // Simular progresso
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 10;
        if (progress <= 50) {
            document.getElementById('progress-bar').style.width = progress + '%';
        }
    }, 200);
    
    fetch('/controle-descartes/importar', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        document.getElementById('progress-bar').style.width = '100%';
        
        if (data.success) {
            document.getElementById('progress-text').textContent = `✅ Sucesso! ${data.imported} registros importados.`;
            
            if (data.errors && data.errors.length > 0) {
                const errorMsg = `\n\nAvisos:\n${data.errors.join('\n')}`;
                alert(`Importação concluída com ${data.imported} registros.${errorMsg}`);
            } else {
                alert(`Importação concluída! ${data.imported} registros importados com sucesso.`);
            }
            
            setTimeout(() => {
                fecharModalImportacao();
                carregarDescartes();
            }, 2000);
        } else {
            document.getElementById('progress-text').textContent = '❌ Erro na importação';
            alert('Erro ao importar: ' + data.message);
            document.getElementById('btn-importar').disabled = false;
        }
    })
    .catch(error => {
        clearInterval(progressInterval);
        console.error('Erro:', error);
        document.getElementById('progress-text').textContent = '❌ Erro ao processar arquivo';
        alert('Erro ao processar importação');
        document.getElementById('btn-importar').disabled = false;
    });
}
</script>
