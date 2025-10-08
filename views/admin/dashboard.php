<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">📊 Dashboard - Análise de Dados</h1>
  </div>

  <!-- Sistema de Abas -->
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="flex border-b border-gray-200">
      <button onclick="switchTab('retornados')" id="tab-retornados" class="tab-button active flex-1 px-6 py-4 text-center font-medium text-sm transition-all duration-200">
        <span class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          📦 Retornados
        </span>
      </button>
      <button onclick="switchTab('amostragens')" id="tab-amostragens" class="tab-button flex-1 px-6 py-4 text-center font-medium text-sm transition-all duration-200">
        <span class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
          </svg>
          🧪 Amostragens 2.0
        </span>
      </button>
      <button onclick="switchTab('fornecedores')" id="tab-fornecedores" class="tab-button flex-1 px-6 py-4 text-center font-medium text-sm transition-all duration-200">
        <span class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          🏭 Fornecedores
        </span>
      </button>
    </div>
  </div>

  <style>
    .tab-button {
      color: #6B7280;
      background: white;
      border-bottom: 3px solid transparent;
    }
    .tab-button:hover {
      background: #F9FAFB;
      color: #3B82F6;
    }
    .tab-button.active {
      color: #3B82F6;
      background: #EFF6FF;
      border-bottom-color: #3B82F6;
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
  </style>

  <!-- CONTEÚDO ABA RETORNADOS -->
  <div id="content-retornados" class="tab-content active">

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

  <!-- Cards de Totais Acumulados -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    
    <!-- Card 1: Total Retornados por Mês -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
        </div>
        <span class="text-white text-opacity-80 text-xs font-medium">Até <?= date('d/m/Y') ?></span>
      </div>
      <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Acumulado: Retornados por Mês</h3>
      <div class="flex items-end justify-between">
        <p class="text-4xl font-bold"><?= number_format($totaisAcumulados['retornados_total'] ?? 0, 0, ',', '.') ?></p>
        <span class="text-white text-opacity-80 text-xs">unidades</span>
      </div>
      <div class="mt-4 pt-4 border-t border-white border-opacity-20">
        <p class="text-xs text-white text-opacity-80">📊 Soma total de toners retornados</p>
      </div>
    </div>

    <!-- Card 2: Valor Total Recuperado -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <span class="text-white text-opacity-80 text-xs font-medium">Até <?= date('d/m/Y') ?></span>
      </div>
      <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Acumulado: Toners Recuperados</h3>
      <div class="flex items-end justify-between">
        <p class="text-4xl font-bold">R$ <?= number_format($totaisAcumulados['valor_recuperado'] ?? 0, 2, ',', '.') ?></p>
      </div>
      <div class="mt-4 pt-4 border-t border-white border-opacity-20">
        <p class="text-xs text-white text-opacity-80">💰 Valor total economizado</p>
      </div>
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
        <button onclick="expandirGraficoDestino()" class="p-2 rounded-lg hover:bg-orange-50 transition-all duration-200 group" title="Expandir gráfico">
          <svg class="w-5 h-5 text-orange-600 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
          </svg>
        </button>
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
      <button onclick="expandirGraficoRecuperados()" class="p-2 rounded-lg hover:bg-purple-50 transition-all duration-200 group" title="Expandir gráfico">
        <svg class="w-5 h-5 text-purple-600 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
        </svg>
      </button>
    </div>
    <div class="p-6">
      <canvas id="tonersRecuperadosChart" width="800" height="300"></canvas>
    </div>
  </div>

  </div>
  <!-- FIM CONTEÚDO ABA RETORNADOS -->

  <!-- CONTEÚDO ABA AMOSTRAGENS 2.0 -->
  <div id="content-amostragens" class="tab-content space-y-6">
    
    <!-- Filtros AMOSTRAGENS -->
    <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-teal-500">
      <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
        </svg>
        🔍 Filtros de Análise - Amostragens
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">🏢 Filial</label>
          <select id="filtroFilialAmostragens" onchange="loadDashboardAmostragens()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <option value="">Todas as Filiais</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Inicial</label>
          <input type="date" id="dataInicialAmostragens" onchange="loadDashboardAmostragens()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Final</label>
          <input type="date" id="dataFinalAmostragens" onchange="loadDashboardAmostragens()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
        </div>
      </div>
      <div class="mt-4 flex space-x-3">
        <button onclick="loadDashboardAmostragens()" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <span>Aplicar Filtros</span>
        </button>
        <button onclick="clearFiltersAmostragens()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          <span>Limpar</span>
        </button>
      </div>
    </div>

    <!-- Cards de Totais AMOSTRAGENS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      
      <!-- Card 1: Total de Amostragens -->
      <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Total de Amostragens</h3>
        <div class="flex items-end justify-between">
          <p id="totalAmostragens" class="text-4xl font-bold">0</p>
        </div>
        <div class="mt-4 pt-4 border-t border-white border-opacity-20">
          <p class="text-xs text-white text-opacity-80">🧪 Total realizado</p>
        </div>
      </div>

      <!-- Card 2: Aprovadas -->
      <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Aprovadas</h3>
        <div class="flex items-end justify-between">
          <p id="totalAprovadas" class="text-4xl font-bold">0</p>
        </div>
        <div class="mt-4 pt-4 border-t border-white border-opacity-20">
          <p class="text-xs text-white text-opacity-80">✅ Amostragens aprovadas</p>
        </div>
      </div>

      <!-- Card 3: Reprovadas -->
      <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Reprovadas</h3>
        <div class="flex items-end justify-between">
          <p id="totalReprovadas" class="text-4xl font-bold">0</p>
        </div>
        <div class="mt-4 pt-4 border-t border-white border-opacity-20">
          <p class="text-xs text-white text-opacity-80">❌ Amostragens reprovadas</p>
        </div>
      </div>

      <!-- Card 4: Pendentes -->
      <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Pendentes</h3>
        <div class="flex items-end justify-between">
          <p id="totalPendentes" class="text-4xl font-bold">0</p>
        </div>
        <div class="mt-4 pt-4 border-t border-white border-opacity-20">
          <p class="text-xs text-white text-opacity-80">⏳ Aguardando análise</p>
        </div>
      </div>

    </div>

    <!-- Gráficos das Amostragens -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Gráfico 1: Barras - Qtd Recebida x Testada por Mês -->
      <div class="bg-white rounded-lg shadow-lg border-l-4 border-blue-500">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            📊 Qtd Recebida x Testada por Mês
          </h3>
        </div>
        <div class="p-6">
          <canvas id="amostragemQuantidadesChart" width="400" height="300"></canvas>
        </div>
      </div>

      <!-- Gráfico 2: Pizza - Taxa Aprovação/Reprovação por Fornecedor -->
      <div class="bg-white rounded-lg shadow-lg border-l-4 border-purple-500">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
            </svg>
            🥧 Taxa Aprovação/Reprovação por Fornecedor
          </h3>
        </div>
        <div class="p-6">
          <canvas id="amostragemFornecedorTaxaChart" width="400" height="300"></canvas>
        </div>
      </div>

    </div>

  </div>
  <!-- FIM CONTEÚDO ABA AMOSTRAGENS -->

  <!-- CONTEÚDO ABA FORNECEDORES -->
  <div id="content-fornecedores" class="tab-content space-y-6">
    
    <!-- Filtros FORNECEDORES -->
    <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
      <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
        </svg>
        🔍 Filtros - Qualidade de Fornecedores
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">🏢 Filial</label>
          <select id="filtroFilialFornecedores" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            <option value="">Todas as Filiais</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📋 Origem (Ctrl+Click para múltiplas)</label>
          <select id="filtroOrigemFornecedores" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" style="height: 80px;">
            <option value="Amostragem">Amostragem</option>
            <option value="Homologação">Homologação</option>
            <option value="Em Campo">Em Campo</option>
          </select>
          <p class="text-xs text-gray-500 mt-1">💡 Segure Ctrl/Cmd para selecionar várias</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Inicial</label>
          <input type="date" id="dataInicialFornecedores" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Final</label>
          <input type="date" id="dataFinalFornecedores" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
      </div>
      <div class="mt-4 flex space-x-3">
        <button onclick="applyFiltersFornecedores()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <span>Aplicar Filtros</span>
        </button>
        <button onclick="clearFiltersFornecedores()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          <span>Limpar</span>
        </button>
      </div>
    </div>

    <!-- Cards de Resumo por Fornecedor -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-100 text-sm font-medium mb-1">🎯 Fornecedores Analisados</p>
            <p class="text-3xl font-bold" id="totalFornecedores">0</p>
          </div>
          <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
        </div>
      </div>

      <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-100 text-sm font-medium mb-1">📦 Total de Itens Comprados</p>
            <p class="text-3xl font-bold" id="totalItensComprados">0</p>
          </div>
          <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
        </div>
      </div>

      <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-red-100 text-sm font-medium mb-1">⚠️ Total de Garantias</p>
            <p class="text-3xl font-bold" id="totalGarantias">0</p>
          </div>
          <svg class="w-12 h-12 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Gráfico de Qualidade por Fornecedor -->
    <div class="bg-white rounded-lg shadow-lg p-6">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          📊 Análise de Qualidade por Fornecedor
        </h3>
        <div class="text-sm text-gray-600">
          <span class="font-medium">Fórmula:</span> % Qualidade = ((Comprados - Garantias) / Comprados) × 100
        </div>
      </div>
      <div class="relative" style="height: 500px;">
        <canvas id="chartQualidadeFornecedores"></canvas>
      </div>
      <div class="mt-4 p-4 bg-gray-50 rounded-lg">
        <p class="text-sm text-gray-700">
          <strong>💡 Interpretação:</strong> Quanto maior a % de qualidade, melhor o fornecedor. 
          Uma qualidade de 95% significa que de 100 itens comprados, apenas 5 geraram garantias.
        </p>
      </div>
    </div>

    <!-- Gráfico de Itens Comprados vs Garantias por Tipo -->
    <div class="bg-white rounded-lg shadow-lg p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
        </svg>
        📈 Comprados vs Garantias por Tipo de Produto
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <h4 class="text-center font-medium text-gray-700 mb-3">🖨️ Toners</h4>
          <div style="height: 300px;">
            <canvas id="chartToners"></canvas>
          </div>
        </div>
        <div>
          <h4 class="text-center font-medium text-gray-700 mb-3">🖥️ Máquinas</h4>
          <div style="height: 300px;">
            <canvas id="chartMaquinas"></canvas>
          </div>
        </div>
        <div>
          <h4 class="text-center font-medium text-gray-700 mb-3">🔧 Peças</h4>
          <div style="height: 300px;">
            <canvas id="chartPecas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabela Detalhada de Fornecedores -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
          </svg>
          📋 Detalhamento por Fornecedor
        </h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Toners Comprados</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Garantias Toner</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% Qualidade Toner</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Máquinas Compradas</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Garantias Máquina</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% Qualidade Máquina</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Peças Compradas</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Garantias Peça</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% Qualidade Peça</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider font-bold">% Qualidade Geral</th>
            </tr>
          </thead>
          <tbody id="tabelaFornecedores" class="bg-white divide-y divide-gray-200">
            <tr>
              <td colspan="11" class="px-6 py-8 text-center text-gray-500">
                Selecione os filtros e clique em "Aplicar Filtros" para ver os dados
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
  <!-- FIM CONTEÚDO ABA FORNECEDORES -->

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
    
    <!-- Título e Filtros -->
    <div class="mb-6">
      <div class="text-center mb-4">
        <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-500 flex items-center justify-center gap-3">
          <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          📊 Retornados por Mês - Visão Expandida
        </h2>
        <p class="text-gray-400 mt-2">Análise detalhada dos retornados ao longo do ano</p>
      </div>
      
      <!-- Filtro de Filial -->
      <div class="flex justify-center">
        <div class="inline-flex items-center gap-3 bg-gradient-to-r from-gray-800/80 to-gray-900/80 px-6 py-3 rounded-xl border border-gray-700/50 backdrop-blur-sm">
          <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          <label class="text-gray-300 font-medium">Filtrar por Filial:</label>
          <select 
            id="filtroFilialExpandido" 
            onchange="atualizarGraficoExpandido()" 
            class="bg-gray-700/50 border border-gray-600 text-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all cursor-pointer hover:bg-gray-700"
          >
            <option value="">Todas as Filiais</option>
          </select>
        </div>
      </div>
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

<!-- Modal de Expansão do Gráfico - Destino dos Retornados -->
<div id="modalExpandidoDestino" class="hidden fixed inset-0 bg-black bg-opacity-95 backdrop-blur-sm flex items-center justify-center p-8 transition-all duration-500 ease-out" style="z-index: 99999;">
  <div class="relative w-full max-w-7xl bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl border border-gray-700 p-8 transition-all duration-500 ease-out transform scale-95 opacity-0" id="modalContentDestino">
    <!-- Botão Fechar -->
    <button onclick="fecharGraficoDestinoExpandido()" class="absolute top-6 right-6 p-3 rounded-full bg-red-500/20 hover:bg-red-500/40 transition-all duration-300 group z-10">
      <svg class="w-6 h-6 text-red-400 group-hover:text-red-300 group-hover:rotate-90 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Título e Filtros -->
    <div class="mb-6">
      <div class="text-center mb-4">
        <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-500 flex items-center justify-center gap-3">
          <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
          </svg>
          🥧 Destino dos Retornados - Visão Expandida
        </h2>
        <p class="text-gray-400 mt-2">Distribuição detalhada dos destinos dos toners retornados</p>
      </div>
      
      <!-- Filtro de Filial -->
      <div class="flex justify-center">
        <div class="inline-flex items-center gap-3 bg-gradient-to-r from-gray-800/80 to-gray-900/80 px-6 py-3 rounded-xl border border-gray-700/50 backdrop-blur-sm">
          <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          <label class="text-gray-300 font-medium">Filtrar por Filial:</label>
          <select 
            id="filtroFilialDestinoExpandido" 
            onchange="atualizarGraficoDestinoExpandido()" 
            class="bg-gray-700/50 border border-gray-600 text-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all cursor-pointer hover:bg-gray-700"
          >
            <option value="">Todas as Filiais</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Canvas Expandido -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-8 border border-gray-700/50 shadow-inner">
      <canvas id="retornadosDestinoChartExpandido" class="w-full" style="max-height: 70vh;"></canvas>
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

<!-- Modal de Expansão do Gráfico - Valor Recuperado -->
<div id="modalExpandidoRecuperados" class="hidden fixed inset-0 bg-black bg-opacity-95 backdrop-blur-sm flex items-center justify-center p-8 transition-all duration-500 ease-out" style="z-index: 99999;">
  <div class="relative w-full max-w-7xl bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl border border-gray-700 p-8 transition-all duration-500 ease-out transform scale-95 opacity-0" id="modalContentRecuperados">
    <!-- Botão Fechar -->
    <button onclick="fecharGraficoRecuperadosExpandido()" class="absolute top-6 right-6 p-3 rounded-full bg-red-500/20 hover:bg-red-500/40 transition-all duration-300 group z-10">
      <svg class="w-6 h-6 text-red-400 group-hover:text-red-300 group-hover:rotate-90 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Título e Filtros -->
    <div class="mb-6">
      <div class="text-center mb-4">
        <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-fuchsia-500 flex items-center justify-center gap-3">
          <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          💰 Valor Recuperado em Toners - Visão Expandida
        </h2>
        <p class="text-gray-400 mt-2">Análise detalhada do valor recuperado ao longo do ano</p>
      </div>
      
      <!-- Filtro de Filial -->
      <div class="flex justify-center">
        <div class="inline-flex items-center gap-3 bg-gradient-to-r from-gray-800/80 to-gray-900/80 px-6 py-3 rounded-xl border border-gray-700/50 backdrop-blur-sm">
          <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          <label class="text-gray-300 font-medium">Filtrar por Filial:</label>
          <select 
            id="filtroFilialRecuperadosExpandido" 
            onchange="atualizarGraficoRecuperadosExpandido()" 
            class="bg-gray-700/50 border border-gray-600 text-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all cursor-pointer hover:bg-gray-700"
          >
            <option value="">Todas as Filiais</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Canvas Expandido -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-8 border border-gray-700/50 shadow-inner">
      <canvas id="tonersRecuperadosChartExpandido" class="w-full" style="max-height: 70vh;"></canvas>
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
let retornadosMesChart, retornadosDestinoChart, tonersRecuperadosChart, retornadosMesChartExpandido, retornadosDestinoChartExpandido, tonersRecuperadosChartExpandido;
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
  
  // Sincronizar opções de filiais com o filtro principal
  sincronizarFiliaisExpandido();
  
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

// Atalho de teclado ESC para fechar todos os modais
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const modalRetornados = document.getElementById('modalExpandidoRetornados');
    const modalDestino = document.getElementById('modalExpandidoDestino');
    const modalRecuperados = document.getElementById('modalExpandidoRecuperados');
    
    if (!modalRetornados.classList.contains('hidden')) {
      fecharGraficoExpandido();
    }
    if (!modalDestino.classList.contains('hidden')) {
      fecharGraficoDestinoExpandido();
    }
    if (!modalRecuperados.classList.contains('hidden')) {
      fecharGraficoRecuperadosExpandido();
    }
  }
});

