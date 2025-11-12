<section class="space-y-6">
  <div class="flex justify-between items-center">
    <div>
      <a href="/nps" class="text-blue-600 hover:text-blue-700 text-sm mb-2 inline-block flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Voltar para Formulários
      </a>
      <h1 class="text-2xl font-semibold text-gray-900"><?= e($formulario['titulo']) ?></h1>
      <p class="text-gray-600 mt-1">
        <span class="font-medium">Total de respostas:</span> <span id="contadorTotal"><?= count($respostas) ?></span>
        <span class="text-gray-400 ml-2">|</span>
        <span class="ml-2">Exibindo: <span id="contadorFiltrado"><?= count($respostas) ?></span></span>
      </p>
    </div>
    
    <div class="text-right">
      <p class="text-sm text-gray-500">Criado por: <?= e($formulario['criado_por_nome']) ?></p>
      <p class="text-sm text-gray-500">Em: <?= date('d/m/Y H:i', strtotime($formulario['criado_em'])) ?></p>
    </div>
  </div>

  <?php if (empty($respostas)): ?>
    <!-- Sem Respostas -->
    <div class="bg-white rounded-lg shadow p-12 text-center">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <p class="text-gray-600 mb-2">Nenhuma resposta ainda</p>
      <p class="text-sm text-gray-500">Compartilhe o link público para começar a receber respostas</p>
    </div>
  
  <?php else: ?>
    <!-- Barra de Filtros e Controles -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <!-- Busca por Nome/Email -->
        <div class="md:col-span-2">
          <label class="block text-xs font-medium text-gray-700 mb-1">🔍 Buscar</label>
          <input type="text" id="filtroBusca" placeholder="Nome ou email..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <!-- Data Inicial -->
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">📅 De</label>
          <input type="date" id="filtroDataInicio" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <!-- Data Final -->
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">📅 Até</label>
          <input type="date" id="filtroDataFim" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>
      
      <div class="flex justify-between items-center pt-3 border-t border-gray-200">
        <!-- Filtros Rápidos -->
        <div class="flex items-center space-x-2">
          <button onclick="aplicarFiltroRapido('todos')" class="filtro-rapido active px-3 py-1 text-xs rounded-full border border-gray-300 hover:bg-gray-50 transition-colors" data-filtro="todos">
            Todos
          </button>
          <button onclick="aplicarFiltroRapido('hoje')" class="filtro-rapido px-3 py-1 text-xs rounded-full border border-gray-300 hover:bg-gray-50 transition-colors" data-filtro="hoje">
            Hoje
          </button>
          <button onclick="aplicarFiltroRapido('semana')" class="filtro-rapido px-3 py-1 text-xs rounded-full border border-gray-300 hover:bg-gray-50 transition-colors" data-filtro="semana">
            Esta Semana
          </button>
          <button onclick="aplicarFiltroRapido('mes')" class="filtro-rapido px-3 py-1 text-xs rounded-full border border-gray-300 hover:bg-gray-50 transition-colors" data-filtro="mes">
            Este Mês
          </button>
          <button onclick="limparFiltros()" class="px-3 py-1 text-xs text-red-600 hover:bg-red-50 rounded-full transition-colors">
            Limpar
          </button>
        </div>
        
        <!-- Toggle Visualização -->
        <div class="flex items-center space-x-2">
          <span class="text-xs text-gray-600">Visualização:</span>
          <button onclick="alternarVisualizacao('grid')" id="btnGrid" class="p-2 rounded hover:bg-gray-100 transition-colors bg-blue-50 text-blue-600" title="Grid">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
          </button>
          <button onclick="alternarVisualizacao('lista')" id="btnLista" class="p-2 rounded hover:bg-gray-100 transition-colors" title="Lista">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Container de Respostas (Grid/Lista) -->
    <div id="respostasContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($respostas as $resposta): ?>
        <div class="resposta-card bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6" 
             data-nome="<?= strtolower(e($resposta['nome'])) ?>"
             data-email="<?= strtolower(e($resposta['email'] ?? '')) ?>"
             data-data="<?= date('Y-m-d', strtotime($resposta['respondido_em'])) ?>">
          
          <!-- Cabeçalho da Resposta -->
          <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-200">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-900 flex items-center truncate">
                <svg class="w-5 h-5 mr-2 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="truncate"><?= e($resposta['nome']) ?></span>
              </h3>
              <?php if ($resposta['email']): ?>
                <p class="text-xs text-gray-600 mt-1 ml-7 truncate"><?= e($resposta['email']) ?></p>
              <?php endif; ?>
            </div>
            <div class="flex items-center space-x-2 ml-2 flex-shrink-0">
              <div class="text-right">
                <span class="text-xs font-medium text-gray-700 block">
                  <?= date('d/m/Y', strtotime($resposta['respondido_em'])) ?>
                </span>
                <p class="text-xs text-gray-500">
                  <?= date('H:i', strtotime($resposta['respondido_em'])) ?>
                </p>
              </div>
              <?php if ($podeExcluir): ?>
                <button onclick="excluirResposta('<?= e($resposta['id']) ?>')" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors" title="Excluir resposta">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- Respostas -->
          <div class="space-y-3 max-h-64 overflow-y-auto">
            <?php foreach ($resposta['respostas'] as $r): ?>
              <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs font-medium text-gray-700 mb-1">
                  <?= e($r['pergunta']) ?>
                </p>
                <p class="text-sm text-gray-900 <?= is_numeric($r['resposta']) && $r['resposta'] >= 9 ? 'text-green-600 font-bold' : '' ?>">
                  <?= e($r['resposta']) ?>
                  <?php if (is_numeric($r['resposta']) && $r['resposta'] <= 10): ?>
                    <span class="text-xs text-gray-500">/ 10</span>
                  <?php endif; ?>
                </p>
              </div>
            <?php endforeach; ?>
          </div>
          
          <!-- Metadados -->
          <div class="mt-4 pt-3 border-t border-gray-200 flex items-center justify-between text-xs text-gray-400">
            <span class="truncate">IP: <?= e($resposta['ip']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Mensagem quando não há resultados -->
    <div id="semResultados" class="hidden bg-white rounded-lg shadow p-12 text-center">
      <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
      <p class="text-gray-600 mb-2">Nenhuma resposta encontrada</p>
      <p class="text-sm text-gray-500">Tente ajustar os filtros</p>
    </div>

    <!-- Estatísticas (se houver perguntas numéricas) -->
    <?php
    // Calcular NPS se houver perguntas numéricas
    $perguntasNumericas = array_filter($formulario['perguntas'], fn($p) => $p['tipo'] === 'numero');
    if (!empty($perguntasNumericas)):
    ?>
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Estatísticas</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <?php
          $promotores = 0;
          $neutros = 0;
          $detratores = 0;
          
          foreach ($respostas as $resp) {
            foreach ($resp['respostas'] as $r) {
              if (is_numeric($r['resposta'])) {
                $nota = (int)$r['resposta'];
                if ($nota >= 9) $promotores++;
                elseif ($nota >= 7) $neutros++;
                else $detratores++;
              }
            }
          }
          
          $total = $promotores + $neutros + $detratores;
          $nps = $total > 0 ? (($promotores - $detratores) / $total) * 100 : 0;
          ?>
          
          <div class="bg-white rounded-lg p-4 text-center">
            <p class="text-sm text-gray-600 mb-1">Promotores (9-10)</p>
            <p class="text-3xl font-bold text-green-600"><?= $promotores ?></p>
            <p class="text-xs text-gray-500"><?= $total > 0 ? round(($promotores/$total)*100, 1) : 0 ?>%</p>
          </div>
          
          <div class="bg-white rounded-lg p-4 text-center">
            <p class="text-sm text-gray-600 mb-1">Neutros (7-8)</p>
            <p class="text-3xl font-bold text-yellow-600"><?= $neutros ?></p>
            <p class="text-xs text-gray-500"><?= $total > 0 ? round(($neutros/$total)*100, 1) : 0 ?>%</p>
          </div>
          
          <div class="bg-white rounded-lg p-4 text-center">
            <p class="text-sm text-gray-600 mb-1">Detratores (0-6)</p>
            <p class="text-3xl font-bold text-red-600"><?= $detratores ?></p>
            <p class="text-xs text-gray-500"><?= $total > 0 ? round(($detratores/$total)*100, 1) : 0 ?>%</p>
          </div>
        </div>
        
        <div class="mt-6 bg-white rounded-lg p-6 text-center">
          <p class="text-sm text-gray-600 mb-2">Net Promoter Score (NPS)</p>
          <p class="text-5xl font-bold <?= $nps >= 50 ? 'text-green-600' : ($nps >= 0 ? 'text-yellow-600' : 'text-red-600') ?>">
            <?= round($nps) ?>
          </p>
          <p class="text-sm text-gray-500 mt-2">
            <?php if ($nps >= 75): ?>
              Excelente! Zona de Excelência
            <?php elseif ($nps >= 50): ?>
              Muito Bom! Zona de Qualidade
            <?php elseif ($nps >= 0): ?>
              Zona de Aperfeiçoamento
            <?php else: ?>
              Zona Crítica - Atenção Necessária
            <?php endif; ?>
          </p>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>

<style>
.filtro-rapido.active {
  background-color: #3B82F6;
  color: white;
  border-color: #3B82F6;
}
</style>

<script>
let visualizacaoAtual = 'grid';

// Aplicar filtros
function aplicarFiltros() {
  const busca = document.getElementById('filtroBusca').value.toLowerCase();
  const dataInicio = document.getElementById('filtroDataInicio').value;
  const dataFim = document.getElementById('filtroDataFim').value;
  
  const cards = document.querySelectorAll('.resposta-card');
  let visiveis = 0;
  
  cards.forEach(card => {
    const nome = card.dataset.nome || '';
    const email = card.dataset.email || '';
    const data = card.dataset.data;
    
    // Filtro de busca
    const matchBusca = !busca || nome.includes(busca) || email.includes(busca);
    
    // Filtro de data
    let matchData = true;
    if (dataInicio && data < dataInicio) matchData = false;
    if (dataFim && data > dataFim) matchData = false;
    
    if (matchBusca && matchData) {
      card.style.display = '';
      visiveis++;
    } else {
      card.style.display = 'none';
    }
  });
  
  // Atualizar contador
  document.getElementById('contadorFiltrado').textContent = visiveis;
  
  // Mostrar/ocultar mensagem de sem resultados
  const container = document.getElementById('respostasContainer');
  const semResultados = document.getElementById('semResultados');
  
  if (visiveis === 0) {
    container.style.display = 'none';
    semResultados.classList.remove('hidden');
  } else {
    container.style.display = '';
    semResultados.classList.add('hidden');
  }
}

// Filtros rápidos
function aplicarFiltroRapido(tipo) {
  const hoje = new Date();
  let dataInicio = '';
  
  // Remover active de todos
  document.querySelectorAll('.filtro-rapido').forEach(btn => {
    btn.classList.remove('active');
  });
  
  // Adicionar active no botão clicado
  event.target.classList.add('active');
  
  if (tipo === 'todos') {
    document.getElementById('filtroDataInicio').value = '';
    document.getElementById('filtroDataFim').value = '';
  } else if (tipo === 'hoje') {
    dataInicio = hoje.toISOString().split('T')[0];
    document.getElementById('filtroDataInicio').value = dataInicio;
    document.getElementById('filtroDataFim').value = dataInicio;
  } else if (tipo === 'semana') {
    const primeiroDia = new Date(hoje);
    primeiroDia.setDate(hoje.getDate() - hoje.getDay());
    dataInicio = primeiroDia.toISOString().split('T')[0];
    document.getElementById('filtroDataInicio').value = dataInicio;
    document.getElementById('filtroDataFim').value = hoje.toISOString().split('T')[0];
  } else if (tipo === 'mes') {
    const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    dataInicio = primeiroDia.toISOString().split('T')[0];
    document.getElementById('filtroDataInicio').value = dataInicio;
    document.getElementById('filtroDataFim').value = hoje.toISOString().split('T')[0];
  }
  
  aplicarFiltros();
}

// Limpar filtros
function limparFiltros() {
  document.getElementById('filtroBusca').value = '';
  document.getElementById('filtroDataInicio').value = '';
  document.getElementById('filtroDataFim').value = '';
  
  // Marcar "Todos" como ativo
  document.querySelectorAll('.filtro-rapido').forEach(btn => {
    btn.classList.remove('active');
    if (btn.dataset.filtro === 'todos') {
      btn.classList.add('active');
    }
  });
  
  aplicarFiltros();
}

// Alternar visualização
function alternarVisualizacao(tipo) {
  visualizacaoAtual = tipo;
  const container = document.getElementById('respostasContainer');
  const btnGrid = document.getElementById('btnGrid');
  const btnLista = document.getElementById('btnLista');
  
  if (tipo === 'grid') {
    container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4';
    btnGrid.classList.add('bg-blue-50', 'text-blue-600');
    btnGrid.classList.remove('text-gray-600');
    btnLista.classList.remove('bg-blue-50', 'text-blue-600');
    btnLista.classList.add('text-gray-600');
  } else {
    container.className = 'space-y-4';
    btnLista.classList.add('bg-blue-50', 'text-blue-600');
    btnLista.classList.remove('text-gray-600');
    btnGrid.classList.remove('bg-blue-50', 'text-blue-600');
    btnGrid.classList.add('text-gray-600');
  }
}

// Event listeners
document.getElementById('filtroBusca').addEventListener('input', aplicarFiltros);
document.getElementById('filtroDataInicio').addEventListener('change', aplicarFiltros);
document.getElementById('filtroDataFim').addEventListener('change', aplicarFiltros);

// Excluir resposta (apenas admin/super_admin)
function excluirResposta(respostaId) {
  if (!confirm('Tem certeza que deseja excluir esta resposta? Esta ação não pode ser desfeita!')) {
    return;
  }
  
  const formData = new FormData();
  formData.append('resposta_id', respostaId);
  
  fetch('/nps/excluir-resposta', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      location.reload(); // Recarregar a página para atualizar a lista
    } else {
      alert('Erro: ' + data.message);
    }
  })
  .catch(err => {
    console.error(err);
    alert('Erro de conexão');
  });
}
</script>
