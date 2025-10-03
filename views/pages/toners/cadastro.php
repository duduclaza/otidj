<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Cadastro de Toners</h1>
    <button onclick="openImportModal()" class="px-4 py-2 rounded-lg bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700 flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200 font-medium">
      <span>📊</span>
      Importar
    </button>
  </div>
  
  <!-- Formulário de Cadastro -->
  <div class="bg-white border rounded-lg p-6">
    <h2 class="text-lg font-medium mb-4">Cadastrar Novo Toner</h2>
    <form method="post" action="/toners/cadastro" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
        <input type="text" name="modelo" placeholder="Ex: HP CF280A" class="w-full border rounded px-3 py-2" required>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Peso Cheio (g) *</label>
        <input type="number" step="0.01" name="peso_cheio" placeholder="Ex: 850.50" class="w-full border rounded px-3 py-2" required onchange="calcularCampos()">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Peso Vazio (g) *</label>
        <input type="number" step="0.01" name="peso_vazio" placeholder="Ex: 120.30" class="w-full border rounded px-3 py-2" required onchange="calcularCampos()">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gramatura (g)</label>
        <input type="number" step="0.01" name="gramatura" placeholder="Calculado automaticamente" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Capacidade de Folhas *</label>
        <input type="number" name="capacidade_folhas" placeholder="Ex: 2700" class="w-full border rounded px-3 py-2" required onchange="calcularCampos()">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Preço do Toner (R$) *</label>
        <input type="number" step="0.01" name="preco_toner" placeholder="Ex: 89.90" class="w-full border rounded px-3 py-2" required onchange="calcularCampos()">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gramatura por Folha (g)</label>
        <input type="number" step="0.0001" name="gramatura_por_folha" placeholder="Calculado automaticamente" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Custo por Folha (R$)</label>
        <input type="number" step="0.0001" name="custo_por_folha" placeholder="Calculado automaticamente" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Cor *</label>
        <select name="cor" class="w-full border rounded px-3 py-2" required>
          <option value="">Selecione a cor</option>
          <option value="Yellow">Yellow</option>
          <option value="Magenta">Magenta</option>
          <option value="Cyan">Cyan</option>
          <option value="Black">Black</option>
        </select>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
        <select name="tipo" class="w-full border rounded px-3 py-2" required>
          <option value="">Selecione o tipo</option>
          <option value="Original">Original</option>
          <option value="Compativel">Compatível</option>
          <option value="Remanufaturado">Remanufaturado</option>
        </select>
      </div>
      
      <div class="md:col-span-2 lg:col-span-3">
        <button type="submit" class="px-6 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Salvar Toner</button>
      </div>
    </form>
  </div>

  <!-- Lista/Grid -->
  <div class="bg-white border rounded-lg">
    <div class="px-4 py-3 border-b flex justify-between items-center">
      <div>
        <h2 class="text-lg font-medium">Toners Cadastrados</h2>
        <?php if (isset($pagination)): ?>
          <p class="text-sm text-gray-600 mt-1">
            <?= $pagination['total_records'] ?> toner(s) encontrado(s) • 
            Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>
          </p>
        <?php endif; ?>
      </div>
      <button onclick="exportToExcel()" class="px-3 py-1 text-sm rounded bg-green-600 text-white hover:bg-green-700 flex items-center space-x-1 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <span>Exportar Excel</span>
      </button>
    </div>
    
    <!-- Campo de Busca -->
    <div class="px-4 py-3 border-b bg-gray-50">
      <div class="flex gap-3 items-center">
        <!-- Dropdown de Coluna -->
        <select 
          id="searchColumn" 
          class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
        >
          <option value="all">Todas as colunas</option>
          <option value="0">Modelo</option>
          <option value="8">Cor</option>
          <option value="9">Tipo</option>
        </select>
        
        <!-- Campo de Busca -->
        <div class="relative flex-1 max-w-md">
          <input 
            type="text" 
            id="searchToners" 
            placeholder="Digite para buscar..." 
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            onkeyup="searchToners()" 
            oninput="searchToners()"
          >
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Modelo</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Peso Cheio</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Peso Vazio</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Gramatura</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Cap. Folhas</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Preço</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Gram/Folha</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Custo/Folha</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Cor</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Tipo</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">
              <div class="flex items-center space-x-1">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Última Atualização</span>
              </div>
            </th>
            <th class="px-3 py-2 text-right font-medium text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($toners)): ?>
            <tr>
              <td colspan="12" class="px-4 py-8 text-center text-gray-500">Nenhum toner cadastrado</td>
            </tr>
          <?php else: ?>
            <?php foreach ($toners as $t): ?>
              <tr>
                <td class="px-3 py-2">
                  <span class="edit-display-modelo-<?= $t['id'] ?>"><?= e($t['modelo']) ?></span>
                  <input type="text" class="edit-input-modelo-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-full text-xs" value="<?= e($t['modelo']) ?>">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-peso_cheio-<?= $t['id'] ?>"><?= number_format($t['peso_cheio'], 2) ?>g</span>
                  <input type="number" step="0.01" class="edit-input-peso_cheio-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['peso_cheio'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-peso_vazio-<?= $t['id'] ?>"><?= number_format($t['peso_vazio'], 2) ?>g</span>
                  <input type="number" step="0.01" class="edit-input-peso_vazio-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['peso_vazio'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-gramatura-<?= $t['id'] ?>"><?= number_format($t['gramatura'], 2) ?>g</span>
                  <input type="number" step="0.01" class="edit-input-gramatura-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs bg-gray-100" readonly>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-capacidade_folhas-<?= $t['id'] ?>"><?= number_format($t['capacidade_folhas']) ?></span>
                  <input type="number" class="edit-input-capacidade_folhas-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['capacidade_folhas'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-preco_toner-<?= $t['id'] ?>">R$ <?= number_format($t['preco_toner'], 2, ',', '.') ?></span>
                  <input type="number" step="0.01" class="edit-input-preco_toner-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['preco_toner'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-gramatura_por_folha-<?= $t['id'] ?>"><?= number_format($t['gramatura_por_folha'], 4) ?>g</span>
                  <input type="number" step="0.0001" class="edit-input-gramatura_por_folha-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs bg-gray-100" readonly>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-custo_por_folha-<?= $t['id'] ?>">R$ <?= number_format($t['custo_por_folha'], 4, ',', '.') ?></span>
                  <input type="number" step="0.0001" class="edit-input-custo_por_folha-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs bg-gray-100" readonly>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-cor-<?= $t['id'] ?>"><?= e($t['cor']) ?></span>
                  <select class="edit-input-cor-<?= $t['id'] ?> border rounded px-2 py-1 hidden text-xs">
                    <option value="Yellow" <?= $t['cor'] === 'Yellow' ? 'selected' : '' ?>>Yellow</option>
                    <option value="Magenta" <?= $t['cor'] === 'Magenta' ? 'selected' : '' ?>>Magenta</option>
                    <option value="Cyan" <?= $t['cor'] === 'Cyan' ? 'selected' : '' ?>>Cyan</option>
                    <option value="Black" <?= $t['cor'] === 'Black' ? 'selected' : '' ?>>Black</option>
                  </select>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-tipo-<?= $t['id'] ?>"><?= e($t['tipo']) ?></span>
                  <select class="edit-input-tipo-<?= $t['id'] ?> border rounded px-2 py-1 hidden text-xs">
                    <option value="Original" <?= $t['tipo'] === 'Original' ? 'selected' : '' ?>>Original</option>
                    <option value="Compativel" <?= $t['tipo'] === 'Compativel' ? 'selected' : '' ?>>Compatível</option>
                    <option value="Remanufaturado" <?= $t['tipo'] === 'Remanufaturado' ? 'selected' : '' ?>>Remanufaturado</option>
                  </select>
                </td>
                <td class="px-3 py-2">
                  <?php 
                  $updatedTime = strtotime($t['updated_at']);
                  $timeDiff = time() - $updatedTime;
                  $isRecent = $timeDiff < 86400; // 24 horas
                  $textColor = $isRecent ? 'text-green-600' : 'text-gray-600';
                  $iconColor = $isRecent ? 'text-green-500' : 'text-gray-400';
                  
                  // Formato de tempo relativo
                  if ($timeDiff < 3600) { // Menos de 1 hora
                    $timeAgo = 'há ' . floor($timeDiff / 60) . ' min';
                  } elseif ($timeDiff < 86400) { // Menos de 24 horas
                    $timeAgo = 'há ' . floor($timeDiff / 3600) . 'h';
                  } elseif ($timeDiff < 2592000) { // Menos de 30 dias
                    $timeAgo = 'há ' . floor($timeDiff / 86400) . ' dias';
                  } else {
                    $timeAgo = date('d/m/Y', $updatedTime);
                  }
                  ?>
                  <div class="flex items-center space-x-1">
                    <svg class="w-3 h-3 <?= $iconColor ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex flex-col">
                      <span class="text-xs <?= $textColor ?>" title="Última atualização: <?= date('d/m/Y H:i:s', $updatedTime) ?><?= $isRecent ? ' (Recente)' : '' ?>">
                        <?= date('d/m/Y H:i', $updatedTime) ?>
                      </span>
                      <span class="text-xs text-gray-400 italic">
                        <?= $timeAgo ?>
                        <?php if ($isRecent): ?>
                          <span class="inline-block w-1.5 h-1.5 bg-green-400 rounded-full ml-1" title="Atualizado nas últimas 24 horas"></span>
                        <?php endif; ?>
                      </span>
                    </div>
                  </div>
                </td>
                <td class="px-3 py-2 text-right space-x-1">
                  <button onclick="editToner(<?= $t['id'] ?>)" class="edit-btn-<?= $t['id'] ?> text-blue-600 hover:text-blue-800 text-xs">Editar</button>
                  <button onclick="saveToner(<?= $t['id'] ?>)" class="save-btn-<?= $t['id'] ?> text-green-600 hover:text-green-800 text-xs hidden">Salvar</button>
                  <button onclick="cancelEditToner(<?= $t['id'] ?>)" class="cancel-btn-<?= $t['id'] ?> text-gray-600 hover:text-gray-800 text-xs hidden">Cancelar</button>
                  <button onclick="deleteToner(<?= $t['id'] ?>)" class="text-red-600 hover:text-red-800 text-xs">Excluir</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Paginação -->
    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
    <div class="px-4 py-3 border-t bg-gray-50 flex items-center justify-between">
      <div class="flex items-center text-sm text-gray-700">
        <span>
          Mostrando 
          <span class="font-medium"><?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?></span>
          até 
          <span class="font-medium"><?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']) ?></span>
          de 
          <span class="font-medium"><?= $pagination['total_records'] ?></span>
          resultados
        </span>
      </div>
      
      <div class="flex items-center space-x-2">
        <!-- Botão Anterior -->
        <?php if ($pagination['has_previous']): ?>
          <a href="?page=<?= $pagination['previous_page'] ?>" 
             class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Anterior
          </a>
        <?php else: ?>
          <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Anterior
          </span>
        <?php endif; ?>

        <!-- Números das páginas -->
        <div class="flex items-center space-x-1">
          <?php
          $start = max(1, $pagination['current_page'] - 2);
          $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
          
          // Mostrar primeira página se não estiver no range
          if ($start > 1): ?>
            <a href="?page=1" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors">1</a>
            <?php if ($start > 2): ?>
              <span class="px-2 py-2 text-sm text-gray-500">...</span>
            <?php endif; ?>
          <?php endif; ?>

          <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $pagination['current_page']): ?>
              <span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-md">
                <?= $i ?>
              </span>
            <?php else: ?>
              <a href="?page=<?= $i ?>" 
                 class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors">
                <?= $i ?>
              </a>
            <?php endif; ?>
          <?php endfor; ?>

          <!-- Mostrar última página se não estiver no range -->
          <?php if ($end < $pagination['total_pages']): ?>
            <?php if ($end < $pagination['total_pages'] - 1): ?>
              <span class="px-2 py-2 text-sm text-gray-500">...</span>
            <?php endif; ?>
            <a href="?page=<?= $pagination['total_pages'] ?>" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors">
              <?= $pagination['total_pages'] ?>
            </a>
          <?php endif; ?>
        </div>

        <!-- Botão Próximo -->
        <?php if ($pagination['has_next']): ?>
          <a href="?page=<?= $pagination['next_page'] ?>" 
             class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors">
            Próximo
            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </a>
        <?php else: ?>
          <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
            Próximo
            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </span>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Import Modal -->
  <div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center p-4" style="z-index: 999999; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white rounded-t-lg">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">📊 Importar Toners</h3>
              <p class="text-sm text-gray-600 mt-1">Faça upload de um arquivo Excel ou CSV com os dados dos toners</p>
            </div>
          </div>
          <!-- Close Button -->
          <button onclick="console.log('X clicado!'); event.stopPropagation(); closeImportModal();" class="flex-shrink-0 w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center transition-colors duration-200 group">
            <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Content -->
      <div class="px-6 py-4 space-y-4">
        <!-- File Input -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-3">
            📁 Selecione o arquivo Excel ou CSV:
          </label>
          <div class="relative group">
            <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" 
                   class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-4 text-sm focus:ring-3 focus:ring-blue-200 focus:border-blue-400 hover:border-gray-400 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
              <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
              </svg>
            </div>
          </div>
          <div class="flex items-center mt-2 text-xs text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Formatos aceitos: <span class="font-medium">.xlsx, .xls, .csv</span> • Tamanho máximo: <span class="font-medium">10MB</span>
          </div>
        </div>
        
        <!-- Progress Bar (hidden by default) -->
        <div id="progressContainer" class="hidden">
          <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-blue-200 rounded-xl p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-2"></div>
                <span class="text-sm font-semibold text-gray-700">⚡ Progresso da Importação</span>
              </div>
              <span id="progressText" class="text-sm font-bold text-blue-600">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
              <div id="progressBar" class="bg-gradient-to-r from-blue-500 via-blue-600 to-green-500 h-4 rounded-full transition-all duration-500 ease-out shadow-sm" style="width: 0%"></div>
            </div>
            <div id="importStatus" class="text-sm text-gray-700 bg-white rounded-lg p-3 mt-3 border border-gray-200 shadow-sm">
              Preparando importação...
            </div>
          </div>
        </div>
      </div>
      
      <!-- Footer -->
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
        <!-- Template Download -->
        <div class="mb-3">
          <button onclick="downloadTemplate()" 
                  class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-700 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg hover:from-blue-100 hover:to-blue-200 hover:border-blue-300 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200 shadow-sm hover:shadow">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            📥 Baixar Template
          </button>
        </div>
        
        <!-- Import Button -->
        <div>
          <button id="importBtn" onclick="importExcel()" 
                  class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-green-600 border border-green-500 rounded-lg hover:from-green-600 hover:to-green-700 hover:border-green-600 focus:ring-2 focus:ring-green-200 focus:ring-opacity-50 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-md">
            <span class="flex items-center justify-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
              📤 Importar Dados
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center p-4" style="z-index: 999999; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white rounded-t-lg">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">📊 Importar Toners</h3>
            <p class="text-sm text-gray-600 mt-1">Faça upload de um arquivo Excel ou CSV com os dados dos toners</p>
          </div>
        </div>
        <!-- Close Button -->
        <button onclick="console.log('X clicado!'); event.stopPropagation(); closeImportModal();" class="flex-shrink-0 w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center transition-colors duration-200 group">
          <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Content -->
    <div class="px-6 py-4 space-y-4">
      <!-- File Input -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-3">
          📁 Selecione o arquivo Excel ou CSV:
        </label>
        <div class="relative group">
          <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" 
                 class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-4 text-sm focus:ring-3 focus:ring-blue-200 focus:border-blue-400 hover:border-gray-400 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
          <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
            </svg>
          </div>
        </div>
        <div class="flex items-center mt-2 text-xs text-gray-500">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Formatos aceitos: <span class="font-medium">.xlsx, .xls, .csv</span> • Tamanho máximo: <span class="font-medium">10MB</span>
        </div>
      </div>
      
      <!-- Progress Bar (hidden by default) -->
      <div id="progressContainer" class="hidden">
        <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-blue-200 rounded-xl p-4 mb-4">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
              <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-2"></div>
              <span class="text-sm font-semibold text-gray-700">⚡ Progresso da Importação</span>
            </div>
            <span id="progressText" class="text-sm font-bold text-blue-600">0%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
            <div id="progressBar" class="bg-gradient-to-r from-blue-500 via-blue-600 to-green-500 h-4 rounded-full transition-all duration-500 ease-out shadow-sm" style="width: 0%"></div>
          </div>
          <div id="importStatus" class="text-sm text-gray-700 bg-white rounded-lg p-3 mt-3 border border-gray-200 shadow-sm">
            Preparando importação...
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
      <!-- Template Download -->
      <div class="mb-3">
        <button onclick="downloadTemplate()" 
                class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-700 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg hover:from-blue-100 hover:to-blue-200 hover:border-blue-300 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200 shadow-sm hover:shadow">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          📥 Baixar Template
        </button>
      </div>
      
      <!-- Import Button -->
      <div>
        <button id="importBtn" onclick="importExcel()" 
                class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-green-600 border border-green-500 rounded-lg hover:from-green-600 hover:to-green-700 hover:border-green-600 focus:ring-2 focus:ring-green-200 focus:ring-opacity-50 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-md">
          <span class="flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            📤 Importar Dados
          </span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Cálculos automáticos no formulário
