<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Cadastro de Toners</h1>
    <button onclick="openImportModal()" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 flex items-center gap-2">
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
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-96">
    <h3 class="text-lg font-semibold mb-4">Importar Toners</h3>
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-2">Selecione o arquivo Excel:</label>
        <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" class="w-full border rounded px-3 py-2">
      </div>
      
      <!-- Progress Bar (hidden by default) -->
      <div id="progressContainer" class="hidden">
        <div class="mb-2">
          <div class="flex justify-between text-sm">
            <span>Progresso da Importação</span>
            <span id="progressText">0%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
          </div>
        </div>
        <div id="importStatus" class="text-sm text-gray-600"></div>
      </div>
      
      <div class="flex justify-between">
        <button onclick="downloadTemplate()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
          Baixar Template
        </button>
        <div class="space-x-2">
          <button id="cancelBtn" onclick="closeImportModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
            Cancelar
          </button>
          <button id="importBtn" onclick="importExcel()" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            Importar
          </button>
        </div>
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

// Modal functions
function openImportModal() {
  document.getElementById('importModal').classList.remove('hidden');
}

function closeImportModal() {
  document.getElementById('importModal').classList.add('hidden');
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
  const fileInput = document.getElementById('excelFileInput');
  const file = fileInput.files[0];
  
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
