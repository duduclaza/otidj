
<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="modal-header">
    <h3 class="modal-title">Importar Amostragens</h3>
    <button class="modal-close" data-modal-close>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
  </div>
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Amostragens</h1>
    <div class="flex space-x-3">
      <button onclick="window.downloadAmostragemLog()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <span>Download Log</span>
      </button>
      <button id="openAmostragemBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nova Amostragem</span>
      </button>
    </div>
  </div>

  <!-- Filters and Search -->
  <div class="bg-white border rounded-lg p-4">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
        <input type="text" id="searchInput" placeholder="Número da NF, status..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="statusFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Todos</option>
          <option value="pendente">Pendente</option>
          <option value="aprovado">Aprovado</option>
          <option value="reprovado">Reprovado</option>
        </select>
      </div>
      <div class="flex items-end">
        <button onclick="window.printAmostragens()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          <span>Imprimir</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Amostragens Grid -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número NF</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsáveis</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observação</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="amostragemTableBody">
          <?php if (isset($amostragens) && !empty($amostragens)): ?>
            <?php foreach ($amostragens as $amostragem): ?>
              <tr>
                <td class="px-4 py-2 text-sm text-gray-900"><?= e($amostragem['numero_nf']) ?></td>
                <td class="px-4 py-2 text-sm text-gray-500">
                  <?php 
                  $responsaveis = !empty($amostragem['responsaveis']) ? json_decode($amostragem['responsaveis'], true) : [];
                  if (!empty($responsaveis)) {
                    echo implode(', ', array_slice($responsaveis, 0, 2));
                    if (count($responsaveis) > 2) echo ' +' . (count($responsaveis) - 2);
                  } else {
                    echo '-';
                  }
                  ?>
                </td>
                <td class="px-4 py-2">
                  <?php if ($amostragem['status'] === 'aprovado'): ?>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aprovado</span>
                  <?php elseif ($amostragem['status'] === 'pendente'): ?>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>
                  <?php else: ?>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Reprovado</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-2 text-sm text-gray-900"><?= date('d/m/Y', strtotime($amostragem['data_registro'])) ?></td>
                <td class="px-4 py-2 text-sm text-gray-500">
                  <?php if (!empty($amostragem['observacao'])): ?>
                    <span title="<?= e($amostragem['observacao']) ?>"><?= e(substr($amostragem['observacao'], 0, 50)) ?><?= strlen($amostragem['observacao']) > 50 ? '...' : '' ?></span>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td class="px-4 py-2 text-sm space-x-2">
                  <?php if (!empty($amostragem['arquivo_nf'])): ?>
                    <a href="/uploads/<?= e($amostragem['arquivo_nf']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800">PDF</a>
                  <?php endif; ?>
                  <button onclick="window.editAmostragem(<?= $amostragem['id'] ?>)" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                  <button onclick="window.deleteAmostragem(<?= $amostragem['id'] ?>)" class="text-red-600 hover:text-red-900">Excluir</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="px-4 py-8 text-center text-gray-500">Nenhuma amostragem encontrada</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal para Adicionar/Editar Amostragem -->
<div id="amostragemModal" class="modal-overlay">
  <div class="modal-container w-full max-w-4xl">
    <!-- Header -->
    <div class="modal-header">
      <h3 id="modalTitle" class="modal-title">Adicionar Nova Amostragem</h3>
      <button class="modal-close" data-modal-close>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <!-- Content -->
    <form id="amostragemForm" class="px-6 py-6 space-y-6" enctype="multipart/form-data">
      <input type="hidden" name="id" id="amostragemId">
      
      <!-- Número da NF -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Número da NF *</label>
        <input type="text" name="numero_nf" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <!-- Anexo da NF -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Anexo da NF (PDF) *</label>
        <input type="file" name="arquivo_nf" accept=".pdf" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <p class="text-xs text-gray-500 mt-1">Apenas arquivos PDF são aceitos</p>
      </div>

      <!-- Fotos -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fotos</label>
        <input type="file" name="fotos[]" accept="image/*" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <p class="text-xs text-gray-500 mt-1">Selecione uma ou mais fotos (JPG, PNG, etc.)</p>
      </div>

      <!-- Responsáveis -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Responsáveis *</label>
        <div class="border border-gray-300 rounded-lg p-2 bg-white" style="min-height: 120px; max-height: 200px; overflow-y: auto;">
          <div id="responsaveisCheckboxes">
            <!-- Checkboxes will be loaded dynamically -->
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-1">Selecione um ou mais responsáveis</p>
      </div>

      <!-- Status -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Status *</label>
        <div class="flex space-x-4">
          <label class="flex items-center">
            <input type="radio" name="status" value="pendente" class="mr-2" onchange="window.toggleStatusFields()" checked>
            <span class="text-sm font-medium text-yellow-700">Pendente</span>
          </label>
          <label class="flex items-center">
            <input type="radio" name="status" value="aprovado" class="mr-2" onchange="window.toggleStatusFields()">
            <span class="text-sm font-medium text-green-700">Aprovado</span>
          </label>
          <label class="flex items-center">
            <input type="radio" name="status" value="reprovado" class="mr-2" onchange="window.toggleStatusFields()">
            <span class="text-sm font-medium text-red-700">Reprovado</span>
          </label>
        </div>
      </div>

      <!-- Campos condicionais para reprovado -->
      <div id="reprovadoFields" class="hidden space-y-4">
        <!-- Observação -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Observação *</label>
          <textarea name="observacao" rows="3" placeholder="Descreva o motivo da reprovação..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
        </div>

        <!-- Evidências -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Evidências (Fotos)</label>
          <input type="file" name="evidencias[]" accept="image/*" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <p class="text-xs text-gray-500 mt-1">Selecione uma ou mais fotos como evidência do problema</p>
        </div>
      </div>
    </form>

    <!-- Footer -->
    <div class="modal-body">
      <div class="flex justify-end space-x-4">
        <button onclick="window.closeAmostragemModal()" class="px-6 py-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button onclick="window.submitAmostragem()" class="px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Salvar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let selectedStatus = '';
