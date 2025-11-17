<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">📊 Formulários Online</h1>
    <div class="flex items-center space-x-3">
      <a href="/nps/dashboard" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <span>Dashboard</span>
      </a>
      <button onclick="abrirModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <span>Novo Formulário</span>
      </button>
    </div>
  </div>

  <!-- Grid de Formulários -->
  <div id="formulariosGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Carregando... -->
    <div class="col-span-full text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">Carregando formulários...</p>
    </div>
  </div>
</section>

<!-- Modal Criar/Editar Formulário -->
<div id="modalFormulario" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
    <div class="p-6 border-b border-gray-200">
      <div class="flex justify-between items-center">
        <h3 id="modalTitulo" class="text-xl font-semibold text-gray-900">Novo Formulário</h3>
        <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <div class="p-6">
      <form id="formularioForm" class="space-y-6" enctype="multipart/form-data">
        <input type="hidden" id="formulario_id">
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Título do Formulário *</label>
          <input type="text" id="titulo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: Pesquisa de Satisfação - Cliente">
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
          <textarea id="descricao" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Breve descrição sobre o formulário"></textarea>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">🎨 Logo Personalizado (opcional)</label>
          <div class="mt-1 flex items-center space-x-4">
            <div id="logoPreview" class="hidden w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden">
              <img id="logoPreviewImg" src="" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div class="flex-1">
              <label class="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Selecionar Logo (PNG)
                <input type="file" id="logo" accept="image/png" class="hidden" onchange="previewLogo(this)">
              </label>
              <p class="text-xs text-gray-500 mt-2">PNG recomendado: 200x200px, máx. 500KB</p>
              <button type="button" onclick="removerLogo()" id="btnRemoverLogo" class="hidden mt-2 text-xs text-red-600 hover:text-red-700">Remover logo</button>
            </div>
          </div>
        </div>
        
        <div>
          <div class="flex justify-between items-center mb-3">
            <label class="block text-sm font-medium text-gray-700">Perguntas *</label>
            <button type="button" onclick="adicionarPergunta()" class="text-sm text-blue-600 hover:text-blue-700 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Adicionar Pergunta
            </button>
          </div>
          
          <div id="perguntasContainer" class="space-y-4">
            <!-- Perguntas serão adicionadas aqui -->
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
          <button type="button" onclick="fecharModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancelar
          </button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Salvar Formulário
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal QR Code -->
<div id="modalQRCode" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" onclick="fecharModalQR()">
  <div class="bg-white rounded-lg shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
    <div class="p-6 border-b border-gray-200">
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-900">📱 QR Code do Formulário</h3>
        <button onclick="fecharModalQR()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <div class="p-6 text-center">
      <h4 id="qrTitulo" class="text-lg font-medium text-gray-900 mb-4"></h4>
      <div id="qrcodeContainer" class="flex justify-center mb-4 bg-white p-4 rounded-lg inline-block"></div>
      <p class="text-sm text-gray-600 mb-4">Escaneie este QR Code para acessar o formulário</p>
      <button onclick="baixarQRCode()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 mx-auto transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        <span>Baixar QR Code</span>
      </button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
let perguntas = [];
let qrCodeInstance = null;

// Carregar formulários ao abrir a página
document.addEventListener('DOMContentLoaded', function() {
  carregarFormularios();
  
  // Fechar modal QR Code com tecla ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const modalQR = document.getElementById('modalQRCode');
      if (modalQR && !modalQR.classList.contains('hidden')) {
        fecharModalQR();
      }
    }
  });
});

// Carregar lista de formulários
function carregarFormularios() {
  fetch('/nps/listar', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      renderFormularios(data.formularios);
    } else {
      alert('Erro ao carregar formulários');
    }
  })
  .catch(err => {
    console.error(err);
    document.getElementById('formulariosGrid').innerHTML = '<div class="col-span-full text-center py-12 text-red-600">Erro ao carregar formulários</div>';
  });
}

