<section class="space-y-6">
  <h1 class="text-2xl font-semibold">Departamentos</h1>
  
  <!-- Formulário de Cadastro -->
  <div class="bg-white border rounded-lg p-4">
    <h2 class="text-lg font-medium mb-3">Cadastrar Novo Departamento</h2>
    <form method="post" action="/registros/departamentos/store" class="flex flex-col sm:flex-row gap-3 items-start">
      <input type="text" name="nome" placeholder="Nome do departamento" class="border rounded px-3 py-2 w-full sm:w-80" required>
      <button class="px-4 py-2 rounded bg-primary-600 text-white hover:bg-primary-700">Salvar</button>
    </form>
  </div>

  <!-- Lista/Grid -->
  <div class="bg-white border rounded-lg">
    <div class="px-4 py-3 border-b">
      <h2 class="text-lg font-medium">Departamentos Cadastrados</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nome</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($departamentos)): ?>
            <tr>
              <td colspan="2" class="px-4 py-8 text-center text-gray-500">Nenhum departamento cadastrado</td>
            </tr>
          <?php else: ?>
            <?php foreach ($departamentos as $d): ?>
              <tr>
                <td class="px-4 py-3">
                  <span class="edit-display-<?= $d['id'] ?>"><?= e($d['nome']) ?></span>
                  <input type="text" class="edit-input-<?= $d['id'] ?> border rounded px-2 py-1 hidden" value="<?= e($d['nome']) ?>">
                </td>
                <td class="px-4 py-3 text-right space-x-2">
                  <button onclick="editRow(<?= $d['id'] ?>)" class="edit-btn-<?= $d['id'] ?> text-blue-600 hover:text-blue-800 text-sm">Editar</button>
                  <button onclick="saveRow(<?= $d['id'] ?>)" class="save-btn-<?= $d['id'] ?> text-green-600 hover:text-green-800 text-sm hidden">Salvar</button>
                  <button onclick="cancelEdit(<?= $d['id'] ?>)" class="cancel-btn-<?= $d['id'] ?> text-gray-600 hover:text-gray-800 text-sm hidden">Cancelar</button>
                  <button onclick="deleteRow(<?= $d['id'] ?>)" class="text-red-600 hover:text-red-800 text-sm">Excluir</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
function editRow(id) {
  document.querySelector('.edit-display-' + id).classList.add('hidden');
  document.querySelector('.edit-input-' + id).classList.remove('hidden');
  document.querySelector('.edit-btn-' + id).classList.add('hidden');
  document.querySelector('.save-btn-' + id).classList.remove('hidden');
  document.querySelector('.cancel-btn-' + id).classList.remove('hidden');
}

function cancelEdit(id) {
  document.querySelector('.edit-display-' + id).classList.remove('hidden');
  document.querySelector('.edit-input-' + id).classList.add('hidden');
  document.querySelector('.edit-btn-' + id).classList.remove('hidden');
  document.querySelector('.save-btn-' + id).classList.add('hidden');
  document.querySelector('.cancel-btn-' + id).classList.add('hidden');
}

function saveRow(id) {
  const nome = document.querySelector('.edit-input-' + id).value.trim();
  if (!nome) { alert('Nome é obrigatório'); return; }
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/registros/departamentos/update';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '"><input type="hidden" name="nome" value="' + nome + '">';
  document.body.appendChild(form);
  form.submit();
}

function deleteRow(id) {
  if (!confirm('Tem certeza que deseja excluir este departamento?')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/registros/departamentos/delete';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
  document.body.appendChild(form);
  form.submit();
}
</script>
