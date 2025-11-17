<!-- Modal Nova NC -->
<div id="modalNovaNC" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
    <h2 class="text-xl font-bold mb-4">➕ Nova Não Conformidade</h2>
    <form id="formNovaNC" enctype="multipart/form-data">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Título *</label>
          <input type="text" name="titulo" required class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Descrição Detalhada *</label>
          <textarea name="descricao" required rows="4" class="w-full px-3 py-2 border rounded-lg"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Responsável pela Correção *</label>
          <select name="responsavel_id" required class="w-full px-3 py-2 border rounded-lg">
            <option value="">Selecione...</option>
            <?php foreach ($usuarios ?? [] as $u): ?>
              <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Evidências (Fotos, PDFs)</label>
          <input type="file" name="anexos[]" multiple accept="image/*,application/pdf,video/mp4" class="w-full px-3 py-2 border rounded-lg">
        </div>
      </div>
      <div class="flex gap-2 mt-6">
        <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Criar NC</button>
        <button type="button" onclick="fecharModalNovaNC()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Detalhes -->
<div id="modalDetalhes" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
    <div class="flex justify-between items-start mb-4">
      <h2 class="text-xl font-bold">Detalhes da NC</h2>
      <button onclick="fecharModalDetalhes()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
    </div>
    <div id="conteudoDetalhes"></div>
  </div>
</div>

<!-- Modal Registrar Ação -->
<div id="modalAcao" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
    <h2 class="text-xl font-bold mb-4">✍️ Registrar Ação Corretiva</h2>
    <form id="formAcao" enctype="multipart/form-data">
      <input type="hidden" name="nc_id" id="acaoNcId">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Ação Corretiva *</label>
          <textarea name="acao_corretiva" required rows="4" class="w-full px-3 py-2 border rounded-lg" placeholder="Descreva a ação que será tomada..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Evidências da Ação</label>
          <input type="file" name="anexos[]" multiple accept="image/*,application/pdf" class="w-full px-3 py-2 border rounded-lg">
        </div>
      </div>
      <div class="flex gap-2 mt-6">
        <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Registrar Ação</button>
        <button type="button" onclick="fecharModalAcao()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<style>
.tab-button {
  padding: 1rem 1.5rem;
  font-weight: 500;
  color: #6b7280;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}
.tab-button:hover { color: #ef4444; }
.tab-button.active {
  color: #ef4444;
  border-bottom-color: #ef4444;
}
.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  background: #fee2e2;
  color: #991b1b;
  border-radius: 9999px;
  font-size: 0.75rem;
  margin-left: 0.5rem;
}
</style>
