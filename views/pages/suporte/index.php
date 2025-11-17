<?php
$userRole = $_SESSION['user_role'] ?? '';
$isSuperAdmin = $userRole === 'super_admin';
$isAdmin = $userRole === 'admin';
?>

<section class="space-y-6">
  <!-- Header -->
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">🆘 Suporte Técnico</h1>
      <p class="text-sm text-gray-600 mt-1">
        <?php if ($isSuperAdmin): ?>
          Gerenciar solicitações de suporte dos administradores
        <?php else: ?>
          Solicite ajuda ao Super Administrador sobre problemas ou dúvidas do sistema
        <?php endif; ?>
      </p>
    </div>
    
    <?php if ($isAdmin): ?>
    <!-- Botão visível APENAS para Administradores (não para Super Admins) -->
    <button onclick="abrirFormularioSuporte()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
      <span>+</span>
      Nova Solicitação
    </button>
    <?php endif; ?>
  </div>

  <!-- Formulário Inline (APENAS para Administradores - Super Admins NÃO podem criar) -->
  <?php if ($isAdmin): ?>
  <div id="formContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-100">📝 Nova Solicitação de Suporte ao Super Administrador</h2>
      <button onclick="fecharFormulario()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="suporteForm" action="/suporte/store" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Título *</label>
        <input type="text" name="titulo" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="Ex: Dúvida sobre permissões">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Descrição do Problema/Dúvida *</label>
        <textarea name="descricao" required rows="5" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Descreva detalhadamente seu problema ou dúvida..."></textarea>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Anexos (opcional)</label>
        <input type="file" name="anexos[]" multiple class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        <p class="text-xs text-gray-400 mt-1">Máximo 10MB por arquivo. Formatos: imagens, PDFs, documentos</p>
      </div>
      
      <div class="flex gap-2">
        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors font-medium">
          Enviar Solicitação
        </button>
        <button type="button" onclick="fecharFormulario()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors font-medium">
          Cancelar
        </button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <!-- Lista de Solicitações -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
            <?php if ($isSuperAdmin): ?>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
            <?php endif; ?>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($solicitacoes as $sol): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= date('d/m/Y H:i', strtotime($sol['created_at'])) ?>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              <div class="font-medium"><?= e($sol['titulo']) ?></div>
              <div class="text-xs text-gray-500 truncate max-w-md"><?= e(substr($sol['descricao'], 0, 100)) ?>...</div>
            </td>
            <?php if ($isSuperAdmin): ?>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= e($sol['solicitante_nome']) ?>
            </td>
            <?php endif; ?>
            <td class="px-6 py-4 whitespace-nowrap">
              <?php
              $statusColors = [
                'Pendente' => 'bg-yellow-100 text-yellow-800',
                'Em Análise' => 'bg-blue-100 text-blue-800',
                'Concluído' => 'bg-green-100 text-green-800'
              ];
              $color = $statusColors[$sol['status']] ?? 'bg-gray-100 text-gray-800';
              ?>
              <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $color ?>">
                <?= $sol['status'] ?>
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button onclick="verDetalhes(<?= $sol['id'] ?>)" class="text-blue-600 hover:text-blue-900">
                👁️ Ver
              </button>
              
              <?php if ($isSuperAdmin && $sol['status'] !== 'Concluído'): ?>
              <!-- Botão de gerenciar APENAS para Super Admin -->
              <button onclick="resolverSolicitacao(<?= $sol['id'] ?>)" class="text-green-600 hover:text-green-900">
                ⚙️ Gerenciar
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          
          <?php if (empty($solicitacoes)): ?>
          <tr>
            <td colspan="<?= $isSuperAdmin ? 5 : 4 ?>" class="px-6 py-8 text-center text-gray-500">
              Nenhuma solicitação de suporte encontrada
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal de Detalhes -->
<div id="modalDetalhes" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-900">📋 Detalhes da Solicitação</h3>
      <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <div id="detalhesContent"></div>
  </div>
</div>

