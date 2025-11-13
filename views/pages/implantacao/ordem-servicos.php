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
            <h1 class="text-3xl font-bold text-gray-900">📋 Ordem de Serviços de Implantação</h1>
            <p class="text-gray-600 mt-2">Gestão completa de ordens de serviço e acompanhamento de implantações</p>
        </div>
    </div>

    <!-- Banner Principal -->
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-5 rounded-full transform translate-x-48 -translate-y-48"></div>
        
        <div class="relative p-8 md:p-12">
            <div class="max-w-4xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-4 py-2 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full animate-pulse">
                        🚀 LANÇAMENTO TRIAL EM BREVE
                    </span>
                    <span class="px-4 py-2 bg-pink-400 text-pink-900 text-sm font-bold rounded-full">
                        💎 MÓDULO PREMIUM
                    </span>
                </div>
                
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Sistema de Ordens de Serviço Inteligente
                </h2>
                
                <p class="text-xl text-purple-100 mb-6">
                    Automatize a criação, distribuição e acompanhamento de ordens de serviço de implantação.
                    Tenha controle total sobre cada etapa do processo.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20">
                        <h3 class="text-white font-bold mb-3 flex items-center gap-2">
                            <span class="text-2xl">⚡</span> Recursos Principais
                        </h3>
                        <ul class="space-y-2 text-purple-100 text-sm">
                            <li>✓ Criação automática de OS</li>
                            <li>✓ Atribuição de equipes</li>
                            <li>✓ Checklist de atividades</li>
                            <li>✓ Controle de prazos (SLA)</li>
                            <li>✓ Assinatura digital</li>
                            <li>✓ Fotos de evidência</li>
                        </ul>
                    </div>
                    
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20">
                        <h3 class="text-white font-bold mb-3 flex items-center gap-2">
                            <span class="text-2xl">📊</span> Benefícios
                        </h3>
                        <ul class="space-y-2 text-purple-100 text-sm">
                            <li>✓ Redução de 70% em papel</li>
                            <li>✓ Rastreamento em tempo real</li>
                            <li>✓ Histórico completo</li>
                            <li>✓ Integração com equipe móvel</li>
                            <li>✓ Relatórios automáticos</li>
                            <li>✓ Alertas inteligentes</li>
                        </ul>
                    </div>
                </div>
                
                <div class="flex items-center gap-6 mb-6">
                    <div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-bold text-white">R$ 700</span>
                            <span class="text-xl text-purple-200">/mês</span>
                        </div>
                        <p class="text-purple-200 text-sm mt-1">Inclui todo o módulo de Gestão de Implantação</p>
                    </div>
                </div>
                
                <div class="flex gap-4 flex-wrap">
                    <button disabled class="bg-white text-indigo-700 px-8 py-4 rounded-xl font-bold text-lg shadow-xl opacity-60 cursor-not-allowed">
                        🔒 Trial Em Breve
                    </button>
                    <button disabled class="bg-indigo-500 bg-opacity-30 text-white border-2 border-white px-8 py-4 rounded-xl font-semibold text-lg opacity-60 cursor-not-allowed">
                        Solicitar Demonstração
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview do Sistema -->
    <div class="bg-white rounded-xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6">
            <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                <span class="text-3xl">🎯</span>
                Preview do Sistema de OS
            </h3>
        </div>
        
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h4 class="text-lg font-bold text-gray-900 mb-4">📱 Interface Mobile-First</h4>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start gap-3">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>App otimizado para técnicos em campo</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Funciona offline com sincronização automática</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Câmera integrada para evidências</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Assinatura digital do cliente</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold text-gray-900 mb-4">💼 Gestão Administrativa</h4>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start gap-3">
                            <span class="text-blue-600 font-bold">✓</span>
                            <span>Dashboard executivo com KPIs</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-600 font-bold">✓</span>
                            <span>Atribuição automática de equipes</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-600 font-bold">✓</span>
                            <span>Controle de SLA e alertas</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-600 font-bold">✓</span>
                            <span>Relatórios personalizáveis</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border-l-4 border-indigo-600 rounded-lg p-8 text-center">
        <h3 class="text-2xl font-bold text-gray-900 mb-3">🎁 Condições Especiais de Lançamento</h3>
        <p class="text-gray-700 mb-6 max-w-2xl mx-auto">
            Seja um dos primeiros a testar o sistema durante o período trial e garanta desconto especial na contratação!
        </p>
        <div class="flex justify-center gap-4 flex-wrap">
            <a href="mailto:comercial@sgqoti.com" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all">
                📧 Falar com Especialista
            </a>
            <a href="tel:+5511999999999" class="bg-green-600 hover:bg-green-700 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all">
                📱 WhatsApp Comercial
            </a>
        </div>
    </div>
</section>
