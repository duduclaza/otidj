<?php
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin']);
$isSuperAdmin = \App\Services\PermissionService::isSuperAdmin($_SESSION['user_id']);
?>

<div class="space-y-6">
  <!-- Header -->
  <div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
          ⚠️ Não Conformidades
        </h1>
        <p class="text-gray-600 mt-1">Gestão de NCs com apontamento e resolução</p>
      </div>
      <?php if ($isAdmin || $isSuperAdmin): ?>
      <button onclick="abrirModalNovaNC()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
        <span>➕</span>
        Nova NC
      </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tabs -->
  <div class="bg-white rounded-lg shadow-sm">
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex">
        <button onclick="mudarAba('pendentes')" id="tab-pendentes" class="tab-button active">
          Pendentes <span class="badge"><?= count($pendentes ?? []) ?></span>
        </button>
        <button onclick="mudarAba('em_andamento')" id="tab-em_andamento" class="tab-button">
          Em Andamento <span class="badge"><?= count($emAndamento ?? []) ?></span>
        </button>
        <button onclick="mudarAba('solucionadas')" id="tab-solucionadas" class="tab-button">
          Solucionadas <span class="badge"><?= count($solucionadas ?? []) ?></span>
        </button>
      </nav>
    </div>

    <div class="p-6">
      <!-- Aba Pendentes -->
      <div id="aba-pendentes" class="tab-content"><?php include 'partials/lista_ncs.php'; ?></div>
      
      <!-- Aba Em Andamento -->
      <div id="aba-em_andamento" class="tab-content hidden"><?php $ncs = $emAndamento ?? []; include 'partials/lista_ncs.php'; ?></div>
      
      <!-- Aba Solucionadas -->
      <div id="aba-solucionadas" class="tab-content hidden"><?php $ncs = $solucionadas ?? []; include 'partials/lista_ncs.php'; ?></div>
    </div>
  </div>
</div>

<?php include 'partials/modais.php'; ?>
<?php include 'partials/scripts.php'; ?>