function calcularCampos() {
  const pesocheio = parseFloat(document.querySelector('input[name="peso_cheio"]').value) || 0;
  const pesovazio = parseFloat(document.querySelector('input[name="peso_vazio"]').value) || 0;
  const capacidade = parseInt(document.querySelector('input[name="capacidade_folhas"]').value) || 0;
  const preco = parseFloat(document.querySelector('input[name="preco_toner"]').value) || 0;
  
  const gramatura = pesocheio - pesovazio;
  document.querySelector('input[name="gramatura"]').value = gramatura.toFixed(2);
  
  if (capacidade > 0) {
    const gramaturaFolha = gramatura / capacidade;
    document.querySelector('input[name="gramatura_por_folha"]').value = gramaturaFolha.toFixed(4);
    
    const custoFolha = preco / capacidade;
    document.querySelector('input[name="custo_por_folha"]').value = custoFolha.toFixed(4);
  }
}

// Cálculos na edição
function calcularEdicao(id) {
  const pesocheio = parseFloat(document.querySelector('.edit-input-peso_cheio-' + id).value) || 0;
  const pesovazio = parseFloat(document.querySelector('.edit-input-peso_vazio-' + id).value) || 0;
  const capacidade = parseInt(document.querySelector('.edit-input-capacidade_folhas-' + id).value) || 0;
  const preco = parseFloat(document.querySelector('.edit-input-preco_toner-' + id).value) || 0;
  
  const gramatura = pesocheio - pesovazio;
  document.querySelector('.edit-input-gramatura-' + id).value = gramatura.toFixed(2);
  
  if (capacidade > 0) {
    const gramaturaFolha = gramatura / capacidade;
    document.querySelector('.edit-input-gramatura_por_folha-' + id).value = gramaturaFolha.toFixed(4);
    
    const custoFolha = preco / capacidade;
    document.querySelector('.edit-input-custo_por_folha-' + id).value = custoFolha.toFixed(4);
  }
}

