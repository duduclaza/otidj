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

  <!-- Cards de Totais Acumulados -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Card 1: Retornados -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
          </svg>
        </div>
        <span class="text-white text-opacity-80 text-xs font-medium">Até <?= date('d/m/Y') ?></span>
      </div>
      <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Toners Retornados</h3>
      <div class="flex items-end justify-between">
        <p class="text-4xl font-bold"><?= number_format($totais['retornados'] ?? 0, 0, ',', '.') ?></p>
        <span class="text-white text-opacity-80 text-xs">registros</span>
      </div>
      <div class="mt-4 pt-4 border-t border-white border-opacity-20">
        <p class="text-xs text-white text-opacity-80">📊 Total acumulado do módulo</p>
      </div>
    </div>

    <!-- Card 2: Amostragens -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
          </svg>
        </div>
        <span class="text-white text-opacity-80 text-xs font-medium">Até <?= date('d/m/Y') ?></span>
      </div>
      <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Amostragens 2.0</h3>
      <div class="flex items-end justify-between">
        <p class="text-4xl font-bold"><?= number_format($totais['amostragens'] ?? 0, 0, ',', '.') ?></p>
        <span class="text-white text-opacity-80 text-xs">registros</span>
      </div>
      <div class="mt-4 pt-4 border-t border-white border-opacity-20">
        <p class="text-xs text-white text-opacity-80">🔬 Total acumulado do módulo</p>
      </div>
    </div>

    <!-- Card 3: Melhorias Contínuas -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
          </svg>
        </div>
        <span class="text-white text-opacity-80 text-xs font-medium">Até <?= date('d/m/Y') ?></span>
      </div>
      <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Melhorias Contínuas</h3>
      <div class="flex items-end justify-between">
        <p class="text-4xl font-bold"><?= number_format($totais['melhorias'] ?? 0, 0, ',', '.') ?></p>
        <span class="text-white text-opacity-80 text-xs">registros</span>
      </div>
      <div class="mt-4 pt-4 border-t border-white border-opacity-20">
        <p class="text-xs text-white text-opacity-80">💡 Total acumulado do módulo</p>
      </div>
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
              <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $update['type'] === 'Correção Crítica' ? 'bg-red-100' : ($update['type'] === 'Correção' ? 'bg-yellow-100' : ($update['type'] === 'Ajuste' ? 'bg-purple-100' : ($update['type'] === 'Investigação' ? 'bg-orange-100' : 'bg-blue-100'))) ?>">
                <?php if ($update['type'] === 'Correção Crítica'): ?>
                  <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                <?php elseif ($update['type'] === 'Correção'): ?>
                  <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                <?php elseif ($update['type'] === 'Ajuste'): ?>
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                  </svg>
                <?php elseif ($update['type'] === 'Investigação'): ?>
                  <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
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
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $update['type'] === 'Correção Crítica' ? 'bg-red-100 text-red-800' : ($update['type'] === 'Correção' ? 'bg-yellow-100 text-yellow-800' : ($update['type'] === 'Ajuste' ? 'bg-purple-100 text-purple-800' : ($update['type'] === 'Investigação' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'))) ?>">
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
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
