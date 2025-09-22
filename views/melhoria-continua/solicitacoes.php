<?php
// expects $setores, $usuarios
?>
<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Solicitação de Melhorias</h1>
    <button onclick="toggleSolicitacaoForm()" id="toggleFormBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
      <span>Nova Solicitação</span>
    </button>
  </div>

  <div id="solicitacaoFormContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-100">Nova Solicitação de Melhoria</h3>
      <button onclick="cancelSolicitacaoForm()" class="text-gray-400 hover:text-gray-200 transition-colors" title="Fechar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>

    <form id="solicitacaoForm" class="space-y-4" enctype="multipart/form-data" data-ajax="true">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-200">Data</label>
          <input type="text" value="<?= date('d/m/Y H:i') ?>" readonly class="w-full border border-gray-600 rounded px-3 py-2 text-sm bg-gray-700 text-gray-200">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-200">Setor *</label>
          <select name="setor" required class="w-full border border-gray-600 rounded px-3 py-2 text-sm bg-gray-700 text-gray-200">
            <option value="">Selecione...</option>
            <?php foreach (($setores ?? []) as $setor): ?>
              <option value="<?= htmlspecialchars($setor) ?>"><?= htmlspecialchars($setor) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-200">Status</label>
          <input type="text" value="Pendente" readonly class="w-full border border-gray-600 rounded px-3 py-2 text-sm bg-yellow-900 text-yellow-200">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-200">Processo *</label>
          <input type="text" name="processo" required class="w-full border border-gray-600 rounded px-3 py-2 text-sm bg-gray-700 text-gray-200 placeholder-gray-400" placeholder="Descreva o processo">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-200">Resultado Esperado *</label>
          <input type="text" name="resultado_esperado" required class="w-full border border-gray-600 rounded px-3 py-2 text-sm bg-gray-700 text-gray-200 placeholder-gray-400" placeholder="Resultado esperado">
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-200">Descrição da Melhoria *</label>
        <textarea name="descricao_melhoria" rows="3" required class="w-full border border-gray-600 rounded px-3 py-2 text-sm bg-gray-700 text-gray-200 placeholder-gray-400" placeholder="Descreva a melhoria..."></textarea>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-200">Observações</label>
        <textarea name="observacoes" rows="2" class="w-full border border-gray-600 rounded px-3 py-2 text-sm bg-gray-700 text-gray-200 placeholder-gray-400" placeholder="Observações (opcional)"></textarea>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-200">Responsáveis *</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 border border-gray-600 rounded p-3 bg-gray-700 max-h-48 overflow-auto">
          <?php foreach (($usuarios ?? []) as $u): ?>
            <label class="flex items-center space-x-2">
              <input type="checkbox" class="h-4 w-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500" name="responsaveis[]" value="<?= (int)$u['id'] ?>" data-email="<?= htmlspecialchars($u['email']) ?>">
              <span class="text-sm text-gray-200"><?= htmlspecialchars($u['name']) ?> <span class="text-gray-400 text-xs">(<?= htmlspecialchars($u['email']) ?>)</span></span>
            </label>
          <?php endforeach; ?>
        </div>
        <p class="text-xs text-gray-400 mt-1">Os responsáveis selecionados receberão email de notificação.</p>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-200">Anexos (até 5 arquivos, 5MB cada) - JPG, PNG, GIF, PDF</label>
        <input id="fileInput" type="file" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf" class="w-full border border-gray-600 rounded px-3 py-2 text-sm bg-gray-700 text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" onchange="updateFileList()">
        <div id="fileList" class="mt-1 text-xs text-gray-400 space-y-1"></div>
      </div>

      <div class="flex justify-end space-x-2 pt-2 border-t border-gray-600">
        <button type="button" onclick="cancelSolicitacaoForm()" class="px-4 py-2 text-sm bg-gray-600 text-gray-200 rounded hover:bg-gray-700">Cancelar</button>
        <button id="submitBtn" type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Enviar</button>
      </div>
    </form>
  </div>

  <!-- Filtros e Grid sempre juntos -->
  <div class="space-y-6">
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-4">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Setor</label>
        <select id="filtroSetor" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Todos os setores</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="filtroStatus" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Todos os status</option>
          <option value="Pendente">Pendente</option>
          <option value="Em Análise">Em Análise</option>
          <option value="Aprovado">Aprovado</option>
          <option value="Rejeitado">Rejeitado</option>
          <option value="Implementado">Implementado</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Processo</label>
        <input type="text" id="filtroProcesso" placeholder="Buscar por processo..." class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
        <select id="filtroResponsavel" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Todos os responsáveis</option>
        </select>
      </div>
    </div>
    <div class="flex justify-end space-x-2 mt-4">
      <button onclick="limparFiltros()" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
        Limpar Filtros
      </button>
      <button onclick="aplicarFiltros()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
        Aplicar Filtros
      </button>
    </div>
    
    <!-- Grid de Solicitações -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-4 py-3 border-b"><h3 class="font-medium">Minhas Solicitações</h3></div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left">ID</th>
            <th class="px-3 py-2 text-left">Data</th>
            <th class="px-3 py-2 text-left">Processo</th>
            <th class="px-3 py-2 text-left">Setor</th>
            <th class="px-3 py-2 text-left">Status</th>
            <th class="px-3 py-2 text-left">Anexos</th>
            <th class="px-3 py-2 text-left">Responsáveis</th>
            <th class="px-3 py-2 text-left">Ações</th>
          </tr>
        </thead>
        <tbody id="gridBody" class="divide-y">
          <tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Sem registros</td></tr>
        </tbody>
      </table>
    </div>
    </div>
  </div>