// Edição inline
function editToner(id) {
  const fields = ['modelo', 'peso_cheio', 'peso_vazio', 'gramatura', 'capacidade_folhas', 'preco_toner', 'gramatura_por_folha', 'custo_por_folha', 'cor', 'tipo'];
  fields.forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.add('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.remove('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.add('hidden');
  document.querySelector('.save-btn-' + id).classList.remove('hidden');
  document.querySelector('.cancel-btn-' + id).classList.remove('hidden');
}

function cancelEditToner(id) {
  const fields = ['modelo', 'peso_cheio', 'peso_vazio', 'gramatura', 'capacidade_folhas', 'preco_toner', 'gramatura_por_folha', 'custo_por_folha', 'cor', 'tipo'];
  fields.forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.remove('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.add('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.remove('hidden');
  document.querySelector('.save-btn-' + id).classList.add('hidden');
  document.querySelector('.cancel-btn-' + id).classList.add('hidden');
}

function saveToner(id) {
  const modelo = document.querySelector('.edit-input-modelo-' + id).value.trim();
  const peso_cheio = document.querySelector('.edit-input-peso_cheio-' + id).value;
  const peso_vazio = document.querySelector('.edit-input-peso_vazio-' + id).value;
  const capacidade_folhas = document.querySelector('.edit-input-capacidade_folhas-' + id).value;
  const preco_toner = document.querySelector('.edit-input-preco_toner-' + id).value;
  const cor = document.querySelector('.edit-input-cor-' + id).value;
  const tipo = document.querySelector('.edit-input-tipo-' + id).value;
  
  if (!modelo || !peso_cheio || !peso_vazio || !capacidade_folhas || !preco_toner || !cor || !tipo) {
    alert('Todos os campos são obrigatórios');
    return;
  }
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/toners/update';
  form.innerHTML = `
    <input type="hidden" name="id" value="${id}">
    <input type="hidden" name="modelo" value="${modelo}">
    <input type="hidden" name="peso_cheio" value="${peso_cheio}">
    <input type="hidden" name="peso_vazio" value="${peso_vazio}">
    <input type="hidden" name="capacidade_folhas" value="${capacidade_folhas}">
    <input type="hidden" name="preco_toner" value="${preco_toner}">
    <input type="hidden" name="cor" value="${cor}">
    <input type="hidden" name="tipo" value="${tipo}">
  `;
  document.body.appendChild(form);
  form.submit();
}

function deleteToner(id) {
  if (!confirm('Tem certeza que deseja excluir este toner?')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/toners/delete';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
  document.body.appendChild(form);
  form.submit();
}

// Função de teste
function testModal() {
  alert('Teste de JavaScript funcionando!');
  console.log('Teste de JavaScript funcionando!');
  
  // Criar um modal de teste simples
  const testDiv = document.createElement('div');
  testDiv.innerHTML = `
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 99999; display: flex; align-items: center; justify-content: center;">
      <div style="background: white; padding: 20px; border-radius: 8px; max-width: 400px;">
        <h3>Modal de Teste</h3>
        <p>Se você está vendo isso, o JavaScript está funcionando!</p>
        <button onclick="this.closest('div').parentElement.remove()" style="background: #dc2626; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">Fechar</button>
      </div>
    </div>
  `;
  document.body.appendChild(testDiv);
}

// Função para forçar modal manualmente (use no Console se necessário)
function forceShowModal() {
  const modal = document.getElementById('importModal');
  if (modal) {
    // Remover todas as classes
    modal.className = '';
    
    // Aplicar estilos brutalmente
    modal.setAttribute('style', `
      display: flex !important;
      position: fixed !important;
      top: 0px !important;
      left: 0px !important;
      width: 100vw !important;
      height: 100vh !important;
      z-index: 999999 !important;
      background: rgba(255, 0, 0, 0.9) !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 16px !important;
      visibility: visible !important;
      opacity: 1 !important;
    `);
    
    console.log('Modal forçado a aparecer com fundo vermelho!');
    return true;
  }
  return false;
}

// Modal functions
function openImportModal() {
  let modal = document.getElementById('importModal');
  
  // Se o modal existe, mover para o body principal para aparecer por cima de tudo
  if (modal) {
    // Mover modal para o body principal (fora do iframe)
    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
    // Remover hidden e forçar estilos necessários
    modal.classList.remove('hidden');
    modal.style.cssText = `
      display: flex !important;
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100vw !important;
      height: 100vh !important;
      z-index: 999999 !important;
      background-color: rgba(0, 0, 0, 0.85) !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 16px !important;
      visibility: visible !important;
      opacity: 1 !important;
    `;
    
    // Garantir que o modal apareça por cima de tudo
    document.body.style.overflow = 'hidden'; // Impede scroll da página
    
    // Garantir que o conteúdo interno seja visível
    const modalContent = modal.querySelector('.bg-white');
    if (modalContent) {
      modalContent.style.cssText = `
        background: white !important;
        border-radius: 8px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        width: 100% !important;
        max-width: 28rem !important;
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 100000 !important;
        position: relative !important;
      `;
    }
    
    // Adicionar evento de clique no overlay para fechar
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeImportModal();
      }
    });
    
  } else {
    // Fallback: criar modal dinamicamente no body principal
    createFullScreenModal();
  }
}

