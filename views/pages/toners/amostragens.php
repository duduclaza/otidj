
<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Amostragens</h1>
    <div class="flex space-x-3">
      <button id="toggleAmostragemFormBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nova Amostragem</span>
      </button>
    </div>
  </div>

  <!-- Formulário Inline de Nova Amostragem -->
  <div id="amostragemFormContainer" class="hidden bg-white border rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-900">Nova Amostragem</h2>
      <button id="closeAmostragemFormBtn" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="amostragemForm" class="space-y-6" enctype="multipart/form-data">
      <input type="hidden" name="id" id="amostragemId">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Número da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Número da NF *</label>
          <input type="text" name="numero_nf" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-3">Status *</label>
          <div class="flex space-x-4">
            <label class="flex items-center">
              <input type="radio" name="status" value="pendente" class="mr-2" onchange="toggleStatusFields()" checked>
              <span class="text-sm font-medium text-yellow-700">Pendente</span>
            </label>
            <label class="flex items-center">
              <input type="radio" name="status" value="aprovado" class="mr-2" onchange="toggleStatusFields()">
              <span class="text-sm font-medium text-green-700">Aprovado</span>
            </label>
            <label class="flex items-center">
              <input type="radio" name="status" value="reprovado" class="mr-2" onchange="toggleStatusFields()">
              <span class="text-sm font-medium text-red-700">Reprovado</span>
            </label>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Anexo da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Anexo da NF (PDF) *</label>
          <input id="arquivo_nf" type="file" name="arquivo_nf" accept="application/pdf,.pdf" required onchange="validatePdfFile(this)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <p class="text-xs text-gray-500 mt-1">Apenas PDF até 10MB</p>
          <div id="arquivo_nf_preview" class="mt-2"></div>
        </div>

        <!-- Fotos -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Fotos</label>
          <input type="file" name="fotos[]" accept="image/*" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <p class="text-xs text-gray-500 mt-1">Selecione uma ou mais fotos (JPG, PNG, etc.)</p>
        </div>
      </div>

      <!-- Responsáveis -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Responsáveis *</label>
        <div class="border border-gray-300 rounded-lg p-3 bg-white" style="min-height: 120px; max-height: 200px; overflow-y: auto;">
          <div id="responsaveisCheckboxes">
            <!-- Checkboxes will be loaded dynamically -->
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-1">Selecione um ou mais responsáveis</p>
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
          <input id="evidencias" type="file" name="evidencias[]" accept="image/*" multiple onchange="validateEvidenceFiles(this)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <p class="text-xs text-gray-500 mt-1">Selecione uma ou mais fotos como evidência do problema</p>
          <div id="evidencias_preview" class="mt-2"></div>
        </div>
      </div>

      <!-- Botões -->
      <div class="flex justify-end space-x-4 pt-4 border-t">
        <button type="button" onclick="closeAmostragemForm()" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button type="button" onclick="submitAmostragem()" class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Salvar Amostragem
        </button>
      </div>
    </form>
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
                <td class="px-4 py-2 text-sm">
                  <div class="flex items-center space-x-2">
                    <?php if (!empty($amostragem['arquivo_nf'])): ?>
                      <a href="/uploads/<?= e($amostragem['arquivo_nf']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs">PDF</a>
                    <?php endif; ?>
                    <button onclick="excluirAmostragem(<?= $amostragem['id'] ?>, '<?= e($amostragem['numero_nf']) ?>')" 
                            class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded-md font-medium transition-colors duration-200 shadow-sm hover:shadow-md">
                      Excluir
                    </button>
                  </div>
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

<!-- Modal removido - usando formulário inline -->

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
function toggleAmostragemForm() {
  const container = document.getElementById('amostragemFormContainer');
  const btn = document.getElementById('toggleAmostragemFormBtn');
  if (container.classList.contains('hidden')) {
    container.classList.remove('hidden');
    btn.innerHTML = `
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
      <span>Cancelar</span>
    `;
    // default status e usuários
    document.querySelector('input[name="status"][value="pendente"]').checked = true;
    selectedStatus = 'pendente';
    loadUsers();
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } else {
    closeAmostragemForm();
  }
}

function closeAmostragemForm() {
  const container = document.getElementById('amostragemFormContainer');
  const btn = document.getElementById('toggleAmostragemFormBtn');
  const form = document.getElementById('amostragemForm');
  container.classList.add('hidden');
  form.reset();
  btn.innerHTML = `
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
    </svg>
    <span>Nova Amostragem</span>
  `;
  // reset condicionais e previews
  document.querySelector('input[name="status"][value="pendente"]').checked = true;
  toggleStatusFields();
  clearFilePreview('arquivo_nf');
  clearFilePreview('evidencias');
}

