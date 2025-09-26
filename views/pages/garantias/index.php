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
        <div class="flex space-x-3">
            <button id="toggleCorreiosFormBtn" type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span>Formulário de Correios</span>
            </button>
            <button id="toggleGarantiaFormBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Nova Garantia</span>
            </button>
        </div>
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
                    <label class="block text-sm font-medium text-white mb-2">Fornecedor *</label>
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
                    <label class="block text-sm font-medium text-white mb-2">Origem da Garantia *</label>
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
                    <label class="block text-sm font-medium text-white mb-2">Número NF Compras</label>
                    <input type="text" name="numero_nf_compras" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Digite o número da NF">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Número NF Remessa Simples</label>
                    <input type="text" name="numero_nf_remessa_simples" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Digite o número da NF">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Número NF Remessa Devolução</label>
                    <input type="text" name="numero_nf_remessa_devolucao" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Digite o número da NF">
                </div>
            </div>

            <!-- Anexos das Notas Fiscais -->
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-medium text-white mb-4">📎 Anexos das Notas Fiscais</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">NF Compras (PDF)</label>
                        <input type="file" name="anexo_nf_compras" accept=".pdf" onchange="validateFileUpload(this, 'nf_compras')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">Apenas PDF até 10MB</p>
                        <div id="preview_nf_compras" class="mt-2"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">NF Remessa Simples (PDF)</label>
                        <input type="file" name="anexo_nf_remessa_simples" accept=".pdf" onchange="validateFileUpload(this, 'nf_remessa_simples')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">Apenas PDF até 10MB</p>
                        <div id="preview_nf_remessa_simples" class="mt-2"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">NF Remessa Devolução (PDF)</label>
                        <input type="file" name="anexo_nf_remessa_devolucao" accept=".pdf" onchange="validateFileUpload(this, 'nf_remessa_devolucao')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">Apenas PDF até 10MB</p>
                        <div id="preview_nf_remessa_devolucao" class="mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- Campos Opcionais -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Número de Série</label>
                    <input type="text" name="numero_serie" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Digite o número de série">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Número do Lote</label>
                    <input type="text" name="numero_lote" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Digite o número do lote">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Número Ticket/OS</label>
                    <input type="text" name="numero_ticket_os" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Digite o número do ticket">
                </div>
            </div>

            <!-- Anexos dos Laudos -->
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-medium text-white mb-4">📋 Anexos dos Laudos</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Laudo Técnico (PDF/DOC)</label>
                        <input type="file" name="anexo_laudo_tecnico" accept=".pdf,.doc,.docx" onchange="validateFileUpload(this, 'laudo_tecnico')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">PDF, DOC ou DOCX até 10MB</p>
                        <div id="preview_laudo_tecnico" class="mt-2"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Evidências (Imagens)</label>
                        <input type="file" name="anexo_evidencias[]" accept="image/*" multiple onchange="validateImageUpload(this, 'evidencias')" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">Imagens até 5MB cada (máx. 10 arquivos)</p>
                        <div id="preview_evidencias" class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                    </div>
                </div>
            </div>

            <!-- Status e Observação -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Status</label>
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
                    <label class="block text-sm font-medium text-white mb-2">Observação</label>
                    <textarea name="observacao" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Observações sobre a garantia..."></textarea>
                </div>
            </div>

            <!-- Informações de Logística (Opcional) -->
            <div class="bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-medium text-white mb-4">🚚 Informações de Logística (Opcional)</h3>
                
                <!-- Dados da Transportadora -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Nome da Transportadora</label>
                        <input type="text" name="nome_transportadora" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Ex: Transportadora ABC Ltda">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">CNPJ da Transportadora</label>
                        <input type="text" name="cnpj_transportadora" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="00.000.000/0000-00">
                    </div>
                </div>

                <!-- Dimensões e Peso -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Peso Total (Kg)</label>
                        <input type="number" name="peso_total_logistica" step="0.001" min="0" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="0.000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Altura (cm)</label>
                        <input type="number" name="altura" step="0.01" min="0" onchange="calcularVolume()" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Largura (cm)</label>
                        <input type="number" name="largura" step="0.01" min="0" onchange="calcularVolume()" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Profundidade (cm)</label>
                        <input type="number" name="profundidade" step="0.01" min="0" onchange="calcularVolume()" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="0.00">
                    </div>
                </div>

                <!-- Volume Calculado e Observações -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Volume Calculado (m³)</label>
                        <input type="text" id="volumeCalculado" class="w-full bg-gray-500 border border-gray-400 text-gray-300 rounded-lg px-3 py-2" readonly placeholder="Calculado automaticamente">
                        <p class="text-xs text-gray-400 mt-1">Calculado automaticamente: Altura × Largura × Profundidade</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Observações de Logística</label>
                        <textarea name="observacoes_logistica" rows="3" class="w-full bg-gray-600 border border-gray-500 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Ex: Produto frágil, manuseio cuidadoso..."></textarea>
                    </div>
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

    <!-- Formulário de Correios -->
    <div id="correiosFormContainer" class="hidden bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-green-800">📮 Formulário de Correios</h2>
            <button onclick="cancelCorreiosForm()" class="text-green-600 hover:text-green-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Remetentes -->
            <div class="bg-white rounded-lg border border-green-200 p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">👤 Remetentes</h3>
                    <button type="button" onclick="adicionarRemetente()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                        + Adicionar Remetente
                    </button>
                </div>
                <div id="remetentesContainer" class="space-y-4">
                    <!-- Remetentes adicionados dinamicamente -->
                </div>
            </div>

            <!-- Destinatários -->
            <div class="bg-white rounded-lg border border-green-200 p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">📍 Destinatários</h3>
                    <button type="button" onclick="adicionarDestinatario()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                        + Adicionar Destinatário
                    </button>
                </div>
                <div id="destinatariosContainer" class="space-y-4">
                    <!-- Destinatários adicionados dinamicamente -->
                </div>
            </div>
        </div>

        <!-- Itens da Declaração -->
        <div class="mt-8 bg-white rounded-lg border border-green-200 p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">📦 Identificação dos Bens</h3>
                <button type="button" onclick="adicionarItemDeclaracao()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                    + Adicionar Item
                </button>
            </div>
            <div id="itensDeclaracaoContainer" class="space-y-4">
                <!-- Itens adicionados dinamicamente -->
            </div>
            
            <!-- Totais -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Peso Total (Kg)</label>
                        <input type="number" id="pesoTotal" step="0.001" min="0" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="0.000">
                    </div>
                    <div class="flex items-end">
                        <div class="text-lg font-semibold text-gray-900">
                            Valor Total: R$ <span id="valorTotalDeclaracao">0,00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="cancelCorreiosForm()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancelar
            </button>
            <button type="button" onclick="gerarDeclaracaoConteudo()" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                Gerar Declaração de Conteúdo
            </button>
        </div>
    </div>

    <!-- Tabela de Garantias -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <!-- Controles da Tabela -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h3 class="text-sm font-medium text-gray-900">Tabela de Garantias</h3>
                    <span class="text-xs text-gray-500">Arraste as bordas das colunas para redimensionar</span>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="resetColumnWidths()" class="text-xs text-blue-600 hover:text-blue-800 px-2 py-1 rounded border border-blue-200 hover:bg-blue-50">
                        Resetar Colunas
                    </button>
                    <button onclick="toggleColumnVisibility()" class="text-xs text-gray-600 hover:text-gray-800 px-2 py-1 rounded border border-gray-200 hover:bg-gray-50">
                        Configurar Colunas
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de Configuração de Colunas -->
        <div id="columnConfigModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Configurar Colunas</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3" id="columnToggles">
                        <!-- Checkboxes gerados dinamicamente -->
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button onclick="closeColumnConfig()" class="px-4 py-2 text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                        Fechar
                    </button>
                    <button onclick="applyColumnConfig()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Aplicar
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="garantiasTable" class="min-w-full divide-y divide-gray-200 table-fixed">
                <thead class="bg-gray-50">
                    <tr>
                        <th data-column="id" class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 80px; min-width: 60px;">
                            ID
                            <div class="column-resizer"></div>
                        </th>
                        <th data-column="fornecedor" class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 200px; min-width: 120px;">
                            Fornecedor
                            <div class="column-resizer"></div>
                        </th>
                        <th data-column="origem" class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 120px; min-width: 100px;">
                            Origem
                            <div class="column-resizer"></div>
                        </th>
                        <th data-column="nfs" class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 150px; min-width: 100px;">
                            NFs
                            <div class="column-resizer"></div>
                        </th>
                        <th data-column="status" class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 200px; min-width: 150px;">
                            Status
                            <div class="column-resizer"></div>
                        </th>
                        <th data-column="itens" class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 80px; min-width: 60px;">
                            Itens
                            <div class="column-resizer"></div>
                        </th>
                        <th data-column="valor" class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 120px; min-width: 100px;">
                            Valor Total
                            <div class="column-resizer"></div>
                        </th>
                        <th data-column="anexos" class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 100px; min-width: 80px;">
                            Anexos
                            <div class="column-resizer"></div>
                        </th>
                        <th data-column="data" class="resizable-column px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 140px; min-width: 120px;">
                            Criado em
                            <div class="column-resizer"></div>
                        </th>
                        <th data-column="acoes" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 200px; min-width: 180px;">
                            Ações
                        </th>
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

