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

  <div id="solicitacaoFormContainer" class="hidden bg-white border border-gray-200 rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Nova Solicitação de Melhoria</h3>
      <button onclick="cancelSolicitacaoForm()" class="text-gray-400 hover:text-gray-600 transition-colors" title="Fechar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>

    <form id="solicitacaoForm" class="space-y-4" enctype="multipart/form-data">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600">Data</label>
          <input type="text" value="<?= date('d/m/Y H:i') ?>" readonly class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600">Setor *</label>
          <select name="setor" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">Selecione...</option>
            <?php foreach (($setores ?? []) as $setor): ?>
              <option value="<?= htmlspecialchars($setor) ?>"><?= htmlspecialchars($setor) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600">Status</label>
          <input type="text" value="Pendente" readonly class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-yellow-50 text-yellow-800">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600">Processo *</label>
          <input type="text" name="processo" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Descreva o processo">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600">Resultado Esperado *</label>
          <input type="text" name="resultado_esperado" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Resultado esperado">
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Descrição da Melhoria *</label>
        <textarea name="descricao_melhoria" rows="3" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Descreva a melhoria..."></textarea>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Observações</label>
        <textarea name="observacoes" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Observações (opcional)"></textarea>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Responsáveis *</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 border border-gray-200 rounded p-3 bg-gray-50 max-h-48 overflow-auto">
          <?php foreach (($usuarios ?? []) as $u): ?>
            <label class="flex items-center space-x-2">
              <input type="checkbox" class="h-4 w-4" name="responsaveis[]" value="<?= (int)$u['id'] ?>" data-email="<?= htmlspecialchars($u['email']) ?>">
              <span class="text-sm text-gray-800"><?= htmlspecialchars($u['name']) ?> <span class="text-gray-400 text-xs">(<?= htmlspecialchars($u['email']) ?>)</span></span>
            </label>
          <?php endforeach; ?>
        </div>
        <p class="text-xs text-gray-500 mt-1">Os responsáveis selecionados receberão email de notificação.</p>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Anexos (até 5 arquivos, 5MB cada) - JPG, PNG, GIF, PDF</label>
        <input id="fileInput" type="file" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" onchange="updateFileList()">
        <div id="fileList" class="mt-1 text-xs text-gray-600 space-y-1"></div>
      </div>

      <div class="flex justify-end space-x-2 pt-2 border-t">
        <button type="button" onclick="cancelSolicitacaoForm()" class="px-4 py-2 text-sm bg-gray-200 rounded">Cancelar</button>
        <button id="submitBtn" type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded">Enviar</button>
      </div>
    </form>
  </div>

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
  // Evita que o handler global de submit deixe o overlay preso
  e.preventDefault();
  e.stopPropagation();
  const overlay = document.getElementById('loadingOverlay');
  if (overlay) overlay.classList.add('active');

  const form = document.getElementById('solicitacaoForm');
  const submitBtn = document.getElementById('submitBtn');
  const fd = new FormData(form);
  try {
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Enviando...'; }
    const resp = await fetch('/melhoria-continua/solicitacoes/create', { method:'POST', body: fd });
    const json = await resp.json().catch(()=>({success:false,message:'Erro no servidor'}));
    alert(json.message || (json.success?'Sucesso':'Erro'));
    if (json.success){
      cancelSolicitacaoForm();
      await loadGrid();
    }
  } catch(err){
    console.error(err);
    alert('Falha ao enviar solicitação.');
  } finally {
    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Enviar'; }
    if (overlay) overlay.classList.remove('active');
  }
}

async function loadGrid(){
  let overlay = document.getElementById('loadingOverlay');
  try {
    const resp = await fetch('/melhoria-continua/solicitacoes/list');
    const json = await resp.json().catch(()=>({success:false,data:[]}));
    const body = document.getElementById('gridBody');
    body.innerHTML = '';
    if (!json.data || json.data.length === 0){
      body.innerHTML = '<tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Sem registros</td></tr>';
      return;
    }
    json.data.forEach(row=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-3 py-2">${row.id}</td>
        <td class="px-3 py-2">${row.data || ''}</td>
        <td class="px-3 py-2">${row.processo || ''}</td>
        <td class="px-3 py-2">${row.setor || ''}</td>
        <td class="px-3 py-2">${row.status || ''}</td>
        <td class="px-3 py-2">${(row.responsaveis||[]).join(', ')}</td>
        <td class="px-3 py-2">-</td>`;
      body.appendChild(tr);
    });
  } finally {
    if (overlay) overlay.classList.remove('active');
  }
}

document.getElementById('solicitacaoForm').addEventListener('submit', createSolicitacao);
window.addEventListener('load', loadGrid);
</script>
