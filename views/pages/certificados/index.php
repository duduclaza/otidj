<?php // Renderizada via views/layouts/main.php; não incluir header/footer aqui ?>

<div class="p-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-bold text-slate-800">Certificados</h1>
      <p class="text-slate-600 mt-1">Cadastro e gestão de certificados (PDF)</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Formulário -->
    <div class="lg:col-span-1">
      <div class="bg-white rounded-xl border border-slate-200 shadow p-5">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Novo Certificado</h2>

        <form id="formCertificado" class="space-y-4">
          <!-- Título existente -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Título existente</label>
            <select name="titulo_id" id="titulo_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
              <option value="">— Selecione um título salvo —</option>
              <?php foreach (($titulos ?? []) as $t): ?>
              <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['titulo']) ?></option>
              <?php endforeach; ?>
            </select>
            <p class="text-xs text-slate-500 mt-1">Ou informe um novo título abaixo</p>
          </div>

          <!-- Novo título -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Novo título</label>
            <input type="text" name="titulo_text" id="titulo_text" placeholder="Ex.: Certificado ISO 9001 - Fornecedor X" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- Data registro -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Data de registro</label>
            <input type="date" name="data_registro" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- Arquivo -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Anexo (PDF)</label>
            <input type="file" name="arquivo" accept="application/pdf" required class="w-full px-3 py-2 border border-slate-300 rounded-lg">
            <p class="text-xs text-slate-500 mt-1">Apenas PDF. Tamanho recomendado até 10MB.</p>
          </div>

          <div class="pt-2">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Salvar Certificado</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Grid -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-xl border border-slate-200 shadow">
        <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-slate-800">Registros</h2>
          <span class="text-xs text-slate-500">Mostrando até 100 mais recentes</span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-2 text-left font-semibold text-slate-700">Título</th>
                <th class="px-4 py-2 text-left font-semibold text-slate-700">Data</th>
                <th class="px-4 py-2 text-left font-semibold text-slate-700">Arquivo</th>
                <th class="px-4 py-2 text-right font-semibold text-slate-700">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php if (!empty($certificados)): foreach ($certificados as $c): ?>
              <tr>
                <td class="px-4 py-2 text-slate-800">
                  <?= htmlspecialchars($c['titulo_text']) ?>
                </td>
                <td class="px-4 py-2 text-slate-600">
                  <?= htmlspecialchars(date('d/m/Y', strtotime($c['data_registro']))) ?>
                </td>
                <td class="px-4 py-2 text-slate-600">
                  <?= htmlspecialchars($c['nome_arquivo']) ?>
                </td>
                <td class="px-4 py-2 text-right">
                  <a href="/certificados/download/<?= (int)$c['id'] ?>" class="inline-flex items-center px-3 py-1.5 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200">Download</a>
                  <button onclick="deleteCertificado(<?= (int)$c['id'] ?>)" class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-100 text-red-700 hover:bg-red-200 ml-2">Excluir</button>
                </td>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td class="px-4 py-6 text-center text-slate-500" colspan="4">Nenhum certificado cadastrado</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Submit do formulário
const form = document.getElementById('formCertificado');
form?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(form);
  // Regras: se selecionar título existente, limpar novo título
  const tituloId = fd.get('titulo_id');
  const tituloText = fd.get('titulo_text');
  if (tituloId && String(tituloId).trim() !== '') {
    fd.set('titulo_text', '');
  }
  try {
    const res = await fetch('/certificados/store', { method: 'POST', body: fd });
    const result = await res.json();
    if (result.success) {
      alert('✅ ' + result.message);
      location.reload();
    } else {
      alert('❌ ' + (result.message || 'Erro ao salvar'));
    }
  } catch (err) {
    alert('❌ Erro ao salvar');
  }
});

// Excluir certificado
async function deleteCertificado(id) {
  if (!confirm('Tem certeza que deseja excluir este certificado?')) return;
  try {
    const fd = new FormData();
    fd.append('id', id);
    const res = await fetch('/certificados/delete', { method: 'POST', body: fd });
    const result = await res.json();
    if (result.success) {
      alert('✅ ' + result.message);
      location.reload();
    } else {
      alert('❌ ' + (result.message || 'Erro ao excluir'));
    }
  } catch (err) {
    alert('❌ Erro ao excluir');
  }
}
</script>
