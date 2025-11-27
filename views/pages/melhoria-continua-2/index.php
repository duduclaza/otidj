<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = $_SESSION['user_role'] === 'admin';
$userId = $_SESSION['user_id'];

/**
 * Função para construir URL de paginação mantendo os filtros
 */
function construirUrlPaginacao($pagina) {
    $params = $_GET;
    $params['pagina'] = $pagina;
    return '/melhoria-continua-2?' . http_build_query($params);
}
?>

<section class="space-y-6">
  <!-- Header Padrão -->
  <div class="flex justify-between items-center">
    <div class="flex items-center gap-3">
      <h1 class="text-2xl font-semibold text-gray-900">🚀 Melhoria Contínua 2.0</h1>
      <span class="beta-badge">BETA</span>
    </div>
    <button onclick="openMelhoriaModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
      <span>+</span>
      Nova Melhoria
    </button>
  </div>

  <!-- Filtros -->
  <div class="bg-white border rounded-lg p-4">
    <form method="GET" action="/melhoria-continua-2" class="space-y-4">
      <!-- Primeira linha de filtros -->
      <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input type="text" name="search" value="<?= $_GET['search'] ?? '' ?>" placeholder="Título, descrição..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
          <select name="departamento_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Todos</option>
            <?php foreach ($departamentos as $dept): ?>
              <option value="<?= $dept['id'] ?>" <?= ($_GET['departamento_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                <?= e($dept['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Todos</option>
            <option value="pendente" <?= ($_GET['status'] ?? '') == 'pendente' ? 'selected' : '' ?>>⏳ Pendente</option>
            <option value="em_analise" <?= ($_GET['status'] ?? '') == 'em_analise' ? 'selected' : '' ?>>🔍 Em Análise</option>
            <option value="aprovado" <?= ($_GET['status'] ?? '') == 'aprovado' ? 'selected' : '' ?>>✅ Aprovado</option>
            <option value="reprovado" <?= ($_GET['status'] ?? '') == 'reprovado' ? 'selected' : '' ?>>❌ Reprovado</option>
            <option value="implementado" <?= ($_GET['status'] ?? '') == 'implementado' ? 'selected' : '' ?>>🚀 Implementado</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Idealizador</label>
          <input type="text" name="idealizador" value="<?= $_GET['idealizador'] ?? '' ?>" placeholder="Nome do idealizador..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Pontuação Mín.</label>
          <input type="number" name="pontuacao_min" value="<?= $_GET['pontuacao_min'] ?? '' ?>" placeholder="0" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Pontuação Máx.</label>
          <input type="number" name="pontuacao_max" value="<?= $_GET['pontuacao_max'] ?? '' ?>" placeholder="100" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>
      <!-- Segunda linha de filtros -->
      <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
          <input type="date" name="data_inicio" value="<?= $_GET['data_inicio'] ?? '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
          <input type="date" name="data_fim" value="<?= $_GET['data_fim'] ?? '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="md:col-span-4 flex items-end gap-2">
          <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors font-medium shadow-md whitespace-nowrap">
            🔍 Filtrar
          </button>
          <a href="/melhoria-continua-2" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition-colors font-medium shadow-md whitespace-nowrap text-center">
            🧹 Limpar
          </a>
          <button type="button" onclick="exportarExcel()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors font-medium shadow-md whitespace-nowrap">
            📊 Exportar
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Formulário Inline -->
  <div id="melhoriaFormContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-100">🚀 Nova Melhoria Contínua 2.0 <span class="beta-badge ml-2">BETA</span></h2>
      <button onclick="closeMelhoriaModal()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="melhoriaForm" action="/melhoria-continua-2/store" method="POST" class="space-y-6" enctype="multipart/form-data">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Data de Registro</label>
          <input type="text" value="<?= date('d/m/Y H:i') ?>" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 cursor-not-allowed" readonly>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Departamento *</label>
          <select name="departamento_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Selecione o departamento...</option>
            <?php foreach ($departamentos as $dept): ?>
              <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Título *</label>
        <input type="text" name="titulo" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Título da melhoria...">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Descrição da Melhoria *</label>
        <textarea name="descricao" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Descreva detalhadamente a melhoria proposta..."></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Resultado Esperado *</label>
        <textarea name="resultado_esperado" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Qual resultado você espera alcançar com esta melhoria?"></textarea>
      </div>
      
      <!-- 5W2H Compacto -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">O que será feito? *</label>
          <textarea name="o_que" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="O que será implementado..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Como será feito? *</label>
          <textarea name="como" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Como será executado..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Onde será feito? *</label>
          <textarea name="onde" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Local de aplicação..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Por que será feito? *</label>
          <textarea name="porque" required rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Justificativa..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quando será feito? *</label>
          <input type="date" name="quando" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quanto custa?</label>
          <input type="number" step="0.01" name="quanto_custa" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="R$ 0,00">
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Idealizador da Ideia *</label>
          <input type="text" name="idealizador" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nome do idealizador...">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Responsáveis</label>
          <select name="responsaveis[]" multiple class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" style="min-height: 100px;">
            <?php foreach ($usuarios as $usuario): ?>
              <option value="<?= $usuario['id'] ?>"><?= e($usuario['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-gray-400 mt-1">Segure Ctrl para selecionar múltiplos</p>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Observações</label>
        <textarea name="observacao" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Observações adicionais..."></textarea>
      </div>
      
      <!-- Anexos Existentes -->
      <div id="anexosExistentesContainer" class="hidden">
        <label class="block text-sm font-medium text-gray-200 mb-2">Anexos Atuais</label>
        <div id="anexosExistentesList" class="space-y-2 mb-4">
          <!-- Anexos carregados dinamicamente -->
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Adicionar Novos Anexos</label>
        <input type="file" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.ppt,.pptx" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200">
        <p class="text-xs text-gray-400 mt-1">Máximo 5 arquivos de 10MB cada. Formatos: JPG, PNG, GIF, PDF, PPT, PPTX</p>
      </div>
      
      <div class="flex justify-end space-x-3 pt-4">
        <button type="button" onclick="closeMelhoriaModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
          Cancelar
        </button>
        <button type="submit" id="submitButton" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          💾 Salvar Melhoria
        </button>
      </div>
    </form>
  </div>

  <!-- Controles de Paginação -->
  <?php if (isset($paginacao)): ?>
  <div class="bg-white border rounded-lg p-4">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
      <!-- Seletor de registros por página -->
      <div class="flex items-center gap-2">
        <label class="text-sm font-medium text-gray-700">Mostrar:</label>
        <select onchange="alterarPorPagina(this.value)" 
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
          <option value="10" <?= $paginacao['por_pagina'] == 10 ? 'selected' : '' ?>>10</option>
          <option value="50" <?= $paginacao['por_pagina'] == 50 ? 'selected' : '' ?>>50</option>
          <option value="100" <?= $paginacao['por_pagina'] == 100 ? 'selected' : '' ?>>100</option>
        </select>
        <span class="text-sm text-gray-600">registros por página</span>
      </div>

      <!-- Informação de registros -->
      <div class="text-sm text-gray-700">
        Mostrando 
        <span class="font-semibold"><?= $paginacao['offset'] + 1 ?></span>
        até 
        <span class="font-semibold"><?= min($paginacao['offset'] + $paginacao['por_pagina'], $paginacao['total_registros']) ?></span>
        de 
        <span class="font-semibold"><?= $paginacao['total_registros'] ?></span>
        registros
      </div>

      <!-- Navegação de páginas -->
      <?php if ($paginacao['total_paginas'] > 1): ?>
      <div class="flex items-center gap-1">
        <!-- Botão Primeira -->
        <?php if ($paginacao['pagina_atual'] > 1): ?>
          <a href="<?= construirUrlPaginacao(1) ?>" 
             class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            « Primeira
          </a>
        <?php endif; ?>

        <!-- Botão Anterior -->
        <?php if ($paginacao['pagina_atual'] > 1): ?>
          <a href="<?= construirUrlPaginacao($paginacao['pagina_atual'] - 1) ?>" 
             class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            ‹ Anterior
          </a>
        <?php endif; ?>

        <!-- Números de página -->
        <?php
        $inicio = max(1, $paginacao['pagina_atual'] - 2);
        $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + 2);
        
        for ($i = $inicio; $i <= $fim; $i++):
        ?>
          <a href="<?= construirUrlPaginacao($i) ?>" 
             class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors
                    <?= $i == $paginacao['pagina_atual'] 
                        ? 'bg-blue-600 text-white border-blue-600' 
                        : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <!-- Botão Próxima -->
        <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
          <a href="<?= construirUrlPaginacao($paginacao['pagina_atual'] + 1) ?>" 
             class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            Próxima ›
          </a>
        <?php endif; ?>

        <!-- Botão Última -->
        <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
          <a href="<?= construirUrlPaginacao($paginacao['total_paginas']) ?>" 
             class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            Última »
          </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Grid de Melhorias -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <!-- Barra de rolagem superior -->
    <div id="scrollTop" class="overflow-x-auto border-b" style="height: 20px;">
      <div id="scrollTopContent" style="height: 1px;"></div>
    </div>
    
    <!-- Tabela principal -->
    <div id="scrollBottom" class="overflow-x-auto">
      <table id="melhoriaTable" class="min-w-full text-sm" style="table-layout: fixed;">
        <thead class="bg-gray-50">
          <tr>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="data" style="width: 120px; min-width: 80px; position: relative;">
              Data
              <div class="resize-handle"></div>
            </th>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="departamento" style="width: 150px; min-width: 100px; position: relative;">
              Departamento
              <div class="resize-handle"></div>
            </th>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="titulo" style="width: 200px; min-width: 120px; position: relative;">
              Título
              <div class="resize-handle"></div>
            </th>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="descricao" style="width: 250px; min-width: 150px; position: relative;">
              Descrição
              <div class="resize-handle"></div>
            </th>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="resultado" style="width: 250px; min-width: 150px; position: relative;">
              Resultado Esperado
              <div class="resize-handle"></div>
            </th>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="status" style="width: 180px; min-width: 120px; position: relative;">
              Status
              <div class="resize-handle"></div>
            </th>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="idealizador" style="width: 150px; min-width: 100px; position: relative;">
              Idealizador
              <div class="resize-handle"></div>
            </th>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="criador" style="width: 150px; min-width: 100px; position: relative;">
              Criado por
              <div class="resize-handle"></div>
            </th>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="responsaveis" style="width: 180px; min-width: 120px; position: relative;">
              Responsáveis
              <div class="resize-handle"></div>
            </th>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="data_prevista" style="width: 130px; min-width: 100px; position: relative;">
              Data Prevista
              <div class="resize-handle"></div>
            </th>
            <?php if ($isAdmin): ?>
            <th class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="pontuacao" style="width: 120px; min-width: 80px; position: relative;">
              Pontuação
              <div class="resize-handle"></div>
            </th>
            <?php endif; ?>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 200px; min-width: 180px;">
              Ações
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($melhorias as $melhoria): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= date('d/m/Y', strtotime($melhoria['created_at'])) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= e($melhoria['departamento_nome'] ?? 'N/A') ?>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              <div class="font-medium"><?= e($melhoria['titulo']) ?></div>
            </td>
            <td class="px-4 py-4 text-sm text-gray-500 max-w-xs truncate">
              <?= !empty($melhoria['descricao']) ? e($melhoria['descricao']) : '-' ?>
            </td>
            <td class="px-4 py-4 text-sm text-gray-500 max-w-xs truncate">
              <?= !empty($melhoria['resultado_esperado']) ? e($melhoria['resultado_esperado']) : '-' ?>
            </td>
            <td class="px-4 py-4">
              <div>
                <?php if ($isAdmin): ?>
                  <select onchange="updateStatusInline(<?= $melhoria['id'] ?>, this.value)" class="status-badge status-<?= strtolower(str_replace(' ', '-', $melhoria['status'])) ?> border-0 cursor-pointer">
                    <option value="Pendente análise" <?= $melhoria['status'] === 'Pendente análise' ? 'selected' : '' ?>>Pendente análise</option>
                    <option value="Enviado para Aprovação" <?= $melhoria['status'] === 'Enviado para Aprovação' ? 'selected' : '' ?>>Enviado para Aprovação</option>
                    <option value="Em andamento" <?= $melhoria['status'] === 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                    <option value="Concluída" <?= $melhoria['status'] === 'Concluída' ? 'selected' : '' ?>>Concluída</option>
                    <option value="Recusada" <?= $melhoria['status'] === 'Recusada' ? 'selected' : '' ?>>Recusada</option>
                    <option value="Pendente Adaptação" <?= $melhoria['status'] === 'Pendente Adaptação' ? 'selected' : '' ?>>Pendente Adaptação</option>
                  </select>
                <?php else: ?>
                  <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $melhoria['status'])) ?>">
                    <?= e($melhoria['status']) ?>
                  </span>
                <?php endif; ?>
                
                <?php if ($melhoria['status'] === 'Recusada' && !empty($melhoria['observacao'])): ?>
                  <div class="mt-1 text-xs text-red-600 font-medium">
                    <span class="inline-block mr-1">❌</span>
                    <?= e(str_replace('RECUSADA: ', '', $melhoria['observacao'])) ?>
                  </div>
                <?php endif; ?>
              </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= e($melhoria['idealizador'] ?? '-') ?>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= e($melhoria['criador_nome']) ?>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= e($melhoria['responsaveis_nomes'] ?? '-') ?>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
              <?= $melhoria['quando'] ? date('d/m/Y', strtotime($melhoria['quando'])) : '-' ?>
            </td>
            <?php if ($isAdmin): ?>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <input type="number" min="0" max="3" step="1" value="<?= $melhoria['pontuacao'] ?? '' ?>" 
                     onchange="updatePontuacaoInline(<?= $melhoria['id'] ?>, this.value)"
                     class="w-16 border border-gray-300 rounded px-2 py-1 text-center"
                     placeholder="0-3">
            </td>
            <?php endif; ?>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button onclick="printMelhoria(<?= $melhoria['id'] ?>)" class="text-gray-600 hover:text-gray-900" title="Imprimir">
                🖨️ Imprimir
              </button>
              
              <button onclick="enviarEmailDetalhes(<?= $melhoria['id'] ?>)" class="text-purple-600 hover:text-purple-900" title="Enviar detalhes por email">
                📧 Email
              </button>
              
              <?php if ($isAdmin || ($melhoria['criado_por'] == $userId && $melhoria['status'] === 'Pendente Adaptação')): ?>
              <button onclick="editMelhoria(<?= $melhoria['id'] ?>)" class="text-green-600 hover:text-green-900" title="Editar">
                ✏️ Editar
              </button>
              <?php endif; ?>
              
              <?php if ($isAdmin || ($melhoria['criado_por'] == $userId && $melhoria['status'] === 'Recusada')): ?>
              <button onclick="deleteMelhoria(<?= $melhoria['id'] ?>)" class="text-red-600 hover:text-red-900" title="Excluir">
                🗑️ Excluir
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          
          <?php if (empty($melhorias)): ?>
          <tr>
            <td colspan="<?= $isAdmin ? 12 : 11 ?>" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-semibold mb-2">Nenhuma melhoria encontrada</p>
                <p class="text-sm">Tente ajustar os filtros ou crie uma nova melhoria</p>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Controles de Paginação (Rodapé) -->
  <?php if (isset($paginacao) && $paginacao['total_paginas'] > 1): ?>
  <div class="bg-white border rounded-lg p-4">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
      <!-- Informação de registros -->
      <div class="text-sm text-gray-700">
        Mostrando 
        <span class="font-semibold"><?= $paginacao['offset'] + 1 ?></span>
        até 
        <span class="font-semibold"><?= min($paginacao['offset'] + $paginacao['por_pagina'], $paginacao['total_registros']) ?></span>
        de 
        <span class="font-semibold"><?= $paginacao['total_registros'] ?></span>
        registros
      </div>

      <!-- Navegação de páginas -->
      <div class="flex items-center gap-1">
        <!-- Botão Primeira -->
        <?php if ($paginacao['pagina_atual'] > 1): ?>
          <a href="<?= construirUrlPaginacao(1) ?>" 
             class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            « Primeira
          </a>
        <?php endif; ?>

        <!-- Botão Anterior -->
        <?php if ($paginacao['pagina_atual'] > 1): ?>
          <a href="<?= construirUrlPaginacao($paginacao['pagina_atual'] - 1) ?>" 
             class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            ‹ Anterior
          </a>
        <?php endif; ?>

        <!-- Números de página -->
        <?php
        $inicio = max(1, $paginacao['pagina_atual'] - 2);
        $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + 2);
        
        for ($i = $inicio; $i <= $fim; $i++):
        ?>
          <a href="<?= construirUrlPaginacao($i) ?>" 
             class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors
                    <?= $i == $paginacao['pagina_atual'] 
                        ? 'bg-blue-600 text-white border-blue-600' 
                        : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <!-- Botão Próxima -->
        <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
          <a href="<?= construirUrlPaginacao($paginacao['pagina_atual'] + 1) ?>" 
             class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            Próxima ›
          </a>
        <?php endif; ?>

        <!-- Botão Última -->
        <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
          <a href="<?= construirUrlPaginacao($paginacao['total_paginas']) ?>" 
             class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            Última »
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</section>

