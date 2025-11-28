<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Registro de Retornados</h1>
    <div class="flex space-x-3">
      <button id="toggleRetornadoFormBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Registrar Novo Retornado</span>
      </button>
    </div>
  </div>

  <!-- Formulário Inline de Registro de Retornados -->
  <div id="retornadoFormContainer" class="hidden bg-white rounded-lg shadow-lg border border-gray-200 p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
      <h2 id="retornadoFormTitle" class="text-xl font-semibold text-gray-900">Registrar Novo Retornado</h2>
      <button onclick="cancelRetornadoForm()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="retornadoForm" class="space-y-6">
      <!-- Usuário e Filial -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="nomeUsuario" class="block text-sm font-medium text-gray-700 mb-2">Nome do Usuário</label>
          <input type="text" id="nomeUsuario" name="usuario" value="<?= $_SESSION['user_name'] ?? 'Usuário' ?>" readonly class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-600 cursor-not-allowed">
        </div>
        <div>
          <label for="filialUsuario" class="block text-sm font-medium text-gray-700 mb-2">Filial</label>
          <input type="text" id="filialUsuario" name="filial" value="<?= $_SESSION['user_filial'] ?? 'Jundiaí' ?>" readonly class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-600 cursor-not-allowed">
        </div>
      </div>

      <!-- Modelo, Serial e Quantidade -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="relative">
          <label for="modeloToner" class="block text-sm font-medium text-gray-700 mb-2">Modelo do Toner *</label>
          <div class="relative">
            <input 
              type="text" 
              id="modeloToner" 
              name="modelo" 
              required 
              placeholder="🔍 Digite para buscar um modelo..."
              autocomplete="off"
              class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
            <input type="hidden" id="modeloId" name="modelo_id" value="">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>
          
          <!-- Dropdown com resultados da busca -->
          <div id="modeloDropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
            <div id="modeloOptions" class="py-1">
              <!-- Opções serão inseridas aqui via JavaScript -->
            </div>
            <div id="noResults" class="px-3 py-2 text-sm text-gray-500 hidden">
              <div class="flex items-center justify-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Nenhum modelo encontrado
              </div>
            </div>
          </div>
        </div>
        <div>
          <label for="codigoCliente" class="block text-sm font-medium text-gray-700 mb-2">Código Cliente *</label>
          <input type="text" id="codigoCliente" name="codigo_cliente" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-2">Quantidade *</label>
          <input type="number" id="quantidade" name="quantidade" value="1" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <p class="mt-1 text-xs text-gray-500">Quantidade de toners retornados</p>
        </div>
      </div>

      <!-- Dados do Modelo (Exibição) -->
      <div id="dadosModelo" class="hidden bg-gray-50 rounded-lg p-4">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Dados do Modelo</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
          <div>
            <span class="text-gray-600">Peso Cheio:</span>
            <span id="pesoCheio" class="font-medium ml-2">-</span>
          </div>
          <div>
            <span class="text-gray-600">Peso Vazio:</span>
            <span id="pesoVazio" class="font-medium ml-2">-</span>
          </div>
          <div>
            <span class="text-gray-600">Gramatura:</span>
            <span id="gramatura" class="font-medium ml-2">-</span>
          </div>
          <div>
            <span class="text-gray-600">Rendimento:</span>
            <span id="rendimento" class="font-medium ml-2">-</span>
          </div>
        </div>
      </div>

      <!-- Tipo de Medição -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Medição *</label>
        <div class="flex space-x-4">
          <label class="flex items-center">
            <input type="radio" name="tipoMedicao" value="peso" class="mr-2" onchange="toggleMedicaoType()">
            <span>Peso Físico</span>
          </label>
          <label class="flex items-center">
            <input type="radio" name="tipoMedicao" value="chip" class="mr-2" onchange="toggleMedicaoType()">
            <span>% do Chip</span>
          </label>
        </div>
      </div>

      <!-- Campo de Peso -->
      <div id="camposPeso" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="pesoRetornado" class="block text-sm font-medium text-gray-700 mb-2">Peso do Retornado (g) *</label>
          <input type="number" id="pesoRetornado" name="peso_retornado" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="calcularGramatura()" onchange="calcularGramatura()">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Gramatura Restante</label>
          <div id="gramaturaRestante" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">-</div>
        </div>
      </div>

      <!-- Campo de Percentual -->
      <div id="camposPercentual" class="hidden">
        <div>
          <label for="percentualChip" class="block text-sm font-medium text-gray-700 mb-2">% do Chip *</label>
          <input type="number" id="percentualChip" name="percentual_chip" min="0" max="100" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="calcularPercentual()" onchange="calcularPercentual()">
        </div>
      </div>

      <!-- Resultado dos Cálculos -->
      <div id="resultadoCalculo" class="hidden bg-blue-50 rounded-lg p-4">
        <h3 class="text-lg font-medium text-blue-900 mb-3">Resultado da Análise</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
          <div>
            <span class="text-blue-700">% Restante:</span>
            <span id="percentualRestante" class="font-bold ml-2">-</span>
          </div>
          <div>
            <span class="text-blue-700">Folhas Estimadas:</span>
            <span id="folhasEstimadas" class="font-bold ml-2">-</span>
          </div>
          <div>
            <span class="text-blue-700">Valor Estimado:</span>
            <span id="valorEstimado" class="font-bold ml-2">-</span>
          </div>
        </div>
        <div id="orientacaoSistema" class="bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-500 p-4 rounded-lg shadow-sm">
          <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-yellow-800 font-semibold">Orientação do Sistema:</p>
          </div>
          <p id="textoOrientacao" class="text-yellow-800 font-medium text-lg">-</p>
        </div>
      </div>

      <!-- Seleção de Destino -->
      <div id="selecaoDestino" class="hidden">
        <label class="block text-sm font-medium text-gray-700 mb-3">Destino do Toner *</label>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <button type="button" onclick="selecionarDestino('descarte')" class="destino-btn border-2 border-gray-300 rounded-lg p-3 text-center hover:border-red-500 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-colors">
            <div class="text-red-600 font-medium">Descarte</div>
          </button>
          <button type="button" onclick="selecionarDestino('uso_interno')" class="destino-btn border-2 border-gray-300 rounded-lg p-3 text-center hover:border-blue-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors">
            <div class="text-blue-600 font-medium">Uso Interno</div>
          </button>
          <button type="button" onclick="selecionarDestino('estoque')" class="destino-btn border-2 border-gray-300 rounded-lg p-3 text-center hover:border-green-500 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-colors">
            <div class="text-green-600 font-medium">Estoque</div>
          </button>
          <button type="button" onclick="selecionarDestino('garantia')" class="destino-btn border-2 border-gray-300 rounded-lg p-3 text-center hover:border-purple-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors">
            <div class="text-purple-600 font-medium">Garantia</div>
          </button>
        </div>
        <input type="hidden" id="destinoSelecionado" name="destino" required>
      </div>

      <!-- Campo de Observação (para Descarte) -->
      <div id="campoObservacao" class="hidden">
        <label for="observacaoDescarte" class="block text-sm font-medium text-gray-700 mb-2">Observação</label>
        <textarea id="observacaoDescarte" name="observacao" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Motivo do descarte ou observações adicionais..."></textarea>
      </div>

      <!-- Botões de Ação -->
      <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
        <button type="button" onclick="cancelRetornadoForm()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          Cancelar
        </button>
        <button type="submit" id="submitRetornadoBtn" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          Registrar Retornado
        </button>
      </div>
    </form>
  </div>
    <script>
// Variáveis globais
let modelosData = [];
let parametrosGerais = [];
let selectedDestino = '';
let deleteId = null;

// ===== TODAS AS FUNÇÕES DEFINIDAS NO INÍCIO =====

// Filter and export functions
window.filterData = function filterData() {
  const searchInput = document.getElementById('searchInput');
  const search = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const dateFrom = document.getElementById('dateFrom')?.value || '';
  const dateTo = document.getElementById('dateTo')?.value || '';
  
  console.log('🔍 Buscando:', { search, dateFrom, dateTo });
  
  // Get all table rows from tbody
  const tbody = document.getElementById('retornadosTable');
  if (!tbody) {
    console.error('❌ Tabela retornadosTable não encontrada!');
    return;
  }
  
  const rows = tbody.querySelectorAll('tr');
  let visibleCount = 0;
  let totalCount = rows.length;
  
  console.log(`📊 Total de linhas: ${totalCount}`);
  
  rows.forEach((row, index) => {
    let show = true;
    
    // Text search filter - busca em todas as colunas
    if (search) {
      const cells = row.querySelectorAll('td');
      let rowText = '';
      cells.forEach(cell => {
        rowText += ' ' + cell.textContent.toLowerCase();
      });
      
      // Busca parcial - cada termo deve estar presente
      const searchTerms = search.split(' ').filter(t => t.length > 0);
      for (const term of searchTerms) {
        if (!rowText.includes(term)) {
          show = false;
          break;
        }
      }
    }
    
    // Date range filter
    if (show && (dateFrom || dateTo)) {
      const dateCell = row.querySelector('td:nth-last-child(2)'); // Data column (second to last)
      if (dateCell) {
        const dateText = dateCell.textContent.trim();
        // Convert DD/MM/YYYY to YYYY-MM-DD for comparison
        const dateParts = dateText.split('/');
        if (dateParts.length === 3) {
          const rowDate = `${dateParts[2]}-${dateParts[1].padStart(2, '0')}-${dateParts[0].padStart(2, '0')}`;
          
          if (dateFrom && rowDate < dateFrom) {
            show = false;
          }
          if (dateTo && rowDate > dateTo) {
            show = false;
          }
        }
      }
    }
    
    row.style.display = show ? '' : 'none';
    if (show) visibleCount++;
  });
  
  console.log(`✅ Resultado: ${visibleCount} de ${totalCount} registros`);
  
  // Show feedback only if there was a search term
  if (search || dateFrom || dateTo) {
    showNotification(`${visibleCount} de ${totalCount} registro(s) encontrado(s)`, 'info');
  }
}