function toggleStatusFields() {
  const status = document.querySelector('input[name="status"]:checked')?.value;
  const reprovadoFields = document.getElementById('reprovadoFields');
  logActivity('user_action', 'Status Changed', { status });
  selectedStatus = status;
  if (status === 'reprovado') {
    reprovadoFields.classList.remove('hidden');
    const obs = document.querySelector('textarea[name="observacao"]');
    const evid = document.getElementById('evidencias');
    if (obs) obs.required = true;
    if (evid) evid.required = true;
  } else {
    reprovadoFields.classList.add('hidden');
    const obs = document.querySelector('textarea[name="observacao"]');
    const evid = document.getElementById('evidencias');
    if (obs) obs.required = false;
    if (evid) evid.required = false;
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
          checkboxDiv.className = 'flex items-center p-2 hover:bg-blue-50 rounded-lg mb-1';
          
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.name = 'responsaveis[]';
          checkbox.value = JSON.stringify({ name: user.name, email: user.email });
          checkbox.id = `responsavel_${index}`;
          checkbox.className = 'mr-3 text-blue-600 focus:ring-blue-500 h-4 w-4';
          
          const label = document.createElement('label');
          label.htmlFor = `responsavel_${index}`;
          label.innerHTML = `<div class="flex flex-col"><span class="text-sm font-medium text-gray-900">${user.name}</span><span class="text-xs text-gray-500">${user.email}</span></div>`;
          label.className = 'cursor-pointer flex-1';
          
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
        checkbox.value = JSON.stringify({ name: 'Test User', email: 'test@example.com' });
        checkbox.id = 'responsavel_test';
        checkbox.className = 'mr-3 text-blue-600 focus:ring-blue-500 h-4 w-4';
        
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
  
  // Add responsaveis (JSON de name+email)
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
      closeAmostragemForm();
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

function excluirAmostragem(id, numeroNf) {
  console.log('Excluir amostragem:', id, numeroNf);
  deleteAmostragem(id);
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
  const toggleBtn = document.getElementById('toggleAmostragemFormBtn');
  if (toggleBtn) toggleBtn.addEventListener('click', toggleAmostragemForm);
  const closeBtn = document.getElementById('closeAmostragemFormBtn');
  if (closeBtn) closeBtn.addEventListener('click', closeAmostragemForm);
  logActivity('system', 'Page Loaded');
});

// Export functions globally
// Exports
window.toggleAmostragemForm = toggleAmostragemForm;
window.closeAmostragemForm = closeAmostragemForm;
window.toggleStatusFields = toggleStatusFields;
window.loadUsers = loadUsers;
window.submitAmostragem = submitAmostragem;
window.editAmostragem = editAmostragem;
window.deleteAmostragem = deleteAmostragem;
window.excluirAmostragem = excluirAmostragem;
window.printAmostragens = printAmostragens;
window.downloadAmostragemLog = downloadAmostragemLog;

// ===== Utilidades de validação e preview =====
function validatePdfFile(input) {
  const file = input.files[0];
  const maxSize = 10 * 1024 * 1024; // 10MB
  if (!file) return true;
  const allowed = ['application/pdf'];
  if (!allowed.includes(file.type)) {
    alert('Apenas arquivos PDF são permitidos.');
    input.value = '';
    return false;
  }
  if (file.size > maxSize) {
    alert('O PDF deve ter no máximo 10MB.');
    input.value = '';
    return false;
  }
  showFilePreview(file, 'arquivo_nf_preview');
  return true;
}

function validateEvidenceFiles(input) {
  const files = Array.from(input.files || []);
  if (files.length === 0) return true;
  const maxEach = 5 * 1024 * 1024; // 5MB
  const allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
  if (files.length > 5) {
    alert('Máximo de 5 imagens.');
    input.value='';
    return false;
  }
  for (const f of files) {
    if (!allowed.includes(f.type)) { alert('Apenas imagens (JPG, PNG, GIF, WEBP).'); input.value=''; return false; }
    if (f.size > maxEach) { alert(`Imagem muito grande: ${f.name}`); input.value=''; return false; }
  }
  showImagePreviews(files, 'evidencias_preview');
  return true;
}

function showFilePreview(file, containerId) {
  const c = document.getElementById(containerId);
  if (!c) return;
  c.innerHTML = `
    <div class="flex items-center p-2 bg-green-50 border border-green-200 rounded-lg">
      <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M8 6h8m5 5v7a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2h6"></path>
      </svg>
      <div class="flex-1">
        <p class="text-sm font-medium text-green-800">${file.name}</p>
        <p class="text-xs text-green-600">${(file.size/1048576).toFixed(2)} MB</p>
      </div>
      <button type="button" onclick="clearFilePreview('arquivo_nf')" class="text-green-600 hover:text-green-800">&times;</button>
    </div>`;
}

function showImagePreviews(files, containerId) {
  const c = document.getElementById(containerId);
  if (!c) return;
  c.innerHTML = '';
  files.forEach((file, idx) => {
    const r = new FileReader();
    r.onload = e => {
      const d = document.createElement('div');
      d.className = 'relative inline-block mr-2 mb-2';
      d.innerHTML = `
        <img src="${e.target.result}" alt="ev${idx}" class="w-20 h-20 object-cover rounded border" />
        <div class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs cursor-pointer" onclick="this.parentElement.remove()">×</div>`;
      c.appendChild(d);
    };
    r.readAsDataURL(file);
  });
}

function clearFilePreview(inputName) {
  const input = document.querySelector(`input[name="${inputName}"]`);
  const preview = document.getElementById(`${inputName}_preview`);
  if (input) input.value='';
  if (preview) preview.innerHTML='';
}
</script>
