<section class="space-y-8">
  <!-- Cabeçalho de Boas-vindas -->
  <div class="text-center">
    <div class="mb-6">
      <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full mb-4">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
        </svg>
      </div>
      <h1 class="text-4xl font-bold text-gray-900 mb-2">Bem-vindo ao SGQ OTI DJ</h1>
      <p class="text-xl text-gray-600">Sistema de Gestão da Qualidade</p>
    </div>
    
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 mb-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-2">Olá, <?= e($userName) ?>!</h2>
      <p class="text-gray-700">Perfil: <span class="font-medium text-blue-600"><?= e($userProfile) ?></span></p>
      <p class="text-sm text-gray-600 mt-2">Utilize o menu lateral para navegar pelos módulos disponíveis para seu perfil.</p>
    </div>
  </div>


  <!-- Últimas Atualizações -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center">
        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
          <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">Últimas Atualizações</h3>
      </div>
    </div>
    <div class="p-6">
      <div class="space-y-6">
        <?php foreach ($updates as $index => $update): ?>
        <div class="<?= $index > 0 ? 'border-t border-gray-100 pt-6' : '' ?>">
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $update['type'] === 'Correção Crítica' ? 'bg-red-100' : ($update['type'] === 'Correção' ? 'bg-yellow-100' : 'bg-blue-100') ?>">
                <?php if ($update['type'] === 'Correção Crítica'): ?>
                  <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                <?php elseif ($update['type'] === 'Correção'): ?>
                  <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                <?php else: ?>
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                <?php endif; ?>
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $update['type'] === 'Correção Crítica' ? 'bg-red-100 text-red-800' : ($update['type'] === 'Correção' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') ?>">
                  <?= e($update['type']) ?>
                </span>
                <span class="text-sm text-gray-500">v<?= e($update['version']) ?></span>
                <span class="text-sm text-gray-400">•</span>
                <span class="text-sm text-gray-500"><?= e($update['date']) ?></span>
              </div>
              <h4 class="text-sm font-medium text-gray-900 mb-1"><?= e($update['title']) ?></h4>
              <p class="text-sm text-gray-600 mb-3"><?= e($update['description']) ?></p>
              <ul class="text-sm text-gray-600 space-y-1">
                <?php foreach ($update['items'] as $item): ?>
                <li class="flex items-start">
                  <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <?= e($item) ?>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
        <?php if ($index === 2): break; endif; // Mostrar apenas as 3 primeiras ?>
        <?php endforeach; ?>
      </div>
      
      <?php if (count($updates) > 3): ?>
      <div class="mt-6 pt-4 border-t border-gray-100 text-center">
        <button class="text-sm text-blue-600 hover:text-blue-700 font-medium" onclick="toggleAllUpdates()">
          <span id="toggleText">Ver todas as atualizações</span>
          <svg id="toggleIcon" class="w-4 h-4 inline ml-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
function toggleAllUpdates() {
  // Esta função pode ser implementada para mostrar/ocultar todas as atualizações
  const toggleText = document.getElementById('toggleText');
  const toggleIcon = document.getElementById('toggleIcon');
  
  if (toggleText.textContent === 'Ver todas as atualizações') {
    toggleText.textContent = 'Ver menos atualizações';
    toggleIcon.classList.add('rotate-180');
    // Aqui você pode implementar a lógica para mostrar todas as atualizações
  } else {
    toggleText.textContent = 'Ver todas as atualizações';
    toggleIcon.classList.remove('rotate-180');
    // Aqui você pode implementar a lógica para ocultar as atualizações extras
  }
}
</script>