// Função para criar modal em tela cheia
function createFullScreenModal() {
  // Remover modal existente se houver
  const existingModal = document.getElementById('fullScreenImportModal');
  if (existingModal) {
    existingModal.remove();
  }
  
  // Bloquear scroll da página
  document.body.style.overflow = 'hidden';
  
  const modalHTML = `
    <div id="fullScreenImportModal" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.9); z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 16px;">
      <div style="background: white; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); width: 100%; max-width: 28rem;" onclick="event.stopPropagation()">
        <!-- Header -->
        <div style="padding: 24px 24px 16px 24px; border-bottom: 1px solid #e5e7eb; background: linear-gradient(to right, #f9fafb, white); border-radius: 12px 12px 0 0; display: flex; align-items: center; justify-content: space-between;">
          <div style="display: flex; align-items: center;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
              <svg style="width: 24px; height: 24px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
            </div>
            <div>
              <h3 style="font-size: 20px; font-weight: bold; color: #111827; margin: 0;">📊 Importar Toners</h3>
              <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;">Faça upload de um arquivo Excel ou CSV com os dados dos toners</p>
            </div>
          </div>
          <button onclick="closeFullScreenModal()" style="width: 32px; height: 32px; background: #f3f4f6; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
            <svg style="width: 20px; height: 20px; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <!-- Content -->
        <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
          <!-- File Input -->
          <div>
            <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px;">
              📁 Selecione o arquivo Excel ou CSV:
            </label>
            <input type="file" id="fullScreenFileInput" accept=".xlsx,.xls,.csv" 
                   style="width: 100%; border: 2px dashed #d1d5db; border-radius: 12px; padding: 16px; font-size: 14px; transition: all 0.2s;" 
                   onchange="this.style.borderColor='#3b82f6'"
                   onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)'"
                   onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
            <div style="flex items-center mt-2 text-xs text-gray-500">
              <svg style="width: 16px; height: 16px; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Formatos aceitos: <span style="font-weight: 500;">.xlsx, .xls, .csv</span> • Tamanho máximo: <span style="font-weight: 500;">10MB</span>
            </div>
          </div>
          
          <!-- Progress Container -->
          <div id="fullScreenProgressContainer" style="display: none;">
            <div style="background: linear-gradient(to right, #dbeafe, #dcfce7); border: 1px solid #3b82f6; border-radius: 12px; padding: 16px;">
              <div style="flex items-center justify-between mb-3">
                <div style="flex items-center">
                  <div style="width: 20px; height: 20px; border: 2px solid #3b82f6; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite; margin-right: 8px;"></div>
                  <span style="font-size: 14px; font-weight: 600; color: #374151;">⚡ Progresso da Importação</span>
                </div>
                <span id="fullScreenProgressText" style="font-size: 14px; font-weight: bold; color: #3b82f6;">0%</span>
              </div>
              <div style="width: 100%; background: #e5e7eb; border-radius: 9999px; height: 16px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                <div id="fullScreenProgressBar" style="background: linear-gradient(to right, #3b82f6, #10b981); height: 16px; border-radius: 9999px; transition: all 0.5s ease-out; width: 0%;"></div>
              </div>
              <div id="fullScreenImportStatus" style="font-size: 14px; color: #374151; background: white; border-radius: 8px; padding: 12px; margin-top: 12px; border: 1px solid #e5e7eb;">
                Preparando importação...
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer -->
        <div style="padding: 16px 24px 24px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb; border-radius: 0 0 12px 12px;">
          <!-- Template Download -->
          <div style="margin-bottom: 12px;">
            <button onclick="downloadTemplate()" 
                    style="width: 100%; display: flex; align-items: center; justify-content: center; padding: 12px 16px; font-size: 14px; font-weight: 500; color: #1d4ed8; background: linear-gradient(to right, #dbeafe, #bfdbfe); border: 1px solid #3b82f6; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;"
                    onmouseover="this.style.background='linear-gradient(to right, #bfdbfe, #93c5fd)'"
                    onmouseout="this.style.background='linear-gradient(to right, #dbeafe, #bfdbfe)'">
              <svg style="width: 16px; height: 16px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              📥 Baixar Template
            </button>
          </div>
          
          <!-- Import Button -->
          <div>
            <button onclick="importFullScreenExcel()" 
                    style="width: 100%; padding: 12px 16px; font-size: 14px; font-weight: 600; color: white; background: linear-gradient(to right, #10b981, #059669); border: 1px solid #10b981; border-radius: 8px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
              <span style="display: flex; align-items: center; justify-content: center;">
                <svg style="width: 16px; height: 16px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                📤 Importar Dados
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <style>
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    </style>
  `;
  
  // Adicionar ao body principal
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  // Adicionar event listener para fechar ao clicar no overlay
  document.getElementById('fullScreenImportModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeFullScreenModal();
    }
  });
}

