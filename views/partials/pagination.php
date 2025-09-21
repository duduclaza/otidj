<?php
/**
 * Componente de Paginação Reutilizável
 * 
 * Uso:
 * include 'views/partials/pagination.php';
 * 
 * Variáveis necessárias:
 * $pagination = [
 *     'current_page' => 1,
 *     'total_pages' => 10,
 *     'total_records' => 100,
 *     'per_page' => 10,
 *     'has_previous' => false,
 *     'has_next' => true,
 *     'previous_page' => null,
 *     'next_page' => 2
 * ];
 */

if (!isset($pagination) || $pagination['total_pages'] <= 1) {
    return; // Não mostrar paginação se houver apenas 1 página ou menos
}
?>

<!-- Componente de Paginação -->
<div class="px-4 py-3 border-t bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4">
  <!-- Informações da paginação -->
  <div class="flex items-center text-sm text-gray-700">
    <span>
      Mostrando 
      <span class="font-medium"><?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?></span>
      até 
      <span class="font-medium"><?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']) ?></span>
      de 
      <span class="font-medium"><?= number_format($pagination['total_records']) ?></span>
      resultados
    </span>
  </div>
  
  <!-- Controles de navegação -->
  <div class="flex items-center space-x-2">
    <!-- Botão Primeira Página -->
    <?php if ($pagination['current_page'] > 2): ?>
      <a href="?page=1" 
         class="px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors"
         title="Primeira página (Home)">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
        </svg>
      </a>
    <?php endif; ?>

    <!-- Botão Anterior -->
    <?php if ($pagination['has_previous']): ?>
      <a href="?page=<?= $pagination['previous_page'] ?>" 
         class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors"
         title="Página anterior (← ou P)">
        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        <span class="hidden sm:inline">Anterior</span>
      </a>
    <?php else: ?>
      <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        <span class="hidden sm:inline">Anterior</span>
      </span>
    <?php endif; ?>

    <!-- Números das páginas -->
    <div class="flex items-center space-x-1">
      <?php
      $start = max(1, $pagination['current_page'] - 2);
      $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
      
      // Mostrar primeira página se não estiver no range
      if ($start > 1): ?>
        <a href="?page=1" 
           class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors"
           title="Página 1">1</a>
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
             class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors"
             title="Página <?= $i ?>">
            <?= $i ?>
          </a>
        <?php endif; ?>
      <?php endfor; ?>

      <!-- Mostrar última página se não estiver no range -->
      <?php if ($end < $pagination['total_pages']): ?>
        <?php if ($end < $pagination['total_pages'] - 1): ?>
          <span class="px-2 py-2 text-sm text-gray-500">...</span>
        <?php endif; ?>
        <a href="?page=<?= $pagination['total_pages'] ?>" 
           class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors"
           title="Página <?= $pagination['total_pages'] ?>">
          <?= $pagination['total_pages'] ?>
        </a>
      <?php endif; ?>
    </div>

    <!-- Botão Próximo -->
    <?php if ($pagination['has_next']): ?>
      <a href="?page=<?= $pagination['next_page'] ?>" 
         class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors"
         title="Próxima página (→ ou N)">
        <span class="hidden sm:inline">Próximo</span>
        <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </a>
    <?php else: ?>
      <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
        <span class="hidden sm:inline">Próximo</span>
        <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </span>
    <?php endif; ?>

    <!-- Botão Última Página -->
    <?php if ($pagination['current_page'] < $pagination['total_pages'] - 1): ?>
      <a href="?page=<?= $pagination['total_pages'] ?>" 
         class="px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors"
         title="Última página (End)">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
        </svg>
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- JavaScript para navegação por teclado (incluir apenas uma vez por página) -->
<script>
if (!window.paginationKeyboardHandlerAdded) {
  window.paginationKeyboardHandlerAdded = true;
  
  document.addEventListener('DOMContentLoaded', function() {
    // Navegação por teclado
    document.addEventListener('keydown', function(e) {
      // Verificar se não está em um input, textarea ou select
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
        return;
      }
      
      const currentPage = <?= $pagination['current_page'] ?>;
      const totalPages = <?= $pagination['total_pages'] ?>;
      
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
    
    // Adicionar loading aos links de paginação
    const paginationLinks = document.querySelectorAll('a[href*="page="]');
    paginationLinks.forEach(link => {
      link.addEventListener('click', function() {
        const originalText = this.textContent;
        this.textContent = 'Carregando...';
        this.style.opacity = '0.7';
        this.style.pointerEvents = 'none';
      });
    });
    
    // Mostrar atalhos no console
    console.log('⌨️ ATALHOS DE PAGINAÇÃO: ← P (anterior) | → N (próximo) | Home (primeira) | End (última)');
  });
}
</script>
