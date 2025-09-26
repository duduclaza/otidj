<?php
// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Garantias</h1>
            <p class="text-gray-600 mt-1">Controle de garantias de produtos</p>
        </div>
        <button id="toggleGarantiaFormBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Nova Garantia</span>
        </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="filtroStatus" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">Todos os status</option>
                    <option value="Em andamento">Em andamento</option>
                    <option value="Aguardando Fornecedor">Aguardando Fornecedor</option>
                    <option value="Aguardando Recebimento">Aguardando Recebimento</option>
                    <option value="Finalizado">Finalizado</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origem</label>
                <select id="filtroOrigem" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">Todas as origens</option>
                    <option value="Amostragem">Amostragem</option>
                    <option value="Homologação">Homologação</option>
                    <option value="Em Campo">Em Campo</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
                <select id="filtroFornecedor" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">Todos os fornecedores</option>
                </select>
            </div>
            <div class="flex items-end">
                <button id="btnLimparFiltros" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    Limpar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Formulário Inline de Nova Garantia -->
    <div id="garantiaFormContainer" class="hidden bg-gray-800 border border-gray-600 rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 id="garantiaFormTitle" class="text-xl font-semibold text-white">Nova Garantia</h2>
            <button onclick="cancelGarantiaForm()" class="text-gray-400 hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="garantiaForm" class="space-y-6" enctype="multipart/form-data">
            <input type="hidden" name="id" id="garantiaId">
            
            <!-- Informações Básicas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Fornecedor *</label>
                    <select name="fornecedor_id" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione um fornecedor</option>
                        <?php if (isset($fornecedores)): ?>
                            <?php foreach ($fornecedores as $fornecedor): ?>
                                <option value="<?= $fornecedor['id'] ?>"><?= htmlspecialchars($fornecedor['nome']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Origem da Garantia *</label>
                    <select name="origem_garantia" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione a origem</option>
                        <option value="Amostragem">Amostragem</option>
                        <option value="Homologação">Homologação</option>
                        <option value="Em Campo">Em Campo</option>
                    </select>
                </div>
            </div>

            <!-- Números de NF -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Número NF Compras</label>
                    <input type="text" name="numero_nf_compras" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Número NF Remessa Simples</label>
                    <input type="text" name="numero_nf_remessa_simples" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Número NF Remessa Devolução</label>
                    <input type="text" name="numero_nf_remessa_devolucao" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Campos Opcionais -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Número de Série</label>
                    <input type="text" name="numero_serie" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Número do Lote</label>
                    <input type="text" name="numero_lote" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Número Ticket/OS</label>
                    <input type="text" name="numero_ticket_os" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Anexos das Notas Fiscais -->
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-medium text-white mb-4">📎 Anexos das Notas Fiscais</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">NF Compras (PDF)</label>
                        <input type="file" name="anexo_nf_compras" accept=".pdf" onchange="validateFileUpload(this, 'nf_compras')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">Apenas PDF até 10MB</p>
                        <div id="preview_nf_compras" class="mt-2"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">NF Remessa Simples (PDF)</label>
                        <input type="file" name="anexo_nf_remessa_simples" accept=".pdf" onchange="validateFileUpload(this, 'nf_remessa_simples')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">Apenas PDF até 10MB</p>
                        <div id="preview_nf_remessa_simples" class="mt-2"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">NF Remessa Devolução (PDF)</label>
                        <input type="file" name="anexo_nf_remessa_devolucao" accept=".pdf" onchange="validateFileUpload(this, 'nf_remessa_devolucao')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">Apenas PDF até 10MB</p>
                        <div id="preview_nf_remessa_devolucao" class="mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- Anexos dos Laudos -->
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-medium text-white mb-4">📋 Anexos dos Laudos</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Laudo Técnico (PDF/DOC)</label>
                        <input type="file" name="anexo_laudo_tecnico" accept=".pdf,.doc,.docx" onchange="validateFileUpload(this, 'laudo_tecnico')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">PDF, DOC ou DOCX até 10MB</p>
                        <div id="preview_laudo_tecnico" class="mt-2"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Evidências (Imagens)</label>
                        <input type="file" name="anexo_evidencias[]" accept="image/*" multiple onchange="validateImageUpload(this, 'evidencias')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">Imagens até 5MB cada (máx. 10 arquivos)</p>
                        <div id="preview_evidencias" class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                    </div>
                </div>
            </div>

            <!-- Status e Observação -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Em andamento">Em andamento</option>
                        <option value="Aguardando Fornecedor">Aguardando Fornecedor</option>
                        <option value="Aguardando Recebimento">Aguardando Recebimento</option>
                        <option value="Aguardando Item Chegar ao laboratório">Aguardando Item Chegar ao laboratório</option>
                        <option value="Aguardando Emissão de NF">Aguardando Emissão de NF</option>
                        <option value="Aguardando Despache">Aguardando Despache</option>
                        <option value="Aguardando Testes">Aguardando Testes</option>
                        <option value="Finalizado">Finalizado</option>
                        <option value="Garantia Expirada">Garantia Expirada</option>
                        <option value="Garantia não coberta">Garantia não coberta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Observação</label>
                    <textarea name="observacao" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Observações sobre a garantia..."></textarea>
                </div>
            </div>

            <!-- Itens da Garantia -->
            <div class="bg-gray-700 rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-white">📦 Itens da Garantia</h3>
                    <button type="button" onclick="adicionarItem()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                        + Adicionar Item
                    </button>
                </div>
                <div id="itensContainer" class="space-y-4">
                    <!-- Itens adicionados dinamicamente -->
                </div>
                <div class="mt-4 p-4 bg-gray-600 rounded-lg">
                    <div class="flex justify-between text-sm text-gray-300">
                        <span>Total de Itens: <span id="totalItens" class="font-medium text-white">0</span></span>
                        <span>Valor Total: R$ <span id="valorTotal" class="font-medium text-white">0,00</span></span>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-600">
                <button type="button" onclick="cancelGarantiaForm()" class="px-4 py-2 border border-gray-500 rounded-md text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Cancelar
                </button>
                <button type="submit" id="submitGarantiaBtn" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Salvar Garantia
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Garantias -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origem</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Itens</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaGarantias" class="bg-white divide-y divide-gray-200">
                    <!-- Dados carregados via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" class="text-center py-8 hidden">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Carregando...</p>
    </div>
</section>

<!-- Modal Nova/Editar Garantia (mantido para compatibilidade) -->
<div id="modalGarantia" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Nova Garantia</h3>
            </div>
            
            <form id="formGarantia" class="p-6 space-y-6">
                <input type="hidden" id="garantiaId" name="garantia_id">
                
                <!-- Informações Básicas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor *</label>
                        <select id="fornecedorId" name="fornecedor_id" required class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Selecione um fornecedor</option>
                            <?php foreach ($fornecedores as $fornecedor): ?>
                                <option value="<?= $fornecedor['id'] ?>"><?= htmlspecialchars($fornecedor['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Origem da Garantia *</label>
                        <select id="origemGarantia" name="origem_garantia" required class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Selecione a origem</option>
                            <option value="Amostragem">Amostragem</option>
                            <option value="Homologação">Homologação</option>
                            <option value="Em Campo">Em Campo</option>
                        </select>
                    </div>
                </div>

                <!-- Números de NF -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número NF Compras</label>
                        <input type="text" id="numeroNfCompras" name="numero_nf_compras" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número NF Remessa Simples</label>
                        <input type="text" id="numeroNfRemessaSimples" name="numero_nf_remessa_simples" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número NF Remessa Devolução</label>
                        <input type="text" id="numeroNfRemessaDevolucao" name="numero_nf_remessa_devolucao" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                </div>

                <!-- Campos Opcionais -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Série</label>
                        <input type="text" id="numeroSerie" name="numero_serie" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número do Lote</label>
                        <input type="text" id="numeroLote" name="numero_lote" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número Ticket/OS</label>
                        <input type="text" id="numeroTicketOs" name="numero_ticket_os" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                </div>

                <!-- Status e Observação -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="Em andamento">Em andamento</option>
                            <option value="Aguardando Fornecedor">Aguardando Fornecedor</option>
                            <option value="Aguardando Recebimento">Aguardando Recebimento</option>
                            <option value="Aguardando Item Chegar ao laboratório">Aguardando Item Chegar ao laboratório</option>
                            <option value="Aguardando Emissão de NF">Aguardando Emissão de NF</option>
                            <option value="Aguardando Despache">Aguardando Despache</option>
                            <option value="Aguardando Testes">Aguardando Testes</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Garantia Expirada">Garantia Expirada</option>
                            <option value="Garantia não coberta">Garantia não coberta</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observação</label>
                        <textarea id="observacao" name="observacao" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                        <p id="observacaoHelp" class="text-sm text-gray-500 mt-1 hidden">Observação obrigatória para este status</p>
                    </div>
                </div>

                <!-- Itens -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Itens da Garantia</h4>
                        <button type="button" id="btnAdicionarItem" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                            + Adicionar Item
                        </button>
                    </div>
                    <div id="itensContainer" class="space-y-4">
                        <!-- Itens adicionados dinamicamente -->
                    </div>
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex justify-between text-sm">
                            <span>Total de Itens: <span id="totalItens" class="font-medium">0</span></span>
                            <span>Valor Total: R$ <span id="valorTotal" class="font-medium">0,00</span></span>
                        </div>
                    </div>
                </div>
            </form>

            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" id="btnCancelar" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md">
                    Cancelar
                </button>
                <button type="submit" form="formGarantia" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variáveis globais
let garantias = [];
let fornecedores = <?= json_encode($fornecedores) ?>;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarGarantias();
    carregarFornecedoresSelect();
    configurarEventos();
    adicionarPrimeiroItem();
});

// Configurar eventos
function configurarEventos() {
    const toggleBtn = document.getElementById('toggleGarantiaFormBtn');
    if (toggleBtn) toggleBtn.addEventListener('click', toggleGarantiaForm);
    
    const form = document.getElementById('garantiaForm');
    if (form) form.addEventListener('submit', submitGarantia);
    
    // Filtros
    document.getElementById('filtroStatus').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroOrigem').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroFornecedor').addEventListener('change', aplicarFiltros);
    document.getElementById('btnLimparFiltros').addEventListener('click', limparFiltros);
}