<!-- Modal de Motivo de Recusa -->
<div id="modalMotivoRecusa" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-900">❌ Motivo da Recusa</h3>
      <button onclick="fecharModalRecusa()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <p class="text-sm text-gray-600 mb-4">Informe o motivo da recusa desta melhoria:</p>
    <textarea id="motivoRecusaTexto" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none" placeholder="Digite o motivo da recusa..."></textarea>
    <div class="flex gap-2 mt-4">
      <button onclick="confirmarRecusa()" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors font-medium">
        Confirmar Recusa
      </button>
      <button onclick="fecharModalRecusa()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition-colors font-medium">
        Cancelar
      </button>
    </div>
  </div>
</div>

<style>

/* Colunas Redimensionáveis */
.resizable-column {
  position: relative;
  overflow: hidden;
  text-overflow: ellipsis;
}

.resize-handle {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  width: 10px;
  cursor: col-resize;
  user-select: none;
  z-index: 10;
}

.resize-handle:hover {
  background: rgba(59, 130, 246, 0.1);
  border-right: 2px solid #3b82f6;
}

.resize-handle:active {
  background: rgba(59, 130, 246, 0.2);
  border-right: 2px solid #2563eb;
}

.resizing {
  cursor: col-resize !important;
  user-select: none !important;
}