let activityLog = [];

// Activity logging
function logActivity(type, action, details = {}) {
  const timestamp = new Date().toISOString();
  activityLog.push({ timestamp, type, action, details });
  console.log(`[${type.toUpperCase()}] ${action}:`, details);
}

// Modal functions
function openAmostragemModal() {
  openModal('amostragemModal');
  document.getElementById('modalTitle').textContent = 'Adicionar Nova Amostragem';
  document.getElementById('amostragemForm').reset();
  document.getElementById('amostragemId').value = '';
  
  // Set default status to 'pendente'
  document.querySelector('input[name="status"][value="pendente"]').checked = true;
  selectedStatus = 'pendente';
  
  // Load users for responsáveis dropdown
  loadUsers();
  
  logActivity('modal', 'Abrir modal de amostragem');
}

function closeAmostragemModal() {
  closeModal('amostragemModal');
  document.getElementById('amostragemForm').reset();
  logActivity('modal', 'Fechar modal de amostragem');
}

function toggleStatusFields() {
  const status = document.querySelector('input[name="status"]:checked')?.value;
  const reprovadoFields = document.getElementById('reprovadoFields');
  
  logActivity('user_action', 'Status Changed', { status });
  selectedStatus = status;
  
  if (status === 'reprovado') {
    reprovadoFields.classList.remove('hidden');
    document.querySelector('textarea[name="observacao"]').required = true;
  } else {
    reprovadoFields.classList.add('hidden');
    document.querySelector('textarea[name="observacao"]').required = false;
  }
}

function loadUsers() {
  console.log('Iniciando carregamento de usuários...');
  fetch('/api/users')
    .then(response => {
      console.log('Response status:', response.status);
      return response.json();
    })
    .then(users => {
      console.log('Dados recebidos da API:', users);
      const container = document.getElementById('responsaveisCheckboxes');
      console.log('Container element:', container);
      
      if (!container) {
        console.error('Container de checkboxes não encontrado!');
        return;
      }
      
      container.innerHTML = '';
      
      if (Array.isArray(users)) {
        users.forEach((user, index) => {
          const checkboxDiv = document.createElement('div');
          checkboxDiv.className = 'flex items-center mb-2';
          
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.name = 'responsaveis[]';
          checkbox.value = user.name;
          checkbox.id = `responsavel_${index}`;
          checkbox.className = 'mr-2';
          
          const label = document.createElement('label');
          label.htmlFor = `responsavel_${index}`;
          label.textContent = `${user.name} (${user.email})`;
          label.className = 'text-sm cursor-pointer';
          
          checkboxDiv.appendChild(checkbox);
          checkboxDiv.appendChild(label);
          container.appendChild(checkboxDiv);
        });
        console.log('Usuários carregados:', users.length);
      } else {
        console.error('Resposta da API não é um array:', users);
        // Add test checkbox
        const checkboxDiv = document.createElement('div');
        checkboxDiv.className = 'flex items-center mb-2';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'responsaveis[]';
        checkbox.value = 'Test User';
        checkbox.id = 'responsavel_test';
        checkbox.className = 'mr-2';
        
        const label = document.createElement('label');
        label.htmlFor = 'responsavel_test';
        label.textContent = 'Test User (test@example.com)';
        label.className = 'text-sm cursor-pointer';
        
        checkboxDiv.appendChild(checkbox);
        checkboxDiv.appendChild(label);
        container.appendChild(checkboxDiv);
        console.log('Adicionado checkbox de teste');
      }
    })
    .catch(error => {
      console.error('Erro ao carregar usuários:', error);
      // Add test checkbox on error
      const container = document.getElementById('responsaveisCheckboxes');
      if (container) {
        const checkboxDiv = document.createElement('div');
        checkboxDiv.className = 'flex items-center mb-2';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'responsaveis[]';
        checkbox.value = 'Test User';
        checkbox.id = 'responsavel_test';
        checkbox.className = 'mr-2';
        
        const label = document.createElement('label');
        label.htmlFor = 'responsavel_test';
        label.textContent = 'Test User (test@example.com)';
        label.className = 'text-sm cursor-pointer';
        
        checkboxDiv.appendChild(checkbox);
        checkboxDiv.appendChild(label);
        container.appendChild(checkboxDiv);
        console.log('Adicionado checkbox de teste devido ao erro');
      }
    });
}

