<?php
$trialInfo = $trialInfo ?? ['ativo' => false, 'dias_restantes' => 0, 'status' => 'nao_iniciado'];
$moduloBloqueado = !$trialInfo['ativo'];
?>

<section class="space-y-6">
  <!-- Header -->
  <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
      <div class="flex items-center gap-2">
        <a href="/area-tecnica" class="text-gray-500 hover:text-gray-700">🔧 Área Técnica</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 font-medium">Consulta de Checklists</span>
      </div>
      <h1 class="text-2xl font-semibold text-gray-900 mt-2">🔍 Consulta de Checklists</h1>
      <p class="text-sm text-gray-600 mt-1">Pesquise o histórico de manutenções por número de série</p>
    </div>
    
    <!-- Status Trial -->
    <?php if ($trialInfo['status'] === 'trial'): ?>
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg text-sm font-medium">
      ⏱️ <?= $trialInfo['dias_restantes'] ?> dias de teste restantes
    </div>
    <?php endif; ?>
  </div>

  <?php if ($moduloBloqueado): ?>
  <!-- Módulo Bloqueado -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
    <div class="text-6xl mb-4">🔒</div>
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Módulo Bloqueado</h2>
    <p class="text-gray-600 mb-6">Seu período de teste expirou. Entre em contato para ativar.</p>
    <a href="/area-tecnica" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition">
      ← Voltar para Área Técnica
    </a>
  </div>
  <?php else: ?>

  <!-- Busca -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex flex-col md:flex-row gap-4">
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">🔢 Número de Série do Equipamento</label>
        <input 
          type="text" 
          id="numeroSerieBusca" 
          placeholder="Digite o número de série para buscar..."
          class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 uppercase"
          onkeypress="if(event.key === 'Enter') buscarChecklists()"
        >
      </div>
      <div class="flex items-end gap-2">
        <button onclick="buscarChecklists()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          Buscar
        </button>
        <button onclick="listarTodos()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition">
          Ver Todos
        </button>
      </div>
    </div>
  </div>

  <!-- Resultados -->
  <div id="resultados" class="hidden">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold text-gray-900">
        📋 Resultados 
        <span id="totalResultados" class="text-sm font-normal text-gray-500">(0 registros)</span>
      </h2>
    </div>
    
    <!-- Grid de Cards -->
    <div id="gridChecklists" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- Cards serão inseridos via JS -->
    </div>
    
    <!-- Paginação -->
    <div id="paginacao" class="hidden mt-6 flex justify-center gap-2">
    </div>
  </div>

  <!-- Estado Vazio -->
  <div id="estadoVazio" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
    <div class="text-6xl mb-4">🔎</div>
    <h2 class="text-xl font-semibold text-gray-900 mb-2">Pesquise um Equipamento</h2>
    <p class="text-gray-600">Digite o número de série acima para visualizar o histórico de checklists.</p>
  </div>

  <?php endif; ?>
</section>

<!-- Modal de Detalhes -->
<div id="modalDetalhes" class="fixed inset-0 hidden z-50 overflow-y-auto">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="fixed inset-0 bg-black/50" onclick="fecharModal()"></div>
    <div class="relative bg-white rounded-2xl max-w-2xl w-full shadow-2xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4 rounded-t-2xl flex justify-between items-center">
        <h3 class="text-lg font-semibold">📋 Detalhes do Checklist</h3>
        <button onclick="fecharModal()" class="text-white/80 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <!-- Conteúdo -->
      <div id="modalConteudo" class="p-6">
        <!-- Será preenchido via JS -->
      </div>
    </div>
  </div>
</div>

<script>
let paginaAtual = 1;

// Buscar por número de série
async function buscarChecklists() {
  const numeroSerie = document.getElementById('numeroSerieBusca').value.trim();
  
  if (!numeroSerie) {
    alert('Digite um número de série');
    return;
  }
  
  try {
    const response = await fetch(`/area-tecnica/checklists/buscar?numero_serie=${encodeURIComponent(numeroSerie)}`);
    const result = await response.json();
    
    if (result.success) {
      exibirResultados(result.data, result.total);
    } else {
      alert('❌ ' + result.message);
    }
  } catch (error) {
    console.error('Erro:', error);
    alert('Erro ao buscar');
  }
}

// Listar todos
async function listarTodos(page = 1) {
  paginaAtual = page;
  
  try {
    const response = await fetch(`/area-tecnica/checklists/listar?page=${page}`);
    const result = await response.json();
    
    if (result.success) {
      exibirResultados(result.data, result.total, result.pages);
    }
  } catch (error) {
    console.error('Erro:', error);
  }
}

