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
            <h1 class="text-2xl font-semibold text-gray-900">Requisição de Garantias</h1>
            <p class="text-gray-600 mt-1">Solicitar novas garantias para produtos</p>
        </div>
        <div class="flex space-x-3">
            <a href="/garantias" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Voltar</span>
            </a>
            <button id="btnNovaRequisicao" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Nova Requisição</span>
            </button>
        </div>
    </div>

    <!-- Card de Informação -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-600 rounded-lg p-6 shadow-md">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="bg-blue-600 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <h3 class="text-lg font-bold text-blue-900">🚀 Melhoria de Processos - Requisição de Garantias</h3>
                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">EM DESENVOLVIMENTO</span>
                </div>
                <p class="text-blue-800 mb-3 font-medium">
                    Este módulo faz parte da melhoria contínua dos processos de gestão de garantias de produtos.
                </p>
                <div class="bg-white bg-opacity-60 rounded-lg p-4 mb-3">
                    <p class="text-sm text-gray-700 mb-2 font-semibold">📋 Objetivo da Melhoria:</p>
                    <p class="text-sm text-gray-600 mb-3">
                        Automatizar e centralizar o processo de solicitação de garantias, reduzindo tempo de resposta, 
                        melhorando rastreabilidade e facilitando a comunicação com fornecedores.
                    </p>
                </div>
                <p class="text-sm text-blue-700 font-semibold mb-2">✨ Funcionalidades Planejadas:</p>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• <strong>Formulário Digital:</strong> Preenchimento rápido com validação automática</li>
                    <li>• <strong>Anexo de Evidências:</strong> Upload de fotos e documentos do produto defeituoso</li>
                    <li>• <strong>Workflow de Aprovação:</strong> Análise e aprovação em múltiplos níveis</li>
                    <li>• <strong>Notificações Automáticas:</strong> Email/SMS para solicitante e responsáveis</li>
                    <li>• <strong>Rastreamento em Tempo Real:</strong> Acompanhamento do status da requisição</li>
                    <li>• <strong>Histórico Completo:</strong> Registro de todas as ações e comunicações</li>
                    <li>• <strong>Relatórios e KPIs:</strong> Métricas de tempo de resposta e taxa de aprovação</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Área de conteúdo principal -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="text-center py-12">
            <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Funcionalidade em Desenvolvimento</h3>
            <p class="text-gray-600 mb-6">
                O formulário de requisição de garantias estará disponível em breve.
            </p>
            <p class="text-sm text-gray-500">
                Por enquanto, utilize o módulo "Registro de Garantias" para cadastrar garantias.
            </p>
        </div>
    </div>
</section>
