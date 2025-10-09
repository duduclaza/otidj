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
        <label class="block text-sm font-medium text-gray-700 mb-2">Senha <span id="passwordRequired">*</span></label>
        <div class="relative">
          <input type="password" id="userPassword" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-3 pr-12 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
          <button type="button" onclick="toggleUserPassword()" 
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
            <svg id="userPasswordEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
          </button>
        </div>
        <p id="passwordHelp" class="text-xs text-gray-500 mt-1 hidden">Deixe em branco para manter a senha atual</p>
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

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Perfil de Acesso *</label>
        <select id="userProfile" name="profile_id" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
          <option value="">Selecione um perfil</option>
        </select>
      </div>

      <!-- Sistema de Notificações - Para todos os usuários -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start space-x-3">
          <input type="checkbox" id="notificacoesAtivadas" name="notificacoes_ativadas" checked class="mt-1 h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
          <div>
            <label for="notificacoesAtivadas" class="block text-sm font-medium text-gray-900 cursor-pointer">
              🔔 Notificações do Sistema Ativadas
            </label>
            <p class="text-xs text-gray-600 mt-1">
              Quando marcado, o usuário verá o sino de notificações no sistema e receberá alertas visuais e sonoros. Quando desmarcado, o sino não será exibido.
            </p>
          </div>
        </div>
      </div>

      <!-- Permissões específicas para aprovação de módulos -->
      <div id="permissoesAprovacaoContainer" class="hidden space-y-3">
        <!-- POPs e ITs -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-start space-x-3">
            <input type="checkbox" id="podeAprovarPopsIts" name="pode_aprovar_pops_its" class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <div>
              <label for="podeAprovarPopsIts" class="block text-sm font-medium text-gray-900 cursor-pointer">
                🔐 Pode Aprovar POPs e ITs
              </label>
              <p class="text-xs text-gray-600 mt-1">
                Quando marcado, este administrador receberá emails automáticos sempre que houver POPs ou ITs pendentes de aprovação.
              </p>
            </div>
          </div>
        </div>

        <!-- Fluxogramas -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
          <div class="flex items-start space-x-3">
            <input type="checkbox" id="podeAprovarFluxogramas" name="pode_aprovar_fluxogramas" class="mt-1 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
            <div>
              <label for="podeAprovarFluxogramas" class="block text-sm font-medium text-gray-900 cursor-pointer">
                🔀 Pode Aprovar Fluxogramas
              </label>
              <p class="text-xs text-gray-600 mt-1">
                Quando marcado, este administrador receberá emails automáticos sempre que houver Fluxogramas pendentes de aprovação.
              </p>
            </div>
          </div>
        </div>

        <!-- Amostragens -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
          <div class="flex items-start space-x-3">
            <input type="checkbox" id="podeAprovarAmostragens" name="pode_aprovar_amostragens" class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
            <div>
              <label for="podeAprovarAmostragens" class="block text-sm font-medium text-gray-900 cursor-pointer">
                🧪 Pode Aprovar Amostragens
              </label>
              <p class="text-xs text-gray-600 mt-1">
                Quando marcado, este administrador receberá emails automáticos sempre que houver Amostragens pendentes de aprovação.
              </p>
            </div>
          </div>
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil</th>
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
  <div class="modal-container w-full max-w-4xl mx-4">
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
    
    <div class="modal-body max-h-96 overflow-y-auto">
      <div id="permissionsContent" class="space-y-4">
        <!-- Permissions will be loaded here -->
      </div>
    </div>

    <div class="modal-footer flex-col sm:flex-row gap-2 sm:gap-4">
      <button onclick="closePermissionsModal()" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        Cancelar
      </button>
      <button onclick="savePermissions()" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
        Salvar Permissões
      </button>
    </div>
  </div>
</div>

<script>
let currentUserId = null;
let setoresList = [];
let filiaisList = [];
let profilesList = [];