</section>

<script>
function toggleSolicitacaoForm(){
  const el = document.getElementById('solicitacaoFormContainer');
  el.classList.toggle('hidden');
}
function cancelSolicitacaoForm(){
  document.getElementById('solicitacaoForm').reset();
  document.getElementById('fileList').innerHTML = '';
  document.getElementById('solicitacaoFormContainer').classList.add('hidden');
}
function updateFileList(){
  const inp = document.getElementById('fileInput');
  const list = document.getElementById('fileList');
  list.innerHTML = '';
  if (!inp.files) return;
  if (inp.files.length > 5){ alert('Máximo 5 arquivos'); inp.value=''; return; }
  [...inp.files].forEach(f=>{
    if (f.size > 5*1024*1024){ alert('Arquivo acima de 5MB: '+f.name); inp.value=''; list.innerHTML=''; return; }
    const div = document.createElement('div');
    div.textContent = `${f.name} (${(f.size/1024/1024).toFixed(2)} MB)`;
    list.appendChild(div);
  });
}

async function createSolicitacao(e){
  e.preventDefault();
  e.stopPropagation(); // Evita que o handler global de submit interfira
  
  const overlay = document.getElementById('loadingOverlay');
  const form = document.getElementById('solicitacaoForm');
  const submitBtn = document.getElementById('submitBtn');
  
  try {
    // Mostrar overlay e desabilitar botão
    if (overlay) overlay.classList.add('active');
    if (submitBtn) { 
      submitBtn.disabled = true; 
      submitBtn.textContent = 'Enviando...'; 
    }
    
    const fd = new FormData(form);
    const resp = await fetch('/melhoria-continua/solicitacoes/create', { 
      method: 'POST', 
      body: fd 
    });
    
    const json = await resp.json().catch(() => ({
      success: false, 
      message: 'Erro no servidor'
    }));
    
    alert(json.message || (json.success ? 'Sucesso' : 'Erro'));
    
    if (json.success) {
      cancelSolicitacaoForm();
      await loadGrid();
    }
  } catch(err) {
    console.error(err);
    alert('Falha ao enviar solicitação.');
  } finally {
    // Sempre remover overlay e reabilitar botão
    if (overlay) overlay.classList.remove('active');
    if (submitBtn) { 
      submitBtn.disabled = false; 
      submitBtn.textContent = 'Enviar'; 
    }
  }
}

let todasAsSolicitacoes = []; // Armazena todas as solicitações para filtrar localmente
let setoresUnicos = new Set(); // Para popular o dropdown de setores
let responsaveisUnicos = new Set(); // Para popular o dropdown de responsáveis

async function loadGrid(){
  let overlay = document.getElementById('loadingOverlay');
  try {
    const resp = await fetch('/melhoria-continua/solicitacoes/list');
    const json = await resp.json().catch(()=>({success:false,data:[]}));
    
    if (json.data && json.data.length > 0) {
      todasAsSolicitacoes = json.data;
      
      // Popular dropdowns
      setoresUnicos.clear();
      responsaveisUnicos.clear();
      
      json.data.forEach(row => {
        if (row.setor) setoresUnicos.add(row.setor);
        if (row.responsaveis && Array.isArray(row.responsaveis)) {
          row.responsaveis.forEach(resp => responsaveisUnicos.add(resp));
        }
      });
      
      // Popular dropdown de setores
      const selectSetor = document.getElementById('filtroSetor');
      while (selectSetor.children.length > 1) {
        selectSetor.removeChild(selectSetor.lastChild);
      }
      Array.from(setoresUnicos).sort().forEach(setor => {
        const option = document.createElement('option');
        option.value = setor;
        option.textContent = setor;
        selectSetor.appendChild(option);
      });
      
      // Popular dropdown de responsáveis
      const selectResponsavel = document.getElementById('filtroResponsavel');
      while (selectResponsavel.children.length > 1) {
        selectResponsavel.removeChild(selectResponsavel.lastChild);
      }
      Array.from(responsaveisUnicos).sort().forEach(responsavel => {
        const option = document.createElement('option');
        option.value = responsavel;
        option.textContent = responsavel;
        selectResponsavel.appendChild(option);
      });
    }
    
    renderizarTabela(todasAsSolicitacoes);
  } finally {
    if (overlay) overlay.classList.remove('active');
  }
}

