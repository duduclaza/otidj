<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Cadastro de Toners</h1>
    <button onclick="openImportModal()" class="px-4 py-2 rounded-lg bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700 flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200 font-medium">
      <span>📊</span>
      Importar
    </button>
  </div>
  
  <!-- Formulário de Cadastro -->
  <div class="bg-white border rounded-lg p-6">
    <h2 class="text-lg font-medium mb-4">Cadastrar Novo Toner</h2>
    <form method="post" action="/toners/cadastro" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
        <input type="text" name="modelo" placeholder="Ex: HP CF280A" class="w-full border rounded px-3 py-2" required>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Peso Cheio (g) *</label>
        <input type="number" step="0.01" name="peso_cheio" placeholder="Ex: 850.50" class="w-full border rounded px-3 py-2" required onchange="calcularCampos()">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Peso Vazio (g) *</label>
        <input type="number" step="0.01" name="peso_vazio" placeholder="Ex: 120.30" class="w-full border rounded px-3 py-2" required onchange="calcularCampos()">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gramatura (g)</label>
        <input type="number" step="0.01" name="gramatura" placeholder="Calculado automaticamente" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Capacidade de Folhas *</label>
        <input type="number" name="capacidade_folhas" placeholder="Ex: 2700" class="w-full border rounded px-3 py-2" required onchange="calcularCampos()">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Preço do Toner (R$) *</label>
        <input type="number" step="0.01" name="preco_toner" placeholder="Ex: 89.90" class="w-full border rounded px-3 py-2" required onchange="calcularCampos()">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gramatura por Folha (g)</label>
        <input type="number" step="0.0001" name="gramatura_por_folha" placeholder="Calculado automaticamente" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Custo por Folha (R$)</label>
        <input type="number" step="0.0001" name="custo_por_folha" placeholder="Calculado automaticamente" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Cor *</label>
        <select name="cor" class="w-full border rounded px-3 py-2" required>
          <option value="">Selecione a cor</option>
          <option value="Yellow">Yellow</option>
          <option value="Magenta">Magenta</option>
          <option value="Cyan">Cyan</option>
          <option value="Black">Black</option>
        </select>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
        <select name="tipo" class="w-full border rounded px-3 py-2" required>
          <option value="">Selecione o tipo</option>
          <option value="Original">Original</option>
          <option value="Compativel">Compatível</option>
          <option value="Remanufaturado">Remanufaturado</option>
        </select>
      </div>
      
      <div class="md:col-span-2 lg:col-span-3">
        <button type="submit" class="px-6 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Salvar Toner</button>
      </div>
    </form>
  </div>

  <!-- Lista/Grid -->
  <div class="bg-white border rounded-lg">
    <div class="px-4 py-3 border-b flex justify-between items-center">
      <h2 class="text-lg font-medium">Toners Cadastrados</h2>
      <button onclick="exportToExcel()" class="px-3 py-1 text-sm rounded bg-gray-600 text-white hover:bg-gray-700">
        Exportar Excel
      </button>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Modelo</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Peso Cheio</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Peso Vazio</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Gramatura</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Cap. Folhas</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Preço</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Gram/Folha</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Custo/Folha</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Cor</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Tipo</th>
            <th class="px-3 py-2 text-right font-medium text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($toners)): ?>
            <tr>
              <td colspan="11" class="px-4 py-8 text-center text-gray-500">Nenhum toner cadastrado</td>
            </tr>
          <?php else: ?>
            <?php foreach ($toners as $t): ?>
              <tr>
                <td class="px-3 py-2">
                  <span class="edit-display-modelo-<?= $t['id'] ?>"><?= e($t['modelo']) ?></span>
                  <input type="text" class="edit-input-modelo-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-full text-xs" value="<?= e($t['modelo']) ?>">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-peso_cheio-<?= $t['id'] ?>"><?= number_format($t['peso_cheio'], 2) ?>g</span>
                  <input type="number" step="0.01" class="edit-input-peso_cheio-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['peso_cheio'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-peso_vazio-<?= $t['id'] ?>"><?= number_format($t['peso_vazio'], 2) ?>g</span>
                  <input type="number" step="0.01" class="edit-input-peso_vazio-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['peso_vazio'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-gramatura-<?= $t['id'] ?>"><?= number_format($t['gramatura'], 2) ?>g</span>
                  <input type="number" step="0.01" class="edit-input-gramatura-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs bg-gray-100" readonly>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-capacidade_folhas-<?= $t['id'] ?>"><?= number_format($t['capacidade_folhas']) ?></span>
                  <input type="number" class="edit-input-capacidade_folhas-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['capacidade_folhas'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-preco_toner-<?= $t['id'] ?>">R$ <?= number_format($t['preco_toner'], 2, ',', '.') ?></span>
                  <input type="number" step="0.01" class="edit-input-preco_toner-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['preco_toner'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-gramatura_por_folha-<?= $t['id'] ?>"><?= number_format($t['gramatura_por_folha'], 4) ?>g</span>
                  <input type="number" step="0.0001" class="edit-input-gramatura_por_folha-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs bg-gray-100" readonly>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-custo_por_folha-<?= $t['id'] ?>">R$ <?= number_format($t['custo_por_folha'], 4, ',', '.') ?></span>
                  <input type="number" step="0.0001" class="edit-input-custo_por_folha-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs bg-gray-100" readonly>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-cor-<?= $t['id'] ?>"><?= e($t['cor']) ?></span>
                  <select class="edit-input-cor-<?= $t['id'] ?> border rounded px-2 py-1 hidden text-xs">
                    <option value="Yellow" <?= $t['cor'] === 'Yellow' ? 'selected' : '' ?>>Yellow</option>
                    <option value="Magenta" <?= $t['cor'] === 'Magenta' ? 'selected' : '' ?>>Magenta</option>
                    <option value="Cyan" <?= $t['cor'] === 'Cyan' ? 'selected' : '' ?>>Cyan</option>
                    <option value="Black" <?= $t['cor'] === 'Black' ? 'selected' : '' ?>>Black</option>
                  </select>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-tipo-<?= $t['id'] ?>"><?= e($t['tipo']) ?></span>
                  <select class="edit-input-tipo-<?= $t['id'] ?> border rounded px-2 py-1 hidden text-xs">
                    <option value="Original" <?= $t['tipo'] === 'Original' ? 'selected' : '' ?>>Original</option>
                    <option value="Compativel" <?= $t['tipo'] === 'Compativel' ? 'selected' : '' ?>>Compatível</option>
                    <option value="Remanufaturado" <?= $t['tipo'] === 'Remanufaturado' ? 'selected' : '' ?>>Remanufaturado</option>
                  </select>
                </td>
                <td class="px-3 py-2 text-right space-x-1">
                  <button onclick="editToner(<?= $t['id'] ?>)" class="edit-btn-<?= $t['id'] ?> text-blue-600 hover:text-blue-800 text-xs">Editar</button>
                  <button onclick="saveToner(<?= $t['id'] ?>)" class="save-btn-<?= $t['id'] ?> text-green-600 hover:text-green-800 text-xs hidden">Salvar</button>
                  <button onclick="cancelEditToner(<?= $t['id'] ?>)" class="cancel-btn-<?= $t['id'] ?> text-gray-600 hover:text-gray-800 text-xs hidden">Cancelar</button>
                  <button onclick="deleteToner(<?= $t['id'] ?>)" class="text-red-600 hover:text-red-800 text-xs">Excluir</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4" style="z-index: 9999;">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white rounded-t-lg">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">📊 Importar Toners</h3>
            <p class="text-sm text-gray-600 mt-1">Faça upload de um arquivo Excel ou CSV com os dados dos toners</p>
          </div>
        </div>
        <!-- Close Button -->
        <button onclick="console.log('X clicado!'); event.stopPropagation(); closeImportModal();" class="flex-shrink-0 w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center transition-colors duration-200 group">
          <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Content -->
    <div class="px-6 py-4 space-y-4">
      <!-- File Input -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-3">
          📁 Selecione o arquivo Excel ou CSV:
        </label>
        <div class="relative group">
          <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" 
                 class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-4 text-sm focus:ring-3 focus:ring-blue-200 focus:border-blue-400 hover:border-gray-400 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
          <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
            </svg>
          </div>
        </div>
        <div class="flex items-center mt-2 text-xs text-gray-500">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Formatos aceitos: <span class="font-medium">.xlsx, .xls, .csv</span> • Tamanho máximo: <span class="font-medium">10MB</span>
        </div>
      </div>
      
      <!-- Progress Bar (hidden by default) -->
      <div id="progressContainer" class="hidden">
        <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-blue-200 rounded-xl p-4 mb-4">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
              <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-2"></div>
              <span class="text-sm font-semibold text-gray-700">⚡ Progresso da Importação</span>
            </div>
            <span id="progressText" class="text-sm font-bold text-blue-600">0%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
            <div id="progressBar" class="bg-gradient-to-r from-blue-500 via-blue-600 to-green-500 h-4 rounded-full transition-all duration-500 ease-out shadow-sm" style="width: 0%"></div>
          </div>
          <div id="importStatus" class="text-sm text-gray-700 bg-white rounded-lg p-3 mt-3 border border-gray-200 shadow-sm">
            Preparando importação...
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
      <!-- Template Download -->
      <div class="mb-3">
        <button onclick="downloadTemplate()" 
                class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-700 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg hover:from-blue-100 hover:to-blue-200 hover:border-blue-300 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200 shadow-sm hover:shadow">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          📥 Baixar Template
        </button>
      </div>
      
      <!-- Import Button -->
      <div>
        <button id="importBtn" onclick="importExcel()" 
                class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-green-600 border border-green-500 rounded-lg hover:from-green-600 hover:to-green-700 hover:border-green-600 focus:ring-2 focus:ring-green-200 focus:ring-opacity-50 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-md">
          <span class="flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            📤 Importar Dados
          </span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Cálculos automáticos no formulário