.resizing * {
  cursor: col-resize !important;
  user-select: none !important;
}

/* Badge BETA */
.beta-badge {
  background: linear-gradient(45deg, #ff6b6b, #feca57);
  color: white;
  font-size: 0.7rem;
  font-weight: bold;
  padding: 4px 8px;
  border-radius: 12px;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}

/* Status badges */
.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pendente-análise { 
  background: #fef3c7; 
  color: #92400e; 
}

.status-enviado-para-aprovação { 
  background: #e0e7ff; 
  color: #3730a3; 
}

.status-em-andamento { 
  background: #dbeafe; 
  color: #1e40af; 
}

.status-concluída { 
  background: #d1fae5; 
  color: #065f46; 
}

.status-recusada { 
  background: #fee2e2; 
  color: #991b1b; 
}

.status-pendente-adaptação { 
  background: #f3e8ff; 
  color: #7c3aed; 
}
</style>

<script>
// Alterar quantidade de registros por página
function alterarPorPagina(porPagina) {
  const urlParams = new URLSearchParams(window.location.search);
  urlParams.set('por_pagina', porPagina);
  urlParams.set('pagina', '1'); // Resetar para primeira página
  window.location.href = '/melhoria-continua-2?' + urlParams.toString();
}

// Sistema de Redimensionamento de Colunas
(function() {
  const STORAGE_KEY = 'melhoria_continua_column_widths';
  
  let isResizing = false;
  let currentColumn = null;
  let startX = 0;
  let startWidth = 0;
  
  // Carregar larguras salvas
  function loadColumnWidths() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved) {
        const widths = JSON.parse(saved);
        Object.keys(widths).forEach(columnName => {
          const th = document.querySelector(`th[data-column="${columnName}"]`);
          if (th) {
            th.style.width = widths[columnName] + 'px';
          }
        });
      }
    } catch (e) {
      console.error('Erro ao carregar larguras das colunas:', e);
    }
  }
  
  // Salvar larguras
  function saveColumnWidths() {
    try {
      const columns = document.querySelectorAll('.resizable-column');
      const widths = {};
      columns.forEach(col => {
        const columnName = col.getAttribute('data-column');
        if (columnName) {
          widths[columnName] = col.offsetWidth;
        }
      });
      localStorage.setItem(STORAGE_KEY, JSON.stringify(widths));
      console.log('Larguras salvas:', widths);
    } catch (e) {
      console.error('Erro ao salvar larguras das colunas:', e);
    }
  }
  
  // Iniciar redimensionamento
  function startResize(e) {
    // Encontrar o handle clicado
    let handle = e.target;
    if (!handle.classList.contains('resize-handle')) return;
    
    // Pegar a coluna pai do handle
    currentColumn = handle.closest('th.resizable-column');
    if (!currentColumn) return;
    
    isResizing = true;
    startX = e.pageX;
    startWidth = currentColumn.offsetWidth;
    
    document.body.classList.add('resizing');
    document.body.style.cursor = 'col-resize';
    
    console.log('Iniciando resize da coluna:', currentColumn.getAttribute('data-column'), 'Largura atual:', startWidth);
    
    e.preventDefault();
    e.stopPropagation();
  }
  
  // Durante o redimensionamento
  function doResize(e) {
    if (!isResizing || !currentColumn) return;
    
    const diff = e.pageX - startX;
    let newWidth = startWidth + diff;
    
    // Pegar largura mínima do estilo inline
    const minWidthStr = currentColumn.style.minWidth;
    const minWidth = minWidthStr ? parseInt(minWidthStr) : 80;
    
    // Garantir que não fique menor que o mínimo
    if (newWidth < minWidth) {
      newWidth = minWidth;
    }
    
    // Aplicar nova largura
    currentColumn.style.width = newWidth + 'px';
    
    // Atualizar scroll
    updateScrollBarWidth();
    
    console.log('Redimensionando:', newWidth + 'px');
    
    e.preventDefault();
  }
  
  // Finalizar redimensionamento
  function stopResize(e) {
    if (!isResizing) return;
    
    console.log('Finalizando resize');
    
    isResizing = false;
    document.body.classList.remove('resizing');
    document.body.style.cursor = '';
    
    if (currentColumn) {
      saveColumnWidths();
      currentColumn = null;
    }
  }
  
  // Atualizar largura da barra de scroll superior
  function updateScrollBarWidth() {
    const table = document.getElementById('melhoriaTable');
    const scrollTopContent = document.getElementById('scrollTopContent');
    if (table && scrollTopContent) {
      scrollTopContent.style.width = table.offsetWidth + 'px';
    }
  }
  
  // Sincronizar scroll horizontal
  function syncScroll() {
    const scrollTop = document.getElementById('scrollTop');
    const scrollBottom = document.getElementById('scrollBottom');
    
    if (scrollTop && scrollBottom) {
      scrollTop.addEventListener('scroll', function() {
        scrollBottom.scrollLeft = this.scrollLeft;
      });
      
      scrollBottom.addEventListener('scroll', function() {
        scrollTop.scrollLeft = this.scrollLeft;
      });
    }
  }
  
  // Inicializar quando o DOM estiver pronto
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando sistema de redimensionamento de colunas');
    
    // Carregar larguras salvas
    loadColumnWidths();
    
    // Atualizar largura do scroll
    setTimeout(updateScrollBarWidth, 100);
    
    // Sincronizar scroll
    syncScroll();
    
    // Adicionar event listeners
    document.addEventListener('mousedown', startResize);
    document.addEventListener('mousemove', doResize);
    document.addEventListener('mouseup', stopResize);
    
    console.log('Sistema de redimensionamento pronto');
  });
  
  // Atualizar após resize da janela
  window.addEventListener('resize', updateScrollBarWidth);
})();