<!-- Modal removido - usando apenas formulário inline -->

<style>
/* Estilos para redimensionamento de colunas */
.resizable-column {
    position: relative;
    user-select: none;
}

.column-resizer {
    position: absolute;
    top: 0;
    right: 0;
    width: 8px;
    height: 100%;
    cursor: col-resize;
    background: transparent;
    border-right: 2px solid transparent;
    transition: border-color 0.2s ease;
}

.column-resizer:hover {
    border-right-color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
}

.column-resizer.resizing {
    border-right-color: #1d4ed8;
    background: rgba(29, 78, 216, 0.2);
}

.table-fixed {
    table-layout: fixed;
}

.table-fixed th,
.table-fixed td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Animação para mostrar/ocultar colunas */
.column-hidden {
    display: none !important;
}

.column-show {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Estilos para o modal de configuração */
#columnConfigModal .space-y-3 > div {
    display: flex;
    items-center;
    justify-content: space-between;
    padding: 8px 0;
}

#columnConfigModal input[type="checkbox"] {
    margin-right: 8px;
}

/* Indicador visual de redimensionamento */
.table-resizing {
    user-select: none;
}

.table-resizing * {
    cursor: col-resize !important;
}
</style>

<script>
// Variáveis globais
let garantias = [];
let fornecedores = <?= json_encode($fornecedores) ?>;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarGarantias();
    configurarEventos();
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
    // Resetar formulário
    document.getElementById('garantiaForm').reset();
    document.getElementById('garantiaId').value = '';
    
    // Resetar título
    document.getElementById('garantiaFormTitle').textContent = 'Nova Garantia';
    
    // Resetar botão de submit
    const submitBtn = document.getElementById('submitGarantiaBtn');
    submitBtn.textContent = 'Salvar Garantia';
    submitBtn.className = 'px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2';
    
    // Limpar itens
    document.getElementById('itensContainer').innerHTML = '';
    
    // Limpar previews de anexos
    clearAllPreviews();
    
    // Remover seção de anexos existentes se existir
    const anexosExistentes = document.querySelector('.bg-blue-50.border-blue-200');
    if (anexosExistentes) {
        anexosExistentes.remove();
    }
    
    // Limpar campos de logística
    document.querySelector('[name="nome_transportadora"]').value = '';
    document.querySelector('[name="cnpj_transportadora"]').value = '';
    document.querySelector('[name="peso_total_logistica"]').value = '';
    document.querySelector('[name="altura"]').value = '';
    document.querySelector('[name="largura"]').value = '';
    document.querySelector('[name="profundidade"]').value = '';
    document.querySelector('[name="observacoes_logistica"]').value = '';
    document.getElementById('volumeCalculado').value = '';
    
    // Atualizar totais
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
    const garantiaId = document.getElementById('garantiaId').value;
    const isEdicao = garantiaId && garantiaId.trim() !== '';
    
    console.log('📝 Tipo de operação:', isEdicao ? 'Edição' : 'Criação', 'ID:', garantiaId);
    
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
        
        console.log(`Item ${index + 1}:`, {
            descricao: descricao,
            quantidade: quantidade,
            valor: valor
        });
        
        if (descricao && quantidade && valor && quantidade > 0 && valor > 0) {
            const itemData = {
                descricao: descricao,
                quantidade: parseInt(quantidade),
                valor_unitario: parseFloat(valor)
            };
            itensData.push(itemData);
            console.log(`Item ${index + 1} adicionado:`, itemData);
        } else {
            console.log(`Item ${index + 1} ignorado - dados inválidos`);
        }
    });
    
    console.log('Dados dos itens coletados:', itensData);
    
    // Adicionar itens ao FormData
    formData.append('itens', JSON.stringify(itensData));
    console.log('JSON dos itens:', JSON.stringify(itensData));
    
    // Debug FormData
    console.log('Dados do formulário:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    // Determinar URL e método
    const url = isEdicao ? `/garantias/${garantiaId}/update` : '/garantias';
    const operacao = isEdicao ? 'atualizada' : 'criada';
    
    // Enviar dados
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        console.log('📡 Response status:', response.status);
        console.log('📡 Response headers:', response.headers.get('content-type'));
        
        // Verificar se a resposta é OK
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        // Tentar ler como texto primeiro para debug
        const responseText = await response.text();
        console.log('📋 Response text:', responseText);
        
        try {
            return JSON.parse(responseText);
        } catch (parseError) {
            console.error('❌ Erro ao fazer parse do JSON:', parseError);
            console.error('📄 Resposta recebida:', responseText);
            throw new Error('Resposta inválida do servidor. Verifique se a rota /garantias existe e retorna JSON válido.');
        }
    })
    .then(result => {
        console.log('✅ Resultado parseado:', result);
        
        if (result && result.success) {
            showNotification(`Garantia ${operacao} com sucesso!`, 'success');
            cancelGarantiaForm();
            carregarGarantias();
        } else {
            alert('Erro: ' + (result ? result.message : 'Resposta inválida do servidor'));
        }
    })
    .catch(error => {
        console.error('❌ Erro completo:', error);
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
                <input type="text" name="item_descricao" required class="w-full bg-gray-700 border border-gray-500 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Descrição do item">
            </div>
            <div>
                <label class="block text-sm font-medium text-white mb-1">Quantidade *</label>
                <input type="number" name="item_quantidade" min="1" required onchange="atualizarTotais()" class="w-full bg-gray-700 border border-gray-500 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="1">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-white mb-1">Valor Unitário (R$) *</label>
                <input type="number" name="item_valor" step="0.01" min="0" required onchange="atualizarTotais()" class="w-full bg-gray-700 border border-gray-500 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="0,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-white mb-1">Valor Total</label>
                <input type="text" class="item-valor-total w-full bg-gray-600 border border-gray-500 text-gray-300 rounded px-3 py-2 placeholder-gray-400" readonly placeholder="R$ 0,00">
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

// Calcular volume automaticamente
function calcularVolume() {
    const altura = parseFloat(document.querySelector('input[name="altura"]').value) || 0;
    const largura = parseFloat(document.querySelector('input[name="largura"]').value) || 0;
    const profundidade = parseFloat(document.querySelector('input[name="profundidade"]').value) || 0;
    
    if (altura > 0 && largura > 0 && profundidade > 0) {
        // Calcular volume em m³ (converter de cm³ para m³)
        const volumeM3 = (altura * largura * profundidade) / 1000000;
        document.getElementById('volumeCalculado').value = volumeM3.toFixed(6) + ' m³';
    } else {
        document.getElementById('volumeCalculado').value = '';
    }
}

// Carregar garantias
async function carregarGarantias() {
    try {
        document.getElementById('loading').classList.remove('hidden');
        
        const response = await fetch('/garantias/list');
        console.log('📡 Carregando garantias - Status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const responseText = await response.text();
        console.log('📋 Response garantias:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('❌ Erro ao fazer parse do JSON:', parseError);
            console.error('📄 Resposta recebida:', responseText);
            
            // Se a rota não existe, mostrar dados de exemplo
            console.log('⚠️ Usando dados de exemplo - rota /garantias/list não implementada');
            garantias = [];
            renderizarTabela(garantias);
            return;
        }
        
        if (result && result.success) {
            garantias = result.data || [];
            console.log('📊 Dados das garantias carregados:', garantias);
            
            // Debug dos totais
            garantias.forEach(g => {
                console.log(`Garantia #${g.id}: ${g.total_itens} itens, R$ ${g.valor_total}`);
            });
            
            renderizarTabela(garantias);
            carregarFornecedoresFiltro();
        } else {
            console.error('❌ Erro na resposta:', result);
            alert('Erro ao carregar garantias: ' + (result ? result.message : 'Resposta inválida'));
        }
    } catch (error) {
        console.error('❌ Erro ao carregar garantias:', error);
        
        // Em caso de erro, mostrar tabela vazia
        garantias = [];
        renderizarTabela(garantias);
        
        // Só mostrar alert se não for erro de rota não encontrada
        if (!error.message.includes('404')) {
            alert('Erro ao carregar garantias: ' + error.message);
        }
    } finally {
        document.getElementById('loading').classList.add('hidden');
    }
}

// Renderizar tabela
function renderizarTabela(dados) {
    const tbody = document.getElementById('tabelaGarantias');
    tbody.innerHTML = '';
    
    if (dados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="px-4 py-8 text-center text-gray-500">Nenhuma garantia encontrada</td></tr>';
        return;
    }
    
    dados.forEach(garantia => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        
        const statusClass = getStatusClass(garantia.status);
        
        // Montar lista de NFs
        const nfs = [];
        if (garantia.numero_nf_compras) nfs.push(`C: ${garantia.numero_nf_compras}`);
        if (garantia.numero_nf_remessa_simples) nfs.push(`RS: ${garantia.numero_nf_remessa_simples}`);
        if (garantia.numero_nf_remessa_devolucao) nfs.push(`RD: ${garantia.numero_nf_remessa_devolucao}`);
        const nfsText = nfs.length > 0 ? nfs.join('<br>') : '-';
        
        tr.innerHTML = `
            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">#${garantia.id}</td>
            <td class="px-4 py-3 text-sm text-gray-900 max-w-xs">
                <div class="truncate" title="${garantia.fornecedor_nome || 'N/A'}">
                    ${garantia.fornecedor_nome || 'N/A'}
                </div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${garantia.origem_garantia}
                </span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-600 max-w-xs">
                <div class="space-y-1">${nfsText}</div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <select onchange="updateGarantiaStatus(${garantia.id}, this.value, this)" 
                        class="text-xs font-semibold rounded-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 ${statusClass}">
                    <option value="Em andamento" ${garantia.status === 'Em andamento' ? 'selected' : ''}>Em andamento</option>
                    <option value="Aguardando Fornecedor" ${garantia.status === 'Aguardando Fornecedor' ? 'selected' : ''}>Aguardando Fornecedor</option>
                    <option value="Aguardando Recebimento" ${garantia.status === 'Aguardando Recebimento' ? 'selected' : ''}>Aguardando Recebimento</option>
                    <option value="Aguardando Item Chegar ao laboratório" ${garantia.status === 'Aguardando Item Chegar ao laboratório' ? 'selected' : ''}>Aguardando Item Chegar ao laboratório</option>
                    <option value="Aguardando Emissão de NF" ${garantia.status === 'Aguardando Emissão de NF' ? 'selected' : ''}>Aguardando Emissão de NF</option>
                    <option value="Aguardando Despache" ${garantia.status === 'Aguardando Despache' ? 'selected' : ''}>Aguardando Despache</option>
                    <option value="Aguardando Testes" ${garantia.status === 'Aguardando Testes' ? 'selected' : ''}>Aguardando Testes</option>
                    <option value="Finalizado" ${garantia.status === 'Finalizado' ? 'selected' : ''}>Finalizado</option>
                    <option value="Garantia Expirada" ${garantia.status === 'Garantia Expirada' ? 'selected' : ''}>Garantia Expirada</option>
                    <option value="Garantia não coberta" ${garantia.status === 'Garantia não coberta' ? 'selected' : ''}>Garantia não coberta</option>
                </select>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    ${parseInt(garantia.total_itens) || 0}
                </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">
                R$ ${formatarValorBrasileiro(garantia.valor_total || 0)}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-center">
                <div class="flex items-center justify-center space-x-1">
                    <button onclick="downloadAllAnexos(${garantia.id})" 
                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition-colors"
                            title="Baixar todos os anexos">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        ${garantia.total_anexos || 0}
                    </button>
                </div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                <div class="text-xs">${formatarData(garantia.created_at)}</div>
                <div class="text-xs text-gray-400">${calcularTempoDecorrido(garantia.created_at)}</div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                    <button onclick="visualizarGarantia(${garantia.id})" 
                            class="text-blue-600 hover:text-blue-900 text-xs bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 transition-colors"
                            title="Ver detalhes completos">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                        </svg>
                        Ver
                    </button>
                    <button onclick="editarGarantia(${garantia.id})" 
                            class="text-indigo-600 hover:text-indigo-900 text-xs bg-indigo-50 px-2 py-1 rounded hover:bg-indigo-100 transition-colors"
                            title="Editar garantia">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Editar
                    </button>
                    <button onclick="excluirGarantia(${garantia.id})" 
                            class="text-red-600 hover:text-red-900 text-xs bg-red-50 px-2 py-1 rounded hover:bg-red-100 transition-colors"
                            title="Excluir garantia">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Excluir
                    </button>
                </div>
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

function formatarValorBrasileiro(valor) {
    const numero = parseFloat(valor) || 0;
    return numero.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function calcularTempoDecorrido(data) {
    const agora = new Date();
    const dataGarantia = new Date(data);
    const diffMs = agora - dataGarantia;
    const diffDias = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    if (diffDias === 0) return 'Hoje';
    if (diffDias === 1) return '1 dia atrás';
    if (diffDias < 30) return `${diffDias} dias atrás`;
    if (diffDias < 365) return `${Math.floor(diffDias/30)} meses atrás`;
    return `${Math.floor(diffDias/365)} anos atrás`;
}

// Atualizar status da garantia no grid
async function updateGarantiaStatus(id, newStatus, selectElement) {
    try {
        console.log('🔄 Atualizando status da garantia:', { id, newStatus });
        
        const formData = new FormData();
        formData.append('status', newStatus);
        
        const response = await fetch(`/garantias/${id}/update-status`, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        
        if (result && result.success) {
            // Atualizar cor do select
            if (selectElement) {
                const statusClass = getStatusClass(newStatus);
                selectElement.className = `text-xs font-semibold rounded-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 ${statusClass}`;
            }
            
            console.log('✅ Status atualizado com sucesso!');
            
            // Mostrar notificação de sucesso
            showNotification('Status atualizado com sucesso!', 'success');
        } else {
            console.error('❌ Erro retornado pela API:', result);
            alert('Erro: ' + (result ? result.message : 'Resposta inválida'));
            location.reload();
        }
    } catch (error) {
        console.error('❌ Erro ao atualizar status:', error);
        alert('Erro ao atualizar status: ' + error.message);
        location.reload();
    }
}

// Download de todos os anexos
async function downloadAllAnexos(garantiaId) {
    try {
        console.log('📥 Baixando anexos da garantia:', garantiaId);
        
        const response = await fetch(`/garantias/${garantiaId}/anexos/download-all`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        // Criar download do arquivo ZIP
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `garantia_${garantiaId}_anexos.zip`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showNotification('Anexos baixados com sucesso!', 'success');
    } catch (error) {
        console.error('❌ Erro ao baixar anexos:', error);
        alert('Erro ao baixar anexos: ' + error.message);
    }
}

// Mostrar notificação
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
        type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
        'bg-blue-100 text-blue-800 border border-blue-200'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                ${type === 'success' ? 
                    '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>' :
                    '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>'
                }
            </svg>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Remover após 3 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Modal functions
// Funções antigas do modal removidas - usando apenas formulário inline

// Funções antigas do modal removidas - usando apenas formulário inline

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

// Visualizar garantia com detalhes completos
async function visualizarGarantia(id) {
    try {
        console.log('👁️ Carregando detalhes da garantia:', id);
        
        const response = await fetch(`/garantias/${id}`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Erro ao carregar garantia');
        }
        
        const garantia = result.data;
        mostrarModalDetalhes(garantia);
        
    } catch (error) {
        console.error('❌ Erro ao carregar garantia:', error);
        alert('Erro ao carregar detalhes: ' + error.message);
    }
}

// Mostrar modal com detalhes completos
function mostrarModalDetalhes(garantia) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.onclick = (e) => { if (e.target === modal) fecharModalDetalhes(modal); };
    
    // Calcular tempo em cada status (simulado - seria implementado no backend)
    const tempoStatus = calcularTempoStatus(garantia);
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Detalhes da Garantia #${garantia.id}</h3>
                <button onclick="fecharModalDetalhes(this.closest('.fixed'))" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Informações Básicas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">📋 Informações Básicas</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Fornecedor:</span> ${garantia.fornecedor_nome || 'N/A'}</div>
                            <div><span class="font-medium">Origem:</span> ${garantia.origem_garantia}</div>
                            <div><span class="font-medium">Status:</span> 
                                <span class="px-2 py-1 rounded-full text-xs font-medium ${getStatusClass(garantia.status)}">
                                    ${garantia.status}
                                </span>
                            </div>
                            <div><span class="font-medium">Criado em:</span> ${formatarData(garantia.created_at)}</div>
                            <div><span class="font-medium">Última atualização:</span> ${formatarData(garantia.updated_at)}</div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">📄 Notas Fiscais</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">NF Compras:</span> ${garantia.numero_nf_compras || '-'}</div>
                            <div><span class="font-medium">NF Remessa Simples:</span> ${garantia.numero_nf_remessa_simples || '-'}</div>
                            <div><span class="font-medium">NF Remessa Devolução:</span> ${garantia.numero_nf_remessa_devolucao || '-'}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Campos Opcionais -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-3">🔧 Informações Técnicas</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div><span class="font-medium">Número de Série:</span> ${garantia.numero_serie || '-'}</div>
                        <div><span class="font-medium">Número do Lote:</span> ${garantia.numero_lote || '-'}</div>
                        <div><span class="font-medium">Ticket/OS:</span> ${garantia.numero_ticket_os || '-'}</div>
                    </div>
                </div>
                
                <!-- Informações de Logística -->
                ${garantia.logistica ? `
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <h4 class="font-medium text-gray-900 mb-3">🚚 Informações de Logística</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <h5 class="font-medium text-gray-700 mb-2">Transportadora</h5>
                                <div class="space-y-1 text-sm">
                                    <div><span class="font-medium">Nome:</span> ${garantia.logistica.nome_transportadora || '-'}</div>
                                    <div><span class="font-medium">CNPJ:</span> ${garantia.logistica.cnpj_transportadora || '-'}</div>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-700 mb-2">Dimensões e Peso</h5>
                                <div class="space-y-1 text-sm">
                                    <div><span class="font-medium">Peso Total:</span> ${garantia.logistica.peso_total ? garantia.logistica.peso_total + ' kg' : '-'}</div>
                                    <div><span class="font-medium">Dimensões (A×L×P):</span> 
                                        ${garantia.logistica.altura && garantia.logistica.largura && garantia.logistica.profundidade 
                                            ? `${garantia.logistica.altura}×${garantia.logistica.largura}×${garantia.logistica.profundidade} cm`
                                            : '-'
                                        }
                                    </div>
                                    <div><span class="font-medium">Volume:</span> 
                                        ${garantia.logistica.volume_total ? garantia.logistica.volume_total + ' m³' : '-'}
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${garantia.logistica.observacoes_logistica ? `
                            <div>
                                <h5 class="font-medium text-gray-700 mb-1">Observações de Logística</h5>
                                <p class="text-sm text-gray-600 bg-white p-2 rounded border">${garantia.logistica.observacoes_logistica}</p>
                            </div>
                        ` : ''}
                    </div>
                ` : ''}
                
                <!-- Tempo em cada Status -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-3">⏱️ Tempo por Status</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        ${tempoStatus.map(item => `
                            <div class="text-center p-2 bg-white rounded border">
                                <div class="font-medium text-xs text-gray-600 mb-1">${item.status}</div>
                                <div class="text-lg font-bold ${item.atual ? 'text-blue-600' : 'text-gray-800'}">${item.tempo}</div>
                                ${item.atual ? '<div class="text-xs text-blue-600">Atual</div>' : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Itens -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-3">📦 Itens da Garantia</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qtd</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Valor Unit.</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${(garantia.itens || []).map(item => `
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-900">${item.descricao}</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">${item.quantidade}</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">R$ ${parseFloat(item.valor_unitario).toFixed(2).replace('.', ',')}</td>
                                        <td class="px-3 py-2 text-sm font-medium text-gray-900">R$ ${parseFloat(item.valor_total).toFixed(2).replace('.', ',')}</td>
                                    </tr>
                                `).join('')}
                                <tr class="bg-gray-100 font-medium">
                                    <td colspan="3" class="px-3 py-2 text-sm text-gray-900 text-right">Total Geral:</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">R$ ${parseFloat(garantia.valor_total || 0).toFixed(2).replace('.', ',')}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Anexos -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-3">📎 Anexos</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${(garantia.anexos || []).map(anexo => `
                            <div class="flex items-center justify-between p-3 bg-white rounded border">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">${anexo.nome_arquivo}</div>
                                        <div class="text-xs text-gray-500">${anexo.tipo_anexo} • ${(anexo.tamanho_bytes/1024/1024).toFixed(2)} MB</div>
                                    </div>
                                </div>
                                <button onclick="downloadAnexo(${anexo.id})" class="text-blue-600 hover:text-blue-800 text-sm">
                                    Baixar
                                </button>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Observações -->
                ${garantia.observacao ? `
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">💬 Observações</h4>
                        <p class="text-sm text-gray-700">${garantia.observacao}</p>
                    </div>
                ` : ''}
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button onclick="downloadAllAnexos(${garantia.id})" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                    Baixar Todos os Anexos
                </button>
                <button onclick="editarGarantia(${garantia.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                    Editar Garantia
                </button>
                <button onclick="fecharModalDetalhes(this.closest('.fixed'))" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function fecharModalDetalhes(modal) {
    if (modal && modal.parentNode) {
        modal.parentNode.removeChild(modal);
    }
}

function calcularTempoStatus(garantia) {
    // Esta função seria implementada no backend com dados reais
    // Por enquanto, retorna dados simulados
    const statusList = [
        { status: 'Em andamento', tempo: '2 dias', atual: garantia.status === 'Em andamento' },
        { status: 'Aguardando Fornecedor', tempo: '5 dias', atual: garantia.status === 'Aguardando Fornecedor' },
        { status: 'Aguardando Recebimento', tempo: '3 dias', atual: garantia.status === 'Aguardando Recebimento' },
        { status: 'Finalizado', tempo: '-', atual: garantia.status === 'Finalizado' }
    ];
    
    return statusList;
}

function downloadAnexo(anexoId) {
    window.open(`/garantias/anexo/${anexoId}`, '_blank');
}

// Editar garantia - carrega dados no formulário inline
async function editarGarantia(id) {
    try {
        console.log('✏️ Carregando garantia para edição:', id);
        
        const response = await fetch(`/garantias/${id}`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Erro ao carregar garantia');
        }
        
        const garantia = result.data;
        preencherFormularioEdicao(garantia);
        
    } catch (error) {
        console.error('❌ Erro ao carregar garantia:', error);
        alert('Erro ao carregar dados para edição: ' + error.message);
    }
}

// Preencher formulário com dados da garantia
function preencherFormularioEdicao(garantia) {
    // Mostrar formulário se estiver oculto
    const container = document.getElementById('garantiaFormContainer');
    const btn = document.getElementById('toggleGarantiaFormBtn');
    
    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        btn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>Cancelar</span>
        `;
    }
    
    // Atualizar título do formulário
    document.getElementById('garantiaFormTitle').textContent = `Editar Garantia #${garantia.id}`;
    
    // Preencher campos básicos
    document.getElementById('garantiaId').value = garantia.id;
    document.querySelector('[name="fornecedor_id"]').value = garantia.fornecedor_id || '';
    document.querySelector('[name="origem_garantia"]').value = garantia.origem_garantia || '';
    
    // Preencher números de NF
    document.querySelector('[name="numero_nf_compras"]').value = garantia.numero_nf_compras || '';
    document.querySelector('[name="numero_nf_remessa_simples"]').value = garantia.numero_nf_remessa_simples || '';
    document.querySelector('[name="numero_nf_remessa_devolucao"]').value = garantia.numero_nf_remessa_devolucao || '';
    
    // Preencher campos opcionais
    document.querySelector('[name="numero_serie"]').value = garantia.numero_serie || '';
    document.querySelector('[name="numero_lote"]').value = garantia.numero_lote || '';
    document.querySelector('[name="numero_ticket_os"]').value = garantia.numero_ticket_os || '';
    
    // Preencher status e observação
    document.querySelector('[name="status"]').value = garantia.status || 'Em andamento';
    document.querySelector('[name="observacao"]').value = garantia.observacao || '';
    
    // Preencher dados de logística se existirem
    if (garantia.logistica) {
        document.querySelector('[name="nome_transportadora"]').value = garantia.logistica.nome_transportadora || '';
        document.querySelector('[name="cnpj_transportadora"]').value = garantia.logistica.cnpj_transportadora || '';
        document.querySelector('[name="peso_total_logistica"]').value = garantia.logistica.peso_total || '';
        document.querySelector('[name="altura"]').value = garantia.logistica.altura || '';
        document.querySelector('[name="largura"]').value = garantia.logistica.largura || '';
        document.querySelector('[name="profundidade"]').value = garantia.logistica.profundidade || '';
        document.querySelector('[name="observacoes_logistica"]').value = garantia.logistica.observacoes_logistica || '';
        
        // Calcular volume se as dimensões existirem
        calcularVolume();
    }
    
    // Limpar itens existentes e adicionar os da garantia
    document.getElementById('itensContainer').innerHTML = '';
    
    if (garantia.itens && garantia.itens.length > 0) {
        garantia.itens.forEach(item => {
            adicionarItemEdicao(item);
        });
    } else {
        adicionarItem(); // Adicionar um item vazio se não houver itens
    }
    
    // Atualizar botão de submit
    const submitBtn = document.getElementById('submitGarantiaBtn');
    submitBtn.textContent = 'Atualizar Garantia';
    submitBtn.className = 'px-6 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2';
    
    // Mostrar anexos existentes (apenas informativo)
    mostrarAnexosExistentes(garantia.anexos || []);
    
    // Scroll para o formulário
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    showNotification(`Garantia #${garantia.id} carregada para edição`, 'info');
}

// Adicionar item com dados existentes
function adicionarItemEdicao(itemData) {
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
                <input type="text" name="item_descricao" required value="${itemData.descricao || ''}" class="w-full bg-gray-700 border border-gray-500 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Descrição do item">
            </div>
            <div>
                <label class="block text-sm font-medium text-white mb-1">Quantidade *</label>
                <input type="number" name="item_quantidade" min="1" required value="${itemData.quantidade || 1}" onchange="atualizarTotais()" class="w-full bg-gray-700 border border-gray-500 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="1">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-white mb-1">Valor Unitário (R$) *</label>
                <input type="number" name="item_valor" step="0.01" min="0" required value="${itemData.valor_unitario || 0}" onchange="atualizarTotais()" class="w-full bg-gray-700 border border-gray-500 text-white rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="0,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-white mb-1">Valor Total</label>
                <input type="text" class="item-valor-total w-full bg-gray-600 border border-gray-500 text-gray-300 rounded px-3 py-2 placeholder-gray-400" readonly placeholder="R$ 0,00">
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
    atualizarTotais();
}

// Mostrar anexos existentes (apenas informativo)
function mostrarAnexosExistentes(anexos) {
    if (anexos.length === 0) return;
    
    // Criar seção de anexos existentes
    const anexosSection = document.createElement('div');
    anexosSection.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4';
    anexosSection.innerHTML = `
        <h4 class="text-blue-800 font-medium mb-3">📎 Anexos Existentes</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            ${anexos.map(anexo => `
                <div class="flex items-center justify-between p-2 bg-white rounded border">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <div class="text-sm font-medium text-gray-900">${anexo.nome_arquivo}</div>
                            <div class="text-xs text-gray-500">${anexo.tipo_anexo}</div>
                        </div>
                    </div>
                    <button type="button" onclick="downloadAnexo(${anexo.id})" class="text-blue-600 hover:text-blue-800 text-xs">
                        Baixar
                    </button>
                </div>
            `).join('')}
        </div>
        <p class="text-xs text-blue-600 mt-2">💡 Para substituir anexos, faça upload de novos arquivos abaixo</p>
    `;
    
    // Inserir antes da seção de itens
    const itensSection = document.querySelector('.bg-gray-700:has(#itensContainer)').parentElement;
    itensSection.parentElement.insertBefore(anexosSection, itensSection);
}

// =====================================================
// SISTEMA DE FORMULÁRIO DE CORREIOS
// =====================================================

// Toggle do formulário de correios
function toggleCorreiosForm() {
    const container = document.getElementById('correiosFormContainer');
    const btn = document.getElementById('toggleCorreiosFormBtn');
    
    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        btn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>Cancelar</span>
        `;
        
        // Adicionar primeiro remetente e destinatário se não existirem
        if (document.getElementById('remetentesContainer').children.length === 0) {
            adicionarRemetente();
        }
        if (document.getElementById('destinatariosContainer').children.length === 0) {
            adicionarDestinatario();
        }
        if (document.getElementById('itensDeclaracaoContainer').children.length === 0) {
            adicionarItemDeclaracao();
        }
    } else {
        cancelCorreiosForm();
    }
}

// Cancelar formulário de correios
function cancelCorreiosForm() {
    const container = document.getElementById('correiosFormContainer');
    const btn = document.getElementById('toggleCorreiosFormBtn');
    
    container.classList.add('hidden');
    btn.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
        <span>Formulário de Correios</span>
    `;
    
    // Limpar formulário
    document.getElementById('remetentesContainer').innerHTML = '';
    document.getElementById('destinatariosContainer').innerHTML = '';
    document.getElementById('itensDeclaracaoContainer').innerHTML = '';
    document.getElementById('pesoTotal').value = '';
    document.getElementById('valorTotalDeclaracao').textContent = '0,00';
}

// Adicionar remetente
function adicionarRemetente() {
    const container = document.getElementById('remetentesContainer');
    const index = container.children.length;
    
    const remetenteDiv = document.createElement('div');
    remetenteDiv.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
    remetenteDiv.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-medium text-gray-900">Remetente ${index + 1}</h4>
            <button type="button" onclick="removerRemetente(this)" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                <input type="text" name="remetente_nome[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Nome completo">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Endereço *</label>
                <input type="text" name="remetente_endereco[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Endereço completo">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                    <input type="text" name="remetente_cidade[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Cidade">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF *</label>
                    <input type="text" name="remetente_uf[]" required maxlength="2" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="UF">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP *</label>
                    <input type="text" name="remetente_cep[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="00000-000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ/DOC.ESTRANGEIRO *</label>
                    <input type="text" name="remetente_documento[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Documento">
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(remetenteDiv);
}

// Adicionar destinatário
function adicionarDestinatario() {
    const container = document.getElementById('destinatariosContainer');
    const index = container.children.length;
    
    const destinatarioDiv = document.createElement('div');
    destinatarioDiv.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
    destinatarioDiv.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-medium text-gray-900">Destinatário ${index + 1}</h4>
            <button type="button" onclick="removerDestinatario(this)" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                <input type="text" name="destinatario_nome[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Nome completo">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Endereço *</label>
                <input type="text" name="destinatario_endereco[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Endereço completo">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                    <input type="text" name="destinatario_cidade[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Cidade">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF *</label>
                    <input type="text" name="destinatario_uf[]" required maxlength="2" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="UF">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP *</label>
                    <input type="text" name="destinatario_cep[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="00000-000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ/DOC.ESTRANGEIRO *</label>
                    <input type="text" name="destinatario_documento[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Documento">
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(destinatarioDiv);
}

// Adicionar item da declaração
function adicionarItemDeclaracao() {
    const container = document.getElementById('itensDeclaracaoContainer');
    const index = container.children.length;
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
    itemDiv.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-medium text-gray-900">Item ${index + 1}</h4>
            <button type="button" onclick="removerItemDeclaracao(this)" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conteúdo *</label>
                <input type="text" name="item_conteudo[]" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Descrição do item">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade *</label>
                <input type="number" name="item_quantidade[]" min="1" required onchange="calcularTotaisDeclaracao()" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="1">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Unitário (R$) *</label>
                <input type="number" name="item_valor[]" step="0.01" min="0" required onchange="calcularTotaisDeclaracao()" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="0,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total</label>
                <input type="text" class="item-valor-total-declaracao w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" readonly placeholder="R$ 0,00">
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
    calcularTotaisDeclaracao();
}

// Remover remetente
function removerRemetente(button) {
    button.closest('.border').remove();
    renumerarRemetentes();
}

// Remover destinatário
function removerDestinatario(button) {
    button.closest('.border').remove();
    renumerarDestinatarios();
}

// Remover item da declaração
function removerItemDeclaracao(button) {
    button.closest('.border').remove();
    renumerarItensDeclaracao();
    calcularTotaisDeclaracao();
}

// Renumerar remetentes
function renumerarRemetentes() {
    const remetentes = document.querySelectorAll('#remetentesContainer .border');
    remetentes.forEach((remetente, index) => {
        remetente.querySelector('h4').textContent = `Remetente ${index + 1}`;
    });
}

// Renumerar destinatários
function renumerarDestinatarios() {
    const destinatarios = document.querySelectorAll('#destinatariosContainer .border');
    destinatarios.forEach((destinatario, index) => {
        destinatario.querySelector('h4').textContent = `Destinatário ${index + 1}`;
    });
}

// Renumerar itens da declaração
function renumerarItensDeclaracao() {
    const itens = document.querySelectorAll('#itensDeclaracaoContainer .border');
    itens.forEach((item, index) => {
        item.querySelector('h4').textContent = `Item ${index + 1}`;
    });
}

// Calcular totais da declaração
function calcularTotaisDeclaracao() {
    let valorTotal = 0;
    
    const itens = document.querySelectorAll('#itensDeclaracaoContainer .border');
    itens.forEach(item => {
        const quantidade = parseFloat(item.querySelector('input[name="item_quantidade[]"]').value) || 0;
        const valorUnitario = parseFloat(item.querySelector('input[name="item_valor[]"]').value) || 0;
        const valorItemTotal = quantidade * valorUnitario;
        
        // Atualizar valor total do item
        item.querySelector('.item-valor-total-declaracao').value = `R$ ${valorItemTotal.toFixed(2).replace('.', ',')}`;
        
        valorTotal += valorItemTotal;
    });
    
    // Atualizar valor total geral
    document.getElementById('valorTotalDeclaracao').textContent = valorTotal.toFixed(2).replace('.', ',');
}

// Gerar declaração de conteúdo
function gerarDeclaracaoConteudo() {
    // Validar se há pelo menos um remetente, destinatário e item
    const remetentes = document.querySelectorAll('#remetentesContainer .border');
    const destinatarios = document.querySelectorAll('#destinatariosContainer .border');
    const itens = document.querySelectorAll('#itensDeclaracaoContainer .border');
    
    if (remetentes.length === 0) {
        alert('Adicione pelo menos um remetente');
        return;
    }
    
    if (destinatarios.length === 0) {
        alert('Adicione pelo menos um destinatário');
        return;
    }
    
    if (itens.length === 0) {
        alert('Adicione pelo menos um item');
        return;
    }
    
    // Coletar dados e gerar PDF
    const dados = coletarDadosDeclaracao();
    gerarPDFDeclaracao(dados);
}

// Coletar dados da declaração
function coletarDadosDeclaracao() {
    const dados = {
        remetentes: [],
        destinatarios: [],
        itens: [],
        pesoTotal: document.getElementById('pesoTotal').value || '0',
        valorTotal: document.getElementById('valorTotalDeclaracao').textContent
    };
    
    // Coletar remetentes
    document.querySelectorAll('#remetentesContainer .border').forEach(remetente => {
        dados.remetentes.push({
            nome: remetente.querySelector('input[name="remetente_nome[]"]').value,
            endereco: remetente.querySelector('input[name="remetente_endereco[]"]').value,
            cidade: remetente.querySelector('input[name="remetente_cidade[]"]').value,
            uf: remetente.querySelector('input[name="remetente_uf[]"]').value,
            cep: remetente.querySelector('input[name="remetente_cep[]"]').value,
            documento: remetente.querySelector('input[name="remetente_documento[]"]').value
        });
    });
    
    // Coletar destinatários
    document.querySelectorAll('#destinatariosContainer .border').forEach(destinatario => {
        dados.destinatarios.push({
            nome: destinatario.querySelector('input[name="destinatario_nome[]"]').value,
            endereco: destinatario.querySelector('input[name="destinatario_endereco[]"]').value,
            cidade: destinatario.querySelector('input[name="destinatario_cidade[]"]').value,
            uf: destinatario.querySelector('input[name="destinatario_uf[]"]').value,
            cep: destinatario.querySelector('input[name="destinatario_cep[]"]').value,
            documento: destinatario.querySelector('input[name="destinatario_documento[]"]').value
        });
    });
    
    // Coletar itens
    document.querySelectorAll('#itensDeclaracaoContainer .border').forEach(item => {
        dados.itens.push({
            conteudo: item.querySelector('input[name="item_conteudo[]"]').value,
            quantidade: item.querySelector('input[name="item_quantidade[]"]').value,
            valor: item.querySelector('input[name="item_valor[]"]').value
        });
    });
    
    return dados;
}

// Gerar PDF da declaração (implementação básica)
function gerarPDFDeclaracao(dados) {
    // Por enquanto, mostrar os dados coletados
    console.log('📋 Dados da Declaração:', dados);
    
    // Aqui seria implementada a geração do PDF
    // Pode usar bibliotecas como jsPDF ou enviar para o backend
    
    showNotification('Funcionalidade de geração de PDF será implementada em breve!', 'info');
    
    // Exemplo de como seria:
    // const pdf = new jsPDF();
    // // Adicionar conteúdo ao PDF baseado no template da imagem
    // pdf.save('declaracao-conteudo.pdf');
}

// =====================================================
// SISTEMA DE REDIMENSIONAMENTO DE COLUNAS
// =====================================================

// Variáveis para redimensionamento
let isResizing = false;
let currentColumn = null;
let startX = 0;
let startWidth = 0;

// Configuração padrão das colunas
const defaultColumnWidths = {
    'id': 80,
    'fornecedor': 200,
    'origem': 120,
    'nfs': 150,
    'status': 200,
    'itens': 80,
    'valor': 120,
    'anexos': 100,
    'data': 140,
    'acoes': 200
};

// Configuração de visibilidade das colunas
const columnConfig = {
    'id': { name: 'ID', visible: true },
    'fornecedor': { name: 'Fornecedor', visible: true },
    'origem': { name: 'Origem', visible: true },
    'nfs': { name: 'NFs', visible: true },
    'status': { name: 'Status', visible: true },
    'itens': { name: 'Itens', visible: true },
    'valor': { name: 'Valor Total', visible: true },
    'anexos': { name: 'Anexos', visible: true },
    'data': { name: 'Criado em', visible: true },
    'acoes': { name: 'Ações', visible: true }
};

// Inicializar redimensionamento de colunas
function initColumnResizing() {
    const table = document.getElementById('garantiasTable');
    if (!table) return;
    
    // Adicionar event listeners para redimensionamento
    const resizers = table.querySelectorAll('.column-resizer');
    resizers.forEach(resizer => {
        resizer.addEventListener('mousedown', startResize);
    });
    
    // Event listeners globais
    document.addEventListener('mousemove', doResize);
    document.addEventListener('mouseup', stopResize);
    
    // Carregar configurações salvas
    loadColumnSettings();
    
    console.log('🔧 Sistema de redimensionamento de colunas inicializado');
}

// Iniciar redimensionamento
function startResize(e) {
    isResizing = true;
    currentColumn = e.target.parentElement;
    startX = e.clientX;
    startWidth = parseInt(document.defaultView.getComputedStyle(currentColumn).width, 10);
    
    // Adicionar classe visual
    e.target.classList.add('resizing');
    document.body.classList.add('table-resizing');
    
    e.preventDefault();
}

// Fazer redimensionamento
function doResize(e) {
    if (!isResizing) return;
    
    const width = startWidth + e.clientX - startX;
    const minWidth = parseInt(currentColumn.style.minWidth) || 60;
    const maxWidth = 500; // Largura máxima
    
    const newWidth = Math.max(minWidth, Math.min(maxWidth, width));
    currentColumn.style.width = newWidth + 'px';
    
    // Atualizar células correspondentes
    const columnIndex = Array.from(currentColumn.parentElement.children).indexOf(currentColumn);
    const tbody = currentColumn.closest('table').querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cell = row.children[columnIndex];
        if (cell) {
            cell.style.width = newWidth + 'px';
        }
    });
}

// Parar redimensionamento
function stopResize(e) {
    if (!isResizing) return;
    
    isResizing = false;
    
    // Remover classes visuais
    const resizer = document.querySelector('.column-resizer.resizing');
    if (resizer) {
        resizer.classList.remove('resizing');
    }
    document.body.classList.remove('table-resizing');
    
    // Salvar configurações
    saveColumnSettings();
    
    currentColumn = null;
}

// Resetar larguras das colunas
function resetColumnWidths() {
    const table = document.getElementById('garantiasTable');
    if (!table) return;
    
    const headers = table.querySelectorAll('th[data-column]');
    headers.forEach(header => {
        const columnName = header.getAttribute('data-column');
        const defaultWidth = defaultColumnWidths[columnName] || 120;
        header.style.width = defaultWidth + 'px';
    });
    
    // Recarregar tabela para aplicar larguras
    if (garantias.length > 0) {
        renderizarTabela(garantias);
    }
    
    // Salvar configurações
    saveColumnSettings();
    
    showNotification('Larguras das colunas resetadas!', 'success');
}

// Configurar visibilidade das colunas
function toggleColumnVisibility() {
    const modal = document.getElementById('columnConfigModal');
    const togglesContainer = document.getElementById('columnToggles');
    
    // Limpar container
    togglesContainer.innerHTML = '';
    
    // Criar checkboxes para cada coluna
    Object.keys(columnConfig).forEach(columnKey => {
        const config = columnConfig[columnKey];
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between';
        div.innerHTML = `
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" ${config.visible ? 'checked' : ''} data-column="${columnKey}" class="mr-2">
                <span class="text-sm text-gray-700">${config.name}</span>
            </label>
        `;
        togglesContainer.appendChild(div);
    });
    
    modal.classList.remove('hidden');
}

// Fechar modal de configuração
function closeColumnConfig() {
    document.getElementById('columnConfigModal').classList.add('hidden');
}

// Aplicar configuração de colunas
function applyColumnConfig() {
    const checkboxes = document.querySelectorAll('#columnToggles input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
        const columnKey = checkbox.getAttribute('data-column');
        const isVisible = checkbox.checked;
        
        columnConfig[columnKey].visible = isVisible;
        
        // Aplicar visibilidade
        const table = document.getElementById('garantiasTable');
        const header = table.querySelector(`th[data-column="${columnKey}"]`);
        const columnIndex = Array.from(header.parentElement.children).indexOf(header);
        
        if (isVisible) {
            header.classList.remove('column-hidden');
            header.classList.add('column-show');
            
            // Mostrar células da coluna
            const tbody = table.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const cell = row.children[columnIndex];
                if (cell) {
                    cell.classList.remove('column-hidden');
                    cell.classList.add('column-show');
                }
            });
        } else {
            header.classList.add('column-hidden');
            header.classList.remove('column-show');
            
            // Ocultar células da coluna
            const tbody = table.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const cell = row.children[columnIndex];
                if (cell) {
                    cell.classList.add('column-hidden');
                    cell.classList.remove('column-show');
                }
            });
        }
    });
    
    // Salvar configurações
    saveColumnSettings();
    closeColumnConfig();
    
    showNotification('Configuração de colunas aplicada!', 'success');
}