// Renderizar grid de formulários
function renderFormularios(formularios) {
  const grid = document.getElementById('formulariosGrid');
  
  if (formularios.length === 0) {
    grid.innerHTML = `
      <div class="col-span-full text-center py-12">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="text-gray-600 mb-4">Nenhum formulário criado ainda</p>
        <button onclick="abrirModal()" class="text-blue-600 hover:text-blue-700 font-medium">
          Criar seu primeiro formulário
        </button>
      </div>
    `;
    return;
  }
  
  grid.innerHTML = formularios.map(f => `
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow">
      <div class="p-6">
        <div class="flex justify-between items-start mb-4">
          <h3 class="text-lg font-semibold text-gray-900">${escapeHtml(f.titulo)}</h3>
          <span class="px-2 py-1 text-xs rounded-full ${f.ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
            ${f.ativo ? '✓ Ativo' : '✗ Inativo'}
          </span>
        </div>
        
        ${f.descricao ? `<p class="text-sm text-gray-600 mb-4">${escapeHtml(f.descricao)}</p>` : ''}
        
        <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">
          <span>📝 ${f.total_respostas} respostas</span>
          <span>📅 ${formatarData(f.criado_em)}</span>
        </div>
        
        <div class="mb-4">
          <label class="text-xs text-gray-500 mb-1 block">Link Público:</label>
          <div class="flex items-center space-x-2">
            <input type="text" value="${f.link_publico}" readonly class="flex-1 text-xs px-2 py-1 border border-gray-300 rounded bg-gray-50" id="link-${f.id}">
            <button onclick="copiarLink('${f.id}')" class="text-blue-600 hover:text-blue-700" title="Copiar link">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
              </svg>
            </button>
            <button onclick="gerarQRCode('${f.id}', '${f.link_publico}', '${escapeHtml(f.titulo)}')" class="text-purple-600 hover:text-purple-700" title="Gerar QR Code">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
              </svg>
            </button>
          </div>
        </div>
        
        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
          <button onclick="verRespostas('${f.id}')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
            Ver Respostas
          </button>
          <div class="flex items-center space-x-2">
            <!-- Ícone de Chave/Cadeado para Ativo/Inativo -->
            <button onclick="toggleStatus('${f.id}')" class="p-2 ${f.ativo ? 'text-green-600 hover:text-green-700' : 'text-gray-400 hover:text-gray-600'}" title="${f.ativo ? '🔓 Formulário Aberto (clique para fechar)' : '🔒 Formulário Fechado (clique para abrir)'}">
              ${f.ativo ? `
                <!-- Cadeado Aberto -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
              ` : `
                <!-- Cadeado Fechado -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
              `}
            </button>
            
            <!-- Botão Excluir (só se não tiver respostas) -->
            <button onclick="excluirFormulario('${f.id}', ${f.total_respostas})" class="p-2 ${parseInt(f.total_respostas) === 0 ? 'text-red-600 hover:text-red-700' : 'text-gray-300 cursor-not-allowed'}" title="${parseInt(f.total_respostas) === 0 ? '🗑️ Excluir formulário' : '🔒 Não é possível excluir formulário com respostas ('+f.total_respostas+' respostas)'}" ${parseInt(f.total_respostas) > 0 ? 'disabled' : ''}>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  `).join('');
}

// Abrir modal para criar
function abrirModal() {
  document.getElementById('modalTitulo').textContent = 'Novo Formulário';
  document.getElementById('formularioForm').reset();
  document.getElementById('formulario_id').value = '';
  perguntas = [];
  document.getElementById('perguntasContainer').innerHTML = '';
  adicionarPergunta(); // Adicionar primeira pergunta automaticamente
  document.getElementById('modalFormulario').classList.remove('hidden');
}

// Fechar modal
function fecharModal() {
  document.getElementById('modalFormulario').classList.add('hidden');
}

// Adicionar pergunta
function adicionarPergunta() {
  const index = perguntas.length;
  perguntas.push({ texto: '', tipo: 'texto' });
  
  const container = document.getElementById('perguntasContainer');
  const div = document.createElement('div');
  div.className = 'border border-gray-300 rounded-lg p-4 bg-gray-50';
  div.innerHTML = `
    <div class="flex justify-between items-center mb-3">
      <span class="text-sm font-medium text-gray-700">Pergunta ${index + 1}</span>
      <button type="button" onclick="removerPergunta(${index})" class="text-red-600 hover:text-red-700 text-sm">
        Remover
      </button>
    </div>
    <input type="text" id="pergunta_${index}" placeholder="Digite a pergunta" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-2 focus:ring-2 focus:ring-blue-500" required>
    <select id="tipo_${index}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
      <option value="texto">Resposta de Texto</option>
      <option value="numero">Resposta Numérica (0-5)</option>
      <option value="multipla">Múltipla Escolha</option>
      <option value="sim_nao">Sim/Não</option>
    </select>
  `;
  container.appendChild(div);
}

// Remover pergunta
function removerPergunta(index) {
  perguntas.splice(index, 1);
  document.getElementById('perguntasContainer').children[index].remove();
  // Renumerar perguntas
  Array.from(document.getElementById('perguntasContainer').children).forEach((el, i) => {
    el.querySelector('.text-sm').textContent = `Pergunta ${i + 1}`;
  });
}

