<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">FMEA - Análise de Modo e Efeito de Falha</h1>
    <div class="flex space-x-3">
      <button id="chartsBtn" type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <span>Gráficos FMEA</span>
      </button>
      <button id="newFmeaBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Novo Registro FMEA</span>
      </button>
    </div>
  </div>

  <!-- Formulário FMEA -->
  <div id="fmeaFormContainer" class="hidden bg-white border rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-900">Registro FMEA</h2>
      <button id="closeFmeaFormBtn" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="fmeaForm" class="space-y-6" data-ajax="true">
      <input type="hidden" id="fmeaId" name="id">
      
      <div class="grid grid-cols-1 gap-6">
        <!-- Modo de Falha -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Modo de Falha *</label>
          <textarea id="modoFalha" name="modo_falha" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Descreva como o processo/produto pode falhar..."></textarea>
        </div>

        <!-- Efeito da Falha -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Efeito da Falha *</label>
          <textarea id="efeitoFalha" name="efeito_falha" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Descreva o impacto/consequência da falha..."></textarea>
        </div>

        <!-- Avaliações (Severidade, Ocorrência, Detecção) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Severidade (0-10) *</label>
            <select id="severidade" name="severidade" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Selecione...</option>
              <option value="1">1 - Sem efeito</option>
              <option value="2">2 - Muito pequeno</option>
              <option value="3">3 - Pequeno</option>
              <option value="4">4 - Muito baixo</option>
              <option value="5">5 - Baixo</option>
              <option value="6">6 - Moderado</option>
              <option value="7">7 - Alto</option>
              <option value="8">8 - Muito alto</option>
              <option value="9">9 - Perigoso</option>
              <option value="10">10 - Catastrófico</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ocorrência (0-10) *</label>
            <select id="ocorrencia" name="ocorrencia" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Selecione...</option>
              <option value="1">1 - Remota</option>
              <option value="2">2 - Muito baixa</option>
              <option value="3">3 - Baixa</option>
              <option value="4">4 - Moderadamente baixa</option>
              <option value="5">5 - Moderada</option>
              <option value="6">6 - Moderadamente alta</option>
              <option value="7">7 - Alta</option>
              <option value="8">8 - Muito alta</option>
              <option value="9">9 - Extremamente alta</option>
              <option value="10">10 - Quase certa</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Detecção (0-10) *</label>
            <select id="deteccao" name="deteccao" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Selecione...</option>
              <option value="1">1 - Quase certa</option>
              <option value="2">2 - Muito alta</option>
              <option value="3">3 - Alta</option>
              <option value="4">4 - Moderadamente alta</option>
              <option value="5">5 - Moderada</option>
              <option value="6">6 - Baixa</option>
              <option value="7">7 - Muito baixa</option>
              <option value="8">8 - Remota</option>
              <option value="9">9 - Muito remota</option>
              <option value="10">10 - Quase impossível</option>
            </select>
          </div>
        </div>

        <!-- RPN e Risco (calculados automaticamente) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">RPN (Calculado)</label>
            <input type="text" id="rpnDisplay" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 font-medium" placeholder="Será calculado automaticamente">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Classificação de Risco</label>
            <input type="text" id="riscoDisplay" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 font-medium" placeholder="Será calculado automaticamente">
          </div>
        </div>

        <!-- Ação Sugerida -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Ação Sugerida *</label>
          <textarea id="acaoSugerida" name="acao_sugerida" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Descreva as ações recomendadas para mitigar o risco..."></textarea>
        </div>
      </div>

      <!-- Botões -->
      <div class="flex justify-end space-x-4 pt-4 border-t">
        <button type="button" onclick="closeFmeaForm()" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Salvar Registro
        </button>
      </div>
    </form>
  </div>

  <!-- Grid de Registros FMEA -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modo de Falha</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Efeito</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">S</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">O</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">D</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">RPN</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risco</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody id="fmeaTableBody" class="bg-white divide-y divide-gray-200">
          <!-- Registros serão carregados aqui -->
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal de Gráficos -->
<div id="chartsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-6xl max-h-[90vh] overflow-y-auto w-full mx-4">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xl font-semibold text-gray-900">Gráficos e Análises FMEA</h3>
      <button onclick="closeChartsModal()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <!-- Estatísticas Resumo -->
    <div id="statsCards" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <!-- Cards serão carregados aqui -->
    </div>
    
    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Distribuição por Risco</h4>
        <canvas id="riscoChart" width="400" height="300"></canvas>
      </div>
      
      <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Evolução RPN Médio</h4>
        <canvas id="rpnChart" width="400" height="300"></canvas>
      </div>
      
      <div class="bg-gray-50 p-4 rounded-lg lg:col-span-2">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Top 10 Maiores RPNs</h4>
        <canvas id="topRpnChart" width="800" height="400"></canvas>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Variáveis globais
