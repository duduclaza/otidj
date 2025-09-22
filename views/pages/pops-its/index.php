<?php
// Function to check if user has permission
function hasPermission($module, $action = 'view') {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Admin has all permissions - verificar ambos os formatos
    $profile = $_SESSION['profile'] ?? $_SESSION['user_profile']['profile_name'] ?? null;
    if ($profile === 'Administrador') {
        return true;
    }
    
    $permissions = $_SESSION['permissions'] ?? $_SESSION['user_profile']['permissions'] ?? [];
    if (empty($permissions)) {
        return false;
    }
    
    foreach ($permissions as $permission) {
        if ($permission['module'] === $module) {
            switch ($action) {
                case 'view': return (bool)$permission['can_view'];
                case 'edit': return (bool)$permission['can_edit'];
                case 'delete': return (bool)$permission['can_delete'];
                case 'import': return (bool)$permission['can_import'];
                case 'export': return (bool)$permission['can_export'];
            }
        }
    }
    
    return false;
}

// Function to escape HTML
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Verificar permissões para cada aba
$canViewCadastroTitulos = hasPermission('pops_its_cadastro_titulos', 'view');
$canViewMeusRegistros = hasPermission('pops_its_meus_registros', 'view');
$canViewPendenteAprovacao = hasPermission('pops_its_pendente_aprovacao', 'view');
$canViewVisualizacao = hasPermission('pops_its_visualizacao', 'view');
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
            <table class="min-w-full text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-3 py-2 text-left">Título</th>
                  <th class="px-3 py-2 text-left">Versão</th>
                  <th class="px-3 py-2 text-left">Status</th>
                  <th class="px-3 py-2 text-left">Data</th>
                  <th class="px-3 py-2 text-left">Ações</th>
                </tr>
              </thead>
              <tbody id="listaMeusRegistros" class="divide-y">
                <tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Carregando...</td></tr>
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
          <table class="min-w-full text-sm bg-white rounded-lg">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-3 py-2 text-left">Título</th>
                <th class="px-3 py-2 text-left">Versão</th>
                <th class="px-3 py-2 text-left">Criado por</th>
                <th class="px-3 py-2 text-left">Data</th>
                <th class="px-3 py-2 text-left">Arquivo</th>
                <th class="px-3 py-2 text-left">Ações</th>
              </tr>
            </thead>
            <tbody id="listaPendentes" class="divide-y">
              <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Carregando...</td></tr>
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
    </div>
  </div>
</section>

<!-- Modal de Reprovação -->
<div id="modalReprovacao" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
  <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Reprovar Registro</h3>
    <form id="formReprovacao">
      <input type="hidden" id="reprovarRegistroId" name="registro_id">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Observação da Reprovação *</label>
        <textarea name="observacao" required rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Descreva o motivo da reprovação..."></textarea>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="fecharModalReprovacao()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</button>
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
  
  // Carregar primeira aba ativa
  const firstTab = document.querySelector('.tab-button.active');
  if (firstTab) {
    const tabId = firstTab.id.replace('tab-', '');
    loadTabData(tabId);
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
  }
}