// Salvar configurações das colunas
function saveColumnSettings() {
    const settings = {
        widths: {},
        visibility: columnConfig
    };
    
    const table = document.getElementById('garantiasTable');
    if (table) {
        const headers = table.querySelectorAll('th[data-column]');
        headers.forEach(header => {
            const columnName = header.getAttribute('data-column');
            settings.widths[columnName] = parseInt(header.style.width) || defaultColumnWidths[columnName];
        });
    }
    
    localStorage.setItem('garantias_column_settings', JSON.stringify(settings));
}

// Carregar configurações das colunas
function loadColumnSettings() {
    const savedSettings = localStorage.getItem('garantias_column_settings');
    if (!savedSettings) return;
    
    try {
        const settings = JSON.parse(savedSettings);
        
        // Aplicar larguras
        if (settings.widths) {
            const table = document.getElementById('garantiasTable');
            if (table) {
                const headers = table.querySelectorAll('th[data-column]');
                headers.forEach(header => {
                    const columnName = header.getAttribute('data-column');
                    if (settings.widths[columnName]) {
                        header.style.width = settings.widths[columnName] + 'px';
                    }
                });
            }
        }
        
        // Aplicar visibilidade
        if (settings.visibility) {
            Object.assign(columnConfig, settings.visibility);
            
            // Aplicar visibilidade na tabela
            Object.keys(columnConfig).forEach(columnKey => {
                const config = columnConfig[columnKey];
                if (!config.visible) {
                    const table = document.getElementById('garantiasTable');
                    if (table) {
                        const header = table.querySelector(`th[data-column="${columnKey}"]`);
                        if (header) {
                            header.classList.add('column-hidden');
                        }
                    }
                }
            });
        }
        
        console.log('⚙️ Configurações de colunas carregadas:', settings);
    } catch (error) {
        console.error('❌ Erro ao carregar configurações de colunas:', error);
    }
}

// Inicializar sistema de colunas quando a tabela for renderizada
document.addEventListener('DOMContentLoaded', function() {
    // Aguardar um pouco para garantir que a tabela foi criada
    setTimeout(() => {
        initColumnResizing();
    }, 500);
});
</script>
