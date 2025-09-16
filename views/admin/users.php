<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Gerenciar Usuários</h1>
    <button onclick="openCreateUserModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
      </svg>
      <span>Novo Usuário</span>
    </button>
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

<!-- Create/Edit User Modal -->
<div id="userModal" class="modal-overlay">
  <div class="modal-container w-full max-w-md">
    <div class="modal-header">
      <h3 id="modalTitle" class="modal-title">Criar Novo Usuário</h3>
      <button class="modal-close" data-modal-close>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="modal-body">
      <form id="userForm" class="space-y-4">
      <input type="hidden" id="userId" name="id">
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
        <input type="text" id="userName" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
        <input type="email" id="userEmail" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div id="passwordField">
        <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
        <input type="password" id="userPassword" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Setor</label>
          <input type="text" id="userSetor" name="setor" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filial</label>
          <input type="text" id="userFilial" name="filial" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Função</label>
        <select id="userRole" name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="user">Usuário</option>
          <option value="admin">Administrador</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="userStatus" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="active">Ativo</option>
          <option value="inactive">Inativo</option>
        </select>
      </div>
      </form>
    </div>

    <div class="modal-footer">
      <button onclick="closeUserModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
        Cancelar
      </button>
      <button onclick="submitUser()" id="submitBtn" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
        Criar Usuário
      </button>
    </div>
  </div>
</div>

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

// Load users on page load
document.addEventListener('DOMContentLoaded', function() {
  loadUsers();
});

function loadUsers() {
  fetch('/admin/users', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      displayUsers(result.users);
    } else {
      alert('Erro ao carregar usuários: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Erro de conexão');
  });
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

function openCreateUserModal() {
  document.getElementById('modalTitle').textContent = 'Criar Novo Usuário';
  document.getElementById('submitBtn').textContent = 'Criar Usuário';
  document.getElementById('userForm').reset();
  document.getElementById('userId').value = '';
  document.getElementById('passwordField').style.display = 'block';
  document.getElementById('userPassword').required = true;
  openModal('userModal');
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
        document.getElementById('modalTitle').textContent = 'Editar Usuário';
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
        openModal('userModal');
      }
    }
  });
}

function closeUserModal() {
  closeModal('userModal');
  document.getElementById('userForm').reset();
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
      closeUserModal();
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
  const modules = ['toners', 'amostragens', 'retornados', 'registros', 'configuracoes'];
  const actions = ['view', 'edit', 'delete', 'import', 'export'];
  
  content.innerHTML = '';
  
  modules.forEach(module => {
    const moduleDiv = document.createElement('div');
    moduleDiv.className = 'border border-gray-200 rounded-lg p-4';
    
    const moduleTitle = document.createElement('h4');
    moduleTitle.className = 'font-medium text-gray-900 mb-3 capitalize';
    moduleTitle.textContent = module;
    moduleDiv.appendChild(moduleTitle);
    
    const actionsGrid = document.createElement('div');
    actionsGrid.className = 'grid grid-cols-5 gap-2';
    
    actions.forEach(action => {
      const label = document.createElement('label');
      label.className = 'flex items-center space-x-2 text-sm';
      
      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.name = `permissions[${module}][${action}]`;
      checkbox.value = '1';
      checkbox.className = 'rounded border-gray-300 text-blue-600 focus:ring-blue-500';
      
      // Check if user has this permission
      const hasPermission = permissions.some(p => p.module === module && p.action === action);
      checkbox.checked = hasPermission;
      
      const span = document.createElement('span');
      span.textContent = action;
      span.className = 'capitalize';
      
      label.appendChild(checkbox);
      label.appendChild(span);
      actionsGrid.appendChild(label);
    });
    
    moduleDiv.appendChild(actionsGrid);
    content.appendChild(moduleDiv);
  });
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
