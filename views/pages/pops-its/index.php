<?php
// Helpers protegidos contra redeclaração (servidor já carrega helpers.php)
if (!function_exists('hasPermission')) {
    function hasPermission($module, $action = 'view') {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        // Admin tem acesso total
        try {
            if (\App\Services\PermissionService::isAdmin((int)$_SESSION['user_id'])) {
                return true;
            }
        } catch (\Throwable $e) {}

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

// Verificar permissões para cada aba
$canViewCadastroTitulos = hasPermission('pops_its_cadastro_titulos', 'view');
$canViewMeusRegistros = hasPermission('pops_its_meus_registros', 'view');
$canViewPendenteAprovacao = hasPermission('pops_its_pendente_aprovacao', 'view');
$canViewVisualizacao = hasPermission('pops_its_visualizacao', 'view');
// Detectar admin de forma robusta para garantir acesso
$isAdmin = false;
try {
    if (isset($_SESSION['user_id']) && \App\Services\PermissionService::isAdmin((int)$_SESSION['user_id'])) {
        $isAdmin = true;
    }
} catch (\Throwable $e) {
    // Ignorar e tentar via sessão
}
$profileName = $_SESSION['profile'] ?? ($_SESSION['user_profile']['profile_name'] ?? null);
if (!$isAdmin && $profileName) {
    $isAdmin = in_array(strtolower($profileName), ['administrador','admin','administrator']);
}

// Aba de solicitações visível apenas para admin
$canViewSolicitacoes = $isAdmin;

// Se nenhuma aba estiver liberada (por algum motivo de permissão), habilitar Meus Registros como fallback
if (!$canViewCadastroTitulos && !$canViewMeusRegistros && !$canViewPendenteAprovacao && !$canViewVisualizacao) {
    $canViewMeusRegistros = true;
}
?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">POPs e ITs</h1>
  </div>

  <!-- Navegação por Abas -->
  <div class="bg-white rounded-lg shadow">
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
        <?php if ($canViewCadastroTitulos): ?>
        <button id="tab-cadastro" class="tab-button active border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
          📚 Cadastro de Títulos
        </button>
        <?php endif; ?>
        
        <?php if ($canViewMeusRegistros): ?>
        <button id="tab-registros" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
          📝 Meus Registros
        </button>
        <?php endif; ?>
        
        <?php if ($canViewPendenteAprovacao): ?>
        <button id="tab-pendentes" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
          ⏳ Pendente Aprovação
        </button>
        <?php endif; ?>
        
        <?php if ($canViewVisualizacao): ?>
        <button id="tab-visualizacao" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
          👁️ Visualização
        </button>
        <?php endif; ?>
      </nav>
    </div>

    <!-- Conteúdo das Abas -->
    <div class="p-6">
      
      <!-- ABA 1: CADASTRO DE TÍTULOS -->
      <?php if ($canViewCadastroTitulos): ?>
      <div id="content-cadastro" class="tab-content">
        <div class="mb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Cadastrar Novo Título</h3>
          
          <form id="formCadastroTitulo" class="space-y-4" data-ajax="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título do POP ou IT *</label>
                <input type="text" name="titulo" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Digite o título...">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Departamento *</label>
                <select name="departamento_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">Selecione...</option>
                  <?php foreach ($departamentos as $dept): ?>
                    <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                Cadastrar Título
              </button>
            </div>
          </form>
        </div>

        <!-- Lista de Títulos -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h4 class="font-medium text-gray-900 mb-3">Títulos Cadastrados</h4>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-3 py-2 text-left">Título</th>
                  <th class="px-3 py-2 text-left">Departamento</th>
                  <th class="px-3 py-2 text-left">Criado por</th>
                  <th class="px-3 py-2 text-left">Data</th>
                </tr>
              </thead>
              <tbody id="listaTitulos" class="divide-y">
                <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">Carregando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ABA 2: MEUS REGISTROS -->
      <?php if ($canViewMeusRegistros): ?>
      <div id="content-registros" class="tab-content hidden">
        <div class="mb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Novo Registro</h3>
          
          <form id="formCriarRegistro" class="space-y-4" enctype="multipart/form-data" data-ajax="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                <select name="titulo_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">Selecione um título...</option>
                  <?php foreach ($titulos as $titulo): ?>
                    <option value="<?= $titulo['id'] ?>"><?= e($titulo['titulo']) ?> (<?= e($titulo['departamento_nome']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo * (PDF, PNG, JPEG, PPT - Max 10MB)</label>
                <input type="file" name="arquivo" required accept=".pdf,.png,.jpg,.jpeg,.ppt,.pptx" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Visualização</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="radio" name="visibilidade" value="publico" class="mr-2">
                  <span class="text-sm">Público (todos os usuários podem ver)</span>
                </label>
                <label class="flex items-center">
                  <input type="radio" name="visibilidade" value="departamentos" checked class="mr-2">
                  <span class="text-sm">Departamentos específicos</span>
                </label>
              </div>
            </div>

            <div id="departamentosSection" class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Departamentos Permitidos</label>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-32 overflow-y-auto border border-gray-200 rounded p-2">
                <?php foreach ($departamentos as $dept): ?>
                <label class="flex items-center text-sm">
                  <input type="checkbox" name="departamentos_permitidos[]" value="<?= $dept['id'] ?>" class="mr-2">
                  <?= e($dept['nome']) ?>
                </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="flex justify-end">
              <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                Registrar
              </button>
            </div>
          </form>
        </div>

        <!-- Lista de Meus Registros -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h4 class="font-medium text-gray-900 mb-3">Meus Registros</h4>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm bg-white rounded-lg shadow">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-3 py-2 text-left">Título</th>
                  <th class="px-3 py-2 text-left">Versão</th>
                  <th class="px-3 py-2 text-left">Status</th>
                  <th class="px-3 py-2 text-left">Arquivo</th>
                  <th class="px-3 py-2 text-left">Tamanho</th>
                  <th class="px-3 py-2 text-left">Data Criação</th>
                  <th class="px-3 py-2 text-left">Ações</th>
                </tr>
              </thead>
              <tbody id="listaMeusRegistros" class="divide-y">
                <tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Carregando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ABA 3: PENDENTE APROVAÇÃO -->
      <?php if ($canViewPendenteAprovacao): ?>
      <div id="content-pendentes" class="tab-content hidden">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Registros Pendentes de Aprovação</h3>
        
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm bg-white rounded-lg shadow">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-3 py-2 text-left">Título</th>
                <th class="px-3 py-2 text-left">Versão</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-left">Arquivo</th>
                <th class="px-3 py-2 text-left">Tamanho</th>
                <th class="px-3 py-2 text-left">Data Criação</th>
                <th class="px-3 py-2 text-left">Ações</th>
              </tr>
            </thead>
            <tbody id="listaPendentes" class="divide-y">
              <tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Carregando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- ABA 4: VISUALIZAÇÃO -->
      <?php if ($canViewVisualizacao): ?>
      <div id="content-visualizacao" class="tab-content hidden">
        <h3 class="text-lg font-medium text-gray-900 mb-4">POPs e ITs Aprovados</h3>
        
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm bg-white rounded-lg">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-3 py-2 text-left">Título</th>
                <th class="px-3 py-2 text-left">Versão</th>
                <th class="px-3 py-2 text-left">Departamento</th>
                <th class="px-3 py-2 text-left">Criado por</th>
                <th class="px-3 py-2 text-left">Data Aprovação</th>
                <th class="px-3 py-2 text-left">Arquivo</th>
              </tr>
            </thead>
            <tbody id="listaVisualizacao" class="divide-y">
              <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Carregando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- ABA 5: SOLICITAÇÕES DE EXCLUSÃO -->
      <?php if ($canViewSolicitacoes): ?>
      <div id="content-solicitacoes" class="tab-content hidden">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Solicitações de Exclusão</h3>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm bg-white rounded-lg shadow">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-3 py-2 text-left">Título</th>
                <th class="px-3 py-2 text-left">Versão</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-left">Solicitante</th>
                <th class="px-3 py-2 text-left">Data</th>
                <th class="px-3 py-2 text-left">Tipo</th>
                <th class="px-3 py-2 text-left">Ações</th>
              </tr>
            </thead>
            <tbody id="listaSolicitacoes" class="divide-y">
              <tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Carregando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

<!-- Modal de Reprovação de Solicitação -->
<div id="modalReprovarSolicitacao" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
  <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Reprovar Solicitação</h3>
    <form id="formReprovarSolicitacao">
      <input type="hidden" id="reprovarSolicitacaoId" name="solicitacao_id">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Observação da Reprovação *</label>
        <textarea name="observacao" required rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Descreva o motivo da reprovação..."></textarea>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="fecharModalReprovarSolicitacao()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</button>
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Reprovar</button>
      </div>
    </form>
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
        loadTabData(tabId);
      }
    });
  });
  
  // Carregar primeira aba ativa (fallback para a primeira visível caso nenhuma esteja ativa)
  let firstTab = document.querySelector('.tab-button.active');
  if (firstTab) {
    const tabId = firstTab.id.replace('tab-', '');
    loadTabData(tabId);
  } else {
    const firstVisible = document.querySelector('.tab-button');
    if (firstVisible) {
      firstVisible.classList.add('active', 'border-blue-500', 'text-blue-600');
      firstVisible.classList.remove('border-transparent', 'text-gray-500');
      const tabId = firstVisible.id.replace('tab-', '');
      const activeContent = document.getElementById(`content-${tabId}`);
      if (activeContent) activeContent.classList.remove('hidden');
      loadTabData(tabId);
    }
  }
  
  // Configurar formulários
  setupForms();
  
  // Configurar visibilidade de departamentos
  setupVisibilidadeToggle();
});

