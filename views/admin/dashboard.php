<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">📊 Dashboard - Análise de Dados</h1>
  </div>

  <!-- Filtros -->
  <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
      <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
      </svg>
      🔍 Filtros de Análise
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">🏢 Filial</label>
        <select id="filtroFilial" onchange="updateCharts()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Todas as Filiais</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Inicial</label>
        <input type="date" id="dataInicial" onchange="updateCharts()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Final</label>
        <input type="date" id="dataFinal" onchange="updateCharts()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
    </div>
    <div class="mt-4 flex space-x-3">
      <button onclick="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <span>Aplicar Filtros</span>
      </button>
      <button onclick="clearFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <span>Limpar</span>
      </button>
    </div>
  </div>


  <!-- Gráficos dos Retornados -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Gráfico de Barras - Retornados por Mês -->
    <div class="bg-white rounded-lg shadow-lg border-l-4 border-green-500">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          📊 Retornados por Mês
        </h3>
        <button onclick="expandirGraficoRetornados()" class="p-2 rounded-lg hover:bg-green-50 transition-all duration-200 group" title="Expandir gráfico">
          <svg class="w-5 h-5 text-green-600 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
          </svg>
        </button>
      </div>
      <div class="p-6">
        <canvas id="retornadosMesChart" width="400" height="200"></canvas>
      </div>
    </div>

    <!-- Gráfico de Pizza - Retornados por Destino -->
    <div class="bg-white rounded-lg shadow-lg border-l-4 border-orange-500">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
          </svg>
          🥧 Destino dos Retornados
        </h3>
      </div>
      <div class="p-6">
        <canvas id="retornadosDestinoChart" width="400" height="200"></canvas>
      </div>
    </div>
  </div>

  <!-- Gráfico de Toners Recuperados -->
  <div class="bg-white rounded-lg shadow-lg border-l-4 border-purple-500">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900 flex items-center">
        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        💰 Valor Recuperado em Toners (R$)
      </h3>
    </div>
    <div class="p-6">
      <canvas id="tonersRecuperadosChart" width="800" height="300"></canvas>
    </div>
  </div>

</section>

<!-- Modal de Expansão do Gráfico - Retornados por Mês -->
<div id="modalExpandidoRetornados" class="hidden fixed inset-0 bg-black bg-opacity-95 backdrop-blur-sm flex items-center justify-center p-8 transition-all duration-500 ease-out" style="z-index: 99999;">
  <div class="relative w-full max-w-7xl bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl border border-gray-700 p-8 transition-all duration-500 ease-out transform scale-95 opacity-0" id="modalContentRetornados">
    <!-- Botão Fechar -->
    <button onclick="fecharGraficoExpandido()" class="absolute top-6 right-6 p-3 rounded-full bg-red-500/20 hover:bg-red-500/40 transition-all duration-300 group z-10">
      <svg class="w-6 h-6 text-red-400 group-hover:text-red-300 group-hover:rotate-90 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Título -->
    <div class="mb-6 text-center">
      <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-500 flex items-center justify-center gap-3">
        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        📊 Retornados por Mês - Visão Expandida
      </h2>
      <p class="text-gray-400 mt-2">Análise detalhada dos retornados ao longo do ano</p>
    </div>
    
    <!-- Canvas Expandido -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-8 border border-gray-700/50 shadow-inner">
      <canvas id="retornadosMesChartExpandido" class="w-full" style="max-height: 70vh;"></canvas>
    </div>
    
    <!-- Dica -->
    <div class="mt-6 text-center">
      <p class="text-gray-500 text-sm flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Pressione <kbd class="px-2 py-1 bg-gray-700 rounded text-xs mx-1">ESC</kbd> ou clique no botão ✕ para fechar
      </p>
    </div>
  </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal-overlay">
  <div class="modal-container w-full max-w-md">
    <div class="modal-header">
      <h3 class="modal-title">Criar Novo Usuário</h3>
      <button class="modal-close" data-modal-close>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="modal-body">
      <form id="createUserForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
        <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
        <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
        <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Setor</label>
          <input type="text" name="setor" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filial</label>
          <input type="text" name="filial" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Função</label>
        <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="user">Usuário</option>
          <option value="admin">Administrador</option>
        </select>
      </div>
      </form>
    </div>

    <div class="modal-footer">
      <button onclick="closeCreateUserModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
        Cancelar
      </button>
      <button onclick="submitCreateUser()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
        Criar Usuário
      </button>
    </div>
  </div>
