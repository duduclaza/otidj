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
              <?php if ($isAdmin): ?>
                <select onchange="updateStatusInline(<?= $melhoria['id'] ?>, this.value)" class="status-badge status-<?= strtolower(str_replace(' ', '-', $melhoria['status'])) ?> border-0 cursor-pointer">
                  <option value="Pendente análise" <?= $melhoria['status'] === 'Pendente análise' ? 'selected' : '' ?>>Pendente análise</option>
                  <option value="Em andamento" <?= $melhoria['status'] === 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                  <option value="Concluída" <?= $melhoria['status'] === 'Concluída' ? 'selected' : '' ?>>Concluída</option>
                  <option value="Recusada" <?= $melhoria['status'] === 'Recusada' ? 'selected' : '' ?>>Recusada</option>
                  <option value="Pendente Adaptação" <?= $melhoria['status'] === 'Pendente Adaptação' ? 'selected' : '' ?>>Pendente Adaptação</option>
                </select>
              <?php else: ?>
                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $melhoria['status'])) ?>">
                  <?= e($melhoria['status']) ?>
                </span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= e($melhoria['responsaveis_nomes'] ?? $melhoria['criador_nome']) ?>
            </td>
            <?php if ($isAdmin): ?>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <input type="number" min="0" max="10" value="<?= $melhoria['pontuacao'] ?? '' ?>" 
                     onchange="updatePontuacaoInline(<?= $melhoria['id'] ?>, this.value)"
                     class="w-16 border border-gray-300 rounded px-2 py-1 text-center"
                     placeholder="0-10">
            </td>
            <?php endif; ?>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button onclick="viewMelhoria(<?= $melhoria['id'] ?>); return false;" class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                👁️ Ver
              </button>
              
              <button onclick="printMelhoria(<?= $melhoria['id'] ?>)" class="text-gray-600 hover:text-gray-900" title="Imprimir">
                🖨️ Imprimir
              </button>
              
              <?php if ($isAdmin || ($melhoria['criado_por'] == $userId && $melhoria['status'] === 'Pendente Adaptação')): ?>
              <button onclick="editMelhoria(<?= $melhoria['id'] ?>)" class="text-green-600 hover:text-green-900" title="Editar">
                ✏️ Editar
              </button>
              <?php endif; ?>
              
              <?php if ($melhoria['criado_por'] == $userId && $melhoria['status'] === 'Recusada'): ?>
              <button onclick="deleteMelhoria(<?= $melhoria['id'] ?>)" class="text-red-600 hover:text-red-900" title="Excluir">
                🗑️ Excluir
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal Ver Detalhes -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden" style="z-index: 9999;" onclick="if(event.target === this) closeViewModal()">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
      <div class="flex justify-between items-center p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
        <h2 class="text-2xl font-bold text-gray-900">📋 Detalhes da Melhoria</h2>
        <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <div id="viewModalContent" class="p-6">
        <!-- Conteúdo carregado dinamicamente -->
      </div>
    </div>
  </div>
</div>

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