function submitAmostragem() {
  logActivity('form', 'Submit Amostragem');
  
  // Get form values manually instead of using FormData
  const numeroNf = document.querySelector('input[name="numero_nf"]')?.value || '';
  const statusSelected = document.querySelector('input[name="status"]:checked')?.value || '';
  const observacao = document.querySelector('textarea[name="observacao"]')?.value || '';
  
  // Get selected responsaveis
  const responsaveisChecked = document.querySelectorAll('input[name="responsaveis[]"]:checked');
  const responsaveis = Array.from(responsaveisChecked).map(checkbox => checkbox.value);
  
  // Get files
  const arquivoNf = document.querySelector('input[name="arquivo_nf"]')?.files[0];
  const fotos = document.querySelector('input[name="fotos[]"]')?.files;
  
  console.log('Valores capturados:');
  console.log('numero_nf:', numeroNf);
  console.log('status:', statusSelected);
  console.log('observacao:', observacao);
  console.log('responsaveis:', responsaveis);
  console.log('arquivo_nf:', arquivoNf);
  console.log('fotos:', fotos);
  
  // Validate required fields
  if (!numeroNf) {
    alert('Por favor, preencha o número da NF.');
    return;
  }
  
  if (!statusSelected) {
    alert('Por favor, selecione um status.');
    return;
  }
  
  if (responsaveis.length === 0) {
    alert('Por favor, selecione pelo menos um responsável.');
    return;
  }
  
  if (!arquivoNf) {
    alert('Por favor, anexe o PDF da NF.');
    return;
  }
  
  // Create FormData manually
  const formData = new FormData();
  formData.append('numero_nf', numeroNf);
  formData.append('status', statusSelected);
  formData.append('observacao', observacao);
  
  // Add responsaveis
  responsaveis.forEach(responsavel => {
    formData.append('responsaveis[]', responsavel);
  });
  
  // Add files
  if (arquivoNf) {
    formData.append('arquivo_nf', arquivoNf);
  }
  
  if (fotos && fotos.length > 0) {
    for (let i = 0; i < fotos.length; i++) {
      formData.append('fotos[]', fotos[i]);
    }
  }
  
  // Debug FormData
  console.log('FormData final:');
  for (let [key, value] of formData.entries()) {
    console.log(key, value);
  }
  
  fetch('/toners/amostragens', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      closeAmostragemModal();
      location.reload();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

function editAmostragem(id) {
  logActivity('user_action', 'Edit Amostragem', { id });
  // Implementar edição
}

function deleteAmostragem(id) {
  logActivity('user_action', 'Delete Amostragem', { id });
  if (confirm('Tem certeza que deseja excluir esta amostragem?')) {
    fetch(`/toners/amostragens/${id}`, { method: 'DELETE' })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        alert('Amostragem excluída com sucesso!');
        location.reload();
      } else {
        alert('Erro: ' + result.message);
      }
    });
  }
}

function printAmostragens() {
  logActivity('user_action', 'Print Amostragens');
  window.print();
}

function downloadAmostragemLog() {
  logActivity('user_action', 'Download Log');
  const report = {
    generated_at: new Date().toISOString(),
    activities: activityLog
  };
  const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `amostragens-log-${new Date().toISOString().slice(0,19)}.json`;
  a.click();
  URL.revokeObjectURL(url);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('openAmostragemBtn').addEventListener('click', openAmostragemModal);
  logActivity('system', 'Page Loaded');
});

// Export functions globally
window.openAmostragemModal = openAmostragemModal;
window.closeAmostragemModal = closeAmostragemModal;
window.toggleStatusFields = toggleStatusFields;
window.loadUsers = loadUsers;
window.submitAmostragem = submitAmostragem;
window.editAmostragem = editAmostragem;
window.deleteAmostragem = deleteAmostragem;
window.printAmostragens = printAmostragens;
window.downloadAmostragemLog = downloadAmostragemLog;
</script>
