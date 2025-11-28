<?php
$title = 'Cadastro de Clientes - SGQ OTI';
$clientes = $clientes ?? [];
?>

<section class="space-y-6">
  <!-- Header -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
          👥 Cadastro de Clientes
        </h1>
        <p class="text-gray-600 mt-1">Cadastro simples de clientes (Código e Nome)</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button onclick="abrirModalCadastro()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Novo Cliente
        </button>
        <button onclick="abrirModalImportacao()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
          </svg>
          Importar Excel
        </button>
        <a href="/cadastros/clientes/template" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Baixar Template
        </a>
      </div>
    </div>
  </div>

  <!-- Busca -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
    <div class="flex gap-4">
      <div class="flex-1">
        <input type="text" id="searchClientes" placeholder="🔍 Buscar por código ou nome..." 
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               oninput="filtrarClientes()">
      </div>
      <div class="text-sm text-gray-500 flex items-center" id="contadorClientes">
        <?= count($clientes) ?> cliente(s) cadastrado(s)
      </div>
    </div>
  </div>

  <!-- Tabela de Clientes -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Cliente</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastrado em</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Ações</th>
          </tr>
        </thead>
        <tbody id="clientesTable" class="bg-white divide-y divide-gray-200">
          <?php if (empty($clientes)): ?>
            <tr id="emptyRow">
              <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-lg font-medium">Nenhum cliente cadastrado</p>
                <p class="text-sm mt-1">Clique em "Novo Cliente" ou "Importar Excel" para começar</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($clientes as $cliente): ?>
              <tr class="hover:bg-gray-50 cliente-row" data-codigo="<?= e($cliente['codigo']) ?>" data-nome="<?= e($cliente['nome']) ?>">
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <?= e($cliente['codigo']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= e($cliente['nome']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?= date('d/m/Y H:i', strtotime($cliente['created_at'])) ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <button onclick="editarCliente(<?= $cliente['id'] ?>, '<?= e($cliente['codigo']) ?>', '<?= addslashes(e($cliente['nome'])) ?>')" 
                          class="text-blue-600 hover:text-blue-800 mr-3" title="Editar">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button onclick="excluirCliente(<?= $cliente['id'] ?>, '<?= e($cliente['codigo']) ?>')" 
                          class="text-red-600 hover:text-red-800" title="Excluir">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal Cadastro/Edição -->
<div id="modalCadastro" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="fecharModalCadastro()"></div>
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-white flex items-center gap-2" id="modalTitulo">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Novo Cliente
          </h3>
          <button onclick="fecharModalCadastro()" class="text-white/80 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Form -->
      <form id="formCliente" onsubmit="salvarCliente(event)" class="p-6 space-y-4">
        <input type="hidden" id="clienteId" name="id">
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Código do Cliente *</label>
          <input type="text" id="clienteCodigo" name="codigo" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                 placeholder="Ex: 00001234">
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Cliente *</label>
          <input type="text" id="clienteNome" name="nome" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                 placeholder="Ex: Empresa Exemplo Ltda">
        </div>
        
        <div class="flex gap-3 pt-4">
          <button type="button" onclick="fecharModalCadastro()" 
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            Cancelar
          </button>
          <button type="submit" id="btnSalvar"
                  class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
            Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Importação -->
<div id="modalImportacao" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="fecharModalImportacao()"></div>
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
      <!-- Header -->
      <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 rounded-t-xl">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
            Importar Clientes
          </h3>
          <button onclick="fecharModalImportacao()" class="text-white/80 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Conteúdo -->
      <div class="p-6">
        <!-- Etapa 1: Upload -->
        <div id="etapaUpload">
          <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-green-400 transition-colors cursor-pointer"
               onclick="document.getElementById('arquivoExcel').click()">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="text-gray-600 font-medium">Clique para selecionar o arquivo</p>
            <p class="text-sm text-gray-500 mt-1">Excel (.xlsx, .xls) ou CSV</p>
            <input type="file" id="arquivoExcel" accept=".xlsx,.xls,.csv" class="hidden" onchange="processarArquivo(this)">
          </div>
          
          <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <h4 class="font-medium text-blue-800 mb-2">📋 Formato esperado:</h4>
            <ul class="text-sm text-blue-700 space-y-1">
              <li>• Coluna A: <strong>Código do Cliente</strong></li>
              <li>• Coluna B: <strong>Nome do Cliente</strong></li>
              <li>• Primeira linha = cabeçalho (será ignorada)</li>
            </ul>
          </div>
        </div>
        
        <!-- Etapa 2: Progresso -->
        <div id="etapaProgresso" class="hidden">
          <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
              <svg class="w-8 h-8 text-green-600 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
            <h4 class="text-lg font-medium text-gray-900">Importando clientes...</h4>
            <p class="text-sm text-gray-500 mt-1" id="statusImportacao">Processando arquivo...</p>
          </div>
          
          <!-- Barra de Progresso -->
          <div class="relative pt-1">
            <div class="flex mb-2 items-center justify-between">
              <div>
                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-600 bg-green-200" id="progressoTexto">
                  0%
                </span>
              </div>
              <div class="text-right">
                <span class="text-xs font-semibold inline-block text-green-600" id="progressoContador">
                  0 / 0
                </span>
              </div>
            </div>
            <div class="overflow-hidden h-4 mb-4 text-xs flex rounded-full bg-gray-200">
              <div id="barraProgresso" 
                   class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-green-500 to-green-600 transition-all duration-300 ease-out rounded-full"
                   style="width: 0%">
              </div>
            </div>
          </div>
        </div>
        
        <!-- Etapa 3: Resultado -->
        <div id="etapaResultado" class="hidden">
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <h4 class="text-lg font-medium text-gray-900 mb-2">Importação Concluída!</h4>
            <div id="resultadoImportacao" class="text-sm text-gray-600 space-y-1"></div>
          </div>
          
          <div class="mt-6">
            <button onclick="fecharModalImportacao(); location.reload();" 
                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
              Fechar e Atualizar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- XLSX Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
// ===== FUNÇÕES DO MODAL CADASTRO =====

function abrirModalCadastro() {
  document.getElementById('modalCadastro').classList.remove('hidden');
  document.getElementById('modalTitulo').innerHTML = `
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
    </svg>
    Novo Cliente
  `;
  document.getElementById('formCliente').reset();
  document.getElementById('clienteId').value = '';
  document.getElementById('clienteCodigo').focus();
}

function fecharModalCadastro() {
  document.getElementById('modalCadastro').classList.add('hidden');
}

function editarCliente(id, codigo, nome) {
  document.getElementById('modalCadastro').classList.remove('hidden');
  document.getElementById('modalTitulo').innerHTML = `
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
    </svg>
    Editar Cliente
  `;
  document.getElementById('clienteId').value = id;
  document.getElementById('clienteCodigo').value = codigo;
  document.getElementById('clienteNome').value = nome;
}

async function salvarCliente(event) {
  event.preventDefault();
  
  const btn = document.getElementById('btnSalvar');
  const btnTexto = btn.innerHTML;
  btn.innerHTML = '<svg class="w-5 h-5 animate-spin inline mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Salvando...';
  btn.disabled = true;
  
  const formData = new FormData(document.getElementById('formCliente'));
  const id = formData.get('id');
  const url = id ? '/cadastros/clientes/atualizar' : '/cadastros/clientes/criar';
  
  try {
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      fecharModalCadastro();
      location.reload();
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro de conexão: ' + error.message);
  } finally {
    btn.innerHTML = btnTexto;
    btn.disabled = false;
  }
}

async function excluirCliente(id, codigo) {
  if (!confirm(`Deseja realmente excluir o cliente "${codigo}"?`)) {
    return;
  }
  
  try {
    const response = await fetch('/cadastros/clientes/excluir', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${id}`
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      location.reload();
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro de conexão: ' + error.message);
  }
}

// ===== FUNÇÕES DO MODAL IMPORTAÇÃO =====

function abrirModalImportacao() {
  document.getElementById('modalImportacao').classList.remove('hidden');
  document.getElementById('etapaUpload').classList.remove('hidden');
  document.getElementById('etapaProgresso').classList.add('hidden');
  document.getElementById('etapaResultado').classList.add('hidden');
  document.getElementById('arquivoExcel').value = '';
}

function fecharModalImportacao() {
  document.getElementById('modalImportacao').classList.add('hidden');
}

function processarArquivo(input) {
  const file = input.files[0];
  if (!file) return;
  
  // Mostrar etapa de progresso
  document.getElementById('etapaUpload').classList.add('hidden');
  document.getElementById('etapaProgresso').classList.remove('hidden');
  document.getElementById('statusImportacao').textContent = 'Lendo arquivo...';
  
  const reader = new FileReader();
  
  reader.onload = function(e) {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      
      const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
      const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
      
      // Remover cabeçalho
      const rows = jsonData.slice(1).filter(row => row.length >= 2 && row[0] && row[1]);
      
      if (rows.length === 0) {
        alert('Nenhum dado válido encontrado no arquivo');
        abrirModalImportacao();
        return;
      }
      
      // Preparar dados
      const clientes = rows.map(row => ({
        codigo: String(row[0]).trim(),
        nome: String(row[1]).trim()
      }));
      
      importarClientes(clientes);
      
    } catch (error) {
      alert('Erro ao processar arquivo: ' + error.message);
      abrirModalImportacao();
    }
  };
  
  reader.readAsArrayBuffer(file);
}

async function importarClientes(clientes) {
  const total = clientes.length;
  let processados = 0;
  
  document.getElementById('statusImportacao').textContent = `Importando ${total} clientes...`;
  document.getElementById('progressoContador').textContent = `0 / ${total}`;
  
  // Animação inicial da barra
  const barra = document.getElementById('barraProgresso');
  barra.style.width = '5%';
  
  // Simular progresso enquanto envia
  const progressInterval = setInterval(() => {
    processados++;
    const percent = Math.min(Math.round((processados / total) * 90), 90);
    barra.style.width = percent + '%';
    document.getElementById('progressoTexto').textContent = percent + '%';
    document.getElementById('progressoContador').textContent = `${Math.min(processados, total)} / ${total}`;
    
    if (processados >= total) {
      clearInterval(progressInterval);
    }
  }, 50);
  
  try {
    const response = await fetch('/cadastros/clientes/importar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(clientes)
    });
    
    const result = await response.json();
    
    clearInterval(progressInterval);
    
    // Completar barra
    barra.style.width = '100%';
    document.getElementById('progressoTexto').textContent = '100%';
    document.getElementById('progressoContador').textContent = `${total} / ${total}`;
    
    setTimeout(() => {
      // Mostrar resultado
      document.getElementById('etapaProgresso').classList.add('hidden');
      document.getElementById('etapaResultado').classList.remove('hidden');
      
      if (result.success) {
        document.getElementById('resultadoImportacao').innerHTML = `
          <p class="text-green-600 font-medium">✅ ${result.importados} cliente(s) importado(s)</p>
          <p class="text-blue-600">🔄 ${result.atualizados} cliente(s) atualizado(s)</p>
          ${result.erros > 0 ? `<p class="text-red-600">❌ ${result.erros} erro(s)</p>` : ''}
        `;
      } else {
        document.getElementById('resultadoImportacao').innerHTML = `
          <p class="text-red-600">❌ Erro: ${result.message}</p>
        `;
      }
    }, 500);
    
  } catch (error) {
    clearInterval(progressInterval);
    alert('Erro na importação: ' + error.message);
    abrirModalImportacao();
  }
}

// ===== FUNÇÃO DE BUSCA =====

function filtrarClientes() {
  const search = document.getElementById('searchClientes').value.toLowerCase().trim();
  const rows = document.querySelectorAll('.cliente-row');
  let visibleCount = 0;
  
  rows.forEach(row => {
    const codigo = row.dataset.codigo.toLowerCase();
    const nome = row.dataset.nome.toLowerCase();
    
    if (codigo.includes(search) || nome.includes(search)) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  
  document.getElementById('contadorClientes').textContent = 
    `${visibleCount} de ${rows.length} cliente(s)`;
}

// Fechar modais com ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    fecharModalCadastro();
    fecharModalImportacao();
  }
});
</script>
