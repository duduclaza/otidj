<?php
$trialInfo = $trialInfo ?? ['ativo' => false, 'dias_restantes' => 0, 'status' => 'nao_iniciado'];
$moduloBloqueado = !$trialInfo['ativo'] && $trialInfo['status'] !== 'nao_iniciado';
?>

<section class="space-y-6">
  <!-- Header -->
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">🔧 Área Técnica</h1>
      <p class="text-sm text-gray-600 mt-1">Ferramentas para a equipe técnica de campo</p>
    </div>
    
    <!-- Status do Trial -->
    <div id="trialStatus">
      <?php if ($trialInfo['status'] === 'nao_iniciado'): ?>
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg">
          <div class="text-sm font-medium">🎁 Teste Grátis por 7 Dias</div>
          <div class="text-xs opacity-90">Depois apenas R$ 200,00/mês</div>
          <button onclick="ativarTrial()" class="mt-2 w-full bg-white text-indigo-600 px-3 py-1 rounded text-sm font-semibold hover:bg-gray-100 transition">
            ✅ Ativar Agora
          </button>
        </div>
      <?php elseif ($trialInfo['status'] === 'trial'): ?>
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-lg shadow-lg">
          <div class="text-sm font-medium">⏱️ Período de Teste</div>
          <div class="text-2xl font-bold"><?= $trialInfo['dias_restantes'] ?> dias restantes</div>
          <div class="text-xs opacity-90">Depois R$ 200,00/mês</div>
        </div>
      <?php elseif ($trialInfo['status'] === 'pago'): ?>
        <div class="bg-gradient-to-r from-emerald-500 to-green-600 text-white px-4 py-2 rounded-lg shadow-lg">
          <div class="text-sm font-medium">✅ Módulo Ativo</div>
          <div class="text-xs opacity-90">Acesso liberado</div>
        </div>
      <?php elseif ($trialInfo['status'] === 'expirado'): ?>
        <div class="bg-gradient-to-r from-red-500 to-rose-600 text-white px-4 py-2 rounded-lg shadow-lg">
          <div class="text-sm font-medium">🔒 Trial Expirado</div>
          <div class="text-xs opacity-90">R$ 200,00/mês para ativar</div>
          <button onclick="contatoVendas()" class="mt-2 w-full bg-white text-red-600 px-3 py-1 rounded text-sm font-semibold hover:bg-gray-100 transition">
            📞 Falar com Vendas
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Grid de Módulos -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <!-- Card: Checklist Virtual -->
    <div class="relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow <?= $moduloBloqueado ? 'opacity-60' : '' ?>">
      <?php if ($moduloBloqueado): ?>
        <div class="absolute inset-0 bg-gray-900/50 flex items-center justify-center z-10 cursor-pointer" onclick="contatoVendas()">
          <div class="text-center text-white">
            <div class="text-5xl mb-2">🔒</div>
            <div class="font-semibold">Módulo Bloqueado</div>
            <div class="text-sm opacity-90">Clique para ativar</div>
          </div>
        </div>
      <?php endif; ?>
      
      <div class="p-6">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
            <span class="text-2xl">📋</span>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Checklist Virtual</h3>
            <p class="text-sm text-gray-500">Formulário para técnicos</p>
          </div>
        </div>
        
        <p class="text-gray-600 text-sm mb-4">
          Formulário público acessível por smartphone ou computador. Registre manutenções com fotos do contador e equipamento.
        </p>
        
        <div class="space-y-2">
          <a href="/area-tecnica/checklist" target="_blank" class="flex items-center justify-between w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition <?= $moduloBloqueado ? 'pointer-events-none' : '' ?>">
            <span>📱 Abrir Formulário</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
          </a>
          
          <button onclick="copiarLink()" class="flex items-center justify-between w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition <?= $moduloBloqueado ? 'pointer-events-none' : '' ?>">
            <span>🔗 Copiar Link Público</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Card: Consulta de Checklists -->
    <div class="relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow <?= $moduloBloqueado ? 'opacity-60' : '' ?>">
      <?php if ($moduloBloqueado): ?>
        <div class="absolute inset-0 bg-gray-900/50 flex items-center justify-center z-10 cursor-pointer" onclick="contatoVendas()">
          <div class="text-center text-white">
            <div class="text-5xl mb-2">🔒</div>
            <div class="font-semibold">Módulo Bloqueado</div>
            <div class="text-sm opacity-90">Clique para ativar</div>
          </div>
        </div>
      <?php endif; ?>
      
      <div class="p-6">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
            <span class="text-2xl">🔍</span>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Consulta de Checklists</h3>
            <p class="text-sm text-gray-500">Buscar por nº de série</p>
          </div>
        </div>
        
        <p class="text-gray-600 text-sm mb-4">
          Pesquise todos os checklists registrados para um equipamento específico. Visualize histórico completo de manutenções.
        </p>
        
        <a href="/area-tecnica/consulta" class="flex items-center justify-center gap-2 w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition <?= $moduloBloqueado ? 'pointer-events-none' : '' ?>">
          <span>🔎 Consultar Checklists</span>
        </a>
      </div>
    </div>
    
    <!-- Card: Em Breve -->
    <div class="bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 overflow-hidden">
      <div class="p-6 text-center">
        <div class="w-14 h-14 bg-gray-200 rounded-xl flex items-center justify-center mx-auto mb-4">
          <span class="text-2xl">🚧</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-500">Em Breve</h3>
        <p class="text-sm text-gray-400 mt-2">
          Novos recursos serão adicionados em breve: Ordem de Serviço, Relatórios Técnicos, etc.
        </p>
      </div>
    </div>
    
  </div>

  <!-- Estatísticas Rápidas -->
  <?php if ($trialInfo['ativo']): ?>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Estatísticas</h3>
    <div id="estatisticas" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="text-center p-4 bg-blue-50 rounded-lg">
        <div class="text-3xl font-bold text-blue-600" id="totalChecklists">-</div>
        <div class="text-sm text-gray-600">Total de Checklists</div>
      </div>
      <div class="text-center p-4 bg-green-50 rounded-lg">
        <div class="text-3xl font-bold text-green-600" id="checklistsHoje">-</div>
        <div class="text-sm text-gray-600">Hoje</div>
      </div>
      <div class="text-center p-4 bg-purple-50 rounded-lg">
        <div class="text-3xl font-bold text-purple-600" id="checklistsSemana">-</div>
        <div class="text-sm text-gray-600">Esta Semana</div>
      </div>
      <div class="text-center p-4 bg-amber-50 rounded-lg">
        <div class="text-3xl font-bold text-amber-600" id="equipamentosUnicos">-</div>
        <div class="text-sm text-gray-600">Equipamentos</div>
      </div>
    </div>
  </div>
  <?php endif; ?>