// Funções para o modal em tela cheia
function closeFullScreenModal() {
  document.body.style.overflow = '';
  const modal = document.getElementById('fullScreenImportModal');
  if (modal) {
    modal.remove();
  }
}

function importFullScreenExcel() {
  const fileInput = document.getElementById('fullScreenFileInput');
  const file = fileInput.files[0];
  
  if (!file) {
    alert('Por favor, selecione um arquivo Excel.');
    return;
  }
  
  // Mostrar progress
  document.getElementById('fullScreenProgressContainer').style.display = 'block';
  
  const formData = new FormData();
  formData.append('excel_file', file);
  
  updateFullScreenProgress(10, 'Enviando arquivo...');
  
  fetch('/toners/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      updateFullScreenProgress(100, `Concluído! ${result.imported} registros importados`);
      setTimeout(() => {
        closeFullScreenModal();
        alert('Importação concluída com sucesso!');
        location.reload();
      }, 2000);
    } else {
      alert('Erro na importação: ' + result.message);
      document.getElementById('fullScreenProgressContainer').style.display = 'none';
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
    document.getElementById('fullScreenProgressContainer').style.display = 'none';
  });
}

function updateFullScreenProgress(percentage, status) {
  document.getElementById('fullScreenProgressBar').style.width = percentage + '%';
  document.getElementById('fullScreenProgressText').textContent = percentage + '%';
  document.getElementById('fullScreenImportStatus').textContent = status;
}

