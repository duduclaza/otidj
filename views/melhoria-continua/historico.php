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
            <th class="px-3 py-2 text-left">Ações</th>
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
let todosOsLogs = []; // Armazena todos os logs para filtrar localmente
let usuariosUnicos = new Set(); // Para popular o dropdown de usuários

async function loadHistorico(){
  const resp = await fetch('/melhoria-continua/historico/logs');
  const json = await resp.json().catch(()=>({success:false,data:[]}));
  
  if (json.data && json.data.length > 0) {
    todosOsLogs = json.data;
    
    // Popular dropdown de usuários
    usuariosUnicos.clear();
    json.data.forEach(row => {
      if (row.usuario) usuariosUnicos.add(row.usuario);
    });
    
    const selectUsuario = document.getElementById('filtroUsuario');
    // Limpar opções existentes (exceto "Todos")
    while (selectUsuario.children.length > 1) {
      selectUsuario.removeChild(selectUsuario.lastChild);
    }
    
    // Adicionar usuários únicos
    Array.from(usuariosUnicos).sort().forEach(usuario => {
      const option = document.createElement('option');
      option.value = usuario;
      option.textContent = usuario;
      selectUsuario.appendChild(option);
    });
  }
  
  renderizarTabela(todosOsLogs);
}

function renderizarTabela(dados) {
  const body = document.getElementById('gridHistorico');
  body.innerHTML = '';
  
  if (!dados || dados.length === 0) {
    body.innerHTML = '<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Sem logs encontrados</td></tr>';
    return;
  }
  
  dados.forEach(row => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="px-3 py-2">${row.data || ''}</td>
      <td class="px-3 py-2">#${row.solicitacao_id || ''}</td>
      <td class="px-3 py-2">${row.usuario || ''}</td>
      <td class="px-3 py-2">${row.acao || ''}</td>
      <td class="px-3 py-2">${row.detalhes || ''}</td>
      <td class="px-3 py-2">
        <a class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" href="/melhoria-continua/solicitacoes/${row.solicitacao_id}/anexos" target="_blank">Anexos</a>
      </td>`;
    body.appendChild(tr);
  });
}

function aplicarFiltros() {
  const dataInicial = document.getElementById('filtroDataInicial').value;
  const dataFinal = document.getElementById('filtroDataFinal').value;
  const usuario = document.getElementById('filtroUsuario').value;
  const acao = document.getElementById('filtroAcao').value;
  
  let dadosFiltrados = [...todosOsLogs];
  
  // Filtro por data inicial
  if (dataInicial) {
    dadosFiltrados = dadosFiltrados.filter(row => {
      if (!row.data) return false;
      const dataRow = new Date(row.data.split('/').reverse().join('-')); // Converte dd/mm/yyyy para yyyy-mm-dd
      const dataFiltro = new Date(dataInicial);
      return dataRow >= dataFiltro;
    });
  }
  
  // Filtro por data final
  if (dataFinal) {
    dadosFiltrados = dadosFiltrados.filter(row => {
      if (!row.data) return false;
      const dataRow = new Date(row.data.split('/').reverse().join('-')); // Converte dd/mm/yyyy para yyyy-mm-dd
      const dataFiltro = new Date(dataFinal);
      return dataRow <= dataFiltro;
    });
  }
  
  // Filtro por usuário
  if (usuario) {
    dadosFiltrados = dadosFiltrados.filter(row => 
      row.usuario && row.usuario.toLowerCase().includes(usuario.toLowerCase())
    );
  }
  
  // Filtro por ação
  if (acao) {
    dadosFiltrados = dadosFiltrados.filter(row => 
      row.acao && row.acao.toLowerCase().includes(acao.toLowerCase())
    );
  }
  
  renderizarTabela(dadosFiltrados);
  
  // Feedback visual
  const totalFiltrados = dadosFiltrados.length;
  const totalOriginal = todosOsLogs.length;
  
  if (totalFiltrados !== totalOriginal) {
    const mensagem = `Mostrando ${totalFiltrados} de ${totalOriginal} registros`;
    console.log(mensagem);
    
    // Adicionar indicador visual de filtro ativo
    const headerLogs = document.querySelector('.bg-white.rounded-lg.shadow.overflow-hidden .px-4.py-3.border-b');
    let indicador = headerLogs.querySelector('.filtro-ativo');
    if (!indicador) {
      indicador = document.createElement('div');
      indicador.className = 'filtro-ativo text-xs text-blue-600 font-medium';
      headerLogs.appendChild(indicador);
    }
    indicador.textContent = mensagem;
  }
}

function limparFiltros() {
  document.getElementById('filtroDataInicial').value = '';
  document.getElementById('filtroDataFinal').value = '';
  document.getElementById('filtroUsuario').value = '';
  document.getElementById('filtroAcao').value = '';
  
  // Remover indicador de filtro ativo
  const indicador = document.querySelector('.filtro-ativo');
  if (indicador) indicador.remove();
  
  renderizarTabela(todosOsLogs);
}

// Definir datas padrão (últimos 30 dias)
function definirDatasPadrao() {
  const hoje = new Date();
  const trintaDiasAtras = new Date();
  trintaDiasAtras.setDate(hoje.getDate() - 30);
  
  document.getElementById('filtroDataFinal').value = hoje.toISOString().split('T')[0];
  document.getElementById('filtroDataInicial').value = trintaDiasAtras.toISOString().split('T')[0];
}

window.addEventListener('load', () => {
  definirDatasPadrao();
  loadHistorico();
});
</script>