let fmeaData = [];
let editingId = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
  loadFmeaData();
  
  // Event listeners
  document.getElementById('newFmeaBtn').addEventListener('click', openFmeaForm);
  document.getElementById('closeFmeaFormBtn').addEventListener('click', closeFmeaForm);
  document.getElementById('fmeaForm').addEventListener('submit', submitFmea);
  document.getElementById('chartsBtn').addEventListener('click', openChartsModal);
  
  // Calcular RPN automaticamente
  ['severidade', 'ocorrencia', 'deteccao'].forEach(field => {
    document.getElementById(field).addEventListener('change', calculateRPN);
  });
});

// Carregar dados FMEA
async function loadFmeaData() {
  try {
    const response = await fetch('/fmea/list');
    const data = await response.json();
    
    if (data.success) {
      fmeaData = data.data;
      renderFmeaTable();
    } else {
      alert('Erro ao carregar dados: ' + data.message);
    }
  } catch (error) {
    console.error('Erro ao carregar dados:', error);
    alert('Erro ao carregar dados FMEA');
  }
}

// Renderizar tabela
function renderFmeaTable() {
  const tbody = document.getElementById('fmeaTableBody');
  
  if (fmeaData.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">Nenhum registro encontrado</td></tr>';
    return;
  }
  
  tbody.innerHTML = fmeaData.map(item => `
    <tr class="hover:bg-gray-50">
      <td class="px-4 py-3 text-sm">
        <div class="max-w-xs truncate" title="${item.modo_falha}">${item.modo_falha}</div>
      </td>
      <td class="px-4 py-3 text-sm">
        <div class="max-w-xs truncate" title="${item.efeito_falha}">${item.efeito_falha}</div>
      </td>
      <td class="px-4 py-3 text-center text-sm font-medium">${item.severidade}</td>
      <td class="px-4 py-3 text-center text-sm font-medium">${item.ocorrencia}</td>
      <td class="px-4 py-3 text-center text-sm font-medium">${item.deteccao}</td>
      <td class="px-4 py-3 text-center text-sm font-bold ${getRpnColor(item.rpn)}">${item.rpn}</td>
      <td class="px-4 py-3 text-sm">
        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getRiscoColor(item.risco)}">${item.risco}</span>
      </td>
      <td class="px-4 py-3 text-sm text-gray-500">${formatDate(item.data_registro)}</td>
      <td class="px-4 py-3 text-sm">
        <div class="flex space-x-2">
          <button onclick="editFmea(${item.id})" class="text-blue-600 hover:text-blue-800 text-xs">Editar</button>
          <button onclick="printFmea(${item.id})" class="text-green-600 hover:text-green-800 text-xs">Imprimir</button>
          <button onclick="deleteFmea(${item.id})" class="text-red-600 hover:text-red-800 text-xs">Excluir</button>
        </div>
      </td>
    </tr>
  `).join('');
}

