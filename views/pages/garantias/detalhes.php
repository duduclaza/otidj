<!-- Cabeçalho -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
                🛡️ Garantia #<?= $garantia['id'] ?>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    <?php
                        $statusClasses = [
                            'Em andamento' => 'bg-blue-100 text-blue-800',
                            'Aguardando Fornecedor' => 'bg-yellow-100 text-yellow-800',
                            'Aguardando Recebimento' => 'bg-purple-100 text-purple-800',
                            'Aguardando Testes' => 'bg-orange-100 text-orange-800',
                            'Finalizado' => 'bg-green-100 text-green-800',
                            'Garantia Expirada' => 'bg-red-100 text-red-800',
                            'Garantia não coberta' => 'bg-gray-100 text-gray-800'
                        ];
                        echo $statusClasses[$garantia['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>">
                    <?= e($garantia['status']) ?>
                </span>
            </h1>
            <p class="text-gray-600 mt-1">
                Criada em <?= date('d/m/Y H:i', strtotime($garantia['created_at'])) ?>
                <?php if ($garantia['updated_at']): ?>
                    • Atualizada em <?= date('d/m/Y H:i', strtotime($garantia['updated_at'])) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="/garantias" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                ← Voltar
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                🖨️ Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Grid Principal -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Coluna Esquerda - Informações Principais -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Informações Básicas -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                📋 Informações Básicas
            </h2>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600">Fornecedor</label>
                    <p class="font-medium text-gray-900"><?= e($garantia['fornecedor_nome']) ?></p>
                </div>
                
                <div>
                    <label class="text-sm text-gray-600">Origem da Garantia</label>
                    <p class="font-medium text-gray-900"><?= e($garantia['origem_garantia']) ?></p>
                </div>
                
                <?php if ($garantia['numero_nf_compras']): ?>
                <div>
                    <label class="text-sm text-gray-600">NF Compras</label>
                    <p class="font-medium text-gray-900"><?= e($garantia['numero_nf_compras']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($garantia['numero_nf_remessa_simples']): ?>
                <div>
                    <label class="text-sm text-gray-600">NF Remessa Simples</label>
                    <p class="font-medium text-gray-900"><?= e($garantia['numero_nf_remessa_simples']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($garantia['numero_nf_remessa_devolucao']): ?>
                <div>
                    <label class="text-sm text-gray-600">NF Remessa Devolução</label>
                    <p class="font-medium text-gray-900"><?= e($garantia['numero_nf_remessa_devolucao']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($garantia['numero_serie']): ?>
                <div>
                    <label class="text-sm text-gray-600">Número de Série</label>
                    <p class="font-medium text-gray-900"><?= e($garantia['numero_serie']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($garantia['numero_lote']): ?>
                <div>
                    <label class="text-sm text-gray-600">Número do Lote</label>
                    <p class="font-medium text-gray-900"><?= e($garantia['numero_lote']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($garantia['numero_ticket_os']): ?>
                <div>
                    <label class="text-sm text-gray-600">Ticket/OS</label>
                    <p class="font-medium text-gray-900"><?= e($garantia['numero_ticket_os']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($garantia['numero_ticket_interno']): ?>
                <div>
                    <label class="text-sm text-gray-600">Ticket Interno</label>
                    <p class="font-medium text-gray-900"><?= e($garantia['numero_ticket_interno']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($garantia['observacao']): ?>
            <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <label class="text-sm text-gray-600 block mb-1">💬 Observação</label>
                <p class="text-gray-900"><?= nl2br(e($garantia['observacao'])) ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($garantia['descricao_defeito']): ?>
            <div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-200">
                <label class="text-sm text-gray-600 block mb-1">🔧 Descrição do Defeito</label>
                <p class="text-gray-900"><?= nl2br(e($garantia['descricao_defeito'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Itens da Garantia -->
        <?php if (!empty($itens)): ?>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                📦 Itens da Garantia
            </h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tipo</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Código</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Descrição</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Qtd</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Valor Unit.</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        $totalGeral = 0;
                        foreach ($itens as $item): 
                            $totalItem = $item['quantidade'] * $item['valor_unitario'];
                            $totalGeral += $totalItem;
                        ?>
                        <tr>
                            <td class="px-4 py-3 text-sm">
                                <?php if ($item['tipo_produto']): ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                                        <?= e($item['tipo_produto']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono"><?= e($item['codigo_produto'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-sm"><?= e($item['descricao']) ?></td>
                            <td class="px-4 py-3 text-sm text-right"><?= $item['quantidade'] ?></td>
                            <td class="px-4 py-3 text-sm text-right">R$ <?= number_format($item['valor_unitario'], 2, ',', '.') ?></td>
                            <td class="px-4 py-3 text-sm text-right font-medium">R$ <?= number_format($totalItem, 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-900">Total Geral:</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">R$ <?= number_format($totalGeral, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Histórico de Status REAL -->
        <?php if (!empty($historicoStatus)): ?>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                📊 Histórico de Status
            </h2>
            
            <div class="space-y-4">
                <?php foreach ($historicoStatus as $historico): ?>
                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                            📝
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-gray-900">
                                <?php if ($historico['status_anterior']): ?>
                                    <?= e($historico['status_anterior']) ?> → 
                                <?php endif; ?>
                                <?= e($historico['status_novo']) ?>
                            </span>
                            <span class="text-sm text-gray-600">
                                <?= date('d/m/Y H:i', strtotime($historico['data_mudanca'])) ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            Por: <?= e($historico['usuario_nome']) ?>
                        </p>
                        <?php if ($historico['observacao']): ?>
                        <p class="text-sm text-gray-700 mt-1 italic">
                            "<?= e($historico['observacao']) ?>"
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Coluna Direita - Informações Complementares -->
    <div class="space-y-6">
        
        <!-- Tempo Real por Status -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">⏱️ Tempo por Status</h2>
            
            <div class="space-y-3">
                <?php foreach ($temposPorStatus as $status => $dados): ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700"><?= e($status) ?></span>
                    <span class="text-sm font-medium text-gray-900"><?= $dados['tempo_formatado'] ?></span>
                </div>
                <?php endforeach; ?>
                
                <div class="pt-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-900">Tempo Total</span>
                        <span class="text-sm font-bold text-blue-600"><?= $tempoTotal ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notificação -->
        <?php if ($garantia['usuario_notificado_nome']): ?>
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <h3 class="font-medium text-blue-900 mb-2 flex items-center gap-2">
                🔔 Notificação Ativa
            </h3>
            <p class="text-sm text-blue-800">
                <strong><?= e($garantia['usuario_notificado_nome']) ?></strong><br>
                <?= e($garantia['usuario_notificado_email']) ?>
            </p>
            <p class="text-xs text-blue-600 mt-2">
                Recebe emails de mudanças de status
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Informações do Sistema -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-medium text-gray-900 mb-3">ℹ️ Informações do Sistema</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">ID:</span>
                    <span class="font-medium">#<?= $garantia['id'] ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Criada:</span>
                    <span class="font-medium"><?= date('d/m/Y H:i', strtotime($garantia['created_at'])) ?></span>
                </div>
                <?php if ($garantia['updated_at']): ?>
                <div class="flex justify-between">
                    <span class="text-gray-600">Atualizada:</span>
                    <span class="font-medium"><?= date('d/m/Y H:i', strtotime($garantia['updated_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background: white; }
}
</style>