// Funções do Formulário Inline
function openMelhoriaModal() {
  const formContainer = document.getElementById('melhoriaFormContainer');
  if (formContainer) {
    // Limpar formulário para nova melhoria
    limparFormulario();
    
    formContainer.classList.remove('hidden');
    // Scroll suave até o formulário
    formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

function closeMelhoriaModal() {
  const formContainer = document.getElementById('melhoriaFormContainer');
  if (formContainer) {
    formContainer.classList.add('hidden');
    limparFormulario();
  }
}

function limparFormulario() {
  const form = document.getElementById('melhoriaForm');
  form.reset();
  form.action = '/melhoria-continua-2/store';
  
  // Remover campos hidden de edição
  const hiddenId = document.querySelector('[name="id"]');
  if (hiddenId) hiddenId.remove();
  
  const anexosField = document.querySelector('[name="anexos_atuais"]');
  if (anexosField) anexosField.remove();
  
  // Esconder anexos existentes
  document.getElementById('anexosExistentesContainer').classList.add('hidden');
  document.getElementById('anexosExistentesList').innerHTML = '';
  
  // Mudar botão para "Salvar"
  const submitButton = document.getElementById('submitButton');
  submitButton.innerHTML = '💾 Salvar Melhoria';
  submitButton.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors';
}

// Configurar eventos
document.addEventListener('DOMContentLoaded', function() {
  // Pressionar ESC para fechar formulário
  document.addEventListener('keydown', function(e) {
    const formContainer = document.getElementById('melhoriaFormContainer');
    if (e.key === 'Escape' && formContainer && !formContainer.classList.contains('hidden')) {
      closeMelhoriaModal();
    }
  });
  
  // Sincronizar barras de rolagem (topo e tabela)
  const scrollTop = document.getElementById('scrollTop');
  const scrollBottom = document.getElementById('scrollBottom');
  const scrollTopContent = document.getElementById('scrollTopContent');
  const table = document.querySelector('#scrollBottom table');
  
  if (scrollTop && scrollBottom && scrollTopContent && table) {
    // Ajustar largura do conteúdo da barra superior para corresponder à largura da tabela
    function adjustScrollTopWidth() {
      scrollTopContent.style.width = table.offsetWidth + 'px';
    }
    
    // Ajustar ao carregar e ao redimensionar
    adjustScrollTopWidth();
    window.addEventListener('resize', adjustScrollTopWidth);
    
    // Sincronizar scroll de cima para baixo
    scrollTop.addEventListener('scroll', function() {
      scrollBottom.scrollLeft = scrollTop.scrollLeft;
    });
    
    // Sincronizar scroll de baixo para cima
    scrollBottom.addEventListener('scroll', function() {
      scrollTop.scrollLeft = scrollBottom.scrollLeft;
    });
  }
});

// Submit do formulário
document.getElementById('melhoriaForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const actionUrl = this.action; // Usa o action do formulário (store ou update)
  
  console.log('Enviando para:', actionUrl);
  
  try {
    const response = await fetch(actionUrl, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      if (result.redirect) {
        window.location.href = result.redirect;
      }
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro ao enviar formulário');
  }
});

// Variáveis globais para o modal de recusa
let melhoriaIdRecusa = null;

// Atualizar Status Inline (Admin)
async function updateStatusInline(id, status) {
  // Se o status for "Recusada", abrir modal para pedir motivo
  if (status === 'Recusada') {
    melhoriaIdRecusa = id;
    document.getElementById('modalMotivoRecusa').classList.remove('hidden');
    document.getElementById('motivoRecusaTexto').value = '';
    document.getElementById('motivoRecusaTexto').focus();
    return;
  }
  
  // Para outros status, atualizar normalmente
  try {
    console.log('=== ATUALIZANDO STATUS ===');
    console.log('ID:', id);
    console.log('Novo Status:', status);
    
    const response = await fetch(`/melhoria-continua-2/${id}/update-status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status })
    });
    
    console.log('Response Status:', response.status);
    console.log('Response OK:', response.ok);
    
    const data = await response.json();
    console.log('Response Data:', data);
    
    if (data.success) {
      alert('✅ Status atualizado com sucesso!' + (status === 'Concluída' ? '\n📧 Email será enviado aos responsáveis.' : ''));
      location.reload();
    } else {
      alert('❌ Erro: ' + data.message);
    }
  } catch (error) {
    console.error('Erro completo:', error);
    alert('❌ Erro ao atualizar status: ' + error.message);
  }
}

// Fechar modal de recusa
function fecharModalRecusa() {
  document.getElementById('modalMotivoRecusa').classList.add('hidden');
  document.getElementById('motivoRecusaTexto').value = '';
  melhoriaIdRecusa = null;
}

// Confirmar recusa com motivo
async function confirmarRecusa() {
  const motivo = document.getElementById('motivoRecusaTexto').value.trim();
  
  if (!motivo) {
    alert('❌ Por favor, informe o motivo da recusa');
    return;
  }
  
  try {
    const response = await fetch(`/melhoria-continua-2/${melhoriaIdRecusa}/update-status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        status: 'Recusada',
        motivo_recusa: motivo
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert('✅ Melhoria recusada com sucesso!\n📧 Email com o motivo será enviado aos envolvidos.');
      fecharModalRecusa();
      location.reload();
    } else {
      alert('❌ Erro: ' + data.message);
    }
  } catch (error) {
    console.error('Erro ao recusar:', error);
    alert('❌ Erro ao recusar melhoria: ' + error.message);
  }
}

// Atualizar Pontuação Inline (Admin)
async function updatePontuacaoInline(id, pontuacao) {
  if (pontuacao < 0 || pontuacao > 3) {
    alert('❌ Pontuação deve estar entre 0 e 3');
    return;
  }
  
  try {
    const response = await fetch(`/melhoria-continua-2/${id}/update-pontuacao`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pontuacao })
    });
    
    const data = await response.json();
    if (data.success) {
      alert('✅ Pontuação atualizada com sucesso!');
    } else {
      alert('❌ Erro: ' + data.message);
    }
  } catch (error) {
    alert('❌ Erro ao atualizar pontuação');
  }
}