// Função para criar modal dinamicamente
function createDynamicModal() {
  // Remover modal existente se houver
  const existingModal = document.getElementById('dynamicImportModal');
  if (existingModal) {
    existingModal.remove();
  }
  
  const modalHTML = `
    <div id="dynamicImportModal" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.8); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px;">
      <div style="background: white; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); width: 100%; max-width: 28rem;" onclick="event.stopPropagation()">
        <!-- Header -->
        <div style="padding: 24px 24px 16px 24px; border-bottom: 1px solid #e5e7eb;">
          <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Importar Toners</h3>
        </div>
        
        <!-- Content -->
        <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
          <!-- File Input -->
          <div>
            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">
              Selecione o arquivo Excel:
            </label>
            <input type="file" id="dynamicExcelFileInput" accept=".xlsx,.xls,.csv" 
                   style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 12px; font-size: 14px;">
            <p style="font-size: 12px; color: #6b7280; margin-top: 4px; margin-bottom: 0;">Formatos aceitos: .xlsx, .xls, .csv</p>
          </div>
          
          <!-- Progress Container -->
          <div id="dynamicProgressContainer" style="display: none;">
            <div style="margin-bottom: 12px;">
              <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">
                <span>Progresso da Importação</span>
                <span id="dynamicProgressText">0%</span>
              </div>
              <div style="width: 100%; background: #e5e7eb; border-radius: 9999px; height: 12px;">
                <div id="dynamicProgressBar" style="background: linear-gradient(to right, #3b82f6, #2563eb); height: 12px; border-radius: 9999px; transition: all 0.5s ease-out; width: 0%;"></div>
              </div>
            </div>
            <div id="dynamicImportStatus" style="font-size: 14px; color: #4b5563; background: #f9fafb; border-radius: 8px; padding: 8px;"></div>
          </div>
        </div>
        
        <!-- Footer -->
        <div style="padding: 16px 24px 24px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb; border-radius: 0 0 8px 8px;">
          <!-- Template Download -->
          <div style="margin-bottom: 12px;">
            <button onclick="downloadTemplate()" 
                    style="width: 100%; display: flex; align-items: center; justify-content: center; padding: 8px 16px; font-size: 14px; font-weight: 500; color: #1d4ed8; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;">
              📥 Baixar Template Excel
            </button>
          </div>
          
          <!-- Action Buttons -->
          <div style="display: flex; gap: 12px;">
            <button onclick="closeDynamicModal()" 
                    style="flex: 1; padding: 8px 16px; font-size: 14px; font-weight: 500; color: #374151; background: white; border: 1px solid #d1d5db; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;">
              Cancelar
            </button>
            <button onclick="importDynamicExcel()" 
                    style="flex: 1; padding: 8px 16px; font-size: 14px; font-weight: 500; color: white; background: #16a34a; border: 1px solid transparent; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;">
              📤 Importar Dados
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  // Adicionar event listener para fechar ao clicar no overlay
  document.getElementById('dynamicImportModal').addEventListener('click', closeDynamicModal);
}

// Funções para o modal dinâmico
function closeDynamicModal() {
  const modal = document.getElementById('dynamicImportModal');
  if (modal) {
    modal.remove();
  }
}

function importDynamicExcel() {
  const fileInput = document.getElementById('dynamicExcelFileInput');
  const file = fileInput.files[0];
  
  if (!file) {
    alert('Por favor, selecione um arquivo Excel.');
    return;
  }
  
  // Usar a mesma lógica de importação, mas com IDs dinâmicos
  document.getElementById('dynamicProgressContainer').style.display = 'block';
  
  const formData = new FormData();
  formData.append('excel_file', file);
  
  updateDynamicProgress(10, 'Enviando arquivo...');
  
  fetch('/toners/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      updateDynamicProgress(100, `Concluído! ${result.imported} registros importados`);
      setTimeout(() => {
        closeDynamicModal();
        alert('Importação concluída com sucesso!');
        location.reload();
      }, 2000);
    } else {
      alert('Erro na importação: ' + result.message);
      document.getElementById('dynamicProgressContainer').style.display = 'none';
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
    document.getElementById('dynamicProgressContainer').style.display = 'none';
  });
}

function updateDynamicProgress(percentage, status) {
  document.getElementById('dynamicProgressBar').style.width = percentage + '%';
  document.getElementById('dynamicProgressText').textContent = percentage + '%';
  document.getElementById('dynamicImportStatus').textContent = status;
}

function closeImportModal() {
  console.log('🔥 FORÇANDO FECHAMENTO DO MODAL...');
  
  // Restaurar scroll da página
  document.body.style.overflow = '';
  
  // Buscar e fechar TODOS os modals de importação
  const modalIds = ['importModal', 'dynamicImportModal', 'fullScreenImportModal'];
  
  modalIds.forEach(id => {
    const modal = document.getElementById(id);
    if (modal) {
      // Forçar fechamento brutal
      modal.remove(); // Remove completamente
      console.log('✅ Modal removido:', id);
    }
  });
  
  // Buscar por qualquer elemento com z-index alto (provavelmente modal)
  const allElements = document.querySelectorAll('*');
  allElements.forEach(el => {
    const zIndex = parseInt(window.getComputedStyle(el).zIndex);
    if (zIndex > 99999 && el.style.position === 'fixed') {
      el.remove();
      console.log('✅ Elemento de z-index alto removido');
    }
  });
  
  console.log(' Fechamento concluído!');
}

function downloadTemplate() {
  // Create Excel template with proper 
  const data = [
    ['Modelo', 'Peso Cheio (g)', 'Peso Vazio (g)', 'Capacidade Folhas', 'Preço Toner (R$)', 'Cor', 'Tipo'],
    ['HP CF280A', 850.5, 120.3, 2700, 89.90, 'Black', 'Original'],
    ['Canon 045', 720.8, 110.2, 1300, 75.50, 'Yellow', 'Compativel'],
    ['Brother TN-421', 680.9, 105.1, 1800, 65.00, 'Magenta', 'Remanufaturado']
  ];
  
  // Create workbook
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(data);
  
  // Set column widths
  ws['!cols'] = [
    {wch: 15}, // Modelo
    {wch: 15}, // Peso Cheio
    {wch: 15}, // Peso Vazio
    {wch: 18}, // Capacidade
    {wch: 18}, // Preço
    {wch: 12}, // Cor
    {wch: 15}  // Tipo
  ];
  
  // Style header row
  const headerStyle = {
    font: { bold: true, color: { rgb: "FFFFFF" } },
    fill: { fgColor: { rgb: "4F46E5" } },
    alignment: { horizontal: "center" }
  };
  
  // Apply header styling
  for (let col = 0; col < 7; col++) {
    const cellRef = XLSX.utils.encode_cell({ r: 0, c: col });
    if (!ws[cellRef]) ws[cellRef] = {};
    ws[cellRef].s = headerStyle;
  }
  
  // Add data validation for Cor column (F)
  ws['!dataValidation'] = {
    F2: { type: 'list', formula1: '"Yellow,Magenta,Cyan,Black"' },
    F3: { type: 'list', formula1: '"Yellow,Magenta,Cyan,Black"' },
    F4: { type: 'list', formula1: '"Yellow,Magenta,Cyan,Black"' }
  };
  
  // Add data validation for Tipo column (G)
  ws['!dataValidation'] = {
    ...ws['!dataValidation'],
    G2: { type: 'list', formula1: '"Original,Compativel,Remanufaturado"' },
    G3: { type: 'list', formula1: '"Original,Compativel,Remanufaturado"' },
    G4: { type: 'list', formula1: '"Original,Compativel,Remanufaturado"' }
  };
  
  XLSX.utils.book_append_sheet(wb, ws, "Template Toners");
  
  // Download file
  XLSX.writeFile(wb, 'template_toners.xlsx');
}

function importExcel() {
  console.log('Iniciando importação...');
  const fileInput = document.getElementById('excelFileInput');
  const file = fileInput.files[0];
  
  console.log('Arquivo selecionado:', file);
  
  if (!file) {
    alert('Por favor, selecione um arquivo Excel.');
    return;
  }
  
  // Show progress container and hide buttons
  document.getElementById('progressContainer').classList.remove('hidden');
  document.getElementById('importBtn').disabled = true;
  document.getElementById('cancelBtn').disabled = true;
  
  // Read Excel file
  const reader = new FileReader();
  reader.onload = function(e) {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
      const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
      
      if (jsonData.length <= 1) {
        throw new Error('Arquivo vazio ou sem dados');
      }
      
      // Process data with progress simulation
      processImportData(jsonData);
      
    } catch (error) {
      showImportError('Erro ao ler arquivo: ' + error.message);
    }
  };
  
  reader.onerror = function() {
    showImportError('Erro ao ler o arquivo');
  };
  
  reader.readAsArrayBuffer(file);
}

function processImportData(data) {
  const totalRows = data.length - 1; // Exclude header
  let currentRow = 0;
  
  // Simulate progress with actual data processing
  const processRow = () => {
    if (currentRow >= totalRows) {
      // All rows processed, send to server
      sendDataToServer(data);
      return;
    }
    
    currentRow++;
    const progress = Math.round((currentRow / totalRows) * 50); // First 50% for reading
    updateProgress(progress, `Processando linha ${currentRow} de ${totalRows}...`);
    
    setTimeout(processRow, 50); // Small delay for visual effect
  };
  
  processRow();
}

function sendDataToServer(data) {
  updateProgress(60, 'Enviando dados para o servidor...');
  
  const formData = new FormData();
  
  // Convert data to CSV format for server processing
  const csvContent = data.map(row => row.join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv' });
  formData.append('excel_file', blob, 'import.csv');
  
  fetch('/toners/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      // Simulate final progress steps
      updateProgress(80, 'Validando dados...');
      setTimeout(() => {
        updateProgress(90, 'Inserindo no banco de dados...');
        setTimeout(() => {
          updateProgress(100, `Concluído! ${result.imported} registros importados`);
          setTimeout(() => {
            closeImportModal();
            showSuccessMessage(result.message);
            location.reload(); // Refresh to show new data
          }, 1500);
        }, 500);
      }, 500);
    } else {
      showImportError(result.message);
    }
  })
  .catch(error => {
    showImportError('Erro de conexão: ' + error.message);
  });
}

function updateProgress(percentage, status) {
  document.getElementById('progressBar').style.width = percentage + '%';
  document.getElementById('progressText').textContent = percentage + '%';
  document.getElementById('importStatus').textContent = status;
}

function showImportError(message) {
  document.getElementById('progressContainer').classList.add('hidden');
  document.getElementById('importBtn').disabled = false;
  document.getElementById('cancelBtn').disabled = false;
  alert('Erro na importação: ' + message);
}

function showSuccessMessage(message) {
  // Create and show success notification
  const notification = document.createElement('div');
  notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
  notification.textContent = message;
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.remove();
  }, 5000);
}

function exportToExcel() {
  // Show loading state
  const button = event.target.closest('button');
  const originalContent = button.innerHTML;
  button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> <span>Exportando...</span>';
  button.disabled = true;
  
  // Create download link and trigger download (usando versão avançada com estatísticas)
  const link = document.createElement('a');
  link.href = '/toners/export';
  link.download = 'toners_relatorio_completo_' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  
  // Restore button after a short delay
  setTimeout(() => {
    button.innerHTML = originalContent;
    button.disabled = false;
    
    // Show success message
    showNotification('Planilha Excel exportada com sucesso!', 'success');
  }, 2500);
}

function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
  
  notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
  notification.innerHTML = `
    <div class="flex items-center space-x-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
      <span>${message}</span>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  // Animate in
  setTimeout(() => {
    notification.classList.remove('translate-x-full');
  }, 100);
  
  // Animate out and remove
  setTimeout(() => {
    notification.classList.add('translate-x-full');
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 4000);
}

// 🚨 FUNÇÃO DE EMERGÊNCIA - Use no Console se o X não funcionar
window.forceCloseModal = function() {
  console.log('🚨 EMERGÊNCIA: FECHANDO TODOS OS MODALS...');
  
  // Restaurar scroll
  document.body.style.overflow = '';
  
  // Remover TODOS os elementos com position fixed e z-index alto
  document.querySelectorAll('*').forEach(el => {
    const styles = window.getComputedStyle(el);
    const zIndex = parseInt(styles.zIndex);
    
    if (styles.position === 'fixed' && zIndex > 1000) {
      el.remove();
      console.log('🗑️ Removido elemento suspeito:', el.tagName, el.id, el.className);
    }
  });
  
  // Remover elementos por ID que contenham "modal"
  ['importModal', 'dynamicImportModal', 'fullScreenImportModal', 'modal'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.remove();
      console.log('🗑️ Removido por ID:', id);
    }
  });
  
  console.log('✅ EMERGÊNCIA CONCLUÍDA!');
};

