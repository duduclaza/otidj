<?php
// Garantir que variáveis existam
$amostragem = $amostragem ?? [];
$responsaveis = $responsaveis ?? [];
$evidencias = $evidencias ?? [];
?>

<div class="max-w-6xl mx-auto">
  <!-- Header com botão voltar -->
  <div class="flex justify-between items-center mb-6">
    <div class="flex items-center space-x-4">
      <a href="/amostragens-2" class="text-gray-600 hover:text-gray-900">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
      </a>
      <h1 class="text-3xl font-bold text-gray-900">🔬 Detalhes da Amostragem</h1>
    </div>
    
    <div class="flex space-x-3">
      <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
        <span>🖨️</span>
        <span>Imprimir</span>
      </button>
      <a href="/amostragens-2" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
        <span>📋</span>
        <span>Voltar para Lista</span>
      </a>
    </div>
  </div>

  <!-- Badge de Status -->
  <div class="mb-6 flex justify-center">
    <span class="px-6 py-3 text-lg font-bold rounded-full
      <?php
        switch($amostragem['status_final']) {
          case 'Aprovado': echo 'bg-green-100 text-green-800'; break;
          case 'Aprovado Parcialmente': echo 'bg-yellow-100 text-yellow-800'; break;
          case 'Reprovado': echo 'bg-red-100 text-red-800'; break;
          default: echo 'bg-gray-100 text-gray-800';
        }
      ?>">
      <?= e($amostragem['status_final']) ?>
    </span>
  </div>

  <!-- Card Principal -->
  <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
    <!-- Informações Básicas -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
      <h2 class="text-xl font-bold text-white">📄 Informações Básicas</h2>
    </div>
    
    <div class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Número da NF</label>
          <p class="text-lg font-semibold text-gray-900"><?= e($amostragem['numero_nf']) ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Data de Criação</label>
          <p class="text-lg text-gray-900"><?= date('d/m/Y H:i', strtotime($amostragem['created_at'])) ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Criado por</label>
          <p class="text-lg text-gray-900"><?= e($amostragem['criador_nome']) ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filial</label>
          <p class="text-lg text-gray-900"><?= e($amostragem['filial_nome']) ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
          <p class="text-lg text-gray-900"><?= e($amostragem['fornecedor_nome']) ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Última Atualização</label>
          <p class="text-lg text-gray-900"><?= $amostragem['updated_at'] ? date('d/m/Y H:i', strtotime($amostragem['updated_at'])) : '-' ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Produto -->
  <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4">
      <h2 class="text-xl font-bold text-white">📦 Informações do Produto</h2>
    </div>
    
    <div class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Produto</label>
          <p class="text-lg font-semibold text-gray-900"><?= e($amostragem['tipo_produto']) ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
          <p class="text-lg font-semibold text-gray-900"><?= e($amostragem['codigo_produto']) ?></p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Produto</label>
          <p class="text-lg text-gray-900"><?= e($amostragem['nome_produto']) ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Quantidades -->
  <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-green-600 to-green-800 px-6 py-4">
      <h2 class="text-xl font-bold text-white">📊 Quantidades e Resultados</h2>
    </div>
    
    <div class="p-6">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="text-center">
          <label class="block text-sm font-medium text-gray-700 mb-2">Recebida</label>
          <p class="text-4xl font-bold text-blue-600"><?= $amostragem['quantidade_recebida'] ?></p>
        </div>
        
        <div class="text-center">
          <label class="block text-sm font-medium text-gray-700 mb-2">Testada</label>
          <p class="text-4xl font-bold text-purple-600"><?= $amostragem['quantidade_testada'] ?></p>
        </div>
        
        <div class="text-center">
          <label class="block text-sm font-medium text-gray-700 mb-2">Aprovada</label>
          <p class="text-4xl font-bold text-green-600"><?= $amostragem['quantidade_aprovada'] ?></p>
        </div>
        
        <div class="text-center">
          <label class="block text-sm font-medium text-gray-700 mb-2">Reprovada</label>
          <p class="text-4xl font-bold text-red-600"><?= $amostragem['quantidade_reprovada'] ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Responsáveis -->
  <?php if (!empty($responsaveis)): ?>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-6 py-4">
      <h2 class="text-xl font-bold text-white">👥 Responsáveis pelo Teste</h2>
    </div>
    
    <div class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($responsaveis as $resp): ?>
        <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
          <div class="bg-indigo-100 text-indigo-600 w-10 h-10 rounded-full flex items-center justify-center font-bold">
            <?= strtoupper(substr($resp['name'], 0, 1)) ?>
          </div>
          <div>
            <p class="font-semibold text-gray-900"><?= e($resp['name']) ?></p>
            <?php if (!empty($resp['email'])): ?>
            <p class="text-sm text-gray-600"><?= e($resp['email']) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Anexo NF -->
  <?php if (!empty($amostragem['anexo_nf_nome'])): ?>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-yellow-600 to-yellow-800 px-6 py-4">
      <h2 class="text-xl font-bold text-white">📄 Anexo da Nota Fiscal</h2>
    </div>
    
    <div class="p-6">
      <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
        <div class="flex items-center space-x-3">
          <div class="text-4xl">📎</div>
          <div>
            <p class="font-semibold text-gray-900"><?= e($amostragem['anexo_nf_nome']) ?></p>
            <p class="text-sm text-gray-600">
              Tipo: <?= e($amostragem['anexo_nf_tipo']) ?> | 
              Tamanho: <?= number_format($amostragem['anexo_nf_tamanho'] / 1024, 2) ?> KB
            </p>
          </div>
        </div>
        <a href="/amostragens-2/<?= $amostragem['id'] ?>/download-nf" 
           class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold">
          📥 Baixar
        </a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Evidências -->
  <?php if (!empty($evidencias)): ?>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-pink-600 to-pink-800 px-6 py-4">
      <h2 class="text-xl font-bold text-white">📸 Evidências (<?= count($evidencias) ?>)</h2>
    </div>
    
    <div class="p-6">
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($evidencias as $ev): ?>
        <div class="border border-gray-300 rounded-lg p-3 hover:shadow-lg transition-shadow">
          <div class="text-center mb-2">
            <div class="text-4xl mb-2">🖼️</div>
            <p class="text-sm font-medium text-gray-900 truncate" title="<?= e($ev['nome']) ?>">
              <?= e($ev['nome']) ?>
            </p>
            <p class="text-xs text-gray-500 mt-1">
              <?= number_format($ev['tamanho'] / 1024, 1) ?> KB
            </p>
          </div>
          <a href="/amostragens-2/<?= $amostragem['id'] ?>/download-evidencia/<?= $ev['id'] ?>" 
             class="block w-full text-center bg-pink-600 hover:bg-pink-700 text-white px-3 py-2 rounded text-sm font-semibold">
            📥 Baixar
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

<style>
@media print {
  .no-print {
    display: none !important;
  }
  
  body {
    background: white !important;
  }
}
</style>