// Preview do logo
function previewLogo(input) {
  const preview = document.getElementById('logoPreview');
  const previewImg = document.getElementById('logoPreviewImg');
  const btnRemover = document.getElementById('btnRemoverLogo');
  
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      preview.classList.remove('hidden');
      btnRemover.classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Remover logo
function removerLogo() {
  document.getElementById('logo').value = '';
  document.getElementById('logoPreview').classList.add('hidden');
  document.getElementById('btnRemoverLogo').classList.add('hidden');
}

// Submeter formulário (CRIAR apenas)
document.getElementById('formularioForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const titulo = document.getElementById('titulo').value.trim();
  const descricao = document.getElementById('descricao').value.trim();
  const logoFile = document.getElementById('logo').files[0];
  
  // Coletar perguntas
  const perguntasData = [];
  Array.from(document.getElementById('perguntasContainer').children).forEach((el, i) => {
    const texto = document.getElementById(`pergunta_${i}`).value.trim();
    const tipo = document.getElementById(`tipo_${i}`).value;
    if (texto) {
      perguntasData.push({ texto, tipo });
    }
  });
  
  if (perguntasData.length === 0) {
    alert('Adicione pelo menos uma pergunta');
    return;
  }
  
  const formData = new FormData();
  formData.append('titulo', titulo);
  formData.append('descricao', descricao);
  formData.append('perguntas', JSON.stringify(perguntasData));
  if (logoFile) {
    formData.append('logo', logoFile);
  }
  
  fetch('/nps/criar', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      if (data.link_publico) {
        alert('Link público: ' + data.link_publico);
      }
      fecharModal();
      carregarFormularios();
    } else {
      alert('Erro: ' + data.message);
    }
  })
  .catch(err => alert('Erro de conexão'));
});

// Toggle status
function toggleStatus(id) {
  if (!confirm('Alterar status deste formulário?')) return;
  
  const formData = new FormData();
  formData.append('formulario_id', id);
  
  fetch('/nps/toggle-status', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      carregarFormularios();
    } else {
      alert('Erro: ' + data.message);
    }
  });
}

// Excluir formulário
function excluirFormulario(id, totalRespostas) {
  if (totalRespostas > 0) {
    alert('Não é possível excluir! Este formulário possui ' + totalRespostas + ' resposta(s).');
    return;
  }
  
  if (!confirm('Tem certeza que deseja excluir este formulário?')) return;
  
  const formData = new FormData();
  formData.append('formulario_id', id);
  
  fetch('/nps/excluir', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      carregarFormularios();
    } else {
      alert('Erro: ' + data.message);
    }
  });
}

// Ver respostas
function verRespostas(id) {
  window.location.href = '/nps/' + id + '/respostas';
}

// Copiar link para clipboard
function copiarLink(id) {
  const linkInput = document.getElementById('link-' + id);
  linkInput.select();
  document.execCommand('copy');
  alert('Link copiado!');
}

// Formatar data
function formatarData(dataStr) {
  const d = new Date(dataStr);
  return d.toLocaleDateString('pt-BR');
}

// Escape HTML
function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

