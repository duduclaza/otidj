<?php
// $ncs deve ser passado pela view pai
$ncsExibir = $ncs ?? $pendentes ?? [];
?>

<?php if (empty($ncsExibir)): ?>
  <div class="text-center py-12">
    <div class="text-6xl mb-4">📋</div>
    <p class="text-gray-500">Nenhuma NC nesta categoria</p>
  </div>
<?php else: ?>
  <div class="space-y-4">
    <?php foreach ($ncsExibir as $nc): ?>
      <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($nc['titulo']) ?></h3>
              <span class="px-2 py-1 text-xs rounded-full <?= 
                $nc['status'] === 'pendente' ? 'bg-red-100 text-red-700' : 
                ($nc['status'] === 'em_andamento' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') 
              ?>">
                <?= $nc['status'] === 'pendente' ? 'Pendente' : ($nc['status'] === 'em_andamento' ? 'Em Andamento' : 'Solucionada') ?>
              </span>
            </div>
            <p class="text-sm text-gray-600 mb-3"><?= nl2br(htmlspecialchars(substr($nc['descricao'], 0, 150))) ?><?= strlen($nc['descricao']) > 150 ? '...' : '' ?></p>
            <div class="flex items-center gap-4 text-xs text-gray-500">
              <span>🆔 #<?= $nc['id'] ?></span>
              <span>👤 Por: <?= htmlspecialchars($nc['criador_nome']) ?></span>
              <span>👨‍💼 Responsável: <?= htmlspecialchars($nc['responsavel_nome']) ?></span>
              <span>📅 <?= date('d/m/Y H:i', strtotime($nc['created_at'])) ?></span>
              <?php if ($nc['total_anexos'] > 0): ?>
                <span>📎 <?= $nc['total_anexos'] ?> anexo(s)</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="flex gap-2 ml-4">
            <button onclick="verDetalhes(<?= $nc['id'] ?>)" class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
              Ver Detalhes
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