// Cores baseadas no RPN
function getRpnColor(rpn) {
  if (rpn >= 200) return 'text-red-600';
  if (rpn >= 100) return 'text-orange-600';
  if (rpn >= 40) return 'text-yellow-600';
  return 'text-green-600';
}

// Cores baseadas no risco
function getRiscoColor(risco) {
  switch (risco) {
    case 'Risco Crítico': return 'bg-red-100 text-red-800';
    case 'Risco Alto': return 'bg-orange-100 text-orange-800';
    case 'Risco Moderado': return 'bg-yellow-100 text-yellow-800';
    default: return 'bg-green-100 text-green-800';
  }
}

// Formatar data
function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('pt-BR');
}

// Abrir formulário
function openFmeaForm() {
  editingId = null;
  document.getElementById('fmeaForm').reset();
  document.getElementById('fmeaId').value = '';
  document.getElementById('rpnDisplay').value = '';
  document.getElementById('riscoDisplay').value = '';
  document.getElementById('fmeaFormContainer').classList.remove('hidden');
}

// Fechar formulário
function closeFmeaForm() {
  document.getElementById('fmeaFormContainer').classList.add('hidden');
}

// Calcular RPN
function calculateRPN() {
  const severidade = parseInt(document.getElementById('severidade').value) || 0;
  const ocorrencia = parseInt(document.getElementById('ocorrencia').value) || 0;
  const deteccao = parseInt(document.getElementById('deteccao').value) || 0;
  
  const rpn = severidade * ocorrencia * deteccao;
  document.getElementById('rpnDisplay').value = rpn;
  
  // Calcular risco
  let risco = '';
  if (rpn < 40) risco = 'Não Crítico';
  else if (rpn < 100) risco = 'Risco Moderado';
  else if (rpn < 200) risco = 'Risco Alto';
  else risco = 'Risco Crítico';
  
  document.getElementById('riscoDisplay').value = risco;
  document.getElementById('riscoDisplay').className = `w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 font-medium ${getRiscoColor(risco).replace('bg-', 'text-').replace('-100', '-600')}`;
}

// Submeter formulário
async function submitFmea(e) {
  e.preventDefault();
  
  const overlay = document.getElementById('loadingOverlay');
  const submitBtn = document.querySelector('#fmeaForm button[type="submit"]');
  
  try {
    // Mostrar overlay e desabilitar botão
    overlay.classList.add('active');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Salvando...';
    }
    
    const formData = new FormData(document.getElementById('fmeaForm'));
    const url = editingId ? `/fmea/${editingId}/update` : '/fmea/store';
    
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      closeFmeaForm();
      loadFmeaData();
      alert(result.message);
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao salvar:', error);
    alert('Erro ao salvar registro');
  } finally {
    // Remover overlay e reabilitar botão
    overlay.classList.remove('active');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = editingId ? 'Atualizar' : 'Salvar';
    }
  }
}

// Editar registro
async function editFmea(id) {
  try {
    const response = await fetch(`/fmea/${id}`);
    const data = await response.json();
    
    if (data.success) {
      const fmea = data.data;
      editingId = id;
      
      document.getElementById('fmeaId').value = fmea.id;
      document.getElementById('modoFalha').value = fmea.modo_falha;
      document.getElementById('efeitoFalha').value = fmea.efeito_falha;
      document.getElementById('severidade').value = fmea.severidade;
      document.getElementById('ocorrencia').value = fmea.ocorrencia;
      document.getElementById('deteccao').value = fmea.deteccao;
      document.getElementById('acaoSugerida').value = fmea.acao_sugerida;
      
      calculateRPN();
      document.getElementById('fmeaFormContainer').classList.remove('hidden');
    } else {
      alert('Erro ao carregar registro: ' + data.message);
    }
  } catch (error) {
    console.error('Erro ao carregar registro:', error);
    alert('Erro ao carregar registro');
  }
}