</section>

<!-- Modal de Contato com Vendas -->
<div id="modalVendas" class="fixed inset-0 hidden z-50 overflow-y-auto">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="fixed inset-0 bg-black/50" onclick="fecharModalVendas()"></div>
    <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
      <button onclick="fecharModalVendas()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
      
      <div class="text-center">
        <div class="text-5xl mb-4">📞</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Ative o Módulo Área Técnica</h3>
        <p class="text-gray-600 mb-6">
          Seu período de teste expirou. Para continuar usando o módulo Área Técnica, entre em contato com nossa equipe de vendas.
        </p>
        
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 mb-6">
          <div class="text-3xl font-bold text-indigo-600">R$ 200,00</div>
          <div class="text-sm text-gray-600">por mês</div>
        </div>
        
        <div class="space-y-3">
          <a href="https://wa.me/5500000000000?text=Olá! Gostaria de ativar o módulo Área Técnica do SGQ-OTI" target="_blank" class="flex items-center justify-center gap-2 w-full bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg transition font-medium">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            WhatsApp
          </a>
          <a href="mailto:vendas@sgqoti.com.br?subject=Ativação do Módulo Área Técnica" class="flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg transition font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            E-mail
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Ativar trial
async function ativarTrial() {
  if (!confirm('Deseja ativar o período de teste de 7 dias grátis?')) return;
  
  try {
    const response = await fetch('/area-tecnica/ativar-trial', { method: 'POST' });
    const result = await response.json();
    
    if (result.success) {
      alert('✅ ' + result.message);
      location.reload();
    } else {
      alert('❌ ' + result.message);
    }
  } catch (error) {
    alert('Erro ao ativar trial');
  }
}

// Copiar link público
function copiarLink() {
  const link = window.location.origin + '/area-tecnica/checklist';
  navigator.clipboard.writeText(link).then(() => {
    alert('✅ Link copiado!\n\n' + link);
  });
}

// Modal de vendas
function contatoVendas() {
  document.getElementById('modalVendas').classList.remove('hidden');
}

function fecharModalVendas() {
  document.getElementById('modalVendas').classList.add('hidden');
}

// Carregar estatísticas
document.addEventListener('DOMContentLoaded', function() {
  <?php if ($trialInfo['ativo']): ?>
  carregarEstatisticas();
  <?php endif; ?>
});

async function carregarEstatisticas() {
  try {
    const response = await fetch('/area-tecnica/checklists/listar');
    const result = await response.json();
    
    if (result.success) {
      document.getElementById('totalChecklists').textContent = result.total || 0;
      
      // Calcular estatísticas
      const hoje = new Date().toISOString().split('T')[0];
      const inicioSemana = new Date();
      inicioSemana.setDate(inicioSemana.getDate() - inicioSemana.getDay());
      
      let checklistsHoje = 0;
      let checklistsSemana = 0;
      let equipamentos = new Set();
      
      result.data.forEach(c => {
        const dataChecklist = c.data_hora.split(' ')[0];
        if (dataChecklist === hoje) checklistsHoje++;
        if (new Date(dataChecklist) >= inicioSemana) checklistsSemana++;
        equipamentos.add(c.numero_serie);
      });
      
      document.getElementById('checklistsHoje').textContent = checklistsHoje;
      document.getElementById('checklistsSemana').textContent = checklistsSemana;
      document.getElementById('equipamentosUnicos').textContent = equipamentos.size;
    }
  } catch (error) {
    console.error('Erro:', error);
  }
}

// ESC fecha modal
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') fecharModalVendas();
});
</script>