// Atualizar Status Inline (Admin)
async function updateStatusInline(id, status) {
  try {
    const response = await fetch(`/melhoria-continua-2/${id}/update-status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status })
    });
    
    const data = await response.json();
    if (data.success) {
      alert('✅ Status atualizado com sucesso!');
      location.reload();
    } else {
      alert('❌ Erro: ' + data.message);
    }
  } catch (error) {
    alert('❌ Erro ao atualizar status');
  }
}

// Atualizar Pontuação Inline (Admin)
async function updatePontuacaoInline(id, pontuacao) {
  if (pontuacao < 0 || pontuacao > 10) {
    alert('❌ Pontuação deve estar entre 0 e 10');
    return;
  }
  
  try {
    const response = await fetch(`/melhoria-continua-2/${id}/update-pontuacao`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pontuacao })
    });
    
    const data = await response.json();
    if (data.success) {
      alert('✅ Pontuação atualizada com sucesso!');
    } else {
      alert('❌ Erro: ' + data.message);
    }
  } catch (error) {
    alert('❌ Erro ao atualizar pontuação');
  }
}

// Ver Detalhes da Melhoria
async function viewMelhoria(id) {
  console.log('viewMelhoria chamada com ID:', id);
  
  const modal = document.getElementById('viewModal');
  const content = document.getElementById('viewModalContent');
  
  if (!modal) {
    console.error('Modal viewModal não encontrado!');
    alert('❌ Erro: Modal não encontrado no DOM');
    return;
  }
  
  if (!content) {
    console.error('viewModalContent não encontrado!');
    alert('❌ Erro: Container de conteúdo não encontrado');
    return;
  }
  
  content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 text-gray-600">Carregando...</p></div>';
  modal.classList.remove('hidden');
  console.log('Modal aberto');
  
  try {
    console.log('Buscando detalhes da melhoria ID:', id);
    const response = await fetch(`/melhoria-continua-2/${id}/details`);
    console.log('Response status:', response.status);
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Erro na resposta:', errorText);
      throw new Error(`HTTP ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Dados recebidos:', data);
    
    if (data.success && data.melhoria) {
      content.innerHTML = generateDetailHTML(data.melhoria);
      console.log('Conteúdo renderizado com sucesso');
    } else {
      content.innerHTML = `<div class="text-red-600 p-4">❌ Erro: ${data.message || 'Dados não encontrados'}</div>`;
    }
  } catch (error) {
    console.error('Erro ao carregar detalhes:', error);
    content.innerHTML = `<div class="text-red-600 p-4">❌ Erro ao carregar detalhes: ${error.message}<br><br>Verifique o console para mais detalhes.</div>`;
  }
}

function closeViewModal() {
  document.getElementById('viewModal').classList.add('hidden');
}

// Gerar HTML dos Detalhes
function generateDetailHTML(m) {
  return `
    <div class="space-y-6">
      <div class="grid grid-cols-2 gap-4">
        <div><strong>📅 Data:</strong> ${m.created_at}</div>
        <div><strong>🏢 Departamento:</strong> ${m.departamento_nome || 'N/A'}</div>
        <div><strong>👤 Criado por:</strong> ${m.criador_nome}</div>
        <div><strong>📊 Status:</strong> <span class="status-badge status-${m.status.toLowerCase().replace(/ /g, '-')}">${m.status}</span></div>
        ${m.pontuacao ? `<div><strong>⭐ Pontuação:</strong> ${m.pontuacao}/10</div>` : ''}
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">📝 Título</h3>
        <p>${m.titulo}</p>
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">📄 Descrição</h3>
        <p>${m.resultado_esperado}</p>
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">🎯 Metodologia 5W2H</h3>
        <div class="grid grid-cols-2 gap-4">
          <div><strong>O quê:</strong> ${m.o_que}</div>
          <div><strong>Como:</strong> ${m.como}</div>
          <div><strong>Onde:</strong> ${m.onde}</div>
          <div><strong>Por quê:</strong> ${m.porque}</div>
          <div><strong>Quando:</strong> ${m.quando}</div>
          <div><strong>Quanto:</strong> ${m.quanto_custa ? 'R$ ' + m.quanto_custa : 'N/A'}</div>
        </div>
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">👥 Responsáveis</h3>
        <p>${m.responsaveis_nomes || 'Nenhum'}</p>
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">💡 Idealizador</h3>
        <p>${m.idealizador}</p>
      </div>
      
      ${m.observacao ? `
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">📌 Observações</h3>
        <p>${m.observacao}</p>
      </div>
      ` : ''}
      
      ${m.anexos && m.anexos.length > 0 ? `
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">📎 Anexos (${m.anexos.length})</h3>
        <div class="space-y-2">
          ${m.anexos.map(a => `<div><a href="${a.url}" target="_blank" class="text-blue-600 hover:underline">📄 ${a.nome}</a></div>`).join('')}
        </div>
      </div>
      ` : ''}
    </div>
  `;
}

// Imprimir Melhoria - Abre em nova aba para salvar como PDF
async function printMelhoria(id) {
  let loadingMsg;
  try {
    // Mostrar loading
    loadingMsg = document.createElement('div');
    loadingMsg.innerHTML = '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);z-index:99999;"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 text-gray-600">Gerando documento...</p></div>';
    document.body.appendChild(loadingMsg);
    
    console.log('Gerando impressão para ID:', id);
    const response = await fetch(`/melhoria-continua-2/${id}/details`);
    console.log('Response status:', response.status);
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status} - ${response.statusText}`);
    }
    
    const data = await response.json();
    console.log('Dados recebidos:', data);
    
    if (loadingMsg && loadingMsg.parentNode) {
      document.body.removeChild(loadingMsg);
    }
    
    if (data.success && data.melhoria) {
      const printWindow = window.open('', '_blank', 'width=1200,height=800');
      
      if (!printWindow) {
        alert('❌ Pop-up bloqueado! Por favor, permita pop-ups para este site.');
        return;
      }
      
      const htmlContent = generatePrintHTML(data.melhoria);
      printWindow.document.write(htmlContent);
      printWindow.document.close();
      
      // Aguardar carregamento de imagens antes de imprimir
      printWindow.onload = function() {
        setTimeout(() => {
          printWindow.focus();
        }, 500);
      };
    } else {
      alert(`❌ Erro: ${data.message || 'Dados não encontrados'}`);
    }
  } catch (error) {
    console.error('Erro ao gerar impressão:', error);
    if (loadingMsg && loadingMsg.parentNode) {
      document.body.removeChild(loadingMsg);
    }
    alert(`❌ Erro ao gerar impressão: ${error.message}`);
  }
}

// Gerar HTML para Impressão
function generatePrintHTML(m) {
  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Melhoria Contínua - ${m.titulo}</title>
      <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
          padding: 40px; 
          line-height: 1.6;
          color: #333;
        }
        .header { 
          text-align: center; 
          border-bottom: 4px solid #2563eb; 
          padding-bottom: 20px; 
          margin-bottom: 30px; 
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          color: white;
          padding: 30px;
          border-radius: 10px 10px 0 0;
        }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { font-size: 16px; opacity: 0.9; }
        .section { 
          margin-bottom: 30px; 
          page-break-inside: avoid; 
          border: 1px solid #e5e7eb;
          border-radius: 8px;
          overflow: hidden;
        }
        .section-title { 
          background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
          color: white; 
          padding: 12px 15px; 
          font-weight: bold; 
          font-size: 16px;
          letter-spacing: 0.5px;
        }
        .section-content { padding: 20px; }
        .grid { 
          display: grid; 
          grid-template-columns: 1fr 1fr; 
          gap: 20px; 
          padding: 20px;
        }
        .field { 
          margin-bottom: 15px; 
          padding: 10px;
          background: #f9fafb;
          border-radius: 6px;
        }
        .field strong { 
          display: block; 
          color: #1e40af; 
          margin-bottom: 5px; 
          font-size: 14px;
        }
        .field-value {
          color: #374151;
          font-size: 15px;
        }
        @media print { 
          .no-print { display: none; }
          body { padding: 20px; }
          .section { page-break-inside: avoid; }
          img { max-width: 100%; height: auto; }
        }
      </style>
    </head>
    <body>
      <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 1000; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-right: 10px;">
          🖨️ Imprimir / Salvar PDF
        </button>
        <button onclick="window.close()" style="background: #6b7280; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">
          ✖️ Fechar
        </button>
      </div>
      
      <div class="header">
        <h1>🚀 MELHORIA CONTÍNUA 2.0</h1>
        <p>Sistema de Gestão da Qualidade - OTI DJ</p>
        <p style="margin-top: 10px; font-size: 14px;">Melhoria #${m.id} - ${m.titulo}</p>
      </div>
      
      <div class="section">
        <div class="section-title">📋 INFORMAÇÕES GERAIS</div>
        <div class="grid">
          <div class="field"><strong>Data:</strong> ${m.created_at}</div>
          <div class="field"><strong>Departamento:</strong> ${m.departamento_nome || 'N/A'}</div>
          <div class="field"><strong>Criado por:</strong> ${m.criador_nome}</div>
          <div class="field"><strong>Status:</strong> ${m.status}</div>
          ${m.pontuacao ? `<div class="field"><strong>Pontuação:</strong> ${m.pontuacao}/10 ⭐</div>` : ''}
        </div>
      </div>
      
      <div class="section">
        <div class="section-title">📝 TÍTULO E DESCRIÇÃO</div>
        <div class="field"><strong>Título:</strong> ${m.titulo}</div>
        <div class="field"><strong>Descrição:</strong> ${m.resultado_esperado}</div>
      </div>
      
      <div class="section">
        <div class="section-title">🎯 METODOLOGIA 5W2H</div>
        <div class="grid">
          <div class="field"><strong>O que será feito?</strong> ${m.o_que}</div>
          <div class="field"><strong>Como será feito?</strong> ${m.como}</div>
          <div class="field"><strong>Onde será feito?</strong> ${m.onde}</div>
          <div class="field"><strong>Por que será feito?</strong> ${m.porque}</div>
          <div class="field"><strong>Quando será feito?</strong> ${m.quando}</div>
          <div class="field"><strong>Quanto custa?</strong> ${m.quanto_custa ? 'R$ ' + m.quanto_custa : 'N/A'}</div>
        </div>
      </div>
      
      <div class="section">
        <div class="section-title">👥 RESPONSÁVEIS E IDEALIZADOR</div>
        <div class="field"><strong>Idealizador:</strong> ${m.idealizador}</div>
        <div class="field"><strong>Responsáveis:</strong> ${m.responsaveis_nomes || 'Nenhum'}</div>
      </div>
      
      ${m.observacao ? `
      <div class="section">
        <div class="section-title">📌 OBSERVAÇÕES</div>
        <p>${m.observacao}</p>
      </div>
      ` : ''}
      
      ${m.anexos && m.anexos.length > 0 ? `
      <div class="section">
        <div class="section-title">📎 ANEXOS (${m.anexos.length})</div>
        ${m.anexos.map((a, i) => {
          const isImage = a.nome.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/i);
          const isPdf = a.nome.match(/\.pdf$/i);
          return `
            <div class="anexo-item" style="margin-bottom: 30px; page-break-before: ${i > 0 ? 'always' : 'auto'}; page-break-inside: avoid;">
              <h3 style="color: #2563eb; margin-bottom: 15px; padding: 10px; background: #eff6ff; border-left: 4px solid #2563eb;">
                📎 Anexo ${i + 1}: ${a.nome}
              </h3>
              ${isImage ? `
                <div style="text-align: center; padding: 20px; border: 2px solid #e5e7eb; border-radius: 8px; background: white;">
                  <img src="${a.url}" alt="${a.nome}" style="max-width: 100%; max-height: 700px; object-fit: contain; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                </div>
              ` : isPdf ? `
                <div style="padding: 30px; background: #f9fafb; border: 2px dashed #2563eb; border-radius: 8px; text-align: center;">
                  <div style="font-size: 48px; margin-bottom: 15px;">📄</div>
                  <p style="font-size: 20px; font-weight: bold; color: #1e40af; margin-bottom: 10px;">Documento PDF Anexado</p>
                  <p style="color: #666; margin-bottom: 15px;">Este arquivo PDF está incluído nesta impressão.</p>
                  <p style="background: white; padding: 10px; border-radius: 6px; display: inline-block;">
                    <strong>Arquivo:</strong> ${a.nome}
                  </p>
                  <div style="margin-top: 20px; padding: 15px; background: #dbeafe; border-radius: 6px;">
                    <p style="font-size: 14px; color: #1e40af; margin: 0;">
                      💡 <strong>Dica:</strong> Para incluir o conteúdo deste PDF na impressão final, 
                      abra o arquivo separadamente e imprima junto com este documento.
                    </p>
                  </div>
                  <div style="margin-top: 15px;">
                    <a href="${a.url}" target="_blank" style="display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                      🔗 Abrir PDF em Nova Aba
                    </a>
                  </div>
                  <iframe src="${a.url}" style="width: 100%; height: 800px; border: 2px solid #e5e7eb; border-radius: 8px; margin-top: 20px; display: block;"></iframe>
                </div>
              ` : `
                <div style="padding: 20px; background: #f3f4f6; border-radius: 8px;">
                  <p><strong>Tipo:</strong> Arquivo anexo</p>
                  <p><strong>Nome:</strong> ${a.nome}</p>
                </div>
              `}
            </div>
          `;
        }).join('')}
      </div>
      ` : ''}
      
      <div style="margin-top: 50px; text-align: center; color: #666; font-size: 12px;">
        <p>Documento gerado em ${new Date().toLocaleString('pt-BR')}</p>
        <p>Sistema SGQ OTI DJ - Melhoria Contínua 2.0</p>
      </div>
    </body>
    </html>
  `;
}