// Gerar QR Code
function gerarQRCode(id, link, titulo) {
  try {
    // Verificar se biblioteca QRCode foi carregada
    if (typeof QRCode === 'undefined') {
      alert('❌ Erro: Biblioteca QR Code não carregada. Recarregue a página.');
      console.error('QRCode library not loaded');
      return;
    }
    
    // Detectar se está em iframe
    const isInIframe = window.self !== window.top;
    const targetWindow = isInIframe ? window.top : window;
    const targetDocument = targetWindow.document;
    
    // Criar ou encontrar modal no parent window
    let modalQR = targetDocument.getElementById('modalQRCodeNPS');
    
    if (!modalQR) {
      // Criar modal no parent window
      modalQR = targetDocument.createElement('div');
      modalQR.id = 'modalQRCodeNPS';
      modalQR.className = 'hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-[9999]';
      modalQR.innerHTML = `
        <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
          <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-xl font-semibold text-gray-900">📱 QR Code do Formulário</h3>
              <button onclick="window.fecharModalQRNPS()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>
          
          <div class="p-8 text-center">
            <h4 id="qrTituloNPS" class="text-lg font-medium text-gray-900 mb-4"></h4>
            <div id="qrcodeContainerNPS" class="flex justify-center mb-4 bg-gray-50 p-4 rounded-lg inline-block min-h-[256px] min-w-[256px]"></div>
            <p class="text-sm text-gray-600 mb-4">Escaneie este QR Code para acessar o formulário</p>
            <button onclick="window.baixarQRCodeNPS()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 mx-auto transition-colors shadow-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
              </svg>
              <span>Baixar QR Code</span>
            </button>
          </div>
        </div>
      `;
      
      // Adicionar CSS Tailwind se não existir
      if (!targetDocument.getElementById('tailwindCSSQR')) {
        const tailwindLink = targetDocument.createElement('link');
        tailwindLink.id = 'tailwindCSSQR';
        tailwindLink.rel = 'stylesheet';
        tailwindLink.href = 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css';
        targetDocument.head.appendChild(tailwindLink);
      }
      
      targetDocument.body.appendChild(modalQR);
      
      // Adicionar event listener para fechar com clique fora
      modalQR.addEventListener('click', function() {
        targetWindow.fecharModalQRNPS();
      });
      
      // Adicionar event listener para ESC
      targetDocument.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modalQR.classList.contains('hidden')) {
          targetWindow.fecharModalQRNPS();
        }
      });
    }
    
    const container = targetDocument.getElementById('qrcodeContainerNPS');
    const tituloEl = targetDocument.getElementById('qrTituloNPS');
    
    // Limpar completamente QR Code anterior
    container.innerHTML = '<div class="text-gray-500 animate-pulse">Gerando QR Code...</div>';
    
    // Atualizar título
    tituloEl.textContent = titulo;
    
    // Abrir modal primeiro
    modalQR.classList.remove('hidden');
    targetDocument.body.style.overflow = 'hidden';
    
    // Aguardar um momento para o modal renderizar
    setTimeout(() => {
      // Limpar loading
      container.innerHTML = '';
      
      // Destruir instância anterior se existir
      if (targetWindow.qrCodeInstanceNPS) {
        targetWindow.qrCodeInstanceNPS.clear();
        targetWindow.qrCodeInstanceNPS = null;
      }
      
      // Carregar biblioteca QRCode no parent se necessário
      if (typeof targetWindow.QRCode === 'undefined') {
        const script = targetDocument.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
        script.onload = function() {
          // Gerar QR Code após carregar biblioteca
          targetWindow.qrCodeInstanceNPS = new targetWindow.QRCode(container, {
            text: link,
            width: 256,
            height: 256,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: targetWindow.QRCode.CorrectLevel.H
          });
          console.log('✅ QR Code gerado com sucesso (biblioteca carregada)');
        };
        targetDocument.head.appendChild(script);
      } else {
        // Gerar novo QR Code
        targetWindow.qrCodeInstanceNPS = new targetWindow.QRCode(container, {
          text: link,
          width: 256,
          height: 256,
          colorDark: '#000000',
          colorLight: '#ffffff',
          correctLevel: targetWindow.QRCode.CorrectLevel.H
        });
        console.log('✅ QR Code gerado com sucesso');
      }
    }, 100);
    
  } catch (error) {
    console.error('Erro ao gerar QR Code:', error);
    alert('❌ Erro ao gerar QR Code: ' + error.message);
  }
}

// Fechar modal QR Code (antiga - mantida para compatibilidade)
function fecharModalQR() {
  const modal = document.getElementById('modalQRCode');
  if (modal) {
    modal.classList.add('hidden');
    const container = document.getElementById('qrcodeContainer');
    if (container) {
      container.innerHTML = '';
    }
    if (qrCodeInstance) {
      try {
        qrCodeInstance.clear();
      } catch (e) {
        console.log('QR Code já foi limpo');
      }
      qrCodeInstance = null;
    }
  }
}

// Fechar modal QR Code no parent window (NOVA)
window.fecharModalQRNPS = function() {
  const isInIframe = window.self !== window.top;
  const targetWindow = isInIframe ? window.top : window;
  const targetDocument = targetWindow.document;
  
  const modal = targetDocument.getElementById('modalQRCodeNPS');
  if (modal) {
    modal.classList.add('hidden');
    targetDocument.body.style.overflow = '';
    
    // Limpar QR Code ao fechar
    const container = targetDocument.getElementById('qrcodeContainerNPS');
    if (container) {
      container.innerHTML = '';
    }
    
    // Destruir instância
    if (targetWindow.qrCodeInstanceNPS) {
      try {
        targetWindow.qrCodeInstanceNPS.clear();
      } catch (e) {
        console.log('QR Code já foi limpo');
      }
      targetWindow.qrCodeInstanceNPS = null;
    }
  }
};

// Baixar QR Code como PNG (antiga - mantida para compatibilidade)
function baixarQRCode() {
  const canvas = document.querySelector('#qrcodeContainer canvas');
  if (canvas) {
    const link = document.createElement('a');
    link.download = 'qrcode-formulario-nps.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
  }
}

// Baixar QR Code como PNG no parent window (NOVA)
window.baixarQRCodeNPS = function() {
  const isInIframe = window.self !== window.top;
  const targetWindow = isInIframe ? window.top : window;
  const targetDocument = targetWindow.document;
  
  const canvas = targetDocument.querySelector('#qrcodeContainerNPS canvas');
  if (canvas) {
    const link = targetDocument.createElement('a');
    link.download = 'qrcode-formulario-nps.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
    console.log('✅ QR Code baixado com sucesso');
  } else {
    alert('❌ Erro: QR Code não encontrado para download');
  }
};
</script>
