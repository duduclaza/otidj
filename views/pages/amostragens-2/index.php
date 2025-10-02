<?php
// Garantir que as variáveis existam
$amostragens = $amostragens ?? [];
$usuarios = $usuarios ?? [];
$filiais = $filiais ?? [];
$fornecedores = $fornecedores ?? [];
$toners = $toners ?? [];
$isAdmin = $_SESSION['user_role'] === 'admin';
?>

<section class="mb-8">
  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">🔬 Amostragens 2.0 <span class="beta-badge">BETA</span></h1>
      <p class="text-gray-600 mt-1">Controle de testes de amostragens de produtos</p>
    </div>
    <button onclick="openAmostragemModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
      + Nova Amostragem
    </button>
  </div>

  <!-- Formulário Inline (Hidden por padrão) -->
  <div id="amostragemFormContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-100">🔬 Nova Amostragem</h2>
      <button onclick="closeAmostragemModal()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="amostragemForm" action="/amostragens-2/store" method="POST" enctype="multipart/form-data" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Número da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Número da NF *</label>
          <input type="text" name="numero_nf" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Anexo da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Anexo da NF (PDF ou Foto - Máx 10MB)</label>
          <input type="file" name="anexo_nf" accept=".pdf,image/*" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200">
        </div>

        <!-- Tipo de Produto -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Tipo de Produto *</label>
          <select name="tipo_produto" id="tipoProduto" required onchange="carregarProdutos()" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <option value="">Selecione...</option>
            <option value="Toner">Toner</option>
            <option value="Peça">Peça</option>
            <option value="Máquina">Máquina</option>
          </select>
        </div>

        <!-- Código do Produto -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Código do Produto *</label>
          <div class="relative">
            <input type="text" id="buscaProduto" placeholder="Digite para buscar..." onkeyup="filtrarProdutos()" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <select name="produto_id" id="produtoSelect" required size="5" class="hidden w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 mt-2 max-h-40 overflow-y-auto">
              <option value="">Selecione o tipo de produto primeiro</option>
            </select>
          </div>
          <input type="hidden" name="codigo_produto" id="codigoProduto">
          <input type="hidden" name="nome_produto" id="nomeProduto">
        </div>

        <!-- Quantidade Recebida -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quantidade Recebida *</label>
          <input type="number" name="quantidade_recebida" min="1" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Quantidade Testada -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quantidade Testada *</label>
          <input type="number" name="quantidade_testada" min="1" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Quantidade Aprovada -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quantidade Aprovada *</label>
          <input type="number" name="quantidade_aprovada" min="0" value="0" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Quantidade Reprovada -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quantidade Reprovada *</label>
          <input type="number" name="quantidade_reprovada" min="0" value="0" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Fornecedor -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Fornecedor *</label>
          <select name="fornecedor_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <option value="">Selecione...</option>
            <?php foreach ($fornecedores as $forn): ?>
              <option value="<?= $forn['id'] ?>"><?= e($forn['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Responsáveis -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Responsáveis pelo Teste *</label>
          <select name="responsaveis[]" multiple required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500" size="4">
            <?php foreach ($usuarios as $user): ?>
              <option value="<?= $user['id'] ?>"><?= e($user['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-gray-400 mt-1">Segure Ctrl/Cmd para selecionar múltiplos</p>
        </div>

        <!-- Status Final -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Status Final *</label>
          <select name="status_final" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <option value="Pendente">Pendente</option>
            <option value="Aprovado">Aprovado</option>
            <option value="Aprovado Parcialmente">Aprovado Parcialmente</option>
            <option value="Reprovado">Reprovado</option>
          </select>
        </div>

        <!-- Evidências (Fotos) -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-200 mb-1">Evidências (Fotos - Máx 5 arquivos de 10MB cada)</label>
          <input type="file" name="evidencias[]" multiple accept="image/*" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200">
          <p class="text-xs text-gray-400 mt-1">Opcional - Máximo 5 fotos</p>
        </div>
      </div>

      <div class="flex justify-end space-x-3 pt-4">
        <button type="button" onclick="closeAmostragemModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
          Cancelar
        </button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          💾 Salvar Amostragem
        </button>
      </div>
    </form>
  </div>

  <!-- Filtros -->
  <div class="bg-white border rounded-lg p-4 mb-6">
    <h3 class="font-semibold text-gray-900 mb-4">🔍 Filtros</h3>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Código do Produto</label>
        <input type="text" name="codigo_produto" value="<?= $_GET['codigo_produto'] ?? '' ?>" placeholder="Digite o código..." class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
        <select name="user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Todos</option>
          <?php foreach ($usuarios as $user): ?>
            <option value="<?= $user['id'] ?>" <?= ($_GET['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
              <?= e($user['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Filial</label>
        <select name="filial_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Todas</option>
          <?php foreach ($filiais as $filial): ?>
            <option value="<?= $filial['id'] ?>" <?= ($_GET['filial_id'] ?? '') == $filial['id'] ? 'selected' : '' ?>>
              <?= e($filial['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
        <select name="fornecedor_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Todos</option>
          <?php foreach ($fornecedores as $forn): ?>
            <option value="<?= $forn['id'] ?>" <?= ($_GET['fornecedor_id'] ?? '') == $forn['id'] ? 'selected' : '' ?>>
              <?= e($forn['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Todos</option>
          <option value="Pendente" <?= ($_GET['status'] ?? '') == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
          <option value="Aprovado" <?= ($_GET['status'] ?? '') == 'Aprovado' ? 'selected' : '' ?>>Aprovado</option>
          <option value="Aprovado Parcialmente" <?= ($_GET['status'] ?? '') == 'Aprovado Parcialmente' ? 'selected' : '' ?>>Aprovado Parcialmente</option>
          <option value="Reprovado" <?= ($_GET['status'] ?? '') == 'Reprovado' ? 'selected' : '' ?>>Reprovado</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
        <input type="date" name="data_inicio" value="<?= $_GET['data_inicio'] ?? '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
        <input type="date" name="data_fim" value="<?= $_GET['data_fim'] ?? '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>

      <div class="flex items-end gap-1.5 col-span-1">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          Filtrar
        </button>
        <a href="/amostragens-2" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg text-center transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          Limpar
        </a>
        <button type="button" onclick="exportarExcel()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          📊 Exportar
        </button>
      </div>
    </form>
  </div>

  <!-- Grid de Amostragens -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NF</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filial</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fornecedor</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qtd Recebida</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qtd Testada</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aprovada</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reprovada</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anexo NF</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evidências</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($amostragens as $amostra): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= date('d/m/Y', strtotime($amostra['created_at'])) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <?= e($amostra['numero_nf']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['usuario_nome']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['filial_nome']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['tipo_produto']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['codigo_produto']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['fornecedor_nome']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?= $amostra['quantidade_recebida'] ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?= $amostra['quantidade_testada'] ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-600 font-semibold">
              <?= $amostra['quantidade_aprovada'] ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-semibold">
              <?= $amostra['quantidade_reprovada'] ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 py-1 text-xs font-semibold rounded-full
                <?php
                  switch($amostra['status_final']) {
                    case 'Aprovado': echo 'bg-green-100 text-green-800'; break;
                    case 'Aprovado Parcialmente': echo 'bg-yellow-100 text-yellow-800'; break;
                    case 'Reprovado': echo 'bg-red-100 text-red-800'; break;
                    default: echo 'bg-gray-100 text-gray-800';
                  }
                ?>">
                <?= e($amostra['status_final']) ?>
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?php if (!empty($amostra['anexo_nf_nome'])): ?>
                <a href="/amostragens-2/<?= $amostra['id'] ?>/download-nf" 
                   class="text-blue-600 hover:text-blue-800" 
                   title="<?= e($amostra['anexo_nf_nome']) ?>">
                  📄 Baixar
                </a>
              <?php else: ?>
                <span class="text-gray-400">-</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?php if ($amostra['total_evidencias'] > 0): ?>
                <button onclick="verEvidencias(<?= $amostra['id'] ?>)" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">
                  📷 Ver (<?= $amostra['total_evidencias'] ?>)
                </button>
              <?php else: ?>
                <span class="text-gray-400">Sem evidências</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
              <button onclick="editarAmostragem(<?= $amostra['id'] ?>)" 
                      class="text-blue-600 hover:text-blue-800">
                ✏️ Editar
              </button>
              <button onclick="excluirAmostragem(<?= $amostra['id'] ?>)" 
                      class="text-red-600 hover:text-red-800">
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

<!-- Modal de Evidências -->
<div id="evidenciasModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">📷 Evidências da Amostragem</h3>
      <button onclick="closeEvidenciasModal()" class="text-gray-500 hover:text-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <div id="evidenciasContent" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Evidências serão carregadas aqui -->
    </div>
  </div>
</div>

<style>
.beta-badge {
  background: linear-gradient(45deg, #ff6b6b, #feca57);
  color: white;
  font-size: 0.7rem;
  font-weight: bold;
  padding: 4px 8px;
  border-radius: 12px;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}
</style>

<script>
const produtosData = {
  toners: <?= json_encode($toners ?? []) ?>,
  pecas: <?= json_encode($pecas ?? []) ?>,
  maquinas: <?= json_encode($maquinas ?? []) ?>
};

function openAmostragemModal() {
  document.getElementById('amostragemFormContainer').classList.remove('hidden');
  document.getElementById('amostragemFormContainer').scrollIntoView({ behavior: 'smooth' });
}

function closeAmostragemModal() {
  document.getElementById('amostragemFormContainer').classList.add('hidden');
  document.getElementById('amostragemForm').reset();
  
  // Voltar para modo criar
  document.getElementById('amostragemForm').action = '/amostragens-2/store';
  
  // Remover campo hidden de ID se existir
  const hiddenId = document.querySelector('input[name="amostragem_id"]');
  if (hiddenId) {
    hiddenId.remove();
  }
}

function carregarProdutos() {
  const tipo = document.getElementById('tipoProduto').value;
  const select = document.getElementById('produtoSelect');
  
  select.innerHTML = '<option value="">Selecione...</option>';
  
  let produtos = [];
  
  if (tipo === 'Toner') {
    produtos = produtosData.toners;
  } else if (tipo === 'Peça') {
    produtos = produtosData.pecas;
  } else if (tipo === 'Máquina') {
    produtos = produtosData.maquinas;
  }
  
  produtos.forEach(p => {
    const option = document.createElement('option');
    option.value = p.id;
    option.textContent = `${p.codigo} - ${p.nome}`;
    option.dataset.codigo = p.codigo;
    option.dataset.nome = p.nome;
    select.appendChild(option);
  });
  
  select.classList.remove('hidden');
  
  select.onchange = function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('codigoProduto').value = selected.dataset.codigo || '';
    document.getElementById('nomeProduto').value = selected.dataset.nome || '';
  };
}

function filtrarProdutos() {
  const busca = document.getElementById('buscaProduto').value.toLowerCase();
  const select = document.getElementById('produtoSelect');
  const options = select.options;
  
  for (let i = 0; i < options.length; i++) {
    const text = options[i].textContent.toLowerCase();
    options[i].style.display = text.includes(busca) ? '' : 'none';
  }
}

// Submit do formulário
document.getElementById('amostragemForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  try {
    const response = await fetch(this.action, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      if (result.redirect) {
        window.location.href = result.redirect;
      }
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro ao enviar formulário');
  }
});

// Ver evidências
async function verEvidencias(amostragemId) {
  console.log('Buscando evidências para amostragem:', amostragemId);
  
  try {
    const response = await fetch(`/amostragens-2/${amostragemId}/evidencias`);
    const data = await response.json();
    
    console.log('Resposta do servidor:', data);
    
    const content = document.getElementById('evidenciasContent');
    
    if (data.success && data.evidencias && data.evidencias.length > 0) {
      console.log('Evidências encontradas:', data.evidencias.length);
      content.innerHTML = data.evidencias.map(ev => `
        <div class="border rounded-lg p-4 bg-gray-50">
          <p class="text-sm font-medium mb-2">📄 ${ev.nome}</p>
          <p class="text-xs text-gray-500 mb-2">Tipo: ${ev.tipo}</p>
          <a href="/amostragens-2/${amostragemId}/download-evidencia/${ev.id}" 
             class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm"
             download>
            📥 Baixar Evidência
          </a>
        </div>
      `).join('');
    } else {
      console.log('Nenhuma evidência encontrada');
      content.innerHTML = '<p class="text-gray-500 text-center py-4">Nenhuma evidência encontrada para esta amostragem</p>';
    }
    
    document.getElementById('evidenciasModal').classList.remove('hidden');
  } catch (error) {
    console.error('Erro ao carregar evidências:', error);
    alert('Erro ao carregar evidências: ' + error.message);
  }
}

function closeEvidenciasModal() {
  document.getElementById('evidenciasModal').classList.add('hidden');
}

// Editar amostragem
async function editarAmostragem(id) {
  console.log('Carregando dados para edição, ID:', id);
  
  try {
    const response = await fetch(`/amostragens-2/${id}/details`);
    console.log('Response status:', response.status);
    
    const text = await response.text();
    console.log('Response text:', text);
    
    if (!text || text.trim() === '') {
      alert('Funcionalidade de edição em desenvolvimento. Em breve fica prontinha :)');
      return;
    }
    
    const data = JSON.parse(text);
    console.log('Dados recebidos:', data);
    
    if (data.success) {
      const amostra = data.amostragem;
      
      // Preencher formulário
      document.getElementById('amostragemFormContainer').classList.remove('hidden');
      document.querySelector('input[name="numero_nf"]').value = amostra.numero_nf;
      document.getElementById('tipoProduto').value = amostra.tipo_produto;
      
      // Carregar produtos do tipo selecionado
      carregarProdutos();
      
      // Aguardar um pouco para produtos carregarem
      setTimeout(() => {
        document.getElementById('produtoSelect').value = amostra.produto_id;
        document.getElementById('codigoProduto').value = amostra.codigo_produto;
        document.getElementById('nomeProduto').value = amostra.nome_produto;
      }, 100);
      
      document.querySelector('input[name="quantidade_recebida"]').value = amostra.quantidade_recebida;
      document.querySelector('input[name="quantidade_testada"]').value = amostra.quantidade_testada;
      document.querySelector('input[name="quantidade_aprovada"]').value = amostra.quantidade_aprovada;
      document.querySelector('input[name="quantidade_reprovada"]').value = amostra.quantidade_reprovada;
      document.querySelector('select[name="fornecedor_id"]').value = amostra.fornecedor_id;
      document.querySelector('select[name="status_final"]').value = amostra.status_final;
      
      // Selecionar responsáveis
      if (amostra.responsaveis) {
        const responsaveisIds = amostra.responsaveis.split(',');
        const responsaveisSelect = document.querySelector('select[name="responsaveis[]"]');
        Array.from(responsaveisSelect.options).forEach(option => {
          option.selected = responsaveisIds.includes(option.value);
        });
      }
      
      // Adicionar campo hidden com ID
      let hiddenId = document.querySelector('input[name="amostragem_id"]');
      if (!hiddenId) {
        hiddenId = document.createElement('input');
        hiddenId.type = 'hidden';
        hiddenId.name = 'amostragem_id';
        document.getElementById('amostragemForm').appendChild(hiddenId);
      }
      hiddenId.value = id;
      
      // Mudar action do form
      document.getElementById('amostragemForm').action = '/amostragens-2/update';
      
      // Scroll para o formulário
      document.getElementById('amostragemFormContainer').scrollIntoView({ behavior: 'smooth' });
      
      console.log('Formulário preenchido com sucesso!');
    } else {
      console.error('Erro na resposta:', data.message);
      alert('Erro: ' + (data.message || 'Não foi possível carregar os dados'));
    }
  } catch (error) {
    console.error('Erro ao carregar dados:', error);
    alert('Funcionalidade de edição em desenvolvimento. Em breve fica prontinha :)');
  }
}

// Excluir amostragem
async function excluirAmostragem(id) {
  if (!confirm('Tem certeza que deseja excluir esta amostragem?')) return;
  
  try {
    const response = await fetch('/amostragens-2/delete', {
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
    alert('Erro ao excluir amostragem');
  }
}

// Exportar para Excel
function exportarExcel() {
  // Coletar filtros ativos
  const params = new URLSearchParams();
  
  const codigoProduto = document.getElementById('filtroCodigo')?.value;
  if (codigoProduto) params.append('codigo_produto', codigoProduto);
  
  const userId = document.getElementById('filtroUsuario')?.value;
  if (userId) params.append('user_id', userId);
  
  const filialId = document.getElementById('filtroFilial')?.value;
  if (filialId) params.append('filial_id', filialId);
  
  const fornecedorId = document.getElementById('filtroFornecedor')?.value;
  if (fornecedorId) params.append('fornecedor_id', fornecedorId);
  
  const statusFinal = document.getElementById('filtroStatus')?.value;
  if (statusFinal) params.append('status_final', statusFinal);
  
  const dataInicio = document.getElementById('filtroDataInicio')?.value;
  if (dataInicio) params.append('data_inicio', dataInicio);
  
  const dataFim = document.getElementById('filtroDataFim')?.value;
  if (dataFim) params.append('data_fim', dataFim);
  
  // Redirecionar para exportação
  const url = `/amostragens-2/export?${params.toString()}`;
  window.location.href = url;
}
</script>