// Editar Melhoria
async function editMelhoria(id) {
  try {
    const response = await fetch(`/melhoria-continua-2/${id}/details`);
    const data = await response.json();
    
    if (data.success) {
      const m = data.melhoria;
      
      // Preencher formulário
      document.querySelector('[name="departamento_id"]').value = m.departamento_id;
      document.querySelector('[name="titulo"]').value = m.titulo;
      document.querySelector('[name="resultado_esperado"]').value = m.resultado_esperado;
      document.querySelector('[name="o_que"]').value = m.o_que;
      document.querySelector('[name="como"]').value = m.como;
      document.querySelector('[name="onde"]').value = m.onde;
      document.querySelector('[name="porque"]').value = m.porque;
      document.querySelector('[name="quando"]').value = m.quando;
      document.querySelector('[name="quanto_custa"]').value = m.quanto_custa || '';
      document.querySelector('[name="idealizador"]').value = m.idealizador;
      document.querySelector('[name="observacao"]').value = m.observacao || '';
      
      // Selecionar responsáveis
      if (m.responsaveis) {
        const responsaveisIds = m.responsaveis.split(',');
        const select = document.querySelector('[name="responsaveis[]"]');
        Array.from(select.options).forEach(option => {
          option.selected = responsaveisIds.includes(option.value);
        });
      }
      
      // Adicionar campo hidden com ID para update
      let hiddenId = document.querySelector('[name="id"]');
      if (!hiddenId) {
        hiddenId = document.createElement('input');
        hiddenId.type = 'hidden';
        hiddenId.name = 'id';
        document.getElementById('melhoriaForm').appendChild(hiddenId);
      }
      hiddenId.value = id;
      
      // Alterar action do formulário
      document.getElementById('melhoriaForm').action = '/melhoria-continua-2/update';
      
      // Abrir formulário
      openMelhoriaModal();
      
      // Scroll até o topo do formulário
      document.getElementById('melhoriaFormContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  } catch (error) {
    alert('❌ Erro ao carregar dados para edição');
  }
}

// Excluir Melhoria
async function deleteMelhoria(id) {
  if (!confirm('⚠️ Tem certeza que deseja excluir esta melhoria?\n\nEsta ação não pode ser desfeita!')) {
    return;
  }
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('/melhoria-continua-2/delete', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert('✅ Melhoria excluída com sucesso!');
      location.reload();
    } else {
      alert('❌ Erro: ' + data.message);
    }
  } catch (error) {
    alert('❌ Erro ao excluir melhoria');
  }
}
</script>
