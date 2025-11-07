<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $melhoria vem do controller
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melhoria #<?= $melhoria['id'] ?> - <?= htmlspecialchars($melhoria['titulo']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            animation: slideUp 0.3s ease-out;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="p-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="card bg-white rounded-lg shadow-2xl mb-6 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">🚀 Melhoria Contínua 2.0</h1>
                        <p class="text-blue-100">Melhoria #<?= $melhoria['id'] ?></p>
                    </div>
                    <button onclick="window.close()" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                        ✖️ Fechar
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($melhoria['titulo']) ?></h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-xs text-blue-600 font-semibold mb-1">📅 DATA</p>
                        <p class="text-sm font-medium"><?= date('d/m/Y H:i', strtotime($melhoria['created_at'])) ?></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-xs text-green-600 font-semibold mb-1">🏢 DEPARTAMENTO</p>
                        <p class="text-sm font-medium"><?= htmlspecialchars($melhoria['departamento_nome'] ?? 'N/A') ?></p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <p class="text-xs text-purple-600 font-semibold mb-1">📊 STATUS</p>
                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full 
                            <?php 
                            switch($melhoria['status']) {
                                case 'Pendente análise': echo 'bg-yellow-100 text-yellow-800'; break;
                                case 'Em andamento': echo 'bg-blue-100 text-blue-800'; break;
                                case 'Concluída': echo 'bg-green-100 text-green-800'; break;
                                case 'Recusada': echo 'bg-red-100 text-red-800'; break;
                                default: echo 'bg-gray-100 text-gray-800';
                            }
                            ?>">
                            <?= htmlspecialchars($melhoria['status']) ?>
                        </span>
                    </div>
                    <?php if (!empty($melhoria['pontuacao'])): ?>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <p class="text-xs text-yellow-600 font-semibold mb-1">⭐ PONTUAÇÃO</p>
                        <p class="text-sm font-medium"><?= $melhoria['pontuacao'] ?>/3</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Descrição -->
        <div class="card bg-white rounded-lg shadow-xl mb-6 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center mr-3">📄</span>
                Descrição da Melhoria
            </h3>
            <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($melhoria['resultado_esperado'])) ?></p>
        </div>

        <!-- 5W2H -->
        <div class="card bg-white rounded-lg shadow-xl mb-6 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center mr-3">🎯</span>
                Metodologia 5W2H
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <p class="text-xs font-semibold text-blue-600 mb-1">O QUE será feito?</p>
                    <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($melhoria['o_que'])) ?></p>
                </div>
                <div class="border-l-4 border-green-500 pl-4 py-2">
                    <p class="text-xs font-semibold text-green-600 mb-1">COMO será feito?</p>
                    <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($melhoria['como'])) ?></p>
                </div>
                <div class="border-l-4 border-yellow-500 pl-4 py-2">
                    <p class="text-xs font-semibold text-yellow-600 mb-1">ONDE será feito?</p>
                    <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($melhoria['onde'])) ?></p>
                </div>
                <div class="border-l-4 border-red-500 pl-4 py-2">
                    <p class="text-xs font-semibold text-red-600 mb-1">POR QUE será feito?</p>
                    <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($melhoria['porque'])) ?></p>
                </div>
                <div class="border-l-4 border-purple-500 pl-4 py-2">
                    <p class="text-xs font-semibold text-purple-600 mb-1">QUANDO será feito?</p>
                    <p class="text-sm text-gray-700"><?= date('d/m/Y', strtotime($melhoria['quando'])) ?></p>
                </div>
                <div class="border-l-4 border-indigo-500 pl-4 py-2">
                    <p class="text-xs font-semibold text-indigo-600 mb-1">QUANTO custa?</p>
                    <p class="text-sm text-gray-700"><?= $melhoria['quanto_custa'] ? 'R$ ' . number_format($melhoria['quanto_custa'], 2, ',', '.') : 'Não informado' ?></p>
                </div>
            </div>
        </div>

        <!-- Responsáveis -->
        <div class="card bg-white rounded-lg shadow-xl mb-6 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <span class="bg-green-100 text-green-600 w-8 h-8 rounded-full flex items-center justify-center mr-3">👥</span>
                Equipe
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-xs font-semibold text-blue-600 mb-2">💡 IDEALIZADOR</p>
                    <p class="text-sm font-medium"><?= htmlspecialchars($melhoria['idealizador']) ?></p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-xs font-semibold text-green-600 mb-2">👤 CRIADO POR</p>
                    <p class="text-sm font-medium"><?= htmlspecialchars($melhoria['criador_nome']) ?></p>
                </div>
                <?php if (!empty($melhoria['responsaveis_nomes'])): ?>
                <div class="bg-purple-50 p-4 rounded-lg md:col-span-2">
                    <p class="text-xs font-semibold text-purple-600 mb-2">👥 RESPONSÁVEIS</p>
                    <p class="text-sm font-medium"><?= htmlspecialchars($melhoria['responsaveis_nomes']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Observações -->
        <?php if (!empty($melhoria['observacao'])): ?>
        <div class="card bg-white rounded-lg shadow-xl mb-6 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                <span class="bg-yellow-100 text-yellow-600 w-8 h-8 rounded-full flex items-center justify-center mr-3">📌</span>
                Observações
            </h3>
            <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($melhoria['observacao'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Anexos -->
        <?php 
        $anexos = !empty($melhoria['anexos']) ? json_decode($melhoria['anexos'], true) : [];
        if (!empty($anexos)): 
        ?>
        <div class="card bg-white rounded-lg shadow-xl mb-6 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <span class="bg-red-100 text-red-600 w-8 h-8 rounded-full flex items-center justify-center mr-3">📎</span>
                Anexos (<?= count($anexos) ?>)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($anexos as $anexo): ?>
                <a href="<?= htmlspecialchars($anexo['url']) ?>" target="_blank" 
                   class="flex items-center space-x-3 p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all">
                    <span class="text-3xl">
                        <?= (isset($anexo['tipo']) && strpos($anexo['tipo'], 'image') !== false) ? '🖼️' : '📄' ?>
                    </span>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($anexo['nome']) ?></p>
                        <p class="text-xs text-gray-500"><?= isset($anexo['tamanho']) ? number_format($anexo['tamanho'] / 1024, 1) . ' KB' : '' ?></p>
                    </div>
                    <span class="text-blue-600">→</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ações -->
        <div class="card bg-white rounded-lg shadow-xl p-6 mb-6">
            <div class="flex justify-center space-x-4">
                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    🖨️ Imprimir
                </button>
                <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    ✖️ Fechar
                </button>
            </div>
        </div>
    </div>
</body>
</html>