// Clear filters function
window.clearFilters = function clearFilters() {
  console.log('🧹 Limpando filtros...');
  
  // Limpar campos de filtro
  document.getElementById('searchInput').value = '';
  document.getElementById('dateFrom').value = '';
  document.getElementById('dateTo').value = '';
  
  // Mostrar todas as linhas da tabela
  const rows = document.querySelectorAll('#retornadosTable tr');
  let totalCount = 0;
  
  rows.forEach(row => {
    row.style.display = '';
    totalCount++;
  });
  
  // Show feedback
  showNotification(`Filtros limpos: ${totalCount} registro(s) visível(is)`, 'success');
  console.log('✅ Filtros limpos - todas as linhas visíveis');
}

window.exportToExcel = function exportToExcel() {
  // Show loading state
  const button = event.target.closest('button');
  const originalContent = button.innerHTML;
  button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> <span>Exportando...</span>';
  button.disabled = true;
  
  // Get filter values
  const dateFrom = document.getElementById('dateFrom').value;
  const dateTo = document.getElementById('dateTo').value;
  const search = document.getElementById('searchInput').value;
  
  // Build query parameters
  const params = new URLSearchParams();
  if (dateFrom) params.append('date_from', dateFrom);
  if (dateTo) params.append('date_to', dateTo);
  if (search) params.append('search', search);
  
  // Create download link
  const link = document.createElement('a');
  link.href = `/toners/retornados/export?${params.toString()}`;
  link.download = `retornados_${new Date().toISOString().slice(0, 10)}.csv`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  
  // Restore button after delay
  setTimeout(() => {
    button.innerHTML = originalContent;
    button.disabled = false;
    showNotification('Exportação concluída com sucesso!', 'success');
  }, 2000);
}

// Import functions removed - not needed anymore

// Nova função de exclusão simples e direta
window.excluirRetornado = function excluirRetornado(id, modelo) {
  console.log('🗑️ Excluir retornado:', { id, modelo });
  
  // Confirmação simples com alert nativo
  if (!confirm(`Tem certeza que deseja excluir o registro do modelo "${modelo}"?\n\nEsta ação não pode ser desfeita.`)) {
    return;
  }
  
  console.log('✅ Usuário confirmou exclusão, enviando requisição...');
  
  // Fazer requisição DELETE diretamente
  fetch(`/toners/retornados/delete/${id}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
    }
  })
  .then(response => {
    console.log('📡 Resposta recebida:', response.status);
    return response.json();
  })
  .then(result => {
    console.log('📋 Resultado:', result);
    
    if (result.success) {
      alert('✅ Registro excluído com sucesso!');
      location.reload(); // Recarregar página para atualizar lista
    } else {
      alert('❌ Erro ao excluir registro: ' + result.message);
    }
  })
  .catch(error => {
    console.error('❌ Erro na requisição:', error);
    alert('❌ Erro de conexão: ' + error.message);
  });
}

// Notification function
window.showNotification = function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
  
  notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
  notification.innerHTML = `
    <div class="flex items-center space-x-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
      <span>${message}</span>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  // Animate in
  setTimeout(() => {
    notification.classList.remove('translate-x-full');
  }, 100);
  
  // Animate out and remove
  setTimeout(() => {
    notification.classList.add('translate-x-full');
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 4000);
}

// Carregar dados iniciais
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded, iniciando carregamento...');
  
  // Verificar se as funções estão disponíveis
  console.log('Funções disponíveis:', {
    excluirRetornado: typeof window.excluirRetornado,
    showNotification: typeof window.showNotification,
    filterData: typeof window.filterData,
    exportToExcel: typeof window.exportToExcel
  });
  
  // Teste rápido das funções
  if (typeof window.excluirRetornado !== 'function') {
    console.error('❌ excluirRetornado não está definida!');
  } else {
    console.log('✅ excluirRetornado está OK');
  }
  
  // Teste simples das funções críticas
  setTimeout(() => {
    console.log('🧪 TESTE DAS FUNÇÕES:');
    console.log('excluirRetornado disponível:', typeof window.excluirRetornado === 'function');
    console.log('filterData disponível:', typeof window.filterData === 'function');
    console.log('exportToExcel disponível:', typeof window.exportToExcel === 'function');
    console.log('calcularPercentual disponível:', typeof calcularPercentual === 'function');
    console.log('mostrarResultados disponível:', typeof mostrarResultados === 'function');
    console.log('forcarExibicaoDestinos disponível:', typeof forcarExibicaoDestinos === 'function');
  }, 1000);
  
  carregarModelos();
  carregarParametrosGerais();
  
  // Bind do botão toggle
  const toggleBtn = document.getElementById('toggleRetornadoFormBtn');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', toggleRetornadoForm);
  }
  
  // Bind do formulário
  const form = document.getElementById('retornadoForm');
  if (form) {
    form.addEventListener('submit', submitRetornado);
  }
  
  // Bind da busca em tempo real
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    // Busca enquanto digita (com debounce)
    let searchTimeout;
    searchInput.addEventListener('input', function(e) {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        filterData();
      }, 300);
    });
    
    // Busca ao pressionar Enter
    searchInput.addEventListener('keyup', function(e) {
      if (e.key === 'Enter') {
        clearTimeout(searchTimeout);
        filterData();
      }
    });
    
    console.log('✅ Busca em tempo real configurada');
  }
  
  // Teste direto da API
  setTimeout(() => {
    console.log('Testando API diretamente...');
    fetch('/api/toner')
      .then(response => {
        console.log('Teste direto - Status:', response.status);
        return response.text();
      })
      .then(text => {
        console.log('Teste direto - Response:', text);
      })
      .catch(error => {
        console.error('Teste direto - Erro:', error);
      });
  }, 1000);
});

// Toggle do formulário inline
function toggleRetornadoForm() {
  const container = document.getElementById('retornadoFormContainer');
  const btn = document.getElementById('toggleRetornadoFormBtn');
  
  if (container.classList.contains('hidden')) {
    // Mostrar formulário
    container.classList.remove('hidden');
    btn.innerHTML = `
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
      <span>Cancelar</span>
    `;
    resetForm();
    // Recarregar modelos quando abrir o formulário
    carregarModelos();
  } else {
    // Ocultar formulário
    cancelRetornadoForm();
  }
}

function cancelRetornadoForm() {
  const container = document.getElementById('retornadoFormContainer');
  const btn = document.getElementById('toggleRetornadoFormBtn');
  
  container.classList.add('hidden');
  btn.innerHTML = `
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
    </svg>
    <span>Registrar Novo Retornado</span>
  `;
  resetForm();
}

function resetForm() {
  document.getElementById('retornadoForm').reset();
  
  // Restaurar valores padrão dos campos de usuário e filial
  document.getElementById('nomeUsuario').value = '<?= $_SESSION['user_name'] ?? 'Usuário' ?>';
  document.getElementById('filialUsuario').value = '<?= $_SESSION['user_filial'] ?? 'Jundiaí' ?>';
  
  document.getElementById('dadosModelo').classList.add('hidden');
  document.getElementById('camposPeso').classList.add('hidden');
  document.getElementById('camposPercentual').classList.add('hidden');
  document.getElementById('resultadoCalculo').classList.add('hidden');
  document.getElementById('selecaoDestino').classList.add('hidden');
  document.getElementById('campoObservacao').classList.add('hidden');
  selectedDestino = '';
  updateDestinoButtons();
}

// Carregar modelos de toner
function carregarModelos() {
  console.log('Carregando modelos de toner...');
  fetch('/api/toner', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json'
    }
  })
    .then(response => {
      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers.get('content-type'));
      return response.text();
    })
    .then(text => {
      console.log('Raw response:', text);
      try {
        const data = JSON.parse(text);
        console.log('Parsed data:', data);
        
        if (Array.isArray(data)) {
          modelosData = data;
          console.log(`${data.length} modelos carregados para busca`);
          
          // Configurar funcionalidade de busca
          setupModeloSearch();
        } else {
          console.error('Resposta não é um array:', data);
        }
      } catch (e) {
        console.error('JSON parse error:', e);
        console.error('Response text:', text);
      }
    })
    .catch(error => {
      console.error('Erro ao carregar modelos:', error);
    });
}

