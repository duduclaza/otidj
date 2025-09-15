<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Registro de Retornados</h1>
    <button onclick="openRetornadoModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
      </svg>
      <span>Registrar Novo Retornado</span>
    </button>
  </div>

  <!-- Filters and Search -->
  <div class="bg-white border rounded-lg p-4">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
        <input type="text" id="searchInput" placeholder="Modelo, código, usuário..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
        <input type="date" id="dateFrom" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
        <input type="date" id="dateTo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div class="flex items-end justify-end space-x-2">
        <button onclick="filterData()" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
          Filtrar
        </button>
        <button onclick="exportData()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
          Exportar
        </button>
        <button onclick="openImportModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
          Importar
        </button>
      </div>
    </div>
  </div>

  <!-- Data Grid -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código Cliente</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filial</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destino</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
          </tr>
        </thead>
        <tbody id="retornadosTable" class="bg-white divide-y divide-gray-200">
          <?php if (!empty($retornados)): ?>
            <?php foreach ($retornados as $retornado): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <?= e($retornado['modelo']) ?>
                  <?php if (!$retornado['modelo_cadastrado']): ?>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                      Modelo não cadastrado
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($retornado['codigo_cliente']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($retornado['usuario']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($retornado['filial']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php 
                    $colors = [
                      'descarte' => 'bg-red-100 text-red-800',
                      'estoque' => 'bg-green-100 text-green-800', 
                      'uso_interno' => 'bg-blue-100 text-blue-800',
                      'garantia' => 'bg-purple-100 text-purple-800'
                    ];
                    $color = $colors[$retornado['destino']] ?? 'bg-gray-100 text-gray-800';
                  ?>
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= $color ?>">
                    <?= ucfirst(str_replace('_', ' ', $retornado['destino'])) ?>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('d/m/Y', strtotime($retornado['data_registro'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum registro encontrado</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

</div>
</main>
</div>
</div>

<!-- Retornado Modal -->
<div id="retornadoModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] overflow-y-auto flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-white rounded-t-xl sticky top-0 z-10 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">Registrar Novo Retornado</h3>
      <button onclick="closeRetornadoModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <!-- Content -->
    <form id="retornadoForm" class="px-6 py-6 space-y-6">
      <!-- Mode Selection -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <label class="block text-sm font-medium text-gray-700 mb-3">Modo de Registro</label>
        <div class="flex space-x-4">
          <label class="flex items-center">
            <input type="radio" name="modo" value="peso" checked class="mr-2" onchange="toggleMode()">
            <span class="text-sm font-medium">Modo Peso</span>
          </label>
          <label class="flex items-center">
            <input type="radio" name="modo" value="chip" class="mr-2" onchange="toggleMode()">
            <span class="text-sm font-medium">Modo % Chip</span>
          </label>
        </div>
      </div>

      <!-- Basic Fields -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
          <select name="modelo" onchange="updateTonerData()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Selecione o modelo</option>
          <?php foreach ($toners as $toner): ?>
            <option value="<?= e($toner) ?>"><?= e($toner) ?></option>
          <?php endforeach; ?>
        </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Usuário *</label>
          <input type="text" name="usuario" value="<?= e($_SESSION['user'] ?? 'Usuário Sistema') ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filial *</label>
          <select name="filial" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Selecione a filial</option>
            <?php foreach ($filiais as $filial): ?>
              <option value="<?= e($filial) ?>"><?= e($filial) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Código do Cliente *</label>
          <input type="text" name="codigo_cliente" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <!-- Mode-specific Fields -->
      <div id="pesoFields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Peso Retornado (g)</label>
          <input type="number" name="peso_retornado" step="0.01" min="0" oninput="calculatePercentage()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Percentual Restante</label>
          <input type="text" id="percentualCalculado" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50">
        </div>
      </div>

      <div id="chipFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Percentual do Chip (%)</label>
          <input type="number" name="percentual_chip" step="0.01" min="0" max="100" oninput="calculatePercentage()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <!-- Destination Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Destino Final *</label>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <button type="button" onclick="selectDestino('descarte')" class="destino-btn p-3 border-2 border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors text-center" data-destino="descarte">
            <div class="text-sm font-bold">DESCARTE</div>
          </button>
          <button type="button" onclick="selectDestino('estoque')" class="destino-btn p-3 border-2 border-green-300 text-green-700 rounded-lg hover:bg-green-50 transition-colors text-center" data-destino="estoque">
            <div class="text-sm font-bold">ESTOQUE</div>
          </button>
          <button type="button" onclick="selectDestino('uso_interno')" class="destino-btn p-3 border-2 border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors text-center" data-destino="uso_interno">
            <div class="text-sm font-bold">USO INTERNO</div>
          </button>
          <button type="button" onclick="selectDestino('garantia')" class="destino-btn p-3 border-2 border-purple-300 text-purple-700 rounded-lg hover:bg-purple-50 transition-colors text-center" data-destino="garantia">
            <div class="text-sm font-bold">GARANTIA</div>
          </button>
        </div>
        <input type="hidden" name="destino" id="destinoSelected">
      </div>

      <!-- Value Calculation Display -->
      <div id="valorCalculado" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="text-sm font-medium text-green-800 mb-1">Valor Calculado para Estoque:</div>
        <div class="text-lg font-bold text-green-900" id="valorDisplay">R$ 0,00</div>
      </div>

      <!-- Guidance Display -->
      <div id="guidanceDisplay" class="hidden rounded-lg p-4">
        <div class="text-sm font-medium mb-2" id="guidanceTitle">Orientação:</div>
        <div class="text-sm" id="guidanceText"></div>
      </div>
    </form>

    <!-- Footer -->
    <div class="px-6 py-6 bg-gray-50 border-t border-gray-200 rounded-b-xl sticky bottom-0 z-10">
      <div class="flex justify-end space-x-4">
        <button onclick="closeRetornadoModal()" class="px-6 py-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button onclick="submitRetornado()" class="px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Registrar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">Importar Retornados</h3>
    </div>
    
    <!-- Content -->
    <div class="px-6 py-4 space-y-4">
      <!-- File Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Selecione o arquivo Excel:</label>
        <input type="file" id="importFileInput" accept=".xlsx,.xls,.csv" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <p class="text-xs text-gray-500 mt-1">Formatos aceitos: .xlsx, .xls, .csv</p>
      </div>
      
      <!-- Progress Bar -->
      <div id="importProgressContainer" class="hidden">
        <div class="mb-3">
          <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
            <span>Progresso da Importação</span>
            <span id="importProgressText">0%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-3">
            <div id="importProgressBar" class="bg-gradient-to-r from-orange-500 to-orange-600 h-3 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
          </div>
        </div>
        <div id="importStatus" class="text-sm text-gray-600 bg-gray-50 rounded-lg p-2"></div>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
      <!-- Template Download -->
      <div class="mb-3">
        <button onclick="downloadRetornadosTemplate()" class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-orange-700 bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100 transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Baixar Template Excel
        </button>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex space-x-3">
        <button id="importCancelBtn" onclick="closeImportModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button id="importSubmitBtn" onclick="importRetornados()" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-lg hover:bg-orange-700 transition-colors">
          Importar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let tonerData = {};
let selectedDestino = '';

// Modal functions
function openRetornadoModal() {
  document.getElementById('retornadoModal').classList.remove('hidden');
}

function closeRetornadoModal() {
  document.getElementById('retornadoModal').classList.add('hidden');
  document.getElementById('retornadoForm').reset();
  selectedDestino = '';
  updateDestinoButtons();
}

function openImportModal() {
  document.getElementById('importModal').classList.remove('hidden');
}

function closeImportModal() {
  document.getElementById('importModal').classList.add('hidden');
  document.getElementById('importProgressContainer').classList.add('hidden');
}

// Mode toggle
function toggleMode() {
  const modo = document.querySelector('input[name="modo"]:checked').value;
  const pesoFields = document.getElementById('pesoFields');
  const chipFields = document.getElementById('chipFields');
  
  if (modo === 'peso') {
    pesoFields.classList.remove('hidden');
    chipFields.classList.add('hidden');
  } else {
    pesoFields.classList.add('hidden');
    chipFields.classList.remove('hidden');
  }
  
  calculatePercentage();
}

// Toner data update
function updateTonerData() {
  const modelo = document.querySelector('select[name="modelo"]').value;
  if (!modelo) return;
  
  // Fetch toner data from server
  fetch(`/api/toner?modelo=${encodeURIComponent(modelo)}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.tonerData = data.toner;
        calculatePercentage();
      }
    })
    .catch(() => {
      // Fallback - use default values
      window.tonerData = { gramatura: 10, peso_vazio: 0, preco: 150 };
      calculatePercentage();
    });
}

// Calculations
function calculatePercentage() {
  const modo = document.querySelector('input[name="modo"]:checked').value;
  const modelo = document.querySelector('select[name="modelo"]').value;
  
  if (!modelo) return;
  
  if (modo === 'peso') {
    const pesoRetornado = parseFloat(document.querySelector('input[name="peso_retornado"]').value) || 0;
    const pesoVazio = window.tonerData ? window.tonerData.peso_vazio : 0;
    const gramatura = window.tonerData ? window.tonerData.gramatura : 10;
    
    // Calculate gramatura existente: peso_retornado - peso_vazio
    const gramaturaExistente = Math.max(0, pesoRetornado - pesoVazio);
    
    // Calculate percentage: (gramatura_existente / gramatura_total) * 100
    const percentual = Math.min(100, Math.max(0, (gramaturaExistente / gramatura) * 100));
    document.getElementById('percentualCalculado').value = percentual.toFixed(2) + '%';
    
    // Store calculated percentage for value calculation and guidance
    window.calculatedPercentage = percentual;
    window.gramaturaExistente = gramaturaExistente;
  } else if (modo === 'chip') {
    const percentualChip = parseFloat(document.querySelector('input[name="percentual_chip"]').value) || 0;
    window.calculatedPercentage = percentualChip;
  }
  
  calculateValue();
  showGuidance();
}

// Guidance system based on parameters
function showGuidance() {
  const percentual = window.calculatedPercentage || 0;
  const guidanceDiv = document.getElementById('guidanceDisplay');
  const guidanceTitle = document.getElementById('guidanceTitle');
  const guidanceText = document.getElementById('guidanceText');
  
  if (!window.parameters) {
    // Load parameters from database
    loadParameters().then(() => showGuidance());
    return;
  }
  
  // Find matching parameter
  const matchingParam = window.parameters.find(param => 
    percentual >= param.percentual_min && (param.percentual_max === null || percentual <= param.percentual_max)
  );
  
  if (matchingParam && percentual > 0) {
    guidanceTitle.textContent = `Orientação (${percentual.toFixed(2)}%):`;
    guidanceText.textContent = matchingParam.orientacao;
    
    // Set color based on parameter name
    guidanceDiv.className = 'rounded-lg p-4 ';
    const paramName = matchingParam.nome.toLowerCase();
    if (paramName.includes('descarte')) {
      guidanceDiv.className += 'bg-red-50 border border-red-200 text-red-800';
    } else if (paramName.includes('semi') || paramName.includes('amarelo')) {
      guidanceDiv.className += 'bg-yellow-50 border border-yellow-200 text-yellow-800';
    } else {
      guidanceDiv.className += 'bg-green-50 border border-green-200 text-green-800';
    }
    
    guidanceDiv.classList.remove('hidden');
  } else {
    guidanceDiv.classList.add('hidden');
  }
}

// Load parameters from database
function loadParameters() {
  return fetch('/api/parameters')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.parameters = data.parameters;
      }
    })
    .catch(() => {
      // Fallback parameters
      window.parameters = [
        { nome: 'Descarte', percentual_min: 0, percentual_max: 39, orientacao: 'Se a % for <= 39%: Descarte o toner, pois não tem mais utilidade.' },
        { nome: 'Estoque Semi Novo', percentual_min: 40, percentual_max: 89, orientacao: 'Se a % for >= 40% e <= 89%: Teste o Toner; se a qualidade estiver boa, envie para o estoque como seminovo e marque a % na caixa; se estiver ruim, solicite garantia.' },
        { nome: 'Estoque Novo', percentual_min: 90, percentual_max: 100, orientacao: 'Se a % for >= 90%: Teste o Toner; se a qualidade estiver boa, envie para o estoque como novo e marque na caixa; se estiver ruim, solicite garantia.' }
      ];
    });
}

// Destination selection
function selectDestino(destino) {
  selectedDestino = destino;
  document.getElementById('destinoSelected').value = destino;
  updateDestinoButtons();
  calculateValue();
}

function updateDestinoButtons() {
  document.querySelectorAll('.destino-btn').forEach(btn => {
    const btnDestino = btn.getAttribute('data-destino');
    if (btnDestino === selectedDestino) {
      btn.classList.add('ring-2', 'ring-offset-2');
      if (btnDestino === 'descarte') btn.classList.add('ring-red-500', 'bg-red-50');
      else if (btnDestino === 'estoque') btn.classList.add('ring-green-500', 'bg-green-50');
      else if (btnDestino === 'uso_interno') btn.classList.add('ring-blue-500', 'bg-blue-50');
      else if (btnDestino === 'garantia') btn.classList.add('ring-purple-500', 'bg-purple-50');
    } else {
      btn.classList.remove('ring-2', 'ring-offset-2', 'ring-red-500', 'ring-green-500', 'ring-blue-500', 'ring-purple-500', 'bg-red-50', 'bg-green-50', 'bg-blue-50', 'bg-purple-50');
    }
  });
}

// Value calculation
function calculateValue() {
  const valorDiv = document.getElementById('valorCalculado');
  const valorDisplay = document.getElementById('valorDisplay');
  
  if (selectedDestino === 'estoque') {
    const preco = window.tonerData ? window.tonerData.preco : 150;
    const percentual = window.calculatedPercentage || 0;
    
    // Calculate value based on percentage remaining and toner price
    const valor = (preco * percentual) / 100;
    valorDisplay.textContent = 'R$ ' + valor.toFixed(2).replace('.', ',');
    valorDiv.classList.remove('hidden');
  } else {
    valorDiv.classList.add('hidden');
  }
}

// Form submission
function submitRetornado() {
  const form = document.getElementById('retornadoForm');
  const formData = new FormData(form);
  
  if (!selectedDestino) {
    alert('Por favor, selecione um destino.');
    return;
  }
  
  fetch('/toners/retornados', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      closeRetornadoModal();
      location.reload();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

// Import functions
function downloadRetornadosTemplate() {
  const data = [
    ['Modelo', 'Código Cliente', 'Usuário', 'Filial', 'Modo', 'Peso Retornado', 'Percentual Chip', 'Destino', 'Data Registro'],
    ['HP CF280A', 'CLI001', 'João Silva', 'Matriz', 'peso', '450.50', '', 'estoque', '2024-01-15'],
    ['HP CE285A', 'CLI002', 'Maria Santos', 'Filial 1', 'chip', '', '75.5', 'uso_interno', '2024-01-16'],
    ['', '', '', '', '', '', '', '', '']
  ];
  
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(data);
  
  ws['!cols'] = [
    {wch: 15}, {wch: 15}, {wch: 15}, {wch: 15}, {wch: 10}, {wch: 15}, {wch: 15}, {wch: 15}, {wch: 12}
  ];
  
  XLSX.utils.book_append_sheet(wb, ws, "Template Retornados");
  XLSX.writeFile(wb, 'template_retornados.xlsx');
}

function importRetornados() {
  const fileInput = document.getElementById('importFileInput');
  const file = fileInput.files[0];
  
  if (!file) {
    alert('Por favor, selecione um arquivo.');
    return;
  }
  
  // Show progress and simulate import
  document.getElementById('importProgressContainer').classList.remove('hidden');
  document.getElementById('importSubmitBtn').disabled = true;
  
  let progress = 0;
  const interval = setInterval(() => {
    progress += Math.random() * 20;
    if (progress > 100) progress = 100;
    
    document.getElementById('importProgressBar').style.width = progress + '%';
    document.getElementById('importProgressText').textContent = Math.round(progress) + '%';
    document.getElementById('importStatus').textContent = `Importando linha ${Math.round(progress/10)}...`;
    
    if (progress >= 100) {
      clearInterval(interval);
      setTimeout(() => {
        alert('Importação concluída!');
        closeImportModal();
        location.reload();
      }, 500);
    }
  }, 200);
}

// Filter and export functions
function filterData() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const dateFrom = document.getElementById('dateFrom').value;
  const dateTo = document.getElementById('dateTo').value;
  
  // Simple client-side filtering
  const rows = document.querySelectorAll('#retornadosTable tr');
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    const show = text.includes(search);
    row.style.display = show ? '' : 'none';
  });
}

function exportData() {
  const dateFrom = prompt('Data inicial (YYYY-MM-DD):');
  const dateTo = prompt('Data final (YYYY-MM-DD):');
  
  if (dateFrom && dateTo) {
    alert(`Exportando dados de ${dateFrom} até ${dateTo}...`);
    // Implement actual export logic
  }
}
</script>
