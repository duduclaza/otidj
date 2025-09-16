<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Registro de Retornados</h1>
    <div class="flex space-x-3">
      <button onclick="downloadActivityLog()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <span>Download Log</span>
      </button>
      <button id="openRetornadoBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Registrar Novo Retornado</span>
      </button>
    </div>
    <script>
      // Immediate binding near the button as a fallback
      (function(){
        var btn = document.getElementById('openRetornadoBtn');
        if (btn && !btn.__retornadoBound) {
          btn.__retornadoBound = true;
          btn.addEventListener('click', function(e){
            e.preventDefault();
            if (typeof window.openRetornadoModal === 'function') {
              window.openRetornadoModal();
              return;
            }
            if (typeof openRetornadoModal === 'function') {
              openRetornadoModal();
              return;
            }
            // Fallback: abrir o modal diretamente sem depender da função
            try {
              var modal = document.getElementById('retornadoModal');
              if (modal) {
                modal.classList.remove('hidden');
              }
              var form = document.getElementById('retornadoForm');
              if (form) { form.reset(); }
              // Variáveis e funções opcionais
              try { window.selectedDestino = ''; } catch(_){}
              if (typeof window.updateDestinoButtons === 'function') { window.updateDestinoButtons(); }
              var obs = document.getElementById('observacao-container');
              if (obs) { obs.classList.add('hidden'); }
              if (typeof window.loadParameters === 'function') {
                Promise.resolve(window.loadParameters()).then(function(){
                  if (typeof window.toggleMode === 'function') { try { window.toggleMode(); } catch(_){} }
                  if (typeof window.showGuidance === 'function') { try { window.showGuidance(window.selectedDestino || ''); } catch(_){} }
                  if (typeof window.calculateValue === 'function') { try { window.calculateValue(); } catch(_){} }
                  if (typeof window.checkAutoDiscard === 'function') { try { window.checkAutoDiscard(); } catch(_){} }
                });
              }
            } catch (err) {
              console.error('Falha ao abrir o modal via fallback:', err);
              alert('Erro ao abrir o formulário. Recarregue a página.');
            }
          });
        }
      })();
    </script>
  </div>

  <!-- Filters and Search -->
  <div class="bg-white border rounded-lg p-4">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
        <input type="text" id="searchInput" placeholder="Modelo, código, usuário..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
        <input type="date" id="dateFrom" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
        <input type="date" id="dateTo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div class="flex items-end justify-end space-x-2">
        <button onclick="filterData()" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
          Filtrar
        </button>
        <button onclick="exportData()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
          Exportar
        </button>
        <button onclick="openImportModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
          Importar
        </button>
      </div>
    </div>
  </div>

  <!-- Data Grid -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código Cliente</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filial</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destino</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observação</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody id="retornadosTable" class="bg-white divide-y divide-gray-200">
          <?php if (!empty($retornados)): ?>
            <?php foreach ($retornados as $retornado): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <?= e($retornado['modelo']) ?>
                  <?php if (!$retornado['modelo_cadastrado']): ?>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                      Modelo não cadastrado
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($retornado['codigo_cliente']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($retornado['usuario']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($retornado['filial']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php 
                    $colors = [
                      'descarte' => 'bg-red-100 text-red-800',
                      'estoque' => 'bg-green-100 text-green-800', 
                      'uso_interno' => 'bg-blue-100 text-blue-800',
                      'garantia' => 'bg-purple-100 text-purple-800'
                    ];
                    $color = $colors[$retornado['destino']] ?? 'bg-gray-100 text-gray-800';
                  ?>
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= $color ?>">
                    <?= ucfirst(str_replace('_', ' ', $retornado['destino'])) ?>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <?php if (isset($retornado['valor_calculado']) && $retornado['valor_calculado'] > 0): ?>
                    <span class="font-semibold text-green-600">R$ <?= number_format($retornado['valor_calculado'], 2, ',', '.') ?></span>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2 text-sm text-gray-900 max-w-xs">
                  <?php if (!empty($retornado['observacao'])): ?>
                    <span class="truncate block" title="<?= htmlspecialchars($retornado['observacao']) ?>">
                      <?= htmlspecialchars(substr($retornado['observacao'], 0, 50)) ?><?= strlen($retornado['observacao']) > 50 ? '...' : '' ?>
                    </span>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('d/m/Y', strtotime($retornado['data_registro'])) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <button onclick="confirmDelete(<?= $retornado['id'] ?>, '<?= e($retornado['modelo']) ?>')" 
                          class="text-red-600 hover:text-red-900 transition-colors" 
                          title="Excluir registro">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">Nenhum registro encontrado</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
      <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <div class="flex items-center text-sm text-gray-700">
          <span>Mostrando <?= ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 ?> a <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']) ?> de <?= $pagination['total_records'] ?> registros</span>
        </div>
        <div class="flex items-center space-x-2">
          <!-- Previous Button -->
          <?php if ($pagination['has_prev']): ?>
            <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors">
              Anterior
            </a>
          <?php else: ?>
            <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
              Anterior
            </span>
          <?php endif; ?>
          
          <!-- Page Numbers -->
          <?php
          $start = max(1, $pagination['current_page'] - 2);
          $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
          ?>
          
          <?php if ($start > 1): ?>
            <a href="?page=1" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors">1</a>
            <?php if ($start > 2): ?>
              <span class="px-3 py-2 text-sm font-medium text-gray-400">...</span>
            <?php endif; ?>
          <?php endif; ?>
          
          <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $pagination['current_page']): ?>
              <span class="px-2 py-1 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded">
                <?= $i ?>
              </span>
            <?php else: ?>
              <a href="?page=<?= $i ?>" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors">
                <?= $i ?>
              </a>
            <?php endif; ?>
          <?php endfor; ?>
          
          <?php if ($end < $pagination['total_pages']): ?>
            <?php if ($end < $pagination['total_pages'] - 1): ?>
              <span class="px-3 py-2 text-sm font-medium text-gray-400">...</span>
            <?php endif; ?>
            <a href="?page=<?= $pagination['total_pages'] ?>" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors"><?= $pagination['total_pages'] ?></a>
          <?php endif; ?>
          
          <!-- Next Button -->
          <?php if ($pagination['has_next']): ?>
            <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="px-2 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors">
              Próximo
            </a>
          <?php else: ?>
            <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
              Próximo
            </span>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

</div>
</main>
</div>
</div>

<!-- Retornado Modal -->
<div id="retornadoModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] overflow-y-auto flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-white rounded-t-xl sticky top-0 z-10 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">Registrar Novo Retornado</h3>
      <button onclick="window.closeRetornadoModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <!-- Content -->
    <form id="retornadoForm" class="px-6 py-6 space-y-6">
      <!-- Mode Selection -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <label class="block text-sm font-medium text-gray-700 mb-3">Modo de Registro</label>
        <div class="flex space-x-4">
          <label class="flex items-center">
            <input type="radio" name="modo" value="peso" class="mr-2" checked onchange="window.toggleMode()">
            <span class="text-sm font-medium">Modo Peso</span>
          </label>
          <label class="flex items-center">
            <input type="radio" name="modo" value="chip" class="mr-2" onchange="window.toggleMode()">
            <span class="text-sm font-medium">Modo % Chip</span>
          </label>
        </div>
      </div>

      <!-- Basic Fields -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
          <select name="modelo" onchange="window.updateTonerData()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Selecione o modelo</option>
          <?php foreach ($toners as $toner): ?>
            <option value="<?= e($toner) ?>"><?= e($toner) ?></option>
          <?php endforeach; ?>
        </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Usuário *</label>
          <input type="text" name="usuario" value="Sistema" required readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-700 focus:ring-0 focus:border-gray-300">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filial *</label>
          <select name="filial" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Selecione a filial</option>
            <?php foreach ($filiais as $filial): ?>
              <option value="<?= e($filial) ?>"><?= e($filial) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Código do Cliente *</label>
          <input type="text" name="codigo_cliente" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <!-- Mode-specific Fields -->
      <div id="pesoFields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Peso Retornado (g)</label>
          <input type="number" name="peso_retornado" step="0.01" min="0" oninput="window.calculatePercentage(); window.calculateValue(); window.showGuidance(); window.checkAutoDiscard();" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Percentual Restante</label>
          <input type="text" id="percentualCalculado" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50">
        </div>
      </div>

      <div id="chipFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Percentual do Chip (%)</label>
          <input type="number" name="percentual_chip" step="0.01" min="0" max="100" oninput="window.calculatePercentage(); window.calculateValue(); window.showGuidance(); window.checkAutoDiscard();" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <!-- Destination Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Destino Final *</label>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <button type="button" onclick="window.selectDestino('descarte')" class="destino-btn p-3 border-2 border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors text-center" data-destino="descarte">
            <div class="text-sm font-bold">DESCARTE</div>
          </button>
          <button type="button" onclick="window.selectDestino('estoque')" class="destino-btn p-3 border-2 border-green-300 text-green-700 rounded-lg hover:bg-green-50 transition-colors text-center" data-destino="estoque">
            <div class="text-sm font-bold">ESTOQUE</div>
          </button>
          <button type="button" onclick="window.selectDestino('uso_interno')" class="destino-btn p-3 border-2 border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors text-center" data-destino="uso_interno">
            <div class="text-sm font-bold">USO INTERNO</div>
          </button>
          <button type="button" onclick="window.selectDestino('garantia')" class="destino-btn p-3 border-2 border-purple-300 text-purple-700 rounded-lg hover:bg-purple-50 transition-colors text-center" data-destino="garantia">
            <div class="text-sm font-bold">GARANTIA</div>
          </button>
        </div>
        <input type="hidden" name="destino" id="destinoSelected">
      </div>

      <!-- Value Calculation Display -->
      <div id="valorCalculado" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="text-sm font-medium text-green-800 mb-1">Valor Calculado para Estoque:</div>
        <div class="text-lg font-bold text-green-900" id="valorDisplay">R$ 0,00</div>
      </div>

      <!-- Guidance Display -->
      <div id="guidanceDisplay" class="hidden rounded-lg p-4">
        <div class="text-sm font-medium mb-2" id="guidanceTitle">Orientação:</div>
        <div class="text-sm" id="guidanceText"></div>
      </div>

      <!-- Campo de Observação (aparece apenas quando destino é descarte) -->
      <div id="observacao-container" class="hidden">
        <label for="retornado-observacao" class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
        <textarea id="retornado-observacao" name="observacao" rows="3" placeholder="Digite uma observação sobre o descarte..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
      </div>
    </form>

    <!-- Footer -->
    <div class="px-6 py-6 bg-gray-50 border-t border-gray-200 rounded-b-xl sticky bottom-0 z-10">
      <div class="flex justify-end space-x-4">
        <button onclick="window.closeRetornadoModal()" class="px-6 py-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button onclick="window.submitRetornado()" class="px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Registrar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">Importar Retornados</h3>
    </div>
    
    <!-- Content -->
    <div class="px-6 py-4">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo Excel (.xlsx)</label>
        <input type="file" id="importFileInput" accept=".xlsx,.xls,.csv" class="w-full border border-gray-300 rounded-md px-3 py-2">
      </div>
      
      <div id="importProgress" class="mb-4" style="display: none;">
        <div class="bg-gray-200 rounded-full h-2 mb-2">
          <div id="importProgressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <p id="importStatus" class="text-sm text-gray-600">Preparando importação...</p>
      </div>
      
      <!-- Debug Console -->
      <div id="debugConsole" class="mb-4" style="display: none;">
        <h4 class="text-sm font-semibold text-gray-700 mb-2">Debug Console:</h4>
        <div id="debugOutput" class="bg-gray-900 text-green-400 p-3 rounded-md h-64 overflow-y-auto text-xs font-mono"></div>
        <button onclick="downloadDebugReport()" id="downloadDebugBtn" class="mt-2 px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">Baixar Relatório Debug</button>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
      <!-- Template Download -->
      <div class="mb-3">
        <button onclick="downloadRetornadosTemplate()" class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-orange-700 bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100 transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Baixar Template Excel
        </button>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex space-x-3">
        <button id="importCancelBtn" onclick="closeImportModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button onclick="toggleDebug()" id="debugToggleBtn" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700">Mostrar Debug</button>
        <button id="importSubmitBtn" onclick="importRetornados()" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-lg hover:bg-orange-700 transition-colors">
          Importar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">Confirmar Exclusão</h3>
    </div>
    
    <!-- Content -->
    <div class="px-6 py-4">
      <div class="flex items-center mb-4">
        <svg class="w-12 h-12 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.876c1.17 0 2.25-.16 2.25-1.729 0-.329-.314-.729-.314-1.271L18 7.5c0-.621-.504-1.125-1.125-1.125H7.125C6.504 6.375 6 6.879 6 7.5l-.686 8.5c0 .542-.314.942-.314 1.271 0 1.569 1.08 1.729 2.25 1.729z"></path>
        </svg>
        <div>
          <p class="text-gray-900 font-medium">Tem certeza que deseja excluir este registro?</p>
          <p class="text-gray-600 text-sm mt-1">Modelo: <span id="deleteModeloName" class="font-semibold"></span></p>
          <p class="text-red-600 text-sm mt-2">Esta ação não pode ser desfeita.</p>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
      <div class="flex space-x-3 justify-end">
        <button onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
          Cancelar
        </button>
        <button onclick="deleteRetornado()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 transition-colors">
          Excluir
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let tonerData = {};
let selectedDestino = '';

// Debug: Check if modal exists when page loads
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('retornadoModal');
  console.log('Modal found:', modal ? 'Yes' : 'No');
  if (!modal) {
    console.error('retornadoModal element not found in DOM');
  }
  // Ensure the open button triggers the modal even if inline handler is blocked
  const openBtn = document.getElementById('openRetornadoBtn');
  if (openBtn) {
    openBtn.addEventListener('click', function(e) {
      e.preventDefault();
      if (typeof window.openRetornadoModal === 'function') {
        window.openRetornadoModal();
      } else if (typeof openRetornadoModal === 'function') {
        openRetornadoModal();
      } else {
        console.error('openRetornadoModal is not defined');
        alert('Erro ao abrir o formulário. Recarregue a página.');
      }
    });
  }
});

