<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Histórico de Melhorias</h1>
  </div>

  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-4 py-3 border-b flex justify-between items-center">
      <h3 class="font-medium">Logs de eventos</h3>
      <div class="text-sm text-gray-500">Todas as alterações e ações realizadas</div>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left">Data</th>
            <th class="px-3 py-2 text-left">Solicitação</th>
            <th class="px-3 py-2 text-left">Usuário</th>
            <th class="px-3 py-2 text-left">Ação</th>
            <th class="px-3 py-2 text-left">Detalhes</th>
          </tr>
        </thead>
        <tbody id="gridHistorico" class="divide-y">
          <tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Carregando...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
async function loadHistorico(){
  const resp = await fetch('/melhoria-continua/historico/logs');
  const json = await resp.json().catch(()=>({success:false,data:[]}));
  const body = document.getElementById('gridHistorico');
  body.innerHTML = '';
  if (!json.data || json.data.length === 0){
    body.innerHTML = '<tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Sem logs</td></tr>';
    return;
  }
  json.data.forEach(row=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="px-3 py-2">${row.data || ''}</td>
      <td class="px-3 py-2">#${row.solicitacao_id || ''}</td>
      <td class="px-3 py-2">${row.usuario || ''}</td>
      <td class="px-3 py-2">${row.acao || ''}</td>
      <td class="px-3 py-2">${row.detalhes || ''}</td>`;
    body.appendChild(tr);
  });
}

window.addEventListener('load', loadHistorico);
</script>