// Configurar funcionalidade de busca de modelos
function setupModeloSearch() {
  const input = document.getElementById('modeloToner');
  const dropdown = document.getElementById('modeloDropdown');
  const optionsContainer = document.getElementById('modeloOptions');
  const noResults = document.getElementById('noResults');
  let selectedModeloId = null;
  
  // Função para filtrar e exibir modelos
  function filterModelos(searchTerm) {
    const filtered = modelosData.filter(modelo => 
      modelo.modelo.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    optionsContainer.innerHTML = '';
    
    if (filtered.length === 0) {
      noResults.classList.remove('hidden');
      optionsContainer.classList.add('hidden');
    } else {
      noResults.classList.add('hidden');
      optionsContainer.classList.remove('hidden');
      
      filtered.forEach(modelo => {
        const option = document.createElement('div');
        option.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50 hover:text-blue-700 text-sm';
        option.textContent = modelo.modelo;
        option.dataset.modeloId = modelo.id;
        option.dataset.modeloNome = modelo.modelo;
        
        option.addEventListener('click', () => {
          input.value = modelo.modelo;
          selectedModeloId = modelo.id;
          
          // Definir o ID no campo hidden
          const modeloIdField = document.getElementById('modeloId');
          if (modeloIdField) {
            modeloIdField.value = modelo.id;
          }
          
          dropdown.classList.add('hidden');
          
          console.log('✅ Modelo selecionado:', {
            nome: modelo.modelo,
            id: modelo.id
          });
          
          // Disparar evento change para carregar dados do modelo
          const changeEvent = new Event('change', { bubbles: true });
          Object.defineProperty(changeEvent, 'target', {
            writable: false,
            value: { id: 'modeloToner', value: modelo.id }
          });
          document.dispatchEvent(changeEvent);
        });
        
        optionsContainer.appendChild(option);
      });
    }
  }
  
  // Evento de input para busca
  input.addEventListener('input', (e) => {
    const searchTerm = e.target.value.trim();
    
    if (searchTerm.length === 0) {
      dropdown.classList.add('hidden');
      selectedModeloId = null;
      return;
    }
    
    filterModelos(searchTerm);
    dropdown.classList.remove('hidden');
  });
  
  // Mostrar todos os modelos ao focar no campo
  input.addEventListener('focus', () => {
    if (input.value.length === 0) {
      filterModelos(''); // Mostrar todos
      dropdown.classList.remove('hidden');
    }
  });
  
  // Fechar dropdown ao clicar fora
  document.addEventListener('click', (e) => {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.add('hidden');
    }
  });
  
  // Navegação por teclado
  input.addEventListener('keydown', (e) => {
    const options = optionsContainer.querySelectorAll('div');
    const currentActive = optionsContainer.querySelector('.bg-blue-100');
    let activeIndex = -1;
    
    if (currentActive) {
      activeIndex = Array.from(options).indexOf(currentActive);
    }
    
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (currentActive) currentActive.classList.remove('bg-blue-100');
      
      activeIndex = activeIndex < options.length - 1 ? activeIndex + 1 : 0;
      if (options[activeIndex]) {
        options[activeIndex].classList.add('bg-blue-100');
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (currentActive) currentActive.classList.remove('bg-blue-100');
      
      activeIndex = activeIndex > 0 ? activeIndex - 1 : options.length - 1;
      if (options[activeIndex]) {
        options[activeIndex].classList.add('bg-blue-100');
      }
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (currentActive) {
        currentActive.click();
      }
    } else if (e.key === 'Escape') {
      dropdown.classList.add('hidden');
    }
  });
}

// Carregar parâmetros gerais com retry automático - SEMPRE usar os parâmetros configurados
function carregarParametrosGerais(tentativa = 1, maxTentativas = 3) {
  console.log(`📡 Carregando parâmetros de retornados configurados... (tentativa ${tentativa}/${maxTentativas})`);
  
  return fetch('/api/parametros', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json'
    }
  })
    .then(response => {
      console.log('Response status (parâmetros):', response.status);
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      return response.text();
    })
    .then(text => {
      console.log('Raw response (parâmetros):', text);
      try {
        const data = JSON.parse(text);
        console.log('Parsed data (parâmetros):', data);
        
        if (data.success && Array.isArray(data.data)) {
          parametrosGerais = data.data;
          console.log(`✅ ${data.data.length} parâmetros carregados com sucesso!`);
          
          // Mostrar notificação de sucesso se houve tentativas anteriores
          if (tentativa > 1) {
            mostrarNotificacaoParametros('Parâmetros carregados com sucesso!', 'success');
          }
          
          return data.data;
        } else {
          throw new Error('Resposta inválida da API: ' + JSON.stringify(data));
        }
      } catch (e) {
        throw new Error('Erro ao processar resposta JSON: ' + e.message);
      }
    })
    .catch(error => {
      console.error(`❌ Erro ao carregar parâmetros (tentativa ${tentativa}):`, error);
      
      // Se ainda há tentativas restantes, tentar novamente
      if (tentativa < maxTentativas) {
        const delay = tentativa * 1000; // Delay progressivo: 1s, 2s, 3s
        console.log(`🔄 Tentando novamente em ${delay}ms...`);
        
        mostrarNotificacaoParametros(`Erro ao carregar parâmetros. Tentando novamente... (${tentativa}/${maxTentativas})`, 'warning');
        
        return new Promise(resolve => {
          setTimeout(() => {
            resolve(carregarParametrosGerais(tentativa + 1, maxTentativas));
          }, delay);
        });
      } else {
        // Esgotaram as tentativas
        console.error('❌ Falha ao carregar parâmetros após todas as tentativas');
        parametrosGerais = [];
        mostrarNotificacaoParametros('Falha ao carregar parâmetros. Usando valores padrão.', 'error');
        return [];
      }
    });
}