// Global variables for debug and activity logging
let debugMode = false;
let importResults = [];
let activityLog = [];

// Activity logging system
function logActivity(type, action, details = {}) {
  const timestamp = new Date().toISOString();
  const logEntry = {
    timestamp,
    type, // 'user_action', 'calculation', 'error', 'validation', 'modal', 'form'
    action,
    details,
    url: window.location.href,
    userAgent: navigator.userAgent
  };
  
  activityLog.push(logEntry);
  console.log(`[${type.toUpperCase()}] ${action}:`, details);
  
  // Keep only last 1000 entries to prevent memory issues
  if (activityLog.length > 1000) {
    activityLog = activityLog.slice(-1000);
  }
}

// Enhanced error handling
window.addEventListener('error', function(e) {
  logActivity('error', 'JavaScript Error', {
    message: e.message,
    filename: e.filename,
    lineno: e.lineno,
    colno: e.colno,
    stack: e.error ? e.error.stack : null
  });
});

window.addEventListener('unhandledrejection', function(e) {
  logActivity('error', 'Unhandled Promise Rejection', {
    reason: e.reason,
    stack: e.reason && e.reason.stack ? e.reason.stack : null
  });
});

function toggleDebug() {
  debugMode = !debugMode;
  const console = document.getElementById('debugConsole');
  const btn = document.getElementById('debugToggleBtn');
  if (debugMode) {
    console.style.display = 'block';
    btn.textContent = 'Ocultar Debug';
    logActivity('user_action', 'Debug Console Opened');
  } else {
    console.style.display = 'none';
    btn.textContent = 'Mostrar Debug';
    logActivity('user_action', 'Debug Console Closed');
  }
}