// Função para mostrar/ocultar senha
function toggleUserPassword() {
  const passwordInput = document.getElementById('userPassword');
  const eyeIcon = document.getElementById('userPasswordEyeIcon');
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    eyeIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
    `;
  } else {
    passwordInput.type = 'password';
    eyeIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
    `;
  }
}

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
      profilesList = result.profiles || [];
      console.log('Setores recebidos:', setoresList);
      console.log('Filiais recebidas:', filiaisList);
      console.log('Perfis recebidos:', profilesList);
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
  
  // Populate profiles dropdown
  const profileSelect = document.getElementById('userProfile');
  if (profileSelect) {
    profileSelect.innerHTML = '<option value="">Selecione um perfil</option>';
    if (profilesList && profilesList.length > 0) {
      profilesList.forEach(profile => {
        const option = document.createElement('option');
        option.value = profile.id;
        option.textContent = profile.name;
        if (profile.description) {
          option.title = profile.description;
        }
        profileSelect.appendChild(option);
      });
    } else {
      console.log('Nenhum perfil encontrado');
    }
  } else {
    console.error('Elemento userProfile não encontrado');
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
        <span class="px-2 py-1 text-xs font-medium rounded-full ${user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}" title="${user.profile_description || ''}">
          ${user.profile_name || 'Sem perfil'}
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
      <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium space-x-2">
        <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-900">Editar</button>
        <button onclick="deleteUser(${user.id}, '${user.name}')" class="text-red-600 hover:text-red-900">Excluir</button>
      </td>
    `;
    tbody.appendChild(row);
  });
}

// Função para mostrar/esconder permissões de aprovação
function togglePopsItsPermission() {
  const role = document.getElementById('userRole').value;
  const container = document.getElementById('permissoesAprovacaoContainer');
  
  if (role === 'admin') {
    container.classList.remove('hidden');
  } else {
    container.classList.add('hidden');
    document.getElementById('podeAprovarPopsIts').checked = false;
    document.getElementById('podeAprovarFluxogramas').checked = false;
    document.getElementById('podeAprovarAmostragens').checked = false;
  }
}

// Event listener para mudança de role
document.addEventListener('DOMContentLoaded', function() {
  const userRoleSelect = document.getElementById('userRole');
  if (userRoleSelect) {
    userRoleSelect.addEventListener('change', togglePopsItsPermission);
  }
});

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
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('passwordHelp').classList.add('hidden');
    
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
          document.getElementById('userProfile').value = user.profile_id || '';
          
          // Debug: Ver valores que chegaram do banco
          console.log('🔍 Dados do usuário recebidos:', user);
          console.log('📊 Valores dos checkboxes do banco:');
          console.log('  - pode_aprovar_pops_its:', user.pode_aprovar_pops_its);
          console.log('  - pode_aprovar_fluxogramas:', user.pode_aprovar_fluxogramas);
          console.log('  - pode_aprovar_amostragens:', user.pode_aprovar_amostragens);
          console.log('  - notificacoes_ativadas:', user.notificacoes_ativadas);
          
          // Preencher campos de aprovação (converter para booleano)
          const podeAprovarPopsIts = Boolean(Number(user.pode_aprovar_pops_its));
          document.getElementById('podeAprovarPopsIts').checked = podeAprovarPopsIts;
          console.log('✅ POPs/ITs checkbox:', podeAprovarPopsIts ? 'MARCADO' : 'DESMARCADO');
          
          const podeAprovarFluxogramas = Boolean(Number(user.pode_aprovar_fluxogramas));
          document.getElementById('podeAprovarFluxogramas').checked = podeAprovarFluxogramas;
          console.log('✅ Fluxogramas checkbox:', podeAprovarFluxogramas ? 'MARCADO' : 'DESMARCADO');
          
          const podeAprovarAmostragens = Boolean(Number(user.pode_aprovar_amostragens));
          document.getElementById('podeAprovarAmostragens').checked = podeAprovarAmostragens;
          console.log('✅ Amostragens checkbox:', podeAprovarAmostragens ? 'MARCADO' : 'DESMARCADO');
          
          // Preencher campo de notificações (padrão: ativado se não informado)
          const notificacoesAtivadas = user.notificacoes_ativadas === undefined || Boolean(Number(user.notificacoes_ativadas));
          document.getElementById('notificacoesAtivadas').checked = notificacoesAtivadas;
          console.log('✅ Notificações checkbox:', notificacoesAtivadas ? 'MARCADO' : 'DESMARCADO');
          
          // Mostrar/esconder permissões de aprovação baseado no role
          togglePopsItsPermission();
          
          // Show password field but make it optional for editing
          document.getElementById('passwordField').style.display = 'block';
          document.getElementById('userPassword').required = false;
          document.getElementById('userPassword').value = '';
          document.getElementById('passwordRequired').style.display = 'none';
          document.getElementById('passwordHelp').classList.remove('hidden');
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
  
  // IMPORTANTE: Adicionar checkboxes explicitamente (mesmo se desmarcados)
  // FormData só envia checkboxes marcados, precisamos enviar todos
  const checkboxMap = {
    'notificacoes_ativadas': 'notificacoesAtivadas',
    'pode_aprovar_pops_its': 'podeAprovarPopsIts',
    'pode_aprovar_fluxogramas': 'podeAprovarFluxogramas',
    'pode_aprovar_amostragens': 'podeAprovarAmostragens'
  };
  
  Object.entries(checkboxMap).forEach(([fieldName, checkboxId]) => {
    const checkbox = document.getElementById(checkboxId);
    if (checkbox) {
      // Remove o valor antigo se existir
      formData.delete(fieldName);
      // Adiciona explicitamente: '1' se marcado, '0' se desmarcado
      const value = checkbox.checked ? '1' : '0';
      formData.append(fieldName, value);
      console.log(`✅ ${fieldName} = ${value} (checkbox ${checkbox.checked ? 'marcado' : 'desmarcado'})`);
    } else {
      console.warn(`⚠️ Checkbox ${checkboxId} não encontrado!`);
    }
  });
  
  console.log('📤 Enviando dados:', Object.fromEntries(formData));
  
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
    formData.append('user_id', userId);
    
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
  
  fetch(`/admin/users/${userId}/permissions`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json'
    }
  })
  .then(response => {
    console.log('Response status:', response.status);
    console.log('Response headers:', response.headers.get('content-type'));
    return response.text();
  })
  .then(text => {
    console.log('Raw response:', text);
    try {
      const result = JSON.parse(text);
      if (result.success) {
        displayPermissions(result.permissions);
        openModal('permissionsModal');
      } else {
        alert('Erro ao carregar permissões: ' + result.message);
      }
    } catch (e) {
      console.error('JSON parse error:', e);
      alert('Erro: Resposta inválida do servidor');
    }
  })
  .catch(error => {
    console.error('Fetch error:', error);
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
    <div class="bg-gray-50 p-4 sm:p-6 rounded-lg">
      <div class="overflow-x-auto">
        <div class="min-w-full">
          <div class="grid grid-cols-4 gap-2 sm:gap-4 mb-4 min-w-max">
            <div class="font-semibold text-gray-700 text-sm sm:text-base">Módulo</div>
            <div class="font-semibold text-gray-700 text-center text-sm sm:text-base">Visualizar</div>
            <div class="font-semibold text-gray-700 text-center text-sm sm:text-base">Editar</div>
            <div class="font-semibold text-gray-700 text-center text-sm sm:text-base">Excluir</div>
          </div>
  `;
  
  modules.forEach(module => {
    const perm = permissions[module.key] || {};
    html += `
      <div class="grid grid-cols-4 gap-2 sm:gap-4 py-3 border-b border-gray-200 items-center min-w-max">
        <div class="font-medium text-gray-900 text-sm sm:text-base pr-2">${module.name}</div>
        <div class="text-center">
          <label class="inline-flex items-center justify-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][view]" 
                   ${perm.can_view ? 'checked' : ''} 
                   class="form-checkbox h-4 w-4 sm:h-5 sm:w-5 text-blue-600 rounded">
          </label>
        </div>
        <div class="text-center">
          <label class="inline-flex items-center justify-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][edit]" 
                   ${perm.can_edit ? 'checked' : ''} 
                   class="form-checkbox h-4 w-4 sm:h-5 sm:w-5 text-blue-600 rounded">
          </label>
        </div>
        <div class="text-center">
          <label class="inline-flex items-center justify-center">
            <input type="checkbox" 
                   name="permissions[${module.key}][delete]" 
                   ${perm.can_delete ? 'checked' : ''} 
                   class="form-checkbox h-4 w-4 sm:h-5 sm:w-5 text-blue-600 rounded">
          </label>
        </div>
      </div>
    `;
  });
  
  html += `
        </div>
      </div>
    </div>
  `;
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


// Modal functions
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    const modalId = e.target.id;
    closeModal(modalId);
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const activeModal = document.querySelector('.modal-overlay.active');
    if (activeModal) {
      closeModal(activeModal.id);
    }
  }
});

// Close modal buttons
document.addEventListener('click', function(e) {
  if (e.target.matches('[data-modal-close]') || e.target.closest('[data-modal-close]')) {
    const modal = e.target.closest('.modal-overlay');
    if (modal) {
      closeModal(modal.id);
    }
  }
});
</script>
