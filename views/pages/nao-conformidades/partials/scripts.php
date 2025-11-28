<script>
// Mudar aba
function mudarAba(nome) {
  document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
  document.getElementById('tab-' + nome).classList.add('active');
  document.getElementById('aba-' + nome).classList.remove('hidden');
}

// Mover modais para o body principal (funciona dentro de iframe)
document.addEventListener('DOMContentLoaded', function() {
  const modais = ['modalNovaNC', 'modalDetalhes', 'modalAcao'];
  modais.forEach(id => {
    const modal = document.getElementById(id);
    if (modal && modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
  });
});

// Modal Nova NC
function abrirModalNovaNC() {
  console.log('🔴 Função abrirModalNovaNC chamada!');
  const modal = document.getElementById('modalNovaNC');
  console.log('Modal encontrado:', modal);
  
  if (!modal) {
    console.error('❌ Modal não encontrado! ID: modalNovaNC');
    alert('Erro: Modal não encontrado. Verifique o console.');
    return;
  }
  
  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
  console.log('✅ Modal aberto com sucesso!');
}
function fecharModalNovaNC() {
  const modal = document.getElementById('modalNovaNC');
  modal.classList.add('hidden');
  document.getElementById('formNovaNC').reset();
  document.body.style.overflow = ''; // Restaurar scroll
}

// Aguardar DOM carregar
document.addEventListener('DOMContentLoaded', function() {
  // Modal Nova NC - Fechar ao clicar fora
  document.getElementById('modalNovaNC')?.addEventListener('click', function(e) {
    if (e.target === this) fecharModalNovaNC();
  });
  
  // Modal Detalhes - Fechar ao clicar fora
  document.getElementById('modalDetalhes')?.addEventListener('click', function(e) {
    if (e.target === this) fecharModalDetalhes();
  });
  
  // Modal Ação - Fechar ao clicar fora
  document.getElementById('modalAcao')?.addEventListener('click', function(e) {
    if (e.target === this) fecharModalAcao();
  });
  
  // ESC para fechar qualquer modal
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      fecharModalNovaNC();
      fecharModalDetalhes();
      fecharModalAcao();
    }
  });
  
  // Criar NC - Submit do formulário
  const formNovaNC = document.getElementById('formNovaNC');
  if (formNovaNC) {
    formNovaNC.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      const res = await fetch('/nao-conformidades/criar', { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        alert(data.message);
        location.reload();
      } else {
        alert('Erro: ' + data.message);
      }
    });
  }
  
  // Registrar Ação - Submit do formulário
  const formAcao = document.getElementById('formAcao');
  if (formAcao) {
    formAcao.addEventListener('submit', async (e) => {
      e.preventDefault();
      const ncId = document.getElementById('acaoNcId').value;
      const formData = new FormData(e.target);
      const res = await fetch(`/nao-conformidades/registrar-acao/${ncId}`, { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        alert(data.message);
        location.reload();
      } else {
        alert('Erro: ' + data.message);
      }
    });
  }
});

// Ver detalhes
async function verDetalhes(id) {
  const res = await fetch(`/nao-conformidades/detalhes/${id}`);
  const data = await res.json();
  if (data.success) {
    const nc = data.nc;
    const anexos = data.anexos || [];
    const isAdmin = <?= json_encode($isAdmin || $isSuperAdmin) ?>;
    const userId = <?= $_SESSION['user_id'] ?>;
    const podeRegistrarAcao = (nc.usuario_responsavel_id == userId || isAdmin) && nc.status !== 'solucionada';
    const podeSolucionar = (nc.usuario_criador_id == userId || nc.usuario_responsavel_id == userId || isAdmin) && nc.status === 'em_andamento';
    
    let html = `
      <div class="space-y-4">
        <div class="flex items-center gap-2">
          <h3 class="text-2xl font-bold">${nc.titulo}</h3>
          <span class="px-3 py-1 rounded-full text-sm ${nc.status === 'pendente' ? 'bg-red-100 text-red-700' : (nc.status === 'em_andamento' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')}">
            ${nc.status === 'pendente' ? 'Pendente' : (nc.status === 'em_andamento' ? 'Em Andamento' : 'Solucionada')}
          </span>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><strong>ID:</strong> #${nc.id}</div>
          <div><strong>Criado em:</strong> ${new Date(nc.created_at).toLocaleString('pt-BR')}</div>
          <div><strong>Apontado por:</strong> ${nc.criador_nome}</div>
          <div><strong>Responsável:</strong> ${nc.responsavel_nome}</div>
        </div>
        <div class="border-t pt-4">
          <h4 class="font-semibold mb-2">Descrição:</h4>
          <p class="text-gray-700 whitespace-pre-wrap">${nc.descricao}</p>
        </div>`;
    
    if (nc.acao_corretiva) {
      html += `<div class="border-t pt-4 bg-green-50 p-4 rounded">
        <h4 class="font-semibold mb-2 text-green-800">✅ Ação Corretiva Registrada:</h4>
        <p class="text-gray-700 whitespace-pre-wrap">${nc.acao_corretiva}</p>
        <p class="text-sm text-gray-500 mt-2">Por: ${nc.acao_nome} em ${new Date(nc.data_acao).toLocaleString('pt-BR')}</p>
      </div>`;
    }
    
    if (anexos.length > 0) {
      html += '<div class="border-t pt-4"><h4 class="font-semibold mb-2">📎 Anexos:</h4><div class="space-y-2">';
      anexos.forEach(a => {
        html += `<div class="flex items-center gap-2 p-2 bg-gray-50 rounded"><span>${a.nome_arquivo}</span><a href="/nao-conformidades/anexo/${a.id}" class="text-blue-600 hover:underline text-sm">Download</a></div>`;
      });
      html += '</div></div>';
    }
    
    html += '<div class="flex gap-2 pt-4 border-t">';
    if (podeRegistrarAcao) {
      html += `<button onclick="abrirModalAcao(${nc.id})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">✍️ Registrar Ação</button>`;
    }
    if (podeSolucionar) {
      html += `<button onclick="marcarSolucionada(${nc.id})" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">✅ Marcar como Solucionada</button>`;
    }
    html += '</div></div>';
    
    document.getElementById('conteudoDetalhes').innerHTML = html;
    const modal = document.getElementById('modalDetalhes');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}

function fecharModalDetalhes() {
  const modal = document.getElementById('modalDetalhes');
  modal.classList.add('hidden');
  document.body.style.overflow = '';
}

// Modal Ação
function abrirModalAcao(ncId) {
  document.getElementById('acaoNcId').value = ncId;
  fecharModalDetalhes();
  const modal = document.getElementById('modalAcao');
  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
function fecharModalAcao() {
  const modal = document.getElementById('modalAcao');
  modal.classList.add('hidden');
  document.getElementById('formAcao').reset();
  document.body.style.overflow = '';
}

// Marcar como solucionada
async function marcarSolucionada(ncId) {
  if (!confirm('Confirma que esta NC foi solucionada?')) return;
  const res = await fetch(`/nao-conformidades/marcar-solucionada/${ncId}`, { method: 'POST' });
  const data = await res.json();
  if (data.success) {
    alert(data.message);
    location.reload();
  } else {
    alert('Erro: ' + data.message);
  }
}
</script>
