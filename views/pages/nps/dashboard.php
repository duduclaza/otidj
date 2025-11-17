<section class="space-y-6">
  <!-- Cabeçalho -->
  <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">📊 Dashboard de Formulários</h1>
      <p class="text-sm text-gray-600 mt-1">Visão geral e análise de todas as respostas</p>
    </div>
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
      <!-- Filtro por Formulário -->
      <?php if (count($formularios) > 0): ?>
      <div class="flex items-center space-x-2">
        <label for="filtroFormulario" class="text-sm font-medium text-gray-700 whitespace-nowrap">📋 Formulário:</label>
        <select id="filtroFormulario" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
          <option value="todos">Todos os Formulários</option>
          <?php foreach ($formularios as $form): ?>
            <option value="<?= htmlspecialchars($form['id']) ?>"><?= htmlspecialchars($form['titulo']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      
      <!-- Botões de Ação -->
      <?php if ($stats['total_respostas'] > 0): ?>
      <a href="/nps/exportar-csv" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <span>Exportar CSV</span>
      </a>
      <?php endif; ?>
      
      <!-- Botão Limpar Respostas Órfãs (só para admin) -->
      <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin'])): ?>
      <button onclick="limparRespostasOrfas()" id="btnLimparOrfas" class="hidden bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        <span>Limpar Órfãs (<span id="totalOrfas">0</span>)</span>
      </button>
      <?php endif; ?>
      <a href="/nps" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <span>Formulários</span>
      </a>
    </div>
  </div>

  <!-- Cards de Estatísticas -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Card Score Geral -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium opacity-90">Pontuação Geral</h3>
        <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
      </div>
      <p class="text-4xl font-bold"><?= $stats['nps_medio'] ?></p>
      <p class="text-xs opacity-80 mt-1">
        <?php if ($stats['nps_medio'] >= 75): ?>
          Excelente! 🎉
        <?php elseif ($stats['nps_medio'] >= 50): ?>
          Muito Bom! 👍
        <?php elseif ($stats['nps_medio'] >= 0): ?>
          Bom 😊
        <?php else: ?>
          Precisa Melhorar 📈
        <?php endif; ?>
      </p>
    </div>

    <!-- Card Total Respostas -->
    <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium text-gray-600">Total de Respostas</h3>
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
      </div>
      <p class="text-4xl font-bold text-gray-900"><?= $stats['total_respostas'] ?></p>
      <p class="text-xs text-gray-500 mt-1">Respostas coletadas</p>
    </div>

    <!-- Card Formulários -->
    <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium text-gray-600">Formulários</h3>
        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
      </div>
      <p class="text-4xl font-bold text-gray-900"><?= $stats['total_formularios'] ?></p>
      <p class="text-xs text-gray-500 mt-1"><?= $stats['formularios_ativos'] ?> ativos</p>
    </div>

    <!-- Card Promotores -->
    <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium text-gray-600">Promotores</h3>
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
        </svg>
      </div>
      <p class="text-4xl font-bold text-gray-900"><?= $stats['promotores'] ?></p>
      <p class="text-xs text-gray-500 mt-1">Notas 4-5</p>
    </div>
  </div>

  <!-- Gráficos -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Gráfico de Pizza: Distribuição NPS -->
    <div class="bg-white rounded-lg shadow-lg p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Distribuição NPS</h3>
      <div class="relative" style="height: 300px;">
        <canvas id="chartDistribuicao"></canvas>
      </div>
      <div class="grid grid-cols-3 gap-4 mt-4">
        <div class="text-center">
          <p class="text-2xl font-bold text-green-600"><?= $stats['promotores'] ?></p>
          <p class="text-xs text-gray-600">Promotores (4-5)</p>
        </div>
        <div class="text-center">
          <p class="text-2xl font-bold text-yellow-600"><?= $stats['neutros'] ?></p>
          <p class="text-xs text-gray-600">Neutros (3)</p>
        </div>
        <div class="text-center">
          <p class="text-2xl font-bold text-red-600"><?= $stats['detratores'] ?></p>
          <p class="text-xs text-gray-600">Detratores (0-2)</p>
        </div>
      </div>
    </div>

    <!-- Gráfico de Barras: Distribuição de Notas -->
    <div class="bg-white rounded-lg shadow-lg p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Distribuição de Notas</h3>
      <div class="relative" style="height: 300px;">
        <canvas id="chartNotas"></canvas>
      </div>
    </div>
  </div>

  <!-- Gráfico de Linha: Respostas ao Longo do Tempo -->
  <div class="bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">📅 Respostas nos Últimos 30 Dias</h3>
    <div class="relative" style="height: 300px;">
      <canvas id="chartTempo"></canvas>
    </div>
  </div>

  <?php if ($stats['total_respostas'] === 0): ?>
    <!-- Mensagem de Boas-Vindas -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
      <svg class="w-16 h-16 text-blue-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
      </svg>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Bem-vindo ao Dashboard NPS! 🎉</h3>
      <p class="text-gray-600 mb-4">Você ainda não tem respostas coletadas. Crie seu primeiro formulário e compartilhe o link para começar a receber feedback!</p>
      <a href="/nps" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
        Criar Formulário
      </a>
    </div>
  <?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dados do PHP
const stats = <?= json_encode($stats) ?>;

// Gráfico de Pizza: Distribuição NPS
const ctxDistribuicao = document.getElementById('chartDistribuicao').getContext('2d');
let chartDistribuicao = new Chart(ctxDistribuicao, {
  type: 'doughnut',
  data: {
    labels: ['Promotores (4-5)', 'Neutros (3)', 'Detratores (0-2)'],
    datasets: [{
      data: [stats.promotores, stats.neutros, stats.detratores],
      backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
      borderWidth: 0
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          padding: 15,
          font: { size: 12 }
        }
      }
    }
  }
});

