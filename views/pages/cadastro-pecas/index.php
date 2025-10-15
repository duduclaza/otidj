<?php
$pecas = $pecas ?? [];
$isAdmin = $_SESSION['user_role'] === 'admin';
?>

<section class="mb-8">
  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">🔧 Cadastro de Peças</h1>
      <p class="text-gray-600 mt-1">Gerenciamento de peças cadastradas</p>
    </div>
    <div class="flex gap-3">
      <button onclick="openImportModal()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg flex items-center gap-2">
        <span>📊</span>
        Importar Peças
      </button>
      <button onclick="openFormModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
        + Nova Peça
      </button>
    </div>
  </div>

  <!-- Formulário Inline -->
  <div id="formContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-100" id="formTitle">Nova Peça</h2>
      <button onclick="closeFormModal()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="pecaForm" class="space-y-4">
      <input type="hidden" name="id" id="pecaId">
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Código de Referência *</label>
        <input type="text" name="codigo_referencia" id="codigoReferencia" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Descrição *</label>
        <textarea name="descricao" id="descricao" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <div class="flex justify-end space-x-3 pt-4">
        <button type="button" onclick="closeFormModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
          Cancelar
        </button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          💾 Salvar
        </button>
      </div>
    </form>
  </div>

  <!-- Grid -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código Referência</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criado por</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($pecas as $peca): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= $peca['id'] ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?= e($peca['codigo_referencia']) ?></td>
            <td class="px-6 py-4 text-sm"><?= e($peca['descricao']) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= e($peca['criador_nome'] ?? 'N/A') ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= date('d/m/Y', strtotime($peca['created_at'])) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
              <button onclick='editPeca(<?= json_encode($peca) ?>)' class="text-blue-600 hover:text-blue-800">
                ✏️ Editar
              </button>
              <button onclick="deletePeca(<?= $peca['id'] ?>)" class="text-red-600 hover:text-red-800">
                🗑️ Excluir
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal de Importação -->
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center p-4" style="z-index: 999999; display: none;" onclick="closeImportModal()">
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
            <h3 class="text-xl font-bold text-gray-900">📊 Importar Peças</h3>
            <p class="text-sm text-gray-600 mt-1">Faça upload de um arquivo Excel com as peças</p>
          </div>
        </div>
        <button onclick="closeImportModal()" class="flex-shrink-0 w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center transition-colors duration-200 group">
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
          📁 Selecione o arquivo Excel:
        </label>
        <div class="relative group">
          <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" 
                 class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-4 text-sm focus:ring-3 focus:ring-green-200 focus:border-green-400 hover:border-gray-400 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
        </div>
        <div class="flex items-center mt-2 text-xs text-gray-500">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Formatos: <span class="font-medium">.xlsx, .xls, .csv</span> • Máx: <span class="font-medium">10MB</span>
        </div>
      </div>
      
      <!-- Progress Bar -->
      <div id="progressContainer" class="hidden">
        <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-xl p-4">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
              <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-green-600 mr-2"></div>
              <span class="text-sm font-semibold text-gray-700">⚡ Progresso da Importação</span>
            </div>
            <span id="progressText" class="text-sm font-bold text-green-600">0%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
            <div id="progressBar" class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full transition-all duration-500" style="width: 0%"></div>
          </div>
          <div id="importStatus" class="text-sm text-gray-700 bg-white rounded-lg p-3 mt-3 border border-gray-200">
            Preparando importação...
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
      <!-- Template Download -->
      <div class="mb-3">
        <button onclick="downloadTemplatePecas()" 
                class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-700 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg hover:from-blue-100 hover:to-blue-200 hover:border-blue-300 transition-all duration-200 shadow-sm hover:shadow">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          📥 Baixar Template Excel
        </button>
      </div>
      
      <!-- Import Button -->
      <div>
        <button id="importBtn" onclick="importExcelPecas()" 
                class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-green-600 rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-md hover:shadow-lg">
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

<!-- Adicionar biblioteca XLSX -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
let isEditing = false;