function mostrarNotificacaoParametros(mensagem, tipo = 'info') {
  // Criar notificação temporária
  const notification = document.createElement('div');
  const bgColor = tipo === 'success' ? 'bg-green-500' : 
                  tipo === 'error' ? 'bg-red-500' : 
                  tipo === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
  
  notification.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full text-sm`;
  notification.innerHTML = `
    <div class="flex items-center space-x-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <span>${mensagem}</span>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  // Animar entrada
  setTimeout(() => {
    notification.classList.remove('translate-x-full');
  }, 100);
  
  // Remover após 3 segundos
  setTimeout(() => {
    notification.classList.add('translate-x-full');
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

// Ao selecionar modelo
document.addEventListener('change', function(e) {
  if (e.target.id === 'modeloToner') {
    const modeloValue = e.target.value;
    console.log('🔍 Modelo selecionado:', modeloValue);
    
    if (modeloValue) {
      // Buscar modelo por ID ou por nome
      let modelo = modelosData.find(m => m.id == modeloValue);
      if (!modelo) {
        modelo = modelosData.find(m => m.modelo === modeloValue);
      }
      
      console.log('📋 Modelo encontrado:', modelo);
      
      if (modelo) {
        exibirDadosModelo(modelo);
      } else {
        console.log('⚠️ Modelo não encontrado nos dados carregados');
        document.getElementById('dadosModelo').classList.add('hidden');
      }
    } else {
      document.getElementById('dadosModelo').classList.add('hidden');
    }
  }
});

function exibirDadosModelo(modelo) {
  document.getElementById('pesoCheio').textContent = modelo.peso_cheio ? modelo.peso_cheio + 'g' : '-';
  document.getElementById('pesoVazio').textContent = modelo.peso_vazio ? modelo.peso_vazio + 'g' : '-';
  document.getElementById('gramatura').textContent = modelo.gramatura ? modelo.gramatura + 'g' : '-';
  document.getElementById('rendimento').textContent = modelo.rendimento ? modelo.rendimento + ' folhas' : '-';
  
  document.getElementById('dadosModelo').classList.remove('hidden');
}

// Toggle tipo de medição
function toggleMedicaoType() {
  const tipo = document.querySelector('input[name="tipoMedicao"]:checked')?.value;
  
  document.getElementById('camposPeso').classList.add('hidden');
  document.getElementById('camposPercentual').classList.add('hidden');
  document.getElementById('resultadoCalculo').classList.add('hidden');
  document.getElementById('selecaoDestino').classList.add('hidden');
  
  if (tipo === 'peso') {
    document.getElementById('camposPeso').classList.remove('hidden');
    validarModeloParaPeso();
  } else if (tipo === 'chip') {
    document.getElementById('camposPercentual').classList.remove('hidden');
  }
  
  console.log('🔄 Tipo de medição alterado para:', tipo);
}

function validarModeloParaPeso() {
  const modeloId = document.getElementById('modeloToner').value;
  if (!modeloId) {
    alert('Selecione um modelo primeiro');
    return false;
  }
  
  const modelo = modelosData.find(m => m.id == modeloId);
  if (!modelo || !modelo.peso_cheio || !modelo.peso_vazio) {
    alert('Para usar peso físico, o modelo deve ter peso cheio e vazio cadastrados');
    return false;
  }
  
  return true;
}

// Calcular gramatura a partir do peso
function calcularGramatura() {
  console.log('⚖️ Calculando por peso físico...');
  
  const modeloId = document.getElementById('modeloToner').value;
  const pesoInput = document.getElementById('pesoRetornado').value;
  const pesoRetornado = parseFloat(pesoInput);
  
  console.log('📊 Dados do cálculo por peso:', {
    modeloId,
    pesoInput,
    pesoRetornado,
    isNaN: isNaN(pesoRetornado)
  });
  
  if (!modeloId) {
    console.log('⚠️ Modelo não selecionado');
    // Ocultar seções se não há modelo
    document.getElementById('resultadoCalculo').classList.add('hidden');
    document.getElementById('selecaoDestino').classList.add('hidden');
    document.getElementById('gramaturaRestante').textContent = '-';
    return;
  }
  
  if (isNaN(pesoRetornado) || pesoRetornado < 0) {
    console.log('⚠️ Peso inválido:', pesoRetornado);
    // Ocultar seções se peso inválido
    document.getElementById('resultadoCalculo').classList.add('hidden');
    document.getElementById('selecaoDestino').classList.add('hidden');
    document.getElementById('gramaturaRestante').textContent = '-';
    return;
  }
  
  // Buscar modelo por ID ou por nome
  let modelo = modelosData.find(m => m.id == modeloId);
  if (!modelo) {
    modelo = modelosData.find(m => m.modelo === modeloId);
  }
  
  if (!modelo) {
    console.log('⚠️ Modelo não encontrado nos dados:', modeloId);
    console.log('📋 Modelos disponíveis:', modelosData.map(m => ({id: m.id, modelo: m.modelo})));
    // Ainda assim, tentar mostrar os botões de destino
    forcarExibicaoDestinos();
    document.getElementById('gramaturaRestante').textContent = 'Modelo não encontrado';
    return;
  }
  
  // Garantir que o modelo tenha valores padrão se necessário
  const modeloSeguro = {
    peso_vazio: modelo?.peso_vazio || 0,
    peso_cheio: modelo?.peso_cheio || 0,
    gramatura: modelo?.gramatura || 0,
    ...modelo
  };
  
  console.log('📋 Modelo encontrado:', modeloSeguro);
  
  // Verificar se temos dados suficientes para cálculo
  if (!modeloSeguro.peso_vazio && !modeloSeguro.gramatura) {
    console.log('⚠️ Modelo sem dados de peso/gramatura - usando fallback');
    // Mostrar botões mesmo sem dados completos
    forcarExibicaoDestinos();
    document.getElementById('gramaturaRestante').textContent = 'Dados incompletos';
    return;
  }
  
  // Calcular gramatura restante
  const gramaturaRestante = Math.max(0, pesoRetornado - modeloSeguro.peso_vazio);
  
  // Calcular percentual restante
  let percentualRestante = 0;
  if (modeloSeguro.gramatura > 0) {
    percentualRestante = Math.max(0, Math.min(100, (gramaturaRestante / modeloSeguro.gramatura) * 100));
  } else if (modeloSeguro.peso_cheio > 0 && modeloSeguro.peso_vazio > 0) {
    // Fallback: usar diferença entre peso cheio e vazio
    const gramaturaTotal = modeloSeguro.peso_cheio - modeloSeguro.peso_vazio;
    percentualRestante = Math.max(0, Math.min(100, (gramaturaRestante / gramaturaTotal) * 100));
  }
  
  // Atualizar display da gramatura restante
  document.getElementById('gramaturaRestante').textContent = gramaturaRestante.toFixed(1) + 'g (' + percentualRestante.toFixed(1) + '%)';
  
  console.log('📊 Cálculo por peso completo:', {
    pesoRetornado,
    pesoVazio: modeloSeguro.peso_vazio,
    pesoCheio: modeloSeguro.peso_cheio,
    gramatura: modeloSeguro.gramatura,
    gramaturaRestante,
    percentualRestante
  });
  
  // Detectar casos especiais
  if (pesoRetornado <= modeloSeguro.peso_vazio) {
    console.log('🚨 PESO IGUAL OU MENOR QUE PESO VAZIO - TONER VAZIO!');
    percentualRestante = 0;
  } else if (modeloSeguro.peso_cheio > 0 && pesoRetornado >= modeloSeguro.peso_cheio) {
    console.log('✅ PESO IGUAL OU MAIOR QUE PESO CHEIO - TONER CHEIO!');
    percentualRestante = 100;
  }
  
  console.log('✅ Chamando mostrarResultados com percentual:', percentualRestante);
  mostrarResultados(percentualRestante, modeloSeguro);
}

// Calcular a partir do percentual
function calcularPercentual() {
  console.log('🔢 Calculando por percentual do chip...');
  
  const modeloId = document.getElementById('modeloToner').value;
  const percentualInput = document.getElementById('percentualChip').value;
  const percentual = parseFloat(percentualInput);
  
  console.log('📊 Dados do cálculo por percentual:', {
    modeloId,
    percentualInput,
    percentual,
    isNaN: isNaN(percentual)
  });
  
  if (!modeloId) {
    console.log('⚠️ Modelo não selecionado');
    // Ocultar seções se não há modelo
    document.getElementById('resultadoCalculo').classList.add('hidden');
    document.getElementById('selecaoDestino').classList.add('hidden');
    return;
  }
  
  if (isNaN(percentual) || percentual < 0 || percentual > 100) {
    console.log('⚠️ Percentual inválido:', percentual);
    // Ocultar seções se percentual inválido
    document.getElementById('resultadoCalculo').classList.add('hidden');
    document.getElementById('selecaoDestino').classList.add('hidden');
    return;
  }
  
  // Permitir percentual 0 (toner vazio)
  if (percentual === 0) {
    console.log('📊 Percentual é 0% - toner vazio');
  }
  
  // Buscar modelo por ID ou por nome
  let modelo = modelosData.find(m => m.id == modeloId);
  if (!modelo) {
    modelo = modelosData.find(m => m.modelo === modeloId);
  }
  
  if (!modelo) {
    console.log('⚠️ Modelo não encontrado nos dados:', modeloId);
    console.log('📋 Modelos disponíveis:', modelosData.map(m => ({id: m.id, modelo: m.modelo})));
    // Ainda assim, tentar mostrar os botões de destino
    forcarExibicaoDestinos();
    return;
  }
  
  console.log('✅ Chamando mostrarResultados com percentual:', percentual);
  mostrarResultados(percentual, modelo);
}

// Função de teste para modo percentual
window.testarModoPercentual = function(percentualTeste = 50) {
  console.log('🧪 TESTE DO MODO PERCENTUAL');
  console.log('Simulando entrada de', percentualTeste + '%');
  
  // Simular seleção de modelo
  const modeloInput = document.getElementById('modeloToner');
  if (modeloInput && modelosData.length > 0) {
    modeloInput.value = modelosData[0].id || modelosData[0].modelo;
    console.log('✅ Modelo selecionado:', modeloInput.value);
  }
  
  // Simular entrada de percentual
  const percentualInput = document.getElementById('percentualChip');
  if (percentualInput) {
    percentualInput.value = percentualTeste;
    console.log('✅ Percentual definido:', percentualInput.value);
    
    // Chamar função de cálculo
    calcularPercentual();
    console.log('✅ Função calcularPercentual() chamada');
  } else {
    console.error('❌ Campo percentualChip não encontrado');
  }
}

// Função de teste para modo peso
window.testarModoPeso = function(pesoTeste = 1122) {
  console.log('🧪 TESTE DO MODO PESO');
  console.log('Simulando entrada de', pesoTeste + 'g');
  
  // Simular seleção de modelo
  const modeloInput = document.getElementById('modeloToner');
  if (modeloInput && modelosData.length > 0) {
    modeloInput.value = modelosData[0].id || modelosData[0].modelo;
    console.log('✅ Modelo selecionado:', modeloInput.value);
    
    // Simular dados do modelo se necessário
    if (modelosData[0]) {
      console.log('📋 Dados do modelo:', {
        peso_vazio: modelosData[0].peso_vazio,
        peso_cheio: modelosData[0].peso_cheio,
        gramatura: modelosData[0].gramatura
      });
    }
  }
  
  // Simular entrada de peso
  const pesoInput = document.getElementById('pesoRetornado');
  if (pesoInput) {
    pesoInput.value = pesoTeste;
    console.log('✅ Peso definido:', pesoInput.value);
    
    // Chamar função de cálculo
    calcularGramatura();
    console.log('✅ Função calcularGramatura() chamada');
  } else {
    console.error('❌ Campo pesoRetornado não encontrado');
  }
}

function mostrarResultados(percentualRestante, modelo) {
  console.log('🎯 Mostrando resultados para:', percentualRestante + '%');
  console.log('📋 Modelo recebido:', modelo);
  
  // Garantir que o modelo tenha valores padrão se necessário
  const modeloSeguro = {
    rendimento: modelo?.rendimento || 1500,
    valor: modelo?.valor || 150,
    ...modelo
  };
  
  // Calcular folhas estimadas
  const folhasEstimadas = Math.round((percentualRestante / 100) * modeloSeguro.rendimento);
  
  // Calcular valor estimado (simulação)
  const valorEstimado = (percentualRestante / 100) * modeloSeguro.valor;
  
  // Atualizar display
  document.getElementById('percentualRestante').textContent = percentualRestante.toFixed(1) + '%';
  document.getElementById('folhasEstimadas').textContent = folhasEstimadas + ' folhas';
  document.getElementById('valorEstimado').textContent = 'R$ ' + valorEstimado.toFixed(2);
  
  // SEMPRE mostrar resultados e seleção de destino primeiro
  document.getElementById('resultadoCalculo').classList.remove('hidden');
  document.getElementById('selecaoDestino').classList.remove('hidden');
  console.log('✅ Seções de resultado e destino exibidas');
  
  // Verificar se parâmetros estão carregados, senão recarregar
  if (!Array.isArray(parametrosGerais) || parametrosGerais.length === 0) {
    console.log('⚠️ Parâmetros não carregados, recarregando...');
    document.getElementById('textoOrientacao').textContent = 'Carregando orientação do sistema...';
    
    // Carregar parâmetros e depois gerar orientação
    carregarParametrosGerais().then(() => {
      console.log('✅ Parâmetros recarregados, gerando orientação...');
      const orientacao = gerarOrientacao(percentualRestante);
      atualizarOrientacaoVisual(orientacao, percentualRestante);
    }).catch(error => {
      console.error('❌ Erro ao recarregar parâmetros:', error);
      document.getElementById('textoOrientacao').textContent = 'Erro ao carregar orientação. Verifique os parâmetros do sistema.';
    });
  } else {
    // Gerar orientação normalmente
    console.log('✅ Parâmetros já carregados, gerando orientação...');
    const orientacao = gerarOrientacao(percentualRestante);
    atualizarOrientacaoVisual(orientacao, percentualRestante);
  }
}

function atualizarOrientacaoVisual(orientacao, percentual) {
  const textoElement = document.getElementById('textoOrientacao');
  const containerElement = document.getElementById('orientacaoSistema');
  
  // Atualizar texto
  textoElement.textContent = orientacao;
  
  // Adicionar animação de mudança
  containerElement.style.transform = 'scale(0.95)';
  containerElement.style.opacity = '0.7';
  
  setTimeout(() => {
    containerElement.style.transform = 'scale(1)';
    containerElement.style.opacity = '1';
    containerElement.style.transition = 'all 0.3s ease';
  }, 100);
  
  // Mudar cor baseada no tipo de orientação
  containerElement.classList.remove('border-yellow-500', 'border-red-500', 'border-green-500', 'border-blue-500');
  
  if (orientacao.toLowerCase().includes('descarte')) {
    containerElement.classList.add('border-red-500');
    containerElement.className = containerElement.className.replace(/from-\w+-\d+/, 'from-red-50').replace(/to-\w+-\d+/, 'to-red-100');
  } else if (orientacao.toLowerCase().includes('estoque')) {
    containerElement.classList.add('border-green-500');
    containerElement.className = containerElement.className.replace(/from-\w+-\d+/, 'from-green-50').replace(/to-\w+-\d+/, 'to-green-100');
  } else if (orientacao.toLowerCase().includes('interno')) {
    containerElement.classList.add('border-blue-500');
    containerElement.className = containerElement.className.replace(/from-\w+-\d+/, 'from-blue-50').replace(/to-\w+-\d+/, 'to-blue-100');
  } else {
    containerElement.classList.add('border-yellow-500');
    containerElement.className = containerElement.className.replace(/from-\w+-\d+/, 'from-yellow-50').replace(/to-\w+-\d+/, 'to-orange-50');
  }
  
  // Log para debug
  console.log(`💡 Orientação atualizada para ${percentual.toFixed(1)}%: "${orientacao}"`);
}

function gerarOrientacao(percentual) {
  console.log('🎯 Gerando orientação para percentual:', percentual);
  console.log('📋 Parâmetros disponíveis:', parametrosGerais);
  
  // SEMPRE tentar usar os parâmetros configurados primeiro
  if (Array.isArray(parametrosGerais) && parametrosGerais.length > 0) {
    console.log('✅ Usando parâmetros configurados do sistema');
    
    // Ordenar parâmetros por faixa_min para garantir ordem correta
    const parametrosOrdenados = [...parametrosGerais].sort((a, b) => a.faixa_min - b.faixa_min);
    console.log('📊 Parâmetros ordenados:', parametrosOrdenados);
    
    // Encontrar o parâmetro correspondente ao percentual
    for (const parametro of parametrosOrdenados) {
      const faixaMin = parseFloat(parametro.faixa_min);
      const faixaMax = parametro.faixa_max ? parseFloat(parametro.faixa_max) : null;
      
      console.log(`🔍 Verificando faixa: ${faixaMin}% - ${faixaMax ? faixaMax + '%' : '∞'}`);
      
      // Se tem faixa máxima, verificar se está dentro do intervalo
      if (faixaMax !== null) {
        if (percentual >= faixaMin && percentual <= faixaMax) {
          console.log(`✅ Percentual ${percentual}% está na faixa ${faixaMin}% - ${faixaMax}%`);
          return parametro.orientacao;
        }
      } else {
        // Se não tem faixa máxima, verificar se é maior ou igual ao mínimo
        if (percentual >= faixaMin) {
          console.log(`✅ Percentual ${percentual}% está na faixa ${faixaMin}% - ∞`);
          return parametro.orientacao;
        }
      }
    }
    
    // Se não encontrou parâmetro correspondente, mas tem parâmetros carregados
    console.log('⚠️ Percentual fora das faixas configuradas nos parâmetros');
    return 'Percentual (' + percentual.toFixed(1) + '%) fora das faixas configuradas. Verifique os parâmetros de retornados.';
  }
  
  // APENAS se não conseguir carregar os parâmetros, usar fallback mínimo
  console.log('❌ Parâmetros não carregados - tentando recarregar...');
  
  // Tentar recarregar parâmetros uma vez
  carregarParametrosGerais().then(() => {
    console.log('🔄 Parâmetros recarregados, gerando orientação novamente...');
    const novaOrientacao = gerarOrientacao(percentual);
    atualizarOrientacaoVisual(novaOrientacao, percentual);
  }).catch(() => {
    console.log('❌ Falha ao recarregar parâmetros');
  });
  
  return 'Carregando orientações dos parâmetros configurados...';
}

function selecionarDestino(destino) {
  selectedDestino = destino;
  document.getElementById('destinoSelecionado').value = destino;
  updateDestinoButtons();
  
  // Mostrar campo de observação apenas para descarte
  if (destino === 'descarte') {
    document.getElementById('campoObservacao').classList.remove('hidden');
  } else {
    document.getElementById('campoObservacao').classList.add('hidden');
  }
}

function updateDestinoButtons() {
  const buttons = document.querySelectorAll('.destino-btn');
  buttons.forEach(btn => {
    btn.classList.remove('border-red-500', 'border-blue-500', 'border-green-500', 'border-purple-500');
    btn.classList.add('border-gray-300');
  });
  
  if (selectedDestino) {
    const colors = {
      'descarte': 'border-red-500',
      'uso_interno': 'border-blue-500',
      'estoque': 'border-green-500',
      'garantia': 'border-purple-500'
    };
    
    const selectedBtn = document.querySelector(`[onclick="selecionarDestino('${selectedDestino}')"]`);
    if (selectedBtn) {
      selectedBtn.classList.remove('border-gray-300');
      selectedBtn.classList.add(colors[selectedDestino]);
    }
  }
}

// Função para forçar exibição dos destinos quando há problemas
function forcarExibicaoDestinos() {
  console.log('🔧 Forçando exibição dos botões de destino...');
  
  // Mostrar seção de destino
  const selecaoDestino = document.getElementById('selecaoDestino');
  if (selecaoDestino) {
    selecaoDestino.classList.remove('hidden');
    console.log('✅ Seção de destino exibida');
  }
  
  // Mostrar orientação padrão
  const orientacaoSistema = document.getElementById('orientacaoSistema');
  const textoOrientacao = document.getElementById('textoOrientacao');
  if (orientacaoSistema && textoOrientacao) {
    textoOrientacao.textContent = 'Selecione o destino apropriado para este toner. Verifique os parâmetros do sistema se necessário.';
    orientacaoSistema.parentElement.classList.remove('hidden');
    console.log('✅ Orientação padrão exibida');
  }
}

function submitRetornado(e) {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  
  // Adicionar campo modo baseado no tipo de medição selecionado
  const tipoMedicao = document.querySelector('input[name="tipoMedicao"]:checked');
  if (tipoMedicao) {
    formData.append('modo', tipoMedicao.value);
  }
  
  // Adicionar destino selecionado
  if (selectedDestino) {
    formData.append('destino', selectedDestino);
  }
  
  // Debug: mostrar todos os dados que serão enviados
  console.log('Dados do formulário:');
  for (let [key, value] of formData.entries()) {
    console.log(key + ': ' + value);
  }
  
  // Validações
  if (!selectedDestino) {
    alert('Selecione um destino para o toner');
    return;
  }
  
  if (!tipoMedicao) {
    alert('Selecione o tipo de medição (Peso Físico ou % do Chip)');
    return;
  }
  
  // Enviar dados
  fetch('/toners/retornados', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    console.log('Response status:', response.status);
    console.log('Response headers:', response.headers.get('content-type'));
    return response.text();
  })
  .then(text => {
    console.log('Raw response:', text);
    try {
      const result = JSON.parse(text);
      if (result.success) {
        alert('Retornado registrado com sucesso!');
        cancelRetornadoForm();
        // Recarregar a página para mostrar o novo registro
        window.location.reload();
      } else {
        alert('Erro: ' + result.message);
      }
    } catch (e) {
      console.error('JSON parse error:', e);
      console.error('Response text:', text);
      alert('Erro no servidor: Resposta inválida recebida');
    }
  })
  .catch(error => {
    console.error('Fetch error:', error);
    alert('Erro de conexão: ' + error.message);
  });
}


      // Código legado mantido para compatibilidade
      (function(){
        var btn = document.getElementById('openRetornadoBtn');
        if (btn && !btn.__retornadoBound) {
          btn.__retornadoBound = true;
          btn.addEventListener('click', function(e){
            e.preventDefault();
            if (typeof window.openRetornadoModal === 'function') {
              window.openRetornadoModal();
              return;
            }
            if (typeof openRetornadoModal === 'function') {
              openRetornadoModal();
              return;
            }
            // Fallback: abrir o modal diretamente sem depender da função
            try {
              var modal = document.getElementById('retornadoModal');
              if (modal) {
                modal.classList.remove('hidden');
              }
              var form = document.getElementById('retornadoForm');
              if (form) { form.reset(); }
              // Variáveis e funções opcionais
              try { window.selectedDestino = ''; } catch(_){}
              if (typeof window.updateDestinoButtons === 'function') { window.updateDestinoButtons(); }
              var obs = document.getElementById('observacao-container');
              if (obs) { obs.classList.add('hidden'); }
              if (typeof window.loadParameters === 'function') {
                Promise.resolve(window.loadParameters()).then(function(){
                  if (typeof window.toggleMode === 'function') { try { window.toggleMode(); } catch(_){} }
                  if (typeof window.showGuidance === 'function') { try { window.showGuidance(window.selectedDestino || ''); } catch(_){} }
                  if (typeof window.calculateValue === 'function') { try { window.calculateValue(); } catch(_){} }
                  if (typeof window.checkAutoDiscard === 'function') { try { window.checkAutoDiscard(); } catch(_){} }
                });
              }
            } catch (err) {
              console.error('Falha ao abrir o modal via fallback:', err);
              alert('Erro ao abrir o formulário. Recarregue a página.');
            }
          });
        }
      })();
    </script>
  </div>

  <!-- Filters and Search -->
  <div class="bg-white border rounded-lg p-4">
    <div class="grid grid-cols-1 lg:grid-cols-6 gap-3">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
        <input type="text" id="searchInput" placeholder="Modelo, cód. cliente, usuário..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
        <input type="date" id="dateFrom" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
        <input type="date" id="dateTo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div class="flex items-end">
        <button onclick="filterData()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition-colors flex items-center justify-center space-x-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
          </svg>
          <span>Filtrar</span>
        </button>
      </div>
      <div class="flex items-end">
        <button onclick="clearFilters()" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg text-sm transition-colors flex items-center justify-center space-x-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
          <span>Limpar</span>
        </button>
      </div>
      <div class="flex items-end">
        <button onclick="exportToExcel()" class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition-colors flex items-center justify-center space-x-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <span>Exportar</span>
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
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filial</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destino</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observação</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
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
                <td class="px-3 py-2 whitespace-nowrap text-sm text-center">
                  <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    <?= e($retornado['quantidade'] ?? 1) ?>
                  </span>
                </td>
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
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <?php if (isset($retornado['valor_calculado']) && $retornado['valor_calculado'] > 0): ?>
                    <span class="font-semibold text-green-600">R$ <?= number_format($retornado['valor_calculado'], 2, ',', '.') ?></span>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2 text-sm text-gray-900 max-w-xs">
                  <?php if (!empty($retornado['observacao'])): ?>
                    <span class="truncate block" title="<?= htmlspecialchars($retornado['observacao']) ?>">
                      <?= htmlspecialchars(substr($retornado['observacao'], 0, 50)) ?><?= strlen($retornado['observacao']) > 50 ? '...' : '' ?>
                    </span>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('d/m/Y', strtotime($retornado['data_registro'])) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <button onclick="excluirRetornado(<?= $retornado['id'] ?>, '<?= e($retornado['modelo']) ?>')" 
                          class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded-md font-medium transition-colors duration-200 shadow-sm hover:shadow-md">
                    Excluir
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">Nenhum registro encontrado</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
      <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <div class="flex items-center text-sm text-gray-700">
          <span>Mostrando <?= ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 ?> a <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']) ?> de <?= $pagination['total_records'] ?> registros</span>
        </div>
        <div class="flex items-center space-x-2">
          <!-- Previous Button -->
          <?php if ($pagination['has_prev']): ?>
            <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors">
              Anterior
            </a>
          <?php else: ?>
            <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
              Anterior
            </span>
          <?php endif; ?>
          
          <!-- Page Numbers -->
          <?php
          $start = max(1, $pagination['current_page'] - 2);
          $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
          ?>
          
          <?php if ($start > 1): ?>
            <a href="?page=1" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors">1</a>
            <?php if ($start > 2): ?>
              <span class="px-3 py-2 text-sm font-medium text-gray-400">...</span>
            <?php endif; ?>
          <?php endif; ?>
          
          <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $pagination['current_page']): ?>
              <span class="px-2 py-1 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded">
                <?= $i ?>
              </span>
            <?php else: ?>
              <a href="?page=<?= $i ?>" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors">
                <?= $i ?>
              </a>
            <?php endif; ?>
          <?php endfor; ?>
          
          <?php if ($end < $pagination['total_pages']): ?>
            <?php if ($end < $pagination['total_pages'] - 1): ?>
              <span class="px-3 py-2 text-sm font-medium text-gray-400">...</span>
            <?php endif; ?>
            <a href="?page=<?= $pagination['total_pages'] ?>" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors"><?= $pagination['total_pages'] ?></a>
          <?php endif; ?>
          
          <!-- Next Button -->
          <?php if ($pagination['has_next']): ?>
            <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors">
              Próximo
            </a>
          <?php else: ?>
            <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
              Próximo
            </span>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
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
      <button onclick="window.closeRetornadoModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
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
            <input type="radio" name="modo" value="peso" class="mr-2" checked onchange="window.toggleMode()">
            <span class="text-sm font-medium">Modo Peso</span>
          </label>
          <label class="flex items-center">
            <input type="radio" name="modo" value="chip" class="mr-2" onchange="window.toggleMode()">
            <span class="text-sm font-medium">Modo % Chip</span>
          </label>
        </div>
      </div>

      <!-- Basic Fields -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
          <select name="modelo" onchange="window.updateTonerData()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Selecione o modelo</option>
          <?php foreach ($toners as $toner): ?>
            <option value="<?= e($toner) ?>"><?= e($toner) ?></option>
          <?php endforeach; ?>
        </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Usuário *</label>
          <input type="text" name="usuario" value="Sistema" required readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-700 focus:ring-0 focus:border-gray-300">
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
          <input type="number" name="peso_retornado" step="0.01" min="0" oninput="window.calculatePercentage(); window.calculateValue(); window.showGuidance(); window.checkAutoDiscard();" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Percentual Restante</label>
          <input type="text" id="percentualCalculado" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50">
        </div>
      </div>

      <div id="chipFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Percentual do Chip (%)</label>
          <input type="number" name="percentual_chip" step="0.01" min="0" max="100" oninput="window.calculatePercentage(); window.calculateValue(); window.showGuidance(); window.checkAutoDiscard();" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <!-- Destination Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Destino Final *</label>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <button type="button" onclick="window.selectDestino('descarte')" class="destino-btn p-3 border-2 border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors text-center" data-destino="descarte">
            <div class="text-sm font-bold">DESCARTE</div>
          </button>
          <button type="button" onclick="window.selectDestino('estoque')" class="destino-btn p-3 border-2 border-green-300 text-green-700 rounded-lg hover:bg-green-50 transition-colors text-center" data-destino="estoque">
            <div class="text-sm font-bold">ESTOQUE</div>
          </button>
          <button type="button" onclick="window.selectDestino('uso_interno')" class="destino-btn p-3 border-2 border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors text-center" data-destino="uso_interno">
            <div class="text-sm font-bold">USO INTERNO</div>
          </button>
          <button type="button" onclick="window.selectDestino('garantia')" class="destino-btn p-3 border-2 border-purple-300 text-purple-700 rounded-lg hover:bg-purple-50 transition-colors text-center" data-destino="garantia">
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

      <!-- Campo de Observação (aparece apenas quando destino é descarte) -->
      <div id="observacao-container" class="hidden">
        <label for="retornado-observacao" class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
        <textarea id="retornado-observacao" name="observacao" rows="3" placeholder="Digite uma observação sobre o descarte..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
      </div>
    </form>

    <!-- Footer -->
    <div class="px-6 py-6 bg-gray-50 border-t border-gray-200 rounded-b-xl sticky bottom-0 z-10">
      <div class="flex justify-end space-x-4">
        <button onclick="window.closeRetornadoModal()" class="px-6 py-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button onclick="window.submitRetornado()" class="px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Registrar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">Importar Retornados</h3>
    </div>
    
    <!-- Content -->
    <div class="px-6 py-4">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo Excel (.xlsx)</label>
        <input type="file" id="importFileInput" accept=".xlsx,.xls,.csv" class="w-full border border-gray-300 rounded-md px-3 py-2">
      </div>
      
      <div id="importProgress" class="mb-4" style="display: none;">
        <div class="bg-gray-200 rounded-full h-2 mb-2">
          <div id="importProgressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <p id="importStatus" class="text-sm text-gray-600">Preparando importação...</p>
      </div>
      
      <!-- Debug Console -->
      <div id="debugConsole" class="mb-4" style="display: none;">
        <h4 class="text-sm font-semibold text-gray-700 mb-2">Debug Console:</h4>
        <div id="debugOutput" class="bg-gray-900 text-green-400 p-3 rounded-md h-64 overflow-y-auto text-xs font-mono"></div>
        <button onclick="downloadDebugReport()" id="downloadDebugBtn" class="mt-2 px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">Baixar Relatório Debug</button>
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
        <button onclick="toggleDebug()" id="debugToggleBtn" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700">Mostrar Debug</button>
        <button id="importSubmitBtn" onclick="importRetornados()" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-lg hover:bg-orange-700 transition-colors">
          Importar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de exclusão removido - usando confirmação nativa -->

<script>
let tonerData = {};
let selectedDestino = '';

// Debug: Check if modal exists when page loads
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('retornadoModal');
  console.log('Modal found:', modal ? 'Yes' : 'No');
  if (!modal) {
    console.error('retornadoModal element not found in DOM');
  }
  // Ensure the open button triggers the modal even if inline handler is blocked
  const openBtn = document.getElementById('openRetornadoBtn');
  if (openBtn) {
    openBtn.addEventListener('click', function(e) {
      e.preventDefault();
      if (typeof window.openRetornadoModal === 'function') {
        window.openRetornadoModal();
      } else if (typeof openRetornadoModal === 'function') {
        openRetornadoModal();
      } else {
        console.error('openRetornadoModal is not defined');
        alert('Erro ao abrir o formulário. Recarregue a página.');
      }
    });
  }
});

