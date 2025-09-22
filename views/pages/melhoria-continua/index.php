<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Melhoria Contínua</h1>
    <button id="newMelhoriaBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
      </svg>
      <span>Nova Melhoria</span>
    </button>
  </div>

  <!-- Filtros -->
  <div class="bg-white border rounded-lg p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
        <input type="text" id="searchInput" placeholder="Processo ou descrição..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
        <input type="date" id="dataInicio" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
        <input type="date" id="dataFim" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div class="flex items-end space-x-2">
        <button id="filterBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
          Filtrar
        </button>
        <button id="clearFilterBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
          Limpar
        </button>
      </div>
    </div>
  </div>

  <!-- Formulário Inline -->
  <div id="melhoriaFormContainer" class="hidden bg-white border rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-900">Nova Melhoria Contínua</h2>
      <button id="closeMelhoriaFormBtn" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="melhoriaForm" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Data Automática -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Data de Registro</label>
          <input type="text" readonly value="<?= date('d/m/Y H:i') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 cursor-not-allowed">
        </div>

        <!-- Departamento -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Departamento *</label>
          <select id="departamento" name="departamento_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Selecione o departamento...</option>
            <!-- Carregado via JavaScript -->
          </select>
        </div>
      </div>

      <!-- Processo -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Processo *</label>
        <input type="text" id="processo" name="processo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nome do processo a ser melhorado...">
      </div>

      <!-- Descrição da Melhoria -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição da Melhoria *</label>
        <textarea id="descricaoMelhoria" name="descricao_melhoria" rows="4" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Descreva detalhadamente a melhoria proposta..."></textarea>
      </div>

      <!-- Responsáveis -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Responsáveis pela Melhoria *</label>
        <select id="responsaveis" name="responsaveis[]" multiple required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" style="min-height: 100px;">
          <!-- Carregado via JavaScript -->
        </select>
        <p class="text-xs text-gray-500 mt-1">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplos responsáveis</p>
      </div>

      <!-- Status (readonly) -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <input type="text" readonly value="Pendente" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 cursor-not-allowed">
      </div>

      <!-- Pontuação (apenas para admins) -->
      <div id="pontuacaoContainer" class="hidden">
        <label class="block text-sm font-medium text-gray-700 mb-1">Pontuação (Admin)</label>
        <input type="number" id="pontuacao" name="pontuacao" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0-100">
      </div>

      <!-- Observação -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Observação</label>
        <textarea id="observacao" name="observacao" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Observações adicionais (opcional)..."></textarea>
      </div>

      <!-- Resultado -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Resultado</label>
        <textarea id="resultado" name="resultado" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Resultado esperado ou obtido (opcional)..."></textarea>
      </div>

      <!-- Anexos -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Anexos (Imagens e PDFs)</label>
        <input type="file" id="anexos" name="anexos[]" multiple accept="image/*,.pdf" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <p class="text-xs text-gray-500 mt-1">Máximo 10 arquivos, 10MB cada. Formatos: JPG, PNG, GIF, PDF</p>
      </div>

      <!-- Botões -->
      <div class="flex justify-end space-x-4 pt-4 border-t">
        <button type="button" onclick="closeMelhoriaForm()" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Salvar Melhoria
        </button>
      </div>
    </form>
  </div>

  <!-- Grid de Melhorias -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processo</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsáveis</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pontuação</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Anexos</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody id="melhoriaTableBody" class="bg-white divide-y divide-gray-200">
          <!-- Melhorias serão carregadas aqui -->
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
// Variáveis globais
let melhoriaData = [];
let departamentos = [];
let usuarios = [];
let isAdmin = false;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
  // Verificar se é admin
  isAdmin = checkIfAdmin();
  
  // Mostrar campo de pontuação para admins
  if (isAdmin) {
    document.getElementById('pontuacaoContainer').classList.remove('hidden');
  }
  
  loadDepartamentos();
  loadUsuarios();
  loadMelhoriaData();
  
  // Event listeners
  document.getElementById('newMelhoriaBtn').addEventListener('click', openMelhoriaForm);
  document.getElementById('closeMelhoriaFormBtn').addEventListener('click', closeMelhoriaForm);
  document.getElementById('melhoriaForm').addEventListener('submit', submitMelhoria);
  document.getElementById('filterBtn').addEventListener('click', applyFilters);
  document.getElementById('clearFilterBtn').addEventListener('click', clearFilters);
});

// Verificar se é admin
function checkIfAdmin() {
  // Esta função deve verificar o papel do usuário na sessão
  // Por enquanto, retorna false - implementar conforme necessário
  return false;
}