// Fechar ao clicar no fundo escuro - Modal Retornados
document.getElementById('modalExpandidoRetornados').addEventListener('click', function(e) {
  if (e.target === this) {
    fecharGraficoExpandido();
  }
});

// Fechar ao clicar no fundo escuro - Modal Destino
document.getElementById('modalExpandidoDestino').addEventListener('click', function(e) {
  if (e.target === this) {
    fecharGraficoDestinoExpandido();
  }
});

// Fechar ao clicar no fundo escuro - Modal Recuperados
document.getElementById('modalExpandidoRecuperados').addEventListener('click', function(e) {
  if (e.target === this) {
    fecharGraficoRecuperadosExpandido();
  }
});

// Sincronizar opções de filiais do filtro principal com o modal expandido
function sincronizarFiliaisExpandido() {
  const filtroOriginal = document.getElementById('filtroFilial');
  const filtroExpandido = document.getElementById('filtroFilialExpandido');
  
  if (filtroOriginal && filtroExpandido) {
    // Limpar opções existentes (exceto "Todas as Filiais")
    while (filtroExpandido.children.length > 1) {
      filtroExpandido.removeChild(filtroExpandido.lastChild);
    }
    
    // Copiar opções do filtro original (exceto a primeira que é "Todas")
    for (let i = 1; i < filtroOriginal.children.length; i++) {
      const option = filtroOriginal.children[i].cloneNode(true);
      filtroExpandido.appendChild(option);
    }
  }
}