function loadTabData(tabId) {
  switch(tabId) {
    case 'cadastro':
      loadTitulos();
      break;
    case 'registros':
      loadMeusRegistros();
      break;
    case 'pendentes':
      loadPendentesAprovacao();
      break;
    case 'visualizacao':
      loadVisualizacao();
      break;
    case 'solicitacoes':
      loadSolicitacoes();
      break;
  }
}

function setupForms() {
  // Form Cadastro Título
  const formTitulo = document.getElementById('formCadastroTitulo');
  if (formTitulo) {
    formTitulo.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      try {
        const formData = new FormData(formTitulo);
        const response = await fetch('/pops-its/titulo/create', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          alert('Título cadastrado com sucesso!');
          formTitulo.reset();
          loadTitulos(); // Atualizar lista de títulos
          updateTitulosDropdown(); // Atualizar dropdown na aba de registros
        } else {
          alert('Erro: ' + result.message);
        }
      } catch (error) {
        console.error('Erro ao cadastrar título:', error);
        alert('Erro ao cadastrar título');
      }
    });
  }
  
  // Form Criar Registro
  const formRegistro = document.getElementById('formCriarRegistro');
  if (formRegistro) {
    formRegistro.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      try {
        const formData = new FormData(formRegistro);
        const response = await fetch('/pops-its/registro/create', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          alert('Registro criado com sucesso!');
          formRegistro.reset();
          loadMeusRegistros(); // Atualizar grid
        } else {
          alert('Erro: ' + result.message);
        }
      } catch (error) {
        console.error('Erro ao criar registro:', error);
        alert('Erro ao criar registro');
      }
    });
  }
  
  // Form Reprovação de Solicitação
  const formReprovarSolicitacao = document.getElementById('formReprovarSolicitacao');
  if (formReprovarSolicitacao) {
    formReprovarSolicitacao.addEventListener('submit', async (e) => {
      e.preventDefault();

      try {
        const formData = new FormData(formReprovarSolicitacao);
        const response = await fetch('/pops-its/solicitacao/reprovar', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          alert('Solicitação reprovada com sucesso!');
          fecharModalReprovarSolicitacao();
          loadSolicitacoes(); // Atualizar lista de solicitações
        } else {
          alert('Erro: ' + result.message);
        }
      } catch (error) {
        console.error('Erro ao reprovar solicitação:', error);
        alert('Erro ao reprovar solicitação');
      }
    });
  }
}

