<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Garantia - SGQ OTI DJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-full { width: 100% !important; max-width: none !important; }
        }
        .ticket-box {
            border: 3px solid #667eea;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
        }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto print-full">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white p-6 rounded-t-lg no-print">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">🎫 Ficha de Garantia</h1>
                    <p class="text-purple-200 mt-1">SGQ OTI DJ - Sistema de Gestão da Qualidade</p>
                </div>
                <div class="text-right">
                    <div class="bg-white text-purple-600 px-4 py-2 rounded-lg font-mono text-xl font-bold" id="ticketDisplay">
                        Carregando...
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <div class="bg-white p-8 shadow-lg" id="fichaContent">
            <!-- Cabeçalho para impressão -->
            <div class="hidden print:block mb-6 text-center border-b-2 border-purple-600 pb-4">
                <h1 class="text-3xl font-bold text-purple-600">SGQ OTI DJ</h1>
                <p class="text-lg text-gray-600">Ficha de Garantia</p>
                <div class="ticket-box mt-4 inline-block">
                    <p class="text-sm text-gray-600">Nº do Ticket</p>
                    <p class="text-3xl font-bold text-purple-600 font-mono" id="ticketPrint">-</p>
                </div>
            </div>

            <form id="fichaForm" class="space-y-6">
                <input type="hidden" id="numeroTicket" name="numero_ticket">

                <!-- Código do Produto -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Código do Produto <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="codigoProduto"
                        name="codigo_produto"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Ex: HP CF283A, M1212nf, etc..."
                    >
                </div>

                <!-- Nome do Solicitante -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Solicitante <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="nomeSolicitante"
                        name="nome_solicitante"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Nome completo"
                    >
                </div>

                <!-- Descrição do Defeito -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição do Defeito <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="descricaoDefeito"
                        name="descricao_defeito"
                        required
                        rows="5"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Descreva detalhadamente o defeito apresentado pelo produto..."
                    ></textarea>
                </div>

                <!-- Data e Hora de Abertura -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <strong>Data e Hora de Abertura:</strong> <span id="dataHoraAbertura">-</span>
                    </p>
                </div>

                <!-- Observações Importantes -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800 mb-2">⚠️ Observações Importantes:</h3>
                            <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                                <li><strong>Encaminhe esta ficha</strong> para o departamento responsável por garantias</li>
                                <li>A contagem do prazo <strong>iniciará apenas</strong> quando o produto <strong>chegar ao laboratório de garantias</strong></li>
                                <li>Você receberá <strong>e-mails automáticos</strong> sobre cada mudança de status da garantia</li>
                                <li>Guarde o <strong>número do ticket</strong> para acompanhamento</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex space-x-3 no-print">
                    <button
                        type="button"
                        onclick="imprimirFicha()"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center space-x-2 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Imprimir Ficha</span>
                    </button>
                    <button
                        type="button"
                        onclick="window.close()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors"
                    >
                        Fechar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let numeroTicket = null;

        // Carregar ticket ao abrir página
        document.addEventListener('DOMContentLoaded', function() {
            gerarTicket();
            atualizarDataHora();
        });

        // Gerar número de ticket único
        async function gerarTicket() {
            try {
                console.log('🎫 Gerando número de ticket...');
                
                const response = await fetch('/garantias/gerar-ticket', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                
                if (result.success) {
                    numeroTicket = result.ticket;
                    document.getElementById('ticketDisplay').textContent = numeroTicket;
                    document.getElementById('ticketPrint').textContent = numeroTicket;
                    document.getElementById('numeroTicket').value = numeroTicket;
                    console.log('✅ Ticket gerado:', numeroTicket);
                } else {
                    console.error('❌ Erro ao gerar ticket:', result.message);
                    alert('Erro ao gerar ticket: ' + result.message);
                }
            } catch (error) {
                console.error('❌ Erro na requisição:', error);
                alert('Erro ao gerar ticket. Verifique sua conexão.');
            }
        }

        // Atualizar data e hora
        function atualizarDataHora() {
            const agora = new Date();
            const dataFormatada = agora.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('dataHoraAbertura').textContent = dataFormatada;
        }

        // Imprimir ficha
        function imprimirFicha() {
            // Validar campos antes de imprimir
            const form = document.getElementById('fichaForm');
            if (!form.checkValidity()) {
                alert('Por favor, preencha todos os campos obrigatórios antes de imprimir.');
                form.reportValidity();
                return;
            }

            if (!numeroTicket) {
                alert('Aguarde a geração do número do ticket...');
                return;
            }

            console.log('🖨️ Imprimindo ficha...');
            window.print();
        }
    </script>
</body>
</html>