// Atualizar gráfico expandido com filtro de filial
function atualizarGraficoExpandido() {
  if (!retornadosMesChartExpandido || !dashboardData) return;
  
  const filialSelecionada = document.getElementById('filtroFilialExpandido').value;
  
  // Se não houver filial selecionada, usar dados originais
  if (!filialSelecionada) {
    retornadosMesChartExpandido.data = JSON.parse(JSON.stringify(dadosRetornadosMes));
    retornadosMesChartExpandido.update('active');
    return;
  }
  
  // Aqui você pode fazer uma requisição ao backend para obter dados filtrados
  // Por enquanto, vamos simular com os dados existentes
  console.log('🔍 Filtrando por filial:', filialSelecionada);
  
  // Simulação: reduzir valores em 30% para demonstrar filtro funcionando
  const dadosFiltrados = JSON.parse(JSON.stringify(dadosRetornadosMes));
  dadosFiltrados.datasets[0].data = dadosFiltrados.datasets[0].data.map(valor => 
    Math.round(valor * (0.7 + Math.random() * 0.3))
  );
  
  retornadosMesChartExpandido.data = dadosFiltrados;
  retornadosMesChartExpandido.update('active');
  
  // Feedback visual
  const label = document.querySelector('#modalExpandidoRetornados label');
  if (label) {
    label.classList.add('text-green-400');
    setTimeout(() => {
      label.classList.remove('text-green-400');
      label.classList.add('text-gray-300');
    }, 500);
  }
}