// Gráfico de Barras: Distribuição de Notas
const ctxNotas = document.getElementById('chartNotas').getContext('2d');
let chartNotas = new Chart(ctxNotas, {
  type: 'bar',
  data: {
    labels: ['0', '1', '2', '3', '4', '5'],
    datasets: [{
      label: 'Quantidade',
      data: stats.distribuicao_notas,
      backgroundColor: function(context) {
        const value = context.dataIndex;
        if (value >= 4) return '#10B981'; // Verde (Promotores 4-5)
        if (value == 3) return '#F59E0B'; // Amarelo (Neutros 3)
        return '#EF4444'; // Vermelho (Detratores 0-2)
      },
      borderRadius: 4
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 }
      }
    }
  }
});

// Gráfico de Linha: Respostas ao Longo do Tempo
const ctxTempo = document.getElementById('chartTempo').getContext('2d');
let chartTempo = new Chart(ctxTempo, {
  type: 'line',
  data: {
    labels: stats.respostas_por_dia.map(d => d.data),
    datasets: [{
      label: 'Respostas',
      data: stats.respostas_por_dia.map(d => d.total),
      borderColor: '#3B82F6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      fill: true,
      tension: 0.4,
      pointRadius: 3,
      pointHoverRadius: 6
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 }
      }
    }
  }
});