// Gerar HTML dos Detalhes
function generateDetailHTML(m) {
  return `
    <div class="space-y-6">
      <div class="grid grid-cols-2 gap-4">
        <div><strong>📅 Data:</strong> ${m.created_at}</div>
        <div><strong>🏢 Departamento:</strong> ${m.departamento_nome || 'N/A'}</div>
        <div><strong>👤 Criado por:</strong> ${m.criador_nome}</div>
        <div><strong>📊 Status:</strong> <span class="status-badge status-${m.status.toLowerCase().replace(/ /g, '-')}">${m.status}</span></div>
        ${m.pontuacao ? `<div><strong>⭐ Pontuação:</strong> ${m.pontuacao}/3</div>` : ''}
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">📝 Título</h3>
        <p>${m.titulo}</p>
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">📄 Descrição</h3>
        <p>${m.resultado_esperado}</p>
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">🎯 Metodologia 5W2H</h3>
        <div class="grid grid-cols-2 gap-4">
          <div><strong>O quê:</strong> ${m.o_que}</div>
          <div><strong>Como:</strong> ${m.como}</div>
          <div><strong>Onde:</strong> ${m.onde}</div>
          <div><strong>Por quê:</strong> ${m.porque}</div>
          <div><strong>Quando:</strong> ${m.quando}</div>
          <div><strong>Quanto:</strong> ${m.quanto_custa ? 'R$ ' + m.quanto_custa : 'N/A'}</div>
        </div>
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">👥 Responsáveis</h3>
        <p>${m.responsaveis_nomes || 'Nenhum'}</p>
      </div>
      
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">💡 Idealizador</h3>
        <p>${m.idealizador}</p>
      </div>
      
      ${m.observacao ? `
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">📌 Observações</h3>
        <p>${m.observacao}</p>
      </div>
      ` : ''}
      
      ${m.anexos && m.anexos.length > 0 ? `
      <div class="border-t pt-4">
        <h3 class="font-bold text-lg mb-2">📎 Anexos (${m.anexos.length})</h3>
        <div class="space-y-2">
          ${m.anexos.map(a => `<div><a href="${a.url}" target="_blank" class="text-blue-600 hover:underline">📄 ${a.nome}</a></div>`).join('')}
        </div>
      </div>
      ` : ''}
    </div>
  `;
}