</div>


<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Variáveis globais para os gráficos
let retornadosMesChart, retornadosDestinoChart, tonersRecuperadosChart, retornadosMesChartExpandido;
let dashboardData = null;

// Dados iniciais vazios (serão carregados da API)
let dadosRetornadosMes = {
  labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
  datasets: [{
    label: 'Quantidade de Retornados',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    backgroundColor: 'rgba(34, 197, 94, 0.8)',
    borderColor: 'rgba(34, 197, 94, 1)',
    borderWidth: 2,
    borderRadius: 8,
    borderSkipped: false,
  }]
};

let dadosRetornadosDestino = {
  labels: ['Carregando...'],
  datasets: [{
    data: [0],
    backgroundColor: ['rgba(156, 163, 175, 0.8)'],
    borderColor: ['rgba(156, 163, 175, 1)'],
    borderWidth: 2
  }]
};

let dadosTonersRecuperados = {
  labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
  datasets: [{
    label: 'Valor Recuperado (R$)',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    backgroundColor: 'rgba(168, 85, 247, 0.8)',
    borderColor: 'rgba(168, 85, 247, 1)',
    borderWidth: 2,
    borderRadius: 8,
    borderSkipped: false,
  }]
};

// Carregar dados da API
async function loadDashboardData() {
  try {
    const filial = document.getElementById('filtroFilial').value;
    const dataInicial = document.getElementById('dataInicial').value;
    const dataFinal = document.getElementById('dataFinal').value;
    
    const params = new URLSearchParams();
    if (filial) params.append('filial', filial);
    if (dataInicial) params.append('data_inicial', dataInicial);
    if (dataFinal) params.append('data_final', dataFinal);
    
    const response = await fetch(`/admin/dashboard/data?${params.toString()}`);
    const result = await response.json();
    
    if (result.success) {
      dashboardData = result.data;
      updateChartsWithData();
      populateFilialOptions(result.data.filiais);
    } else {
      console.error('Erro ao carregar dados:', result.message);
    }
  } catch (error) {
    console.error('Erro na requisição:', error);
  }
}

// Atualizar gráficos com dados da API
function updateChartsWithData() {
  if (!dashboardData) return;
  
  // Atualizar dados do gráfico de retornados por mês
  dadosRetornadosMes.datasets[0].data = dashboardData.retornados_mes.data;
  
  // Atualizar dados do gráfico de destino
  dadosRetornadosDestino.labels = dashboardData.retornados_destino.labels;
  dadosRetornadosDestino.datasets[0].data = dashboardData.retornados_destino.data;
  
  // Cores dinâmicas para o gráfico de destino
  const colors = [
    'rgba(239, 68, 68, 0.8)',   // Vermelho
    'rgba(34, 197, 94, 0.8)',   // Verde
    'rgba(59, 130, 246, 0.8)',  // Azul
    'rgba(168, 85, 247, 0.8)',  // Roxo
    'rgba(245, 158, 11, 0.8)',  // Amarelo
    'rgba(236, 72, 153, 0.8)',  // Rosa
    'rgba(14, 165, 233, 0.8)',  // Azul claro
    'rgba(34, 197, 94, 0.8)'    // Verde claro
  ];
  
  dadosRetornadosDestino.datasets[0].backgroundColor = colors.slice(0, dashboardData.retornados_destino.labels.length);
  dadosRetornadosDestino.datasets[0].borderColor = colors.slice(0, dashboardData.retornados_destino.labels.length).map(color => color.replace('0.8', '1'));
  
  // Atualizar dados do gráfico de toners recuperados
  dadosTonersRecuperados.datasets[0].data = dashboardData.toners_recuperados.data;
  
  // Atualizar cores das barras baseado no percentual
  if (dashboardData.toners_recuperados.cores) {
    const coresMap = {
      'green': 'rgba(34, 197, 94, 0.8)',
      'red': 'rgba(239, 68, 68, 0.8)',
      'gray': 'rgba(168, 85, 247, 0.8)'
    };
    dadosTonersRecuperados.datasets[0].backgroundColor = dashboardData.toners_recuperados.cores.map(cor => coresMap[cor] || coresMap['gray']);
    dadosTonersRecuperados.datasets[0].borderColor = dashboardData.toners_recuperados.cores.map(cor => coresMap[cor]?.replace('0.8', '1') || coresMap['gray'].replace('0.8', '1'));
  }
  
  // Atualizar os gráficos se já estiverem criados
  if (retornadosMesChart) {
    retornadosMesChart.update();
  }
  if (retornadosDestinoChart) {
    retornadosDestinoChart.update();
  }
  if (tonersRecuperadosChart) {
    tonersRecuperadosChart.update();
  }
}

