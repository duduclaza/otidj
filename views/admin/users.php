<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Gerenciar Usuários</h1>
    <button onclick="toggleUserForm()" id="toggleFormBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
      </svg>
      <span>Novo Usuário</span>
    </button>
  </div>

  <!-- User Form -->
  <div id="userFormContainer" class="hidden bg-white rounded-lg shadow-lg border border-gray-200 p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 id="formTitle" class="text-lg font-semibold text-gray-900">Criar Novo Usuário</h3>
      <button onclick="cancelUserForm()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="userForm" class="space-y-6">
      <input type="hidden" id="userId" name="id">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
          <input type="text" id="userName" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
          <input type="email" id="userEmail" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        </div>
      </div>

      <div id="passwordField">
        <label class="block text-sm font-medium text-gray-700 mb-2">Senha *</label>
        <input type="password" id="userPassword" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Setor</label>
          <select id="userSetor" name="setor" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            <option value="">Selecione um setor</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Filial</label>
          <select id="userFilial" name="filial" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            <option value="">Selecione uma filial</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Função</label>
          <select id="userRole" name="role" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            <option value="user">Usuário</option>
            <option value="admin">Administrador</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select id="userStatus" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            <option value="active">Ativo</option>
            <option value="inactive">Inativo</option>
          </select>
        </div>
      </div>

      <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
        <button type="button" onclick="cancelUserForm()" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button type="button" onclick="submitUser()" id="submitBtn" class="px-6 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Criar Usuário
        </button>
      </div>
    </form>
  </div>

  <!-- Users Table -->
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Lista de Usuários</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Função</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody id="usersTableBody" class="bg-white divide-y divide-gray-200">
          <!-- Users will be loaded here via JavaScript -->
        </tbody>
      </table>
    </div>
  </div>
</section>


<!-- Permissions Modal -->
<div id="permissionsModal" class="modal-overlay">
  <div class="modal-container w-full max-w-2xl">
    <div class="modal-header">
      <div>
        <h3 class="modal-title">Gerenciar Permissões</h3>
        <p class="text-sm text-gray-600 mt-1">Usuário: <span id="permissionsUserName"></span></p>
      </div>
      <button class="modal-close" data-modal-close>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="modal-body">
      <div id="permissionsContent" class="space-y-4">
        <!-- Permissions will be loaded here -->
      </div>
    </div>

    <div class="modal-footer">
      <button onclick="closePermissionsModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
        Cancelar
      </button>
      <button onclick="savePermissions()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
        Salvar Permissões
      </button>
    </div>
  </div>
</div>

<script>
let currentUserId = null;
let setoresList = [];
let filiaisList = [];

// Load users on page load
document.addEventListener('DOMContentLoaded', function() {
  loadUsers();
});

function loadUsers() {
  console.log('Carregando usuários...');
  fetch('/admin/users', {
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
      displayUsers(result.users);
      setoresList = result.setores || [];
      filiaisList = result.filiais || [];
      console.log('Setores recebidos:', setoresList);
      console.log('Filiais recebidas:', filiaisList);
      populateDropdowns();
    } else {
      alert('Erro ao carregar usuários: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Erro de conexão');
  });
}

function populateDropdowns() {
  console.log('Populando dropdowns...', { setoresList, filiaisList });
  
  // Populate setores dropdown
  const setorSelect = document.getElementById('userSetor');
  if (setorSelect) {
    setorSelect.innerHTML = '<option value="">Selecione um setor</option>';
    if (setoresList && setoresList.length > 0) {
      setoresList.forEach(setor => {
        const option = document.createElement('option');
        option.value = setor;
        option.textContent = setor;
        setorSelect.appendChild(option);
      });
    } else {
      console.log('Nenhum setor encontrado');
    }
  } else {
    console.error('Elemento userSetor não encontrado');
  }
  
  // Populate filiais dropdown
  const filialSelect = document.getElementById('userFilial');
  if (filialSelect) {
    filialSelect.innerHTML = '<option value="">Selecione uma filial</option>';
    if (filiaisList && filiaisList.length > 0) {
      filiaisList.forEach(filial => {
        const option = document.createElement('option');
        option.value = filial;
        option.textContent = filial;
        filialSelect.appendChild(option);
      });
    } else {
      console.log('Nenhuma filial encontrada');
    }
  } else {
    console.error('Elemento userFilial não encontrado');
  }
}

