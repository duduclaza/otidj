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
  { key: 'toners_retornados', name: 'Registro de Retornados' },
  { key: 'homologacoes', name: 'Homologações' },
  { key: 'amostragens', name: 'Amostragens' },
  { key: 'garantias', name: 'Garantias' },
  { key: 'controle_descartes', name: 'Controle de Descartes' },
  { key: 'femea', name: 'FEMEA' },
  { key: 'pops_its', name: 'POPs e ITs' },
  { key: 'fluxogramas', name: 'Fluxogramas' },
  { key: 'melhoria_continua', name: 'Melhoria Contínua' },
  { key: 'controle_rc', name: 'Controle de RC' },
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
      displayProfiles(result.profiles);
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
          <label class="inline-flex items-center justify-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][view]" 
                   class="form-checkbox h-4 w-4 text-blue-600 rounded">
          </label>
        </div>
        <div class="text-center">
          <label class="inline-flex items-center justify-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][edit]" 
                   class="form-checkbox h-4 w-4 text-blue-600 rounded">
          </label>
        </div>
        <div class="text-center">
          <label class="inline-flex items-center justify-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][delete]" 
                   class="form-checkbox h-4 w-4 text-blue-600 rounded">
          </label>
        </div>
        <div class="text-center">
          <label class="inline-flex items-center justify-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][import]" 
                   class="form-checkbox h-4 w-4 text-blue-600 rounded">
          </label>
        </div>
        <div class="text-center">
          <label class="inline-flex items-center justify-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][export]" 
                   class="form-checkbox h-4 w-4 text-blue-600 rounded">
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
    
    const actions = profile.is_admin == 1 
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
</script>