function calcularCampos() {
  const pesocheio = parseFloat(document.querySelector('input[name="peso_cheio"]').value) || 0;
  const pesovazio = parseFloat(document.querySelector('input[name="peso_vazio"]').value) || 0;
  const capacidade = parseInt(document.querySelector('input[name="capacidade_folhas"]').value) || 0;
  const preco = parseFloat(document.querySelector('input[name="preco_toner"]').value) || 0;
  
  const gramatura = pesocheio - pesovazio;
  document.querySelector('input[name="gramatura"]').value = gramatura.toFixed(2);
  
  if (capacidade > 0) {
    const gramaturaFolha = gramatura / capacidade;
    document.querySelector('input[name="gramatura_por_folha"]').value = gramaturaFolha.toFixed(4);
    
    const custoFolha = preco / capacidade;
    document.querySelector('input[name="custo_por_folha"]').value = custoFolha.toFixed(4);
  }
}

// Cálculos na edição
function calcularEdicao(id) {
  const pesocheio = parseFloat(document.querySelector('.edit-input-peso_cheio-' + id).value) || 0;
  const pesovazio = parseFloat(document.querySelector('.edit-input-peso_vazio-' + id).value) || 0;
  const capacidade = parseInt(document.querySelector('.edit-input-capacidade_folhas-' + id).value) || 0;
  const preco = parseFloat(document.querySelector('.edit-input-preco_toner-' + id).value) || 0;
  
  const gramatura = pesocheio - pesovazio;
  document.querySelector('.edit-input-gramatura-' + id).value = gramatura.toFixed(2);
  
  if (capacidade > 0) {
    const gramaturaFolha = gramatura / capacidade;
    document.querySelector('.edit-input-gramatura_por_folha-' + id).value = gramaturaFolha.toFixed(4);
    
    const custoFolha = preco / capacidade;
    document.querySelector('.edit-input-custo_por_folha-' + id).value = custoFolha.toFixed(4);
  }
}