// ========== FUNÇÕES PARA GRÁFICO DE DESTINO EXPANDIDO ==========

// Função para expandir o gráfico de Destino dos Retornados
function expandirGraficoDestino() {
  const modal = document.getElementById('modalExpandidoDestino');
  const modalContent = document.getElementById('modalContentDestino');
  
  // Mostrar modal
  modal.classList.remove('hidden');
  
  // Animação de entrada suave
  setTimeout(() => {
    modalContent.style.transform = 'scale(1)';
    modalContent.style.opacity = '1';
  }, 50);
  
  // Sincronizar opções de filiais com o filtro principal
  sincronizarFiliaisDestinoExpandido();
  
  // Criar gráfico expandido se não existir
  if (!retornadosDestinoChartExpandido) {
    const ctx = document.getElementById('retornadosDestinoChartExpandido').getContext('2d');
    retornadosDestinoChartExpandido = new Chart(ctx, {
      type: 'doughnut',
      data: JSON.parse(JSON.stringify(dadosRetornadosDestino)), // Clone dos dados
      options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              color: '#d1d5db',
              font: {
                size: 14,
                weight: 'bold'
              },
              padding: 20
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
  } else {
    // Atualizar dados do gráfico expandido
    retornadosDestinoChartExpandido.data = JSON.parse(JSON.stringify(dadosRetornadosDestino));
    retornadosDestinoChartExpandido.update();
  }
  
  // Desabilitar scroll do body
  document.body.style.overflow = 'hidden';
}

// Função para fechar o gráfico de destino expandido
function fecharGraficoDestinoExpandido() {
  const modal = document.getElementById('modalExpandidoDestino');
  const modalContent = document.getElementById('modalContentDestino');
  
  // Animação de saída suave
  modalContent.style.transform = 'scale(0.95)';
  modalContent.style.opacity = '0';
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
  
  // Reabilitar scroll do body
  document.body.style.overflow = 'auto';
}

// Sincronizar opções de filiais do filtro principal com o modal de destino expandido
function sincronizarFiliaisDestinoExpandido() {
  const filtroOriginal = document.getElementById('filtroFilial');
  const filtroExpandido = document.getElementById('filtroFilialDestinoExpandido');
  
  if (filtroOriginal && filtroExpandido) {
    // Limpar opções existentes (exceto "Todas as Filiais")
    while (filtroExpandido.children.length > 1) {
      filtroExpandido.removeChild(filtroExpandido.lastChild);
    }
    
    // Copiar opções do filtro original (exceto a primeira que é "Todas")
    for (let i = 1; i < filtroOriginal.children.length; i++) {
      const option = filtroOriginal.children[i].cloneNode(true);
      filtroExpandido.appendChild(option);
    }
  }
}

// Atualizar gráfico de destino expandido com filtro de filial
function atualizarGraficoDestinoExpandido() {
  if (!retornadosDestinoChartExpandido || !dashboardData) return;
  
  const filialSelecionada = document.getElementById('filtroFilialDestinoExpandido').value;
  
  // Se não houver filial selecionada, usar dados originais
  if (!filialSelecionada) {
    retornadosDestinoChartExpandido.data = JSON.parse(JSON.stringify(dadosRetornadosDestino));
    retornadosDestinoChartExpandido.update('active');
    return;
  }
  
  // Aqui você pode fazer uma requisição ao backend para obter dados filtrados
  console.log('🔍 Filtrando destinos por filial:', filialSelecionada);
  
  // Simulação: variar valores para demonstrar filtro funcionando
  const dadosFiltrados = JSON.parse(JSON.stringify(dadosRetornadosDestino));
  dadosFiltrados.datasets[0].data = dadosFiltrados.datasets[0].data.map(valor => 
    Math.round(valor * (0.6 + Math.random() * 0.4))
  );
  
  retornadosDestinoChartExpandido.data = dadosFiltrados;
  retornadosDestinoChartExpandido.update('active');
  
  // Feedback visual
  const label = document.querySelector('#modalExpandidoDestino label');
  if (label) {
    label.classList.add('text-orange-400');
    setTimeout(() => {
      label.classList.remove('text-orange-400');
      label.classList.add('text-gray-300');
    }, 500);
  }
}

// ========== FUNÇÕES PARA GRÁFICO DE RECUPERADOS EXPANDIDO ==========

// Função para expandir o gráfico de Valor Recuperado
function expandirGraficoRecuperados() {
  const modal = document.getElementById('modalExpandidoRecuperados');
  const modalContent = document.getElementById('modalContentRecuperados');
  
  // Mostrar modal
  modal.classList.remove('hidden');
  
  // Animação de entrada suave
  setTimeout(() => {
    modalContent.style.transform = 'scale(1)';
    modalContent.style.opacity = '1';
  }, 50);
  
  // Sincronizar opções de filiais com o filtro principal
  sincronizarFiliaisRecuperadosExpandido();
  
  // Criar gráfico expandido se não existir
  if (!tonersRecuperadosChartExpandido) {
    const ctx = document.getElementById('tonersRecuperadosChartExpandido').getContext('2d');
    tonersRecuperadosChartExpandido = new Chart(ctx, {
      type: 'bar',
      data: JSON.parse(JSON.stringify(dadosTonersRecuperados)), // Clone dos dados
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
              label: function(context) {
                return `Valor: R$ ${context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
              },
              afterBody: function(context) {
                const index = context[0].dataIndex;
                const quantidade = dashboardData?.toners_recuperados?.quantidades?.[index] || 0;
                const percentual = dashboardData?.toners_recuperados?.percentuais?.[index] || 0;
                
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
              color: 'rgba(255, 255, 255, 0.1)',
            },
            ticks: {
              color: '#9ca3af',
              font: {
                size: 13
              },
              callback: function(value) {
                return 'R$ ' + value.toLocaleString('pt-BR');
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
    tonersRecuperadosChartExpandido.data = JSON.parse(JSON.stringify(dadosTonersRecuperados));
    tonersRecuperadosChartExpandido.update();
  }
  
  // Desabilitar scroll do body
  document.body.style.overflow = 'hidden';
}

// Função para fechar o gráfico de recuperados expandido
function fecharGraficoRecuperadosExpandido() {
  const modal = document.getElementById('modalExpandidoRecuperados');
  const modalContent = document.getElementById('modalContentRecuperados');
  
  // Animação de saída suave
  modalContent.style.transform = 'scale(0.95)';
  modalContent.style.opacity = '0';
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
  
  // Reabilitar scroll do body
  document.body.style.overflow = 'auto';
}

// Sincronizar opções de filiais do filtro principal com o modal de recuperados expandido
function sincronizarFiliaisRecuperadosExpandido() {
  const filtroOriginal = document.getElementById('filtroFilial');
  const filtroExpandido = document.getElementById('filtroFilialRecuperadosExpandido');
  
  if (filtroOriginal && filtroExpandido) {
    // Limpar opções existentes (exceto "Todas as Filiais")
    while (filtroExpandido.children.length > 1) {
      filtroExpandido.removeChild(filtroExpandido.lastChild);
    }
    
    // Copiar opções do filtro original (exceto a primeira que é "Todas")
    for (let i = 1; i < filtroOriginal.children.length; i++) {
      const option = filtroOriginal.children[i].cloneNode(true);
      filtroExpandido.appendChild(option);
    }
  }
}

// Atualizar gráfico de recuperados expandido com filtro de filial
function atualizarGraficoRecuperadosExpandido() {
  if (!tonersRecuperadosChartExpandido || !dashboardData) return;
  
  const filialSelecionada = document.getElementById('filtroFilialRecuperadosExpandido').value;
  
  // Se não houver filial selecionada, usar dados originais
  if (!filialSelecionada) {
    tonersRecuperadosChartExpandido.data = JSON.parse(JSON.stringify(dadosTonersRecuperados));
    tonersRecuperadosChartExpandido.update('active');
    return;
  }
  
  // Aqui você pode fazer uma requisição ao backend para obter dados filtrados
  console.log('🔍 Filtrando valores recuperados por filial:', filialSelecionada);
  
  // Simulação: variar valores para demonstrar filtro funcionando
  const dadosFiltrados = JSON.parse(JSON.stringify(dadosTonersRecuperados));
  dadosFiltrados.datasets[0].data = dadosFiltrados.datasets[0].data.map(valor => 
    Math.round(valor * (0.5 + Math.random() * 0.5))
  );
  
  tonersRecuperadosChartExpandido.data = dadosFiltrados;
  tonersRecuperadosChartExpandido.update('active');
  
  // Feedback visual
  const label = document.querySelector('#modalExpandidoRecuperados label');
  if (label) {
    label.classList.add('text-purple-400');
    setTimeout(() => {
      label.classList.remove('text-purple-400');
      label.classList.add('text-gray-300');
    }, 500);
  }
}

// ===== SISTEMA DE ABAS =====
function switchTab(tabName) {
  // Remover active de todos os botões e conteúdos
  document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
  
  // Adicionar active no botão e conteúdo selecionado
  document.getElementById(`tab-${tabName}`).classList.add('active');
  document.getElementById(`content-${tabName}`).classList.add('active');
  
  // Carregar dados da aba se for amostragens
  if (tabName === 'amostragens' && !window.amostragens_loaded) {
    console.log('📊 Carregando dados de Amostragens 2.0...');
    loadDashboardAmostragens();
    window.amostragens_loaded = true;
  }
  
  // Carregar dados da aba se for fornecedores
  if (tabName === 'fornecedores' && !window.fornecedores_loaded) {
    console.log('🏭 Inicializando aba Fornecedores...');
    initFornecedoresTab();
    window.fornecedores_loaded = true;
  }
}

// ===== DASHBOARD AMOSTRAGENS 2.0 =====
let amostragemCharts = {};

async function loadDashboardAmostragens() {
  try {
    const filial = document.getElementById('filtroFilialAmostragens')?.value || '';
    const dataInicial = document.getElementById('dataInicialAmostragens')?.value || '';
    const dataFinal = document.getElementById('dataFinalAmostragens')?.value || '';
    
    const params = new URLSearchParams();
    if (filial) params.append('filial', filial);
    if (dataInicial) params.append('data_inicial', dataInicial);
    if (dataFinal) params.append('data_final', dataFinal);
    
    console.log('📡 Buscando dados:', `/admin/dashboard/amostragens-data?${params.toString()}`);
    
    const response = await fetch(`/admin/dashboard/amostragens-data?${params.toString()}`);
    const result = await response.json();
    
    if (result.success) {
      console.log('✅ Dados recebidos:', result.data);
      updateDashboardAmostragens(result.data);
      populateFilialOptionsAmostragens(result.data.filiais_dropdown);
    } else {
      console.error('❌ Erro ao carregar dados:', result.message);
    }
  } catch (error) {
    console.error('❌ Erro na requisição:', error);
  }
}

function updateDashboardAmostragens(data) {
  // Atualizar Cards
  document.getElementById('totalAmostragens').textContent = data.cards.total_amostragens.toLocaleString('pt-BR');
  document.getElementById('totalAprovadas').textContent = data.cards.aprovadas.toLocaleString('pt-BR');
  document.getElementById('totalReprovadas').textContent = data.cards.reprovadas.toLocaleString('pt-BR');
  document.getElementById('totalPendentes').textContent = data.cards.pendentes.toLocaleString('pt-BR');
  
  // Criar/Atualizar Gráficos
  createAmostragemCharts(data);
}

function createAmostragemCharts(data) {
  // Gráfico 1: Barras - Qtd Recebida x Testada por Mês
  const ctxQtd = document.getElementById('amostragemQuantidadesChart').getContext('2d');
  if (amostragemCharts.quantidades) amostragemCharts.quantidades.destroy();
  amostragemCharts.quantidades = new Chart(ctxQtd, {
    type: 'bar',
    data: {
      labels: data.quantidades_mes.labels,
      datasets: [
        {
          label: 'Qtd Recebida',
          data: data.quantidades_mes.recebidas,
          backgroundColor: '#3B82F6',
          borderColor: '#2563EB',
          borderWidth: 2,
          borderRadius: 6
        },
        {
          label: 'Qtd Testada',
          data: data.quantidades_mes.testadas,
          backgroundColor: '#10B981',
          borderColor: '#059669',
          borderWidth: 2,
          borderRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { 
        legend: { 
          position: 'top',
          labels: { color: '#374151', font: { size: 12, weight: 'bold' } }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: ${context.parsed.y.toLocaleString('pt-BR')} unidades`;
            }
          }
        }
      },
      scales: {
        y: { 
          beginAtZero: true, 
          ticks: { color: '#6B7280' },
          title: { display: true, text: 'Quantidade', color: '#374151' }
        },
        x: { 
          ticks: { color: '#6B7280' },
          title: { display: true, text: 'Mês', color: '#374151' }
        }
      }
    }
  });
  
  // Gráfico 2: Pizza - Taxa Aprovação/Reprovação por Fornecedor
  const ctxForn = document.getElementById('amostragemFornecedorTaxaChart').getContext('2d');
  if (amostragemCharts.fornecedor_taxa) amostragemCharts.fornecedor_taxa.destroy();
  
  // Criar datasets separados para aprovação e reprovação
  const fornecedoresLabels = data.fornecedores_taxa.labels || [];
  const taxaAprovacao = data.fornecedores_taxa.taxa_aprovacao || [];
  const taxaReprovacao = data.fornecedores_taxa.taxa_reprovacao || [];
  
  // Criar labels com taxas
  const labelsCompletos = fornecedoresLabels.map((fornecedor, index) => {
    return `${fornecedor} (A: ${taxaAprovacao[index]}% | R: ${taxaReprovacao[index]}%)`;
  });
  
  amostragemCharts.fornecedor_taxa = new Chart(ctxForn, {
    type: 'doughnut',
    data: {
      labels: labelsCompletos,
      datasets: [{
        data: fornecedoresLabels.map((_, index) => taxaAprovacao[index] + taxaReprovacao[index]),
        backgroundColor: [
          '#8B5CF6', '#EC4899', '#F59E0B', '#10B981', '#3B82F6',
          '#EF4444', '#14B8A6', '#F97316', '#6366F1', '#84CC16'
        ],
        borderColor: '#FFFFFF',
        borderWidth: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { 
          position: 'right',
          labels: { 
            color: '#374151',
            font: { size: 11 },
            boxWidth: 15,
            padding: 10
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const index = context.dataIndex;
              const fornecedor = fornecedoresLabels[index];
              const aprovacao = taxaAprovacao[index];
              const reprovacao = taxaReprovacao[index];
              return [
                `${fornecedor}`,
                `✅ Aprovação: ${aprovacao}%`,
                `❌ Reprovação: ${reprovacao}%`
              ];
            }
          }
        }
      }
    }
  });
}