function renderizarTabela(dados) {
  const body = document.getElementById('gridBody');
  body.innerHTML = '';
  
  if (!dados || dados.length === 0) {
    body.innerHTML = '<tr><td colspan="8" class="px-3 py-4 text-center text-gray-500">Sem registros encontrados</td></tr>';
    return;
  }
  
  dados.forEach(row => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="px-3 py-2">${row.id}</td>
      <td class="px-3 py-2">${row.data || ''}</td>
      <td class="px-3 py-2">${row.processo || ''}</td>
      <td class="px-3 py-2">${row.setor || ''}</td>
      <td class="px-3 py-2">
        <span class="px-2 py-1 text-xs rounded-full ${getStatusColor(row.status)}">
          ${row.status || ''}
        </span>
      </td>
      <td class="px-3 py-2"><a class="text-blue-600 hover:underline" href="/melhoria-continua/solicitacoes/${row.id}/anexos" target="_blank">Ver anexos</a></td>
      <td class="px-3 py-2">${(row.responsaveis||[]).join(', ')}</td>
      <td class="px-3 py-2 space-x-2"><button class="px-2 py-1 text-xs bg-red-600 text-white rounded" onclick="excluir(${row.id})">Excluir</button></td>`;
    body.appendChild(tr);
  });
}

function getStatusColor(status) {
  switch(status) {
    case 'Pendente': return 'bg-yellow-100 text-yellow-800';
    case 'Em Análise': return 'bg-blue-100 text-blue-800';
    case 'Aprovado': return 'bg-green-100 text-green-800';
    case 'Rejeitado': return 'bg-red-100 text-red-800';
    case 'Implementado': return 'bg-purple-100 text-purple-800';
    default: return 'bg-gray-100 text-gray-800';
  }
}

function aplicarFiltros() {
  const setor = document.getElementById('filtroSetor').value;
  const status = document.getElementById('filtroStatus').value;
  const processo = document.getElementById('filtroProcesso').value.toLowerCase();
  const responsavel = document.getElementById('filtroResponsavel').value;
  
  let dadosFiltrados = [...todasAsSolicitacoes];
  
  // Filtro por setor
  if (setor) {
    dadosFiltrados = dadosFiltrados.filter(row => row.setor === setor);
  }
  
  // Filtro por status
  if (status) {
    dadosFiltrados = dadosFiltrados.filter(row => row.status === status);
  }
  
  // Filtro por processo
  if (processo) {
    dadosFiltrados = dadosFiltrados.filter(row => 
      row.processo && row.processo.toLowerCase().includes(processo)
    );
  }
  
  // Filtro por responsável
  if (responsavel) {
    dadosFiltrados = dadosFiltrados.filter(row => 
      row.responsaveis && row.responsaveis.includes(responsavel)
    );
  }
  
  renderizarTabela(dadosFiltrados);
  
  // Feedback visual
  const totalFiltrados = dadosFiltrados.length;
  const totalOriginal = todasAsSolicitacoes.length;
  
  if (totalFiltrados !== totalOriginal) {
    const mensagem = `Mostrando ${totalFiltrados} de ${totalOriginal} solicitações`;
    console.log(mensagem);
    
    // Adicionar indicador visual de filtro ativo
    const header = document.querySelector('.bg-white.rounded-lg.shadow.overflow-hidden .px-4.py-3.border-b');
    let indicador = header.querySelector('.filtro-ativo');
    if (!indicador) {
      indicador = document.createElement('div');
      indicador.className = 'filtro-ativo text-xs text-blue-600 font-medium';
      header.appendChild(indicador);
    }
    indicador.textContent = mensagem;
  }
}

function limparFiltros() {
  document.getElementById('filtroSetor').value = '';
  document.getElementById('filtroStatus').value = '';
  document.getElementById('filtroProcesso').value = '';
  document.getElementById('filtroResponsavel').value = '';
  
  // Remover indicador de filtro ativo
  const indicador = document.querySelector('.filtro-ativo');
  if (indicador) indicador.remove();
  
  renderizarTabela(todasAsSolicitacoes);
}

async function excluir(id){
  if (!confirm('Deseja excluir esta melhoria?')) return;
  const overlay = document.getElementById('loadingOverlay');
  if (overlay) overlay.classList.add('active');
  try {
    const fd = new FormData(); fd.append('id', id);
    const resp = await fetch('/melhoria-continua/pendentes/delete', { method:'POST', body: fd });
    const json = await resp.json().catch(()=>({success:false,message:'Erro'}));
    alert(json.message || (json.success ? 'Excluído' : 'Erro'));
    if (json.success) await loadGrid();
  } catch(err){
    console.error(err);
    alert('Falha ao excluir.');
  } finally {
    if (overlay) overlay.classList.remove('active');
  }
}

document.getElementById('solicitacaoForm').addEventListener('submit', createSolicitacao);
window.addEventListener('load', loadGrid);
</script>
