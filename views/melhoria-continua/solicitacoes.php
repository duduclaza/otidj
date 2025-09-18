<?php 
// Function to safely escape output
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>

<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<?php if (isset($success)): ?>
  <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
    <?= e($success) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Solicitação de Melhorias</h1>
    <button onclick="toggleSolicitacaoForm()" id="toggleFormBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
      </svg>
      <span>Nova Solicitação</span>
    </button>
  </div>

  <!-- Solicitação Form -->
  <div id="solicitacaoFormContainer" class="hidden bg-white rounded-lg shadow-lg border border-gray-200 p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-900">Nova Solicitação de Melhoria</h3>
      <button onclick="cancelSolicitacaoForm()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="solicitacaoForm" class="space-y-6" enctype="multipart/form-data">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Usuário (automático) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Usuário</label>
          <input type="text" value="<?= e($_SESSION['user_name'] ?? 'Usuário') ?>" readonly 
                 class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-gray-100 cursor-not-allowed">
        </div>

        <!-- Data (automática) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Data da Solicitação</label>
          <input type="text" value="<?= date('d/m/Y H:i') ?>" readonly 
                 class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-gray-100 cursor-not-allowed">
        </div>

        <!-- Setor -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Setor *</label>
          <select name="setor" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            <option value="">Selecione o setor</option>
            <?php if (isset($setores) && is_array($setores)): ?>
              <?php foreach ($setores as $setor): ?>
                <option value="<?= e($setor) ?>"><?= e($setor) ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <!-- Status (automático) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <input type="text" value="Pendente" readonly 
                 class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-gray-100 cursor-not-allowed">
        </div>
      </div>

      <!-- Processo -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Processo *</label>
        <input type="text" name="processo" required 
               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
               placeholder="Descreva o processo relacionado à melhoria">
      </div>

      <!-- Descrição da Melhoria -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Descrição da Melhoria *</label>
        <textarea name="descricao_melhoria" required rows="4" 
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  placeholder="Descreva detalhadamente a melhoria proposta..."></textarea>
      </div>

      <!-- Responsáveis -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Responsáveis *</label>
        <div class="border border-gray-300 rounded-lg p-4 max-h-48 overflow-y-auto">
          <?php if (isset($usuarios) && is_array($usuarios)): ?>
            <?php foreach ($usuarios as $usuario): ?>
              <label class="flex items-center space-x-2 py-1">
                <input type="checkbox" name="responsaveis[]" value="<?= $usuario['id'] ?>" 
                       class="form-checkbox h-4 w-4 text-blue-600 rounded">
                <span class="text-sm"><?= e($usuario['name']) ?> (<?= e($usuario['email']) ?>)</span>
              </label>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-gray-500 text-sm">Nenhum usuário encontrado</p>
          <?php endif; ?>
        </div>
        <p class="text-xs text-gray-500 mt-1">Selecione um ou mais responsáveis que receberão notificação por email</p>
      </div>

      <!-- Resultado Esperado -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Resultado Esperado *</label>
        <textarea name="resultado_esperado" required rows="3" 
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  placeholder="Descreva o resultado esperado com a implementação desta melhoria..."></textarea>
      </div>

      <!-- Observações -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
        <textarea name="observacoes" rows="3" 
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  placeholder="Observações adicionais (opcional)..."></textarea>
      </div>

      <!-- Anexos -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Anexos</label>
        <input type="file" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf" 
               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        <p class="text-xs text-gray-500 mt-1">Máximo 5 arquivos, até 5MB cada. Formatos: JPG, PNG, GIF, PDF</p>
      </div>

      <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
        <button type="button" onclick="cancelSolicitacaoForm()" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button type="button" onclick="submitSolicitacao()" id="submitBtn" class="px-6 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Enviar Solicitação
        </button>
      </div>
    </form>
  </div>

  <!-- Solicitações Grid -->
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Minhas Solicitações</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Setor</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsáveis</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody id="solicitacoesTableBody" class="bg-white divide-y divide-gray-200">
          <!-- Solicitações will be loaded here via JavaScript -->
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal de Detalhes -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">Detalhes da Solicitação</h3>
      <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <div id="detailsContent" class="p-6">
      <!-- Content will be loaded here -->
    </div>
  </div>
</div>

<script>
// Load solicitações on page load
document.addEventListener('DOMContentLoaded', function() {
  loadSolicitacoes();
});

function toggleSolicitacaoForm() {
  const container = document.getElementById('solicitacaoFormContainer');
  const btn = document.getElementById('toggleFormBtn');
  
  if (container.classList.contains('hidden')) {
    container.classList.remove('hidden');
    btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg><span>Cancelar</span>';
  } else {
    cancelSolicitacaoForm();
  }
}

function cancelSolicitacaoForm() {
  const container = document.getElementById('solicitacaoFormContainer');
  const btn = document.getElementById('toggleFormBtn');
  const form = document.getElementById('solicitacaoForm');
  
  container.classList.add('hidden');
  form.reset();
  btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg><span>Nova Solicitação</span>';
}

