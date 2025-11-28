<!-- Modal Nova NC -->
<div id="modalNovaNC" class="modal-overlay hidden">
  <div class="modal-content">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-900">Nova Não Conformidade</h2>
          <p class="text-sm text-gray-500">Registre um novo apontamento</p>
        </div>
      </div>
      <button type="button" onclick="fecharModalNovaNC()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="formNovaNC" enctype="multipart/form-data" class="mt-6">
      <div class="space-y-5">
        <!-- Título -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
              </svg>
              Título da NC
            </span>
          </label>
          <input type="text" name="titulo" required 
                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all placeholder-gray-400"
                 placeholder="Ex: Produto com defeito de fabricação">
        </div>
        
        <!-- Descrição -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
              </svg>
              Descrição Detalhada
            </span>
          </label>
          <textarea name="descricao" required rows="4" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all placeholder-gray-400 resize-none"
                    placeholder="Descreva o problema encontrado com o máximo de detalhes possível..."></textarea>
        </div>
        
        <!-- Responsável -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              Responsável pela Correção
            </span>
          </label>
          <select name="responsavel_id" required 
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all bg-white">
            <option value="">Selecione o responsável...</option>
            <?php 
            if (empty($usuarios)) {
                echo '<option value="" disabled>⚠️ Nenhum usuário encontrado</option>';
            } else {
                foreach ($usuarios as $u): 
            ?>
              <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
            <?php 
                endforeach;
            }
            ?>
          </select>
        </div>
        
        <!-- Evidências -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
              </svg>
              Evidências (opcional)
            </span>
          </label>
          <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-red-400 transition-colors cursor-pointer" onclick="document.getElementById('inputAnexosNC').click()">
            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="text-sm text-gray-600">Clique para anexar fotos, PDFs ou vídeos</p>
            <p class="text-xs text-gray-400 mt-1">Arraste arquivos ou clique para selecionar</p>
          </div>
          <input type="file" id="inputAnexosNC" name="anexos[]" multiple accept="image/*,application/pdf,video/mp4" class="hidden">
        </div>
      </div>
      
      <!-- Botões -->
      <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
        <button type="submit" 
                class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl font-semibold hover:from-red-700 hover:to-red-800 transition-all shadow-lg shadow-red-500/25 flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Criar NC
        </button>
        <button type="button" onclick="fecharModalNovaNC()" 
                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all">
          Cancelar
        </button>
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