// Função para atualizar os gráficos com novos dados
function atualizarDashboard(formularioId) {
  // Mostrar indicador de carregamento (opcional)
  const url = '/nps/dashboard/data' + (formularioId !== 'todos' ? '?formulario_id=' + encodeURIComponent(formularioId) : '');
  
  fetch(url)
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        const newStats = result.stats;
        
        // Atualizar cards de estatísticas
        document.querySelector('.bg-gradient-to-br.from-blue-500 p.text-4xl').textContent = newStats.nps_medio;
        document.querySelectorAll('.bg-white .text-4xl')[0].textContent = newStats.total_respostas;
        document.querySelectorAll('.bg-white .text-4xl')[1].textContent = newStats.total_formularios;
        document.querySelectorAll('.bg-white .text-4xl')[2].textContent = newStats.promotores;
        
        // Atualizar texto dos formulários ativos
        document.querySelectorAll('.text-xs.text-gray-500')[1].textContent = newStats.formularios_ativos + ' ativos';
        
        // Atualizar resumo abaixo do gráfico de pizza
        document.querySelectorAll('.grid.grid-cols-3 .text-2xl')[0].textContent = newStats.promotores;
        document.querySelectorAll('.grid.grid-cols-3 .text-2xl')[1].textContent = newStats.neutros;
        document.querySelectorAll('.grid.grid-cols-3 .text-2xl')[2].textContent = newStats.detratores;
        
        // Atualizar gráfico de pizza
        chartDistribuicao.data.datasets[0].data = [newStats.promotores, newStats.neutros, newStats.detratores];
        chartDistribuicao.update();
        
        // Atualizar gráfico de barras (distribuição de notas)
        chartNotas.data.datasets[0].data = newStats.distribuicao_notas;
        chartNotas.update();
        
        // Atualizar gráfico de linha (respostas ao longo do tempo)
        chartTempo.data.labels = newStats.respostas_por_dia.map(d => d.data);
        chartTempo.data.datasets[0].data = newStats.respostas_por_dia.map(d => d.total);
        chartTempo.update();
        
        // Atualizar mensagem de NPS
        const npsTexto = document.querySelector('.bg-gradient-to-br p.text-xs');
        if (newStats.nps_medio >= 75) {
          npsTexto.textContent = 'Excelente! 🎉';
        } else if (newStats.nps_medio >= 50) {
          npsTexto.textContent = 'Muito Bom! 👍';
        } else if (newStats.nps_medio >= 0) {
          npsTexto.textContent = 'Bom 😊';
        } else {
          npsTexto.textContent = 'Precisa Melhorar 📈';
        }
      } else {
        console.error('Erro ao carregar dados:', result.message);
      }
    })
    .catch(error => {
      console.error('Erro na requisição:', error);
    });
}

// Event listener para o filtro de formulário
const filtroFormulario = document.getElementById('filtroFormulario');
if (filtroFormulario) {
  filtroFormulario.addEventListener('change', function() {
    atualizarDashboard(this.value);
  });
}

// Verificar respostas órfãs ao carregar página
function verificarRespostasOrfas() {
  fetch('/nps/contar-orfas', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    if (data.success && data.total_orfas > 0) {
      const btnLimpar = document.getElementById('btnLimparOrfas');
      const spanTotal = document.getElementById('totalOrfas');
      if (btnLimpar && spanTotal) {
        spanTotal.textContent = data.total_orfas;
        btnLimpar.classList.remove('hidden');
      }
    }
  })
  .catch(error => console.log('Erro ao verificar órfãs:', error));
}

// Limpar respostas órfãs
function limparRespostasOrfas() {
  const totalOrfas = document.getElementById('totalOrfas').textContent;
  
  if (!confirm(`Tem certeza que deseja remover ${totalOrfas} resposta(s) órfã(s)?\n\nEsta ação não pode ser desfeita!`)) {
    return;
  }
  
  const btn = document.getElementById('btnLimparOrfas');
  const spanOriginal = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><span>Limpando...</span>';
  
  fetch('/nps/limpar-orfas', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      alert(`✅ ${data.message}`);
      btn.classList.add('hidden');
      // Recarregar dashboard para atualizar estatísticas
      location.reload();
    } else {
      alert('❌ Erro: ' + data.message);
      btn.disabled = false;
      btn.innerHTML = spanOriginal;
    }
  })
  .catch(error => {
    alert('❌ Erro ao limpar respostas órfãs');
    btn.disabled = false;
    btn.innerHTML = spanOriginal;
    console.error(error);
  });
}

// Verificar órfãs ao carregar
document.addEventListener('DOMContentLoaded', verificarRespostasOrfas);
</script>
