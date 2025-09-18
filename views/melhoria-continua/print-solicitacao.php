<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Melhoria #<?= $solicitacao['id'] ?> - SGQ OTI DJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-break { page-break-before: always; }
        }
    </style>
</head>
<body class="bg-white text-gray-900">
    <div class="max-w-4xl mx-auto p-8">
        <!-- Header -->
        <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Sistema de Gestão da Qualidade</h1>
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Solicitação de Melhoria</h2>
            <div class="text-lg font-medium text-blue-600">Protocolo #<?= str_pad($solicitacao['id'], 6, '0', STR_PAD_LEFT) ?></div>
        </div>

        <!-- Print Button -->
        <div class="no-print mb-6 text-center">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg mr-4">
                Imprimir
            </button>
            <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                Fechar
            </button>
        </div>

        <!-- Informações Básicas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-gray-700 mb-3 border-b border-gray-300 pb-2">Informações da Solicitação</h3>
                <div class="space-y-2">
                    <div><span class="font-medium">ID:</span> #<?= $solicitacao['id'] ?></div>
                    <div><span class="font-medium">Data:</span> <?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></div>
                    <div><span class="font-medium">Usuário:</span> <?= e($solicitacao['usuario_nome']) ?></div>
                    <div><span class="font-medium">Setor:</span> <?= e($solicitacao['setor']) ?></div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-gray-700 mb-3 border-b border-gray-300 pb-2">Status</h3>
                <div class="space-y-2">
                    <?php
                    $statusLabels = [
                        'pendente' => 'Pendente',
                        'em_analise' => 'Em Análise',
                        'aprovado' => 'Aprovado',
                        'rejeitado' => 'Rejeitado',
                        'implementado' => 'Implementado'
                    ];
                    $statusColors = [
                        'pendente' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        'em_analise' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'aprovado' => 'bg-green-100 text-green-800 border-green-300',
                        'rejeitado' => 'bg-red-100 text-red-800 border-red-300',
                        'implementado' => 'bg-purple-100 text-purple-800 border-purple-300'
                    ];
                    ?>
                    <div class="inline-flex px-3 py-1 text-sm font-medium rounded-full border <?= $statusColors[$solicitacao['status']] ?? 'bg-gray-100 text-gray-800 border-gray-300' ?>">
                        <?= $statusLabels[$solicitacao['status']] ?? $solicitacao['status'] ?>
                    </div>
                    <div class="text-sm text-gray-600 mt-2">
                        Última atualização: <?= date('d/m/Y H:i', strtotime($solicitacao['updated_at'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Processo -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-3 border-b border-gray-300 pb-2">Processo</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900"><?= nl2br(e($solicitacao['processo'])) ?></p>
            </div>
        </div>

        <!-- Descrição da Melhoria -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-3 border-b border-gray-300 pb-2">Descrição da Melhoria</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900"><?= nl2br(e($solicitacao['descricao_melhoria'])) ?></p>
            </div>
        </div>

        <!-- Resultado Esperado -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-3 border-b border-gray-300 pb-2">Resultado Esperado</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900"><?= nl2br(e($solicitacao['resultado_esperado'])) ?></p>
            </div>
        </div>

        <!-- Observações -->
        <?php if (!empty($solicitacao['observacoes'])): ?>
        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-3 border-b border-gray-300 pb-2">Observações</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900"><?= nl2br(e($solicitacao['observacoes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Responsáveis -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-3 border-b border-gray-300 pb-2">Responsáveis</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <?php if (!empty($responsaveis)): ?>
                    <ul class="space-y-2">
                        <?php foreach ($responsaveis as $resp): ?>
                            <li class="flex items-center space-x-2">
                                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                <span class="font-medium"><?= e($resp['usuario_nome']) ?></span>
                                <span class="text-gray-600">(<?= e($resp['usuario_email']) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500">Nenhum responsável definido</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Anexos -->
        <?php if (!empty($anexos)): ?>
        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-3 border-b border-gray-300 pb-2">Anexos</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="space-y-2">
                    <?php foreach ($anexos as $anexo): ?>
                        <div class="flex items-center space-x-3 p-2 bg-white rounded border">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?= e($anexo['nome_original']) ?></p>
                                <p class="text-xs text-gray-500">
                                    <?= strtoupper(pathinfo($anexo['nome_original'], PATHINFO_EXTENSION)) ?> - 
                                    <?= number_format($anexo['tamanho_arquivo'] / 1024 / 1024, 2) ?> MB
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="mt-12 pt-6 border-t border-gray-300 text-center text-sm text-gray-600">
            <p>Sistema de Gestão da Qualidade - SGQ OTI DJ</p>
            <p>Documento gerado em <?= date('d/m/Y H:i:s') ?></p>
        </div>

        <!-- Assinaturas -->
        <div class="print-break mt-12 pt-8">
            <h3 class="font-semibold text-gray-700 mb-8 text-center">Controle de Assinaturas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="text-center">
                    <div class="border-b border-gray-400 mb-2 pb-1 min-h-[60px]"></div>
                    <p class="font-medium">Solicitante</p>
                    <p class="text-sm text-gray-600"><?= e($solicitacao['usuario_nome']) ?></p>
                    <p class="text-sm text-gray-600">Data: ___/___/______</p>
                </div>
                
                <div class="text-center">
                    <div class="border-b border-gray-400 mb-2 pb-1 min-h-[60px]"></div>
                    <p class="font-medium">Responsável pela Análise</p>
                    <p class="text-sm text-gray-600">Nome: _________________________</p>
                    <p class="text-sm text-gray-600">Data: ___/___/______</p>
                </div>
            </div>
            
            <div class="mt-12 text-center">
                <div class="border-b border-gray-400 mb-2 pb-1 min-h-[60px] max-w-md mx-auto"></div>
                <p class="font-medium">Aprovação Final</p>
                <p class="text-sm text-gray-600">Gestor da Qualidade</p>
                <p class="text-sm text-gray-600">Data: ___/___/______</p>
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