function addDebugLog(message) {
  const timestamp = new Date().toLocaleTimeString();
  const logEntry = `[${timestamp}] ${message}`;
  debugLogs.push(logEntry);
  
  if (debugMode) {
    const output = document.getElementById('debugOutput');
    output.innerHTML += logEntry + '\n';
    output.scrollTop = output.scrollHeight;
  }
}

function downloadDebugReport() {
  const report = {
    timestamp: new Date().toISOString(),
    logs: debugLogs,
    results: importResults,
    summary: {
      totalRows: importResults.length,
      successful: importResults.filter(r => r.success).length,
      failed: importResults.filter(r => !r.success).length
    }
  };
  
  const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `debug-report-${new Date().toISOString().slice(0,19)}.json`;
  a.click();
  URL.revokeObjectURL(url);
}

function downloadImportReport() {
  const timestamp = new Date();
  const dateStr = timestamp.toLocaleDateString('pt-BR');
  const timeStr = timestamp.toLocaleTimeString('pt-BR');
  
  // Create detailed report
  const report = {
    titulo: 'Relatório de Importação - Retornados',
    data_importacao: `${dateStr} às ${timeStr}`,
    resumo: {
      total_linhas_processadas: importResults.length,
      sucessos: importResults.filter(r => r.success).length,
      erros: importResults.filter(r => !r.success).length,
      taxa_sucesso: `${((importResults.filter(r => r.success).length / importResults.length) * 100).toFixed(1)}%`
    },
    detalhes_por_linha: importResults.map(result => ({
      linha: result.row,
      status: result.success ? 'SUCESSO' : 'ERRO',
      dados_enviados: result.data,
      resposta_servidor: result.response,
      observacoes: result.success ? 'Importado com sucesso' : result.response.message
    })),
    logs_debug: debugLogs,
    estatisticas: {
      modelos_importados: [...new Set(importResults.filter(r => r.success).map(r => r.data.modelo))],
      filiais_envolvidas: [...new Set(importResults.filter(r => r.success).map(r => r.data.filial))],
      destinos_utilizados: [...new Set(importResults.filter(r => r.success).map(r => r.data.destino))]
    }
  };
  
  // Generate filename with timestamp
  const filename = `relatorio-importacao-${timestamp.getFullYear()}-${String(timestamp.getMonth()+1).padStart(2,'0')}-${String(timestamp.getDate()).padStart(2,'0')}_${String(timestamp.getHours()).padStart(2,'0')}-${String(timestamp.getMinutes()).padStart(2,'0')}.json`;
  
  // Download the report
  const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
  
  // Also create a simplified CSV version
  downloadImportCSV(report);
}