// Global variables for debug and activity logging
let debugMode = false;
let importResults = [];
let activityLog = [];

// Activity logging system
function logActivity(type, action, details = {}) {
  const timestamp = new Date().toISOString();
  const logEntry = {
    timestamp,
    type, // 'user_action', 'calculation', 'error', 'validation', 'modal', 'form'
    action,
    details,
    url: window.location.href,
    userAgent: navigator.userAgent
  };
  
  activityLog.push(logEntry);
  console.log(`[${type.toUpperCase()}] ${action}:`, details);
  
  // Keep only last 1000 entries to prevent memory issues
  if (activityLog.length > 1000) {
    activityLog = activityLog.slice(-1000);
  }
}

// Enhanced error handling
window.addEventListener('error', function(e) {
  logActivity('error', 'JavaScript Error', {
    message: e.message,
    filename: e.filename,
    lineno: e.lineno,
    colno: e.colno,
    stack: e.error ? e.error.stack : null
  });
});

window.addEventListener('unhandledrejection', function(e) {
  logActivity('error', 'Unhandled Promise Rejection', {
    reason: e.reason,
    stack: e.reason && e.reason.stack ? e.reason.stack : null
  });
});

function toggleDebug() {
  debugMode = !debugMode;
  const console = document.getElementById('debugConsole');
  const btn = document.getElementById('debugToggleBtn');
  if (debugMode) {
    console.style.display = 'block';
    btn.textContent = 'Ocultar Debug';
    logActivity('user_action', 'Debug Console Opened');
  } else {
    console.style.display = 'none';
    btn.textContent = 'Mostrar Debug';
    logActivity('user_action', 'Debug Console Closed');
  }
}