// Exibir resultados
function exibirResultados(checklists, total, totalPaginas = 1) {
  document.getElementById('estadoVazio').classList.add('hidden');
  document.getElementById('resultados').classList.remove('hidden');
  document.getElementById('totalResultados').textContent = `(${total} registros)`;
  
  const grid = document.getElementById('gridChecklists');
  
  if (checklists.length === 0) {
    grid.innerHTML = `
      <div class="col-span-full text-center py-12 bg-white rounded-xl border border-gray-200">
        <div class="text-4xl mb-2">📭</div>
        <p class="text-gray-600">Nenhum checklist encontrado</p>
      </div>
    `;
    return;
  }
  
  grid.innerHTML = checklists.map(c => {
    const dataHora = new Date(c.data_hora).toLocaleString('pt-BR');
    const temFotoContador = c.foto_contador ? '📸' : '—';
    const temFotoEquip = c.foto_equipamento ? '📸' : '—';
    
    return `
      <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-lg transition cursor-pointer" onclick="verDetalhes(${c.id})">
        <div class="flex justify-between items-start mb-3">
          <div>
            <div class="text-xs text-gray-500 mb-1">Nº Série</div>
            <div class="font-bold text-gray-900 text-lg">${c.numero_serie}</div>
          </div>
          <div class="text-right">
            <div class="text-xs text-gray-500">${dataHora}</div>
          </div>
        </div>
        
        <div class="text-sm text-gray-600 mb-3 line-clamp-2">${c.manutencao_realizada}</div>
        
        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
          <div class="flex items-center gap-2 text-sm text-gray-600">
            <span>👤</span>
            <span>${c.colaborador}</span>
          </div>
          <div class="flex gap-2 text-sm">
            <span title="Foto do Contador">${temFotoContador}</span>
            <span title="Foto do Equipamento">${temFotoEquip}</span>
          </div>
        </div>
      </div>
    `;
  }).join('');
  
  // Paginação
  if (totalPaginas > 1) {
    const pag = document.getElementById('paginacao');
    pag.classList.remove('hidden');
    pag.innerHTML = '';
    
    for (let i = 1; i <= totalPaginas; i++) {
      const btn = document.createElement('button');
      btn.textContent = i;
      btn.className = i === paginaAtual 
        ? 'px-4 py-2 bg-indigo-600 text-white rounded-lg' 
        : 'px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200';
      btn.onclick = () => listarTodos(i);
      pag.appendChild(btn);
    }
  } else {
    document.getElementById('paginacao').classList.add('hidden');
  }
}

// Ver detalhes
async function verDetalhes(id) {
  try {
    const response = await fetch(`/area-tecnica/checklists/${id}`);
    const result = await response.json();
    
    if (result.success) {
      const c = result.data;
      const dataHora = new Date(c.data_hora).toLocaleString('pt-BR');
      
      let fotoContadorHtml = c.foto_contador 
        ? `<img src="/uploads/checklists/${c.foto_contador}" class="w-full rounded-lg shadow" alt="Foto do Contador">`
        : '<div class="bg-gray-100 rounded-lg p-8 text-center text-gray-500">Sem foto</div>';
        
      let fotoEquipHtml = c.foto_equipamento 
        ? `<img src="/uploads/checklists/${c.foto_equipamento}" class="w-full rounded-lg shadow" alt="Foto do Equipamento">`
        : '<div class="bg-gray-100 rounded-lg p-8 text-center text-gray-500">Sem foto</div>';
      
      document.getElementById('modalConteudo').innerHTML = `
        <div class="space-y-6">
          <!-- Info Principal -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="text-xs text-gray-500 uppercase mb-1">Número de Série</div>
              <div class="text-xl font-bold text-gray-900">${c.numero_serie}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="text-xs text-gray-500 uppercase mb-1">Data/Hora</div>
              <div class="text-lg font-medium text-gray-900">${dataHora}</div>
            </div>
          </div>
          
          <!-- Colaborador -->
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-xs text-blue-600 uppercase mb-1">👤 Colaborador</div>
            <div class="text-lg font-medium text-gray-900">${c.colaborador}</div>
          </div>
          
          <!-- Manutenção -->
          <div>
            <div class="text-sm font-semibold text-gray-700 mb-2">🔧 Manutenção Realizada</div>
            <div class="bg-gray-50 p-4 rounded-lg text-gray-700 whitespace-pre-wrap">${c.manutencao_realizada}</div>
          </div>
          
          <!-- Fotos -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <div class="text-sm font-semibold text-gray-700 mb-2">📸 Foto do Contador</div>
              ${fotoContadorHtml}
            </div>
            <div>
              <div class="text-sm font-semibold text-gray-700 mb-2">🖨️ Foto do Equipamento</div>
              ${fotoEquipHtml}
            </div>
          </div>
          
          <!-- IP de Origem -->
          <div class="text-xs text-gray-400 pt-4 border-t">
            Registrado de: ${c.ip_origem || 'N/A'}
          </div>
        </div>
      `;
      
      document.getElementById('modalDetalhes').classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }
  } catch (error) {
    console.error('Erro:', error);
  }
}

function fecharModal() {
  document.getElementById('modalDetalhes').classList.add('hidden');
  document.body.style.overflow = '';
}

// ESC fecha modal
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') fecharModal();
});
</script>

<style>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