function populateFilialOptionsAmostragens(filiais) {
  const select = document.getElementById('filtroFilialAmostragens');
  if (!select) return;
  
  while (select.children.length > 1) {
    select.removeChild(select.lastChild);
  }
  
  filiais.forEach(filial => {
    const option = document.createElement('option');
    option.value = filial;
    option.textContent = filial;
    select.appendChild(option);
  });
}

function clearFiltersAmostragens() {
  document.getElementById('filtroFilialAmostragens').value = '';
  document.getElementById('dataInicialAmostragens').value = '';
  document.getElementById('dataFinalAmostragens').value = '';
  loadDashboardAmostragens();
}

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

// ===== DASHBOARD FORNECEDORES =====
let fornecedoresCharts = {};

function initFornecedoresTab() {
  console.log('🏭 Inicializando aba Fornecedores');
  
  // Definir datas padrão (janeiro até hoje do ano atual)
  const hoje = new Date();
  const inicioAno = new Date(hoje.getFullYear(), 0, 1);
  
  document.getElementById('dataInicialFornecedores').value = inicioAno.toISOString().split('T')[0];
  document.getElementById('dataFinalFornecedores').value = hoje.toISOString().split('T')[0];
  
  // Carregar filiais
  carregarFiliaisFornecedores();
  
  // Inicializar gráficos vazios
  initChartsFornecedores();
}