function addDebugLog(message) {
  const timestamp = new Date().toLocaleTimeString();
  const logEntry = `[${timestamp}] ${message}`;
  debugLogs.push(logEntry);
  
  if (debugMode) {
    const output = document.getElementById('debugOutput');
    output.innerHTML += logEntry + '\n';
    output.scrollTop = output.scrollHeight;
  }
}

function downloadDebugReport() {
  const report = {
    timestamp: new Date().toISOString(),
    logs: debugLogs,
    results: importResults,
    summary: {
      totalRows: importResults.length,
      successful: importResults.filter(r => r.success).length,
      failed: importResults.filter(r => !r.success).length
    }
  };
  
  const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `debug-report-${new Date().toISOString().slice(0,19)}.json`;
  a.click();
  URL.revokeObjectURL(url);
}

function downloadImportReport() {
  const timestamp = new Date();
  const dateStr = timestamp.toLocaleDateString('pt-BR');
  const timeStr = timestamp.toLocaleTimeString('pt-BR');
  
  // Create detailed report
  const report = {
    titulo: 'Relatório de Importação - Retornados',
    data_importacao: `${dateStr} às ${timeStr}`,
    resumo: {
      total_linhas_processadas: importResults.length,
      sucessos: importResults.filter(r => r.success).length,
      erros: importResults.filter(r => !r.success).length,
      taxa_sucesso: `${((importResults.filter(r => r.success).length / importResults.length) * 100).toFixed(1)}%`
    },
    detalhes_por_linha: importResults.map(result => ({
      linha: result.row,
      status: result.success ? 'SUCESSO' : 'ERRO',
      dados_enviados: result.data,
      resposta_servidor: result.response,
      observacoes: result.success ? 'Importado com sucesso' : result.response.message
    })),
    logs_debug: debugLogs,
    estatisticas: {
      modelos_importados: [...new Set(importResults.filter(r => r.success).map(r => r.data.modelo))],
      filiais_envolvidas: [...new Set(importResults.filter(r => r.success).map(r => r.data.filial))],
      destinos_utilizados: [...new Set(importResults.filter(r => r.success).map(r => r.data.destino))]
    }
  };
  
  // Generate filename with timestamp
  const filename = `relatorio-importacao-${timestamp.getFullYear()}-${String(timestamp.getMonth()+1).padStart(2,'0')}-${String(timestamp.getDate()).padStart(2,'0')}_${String(timestamp.getHours()).padStart(2,'0')}-${String(timestamp.getMinutes()).padStart(2,'0')}.json`;
  
  // Download the report
  const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
  
  // Also create a simplified CSV version
  downloadImportCSV(report);
}

