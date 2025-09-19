<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Melhorias Pendentes</h1>
  </div>

  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-4 py-3 border-b flex justify-between items-center">
      <h3 class="font-medium">Pendentes atribuídas a mim</h3>
      <div class="text-sm text-gray-500">Atualize o status e registre observações</div>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left">ID</th>
            <th class="px-3 py-2 text-left">Data</th>
            <th class="px-3 py-2 text-left">Processo</th>
            <th class="px-3 py-2 text-left">Setor</th>
            <th class="px-3 py-2 text-left">Status</th>
            <th class="px-3 py-2 text-left">Observação</th>
            <th class="px-3 py-2 text-left">Anexos</th>
            <th class="px-3 py-2 text-left">Ações</th>
          </tr>
        </thead>
        <tbody id="gridPendentes" class="divide-y">
          <tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Carregando...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
async function loadPendentes(){
  const resp = await fetch('/melhoria-continua/pendentes/list');
  const json = await resp.json().catch(()=>({success:false,data:[]}));
  const body = document.getElementById('gridPendentes');
  body.innerHTML = '';
  if (!json.data || json.data.length === 0){
    body.innerHTML = '<tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">Sem pendências</td></tr>';
    return;
  }
  json.data.forEach(row=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="px-3 py-2">${row.id}</td>
      <td class="px-3 py-2">${row.data || ''}</td>
      <td class="px-3 py-2">${row.processo || ''}</td>
      <td class="px-3 py-2">${row.setor || ''}</td>
      <td class="px-3 py-2">
        <select class="border rounded px-2 py-1 text-sm" data-id="${row.id}" onchange="updateStatus(this)">
          <option value="pendente" ${row.status==='pendente'?'selected':''}>Pendente</option>
          <option value="aprovado" ${row.status==='aprovado'?'selected':''}>Aprovado</option>
          <option value="aprovado_obs" ${row.status==='aprovado_obs'?'selected':''}>Aprovado c/ observações</option>
          <option value="reprovado" ${row.status==='reprovado'?'selected':''}>Reprovado</option>
        </select>
      </td>
      <td class="px-3 py-2"><input type="text" class="border rounded px-2 py-1 text-sm w-64" value="${row.observacoes||''}" data-obs-for="${row.id}" placeholder="Digite a observação"></td>
      <td class="px-3 py-2"><a class="text-blue-600 hover:underline" href="/melhoria-continua/solicitacoes/${row.id}/anexos" target="_blank">Ver anexos</a></td>
      <td class="px-3 py-2 space-x-2">
        <button class="px-2 py-1 text-xs bg-blue-600 text-white rounded" onclick="salvar(${row.id})">Salvar</button>
      </td>`;
    body.appendChild(tr);
  });
}

function updateStatus(sel){
  const id = sel.getAttribute('data-id');
  const obs = document.querySelector(`[data-obs-for="${id}"]`);
  if (!obs.value.trim()){
    alert('Informe a observação para alterar o status.');
    sel.value = 'pendente';
  }
}

async function salvar(id){
  const sel = document.querySelector(`select[data-id="${id}"]`);
  const obs = document.querySelector(`[data-obs-for="${id}"]`);
  if (!obs.value.trim()) { alert('Observação é obrigatória.'); return; }
  const fd = new FormData();
  fd.append('id', id);
  fd.append('status', sel.value);
  fd.append('observacoes', obs.value);
  const resp = await fetch('/melhoria-continua/pendentes/update-status', { method:'POST', body: fd });
  const json = await resp.json().catch(()=>({success:false,message:'Erro'}));
  alert(json.message || (json.success ? 'Atualizado' : 'Erro'));
  if (json.success) loadPendentes();
}

async function excluir(id){
  if (!confirm('Deseja excluir esta melhoria?')) return;
  const fd = new FormData(); fd.append('id', id);
  const resp = await fetch('/melhoria-continua/pendentes/delete', { method:'POST', body: fd });
  const json = await resp.json().catch(()=>({success:false,message:'Erro'}));
  alert(json.message || (json.success ? 'Excluído' : 'Erro'));
  if (json.success) loadPendentes();
}

window.addEventListener('load', loadPendentes);
</script>
