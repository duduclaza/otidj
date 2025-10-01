<?php
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function getMesNome($mes) {
    $meses = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];
    return $meses[$mes] ?? '';
}

$isAdmin = $_SESSION['user_role'] === 'admin';
?>

<section class="p-6">
  <div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
      💰 Financeiro
      <span class="text-sm font-normal text-gray-500">Histórico de Pagamentos</span>
    </h1>
  </div>

  <!-- Alerta de Pagamento Pendente -->
  <?php if ($pagamentoAtual && $pagamentoAtual['status'] !== 'Pago'): ?>
    <?php
    $diasAtraso = (strtotime(date('Y-m-d')) - strtotime($pagamentoAtual['data_vencimento'])) / 86400;
    $corAlerta = $diasAtraso > 5 ? 'red' : ($diasAtraso > 3 ? 'orange' : 'yellow');
    ?>
    <div class="mb-6 bg-<?= $corAlerta ?>-50 border-l-4 border-<?= $corAlerta ?>-500 p-4 rounded">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-<?= $corAlerta ?>-800 font-semibold">
            ⚠️ Pagamento de <?= getMesNome($pagamentoAtual['mes']) ?>/<?= $pagamentoAtual['ano'] ?> - <?= $pagamentoAtual['status'] ?>
          </h3>
          <p class="text-<?= $corAlerta ?>-700 text-sm mt-1">
            Vencimento: <?= date('d/m/Y', strtotime($pagamentoAtual['data_vencimento'])) ?>
            <?php if ($diasAtraso > 0): ?>
              - <strong><?= (int)$diasAtraso ?> dia(s) de atraso</strong>
            <?php endif; ?>
          </p>
        </div>
        <?php if (!$pagamentoAtual['comprovante']): ?>
          <button onclick="abrirModalComprovante(<?= $pagamentoAtual['id'] ?>)" 
                  class="bg-<?= $corAlerta ?>-600 hover:bg-<?= $corAlerta ?>-700 text-white px-4 py-2 rounded-lg">
            📎 Anexar Comprovante
          </button>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Histórico de Pagamentos -->
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b">
      <h2 class="text-lg font-semibold text-gray-800">📋 Histórico de Pagamentos</h2>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mês/Ano</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Pagamento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anexado Por</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprovante</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($pagamentos as $pag): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <?= getMesNome($pag['mes']) ?>/<?= $pag['ano'] ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= date('d/m/Y', strtotime($pag['data_vencimento'])) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 py-1 text-xs font-semibold rounded-full
                <?php
                  switch($pag['status']) {
                    case 'Pago': echo 'bg-green-100 text-green-800'; break;
                    case 'Em Aberto': echo 'bg-yellow-100 text-yellow-800'; break;
                    case 'Atrasado': echo 'bg-red-100 text-red-800'; break;
                  }
                ?>">
                <?= e($pag['status']) ?>
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= $pag['data_pagamento'] ? date('d/m/Y H:i', strtotime($pag['data_pagamento'])) : '-' ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($pag['anexado_por_nome'] ?? '-') ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?php if ($pag['comprovante']): ?>
                <a href="/financeiro/<?= $pag['id'] ?>/download-comprovante" 
                   class="text-blue-600 hover:text-blue-800">
                  📄 Baixar
                </a>
              <?php else: ?>
                <span class="text-gray-400">-</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?php if ($pag['status'] !== 'Pago'): ?>
                <button onclick="abrirModalComprovante(<?= $pag['id'] ?>)" 
                        class="text-blue-600 hover:text-blue-800">
                  📎 Anexar
                </button>
              <?php else: ?>
                <span class="text-green-600">✓ Pago</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal para Anexar Comprovante -->
<div id="comprovanteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">📎 Anexar Comprovante de Pagamento</h3>
      <button onclick="fecharModalComprovante()" class="text-gray-500 hover:text-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="comprovanteForm" enctype="multipart/form-data">
      <input type="hidden" name="pagamento_id" id="pagamentoId">
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Comprovante (PDF, Imagem - Máx 10MB)
        </label>
        <input type="file" 
               name="comprovante" 
               accept=".pdf,.jpg,.jpeg,.png"
               required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
      </div>

      <div class="flex gap-3">
        <button type="submit" 
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
          💾 Salvar
        </button>
        <button type="button" 
                onclick="fecharModalComprovante()"
                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
          Cancelar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirModalComprovante(pagamentoId) {
  document.getElementById('pagamentoId').value = pagamentoId;
  document.getElementById('comprovanteModal').classList.remove('hidden');
}

function fecharModalComprovante() {
  document.getElementById('comprovanteModal').classList.add('hidden');
  document.getElementById('comprovanteForm').reset();
}

document.getElementById('comprovanteForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  try {
    const response = await fetch('/financeiro/anexar-comprovante', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    alert(result.message);
    
    if (result.success) {
      window.location.reload();
    }
  } catch (error) {
    alert('Erro ao anexar comprovante');
  }
});
</script>
