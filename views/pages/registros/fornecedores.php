<section class="space-y-6">
  <h1 class="text-2xl font-semibold">Fornecedores</h1>
  
  <!-- Formulário de Cadastro -->
  <div class="bg-white border rounded-lg p-4">
    <h2 class="text-lg font-medium mb-3">Cadastrar Novo Fornecedor</h2>
    <form method="post" action="/registros/fornecedores/store" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
      <input type="text" name="nome" placeholder="Nome do fornecedor (obrigatório)" class="border rounded px-3 py-2" required>
      <input type="text" name="contato" placeholder="Contato (link/email/telefone)" class="border rounded px-3 py-2">
      <input type="text" name="rma" placeholder="RMA (link/email/telefone)" class="border rounded px-3 py-2">
      <button class="px-4 py-2 rounded bg-primary-600 text-white hover:bg-primary-700">Salvar</button>
    </form>
  </div>

  <!-- Lista/Grid -->
  <div class="bg-white border rounded-lg">
    <div class="px-4 py-3 border-b">
      <h2 class="text-lg font-medium">Fornecedores Cadastrados</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nome</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Contato</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">RMA</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($fornecedores)): ?>
            <tr>
              <td colspan="4" class="px-4 py-8 text-center text-gray-500">Nenhum fornecedor cadastrado</td>
            </tr>
          <?php else: ?>
            <?php foreach ($fornecedores as $f): ?>
              <tr>
                <td class="px-4 py-3">
                  <span class="edit-display-nome-<?= $f['id'] ?>"><?= e($f['nome']) ?></span>
                  <input type="text" class="edit-input-nome-<?= $f['id'] ?> border rounded px-2 py-1 hidden w-full" value="<?= e($f['nome']) ?>">
                </td>
                <td class="px-4 py-3">
                  <span class="edit-display-contato-<?= $f['id'] ?>"><?= e($f['contato']) ?></span>
                  <input type="text" class="edit-input-contato-<?= $f['id'] ?> border rounded px-2 py-1 hidden w-full" value="<?= e($f['contato']) ?>">
                </td>
                <td class="px-4 py-3">
                  <span class="edit-display-rma-<?= $f['id'] ?>"><?= e($f['rma']) ?></span>
                  <input type="text" class="edit-input-rma-<?= $f['id'] ?> border rounded px-2 py-1 hidden w-full" value="<?= e($f['rma']) ?>">
                </td>
                <td class="px-4 py-3 text-right space-x-2">
                  <button onclick="editRow(<?= $f['id'] ?>)" class="edit-btn-<?= $f['id'] ?> text-blue-600 hover:text-blue-800 text-sm">Editar</button>
                  <button onclick="saveRow(<?= $f['id'] ?>)" class="save-btn-<?= $f['id'] ?> text-green-600 hover:text-green-800 text-sm hidden">Salvar</button>
                  <button onclick="cancelEdit(<?= $f['id'] ?>)" class="cancel-btn-<?= $f['id'] ?> text-gray-600 hover:text-gray-800 text-sm hidden">Cancelar</button>
                  <button onclick="deleteRow(<?= $f['id'] ?>)" class="text-red-600 hover:text-red-800 text-sm">Excluir</button>
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
  ['nome', 'contato', 'rma'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.add('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.remove('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.add('hidden');
  document.querySelector('.save-btn-' + id).classList.remove('hidden');
  document.querySelector('.cancel-btn-' + id).classList.remove('hidden');
}

function cancelEdit(id) {
  ['nome', 'contato', 'rma'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.remove('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.add('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.remove('hidden');
  document.querySelector('.save-btn-' + id).classList.add('hidden');
  document.querySelector('.cancel-btn-' + id).classList.add('hidden');
}

function saveRow(id) {
  const nome = document.querySelector('.edit-input-nome-' + id).value.trim();
  const contato = document.querySelector('.edit-input-contato-' + id).value.trim();
  const rma = document.querySelector('.edit-input-rma-' + id).value.trim();
  
  if (!nome) { alert('Nome é obrigatório'); return; }
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/registros/fornecedores/update';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">' +
                   '<input type="hidden" name="nome" value="' + nome + '">' +
                   '<input type="hidden" name="contato" value="' + contato + '">' +
                   '<input type="hidden" name="rma" value="' + rma + '">';
  document.body.appendChild(form);
  form.submit();
}

function deleteRow(id) {
  if (!confirm('Tem certeza que deseja excluir este fornecedor?')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/registros/fornecedores/delete';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
  document.body.appendChild(form);
  form.submit();
}
</script>