function downloadImportCSV(report) {
  const csvData = [
    ['Linha', 'Status', 'Modelo', 'Código Cliente', 'Usuário', 'Filial', 'Destino', 'Valor', 'Data', 'Observações'],
    ...report.detalhes_por_linha.map(linha => [
      linha.linha,
      linha.status,
      linha.dados_enviados.modelo || '',
      linha.dados_enviados.codigo_cliente || '',
      linha.dados_enviados.usuario || '',
      linha.dados_enviados.filial || '',
      linha.dados_enviados.destino || '',
      linha.dados_enviados.valor_calculado || 0,
      linha.dados_enviados.data_registro || '',
      linha.observacoes || ''
    ])
  ];
  
  const csvContent = csvData.map(row => 
    row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(',')
  ).join('\n');
  
  const timestamp = new Date();
  const filename = `relatorio-importacao-${timestamp.getFullYear()}-${String(timestamp.getMonth()+1).padStart(2,'0')}-${String(timestamp.getDate()).padStart(2,'0')}_${String(timestamp.getHours()).padStart(2,'0')}-${String(timestamp.getMinutes()).padStart(2,'0')}.csv`;
  
  const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

// Modal functions
function openRetornadoModal() {
  logActivity('modal', 'Open Retornado Modal', { modalId: 'retornadoModal' });
  console.log('openRetornadoModal called');
  const modal = document.getElementById('retornadoModal');
  console.log('Modal element:', modal);
  
  if (modal) {
    console.log('Removing hidden class from modal');
    modal.classList.remove('hidden');
    
    // Load parameters when modal opens to ensure fresh data
    if (typeof loadParameters === 'function') {
      Promise.resolve(loadParameters()).then(() => {
        if (typeof showGuidance === 'function') {
          try { showGuidance(window.selectedDestino || ''); } catch(_) {}
        }
        if (typeof calculateValue === 'function') {
          try { calculateValue(); } catch(_) {}
        }
        if (typeof checkAutoDiscard === 'function') {
          try { checkAutoDiscard(); } catch(_) {}
        }
      });
    }
    
    // Reset form
    const form = document.getElementById('retornadoForm');
    if (form) {
      form.reset();
    }
    
    selectedDestino = '';
    
    // Try to update destino buttons if function exists
    if (typeof updateDestinoButtons === 'function') {
      updateDestinoButtons();
    }
    
    // Hide observacao container initially
    const observacaoContainer = document.getElementById('observacao-container');
    if (observacaoContainer) {
      observacaoContainer.classList.add('hidden');
    }
    
    console.log('Modal should be visible now');
  } else {
    console.error('Modal retornadoModal não encontrado no DOM');
    alert('Erro: Modal não encontrado. Recarregue a página.');
  }
}

function closeRetornadoModal() {
  document.getElementById('retornadoModal').classList.add('hidden');
  document.getElementById('retornadoForm').reset();
  selectedDestino = '';
  updateDestinoButtons();
}

function openImportModal() {
  console.log('Opening import modal...');
  const modal = document.getElementById('importModal');
  if (modal) {
    modal.classList.remove('hidden');
    console.log('Modal opened successfully');
  } else {
    console.error('Import modal not found!');
  }
}

function closeImportModal() {
  document.getElementById('importModal').classList.add('hidden');
  const progressDiv = document.getElementById('importProgress');
  if (progressDiv) {
    progressDiv.style.display = 'none';
  }
  const debugConsole = document.getElementById('debugConsole');
  if (debugConsole) {
    debugConsole.style.display = 'none';
  }
  debugMode = false;
  const debugBtn = document.getElementById('debugToggleBtn');
  if (debugBtn) {
    debugBtn.textContent = 'Mostrar Debug';
  }
}

// Mode toggle
function toggleMode() {
  const modo = document.querySelector('input[name="modo"]:checked').value;
  logActivity('user_action', 'Mode Changed', { mode: modo });
  
  const pesoFields = document.getElementById('pesoFields');
  const chipFields = document.getElementById('chipFields');
  
  if (modo === 'peso') {
    pesoFields.classList.remove('hidden');
    chipFields.classList.add('hidden');
  } else {
    pesoFields.classList.add('hidden');
    chipFields.classList.remove('hidden');
  }
  
  calculatePercentage();
}

// Toner data update
function updateTonerData() {
  const modelo = document.querySelector('select[name="modelo"]').value;
  if (!modelo) return;
  
  // Fetch toner data from server
  fetch(`/api/toner?modelo=${encodeURIComponent(modelo)}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.tonerData = data.toner;
        calculatePercentage();
      }
    })
    .catch(() => {
      // Fallback - use default values
      window.tonerData = { gramatura: 10, peso_vazio: 0, preco: 150 };
      calculatePercentage();
    });
}

// Calculations
function calculatePercentage() {
  const modo = document.querySelector('input[name="modo"]:checked').value;
  const modelo = document.querySelector('select[name="modelo"]').value;
  
  if (!modelo) return;
  
  if (modo === 'peso') {
    const pesoRetornado = parseFloat(document.querySelector('input[name="peso_retornado"]').value) || 0;
    const pesoVazio = window.tonerData ? window.tonerData.peso_vazio : 0;
    const gramatura = window.tonerData ? window.tonerData.gramatura : 10;
    
    // Calculate gramatura existente: peso_retornado - peso_vazio
    const gramaturaExistente = Math.max(0, pesoRetornado - pesoVazio);
    
    // Calculate percentage: (gramatura_existente / gramatura_total) * 100
    const percentual = Math.min(100, Math.max(0, (gramaturaExistente / gramatura) * 100));
    document.getElementById('percentualCalculado').value = percentual.toFixed(2) + '%';
    
    // Store calculated percentage for value calculation and guidance
    window.calculatedPercentage = percentual;
    window.gramaturaExistente = gramaturaExistente;
  } else if (modo === 'chip') {
    const percentualChip = parseFloat(document.querySelector('input[name="percentual_chip"]').value) || 0;
    window.calculatedPercentage = percentualChip;
  }
  
  calculateValue();
  showGuidance();
  checkAutoDiscard();
}

// Guidance system based on parameters
function showGuidance() {
  const percentual = window.calculatedPercentage || 0;
  const guidanceDiv = document.getElementById('guidanceDisplay');
  const guidanceTitle = document.getElementById('guidanceTitle');
  const guidanceText = document.getElementById('guidanceText');
  
  if (!window.parameters) {
    // Load parameters from database
    loadParameters().then(() => showGuidance());
    return;
  }
  
  // Find matching parameter
  const matchingParam = window.parameters.find(param => 
    percentual >= param.percentual_min && (param.percentual_max === null || percentual <= param.percentual_max)
  );
  
  if (matchingParam && percentual > 0) {
    guidanceTitle.textContent = `Orientação (${percentual.toFixed(2)}%):`;
    guidanceText.textContent = matchingParam.orientacao;
    
    // Set color based on parameter name
    guidanceDiv.className = 'rounded-lg p-4 ';
    const paramName = matchingParam.nome.toLowerCase();
    if (paramName.includes('descarte')) {
      guidanceDiv.className += 'bg-red-50 border border-red-200 text-red-800';
    } else if (paramName.includes('semi') || paramName.includes('amarelo')) {
      guidanceDiv.className += 'bg-yellow-50 border border-yellow-200 text-yellow-800';
    } else {
      guidanceDiv.className += 'bg-green-50 border border-green-200 text-green-800';
    }
    
    guidanceDiv.classList.remove('hidden');
  } else {
    guidanceDiv.classList.add('hidden');
  }
}

// Load parameters from database
function loadParameters() {
  return fetch('/api/parameters')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.parameters = data.parameters;
      }
    })
    .catch(() => {
      // Fallback parameters
      window.parameters = [
        { nome: 'Descarte', percentual_min: 0, percentual_max: 39, orientacao: 'Se a % for <= 39%: Descarte o toner, pois não tem mais utilidade.' },
        { nome: 'Estoque Semi Novo', percentual_min: 40, percentual_max: 89, orientacao: 'Se a % for >= 40% e <= 89%: Teste o Toner; se a qualidade estiver boa, envie para o estoque como seminovo e marque a % na caixa; se estiver ruim, solicite garantia.' },
        { nome: 'Estoque Novo', percentual_min: 90, percentual_max: 100, orientacao: 'Se a % for >= 90%: Teste o Toner; se a qualidade estiver boa, envie para o estoque como novo e marque na caixa; se estiver ruim, solicite garantia.' }
      ];
    });
}

// Auto discard detection
function checkAutoDiscard() {
  const modo = document.querySelector('input[name="modo"]:checked').value;
  const percentual = window.calculatedPercentage || 0;
  const pesoRetornado = parseFloat(document.querySelector('input[name="peso_retornado"]').value) || 0;
  const pesoVazio = window.tonerData ? window.tonerData.peso_vazio : 0;
  
  let autoDiscardReason = '';
  
  // Check for weight-based discard (peso igual ao peso vazio)
  if (modo === 'peso' && pesoRetornado > 0 && pesoVazio > 0 && pesoRetornado <= pesoVazio) {
    autoDiscardReason = 'Peso igual ao peso vazio - Toner sem tinta restante';
  }
  // Check for chip percentage discard (0%)
  else if (modo === 'chip' && percentual === 0) {
    autoDiscardReason = 'Percentual do chip é 0% - Toner vazio';
  }
  // Check for calculated percentage discard (0% remaining)
  else if (percentual === 0) {
    autoDiscardReason = 'Percentual restante é 0% - Toner vazio';
  }
  
  if (autoDiscardReason) {
    // Suggest descarte (manual 100%): apenas exibe a notificação, não seleciona automaticamente
    showAutoDiscardNotification(autoDiscardReason);
  } else {
    // Hide notification if no auto discard
    hideAutoDiscardNotification();
  }
}

function showAutoDiscardNotification(reason) {
  // Remove existing notification
  const existing = document.getElementById('autoDiscardNotification');
  if (existing) existing.remove();
  
  // Create notification
  const notification = document.createElement('div');
  notification.id = 'autoDiscardNotification';
  notification.className = 'bg-red-50 border border-red-200 rounded-lg p-4 mb-4';
  notification.innerHTML = `
    <div class="flex items-center">
      <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.876c1.17 0 2.25-.16 2.25-1.729 0-.329-.314-.729-.314-1.271L18 7.5c0-.621-.504-1.125-1.125-1.125H7.125C6.504 6.375 6 6.879 6 7.5l-.686 8.5c0 .542-.314.942-.314 1.271 0 1.569 1.08 1.729 2.25 1.729z"></path>
      </svg>
      <div>
        <div class="text-sm font-medium text-red-800">Descarte Automático Detectado</div>
        <div class="text-sm text-red-700 mt-1">${reason}</div>
        <div class="text-xs text-red-600 mt-1">DESCARTE foi selecionado automaticamente. Você pode alterar se necessário.</div>
      </div>
    </div>
  `;
  
  // Insert before destination selection
  const destinoSection = document.querySelector('label[for="destinoSelected"]').parentElement;
  destinoSection.parentElement.insertBefore(notification, destinoSection);
}

function hideAutoDiscardNotification() {
  const notification = document.getElementById('autoDiscardNotification');
  if (notification) notification.remove();
}

// Destination selection
function selectDestino(destino) {
  logActivity('user_action', 'Destination Selected', { destination: destino, previousDestination: selectedDestino });
  
  selectedDestino = destino;
  document.getElementById('destinoSelected').value = destino;
  updateDestinoButtons();
  calculateValue();
  
  // Hide auto discard notification when user manually selects destination
  }

  // Show/hide value calculation for estoque
  const valorDiv = document.getElementById('valorCalculado');
  if (destino === 'estoque') {
    valorDiv.classList.remove('hidden');
  } else {
    valorDiv.classList.add('hidden');
  }

  // Show guidance
  showGuidance(destino);
}

function updateDestinoButtons() {
  document.querySelectorAll('.destino-btn').forEach(btn => {
    const btnDestino = btn.getAttribute('data-destino');
    if (btnDestino === selectedDestino) {
      btn.classList.add('ring-2', 'ring-offset-2');
      if (btnDestino === 'descarte') btn.classList.add('ring-red-500', 'bg-red-50');
      else if (btnDestino === 'estoque') btn.classList.add('ring-green-500', 'bg-green-50');
      else if (btnDestino === 'uso_interno') btn.classList.add('ring-blue-500', 'bg-blue-50');
      else if (btnDestino === 'garantia') btn.classList.add('ring-purple-500', 'bg-purple-50');
    } else {
      btn.classList.remove('ring-2', 'ring-offset-2', 'ring-red-500', 'ring-green-500', 'ring-blue-500', 'ring-purple-500', 'bg-red-50', 'bg-green-50', 'bg-blue-50', 'bg-purple-50');
    }
  });
}

// Value calculation
function calculateValue() {
  const valorDiv = document.getElementById('valorCalculado');
  const valorDisplay = document.getElementById('valorDisplay');
  
  if (selectedDestino === 'estoque') {
    const modo = document.querySelector('input[name="modo"]:checked')?.value || 'peso';
    const custoFolha = window.tonerData ? window.tonerData.custo_por_folha : 0.10;
    const capacidadeFolhas = window.tonerData ? window.tonerData.capacidade_folhas : 1500;
    
    let folhasRestantes = 0;
    
    if (modo === 'peso') {
      // Modo Peso: usar gramatura existente para calcular folhas
      const gramaturaExistente = window.gramaturaExistente || 0;
      const gramaturaPorFolha = window.tonerData ? (window.tonerData.gramatura / capacidadeFolhas) : 0.007;
      folhasRestantes = gramaturaExistente / gramaturaPorFolha;
    } else if (modo === 'chip') {
      // Modo Chip: usar percentual do chip para calcular folhas
      const percentualChip = window.calculatedPercentage || 0;
      folhasRestantes = (capacidadeFolhas * percentualChip) / 100;
    }
    
    // Calcular valor em dinheiro: folhas restantes × custo por folha
    const valor = folhasRestantes * custoFolha;
    valorDisplay.textContent = 'R$ ' + valor.toFixed(2).replace('.', ',') + ' (' + Math.round(folhasRestantes) + ' folhas)';
    valorDiv.classList.remove('hidden');
  } else {
    valorDiv.classList.add('hidden');
  }
}

// Form submission
function submitRetornado() {
  const form = document.getElementById('retornadoForm');
  const formData = new FormData(form);
  
  if (!selectedDestino) {
    alert('Por favor, selecione um destino.');
    return;
  }
  
  fetch('/toners/retornados', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      closeRetornadoModal();
      location.reload();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

// Import functions
function downloadRetornadosTemplate() {
  const data = [
    ['Modelo', 'Código Cliente', 'Usuário', 'Filial', 'Destino', 'Valor Recuperado', 'Data'],
    ['HP CF280A', 'CLI001', 'João Silva', 'Matriz', 'estoque', '125.50', '15/01/2024'],
    ['HP CE285A', 'CLI002', 'Maria Santos', 'Filial 1', 'uso interno', '0.00', '16/01/2024'],
    ['HP CB435A', 'CLI003', 'Pedro Costa', 'Filial 2', 'descarte', '0.00', '17/01/2024'],
    ['HP Q2612A', 'CLI004', 'Ana Oliveira', 'Filial 3', 'garantia', '0.00', '18/01/2024'],
    ['', '', '', '', '', '', '']
  ];
  
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(data);
  
  ws['!cols'] = [
    {wch: 15}, {wch: 15}, {wch: 15}, {wch: 15}, {wch: 15}, {wch: 15}, {wch: 12}
  ];
  
  XLSX.utils.book_append_sheet(wb, ws, "Template Retornados");
  XLSX.writeFile(wb, 'template_retornados.xlsx');
}

function importRetornados() {
  const fileInput = document.getElementById('importFileInput');
  const file = fileInput.files[0];
  
  if (!file) {
    alert('Por favor, selecione um arquivo.');
    return;
  }
  
  // Show progress container
  document.getElementById('importProgress').style.display = 'block';
  document.getElementById('importSubmitBtn').disabled = true;
  document.getElementById('importCancelBtn').disabled = true;
  
  // Read and process Excel file
  const reader = new FileReader();
  reader.onload = function(e) {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, {type: 'array'});
      const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
      const jsonData = XLSX.utils.sheet_to_json(firstSheet, {header: 1});
      
      // Remove header row and empty rows
      const rows = jsonData.slice(1).filter(row => {
        // Check if row has at least one non-empty cell
        return row.some(cell => cell !== undefined && cell !== null && String(cell).trim() !== '');
      });
      
      // Debug: Always log rows for troubleshooting (temporarily)
      addDebugLog(`📊 Total de linhas na planilha: ${jsonData.length}`);
      addDebugLog(`📊 Linhas após filtro: ${rows.length}`);
      jsonData.forEach((row, index) => {
        if (index === 0) {
          addDebugLog(`📋 HEADER: ${JSON.stringify(row)}`);
        } else {
          addDebugLog(`📋 Linha ${index}: ${JSON.stringify(row)} - Filtrada: ${rows.includes(row) ? 'NÃO' : 'SIM'}`);
        }
      });
      
      if (rows.length === 0) {
        alert('Nenhum dado encontrado na planilha.');
        resetImportModal();
        return;
      }
      
      // Process rows with real progress tracking
      processImportRows(rows);
      
    } catch (error) {
      alert('Erro ao ler arquivo: ' + error.message);
      resetImportModal();
    }
  };
  
  reader.readAsArrayBuffer(file);
}

function processImportRows(rows) {
  let processed = 0;
  const total = rows.length;
  const results = [];
  let successful = 0;
  let errors = 0;
  
  async function processNextRow() {
    if (processed >= total) {
      // Import completed
      setTimeout(() => {
        let message = `Importação concluída!\n`;
        message += `${successful} registros importados com sucesso.\n`;
        if (errors > 0) {
          message += `${errors} registros com erro.`;
        }
        
        // Auto-download debug report
        downloadImportReport();
        
        alert(message + '\n\nRelatório de importação baixado automaticamente.');
        closeImportModal();
        location.reload();
      }, 2000);
      return;
    }
    
    const row = rows[processed];
    const progress = ((processed + 1) / total) * 100;
    document.getElementById('importProgressBar').style.width = progress + '%';
    document.getElementById('importStatus').textContent = `Processando linha ${processed + 1} de ${total}...`;
    
    // Prepare row data - expected format: [Modelo, Código Cliente, Usuário, Filial, Destino, Valor Recuperado, Data]
    const rowData = {
      modelo: row[0] || '',
      codigo_cliente: row[1] || '',
      usuario: row[2] || '',
      filial: row[3] || '',
      destino: row[4] || '',
      valor_calculado: parseFloat(row[5]) || 0,
      data_registro: row[6] || ''
    };
    
    addDebugLog(`📤 Enviando linha ${processed + 1}: ${JSON.stringify(rowData)}`);
    
    // Skip completely empty rows
    if (!rowData.modelo && !rowData.codigo_cliente && !rowData.usuario && !rowData.filial && !rowData.destino) {
      addDebugLog(`⏭️ Pulando linha ${processed + 1} - completamente vazia`);
      processed++;
      await new Promise(resolve => setTimeout(resolve, 50));
      await processNextRow();
      return;
    }
    
    try {
      // Send row to server
      const response = await fetch('/toners/retornados/import-row', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(rowData)
      });
      
      const data = await response.json();
      
      addDebugLog(`📥 Resposta linha ${processed + 1}: ${JSON.stringify(data)}`);
      
      const result = {
        row: processed + 1,
        data: rowData,
        response: data,
        success: data.success
      };
      
      importResults.push(result);
      
      if (data.success) {
        successful++;
        addDebugLog(`✅ Linha ${processed + 1} importada com sucesso`);
      } else {
        errors++;
        addDebugLog(`❌ Erro linha ${processed + 1}: ${data.message}`);
      }
    } catch (error) {
      errors++;
      const errorMsg = `Erro de rede linha ${processed + 1}: ${error.message}`;
      addDebugLog(`🚨 ${errorMsg}`);
      
      importResults.push({
        row: processed + 1,
        data: rowData,
        response: { success: false, message: errorMsg },
        success: false
      });
    }
    
    processed++;
    
    // Small delay for animation
    await new Promise(resolve => setTimeout(resolve, 100));
    
    // Continue with next row
    await processNextRow();
  }
  
  // Start processing
  processNextRow();
}

function resetImportModal() {
  document.getElementById('importProgress').style.display = 'none';
  document.getElementById('importSubmitBtn').disabled = false;
  document.getElementById('importCancelBtn').disabled = false;
  document.getElementById('importProgressBar').style.width = '0%';
  document.getElementById('importStatus').textContent = 'Preparando importação...';
}

// Filter and export functions
function filterData() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const dateFrom = document.getElementById('dateFrom').value;
  const dateTo = document.getElementById('dateTo').value;
  
  // Simple client-side filtering
  const rows = document.querySelectorAll('#retornadosTable tr');
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    const show = text.includes(search);
    row.style.display = show ? '' : 'none';
  });
}

function exportData() {
  const dateFrom = prompt('Data inicial (YYYY-MM-DD):');
  const dateTo = prompt('Data final (YYYY-MM-DD):');
  
  if (dateFrom && dateTo) {
    alert(`Exportando dados de ${dateFrom} até ${dateTo}...`);
    // Implement actual export logic
  }
}

// Delete functions
let deleteId = null;

function confirmDelete(id, modelo) {
  deleteId = id;
  document.getElementById('deleteModeloName').textContent = modelo;
  document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
  document.getElementById('deleteModal').classList.add('hidden');
  deleteId = null;
}

function deleteRetornado() {
  if (!deleteId) return;
  
  fetch(`/toners/retornados/delete/${deleteId}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
    }
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert('Registro excluído com sucesso!');
      closeDeleteModal();
      location.reload();
    } else {
      alert('Erro ao excluir registro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

// Ensure functions are available globally
window.openRetornadoModal = openRetornadoModal;
window.closeRetornadoModal = closeRetornadoModal;
window.openImportModal = openImportModal;
window.closeImportModal = closeImportModal;
window.confirmDelete = confirmDelete;
window.closeDeleteModal = closeDeleteModal;
window.deleteRetornado = deleteRetornado;
window.toggleMode = toggleMode;
window.updateTonerData = updateTonerData;
window.calculatePercentage = calculatePercentage;
window.selectDestino = selectDestino;
window.updateDestinoButtons = updateDestinoButtons;
window.calculateValue = calculateValue;
window.showGuidance = showGuidance;
window.checkAutoDiscard = checkAutoDiscard;
window.loadParameters = loadParameters;
window.downloadActivityLog = downloadActivityLog;
window.submitRetornado = submitRetornado;

// Activity log download function
function downloadActivityLog() {
  logActivity('user_action', 'Download Activity Log Requested');
  
  const report = {
    generated_at: new Date().toISOString(),
    page_url: window.location.href,
    user_agent: navigator.userAgent,
    session_duration: Date.now() - (window.sessionStartTime || Date.now()),
    total_activities: activityLog.length,
    
    // Summary statistics
    summary: {
      errors: activityLog.filter(log => log.type === 'error').length,
      user_actions: activityLog.filter(log => log.type === 'user_action').length,
      calculations: activityLog.filter(log => log.type === 'calculation').length,
      validations: activityLog.filter(log => log.type === 'validation').length,
      modal_actions: activityLog.filter(log => log.type === 'modal').length,
      form_actions: activityLog.filter(log => log.type === 'form').length
    },
    
    // Current form state
    current_state: {
      selected_destination: selectedDestino,
      modal_open: !document.getElementById('retornadoModal')?.classList.contains('hidden'),
      debug_mode: debugMode,
      toner_data_loaded: !!window.tonerData,
      parameters_loaded: !!window.parameters
    },
    
    // Browser info
    browser_info: {
      viewport: {
        width: window.innerWidth,
        height: window.innerHeight
      },
      screen: {
        width: screen.width,
        height: screen.height
      },
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
      language: navigator.language
    },
    
    // All activity logs
    activities: activityLog,
    
    // Import results if any
    import_results: importResults.length > 0 ? importResults : null
  };
  
  // Create filename with timestamp
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
  const filename = `retornados-activity-log-${timestamp}.json`;
  
  // Download JSON file
  const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
  
  logActivity('user_action', 'Activity Log Downloaded', { filename, total_entries: activityLog.length });
}

// Initialize session start time
window.sessionStartTime = Date.now();
logActivity('system', 'Page Loaded', { timestamp: new Date().toISOString() });
</script>