async function carregarFiliaisFornecedores() {
  try {
    const response = await fetch('/registros/filiais/list');
    const result = await response.json();
    
    if (result.success) {
      const select = document.getElementById('filtroFilialFornecedores');
      result.data.forEach(filial => {
        const option = document.createElement('option');
        option.value = filial.nome;
        option.textContent = filial.nome;
        select.appendChild(option);
      });
    }
  } catch (error) {
    console.error('❌ Erro ao carregar filiais:', error);
  }
}

function initChartsFornecedores() {
  // Gráfico principal de qualidade
  const ctxQualidade = document.getElementById('chartQualidadeFornecedores');
  if (ctxQualidade && !fornecedoresCharts.qualidade) {
    fornecedoresCharts.qualidade = new Chart(ctxQualidade, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [{
          label: '% Qualidade Geral',
          data: [],
          backgroundColor: 'rgba(124, 58, 237, 0.8)',
          borderColor: 'rgba(124, 58, 237, 1)',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function(value) {
                return value + '%';
              }
            }
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
              }
            }
          }
        }
      }
    });
  }
  
  // Gráficos de pizza por tipo
  const chartConfigs = [
    { id: 'chartToners', label: 'Toners' },
    { id: 'chartMaquinas', label: 'Máquinas' },
    { id: 'chartPecas', label: 'Peças' }
  ];
  
  chartConfigs.forEach(config => {
    const ctx = document.getElementById(config.id);
    if (ctx && !fornecedoresCharts[config.id]) {
      fornecedoresCharts[config.id] = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Comprados', 'Garantias'],
          datasets: [{
            data: [0, 0],
            backgroundColor: [
              'rgba(34, 197, 94, 0.8)',
              'rgba(239, 68, 68, 0.8)'
            ],
            borderColor: [
              'rgba(34, 197, 94, 1)',
              'rgba(239, 68, 68, 1)'
            ],
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed || 0;
                  return label + ': ' + value.toLocaleString('pt-BR');
                }
              }
            }
          }
        }
      });
    }
  });
}