// Excluir registro
async function deleteFmea(id) {
  if (!confirm('Tem certeza que deseja excluir este registro?')) return;
  
  try {
    const response = await fetch(`/fmea/${id}/delete`, { method: 'DELETE' });
    const result = await response.json();
    
    if (result.success) {
      loadFmeaData();
      alert(result.message);
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    console.error('Erro ao excluir:', error);
    alert('Erro ao excluir registro');
  }
}

// Imprimir registro
function printFmea(id) {
  window.open(`/fmea/${id}/print`, '_blank');
}

// Abrir modal de gráficos
async function openChartsModal() {
  document.getElementById('chartsModal').classList.remove('hidden');
  await loadChartData();
}

// Fechar modal de gráficos
function closeChartsModal() {
  document.getElementById('chartsModal').classList.add('hidden');
}

// Carregar dados dos gráficos
async function loadChartData() {
  try {
    const response = await fetch('/fmea/charts');
    const data = await response.json();
    
    if (data.success) {
      renderStatsCards(data.statistics);
      renderRiscoChart(data.risco_distribution);
      renderRpnChart(data.rpn_timeline);
      renderTopRpnChart(data.top_rpn);
    } else {
      alert('Erro ao carregar dados dos gráficos: ' + data.message);
    }
  } catch (error) {
    console.error('Erro ao carregar gráficos:', error);
    alert('Erro ao carregar gráficos');
  }
}

// Renderizar cards de estatísticas
function renderStatsCards(stats) {
  const container = document.getElementById('statsCards');
  container.innerHTML = `
    <div class="bg-white p-4 rounded-lg border">
      <div class="text-2xl font-bold text-blue-600">${stats.total_registros}</div>
      <div class="text-sm text-gray-600">Total de Registros</div>
    </div>
    <div class="bg-white p-4 rounded-lg border">
      <div class="text-2xl font-bold text-orange-600">${Math.round(stats.rpn_medio)}</div>
      <div class="text-sm text-gray-600">RPN Médio</div>
    </div>
    <div class="bg-white p-4 rounded-lg border">
      <div class="text-2xl font-bold text-red-600">${stats.criticos}</div>
      <div class="text-sm text-gray-600">Riscos Críticos</div>
    </div>
    <div class="bg-white p-4 rounded-lg border">
      <div class="text-2xl font-bold text-yellow-600">${stats.altos}</div>
      <div class="text-sm text-gray-600">Riscos Altos</div>
    </div>
  `;
}

// Renderizar gráfico de distribuição de risco
function renderRiscoChart(data) {
  const ctx = document.getElementById('riscoChart').getContext('2d');
  
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: data.map(item => item.risco),
      datasets: [{
        data: data.map(item => item.total),
        backgroundColor: [
          '#10B981', // Verde - Não Crítico
          '#F59E0B', // Amarelo - Moderado
          '#F97316', // Laranja - Alto
          '#EF4444'  // Vermelho - Crítico
        ]
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });
}

// Renderizar gráfico de evolução RPN
function renderRpnChart(data) {
  const ctx = document.getElementById('rpnChart').getContext('2d');
  
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(item => item.mes),
      datasets: [{
        label: 'RPN Médio',
        data: data.map(item => Math.round(item.rpn_medio)),
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

// Renderizar gráfico top RPN
function renderTopRpnChart(data) {
  const ctx = document.getElementById('topRpnChart').getContext('2d');
  
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.map(item => item.modo_falha.substring(0, 30) + '...'),
      datasets: [{
        label: 'RPN',
        data: data.map(item => item.rpn),
        backgroundColor: data.map(item => {
          if (item.rpn >= 200) return '#EF4444';
          if (item.rpn >= 100) return '#F97316';
          if (item.rpn >= 40) return '#F59E0B';
          return '#10B981';
        })
      }]
    },
    options: {
      responsive: true,
      indexAxis: 'y',
      scales: {
        x: {
          beginAtZero: true
        }
      }
    }
  });
}
</script>