// Edição inline
function editToner(id) {
  const fields = ['modelo', 'peso_cheio', 'peso_vazio', 'gramatura', 'capacidade_folhas', 'preco_toner', 'gramatura_por_folha', 'custo_por_folha', 'cor', 'tipo'];
  fields.forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.add('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.remove('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.add('hidden');
  document.querySelector('.save-btn-' + id).classList.remove('hidden');
  document.querySelector('.cancel-btn-' + id).classList.remove('hidden');
}

function cancelEditToner(id) {
  const fields = ['modelo', 'peso_cheio', 'peso_vazio', 'gramatura', 'capacidade_folhas', 'preco_toner', 'gramatura_por_folha', 'custo_por_folha', 'cor', 'tipo'];
  fields.forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.remove('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.add('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.remove('hidden');
  document.querySelector('.save-btn-' + id).classList.add('hidden');
  document.querySelector('.cancel-btn-' + id).classList.add('hidden');
}

function saveToner(id) {
  const modelo = document.querySelector('.edit-input-modelo-' + id).value.trim();
  const peso_cheio = document.querySelector('.edit-input-peso_cheio-' + id).value;
  const peso_vazio = document.querySelector('.edit-input-peso_vazio-' + id).value;
  const capacidade_folhas = document.querySelector('.edit-input-capacidade_folhas-' + id).value;
  const preco_toner = document.querySelector('.edit-input-preco_toner-' + id).value;
  const cor = document.querySelector('.edit-input-cor-' + id).value;
  const tipo = document.querySelector('.edit-input-tipo-' + id).value;
  
  if (!modelo || !peso_cheio || !peso_vazio || !capacidade_folhas || !preco_toner || !cor || !tipo) {
    alert('Todos os campos são obrigatórios');
    return;
  }
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/toners/cadastro/edit';
  form.innerHTML = `
    <input type="hidden" name="id" value="${id}">
    <input type="hidden" name="modelo" value="${modelo}">
    <input type="hidden" name="peso_cheio" value="${peso_cheio}">
    <input type="hidden" name="peso_vazio" value="${peso_vazio}">
    <input type="hidden" name="capacidade_folhas" value="${capacidade_folhas}">
    <input type="hidden" name="preco_toner" value="${preco_toner}">
    <input type="hidden" name="cor" value="${cor}">
    <input type="hidden" name="tipo" value="${tipo}">
  `;
  document.body.appendChild(form);
  form.submit();
}

function deleteToner(id) {
  if (!confirm('Tem certeza que deseja excluir este toner?')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/toners/cadastro/delete';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
  document.body.appendChild(form);
  form.submit();
}

// Função de teste
function testModal() {
  alert('Teste de JavaScript funcionando!');
  console.log('Teste de JavaScript funcionando!');
  
  // Criar um modal de teste simples
  const testDiv = document.createElement('div');
  testDiv.innerHTML = `
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 99999; display: flex; align-items: center; justify-content: center;">
      <div style="background: white; padding: 20px; border-radius: 8px; max-width: 400px;">
        <h3>Modal de Teste</h3>
        <p>Se você está vendo isso, o JavaScript está funcionando!</p>
        <button onclick="this.closest('div').parentElement.remove()" style="background: #dc2626; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">Fechar</button>
      </div>
    </div>
  `;
  document.body.appendChild(testDiv);
}

// Função para forçar modal manualmente (use no Console se necessário)
function forceShowModal() {
  const modal = document.getElementById('importModal');
  if (modal) {
    // Remover todas as classes
    modal.className = '';
    
    // Aplicar estilos brutalmente
    modal.setAttribute('style', `
      display: flex !important;
      position: fixed !important;
      top: 0px !important;
      left: 0px !important;
      width: 100vw !important;
      height: 100vh !important;
      z-index: 999999 !important;
      background: rgba(255, 0, 0, 0.9) !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 16px !important;
      visibility: visible !important;
      opacity: 1 !important;
    `);
    
    console.log('Modal forçado a aparecer com fundo vermelho!');
    return true;
  }
  return false;
}

// Modal functions
function openImportModal() {
  const modal = document.getElementById('importModal');
  if (modal) {
    // Remover hidden e forçar estilos necessários
    modal.classList.remove('hidden');
    modal.style.cssText = `
      display: flex !important;
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100vw !important;
      height: 100vh !important;
      z-index: 99999 !important;
      background-color: rgba(0, 0, 0, 0.8) !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 16px !important;
      visibility: visible !important;
      opacity: 1 !important;
    `;
    
    // Garantir que o conteúdo interno seja visível
    const modalContent = modal.querySelector('.bg-white');
    if (modalContent) {
      modalContent.style.cssText = `
        background: white !important;
        border-radius: 8px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        width: 100% !important;
        max-width: 28rem !important;
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 100000 !important;
        position: relative !important;
      `;
    }
    
    // Adicionar evento de clique no overlay para fechar
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeImportModal();
      }
    });
    
  } else {
    // Fallback: criar modal dinamicamente
    createDynamicModal();
  }
}

