<?php
// Garantir que as variáveis existam
$amostragens = $amostragens ?? [];
$usuarios = $usuarios ?? [];
$filiais = $filiais ?? [];
$fornecedores = $fornecedores ?? [];
$toners = $toners ?? [];

/**
 * Função para construir URL de paginação mantendo os filtros
 */
function construirUrlPaginacao($pagina) {
    $params = $_GET;
    $params['pagina'] = $pagina;
    return '/amostragens-2?' . http_build_query($params);
}
?>

<section class="mb-8">
  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">🔬 Amostragens 2.0</h1>
    <button onclick="novaAmostragem()" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
      <span>➕</span>
      <span>Nova Amostragem</span>
    </button>
  </div>

  <!-- Formulário Inline (Hidden por padrão) -->
  <div id="amostragemFormContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
      <h2 id="formTitle" class="text-lg font-semibold text-gray-100">🔬 Nova Amostragem</h2>
      <button onclick="closeAmostragemModal()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="amostragemForm" action="/amostragens-2/store" method="POST" enctype="multipart/form-data" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Número da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Número da NF *</label>
          <input type="text" name="numero_nf" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Anexo da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Anexo da NF (PDF ou Foto - Máx 10MB)</label>
          <input type="file" name="anexo_nf" accept=".pdf,image/*" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200">
          <div id="anexoNfExistente" class="hidden mt-2">
            <p class="text-xs text-gray-400">Anexo atual: <span id="anexoNfNome" class="text-blue-400"></span></p>
            <p class="text-xs text-gray-500">Envie um novo arquivo para substituir</p>
          </div>
        </div>

        <!-- Tipo de Produto -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Tipo de Produto *</label>
          <select name="tipo_produto" id="tipoProduto" required onchange="carregarProdutos()" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <option value="">Selecione...</option>
            <option value="Toner">Toner</option>
            <option value="Peça">Peça</option>
            <option value="Máquina">Máquina</option>
          </select>
        </div>

        <!-- Código do Produto -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Código do Produto *</label>
          <div class="relative">
            <input type="text" id="buscaProduto" placeholder="Digite para buscar..." onkeyup="filtrarProdutos()" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <select name="produto_id" id="produtoSelect" required size="5" class="hidden w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 mt-2 max-h-40 overflow-y-auto">
              <option value="">Selecione o tipo de produto primeiro</option>
            </select>
          </div>
          <input type="hidden" name="codigo_produto" id="codigoProduto">
          <input type="hidden" name="nome_produto" id="nomeProduto">
        </div>

        <!-- Quantidade Recebida -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quantidade Recebida *</label>
          <input type="number" name="quantidade_recebida" min="1" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Quantidade Testada -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quantidade Testada <span class="text-gray-400 text-xs">(Opcional)</span></label>
          <input type="number" name="quantidade_testada" min="0" value="" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Quantidade Aprovada -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quantidade Aprovada <span class="text-gray-400 text-xs">(Opcional)</span></label>
          <input type="number" name="quantidade_aprovada" min="0" value="" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Quantidade Reprovada -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Quantidade Reprovada <span class="text-gray-400 text-xs">(Opcional)</span></label>
          <input type="number" name="quantidade_reprovada" min="0" value="" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Fornecedor -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Fornecedor *</label>
          <select name="fornecedor_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <option value="">Selecione...</option>
            <?php foreach ($fornecedores as $forn): ?>
              <option value="<?= $forn['id'] ?>"><?= e($forn['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Responsáveis -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Responsáveis pelo Teste *</label>
          <select name="responsaveis[]" multiple required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500" size="4">
            <?php foreach ($usuarios as $user): ?>
              <option value="<?= $user['id'] ?>"><?= e($user['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-gray-400 mt-1">Segure Ctrl/Cmd para selecionar múltiplos</p>
        </div>

        <!-- Status Final -->
        <div>
          <label class="block text-sm font-medium text-gray-200 mb-1">Status Final <span class="text-gray-400 text-xs">(Automático se campos vazios)</span></label>
          <select name="status_final" id="statusFinalSelect" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
            <option value="Pendente">Pendente</option>
            <option value="Aprovado">Aprovado</option>
            <option value="Aprovado Parcialmente">Aprovado Parcialmente</option>
            <option value="Reprovado">Reprovado</option>
          </select>
          <p class="text-xs text-gray-400 mt-1">💡 Status será "Pendente" automaticamente se não houver resultados de testes</p>
        </div>

        <!-- Observação -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-200 mb-1">Observação <span class="text-gray-400 text-xs">(Opcional)</span></label>
          <textarea name="observacoes" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="Informações adicionais sobre a amostragem..."></textarea>
        </div>

        <!-- Evidências (Fotos) -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-200 mb-1">Evidências (Fotos - Máx 5 arquivos de 10MB cada)</label>
          <input type="file" name="evidencias[]" multiple accept="image/*" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200">
          <p class="text-xs text-gray-400 mt-1">Opcional - Máximo 5 fotos</p>
          <div id="evidenciasExistentes" class="hidden mt-3">
            <p class="text-sm font-medium text-gray-200 mb-2">Evidências atuais:</p>
            <div id="listaEvidencias" class="grid grid-cols-2 md:grid-cols-3 gap-2"></div>
            <p class="text-xs text-gray-500 mt-2">Novas evidências serão adicionadas às existentes</p>
          </div>
        </div>
      </div>

      <div class="flex justify-end space-x-3 pt-4">
        <button type="button" onclick="cancelarEdicao()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
          Cancelar
        </button>
        <button type="submit" id="submitButton" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          💾 Salvar Amostragem
        </button>
      </div>
    </form>
  </div>

  <!-- Filtros -->
  <div class="bg-white border rounded-lg p-4 mb-6">
    <h3 class="font-semibold text-gray-900 mb-4">🔍 Filtros</h3>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Código do Produto</label>
        <input type="text" name="codigo_produto" value="<?= $_GET['codigo_produto'] ?? '' ?>" placeholder="Digite o código..." class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
        <select name="user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Todos</option>
          <?php foreach ($usuarios as $user): ?>
            <option value="<?= $user['id'] ?>" <?= ($_GET['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
              <?= e($user['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Filial</label>
        <select name="filial_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Todas</option>
          <?php foreach ($filiais as $filial): ?>
            <option value="<?= $filial['id'] ?>" <?= ($_GET['filial_id'] ?? '') == $filial['id'] ? 'selected' : '' ?>>
              <?= e($filial['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
        <select name="fornecedor_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Todos</option>
          <?php foreach ($fornecedores as $forn): ?>
            <option value="<?= $forn['id'] ?>" <?= ($_GET['fornecedor_id'] ?? '') == $forn['id'] ? 'selected' : '' ?>>
              <?= e($forn['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Todos</option>
          <option value="Pendente" <?= ($_GET['status'] ?? '') == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
          <option value="Aprovado" <?= ($_GET['status'] ?? '') == 'Aprovado' ? 'selected' : '' ?>>Aprovado</option>
          <option value="Aprovado Parcialmente" <?= ($_GET['status'] ?? '') == 'Aprovado Parcialmente' ? 'selected' : '' ?>>Aprovado Parcialmente</option>
          <option value="Reprovado" <?= ($_GET['status'] ?? '') == 'Reprovado' ? 'selected' : '' ?>>Reprovado</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
        <input type="date" name="data_inicio" value="<?= $_GET['data_inicio'] ?? '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
        <input type="date" name="data_fim" value="<?= $_GET['data_fim'] ?? '' ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>

      <div class="flex items-end gap-1.5 col-span-1">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          Filtrar
        </button>
        <a href="/amostragens-2" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg text-center transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          Limpar
        </a>
        <button type="button" onclick="exportarExcel()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors whitespace-nowrap font-medium shadow-md text-sm">
          📊 Exportar
        </button>
      </div>
    </form>
  </div>

  <!-- Controles de Paginação -->
  <?php if (isset($paginacao)): ?>
  <div class="bg-white border rounded-lg p-4 mb-4">
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

  <!-- Grid de Amostragens -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NF</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filial</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fornecedor</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qtd Recebida</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qtd Testada</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aprovada</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reprovada</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aprovado Por</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anexo NF</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evidências</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($amostragens as $amostra): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= date('d/m/Y', strtotime($amostra['created_at'])) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <?= e($amostra['numero_nf']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['usuario_nome']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['filial_nome']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['tipo_produto']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['codigo_produto']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?= e($amostra['fornecedor_nome']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?= $amostra['quantidade_recebida'] ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?= $amostra['quantidade_testada'] ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-600 font-semibold">
              <?= $amostra['quantidade_aprovada'] ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-semibold">
              <?= $amostra['quantidade_reprovada'] ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <select onchange="alterarStatus(<?= $amostra['id'] ?>, this.value)" 
                      data-old-value="<?= e($amostra['status_final']) ?>"
                      class="px-2 py-1 text-xs font-semibold rounded-md border-0 cursor-pointer
                        <?php
                          switch($amostra['status_final']) {
                            case 'Aprovado': echo 'bg-green-100 text-green-800'; break;
                            case 'Aprovado Parcialmente': echo 'bg-yellow-100 text-yellow-800'; break;
                            case 'Reprovado': echo 'bg-red-100 text-red-800'; break;
                            default: echo 'bg-gray-100 text-gray-800';
                          }
                        ?>">
                <option value="Pendente" <?= $amostra['status_final'] == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                <option value="Aprovado" <?= $amostra['status_final'] == 'Aprovado' ? 'selected' : '' ?>>Aprovado</option>
                <option value="Aprovado Parcialmente" <?= $amostra['status_final'] == 'Aprovado Parcialmente' ? 'selected' : '' ?>>Aprovado Parcialmente</option>
                <option value="Reprovado" <?= $amostra['status_final'] == 'Reprovado' ? 'selected' : '' ?>>Reprovado</option>
              </select>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?php if (!empty($amostra['aprovado_por_nome'])): ?>
                <div class="flex flex-col">
                  <span class="text-gray-900 font-medium"><?= e($amostra['aprovado_por_nome']) ?></span>
                  <?php if (!empty($amostra['aprovado_em'])): ?>
                    <?php
                    // Converter para timezone do Brasil (América/São_Paulo = UTC-3)
                    $dt = new DateTime($amostra['aprovado_em'], new DateTimeZone('UTC'));
                    $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                    ?>
                    <span class="text-xs text-gray-500"><?= $dt->format('d/m/Y H:i') ?></span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <span class="text-gray-400 text-xs">-</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?php if (!empty($amostra['anexo_nf_nome'])): ?>
                <a href="/amostragens-2/<?= $amostra['id'] ?>/download-nf" 
                   class="text-blue-600 hover:text-blue-800" 
                   title="<?= e($amostra['anexo_nf_nome']) ?>">
                  📄 Baixar
                </a>
              <?php else: ?>
                <span class="text-gray-400">-</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <?php if ($amostra['total_evidencias'] > 0): ?>
                <button onclick="baixarEvidencias(<?= $amostra['id'] ?>)" 
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">
                  📥 Baixar (<?= $amostra['total_evidencias'] ?>)
                </button>
              <?php else: ?>
                <span class="text-gray-400">Sem evidências</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
              <button onclick="editarAmostragem(<?= $amostra['id'] ?>)" 
                      class="text-blue-600 hover:text-blue-800">
                ✏️ Editar
              </button>
              <button onclick="excluirAmostragem(<?= $amostra['id'] ?>)" 
                      class="text-red-600 hover:text-red-800">
                🗑️ Excluir
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          
          <?php if (empty($amostragens)): ?>
          <tr>
            <td colspan="16" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-semibold mb-2">Nenhuma amostragem encontrada</p>
                <p class="text-sm">Tente ajustar os filtros ou crie uma nova amostragem</p>
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
  <div class="bg-white border rounded-lg p-4 mt-4">
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

<!-- Modal de Loading para Downloads -->
<div id="loadingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
    <div class="text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
      <h3 class="text-lg font-semibold mb-2">Preparando Download...</h3>
      <p class="text-gray-600">Aguarde enquanto preparamos as evidências para download.</p>
    </div>
  </div>
</div>

<style>
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
</style>

<script>
const produtosData = {
  toners: <?= json_encode($toners ?? []) ?>,
  pecas: <?= json_encode($pecas ?? []) ?>,
  maquinas: <?= json_encode($maquinas ?? []) ?>
};

function openAmostragemModal() {
  document.getElementById('amostragemFormContainer').classList.remove('hidden');
  document.getElementById('amostragemFormContainer').scrollIntoView({ behavior: 'smooth' });
}

function closeAmostragemModal() {
  document.getElementById('amostragemFormContainer').classList.add('hidden');
  document.getElementById('amostragemForm').reset();
  
  // Voltar para modo criar
  document.getElementById('amostragemForm').action = '/amostragens-2/store';
  
  // Remover campo hidden de ID se existir
  const hiddenId = document.querySelector('input[name="amostragem_id"]');
  if (hiddenId) {
    hiddenId.remove();
  }
}

function carregarProdutos() {
  const tipo = document.getElementById('tipoProduto').value;
  const select = document.getElementById('produtoSelect');
  
  select.innerHTML = '<option value="">Selecione...</option>';
  
  let produtos = [];
  
  if (tipo === 'Toner') {
    produtos = produtosData.toners;
  } else if (tipo === 'Peça') {
    produtos = produtosData.pecas;
  } else if (tipo === 'Máquina') {
    produtos = produtosData.maquinas;
  }
  
  produtos.forEach(p => {
    const option = document.createElement('option');
    option.value = p.id;
    // Todos os tipos: mostrar apenas código de referência
    option.textContent = p.codigo;
    option.dataset.codigo = p.codigo;
    option.dataset.nome = p.nome;
    select.appendChild(option);
  });
  
  select.classList.remove('hidden');
  
  select.onchange = function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('codigoProduto').value = selected.dataset.codigo || '';
    document.getElementById('nomeProduto').value = selected.dataset.nome || '';
  };
}

function filtrarProdutos() {
  const busca = document.getElementById('buscaProduto').value.toLowerCase();
  const select = document.getElementById('produtoSelect');
  const options = select.options;
  
  for (let i = 0; i < options.length; i++) {
    const text = options[i].textContent.toLowerCase();
    options[i].style.display = text.includes(busca) ? '' : 'none';
  }
}

// Monitorar campos de teste e ajustar status automaticamente
const qtdTestadaInput = document.querySelector('input[name="quantidade_testada"]');
const qtdAprovadaInput = document.querySelector('input[name="quantidade_aprovada"]');
const qtdReprovadaInput = document.querySelector('input[name="quantidade_reprovada"]');
const statusSelect = document.getElementById('statusFinalSelect');

function verificarCamposTestagem() {
  const testada = qtdTestadaInput.value.trim();
  const aprovada = qtdAprovadaInput.value.trim();
  const reprovada = qtdReprovadaInput.value.trim();
  
  // Se algum campo estiver vazio, forçar status Pendente
  if (!testada || !aprovada || !reprovada) {
    statusSelect.value = 'Pendente';
    statusSelect.style.opacity = '0.6';
    statusSelect.title = 'Preencha os campos de teste para alterar o status';
  } else {
    statusSelect.style.opacity = '1';
    statusSelect.title = '';
  }
}

// Adicionar listeners
if (qtdTestadaInput) qtdTestadaInput.addEventListener('input', verificarCamposTestagem);
if (qtdAprovadaInput) qtdAprovadaInput.addEventListener('input', verificarCamposTestagem);
if (qtdReprovadaInput) qtdReprovadaInput.addEventListener('input', verificarCamposTestagem);

// Verificar ao carregar
verificarCamposTestagem();

// Submit do formulário
document.getElementById('amostragemForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  try {
    const response = await fetch(this.action, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      if (result.redirect) {
        window.location.href = result.redirect;
      } else {
        window.location.reload();
      }
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro ao enviar formulário');
  }
});

// Baixar evidências
async function baixarEvidencias(amostragemId) {
  console.log('Baixando evidências para amostragem:', amostragemId);
  
  // Mostrar modal de loading
  document.getElementById('loadingModal').classList.remove('hidden');
  
  try {
    const response = await fetch(`/amostragens-2/${amostragemId}/evidencias`);
    const data = await response.json();
    
    console.log('Resposta do servidor:', data);
    
    if (data.success && data.evidencias && data.evidencias.length > 0) {
      console.log('Evidências encontradas:', data.evidencias.length);
      
      // Baixar cada evidência individualmente
      for (let i = 0; i < data.evidencias.length; i++) {
        const ev = data.evidencias[i];
        console.log(`Baixando evidência ${i + 1}/${data.evidencias.length}: ${ev.nome}`);
        
        // Criar link temporário para download
        const link = document.createElement('a');
        link.href = `/amostragens-2/${amostragemId}/download-evidencia/${ev.id}`;
        link.download = ev.nome;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Pequeno delay entre downloads para não sobrecarregar
        if (i < data.evidencias.length - 1) {
          await new Promise(resolve => setTimeout(resolve, 500));
        }
      }
      
      // Fechar modal e mostrar sucesso
      document.getElementById('loadingModal').classList.add('hidden');
      alert(`✅ ${data.evidencias.length} evidência(s) baixada(s) com sucesso!`);
      
    } else {
      document.getElementById('loadingModal').classList.add('hidden');
      alert('⚠️ Nenhuma evidência encontrada para esta amostragem');
    }
    
  } catch (error) {
    console.error('Erro ao baixar evidências:', error);
    document.getElementById('loadingModal').classList.add('hidden');
    alert('❌ Erro ao baixar evidências: ' + error.message);
  }
}

// Função de email removida - mantendo apenas notificações visuais

// Editar amostragem
async function editarAmostragem(id) {
  try {
    console.log('Carregando amostragem para edição:', id);
    
    // Buscar dados da amostragem via API JSON
    const response = await fetch(`/amostragens-2/${id}/details-json`);
    
    // Verificar se a resposta é válida
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    // Verificar se tem conteúdo antes de fazer parse
    const text = await response.text();
    console.log('Resposta do servidor:', text);
    
    if (!text) {
      throw new Error('Resposta vazia do servidor');
    }
    
    const result = JSON.parse(text);
    
    if (!result.success) {
      alert('Erro ao carregar amostragem: ' + result.message);
      return;
    }
    
    const amostra = result.amostragem;
    console.log('Dados carregados:', amostra);
    
    // Alterar título do formulário
    document.getElementById('formTitle').textContent = '✏️ Editar Amostragem';
    
    // Alterar action do formulário para update
    document.getElementById('amostragemForm').action = '/amostragens-2/update';
    
    // Adicionar campo hidden com ID da amostragem
    let hiddenId = document.querySelector('input[name="amostragem_id"]');
    if (!hiddenId) {
      hiddenId = document.createElement('input');
      hiddenId.type = 'hidden';
      hiddenId.name = 'amostragem_id';
      document.getElementById('amostragemForm').appendChild(hiddenId);
    }
    hiddenId.value = id;
    
    // Preencher campos do formulário
    document.querySelector('input[name="numero_nf"]').value = amostra.numero_nf || '';
    
    // Tipo de produto
    document.getElementById('tipoProduto').value = amostra.tipo_produto || '';
    carregarProdutos(); // Carregar lista de produtos
    
    // Aguardar produtos carregarem e selecionar o correto
    setTimeout(() => {
      const produtoSelect = document.getElementById('produtoSelect');
      produtoSelect.value = amostra.produto_id || '';
      
      // Disparar evento change para preencher campos hidden
      const event = new Event('change');
      produtoSelect.dispatchEvent(event);
    }, 100);
    
    // Quantidades
    document.querySelector('input[name="quantidade_recebida"]').value = amostra.quantidade_recebida || '';
    document.querySelector('input[name="quantidade_testada"]').value = amostra.quantidade_testada || '';
    document.querySelector('input[name="quantidade_aprovada"]').value = amostra.quantidade_aprovada || '';
    document.querySelector('input[name="quantidade_reprovada"]').value = amostra.quantidade_reprovada || '';
    
    // Fornecedor
    document.querySelector('select[name="fornecedor_id"]').value = amostra.fornecedor_id || '';
    
    // Responsáveis (múltipla seleção)
    if (amostra.responsaveis) {
      const responsaveisIds = amostra.responsaveis.split(',').map(id => id.trim());
      const responsaveisSelect = document.querySelector('select[name="responsaveis[]"]');
      
      for (let option of responsaveisSelect.options) {
        option.selected = responsaveisIds.includes(option.value);
      }
    }
    
    // Status
    document.querySelector('select[name="status_final"]').value = amostra.status_final || 'Pendente';
    
    // Observações
    document.querySelector('textarea[name="observacoes"]').value = amostra.observacoes || '';
    
    // Mostrar anexo NF existente se houver
    if (amostra.anexo_nf_nome) {
      document.getElementById('anexoNfExistente').classList.remove('hidden');
      document.getElementById('anexoNfNome').textContent = amostra.anexo_nf_nome;
    } else {
      document.getElementById('anexoNfExistente').classList.add('hidden');
    }
    
    // Buscar e mostrar evidências existentes
    const evidResponse = await fetch(`/amostragens-2/${id}/evidencias`);
    const evidResult = await evidResponse.json();
    
    if (evidResult.success && evidResult.evidencias && evidResult.evidencias.length > 0) {
      const listaEvidencias = document.getElementById('listaEvidencias');
      listaEvidencias.innerHTML = '';
      
      evidResult.evidencias.forEach(ev => {
        const div = document.createElement('div');
        div.className = 'bg-gray-700 p-2 rounded text-xs';
        div.innerHTML = `
          <p class="text-gray-300 truncate" title="${ev.nome}">${ev.nome}</p>
          <p class="text-gray-500">${(ev.tamanho / 1024).toFixed(1)} KB</p>
        `;
        listaEvidencias.appendChild(div);
      });
      
      document.getElementById('evidenciasExistentes').classList.remove('hidden');
    } else {
      document.getElementById('evidenciasExistentes').classList.add('hidden');
    }
    
    // Alterar texto do botão
    document.getElementById('submitButton').innerHTML = '💾 Atualizar Amostragem';
    
    // Mostrar formulário
    document.getElementById('amostragemFormContainer').classList.remove('hidden');
    
    // Scroll para o formulário
    document.getElementById('amostragemFormContainer').scrollIntoView({ behavior: 'smooth' });
    
    console.log('Formulário preenchido e pronto para edição');
    
  } catch (error) {
    console.error('Erro ao carregar amostragem:', error);
    alert('Erro ao carregar dados da amostragem: ' + error.message);
  }
}

// Nova amostragem
function novaAmostragem() {
  // Limpar formulário
  document.getElementById('amostragemForm').reset();
  
  // Remover campo hidden de ID se existir
  const hiddenId = document.querySelector('input[name="amostragem_id"]');
  if (hiddenId) {
    hiddenId.remove();
  }
  
  // Restaurar título do formulário
  document.getElementById('formTitle').textContent = '🔬 Nova Amostragem';
  
  // Restaurar action original e texto do botão
  document.getElementById('amostragemForm').action = '/amostragens-2/store';
  document.getElementById('submitButton').innerHTML = '💾 Salvar Amostragem';
  
  // Esconder seções de anexos existentes
  document.getElementById('anexoNfExistente').classList.add('hidden');
  document.getElementById('evidenciasExistentes').classList.add('hidden');
  
  // Mostrar formulário
  document.getElementById('amostragemFormContainer').classList.remove('hidden');
  
  // Scroll para o formulário
  document.getElementById('amostragemFormContainer').scrollIntoView({ behavior: 'smooth' });
  
  console.log('Formulário preparado para nova amostragem');
}

// Cancelar edição
function cancelarEdicao() {
  // Limpar formulário
  document.getElementById('amostragemForm').reset();
  
  // Remover campo hidden de ID se existir
  const hiddenId = document.querySelector('input[name="amostragem_id"]');
  if (hiddenId) {
    hiddenId.remove();
  }
  
  // Restaurar título do formulário
  document.getElementById('formTitle').textContent = '🔬 Nova Amostragem';
  
  // Restaurar action original e texto do botão
  document.getElementById('amostragemForm').action = '/amostragens-2/store';
  document.getElementById('submitButton').innerHTML = '💾 Salvar Amostragem';
  
  // Esconder seções de anexos existentes
  document.getElementById('anexoNfExistente').classList.add('hidden');
  document.getElementById('evidenciasExistentes').classList.add('hidden');
  
  // Esconder formulário
  document.getElementById('amostragemFormContainer').classList.add('hidden');
  
  console.log('Edição cancelada, formulário limpo');
}

// Excluir amostragem
async function excluirAmostragem(id) {
  if (!confirm('Tem certeza que deseja excluir esta amostragem?')) return;
  
  try {
    const response = await fetch('/amostragens-2/delete', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}`
    });
    
    const result = await response.json();
    alert(result.message);
    
    if (result.success) {
      window.location.reload();
    }
  } catch (error) {
    alert('Erro ao excluir amostragem');
  }
}

// Alterar quantidade de registros por página
function alterarPorPagina(porPagina) {
  const urlParams = new URLSearchParams(window.location.search);
  urlParams.set('por_pagina', porPagina);
  urlParams.set('pagina', '1'); // Resetar para primeira página
  window.location.href = '/amostragens-2?' + urlParams.toString();
}

// Exportar para Excel
function exportarExcel() {
  // Coletar filtros ativos
  const params = new URLSearchParams();
  
  const codigoProduto = document.getElementById('filtroCodigo')?.value;
  if (codigoProduto) params.append('codigo_produto', codigoProduto);
  
  const userId = document.getElementById('filtroUsuario')?.value;
  if (userId) params.append('user_id', userId);
  
  const filialId = document.getElementById('filtroFilial')?.value;
  if (filialId) params.append('filial_id', filialId);
  
  const fornecedorId = document.getElementById('filtroFornecedor')?.value;
  if (fornecedorId) params.append('fornecedor_id', fornecedorId);
  
  const statusFinal = document.getElementById('filtroStatus')?.value;
  if (statusFinal) params.append('status_final', statusFinal);
  
  const dataInicio = document.getElementById('filtroDataInicio')?.value;
  if (dataInicio) params.append('data_inicio', dataInicio);
  
  const dataFim = document.getElementById('filtroDataFim')?.value;
  if (dataFim) params.append('data_fim', dataFim);
  
  // Redirecionar para exportação
  const url = `/amostragens-2/export?${params.toString()}`;
  window.location.href = url;
}

// Alterar status da amostragem
async function alterarStatus(id, novoStatus) {
  // Salvar o select que foi alterado para poder reverter se cancelar
  const selectElement = event.target;
  const oldValue = selectElement.getAttribute('data-old-value') || selectElement.value;
  
  if (!confirm(`Tem certeza que deseja alterar o status para "${novoStatus}"?\n\nUm email será enviado aos responsáveis.`)) {
    // Reverter select ao valor anterior
    selectElement.value = oldValue;
    return;
  }
  
  // Desabilitar select durante o processamento
  selectElement.disabled = true;
  
  try {
    console.log(`🔄 Alterando status da amostragem ${id} para: ${novoStatus}`);
    
    const response = await fetch('/amostragens-2/update-status', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}&status=${encodeURIComponent(novoStatus)}`
    });
    
    const result = await response.json();
    console.log('📡 Resposta do servidor:', result);
    
    if (result.success) {
      console.log('✅ Status atualizado com sucesso!');
      alert('✅ ' + result.message + '\n\n📧 Email enviado aos responsáveis!');
      
      // Recarregar página para mostrar mudanças
      console.log('🔄 Recarregando página...');
      window.location.reload();
    } else {
      alert('❌ Erro: ' + result.message);
      // Reverter select ao valor anterior
      selectElement.value = oldValue;
      selectElement.disabled = false;
    }
  } catch (error) {
    console.error('❌ Erro ao alterar status:', error);
    alert('❌ Erro ao alterar status: ' + error.message);
    // Reverter select ao valor anterior
    selectElement.value = oldValue;
    selectElement.disabled = false;
  }
}
</script>
