<section class="space-y-6">
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-semibold">🗒️ Auditoria do Sistema</h1>
      <p class="text-sm text-gray-600 mt-1">Registro de todas as ações realizadas no sistema</p>
    </div>
    <a href="/auditoria/export" class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200 font-medium">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      Exportar CSV
    </a>
  </div>

  <!-- Estatísticas -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white border rounded-lg p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Ações Hoje</p>
          <p class="text-3xl font-bold text-blue-600"><?= $stats['today'] ?? 0 ?></p>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
          <span class="text-2xl">📅</span>
        </div>
      </div>
    </div>

    <div class="bg-white border rounded-lg p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Ações Esta Semana</p>
          <p class="text-3xl font-bold text-green-600"><?= $stats['week'] ?? 0 ?></p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
          <span class="text-2xl">📊</span>
        </div>
      </div>
    </div>

    <div class="bg-white border rounded-lg p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Ações Este Mês</p>
          <p class="text-3xl font-bold text-purple-600"><?= $stats['month'] ?? 0 ?></p>
        </div>
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
          <span class="text-2xl">📈</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Logs de Auditoria -->
  <div class="bg-white border rounded-lg">
    <div class="px-4 py-3 border-b">
      <h2 class="text-lg font-medium">Registro de Ações</h2>
      <p class="text-sm text-gray-600 mt-1">Últimas 100 ações realizadas no sistema</p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Data/Hora</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Usuário</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Ação</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Módulo</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">Detalhes</th>
            <th class="px-3 py-2 text-left font-medium text-gray-700">IP</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($logs)): ?>
            <tr>
              <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                <div class="flex flex-col items-center gap-2">
                  <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <p class="font-medium">Nenhum registro de auditoria encontrado</p>
                  <p class="text-xs">A tabela de auditoria pode não estar criada ainda</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($logs as $log): ?>
              <?php
                // Definir cor baseada na ação
                $actionColors = [
                  'create' => 'bg-green-100 text-green-800',
                  'update' => 'bg-blue-100 text-blue-800',
                  'delete' => 'bg-red-100 text-red-800',
                  'login' => 'bg-purple-100 text-purple-800',
                  'logout' => 'bg-gray-100 text-gray-800',
                  'view' => 'bg-cyan-100 text-cyan-800',
                  'export' => 'bg-orange-100 text-orange-800',
                  'import' => 'bg-yellow-100 text-yellow-800',
                ];
                $actionColor = $actionColors[$log['action']] ?? 'bg-gray-100 text-gray-800';
              ?>
              <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 whitespace-nowrap">
                  <div class="text-xs">
                    <?= date('d/m/Y', strtotime($log['created_at'])) ?>
                    <br>
                    <span class="text-gray-500"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                  </div>
                </td>
                <td class="px-3 py-2">
                  <div class="text-xs">
                    <div class="font-medium"><?= e($log['user_name'] ?? 'N/A') ?></div>
                    <div class="text-gray-500"><?= e($log['user_email'] ?? '') ?></div>
                  </div>
                </td>
                <td class="px-3 py-2">
                  <span class="px-2 py-1 rounded text-xs font-medium <?= $actionColor ?>">
                    <?= strtoupper($log['action']) ?>
                  </span>
                </td>
                <td class="px-3 py-2">
                  <span class="text-xs font-medium"><?= e($log['module']) ?></span>
                </td>
                <td class="px-3 py-2">
                  <div class="text-xs text-gray-600 max-w-xs truncate" title="<?= e($log['details'] ?? '') ?>">
                    <?= e($log['details'] ?? '-') ?>
                  </div>
                </td>
                <td class="px-3 py-2">
                  <span class="text-xs text-gray-500"><?= e($log['ip_address'] ?? '-') ?></span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top Usuários e Ações -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Top Usuários -->
    <div class="bg-white border rounded-lg p-4">
      <h3 class="text-md font-medium mb-3">👥 Usuários Mais Ativos (30 dias)</h3>
      <?php if (!empty($stats['top_users'])): ?>
        <div class="space-y-2">
          <?php foreach ($stats['top_users'] as $user): ?>
            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
              <div class="text-sm">
                <div class="font-medium"><?= e($user['name']) ?></div>
                <div class="text-xs text-gray-500"><?= e($user['email']) ?></div>
              </div>
              <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                <?= $user['total_actions'] ?> ações
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-sm text-gray-500">Nenhum dado disponível</p>
      <?php endif; ?>
    </div>

    <!-- Top Ações -->
    <div class="bg-white border rounded-lg p-4">
      <h3 class="text-md font-medium mb-3">📊 Ações Mais Comuns (30 dias)</h3>
      <?php if (!empty($stats['top_actions'])): ?>
        <div class="space-y-2">
          <?php foreach ($stats['top_actions'] as $action): ?>
            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
              <span class="text-sm font-medium"><?= strtoupper($action['action']) ?></span>
              <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">
                <?= $action['total'] ?> vezes
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-sm text-gray-500">Nenhum dado disponível</p>
      <?php endif; ?>
    </div>
  </div>
</section>