// Função para criar modal dinamicamente
function createDynamicModal() {
  // Remover modal existente se houver
  const existingModal = document.getElementById('dynamicImportModal');
  if (existingModal) {
    existingModal.remove();
  }
  
  const modalHTML = `
    <div id="dynamicImportModal" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.8); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px;">
      <div style="background: white; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); width: 100%; max-width: 28rem;" onclick="event.stopPropagation()">
        <!-- Header -->
        <div style="padding: 24px 24px 16px 24px; border-bottom: 1px solid #e5e7eb;">
          <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Importar Toners</h3>
        </div>
        
        <!-- Content -->
        <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
          <!-- File Input -->
          <div>
            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">
              Selecione o arquivo Excel:
            </label>
            <input type="file" id="dynamicExcelFileInput" accept=".xlsx,.xls,.csv" 
                   style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 12px; font-size: 14px;">
            <p style="font-size: 12px; color: #6b7280; margin-top: 4px; margin-bottom: 0;">Formatos aceitos: .xlsx, .xls, .csv</p>
          </div>
          
          <!-- Progress Container -->
          <div id="dynamicProgressContainer" style="display: none;">
            <div style="margin-bottom: 12px;">
              <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">
                <span>Progresso da Importação</span>
                <span id="dynamicProgressText">0%</span>
              </div>
              <div style="width: 100%; background: #e5e7eb; border-radius: 9999px; height: 12px;">
                <div id="dynamicProgressBar" style="background: linear-gradient(to right, #3b82f6, #2563eb); height: 12px; border-radius: 9999px; transition: all 0.5s ease-out; width: 0%;"></div>
              </div>
            </div>
            <div id="dynamicImportStatus" style="font-size: 14px; color: #4b5563; background: #f9fafb; border-radius: 8px; padding: 8px;"></div>
          </div>
        </div>
        
        <!-- Footer -->
        <div style="padding: 16px 24px 24px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb; border-radius: 0 0 8px 8px;">
          <!-- Template Download -->
          <div style="margin-bottom: 12px;">
            <button onclick="downloadTemplate()" 
                    style="width: 100%; display: flex; align-items: center; justify-content: center; padding: 8px 16px; font-size: 14px; font-weight: 500; color: #1d4ed8; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;">
              📥 Baixar Template Excel
            </button>
          </div>
          
          <!-- Action Buttons -->
          <div style="display: flex; gap: 12px;">
            <button onclick="closeDynamicModal()" 
                    style="flex: 1; padding: 8px 16px; font-size: 14px; font-weight: 500; color: #374151; background: white; border: 1px solid #d1d5db; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;">
              Cancelar
            </button>
            <button onclick="importDynamicExcel()" 
                    style="flex: 1; padding: 8px 16px; font-size: 14px; font-weight: 500; color: white; background: #16a34a; border: 1px solid transparent; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;">
              📤 Importar Dados
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  // Adicionar event listener para fechar ao clicar no overlay
  document.getElementById('dynamicImportModal').addEventListener('click', closeDynamicModal);
}

// Funções para o modal dinâmico
function closeDynamicModal() {
  const modal = document.getElementById('dynamicImportModal');
  if (modal) {
    modal.remove();
  }
}

function importDynamicExcel() {
  const fileInput = document.getElementById('dynamicExcelFileInput');
  const file = fileInput.files[0];
  
  if (!file) {
    alert('Por favor, selecione um arquivo Excel.');
    return;
  }
  
  // Usar a mesma lógica de importação, mas com IDs dinâmicos
  document.getElementById('dynamicProgressContainer').style.display = 'block';
  
  const formData = new FormData();
  formData.append('excel_file', file);
  
  updateDynamicProgress(10, 'Enviando arquivo...');
  
  fetch('/toners/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      updateDynamicProgress(100, `Concluído! ${result.imported} registros importados`);
      setTimeout(() => {
        closeDynamicModal();
        alert('Importação concluída com sucesso!');
        location.reload();
      }, 2000);
    } else {
      alert('Erro na importação: ' + result.message);
      document.getElementById('dynamicProgressContainer').style.display = 'none';
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
    document.getElementById('dynamicProgressContainer').style.display = 'none';
  });
}

function updateDynamicProgress(percentage, status) {
  document.getElementById('dynamicProgressBar').style.width = percentage + '%';
  document.getElementById('dynamicProgressText').textContent = percentage + '%';
  document.getElementById('dynamicImportStatus').textContent = status;
}

function closeImportModal() {
  console.log('Fechando modal...');
  const modal = document.getElementById('importModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
    console.log('Modal fechado com sucesso');
  } else {
    console.log('Modal não encontrado');
  }
  
  // Fechar modal dinâmico também se existir
  const dynamicModal = document.getElementById('dynamicImportModal');
  if (dynamicModal) {
    dynamicModal.remove();
    console.log('Modal dinâmico removido');
  }
}

function downloadTemplate() {
  // Create Excel template with proper structure
  const data = [
    ['Modelo', 'Peso Cheio (g)', 'Peso Vazio (g)', 'Capacidade de Folhas', 'Preço do Toner (R$)', 'Cor', 'Tipo'],
    ['HP CF280A', '850.50', '120.30', '2700', '89.90', 'Black', 'Original'],
    ['HP CE285A', '720.00', '110.50', '1600', '65.00', 'Black', 'Compativel'],
    ['', '', '', '', '', '', '']
  ];
  
  // Create workbook
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(data);
  
  // Set column widths
  ws['!cols'] = [
    {wch: 15}, // Modelo
    {wch: 15}, // Peso Cheio
    {wch: 15}, // Peso Vazio
    {wch: 18}, // Capacidade
    {wch: 18}, // Preço
    {wch: 12}, // Cor
    {wch: 15}  // Tipo
  ];
  
  // Style header row
  const headerStyle = {
    font: { bold: true, color: { rgb: "FFFFFF" } },
    fill: { fgColor: { rgb: "4F46E5" } },
    alignment: { horizontal: "center" }
  };
  
  // Apply header styling
  for (let col = 0; col < 7; col++) {
    const cellRef = XLSX.utils.encode_cell({ r: 0, c: col });
    if (!ws[cellRef]) ws[cellRef] = {};
    ws[cellRef].s = headerStyle;
  }
  
  // Add data validation for Cor column (F)
  ws['!dataValidation'] = {
    F2: { type: 'list', formula1: '"Yellow,Magenta,Cyan,Black"' },
    F3: { type: 'list', formula1: '"Yellow,Magenta,Cyan,Black"' },
    F4: { type: 'list', formula1: '"Yellow,Magenta,Cyan,Black"' }
  };
  
  // Add data validation for Tipo column (G)
  ws['!dataValidation'] = {
    ...ws['!dataValidation'],
    G2: { type: 'list', formula1: '"Original,Compativel,Remanufaturado"' },
    G3: { type: 'list', formula1: '"Original,Compativel,Remanufaturado"' },
    G4: { type: 'list', formula1: '"Original,Compativel,Remanufaturado"' }
  };
  
  XLSX.utils.book_append_sheet(wb, ws, "Template Toners");
  
  // Download file
  XLSX.writeFile(wb, 'template_toners.xlsx');
}

function importExcel() {
  console.log('Iniciando importação...');
  const fileInput = document.getElementById('excelFileInput');
  const file = fileInput.files[0];
  
  console.log('Arquivo selecionado:', file);
  
  if (!file) {
    alert('Por favor, selecione um arquivo Excel.');
    return;
  }
  
  // Show progress container and hide buttons
  document.getElementById('progressContainer').classList.remove('hidden');
  document.getElementById('importBtn').disabled = true;
  document.getElementById('cancelBtn').disabled = true;
  
  // Read Excel file
  const reader = new FileReader();
  reader.onload = function(e) {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
      const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
      
      if (jsonData.length <= 1) {
        throw new Error('Arquivo vazio ou sem dados');
      }
      
      // Process data with progress simulation
      processImportData(jsonData);
      
    } catch (error) {
      showImportError('Erro ao ler arquivo: ' + error.message);
    }
  };
  
  reader.onerror = function() {
    showImportError('Erro ao ler o arquivo');
  };
  
  reader.readAsArrayBuffer(file);
}

function processImportData(data) {
  const totalRows = data.length - 1; // Exclude header
  let currentRow = 0;
  
  // Simulate progress with actual data processing
  const processRow = () => {
    if (currentRow >= totalRows) {
      // All rows processed, send to server
      sendDataToServer(data);
      return;
    }
    
    currentRow++;
    const progress = Math.round((currentRow / totalRows) * 50); // First 50% for reading
    updateProgress(progress, `Processando linha ${currentRow} de ${totalRows}...`);
    
    setTimeout(processRow, 50); // Small delay for visual effect
  };
  
  processRow();
}

function sendDataToServer(data) {
  updateProgress(60, 'Enviando dados para o servidor...');
  
  const formData = new FormData();
  
  // Convert data to CSV format for server processing
  const csvContent = data.map(row => row.join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv' });
  formData.append('excel_file', blob, 'import.csv');
  
  fetch('/toners/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      // Simulate final progress steps
      updateProgress(80, 'Validando dados...');
      setTimeout(() => {
        updateProgress(90, 'Inserindo no banco de dados...');
        setTimeout(() => {
          updateProgress(100, `Concluído! ${result.imported} registros importados`);
          setTimeout(() => {
            closeImportModal();
            showSuccessMessage(result.message);
            location.reload(); // Refresh to show new data
          }, 1500);
        }, 500);
      }, 500);
    } else {
      showImportError(result.message);
    }
  })
  .catch(error => {
    showImportError('Erro de conexão: ' + error.message);
  });
}

function updateProgress(percentage, status) {
  document.getElementById('progressBar').style.width = percentage + '%';
  document.getElementById('progressText').textContent = percentage + '%';
  document.getElementById('importStatus').textContent = status;
}

function showImportError(message) {
  document.getElementById('progressContainer').classList.add('hidden');
  document.getElementById('importBtn').disabled = false;
  document.getElementById('cancelBtn').disabled = false;
  alert('Erro na importação: ' + message);
}

function showSuccessMessage(message) {
  // Create and show success notification
  const notification = document.createElement('div');
  notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
  notification.textContent = message;
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.remove();
  }, 5000);
}

function exportToExcel() {
  alert('Funcionalidade de exportação será implementada em breve.');
}
</script>