function setupVisibilidadeToggle() {
  const radioButtons = document.querySelectorAll('input[name="visibilidade"]');
  const departamentosSection = document.getElementById('departamentosSection');
  
  radioButtons.forEach(radio => {
    radio.addEventListener('change', () => {
      if (radio.value === 'publico') {
        departamentosSection.style.display = 'none';
      } else {
        departamentosSection.style.display = 'block';
      }
    });
  });
}

async function submitForm(url, formData, onSuccess) {
  const overlay = document.getElementById('loadingOverlay');
  try {
    if (overlay) overlay.classList.add('active');
    
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      if (onSuccess) onSuccess();
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro:', error);
    alert('Erro ao processar solicitação');
  } finally {
    if (overlay) overlay.classList.remove('active');
  }
}

// Funções de carregamento de dados
async function loadTitulos() {
  try {
    const response = await fetch('/pops-its/titulos/list');
    const result = await response.json();
    
    const tbody = document.getElementById('listaTitulos');
    if (!result.success || !result.data.length) {
      tbody.innerHTML = '<tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">Nenhum título cadastrado</td></tr>';
      return;
    }
    
    tbody.innerHTML = result.data.map(titulo => `
      <tr>
        <td class="px-3 py-2">${titulo.titulo}</td>
        <td class="px-3 py-2">${titulo.departamento_nome}</td>
        <td class="px-3 py-2">${titulo.criador_nome}</td>
        <td class="px-3 py-2">${formatDate(titulo.created_at)}</td>
      </tr>
    `).join('');
  } catch (error) {
    console.error('Erro ao carregar títulos:', error);
  }
}

