<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Solicitações de Acesso</h1>
    <div class="text-sm text-gray-600">
      Gerencie as solicitações de convite para acesso ao sistema
    </div>
  </div>

  <!-- Pending Invitations -->
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Solicitações Pendentes</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Setor/Filial</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensagem</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody id="invitationsTableBody" class="bg-white divide-y divide-gray-200">
          <!-- Invitations will be loaded here via JavaScript -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- All Invitations History -->
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Histórico de Solicitações</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processado por</th>
          </tr>
        </thead>
        <tbody id="historyTableBody" class="bg-white divide-y divide-gray-200">
          <!-- History will be loaded here via JavaScript -->
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Approval Modal -->
<div id="approvalModal" class="modal-overlay">
  <div class="modal-container w-full max-w-md">
    <div class="modal-header">
      <h3 class="modal-title">Aprovar Solicitação</h3>
      <button class="modal-close" data-modal-close>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="modal-body space-y-4">
      <div>
        <p class="text-sm text-gray-600 mb-4">
          Você está prestes a aprovar a solicitação de acesso para:
        </p>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="text-sm">
            <div class="font-medium text-gray-900" id="approvalName"></div>
            <div class="text-gray-600" id="approvalEmail"></div>
            <div class="text-gray-600" id="approvalDetails"></div>
          </div>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Função do Usuário</label>
        <select id="approvalRole" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="user">Usuário</option>
          <option value="admin">Administrador</option>
        </select>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Senha Temporária</label>
        <input type="text" id="approvalPassword" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Será gerada automaticamente">
      </div>
    </div>

    <div class="modal-footer">
      <button onclick="closeApprovalModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
        Cancelar
      </button>
      <button onclick="approveInvitation()" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700">
        Aprovar e Criar Usuário
      </button>
    </div>
  </div>
</div>

<script>
let currentInvitationId = null;

// Load invitations on page load
document.addEventListener('DOMContentLoaded', function() {
  loadInvitations();
});

function loadInvitations() {
  fetch('/admin/invitations', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      displayPendingInvitations(result.pending);
      displayInvitationHistory(result.all);
    } else {
      alert('Erro ao carregar solicitações: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Erro de conexão');
  });
}

function displayPendingInvitations(invitations) {
  const tbody = document.getElementById('invitationsTableBody');
  tbody.innerHTML = '';
  
  if (invitations.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
          <div class="text-sm">Nenhuma solicitação pendente</div>
        </td>
      </tr>
    `;
    return;
  }
  
  invitations.forEach(invitation => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-medium text-gray-900">${invitation.name}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${invitation.email}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        ${invitation.setor || ''} ${invitation.filial ? '- ' + invitation.filial : ''}
      </td>
      <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
        ${invitation.message || 'Sem mensagem'}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        ${new Date(invitation.created_at).toLocaleDateString('pt-BR')}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
        <button onclick="openApprovalModal(${invitation.id}, '${invitation.name}', '${invitation.email}', '${invitation.setor || ''}', '${invitation.filial || ''}')" 
                class="text-green-600 hover:text-green-900">Aprovar</button>
        <button onclick="rejectInvitation(${invitation.id}, '${invitation.name}')" 
                class="text-red-600 hover:text-red-900">Rejeitar</button>
      </td>
    `;
    tbody.appendChild(row);
  });
}

function displayInvitationHistory(invitations) {
  const tbody = document.getElementById('historyTableBody');
  tbody.innerHTML = '';
  
  invitations.forEach(invitation => {
    const row = document.createElement('tr');
    const statusClass = invitation.status === 'approved' ? 'bg-green-100 text-green-800' : 
                       invitation.status === 'rejected' ? 'bg-red-100 text-red-800' : 
                       'bg-yellow-100 text-yellow-800';
    const statusText = invitation.status === 'approved' ? 'Aprovado' : 
                      invitation.status === 'rejected' ? 'Rejeitado' : 'Pendente';
    
    row.innerHTML = `
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${invitation.name}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${invitation.email}</td>
      <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 py-1 text-xs font-medium rounded-full ${statusClass}">
          ${statusText}
        </span>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        ${new Date(invitation.created_at).toLocaleDateString('pt-BR')}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        ${invitation.processed_by || '-'}
      </td>
    `;
    tbody.appendChild(row);
  });
}

function openApprovalModal(id, name, email, setor, filial) {
  currentInvitationId = id;
  document.getElementById('approvalName').textContent = name;
  document.getElementById('approvalEmail').textContent = email;
  document.getElementById('approvalDetails').textContent = `${setor} ${filial ? '- ' + filial : ''}`.trim();
  
  // Generate random password
  const password = generatePassword();
  document.getElementById('approvalPassword').value = password;
  
  openModal('approvalModal');
}

function closeApprovalModal() {
  closeModal('approvalModal');
  currentInvitationId = null;
}

function generatePassword() {
  const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
  let password = '';
  for (let i = 0; i < 8; i++) {
    password += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return password;
}

function approveInvitation() {
  const formData = new FormData();
  formData.append('invitation_id', currentInvitationId);
  formData.append('role', document.getElementById('approvalRole').value);
  formData.append('password', document.getElementById('approvalPassword').value);
  
  fetch('/admin/invitations/approve', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      closeApprovalModal();
      loadInvitations();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

function rejectInvitation(invitationId, name) {
  if (confirm(`Tem certeza que deseja rejeitar a solicitação de "${name}"?`)) {
    const formData = new FormData();
    formData.append('invitation_id', invitationId);
    
    fetch('/admin/invitations/reject', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        alert(result.message);
        loadInvitations();
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