// Carregar departamentos
async function loadDepartamentos() {
  try {
    const response = await fetch('/melhoria-continua/departamentos');
    const data = await response.json();
    
    if (data.success) {
      departamentos = data.data;
      const select = document.getElementById('departamento');
      select.innerHTML = '<option value="">Selecione o departamento...</option>';
      
      departamentos.forEach(dept => {
        const option = document.createElement('option');
        option.value = dept.id;
        option.textContent = dept.nome;
        select.appendChild(option);
      });
    }
  } catch (error) {
    console.error('Erro ao carregar departamentos:', error);
  }
}

// Carregar usuários
async function loadUsuarios() {
  try {
    const response = await fetch('/melhoria-continua/usuarios');
    const data = await response.json();
    
    if (data.success) {
      usuarios = data.data;
      const select = document.getElementById('responsaveis');
      select.innerHTML = '';
      
      usuarios.forEach(user => {
        const option = document.createElement('option');
        option.value = user.id;
        option.textContent = user.name;
        select.appendChild(option);
      });
    }
  } catch (error) {
    console.error('Erro ao carregar usuários:', error);
  }
}

// Carregar dados das melhorias
async function loadMelhoriaData() {
  try {
    const params = new URLSearchParams();
    const search = document.getElementById('searchInput').value;
    const dataInicio = document.getElementById('dataInicio').value;
    const dataFim = document.getElementById('dataFim').value;
    
    if (search) params.append('search', search);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    
    const response = await fetch('/melhoria-continua/list?' + params.toString());
    const data = await response.json();
    
    if (data.success) {
      melhoriaData = data.data;
      renderMelhoriaTable();
    } else {
      alert('Erro ao carregar dados: ' + data.message);
    }
  } catch (error) {
    console.error('Erro ao carregar dados:', error);
    alert('Erro ao carregar dados das melhorias');
  }
}

// Renderizar tabela
function renderMelhoriaTable() {
  const tbody = document.getElementById('melhoriaTableBody');
  
  if (melhoriaData.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">Nenhuma melhoria encontrada</td></tr>';
    return;
  }
  
  tbody.innerHTML = melhoriaData.map(item => `
    <tr class="hover:bg-gray-50">
      <td class="px-4 py-3 text-sm text-gray-900">${formatDate(item.data_registro)}</td>
      <td class="px-4 py-3 text-sm text-gray-900">${item.departamento_nome || 'N/A'}</td>
      <td class="px-4 py-3 text-sm">
        <div class="max-w-xs truncate" title="${item.processo}">${item.processo}</div>
      </td>
      <td class="px-4 py-3 text-sm">
        <div class="max-w-xs truncate" title="${item.descricao_melhoria}">${item.descricao_melhoria}</div>
      </td>
      <td class="px-4 py-3 text-sm">
        <div class="max-w-xs truncate" title="${item.responsaveis_nomes || 'N/A'}">${item.responsaveis_nomes || 'N/A'}</div>
      </td>
      <td class="px-4 py-3 text-sm">
        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(item.status)}">${getStatusText(item.status)}</span>
      </td>
      <td class="px-4 py-3 text-center text-sm font-medium">
        ${isAdmin ? `<span class="editable-pontuacao cursor-pointer hover:bg-yellow-100 px-2 py-1 rounded" data-id="${item.id}">${item.pontuacao || '-'}</span>` : (item.pontuacao || '-')}
      </td>
      <td class="px-4 py-3 text-center text-sm">
        ${item.total_anexos > 0 ? `<span class="text-blue-600">${item.total_anexos} arquivo(s)</span>` : '-'}
      </td>
      <td class="px-4 py-3 text-sm">
        <div class="flex space-x-2">
          ${isAdmin ? `
            <button onclick="editStatus(${item.id})" class="text-blue-600 hover:text-blue-800 text-xs">Status</button>
            <button onclick="editObservacao(${item.id})" class="text-green-600 hover:text-green-800 text-xs">Obs</button>
            <button onclick="editResultado(${item.id})" class="text-purple-600 hover:text-purple-800 text-xs">Result</button>
          ` : ''}
          <button onclick="printMelhoria(${item.id})" class="text-gray-600 hover:text-gray-800 text-xs">Imprimir</button>
          <button onclick="deleteMelhoria(${item.id})" class="text-red-600 hover:text-red-800 text-xs">Excluir</button>
        </div>
      </td>
    </tr>
  `).join('');
  
  // Adicionar event listeners para edição inline de pontuação
  if (isAdmin) {
    document.querySelectorAll('.editable-pontuacao').forEach(el => {
      el.addEventListener('click', function() {
        editPontuacao(this.dataset.id, this.textContent.trim());
      });
    });
  }
}