function setupForms() {
  // Form Cadastro Título
  const formTitulo = document.getElementById('formCadastroTitulo');
  if (formTitulo) {
    formTitulo.addEventListener('submit', async (e) => {
      e.preventDefault();
      await submitForm('/pops-its/titulo/create', new FormData(formTitulo), () => {
        formTitulo.reset();
        loadTitulos();
      });
    });
  }
  
  // Form Criar Registro
  const formRegistro = document.getElementById('formCriarRegistro');
  if (formRegistro) {
    formRegistro.addEventListener('submit', async (e) => {
      e.preventDefault();
      await submitForm('/pops-its/registro/create', new FormData(formRegistro), () => {
        formRegistro.reset();
        loadMeusRegistros();
      });
    });
  }
  
  // Form Reprovação
  const formReprovacao = document.getElementById('formReprovacao');
  if (formReprovacao) {
    formReprovacao.addEventListener('submit', async (e) => {
      e.preventDefault();
      await submitForm('/pops-its/registro/reprovar', new FormData(formReprovacao), () => {
        fecharModalReprovacao();
        loadPendentesAprovacao();
      });
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
    const response = await fetch('/pops-its/registros/meus');
    const result = await response.json();
    
    const tbody = document.getElementById('listaMeusRegistros');
    if (!result.success || !result.data.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Nenhum registro encontrado</td></tr>';
      return;
    }
    
    tbody.innerHTML = result.data.map(registro => `
      <tr>
        <td class="px-3 py-2">${registro.titulo}</td>
        <td class="px-3 py-2">${registro.versao}</td>
        <td class="px-3 py-2">
          <span class="px-2 py-1 text-xs rounded-full ${getStatusColor(registro.status)}">
            ${getStatusText(registro.status)}
          </span>
          ${registro.observacao_reprovacao ? `<br><small class="text-red-600">${registro.observacao_reprovacao}</small>` : ''}
        </td>
        <td class="px-3 py-2">${formatDate(registro.created_at)}</td>
        <td class="px-3 py-2 space-x-1">
          <a href="/pops-its/arquivo/${registro.id}" target="_blank" class="text-blue-600 hover:underline text-xs">Ver</a>
          ${registro.status === 'reprovado' ? `<button onclick="atualizarRegistro(${registro.id})" class="text-green-600 hover:underline text-xs">Atualizar</button>` : ''}
          <button onclick="excluirRegistro(${registro.id})" class="text-red-600 hover:underline text-xs">Excluir</button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    console.error('Erro ao carregar registros:', error);
  }
}

async function loadPendentesAprovacao() {
  try {
    const response = await fetch('/pops-its/pendentes/list');
    const result = await response.json();
    
    const tbody = document.getElementById('listaPendentes');
    if (!result.success || !result.data.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Nenhum registro pendente</td></tr>';
      return;
    }
    
    tbody.innerHTML = result.data.map(registro => `
      <tr>
        <td class="px-3 py-2">${registro.titulo}</td>
        <td class="px-3 py-2">${registro.versao}</td>
        <td class="px-3 py-2">${registro.criador_nome}</td>
        <td class="px-3 py-2">${formatDate(registro.created_at)}</td>
        <td class="px-3 py-2">
          <a href="/pops-its/arquivo/${registro.id}" target="_blank" class="text-blue-600 hover:underline">Baixar</a>
        </td>
        <td class="px-3 py-2 space-x-1">
          <button onclick="aprovarRegistro(${registro.id})" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">Aprovar</button>
          <button onclick="abrirModalReprovacao(${registro.id})" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">Reprovar</button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    console.error('Erro ao carregar pendentes:', error);
  }
}

async function loadVisualizacao() {
  try {
    const response = await fetch('/pops-its/visualizacao/list');
    const result = await response.json();
    
    const tbody = document.getElementById('listaVisualizacao');
    if (!result.success || !result.data.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Nenhum documento disponível</td></tr>';
      return;
    }
    
    tbody.innerHTML = result.data.map(registro => `
      <tr>
        <td class="px-3 py-2">${registro.titulo}</td>
        <td class="px-3 py-2">${registro.versao}</td>
        <td class="px-3 py-2">${registro.departamento_nome}</td>
        <td class="px-3 py-2">${registro.criador_nome}</td>
        <td class="px-3 py-2">${formatDate(registro.approved_at)}</td>
        <td class="px-3 py-2">
          <a href="/pops-its/arquivo/${registro.id}" target="_blank" class="text-blue-600 hover:underline">Ver Documento</a>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    console.error('Erro ao carregar visualização:', error);
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
  
  const formData = new FormData();
  formData.append('registro_id', id);
  
  await submitForm('/pops-its/registro/aprovar', formData, () => {
    loadPendentesAprovacao();
  });
}

function abrirModalReprovacao(id) {
  document.getElementById('reprovarRegistroId').value = id;
  document.getElementById('modalReprovacao').classList.remove('hidden');
}

function fecharModalReprovacao() {
  document.getElementById('modalReprovacao').classList.add('hidden');
  document.getElementById('formReprovacao').reset();
}

async function excluirRegistro(id) {
  if (!confirm('Tem certeza que deseja excluir este registro?')) return;
  
  const formData = new FormData();
  formData.append('registro_id', id);
  
  await submitForm('/pops-its/registro/delete', formData, () => {
    loadMeusRegistros();
  });
}

function atualizarRegistro(id) {
  // Implementar modal de atualização de arquivo
  alert('Funcionalidade de atualização em desenvolvimento');
}
</script>
