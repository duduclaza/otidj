<?php
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function getMesNome($mes) {
    $meses = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];
    return $meses[$mes] ?? '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Dashboard - SGQ OTI DJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">🔐 Master Dashboard</h1>
                    <p class="text-sm text-purple-100">Painel Administrativo Avançado</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm">👤 <?= e($_SESSION['master_email']) ?></span>
                    <a href="/master/logout" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm">
                        🚪 Sair
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total de Usuários</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $stats['total_users'] ?></p>
                    </div>
                    <div class="text-4xl">👥</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pagamentos Pendentes</p>
                        <p class="text-3xl font-bold text-orange-600"><?= $stats['pagamentos_pendentes'] ?></p>
                    </div>
                    <div class="text-4xl">⏳</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Status do Sistema</p>
                        <p class="text-lg font-bold <?= $stats['sistema_bloqueado'] ? 'text-red-600' : 'text-green-600' ?>">
                            <?= $stats['sistema_bloqueado'] ? '🔒 Bloqueado' : '✅ Ativo' ?>
                        </p>
                    </div>
                    <div class="text-4xl"><?= $stats['sistema_bloqueado'] ? '🔒' : '✅' ?></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Pagos</p>
                        <p class="text-3xl font-bold text-green-600"><?= $stats['total_pagos'] ?></p>
                    </div>
                    <div class="text-4xl">💰</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b">
                <nav class="flex">
                    <button onclick="showTab('cobrancas')" id="tab-cobrancas" 
                            class="tab-button px-6 py-3 font-medium border-b-2 border-purple-600 text-purple-600">
                        💳 Cobranças
                    </button>
                    <button onclick="showTab('chamados')" id="tab-chamados" 
                            class="tab-button px-6 py-3 font-medium text-gray-500 hover:text-gray-700">
                        📞 Chamados (Em breve)
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab: Cobranças -->
        <div id="content-cobrancas" class="tab-content">
            <!-- Pagamentos Pendentes -->
            <?php if (count($pagamentosPendentes) > 0): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <h3 class="font-semibold text-red-800 mb-2">⚠️ Pagamentos Pendentes</h3>
                <div class="space-y-2">
                    <?php foreach ($pagamentosPendentes as $pag): ?>
                    <div class="flex justify-between items-center bg-white p-3 rounded">
                        <div>
                            <span class="font-medium"><?= getMesNome($pag['mes']) ?>/<?= $pag['ano'] ?></span>
                            <span class="text-sm text-gray-500 ml-2">
                                Vencimento: <?= date('d/m/Y', strtotime($pag['data_vencimento'])) ?>
                            </span>
                        </div>
                        <button onclick="aprovarPagamento(<?= $pag['id'] ?>)" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                            ✓ Aprovar Pagamento
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Histórico Completo -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h2 class="text-lg font-semibold text-gray-800">📋 Histórico Completo de Pagamentos</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mês/Ano</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Pagamento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprovante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($todosPagamentos as $pag): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?= getMesNome($pag['mes']) ?>/<?= $pag['ano'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?= date('d/m/Y', strtotime($pag['data_vencimento'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        <?php
                                          switch($pag['status']) {
                                            case 'Pago': echo 'bg-green-100 text-green-800'; break;
                                            case 'Em Aberto': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'Atrasado': echo 'bg-red-100 text-red-800'; break;
                                          }
                                        ?>">
                                        <?= e($pag['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?= $pag['data_pagamento'] ? date('d/m/Y H:i', strtotime($pag['data_pagamento'])) : '-' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <?php if ($pag['comprovante']): ?>
                                        <a href="/financeiro/<?= $pag['id'] ?>/download-comprovante" 
                                           class="text-blue-600 hover:text-blue-800">
                                            📄 Baixar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if ($pag['status'] !== 'Pago'): ?>
                                        <button onclick="aprovarPagamento(<?= $pag['id'] ?>)" 
                                                class="text-green-600 hover:text-green-800 font-medium">
                                            ✓ Aprovar
                                        </button>
                                    <?php else: ?>
                                        <span class="text-green-600">✓ Pago</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Chamados -->
        <div id="content-chamados" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-6xl mb-4">🚧</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Em Desenvolvimento</h3>
                <p class="text-gray-600">O módulo de chamados estará disponível em breve!</p>
            </div>
        </div>
    </div>

    <script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-purple-600', 'text-purple-600');
            btn.classList.add('text-gray-500');
        });

        // Show selected tab
        document.getElementById('content-' + tabName).classList.remove('hidden');

        // Add active class to selected button
        const activeBtn = document.getElementById('tab-' + tabName);
        activeBtn.classList.remove('text-gray-500');
        activeBtn.classList.add('border-purple-600', 'text-purple-600');
    }

    async function aprovarPagamento(id) {
        if (!confirm('Tem certeza que deseja aprovar este pagamento?')) return;

        try {
            const response = await fetch('/master/aprovar-pagamento', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `pagamento_id=${id}`
            });

            const result = await response.json();
            alert(result.message);

            if (result.success) {
                window.location.reload();
            }
        } catch (error) {
            alert('Erro ao aprovar pagamento');
        }
    }
    </script>
</body>
</html>