function downloadImportCSV(report) {
  const csvData = [
    ['Linha', 'Status', 'Modelo', 'Código Cliente', 'Usuário', 'Filial', 'Destino', 'Valor', 'Data', 'Observações'],
    ...report.detalhes_por_linha.map(linha => [
      linha.linha,
      linha.status,
      linha.dados_enviados.modelo || '',
      linha.dados_enviados.codigo_cliente || '',
      linha.dados_enviados.usuario || '',
      linha.dados_enviados.filial || '',
      linha.dados_enviados.destino || '',
      linha.dados_enviados.valor_calculado || 0,
      linha.dados_enviados.data_registro || '',
      linha.observacoes || ''
    ])
  ];
  
  const csvContent = csvData.map(row => 
    row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(',')
  ).join('\n');
  
  const timestamp = new Date();
  const filename = `relatorio-importacao-${timestamp.getFullYear()}-${String(timestamp.getMonth()+1).padStart(2,'0')}-${String(timestamp.getDate()).padStart(2,'0')}_${String(timestamp.getHours()).padStart(2,'0')}-${String(timestamp.getMinutes()).padStart(2,'0')}.csv`;
  
  const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

// Modal functions
function openRetornadoModal() {
  logActivity('modal', 'Open Retornado Modal', { modalId: 'retornadoModal' });
  console.log('openRetornadoModal called');
  const modal = document.getElementById('retornadoModal');
  console.log('Modal element:', modal);
  
  if (modal) {
    console.log('Removing hidden class from modal');
    modal.classList.remove('hidden');
    
    // Load parameters when modal opens to ensure fresh data
    if (typeof loadParameters === 'function') {
      Promise.resolve(loadParameters()).then(() => {
        if (typeof showGuidance === 'function') {
          try { showGuidance(window.selectedDestino || ''); } catch(_) {}
        }
        if (typeof calculateValue === 'function') {
          try { calculateValue(); } catch(_) {}
        }
        if (typeof checkAutoDiscard === 'function') {
          try { checkAutoDiscard(); } catch(_) {}
        }
      });
    }
    
    // Reset form
    const form = document.getElementById('retornadoForm');
    if (form) {
      form.reset();
    }
    
    selectedDestino = '';
    
    // Try to update destino buttons if function exists
    if (typeof updateDestinoButtons === 'function') {
      updateDestinoButtons();
    }
    
    // Hide observacao container initially
    const observacaoContainer = document.getElementById('observacao-container');
    if (observacaoContainer) {
      observacaoContainer.classList.add('hidden');
    }
    
    console.log('Modal should be visible now');
  } else {
    console.error('Modal retornadoModal não encontrado no DOM');
    alert('Erro: Modal não encontrado. Recarregue a página.');
  }
}

function closeRetornadoModal() {
  document.getElementById('retornadoModal').classList.add('hidden');
  document.getElementById('retornadoForm').reset();
  selectedDestino = '';
  updateDestinoButtons();
}

// Import modal functions already defined at the top

// Mode toggle
function toggleMode() {
  const modo = document.querySelector('input[name="modo"]:checked').value;
  logActivity('user_action', 'Mode Changed', { mode: modo });
  
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
  checkAutoDiscard();
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

// Auto discard detection
function checkAutoDiscard() {
  const modo = document.querySelector('input[name="modo"]:checked').value;
  const percentual = window.calculatedPercentage || 0;
  const pesoRetornado = parseFloat(document.querySelector('input[name="peso_retornado"]').value) || 0;
  const pesoVazio = window.tonerData ? window.tonerData.peso_vazio : 0;
  
  let autoDiscardReason = '';
  
  // Check for weight-based discard (peso igual ao peso vazio)
  if (modo === 'peso' && pesoRetornado > 0 && pesoVazio > 0 && pesoRetornado <= pesoVazio) {
    autoDiscardReason = 'Peso igual ao peso vazio - Toner sem tinta restante';
  }
  // Check for chip percentage discard (0%)
  else if (modo === 'chip' && percentual === 0) {
    autoDiscardReason = 'Percentual do chip é 0% - Toner vazio';
  }
  // Check for calculated percentage discard (0% remaining)
  else if (percentual === 0) {
    autoDiscardReason = 'Percentual restante é 0% - Toner vazio';
  }
  
  if (autoDiscardReason) {
    // Suggest descarte (manual 100%): apenas exibe a notificação, não seleciona automaticamente
    showAutoDiscardNotification(autoDiscardReason);
  } else {
    // Hide notification if no auto discard
    hideAutoDiscardNotification();
  }
}

function showAutoDiscardNotification(reason) {
  // Remove existing notification
  const existing = document.getElementById('autoDiscardNotification');
  if (existing) existing.remove();
  
  // Create notification
  const notification = document.createElement('div');
  notification.id = 'autoDiscardNotification';
  notification.className = 'bg-red-50 border border-red-200 rounded-lg p-4 mb-4';
  notification.innerHTML = `
    <div class="flex items-center">
      <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.876c1.17 0 2.25-.16 2.25-1.729 0-.329-.314-.729-.314-1.271L18 7.5c0-.621-.504-1.125-1.125-1.125H7.125C6.504 6.375 6 6.879 6 7.5l-.686 8.5c0 .542-.314.942-.314 1.271 0 1.569 1.08 1.729 2.25 1.729z"></path>
      </svg>
      <div>
        <div class="text-sm font-medium text-red-800">Descarte Automático Detectado</div>
        <div class="text-sm text-red-700 mt-1">${reason}</div>
        <div class="text-xs text-red-600 mt-1">DESCARTE foi selecionado automaticamente. Você pode alterar se necessário.</div>
      </div>
    </div>
  `;
  
  // Insert before destination selection
  const destinoSection = document.querySelector('label[for="destinoSelected"]').parentElement;
  destinoSection.parentElement.insertBefore(notification, destinoSection);
}

function hideAutoDiscardNotification() {
  const notification = document.getElementById('autoDiscardNotification');
  if (notification) notification.remove();
}

// Destination selection
function selectDestino(destino) {
  logActivity('user_action', 'Destination Selected', { destination: destino, previousDestination: selectedDestino });
  
  selectedDestino = destino;
  document.getElementById('destinoSelected').value = destino;
  updateDestinoButtons();
  calculateValue();
  
  // Hide auto discard notification when user manually selects destination
  }

  // Show/hide value calculation for estoque
  const valorDiv = document.getElementById('valorCalculado');
  if (destino === 'estoque') {
    valorDiv.classList.remove('hidden');
  } else {
    valorDiv.classList.add('hidden');
  }

  // Show guidance
  showGuidance(destino);
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
    const modo = document.querySelector('input[name="modo"]:checked')?.value || 'peso';
    const custoFolha = window.tonerData ? window.tonerData.custo_por_folha : 0.10;
    const capacidadeFolhas = window.tonerData ? window.tonerData.capacidade_folhas : 1500;
    
    let folhasRestantes = 0;
    
    if (modo === 'peso') {
      // Modo Peso: usar gramatura existente para calcular folhas
      const gramaturaExistente = window.gramaturaExistente || 0;
      const gramaturaPorFolha = window.tonerData ? (window.tonerData.gramatura / capacidadeFolhas) : 0.007;
      folhasRestantes = gramaturaExistente / gramaturaPorFolha;
    } else if (modo === 'chip') {
      // Modo Chip: usar percentual do chip para calcular folhas
      const percentualChip = window.calculatedPercentage || 0;
      folhasRestantes = (capacidadeFolhas * percentualChip) / 100;
    }
    
    // Calcular valor em dinheiro: folhas restantes × custo por folha
    const valor = folhasRestantes * custoFolha;
    valorDisplay.textContent = 'R$ ' + valor.toFixed(2).replace('.', ',') + ' (' + Math.round(folhasRestantes) + ' folhas)';
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
    ['Modelo', 'Código Cliente', 'Usuário', 'Filial', 'Destino', 'Valor Recuperado', 'Data'],
    ['HP CF280A', 'CLI001', 'João Silva', 'Matriz', 'estoque', '125.50', '15/01/2024'],
    ['HP CE285A', 'CLI002', 'Maria Santos', 'Filial 1', 'uso interno', '0.00', '16/01/2024'],
    ['HP CB435A', 'CLI003', 'Pedro Costa', 'Filial 2', 'descarte', '0.00', '17/01/2024'],
    ['HP Q2612A', 'CLI004', 'Ana Oliveira', 'Filial 3', 'garantia', '0.00', '18/01/2024'],
    ['', '', '', '', '', '', '']
  ];
  
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(data);
  
  ws['!cols'] = [
    {wch: 15}, {wch: 15}, {wch: 15}, {wch: 15}, {wch: 15}, {wch: 15}, {wch: 12}
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
  
  // Show progress container
  document.getElementById('importProgress').style.display = 'block';
  document.getElementById('importSubmitBtn').disabled = true;
  document.getElementById('importCancelBtn').disabled = true;
  
  // Read and process Excel file
  const reader = new FileReader();
  reader.onload = function(e) {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, {type: 'array'});
      const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
      const jsonData = XLSX.utils.sheet_to_json(firstSheet, {header: 1});
      
      // Remove header row and empty rows
      const rows = jsonData.slice(1).filter(row => {
        // Check if row has at least one non-empty cell
        return row.some(cell => cell !== undefined && cell !== null && String(cell).trim() !== '');
      });
      
      // Debug: Always log rows for troubleshooting (temporarily)
      addDebugLog(`📊 Total de linhas na planilha: ${jsonData.length}`);
      addDebugLog(`📊 Linhas após filtro: ${rows.length}`);
      jsonData.forEach((row, index) => {
        if (index === 0) {
          addDebugLog(`📋 HEADER: ${JSON.stringify(row)}`);
        } else {
          addDebugLog(`📋 Linha ${index}: ${JSON.stringify(row)} - Filtrada: ${rows.includes(row) ? 'NÃO' : 'SIM'}`);
        }
      });
      
      if (rows.length === 0) {
        alert('Nenhum dado encontrado na planilha.');
        resetImportModal();
        return;
      }
      
      // Process rows with batch import
      processBatchImport(file);
      
    } catch (error) {
      alert('Erro ao ler arquivo: ' + error.message);
      resetImportModal();
    }
  };
  
  reader.readAsArrayBuffer(file);
}