// Toggle do formulário inline
function toggleGarantiaForm() {
    const container = document.getElementById('garantiaFormContainer');
    const btn = document.getElementById('toggleGarantiaFormBtn');
    
    if (container.classList.contains('hidden')) {
        // Mostrar formulário
        container.classList.remove('hidden');
        btn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>Cancelar</span>
        `;
        resetGarantiaForm();
        adicionarPrimeiroItem();
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        // Ocultar formulário
        cancelGarantiaForm();
    }
}

function cancelGarantiaForm() {
    const container = document.getElementById('garantiaFormContainer');
    const btn = document.getElementById('toggleGarantiaFormBtn');
    const form = document.getElementById('garantiaForm');
    
    container.classList.add('hidden');
    form.reset();
    btn.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nova Garantia</span>
    `;
    resetGarantiaForm();
}

function resetGarantiaForm() {
    document.getElementById('garantiaForm').reset();
    document.getElementById('itensContainer').innerHTML = '';
    clearAllPreviews();
    atualizarTotais();
}

// Funções de validação de upload
function validateFileUpload(input, previewId) {
    const file = input.files[0];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (!file) return;
    
    // Validar tipo
    const allowedTypes = {
        'nf_compras': ['application/pdf'],
        'nf_remessa_simples': ['application/pdf'], 
        'nf_remessa_devolucao': ['application/pdf'],
        'laudo_tecnico': ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    };
    
    if (allowedTypes[previewId] && !allowedTypes[previewId].includes(file.type)) {
        alert('Tipo de arquivo não permitido para este campo.');
        input.value = '';
        return;
    }
    
    // Validar tamanho
    if (file.size > maxSize) {
        alert('Arquivo muito grande. Máximo 10MB.');
        input.value = '';
        return;
    }
    
    showFilePreview(file, previewId);
}

function validateImageUpload(input, previewId) {
    const files = Array.from(input.files);
    const maxSize = 5 * 1024 * 1024; // 5MB
    const maxFiles = 10;
    
    if (files.length > maxFiles) {
        alert(`Máximo ${maxFiles} imagens permitidas.`);
        input.value = '';
        return;
    }
    
    for (const file of files) {
        if (!file.type.startsWith('image/')) {
            alert('Apenas imagens são permitidas.');
            input.value = '';
            return;
        }
        
        if (file.size > maxSize) {
            alert(`Imagem muito grande: ${file.name}. Máximo 5MB.`);
            input.value = '';
            return;
        }
    }
    
    showImagePreviews(files, previewId);
}

function showFilePreview(file, previewId) {
    const container = document.getElementById(`preview_${previewId}`);
    if (!container) return;
    
    const fileIcon = file.type === 'application/pdf' ? '📄' : '📝';
    
    container.innerHTML = `
        <div class="flex items-center p-2 bg-gray-600 border border-gray-500 rounded-lg">
            <span class="text-2xl mr-2">${fileIcon}</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">${file.name}</p>
                <p class="text-xs text-gray-300">${(file.size/1048576).toFixed(2)} MB</p>
            </div>
            <button type="button" onclick="clearPreview('${previewId}')" class="text-red-400 hover:text-red-300 ml-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    `;
}

function showImagePreviews(files, previewId) {
    const container = document.getElementById(`preview_${previewId}`);
    if (!container) return;
    
    container.innerHTML = '';
    
    files.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index}" class="w-20 h-20 object-cover rounded border border-gray-500">
                <button type="button" onclick="removeImagePreview(this)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                    ×
                </button>
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function clearPreview(previewId) {
    const container = document.getElementById(`preview_${previewId}`);
    const input = document.querySelector(`input[onchange*="${previewId}"]`);
    
    if (container) container.innerHTML = '';
    if (input) input.value = '';
}

function removeImagePreview(button) {
    button.parentElement.remove();
    // Note: This doesn't remove from the file input, just the preview
}

function clearAllPreviews() {
    const previewIds = ['nf_compras', 'nf_remessa_simples', 'nf_remessa_devolucao', 'laudo_tecnico', 'evidencias'];
    previewIds.forEach(id => clearPreview(id));
}

// Função de submit do formulário
function submitGarantia(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    // Validações
    if (!formData.get('fornecedor_id')) {
        alert('Selecione um fornecedor');
        return;
    }
    
    if (!formData.get('origem_garantia')) {
        alert('Selecione a origem da garantia');
        return;
    }
    
    // Verificar se há pelo menos um item válido
    const itens = document.querySelectorAll('#itensContainer .item-garantia');
    if (itens.length === 0) {
        alert('Adicione pelo menos um item à garantia');
        return;
    }
    
    // Verificar se pelo menos um item tem todos os campos preenchidos
    let itemValido = false;
    itens.forEach(item => {
        const descricao = item.querySelector('input[name="item_descricao"]').value.trim();
        const quantidade = item.querySelector('input[name="item_quantidade"]').value;
        const valor = item.querySelector('input[name="item_valor"]').value;
        
        if (descricao && quantidade && valor && quantidade > 0 && valor > 0) {
            itemValido = true;
        }
    });
    
    if (!itemValido) {
        alert('Preencha todos os campos de pelo menos um item (Descrição, Quantidade e Valor)');
        return;
    }
    
    // Coletar dados dos itens válidos
    const itensData = [];
    itens.forEach((item, index) => {
        const descricao = item.querySelector('input[name="item_descricao"]').value.trim();
        const quantidade = item.querySelector('input[name="item_quantidade"]').value;
        const valor = item.querySelector('input[name="item_valor"]').value;
        
        if (descricao && quantidade && valor && quantidade > 0 && valor > 0) {
            itensData.push({
                descricao: descricao,
                quantidade: parseInt(quantidade),
                valor_unitario: parseFloat(valor)
            });
        }
    });
    
    // Adicionar itens ao FormData
    formData.append('itens', JSON.stringify(itensData));
    
    // Debug FormData
    console.log('Dados do formulário:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    // Enviar dados
    fetch('/garantias', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Garantia registrada com sucesso!');
            cancelGarantiaForm();
            carregarGarantias();
        } else {
            alert('Erro: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão: ' + error.message);
    });
}

// Funções para gerenciar itens
function adicionarItem() {
    const container = document.getElementById('itensContainer');
    const itemIndex = container.children.length;
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'item-garantia bg-gray-600 p-4 rounded-lg border border-gray-500';
    itemDiv.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <h4 class="text-white font-medium">Item ${itemIndex + 1}</h4>
            <button type="button" onclick="removerItem(this)" class="text-red-400 hover:text-red-300">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-white mb-1">Descrição *</label>
                <input type="text" name="item_descricao" required class="w-full bg-gray-700 border border-gray-500 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Descrição do item">
            </div>
            <div>
                <label class="block text-sm font-medium text-white mb-1">Quantidade *</label>
                <input type="number" name="item_quantidade" min="1" required onchange="atualizarTotais()" class="w-full bg-gray-700 border border-gray-500 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="1">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-white mb-1">Valor Unitário (R$) *</label>
                <input type="number" name="item_valor" step="0.01" min="0" required onchange="atualizarTotais()" class="w-full bg-gray-700 border border-gray-500 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-white mb-1">Valor Total</label>
                <input type="text" class="item-valor-total w-full bg-gray-600 border border-gray-500 text-gray-300 rounded px-3 py-2" readonly placeholder="R$ 0,00">
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
    atualizarTotais();
}

function removerItem(button) {
    button.closest('.item-garantia').remove();
    atualizarTotais();
    
    // Renumerar itens
    const itens = document.querySelectorAll('#itensContainer .item-garantia');
    itens.forEach((item, index) => {
        item.querySelector('h4').textContent = `Item ${index + 1}`;
    });
}

function adicionarPrimeiroItem() {
    const container = document.getElementById('itensContainer');
    if (container && container.children.length === 0) {
        adicionarItem();
    }
}

function atualizarTotais() {
    const itens = document.querySelectorAll('#itensContainer .item-garantia');
    let totalItens = 0;
    let valorTotal = 0;
    
    itens.forEach(item => {
        const quantidade = parseFloat(item.querySelector('input[name="item_quantidade"]').value) || 0;
        const valorUnitario = parseFloat(item.querySelector('input[name="item_valor"]').value) || 0;
        const valorItemTotal = quantidade * valorUnitario;
        
        // Atualizar valor total do item
        const valorTotalInput = item.querySelector('.item-valor-total');
        if (valorTotalInput) {
            valorTotalInput.value = `R$ ${valorItemTotal.toFixed(2).replace('.', ',')}`;
        }
        
        totalItens += quantidade;
        valorTotal += valorItemTotal;
    });
    
    // Atualizar totais gerais
    const totalItensSpan = document.getElementById('totalItens');
    const valorTotalSpan = document.getElementById('valorTotal');
    
    if (totalItensSpan) totalItensSpan.textContent = totalItens;
    if (valorTotalSpan) valorTotalSpan.textContent = valorTotal.toFixed(2).replace('.', ',');
}

// Carregar garantias
async function carregarGarantias() {
    try {
        document.getElementById('loading').classList.remove('hidden');
        const response = await fetch('/garantias/list');
        const result = await response.json();
        
        if (result.success) {
            garantias = result.data;
            renderizarTabela(garantias);
            carregarFornecedoresFiltro();
        } else {
            alert('Erro ao carregar garantias: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar garantias');
    } finally {
        document.getElementById('loading').classList.add('hidden');
    }
}

// Renderizar tabela
function renderizarTabela(dados) {
    const tbody = document.getElementById('tabelaGarantias');
    tbody.innerHTML = '';
    
    if (dados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">Nenhuma garantia encontrada</td></tr>';
        return;
    }
    
    dados.forEach(garantia => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        
        const statusClass = getStatusClass(garantia.status);
        
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${garantia.id}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${garantia.fornecedor_nome || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${garantia.origem_garantia}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                    ${garantia.status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${garantia.total_itens}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ ${parseFloat(garantia.valor_total).toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatarData(garantia.created_at)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="visualizarGarantia(${garantia.id})" class="text-blue-600 hover:text-blue-900 mr-3">Ver</button>
                <button onclick="editarGarantia(${garantia.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                <button onclick="excluirGarantia(${garantia.id})" class="text-red-600 hover:text-red-900">Excluir</button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

// Funções auxiliares
function getStatusClass(status) {
    const classes = {
        'Em andamento': 'bg-blue-100 text-blue-800',
        'Finalizado': 'bg-green-100 text-green-800',
        'Garantia Expirada': 'bg-red-100 text-red-800',
        'Garantia não coberta': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR');
}

// Modal functions
function abrirModalNova() {
    document.getElementById('modalTitle').textContent = 'Nova Garantia';
    document.getElementById('formGarantia').reset();
    document.getElementById('garantiaId').value = '';
    limparItens();
    adicionarPrimeiroItem();
    document.getElementById('modalGarantia').classList.remove('hidden');
}

function fecharModal() {
    document.getElementById('modalGarantia').classList.add('hidden');
}

// Gerenciamento de itens
function adicionarPrimeiroItem() {
    if (document.getElementById('itensContainer').children.length === 0) {
        adicionarItem();
    }
}

function adicionarItem() {
    const container = document.getElementById('itensContainer');
    const index = container.children.length;
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'grid grid-cols-1 md:grid-cols-5 gap-4 p-4 border border-gray-200 rounded-lg';
    itemDiv.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item *</label>
            <input type="text" name="itens[${index}][item]" required class="w-full border border-gray-300 rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade *</label>
            <input type="number" name="itens[${index}][quantidade]" min="1" value="1" required class="w-full border border-gray-300 rounded-md px-3 py-2" onchange="calcularTotais()">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Unitário *</label>
            <input type="number" name="itens[${index}][valor_unitario]" step="0.01" min="0" required class="w-full border border-gray-300 rounded-md px-3 py-2" onchange="calcularTotais()">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Defeito</label>
            <textarea name="itens[${index}][defeito]" rows="2" class="w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
        </div>
        <div class="flex items-end">
            <button type="button" onclick="removerItem(this)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm">
                Remover
            </button>
        </div>
    `;
    
    container.appendChild(itemDiv);
    calcularTotais();
}

function removerItem(button) {
    const container = document.getElementById('itensContainer');
    if (container.children.length > 1) {
        button.closest('.grid').remove();
        calcularTotais();
    } else {
        alert('Deve haver pelo menos um item');
    }
}

function limparItens() {
    document.getElementById('itensContainer').innerHTML = '';
}

function calcularTotais() {
    const container = document.getElementById('itensContainer');
    let totalItens = 0;
    let valorTotal = 0;
    
    Array.from(container.children).forEach(item => {
        const quantidade = parseInt(item.querySelector('input[name*="[quantidade]"]').value) || 0;
        const valorUnitario = parseFloat(item.querySelector('input[name*="[valor_unitario]"]').value) || 0;
        
        totalItens += quantidade;
        valorTotal += quantidade * valorUnitario;
    });
    
    document.getElementById('totalItens').textContent = totalItens;
    document.getElementById('valorTotal').textContent = valorTotal.toFixed(2);
}

function verificarObservacaoObrigatoria() {
    const status = document.getElementById('status').value;
    const observacao = document.getElementById('observacao');
    const help = document.getElementById('observacaoHelp');
    
    const statusObrigatorios = ['Finalizado', 'Garantia Expirada', 'Garantia não coberta'];
    
    if (statusObrigatorios.includes(status)) {
        observacao.required = true;
        help.classList.remove('hidden');
    } else {
        observacao.required = false;
        help.classList.add('hidden');
    }
}

// Salvar garantia
async function salvarGarantia(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    // Coletar itens
    const itens = [];
    const container = document.getElementById('itensContainer');
    Array.from(container.children).forEach((item, index) => {
        const itemData = {
            item: item.querySelector('input[name*="[item]"]').value,
            quantidade: parseInt(item.querySelector('input[name*="[quantidade]"]').value),
            valor_unitario: parseFloat(item.querySelector('input[name*="[valor_unitario]"]').value),
            defeito: item.querySelector('textarea[name*="[defeito]"]').value
        };
        itens.push(itemData);
    });
    
    formData.append('itens', JSON.stringify(itens));
    
    try {
        const garantiaId = document.getElementById('garantiaId').value;
        const url = garantiaId ? `/garantias/${garantiaId}/update` : '/garantias/create';
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            fecharModal();
            carregarGarantias();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao salvar garantia');
    }
}

// Carregar fornecedores no select do modal
async function carregarFornecedoresSelect() {
    try {
        // Se já temos fornecedores do PHP, usar eles
        if (fornecedores && fornecedores.length > 0) {
            preencherSelectFornecedores(fornecedores);
            return;
        }
        
        // Caso contrário, carregar via AJAX
        const response = await fetch('/garantias/fornecedores');
        const result = await response.json();
        
        if (result.success && result.data) {
            fornecedores = result.data;
            preencherSelectFornecedores(result.data);
            console.log('Fornecedores carregados via AJAX:', result.debug);
        } else {
            console.error('Erro ao carregar fornecedores:', result.message);
        }
    } catch (error) {
        console.error('Erro ao carregar fornecedores:', error);
    }
}

function preencherSelectFornecedores(fornecedoresList) {
    const select = document.getElementById('fornecedorId');
    
    // Limpar opções existentes (exceto a primeira)
    while (select.children.length > 1) {
        select.removeChild(select.lastChild);
    }
    
    // Adicionar fornecedores
    fornecedoresList.forEach(fornecedor => {
        const option = document.createElement('option');
        option.value = fornecedor.id;
        option.textContent = fornecedor.nome;
        select.appendChild(option);
    });
}

// Outras funções
function carregarFornecedoresFiltro() {
    const select = document.getElementById('filtroFornecedor');
    const fornecedoresUnicos = [...new Set(garantias.map(g => g.fornecedor_nome))].filter(Boolean);
    
    fornecedoresUnicos.forEach(nome => {
        const option = document.createElement('option');
        option.value = nome;
        option.textContent = nome;
        select.appendChild(option);
    });
}

function aplicarFiltros() {
    const status = document.getElementById('filtroStatus').value;
    const origem = document.getElementById('filtroOrigem').value;
    const fornecedor = document.getElementById('filtroFornecedor').value;
    
    let dadosFiltrados = garantias;
    
    if (status) {
        dadosFiltrados = dadosFiltrados.filter(g => g.status === status);
    }
    
    if (origem) {
        dadosFiltrados = dadosFiltrados.filter(g => g.origem_garantia === origem);
    }
    
    if (fornecedor) {
        dadosFiltrados = dadosFiltrados.filter(g => g.fornecedor_nome === fornecedor);
    }
    
    renderizarTabela(dadosFiltrados);
}

function limparFiltros() {
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroOrigem').value = '';
    document.getElementById('filtroFornecedor').value = '';
    renderizarTabela(garantias);
}

async function excluirGarantia(id) {
    if (!confirm('Tem certeza que deseja excluir esta garantia?')) return;
    
    try {
        const response = await fetch(`/garantias/${id}/delete`, {
            method: 'POST'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            carregarGarantias();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir garantia');
    }
}

// Placeholder functions
function visualizarGarantia(id) {
    alert('Funcionalidade de visualização em desenvolvimento');
}

function editarGarantia(id) {
    alert('Funcionalidade de edição em desenvolvimento');
}
</script>