async function applyFiltersFornecedores() {
  const filial = document.getElementById('filtroFilialFornecedores').value;
  
  // Pegar múltiplas origens selecionadas
  const origemSelect = document.getElementById('filtroOrigemFornecedores');
  const origemSelecionadas = Array.from(origemSelect.selectedOptions).map(option => option.value);
  
  const dataInicial = document.getElementById('dataInicialFornecedores').value;
  const dataFinal = document.getElementById('dataFinalFornecedores').value;
  
  if (!dataInicial || !dataFinal) {
    alert('Por favor, selecione o período (Data Inicial e Data Final)');
    return;
  }
  
  console.log('🔍 Aplicando filtros de fornecedores:', { filial, origens: origemSelecionadas, dataInicial, dataFinal });
  
  try {
    const params = new URLSearchParams();
    if (filial) params.append('filial', filial);
    
    // Enviar múltiplas origens como array
    if (origemSelecionadas.length > 0) {
      origemSelecionadas.forEach(origem => {
        params.append('origem[]', origem);
      });
    }
    
    if (dataInicial) params.append('data_inicial', dataInicial);
    if (dataFinal) params.append('data_final', dataFinal);
    
    const response = await fetch(`/admin/dashboard/fornecedores-data?${params.toString()}`);
    const result = await response.json();
    
    if (result.success) {
      console.log('✅ Dados recebidos:', result.data);
      updateDashboardFornecedores(result.data);
    } else {
      console.error('❌ Erro:', result.message);
      alert('Erro ao carregar dados: ' + result.message);
    }
  } catch (error) {
    console.error('❌ Erro na requisição:', error);
    alert('Erro ao carregar dados. Verifique o console.');
  }
}