function processBatchImport(file) {
  // Show progress
  document.getElementById('importProgressBar').style.width = '50%';
  document.getElementById('importStatus').textContent = 'Enviando arquivo para o servidor...';
  
  // Create FormData for file upload
  const formData = new FormData();
  formData.append('import_file', file);
  
  // Send to server
  fetch('/toners/retornados/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    document.getElementById('importProgressBar').style.width = '100%';
    
    if (result.success) {
      document.getElementById('importStatus').textContent = 'Importação concluída!';
      setTimeout(() => {
        alert(`Importação concluída!\n${result.imported} registros importados com sucesso.`);
        closeImportModal();
        location.reload();
      }, 1000);
    } else {
      document.getElementById('importStatus').textContent = 'Erro na importação';
      alert('Erro na importação: ' + result.message);
      resetImportModal();
    }
  })
  .catch(error => {
    document.getElementById('importStatus').textContent = 'Erro de conexão';
    alert('Erro de conexão: ' + error.message);
    resetImportModal();
  });
}

function processImportRows(rows) {
  let processed = 0;
  const total = rows.length;
  const results = [];
  let successful = 0;
  let errors = 0;
  
  async function processNextRow() {
    if (processed >= total) {
      // Import completed
      setTimeout(() => {
        let message = `Importação concluída!\n`;
        message += `${successful} registros importados com sucesso.\n`;
        if (errors > 0) {
          message += `${errors} registros com erro.`;
        }
        
        // Auto-download debug report
        downloadImportReport();
        
        alert(message + '\n\nRelatório de importação baixado automaticamente.');
        closeImportModal();
        location.reload();
      }, 2000);
      return;
    }
    
    const row = rows[processed];
    const progress = ((processed + 1) / total) * 100;
    document.getElementById('importProgressBar').style.width = progress + '%';
    document.getElementById('importStatus').textContent = `Processando linha ${processed + 1} de ${total}...`;
    
    // Prepare row data - expected format: [Modelo, Código Cliente, Usuário, Filial, Destino, Valor Recuperado, Data]
    const rowData = {
      modelo: row[0] || '',
      codigo_cliente: row[1] || '',
      usuario: row[2] || '',
      filial: row[3] || '',
      destino: row[4] || '',
      valor_calculado: parseFloat(row[5]) || 0,
      data_registro: row[6] || ''
    };
    
    addDebugLog(`📤 Enviando linha ${processed + 1}: ${JSON.stringify(rowData)}`);
    
    // Skip completely empty rows
    if (!rowData.modelo && !rowData.codigo_cliente && !rowData.usuario && !rowData.filial && !rowData.destino) {
      addDebugLog(`⏭️ Pulando linha ${processed + 1} - completamente vazia`);
      processed++;
      await new Promise(resolve => setTimeout(resolve, 50));
      await processNextRow();
      return;
    }
    
    try {
      // Send row to server
      const response = await fetch('/toners/retornados/import-row', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(rowData)
      });
      
      const data = await response.json();
      
      addDebugLog(`📥 Resposta linha ${processed + 1}: ${JSON.stringify(data)}`);
      
      const result = {
        row: processed + 1,
        data: rowData,
        response: data,
        success: data.success
      };
      
      importResults.push(result);
      
      if (data.success) {
        successful++;
        addDebugLog(`✅ Linha ${processed + 1} importada com sucesso`);
      } else {
        errors++;
        addDebugLog(`❌ Erro linha ${processed + 1}: ${data.message}`);
      }
    } catch (error) {
      errors++;
      const errorMsg = `Erro de rede linha ${processed + 1}: ${error.message}`;
      addDebugLog(`🚨 ${errorMsg}`);
      
      importResults.push({
        row: processed + 1,
        data: rowData,
        response: { success: false, message: errorMsg },
        success: false
      });
    }
    
    processed++;
    
    // Small delay for animation
    await new Promise(resolve => setTimeout(resolve, 100));
    
    // Continue with next row
    await processNextRow();
  }
  
  // Start processing
  processNextRow();
}

function resetImportModal() {
  document.getElementById('importProgress').style.display = 'none';
  document.getElementById('importSubmitBtn').disabled = false;
  document.getElementById('importCancelBtn').disabled = false;
  document.getElementById('importProgressBar').style.width = '0%';
  document.getElementById('importStatus').textContent = 'Preparando importação...';
}

// Filter and export functions already defined at the top

// Functions already defined at the top of the script

// Functions already defined at the top of the script

// Functions already assigned to window when defined above
if (typeof toggleMode === 'function') window.toggleMode = toggleMode;
if (typeof updateTonerData === 'function') window.updateTonerData = updateTonerData;
if (typeof calculatePercentage === 'function') window.calculatePercentage = calculatePercentage;
if (typeof selectDestino === 'function') window.selectDestino = selectDestino;
if (typeof updateDestinoButtons === 'function') window.updateDestinoButtons = updateDestinoButtons;
if (typeof calculateValue === 'function') window.calculateValue = calculateValue;
if (typeof showGuidance === 'function') window.showGuidance = showGuidance;
if (typeof checkAutoDiscard === 'function') window.checkAutoDiscard = checkAutoDiscard;
if (typeof loadParameters === 'function') window.loadParameters = loadParameters;
if (typeof submitRetornado === 'function') window.submitRetornado = submitRetornado;

// Simplified activity logging
function logActivity(type, message, data = {}) {
  console.log(`[${type.toUpperCase()}] ${message}`, data);
}

// Initialize session start time
window.sessionStartTime = Date.now();
logActivity('system', 'Page Loaded', { timestamp: new Date().toISOString() });

// Final check - log all available functions
console.log('🔧 FUNÇÕES DISPONÍVEIS NO WINDOW:');
console.log('excluirRetornado:', typeof window.excluirRetornado);
console.log('filterData:', typeof window.filterData);
console.log('exportToExcel:', typeof window.exportToExcel);

// Test if functions are callable
try {
  if (typeof window.excluirRetornado === 'function') {
    console.log('✅ excluirRetornado está OK');
  } else {
    console.error('❌ excluirRetornado não é uma função');
  }
} catch (e) {
  console.error('❌ Erro ao testar excluirRetornado:', e);
}
</script>
