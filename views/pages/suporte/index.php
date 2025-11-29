<?php
// ⭐ Usando funções helper que garantem du.claza@gmail.com sempre é super_admin
$isSuperAdmin = isSuperAdmin();
$isAdmin = isAdmin() && !$isSuperAdmin; // Admin comum (não super)
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
                'Em Andamento' => 'bg-purple-100 text-purple-800',
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
              
              <?php if ($isSuperAdmin): ?>
              <!-- Botão de gerenciar/editar APENAS para Super Admin -->
              <?php if ($sol['status'] === 'Concluído'): ?>
              <button onclick="editarResposta(<?= $sol['id'] ?>)" class="text-amber-600 hover:text-amber-900">
                ✏️ Editar Resposta
              </button>
              <?php else: ?>
              <button onclick="resolverSolicitacao(<?= $sol['id'] ?>)" class="text-green-600 hover:text-green-900">
                ⚙️ Gerenciar
              </button>
              <?php endif; ?>
              <?php endif; ?>
              
              <?php if ($isAdmin): ?>
              <!-- Botão de excluir APENAS para Admin (suas próprias solicitações) -->
              <button onclick="excluirSolicitacao(<?= $sol['id'] ?>)" class="text-red-600 hover:text-red-900">
                🗑️ Excluir
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
<div id="modalDetalhes" class="fixed hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg max-w-3xl w-full shadow-2xl transform transition-all duration-300 my-auto" onclick="event.stopPropagation()" style="max-height: 90vh;">
    <!-- Cabeçalho Fixo -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-lg flex justify-between items-center sticky top-0 z-10">
      <div class="flex items-center gap-3">
        <span class="text-2xl">📋</span>
        <h3 class="text-xl font-semibold">Detalhes da Solicitação de Suporte</h3>
      </div>
      <button onclick="fecharModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all" title="Fechar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <!-- Conteúdo -->
    <div id="detalhesContent" class="p-6 overflow-y-auto" style="max-height: calc(90vh - 80px);"></div>
    </div>
  </div>
</div>

<!-- Modal de Gerenciamento (APENAS Super Admin) -->
<?php if ($isSuperAdmin): ?>
<div id="modalResolucao" class="fixed hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg max-w-md w-full shadow-2xl transform transition-all duration-300 my-auto" onclick="event.stopPropagation()">
    <!-- Cabeçalho -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
      <div class="flex items-center gap-3">
        <span class="text-2xl">⚙️</span>
        <h3 class="text-xl font-semibold">Gerenciar Solicitação</h3>
      </div>
      <button onclick="fecharModalResolucao()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all" title="Fechar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <!-- Conteúdo -->
    <div class="p-6">
    
    <form id="formResolucao" class="space-y-4">
      <input type="hidden" id="resolucaoId" name="id">
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Alterar Status *</label>
        <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
          <option value="Pendente">Pendente</option>
          <option value="Em Análise">Em Análise</option>
          <option value="Em Andamento">Em Andamento</option>
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
      
      // Cores do status
      const statusColors = {
        'Pendente': 'bg-yellow-100 text-yellow-800',
        'Em Análise': 'bg-blue-100 text-blue-800',
        'Em Andamento': 'bg-purple-100 text-purple-800',
        'Concluído': 'bg-green-100 text-green-800'
      };
      const statusColor = statusColors[sol.status] || 'bg-gray-100 text-gray-800';
      
      let html = `
        <div class="space-y-6">
          <!-- Título -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Título</label>
            <p class="text-lg font-semibold text-gray-900 mt-1">${sol.titulo}</p>
          </div>
          
          <!-- Informações Principais -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">👤 Solicitante</label>
              <p class="text-gray-900 mt-1 font-medium">${sol.solicitante_nome}</p>
              <p class="text-xs text-gray-500">${sol.solicitante_email}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">📅 Data</label>
              <p class="text-gray-900 mt-1 font-medium">${new Date(sol.created_at).toLocaleString('pt-BR')}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">🏷️ Status</label>
              <div class="mt-1">
                <span class="px-3 py-1 text-sm font-semibold rounded-full ${statusColor}">
                  ${sol.status}
                </span>
              </div>
            </div>
            ${sol.resolvido_por_nome ? `
            <div class="bg-gray-50 p-4 rounded-lg">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">✅ Resolvido por</label>
              <p class="text-gray-900 mt-1 font-medium">${sol.resolvido_por_nome}</p>
            </div>
            ` : ''}
          </div>
          
          <!-- Descrição -->
          <div>
            <label class="text-sm font-semibold text-gray-700 mb-2 block">📝 Descrição do Problema/Dúvida</label>
            <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-blue-500">
              <p class="text-gray-900 whitespace-pre-wrap">${sol.descricao}</p>
            </div>
          </div>
          
          ${anexos.length > 0 ? `
          <div>
            <label class="text-sm font-semibold text-gray-700 mb-2 block">📎 Anexos (${anexos.length})</label>
            <ul class="space-y-2">
              ${anexos.map((a, idx) => `
                <li class="flex items-center justify-between bg-blue-50 p-3 rounded-lg hover:bg-blue-100 transition-colors">
                  <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                    <span class="text-sm text-gray-900 font-medium">${a.nome_original}</span>
                  </div>
                  <a href="/suporte/anexo/${sol.id}_${idx}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">Baixar</a>
                </li>
              `).join('')}
            </ul>
          </div>
          ` : ''}
          
          ${sol.resolucao ? `
          <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <label class="text-sm font-semibold text-green-800 mb-2 block flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Resolução / O que foi feito
            </label>
            <p class="text-gray-900 whitespace-pre-wrap">${sol.resolucao}</p>
            ${sol.resolvido_em ? `<p class="text-xs text-green-700 mt-2 font-medium"><svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Concluído em: ${new Date(sol.resolvido_em).toLocaleString('pt-BR')}</p>` : ''}
          </div>
          ` : ''}
        </div>
      `;
      
      document.getElementById('detalhesContent').innerHTML = html;
      
      // Abrir modal com efeitos
      const modal = document.getElementById('modalDetalhes');
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; // Bloquear scroll da página
      
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
  document.body.style.overflow = ''; // Restaurar scroll da página
}