function openFormModal() {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Nova Peça';
  document.getElementById('pecaForm').reset();
  document.getElementById('pecaId').value = '';
  isEditing = false;
}

function closeFormModal() {
  document.getElementById('formContainer').classList.add('hidden');
  document.getElementById('pecaForm').reset();
}

function editPeca(peca) {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Editar Peça';
  document.getElementById('pecaId').value = peca.id;
  document.getElementById('codigoReferencia').value = peca.codigo_referencia;
  document.getElementById('descricao').value = peca.descricao;
  isEditing = true;
}

async function deletePeca(id) {
  if (!confirm('Tem certeza que deseja excluir esta peça?')) return;
  
  try {
    const response = await fetch('/cadastro-pecas/delete', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}`
    });
    
    const result = await response.json();
    alert(result.message);
    
    if (result.success) {
      window.location.reload();
    }
  } catch (error) {
    alert('Erro ao excluir peça');
  }
}

document.getElementById('pecaForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const url = isEditing ? '/cadastro-pecas/update' : '/cadastro-pecas/store';
  
  try {
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    alert(result.message);
    
    if (result.success && result.redirect) {
      window.location.href = result.redirect;
    }
  } catch (error) {
    alert('Erro ao salvar peça');
  }
});

// ===== FUNÇÕES DE IMPORTAÇÃO =====

function openImportModal() {
  console.log('🔍 Tentando abrir modal de importação...');
  const modal = document.getElementById('importModal');
  console.log('Modal encontrado:', modal);
  
  if (!modal) {
    console.error('❌ Modal não encontrado no DOM!');
    alert('Erro: Modal de importação não foi encontrado. Por favor, recarregue a página.');
    return;
  }
  
  // Remover classe hidden
  modal.classList.remove('hidden');
  
  // Forçar display (sobrescrever CSS do layout)
  modal.style.display = 'flex';
  modal.style.visibility = 'visible';
  modal.style.opacity = '1';
  
  console.log('✅ Modal aberto com sucesso!');
  console.log('Display:', modal.style.display);
}

function closeImportModal() {
  const modal = document.getElementById('importModal');
  
  // Adicionar classe hidden
  modal.classList.add('hidden');
  
  // Forçar ocultação
  modal.style.display = 'none';
  modal.style.visibility = 'hidden';
  modal.style.opacity = '0';
  
  // Limpar inputs
  document.getElementById('excelFileInput').value = '';
  document.getElementById('progressContainer').classList.add('hidden');
  
  console.log('🚪 Modal fechado!');
}

function downloadTemplatePecas() {
  console.log('📥 Gerando template Excel de Peças...');
  
  // Criar dados da planilha
  const data = [
    ['TEMPLATE DE IMPORTAÇÃO DE PEÇAS - SGQ OTI DJ'],
    [],
    ['📋 INSTRUÇÕES DE PREENCHIMENTO:'],
    ['1. Preencha os dados a partir da linha 8 (abaixo dos cabeçalhos)'],
    ['2. CAMPOS OBRIGATÓRIOS: Código de Referência e Descrição'],
    ['3. Código de Referência: identificador único da peça'],
    ['4. Descrição: descrição completa e detalhada da peça'],
    [],
    ['Código de Referência *', 'Descrição *'],
    ['P-001', 'Parafuso M6 x 20mm - Aço Inox'],
    ['P-002', 'Rolamento 6200 - Alta Velocidade'],
    ['P-003', 'Correia Dentada GT2 - 6mm x 2m'],
    ['P-004', 'Engrenagem Helicoidal Z40 - Módulo 2'],
    ['P-005', 'Sensor Indutivo PNP - 8mm - 12-24V']
  ];
  
  // Criar workbook
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(data);
  
  // Larguras das colunas
  ws['!cols'] = [
    {wch: 25}, // Código
    {wch: 50}  // Descrição
  ];
  
  // Mesclar título
  ws['!merges'] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: 1 } }
  ];
  
  // Estilizar título
  if (!ws['A1'].s) ws['A1'].s = {};
  ws['A1'].s = {
    font: { bold: true, sz: 14, color: { rgb: "FFFFFF" } },
    fill: { fgColor: { rgb: "1E40AF" } },
    alignment: { horizontal: "center", vertical: "center" }
  };
  
  // Estilizar instruções
  for (let row = 2; row <= 6; row++) {
    const cellRef = XLSX.utils.encode_cell({ r: row, c: 0 });
    if (!ws[cellRef]) ws[cellRef] = { v: '', t: 's' };
    ws[cellRef].s = {
      font: { italic: true, sz: 10, color: { rgb: "374151" } },
      fill: { fgColor: { rgb: "F3F4F6" } },
      alignment: { horizontal: "left", vertical: "center" }
    };
  }
  
  // Estilizar cabeçalhos
  for (let col = 0; col < 2; col++) {
    const cellRef = XLSX.utils.encode_cell({ r: 8, c: col });
    if (!ws[cellRef]) ws[cellRef] = { v: '', t: 's' };
    ws[cellRef].s = {
      font: { bold: true, sz: 11, color: { rgb: "FFFFFF" } },
      fill: { fgColor: { rgb: "10B981" } },
      alignment: { horizontal: "center", vertical: "center" },
      border: {
        top: { style: 'thin', color: { rgb: "000000" } },
        bottom: { style: 'thin', color: { rgb: "000000" } },
        left: { style: 'thin', color: { rgb: "000000" } },
        right: { style: 'thin', color: { rgb: "000000" } }
      }
    };
  }
  
  // Estilizar exemplos
  for (let row = 9; row <= 13; row++) {
    for (let col = 0; col < 2; col++) {
      const cellRef = XLSX.utils.encode_cell({ r: row, c: col });
      if (!ws[cellRef]) continue;
      ws[cellRef].s = {
        alignment: { horizontal: "left", vertical: "center" },
        border: {
          top: { style: 'thin', color: { rgb: "E5E7EB" } },
          bottom: { style: 'thin', color: { rgb: "E5E7EB" } },
          left: { style: 'thin', color: { rgb: "E5E7EB" } },
          right: { style: 'thin', color: { rgb: "E5E7EB" } }
        }
      };
    }
  }
  
  // Adicionar aba
  XLSX.utils.book_append_sheet(wb, ws, "Cadastro de Peças");
  
  // Aba de instruções
  const instrData = [
    ['INSTRUÇÕES DETALHADAS - IMPORTAÇÃO DE PEÇAS'],
    [],
    ['CAMPOS OBRIGATÓRIOS (*)'],
    ['Campo', 'Descrição', 'Exemplo'],
    ['Código de Referência', 'Identificador único da peça', 'P-001'],
    ['Descrição', 'Descrição completa e detalhada', 'Parafuso M6 x 20mm - Aço Inox'],
    [],
    ['OBSERVAÇÕES IMPORTANTES:'],
    ['• Ambos os campos são obrigatórios'],
    ['• Código de Referência deve ser único (máx. 100 caracteres)'],
    ['• Descrição pode ser detalhada (campo de texto longo)'],
    ['• A primeira linha com dados é a linha 9 (após os cabeçalhos)'],
    ['• Linhas em branco são ignoradas automaticamente'],
    ['• Use códigos claros e padronizados para facilitar busca'],
    [],
    ['EXEMPLOS DE PREENCHIMENTO:'],
    ['P-001 | Parafuso M6 x 20mm - Aço Inox'],
    ['ENG-040 | Engrenagem Helicoidal Z40 - Módulo 2'],
    ['SENS-IND-8 | Sensor Indutivo PNP - 8mm - 12-24V']
  ];
  
  const wsInstr = XLSX.utils.aoa_to_sheet(instrData);
  wsInstr['!cols'] = [{wch: 25}, {wch: 50}, {wch: 30}];
  XLSX.utils.book_append_sheet(wb, wsInstr, "Instruções");
  
  // Download
  const fileName = `template_pecas_${new Date().toISOString().split('T')[0]}.xlsx`;
  XLSX.writeFile(wb, fileName);
  
  console.log('✅ Template gerado:', fileName);
  
  // Feedback
  const btn = event.target;
  const originalText = btn.innerHTML;
  btn.innerHTML = '✅ Template baixado!';
  btn.disabled = true;
  setTimeout(() => {
    btn.innerHTML = originalText;
    btn.disabled = false;
  }, 2000);
}

function importExcelPecas() {
  const fileInput = document.getElementById('excelFileInput');
  const file = fileInput.files[0];
  
  if (!file) {
    alert('Por favor, selecione um arquivo Excel.');
    return;
  }
  
  document.getElementById('progressContainer').classList.remove('hidden');
  document.getElementById('importBtn').disabled = true;
  
  const reader = new FileReader();
  reader.onload = function(e) {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
      const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
      
      if (jsonData.length <= 9) {
        throw new Error('Arquivo vazio ou sem dados');
      }
      
      processImportPecas(jsonData);
      
    } catch (error) {
      showImportError('Erro ao ler arquivo: ' + error.message);
    }
  };
  
  reader.onerror = function() {
    showImportError('Erro ao ler o arquivo');
  };
  
  reader.readAsArrayBuffer(file);
}

function processImportPecas(data) {
  updateProgressPecas(20, 'Processando dados...');
  
  // Pular linhas de cabeçalho (até linha 8)
  const dataRows = data.slice(9).filter(row => row && row.length >= 2 && row[0] && row[1]);
  
  if (dataRows.length === 0) {
    showImportError('Nenhum dado válido encontrado no arquivo');
    return;
  }
  
  updateProgressPecas(40, `Encontradas ${dataRows.length} peças para importar...`);
  
  // Preparar dados para envio
  const formData = new FormData();
  formData.append('pecas_data', JSON.stringify(dataRows));
  
  updateProgressPecas(60, 'Enviando dados para o servidor...');
  
  fetch('/cadastro-pecas/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      updateProgressPecas(100, `Concluído! ${result.imported} peças importadas`);
      setTimeout(() => {
        closeImportModal();
        alert('Importação concluída com sucesso!');
        window.location.reload();
      }, 1500);
    } else {
      showImportError(result.message || 'Erro ao importar peças');
    }
  })
  .catch(error => {
    showImportError('Erro de conexão: ' + error.message);
  });
}

function updateProgressPecas(percentage, status) {
  document.getElementById('progressBar').style.width = percentage + '%';
  document.getElementById('progressText').textContent = percentage + '%';
  document.getElementById('importStatus').textContent = status;
}

function showImportError(message) {
  document.getElementById('progressContainer').classList.add('hidden');
  document.getElementById('importBtn').disabled = false;
  alert('Erro na importação: ' + message);
}

// ===== DIAGNÓSTICO DE CARREGAMENTO =====
document.addEventListener('DOMContentLoaded', function() {
  console.log('🔧 [PEÇAS] Página carregada!');
  
  const modal = document.getElementById('importModal');
  console.log('🔧 [PEÇAS] Modal presente:', !!modal);
  console.log('🔧 [PEÇAS] Função openImportModal disponível:', typeof openImportModal);
  console.log('🔧 [PEÇAS] Biblioteca XLSX disponível:', typeof XLSX !== 'undefined');
  
  if (modal) {
    const computedStyle = window.getComputedStyle(modal);
    console.log('🔧 [PEÇAS] Display inicial:', computedStyle.display);
    console.log('🔧 [PEÇAS] Visibility inicial:', computedStyle.visibility);
    console.log('🔧 [PEÇAS] Z-index:', computedStyle.zIndex);
    console.log('🔧 [PEÇAS] Classes:', modal.className);
    
    // Garantir que o modal esteja oculto inicialmente
    if (computedStyle.display !== 'none') {
      console.warn('⚠️ [PEÇAS] Modal não está oculto inicialmente! Corrigindo...');
      modal.style.display = 'none';
    }
  }
});
</script>