function displayUsers(users) {
  const tbody = document.getElementById('usersTableBody');
  tbody.innerHTML = '';
  
  users.forEach(user => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
            <span class="text-white text-sm font-medium">${user.name.charAt(0).toUpperCase()}</span>
          </div>
          <div class="ml-4">
            <div class="text-sm font-medium text-gray-900">${user.name}</div>
            <div class="text-sm text-gray-500">${user.setor || ''} ${user.filial ? '- ' + user.filial : ''}</div>
          </div>
        </div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.email}</td>
      <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 py-1 text-xs font-medium rounded-full ${user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'}">
          ${user.role === 'admin' ? 'Administrador' : 'Usuário'}
        </span>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 py-1 text-xs font-medium rounded-full ${user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
          ${user.status === 'active' ? 'Ativo' : 'Inativo'}
        </span>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        ${new Date(user.created_at).toLocaleDateString('pt-BR')}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
        <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-900">Editar</button>
        <button onclick="managePermissions(${user.id}, '${user.name}')" class="text-green-600 hover:text-green-900">Permissões</button>
        <button onclick="deleteUser(${user.id}, '${user.name}')" class="text-red-600 hover:text-red-900">Excluir</button>
      </td>
    `;
    tbody.appendChild(row);
  });
}

function toggleUserForm() {
  const container = document.getElementById('userFormContainer');
  const btn = document.getElementById('toggleFormBtn');
  
  if (container.classList.contains('hidden')) {
    // Show form for creating new user
    container.classList.remove('hidden');
    document.getElementById('formTitle').textContent = 'Criar Novo Usuário';
    document.getElementById('submitBtn').textContent = 'Criar Usuário';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordField').style.display = 'block';
    document.getElementById('userPassword').required = true;
    
    // Repopulate dropdowns after form reset
    populateDropdowns();
    
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
      <span>Novo Usuário</span>
    `;
  }
}

function editUser(userId) {
  // Find user data and populate form
  fetch('/admin/users', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      const user = result.users.find(u => u.id == userId);
      if (user) {
        // Show form and populate with user data
        const container = document.getElementById('userFormContainer');
        container.classList.remove('hidden');
        
        // First populate dropdowns, then set values
        populateDropdowns();
        
        // Use setTimeout to ensure dropdowns are populated before setting values
        setTimeout(() => {
          document.getElementById('formTitle').textContent = 'Editar Usuário';
          document.getElementById('submitBtn').textContent = 'Salvar Alterações';
          document.getElementById('userId').value = user.id;
          document.getElementById('userName').value = user.name;
          document.getElementById('userEmail').value = user.email;
          document.getElementById('userSetor').value = user.setor || '';
          document.getElementById('userFilial').value = user.filial || '';
          document.getElementById('userRole').value = user.role;
          document.getElementById('userStatus').value = user.status;
          document.getElementById('passwordField').style.display = 'none';
          document.getElementById('userPassword').required = false;
        }, 100);
        
        // Update toggle button
        const btn = document.getElementById('toggleFormBtn');
        btn.innerHTML = `
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          <span>Cancelar</span>
        `;
      }
    }
  });
}

function cancelUserForm() {
  const container = document.getElementById('userFormContainer');
  container.classList.add('hidden');
  document.getElementById('userForm').reset();
  
  // Repopulate dropdowns after reset
  populateDropdowns();
  
  // Reset toggle button
  const btn = document.getElementById('toggleFormBtn');
  btn.innerHTML = `
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
    </svg>
    <span>Novo Usuário</span>
  `;
}

function submitUser() {
  const form = document.getElementById('userForm');
  const formData = new FormData(form);
  const userId = document.getElementById('userId').value;
  
  const url = userId ? '/admin/users/update' : '/admin/users/create';
  
  fetch(url, {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      cancelUserForm();
      loadUsers();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

function deleteUser(userId, userName) {
  if (confirm(`Tem certeza que deseja excluir o usuário "${userName}"?`)) {
    const formData = new FormData();
    formData.append('id', userId);
    
    fetch('/admin/users/delete', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        alert(result.message);
        loadUsers();
      } else {
        alert('Erro: ' + result.message);
      }
    })
    .catch(error => {
      alert('Erro de conexão: ' + error.message);
    });
  }
}

function managePermissions(userId, userName) {
  currentUserId = userId;
  document.getElementById('permissionsUserName').textContent = userName;
  
  fetch(`/admin/users/${userId}/permissions`)
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      displayPermissions(result.permissions);
      openModal('permissionsModal');
    } else {
      alert('Erro ao carregar permissões: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

function displayPermissions(permissions) {
  const content = document.getElementById('permissionsContent');
  const modules = [
    { key: 'dashboard', name: 'Dashboard' },
    { key: 'toners', name: 'Controle de Toners' },
    { key: 'homologacoes', name: 'Homologações' },
    { key: 'amostragens', name: 'Amostragens' },
    { key: 'auditorias', name: 'Auditorias' },
    { key: 'garantias', name: 'Garantias' }
  ];
  
  let html = `
    <div class="bg-gray-50 p-6 rounded-lg">
      <div class="grid grid-cols-4 gap-4 mb-4">
        <div class="font-semibold text-gray-700">Módulo</div>
        <div class="font-semibold text-gray-700 text-center">Visualizar</div>
        <div class="font-semibold text-gray-700 text-center">Editar</div>
        <div class="font-semibold text-gray-700 text-center">Excluir</div>
      </div>
  `;
  
  modules.forEach(module => {
    const perm = permissions[module.key] || {};
    html += `
      <div class="grid grid-cols-4 gap-4 py-3 border-b border-gray-200 items-center">
        <div class="font-medium text-gray-900">${module.name}</div>
        <div class="text-center">
          <label class="inline-flex items-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][view]" 
                   ${perm.can_view ? 'checked' : ''} 
                   class="form-checkbox h-5 w-5 text-blue-600 rounded">
          </label>
        </div>
        <div class="text-center">
          <label class="inline-flex items-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][edit]" 
                   ${perm.can_edit ? 'checked' : ''} 
                   class="form-checkbox h-5 w-5 text-blue-600 rounded">
          </label>
        </div>
        <div class="text-center">
          <label class="inline-flex items-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][delete]" 
                   ${perm.can_delete ? 'checked' : ''} 
                   class="form-checkbox h-5 w-5 text-blue-600 rounded">
          </label>
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  content.innerHTML = html;
}

function closePermissionsModal() {
  closeModal('permissionsModal');
  currentUserId = null;
}

function savePermissions() {
  const form = document.getElementById('permissionsContent');
  const formData = new FormData();
  formData.append('user_id', currentUserId);
  
  // Collect all checked permissions
  const checkboxes = form.querySelectorAll('input[type="checkbox"]:checked');
  checkboxes.forEach(checkbox => {
    formData.append(checkbox.name, checkbox.value);
  });
  
  fetch('/admin/permissions/update', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      closePermissionsModal();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}
</script>
