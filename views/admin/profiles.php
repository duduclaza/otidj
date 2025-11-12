<style>
/* Toggle Switch Moderno */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 24px;
  vertical-align: middle;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #cbd5e1;
  transition: 0.3s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
  background-color: #3b82f6;
}

.toggle-switch input:focus + .toggle-slider {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(24px);
}

.toggle-switch:hover .toggle-slider {
  background-color: #94a3b8;
}

.toggle-switch input:checked:hover + .toggle-slider {
  background-color: #2563eb;
}

/* Animação suave */
.toggle-slider,
.toggle-slider:before {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Centralizar toggles nas células */
#permissionsGrid > div {
  align-items: center !important;
}

#permissionsGrid > div > div {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
}
</style>

<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Gerenciar Perfis</h1>
    <button onclick="toggleProfileForm()" id="toggleFormBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
      </svg>
      <span>Novo Perfil</span>
    </button>
  </div>

  <!-- Profile Form -->
  <div id="profileFormContainer" class="hidden bg-white rounded-lg shadow-lg border border-gray-200 p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 id="formTitle" class="text-lg font-semibold text-gray-900">Criar Novo Perfil</h3>
      <button onclick="cancelProfileForm()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="profileForm" class="space-y-6">
      <input type="hidden" id="profileId" name="id">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Perfil *</label>
          <input type="text" id="profileName" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <input type="checkbox" id="isDefault" name="is_default" class="mr-2">
            Perfil Padrão
          </label>
          <p class="text-xs text-gray-500">Novos usuários receberão este perfil automaticamente</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
        <textarea id="profileDescription" name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Descreva as responsabilidades deste perfil..."></textarea>
      </div>

      <!-- Permissions Section -->
      <div>
        <h4 class="text-md font-semibold text-gray-900 mb-4">Permissões do Perfil</h4>
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="overflow-x-auto">
            <div class="min-w-full">
              <div class="grid grid-cols-6 gap-2 mb-4 min-w-max">
                <div class="font-semibold text-gray-700 text-sm">Módulo</div>
                <div class="font-semibold text-gray-700 text-center text-sm">Visualizar</div>
                <div class="font-semibold text-gray-700 text-center text-sm">Editar</div>
                <div class="font-semibold text-gray-700 text-center text-sm">Excluir</div>
                <div class="font-semibold text-gray-700 text-center text-sm">Importar</div>
                <div class="font-semibold text-gray-700 text-center text-sm">Exportar</div>
              </div>
              
              <div id="permissionsGrid">
                <!-- Permissions will be loaded here -->
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Dashboard Tabs Permissions Section -->
      <div class="mt-6">
        <h4 class="text-md font-semibold text-gray-900 mb-4">📊 Permissões de Abas do Dashboard</h4>
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-200">
          <p class="text-sm text-gray-600 mb-4">Controle quais abas do Dashboard este perfil pode visualizar</p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm">
              <div class="flex items-center gap-2">
                <span class="text-xl">📦</span>
                <span class="font-medium text-gray-900">Retornados</span>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" name="dashboard_tabs[retornados]" checked>
                <span class="toggle-slider"></span>
              </label>
            </div>
            
            <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm">
              <div class="flex items-center gap-2">
                <span class="text-xl">🧪</span>
                <span class="font-medium text-gray-900">Amostragens 2.0</span>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" name="dashboard_tabs[amostragens]" checked>
                <span class="toggle-slider"></span>
              </label>
            </div>
            
            <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm">
              <div class="flex items-center gap-2">
                <span class="text-xl">🏭</span>
                <span class="font-medium text-gray-900">Fornecedores</span>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" name="dashboard_tabs[fornecedores]" checked>
                <span class="toggle-slider"></span>
              </label>
            </div>
            
            <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm">
              <div class="flex items-center gap-2">
                <span class="text-xl">🛡️</span>
                <span class="font-medium text-gray-900">Garantias</span>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" name="dashboard_tabs[garantias]" checked>
                <span class="toggle-slider"></span>
              </label>
            </div>
            
            <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm">
              <div class="flex items-center gap-2">
                <span class="text-xl">🚀</span>
                <span class="font-medium text-gray-900">Melhorias Contínuas</span>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" name="dashboard_tabs[melhorias]" checked>
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
        <button type="button" onclick="cancelProfileForm()" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button type="button" onclick="submitProfile()" id="submitBtn" class="px-6 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Criar Perfil
        </button>
      </div>
    </form>
  </div>

  <!-- Profiles Table -->
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Lista de Perfis</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuários</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody id="profilesTableBody" class="bg-white divide-y divide-gray-200">
          <!-- Profiles will be loaded here via JavaScript -->
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
let currentProfileId = null;
const modules = [
  { key: 'dashboard', name: 'Dashboard' },
  { key: 'toners_cadastro', name: 'Cadastro de Toners' },
  { key: 'cadastro_maquinas', name: 'Cadastro de Máquinas 🖨️' },
  { key: 'cadastro_pecas', name: 'Cadastro de Peças 🔧' },
  { key: 'toners_retornados', name: 'Registro de Retornados' },
  { key: 'homologacoes', name: 'Homologações' },
  { key: 'amostragens_2', name: 'Amostragens 2.0 🔬' },
  { key: 'garantias', name: 'Garantias' },
  { key: 'controle_descartes', name: 'Controle de Descartes' },
  { key: 'fmea', name: 'FMEA' },
  { key: 'certificados', name: 'Certificados' },
  { key: 'pops_its', name: 'POPs e ITs' },
  { key: 'pops_its_cadastro_titulos', name: '→ Cadastro de Títulos' },
  { key: 'pops_its_meus_registros', name: '→ Meus Registros' },
  { key: 'pops_its_pendente_aprovacao', name: '→ Pendente Aprovação' },
  { key: 'pops_its_visualizacao', name: '→ Visualização' },
  { key: 'pops_its_solicitacoes', name: '→ Solicitações de Exclusão' },
  { key: '5w2h', name: '5W2H' },
  { key: 'fluxogramas', name: 'Fluxogramas' },
  { key: 'melhoria_continua', name: 'Melhoria Contínua' },
  { key: 'melhoria_continua_2', name: 'Melhoria Contínua 2.0 🚀' },
  { key: 'controle_rc', name: 'Controle de RC' },
  { key: 'auditorias', name: 'Auditorias' },
  { key: 'nps', name: 'NPS - Net Promoter Score 🎯' },
  { key: 'registros_filiais', name: 'Filiais' },
  { key: 'registros_departamentos', name: 'Departamentos' },
  { key: 'registros_fornecedores', name: 'Fornecedores' },
  { key: 'registros_parametros', name: 'Parâmetros de Retornados' },
  { key: 'configuracoes_gerais', name: 'Configurações Gerais' },
  { key: 'admin_usuarios', name: 'Gerenciar Usuários' },
  { key: 'admin_perfis', name: 'Gerenciar Perfis' },
  { key: 'admin_convites', name: 'Solicitações de Acesso' },
  { key: 'admin_painel', name: 'Painel Administrativo' },
  { key: 'profile', name: 'Perfil do Usuário' },
  { key: 'email_config', name: 'Configurações de Email' }
];