// Imprimir Melhoria - Abre em nova aba para salvar como PDF
async function printMelhoria(id) {
  let loadingMsg;
  try {
    // Mostrar loading
    loadingMsg = document.createElement('div');
    loadingMsg.innerHTML = '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);z-index:99999;"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 text-gray-600">Gerando documento...</p></div>';
    document.body.appendChild(loadingMsg);
    
    console.log('Gerando impressão para ID:', id);
    const response = await fetch(`/melhoria-continua-2/${id}/details`);
    console.log('Response status:', response.status);
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status} - ${response.statusText}`);
    }
    
    const data = await response.json();
    console.log('Dados recebidos:', data);
    
    if (loadingMsg && loadingMsg.parentNode) {
      document.body.removeChild(loadingMsg);
    }
    
    if (data.success && data.melhoria) {
      const printWindow = window.open('', '_blank', 'width=1200,height=800');
      
      if (!printWindow) {
        alert('❌ Pop-up bloqueado! Por favor, permita pop-ups para este site.');
        return;
      }
      
      const htmlContent = generatePrintHTML(data.melhoria);
      printWindow.document.write(htmlContent);
      printWindow.document.close();
      
      // Aguardar carregamento de imagens antes de imprimir
      printWindow.onload = function() {
        setTimeout(() => {
          printWindow.focus();
        }, 500);
      };
    } else {
      alert(`❌ Erro: ${data.message || 'Dados não encontrados'}`);
    }
  } catch (error) {
    console.error('Erro ao gerar impressão:', error);
    if (loadingMsg && loadingMsg.parentNode) {
      document.body.removeChild(loadingMsg);
    }
    alert(`❌ Erro ao gerar impressão: ${error.message}`);
  }
}

// Gerar HTML para Impressão
function generatePrintHTML(m) {
  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Melhoria Contínua - ${m.titulo}</title>
      <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
          padding: 40px; 
          line-height: 1.6;
          color: #333;
        }
        .header { 
          text-align: center; 
          border-bottom: 4px solid #2563eb; 
          padding-bottom: 20px; 
          margin-bottom: 30px; 
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          color: white;
          padding: 30px;
          border-radius: 10px 10px 0 0;
        }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { font-size: 16px; opacity: 0.9; }
        .section { 
          margin-bottom: 30px; 
          page-break-inside: avoid; 
          border: 1px solid #e5e7eb;
          border-radius: 8px;
          overflow: hidden;
        }
        .section-title { 
          background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
          color: white; 
          padding: 12px 15px; 
          font-weight: bold; 
          font-size: 16px;
          letter-spacing: 0.5px;
        }
        .section-content { padding: 20px; }
        .grid { 
          display: grid; 
          grid-template-columns: 1fr 1fr; 
          gap: 20px; 
          padding: 20px;
        }
        .field { 
          margin-bottom: 15px; 
          padding: 15px;
          background: #f9fafb;
          border-radius: 6px;
          border-left: 4px solid #2563eb;
        }
        .field strong { 
          display: block; 
          color: #1e40af; 
          margin-bottom: 8px; 
          font-size: 14px;
          font-weight: 600;
        }
        .field-value {
          color: #374151;
          font-size: 15px;
          line-height: 1.8;
          white-space: pre-wrap;
        }
        @media print { 
          .no-print { display: none; }
          body { padding: 20px; }
          .section { page-break-inside: avoid; }
          img { max-width: 100%; height: auto; }
        }
      </style>
    </head>
    <body>
      <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 1000; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-right: 10px;">
          🖨️ Imprimir / Salvar PDF
        </button>
        <button onclick="window.close()" style="background: #6b7280; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">
          ✖️ Fechar
        </button>
      </div>
      
      <div class="header">
        <h1>🚀 MELHORIA CONTÍNUA 2.0</h1>
        <p>Sistema de Gestão da Qualidade - OTI DJ</p>
        <p style="margin-top: 10px; font-size: 14px;">Melhoria #${m.id} - ${m.titulo}</p>
      </div>
      
      <div class="section">
        <div class="section-title">📋 INFORMAÇÕES GERAIS</div>
        <div class="grid">
          <div class="field"><strong>Data:</strong> ${m.created_at}</div>
          <div class="field"><strong>Departamento:</strong> ${m.departamento_nome || 'N/A'}</div>
          <div class="field"><strong>Criado por:</strong> ${m.criador_nome}</div>
          <div class="field"><strong>Status:</strong> ${m.status}</div>
          ${m.pontuacao ? `<div class="field"><strong>Pontuação:</strong> ${m.pontuacao}/3 ⭐</div>` : ''}
        </div>
      </div>
      
      <div class="section">
        <div class="section-title">📝 TÍTULO</div>
        <div class="section-content">
          <div class="field"><strong>Título da Melhoria:</strong><div class="field-value">${m.titulo}</div></div>
        </div>
      </div>
      
      <div class="section">
        <div class="section-title">📄 DESCRIÇÃO DA MELHORIA</div>
        <div class="section-content">
          <div class="field"><div class="field-value">${m.descricao || 'Não informada'}</div></div>
        </div>
      </div>
      
      <div class="section">
        <div class="section-title">🎯 RESULTADO ESPERADO</div>
        <div class="section-content">
          <div class="field"><div class="field-value">${m.resultado_esperado || 'Não informado'}</div></div>
        </div>
      </div>
      
      <div class="section">
        <div class="section-title">📊 METODOLOGIA 5W2H</div>
        <div class="section-content">
          <div class="field">
            <strong>❓ O QUE será feito?</strong>
            <div class="field-value">${m.o_que || 'Não informado'}</div>
          </div>
          <div class="field">
            <strong>🔧 COMO será feito?</strong>
            <div class="field-value">${m.como || 'Não informado'}</div>
          </div>
          <div class="field">
            <strong>📍 ONDE será feito?</strong>
            <div class="field-value">${m.onde || 'Não informado'}</div>
          </div>
          <div class="field">
            <strong>💡 POR QUE será feito?</strong>
            <div class="field-value">${m.porque || 'Não informado'}</div>
          </div>
          <div class="field">
            <strong>📅 QUANDO será feito?</strong>
            <div class="field-value">${m.quando ? new Date(m.quando).toLocaleDateString('pt-BR') : 'Não informado'}</div>
          </div>
          <div class="field">
            <strong>💰 QUANTO custa?</strong>
            <div class="field-value">${m.quanto_custa ? 'R$ ' + parseFloat(m.quanto_custa).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'Não informado'}</div>
          </div>
        </div>
      </div>
      
      <div class="section">
        <div class="section-title">👤 IDEALIZADOR DA IDEIA</div>
        <div class="section-content">
          <div class="field"><strong>Nome do Idealizador:</strong><div class="field-value">${m.idealizador || 'Não informado'}</div></div>
        </div>
      </div>
      
      <div class="section">
        <div class="section-title">👥 RESPONSÁVEIS PELA IMPLEMENTAÇÃO</div>
        <div class="section-content">
          <div class="field"><div class="field-value">${m.responsaveis_nomes || 'Nenhum responsável atribuído'}</div></div>
        </div>
      </div>
      
      ${m.observacao ? `
      <div class="section">
        <div class="section-title">📌 OBSERVAÇÕES ADICIONAIS</div>
        <div class="section-content">
          <div class="field"><div class="field-value">${m.observacao}</div></div>
        </div>
      </div>
      ` : ''}
      
      ${m.anexos && m.anexos.length > 0 ? `
      <div class="section">
        <div class="section-title">📎 ANEXOS (${m.anexos.length})</div>
        ${m.anexos.map((a, i) => {
          const isImage = a.nome.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/i);
          const isPdf = a.nome.match(/\.pdf$/i);
          return `
            <div class="anexo-item" style="margin-bottom: 30px; page-break-before: ${i > 0 ? 'always' : 'auto'}; page-break-inside: avoid;">
              <h3 style="color: #2563eb; margin-bottom: 15px; padding: 10px; background: #eff6ff; border-left: 4px solid #2563eb;">
                📎 Anexo ${i + 1}: ${a.nome}
              </h3>
              ${isImage ? `
                <div style="text-align: center; padding: 20px; border: 2px solid #e5e7eb; border-radius: 8px; background: white;">
                  <img src="${a.url}" alt="${a.nome}" style="max-width: 100%; max-height: 700px; object-fit: contain; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                </div>
              ` : isPdf ? `
                <div style="padding: 30px; background: #f9fafb; border: 2px dashed #2563eb; border-radius: 8px; text-align: center;">
                  <div style="font-size: 48px; margin-bottom: 15px;">📄</div>
                  <p style="font-size: 20px; font-weight: bold; color: #1e40af; margin-bottom: 10px;">Documento PDF Anexado</p>
                  <p style="color: #666; margin-bottom: 15px;">Este arquivo PDF está incluído nesta impressão.</p>
                  <p style="background: white; padding: 10px; border-radius: 6px; display: inline-block;">
                    <strong>Arquivo:</strong> ${a.nome}
                  </p>
                  <div style="margin-top: 20px; padding: 15px; background: #dbeafe; border-radius: 6px;">
                    <p style="font-size: 14px; color: #1e40af; margin: 0;">
                      💡 <strong>Dica:</strong> Para incluir o conteúdo deste PDF na impressão final, 
                      abra o arquivo separadamente e imprima junto com este documento.
                    </p>
                  </div>
                  <div style="margin-top: 15px;">
                    <a href="${a.url}" target="_blank" style="display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                      🔗 Abrir PDF em Nova Aba
                    </a>
                  </div>
                  <iframe src="${a.url}" style="width: 100%; height: 800px; border: 2px solid #e5e7eb; border-radius: 8px; margin-top: 20px; display: block;"></iframe>
                </div>
              ` : `
                <div style="padding: 20px; background: #f3f4f6; border-radius: 8px;">
                  <p><strong>Tipo:</strong> Arquivo anexo</p>
                  <p><strong>Nome:</strong> ${a.nome}</p>
                </div>
              `}
            </div>
          `;
        }).join('')}
      </div>
      ` : ''}
      
      <div style="margin-top: 50px; text-align: center; color: #666; font-size: 12px;">
        <p>Documento gerado em ${new Date().toLocaleString('pt-BR')}</p>
        <p>Sistema SGQ OTI DJ - Melhoria Contínua 2.0</p>
      </div>
    </body>
    </html>
  `;
}