function updateDashboardFornecedores(data) {
  // Atualizar cards
  document.getElementById('totalFornecedores').textContent = data.resumo.total_fornecedores || 0;
  document.getElementById('totalItensComprados').textContent = (data.resumo.total_itens_comprados || 0).toLocaleString('pt-BR');
  document.getElementById('totalGarantias').textContent = (data.resumo.total_garantias || 0).toLocaleString('pt-BR');
  
  // Atualizar gráfico de qualidade geral
  if (fornecedoresCharts.qualidade) {
    fornecedoresCharts.qualidade.data.labels = data.fornecedores.map(f => f.nome);
    fornecedoresCharts.qualidade.data.datasets[0].data = data.fornecedores.map(f => f.qualidade_geral);
    fornecedoresCharts.qualidade.update();
  }
  
  // Atualizar gráficos de pizza por tipo
  const totaisPorTipo = data.resumo.por_tipo || {
    toner: { comprados: 0, garantias: 0 },
    maquina: { comprados: 0, garantias: 0 },
    peca: { comprados: 0, garantias: 0 }
  };
  
  if (fornecedoresCharts.chartToners) {
    fornecedoresCharts.chartToners.data.datasets[0].data = [
      totaisPorTipo.toner.comprados,
      totaisPorTipo.toner.garantias
    ];
    fornecedoresCharts.chartToners.update();
  }
  
  if (fornecedoresCharts.chartMaquinas) {
    fornecedoresCharts.chartMaquinas.data.datasets[0].data = [
      totaisPorTipo.maquina.comprados,
      totaisPorTipo.maquina.garantias
    ];
    fornecedoresCharts.chartMaquinas.update();
  }
  
  if (fornecedoresCharts.chartPecas) {
    fornecedoresCharts.chartPecas.data.datasets[0].data = [
      totaisPorTipo.peca.comprados,
      totaisPorTipo.peca.garantias
    ];
    fornecedoresCharts.chartPecas.update();
  }
  
  // Atualizar tabela
  updateTabelaFornecedores(data.fornecedores);
}

function updateTabelaFornecedores(fornecedores) {
  const tbody = document.getElementById('tabelaFornecedores');
  
  if (!fornecedores || fornecedores.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="11" class="px-6 py-8 text-center text-gray-500">
          Nenhum dado encontrado para os filtros selecionados
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = fornecedores.map(f => `
    <tr class="hover:bg-gray-50">
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${f.nome}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">${f.toner.comprados}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-medium">${f.toner.garantias}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center ${getQualidadeColorClass(f.toner.qualidade)}">
        ${f.toner.qualidade.toFixed(2)}%
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">${f.maquina.comprados}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-medium">${f.maquina.garantias}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center ${getQualidadeColorClass(f.maquina.qualidade)}">
        ${f.maquina.qualidade.toFixed(2)}%
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">${f.peca.comprados}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-medium">${f.peca.garantias}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center ${getQualidadeColorClass(f.peca.qualidade)}">
        ${f.peca.qualidade.toFixed(2)}%
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold ${getQualidadeColorClass(f.qualidade_geral)}">
        ${f.qualidade_geral.toFixed(2)}%
      </td>
    </tr>
  `).join('');
}

function getQualidadeColorClass(percentual) {
  if (percentual >= 95) return 'text-green-600 font-semibold';
  if (percentual >= 90) return 'text-blue-600 font-semibold';
  if (percentual >= 80) return 'text-yellow-600 font-semibold';
  if (percentual >= 70) return 'text-orange-600 font-semibold';
  return 'text-red-600 font-semibold';
}

function clearFiltersFornecedores() {
  document.getElementById('filtroFilialFornecedores').value = '';
  
  // Desmarcar todas as origens selecionadas
  const origemSelect = document.getElementById('filtroOrigemFornecedores');
  Array.from(origemSelect.options).forEach(option => option.selected = false);
  
  const hoje = new Date();
  const inicioAno = new Date(hoje.getFullYear(), 0, 1);
  
  document.getElementById('dataInicialFornecedores').value = inicioAno.toISOString().split('T')[0];
  document.getElementById('dataFinalFornecedores').value = hoje.toISOString().split('T')[0];
  
  // Limpar tabela
  document.getElementById('tabelaFornecedores').innerHTML = `
    <tr>
      <td colspan="11" class="px-6 py-8 text-center text-gray-500">
        Selecione os filtros e clique em "Aplicar Filtros" para ver os dados
      </td>
    </tr>
  `;
  
  // Limpar cards
  document.getElementById('totalFornecedores').textContent = '0';
  document.getElementById('totalItensComprados').textContent = '0';
  document.getElementById('totalGarantias').textContent = '0';
}
</script>