// Email do usuário logado (Master User pode editar tudo)
const currentUserEmail = '<?= $_SESSION['user_email'] ?? '' ?>';
const isMasterUser = currentUserEmail.toLowerCase() === 'du.claza@gmail.com';

console.log('👑 Master User Detection:');
console.log('  Email atual:', currentUserEmail);
console.log('  É Master?', isMasterUser ? '✅ SIM - GOD MODE ATIVO!' : '❌ Não');

// Load profiles on page load
document.addEventListener('DOMContentLoaded', function() {
  loadProfiles();
  generatePermissionsGrid();
});

function loadProfiles() {
  console.log('Carregando perfis...');
  fetch('/admin/profiles', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => {
    console.log('Response status:', response.status);
    return response.json();
  })
  .then(result => {
    console.log('Response data:', result);
    if (result.success) {
      // Ocultar 'Super Administrador' para quem não é Master
      const profiles = Array.isArray(result.profiles) ? result.profiles.filter(p => !(String(p.name).toLowerCase() === 'super administrador' && !isMasterUser)) : [];
      displayProfiles(profiles);
    } else {
      alert('Erro ao carregar perfis: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Erro de conexão');
  });
}

function generatePermissionsGrid() {
  const grid = document.getElementById('permissionsGrid');
  let html = '';
  
  modules.forEach(module => {
    html += `
      <div class="grid grid-cols-6 gap-2 py-3 border-b border-gray-200 items-center min-w-max">
        <div class="font-medium text-gray-900 text-sm pr-2">${module.name}</div>
        <div class="text-center">
          <label class="toggle-switch">
            <input type="checkbox" name="permissions[${module.key}][view]">
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div class="text-center">
          <label class="toggle-switch">
            <input type="checkbox" name="permissions[${module.key}][edit]">
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div class="text-center">
          <label class="toggle-switch">
            <input type="checkbox" name="permissions[${module.key}][delete]">
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div class="text-center">
          <label class="toggle-switch">
            <input type="checkbox" name="permissions[${module.key}][import]">
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div class="text-center">
          <label class="toggle-switch">
            <input type="checkbox" name="permissions[${module.key}][export]">
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>
    `;
  });
  
  grid.innerHTML = html;
}

function displayProfiles(profiles) {
  const tbody = document.getElementById('profilesTableBody');
  tbody.innerHTML = '';
  
  profiles.forEach(profile => {
    const row = document.createElement('tr');
    
    const statusBadge = profile.is_admin == 1 
      ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">Administrador</span>'
      : profile.is_default == 1 
        ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Padrão</span>'
        : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Normal</span>';
    
    // Master User (GOD MODE) pode editar qualquer perfil, incluindo Administrador
    const actions = (profile.is_admin == 1 && !isMasterUser)
      ? '<span class="text-gray-400 text-sm">Não editável</span>'
      : `
        <button onclick="editProfile(${profile.id})" class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
        <button onclick="deleteProfile(${profile.id}, '${profile.name}')" class="text-red-600 hover:text-red-900">Excluir</button>
      `;
    
    row.innerHTML = `
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
            <span class="text-white text-sm font-medium">${profile.name.charAt(0).toUpperCase()}</span>
          </div>
          <div class="ml-4">
            <div class="text-sm font-medium text-gray-900">${profile.name}</div>
          </div>
        </div>
      </td>
      <td class="px-6 py-4">
        <div class="text-sm text-gray-900">${profile.description || 'Sem descrição'}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
        ${profile.users_count} usuário(s)
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        ${statusBadge}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        ${new Date(profile.created_at).toLocaleDateString('pt-BR')}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
        ${actions}
      </td>
    `;
    tbody.appendChild(row);
  });
}

function toggleProfileForm() {
  const container = document.getElementById('profileFormContainer');
  const btn = document.getElementById('toggleFormBtn');
  
  if (container.classList.contains('hidden')) {
    // Show form for creating new profile
    container.classList.remove('hidden');
    document.getElementById('formTitle').textContent = 'Criar Novo Perfil';
    document.getElementById('submitBtn').textContent = 'Criar Perfil';
    document.getElementById('profileForm').reset();
    document.getElementById('profileId').value = '';
    generatePermissionsGrid();
    
    btn.innerHTML = `
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
      <span>Cancelar</span>
    `;
  } else {
    // Hide form
    container.classList.add('hidden');
    btn.innerHTML = `
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
      </svg>
      <span>Novo Perfil</span>
    `;
  }
}

function editProfile(profileId) {
  // Get profile data and populate form
  fetch(`/admin/profiles/${profileId}/permissions`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      const profile = result.profile;
      const permissions = result.permissions;
      
      // Show form and populate with profile data
      const container = document.getElementById('profileFormContainer');
      container.classList.remove('hidden');
      
      document.getElementById('formTitle').textContent = 'Editar Perfil';
      document.getElementById('submitBtn').textContent = 'Salvar Alterações';
      document.getElementById('profileId').value = profile.id;
      document.getElementById('profileName').value = profile.name;
      document.getElementById('profileDescription').value = profile.description || '';
      document.getElementById('isDefault').checked = profile.is_default == 1;
      
      // Generate permissions grid and set values
      generatePermissionsGrid();
      
      // Set permission checkboxes
      Object.keys(permissions).forEach(module => {
        const perm = permissions[module];
        const checkboxes = {
          view: document.querySelector(`input[name="permissions[${module}][view]"]`),
          edit: document.querySelector(`input[name="permissions[${module}][edit]"]`),
          delete: document.querySelector(`input[name="permissions[${module}][delete]"]`),
          import: document.querySelector(`input[name="permissions[${module}][import]"]`),
          export: document.querySelector(`input[name="permissions[${module}][export]"]`)
        };
        
        if (checkboxes.view) checkboxes.view.checked = perm.can_view == 1;
        if (checkboxes.edit) checkboxes.edit.checked = perm.can_edit == 1;
        if (checkboxes.delete) checkboxes.delete.checked = perm.can_delete == 1;
        if (checkboxes.import) checkboxes.import.checked = perm.can_import == 1;
        if (checkboxes.export) checkboxes.export.checked = perm.can_export == 1;
      });
      
      // Load dashboard tab permissions
      loadDashboardTabPermissions(profile.id);
      
      // Update toggle button
      const btn = document.getElementById('toggleFormBtn');
      btn.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <span>Cancelar</span>
      `;
    } else {
      alert('Erro ao carregar dados do perfil: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Erro de conexão');
  });
}

function cancelProfileForm() {
  const container = document.getElementById('profileFormContainer');
  container.classList.add('hidden');
  document.getElementById('profileForm').reset();
  
  // Reset toggle button
  const btn = document.getElementById('toggleFormBtn');
  btn.innerHTML = `
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
    </svg>
    <span>Novo Perfil</span>
  `;
}

function submitProfile() {
  const form = document.getElementById('profileForm');
  const formData = new FormData(form);
  const profileId = document.getElementById('profileId').value;
  
  const url = profileId ? '/admin/profiles/update' : '/admin/profiles/create';
  
  fetch(url, {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      cancelProfileForm();
      loadProfiles();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

function deleteProfile(profileId, profileName) {
  if (confirm(`Tem certeza que deseja excluir o perfil "${profileName}"?`)) {
    const formData = new FormData();
    formData.append('profile_id', profileId);
    
    fetch('/admin/profiles/delete', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        alert(result.message);
        loadProfiles();
      } else {
        alert('Erro: ' + result.message);
      }
    })
    .catch(error => {
      alert('Erro de conexão: ' + error.message);
    });
  }
}

// Load dashboard tab permissions for a profile
function loadDashboardTabPermissions(profileId) {
  fetch(`/admin/profiles/${profileId}/dashboard-tabs`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success && result.dashboard_tabs) {
      const tabs = result.dashboard_tabs;
      
      // Set dashboard tab checkboxes
      const tabCheckboxes = {
        retornados: document.querySelector('input[name="dashboard_tabs[retornados]"]'),
        amostragens: document.querySelector('input[name="dashboard_tabs[amostragens]"]'),
        fornecedores: document.querySelector('input[name="dashboard_tabs[fornecedores]"]'),
        garantias: document.querySelector('input[name="dashboard_tabs[garantias]"]'),
        melhorias: document.querySelector('input[name="dashboard_tabs[melhorias]"]')
      };
      
      Object.keys(tabCheckboxes).forEach(tab => {
        if (tabCheckboxes[tab]) {
          tabCheckboxes[tab].checked = tabs[tab] === true || tabs[tab] === 1;
        }
      });
      
      console.log('✅ Dashboard tab permissions loaded:', tabs);
    } else {
      console.log('⚠️ No dashboard tab permissions found, using defaults');
    }
  })
  .catch(error => {
    console.error('Error loading dashboard tab permissions:', error);
    // Não alertar o usuário, apenas logar
  });
}
</script>