// Cores do status
function getStatusColor(status) {
  switch (status) {
    case 'pendente': return 'bg-yellow-100 text-yellow-800';
    case 'em_andamento': return 'bg-blue-100 text-blue-800';
    case 'concluido': return 'bg-green-100 text-green-800';
    case 'cancelado': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
}

// Texto do status
function getStatusText(status) {
  switch (status) {
    case 'pendente': return 'Pendente';
    case 'em_andamento': return 'Em Andamento';
    case 'concluido': return 'Concluído';
    case 'cancelado': return 'Cancelado';
    default: return 'Desconhecido';
  }
}

// Formatar data
function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('pt-BR');
}

// Abrir formulário
function openMelhoriaForm() {
  document.getElementById('melhoriaForm').reset();
  document.getElementById('melhoriaFormContainer').classList.remove('hidden');
}

// Fechar formulário
function closeMelhoriaForm() {
  document.getElementById('melhoriaFormContainer').classList.add('hidden');
}

// Submeter formulário
async function submitMelhoria(e) {
  e.preventDefault();
  
  const formData = new FormData(document.getElementById('melhoriaForm'));
  
  try {
    const response = await fetch('/melhoria-continua/store', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      closeMelhoriaForm();
      loadMelhoriaData();
      alert(result.message);
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao salvar:', error);
    alert('Erro ao salvar melhoria');
  }
}

// Aplicar filtros
function applyFilters() {
  loadMelhoriaData();
}

// Limpar filtros
function clearFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('dataInicio').value = '';
  document.getElementById('dataFim').value = '';
  loadMelhoriaData();
}

// Editar pontuação (inline)
function editPontuacao(id, currentValue) {
  const newValue = prompt('Nova pontuação (0-100):', currentValue === '-' ? '' : currentValue);
  if (newValue !== null) {
    updatePontuacao(id, newValue);
  }
}

// Atualizar pontuação
async function updatePontuacao(id, pontuacao) {
  try {
    const formData = new FormData();
    formData.append('pontuacao', pontuacao);
    
    const response = await fetch(`/melhoria-continua/${id}/pontuacao`, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      loadMelhoriaData();
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao atualizar pontuação:', error);
    alert('Erro ao atualizar pontuação');
  }
}

// Editar status
function editStatus(id) {
  const newStatus = prompt('Novo status:\n1 - Pendente\n2 - Em Andamento\n3 - Concluído\n4 - Cancelado\n\nDigite o número:');
  if (newStatus) {
    const statusMap = {
      '1': 'pendente',
      '2': 'em_andamento', 
      '3': 'concluido',
      '4': 'cancelado'
    };
    
    if (statusMap[newStatus]) {
      updateStatus(id, statusMap[newStatus]);
    } else {
      alert('Status inválido');
    }
  }
}

// Atualizar status
async function updateStatus(id, status) {
  try {
    const formData = new FormData();
    formData.append('status', status);
    
    const response = await fetch(`/melhoria-continua/${id}/status`, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      loadMelhoriaData();
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao atualizar status:', error);
    alert('Erro ao atualizar status');
  }
}

// Editar observação
function editObservacao(id) {
  const newObs = prompt('Nova observação:');
  if (newObs !== null) {
    updateObservacao(id, newObs);
  }
}

// Atualizar observação
async function updateObservacao(id, observacao) {
  try {
    const formData = new FormData();
    formData.append('observacao', observacao);
    
    const response = await fetch(`/melhoria-continua/${id}/observacao`, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert('Observação atualizada com sucesso');
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao atualizar observação:', error);
    alert('Erro ao atualizar observação');
  }
}

// Editar resultado
function editResultado(id) {
  const newResult = prompt('Novo resultado:');
  if (newResult !== null) {
    updateResultado(id, newResult);
  }
}

// Atualizar resultado
async function updateResultado(id, resultado) {
  try {
    const formData = new FormData();
    formData.append('resultado', resultado);
    
    const response = await fetch(`/melhoria-continua/${id}/resultado`, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert('Resultado atualizado com sucesso');
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao atualizar resultado:', error);
    alert('Erro ao atualizar resultado');
  }
}

// Imprimir melhoria
function printMelhoria(id) {
  window.open(`/melhoria-continua/${id}/print`, '_blank');
}

// Excluir melhoria
async function deleteMelhoria(id) {
  if (!confirm('Tem certeza que deseja excluir esta melhoria?')) return;
  
  try {
    const response = await fetch(`/melhoria-continua/${id}/delete`, { method: 'DELETE' });
    const result = await response.json();
    
    if (result.success) {
      loadMelhoriaData();
      alert(result.message);
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao excluir:', error);
    alert('Erro ao excluir melhoria');
  }
}
</script>