// Popular opções de filiais
function populateFilialOptions(filiais) {
  const select = document.getElementById('filtroFilial');
  const currentValue = select.value;
  
  // Limpar opções existentes (exceto "Todas as Filiais")
  while (select.children.length > 1) {
    select.removeChild(select.lastChild);
  }
  
  // Adicionar filiais
  filiais.forEach(filial => {
    const option = document.createElement('option');
    option.value = filial;
    option.textContent = filial;
    select.appendChild(option);
  });
  
  // Restaurar valor selecionado
  select.value = currentValue;
}

// Inicializar gráficos
function initCharts() {
  // Gráfico de Retornados por Mês
  const ctx1 = document.getElementById('retornadosMesChart').getContext('2d');
  retornadosMesChart = new Chart(ctx1, {
    type: 'bar',
    data: dadosRetornadosMes,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: 'white',
          bodyColor: 'white',
          borderColor: 'rgba(255, 255, 255, 0.2)',
          borderWidth: 1,
          cornerRadius: 8,
          callbacks: {
            afterBody: function(context) {
              const currentValue = context[0].parsed.y;
              const previousIndex = context[0].dataIndex - 1;
              if (previousIndex >= 0) {
                const previousValue = dadosRetornadosMes.datasets[0].data[previousIndex];
                const percentage = ((currentValue - previousValue) / previousValue * 100).toFixed(1);
                return `Variação: ${percentage > 0 ? '+' : ''}${percentage}% vs mês anterior`;
              }
              return '';
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.1)',
          },
          ticks: {
            color: '#6B7280',
          }
        },
        x: {
          grid: {
            color: 'rgba(0, 0, 0, 0.1)',
          },
          ticks: {
            color: '#6B7280',
          }
        }
      }
    }
  });

  // Gráfico de Retornados por Destino
  const ctx2 = document.getElementById('retornadosDestinoChart').getContext('2d');
  retornadosDestinoChart = new Chart(ctx2, {
    type: 'doughnut',
    data: dadosRetornadosDestino,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: 'white',
          bodyColor: 'white',
          cornerRadius: 8,
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((context.parsed / total) * 100).toFixed(1);
              return `${context.label}: ${context.parsed} (${percentage}%)`;
            }
          }
        }
      }
    }
  });

  // Gráfico de Toners Recuperados
  const ctx3 = document.getElementById('tonersRecuperadosChart').getContext('2d');
  tonersRecuperadosChart = new Chart(ctx3, {
    type: 'bar',
    data: dadosTonersRecuperados,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: 'white',
          bodyColor: 'white',
          borderColor: 'rgba(255, 255, 255, 0.2)',
          borderWidth: 1,
          cornerRadius: 8,
          callbacks: {
            label: function(context) {
              return `Valor: R$ ${context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            },
            afterBody: function(context) {
              const index = context[0].dataIndex;
              const quantidade = dashboardData?.toners_recuperados?.quantidades?.[index] || 0;
              const percentual = dashboardData?.toners_recuperados?.percentuais?.[index] || 0;
              const cor = dashboardData?.toners_recuperados?.cores?.[index] || 'gray';
              
              let lines = [];
              lines.push(`Qtd enviadas para o estoque: ${quantidade} toners`);
              
              if (index > 0 && percentual !== 0) {
                const sinal = percentual > 0 ? '+' : '';
                const emoji = percentual > 0 ? '📈' : '📉';
                lines.push(`${emoji} Variação: ${sinal}${percentual.toFixed(1)}% vs mês anterior`);
              }
              
              return lines;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.1)',
          },
          ticks: {
            color: '#6B7280',
            callback: function(value) {
              return 'R$ ' + value.toLocaleString('pt-BR');
            }
          }
        },
        x: {
          grid: {
            color: 'rgba(0, 0, 0, 0.1)',
          },
          ticks: {
            color: '#6B7280',
          }
        }
      }
    }
  });
}



// Funções de filtro
function updateCharts() {
  loadDashboardData();
}

function applyFilters() {
  loadDashboardData();
}

function clearFilters() {
  document.getElementById('filtroFilial').value = '';
  document.getElementById('dataInicial').value = '';
  document.getElementById('dataFinal').value = '';
  loadDashboardData();
}

// Funções do modal de usuário
function openCreateUserModal() {
  openModal('createUserModal');
}

function closeCreateUserModal() {
  closeModal('createUserModal');
  document.getElementById('createUserForm').reset();
}

function submitCreateUser() {
  const form = document.getElementById('createUserForm');
  const formData = new FormData(form);
  
  fetch('/admin/users/create', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      closeCreateUserModal();
      location.reload();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

// Função para expandir o gráfico de Retornados por Mês
function expandirGraficoRetornados() {
  const modal = document.getElementById('modalExpandidoRetornados');
  const modalContent = document.getElementById('modalContentRetornados');
  
  // Mostrar modal
  modal.classList.remove('hidden');
  
  // Animação de entrada suave
  setTimeout(() => {
    modalContent.style.transform = 'scale(1)';
    modalContent.style.opacity = '1';
  }, 50);
  
  // Criar gráfico expandido se não existir
  if (!retornadosMesChartExpandido) {
    const ctx = document.getElementById('retornadosMesChartExpandido').getContext('2d');
    retornadosMesChartExpandido = new Chart(ctx, {
      type: 'bar',
      data: JSON.parse(JSON.stringify(dadosRetornadosMes)), // Clone dos dados
      options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2.5,
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              color: '#d1d5db',
              font: {
                size: 14,
                weight: 'bold'
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#fff',
            bodyColor: '#d1d5db',
            borderColor: 'rgba(255, 255, 255, 0.3)',
            borderWidth: 2,
            cornerRadius: 12,
            padding: 16,
            titleFont: {
              size: 16,
              weight: 'bold'
            },
            bodyFont: {
              size: 14
            },
            callbacks: {
              afterBody: function(context) {
                const currentValue = context[0].parsed.y;
                const previousIndex = context[0].dataIndex - 1;
                if (previousIndex >= 0) {
                  const previousValue = dadosRetornadosMes.datasets[0].data[previousIndex];
                  const percentage = ((currentValue - previousValue) / previousValue * 100).toFixed(1);
                  return `Variação: ${percentage > 0 ? '+' : ''}${percentage}% vs mês anterior`;
                }
                return '';
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(255, 255, 255, 0.1)',
            },
            ticks: {
              color: '#9ca3af',
              font: {
                size: 13
              }
            }
          },
          x: {
            grid: {
              color: 'rgba(255, 255, 255, 0.05)',
            },
            ticks: {
              color: '#9ca3af',
              font: {
                size: 13
              }
            }
          }
        }
      }
    });
  } else {
    // Atualizar dados do gráfico expandido
    retornadosMesChartExpandido.data = JSON.parse(JSON.stringify(dadosRetornadosMes));
    retornadosMesChartExpandido.update();
  }
  
  // Desabilitar scroll do body
  document.body.style.overflow = 'hidden';
}

// Função para fechar o gráfico expandido
function fecharGraficoExpandido() {
  const modal = document.getElementById('modalExpandidoRetornados');
  const modalContent = document.getElementById('modalContentRetornados');
  
  // Animação de saída suave
  modalContent.style.transform = 'scale(0.95)';
  modalContent.style.opacity = '0';
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
  
  // Reabilitar scroll do body
  document.body.style.overflow = 'auto';
}

// Atalho de teclado ESC para fechar
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const modal = document.getElementById('modalExpandidoRetornados');
    if (!modal.classList.contains('hidden')) {
      fecharGraficoExpandido();
    }
  }
});

// Fechar ao clicar no fundo escuro
document.getElementById('modalExpandidoRetornados').addEventListener('click', function(e) {
  if (e.target === this) {
    fecharGraficoExpandido();
  }
});

// Inicializar dashboard quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
  console.log('🚀 Dashboard carregado, iniciando...');
  
  // Definir datas padrão
  const hoje = new Date();
  const primeiroDiaAno = new Date(hoje.getFullYear(), 0, 1); // 01 de janeiro do ano atual
  
  document.getElementById('dataInicial').value = primeiroDiaAno.toISOString().split('T')[0];
  document.getElementById('dataFinal').value = hoje.toISOString().split('T')[0];
  
  // Inicializar gráficos primeiro
  initCharts();
  
  // Carregar dados após inicializar gráficos
  setTimeout(() => {
    loadDashboardData();
  }, 1000);
});
</script>