// Instrução para o usuário
console.log('💡 DICA: Se o modal não fechar, digite no console: forceCloseModal()');

// ===== PAGINAÇÃO - NAVEGAÇÃO POR TECLADO =====
document.addEventListener('DOMContentLoaded', function() {
  // Navegação por teclado na paginação
  document.addEventListener('keydown', function(e) {
    // Verificar se não está em um input ou textarea
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
      return;
    }
    
    const currentPage = <?= $pagination['current_page'] ?? 1 ?>;
    const totalPages = <?= $pagination['total_pages'] ?? 1 ?>;
    
    // Seta esquerda ou 'P' para página anterior
    if ((e.key === 'ArrowLeft' || e.key.toLowerCase() === 'p') && currentPage > 1) {
      e.preventDefault();
      window.location.href = '?page=' + (currentPage - 1);
    }
    
    // Seta direita ou 'N' para próxima página
    if ((e.key === 'ArrowRight' || e.key.toLowerCase() === 'n') && currentPage < totalPages) {
      e.preventDefault();
      window.location.href = '?page=' + (currentPage + 1);
    }
    
    // Home para primeira página
    if (e.key === 'Home' && currentPage > 1) {
      e.preventDefault();
      window.location.href = '?page=1';
    }
    
    // End para última página
    if (e.key === 'End' && currentPage < totalPages) {
      e.preventDefault();
      window.location.href = '?page=' + totalPages;
    }
  });
  
  // Adicionar tooltips nos botões de paginação
  const paginationButtons = document.querySelectorAll('a[href*="page="]');
  paginationButtons.forEach(button => {
    const page = new URL(button.href).searchParams.get('page');
    button.title = `Ir para página ${page}`;
  });
  
  // Mostrar atalhos de teclado se houver mais de uma página
  if (<?= $pagination['total_pages'] ?? 1 ?> > 1) {
    console.log('⌨️ ATALHOS DE PAGINAÇÃO:');
    console.log('   ← ou P = Página anterior');
    console.log('   → ou N = Próxima página');
    console.log('   Home = Primeira página');
    console.log('   End = Última página');
  }
  
  // Função de busca por coluna específica
  window.searchToners = function() {
    const searchText = document.getElementById('searchToners').value.toLowerCase().trim();
    const searchColumn = document.getElementById('searchColumn').value;
    const tbody = document.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
      // Pular linhas vazias ou de mensagem
      if (row.cells.length < 2) {
        return;
      }
      
      // Se campo vazio, mostrar tudo
      if (!searchText) {
        row.style.removeProperty('display');
        visibleCount++;
        return;
      }
      
      let textToSearch = '';
      
      // Buscar na coluna específica ou em todas
      if (searchColumn === 'all') {
        // Buscar em todas as colunas
        textToSearch = row.textContent.toLowerCase();
      } else {
        // Buscar apenas na coluna selecionada
        const columnIndex = parseInt(searchColumn);
        textToSearch = row.cells[columnIndex]?.textContent?.toLowerCase().trim() || '';
      }
      
      // Verificar se o texto da busca está presente
      if (textToSearch.includes(searchText)) {
        row.style.removeProperty('display');
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });
    
    // Atualizar contador
    updateResultsCount(visibleCount, rows.length);
  };
  
  window.updateResultsCount = function(visibleCount, totalCount) {
    const paginationText = document.querySelector('.text-sm.text-gray-600');
    if (paginationText) {
      const pageInfo = paginationText.textContent.split('•')[1] || '';
      const resultText = visibleCount === totalCount 
        ? `${totalCount} toner(s) encontrado(s)` 
        : `${visibleCount} de ${totalCount} toner(s) encontrado(s)`;
      paginationText.textContent = `${resultText}${pageInfo ? ' • ' + pageInfo.trim() : ''}`;
    }
  };
  
});

// Função para ir diretamente para uma página específica
function goToPage(page) {
  const totalPages = <?= $pagination['total_pages'] ?? 1 ?>;
  if (page >= 1 && page <= totalPages) {
    window.location.href = '?page=' + page;
  }
}

// Função para mostrar loading durante navegação
function showPaginationLoading(element) {
  const originalText = element.textContent;
  element.textContent = 'Carregando...';
  element.style.opacity = '0.7';
  element.style.pointerEvents = 'none';
  
  // Restaurar após um tempo (fallback)
  setTimeout(() => {
    element.textContent = originalText;
    element.style.opacity = '1';
    element.style.pointerEvents = 'auto';
  }, 3000);
}

// Adicionar loading aos links de paginação
document.addEventListener('DOMContentLoaded', function() {
  const paginationLinks = document.querySelectorAll('a[href*="page="]');
  paginationLinks.forEach(link => {
    link.addEventListener('click', function() {
      showPaginationLoading(this);
    });
  });
});
</script>