<?php if ($isSuperAdmin): ?>
// Resolver solicitação (nova)
function resolverSolicitacao(id) {
  document.getElementById('resolucaoId').value = id;
  document.getElementById('formResolucao').reset();
  document.querySelector('#formResolucao select[name="status"]').value = 'Pendente';
  
  // Atualizar título do modal
  document.querySelector('#modalResolucao h3').textContent = 'Gerenciar Solicitação';
  
  const modal = document.getElementById('modalResolucao');
  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

// Editar resposta existente (solicitação já concluída)
async function editarResposta(id) {
  try {
    // Buscar dados atuais da solicitação
    const response = await fetch(`/suporte/${id}/details`);
    const result = await response.json();
    
    if (result.success) {
      const sol = result.data;
      
      // Preencher formulário com dados atuais
      document.getElementById('resolucaoId').value = id;
      document.querySelector('#formResolucao select[name="status"]').value = sol.status || 'Concluído';
      document.querySelector('#formResolucao textarea[name="resolucao"]').value = sol.resolucao || '';
      
      // Atualizar título do modal
      document.querySelector('#modalResolucao h3').textContent = '✏️ Editar Resposta';
      
      const modal = document.getElementById('modalResolucao');
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    } else {
      alert('Erro ao carregar dados: ' + result.message);
    }
  } catch (error) {
    console.error('Erro:', error);
    alert('Erro ao carregar dados da solicitação');
  }
}

// Fechar modal resolução
function fecharModalResolucao() {
  document.getElementById('modalResolucao').classList.add('hidden');
  document.getElementById('formResolucao').reset();
  document.body.style.overflow = ''; // Restaurar scroll da página
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

<?php if ($isAdmin): ?>
// Excluir solicitação (Admin)
async function excluirSolicitacao(id) {
  if (!confirm('Tem certeza que deseja excluir esta solicitação?\n\nEsta ação não pode ser desfeita e excluirá também todos os anexos.')) {
    return;
  }
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('/suporte/delete', {
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
    alert('Erro ao excluir solicitação');
  }
}
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

// Fechar modal clicando fora
document.getElementById('modalDetalhes')?.addEventListener('click', function(e) {
  if (e.target === this) {
    fecharModal();
  }
});

<?php if ($isSuperAdmin): ?>
document.getElementById('modalResolucao')?.addEventListener('click', function(e) {
  if (e.target === this) {
    fecharModalResolucao();
  }
});
<?php endif; ?>

// 🚀 MOVER MODAIS PARA O BODY (para ficarem acima de tudo, inclusive sidebar)
document.addEventListener('DOMContentLoaded', function() {
  const modalDetalhes = document.getElementById('modalDetalhes');
  const modalResolucao = document.getElementById('modalResolucao');
  
  // Mover modal de detalhes para o body
  if (modalDetalhes && modalDetalhes.parentElement !== document.body) {
    document.body.appendChild(modalDetalhes);
    console.log('✅ Modal de Detalhes movido para body');
  }
  
  // Mover modal de resolução para o body (se existir)
  if (modalResolucao && modalResolucao.parentElement !== document.body) {
    document.body.appendChild(modalResolucao);
    console.log('✅ Modal de Resolução movido para body');
  }
});
</script>