async function loadMeusRegistros() {
  try {
    const response = await fetch('/pops-its/registros/meus', {
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    const text = await response.text();
    let result;
    try {
      result = text ? JSON.parse(text) : { success: false, message: 'Resposta vazia do servidor' };
    } catch (e) {
      result = { success: false, message: 'Resposta não é JSON válido', raw: text };
    }
    
    const tbody = document.getElementById('listaMeusRegistros');
    
    if (!result.success) {
      console.error('Erro na resposta:', result.message);
      tbody.innerHTML = `<tr><td colspan="7" class="px-3 py-4 text-center text-red-500">Erro: ${result.message}</td></tr>`;
      return;
    }

    if (!result.data || result.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Nenhum registro encontrado</td></tr>';
      return;
    }

    tbody.innerHTML = result.data.map(registro => {
      // Formatar tamanho do arquivo
      const formatFileSize = (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
      };

      return `
        <tr>
          <td class="px-3 py-2">${registro.titulo || 'Título não encontrado'}</td>
          <td class="px-3 py-2">${registro.versao || 'N/A'}</td>
          <td class="px-3 py-2">
            <span class="px-2 py-1 text-xs rounded-full ${getStatusColor(registro.status)}">
              ${getStatusText(registro.status)}
            </span>
            ${registro.observacao_reprovacao ? `<br><small class="text-red-600">${registro.observacao_reprovacao}</small>` : ''}
          </td>
          <td class="px-3 py-2">
            <div class="flex flex-col">
              <span class="font-medium">${registro.arquivo_nome || 'Arquivo não informado'}</span>
              <span class="text-xs text-gray-500">${registro.arquivo_tipo || 'Tipo desconhecido'}</span>
            </div>
          </td>
          <td class="px-3 py-2">${formatFileSize(registro.arquivo_tamanho || 0)}</td>
          <td class="px-3 py-2">${formatDate(registro.created_at)}</td>
          <td class="px-3 py-2 space-x-1">
            <a href="/pops-its/arquivo/${registro.id}" target="_blank" class="text-blue-600 hover:underline text-xs">
              Ver/Download
            </a>
            ${registro.status === 'reprovado' ? `<br><button onclick="atualizarRegistro(${registro.id})" class="text-green-600 hover:underline text-xs mt-1">Atualizar</button>` : ''}
            <br><button onclick="excluirRegistro(${registro.id})" class="text-red-600 hover:underline text-xs mt-1">Excluir</button>
          </td>
        </tr>
      `;
    }).join('');
  } catch (error) {
    console.error('Erro ao carregar registros:', error);
    const tbody = document.getElementById('listaMeusRegistros');
    tbody.innerHTML = `<tr><td colspan="7" class="px-3 py-4 text-center text-red-500">Erro ao carregar: ${error.message}</td></tr>`;
  }
}

async function loadPendentesAprovacao() {
  try {
    const response = await fetch('/pops-its/pendentes/list', {
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    const text = await response.text();
    let result;
    try {
      result = text ? JSON.parse(text) : { success: false, message: 'Resposta vazia do servidor' };
    } catch (e) {
      result = { success: false, message: 'Resposta não é JSON válido', raw: text };
    }
    
    const tbody = document.getElementById('listaPendentes');
    if (!result.success) {
      console.error('Erro na resposta:', result.message);
      tbody.innerHTML = `<tr><td colspan="7" class="px-3 py-4 text-center text-red-500">Erro: ${result.message}</td></tr>`;
      return;
    }

    if (!result.data || result.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Nenhum registro pendente</td></tr>';
      return;
    }

    tbody.innerHTML = result.data.map(registro => {
      // Formatar tamanho do arquivo
      const formatFileSize = (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
      };

      return `
        <tr>
          <td class="px-3 py-2">${registro.titulo || 'Título não encontrado'}</td>
          <td class="px-3 py-2">${registro.versao || 'N/A'}</td>
          <td class="px-3 py-2">
            <span class="px-2 py-1 text-xs rounded-full ${getStatusColor(registro.status)}">
              ${getStatusText(registro.status)}
            </span>
          </td>
          <td class="px-3 py-2">
            <div class="flex flex-col">
              <span class="font-medium">${registro.arquivo_nome || 'Arquivo não informado'}</span>
              <span class="text-xs text-gray-500">${registro.arquivo_tipo || 'Tipo desconhecido'}</span>
            </div>
          </td>
          <td class="px-3 py-2">${formatFileSize(registro.arquivo_tamanho || 0)}</td>
          <td class="px-3 py-2">${formatDate(registro.created_at)}</td>
          <td class="px-3 py-2 space-x-1">
            <button onclick="aprovarRegistro(${registro.id})" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">Aprovar</button>
            <button onclick="abrirModalReprovacao(${registro.id})" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">Reprovar</button>
          </td>
        </tr>
      `;
    }).join('');
  } catch (error) {
    console.error('Erro ao carregar pendentes:', error);
  }
}

async function loadSolicitacoes() {
  try {
    const response = await fetch('/pops-its/solicitacoes/list', {
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    const text = await response.text();
    let result;
    try {
      result = text ? JSON.parse(text) : { success: false, message: 'Resposta vazia do servidor' };
    } catch (e) {
      result = { success: false, message: 'Resposta não é JSON válido', raw: text };
    }

    const tbody = document.getElementById('listaSolicitacoes');
    if (!result.success) {
      console.error('Erro na resposta:', result.message);
      tbody.innerHTML = `<tr><td colspan="7" class="px-3 py-4 text-center text-red-500">Erro: ${result.message}</td></tr>`;
      return;
    }

    if (!result.data || result.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Nenhuma solicitação pendente</td></tr>';
      return;
    }

    tbody.innerHTML = result.data.map(solicitacao => {
      const statusColor = solicitacao.status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800';
      const statusText = solicitacao.status === 'pendente' ? 'Pendente' : 'Processada';

      return `
        <tr>
          <td class="px-3 py-2">${solicitacao.titulo || 'Título não encontrado'}</td>
          <td class="px-3 py-2">${solicitacao.versao || 'N/A'}</td>
          <td class="px-3 py-2">
            <span class="px-2 py-1 text-xs rounded-full ${statusColor}">
              ${statusText}
            </span>
          </td>
          <td class="px-3 py-2">${solicitacao.solicitante_nome}</td>
          <td class="px-3 py-2">${formatDate(solicitacao.created_at)}</td>
          <td class="px-3 py-2">
            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
              ${solicitacao.tipo_solicitacao === 'exclusao' ? 'Exclusão' : solicitacao.tipo_solicitacao}
            </span>
          </td>
          <td class="px-3 py-2 space-x-1">
            ${solicitacao.status === 'pendente' ? `
              <button onclick="aprovarSolicitacao(${solicitacao.id})" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">Aprovar</button>
              <button onclick="abrirModalReprovarSolicitacao(${solicitacao.id})" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">Reprovar</button>
            ` : 'Processada'}
          </td>
        </tr>
      `;
    }).join('');
  } catch (error) {
    console.error('Erro ao carregar solicitações:', error);
    const tbody = document.getElementById('listaSolicitacoes');
    tbody.innerHTML = `<tr><td colspan="7" class="px-3 py-4 text-center text-red-500">Erro ao carregar: ${error.message}</td></tr>`;
  }
}

// Funções auxiliares
function getStatusColor(status) {
  switch(status) {
    case 'pendente': return 'bg-yellow-100 text-yellow-800';
    case 'aprovado': return 'bg-green-100 text-green-800';
    case 'reprovado': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
}

function getStatusText(status) {
  switch(status) {
    case 'pendente': return 'Pendente';
    case 'aprovado': return 'Aprovado';
    case 'reprovado': return 'Reprovado';
    default: return status;
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

// Ações específicas
async function aprovarRegistro(id) {
  if (!confirm('Tem certeza que deseja aprovar este registro?')) return;
  
  try {
    const formData = new FormData();
    formData.append('registro_id', id);
    
    const response = await fetch('/pops-its/registro/aprovar', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert('Registro aprovado com sucesso!');
      loadPendentesAprovacao(); // Atualizar lista de pendentes
      loadVisualizacao(); // Atualizar lista de visualização
      loadMeusRegistros(); // Atualizar lista de meus registros (sincronizar status)
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao aprovar registro:', error);
    alert('Erro ao aprovar registro');
  }
}

async function aprovarSolicitacao(id) {
  if (!confirm('Tem certeza que deseja APROVAR esta solicitação? Esta ação excluirá o registro permanentemente.')) return;

  try {
    const formData = new FormData();
    formData.append('solicitacao_id', id);

    const response = await fetch('/pops-its/solicitacao/aprovar', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      alert('Solicitação aprovada e executada com sucesso!');
      loadSolicitacoes(); // Atualizar lista de solicitações
      loadMeusRegistros(); // Atualizar lista de meus registros (caso tenha excluído algum)
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao aprovar solicitação:', error);
    alert('Erro ao aprovar solicitação');
  }
}

function fecharModalReprovarSolicitacao() {
  document.getElementById('modalReprovarSolicitacao').classList.add('hidden');
  document.getElementById('formReprovarSolicitacao').reset();
}

async function excluirRegistro(id) {
  try {
    // Primeiro, buscar informações do registro para verificar o status
    const response = await fetch(`/pops-its/registros/meus`, {
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });

    const text = await response.text();
    let result;
    try {
      result = text ? JSON.parse(text) : { success: false, message: 'Resposta vazia do servidor' };
    } catch (e) {
      result = { success: false, message: 'Resposta não é JSON válido', raw: text };
    }

    if (!result.success || !result.data) {
      alert('Erro ao verificar status do registro');
      return;
    }

    const registro = result.data.find(r => r.id === id);
    if (!registro) {
      alert('Registro não encontrado');
      return;
    }

    // Verificar se é reprovado (pode excluir diretamente) ou aprovado (solicitar exclusão)
    if (registro.status === 'reprovado') {
      // Exclusão direta para reprovados
      if (!confirm('Tem certeza que deseja excluir este registro reprovado?')) return;

      const formData = new FormData();
      formData.append('registro_id', id);

      const deleteResponse = await fetch('/pops-its/registro/delete', {
        method: 'POST',
        body: formData
      });

      const deleteResult = await deleteResponse.json();

      if (deleteResult.success) {
        alert('Registro excluído com sucesso!');
        loadMeusRegistros(); // Atualizar lista
      } else {
        alert('Erro: ' + deleteResult.message);
      }
    } else if (registro.status === 'aprovado') {
      // Solicitar exclusão para aprovados
      if (!confirm('Este registro está APROVADO. Deseja solicitar exclusão ao administrador?')) return;

      const formData = new FormData();
      formData.append('registro_id', id);
      formData.append('tipo_solicitacao', 'exclusao');
      formData.append('justificativa', 'Solicitação de exclusão pelo usuário');

      const solicitacaoResponse = await fetch('/pops-its/solicitacao/create', {
        method: 'POST',
        body: formData
      });

      const solicitacaoResult = await solicitacaoResponse.json();

      if (solicitacaoResult.success) {
        alert('Solicitação de exclusão enviada! Aguardando aprovação do administrador.');
      } else {
        alert('Erro: ' + solicitacaoResult.message);
      }
    } else {
      alert('Não é possível excluir registros com status PENDENTE. Aguarde a aprovação ou reprovação.');
    }
  } catch (error) {
    console.error('Erro ao processar exclusão:', error);
    alert('Erro ao processar exclusão');
  }
}

function atualizarRegistro(id) {
  // Implementar modal de atualização de arquivo
  alert('Funcionalidade de atualização em desenvolvimento');
}

// Atualizar dropdown de títulos na aba de registros
async function updateTitulosDropdown() {
  try {
    const response = await fetch('/pops-its/titulos/list');
    const result = await response.json();
    
    if (result.success) {
      const select = document.querySelector('select[name="titulo_id"]');
      if (select) {
        select.innerHTML = '<option value="">Selecione um título...</option>';
        result.data.forEach(titulo => {
          const option = document.createElement('option');
          option.value = titulo.id;
          option.textContent = `${titulo.titulo} (${titulo.departamento_nome})`;
          select.appendChild(option);
        });
      }
    }
  } catch (error) {
    console.error('Erro ao atualizar dropdown de títulos:', error);
  }
}
</script>
