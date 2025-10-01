<?php
$maquinas = $maquinas ?? [];
$isAdmin = $_SESSION['user_role'] === 'admin';
?>

<section class="mb-8">
  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">🖨️ Cadastro de Máquinas</h1>
      <p class="text-gray-600 mt-1">Gerenciamento de máquinas cadastradas</p>
    </div>
    <button onclick="openFormModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
      + Nova Máquina
    </button>
  </div>

  <!-- Formulário Inline -->
  <div id="formContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-100" id="formTitle">Nova Máquina</h2>
      <button onclick="closeFormModal()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="maquinaForm" class="space-y-4">
      <input type="hidden" name="id" id="maquinaId">
      
      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Modelo *</label>
        <input type="text" name="modelo" id="modelo" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-200 mb-1">Código de Referência *</label>
        <input type="text" name="cod_referencia" id="codReferencia" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-200 focus:ring-2 focus:ring-blue-500">
      </div>

      <div class="flex justify-end space-x-3 pt-4">
        <button type="button" onclick="closeFormModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
          Cancelar
        </button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          💾 Salvar
        </button>
      </div>
    </form>
  </div>

  <!-- Grid -->
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modelo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código Referência</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criado por</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($maquinas as $maquina): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= $maquina['id'] ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?= e($maquina['modelo']) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= e($maquina['cod_referencia']) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= e($maquina['criador_nome'] ?? 'N/A') ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= date('d/m/Y', strtotime($maquina['created_at'])) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
              <button onclick='editMaquina(<?= json_encode($maquina) ?>)' class="text-blue-600 hover:text-blue-800">
                ✏️ Editar
              </button>
              <button onclick="deleteMaquina(<?= $maquina['id'] ?>)" class="text-red-600 hover:text-red-800">
                🗑️ Excluir
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
let isEditing = false;

function openFormModal() {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Nova Máquina';
  document.getElementById('maquinaForm').reset();
  document.getElementById('maquinaId').value = '';
  isEditing = false;
}

function closeFormModal() {
  document.getElementById('formContainer').classList.add('hidden');
  document.getElementById('maquinaForm').reset();
}

function editMaquina(maquina) {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Editar Máquina';
  document.getElementById('maquinaId').value = maquina.id;
  document.getElementById('modelo').value = maquina.modelo;
  document.getElementById('codReferencia').value = maquina.cod_referencia;
  isEditing = true;
}

async function deleteMaquina(id) {
  if (!confirm('Tem certeza que deseja excluir esta máquina?')) return;
  
  try {
    const response = await fetch('/cadastro-maquinas/delete', {
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
    alert('Erro ao excluir máquina');
  }
}

document.getElementById('maquinaForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const url = isEditing ? '/cadastro-maquinas/update' : '/cadastro-maquinas/store';
  
  try {
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    alert(result.message);
    
    if (result.success && result.redirect) {
      window.location.href = result.redirect;
    }
  } catch (error) {
    alert('Erro ao salvar máquina');
  }
});
</script>