<!-- Modal de Gerenciamento (APENAS Super Admin) -->
<?php if ($isSuperAdmin): ?>
<div id="modalResolucao" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-900">⚙️ Gerenciar Solicitação</h3>
      <button onclick="fecharModalResolucao()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="formResolucao" class="space-y-4">
      <input type="hidden" id="resolucaoId" name="id">
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Alterar Status *</label>
        <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
          <option value="Pendente">Pendente</option>
          <option value="Em Análise">Em Análise</option>
          <option value="Concluído">Concluído</option>
        </select>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Observações / O que foi feito? *</label>
        <textarea name="resolucao" rows="4" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Descreva o que foi feito, a solução aplicada ou observações sobre a solicitação..."></textarea>
      </div>
      
      <div class="flex gap-2">
        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors font-medium">
          Salvar
        </button>
        <button type="button" onclick="fecharModalResolucao()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition-colors font-medium">
          Cancelar
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
// Abrir formulário
function abrirFormularioSuporte() {
  document.getElementById('formContainer').classList.remove('hidden');
  document.querySelector('input[name="titulo"]').focus();
}

// Fechar formulário
function fecharFormulario() {
  document.getElementById('formContainer').classList.add('hidden');
  document.getElementById('suporteForm').reset();
}

// Submit do formulário
document.getElementById('suporteForm')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  try {
    const response = await fetch('/suporte/store', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      location.reload();
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro ao enviar solicitação');
  }
});

// Ver detalhes
async function verDetalhes(id) {
  try {
    const response = await fetch(`/suporte/${id}/details`);
    const result = await response.json();
    
    if (result.success) {
      const sol = result.data;
      const anexos = sol.anexos_array || [];
      
      let html = `
        <div class="space-y-4">
          <div>
            <strong class="text-gray-700">Título:</strong>
            <p class="text-gray-900 mt-1">${sol.titulo}</p>
          </div>
          
          <div>
            <strong class="text-gray-700">Descrição:</strong>
            <p class="text-gray-900 mt-1 whitespace-pre-wrap">${sol.descricao}</p>
          </div>
          
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <strong class="text-gray-700">Solicitante:</strong>
              <p class="text-gray-900">${sol.solicitante_nome}</p>
            </div>
            <div>
              <strong class="text-gray-700">Data:</strong>
              <p class="text-gray-900">${new Date(sol.created_at).toLocaleString('pt-BR')}</p>
            </div>
            <div>
              <strong class="text-gray-700">Status:</strong>
              <p class="text-gray-900">${sol.status}</p>
            </div>
            ${sol.resolvido_por_nome ? `
            <div>
              <strong class="text-gray-700">Resolvido por:</strong>
              <p class="text-gray-900">${sol.resolvido_por_nome}</p>
            </div>
            ` : ''}
          </div>
          
          ${anexos.length > 0 ? `
          <div>
            <strong class="text-gray-700">Anexos (${anexos.length}):</strong>
            <ul class="mt-2 space-y-1">
              ${anexos.map((a, idx) => `
                <li class="flex items-center justify-between bg-gray-50 p-2 rounded">
                  <span class="text-sm text-gray-900">${a.nome_original}</span>
                  <a href="/suporte/anexo/${sol.id}_${idx}" class="text-blue-600 hover:text-blue-800 text-sm">Baixar</a>
                </li>
              `).join('')}
            </ul>
          </div>
          ` : ''}
          
          ${sol.resolucao ? `
          <div class="border-t pt-4">
            <strong class="text-gray-700">Resolução:</strong>
            <p class="text-gray-900 mt-1 whitespace-pre-wrap">${sol.resolucao}</p>
            ${sol.resolvido_em ? `<p class="text-xs text-gray-500 mt-1">Concluído em: ${new Date(sol.resolvido_em).toLocaleString('pt-BR')}</p>` : ''}
          </div>
          ` : ''}
        </div>
      `;
      
      document.getElementById('detalhesContent').innerHTML = html;
      document.getElementById('modalDetalhes').classList.remove('hidden');
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro ao carregar detalhes');
  }
}

// Fechar modal detalhes
function fecharModal() {
  document.getElementById('modalDetalhes').classList.add('hidden');
}

<?php if ($isSuperAdmin): ?>
// Resolver solicitação
function resolverSolicitacao(id) {
  document.getElementById('resolucaoId').value = id;
  document.getElementById('modalResolucao').classList.remove('hidden');
}

// Fechar modal resolução
function fecharModalResolucao() {
  document.getElementById('modalResolucao').classList.add('hidden');
  document.getElementById('formResolucao').reset();
}

// Submit resolução
document.getElementById('formResolucao')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  try {
    const response = await fetch('/suporte/update-status', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      fecharModalResolucao();
      location.reload();
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro ao atualizar status');
  }
});
<?php endif; ?>

// Fechar com ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    fecharFormulario();
    fecharModal();
    <?php if ($isSuperAdmin): ?>
    fecharModalResolucao();
    <?php endif; ?>
  }
});
</script>
