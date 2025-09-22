<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FMEA - Registro #<?= $fmea['id'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { print-color-adjust: exact; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body class="bg-white p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Cabeçalho -->
        <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">FMEA - Análise de Modo e Efeito de Falha</h1>
            <h2 class="text-xl text-gray-600">Registro #<?= $fmea['id'] ?></h2>
            <p class="text-sm text-gray-500 mt-2">Data de Registro: <?= date('d/m/Y H:i', strtotime($fmea['data_registro'])) ?></p>
        </div>

        <!-- Botão de Impressão -->
        <div class="no-print mb-6 text-center">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Imprimir Registro
            </button>
        </div>

        <!-- Informações do Registro -->
        <div class="space-y-6">
            <!-- Modo de Falha -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Modo de Falha</h3>
                <p class="text-gray-700"><?= nl2br(e($fmea['modo_falha'])) ?></p>
            </div>

            <!-- Efeito da Falha -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Efeito da Falha</h3>
                <p class="text-gray-700"><?= nl2br(e($fmea['efeito_falha'])) ?></p>
            </div>

            <!-- Avaliações -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Severidade</h3>
                    <div class="text-3xl font-bold text-blue-600"><?= $fmea['severidade'] ?></div>
                    <p class="text-sm text-blue-700 mt-1">Impacto da falha</p>
                </div>
                
                <div class="bg-orange-50 p-4 rounded-lg text-center">
                    <h3 class="text-lg font-semibold text-orange-900 mb-2">Ocorrência</h3>
                    <div class="text-3xl font-bold text-orange-600"><?= $fmea['ocorrencia'] ?></div>
                    <p class="text-sm text-orange-700 mt-1">Frequência da falha</p>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <h3 class="text-lg font-semibold text-green-900 mb-2">Detecção</h3>
                    <div class="text-3xl font-bold text-green-600"><?= $fmea['deteccao'] ?></div>
                    <p class="text-sm text-green-700 mt-1">Capacidade de detecção</p>
                </div>
            </div>

            <!-- RPN e Risco -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-purple-50 p-6 rounded-lg text-center">
                    <h3 class="text-lg font-semibold text-purple-900 mb-2">RPN (Risk Priority Number)</h3>
                    <div class="text-4xl font-bold text-purple-600 mb-2"><?= $fmea['rpn'] ?></div>
                    <p class="text-sm text-purple-700">Severidade × Ocorrência × Detecção</p>
                    <p class="text-xs text-purple-600 mt-1"><?= $fmea['severidade'] ?> × <?= $fmea['ocorrencia'] ?> × <?= $fmea['deteccao'] ?> = <?= $fmea['rpn'] ?></p>
                </div>
                
                <div class="p-6 rounded-lg text-center <?php 
                    echo $fmea['risco'] === 'Risco Crítico' ? 'bg-red-50' : 
                         ($fmea['risco'] === 'Risco Alto' ? 'bg-orange-50' : 
                         ($fmea['risco'] === 'Risco Moderado' ? 'bg-yellow-50' : 'bg-green-50')); 
                ?>">
                    <h3 class="text-lg font-semibold mb-2 <?php 
                        echo $fmea['risco'] === 'Risco Crítico' ? 'text-red-900' : 
                             ($fmea['risco'] === 'Risco Alto' ? 'text-orange-900' : 
                             ($fmea['risco'] === 'Risco Moderado' ? 'text-yellow-900' : 'text-green-900')); 
                    ?>">Classificação de Risco</h3>
                    <div class="text-2xl font-bold mb-2 <?php 
                        echo $fmea['risco'] === 'Risco Crítico' ? 'text-red-600' : 
                             ($fmea['risco'] === 'Risco Alto' ? 'text-orange-600' : 
                             ($fmea['risco'] === 'Risco Moderado' ? 'text-yellow-600' : 'text-green-600')); 
                    ?>"><?= e($fmea['risco']) ?></div>
                    <p class="text-sm <?php 
                        echo $fmea['risco'] === 'Risco Crítico' ? 'text-red-700' : 
                             ($fmea['risco'] === 'Risco Alto' ? 'text-orange-700' : 
                             ($fmea['risco'] === 'Risco Moderado' ? 'text-yellow-700' : 'text-green-700')); 
                    ?>">
                        <?php if ($fmea['rpn'] < 40): ?>
                            RPN < 40: Risco aceitável
                        <?php elseif ($fmea['rpn'] < 100): ?>
                            40 ≤ RPN < 100: Monitoramento recomendado
                        <?php elseif ($fmea['rpn'] < 200): ?>
                            100 ≤ RPN < 200: Ação corretiva necessária
                        <?php else: ?>
                            RPN ≥ 200: Ação imediata obrigatória
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Ação Sugerida -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Ação Sugerida</h3>
                <p class="text-gray-700"><?= nl2br(e($fmea['acao_sugerida'])) ?></p>
            </div>

            <!-- Escala de Referência -->
            <div class="border-t pt-6 mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Escala de Referência FMEA</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-2">Severidade (S)</h4>
                        <ul class="space-y-1 text-blue-700">
                            <li>1-3: Efeito menor</li>
                            <li>4-6: Efeito moderado</li>
                            <li>7-8: Efeito alto</li>
                            <li>9-10: Efeito crítico</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-orange-900 mb-2">Ocorrência (O)</h4>
                        <ul class="space-y-1 text-orange-700">
                            <li>1-3: Remota/Baixa</li>
                            <li>4-6: Moderada</li>
                            <li>7-8: Alta</li>
                            <li>9-10: Muito alta</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-green-900 mb-2">Detecção (D)</h4>
                        <ul class="space-y-1 text-green-700">
                            <li>1-3: Quase certa</li>
                            <li>4-6: Moderada</li>
                            <li>7-8: Baixa</li>
                            <li>9-10: Muito baixa</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Área para Assinaturas -->
            <div class="border-t pt-6 mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Aprovações e Assinaturas</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <p class="text-sm text-gray-600 mb-4">Responsável pela Análise:</p>
                        <div class="border-b border-gray-400 h-16 mb-2"></div>
                        <p class="text-xs text-gray-500">Nome e Assinatura</p>
                        <p class="text-xs text-gray-500 mt-1">Data: ___/___/______</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 mb-4">Aprovado por:</p>
                        <div class="border-b border-gray-400 h-16 mb-2"></div>
                        <p class="text-xs text-gray-500">Nome e Assinatura</p>
                        <p class="text-xs text-gray-500 mt-1">Data: ___/___/______</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="text-center text-xs text-gray-500 mt-8 border-t pt-4">
            <p>Sistema SGQ OTI DJ - FMEA Registro #<?= $fmea['id'] ?></p>
            <p>Impresso em <?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>

    <script>
        // Auto-imprimir quando a página carregar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