function submitSolicitacao() {
  const form = document.getElementById('solicitacaoForm');
  const formData = new FormData(form);
  
  // Validate responsáveis
  const responsaveis = formData.getAll('responsaveis[]');
  if (responsaveis.length === 0) {
    alert('Selecione pelo menos um responsável');
    return;
  }
  
  const submitBtn = document.getElementById('submitBtn');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Enviando...';
  
  fetch('/melhoria-continua/solicitacoes/create', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      cancelSolicitacaoForm();
      loadSolicitacoes();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Erro de conexão');
  })
  .finally(() => {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Enviar Solicitação';
  });
}

function loadSolicitacoes() {
  fetch('/melhoria-continua/solicitacoes/list', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      displaySolicitacoes(result.solicitacoes);
    } else {
      alert('Erro ao carregar solicitações: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Erro de conexão');
  });
}

function displaySolicitacoes(solicitacoes) {
  const tbody = document.getElementById('solicitacoesTableBody');
  
  if (solicitacoes.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Nenhuma solicitação encontrada</td></tr>';
    return;
  }
  
  tbody.innerHTML = solicitacoes.map(sol => {
    const statusColors = {
      'pendente': 'bg-yellow-100 text-yellow-800',
      'em_analise': 'bg-blue-100 text-blue-800',
      'aprovado': 'bg-green-100 text-green-800',
      'rejeitado': 'bg-red-100 text-red-800',
      'implementado': 'bg-purple-100 text-purple-800'
    };
    
    const statusLabels = {
      'pendente': 'Pendente',
      'em_analise': 'Em Análise',
      'aprovado': 'Aprovado',
      'rejeitado': 'Rejeitado',
      'implementado': 'Implementado'
    };
    
    return `
      <tr>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${sol.id}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(sol.data_solicitacao).toLocaleDateString('pt-BR')}</td>
        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">${sol.processo}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${sol.setor}</td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[sol.status] || 'bg-gray-100 text-gray-800'}">
            ${statusLabels[sol.status] || sol.status}
          </span>
        </td>
        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">${sol.responsaveis || 'N/A'}</td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
          <button onclick="viewDetails(${sol.id})" class="text-blue-600 hover:text-blue-900 mr-3">Ver Detalhes</button>
          <button onclick="printSolicitacao(${sol.id})" class="text-green-600 hover:text-green-900">Imprimir</button>
        </td>
      </tr>
    `;
  }).join('');
}

function viewDetails(id) {
  fetch(`/melhoria-continua/solicitacoes/${id}/details`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      showDetailsModal(result.solicitacao, result.responsaveis, result.anexos);
    } else {
      alert('Erro ao carregar detalhes: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Erro de conexão');
  });
}

function showDetailsModal(solicitacao, responsaveis, anexos) {
  const modal = document.getElementById('detailsModal');
  const content = document.getElementById('detailsContent');
  
  const statusLabels = {
    'pendente': 'Pendente',
    'em_analise': 'Em Análise',
    'aprovado': 'Aprovado',
    'rejeitado': 'Rejeitado',
    'implementado': 'Implementado'
  };
  
  content.innerHTML = `
    <div class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ID da Solicitação</label>
          <p class="text-lg font-semibold text-gray-900">#${solicitacao.id}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Data da Solicitação</label>
          <p class="text-gray-900">${new Date(solicitacao.data_solicitacao).toLocaleDateString('pt-BR', {
            year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
          })}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
          <p class="text-gray-900">${solicitacao.usuario_nome}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Setor</label>
          <p class="text-gray-900">${solicitacao.setor}</p>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-800">
            ${statusLabels[solicitacao.status] || solicitacao.status}
          </span>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Processo</label>
        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">${solicitacao.processo}</p>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Descrição da Melhoria</label>
        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">${solicitacao.descricao_melhoria}</p>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Resultado Esperado</label>
        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">${solicitacao.resultado_esperado}</p>
      </div>
      
      ${solicitacao.observacoes ? `
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">${solicitacao.observacoes}</p>
      </div>
      ` : ''}
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Responsáveis</label>
        <div class="bg-gray-50 p-3 rounded-lg">
          ${responsaveis.map(resp => `
            <div class="flex items-center space-x-2 py-1">
              <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
              <span class="text-gray-900">${resp.usuario_nome} (${resp.usuario_email})</span>
            </div>
          `).join('')}
        </div>
      </div>
      
      ${anexos.length > 0 ? `
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Anexos</label>
        <div class="bg-gray-50 p-3 rounded-lg space-y-2">
          ${anexos.map(anexo => `
            <div class="flex items-center space-x-3 p-2 bg-white rounded border">
              <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">${anexo.nome_original}</p>
                <p class="text-xs text-gray-500">${(anexo.tamanho_arquivo / 1024 / 1024).toFixed(2)} MB</p>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
      ` : ''}
    </div>
  `;
  
  modal.classList.remove('hidden');
}

function closeDetailsModal() {
  document.getElementById('detailsModal').classList.add('hidden');
}

function printSolicitacao(id) {
  window.open(`/melhoria-continua/solicitacoes/${id}/print`, '_blank');
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeDetailsModal();
  }
});

// Close modal on outside click
document.getElementById('detailsModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeDetailsModal();
  }
});
</script>