// Editar Melhoria
async function editMelhoria(id) {
  try {
    console.log('Carregando melhoria para edição, ID:', id);
    const response = await fetch(`/melhoria-continua-2/${id}/details`);
    const data = await response.json();
    
    console.log('Dados recebidos:', data);
    
    if (data.success && data.melhoria) {
      const m = data.melhoria;
      console.log('Melhoria:', m);
      
      // Preencher formulário
      const form = document.getElementById('melhoriaForm');
      
      form.querySelector('[name="departamento_id"]').value = m.departamento_id || '';
      form.querySelector('[name="titulo"]').value = m.titulo || '';
      form.querySelector('[name="descricao"]').value = m.descricao || '';
      form.querySelector('[name="resultado_esperado"]').value = m.resultado_esperado || '';
      form.querySelector('[name="o_que"]').value = m.o_que || '';
      form.querySelector('[name="como"]').value = m.como || '';
      form.querySelector('[name="onde"]').value = m.onde || '';
      form.querySelector('[name="porque"]').value = m.porque || '';
      form.querySelector('[name="quando"]').value = m.quando || '';
      form.querySelector('[name="quanto_custa"]').value = m.quanto_custa || '';
      form.querySelector('[name="idealizador"]').value = m.idealizador || '';
      form.querySelector('[name="observacao"]').value = m.observacao || '';
      
      console.log('Formulário preenchido');
      
      // Selecionar responsáveis
      if (m.responsaveis) {
        const responsaveisIds = m.responsaveis.split(',');
        const select = document.querySelector('[name="responsaveis[]"]');
        Array.from(select.options).forEach(option => {
          option.selected = responsaveisIds.includes(option.value);
        });
      }
      
      // Mostrar anexos existentes
      const anexosContainer = document.getElementById('anexosExistentesContainer');
      const anexosList = document.getElementById('anexosExistentesList');
      
      if (m.anexos && m.anexos.length > 0) {
        anexosContainer.classList.remove('hidden');
        anexosList.innerHTML = m.anexos.map((anexo, index) => {
          const isImage = anexo.tipo && anexo.tipo.includes('image');
          const isPdf = anexo.nome && anexo.nome.toLowerCase().endsWith('.pdf');
          
          return `
          <div class="bg-gray-700 p-4 rounded-lg border-2 border-gray-600" id="anexo-${index}">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center space-x-3">
                <span class="text-2xl">${isImage ? '🖼️' : isPdf ? '📄' : '📎'}</span>
                <div>
                  <p class="text-gray-200 font-medium">${anexo.nome}</p>
                  <p class="text-xs text-gray-400">${(anexo.tamanho / 1024).toFixed(1)} KB</p>
                </div>
              </div>
              <button type="button" onclick="removerAnexo(${index}, '${anexo.arquivo}')" 
                      class="text-red-400 hover:text-red-600 px-3 py-2 rounded-lg bg-red-900/20 hover:bg-red-900/40 transition-colors">
                🗑️ Remover
              </button>
            </div>
            ${isImage ? `
              <div class="mt-3 border-2 border-gray-600 rounded-lg overflow-hidden">
                <img src="${anexo.url}" alt="${anexo.nome}" 
                     class="w-full h-48 object-cover cursor-pointer hover:opacity-90 transition-opacity"
                     onclick="window.open('${anexo.url}', '_blank')">
              </div>
              <p class="text-xs text-gray-400 mt-2 text-center">Clique na imagem para ampliar</p>
            ` : isPdf ? `
              <div class="mt-3 bg-gray-800 p-4 rounded-lg text-center">
                <a href="${anexo.url}" target="_blank" 
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                  📄 Abrir PDF
                </a>
              </div>
            ` : ''}
          </div>
        `;
        }).join('');
        
        // Guardar anexos atuais em campo hidden
        let anexosField = document.querySelector('[name="anexos_atuais"]');
        if (!anexosField) {
          anexosField = document.createElement('input');
          anexosField.type = 'hidden';
          anexosField.name = 'anexos_atuais';
          document.getElementById('melhoriaForm').appendChild(anexosField);
        }
        anexosField.value = JSON.stringify(m.anexos);
      } else {
        anexosContainer.classList.add('hidden');
      }
      
      // Adicionar campo hidden com ID para update
      let hiddenId = document.querySelector('[name="id"]');
      if (!hiddenId) {
        hiddenId = document.createElement('input');
        hiddenId.type = 'hidden';
        hiddenId.name = 'id';
        document.getElementById('melhoriaForm').appendChild(hiddenId);
      }
      hiddenId.value = id;
      
      // Alterar action do formulário para update
      form.action = '/melhoria-continua-2/update';
      console.log('Action alterada para:', form.action);
      
      // Mudar botão para "Atualizar"
      const submitButton = document.getElementById('submitButton');
      submitButton.innerHTML = '🔄 Atualizar Melhoria';
      submitButton.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors';
      
      // Abrir formulário (sem limpar!)
      const formContainer = document.getElementById('melhoriaFormContainer');
      formContainer.classList.remove('hidden');
      console.log('Formulário aberto');
      
      // Scroll até o topo do formulário
      formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
      
    } else {
      console.error('Dados inválidos:', data);
      alert('❌ Erro: Dados da melhoria não encontrados');
    }
  } catch (error) {
    console.error('Erro ao carregar dados:', error);
    alert('❌ Erro ao carregar dados para edição: ' + error.message);
  }
}

