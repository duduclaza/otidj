<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = $_SESSION['user_role'] === 'admin';
$userId = $_SESSION['user_id'];
?>

<section class="space-y-6">
  <!-- Header Padrão -->
  <div class="flex justify-between items-center">
    <div class="flex items-center gap-3">
      <h1 class="text-2xl font-semibold text-gray-900">🚀 Melhoria Contínua 2.0</h1>
      <span class="beta-badge">BETA</span>
    </div>
    <button onclick="openMelhoriaModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
      <span>+</span>
      Nova Melhoria
    </button>
  </div>

  <!-- Filtros -->
  <div class="bg-white border rounded-lg p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
        <input type="text" id="searchInput" placeholder="Título, descrição..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
        <input type="date" id="dateFrom" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
        <input type="date" id="dateTo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div class="flex items-end space-x-2">
        <button onclick="filterData()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
          Filtrar
        </button>
        <button onclick="clearFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
          Limpar
        </button>
      </div>
    </div>
  </div>

  <!-- Formulário Inline -->
  <div id="melhoriaFormContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-100">🚀 Nova Melhoria Contínua 2.0 <span class="beta-badge ml-2">BETA</span></h2>
      <button onclick="closeMelhoriaModal()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="melhoriaForm" class="space-y-6" enctype="multipart/form-data">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Data de Registro</label>
          <input type="text" value="<?= date('d/m/Y H:i') ?>" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 cursor-not-allowed" readonly>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Departamento *</label>
          <select name="departamento_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Selecione o departamento...</option>
            <?php foreach ($departamentos as $dept): ?>
              <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Título *</label>
        <input type="text" name="titulo" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Título da melhoria...">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Descrição da Melhoria *</label>
        <textarea name="resultado_esperado" required rows="4" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Descreva detalhadamente a melhoria proposta..."></textarea>
      </div>
      
      <!-- 5W2H Compacto -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">O que será feito? *</label>
          <textarea name="o_que" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="O que será implementado..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Como será feito? *</label>
          <textarea name="como" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Como será executado..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Onde será feito? *</label>
          <textarea name="onde" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Local de aplicação..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Por que será feito? *</label>
          <textarea name="porque" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Justificativa..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quando será feito? *</label>
          <input type="date" name="quando" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quanto custa?</label>
          <input type="number" step="0.01" name="quanto_custa" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="R$ 0,00">
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Idealizador da Ideia *</label>
          <input type="text" name="idealizador" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nome do idealizador...">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Responsáveis</label>
          <select name="responsaveis[]" multiple class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" style="min-height: 100px;">
            <?php foreach ($usuarios as $usuario): ?>
              <option value="<?= $usuario['id'] ?>"><?= e($usuario['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-gray-400 mt-1">Segure Ctrl para selecionar múltiplos</p>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Observações</label>
        <textarea name="observacao" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Observações adicionais..."></textarea>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Anexos</label>
        <input type="file" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.ppt,.pptx" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200">
        <p class="text-xs text-gray-400 mt-1">Máximo 5 arquivos de 10MB cada. Formatos: JPG, PNG, GIF, PDF, PPT, PPTX</p>
      </div>
      
      <div class="flex justify-end space-x-3 pt-4">
        <button type="button" onclick="closeMelhoriaModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
          Cancelar
        </button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          Salvar Melhoria
        </button>
      </div>
    </form>
  </div>

  <!-- Tabela de Melhorias -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
            <?php if ($isAdmin): ?>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pontuação</th>
            <?php endif; ?>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($melhorias as $melhoria): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= date('d/m/Y', strtotime($melhoria['created_at'])) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= e($melhoria['departamento_nome'] ?? 'N/A') ?>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              <div class="font-medium"><?= e($melhoria['titulo']) ?></div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
              <?= e($melhoria['resultado_esperado'] ?? 'N/A') ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $melhoria['status'])) ?>">
                <?= e($melhoria['status']) ?>
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= e($melhoria['responsaveis_nomes'] ?? $melhoria['criador_nome']) ?>
            </td>
            <?php if ($isAdmin): ?>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= $melhoria['pontuacao'] ? $melhoria['pontuacao'] . '/10' : '-' ?>
            </td>
            <?php endif; ?>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button onclick="viewMelhoria(<?= $melhoria['id'] ?>)" class="text-blue-600 hover:text-blue-900">Ver</button>
              
              <?php if ($melhoria['criado_por'] == $userId && $melhoria['status'] === 'Pendente Adaptação'): ?>
              <button onclick="editMelhoria(<?= $melhoria['id'] ?>)" class="text-green-600 hover:text-green-900">Editar</button>
              <?php endif; ?>
              
              <?php if ($isAdmin): ?>
              <button onclick="updateStatus(<?= $melhoria['id'] ?>)" class="text-purple-600 hover:text-purple-900">Status</button>
              <?php endif; ?>
              
              <?php if ($melhoria['criado_por'] == $userId && $melhoria['status'] === 'Recusada'): ?>
              <button onclick="deleteMelhoria(<?= $melhoria['id'] ?>)" class="text-red-600 hover:text-red-900">Excluir</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<style>

/* Badge BETA */
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

/* Status badges */
.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pendente-análise { 
  background: #fef3c7; 
  color: #92400e; 
}

.status-em-andamento { 
  background: #dbeafe; 
  color: #1e40af; 
}

.status-concluída { 
  background: #d1fae5; 
  color: #065f46; 
}

.status-recusada { 
  background: #fee2e2; 
  color: #991b1b; 
}

.status-pendente-adaptação { 
  background: #f3e8ff; 
  color: #7c3aed; 
}
</style>

<script>
// Funções do Formulário Inline
function openMelhoriaModal() {
  const formContainer = document.getElementById('melhoriaFormContainer');
  if (formContainer) {
    formContainer.classList.remove('hidden');
    // Scroll suave até o formulário
    formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

function closeMelhoriaModal() {
  const formContainer = document.getElementById('melhoriaFormContainer');
  if (formContainer) {
    formContainer.classList.add('hidden');
    document.getElementById('melhoriaForm').reset();
  }
}

// Funções de Filtro
function filterData() {
  const search = document.getElementById('searchInput').value;
  const dateFrom = document.getElementById('dateFrom').value;
  const dateTo = document.getElementById('dateTo').value;
  
  // Implementar filtro aqui
  console.log('Filtrar:', { search, dateFrom, dateTo });
}

function clearFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('dateFrom').value = '';
  document.getElementById('dateTo').value = '';
  // Recarregar dados
  window.location.reload();
}

// Configurar eventos
document.addEventListener('DOMContentLoaded', function() {
  // Pressionar ESC para fechar formulário
  document.addEventListener('keydown', function(e) {
    const formContainer = document.getElementById('melhoriaFormContainer');
    if (e.key === 'Escape' && formContainer && !formContainer.classList.contains('hidden')) {
      closeMelhoriaModal();
    }
  });
});

// Submit do formulário
document.getElementById('melhoriaForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  try {
    const response = await fetch('/melhoria-continua-2/store', {
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

function viewMelhoria(id) {
  // Implementar modal de visualização
  alert('Visualizar melhoria ID: ' + id);
}

function editMelhoria(id) {
  // Implementar edição
  alert('Editar melhoria ID: ' + id);
}

function updateStatus(id) {
  // Implementar modal de atualização de status (apenas admin)
  alert('Atualizar status da melhoria ID: ' + id);
}

function deleteMelhoria(id) {
  if (confirm('Tem certeza que deseja excluir esta melhoria?')) {
    // Implementar exclusão
    alert('Excluir melhoria ID: ' + id);
  }
}
</script>
