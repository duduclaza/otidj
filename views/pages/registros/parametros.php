<section class="space-y-6">
  <h1 class="text-2xl font-semibold">Parâmetros de Retornados</h1>
  
  <!-- Formulário de Cadastro -->
  <div class="bg-white border rounded-lg p-4">
    <h2 class="text-lg font-medium mb-3">Cadastrar Novo Parâmetro</h2>
    <form method="post" action="/registros/parametros/store" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
      <input type="text" name="nome" placeholder="Nome do parâmetro" class="border rounded px-3 py-2" required>
      <input type="number" name="faixa_min" placeholder="Faixa mínima (%)" step="0.1" min="0" max="100" class="border rounded px-3 py-2" required>
      <input type="number" name="faixa_max" placeholder="Faixa máxima (%) (opcional)" step="0.1" min="0" max="100" class="border rounded px-3 py-2">
      <textarea name="orientacao" placeholder="Orientação" class="border rounded px-3 py-2 sm:col-span-2 lg:col-span-3" required></textarea>
      <button class="px-4 py-2 rounded bg-primary-600 text-white hover:bg-primary-700">Salvar</button>
    </form>
  </div>

  <!-- Lista/Grid -->
  <div class="bg-white border rounded-lg">
    <div class="px-4 py-3 border-b">
      <h2 class="text-lg font-medium">Parâmetros Cadastrados</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nome</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Faixa</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Orientação</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($parametros)): ?>
            <tr>
              <td colspan="4" class="px-4 py-8 text-center text-gray-500">Nenhum parâmetro cadastrado</td>
            </tr>
          <?php else: ?>
            <?php foreach ($parametros as $p): ?>
              <tr>
                <td class="px-4 py-3">
                  <span class="edit-display-nome-<?= $p['id'] ?>"><?= e($p['nome']) ?></span>
                  <input type="text" class="edit-input-nome-<?= $p['id'] ?> border rounded px-2 py-1 hidden w-full" value="<?= e($p['nome']) ?>">
                </td>
                <td class="px-4 py-3">
                  <span class="edit-display-faixa-<?= $p['id'] ?>">
                    <?= number_format((float)$p['faixa_min'], 1, ',', '.') ?>% - <?= $p['faixa_max'] !== null ? number_format((float)$p['faixa_max'], 1, ',', '.').'%' : '∞' ?>
                  </span>
                  <div class="edit-input-faixa-<?= $p['id'] ?> hidden space-x-1">
                    <input type="number" step="0.1" min="0" max="100" class="edit-input-faixa-min-<?= $p['id'] ?> border rounded px-2 py-1 w-20" value="<?= $p['faixa_min'] ?>">
                    <span>-</span>
                    <input type="number" step="0.1" min="0" max="100" class="edit-input-faixa-max-<?= $p['id'] ?> border rounded px-2 py-1 w-20" value="<?= $p['faixa_max'] ?>">
                  </div>
                </td>
                <td class="px-4 py-3 max-w-xs">
                  <span class="edit-display-orientacao-<?= $p['id'] ?>"><?= e($p['orientacao']) ?></span>
                  <textarea class="edit-input-orientacao-<?= $p['id'] ?> border rounded px-2 py-1 hidden w-full"><?= e($p['orientacao']) ?></textarea>
                </td>
                <td class="px-4 py-3 text-right space-x-2">
                  <button onclick="editRow(<?= $p['id'] ?>)" class="edit-btn-<?= $p['id'] ?> text-blue-600 hover:text-blue-800 text-sm">Editar</button>
                  <button onclick="saveRow(<?= $p['id'] ?>)" class="save-btn-<?= $p['id'] ?> text-green-600 hover:text-green-800 text-sm hidden">Salvar</button>
                  <button onclick="cancelEdit(<?= $p['id'] ?>)" class="cancel-btn-<?= $p['id'] ?> text-gray-600 hover:text-gray-800 text-sm hidden">Cancelar</button>
                  <button onclick="deleteRow(<?= $p['id'] ?>)" class="text-red-600 hover:text-red-800 text-sm">Excluir</button>
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
  ['nome', 'faixa', 'orientacao'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.add('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.remove('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.add('hidden');
  document.querySelector('.save-btn-' + id).classList.remove('hidden');
  document.querySelector('.cancel-btn-' + id).classList.remove('hidden');
}

function cancelEdit(id) {
  ['nome', 'faixa', 'orientacao'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.remove('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.add('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.remove('hidden');
  document.querySelector('.save-btn-' + id).classList.add('hidden');
  document.querySelector('.cancel-btn-' + id).classList.add('hidden');
}

function saveRow(id) {
  const nome = document.querySelector('.edit-input-nome-' + id).value.trim();
  const faixa_min = parseFloat(document.querySelector('.edit-input-faixa-min-' + id).value);
  const faixa_max = document.querySelector('.edit-input-faixa-max-' + id).value;
  const orientacao = document.querySelector('.edit-input-orientacao-' + id).value.trim();
  
  if (!nome || !orientacao) { alert('Nome e orientação são obrigatórios'); return; }
  
  // Validar faixas
  if (isNaN(faixa_min) || faixa_min < 0 || faixa_min > 100) {
    alert('Faixa mínima deve ser um número entre 0 e 100');
    return;
  }
  
  if (faixa_max !== '' && faixa_max !== null) {
    const faixa_max_num = parseFloat(faixa_max);
    if (isNaN(faixa_max_num) || faixa_max_num < 0 || faixa_max_num > 100) {
      alert('Faixa máxima deve ser um número entre 0 e 100');
      return;
    }
    if (faixa_max_num <= faixa_min) {
      alert('Faixa máxima deve ser maior que a faixa mínima');
      return;
    }
  }
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/registros/parametros/update';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">' +
                   '<input type="hidden" name="nome" value="' + nome + '">' +
                   '<input type="hidden" name="faixa_min" value="' + faixa_min + '">' +
                   '<input type="hidden" name="faixa_max" value="' + faixa_max + '">' +
                   '<input type="hidden" name="orientacao" value="' + orientacao + '">';
  document.body.appendChild(form);
  form.submit();
}

function deleteRow(id) {
  if (!confirm('Tem certeza que deseja excluir este parâmetro?')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/registros/parametros/delete';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
  document.body.appendChild(form);
  form.submit();
}
</script>
