<!-- Modal Nova NC -->
<div id="modalNovaNC" class="modal-overlay hidden">
  <div class="modal-content">
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
            <?php 
            // Debug temporário
            if (empty($usuarios)) {
                echo '<option value="" disabled>⚠️ Nenhum usuário encontrado</option>';
            } else {
                foreach ($usuarios as $u): 
            ?>
              <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
            <?php 
                endforeach;
            }
            ?>
          </select>
          <?php if (empty($usuarios)): ?>
            <p class="text-xs text-red-600 mt-1">⚠️ Execute: database/verificar_usuarios.sql</p>
          <?php endif; ?>
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
<div id="modalDetalhes" class="modal-overlay hidden">
  <div class="modal-content modal-content-large">
    <div class="flex justify-between items-start mb-4">
      <h2 class="text-xl font-bold">Detalhes da NC</h2>
      <button onclick="fecharModalDetalhes()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
    </div>
    <div id="conteudoDetalhes"></div>
  </div>
</div>

<!-- Modal Registrar Ação -->
<div id="modalAcao" class="modal-overlay hidden">
  <div class="modal-content">
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
/* Tabs */
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

/* Modais - Sair do iframe e centralizar */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999 !important;
  padding: 1rem;
  overflow-y: auto;
}

.modal-overlay.hidden {
  display: none;
}

.modal-content {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  max-width: 42rem;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  animation: modalFadeIn 0.2s ease-out;
  position: relative;
  margin: auto;
}

.modal-content-large {
  max-width: 56rem;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Scrollbar do modal */
.modal-content::-webkit-scrollbar {
  width: 8px;
}

.modal-content::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Fechar modal ao clicar fora */
.modal-overlay:not(.no-close-outside) {
  cursor: pointer;
}

.modal-overlay .modal-content {
  cursor: default;
}
</style>