// Remover anexo da lista
function removerAnexo(index, arquivo) {
  if (!confirm('Tem certeza que deseja remover este anexo?')) {
    return;
  }
  
  // Remover da interface
  const elemento = document.getElementById(`anexo-${index}`);
  if (elemento) {
    elemento.remove();
  }
  
  // Atualizar campo hidden
  const anexosField = document.querySelector('[name="anexos_atuais"]');
  if (anexosField) {
    const anexos = JSON.parse(anexosField.value);
    anexos.splice(index, 1);
    anexosField.value = JSON.stringify(anexos);
    
    // Se não há mais anexos, esconder container
    if (anexos.length === 0) {
      document.getElementById('anexosExistentesContainer').classList.add('hidden');
    }
  }
}

// Excluir Melhoria
async function deleteMelhoria(id) {
  if (!confirm('⚠️ Tem certeza que deseja excluir esta melhoria?\n\nEsta ação não pode ser desfeita!')) {
    return;
  }
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('/melhoria-continua-2/delete', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert('✅ Melhoria excluída com sucesso!');
      location.reload();
    } else {
      alert('❌ Erro: ' + data.message);
    }
  } catch (error) {
    alert('❌ Erro ao excluir melhoria');
  }
}

// Exportar para Excel
function exportarExcel() {
  const params = new URLSearchParams(window.location.search);
  const url = `/melhoria-continua-2/export?${params.toString()}`;
  window.location.href = url;
}

// Enviar detalhes por email
async function enviarEmailDetalhes(id) {
  if (!confirm('📧 Enviar detalhes desta melhoria por email para os responsáveis?')) {
    return;
  }
  
  try {
    const response = await fetch('/melhoria-continua-2/enviar-email', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${id}`
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert('✅ ' + data.message);
    } else {
      alert('❌ ' + data.message);
    }
  } catch (error) {
    console.error('Erro:', error);
    alert('❌ Erro ao enviar email');
  }
}
</script>
